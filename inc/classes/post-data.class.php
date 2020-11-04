<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Post_Data
 * @subpackage The_SEO_Framework\Data
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2020 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\Post_Data
 *
 * Holds Post data.
 *
 * @since 2.1.6
 */
class Post_Data extends Detect {

	/**
	 * @since 2.7.0
	 * @since 3.2.0 Added '_nonce' suffix.
	 * @var string The inpost nonce name.
	 */
	public $inpost_nonce_name = 'tsf_inpost_seo_settings_nonce';

	/**
	 * @since 2.7.0
	 * @var string The inpost nonce field.
	 */
	public $inpost_nonce_field = 'tsf_inpost_nonce';

	/**
	 * Returns a post SEO meta item by key.
	 *
	 * Unlike other post meta calls, no \WP_Post object is accepted as an input value,
	 * this is done for performance reasons, so we can cache here, instead of relying on
	 * WordPress's cache, where they cast many filters and redundantly sanitize the object.
	 *
	 * When we'll be moving to PHP 7 and later, we'll enforce type hinting.
	 *
	 * @since 4.0.0
	 * @since 4.0.1 Now obtains the real ID when none is supplied.
	 *
	 * @param string $item      The item to get.
	 * @param int    $post_id   The post ID.
	 * @param bool   $use_cache Whether to use caching.
	 */
	public function get_post_meta_item( $item, $post_id = 0, $use_cache = true ) {

		$meta = $this->get_post_meta( $post_id ?: $this->get_the_real_ID(), $use_cache );

		return isset( $meta[ $item ] ) ? $meta[ $item ] : null;
	}

	/**
	 * Returns all registered custom SEO fields for a post.
	 * Memoizes the return value.
	 *
	 * Unlike other post meta calls, no \WP_Post object is accepted as an input value,
	 * this is done for performance reasons, so we can cache here, instead of relying on
	 * WordPress's cache, where they cast many filters and redundantly sanitize the object.
	 *
	 * When we'll be moving to PHP 7 and later, we'll enforce type hinting.
	 *
	 * @since 4.0.0
	 * @since 4.0.2 Now tests for valid post ID in the post object.
	 *
	 * @param int  $post_id   The post ID.
	 * @param bool $use_cache Whether to use caching.
	 * @return array The post meta.
	 */
	public function get_post_meta( $post_id, $use_cache = true ) {

		if ( $use_cache ) {
			static $cache = [];

			if ( isset( $cache[ $post_id ] ) )
				return $cache[ $post_id ];
		}

		// get_post_meta() requires a valid post ID. Make sure that post exists.
		$post = \get_post( $post_id );

		if ( empty( $post->ID ) )
			return $cache[ $post_id ] = [];

		/**
		 * We can't trust the filter to always contain the expected keys.
		 * However, it may contain more keys than we anticipated. Merge them.
		 */
		$defaults = array_merge(
			$this->get_unfiltered_post_meta_defaults(),
			$this->get_post_meta_defaults( $post->ID )
		);

		// Filter the post meta items based on defaults' keys.
		$meta = array_intersect_key(
			\get_post_meta( $post->ID ), // Gets all post meta. This is a discrepancy with get_term_meta()!
			$defaults
		);

		// WP converts all entries to arrays, because we got ALL entries. Disarray!
		foreach ( $meta as $key => $value ) {
			$meta[ $key ] = $value[0];
		}

		/**
		 * @since 4.0.5
		 * @note Do not delete/unset/add indexes! It'll cause errors.
		 * @param array $meta    The current post meta.
		 * @param int   $post_id The post ID.
		 */
		$meta = \apply_filters_ref_array(
			'the_seo_framework_post_meta',
			[
				array_merge( $defaults, $meta ),
				$post->ID,
			]
		);

		// Cache using the input ID, otherwise invalid queries can bypass the cache.
		return $cache[ $post_id ] = $meta;
	}

