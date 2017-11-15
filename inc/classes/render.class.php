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
 * Class The_SEO_Framework\Render
 *
 * Puts all data into HTML valid meta tags.
 *
 * @since 2.8.0
 */
class Render extends Admin_Init {

	/**
	 * Theme title doing it wrong boolean.
	 *
	 * @since 2.4.0
	 *
	 * @var bool Holds Theme is doing it wrong.
	 */
	protected $title_doing_it_wrong = null;

	/**
	 * Constructor, load parent constructor
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Caches and returns the current URL.
	 *
	 * @since 3.0.0
	 * @staticvar string $cache
	 *
	 * @return string The current URL.
	 */
	public function get_current_canonical_url() {

		static $cache;

		return isset( $cache ) ? $cache : $cache = $this->get_canonical_url();
	}

	/**
	 * Caches and returns the current permalink.
	 * This link excludes any pagination. Great for structured data.
	 *
	 * @since 3.0.0
	 * @staticvar string $cache
	 *
	 * @return string The current permalink.
	 */
	public function get_current_permalink() {

		static $cache;

		return isset( $cache ) ? $cache : $cache = $this->create_canonical_url(
			array( 'id' => $this->get_the_real_ID(), 'is_term' => $this->is_archive() )
		);
	}

	/**
	 * Caches and returns the homepage URL.
	 *
	 * @since 3.0.0
	 * @staticvar string $cache
	 *
	 * @return string The home URL.
	 */
	public function get_homepage_permalink() {

		static $cache;

		return isset( $cache ) ? $cache : $cache = $this->create_canonical_url( array( 'id' => $this->get_the_front_page_ID() ) );
	}

	/**
	 * Cache description in static variable
	 * Must be called inside the loop
	 *
	 * @since 2.2.2
	 * @staticvar array $description_cache
	 *
	 * @return string The description
	 */
	public function description_from_cache( $social = false ) {

		static $description_cache = array();

		if ( isset( $description_cache[ $social ] ) )
			return $description_cache[ $social ];

		return $description_cache[ $social ] = $this->generate_description( '', array( 'social' => $social ) );
	}

	/**
	 * Cache current Title in static variable
	 * Must be called inside the loop
	 *
	 * @since 2.2.2
	 * @since 2.4.0 : If the theme is doing it right, override cache parameters to speed things up.
	 * @staticvar array $title_cache
	 *
	 * @param string $title The Title to return
	 * @param string $sep The Title sepeartor
	 * @param string $seplocation The Title sepeartor location, accepts 'left' or 'right'.
	 * @param bool $meta Ignore theme doing it wrong.
	 * @return string The title
	 */
	public function title_from_cache( $title = '', $sep = '', $seplocation = '', $meta = false ) {

		/**
		 * Cache the inputs, for when the title is doing it right.
		 * Use those values to fetch the cached title.
		 *
		 * @since 2.4.0
		 */
		static $setup_cache,
		       $title_param_cache,
		       $sep_param_cache,
		       $seplocation_param_cache;

		if ( ! $setup_cache ) {
			if ( \doing_filter( 'pre_get_document_title' ) || \doing_filter( 'wp_title' ) ) {
				$title_param_cache = $title;
				$sep_param_cache = $sep;
				$seplocation_param_cache = $seplocation;

				$setup_cache = 'I like turtles.';
			}
		}

		if ( isset( $this->title_doing_it_wrong ) && false === $this->title_doing_it_wrong ) {
			$title = $title_param_cache;
			$sep = $sep_param_cache;
			$seplocation = $seplocation_param_cache;
			$meta = false;
		}

		static $title_cache = array();

		if ( isset( $title_cache[ $title ][ $sep ][ $seplocation ][ $meta ] ) )
			return $title_cache[ $title ][ $sep ][ $seplocation ][ $meta ];

		return $title_cache[ $title ][ $sep ][ $seplocation ][ $meta ] = $this->title( $title, $sep, $seplocation, array( 'meta' => $meta ) );
	}

	/**
	 * Caches current Image URL in static variable.
	 * Must be called inside the loop.
	 *
	 * @since 2.2.2
	 * @since 2.7.0 $get_id parameter has been added.
	 * @staticvar string $cache
	 *
	 * @return string The image URL.
	 */
	public function get_image_from_cache() {

		static $cache = null;

		return isset( $cache ) ? $cache : $cache = $this->get_social_image( array(), true );
	}

