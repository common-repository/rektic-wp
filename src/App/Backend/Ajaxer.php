<?php
/**
 * Rektic WP
 *
 * This file is the main controller for all Ajax reuests performed by the plugin
 *
 * @package   rektic-wp
 * @author    SAKHRAOUI Omar <info@omarion.me>
 * @copyright 2022 Rektic WP
 * @license   GPLv3
 * @link      https://omarion.me
 */

declare(strict_types=1);

namespace RekticWp\App\Backend;

use RekticWp\Common\Abstracts\Base;
use RekticWp\Common\Utils\Sanitize;
use function __;
use function add_query_arg;
use function compact;
use function get_edit_post_link;
use function header;
use function is_numeric;
use function is_wp_error;
use function json_decode;
use function json_encode;
use function sanitize_text_field;
use function sanitize_textarea_field;
use function str_word_count;
use function strip_tags;
use function strlen;
use function strtotime;
use function wp_date;
use function wp_insert_post;
use function wp_json_encode;
use function wp_kses;
use function wp_kses_post;
use function wp_remote_get;
use function wp_remote_post;
use function wp_remote_request;
use function wp_remote_retrieve_body;
use function wp_remote_retrieve_response_code;
use function wp_verify_nonce;

/**
 * Class Ajaxer
 *
 * @package RekticWp\App\Backend
 * @since   1.0.0
 */
class Ajaxer extends Base{
	private $rektic_settings_options;
	/**
	 * @var array
	 */
	private $allowedHtml;
	/**
	 * @var array
	 */
	private $seoMessages;

