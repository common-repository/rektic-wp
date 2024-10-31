<?php
/**
 * Rektic WP
 *
 * @package   rektic-wp
 * @author    Rektic <info@rektic.ai>
 * @copyright 2023 Rektic
 * @license   GPLv3
 * @link      https://rektic.ai
 *
 * Plugin Name:     Rektic WP
 * Plugin URI:      https://rektic.ai
 * Description:     Rektic WP plugin
 * Version:         1.1.5
 * Author:          Rektic
 * Author URI:      https://rektic.ai
 * Text Domain:     rektic-wp
 * Domain Path:     /languages
 * Requires PHP:    7.1
 * Requires WP:     5.6
 * Namespace:       RekticWp
 */

declare(strict_types=1);

/**
 * Define the default root file of the plugin
 *
 * @since 1.0.0
 */

use RekticWp\Bootstrap;
use RekticWp\Common\Functions;

const REKTIC_WP_PLUGIN_FILE = __FILE__;

/**
 * Load PSR4 autoloader
 *
 * @since 1.0.0
 */
$rektic_wp_autoloader = require plugin_dir_path(REKTIC_WP_PLUGIN_FILE) . 'vendor/autoload.php';

/**
 * Setup hooks (activation, deactivation, uninstall)
 *
 * @since 1.0.0
 */
register_activation_hook(__FILE__, [
	'RekticWp\Config\Setup',
	'activation',
]);
register_deactivation_hook(__FILE__, [
	'RekticWp\Config\Setup',
	'deactivation',
]);
register_uninstall_hook(__FILE__, [
	'RekticWp\Config\Setup',
	'uninstall',
]);

/**
 * Bootstrap the plugin
 *
 * @since 1.0.0
 */
if( ! class_exists('\RekticWp\Bootstrap')){
	wp_die(__('Rektic WP is unable to find the Bootstrap class.', 'rektic-wp'));
}
add_action('plugins_loaded', static function() use ($rektic_wp_autoloader){
	/**
	 * @see \RekticWp\Bootstrap
	 */
	try{
		new Bootstrap($rektic_wp_autoloader);
	}
	catch(Exception $e){
		wp_die(__('Rektic WP is unable to run the Bootstrap class.', 'rektic-wp'));
	}
});

/**
 * Create a main function for external uses
 *
 * @return Functions
 * @since 1.0.0
 */
function rektic_wp(): Functions{
	return new Functions();
}
