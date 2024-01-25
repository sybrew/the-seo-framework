<?php
/**
 * @package The_SEO_Framework\Classes\Admin\Script\AJAX
 * @subpackage The_SEO_Framework\Scripts
 */

namespace The_SEO_Framework\Admin\Script;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\{
	Admin,
	Data,
	Data\Filter\Sanitize,
	Helper,
	Helper\Query,
	Meta,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2021 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds AJAX callbacks.
 *
 * The methods in this class may move to other classes later, so that
 * they're with their relatives.
 *
 * @since 4.1.4
 * @since 5.0.0 Moved from `\The_SEO_Framework\Bridges`
 * @access private
 */
final class AJAX {

	/**
	 * Clears persistent notice on user request (clicked Dismiss icon) via AJAX.
	 *
	 * @since 4.1.0
	 * @since 4.2.0 Now cleans response header.
	 * @since 5.0.0 Removed _wp_ajax_ from the plugin name.
	 * @access private
	 */
	public static function dismiss_notice() {

		Helper\Headers::clean_response_header();

		// phpcs:ignore, WordPress.Security.NonceVerification.Missing -- We require the POST data to find locally stored nonces.
		$key = $_POST['tsf_dismiss_key'] ?? '';

		if ( ! $key )
			\wp_send_json_error( null, 400 );

		$notices = Data\Plugin::get_site_cache( 'persistent_notices' ) ?? [];

		if ( empty( $notices[ $key ]['conditions']['capability'] ) ) {
			// Notice was deleted already elsewhere, or key was faulty. Either way, ignore--should be self-resolving.
			\wp_send_json_error( null, 409 );
		}

		if (
			   ! \current_user_can( $notices[ $key ]['conditions']['capability'] )
			|| ! \check_ajax_referer( Admin\Notice\Persistent::_get_dismiss_nonce_action( $key ), 'tsf_dismiss_nonce', false )
		) {
			\wp_die( -1, 403 );
		}

		Admin\Notice\Persistent::clear_notice( $key );
		\wp_send_json_success( null, 200 );
	}

