<?php
/**
 * @package The_SEO_Framework\Classes\Bridges\Sitemap
 * @subpackage The_SEO_Framework\Sitemap
 */

namespace The_SEO_Framework\Bridges;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2020 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * Sets up class loader as file is loaded.
 * This is done asynchronously, because static calls are handled prior and after.
 *
 * @see EOF. Because of the autoloader and (future) trait calling, we can't do it before the class is read.
 * @link https://bugs.php.net/bug.php?id=75771
 */
$_load_sitemap_class = function() {
	// phpcs:ignore, TSF.Performance.Opcodes.ShouldHaveNamespaceEscape
	new Sitemap();
};

/**
 * Prepares sitemap output.
 *
 * @since 4.0.0
 * @access protected
 * @final Can't be extended.
 */
final class Sitemap {

	/**
	 * @since 4.0.0
	 * @var \The_SEO_Framework\Bridges\Sitemap
	 */
	private static $instance;

	/**
	 * @var null|\The_SEO_Framework\Load
	 */
	private static $tsf = null;

	/**
	 * Returns this instance.
	 *
	 * @since 4.0.0
	 *
	 * @return \The_SEO_Framework\Bridges\Sitemap $instance
	 */
	public static function get_instance() {
		return static::$instance;
	}

	/**
	 * Prepares the class and loads constructor.
	 *
	 * Use this if the actions need to be registered early, but nothing else of
	 * this class is needed yet.
	 *
	 * @since 4.0.0
	 */
	public static function prepare() {}

	/**
	 * The constructor. Can't be instantiated externally from this file.
	 * Kills PHP on subsequent duplicated request. Enforces singleton.
	 *
	 * This probably autoloads at action "template_redirect", priority "1".
	 *
	 * @since 4.0.0
	 * @access private
	 * @internal
	 */
	public function __construct() {

		static $count = 0;
		0 === $count++ or \wp_die( 'Don\'t instance <code>' . __CLASS__ . '</code>.' );

		static::$tsf      = \the_seo_framework();
		static::$instance = &$this;
	}

	/**
	 * Initializes scripts based on admin query.
	 *
	 * @since 4.0.0
	 * @since 4.0.2 Can now parse non-ASCII URLs. No longer only lowercases raw URIs.
	 * @access private
	 * @internal This always runs; build your own loader from the public methods, instead.
	 */
	public function _init() {

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

		$sitemap_id = $this->get_sitemap_id_from_uri( $raw_uri );

		if ( ! $sitemap_id ) return;

		// Don't let WordPress think this is 404.
		$GLOBALS['wp_query']->is_404 = false;

		static::$tsf->is_sitemap( true );

		/**
		 * Set at least 2000 variables free.
		 * Freeing 0.15MB on a clean WordPress installation on PHP 7.
		 */
		$this->clean_up_globals();

		/**
		 * @since 4.0.0
		 * @param string $sitemap_id The sitemap ID. See `static::get_sitemap_endpoint_list()`.
		 */
		\do_action( 'the_seo_framework_sitemap_header', $sitemap_id );

		\call_user_func( $this->get_sitemap_endpoint_list()[ $sitemap_id ]['callback'], $sitemap_id );
	}

	/**
	 * Returns the expected sitemap endpoint for the given ID.
	 *
	 * @since 4.0.0
	 * @since 4.1.2 No longer passes the path to the home_url() function because
	 *              Polylang is being astonishingly asinine.
	 * @global \WP_Rewrite $wp_rewrite
	 *
	 * @param string $id The base ID. Default 'base'.
	 * @return string|bool False if ID isn't registered; the URL otherwise.
	 */
	public function get_expected_sitemap_endpoint_url( $id = 'base' ) {

		$list = $this->get_sitemap_endpoint_list();

		if ( ! isset( $list[ $id ] ) ) return false;

		global $wp_rewrite;

		$scheme = static::$tsf->get_preferred_scheme();
		$prefix = $this->get_sitemap_path_prefix();

		$home_url = \home_url( '/', $scheme );

		// Other plugins may append a query (such as translations).
		$home_query = parse_url( $home_url, PHP_URL_QUERY );
		// Remove query from URL when found. Add back later.
		if ( $home_query )
			$home_url = static::$tsf->s_url( $home_url );

		if ( $wp_rewrite->using_index_permalinks() ) {
			$path = "/index.php$prefix{$list[ $id ]['endpoint']}";
		} elseif ( $wp_rewrite->using_permalinks() ) {
			$path = "$prefix{$list[ $id ]['endpoint']}";
		} else {
			$path = "$prefix?tsf-sitemap=$id";
		}

		$url = \trailingslashit( $home_url ) . ltrim( $path, '/' );

		if ( $home_query )
			$url = static::$tsf->append_php_query( $url, $home_query );

		return \esc_url_raw( $url );
	}

