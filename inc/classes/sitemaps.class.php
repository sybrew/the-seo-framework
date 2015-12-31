<?php
/**
 * The SEO Framework plugin
 * Copyright (C) 2015 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Class AutoDescription_Search
 *
 * Generates sitemap and outputs it.
 *
 * @since 2.2.9
 */
class AutoDescription_Sitemaps extends AutoDescription_Metaboxes {

	/**
	 * Maximum number of posts that show up in the sitemap.xml page.
	 *
	 * @since 2.2.9
	 *
	 * @var int Max Posts in Sitemap
	 */
	protected $max_posts;

	/**
	 * Checks for pretty permalinks.
	 *
	 * @since 2.2.9
	 *
	 * @var bool true if pretty
	 */
	protected $pretty_permalinks;

	/**
	 * Constructor, load parent constructor and set up caches.
	 */
	public function __construct() {
		parent::__construct();

		$this->max_posts = (int) apply_filters( 'the_seo_framework_sitemap_post_limit', 700 );

		// I'm not going to initialize my own rewrite engine. Causes too many problems.
		$this->pretty_permalinks = ( get_option( 'permalink_structure' ) != '' ) ? true : false;

		/**
		 * Add query strings to rewrite
		 */
		add_action( 'init', array( $this, 'rewrite_rule_sitemap' ), 1 );
		add_filter( 'query_vars', array( $this, 'enqueue_sitemap_query_vars' ), 1 );

		/**
		 * Adding a higher priority will cause a trailing slash to be added.
		 * We need to be in front of the queue to prevent this from happening.
		 */
		add_action( 'template_redirect', array( $this, 'maybe_output_sitemap' ), 1 );

		//* Edit the robots.txt file
		add_filter( 'robots_txt', array( $this, 'robots_txt' ), 10, 2 );

		//* Enqueue rewrite flush
		add_action( 'shutdown', array( $this, 'maybe_flush_rewrite' ), 999 );
	}

	/**
	 * Adds rewrite rule to WordPress
	 * This rule defines the sitemap.xml output
	 *
	 * @param bool $override add the rule anyway, regardless of setting.
	 *
	 * @since 2.2.9
	 */
	public function rewrite_rule_sitemap( $run = false ) {

		if ( (bool) $this->get_option( 'sitemaps_output' ) || $run ) {

			/**
			 * Don't do anything if a sitemap plugin is active.
			 * On sitemap plugin activation, the sitemap plugin should flush the
			 * rewrite rules. If it doesn't, then this plugin's sitemap will be called.
			 *
			 * @todo expand detection list.
			 */
			if ( $this->has_sitemap_plugin() )
				return;

			//* Adding rewrite rules only has effect when permalink structures are active.
			if ( $this->pretty_permalinks )
				add_rewrite_rule( 'sitemap\.xml$', 'index.php?the_seo_framework_sitemap=xml', 'top' );

			$this->wpmudev_domainmap_flush_fix( false );

		}
	}

	/**
	 * Register the_seo_framework_sitemap to wp_query
	 *
	 * @param array vars The WP_Query vars
	 *
	 * @since 2.2.9
	 */
	public function enqueue_sitemap_query_vars( $vars ) {

		if ( (bool) $this->get_option( 'sitemaps_output' ) )
			$vars[] = 'the_seo_framework_sitemap';

		return $vars;
	}

	/**
	 * Maybe Output sitemap.xml 'file' and header.
	 *
	 * @since 2.2.9
	 *
	 * @return void|mixed SiteMAp XML file.
	 */
	public function maybe_output_sitemap() {

		if ( (bool) $this->get_option( 'sitemaps_output' ) && $this->pretty_permalinks ) {
			global $current_blog;

			/**
			 * Don't do anything on a deleted or spam blog.
			 * There's nothing to find anyway. Multisite Only.
			 *
			 * @since 2.2.9
			 */
			if ( isset( $current_blog ) && ( $current_blog->spam == 1 || $current_blog->deleted == 1 ) )
				return;

			global $wp_query;

			if ( isset( $wp_query->query_vars['the_seo_framework_sitemap'] ) && $wp_query->query_vars['the_seo_framework_sitemap'] === 'xml' ) {
				// Don't let WordPress think this is 404.
				$wp_query->is_404 = false;

				return $this->output_sitemap();
			}
		}

	}

