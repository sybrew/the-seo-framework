<?php
/**
 * @package The_SEO_Framework\Builders\Core_Sitemaps
 * @subpackage WordPress\Sitemaps
 */

namespace The_SEO_Framework\Builders\CoreSitemaps;

/**
 * The SEO Framework plugin
 * Copyright (C) 2020 - 2021 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Augments the WordPress Core 'posts' sitemap.
 *
 * @since 4.1.2
 *
 * @access private
 */
class Posts extends \WP_Sitemaps_Posts {

	/**
	 * Gets a URL list for a post type sitemap.
	 *
	 * Copied from parent and augmented slightly to return
	 *
	 * @since 4.1.2
	 * @source \WP_Sitemaps_Posts\get_url_list()
	 * @TEMP https://wordpress.slack.com/archives/CTKTGNJJW/p1604995479019700
	 * @link <https://core.trac.wordpress.org/ticket/51860>
	 *
	 * @param int    $page_num  Page of results.
	 * @param string $post_type Optional. Post type name. Default empty.
	 * @return array Array of URLs for a sitemap.
	 */
	public function get_url_list( $page_num, $post_type = '' ) {
		// Bail early if the queried post type is not supported.
		$supported_types = $this->get_object_subtypes();

		if ( ! isset( $supported_types[ $post_type ] ) ) {
			return [];
		}

		/**
		 * Filters the posts URL list before it is generated.
		 *
		 * Passing a non-null value will effectively short-circuit the generation,
		 * returning that value instead.
		 *
		 * @since WP Core 5.5.0
		 *
		 * @param array  $url_list  The URL list. Default null.
		 * @param string $post_type Post type name.
		 * @param int    $page_num  Page of results.
		 */
		$url_list = \apply_filters(
			'wp_sitemaps_posts_pre_url_list',
			null,
			$post_type,
			$page_num
		);

		if ( null !== $url_list ) {
			return $url_list;
		}

		$args          = $this->get_posts_query_args( $post_type );
		$args['paged'] = $page_num;

		$query = new \WP_Query( $args );

		$url_list = [];

		/**
		 * @augmented This differs from the inherented.
		 */
		$tsf              = \the_seo_framework();
		$main             = Main::get_instance();
		$show_modified    = (bool) $tsf->get_option( 'sitemaps_modified' );
		$timestamp_format = $tsf->get_timestamp_format();

		/*
		 * Add a URL for the homepage in the pages sitemap.
		 * Shows only on the first page if the reading settings are set to display latest posts.
		 */
		if ( 'page' === $post_type && 1 === $page_num && 'posts' === \get_option( 'show_on_front' ) ) {
			/**
			 * @augmented This if-statement prevents including the homepage as blog in the sitemap when conditions apply.
			 */
			if ( $main->is_post_included_in_sitemap( 0 ) ) {
				// Extract the data needed for home URL to add to the array.
				$sitemap_entry = [
					'loc' => \home_url( '/' ),
				];

				/**
				 * @augmented Adds lastmod to sitemap entry.
				 */
				if ( $show_modified ) {
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

					$lastmod = isset( $latests_posts[0]->post_date_gmt ) ? $latests_posts[0]->post_date_gmt : '0000-00-00 00:00:00';

					/**
					 * @since 4.1.1
					 * @param string $lastmod The lastmod time in SQL notation (`Y-m-d H:i:s`). Expected to explicitly follow that format!
					 */
					$sitemap_entry['lastmod'] = (string) \apply_filters( 'the_seo_framework_sitemap_blog_lastmod', $lastmod );
				}

				/**
				 * Filters the sitemap entry for the home page when the 'show_on_front' option equals 'posts'.
				 *
				 * @since WP Core 5.5.0
				 *
				 * @param array $sitemap_entry Sitemap entry for the home page.
				 */
				$sitemap_entry = \apply_filters( 'wp_sitemaps_posts_show_on_front_entry', $sitemap_entry );
				$url_list[]    = $sitemap_entry;
			}
		}

		foreach ( $query->posts as $post ) {
			/**
			 * @augmented This if-statement prevents including the post in the sitemap when conditions apply.
			 */
			if ( ! $main->is_post_included_in_sitemap( $post->ID ) )
				continue;

			$sitemap_entry = [
				'loc' => \get_permalink( $post ),
			];

			/**
			 * @augmented Adds lastmod to sitemap entry.
			 */
			if ( $show_modified ) {
				$lastmod = isset( $post->post_modified_gmt ) ? $post->post_modified_gmt : false;

				if ( $lastmod && '0000-00-00 00:00:00' !== $lastmod )
					$sitemap_entry['lastmod'] = $tsf->gmt2date( $timestamp_format, $lastmod );
			}

			/**
			 * Filters the sitemap entry for an individual post.
			 *
			 * @since WP Core 5.5.0
			 *
			 * @param array   $sitemap_entry Sitemap entry for the post.
			 * @param WP_Post $post          Post object.
			 * @param string  $post_type     Name of the post_type.
			 */
			$sitemap_entry = \apply_filters( 'wp_sitemaps_posts_entry', $sitemap_entry, $post, $post_type );
			$url_list[]    = $sitemap_entry;
		}

		return $url_list;
	}
}
