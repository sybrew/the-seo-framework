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
 * Class The_SEO_Framework\Site_Options
 *
 * Handles Site Options for the plugin.
 *
 * @since 2.8.0
 */
class Site_Options extends Sanitize {

	/**
	 * Site Settings field.
	 *
	 * @since 2.2.2
	 *
	 * @var string Settings field.
	 */
	protected $settings_field;

	/**
	 * Hold the SEO Settings Page ID for this plugin.
	 *
	 * @since 2.2.2
	 * @since 2.7.0 Renamed var from page_id and made public.
	 *
	 * @var string The page ID
	 */
	public $seo_settings_page_slug;

	/**
	 * Holds the update option.
	 *
	 * @since 2.6.0
	 *
	 * @var string The Updated option name.
	 */
	protected $o_plugin_updated;

	/**
	 * Constructor, load parent constructor and set up cachable variables.
	 */
	protected function __construct() {
		parent::__construct();

		$this->settings_field = THE_SEO_FRAMEWORK_SITE_OPTIONS;
		$this->o_plugin_updated = 'updated_' . THE_SEO_FRAMEWORK_DB_VERSION;
		$this->seo_settings_page_slug = 'theseoframework-settings';

		\add_filter( "option_page_capability_{$this->settings_field}", array( $this, 'get_settings_capability' ) );
	}

