<?php
/**
 * @package The_SEO_Framework\Classes\Helper\Post_Type
 * @subpackage The_SEO_Framework\Query
 */

namespace The_SEO_Framework\Helper;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\{
	memo,
	umemo,
};

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
 * Holds a collection of helper methods for Post Types.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->post_type() instead.
 */
class Post_Type {

	/**
	 * Determines if the post type is disabled from SEO all optimization.
	 *
	 * @since 3.1.0
	 * @since 3.1.2 Now is fiterable.
	 * @since 4.0.5 The `$post_type` fallback now uses a real query ID, instead of `$GLOBALS['post']`;
	 *              mitigating issues with singular-archives pages (blog, shop, etc.).
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `is_post_type_disabled`.
	 *
	 * @param string $post_type Optional. The post type to check.
	 * @return bool True if disabled, false otherwise.
	 */
	public static function is_disabled( $post_type = '' ) {

		$post_type = $post_type ?: Query::get_current_post_type();

		/**
		 * @since 3.1.2
		 * @param bool   $disabled
		 * @param string $post_type
		 */
		return (bool) \apply_filters(
			'the_seo_framework_post_type_disabled',
			Data\Plugin::get_option( 'disabled_post_types', $post_type ),
			$post_type,
		);
	}

	/**
	 * Detects if the current or inputted post type is supported and not disabled.
	 *
	 * @since 3.1.0
	 * @since 4.0.5 The `$post_type` fallback now uses a real query ID, instead of `$GLOBALS['post']`;
	 *              mitigating issues with singular-archives pages (blog, shop, etc.).
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `is_post_type_supported`.
	 *
	 * @param string $post_type Optional. The post type to check.
	 * @return bool
	 */
	public static function is_supported( $post_type = '' ) {

		$post_type = $post_type ?: Query::get_current_post_type();

		/**
		 * @since 2.6.2
		 * @since 3.1.0 The first parameter is always a boolean now.
		 * @param bool   $supported           Whether the post type is supported.
		 * @param string $post_type_evaluated The evaluated post type.
		 */
		return (bool) \apply_filters(
			'the_seo_framework_supported_post_type',
			$post_type
				&& ! static::is_disabled( $post_type )
				&& \in_array( $post_type, static::get_all_public(), true ),
			$post_type,
		);
	}

	/**
	 * Detects if the current or inputted post type's archive is supported and not disabled.
	 *
	 * @since 4.2.8
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `is_post_type_archive_supported`.
	 *
	 * @param string $post_type Optional. The post type's archive to check.
	 * @return bool
	 */
	public static function is_pta_supported( $post_type = '' ) {

		$post_type = $post_type ?: Query::get_current_post_type();

		/**
		 * @since 4.2.8
		 * @param bool   $supported           Whether the post type archive is supported.
		 * @param string $post_type_evaluated The evaluated post type.
		 */
		return (bool) \apply_filters(
			'the_seo_framework_supported_post_type_archive',
			$post_type
				&& static::is_supported( $post_type )
				&& \in_array( $post_type, static::get_public_pta(), true ),
			$post_type,
		);
	}

	/**
	 * Checks (current) Post Type for having taxonomical archives.
	 * Memoizes the return value for the input argument.
	 *
	 * @since 2.9.3
	 * @since 4.0.5 The `$post_type` fallback now uses a real query ID, instead of `$GLOBALS['post']`;
	 *              mitigating issues with singular-archives pages (blog, shop, etc.).
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `post_type_supports_taxonomies`.
	 * @global \WP_Screen $current_screen
	 *
	 * @param string $post_type Optional. The post type to check.
	 * @return bool True when the post type has taxonomies.
	 */
	public static function supports_taxonomies( $post_type = '' ) {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo( null, $post_type ) ) return $memo;

		$post_type = $post_type ?: Query::get_current_post_type();

