<?php
/**
 * @package The_SEO_Framework\Classes\Helper\Format\Arrays
 * @subpackage The_SEO_Framework\Formatting
 */

namespace The_SEO_Framework\Helper\Format;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\memo;

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
 * Holds methods for Array interpretation and conversion.
 * Array is a reserved keyword, so we use Arrays.
 *
 * @since 5.0.0
 *
 * @access protected
 *         Use tsf()->format()->arrays() instead.
 */
class Arrays {

	/**
	 * Flattens multidimensional lists into a single dimensional list.
	 * Deeply nested lists are merged as well. Won't dig associative arrays.
	 *
	 * E.g., this [ [ 'one' => 1 ], [ [ 'two' => 2 ], [ 'three' => [ 3, 4 ] ] ] ]
	 * becomes    [ [ 'one' => 1 ], [ 'two' => 2 ], [ 'three' => [ 3, 4 ] ] ];
	 *
	 * @link <https://3v4l.org/XBSFa>, test it here.
	 *
	 * @since 5.0.0
	 *
	 * @param array $array The array to flatten. If input is not an array, it'll be casted.
	 * @return array The flattened array.
	 */
	public static function flatten_list( $array ) {

		// PHP 8.1+, use `!array_is_list()`?
		// This is over 350x faster than a polyfill for `!array_is_list()`.
		if ( empty( $array ) || array_values( $array ) !== $array ) return $array;

		$ret = [];

		foreach ( $array as $value ) {
			// We can later use `array_is_list()`.
			if ( \is_array( $value ) && [] !== $value && array_values( $value ) === $value ) {
				$ret = array_merge( $ret, static::flatten_list( $value ) );
			} else {
				array_push( $ret, $value );
			}
		}

		return $ret;
	}

	/**
	 * Scrubs multidimensional arrays into simple items when some conditions are met.
	 * 1. If the array value is empty, the index is removed.
	 * 2. If the array value is alone and of type list, it's converted to its value.
	 *
	 * Deeply nested lists are scrubbed as well.
	 *
	 * E.g., this [ [ 'a' => [ 1 ] ], [ [ 'b' => [ 2, 3 ] ], [ 'c' => [] ] ] ]
	 * becomes    [ [ 'a' => 1 ], [ 'b' => [ 2, 3 ] ] ];
	 *
	 * @link <https://3v4l.org/SDdal>, test it here.
	 *
	 * @since 5.0.0
	 *
	 * @param array $array The array to flatten. If input is not an array, it'll be casted.
	 * @return array The flattened array.
	 */
	public static function scrub( $array ) {

		foreach ( $array as $key => &$item ) {
			// Keep 0 and '0', but grab empty string, null, false, and [].
			if ( empty( $item ) && 0 !== $item && '0' !== $item ) {
				unset( $array[ $key ] );
			} elseif ( \is_array( $item ) ) {
				if ( isset( $item[0] ) && 1 === \count( $item ) ) {
					$item = reset( $item );
				} else {
					$item = static::scrub( $item );
				}
			}
		}

		return $array;
	}

	/**
	 * Merges arrays distinctly, much like `array_merge()`, but then for multidimensionals.
	 * Unlike PHP's `array_merge_recursive()`, this method doesn't convert non-unique keys as sequential.
	 *
	 * This is the only correct function of kind that exists, made bespoke by Sybre for TSF.
	 *
	 * @link <https://3v4l.org/9pnW1> Test it here.
	 *
	 * @since 4.1.4
	 * @since 4.2.7 1. Now supports a single array entry without causing issues.
	 *              2. Reduced number of opcodes by roughly 27% by reworking it.
	 *              3. Now no longer throws warnings with qubed+ arrays.
	 *              4. Now no longer prevents scalar values overwriting arrays.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param array ...$arrays The arrays to merge. The rightmost array's values are dominant.
	 * @return array The merged arrays.
	 */
	public static function array_merge_recursive_distinct( ...$arrays ) {

		$i = \count( $arrays );

		while ( --$i ) {
			$p = $i - 1;

			foreach ( $arrays[ $i ] as $key => $value )
				$arrays[ $p ][ $key ] = isset( $arrays[ $p ][ $key ] ) && \is_array( $value )
					? static::array_merge_recursive_distinct( $arrays[ $p ][ $key ], $value )
					: $value;
		}

		return $arrays[0];
	}

	/**
	 * Computes a difference between arrays, recursively. Much like `array_diff_assoc()`, but then for multidimensionals.
	 *
	 * Unlike `array_diff_assoc()`, this method considers out of order arrays as equal.
	 * So, [ 1, 2 ] and [ 2, 1 ] are considered equal. This is helpful for associative array comparison, like with options.
	 *
	 * This is the only correct function of kind that exists, made bespoke by Sybre for TSF.
	 *
	 * @link <https://3v4l.org/CuItX> Test it here.
	 * TODO consider array_reduce() for improved performance?
	 *
	 * @since 5.1.0
	 *
	 * @param array ...$arrays The arrays to differentiate. The leftmost array's values are dominant.
	 * @return array The differentiated array values.
	 */
	public static function array_diff_assoc_recursive( ...$arrays ) {

		$i = \count( $arrays );

		while ( --$i ) {
			$p = $i - 1;

			if ( \is_array( $arrays[ $i ] ) && \is_array( $arrays[ $p ] ) ) {
				foreach ( $arrays[ $i ] as $key => &$value ) {
					if ( ! \array_key_exists( $key, $arrays[ $p ] ) ) {
						// If the value doesn't exist in previous array, pass it along.
						$arrays[ $p ][ $key ] = $value;
						continue;
					}

					if (
						   $value === $arrays[ $p ][ $key ]
						|| ( \is_array( $value ) && ! static::array_diff_assoc_recursive( ...array_column( $arrays, $key ) ) )
					) {
						// If there's no diff with the previous array or no diff can be found recursively, remove it from all the next arrays.
						foreach ( range( $p, $i ) as $_i )
							if ( \is_array( $arrays[ $_i ] ) )
								unset( $arrays[ $_i ][ $key ] );

						continue;
					}
				}
			}
		}

		return $arrays[0];
	}
}