	/**
	 * Handles counter option update on AJAX request for users that can edit posts.
	 *
	 * @since 3.1.0 Introduced in 2.6.0, but the name changed.
	 * @since 4.2.0 1. Now uses wp.ajax instead of $.ajax.
	 *              2. No longer tests if settings-saving was successful.
	 * @since 5.0.0 Removed _wp_ajax_ from the plugin name.
	 * @access private
	 */
	public static function update_counter_type() {

		Helper\Headers::clean_response_header();

		// phpcs:disable, WordPress.Security.NonceVerification -- check_ajax_referer() does this.
		Utils::check_ajax_capability_referer( 'edit_posts' );

		/**
		 * Count up, reset to 0 if needed. We have 4 options: 0, 1, 2, 3
		 * $_POST['val'] already contains updated number.
		 */
		if ( isset( $_POST['val'] ) ) {
			$value = (int) $_POST['val'];
		} else {
			$value = Data\Plugin\User::get_meta_item( 'counter_type' ) + 1;
		}
		$value = \absint( $value );

		if ( $value > 3 )
			$value = 0;

		// Update the option and get results of action.
		Data\Plugin\User::update_single_meta_item( Query::get_current_user_id(), 'counter_type', $value );

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
	 * @since 4.2.0 Now cleans response header.
	 * @since 4.2.5 1. Backported cropping support for WebP (WP 5.9).
	 *              2. Backported title, description, alt tag, and excerpt preservation (WP 6.0).
	 * @since 5.0.0 Removed _wp_ajax_ from the plugin name.
	 * @access private
	 */
	public static function crop_image() {

		Helper\Headers::clean_response_header();

		// phpcs:disable, WordPress.Security.NonceVerification -- check_ajax_referer does this.
		Utils::check_ajax_capability_referer( 'upload_files' );

		if ( ! isset( $_POST['id'], $_POST['context'], $_POST['cropDetails'] ) )
			\wp_send_json_error();

		$attachment_id = \absint( $_POST['id'] );

		if ( ! $attachment_id || 'attachment' !== \get_post_type( $attachment_id ) || ! \wp_attachment_is_image( $attachment_id ) )
			\wp_send_json_error( [ 'message' => \esc_js( \__( 'Image could not be processed.', 'default' ) ) ] );

		$context = str_replace( '_', '-', \sanitize_key( $_POST['context'] ) );
		$data    = array_map( 'absint', $_POST['cropDetails'] );
		$cropped = \wp_crop_image( $attachment_id, $data['x1'], $data['y1'], $data['width'], $data['height'], $data['dst_width'], $data['dst_height'] );

		if ( ! $cropped || \is_wp_error( $cropped ) )
			\wp_send_json_error( [ 'message' => \esc_js( \__( 'Image could not be processed.', 'default' ) ) ] );

		switch ( $context ) {
			case 'tsf-image':
				/**
				 * Fires before a cropped image is saved.
				 *
				 * Allows to add filters to modify the way a cropped image is saved.
				 *
				 * @since 5.0.0 WordPress Core
				 *
				 * @param string $context       The Customizer control requesting the cropped image.
				 * @param int    $attachment_id The attachment ID of the original image.
				 * @param string $cropped       Path to the cropped image file.
				 */
				\do_action( 'wp_ajax_crop_image_pre_save', $context, $attachment_id, $cropped );

				/** This filter is documented in wp-admin/includes/class-custom-image-header.php */
				$cropped = \apply_filters( 'wp_create_file_in_uploads', $cropped, $attachment_id ); // For replication.

				$parent_url       = \wp_get_attachment_url( $attachment_id );
				$parent_basename  = \wp_basename( $parent_url );
				$cropped_basename = \wp_basename( $cropped );
				$url              = str_replace( $parent_basename, $cropped_basename, $parent_url );

				// phpcs:ignore, WordPress.PHP.NoSilencedErrors -- See https://core.trac.wordpress.org/ticket/42480
				$size       = \function_exists( 'wp_getimagesize' ) ? \wp_getimagesize( $cropped ) : @getimagesize( $cropped );
				$image_type = $size ? $size['mime'] : 'image/jpeg';

				// Get the original image's post to pre-populate the cropped image.
				$original_attachment  = \get_post( $attachment_id );
				$sanitized_post_title = \sanitize_file_name( $original_attachment->post_title );
				$use_original_title   = (
					\strlen( trim( $original_attachment->post_title ) ) &&
					/**
					 * Check if the original image has a title other than the "filename" default,
					 * meaning the image had a title when originally uploaded or its title was edited.
					 */
					( $parent_basename !== $sanitized_post_title ) &&
					( pathinfo( $parent_basename, \PATHINFO_FILENAME ) !== $sanitized_post_title )
				);
				$use_original_description = \strlen( trim( $original_attachment->post_content ) );

				$attachment = [
					'post_title'     => $use_original_title ? $original_attachment->post_title : $cropped_basename,
					'post_content'   => $use_original_description ? $original_attachment->post_content : $url,
					'post_mime_type' => $image_type,
					'guid'           => $url,
					'context'        => $context,
				];

				// Copy the image caption attribute (post_excerpt field) from the original image.
				if ( \strlen( trim( $original_attachment->post_excerpt ) ) )
					$attachment['post_excerpt'] = $original_attachment->post_excerpt;

				// Copy the image alt text attribute from the original image.
				if ( \strlen( trim( $original_attachment->_wp_attachment_image_alt ) ) )
					$attachment['meta_input'] = [
						'_wp_attachment_image_alt' => \wp_slash( $original_attachment->_wp_attachment_image_alt ),
					];

				$attachment_id = \wp_insert_attachment( $attachment, $cropped );
				$metadata      = \wp_generate_attachment_metadata( $attachment_id, $cropped );

				/**
				 * @since 5.0.0 WordPress Core
				 * @see wp_generate_attachment_metadata()
				 * @param array $metadata Attachment metadata.
				 */
				$metadata = \apply_filters( 'wp_ajax_cropped_attachment_metadata', $metadata );
				\wp_update_attachment_metadata( $attachment_id, $metadata );

				/**
				 * @since 5.0.0 WordPress Core
				 * @param int    $attachment_id The attachment ID of the cropped image.
				 * @param string $context       The Customizer control requesting the cropped image.
				 */
				$attachment_id = \apply_filters( 'wp_ajax_cropped_attachment_id', $attachment_id, $context );
				break;

			default:
				\wp_send_json_error( [ 'message' => \esc_js( \__( 'Image could not be processed.', 'default' ) ) ] );
		}

		\wp_send_json_success( \wp_prepare_attachment_for_js( $attachment_id ) );

		// phpcs:enable, WordPress.Security.NonceVerification
	}

	/**
	 * Gets an SEO Bar for AJAX during edit-post.
	 *
	 * @since 4.0.0
	 * @since 4.2.0 Now uses wp.ajax, instead of $.ajax
	 * @since 5.0.0 Removed _wp_ajax_ from the plugin name.
	 * @access private
	 */
	public static function get_post_data() {

		Helper\Headers::clean_response_header();

		// phpcs:disable, WordPress.Security.NonceVerification -- check_ajax_referer() does this.
		Utils::check_ajax_capability_referer( 'edit_posts' );

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
						(array) ( $_POST['get'] ?? [] ),
					),
					$_get_defaults,
				)
			)
		);

		$generator_args = [ 'id' => $post_id ];

		$data = [];

		foreach ( $get as $g ) switch ( $g ) {
			case 'seobar':
				$data[ $g ] = Admin\SEOBar\Builder::generate_bar( $generator_args );
				break;

			case 'metadescription':
			case 'ogdescription':
			case 'twdescription':
				switch ( $g ) {
					case 'metadescription':
						if ( Query::is_static_front_page( $post_id ) ) {
							$data[ $g ] = Sanitize::metadata_content( Data\Plugin::get_option( 'homepage_description' ) )
									   ?: Meta\Description::get_generated_description( $generator_args );
						} else {
							$data[ $g ] = Meta\Description::get_generated_description( $generator_args );
						}
						break;
					case 'ogdescription':
						if ( Query::is_static_front_page( $post_id ) ) {
							$data[ $g ] = Sanitize::metadata_content( Data\Plugin::get_option( 'homepage_description' ) )
									   ?: Meta\Open_Graph::get_generated_description( $generator_args );
						} else {
							$data[ $g ] = Meta\Open_Graph::get_generated_description( $generator_args );
						}
						break;
					case 'twdescription':
						if ( Query::is_static_front_page( $post_id ) ) {
							$data[ $g ] = Sanitize::metadata_content( Data\Plugin::get_option( 'homepage_description' ) )
									   ?: Meta\Twitter::get_generated_description( $generator_args );
						} else {
							$data[ $g ] = Meta\Twitter::get_generated_description( $generator_args );
						}
				}

				$data[ $g ] = \esc_html( $data[ $g ] );
				break;

			case 'imageurl':
				if ( Query::is_static_front_page( $post_id ) ) {
					$data[ $g ] = \sanitize_url( Data\Plugin::get_option( 'homepage_social_image_url' ), [ 'https', 'http' ] )
							   ?: Meta\Image::get_first_generated_image_url( $generator_args, 'social' );
				} else {
					$data[ $g ] = Meta\Image::get_first_generated_image_url( $generator_args, 'social' );
				}
		}

		\wp_send_json_success( [
			'data'      => $data,
			'processed' => $get,
		] );
		// phpcs:enable, WordPress.Security.NonceVerification
	}
}