	/**
	 * Output sitemap.xml 'file' and header.
	 *
	 * @since 2.2.9
	 */
	protected function output_sitemap() {

		//* Fetch sitemap content.
		$xml_content = $this->get_sitemap_content();

		header( 'Content-type: text/xml; charset=utf-8' );

		echo $xml_content . "\r\n";

		// We're done now.
		die();
	}

	/**
	 * Output sitemap.xml content from transient.
	 *
	 * @since 2.2.9
	 *
	 * @return string Sitemap XML contents.
	 */
	protected function get_sitemap_content() {

		$timer_start = microtime( true );

		/**
		 * Re-use the variable, eliminating database requests
		 * @since 2.4.0
		 */
		$sitemap_content = get_transient( $this->sitemap_transient );

		if ( false == $sitemap_content ) {
			$cached_content = "\r\n<!-- Sitemap is generated for this view -->";
		} else {
			$cached_content = "\r\n<!-- Sitemap is served from cache -->";
		}

		$content  = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n";
		$content .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\r\n";
		$content .= $this->setup_sitemap_transient( $sitemap_content );
		$content .= '</urlset>';

		$content .= $cached_content;

		/**
		 * Output debug info.
		 *
		 * @since 2.3.7
		 */
		if ( defined( 'THE_SEO_FRAMEWORK_DEBUG' ) && THE_SEO_FRAMEWORK_DEBUG && function_exists( 'memory_get_usage' ) ) {
			$content .= "\r\n<!-- Site current usage: " . ( memory_get_usage() / 1024 / 1024 ) . " MB -->";
			$content .= "\r\n<!-- System current usage: " . ( memory_get_usage( true ) / 1024 / 1024 ) . " MB -->";
			$content .= "\r\n<!-- Sitemap generation time: " . ( number_format( microtime( true ) - $timer_start, 6 ) ) . " seconds -->";
		}

		return $content;
	}

	/**
	 * Create sitemap.xml content transient.
	 *
	 * @param string|bool $content required The sitemap transient content.
	 *
	 * @since 2.2.9
	 */
	public function setup_sitemap_transient( $sitemap_content ) {

		if ( false === $sitemap_content ) {
			//* Transient doesn't exist yet.
			$sitemap_content = $this->generate_sitemap();

			/**
			 * Transient expiration: 1 week.
			 * Keep the sitemap for at most 1 week.
			 *
			 * 60s * 60m * 24h * 7d
			 */
			$expiration = 60 * 60 * 24 * 7;

			set_transient( $this->sitemap_transient, $sitemap_content, $expiration );
		}

		return $sitemap_content;
	}

