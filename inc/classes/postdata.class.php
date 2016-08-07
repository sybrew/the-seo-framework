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
 * Class AutoDescription_PostData
 *
 * Holds Post data.
 *
 * @since 2.1.6
 */
class AutoDescription_PostData extends AutoDescription_Detect {

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
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'save_post', array( $this, 'inpost_seo_save' ), 1, 2 );
	}

	/**
	 * Save the SEO settings when we save a post or page.
	 * Some values get sanitized, the rest are pulled from identically named subkeys in the $_POST['autodescription'] array.
	 *
	 * @uses $this->save_custom_fields() Perform checks and saves post meta / custom field data to a post or page.
	 *
	 * @since 2.0.0
	 *
	 * @param integer  $post_id  Post ID.
	 * @param stdClass $post     Post object.
	 * @return mixed Returns post id if permissions incorrect, null if doing autosave, ajax or future post, false if update
	 *               or delete failed, and true on success.
	 */
	public function inpost_seo_save( $post_id, $post ) {

		if ( ! isset( $_POST['autodescription'] ) )
			return;

		/**
		 * Merge user submitted options with fallback defaults
		 * Passes through nonce at the end of the function.
		 */
		$data = wp_parse_args( $_POST['autodescription'], array(
			'_genesis_title'         => '',
			'_genesis_description'   => '',
			'_genesis_canonical_uri' => '',
			'redirect'               => '',
			'_genesis_noindex'       => 0,
			'_genesis_nofollow'      => 0,
			'_genesis_noarchive'     => 0,
			'exclude_local_search'   => 0,
		) );

		foreach ( (array) $data as $key => $value ) {
			//* Sanitize the title
			if ( '_genesis_title' === $key )
				$data[ $key ] = trim( strip_tags( $value ) );

			//* Sanitize the description
			if ( '_genesis_description' === $key )
				$data[ $key ] = $this->s_description( $value );

			//* Sanitize the URL. Make sure it's an absolute URL
			if ( 'redirect' === $key )
				$data[ $key ] = $this->s_redirect_url( $value );

		}

		$this->save_custom_fields( $data, $this->inpost_nonce_field, $this->inpost_nonce_name, $post );
	}

	/**
	 * Save post meta / custom field data for a post or page.
	 *
	 * It verifies the nonce, then checks we're not doing autosave, ajax or a future post request. It then checks the
	 * current user's permissions, before finally* either updating the post meta, or deleting the field if the value was not
	 * truthy.
	 *
	 * By passing an array of fields => values from the same metabox (and therefore same nonce) into the $data argument,
	 * repeated checks against the nonce, request and permissions are avoided.
	 *
	 * @since 2.0.0
	 *
	 * @thanks StudioPress (http://www.studiopress.com/) for some code.
	 *
	 * @param array    $data         Key/Value pairs of data to save in '_field_name' => 'value' format.
	 * @param string   $nonce_action Nonce action for use with wp_verify_nonce().
	 * @param string   $nonce_name   Name of the nonce to check for permissions.
	 * @param WP_Post|integer $post  Post object or ID.
	 * @return mixed Return null if permissions incorrect, doing autosave, ajax or future post, false if update or delete
	 *               failed, and true on success.
	 */
	public function save_custom_fields( array $data, $nonce_action, $nonce_name, $post ) {

		//* Verify the nonce
		if ( ! isset( $_POST[ $nonce_name ] ) || ! wp_verify_nonce( $_POST[ $nonce_name ], $nonce_action ) )
			return;

		//* Don't try to save the data under autosave, ajax, or future post.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			return;
		if ( defined( 'DOING_CRON' ) && DOING_CRON )
			return;

		//* Grab the post object
		$post = get_post( $post );

		//* Don't save if WP is creating a revision (same as DOING_AUTOSAVE?)
		if ( 'revision' === get_post_type( $post ) )
			return;

		//* Check that the user is allowed to edit the post
		if ( ! current_user_can( 'edit_post', $post->ID ) )
			return;

		//* Cycle through $data, insert value or delete field
		foreach ( (array) $data as $field => $value ) {
			//* Save $value, or delete if the $value is empty
			if ( $value )
				update_post_meta( $post->ID, $field, $value );
			else
				delete_post_meta( $post->ID, $field );
		}

	}

	/**
	 * Fetches or parses the excerpt of the post.
	 *
	 * @since 1.0.0
	 *
	 * @param string $excerpt the Excerpt.
	 * @param int $the_id The Post ID.
	 * @param int $tt_id The Taxonomy Term ID.
	 * @return string The Excerpt
	 */
	public function get_excerpt_by_id( $excerpt = '', $the_id = '', $tt_id = '' ) {

		if ( empty( $excerpt ) )
			$excerpt = $this->fetch_excerpt( $the_id, $tt_id );

		//* No need to parse an empty excerpt.
		if ( '' === $excerpt )
			return '';

		/**
		 * Applies filters 'the_seo_framework_allow_excerpt_shortcode_tags' : boolean
		 * @since 2.6.6.1
		 */
		if ( apply_filters( 'the_seo_framework_allow_excerpt_shortcode_tags', false ) && false === $this->is_feed() ) {
			$excerpt = wp_strip_all_tags( $excerpt );
		} else {
			$excerpt = wp_strip_all_tags( strip_shortcodes( $excerpt ) );
		}

		$output = $this->s_description( $excerpt );

		return $output;
	}

	/**
	 * Fetches excerpt from post excerpt or fetches the full post content.
	 * Determines if a page builder is used to return an empty string.
	 * Does not sanitize output.
	 *
	 * @since 2.5.2
	 * @since 2.6.6 Detects Page builders.
	 *
	 * @param int $the_id The Post ID.
	 * @param int $tt_id The Taxonomy Term ID.
	 * @return string|empty excerpt.
	 */
	public function fetch_excerpt( $the_id = '', $tt_id = '' ) {

		$post = $this->fetch_post_by_id( $the_id, $tt_id, OBJECT );

		if ( empty( $post ) )
			return '';

		/**
		 * Fetch custom excerpt, if not empty, from the post_excerpt field.
		 * @since 2.5.2
		 */
		if ( isset( $post->post_excerpt ) && $post->post_excerpt ) {
			$excerpt = $post->post_excerpt;
		} elseif ( isset( $post->post_content ) ) {
			$uses_builder = $this->uses_page_builder( $post->ID );
			$excerpt = $uses_builder ? '' : $post->post_content;
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
	 * @since 2.6.6 Added $output parameter.
	 *
	 * @param int $the_id The Post ID.
	 * @param int $tt_id The Taxonomy Term ID
	 * @param mixed $output The value type to return. Accepts OBJECT, ARRAY_A, or ARRAY_N
	 * @return empty|array The Post Array.
	 */
	protected function fetch_post_by_id( $the_id = '', $tt_id = '', $output = ARRAY_A ) {

		if ( '' === $the_id && '' === $tt_id ) {
			$the_id = $this->get_the_real_ID();

			if ( false === $the_id )
				return '';
		}

		/**
		 * @since 2.2.8 Use the 2nd parameter.
		 * @since 2.3.3 Now casts to array
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
				$post = get_post( $the_id );
			}
		} elseif ( '' !== $tt_id ) {
			/**
			 * @since 2.3.3 Match the descriptions in admin as on the front end.
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
			$post = get_post( $the_id );
		}

		/**
		 * @since 2.6.5 Transform post array to object (on Archives).
		 */
		if ( is_array( $post ) && isset( $post[0] ) && is_object( $post[0] ) )
			$post = $post[0];

		//* Something went wrong, nothing to be found. Return empty.
		if ( empty( $post ) )
			return '';

		//* Stop getting something that doesn't exists. E.g. 404
		if ( isset( $post->ID ) && 0 === $post->ID )
			return '';

		/**
		 * @since 2.6.6
		 */
		if ( ARRAY_A === $output || ARRAY_N === $output ) {
			$_post = WP_Post::get_instance( $post );
			$post = $_post->to_array();

			if ( ARRAY_N === $output )
				$post = array_values( $post );
		}

		return $post;
	}

	/**
	 * Fetch latest public post ID.
	 *
	 * @since 2.4.3
	 * @staticvar int $page_id
	 * @global object $wpdb
	 * @global int $blog_id
	 *
	 * @return int Latest Post ID.
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
				'',
				1
			);

			$page_id = (int) $wpdb->get_var( $sql );
			$this->object_cache_set( $latest_posts_key, $page_id, DAY_IN_SECONDS );
		}

		return $page_id;
	}

	/**
	 * Fetches Post content.
	 *
	 * @since 2.6.0
	 *
	 * @param int $id The post ID.
	 * @return string The post content.
	 */
	public function get_post_content( $id = 0 ) {

		$id = $id ? $id : $this->get_the_real_ID();

		$content = get_post_field( 'post_content', $id );

		if ( is_string( $content ) )
			return $content;

		return '';
	}

	/**
	 * Determines whether the post has a page builder attached to it.
	 * Doesn't use plugin detection features as some builders might be incorporated within themes.
	 *
	 * Detects the following builders:
	 * - Divi Builder by Elegant Themes
	 * - Visual Composer by WPBakery
	 * - Page Builder by SiteOrigin
	 * - Beaver Builder by Fastline Media
	 *
	 * @since 2.6.6
	 *
	 * @param int $post_id
	 * @return boolean
	 */
	public function uses_page_builder( $post_id ) {

		$meta = get_post_meta( $post_id );

		/**
		 * Applies filters 'the_seo_framework_detect_page_builder' : boolean
		 * Determines whether a page builder has been detected.
		 * @since 2.6.6
		 *
		 * @param boolean The current state.
		 * @param int $post_id The current Post ID.
		 * @param array $meta The current post meta.
		 */
		$detected = (bool) apply_filters( 'the_seo_framework_detect_page_builder', false, $post_id, $meta );

		if ( $detected )
			return true;

		if ( empty( $meta ) )
			return false;

		if ( isset( $meta['_et_pb_use_builder'][0] ) && 'on' === $meta['_et_pb_use_builder'][0] && defined( 'ET_BUILDER_VERSION' ) )
			//* Divi Builder by Elegant Themes
			return true;
		elseif ( isset( $meta['_wpb_vc_js_status'][0] ) && 'true' === $meta['_wpb_vc_js_status'][0] && defined( 'WPB_VC_VERSION' ) )
			//* Visual Composer by WPBakery
			return true;
		elseif ( isset( $meta['panels_data'][0] ) && '' !== $meta['panels_data'][0] && defined( 'SITEORIGIN_PANELS_VERSION' ) )
			//* Page Builder by SiteOrigin
			return true;
		elseif ( isset( $meta['_fl_builder_enabled'][0] ) && '1' === $meta['_fl_builder_enabled'][0] && defined( 'FL_BUILDER_VERSION' ) )
			//* Beaver Builder by Fastline Media
			return true;

		return false;
	}
}
