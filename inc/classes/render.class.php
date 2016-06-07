<?php
/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class AutoDescription_Render
 *
 * Puts all data into HTML valid strings
 * Returns strings
 *
 * @since 2.1.6
 */
class AutoDescription_Render extends AutoDescription_Admin_Init {

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
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Cache description in static variable
	 * Must be called inside the loop
	 *
	 * @staticvar array $description_cache
	 *
	 * @since 2.2.2
	 * @return string The description
	 */
	public function description_from_cache( $social = false ) {

		static $description_cache = array();

		if ( isset( $description_cache[$social] ) )
			return $description_cache[$social];

		return $description_cache[$social] = $this->generate_description( '', array( 'social' => $social ) );
	}

	/**
	 * Cache current URL in static variable
	 * Must be called inside the loop
	 *
	 * @param string $url the url
	 * @param int $post_id the page id, if empty it will fetch the requested ID, else the page uri
	 * @param bool $paged Return current page URL with pagination
	 * @param bool $from_option Get the canonical uri option
	 * @param bool $paged_plural Whether to allow pagination on second or later pages.
	 *
	 * @staticvar array $url_cache
	 *
	 * @since 2.2.2
	 * @return string The url
	 */
	public function the_url_from_cache( $url = '', $post_id = null, $paged = false, $from_option = true, $paged_plural = true ) {

		static $url_cache = array();

		if ( is_null( $post_id ) )
			$post_id = $this->get_the_real_ID();

		if ( isset( $url_cache[$url][$post_id][$paged][$from_option][$paged_plural] ) )
			return $url_cache[$url][$post_id][$paged][$from_option][$paged_plural];

		return $url_cache[$url][$post_id][$paged][$from_option][$paged_plural] = $this->the_url( $url, array( 'paged' => $paged, 'get_custom_field' => $from_option, 'id' => $post_id, 'paged_plural' => $paged_plural ) );
	}

	/**
	 * Cache home URL in static variable
	 *
	 * @param bool $force_slash Force slash
	 *
	 * @staticvar array $url_cache
	 *
	 * @since 2.5.0
	 * @return string The url
	 */
	public function the_home_url_from_cache( $force_slash = false ) {

		static $url_cache = array();

		if ( isset( $url_cache[$force_slash] ) )
			return $url_cache[$force_slash];

		return $url_cache[$force_slash] = $this->the_url( '', array( 'home' => true, 'forceslash' => $force_slash ) );
	}

	/**
	 * Cache current Title in static variable
	 * Must be called inside the loop
	 *
	 * @param string $title The Title to return
	 * @param string $sep The Title sepeartor
	 * @param string $seplocation The Title sepeartor location ( accepts 'left' or 'right' )
	 * @param bool $meta Ignore theme doing it wrong.
	 *
	 * @staticvar array $title_cache
	 *
	 * @since 2.2.2
	 * @return string The title
	 */
	public function title_from_cache( $title = '', $sep = '', $seplocation = '', $meta = false ) {

		/**
		 * Cache the inputs, for when the title is doing it right.
		 * Use those values to fetch the cached title.
		 *
		 * @since 2.4.0
		 */
		static $setup_cache = null;
		static $title_param_cache = null;
		static $sep_param_cache = null;
		static $seplocation_param_cache = null;

		if ( ! isset( $setup_cache ) ) {
			if ( doing_filter( 'pre_get_document_title' ) || doing_filter( 'wp_title' ) ) {
				$title_param_cache = $title;
				$sep_param_cache = $sep;
				$seplocation_param_cache = $seplocation;

				$setup_cache = 'I like turtles.';
			}
		}

		/**
		 * If the theme is doing it right, override parameters to speed things up.
		 *
		 * @since 2.4.0
		 */
		if ( isset( $this->title_doing_it_wrong ) && false === $this->title_doing_it_wrong ) {
			$title = $title_param_cache;
			$sep = $sep_param_cache;
			$seplocation = $seplocation_param_cache;
			$meta = false;
		}

		static $title_cache = array();

		if ( isset( $title_cache[$title][$sep][$seplocation][$meta] ) )
			return $title_cache[$title][$sep][$seplocation][$meta];

		return $title_cache[$title][$sep][$seplocation][$meta] = $this->title( $title, $sep, $seplocation, array( 'meta' => $meta ) );
	}

