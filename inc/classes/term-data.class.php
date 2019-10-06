<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Term_Data
 * @subpackage The_SEO_Framework\Data
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
	 * @since 4.0.0
	 */
	public function init_term_meta() {
		\add_action( 'edit_term', [ $this, '_update_term_meta' ], 10, 3 );
		\add_action( 'delete_term', [ $this, '_delete_term_meta' ], 10, 3 );
	}

	/**
	 * Determines if current query handles term meta.
	 *
	 * @since 3.0.0
	 * @since 4.0.0 No longer lists post type archives as term-meta capable. It's not a taxonomy.
	 *
	 * @return bool
	 */
	public function is_term_meta_capable() {
		return $this->is_category() || $this->is_tag() || $this->is_tax();
	}

	/**
	 * Returns the term meta item by key.
	 *
	 * @param string $item      The item to get.
	 * @param int    $term_id   The Term ID.
	 * @param bool   $use_cache Whether to use caching; only has effect when $term_id is set.
	 * @return mixed The term meta item. Null when not found.
	 */
	public function get_term_meta_item( $item, $term_id = 0, $use_cache = true ) {

		if ( ! $term_id ) {
			$meta = $this->get_current_term_meta();
		} else {
			$meta = $this->get_term_meta( $term_id, $use_cache );
		}

		return isset( $meta[ $item ] ) ? $meta[ $item ] : null;
	}

	/**
	 * Returns and caches term meta for the current query.
	 *
	 * @since 3.0.0
	 * @since 4.0.1 Now uses the filterable `get_the_real_ID()`
	 * @staticvar array $cache
	 *
	 * @return array The current term meta.
	 */
	public function get_current_term_meta() {

		static $cache;

		if ( isset( $cache ) )
			return $cache;

		if ( $this->is_term_meta_capable() ) {
			$cache = $this->get_term_meta( $this->get_the_real_ID() ) ?: [];
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
	 * @since 4.0.0 1. Removed deprecated filter.
	 *              2. Now fills in defaults.
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

		/**
		 * We can't trust the filter to always contain the expected keys.
		 * However, it may contain more keys than we anticipated. Merge them.
		 */
		$defaults = array_merge(
			$this->get_unfiltered_term_meta_defaults(),
			$this->get_term_meta_defaults( $term_id )
		);

		$meta = \get_term_meta( $term_id, THE_SEO_FRAMEWORK_TERM_OPTIONS, true ) ?: [];

		static $has_deprecated_filter = null;
		if ( null === $has_deprecated_filter && \has_filter( 'the_seo_framework_current_term_meta' ) ) {
			$has_deprecated_filter = true;
			$this->_deprecated_filter( 'the_seo_framework_current_term_meta', '4.0.0', 'get_term_metadata' );
		}

		if ( $has_deprecated_filter && $meta ) {
			/**
			 * @since 3.0.0
			 * @since 4.0.0 Deprecated.
			 * @deprecated
			 * @param array $meta The CURRENT term metadata.
			 * @param int   $term_id The term ID.
			 */
			$meta = \apply_filters( 'the_seo_framework_current_term_meta', $meta, $term_id );

			/**
			 * Filter the extraneous term meta items based on defaults' keys.
			 * This is redundant, but in line with the requirement at `get_post_meta()`
			 * where we get all metadata without a key.
			 *
			 * @see `$this->s_term_meta()`, which strips them out, already. As such,
			 * we only use this when the (deprecated) filter is used.
			 */
			$meta = array_intersect_key(
				$meta,
				$defaults
			);
		}

		return $cache[ $term_id ] = array_merge( $defaults, $meta );
	}

	/**
	 * Returns an array of default term options.
	 *
	 * @since 2.7.0
	 * @since 3.1.0 This is now always used.
	 * @since 4.0.0 : 1. Added $term_id parameter.
	 *                2. Added 'redirect' value.
	 *                3. Added 'title_no_blog_name' value.
	 *                4. Removed 'saved_flag' value.
	 *
	 * @param int $term_id The term ID.
	 * @return array The Term Metadata default options.
	 */
	public function get_term_meta_defaults( $term_id = 0 ) {
		/**
		 * @since 2.1.8
		 * @param array $defaults
		 * @param int   $term_id The current term ID.
		 */
		return (array) \apply_filters_ref_array(
			'the_seo_framework_term_meta_defaults',
			[
				$this->get_unfiltered_term_meta_defaults(),
				$term_id ?: $this->get_the_real_ID(),
			]
		);
	}

	/**
	 * Returns the unfiltered term meta defaults.
	 *
	 * @since 4.0.0
	 *
	 * @return array The default, unfiltered, post meta.
	 */
	protected function get_unfiltered_term_meta_defaults() {
		return [
			'doctitle'           => '',
			'title_no_blog_name' => 0,
			'description'        => '',
			'og_title'           => '',
			'og_description'     => '',
			'tw_title'           => '',
			'tw_description'     => '',
			'social_image_url'   => '',
			'social_image_id'    => 0,
			'canonical'          => '',
			'noindex'            => 0,
			'nofollow'           => 0,
			'noarchive'          => 0,
			'redirect'           => '',
		];
	}

	/**
	 * Sanitizes and saves term meta data when a term is altered.
	 *
	 * @since 2.7.0
	 * @since 4.0.0: 1. Renamed from `update_term_meta`
	 *               2. noindex, nofollow, noarchive are now converted to qubits.
	 *               3. Added new keys to sanitize.
	 *               4. Now marked as private.
	 *               5. Added more sanity protection.
	 *               6. No longer runs when no `autodescription-meta` POST data is sent.
	 *               7. Now uses the current term meta to set new values.
	 *               8. No longer deletes meta from abstracting plugins on save when they're deactivated.
	 *               9. Now allows updating during `WP_AJAX`.
	 * @securitycheck 3.0.0 OK.
	 * @access private
	 *         Use $this->save_term_meta() instead.
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 */
	public function _update_term_meta( $term_id, $tt_id, $taxonomy ) {
		// phpcs:disable, WordPress.Security.NonceVerification

		if ( ! empty( $_POST['autodescription-quick'] ) ) {
			$this->update_quick_edit_term_meta( $term_id, $tt_id, $taxonomy );
		} elseif ( ! empty( $_POST['autodescription-meta'] ) ) {
			$this->update_term_edit_term_meta( $term_id, $tt_id, $taxonomy );
		}

		// phpcs:enable, WordPress.Security.NonceVerification
	}

	/**
	 * Overwrites all of the term meta on term-edit.
	 *
	 * @since 4.0.0
	 * @since 4.0.2 1: Now tests for valid term ID in the term object.
	 *              2: Now continues using the filtered term object.
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 * @return void
	 */
	protected function update_term_edit_term_meta( $term_id, $tt_id, $taxonomy ) {

		$term = \get_term( $term_id, $taxonomy );

		// We could test for is_wp_error( $term ), but this is more to the point.
		if ( empty( $term->term_id ) ) return;

		//* Check again against ambiguous injection...
		// Note, however: function wp_update_term() already performs all these checks for us before firing this callback's action.
		if ( ! \current_user_can( 'edit_term', $term->term_id ) ) return;
		if ( ! isset( $_POST['_wpnonce'] ) ) return;
		if ( ! \wp_verify_nonce( \stripslashes_from_strings_only( $_POST['_wpnonce'] ), 'update-tag_' . $term->term_id ) ) return;

		$data = (array) $_POST['autodescription-meta'];

		$this->save_term_meta( $term->term_id, $tt_id, $taxonomy, $data );
	}

	/**
	 * Overwrites a part of the term meta on quick-edit.
	 *
	 * @since 4.0.0
	 * @since 4.0.2 1: Now tests for valid term ID in the term object.
	 *              2: Now continues using the filtered term object.
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 * @return void
	 */
	protected function update_quick_edit_term_meta( $term_id, $tt_id, $taxonomy ) {

		$term = \get_term( $term_id, $taxonomy );

		// We could test for is_wp_error( $term ), but this is more to the point.
		if ( empty( $term->term_id ) ) return;

		//* Check again against ambiguous injection...
		// Note, however: function wp_ajax_inline_save_tax() already performs all these checks for us before firing this callback's action.
		if ( ! \current_user_can( 'edit_term', $term->term_id ) ) return;
		if ( ! \check_ajax_referer( 'taxinlineeditnonce', '_inline_edit', false ) ) return;

		// Unlike the term-edit saving, we don't reset the data, just overwrite what's given.
		// This is because we only update a portion of the meta.
		$data = array_merge(
			$this->get_term_meta( $term->term_id, false ),
			(array) $_POST['autodescription-quick']
		);

		$this->save_term_meta( $term->term_id, $tt_id, $taxonomy, $data );
	}

	/**
	 * Updates single term meta value.
	 *
	 * Note that this method can be more resource intensive than you intend it to be,
	 * as it reprocesses all term meta.
	 *
	 * @since 4.0.0
	 * @since 4.0.2 1: Now tests for valid term ID in the term object.
	 *              2: Now continues using the filtered term object.
	 * @uses $this->save_term_meta() to process all data.
	 *
	 * @param string $item     The item to update.
	 * @param mixed  $value    The value the item should be at.
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 */
	public function update_single_term_meta_item( $item, $value, $term_id, $tt_id, $taxonomy ) {

		$term = \get_term( $term_id, $taxonomy );

		// We could test for is_wp_error( $term ), but this is more to the point.
		if ( empty( $term->term_id ) ) return;

		$meta          = $this->get_term_meta( $term->term_id, false );
		$meta[ $item ] = $value;

		$this->save_term_meta( $term->term_id, $tt_id, $taxonomy, $meta );
	}

	/**
	 * Updates term meta from input.
	 *
	 * @since 4.0.0
	 * @since 4.0.2 1: Now tests for valid term ID in the term object.
	 *              2: Now continues using the filtered term object.
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term Taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 * @param array  $data     The data to save.
	 */
	public function save_term_meta( $term_id, $tt_id, $taxonomy, array $data ) {

		$term = \get_term( $term_id, $taxonomy );

		// We could test for is_wp_error( $term ), but this is more to the point.
		if ( empty( $term->term_id ) ) return;

		$data = (array) \wp_parse_args( $data, $this->get_term_meta_defaults( $term->term_id ) );
		$data = $this->s_term_meta( $data );

		/**
		 * @since 3.1.0
		 * @param array  $data     The data that's going to be saved.
		 * @param int    $term_id  The term ID.
		 * @param int    $tt_id    The term taxonomy ID.
		 * @param string $taxonomy The taxonomy slug.
		 */
		$data = (array) \apply_filters_ref_array(
			'the_seo_framework_save_term_data',
			[
				$data,
				$term->term_id,
				$tt_id,
				$taxonomy,
			]
		);

		\update_term_meta( $term->term_id, THE_SEO_FRAMEWORK_TERM_OPTIONS, $data );
	}

	/**
	 * Delete term meta data when a term is deleted.
	 * Deletes only the default data keys; or everything when only that is present.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term Taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 */
	public function _delete_term_meta( $term_id, $tt_id, $taxonomy ) { // phpcs:ignore, VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$this->delete_term_meta( $term_id );
	}

	/**
	 * Deletes term meta.
	 * Deletes only the default data keys; or everything when only that is present.
	 *
	 * @since 2.7.0
	 * @since 4.0.0 Removed 2nd, unused, parameter.
	 *
	 * @param int $term_id Term ID.
	 */
	public function delete_term_meta( $term_id ) {

		//* If this results in an empty data string, all data has already been removed by WP core.
		$data = \get_term_meta( $term_id, THE_SEO_FRAMEWORK_TERM_OPTIONS, true );

		if ( is_array( $data ) ) {
			foreach ( $this->get_term_meta_defaults( $term_id ) as $key => $value ) {
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
		$taxonomies = array_filter(
			$taxonomies,
			function( $t ) {
				return $t->hierarchical;
			}
		);

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
