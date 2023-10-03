<?php
/**
 * @package The_SEO_Framework\Classes\Data\Plugin\Post
 * @subpackage The_SEO_Framework\Data\Plugin
 */

namespace The_SEO_Framework\Data\Plugin;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\is_headless;

use \The_SEO_Framework\Data; // Yes, it is legal to share class and namespaces.

use \The_SEO_Framework\Helper\{
	Post_Types,
	Query,
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
 * Holds a collection of Post Type Archive data interface methods for TSF.
 *
 * @since 4.3.0
 * @access protected
 * @internal Use tsf()->data()->plugin->pta() instead.
 */
class PTA {

	/**
	 * @since 4.3.0
	 * @var array[] Stored pta meta data.
	 */
	private static $pta_meta = [];

	/**
	 * Flushes all PTA runtime cache.
	 *
	 * @since 4.3.0
	 * @internal
	 */
	public static function flush_cache() {
		static::$pta_meta = [];
	}

	/**
	 * Returns a single post type archive item's value.
	 *
	 * @since 4.2.0
	 * @since 4.3.0 1. Removed the third `$use_cache` parameter.
	 *              2. Moved to \The_SEO_Framework\Data\Plugin\PTA.
	 *
	 * @param string $item      The item to get.
	 * @param string $post_type The post type.
	 * @return ?mixed The post type archive's meta item value. Null when item isn't registered.
	 */
	public static function get_post_type_archive_meta_item( $item, $post_type = '' ) {

		$post_type = $post_type ?: Query::get_current_post_type();

		return $post_type
			? static::get_post_type_archive_meta( $post_type )[ $item ] ?? null
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
	 * @since 4.3.0 1. The first parameter may now be empty to autodetermine post type.
	 *              2. Removed the second `$use_cache` parameter.
	 *              2. Moved to \The_SEO_Framework\Data\Plugin\PTA.
	 *
	 * @param string $post_type The post type.
	 * @return array The post type archive's meta item's values.
	 */
	public static function get_post_type_archive_meta( $post_type = '' ) {

		$post_type = $post_type ?: Query::get_current_post_type();

		if ( isset( static::$pta_meta[ $post_type ] ) )
			return static::$pta_meta[ $post_type ];

		// We test post type support for "post_query"-queries might get past this point.
		if ( empty( $post_type ) || ! Post_Types::is_post_type_supported( $post_type ) )
			return static::$pta_meta[ $post_type ] = [];

		// Keep lucky first when exceeding nice numbers. This way, we won't overload memory in memoization.
		if ( \count( static::$pta_meta ) > 69 )
			static::$pta_meta = \array_slice( static::$pta_meta, 0, 7, true );

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
		return static::$pta_meta[ $post_type ] = \apply_filters(
			'the_seo_framework_post_type_archive_meta',
			array_merge(
				static::get_post_type_archive_meta_defaults( $post_type ),
				$meta
			),
			$post_type,
			$is_headless,
		);
	}

	/**
	 * Returns an array of all public post type archive option defaults.
	 *
	 * @since 4.2.0
	 * @since 4.3.0 Moved to \The_SEO_Framework\Data\Plugin\PTA.
	 *
	 * @return array[] The Post Type Archive Metadata default options
	 *                 of all public Post Type archives.
	 */
	public static function get_all_post_type_archive_meta_defaults() {

		$defaults = [];

		foreach ( Post_Types::get_public_post_type_archives() as $pta )
			$defaults[ $pta ] = static::get_post_type_archive_meta_defaults( $pta );

		return $defaults;
	}

	/**
	 * Returns an array of default post type archive meta.
	 *
	 * @since 4.2.0
	 * @since 4.3.0 Moved to \The_SEO_Framework\Data\Plugin\PTA.
	 *
	 * @param int $post_type The post type.
	 * @return array The Post Type Archive Metadata default options.
	 */
	public static function get_post_type_archive_meta_defaults( $post_type = '' ) {
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
