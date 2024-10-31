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

/*
 * SEO INSIGHTS - secondary in depth - pro company
 * INSPIRATION - related websites adv - pro company
 */
?>
<div class="rektic-metabox" style="display: none;">
	<input type="hidden" id="rek-meta-nonce" value="<?php echo esc_attr($rekticNonce) ?>">
	<h4>SEO</h4>
	<p>
		<strong><?php echo __('Score:', 'rektic-wp')?></strong>
		<span id="rektic-seo-score">--%</span>
	</p>
	<button id="rektic-generate-seo" class="btn primary"><?php echo __('Regenerate SEO report', 'rektic-wp')?></button>
	<button id="rektic-generate-seo-report" class="btn primary"><?php echo __('View SEO Details', 'rektic-wp')?></button>
	<h4><?php echo __('Tools', 'rektic-wp')?></h4>
	<button id="rektic-generate-intro" class="btn primary"><?php echo __('Generate Intro', 'rektic-wp')?></button>
	<button id="rektic-generate-more" class="btn primary"><?php echo __('Generate more', 'rektic-wp')?></button>
	<button id="rektic-generate-rewrite" class="btn primary"><?php echo __('Re-write selection', 'rektic-wp')?></button>
	<h4><?php echo __('Useful information', 'rektic-wp')?></h4>
	<button id="rektic-top-ranked" class="btn primary"><?php echo __('Related websites', 'rektic-wp')?></button>
	<button id="rektic-top-articles" class="btn primary"><?php echo __('Related websites - InDepth', 'rektic-wp')?></button>
	<button id="rektic-top-seo" class="btn primary"><?php echo __('Associated searches', 'rektic-wp')?></button>
	<button id="rektic-top-secondary" class="btn primary"><?php echo __('Secondary keyword suggestions', 'rektic-wp')?></button>
	<button id="rektic-top-secondary-deap" class="btn primary"><?php echo __('Secondary keywords - InDepth', 'rektic-wp')?></button>
	<button id="rektic-top-most-asked" class="btn primary"><?php echo __('People also ask', 'rektic-wp')?></button>
	<h4><?php echo __('Plagiarism', 'rektic-wp')?></h4>
	<button id="rektic-plagia-gen" class="btn primary"><?php echo __('Regenerate plagiarism report', 'rektic-wp')?></button>
	<button id="rektic-plagia-dl" class="btn primary" style="display: none"><?php echo __('Download last report', 'rektic-wp')?></button>
</div>
<div class="rektic-metabox-loader">
	<img class="step-loader Animationlogo01" id="step2-loader"
		 src="<?php echo esc_url(plugins_url('/assets/public/images/logo.png', REKTIC_WP_PLUGIN_FILE)) ?>">
</div>
