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
 * Class AutoDescription_Siteoptions
 *
 * Holds Site Options for the plugin.
 *
 * @since 2.2.2
 */
class AutoDescription_Siteoptions extends AutoDescription_Sanitize {

	/**
	 * Filterable Site Settings array.
	 *
	 * @since 2.2.2
	 *
	 * @var array Holds Site SEO options.
	 */
	protected $default_site_options = array();

	/**
	 * Filterable Site Settings array.
	 *
	 * @since 2.2.2
	 *
	 * @var array Holds Site SEO options.
	 */
	protected $warned_site_options = array();

	/**
	 * Site Settings field.
	 *
	 * @since 2.2.2
	 *
	 * @var string Settings field.
	 */
	protected $settings_field;

	/**
	 * Hold the Page ID for this class
	 *
	 * @since 2.2.2
	 *
	 * @var string The page ID
	 */
	protected $page_id;

	/**
	 * Constructor, load parent constructor and set up cachable variables.
	 */
	public function __construct() {
		parent::__construct();

		//* Register defaults early.
		add_action( 'after_setup_theme', array( $this, 'initialize_defaults' ), 0 );

		$this->settings_field = THE_SEO_FRAMEWORK_SITE_OPTIONS;

		//* Set up site settings and save/reset them
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// The page_id
		$this->page_id = 'autodescription-settings';

		/**
		 * Add plugin links to the plugin activation page.
		 * @since 2.2.8
		 */
		add_filter( 'plugin_action_links_' . THE_SEO_FRAMEWORK_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ), 10, 2 );
	}

	/**
	 * Initializes default settings very early at the after_setup_theme hook
	 * Therefore supports is_rtl().
	 *
	 * @since 2.5.0
	 */
	public function initialize_defaults() {
		/**
		 * Switch when RTL is active;
		 * @since 2.5.0
		 */
		if ( is_rtl() ) {
			$titleloc = 'left';
			$h_titleloc = 'right';
		} else {
			$titleloc = 'right';
			$h_titleloc = 'left';
		}

		/**
		 * Default site settings. Separated from Author, page or network settings.
		 *
		 * These settings can be overwritten per page or post depending on type and setting.
		 *
		 * @since 2.2.2
		 *
		 * No longer directly applies filters
		 * @since 2.2.7
		 */
		$this->default_site_options = array(
			'title_seperator'		=> 'pipe',	// Title separator (note: TYPO)
			'title_location'		=> $titleloc,	// Title separation location

			'description_separator'	=> 'pipe',	// Description separator
			'description_blogname'	=> 1, 		// "on Blogname" within Description

			'noodp'					=> 1, 	// Site noopd robots settings
			'noydir'				=> 1, 	// Site noydir robots settings

			'category_noindex'		=> 0,	// Category Archive robots noindex
			'tag_noindex'			=> 0,	// Tag Archive robots noindex
			'author_noindex'		=> 0,	// Author Archive robots noindex
			'date_noindex'			=> 1,	// Date Archive robots noindex
			'search_noindex'		=> 1,	// Search Page robots noindex
			'attachment_noindex'	=> 1,	// Attachment Pages robots noindex
			'site_noindex'			=> 0,	// Site Page robots noindex

			'category_nofollow'		=> 0,	// Category Archive robots nofollow
			'tag_nofollow'			=> 0,	// Tag Archive robots nofollow
			'author_nofollow'		=> 0,	// Author Archive robots nofollow
			'date_nofollow'			=> 0,	// Date Archive robots nofollow
			'search_nofollow'		=> 0,	// Search Page robots nofollow
			'attachment_nofollow'	=> 0,	// Attachment Pages robots noindex
			'site_nofollow'			=> 0,	// Site Page robots nofollow

			'category_noarchive'	=> 0,	// Category Archive robots noarchive
			'tag_noarchive'			=> 0,	// Tag Archive robots noarchive
			'author_noarchive'		=> 0,	// Author Archive robots noarchive
			'date_noarchive'		=> 0,	// Date Archive robots noarchive
			'search_noarchive'		=> 0,	// Search Page robots noarchive
			'attachment_noarchive'	=> 0,	// Attachment Page robots noarchive
			'site_noarchive'		=> 0,	// Site Page robots noarchive

			'paged_noindex'			=> 1,	// Every second or later page noindex

			'homepage_noindex'		=> 0,	// Home Page robots noindex
			'homepage_nofollow'		=> 0,	// Home Page robots noarchive
			'homepage_noarchive'	=> 0,	// Home Page robots nofollow

			'homepage_title'		=> '',	// Home Page Title string
			'homepage_tagline'		=> 1,	// Home Page add blog Tagline
			'homepage_description'	=> '',	// Home Page Description string
			'homepage_title_tagline' => '',	// Home Page Tagline string
			'home_title_location'	=> $h_titleloc,	// Title separation location

			'shortlink_tag'			=> 0,	// Adds shortlink tag

			'prev_next_posts'		=> 0,	// Adds next/prev tags
			'prev_next_archives'	=> 1,	// Adds next/prev tags

			'facebook_publisher'	=> '',	// Facebook Business Url
			'facebook_author'		=> '',	// Facebook User URl
			'facebook_appid'		=> '',	// Facebook App ID

			'post_publish_time'		=> 1,	// Article Published Time
			'post_modify_time'		=> 1,	// Article Modified Time

			'page_publish_time'		=> 0,	// Article Published Time
			'page_modify_time'		=> 0,	// Article Modified Time

			'home_publish_time'		=> 0,	// Article Modified Time
			'home_modify_time'		=> 0,	// Article Modified Time

			'twitter_card' 			=> 'summary_large_image',	// Twitter Card layout. If no twitter:image image is found, it'll change to 'summary'
			'twitter_site' 			=> '', 	// Twitter business @username
			'twitter_creator' 		=> '', 	// Twitter user @username

			'og_tags' 				=> 1,	// Output of Open Graph meta tags
			'facebook_tags'			=> 1, 	// Output the Facebook meta tags
			'twitter_tags'			=> 1, 	// Output the Twitter meta tags

			'google_verification'	=> '', 	// Google Verification Code
			'bing_verification'		=> '', 	// Bing Verification Code

			// https://developers.google.com/structured-data/customize/contact-points - This is extremely extended and valuable. Expect a premium version.
			'knowledge_output'		=> 1,				// Default for outputing the Knowledge SEO.
			'knowledge_type'		=> 'organization',	// Organization or Person

			// https://developers.google.com/structured-data/customize/logos
			'knowledge_logo'		=> 1,	// Fetch logo from WP Favicon
			'knowledge_name'		=> '',	// Person or Organization name

			// 'Sameas'
			'knowledge_facebook'	=> '',	// Facebook Account
			'knowledge_twitter'		=> '',	// Twitter Account
			'knowledge_gplus'		=> '',	// Google Plus Account
			'knowledge_instagram'	=> '',	// Instagram Account
			'knowledge_youtube'		=> '',	// Youtube Account
			'knowledge_linkedin'	=> '',	// Linkedin Account
		//	'knowledge_myspace'		=> '',	// MySpace Account // meh.
			'knowledge_pinterest'	=> '',	// Pinterest Account
			'knowledge_soundcloud'	=> '',	// SoundCloud Account
			'knowledge_tumblr'		=> '',	// Tumblr Account

			// Sitemaps
			'sitemaps_output'		=> 1,	// Output of sitemaps
			'sitemaps_modified'		=> 1,	// Add sitemaps modified time
			'sitemaps_robots'		=> 1,	// Add sitemaps location to robots.txt
			'ping_google'			=> 1,	// Ping Google
			'ping_bing'				=> 1,	// Ping Bing
			'ping_yahoo'			=> 1,	// Ping Yahoo
		);

		/**
		 * Warned site settings. Only accepts checkbox options.
		 * When listed as 1, it's a feature which can destroy your site's SEO value when checked.
		 *
		 * Unchecking a box is simply "I'm not active." - Removing features generally do not negatively impact SEO value.
		 * Since it's all about the content.
		 *
		 * Only used within the SEO Settings page.
		 */
		$this->warned_site_options = array(
			'noodp'					=> 0, 	// Site noopd robots settings
			'noydir'				=> 0, 	// Site noydir robots settings

			'description_blogname'	=> 0, 	// "on Blogname" within Description

			'category_noindex'		=> 0,	// Category Archive robots noindex
			'tag_noindex'			=> 0,	// Tag Archive robots noindex
			'author_noindex'		=> 0,	// Author Archive robots noindex
			'date_noindex'			=> 0,	// Date Archive robots noindex
			'search_noindex'		=> 0,	// Search Page robots noindex
			'attachment_noindex'	=> 0,	// Attachment Pages robots noindex
			'site_noindex'			=> 1,	// Site Page robots noindex

			'category_nofollow'		=> 0,	// Category Archive robots nofollow
			'tag_nofollow'			=> 0,	// Tag Archive robots nofollow
			'author_nofollow'		=> 0,	// Author Archive robots nofollow
			'date_nofollow'			=> 0,	// Date Archive robots nofollow
			'search_nofollow'		=> 0,	// Search Page robots nofollow
			'attachment_nofollow'	=> 0,	// Attachment Pages robots noindex
			'site_nofollow'			=> 1,	// Site Page robots nofollow

			'category_noarchive'	=> 0,	// Category Archive robots noarchive
			'tag_noarchive'			=> 0,	// Tag Archive robots noarchive
			'author_noarchive'		=> 0,	// Author Archive robots noarchive
			'date_noarchive'		=> 0,	// Date Archive robots noarchive
			'search_noarchive'		=> 0,	// Search Page robots noarchive
			'attachment_noarchive'	=> 0,	// Attachment Page robots noarchive
			'site_noarchive'		=> 0,	// Site Page robots noarchive

			'paged_noindex'			=> 0,	// Every second or later page noindex

			'homepage_noindex'		=> 1,	// Home Page robots noindex
			'homepage_nofollow'		=> 1,	// Home Page robots noarchive
			'homepage_noarchive'	=> 0,	// Home Page robots nofollow

			'homepage_tagline'		=> 0,	// Home Page add blog Tagline

			'shortlink_tag'			=> 0,	// Adds shortlink tag

			'prev_next_posts'		=> 0,	// Adds next/prev tags
			'prev_next_archives'	=> 0,	// Adds next/prev tags

			'post_publish_time'		=> 0,	// Article Published Time
			'post_modify_time'		=> 0,	// Article Modified Time

			'page_publish_time'		=> 0,	// Article Published Time
			'page_modify_time'		=> 0,	// Article Modified Time

			'home_publish_time'		=> 0,	// Article Modified Time
			'home_modify_time'		=> 0,	// Article Modified Time

			'og_tags' 				=> 0,	// Output of Open Graph meta tags
			'facebook_tags'			=> 0, 	// Output the Facebook meta tags
			'twitter_tags'			=> 0, 	// Output the Twitter meta tags

			'knowledge_output'		=> 0,	// Default for outputing the Knowledge SEO.
			'knowledge_logo'		=> 0,	// Fetch logo from WP Favicon

			// Sitemaps
			'sitemaps_output'		=> 0,	// Output of sitemaps
			'sitemaps_modified'		=> 0,	// Add sitemaps modified time
			'sitemaps_robots'		=> 0,	// Add sitemaps location to robots.txt
			'ping_google'			=> 0,	// Ping Google
			'ping_bing'				=> 0,	// Ping Bing
			'ping_yahoo'			=> 0,	// Ping Yahoo
		);
	}

	/**
	 * Return SEO options from the SEO options database.
	 *
	 * @since 2.2.2
	 *
	 * @uses $this->the_seo_framework_get_option() Return option from the options table and cache result.
	 * @uses THE_SEO_FRAMEWORK_SITE_OPTIONS
	 *
	 * @param string  $key       Option name.
	 * @param boolean $use_cache Optional. Whether to use the cache value or not. Defaults to true.
	 *
	 * @return mixed The value of this $key in the database.
	 */
	public function get_option( $key, $use_cache = true ) {
		return $this->the_seo_framework_get_option( $key, THE_SEO_FRAMEWORK_SITE_OPTIONS, $use_cache );
	}

	/**
	 * Return SEO options from the SEO options database.
	 *
	 * @since 2.2.2
	 *
	 * @uses $this->the_seo_framework_get_option() Return option from the options table and cache result.
	 * @uses THE_SEO_FRAMEWORK_NETWORK_OPTIONS
	 *
	 * @param string  $key       Option name.
	 * @param boolean $use_cache Optional. Whether to use the cache value or not. Defaults to true.
	 *
	 * @return mixed The value of this $key in the database.
	 */
	public function get_site_option( $key, $use_cache = true ) {
		return $this->the_seo_framework_get_option( $key, THE_SEO_FRAMEWORK_NETWORK_OPTIONS, $use_cache );
	}

	/**
	 * Return Default SEO options from the SEO options array.
	 *
	 * @since 2.2.5
	 *
	 * @uses $this->get_default_settings() Return option from the options table and cache result.
	 * @uses THE_SEO_FRAMEWORK_SITE_OPTIONS
	 *
	 * @param string  $key       Option name.
	 * @param boolean $use_cache Optional. Whether to use the cache value or not. Defaults to true.
	 *
	 * @return mixed The value of this $key in the database.
	 */
	public function get_default_option( $key, $use_cache = true ) {
		return $this->get_default_settings( $key, THE_SEO_FRAMEWORK_SITE_OPTIONS, $use_cache );
	}

	/**
	 * Return the compiled default options array.
	 *
	 * @since 2.2.7
	 *
	 * Applies filters the_seo_framework_default_site_options The default site options array.
	 *
	 * @param array $args The new default options through filter.
	 * @return array The SEO Framework Options
	 */
	protected function default_site_options( $args = array() ) {
		/**
		 * New filter.
		 * @since 2.3.0
		 *
		 * Removed previous filter.
		 * @since 2.3.5
		 */
		return $this->default_site_options = wp_parse_args(
			$args,
			apply_filters(
				'the_seo_framework_default_site_options',
				wp_parse_args(
					$args,
					$this->default_site_options
				)
			)
		);
	}

	/**
	 * Return the Warned site options. Options which should be 'avoided' return true.
	 *
	 * @since 2.3.4
	 *
	 * Applies filters the_seo_framework_warned_site_options The warned site options array.
	 *
	 * @param array $args The new warned options through filter.
	 * @return array The SEO Framework Warned Options
	 */
	protected function warned_site_options( $args = array() ) {
		return $this->default_site_options = wp_parse_args(
			$args,
			apply_filters(
				'the_seo_framework_warned_site_options',
				wp_parse_args(
					$args,
					$this->warned_site_options
				)
			)
		);
	}

	/**
	 * Register the database settings for storage.
	 *
	 * @since 2.2.2
	 *
	 * @return void
	 *
	 * @thanks StudioPress (http://www.studiopress.com/) for some code.
	 */
	public function register_settings() {
		//* If this page doesn't store settings, no need to register them
		if ( ! $this->settings_field )
			return;

		register_setting( $this->settings_field, $this->settings_field );
		add_option( $this->settings_field, $this->default_site_options() );

		//* If this page isn't the SEO Settings page, there's no need to check for a reset.
		if ( ! $this->is_menu_page( $this->page_id ) )
			return;

		if ( $this->get_option( 'reset', $this->settings_field ) ) {
			if ( update_option( $this->settings_field, $this->default_site_options() ) )
				$this->admin_redirect( $this->page_id, array( 'reset' => 'true' ) );
			else
				$this->admin_redirect( $this->page_id, array( 'error' => 'true' ) );
			exit;
		}

	}

	/**
	 * Get the default of any of the The SEO Framework settings.
	 *
	 * @since 2.2.4
	 *
	 * @uses $this->settings_field
	 * @uses $this->default_site_options()
	 *
	 * @param string $key required The option name
	 * @param string $setting optional The settings field
	 * @param bool $use_cache optional Use the options cache or not. For debugging purposes.
	 *
	 * @staticvar array $defaults_cache
	 *
	 * @return 	int|bool|string default option
	 *			int '-1' if option doesn't exist.
	 */
	public function get_default_settings( $key, $setting = '', $use_cache = true ) {

		if ( !isset( $key ) || empty( $key ) )
			return false;

		//* Fetch default settings if it's not set.
		if ( empty( $setting ) )
			$setting = $this->settings_field;

		//* If we need to bypass the cache
		if ( ! $use_cache ) {
			$defaults = $this->default_site_options();

			if ( ! is_array( $defaults ) || ! array_key_exists( $key, $defaults ) )
				return -1;

			return is_array( $defaults[$key] ) ? stripslashes_deep( $defaults[$key] ) : stripslashes( wp_kses_decode_entities( $defaults[$key] ) );
		}

		static $defaults_cache = array();

		//* Check options cache
		if ( isset( $defaults_cache[$key] ) )
			//* Option has been cached
			return $defaults_cache[$key];

		$defaults_cache = $this->default_site_options();

		if ( ! is_array( $defaults_cache ) || ! array_key_exists( $key, (array) $defaults_cache ) )
			$defaults_cache[$key] = -1;

		return $defaults_cache[$key];
	}

	/**
	 * Get the warned setting of any of the The SEO Framework settings.
	 *
	 * @since 2.3.4
	 *
	 * @uses $this->settings_field
	 * @uses $this->warned_site_options()
	 *
	 * @param string $key required The option name
	 * @param string $setting optional The settings field
	 * @param bool $use_cache optional Use the options cache or not. For debugging purposes.
	 *
	 * @staticvar array $warned_cache
	 *
	 * @return 	int|bool|string default option
	 *			int '-1' if option doesn't exist.
	 */
	public function get_warned_settings( $key, $setting = '', $use_cache = true ) {

		if ( !isset( $key ) || empty( $key ) )
			return false;

		//* Fetch default settings if it's not set.
		if ( empty( $setting ) )
			$setting = $this->settings_field;

		//* If we need to bypass the cache
		if ( ! $use_cache ) {
			$warned = $this->warned_site_options();

			if ( ! is_array( $warned ) || ! array_key_exists( $key, $warned ) )
				return -1;

			return is_array( $warned[$key] ) ? stripslashes_deep( $warned[$key] ) : stripslashes( wp_kses_decode_entities( $warned[$key] ) );
		}

		static $warned_cache = array();

		//* Check options cache
		if ( isset( $warned_cache[$key] ) )
			//* Option has been cached
			return $warned_cache[$key];

		$warned_cache = $this->warned_site_options();

		if ( ! is_array( $warned_cache ) || ! array_key_exists( $key, (array) $warned_cache ) )
			$warned_cache[$key] = -1;

		return $warned_cache[$key];
	}

	/**
	 * Adds link from plugins page to SEO Settings page.
	 *
	 * @param array $links The current links.
	 *
	 * @since 2.2.8
	 */
	public function plugin_action_links( $links ) {

		$framework_links = array(
			'settings' => '<a href="' . esc_url( admin_url( 'admin.php?page=' . $this->page_id ) ) . '">' . __( 'SEO Settings', 'autodescription' ) . '</a>',
			'home' => '<a href="'. esc_url( 'http://theseoframework.com', 'http' ) . '" target="_blank">' . _x( 'Plugin Home', 'As in: The Plugin Home Page', 'autodescription' ) . '</a>'
		);

		return array_merge( $framework_links, $links );
	}

}