	/**
	 * Initialize the class.
	 *
	 * @since 1.0.0
	 */
	public function init(){
		/**
		 * This backend class is only being instantiated in the backend as requested in the Bootstrap class
		 *
		 * @see Requester::isAdminBackend()
		 * @see Bootstrap::__construct
		 *
		 * Add plugin code here for admin settings specific functions
		 */
		$this->rektic_settings_options = get_option('rektic_settings_option_name');
		$this->allowedHtml             = wp_kses_allowed_html('post');
		$this->seoMessages             = [
			"DMCP"              => __("Main Keyword Density", 'rektic-wp'),
			"MCPI"              => __("Main keywords in the introduction", 'rektic-wp'),
			"MCST"              => __("Main keyword in the sub-titles", 'rektic-wp'),
			"MCPMD"             => __("Main keyword in the meta-description", 'rektic-wp'),
			"RTSEO"             => __("Query in the SEO title (meta-title)", 'rektic-wp'),
			"LMD"               => __("Length of meta-description", 'rektic-wp'),
			"LE"                => __("External links", 'rektic-wp'),
			"LI"                => __("Internal linkage", 'rektic-wp'),
			"LC"                => __("Length of the content", 'rektic-wp'),
			"LTSEO"             => __("SEO title length", 'rektic-wp'),
			"L"                 => __("Readability", 'rektic-wp'),
			"BHN"               => __("HN tags", 'rektic-wp'),
			"AM"                => __("Adding media", 'rektic-wp'),
			"BALT"              => __("Alt TAG", 'rektic-wp'),
			"DPU"               => __("Dimensions of the pictures", 'rektic-wp'),
			"DMCP_INF"          => __("Caution, the main keyword is not highlighted. The ideal case is to insert it {var1} times in this article",
									  'rektic-wp'),
			"DMCP_SUP"          => __("The main keyword appears {var1} times, beware of over-optimization! The maximum is {var2} times for this length.",
									  'rektic-wp'),
			"DMCP_OK"           => __("Perfect! Your target query appears {var1} times in your {wordsCount} words article.", 'rektic-wp'),
			"DMCP_PR"           => __("It must be inserted {var1} times in this article", 'rektic-wp'),
			"MCPI_OK"           => __("Good, your main keyword appears in the first sentence.", 'rektic-wp'),
			"MCPI_PRT_KW"       => __("Only a part of your main keyword appears in the first sentence.", 'rektic-wp'),
			"MCPI_KO"           => __("You must insert it in the first sentence of your article.", 'rektic-wp'),
			"MCST_KW_HN_ONE"    => __("Good, you have inserted the query once in an H2 or H3", 'rektic-wp'),
			"MCST_KW_HN_MLT"    => __("Perfect! The query appears twice in the H2 or H3.", 'rektic-wp'),
			"MCST_KW_SUROP"     => __("Beware of over-optimization, it is better to insert the query at most twice in the different H2 and H3..",
									  'rektic-wp'),
			"MCST_PRT_KW_HN"    => __("Only a part of your main keyword appears in the H2 or H3.", 'rektic-wp'),
			"MCST_NO_KW_HN"     => __("You must insert the main keyword at least once in the H2 or H3.", 'rektic-wp'),
			"MCPMD_MD_KW_OK"    => __("Good, your main keyword appears in the meta-description.", 'rektic-wp'),
			"MCPMD_MD_KW_KO"    => __("Caution, you must insert the main keyword in the meta-description.", 'rektic-wp'),
			"MCPMD_MD_KW_SUROP" => __("Beware of over-optimization. The main keyword must be inserted only once in the meta-description.",
									  'rektic-wp'),
			"RTSEO_MT_KW_OK"    => __("Good, your main keyword appears in the meta-title.", 'rektic-wp'),
			"RTSEO_MT_KW_KO"    => __("Caution, you must insert the main keyword in the meta-title.", 'rektic-wp'),
			"RTSEO_MT_KW_SUROP" => __("Beware of over-optimization. The main keyword must be inserted only once in the meta-title.",
									  'rektic-wp'),
			"LMD_OK"            => __("Perfect!", 'rektic-wp'),
			"LMD_PR"            => __("The length of the meta-description must be between 140 and 160.", 'rektic-wp'),
			"LMD_KO_INF"        => __("Caution, it must be at least 140 characters.", 'rektic-wp'),
			"LMD_KO_SUP"        => __("Caution, you must not exceed 160 characters.", 'rektic-wp'),
			"LI_OK"             => __("Good, the article contains external links!", 'rektic-wp'),
			"LI_KO"             => __("Caution, you must insert at least one external link.", 'rektic-wp'),
			"LE_OK"             => __("Good, the article contains internal links!", 'rektic-wp'),
			"LE_KO"             => __("Caution, you must insert at least one internal link.", 'rektic-wp'),
			"LC_OK"             => __("Good, you have written a content of {wordsCount} words.", 'rektic-wp'),
			"LC_PR"             => __("Quantity plays an important role in SEO; try to reach at least 1000 words!", 'rektic-wp'),
			"LC_KO"             => __("Be careful, the content is very short; try to reach at least the 1000 words!", 'rektic-wp'),
			"LTSEO_OK"          => __("Good!", 'rektic-wp'),
			"LTSEO_SUP"         => __("Be careful, too long!", 'rektic-wp'),
			"LTSEO_INF"         => __("Be careful, too short!", 'rektic-wp'),
			"L_OK"              => __("According to the Flesch test, your readability score is {var1}. Congratulations, your article is easy to read.",
									  'rektic-wp'),
			"L_PR"              => __("According to the Flesch test, your score is {var1}... Try to shorten long sentences or multiply lists.",
									  'rektic-wp'),
			"L_KO"              => __("According to the Flesch test, your score is {var1}... Beware, your article is difficult to read. Try to shorten long sentences and multiply lists.",
									  'rektic-wp'),
			"BHN_OK"            => __("Good for you, there are enough H2 and H3!", 'rektic-wp'),
			"BHN_INF_H3"        => __("Caution! You have no H3s, add at least one.", 'rektic-wp'),
			"BHN_INF_H2"        => __("Caution! You need at least two H2s.", 'rektic-wp'),
			"AM_OK"             => __("Perfect, good job !", 'rektic-wp'),
			"AM_PR"             => __("You need at least 4 media per article (images and videos)", 'rektic-wp'),
			"AM_KO"             => __("You need to illustrate the article with images and videos, at least 4 media per article.",
									  'rektic-wp'),
			"BALT_OK"           => __("Good, you have filled in the ALT tag correctly", 'rektic-wp'),
			"BALT_OK_NO_KW_ALT" => __("Good, you filled in the alt tag but you need to insert the main keyword ", 'rektic-wp'),
			"BALT_NO_ALT"       => __("It is essential to fill in the ALT tag without forgetting to insert the main keyword.", 'rektic-wp'),
			"BALT_NO_IMG"       => __("Insert an image on the front page and fill in the ALT tag", 'rektic-wp'),
			"DPU_OK"            => __("Good, all the images inserted are 600 px wide", 'rektic-wp'),
			"DPU_KO"            => __("Attention, the width of the inserted images must be 600px.", 'rektic-wp'),
			"IMG_BALT_OK"       => __("Good, you have filled in the ALT tag", 'rektic-wp'),
			"IMG_BALT_KO"       => __("It is essential to fill in the ALT tag", 'rektic-wp'),
		];
		add_action("wp_ajax_rektic_step1", [
			$this,
			"step1",
		]);

		add_action("wp_ajax_rektic_get_h1s", [
			$this,
			"getH1s",
		]);

		add_action("wp_ajax_rektic_get_h2s", [
			$this,
			"getH2s",
		]);

		add_action("wp_ajax_rektic_get_h2s", [
			$this,
			"getH2s",
		]);

		add_action("wp_ajax_rektic_select_info", [
			$this,
			"selectArticleInfo",
		]);

		add_action("wp_ajax_rektic_generate_more", [
			$this,
			"generateMoreContent",
		]);

		add_action("wp_ajax_rektic_generate_rewrite", [
			$this,
			"generateReWrite",
		]);

		add_action("wp_ajax_rektic_generate_intro", [
			$this,
			"generateIntro",
		]);

		add_action("wp_ajax_rektic_suggested-links", [
			$this,
			"getSuggestedLinks",
		]);

		add_action("wp_ajax_rektic_suggested-keywords", [
			$this,
			"getSuggestedKeywords",
		]);

		add_action("wp_ajax_rektic_suggested-seo", [
			$this,
			"getSuggestedSEO",
		]);

		add_action("wp_ajax_rektic_suggested-most-asked", [
			$this,
			"getMostAsked",
		]);

		add_action("wp_ajax_rektic_suggested-articles", [
			$this,
			"getRelatedArticles",
		]);

		add_action("wp_ajax_rektic_save_article", [
			$this,
			"saveAticle",
		]);

		add_action("wp_ajax_rektic_get_seo_score", [
			$this,
			"getSeoScore",
		]);

		add_action("wp_ajax_rektic_request_plagia", [
			$this,
			"generatePlagia",
		]);

		add_action("wp_ajax_rektic_download_plagia", [
			$this,
			"downloadPlagia",
		]);

		add_action("wp_ajax_rektic_get_article", [
			$this,
			"getArticle",
		]);

		add_action("wp_ajax_rektic_export_article", [
			$this,
			"exportArticle",
		]);
	}

