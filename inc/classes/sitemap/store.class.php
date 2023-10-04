<?php
/**
 * @package The_SEO_Framework\Classes\Sitemap\Store
 * @subpackage The_SEO_Framework\Sitemap
 */

namespace The_SEO_Framework\Sitemap;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use const \The_SEO_Framework\ROBOTS_IGNORE_PROTECTION;

use \The_SEO_Framework\Data,
	\The_SEO_Framework\Meta;

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
 * Handles the data and caching interface for sitemaps.
 *
 * @since 4.3.0
 * @access public
 * @internal
 * @final Can't be extended.
 */
final class Store {

	/**
	 * Returns a unique cache key suffix per blog and language.
	 *
	 * @since 4.3.0
	 *
	 * @param string $key The cache key.
	 * @return string The cache key with blog ID and locale appended.
	 */
	public static function build_unique_cache_key_suffix( $key ) {

		// Do not memoize: May change at runtime.
		$locale = \get_locale();

		return "{$key}_{$GLOBALS['blog_id']}_{$locale}";
	}

	/**
	 * Refreshes sitemaps on post change.
	 *
	 * @since 4.3.0
	 * @access private
	 *
	 * @param int $post_id The Post ID that has been updated.
	 * @return bool True on success, false on failure.
	 */
	public static function _refresh_sitemap_on_post_change( $post_id ) {

		// Don't refresh sitemap on revision.
		if ( ! $post_id || \wp_is_post_revision( $post_id ) ) return false;

		return Registry::refresh_sitemaps();
	}

	/**
	 * Checks whether the permalink structure is updated.
	 *
	 * @since 4.3.0
	 * @access private
	 *
	 * @return bool Whether if sitemap transient is deleted.
	 */
	public static function _refresh_sitemap_transient_permalink_updated() {

		if (
			   ( isset( $_POST['permalink_structure'] ) || isset( $_POST['category_base'] ) )
			&& \check_admin_referer( 'update-permalink' )
		) {
				return Registry::refresh_sitemaps();
		}

		return false;
	}

	/**
	 * Clears sitemap transients.
	 *
	 * @since 4.3.0
	 */
	public static function clear_sitemap_transients() {

		$sitemap = Registry::get_instance();

		foreach ( $sitemap->get_sitemap_endpoint_list() as $id => $data ) {
			$transient = $sitemap->get_transient_key( $id );

			if ( $transient )
				\delete_transient( $transient );
		}

		/**
		 * @since 4.3.0
		 */
		\do_action( 'the_seo_framework_cleared_sitemap_transients' );

		/**
		 * @since 3.1.0
		 * @since 4.3.0 Deprecated. Use action 'the_seo_framework_cleared_sitemap_transients' instead.
		 *
		 * @param string $type    The flush type. Comes in handy when you use a catch-all function.
		 * @param int    $id      The post, page or TT ID. Defaults to Query::get_the_real_id().
		 * @param array  $args    Additional arguments. They can overwrite $type and $id.
		 * @param array  $success Whether the action cleared. Set to always be true since deprecation.
		 */
		\do_action_deprecated(
			'the_seo_framework_delete_cache_sitemap',
			[
				'sitemap',
				0,
				[ 'type' => 'sitemap' ],
				[ true ],
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_cleared_sitemap_transients'
		);
	}

	/**
	 * Returns the sitemap post query limit.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $hierarchical Whether the query is for hierarchical post types or not.
	 * @return int The post limit
	 */
	public static function get_sitemap_post_limit( $hierarchical = false ) {
		/**
		 * @since 2.2.9
		 * @since 2.8.0 Increased to 1200 from 700.
		 * @since 3.1.0 Now returns an option value; it falls back to the default value if not set.
		 * @since 4.0.0 1. The default is now 3000, from 1200.
		 *              2. Now passes a second parameter.
		 * @param int $total_post_limit
		 * @param bool $hierarchical Whether the query is for hierarchical post types or not.
		 */
		return (int) \apply_filters(
			'the_seo_framework_sitemap_post_limit',
			Data\Plugin::get_option( 'sitemap_query_limit' ),
			$hierarchical
		);
	}

	/**
	 * Determines if post is possibly included in the sitemap.
	 *
	 * @since 4.3.0
	 *
	 * @param int $post_id The Post ID to check.
	 * @return bool True if included, false otherwise.
	 */
	public static function is_post_included_in_sitemap( $post_id ) {

		static $excluded;

		if ( ! isset( $excluded ) ) {
			/**
			 * @since 2.5.2
			 * @since 2.8.0 No longer accepts '0' as entry.
			 * @since 3.1.0 '0' is accepted again.
			 * @param int[] $excluded Sequential list of excluded IDs: [ int ...post_id ]
			 */
			$excluded = (array) \apply_filters( 'the_seo_framework_sitemap_exclude_ids', [] );

			// isset() is faster than in_array(). So, we flip it.
			$excluded = $excluded ? array_flip( $excluded ) : [];
		}

		$included = ! isset( $excluded[ $post_id ] );

		while ( $included ) {
			$_generator_args = [ 'id' => $post_id ];

			// ROBOTS_IGNORE_PROTECTION as we don't need to test 'private' ('post_status'=>'publish'), nor 'password' ('has_password'=>false)
			$included = 'noindex'
				!== (
					Meta\Robots::generate_meta(
						$_generator_args,
						[ 'noindex' ],
						ROBOTS_IGNORE_PROTECTION
					)['noindex'] ?? false // We cast type false for Zend tests strict type before identical-string-comparing.
				);

			if ( ! $included ) break;

			// This is less likely than a "noindex," even though it's faster to process, we put it later.
			$included = ! Meta\URI::get_redirect_url( $_generator_args );
			break;
		}

		return $included;
	}

	/**
	 * Determines if term is possibly included in the sitemap.
	 *
	 * @since 4.3.0
	 *
	 * @param int    $term_id  The Term ID to check.
	 * @param string $taxonomy The taxonomy.
	 * @return bool True if included, false otherwise.
	 */
	public static function is_term_included_in_sitemap( $term_id, $taxonomy ) {

		static $excluded;

		if ( ! isset( $excluded ) ) {
			/**
			 * @since 4.0.0
			 * @param int[] $excluded Sequential list of excluded IDs: [ int ...term_id ]
			 */
			$excluded = (array) \apply_filters( 'the_seo_framework_sitemap_exclude_term_ids', [] );

			// isset() is faster than in_array(). So, we flip it.
			$excluded = $excluded ? array_flip( $excluded ) : [];
		}

		$included = ! isset( $excluded[ $term_id ] );

		// Yes, 90% of this code code isn't DRY. However, terms !== posts. terms == posts, though :).
		// Really: <https://core.trac.wordpress.org/ticket/50568>
		while ( $included ) {
			$_generator_args = [
				'id'  => $term_id,
				'tax' => $taxonomy,
			];

			// ROBOTS_IGNORE_PROTECTION is not tested for terms. However, we may use that later.
			$included = 'noindex'
				!== (
					Meta\Robots::generate_meta(
						$_generator_args,
						[ 'noindex' ],
						ROBOTS_IGNORE_PROTECTION
					)['noindex'] ?? false // We cast type false for Zend tests strict type before identical-string-comparing.
				);

			if ( ! $included ) break;

			// This is less likely than a "noindex," even though it's faster to process, we put it later.
			$included = ! Meta\URI::get_redirect_url( $_generator_args );
			break;
		}

		return $included;
	}
}
