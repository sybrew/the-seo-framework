<?php
/**
 * @package The_SEO_Framework\Classes\Sitemap\Registry
 * @subpackage The_SEO_Framework\Sitemap
 */

namespace The_SEO_Framework\Sitemap;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\{
	memo,
	has_run,
};

use \The_SEO_Framework\{
	Data,
	Helper,
	Helper\Query,
	Helper\Template,
	Meta,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Prepares sitemap output.
 *
 * @since 4.0.0
 * @since 5.0.0 1. Renamed from `Sitemap`.
 *              2. Moved to `\The_SEO_Framework\Sitemap`.
 * @access protected
 *         Use tsf()->sitemap()->registry() instead.
 */
class Registry {

	/**
	 * Initializes sitemap output.
	 *
	 * @hook parse_request 15
	 * @since 4.0.0
	 * @since 4.0.2 Can now parse non-ASCII URLs. No longer only lowercases raw URIs.
	 * @since 5.0.0 Is now static.
	 * @access private
	 */
	public static function _init() {

		// The raw path(+query) of the requested URI.
		// TODO consider reverse proxies, as WP()->parse_request() seems to do.
		// @link https://github.com/sybrew/the-seo-framework/issues/529
		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$raw_uri = rawurldecode(
				\wp_check_invalid_utf8(
					stripslashes( $_SERVER['REQUEST_URI'] )
				)
			) ?: '/';
		} else {
			$raw_uri = '/';
		}

		// Probably home page.
		if ( '/' === $raw_uri ) return;

		// The path+query where sitemaps are served.
		$path_info = static::get_sitemap_base_path_info();

		// A regex which detects $sitemap_path at the beginning of a string.
		$path_regex = '/^' . preg_quote( rawurldecode( $path_info['path'] ), '/' ) . '/ui';

		// See if the base matches the endpoint. This is crucial for query-based endpoints.
		if ( ! preg_match( $path_regex, $raw_uri ) ) return;

		$stripped_uri = preg_replace( $path_regex, '', rtrim( $raw_uri, '/' ) );

		// Strip the base URI. If nothing's left, stop assessing.
		if ( ! $stripped_uri ) return;

		// Loop over the sitemap endpoints, and see if it matches the stripped uri.
		if ( $path_info['use_query_var'] ) {
			foreach ( static::get_sitemap_endpoint_list() as $_id => $_data ) {
				$_regex = '/^' . preg_quote( $_id, '/' ) . '/i';
				// Yes, we know. It's not really checking for standardized query-variables.
				if ( preg_match( $_regex, $stripped_uri ) ) {
					$sitemap_id = $_id;
					break;
				}
			}
		} else {
			foreach ( static::get_sitemap_endpoint_list() as $_id => $_data ) {
				if ( preg_match( $_data['regex'], $stripped_uri ) ) {
					$sitemap_id = $_id;
					break;
				}
			}
		}

		// No matched sitemap ID is found.
		if ( empty( $sitemap_id ) ) return;

		// Register we're on a sitemap.
		Query::is_sitemap( true );
		\add_action( 'pre_get_posts', [ static::class, '_override_query_parameters' ] );

		/**
		 * Set at least 2000 variables free.
		 * Freeing 0.15MB on a clean WordPress installation on PHP 7.
		 */
		static::clean_up_globals();

		/**
		 * @since 4.0.0
		 * @param string $sitemap_id The sitemap ID. See `static::get_sitemap_endpoint_list()`.
		 */
		\do_action( 'the_seo_framework_sitemap_header', $sitemap_id );

		\call_user_func( static::get_sitemap_endpoint_list()[ $sitemap_id ]['callback'], $sitemap_id );
	}

	/**
	 * Sets `is_home` to false for the sitemap.
	 * Also sets proposed `is_sitemap` to true, effectively achieving the same.
	 *
	 * @hook pre_get_posts 10
	 * @link https://core.trac.wordpress.org/ticket/51542
	 * @link https://core.trac.wordpress.org/ticket/51117
	 * @since 5.0.0
	 * @access private
	 *
	 * @param \WP_Query $wp_query The WordPress WC_Query instance.
	 */
	public static function _override_query_parameters( $wp_query ) {
		$wp_query->is_home = false;
		// $wp_query allows dynamic properties. This one is proposed in https://core.trac.wordpress.org/ticket/51117#comment:7
		$wp_query->is_sitemap = true;
	}

	/**
	 * Returns the expected sitemap endpoint for the given ID.
	 *
	 * @since 4.0.0
	 * @since 4.1.2 No longer passes the path to the home_url() function because
	 *              Polylang is being astonishingly asinine.
	 * @since 4.1.4 Now assimilates the output using the base path, so that filter
	 *              `the_seo_framework_sitemap_base_path` also works. Glues the
	 *              pieces together using the `get_site_host()` value.
	 * @since 5.0.0 Is now static.
	 * @global \WP_Rewrite $wp_rewrite
	 *
	 * @param string $id The base ID. Default 'base'.
	 * @return string|bool False if ID isn't registered; the URL otherwise.
	 */
	public static function get_expected_sitemap_endpoint_url( $id = 'base' ) {

		$list = static::get_sitemap_endpoint_list();

		if ( ! isset( $list[ $id ] ) ) return false;

		$host      = Meta\URI\Utils::set_preferred_url_scheme( Meta\URI\Utils::get_site_host() );
		$path_info = static::get_sitemap_base_path_info();

		return \sanitize_url(
			$path_info['use_query_var']
				? "$host{$path_info['path']}$id"
				: "$host{$path_info['path']}{$list[ $id ]['endpoint']}",
		);
	}

	/**
	 * Returns a list of known sitemap endpoints.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Is now static.
	 *
	 * @return array[] The sitemap endpoints with their callbacks.
	 */
	public static function get_sitemap_endpoint_list() {
		return memo() ?? memo(
			/**
			 * @since 4.0.0
			 * @since 4.0.2 Made the endpoints' regex case-insensitive.
			 * @link Example: https://github.com/sybrew/tsf-term-sitemap
			 * @param array[] $list {
			 *     A list of sitemap endpoints keyed by ID.
			 *
			 *     @type string|false $lock_id  Optional. The cache key to use for locking. Defaults to index 'id'.
			 *                                  Set to false to disable locking.
			 *     @type string|false $cache_id Optional. The cache key to use for storing. Defaults to index 'id'.
			 *                                  Set to false to disable caching.
			 *     @type string       $endpoint The expected "pretty" endpoint, meant for administrative display.
			 *     @type string       $epregex  The endpoint regex, following the home path regex.
			 *                                  N.B. Be wary of case sensitivity. Append the i-flag.
			 *                                  N.B. Trailing slashes will cause the match to fail.
			 *                                  N.B. Use ASCII-endpoints only. Don't play with UTF-8 or translation strings.
			 *     @type callable     $callback The callback for the sitemap output.
			 *                                  Tip: You can pass arbitrary indexes. Prefix them with an underscore to ensure forward compatibility.
			 *                                  Tip: In the callback, use
			 *                                       `\The_SEO_Framework\Sitemap\Registry::get_sitemap_endpoint_list()[$sitemap_id]`
			 *                                       It returns the arguments you've passed in this filter; including your arbitrary indexes.
			 *     @type bool         $robots   Whether the endpoint should be mentioned in the robots.txt file.
			 * }
			 */
			(array) \apply_filters(
				'the_seo_framework_sitemap_endpoint_list',
				[
					'base'           => [
						'lock_id'  => 'base', // Example, real usage is with "index" using base.
						'cache_id' => 'base', // Example, real usage is with "index" using base.
						'endpoint' => 'sitemap.xml',
						'regex'    => '/^sitemap\.xml/i',
						'callback' => [ static::class, 'output_base_sitemap' ],
						'robots'   => true,
					],
					'index'          => [
						'lock_id'  => 'base',
						'cache_id' => 'base',
						'endpoint' => 'sitemap_index.xml',
						'regex'    => '/^sitemap_index\.xml/i',
						'callback' => [ static::class, 'output_base_sitemap' ],
						'robots'   => false,
					],
					'xsl-stylesheet' => [
						'lock_id'  => false,
						'cache_id' => false,
						'endpoint' => 'sitemap.xsl',
						'regex'    => '/^sitemap\.xsl/i',
						'callback' => [ static::class, 'output_stylesheet' ],
						'robots'   => false,
					],
				],
			),
		);
	}

	/**
	 * Deletes transients for sitemaps. Also engages pings for or pings search engines.
	 * Can only run once per request.
	 *
	 * @hook "update_option_ . THE_SEO_FRAMEWORK_SITE_OPTIONS" 10
	 * @since 5.0.0
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function refresh_sitemaps() {

		if ( has_run( __METHOD__ ) ) return false;

		Cache::clear_sitemap_caches();

		/**
		 * @since 4.1.1
		 * @since 5.0.5 Removed indexes `ping_use_cron` and `ping_use_cron_prerender`.
		 * @param array $deprecated Deprecated; do not use the first parameter.
		 */
		\do_action( 'the_seo_framework_sitemap_transient_cleared', [] );

		Cron::schedule_single_event();

		return true;
	}

	/**
	 * Refreshes sitemaps on post change.
	 *
	 * @hook publish_post 10
	 * @hook publish_page 10
	 * @hook deleted_post 10
	 * @hook deleted_page 10
	 * @hook post_updated 10
	 * @hook page_updated 10
	 * @since 5.0.0
	 * @access private
	 *
	 * @param int $post_id The Post ID that has been updated.
	 * @return bool True on success, false on failure.
	 */
	public static function _refresh_sitemap_on_post_change( $post_id ) {

		// Don't refresh sitemap on revision.
		if ( ! $post_id || \wp_is_post_revision( $post_id ) ) return false;

		return static::refresh_sitemaps();
	}

	/**
	 * Checks whether the permalink structure is updated.
	 *
	 * @hook load-options-permalink.php 20
	 * @since 5.0.0
	 * @access private
	 *
	 * @return bool Whether if sitemap transient is deleted.
	 */
	public static function _refresh_sitemap_transient_permalink_updated() {

		if (
			   ( isset( $_POST['permalink_structure'] ) || isset( $_POST['category_base'] ) )
			&& \check_admin_referer( 'update-permalink' )
		) {
			return static::refresh_sitemaps();
		}

		return false;
	}

	/**
	 * Outputs sitemap.xml 'file' and header.
	 *
	 * @since 2.2.9
	 * @since 3.1.0 1. Now outputs 200-response code.
	 *              2. Now outputs robots tag, preventing indexing.
	 *              3. Now overrides other header tags.
	 * @since 4.0.0 1. Moved to \The_SEO_Framework\Bridges\Sitemap
	 *              2. Renamed from `output_sitemap()`
	 * @since 4.1.2 Is now static.
	 *
	 * @param string $sitemap_id The sitemap ID.
	 */
	public static function output_base_sitemap( $sitemap_id = 'base' ) {

		if ( Lock::is_sitemap_locked( $sitemap_id ) ) {
			Lock::output_locked_header( $sitemap_id );
			exit;
		}

		Helper\Headers::clean_response_header();

		if ( ! headers_sent() ) {
			\status_header( 200 );
			header( 'Content-type: text/xml; charset=utf-8', true );
		}

		// Fetch sitemap content and add trailing line. Already escaped internally.
		Template::output_view( 'sitemap/xml-sitemap', $sitemap_id );
		echo "\n";

		// We're done now.
		exit;
	}

	/**
	 * Sitemap XSL stylesheet output.
	 *
	 * @since 2.8.0
	 * @since 3.1.0 1. Now outputs 200-response code.
	 *              2. Now outputs robots tag, preventing indexing.
	 *              3. Now overrides other header tags.
	 * @since 4.0.0 1. Moved to \The_SEO_Framework\Bridges\Sitemap
	 *              2. Renamed from `output_sitemap_xsl_stylesheet()`
	 * @since 4.1.2 Is now static.
	 */
	public static function output_stylesheet() {

		Helper\Headers::clean_response_header();

		if ( ! headers_sent() ) {
			\status_header( 200 );
			header( 'Content-type: text/xsl; charset=utf-8', true );
			header( 'Cache-Control: max-age=1800', true );
		}

		Optimized\XSL::register_hooks();

		Template::output_view( 'sitemap/xsl-stylesheet' );
		exit;
	}

	/**
	 * Outputs the sitemap header.
	 *
	 * @since 4.0.0
	 * @since 4.1.3 Added a trailing newline to the stylesheet-tag for readability.
	 * @since 5.0.0 Is now static.
	 */
	public static function output_sitemap_header() {

		echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";

		if ( Data\Plugin::get_option( 'sitemap_styles' ) ) {
			printf(
				'<?xml-stylesheet type="text/xsl" href="%s"?>' . "\n",
				// phpcs:ignore, WordPress.Security.EscapeOutput
				static::get_expected_sitemap_endpoint_url( 'xsl-stylesheet' )
			);
		}
	}

	/**
	 * Returns the opening tag for the sitemap urlset.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Is now static.
	 */
	public static function output_sitemap_urlset_open_tag() {

		$schemas = [
			'xmlns'              => 'http://www.sitemaps.org/schemas/sitemap/0.9',
			'xmlns:xhtml'        => 'http://www.w3.org/1999/xhtml',
			'xmlns:xsi'          => 'http://www.w3.org/2001/XMLSchema-instance',
			'xsi:schemaLocation' => [
				'http://www.sitemaps.org/schemas/sitemap/0.9',
				'http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd',
			],
		];

		/**
		 * @since 2.8.0
		 * @param array $schemas The schema list. URLs and indexes are expected to be escaped.
		 */
		$schemas = (array) \apply_filters( 'the_seo_framework_sitemap_schemas', $schemas );

		array_walk(
			$schemas,
			function ( &$schema, $key ) {
				$schema = \sprintf( '%s="%s"', $key, implode( ' ', (array) $schema ) );
			}
		);

		// phpcs:ignore, WordPress.Security.EscapeOutput -- Output is expected to be escaped.
		printf( "<urlset %s>\n", implode( ' ', $schemas ) );
	}

	/**
	 * Outputs the closing tag for the sitemap urlset.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Is now static.
	 */
	public static function output_sitemap_urlset_close_tag() {
		echo '</urlset>';
	}

	/**
	 * Returns the sitemap base path.
	 * Useful when the path is non-standard, like notoriously in Polylang.
	 *
	 * @since 4.1.2
	 * @since 5.0.0 Is now static.
	 *
	 * @return string The path.
	 */
	private static function get_sitemap_base_path() {
		/**
		 * @since 4.1.2
		 * @param string $path The home path.
		 */
		return \apply_filters(
			'the_seo_framework_sitemap_base_path',
			rtrim(
				Meta\URI\Utils::get_parsed_front_page_url()['path'] ?? '',
				'/',
			),
		);
	}

	/**
	 * Returns the sitemap path prefix.
	 * Useful when the prefix path is non-standard, like notoriously in Polylang.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Is now static.
	 *
	 * @return string The path prefix.
	 */
	private static function get_sitemap_path_prefix() {
		/**
		 * Ignore RFC2616 slashlessness by adding a slash;
		 * this makes life easier when trailing and testing the URL, as well.
		 *
		 * @since 4.0.0
		 * @param string $prefix The path prefix. Ideally appended with a slash.
		 *                       Recommended return value: "$prefix$custompath/"
		 */
		return \apply_filters( 'the_seo_framework_sitemap_path_prefix', '/' );
	}

	/**
	 * Returns the base path information for the sitemap.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Is now static.
	 * @global \WP_Rewrite $wp_rewrite
	 *
	 * @return array {
	 *     The sitemap base path information.
	 *
	 *     @type string $path          The sitemap base path, like subdirectories or translations.
	 *     @type bool   $use_query_var Whether to use the query var.
	 * }
	 */
	private static function get_sitemap_base_path_info() {
		global $wp_rewrite;

		$base_path = static::get_sitemap_base_path();
		$prefix    = static::get_sitemap_path_prefix();

		$use_query_var = false;

		if ( $wp_rewrite->using_index_permalinks() ) {
			$path = "$base_path/index.php$prefix";
		} elseif ( $wp_rewrite->using_permalinks() ) {
			$path = "$base_path$prefix";
		} else {
			// Yes, we know. This is not really checking for standardized query-variables.
			// It's straightforward and doesn't mess with the rest of the site, however.
			$path = "$base_path$prefix?tsf-sitemap=";

			$use_query_var = true;
		}

		return compact( 'path', 'use_query_var' );
	}

	/**
	 * Returns freed memory for debugging.
	 *
	 * This method is to be used after outputting the sitemap.
	 *
	 * @since 4.1.1
	 * @since 5.0.0 Is now static.
	 *
	 * @return int bytes freed.
	 */
	public static function get_freed_memory() {
		return static::clean_up_globals( true );
	}

	/**
	 * Destroys unused $GLOBALS.
	 *
	 * This method is to be used prior to outputting the sitemap.
	 *
	 * @since 2.6.0
	 * @since 2.8.0 Renamed from clean_up_globals().
	 * @since 4.0.0 1. Moved to \The_SEO_Framework\Bridges\Sitemap
	 *              2. Renamed from clean_up_globals_for_sitemap()
	 * @since 4.2.0 Now always returns the freed memory.
	 * @since 5.0.0 Is now static.
	 *
	 * @param bool $get_freed_memory Whether to return the freed memory in bytes.
	 * @return int $freed_memory in bytes
	 */
	private static function clean_up_globals( $get_freed_memory = false ) {

		if ( $get_freed_memory ) return memo() ?? 0;

		$memory = memory_get_usage();

		$remove = [
			'wp_filter' => [
				'wp_head',
				'admin_head',
				'the_content',
				'the_content_feed',
				'the_excerpt_rss',
				'wp_footer',
				'admin_footer',
				'widgets_init',
			],
			'wp_registered_widgets',
			'wp_registered_sidebars',
			'wp_registered_widget_updates',
			'wp_registered_widget_controls',
			'_wp_deprecated_widgets_callbacks',
			'posts',
		];

		foreach ( $remove as $key => $value ) {
			if ( \is_array( $value ) ) {
				foreach ( $value as $v )
					unset( $GLOBALS[ $key ][ $v ] );
			} else {
				unset( $GLOBALS[ $value ] );
			}
		}

		// This one requires to be an array for wp_texturize(). There's an API, let's use it:
		\remove_all_shortcodes();

		return memo( $memory - memory_get_usage() );
	}
}
