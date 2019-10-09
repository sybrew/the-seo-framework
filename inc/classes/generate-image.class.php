<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Generate_Image
 * @subpackage The_SEO_Framework\Getters\Image
 */

namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\Generate_Image
 *
 * Generates Image SEO data based on content.
 *
 * @since 2.8.0
 */
class Generate_Image extends Generate_Url {

	/**
	 * Returns the image details from cache.
	 * Only to be used within the loop, uses default parameters, inlucing the 'social' context.
	 *
	 * @since 4.0.0
	 * @staticvar array $cache
	 *
	 * @return array The image details array, sequential: int => {
	 *    string url:    The image URL,
	 *    int    id:     The image ID,
	 *    int    width:  The image width in pixels,
	 *    int    height: The image height in pixels,
	 *    string alt:    The image alt tag,
	 * }
	 */
	public function get_image_details_from_cache() {
		static $cache;
		return isset( $cache ) ? $cache : $cache = $this->get_image_details();
	}

	/**
	 * Returns image details.
	 *
	 * @since 4.0.0
	 *
	 * @param array|null $args    The query arguments. Accepts 'id' and 'taxonomy'.
	 *                            Leave null to autodetermine query.
	 * @param bool       $single  Whether to fetch one image, or multiple.
	 * @param string     $context The filter context. Default 'social'.
	 * @param bool       $clean   Whether to clean the image, like stripping duplicates and erroneous items.
	 *                            It's best to leave this enabled, unless you're merging the calls, and clean up yourself.
	 * @return array The image details array, sequential: int => {
	 *    string url:    The image URL,
	 *    int    id:     The image ID,
	 *    int    width:  The image width in pixels,
	 *    int    height: The image height in pixels,
	 *    string alt:    The image alt tag,
	 * }
	 */
	public function get_image_details( $args = null, $single = false, $context = 'social', $clean = true ) {

		if ( $single ) {
			$details = $this->get_custom_field_image_details( $args, $single, false );

			if ( empty( $details[0]['url'] ) )
				$details = $this->get_generated_image_details( $args, $single, $context, false );
		} else {
			$details = array_merge(
				$this->get_custom_field_image_details( $args, $single, false ),
				$this->get_generated_image_details( $args, $single, $context, false )
			);
		}

		return $clean ? $this->s_image_details( $details ) : $details;
	}

	/**
	 * Returns single custom field image details.
	 *
	 * @since 4.0.0
	 *
	 * @param array|null $args   The query arguments. Accepts 'id' and 'taxonomy'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $single Whether to fetch one image, or multiple. Unused, reserved.
	 * @param bool       $clean  Whether to clean the image, like stripping duplicates and erroneous items.
	 *                           It's best to leave this enabled, unless you're merging the calls, and clean up yourself.
	 * @return array The image details array, sequential: int => {
	 *    string url:    The image URL,
	 *    int    id:     The image ID,
	 *    int    width:  The image width in pixels,
	 *    int    height: The image height in pixels,
	 *    string alt:    The image alt tag,
	 * }
	 */
	public function get_custom_field_image_details( $args = null, $single = false, $clean = true ) {

		if ( null === $args ) {
			$details = $this->get_custom_field_image_details_from_query();
		} else {
			$this->fix_generation_args( $args );
			$details = $this->get_custom_field_image_details_from_args( $args );
		}

		return $clean ? $this->s_image_details( $details ) : $details;
	}

	/**
	 * Returns single or multiple generates image details.
	 *
	 * @since 4.0.0
	 *
	 * @param array|null $args    The query arguments. Accepts 'id' and 'taxonomy'.
	 *                            Leave null to autodetermine query.
	 * @param bool       $single  Whether to fetch one image, or multiple.
	 * @param string     $context The filter context. Default 'social'.
	 * @param bool       $clean   Whether to clean the image, like stripping duplicates and erroneous items.
	 *                            It's best to leave this enabled, unless you're merging the calls, and clean up yourself.
	 * @return array The image details array, sequential: int => {
	 *    string url:    The image URL,
	 *    int    id:     The image ID,
	 *    int    width:  The image width in pixels,
	 *    int    height: The image height in pixels,
	 *    string alt:    The image alt tag,
	 * }
	 */
	public function get_generated_image_details( $args = null, $single = false, $context = 'social', $clean = true ) {

		if ( null === $args ) {
			$details = $this->generate_image_details( null, $single, $context );
		} else {
			$this->fix_generation_args( $args );
			$details = $this->generate_image_details( $args, $single, $context );
		}

		return $clean ? $this->s_image_details( $details ) : $details;
	}

