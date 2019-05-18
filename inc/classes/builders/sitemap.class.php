<?php
/**
 * @package The_SEO_Framework\Classes\Builders
 * @subpackage The_SEO_Framework\Builders
 */

namespace The_SEO_Framework\Builders;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * Generates the sitemap.
 *
 * @since 3.3.0
 *
 * @access private
 */
class Sitemap {

	/**
	 * @var null|\The_SEO_Framework\Load
	 */
	private static $tsf = null;

	/**
	 * Unserializing instances of this object is forbidden.
	 */
	final protected function __wakeup() { }

	/**
	 * Cloning of this object is forbidden.
	 */
	final protected function __clone() { }

	/**
	 * Constructor.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		static::$tsf = \the_seo_framework();
	}

	/**
	 * Destructor.
	 *
	 * @since 3.3.0
	 */
	public function __destruct() {
		static::$tsf = null;
	}

	/**
	 * Prepares sitemap generation by raising the memory limit and fixing the timezone.
	 *
	 * @since 3.3.0
	 */
	public function prepare_generation() {

		\wp_raise_memory_limit( 'sitemap' );

		// Set timezone according to settings.
		static::$tsf->set_timezone();
	}

	/**
	 * Shuts down the sitemap generator.
	 *
	 * @since 3.3.0
	 */
	public function shutdown_generation() {
		static::$tsf->reset_timezone();
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
	 * @since 3.3.0 1. Now assesses all public post types, in favor of qubit options.
	 *              2. Improved performance by a factor of two+.
	 *              3. Renamed method from "generate sitemap" to "build sitemap content".
	 *              4. Moved to \The_SEO_Framework\Builders\Sitemap
	 *
	 * @return string The sitemap content.
	 */
	public function build_sitemap_content() {

		$content = '';

		$show_priority = (bool) static::$tsf->get_option( 'sitemaps_priority' );
		$show_modified = (bool) static::$tsf->get_option( 'sitemaps_modified' );

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

		foreach ( $this->generate_front_and_blog_url_items(
			compact( 'show_priority', 'show_modified' )
		) as $_values )
			$content .= $this->build_url_item( $_values );

		$post_types = array_diff( static::$tsf->get_supported_post_types(), [ 'attachment' ] );

		/**
		 * @since 3.3.0
		 * @param array $post_types The supported post types.
		 */
		$post_types = (array) \apply_filters( 'the_seo_framework_sitemap_supported_post_types', $post_types );

		$non_hierarchical_post_types = [];
		$hierarchical_post_types     = [];

		foreach ( $post_types as $_post_type ) {
			if ( \is_post_type_hierarchical( $_post_type ) ) {
				$hierarchical_post_types[] = $_post_type;
			} else {
				$non_hierarchical_post_types[] = $_post_type;
			}
		}

		$wp_query = new \WP_Query;
		$wp_query->init();
		$hierarchical_post_ids = $non_hierarchical_post_ids = [];

		if ( $hierarchical_post_types ) {
			$_exclude_ids = array_filter( [
				(int) \get_option( 'page_on_front' ),
				(int) \get_option( 'page_for_posts' ),
			] );

			/**
			 * @since 3.3.0
			 * @param array $args The query arguments.
			 */
			$args = \apply_filters( 'the_seo_framework_sitemap_hpt_query_args', [
				'posts_per_page'   => $this->get_sitemap_post_limit( true ) + count( $_exclude_ids ),
				'post_type'        => $hierarchical_post_types,
				'orderby'          => 'date',
				'order'            => 'ASC',
				'post_status'      => 'publish',
				'has_password'     => false,
				'fields'           => 'ids',
				'cache_results'    => false,
				'suppress_filters' => false,
				'no_found_rows'    => true,
			] );

			$wp_query->query = $wp_query->query_vars = $args;
			$hierarchical_post_ids = $wp_query->get_posts();

			$hierarchical_post_ids = array_diff( $hierarchical_post_ids, $_exclude_ids );
		}

		if ( $non_hierarchical_post_types ) {
			/**
			 * @since 3.3.0
			 * @param array $args The query arguments.
			 */
			$args = \apply_filters( 'the_seo_framework_sitemap_nhpt_query_args', [
				'posts_per_page'   => $this->get_sitemap_post_limit( false ),
				'post_type'        => $non_hierarchical_post_types,
				'orderby'          => 'lastmod',
				'order'            => 'DESC',
				'post_status'      => 'publish',
				'has_password'     => false,
				'fields'           => 'ids',
				'cache_results'    => false,
				'suppress_filters' => false,
				'no_found_rows'    => true,
			] );

			$wp_query->query = $wp_query->query_vars = $args;
			$non_hierarchical_post_ids = $wp_query->get_posts();
		}

		// Destroy class.
		$wp_query = null;

		// 49998 = 50000-2, max sitemap items.
		$max_items   = min( max( $this->get_sitemap_post_limit( false ), $this->get_sitemap_post_limit( true ) ), 49998 );
		$item_count  = 0;
		$total_items = count( $hierarchical_post_ids ) + count( $non_hierarchical_post_ids );

		foreach ( $this->generate_url_item_values(
			array_merge( $hierarchical_post_ids, $non_hierarchical_post_ids ),
			compact( 'show_priority', 'show_modified', 'total_items' )
		) as $_values ) {
			$content .= $this->build_url_item( $_values );
			if ( ++$item_count > $max_items ) break;
		}

		if ( \has_filter( 'the_seo_framework_sitemap_additional_urls' ) ) {
			/**
			 * @since 2.5.2
			 * @since 3.2.2 Invalid URLs are now skipped.
			 * @example return value: [ 'http://example.com' => [ 'lastmod' => '14-01-2018', 'priority' => 0.9 ] ]
			 * @param array $custom_urls : {
			 *    @param string (key) $url The absolute url to the page. : array {
			 *       @param string           $lastmod  UNIXTIME Last modified date, e.g. "2016-01-26 13:04:55"
			 *       @param float|int|string $priority URL Priority
			 *    }
			 * }
			 */
			$custom_urls = (array) \apply_filters( 'the_seo_framework_sitemap_additional_urls', [] );

			$timestamp_format = static::$tsf->get_timestamp_format();

			foreach ( $custom_urls as $url => $args ) {
				if ( ! is_array( $args ) ) {
					//* If there are no args, it's assigned as URL (per example)
					$url = $args;
				}

				$_url = \esc_url_raw( $url, [ 'http', 'https' ] );

				if ( ! $_url ) continue;

				$content .= "\t<url>\n";
				//* No need to use static vars
				$content .= "\t\t<loc>" . $_url . "</loc>\n";

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
		if ( $extend = (string) \apply_filters( 'the_seo_framework_sitemap_extend', '' ) )
			$content .= "\t" . $extend . "\n";

		return $content;
	}

	/**
	 * Generates front-and blog page sitemap items.
	 *
	 * @since 3.3.0
	 * @generator
	 *
	 * @param array $args The generator arguments.
	 * @yield array|void : {
	 *   string loc
	 *   string lastmod
	 *   string priority
	 * }
	 */
	protected function generate_front_and_blog_url_items( $args ) {

		$front_page_id = (int) \get_option( 'page_on_front' );
		$posts_page_id = (int) \get_option( 'page_for_posts' );

		if ( static::$tsf->has_page_on_front() ) {
			if ( $front_page_id && $this->is_post_included_in_sitemap( $front_page_id ) ) {
				// PHP7:...
				// yield from $this->generate_url_item_values()

				// Reset.
				$_values = [];
				$_values['loc'] = static::$tsf->create_canonical_url( [ 'id' => $front_page_id ] );

				if ( $args['show_modified'] ) {
					$post = \get_post( $front_page_id );
					$_values['lastmod'] = isset( $post->post_date_gmt ) ? $post->post_date_gmt : false;
				}

				if ( $args['show_priority'] ) {
					$_values['priority'] = '1.0';
				}

				yield $_values;
			}
			if ( $posts_page_id && $this->is_post_included_in_sitemap( $posts_page_id ) ) {
				// Reset.
				$_values = [];
				$_values['loc'] = static::$tsf->create_canonical_url( [ 'id' => $posts_page_id ] );

				if ( $args['show_modified'] ) {
					$latests_posts = \wp_get_recent_posts( [
						'numberposts'  => 1,
						'post_type'    => 'post',
						'post_status'  => 'publish',
						'has_password' => false,
						'orderby'      => 'post_date',
						'order'        => 'DESC',
						'offset'       => 0,
					], OBJECT );
					$latest_post   = isset( $latests_posts[0] ) ? $latests_posts[0] : null;
					$_lastmod_post = isset( $latest_post->post_date_gmt ) ? $latest_post->post_date_gmt : '0000-00-00 00:00:00';

					$post = \get_post( $posts_page_id );
					$_lastmod_blog = isset( $post->post_date_gmt ) ? $post->post_date_gmt : '0000-00-00 00:00:00';

					if ( strtotime( $_lastmod_post ) > strtotime( $_lastmod_blog ) ) {
						$_values['lastmod'] = $_lastmod_post;
					} else {
						$_values['lastmod'] = $_lastmod_blog;
					}
				}

				if ( $args['show_priority'] ) {
					$_values['priority'] = '1.0';
				}

				yield $_values;
			}
		} else {
			// Blog page as front.
			if ( $this->is_post_included_in_sitemap( 0 ) ) {
				// Reset.
				$_values = [];
				$_values['loc'] = static::$tsf->get_homepage_permalink();

				if ( $args['show_modified'] ) {
					$latests_posts = \wp_get_recent_posts( [
						'numberposts'  => 1,
						'post_type'    => 'post',
						'post_status'  => 'publish',
						'has_password' => false,
						'orderby'      => 'post_date',
						'order'        => 'DESC',
						'offset'       => 0,
					], OBJECT );
					$latest_post   = isset( $latests_posts[0] ) ? $latests_posts[0] : null;
					$_lastmod_post = isset( $latest_post->post_date_gmt ) ? $latest_post->post_date_gmt : '0000-00-00 00:00:00';

					$_values['lastmod'] = $_lastmod_post;
				}

				if ( $args['show_priority'] ) {
					$_values['priority'] = '1.0';
				}

				yield $_values;
			}
		}
	}

	/**
	 * Generates front-and blog page sitemap URL item values.
	 *
	 * @since 3.3.0
	 * @generator
	 * @iterator
	 *
	 * @param array $post_ids The post IDs to go over.
	 * @param array $args    The generator arguments.
	 * @param int   $count   The iteration count. Passed by reference.
	 * @yield array|void : {
	 *   string loc
	 *   string lastmod
	 *   string priority
	 *   int    total_items
	 * }
	 */
	protected function generate_url_item_values( $post_ids, $args, &$count = 0 ) {

		foreach ( $post_ids as $post_id ) {
			if ( $this->is_post_included_in_sitemap( $post_id ) ) {
				$_values = [];

				$_values['loc'] = static::$tsf->create_canonical_url( [ 'id' => $post_id ] );

				if ( $args['show_modified'] ) {
					$post = \get_post( $post_id );
					$_values['lastmod'] = isset( $post->post_date_gmt ) ? $post->post_date_gmt : '0000-00-00 00:00:00';
				}

				if ( $args['show_priority'] ) {
					$_values['priority'] = .949999 - ( $count / $args['total_items'] );
				}

				$count++;
				yield $_values;
			}
		}
	}

	/**
	 * Builds and returns a sitemap URL item.
	 *
	 * @since 3.3.0
	 * @staticvar string $timestamp_format
	 *
	 * @param array $args : {
	 *   string               $loc      : The item's URI.
	 *   string|void|false    $lastmod  : string if set and not '0000-00-00 00:00:00', false otherwise.
	 *   int|float|void|false $priority : int if set, false otherwise.
	 * }
	 * @return string The sitemap item.
	 */
	protected function build_url_item( $args ) {

		if ( empty( $args['loc'] ) ) return '';

		static $timestamp_format = null;
		$timestamp_format = $timestamp_format ?: static::$tsf->get_timestamp_format();

		return sprintf(
			"\t<url>\n%s\t</url>\n",
			vsprintf(
				'%s%s%s',
				[
					sprintf(
						"\t\t<loc>%s</loc>\n",
						$args['loc'] // Already escaped.
					),
					isset( $args['lastmod'] ) && '0000-00-00 00:00:00' !== $args['lastmod']
						? sprintf( "\t\t<lastmod>%s</lastmod>\n", static::$tsf->gmt2date( $timestamp_format, $args['lastmod'] ) )
						: '',
					isset( $args['priority'] ) && is_numeric( $args['priority'] )
						? sprintf( "\t\t<priority>%s</priority>\n", number_format( $args['priority'], 1, '.', ',' ) )
						: '',
				]
			)
		);
	}

	/**
	 * Determines if post is possibly included in the sitemap.
	 *
	 * This is a weak check, as the filter might not be present outside of the sitemap's scope.
	 * The URL also isn't checked, nor the position.
	 *
	 * @since 3.0.4
	 * @since 3.0.6 First filter value now works as intended.
	 * @since 3.1.0 1. Resolved a PHP notice when ID is 0, resulting in returning false-esque unintentionally.
	 *              2. Now accepts 0 in the filter.
	 * @since 3.3.0 1. Now tests qubit options.
	 *              2. Now tests for redirect settings.
	 *              3. First parameter can now be a post object.
	 *              4. If the first parameter is 0, it's now indicative of a home-as-blog page.
	 *              5. Moved to \The_SEO_Framework\Builders\Sitemap
	 *
	 * @param int $post_id The Post ID to check.
	 * @return bool True if included, false otherwise.
	 */
	public function is_post_included_in_sitemap( $post_id ) {

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

		return ! isset( $excluded[ $post_id ] ) && ! static::$tsf->is_robots_meta_noindex_set_by_args( [ 'id' => $post_id ] );
	}

	/**
	 * Returns the sitemap post query limit.
	 *
	 * @since 3.1.0
	 * @since 3.3.0 Moved to \The_SEO_Framework\Builders\Sitemap
	 *
	 * @param bool $hierarchical Whether the query is for hierarchical post types or not.
	 * @return int The post limit
	 */
	protected function get_sitemap_post_limit( $hierarchical = false ) {
		/**
		 * @since 2.2.9
		 * @since 2.8.0 Increased to 1200 from 700.
		 * @since 3.1.0 Now returns an option value; it falls back to the default value if not set.
		 * @since 3.3.0 1. The default is now 3000, from 1200.
		 *              2. Now passes a second parameter.
		 * @param int $total_post_limit
		 * @param bool $hierarchical Whether the query is for hierarchical post types or not.
		 */
		return (int) \apply_filters(
			'the_seo_framework_sitemap_post_limit',
			static::$tsf->get_option( 'sitemap_query_limit' ),
			$hierarchical
		);
	}
}
