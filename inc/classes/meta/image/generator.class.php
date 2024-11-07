<?php
/**
 * @package The_SEO_Framework\Classes\Meta\Image
 * @subpackage The_SEO_Framework\Meta\Image
 */

namespace The_SEO_Framework\Meta\Image;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\{
	Data,
	Helper\Query,
	Helper\Format,
};

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
 * Generates images.
 *
 * @since 5.0.0
 * @access private
 */
final class Generator {

	/**
	 * @since 4.1.4
	 * @var int MAX_CONTENT_IMAGES The maximum number of images to get from the content.
	 */
	private const MAX_CONTENT_IMAGES = 5;

	/**
	 * Generates image URLs and IDs from the attachment page entry.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 No longer yields if there's obviously no URL.
	 * @generator
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @param string     $size The size of the image to get.
	 * @yield array {
	 *     The image details.
	 *
	 *     @type string $url The image URL.
	 *     @type int    $id  The image ID.
	 * }
	 */
	public static function generate_attachment_image_details( $args = null, $size = 'full' ) {

		$id  = $args['id'] ?? Query::get_the_real_id();
		$url = $id ? \wp_get_attachment_image_url( $id, $size ) : '';

		if ( $url )
			yield [
				'url' => $url,
				'id'  => $id,
			];
	}

	/**
	 * Generates image URLs and IDs from the featured image input.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 No longer yields if there's obviously no URL.
	 * @generator
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @param string     $size The size of the image to get.
	 * @yield array {
	 *     The image details.
	 *
	 *     @type string $url The image URL.
	 *     @type int    $id  The image ID.
	 * }
	 */
	public static function generate_featured_image_details( $args = null, $size = 'full' ) {

		$id  = \get_post_thumbnail_id( $args['id'] ?? Query::get_the_real_id() );
		$url = $id ? \wp_get_attachment_image_url( $id, $size ) : '';

		if ( $url )
			yield [
				'url' => $url,
				'id'  => $id,
			];
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
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @yield array {
	 *     The image details.
	 *
	 *     @type string $url The image URL.
	 *     @type int    $id  The image ID.
	 * }
	 */
	public static function generate_content_image_details( $args = null ) {

		if ( isset( $args ) ) {
			if ( empty( $args['tax'] ) && empty( $args['pta'] ) && empty( $args['uid'] ) ) {
				$content = Data\Post::get_content( $args['id'] );
			}
		} elseif ( Query::is_singular() ) {
			// $GLOBALS['pages'] isn't populated here -- let's not try pagination to conserve CPU usage.
			$content = Data\Post::get_content();
		}

		if ( empty( $content ) ) return;

		// \strlen( '<img src=a>' ) === 11; yes, that's a valid self-closing tag with a relative source.
		if ( \strlen( $content ) > 10 && false !== stripos( $content, '<img ' ) ) {
			// Clear what might have unfavourable images.
			$content = Format\HTML::strip_tags_cs(
				$content,
				[
					'space' => [],
					'clear' =>
						[ 'address', 'aside', 'blockquote', 'button', 'canvas', 'code', 'datalist', 'dialog', 'dl', 'fieldset', 'footer', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', 'hgroup', 'iframe', 'input', 'label', 'map', 'menu', 'nav', 'noscript', 'ol', 'object', 'output', 'pre', 'script', 'select', 'style', 'svg', 'table', 'template', 'textarea', 'ul', 'var', 'video' ],
					'strip' => false,
				],
			);

			// TODO can we somehow limit this search to static::MAX_CONTENT_IMAGES?
			// -> We could, via preg_match() and strip content, but the function overhead won't help.
			preg_match_all(
				'/<img\b[^>]+?\bsrc=(["\'])?([^"\'>\s]+)\1?[^>]*?>/mi',
				$content,
				$matches,
				\PREG_SET_ORDER,
			);
		}

		$yielded_images = 0;

		foreach ( $matches ?? [] as $match ) {
			// A relative image URL of '0' is so uncommon it's not worth dealing with that. Skip.
			if ( empty( $match[2] ) ) continue;

			yield [
				'url' => $match[2],
				'id'  => 0,
			];

			if ( ++$yielded_images > static::MAX_CONTENT_IMAGES ) break;
		}
	}

	/**
	 * Generates image URLs and IDs from the fallback image options.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 No longer yields if there's obviously no URL.
	 * @generator
	 *
	 * @yield array {
	 *     The image details.
	 *
	 *     @type string $url The image URL.
	 *     @type int    $id  The image ID.
	 * }
	 */
	public static function generate_fallback_image_details() {

		$url = Data\Plugin::get_option( 'social_image_fb_url' );

		if ( $url )
			yield [
				'url' => $url,
				'id'  => Data\Plugin::get_option( 'social_image_fb_id' ) ?: 0,
			];
	}

	/**
	 * Generates image URLs and IDs from the theme header defaults or options.
	 *
	 * N.B. This output may be randomized.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 No longer yields if there's obviously no URL.
	 * @since 5.0.1 No longer uses `get_custom_header()`, which tried to generate images.
	 * @generator
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @param string     $size The size of the image to get.
	 * @yield array {
	 *     The image details.
	 *
	 *     @type string $url The image URL.
	 *     @type int    $id  The image ID.
	 * }
	 */
	public static function generate_theme_header_image_details( $args = null, $size = 'full' ) {

		$image = \get_theme_mod(
			'header_image_data',
			\get_theme_support( 'custom-header', 'default-image' )
		);

		if ( \is_string( $image ) && $image ) {
			yield [
				'url' => $image,
				'id'  => 0,
			];
		} elseif ( \is_object( $image ) && ! empty( $image->url ) ) {
			if ( empty( $image->attachment_id ) ) { // This property isn't stored by default.
				yield [
					'url' => $image->url,
					'id'  => 0,
				];
			} else {
				$url = \wp_get_attachment_image_url( $image->attachment_id, $size );

				if ( $url )
					yield [
						'url' => $url,
						'id'  => $image->attachment_id,
					];
			}
		}
	}

	/**
	 * Generates image URLs and IDs from the logo modification.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 No longer yields if there's obviously no URL.
	 * @generator
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @param string     $size The size of the image to get.
	 * @yield array {
	 *     The image details.
	 *
	 *     @type string $url The image URL.
	 *     @type int    $id  The image ID.
	 * }
	 */
	public static function generate_site_logo_image_details( $args = null, $size = 'full' ) {

		// WP's _override_custom_logo_theme_mod() sets this to get_option( 'site_icon' ) instead.
		$id  = \get_theme_mod( 'custom_logo' );
		$url = $id ? \wp_get_attachment_image_url( $id, $size ) : '';

		if ( $url )
			yield [
				'url' => $url,
				'id'  => $id,
			];
	}

	/**
	 * Generates image URLs and IDs from site icon options.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 No longer yields if there's obviously no URL.
	 * @generator
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @param string     $size The size of the image to get.
	 * @yield array {
	 *     The image details.
	 *
	 *     @type string $url The image URL.
	 *     @type int    $id  The image ID.
	 * }
	 */
	public static function generate_site_icon_image_details( $args = null, $size = 'full' ) {

		$id  = \get_option( 'site_icon' );
		$url = $id ? \wp_get_attachment_image_url( $id, $size ) : '';

		if ( $url )
			yield [
				'url' => $url,
				'id'  => $id,
			];
	}
}
