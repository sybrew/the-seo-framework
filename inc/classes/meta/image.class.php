<?php
/**
 * @package The_SEO_Framework\Classes\Meta
 * @subpackage The_SEO_Framework\Meta\Image
 */

namespace The_SEO_Framework\Meta;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\{
	get_query_type_from_args,
	normalize_generation_args,
};

use \The_SEO_Framework\{
	Data,
	Data\Filter\Sanitize,
	Helper\Query,
	Meta,
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
 * Holds getters for meta tag output.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->image() instead.
 */
class Image {

	/**
	 * @since 5.0.0
	 *
	 * @param array|null $args    The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                            Leave null to autodetermine query.
	 * @param string     $context Caller context. Internally supports 'organization', 'social', and 'oembed'. Default 'social'.
	 * @return string The first valid image URL found, if any.
	 */
	public static function get_first_image_url( $args, $context = 'social' ) {
		return static::get_first_custom_image_url( $args, $context )
			?: static::get_first_generated_image_url( $args, $context );
	}

	/**
	 * @since 5.0.0
	 *
	 * @param array|null $args    The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                            Leave null to autodetermine query.
	 * @param string     $context Caller context. Internally supports 'organization', 'social', and 'oembed'. Default 'social'.
	 * @return string The first valid image URL found, if any.
	 */
	public static function get_first_custom_image_url( $args, $context = 'social' ) {
		return current( static::get_custom_image_details( $args, null, $context ) )['url'] ?? '';
	}

	/**
	 * @since 5.0.0
	 *
	 * @param array|null $args    The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                            Leave null to autodetermine query.
	 * @param string     $context Caller context. Internally supports 'organization', 'social', and 'oembed'. Default 'social'.
	 * @return string The first valid image URL found, if any.
	 */
	public static function get_first_generated_image_url( $args, $context = 'social' ) {
		return current( static::get_generated_image_details( $args, null, $context ) )['url'] ?? '';
	}

	/**
	 * Returns image details.
	 *
	 * @since 4.0.0
	 * @since 4.0.5 The output is now filterable.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 5.0.0 1. Now always obtains cleaned images.
	 *              2. Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param array|null $args    The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                            Leave null to autodetermine query.
	 * @param bool       $single  Whether to fetch one image, or multiple.
	 * @param string     $context Caller context. Internally supports 'organization', 'social', and 'oembed'. Default 'social'.
	 * @return array[] {
	 *     An array of image details.
	 *
	 *     @type string $url      The image URL.
	 *     @type int    $id       The image ID.
	 *     @type int    $width    The image width in pixels.
	 *     @type int    $height   The image height in pixels.
	 *     @type string $alt      The image alt tag.
	 *     @type string $caption  The image caption.
	 *     @type int    $filesize The image filesize in bytes.
	 * }
	 */
	public static function get_image_details( $args = null, $single = false, $context = 'social' ) {
		/**
		 * @since 4.0.5
		 * @since 4.2.0 Now supports the `$args['pta']` index.
		 * @since 5.0.0 Deprecated.
		 * @deprecated
		 * @param array      $details {
		 *     The image details array, sequential.
		 *
		 *     @type string $url      The image URL.
		 *     @type int    $id       The image ID.
		 *     @type int    $width    The image width in pixels.
		 *     @type int    $height   The image height in pixels.
		 *     @type string $alt      The image alt tag.
		 *     @type string $caption  The image caption.
		 *     @type int    $filesize The image filesize in bytes.
		 * }
		 * @param array|null $args    The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
		 *                            Is null when the query is auto-determined.
		 * @param bool       $single  Whether to fetch one image, or multiple.
		 * @param string     $context Caller context. Internally supports 'organization', 'social', and 'oembed'. Default 'social'.
		 * @param bool       $clean   Deprecated. We always clean now.
		 */
		return \apply_filters_deprecated(
			'the_seo_framework_image_details',
			[
				(
					   static::get_custom_image_details( $args, $single, $context )
					?: static::get_generated_image_details( $args, $single, $context )
				),
				$args,
				$single,
				$context,
				true,
			],
			'5.0.0 of The SEO Framework',
			'the_seo_framework_custom_image_details or the_seo_framework_generated_image_details',
		);
	}

	/**
	 * Returns single custom field image details.
	 *
	 * @since 4.0.0
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `get_custom_field_image_details`.
	 *              3. Now accepts `$context`.
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $single  Whether to fetch one image, or multiple.
	 * @param string     $context Caller context. Internally supports 'organization', 'social', and 'oembed'. Default 'social'.
	 * @return array[] {
	 *     An array of image details.
	 *
	 *     @type string $url      The image URL.
	 *     @type int    $id       The image ID.
	 *     @type int    $width    The image width in pixels.
	 *     @type int    $height   The image height in pixels.
	 *     @type string $alt      The image alt tag.
	 *     @type string $caption  The image caption.
	 *     @type int    $filesize The image filesize in bytes.
	 * }
	 */
	public static function get_custom_image_details( $args = null, $single = false, $context = 'social' ) {
		/**
		 * @since 5.0.0
		 * @param array      $details {
		 *     The image details array, sequential.
		 *
		 *     @type string $url      The image URL.
		 *     @type int    $id       The image ID.
		 *     @type int    $width    The image width in pixels.
		 *     @type int    $height   The image height in pixels.
		 *     @type string $alt      The image alt tag.
		 *     @type string $caption  The image caption.
		 *     @type int    $filesize The image filesize in bytes.
		 * }
		 * @param array|null $args    The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
		 *                            Is null when the query is auto-determined.
		 * @param bool       $single  Whether to fetch one image, or multiple.
		 */
		return \apply_filters(
			'the_seo_framework_custom_image_details',
			$single
				? array_filter( [ static::generate_custom_image_details( $args, $context )->current() ] )
				: [ ...static::generate_custom_image_details( $args, $context ) ],
			$args,
			$single,
		);
	}

	/**
	 * Returns single or multiple generates image details.
	 *
	 * @since 4.0.0
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param array|null $args    The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                            Leave null to autodetermine query.
	 * @param bool       $single  Whether to fetch one image, or multiple.
	 * @param string     $context Caller context. Internally supports 'organization', 'social', and 'oembed'. Default 'social'.
	 * @return array[] {
	 *     An array of image details.
	 *
	 *     @type string $url      The image URL.
	 *     @type int    $id       The image ID.
	 *     @type int    $width    The image width in pixels.
	 *     @type int    $height   The image height in pixels.
	 *     @type string $alt      The image alt tag.
	 *     @type string $caption  The image caption.
	 *     @type int    $filesize The image filesize in bytes.
	 * }
	 */
	public static function get_generated_image_details( $args = null, $single = false, $context = 'social' ) {
		/**
		 * @since 5.0.0
		 * @param array      $details {
		 *     The image details array, sequential.
		 *
		 *     @type string $url      The image URL.
		 *     @type int    $id       The image ID.
		 *     @type int    $width    The image width in pixels.
		 *     @type int    $height   The image height in pixels.
		 *     @type string $alt      The image alt tag.
		 *     @type string $caption  The image caption.
		 *     @type int    $filesize The image filesize in bytes.
		 * }
		 * @param array|null $args    The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
		 *                            Is null when the query is auto-determined.
		 * @param bool       $single  Whether to fetch one image, or multiple.
		 * @param string     $context Caller context. Internally supports 'organization', 'social', and 'oembed'. Default 'social'.
		 */
		return \apply_filters(
			'the_seo_framework_generated_image_details',
			$single
				? array_filter( [ static::generate_generated_image_details( $args, $context )->current() ] )
				: [ ...static::generate_generated_image_details( $args, $context ) ],
			$args,
			$single,
			$context,
		);
	}

	/**
	 * Yields generated image details.
	 *
	 * @since 5.0.0
	 * @generator
	 *
	 * @param array|null $args    The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                            Leave null to autodetermine query.
	 * @param string     $context Caller context. Internally supports 'organization', 'social', and 'oembed'. Default 'social'.
	 * @yield array[] {
	 *     An array of image details.
	 *
	 *     @type string $url      The image URL.
	 *     @type int    $id       The image ID.
	 *     @type int    $width    The image width in pixels.
	 *     @type int    $height   The image height in pixels.
	 *     @type string $alt      The image alt tag.
	 *     @type string $caption  The image caption.
	 *     @type int    $filesize The image filesize in bytes.
	 * }
	 */
	public static function generate_image_details( $args = null, $context = 'social' ) {

		foreach ( static::generate_custom_image_details( $args, $context ) as $details ) {
			yield $details;
			$yielded_custom = true;
		}

		empty( $yielded_custom )
			and yield from static::generate_generated_image_details( $args, $context );
	}

	/**
	 * Yields generated image details.
	 * Yes, brilliant name.
	 *
	 * @since 5.0.0
	 * @generator
	 *
	 * @param array|null $args    The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                            Leave null to autodetermine query.
	 * @param string     $context Caller context. Internally supports 'organization', 'social', and 'oembed'. Default 'social'.
	 * @yield array[] {
	 *     An array of image details.
	 *
	 *     @type string $url      The image URL.
	 *     @type int    $id       The image ID.
	 *     @type int    $width    The image width in pixels.
	 *     @type int    $height   The image height in pixels.
	 *     @type string $alt      The image alt tag.
	 *     @type string $caption  The image caption.
	 *     @type int    $filesize The image filesize in bytes.
	 * }
	 */
	public static function generate_custom_image_details( $args = null, $context = 'social' ) {

		if ( isset( $args ) ) {
			yield from static::generate_custom_image_details_from_args( $args, $context );
		} else {
			yield from static::generate_custom_image_details_from_query( $context );
		}
	}

	/**
	 * Yields generated image details.
	 * Yes, brilliant name.
	 *
	 * @since 5.0.0
	 * @generator
	 *
	 * @param array|null $args    The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                            Leave null to autodetermine query.
	 * @param string     $context Caller context. Internally supports 'organization', 'social', and 'oembed'. Default 'social'.
	 * @yield array[] {
	 *     An array of image details.
	 *
	 *     @type string $url      The image URL.
	 *     @type int    $id       The image ID.
	 *     @type int    $width    The image width in pixels.
	 *     @type int    $height   The image height in pixels.
	 *     @type string $alt      The image alt tag.
	 *     @type string $caption  The image caption.
	 *     @type int    $filesize The image filesize in bytes.
	 * }
	 */
	public static function generate_generated_image_details( $args = null, $context = 'social' ) {

		isset( $args ) and normalize_generation_args( $args );

		$params = static::get_image_generation_params( $args, $context );

		foreach (
			static::generate_image_from_callbacks( $args, $params['cbs'], $params['size'], ! $params['multi'] )
			as $details
		) {
			yield $details;
			$yielded_cbs = true;
		}

		empty( $yielded_cbs )
			and yield from static::generate_image_from_callbacks( $args, $params['fallback'], $params['size'], true );
	}

	/**
	 * Yields custom image details from query.
	 *
	 * @since 5.0.0
	 * @since 5.1.0 Is now public.
	 * @generator
	 *
	 * @param string $context Caller context. Internally supports 'organization', 'social', and 'oembed'. Default 'social'.
	 * @yield array {
	 *     The image details array.
	 *
	 *     @type string $url      The image URL.
	 *     @type int    $id       The image ID.
	 *     @type int    $width    The image width in pixels.
	 *     @type int    $height   The image height in pixels.
	 *     @type string $alt      The image alt tag.
	 *     @type string $caption  The image caption.
	 *     @type int    $filesize The image filesize in bytes.
	 * }
	 */
	public static function generate_custom_image_details_from_query( $context = 'social' ) {

		if ( 'organization' === $context ) {
			$details = [
				'url' => Data\Plugin::get_option( 'knowledge_logo_url' ),
				'id'  => Data\Plugin::get_option( 'knowledge_logo_id' ),
			];
		} else {
			if ( Query::is_real_front_page() ) {
				if ( Query::is_static_front_page() ) {
					$details = [
						'url' => Data\Plugin::get_option( 'homepage_social_image_url' ),
						'id'  => Data\Plugin::get_option( 'homepage_social_image_id' ),
					];
					if ( ! $details['url'] ) {
						$details = [
							'url' => Data\Plugin\Post::get_meta_item( '_social_image_url' ),
							'id'  => Data\Plugin\Post::get_meta_item( '_social_image_id' ),
						];
					}
				} else {
					$details = [
						'url' => Data\Plugin::get_option( 'homepage_social_image_url' ),
						'id'  => Data\Plugin::get_option( 'homepage_social_image_id' ),
					];
				}
			} elseif ( Query::is_singular() ) {
				$details = [
					'url' => Data\Plugin\Post::get_meta_item( '_social_image_url' ),
					'id'  => Data\Plugin\Post::get_meta_item( '_social_image_id' ),
				];
			} elseif ( Query::is_editable_term() ) {
				$details = [
					'url' => Data\Plugin\Term::get_meta_item( 'social_image_url' ),
					'id'  => Data\Plugin\Term::get_meta_item( 'social_image_id' ),
				];
			} elseif ( \is_post_type_archive() ) {
				$details = [
					'url' => Data\Plugin\PTA::get_meta_item( 'social_image_url' ),
					'id'  => Data\Plugin\PTA::get_meta_item( 'social_image_id' ),
				];
			}
		}

		if ( ! empty( $details['url'] ) ) {
			$details = Sanitize::image_details( static::merge_extra_image_details( $details, 'full' ) );

			if ( $details['url'] )
				yield $details;
		}
	}

	/**
	 * Yields custom image details from args.
	 *
	 * @since 5.0.0
	 * @since 5.1.0 Is now public.
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 * @param string     $context Caller context. Internally supports 'organization', 'social', and 'oembed'. Default 'social'.
	 * @yield array {
	 *     The image details array.
	 *
	 *     @type string $url      The image URL.
	 *     @type int    $id       The image ID.
	 *     @type int    $width    The image width in pixels.
	 *     @type int    $height   The image height in pixels.
	 *     @type string $alt      The image alt tag.
	 *     @type string $caption  The image caption.
	 *     @type int    $filesize The image filesize in bytes.
	 * }
	 */
	public static function generate_custom_image_details_from_args( $args, $context = 'social' ) {

		normalize_generation_args( $args );

		if ( 'organization' === $context ) {
			$details = [
				'url' => Data\Plugin::get_option( 'knowledge_logo_url' ),
				'id'  => Data\Plugin::get_option( 'knowledge_logo_id' ),
			];
		} else {
			normalize_generation_args( $args );

			if ( $args['tax'] ) {
				$details = [
					'url' => Data\Plugin\Term::get_meta_item( 'social_image_url', $args['id'] ),
					'id'  => Data\Plugin\Term::get_meta_item( 'social_image_id', $args['id'] ),
				];
			} elseif ( $args['pta'] ) {
				$details = [
					'url' => Data\Plugin\PTA::get_meta_item( 'social_image_url', $args['pta'] ),
					'id'  => Data\Plugin\PTA::get_meta_item( 'social_image_id', $args['pta'] ),
				];
			} elseif ( empty( $args['uid'] ) && Query::is_real_front_page_by_id( $args['id'] ) ) {
				$details = [
					'url' => Data\Plugin::get_option( 'homepage_social_image_url' ),
					'id'  => Data\Plugin::get_option( 'homepage_social_image_id' ),
				];

				if ( $args['id'] && ! $details['url'] ) {
					$details = [
						'url' => Data\Plugin\Post::get_meta_item( '_social_image_url', $args['id'] ),
						'id'  => Data\Plugin\Post::get_meta_item( '_social_image_id', $args['id'] ),
					];
				}
			} elseif ( $args['id'] ) {
				$details = [
					'url' => Data\Plugin\Post::get_meta_item( '_social_image_url', $args['id'] ),
					'id'  => Data\Plugin\Post::get_meta_item( '_social_image_id', $args['id'] ),
				];
			}
		}

		if ( ! empty( $details['url'] ) ) {
			$details = Sanitize::image_details( static::merge_extra_image_details( $details, 'full' ) );

			if ( $details['url'] )
				yield $details;
		}
	}

	/**
	 * Returns image generation parameters.
	 *
	 * @since 4.0.0
	 * @since 4.1.1 Now only the 'social' context will fetch images from the content.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 5.0.0 1. Now expects an ID before testing whether an attachment is an image.
	 *              2. Now supports 'organization' context.
	 *
	 * @param array|null $args    The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                            Use null to autodetermine query.
	 * @param string     $context Caller context. Internally supports 'organization', 'social', and 'oembed'. Default 'social'.
	 *                            May be (for example) 'breadcrumb' or 'article' for structured data.
	 * @return array {
	 *     The image generation parameters, associative.
	 *
	 *     @type string  $size     The image size by name.
	 *     @type boolean $multi    Whether multiple images may be returned.
	 *     @type array   $cbs:     An array of image generation callbacks, in order of most important to least.
	 *                             When 'multi' (or $single input) parameter is "false", it will use the first found.
	 *     @type array   $fallback An array of image generation callbacks, in order of most important to least.
	 *                             Only one image is obtained from the fallback, and only if the regular cbs don't
	 *                             return any image.
	 * }
	 */
	private static function get_image_generation_params( $args, $context ) {

		$generator = Image\Generator::class;

		if ( 'organization' === $context ) {
			$cbs = [
				'logo' => [ $generator, 'generate_site_logo_image_details' ],
				'icon' => [ $generator, 'generate_site_icon_image_details' ],
			];
		} else {
			if ( isset( $args ) ) {
				if ( 'single' === get_query_type_from_args( $args ) ) {
					if ( \wp_attachment_is_image( $args['id'] ) ) {
						$cbs = [
							'attachment' => [ $generator, 'generate_attachment_image_details' ],
						];
					} else {
						$cbs = [
							'featured' => [ $generator, 'generate_featured_image_details' ],
						];
						if ( 'social' === $context ) {
							$cbs['content'] = [ $generator, 'generate_content_image_details' ];
						}
					}
				}
			} else {
				if ( Query::is_attachment() ) {
					$cbs = [
						'attachment' => [ $generator, 'generate_attachment_image_details' ],
					];
				} elseif ( Query::is_singular() ) {
					$cbs = [
						'featured' => [ $generator, 'generate_featured_image_details' ],
					];

					if ( 'social' === $context )
						$cbs['content'] = [ $generator, 'generate_content_image_details' ];
				}
			}

			if ( 'social' === $context )
				$fallback = [
					'settings' => [ $generator, 'generate_fallback_image_details' ],
					'header'   => [ $generator, 'generate_theme_header_image_details' ],
					'logo'     => [ $generator, 'generate_site_logo_image_details' ],
					'icon'     => [ $generator, 'generate_site_icon_image_details' ],
				];
		}

		/**
		 * @since 4.0.0
		 * @since 4.2.0 Now supports the `$args['pta']` index.
		 * @param array      $params  {
		 *     The image generation parameters.
		 *
		 *     @type string  $size     The image size to use.
		 *     @type boolean $multi    Whether to allow multiple images to be returned. This may be overwritten by generators to 'false'.
		 *     @type array   $cbs      The callbacks to parse. Ideally be generators, so we can halt remotely.
		 *     @type array   $fallback The callbacks to parse. Ideally be generators, so we can halt remotely.
		 * ];
		 * @param array|null $args    The query arguments. Contains 'id', 'tax', 'pta', and 'uid'.
		 *                            Is null when the query is auto-determined.
		 * @param string     $context Caller context. Internally supports 'organization', 'social', and 'oembed'. Default 'social'.
		 *                            May be (for example) 'breadcrumb' or 'article' for structured data.
		 */
		return \apply_filters(
			'the_seo_framework_image_generation_params',
			[
				'size'     => 'full',
				'multi'    => true,
				'cbs'      => $cbs ?? [],
				'fallback' => $fallback ?? [],
			],
			$args,
			$context,
		);
	}

	/**
	 * Generates image details from callbacks.
	 * Memoizes the callbacks when $args is null.
	 *
	 * @since 5.0.0
	 * @generator
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                           Leave null to autodetermine query.
	 * @param callable[] $cbs    The callbacks to parse. Ideally be generators, so we can halt early.
	 * @param string     $size   The image size to use.
	 * @param bool       $single Whether to fetch one image, or multiple.
	 * @yield array {
	 *     The image details array.
	 *
	 *     @type string $url      The image URL.
	 *     @type int    $id       The image ID.
	 *     @type int    $width    The image width in pixels.
	 *     @type int    $height   The image height in pixels.
	 *     @type string $alt      The image alt tag.
	 *     @type string $caption  The image caption.
	 *     @type int    $filesize The image filesize in bytes.
	 * }
	 */
	private static function generate_image_from_callbacks( $args, $cbs, $size, $single ) {

		if ( isset( $args ) ) {
			foreach ( $cbs as $cb ) {
				foreach ( \call_user_func_array( $cb, [ $args, $size ] ) as $details ) {
					$details = Sanitize::image_details( static::merge_extra_image_details( $details, $size ) );

					if ( $details['url'] ) {
						yield $details;
						if ( $single ) break 2;
					}
				}
			}
		} else {
			// Memoize the query.
			static $m;

			foreach ( $cbs as $cb ) {
				// Grab memoized data from callback, or create an index if it's the first run.
				$memo = &$m[ json_encode( [ $cb, $size ] ) ];

				// If values have already been stored, return those first.
				// The Fiber will continue where it has left off if more images are requested.
				foreach ( $memo['values'] ?? [] as $details ) {
					yield $details;
					if ( $single ) break 2;
				}

				// Simulate the Fiber API. TODO PHP8.0+ make actual Fiber.
				$memo['fiber'] ??= null;
				$fiber           = &$memo['fiber'];

				if ( isset( $fiber ) ) {
					// If Fiber's exhausted, go to next generator cb.
					if ( ! $fiber ) continue;

					// Iterate in current cb if still valid from last run.
					$fiber->next();
				} else {
					$fiber = \call_user_func_array( $cb, [ null, $size ] );
				}

				// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- gotta check and end early.
				while ( $fiber->valid() || ( $fiber = false ) ) {
					$details = Sanitize::image_details( static::merge_extra_image_details(
						$fiber->current(),
						$size,
					) );

					if ( $details['url'] ) {
						yield $memo['values'][] = $details;
						if ( $single ) break 2;
					}

					$fiber->next();
				}
			}
		}
	}

	/**
	 * Adds image dimension and alt parameters to the input details, if any.
	 *
	 * @since 5.0.0
	 *
	 * @param array  $details {
	 *     The image details array, associative.
	 *
	 *     @type string $url    The image URL.
	 *     @type int    $id     The image ID.
	 * }
	 * @param string $size    The size of the image used.
	 * @return array {
	 *     The image details array, associative.
	 *
	 *     @type string $url      The image URL.
	 *     @type int    $id       The image ID.
	 *     @type int    $width    The image width in pixels.
	 *     @type int    $height   The image height in pixels.
	 *     @type string $alt      The image alt tag.
	 *     @type string $caption  The image caption.
	 *     @type int    $filesize The image filesize in bytes.
	 * }
	 */
	public static function merge_extra_image_details( $details, $size = 'full' ) {

		if ( $details['id'] ) {
			// This returns an array with 'width' and 'height' indexes.
			$details += Image\Utils::get_image_dimensions( $details['id'], $size );
			// TODO PHP 8.1+ String unpacking in array, so we can directly add the above to it:
			$details += [
				'alt'      => Image\Utils::get_image_alt_tag( $details['id'] ),
				'caption'  => Image\Utils::get_image_caption( $details['id'] ),
				'filesize' => Image\Utils::get_image_filesize( $details['id'], $size ),
			];
		} else {
			$details += [
				'width'    => 0,
				'height'   => 0,
				'alt'      => '',
				'caption'  => '',
				'filesize' => 0,
			];
		}

		return $details;
	}
}