	/**
	 * Returns the current Twitter card type.
	 *
	 * @since 2.8.2
	 * @staticvar string $cache
	 *
	 * @return string The cached Twitter card.
	 */
	public function get_current_twitter_card_type() {

		static $cache = null;

		return isset( $cache ) ? $cache : $cache = $this->generate_twitter_card_type();
	}

	/**
	 * Renders the description meta tag.
	 *
	 * @since 1.3.0
	 * @uses $this->description_from_cache()
	 * @uses $this->detect_seo_plugins()
	 *
	 * @return string The description meta tag.
	 */
	public function the_description() {

		if ( $this->detect_seo_plugins() )
			return '';

		/**
		 * Applies filters 'the_seo_framework_description_output' : string
		 * @since 2.3.0
		 * @since 2.7.0 : Added output within filter.
		 */
		$description = (string) \apply_filters( 'the_seo_framework_description_output', $this->description_from_cache(), $this->get_the_real_ID() );

		if ( $description )
			return '<meta name="description" content="' . \esc_attr( $description ) . '" />' . "\r\n";

		return '';
	}

	/**
	 * Renders og:description meta tag
	 *
	 * @since 1.3.0
	 * @uses $this->description_from_cache()
	 *
	 * @return string The Open Graph description meta tag.
	 */
	public function og_description() {

		if ( ! $this->use_og_tags() )
			return '';

		/**
		 * Applies filters 'the_seo_framework_ogdescription_output' : string
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 */
		$description = (string) \apply_filters( 'the_seo_framework_ogdescription_output', $this->description_from_cache( true ), $this->get_the_real_ID() );

		if ( $description )
			return '<meta property="og:description" content="' . \esc_attr( $description ) . '" />' . "\r\n";

		return '';
	}

	/**
	 * Renders the OG locale meta tag.
	 *
	 * @since 1.0.0
	 *
	 * @return string The Open Graph locale meta tag.
	 */
	public function og_locale() {

		if ( ! $this->use_og_tags() )
			return '';

		/**
		 * Applies filters 'the_seo_framework_oglocale_output' : string
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 */
		$locale = (string) \apply_filters( 'the_seo_framework_oglocale_output', $this->fetch_locale(), $this->get_the_real_ID() );

		if ( $locale )
			return '<meta property="og:locale" content="' . \esc_attr( $locale ) . '" />' . "\r\n";

		return '';
	}

	/**
	 * Renders the Open Graph title meta tag.
	 *
	 * @uses $this->title_from_cache()
	 * @since 2.0.3
	 *
	 * @return string The Open Graph title meta tag.
	 */
	public function og_title() {

		if ( ! $this->use_og_tags() )
			return '';

		/**
		 * Applies filters 'the_seo_framework_ogtitle_output' : string
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 */
		$title = (string) \apply_filters( 'the_seo_framework_ogtitle_output', $this->title_from_cache( '', '', '', true ), $this->get_the_real_ID() );

		if ( $title )
			return '<meta property="og:title" content="' . \esc_attr( $title ) . '" />' . "\r\n";

		return '';
	}

	/**
	 * Renders the Open Graph type meta tag.
	 *
	 * @since 1.1.0
	 *
	 * @return string The Open Graph type meta tag.
	 */
	public function og_type() {

		if ( ! $this->use_og_tags() )
			return '';

		$type = $this->get_og_type();

		if ( $type )
			return '<meta property="og:type" content="' . \esc_attr( $type ) . '" />' . "\r\n";

		return '';
	}

