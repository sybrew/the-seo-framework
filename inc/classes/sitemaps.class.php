<?php
/**
 * @package The_SEO_Framework\Classes
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
	 * Checks if sitemap is being output.
	 *
	 * @since 2.5.2
	 *
	 * @var bool true if sitemap is being output.
	 */
	protected $doing_sitemap = false;

	/**
	 * Constructor, load parent constructor and set up caches.
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Determines whether we can output sitemap or not based on options.
	 *
	 * @since 2.6.0
	 * @staticvar bool $cache
	 *
	 * @return bool
	 */
	public function can_run_sitemap() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		/**
		 * Don't do anything on a deleted or spam blog.
		 * There's nothing to find anyway. Multisite Only.
		 */
		return $cache = $this->pretty_permalinks && $this->is_option_checked( 'sitemaps_output' ) && false === $this->current_blog_is_spam_or_deleted();
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
				// Don't let WordPress think this is 404.
				$wp_query->is_404 = false;

				$this->doing_sitemap = true;

				/**
				 * Applies filters 'the_seo_framework_sitemap_post_limit' : int
				 * @since 2.2.9
				 * @since 2.8.0 Increased to 1200 from 700.
				 */
				$this->max_posts = (int) \apply_filters( 'the_seo_framework_sitemap_post_limit', 1200 );

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

		$remove = array(
			'wp_filter' => array(
				'wp_head',
				'admin_head',
				'the_content',
				'the_content_feed',
				'the_excerpt_rss',
				'wp_footer',
				'admin_footer',
			),
			'wp_registered_widgets',
			'wp_registered_sidebars',
			'wp_registered_widget_updates',
			'wp_registered_widget_controls',
			'_wp_deprecated_widgets_callbacks',
			'posts',
			'shortcode_tags',
		);

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
	 */
	protected function output_sitemap() {

		//* Remove output, if any.
		$this->clean_reponse_header();

		if ( ! headers_sent() )
			header( 'Content-type: text/xml; charset=utf-8' );

		//* Fetch sitemap content and add trailing line. Already escaped internally.
		$this->output_sitemap_content();
		echo "\r\n";

		// We're done now.
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
		$sitemap_content = $this->is_option_checked( 'cache_sitemap' ) ? $this->get_transient( $this->sitemap_transient ) : false;

		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n";
		echo $this->get_sitemap_xsl_stylesheet_tag();

		/**
		 * Output debug prior output.
		 * @since 2.8.0
		 */
		if ( $this->the_seo_framework_debug ) {
			echo '<!-- Site estimated peak usage prior to generation: ' . number_format( memory_get_peak_usage() / 1024 / 1024, 3 ) . ' MB -->' . "\r\n";
			echo '<!-- System estimated peak usage prior to generation: ' . number_format( memory_get_peak_usage( true ) / 1024 / 1024, 3 ) . ' MB -->' . "\r\n";
		}

		echo $this->get_sitemap_urlset_open_tag();
		echo $this->setup_sitemap( $sitemap_content );
		echo $this->get_sitemap_urlset_close_tag();

		if ( false === $sitemap_content ) {
			echo "\r\n" . '<!-- ' . \esc_html__( 'Sitemap is generated for this view', 'autodescription' ) . ' -->';
		} else {
			echo "\r\n" . '<!-- ' . \esc_html__( 'Sitemap is served from cache', 'autodescription' ) . ' -->';
		}

		/**
		 * Output debug info.
		 * @since 2.3.7
		 */
		if ( $this->the_seo_framework_debug ) {
			echo "\r\n" . '<!-- Site estimated peak usage: ' . number_format( memory_get_peak_usage() / 1024 / 1024, 3 ) . ' MB -->';
			echo "\r\n" . '<!-- System estimated peak usage: ' . number_format( memory_get_peak_usage( true ) / 1024 / 1024, 3 ) . ' MB -->';
			echo "\r\n" . '<!-- Freed memory prior to generation: ' . number_format( $this->clean_up_globals( true ) / 1024, 3 ) . ' kB -->';
			echo "\r\n" . '<!-- Sitemap generation time: ' . ( number_format( microtime( true ) - $timer_start, 6 ) ) . ' seconds -->';
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

		$schemas = array(
			'xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9',
			'xmlns:xhtml' => 'http://www.w3.org/1999/xhtml',
			'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
			'xsi:schemaLocation' => array(
				'http://www.sitemaps.org/schemas/sitemap/0.9',
				'http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd',
			),
		);

		/**
		 * Applies filters 'the_seo_framework_sitemap_schemas' : array
		 * @since 2.8.0
		 * @param array $schems The schema list. URLs are expected to be escaped.
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

		return $urlset . "\r\n";
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
	 *
	 * @return string The sitemap XSL location tag.
	 */
	public function get_sitemap_xsl_stylesheet_tag() {

		if ( $this->is_option_checked( 'sitemap_styles' ) )
			return sprintf( '<?xml-stylesheet type="text/xsl" href="%s"?>', \esc_url( $this->get_sitemap_xsl_url() ) ) . "\r\n";

		return '';
	}

	/**
	 * Returns the stylesheet XSL location URL.
	 *
	 * @since 2.8.0
	 *
	 * @return string URL location of the XSL stylesheet. Unescaped.
	 */
	public function get_sitemap_xsl_url() {
		global $wp_rewrite;

		if ( $wp_rewrite->using_index_permalinks() ) {
			$xsl = \home_url( '/index.php/sitemap.xsl' );
		} elseif ( $wp_rewrite->using_permalinks() ) {
			$xsl = \home_url( '/sitemap.xsl' );
		} else {
			$xsl = \home_url( '/?the_seo_framework_sitemap-xsl=true' );
		}

		return $xsl;
	}

	/**
	 * Sitemap XSL stylesheet output.
	 *
	 * @since 2.8.0
	 */
	public function output_sitemap_xsl_stylesheet() {

		$this->clean_reponse_header();

		if ( ! headers_sent() ) {
			header( 'Content-type: text/xsl; charset=utf-8' );
			header( 'Cache-Control: max-age=1800' );
		}

		$this->get_view( 'sitemap/xsl-stylesheet' );
		exit;
	}

	/**
	 * Create sitemap.xml content transient.
	 *
	 * @since 2.6.0
	 *
	 * @param string|bool $content required The sitemap transient content.
	 * @return string The sitemap content.
	 */
	public function setup_sitemap( $sitemap_content ) {

		if ( false === $sitemap_content ) {
			//* Transient doesn't exist yet.
			$sitemap_content = $this->generate_sitemap();

			/**
			 * Transient expiration: 1 week.
			 * Keep the sitemap for at most 1 week.
			 */
			$expiration = WEEK_IN_SECONDS;

			$this->set_transient( $this->sitemap_transient, $sitemap_content, $expiration );
		}

		return $sitemap_content;
	}

	/**
	 * Generate sitemap.xml content.
	 *
	 * @since 2.2.9
	 * @since 2.8.0 Now adjusts memory limit when possible.
	 *
	 * @return string The sitemap content.
	 */
	protected function generate_sitemap() {

		\wp_is_ini_value_changeable( 'memory_limit' ) and @ini_set( 'memory_limit', WP_MAX_MEMORY_LIMIT );

		$content = '';

		/**
		 * Applies filters the_seo_framework_sitemap_exclude_ids : array of id's
		 *
		 * @since 2.5.2
		 * @since 2.8.0 : No longer accepts '0' as entry.
		 */
		$excluded = (array) \apply_filters( 'the_seo_framework_sitemap_exclude_ids', array() );

		if ( empty( $excluded ) ) {
			$excluded = '';
		} else {
			$excluded = array_flip( $excluded );
		}

		/**
		 * Maximum pages and posts to fetch.
		 * A total of 2100, consisting of 3 times $max_posts
		 *
		 * Applies filters the_seo_framework_sitemap_pages_count : int max pages
		 * Applies filters the_seo_framework_sitemap_posts_count : int max posts
		 * Applies filters the_seo_framework_sitemap_custom_posts_count : int max posts
		 */
		$totalpages = (int) \apply_filters( 'the_seo_framework_sitemap_pages_count', $this->max_posts );
		$totalposts = (int) \apply_filters( 'the_seo_framework_sitemap_posts_count', $this->max_posts );
		$total_cpt_posts = (int) \apply_filters( 'the_seo_framework_sitemap_custom_posts_count', $this->max_posts );

		$latest_pages = array();
		$latest_posts = array();
		$latest_cpt_posts = array();
		$cpt = array();

		//* Sets timezone according to WordPress settings.
		$this->set_timezone();
		$timestamp_format = $this->get_option( 'sitemap_timestamps' );
		$timestamp_format = '1' === $timestamp_format ? 'Y-m-d\TH:iP' : 'Y-m-d';

		/**
		 * Fetch the page/post modified options.
		 * We can't get specific on the home page, unfortunately.
		 */
		$sitemaps_modified = $this->is_option_checked( 'sitemaps_modified' );
		if ( $sitemaps_modified ) {
			$page_lastmod = true;
			$post_lastmod = true;
			$home_lastmod = true;
		} else {
			$page_lastmod = $this->is_option_checked( 'page_modify_time' );
			$post_lastmod = $this->is_option_checked( 'post_modify_time' );
			$home_lastmod = $page_lastmod ?: $this->is_option_checked( 'home_modify_time' );
		}

		/**
		 * Generation time output
		 *
		 * Applies filter the_seo_framework_sitemap_timestamp : bool
		 */
		$timestamp = (bool) \apply_filters( 'the_seo_framework_sitemap_timestamp', true );

		if ( $timestamp )
			$content .= '<!-- ' . \esc_html__( 'Sitemap is generated on', 'autodescription' ) . ' ' . \current_time( 'Y-m-d H:i:s' ) . ' -->' . "\r\n";

		$wp_query = new \WP_Query;
		$wp_query->init();
		$query = $wp_query->query = $wp_query->query_vars = array();

		if ( $totalpages ) {
			//* Ascend by the date for normal pages. Older pages get to the top of the list.
			$defaults = array(
				'posts_per_page'   => $totalpages,
				'post_type'        => 'page',
				'orderby'          => 'date',
				'order'            => 'ASC',
				'post_status'      => 'publish',
				'fields'           => 'ids',
				'cache_results'    => false,
				'suppress_filters' => true,
				'no_found_rows'    => true,
			);

			/**
			 * Applies filters 'the_seo_framework_sitemap_pages_query_args' : array
			 *
			 * @since 2.8.0
			 *
			 * @param array $args The new query arguments.
			 * @param array $defaults The default query arguments
			 */
			$args = \apply_filters( 'the_seo_framework_sitemap_pages_query_args', array(), $defaults );

			$wp_query->query = $wp_query->query_vars = \wp_parse_args( $args, $defaults );
			$latest_pages = $wp_query->get_posts();
		}
		$latest_pages_amount = (int) count( $latest_pages );

		if ( $latest_pages_amount > 0 ) :

			$id_on_front = $this->has_page_on_front() ? (int) \get_option( 'page_on_front' ) : (int) \get_option( 'page_for_posts' );

			/**
			 * This can be heavy.
			 */
			foreach ( $latest_pages as $page_id ) :

				$page = \get_post( $page_id );

				if ( isset( $page->ID ) ) :
					$page_id = $page->ID;

					if ( '' === $excluded || empty( $excluded[ $page_id ] ) ) {

						//* Fetch the noindex option, per page.
						$indexed = ! $this->get_custom_field( '_genesis_noindex', $page_id );

						//* Continue if indexed.
						if ( $indexed ) {
							//* Is this the front page?
							$page_is_front = $page_id === $id_on_front;

							$content .= "\t<url>\r\n";
							if ( $page_is_front ) {
								$content .= "\t\t<loc>" . $this->the_url( '', array( 'get_custom_field' => false, 'external' => true, 'home' => true ) ) . "</loc>\r\n";
							} else {
								$content .= "\t\t<loc>" . $this->the_url( '', array( 'get_custom_field' => false, 'external' => true, 'post' => $page, 'id' => $page_id ) ) . "</loc>\r\n";
							}

							// Keep it consistent. Only parse if page_lastmod is true.
							if ( $page_lastmod || ( $page_is_front && $home_lastmod ) ) {
								$page_modified_gmt = $page->post_modified_gmt;

								if ( '0000-00-00 00:00:00' !== $page_modified_gmt )
									$content .= "\t\t<lastmod>" . $this->gmt2date( $timestamp_format, $page_modified_gmt ) . "</lastmod>\r\n";
							}

							// Give higher priority to the home page.
							$priority_page = $page_is_front ? 1 : 0.9;

							$content .= "\t\t<priority>" . number_format( $priority_page, 1 ) . "</priority>\r\n";
							$content .= "\t</url>\r\n";
						}
					}
				endif;
			endforeach;

			//* Free memory.
			unset( $latest_pages );
		endif;

		if ( $totalposts ) {
			//* Descend by the date for posts. The latest posts get to the top of the list after pages.
			$defaults = array(
				'posts_per_page'   => $totalposts,
				'post_type'        => 'post',
				'orderby'          => 'date',
				'order'            => 'DESC',
				'post_status'      => 'publish',
				'fields'           => 'ids',
				'cache_results'    => false,
				'suppress_filters' => true,
				'no_found_rows'    => true,
			);

			/**
			 * Applies filters 'the_seo_framework_sitemap_posts_query_args' : array
			 *
			 * @since 2.8.0
			 *
			 * @param array $args The new query arguments.
			 * @param array $defaults The default query arguments
			 */
			$args = \apply_filters( 'the_seo_framework_sitemap_posts_query_args', array(), $defaults );

			$wp_query->query = $wp_query->query_vars = \wp_parse_args( $args, $defaults );
			$latest_posts = $wp_query->get_posts();
		}
		$latest_posts_amount = (int) count( $latest_posts );

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

			if ( $latest_posts_amount > (int) 1 )
				$prioritydiff = 0.9 / $latest_posts_amount;

			// Keep it consistent. Only remove 0.1 when we only have a few posts.
			if ( $latest_posts_amount <= (int) 9 && $latest_posts_amount > (int) 1 )
				$prioritydiff = 0.1;

			/**
			 * This can be heavy.
			 */
			foreach ( $latest_posts as $post_id ) :

				$post = \get_post( $post_id );

				if ( isset( $post->ID ) ) :
					$post_id = $post->ID;

					if ( '' === $excluded || empty( $excluded[ $post_id ] ) ) {

						//* Fetch the noindex option, per page.
						$indexed = ! $this->get_custom_field( '_genesis_noindex', $post_id );

						//* Continue if indexed
						if ( $indexed ) {

							$content .= "\t<url>\r\n";
							// No need to use static vars
							$content .= "\t\t<loc>" . $this->the_url( '', array( 'get_custom_field' => false, 'external' => true, 'post' => $post, 'id' => $post_id ) ) . "</loc>\r\n";

							// Keep it consistent. Only parse if page_lastmod is true.
							if ( $post_lastmod ) {
								$post_modified_gmt = $post->post_modified_gmt;

								if ( '0000-00-00 00:00:00' !== $post_modified_gmt )
									$content .= "\t\t<lastmod>" . $this->gmt2date( $timestamp_format, $post_modified_gmt ) . "</lastmod>\r\n";
							}

							$content .= "\t\t<priority>" . number_format( $priority, 1 ) . "</priority>\r\n";
							$content .= "\t</url>\r\n";

							// Lower the priority for the next pass.
							$priority = $priority - $prioritydiff;

							// Cast away negative numbers.
							$priority = $priority <= (int) 0 ? (int) 0 : (float) $priority;
						}
					}
				endif;
			endforeach;

			//* Free memory.
			unset( $latest_posts );
		endif;

		if ( $total_cpt_posts ) :
			$post_page = (array) \get_post_types( array( 'public' => true ) );

			/**
			 * Applies filters Array the_seo_framework_sitemap_exclude_cpt : Excludes these CPT
			 * @since 2.5.0
			 */
			$excluded_cpt = (array) \apply_filters( 'the_seo_framework_sitemap_exclude_cpt', array() );

			$not_cpt = array( 'post', 'page', 'attachment' );

			foreach ( $post_page as $post_type ) {
				if ( false === in_array( $post_type, $not_cpt, true ) ) {
					if ( empty( $excluded_cpt ) || false === in_array( $post_type, $excluded_cpt, true ) ) {
						if ( $this->post_type_supports_custom_seo( $post_type ) ) {
							$cpt[] = $post_type;
						}
					}
				}
			}

			if ( $cpt ) {
				//* Descend by the date for CPTs. The latest posts get to the top of the list after pages.
				$defaults = array(
					'posts_per_page'   => $total_cpt_posts,
					'post_type'        => $cpt,
					'orderby'          => 'date',
					'order'            => 'DESC',
					'post_status'      => 'publish',
					'fields'           => 'ids',
					'cache_results'    => false,
					'suppress_filters' => true,
					'no_found_rows'    => true,
				);

				/**
				 * Applies filters 'the_seo_framework_sitemap_posts_query_args' : array
				 *
				 * @since 2.8.0
				 *
				 * @param array $args The new query arguments.
				 * @param array $defaults The default query arguments
				 */
				$args = \apply_filters( 'the_seo_framework_sitemap_cpt_query_args', array(), $defaults );

				$wp_query->query = $wp_query->query_vars = \wp_parse_args( $args, $defaults );
				$latest_cpt_posts = $wp_query->get_posts();
			}
		endif;
		$latest_cpt_posts_amount = (int) count( $latest_cpt_posts );

		if ( $latest_cpt_posts_amount > 0 ) :

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
			foreach ( $latest_cpt_posts as $ctp_post_id ) :

				$ctp_post = \get_post( $ctp_post_id );

				if ( isset( $ctp_post->ID ) ) :
					$cpt_id = $ctp_post->ID;

					if ( '' === $excluded || empty( $excluded[ $cpt_id ] ) ) {

						//* Fetch the noindex option, per page.
						$indexed = ! $this->get_custom_field( '_genesis_noindex', $cpt_id );

						//* Continue if indexed
						if ( $indexed ) {

							$content .= "\t<url>\r\n";
							//* No need to use static vars
							$content .= "\t\t<loc>" . $this->the_url( '', array( 'get_custom_field' => false, 'external' => true, 'post' => $ctp_post, 'id' => $cpt_id ) ) . "</loc>\r\n";

							//* Keep it consistent. Only parse if page_lastmod is true.
							if ( $post_lastmod ) {
								$cpt_modified_gmt = $ctp_post->post_modified_gmt;

								//* Some CPT don't set modified time.
								if ( '0000-00-00 00:00:00' !== $cpt_modified_gmt )
									$content .= "\t\t<lastmod>" . $this->gmt2date( $timestamp_format, $cpt_modified_gmt ) . "</lastmod>\r\n";
							}

							$content .= "\t\t<priority>" . number_format( $priority_cpt, 1 ) . "</priority>\r\n";
							$content .= "\t</url>\r\n";

							// Lower the priority for the next pass.
							$priority_cpt = $priority_cpt - $prioritydiff_cpt;

							// Cast away negative numbers.
							$priority_cpt = $priority_cpt <= (int) 0 ? (int) 0 : (float) $priority_cpt;
						}
					}
				endif;
			endforeach;

			//* Free memory.
			unset( $latest_cpt_posts );
		endif;

		/**
		 * Applies filters the_seo_framework_sitemap_additional_urls : {
		 * 		@param string url The absolute url to the page. : {
		 * 			@param string lastmod UNIXTIME Last modified date, e.g. "2016-01-26 13:04:55"
		 * 			@param float|int|string priority URL Priority
		 *		}
		 * }
		 *
		 * @since 2.5.2
		 */
		$custom_urls = (array) \apply_filters( 'the_seo_framework_sitemap_additional_urls', array() );

		if ( $custom_urls ) {

			//* Force ent2ncr to run, regardless of filters.
			\remove_all_filters( 'pre_ent2ncr', false );

			foreach ( $custom_urls as $url => $args ) {

				if ( ! is_array( $args ) ) {
					//* If there are no args, it's assigned as URL (per example)
					$url = $args;
				}

				$content .= "\t<url>\r\n";
				//* No need to use static vars
				$content .= "\t\t<loc>" . \ent2ncr( \esc_url_raw( $url ) ) . "</loc>\r\n";

				if ( isset( $args['lastmod'] ) && $args['lastmod'] ) {
					$content .= "\t\t<lastmod>" . \mysql2date( $timestamp_format, $args['lastmod'], false ) . "</lastmod>\r\n";
				}

				if ( isset( $args['priority'] ) && $args['priority'] ) {
					$priority = $args['priority'];
				} else {
					$priority = 0.9;
				}

				$content .= "\t\t<priority>" . number_format( $priority, 1 ) . "</priority>\r\n";
				$content .= "\t</url>\r\n";
			}
		}

		/**
		 * Applies filters the_seo_framework_sitemap_extend : string
		 * @since 2.5.2
		 */
		$extend = (string) \apply_filters( 'the_seo_framework_sitemap_extend', '' );

		if ( $extend )
			$content .= "\t" . $extend . "\r\n";

		//* Reset timezone to default.
		$this->reset_timezone();

		return $content;
	}

	/**
	 * Ping search engines on post publish.
	 *
	 * @since 2.2.9
	 * @global int $blog_id
	 *
	 * @return void Early if blog is not public.
	 */
	public function ping_searchengines() {

		if ( $this->is_option_checked( 'site_noindex' ) || $this->is_blog_public() )
			return;

		$blog_id = (string) $GLOBALS['blog_id'];

		$transient = 'tsf_throttle_ping_' . $blog_id;

		//* NOTE: Use legacy get_transient to prevent ping spam.
		if ( false === \get_transient( $transient ) ) {
			//* Transient doesn't exist yet.

			if ( $this->is_option_checked( 'ping_google' ) )
				$this->ping_google();

			if ( $this->is_option_checked( 'ping_bing' ) )
				$this->ping_bing();

			if ( $this->is_option_checked( 'ping_yandex' ) )
				$this->ping_yandex();

			// Sorry, I couldn't help myself.
			$throttle = 'Bert and Ernie are weird.';

			/**
			 * Limit the pinging to a maximum of 1 per hour.
			 * Transient expiration. 1 hour.
			 *
			 * Applies filters the_seo_framework_sitemap_throttle_s
			 * @since 2.5.1
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
	 */
	public function ping_google() {
		$pingurl = 'http://www.google.com/webmasters/sitemaps/ping?sitemap=' . urlencode( $this->the_home_url_from_cache( true ) . 'sitemap.xml' );
		\wp_safe_remote_get( $pingurl, array( 'timeout' => 3 ) );
	}

	/**
	 * Ping Bing
	 *
	 * @since 2.2.9
	 */
	public function ping_bing() {
		$pingurl = 'http://www.bing.com/webmaster/ping.aspx?siteMap=' . urlencode( $this->the_home_url_from_cache( true ) . 'sitemap.xml' );
		\wp_safe_remote_get( $pingurl, array( 'timeout' => 3 ) );
	}

	/**
	 * Ping Yandex
	 *
	 * @since 2.6.0
	 */
	public function ping_yandex() {
		$pingurl = 'http://blogs.yandex.ru/pings/?status=success&url=' . urlencode( $this->the_home_url_from_cache( true ) . 'sitemap.xml' );
		\wp_safe_remote_get( $pingurl, array( 'timeout' => 3 ) );
	}

	/**
	 * Initialize and flush rewrite rules.
	 *
	 * @since 2.6.0
	 * @since 2.8.0 : Deprecated?
	 * @access private
	 * @deprecated silently.
	 */
	public function flush_rewrite_rules() {
		global $wp_rewrite;

		$this->rewrite_rule_sitemap();

		\flush_rewrite_rules();
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
	 * @staticvar bool $flush Only true
	 *
	 * @param bool $enqueue Whether to enqueue the flush or return its state.
	 *
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
	 *
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
	 * Enqueue rewrite flush for deactivation.
	 *
	 * @since 2.6.0
	 * @access private
	 * @staticvar bool $flush Only true
	 *
	 * @param bool $enqueue Whether to enqueue the flush or return its state.
	 *
	 * @return bool Whether to flush.
	 */
	public function enqueue_rewrite_flush_other( $enqueue = false ) {

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

		if ( $this->enqueue_rewrite_flush_other() )
			$this->flush_rewrite_rules();

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
	 * @global object $wp_rewrite
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
			$retval = array(
				'main'   => '#333',
				'accent' => '#00cd98',
			);
		} else {
			$main = $this->s_color_hex( $this->get_option( 'sitemap_color_main' ) );
			$accent = $this->s_color_hex( $this->get_option( 'sitemap_color_accent' ) );

			$options = array(
				'main'   => $main ? '#' . $main : '',
				'accent' => $accent ? '#' . $accent : '',
			);

			$options = array_filter( $options );

			$retval = array_merge( $this->get_sitemap_colors( true ), $options );
		}

		return $retval;
	}
}