	public function step1(){
		if( ! wp_verify_nonce($_REQUEST['nonce'], "step1")){
			exit(__("Not enough power you wield", 'rektic-wp'));
		}
		$else   = true;
		$result = ['type' => 'success'];
		$params = [];

		$keywords = sanitize_text_field($_POST['keywords']);
		$country  = sanitize_text_field($_POST['country']);
		$language = sanitize_text_field($_POST['language']);

		if( ! empty($keywords) && ! empty($language) && ! empty($country)){
			$body = json_encode([
									"keyword" => $keywords,
									"lang"    => $language,
									"country" => $country,
								]);

			$headers = [
				'Authorization' => 'Basic ' . $this->rektic_settings_options['api_token_0'],
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			];
			$timeout = $this->plugin->apiTimeout();
			$url     = $this->plugin->apiURL() . '/articles';

			$request = wp_remote_post(add_query_arg($params, $url), compact('body', 'headers', 'timeout'));

			if( ! is_wp_error($request) && '201' == wp_remote_retrieve_response_code($request)){
				$article = json_decode(wp_remote_retrieve_body($request));
				if( ! empty($article) && $article->step == "INIT"){
					$result['id']      = $article->id;
					$result['created'] = $article->createdAt;
					$else              = false;
				}
				if($else){
					$result['message'] = __('This article does not exist!', 'rektic-wp');
				}
			}
			else{
				$result['message'] = __('Could not contact the server!', 'rektic-wp');
			}
		}
		else{
			$result['message'] = __('Some data is missing!', 'rektic-wp');
		}

		if($else){
			$result['type'] = 'failure';
		}

		header('Content-Type: application/json');
		echo wp_kses_post(wp_json_encode($result));
		die();
	}

	public function getH1s(){
		if( ! wp_verify_nonce($_REQUEST['nonce'], "step2")){
			exit(__("Not enough power you wield", 'rektic-wp'));
		}
		$else   = true;
		$result = ['type' => 'success'];
		$params = [];

		// IDs are not numeric values
		$id = sanitize_text_field($_POST['id']);

		if( ! empty($id)){
			$headers = [
				'Authorization' => 'Basic ' . $this->rektic_settings_options['api_token_0'],
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			];
			$timeout = $this->plugin->apiTimeout();
			$url     = $this->plugin->apiURL() . '/articles/' . $id . '/h1';

			$request = wp_remote_get(add_query_arg($params, $url), compact('headers', 'timeout'));

			if( ! is_wp_error($request) && '200' == wp_remote_retrieve_response_code($request)){
				$article = json_decode(wp_remote_retrieve_body($request));
				if( ! empty($article)){
					$result['heads'] = $article->h1List;
					$else            = false;
				}
				if($else){
					$result['message'] = __('This article does not exist!', 'rektic-wp');
				}
			}
			else{
				$result['message'] = __('Could not contact the server!', 'rektic-wp');
			}
		}
		else{
			$result['message'] = __('Some data is missing!', 'rektic-wp');
		}

		if($else){
			$result['type'] = 'failure';
		}

		header('Content-Type: application/json');
		echo wp_kses_post(wp_json_encode($result));
		die();
	}

	public function getH2s(){
		if( ! wp_verify_nonce($_REQUEST['nonce'], "step2")){
			exit(__("Not enough power you wield", 'rektic-wp'));
		}
		$else   = true;
		$result = ['type' => 'success'];
		$params = [];

		$id   = sanitize_text_field($_POST['id']);
		$h1   = sanitize_text_field($_POST['h1']);
		$h1id = sanitize_text_field($_POST['h1id']);

		if( ! empty($id) && ! empty($h1) && ! empty($h1id) && is_numeric($h1id)){
			$headers = [
				'Authorization' => 'Basic ' . $this->rektic_settings_options['api_token_0'],
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			];
			$timeout = $this->plugin->apiTimeout();
			$body    = json_encode(['h1' => $h1]);
			$method  = 'PATCH';
			$url     = $this->plugin->apiURL() . '/articles/' . $id . '/h1/' . $h1id . '/h2';
			$urlMeta = $this->plugin->apiURL() . '/articles/' . $id . '/h1/' . $h1id . '/meta';

			$requestH2   = wp_remote_request(add_query_arg($params, $url), compact('timeout', 'body', 'method', 'headers'));
			$requestMeta = wp_remote_request(add_query_arg($params, $urlMeta), compact('timeout', 'body', 'method', 'headers'));

			if(( ! is_wp_error($requestH2) && '200' == wp_remote_retrieve_response_code($requestH2)) &&
			   ( ! is_wp_error($requestMeta) && '200' == wp_remote_retrieve_response_code($requestMeta))){
				$articleH2s  = json_decode(wp_remote_retrieve_body($requestH2));
				$articleMeta = json_decode(wp_remote_retrieve_body($requestMeta));
				if( ! empty($articleH2s) && ! empty($articleMeta)){
					$result['heads'] = $articleH2s->h2List;
					$result['metas'] = $articleMeta->metaList;
					$else            = false;
				}
				if($else){
					$result['message'] = __('This article does not exist!', 'rektic-wp');
				}
			}
			else{
				$result['message'] = __('Could not contact the server!', 'rektic-wp');
				$result['reqH2']   = $requestH2;
				$result['reqMeta'] = $requestMeta;
			}
		}
		else{
			$result['message'] = __('Some data is missing!', 'rektic-wp');
		}

		if($else){
			$result['type'] = 'failure';
		}

		header('Content-Type: application/json');
		echo wp_kses_post(wp_json_encode($result));
		die();
	}

