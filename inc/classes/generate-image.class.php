<?php
/**
 * @package The_SEO_Framework\Classes
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
	 * Holds the image dimensions, if found.
	 *
	 * @since 2.7.0
	 *
	 * @var array
	 */
	public $image_dimensions = array();

	/**
	 * Constructor, loads parent constructor.
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Returns image URL suitable for Schema items.
	 *
	 * These are images that are strictly assigned to the Post or Page.
	 * Themes should compliment these. If not, then Open Graph should at least
	 * compliment these.
	 * If that's not even true, then I don't know what happens. But then you're
	 * in a grey area... @TODO make images optional for Schema?
	 *
	 * @since 2.9.3
	 * @uses $this->get_social_image()
	 * @staticvar array $images
	 *
	 * @TODO support Terms.
	 *
	 * @param int|string $id The page, post, product or term ID.
	 * @param bool $singular Whether the ID is singular or archival.
	 * @return string $url The Schema.org safe image.
	 */
	public function get_schema_image( $id = 0, $singular = false ) {

		//= TODO remove this when term images are introduced.
		if ( ! $singular )
			return '';

		static $images = array();

		$id = (int) $id;

		if ( isset( $images[ $id ][ $singular ] ) )
			return $images[ $id ][ $singular ];

		if ( $singular ) {
			if ( $id === $this->get_the_front_page_ID() ) {
				if ( $this->has_page_on_front() ) {
					$image_args = array(
						'post_id' => $id,
						'skip_fallback' => true,
						'escape' => false,
					);
				} else {
					$image_args = array(
						'post_id' => $id,
						'skip_fallback' => true,
						'disallowed' => array(
							'postmeta',
							'featured',
						),
						'escape' => false,
					);
				}
			} else {
				$image_args = array(
					'post_id' => $id,
					'skip_fallback' => true,
					'disallowed' => array(
						'homemeta',
					),
					'escape' => false,
				);
			}
			$url = $this->get_social_image( $image_args, false );
		} else {
			//* Placeholder for when Terms get image uploads.
			$url = '';
		}

		/**
		 * Applies filters 'the_seo_framework_ld_json_breadcrumb_image' : string
		 * @since 2.7.0
		 * @param string $image The current image.
		 * @param int $id The page, post, product or term ID.
		 * @param bool $singular Whether the ID is singular.
		 */
		$url = \apply_filters( 'the_seo_framework_ld_json_breadcrumb_image', $url, $id, $singular );

		return $images[ $id ][ $singular ] = \esc_url_raw( $url );
	}

	/**
	 * Returns social image URL and sets $this->image_dimensions.
	 *
	 * @since 2.9.0
	 *
	 * @todo listen to attached images within post.
	 * @todo listen to archive images.
	 *
	 * @param array $args The image arguments.
	 * @param bool $set_og_dimension Whether to set open graph dimensions.
	 * @return string The social image.
	 */
	public function get_social_image( $args = array(), $set_og_dimension = false ) {

		$args = $this->reparse_image_args( $args );

		//* 0. Image from argument.
		pre_0 : {
			if ( $image = $args['image'] )
				goto end;
		}

		//* Check if there are no disallowed arguments.
		$all_allowed = empty( $args['disallowed'] );

		//* 1. Fetch image from homepage SEO meta upload.
		if ( $all_allowed || false === in_array( 'homemeta', $args['disallowed'], true ) ) {
			if ( $image = $this->get_social_image_url_from_home_meta( $args['post_id'], true ) )
				goto end;
		}

		if ( $args['post_id'] ) {
			//* 2. Fetch image from SEO meta upload.
			if ( $all_allowed || false === in_array( 'postmeta', $args['disallowed'], true ) ) {
				if ( $image = $this->get_social_image_url_from_post_meta( $args['post_id'], true ) )
					goto end;
			}

			//* 3. Fetch image from featured.
			if ( $all_allowed || false === in_array( 'featured', $args['disallowed'], true ) ) {
				if ( $image = $this->get_social_image_url_from_post_thumbnail( $args['post_id'], $args, true ) )
					goto end;
			}
		}

		if ( $args['skip_fallback'] )
			goto end;

		//* 4. Fetch image from SEO settings
		if ( $all_allowed || false === in_array( 'option', $args['disallowed'], true ) ) {
			if ( $image = $this->get_social_image_url_from_seo_settings( true ) )
				goto end;
		}

		//* 5. Fetch image from fallback filter 1
		/**
		 * Applies filters 'the_seo_framework_og_image_after_featured' : string
		 * @since 2.5.2
		 *
		 * @param string $image   The image URL.
		 * @param int    $post_id The post ID.
		 */
		fallback_1 : {
			if ( $image = (string) \apply_filters( 'the_seo_framework_og_image_after_featured', '', $args['post_id'] ) )
				goto end;
		}

		//* 6. Fallback: Get header image if exists
		if ( ( $all_allowed || false === in_array( 'header', $args['disallowed'], true ) ) && \current_theme_supports( 'custom-header', 'default-image' ) ) {
			if ( $image = $this->get_header_image( true ) )
				goto end;
		}

		//* 7. Fetch image from fallback filter 2
		/**
		 * Applies filters 'the_seo_framework_og_image_after_header' : string
		 * @since 2.5.2
		 *
		 * @param string $image   The image URL.
		 * @param int    $post_id The post ID.
		 */
		fallback_2 : {
			if ( $image = (string) \apply_filters( 'the_seo_framework_og_image_after_header', '', $args['post_id'] ) )
				goto end;
		}

		//* 8. Get the WP 4.5 Site Logo
		if ( ( $all_allowed || false === in_array( 'logo', $args['disallowed'], true ) ) && $this->can_use_logo() ) {
			if ( $image = $this->get_site_logo( true ) )
				goto end;
		}

		//* 9. Get the WP 4.3 Site Icon
		if ( $all_allowed || false === in_array( 'icon', $args['disallowed'], true ) ) {
			if ( $image = $this->get_site_icon( 'full', true ) )
				goto end;
		}

		end :;

		if ( $args['escape'] && $image )
			$image = \esc_url( $image );

		return (string) $image;
	}

	/**
	 * Parse and sanitize image args.
	 *
	 * @since 2.5.0
	 * @since 2.9.0 : 1. Removed 'attr' index, it was unused.
	 *                2. Added 'skip_fallback' option.
	 *                3. Added 'escape' option.
	 *
	 * The image set in the filter will always be used as fallback
	 *
	 * @param array $args required The passed arguments.
	 * @param array $defaults The default arguments.
	 * @param bool $get_defaults Return the default arguments. Ignoring $args.
	 * @return array $args parsed args.
	 */
	public function parse_image_args( $args = array(), $defaults = array(), $get_defaults = false ) {

		//* Passing back the defaults reduces the memory usage.
		if ( empty( $defaults ) ) {
			$defaults = array(
				'post_id'    => $this->get_the_real_ID(),
				'image'      => '',
				'size'       => 'full',
				'icon'       => false,
				'skip_fallback' => false,
				'disallowed' => array(),
				'escape'     => true,
			);

			/**
			 * Applies filters the_seo_framework_og_image_args : array
			 *
			 * @since 2.0.1
			 *
			 * @param array $defaults The image defaults: {
			 *    @param string $image The image url
			 *    @param mixed $size The image size
			 *    @param bool $icon Fetch Image icon
			 *    @param bool 'skip_fallback' Whether to skip fallback images.
			 *    @param array $disallowed Disallowed image types : {
			 *        array (
			 *            string 'featured'
			 *            string 'header'
			 *            string 'icon'
			 *        )
			 *    }
			 *    @param bool 'escape' Whether to escape output.
			 * }
			 * @param array $args The input args.
			 */
			$defaults = (array) \apply_filters( 'the_seo_framework_og_image_args', $defaults, $args );
		}

		//* Return early if it's only a default args request.
		if ( $get_defaults )
			return $defaults;

		//* Array merge doesn't support sanitation. We're simply type casting here.
		$args['post_id']       = isset( $args['post_id'] )       ? (int) $args['post_id']        : $defaults['post_id'];
		$args['image']         = isset( $args['image'] )         ? (string) $args['image']       : $defaults['image'];
		$args['size']          = isset( $args['size'] )          ? $args['size']                 : $defaults['size']; // Mixed.
		$args['icon']          = isset( $args['icon'] )          ? (bool) $args['icon']          : $defaults['icon'];
		$args['skip_fallback'] = isset( $args['skip_fallback'] ) ? (bool) $args['skip_fallback'] : $defaults['skip_fallback'];
		$args['disallowed']    = isset( $args['disallowed'] )    ? (array) $args['disallowed']   : $defaults['disallowed'];
		$args['escape']        = isset( $args['escape'] )        ? (bool) $args['escape']        : $defaults['escape'];

		return $args;
	}

	/**
	 * Reparses image args.
	 *
	 * @since 2.6.6
	 * @since 2.9.2 Now passes args to filter.
	 *
	 * @param array $args required The passed arguments.
	 * @return array $args parsed args.
	 */
	public function reparse_image_args( $args = array() ) {

		$default_args = $this->parse_image_args( $args, '', true );

		if ( empty( $args ) ) {
			$args = $default_args;
		} else {
			$args = $this->parse_image_args( $args, $default_args );
		}

		return $args;
	}

	/**
	 * Returns unescaped HomePage settings image URL from post ID input.
	 *
	 * @since 2.9.0
	 * @since 2.9.4 Now converts URL scheme.
	 * @uses $this->image_dimensions
	 *
	 * @param int $id The post ID.
	 * @param bool $set_og_dimensions Whether to set Open Graph and Twitter dimensions.
	 * @return string The unescaped HomePage social image URL.
	 */
	public function get_social_image_url_from_home_meta( $id = 0, $set_og_dimensions = false ) {

		//* Don't output if not front page.
		if ( false === $this->is_front_page_by_id( $id ) )
			return '';

		$src = $this->get_option( 'homepage_social_image_url' );

		if ( ! $src )
			return '';

		//* Calculate image sizes.
		if ( $set_og_dimensions && $img_id = $this->get_option( 'homepage_social_image_id' ) ) {
			$_src = \wp_get_attachment_image_src( $img_id, 'full' );

			$i = $_src[0]; // Source URL
			$w = $_src[1]; // Width
			$h = $_src[2]; // Height

			$test_i = \esc_url_raw( $this->set_preferred_url_scheme( $i ), array( 'http', 'https' ) );
			$test_src = \esc_url_raw( $this->set_preferred_url_scheme( $src ), array( 'http', 'https' ) );

			if ( $test_i === $test_src )
				$this->image_dimensions = $this->image_dimensions + array( $id => array( 'width' => $w, 'height' => $h ) );
		}

		if ( $src && $this->matches_this_domain( $src ) )
			$src = $this->set_preferred_url_scheme( $src );

		return $src;
	}

	/**
	 * Returns unescaped Post settings image URL from post ID input.
	 *
	 * @since 2.8.0
	 * @since 2.9.0 1. The second parameter now works.
	 *              2. Fallback image ID has been removed.
	 * @since 2.9.4 Now converts URL scheme.
	 * @uses $this->image_dimensions
	 *
	 * @param int $id The post ID. Required.
	 * @param bool $set_og_dimensions Whether to set Open Graph and Twitter dimensions.
	 * @return string The unescaped social image URL.
	 */
	public function get_social_image_url_from_post_meta( $id, $set_og_dimensions = false ) {

		$src = $id ? $this->get_custom_field( '_social_image_url', $id ) : '';

		if ( ! $src )
			return '';

		//* Calculate image sizes.
		if ( $set_og_dimensions && $img_id = $this->get_custom_field( '_social_image_id', $id ) ) {
			$_src = \wp_get_attachment_image_src( $img_id, 'full' );

			$i = $_src[0]; // Source URL
			$w = $_src[1]; // Width
			$h = $_src[2]; // Height

			$test_i = \esc_url_raw( $this->set_preferred_url_scheme( $i ), array( 'http', 'https' ) );
			$test_src = \esc_url_raw( $this->set_preferred_url_scheme( $src ), array( 'http', 'https' ) );

			if ( $test_i === $test_src )
				$this->image_dimensions = $this->image_dimensions + array( $id => array( 'width' => $w, 'height' => $h ) );
		}

		if ( $src && $this->matches_this_domain( $src ) )
			$src = $this->set_preferred_url_scheme( $src );

		return $src;
	}

	/**
	 * Returns unescaped URL from options input.
	 *
	 * @since 2.8.2
	 * @since 2.9.4 1: Now converts URL scheme.
	 *              2: $set_og_dimensions now works.
	 * @uses $this->image_dimensions
	 *
	 * @param bool $set_og_dimensions Whether to set open graph and twitter dimensions.
	 * @return string The unescaped social image fallback URL.
	 */
	public function get_social_image_url_from_seo_settings( $set_og_dimensions = false ) {

		$src = $this->get_option( 'social_image_fb_url' );

		if ( ! $src )
			return '';

		//* Calculate image sizes.
		if ( $set_og_dimensions && $img_id = $this->get_option( 'social_image_fb_id' ) ) {
			$_src = \wp_get_attachment_image_src( $img_id, 'full' );

			$i = $_src[0]; // Source URL
			$w = $_src[1]; // Width
			$h = $_src[2]; // Height

			$test_i = \esc_url_raw( $this->set_preferred_url_scheme( $i ), array( 'http', 'https' ) );
			$test_src = \esc_url_raw( $this->set_preferred_url_scheme( $src ), array( 'http', 'https' ) );

			if ( $test_i === $test_src )
				$this->image_dimensions = $this->image_dimensions + array( $this->get_the_real_ID() => array( 'width' => $w, 'height' => $h ) );
		}

		if ( $src && $this->matches_this_domain( $src ) )
			$src = $this->set_preferred_url_scheme( $src );

		return $src;
	}

	/**
	 * Fetches image from post thumbnail.
	 *
	 * Resizes the image between 4096px if bigger. Then it saves the image and
	 * Keeps dimensions relative.
	 *
	 * @since 2.9.0
	 * @since 2.9.3 Now supports 4K.
	 * @since 2.9.4 Now converts URL scheme.
	 *
	 * @param int $id The post ID. Required.
	 * @param array $args The image args.
	 * @param bool $set_og_dimensions Whether to set Open Graph image dimensions.
	 * @return string The social image URL.
	 */
	public function get_social_image_url_from_post_thumbnail( $id, $args = array(), $set_og_dimensions = false ) {

		$image_id = $id ? \get_post_thumbnail_id( $id ) : '';

		if ( ! $image_id )
			return '';

		$args = $this->reparse_image_args( $args );
		$args['get_the_real_ID'] = true;

		$src = $this->parse_og_image( $image_id, $args, $set_og_dimensions );

		if ( $src && $this->matches_this_domain( $src ) )
			$src = $this->set_preferred_url_scheme( $src );

		return $src;
	}

	/**
	 * Fetches images id's from WooCommerce gallery
	 *
	 * @since 2.5.0
	 * @staticvar array $ids The image IDs
	 *
	 * @param array $args Image arguments.
	 * @return array The image URL's.
	 */
	public function get_image_from_woocommerce_gallery() {

		static $ids = null;

		if ( isset( $ids ) )
			return $ids;

		$attachment_ids = '';

		$post_id = $this->get_the_real_ID();

		if ( \metadata_exists( 'post', $post_id, '_product_image_gallery' ) ) {
			$product_image_gallery = \get_post_meta( $post_id, '_product_image_gallery', true );

			$attachment_ids = array_map( 'absint', array_filter( explode( ',', $product_image_gallery ) ) );
		}

		return $ids = $attachment_ids;
	}

	/**
	 * Parses OG image to correct size.
	 *
	 * @since 2.5.0
	 * @since 2.8.0 : 1. Removed staticvar.
	 *                2. Now adds ID call to OG image called listener.
	 * @since 2.9.0 : Added $set_og_dimension parameter
	 * @since 2.9.3 : 4k baby.
	 * @since 3.0.0 : Now sets preferred canonical URL scheme.
	 *
	 * @todo create formula to fetch transient.
	 * @priority high 2.7.0
	 * @prioerity lowered with 4K @ 2.9.3
	 *
	 * @param int $id The attachment ID.
	 * @param array $args The image args
	 * @param bool $set_og_dimensions Whether to set OG dimensions.
	 * @return string Parsed image url or empty if already called
	 */
	public function parse_og_image( $id, $args = array(), $set_og_dimensions = false ) {

		//* Don't do anything if $id isn't given.
		if ( empty( $id ) )
			return;

		if ( empty( $args ) )
			$args = $this->reparse_image_args( $args );

		$src = \wp_get_attachment_image_src( $id, $args['size'], $args['icon'] );

		$i = $src[0]; // Source URL
		$w = $src[1]; // Width
		$h = $src[2]; // Height

		//* @TODO add filter that can lower it?
		$_size = 4096;

		//* Preferred 4096px, resize it
		if ( $w > $_size || $h > $_size ) :

			if ( $w === $h ) {
				//* Square
				$w = $_size;
				$h = $_size;
			} elseif ( $w > $h ) {
				//* Landscape, set $w to 4096.
				$h = $this->proportionate_dimensions( $h, $w, $w = $_size );
			} elseif ( $h > $w ) {
				//* Portrait, set $h to 4096.
				$w = $this->proportionate_dimensions( $w, $h, $h = $_size );
			}

			//* Get path of image and load it into the wp_get_image_editor
			$i_file_path = \get_attached_file( $id );
			$i_file_ext  = pathinfo( $i_file_path, PATHINFO_EXTENSION );

			if ( $i_file_ext ) {
				$i_file_dir_name = pathinfo( $i_file_path, PATHINFO_DIRNAME );
				//* Add trailing slash.
				$i_file_dir_name = '/' === substr( $i_file_dir_name, -1 ) ? $i_file_dir_name : $i_file_dir_name . '/';

				$i_file_file_name = pathinfo( $i_file_path, PATHINFO_FILENAME );

				//* Yes I know, I should use generate_filename(), but it's slower.
				//* Will look at that later. This is already 100 lines of correctly working code.
				$new_image_dirfile = $i_file_dir_name . $i_file_file_name . '-' . $w . 'x' . $h . '.' . $i_file_ext;

				//* Generate image URL.
				$upload_dir     = \wp_upload_dir();
				$upload_url     = $upload_dir['baseurl'];
				$upload_basedir = $upload_dir['basedir'];

				//* We've got our image path.
				$i = str_ireplace( $upload_basedir, '', $new_image_dirfile );
				$i = $upload_url . $i;

				// Generate file if it doesn't exists yet.
				if ( ! file_exists( $new_image_dirfile ) ) {

					$image_editor = \wp_get_image_editor( $i_file_path );

					if ( ! \is_wp_error( $image_editor ) ) {
						$image_editor->resize( $w, $h, false );
						$image_editor->set_quality( 82 ); // Let's save some bandwidth, Facebook compresses it even further anyway.
						$image_editor->save( $new_image_dirfile );
					} else {
						//* Image has failed to create.
						$i = '';
					}
				}
			}
		endif;

		if ( $set_og_dimensions ) {
			//* Whether to use the post ID (Post Thumbnail) or input ID (ID was known beforehand)
			$usage_id = ! empty( $args['get_the_real_ID'] ) ? $this->get_the_real_ID() : $id;

			$this->image_dimensions = $this->image_dimensions + array( $usage_id => array( 'width' => $w, 'height' => $h ) );
		}

		if ( $i && $this->matches_this_domain( $i ) )
			$i = $this->set_preferred_url_scheme( $i );

		return $i;
	}

	/**
	 * Fetches site icon brought in WordPress 4.3
	 *
	 * @since 2.8.0
	 * @since 3.0.0 : Now sets preferred canonical URL scheme.
	 *
	 * @param string|int $size The icon size, accepts 'full' and pixel values.
	 * @param bool $set_og_dimensions Whether to set size for OG image. Always falls back to the current post ID.
	 * @return string URL site icon, not escaped.
	 */
	public function get_site_icon( $size = 'full', $set_og_dimensions = false ) {

		$icon = '';

		if ( 'full' === $size ) {
			$site_icon_id = \get_option( 'site_icon' );

			if ( $site_icon_id ) {
				$url_data = '';
				$url_data = \wp_get_attachment_image_src( $site_icon_id, $size );

				$icon = $url_data ? $url_data[0] : '';

				if ( $set_og_dimensions && $icon ) {
					$w = $url_data[1];
					$h = $url_data[2];

					$this->image_dimensions = $this->image_dimensions + array( $this->get_the_real_ID() => array( 'width' => $w, 'height' => $h ) );
				}
			}
		} elseif ( is_int( $size ) && function_exists( 'has_site_icon' ) ) {
			//* Above 512 defaults to full. Loop back.
			if ( $size > 512 )
				return $this->get_site_icon( 'full', $set_og_dimensions );

			//* Also applies (MultiSite) filters.
			$icon = \get_site_icon_url( $size );
		}

		if ( $icon && $this->matches_this_domain( $icon ) )
			$icon = $this->set_preferred_url_scheme( $icon );

		return $icon;
	}

	/**
	 * Fetches site logo brought in WordPress 4.5
	 *
	 * @since 2.8.0
	 * @since 3.0.0 : Now sets preferred canonical URL scheme.
	 *
	 * @param bool $set_og_dimensions Whether to set size for OG image. Always falls back to the current post ID.
	 * @return string URL site logo, not escaped.
	 */
	public function get_site_logo( $set_og_dimensions = false ) {

		if ( false === $this->can_use_logo() )
			return '';

		$logo = '';

		$site_logo_id = \get_theme_mod( 'custom_logo' );

		if ( $site_logo_id ) {
			$url_data = '';
			$url_data = \wp_get_attachment_image_src( $site_logo_id, 'full' );

			$logo = $url_data ? $url_data[0] : '';

			if ( $set_og_dimensions && $logo ) {
				$w = $url_data[1];
				$h = $url_data[2];

				$this->image_dimensions = $this->image_dimensions + array( $this->get_the_real_ID() => array( 'width' => $w, 'height' => $h ) );
			}
		}

		if ( $logo && $this->matches_this_domain( $logo ) )
			$logo = $this->set_preferred_url_scheme( $logo );

		return $logo;
	}

	/**
	 * Returns header image URL.
	 * Also sets image dimensions. Falls back to current post ID for index.
	 *
	 * @since 2.7.0
	 * @since 3.0.0 : Now sets preferred canonical URL scheme.
	 *
	 * @param bool $set_og_dimensions Whether to set size for OG image. Always falls back to the current post ID.
	 * @return string The header image URL, not escaped.
	 */
	public function get_header_image( $set_og_dimensions = false ) {

		$image = \get_header_image();

		if ( $set_og_dimensions && $image ) {

			$w = (int) \get_theme_support( 'custom-header', 'width' );
			$h = (int) \get_theme_support( 'custom-header', 'height' );

			if ( $w && $h )
				$this->image_dimensions = $this->image_dimensions + array( $this->get_the_real_ID() => array( 'width' => $w, 'height' => $h ) );
		}

		if ( $image && $this->matches_this_domain( $image ) )
			$image = $this->set_preferred_url_scheme( $image );

		return $image;
	}
}