	/**
	 * Returns single custom field image details from query.
	 *
	 * @since 4.0.0
	 *
	 * @return array The image details array, sequential: int => {
	 *    string url:    The image URL,
	 *    int    id:     The image ID,
	 *    int    width:  The image width in pixels,
	 *    int    height: The image height in pixels,
	 *    string alt:    The image alt tag,
	 * }
	 */
	protected function get_custom_field_image_details_from_query() {

		if ( $this->is_real_front_page() ) {
			if ( $this->is_static_frontpage() ) {
				$details = [
					'url' => $this->get_option( 'homepage_social_image_url' ),
					'id'  => $this->get_option( 'homepage_social_image_id' ),
				];
				if ( ! $details['url'] ) {
					$details = [
						'url' => $this->get_post_meta_item( '_social_image_url' ),
						'id'  => $this->get_post_meta_item( '_social_image_id' ),
					];
				}
			} else {
				$details = [
					'url' => $this->get_option( 'homepage_social_image_url' ),
					'id'  => $this->get_option( 'homepage_social_image_id' ),
				];
			}
		} elseif ( $this->is_singular() ) {
			$details = [
				'url' => $this->get_post_meta_item( '_social_image_url' ),
				'id'  => $this->get_post_meta_item( '_social_image_id' ),
			];
		} elseif ( $this->is_term_meta_capable() ) {
			$details = [
				'url' => $this->get_term_meta_item( 'social_image_url' ),
				'id'  => $this->get_term_meta_item( 'social_image_id' ),
			];
		} else {
			$details = [
				'url' => '',
				'id'  => 0,
			];
		}

		if ( $details['url'] ) {
			$details = $this->merge_extra_image_details( $details, 'full' );
		} else {
			$details = [
				'url' => '',
				'id'  => 0,
			];
		}

		return [ $details ];
	}

	/**
	 * Returns single custom field image details from arguments.
	 *
	 * @since 4.0.0
	 *
	 * @param array $args The query arguments. Must have 'id' and 'taxonomy'.
	 * @return array The image details array, sequential: int => {
	 *    string url:    The image URL,
	 *    int    id:     The image ID,
	 *    int    width:  The image width in pixels,
	 *    int    height: The image height in pixels,
	 *    string alt:    The image alt tag,
	 * }
	 */
	protected function get_custom_field_image_details_from_args( $args ) {

		if ( $args['taxonomy'] ) {
			$details = [
				'url' => $this->get_term_meta_item( 'social_image_url', $args['id'] ),
				'id'  => $this->get_term_meta_item( 'social_image_id', $args['id'] ),
			];
		} else {
			if ( $this->is_static_frontpage( $args['id'] ) ) {
				$details = [
					'url' => $this->get_option( 'homepage_social_image_url' ),
					'id'  => $this->get_option( 'homepage_social_image_id' ),
				];
				if ( ! $details['url'] ) {
					$details = [
						'url' => $this->get_post_meta_item( '_social_image_url', $args['id'] ),
						'id'  => $this->get_post_meta_item( '_social_image_id', $args['id'] ),
					];
				}
			} elseif ( $this->is_real_front_page_by_id( $args['id'] ) ) {
				$details = [
					'url' => $this->get_option( 'homepage_social_image_url' ),
					'id'  => $this->get_option( 'homepage_social_image_id' ),
				];
			} else {
				$details = [
					'url' => $this->get_post_meta_item( '_social_image_url', $args['id'] ),
					'id'  => $this->get_post_meta_item( '_social_image_id', $args['id'] ),
				];
			}
		}

		if ( $details['url'] ) {
			$details = $this->merge_extra_image_details( $details, 'full' );
		} else {
			$details = [
				'url' => '',
				'id'  => 0,
			];
		}

		return [ $details ];
	}

