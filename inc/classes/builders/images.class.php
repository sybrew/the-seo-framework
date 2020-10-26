<?php
/**
 * @package The_SEO_Framework\Classes\Builders\Images
 * @subpackage The_SEO_Framework\Getters\Image
 */

namespace The_SEO_Framework\Builders;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2020 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Generates images.
 *
 * @since 4.0.0
 */
final class Images {

	/**
	 * The constructor. Or rather, the lack thereof.
	 *
	 * @since 4.0.0
	 */
	private function __construct() { }

	/**
	 * Generates image URLs and IDs from the attachment page entry.
	 *
	 * @since 4.0.0
	 * @generator
	 *
	 * @param array|null $args The query arguments. Accepts 'id' and 'taxonomy'.
	 *                         Leave null to autodetermine query.
	 * @param string     $size The size of the image to get.
	 * @yield array : {
	 *    string url: The image URL location,
	 *    int    id:  The image ID,
	 * }
	 */
	public static function get_attachment_image_details( $args = null, $size = 'full' ) {

		$id = isset( $args['id'] ) ? $args['id'] : \the_seo_framework()->get_the_real_ID();

		if ( $id ) {
			yield [
				'url' => \wp_get_attachment_image_url( $id, $size ) ?: '',
				'id'  => $id,
			];
		} else {
			yield [
				'url' => '',
				'id'  => 0,
			];
		}
	}

	/**
	 * Generates image URLs and IDs from the featured image input.
	 *
	 * @since 4.0.0
	 * @generator
	 *
	 * @param array|null $args The query arguments. Accepts 'id' and 'taxonomy'.
	 *                         Leave null to autodetermine query.
	 * @param string     $size The size of the image to get.
	 * @yield array : {
	 *    string url: The image URL location,
	 *    int    id:  The image ID,
	 * }
	 */
	public static function get_featured_image_details( $args = null, $size = 'full' ) {

		$post_id = isset( $args['id'] ) ? $args['id'] : \the_seo_framework()->get_the_real_ID();
		$id      = \get_post_thumbnail_id( $post_id );

		if ( $id ) {
			yield [
				'url' => \wp_get_attachment_image_url( $id, $size ) ?: '',
				'id'  => $id,
			];
		} else {
			yield [
				'url' => '',
				'id'  => 0,
			];
		}
	}

