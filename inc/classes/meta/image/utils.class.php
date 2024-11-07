<?php
/**
 * @package The_SEO_Framework\Classes\Meta\Image
 * @subpackage The_SEO_Framework\Meta\Image
 */

namespace The_SEO_Framework\Meta\Image;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds utility for the Image factory.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->image()->utils() instead.
 */
class Utils {

	/**
	 * Fetches image dimensions.
	 *
	 * @since 5.0.0
	 *
	 * @param int    $src_id The source ID of the image.
	 * @param string $size   The size of the image to get.
	 *                       It falls back to the original image if not found.
	 * @return array The image dimensions, associative: {
	 *     The image's dimensions.
	 *
	 *     @type int $width  The image width in pixels.
	 *     @type int $height The image height in pixels.
	 * }
	 */
	public static function get_image_dimensions( $src_id, $size ) {

		$data = \wp_get_attachment_metadata( $src_id ) ?? [];
		$data = $data['sizes'][ $size ] ?? $data;

		if ( isset( $data['width'], $data['height'] ) )
			return [
				'width'  => $data['width'],
				'height' => $data['height'],
			];

		return [
			'width'  => 0,
			'height' => 0,
		];
	}

	/**
	 * Fetches image alt tag.
	 *
	 * @since 5.0.0
	 *
	 * @param int $src_id The source ID of the image.
	 * @return string The image alt tag.
	 */
	public static function get_image_alt_tag( $src_id ) {
		return \get_post_meta( $src_id, '_wp_attachment_image_alt', true ) ?: '';
	}

	/**
	 * Fetches image caption.
	 *
	 * @since 5.0.0
	 *
	 * @param int $src_id The source ID of the image.
	 * @return string The image caption.
	 */
	public static function get_image_caption( $src_id ) {
		return \wp_get_attachment_caption( $src_id ) ?: '';
	}

	/**
	 * Fetches image filesize in bytes. Requires an image (re)generated in WP 6.0 or later.
	 *
	 * @since 5.0.0
	 *
	 * @param int    $src_id The source ID of the image.
	 * @param string $size   The size of the image used.
	 * @return int The image filesize in bytes. Returns 0 for unprocessed/unprocessable image.
	 */
	public static function get_image_filesize( $src_id, $size ) {

		$data = \wp_get_attachment_metadata( $src_id ) ?: [];

		return ( $data['sizes'][ $size ]['filesize'] ?? $data['filesize'] ?? 0 ) ?: 0;
	}

	/**
	 * Returns the largest acceptable image size's details.
	 * Skips the original image, which may also be acceptable.
	 *
	 * @since 5.0.0
	 * @todo Can we maintain an aspect ratio? This must be registered first with WP, so it's unlikely.
	 *
	 * @param int $id           The image ID.
	 * @param int $max_size     The largest acceptable dimension in pixels. Accounts for both width and height.
	 * @param int $max_filesize The largest acceptable filesize in bytes. Default 5MB (5242880).
	 * @return array|false {
	 *     Array of image data, or boolean false if no image is available.
	 *
	 *     @type string $0 Image source URL.
	 *     @type int    $1 Image width in pixels.
	 *     @type int    $2 Image height in pixels.
	 *     @type bool   $3 Whether the image is a resized image.
	 * }
	 */
	public static function get_largest_image_src( $id, $max_size = 4096, $max_filesize = 5242880 ) {

		// Imply there's a correct ID set. When there's not, the loop won't run.
		$sizes = \wp_get_attachment_metadata( $id )['sizes'] ?? [];

		// law = largest accepted width.
		$law  = 0;
		$size = '';

		foreach ( $sizes as $_s => $_d ) {
			if ( ( $_d['filesize'] ?? 0 ) > $max_filesize )
				continue;

			if (
				   isset( $_d['width'], $_d['height'] )
				&& $_d['width'] > $law
				&& $_d['width'] <= $max_size
				&& $_d['height'] <= $max_size
			) {
				$law  = $_d['width'];
				$size = $_s;
				// Keep looping to find the largest acceptable width ($law).
			}
		}

		return $size ? \wp_get_attachment_image_src( $id, $size ) : false;
	}
}
