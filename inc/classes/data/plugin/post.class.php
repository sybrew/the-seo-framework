<?php
/**
 * @package The_SEO_Framework\Classes\Data\Plugin\Post
 * @subpackage The_SEO_Framework\Data\Plugin
 */

namespace The_SEO_Framework\Data\Plugin;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\is_headless;

use \The_SEO_Framework\{
	Data,
	Helper\Post_Type,
	Helper\Query,
	Traits\Property_Refresher,
};

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
 * Holds a collection of Post data interface methods for TSF.
 *
 * @since 5.0.0
 * @since 5.1.0 Added the Property_Refresher trait.
 * @access protected
 *         Use tsf()->data()->plugin()->post() instead.
 */
class Post {
	use Property_Refresher;

	/**
	 * @since 5.0.0
	 * @var array[] Stored post meta data.
	 */
	private static $meta_memo = [];

	/**
	 * @since 5.0.0
	 * @var array[] Stored primary term IDs cache.
	 */
	private static $pt_memo = [];

	/**
	 * Returns a post SEO meta item by key.
	 *
	 * Unlike other post meta calls, no \WP_Post object is accepted as an input value,
	 * this is done for performance reasons, so we can cache here, instead of relying on
	 * WordPress's cache, where they cast many filters and redundantly sanitize the object.
	 *
	 * @since 4.0.0
	 * @since 4.0.1 Now obtains the real ID when none is supplied.
	 * @since 5.0.0 1. Removed the third `$use_cache` parameter.
	 *              2. Moved from `\The_SEO_Framework\Load`.
	 *              3. Renamed from `get_post_meta_item`.
	 *
	 * @param string $item      The item to get.
	 * @param int    $post_id   The post ID.
	 * @return mixed The post meta item's value. Null when item isn't registered.
	 */
	public static function get_meta_item( $item, $post_id = 0 ) {

		$post_id = $post_id ?: Query::get_the_real_id();

		return $post_id
			? static::get_meta( $post_id )[ $item ] ?? null
			: null;
	}

	/**
	 * Returns all registered custom SEO fields for a post.
	 * Memoizes the return value.
	 *
	 * Unlike other post meta calls, no \WP_Post object is accepted as an input value,
	 * this is done for performance reasons, so we can cache here, instead of relying on
	 * WordPress's cache, where they cast many filters and redundantly sanitize the object.
	 *
	 * @since 4.0.0
	 * @since 4.0.2 Now tests for valid post ID in the post object.
	 * @since 4.1.4 1. Now returns an empty array when the post type isn't supported.
	 *              2. Now considers headlessness.
	 * @since 5.0.0 1. Removed the third `$use_cache` parameter.
	 *              2. Moved from `\The_SEO_Framework\Load`.
	 *              3. Renamed from `get_post_meta`.
	 * @since 5.1.0 Now returns the default meta if the post type isn't supported.
	 *
	 * @param int $post_id The post ID.
	 * @return array The post meta.
	 */
	public static function get_meta( $post_id = 0 ) {

		$post_id = $post_id ?: Query::get_the_real_id();

		if ( isset( static::$meta_memo[ $post_id ] ) )
			return static::$meta_memo[ $post_id ];

		// Code smell: the empty test is for performance since the memo can be bypassed by input vars.
		empty( static::$meta_memo ) and static::register_automated_refresh( 'meta_memo' );

		// We test post type support for "post_query"-queries might get past this point.
		if ( empty( $post_id ) || ! Post_Type::is_supported( \get_post( $post_id )->post_type ?? '' ) )
			return static::$meta_memo[ $post_id ] = static::get_default_meta( $post_id );

		// Keep lucky first when exceeding nice numbers. This way, we won't overload memory in memoization.
		if ( \count( static::$meta_memo ) > 69 )
			static::$meta_memo = \array_slice( static::$meta_memo, 0, 7, true );

		// We need this early to filter keys from post meta.
		$defaults    = static::get_default_meta( $post_id );
		$is_headless = is_headless( 'meta' );

		if ( $is_headless ) {
			$meta = [];
		} else {
			// Filter the post meta items based on defaults' keys.
			// Fix: <https://github.com/sybrew/the-seo-framework/issues/185>
			$meta = array_intersect_key(
				\get_post_meta( $post_id ) ?: [], // Gets all post meta. This is a discrepancy with get_term_meta()!
				$defaults,
			);

			// WP converts all entries to arrays, because we got ALL entries. Disarray!
			// We cannot use array_column() because we need to preserve the keys.
			foreach ( $meta as &$value )
				$value = $value[0];
		}

		/**
		 * @since 4.0.5
		 * @since 4.1.4 1. Now considers headlessness.
		 *              2. Now returns a 3rd parameter: boolean $headless.
		 * @note Do not delete/unset/add indexes! It'll cause errors.
		 * @param array $meta    The current post meta.
		 * @param int   $post_id The post ID.
		 * @param bool  $headless Whether the meta are headless.
		 */
		return static::$meta_memo[ $post_id ] = \apply_filters(
			'the_seo_framework_post_meta',
			array_merge( $defaults, $meta ),
			$post_id,
			$is_headless,
		);
	}