	/**
	 * Returns a list of known sitemap endpoints.
	 *
	 * @since 4.0.0
	 * @static array $list
	 *
	 * @return array The sitemap endpoints with their callbacks.
	 */
	public function get_sitemap_endpoint_list() {
		static $list;
		/**
		 * @since 4.0.0
		 * @since 4.0.2 Made the endpoints' regex case-insensitive.
		 * @link Example: https://github.com/sybrew/tsf-term-sitemap
		 * @param array $list The endpoints: {
		 *   'id' => array: {
		 *      'cache_id' => string   Optional. The cache key to use for locking. Defaults to index 'id'.
		 *      'endpoint' => string   The expected "pretty" endpoint, meant for administrative display.
		 *      'epregex'  => string   The endpoint regex, following the home path regex.
		 *                             N.B. Be wary of case sensitivity. Append the i-flag.
		 *                             N.B. Trailing slashes will cause the match to fail.
		 *                             N.B. Use ASCII-endpoints only. Don't play with UTF-8 or translation strings.
		 *      'callback' => callable The callback for the sitemap output.
		 *                             Tip: You can pass arbitrary indexes. Prefix them with an underscore to ensure forward compatibility.
		 *                             Tip: In the callback, use
		 *                                  `\The_SEO_Framework\Bridges\Sitemap::get_instance()->get_sitemap_endpoint_list()[$sitemap_id]`
		 *                                  It returns the arguments you've passed in this filter; including your arbitrary indexes.
		 *      'robots'   => bool     Whether the endpoint should be mentioned in the robots.txt file.
		 *   }
		 * }
		 */
		return $list = $list ?: \apply_filters(
			'the_seo_framework_sitemap_endpoint_list',
			[
				'base'           => [
					'lock_id'  => 'base',
					'endpoint' => 'sitemap.xml',
					'regex'    => '/^sitemap\.xml/i',
					'callback' => static::class . '::output_base_sitemap',
					'robots'   => true,
				],
				'index'          => [
					'lock_id'  => 'base',
					'endpoint' => 'sitemap_index.xml',
					'regex'    => '/^sitemap_index\.xml/i',
					'callback' => static::class . '::output_base_sitemap',
					'robots'   => false,
				],
				'xsl-stylesheet' => [
					'endpoint' => 'sitemap.xsl',
					'regex'    => '/^sitemap\.xsl/i',
					'callback' => static::class . '::output_stylesheet',
					'robots'   => false,
				],
			]
		);
	}

	/**
	 * Tells whether sitemap caching is enabled by user.
	 *
	 * @since 4.1.2
	 *
	 * @return bool
	 */
	public function sitemap_cache_enabled() {
		return (bool) static::$tsf->get_option( 'cache_sitemap' );
	}

	/**
	 * Outputs a '503: Service Unavailable' header and no-cache headers.
	 *
	 * @since 4.1.2
	 * TODO consider instead of sending me, output the previous sitemap from cache, instead? Spaghetti.
	 *
	 * @param int $timeout How many seconds the user has to wait. Optional. Leave 0 to send a generic message.
	 */
	public function output_locked_header( $timeout = 0 ) {

		static::$tsf->clean_response_header();

		\status_header( 503 );
		\nocache_headers();

		if ( $timeout ) {
			printf(
				'Sitemap is locked for %d seconds. Try again later.',
				(int) ( $timeout - time() )
			);
		} else {
			echo 'Sitemap is locked temporarily. Try again later.';
		}

		echo PHP_EOL;
		exit;
	}

