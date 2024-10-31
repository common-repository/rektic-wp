<?php
/**
 * Rektic WP
 *
 * The real entry point
 *
 * @package   rektic-wp
 * @author    SAKHRAOUI Omar <info@omarion.me>
 * @copyright 2022 Rektic WP
 * @license   GPLv3
 * @link      https://omarion.me
 */

declare(strict_types=1);

namespace RekticWp\Common;

use RekticWp\Common\Abstracts\Base;

/**
 * Main function class for external uses
 *
 * @see     rektic_wp()
 * @package RekticWp\Common
 */
class Functions extends Base{
	/**
	 * Get plugin data by using rektic_wp()->getData()
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function getData(): array{
		return $this->plugin->data();
	}
}
