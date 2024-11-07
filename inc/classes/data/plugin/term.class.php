<?php
/**
 * @package The_SEO_Framework\Classes\Data\Plugin\Term
 * @subpackage The_SEO_Framework\Data\Plugin
 */

namespace The_SEO_Framework\Data\Plugin;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\is_headless;

use \The_SEO_Framework\{
	Helper\Query,
	Helper\Taxonomy,
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
 * Holds a collection of Term data interface methods for TSF.
 *
 * @since 5.0.0
 * @since 5.1.0 Added the Property_Refresher trait.
 * @access protected
 *         Use tsf()->data()->plugin()->term() instead.
 */
class Term {
	use Property_Refresher;

	/**
	 * @since 5.0.0
	 * @var array[] Stored term meta data.
	 */
	private static $meta_memo = [];

	/**
	 * Returns the term meta item by key.
	 *
	 * @since 4.0.0
	 * @since 4.2.0 No longer accidentally returns an empty array on failure.
	 * @since 5.0.0 1. Removed the third `$use_cache` parameter.
	 *              2. Moved from `\The_SEO_Framework\Load`.
	 *              3. Renamed from `get_term_meta_item`.
	 *
	 * @param string $item    The item to get.
	 * @param int    $term_id The Term ID.
	 * @return mixed The term meta item. Null when not found.
	 */
	public static function get_meta_item( $item, $term_id = 0 ) {

		$term_id = $term_id ?: Query::get_the_real_id();

		return $term_id
			? static::get_meta( $term_id )[ $item ] ?? null
			: null;
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
	 * @since 5.0.0 1. Removed the second `$use_cache` parameter.
	 *              2. Moved from `\The_SEO_Framework\Load`.
	 *              3. Renamed from `get_term_meta`.
	 * @since 5.1.0 Now returns the default meta if the term's taxonomy isn't supported.
	 *
	 * @param int $term_id The Term ID.
	 * @return array The term meta data.
	 */
	public static function get_meta( $term_id = 0 ) {

		$term_id = $term_id ?: Query::get_the_real_id();

		if ( isset( static::$meta_memo[ $term_id ] ) )
			return static::$meta_memo[ $term_id ];

		// Code smell: the empty test is for performance since the memo can be bypassed by input vars.
		empty( static::$meta_memo ) and static::register_automated_refresh( 'meta_memo' );

		// We test taxonomy support to be consistent with `get_post_meta()`.
		if ( empty( $term_id ) || ! Taxonomy::is_supported( \get_term( $term_id )->taxonomy ?? '' ) )
			return static::$meta_memo[ $term_id ] = static::get_default_meta( $term_id );

		// Keep lucky first when exceeding nice numbers. This way, we won't overload memory in memoization.
		if ( \count( static::$meta_memo ) > 69 )
			static::$meta_memo = \array_slice( static::$meta_memo, 0, 7, true );

		$is_headless = is_headless( 'meta' );

		if ( $is_headless ) {
			$meta = [];
		} else {
			$meta = \get_term_meta( $term_id, \THE_SEO_FRAMEWORK_TERM_OPTIONS, true ) ?: [];
		}

		/**
		 * @since 4.0.5
		 * @since 4.1.4 1. Now considers headlessness.
		 *              2. Now returns a 3rd parameter: boolean $headless.
		 * @note Do not delete/unset/add indexes! It'll cause errors.
		 * @param array $meta        The current term meta.
		 * @param int   $term_id     The term ID.
		 * @param bool  $is_headless Whether the meta are headless.
		 */
		return static::$meta_memo[ $term_id ] = \apply_filters(
			'the_seo_framework_term_meta',
			array_merge(
				static::get_default_meta( $term_id ),
				$meta,
			),
			$term_id,
			$is_headless,
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
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `get_term_meta_defaults`.
	 *              3. Added 'tw_card_type' value.
	 *
	 * @param int $term_id The term ID.
	 * @return array The Term Metadata default options.
	 */
	public static function get_default_meta( $term_id = 0 ) {
		/**
		 * @since 2.1.8
		 * @param array $defaults
		 * @param int   $term_id The current term ID.
		 */
		return (array) \apply_filters(
			'the_seo_framework_term_meta_defaults',
			[
				'doctitle'           => '',
				'title_no_blog_name' => 0,
				'description'        => '',
				'og_title'           => '',
				'og_description'     => '',
				'tw_title'           => '',
				'tw_description'     => '',
				'tw_card_type'       => '',
				'social_image_url'   => '',
				'social_image_id'    => 0,
				'canonical'          => '',
				'noindex'            => 0,
				'nofollow'           => 0,
				'noarchive'          => 0,
				'redirect'           => '',
			],
			$term_id ?: Query::get_the_real_id(),
		);
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
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `update_single_term_meta_item`.
	 *
	 * @param string $item     The item to update.
	 * @param mixed  $value    The value the item should be at.
	 * @param int    $term_id  Term ID.
	 */
	public static function update_single_meta_item( $item, $value, $term_id ) {

		$term_id = \get_term( $term_id )->term_id ?? null;

		if ( empty( $term_id ) ) return;

		$meta          = static::get_meta( $term_id, false );
		$meta[ $item ] = $value;

		static::save_meta( $term_id, $meta );
	}

	/**
	 * Updates term meta from input.
	 *
	 * @since 4.0.0
	 * @since 4.0.2 1. Now tests for valid term ID in the term object.
	 *              2. Now continues using the filtered term object.
	 * @since 5.0.0 1. Removed 3rd and 4th parameters ($tt_id and $taxonomy).
	 *              2. Moved from `\The_SEO_Framework\Load`.
	 *              3. Renamed from `save_term_meta`.
	 *
	 * @param int   $term_id Term ID.
	 * @param array $data    The data to save.
	 */
	public static function save_meta( $term_id, $data ) {

		$term_id = \get_term( $term_id )->term_id ?? null;

		if ( empty( $term_id ) ) return;

		/**
		 * @NOTE Do not remove indexes. We store all data, even if empty,
		 *       to ensure defaults don't override them.
		 * @since 3.1.0
		 * @since 5.0.0 1. Removed 3rd and 4th parameters (`$tt_id` and `$taxonomy`).
		 *              2. No longer sends pre-sanitized data to the filter.
		 * @param array  $data     The data that's going to be saved.
		 * @param int    $term_id  The term ID.
		 */
		$data = (array) \apply_filters(
			'the_seo_framework_save_term_data',
			array_merge(
				static::get_default_meta( $term_id ),
				$data,
			),
			$term_id,
		);

		unset( static::$meta_memo[ $term_id ] );

		// Do we want to cycle through the data, so we store only the non-defaults? @see save_post_meta()
		\update_term_meta( $term_id, \THE_SEO_FRAMEWORK_TERM_OPTIONS, $data );
	}

	/**
	 * Deletes term meta.
	 * Deletes only the default data keys as set by `get_default_meta()`
	 * or everything when no custom keys are set.
	 *
	 * @since 2.7.0
	 * @since 4.0.0 Removed 2nd, unused, parameter.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `delete_term_meta`.
	 * @ignore Unused internally. Public API.
	 *
	 * @param int $term_id Term ID.
	 */
	public static function delete_meta( $term_id ) {

		// If this results in an empty data string, all data has already been removed by WP core.
		$data = \get_term_meta( $term_id, \THE_SEO_FRAMEWORK_TERM_OPTIONS, true );

		if ( \is_array( $data ) ) {
			foreach ( static::get_default_meta( $term_id ) as $key => $value )
				unset( $data[ $key ] );
		}

		// Always unset. We must refill defaults later.
		unset( static::$meta_memo[ $term_id ] );

		// Only delete when no values are left, because someone else might've filtered it.
		if ( empty( $data ) ) {
			\delete_term_meta( $term_id, \THE_SEO_FRAMEWORK_TERM_OPTIONS );
		} else {
			\update_term_meta( $term_id, \THE_SEO_FRAMEWORK_TERM_OPTIONS, $data );
		}
	}
}
