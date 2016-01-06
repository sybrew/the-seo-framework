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
 * Class AutoDescription_PageOptions
 *
 * Hold Post/Page/Taxonomy Options for the plugin and sanitizes them.
 *
 * @since 2.1.6
 */
class AutoDescription_PageOptions extends AutoDescription_DoingItRight {

	/**
	 * Constructor, load parent constructor
	 *
	 * Initalizes options
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'save_post', array( $this, 'inpost_seo_save' ), 1, 2 );
		add_action( 'edit_term', array( $this, 'taxonomy_seo_save' ), 10, 2 );

		add_filter( 'get_term', array( $this, 'get_term_filter' ), 10, 2 );
		add_action( 'delete_term', array( $this, 'term_meta_delete' ), 10, 2 );
		add_filter( 'get_terms', array( $this, 'get_terms_filter' ), 10, 2 );
	}

	/**
	 * Save the SEO settings when we save a post or page.
	 *
	 * Some values get sanitized, the rest are pulled from identically named subkeys in the $_POST['autodescription'] array.
	 *
	 * @since 2.0.0
	 *
	 * @uses $this->save_custom_fields() Perform checks and saves post meta / custom field data to a post or page.
	 *
	 * @param integer  $post_id  Post ID.
	 * @param stdClass $post     Post object.
	 *
	 * @return mixed Returns post id if permissions incorrect, null if doing autosave, ajax or future post, false if update
	 *               or delete failed, and true on success.
	 */
	public function inpost_seo_save( $post_id, $post ) {

		if ( ! isset( $_POST['autodescription'] ) )
			return;

		//* Merge user submitted options with fallback defaults
		$data = wp_parse_args( $_POST['autodescription'], array(
			'_genesis_title'         => '',
			'_genesis_description'   => '',
			'_genesis_canonical_uri' => '',
			'redirect'               => '',
			'_genesis_noindex'       => 0,
			'_genesis_nofollow'      => 0,
			'_genesis_noarchive'     => 0,
			'exclude_local_search'   => 0,
			'saved_flag'             => 0,  // Don't touch, used to prevent data conflict.
		) );

		foreach ( (array) $data as $key => $value ) {
			//* Sanitize the title
			if ( '_genesis_title' === $key ) {
				$data[$key] = trim( strip_tags( $value ) );
			}

			//* Sanitize the description
			if ( '_genesis_description' === $key ) {
				$data[$key] = $this->s_description( $value );
			}

			//* Sanitize the URL. Make sure it's an absolute URL
			if ( 'redirect' === $key ) {
				$data[$key] = $this->s_redirect_url( $value );
			}
		}

		$this->save_custom_fields( $data, 'inpost_seo_save', 'hmpl_ad_inpost_seo_nonce', $post );
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
	 * @param array    $data         Key/Value pairs of data to save in '_field_name' => 'value' format.
	 * @param string   $nonce_action Nonce action for use with wp_verify_nonce().
	 * @param string   $nonce_name   Name of the nonce to check for permissions.
	 * @param WP_Post|integer $post  Post object or ID.
	 *
	 * @return mixed Return null if permissions incorrect, doing autosave, ajax or future post, false if update or delete
	 *               failed, and true on success.
	 *
	 * @thanks StudioPress (http://www.studiopress.com/) for some code.
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
	 * Add term meta data into options table of the term.
	 * Adds separated database options for terms, as the terms table doesn't allow for addition.
	 *
	 * Applies filters array the_seo_framework_term_meta_defaults : Array of default term SEO options
	 * Applies filters mixed the_seo_framework_term_meta_{field} : Override filter for specifics.
	 * Applies filters array the_seo_framework_term_meta : Override output for term or taxonomy.
	 *
	 * @since 2.1.8
	 *
	 * @param object $term     Database row object.
	 * @param string $taxonomy Taxonomy name that $term is part of.
	 *
	 * @return object $term Database row object.
	 */
	public function get_term_filter( $term, $taxonomy ) {

		//* Do nothing, if $term is not object
		if ( ! is_object( $term ) )
			return $term;

		//* Do nothing, if called in the context of creating a term via an ajax call
		if ( did_action( 'wp_ajax_add-tag' ) )
			return $term;

		$db = get_option( 'autodescription-term-meta' );
		$term_meta = isset( $db[$term->term_id] ) ? $db[$term->term_id] : array();

		/**
		 * New filter.
		 * @since 2.3.0
		 *
		 * Removed previous filter.
		 * @since 2.3.5
		 */
		$args = (array) apply_filters( 'the_seo_framework_term_meta_defaults', array(
			'doctitle'            => '',
			'description'         => '',
			'noindex'             => 0,
			'nofollow'            => 0,
			'noarchive'           => 0,
			'saved_flag'          => 0, // Don't touch, used to prevent data conflict with Genesis.
		) );

		$term->admeta = wp_parse_args( $term_meta, $args );

		//* Sanitize term meta
		foreach ( $term->admeta as $field => $value ) {

			/**
			 * Trim and sanitize the title beforehand.
			 * @since 2.5.0
			 */
			if ( 'doctitle' === $field )
				$value = trim( strip_tags( $value ) );

			/**
			 * Trim and sanitize the description beforehand.
			 * @since 2.5.0
			 */
			if ( 'description' === $field )
				$value = $this->s_description( $value );

			/**
			 * New filter.
			 * @since 2.3.0
			 *
			 * Removed previous filter.
			 * @since 2.3.5
			 *
			 * @param $taxonomy the Taxonomy name
			 */
			$term->admeta[$field] = (string) apply_filters( "the_seo_framework_term_meta_{$field}", stripslashes( wp_kses_decode_entities( $value ) ), $term, $taxonomy );
		}

		/**
		 * New filter.
		 * @since 2.3.0
		 *
		 * Removed previous filter.
		 * @since 2.3.5
		 *
		 * @param $taxonomy the Taxonomy name
		 */
		$term->admeta = (array) apply_filters( 'the_seo_framework_term_meta', $term->admeta, $term, $taxonomy );

		return $term;
	}

	/**
	 * Save taxonomy meta data.
	 *
	 * Fires when a user edits and saves a taxonomy.
	 *
	 * @since 2.1.8
	 *
	 * @param integer $term_id Term ID.
	 * @param integer $tt_id   Term Taxonomy ID.
	 */
	public function taxonomy_seo_save( $term_id, $tt_id ) {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			return;

		$term_meta = (array) get_option( 'autodescription-term-meta' );

		$term_meta[$term_id] = isset( $_POST['autodescription-meta'] ) ? (array) $_POST['autodescription-meta'] : array();

		//* Pass through wp_kses if not super admin.
		if ( ! current_user_can( 'unfiltered_html' ) && isset( $term_meta[$term_id]['archive_description'] ) )
			$term_meta[$term_id]['archive_description'] = wp_kses( $term_meta[$term_id]['archive_description'] );

		update_option( 'autodescription-term-meta', $term_meta );

	}

	/**
	 * Add AutoDescription term-meta data to functions that return multiple terms.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $terms    Database row objects.
	 * @param string $taxonomy Taxonomy name that $terms are part of.
	 *
	 * @return array $terms Database row objects.
	 */
	public function get_terms_filter( array $terms, $taxonomy ) {

		foreach( $terms as $term )
			$term = $this->get_term_filter( $term, $taxonomy );

		return $terms;
	}

	/**
	 * Delete term meta data.
	 *
	 * Fires when a user deletes a term.
	 *
	 * @since 2.1.8
	 *
	 * @param integer $term_id Term ID.
	 * @param integer $tt_id   Taxonomy Term ID.
	 */
	public function term_meta_delete( $term_id, $tt_id ) {

		$term_meta = (array) get_option( 'autodescription-term-meta' );

		unset( $term_meta[$term_id] );

		update_option( 'autodescription-term-meta', (array) $term_meta );

	}

}