	/**
	 * Generate sitemap.xml content.
	 *
	 * @param bool $secure Only process when param is given.
	 *
	 * @since 2.2.9
	 */
	protected function generate_sitemap() {

		$content = '';

		/**
		 * Maximum pages and posts to fetch.
		 * A total of 2100, consisting of 3 times $max_posts
		 *
		 * Applies filters the_seo_framework_sitemap_pages_count : int max pages
		 * Applies filters the_seo_framework_sitemap_posts_count : int max posts
		 * Applies filters the_seo_framework_sitemap_custom_posts_count : int max posts
		 */
		$totalpages = (int) apply_filters( 'the_seo_framework_sitemap_pages_count', $this->max_posts );
		$totalposts = (int) apply_filters( 'the_seo_framework_sitemap_posts_count', $this->max_posts );
		$total_cpt_posts = (int) apply_filters( 'the_seo_framework_sitemap_custom_posts_count', $this->max_posts );

		//* Ascend by the date for normal pages. Older pages get to the top of the list.
		$latest_pages = $totalpages ? get_posts( 'numberposts=' . $totalpages . '&post_type=page&orderby=date&order=ASC&post_status=publish' ) : array();

		//* Descend by the date for posts. The latest posts get to the top of the list after pages.
		$latest_posts = $totalposts ? get_posts( 'numberposts=' . $totalposts . '&post_type=post&orderby=date&order=DESC&post_status=publish' ) : array();

		$cpt = array();

		if ( $total_cpt_posts ) {
			$post_page = (array) get_post_types( array( 'public' => true ) );
			foreach ( $post_page as $post_type ) {
				if ( $post_type != 'post' && $post_type != 'page' && $post_type != 'attachment' ) {
					if ( $this->post_type_supports_custom_seo( $post_type ) )
						$cpt[] = $post_type;
				}
			}
		}

		if ( $total_cpt_posts && !empty( $cpt ) ) {
			//* Descend by the date for CPTs. The latest posts get to the top of the list after pages.
			$args = array(
				'numberposts' => $total_cpt_posts,
				'post_type' => $cpt,
				'orderby' => 'date',
				'order' => 'DESC',
				'post_status' => 'publish'
			);

			$latest_cpt_posts = get_posts( $args );
		} else {
			$latest_cpt_posts = array();
		}

		/**
		 * Fetch the page/post modified options.
		 * We can't get specific on the home page, unfortunately.
		 */
		$page_lastmod = $this->get_option( 'sitemaps_modified' ) || $this->get_option( 'page_modify_time' ) ? true : false;
		$post_lastmod = $this->get_option( 'sitemaps_modified' ) || $this->get_option( 'post_modify_time' ) ? true : false;
		$home_lastmod = $this->get_option( 'sitemaps_modified' ) || $this->get_option( 'home_modify_time' ) ? true : false;

		/**
		 * Generation time output
		 *
		 * Applies filter the_seo_framework_sitemap_timestamp : bool
		 */
		$timestamp = (bool) apply_filters( 'the_seo_framework_sitemap_timestamp', true );

		if ( $timestamp )
			$content .= '<!-- Sitemap is generated on ' . current_time( "Y-m-d H:i:s" ) . ' -->' . "\r\n";

		$latest_pages_amount = (int) count( $latest_pages );

		if ( $latest_pages_amount > 0 ) {

			$page_on_front = (int) get_option( 'page_on_front' );
			$page_for_posts_option = (int) get_option( 'page_for_posts' );
			$page_show_on_front = ( 'page' == get_option( 'show_on_front' ) ) ? true : false;

			/**
			 * This can be heavy.
			 */
			foreach ( $latest_pages as $page ) {
				if ( isset( $page->ID ) ) {
					$page_id = $page->ID;

					//* Is this the front page?
					$page_is_front = $page_id == $page_on_front ? true : false;

					//* Fetch the noindex option, per page.
					$noindex = $this->get_custom_field( '_genesis_noindex', $page_id ) ? true : false;

					//* Continue if indexed.
					if ( ! $noindex ) {
						//* Don't add the posts page.
						if ( ! $page_show_on_front || ! ( $page_show_on_front && $page_id == $page_for_posts_option ) ) {

							$content .= "	<url>\r\n";
							// No need to use static vars.
							$content .= '		<loc>' . $this->the_url( '', $page_id, array( 'get_custom_field' => false, 'external' => true, 'post' => $page ) ) . "</loc>\r\n";

							// Keep it consistent. Only parse if page_lastmod is true.
							if ( $page_lastmod && ( ! $page_is_front || ( $home_lastmod && $page_is_front ) ) ) {
								$page_modified_gmt = $page->post_modified_gmt;

								if ( $page_modified_gmt !== '0000-00-00 00:00:00' )
									$content .= '		<lastmod>' . mysql2date( 'Y-m-d', $page_modified_gmt ) . "</lastmod>\r\n";
							}

							// Give higher priority to the home page.
							$priority_page = $page_is_front ? 1 : 0.9;

							$content .= '		<priority>' . number_format( $priority_page, 1 ) . "</priority>\r\n";
							$content .= "	</url>\r\n";

						}
					}
				}
			}
		}

		$latest_posts_amount = (int) count( $latest_posts );

		if ( $latest_posts_amount > 0 ) {

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

			if ( $latest_posts_amount > (int) 1 )
				$prioritydiff = 0.9 / $latest_posts_amount;

			// Keep it consistent. Only remove 0.1 when we only have a few posts.
			if ( $latest_posts_amount <= (int) 9 && $latest_posts_amount > (int) 1 )
				$prioritydiff = 0.1;

			/**
			 * This can be heavy.
			 */
			foreach ( $latest_posts as $post ) {
				if ( isset( $post->ID ) ) {
					$post_id = $post->ID;

					//* Fetch the noindex option, per page.
					$noindex = $this->get_custom_field( '_genesis_noindex', $post_id ) ? true : false;

					//* Continue if indexed
					if ( ! $noindex ) {

						$content .= "	<url>\r\n";
						// No need to use static vars
						$content .= '		<loc>' . $this->the_url( '', $post_id, array( 'get_custom_field' => false, 'external' => true, 'post' => $post ) ) . "</loc>\r\n";

						// Keep it consistent. Only parse if page_lastmod is true.
						if ( $post_lastmod ) {
							$post_modified_gmt = $post->post_modified_gmt;

							if ( $post_modified_gmt !== '0000-00-00 00:00:00' )
								$content .= '		<lastmod>' . mysql2date( 'Y-m-d', $post_modified_gmt ) . "</lastmod>\r\n";
						}

						$content .= '		<priority>' . number_format( $priority, 1 ) . "</priority>\r\n";
						$content .= "	</url>\r\n";

						// Lower the priority for the next pass.
						$priority = $priority - $prioritydiff;

						// Cast away negative numbers.
						$priority = $priority <= (int) 0 ? (int) 0 : (float) $priority;
					}
				}
			}
		}

		$latest_cpt_posts_amount = (int) count( $latest_cpt_posts );

		if ( $latest_cpt_posts_amount > 0 ) {
			/**
			 * Setting up priorities, with pages always being important.
			 *
			 * From there, older posts get a gradually lower priority. Down to 0.
			 * Differentiate with 1 / max posts (0 to $this->max_posts). With a 1 dot decimal.
			 */
			$priority_cpt = 0.9;

			$prioritydiff_cpt = 0;

			if ( $latest_cpt_posts_amount > (int) 1 )
				$prioritydiff_cpt = 0.9 / $latest_cpt_posts_amount;

			// Keep it consistent. Only remove 0.1 when we only have a few posts.
			if ( $latest_cpt_posts_amount <= (int) 9 && $latest_cpt_posts_amount > (int) 1 )
				$prioritydiff_cpt = 0.1;

			/**
			 * This can be heavy.
			 */
			foreach ( $latest_cpt_posts as $ctp_post ) {
				if ( isset( $ctp_post->ID ) ) {
					$post_id = $ctp_post->ID;

					//* Fetch the noindex option, per page.
					$noindex = $this->get_custom_field( '_genesis_noindex', $post_id ) ? true : false;

					//* Continue if indexed
					if ( ! $noindex ) {

						$content .= "	<url>\r\n";
						//* No need to use static vars
						$content .= '		<loc>' . $this->the_url( '', $post_id, array( 'get_custom_field' => false, 'external' => true, 'post' => $ctp_post ) ) . "</loc>\r\n";

						//* Keep it consistent. Only parse if page_lastmod is true.
						if ( $post_lastmod ) {
							$post_modified_gmt = $ctp_post->post_modified_gmt;

							//* Some CPT don't set modified time.
							if ( $post_modified_gmt !== '0000-00-00 00:00:00' )
								$content .= '		<lastmod>' . mysql2date( 'Y-m-d', $post_modified_gmt ) . "</lastmod>\r\n";
						}

						$content .= '		<priority>' . number_format( $priority_cpt, 1 ) . "</priority>\r\n";
						$content .= "	</url>\r\n";

						// Lower the priority for the next pass.
						$priority_cpt = $priority_cpt - $prioritydiff_cpt;

						// Cast away negative numbers.
						$priority_cpt = $priority_cpt <= (int) 0 ? (int) 0 : (float) $priority_cpt;
					}
				}
			}
		}

		return $content;
	}

