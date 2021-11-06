<?php
/**
 * @package The_SEO_Framework\Classes\Bridges\AJAX
 * @subpackage The_SEO_Framework\Feed
 */

namespace The_SEO_Framework\Bridges;

/**
 * The SEO Framework plugin
 * Copyright (C) 2021 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * Holds AJAX callbacks.
 *
 * The methods in this class may move to other classes later, so that
 * they're with their relatives.
 *
 * @since 4.1.4
 * @access private
 * @final Can't be extended.
 */
final class AJAX {

	/**
	 * Clears persistent notice on user request (clicked Dismiss icon) via AJAX.
	 *
	 * @since 4.1.0
	 * @since 4.1.4 Moved to \The_SEO_Framework\Bridges\AJAX and made static.
	 * @since 4.2.0 Now cleans response header.
	 * Security check OK.
	 * @access private
	 */
	public static function _wp_ajax_dismiss_notice() {

		$tsf = \tsf();
		$tsf->clean_response_header();

		// phpcs:ignore, WordPress.Security.NonceVerification.Missing -- We require the POST data to find locally stored nonces.
		$key = $_POST['tsf_dismiss_key'] ?? '';

		if ( ! $key )
			\wp_send_json_error( null, 400 );

		$notices = $tsf->get_static_cache( 'persistent_notices', [] );
		if ( empty( $notices[ $key ]['conditions']['capability'] ) ) {
			// Notice was deleted already elsewhere, or key was faulty. Either way, ignore--should be self-resolving.
			\wp_send_json_error( null, 409 );
		}

		if ( ! \current_user_can( $notices[ $key ]['conditions']['capability'] )
		|| ! \check_ajax_referer( $tsf->_get_dismiss_notice_nonce_action( $key ), 'tsf_dismiss_nonce', false ) )
			\wp_die( -1, 403 );

		$tsf->clear_persistent_notice( $key );
		\wp_send_json_success( null, 200 );
	}

	/**
	 * Handles counter option update on AJAX request for users that can edit posts.
	 *
	 * @since 3.1.0 Introduced in 2.6.0, but the name changed.
	 * @since 4.1.4 Moved to \The_SEO_Framework\Bridges\AJAX and made static.
	 * @since 4.2.0 1. Now uses wp.ajax instead of $.ajax.
	 *              2. No longer tests if settings-saving was successful.
	 * @securitycheck 3.0.0 OK.
	 * @access private
	 */
	public static function _wp_ajax_update_counter_type() {

		$tsf = \tsf();
		$tsf->clean_response_header();

		// phpcs:disable, WordPress.Security.NonceVerification -- _check_tsf_ajax_referer() does this.
		$tsf->_check_tsf_ajax_referer( 'edit_posts' );

		// If current user isn't allowed to edit posts, don't do anything and kill PHP.
		if ( ! \current_user_can( 'edit_posts' ) )
			\wp_send_json_error();

		/**
		 * Count up, reset to 0 if needed. We have 4 options: 0, 1, 2, 3
		 * $_POST['val'] already contains updated number.
		 */
		if ( isset( $_POST['val'] ) ) {
			$value = (int) $_POST['val'];
		} else {
			$value = $tsf->get_user_meta_item( 'counter_type' ) + 1;
		}
		$value = \absint( $value );

		if ( $value > 3 )
			$value = 0;

		// Update the option and get results of action.
		$tsf->update_single_user_meta_item( $tsf->get_user_id(), 'counter_type', $value );

		// Encode and echo results. Requires JSON decode within JS.
		\wp_send_json_success( [
			'type'  => 'success',
			'value' => $value,
		] );
		// phpcs:enable, WordPress.Security.NonceVerification
	}