	/**
	 * Cache current Image URL in static variable
	 * Must be called inside the loop
	 *
	 * @staticvar string $image_cache
	 *
	 * @since 2.2.2
	 * @return string The image url
	 */
	public function get_image_from_cache() {

		static $image_cache = null;

		if ( isset( $image_cache ) )
			return $image_cache;

		$post_id = $this->get_the_real_ID();

		//* End this madness if there's no ID found (search/404/etc.)
		if ( empty( $post_id ) )
			return '';

		$image_cache = $this->get_image( $post_id );

		return $image_cache;
	}

	/**
	 * Render the description
	 *
	 * @uses $this->description_from_cache()
	 * @uses $this->detect_seo_plugins()
	 *
	 * @since 1.3.0
	 */
	public function the_description() {

		if ( $this->detect_seo_plugins() )
			return;

		/**
		 * Applies filters 'the_seo_framework_description_output' : string
		 * @since 2.3.0
		 */
		$description = (string) apply_filters( 'the_seo_framework_description_output', '', $this->get_the_real_ID() );

		if ( empty( $description ) )
			$description = $this->description_from_cache();

		if ( $description )
			return '<meta name="description" content="' . esc_attr( $description ) . '" />' . "\r\n";

		return '';
	}

	/**
	 * Render og:description
	 *
	 * @uses $this->description_from_cache()
	 *
	 * @since 1.3.0
	 */
	public function og_description() {

		if ( $this->use_og_tags() ) {

			/**
			 * Applies filters 'the_seo_framework_ogdescription_output' : string
			 * @since 2.3.0
			 */
			$description = (string) apply_filters( 'the_seo_framework_ogdescription_output', '', $this->get_the_real_ID() );

			if ( empty( $description ) )
				$description = $this->description_from_cache( true );

			return '<meta property="og:description" content="' . esc_attr( $description ) . '" />' . "\r\n";
		}

		return '';
	}

	/**
	 * Render the OG locale.
	 *
	 * @since 1.0.0
	 */
	public function og_locale() {

		if ( $this->use_og_tags() ) {

			/**
			 * Applies filters 'the_seo_framework_oglocale_output' : string
			 * @since 2.3.0
			 */
			$locale = (string) apply_filters( 'the_seo_framework_oglocale_output', '', $this->get_the_real_ID() );

			if ( empty( $locale ) )
				$locale = $this->fetch_locale();

			return '<meta property="og:locale" content="' . esc_attr( $locale ) . '" />' . "\r\n";
		}

		return '';
	}

	/**
	 * Process the title to WordPress
	 *
	 * @uses $this->title_from_cache()
	 *
	 * @since 2.0.3
	 */
	public function og_title() {

		if ( $this->use_og_tags() ) {

			/**
			 * Applies filters 'the_seo_framework_ogtitle_output' : string
			 * @since 2.3.0
			 */
			$title = (string) apply_filters( 'the_seo_framework_ogtitle_output', '', $this->get_the_real_ID() );

			if ( empty( $title ) )
				$title = $this->title_from_cache( '', '', '', true );

			return '<meta property="og:title" content="' . esc_attr( $title ) . '" />' . "\r\n";
		}

		return '';
	}

	/**
	 * Get the OG type.
	 *
	 * @since 1.1.0
	 */
	public function og_type() {

		if ( $this->use_og_tags() ) {

			/**
			 * Applies filters 'the_seo_framework_ogtype_output' : string
			 * @since 2.3.0
			 */
			$type = (string) apply_filters( 'the_seo_framework_ogtype_output', '', $this->get_the_real_ID() );

			if ( empty( $type ) ) {
				if ( $this->is_wc_product() ) {
					$type = 'product';
				} else if ( $this->is_single() && $this->get_image_from_cache() ) {
					$type = 'article';
				} else if ( $this->is_author() ) {
					$type = 'profile';
				} else if ( $this->is_blog_page() || ( $this->is_front_page() && ! $this->has_page_on_front() ) ) {
					$type = 'blog';
				} else {
					$type = 'website';
				}
			}

			return '<meta property="og:type" content="' . esc_attr( $type ) . '" />' . "\r\n";
		}

		return '';
	}

