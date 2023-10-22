<?php
/**
 * @package The_SEO_Framework\Classes\Data\Plugin\Sanitize
 * @subpackage The_SEO_Framework\Data\Plugin
 */

namespace The_SEO_Framework\Data\Plugin;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\has_run;

use \The_SEO_Framework\Data;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds a collection of option sanitization methods for TSF's settings index.
 *
 * @since 4.3.0
 * @access private
 */
class Sanitize {

	/**
	 * @since 4.3.0
	 * @var array[] The option filter sanitization callbacks.
	 */
	private static $sanitizers = [];

	/**
	 * Filters the settings whenever updated.
	 * Only hooked in admin.
	 *
	 * @hook "sanitize_option . THE_SEO_FRAMEWORK_SITE_OPTIONS" 10
	 * @since 2.2.2
	 * @since 4.3.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `sanitize`.
	 *
	 * @param mixed $value The sanitized [sic] option value.
	 * @return mixed The actually sanitized option value.
	 */
	public static function filter_settings_update( $value ) {

		static::register_sanitizers_jit();

		$old_value = Data\Plugin::get_options();

		/**
		 * @since 4.3.0
		 * @param array $filters A map of filters and their callbacks : {
		 *    string option_name => callable[] A list of callbacks,
		 * }
		 */
		$sanitizers = \apply_filters(
			'the_seo_framework_settings_update_sanitizers',
			static::$sanitizers,
		);

		return $value;

		// var_dump() TODO make all sanitizers work!!!

		foreach ( $sanitizers as $option => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				$value[ $option ] = \call_user_func_array(
					$callback,
					[
						$value[ $option ], // Should always be set, no?
						$old_value[ $option ] ?? null,
						$option,
					]
				);
			}
		}