	/**
	 * Handles cropping of images on AJAX request.
	 *
	 * This function is necessary because we don't believe `wp_ajax_crop_image()` should check for `edit_post`,
	 * but for `upload_files`. This is because the 'post' (file) that's edited isn't linked to the newly cropped
	 * image, thus it's creating a NEW 'post'. Nothing is 'edited' on the old 'post'.
	 *
	 * Copied from WordPress Core wp_ajax_crop_image.
	 * Adjusted: 1. It accepts capability 'upload_files', instead of 'customize'.
	 *               - This was set to 'edit_post' in WP 4.7? trac ticket got lost, probably for (invalid) security reasons.
	 *                 In any case, that's still incorrect, and I gave up on communicating this;
	 *                 We're not editing the image, we're creating a new one!
	 *           2. It now only accepts TSF own AJAX nonces.
	 *           3. It now only accepts context 'tsf-image'
	 *           4. It no longer accepts a default context.
	 *
	 * @since 3.1.0 Introduced in 2.9.0, but the name changed.
	 * @since 4.1.4 Moved to \The_SEO_Framework\Bridges\AJAX and made static.
	 * @since 4.2.0 Now cleans response header.
	 * @securitycheck 3.0.0 OK.
	 * @access private
	 */
	public static function _wp_ajax_crop_image() {

		$tsf = \tsf();
		$tsf->clean_response_header();

		// phpcs:disable, WordPress.Security.NonceVerification -- _check_tsf_ajax_referer does this.
		$tsf->_check_tsf_ajax_referer( 'upload_files' );

		if ( ! \current_user_can( 'upload_files' ) || ! isset( $_POST['id'], $_POST['context'], $_POST['cropDetails'] ) )
			\wp_send_json_error();

		$attachment_id = \absint( $_POST['id'] );

		$context = str_replace( '_', '-', \sanitize_key( $_POST['context'] ) );
		$data    = array_map( 'absint', $_POST['cropDetails'] );
		$cropped = \wp_crop_image( $attachment_id, $data['x1'], $data['y1'], $data['width'], $data['height'], $data['dst_width'], $data['dst_height'] );

		if ( ! $cropped || \is_wp_error( $cropped ) )
			\wp_send_json_error( [ 'message' => \esc_js( \__( 'Image could not be processed.', 'default' ) ) ] );

		switch ( $context ) :
			case 'tsf-image':
				/**
				 * Fires before a cropped image is saved.
				 *
				 * Allows to add filters to modify the way a cropped image is saved.
				 *
				 * @since 4.3.0 WordPress Core
				 *
				 * @param string $context       The Customizer control requesting the cropped image.
				 * @param int    $attachment_id The attachment ID of the original image.
				 * @param string $cropped       Path to the cropped image file.
				 */
				\do_action( 'wp_ajax_crop_image_pre_save', $context, $attachment_id, $cropped );

				/** This filter is documented in wp-admin/custom-header.php */
				$cropped = \apply_filters( 'wp_create_file_in_uploads', $cropped, $attachment_id ); // For replication.

				$parent_url = \wp_get_attachment_url( $attachment_id );
				$url        = str_replace( basename( $parent_url ), basename( $cropped ), $parent_url );

				// phpcs:ignore, WordPress.PHP.NoSilencedErrors -- Feature may be disabled; should not cause fatal errors.
				$size       = @getimagesize( $cropped );
				$image_type = ( $size ) ? $size['mime'] : 'image/jpeg';

				$object = [
					'post_title'     => basename( $cropped ),
					'post_content'   => $url,
					'post_mime_type' => $image_type,
					'guid'           => $url,
					'context'        => $context,
				];

				$attachment_id = \wp_insert_attachment( $object, $cropped );
				$metadata      = \wp_generate_attachment_metadata( $attachment_id, $cropped );

				/**
				 * Filters the cropped image attachment metadata.
				 *
				 * @since 4.3.0 WordPress Core
				 * @see wp_generate_attachment_metadata()
				 *
				 * @param array $metadata Attachment metadata.
				 */
				$metadata = \apply_filters( 'wp_ajax_cropped_attachment_metadata', $metadata );
				\wp_update_attachment_metadata( $attachment_id, $metadata );

				/**
				 * Filters the attachment ID for a cropped image.
				 *
				 * @since 4.3.0 WordPress Core
				 *
				 * @param int    $attachment_id The attachment ID of the cropped image.
				 * @param string $context       The Customizer control requesting the cropped image.
				 */
				$attachment_id = \apply_filters( 'wp_ajax_cropped_attachment_id', $attachment_id, $context );
				break;

			default:
				\wp_send_json_error( [ 'message' => \esc_js( \__( 'Image could not be processed.', 'default' ) ) ] );
				break;
		endswitch;

		\wp_send_json_success( \wp_prepare_attachment_for_js( $attachment_id ) );

		// phpcs:enable, WordPress.Security.NonceVerification
	}

