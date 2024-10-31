<?php
/**
 * Rektic WP
 *
 * Any kind of notice displayed by the plugin, anywhere, is declared here
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

/**
 * Class Notices
 *
 * @package RekticWp\App\Backend
 * @since   1.0.0
 */
class Notices extends Base{

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
		 * Add plugin code here for admin notices specific functions
		 */
		add_action('admin_notices', [
			$this,
			'pluginAdminNotice',
		]);
	}

	/**
	 * Plugin admin notice
	 *
	 * @since 1.0.0
	 */
	public function pluginAdminNotice(){
		global $pagenow;
		if(isset($_GET['page']) && $pagenow === 'options-general.php' && $_GET['page'] === 'settings-rektic'){
			echo '<div class="notice notice-warning is-dismissible">
             <p>' . __('This area is critical, proceed carefully!.', 'rektic-wp') . '</p>
         </div>';
		}
	}
}