		return $value;
	}

	/**
	 * Add sanitization filters to suboptions.
	 * Will only set filters if they don't already exists. This allows for other
	 * developers to add their custom filters before we do --- use filter
	 * `'sanitize_option_' . \THE_SEO_FRAMEWORK_SITE_OPTIONS`.
	 *
	 * @since 4.3.0
	 *
	 * @param array $filters A map of filters and their callbacks : {
	 *    string option_name => callable|callable[] callback,
	 * }
	 */
	public static function register_sanitizers( $filters ) {

		// Remit FETCH_STATIC_PROP_R opcode calls every time we'd otherwise use static::$filters hereinafter.
		$_sanitizers = &static::$sanitizers;

		foreach ( $filters as $option => $callbacks ) {
			if ( isset( $callbacks[0] ) ) {
				$_sanitizers[ $option ] ??= $callbacks;
			} else {
				$_sanitizers[ $option ] ??= [ $callbacks ];
			}
		}
	}

	/**
	 * Registers each of the settings with a sanitization filter type.
	 *
	 * @since 2.8.0
	 * @since 3.1.0 Added caching, preventing duplicate registrations.
	 * @since 4.3.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `init_sanitizer_filters`.
	 */
	public static function register_sanitizers_jit() {

		if ( has_run( __METHOD__ ) ) return;

		$filters = [
			'advanced_query_protection'    => 'zero_or_one',
			'alter_archive_query_type'     => 'alter_query_type',
			'alter_archive_query'          => 'zero_or_one',
			'alter_search_query_type'      => 'alter_query_type',
			'alter_search_query'           => 'zero_or_one',
			'author_noarchive'             => 'zero_or_one',
			'author_nofollow'              => 'zero_or_one',
			'author_noindex'               => 'zero_or_one',
			'auto_description'             => 'zero_or_one',
			'baidu_verification'           => [ 'strip_tags', 'remove_spacing' ],
			'bing_verification'            => [ 'strip_tags', 'remove_spacing' ],
			'cache_sitemap'                => 'zero_or_one',
			'canonical_scheme'             => 'canonical_scheme_setting',
			'date_noarchive'               => 'zero_or_one',
			'date_nofollow'                => 'zero_or_one',
			'date_noindex'                 => 'zero_or_one',
			'disabled_post_types'          => [ 'filter_forced_post_types', 'zero_or_one_array' ],
			'disabled_taxonomies'          => [ 'filter_forced_taxonomies', 'zero_or_one_array' ],
			'display_character_counter'    => 'zero_or_one',
			'display_pixel_counter'        => 'zero_or_one',
			'display_seo_bar_metabox'      => 'zero_or_one',
			'display_seo_bar_tables'       => 'zero_or_one',
			'excerpt_the_feed'             => 'zero_or_one',
			'facebook_author'              => 'facebook_profile_link',
			'facebook_publisher'           => 'facebook_profile_link',
			'facebook_tags'                => 'zero_or_one',
			'google_verification'          => [ 'strip_tags', 'remove_spacing' ],
			'home_paged_noindex'           => 'zero_or_one',
			'home_title_location'          => 'title_location',
			'homepage_description'         => 'text',
			'homepage_noarchive'           => 'zero_or_one',
			'homepage_nofollow'            => 'zero_or_one',
			'homepage_noindex'             => 'zero_or_one',
			'homepage_og_description'      => 'text',
			'homepage_og_title'            => 'text',
			'homepage_social_image_id'     => 'absint',
			'homepage_social_image_url'    => 'uri',
			'homepage_tagline'             => 'zero_or_one',
			'homepage_title_tagline'       => 'text',
			'homepage_title'               => 'text',
			'homepage_twitter_description' => 'text',
			'homepage_twitter_title'       => 'text',
			'index_the_feed'               => 'zero_or_one',
			'knowledge_facebook'           => 'uri_only_scheme_authority_path',
			'knowledge_instagram'          => 'uri_only_scheme_authority_path',
			'knowledge_linkedin'           => 'uri',
			'knowledge_logo_id'            => 'absint',
			'knowledge_logo_url'           => 'uri',
			'knowledge_logo'               => 'zero_or_one',
			'knowledge_name'               => 'text',
			'knowledge_output'             => 'zero_or_one',
			'knowledge_pinterest'          => 'uri_only_scheme_authority_path',
			'knowledge_soundcloud'         => 'uri_only_scheme_authority_path',
			'knowledge_tumblr'             => 'uri_only_scheme_authority_path',
			'knowledge_twitter'            => 'uri_only_scheme_authority_path',
			'knowledge_type'               => 'knowledge_type_setting',
			'knowledge_youtube'            => 'uri_only_scheme_authority_path',
			'ld_json_breadcrumbs'          => 'zero_or_one',
			'ld_json_searchbox'            => 'zero_or_one',
			'max_image_preview'            => 'copyright_size_setting',
			'max_snippet_length'           => 'copyright_length_setting',
			'max_video_preview'            => 'copyright_length_setting',
			'multi_og_image'               => 'zero_or_one',
			'oembed_remove_author'         => 'zero_or_one',
			'oembed_scripts'               => 'zero_or_one',
			'oembed_use_og_title'          => 'zero_or_one',
			'oembed_use_social_image'      => 'zero_or_one',
			'og_tags'                      => 'zero_or_one',
			'paged_noindex'                => 'zero_or_one',
			'ping_bing'                    => 'zero_or_one',
			'ping_google'                  => 'zero_or_one',
			'ping_use_cron_prerender'      => 'zero_or_one',
			'ping_use_cron'                => 'zero_or_one',
			'pint_verification'            => [ 'strip_tags', 'remove_spacing' ],
			'post_modify_time'             => 'zero_or_one',
			'post_publish_time'            => 'zero_or_one',
			'prev_next_archives'           => 'zero_or_one',
			'prev_next_frontpage'          => 'zero_or_one',
			'prev_next_posts'              => 'zero_or_one',
			'pta'                          => 'pta_meta_settings',
			'search_noarchive'             => 'zero_or_one',
			'search_nofollow'              => 'zero_or_one',
			'search_noindex'               => 'zero_or_one',
			'seo_bar_symbols'              => 'zero_or_one',
			'set_copyright_directives'     => 'zero_or_one',
			'shortlink_tag'                => 'zero_or_one',
			'site_noarchive'               => 'zero_or_one',
			'site_nofollow'                => 'zero_or_one',
			'site_noindex'                 => 'zero_or_one',
			'site_title'                   => 'text',
			'sitemap_color_accent'         => 'rgb_hex_3_6',
			'sitemap_color_main'           => 'rgb_hex_3_6',
			'sitemap_logo_id'              => 'absint',
			'sitemap_logo_url'             => 'uri',
			'sitemap_logo'                 => 'zero_or_one',
			'sitemap_query_limit'          => 'sitemap_query_limit_setting',
			'sitemap_styles'               => 'zero_or_one',
			'sitemaps_modified'            => 'zero_or_one',
			'sitemaps_output'              => 'zero_or_one',
			'sitemaps_robots'              => 'zero_or_one',
			'social_image_fb_id'           => 'absint',
			'social_image_fb_url'          => 'uri',
			'social_title_rem_additions'   => 'zero_or_one',
			'source_the_feed'              => 'zero_or_one',
			'theme_color'                  => 'rgb_hex_3_6',
			'timestamps_format'            => 'numeric_string',
			'title_location'               => 'title_location',
			'title_rem_additions'          => 'zero_or_one',
			'title_rem_prefixes'           => 'zero_or_one',
			'title_separator'              => 'title_separator_setting',
			'title_strip_tags'             => 'zero_or_one',
			'twitter_card'                 => 'twitter_card_setting',
			'twitter_creator'              => 'twitter_profile_handle',
			'twitter_site'                 => 'twitter_profile_handle',
			'twitter_tags'                 => 'zero_or_one',
			'yandex_verification'          => [ 'strip_tags', 'remove_spacing' ],

			// phpcs:disable, WordPress.Arrays.MultipleStatementAlignment.LongIndexSpaceBeforeDoubleArrow -- it fine.
			Data\Plugin\Helper::get_robots_option_index( 'post_type', 'noarchive' ) => 'zero_or_one_array',
			Data\Plugin\Helper::get_robots_option_index( 'post_type', 'nofollow' )  => 'zero_or_one_array',
			Data\Plugin\Helper::get_robots_option_index( 'post_type', 'noindex' )   => 'zero_or_one_array',
			Data\Plugin\Helper::get_robots_option_index( 'taxonomy', 'noarchive' )  => 'zero_or_one_array',
			Data\Plugin\Helper::get_robots_option_index( 'taxonomy', 'nofollow' )   => 'zero_or_one_array',
			Data\Plugin\Helper::get_robots_option_index( 'taxonomy', 'noindex' )    => 'zero_or_one_array',
			// phpcs:enable, WordPress.Arrays.MultipleStatementAlignment.LongIndexSpaceBeforeDoubleArrow
		];

		$sanitizer_class = Data\Filter\Sanitize::class;

		foreach ( $filters as &$callbacks ) {
			if ( \is_array( $callbacks ) ) {
				foreach ( $callbacks as &$cb )
					$cb = [ $sanitizer_class, $cb ];
			} else {
				$callbacks = [ $sanitizer_class, $callbacks ];
			}
		}

		static::register_sanitizers( $filters );
	}
}
