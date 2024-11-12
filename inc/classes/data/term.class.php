<?php
/**
 * @package The_SEO_Framework\Classes\Data\Post
 * @subpackage The_SEO_Framework\Data
 */

namespace The_SEO_Framework\Data;

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
 * Holds a collection of data helper methods for a term.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->data()->term() instead.
 */
class Term {

	/**
	 * Fetch latest public term ID for any taxonomy.
	 * Memoizes the return value.
	 *
	 * @since 5.0.0
	 * @slow The queried result is not stored in WP Term's cache, which would allow
	 *       direct access to all values of the term (if requested). This is because
	 *       we're using `'fields' => 'ids'` instead of `'fields' => 'all'`.
	 *
	 * @param string $taxonomy The taxonomy to get the latest term from.
	 * @return int Latest Term ID.
	 */
	public static function get_latest_term_id( $taxonomy = 'category' ) {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo( null, $taxonomy ) ) return $memo;

		$cats = \get_terms( [
			'taxonomy'   => $taxonomy,
			'fields'     => 'ids',
			'hide_empty' => false,
			'orderby'    => 'term_id',
			'order'      => 'DESC',
			'number'     => 1,
		] );

		return memo( reset( $cats ), $taxonomy );
	}

	/**
	 * Tests whether term is populated. Also tests the child terms.
	 * Memoizes the return value.
	 *
	 * @since 4.2.8
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param int    $term_id The term ID.
	 * @param string $taxonomy The term taxonomy.
	 * @return bool True when term or child terms are populated, false otherwise.
	 */
	public static function is_term_populated( $term_id, $taxonomy ) {
		return memo( null, $term_id, $taxonomy )
			?? memo(
				// phpcs:ignore, PEAR.Functions.FunctionCallSignature.Indent -- legibility
				   ! empty( \get_term( $term_id, $taxonomy )->count )
				|| array_filter( // Filter count => 0 -- if all are 0, we get an empty array, boolean false.
					array_column(
						\get_terms( [
							'taxonomy'   => $taxonomy,
							'child_of'   => $term_id, // Get children of current term.
							'childless'  => false,
							'pad_counts' => false, // If true, this gives us the value we seek, but we can get it faster via column.
							'get'        => '',
						] ),
						'count',
					),
				),
				$term_id,
				$taxonomy,
			);
	}

	/**
	 * Returns the term ancestors.
	 *
	 * @since 5.1.0
	 *
	 * @param int    $term_id      The term ID.
	 * @param string $taxonomy     The taxonomy.
	 * @param bool   $include_self Whether to include the initial term itself.
	 * @return WP_Term[] The term ancestors, indexed by term ID.
	 */
	public static function get_term_parents( $term_id, $taxonomy, $include_self = false ) {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo( null, $term_id, $include_self ) ) return $memo;

		// Term ID may be 0 when no terms are present.
		if ( ! $term_id )
			return memo( [], $term_id, $include_self );

		// This method is inefficient, but it applies filters we must invoke for compatibility with other plugins.
		$ancestors = \get_ancestors( $term_id, $taxonomy, 'taxonomy' );

		if ( $include_self )
			array_unshift( $ancestors, $term_id );

		$parents = [];

		foreach ( array_reverse( $ancestors ) as $_term_id ) {
			$parents[ $_term_id ] = \get_term( $_term_id, $taxonomy );

			if ( \is_wp_error( $parents[ $_term_id ] ) ) {
				/**
				 * Corner case: If the parent term disappeared, the link is broken.
				 * Then, WordPress assumes its ancestor term as its parent via the while loop.
				 * WordPress's get_ancestors() function already checks for this, but may fail ungracefully when caches are out of sync.
				 */
				unset( $parents[ $_term_id ] );
			}
		}

		return memo( $parents, $term_id, $include_self );
	}
}