	/**
	 * Adds og:image
	 *
	 * @param string $image url for image
	 *
	 * @since 1.3.0
	 */
	public function og_image() {

		if ( $this->use_og_tags() ) {

			$id = $this->get_the_real_ID();

			/**
			 * Applies filters 'the_seo_framework_ogimage_output' : string|bool
			 * @since 2.3.0
			 *
			 * @NOTE: Use of this might cause incorrect meta since other functions
			 * depend on the image from cache.
			 *
			 * @todo Place in listener cache.
			 * @priority medium 2.8.0+
			 */
			$image = apply_filters( 'the_seo_framework_ogimage_output', '', $id );

			/**
			 * Now returns empty string on false.
			 * @since 2.6.0
			 */
			if ( false === $image )
				return '';

			if ( empty( $image ) ) {
				$image = $this->get_image_from_cache();
			} else {
				$image = (string) $image;
			}

			/**
			 * Always output
			 * @since 2.1.1
			 */
			$output = '<meta property="og:image" content="' . esc_attr( $image ) . '" />' . "\r\n";

			//* Fetch Product images.
			$woocommerce_product_images = $this->render_woocommerce_product_og_image();

			return $output . $woocommerce_product_images;
		}

		return '';
	}

	/**
	 * Render more OG images to choose from.
	 *
	 * @since 2.6.0
	 *
	 * @return string The rendered OG Image.
	 */
	public function render_woocommerce_product_og_image() {

		$output = '';

		if ( $this->is_wc_product() ) {

			$images = $this->get_image_from_woocommerce_gallery();

			if ( $images && is_array( $images ) ) {
				foreach ( $images as $id ) {
					//* Parse 1500px url.
					$img = $this->parse_og_image( $id );

					if ( $img )
						$output .= '<meta property="og:image" content="' . esc_attr( $img ) . '" />' . "\r\n";
				}
			}
		}

		return $output;
	}

	/**
	 * Adds og:site_name
	 *
	 * @param string output	the output
	 *
	 * @since 1.3.0
	 */
	public function og_sitename() {

		if ( $this->use_og_tags() ) {

			/**
			 * Applies filters 'the_seo_framework_ogsitename_output' : string
			 * @since 2.3.0
			 */
			$sitename = (string) apply_filters( 'the_seo_framework_ogsitename_output', '', $this->get_the_real_ID() );

			if ( empty( $sitename ) )
				$sitename = get_bloginfo( 'name' );

			return '<meta property="og:site_name" content="' . esc_attr( $sitename ) . '" />' . "\r\n";
		}

		return '';
	}

	/**
	 * Adds og:url
	 *
	 * @return string og:url the url meta
	 *
	 * @since 1.3.0
	 *
	 * @uses $this->the_url_from_cache()
	 */
	public function og_url() {

		if ( $this->use_og_tags() )
			return '<meta property="og:url" content="' . $this->the_url_from_cache() . '" />' . "\r\n";

		return '';
	}

	/**
	 * Render twitter:card
	 *
	 * @since 2.2.2
	 */
	public function twitter_card() {

		if ( $this->use_twitter_tags() ) {

			/**
			 * Applies filters 'the_seo_framework_twittercard_output' : string
			 * @since 2.3.0
			 */
			$card = (string) apply_filters( 'the_seo_framework_twittercard_output', '', $this->get_the_real_ID() );

			if ( empty( $card ) ) {
				/**
				 * Return card type if image is found.
				 * Return to summary if not.
				 */
				$card = $this->get_image_from_cache() ? $this->get_option( 'twitter_card' ) : 'summary';
			}

			return '<meta name="twitter:card" content="' . esc_attr( $card ) . '" />' . "\r\n";
		}

		return '';
	}

