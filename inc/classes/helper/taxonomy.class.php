<?php
/**
 * @package The_SEO_Framework\Classes\Helper\Taxonomy
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
 * Holds a collection of helper methods for Taxonomy.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->taxonomy() instead.
 */
class Taxonomy {

	/**
	 * Checks if the taxonomy isn't disabled, and that at least one taxonomy
	 * objects post type supports The SEO Framework.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 1. Now returns true if at least one post type for the taxonomy is supported.
	 *              2. Now uses `is_post_type_supported()` instead of `is_post_type_disabled()`.
	 * @since 4.1.0 1. Now also checks for the option `disabled_taxonomies`.
	 *              2. Now applies filters `the_seo_framework_taxonomy_disabled`.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `is_taxonomy_disabled`.
	 *
	 * @param ?string $taxonomy The taxonomy name. Leave null to automatically determine.
	 * @return bool True if at least one post type in taxonomy is supported.
	 */
	public static function is_disabled( $taxonomy = null ) {

		$disabled = false;

		// First, test pertaining option directly.
		if ( $taxonomy && Data\Plugin::get_option( 'disabled_taxonomies', $taxonomy ) ) {
			$disabled = true;
		} else {
			// Then, test some() post types.
			// Populate $disabled within loop, for the taxonomy might not have post types at all.
			foreach ( static::get_post_types( $taxonomy ) as $type ) {
				if ( Post_Type::is_supported( $type ) ) {
					$disabled = false;
					break;
				}
				$disabled = true;
			}
		}

		/**
		 * @since 4.1.0
		 * @param bool    $disabled Whether the taxonomy is disabled.
		 * @param ?string $taxonomy The taxonomy name. Left null to automatically determine.
		 */
		return \apply_filters(
			'the_seo_framework_taxonomy_disabled',
			$disabled,
			$taxonomy,
		);
	}

	/**
	 * Determines if the taxonomy supports The SEO Framework.
	 *
	 * Checks if at least one taxonomy objects post type supports The SEO Framework,
	 * and whether the taxonomy is public and rewritable.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `is_taxonomy_supported`.
	 *
	 * @param string $taxonomy Optional. The taxonomy name.
	 * @return bool True if at least one post type in taxonomy isn't disabled.
	 */
	public static function is_supported( $taxonomy = '' ) {

		$taxonomy = $taxonomy ?: Query::get_current_taxonomy();

		/**
		 * @since 3.1.0
		 * @since 4.0.0 Now returns only returns false when all post types in the taxonomy aren't supported.
		 * @param bool   $post_type Whether the post type is supported
		 * @param string $post_type_evaluated The evaluated post type.
		 */
		return (bool) \apply_filters(
			'the_seo_framework_supported_taxonomy',
			$taxonomy
				&& ! static::is_disabled( $taxonomy )
				&& \in_array( $taxonomy, static::get_all_public(), true ),
			$taxonomy,
		);
	}

	/**
	 * Returns a list of all supported taxonomies.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `get_supported_taxonomies`.
	 *
	 * @return string[] All supported taxonomies.
	 */
	public static function get_all_supported() {
		return memo() ?? memo( array_values( array_filter(
			static::get_all_public(),
			[ static::class, 'is_supported' ],
		) ) );
	}

	/**
	 * Gets all taxonomies that could possibly support SEO.
	 * Memoizes the return value.
	 *
	 * @since 4.1.0
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `get_public_taxonomies`.
	 *              3. Is now public.
	 *
	 * @return string[] The taxonomies that are public.
	 */
	public static function get_all_public() {
		return umemo( __METHOD__ )
			?? umemo(
				__METHOD__,
				/**
				 * Do not consider using this filter. Properly register your taxonomy, noob.
				 *
				 * @since 4.2.0
				 * @param string[] $taxonomies The public taxonomies.
				 */
				(array) \apply_filters(
					'the_seo_framework_public_taxonomies',
					array_filter(
						array_unique( array_merge(
							static::get_all_forced_supported(),
							// array_values() because get_taxonomies() gives a sequential array.
							array_values( \get_taxonomies( [
								'public'   => true,
								'_builtin' => false,
							] ) )
						) ),
						'is_taxonomy_viewable',
					)
				)
			);
	}

	/**
	 * Returns a list of builtin public taxonomies.
	 *
	 * @since 4.1.0
	 * @since 4.2.0 Removed memoization.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `get_forced_supported_taxonomies`.
	 *              3. Is now public.
	 *
	 * @return string[] Forced supported taxonomies
	 */
	public static function get_all_forced_supported() {
		/**
		 * @since 4.1.0
		 * @param string[] $forced Forced supported taxonomies.
		 */
		return (array) \apply_filters(
			'the_seo_framework_forced_supported_taxonomies',
			array_values( \get_taxonomies( [
				'public'   => true,
				'_builtin' => true,
			] ) ),
		);
	}

	/**
	 * Returns a list of post types shared with the taxonomy.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `get_post_types_from_taxonomy`.
	 *
	 * @param string $taxonomy Optional. The taxonomy to check. Defaults to current screen/query taxonomy.
	 * @return array List of post types.
	 */
	public static function get_post_types( $taxonomy = '' ) {

		$taxonomy = $taxonomy ?: Query::get_current_taxonomy();
		$tax      = $taxonomy ? \get_taxonomy( $taxonomy ) : null;

		return $tax->object_type ?? [];
	}

	/**
	 * Returns hierarchical taxonomies for post type.
	 *
	 * @since 3.0.0
	 * @since 4.0.5 The `$post_type` fallback now uses a real query ID, instead of `$GLOBALS['post']`.
	 * @since 4.1.0 Now filters taxonomies more graciously--expecting broken taxonomies returned in the filter.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `get_hierarchical_taxonomies_as`.
	 *
	 * @param string $get       What to get. Accepts 'names' or 'objects'.
	 * @param string $post_type The post type. Will default to current post type.
	 * @return object[]|string[] The post type taxonomy objects or names.
	 */
	public static function get_hierarchical( $get = 'objects', $post_type = '' ) {

		$post_type = $post_type ?: Query::get_current_post_type();

		if ( ! $post_type )
			return [];

		$taxonomies = array_filter(
			\get_object_taxonomies( $post_type, 'objects' ),
			fn( $t ) => ! empty( $t->hierarchical ),
		);

		// If names isn't $get, assume objects.
		return 'names' === $get ? array_keys( $taxonomies ) : $taxonomies;
	}

	/**
	 * Returns the taxonomy type object label. Either plural or singular.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `get_taxonomy_label`.
	 *              3. The first parameter is now optional.
	 *
	 * @param string $taxonomy The taxonomy.
	 * @param bool   $singular Whether to get the singlural or plural name.
	 * @return string The Taxonomy Type name/label, if found.
	 */
	public static function get_label( $taxonomy = '', $singular = true ) {

		$taxonomy = $taxonomy ?: Query::get_current_taxonomy();
		$tax      = $taxonomy ? \get_taxonomy( $taxonomy ) : null;

		return $tax->labels->{
			$singular ? 'singular_name' : 'name'
		} ?? '';
	}
}