	public function selectArticleInfo(){
		if( ! wp_verify_nonce($_REQUEST['nonce'], "step3")){
			exit(__("Not enough power you wield", 'rektic-wp'));
		}
		$else   = true;
		$result = ['type' => 'success'];
		$params = [];

		$info = Sanitize::recursiveSanitizeTextField($_POST['info']);
		$id   = sanitize_text_field($_POST['id']);

		if( ! empty($info) && ! empty($id)){
			$body = json_encode($info);

			$headers = [
				'Authorization' => 'Basic ' . $this->rektic_settings_options['api_token_0'],
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			];
			$method  = 'PATCH';
			$timeout = $this->plugin->apiTimeout();
			$url     = $this->plugin->apiURL() . '/articles/' . $id . '/head';

			$request = wp_remote_request(add_query_arg($params, $url), compact('method', 'body', 'headers', 'timeout'));

			if( ! is_wp_error($request) && '200' == wp_remote_retrieve_response_code($request)){
				$article = json_decode(wp_remote_retrieve_body($request));
				if( ! empty($article) && $article->step == "DRAFT"){
					$else = false;

					$postId = wp_insert_post([
												 'post_type'      => 'post',
												 'post_title'     => $article->h1,
												 'post_content'   => $article->content,
												 'post_status'    => 'draft',
												 'comment_status' => 'closed',
												 'ping_status'    => 'closed',
												 'meta_input'     => [
													 'rektic_article'      => true,
													 'rektic_article_id'   => $article->id,
													 'rektic_article_lang' => $article->lang,
													 'description'         => $article->metaDescription,
													 'title'               => $article->metaTitle,
												 ],
											 ]);

					$result['link'] = get_edit_post_link($postId);
				}
				if($else){
					$result['message'] = __('This article does not exist!', 'rektic-wp');
				}
			}
			else{
				$result['message'] = __('Could not contact the server!', 'rektic-wp');
			}
		}
		else{
			$result['message'] = __('Some data is missing!', 'rektic-wp');
		}

		if($else){
			$result['type'] = 'failure';
		}

		header('Content-Type: application/json');
		echo wp_kses_post(wp_json_encode($result));
		die();
	}

	public function generateMoreContent(){
		if( ! wp_verify_nonce($_REQUEST['nonce'], "metaboxnonce")){
			exit(__("Not enough power you wield", 'rektic-wp'));
		}

		$else   = true;
		$result = ['type' => 'success'];
		$params = [];

		$articleId = sanitize_text_field($_POST['id']);
		$text      = sanitize_textarea_field($_POST['text']);
		$language  = sanitize_text_field($_POST['language']);
		$type      = 'text';

		if( ! empty($articleId) && ! empty($language) && ! empty($text)){
			$body = json_encode(compact('articleId', 'language', 'type', 'text'));

			$headers = [
				'Authorization' => 'Basic ' . $this->rektic_settings_options['api_token_0'],
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			];
			$timeout = $this->plugin->apiTimeout();
			$url     = $this->plugin->apiURL() . '/generate/content';

			$request = wp_remote_post(add_query_arg($params, $url), compact('body', 'headers', 'timeout'));

			if( ! is_wp_error($request) && '200' == wp_remote_retrieve_response_code($request)){
				$response = json_decode(wp_remote_retrieve_body($request));
				if( ! empty($response) && ! empty($response->counter)){
					$result['counter'] = $response->counter;
					$result['text']    = $response->text;
					$else              = false;
				}
				if($else){
					$result['message'] = __('There was a problem!', 'rektic-wp');
				}
			}
			else{
				$result['message'] = __('Could not contact the server!', 'rektic-wp');
			}
		}
		else{
			$result['message'] = __('Some data is missing!', 'rektic-wp');
		}

		if($else){
			$result['type'] = 'failure';
		}

		header('Content-Type: application/json');
		echo wp_kses_post(wp_json_encode($result));
		die();
	}