	/**
	 * Returns the post meta defaults.
	 *
	 * Unlike other post meta calls, no \WP_Post object is accepted as an input value,
	 * this is done for performance reasons, so we can cache here, instead of relying on
	 * WordPress's cache, where they cast many filters and redundantly sanitize the object.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `get_post_meta_defaults`.
	 *
	 * @param int $post_id The post ID.
	 * @return array The default post meta.
	 */
	public static function get_default_meta( $post_id = 0 ) {
		/**
		 * @since 4.1.4
		 * @since 4.2.0 1. Now corrects the $post_id when none is supplied.
		 *              2. No longer returns the third parameter.
		 * @param array    $defaults
		 * @param integer  $post_id Post ID.
		 * @param \WP_Post $post    Post object.
		 */
		return (array) \apply_filters(
			'the_seo_framework_post_meta_defaults',
			[
				'_genesis_title'          => '',
				'_tsf_title_no_blogname'  => 0, // The prefix I should've used from the start...
				'_genesis_description'    => '',
				'_genesis_canonical_uri'  => '',
				'redirect'                => '', //! Will be displayed in custom fields when set...
				'_social_image_url'       => '',
				'_social_image_id'        => 0,
				'_genesis_noindex'        => 0,
				'_genesis_nofollow'       => 0,
				'_genesis_noarchive'      => 0,
				'exclude_local_search'    => 0, //! Will be displayed in custom fields when set...
				'exclude_from_archive'    => 0, //! Will be displayed in custom fields when set...
				'_open_graph_title'       => '',
				'_open_graph_description' => '',
				'_twitter_title'          => '',
				'_twitter_description'    => '',
				'_tsf_twitter_card_type'  => '',
			],
			$post_id ?: Query::get_the_real_id(),
		);
	}

	/**
	 * Updates single post meta value.
	 *
	 * Note that this method can be more resource intensive than you intend it to be,
	 * as it reprocesses all post meta.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `update_single_post_meta_item`.
	 *
	 * @param string  $item    The item to update.
	 * @param mixed   $value   The value the item should be at.
	 * @param integer $post_id The post ID. Also accepts Post objects.
	 */
	public static function update_single_meta_item( $item, $value, $post_id ) {

		$post_id = \get_post( $post_id )->ID ?? null;

		if ( empty( $post_id ) ) return;

		$meta          = static::get_meta( $post_id );
		$meta[ $item ] = $value;

		static::save_meta( $post_id, $meta );
	}

