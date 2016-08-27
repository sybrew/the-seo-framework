<?php
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

defined( 'ABSPATH' ) or die;

/**
 * Class AutoDescription_Search
 *
 * Excludes pages from search based on options
 *
 * @since 2.1.6
 */
class AutoDescription_Search extends AutoDescription_Generate_Ldjson {

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	private function __wakeup() { }

	/**
	 * Handle unapproachable invoked methods.
	 */
	public function __call( $name, $arguments ) {
		parent::__call( $name, $arguments );
	}

	/**
	 * Constructor, loads parent constructor and adds filters.
	 */
	public function __construct() {
		parent::__construct();

		/**
		 * @since 2.1.7
		 * @since 2.7.0 Changed priority from 999 to 9999.
		 *              Now uses another method. Was: 'search_filter'.
		 */
		add_action( 'pre_get_posts', array( $this, 'adjust_search_filter' ), 9999, 1 );
	}

	/**
	 * Excludes posts from search with certain metadata.
	 * For now, it only looks at 'exclude_local_search'. If it exists, the post or
	 * page will be excluded from the local Search Results.
	 *
	 * @since 2.7.0
	 *
	 * @param array $query The possible search query.
	 * @return void Early if no search query is found.
	 */
	public function adjust_search_filter( $query ) {

		// Don't exclude pages in wp-admin.
		if ( $query->is_search && false === $this->is_admin() ) {

			$q = $query->query;
			//* Only interact with an actual Search Query.
			if ( false === isset( $q['s'] ) )
				return;

			$meta_query = $query->get( 'meta_query' );

			//* Convert to array. Unset it if it's empty.
			if ( false === is_array( $meta_query ) )
				$meta_query = $meta_query ? (array) $meta_query : array();

			/**
			 * Exclude posts with exclude_local_search option on.
			 *
			 * Query is faster when the global relation is not set. Defaults to AND.
			 * Query is faster when secondary relation is set. Defaults to AND.
			 * Looks for CHAR value, while it's an integer/char in when unserialized.
			 */
			$meta_query[] = array(
				array(
					'key'      => 'exclude_local_search',
					'value'    => '1',
					'type'     => 'CHAR',
					'compare'  => 'NOT EXISTS',
					'relation' => 'AND',
				),
			);

			$query->set( 'meta_query', $meta_query );
		}
	}

	/**
	 * Fetches posts with exclude_local_search option on.
	 *
	 * @since 2.1.7
	 * @since 2.7.0 No longer used for performance reasons.
	 * @uses $this->exclude_search_ids()
	 *
	 * @param array $query The possible search query.
	 * @return void Early if no search query is found.
	 */
	public function search_filter( $query ) {

		// Don't exclude pages in wp-admin.
		if ( $query->is_search && false === $this->is_admin() ) {

			$q = $query->query;
			//* Only interact with an actual Search Query.
			if ( false === isset( $q['s'] ) )
				return;

			//* Get excluded IDs.
			$protected_posts = $this->get_excluded_search_ids();
			if ( $protected_posts ) {
				$get = $query->get( 'post__not_in' );

				//* Merge user defined query.
				if ( is_array( $get ) && ! empty( $get ) )
					$protected_posts = array_merge( $protected_posts, $get );

				$query->set( 'post__not_in', $protected_posts );
			}

			// Parse all ID's, even beyond the first page.
			$query->set( 'no_found_rows', false );
		}
	}

	/**
	 * Fetches posts with exclude_local_search option on
	 *
	 * @since 2.7.0
	 * @since 2.7.0 No longer used.
	 * @global int $blog_id
	 *
	 * @return array Excluded Post IDs
	 */
	public function get_excluded_search_ids() {
		global $blog_id;

		$cache_key = 'exclude_search_ids_' . $blog_id . '_' . get_locale();

		$post_ids = $this->object_cache_get( $cache_key );
		if ( false === $post_ids ) {
			$post_ids = array();

			$args = array(
				'post_type'        => 'any',
				'numberposts'      => -1,
				'posts_per_page'   => -1,
				'order'            => 'DESC',
				'post_status'      => 'publish',
				'meta_key'         => 'exclude_local_search',
				'meta_value'       => 1,
				'meta_compare'     => '=',
				'cache_results'    => true,
				'suppress_filters' => false,
			);
			$get_posts = new WP_Query;
			$excluded_posts = $get_posts->query( $args );
			unset( $get_posts );

			if ( $excluded_posts )
				$post_ids = wp_list_pluck( $excluded_posts, 'ID' );

			$this->object_cache_set( $cache_key, $post_ids, 86400 );
		}

		// return an array of exclude post IDs
		return $post_ids;
	}
}
