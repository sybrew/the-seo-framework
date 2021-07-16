<?php
/**
 * @package The_SEO_Framework\Classes\Builders\Sitemap\Base
 * @subpackage The_SEO_Framework\Sitemap
 */

namespace The_SEO_Framework\Builders;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2021 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Generates the base sitemap.
 *
 * @since 4.0.0
 *
 * @access private
 */
class Sitemap_Base extends Sitemap {

	/**
	 * @since 4.1.2
	 * @var bool
	 */
	public $base_is_regenerated = false;

	/**
	 * @since 4.1.2
	 * @var bool
	 */
	public $base_is_prerendering = false;

	/**
	 * Returns the base sitemap's storage transient name.
	 *
	 * @since 4.1.2
	 *
	 * @return string.
	 */
	public function base_get_sitemap_store_key() {
		return static::$tsf->get_sitemap_transient_name();
	}

	/**
	 * Generates the sitemap, and stores the generated content in the database.
	 *
	 * Note that this will work sporadically with translation plugins; however,
	 * it will not conflict, since a unique caching key is generated for each language.
	 * TODO consider expanding this feature for multilingual sites?
	 *
	 * @since 4.1.2
	 */
	public function prerender_sitemap() {

		$bridge = \The_SEO_Framework\Bridges\Sitemap::get_instance();

		if ( ! $bridge->sitemap_cache_enabled() ) return;

		// Don't prerender if the sitemap is already generated.
		if ( false !== static::$tsf->get_transient( $this->base_get_sitemap_store_key() ) ) return;

		set_time_limit( (int) max( ini_get( 'max_execution_time' ), 3 * MINUTE_IN_SECONDS ) );

		// Somehow, the 'base' key is unavailable, the database failed, or a lock is already in place. Either way, bail.
		if ( ! $bridge->lock_sitemap( 'base' ) ) return;

		$this->prepare_generation();
		$this->base_is_prerendering = true;

		static::$tsf->set_transient(
			$this->base_get_sitemap_store_key(),
			$this->build_sitemap(),
			WEEK_IN_SECONDS
		);

		$bridge->unlock_sitemap( 'base' );

		$this->shutdown_generation();
		$this->base_is_regenerated = true;
	}

	/**
	 * Returns the generated sitemap. Also stores it in the database when caching is enabled.
	 *
	 * @since 4.1.2
	 * @abstract
	 *
	 * @param string $sitemap_id The sitemap ID. Expected either 'base' or 'index'--or otherwise overwriten via the API.
	 * @return string The sitemap content.
	 */
	public function generate_sitemap( $sitemap_id = 'base' ) {

		$bridge           = \The_SEO_Framework\Bridges\Sitemap::get_instance();
		$_caching_enabled = $bridge->sitemap_cache_enabled();

		$sitemap_content = $_caching_enabled ? static::$tsf->get_transient( $this->base_get_sitemap_store_key() ) : false;

		if ( false === $sitemap_content ) {
			$this->prepare_generation();
			$_caching_enabled && $bridge->lock_sitemap( $sitemap_id );

			$sitemap_content = $this->build_sitemap();

			$this->shutdown_generation();
			$this->base_is_regenerated = true;

			if ( $_caching_enabled ) {
				static::$tsf->set_transient(
					$this->base_get_sitemap_store_key(),
					$sitemap_content,
					WEEK_IN_SECONDS
				);
				$bridge->unlock_sitemap( $sitemap_id );
			}
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
	 *              2. Now uses WordPress's built-in memory raiser function, with "context" sitemap.
	 * @since 4.0.0 1. Now assesses all public post types, in favor of qubit options.
	 *              2. Improved performance by a factor of two+.
	 *              3. Renamed method from "generate_sitemap" to abstract extension "build_sitemap".
	 *              4. Moved to \The_SEO_Framework\Builders\Sitemap_Base
	 * @abstract
	 *
	 * @return string The sitemap content.
	 */
	public function build_sitemap() {

		$content = '';
		$count   = 0;

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
					(
						$this->base_is_prerendering
							/* translators: %s = timestamp */
							? \esc_html__( 'Sitemap is prerendered on %s', 'autodescription' )
							/* translators: %s = timestamp */
							: \esc_html__( 'Sitemap is generated on %s', 'autodescription' )
					),
					\current_time( 'Y-m-d H:i:s \G\M\T' )
				)
			) . "\n";

		foreach ( $this->generate_front_and_blog_url_items(
			compact( 'show_priority', 'show_modified' ),
			$count
		) as $_values ) {
			$content .= $this->build_url_item( $_values );
		}

		$post_types = array_diff( static::$tsf->get_supported_post_types(), [ 'attachment' ] );

		/**
		 * @since 4.0.0
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

			$_hierarchical_posts_limit = $this->get_sitemap_post_limit( true );

			/**
			 * @since 4.0.0
			 * @param array $args The query arguments.
			 */
			$_args = (array) \apply_filters(
				'the_seo_framework_sitemap_hpt_query_args',
				[
					'posts_per_page' => $_hierarchical_posts_limit + \count( $_exclude_ids ),
					'post_type'      => $hierarchical_post_types,
					'orderby'        => 'date',
					'order'          => 'ASC',
					'post_status'    => 'publish',
					'has_password'   => false,
					'fields'         => 'ids',
					'cache_results'  => false,
					'no_found_rows'  => true,
				]
			);

			if ( $_args['post_type'] ) {
				$wp_query->query = $wp_query->query_vars = $_args;

				$hierarchical_post_ids = array_diff( $wp_query->get_posts(), $_exclude_ids );

				// Stop confusion: trim query to set value (by one or two, depending on whether the homepage and blog are included).
				// This is ultimately redundant, but it'll stop support requests by making the input value more accurate.
				if ( \count( $hierarchical_post_ids ) > $_hierarchical_posts_limit ) {
					array_splice( $hierarchical_post_ids, $_hierarchical_posts_limit );
				}
			}
		}

