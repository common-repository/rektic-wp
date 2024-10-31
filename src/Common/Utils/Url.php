<?php

/**
 * Rektic WP
 *
 * URL generator Utility class for WP
 *
 * @package   rektic-wp
 * @author    SAKHRAOUI Omar <info@omarion.me>
 * @copyright 2022 Rektic WP
 * @license   GPLv3
 * @link      https://omarion.me
 */

declare(strict_types=1);
namespace RekticWp\Common\Utils;

use function add_query_arg;
use function filter_input_array;
use const FILTER_SANITIZE_URL;
use const INPUT_GET;

class Url{
	public static function pluginSettingsURL(){
		return self::urlWithArgs(admin_url('admin.php'), ['page' => 'settings-rektic']);
	}

	public static function urlWithArgs(string $url = '', ?array $args = null): string{
		if(empty($url)){
			$url = self::getCurrentAdminUrl();
		}
		if(empty($args)){
			$urlParameters = filter_input_array(INPUT_GET, FILTER_SANITIZE_URL);
			$args          = $urlParameters;
		}

		return add_query_arg($args, $url);
	}

	public static function getCurrentAdminUrl(): string{
		return admin_url();
	}
}