	/**
	 * Returns image generation parameters.
	 *
	 * @since 4.0.0
	 *
	 * @param array|null $args    The query arguments. Accepts 'id' and 'taxonomy'.
	 *                            Leave null to autodetermine query.
	 * @param string     $context The filter context. Default 'social'.
	 *                            May be (for example) 'breadcrumb' or 'article' for structured data.
	 * @return array The image generation parameters, associative: {
	 *    string  size:     The image size,
	 *    boolean multi:    Whether multiple images may be returned,
	 *    array   cbs:      An array of image generation callbacks, in order of most important to least.
	 *                      When 'multi' (or $single input) parameter is "false", it will use the first found.
	 *    array   fallback: An array of image generaiton callbacks, in order of most important to least,
	 *                      Only one image is obtained from the fallback, and only if the regular cbs don't
	 *                      return any image.
	 * }
	 */
	public function get_image_generation_params( $args = null, $context = 'social' ) {

		if ( null !== $args )
			$this->fix_generation_args( $args );

		$builder = Builders\Images::class;

		if ( null === $args ) {
			if ( $this->is_singular() ) {
				if ( $this->is_attachment() ) {
					$cbs = [
						'attachment' => "$builder::get_attachment_image_details",
					];
				} else {
					$cbs = [
						'featured' => "$builder::get_featured_image_details",
						'content'  => "$builder::get_content_image_details",
					];
				}
			} elseif ( $this->is_term_meta_capable() ) {
				$cbs = [];
			} else {
				$cbs = [];
			}
		} else {
			if ( $args['taxonomy'] ) {
				$cbs = [];
			} else {
				if ( \wp_attachment_is_image( $args['id'] ) ) {
					$cbs = [
						'attachment' => "$builder::get_attachment_image_details",
					];
				} else {
					$cbs = [
						'featured' => "$builder::get_featured_image_details",
						'content'  => "$builder::get_content_image_details",
					];
				}
			}
		}

		if ( 'social' === $context ) {
			$fallback = [
				'settings' => "$builder::get_fallback_image_details",
				'header'   => "$builder::get_theme_header_image_details",
				'logo'     => "$builder::get_site_logo_image_details",
				'icon'     => "$builder::get_site_icon_image_details",
			];
		} else {
			$fallback = [];
		}

		/**
		 * @since 4.0.0
		 * @param array      $params  : [
		 *    string  size:     The image size to use.
		 *    boolean multi:    Whether to allow multiple images to be returned.
		 *    array   cbs:      The callbacks to parse. Ideally be generators, so we can halt remotely.
		 *    array   fallback: The callbacks to parse. Ideally be generators, so we can halt remotely.
		 * ];
		 * @param array|null $args    The query arguments. Contains 'id' and 'taxonomy'.
		 *                            Is null when query is autodetermined.
		 * @param string     $context The filter context. Default 'social'.
		 *                            May be (for example) 'breadcrumb' or 'article' for structured data.
		 */
		return \apply_filters_ref_array(
			'the_seo_framework_image_generation_params',
			[
				[
					'size'     => 'full',
					'multi'    => true,
					'cbs'      => $cbs,
					'fallback' => $fallback,
				],
				$args,
				$context,
			]
		);
	}

	/**
	 * Generates image details.
	 *
	 * @since 4.0.0
	 *
	 * @param array|null $args    The query arguments. Accepts 'id' and 'taxonomy'.
	 *                            Leave null to autodetermine query.
	 * @param bool       $single  Whether to fetch one image, or multiple.
	 * @param string     $context The context of the image generation, albeit 'social', 'schema', etc.
	 * @return array The image details array, sequential: int => {
	 *    string url:    The image URL,
	 *    int    id:     The image ID,
	 *    int    width:  The image width in pixels,
	 *    int    height: The image height in pixels,
	 *    string alt:    The image alt tag,
	 * }
	 */
	protected function generate_image_details( $args, $single = true, $context = 'social' ) {

		$params = $this->get_image_generation_params( $args, $context );
		$single = $single || ! $params['multi'];

		// TODO s_image_details() here? The cbs may be discarded, and then we won't obtain any fallbacks...
		$details = $this->process_image_cbs( $params['cbs'], $args, $params['size'], $single )
				?: $this->process_image_cbs( $params['fallback'], $args, $params['size'], true );

		return $details;
	}