	/**
	 * Ping search engines on post publish.
	 *
	 * @since 2.2.9
	 */
	public function ping_searchengines() {

		/**
		 * Don't ping if the blog isn't public.
		 *
		 * @since 2.3.1
		 */
		if ( ! $this->get_option( 'site_noindex' ) && get_option( 'blog_public' ) ) {
			global $blog_id;

			$blog_id = (string) $blog_id;

			$transient = 'tsf_throttle_ping_' . $blog_id;

			if ( false === get_transient( $transient ) ) {
				//* Transient doesn't exist yet.

				if ( $this->get_option( 'ping_google' ) )
					$this->ping_google();

				if ( $this->get_option( 'ping_bing' ) )
					$this->ping_bing();

				if ( $this->get_option( 'ping_yahoo' ) )
					$this->ping_yahoo();

				// Sorry I couldn't help myself.
				$throttle = 'Bert and Ernie are weird.';

				/**
				 * Limit the pinging to a maximum of 1 per hour.
				 * Transient expiration. 1 hour.
				 *
				 * 60s * 60m
				 */
				$expiration = 60 * 60;

				set_transient( $transient, $throttle, $expiration );
			}
		}

	}

	/**
	 * Ping Google
	 *
	 * @since 2.2.9
	 */
	public function ping_google() {
		$pingurl = 'http://www.google.com/webmasters/sitemaps/ping?sitemap=' . urlencode( $this->the_home_url_from_cache( true ) . 'sitemap.xml' );

		wp_remote_get( $pingurl );
	}