	/**
	 * Render twitter:site
	 *
	 * @since 2.2.2
	 */
	public function twitter_site() {

		if ( $this->use_twitter_tags() ) {

			/**
			 * Applies filters 'the_seo_framework_twittersite_output' : string
			 * @since 2.3.0
			 */
			$site = (string) apply_filters( 'the_seo_framework_twittersite_output', '', $this->get_the_real_ID() );

			if ( empty( $site ) )
				$site = $this->get_option( 'twitter_site' );

			if ( $site )
				return '<meta name="twitter:site" content="' . esc_attr( $site ) . '" />' . "\r\n";
		}

		return '';
	}

	/**
	 * Render twitter:creator or twitter:site:id
	 *
	 * @since 2.2.2
	 */
	public function twitter_creator() {

		if ( $this->use_twitter_tags() ) {

			/**
			 * Applies filters 'the_seo_framework_twittercreator_output' : string
			 * @since 2.3.0
			 */
			$creator = (string) apply_filters( 'the_seo_framework_twittercreator_output', '', $this->get_the_real_ID() );

			if ( empty( $creator ) ) {
				$site = $this->get_option( 'twitter_site' );
				$creator = $this->get_option( 'twitter_creator' );

				/**
				 * Return site:id instead of creator is no twitter:site is found.
				 * Per Twitter requirements
				 */
				if ( empty( $site ) && $creator )
					return '<meta name="twitter:site:id" content="' . esc_attr( $creator ) . '" />' . "\r\n";
			}

			if ( $creator )
				return '<meta name="twitter:creator" content="' . esc_attr( $creator ) . '" />' . "\r\n";
		}

		return '';
	}

	/**
	 * Render twitter:title
	 *
	 * @uses $this->title_from_cache()
	 *
	 * @since 2.2.2
	 */
	public function twitter_title() {

		if ( $this->use_twitter_tags() ) {

			/**
			 * Applies filters 'the_seo_framework_twittertitle_output' : string
			 * @since 2.3.0
			 */
			$title = (string) apply_filters( 'the_seo_framework_twittertitle_output', '', $this->get_the_real_ID() );

			if ( empty( $title ) )
				$title = $this->title_from_cache( '', '', '', true );

			return '<meta name="twitter:title" content="' . esc_attr( $title ) . '" />' . "\r\n";
		}

		return '';
	}

	/**
	 * Render twitter:description
	 *
	 * @uses $this->description_from_cache()
	 *
	 * @since 2.2.2
	 */
	public function twitter_description() {

		if ( $this->use_twitter_tags() ) {

			/**
			 * Applies filters 'the_seo_framework_twitterdescription_output' : string
			 * @since 2.3.0
			 */
			$description = (string) apply_filters( 'the_seo_framework_twitterdescription_output', '', $this->get_the_real_ID() );

			if ( empty( $description ) )
				$description = $this->description_from_cache( true );

			return '<meta name="twitter:description" content="' . esc_attr( $description ) . '" />' . "\r\n";
		}

		return '';
	}

	/**
	 * Render twitter:image:src
	 *
	 * @param string $image url for image
	 *
	 * @since 2.2.2
	 *
	 * @return string|null The twitter image source meta tag
	 */
	public function twitter_image() {

		if ( $this->use_twitter_tags() ) {

			/**
			 * Applies filters 'the_seo_framework_twitterimage_output' : string|bool
			 * @since 2.3.0
			 */
			$image = apply_filters( 'the_seo_framework_twitterimage_output', '', $this->get_the_real_ID() );

			/**
			 * Now returns empty string on false.
			 * @since 2.6.0
			 */
			if ( false === $image )
				return '';

			if ( empty( $image ) ) {
				$image = $this->get_image_from_cache();
			} else {
				$image = (string) $image;
			}

			if ( $image )
				return '<meta name="twitter:image:src" content="' . esc_attr( $image ) . '" />' . "\r\n";
		}

		return '';
	}

