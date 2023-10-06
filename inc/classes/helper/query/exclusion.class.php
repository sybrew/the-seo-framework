<?php
/**
 * @package The_SEO_Framework\Classes\Helper\Query\Exclusion
 * @subpackage The_SEO_Framework\Query
 */

namespace The_SEO_Framework\Helper\Query;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\is_headless;

use \The_SEO_Framework\Data;
use \The_SEO_Framework\Helper\{
	Post_Types,
	Query, // Yes, it is legal to share class and namespaces.
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
 * Excludes stuff from the query.
 *
 * @since 4.3.0
 * @access protected
 * @internal Use tsf()->query()->exclusion() instead, which pooled this class.
 */
class Exclusion {

	/**
	 * Clears static excluded IDs cache.
	 *
	 * @since 4.3.0
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function clear_excluded_post_ids_cache() {
		return Data\Plugin::update_site_cache( 'excluded_ids', [] );
	}

	/**
	 * Builds and returns the excluded post IDs.
	 *
	 * Memoizes the database request.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 Now no longer crashes on database errors.
	 * @since 4.1.4 1. Now tests against post type exclusions.
	 *              2. Now considers headlessness. This method runs only on the front-end.
	 * @since 4.3.0 1. Now uses the static cache methods instead of non-expiring-transients.
	 *              2. Moved to `The_SEO_Framework\Helper\Query\Exclusion`
	 *
	 * @return array : { 'archive', 'search' }
	 */
	public static function get_excluded_ids_from_cache() {

		if ( is_headless( 'meta' ) )
			return [
				'archive' => '',
				'search'  => '',
			];

		$cache = Data\Plugin::get_site_cache( 'excluded_ids' );

		if ( isset( $cache['archive'], $cache['search'] ) ) return $cache;

		global $wpdb;

		$supported_post_types = Post_Types::get_supported_post_types();
		$public_post_types    = Post_Types::get_public_post_types();

		$join  = '';
		$where = '';
		if ( $supported_post_types !== $public_post_types ) {
			// Post types can be registered arbitrarily through other plugins, even manually by non-super-admins. Prepare!
			$post_type__in = "'" . implode( "','", array_map( 'esc_sql', $supported_post_types ) ) . "'";

			// This is as fast as I could make it. Yes, it uses IN, but only on a (tiny) subset of data.
			$join  = "LEFT JOIN {$wpdb->posts} ON {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID";
			$where = "AND {$wpdb->posts}.post_type IN ($post_type__in)";
		}

		// Two separated equals queries are faster than a single IN with 'meta_key'.
		// phpcs:disable, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- We prepared our whole lives.
		$cache = [
			'archive' => $wpdb->get_results(
				"SELECT post_id, meta_value FROM $wpdb->postmeta $join WHERE meta_key = 'exclude_from_archive' $where"
			),
			'search'  => $wpdb->get_results(
				"SELECT post_id, meta_value FROM $wpdb->postmeta $join WHERE meta_key = 'exclude_local_search' $where"
			),
		];
		// phpcs:enable, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		foreach ( [ 'archive', 'search' ] as $type ) {
			array_walk(
				$cache[ $type ],
				static function ( &$v ) {
					if ( isset( $v->meta_value, $v->post_id ) && $v->meta_value ) {
						$v = (int) $v->post_id;
					} else {
						$v = false;
					}
				}
			);
			$cache[ $type ] = array_filter( $cache[ $type ] );
		}

		Data\Plugin::update_site_cache( 'excluded_ids', $cache );

		return $cache;
	}

	/**
	 * Determines whether the archive query adjustment is blocked.
	 *
	 * We do NOT treat this feature with security: If a post still slips through
	 * a query, then so be it. The post may be accessed anyway, otherwise,
	 * if not redirected. This last part is of concern, however, because one
	 * might think the contents of a post is hidden thanks to the redirect, for it
	 * to be exposable via other means. Nevertheless, we never (and won't ever)
	 * redirect REST queries, which may access post content regardless of user settings.
	 *
	 * Perhaps, we should add a disclaimer: Even IF you redirect the post, noindex it,
	 * exclude it from search and archive queries, the post content may still be readable
	 * to the public.
	 *
	 * @since 2.9.4
	 * @since 3.1.0 Now checks for the post type.
	 * @since 4.1.4 1. Renamed from `is_archive_query_adjustment_blocked()`
	 *              2. Added taxonomy-supported lookups.
	 *              3. Added WP Rest checks for the Block Editor.
	 * @since 4.2.0 Improved supported taxonomy loop.
	 * @since 4.2.6 Added check for `did_action( 'wp_loaded' )` early, before queries are tested and cached.
	 * @since 4.2.7 No longer affects the sitemap query.
	 * @since 4.3.0 Moved to `The_SEO_Framework\Helper\Query\Exclusion`
	 *
	 * @param \WP_Query $wp_query WP_Query object.
	 * @return bool
	 */
	private static function is_query_adjustment_blocked( $wp_query ) {

		static $has_filter = null;

		if ( null === $has_filter )
			$has_filter = \has_filter( 'the_seo_framework_do_adjust_archive_query' );

		if ( $has_filter ) {
			/**
			 * This filter affects both 'search-"archives"' and terms/taxonomies.
			 *
			 * @since 2.9.4
			 * @param bool      $do       True is unblocked (do adjustment), false is blocked (don't do adjustment).
			 * @param \WP_Query $wp_query The current query.
			 */
			if ( ! \apply_filters_ref_array( 'the_seo_framework_do_adjust_archive_query', [ true, $wp_query ] ) )
				return true;
		}

		if ( ! \did_action( 'wp_loaded' ) )
			return true;

		if ( \defined( 'REST_REQUEST' ) && \REST_REQUEST ) {
			$referer = \wp_get_referer();
			if ( str_contains( $referer, 'post.php' ) || str_contains( $referer, 'post-new.php' ) ) {
				/**
				 * WordPress should've authenthicated the user at
				 * WP_REST_Server::check_authentication() -> rest_cookie_check_errors() -> wp_nonce et al.
				 * before executing the query. For REST_REQUEST can not be true otherwise. Ergo,
				 * \current_user_can() should work. If it returns true, we can trust it's a safe request.
				 * If it returns false, the user may still be logged in, but the request isn't sent via
				 * WordPress's API with the proper nonces supplied. This is as perfect as it can be.
				 */
				if ( \current_user_can( 'edit_posts' ) )
					return true;
			}
		}

		// If doing sitemap, don't adjust query via query settings.
		if ( Query::is_sitemap() )
			return true;

		// This should primarily affect 'terms'. Test if TSF is blocked from supporting said terms.
		if ( ! empty( $wp_query->tax_query->queries ) ) {
			$supported = true;

			foreach ( $wp_query->tax_query->queries as $_query ) {
				if ( isset( $_query['taxonomy'] ) ) {
					$supported = Taxonomies::is_taxonomy_supported( $_query['taxonomy'] );
					// If just one tax is supported for this query, greenlight it: all must be blocking.
					if ( $supported ) break;
				}
			}

			if ( ! $supported )
				return true;
		}

		return false;
	}

	/**
	 * Alters search results after database query.
	 *
	 * @since 2.9.4
	 * @since 4.3.0 Moved to `The_SEO_Framework\Helper\Query\Exclusion`
	 * @access private
	 *
	 * @param array     $posts    The array of retrieved posts.
	 * @param \WP_Query $wp_query The WP_Query instance.
	 * @return array $posts
	 */
	public static function _alter_search_query_post( $posts, $wp_query ) {

		if ( $wp_query->is_search ) {
			if ( static::is_query_adjustment_blocked( $wp_query ) )
				return $posts;

			foreach ( $posts as $n => $post ) {
				if ( Data\Plugin\Post::get_post_meta_item( 'exclude_local_search', $post->ID ) )
					unset( $posts[ $n ] );
			}
			// Reset numeric index.
			$posts = array_values( $posts );
		}

		return $posts;
	}

	/**
	 * Alters archive query.
	 *
	 * @since 2.9.4
	 * @since 3.0.0 Exchanged meta query for post__not_in query.
	 * @since 4.3.0 Moved to `The_SEO_Framework\Helper\Query\Exclusion`
	 * @see Twenty Fourteen theme @source \Featured_Content::pre_get_posts()
	 * @access private
	 *
	 * @param \WP_Query $wp_query The WP_Query instance.
	 * @return void Early if query alteration is useless or blocked.
	 */
	public static function _alter_archive_query_in( $wp_query ) {

		if ( $wp_query->is_archive || $wp_query->is_home ) {
			if ( static::is_query_adjustment_blocked( $wp_query ) )
				return;

			$excluded = static::get_excluded_ids_from_cache()['archive'];

			if ( ! $excluded )
				return;

			$post__not_in = $wp_query->get( 'post__not_in' );

			if ( ! empty( $post__not_in ) ) {
				$excluded = array_unique(
					array_merge( (array) $post__not_in, $excluded )
				);
			}

			$wp_query->set( 'post__not_in', $excluded );
		}
	}

	/**
	 * Alters archive results after database query.
	 *
	 * @since 2.9.4
	 * @since 4.3.0 Moved to `The_SEO_Framework\Helper\Query\Exclusion`
	 * @access private
	 *
	 * @param array     $posts    The array of retrieved posts.
	 * @param \WP_Query $wp_query The WP_Query instance.
	 * @return array $posts
	 */
	public static function _alter_archive_query_post( $posts, $wp_query ) {

		if ( $wp_query->is_archive || $wp_query->is_home ) {
			if ( static::is_query_adjustment_blocked( $wp_query ) )
				return $posts;

			foreach ( $posts as $n => $post ) {
				if ( Data\Plugin\Post::get_post_meta_item( 'exclude_from_archive', $post->ID ) )
					unset( $posts[ $n ] );
			}
			// Reset numeric index.
			$posts = array_values( $posts );
		}

		return $posts;
	}

	/**
	 * Alters search query.
	 *
	 * @since 2.9.4
	 * @since 3.0.0 Exchanged meta query for post__not_in query.
	 * @since 4.3.0 Moved to `The_SEO_Framework\Helper\Query\Exclusion`
	 * @see Twenty Fourteen theme @source \Featured_Content::pre_get_posts()
	 * @access private
	 *
	 * @param \WP_Query $wp_query The WP_Query instance.
	 * @return void Early if no search query is found.
	 */
	public static function _alter_search_query_in( $wp_query ) {

		// Don't exclude pages in wp-admin.
		if ( $wp_query->is_search ) {
			// Only interact with an actual Search Query.
			if ( ! isset( $wp_query->query['s'] ) )
				return;

			if ( static::is_query_adjustment_blocked( $wp_query ) )
				return;

			$excluded = static::get_excluded_ids_from_cache()['search'];

			if ( ! $excluded )
				return;

			$post__not_in = $wp_query->get( 'post__not_in' );

			if ( ! empty( $post__not_in ) ) {
				$excluded = array_unique(
					array_merge( (array) $post__not_in, $excluded )
				);
			}

			$wp_query->set( 'post__not_in', $excluded );
		}
	}
}
