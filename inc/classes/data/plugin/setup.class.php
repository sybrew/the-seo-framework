<?php
/**
 * @package The_SEO_Framework\Classes\Data\Plugin\Setup
 * @subpackage The_SEO_Framework\Data\Plugin\Settings
 */

namespace The_SEO_Framework\Data\Plugin;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\{
	Data,
	Traits\Property_Refresher,
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
 * Holds a collection of default data interface methods for TSF.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->data()->plugin()->setup() instead.
 */
class Setup {
	use Property_Refresher;

	/**
	 * @since 5.0.0
	 * @var array Holds all default options.
	 */
	private static $default_options;

	/**
	 * @since 5.0.0
	 * @var array Holds all warned options.
	 */
	private static $warned_options;

	/**
	 * Resets the options and flushes all pertaining caches for the current request.
	 *
	 * @since 5.0.0
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function reset_options() {

		$success = \update_option( \THE_SEO_FRAMEWORK_SITE_OPTIONS, static::get_default_options(), true );

		if ( $success )
			Data\Plugin::refresh_static_properties();

		return $success;
	}

	/**
	 * Returns selected default option. Null on failure.
	 *
	 * @since 2.2.5
	 * @since 4.2.0 1. Now supports an option index as `$key`.
	 *              2. Removed second parameter (`$use_cache`).
	 *              3. Now always memoizes.
	 * @since 5.0.0 1. $key is now variadic. Additional variables allow you to dig deeper in the cache.
	 *              2. Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param string ...$key Option name. Additional parameters will try get subvalues of the array.
	 *                       When empty, it'll return all options. You should use get_default_options() instead.
	 * @return mixed The default option. Null if it's not registered.
	 */
	public static function get_default_option( ...$key ) {

		$default = static::$default_options ?? static::get_default_options();

		foreach ( $key as $k )
			$default = $default[ $k ] ?? null;

		return $default;
	}

	/**
	 * Return Warned SEO options from the SEO options array.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 1. $key is now variadic. Additional variables allow you to dig deeper in the cache.
	 *              2. Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param string ...$key Option name. Additional parameters will try get subvalues of the array.
	 *                       When empty, it'll return all options. You should use get_warned_options() instead.
	 * @return bool True if warning is registered. False otherwise.
	 */
	public static function get_warned_option( ...$key ) {

		$warned = static::$warned_options ?? static::get_warned_options();

		foreach ( $key as $k )
			$warned = $warned[ $k ] ?? null;

		return $warned;
	}