	/**
	 * Returns the post meta defaults.
	 *
	 * Unlike other post meta calls, no \WP_Post object is accepted as an input value,
	 * this is done for performance reasons, so we can cache here, instead of relying on
	 * WordPress's cache, where they cast many filters and redundantly sanitize the object.
	 *
	 * When we'll be moving to PHP 7 and later, we'll enforce type hinting.
	 *
	 * @since 4.0.0
	 *
	 * @param int $post_id The post ID.
	 * @return array The default post meta.
	 */
	public function get_post_meta_defaults( $post_id = 0 ) {
		/**
		 * @since 3.1.0
		 * @param array    $defaults
		 * @param integer  $post_id Post ID.
		 * @param \WP_Post $post    Post object.
		 */
		return (array) \apply_filters_ref_array(
			'the_seo_framework_inpost_seo_save_defaults', // TODO rename to the_seo_framework_post_meta_defaults. 4.1.0?
			[
				$this->get_unfiltered_post_meta_defaults(),
				$post_id,
				\get_post( $post_id ),
			]
		);
	}

	/**
	 * Returns the unfiltered post meta defaults.
	 *
	 * @since 4.0.0
	 *
	 * @return array The default, unfiltered, post meta.
	 */
	protected function get_unfiltered_post_meta_defaults() {
		return [
			'_genesis_title'          => '',
			'_tsf_title_no_blogname'  => 0, //? The prefix I should've used from the start...
			'_genesis_description'    => '',
			'_genesis_canonical_uri'  => '',
			'redirect'                => '', //! Will be displayed in custom fields when set...
			'_social_image_url'       => '',
			'_social_image_id'        => 0,
			'_genesis_noindex'        => 0,
			'_genesis_nofollow'       => 0,
			'_genesis_noarchive'      => 0,
			'exclude_local_search'    => 0, //! Will be displayed in custom fields when set...
			'exclude_from_archive'    => 0, //! Will be displayed in custom fields when set...
			'_open_graph_title'       => '',
			'_open_graph_description' => '',
			'_twitter_title'          => '',
			'_twitter_description'    => '',
		];
	}

	/**
	 * Updates single post meta value.
	 *
	 * Note that this method can be more resource intensive than you intend it to be,
	 * as it reprocesses all post meta.
	 *
	 * @since 4.0.0
	 * @uses $this->save_post_meta() to process all data.
	 *
	 * @param string           $item  The item to update.
	 * @param mixed            $value The value the item should be at.
	 * @param \WP_Post|integer $post  The post object or post ID.
	 */
	public function update_single_post_meta_item( $item, $value, $post ) {

		$post = \get_post( $post );

		if ( ! $post ) return;

		$meta          = $this->get_post_meta( $post->ID, false );
		$meta[ $item ] = $value;

		$this->save_post_meta( $post->ID, $meta );
	}

	/**
	 * Save post meta / custom field data for a singular post type.
	 *
	 * @since 4.0.0
	 *
	 * @param \WP_Post|integer $post The post object or post ID.
	 * @param array            $data The post meta fields, will be merged with the defaults.
	 */
	public function save_post_meta( $post, array $data ) {

		$post = \get_post( $post );

		if ( ! $post ) return;

		$data = (array) \wp_parse_args( $data, $this->get_post_meta_defaults( $post->ID ) );
		$data = $this->s_post_meta( $data );

		if ( \has_filter( 'the_seo_framework_save_custom_fields' ) ) {
			$this->_deprecated_filter( 'the_seo_framework_save_custom_fields', '4.0.0', 'the_seo_framework_save_post_meta' );
			/**
			 * @since 3.1.0
			 * @since 4.0.0 Deprecated.
			 * @deprecated
			 * @param array    $data The data that's going to be saved.
			 * @param \WP_Post $post The post object.
			 */
			$data = (array) \apply_filters_ref_array(
				'the_seo_framework_save_custom_fields',
				[
					$data,
					$post,
				]
			);
		}

		/**
		 * @since 4.0.0
		 * @param array    $data The data that's going to be saved.
		 * @param \WP_Post $post The post object.
		 */
		$data = (array) \apply_filters_ref_array(
			'the_seo_framework_save_post_meta',
			[
				$data,
				$post,
			]
		);

		// Cycle through $data, insert value or delete field
		foreach ( (array) $data as $field => $value ) {
			// Save $value, or delete if the $value is empty.
			// We can safely assume no one-zero/qubit options pass through here thanks to sanitization earlier--alleviating database weight.
			if ( $value || ( \is_string( $value ) && \strlen( $value ) ) ) {
				\update_post_meta( $post->ID, $field, $value );
			} else {
				// This is fine for as long as we merge the getter values with the defaults.
				\delete_post_meta( $post->ID, $field );
			}
		}
	}

