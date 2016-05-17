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

/**
 * Class AutoDescription_PostData
 *
 * Holds Post data.
 *
 * @since 2.1.6
 */
class AutoDescription_PostData extends AutoDescription_Detect {

	/**
	 * Constructor, load parent constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Get or parse the excerpt of the post
	 *
	 * @since 1.0.0
	 *
	 * @param string $excerpt the Excerpt
	 * @param int $the_id The Post ID.
	 * @param int $tt_id The Taxonomy Term ID
	 *
	 * @return string The Excerpt
	 */
	public function get_excerpt_by_id( $excerpt = '', $the_id = '', $tt_id = '' ) {

		static $cache = array();

		if ( isset( $cache[$excerpt][$the_id][$tt_id] ) )
			return $cache[$excerpt][$the_id][$tt_id];

		if ( empty( $excerpt ) )
			$excerpt = $this->fetch_excerpt( $the_id, $tt_id );

		//* No need to parse an empty excerpt.
		if ( '' === $excerpt )
			return '';

		$excerpt = wp_strip_all_tags( strip_shortcodes( $excerpt ) );
		$excerpt = str_replace( array( "\r\n", "\r", "\n" ), "\n", $excerpt );

		$lines = explode( "\n", $excerpt );
		$new_lines = array();

		//* Remove line breaks
		foreach ( $lines as $i => $line ) {
			//* Don't add empty lines or paragraphs
			if ( $line && '&nbsp;' !== $line )
				$new_lines[] = trim( $line ) . ' ';
		}

		$output = implode( $new_lines );

		return (string) $output;
	}

	/**
	 * Generate excerpt.
	 *
	 * @since 2.5.2
	 *
	 * @param int $the_id The Post ID.
	 * @param int $tt_id The Taxonomy Term ID
	 *
	 * @return string|empty excerpt.
	 */
	public function fetch_excerpt( $the_id = '', $tt_id = '' ) {

		$post = $this->fetch_post_by_id( $the_id, $tt_id );

		if ( empty( $post ) )
			return '';

		/**
		 * Fetch custom excerpt, if not empty, from the post_excerpt field.
		 * @since 2.5.2
		 */
		if ( isset( $post['post_excerpt'] ) && $post['post_excerpt'] ) {
			$excerpt = $post['post_excerpt'];
		} else if ( isset( $post['post_content'] ) ) {
			$excerpt = $post['post_content'];
		} else {
			$excerpt = '';
		}

		return $excerpt;
	}

	/**
	 * Returns Post Array from ID.
	 * Also returns latest post from blog or archive if applicable.
	 *
	 * @since 2.6.0
	 *
	 * @param int $the_id The Post ID.
	 * @param int $tt_id The Taxonomy Term ID
	 *
	 * @return empty|array The Post Array.
	 */
	protected function fetch_post_by_id( $the_id = '', $tt_id = '' ) {

		if ( '' === $the_id && '' === $tt_id ) {
			$the_id = $this->get_the_real_ID();

			if ( false === $the_id )
				return '';
		}

		/**
		 * Use the 2nd parameter.
		 * @since 2.2.8
		 *
		 * Now casts to array
		 * @since 2.3.3
		 */
		if ( '' !== $the_id ) {
			if ( $this->is_blog_page( $the_id ) ) {
				$args = array(
					'posts_per_page'	=> 1,
					'offset'			=> 0,
					'category'			=> '',
					'category_name'		=> '',
					'orderby'			=> 'date',
					'order'				=> 'DESC',
					'post_type'			=> 'post',
					'post_status'		=> 'publish',
					'cache_results'		=> false,
				);

				$post = get_posts( $args );
			} else {
				$post = get_post( $the_id, ARRAY_A );
			}
		} else if ( '' !== $tt_id ) {
			/**
			 * Match the descriptions in admin as on the front end.
			 * @since 2.3.3
			 */
			$args = array(
				'posts_per_page'	=> 1,
				'offset'			=> 0,
				'category'			=> $tt_id,
				'category_name'		=> '',
				'post_type'			=> 'post',
				'post_status'		=> 'publish',
				'cache_results'		=> false,
			);

			$post = get_posts( $args );
		} else {
			$post = get_post( $the_id, ARRAY_A );
		}

		/**
		 * Cast last found post object to array and put it in $post.
		 * @since 2.3.3
		 */
		if ( isset( $post[0] ) && is_object( $post[0] ) ) {
			$object = $post[0];
			$post = (array) $object;
		}

		// Something went wrong, nothing to be found. Return empty.
		if ( empty( $post ) || ! is_array( $post ) )
			return '';

		//* Stop getting something that doesn't exists. E.g. 404
		if ( isset( $post['ID'] ) && 0 === $post['ID'] )
			return '';

		return $post;
	}

	/**
	 * Fetch latest public post ID.
	 *
	 * @staticvar int $page_id
	 * @global object $wpdb
	 * @global int $blog_id
	 *
	 * @since 2.4.3
	 */
	public function get_latest_post_id() {
		global $wpdb, $blog_id;

		static $page_id = null;

		if ( isset( $page_id ) )
			return $page_id;

		$latest_posts_key = 'latest_post_id_' . $blog_id;

		//* @TODO consider transient.
		$page_id = $this->object_cache_get( $latest_posts_key );
		if ( false === $page_id ) {

			// Prepare array
			$post_type = esc_sql( array( 'post', 'page' ) );
			$post_type_in_string = "'" . implode( "','", $post_type ) . "'";

			// Prepare array
			$post_status = esc_sql( array( 'publish', 'future', 'pending' ) );
			$post_status_in_string = "'" . implode( "','", $post_status ) . "'";

			$sql = $wpdb->prepare(
				"SELECT ID
				FROM $wpdb->posts
				WHERE post_title <> %s
				AND post_type IN ($post_type_in_string)
				AND post_date < NOW()
				AND post_status IN ($post_status_in_string)
				ORDER BY post_date DESC
				LIMIT %d",
				'', 1 );

			$page_id = (int) $wpdb->get_var( $sql );
			$this->object_cache_set( $latest_posts_key, $page_id, 86400 );
		}

		return $page_id;
	}

	/**
	 * Fetches Post content.
	 *
	 * @since 2.6.0
	 *
	 * @param int $id.
	 *
	 * @return string The post content.
	 */
	public function get_post_content( $id = 0 ) {

		if ( empty( $id ) ) {
			global $wp_query;

			if ( isset( $wp_query->post->post_content ) )
				return $wp_query->post->post_content;
		} else {
			$content = get_post_field( 'post_content', $id );

			if ( is_string( $content ) )
				return $content;
		}

		return '';
	}

}