	/**
	 * Returns the sitemap's lock cache ID.
	 *
	 * @since 4.1.2
	 *
	 * @param string|false $sitemap_id The sitemap ID to test. False when key is invalid.
	 */
	public function get_lock_key( $sitemap_id = 'base' ) {

		$ep_list = $this->get_sitemap_endpoint_list();

		if ( ! isset( $ep_list[ $sitemap_id ] ) ) return false;

		$lock_id = isset( $ep_list[ $sitemap_id ]['lock_id'] ) ? $ep_list[ $sitemap_id ]['lock_id'] : $sitemap_id;

		return static::$tsf->generate_cache_key( 0, '', 'sitemap_lock' ) . "_{$lock_id}";
	}

	/**
	 * Locks a sitemap for the current blog & locale and $sitemap_id, preferably
	 * at least as long as PHP is allowed to run.
	 *
	 * @since 4.1.2
	 *
	 * @param string $sitemap_id The sitemap ID.
	 * @return bool True on succes, false on failure.
	 */
	public function lock_sitemap( $sitemap_id = 'base' ) {

		$lock_key = $this->get_lock_key( $sitemap_id );
		if ( ! $lock_key ) return false;

		// This is rather at most as PHP will run. However, 3 minutes to generate a sitemap is already ludicrous.
		$timeout = (int) min( ini_get( 'max_execution_time' ), 3 * MINUTE_IN_SECONDS );

		return \set_transient(
			$lock_key,
			time() + $timeout,
			$timeout
		);
	}

	/**
	 * Unlocks a sitemap for the current blog & locale and $sitemap_id.
	 *
	 * @since 4.1.2
	 *
	 * @param string $sitemap_id The sitemap ID.
	 * @return bool True on succes, false on failure.
	 */
	public function unlock_sitemap( $sitemap_id = 'base' ) {

		$lock_key = $this->get_lock_key( $sitemap_id );
		if ( ! $lock_key ) return false;

		return \delete_transient( $lock_key );
	}

	/**
	 * Tells whether a sitemap is locked for the current blog & locale and $sitemap_id.
	 *
	 * @since 4.1.2
	 *
	 * @param string $sitemap_id The sitemap ID.
	 * @return bool|int False if not locked, the lock UNIX release time otherwise.
	 */
	public function is_sitemap_locked( $sitemap_id = 'base' ) {

		$lock_key = $this->get_lock_key( $sitemap_id );
		if ( ! $lock_key ) return false;

		return \get_transient( $lock_key );
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

		$locked_timeout = static::get_instance()->is_sitemap_locked( $sitemap_id );

		if ( false !== $locked_timeout ) {
			static::get_instance()->output_locked_header( $locked_timeout );
			exit;
		}

		// Remove output, if any.
		static::$tsf->clean_response_header();

		if ( ! headers_sent() ) {
			\status_header( 200 );
			header( 'Content-type: text/xml; charset=utf-8', true );
		}

		// Fetch sitemap content and add trailing line. Already escaped internally.
		static::$tsf->get_view( 'sitemap/xml-sitemap', compact( 'sitemap_id' ) );
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

		static::$tsf->clean_response_header();

		if ( ! headers_sent() ) {
			\status_header( 200 );
			header( 'Content-type: text/xsl; charset=utf-8', true );
			header( 'Cache-Control: max-age=1800', true );
		}

		static::$tsf->get_view( 'sitemap/xsl-stylesheet' );
		exit;
	}

	/**
	 * Outputs the sitemap header.
	 *
	 * @since 4.0.0
	 * @since 4.1.3 Added a trailing newline to the stylesheet-tag for readability.
	 */
	public function output_sitemap_header() {

		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

		if ( static::$tsf->get_option( 'sitemap_styles' ) ) {
			printf(
				'<?xml-stylesheet type="text/xsl" href="%s"?>' . "\n",
				// phpcs:ignore, WordPress.Security.EscapeOutput
				$this->get_expected_sitemap_endpoint_url( 'xsl-stylesheet' )
			);
		}
	}

	/**
	 * Returns the opening tag for the sitemap urlset.
	 *
	 * @since 4.0.0
	 */
	public function output_sitemap_urlset_open_tag() {

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
		 * @param array $schemas The schema list. URLs are expected to be escaped.
		 */
		$schemas = (array) \apply_filters( 'the_seo_framework_sitemap_schemas', $schemas );

		$urlset = '<urlset';
		foreach ( $schemas as $type => $values ) {
			$urlset .= ' ' . $type . '="';
			if ( \is_array( $values ) ) {
				$urlset .= implode( ' ', $values );
			} else {
				$urlset .= $values;
			}
			$urlset .= '"';
		}
		$urlset .= '>';

		// phpcs:ignore, WordPress.Security.EscapeOutput -- Output is static from filter.
		echo $urlset . "\n";
	}