	/**
	 * Generates image URLs and IDs from the content.
	 *
	 * @since 4.0.0
	 * @since 4.0.5 1. Now strips tags before looking for images.
	 *              2. Now only yields at most 5 images.
	 * @generator
	 * @TODO consider matching these images with wp-content/uploads items via database calls, which is heavy...
	 *       Combine query, instead of using WP API? Only do that for the first image, instead?
	 *
	 * @param array|null $args The query arguments. Accepts 'id' and 'taxonomy'.
	 *                         Leave null to autodetermine query.
	 * @param string     $size The size of the image to get.
	 * @yield array : {
	 *    string url: The image URL location,
	 *    int    id:  The image ID,
	 * }
	 */
	public static function get_content_image_details( $args = null, $size = 'full' ) {

		$tsf = \the_seo_framework();

		if ( null === $args ) {
			if ( $tsf->is_singular() ) {
				$content = $tsf->get_post_content();
			}
		} else {
			if ( $args['taxonomy'] ) {
				$content = '';
			} else {
				$content = $tsf->get_post_content( $args['id'] );
			}
		}

		$matches = [];

		// \strlen( '<img src=a>' ) === 11; yes, that's a valid self-closing tag with a relative source.
		if ( \strlen( $content ) > 10 && false !== stripos( $content, '<img' ) ) {
			$content = $tsf->strip_tags_cs(
				$content,
				[
					'space' => [],
					'clear' =>
						[ 'address', 'aside', 'blockquote', 'dd', 'dl', 'dt', 'fieldset', 'figcaption', 'footer', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', 'li', 'nav', 'ol', 'pre', 'table', 'tfoot', 'ul', 'bdo', 'br', 'button', 'canvas', 'code', 'hr', 'iframe', 'input', 'label', 'link', 'noscript', 'meta', 'option', 'samp', 'script', 'select', 'style', 'svg', 'textarea', 'var', 'video' ],
					'strip' => false,
				]
			);
			// TODO can we somehow limit this search to 5?
			preg_match_all(
				'/<img[^>]+src=(\"|\')?([^\"\'>\s]+)\1?.*?>/mi',
				$content,
				$matches,
				PREG_SET_ORDER
			);
		}

		if ( $matches ) {
			$i = 0;
			foreach ( $matches as $match ) {
				// Assume every URL to be correct? Yes. WordPress assumes that too.
				$the_match = $match[2] ?: '';

				// false-esque matches, like '0', are so uncommon it's not worth dealing with them.
				if ( ! $the_match )
					continue;

				yield [
					'url' => $the_match,
					'id'  => 0,
				];

				// Get no more than 5 images.
				if ( ++$i > 4 ) break;
			}
		} else {
			yield [
				'url' => '',
				'id'  => 0,
			];
		}
	}

	/**
	 * Generates image URLs and IDs from the fallback image options.
	 *
	 * @since 4.0.0
	 * @generator
	 *
	 * @param array|null $args The query arguments. Accepts 'id' and 'taxonomy'.
	 *                         Leave null to autodetermine query.
	 * @param string     $size The size of the image to get.
	 * @yield array : {
	 *    string url: The image URL location,
	 *    int    id:  The image ID,
	 * }
	 */
	public static function get_fallback_image_details( $args = null, $size = 'full' ) {

		$tsf = \the_seo_framework();

		yield [
			'url' => $tsf->get_option( 'social_image_fb_url' ) ?: '',
			'id'  => $tsf->get_option( 'social_image_fb_id' ) ?: 0,
		];
	}

	/**
	 * Generates image URLs and IDs from the theme header defaults or options.
	 *
	 * N.B. This output may be randomized.
	 *
	 * @since 4.0.0
	 * @generator
	 *
	 * @param array|null $args The query arguments. Accepts 'id' and 'taxonomy'.
	 *                         Leave null to autodetermine query.
	 * @param string     $size The size of the image to get.
	 * @yield array : {
	 *    string url: The image URL location,
	 *    int    id:  The image ID,
	 * }
	 */
	public static function get_theme_header_image_details( $args = null, $size = 'full' ) {

		$header = \get_custom_header();

		if ( empty( $header->attachment_id ) ) { // This property isn't returned by default.
			yield [
				'url' => $header->url ?: '',
				'id'  => 0,
			];
		} else {
			yield [
				'url' => \wp_get_attachment_image_url( $header->attachment_id, $size ) ?: '',
				'id'  => $header->attachment_id,
			];
		}
	}

	/**
	 * Generates image URLs and IDs from the logo modification.
	 *
	 * @since 4.0.0
	 * @generator
	 *
	 * @param array|null $args The query arguments. Accepts 'id' and 'taxonomy'.
	 *                         Leave null to autodetermine query.
	 * @param string     $size The size of the image to get.
	 * @yield array : {
	 *    string url: The image URL location,
	 *    int    id:  The image ID,
	 * }
	 */
	public static function get_site_logo_image_details( $args = null, $size = 'full' ) {

		$id = \get_theme_mod( 'custom_logo' );

		if ( $id ) {
			yield [
				'url' => \wp_get_attachment_image_url( $id, $size ) ?: '',
				'id'  => $id,
			];
		} else {
			yield [
				'url' => '',
				'id'  => 0,
			];
		}
	}

	/**
	 * Generates image URLs and IDs from site icon options.
	 *
	 * @since 4.0.0
	 * @generator
	 *
	 * @param array|null $args The query arguments. Accepts 'id' and 'taxonomy'.
	 *                         Leave null to autodetermine query.
	 * @param string     $size The size of the image to get.
	 * @yield array : {
	 *    string url: The image URL location,
	 *    int    id:  The image ID,
	 * }
	 */
	public static function get_site_icon_image_details( $args = null, $size = 'full' ) {

		$id = \get_option( 'site_icon' );

		if ( $id ) {
			yield [
				'url' => \wp_get_attachment_image_url( $id, $size ) ?: '',
				'id'  => $id,
			];
		} else {
			yield [
				'url' => '',
				'id'  => 0,
			];
		}
	}
}
