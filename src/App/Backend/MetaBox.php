<?php

/**
 * Rektic WP
 *
 * Metabox's are the WordPress' editor's part that we can plug GUI into
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
use function add_meta_box;
use function get_post_meta;
use function plugins_url;
use function wp_create_nonce;
use function wp_enqueue_script;
use function wp_localize_script;
use const REKTIC_WP_PLUGIN_FILE;

/**
 * Class Settings
 *
 * @package RekticWp\App\Backend
 * @since   1.0.0
 */
class MetaBox extends Base{
	private $rektic_settings_options;
	private $plan  = null;
	private $plans = [
		'free'    => "free",
		'solo'    => "price_1K7Ib6A5GGjmej2tytKMdMMN",
		'pro'     => "price_1K7IhUA5GGjmej2tFfGPwf2A",
		'company' => "price_1K7IiyA5GGjmej2tgRo3iVz2",
	];

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

		add_action('add_meta_boxes', [
			$this,
			'mainMetabox',
		]);
	}

	public function mainMetabox(): void{
		global $post;

		$this->initUserData();
		$isRektic = get_post_meta($post->ID, 'rektic_article', true);
		if($isRektic == 1){
			if($this->plan != null){
				$js = [
					'deps'      => ['jquery'],
					'handle'    => 'plugin-editor-backend-js',
					'in_footer' => true,
					'source'    => plugins_url('/assets/public/js/editor.js', REKTIC_WP_PLUGIN_FILE),
					'version'   => $this->plugin->version(),
				];
				wp_enqueue_script($js['handle'], $js['source'], $js['deps'], $js['version'], $js['in_footer']);

				wp_localize_script('plugin-editor-backend-js', 'rekticArticleMeta', [
					'id'   => get_post_meta($post->ID, 'rektic_article_id', true),
					'lang' => get_post_meta($post->ID, 'rektic_article_lang', true),
				]);
				wp_localize_script('plugin-editor-backend-js', 'rekticUser', [
					'plan' => $this->plan,
				]);
			}
			add_meta_box('rektic_main_metabox', __('Rektic Content Generator', 'rektic-wp'), [
				$this,
				'mainMetaboxDisplay',
			], 'post', 'side');
		}
	}

	private function initUserData(){
		$headers = [
			'Authorization' => 'Basic ' . $this->rektic_settings_options['api_token_0'],
			'Content-Type'  => 'application/json',
			'Accept'        => 'application/json',
		];
		$timeout = $this->plugin->apiTimeout();
		$url     = $this->plugin->saasURL() . '/account';
		$request = wp_remote_get($url, compact('headers', 'timeout'));

		if(!is_wp_error($request) && '200' == wp_remote_retrieve_response_code($request)){
			$response   = json_decode(wp_remote_retrieve_body($request))->data;
			$id         = $response->id;
			$url        .= "/plans";
			$params     = ["account" => $id];
			$activePlan = $response->plan;
			$request    = wp_remote_get(add_query_arg($params, $url), compact('headers', 'timeout'));

			if(!is_wp_error($request) && '200' == wp_remote_retrieve_response_code($request)){
				$response = json_decode(wp_remote_retrieve_body($request))->data;
				//$activePlan = $response->active;
				foreach($response->plans as $plan){
					if($activePlan == $plan->id){
						$this->plan = strtolower($plan->name_en);
					}
				}
			}
		}
	}

	public function mainMetaboxDisplay($post){
		if($this->plan == null){
			include $this->plugin->templatePath() . '/empty-metabox.php';
		}
		else{
			$rekticNonce = wp_create_nonce("metaboxnonce");
			include $this->plugin->templatePath() . '/metabox.php';
		}
	}
}
