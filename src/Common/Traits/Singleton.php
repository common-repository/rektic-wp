<?php
/**
 * Rektic WP
 *
 * Abstraction for singletons...
 *
 * @package   rektic-wp
 * @author    SAKHRAOUI Omar <info@omarion.me>
 * @copyright 2022 Rektic WP
 * @license   GPLv3
 * @link      https://omarion.me
 */

declare(strict_types=1);

namespace RekticWp\Common\Traits;

/**
 * The singleton skeleton trait to instantiate the class only once
 *
 * @package RekticWp\Common\Traits
 * @since   1.0.0
 */
trait Singleton{
	private static $instance;

	private function __construct(){
	}

	/**
	 * @return self
	 * @since 1.0.0
	 */
	final public static function init(): self{
		if( ! self::$instance){
			self::$instance = new self();
		}

		return self::$instance;
	}

	final public function __wakeup(){
	}

	private function __clone(){
	}
}
