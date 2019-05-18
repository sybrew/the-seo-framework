<?php
/**
 * @package The_SEO_Framework\Classes
 */
namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\Sitemaps
 *
 * Handles sitemap output.
 *
 * @since 2.8.0
 */
class Sitemaps extends Metaboxes {

	/**
	 * Checks if sitemap is being output.
	 *
	 * @since 2.5.2
	 *
	 * @var bool true if sitemap is being output.
	 */
	protected $doing_sitemap = false;

	/**
	 * Determines whether we can output sitemap or not based on options and blog status.
	 *
	 * @since 2.6.0
	 * @since 2.9.2 : Now returns true when using plain and ugly permalinks.
	 * @staticvar bool $cache
	 *
	 * @return bool
	 */
	public function can_run_sitemap() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		/**
		 * Don't do anything on a deleted or spam blog on MultiSite.
		 * There's nothing to find anyway.
		 */
		return $cache = $this->get_option( 'sitemaps_output' ) && ! $this->current_blog_is_spam_or_deleted();
	}

	/**
	 * Adds rewrite rule to WordPress
	 * This rule defines the sitemap.xml output
	 *
	 * @since 2.2.9
	 *
	 * @param bool $force add the rule anyway, regardless of detected environment.
	 */
	public function rewrite_rule_sitemap( $force = false ) {

		//* Adding rewrite rules only has effect when permalink structures are active.
		if ( $this->can_run_sitemap() || $force ) {
			/**
			 * Don't do anything if a sitemap plugin is active.
			 * On sitemap plugin activation, the sitemap plugin should flush the
			 * rewrite rules. If it doesn't, then this plugin's sitemap will be called.
			 */
			if ( $this->detect_sitemap_plugin() )
				return;

			\add_rewrite_rule( 'sitemap\.xml$', 'index.php?the_seo_framework_sitemap=xml', 'top' );
			\add_rewrite_rule( 'sitemap\.xsl$', 'index.php?the_seo_framework_sitemap=xsl', 'top' );
		}
	}

	/**
	 * Registers the_seo_framework_sitemap to WP_Query.
	 *
	 * @since 2.2.9
	 *
	 * @param array $vars The WP_Query variables.
	 * @return array $vars The adjusted vars.
	 */
	public function enqueue_sitemap_query_vars( $vars ) {

		if ( $this->can_run_sitemap() ) {
			$vars[] = 'the_seo_framework_sitemap';
		}

		return $vars;
	}

	/**
	 * Outputs sitemap.xml 'file' and header on sitemap query.
	 * Also cleans up globals and sets up variables.
	 *
	 * @since 2.2.9
	 */
	public function maybe_output_sitemap() {

		if ( $this->can_run_sitemap() ) {
			global $wp_query;

			if ( isset( $wp_query->query_vars['the_seo_framework_sitemap'] ) && 'xml' === $wp_query->query_vars['the_seo_framework_sitemap'] ) {

				$this->validate_sitemap_scheme();

				// Don't let WordPress think this is 404.
				$wp_query->is_404 = false;

				$this->doing_sitemap = true;

				/**
				 * Set at least 2000 variables free.
				 * Freeing 0.15MB on a clean WordPress installation.
				 * @since 2.6.0
				 */
				$this->clean_up_globals_for_sitemap();

				$this->output_sitemap();
			}
		}
	}

	/**
	 * Verifies whether the requested URI scheme matches the home URL input.
	 * If it doesn't, it'll redirect the visitor to the set scheme in WordPress home URL settings.
	 *
	 * This prevents invalid cached scheme outputs in the sitemap.
	 *
	 * NOTE: To alleviate bug reports, we also check for the scheme settings.
	 *       So, if users are experiencing issues with this (they won't), then tell them to
	 *       set a preferred URL scheme.
	 *
	 * Normally, WordPress takes care of this via `redirect_canonical()`.
	 * However, `redirect_canonical()` adds unwanted trailing slashes.
	 *
	 * So, we output the sitemap before `redirect_canonical()` fires. Then, we need this to
	 * prevent incorrect scheme bias when the "automatic" scheme setting is turned on.
	 * As otherwise, the plugin will cache the sitemap with the invalid scheme.
	 *
	 * All this is to prevent bad(ly configured) bots triggering unwanted schemes.
	 *
	 * @since 3.1.0
	 * @TODO consider hijacking get_preferred_scheme() instead.
	 * @TODO consider hijacking that filter always, making the "automatic" option even more reliable.
	 */
	protected function validate_sitemap_scheme() {

		if ( $this->get_option( 'canonical_scheme' ) !== 'automatic' ) return;

		$wp_scheme = strtolower( parse_url( \get_home_url(), PHP_URL_SCHEME ) );
		switch ( $wp_scheme ) {
			case 'https':
				if ( $this->is_ssl() ) return;
				break;

			case 'http':
				if ( ! $this->is_ssl() ) return;
				break;

			default:
				// parse_url failure. Bail.
				return;
		}

		//? Prevent redirect loop.
		$fix_arg = [
			'name'  => 'tsfrf', // abbr: tsf redirect fix
			'value' => '1',
		];
		if ( empty( $_GET[ $fix_arg['name'] ] )
		|| $_GET[ $fix_arg['name'] ] != $fix_arg['value'] ) { // loose comparison OK.

			$this->clean_response_header();

			\wp_safe_redirect( \add_query_arg(
				$fix_arg['name'],
				$fix_arg['value'],
				$this->set_url_scheme( $this->get_sitemap_xml_url(), $wp_scheme )
			), 301 );
			exit;
		}
	}

	/**
	 * Outputs sitemap.xsl 'file' and header on sitemap stylesheet query.
	 *
	 * @since 2.2.9
	 */
	public function maybe_output_sitemap_stylesheet() {

		if ( $this->can_run_sitemap() ) {
			global $wp_query;

			if ( isset( $wp_query->query_vars['the_seo_framework_sitemap'] ) && 'xsl' === $wp_query->query_vars['the_seo_framework_sitemap'] ) {
				// Don't let WordPress think this is 404.
				$wp_query->is_404 = false;

				$this->doing_sitemap = true;

				$this->output_sitemap_xsl_stylesheet();
			}
		}
	}

	/**
	 * Destroys unused $GLOBALS.
	 *
	 * This method is to be used prior to outputting sitemap.
	 *
	 * @since 2.6.0
	 * @since 2.8.0 Renamed from clean_up_globals().
	 *
	 * @param bool $get_freed_memory Whether to return the freed memory in bytes.
	 * @return int $freed_memory
	 */
	protected function clean_up_globals_for_sitemap( $get_freed_memory = false ) {

		static $freed_memory = null;

		if ( $get_freed_memory )
			return $freed_memory;

		$this->the_seo_framework_debug and $memory = memory_get_usage();

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
			'shortcode_tags',
		];

		foreach ( $remove as $key => $value ) {
			if ( is_array( $value ) ) {
				foreach ( $value as $v )
					unset( $GLOBALS[ $key ][ $v ] );
			} else {
				unset( $GLOBALS[ $value ] );
			}
		}

		$this->the_seo_framework_debug and $freed_memory = $memory - memory_get_usage();
	}

	/**
	 * Outputs sitemap.xml 'file' and header.
	 *
	 * @since 2.2.9
	 * @since 3.1.0 1. Now outputs 200-response code.
	 *              2. Now outputs robots tag, preventing indexing.
	 *              3. Now overrides other header tags.
	 */
	protected function output_sitemap() {

		//* Remove output, if any.
		$this->clean_response_header();

		if ( ! headers_sent() ) {
			\status_header( 200 );
			header( 'Content-type: text/xml; charset=utf-8', true );
			header( 'X-Robots-Tag: noindex, follow', true );
		}

		//* Fetch sitemap content and add trailing line. Already escaped internally.
		$this->get_view( 'sitemap/xml-sitemap' );
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
	 */
	public function output_sitemap_xsl_stylesheet() {

		$this->clean_response_header();

		if ( ! headers_sent() ) {
			\status_header( 200 );
			header( 'Content-type: text/xsl; charset=utf-8', true );
			header( 'Cache-Control: max-age=1800', true );
			header( 'X-Robots-Tag: noindex, follow', true );
		}

		$this->get_view( 'sitemap/xsl-stylesheet' );
		exit;
	}

	/**
	 * Returns the opening tag for the sitemap URLset.
	 *
	 * @since 2.8.0
	 *
	 * @return string The sitemap URLset opening tag.
	 */
	public function get_sitemap_urlset_open_tag() {

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
			if ( is_array( $values ) ) {
				$urlset .= implode( ' ', $values );
			} else {
				$urlset .= $values;
			}
			$urlset .= '"';
		}
		$urlset .= '>';

		return $urlset . "\n";
	}

	/**
	 * Returns the closing tag for the sitemap URLset.
	 *
	 * @since 2.8.0
	 *
	 * @return string The sitemap URLset closing tag.
	 */
	public function get_sitemap_urlset_close_tag() {
		return '</urlset>';
	}

	/**
	 * Returns stylesheet XSL location tag.
	 *
	 * @since 2.8.0
	 * @since 2.9.3 Now checks against request to see if there's a domain mismatch.
	 *
	 * @return string The sitemap XSL location tag.
	 */
	public function get_sitemap_xsl_stylesheet_tag() {

		if ( $this->get_option( 'sitemap_styles' ) ) {

			$url = \esc_url( $this->get_sitemap_xsl_url(), [ 'http', 'https' ] );

			if ( ! empty( $_SERVER['HTTP_HOST'] ) ) {
				$_parsed   = \wp_parse_url( $url );
				$_r_parsed = \wp_parse_url(
					\esc_url(
						\wp_unslash( $_SERVER['HTTP_HOST'] ),
						[ 'http', 'https' ]
					)
				); // sanitization ok: esc_url is esc_url_raw with a bowtie.

				if ( isset( $_parsed['host'], $_r_parsed['host'] ) )
					if ( $_parsed['host'] !== $_r_parsed['host'] )
						return '';
			}

			return sprintf( '<?xml-stylesheet type="text/xsl" href="%s"?>', $url ) . "\n";
		}

		return '';
	}

	/**
	 * Returns the stylesheet XSL location URL.
	 *
	 * @since 2.8.0
	 * @since 3.0.0 1: No longer uses home URL from cache. But now uses `get_home_url()`.
	 *              2: Now takes query parameters (if any) and restores them correctly.
	 * @global \WP_Rewrite $wp_rewrite
	 *
	 * @return string URL location of the XSL stylesheet. Unescaped.
	 */
	public function get_sitemap_xsl_url() {
		global $wp_rewrite;

		$home = $this->set_url_scheme( \get_home_url() );

		$parsed = parse_url( $home );
		$query  = isset( $parsed['query'] ) ? $parsed['query'] : '';

		if ( $query )
			$home = str_replace( '?' . $query, '', $home );

		$home = \trailingslashit( $home );

		if ( $wp_rewrite->using_index_permalinks() ) {
			$loc = $home . 'index.php/sitemap.xsl';
		} elseif ( $wp_rewrite->using_permalinks() ) {
			$loc = $home . 'sitemap.xsl';
		} else {
			$loc = $home . '?the_seo_framework_sitemap=xsl';
		}

		if ( $query )
			$loc = $this->append_php_query( $loc, $query );

		return $loc;
	}

	/**
	 * Returns the sitemap XML location URL.
	 *
	 * @since 2.9.2
	 * @since 3.0.0 1: No longer uses home URL from cache. But now uses `get_home_url()`.
	 *              2: Now takes query parameters (if any) and restores them correctly.
	 * @global \WP_Rewrite $wp_rewrite
	 *
	 * @return string URL location of the XML sitemap. Unescaped.
	 */
	public function get_sitemap_xml_url() {
		global $wp_rewrite;

		$home = $this->set_url_scheme( \get_home_url() );

		$parsed = parse_url( $home );
		$query  = isset( $parsed['query'] ) ? $parsed['query'] : '';

		if ( $query )
			$home = str_replace( '?' . $query, '', $home );

		$home = \trailingslashit( $home );

		if ( $query )
			$home = str_replace( '?' . $query, '', $home );

		if ( $wp_rewrite->using_index_permalinks() ) {
			$loc = $home . 'index.php/sitemap.xml';
		} elseif ( $wp_rewrite->using_permalinks() ) {
			$loc = $home . 'sitemap.xml';
		} else {
			$loc = $home . '?the_seo_framework_sitemap=xml';
		}

		if ( $query )
			$loc = $this->append_php_query( $loc, $query );

		return $loc;
	}

	/**
	 * Returns the robots.txt location URL.
	 * Only allows root domains.
	 *
	 * @since 2.9.2
	 * @global \WP_Rewrite $wp_rewrite
	 *
	 * @return string URL location of robots.txt. Unescaped.
	 */
	public function get_robots_txt_url() {
		global $wp_rewrite;

		if ( $wp_rewrite->using_permalinks() && ! $this->is_subdirectory_installation() ) {
			$home = \trailingslashit( $this->set_url_scheme( $this->get_home_host() ) );
			$loc  = $home . 'robots.txt';
		} else {
			$loc = '';
		}

		return $loc;
	}

	/**
	 * Create sitemap.xml content transient.
	 *
	 * @since 2.6.0
	 * @since 3.0.6 Now only sets transient when the option is checked.
	 *
	 * @param string|bool $sitemap_content The sitemap transient content.
	 * @return string The sitemap content.
	 */
	public function setup_sitemap( $sitemap_content = false ) {

		if ( false === $sitemap_content ) {
			//* Transient doesn't exist yet.
			$sitemap = new Builders\Sitemap;
			$sitemap->prepare_generation();
			$sitemap_content = $sitemap->build_sitemap_content();
			$sitemap->shutdown_generation();
			$sitemap = null; // destroy class.

			/**
			 * Transient expiration: 1 week.
			 * Keep the sitemap for at most 1 week.
			 */
			$expiration = WEEK_IN_SECONDS;

			if ( $this->get_option( 'cache_sitemap' ) )
				$this->set_transient( $this->get_sitemap_transient_name(), $sitemap_content, $expiration );
		}

		return $sitemap_content;
	}

	/**
	 * Ping search engines on post publish.
	 *
	 * @since 2.2.9
	 * @since 2.8.0 Only worked when the blog was not public...
	 * @since 3.1.0 Now allows one ping per language.
	 *              @uses $this->add_cache_key_suffix()
	 * @since 3.2.3 1. Now works as intended again.
	 *              2. Removed Easter egg.
	 *
	 * @return void Early if blog is not public.
	 */
	public function ping_searchengines() {

		if ( $this->get_option( 'site_noindex' ) || ! $this->is_blog_public() )
			return;

		$transient = $this->add_cache_key_suffix( 'tsf_throttle_ping' );

		//* NOTE: Use legacy get_transient to prevent ping spam.
		if ( false === \get_transient( $transient ) ) {
			//* Transient doesn't exist yet.

			if ( $this->get_option( 'ping_google' ) )
				$this->ping_google();

			if ( $this->get_option( 'ping_bing' ) )
				$this->ping_bing();

			/**
			 * @since 2.5.1
			 * @param int $expiration The minimum time between two pings.
			 */
			$expiration = (int) \apply_filters( 'the_seo_framework_sitemap_throttle_s', HOUR_IN_SECONDS );

			//* @NOTE: Using legacy set_transient to bypass TSF's transient filters and prevent ping spam.
			\set_transient( $transient, 1, $expiration );
		}
	}

	/**
	 * Pings the sitemap location to Google.
	 *
	 * @since 2.2.9
	 * @since 3.1.0 Updated ping URL. Old one still worked, too.
	 * @link https://support.google.com/webmasters/answer/6065812?hl=en
	 */
	public function ping_google() {
		$pingurl = 'http://www.google.com/ping?sitemap=' . rawurlencode( $this->get_sitemap_xml_url() );
		\wp_safe_remote_get( $pingurl, [ 'timeout' => 3 ] );
	}

	/**
	 * Pings the sitemap location to Bing.
	 *
	 * @since 2.2.9
	 * @since 3.2.3 Updated ping URL. Old one still worked, too.
	 * @link https://www.bing.com/webmaster/help/how-to-submit-sitemaps-82a15bd4
	 */
	public function ping_bing() {
		$pingurl = 'http://www.bing.com/ping?sitemap=' . rawurlencode( $this->get_sitemap_xml_url() );
		\wp_safe_remote_get( $pingurl, [ 'timeout' => 3 ] );
	}

	/**
	 * Enqueues rewrite rules flush.
	 *
	 * @since 2.8.0
	 */
	public function reinitialize_rewrite() {

		if ( $this->get_option( 'sitemaps_output', false ) ) {
			$this->rewrite_rule_sitemap();
			$this->enqueue_rewrite_activate( true );
		} else {
			$this->enqueue_rewrite_deactivate( true );
		}
	}

	/**
	 * Enqueue rewrite flush for activation.
	 *
	 * @since 2.3.0
	 * @access private
	 * @staticvar bool $flush Determines whether a flush is enqueued.
	 *
	 * @param bool $enqueue Whether to enqueue the flush or return its state.
	 * @return bool Whether to flush.
	 */
	public function enqueue_rewrite_activate( $enqueue = false ) {
		static $flush = null;
		return $flush ?: $flush = $enqueue;
	}

	/**
	 * Enqueue rewrite flush for deactivation.
	 *
	 * @since 2.3.0
	 * @access private
	 * @staticvar bool $flush Determines whether a flush is enqueued.
	 *
	 * @param bool $enqueue Whether to enqueue the flush or return its state.
	 * @return bool Whether to flush.
	 */
	public function enqueue_rewrite_deactivate( $enqueue = false ) {
		static $flush = null;
		return $flush ?: $flush = $enqueue;
	}

	/**
	 * Flush rewrite rules based on static variables.
	 *
	 * @since 2.3.0
	 * @access private
	 */
	public function maybe_flush_rewrite() {

		if ( $this->enqueue_rewrite_activate() )
			$this->flush_rewrite_rules_activation();

		if ( $this->enqueue_rewrite_deactivate() )
			$this->flush_rewrite_rules_deactivation();
	}

	/**
	 * Add and Flush rewrite rules on plugin settings change.
	 *
	 * @since 2.6.6.1
	 * @access private
	 */
	public function flush_rewrite_rules_activation() {

		//* This function is called statically.
		$this->rewrite_rule_sitemap( true );

		\flush_rewrite_rules();
	}

	/**
	 * Flush rewrite rules on settings change.
	 *
	 * @since 2.6.6.1
	 * @since 3.2.2 Now unsets the XSL stylesheet.
	 * @access private
	 * @global \WP_Rewrite $wp_rewrite
	 */
	public function flush_rewrite_rules_deactivation() {
		global $wp_rewrite;

		$wp_rewrite->init();

		unset( $wp_rewrite->extra_rules_top['sitemap\.xml$'] );
		unset( $wp_rewrite->extra_rules_top['sitemap\.xsl$'] );

		$wp_rewrite->flush_rules( true );
	}

	/**
	 * Returns sitemap color scheme.
	 *
	 * @since 2.8.0
	 *
	 * @param bool $get_defaults Whether to get the default colors.
	 * @return array The sitemap colors.
	 */
	public function get_sitemap_colors( $get_defaults = false ) {

		if ( $get_defaults ) {
			$colors = [
				'main'   => '#333',
				'accent' => '#00cd98',
			];
		} else {
			$main   = $this->s_color_hex( $this->get_option( 'sitemap_color_main' ) );
			$accent = $this->s_color_hex( $this->get_option( 'sitemap_color_accent' ) );

			$options = [
				'main'   => $main ? '#' . $main : '',
				'accent' => $accent ? '#' . $accent : '',
			];

			$options = array_filter( $options );

			$colors = array_merge( $this->get_sitemap_colors( true ), $options );
		}

		return $colors;
	}
}
