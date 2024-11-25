<?php
/**
 * @package The_SEO_Framework\Classes\Meta\URI
 * @subpackage The_SEO_Framework\Meta\URI
 */

namespace The_SEO_Framework\Meta\URI;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\{
	memo,
	umemo,
	get_query_type_from_args,
	normalize_generation_args,
};

use \The_SEO_Framework\{
	Data,
	Helper\Query,
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
 * Holds utility for the URI factory.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->uri()->utils() instead.
 */
class Utils {

	/**
	 * Detects site's URL scheme from site options.
	 * Falls back to is_ssl() when the hom misconfigured via wp-config.php
	 *
	 * NOTE: Some (insecure, e.g. SP) implementations for the `WP_HOME` constant, where
	 * the scheme is interpreted from the request, may cause this to be unreliable.
	 * We're going to ignore those edge-cases; they're doing it wrong.
	 *
	 * However, should we output a notification? Or let them suffer until they use Monitor to find the issue for them?
	 * Yea, Monitor's great for that. Gibe moni plos.
	 *
	 * @since 5.0.0
	 *
	 * @return string The detected URl scheme, lowercase.
	 */
	public static function detect_site_url_scheme() {
		return strtolower( static::get_parsed_front_page_url()['scheme'] ?? (
			Query::is_ssl() ? 'https' : 'http'
		) );
	}

	/**
	 * Fetches home URL host. Like "wordpress.org".
	 * If this fails, you're going to have a bad time.
	 *
	 * @since 2.7.0
	 * @since 2.9.2 1. Now considers port too.
	 *              2. Now uses get_home_url(), rather than get_option('home').
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `get_home_host`.
	 *              2. Removed memoization.
	 *
	 * @return string The home URL host.
	 */
	public static function get_site_host() {

		$parsed_url = static::get_parsed_front_page_url();

		$host = $parsed_url['host'] ?? '';

		if ( $host && isset( $parsed_url['port'] ) )
			$host .= ":{$parsed_url['port']}";

		return $host;
	}

	/**
	 * Fetches the parsed home URL.
	 * Memoizes the return value.
	 *
	 * @since 5.0.0
	 *
	 * @return string The home URL host.
	 */
	public static function get_parsed_front_page_url() {
		return umemo( __METHOD__ )
			?? umemo( __METHOD__, parse_url( Data\Blog::get_front_page_url() ) );
	}

	/**
	 * Slashes the root (home) URL.
	 *
	 * @since 5.0.0
	 * @todo shouldn't this have been "contextual_trailingslashit"?
	 *
	 * @param string $url The root URL.
	 * @return string The root URL plausibly with added slashes.
	 */
	public static function slash_front_page_url( $url ) {

		$parsed = parse_url( $url );

		// Don't slash the home URL if it's been modified by a (translation) plugin.
		if ( empty( $parsed['query'] ) ) {
			if ( isset( $parsed['path'] ) && '/' !== $parsed['path'] ) {
				// Paginated URL or subdirectory.
				$url = \user_trailingslashit( $url, 'home' );
			} else {
				$url = \trailingslashit( $url );
			}
		}

		return $url;
	}

	/**
	 * Returns preferred $url scheme.
	 * Which can automatically be detected when not set, based on the site URL setting.
	 * Memoizes the return value.
	 *
	 * @since 5.0.0
	 *
	 * @return string The preferred URl scheme.
	 */
	public static function get_preferred_url_scheme() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo() ) return $memo;

		// May be 'https', 'http', or 'automatic'.
		switch ( Data\Plugin::get_option( 'canonical_scheme' ) ) {
			case 'https':
				$scheme = 'https';
				break;
			case 'http':
				$scheme = 'http';
				break;
			case 'automatic':
			default:
				$scheme = static::detect_site_url_scheme();
		}

		/**
		 * @since 2.8.0
		 * @param string $scheme The current URL scheme.
		 */
		return memo( (string) \apply_filters( 'the_seo_framework_preferred_url_scheme', $scheme ) );
	}

	/**
	 * Sets URL to preferred URL scheme.
	 * Does not sanitize output.
	 *
	 * @since 2.8.0
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param string $url The URL to set scheme for.
	 * @return string The URL with the preferred scheme.
	 */
	public static function set_preferred_url_scheme( $url ) {
		return static::set_url_scheme( $url, static::get_preferred_url_scheme() );
	}

	/**
	 * Sets URL scheme for input URL.
	 * WordPress core function, without filter.
	 *
	 * @since 2.4.2
	 * @since 3.0.0 $use_filter now defaults to false.
	 * @since 3.1.0 The third parameter ($use_filter) is now $deprecated.
	 * @since 4.0.0 Removed the deprecated parameter.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Removed support for $scheme type 'admin', 'login', 'login_post', and 'rpc'.
	 *
	 * @param string $url    Absolute url that includes a scheme.
	 * @param string $scheme Optional. Scheme to give $url. Currently 'http', 'https', or 'relative'.
	 * @return string url with chosen scheme.
	 */
	public static function set_url_scheme( $url, $scheme = null ) {

		$url = static::make_fully_qualified_url( $url );

		switch ( $scheme ) {
			case 'https':
			case 'http':
			case 'relative':
				break;
			default:
				$scheme = Query::is_ssl() ? 'https' : 'http';
		}

		if ( 'relative' === $scheme ) {
			$url = ltrim( preg_replace( '/^\w+:\/\/[^\/]*/', '', $url ) );

			if ( '/' === ( $url[0] ?? '' ) )
				$url = '/' . ltrim( $url, "/ \t\n\r\0\x0B" );
		} else {
			$url = preg_replace( '#^\w+://#', $scheme . '://', $url );
		}

		return $url;
	}

	/**
	 * Makes URLs absolute if not already, or tries to match the preferred domain
	 * scheme otherwise.
	 * Does nothing if the URL is absolute and doesn't match the current domain.
	 *
	 * @since 5.0.0
	 *
	 * @param string $url An URL or path to rectify.
	 * @return string An absolute URL with the input domain's scheme.
	 */
	public static function make_absolute_current_scheme_url( $url ) {

		if ( static::url_matches_blog_domain( $url ) )
			return static::set_preferred_url_scheme( $url );

		// This also sets preferred URL scheme if path.
		return static::convert_path_to_url( $url );
	}

	/**
	 * Makes a fully qualified URL by adding the scheme prefix.
	 * Always adds http prefix, not https.
	 *
	 * NOTE: Expects the URL to have either a scheme, or a relative scheme set.
	 *       Domain-relative URLs will not be parsed correctly.
	 *       '/path/to/folder/` will become `http:///path/to/folder/`
	 *
	 * @since 2.6.5
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 * @see `static::set_url_scheme()` to set the correct scheme.
	 * @see `static::convert_path_to_url()` to create URLs from paths.
	 *
	 * @param string $url The current maybe not fully qualified URL. Required.
	 * @return string $url
	 */
	public static function make_fully_qualified_url( $url ) {

		if ( '//' === substr( $url, 0, 2 ) )
			return "http:$url";

		if ( 'http' !== substr( $url, 0, 4 ) )
			return "http://{$url}";

		return $url;
	}

	/**
	 * Tests if input URL matches current domain.
	 *
	 * @since 5.0.0
	 *
	 * @param string $url The URL to test. Required.
	 * @return bool true on match, false otherwise.
	 */
	public static function url_matches_blog_domain( $url ) {

		if ( ! $url )
			return false;

		$home_domain =
			   umemo( __METHOD__ )
			?? umemo(
				__METHOD__,
				static::set_url_scheme( \sanitize_url(
					Data\Blog::get_front_page_url(),
					[ 'https', 'http' ]
				) )
			);

		// Test for likely match early, before transforming.
		if ( 0 === stripos( $url, $home_domain ) )
			return true;

		$url = static::set_url_scheme( \sanitize_url(
			$url,
			[ 'https', 'http' ]
		) );

		// If they start with the same, we can assume it's the same domain.
		return 0 === stripos( $url, $home_domain );
	}

	/**
	 * Converts absolute URLs to relative URLs, if they weren't already.
	 * Returns the path, query, and fragment.
	 *
	 * @since 2.6.5
	 * @since 2.8.0 Method is now public.
	 * @since 4.0.0 No longer strips the prepended / path.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `s_relative_url()`
	 *
	 * @param string $url An absolute or relative URL.
	 * @return string $url The URL's path.
	 */
	public static function get_relative_part_from_url( $url ) {
		return preg_replace( '/^(?:https?:)?\/\/[^\/]+(\/.*)/i', '$1', $url );
	}

	/**
	 * Makes a fully qualified URL from any input.
	 *
	 * @since 5.0.0
	 *
	 * @param string $path Either the URL or path. Will always be transformed to the current domain.
	 * @param string $url  The URL to add the path to. Defaults to the current home URL.
	 * @return string $url
	 */
	public static function convert_path_to_url( $path, $url = '' ) {
		return \WP_Http::make_absolute_url(
			$path,
			\trailingslashit(
				$url ?: static::set_preferred_url_scheme( static::get_site_host() )
			)
		);
	}

	/**
	 * Adds pagination to input URL.
	 *
	 * @since 4.2.3
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param string $url      The fully qualified URL.
	 * @param int    $page     The page number. Should be bigger than 1 to paginate.
	 * @param bool   $use_base Whether to use pagination base.
	 *                         If null, it will autodetermine.
	 *                         Should be true on archives and the homepage (blog and static!).
	 *                         False on singular post types.
	 * @return string The fully qualified URL with pagination.
	 */
	public static function add_pagination_to_url( $url, $page = null, $use_base = null ) {

		$page ??= max( Query::paged(), Query::page() );

		if ( $page < 2 )
			return $url;

		$use_base
			??= Query::is_real_front_page()
			 || Query::is_archive()
			 || Query::is_singular_archive()
			 || Query::is_search();

		if ( Query\Utils::using_pretty_permalinks() ) {
			$_query = parse_url( $url, \PHP_URL_QUERY );

			// Remove queries, add them back later.
			if ( $_query )
				$url = strtok( $url, '?' );

			if ( $use_base ) {
				$url = \user_trailingslashit(
					\trailingslashit( $url ) . "{$GLOBALS['wp_rewrite']->pagination_base}/$page",
					'paged',
				);
			} else {
				$url = \user_trailingslashit( \trailingslashit( $url ) . $page, 'single_paged' );
			}

			if ( $_query )
				$url = static::append_query_to_url( $url, $_query );
		} else {
			if ( $use_base ) {
				$url = \add_query_arg( 'paged', $page, $url );
			} else {
				$url = \add_query_arg( 'page', $page, $url );
			}
		}

		return $url;
	}

	/**
	 * Removes pagination from input URL.
	 * The URL must match this query if no second parameter is provided.
	 *
	 * @since 3.0.0
	 * @since 3.2.4 1. Now correctly removes the pagination base on singular post types.
	 *              2. The second parameter now accepts null or a value.
	 *              3. The third parameter is now changed to $use_base, from the archive pagination number.
	 *              4. Now supports pretty permalinks with query parameters.
	 *              5. Is now public.
	 * @since 4.1.2 Now correctly reappends query when pagination isn't removed.
	 * @since 4.2.0 Now properly removes pagination from search links.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param string    $url  The fully qualified URL to remove pagination from.
	 * @param int|null  $page The page number to remove. If null, it will get number from query.
	 * @param bool|null $use_base Whether to remove the pagination base.
	 *                            If null, it will autodetermine.
	 *                            Should be true on archives and the homepage (blog and static!).
	 *                            False on singular post types.
	 * @return string $url The fully qualified URL without pagination.
	 */
	public static function remove_pagination_from_url( $url, $page = null, $use_base = null ) {

		if ( Query\Utils::using_pretty_permalinks() ) {

			$page ??= max( Query::paged(), Query::page() );

			if ( $page > 1 ) {
				$user_slash = $GLOBALS['wp_rewrite']->use_trailing_slashes ? '/' : '';

				$use_base
					??= Query::is_real_front_page()
					 || Query::is_archive()
					 || Query::is_singular_archive()
					 || Query::is_search();

				if ( $use_base ) {
					$find = "/{$GLOBALS['wp_rewrite']->pagination_base}/{$page}{$user_slash}";
				} else {
					$find = "/{$page}{$user_slash}";
				}

				$_query = parse_url( $url, \PHP_URL_QUERY );
				// Remove queries, add them back later.
				if ( $_query )
					$url = strtok( $url, '?' );

				$pos = strrpos( $url, $find );
				// Defensive programming, only remove if $find matches the stack length, without query arguments.
				if ( $pos && $pos + \strlen( $find ) === \strlen( $url ) ) {
					$url = substr( $url, 0, $pos );
					$url = \user_trailingslashit( $url );

					// Add back the query.
					if ( $_query )
						$url = static::append_query_to_url( $url, $_query );
				}
			}
		} else {
			$url = \remove_query_arg( [ 'page', 'paged', 'cpage' ], $url );
		}

		return $url;
	}

	/**
	 * Appends given query to given URL.
	 *
	 * This is a "dumb" replacement of WordPress's add_query_arg(), but much faster
	 * and with more straightforward query and fragment handlers.
	 *
	 * @since 5.0.0
	 *
	 * @param string $url   A fully qualified URL.
	 * @param string $query A fully qualified query taken from parse_url( $url, \PHP_URL_QUERY );
	 * @return string A fully qualified URL with appended $query.
	 */
	public static function append_query_to_url( $url, $query ) {

		if ( str_contains( $url, '#' ) ) {
			$fragment = strstr( $url, '#' );
			$url      = str_replace( $fragment, '', $url );
		} else {
			$fragment = '';
		}

		if ( str_contains( $url, '?' ) )
			return "$url&$query{$fragment}";

		return "$url?$query{$fragment}";
	}


	/**
	 * Returns the permalink structure for the given query.
	 * Does not support pagination or endpoint masks.
	 *
	 * This method is meant for canonical URL prediction in JavaScript.
	 *
	 * Ref, WP Core:
	 * - `get_permalink()`, leads to: `get_page_link()`, `get_attachment_link()`, `get_post_permalink()`
	 * - `get_term_link()`
	 *
	 * @since 5.1.0
	 *
	 * @param array $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 * @return string The URL permastructure for the given query.
	 */
	public static function get_url_permastruct( $args ) {
		global $wp_rewrite;

		normalize_generation_args( $args );

		switch ( get_query_type_from_args( $args ) ) {
			case 'single':
				if ( Query::is_static_front_page( $args['id'] ) ) {
					$permastruct = $wp_rewrite->front;
				} else {
					$post_type = Query::get_post_type_real_id( $args['id'] );

					switch ( $post_type ) {
						case 'page':
							// Both translate to the post's name; this translation eases later processing.
							$permastruct = str_replace( '%pagename%', '%postname%', $wp_rewrite->get_page_permastruct() );
							break;
						case 'attachment':
							if ( Query\Utils::using_pretty_permalinks() ) {
								$attachment  = \get_post( $args['id'] );
								$parent_post = $attachment->post_parent;

								if ( $parent_post ) {
									$parentslug = static::get_relative_part_from_url( \get_permalink( $parent_post ) );

									// This was probably a workaround for paginated parent links. See `get_attachment_link()`.
									// We should also account for this on the Canonical URL Notation Tracker, but this is an extreme oddity.
									// I doubt anyone is managing attachment slugs, especially switching from numericals to non-numericals.
									if (
										   is_numeric( $attachment->post_name )
										|| str_contains( \get_option( 'permalink_structure' ), '%category%' )
									) {
										$namestruct = 'attachment/%postname%';
									} else {
										$namestruct = '%postname%';
									}

									// Odd case is odd. See `get_attachment_link()`.
									// Introduced at https://core.trac.wordpress.org/ticket/1776 -- no explanation provided.
									if ( str_contains( $parentslug, '?' ) ) {
										$permastruct = $namestruct;
									} else {
										$permastruct = \trailingslashit( $parentslug ) . $namestruct;
									}
								} else {
									$permastruct = '%postname%';
								}
								break;
							} // else: ?attachment_id=%post_id%, but this is handled via default.
							break;
						case 'post':
							$permastruct = $wp_rewrite->permalink_structure;
							break;
						// actually: `\in_array( $post_type, \get_post_types( [ '_builtin' => false ] ), true )`, but we covered all others above.
						default:
							$permastruct = \is_post_type_hierarchical( $post_type )
								? $wp_rewrite->get_page_permastruct()
								: $wp_rewrite->get_extra_permastruct( $post_type );

							// Both translate to the post's name; this translation eases later processing.
							$permastruct = str_replace( "%{$post_type}%", '%postname%', $permastruct );
					}
				}
				break;
			case 'homeblog':
				$permastruct = $wp_rewrite->front;
				break;
			case 'term':
				$permastruct = $wp_rewrite->get_extra_permastruct( $args['tax'] );
				break;
			case 'pta':
				$permastruct = $wp_rewrite->get_extra_permastruct( $args['pta'] );
				break;
			case 'user':
				$permastruct = $wp_rewrite->get_author_permastruct();
		}

		return '/' . ltrim( \user_trailingslashit( $permastruct ?? '' ), '/' );
	}
}