	/**
	 * Save post meta / custom field data for a singular post type.
	 *
	 * @since 4.0.0
	 * @since 4.1.4 Removed deprecated filter.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `save_post_meta`.
	 *
	 * @param integer $post_id The post ID. Also accepts Post objects.
	 * @param array   $data    The post meta fields, will be merged with the defaults.
	 */
	public static function save_meta( $post_id, $data ) {

		$post_id = \get_post( $post_id )->ID ?? null;

		if ( empty( $post_id ) ) return;

		/**
		 * @NOTE Do not remove indexes. In the future, we'll store all data,
		 *       even if empty, to ensure defaults don't override them.
		 *       So, set an empty value if you wish to delete them.
		 * @see https://github.com/sybrew/the-seo-framework/issues/185
		 * @since 4.0.0
		 * @since 5.0.0 1. The second parameter is now an integer, instead of Post object.
		 *              2. No longer sends pre-sanitized data to the filter.
		 * @param array $data The data that's going to be saved.
		 * @param int   $post The post object.
		 */
		$data = (array) \apply_filters(
			'the_seo_framework_save_post_meta',
			array_merge(
				static::get_default_meta( $post_id ),
				$data,
			),
			$post_id,
		);

		unset( static::$meta_memo[ $post_id ] );

		// See <https://github.com/sybrew/the-seo-framework/issues/185#issuecomment-1780697955>
		$data = Data\Filter\Post::filter_meta_update( $data );

		// Cycle through $data, insert value or delete field
		foreach ( (array) $data as $field => $value ) {
			// Save $value, or delete if the $value is empty.
			// We can safely assume no one-zero/qubit options pass through here thanks to sanitization earlier--alleviating database weight.
			if ( $value || ( \is_string( $value ) && \strlen( $value ) ) ) {
				\update_post_meta( $post_id, $field, $value );
			} else {
				// All empty values are deleted here, even if they never existed... is this the best way to handle this?
				// This is fine for as long as we merge the getter values with the defaults.
				\delete_post_meta( $post_id, $field );
			}
		}
	}

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
	 * @since 5.0.0 1. Now always tries to return a term if none is set manually.
	 *              2. Now returns `null` instead of `false` on failure.
	 *              3. Now considers headlessness.
	 *              4. Moved from `\The_SEO_Framework\Load`.
	 * @since 5.0.2 Now selects the last child of a primary term if its parent has the lowest ID.
	 * @since 5.1.0 Now returns a valid primary term if the selected one is gone.
	 *
	 * @param int    $post_id  The post ID.
	 * @param string $taxonomy The taxonomy name.
	 * @return ?\WP_Term The primary term. Null if cannot be generated.
	 */
	public static function get_primary_term( $post_id, $taxonomy ) {

		if ( isset( static::$pt_memo[ $post_id ][ $taxonomy ] ) )
			return static::$pt_memo[ $post_id ][ $taxonomy ] ?: null;

		// Code smell: the empty test is for performance since the memo can be bypassed by input vars.
		empty( static::$pt_memo ) and static::register_automated_refresh( 'pt_memo' );

		// Keep lucky first when exceeding nice numbers. This way, we won't overload memory in memoization.
		if ( \count( static::$pt_memo ) > 69 )
			static::$pt_memo = \array_slice( static::$pt_memo, 0, 7, true );

		$is_headless = is_headless( 'meta' );

		if ( $is_headless ) {
			$primary_id = 0;
		} else {
			$primary_id = (int) \get_post_meta( $post_id, "_primary_term_{$taxonomy}", true ) ?: 0;
		}

		// Users can alter the term list via quick/bulk edit, but cannot set a primary term that way.
		// Users can also delete a term from the site that was previously assigned as primary.
		// So, test if the term still exists for the post.
		// Although 'get_the_terms()' is an expensive function, it memoizes, and
		// is always called by WP before we fetch a primary term. So, 0 overhead here.
		$terms        = \get_the_terms( $post_id, $taxonomy );
		$primary_term = null;

		if ( $terms && \is_array( $terms ) ) {
			if ( $primary_id ) {
				// Test for is_array in the unlikely event a post's taxonomy is gone ($terms = WP_Error)
				foreach ( $terms as $term ) {
					if ( $primary_id === $term->term_id ) {
						$primary_term = $term;
						break;
					}
				}
			}

			if ( ! $primary_term ) {
				// No primary term has been assigned, or the primary term was deleted or altered. We need to find a new one.

				$term_ids = array_column( $terms, 'term_id' );
				asort( $term_ids );
				$primary_term = $terms[ array_key_first( $term_ids ) ] ?? null;

				if ( $primary_term && \count( $terms ) > 1 ) {
					// parent_id => child_id; could be 0 => child_id if it has no parent.
					$child_by_parent = array_column( $terms, 'term_id', 'parent' );
					// term_id => $term index; related to $terms, flipped to speed up lookups.
					$term_by_term_id = array_flip( $term_ids );

					// Chain the isset because it expects an array.
					while ( isset(
						$child_by_parent[ $primary_term->term_id ],
						$term_by_term_id[ $child_by_parent[ $primary_term->term_id ] ],
						$terms[ $term_by_term_id[ $child_by_parent[ $primary_term->term_id ] ] ], // this is always an object.
					) ) {
						$primary_term = $terms[ $term_by_term_id[ $child_by_parent[ $primary_term->term_id ] ] ];
					}
				}
			}
		}

		/**
		 * @since 5.0.0
		 * @param ?\WP_Term $primary_term The primary term. Null if cannot be generated.
		 * @param int       $post_id     The post ID.
		 * @param string    $taxonomy    The taxonomy name.
		 * @param bool      $is_headless Whether the meta are headless.
		 */
		static::$pt_memo[ $post_id ][ $taxonomy ] = \apply_filters(
			'the_seo_framework_primary_term',
			$primary_term,
			$post_id,
			$taxonomy,
			$is_headless,
		) ?: false;

		return static::$pt_memo[ $post_id ][ $taxonomy ] ?: null;
	}

	/**
	 * Returns the primary term ID for post.
	 *
	 * @since 3.0.0
	 * @since 4.1.5 1. Now validates if the stored term ID's term exists (for the post or at all).
	 *              2. The first and second parameters are now required.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
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
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param int|null $post_id  The post ID.
	 * @param string   $taxonomy The taxonomy name.
	 * @param int      $value    The new value. If empty, it will delete the entry.
	 * @return bool True on success, false on failure.
	 */
	public static function update_primary_term_id( $post_id = null, $taxonomy = '', $value = 0 ) {

		// Unset and don't refill -- we store a simple number; we don't want to get the entire term here.
		unset( static::$pt_memo[ $post_id ?? \get_the_id() ] );

		$value = \absint( $value );

		if ( empty( $value ) )
			return \delete_post_meta( $post_id, "_primary_term_{$taxonomy}" );

		return \update_post_meta( $post_id, "_primary_term_{$taxonomy}", $value );
	}
}