	/**
	 * Renders Open Graph image meta tag.
	 *
	 * @since 1.3.0
	 * @since 2.6.0 : Added WooCommerce gallery images.
	 * @since 2.7.0 : Added image dimensions if found.
	 *
	 * @return string The Open Graph image meta tag.
	 */
	public function og_image() {

		if ( ! $this->use_og_tags() )
			return '';

		/**
		 * Applies filters 'the_seo_framework_ogimage_output' : string|bool
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 *
		 * @NOTE: Use of this might cause incorrect meta since other functions
		 * depend on the image from cache.
		 *
		 * @todo Place in listener cache.
		 * @priority medium 2.8.0+
		 */
		$image = \apply_filters( 'the_seo_framework_ogimage_output', $this->get_image_from_cache(), $id = $this->get_the_real_ID() );

		/**
		 * Now returns empty string on false.
		 * @since 2.6.0
		 */
		if ( false === $image )
			return '';

		$image = (string) $image;

		/**
		 * Always output
		 * @since 2.1.1
		 */
		$output = '<meta property="og:image" content="' . \esc_attr( $image ) . '" />' . "\r\n";

		if ( $image ) {
			if ( ! empty( $this->image_dimensions[ $id ]['width'] ) && ! empty( $this->image_dimensions[ $id ]['height'] ) ) {
				$output .= '<meta property="og:image:width" content="' . \esc_attr( $this->image_dimensions[ $id ]['width'] ) . '" />' . "\r\n";
				$output .= '<meta property="og:image:height" content="' . \esc_attr( $this->image_dimensions[ $id ]['height'] ) . '" />' . "\r\n";
			}
		}

		//* Fetch Product images.
		$woocommerce_product_images = $this->render_woocommerce_product_og_image();

		return $output . $woocommerce_product_images;
	}

	/**
	 * Renders WooCommerce Product Gallery OG images.
	 *
	 * @since 2.6.0
	 * @since 2.7.0 : Added image dimensions if found.
	 * @since 2.8.0 : Checks for featured ID internally, rather than using a far-off cache.
	 *
	 * @return string The rendered OG Image.
	 */
	public function render_woocommerce_product_og_image() {

		$output = '';

		if ( $this->is_wc_product() ) {

			$images = $this->get_image_from_woocommerce_gallery();

			if ( $images && is_array( $images ) ) {

				$post_id = $this->get_the_real_ID();
				$post_manual_og = $this->get_custom_field( '_social_image_id', $post_id );
				$featured_id = $post_manual_og ? (int) $post_manual_og : (int) \get_post_thumbnail_id( $post_id );

				foreach ( $images as $id ) {

					if ( $id === $featured_id )
						continue;

					//* Parse 4096px url.
					$img = $this->parse_og_image( $id, array(), true );

					if ( $img ) {
						$output .= '<meta property="og:image" content="' . \esc_attr( $img ) . '" />' . "\r\n";

						if ( ! empty( $this->image_dimensions[ $id ]['width'] ) && ! empty( $this->image_dimensions[ $id ]['height'] ) ) {
							$output .= '<meta property="og:image:width" content="' . \esc_attr( $this->image_dimensions[ $id ]['width'] ) . '" />' . "\r\n";
							$output .= '<meta property="og:image:height" content="' . \esc_attr( $this->image_dimensions[ $id ]['height'] ) . '" />' . "\r\n";
						}
					}
				}
			}
		}

		return $output;
	}

	/**
	 * Renders Open Graph sitename meta tag.
	 *
	 * @since 1.3.0
	 *
	 * @return string The Open Graph sitename meta tag.
	 */
	public function og_sitename() {

		if ( ! $this->use_og_tags() )
			return '';

		/**
		 * Applies filters 'the_seo_framework_ogsitename_output' : string
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 */
		$sitename = (string) \apply_filters( 'the_seo_framework_ogsitename_output', \get_bloginfo( 'name' ), $this->get_the_real_ID() );

		if ( $sitename )
			return '<meta property="og:site_name" content="' . \esc_attr( $sitename ) . '" />' . "\r\n";

		return '';
	}

	/**
	 * Renders Open Graph URL meta tag.
	 *
	 * @since 1.3.0
	 * @since 2.9.3 Added filter
	 * @uses $this->get_current_canonical_url()
	 *
	 * @return string The Open Graph URL meta tag.
	 */
	public function og_url() {

		if ( $this->use_og_tags() ) {

			/**
			 * Applies filters 'the_seo_framework_ogurl_output' : string
			 * Changes og:url output.
			 *
			 * @since 2.9.3
			 *
			 * @param string $url The canonical/Open Graph URL. Must be escaped.
			 * @param int    $id  The current page or term ID.
			 */
			$url = (string) \apply_filters( 'the_seo_framework_ogurl_output', $this->get_current_canonical_url(), $this->get_the_real_ID() );

			if ( $url )
				return '<meta property="og:url" content="' . $url . '" />' . "\r\n";
		}

		return '';
	}

