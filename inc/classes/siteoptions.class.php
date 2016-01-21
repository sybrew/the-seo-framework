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
			'title_rem_additions'	=> 0,		// Remove title additions

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
			'pint_verification'		=> '', 	// Pinterest Verification Code

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

			// Feed
			'excerpt_the_feed'		=> 1,	// Generate feed Excerpts
			'source_the_feed'		=> 1,	// Add backlink at the end of the feed
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
			'title_rem_additions'	=> 1, 	// Title remove additions.

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

			// Feed
			'excerpt_the_feed'		=> 0,	// Generate feed Excerpts
			'source_the_feed'		=> 0,	// Add backlink at the end of the feed
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
		return $this->warned_site_options = wp_parse_args(
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

		if ( ! isset( $key ) || empty( $key ) )
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

		if ( ! isset( $key ) || empty( $key ) )
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
			'home' => '<a href="'. esc_url( 'https://theseoframework.com' ) . '" target="_blank">' . _x( 'Plugin Home', 'As in: The Plugin Home Page', 'autodescription' ) . '</a>'
		);

		return array_merge( $framework_links, $links );
	}

	/**
	 * Returns Facebook locales array
	 *
	 * @see https://www.facebook.com/translations/FacebookLocales.xml
	 *
	 * @since 2.5.2
	 * @return array Valid Facebook locales
	 */
	public function fb_locales() {
		return array(
			'af_ZA', // Afrikaans
			'ak_GH', // Akan
			'am_ET', // Amharic
			'ar_AR', // Arabic
			'as_IN', // Assamese
			'ay_BO', // Aymara
			'az_AZ', // Azerbaijani
			'be_BY', // Belarusian
			'bg_BG', // Bulgarian
			'bn_IN', // Bengali
			'br_FR', // Breton
			'bs_BA', // Bosnian
			'ca_ES', // Catalan
			'cb_IQ', // Sorani Kurdish
			'ck_US', // Cherokee
			'co_FR', // Corsican
			'cs_CZ', // Czech
			'cx_PH', // Cebuano
			'cy_GB', // Welsh
			'da_DK', // Danish
			'de_DE', // German
			'el_GR', // Greek
			'en_GB', // English (UK)
			'en_IN', // English (India)
			'en_PI', // English (Pirate)
			'en_UD', // English (Upside Down)
			'en_US', // English (US)
			'eo_EO', // Esperanto
			'es_CL', // Spanish (Chile)
			'es_CO', // Spanish (Colombia)
			'es_ES', // Spanish (Spain)
			'es_LA', // Spanish
			'es_MX', // Spanish (Mexico)
			'es_VE', // Spanish (Venezuela)
			'et_EE', // Estonian
			'eu_ES', // Basque
			'fa_IR', // Persian
			'fb_LT', // Leet Speak
			'ff_NG', // Fulah
			'fi_FI', // Finnish
			'fo_FO', // Faroese
			'fr_CA', // French (Canada)
			'fr_FR', // French (France)
			'fy_NL', // Frisian
			'ga_IE', // Irish
			'gl_ES', // Galician
			'gn_PY', // Guarani
			'gu_IN', // Gujarati
			'gx_GR', // Classical Greek
			'ha_NG', // Hausa
			'he_IL', // Hebrew
			'hi_IN', // Hindi
			'hr_HR', // Croatian
			'hu_HU', // Hungarian
			'hy_AM', // Armenian
			'id_ID', // Indonesian
			'ig_NG', // Igbo
			'is_IS', // Icelandic
			'it_IT', // Italian
			'ja_JP', // Japanese
			'ja_KS', // Japanese (Kansai)
			'jv_ID', // Javanese
			'ka_GE', // Georgian
			'kk_KZ', // Kazakh
			'km_KH', // Khmer
			'kn_IN', // Kannada
			'ko_KR', // Korean
			'ku_TR', // Kurdish (Kurmanji)
			'ky_KG', // Kyrgyz
			'la_VA', // Latin
			'lg_UG', // Ganda
			'li_NL', // Limburgish
			'ln_CD', // Lingala
			'lo_LA', // Lao
			'lt_LT', // Lithuanian
			'lv_LV', // Latvian
			'mg_MG', // Malagasy
			'mi_NZ', // Māori
			'mk_MK', // Macedonian
			'ml_IN', // Malayalam
			'mn_MN', // Mongolian
			'mr_IN', // Marathi
			'ms_MY', // Malay
			'mt_MT', // Maltese
			'my_MM', // Burmese
			'nb_NO', // Norwegian (bokmal)
			'nd_ZW', // Ndebele
			'ne_NP', // Nepali
			'nl_BE', // Dutch (België)
			'nl_NL', // Dutch
			'nn_NO', // Norwegian (nynorsk)
			'ny_MW', // Chewa
			'or_IN', // Oriya
			'pa_IN', // Punjabi
			'pl_PL', // Polish
			'ps_AF', // Pashto
			'pt_BR', // Portuguese (Brazil)
			'pt_PT', // Portuguese (Portugal)
			'qu_PE', // Quechua
			'rm_CH', // Romansh
			'ro_RO', // Romanian
			'ru_RU', // Russian
			'rw_RW', // Kinyarwanda
			'sa_IN', // Sanskrit
			'sc_IT', // Sardinian
			'se_NO', // Northern Sámi
			'si_LK', // Sinhala
			'sk_SK', // Slovak
			'sl_SI', // Slovenian
			'sn_ZW', // Shona
			'so_SO', // Somali
			'sq_AL', // Albanian
			'sr_RS', // Serbian
			'sv_SE', // Swedish
			'sy_SY', // Swahili
			'sw_KE', // Syriac
			'sz_PL', // Silesian
			'ta_IN', // Tamil
			'te_IN', // Telugu
			'tg_TJ', // Tajik
			'th_TH', // Thai
			'tk_TM', // Turkmen
			'tl_PH', // Filipino
			'tl_ST', // Klingon
			'tr_TR', // Turkish
			'tt_RU', // Tatar
			'tz_MA', // Tamazight
			'uk_UA', // Ukrainian
			'ur_PK', // Urdu
			'uz_UZ', // Uzbek
			'vi_VN', // Vietnamese
			'wo_SN', // Wolof
			'xh_ZA', // Xhosa
			'yi_DE', // Yiddish
			'yo_NG', // Yoruba
			'zh_CN', // Simplified Chinese (China)
			'zh_HK', // Traditional Chinese (Hong Kong)
			'zh_TW', // Traditional Chinese (Taiwan)
			'zu_ZA', // Zulu
			'zz_TR', // Zazaki
		);
	}

	/**
	 * Returns Facebook locales array keys.
	 * This is apart from the fb_locales array since there are "duplicated" keys.
	 * Use this to compare the numeric key position.
	 *
	 * @see https://www.facebook.com/translations/FacebookLocales.xml
	 *
	 * @since 2.5.2
	 * @return array Valid Facebook locales
	 */
	public function language_keys() {
		return array(
			'af', // Afrikaans
			'ak', // Akan
			'am', // Amharic
			'ar', // Arabic
			'as', // Assamese
			'ay', // Aymara
			'az', // Azerbaijani
			'be', // Belarusian
			'bg', // Bulgarian
			'bn', // Bengali
			'br', // Breton
			'bs', // Bosnian
			'ca', // Catalan
			'cb', // Sorani Kurdish
			'ck', // Cherokee
			'co', // Corsican
			'cs', // Czech
			'cx', // Cebuano
			'cy', // Welsh
			'da', // Danish
			'de', // German
			'el', // Greek
			'en', // English (UK)
			'en', // English (India)
			'en', // English (Pirate)
			'en', // English (Upside Down)
			'en', // English (US)
			'eo', // Esperanto
			'es', // Spanish (Chile)
			'es', // Spanish (Colombia)
			'es', // Spanish (Spain)
			'es', // Spanish
			'es', // Spanish (Mexico)
			'es', // Spanish (Venezuela)
			'et', // Estonian
			'eu', // Basque
			'fa', // Persian
			'fb', // Leet Speak
			'ff', // Fulah
			'fi', // Finnish
			'fo', // Faroese
			'fr', // French (Canada)
			'fr', // French (France)
			'fy', // Frisian
			'ga', // Irish
			'gl', // Galician
			'gn', // Guarani
			'gu', // Gujarati
			'gx', // Classical Greek
			'ha', // Hausa
			'he', // Hebrew
			'hi', // Hindi
			'hr', // Croatian
			'hu', // Hungarian
			'hy', // Armenian
			'id', // Indonesian
			'ig', // Igbo
			'is', // Icelandic
			'it', // Italian
			'ja', // Japanese
			'ja', // Japanese (Kansai)
			'jv', // Javanese
			'ka', // Georgian
			'kk', // Kazakh
			'km', // Khmer
			'kn', // Kannada
			'ko', // Korean
			'ku', // Kurdish (Kurmanji)
			'ky', // Kyrgyz
			'la', // Latin
			'lg', // Ganda
			'li', // Limburgish
			'ln', // Lingala
			'lo', // Lao
			'lt', // Lithuanian
			'lv', // Latvian
			'mg', // Malagasy
			'mi', // Māori
			'mk', // Macedonian
			'ml', // Malayalam
			'mn', // Mongolian
			'mr', // Marathi
			'ms', // Malay
			'mt', // Maltese
			'my', // Burmese
			'nb', // Norwegian (bokmal)
			'nd', // Ndebele
			'ne', // Nepali
			'nl', // Dutch (België)
			'nl', // Dutch
			'nn', // Norwegian (nynorsk)
			'ny', // Chewa
			'or', // Oriya
			'pa', // Punjabi
			'pl', // Polish
			'ps', // Pashto
			'pt', // Portuguese (Brazil)
			'pt', // Portuguese (Portugal)
			'qu', // Quechua
			'rm', // Romansh
			'ro', // Romanian
			'ru', // Russian
			'rw', // Kinyarwanda
			'sa', // Sanskrit
			'sc', // Sardinian
			'se', // Northern Sámi
			'si', // Sinhala
			'sk', // Slovak
			'sl', // Slovenian
			'sn', // Shona
			'so', // Somali
			'sq', // Albanian
			'sr', // Serbian
			'sv', // Swedish
			'sy', // Swahili
			'sw', // Syriac
			'sz', // Silesian
			'ta', // Tamil
			'te', // Telugu
			'tg', // Tajik
			'th', // Thai
			'tk', // Turkmen
			'tl', // Filipino
			'tl', // Klingon
			'tr', // Turkish
			'tt', // Tatar
			'tz', // Tamazight
			'uk', // Ukrainian
			'ur', // Urdu
			'uz', // Uzbek
			'vi', // Vietnamese
			'wo', // Wolof
			'xh', // Xhosa
			'yi', // Yiddish
			'yo', // Yoruba
			'zh', // Simplified Chinese (China)
			'zh', // Traditional Chinese (Hong Kong)
			'zh', // Traditional Chinese (Taiwan)
			'zu', // Zulu
			'zz', // Zazaki
		);

	}

}