	/**
	 * Render article:author
	 *
	 * @since 2.2.2
	 *
	 * @return string|null The facebook app id
	 */
	public function facebook_author() {

		if ( $this->use_facebook_tags() ) {

			/**
			 * Applies filters 'the_seo_framework_facebookauthor_output' : string
			 * @since 2.3.0
			 */
			$author = (string) apply_filters( 'the_seo_framework_facebookauthor_output', '', $this->get_the_real_ID() );

			if ( empty( $author ) )
				$author = $this->get_option( 'facebook_author' );

			if ( $author )
				return '<meta property="article:author" content="' . esc_attr( esc_url_raw( $author ) ) . '" />' . "\r\n";
		}

		return '';
	}

	/**
	 * Render article:author
	 *
	 * @since 2.2.2
	 *
	 * @return string|null The facebook app id
	 */
	public function facebook_publisher() {

		if ( $this->use_facebook_tags() ) {

			/**
			 * Applies filters 'the_seo_framework_facebookpublisher_output' : string
			 * @since 2.3.0
			 */
			$publisher = (string) apply_filters( 'the_seo_framework_facebookpublisher_output', '', $this->get_the_real_ID() );

			if ( empty( $publisher ) )
				$publisher = $this->get_option( 'facebook_publisher' );

			if ( $publisher )
				return '<meta property="article:publisher" content="' . esc_attr( esc_url_raw( $publisher ) ) . '" />' . "\r\n";
		}

		return '';
	}

	/**
	 * Render fb:app_id
	 *
	 * @since 2.2.2
	 *
	 * @return string|null The facebook app id
	 */
	public function facebook_app_id() {

		if ( $this->use_facebook_tags() ) {

			/**
			 * Applies filters 'the_seo_framework_facebookappid_output' : string
			 * @since 2.3.0
			 */
			$app_id = (string) apply_filters( 'the_seo_framework_facebookappid_output', '', $this->get_the_real_ID() );

			if ( empty( $app_id ) )
				$app_id = $this->get_option( 'facebook_appid' );

			if ( $app_id )
				return '<meta property="fb:app_id" content="' . esc_attr( $app_id ) . '" />' . "\r\n";
		}

		return '';
	}

	/**
	 * Render article:published_time
	 *
	 * @since 2.2.2
	 *
	 * @return string|null The article:published_time
	 */
	public function article_published_time() {

		// Don't do anything if it's not a page or post.
		if ( false === $this->is_singular() )
			return;

		$front_page = (bool) is_front_page();

		// If it's a post, but the option is disabled, don't do anyhting.
		if ( ! $front_page && $this->is_single() && ! $this->get_option( 'post_publish_time' ) )
			return;

		// If it's a page, but the option is disabled, don't do anything.
		if ( ! $front_page && $this->is_page() && ! $this->get_option( 'page_publish_time' ) )
			return;

		// If it's  the home page, but the option is disabled, don't do anything.
		if ( $front_page && ! $this->get_option( 'home_publish_time' ) )
			return;

		//* @since 2.3.0
		$time = (string) apply_filters( 'the_seo_framework_publishedtime_output', '', $this->get_the_real_ID() );

		if ( empty( $time ) )
			$time = get_the_date( 'Y-m-d', '' );

		if ( $time )
			return '<meta property="article:published_time" content="' . esc_attr( $time ) . '" />' . "\r\n";

		return '';
	}

	/**
	 * Render article:modified_time
	 *
	 * @since 2.2.2
	 *
	 * @return string|null The article:modified_time
	 */
	public function article_modified_time() {

		// Don't do anything if it's not a page or post, or if both options are disabled
		if ( false === $this->is_singular() )
			return '';

		if ( $this->is_front_page() ) {
			// If it's the frontpage, but the option is disabled, don't do anything.
			if ( ! $this->get_option( 'home_modify_time' ) )
				return '';
		} else {
			// If it's a post, but the option is disabled, don't do anyhting.
			if ( $this->is_single() && ! $this->get_option( 'post_modify_time' ) )
				return '';

			// If it's a page, but the option is disabled, don't do anything.
			if ( $this->is_page() && ! $this->get_option( 'page_modify_time' ) )
				return '';
		}

		//* @since 2.3.0
		$time = (string) apply_filters( 'the_seo_framework_modifiedtime_output', '', $this->get_the_real_ID() );

		if ( empty( $time ) )
			$time = the_modified_date( 'Y-m-d', '', '', false );

		if ( $time ) {
			$output = '<meta property="article:modified_time" content="' . esc_attr( $time ) . '" />' . "\r\n";

			if ( $this->use_og_tags() )
				$output .= '<meta property="og:updated_time" content="' . esc_attr( $time ) . '" />'. "\r\n";

			return $output;
		}

		return '';
	}