	/**
	 * Renders the Twitter Card type meta tag.
	 *
	 * @since 2.2.2
	 *
	 * @return string The Twitter Card meta tag.
	 */
	public function twitter_card() {

		if ( ! $this->use_twitter_tags() )
			return '';

		/**
		 * Applies filters 'the_seo_framework_twittercard_output' : string
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 */
		$card = (string) \apply_filters( 'the_seo_framework_twittercard_output', $this->get_current_twitter_card_type(), $this->get_the_real_ID() );

		if ( $card )
			return '<meta name="twitter:card" content="' . \esc_attr( $card ) . '" />' . "\r\n";

		return '';
	}

	/**
	 * Renders the Twitter Site meta tag.
	 *
	 * @since 2.2.2
	 *
	 * @return string The Twitter Site meta tag.
	 */
	public function twitter_site() {

		if ( ! $this->use_twitter_tags() )
			return '';

		/**
		 * Applies filters 'the_seo_framework_twittersite_output' : string
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 */
		$site = (string) \apply_filters( 'the_seo_framework_twittersite_output', $this->get_option( 'twitter_site' ), $this->get_the_real_ID() );

		if ( $site )
			return '<meta name="twitter:site" content="' . \esc_attr( $site ) . '" />' . "\r\n";

		return '';
	}

	/**
	 * Renders The Twitter Creator meta tag.
	 *
	 * @since 2.2.2
	 * @since 2.9.3 No longer has a fallback to twitter:site:id
	 *              @link https://dev.twitter.com/cards/getting-started
	 * @since 3.0.0 Now uses author meta data.
	 *
	 * @return string The Twitter Creator or Twitter Site ID meta tag.
	 */
	public function twitter_creator() {

		if ( ! $this->use_twitter_tags() )
			return '';

		$twitter_page = $this->get_current_author_option( 'twitter_page' ) ?: $this->get_option( 'twitter_creator' );

		/**
		 * Applies filters 'the_seo_framework_twittercreator_output' : string
		 *
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 *
		 * @param string $twitter_page
		 * @param int    $id
		 */
		$twitter_page = (string) \apply_filters( 'the_seo_framework_twittercreator_output', $twitter_page, $this->get_the_real_ID() );

		if ( $twitter_page )
			return '<meta name="twitter:creator" content="' . \esc_attr( $twitter_page ) . '" />' . "\r\n";

		return '';
	}

	/**
	 * Renders Twitter Title meta tag.
	 *
	 * @uses $this->title_from_cache()
	 * @since 2.2.2
	 *
	 * @return string The Twitter Title meta tag.
	 */
	public function twitter_title() {

		if ( ! $this->use_twitter_tags() )
			return '';

		/**
		 * Applies filters 'the_seo_framework_twittertitle_output' : string
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 */
		$title = (string) \apply_filters( 'the_seo_framework_twittertitle_output', $this->title_from_cache( '', '', '', true ), $this->get_the_real_ID() );

		if ( $title )
			return '<meta name="twitter:title" content="' . \esc_attr( $title ) . '" />' . "\r\n";

		return '';
	}

	/**
	 * Renders Twitter Description meta tag.
	 *
	 * @uses $this->description_from_cache()
	 * @since 2.2.2
	 *
	 * @return string The Twitter Descritpion meta tag.
	 */
	public function twitter_description() {

		if ( ! $this->use_twitter_tags() )
			return '';

		/**
		 * Applies filters 'the_seo_framework_twitterdescription_output' : string
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 */
		$description = (string) \apply_filters( 'the_seo_framework_twitterdescription_output', $this->description_from_cache( true ), $this->get_the_real_ID() );

		if ( $description )
			return '<meta name="twitter:description" content="' . \esc_attr( $description ) . '" />' . "\r\n";

		return '';
	}

	/**
	 * Renders Twitter Image meta tag.
	 *
	 * @since 2.2.2
	 *
	 * @return string The Twitter Image meta tag.
	 */
	public function twitter_image() {

		if ( ! $this->use_twitter_tags() )
			return '';

		/**
		 * Applies filters 'the_seo_framework_twitterimage_output' : string|bool
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 */
		$image = (string) \apply_filters( 'the_seo_framework_twitterimage_output', $this->get_image_from_cache(), $id = $this->get_the_real_ID() );

		$output = '';

		if ( $image ) {
			$output = '<meta name="twitter:image" content="' . \esc_attr( $image ) . '" />' . "\r\n";

			if ( ! empty( $this->image_dimensions[ $id ]['width'] ) && ! empty( $this->image_dimensions[ $id ]['height'] ) ) {
				$output .= '<meta name="twitter:image:width" content="' . \esc_attr( $this->image_dimensions[ $id ]['width'] ) . '" />' . "\r\n";
				$output .= '<meta name="twitter:image:height" content="' . \esc_attr( $this->image_dimensions[ $id ]['height'] ) . '" />' . "\r\n";
			}
		}

		return $output;
	}