	/**
	 * Saves the SEO settings when we save an attachment.
	 *
	 * This is a passthrough method for `_update_post_meta()`.
	 * Sanity checks are handled deeper.
	 *
	 * @since 3.0.6
	 * @since 4.0.0 Renamed from `inattachment_seo_save`
	 * @uses $this->_update_post_meta()
	 * @access private
	 *
	 * @param int $post_id The post ID.
	 * @return void
	 */
	public function _update_attachment_meta( $post_id ) {
		$this->_update_post_meta( $post_id, \get_post( $post_id ) );
	}

	/**
	 * Saves the Post SEO Meta settings on quick-edit, bulk-edit, or post-edit.
	 *
	 * @since 2.0.0
	 * @since 2.9.3 Added 'exclude_from_archive'.
	 * @since 4.0.0 1. Renamed from `inpost_seo_save`
	 *              2. Now allows updating during `WP_CRON`.
	 *              3. Now allows updating during `WP_AJAX`.
	 * @access private
	 *
	 * @param int      $post_id The post ID. Unused, but sent through filter.
	 * @param \WP_Post $post    The post object.
	 */
	public function _update_post_meta( $post_id, $post ) {
		// phpcs:disable, WordPress.Security.NonceVerification

		if ( ! empty( $_POST['autodescription-quick'] ) ) {
			$this->update_quick_edit_post_meta( $post_id, $post );
		} elseif ( ! empty( $_REQUEST['autodescription-bulk'] ) ) {
			// This is sent via GET. Keep using $_REQUEST for future-compatibility.
			$this->update_bulk_edit_post_meta( $post_id, $post );
		} elseif ( ! empty( $_POST['autodescription'] ) ) {
			$this->update_post_edit_post_meta( $post_id, $post );
		}

		// phpcs:enable, WordPress.Security.NonceVerification
	}

	/**
	 * Overwrites all of the post meta on post-edit.
	 *
	 * @since 4.0.0
	 *
	 * @param int      $post_id The post ID. Unused.
	 * @param \WP_Post $post    The post object.
	 * @return void
	 */
	protected function update_post_edit_post_meta( $post_id, $post ) {

		$post = \get_post( $post );

		if ( ! $post ) return;

		/**
		 * Don't try to save the data prior autosave, or revision post (is_preview).
		 *
		 * @TODO find a way to maintain revisions:
		 * @link https://github.com/sybrew/the-seo-framework/issues/48
		 * @link https://johnblackbourn.com/post-meta-revisions-wordpress
		 */
		if ( \wp_is_post_autosave( $post ) ) return;
		if ( \wp_is_post_revision( $post ) ) return;

		$nonce_name   = $this->inpost_nonce_name;
		$nonce_action = $this->inpost_nonce_field;

		// Check that the user is allowed to edit the post
		if ( ! \current_user_can( 'edit_post', $post->ID ) ) return;
		if ( ! isset( $_POST[ $nonce_name ] ) ) return;
		if ( ! \wp_verify_nonce( $_POST[ $nonce_name ], $nonce_action ) ) return;

		$data = (array) $_POST['autodescription'];

		// Perform nonce check and save fields.
		$this->save_post_meta( $post, $data );
	}

