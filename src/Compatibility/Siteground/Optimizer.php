<?php
/**
 * Rektic WP
 *
 * Make our enqueed files compatible with SiteGround's Optimizer
 *
 * @package   rektic-wp
 * @author    SAKHRAOUI Omar <info@omarion.me>
 * @copyright 2022 Rektic WP
 * @license   GPLv3
 * @link      https://omarion.me
 */

declare(strict_types=1);

namespace RekticWp\Compatibility\Siteground;

/**
 * Class Example
 *
 * @package RekticWp\Compatibility\Siteground
 * @since   1.0.0
 */
class Optimizer{

	/**
	 * Initialize the class.
	 *
	 * @since 1.0.0
	 */
	public function init(){
		/**
		 * Add 3rd party compatibility code here.
		 * Compatibility classes instantiates after anything else
		 *
		 * @see Bootstrap::__construct
		 */
		add_filter('sgo_css_combine_exclude', [
			$this,
			'excludeCssCombine',
		]);
		add_filter('sgo_css_minify_exclude', [
			$this,
			'cssMinifyExclude',
		]);
		add_filter('sgo_js_minify_exclude', [
			$this,
			'jsMinifyExclude',
		]);
		add_filter('sgo_js_async_exclude', [
			$this,
			'jsAsyncExclude',
		]);
	}

	public function excludeCssCombine(array $exclude_list): array{
		$exclude_list[] = 'rektic-frontend-css';

		return $exclude_list;
	}

	function cssMinifyExclude($exclude_list){
		$exclude_list[] = 'rektic-frontend-css';

		return $exclude_list;
	}

	function jsMinifyExclude($exclude_list){
		$exclude_list[] = 'rektic-frontend-js';

		return $exclude_list;
	}

	function jsAsyncExclude($exclude_list){
		$exclude_list[] = 'rektic-frontend-js';

		return $exclude_list;
	}
}