	/**
	 * Renders Facebook Author meta tag.
	 *
	 * @since 2.2.2
	 * @since 2.8.0 Returns empty on og:type 'website' or 'product'
	 * @since 3.0.0 Fetches Author meta data.
	 *
	 * @return string The Facebook Author meta tag.
	 */
	public function facebook_author() {

		if ( ! $this->use_facebook_tags() )
			return '';

		if ( 'article' !== $this->get_og_type() )
			return '';

		$facebook_page = $this->get_current_author_option( 'facebook_page' ) ?: $this->get_option( 'facebook_author' );

		/**
		 * Applies filters 'the_seo_framework_facebookauthor_output' : string
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 */
		$facebook_page = (string) \apply_filters( 'the_seo_framework_facebookauthor_output', $facebook_page, $this->get_the_real_ID() );

		if ( $facebook_page )
			return '<meta property="article:author" content="' . \esc_attr( \esc_url_raw( $facebook_page, array( 'http', 'https' ) ) ) . '" />' . "\r\n";

		return '';
	}

	/**
	 * Renders Facebook Publisher meta tag.
	 *
	 * @since 2.2.2
	 * @since 3.0.0 No longer outputs tag when "og:type" isn't 'article'.
	 *
	 * @return string The Facebook Publisher meta tag.
	 */
	public function facebook_publisher() {

		if ( ! $this->use_facebook_tags() )
			return '';

		if ( 'article' !== $this->get_og_type() )
			return '';

		/**
		 * Applies filters 'the_seo_framework_facebookpublisher_output' : string
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 */
		$publisher = (string) \apply_filters( 'the_seo_framework_facebookpublisher_output', $this->get_option( 'facebook_publisher' ), $this->get_the_real_ID() );

		if ( $publisher )
			return '<meta property="article:publisher" content="' . \esc_attr( \esc_url_raw( $publisher, array( 'http', 'https' ) ) ) . '" />' . "\r\n";

		return '';
	}

	/**
	 * Renders Facebook App ID meta tag.
	 *
	 * @since 2.2.2
	 *
	 * @return string The Facebook App ID meta tag.
	 */
	public function facebook_app_id() {

		if ( ! $this->use_facebook_tags() )
			return '';

		/**
		 * Applies filters 'the_seo_framework_facebookappid_output' : string
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 */
		$app_id = (string) \apply_filters( 'the_seo_framework_facebookappid_output', $this->get_option( 'facebook_appid' ), $this->get_the_real_ID() );

		if ( $app_id )
			return '<meta property="fb:app_id" content="' . \esc_attr( $app_id ) . '" />' . "\r\n";

		return '';
	}

	/**
	 * Renders Article Publishing Time meta tag.
	 *
	 * @since 2.2.2
	 * @since 2.8.0 Returns empty on product pages.
	 * @since 3.0.0: 1. Now checks for 0000 timestamps.
	 *               2. Now uses timestamp formats.
	 *               3. Now uses GMT time.
	 *
	 * @return string The Article Publishing Time meta tag.
	 */
	public function article_published_time() {

		if ( ! $this->output_published_time() )
			return '';

		$id = $this->get_the_real_ID();

		$post = \get_post( $id );
		$post_date_gmt = $post->post_date_gmt;

		if ( '0000-00-00 00:00:00' === $post_date_gmt )
			return '';

		/**
		 * Applies filters 'the_seo_framework_publishedtime_output' : string
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 */
		$time = (string) \apply_filters( 'the_seo_framework_publishedtime_output', $this->gmt2date( $this->get_timestamp_format(), $post_date_gmt ), $id );

		if ( $time )
			return '<meta property="article:published_time" content="' . \esc_attr( $time ) . '" />' . "\r\n";

		return '';
	}