	/**
	 * Ping Bing
	 *
	 * @since 2.2.9
	 */
	public function ping_bing() {
		$pingurl = 'http://www.bing.com/webmaster/ping.aspx?siteMap=' . urlencode( $this->the_home_url_from_cache( true ) . 'sitemap.xml' );

		wp_remote_get( $pingurl );
	}

	/**
	 * Ping Yahoo
	 *
	 * @since 2.2.9
	 */
	public function ping_yahoo() {
		$pingurl = 'http://search.yahooapis.com/SiteExplorerService/V1/ping?sitemap=' . urlencode( $this->the_home_url_from_cache( true ) . 'sitemap.xml' );

		wp_remote_get( $pingurl );
	}

	/**
	 * Edits the robots.txt output
	 *
	 * Requires not to have a robots.txt file in the root directory
	 *
	 * @uses robots_txt filter located at WP core
	 *
	 * @since 2.2.9
	 *
	 * @global int $blog_id;
	 *
	 * @todo maybe combine with noindex/noarchive/(nofollow) -> only when object caching?
	 */
	public function robots_txt( $robots_txt = '', $public = '' ) {
		global $blog_id;

		/**
		 * Don't do anything if the blog isn't public
		 */
		if ( '0' == $public )
			return $robots_txt;

		$revision = '1';

		$cache_key = 'robots_txt_output_' . $revision . $blog_id;

		$output = $this->object_cache_get( $cache_key );
		if ( false === $output ) {
			$output = '';

			/**
			 * Apply filters the_seo_framework_robots_txt_pre & the_seo_framework_robots_txt_pro
			 * 		: Add custom cacheable lines.
			 *		: Don't forget to add line breaks ( "\r\n" | PHP_EOL )
			 */
			$pre = (string) apply_filters( 'the_seo_framework_robots_txt_pre', '' );
			$pro = (string) apply_filters( 'the_seo_framework_robots_txt_pro', '' );

			/**
			 * @TODO use the_url_from_cache() ?
			 *
			 * @see https://architech.hostmijnpagina.nl/robots.txt
			 * Will fix robots :D
			 * @var_dump()
			 * FOUND BUG: Mapped WordPress installations robots.txt will not be compatible. =/
			 */
			$home_url = $this->the_home_url_from_cache();
			$parse_url = parse_url( $home_url );
			$path = ! empty( $site_url['path'] ) ? $site_url['path'] : '';

			$output .= $pre;
			//* Output defaults
			$output .= "User-agent: *\r\n";
			$output .= "Disallow: $path/wp-admin/\r\n";
			$output .= "Allow: $path/wp-admin/admin-ajax.php\r\n";

			/**
			 * Prevents query indexing
			 * @since 2.2.9
			 *
			 * Applies filters the_seo_framework_robots_allow_queries : Wether to allow queries for robots.
			 * @since 2.4.3
			 */
			if ( ! (bool) apply_filters( 'the_seo_framework_robots_allow_queries', false ) )
				$output .= "Disallow: $path/*?*\r\n";

			$output .= $pro;

			if ( $this->get_option( 'sitemaps_output') && (bool) $this->get_option( 'sitemaps_robots' ) ) {
				//* Add whitespace before sitemap.
				$output .= "\r\n";

				//* Add sitemap full url
				$output .= 'Sitemap: ' . trailingslashit( $home_url ) . "sitemap.xml\r\n";
			}

			$this->object_cache_set( $cache_key, $output, 86400 );
		}

		/**
		 * Override robots with output.
		 * @since 2.4.4
		 */
		$robots_txt = $output;

		return $robots_txt;
	}

