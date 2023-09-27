<?php
/**
 * @package The_SEO_Framework\Classes\Data\Plugin\Term
 * @subpackage The_SEO_Framework\Data\Plugin
 */

namespace The_SEO_Framework\Data\Plugin;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\{
	is_headless,
	memo,
};

use \The_SEO_Framework\Helper\{
	Query,
	Taxonomies,
};

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
 * Holds a collection of Term data interface methods for TSF.
 *
 * @since 4.3.0
 * @access protected
 * @internal Use tsf()->data()->plugin->term() instead.
 */
class Term {

	/**
	 * @since 4.3.0
	 * @var array[] The latest term meta data.
	 */
	private static $term_meta = [];

	/**
	 * Returns the term meta item by key.
	 *
	 * @since 4.0.0
	 * @since 4.2.0 No longer accidentally returns an empty array on failure.
	 * @since 4.3.0 1. Removed the third `$used_cache` parameter.
	 *              2. Moved to \The_SEO_Framework\Data\Plugin\Term
	 *
	 * @param string $item      The item to get.
	 * @param int    $term_id   The Term ID.
	 * @return mixed The term meta item. Null when not found.
	 */
	public static function get_term_meta_item( $item, $term_id = 0 ) {
		return static::get_term_meta( $term_id ?: Query::get_the_real_id() )[ $item ] ?? null;
	}

	/**
	 * Returns term meta data from ID.
	 * Memoizes the return value for the current request.
	 *
	 * Returns Genesis 2.3.0+ data if no term meta data is set via compat module.
	 *
	 * @since 2.7.0
	 * @since 2.8.0 Added filter.
	 * @since 3.0.0 Added filter.
	 * @since 3.1.0 Deprecated filter.
	 * @since 4.0.0 1. Removed deprecated filter.
	 *              2. Now fills in defaults.
	 * @since 4.1.4 1. Removed deprecated filter.
	 *              2. Now considers headlessness.
	 * @since 4.2.0 Now returns an empty array when the term's taxonomy isn't supported.
	 * @since 4.3.0 1. Removed the second `$used_cache` parameter.
	 *              2. Moved to \The_SEO_Framework\Data\Plugin\Term
	 *
	 * @param int $term_id The Term ID.
	 * @return array The term meta data.
	 */
	public static function get_term_meta( $term_id ) {

		if ( isset( static::$term_meta[ $term_id ] ) )
			return static::$term_meta[ $term_id ];

		$term = \get_term( $term_id );

		// We test taxonomy support to be consistent with `get_post_meta()`.
		if ( empty( $term->term_id ) || ! Taxonomies::is_taxonomy_supported( $term->taxonomy ) ) {
			// Do not overwrite cache when not requested. Otherwise, we'd have two "initial" states, causing incongruities.
			return static::$term_meta[ $term_id ] = [];
		}

		// Trim truth when exceeding nice numbers. This way, we won't overload memory in memoization.
		if ( \count( static::$term_meta ) > 69 )
			array_splice( static::$meta, 42 );

		/**
		 * We can't trust the filter to always contain the expected keys.
		 * However, it may contain more keys than we anticipated. Merge them.
		 */
		$defaults = array_merge(
			static::get_unfiltered_term_meta_defaults(),
			static::get_term_meta_defaults( $term->term_id )
		);

		$is_headless = is_headless( 'meta' );

		if ( $is_headless ) {
			$meta = [];
		} else {
			// Unlike get_post_meta(), we need not filter here.
			// See: <https://github.com/sybrew/the-seo-framework/issues/185>
			$meta = \get_term_meta( $term->term_id, \THE_SEO_FRAMEWORK_TERM_OPTIONS, true ) ?: [];
		}

		/**
		 * @since 4.0.5
		 * @since 4.1.4 1. Now considers headlessness.
		 *              2. Now returns a 3rd parameter: boolean $headless.
		 * @note Do not delete/unset/add indexes! It'll cause errors.
		 * @param array $meta    The current term meta.
		 * @param int   $term_id The term ID.
		 * @param bool  $headless Whether the meta are headless.
		 */
		return static::$term_meta[ $term_id ] = \apply_filters_ref_array(
			'the_seo_framework_term_meta',
			[
				array_merge( $defaults, $meta ),
				$term->term_id,
				$is_headless,
			]
		);
	}

	/**
	 * Returns an array of default term options.
	 *
	 * @since 2.7.0
	 * @since 3.1.0 This is now always used.
	 * @since 4.0.0 1. Added $term_id parameter.
	 *              2. Added 'redirect' value.
	 *              3. Added 'title_no_blog_name' value.
	 *              4. Removed 'saved_flag' value.
	 * @since 4.3.0 Moved to \The_SEO_Framework\Data\Plugin\Term
	 *
	 * @param int $term_id The term ID.
	 * @return array The Term Metadata default options.
	 */
	public static function get_term_meta_defaults( $term_id = 0 ) {
		/**
		 * @since 2.1.8
		 * @param array $defaults
		 * @param int   $term_id The current term ID.
		 */
		return (array) \apply_filters_ref_array(
			'the_seo_framework_term_meta_defaults',
			[
				static::get_unfiltered_term_meta_defaults(),
				$term_id ?: Query::get_the_real_id(),
			]
		);
	}