	/**
	 * Outputs canonical url
	 *
	 * @since 2.0.6
	 *
	 * @uses $this->the_url_from_cache()
	 *
	 * @return string canonical url meta
	 */
	public function canonical() {

		/**
		 * Applies filters the_seo_framework_output_canonical : Don't output canonical if false.
		 * @since 2.4.2
		 */
		if ( ! apply_filters( 'the_seo_framework_output_canonical', true, $this->get_the_real_ID() ) )
			return;

		return '<link rel="canonical" href="' . $this->the_url_from_cache() . '" />' . "\r\n";
	}

	/**
	 * LD+JSON helper output
	 *
	 * @uses $this->render_ld_json_scripts()
	 *
	 * @since 1.2.0
	 * @return string $json LD+json helpers in header on front page.
	 */
	public function ld_json() {

		//* Check for LD+JSON compat
		if ( $this->is_search() || $this->is_404() )
			return;

		/**
		 * Applies filters 'the_seo_framework_ldjson_scripts' : string
		 * @since 2.6.0
		 */
		$json = (string) apply_filters( 'the_seo_framework_ldjson_scripts', $this->render_ld_json_scripts(), $this->get_the_real_ID() );

		return $json;
	}

	/**
	 * Outputs Google Site Verification code
	 *
	 * @since 2.2.4
	 *
	 * @return string|null google verification code
	 */
	public function google_site_output() {

		/**
		 * Applies filters 'the_seo_framework_googlesite_output' : string
		 * @since 2.6.0
		 */
		$code = (string) apply_filters( 'the_seo_framework_googlesite_output', $this->get_option( 'google_verification' ), $this->get_the_real_ID() );

		if ( empty( $code ) )
			return '';

		return '<meta name="google-site-verification" content="' . esc_attr( $code ) . '" />' . "\r\n";
	}

	/**
	 * Outputs Bing Site Verification code
	 *
	 * @since 2.2.4
	 *
	 * @return string|null Bing Webmaster code
	 */
	public function bing_site_output() {

		/**
		 * Applies filters 'the_seo_framework_bingsite_output' : string
		 * @since 2.6.0
		 */
		$code = (string) apply_filters( 'the_seo_framework_bingsite_output', $this->get_option( 'bing_verification' ), $this->get_the_real_ID() );

		if ( empty( $code ) )
			return '';

		return '<meta name="msvalidate.01" content="' . esc_attr( $code ) . '" />' . "\r\n";
	}

	/**
	 * Outputs Yandex Site Verification code
	 *
	 * @since 2.6.0
	 *
	 * @return string|null Yandex Webmaster code
	 */
	public function yandex_site_output() {

		/**
		 * Applies filters 'the_seo_framework_yandexsite_output' : string
		 * @since 2.6.0
		 */
		$code = (string) apply_filters( 'the_seo_framework_yandexsite_output', $this->get_option( 'yandex_verification' ), $this->get_the_real_ID() );

		if ( empty( $code ) )
			return '';

		return '<meta name="yandex-verification" content="' . esc_attr( $code ) . '" />' . "\r\n";
	}

	/**
	 * Outputs Bing Site Verification code
	 *
	 * @since 2.5.2
	 *
	 * @return string|null Bing Webmaster code
	 */
	public function pint_site_output() {

		/**
		 * Applies filters 'the_seo_framework_pintsite_output' : string
		 * @since 2.6.0
		 */
		$code = (string) apply_filters( 'the_seo_framework_pintsite_output', $this->get_option( 'pint_verification' ), $this->get_the_real_ID() );

		if ( empty( $code ) )
			return '';

		return '<meta name="p:domain_verify" content="' . esc_attr( $code ) . '" />' . "\r\n";
	}

