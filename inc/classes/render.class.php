<?php
/**
 * The SEO Framework plugin
 * Copyright (C) 2015 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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

		$description_cache[$social] = $this->generate_description( '', array( 'social' => $social ) );

		return $description_cache[$social];
	}

	/**
	 * Cache current URL in static variable
	 * Must be called inside the loop
	 *
	 * @param string $url the url
	 * @param int $page_id the page id, if empty it will fetch the requested ID, else the page uri
	 * @param bool $paged Return current page URL without pagination
	 * @param bool $from_option Get the canonical uri option
	 *
	 * @staticvar array $url_cache
	 *
	 * @since 2.2.2
	 * @return string The url
	 */
	public function the_url_from_cache( $url = '', $page_id = '', $paged = false, $from_option = true ) {

		static $url_cache = array();

		if ( isset( $url_cache[$url][$page_id][$paged][$from_option] ) )
			return $url_cache[$url][$page_id][$paged][$from_option];

		$url_cache[$url][$page_id][$paged][$from_option] = $this->the_url( $url, $page_id, array( 'paged' => $paged, 'get_custom_field' => $from_option ) );

		return $url_cache[$url][$page_id][$paged][$from_option];
	}

	/**
	 * Cache home URL in static variable
	 *
	 * @param bool $force_slash Force slash
	 *
	 * @staticvar array $url_cache
	 *
	 * @since 2.4.4
	 * @return string The url
	 */
	public function the_home_url_from_cache( $force_slash = false ) {

		static $url_cache = array();

		if ( isset( $url_cache[$force_slash] ) )
			return $url_cache[$force_slash];

		$url_cache[$force_slash] = $this->the_url( '', '', array( 'home' => true, 'force_slash' => $force_slash ) );

		return $url_cache[$force_slash];
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
			if ( doing_filter( 'wp_title' ) || doing_filter( 'pre_get_document_title' ) ) {
				$title_param_cache = $title;
				$sep_param_cache = $sep;
				$seplocation_param_cache = $seplocation;

				$setup_cache = 'I like turtles.';
			}
		}

		/**
		 * If the theme is doing it right, override parameters.
		 *
		 * @since 2.4.0
		 */
		if ( isset( $this->title_doing_it_wrong ) && ! $this->title_doing_it_wrong ) {
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
		if ( ! $post_id )
			return '';

		$image_cache = esc_url_raw( $this->get_image( $post_id ) );

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

		//* @since 2.3.0
		$description = (string) apply_filters( 'the_seo_framework_description_output', '' );

		if ( empty( $description ) )
			$description = $this->description_from_cache();

		if ( ! empty( $description ) )
			return '<meta name="description" content="' . esc_attr( $description ) . '" />' . "\r\n";

		return '';
	}

	/**
	 * Render og:description
	 *
	 * @uses $this->description_from_cache()
	 * @uses $this->has_og_plugin()
	 *
	 * @since 1.3.0
	 */
	public function og_description() {

		if ( $this->has_og_plugin() !== false )
			return;

		/**
		 * Return if OG tags are disabled
		 *
		 * @since 2.2.2
		 */
		if ( ! $this->get_option( 'og_tags' ) )
			return;

		//* @since 2.3.0
		$description = (string) apply_filters( 'the_seo_framework_ogdescription_output', '' );

		if ( empty( $description ) )
			$description = $this->description_from_cache( true );

		return '<meta property="og:description" content="' . esc_attr( $description ) . '" />' . "\r\n";
	}

	/**
	 * Render the locale
	 *
	 * @uses $this->has_og_plugin()
	 *
	 * @since 1.0.0
	 */
	public function og_locale() {

		if ( $this->has_og_plugin() !== false )
			return;

		/**
		 * Return if OG tags are disabled
		 *
		 * @since 2.2.2
		 */
		if ( ! $this->get_option( 'og_tags' ) )
			return;

		//* @since 2.3.0
		$locale = (string) apply_filters( 'the_seo_framework_oglocale_output', '' );

		if ( empty( $locale ) )
			$locale = get_locale();

		return '<meta property="og:locale" content="' . esc_attr( $locale ) . '" />' . "\r\n";
	}

	/**
	 * Process the title to WordPress
	 *
	 * @uses $this->title_from_cache()
	 * @uses $this->has_og_plugin()
	 *
	 * @since 2.0.3
	 */
	public function og_title() {

		if ( $this->has_og_plugin() !== false )
			return;

		/**
		 * Return if OG tags are disabled
		 *
		 * @since 2.2.2
		 */
		if ( ! $this->get_option( 'og_tags' ) )
			return;

		//* @since 2.3.0
		$title = (string) apply_filters( 'the_seo_framework_ogtitle_output', '' );

		if ( empty( $title ) )
			$title = $this->title_from_cache( '', '', '', true );

		return '<meta property="og:title" content="' . esc_attr( $title ) . '" />' . "\r\n";
	}

	/**
	 * Get the type
	 *
	 * @uses $this->has_og_plugin()
	 *
	 * @since 1.1.0
	 */
	public function og_type() {

		if ( $this->has_og_plugin() !== false )
			return;

		/**
		 * Return if OG tags are disabled
		 *
		 * @since 2.2.2
		 */
		if ( ! $this->get_option( 'og_tags' ) )
			return;

		//* @since 2.3.0
		$type = (string) apply_filters( 'the_seo_framework_ogtype_output', '' );

		if ( empty( $type ) ) {
			if ( is_single() ) {
				$type = 'article';
			} else if ( is_author() ) {
				$type = 'profile';
			} else {
				$type = 'website';
			}
		}

		return '<meta property="og:type" content="' . esc_attr( $type ) . '" />' . "\r\n";
	}

	/**
	 * Adds og:image
	 *
	 * @param string $image url for image
	 *
	 * @since 1.3.0
	 */
	public function og_image() {

		/**
		 * Return if OG tags are disabled
		 *
		 * @since 2.2.2
		 */
		if ( ! $this->get_option( 'og_tags' ) )
			return;

		//* @since 2.3.0
		$image = (string) apply_filters( 'the_seo_framework_ogimage_output', '' );

		if ( empty( $image ) )
			$image = $this->get_image_from_cache();

		if ( function_exists( 'is_product' ) && is_product() ) {

			$output = '';

			if ( ! empty( $image ) )
				$output .= '<meta property="og:image" content="' . esc_attr( $image ) . '" />' . "\r\n";

			$images = $this->get_image_from_woocommerce_gallery();

			if ( is_array( $images ) && ! empty( $images ) ) {
				foreach ( $images as $id ) {
					//* Parse 1500px url.
					$img = $this->parse_og_image( $id );

					if ( ! empty( $img ) )
						$output .= '<meta property="og:image" content="' . esc_attr( $img ) . '" />' . "\r\n";
				}
			} else if ( empty( $output ) ) {
				//* Always add empty if none is found.
				$output .= '<meta property="og:image" content="' . esc_attr( $image ) . '" />' . "\r\n";
			}
		} else {
			/**
			 * Always output
			 *
			 * @since 2.1.1
			 */
			$output = '<meta property="og:image" content="' . esc_attr( $image ) . '" />' . "\r\n";
		}

		return $output;
	}

	/**
	 * Adds og:site_name
	 *
	 * @uses wp
	 *
	 * @param string output	the output
	 *
	 * @since 1.3.0
	 */
	public function og_sitename() {

		//* if WPSEO is active
		if ( $this->has_og_plugin() !== false )
			return;

		/**
		 * Return if OG tags are disabled
		 *
		 * @since 2.2.2
		 */
		if ( ! $this->get_option( 'og_tags' ) )
			return;

		//* @since 2.3.0
		$sitename = (string) apply_filters( 'the_seo_framework_ogsitename_output', '' );

		if ( empty( $sitename ) )
			$sitename = get_bloginfo('name');

		return '<meta property="og:site_name" content="' . esc_attr( $sitename ) . '" />' . "\r\n";
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

		//* if WPSEO is active
		if ( $this->has_og_plugin() !== false )
			return;

		/**
		 * Return if OG tags are disabled
		 *
		 * @since 2.2.2
		 */
		if ( ! $this->get_option( 'og_tags' ) )
			return;

		return '<meta property="og:url" content="' . esc_attr( $this->the_url_from_cache() ) . '" />' . "\r\n";
	}

	/**
	 * Render twitter:card
	 *
	 * @uses $this->has_og_plugin()
	 *
	 * @since 2.2.2
	 */
	public function twitter_card() {

		if ( ! $this->get_option( 'twitter_tags' ) )
			return;

		//* @since 2.3.0
		$card = (string) apply_filters( 'the_seo_framework_twittercard_output', '' );

		if ( empty( $card ) ) {
			/**
			 * Return card type if image is found
			 * Return to summary if not
			 */
			$card = $this->get_image_from_cache() ? $this->get_option( 'twitter_card' ) : 'summary';
		}

		return '<meta name="twitter:card" content="' . esc_attr( $card ) . '" />' . "\r\n";
	}

	/**
	 * Render twitter:site
	 *
	 * @since 2.2.2
	 */
	public function twitter_site() {

		if ( ! $this->get_option( 'twitter_tags' ) )
			return;

		//* @since 2.3.0
		$site = (string) apply_filters( 'the_seo_framework_twittersite_output', '' );

		if ( empty( $site ) ) {
			$site = $this->get_option( 'twitter_site' );

			/**
			 * Return empty if no twitter_site is found
			 */
			if ( empty( $site ) )
				return '';
		}

		return '<meta name="twitter:site" content="' . esc_attr( $site ) . '" />' . "\r\n";
	}

	/**
	 * Render twitter:creator or twitter:site:id
	 *
	 * @since 2.2.2
	 */
	public function twitter_creator() {

		if ( ! $this->get_option( 'twitter_tags' ) )
			return;

		//* @since 2.3.0
		$creator = (string) apply_filters( 'the_seo_framework_twittercreator_output', '' );

		if ( empty( $creator ) ) {
			$site = $this->get_option( 'twitter_site' );
			$creator = $this->get_option( 'twitter_creator' );

			/**
			 * Return site:id instead of creator is no twitter:site is found.
			 * Per Twitter requirements
			 */
			if ( empty( $site ) && !empty( $creator ) )
				return '<meta name="twitter:site:id" content="' . esc_attr( $creator ) . '" />' . "\r\n";
		}

		if ( empty( $creator ) )
			return '';

		return '<meta name="twitter:creator" content="' . esc_attr( $creator ) . '" />' . "\r\n";
	}

	/**
	 * Render twitter:title
	 *
	 * @uses $this->title_from_cache()
	 * @uses $this->has_og_plugin()
	 *
	 * @since 2.2.2
	 */
	public function twitter_title() {

		if ( ! $this->get_option( 'twitter_tags' ) )
			return;

		//* @since 2.3.0
		$title = (string) apply_filters( 'the_seo_framework_twittertitle_output', '' );

		if ( empty( $title ) )
			$title = $this->title_from_cache( '', '', '', true );

		return '<meta name="twitter:title" content="' . esc_attr( $title ) . '" />' . "\r\n";
	}

	/**
	 * Render twitter:description
	 *
	 * @uses $this->description_from_cache()
	 *
	 * @since 2.2.2
	 */
	public function twitter_description() {

		if ( ! $this->get_option( 'twitter_tags' ) )
			return;

		//* @since 2.3.0
		$description = (string) apply_filters( 'the_seo_framework_twitterdescription_output', '' );

		if ( empty( $description ) )
			$description = $this->description_from_cache( true );

		return '<meta name="twitter:description" content="' . esc_attr( $description ) . '" />' . "\r\n";
	}

	/**
	 * Render twitter:image
	 *
	 * @param string $image url for image
	 *
	 * @since 2.2.2
	 *
	 * @return string|null The twitter image source meta tag
	 */
	public function twitter_image() {

		if ( ! $this->get_option( 'twitter_tags' ) )
			return;

		//* @since 2.3.0
		$image = (string) apply_filters( 'the_seo_framework_twitterimage_output', '' );

		if ( empty( $image ) )
			$image = $this->get_image_from_cache();

		if ( !empty( $image ) ) {
			return '<meta name="twitter:image:src" content="' . esc_attr( $image ) . '" />' . "\r\n";
		} else {
			return '';
		}

	}

	/**
	 * Render article:author
	 *
	 * @since 2.2.2
	 *
	 * @return string|null The facebook app id
	 */
	public function facebook_author() {

		if ( ! $this->get_option( 'facebook_tags' ) )
			return;

		//* @since 2.3.0
		$author = (string) apply_filters( 'the_seo_framework_facebookauthor_output', '' );

		if ( empty( $author ) )
			$author = $this->get_option( 'facebook_author' );

		if ( ! empty( $author ) )
			return '<meta name="article:author" content="' . esc_attr( esc_url_raw( $author ) ) . '" />' . "\r\n";

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

		if ( ! $this->get_option( 'facebook_tags' ) )
			return;

		//* @since 2.3.0
		$publisher = (string) apply_filters( 'the_seo_framework_facebookpublisher_output', '' );

		if ( empty( $publisher ) )
			$publisher = $this->get_option( 'facebook_publisher' );

		if ( ! empty( $publisher ) )
			return '<meta name="article:publisher" content="' . esc_attr( esc_url_raw( $publisher ) ) . '" />' . "\r\n";

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

		if ( ! $this->get_option( 'facebook_tags' ) )
			return;

		//* @since 2.3.0
		$app_id = (string) apply_filters( 'the_seo_framework_facebookappid_output', '' );

		if ( empty( $app_id ) )
			$app_id = $this->get_option( 'facebook_appid' );

		if ( ! empty( $app_id ) )
			return '<meta name="fb:app_id" content="' . esc_attr( $app_id ) . '" />' . "\r\n";

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
		if ( !is_singular() )
			return;

		$front_page = (bool) is_front_page();

		// If it's a post, but the option is disabled, don't do anyhting.
		if ( ! $front_page && is_single() && ! $this->get_option( 'post_publish_time' ) )
			return;

		// If it's a page, but the option is disabled, don't do anything.
		if ( ! $front_page && is_page() && ! $this->get_option( 'page_publish_time' ) )
			return;

		// If it's  the home page, but the option is disabled, don't do anything.
		if ( $front_page && ! $this->get_option( 'home_publish_time' ) )
			return;

		//* @since 2.3.0
		$time = (string) apply_filters( 'the_seo_framework_publishedtime_output', '' );

		if ( empty( $time ) )
			$time = get_the_date( 'Y-m-d', '' );

		if ( ! empty( $time ) )
			return '<meta name="article:published_time" content="' . esc_attr( $time ) . '" />' . "\r\n";

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
		if ( !is_singular() )
			return;

		$is_front_page = is_front_page();

		// If it's a post, but the option is disabled, don't do anyhting.
		if ( ! $is_front_page && is_single() && ! $this->get_option( 'post_modify_time' ) )
			return;

		// If it's a page, but the option is disabled, don't do anything.
		if ( ! $is_front_page && is_page() && ! $this->get_option( 'page_modify_time' ) )
			return;

		// If it's the home page, but the option is disabled, don't do anything.
		if ( $is_front_page && ! $this->get_option( 'home_modify_time' ) )
			return;

		//* @since 2.3.0
		$time = (string) apply_filters( 'the_seo_framework_modifiedtime_output', '' );

		if ( empty( $time ) )
			$time = the_modified_date( 'Y-m-d', '', '', false );

		if ( ! empty( $time ) ) {
			$output = '<meta name="article:modified_time" content="' . esc_attr( $time ) . '" />' . "\r\n";

			if ( $this->get_option( 'og_tags' ) )
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

		//* if WPSEO is active
		if ( $this->has_og_plugin() !== false )
			return;

		/**
		 * Applies filters the_seo_framework_output_canonical : Don't output canonical if false.
		 * @since 2.4.2
		 */
		if ( ! apply_filters( 'the_seo_framework_output_canonical', true ) )
			return;

		if ( ! get_option( 'permalink_structure' ) || is_404() )
			return;

		$url = $this->the_url_from_cache();

		/**
		 * Applies filters the_seo_framework_canonical_force_scheme : Changes scheme.
		 *
		 * Accepted variables:
		 * (string) 'https'		: 	Force https
		 * (bool) true 			: 	Force https
		 * (bool) false			: 	Force http
		 * (string) 'http'		: 	Force http
		 * (string) 'relative' 	:	Scheme relative
		 * (void) null			: 	Do nothing
		 *
		 * @since 2.4.2
		 */
		$scheme_settings = apply_filters( 'the_seo_framework_canonical_force_scheme', null );

		if ( isset( $scheme_settings ) ) {
			if ( 'https' ===  $scheme_settings || 'http' === $scheme_settings || 'relative' === $scheme_settings ) {
				$url = $this->set_url_scheme( $url, $scheme_settings );
			} else if ( ! $scheme_settings ) {
				$url = $this->set_url_scheme( $url, 'http' );
			} else if ( $scheme_setting ) {
				$url = $this->set_url_scheme( $url, 'https' );
			}
		}

		return '<link rel="canonical" href="' . esc_attr( $url ) . '" />' . "\r\n";
	}

	/**
	 * LD+JSON helper output
	 *
	 * @uses $this->has_json_ld_plugin()
	 * @uses $this->ld_json_search()
	 * @uses $this->ld_json_knowledge()
	 *
	 * @since 1.2.0
	 * @return string $output LD+json helpers in header on front page.
	 */
	public function ld_json() {

		//* Check for WPSEO LD+JSON
		if ( $this->has_json_ld_plugin() !== false || is_search() || is_404() )
			return;

		$this->setup_ld_json_transient( $this->get_the_real_ID() );

		/**
		 * Debug transient key.
		 * @since 2.4.2
		 */
		if ( defined( 'THE_SEO_FRAMEWORK_DEBUG' ) && THE_SEO_FRAMEWORK_DEBUG ) {
			if ( defined ( 'THE_SEO_FRAMEWORK_DEBUG_HIDDEN' ) && THE_SEO_FRAMEWORK_DEBUG_HIDDEN )
				echo "<!--\r\n";

			echo  "\r\n" . 'START: ' .__CLASS__ . '::' . __FUNCTION__ .  "\r\n";
			$this->echo_debug_information( array( 'LD Json transient name' => $this->ld_json_transient ) );
			$this->echo_debug_information( array( 'Output from transient' => ( get_transient( $this->ld_json_transient ) ? true : false ) ) );

			if ( defined ( 'THE_SEO_FRAMEWORK_DEBUG_HIDDEN' ) && THE_SEO_FRAMEWORK_DEBUG_HIDDEN )
				echo "\r\n-->";
		}

		$output = get_transient( $this->ld_json_transient );
		if ( false === $output ) {

			$output = '';

			//* Only display search helper and knowledge graph on front page.
			if ( is_front_page() ) {

				/**
				 * Add multiple scripts
				 *
				 * @since 2.2.8
				 */
				$searchhelper = $this->ld_json_search();
				$knowledgegraph = $this->ld_json_knowledge();

				if ( ! empty( $searchhelper ) )
					$output .= "<script type='application/ld+json'>" . $searchhelper . "</script>" . "\r\n";

				if ( ! empty( $knowledgegraph ) )
					$output .= "<script type='application/ld+json'>" . $knowledgegraph . "</script>" . "\r\n";
			} else {
				$breadcrumbhelper = $this->ld_json_breadcrumbs();

				//* No wrapper, is done within script generator.
				if ( ! empty( $breadcrumbhelper ) )
					$output .= $breadcrumbhelper;
			}

			/**
			 * Transient expiration: 1 week.
			 * Keep the description for at most 1 week.
			 *
			 * 60s * 60m * 24h * 7d
			 */
			$expiration = 60 * 60 * 24 * 7;

			set_transient( $this->ld_json_transient, $output, $expiration );
		}

		/**
		 * Debug output.
		 * @since 2.4.2
		 */
		if ( defined( 'THE_SEO_FRAMEWORK_DEBUG' ) && THE_SEO_FRAMEWORK_DEBUG ) {

			if ( defined ( 'THE_SEO_FRAMEWORK_DEBUG_HIDDEN' ) && THE_SEO_FRAMEWORK_DEBUG_HIDDEN )
				echo "<!--\r\n";

			if ( defined( 'THE_SEO_FRAMEWORK_DEBUG_MORE' ) && THE_SEO_FRAMEWORK_DEBUG_MORE ) {
				$this->echo_debug_information( array( 'LD Json transient output' => $output ) );
			}
			echo  "\r\n" . 'END: ' .__CLASS__ . '::' . __FUNCTION__ .  "\r\n";

			if ( defined ( 'THE_SEO_FRAMEWORK_DEBUG_HIDDEN' ) && THE_SEO_FRAMEWORK_DEBUG_HIDDEN )
				echo "\r\n-->";
		}

		return $output;
	}

	/**
	 * Outputs Google Site Verification code
	 *
	 * @since 2.2.4
	 *
	 * @return string|null google verification code
	 */
	public function google_site_output() {

		//* @since 2.3.0
		$code = (string) apply_filters( 'the_seo_framework_googlesite_output', '' );

		if ( empty( $code ) )
			$code = $this->get_option( 'google_verification' );

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

		//* @since 2.3.0
		$code = (string) apply_filters( 'the_seo_framework_bingsite_output', '' );

		if ( empty( $code ) )
			$code = $this->get_option( 'bing_verification' );

		if ( empty( $code ) )
			return '';

		return '<meta name="msvalidate.01" content="' . esc_attr( $code ) . '" />' . "\r\n";
	}

	/**
	 * Output the `index`, `follow`, `noodp`, `noydir`, `noarchive` robots meta code in the document `head`.
	 *
	 * @since 2.0.0
	 *
	 * @return null Return early if blog is not public.
	 */
	public function robots() {

		// Don't do anything if the blog isn't set to public.
		if ( ! get_option( 'blog_public' ) )
			return '';

		$robots = '';
		$meta = $this->robots_meta();

		//* Add meta if any exist
		if ( ! empty( $meta ) )
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
	 */
	public function favicon() {

		if ( $this->wp_version( '4.3.0', '<' ) ) {
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

		$url = $this->get_shortlink();

		if ( ! empty( $url ) )
			return sprintf( '<link rel="shortlink" href="%s" />' . "\r\n", $url );

		return '';
	}

	/**
	 * Outputs shortlink meta tag
	 *
	 * @since 2.2.2
	 *
	 * @uses $this->get_paged_url()
	 *
	 * @return string|null shortlink url meta
	 */
	public function paged_urls() {

		$next = $this->get_paged_url( 'next' );
		$prev = $this->get_paged_url( 'prev' );

		$output = '';

		if ( $prev )
			$output .= sprintf( '<link rel="prev" href="%s" />' . "\r\n", $prev );
		if ( $next )
			$output .= sprintf( '<link rel="next" href="%s" />' . "\r\n", $next );

		return $output;
	}

}