	/**
	 * Returns the unfiltered term meta defaults.
	 *
	 * @since 4.0.0
	 * @since 4.3.0 Moved to \The_SEO_Framework\Data\Plugin\Term
	 *
	 * @return array The default, unfiltered, term meta.
	 */
	private static function get_unfiltered_term_meta_defaults() {
		return [
			'doctitle'           => '',
			'title_no_blog_name' => 0,
			'description'        => '',
			'og_title'           => '',
			'og_description'     => '',
			'tw_title'           => '',
			'tw_description'     => '',
			'social_image_url'   => '',
			'social_image_id'    => 0,
			'canonical'          => '',
			'noindex'            => 0,
			'nofollow'           => 0,
			'noarchive'          => 0,
			'redirect'           => '',
		];
	}

	/**
	 * Updates single term meta value.
	 *
	 * Note that this method can be more resource intensive than you intend it to be,
	 * as it reprocesses all term meta.
	 *
	 * @since 4.0.0
	 * @since 4.0.2 1. Now tests for valid term ID in the term object.
	 *              2. Now continues using the filtered term object.
	 * @since 4.3.0 Moved to \The_SEO_Framework\Data\Plugin\Term
	 *
	 * @param string $item     The item to update.
	 * @param mixed  $value    The value the item should be at.
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 */
	public static function update_single_term_meta_item( $item, $value, $term_id, $tt_id, $taxonomy ) {

		$term = \get_term( $term_id, $taxonomy );

		// We could test for is_wp_error( $term ), but this is more to the point.
		if ( empty( $term->term_id ) ) return;

		$meta          = static::get_term_meta( $term->term_id, false );
		$meta[ $item ] = $value;

		static::save_term_meta( $term->term_id, $tt_id, $taxonomy, $meta );
	}

	/**
	 * Updates term meta from input.
	 *
	 * @since 4.0.0
	 * @since 4.0.2 1. Now tests for valid term ID in the term object.
	 *              2. Now continues using the filtered term object.
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term Taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 * @param array  $data     The data to save.
	 */
	public static function save_term_meta( $term_id, $tt_id, $taxonomy, $data ) {

		$term = \get_term( $term_id, $taxonomy );

		// We could test for is_wp_error( $term ), but this is more to the point.
		if ( empty( $term->term_id ) ) return;

		// var_dump() don't rely on wp_parse_args.
		$data = (array) \wp_parse_args( $data, static::get_term_meta_defaults( $term->term_id ) );
		$data = \tsf()->s_term_meta( $data );

		/**
		 * @since 3.1.0
		 * @param array  $data     The data that's going to be saved.
		 * @param int    $term_id  The term ID.
		 * @param int    $tt_id    The term taxonomy ID.
		 * @param string $taxonomy The taxonomy slug.
		 */
		$data = (array) \apply_filters_ref_array(
			'the_seo_framework_save_term_data',
			[
				$data,
				$term->term_id,
				$tt_id,
				$taxonomy,
			]
		);

		// Unset and don't refill -- this is because get_term_meta has a different filter.
		unset( static::$term_meta[ $term_id ] );

		// Do we want to cycle through the data, so we store only the non-defaults? @see save_post_meta()
		\update_term_meta( $term->term_id, \THE_SEO_FRAMEWORK_TERM_OPTIONS, $data );
	}

	/**
	 * Deletes term meta.
	 * Deletes only the default data keys; or everything when only that is present.
	 *
	 * @since 2.7.0
	 * @since 4.0.0 Removed 2nd, unused, parameter.
	 * @since 4.3.0 Moved to \The_SEO_Framework\Data\Plugin\Term
	 *
	 * @param int $term_id Term ID.
	 */
	public static function delete_term_meta( $term_id ) {

		// If this results in an empty data string, all data has already been removed by WP core.
		$data = \get_term_meta( $term_id, \THE_SEO_FRAMEWORK_TERM_OPTIONS, true );

		if ( \is_array( $data ) ) {
			foreach ( static::get_term_meta_defaults( $term_id ) as $key => $value )
				unset( $data[ $key ] );
		}

		// Unset cache and don't refill.
		unset( static::$term_meta[ $term_id ] );

		// Only delete when no values are left, because someone else might've filtered it.
		if ( empty( $data ) ) {
			\delete_term_meta( $term_id, \THE_SEO_FRAMEWORK_TERM_OPTIONS );
		} else {
			\update_term_meta( $term_id, \THE_SEO_FRAMEWORK_TERM_OPTIONS, $data );
		}
	}
}
