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
 * Excludes pages from search based on options
 *
 * @since 2.1.6
 */
class AutoDescription_Search extends AutoDescription_Generate {

	/**
	 * Constructor, load parent constructor
	 *
	 * Initalizes options
	 */
	public function __construct() {
		parent::__construct();

		//* @TODO Add to network settings.
		add_action( 'pre_get_posts', array( $this, 'search_filter' ), 999, 1 );
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
	 */
	public function exclude_search_ids() {
		global $blog_id;

		$cache_key = 'exclude_search_ids_' . $blog_id;

		$post_ids = $this->object_cache_get( $cache_key );
		if ( false === $post_ids ) {
			$post_ids = array();

			$args = array(
				'post_type' => 'any',
				'meta_key' => 'exclude_local_search',
				'meta_value' => 1,
				'posts_per_page' => 99999, // get them all! Fast enough! :D
				'meta_compare' => '=',
			/*	'post_status' => array('publish','private'),*/
			);

			$protected_posts = get_posts( $args );
			if ( $protected_posts ) {
				$post_ids = wp_list_pluck( $protected_posts, 'ID' );
			}

			$this->object_cache_set( $cache_key, $post_ids, 86400 );
		}

		// return an array of exclude post IDs
		return $post_ids;
	}

	/**
	 * Fetches posts with exclude_local_search option on
	 *
	 * @param array $query				The search query
	 * @param array $protected_posts	Posts array with excluded key
	 *
	 * @uses $this->exclude_search_ids()
	 *
	 * @since 2.1.7
	 *
	 * @todo run this only when one post triggers this option?
	 */
	public function search_filter( $query ) {

		// Don't exclude pages in wp-admin
		if ( ! is_admin() ) {
			if ( $query->is_search ) {

				$protected_posts = $this->exclude_search_ids();
				if ( ! empty( $protected_posts ) ) {
					$get = $query->get( 'post__not_in' );

					//* Merge user defined query.
					if ( !empty( $get ) )
						$protected_posts = array_merge( $protected_posts, $get );

					$query->set( 'post__not_in', $protected_posts );
				}

				// Parse all ID's, even beyond the first page.
				$query->set( 'no_found_rows', false );
			}
		}

	}

}
