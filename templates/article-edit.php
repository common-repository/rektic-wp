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
<div class="wrap" id="rektic-editor">
	<h2></h2>
	<p></p>

	<div class="row">
		<div class="form-edit-rektic">
			<h2><?php echo $isNew ? __('Create a new article', 'rektic-wp') : __('Edit article', 'rektic-wp') ?></h2>
			<p><?php echo __('Please follow the form to create your article', 'rektic-wp') ?></p>

			<input id="one" type="radio" name="stage" checked="checked"/>
			<input id="two" type="radio" name="stage"/>
			<input id="three" type="radio" name="stage"/>
			<input id="four" type="radio" name="stage"/>

			<div class="stages">
				<label for="one">1
					<small>Keywords</small>
				</label>
				<label for="two">2
					<small>Headers</small>
				</label>
				<label for="three">3
					<small>Preview and export</small>
				</label>
			</div>

			<span class="progress"><span></span></span>

			<div class="panels">
				<div data-panel="one">
					<input type="hidden" id="nonce1" value="<?php echo esc_attr($nonce1) ?>"/>
					<input type="text" id="keywords" placeholder="Key word"/>
					<div class="niceCountryInputSelector" data-selectedcountry="US" data-showspecial="false" data-showflags="true"
						 data-i18nall="All selected" data-i18nnofilter="No selection" data-i18nfilter="Filter"
						 data-onchangecallback="onChangeCallback"></div>
					<select id="lang">
						<option value="DE">Deutsch</option>
						<option value="EN" selected>English</option>
						<option value="ES">Español</option>
						<option value="FR">Français</option>
						<option value="IT">Italiano</option>
						<option value="NL">Nederlands</option>
						<option value="PT">Português</option>
					</select>
				</div>
				<div data-panel="two">
					<input type="hidden" id="nonce2" value="<?php echo esc_attr($nonce2) ?>"/>
					<img class="step-loader Animationlogo01" id="step2-loader"
						 src="<?php echo plugins_url('/assets/public/images/logo.png', REKTIC_WP_PLUGIN_FILE) ?>">
				</div>
				<div data-panel="three">
					<input type="hidden" id="nonce3" value="<?php echo esc_attr($nonce3) ?>"/>
					<div class="rektic-preview">
						<ul class="view-list">
							<li>
								<strong>H1:</strong>
								<span id="preview-h1"></span>
							</li>
							<li>
								<strong>H2:</strong>
								<ul id="preview-h2"></ul>
							</li>
							<li>
								<strong>Meta Description:</strong>
								<p id="preview-meta"></p>
							</li>
						</ul>
					</div>
				</div>
			</div>

			<div class="rektic-footer">
				<img style="display: none" class="rek-loader" id="rek-loader" src="/wp-admin/images/spinner.gif">
				<button>Next</button>
			</div>

		</div>
	</div>
</div>