	public function generateReWrite(){
		if( ! wp_verify_nonce($_REQUEST['nonce'], "metaboxnonce")){
			exit(__("Not enough power you wield", 'rektic-wp'));
		}

		$else   = true;
		$result = ['type' => 'success'];
		$params = [];

		$text     = sanitize_textarea_field($_POST['text']);
		$language = sanitize_text_field($_POST['language']);

		if( ! empty($language) && ! empty($text)){
			$body = json_encode(compact('language', 'text'));

			$headers = [
				'Authorization' => 'Basic ' . $this->rektic_settings_options['api_token_0'],
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			];
			$timeout = $this->plugin->apiTimeout();
			$url     = $this->plugin->apiURL() . '/generate/rewrite';

			$request = wp_remote_post(add_query_arg($params, $url), compact('body', 'headers', 'timeout'));

			if( ! is_wp_error($request) && '200' == wp_remote_retrieve_response_code($request)){
				$response = json_decode(wp_remote_retrieve_body($request));
				if( ! empty($response) && ! empty($response->counter)){
					$result['counter'] = $response->counter;
					$result['text']    = $response->text;
					$else              = false;
				}
				if($else){
					$result['message'] = __('There was a problem!', 'rektic-wp');
				}
			}
			else{
				$result['message'] = __('Could not contact the server!', 'rektic-wp');
			}
		}
		else{
			$result['message'] = __('Some data is missing!', 'rektic-wp');
		}

		if($else){
			$result['type'] = 'failure';
		}

		header('Content-Type: application/json');
		echo wp_kses_post(wp_json_encode($result));
		die();
	}

	public function generateIntro(){
		if( ! wp_verify_nonce($_REQUEST['nonce'], "metaboxnonce")){
			exit(__("Not enough power you wield", 'rektic-wp'));
		}

		$else   = true;
		$result = ['type' => 'success'];
		$params = [];

		$articleId = sanitize_text_field($_POST['id']);
		$text      = sanitize_textarea_field($_POST['text']);
		$language  = sanitize_text_field($_POST['language']);
		$type      = 'introduction';

		if( ! empty($articleId) && ! empty($language) && ! empty($text)){
			$body = json_encode(compact('articleId', 'language', 'type', 'text'));

			$headers = [
				'Authorization' => 'Basic ' . $this->rektic_settings_options['api_token_0'],
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			];
			$timeout = $this->plugin->apiTimeout();
			$url     = $this->plugin->apiURL() . '/generate/content';

			$request = wp_remote_post(add_query_arg($params, $url), compact('body', 'headers', 'timeout'));

			if( ! is_wp_error($request) && '200' == wp_remote_retrieve_response_code($request)){
				$response = json_decode(wp_remote_retrieve_body($request));
				if( ! empty($response) && ! empty($response->counter)){
					$result['counter'] = $response->counter;
					$result['text']    = $response->text;
					$else              = false;
				}
				if($else){
					$result['message'] = __('There was a problem!', 'rektic-wp');
				}
			}
			else{
				$result['message'] = __('Could not contact the server!', 'rektic-wp');
			}
		}
		else{
			$result['message'] = __('Some data is missing!', 'rektic-wp');
		}

		if($else){
			$result['type'] = 'failure';
		}

		header('Content-Type: application/json');
		echo wp_kses_post(wp_json_encode($result));
		die();
	}

	public function getSuggestedLinks(){
		if( ! wp_verify_nonce($_REQUEST['nonce'], "metaboxnonce")){
			exit(__("Not enough power you wield", 'rektic-wp'));
		}
		$else   = true;
		$result = ['type' => 'success'];
		$params = [];
		$advanced = isset($_POST['advanced']);

		$id = sanitize_text_field($_POST['id']);

		if( ! empty($id)){
			$headers = [
				'Authorization' => 'Basic ' . $this->rektic_settings_options['api_token_0'],
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			];
			$timeout = $this->plugin->apiTimeout();
			$method  = 'PATCH';
			$url     = $this->plugin->apiURL() . '/articles/' . $id . '/suggestedLinks';
			if($advanced){
				$url .= 'Seo';
			}

			$request = wp_remote_request(add_query_arg($params, $url), compact('timeout', 'method', 'headers'));

			if( ! is_wp_error($request) && '200' == wp_remote_retrieve_response_code($request)){
				$response = json_decode(wp_remote_retrieve_body($request));
				if( ! empty($response) && $response->code == "SUCCESS"){
					$result['links'] = $response->message;
					$else            = false;
				}
				if($else){
					$result['message'] = __('This article does not exist!', 'rektic-wp');
				}
			}
			else{
				$result['message'] = __('Could not contact the server!', 'rektic-wp');
			}
		}
		else{
			$result['message'] = __('Some data is missing!', 'rektic-wp');
		}

		if($else){
			$result['type'] = 'failure';
		}

		header('Content-Type: application/json');
		echo wp_kses_post(wp_json_encode($result));
		die();
	}

