<?php
/**
 * Rektic WP
 *
 * Pretty errors!
 *
 * @package   rektic-wp
 * @author    SAKHRAOUI Omar <info@omarion.me>
 * @copyright 2022 Rektic WP
 * @license   GPLv3
 * @link      https://omarion.me
 */

namespace RekticWp\Common\Utils;

class Sanitize{
	/**
	 * Recursive sanitation for an array
	 *
	 * @param $array
	 *
	 * @return mixed
	 */
	public static function recursiveSanitizeTextField($array){
		foreach($array as $key => &$value){
			if(is_array($value)){
				$value = self::recursiveSanitizeTextField($value);
			}
			else{
				$value = sanitize_text_field($value);
			}
		}

		return $array;
	}
}
