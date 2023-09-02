<?php
/**
 * @package The_SEO_Framework\Utils
 */

namespace The_SEO_Framework\Utils;

/**
 * The SEO Framework plugin
 * Copyright (C) 2018 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Normalizes generation args to prevent PHP warnings.
 * This is the standard way TSF determines the type of query.
 *
 * @since 4.3.0
 * @see https://github.com/sybrew/the-seo-framework/issues/640#issuecomment-1703260744.
 *      We made an exception about passing by reference for this function.
 *
 * @param array|null $args The query arguments. Accepts 'id', 'taxonomy', and 'pta'.
 *                         Leave null to have queries be autodetermined.
 *                         Passed by reference.
 */
function normalize_generation_args( &$args ) {

	if ( \is_array( $args ) ) {
		$args += [
			'id'       => 0,
			'taxonomy' => '',
			'pta'      => '',
		];
	} else {
		$args = null;
	}
}

/**
 * Flattens multidimensional lists into a single dimensional list.
 * Deeply nested lists are merged as well. Won't dig associative arrays.
 *
 * E.g., this [ [ 'one' => 1 ], [ [ 'two' => 2 ], [ 'three' => [ 3, 4 ] ] ] ]
 * becomes    [ [ 'one' => 1 ], [ 'two', => 2 ], [ 'three' => [ 3, 4 ] ] ];
 *
 * @link <https://3v4l.org/XBSFa>, test it here.
 *
 * @since 4.2.7
 * @since 4.3.0 Moved to \Utils\ArrayProcessor and is now public.
 *
 * @param array $array The array to flatten. If input is not an array, it'll be casted.
 * @return array The flattened array.
 */
function array_flatten_list( $array ) {

	// PHP 8.1+, use `!array_is_list()`?
	// This is 350x faster than a polyfill for `!array_is_list()`.
	if ( [] === $array || array_values( $array ) !== $array ) return $array;

	$ret = [];

	foreach ( $array as $value ) {
		// We can later use `array_is_list()`.
		if ( \is_array( $value ) && [] !== $value && array_values( $value ) === $value ) {
			$ret = array_merge( $ret, array_flatten_list( $value ) );
		} else {
			array_push( $ret, $value );
		}
	}

	return $ret;
}

/**
 * Merges arrays distinctly, much like `array_merge()`, but then for multidimensionals.
 * Unlike PHP's `array_merge_recursive()`, this method doesn't convert non-unique keys as sequential.
 *
 * @link <https://3v4l.org/9pnW1#v8.1.8> Test it here.
 *
 * @since 4.1.4
 * @since 4.2.7 1. Now supports a single array entry without causing issues.
 *              2. Reduced number of opcodes by roughly 27% by reworking it.
 *              3. Now no longer throws warnings with qubed+ arrays.
 *              4. Now no longer prevents scalar values overwriting arrays.
 * @since 4.3.0 Moved to The_SEO_Framework\Utils as a function.
 *
 * @param array ...$arrays The arrays to merge. The rightmost array's values are dominant.
 * @return array The merged arrays.
 */
function array_merge_recursive_distinct( ...$arrays ) {

	$i = \count( $arrays );

	while ( --$i ) {
		$p = $i - 1;

		foreach ( $arrays[ $i ] as $key => $value )
			$arrays[ $p ][ $key ] = isset( $arrays[ $p ][ $key ] ) && \is_array( $value )
				? array_merge_recursive_distinct( $arrays[ $p ][ $key ], $value )
				: $value;
	}

	return $arrays[0];
}
