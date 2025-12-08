<?php
/**
 * @package The_SEO_Framework\Classes\Data\Admin\Post
 * @subpackage The_SEO_Framework\Data
 */

namespace The_SEO_Framework\Data\Admin;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use The_SEO_Framework\{
	Data,
	Helper\Taxonomy,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 - 2025 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds a collection of data saving methods for posts.
 *
 * @since 5.0.0
 * @access private
 */
final class Post {

	/**
	 * @since 5.1.3
	 * @var array[] {
	 *     The nonce data per save context.
	 *
	 *     @type string $name   The nonce field name.
	 *     @type string $action The nonce action.
	 * }
	 */
	public const SAVE_NONCES = [
		'post-edit'  => [
			'name'   => 'tsf_post_nonce_name',
			'action' => 'tsf_post_nonce_action',
		],
		'quick-edit' => [
			'name'   => 'tsf_post_nonce_name',
			'action' => 'tsf_post_nonce_action',
		],
		'bulk-edit'  => [
			'name'   => 'tsf_post_nonce_name',
			'action' => 'tsf_post_nonce_action',
		],
	];

	/**
	 * Saves the Post SEO Meta settings on quick-edit, bulk-edit, or post-edit.
	 *
	 * @hook save_post 1
	 * @hook edit_attachment 1
	 * @since 2.0.0
	 * @since 2.9.3 Added 'exclude_from_archive'.
	 * @since 4.0.0 1. Renamed from `inpost_seo_save`
	 *              2. Now allows updating during `WP_CRON`.
	 *              3. Now allows updating during `WP_AJAX`.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `_update_post_meta`.
	 * @access private
	 *
	 * @param int $post_id The post ID.
	 */
	public static function update_meta( $post_id ) {
		// phpcs:disable WordPress.Security.NonceVerification

		if ( ! empty( $_POST['autodescription-quick'] ) ) {
			self::update_via_quick_edit( $post_id );
		} elseif ( ! empty( $_REQUEST['autodescription-bulk'] ) ) {
			// This is sent via GET. Keep using $_REQUEST for future-compatibility.
			self::update_via_bulk_edit( $post_id );
		} elseif ( ! empty( $_POST['autodescription'] ) ) {
			self::update_via_post_edit( $post_id );
		}

		// phpcs:enable WordPress.Security.NonceVerification
	}

	/**
	 * Saves primary term data for posts.
	 *
	 * @hook save_post 1
	 * @since 3.0.0
	 * @since 4.0.0 1. Now allows updating during `WP_CRON`.
	 *              2. Now allows updating during `WP_AJAX`.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `_save_inpost_primary_term`.
	 * @since 5.1.3 Now supports quick-edit and bulk-edit.
	 *
	 * @param int $post_id The post ID.
	 * @return void
	 */
	public static function update_primary_term( $post_id ) {

		// This resolves a quirk, since wp_insert_post() has no proper guard.
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

		if ( ! \current_user_can( 'edit_post', $post_id ) ) return;

		$post_type = \get_post_type( $post_id );
		// Can this even fail?
		if ( empty( $post_type ) ) return;

		// Determine edit type: post-edit, quick-edit, or bulk-edit
		if ( ! empty( $_POST['autodescription'] ) ) {
			// Post-edit
			foreach ( Taxonomy::get_hierarchical( 'names', $post_type ) as $taxonomy ) {
				if ( ! \wp_verify_nonce(
					$_POST[ self::SAVE_NONCES['post-edit']['name'] . "_pt_{$taxonomy}" ] ?? '',
					self::SAVE_NONCES['post-edit']['action'] . '_pt',
				) )
					continue;

				Data\Plugin\Post::update_primary_term_id(
					$post_id,
					$taxonomy,
					\absint( $_POST['autodescription'][ "_primary_term_{$taxonomy}" ] ?? 0 ),
				);
			}
		} elseif ( ! empty( $_POST['autodescription-quick'] ) ) {
			// Quick-edit
			if ( ! \check_ajax_referer( 'inlineeditnonce', '_inline_edit', false ) ) return;

			foreach ( Taxonomy::get_hierarchical( 'names', $post_type ) as $taxonomy ) {
				if ( ! isset( $_POST['autodescription-quick'][ "primary_term_{$taxonomy}" ] ) ) continue;

				$term_id = \absint( \wp_unslash( $_POST['autodescription-quick'][ "primary_term_{$taxonomy}" ] ) );

				if ( $term_id > 0 )
					Data\Plugin\Post::update_primary_term_id( $post_id, $taxonomy, $term_id );
			}
		} elseif ( ! empty( $_REQUEST['autodescription-bulk'] ) ) {
			// Bulk-edit
			static $verified_bulk_referer = false;

			if ( ! $verified_bulk_referer ) {
				\check_admin_referer( 'bulk-posts' );
				$verified_bulk_referer = true;
			}

			foreach ( Taxonomy::get_hierarchical( 'names', $post_type ) as $taxonomy ) {
				if ( ! isset( $_REQUEST['autodescription-bulk'][ "primary_term_{$taxonomy}" ] ) ) continue;

				$value = $_REQUEST['autodescription-bulk'][ "primary_term_{$taxonomy}" ];

				if ( 'nochange' === $value ) continue;

				$term_id = \absint( $value );

				if ( $term_id > 0 ) {
					$terms = \get_the_terms( $post_id, $taxonomy );

					if ( $terms && ! \is_wp_error( $terms ) ) {
						$valid_term_ids = \array_column( $terms, 'term_id' );

						if ( \in_array( $term_id, $valid_term_ids, true ) )
							Data\Plugin\Post::update_primary_term_id( $post_id, $taxonomy, $term_id );
					}
				} else {
					Data\Plugin\Post::update_primary_term_id( $post_id, $taxonomy, 0 );
				}
			}
		}
	}