		if ( $non_hierarchical_post_types ) {
			/**
			 * @since 4.0.0
			 * @param array $args The query arguments.
			 */
			$_args = (array) \apply_filters(
				'the_seo_framework_sitemap_nhpt_query_args',
				[
					'posts_per_page' => $this->get_sitemap_post_limit( false ),
					'post_type'      => $non_hierarchical_post_types,
					'orderby'        => 'lastmod',
					'order'          => 'DESC',
					'post_status'    => 'publish',
					'has_password'   => false,
					'fields'         => 'ids',
					'cache_results'  => false,
					'no_found_rows'  => true,
				]
			);

			if ( $_args['post_type'] ) {
				$wp_query->query = $wp_query->query_vars = $_args;

				$non_hierarchical_post_ids = $wp_query->get_posts();
			}
		}

		// Destroy query instance.
		$wp_query = null;

		/**
		 * @since 4.1.0
		 * @param int[] $_items                    The post IDs that will be parsed in the sitemap.
		 *                                         When it totals for more than 49998 items, they'll be spliced.
		 * @param int[] $hierarchical_post_ids     The post IDs from hierarchical post types.
		 * @param int[] $non_hierarchical_post_ids The post IDs from non-hierarchical post types.
		 */
		$_items      = (array) \apply_filters_ref_array(
			'the_seo_framework_sitemap_items',
			[
				array_merge( $hierarchical_post_ids, $non_hierarchical_post_ids ),
				$hierarchical_post_ids,
				$non_hierarchical_post_ids,
			]
		);
		$total_items = \count( $_items );

		// 49998 = 50000-2 (home+blog), max sitemap items.
		if ( $total_items > 49998 ) array_splice( $_items, 49998 );
		// We could also calculate the sitemap length (may not be above 10 MB)...
		// ...but that'd mean each entry must be at least 200 chars long on avg. Good luck with that.

		foreach ( $this->generate_url_item_values(
			$_items,
			compact( 'show_priority', 'show_modified', 'total_items' ),
			$count
		) as $_values ) {
			$content .= $this->build_url_item( $_values );
		}

		if ( \has_filter( 'the_seo_framework_sitemap_additional_urls' ) ) {
			foreach ( $this->generate_additional_base_urls(
				compact( 'show_priority', 'show_modified', 'count' ),
				$count
			) as $_values ) {
				$content .= $this->build_url_item( $_values );
			}
		}

		/**
		 * NOTE: This filter is slower than `the_seo_framework_sitemap_additional_urls`, because it's not a generator.
		 * If you only need to add a few URLs (fewer than 500), then you can safely use this.
		 *
		 * @since 2.5.2
		 * @since 4.0.0 Added $args parameter.
		 * @param string $extend Custom sitemap extension. Must be escaped.
		 * @param array $args : {
		 *   bool $show_priority : Whether to display priority
		 *   bool $show_modified : Whether to display modified date.
		 *   int  $total_itemns  : Estimate: The total sitemap items before adding additional URLs.
		 * }
		 */
		$extend = (string) \apply_filters_ref_array(
			'the_seo_framework_sitemap_extend',
			[
				'',
				compact( 'show_priority', 'show_modified', 'count' ),
			]
		);

		if ( $extend ) {
			$content .= "\t" . $extend . "\n";
		}