	/**
	 * Outputs the closing tag for the sitemap urlset.
	 *
	 * @since 4.0.0
	 */
	public function output_sitemap_urlset_close_tag() {
		echo '</urlset>';
	}

	/**
	 * Returns the sitemap base path.
	 * Useful when the path is non-standard, like notoriously in Polylang.
	 *
	 * @since 4.1.2
	 *
	 * @return string The path.
	 */
	private function get_sitemap_base_path() {
		/**
		 * @since 4.1.2
		 * @param string $path The home path.
		 */
		return \apply_filters(
			'the_seo_framework_sitemap_base_path',
			rtrim( parse_url( \get_home_url(), PHP_URL_PATH ), '/' )
		);
	}

	/**
	 * Returns the sitemap path prefix.
	 * Useful when the prefix path is non-standard, like notoriously in Polylang.
	 *
	 * @since 4.0.0
	 *
	 * @return string The path prefix.
	 */
	private function get_sitemap_path_prefix() {
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
	 * Gets the sitemap ID based on the current request URI.
	 *
	 * @since 4.0.0
	 * @since 4.0.2 Can now parse Unicode-encoded URLs.
	 *
	 * @param string $raw_uri The raw request URI. Unsafe.
	 * @return string|false The endpoint ID on success, false on failure.
	 */
	private function get_sitemap_id_from_uri( $raw_uri ) {

		// The path+query where sitemaps are served.
		$path_info = $this->get_sitemap_base_path_info();

		// A regex which detects $sitemap_path at the beginning of a string.
		$path_regex = '/^' . preg_quote( rawurldecode( $path_info['path'] ), '/' ) . '/ui';

		// See if the base matches the endpoint. This is crucial for query-based endpoints.
		if ( ! preg_match( $path_regex, $raw_uri ) ) return false;

		$stripped_uri = preg_replace( $path_regex, '', rtrim( $raw_uri, '/' ) );

		// Strip the base URI. If nothing's left, stop assimilating.
		if ( ! $stripped_uri ) return false;

		$sitemap_id = '';

		// Loop over the sitemap endpoints, and see if it matches the stripped uri.
		if ( $path_info['use_query_var'] ) {
			foreach ( $this->get_sitemap_endpoint_list() as $_id => $_data ) {
				$_regex = '/^' . preg_quote( $_id, '/' ) . '/i';
				// Yes, we know. It's not really checking for standardized query-variables.
				if ( preg_match( $_regex, $stripped_uri ) ) {
					$sitemap_id = $_id;
					break;
				}
			}
		} else {
			foreach ( $this->get_sitemap_endpoint_list() as $_id => $_data ) {
				if ( preg_match( $_data['regex'], $stripped_uri ) ) {
					$sitemap_id = $_id;
					break;
				}
			}
		}

		return $sitemap_id ?: false;
	}

	/**
	 * Returns the base path information for the sitemap.
	 *
	 * @since 4.0.0
	 * @global \WP_Rewrite $wp_rewrite
	 *
	 * @return array : {
	 *    string path          : The sitemap base path, like subdirectories or translations.
	 *    bool   use_query_var : Whether to use the query var.
	 * }
	 */
	private function get_sitemap_base_path_info() {
		global $wp_rewrite;

		$base_path = $this->get_sitemap_base_path();
		$prefix    = $this->get_sitemap_path_prefix();

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
	 *
	 * @return int bytes freed.
	 */
	public function get_freed_memory() {
		return $this->clean_up_globals( true );
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
	 *
	 * @param bool $get_freed_memory Whether to return the freed memory in bytes.
	 * @return int $freed_memory in bytes
	 */
	private function clean_up_globals( $get_freed_memory = false ) {

		static $freed_memory = null;

		if ( $get_freed_memory )
			return $freed_memory;

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

		$freed_memory = $memory - memory_get_usage();
	}
}

$_load_sitemap_class();