	public function getSuggestedKeywords(){
		if( ! wp_verify_nonce($_REQUEST['nonce'], "metaboxnonce")){
			exit(__("Not enough power you wield", 'rektic-wp'));
		}
		$else   = true;
		$result = ['type' => 'success'];
		$params = [];

		$id = sanitize_text_field($_POST['id']);

		if( ! empty($id)){
			$headers = [
				'Authorization' => 'Basic ' . $this->rektic_settings_options['api_token_0'],
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			];
			$timeout = $this->plugin->apiTimeout();
			$method  = 'PATCH';
			$url     = $this->plugin->apiURL() . '/articles/' . $id . '/secondaryKeywords';

			$request = wp_remote_request(add_query_arg($params, $url), compact('timeout', 'method', 'headers'));

			if( ! is_wp_error($request) && '200' == wp_remote_retrieve_response_code($request)){
				$response = json_decode(wp_remote_retrieve_body($request));
				if( ! empty($response) && $response->code == "SUCCESS"){
					$result['country']  = $response->country;
					$date               = wp_date('F Y', strtotime($response->dateOfLastAnalyze));
					$result['date']     = $date;
					$result['keywords'] = $response->list;
					$else               = false;
				}
				if($else){
					$result['message'] = __('This article does not exist!', 'rektic-wp');
				}
			}
			else{
				$result['message'] = __('Could not contact the server!', 'rektic-wp');
			}
		}
		else{
			$result['message'] = __('Some data is missing!', 'rektic-wp');
		}

		if($else){
			$result['type'] = 'failure';
		}

		header('Content-Type: application/json');
		echo wp_kses_post(wp_json_encode($result));
		die();
	}

	public function getSuggestedSEO(){
		if( ! wp_verify_nonce($_REQUEST['nonce'], "metaboxnonce")){
			exit(__("Not enough power you wield", 'rektic-wp'));
		}
		$else   = true;
		$result = ['type' => 'success'];
		$params = [];

		$id       = sanitize_text_field($_POST['id']);
		$advanced = isset($_POST['advanced']);

		if( ! empty($id)){
			$headers = [
				'Authorization' => 'Basic ' . $this->rektic_settings_options['api_token_0'],
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			];
			$timeout = $this->plugin->apiTimeout();
			$method  = 'PATCH';
			$url     = $this->plugin->apiURL() . '/articles/' . $id . '/suggestedKeywordsSeo';
			if($advanced){
				$url .= 'Seo';
			}

			$request = wp_remote_request(add_query_arg($params, $url), compact('timeout', 'method', 'headers'));

			if( ! is_wp_error($request) && '200' == wp_remote_retrieve_response_code($request)){
				$response = json_decode(wp_remote_retrieve_body($request));
				if( ! empty($response) && $response->code == "SUCCESS"){
					$result['keywords'] = $response->data;
					$else               = false;
				}
				if($else){
					$result['message'] = __('This article does not exist!', 'rektic-wp');
				}
			}
			else{
				$result['message'] = __('Could not contact the server!', 'rektic-wp');
			}
		}
		else{
			$result['message'] = __('Some data is missing!', 'rektic-wp');
		}

		if($else){
			$result['type'] = 'failure';
		}

		header('Content-Type: application/json');
		echo wp_kses_post(wp_json_encode($result));
		die();
	}

	public function getMostAsked(){
		if( ! wp_verify_nonce($_REQUEST['nonce'], "metaboxnonce")){
			exit(__("Not enough power you wield", 'rektic-wp'));
		}
		$else   = true;
		$result = ['type' => 'success'];
		$params = [];

		$id = sanitize_text_field($_POST['id']);

		if( ! empty($id)){
			$headers = [
				'Authorization' => 'Basic ' . $this->rektic_settings_options['api_token_0'],
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			];
			$timeout = $this->plugin->apiTimeout();
			$method  = 'PATCH';
			$url     = $this->plugin->apiURL() . '/articles/' . $id . '/mostAskedQuestions';

			$request = wp_remote_request(add_query_arg($params, $url), compact('timeout', 'method', 'headers'));

			if( ! is_wp_error($request) && '200' == wp_remote_retrieve_response_code($request)){
				$response = json_decode(wp_remote_retrieve_body($request));
				if( ! empty($response) && $response->code == "SUCCESS"){
					$result['keywords'] = $response->message;
					$else               = false;
				}
				if($else){
					$result['message'] = __('This article does not exist!', 'rektic-wp');
				}
			}
			else{
				$result['message'] = __('Could not contact the server!', 'rektic-wp');
			}
		}
		else{
			$result['message'] = __('Some data is missing!', 'rektic-wp');
		}

		if($else){
			$result['type'] = 'failure';
		}

		header('Content-Type: application/json');
		echo wp_kses_post(wp_json_encode($result));
		die();
	}

	public function saveAticle(){
		if( ! wp_verify_nonce($_REQUEST['nonce'], "metaboxnonce")){
			exit(__("Not enough power you wield", 'rektic-wp'));
		}
		$else   = true;
		$result = ['type' => 'success'];
		$params = [];

		$content = wp_kses($_POST['content'], $this->allowedHtml);
		$id      = sanitize_text_field($_POST['id']);

		if( ! empty($content) && ! empty($id)){
			$body = json_encode(compact('content'));

			$headers = [
				'Authorization' => 'Basic ' . $this->rektic_settings_options['api_token_0'],
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			];
			$method  = 'PATCH';
			$timeout = $this->plugin->apiTimeout();
			$url     = $this->plugin->apiURL() . '/articles/' . $id . '/content';

			$request = wp_remote_request(add_query_arg($params, $url), compact('method', 'body', 'headers', 'timeout'));

			if( ! is_wp_error($request) && '200' == wp_remote_retrieve_response_code($request)){
				$article = json_decode(wp_remote_retrieve_body($request));
				if( ! empty($article) && $article->code == "SUCCESS"){
					$else = false;

					$result['saved'] = true;
				}
				if($else){
					$result['message'] = __('This article does not exist!', 'rektic-wp');
				}
			}
			else{
				$result['message'] = __('Could not contact the server!', 'rektic-wp');
			}
		}
		else{
			$result['message'] = __('Some data is missing!', 'rektic-wp');
		}

		if($else){
			$result['type'] = 'failure';
		}

		header('Content-Type: application/json');
		echo wp_kses_post(wp_json_encode($result));
		die();
	}