	/**
	 * Overwrites a part of the post meta on quick-edit.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Allowed title and description parsing.
	 *
	 * @param int      $post_id The post ID. Unused.
	 * @param \WP_Post $post    The post object.
	 * @return void
	 */
	protected function update_quick_edit_post_meta( $post_id, $post ) {

		$post = \get_post( $post );

		if ( empty( $post->ID ) ) return;

		// Check again against ambiguous injection...
		// Note, however: function wp_ajax_inline_save() already performs all these checks for us before firing this callback's action.
		if ( ! \current_user_can( 'edit_post', $post->ID ) ) return;
		if ( ! \check_ajax_referer( 'inlineeditnonce', '_inline_edit', false ) ) return;

		$new_data = [];

		foreach ( (array) $_POST['autodescription-quick'] as $key => $value ) :
			switch ( $key ) :
				case 'doctitle':
					$new_data['_genesis_title'] = $value;
					break;

				case 'description':
				case 'noindex':
				case 'nofollow':
				case 'noarchive':
					$new_data[ "_genesis_$key" ] = $value;
					break;

				case 'redirect':
					$new_data[ $key ] = $value;
					break;

				case 'canonical':
					$new_data['_genesis_canonical_uri'] = $value;
					break;

				default:
					break;
			endswitch;
		endforeach;

		// Unlike the post-edit saving, we don't reset the data, just overwrite what's given.
		// This is because we only update a portion of the meta.
		$data = array_merge(
			$this->get_post_meta( $post->ID, false ),
			$new_data
		);

		$this->save_post_meta( $post, $data );
	}

	/**
	 * Overwrites a park of the post meta on bulk-edit.
	 *
	 * @since 4.0.0
	 *
	 * @param int      $post_id The post ID. Unused.
	 * @param \WP_Post $post    The post object.
	 * @return void
	 */
	protected function update_bulk_edit_post_meta( $post_id, $post ) {

		$post = \get_post( $post );

		if ( empty( $post->ID ) ) return;

		// Check again against ambiguous injection...
		// Note, however: function bulk_edit_posts() already performs all these checks for us before firing this callback's action.
		if ( ! \current_user_can( 'edit_post', $post->ID ) ) return;

		static $verified_referer = false;
		// Memoize the referer check--if it passes (and doesn't exit/die PHP), we're good to execute subsequently.
		if ( ! $verified_referer ) {
			\check_admin_referer( 'bulk-posts' );
			$verified_referer = true;
		}

		static $new_data = null;

		if ( ! isset( $new_data ) ) {
			$new_data = [];

			// This is sent via GET. Keep using $_REQUEST for future-compatibility.
			foreach ( (array) $_REQUEST['autodescription-bulk'] as $key => $value ) :
				switch ( $key ) :
					case 'noindex':
					case 'nofollow':
					case 'noarchive':
						if ( 'nochange' === $value ) continue 2;
						$new_data[ "_genesis_$key" ] = $value;
						break;

					default:
						break;
				endswitch;
			endforeach;
		}

		// Unlike the post-edit saving, we don't reset the data, just overwrite what's given.
		// This is because we only update a portion of the meta.
		$data = array_merge(
			$this->get_post_meta( $post->ID, false ),
			$new_data
		);

		$this->save_post_meta( $post, $data );
	}

