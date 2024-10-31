<?php
/**
 * Rektic WP
 *
 * Here we enquee all the files (JS/CSS) needed throughout the plugin's runtime.
 * To disable caching just turn on WordPress' debug mode
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
use function plugins_url;
use function wp_enqueue_script;
use function wp_enqueue_style;
use function wp_register_style;
use const REKTIC_WP_PLUGIN_FILE;

/**
 * Class Enqueue
 *
 * @package RekticWp\App\Backend
 * @since   1.0.0
 */
class Enqueue extends Base{

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
		 * Add plugin code here
		 */
		add_action('admin_enqueue_scripts', [
			$this,
			'enqueueScripts',
		]);
	}

	/**
	 * Enqueue scripts
	 *
	 * @since 1.0.0
	 */
	public function enqueueScripts(){
		$cssFiles = [
			[
				'deps'    => [],
				'handle'  => 'plugin-name-backend-css',
				'media'   => 'all',
				'source'  => plugins_url('/assets/public/css/backend.css', REKTIC_WP_PLUGIN_FILE),
				'version' => $this->plugin->version(),
			],
			[
				'deps'    => [],
				'handle'  => 'plugin-dialog-backend-css',
				'media'   => 'all',
				'source'  => plugins_url('/assets/public/css/cxdialog.css', REKTIC_WP_PLUGIN_FILE),
				'version' => $this->plugin->version(),
			],
			[
				'deps'    => [],
				'handle'  => 'niceCountryInput-css',
				'media'   => 'all',
				'source'  => plugins_url('/assets/public/css/niceCountryInput.css', REKTIC_WP_PLUGIN_FILE),
				'version' => $this->plugin->version(),
			],
			[
				'deps'    => [],
				'handle'  => 'notification-css',
				'media'   => 'all',
				'source'  => plugins_url('/assets/public/css/mk-notifications.min.css', REKTIC_WP_PLUGIN_FILE),
				'version' => $this->plugin->version(),
			],
		];
		$jsFiles  = [
			[
				'deps'      => ['jquery'],
				'handle'    => 'plugin-dialog-backend-js',
				'in_footer' => true,
				'source'    => plugins_url('/assets/public/js/cxdialog.js', REKTIC_WP_PLUGIN_FILE),
				'version'   => $this->plugin->version(),
			],
			[
				'deps'      => ['jquery'],
				'handle'    => 'plugin-test-backend-js',
				'in_footer' => true,
				'source'    => plugins_url('/assets/public/js/backend.js', REKTIC_WP_PLUGIN_FILE),
				'version'   => $this->plugin->version(),
			],
			[
				'deps'      => ['jquery'],
				'handle'    => 'niceCountryInput-js',
				'in_footer' => true,
				'source'    => plugins_url('/assets/public/js/niceCountryInput.js', REKTIC_WP_PLUGIN_FILE),
				'version'   => $this->plugin->version(),
			],
			[
				'deps'      => ['jquery'],
				'handle'    => 'notifications-js',
				'in_footer' => true,
				'source'    => plugins_url('/assets/public/js/mk-notifications.min.js', REKTIC_WP_PLUGIN_FILE),
				'version'   => $this->plugin->version(),
			],
		];

		// Enqueue CSS
		wp_enqueue_style('thickbox');
		wp_register_style('Font_Awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css');
		wp_enqueue_style('Font_Awesome');
		foreach($cssFiles as $css){
			wp_enqueue_style($css['handle'], $css['source'], $css['deps'], $css['version'], $css['media']);
		}

		// Enqueue JS
		wp_enqueue_script('thickbox');
		foreach($jsFiles as $js){
			wp_enqueue_script($js['handle'], $js['source'], $js['deps'], $js['version'], $js['in_footer']);
		}
		wp_localize_script('plugin-test-backend-js', 'rektic', [
			'ajaxurl'   => admin_url('admin-ajax.php'),
			'interwind' => plugins_url('/assets/public/images/logo.png', REKTIC_WP_PLUGIN_FILE),
		]);
	}
}