	/**
	 * Output robots meta tags
	 *
	 * @since 2.0.0
	 *
	 * @return null Return early if blog is not public.
	 */
	public function robots() {

		//* Don't do anything if the blog isn't set to public.
		if ( false === $this->is_blog_public() )
			return '';

		/**
		 * Applies filters 'the_seo_framework_robots_meta' : array
		 * @since 2.6.0
		 */
		$meta = (array) apply_filters( 'the_seo_framework_robots_meta', $this->robots_meta(), $this->get_the_real_ID() );

		//* Add meta if any exist
		if ( $meta )
			return sprintf( '<meta name="robots" content="%s" />' . "\r\n", implode( ',', $meta ) );

		 return '';
	}

	/**
	 * Outputs favicon urls
	 *
	 * @since 2.2.1
	 *
	 * @uses $this->site_icon()
	 *
	 * @return string icon links.
	 * @TODO Make this work for older wp versions. i.e. add upload area for wp 4.2.99999 and lower
	 * @TODO Make this work in the first place
	 * @ignore
	 * @access private
	 */
	public function favicon() {

		if ( $this->wp_version( '4.2.999', '<=' ) ) {
			$output = '<link rel="icon" type="image/x-icon" href="' . esc_url( $this->site_icon( 16 ) ) . '" sizes="16x16" />' . "\r\n";
			$output .= '<link rel="icon" type="image/x-icon" href="' . esc_url( $this->site_icon( 192 ) ) . '" sizes="192x192" />' . "\r\n";
			$output .= '<link rel="apple-touch-icon-precomposed" href="' . esc_url( $this->site_icon( 180 ) ) . '" />' . "\r\n";
			$output .= '<link rel="msapplication-TileImage" href="' . esc_url( $this->site_icon( 270 ) ) . '" />' . "\r\n";

			return $output;
		}

		return '';
	}

	/**
	 * Outputs shortlink meta tag
	 *
	 * @since 2.2.2
	 *
	 * @uses $this->get_shortlink()
	 *
	 * @return string|null shortlink url meta
	 */
	public function shortlink() {

		/**
		 * Applies filters 'the_seo_framework_shortlink_output' : array
		 * @since 2.6.0
		 */
		$url = (string) apply_filters( 'the_seo_framework_shortlink_output', $this->get_shortlink(), $this->get_the_real_ID() );

		if ( $url )
			return sprintf( '<link rel="shortlink" href="%s" />' . "\r\n", $url );

		return '';
	}

	/**
	 * Outputs paged urls meta tag
	 *
	 * @since 2.2.2
	 *
	 * @uses $this->get_paged_url()
	 *
	 * @return string
	 */
	public function paged_urls() {

		$id = $this->get_the_real_ID();

		/**
		 * Applies filters 'the_seo_framework_paged_url_output' : array
		 * @since 2.6.0
		 */
		$next = (string) apply_filters( 'the_seo_framework_paged_url_output_next', $this->get_paged_url( 'next' ), $id );

		/**
		 * Applies filters 'the_seo_framework_paged_url_output' : array
		 * @since 2.6.0
		 */
		$prev = (string) apply_filters( 'the_seo_framework_paged_url_output_prev', $this->get_paged_url( 'prev' ), $id );

		$output = '';

		if ( $prev )
			$output .= sprintf( '<link rel="prev" href="%s" />' . "\r\n", $prev );

		if ( $next )
			$output .= sprintf( '<link rel="next" href="%s" />' . "\r\n", $next );

		return $output;
	}

	/**
	 * Whether we can use Open Graph tags.
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
	 * Whether we can use Facebook tags.
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
	 * Whether we can use Twitter tags.
	 *
	 * @since 2.6.0
	 * @staticvar bool $cache
	 *
	 * @return bool
	 */
	public function use_twitter_tags() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		return $cache = $this->is_option_checked( 'twitter_tags' ) && false == $this->detect_twitter_card_plugin();
	}

	/**
	 * Whether we can use Google+ tags.
	 *
	 * @since 2.6.0
	 * @staticvar bool $cache
	 *
	 * @return bool
	 */
	public function use_googleplus_tags() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		return $cache = $this->is_option_checked( 'googleplus_tags' );
	}

}