	public function getSeoScore(){
		if( ! wp_verify_nonce($_REQUEST['nonce'], "metaboxnonce")){
			exit(__("Not enough power you wield", 'rektic-wp'));
		}
		$else   = true;
		$result = ['type' => 'success'];
		$params = [];

		$content = wp_kses($_POST['content'], $this->allowedHtml);
		$id      = sanitize_text_field($_POST['id']);

		if( ! empty($content) && ! empty($id)){
			$words      = str_word_count(strip_tags($content));
			$regenerate = isset($_POST['mustRegen']);
			$body       = json_encode(compact('words', 'regenerate'));

			$headers = [
				'Authorization' => 'Basic ' . $this->rektic_settings_options['api_token_0'],
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			];
			$method  = 'PATCH';
			$timeout = $this->plugin->apiTimeout();
			$url     = $this->plugin->apiURL() . '/articles/' . $id . '/seo';

			$request = wp_remote_request(add_query_arg($params, $url), compact('method', 'body', 'headers', 'timeout'));

			if( ! is_wp_error($request) && '200' == wp_remote_retrieve_response_code($request)){
				$article = json_decode(wp_remote_retrieve_body($request));
				if( ! empty($article) && $article->code == "SUCCESS"){
					$else = false;

					$result['score'] = $article->message->SCORE->meta[0]->prc;
					$result['info']  = [];
					$infoSEO         = $article->message;
					unset($infoSEO->SCORE);
					foreach($infoSEO as $key => $value){
						//$value[0]->meta[0]->msg = $this->seoMessages[$value[0]->meta[0]->msg];
						$newVal               = [
							"msg"  => $this->seoMessages[$value[0]->meta[0]->msg],
							"status"=>$value[0]->status,
							"vars" => (function() use ($value){
								unset($value[0]->meta[0]->msg);

								return $value[0]->meta   [0];
							})(),
						];
						$key                  = $this->seoMessages[$key];
						$result['info'][$key] = $newVal;
					}
				}
				if($else){
					$result['message'] = __('This article does not exist!', 'rektic-wp');
				}
			}
			else{
				$result['message'] = __('Could not contact the server!', 'rektic-wp');
			}
		}
		else{
			$result['message'] = __('Some data is missing!', 'rektic-wp');
		}

		if($else){
			$result['type'] = 'failure';
		}

		header('Content-Type: application/json');
		echo wp_kses_post(wp_json_encode($result));
		die();
	}

	public function generatePlagia(){
		if( ! wp_verify_nonce($_REQUEST['nonce'], "metaboxnonce")){
			exit(__("Not enough power you wield", 'rektic-wp'));
		}
		$else   = true;
		$result = ['type' => 'success'];
		$params = [];

		$content = wp_kses($_POST['content'], $this->allowedHtml);
		$id      = sanitize_text_field($_POST['id']);

		if( ! empty($content) && ! empty($id)){
			$text       = (strip_tags($content));
			$regenerate = isset($_POST['mustRegen']);
			$body       = json_encode(compact('text', 'regenerate'));

			$headers = [
				'Authorization' => 'Basic ' . $this->rektic_settings_options['api_token_0'],
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			];
			$method  = 'PATCH';
			$timeout = $this->plugin->apiTimeout();
			$url     = $this->plugin->apiURL() . '/articles/' . $id . '/plagiarism';

			$request = wp_remote_request(add_query_arg($params, $url), compact('method', 'body', 'headers', 'timeout'));

			if( ! is_wp_error($request) && '200' == wp_remote_retrieve_response_code($request)){
				$article = json_decode(wp_remote_retrieve_body($request));
				if( ! empty($article) && $article->code == "SUCCESS"){
					$else = false;

					$result['upload'] = $article->uploadId ?? '';
				}
				else if( ! empty($article) && $article->code == "NO-REPORT"){
					$else = false;
				}
				if($else){
					$result['message'] = __('This article does not exist!', 'rektic-wp');
				}
			}
			else{
				$result['message'] = __('Could not contact the server!', 'rektic-wp');
			}
		}
		else{
			$result['message'] = __('Some data is missing!', 'rektic-wp');
		}

		if($else){
			$result['type'] = 'failure';
		}

		header('Content-Type: application/json');
		echo wp_kses_post(wp_json_encode($result));
		die();
	}

