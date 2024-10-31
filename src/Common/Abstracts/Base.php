<?php
/**
 * Rektic WP
 *
 * Base class for everything! Treated more as an abstract for a controller (in MVC pattern)
 *
 * @package   rektic-wp
 * @author    SAKHRAOUI Omar <info@omarion.me>
 * @copyright 2022 Rektic WP
 * @license   GPLv3
 * @link      https://omarion.me
 */

declare(strict_types=1);

namespace RekticWp\Common\Abstracts;

use RekticWp\Config\Plugin;

/**
 * The Base class which can be extended by other classes to load in default methods
 *
 * @package RekticWp\Common\Abstracts
 * @since   1.0.0
 */
abstract class Base{
	/**
	 * @var array : will be filled with data from the plugin config class
	 * @see Plugin
	 */
	protected $plugin = [];


	/**
	 * Base constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct(){
		$this->plugin        = Plugin::init();
	}
}
