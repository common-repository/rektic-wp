<?php
/**
 * Rektic WP
 *
 * Here we are going to display the pages constituting the plugin
 *
 * @package   rektic-wp
 * @author    SAKHRAOUI Omar <info@omarion.me>
 * @copyright 2022 Rektic WP
 * @license   GPLv3
 * @link      https://omarion.me
 */

namespace RekticWp\App\Backend;

use RekticWp\Common\Abstracts\Base;
use function add_menu_page;
use function add_query_arg;
use function add_submenu_page;
use function compact;
use function is_wp_error;
use function json_decode;
use function plugins_url;
use function wp_create_nonce;
use function wp_enqueue_script;
use function wp_localize_script;
use function wp_remote_get;
use function wp_remote_retrieve_body;
use function wp_remote_retrieve_response_code;
use const REKTIC_WP_PLUGIN_FILE;

class MenuPages extends Base{

	private $rektic_settings_options;
	private $rekticArticles;

	/**
	 * Initialize the class.
	 *
	 * @since 1.0.0
	 */
	public function init(){
		global $pagenow;

		/**
		 * This backend class is only being instantiated in the backend as requested in the Bootstrap class
		 *
		 * @see Requester::isAdminBackend()
		 * @see Bootstrap::__construct
		 *
		 * Add plugin code here for admin settings specific functions
		 */
		$this->rektic_settings_options = get_option('rektic_settings_option_name');
		if(isset($_GET['page']) && $pagenow === 'admin.php' && $_GET['page'] === 'rektic-wp'){
			$this->getArticlesList();
		}
		add_action('admin_menu', [
			$this,
			'registerMenuPages',
		]);
		add_filter('submenu_file', [
			$this,
			'hideEditMenu',
		]);
	}

	private function getArticlesList(){
		$key    = 'rektic-wp-articles_';
		$output = get_transient($key);
		if(false === $output){
			$params   = [
				'status' => 'ACTIVE',
			];
			$headers  = [
				'Authorization' => 'Basic ' . $this->rektic_settings_options['api_token_0'],
			];
			$url      = $this->plugin->apiURL() . '/articles';
			$lifetime = 60;

			$request = wp_remote_get(add_query_arg($params, $url), compact('headers'));

			if(is_wp_error($request) || '200' != wp_remote_retrieve_response_code($request)){
				$this->rekticArticles = [];

				return;
			}

			$events = json_decode(wp_remote_retrieve_body($request));
			if(empty($events)){
				$this->rekticArticles = [];

				return;
			}

			$output = $events;

			set_transient($key, $output, $lifetime);
		}

		$this->rekticArticles = $output;
	}

	public function registerMenuPages(){
		$capability = 'publish_posts';
		$icon       = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjEiIGhlaWdodD0iMjEiIHZpZXdCb3g9IjAgMCAyMSAyMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZmlsbD0iIzAwMkY4NCIgZD0iTTAsMTcuOVYyLjdDMCwxLjIsMS4yLDAsMi42LDBoMTUuMmMyLjQsMCwzLjUsMi44LDEuOSw0LjVMNC41LDE5LjhDMi44LDIxLjQsMCwyMC4yLDAsMTcuOXoiLz4KPGNpcmNsZSBmaWxsPSIjMWM3M2U3IiBjeD0iMTciIGN5PSIxNi44IiByPSI0Ii8+Cjwvc3ZnPgo=';

		add_menu_page(__("My Rektic", 'rektic-wp'), __("My Rektic Articles", 'rektic-wp'), $capability, 'rektic-wp', [
			$this,
			"rekticArticlesPage",
		], $icon);
		add_submenu_page('rektic-wp', __("Create A New Article", 'rektic-wp'), __("Create A New Article", 'rektic-wp'), $capability,
						 'rektic-wp' . '-article-new', [
							 $this,
							 "rekticNewArticlePage",
						 ]);
		add_submenu_page('rektic-wp', __("Edit Article", 'rektic-wp'), __("Edit Article", 'rektic-wp'), $capability,
						 'rektic-wp' . '-article-edit', [
							 $this,
							 "rekticNewArticlePage",
						 ]);
	}

	public function hideEditMenu($submenu_file){
		global $plugin_page;

		$hidden_submenus = [
			'rektic-wp' . '-article-edit' => true,
		];

		// Select another submenu item to highlight (optional).
		if($plugin_page && isset($hidden_submenus[$plugin_page])){
			$submenu_file = 'rektic-wp';
		}

		// Hide the submenu.
		foreach($hidden_submenus as $submenu => $unused){
			remove_submenu_page('rektic-wp', $submenu);
		}

		return $submenu_file;
	}

	public function rekticArticlesPage(){
		if($this->preflight()){
			$nonce = wp_create_nonce("export-rektic");

			$js = [
				'deps'      => ['jquery'],
				'handle'    => 'plugin-list-backend-js',
				'in_footer' => true,
				'source'    => plugins_url('/assets/public/js/art-list.js', REKTIC_WP_PLUGIN_FILE),
				'version'   => $this->plugin->version(),
			];
			wp_enqueue_script($js['handle'], $js['source'], $js['deps'], $js['version'], $js['in_footer']);

			wp_localize_script('plugin-list-backend-js', 'rekticArtList', [
				'nonce' => $nonce,
			]);

			include $this->plugin->templatePath() . '/articles-list.php';
		}
		else{
			include $this->plugin->templatePath() . '/access-denied.php';
		}
	}

	private function preflight(): bool{
		$headers = [
			'Authorization' => 'Basic ' . $this->rektic_settings_options['api_token_0'],
			'Content-Type'  => 'application/json',
			'Accept'        => 'application/json',
		];
		$timeout = $this->plugin->apiTimeout();
		$url     = $this->plugin->saasURL() . '/account';
		$request = wp_remote_get($url, compact('headers', 'timeout'));

		return ( ! is_wp_error($request) && '200' == wp_remote_retrieve_response_code($request));
	}

	public function rekticNewArticlePage(){
		if($this->preflight()){
			$nonce1 = wp_create_nonce("step1");
			$nonce2 = wp_create_nonce("step2");
			$nonce3 = wp_create_nonce("step3");
			$nonce4 = wp_create_nonce("step4");
			$isNew  = $_GET['page'] === 'rektic-wp' . '-article-new';
			include $this->plugin->templatePath() . '/article-edit.php';
		}
		else{
			include $this->plugin->templatePath() . '/access-denied.php';
		}
	}
}