	/**
	 * Add and Flush rewrite rules on plugin activation.
	 *
	 * @since 2.2.9
	 * Do not return anything. Just be here. Thanks.
	 */
	public static function flush_rewrite_rules_activation() {
		global $wp_rewrite;

		// This function is called statically.
		$the_seo_framework = the_seo_framework();

		$the_seo_framework->rewrite_rule_sitemap( true );

		$wp_rewrite->init();
		$wp_rewrite->flush_rules( true );
	}

	/**
	 * Flush rewrite rules on plugin deactivation.
	 *
	 * @since 2.2.9
	 * Do not return anything. Just be here. Thanks.
	 */
	public static function flush_rewrite_rules_deactivation() {
		global $wp_rewrite;

		$wp_rewrite->init();

		// Works as intended.
		unset( $wp_rewrite->extra_rules_top['sitemap\.xml$'] );

		$wp_rewrite->flush_rules( true );
	}

	/**
	 * Enqueue rewrite flush for activation.
	 *
	 * @staticvar bool $flush Only true
	 *
	 * @since 2.3.0
	 */
	public function enqueue_rewrite_activate( $enqueue = false ) {

		static $flush = null;

		if ( isset( $flush ) )
			return (bool) $flush;

		if ( $enqueue )
			return $flush = true;

		return false;
	}

	/**
	 * Enqueue rewrite flush for deactivation.
	 *
	 * @staticvar bool $flush Only true
	 *
	 * @since 2.3.0
	 */
	public function enqueue_rewrite_deactivate( $enqueue = false ) {

		static $flush = null;

		if ( isset( $flush ) )
			return (bool) $flush;

		if ( $enqueue )
			return $flush = true;

		return false;
	}

	/**
	 * Flush rewrite rules based on static variables.
	 *
	 * @since 2.3.0
	 */
	public function maybe_flush_rewrite() {

		if ( $this->enqueue_rewrite_activate() )
			$this->flush_rewrite_rules_activation();

		if ( $this->enqueue_rewrite_deactivate() )
			$this->flush_rewrite_rules_deactivation();

	}

	/**
	 * Add the WPMUdev Domain Mapping rules again. And flush them on init.
	 * Domain Mapping bugfix.
	 *
	 * @param bool $options_saved : If we're in admin and the sanitiation function runs.
	 *
	 * Runs a flush and updates the site option to "true".
	 * When the site option is set to true, it not flush again on init.
	 *
	 * If $options_saved is true, it will not check for the init action hook and continue,
	 * So it will flush the next time on init.
	 *
	 * @since 2.3.0
	 */
	public function wpmudev_domainmap_flush_fix( $options_saved = false ) {

		if ( $this->pretty_permalinks && $this->is_domainmapping_active() ) {
			if ( current_action() == 'init' || $options_saved ) {
				if ( class_exists( 'Domainmap_Module_Cdsso' ) && defined( 'Domainmap_Module_Cdsso::SSO_ENDPOINT' ) ) {
					add_rewrite_endpoint( Domainmap_Module_Cdsso::SSO_ENDPOINT, EP_ALL );

					//* Force extra flush on init.
					if ( class_exists( 'domain_map' ) ) {
						$key = 'the_seo_framework_wpmdev_dm' . get_current_blog_id() . '_extra_flush';

						if ( $options_saved ) {
							if ( get_site_option( $key ) ) {
								//* Prevent flushing multiple times.
								update_site_option( $key, false );
							}
						} else {
							if ( ! get_site_option( $key ) ) {
								//* Prevent flushing multiple times.
								update_site_option( $key, true );

								//* Now flush
								flush_rewrite_rules();
							}
						}
					}
				}
			}
		}

	}

}