	/**
	 * Processes image detail callbacks.
	 *
	 * @since 4.0.0
	 *
	 * @param array      $cbs    The callbacks to parse. Ideally be generators, so we can halt early.
	 * @param array|null $args   The query arguments. Accepts 'id' and 'taxonomy'.
	 *                           Leave null to autodetermine query.
	 * @param sring      $size   The image size to use.
	 * @param bool       $single Whether to fetch one image, or multiple.
	 * @return array The image details array, sequential: int => {
	 *    string url:    The image URL,
	 *    int    id:     The image ID,
	 *    int    width:  The image width in pixels,
	 *    int    height: The image height in pixels,
	 *    string alt:    The image alt tag,
	 * }
	 */
	protected function process_image_cbs( $cbs, $args, $size, $single ) {

		$items = [];
		$i     = 0;

		foreach ( $cbs as $cb ) {
			foreach ( call_user_func_array( $cb, [ $args, $size ] ) as $details ) {
				if ( $details['url'] && $this->s_url_query( $details['url'] ) ) {
					$items[ $i++ ] = $this->merge_extra_image_details( $details, $size );
					if ( $single ) break 2;
				}
			}
		}

		return $items;
	}

	/**
	 * Adds image dimension and alt parameters to the input details, if any.
	 *
	 * @since 4.0.0
	 *
	 * @param array  $details The image details array, associative: {
	 *    string url:    The image URL,
	 *    int    id:     The image ID,
	 * }
	 * @param string $size    The size of the image used.
	 * @return array The image details array, associative: {
	 *    string url:    The image URL,
	 *    int    id:     The image ID,
	 *    int    width:  The image width in pixels,
	 *    int    height: The image height in pixels,
	 *    string alt:    The image alt tag,
	 * }
	 */
	public function merge_extra_image_details( array $details, $size = 'full' ) {

		$details += $this->get_image_dimensions( $details['id'], $details['url'], $size );
		$details += [ 'alt' => $this->get_image_alt_tag( $details['id'] ) ];

		return $details;
	}

	/**
	 * Generates image dimensions.
	 *
	 * @since 4.0.0
	 *
	 * @param int    $src_id The source ID of the image.
	 * @param string $url    The source URL of the image. Ideally related to the $src_id.
	 * @param string $size   The size of the image used.
	 * @return array The image dimensions, associative: {
	 *    int width:  The image width in pixels,
	 *    int height: The image height in pixels,
	 * }
	 */
	public function get_image_dimensions( $src_id, $url, $size ) {

		$image = \wp_get_attachment_image_src( $src_id, $size );

		$dimensions = [
			'width'  => 0,
			'height' => 0,
		];

		if ( $image ) {
			list( $src, $width, $height ) = $image;

			$test_src = \esc_url_raw( $this->set_url_scheme( $src, 'https' ), [ 'https', 'http' ] );
			$test_url = \esc_url_raw( $this->set_url_scheme( $url, 'https' ), [ 'https', 'http' ] );

			if ( $test_src === $test_url ) {
				$dimensions = [
					'width'  => $width,
					'height' => $height,
				];
			}
		}

		return $dimensions;
	}

	/**
	 * Generates image dimensions.
	 *
	 * @since 4.0.0
	 *
	 * @param int $src_id The source ID of the image.
	 * @return string The image alt tag
	 */
	public function get_image_alt_tag( $src_id ) {
		// phpcs:ignore, WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- Fix `wp_get_attachment_image()` first.
		return $src_id ? trim( strip_tags( \get_post_meta( $src_id, '_wp_attachment_image_alt', true ) ) ) : '';
	}

	/**
	 * Returns the largest acceptable image size's details.
	 *
	 * @since 4.0.2
	 *
	 * @param int $id       The image ID.
	 * @param int $max_size The largest acceptable size in pixels. Accounts for both width and height.
	 * @return false|array Returns an array (url, width, height, is_intermediate), or false, if no image is available.
	 */
	public function get_largest_acceptable_image_src( $id, $max_size = 4096 ) {

		// Imply there's a correct ID set. When there's not, the loop won't run.
		$meta  = \wp_get_attachment_metadata( $id );
		$sizes = ! empty( $meta['sizes'] ) && is_array( $meta['sizes'] ) ? $meta['sizes'] : [];

		// law = largest accepted width.
		$law  = 0;
		$size = '';

		foreach ( $sizes as $_s => $_d ) {
			if ( isset( $_d['width'], $_d['height'] ) ) {
				if ( $_d['width'] <= $max_size && $_d['height'] <= $max_size && $_d['width'] > $law ) {
					$law  = $_d['width'];
					$size = $_s;
				}
			}
		}

		return $size ? \wp_get_attachment_image_src( $id, $size ) : false;
	}
}