		return $content;
	}

	/**
	 * Generates front-and blog page sitemap items.
	 *
	 * @since 4.0.0
	 * @generator
	 *
	 * @param array $args  The generator arguments.
	 * @param int   $count The iteration count. Passed by reference.
	 * @yield array|void : {
	 *   string loc
	 *   string lastmod
	 *   string priority
	 * }
	 */
	protected function generate_front_and_blog_url_items( $args, &$count = 0 ) {

		$front_page_id = (int) \get_option( 'page_on_front' );
		$posts_page_id = (int) \get_option( 'page_for_posts' );

		if ( static::$tsf->has_page_on_front() ) {
			if ( $front_page_id && $this->is_post_included_in_sitemap( $front_page_id ) ) {
				// TODO use this instead @ PHP7
				// yield from $this->generate_url_item_values()

				// Reset.
				$_values        = [];
				$_values['loc'] = static::$tsf->create_canonical_url(
					[
						'id'       => $front_page_id,
						'taxonomy' => '',
					]
				);

				if ( $args['show_modified'] ) {
					$post               = \get_post( $front_page_id );
					$_values['lastmod'] = isset( $post->post_modified_gmt ) ? $post->post_modified_gmt : false;
				}

				if ( $args['show_priority'] ) {
					$_values['priority'] = '1.0';
				}

				++$count;
				yield $_values;
			}
			if ( $posts_page_id && $this->is_post_included_in_sitemap( $posts_page_id ) ) {
				// Reset.
				$_values        = [];
				$_values['loc'] = static::$tsf->create_canonical_url(
					[
						'id'       => $posts_page_id,
						'taxonomy' => '',
					]
				);

				if ( $args['show_modified'] ) {
					$latests_posts = \wp_get_recent_posts(
						[
							'numberposts'  => 1,
							'post_type'    => 'post',
							'post_status'  => 'publish',
							'has_password' => false,
							'orderby'      => 'post_date',
							'order'        => 'DESC',
							'offset'       => 0,
						],
						\OBJECT
					);
					$latest_post   = isset( $latests_posts[0] ) ? $latests_posts[0] : null;
					$_publish_post = isset( $latest_post->post_date_gmt ) ? $latest_post->post_date_gmt : '0000-00-00 00:00:00';

					$post          = \get_post( $posts_page_id );
					$_lastmod_blog = isset( $post->post_modified_gmt ) ? $post->post_modified_gmt : '0000-00-00 00:00:00';

					if ( strtotime( $_publish_post ) > strtotime( $_lastmod_blog ) ) {
						$_values['lastmod'] = $_publish_post;
					} else {
						$_values['lastmod'] = $_lastmod_blog;
					}

					/**
					 * @since 4.1.1
					 * @param string $lastmod The lastmod time in SQL notation (`Y-m-d H:i:s`). Expected to explicitly follow that format!
					 */
					$_values['lastmod'] = (string) \apply_filters( 'the_seo_framework_sitemap_blog_lastmod', $_values['lastmod'] );
				}

				if ( $args['show_priority'] ) {
					$_values['priority'] = '1.0';
				}

				++$count;
				yield $_values;
			}
		} else {
			// Blog page as front.
			if ( $this->is_post_included_in_sitemap( 0 ) ) {
				// Reset.
				$_values        = [];
				$_values['loc'] = static::$tsf->get_homepage_permalink();

				if ( $args['show_modified'] ) {
					$latests_posts = \wp_get_recent_posts(
						[
							'numberposts'  => 1,
							'post_type'    => 'post',
							'post_status'  => 'publish',
							'has_password' => false,
							'orderby'      => 'post_date',
							'order'        => 'DESC',
							'offset'       => 0,
						],
						\OBJECT
					);

					$_values['lastmod'] = isset( $latests_posts[0]->post_date_gmt ) ? $latests_posts[0]->post_date_gmt : '0000-00-00 00:00:00';

					/**
					 * @since 4.1.1
					 * @param string $lastmod The lastmod time in SQL notation (`Y-m-d H:i:s`). Expected to explicitly follow that format!
					 */
					$_values['lastmod'] = (string) \apply_filters( 'the_seo_framework_sitemap_blog_lastmod', $_values['lastmod'] );
				}

				if ( $args['show_priority'] ) {
					$_values['priority'] = '1.0';
				}

				++$count;
				yield $_values;
			}
		}
	}

	/**
	 * Generates sitemap URL item values.
	 *
	 * @since 4.0.0
	 * @since 4.1.1 Now clears WordPress's post cache every time an item is generated.
	 * @generator
	 * @iterator
	 *
	 * @param iterable $post_ids The post IDs to go over.
	 * @param array    $args    The generator arguments.
	 * @param int      $count   The iteration count. Passed by reference.
	 * @yield array|void : {
	 *   string loc
	 *   string lastmod
	 *   string priority
	 * }
	 */
	protected function generate_url_item_values( $post_ids, $args, &$count = 0 ) {

		static $using_external_object_cache = null;

		$using_external_object_cache = isset( $using_external_object_cache ) ? $using_external_object_cache : (bool) \wp_using_ext_object_cache();

		foreach ( $post_ids as $post_id ) {
			// Setup post cache, which is also used in is_post_included_in_sitemap() and create_canonical_url().
			$post = \get_post( $post_id );

			if ( $this->is_post_included_in_sitemap( $post_id ) ) {
				$_values        = [];
				$_values['loc'] = static::$tsf->create_canonical_url(
					[
						'id'       => $post_id,
						'taxonomy' => '',
					]
				);

				if ( $args['show_modified'] )
					$_values['lastmod'] = isset( $post->post_modified_gmt ) ? $post->post_modified_gmt : '0000-00-00 00:00:00';

				if ( $args['show_priority'] ) {
					// Add at least 1 to prevent going negative. We added 8 extra (= 9) to smoothen the slope.
					$_values['priority'] = .949999 - ( $count / ( $args['total_items'] + 9 ) );
				}

				++$count;
				yield $_values;
			}

			$using_external_object_cache or \clean_post_cache( $post );
		}
	}

	/**
	 * Builds and returns a sitemap URL item.
	 *
	 * @since 4.0.0
	 * @since 4.1.1 Now uses `create_xml_entry()` to parse the XML.
	 *
	 * @param array $args : {
	 *   string               $loc      : The item's URI.
	 *   string|void|false    $lastmod  : string if set and not '0000-00-00 00:00:00', false otherwise. Expected to be GMT.
	 *   int|float|void|false $priority : int if set, false otherwise.
	 * }
	 * @return string The sitemap item.
	 */
	protected function build_url_item( $args ) {

		if ( empty( $args['loc'] ) ) return '';

		static $timestamp_format = null;

		if ( ! isset( $timestamp_format ) )
			$timestamp_format = static::$tsf->get_timestamp_format();

		$xml = [
			'loc' => $args['loc'], // Already escaped.
		];

		if ( isset( $args['lastmod'] ) && '0000-00-00 00:00:00' !== $args['lastmod'] )
			$xml['lastmod'] = static::$tsf->gmt2date( $timestamp_format, $args['lastmod'] );

		if ( isset( $args['priority'] ) && is_numeric( $args['priority'] ) )
			$xml['priority'] = number_format( $args['priority'], 1, '.', ',' );

		return $this->create_xml_entry( [ 'url' => $xml ], 1 );
	}

	/**
	 * Retrieves additional URLs and builds items from them.
	 *
	 * @since 4.0.0
	 * @since 4.0.1 : 1. Converted to generator and iterator. Therefore, renamed function.
	 *                2. Now actually does something.
	 * @generator
	 * @iterator
	 *
	 * @param array $args  : {
	 *   bool $show_priority : Whether to display priority
	 *   bool $show_modified : Whether to display modified date.
	 *   int  $count         : The total sitemap items before adding additional URLs.
	 * }
	 * @param int   $count The iteration count. Passed by reference.
	 * @yield array|void : {
	 *   string loc
	 *   string lastmod
	 *   string priority
	 * }
	 */
	protected function generate_additional_base_urls( $args, &$count = 0 ) {

		/**
		 * @since 2.5.2
		 * @since 3.2.2 Invalid URLs are now skipped.
		 * @since 4.0.0 Added $args parameter.
		 * @example return value: [ 'http://example.com' => [ 'lastmod' => '14-01-2018', 'priority' => 0.9 ] ]
		 * @param array $custom_urls : {
		 *    string (key) $url The absolute url to the page. : array {
		 *       string           $lastmod  : UNIXTIME <GMT+0> Last modified date, e.g. "2016-01-26 13:04:55"
		 *       float|int|string $priority : URL Priority
		 *    }
		 * }
		 * @param array $args : {
		 *   bool $show_priority : Whether to display priority
		 *   bool $show_modified : Whether to display modified date.
		 *   int  $count         : Estimate: The total sitemap items before adding additional URLs.
		 * }
		 */
		$custom_urls = (array) \apply_filters( 'the_seo_framework_sitemap_additional_urls', [], $args );

		foreach ( $custom_urls as $url => $values ) {
			if ( ! \is_array( $values ) ) {
				// If there are no args, it's assigned as URL (per example)
				$url = $values;
			}

			// Test if URL is valid.
			if ( ! \esc_url_raw( $url, [ 'https', 'http' ] ) ) continue;

			// Reset.
			$_values        = [];
			$_values['loc'] = $url;

			if ( $args['show_modified'] )
				$_values['lastmod'] = ! empty( $values['lastmod'] ) ? $values['lastmod'] : '0000-00-00 00:00:00';

			if ( $args['show_priority'] )
				$_values['priority'] = ! empty( $values['priority'] ) ? $values['priority'] : 0.9;

			++$count;
			yield $_values;
		}
	}
}
