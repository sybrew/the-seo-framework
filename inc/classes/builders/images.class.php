<?php
/**
 * @package The_SEO_Framework\Classes\Builders\Images
 * @subpackage The_SEO_Framework\Getters\Image
 */

namespace The_SEO_Framework\Builders;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
	 * @since 4.1.4
	 * @internal
	 * @var int MAX_CONTENT_IMAGES The maximum number of images to get from the content.
	 */
	private const MAX_CONTENT_IMAGES = 5;

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
	 * @param array|null $args The query arguments. Accepts 'id', 'taxonomy', and 'pta'.
	 *                         Leave null to autodetermine query.
	 * @param string     $size The size of the image to get.
	 * @yield array : {
	 *    string url: The image URL location,
	 *    int    id:  The image ID,
	 * }
	 */
	public static function get_attachment_image_details( $args = null, $size = 'full' ) {

		$id = $args['id'] ?? \tsf()->get_the_real_ID();

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
	 * @param array|null $args The query arguments. Accepts 'id', 'taxonomy', and 'pta'.
	 *                         Leave null to autodetermine query.
	 * @param string     $size The size of the image to get.
	 * @yield array : {
	 *    string url: The image URL location,
	 *    int    id:  The image ID,
	 * }
	 */
	public static function get_featured_image_details( $args = null, $size = 'full' ) {

		$post_id = $args['id'] ?? \tsf()->get_the_real_ID();
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
	 * @since 4.2.0 1. Fixed OB1 error causing the first image to be ignored.
	 *              2. Now supports the `$args['pta']` index.
	 * @since 4.2.7 1. No longer accidentally matches `<imganything` or `<img notsrc="source">`.
	 *              2. Can no longer use images from `datalist`, `dialog`, `hgroup`, `menu`, `ol`, `object`, `output`, and `template` elements.
	 *              3. No longer expect images from `dd`, `dt`, `figcaption`, `li`, `tfoot`, `br`, `hr`, `link`, `meta`, `option`, `samp`.
	 * @generator
	 * @TODO consider matching these images with wp-content/uploads items via database calls, which is heavy...
	 *       Combine query, instead of using WP API? Only do that for the first image, instead?
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'taxonomy', and 'pta'.
	 *                         Leave null to autodetermine query.
	 * @param string     $size The size of the image to get. Unused.
	 * @yield array : {
	 *    string url: The image URL location,
	 *    int    id:  The image ID,
	 * }
	 */
	public static function get_content_image_details( $args = null, $size = 'full' ) {

		$tsf = \tsf();

		if ( null === $args ) {
			if ( $tsf->is_singular() ) {
				$content = $tsf->get_post_content();
			}
		} else {
			if ( $args['taxonomy'] || $args['pta'] ) {
				$content = '';
			} else {
				$content = $tsf->get_post_content( $args['id'] );
			}
		}

		$matches = [];

		// \strlen( '<img src=a>' ) === 11; yes, that's a valid self-closing tag with a relative source.
		if ( \strlen( $content ) > 10 && false !== stripos( $content, '<img ' ) ) {
			// Clear what might have unfavourable images.
			$content = $tsf->strip_tags_cs(
				$content,
				[
					'space' => [],
					'clear' =>
						[ 'address', 'aside', 'blockquote', 'button', 'canvas', 'code', 'datalist', 'dialog', 'dl', 'fieldset', 'footer', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', 'hgroup', 'iframe', 'input', 'label', 'map', 'menu', 'nav', 'noscript', 'ol', 'object', 'output', 'pre', 'script', 'select', 'style', 'svg', 'table', 'template', 'textarea', 'ul', 'var', 'video' ],
					'strip' => false,
				]
			);

			// TODO can we somehow limit this search to static::MAX_CONTENT_IMAGES? -> We could, via preg_match(), but the opcodes won't help.
			preg_match_all(
				'/<img\b[^>]+?\bsrc=(["\'])?([^"\'>\s]+)\1?[^>]*?>/mi',
				$content,
				$matches,
				\PREG_SET_ORDER
			);
		}

		if ( $matches ) {
			for ( $i = 0; $i < static::MAX_CONTENT_IMAGES; $i++ ) {
				// Fewer than MAX_CONTENT_IMAGES matched.
				if ( ! isset( $matches[ $i ][2] ) ) break;

				// Assume every URL to be correct? Yes. WordPress assumes that too.
				$url = $matches[ $i ][2];

				// false-esque matches, like '0', are so uncommon it's not worth dealing with them.
				if ( ! $url ) continue;

				yield [
					'url' => $url,
					'id'  => 0,
				];
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
	 * @param array|null $args The query arguments. Accepts 'id', 'taxonomy', and 'pta'.
	 *                         Leave null to autodetermine query. Unused.
	 * @param string     $size The size of the image to get. Unused.
	 * @yield array : {
	 *    string url: The image URL location,
	 *    int    id:  The image ID,
	 * }
	 */
	public static function get_fallback_image_details( $args = null, $size = 'full' ) {

		$tsf = \tsf();

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
	 * @param array|null $args The query arguments. Accepts 'id', 'taxonomy', and 'pta'.
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
	 * @param array|null $args The query arguments. Accepts 'id', 'taxonomy', and 'pta'.
	 *                         Leave null to autodetermine query.
	 * @param string     $size The size of the image to get.
	 * @yield array : {
	 *    string url: The image URL location,
	 *    int    id:  The image ID,
	 * }
	 */
	public static function get_site_logo_image_details( $args = null, $size = 'full' ) {

		// WP's _override_custom_logo_theme_mod() sets this to get_option( 'site_icon' ) instead.
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
	 * @param array|null $args The query arguments. Accepts 'id', 'taxonomy', and 'pta'.
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