	/**
	 * Saves primary term data for posts.
	 *
	 * @since 3.0.0
	 * @since 4.0.0 1. Now allows updating during `WP_CRON`.
	 *              2. Now allows updating during `WP_AJAX`.
	 * @securitycheck 4.1.0 OK.
	 *
	 * @param int      $post_id The post ID. Unused, but sent through filter.
	 * @param \WP_Post $post    The post object.
	 * @return void
	 */
	public function _save_inpost_primary_term( $post_id, $post ) {

		// The 'autodescription' index should only be used when using the editor.
		// Quick and bulk-edit should be halted here.
		if ( empty( $_POST['autodescription'] ) ) return;

		$post = \get_post( $post );

		if ( empty( $post->ID ) ) return;

		/**
		 * Don't try to save the data prior autosave, or revision post (is_preview).
		 *
		 * @TODO find a way to maintain revisions:
		 * @link https://github.com/sybrew/the-seo-framework/issues/48
		 * @link https://johnblackbourn.com/post-meta-revisions-wordpress
		 */
		if ( \wp_is_post_autosave( $post ) ) return;
		if ( \wp_is_post_revision( $post ) ) return;

		// Check that the user is allowed to edit the post. Nonce checks are done in bulk later.
		if ( ! \current_user_can( 'edit_post', $post->ID ) ) return;

		$post_type = \get_post_type( $post ) ?: false;
		// Can this even fail?
		if ( ! $post_type ) return;

		$_taxonomies = $this->get_hierarchical_taxonomies_as( 'names', $post_type );
		$values      = [];

		foreach ( $_taxonomies as $_taxonomy ) {
			$_post_key = '_primary_term_' . $_taxonomy;

			$values[ $_taxonomy ] = [
				'action' => $this->inpost_nonce_field . '_pt',
				'name'   => $this->inpost_nonce_name . '_pt_' . $_taxonomy,
				'value'  => isset( $_POST['autodescription'][ $_post_key ] ) ? \absint( $_POST['autodescription'][ $_post_key ] ) : 0,
			];
		}

		foreach ( $values as $t => $v ) {
			if ( ! isset( $_POST[ $v['name'] ] ) ) continue;
			if ( \wp_verify_nonce( $_POST[ $v['name'] ], $v['action'] ) ) { // Redundant. Fortified.
				$this->update_primary_term_id( $post->ID, $t, $v['value'] );
			}
		}
	}

	/**
	 * Fetch latest public post/page ID.
	 * Memoizes the return value.
	 *
	 * @since 2.4.3
	 * @since 2.9.3 : 1. Removed object caching.
	 *              : 2. It now uses WP_Query, instead of wpdb.
	 *
	 * @return int Latest Post ID.
	 */
	public function get_latest_post_id() {

		static $post_id = null;

		if ( null !== $post_id )
			return $post_id;

		$query = new \WP_Query( [
			'posts_per_page'   => 1,
			'post_type'        => [ 'post', 'page' ],
			'orderby'          => 'date',
			'order'            => 'DESC',
			'post_status'      => [ 'publish', 'future', 'pending' ],
			'fields'           => 'ids',
			'cache_results'    => false,
			'suppress_filters' => true,
			'no_found_rows'    => true,
		] );

		return $post_id = reset( $query->posts );
	}

	/**
	 * Fetches Post content.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 No longer applies WordPress's default filters.
	 *
	 * @param int $id The post ID.
	 * @return string The post content.
	 */
	public function get_post_content( $id = 0 ) {
		$post = \get_post( $id ?: $this->get_the_real_ID() );
		return empty( $post->post_content ) ? '' : $post->post_content;
	}

	/**
	 * Determines whether the post has a page builder attached to it.
	 * Doesn't use plugin detection features as some builders might be incorporated within themes.
	 *
	 * Detects the following builders:
	 * - Elementor by Elementor LTD
	 * - Divi Builder by Elegant Themes
	 * - Visual Composer by WPBakery
	 * - Page Builder by SiteOrigin
	 * - Beaver Builder by Fastline Media
	 *
	 * @since 2.6.6
	 * @since 3.1.0 Added Elementor detection
	 * @since 4.0.0 Now detects page builders before looping over the meta.
	 * @TODO deprecate?
	 * @ignore unused.
	 *
	 * @param int $post_id The post ID to check.
	 * @return bool
	 */
	public function uses_page_builder( $post_id ) {

		$meta = \get_post_meta( $post_id );

		/**
		 * @since 2.6.6
		 * @since 3.1.0 : 1. Now defaults to `null`
		 *                2. Now, when a boolean (either true or false) is defined, it'll short-circuit this function.
		 * @param boolean|null $detected Whether a builder should be detected.
		 * @param int          $post_id The current Post ID.
		 * @param array        $meta The current post meta.
		 */
		$detected = \apply_filters( 'the_seo_framework_detect_page_builder', null, $post_id, $meta );

		if ( \is_bool( $detected ) )
			return $detected;

		if ( ! $this->detect_page_builder() )
			return false;

		if ( empty( $meta ) )
			return false;

		if ( isset( $meta['_elementor_edit_mode'][0] ) && '' !== $meta['_elementor_edit_mode'][0] && \defined( 'ELEMENTOR_VERSION' ) ) :
			// Elementor by Elementor LTD
			return true;
		elseif ( isset( $meta['_et_pb_use_builder'][0] ) && 'on' === $meta['_et_pb_use_builder'][0] && \defined( 'ET_BUILDER_VERSION' ) ) :
			// Divi Builder by Elegant Themes
			return true;
		elseif ( isset( $meta['_wpb_vc_js_status'][0] ) && 'true' === $meta['_wpb_vc_js_status'][0] && \defined( 'WPB_VC_VERSION' ) ) :
			// Visual Composer by WPBakery
			return true;
		elseif ( isset( $meta['panels_data'][0] ) && '' !== $meta['panels_data'][0] && \defined( 'SITEORIGIN_PANELS_VERSION' ) ) :
			// Page Builder by SiteOrigin
			return true;
		elseif ( isset( $meta['_fl_builder_enabled'][0] ) && '1' === $meta['_fl_builder_enabled'][0] && \defined( 'FL_BUILDER_VERSION' ) ) :
			// Beaver Builder by Fastline Media...
			return true;
		endif;

		return false;
	}

