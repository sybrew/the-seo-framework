<?php
/**
 * @package The_SEO_Framework\Classes\Sitemap\Optimized\Base
 * @subpackage The_SEO_Framework\Sitemap
 */

namespace The_SEO_Framework\Sitemap\Optimized;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\{
	Data,
	Data\Filter\Escape,
	Meta,
	Sitemap,
};
use \The_SEO_Framework\Helper\{
	Format\Time,
	Post_Type,
	Query,
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
 * Generates the base sitemap.
 *
 * @since 4.0.0
 * @since 4.2.0 Renamed from `\The_SEO_Framework\Builders\Sitemap_Base`.
 * @since 5.0.0 Moved from `\The_SEO_Framework\Builders\Sitemap`.
 *
 * @access private
 */
class Base extends Main {

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
	 * Generates the sitemap, and stores the generated content in the database.
	 *
	 * Note that this will work sporadically with translation plugins; however,
	 * it will not conflict, since a unique caching key is generated for each language.
	 * TODO consider expanding this feature for multilingual sites?
	 *
	 * @hook tsf_sitemap_cron_hook_before 10
	 * @since 4.1.2
	 * @since 4.2.1 Now no longer lowers the PHP execution time limit from unlimited to 3 minutes.
	 * @since 5.0.0 1. Can now prerender sitemap on a $sitemap_id basis.
	 *              2. Is now static.
	 *
	 * @param string $sitemap_id The sitemap ID to prerender.
	 * @return void Early if the sitemap's already generated.
	 */
	public static function prerender_sitemap( $sitemap_id = 'base' ) {

		if ( ! Sitemap\Cache::is_sitemap_cache_enabled() ) return;

		// Don't prerender if the sitemap is already generated.
		if ( false !== Sitemap\Cache::get_cached_sitemap_content( $sitemap_id ) ) return;

		$ini_max_execution_time = (int) ini_get( 'max_execution_time' );
		if ( 0 !== $ini_max_execution_time )
			set_time_limit( max( $ini_max_execution_time, 3 * \MINUTE_IN_SECONDS ) );

		// Somehow, the 'base' key is unavailable, the database failed, or a lock is already in place. Either way, bail.
		if ( ! Sitemap\Lock::lock_sitemap( $sitemap_id ) ) return;

		$sitemap_base = new self(); // TODO make static? We needn't more than one instance, right?

		$sitemap_base->prepare_generation();
		$sitemap_base->base_is_prerendering = true;

		Sitemap\Cache::cache_sitemap_content( $sitemap_base->build_sitemap(), $sitemap_id );

		Sitemap\Lock::unlock_sitemap( $sitemap_id );

		$sitemap_base->shutdown_generation();
		$sitemap_base->base_is_regenerated = true;
	}

	/**
	 * Returns the generated sitemap. Also stores it in the database when caching is enabled.
	 *
	 * @since 4.1.2
	 * @since 5.0.0 Can now generate cache on a $sitemap_id basis.
	 * @abstract
	 *
	 * @param string $sitemap_id The sitemap ID. Expected either 'base' or 'index'--or otherwise overwritten via the API.
	 * @return string The sitemap content.
	 */
	public function generate_sitemap( $sitemap_id = 'base' ) {

		$_caching_enabled = Sitemap\Cache::is_sitemap_cache_enabled();

		$sitemap_content = $_caching_enabled
			? Sitemap\Cache::get_cached_sitemap_content( $sitemap_id )
			: false;

		if ( false === $sitemap_content ) {

			$this->prepare_generation();
			$_caching_enabled && Sitemap\Lock::lock_sitemap( $sitemap_id );

			$sitemap_content = $this->build_sitemap();

			$this->shutdown_generation();
			$this->base_is_regenerated = true;

			if ( $_caching_enabled ) {
				Sitemap\Cache::cache_sitemap_content( $sitemap_content, $sitemap_id );
				Sitemap\Lock::unlock_sitemap( $sitemap_id );
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
	 *              4. Moved to \The_SEO_Framework\Builders\Sitemap\Base
	 * @override
	 * @slow The queried results are not stored in WP Post's cache, which would allow direct access
	 *       to all values of the post (if requested). This is because we're using
	 *       `'fields' => 'ids'` instead of `'fields' => 'all'`. However, this would fill RAM
	 *       linearly: at 1000 posts, we'd hit 28MB already, 10 000 would be ~280MB, exceeding max.
	 * @link <https://w.org/support/topic/sitemap-and-memory-exhaustion/#post-13331896>
	 *
	 * @return string The sitemap content.
	 */
	public function build_sitemap() {

		/**
		 * @since 4.2.7
		 * @param \The_SEO_Framework\Sitemap\Optimized\Base
		 */
		\do_action( 'the_seo_framework_build_sitemap_base', $this );

		$content         = '';
		$this->url_count = 0;

		$show_modified = (bool) Data\Plugin::get_option( 'sitemaps_modified' );

		/**
		 * @since 2.2.9
		 * @param bool $timestamp Whether to display the timestamp.
		 */
		$timestamp = (bool) \apply_filters( 'the_seo_framework_sitemap_timestamp', true );

		if ( $timestamp )
			$content .= \sprintf(
				"<!-- %s -->\n",
				\sprintf(
					$this->base_is_prerendering
						/* translators: %s = timestamp */
						? \esc_html__( 'Sitemap is prerendered on %s', 'autodescription' )
						/* translators: %s = timestamp */
						: \esc_html__( 'Sitemap is generated on %s', 'autodescription' ),
					\current_time( 'Y-m-d H:i:s \G\M\T' ),
				),
			);

		foreach ( $this->generate_front_and_blog_url_items(
			[ 'show_modified' => $show_modified ],
		) as $_values ) {
			$content .= $this->build_url_item( $_values );
		}

		$post_types = array_diff( Post_Type::get_all_supported(), [ 'attachment' ] );

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

			$_hierarchical_posts_limit = Sitemap\Utils::get_sitemap_post_limit( 'hierarchical' );

			/**
			 * @since 4.0.0
			 * @since 5.0.5 1. Now sets orderby to 'lastmod', from 'date'.
			 *              2. Now sets order to 'DESC', from 'ASC'.
			 * @param array $args The query arguments.
			 * @link <https://w.org/support/topic/sitemap-and-memory-exhaustion/#post-13331896>
			 */
			$_args = (array) \apply_filters(
				'the_seo_framework_sitemap_hpt_query_args',
				[
					'posts_per_page' => $_hierarchical_posts_limit + \count( $_exclude_ids ),
					'post_type'      => $hierarchical_post_types,
					'orderby'        => 'lastmod',
					'order'          => 'DESC',
					'post_status'    => 'publish',
					'has_password'   => false,
					'fields'         => 'ids',
					'cache_results'  => false,
					'no_found_rows'  => true,
				],
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
					// phpcs:ignore, WordPress.WP.PostsPerPage -- This is a sitemap, it will be slow.
					'posts_per_page' => Sitemap\Utils::get_sitemap_post_limit( 'nonhierarchical' ),
					'post_type'      => $non_hierarchical_post_types,
					'orderby'        => 'lastmod',
					'order'          => 'DESC',
					'post_status'    => 'publish',
					'has_password'   => false,
					'fields'         => 'ids',
					'cache_results'  => false,
					'no_found_rows'  => true,
				],
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
		$_items      = (array) \apply_filters(
			'the_seo_framework_sitemap_items',
			array_merge( $hierarchical_post_ids, $non_hierarchical_post_ids ),
			$hierarchical_post_ids,
			$non_hierarchical_post_ids,
		);
		$total_items = \count( $_items );

		// 49998 = 50000-2 (home+blog), max sitemap items.
		if ( $total_items > 49998 ) array_splice( $_items, 49998 );
		// We could also calculate the sitemap length (may not be above 10 MB)...
		// ...but that'd mean each entry must be at least 200 chars long on avg. Good luck with that.

		foreach ( $this->generate_url_item_values(
			$_items,
			[
				'show_modified' => $show_modified,
				'total_items'   => $total_items,
			],
		) as $_values ) {
			$content .= static::build_url_item( $_values );
		}

		/**
		 * NOTE to devs: Use this filter if you want to let the generator build the string (lower memory usage).
		 * This filter also keeps track toward the sitemap limit via $count.
		 */
		if ( \has_filter( 'the_seo_framework_sitemap_additional_urls' ) ) {
			foreach ( $this->generate_additional_base_urls(
				[
					'show_modified' => $show_modified,
					'count'         => $this->url_count,
				]
			) as $_values ) {
				$content .= static::build_url_item( $_values );
			}
		}

		/**
		 * This filter accepts a simple string, which may strain the memory usage if not generated (via co-routine).
		 *
		 * @since 2.5.2
		 * @since 4.0.0 Added $args parameter.
		 * @since 4.2.0 No longer forwards the 'show_priority' index in the second ($args) parameter.
		 * @param string $extend Custom sitemap extension. Must be escaped.
		 * @param array $args {
		 *     The sitemap extension arguments.
		 *
		 *     @type bool $show_modified Whether to display modified date.
		 *     @type int  $count         The total sitemap items before adding additional URLs.
		 * }
		 */
		$extend = (string) \apply_filters(
			'the_seo_framework_sitemap_extend',
			'',
			[
				'show_modified' => $show_modified,
				'count'         => $this->url_count,
			],
		);

		if ( $extend )
			$content .= "\t$extend\n";

		return $content;
	}

	/**
	 * Generates front-and blog page sitemap items.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Removed second parameter `&$count`.
	 * @since 5.0.5 1. Now tests for is_protected, is_draft, and is_post_included_in_sitemap for the front page.
	 *              2. Now tests for is_protected and is_draft for the posts page.
	 * @generator
	 *
	 * @param array $args {
	 *     The generator arguments.
	 *
	 *     @type bool $show_modified Whether to display the last modified date.
	 * }
	 * @yield array|void : {
	 *     @type string $loc      The URI of the page.
	 *     @type string $lastmod  The page's last modified date.
	 * }
	 */
	protected function generate_front_and_blog_url_items( $args ) {

		if ( Query\Utils::has_page_on_front() ) {
			$front_page_id = (int) \get_option( 'page_on_front' );
			$posts_page_id = (int) \get_option( 'page_for_posts' );

			// Assert 404 here; these are queried separately from all other entries that have these tests builtin.
			if (
				   $front_page_id // Might not be assigned.
				&& ! Data\Post::is_protected( $front_page_id )
				&& ! Data\Post::is_draft( $front_page_id )
				&& Sitemap\Utils::is_post_included_in_sitemap( $front_page_id )
			) {
				yield from $this->generate_url_item_values(
					[ $front_page_id ],
					$args,
				);
			}

			// Assert 404 here; these are queried separately from all other entries that have these tests builtin.
			if (
				   $posts_page_id // Might not be assigned.
				&& ! Data\Post::is_protected( $posts_page_id )
				&& ! Data\Post::is_draft( $posts_page_id )
				&& Sitemap\Utils::is_post_included_in_sitemap( $posts_page_id )
			) {
				foreach ( $this->generate_url_item_values(
					[ $posts_page_id ],
					$args,
				) as $_values ) {
					if ( $_values['loc'] && $args['show_modified'] ) {
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
							\OBJECT,
						);
						$_publish_post = $latests_posts[0]->post_date_gmt ?? '0000-00-00 00:00:00';
						$_lastmod_blog = $_values['lastmod']; // Inferred from generator generate_url_item_values()

						/**
						 * @since 4.1.1
						 * @param string $lastmod The lastmod time in SQL notation (`Y-m-d H:i:s`). Expected to explicitly follow that format!
						 */
						$_values['lastmod'] = (string) \apply_filters(
							'the_seo_framework_sitemap_blog_lastmod',
							strtotime( $_publish_post ) > strtotime( $_lastmod_blog )
								? $_publish_post
								: $_lastmod_blog,
						);
					}

					yield $_values;
				}
			}
		} else {
			// Blog page as front. Unique; cannot go through generate_url_item_values().
			if ( Sitemap\Utils::is_post_included_in_sitemap( 0 ) ) {
				// Reset.
				$_values        = [];
				$_values['loc'] = Meta\URI::get_bare_front_page_url();

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
						\OBJECT,
					);

					/**
					 * @since 4.1.1
					 * @param string $lastmod The lastmod time in SQL notation (`Y-m-d H:i:s`). Expected to explicitly follow that format!
					 */
					$_values['lastmod'] = (string) \apply_filters(
						'the_seo_framework_sitemap_blog_lastmod',
						$latests_posts[0]->post_date_gmt ?? '0000-00-00 00:00:00',
					);
				}

				++$this->url_count;
				yield $_values;
			}
		}
	}

	/**
	 * Generates sitemap URL item values.
	 *
	 * @since 4.0.0
	 * @since 4.1.1 Now clears WordPress's post cache every time an item is generated.
	 * @since 5.0.0 Removed second parameter `&$count`.
	 * @generator
	 * @iterator
	 *
	 * @param int[] $post_ids The post IDs to go over.
	 * @param array $args     {
	 *     The generator arguments.
	 *
	 *     @type bool $show_modified Whether to display the last modified date.
	 * }
	 * @yield array|void : {
	 *     string loc
	 *     string lastmod
	 * }
	 */
	protected function generate_url_item_values( $post_ids, $args ) {

		foreach ( $post_ids as $post_id ) {
			// Setup post cache, which is also used in is_post_included_in_sitemap() and get_bare_singular_url().
			$post = \get_post( $post_id );

			if ( Sitemap\Utils::is_post_included_in_sitemap( $post_id ) ) {
				$_values = [
					'loc' => Meta\URI::get_bare_singular_url( $post_id ),
				];

				if ( $args['show_modified'] )
					$_values['lastmod'] = $post->post_modified_gmt ?? '0000-00-00 00:00:00';

				++$this->url_count;
				yield $_values;
			}

			// Only clean post cache when NOT using a caching plugin.
			\WP_CACHE or \clean_post_cache( $post );
		}
	}

	/**
	 * Builds, escapes, and returns a sitemap URL item.
	 *
	 * @since 4.0.0
	 * @since 4.1.1 Now uses `create_xml_entry()` to parse the XML.
	 * @since 5.0.0 Is now static.
	 *
	 * @param array $args {
	 *     The URL item arguments.
	 *
	 *     @type string  $loc     The item's URI.
	 *     @type ?string $lastmod SQL timestamp string (Y-m-d H:i:s). Expected to be GMT. Null or '0000-00-00 00:00:00' to omit.
	 * }
	 * @return string The sitemap item.
	 */
	protected static function build_url_item( $args ) {

		if ( empty( $args['loc'] ) ) return '';

		$xml = [
			'loc' => Escape::xml_uri( $args['loc'] ),
		];

		if ( isset( $args['lastmod'] ) && '0000-00-00 00:00:00' !== $args['lastmod'] ) {
			// XML safe.
			$xml['lastmod'] = Time::convert_to_preferred_format( $args['lastmod'] );
		}

		return static::create_xml_entry( [ 'url' => $xml ], 1 );
	}

	/**
	 * Retrieves additional URLs and builds items from them.
	 *
	 * @since 4.0.0
	 * @since 4.0.1 1. Converted to generator and iterator. Therefore, renamed function.
	 *              2. Now actually does something.
	 * @since 5.0.0 Removed second parameter `&$count`.
	 * @generator
	 * @iterator
	 *
	 * @param array $args {
	 *     @type bool $show_modified Whether to display modified date.
	 *     @type int  $count         The total sitemap items before adding additional URLs.
	 * }
	 * @yield array|void : {
	 *     @type string $loc      The URI of the page.
	 *     @type string $lastmod  The page's last modified date.
	 * }
	 */
	protected function generate_additional_base_urls( $args ) {
		/**
		 * @since 2.5.2
		 * @since 3.2.2 Invalid URLs are now skipped.
		 * @since 4.0.0 Added $args parameter.
		 * @since 4.2.0 No longer forwards the 'show_priority' index in the second ($args) parameter.
		 * @example return value: [ 'http://example.com' => [ 'lastmod' => '2024-04-10 14:52:06' ] ]
		 * @param array $custom_urls {
		 *     An array of custom URLs, keyed by the absolute url to the page.
		 *
		 *     @type string $lastmod UNIXTIME <GMT+0> Last modified date, e.g. "2016-01-26 13:04:55"
		 * }
		 * @param array $args {
		 *     The sitemap URL extension arguments.
		 *
		 *     @type bool $show_modified Whether to display modified date.
		 *     @type int  $count         Estimate: The total sitemap items before adding additional URLs.
		 * }
		 */
		$custom_urls = (array) \apply_filters( 'the_seo_framework_sitemap_additional_urls', [], $args );

		foreach ( $custom_urls as $url => $values ) {
			if ( ! \is_array( $values ) ) {
				// If there are no args, it's assigned as URL (per example)
				$url = $values;
			}

			// Test if URL is valid.
			if ( ! \sanitize_url( $url, [ 'https', 'http' ] ) ) continue;

			// Reset.
			$_values        = [];
			$_values['loc'] = $url;

			if ( $args['show_modified'] )
				$_values['lastmod'] = ! empty( $values['lastmod'] ) ? $values['lastmod'] : '0000-00-00 00:00:00';

			++$this->url_count;
			yield $_values;
		}
	}
}