	/**
	 * Renders Article Modified Time meta tag.
	 * Also renders the Open Graph Updated Time meta tag if Open Graph tags are enabled.
	 *
	 * @since 2.2.2
	 * @since 2.7.0 Listens to $this->get_the_real_ID() instead of WordPress Core ID determination.
	 * @since 2.8.0 Returns empty on product pages.
	 * @since 3.0.0: 1. Now checks for 0000 timestamps.
	 *               2. Now uses timestamp formats.
	 *
	 * @return string The Article Modified Time meta tag, and optionally the Open Graph Updated Time.
	 */
	public function article_modified_time() {

		if ( ! $this->output_modified_time() )
			return '';

		$id = $this->get_the_real_ID();

		$post = \get_post( $id );
		$post_modified_gmt = $post->post_modified_gmt;

		if ( '0000-00-00 00:00:00' === $post_modified_gmt )
			return '';

		/**
		 * Applies filters 'the_seo_framework_modifiedtime_output' : string
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 */
		$time = (string) \apply_filters( 'the_seo_framework_modifiedtime_output', $this->gmt2date( $this->get_timestamp_format(), $post_modified_gmt ), $id );

		if ( $time ) {
			$output = '<meta property="article:modified_time" content="' . \esc_attr( $time ) . '" />' . "\r\n";

			if ( $this->use_og_tags() )
				$output .= '<meta property="og:updated_time" content="' . \esc_attr( $time ) . '" />' . "\r\n";

			return $output;
		}

		return '';
	}

	/**
	 * Renders Canonical URL meta tag.
	 *
	 * @since 2.0.6
	 * @since 3.0.0 Deleted filter `the_seo_framework_output_canonical`.
	 * @uses $this->get_current_canonical_url()
	 *
	 * @return string The Canonical URL meta tag.
	 */
	public function canonical() {

		/**
		 * Applies filters 'the_seo_framework_rel_canonical_output' : string
		 * Changes canonical URL output.
		 *
		 * @since 2.6.5
		 *
		 * @param string $url The canonical URL. Must be escaped.
		 * @param int    $id  The current page or term ID.
		 */
		$url = (string) \apply_filters( 'the_seo_framework_rel_canonical_output', $this->get_current_canonical_url(), $this->get_the_real_ID() );

		/**
		 * @since 2.7.0 Listens to the second filter.
		 */
		if ( $url )
			return '<link rel="canonical" href="' . $url . '" />' . PHP_EOL;

		return '';
	}

	/**
	 * Renders LD+JSON Schema.org scripts.
	 *
	 * @uses $this->render_ld_json_scripts()
	 *
	 * @since 1.2.0
	 * @return string The LD+json Schema.org scripts.
	 */
	public function ld_json() {

		//* Don't output on Search, 404 or preview.
		if ( $this->is_search() || $this->is_404() || $this->is_preview() )
			return '';

		/**
		 * Applies filters 'the_seo_framework_ldjson_scripts' : string
		 *
		 * @since 2.6.0
		 *
		 * @param string $json The JSON output. Must be escaped.
		 * @param int    $id   The current page or term ID.
		 */
		$json = (string) \apply_filters( 'the_seo_framework_ldjson_scripts', $this->render_ld_json_scripts(), $this->get_the_real_ID() );

		return $json;
	}

	/**
	 * Renders Google Site Verification Code meta tag.
	 *
	 * @since 2.2.4
	 *
	 * @return string The Google Site Verification code meta tag.
	 */
	public function google_site_output() {

		/**
		 * Applies filters 'the_seo_framework_googlesite_output' : string
		 * @since 2.6.0
		 */
		$code = (string) \apply_filters( 'the_seo_framework_googlesite_output', $this->get_option( 'google_verification' ), $this->get_the_real_ID() );

		if ( $code )
			return '<meta name="google-site-verification" content="' . \esc_attr( $code ) . '" />' . PHP_EOL;

		return '';
	}

	/**
	 * Renders Bing Site Verification Code meta tag.
	 *
	 * @since 2.2.4
	 *
	 * @return string The Bing Site Verification Code meta tag.
	 */
	public function bing_site_output() {

		/**
		 * Applies filters 'the_seo_framework_bingsite_output' : string
		 * @since 2.6.0
		 */
		$code = (string) \apply_filters( 'the_seo_framework_bingsite_output', $this->get_option( 'bing_verification' ), $this->get_the_real_ID() );

		if ( $code )
			return '<meta name="msvalidate.01" content="' . \esc_attr( $code ) . '" />' . PHP_EOL;

		return '';
	}