	/**
	 * Holds default site options.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Now applies filters 'the_seo_framework_default_site_options'
	 * @since 4.0.0 `home_title_location` is now switched from right to left, or vice-versa.
	 * @since 4.2.4 `max_image_preview` now defaults to `large`, from `standard`, matching WordPress's default.
	 * @since 4.2.7 Added `auto_description_html_method`, defaults to `fast`.
	 *
	 * @return array Default site options.
	 */
	public static function get_default_options() {

		if ( isset( static::$default_options ) )
			return static::$default_options;

		static::register_automated_refresh( 'default_options' );

		$titleloc = \is_rtl() ? 'left' : 'right';

		// phpcs:disable, WordPress.Arrays.MultipleStatementAlignment -- precision alignment OK.
		/**
		 * @since 2.2.7
		 * @param array $options The default site options.
		 */
		return static::$default_options = (array) \apply_filters(
			'the_seo_framework_default_site_options',
			[
				// General. Performance.
				'alter_search_query'  => 1, // Search query adjustments.
				'alter_archive_query' => 1, // Archive query adjustments.

				'alter_archive_query_type' => 'in_query', // Archive query type.
				'alter_search_query_type'  => 'in_query', // Search query type.

				// General. Layout.
				'display_seo_bar_tables'  => 1, // SEO Bar post-list tables.
				'display_seo_bar_metabox' => 0, // SEO Bar post SEO Settings.
				'seo_bar_low_contrast'    => 0, // SEO Bar contrast display settings.
				'seo_bar_symbols'         => 0, // SEO Bar symbol display settings.

				'display_pixel_counter'     => 1, // Pixel counter.
				'display_character_counter' => 1, // Character counter.

				// General. Canonical.
				'canonical_scheme' => 'automatic', // Canonical URL scheme.

				// General. Timestamps.
				'timestamps_format' => 1, // Timestamp format, numeric string.

				// General. Exclusions.
				'disabled_post_types' => [], // Post Type support.
				'disabled_taxonomies' => [], // Taxonomy support.

				// Title.
				'site_title'          => '',        // Blog name.
				'title_separator'     => 'hyphen',  // Title separator, radio selection.
				'title_location'      => $titleloc, // Title separation location.
				'title_rem_additions' => 0,         // Remove title additions.
				'title_rem_prefixes'  => 0,         // Remove title prefixes from archives.
				'title_strip_tags'    => 1,         // Apply 'strip tags' on titles.

				// Description.
				'auto_description'             => 1, // Enables auto description.
				'auto_description_html_method' => 'fast', // Auto description HTML passes.

				// Robots index.
				'author_noindex' => 0, // Author Archive robots noindex.
				'date_noindex'   => 1, // Date Archive robots noindex.
				'search_noindex' => 1, // Search Page robots noindex.
				'site_noindex'   => 0, // Site Page robots noindex.
				Helper::get_robots_option_index( 'post_type', 'noindex' ) => [
					'attachment' => 1,
				], // Post Type support.
				Helper::get_robots_option_index( 'taxonomy', 'noindex' ) => [
					'post_format' => 1,
				], // Taxonomy support.

				// Robots follow.
				'author_nofollow' => 0, // Author Archive robots nofollow.
				'date_nofollow'   => 0, // Date Archive robots nofollow.
				'search_nofollow' => 0, // Search Page robots nofollow.
				'site_nofollow'   => 0, // Site Page robots nofollow.
				Helper::get_robots_option_index( 'post_type', 'nofollow' ) => [], // Post Type support.
				Helper::get_robots_option_index( 'taxonomy', 'nofollow' ) => [], // Taxonomy support.

				// Robots archive.
				'author_noarchive' => 0, // Author Archive robots noarchive.
				'date_noarchive'   => 0, // Date Archive robots noarchive.
				'search_noarchive' => 0, // Search Page robots noarchive.
				'site_noarchive'   => 0, // Site Page robots noarchive.
				Helper::get_robots_option_index( 'post_type', 'noarchive' ) => [], // Post Type support.
				Helper::get_robots_option_index( 'taxonomy', 'noarchive' ) => [], // Taxonomy support.

				// Robots query protection.
				'advanced_query_protection' => 1,

				// Robots pagination index.
				'paged_noindex'      => 0, // Every second or later page noindex.
				'home_paged_noindex' => 0, // Every second or later homepage noindex.

				// Robots copyright.
				'set_copyright_directives' => 1,       // Allow copyright directive settings.
				'max_snippet_length'       => -1,      // Max text-snippet length. -1 = unlimited, 0 = disabled, R>0 = characters.
				'max_image_preview'        => 'large', // Max image-preview size. 'none', 'standard', 'large'.
				'max_video_preview'        => -1,      // Max video-preview size. -1 = unlimited, 0 = disabled, R>0 = seconds.

				// Robots.txt blocks.
				'robotstxt_block_ai'  => 0, // Blocks large learning models from training on the site content.
				'robotstxt_block_seo' => 0, // Block SEO crawlers like Ahrefs, Moz, and SEMRush.

				// Homepage visibility.
				'homepage_noindex'   => 0, // Homepage robots noindex.
				'homepage_nofollow'  => 0, // Homepage robots noarchive.
				'homepage_noarchive' => 0, // Homepage robots nofollow.

				'homepage_canonical' => '', // Homepage canonical URL.
				'homepage_redirect'  => '', // Homepage redirect URL.

				// Homepage meta.
				'homepage_title'         => '', // Homepage Title string.
				'homepage_tagline'       => 1,  // Homepage add blog Tagline.
				'homepage_description'   => '', // Homepage Description string.
				'homepage_title_tagline' => '', // Homepage Tagline string.
				'home_title_location'    => $titleloc, // Title separation location.

				// Homepage Social.
				'homepage_og_title'            => '',
				'homepage_og_description'      => '',
				'homepage_twitter_card_type'   => '',
				'homepage_twitter_title'       => '',
				'homepage_twitter_description' => '',

				'homepage_social_image_url' => '',
				'homepage_social_image_id'  => 0,

				// Post Type Archives. Prefill all of it for easy filtering, even though it's dynamically populated.
				'pta' => Data\Plugin\PTA::get_all_default_meta(),

				// Relationships.
				'shortlink_tag'       => 0, // Adds shortlink tag.
				'prev_next_posts'     => 1, // Adds next/prev tags.
				'prev_next_archives'  => 1, // Adds next/prev tags.
				'prev_next_frontpage' => 1, // Adds next/prev tags.

				// Facebook.
				'facebook_publisher' => '', // Facebook Business URL.
				'facebook_author'    => '', // Facebook User URL.

				// Dates.
				'post_publish_time' => 1, // Article Published Time.
				'post_modify_time'  => 1, // Article Modified Time.

				// Twitter.
				'twitter_card'    => 'summary_large_image', // Twitter Card layout. If no twitter:image image is found, it'll change to 'summary', radio
				'twitter_site'    => '', // Twitter business @username.
				'twitter_creator' => '', // Twitter user @username.

				// oEmbed.
				'oembed_use_og_title'     => 0, // Use custom meta titles in oEmbeds.
				'oembed_use_social_image' => 1, // Use social images in oEmbeds.
				'oembed_remove_author'    => 1, // Remove author from oEmbeds.

				// Social on/off.
				'og_tags'        => 1, // Output of Open Graph meta tags.
				'facebook_tags'  => 1, // Output the Facebook meta tags.
				'twitter_tags'   => 1, // Output the Twitter meta tags.
				'oembed_scripts' => 1, // Enable WordPress's oEmbed scripts.

				// Social title settings.
				'social_title_rem_additions' => 1, // Remove social title additions.

				// Social image settings.
				'multi_og_image' => 0, // Allow multiple images to be generated.

				// Theme color settings.
				'theme_color' => '', // Theme color metatag, default none.

				// Social FallBack images (fb = fallback)
				'social_image_fb_url' => '', // Fallback image URL.
				'social_image_fb_id'  => 0,  // Fallback image ID.

				// Webmasters.
				'google_verification' => '', // Google Verification Code.
				'bing_verification'   => '', // Bing Verification Code.
				'yandex_verification' => '', // Yandex Verification Code.
				'baidu_verification'  => '', // Baidu Verification Code.
				'pint_verification'   => '', // Pinterest Verification Code.

				// Schema.org.
				'ld_json_enabled'        => 1, // LD+Json toggle for Schema.
				'ld_json_searchbox'      => 1, // LD+Json Sitelinks Search Box.
				'ld_json_breadcrumbs'    => 1, // LD+Json Breadcrumbs.
				'knowledge_output'       => 1, // Default for outputting the Knowledge SEO.

				// Knowledge general <https://developers.google.com/structured-data/customize/contact-points> - This is extremely extended and valuable. Expect a premium version.
				'knowledge_type'   => 'organization', // Organization or Person, dropdown.

				// Knowledge business <https://developers.google.com/structured-data/customize/logos>.
				'knowledge_logo' => 1,  // Use Knowledge Logo from anywhere.
				'knowledge_name' => '', // Person or Organization name.

				// Knowledge Logo image.
				'knowledge_logo_url'   => '',
				'knowledge_logo_id'    => 0,

				// Knowledge sameas locations. TODO: Make this dynamic.
				'knowledge_facebook'   => '', // Facebook Account.
				'knowledge_twitter'    => '', // Twitter Account.
				'knowledge_instagram'  => '', // Instagram Account.
				'knowledge_youtube'    => '', // Youtube Account.
				'knowledge_linkedin'   => '', // Linkedin Account.
				'knowledge_pinterest'  => '', // Pinterest Account.
				'knowledge_soundcloud' => '', // SoundCloud Account.
				'knowledge_tumblr'     => '', // Tumblr Account.

				// Sitemaps.
				'sitemaps_output'         => 1,    // Output of sitemap.
				'sitemap_query_limit'     => 250, // Sitemap post limit.
				'cache_sitemap'           => 1, // Sitemap transient cache.
				'sitemap_cron_prerender'  => 0, // Sitemap cron-ping prerender.

				'sitemaps_modified' => 1, // Add sitemap modified time.

				'sitemaps_robots' => 1, // Add sitemap location to robots.txt.

				'sitemap_styles'       => 1,        // Whether to style the sitemap.
				'sitemap_logo'         => 1,        // Whether to add logo to sitemap.
				'sitemap_logo_url'     => '',       // Sitemap logo URL.
				'sitemap_logo_id'      => 0,        // Sitemap logo ID.
				'sitemap_color_main'   => '222222', // Sitemap main color.
				'sitemap_color_accent' => '00a0d2', // Sitemap accent color.

				// Feed.
				'excerpt_the_feed' => 1, // Generate feed Excerpts.
				'source_the_feed'  => 1, // Add backlink to the end of the feed.
				'index_the_feed'   => 0, // Add backlink to the end of the feed.
			],
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
	 * @since 4.2.0 Now memoizes its return value.
	 *
	 * @return array $options.
	 */
	public static function get_warned_options() {

		if ( isset( static::$warned_options ) )
			return static::$warned_options;

		static::register_automated_refresh( 'warned_options' );

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
		return static::$warned_options = (array) \apply_filters(
			'the_seo_framework_warned_site_options',
			[
				'title_rem_additions' => 1, // Title remove additions.
				'site_noindex'        => 1, // Site Page robots noindex.
				'site_nofollow'       => 1, // Site Page robots nofollow.
				'homepage_noindex'    => 1, // Homepage robots noindex.
				'homepage_nofollow'   => 1, // Homepage robots noarchive.
				Helper::get_robots_option_index( 'post_type', 'noindex' ) => [
					'post' => 1,
					'page' => 1,
				],
				Helper::get_robots_option_index( 'post_type', 'nofollow' ) => [
					'post' => 1,
					'page' => 1,
				],
			],
		);
	}

	/**
	 * Holds default site cache.
	 *
	 * This exists for consistency with how we handle options.
	 *
	 * @since 5.0.2
	 *
	 * @return array Default site cache.
	 */
	public static function get_default_site_caches() {
		return [];
	}
}
