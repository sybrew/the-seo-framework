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
 * Class AutoDescription_TermData
 *
 * Holds Term and Taxonomy data.
 *
 * @since 2.6.0
 */
class AutoDescription_TermData extends AutoDescription_PostData {

	/**
	 * Constructor, load parent constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'current_screen', array( $this, 'init_term_filters' ), 999 );
		add_action( 'get_header', array( $this, 'init_term_filters' ), 999 );

		add_action( 'edit_term', array( $this, 'taxonomy_seo_save' ), 10, 2 );
		add_action( 'delete_term', array( $this, 'term_meta_delete' ), 10, 2 );
	}

	/**
	 * Initializes term filters after wp_query or currentscreen has been set.
	 *
	 * @since 2.6.6
	 * @staticvar boolean $run Whether this function has already run.
	 * @access private
	 *
	 * @return void early if already run.
	 */
	public function init_term_filters() {

		static $run = null;

		if ( isset( $run ) )
			return;

		add_filter( 'get_term', array( $this, 'get_term_filter' ), 10, 2 );
		add_filter( 'get_terms', array( $this, 'get_terms_filter' ), 10, 2 );

		$run = true;

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
	 * @todo Use WordPress 4.4.0 get_term_meta() / update_term_meta()
	 * @priority OMG WTF BBQ 2.6.x / Genesis 2.3.0
	 * @see @link http://www.studiopress.com/important-announcement-for-genesis-plugin-developers/
	 * @link https://core.trac.wordpress.org/browser/tags/4.5/src/wp-includes/taxonomy.php#L1814
	 * @todo still use arrays in get_term_meta() / update_term_meta() ?
	 * @NOTE Keep WP 3.8 compat.
	 *
	 * @param object $term     Database row object.
	 * @param string $taxonomy Taxonomy name that $term is part of.
	 * @return object $term Database row object.
	 */
	public function get_term_filter( $term, $taxonomy ) {

		//* Do nothing, if $term is not an object.
		if ( ! is_object( $term ) )
			return $term;

		//* We can't set query vars just yet.
		if ( false === $this->can_cache_query() )
			return $term;

		/**
		 * No need to process this data outside of the Terms' scope.
		 * @since 2.6.0
		 */
		if ( false === $this->is_admin() && false === $this->is_archive() )
			return $term;

		/**
		 * No need to process this after the data has already been output.
		 * @since 2.6.0
		 */
		if ( did_action( 'the_seo_framework_do_after_output' ) )
			return $term;

		/**
		 * Do nothing if called in the context of creating a term via an Ajax call to prevent data conflict.
		 * @since ???
		 *
		 * @since 2.6.0 delay did_action call as it's a heavy array call.
		 */
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && did_action( 'wp_ajax_add-tag' ) )
			return $term;

		$db = get_option( 'autodescription-term-meta' );
		$term_meta = isset( $db[$term->term_id] ) ? $db[$term->term_id] : array();

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
			 * @param object $term The Term object.
			 * @param string $taxonomy The Taxonomy name.
			 */
			$term->admeta[$field] = (string) apply_filters( "the_seo_framework_term_meta_{$field}", stripslashes( wp_kses_decode_entities( $value ) ), $term, $taxonomy );
		}

		/**
		 * @param object $term The Term object.
		 * @param array $taxonomy The Taxonomy name.
		 */
		$term->admeta = (array) apply_filters( 'the_seo_framework_term_meta', $term->admeta, $term, $taxonomy );

