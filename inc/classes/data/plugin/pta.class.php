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
 * Holds a collection of Post Type Archive data interface methods for TSF.
 *
 * @since 5.0.0
 * @since 5.1.0 Added the Property_Refresher trait.
 * @access protected
 *         Use tsf()->data()->plugin()->pta() instead.
 */
class PTA {
	use Property_Refresher;

	/**
	 * @since 5.0.0
	 * @var array[] Stored pta meta data.
	 */
	private static $meta_memo = [];

	/**
	 * Returns a single post type archive item's value.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 1. Removed the third `$use_cache` parameter.
	 *              2. Moved from `\The_SEO_Framework\Load`.
	 *              3. Renamed from `get_post_type_archive_meta_item`.
	 *
	 * @param string $item      The item to get.
	 * @param string $post_type The post type.
	 * @return ?mixed The post type archive's meta item value. Null when item isn't registered.
	 */
	public static function get_meta_item( $item, $post_type = '' ) {

		$post_type = $post_type ?: Query::get_current_post_type();

		return $post_type
			? static::get_meta( $post_type )[ $item ] ?? null
			: null;
	}

	/**
	 * Returns all post type archive meta.
	 *
	 * We do not test whether a post type is supported, for it'll conflict with data-fills on the
	 * SEO settings page. This meta should never get called on the front-end if the post type is
	 * disabled, anyway, for we never query post types externally, aside from the SEO settings page.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 1. The first parameter may now be empty to autodetermine post type.
	 *              2. Removed the second `$use_cache` parameter.
	 *              3. Moved from `\The_SEO_Framework\Load`.
	 *              4. Renamed from `get_post_type_archive_meta`.
	 * @since 5.1.0 1. Now returns the default meta if the PTA isn't supported.
	 *              2. Now registers `meta_memo` for automated refreshes.
	 *
	 * @param string $post_type The post type.
	 * @return array The post type archive's meta item's values.
	 */
	public static function get_meta( $post_type = '' ) {

		$post_type = $post_type ?: Query::get_current_post_type();

		if ( isset( static::$meta_memo[ $post_type ] ) )
			return static::$meta_memo[ $post_type ];

		// Code smell: the empty test is for performance since the memo can be bypassed by input vars.
		empty( static::$meta_memo ) and static::register_automated_refresh( 'meta_memo' );

		// We test post type support for "post_query"-queries might get past this point.
		if ( empty( $post_type ) || ! Post_Type::is_supported( $post_type ) )
			return static::$meta_memo[ $post_type ] = static::get_default_meta( $post_type );

		// Keep lucky first when exceeding nice numbers. This way, we won't overload memory in memoization.
		if ( \count( static::$meta_memo ) > 69 )
			static::$meta_memo = \array_slice( static::$meta_memo, 0, 7, true );

		// Yes, we abide by "settings". WordPress never gave us Post Type Archive settings-pages.
		$is_headless = is_headless( 'settings' );

		if ( $is_headless ) {
			$meta = [];
		} else {
			$meta = Data\Plugin::get_option( 'pta', $post_type ) ?: [];
		}

		/**
		 * @since 4.2.0
		 * @note Do not delete/unset/add indexes! It'll cause errors.
		 * @param array $meta      The current post type archive meta.
		 * @param int   $post_type The post type.
		 * @param bool  $headless  Whether the meta are headless.
		 */
		return static::$meta_memo[ $post_type ] = \apply_filters(
			'the_seo_framework_post_type_archive_meta',
			array_merge(
				static::get_default_meta( $post_type ),
				$meta,
			),
			$post_type,
			$is_headless,
		);
	}

	/**
	 * Returns an array of all public post type archive option defaults.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `get_all_post_type_archive_meta_defaults`.
	 *
	 * @return array[] The Post Type Archive Metadata default options
	 *                 of all public Post Type archives.
	 */
	public static function get_all_default_meta() {

		$defaults = [];

		foreach ( Post_Type::get_public_pta() as $pta )
			$defaults[ $pta ] = static::get_default_meta( $pta );

		return $defaults;
	}

	/**
	 * Returns an array of default post type archive meta.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `get_post_type_archive_meta_defaults`.
	 *              3. Added 'tw_card_type' value.
	 *
	 * @param int $post_type The post type.
	 * @return array The Post Type Archive Metadata default options.
	 */
	public static function get_default_meta( $post_type = '' ) {
		/**
		 * @since 4.2.0
		 * @param array $defaults
		 * @param int   $term_id The current term ID.
		 */
		return (array) \apply_filters(
			'the_seo_framework_get_post_type_archive_meta_defaults',
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
			$post_type ?: Query::get_current_post_type(),
		);
	}
}
