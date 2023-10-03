<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Post_Data
 * @subpackage The_SEO_Framework\Data
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\is_headless;

use \The_SEO_Framework\Data;
use \The_SEO_Framework\Helper\{
	Post_Types,
	Query,
	Taxonomies,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
		$this->_update_post_meta( $post_id );
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
	 * @param int $post_id The post ID.
	 */
	public function _update_post_meta( $post_id ) {
		// phpcs:disable, WordPress.Security.NonceVerification

		if ( ! empty( $_POST['autodescription-quick'] ) ) {
			$this->update_quick_edit_post_meta( $post_id );
		} elseif ( ! empty( $_REQUEST['autodescription-bulk'] ) ) {
			// This is sent via GET. Keep using $_REQUEST for future-compatibility.
			$this->update_bulk_edit_post_meta( $post_id );
		} elseif ( ! empty( $_POST['autodescription'] ) ) {
			$this->update_post_edit_post_meta( $post_id );
		}

		// phpcs:enable, WordPress.Security.NonceVerification
	}

	/**
	 * Overwrites all of the post meta on post-edit.
	 *
	 * @since 4.0.0
	 *
	 * @param int $post_id The post ID.
	 * @return void
	 */
	protected function update_post_edit_post_meta( $post_id ) {

		$post_id = \get_post( $post_id )->ID ?? null;

		if ( empty( $post_id ) ) return;

		/**
		 * Don't try to save the data prior autosave, or revision post (is_preview).
		 *
		 * @TODO find a way to maintain revisions:
		 * @link https://github.com/sybrew/the-seo-framework/issues/48
		 * @link https://johnblackbourn.com/post-meta-revisions-wordpress
		 */
		if ( \wp_is_post_autosave( $post_id ) || \wp_is_post_revision( $post_id ) ) return;

		$nonce_name   = $this->inpost_nonce_name;
		$nonce_action = $this->inpost_nonce_field;

		// Check that the user is allowed to edit the post
		if (
			   ! \current_user_can( 'edit_post', $post_id )
			|| ! isset( $_POST[ $nonce_name ] )
			|| ! \wp_verify_nonce( $_POST[ $nonce_name ], $nonce_action )
		) return;

		// Perform nonce check and save fields.
		Data\Plugin\Post::save_post_meta(
			$post_id,
			(array) \wp_unslash( $_POST['autodescription'] ),
		);
	}

	/**
	 * Overwrites a part of the post meta on quick-edit.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Allowed title and description parsing.
	 *
	 * @param int $post_id The post ID.
	 * @return void
	 */
	protected function update_quick_edit_post_meta( $post_id ) {

		$post_id = \get_post( $post_id )->ID ?? null;

		if ( empty( $post_id ) ) return;

		// Check again against ambiguous injection...
		// Note, however: function wp_ajax_inline_save() already performs all these checks for us before firing this callback's action.
		if (
			   ! \current_user_can( 'edit_post', $post_id )
			|| ! \check_ajax_referer( 'inlineeditnonce', '_inline_edit', false )
		) return;

		$new_data = [];

		foreach ( (array) \wp_unslash( $_POST['autodescription-quick'] ) as $key => $value ) {
			switch ( $key ) {
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
			}
		}

		// Unlike the post-edit saving, we don't reset the data, just overwrite what's given.
		// This is because we only update a portion of the meta.
		$data = array_merge(
			Data\Plugin\Post::get_post_meta( $post_id ),
			$new_data
		);

		Data\Plugin\Post::save_post_meta( $post_id, $data );
	}

	/**
	 * Overwrites a park of the post meta on bulk-edit.
	 *
	 * @since 4.0.0
	 *
	 * @param int $post_id The post ID.
	 * @return void
	 */
	protected function update_bulk_edit_post_meta( $post_id ) {

		$post_id = \get_post( $post_id )->ID ?? null;

		if ( empty( $post_id ) ) return;

		// Check again against ambiguous injection...
		// Note, however: function bulk_edit_posts() already performs all these checks for us before firing this callback's action.
		if ( ! \current_user_can( 'edit_post', $post_id ) ) return;

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
			foreach ( (array) $_REQUEST['autodescription-bulk'] as $key => $value ) {
				switch ( $key ) {
					case 'noindex':
					case 'nofollow':
					case 'noarchive':
						if ( 'nochange' === $value ) continue 2;
						$new_data[ "_genesis_$key" ] = $value;
				}
			}
		}

		// Unlike the post-edit saving, we don't reset the data, just overwrite what's given.
		// This is because we only update a portion of the meta.
		$data = array_merge(
			Data\Plugin\Post::get_post_meta( $post_id ),
			$new_data
		);

		Data\Plugin\Post::save_post_meta( $post_id, $data );
	}

	/**
	 * Saves primary term data for posts.
	 *
	 * @since 3.0.0
	 * @since 4.0.0 1. Now allows updating during `WP_CRON`.
	 *              2. Now allows updating during `WP_AJAX`.
	 * @securitycheck 4.1.0 OK.
	 *
	 * @param int $post_id The post ID.
	 * @return void
	 */
	public function _save_inpost_primary_term( $post_id ) {

		// The 'autodescription' index should only be used when using the editor.
		// Quick and bulk-edit should be halted here.
		if ( empty( $_POST['autodescription'] ) ) return;

		$post_id = \get_post( $post_id )->ID ?? null;

		if ( empty( $post_id ) ) return;

		/**
		 * Don't try to save the data prior autosave, or revision post (is_preview).
		 *
		 * @TODO find a way to maintain revisions:
		 * @link https://github.com/sybrew/the-seo-framework/issues/48
		 * @link https://johnblackbourn.com/post-meta-revisions-wordpress
		 */
		if ( \wp_is_post_autosave( $post_id ) || \wp_is_post_revision( $post_id ) ) return;

		// Check that the user is allowed to edit the post. Nonce checks are done in bulk later.
		if ( ! \current_user_can( 'edit_post', $post_id ) ) return;

		$post_type = \get_post_type( $post_id ) ?: false;
		// Can this even fail?
		if ( empty( $post_type ) ) return;

		foreach ( Taxonomies::get_hierarchical_taxonomies_as( 'names', $post_type ) as $taxonomy ) {
			// Redundant. Fortified.
			if ( ! \wp_verify_nonce(
				$_POST[ "{$this->inpost_nonce_name}_pt_{$taxonomy}" ] ?? '', // If empty, wp_verify_nonce will return false.
				"{$this->inpost_nonce_field}_pt"
			) ) continue;

			Data\Plugin\Post::update_primary_term_id(
				$post_id,
				$taxonomy,
				\absint( $_POST['autodescription'][ "_primary_term_{$taxonomy}" ] ?? 0 )
			);
		}
	}
}
