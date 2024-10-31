<?php
/**
 * Rektic WP
 *
 * I18N file loader
 *
 * @package   rektic-wp
 * @author    SAKHRAOUI Omar <info@omarion.me>
 * @copyright 2022 Rektic WP
 * @license   GPLv3
 * @link      https://omarion.me
 */

declare(strict_types=1);

namespace RekticWp\Config;

use RekticWp\Common\Abstracts\Base;
use function dirname;
use function load_plugin_textdomain;
use function plugin_basename;
use const REKTIC_WP_PLUGIN_FILE;

/**
 * Internationalization and localization definitions
 *
 * @package RekticWp\Config
 * @since   1.0.0
 */
final class I18n extends Base{
	/**
	 * Load the plugin text domain for translation
	 * @docs https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#loading-text-domain
	 *
	 * @since 1.0.0
	 */
	public function load(){
		$textDomain        = 'rektic-wp';
		$languageFilesPath = dirname(plugin_basename(REKTIC_WP_PLUGIN_FILE)) . '/languages';
		load_plugin_textdomain($textDomain, false, $languageFilesPath);
	}
}
