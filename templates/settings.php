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
?>
<div class="wrap">
	<h2><?php echo __('Rektic Settings', 'rektic-wp') ?></h2>
	<p><?php echo __('Settings for Rektic plugin', 'rektic-wp') ?></p>

	<form method="post" action="options.php">
		<?php
		settings_fields('rektic_settings_option_group');
		do_settings_sections('rektic-settings-admin');
		submit_button();
		?>
	</form>
	<p>
		Please login to
		<a href="https://rektic.ai/">your Rektic</a>
		account to generate the token.
	</p>
</div>