	public function downloadPlagia(){
		if( ! wp_verify_nonce($_REQUEST['nonce'], "metaboxnonce")){
			exit(__("Not enough power you wield", 'rektic-wp'));
		}
		$else   = true;
		$result = ['type' => 'success'];
		$params = [];

		// Both IDs are strings
		$id    = sanitize_text_field($_POST['id']);
		$pdfId = sanitize_text_field($_POST['pdfId']);

		if( ! empty($id)){
			$headers = [
				'Authorization' => 'Basic ' . $this->rektic_settings_options['api_token_0'],
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			];
			$timeout = $this->plugin->apiTimeout();
			$url     = $this->plugin->apiURL() . '/articles/' . $id . '/plagiarism/pdf/' . $pdfId;

			$request = wp_remote_get(add_query_arg($params, $url), compact('headers', 'timeout'));

			if( ! is_wp_error($request) && '200' == wp_remote_retrieve_response_code($request)){
				$article = (wp_remote_retrieve_body($request));
				if( ! empty($article)){
					header('content-type: application/pdf');
					header('content-disposition: attachment; filename=' . $pdfId . '.pdf"');
					header("Content-Length: " . strlen($article));
					echo $article;
					die();
				}
				if($else){
					$result['message'] = __('This article does not exist!', 'rektic-wp');
				}
			}
			else{
				$result['message'] = __('Could not contact the server!', 'rektic-wp');
			}
		}
		else{
			$result['message'] = __('Some data is missing!', 'rektic-wp');
		}

		if($else){
			$result['type'] = 'failure';
		}

		header('Content-Type: application/json');
		echo wp_kses_post(wp_json_encode($result));
		die();
	}

	public function getArticle(){
		if( ! wp_verify_nonce($_REQUEST['nonce'], "export-rektic")){
			exit(__("Not enough power you wield", 'rektic-wp'));
		}
		$else   = true;
		$result = ['type' => 'success'];
		$params = [];

		$id = sanitize_text_field($_POST['id']);

		if( ! empty($id)){
			$headers = [
				'Authorization' => 'Basic ' . $this->rektic_settings_options['api_token_0'],
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			];
			$timeout = $this->plugin->apiTimeout();
			$url     = $this->plugin->apiURL() . '/articles/' . $id;

			$request = wp_remote_get(add_query_arg($params, $url), compact('headers', 'timeout'));

			if( ! is_wp_error($request) && '200' == wp_remote_retrieve_response_code($request)){
				$article = json_decode(wp_remote_retrieve_body($request));
				if( ! empty($article)){
					$result['step']            = $article->step;
					$result['lang']            = $article->lang;
					$result['keyword']         = $article->keyword;
					$result['country']         = $article->country;
					$result['metaDescription'] = $article->metaDescription ?? '';
					$result['metaTitle']       = $article->metaTitle ?? '';
					$else                      = false;
				}
				if($else){
					$result['message'] = __('This article does not exist!', 'rektic-wp');
				}
			}
			else{
				$result['message'] = __('Could not contact the server!', 'rektic-wp');
			}
		}
		else{
			$result['message'] = __('Some data is missing!', 'rektic-wp');
		}

		if($else){
			$result['type'] = 'failure';
		}

		header('Content-Type: application/json');
		echo wp_kses_post(wp_json_encode($result));
		die();
	}

	public function exportArticle(){
		if( ! wp_verify_nonce($_REQUEST['nonce'], "export-rektic")){
			exit(__("Not enough power you wield", 'rektic-wp'));
		}
		$else   = true;
		$result = ['type' => 'success'];
		$params = [];

		$id = sanitize_text_field($_POST['id']);

		if( ! empty($id)){
			$headers = [
				'Authorization' => 'Basic ' . $this->rektic_settings_options['api_token_0'],
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			];
			$timeout = $this->plugin->apiTimeout();
			$url     = $this->plugin->apiURL() . '/articles/' . $id;

			$request = wp_remote_get(add_query_arg($params, $url), compact('headers', 'timeout'));

			if( ! is_wp_error($request) && '200' == wp_remote_retrieve_response_code($request)){
				$article = json_decode(wp_remote_retrieve_body($request));
				if( ! empty($article) && $article->step == "DRAFT"){
					$else = false;

					$postId = wp_insert_post([
												 'post_type'      => 'post',
												 'post_title'     => $article->h1,
												 'post_content'   => $article->content,
												 'post_status'    => 'draft',
												 'comment_status' => 'closed',
												 'ping_status'    => 'closed',
												 'meta_input'     => [
													 'rektic_article'      => true,
													 'rektic_article_id'   => $article->id,
													 'rektic_article_lang' => $article->lang,
													 'description'         => $article->metaDescription,
													 'title'               => $article->metaTitle,
												 ],
											 ]);

					$result['link'] = get_edit_post_link($postId);
				}
				if($else){
					$result['message'] = __('This article does not exist!', 'rektic-wp');
				}
			}
			else{
				$result['message'] = __('Could not contact the server!', 'rektic-wp');
			}
		}
		else{
			$result['message'] = __('Some data is missing!', 'rektic-wp');
		}

		if($else){
			$result['type'] = 'failure';
		}

		header('Content-Type: application/json');
		echo wp_kses_post(wp_json_encode($result));
		die();
	}
}