	/**
	 * Overwrites all of the post meta on post-edit.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `update_post_edit_post_meta`.
	 *
	 * @param int $post_id The post ID.
	 * @return void
	 */
	private static function update_via_post_edit( $post_id ) {

		$post_id = \get_post( $post_id )->ID ?? null;

		if ( empty( $post_id ) ) return;

		/**
		 * Don't try to save the data prior autosave, or revision post (is_preview).
		 *
		 * @TODO find a way to maintain revisions:
		 * @link https://github.com/sybrew/the-seo-framework/issues/48
		 * @link https://johnblackbourn.com/post-meta-revisions-wordpress
		 * @link https://core.trac.wordpress.org/ticket/20299#comment:64
		 * @link https://make.wordpress.org/core/2023/10/24/framework-for-storing-revisions-of-post-meta-in-6-4/
		 */
		if ( \wp_is_post_autosave( $post_id ) || \wp_is_post_revision( $post_id ) ) return;

		// Check that the user is allowed to edit the post. This is redundant and may need to be removed for full Gutenberg support.
		if (
			   ! \current_user_can( 'edit_post', $post_id )
			|| ! isset( $_POST[ self::SAVE_NONCES['post-edit']['name'] ] )
			|| ! \wp_verify_nonce( $_POST[ self::SAVE_NONCES['post-edit']['name'] ], self::SAVE_NONCES['post-edit']['action'] )
		) return;

		// Trim, sanitize, and save the metadata.
		Data\Plugin\Post::save_meta(
			$post_id,
			(array) \wp_unslash( $_POST['autodescription'] ),
		);
	}

	/**
	 * Overwrites a part of the post meta on quick-edit.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Allowed title and description parsing.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `update_quick_edit_post_meta`.
	 *
	 * @param int $post_id The post ID.
	 * @return void
	 */
	private static function update_via_quick_edit( $post_id ) {

		$post_id = \get_post( $post_id )->ID ?? null;

		if ( empty( $post_id ) ) return;

		// Check again against ambiguous injection...
		// Note, however: function wp_ajax_inline_save() already performs all these checks for us before firing this callback's action.
		if (
			   ! \current_user_can( 'edit_post', $post_id )
			|| ! \check_ajax_referer( 'inlineeditnonce', '_inline_edit', false )
			|| ! isset( $_POST[ self::SAVE_NONCES['quick-edit']['name'] ] )
			|| ! \wp_verify_nonce( $_POST[ self::SAVE_NONCES['quick-edit']['name'] ], self::SAVE_NONCES['quick-edit']['action'] )
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
					break;
			}
		}

		// Unlike the post-edit saving, we don't reset the data, just overwrite what's given.
		// This is because we only update a portion of the meta.
		$data = array_merge(
			Data\Plugin\Post::get_meta( $post_id ),
			$new_data,
		);

		Data\Plugin\Post::save_meta( $post_id, $data );
	}

	/**
	 * Overwrites a park of the post meta on bulk-edit.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `update_bulk_edit_post_meta`.
	 *
	 * @param int $post_id The post ID.
	 * @return void
	 */
	private static function update_via_bulk_edit( $post_id ) {

		$post_id = \get_post( $post_id )->ID ?? null;

		if ( empty( $post_id ) ) return;

		// Check again against ambiguous injection...
		// Note, however: function bulk_edit_posts() already performs all these checks for us before firing this callback's action.
		if ( ! \current_user_can( 'edit_post', $post_id ) ) return;

		static $verified_referer = false;
		// Memoize the referer check--if it passes (and doesn't exit/die PHP), we're good to execute subsequently.
		if ( ! $verified_referer ) {
			\check_admin_referer( 'bulk-posts' );

			if (
				   ! isset( $_REQUEST[ self::SAVE_NONCES['bulk-edit']['name'] ] )
				|| ! \wp_verify_nonce( $_REQUEST[ self::SAVE_NONCES['bulk-edit']['name'] ], self::SAVE_NONCES['bulk-edit']['action'] )
			) return;

			$verified_referer = true;
		}

		static $new_data;

		if ( ! isset( $new_data ) ) {
			$new_data = [];

			// This is sent via GET. Keep using $_REQUEST for future-compatibility.
			foreach ( (array) $_REQUEST['autodescription-bulk'] as $key => $value ) {
				switch ( $key ) {
					case 'noindex':
					case 'nofollow':
					case 'noarchive':
						if ( 'nochange' === $value )
							break;
						$new_data[ "_genesis_$key" ] = $value;
				}
			}
		}

		// Unlike the post-edit saving, we don't reset the data, just overwrite what's given.
		// This is because we only update a portion of the meta.
		$data = array_merge(
			Data\Plugin\Post::get_meta( $post_id ),
			$new_data,
		);

		Data\Plugin\Post::save_meta( $post_id, $data );
	}
}
