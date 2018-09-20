<?php
/**
 * @package The_SEO_Framework\Classes
 */
namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2018 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
	 * Destroys unused $GLOBALS. To be used prior to outputting sitemap.
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
		$this->output_sitemap_content();
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
	 * Output sitemap.xml content from transient.
	 *
	 * @since 2.8.0
	 *
	 * @return string Sitemap XML contents.
	 */
	protected function output_sitemap_content() {

		$this->the_seo_framework_debug and $timer_start = microtime( true );

		/**
		 * Re-use the variable, eliminating database requests
		 * @since 2.4.0
		 */
		$sitemap_content = $this->get_option( 'cache_sitemap' ) ? $this->get_transient( $this->get_sitemap_transient_name() ) : false;

		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		echo $this->get_sitemap_xsl_stylesheet_tag();

		/**
		 * Output debug prior output.
		 * @since 2.8.0
		 */
		if ( $this->the_seo_framework_debug ) {
			echo '<!-- Site estimated peak usage prior to generation: ' . number_format( memory_get_peak_usage() / 1024 / 1024, 3 ) . ' MB -->' . "\n";
			echo '<!-- System estimated peak usage prior to generation: ' . number_format( memory_get_peak_usage( true ) / 1024 / 1024, 3 ) . ' MB -->' . "\n";
		}

		echo $this->get_sitemap_urlset_open_tag();
		echo $this->setup_sitemap( $sitemap_content );
		echo $this->get_sitemap_urlset_close_tag();

		if ( false === $sitemap_content ) {
			echo "\n" . '<!-- ' . \esc_html__( 'Sitemap is generated for this view', 'autodescription' ) . ' -->';
		} else {
			echo "\n" . '<!-- ' . \esc_html__( 'Sitemap is served from cache', 'autodescription' ) . ' -->';
		}

		/**
		 * Output debug info.
		 * @since 2.3.7
		 */
		if ( $this->the_seo_framework_debug ) {
			echo "\n" . '<!-- Site estimated peak usage: ' . number_format( memory_get_peak_usage() / 1024 / 1024, 3 ) . ' MB -->';
			echo "\n" . '<!-- System estimated peak usage: ' . number_format( memory_get_peak_usage( true ) / 1024 / 1024, 3 ) . ' MB -->';
			echo "\n" . '<!-- Freed memory prior to generation: ' . number_format( $this->clean_up_globals_for_sitemap( true ) / 1024, 3 ) . ' kB -->';
			echo "\n" . '<!-- Sitemap generation time: ' . number_format( microtime( true ) - $timer_start, 6 ) . ' seconds -->';
		}
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
		$query = isset( $parsed['query'] ) ? $parsed['query'] : '';

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
		$query = isset( $parsed['query'] ) ? $parsed['query'] : '';

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
			$loc = $home . 'robots.txt';
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
	 * @param string|bool $content required The sitemap transient content.
	 * @return string The sitemap content.
	 */
	public function setup_sitemap( $sitemap_content = false ) {

		if ( false === $sitemap_content ) {
			//* Transient doesn't exist yet.
			$sitemap_content = $this->generate_sitemap();

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
	 * Generate sitemap.xml content.
	 *
	 * @since 2.2.9
	 * @since 2.8.0 Now adjusts memory limit when possible.
	 * @since 2.9.3 No longer crashes on WordPress sites below WP 4.6.
	 * @since 3.0.4 No longer outputs empty URL entries.
	 * @since 3.1.0 1. Removed the WP<4.6 function_exists check.
	 *              2. Now uses WordPress' built-in memory raiser function, with "context" sitemap.
	 *
	 * @return string The sitemap content.
	 */
	protected function generate_sitemap() {

		\wp_raise_memory_limit( 'sitemap' );

		$content = '';

		$total_post_limit = $this->get_sitemap_post_limit();

		/**
		 * Maximum pages, posts and cpt to fetch.
		 * A total of 3600, consisting of 3 times $total_post_limit.
		 *
		 * @since 2.2.9
		 * TODO remove?
		 * @param int $totalpages
		 * @param int $totalposts
		 * @param int $total_cpt_posts
		 */
		$totalpages = (int) \apply_filters( 'the_seo_framework_sitemap_pages_count', $total_post_limit );
		$totalposts = (int) \apply_filters( 'the_seo_framework_sitemap_posts_count', $total_post_limit );
		$total_cpt_posts = (int) \apply_filters( 'the_seo_framework_sitemap_custom_posts_count', $total_post_limit );

		$noindex_post_types = $this->get_option( $this->get_robots_post_type_option_id( 'noindex' ) );

		if ( ! empty( $noindex_post_types['page'] ) ) {
			$totalpages = 0;
		}
		if ( ! empty( $noindex_post_types['post'] ) ) {
			$totalposts = 0;
		}

		$latest_pages = [];
		$latest_posts = [];
		$latest_cpt_posts = [];
		$cpt = [];

		//* Sets timezone according to WordPress settings.
		$this->set_timezone();
		$timestamp_format = $this->get_timestamp_format();

		$show_priority = (bool) $this->get_option( 'sitemaps_priority' );
		$show_modified = (bool) $this->get_option( 'sitemaps_modified' );

		/**
		 * @since 2.2.9
		 * @param bool $timestamp Whether to display the timestamp.
		 */
		$timestamp = (bool) \apply_filters( 'the_seo_framework_sitemap_timestamp', true );

		if ( $timestamp )
			$content .= sprintf(
				'<!-- %s -->',
				sprintf(
					/* translators: %s = timestamp */
					\esc_html__( 'Sitemap is generated on %s', 'autodescription' ),
					\current_time( 'Y-m-d H:i:s \G\M\T' )
				)
			) . "\n";

		$wp_query = new \WP_Query;
		$wp_query->init();
		$query = $wp_query->query = $wp_query->query_vars = [];

		if ( $totalpages ) {
			//* Ascend by the date for normal pages. Older pages get to the top of the list.
			$defaults = [
				'posts_per_page'   => $totalpages,
				'post_type'        => 'page',
				'orderby'          => 'date',
				'order'            => 'ASC',
				'post_status'      => 'publish',
				'has_password'     => false,
				'fields'           => 'ids',
				'cache_results'    => false,
				'suppress_filters' => false,
				'no_found_rows'    => true,
			];

			/**
			 * @since 2.8.0
			 * @since 3.0.6: $args['suppress_filters'] now defaults to false.
			 * @param array $args The new query arguments.
			 * @param array $defaults The default query arguments
			 */
			$args = \apply_filters( 'the_seo_framework_sitemap_pages_query_args', [], $defaults );

			$wp_query->query = $wp_query->query_vars = \wp_parse_args( $args, $defaults );
			$latest_pages = $wp_query->get_posts();
		}
		$latest_pages_amount = count( $latest_pages );

		if ( $latest_pages_amount > 0 ) :

			$page_on_front     = $this->has_page_on_front();
			$page_on_front_id  = (int) \get_option( 'page_on_front' );
			$page_for_posts_id = (int) \get_option( 'page_for_posts' );

			$id_on_front = $page_on_front ? $page_on_front_id : $page_for_posts_id;

			//* Remove ID on front from list and add frontpage to list.
			if ( $page_on_front && false !== $key_on_front = array_search( $id_on_front, $latest_pages, true ) ) {
				unset( $latest_pages[ $key_on_front ] );
			}

			//= Render frontpage.
			$front_page = $page_on_front ? \get_post( $id_on_front ) : null;
			$render_front = false;
			if ( ! $this->get_option( 'homepage_noindex' ) ) {
				if ( $page_on_front ) {
					$render_front = isset( $front_page->ID )
						&& $this->is_post_included_in_sitemap( $front_page->ID )
						&& ! $this->is_protected( $front_page->ID );
				} else {
					$render_front = $this->is_post_included_in_sitemap( $id_on_front );
				}
			}
			if ( $render_front ) {
				$_url = $this->get_homepage_permalink();
				if ( $_url ) {
					$content .= "\t<url>\n";
					$content .= "\t\t<loc>" . $_url . "</loc>\n";

					if ( $show_modified ) {
						if ( $page_on_front ) {
							$front_modified_gmt = isset( $front_page->post_modified_gmt ) ? $front_page->post_modified_gmt : '0000-00-00 00:00:00';
						} else {
							$args = [
								'numberposts'  => 1,
								'post_type'    => 'post',
								'post_status'  => 'publish',
								'has_password' => false,
								'orderby'      => 'post_date',
								'order'        => 'DESC',
								'offset'       => 0,
							];
							$latests_posts = \wp_get_recent_posts( $args, OBJECT );
							$latest_post = isset( $latests_posts[0] ) ? $latests_posts[0] : null;
							$front_modified_gmt = isset( $latest_post->post_date_gmt ) ? $latest_post->post_date_gmt : '0000-00-00 00:00:00';
						}

						if ( '0000-00-00 00:00:00' !== $front_modified_gmt )
							$content .= "\t\t<lastmod>" . $this->gmt2date( $timestamp_format, $front_modified_gmt ) . "</lastmod>\n";
					}

					if ( $show_priority ) {
						$content .= "\t\t<priority>1.0</priority>\n";
					}
					$content .= "\t</url>\n";
				}
				//* Free memory.
				unset( $latests_posts, $latest_post, $front_page );
			}

			//= Render the page for posts.
			if ( $page_on_front && $page_for_posts_id ) :
				//* Remove ID for blog from list and add frontpage to list.
				if ( false !== $key_for_posts = array_search( $page_for_posts_id, $latest_pages, true ) ) {
					unset( $latest_pages[ $key_for_posts ] );
				}

				$blog_page = \get_post( $page_for_posts_id );
				$render_blog = isset( $blog_page->ID )
					&& $this->is_post_included_in_sitemap( $blog_page->ID )
					&& ! $this->is_protected( $blog_page->ID );

				if ( $render_blog ) {
					$_url = $this->create_canonical_url( [ 'id' => $blog_page->ID ] );
					if ( $_url ) {
						$content .= "\t<url>\n";
						$content .= "\t\t<loc>" . $_url . "</loc>\n";

						if ( $show_modified ) {
							$args = [
								'numberposts'  => 1,
								'post_type'    => 'post',
								'post_status'  => 'publish',
								'has_password' => false,
								'orderby'      => 'post_date',
								'order'        => 'DESC',
								'offset'       => 0,
							];
							$lastest_posts = \wp_get_recent_posts( $args, OBJECT );
							$lastest_post = isset( $lastest_posts[0] ) ? $lastest_posts[0] : null;
							$latest_post_published_gmt = isset( $lastest_post->post_date_gmt ) ? $lastest_post->post_date_gmt : '0000-00-00 00:00:00';
							$page_for_posts_modified_gmt = $blog_page->post_modified_gmt;

							if ( strtotime( $latest_post_published_gmt ) > strtotime( $page_for_posts_modified_gmt ) ) {
								$page_modified_gmt = $latest_post_published_gmt;
							} else {
								$page_modified_gmt = $page_for_posts_modified_gmt;
							}

							if ( '0000-00-00 00:00:00' !== $page_modified_gmt )
								$content .= "\t\t<lastmod>" . $this->gmt2date( $timestamp_format, $page_modified_gmt ) . "</lastmod>\n";
						}

						if ( $show_priority ) {
							$content .= "\t\t<priority>0.9</priority>\n";
						}
						$content .= "\t</url>\n";
					}
				}

				//* Free memory.
				unset( $latest_posts, $latest_post, $blog_page );
			endif;

			foreach ( $latest_pages as $page_id ) :
				$page = \get_post( $page_id );
				if ( empty( $page->ID ) || ! $this->is_post_included_in_sitemap( $page->ID ) )
					continue;

				$_url = $this->create_canonical_url( [ 'id' => $page->ID ] );
				if ( ! $_url )
					continue;

				$content .= "\t<url>\n";
				$content .= "\t\t<loc>" . $_url . "</loc>\n";

				if ( $show_modified ) {
					$page_modified_gmt = $page->post_modified_gmt;

					if ( '0000-00-00 00:00:00' !== $page_modified_gmt )
						$content .= "\t\t<lastmod>" . $this->gmt2date( $timestamp_format, $page_modified_gmt ) . "</lastmod>\n";
				}

				if ( $show_priority ) {
					$content .= "\t\t<priority>0.9</priority>\n";
				}
				$content .= "\t</url>\n";
			endforeach;

			//* Free memory.
			unset( $latest_pages, $page );
		endif;

		if ( $totalposts ) {
			//* Descend by the date for posts. The latest posts get to the top of the list after pages.
			$defaults = [
				'posts_per_page'   => $totalposts,
				'post_type'        => 'post',
				'orderby'          => 'date',
				'order'            => 'DESC',
				'post_status'      => 'publish',
				'has_password'     => false,
				'fields'           => 'ids',
				'cache_results'    => false,
				'suppress_filters' => false,
				'no_found_rows'    => true,
			];

			/**
			 * @since 2.8.0
			 * @since 3.0.6: $args['suppress_filters'] now defaults to false.
			 * @param array $args The new query arguments.
			 * @param array $defaults The default query arguments
			 */
			$args = \apply_filters( 'the_seo_framework_sitemap_posts_query_args', [], $defaults );

			$wp_query->query = $wp_query->query_vars = \wp_parse_args( $args, $defaults );
			$latest_posts = $wp_query->get_posts();
		}
		$latest_posts_amount = count( $latest_posts );

		if ( $latest_posts_amount > 0 ) :
			/**
			 * Setting up priorities, with pages always being important.
			 *
			 * From there, older posts get a gradually lower priority. Down to 0.
			 * Differentiate with 1 / max posts (0 to $this->max_posts). With a 1 dot decimal.
			 */
			$priority = 0.9;

			/**
			 * Infinity is abstract. But what is it when it's both positive and negative?
			 * Undefined. Bugfix.
			 *
			 * @since 2.3.2
			 * @thanks Schlock | https://wordpress.org/support/topic/sitemap-xml-parsing-error
			 */
			$prioritydiff = 0;

			if ( $latest_posts_amount > 1 )
				$prioritydiff = 0.9 / $latest_posts_amount;

			// Keep it consistent. Only remove 0.1 when we only have a few posts.
			if ( $latest_posts_amount <= 9 && $latest_posts_amount > 1 )
				$prioritydiff = 0.1;

			/**
			 * This can be heavy.
			 */
			foreach ( $latest_posts as $post_id ) :
				$post = \get_post( $post_id );
				if ( empty( $post->ID ) || ! $this->is_post_included_in_sitemap( $post->ID ) )
					continue;

				$_url = $this->create_canonical_url( [ 'id' => $post->ID ] );
				if ( ! $_url )
					continue;

				$content .= "\t<url>\n";
				// No need to use static vars
				$content .= "\t\t<loc>" . $_url . "</loc>\n";

				if ( $show_modified ) {
					$post_modified_gmt = $post->post_modified_gmt;

					if ( '0000-00-00 00:00:00' !== $post_modified_gmt )
						$content .= "\t\t<lastmod>" . $this->gmt2date( $timestamp_format, $post_modified_gmt ) . "</lastmod>\n";
				}

				if ( $show_priority ) {
					$content .= "\t\t<priority>" . number_format( $priority, 1 ) . "</priority>\n";

					// Lower the priority for the next pass.
					$priority = $priority - $prioritydiff;

					// Cast away negative numbers.
					$priority = $priority <= 0 ? 0 : (float) $priority;
				}
				$content .= "\t</url>\n";
			endforeach;

			//* Free memory.
			unset( $latest_posts, $post );
		endif;

		if ( $total_cpt_posts ) :
			// TODO: Use only this loop, instead of separated page/post loops? See $not_cpt var.

			/**
			 * @since 2.5.0
			 * @param array $excluded_cpt The excluded custom post types.
			 */
			$excluded_cpt = (array) \apply_filters( 'the_seo_framework_sitemap_exclude_cpt', [] );

			$not_cpt = [ 'post', 'page', 'attachment' ];

			foreach ( $this->get_supported_post_types() as $post_type ) {
				if ( ! in_array( $post_type, $not_cpt, true ) ) {
					if ( empty( $excluded_cpt ) || ! in_array( $post_type, $excluded_cpt, true ) ) {
						if ( empty( $noindex_post_types[ $post_type ] ) )
							$cpt[] = $post_type;
					}
				}
			}

			if ( $cpt ) {
				//* Descend by the date for CPTs. The latest posts get to the top of the list after pages.
				$defaults = [
					'posts_per_page'   => $total_cpt_posts,
					'post_type'        => $cpt,
					'orderby'          => 'date',
					'order'            => 'DESC',
					'post_status'      => 'publish',
					'has_password'     => false,
					'fields'           => 'ids',
					'cache_results'    => false,
					'suppress_filters' => false,
					'no_found_rows'    => true,
				];

				/**
				 * @since 2.8.0
				 * @since 3.0.6: $args['suppress_filters'] now defaults to false.
				 * @param array $args The new query arguments.
				 * @param array $defaults The default query arguments
				 */
				$args = \apply_filters( 'the_seo_framework_sitemap_cpt_query_args', [], $defaults );

				$wp_query->query = $wp_query->query_vars = \wp_parse_args( $args, $defaults );
				$latest_cpt_posts = $wp_query->get_posts();
			}
		endif;
		$latest_cpt_posts_amount = count( $latest_cpt_posts );

		if ( $latest_cpt_posts_amount > 0 ) :

			/**
			 * Setting up priorities, with pages always being important.
			 *
			 * From there, older posts get a gradually lower priority. Down to 0.
			 * Differentiate with 1 / max posts (0 to $this->max_posts). With a 1 dot decimal.
			 */
			$priority_cpt = 0.9;

			$prioritydiff_cpt = 0;

			if ( $latest_cpt_posts_amount > 1 )
				$prioritydiff_cpt = 0.9 / $latest_cpt_posts_amount;

			// Keep it consistent. Only remove 0.1 when we only have a few posts.
			if ( $latest_cpt_posts_amount <= 9 && $latest_cpt_posts_amount > 1 )
				$prioritydiff_cpt = 0.1;

			/**
			 * This can be heavy.
			 */
			foreach ( $latest_cpt_posts as $ctp_post_id ) :
				$ctp_post = \get_post( $ctp_post_id );
				if ( empty( $ctp_post->ID ) || ! $this->is_post_included_in_sitemap( $ctp_post->ID ) )
					continue;

				$_url = $this->create_canonical_url( [ 'id' => $ctp_post->ID ] );
				if ( ! $_url )
					continue;

				$content .= "\t<url>\n";
				//* No need to use static vars
				$content .= "\t\t<loc>" . $_url . "</loc>\n";

				if ( $show_modified ) {
					$cpt_modified_gmt = $ctp_post->post_modified_gmt;
					//* Some CPT don't set modified time.
					if ( '0000-00-00 00:00:00' !== $cpt_modified_gmt )
						$content .= "\t\t<lastmod>" . $this->gmt2date( $timestamp_format, $cpt_modified_gmt ) . "</lastmod>\n";
				}

				if ( $show_priority ) {
					$content .= "\t\t<priority>" . number_format( $priority_cpt, 1 ) . "</priority>\n";

					// Lower the priority for the next pass.
					$priority_cpt = $priority_cpt - $prioritydiff_cpt;

					// Cast away negative numbers.
					$priority_cpt = $priority_cpt <= 0 ? 0 : (float) $priority_cpt;
				}
				$content .= "\t</url>\n";
			endforeach;

			//* Free memory.
			unset( $latest_cpt_posts, $ctp_post );
		endif;

		/**
		 * @since 2.5.2
		 * @example return value: [ 'http://example.com' => [ 'lastmod' => '14-01-2018', 'priority' => 0.9 ] ]
		 * @param array $custom_urls : {
		 *    @param string (key) $url The absolute url to the page. : array {
		 *       @param string           $lastmod  UNIXTIME Last modified date, e.g. "2016-01-26 13:04:55"
		 *       @param float|int|string $priority URL Priority
		 *    }
		 * }
		 */
		$custom_urls = (array) \apply_filters( 'the_seo_framework_sitemap_additional_urls', [] );

		if ( $custom_urls ) {

			foreach ( $custom_urls as $url => $args ) {

				if ( ! is_array( $args ) ) {
					//* If there are no args, it's assigned as URL (per example)
					$url = $args;
				}

				$content .= "\t<url>\n";
				//* No need to use static vars
				$content .= "\t\t<loc>" . \esc_url_raw( $url, [ 'http', 'https' ] ) . "</loc>\n";

				if ( isset( $args['lastmod'] ) && $args['lastmod'] ) {
					$content .= "\t\t<lastmod>" . \mysql2date( $timestamp_format, $args['lastmod'], false ) . "</lastmod>\n";
				}

				if ( $show_priority ) {
					if ( isset( $args['priority'] ) && $args['priority'] ) {
						$priority = $args['priority'];
					} else {
						$priority = 0.9;
					}
					$content .= "\t\t<priority>" . number_format( $priority, 1 ) . "</priority>\n";
				}
				$content .= "\t</url>\n";
			}
		}

		/**
		 * @since 2.5.2
		 * @param string $extend Custom sitemap extension. Must be escaped.
		 */
		$extend = (string) \apply_filters( 'the_seo_framework_sitemap_extend', '' );

		if ( $extend )
			$content .= "\t" . $extend . "\n";

		//* Reset timezone to default.
		$this->reset_timezone();

		return $content;
	}

	/**
	 * Determines if post is possibly included in the sitemap.
	 *
	 * This is a weak check, as the filter might not be present outside of the
	 * sitemap's scope.
	 * The URL also isn't checked, nor the position.
	 *
	 * @since 3.0.4
	 * @since 3.0.6 First filter value now works as intended.
	 * @since 3.1.0 1. Resolved a PHP notice when ID is 0, resulting in returning false-esque unintentionally.
	 *              2. Now accepts 0 in the filter.
	 *
	 * @param int $id The post ID to check. When 0, the custom field will not be checked.
	 * @return bool True if included, false otherwise.
	 */
	public function is_post_included_in_sitemap( $id ) {

		static $excluded = null;
		if ( null === $excluded ) {
			/**
			 * @since 2.5.2
			 * @since 2.8.0 : No longer accepts '0' as entry.
			 * @since 3.1.0 : '0' is accepted again.
			 * @param array $excluded Sequential list of excluded IDs: [ int ...post_id ]
			 */
			$excluded = (array) \apply_filters( 'the_seo_framework_sitemap_exclude_ids', [] );

			if ( empty( $excluded ) ) {
				$excluded = [];
			} else {
				$excluded = array_flip( $excluded );
			}
		}

		// If it's not in the exclusion list, set it to true.
		$included = ! isset( $excluded[ $id ] );

		if ( $included && $id ) {
			// If it's indexed, keep it true.
			$included = ! $this->get_custom_field( '_genesis_noindex', $id );
		}

		return $included;
	}

	/**
	 * Ping search engines on post publish.
	 *
	 * @since 2.2.9
	 * @since 3.1.0 Now allows one ping per language.
	 *              @uses $this->add_cache_key_suffix()
	 *
	 * @return void Early if blog is not public.
	 */
	public function ping_searchengines() {

		if ( $this->get_option( 'site_noindex' ) || $this->is_blog_public() )
			return;

		$transient = $this->add_cache_key_suffix( 'tsf_throttle_ping' );

		//* NOTE: Use legacy get_transient to prevent ping spam.
		if ( false === \get_transient( $transient ) ) {
			//* Transient doesn't exist yet.

			if ( $this->get_option( 'ping_google' ) )
				$this->ping_google();

			if ( $this->get_option( 'ping_bing' ) )
				$this->ping_bing();

			if ( $this->get_option( 'ping_yandex' ) )
				$this->ping_yandex();

			// Sorry, I couldn't help myself.
			$throttle = 'Bert and Ernie are weird.';

			/**
			 * @since 2.5.1
			 * @param int $expiration The minimum time between two pings.
			 */
			$expiration = (int) \apply_filters( 'the_seo_framework_sitemap_throttle_s', HOUR_IN_SECONDS );

			//* @NOTE: Using legacy set_transient to prevent ping spam.
			\set_transient( $transient, $throttle, $expiration );
		}
	}

	/**
	 * Ping Google
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
	 * Ping Bing
	 *
	 * @since 2.2.9
	 */
	public function ping_bing() {
		$pingurl = 'http://www.bing.com/webmaster/ping.aspx?siteMap=' . urlencode( $this->get_sitemap_xml_url() );
		\wp_safe_remote_get( $pingurl, [ 'timeout' => 3 ] );
	}

	/**
	 * Ping Yandex
	 *
	 * @since 2.6.0
	 */
	public function ping_yandex() {
		$pingurl = 'http://blogs.yandex.ru/pings/?status=success&url=' . urlencode( $this->get_sitemap_xml_url() );
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
	 * @staticvar bool $flush
	 *
	 * @param bool $enqueue Whether to enqueue the flush or return its state.
	 * @return bool Whether to flush.
	 */
	public function enqueue_rewrite_activate( $enqueue = false ) {

		static $flush = null;

		if ( isset( $flush ) )
			return $flush;

		if ( $enqueue )
			return $flush = true;

		return false;
	}

	/**
	 * Enqueue rewrite flush for deactivation.
	 *
	 * @since 2.3.0
	 * @access private
	 * @staticvar bool $flush Only true
	 *
	 * @param bool $enqueue Whether to enqueue the flush or return its state.
	 * @return bool Whether to flush.
	 */
	public function enqueue_rewrite_deactivate( $enqueue = false ) {

		static $flush = null;

		if ( isset( $flush ) )
			return $flush;

		if ( $enqueue )
			return $flush = true;

		return false;
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
	 * @access private
	 * @global \WP_Rewrite $wp_rewrite
	 */
	public function flush_rewrite_rules_deactivation() {
		global $wp_rewrite;

		$wp_rewrite->init();

		unset( $wp_rewrite->extra_rules_top['sitemap\.xml$'] );

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

	/**
	 * Returns the sitemap post query limit.
	 *
	 * @since 3.1.0
	 *
	 * @return int The post limit
	 */
	protected function get_sitemap_post_limit() {
		/**
		 * @since 2.2.9
		 * @since 2.8.0 Increased to 1200 from 700.
		 * @since 3.1.0 Now returns an option value; it falls back to the default value if not set.
		 * @param int $total_post_limit
		 */
		return (int) \apply_filters(
			'the_seo_framework_sitemap_post_limit',
			$this->get_option( 'sitemap_query_limit' )
		);
	}
}