	/**
	 * Determines whether the post has a page builder that renders content dynamically attached to it.
	 * Doesn't use plugin detection features as some builders might be incorporated within themes.
	 *
	 * Detects the following builders:
	 * - Divi Builder by Elegant Themes
	 * - Visual Composer by WPBakery
	 *
	 * @since 4.1.0
	 *
	 * @param int $post_id The post ID to check.
	 * @return bool
	 */
	public function uses_non_html_page_builder( $post_id ) {

		$meta = \get_post_meta( $post_id );

		/**
		 * @since 4.1.0
		 * @param boolean|null $detected Whether a builder should be detected.
		 * @param int          $post_id The current Post ID.
		 * @param array        $meta The current post meta.
		 */
		$detected = \apply_filters( 'the_seo_framework_detect_non_html_page_builder', null, $post_id, $meta );

		if ( \is_bool( $detected ) )
			return $detected;

		if ( ! $this->detect_non_html_page_builder() )
			return false;

		if ( empty( $meta ) )
			return false;

		if ( isset( $meta['_et_pb_use_builder'][0] ) && 'on' === $meta['_et_pb_use_builder'][0] && \defined( 'ET_BUILDER_VERSION' ) ) :
			// Divi Builder by Elegant Themes
			return true;
		elseif ( isset( $meta['_wpb_vc_js_status'][0] ) && 'true' === $meta['_wpb_vc_js_status'][0] && \defined( 'WPB_VC_VERSION' ) ) :
			// Visual Composer by WPBakery
			return true;
		endif;

		return false;
	}

	/**
	 * Determines if the current post is protected or private.
	 * Only works on singular pages.
	 *
	 * @since 2.8.0
	 * @since 3.0.0 1. No longer checks for current query.
	 *              2. Input parameter now default to null.
	 *                 This currently doesn't affect how it works.
	 *
	 * @param int|null|\WP_Post $post The post ID or WP Post object.
	 * @return bool True if protected or private, false otherwise.
	 */
	public function is_protected( $post = null ) {
		$post = \get_post( $post ); // This is here so we don't have to create another instance in the methods called.
		return $this->is_password_protected( $post ) || $this->is_private( $post );
	}

	/**
	 * Determines if the current post has a password.
	 *
	 * @since 3.0.0
	 *
	 * @param int|null|\WP_Post $post The post ID or WP Post object.
	 * @return bool True if protected, false otherwise.
	 */
	public function is_password_protected( $post = null ) {
		$post = \get_post( $post );
		return isset( $post->post_password ) && '' !== $post->post_password;
	}

