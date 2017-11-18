<?php
/**
 * @package The_SEO_Framework\Classes
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\Term_Data
 *
 * Holds Term and Taxonomy data.
 *
 * @since 2.8.0
 */
class Term_Data extends Post_Data {

	/**
	 * Constructor, load parent constructor.
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Initializes term meta data filters and functions.
	 *
	 * @since 2.7.0
	 * @since 3.0.0 No longer checks for admin query.
	 */
	public function initialize_term_meta() {
		\add_action( 'edit_term', array( $this, 'update_term_meta' ), 10, 2 );
		\add_action( 'delete_term', array( $this, 'delete_term_meta' ), 10, 2 );
	}

	/**
	 * Determines if current query handles term meta.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	public function is_term_meta_capable() {
		return $this->is_category() || $this->is_tag() || $this->is_tax() || \is_post_type_archive();
	}

	/**
	 * Returns and caches term meta for the current query.
	 *
	 * @since 3.0.0
	 * @staticvar array $cache
	 *
	 * @return array The current term meta.
	 */
	public function get_current_term_meta() {

		static $cache;

		if ( isset( $cache ) )
			return $cache ?: array();

		if ( $this->is_term_meta_capable() ) {
			$cache = $this->get_term_meta( \get_queried_object_id() ) ?: false;
		} else {
			$cache = false;
		}

		return $cache ?: array();
	}

