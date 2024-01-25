<?php
/**
 * @package The_SEO_Framework\Classes\Data\Plugin\Deprecated
 * @subpackage The_SEO_Framework\Data\Plugin
 */

namespace The_SEO_Framework\Data\Plugin;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Data;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Holds a collection of data deprecator methods for TSF.
 *
 * @since 5.0.0
 * @access private
 */
final class Deprecated {

	/**
	 * @since 5.0.0
	 * @var array Holds 'all' deprecated TSF's options/settings. Updates in real time.
	 */
	private static $deprecation_map;

	/**
	 * Returns the deprecated option value.
	 *
	 * @since 5.0.0
	 *
	 * @param string ...$key Option name. Additional parameters will try get subvalues of the array.
	 *                       When empty, the function will return an unexpected value, but likely null.
	 * @return mixed
	 */
	public static function get_deprecated_option( ...$key ) {

		$map = static::$deprecation_map ??= static::get_deprecation_map();

		foreach ( $key as $k )
			$map = $map[ $k ] ?? null;

		// No key found. Abort.
		if ( empty( $map ) )
			return null;

		// Do not loop back to The_SEO_Framework\Data::get_option(); that could cause an infinite loop.
		$option = Data\Plugin::get_options();

		foreach ( (array) $map as $k )
			$option = $option[ $k ] ?? null;

		return $option ?? null;
	}

	/**
	 * Returns the deprecation map.
	 *
	 * @since 5.0.0
	 *
	 * @return array A list of deprecated options and their replacement indexes.
	 */
	public static function get_deprecation_map() {
		return static::$deprecation_map ??= [];
	}
}