	/**
	 * Gets an SEO Bar for AJAX during edit-post.
	 *
	 * @since 4.0.0
	 * @since 4.1.4 Moved to \The_SEO_Framework\Bridges\AJAX and made static.
	 * @since 4.2.0 Now uses wp.ajax, instead of $.ajax
	 * @access private
	 */
	public static function _wp_ajax_get_post_data() {

		$tsf = \tsf();
		$tsf->clean_response_header();

		// phpcs:disable, WordPress.Security.NonceVerification -- _check_tsf_ajax_referer() does this.
		$tsf->_check_tsf_ajax_referer( 'edit_posts' );

		$post_id = \absint( $_POST['post_id'] );

		if ( ! $post_id || ! \current_user_can( 'edit_post', $post_id ) )
			\wp_send_json_error();

		$_get_defaults = [
			'seobar'          => false,
			'metadescription' => false,
			'ogdescription'   => false,
			'twdescription'   => false,
			'imageurl'        => false,
		];

		// Only get what's indexed in the defaults and set as "true".
		$get = array_keys(
			array_filter(
				array_intersect_key(
					array_merge(
						$_get_defaults,
						(array) ( $_POST['get'] ?? [] )
					),
					$_get_defaults
				)
			)
		);

		$_generator_args = [ 'id' => $post_id ];

		$data = [];

		foreach ( $get as $g ) :
			switch ( $g ) {
				case 'seobar':
					$data[ $g ] = $tsf->get_generated_seo_bar( $_generator_args );
					break;

				case 'metadescription':
				case 'ogdescription':
				case 'twdescription':
					switch ( $g ) {
						case 'metadescription':
							if ( $tsf->is_static_frontpage( $post_id ) ) {
								$data[ $g ] = $tsf->get_option( 'homepage_description' )
										   ?: $tsf->get_generated_description( $_generator_args, false );
							} else {
								$data[ $g ] = $tsf->get_generated_description( $_generator_args, false );
							}
							break;
						case 'ogdescription':
							if ( $tsf->is_static_frontpage( $post_id ) ) {
								$data[ $g ] = $tsf->get_option( 'homepage_description' )
										   ?: $tsf->get_generated_open_graph_description( $_generator_args, false );
							} else {
								$data[ $g ] = $tsf->get_generated_open_graph_description( $_generator_args, false );
							}
							break;
						case 'twdescription':
							if ( $tsf->is_static_frontpage( $post_id ) ) {
								$data[ $g ] = $tsf->get_option( 'homepage_description' )
										   ?: $tsf->get_generated_twitter_description( $_generator_args, false );
							} else {
								$data[ $g ] = $tsf->get_generated_twitter_description( $_generator_args, false );
							}
							break;
					}

					$data[ $g ] = $tsf->s_description( $data[ $g ] );
					break;

				case 'imageurl':
					if ( $tsf->is_static_frontpage( $post_id ) && $tsf->get_option( 'homepage_social_image_url' ) ) {
						$data[ $g ] = current( $tsf->get_image_details( $_generator_args, true, 'social', true ) )['url'] ?? '';
					} else {
						$data[ $g ] = current( $tsf->get_generated_image_details( $_generator_args, true, 'social', true ) )['url'] ?? '';
					}
					break;

				default:
					break;
			}
		endforeach;

		\wp_send_json_success( [
			'data'      => $data,
			'processed' => $get,
		] );
		// phpcs:enable, WordPress.Security.NonceVerification
	}
}