	/**
	 * Determines if the current post is private.
	 *
	 * @since 3.0.0
	 *
	 * @param int|null|\WP_Post $post The post ID or WP Post object.
	 * @return bool True if private, false otherwise.
	 */
	public function is_private( $post = null ) {
		$post = \get_post( $post );
		return isset( $post->post_status ) && 'private' === $post->post_status;
	}

	/**
	 * Determines if the current post is a draft.
	 *
	 * @since 3.1.0
	 *
	 * @param int|null|\WP_Post $post The post ID or WP Post object.
	 * @return bool True if draft, false otherwise.
	 */
	public function is_draft( $post = null ) {
		$post = \get_post( $post );
		return isset( $post->post_status ) && \in_array( $post->post_status, [ 'draft', 'auto-draft', 'pending' ], true );
	}

	/**
	 * Returns list of post IDs that are excluded from search.
	 *
	 * @since 3.0.0
	 *
	 * @return array The excluded post IDs.
	 */
	public function get_ids_excluded_from_search() {
		return $this->get_excluded_ids_from_cache()['search'] ?: [];
	}

	/**
	 * Returns list of post IDs that are excluded from archive.
	 *
	 * @since 3.0.0
	 *
	 * @return array The excluded post IDs.
	 */
	public function get_ids_excluded_from_archive() {
		return $this->get_excluded_ids_from_cache()['archive'] ?: [];
	}

	/**
	 * Returns the post type object label. Either plural or singular.
	 *
	 * @since 3.1.0
	 * @see $this->get_tax_type_label() For the taxonomical alternative.
	 *
	 * @param string $post_type The post type. Required.
	 * @param bool   $singular  Wether to get the singlural or plural name.
	 * @return string The Post Type name/label, if found.
	 */
	public function get_post_type_label( $post_type, $singular = true ) {

		$pto = \get_post_type_object( $post_type );

		return $singular
			? ( isset( $pto->labels->singular_name ) ? $pto->labels->singular_name : '' )
			: ( isset( $pto->labels->name ) ? $pto->labels->name : '' );
	}

	/**
	 * Returns the primary term for post.
	 *
	 * @since 3.0.0
	 *
	 * @param int|null $post_id  The post ID.
	 * @param string   $taxonomy The taxonomy name.
	 * @return \WP_Term|false The primary term. False if not set.
	 */
	public function get_primary_term( $post_id = null, $taxonomy = '' ) {

		$primary_id = $this->get_primary_term_id( $post_id, $taxonomy );

		if ( ! $primary_id ) return false;

		// Users can alter the term list via quick/bulk edit, but cannot set a primary term that way.
		// Users can also delete a term from the site that was previously assigned as primary.
		// So, test if the term still exists for the post.
		$terms        = \get_the_terms( $post_id, $taxonomy );
		$primary_term = false;

		foreach ( $terms as $term ) {
			if ( $primary_id === (int) $term->term_id ) {
				$primary_term = $term;
				break;
			}
		}

		return $primary_term;
	}

	/**
	 * Returns the primary term ID for post.
	 *
	 * @since 3.0.0
	 *
	 * @param int|null $post_id  The post ID.
	 * @param string   $taxonomy The taxonomy name.
	 * @return int     The primary term ID. 0 if not set.
	 */
	public function get_primary_term_id( $post_id = null, $taxonomy = '' ) {
		return (int) \get_post_meta( $post_id, '_primary_term_' . $taxonomy, true ) ?: 0;
	}

	/**
	 * Updates the primary term ID for post.
	 *
	 * @since 3.0.0
	 *
	 * @param int|null $post_id  The post ID.
	 * @param string   $taxonomy The taxonomy name.
	 * @param int      $value    The new value. If empty, it will delete the entry.
	 * @return bool True on success, false on failure.
	 */
	public function update_primary_term_id( $post_id = null, $taxonomy = '', $value = 0 ) {
		if ( empty( $value ) ) {
			$success = \delete_post_meta( $post_id, '_primary_term_' . $taxonomy );
		} else {
			$success = \update_post_meta( $post_id, '_primary_term_' . $taxonomy, $value );
		}
		return $success;
	}
}