	/**
	 * Returns term meta data from ID.
	 * Returns Genesis 2.3.0+ data if no term meta data is set.
	 *
	 * @since 2.7.0
	 * @since 2.8.0 : Added filter.
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

		$data = \get_term_meta( $term_id, THE_SEO_FRAMEWORK_TERM_OPTIONS, true );

		//* Evaluate merely by presence.
		if ( isset( $data['saved_flag'] ) ) {
			/**
			 * Applies filters 'the_seo_framework_current_term_meta'.
			 *
			 * @since 3.0.0
			 *
			 * @param array $data The current term data.
			 * @param int   $term_id The term ID.
			 */
			return $cache[ $term_id ] = \apply_filters( 'the_seo_framework_current_term_meta', $data, $term_id );
		}

		/**
		 * Applies filters 'the_seo_framework_get_term_meta'.
		 * NOTE: Only works before TSF sets its saved - flag. To be used prior to migration.
		 *
		 * @since 2.8.0
		 *
		 * @param array $data The term data.
		 * @param int $term_id The current Term ID.
		 */
		$data = \apply_filters( 'the_seo_framework_get_term_meta', array(), $term_id );

		return $cache[ $term_id ] = $data;
	}

	/**
	 * Returns an array of default term options.
	 *
	 * @since 2.7.0
	 *
	 * @return array The Term Metadata default options.
	 */
	public function get_term_meta_defaults() {
		/**
		 * Applies filters 'the_seo_framework_term_meta_defaults' : Array
		 * @since 2.1.8
		 */
		return (array) \apply_filters( 'the_seo_framework_term_meta_defaults', array(
			'doctitle'    => '',
			'description' => '',
			'noindex'     => 0,
			'nofollow'    => 0,
			'noarchive'   => 0,
			'saved_flag'  => 0, // Don't touch, used to prevent data conflict with Genesis.
		) );
	}

	/**
	 * Sanitizes and saves term meta data when a term is altered.
	 *
	 * @since 2.7.0
	 * @securitycheck 3.0.0 OK.
	 *
	 * @param int $term_id     Term ID.
	 * @param int $tt_id       Term Taxonomy ID.
	 * @param string $taxonomy Taxonomy Slug
	 * @return void Early on AJAX call.
	 */
	public function update_term_meta( $term_id, $tt_id, $taxonomy = '' ) {

		if ( $this->doing_ajax() )
			return;

		//* Check again against ambiguous injection.
		if ( isset( $_POST['_wpnonce'] ) && \wp_verify_nonce( $_POST['_wpnonce'], 'update-tag_' . $term_id ) ) :

			$data = isset( $_POST['autodescription-meta'] ) ? (array) $_POST['autodescription-meta'] : array();
			$data = \wp_parse_args( $data, $this->get_term_meta_defaults() );

			foreach ( (array) $data as $key => $value ) :
				switch ( $key ) :
					case 'doctitle' :
						$data[ $key ] = $this->s_title_raw( $value );
						continue 2;

					case 'description' :
						$data[ $key ] = $this->s_description_raw( $value );
						continue 2;

					case 'noindex' :
					case 'nofollow' :
					case 'noarchive' :
					case 'saved_flag' :
						$data[ $key ] = $this->s_one_zero( $value );
						continue 2;

					default :
						// Not implemented for compatibility reasons.
						// unset( $data[ $key ] );
						break;
				endswitch;
			endforeach;

			\update_term_meta( $term_id, THE_SEO_FRAMEWORK_TERM_OPTIONS, $data );
		endif;
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
		$data = \get_term_meta( $term_id, THE_SEO_FRAMEWORK_TERM_OPTIONS, true );

		if ( is_array( $data ) ) {
			foreach ( $this->get_term_meta_defaults() as $key => $value ) {
				unset( $data[ $key ] );
			}
		}

		if ( empty( $data ) ) {
			\delete_term_meta( $term_id, THE_SEO_FRAMEWORK_TERM_OPTIONS );
		} else {
			\update_term_meta( $term_id, THE_SEO_FRAMEWORK_TERM_OPTIONS, $data );
		}
	}

	/**
	 * Try to fetch a term if none can be found.
	 *
	 * @since 2.6.0
	 * @since 3.0.0 Can now get custom post type objects.
	 * @access private
	 * @todo deprecate
	 *
	 * @param int $id The possible taxonomy Term ID.
	 * @return false|object The Term object.
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
				$term[ $id ] = \get_term_by( 'id', $term_id, $current_screen->taxonomy );
			}
		} else {
			if ( $this->is_category() || $this->is_tag() ) {
				$term[ $id ] = \get_queried_object();
			} elseif ( $this->is_tax() ) {
				$term[ $id ] = \get_term_by( 'slug', \get_query_var( 'term' ), \get_query_var( 'taxonomy' ) );
			} elseif ( \is_post_type_archive() ) {
				$term[ $id ] = \get_post_type_object( \get_query_var( 'post_type' ) );
			}
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

		$tax_object = \get_taxonomy( $tax_type );

		if ( is_object( $tax_object ) )
			return $labels = (object) $tax_object->labels;

		//* Nothing found.
		return null;
	}

	/**
	 * Get the current screen term labels.
	 *
	 * @since 2.6.0
	 * @since 2.9.4 Added $term->label and $term->labels->singular_name as additional fallbacks.
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
		} elseif ( isset( $term->label ) ) {
			return $term->label;
		} elseif ( isset( $term->labels->singular_name ) ) {
			return $term->labels->singular_name;
		}

		if ( $fallback ) {
			//* Fallback to Page as it is generic.
			if ( $singular )
				return $term_name[ $singular ] = \esc_html__( 'Page', 'autodescription' );

			return $term_name[ $singular ] = \esc_html__( 'Pages', 'autodescription' );
		}

		return $term_name[ $singular ] = '';
	}

	/**
	 * Returns hierarchical taxonomies for post type.
	 *
	 * @since 3.0.0
	 *
	 * @param string $get       Whether to get the names or objects.
	 * @param string $post_type The post type. Will default to current post type.
	 * @return array The post type objects or names.
	 */
	public function get_hierarchical_taxonomies_as( $get = 'objects', $post_type = '' ) {

		if ( ! $post_type )
			$post_type = \get_post_type( $this->get_the_real_ID() );

		if ( ! $post_type )
			return array();

		$taxonomies = \get_object_taxonomies( $post_type, 'objects' );
		$taxonomies = array_filter( $taxonomies, function( $t ) {
			return $t->hierarchical;
		} );

		switch ( $get ) {
			case 'names' :
				$taxonomies = array_keys( $taxonomies );
				break;

			default :
			case 'objects' :
				break;
		}

		return $taxonomies;
	}
}
