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
	 * Constructor, load parent constructor
	 *
	 * Initalizes options
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'pre_get_posts', array( $this, 'search_filter' ), 999, 1 );
	}

	/**
	 * Fetches posts with exclude_local_search option on.
	 *
	 * @since 2.1.7
	 * @uses $this->exclude_search_ids()
	 *
	 * @param array $query The search query
	 */
	public function search_filter( $query ) {

		// Don't exclude pages in wp-admin
		if ( $query->is_search && false === $this->is_admin() ) {

			$q = $query->query;
			//* Only interact with an actual Search Query.
			if ( ! isset( $q['s'] ) || ! $q['s'] )
				return;

			//* Get excluded IDs.
			$protected_posts = $this->exclude_search_ids();
			if ( $protected_posts ) {
				$get = $query->get( 'post__not_in' );

				//* Merge user defined query.
				if ( $get )
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
	 * @param array $post_ids			The post id's which are excluded
	 * @param array $args				Posts search arguments
	 * @param array $protected_posts	Posts array with excluded key
	 *
	 * @global int $blog_id
	 *
	 * @since 2.1.7
	 *
	 * @return array Excluded Post IDs
	 */
	public function exclude_search_ids() {
		global $blog_id;

		$cache_key = 'exclude_search_ids_' . $blog_id . '_' . get_locale();

		$post_ids = $this->object_cache_get( $cache_key );
		if ( false === $post_ids ) {
			$post_ids = array();

			$args = array(
				'post_type' => 'any',
				'meta_key' => 'exclude_local_search',
				'meta_value' => 1,
				'posts_per_page' => 99999, // get them all! Fast enough! :D
				'meta_compare' => '=',
			);

			/*
			$get_posts = new WP_Query;
			$protected_posts = $get_posts->query( $args );
			unset( $get_posts );
			*/

			//* @TODO check if this uses cache.
			$protected_posts = get_posts( $args );
			if ( $protected_posts )
				$post_ids = wp_list_pluck( $protected_posts, 'ID' );

			$this->object_cache_set( $cache_key, $post_ids, 86400 );
		}

		// return an array of exclude post IDs
		return $post_ids;
	}
}