	/**
	 * Holds default site options.
	 *
	 * @since 2.6.0
	 *
	 * @return array Default site options.
	 */
	public function get_default_site_options() {

		/**
		 * Switch when RTL is active;
		 * @since 2.5.0
		 */
		if ( \is_rtl() ) {
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
		return array(
			// General. Performance.
			'alter_search_query' => 1, // Search query adjustments.
			'alter_archive_query' => 1, // Archive query adjustments.

			'alter_archive_query_type' => 'in_query', // Archive query type.
			'alter_search_query_type' => 'in_query', // Search query type.

			'cache_meta_description' => 0, // Description transient cache.
			'cache_meta_schema'      => 0, // Schema.org transient cache.
			'cache_sitemap'          => 1, // Sitemap transient cache.
			'cache_object'           => 1, // Object caching.

			// General. Layout.
			'display_seo_bar_tables'  => 1, // SEO Bar post-list tables.
			'display_seo_bar_metabox' => 0, // SEO Bar post SEO Settings.

			'display_pixel_counter'     => 1, // Pixel counter.
			'display_character_counter' => 0, // Character counter.

			// General. Canonical.
			'canonical_scheme' => 'automatic', // Canonical URL scheme.

			// General. Timestamps.
			'timestamps_format'   => '1',   // Timestamp format, numeric string

			// Title.
			'title_seperator'     => 'pipe',    // Title separator (note: TYPO), dropdown
			'title_location'      => $titleloc, // Title separation location
			'title_rem_additions' => 0,         // Remove title additions
			'title_rem_prefixes'  => 0,         // Remove title prefixes

			// Description.
			'description_separator' => 'pipe', // Description separator, dropdown
			'description_additions' => 1,  // "Title on Blogname" within Description
			'description_blogname'  => 1,  // "on Blogname" within Description
		//	'description_custom'    => '', // Custom prefix TODO

			// Robots directory.
			'noydir' => 1, // Site noydir robots settings

			// Robots index.
			'category_noindex'   => 0, // Category Archive robots noindex
			'tag_noindex'        => 0, // Tag Archive robots noindex
			'author_noindex'     => 0, // Author Archive robots noindex
			'date_noindex'       => 1, // Date Archive robots noindex
			'search_noindex'     => 1, // Search Page robots noindex
			'attachment_noindex' => 1, // Attachment Pages robots noindex
			'site_noindex'       => 0, // Site Page robots noindex

			// Robots follow.
			'category_nofollow'   => 0, // Category Archive robots nofollow
			'tag_nofollow'        => 0, // Tag Archive robots nofollow
			'author_nofollow'     => 0, // Author Archive robots nofollow
			'date_nofollow'       => 0, // Date Archive robots nofollow
			'search_nofollow'     => 0, // Search Page robots nofollow
			'attachment_nofollow' => 0, // Attachment Pages robots noindex
			'site_nofollow'       => 0, // Site Page robots nofollow

			// Robots archive.
			'category_noarchive'   => 0, // Category Archive robots noarchive
			'tag_noarchive'        => 0, // Tag Archive robots noarchive
			'author_noarchive'     => 0, // Author Archive robots noarchive
			'date_noarchive'       => 0, // Date Archive robots noarchive
			'search_noarchive'     => 0, // Search Page robots noarchive
			'attachment_noarchive' => 0, // Attachment Page robots noarchive
			'site_noarchive'       => 0, // Site Page robots noarchive

			// Robots pagination index.
			'paged_noindex'      => 1, // Every second or later page noindex
			'home_paged_noindex' => 0, // Every second or later homepage noindex

			// Robots home.
			'homepage_noindex'   => 0, // Home Page robots noindex
			'homepage_nofollow'  => 0, // Home Page robots noarchive
			'homepage_noarchive' => 0, // Home Page robots nofollow

			// Home meta.
			'homepage_title'         => '', // Home Page Title string
			'homepage_tagline'       => 1,  // Home Page add blog Tagline
			'homepage_description'   => '', // Home Page Description string
			'homepage_title_tagline' => '', // Home Page Tagline string
			'home_title_location'    => $h_titleloc, // Title separation location

			// Homepage Social FallBack image
			'homepage_social_image_url' => '',
			'homepage_social_image_id'  => 0,

			// Relationships
			'shortlink_tag'       => 0, // Adds shortlink tag
			'prev_next_posts'     => 0, // Adds next/prev tags
			'prev_next_archives'  => 1, // Adds next/prev tags
			'prev_next_frontpage' => 1, // Adds next/prev tags

			// Facebook.
			'facebook_publisher' => '',	// Facebook Business Url
			'facebook_author'    => '',	// Facebook User URl
			'facebook_appid'     => '',	// Facebook App ID

			// Dates.
			'post_publish_time' => 1, // Article Published Time
			'post_modify_time'  => 1, // Article Modified Time

			// Twitter.
			'twitter_card'    => 'summary_large_image', // Twitter Card layout. If no twitter:image image is found, it'll change to 'summary', radio
			'twitter_site'    => '', // Twitter business @username
			'twitter_creator' => '', // Twitter user @username

			// Social on/off.
			'og_tags'         => 1, // Output of Open Graph meta tags
			'facebook_tags'   => 1, // Output the Facebook meta tags
			'twitter_tags'    => 1, // Output the Twitter meta tags
		//	'googleplus_tags' => 1, // Output the Google+ meta tags

			// Social FallBack images (fb = fallback)
			'social_image_fb_url'   => '', // Fallback image URL
			'social_image_fb_id'    => 0, // Fallback image ID

			// Webmasters.
			'google_verification' => '', // Google Verification Code
			'bing_verification'   => '', // Bing Verification Code
			'yandex_verification' => '', // Yandex Verification Code
			'pint_verification'   => '', // Pinterest Verification Code

			// Knowledge general. https://developers.google.com/structured-data/customize/contact-points - This is extremely extended and valuable. Expect a premium version.
			'knowledge_output' => 1,              // Default for outputing the Knowledge SEO.
			'knowledge_type'   => 'organization', // Organization or Person, dropdown

			// Knowledge business. https://developers.google.com/structured-data/customize/logos
			'knowledge_logo' => 1,  // Use Knowledge Logo from anywhere.
			'knowledge_name' => '', // Person or Organization name

			// Knowledge Logo image
			'knowledge_logo_url'   => '',
			'knowledge_logo_id'    => 0,

			// Knowledge sameas locations
			'knowledge_facebook'   => '', // Facebook Account
			'knowledge_twitter'    => '', // Twitter Account
			'knowledge_gplus'      => '', // Google Plus Account
			'knowledge_instagram'  => '', // Instagram Account
			'knowledge_youtube'    => '', // Youtube Account
			'knowledge_linkedin'   => '', // Linkedin Account
		//	'knowledge_myspace'    => '', // MySpace Account // meh.
			'knowledge_pinterest'  => '', // Pinterest Account
			'knowledge_soundcloud' => '', // SoundCloud Account
			'knowledge_tumblr'     => '', // Tumblr Account

			// Sitemaps.
			'sitemaps_output'      => 1,   // Output of sitemaps
			'sitemaps_modified'    => 1,   // Add sitemaps modified time
			'sitemaps_robots'      => 1,   // Add sitemaps location to robots.txt
			'ping_google'          => 1,   // Ping Google
			'ping_bing'            => 1,   // Ping Bing
			'ping_yandex'          => 1,   // Ping Yandex
			'sitemap_styles'       => 1,   // Whether to style the sitemap
			'sitemap_logo'         => 1,   // Whether to add logo to sitemap
			'sitemap_color_main'   => '333',    // Sitemap main color
			'sitemap_color_accent' => '00cd98', // Sitemap accent color

			// Feed.
			'excerpt_the_feed' => 1, // Generate feed Excerpts
			'source_the_feed'  => 1, // Add backlink at the end of the feed

			// Schema
			'ld_json_searchbox'   => 1, // LD+Json Sitelinks Searchbox
			'ld_json_breadcrumbs' => 1, // LD+Json Breadcrumbs

			// Cache.
			$this->o_plugin_updated => 1, // Plugin update cache.
		);
	}

	/**
	 * Holds warned site options array.
	 *
	 * @since 2.6.0
	 * @since 2.9.0 Removed all non-warned settings.
	 *
	 * @return array $options.
	 */
	public function get_warned_site_options() {
		/**
		 * Warned site settings. Only accepts checkbox options.
		 * When listed as 1, it's a feature which can destroy your website's SEO value when checked.
		 *
		 * Unchecking a box is simply "I'm not active." - Removing features generally do not negatively impact SEO value.
		 * Since it's all about the content.
		 *
		 * Only used within the SEO Settings page.
		 */
		return array(
			'title_rem_additions' => 1, // Title remove additions.
			'site_noindex' => 1, // Site Page robots noindex
			'site_nofollow' => 1, // Site Page robots nofollow
			'homepage_noindex'  => 1, // Home Page robots noindex
			'homepage_nofollow' => 1, // Home Page robots noarchive
		);
	}

	/**
	 * Updates special hidden values to default on settings save.
	 *
	 * @since 2.6.0
	 * @securitycheck 3.0.0 OK.
	 * @TODO REMOVE THIS and use a better upgrade handler. Source for code debt.
	 */
	protected function update_hidden_options_to_default() {

		if ( false === $this->verify_seo_settings_nonce() )
			return;

		//* Disables the New SEO Settings Updated notification.
		$plugin_updated = $this->o_plugin_updated;
		$_POST[ THE_SEO_FRAMEWORK_SITE_OPTIONS ][ $plugin_updated ] = 1;

	}

	/**
	 * Updates option from default options at plugin update.
	 *
	 * @since 2.6.0
	 * @since 2.9.0 Added excluded options check.
	 * @access private
	 *
	 * @return void early if already has been updated.
	 */
	public function site_updated_plugin_option() {

		if ( false === $this->is_admin() )
			return;

		/**
		 * Applies filters 'the_seo_framework_update_options_at_update' : bool
		 * @since 2.6.0
		 */
		if ( ! \apply_filters( 'the_seo_framework_update_options_at_update', true ) )
			return;

		$plugin_updated = $this->o_plugin_updated;

		/**
		 * Prevent this function from running more than once after update.
		 * Also prevent running if no settings field is found.
		 */
		if ( $this->get_option( $plugin_updated ) || empty( $this->settings_field ) )
			return;

		//* If current user isn't allowed to update options, don't do anything.
		if ( ! $this->can_access_settings() )
			return;

		$updated = false;
		$options = $this->get_all_options();
		$new_options = $this->default_site_options();

		/**
		 * Stop this madness from happening again until next update.
		 * Also prevent $updated to become true on this call.
		 */
		$new_options[ $plugin_updated ] = 1;
		$options[ $plugin_updated ] = 1;

		$plausible_missing_options = array(
			'cache_meta_description',
			'cache_meta_schema',
			'cache_sitemap',
			'cache_object',
			'display_seo_bar_tables',
			'display_seo_bar_metabox',
		);

		//* Merge the options. Add to if it's non-existent.
		foreach ( $new_options as $key => $value ) {
			if ( ! isset( $options[ $key ] ) ) {
				if ( in_array( $key, $plausible_missing_options, true ) ) {
					$options[ $key ] = 0;
				} else {
					$options[ $key ] = $value;
				}

				if ( ! empty( $value ) )
					$updated = true;
			}
		}

		//* Updated the options. Check for updated flag and see if settings pages are loaded.
		if ( \update_option( $this->settings_field, $options ) && $updated && $this->load_options )
			$this->pre_output_site_updated_plugin_notice();

	}

	/**
	 * Determine whether to output update notice directly or on refresh.
	 * Run before headers are sent.
	 *
	 * @since 2.6.0
	 * @securitycheck 3.0.3 OK.
	 */
	protected function pre_output_site_updated_plugin_notice() {

		/**
		 * Security check:
		 * Only checks for extra parameters. Then redirects further to only output
		 * notice. User capability is checked beforehand.
		 */
		if ( $this->can_access_settings() && $this->is_seo_settings_page( false ) ) {
			//* Redirect to current page if on options page to correct option values. Once.
			if ( ! isset( $_REQUEST['tsf-settings-updated'] ) || 'true' !== $_REQUEST['tsf-settings-updated'] )
				$this->admin_redirect( $this->seo_settings_page_slug, array( 'tsf-settings-updated' => 'true' ) );

			//* Notice has already been sent.
			return;
		}

		//* Make sure this plugin's scripts are being loaded.
		$this->init_admin_scripts();

		//* Output notice.
		\add_action( 'admin_notices', array( $this, 'do_settings_updated_notice' ) );

	}

	/**
	 * Echos plugin updated notification.
	 *
	 * @since 2.6.0
	 *
	 * @access private
	 */
	public function do_settings_updated_notice() {

		$settings_url = $this->seo_settings_page_url();
		$link = sprintf( '<a href="%s" title="%s" target="_self">%s</a>', $settings_url, \esc_attr__( 'SEO Settings', 'autodescription' ), \esc_html__( 'here', 'autodescription' ) );
		$go_to_page = sprintf( \esc_html_x( 'View the new options %s.', '%s = here', 'autodescription' ), $link );

		$notice = $this->page_defaults['plugin_update_text'] . ' ' . $go_to_page;

		//* Already escaped.
		$this->do_dismissible_notice( $notice, 'updated', true, false );

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
	 * @return mixed The value of this $key in the database.
	 */
	public function get_option( $key, $use_cache = true ) {
		return $this->the_seo_framework_get_option( $key, THE_SEO_FRAMEWORK_SITE_OPTIONS, $use_cache );
	}

	/**
	 * Return current option array.
	 *
	 * @since 2.6.0
	 * @since 2.9.2 Added $use_current parameter.
	 * @staticvar array $cache The option cache.
	 *
	 * @param string $setting The setting key.
	 * @param bool $use_current Whether to use WordPress' version and update the cache
	 *             or use locally the cached version.
	 * @return array Options.
	 */
	public function get_all_options( $setting = null, $use_current = false ) {

		static $cache = array();

		if ( ! $use_current && isset( $cache[ $setting ] ) )
			return $cache[ $setting ];

		if ( is_null( $setting ) )
			$setting = THE_SEO_FRAMEWORK_SITE_OPTIONS;

		/**
		 * Applies filters 'the_seo_framework_get_options' : boolean
		 * @since 2.0.0
		 */
		return $cache[ $setting ] = \apply_filters( 'the_seo_framework_get_options', \get_option( $setting ), $setting );
	}

	/**
	 * Return option from the options table and cache result.
	 *
	 * Values pulled from the database are cached on each request, so a second request for the same value won't cause a
	 * second DB interaction.
	 *
	 * @since 2.0.0
	 * @since 2.8.2 : No longer decodes entities on request.
	 * @staticvar array $settings_cache
	 * @staticvar array $options_cache
	 * @thanks StudioPress (http://www.studiopress.com/) for some code.
	 *
	 * @param string  $key        Option name.
	 * @param string  $setting    Optional. Settings field name. Eventually defaults to null if not passed as an argument.
	 * @param boolean $use_cache  Optional. Whether to use the cache value or not. Default is true.
	 * @return mixed The value of this $key in the database.
	 */
	public function the_seo_framework_get_option( $key, $setting = null, $use_cache = true ) {

		//* If we need to bypass the cache
		if ( ! $use_cache ) {
			$options = \get_option( $setting );

			if ( ! is_array( $options ) || ! array_key_exists( $key, $options ) )
				return '';

			return is_array( $options[ $key ] ) ? \stripslashes_deep( $options[ $key ] ) : stripslashes( $options[ $key ] );
		}

		//* Setup caches
		static $options_cache = array();

		//* Check options cache
		if ( isset( $options_cache[ $setting ][ $key ] ) )
			//* Option has been cached
			return $options_cache[ $setting ][ $key ];

		$options = $this->get_all_options( $setting );

		//* Check for non-existent option
		if ( ! is_array( $options ) || ! array_key_exists( $key, (array) $options ) ) {
			//* Cache non-existent option
			$options_cache[ $setting ][ $key ] = '';
		} else {
			//* Option has not been previously been cached, so cache now
			$options_cache[ $setting ][ $key ] = is_array( $options[ $key ] ) ? \stripslashes_deep( $options[ $key ] ) : stripslashes( $options[ $key ] );
		}

		return $options_cache[ $setting ][ $key ];
	}

	/**
	 * Return SEO options from the SEO options database.
	 *
	 * @since 2.2.2
	 * @uses $this->the_seo_framework_get_option() Return option from the options table and cache result.
	 * @uses THE_SEO_FRAMEWORK_NETWORK_OPTIONS
	 *
	 * Unused.
	 * @todo deprecate.
	 *
	 * @param string  $key       Option name.
	 * @param boolean $use_cache Optional. Whether to use the cache value or not. Defaults to true.
	 * @return mixed The value of this $key in the database.
	 */
	public function get_site_option( $key, $use_cache = true ) {
		return $this->the_seo_framework_get_option( $key, THE_SEO_FRAMEWORK_NETWORK_OPTIONS, $use_cache );
	}

	/**
	 * Return Default SEO options from the SEO options array.
	 *
	 * @since 2.2.5
	 * @uses $this->get_default_settings() Return option from the options table and cache result.
	 * @uses THE_SEO_FRAMEWORK_SITE_OPTIONS
	 *
	 * @param string  $key       Option name.
	 * @param boolean $use_cache Optional. Whether to use the cache value or not. Defaults to true.
	 * @return mixed The value of this $key in the database.
	 */
	public function get_default_option( $key, $use_cache = true ) {
		return $this->get_default_settings( $key, THE_SEO_FRAMEWORK_SITE_OPTIONS, $use_cache );
	}

	/**
	 * Return the parsed default options array.
	 *
	 * @since 2.2.7
	 *
	 * @param array $args Additional default options to filter.
	 * @return array The SEO Framework Options
	 */
	protected function default_site_options( $args = array() ) {
		/**
		 * Applies filters the_seo_framework_default_site_options : array
		 * @since 2.2.7
		 */
		return \wp_parse_args(
			$args,
			\apply_filters(
				'the_seo_framework_default_site_options',
				\wp_parse_args(
					$args,
					$this->get_default_site_options()
				)
			)
		);
	}

	/**
	 * Return the Warned site options. Options which should be 'avoided' return true.
	 *
	 * @param array $args Additional warned options to filter.
	 * @return array The SEO Framework Warned Options
	 */
	protected function warned_site_options( $args = array() ) {
		/**
		 * Applies filters the_seo_framework_warned_site_options : array
		 * @since 2.3.4
		 */
		return \wp_parse_args(
			$args,
			\apply_filters(
				'the_seo_framework_warned_site_options',
				\wp_parse_args(
					$args,
					$this->get_warned_site_options()
				)
			)
		);
	}

	/**
	 * Register the database settings for storage.
	 *
	 * @since 2.2.2
	 * @since 2.9.0 Removed reset options check, see check_options_reset().
	 * @thanks StudioPress (http://www.studiopress.com/) for some code.
	 *
	 * @return void Early if settings can't be registered.
	 */
	public function register_settings() {

		//* If the settings field doesn't exist, we can't register it.
		if ( ! $this->settings_field )
			return;

		\register_setting( $this->settings_field, $this->settings_field );
		\add_option( $this->settings_field, $this->default_site_options() );

		//* Check whether the Options Reset initialization has been added.
		$this->check_options_reset();
	}

	/**
	 * Checks for options reset, and reset them.
	 *
	 * @since 2.9.0
	 *
	 * @return void Early if not on SEO settings page.
	 */
	protected function check_options_reset() {

		/**
		 * Security check:
		 * Further checks are based on previously set options.
		 * These can only be set when one has access to the Settings Page or database.
		 * Also checks for capabilities.
		 */
		if ( ! $this->can_access_settings() || ! $this->is_seo_settings_page( false ) )
			return;

		if ( $this->get_option( 'tsf-settings-reset', false ) ) {
			if ( \update_option( $this->settings_field, $this->default_site_options() ) ) {
				$this->admin_redirect( $this->seo_settings_page_slug, array( 'tsf-settings-reset' => 'true' ) );
				exit;
			} else {
				$this->admin_redirect( $this->seo_settings_page_slug, array( 'error' => 'true' ) );
				exit;
			}
		}
	}

	/**
	 * Updates a single option.
	 *
	 * Can return false if option is unchanged.
	 *
	 * @since 2.9.0
	 *
	 * @param string $key The option key.
	 * @param string $vlaue The option value.
	 * @return bool True on success, false on failure.
	 */
	public function update_option( $key = '', $value = '' ) {

		if ( ! $key ) {
			$this->_doing_it_wrong( __METHOD__, 'No option key has been specified.', '2.9.0' );
			return false;
		}

		return $this->update_settings( array( $key => $value ) );
	}

	/**
	 * Allows updating of settings.
	 *
	 * @since 2.7.0
	 *
	 * @param string|array $new_option {
	 *      if string: The string will act as a key for a new empty string option, e.g. : {
	 *           'sitemap_index' becomes ['sitemap_index' => '']
	 *      }
	 *      if array: The option name(s) and value(s), e.g. : {
	 *            ['sitemap_index' => 1]
	 *      }
	 * }
	 * @param string $settings_field The Settings Field to update. Defaults
	 *               to The SEO Framework settings field.
	 * @return bool True on success. False on failure.
	 */
	public function update_settings( $new = '', $settings_field = '' ) {

		if ( empty( $settings_field ) ) {
			$settings_field = $this->settings_field;
			$this->init_sanitizer_filters();
		}

		$old = \get_option( $settings_field );
		$settings = \wp_parse_args( $new, $old );

		return \update_option( $settings_field, $settings );
	}

	/**
	 * Get the default of any of the The SEO Framework settings.
	 *
	 * @since 2.2.4
	 * @since 2.8.2 : No longer decodes entities on request.
	 * @staticvar array $defaults_cache
	 * @uses $this->settings_field
	 * @uses $this->default_site_options()
	 *
	 * @param string $key required The option name
	 * @param string $setting optional The settings field
	 * @param bool $use_cache optional Use the options cache or not. For debugging purposes.
	 * @return int|bool|string default option
	 *         int '-1' if option doesn't exist.
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

			return is_array( $defaults[ $key ] ) ? \stripslashes_deep( $defaults[ $key ] ) : stripslashes( $defaults[ $key ] );
		}

		static $defaults_cache = array();

		//* Check options cache
		if ( isset( $defaults_cache[ $key ] ) )
			//* Option has been cached
			return $defaults_cache[ $key ];

		$defaults_cache = $this->default_site_options();

		if ( ! is_array( $defaults_cache ) || ! array_key_exists( $key, (array) $defaults_cache ) )
			$defaults_cache[ $key ] = -1;

		return $defaults_cache[ $key ];
	}

	/**
	 * Get the warned setting of any of the The SEO Framework settings.
	 *
	 * @since 2.3.4
	 * @staticvar array $warned_cache
	 * @uses $this->settings_field
	 * @uses $this->warned_site_options()
	 *
	 * @param string $key required The option name
	 * @param string $setting optional The settings field
	 * @param bool $use_cache optional Use the options cache or not. For debugging purposes.
	 * @return int 0|1 Whether the option is flagged as dangerous for SEO.
	 *         int '-1' if option doesn't exist.
	 */
	public function get_warned_settings( $key, $setting = '', $use_cache = true ) {

		if ( empty( $key ) )
			return false;

		//* Fetch default settings if it's not set.
		if ( empty( $setting ) )
			$setting = $this->settings_field;

		//* If we need to bypass the cache
		if ( ! $use_cache ) {
			$warned = $this->warned_site_options();

			if ( ! is_array( $warned ) || ! array_key_exists( $key, $warned ) )
				return -1;

			return $this->s_one_zero( $warned[ $key ] );
		}

		static $warned_cache = array();

		//* Check options cache
		if ( isset( $warned_cache[ $key ] ) )
			//* Option has been cached
			return $warned_cache[ $key ];

		$warned_options = $this->warned_site_options();

		if ( ! array_key_exists( $key, (array) $warned_options ) ) {
			$warned_cache[ $key ] = 0;
		} else {
			$warned_cache[ $key ] = $this->s_one_zero( $warned_options[ $key ] );
		}

		return $warned_cache[ $key ];
	}

	/**
	 * Returns Facebook locales array values.
	 *
	 * @since 2.5.2
	 * @see https://www.facebook.com/translations/FacebookLocales.xml
	 * @see $this->language_keys() for the associative array keys.
	 *
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
	 * Returns Facebook locales' associative array keys.
	 *
	 * This is apart from the fb_locales array since there are "duplicated" keys.
	 * Use this to compare the numeric key position.
	 *
	 * @since 2.5.2
	 * @see https://www.facebook.com/translations/FacebookLocales.xml
	 *
	 * @return array Valid Facebook locale keys
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
