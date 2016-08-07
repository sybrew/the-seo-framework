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
 * Class AutoDescription_TermData
 *
 * Holds Term and Taxonomy data.
 *
 * @since 2.6.0
 */
class AutoDescription_TermData extends AutoDescription_PostData {

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
	 * Constructor, load parent constructor.
	 */
	public function __construct() {
		parent::__construct();

		//* Initialize term meta filters and actions.
		$this->initialize_term_meta();
	}

	/**
	 * Initializes term meta data filters and functions.
	 *
	 * @since 2.7.0
	 */
	public function initialize_term_meta() {

		if ( $this->can_get_term_meta() ) {
			add_action( 'edit_term', array( $this, 'update_term_meta' ), 10, 2 );
			add_action( 'delete_term', array( $this, 'delete_term_meta' ), 10, 2 );
		} else {
			//* Old style term meta data through loop injections.
			add_filter( 'get_term', array( $this, 'get_term_filter' ), 10, 2 );
			add_filter( 'get_terms', array( $this, 'get_terms_filter' ), 10, 2 );

			add_action( 'edit_term', array( $this, 'taxonomy_seo_save' ), 10, 2 );
			add_action( 'delete_term', array( $this, 'term_meta_delete' ), 10, 2 );
		}

	}

	/**
	 * Returns term meta data from ID.
	 * Returns Genesis 2.3.0+ data if no term meta data is set.
	 *
	 * @since 2.7.0
	 * @staticvar array $cache
	 *
	 * @param int $term_id The Term ID.
	 * @param bool $use_cache Whether to use caching.
	 * @return array The term meta data.
	 */
	public function get_term_meta( $term_id, $use_cache = true ) {

		if ( $use_cache ) {
			static $cache = array();

			if ( isset( $cache[ $term_id ] ) )
				return $cache[ $term_id ];
		} else {
			$cache = array();
		}

		$data = get_term_meta( $term_id, THE_SEO_FRAMEWORK_TERM_OPTIONS, true );

		//* Evaluate merely by presence.
		if ( isset( $data['saved_flag'] ) )
			return $cache[ $term_id ] = $data;

		if ( $this->is_theme( 'genesis' ) ) {
			$data = array();
			$data['doctitle'] = get_term_meta( $term_id, 'doctitle', true );
			$data['description'] = get_term_meta( $term_id, 'description', true );
			$data['noindex'] = get_term_meta( $term_id, 'noindex', true );
			$data['nofollow'] = get_term_meta( $term_id, 'nofollow', true );
			$data['noarchive'] = get_term_meta( $term_id, 'noarchive', true );

			return $cache[ $term_id ] = $data;
		}

		return $cache[ $term_id ] = array();
	}

	/**
	 * Returns an array of default term options.
	 *
	 * @since 2.7.0
	 *
	 * @since 2.1.8:
	 * Applies filters array the_seo_framework_term_meta_defaults : Array of default term SEO options
	 *
	 * @return array The Term Metadata default options.
	 */
	public function get_term_meta_defaults() {
		return (array) apply_filters( 'the_seo_framework_term_meta_defaults', array(
			'doctitle'            => '',
			'description'         => '',
			'noindex'             => 0,
			'nofollow'            => 0,
			'noarchive'           => 0,
			'saved_flag'          => 0, // Don't touch, used to prevent data conflict with Genesis.
		) );
	}

	/**
	 * Sanitizes and saves term meta data when a term is altered.
	 *
	 * @since 2.7.0
	 *
	 * @param int $term_id Term ID.
	 * @param int $tt_id   Term Taxonomy ID.
	 * @return void Early on AJAX call.
	 */
	public function update_term_meta( $term_id, $tt_id ) {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			return;

		//* Check again against ambiguous injection.
		check_admin_referer( 'update-tag_' . $term_id );

		$data = isset( $_POST['autodescription-meta'] ) ? (array) map_deep( $_POST['autodescription-meta'], 'esc_attr' ) : array();
		$data = wp_parse_args( $data, $this->get_term_meta_defaults() );

		update_term_meta( $term_id, THE_SEO_FRAMEWORK_TERM_OPTIONS, $data );

	}

