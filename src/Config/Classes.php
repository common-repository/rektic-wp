<?php
/**
 * Rektic WP
 *
 * Plugin's configs
 *
 * @package   rektic-wp
 * @author    SAKHRAOUI Omar <info@omarion.me>
 * @copyright 2022 Rektic WP
 * @license   GPLv3
 * @link      https://omarion.me
 */

declare(strict_types=1);

namespace RekticWp\Config;

/**
 * This array is being used in ../Boostrap.php to instantiate the classes
 *
 * @package RekticWp\Config
 * @since   1.0.0
 */
final class Classes{

	/**
	 * Init the classes inside these folders based on type of request.
	 *
	 * @see Requester for all the type of requests or to add your own
	 */
	public static function get(): array{
		return [
			['init' => 'Integrations'],
			['init' => 'App\\General'],
			[
				'init'       => 'App\\Frontend',
				'on_request' => 'frontend',
			],
			[
				'init'       => 'App\\Backend',
				'on_request' => 'backend',
			],
			[
				'init'       => 'App\\Rest',
				'on_request' => 'rest',
			],
			[
				'init'       => 'App\\Cli',
				'on_request' => 'cli',
			],
			[
				'init'       => 'App\\Cron',
				'on_request' => 'cron',
			],
			['init' => 'Compatibility'],
		];
	}
}