		return $term;
	}

	/**
	 * Add AutoDescription term-meta data to functions that return multiple terms.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $terms    Database row objects.
	 * @param string $taxonomy Taxonomy name that $terms are part of.
	 * @return array $terms Database row objects.
	 */
	public function get_terms_filter( array $terms, $taxonomy ) {

		foreach( $terms as $term )
			$term = $this->get_term_filter( $term, $taxonomy );

		return $terms;
	}

	/**
	 * Save taxonomy meta data.
	 * Fires when a user edits and saves a taxonomy.
	 *
	 * @since 2.1.8
	 *
	 * @param integer $term_id Term ID.
	 * @param integer $tt_id   Term Taxonomy ID.
	 * @return void Early on AJAX call.
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
	 * Delete term meta data.
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

	/**
	 * Fetch set Term data.
	 *
	 * @param object|null $term The TT object, if it isn't set, one is fetched.
	 *
	 * @since 2.6.0
	 *
	 * @return array $data The SEO Framework TT data.
	 */
	public function get_term_data( $term = null ) {

		if ( is_null( $term ) ) {
			if ( $this->is_author() ) {
				//* Special handling.
				return null;
			}

			$term = $this->fetch_the_term();
		}

		if ( $term ) {
			$data = array();

			$data['title'] = isset( $term->admeta['doctitle'] ) ? $term->admeta['doctitle'] : '';
			$data['description'] = isset( $term->admeta['description'] ) ? $term->admeta['description'] : '';
			$data['noindex'] = isset( $term->admeta['noindex'] ) ? $term->admeta['noindex'] : '';
			$data['nofollow'] = isset( $term->admeta['nofollow'] ) ? $term->admeta['nofollow'] : '';
			$data['noarchive'] = isset( $term->admeta['noarchive'] ) ? $term->admeta['noarchive'] : '';
			$flag = isset( $term->admeta['saved_flag'] ) ? (bool) $term->admeta['saved_flag'] : false;

			//* Genesis data fetch. This will override our options with Genesis options on save.
			if ( false === $flag && isset( $term->meta ) ) {
				$data['title'] = empty( $data['title'] ) && isset( $term->meta['doctitle'] ) 				? $term->meta['doctitle'] : $data['noindex'];
				$data['description'] = empty( $data['description'] ) && isset( $term->meta['description'] )	? $term->meta['description'] : $data['description'];
				$data['noindex'] = empty( $data['noindex'] ) && isset( $term->meta['noindex'] ) 			? $term->meta['noindex'] : $data['noindex'];
				$data['nofollow'] = empty( $data['nofollow'] ) && isset( $term->meta['nofollow'] )			? $term->meta['nofollow'] : $data['nofollow'];
				$data['noarchive'] = empty( $data['noarchive'] ) && isset( $term->meta['noarchive'] )		? $term->meta['noarchive'] : $data['noarchive'];
			}

			return $data;
		}

		//* Return null if no term can be set.
		return null;
	}

	/**
	 * Try to fetch a term if none can be found.
	 *
	 * @since 2.6.0
	 * @access private
	 *
	 * @param int $id The possible taxonomy Term ID.
	 * @return null|object The Term object.
	 */
	public function fetch_the_term( $id = '' ) {

		static $term = array();

		if ( isset( $term[$id] ) )
			return $term[$id];

		//* Return null if no term can be set.
		if ( false === $this->is_archive() )
			return false;

		if ( $this->is_admin() ) {
			global $current_screen;

			if ( isset( $current_screen->taxonomy ) ) {
				$term_id = $id ? $id : $this->get_admin_term_id();
				$term[$id] = get_term_by( 'id', $term_id, $current_screen->taxonomy );
			}
		} else {
			if ( $this->is_category() || $this->is_tag() ) {
				$term[$id] = get_queried_object();
			} else if ( $this->is_tax() ) {
				$term[$id] = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
			}
		}

		if ( isset( $term[$id] ) )
			return $term[$id];

		return $term[$id] = false;
	}

	/**
	 * Fetch Tax labels
	 *
	 * @since 2.3.1
	 * @staticvar object $labels
	 *
	 * @param string $tax_type the Taxonomy type.
	 * @return object|null with all the labels as member variables
	 */
	public function get_tax_labels( $tax_type ) {

		static $labels = null;

		if ( isset( $labels ) )
			return $labels;

		$tax_object = get_taxonomy( $tax_type );

		if ( is_object( $tax_object ) )
			return $labels = (object) $tax_object->labels;

		//* Nothing found.
		return null;
	}

	/**
	 * Get the current screen term labels.
	 *
	 * @since 2.6.0
	 * @staticvar string $term_name : Caution: This function only runs once per screen and doesn't check the term type more than once.
	 *
	 * @param object $term The Taxonomy Term object.
	 * @param bool $singular Whether to fetch a singular or plural name.
	 * @param bool $fallback Whether to fallback on a generic name.
	 * @param bool $use_cache Whether to read from cache.
	 * @return string the Term name.
	 */
	protected function get_the_term_name( $term, $singular = true, $fallback = true, $use_cache = true ) {

		if ( false === $use_cache ) {
			//* No cache. Short circuit.

			if ( $term && is_object( $term ) ) {
				$tax_type = $term->taxonomy;
				$term_labels = $this->get_tax_labels( $tax_type );

				if ( $singular ) {
					if ( isset( $term_labels->singular_name ) )
						return $term_labels->singular_name;
				} else {
					if ( isset( $term_labels->name ) )
						return $term_labels->name;
				}
			}

			if ( $fallback ) {
				//* Fallback to Page as it is generic.
				if ( $singular )
					return __( 'Page', 'autodescription' );

				return __( 'Pages', 'autodescription' );
			}
		}

		static $term_name = array();

		if ( isset( $term_name[$singular] ) )
			return $term_name[$singular];

		if ( $term && is_object( $term ) ) {
			$tax_type = $term->taxonomy;

			static $term_labels = null;

			/**
			 * Dynamically fetch the term name.
			 *
			 * @since 2.3.1
			 */
			if ( is_null( $term_labels ) )
				$term_labels = $this->get_tax_labels( $tax_type );

			if ( $singular ) {
				if ( isset( $term_labels->singular_name ) )
					return $term_name[$singular] = $term_labels->singular_name;
			} else {
				if ( isset( $term_labels->name ) )
					return $term_name[$singular] = $term_labels->name;
			}
		}

		if ( $fallback ) {
			//* Fallback to Page as it is generic.
			if ( $singular )
				return $term_name[$singular] = __( 'Page', 'autodescription' );

			return $term_name[$singular] = __( 'Pages', 'autodescription' );
		}

		return $term_name[$singular] = '';
	}

}
