<?php
/**
 * @package The_SEO_Framework\Classes\Front\Query
 * @subpackage The_SEO_Framework\Feed
 */

namespace The_SEO_Framework\Front;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\{
	Data,
	Helper,
	Helper\Query\Exclusion,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2020 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Prepares query modifications.
 *
 * @since 5.0.0
 * @access private
 */
final class Query {

	/**
	 * Alters search results after database query.
	 *
	 * @since 2.9.4
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `alter_search_query_post`.
	 * @access private
	 *
	 * @param array     $posts    The array of retrieved posts.
	 * @param \WP_Query $wp_query The WP_Query instance.
	 * @return array $posts
	 */
	public static function alter_search_query_post( $posts, $wp_query ) {

		if ( $wp_query->is_search ) {
			if ( static::is_query_adjustment_blocked( $wp_query ) )
				return $posts;

			foreach ( $posts as $n => $post ) {
				if ( Data\Plugin\Post::get_meta_item( 'exclude_local_search', $post->ID ) )
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
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `_alter_archive_query_in`.
	 * @see Twenty Fourteen theme @source \Featured_Content::pre_get_posts()
	 * @access private
	 *
	 * @param \WP_Query $wp_query The WP_Query instance.
	 * @return void Early if query alteration is useless or blocked.
	 */
	public static function alter_archive_query_in( $wp_query ) {

		if ( $wp_query->is_archive || $wp_query->is_home ) {
			if ( static::is_query_adjustment_blocked( $wp_query ) )
				return;

			$excluded = Exclusion::get_excluded_ids_from_cache()['archive'];

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
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `_alter_archive_query_post`.
	 * @access private
	 *
	 * @param array     $posts    The array of retrieved posts.
	 * @param \WP_Query $wp_query The WP_Query instance.
	 * @return array $posts
	 */
	public static function alter_archive_query_post( $posts, $wp_query ) {

		if ( $wp_query->is_archive || $wp_query->is_home ) {
			if ( static::is_query_adjustment_blocked( $wp_query ) )
				return $posts;

			foreach ( $posts as $n => $post ) {
				if ( Data\Plugin\Post::get_meta_item( 'exclude_from_archive', $post->ID ) )
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
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `_alter_search_query_in`.
	 * @see Twenty Fourteen theme @source \Featured_Content::pre_get_posts()
	 * @access private
	 *
	 * @param \WP_Query $wp_query The WP_Query instance.
	 * @return void Early if no search query is found.
	 */
	public static function alter_search_query_in( $wp_query ) {

		// Don't exclude pages in wp-admin.
		if ( $wp_query->is_search ) {
			// Only interact with an actual Search Query.
			if ( ! isset( $wp_query->query['s'] ) )
				return;

			if ( static::is_query_adjustment_blocked( $wp_query ) )
				return;

			$excluded = Exclusion::get_excluded_ids_from_cache()['search'];

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
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param \WP_Query $wp_query WP_Query object.
	 * @return bool
	 */
	private static function is_query_adjustment_blocked( $wp_query ) {

		static $has_filter;

		$has_filter ??= \has_filter( 'the_seo_framework_do_adjust_archive_query' );

		/**
		 * This filter affects both 'search-"archives"' and terms/taxonomies.
		 *
		 * @since 2.9.4
		 * @param bool      $do       True is unblocked (do adjustment), false is blocked (don't do adjustment).
		 * @param \WP_Query $wp_query The current query.
		 */
		if ( $has_filter && ! \apply_filters( 'the_seo_framework_do_adjust_archive_query', true, $wp_query ) )
			return true;

		if ( ! \did_action( 'wp_loaded' ) )
			return true;

		// TODO WP 6.5+ https://core.trac.wordpress.org/ticket/42061: wp_is_serving_rest_request()
		if ( \defined( 'REST_REQUEST' ) && \REST_REQUEST ) {
			$referer = \wp_get_referer();
			if ( str_contains( $referer, 'post.php' ) || str_contains( $referer, 'post-new.php' ) ) {
				/**
				 * WordPress should've authenthicated the user at
				 * WP_REST_Server::check_authentication() -> rest_cookie_check_errors() -> wp_nonce etc.
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
		if ( Helper\Query::is_sitemap() )
			return true;

		// This should primarily affect 'terms'. Test if TSF is blocked from supporting said terms.
		if ( ! empty( $wp_query->tax_query->queries ) ) {
			$supported = true;

			foreach ( $wp_query->tax_query->queries as $_query ) {
				if ( isset( $_query['taxonomy'] ) ) {
					$supported = Helper\Taxonomy::is_supported( $_query['taxonomy'] );
					// If just one tax is supported for this query, greenlight it: all must be blocking.
					if ( $supported ) break;
				}
			}

			if ( ! $supported )
				return true;
		}

		return false;
	}
}