	/**
	 * Renders Yandex Site Verification code meta tag.
	 *
	 * @since 2.6.0
	 *
	 * @return string The Yandex Site Verification code meta tag.
	 */
	public function yandex_site_output() {

		/**
		 * Applies filters 'the_seo_framework_yandexsite_output' : string
		 * @since 2.6.0
		 */
		$code = (string) \apply_filters( 'the_seo_framework_yandexsite_output', $this->get_option( 'yandex_verification' ), $this->get_the_real_ID() );

		if ( $code )
			return '<meta name="yandex-verification" content="' . \esc_attr( $code ) . '" />' . PHP_EOL;

		return '';
	}

	/**
	 * Renders Pinterest Site Verification code meta tag.
	 *
	 * @since 2.5.2
	 *
	 * @return string The Pinterest Site Verification code meta tag.
	 */
	public function pint_site_output() {

		/**
		 * Applies filters 'the_seo_framework_pintsite_output' : string
		 * @since 2.6.0
		 */
		$code = (string) \apply_filters( 'the_seo_framework_pintsite_output', $this->get_option( 'pint_verification' ), $this->get_the_real_ID() );

		if ( $code )
			return '<meta name="p:domain_verify" content="' . \esc_attr( $code ) . '" />' . PHP_EOL;

		return '';
	}

	/**
	 * Renders Robots meta tags.
	 * Returns early if blog isn't public. WordPress Core will then output the meta tags.
	 *
	 * @since 2.0.0
	 *
	 * @return string The Robots meta tags.
	 */
	public function robots() {

		//* Don't do anything if the blog isn't set to public.
		if ( false === $this->is_blog_public() )
			return '';

		/**
		 * Applies filters 'the_seo_framework_robots_meta' : array
		 * @since 2.6.0
		 */
		$meta = (array) \apply_filters( 'the_seo_framework_robots_meta', $this->robots_meta(), $this->get_the_real_ID() );

		if ( empty( $meta ) )
			return '';

		return sprintf( '<meta name="robots" content="%s" />' . PHP_EOL, implode( ',', $meta ) );
	}

	/**
	 * Renders Shortlink meta tag
	 *
	 * @since 2.2.2
	 * @since 2.9.3 : Now work when home page is a blog.
	 * @uses $this->get_shortlink()
	 *
	 * @return string The Shortlink meta tag.
	 */
	public function shortlink() {

		$id = $this->get_the_real_ID();

		/**
		 * Applies filters 'the_seo_framework_shortlink_output' : string
		 * @since 2.6.0
		 */
		$url = (string) \apply_filters( 'the_seo_framework_shortlink_output', $this->get_shortlink( $id ), $id );

		if ( $url )
			return '<link rel="shortlink" href="' . $url . '" />' . PHP_EOL;

		return '';
	}

	/**
	 * Renders Prev/Next Paged URL meta tags.
	 *
	 * @since 2.2.2
	 * @uses $this->get_paged_url()
	 *
	 * @return string The Prev/Next Paged URL meta tags.
	 */
	public function paged_urls() {

		$id = $this->get_the_real_ID();

		/**
		 * Applies filters 'the_seo_framework_paged_url_output' : array
		 * @since 2.6.0
		 */
		$next = (string) \apply_filters( 'the_seo_framework_paged_url_output_next', $this->get_paged_url( 'next' ), $id );

		/**
		 * Applies filters 'the_seo_framework_paged_url_output' : array
		 * @since 2.6.0
		 */
		$prev = (string) \apply_filters( 'the_seo_framework_paged_url_output_prev', $this->get_paged_url( 'prev' ), $id );

		$output = '';

		if ( $prev )
			$output .= '<link rel="prev" href="' . $prev . '" />' . PHP_EOL;

		if ( $next )
			$output .= '<link rel="next" href="' . $next . '" />' . PHP_EOL;

		return $output;
	}

