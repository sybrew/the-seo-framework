<?php
/**
 * @package The_SEO_Framework\Classes\Data\Plugin\Post
 * @subpackage The_SEO_Framework\Data\Plugin
 */

namespace The_SEO_Framework\Data\Plugin;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\memo;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds a collection of Post data interface methods for TSF.
 *
 * @since 4.3.0
 * @access protected
 * @internal Use tsf()->data()->plugin->post() instead.
 */
class Post {

	/**
	 * @since 4.3.0
	 * @var array[] Stored primary term IDs.
	 */
	private static $primary_term = [];

	/**
	 * Returns the primary term for post.
	 *
	 * @since 3.0.0
	 * @since 4.1.5   1. Added memoization.
	 *                2. The first and second parameters are now required.
	 * @since 4.1.5.1 1. No longer causes a PHP warning in the unlikely event a post's taxonomy gets deleted.
	 *                2. This method now converts the post meta to an integer, making the comparison work again.
	 * @since 4.2.7 Now correctly memoizes when no terms for a post can be found.
	 * @since 4.2.8 Now correctly returns when no terms for a post can be found.
	 * @since 4.3.0 1. Now always tries to return a term if none is set manually.
	 *              2. Now returns `null` instead of `false` on failure.
	 *              3. Moved to `The_SEO_Framework\Data\Plugin\Post`
	 * @FIXME should this be considered with headless?
	 *
	 * @param int    $post_id  The post ID.
	 * @param string $taxonomy The taxonomy name.
	 * @return \WP_Term|null The primary term. Null if cannot be generated.
	 */
	public static function get_primary_term( $post_id, $taxonomy ) {

		if ( isset( static::$primary_term[ $post_id ][ $taxonomy ] ) )
			return static::$primary_term[ $post_id ][ $taxonomy ];

		// Trim truth when exceeding nice numbers. This way, we won't overload memory in memoization.
		if ( \count( static::$primary_term ) > 69 )
			array_splice( static::$primary_term, 42 );

		$primary_id = (int) \get_post_meta( $post_id, "_primary_term_{$taxonomy}", true ) ?: 0;

		// Users can alter the term list via quick/bulk edit, but cannot set a primary term that way.
		// Users can also delete a term from the site that was previously assigned as primary.
		// So, test if the term still exists for the post.
		// Although 'get_the_terms()' is an expensive function, it memoizes, and
		// is always called by WP before we fetch a primary term. So, 0 overhead here.
		$terms        = \get_the_terms( $post_id, $taxonomy );
		$primary_term = false;

		if ( $terms && \is_array( $terms ) ) {
			if ( $primary_id ) {
				// Test for is_array in the unlikely event a post's taxonomy is gone ($terms = WP_Error)
				foreach ( $terms as $term ) {
					if ( $primary_id === $term->term_id ) {
						$primary_term = $term;
						break;
					}
				}
			} else {
				$term_ids = array_column( $terms, 'term_id' );
				asort( $term_ids );
				$primary_term = $terms[ array_key_first( $term_ids ) ] ?? false;
			}
		}

		return static::$primary_term[ $post_id ][ $taxonomy ] = $primary_term;
	}

	/**
	 * Returns the primary term ID for post.
	 *
	 * @since 3.0.0
	 * @since 4.1.5 1. Now validates if the stored term ID's term exists (for the post or at all).
	 *              2. The first and second parameters are now required.
	 * @since 4.3.0 Moved to `The_SEO_Framework\Data\Plugin\Post`
	 *
	 * @param int    $post_id  The post ID.
	 * @param string $taxonomy The taxonomy name.
	 * @return int   The primary term ID. 0 if not found.
	 */
	public static function get_primary_term_id( $post_id, $taxonomy ) {
		return static::get_primary_term( $post_id, $taxonomy )->term_id ?? 0;
	}

	/**
	 * Updates the primary term ID for post.
	 *
	 * @since 3.0.0
	 * @since 4.3.0 Moved to `The_SEO_Framework\Data\Plugin\Post`
	 *
	 * @param int|null $post_id  The post ID.
	 * @param string   $taxonomy The taxonomy name.
	 * @param int      $value    The new value. If empty, it will delete the entry.
	 * @return bool True on success, false on failure.
	 */
	public static function update_primary_term_id( $post_id = null, $taxonomy = '', $value = 0 ) {

		// Unset and don't refill -- we store a simple number; we don't want to get the entire term here.
		unset( static::$primary_term[ $post_id ?? \get_the_id() ] );

		if ( empty( $value ) )
			return \delete_post_meta( $post_id, "_primary_term_{$taxonomy}" );

		return \update_post_meta( $post_id, "_primary_term_{$taxonomy}", $value );
	}
}
