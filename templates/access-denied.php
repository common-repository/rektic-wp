<?php
/**
 * Rektic WP
 *
 * @package   rektic-wp
 * @author    SAKHRAOUI Omar <info@omarion.me>
 * @copyright 2022 Rektic WP
 * @license   GPLv3
 * @link      https://omarion.me
 */

use RekticWp\Common\Utils\Url;

?>

<div class="wrap">
	<h2><?php echo __('Error', 'rektic-wp') ?></h2>
	<p><?php echo __('Your access has been denied, please login in using ', 'rektic-wp') ?>
		<a href="<?php echo esc_url(Url::pluginSettingsURL()) ?>"><?php echo __('This link', 'rektic-wp') ?></a>
	</p>

</div>