		// Return false if no post type if found -- do not memo that, for query call might be too early.
		return $post_type && memo( (bool) \get_object_taxonomies( $post_type, 'names' ), $post_type );
	}

	/**
	 * Returns a list of all supported post types with archives.
	 * Memoizes the return value.
	 *
	 * @since 4.2.0
	 * @since 4.2.8 Now filters via `static::is_post_type_archive_supported()`.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `get_supported_post_type_archives`.
	 *
	 * @return string[] Supported post types with post type archive support.
	 */
	public static function get_all_supported_pta() {
		return memo() ?? memo( array_values(
			array_filter(
				static::get_public_pta(),
				[ static::class, 'is_pta_supported' ],
			)
		) );
	}

	/**
	 * Gets all post types that have PTA and could support SEO.
	 * Memoizes the return value.
	 *
	 * @since 4.2.0
	 * @since 4.2.8 Added filter `the_seo_framework_public_post_type_archives`.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `get_public_post_type_archives`.
	 *
	 * @return string[] Public post types with post type archive support.
	 */
	public static function get_public_pta() {
		return umemo( __METHOD__ )
			?? umemo(
				__METHOD__,
				/**
				 * Do not consider using this filter. Properly register your post type, noob.
				 *
				 * @since 4.2.8
				 * @param string[] $post_types The public post types.
				 */
				(array) \apply_filters(
					'the_seo_framework_public_post_type_archives',
					array_values(
						array_filter(
							static::get_all_public(),
							fn( $post_type ) => \get_post_type_object( $post_type )->has_archive ?? false,
						)
					)
				)
			);
	}

	/**
	 * Returns a list of all supported post types.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `get_supported_post_types`.
	 *
	 * @return string[] All supported post types.
	 */
	public static function get_all_supported() {
		return memo() ?? memo( array_values(
			array_filter(
				static::get_all_public(),
				[ static::class, 'is_supported' ],
			)
		) );
	}

	/**
	 * Gets all post types that could possibly support SEO.
	 * Memoizes the return value.
	 *
	 * @since 4.1.0
	 * @since 4.1.4 Now resets the index keys of the return value.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `get_public_post_types`.
	 *              3. Is now public.
	 *
	 * @return string[] All public post types.
	 */
	public static function get_all_public() {
		return umemo( __METHOD__ )
			?? umemo(
				__METHOD__,
				/**
				 * Do not consider using this filter. Properly register your post type, noob.
				 *
				 * @since 4.2.0
				 * @param string[] $post_types The public post types.
				 */
				(array) \apply_filters(
					'the_seo_framework_public_post_types',
					array_values( array_filter(
						array_unique( array_merge(
							static::get_all_forced_supported(),
							// array_keys() because get_post_types() gives a sequential array.
							array_keys( (array) \get_post_types( [ 'public' => true ] ) )
						) ),
						'is_post_type_viewable',
					) )
				)
			);
	}

	/**
	 * Returns a list of builtin public post types.
	 *
	 * @since 3.1.0
	 * @since 4.2.0 Removed memoization.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `get_forced_supported_post_types`.
	 *              3. Is now public.
	 *
	 * @return string[] Forced supported post types.
	 */
	public static function get_all_forced_supported() {
		/**
		* @since 3.1.0
		* @param string[] $forced Forced supported post types.
		*/
		return (array) \apply_filters(
			'the_seo_framework_forced_supported_post_types',
			array_values( \get_post_types( [
				'public'   => true,
				'_builtin' => true,
			] ) ),
		);
	}

	/**
	 * Returns an array of hierarchical post types.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now gets hierarchical post types that don't support rewrite, as well.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `get_hierarchical_post_types`.
	 *
	 * @return string[] All public hierarchical post types.
	 */
	public static function get_all_hierarchical() {
		return memo() ?? memo(
			\get_post_types(
				[
					'hierarchical' => true,
					'public'       => true,
				],
				'names',
			)
		);
	}

	/**
	 * Returns an array of nonhierarchical post types.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now gets non-hierarchical post types that don't support rewrite, as well.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `get_nonhierarchical_post_types`.
	 *
	 * @return array The public nonhierarchical post types.
	 */
	public static function get_all_nonhierarchical() {
		return memo() ?? memo(
			\get_post_types(
				[
					'hierarchical' => false,
					'public'       => true,
				],
				'names',
			)
		);
	}

	/**
	 * Returns the post type object label. Either plural or singular.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `get_post_type_label`.
	 *
	 * @param string $post_type The post type. Required.
	 * @param bool   $singular  Whether to get the singlural or plural name.
	 * @return string The Post Type name/label, if found.
	 */
	public static function get_label( $post_type, $singular = true ) {
		return \get_post_type_object( $post_type )->labels->{
			$singular ? 'singular_name' : 'name'
		} ?? '';
	}
}