	/**
	 * Delete term meta data when a term is deleted.
	 * Delete only the default data keys.
	 *
	 * @since 2.7.0
	 *
	 * @param int $term_id Term ID.
	 * @param int $tt_id   Term Taxonomy ID.
	 */
	public function delete_term_meta( $term_id, $tt_id ) {

		//* If this results in an empty data string, all data has already been removed by WP core.
		$data = get_term_meta( $term_id, THE_SEO_FRAMEWORK_TERM_OPTIONS, true );

		if ( is_array( $data ) ) {
			foreach ( $this->get_term_meta_defaults() as $key => $value ) {
				unset( $data[ $key ] );
			}
		}

		if ( empty( $data ) )
			delete_term_meta( $term_id, THE_SEO_FRAMEWORK_TERM_OPTIONS );
		else
			update_term_meta( $term_id, THE_SEO_FRAMEWORK_TERM_OPTIONS, $data );

	}

	/**
	 * Fetch set Term data.
	 *
	 * @since 2.6.0
	 * @since 2.7.0 Handles term object differently for upgraded database.
	 *
	 * @todo @since 2.8.0 Will no longer use $term.
	 *
	 * @param object|null $term The TT object, if it isn't set, one is fetched.
	 * @param object|null $term_id The term object.
	 * @return array The SEO Framework TT data.
	 */
	public function get_term_data( $term = null, $term_id = 0 ) {

		if ( is_null( $term ) )
			$term = $this->fetch_the_term( $term_id );

		if ( isset( $term->term_id ) ) {
			if ( $this->can_get_term_meta() )
				return $this->get_term_meta( $term->term_id );
			else
				return $this->get_old_term_data( $term );
		}

		//* Return null if no term can be set.
		return null;
	}

	/**
	 * Fetches term metadata array for the inpost term metabox.
	 *
	 * @since 2.7.0
	 *
	 * @param object $term The TT object. Must be assigned.
	 * @return array The SEO Framework TT data.
	 */
	protected function get_old_term_data( $term ) {

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

		if ( isset( $term[ $id ] ) )
			return $term[ $id ];

		//* Return null if no term can be set.
		if ( false === $this->is_archive() )
			return false;

		if ( $this->is_admin() ) {
			global $current_screen;

			if ( isset( $current_screen->taxonomy ) ) {
				$term_id = $id ? $id : $this->get_admin_term_id();
				$term[ $id ] = get_term_by( 'id', $term_id, $current_screen->taxonomy );
			}
		} else {
			if ( $this->is_category() || $this->is_tag() )
				$term[ $id ] = get_queried_object();
			elseif ( $this->is_tax() )
				$term[ $id ] = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
		}

		if ( isset( $term[ $id ] ) )
			return $term[ $id ];

		return $term[ $id ] = false;
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

		if ( $use_cache ) {
			static $term_name = array();

			if ( isset( $term_name[ $singular ] ) )
				return $term_name[ $singular ];
		} else {
			$term_name = array();
		}

		if ( isset( $term->taxonomy ) ) {
			$tax_type = $term->taxonomy;

			static $term_labels = array();

			/**
			 * Dynamically fetch the term name.
			 * @since 2.3.1
			 */
			if ( ! isset( $term_labels[ $tax_type ] ) )
				$term_labels[ $tax_type ] = $this->get_tax_labels( $tax_type );

			if ( $singular ) {
				if ( isset( $term_labels[ $tax_type ]->singular_name ) )
					return $term_name[ $singular ] = $term_labels[ $tax_type ]->singular_name;
			} else {
				if ( isset( $term_labels->name ) )
					return $term_name[ $singular ] = $term_labels[ $tax_type ]->name;
			}
		}

		if ( $fallback ) {
			//* Fallback to Page as it is generic.
			if ( $singular )
				return $term_name[ $singular ] = esc_html__( 'Page', 'autodescription' );

			return $term_name[ $singular ] = esc_html__( 'Pages', 'autodescription' );
		}

		return $term_name[ $singular ] = '';
	}
}
