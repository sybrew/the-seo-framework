<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Site_Options
 * @subpackage The_SEO_Framework\Data
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2021 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
	 * Hold the SEO Settings Page ID for this plugin.
	 *
	 * @since 2.2.2
	 * @since 2.7.0 Renamed var from page_id and made public.
	 *
	 * @var string The page ID
	 */
	public $seo_settings_page_slug = 'theseoframework-settings';

	/**
	 * Holds default site options.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Now applies filters 'the_seo_framework_default_site_options'
	 * @since 4.0.0 `home_title_location` is now switched from right to left, or vice-versa.
	 *
	 * @return array Default site options.
	 */
	public function get_default_site_options() {

		if ( \is_rtl() ) {
			$titleloc   = 'left';
			$h_titleloc = 'left';
		} else {
			$titleloc   = 'right';
			$h_titleloc = 'right';
		}

		// phpcs:disable, WordPress.Arrays.MultipleStatementAlignment -- precision alignment OK.
		/**
		 * @since 2.2.7
		 * @param array $options The default site options.
		 */
		return (array) \apply_filters(
			'the_seo_framework_default_site_options',
			[
				// General. Performance.
				'alter_search_query'  => 1, // Search query adjustments.
				'alter_archive_query' => 1, // Archive query adjustments.

				'alter_archive_query_type' => 'in_query', // Archive query type.
				'alter_search_query_type'  => 'in_query', // Search query type.

				'cache_sitemap' => 1, // Sitemap transient cache.

				// General. Layout.
				'display_seo_bar_tables'  => 1, // SEO Bar post-list tables.
				'display_seo_bar_metabox' => 0, // SEO Bar post SEO Settings.
				'seo_bar_symbols'         => 0, // SEO Bar symbolic display settings.

				'display_pixel_counter'     => 1, // Pixel counter.
				'display_character_counter' => 1, // Character counter.

				// General. Canonical.
				'canonical_scheme' => 'automatic', // Canonical URL scheme.

				// General. Timestamps.
				'timestamps_format' => '1', // Timestamp format, numeric string

				// General. Exclusions.
				'disabled_post_types' => [], // Post Type support.
				'disabled_taxonomies' => [], // Taxonomy support.

				// Title.
				'title_separator'     => 'hyphen',  // Title separator, dropdown
				'title_location'      => $titleloc, // Title separation location
				'title_rem_additions' => 0,         // Remove title additions
				'title_rem_prefixes'  => 0,         // Remove title prefixes from archives.
				'title_strip_tags'    => 1,         // Apply 'strip tags' on titles.

				// Description.
				'auto_description' => 1, // Enables auto description.

				// Robots index.
				'author_noindex' => 0, // Author Archive robots noindex
				'date_noindex'   => 1, // Date Archive robots noindex
				'search_noindex' => 1, // Search Page robots noindex
				'site_noindex'   => 0, // Site Page robots noindex
				$this->get_robots_post_type_option_id( 'noindex' ) => [
					'attachment' => 1,
				], // Post Type support.
				$this->get_robots_taxonomy_option_id( 'noindex' ) => [
					'post_format' => 1,
				], // Taxonomy support.

				// Robots follow.
				'author_nofollow' => 0, // Author Archive robots nofollow
				'date_nofollow'   => 0, // Date Archive robots nofollow
				'search_nofollow' => 0, // Search Page robots nofollow
				'site_nofollow'   => 0, // Site Page robots nofollow
				$this->get_robots_post_type_option_id( 'nofollow' ) => [], // Post Type support.
				$this->get_robots_taxonomy_option_id( 'nofollow' ) => [], // Taxonomy support.

				// Robots archive.
				'author_noarchive' => 0, // Author Archive robots noarchive
				'date_noarchive'   => 0, // Date Archive robots noarchive
				'search_noarchive' => 0, // Search Page robots noarchive
				'site_noarchive'   => 0, // Site Page robots noarchive
				$this->get_robots_post_type_option_id( 'noarchive' ) => [], // Post Type support.
				$this->get_robots_taxonomy_option_id( 'noarchive' ) => [], // Taxonomy support.

				// Robots query protection.
				'advanced_query_protection' => 1,

				// Robots pagination index.
				'paged_noindex'      => 0, // Every second or later page noindex
				'home_paged_noindex' => 0, // Every second or later homepage noindex

				// Robots copyright.
				'set_copyright_directives' => 1,          // Allow copyright directive settings.
				'max_snippet_length'       => -1,         // Max text-snippet length. -1 = unlimited, 0 = disabled, R>0 = characters.
				'max_image_preview'        => 'standard', // Max image-preview size. 'none', 'standard', 'large'.
				'max_video_preview'        => -1,         // Max video-preview size. -1 = unlimited, 0 = disabled, R>0 = seconds.

				// Robots home.
				'homepage_noindex'   => 0, // Homepage robots noindex
				'homepage_nofollow'  => 0, // Homepage robots noarchive
				'homepage_noarchive' => 0, // Homepage robots nofollow

				// Home meta.
				'homepage_title'         => '', // Homepage Title string
				'homepage_tagline'       => 1,  // Homepage add blog Tagline
				'homepage_description'   => '', // Homepage Description string
				'homepage_title_tagline' => '', // Homepage Tagline string
				'home_title_location'    => $h_titleloc, // Title separation location

				// Homepage Social.
				'homepage_og_title'            => '',
				'homepage_og_description'      => '',
				'homepage_twitter_title'       => '',
				'homepage_twitter_description' => '',

				'homepage_social_image_url' => '',
				'homepage_social_image_id'  => 0,

				// Relationships.
				'shortlink_tag'       => 0, // Adds shortlink tag
				'prev_next_posts'     => 1, // Adds next/prev tags
				'prev_next_archives'  => 1, // Adds next/prev tags
				'prev_next_frontpage' => 1, // Adds next/prev tags

				// Facebook.
				'facebook_publisher' => '', // Facebook Business URL
				'facebook_author'    => '', // Facebook User URl
				'facebook_appid'     => '', // Facebook App ID

				// Dates.
				'post_publish_time' => 1, // Article Published Time
				'post_modify_time'  => 1, // Article Modified Time

				// Twitter.
				'twitter_card'    => 'summary_large_image', // Twitter Card layout. If no twitter:image image is found, it'll change to 'summary', radio
				'twitter_site'    => '', // Twitter business @username
				'twitter_creator' => '', // Twitter user @username

				// oEmbed.
				'oembed_use_og_title'     => 0, // Use custom meta titles in oEmbeds
				'oembed_use_social_image' => 1, // Use social images in oEmbeds
				'oembed_remove_author'    => 1, // Remove author from oEmbeds

				// Social on/off.
				'og_tags'        => 1, // Output of Open Graph meta tags
				'facebook_tags'  => 1, // Output the Facebook meta tags
				'twitter_tags'   => 1, // Output the Twitter meta tags
				'oembed_scripts' => 1, // Enable WordPress's oEmbed scripts

				// Social title settings.
				'social_title_rem_additions' => 1, // Remove social title additions

				// Social image settings.
				'multi_og_image' => 0, // Allow multiple images to be generated

				// Theme color settings.
				'theme_color' => '', // Theme color metatag, default none

				// Social FallBack images (fb = fallback)
				'social_image_fb_url' => '', // Fallback image URL
				'social_image_fb_id'  => 0,  // Fallback image ID

				// Webmasters.
				'google_verification' => '', // Google Verification Code
				'bing_verification'   => '', // Bing Verification Code
				'yandex_verification' => '', // Yandex Verification Code
				'baidu_verification'  => '', // Baidu Verification Code
				'pint_verification'   => '', // Pinterest Verification Code

				// Knowledge general. https://developers.google.com/structured-data/customize/contact-points - This is extremely extended and valuable. Expect a premium version.
				'knowledge_output' => 1,              // Default for outputing the Knowledge SEO.
				'knowledge_type'   => 'organization', // Organization or Person, dropdown

				// Knowledge business. https://developers.google.com/structured-data/customize/logos
				'knowledge_logo' => 1,  // Use Knowledge Logo from anywhere.
				'knowledge_name' => '', // Person or Organization name

				// Knowledge Logo image.
				'knowledge_logo_url'   => '',
				'knowledge_logo_id'    => 0,

				// Knowledge sameas locations.
				'knowledge_facebook'   => '', // Facebook Account
				'knowledge_twitter'    => '', // Twitter Account
				'knowledge_gplus'      => '', // Google Plus Account
				'knowledge_instagram'  => '', // Instagram Account
				'knowledge_youtube'    => '', // Youtube Account
				'knowledge_linkedin'   => '', // Linkedin Account
				'knowledge_pinterest'  => '', // Pinterest Account
				'knowledge_soundcloud' => '', // SoundCloud Account
				'knowledge_tumblr'     => '', // Tumblr Account

				// Sitemaps.
				'sitemaps_output'     => 1,    // Output of sitemap
				'sitemap_query_limit' => 1000, // Sitemap post limit.

				'sitemaps_modified' => 1, // Add sitemap modified time.
				'sitemaps_priority' => 0, // Add sitemap priorities.

				'sitemaps_robots' => 1, // Add sitemap location to robots.txt

				'ping_use_cron'           => 1, // Ping using cron
				'ping_google'             => 1, // Ping Google
				'ping_bing'               => 1, // Ping Bing
				'ping_use_cron_prerender' => 0, // Sitemap cron-ping prerender

				'sitemap_styles'       => 1,        // Whether to style the sitemap
				'sitemap_logo'         => 1,        // Whether to add logo to sitemap
				'sitemap_logo_url'     => '',       // Sitemap logo URL
				'sitemap_logo_id'      => 0,        // Sitemap logo ID
				'sitemap_color_main'   => '222222', // Sitemap main color
				'sitemap_color_accent' => '00a0d2', // Sitemap accent color

				// Feed.
				'excerpt_the_feed' => 1, // Generate feed Excerpts
				'source_the_feed'  => 1, // Add backlink at the end of the feed
				'index_the_feed'   => 0, // Add backlink at the end of the feed

				// Schema.
				'ld_json_searchbox'   => 1, // LD+Json Sitelinks Searchbox
				'ld_json_breadcrumbs' => 1, // LD+Json Breadcrumbs
			]
		);
		// phpcs:enable, WordPress.Arrays.MultipleStatementAlignment
	}

	/**
	 * Holds warned site options array.
	 *
	 * @since 2.6.0
	 * @since 2.9.0 Removed all non-warned settings.
	 * @since 3.1.0 Now applies the "the_seo_framework_warned_site_options" filter.
	 * @since 4.1.0 Added robots' post type setting warnings.
	 * @since 4.1.2 Added `ping_use_cron_prerender`.
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
		 *
		 * @since 2.3.4
		 * @param array $options The warned site options.
		 */
		return (array) \apply_filters(
			'the_seo_framework_warned_site_options',
			[
				'title_rem_additions'     => 1, // Title remove additions.
				'site_noindex'            => 1, // Site Page robots noindex.
				'site_nofollow'           => 1, // Site Page robots nofollow.
				'homepage_noindex'        => 1, // Homepage robots noindex.
				'homepage_nofollow'       => 1, // Homepage robots noarchive.
				$this->get_robots_post_type_option_id( 'noindex' ) => [
					'post' => 1,
					'page' => 1,
				],
				$this->get_robots_post_type_option_id( 'nofollow' ) => [
					'post' => 1,
					'page' => 1,
				],
				'ping_use_cron_prerender' => 1, // Sitemap cron-ping prerender.
			]
		);
	}

	/**
	 * Return SEO options from the SEO options database.
	 *
	 * @since 2.2.2
	 * @since 2.8.2 No longer decodes entities on request.
	 * @since 3.1.0 Now uses the filterable call when caching is disabled.
	 * @uses THE_SEO_FRAMEWORK_SITE_OPTIONS
	 *
	 * @param string  $key       Option name.
	 * @param boolean $use_cache Optional. Whether to use the cache value or not. Defaults to true.
	 * @return mixed The value of this $key in the database. Empty string when not set.
	 */
	public function get_option( $key, $use_cache = true ) {

		if ( ! $use_cache ) {
			$options = $this->get_all_options( THE_SEO_FRAMEWORK_SITE_OPTIONS, true );
			return isset( $options[ $key ] ) ? \stripslashes_deep( $options[ $key ] ) : '';
		}

		static $cache = [];

		if ( ! isset( $cache[ THE_SEO_FRAMEWORK_SITE_OPTIONS ] ) )
			$cache[ THE_SEO_FRAMEWORK_SITE_OPTIONS ] = \stripslashes_deep( $this->get_all_options( THE_SEO_FRAMEWORK_SITE_OPTIONS ) );

		// TODO fall back to default if not registered? This means we no longer have to rely on upgrading. Or, array merge (recursive) at get_all_options?
		return isset( $cache[ THE_SEO_FRAMEWORK_SITE_OPTIONS ][ $key ] ) ? $cache[ THE_SEO_FRAMEWORK_SITE_OPTIONS ][ $key ] : '';
	}

	/**
	 * Return current option array.
	 * Memoizes the return value, can be bypassed and reset with second parameter.
	 *
	 * @since 2.6.0
	 * @since 2.9.2 Added $use_current parameter.
	 *
	 * @param string $setting The setting key.
	 * @param bool   $reset   Whether to use WordPress's version and update the cache
	 *                        or use the locally cached version.
	 * @return array Options.
	 */
	public function get_all_options( $setting = null, $reset = false ) {

		static $cache = [];

		if ( ! $reset && isset( $cache[ $setting ] ) )
			return $cache[ $setting ];

		if ( ! $setting )
			$setting = THE_SEO_FRAMEWORK_SITE_OPTIONS;

		/**
		 * @since 2.0.0
		 * @since 4.1.4 1. Now considers headlessness.
		 *              2. Now returns a 3rd parameter: boolean $headless.
		 *
		 * @param array  $settings The settings
		 * @param string $setting  The settings field.
		 * @param bool   $headless Whether the options are headless.
		 */
		return $cache[ $setting ] = \apply_filters_ref_array(
			'the_seo_framework_get_options',
			[
				$this->is_headless['settings'] && THE_SEO_FRAMEWORK_SITE_OPTIONS === $setting
					? $this->get_default_site_options()
					: \get_option( $setting ),
				$setting,
				$this->is_headless['settings'],
			]
		);
	}

	/**
	 * Return Default SEO options from the SEO options array.
	 *
	 * @since 2.2.5
	 * @uses $this->get_default_settings() Return option from the options table and cache result.
	 * @uses THE_SEO_FRAMEWORK_SITE_OPTIONS
	 *
	 * @param string  $key       Required. The option name.
	 * @param boolean $use_cache Optional. Whether to use the cache value or not.
	 * @return mixed The value of this $key in the database.
	 */
	public function get_default_option( $key, $use_cache = true ) {
		return $this->get_default_settings( $key, '', $use_cache );
	}

	/**
	 * Register the database settings for storage.
	 *
	 * @since 2.2.2
	 * @since 2.9.0 Removed reset options check, see check_options_reset().
	 * @since 3.1.0 Removed settings field existence check.
	 * @since 4.0.0 Now checks if the option exists before adding it. Shaves 20μs...
	 * @thanks StudioPress (http://www.studiopress.com/) for some code.
	 *
	 * @return void Early if settings can't be registered.
	 */
	public function register_settings() {

		\register_setting( THE_SEO_FRAMEWORK_SITE_OPTIONS, THE_SEO_FRAMEWORK_SITE_OPTIONS );
		\get_option( THE_SEO_FRAMEWORK_SITE_OPTIONS )
			or \add_option( THE_SEO_FRAMEWORK_SITE_OPTIONS, $this->get_default_site_options() );

		// Check whether the Options Reset initialization has been added.
		$this->check_options_reset();

		// Handle post-update actions. Must be initialized on admin_init and is initalized on options.php.
		if ( 'options.php' === $GLOBALS['pagenow'] )
			$this->process_settings_submission();
	}

	/**
	 * Retrieves a single caching option.
	 *
	 * @since 3.1.0
	 *
	 * @param string $key     The option key. Required.
	 * @param string $default The default cache value.
	 * @return mixed Cache value on success, $default if non-existent.
	 */
	public function get_static_cache( $key, $default = false ) {
		$cache = \get_option( THE_SEO_FRAMEWORK_SITE_CACHE, [] );
		return isset( $cache[ $key ] ) ? $cache[ $key ] : $default;
	}

	/**
	 * Updates a single caching option.
	 *
	 * Can return false if option is unchanged.
	 *
	 * @since 3.1.0
	 *
	 * @param string $key   The cache key. Required.
	 * @param string $value The cache value.
	 * @return bool True on success, false on failure.
	 */
	public function update_static_cache( $key, $value = '' ) {

		if ( ! $key ) {
			$this->_doing_it_wrong( __METHOD__, 'No valid cache key has been specified.', '3.1.0' );
			return false;
		}

		return $this->update_settings( [ $key => $value ], THE_SEO_FRAMEWORK_SITE_CACHE );
	}

	/**
	 * Checks for options reset, and reset them.
	 *
	 * @since 2.9.0
	 *
	 * @return void Early if not on SEO settings page.
	 */
	protected function check_options_reset() {

		// Check if we're already dealing with the settings. Buggy cache might interfere, otherwise.
		if ( ! $this->is_seo_settings_page( false ) || ! $this->can_access_settings() )
			return;

		if ( $this->get_option( 'tsf-settings-reset', false ) ) {
			if ( \update_option( THE_SEO_FRAMEWORK_SITE_OPTIONS, $this->get_default_site_options() ) ) {
				$this->update_static_cache( 'settings_notice', 'reset' );
			} else {
				$this->update_static_cache( 'settings_notice', 'error' );
			}
			$this->admin_redirect( $this->seo_settings_page_slug );
			exit;
		}
	}

	/**
	 * Updates a single SEO option.
	 *
	 * Can return false if option is unchanged.
	 *
	 * @since 2.9.0
	 *
	 * @param string $key   The option key.
	 * @param string $value The option value.
	 * @return bool True on success, false on failure.
	 */
	public function update_option( $key = '', $value = '' ) {

		if ( ! $key ) {
			$this->_doing_it_wrong( __METHOD__, 'No option key has been specified.', '2.9.0' );
			return false;
		}

		return $this->update_settings( [ $key => $value ] );
	}

	/**
	 * Allows bulk-updating of the SEO settings.
	 *
	 * @since 2.7.0
	 *
	 * @param string|array $new_option : {
	 *      if string: The string will act as a key for a new empty string option, e.g. : {
	 *           'sitemap_index' becomes ['sitemap_index' => '']
	 *      }
	 *      if array: The option name(s) and value(s), e.g. : {
	 *            ['sitemap_index' => 1]
	 *      }
	 * }
	 * @param string       $settings_field The Settings Field to update. Defaults
	 *                                     to The SEO Framework settings field.
	 * @return bool True on success. False on failure.
	 */
	public function update_settings( $new_option = '', $settings_field = '' ) {

		if ( ! $settings_field ) {
			$settings_field = THE_SEO_FRAMEWORK_SITE_OPTIONS;
			$this->init_sanitizer_filters();
		}

		$settings = \wp_parse_args( $new_option, \get_option( $settings_field ) );

		return \update_option( $settings_field, $settings );
	}

	/**
	 * Get the default of any of the The SEO Framework settings.
	 *
	 * @since 2.2.4
	 * @since 2.8.2 No longer decodes entities on request.
	 * @since 3.1.0 : 1. Now returns null if the option doesn't exist, instead of -1.
	 *                2. Is now influenced by filters.
	 *                3. Now also strips slashes when using cache.
	 *                4. The second parameter is deprecated.
	 * @uses $this->get_default_site_options()
	 *
	 * @param string $key       Required. The option name.
	 * @param string $depr      Deprecated. Leave empty.
	 * @param bool   $use_cache Optional. Whether to use the options cache or bypass it.
	 * @return mixed default option
	 *         null If option doesn't exist.
	 */
	public function get_default_settings( $key, $depr = '', $use_cache = true ) {

		if ( ! $key ) return false;

		if ( $depr )
			$this->_doing_it_wrong( __METHOD__, 'The second parameter is deprecated.', '3.1.0' );

		// If we need to bypass the cache
		if ( ! $use_cache ) {
			$defaults = $this->get_default_site_options();
			return isset( $defaults[ $key ] ) ? \stripslashes_deep( $defaults[ $key ] ) : null;
		}

		static $cache;

		if ( ! isset( $cache ) )
			$cache = \stripslashes_deep( $this->get_default_site_options() );

		return isset( $cache[ $key ] ) ? $cache[ $key ] : null;
	}

	/**
	 * Get the warned setting of any of the The SEO Framework settings.
	 *
	 * @since 2.3.4
	 * @since 3.1.0 Now returns 0 if the option doesn't exist, instead of -1.
	 * @uses THE_SEO_FRAMEWORK_SITE_OPTIONS
	 * @uses $this->get_warned_site_options()
	 *
	 * @param string $key       Required. The option name.
	 * @param string $depr      Deprecated. Leave empty.
	 * @param bool   $use_cache Optional. Whether to use the options cache or bypass it.
	 * @return int 0|1 Whether the option is flagged as dangerous for SEO.
	 */
	public function get_warned_settings( $key, $depr = '', $use_cache = true ) {

		if ( empty( $key ) )
			return false;

		if ( $depr )
			$this->_doing_it_wrong( __METHOD__, 'The second parameter is deprecated.', '3.1.0' );

		// If we need to bypass the cache
		if ( ! $use_cache ) {
			$warned = $this->get_warned_site_options();
			return $this->s_one_zero( ! empty( $warned[ $key ] ) );
		}

		static $cache;

		if ( ! isset( $cache ) )
			$cache = $this->get_warned_site_options();

		return $this->s_one_zero( ! empty( $cache[ $key ] ) );
	}

	/**
	 * Returns the option value for Post Type robots settings.
	 *
	 * @since 3.1.0
	 *
	 * @param string $type Accepts 'noindex', 'nofollow', 'noarchive'.
	 * @return string
	 */
	public function get_robots_post_type_option_id( $type ) {
		return $this->s_field_id( "{$type}_post_types" );
	}

	/**
	 * Returns the option value for Taxonomy robots settings.
	 *
	 * @since 4.1.0
	 *
	 * @param string $type Accepts 'noindex', 'nofollow', 'noarchive'.
	 * @return string
	 */
	public function get_robots_taxonomy_option_id( $type ) {
		return $this->s_field_id( "{$type}_taxonomies" );
	}

	/**
	 * Returns Facebook locales array values.
	 *
	 * @since 2.5.2
	 * TODO collapse this with language_keys(), ll_CC => ll?, return array_keys here, array_values there?
	 *
	 * @see https://www.facebook.com/translations/FacebookLocales.xml (deprecated)
	 * @see https://wordpress.org/support/topic/oglocale-problem/#post-11456346
	 * mirror: http://web.archive.org/web/20190601043836/https://wordpress.org/support/topic/oglocale-problem/
	 * @see $this->language_keys() for the associative array keys.
	 *
	 * @return array Valid Facebook locales
	 */
	public function fb_locales() {
		return [
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
		];
	}

	/**
	 * Returns Facebook locales' associative array keys.
	 *
	 * This is apart from the fb_locales array since there are "duplicated" keys.
	 * Use this to compare the numeric key position.
	 *
	 * @since 2.5.2
	 * @see https://www.facebook.com/translations/FacebookLocales.xml (deprecated)
	 * @see https://wordpress.org/support/topic/oglocale-problem/#post-11456346
	 * mirror: http://web.archive.org/web/20190601043836/https://wordpress.org/support/topic/oglocale-problem/
	 *
	 * @return array Valid Facebook locale keys
	 */
	public function language_keys() {
		return [
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
		];
	}
}