	/**
	 * Returns the plugin hidden HTML indicators.
	 *
	 * @since 2.9.2
	 *
	 * @param string $where Determines the position of the indicator.
	 *               Accepts 'before' for before, anything else for after.
	 * @param int $timing Determines when the output started.
	 * @return string The SEO Framework's HTML plugin indicator.
	 */
	public function get_plugin_indicator( $where = 'before', $timing = 0 ) {

		static $run, $_cache;

		if ( ! isset( $run ) ) {
			/**
			 * Applies filters 'the_seo_framework_indicator'
			 *
			 * @since 2.0.0
			 *
			 * @param bool $run Whether to run and show the indicator.
			 */
			$run = (bool) \apply_filters( 'the_seo_framework_indicator', true );
		}

		if ( false === $run )
			return '';

		if ( ! isset( $_cache ) ) {
			$_cache = array();
			/**
			 * Applies filters 'sybre_waaijer_<3'
			 *
			 * @since 2.4.0
			 *
			 * @param bool $sybre Whether to show the hidden author name in HTML.
			 */
			$sybre = (bool) \apply_filters( 'sybre_waaijer_<3', true );

			// Plugin name can't be translated. Yay.
			$tsf = 'The SEO Framework';

			/**
			 * Applies filters 'the_seo_framework_indicator_timing'
			 *
			 * @since 2.4.0
			 *
			 * @param bool $show_timer Whether to show the hidden generation time in HTML.
			 */
			$_cache['show_timer'] = (bool) \apply_filters( 'the_seo_framework_indicator_timing', true );

			/* translators: %s = 'The SEO Framework' */
			$_cache['start'] = sprintf( \esc_html__( 'Start %s', 'autodescription' ), $tsf );
			/* translators: %s = 'The SEO Framework' */
			$_cache['end'] = sprintf( \esc_html__( 'End %s', 'autodescription' ), $tsf );
			$_cache['author'] = $sybre ? ' ' . \esc_html__( 'by Sybre Waaijer', 'autodescription' ) : '';
		}

		if ( 'before' === $where ) {
			$output = $_cache['start'] . $_cache['author'];
		} else {
			if ( $_cache['show_timer'] && $timing ) {
				$timer = ' | ' . number_format( microtime( true ) - $timing, 5 ) . 's';
			} else {
				$timer = '';
			}
			$output = $_cache['end'] . $_cache['author'] . $timer;
		}

		return sprintf( '<!-- %s -->', $output ) . PHP_EOL;
	}

	/**
	 * Determines if modified time should be used in the current query.
	 *
	 * @since 3.0.0
	 * @staticvar bool $cache
	 *
	 * @return bool
	 */
	public function output_modified_time() {

		static $cache;

		if ( isset( $cache ) )
			return $cache;

		if ( 'article' !== $this->get_og_type() )
			return $cache = false;

		return $cache = (bool) $this->get_option( 'post_modify_time' );
	}

	/**
	 * Determines if published time should be used in the current query.
	 *
	 * @since 3.0.0
	 * @staticvar bool $cache
	 *
	 * @return bool
	 */
	public function output_published_time() {

		static $cache;

		if ( isset( $cache ) )
			return $cache;

		if ( 'article' !== $this->get_og_type() )
			return $cache = false;

		return $cache = (bool) $this->get_option( 'post_publish_time' );
	}

	/**
	 * Determines whether we can use Open Graph tags.
	 *
	 * @since 2.6.0
	 * @staticvar bool $cache
	 *
	 * @return bool
	 */
	public function use_og_tags() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		return $cache = $this->is_option_checked( 'og_tags' ) && false === $this->detect_og_plugin();
	}

	/**
	 * Determines whether we can use Facebook tags.
	 *
	 * @since 2.6.0
	 * @staticvar bool $cache
	 *
	 * @return bool
	 */
	public function use_facebook_tags() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		return $cache = $this->is_option_checked( 'facebook_tags' );
	}

	/**
	 * Determines whether we can use Twitter tags.
	 *
	 * @since 2.6.0
	 * @since 2.8.2 : Now also considers Twitter card type output.
	 * @staticvar bool $cache
	 *
	 * @return bool
	 */
	public function use_twitter_tags() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		return $cache = $this->is_option_checked( 'twitter_tags' ) && false === $this->detect_twitter_card_plugin() && $this->get_current_twitter_card_type();
	}

	/**
	 * Determines whether we can use Google+ tags.
	 *
	 * @since 2.6.0
	 * @staticvar bool $cache
	 * @NOTE: not used.
	 *
	 * @return bool
	 */
	public function use_googleplus_tags() {

		return false;

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		return $cache = $this->is_option_checked( 'googleplus_tags' );
	}
}
