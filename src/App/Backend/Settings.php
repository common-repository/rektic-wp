<?php
/**
 * Rektic WP
 *
 * Here is just the code to display and save, maybe manipulate, the plugin's settings
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
use function add_options_page;
use function array_unshift;
use function plugin_basename;

/**
 * Class Settings
 *
 * @package RekticWp\App\Backend
 * @since   1.0.0
 */
class Settings extends Base{
	private $rektic_settings_options;

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
		add_action('admin_init', [
			$this,
			'register_settings',
		]);
		add_action('admin_menu', [
			$this,
			'registerSettingsPage',
		]);

		add_filter('plugin_action_links', [
			$this,
			'pluginActions',
		], 10, 2);
	}

	public function pluginActions($links, $file){
		static $this_plugin;
		if( ! $this_plugin){
			$this_plugin = plugin_basename(REKTIC_WP_PLUGIN_FILE);
		}

		if($file == $this_plugin){
			$url           = admin_url() . 'options-general.php?page=settings-rektic';
			$settings_link = '<a href="' . $url . '">Settings</a>';
			array_unshift($links, $settings_link);
		}

		return $links;
	}

	public function register_settings(){
		register_setting('rektic_settings_option_group', 'rektic_settings_option_name', [
			$this,
			'rektic_settings_sanitize',
		]);

		add_settings_section('rektic_settings_setting_section', 'API Settings', [
			$this,
			'rektic_settings_section_info',
		], 'rektic-settings-admin');

		add_settings_field('api_token_0', 'API Key', [
			$this,
			'api_token_0_callback',
		], 'rektic-settings-admin', 'rektic_settings_setting_section');
	}

	public function registerSettingsPage(){
		add_options_page(__('Rektic Settings', 'rektic-wp'), __('Rektic Settings', 'rektic-wp'), 'manage_options', 'settings-rektic', [
			$this,
			'showSettingsPage',
		]);
	}

	public function showSettingsPage(){
		include $this->plugin->templatePath() . '/settings.php';
	}

	public function rektic_settings_sanitize($input): array{
		$sanitary_values = [];
		if(isset($input['api_token_0'])){
			$sanitary_values['api_token_0'] = esc_textarea($input['api_token_0']);
		}

		return $sanitary_values;
	}

	public function rektic_settings_section_info(){
	}

	public function api_token_0_callback(){
		printf('<input class="large-text" name="rektic_settings_option_name[api_token_0]" id="api_token_0" value="%s"/>',
			   isset($this->rektic_settings_options['api_token_0']) ? esc_attr($this->rektic_settings_options['api_token_0']) : '');
	}
}
