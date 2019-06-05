<?php
/**
 * @package The_SEO_Framework\Classes
 */
namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
	 * Initializes term meta data filters and functions.
	 *
	 * @since 3.3.0
	 */
	public function init_term_meta() {
		\add_action( 'edit_term', [ $this, 'update_term_meta' ], 10, 2 );
		\add_action( 'delete_term', [ $this, 'delete_term_meta' ], 10, 2 );
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
			return $cache;

		if ( $this->is_term_meta_capable() ) {
			$cache = $this->get_term_meta( \get_queried_object_id() ) ?: [];
		} else {
			$cache = [];
		}

		return $cache;
	}

	/**
	 * Returns term meta data from ID.
	 * Returns Genesis 2.3.0+ data if no term meta data is set via compat module.
	 *
	 * @since 2.7.0
	 * @since 2.8.0 Added filter.
	 * @since 3.0.0 Added filter.
	 * @since 3.1.0 Deprecated filter.
	 * @since 3.3.0 Removed deprecated filter.
	 * @staticvar array $cache
	 *
	 * @param int  $term_id The Term ID.
	 * @param bool $use_cache Whether to use caching.
	 * @return array The term meta data.
	 */
	public function get_term_meta( $term_id, $use_cache = true ) {

		if ( $use_cache ) {
			static $cache = [];

			if ( isset( $cache[ $term_id ] ) )
				return $cache[ $term_id ];
		}

		$data = \get_term_meta( $term_id, THE_SEO_FRAMEWORK_TERM_OPTIONS, true );

		//* Evaluate merely by presence. TODO merge this, we no longer use Genesis' for this...
		if ( isset( $data['saved_flag'] ) ) {
			/**
			 * @since 3.0.0
			 * @param array $data The CURRENT term data.
			 * @param int   $term_id The term ID.
			 */
			return $cache[ $term_id ] = \apply_filters( 'the_seo_framework_current_term_meta', $data, $term_id );
		}

		return $cache[ $term_id ] = $this->get_term_meta_defaults();
	}

	/**
	 * Returns an array of default term options.
	 *
	 * @since 2.7.0
	 * @since 3.1.0 This is now always used.
	 * @since 3.3.0 Added redirect value.
	 *
	 * @return array The Term Metadata default options.
	 */
	public function get_term_meta_defaults() {
		/**
		 * @since 2.1.8
		 * @param array $defaults
		 */
		return (array) \apply_filters( 'the_seo_framework_term_meta_defaults', [
			'doctitle'    => '',
			'description' => '',
			'redirect'    => '',
			'noindex'     => 0,
			'nofollow'    => 0,
			'noarchive'   => 0,
			'saved_flag'  => 0, // Don't touch, used to prevent data conflict with Genesis.
		] );
	}

	/**
	 * Sanitizes and saves term meta data when a term is altered.
	 *
	 * @since 2.7.0
	 * @since 3.3.0: 1. noindex, nofollow, noarchive are converted to qubits.
	 *               2. Added new keys to sanitize.
	 *               3. Now marked as private
	 *               4. Added more protection.
	 * @securitycheck 3.0.0 OK.
	 * @access private
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 * @return void Early on AJAX call.
	 */
	public function update_term_meta( $term_id, $tt_id, $taxonomy = '' ) {

		if ( \wp_doing_ajax() ) return;

		//* Check again against ambiguous injection.
		// Note, however: function wp_update_term() already performs all these checks for us.
		if ( ! \current_user_can( 'edit_term', $term_id ) ) return;
		if ( ! isset( $_POST['_wpnonce'] ) ) return;
		if ( ! \wp_verify_nonce( \stripslashes_from_strings_only( $_POST['_wpnonce'] ), 'update-tag_' . $term_id ) ) return;

		// phpcs:ignore -- wp_unslash() will ruin intended slashes.
		$data = isset( $_POST['autodescription-meta'] ) ? (array) $_POST['autodescription-meta'] : [];
		$this->save_term_meta( $term_id, $tt_id, $taxonomy, $data );
	}

	/**
	 * Saves term meta from input.
	 *
	 * @since 3.3.0
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term Taxonomy ID.
	 * @param string $taxonomy Taxonomy slug
	 */
	public function save_term_meta( $term_id, $tt_id, $taxonomy, $data ) {

		$data = \wp_parse_args( $data, $this->get_term_meta_defaults() );

		foreach ( (array) $data as $key => $value ) :
			switch ( $key ) :
				case 'doctitle':
					$data[ $key ] = $this->s_title_raw( $value );
					continue 2;

				case 'description':
					$data[ $key ] = $this->s_description_raw( $value );
					continue 2;

				case 'noindex':
				case 'nofollow':
				case 'noarchive':
					$data[ $key ] = $this->s_qubit( $value );
					continue 2;

				case 'saved_flag':
					$data[ $key ] = $this->s_one_zero( $value );
					continue 2;

				case 'redirect':
					$data[ $key ] = $this->s_redirect_url( $value );
					continue 2;

				default:
					// Not implemented for compatibility reasons.
					// unset( $data[ $key ] );
					break;
			endswitch;
		endforeach;

		/**
		 * @since 3.1.0
		 * @param array  $data     The data that's going to be saved.
		 * @param int    $term_id  The term ID.
		 * @param int    $tt_id    The term taxonomy ID.
		 * @param string $taxonomy The taxonomy slug.
		 */
		$data = (array) \apply_filters_ref_array( 'the_seo_framework_save_term_data', [
			$data,
			$term_id,
			$tt_id,
			$taxonomy,
		] );

		\update_term_meta( $term_id, THE_SEO_FRAMEWORK_TERM_OPTIONS, $data );
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
	 * Tries to fetch a term by $id from query.
	 *
	 * @since 2.6.0
	 * @since 3.0.0 Can now get custom post type objects.
	 * @todo deprecate
	 *
	 * @param int $id The possible taxonomy Term ID.
	 * @return false|object The Term object.
	 */
	public function fetch_the_term( $id = '' ) {

		static $term = [];

		if ( isset( $term[ $id ] ) )
			return $term[ $id ];

		//* Return null if no term can be detected.
		if ( false === $this->is_archive() )
			return false;

		if ( $this->is_admin() ) {
			$taxonomy = $this->get_current_taxonomy();
			if ( $taxonomy ) {
				$term_id     = $id ?: $this->get_the_real_admin_ID();
				$term[ $id ] = \get_term_by( 'id', $term_id, $taxonomy );
			}
		} else {
			if ( $this->is_category() || $this->is_tag() ) {
				$term[ $id ] = \get_queried_object();
			} elseif ( $this->is_tax() ) {
				$term[ $id ] = \get_term_by( 'slug', \get_query_var( 'term' ), \get_query_var( 'taxonomy' ) );
			} elseif ( \is_post_type_archive() ) {
				$post_type = \get_query_var( 'post_type' );
				$post_type = is_array( $post_type ) ? reset( $post_type ) : $post_type;

				$term[ $id ] = \get_post_type_object( $post_type );
			}
		}

		if ( isset( $term[ $id ] ) )
			return $term[ $id ];

		return $term[ $id ] = false;
	}

	/**
	 * Returns the taxonomy type object label. Either plural or singular.
	 *
	 * @since 3.1.0
	 * @see $this->get_post_type_label() For the singular alternative.
	 *
	 * @param string $tax_type The taxonomy type. Required.
	 * @param bool   $singular Wether to get the singlural or plural name.
	 * @return string The Taxonomy Type name/label, if found.
	 */
	public function get_tax_type_label( $tax_type, $singular = true ) {

		$tto = \get_taxonomy( $tax_type );

		return $singular
			? ( isset( $tto->labels->singular_name ) ? $tto->labels->singular_name : '' )
			: ( isset( $tto->labels->name ) ? $tto->labels->name : '' );
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
			return [];

		$taxonomies = \get_object_taxonomies( $post_type, 'objects' );
		$taxonomies = array_filter( $taxonomies, function( $t ) {
			return $t->hierarchical;
		} );

		switch ( $get ) {
			case 'names':
				$taxonomies = array_keys( $taxonomies );
				break;

			default:
			case 'objects':
				break;
		}

		return $taxonomies;
	}
}
