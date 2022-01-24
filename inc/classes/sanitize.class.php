<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Sanitize
 * @subpackage The_SEO_Framework\Admin
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2022 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\Sanitize
 *
 * Sanitizes input within the plugin.
 *
 * @since 2.8.0
 */
class Sanitize extends Admin_Pages {

	/**
	 * Checks the SEO Settings page nonce. Returns false if nonce can't be found.
	 * Performs wp_die() when nonce verification fails.
	 * Memoizes the return value.
	 *
	 * Never run a sensitive function when it's returning false. This means no nonce can be verified.
	 *
	 * @since 2.7.0
	 * @since 3.1.0 Removed settings field existence check.
	 * @since 4.0.0 Added redundant user capability check.
	 * @since 4.1.0 Is now a protected method.
	 * @securitycheck 3.0.0 OK.
	 *
	 * @return bool True if verified and matches. False if can't verify.
	 */
	protected function verify_seo_settings_nonce() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo() ) return $memo;

		/**
		 * If this page doesn't parse the site options,
		 * There's no need to filter them on each request.
		 * Nonce is handled elsewhere. This function merely injects filters to the $_POST data.
		 *
		 * @since 2.2.9
		 */
		if ( empty( $_POST[ THE_SEO_FRAMEWORK_SITE_OPTIONS ] )
		|| ! \is_array( $_POST[ THE_SEO_FRAMEWORK_SITE_OPTIONS ] ) )
			return memo( false );

		// This is also handled in /wp-admin/options.php. Nevertheless, one might register outside of scope.
		if ( ! \current_user_can( $this->get_settings_capability() ) )
			return memo( false );

		// This is also handled in /wp-admin/options.php. Nevertheless, one might register outside of scope.
		// This also checks the nonce: `_wpnonce`.
		\check_admin_referer( THE_SEO_FRAMEWORK_SITE_OPTIONS . '-options' );

		return memo( true );
	}

	/**
	 * Handles settings field update POST actions.
	 *
	 * @since 2.8.0
	 * @since 3.0.6 Now updates db version, too.
	 * @since 3.1.0 Now always flushes the cache, even before the options are updated.
	 * @since 4.1.0 1. Renamed from 'handle_update_post' to 'process_settings_submission'
	 *              2. Is now a protected method.
	 *
	 * @return void Early if nonce failed.
	 */
	protected function process_settings_submission() {

		// Verify update headers.
		if ( ! $this->verify_seo_settings_nonce() ) return;

		// Initialize sanitation filters parsed on each option update.
		$this->init_sanitizer_filters();

		// Delete main cache now. For when the options don't change.
		$this->delete_main_cache();

		// Set backward compatibility. This runs after the sanitization.
		\add_filter( 'pre_update_option_' . THE_SEO_FRAMEWORK_SITE_OPTIONS, [ $this, '_set_backward_compatibility' ], 10 );

		// Sets that the options are unchanged, preemptively.
		$this->update_static_cache( 'settings_notice', 'unchanged' );
		// But, if this action fires, we can assure that the settings have been changed (according to WP).
		\add_action( 'update_option_' . THE_SEO_FRAMEWORK_SITE_OPTIONS, [ $this, '_set_option_updated_notice' ], 0 );

		// Flush transients again after options have changed.
		\add_action( 'update_option_' . THE_SEO_FRAMEWORK_SITE_OPTIONS, [ $this, 'delete_main_cache' ] );
		\add_action( 'update_option_' . THE_SEO_FRAMEWORK_SITE_OPTIONS, [ $this, 'update_db_version' ], 12 );
	}

	/**
	 * Sets the settings notice cache to "updated".
	 *
	 * @since 4.0.0
	 * @access private
	 */
	public function _set_option_updated_notice() {
		$this->update_static_cache( 'settings_notice', 'updated' );
	}

	/**
	 * Updates the database version to the defined one.
	 *
	 * This prevents errors when users go back to an earlier version, where options
	 * might be different from a future (or past, since v4.1.0) one.
	 *
	 * @since 3.0.6
	 */
	public function update_db_version() {
		\update_option( 'the_seo_framework_upgraded_db_version', THE_SEO_FRAMEWORK_DB_VERSION );
	}

	/**
	 * Maintains backward compatibility for older, migrated options, by injecting them
	 * into the options array before that's processed for updating.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 Emptied and is no longer enqueued.
	 * @since 4.1.0 1. Added taxonomical robots options backward compat.
	 *              2. Added the first two parameters.
	 * @access private
	 *
	 * @param mixed $new_value The new, unserialized, and filtered option value.
	 * @return mixed $new_value The updated option.
	 */
	public function _set_backward_compatibility( $new_value ) {

		db_4103:
		// Category and Tag robots backward compat.
		foreach ( [ 'noindex', 'nofollow', 'noarchive' ] as $r ) :
			$robots_option_id   = $this->get_robots_taxonomy_option_id( $r );
			$new_robots_options = $new_value[ $robots_option_id ] ?? [];

			$new_category_option = $new_robots_options['category'] ?? 0;
			$new_tag_option      = $new_robots_options['post_tag'] ?? 0;

			// Don't compare to old option--it's never reliably set; it might skip otherwise, although it's always correct.
			// Do not resanitize. Others might've overwritten that, let's keep their value.
			$new_value[ "category_$r" ] = $new_category_option;
			$new_value[ "tag_$r" ]      = $new_tag_option;
		endforeach;

		end:;
		return $new_value;
	}

	/**
	 * Registers each of the settings with a sanitization filter type.
	 *
	 * @since 2.8.0
	 * @since 3.1.0 Added caching, preventing duplicate registrations.
	 * @uses $this->add_option_filter() Assign filter to array of settings.
	 */
	public function init_sanitizer_filters() {

		if ( has_run( __METHOD__ ) ) return;

		$this->add_option_filter(
			's_title_separator',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'title_separator',
			]
		);

		$this->add_option_filter(
			's_description_raw',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'homepage_description',
				'homepage_og_description',
				'homepage_twitter_description',
			]
		);

		$this->add_option_filter(
			's_title_raw',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'site_title',
				'knowledge_name',
				'homepage_title',
				'homepage_title_tagline',
				'homepage_og_title',
				'homepage_twitter_title',
			]
		);

		$this->add_option_filter(
			's_knowledge_type',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'knowledge_type',
			]
		);

		$this->add_option_filter(
			's_left_right',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'title_location',
			]
		);

		$this->add_option_filter(
			's_left_right_home',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'home_title_location',
			]
		);

		$this->add_option_filter(
			's_alter_query_type',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'alter_archive_query_type',
				'alter_search_query_type',
			]
		);

		$this->add_option_filter(
			's_one_zero',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'alter_search_query',
				'alter_archive_query',

				'display_pixel_counter',
				'display_character_counter',

				'cache_sitemap',

				'display_seo_bar_tables',
				'display_seo_bar_metabox',
				'seo_bar_symbols',

				'title_rem_additions',
				'title_rem_prefixes',
				'title_strip_tags',

				'auto_description',

				'author_noindex',
				'date_noindex',
				'search_noindex',
				'site_noindex',

				'author_nofollow',
				'date_nofollow',
				'search_nofollow',
				'site_nofollow',

				'author_noarchive',
				'date_noarchive',
				'search_noarchive',
				'site_noarchive',

				'advanced_query_protection',

				'paged_noindex',
				'home_paged_noindex',

				'set_copyright_directives',

				'homepage_noindex',
				'homepage_nofollow',
				'homepage_noarchive',

				'homepage_tagline',

				'shortlink_tag',

				'prev_next_posts',
				'prev_next_archives',
				'prev_next_frontpage',

				'oembed_use_og_title',
				'oembed_use_social_image',
				'oembed_remove_author',

				'og_tags',
				'facebook_tags',
				'twitter_tags',
				'oembed_scripts',

				'social_title_rem_additions',

				'multi_og_image',

				'knowledge_output',

				'post_publish_time',
				'post_modify_time',

				'knowledge_logo',

				'ping_use_cron',
				'ping_google',
				'ping_bing',
				'ping_use_cron_prerender',

				'excerpt_the_feed',
				'source_the_feed',
				'index_the_feed',

				'ld_json_searchbox',
				'ld_json_breadcrumbs',

				'sitemaps_output',
				'sitemaps_robots',
				'sitemaps_modified',
				'sitemap_styles',
				'sitemap_logo',
			]
		);

		$this->add_option_filter(
			's_absint',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'social_image_fb_id',
				'homepage_social_image_id',
				'knowledge_logo_id',
				'sitemap_logo_id',
			]
		);

		$this->add_option_filter(
			's_numeric_string',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'timestamps_format',
			]
		);

		$this->add_option_filter(
			's_disabled_post_types',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'disabled_post_types',
			]
		);

		$this->add_option_filter(
			's_disabled_taxonomies',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'disabled_taxonomies',
			]
		);

		$this->add_option_filter(
			's_post_types',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				$this->get_robots_post_type_option_id( 'noindex' ),
				$this->get_robots_post_type_option_id( 'nofollow' ),
				$this->get_robots_post_type_option_id( 'noarchive' ),
			]
		);

		$this->add_option_filter(
			's_taxonomies',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				$this->get_robots_taxonomy_option_id( 'noindex' ),
				$this->get_robots_taxonomy_option_id( 'nofollow' ),
				$this->get_robots_taxonomy_option_id( 'noarchive' ),
			]
		);

		$this->add_option_filter(
			's_all_post_type_archive_meta',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'pta',
			]
		);

		/**
		 * @todo create content="code" stripper in PHP (redundant from JS's)
		 */
		$this->add_option_filter(
			's_no_html_space',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'facebook_appid',

				'google_verification',
				'bing_verification',
				'yandex_verification',
				'baidu_verification',
				'pint_verification',
			]
		);

		$this->add_option_filter(
			's_url',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'knowledge_facebook',
				'knowledge_twitter',
				'knowledge_gplus',
				'knowledge_instagram',
				'knowledge_youtube',
				'knowledge_pinterest',
				'knowledge_soundcloud',
				'knowledge_tumblr',
			]
		);

		$this->add_option_filter(
			's_url_query',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'knowledge_linkedin',
				'social_image_fb_url',
				'homepage_social_image_url',
				'knowledge_logo_url',
				'sitemap_logo_url',
			]
		);

		$this->add_option_filter(
			's_facebook_profile',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'facebook_publisher',
				'facebook_author',
			]
		);

		$this->add_option_filter(
			's_twitter_name',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'twitter_site',
				'twitter_creator',
			]
		);

		$this->add_option_filter(
			's_twitter_card',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'twitter_card',
			]
		);

		$this->add_option_filter(
			's_canonical_scheme',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'canonical_scheme',
			]
		);

		$this->add_option_filter(
			's_color_hex',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'sitemap_color_main',
				'sitemap_color_accent',
				'theme_color',
			]
		);

		$this->add_option_filter(
			's_min_max_sitemap',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'sitemap_query_limit',
			]
		);

		$this->add_option_filter(
			's_image_preview',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'max_image_preview',
			]
		);

		$this->add_option_filter(
			's_snippet_length',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'max_snippet_length',
				'max_video_preview',
			]
		);
	}

	/**
	 * Add sanitization filters to options.
	 *
	 * Associates a sanitization filter to each option (or sub options if they
	 * exist) before adding a reference to run the option through that
	 * sanitizer at the right time.
	 *
	 * @since 2.2.2
	 * @since 2.7.0 Uses external caching function.
	 * @since 2.8.0 Renamed.
	 * @since 4.0.0 Now caches its $option registration.
	 *
	 * @param string       $filter Sanitization filter type
	 * @param string       $option The option key.
	 * @param array|string $suboption Optional. Suboption key(s).
	 * @return boolean Returns true when complete
	 */
	public function add_option_filter( $filter, $option, $suboption = null ) {

		static $registered = [];

		$this->set_option_filter( $filter, $option, $suboption );

		// Memoize whether a filter has been set for the option already. Should only run once internally.
		if ( ! isset( $registered[ $option ] ) ) {
			\add_filter( "sanitize_option_{$option}", [ $this, 'sanitize' ], 10, 2 );
			$registered[ $option ] = true;
		}

		return true;
	}

	/**
	 * Sets sanitation filters cache.
	 * Memoizes the filters set so we can get them later.
	 *
	 * Associates a sanitization filter to each option (or sub options if they
	 * exist) before adding a reference to run the option through that
	 * sanitizer at the right time.
	 *
	 * @since 2.7.0
	 * @see $this->get_option_filters()
	 * @TODO allow for multiple filters per option? That'd speed up backward compat migration.
	 *
	 * @param string       $filter    Sanitization filter type
	 * @param string       $option    Option key
	 * @param array|string $suboption Optional. Suboption key(s).
	 * @param bool         $get       Whether to retrieve cache.
	 * @return array When $get is true, it will return the option filters.
	 */
	protected function set_option_filter( $filter, $option, $suboption = null, $get = false ) {

		static $options = [];

		if ( $get )
			return $options;

		if ( \is_array( $suboption ) ) {
			foreach ( $suboption as $so ) {
				$options[ $option ][ $so ] = $filter;
			}
		} elseif ( \is_null( $suboption ) ) {
			$options[ $option ] = $filter;
		} else {
			$options[ $option ][ $suboption ] = $filter;
		}
	}

	/**
	 * Returns sanitation filters from cache.
	 *
	 * @since 2.7.0
	 *
	 * @return array Filters with their associated (sub)options.
	 */
	protected function get_option_filters() {
		return $this->set_option_filter( '', '', '', true );
	}

	/**
	 * Sanitize a value, via the sanitization filter type associated with an option.
	 *
	 * @since 2.2.2
	 *
	 * @thanks StudioPress (http://www.studiopress.com/) for some code.
	 *
	 * @param mixed  $new_value New value
	 * @param string $option    Name of the option
	 * @return mixed Filtered, or unfiltered value
	 */
	public function sanitize( $new_value, $option ) {

		$filters = $this->get_option_filters();

		if ( ! isset( $filters[ $option ] ) ) {
			// We are not filtering this option at all
			return $new_value;
		} elseif ( \is_string( $filters[ $option ] ) ) {
			// Single option value
			return $this->do_filter( $filters[ $option ], $new_value, \get_option( $option ), $option );
		} elseif ( \is_array( $filters[ $option ] ) ) {
			// Array of suboption values to loop through
			$old_value = \get_option( $option, [] );
			foreach ( $filters[ $option ] as $suboption => $filter ) {
				$old_value[ $suboption ] = $old_value[ $suboption ] ?? '';
				$new_value[ $suboption ] = $new_value[ $suboption ] ?? '';
				$new_value[ $suboption ] = $this->do_filter( $filter, $new_value[ $suboption ], $old_value[ $suboption ], $option, $suboption );
			}
			return $new_value;
		}

		// Should never hit this, but:
		return $new_value;
	}

	/**
	 * Checks sanitization filter exists, and if so, passes the value through it.
	 *
	 * @since 2.2.2
	 * @since 4.1.0 Added $option and $suboption parameters.
	 *
	 * @param string $filter    Sanitization filter type.
	 * @param string $new_value New value.
	 * @param string $old_value Previous value.
	 * @param string $option    The option to filter.
	 * @param string $suboption The suboption to filter. Optional.
	 * @return mixed Returns filtered value, or submitted value if value is unfiltered.
	 */
	protected function do_filter( $filter, $new_value, $old_value, $option, $suboption = '' ) {

		$available_filters = $this->get_available_filters();

		if ( ! \in_array( $filter, array_keys( $available_filters ), true ) )
			return $new_value;

		return \call_user_func_array(
			$available_filters[ $filter ],
			[
				$new_value,
				$old_value,
				$option,
				$suboption,
			]
		);
	}

	/**
	 * Return array of known sanitization filter types.
	 *
	 * Array can be filtered via 'the_seo_framework_available_sanitizer_filters'
	 * to let themes and other plugins add their own sanitization filters.
	 *
	 * @since 2.2.2
	 *
	 * @thanks StudioPress (http://www.studiopress.com/) for some code.
	 *
	 * @return array Array with keys of sanitization types, and values of the
	 * filter function name as a callback
	 */
	protected function get_available_filters() {
		/**
		 * @since 2.2.2
		 * @param array $default_filters Array with keys of sanitization types
		 *              and values of the filter function name as a callback
		 */
		return (array) \apply_filters(
			'the_seo_framework_available_sanitizer_filters',
			[
				's_left_right'                 => [ $this, 's_left_right' ],
				's_left_right_home'            => [ $this, 's_left_right_home' ],
				's_title_separator'            => [ $this, 's_title_separator' ],
				's_description'                => [ $this, 's_description' ],
				's_description_raw'            => [ $this, 's_description_raw' ],
				's_title'                      => [ $this, 's_title' ],
				's_title_raw'                  => [ $this, 's_title_raw' ],
				's_knowledge_type'             => [ $this, 's_knowledge_type' ],
				's_alter_query_type'           => [ $this, 's_alter_query_type' ],
				's_one_zero'                   => [ $this, 's_one_zero' ],
				's_disabled_post_types'        => [ $this, 's_disabled_post_types' ],
				's_disabled_taxonomies'        => [ $this, 's_disabled_taxonomies' ],
				's_post_types'                 => [ $this, 's_post_types' ],
				's_taxonomies'                 => [ $this, 's_taxonomies' ],
				's_all_post_type_archive_meta' => [ $this, 's_all_post_type_archive_meta' ],
				's_numeric_string'             => [ $this, 's_numeric_string' ],
				's_no_html'                    => [ $this, 's_no_html' ],
				's_no_html_space'              => [ $this, 's_no_html_space' ],
				's_absint'                     => [ $this, 's_absint' ],
				's_safe_html'                  => [ $this, 's_safe_html' ],
				's_url'                        => [ $this, 's_url' ],
				's_url_query'                  => [ $this, 's_url_query' ],
				's_facebook_profile'           => [ $this, 's_facebook_profile' ],
				's_twitter_name'               => [ $this, 's_twitter_name' ],
				's_twitter_card'               => [ $this, 's_twitter_card' ],
				's_canonical_scheme'           => [ $this, 's_canonical_scheme' ],
				's_min_max_sitemap'            => [ $this, 's_min_max_sitemap' ],
				's_image_preview'              => [ $this, 's_image_preview' ],
				's_snippet_length'             => [ $this, 's_snippet_length' ],
			]
		);
	}

	/**
	 * Sanitizes post type archive meta.
	 *
	 * @since 4.2.0
	 *
	 * @param array $data The post type archive meta to sanitize : {
	 *    string $post_type => array $data
	 * }
	 * @return array The sanitized post type archive meta.
	 */
	public function s_all_post_type_archive_meta( $data ) {

		if ( ! $data )
			return [];

		// Do NOT test for post type's existence -- it might be registered incorrectly.
		// If the metadata yields empty -- do not unset key! It'll override "defaults" that way.
		foreach ( $data as $_post_type => &$meta )
			$meta = $this->s_post_type_archive_meta( $meta );

		return $data;
	}

	/**
	 * Sanitizes post type archive meta.
	 *
	 * @since 4.2.0
	 *
	 * @param array $data The post type archive meta to sanitize.
	 * @return array The sanitized post type archive meta.
	 */
	public function s_post_type_archive_meta( $data ) {
		return $this->s_term_meta( $data ); // Coincidence? I think not.
	}

	/**
	 * Sanitizes term meta.
	 *
	 * @since 4.0.0
	 *
	 * @param array $data The term meta to sanitize.
	 * @return array The sanitized term meta.
	 */
	public function s_term_meta( $data ) {

		foreach ( $data as $key => &$value ) :
			switch ( $key ) :
				case 'doctitle':
				case 'og_title':
				case 'tw_title':
					$value = $this->s_title_raw( $value );
					continue 2;

				case 'description':
				case 'og_description':
				case 'tw_description':
					$value = $this->s_description_raw( $value );
					continue 2;

				case 'canonical':
				case 'social_image_url':
					$value = $this->s_url_query( $value );
					continue 2;

				case 'social_image_id':
					// Bound to social_image_url.
					$value = $data['social_image_url'] ? $this->s_absint( $value ) : 0;
					continue 2;

				case 'noindex':
				case 'nofollow':
				case 'noarchive':
					$value = $this->s_qubit( $value );
					continue 2;

				case 'redirect':
					$value = $this->s_redirect_url( $value );
					continue 2;

				case 'title_no_blog_name':
					$value = $this->s_one_zero( $value );
					continue 2;

				default:
					unset( $data[ $key ] );
					break;
			endswitch;
		endforeach;

		return $data;
	}

	/**
	 * Sanitizes post meta.
	 *
	 * @since 4.0.0
	 *
	 * @param array $data The post meta to sanitize.
	 * @return array The sanitized post meta.
	 */
	public function s_post_meta( $data ) {

		foreach ( $data as $key => &$value ) :
			switch ( $key ) :
				case '_genesis_title':
				case '_open_graph_title':
				case '_twitter_title':
					$value = $this->s_title_raw( $value );
					continue 2;

				case '_genesis_description':
				case '_open_graph_description':
				case '_twitter_description':
					$value = $this->s_description_raw( $value );
					continue 2;

				case '_genesis_canonical_uri':
				case '_social_image_url':
					$value = $this->s_url_query( $value );
					continue 2;

				case '_social_image_id':
					// Bound to _social_image_url.
					$value = $data['_social_image_url'] ? $this->s_absint( $value ) : 0;
					continue 2;

				case '_genesis_noindex':
				case '_genesis_nofollow':
				case '_genesis_noarchive':
					$value = $this->s_qubit( $value );
					continue 2;

				case 'redirect':
					// Let's keep this as the output really is.
					$value = $this->s_redirect_url( $value );
					continue 2;

				case '_tsf_title_no_blogname':
				case 'exclude_local_search':
				case 'exclude_from_archive':
					$value = $this->s_one_zero( $value );
					continue 2;

				default:
					unset( $data[ $key ] );
					break;
			endswitch;
		endforeach;

		return $data;
	}

	/**
	 * Sanitizes user meta.
	 *
	 * @since 4.1.4
	 * @since 4.2.0 Now accepts and sanitizes the 'counter_type' index.
	 *
	 * @param array $data The user meta to sanitize.
	 * @return array The sanitized user meta.
	 */
	public function s_user_meta( $data ) {

		foreach ( $data as $key => &$value ) :
			switch ( $key ) :
				case 'facebook_page':
					$value = $this->s_facebook_profile( $value );
					continue 2;

				case 'twitter_page':
					$value = $this->s_twitter_name( $value );
					continue 2;

				case 'counter_type':
					$value = \absint( $value );

					if ( $value > 3 )
						$value = 0;
					continue 2;

				default:
					unset( $data[ $key ] );
					break;
			endswitch;
		endforeach;

		return $data;
	}

	/**
	 * Returns the title separator value string.
	 *
	 * @since 2.2.2
	 *
	 * @param mixed $new_value Should be identical to any of the $this->get_separator_list() values
	 * @return string Title separator option
	 */
	public function s_title_separator( $new_value ) {

		$title_separator = $this->get_separator_list();

		$key = \array_key_exists( $new_value, $title_separator );

		if ( $key )
			return (string) $new_value;

		$previous = $this->get_option( 'title_separator' );

		// Fallback to default if empty.
		if ( empty( $previous ) )
			$previous = $this->get_default_option( 'title_separator' );

		return (string) $previous;
	}

	/**
	 * Escapes and beautifies description.
	 *
	 * @since 2.5.2
	 *
	 * @param string $description The description to escape and beautify.
	 * @return string Escaped and beautified description.
	 */
	public function escape_description( $description = '' ) {

		$description = \wptexturize( $description );
		$description = \convert_chars( $description );
		$description = \esc_html( $description );
		$description = \capital_P_dangit( $description );
		$description = trim( $description );

		return $description;
	}

	/**
	 * Returns an one-line sanitized description and escapes it.
	 *
	 * @since 2.5.0
	 * @since 2.6.6 Removes duplicated spaces.
	 * @since 2.8.0 Method is now public.
	 * @since 2.8.2 Added extra sanitation.
	 * @uses $this->s_description_raw().
	 * @uses $this->escape_description().
	 *
	 * @param string $new_value The Description.
	 * @return string One line sanitized description.
	 */
	public function s_description( $new_value ) {

		$new_value = $this->s_description_raw( $new_value );
		$new_value = $this->escape_description( $new_value );

		return $new_value;
	}

	/**
	 * Returns an single-line, trimmed description without dupliacated spaces, nbsp, or tabs.
	 * Does NOT escape.
	 * Also converts back-solidi to their respective HTML entities for non-destructive handling.
	 *
	 * @since 2.8.2
	 * @since 4.0.5 Now normalized `-` entities.
	 *
	 * @param string $new_value The Description.
	 * @return string One line sanitized description.
	 */
	public function s_description_raw( $new_value ) {

		$new_value = $this->s_singleline( $new_value );
		$new_value = $this->s_nbsp( $new_value );
		$new_value = $this->s_tabs( $new_value );
		$new_value = $this->s_hyphen( $new_value );
		$new_value = $this->s_bsol( $new_value );
		$new_value = $this->s_dupe_space( $new_value );

		return $new_value;
	}

	/**
	 * Converts multilines to single lines.
	 *
	 * @since 2.8.2
	 * @since 3.1.0 Simplified method.
	 * @since 4.1.0 1. Made this method about 25~92% faster (more replacements = more faster). 73% slower on empty strings (negligible).
	 *              2. Now also strips form-feed and vertical whitespace characters--might they appear in the wild.
	 *              3. Now also strips horizontal tabs (reverted in 4.1.1).
	 * @since 4.1.1 1. Now uses real bytes, instead of sequences (causing uneven transformations, plausibly emptying content).
	 *              2. No longer transforms horizontal tabs. Use `s_tabs()` instead.
	 * @link https://www.php.net/manual/en/regexp.reference.escape.php
	 *
	 * @param string $new_value The input value with possible multiline.
	 * @return string The input string without multiple lines.
	 */
	public function s_singleline( $new_value ) {
		// Use x20 because it's a human-visible real space.
		return trim( strtr( $new_value, "\x0A\x0B\x0C\x0D", "\x20\x20\x20\x20" ) );
	}

	/**
	 * Removes duplicated spaces from the input value.
	 *
	 * @since 2.8.2
	 * @since 2.9.4 Now no longer fails when first two characters are spaces.
	 * @since 3.1.0 1. Now also catches non-breaking spaces.
	 *              2. Now uses a regex pattern.
	 *
	 * @param string $new_value The input value with possible multispaces.
	 * @return string The input string without duplicated spaces.
	 */
	public function s_dupe_space( $new_value ) {
		return preg_replace( '/\p{Zs}{2,}/u', ' ', $new_value );
	}

	/**
	 * Removes tabs and replaces it with spaces.
	 *
	 * @since 2.8.2
	 * @since 4.1.1 Now uses real bytes, instead of sequences (causing uneven transformations, plausibly emptying content).
	 * @see $this->s_dupe_space() For removing duplicates spaces.
	 * @link https://www.php.net/manual/en/regexp.reference.escape.php
	 *
	 * @param string $new_value The input value with possible tabs.
	 * @return string The input string without tabs.
	 */
	public function s_tabs( $new_value ) {
		// Use x20 because it's a human-visible real space.
		return strtr( $new_value, "\x09", "\x20" );
	}

	/**
	 * Sanitizes input excerpt.
	 *
	 * @since 2.8.0
	 * @since 2.8.2 1. Added $allow_shortcodes parameter.
	 *              2. Added $escape parameter.
	 * @since 3.2.4 Now selectively clears tags.
	 * @since 4.1.0 Moved `figcaption`, `figure`, `footer`, and `tfoot`, from `space` to `clear`.
	 * @see `$this->strip_tags_cs()`
	 *
	 * @param string $excerpt          The excerpt.
	 * @param bool   $allow_shortcodes Whether to maintain shortcodes from excerpt.
	 * @param bool   $escape           Whether to escape the excerpt.
	 * @return string The escaped Excerpt.
	 */
	public function s_excerpt( $excerpt = '', $allow_shortcodes = true, $escape = true ) {

		// No need to parse an empty excerpt.
		if ( '' === $excerpt ) return '';

		$strip_args = [
			'space' =>
				[ 'article', 'aside', 'blockquote', 'br', 'dd', 'div', 'dl', 'dt', 'li', 'main', 'ol', 'p', 'section', 'ul' ],
			'clear' =>
				[ 'address', 'bdo', 'button', 'canvas', 'code', 'fieldset', 'figcaption', 'figure', 'footer', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', 'hr', 'iframe', 'input', 'label', 'link', 'meta', 'nav', 'noscript', 'option', 'pre', 'samp', 'script', 'select', 'style', 'svg', 'table', 'textarea', 'tfoot', 'var', 'video' ],
		];

		/**
		 * @since 2.6.6.1
		 * @param bool $allow_shortcodes Whether to allow shortcodes.
		 */
		if ( $allow_shortcodes && \apply_filters( 'the_seo_framework_allow_excerpt_shortcode_tags', false ) ) {
			$excerpt = $this->strip_tags_cs( $excerpt, $strip_args );
		} else {
			$excerpt = $this->strip_tags_cs( \strip_shortcodes( $excerpt ), $strip_args );
		}

		if ( $escape )
			return $this->s_description( $excerpt );

		return $this->s_description_raw( $excerpt );
	}

	/**
	 * Cleans input excerpt. Does NOT escape excerpt for output.
	 *
	 * @since 2.8.2
	 * @see $this->s_excerpt - This is basically a copy without sanitation.
	 *
	 * @param string $excerpt          The excerpt.
	 * @param bool   $allow_shortcodes Whether to maintain shortcodes from excerpt.
	 * @return string The unescaped Excerpt.
	 */
	public function s_excerpt_raw( $excerpt = '', $allow_shortcodes = true ) {
		return $this->s_excerpt( $excerpt, $allow_shortcodes, false );
	}

	/**
	 * Escapes and beautifies title.
	 *
	 * @since 2.5.2
	 *
	 * @param string $title The title to escape and beautify.
	 * @param bool   $trim  Whether to trim the title from whitespaces.
	 * @return string Escaped and beautified title.
	 */
	public function escape_title( $title = '', $trim = true ) {

		$title = \wptexturize( $title );
		$title = \convert_chars( $title );
		$title = \esc_html( $title );
		$title = \capital_P_dangit( $title );
		$title = $trim ? trim( $title ) : $title;

		return $title;
	}

	/**
	 * Returns a sanitized and trimmed title.
	 *
	 * @since 2.5.2
	 * @since 2.8.0 Method is now public.
	 *
	 * @param string $new_value The input Title.
	 * @return string Sanitized and trimmed title.
	 */
	public function s_title( $new_value ) {

		$new_value = $this->s_title_raw( $new_value );
		$new_value = $this->escape_title( $new_value );

		return $new_value;
	}

	/**
	 * Returns an single-line, trimmed title without dupliacated spaces, nbsp, or tabs.
	 * Also converts back-solidi to their respective HTML entities for non-destructive handling.
	 *
	 * @since 2.8.2
	 * @since 4.0.0 Now normalizes `&` entities.
	 * @since 4.0.5 Now normalized `-` entities.
	 *
	 * @param string $new_value The input Title.
	 * @return string Sanitized, beautified and trimmed title.
	 */
	public function s_title_raw( $new_value ) {

		$new_value = $this->s_singleline( $new_value );
		$new_value = $this->s_nbsp( $new_value );
		$new_value = $this->s_tabs( $new_value );
		$new_value = $this->s_hyphen( $new_value );
		$new_value = $this->s_bsol( $new_value );
		$new_value = $this->s_dupe_space( $new_value );

		return (string) $new_value;
	}

	/**
	 * Returns the knowledge type value string.
	 *
	 * @since 2.2.8
	 * @since 2.8.0 Method is now public.
	 * @since 4.1.0 Can no longer fall back to its previous value--instead, it will fall back to a generic value.
	 *
	 * @param mixed $new_value Should ideally be a string 'person' or 'organization' passed in.
	 * @return string title Knowledge type option
	 */
	public function s_knowledge_type( $new_value ) {

		if ( \in_array( $new_value, [ 'person', 'organization' ], true ) )
			return $new_value;

		return 'organization';
	}

	/**
	 * Returns left or right, for the separator location.
	 *
	 * This method fetches the default option because it's conditional (LTR/RTL).
	 *
	 * @since 2.2.2
	 * @since 2.8.0 Method is now public.
	 *
	 * @param mixed $new_value Should ideally be a string 'left' or 'right' passed in.
	 * @return string left or right
	 */
	public function s_left_right( $new_value ) {

		if ( \in_array( $new_value, [ 'left', 'right' ], true ) )
			return $new_value;

		$previous = $this->get_option( 'title_location' );

		// Fallback if previous is also empty.
		if ( ! $previous )
			$previous = $this->get_default_option( 'title_location' );

		return (string) $previous;
	}

	/**
	 * Returns left or right, for the home separator location.
	 *
	 * This method fetches the default option because it's conditional (LTR/RTL).
	 *
	 * @since 2.5.2
	 * @since 2.8.0 Method is now public.
	 *
	 * @param mixed $new_value Should ideally be a string 'left' or 'right' passed in.
	 * @return string left or right
	 */
	public function s_left_right_home( $new_value ) {

		if ( \in_array( $new_value, [ 'left', 'right' ], true ) )
			return $new_value;

		$previous = $this->get_option( 'home_title_location' );

		// Fallback if previous is also empty.
		if ( ! $previous )
			$previous = $this->get_default_option( 'home_title_location' );

		return (string) $previous;
	}

	/**
	 * Sanitizes alter query type.
	 *
	 * @since 2.9.4
	 *
	 * @param mixed $new_value Should ideally be a string 'in_query' or 'post_query' passed in.
	 * @return string 'in_query' or 'post_query'
	 */
	public function s_alter_query_type( $new_value ) {

		if ( \in_array( $new_value, [ 'in_query', 'post_query' ], true ) )
			return $new_value;

		return 'in_query';
	}

	/**
	 * Returns a 1 or 0, for all truthy / falsy values.
	 *
	 * Uses double casting. First, we cast to bool, then to integer.
	 *
	 * @since 2.2.2
	 * @since 2.8.0 Method is now public.
	 *
	 * @param mixed $new_value Should ideally be a 1 or 0 integer passed in.
	 * @return int 1 or 0.
	 */
	public function s_one_zero( $new_value ) {
		return (int) (bool) $new_value;
	}

	/**
	 * Returns a -1, 0, or 1, based on nearest value.
	 *
	 * @since 4.0.0
	 *
	 * @param mixed $new_value Should ideally be -1, 0, or 1.
	 * @return int -1, 0, or 1.
	 */
	public function s_qubit( $new_value ) {

		if ( $new_value < -.33 ) {
			$new_value = -1;
		} elseif ( $new_value > .33 ) {
			$new_value = 1;
		} else {
			$new_value = 0;
		}

		return $new_value;
	}

	/**
	 * Sanitizes disabled post type entries.
	 *
	 * Filters out default post types.
	 *
	 * @since 3.1.0
	 *
	 * @param mixed $new_values Should ideally be an array with post type name indexes, and 1 or 0 passed in.
	 * @return array
	 */
	public function s_disabled_post_types( $new_values ) {

		if ( ! \is_array( $new_values ) ) return [];

		foreach ( $this->get_forced_supported_post_types() as $forced )
			unset( $new_values[ $forced ] );

		return $this->s_post_types( $new_values );
	}

	/**
	 * Sanitizes generic post type entries.
	 *
	 * Ideally, we want to check if the post type exists; however, some might be registered too late.
	 *
	 * @since 3.1.0
	 *
	 * @param mixed $new_values Should ideally be an array with post type name indexes, and 1 or 0 passed in.
	 * @return array
	 */
	public function s_post_types( $new_values ) {

		if ( ! \is_array( $new_values ) ) return [];

		foreach ( $new_values as $index => &$value )
			$value = $this->s_one_zero( $value );

		return $new_values;
	}

	/**
	 * Sanitizes disabled taxonomy entries.
	 *
	 * Filters out default taxonomies.
	 *
	 * @since 4.1.0
	 *
	 * @param mixed $new_values Should ideally be an array with taxonomy name indexes, and 1 or 0 passed in.
	 * @return array
	 */
	public function s_disabled_taxonomies( $new_values ) {

		if ( ! \is_array( $new_values ) ) return [];

		foreach ( $this->get_forced_supported_taxonomies() as $forced )
			unset( $new_values[ $forced ] );

		return $this->s_taxonomies( $new_values );
	}

	/**
	 * Sanitizes generic taxonomy entries.
	 *
	 * Ideally, we want to check if the taxonomy exists; however, some might be registered too late.
	 *
	 * @since 4.1.0
	 *
	 * @param mixed $new_values Should ideally be an array with taxonomy name indexes, and 1 or 0 passed in.
	 * @return array
	 */
	public function s_taxonomies( $new_values ) {
		return $this->s_post_types( $new_values );
	}

	/**
	 * Returns a numeric string, like '0', '1', '2'.
	 *
	 * Uses double casting. First, we cast to integer, then to string.
	 * Rounds floats down. Converts non-numeric inputs to '0'.
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $new_value Should ideally be an integer.
	 * @return string An integer as string.
	 */
	public function s_numeric_string( $new_value ) {
		return (string) (int) $new_value;
	}

	/**
	 * Returns a positive integer value.
	 *
	 * @since 2.2.2
	 * @since 2.8.0 Method is now public.
	 *
	 * @param mixed $new_value Should ideally be a positive integer.
	 * @return integer Positive integer.
	 */
	public function s_absint( $new_value ) {
		return \absint( $new_value );
	}

	/**
	 * Removes HTML tags from string.
	 *
	 * @since 2.2.2
	 * @since 2.8.0 Method is now public.
	 *
	 * @param string $new_value String, possibly with HTML in it.
	 * @return string String without HTML in it.
	 */
	public function s_no_html( $new_value ) {
		// phpcs:ignore, WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- This is simple and performant sanity.
		return strip_tags( $new_value );
	}

	/**
	 * Removes HTML tags and line breaks from string.
	 * Also removes all spaces.
	 *
	 * @since 2.5.2
	 * @since 2.8.0 Method is now public.
	 *
	 * @param string $new_value String, possibly with HTML and spaces in it.
	 * @return string String without HTML and breaks in it.
	 */
	public function s_no_html_space( $new_value ) {
		// phpcs:ignore, WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- This is simple and performant sanity.
		return str_replace( ' ', '', strip_tags( $new_value ) );
	}

	/**
	 * Escapes attributes after converting `&` to `&amp;` to prevent double-escaping
	 * of entities in HTML input value attributes.
	 *
	 * @since 4.0.0
	 *
	 * @param string $new_value String with possibly ampersands.
	 * @return string
	 */
	public function esc_attr_preserve_amp( $new_value ) {
		return \esc_attr( str_replace( '&', '&amp;', $new_value ) );
	}

	/**
	 * Makes URLs safe and removes query args.
	 *
	 * @since 2.2.2
	 * @since 2.8.0 Method is now public.
	 *
	 * @param string $new_value String, a URL, possibly unsafe.
	 * @return string String a safe URL without Query Arguments.
	 */
	public function s_url( $new_value ) {

		/**
		 * If queries have been tokenized, take the value before the query args.
		 * Otherwise it's empty, so take the current value.
		 */
		$url = strtok( $new_value, '?' ) ?: $new_value;

		return \esc_url_raw( $url );
	}

	/**
	 * Makes URLs safe, maintaining queries.
	 *
	 * @since 2.2.8
	 * @since 2.8.0 Method is now public.
	 * @TODO rename to s_url_keep_query?
	 *
	 * @param string $new_value String, a URL, possibly unsafe.
	 * @return string String a safe URL with Query Arguments.
	 */
	public function s_url_query( $new_value ) {
		return \esc_url_raw( $new_value );
	}

	/**
	 * Makes non-relative URLs absolute, corrects the scheme to most preferred when the
	 * domain matches the current site, and makes it safer regardless afterward.
	 *
	 * Could not think of a good name. Enjoy.
	 *
	 * @since 4.0.2
	 *
	 * @param string $new_value String, an (invalid) URL, possibly unsafe.
	 * @return string String a safe URL with Query Arguments.
	 */
	public function s_url_relative_to_current_scheme( $new_value ) {

		if ( $this->matches_this_domain( $new_value ) ) {
			$url = $this->set_preferred_url_scheme( $new_value );
		} else {
			// This also sets preferred URL scheme if path.
			$url = $this->convert_to_url_if_path( $new_value );
		}

		return $this->s_url_query( $url );
	}

	/**
	 * Makes Email Addresses safe, via sanitize_email()
	 *
	 * @since 2.2.2
	 * @since 2.8.0 Method is now public.
	 *
	 * @param string $new_value String, an email address, possibly unsafe.
	 * @return string String a safe email address
	 */
	public function s_email_address( $new_value ) {
		return \sanitize_email( $new_value );
	}

	/**
	 * Removes unsafe HTML tags, via wp_kses_post().
	 *
	 * @since 2.2.2
	 * @since 2.8.0 Method is now public.
	 *
	 * @param string $new_value String with potentially unsafe HTML in it.
	 * @return string String with only safe HTML in it
	 */
	public function s_safe_html( $new_value ) {
		return \wp_kses_post( $new_value );
	}

	/**
	 * Parses Twitter name and site. Adds @ if it wasn't supplied.
	 * Parses URL to path and adds @ if URL is given.
	 *
	 * @since 2.2.2
	 * @since 2.8.0 Method is now public.
	 * @since 3.0.0 1. Now removes '@' from the URL path.
	 *              2. Now removes spaces and tabs.
	 * @since 4.0.0 1. Now returns empty on lone `@` entries.
	 *              2. Now returns empty when using only spaces and tabs.
	 *
	 * @param string $new_value String with potentially wrong Twitter username.
	 * @return string String with 'correct' Twitter username
	 */
	public function s_twitter_name( $new_value ) {

		// phpcs:ignore, WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- This is simple and performant sanity.
		$new_value = strip_tags( $new_value );
		$new_value = $this->s_singleline( $new_value );
		$new_value = $this->s_nbsp( $new_value );
		$new_value = $this->s_tabs( $new_value );
		$new_value = trim( $new_value );

		if ( empty( $new_value ) ) return '';

		$profile = trim( $this->s_relative_url( $new_value ), ' /' );

		if ( '@' === $profile ) return '';

		if ( '@' !== substr( $profile, 0, 1 ) )
			$profile = "@$profile";

		return str_replace( [ ' ', "\t" ], '', $profile );
	}

	/**
	 * Parses Facebook profile URLs. Exchanges URLs for Facebook's.
	 *
	 * @since 2.2.2
	 * @since 2.8.0 Method is now public.
	 * @since 3.0.6 Now allows a sole query argument when profile.php is used.
	 * @since 4.0.0 1. No longer returns a plain Facebook URL when the entry path is sanitized to become empty.
	 *              2. Now returns empty when using only spaces and tabs.
	 *
	 * @param string $new_value String with potentially wrong Facebook profile URL.
	 * @return string String with 'correct' Facebook profile URL.
	 */
	public function s_facebook_profile( $new_value ) {

		// phpcs:ignore, WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- This is simple and performant sanity.
		$new_value = strip_tags( $new_value );
		$new_value = $this->s_singleline( $new_value );
		$new_value = $this->s_nbsp( $new_value );
		$new_value = $this->s_tabs( $new_value );
		$new_value = trim( $new_value );

		if ( empty( $new_value ) ) return '';

		$path = trim( $this->s_relative_url( $new_value ), ' /' );

		if ( ! $path ) return '';

		$link = "https://www.facebook.com/{$path}";

		if ( strpos( $link, 'profile.php' ) ) {
			// Gets query parameters.
			parse_str( parse_url( $link, PHP_URL_QUERY ), $r );
			if ( isset( $r['id'] ) ) {
				$link = 'https://www.facebook.com/profile.php?id=' . \absint( $r['id'] );
				$link = $this->s_url_query( $link );
			} else {
				$link = '';
			}
		} else {
			$link = $this->s_url( $link );
		}

		return $link;
	}

	/**
	 * Parses Twitter Card radio input. Fills in default if incorrect value is supplied.
	 * Falls back to previous value if empty. If previous value is empty if will go to default.
	 *
	 * @since 2.5.2
	 * @since 2.8.0 Method is now public.
	 *
	 * @param string $new_value String with potentially wrong option value.
	 * @return string Sanitized twitter card type.
	 */
	public function s_twitter_card( $new_value ) {

		// Fetch Twitter card array.
		$card = $this->get_twitter_card_types();

		$key = \array_key_exists( $new_value, $card );

		if ( $key ) return (string) $new_value;

		$previous = $this->get_option( 'twitter_card' );

		if ( empty( $previous ) )
			$previous = $this->get_default_option( 'twitter_card' );

		return (string) $previous;
	}

	/**
	 * Converts absolute URLs to relative URLs, if they weren't already.
	 * The method should more aptly be named: "maybe_make_url_relative()".
	 *
	 * @since 2.6.5
	 * @since 2.8.0 Method is now public.
	 * @since 4.0.0 No longer strips the prepended / path.
	 *
	 * @param string $url Full Path URL or relative URL.
	 * @return string Abolute path.
	 */
	public function s_relative_url( $url ) {
		return preg_replace( '/^(https?:)?\/\/[^\/]+(\/.*)/i', '$2', $url );
	}

	/**
	 * Sanitizes the Redirect URL.
	 *
	 * @since 2.2.4
	 * @since 2.8.0 Method is now public.
	 * @since 3.0.6 Noqueries is now disabled by default.
	 * @since 4.0.0 1. Removed rudimentary relative URL testing.
	 *              2. Removed input transformation filters, and with that, removed redundant multisite spam protection.
	 *              3. Now allows all protocols. Enjoy!
	 *              4. Now no longer lets through double-absolute URLs (e.g. `https://google.com/https://google.com/path/to/file/`)
	 *                 when filter `the_seo_framework_allow_external_redirect` is set to false.
	 *
	 * @param string $new_value String with potentially unwanted redirect URL.
	 * @return string The Sanitized Redirect URL
	 */
	public function s_redirect_url( $new_value ) {

		// phpcs:ignore, WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- This is simple, performant sanity.
		$url = strip_tags( $new_value );

		if ( ! $url ) return '';

		// This is also checked when performing a redirect.
		if ( ! $this->allow_external_redirect() )
			$url = $this->set_url_scheme( $url, 'relative' );

		// Only adjust scheme if it used to be relative. Do not use `s_url_relative_to_current_scheme()`.
		$url = $this->convert_to_url_if_path( $url );

		/**
		 * @since 2.5.0
		 * @since 3.0.6 Now false by default.
		 * @param bool $noqueries Whether to remove query arguments from URLs.
		 */
		$noqueries = (bool) \apply_filters( 'the_seo_framework_301_noqueries', false );

		/**
		 * Remove queries from the URL
		 *
		 * Returns plain Home URL if $this->allow_external_redirect() is set to false and only a query has been supplied
		 * But that's okay. The URL was rogue anyway :)
		 */
		if ( $noqueries ) {
			/**
			 * Remove query args
			 *
			 * @see The_SEO_Framework\Sanitize::s_url
			 * @since 2.2.4
			 */
			$new_value = $this->s_url( $url );
		} else {
			/**
			 * Allow query string parameters. XSS safe.
			 */
			$new_value = $this->s_url_query( $url );
		}

		return $new_value;
	}

	/**
	 * Sanitizes color hexadecimals.
	 *
	 * @since 2.8.0
	 *
	 * @param string $new_value String with potentially unwanted hex values.
	 * @return string The sanitized color hex.
	 */
	public function s_color_hex( $new_value ) {

		$color = trim( $new_value, '# ' );

		if ( '' === $color )
			return '';

		if ( preg_match( '/^([A-Fa-f0-9]{3}){1,2}$/', $color ) )
			return $color;

		return '';
	}

	/**
	 * Replaces non-transformative hyphens with entity hyphens.
	 * Duplicated simple hyphens are preserved.
	 *
	 * Regex challenge, make the columns without an x light up:
	 * xxx - xx - xxx- - - xxxxxx xxxxxx- xxxxx - -
	 * --- - -- - ---- - - ------ ------- ----- - -
	 *
	 * The answer? `/((-{2,3})(*SKIP)-|-)(?(2)(*FAIL))/`
	 * Sybre-kamisama.
	 *
	 * @since 4.0.5
	 *
	 * @param string $text String with potential hyphens.
	 * @return string A string with safe HTML encoded hyphens.
	 */
	public function s_hyphen( $text ) {

		$text = preg_replace( '/((-{2,3})(*SKIP)-|-)(?(2)(*FAIL))/', '&#x2d;', $text );

		// str_replace is faster than putting these alternative sequences in the `-|-` regex above.
		// That'd be this: "/((?'h'-|&\#45;|\xe2\x80\x90){2,3}(*SKIP)(?&h)|(?&h))(?(h)(*FAIL))/u"
		return str_replace( [ '&#45;', "\xe2\x80\x90" ], '&#x2d;', $text );
	}

	/**
	 * Replaces non-break spaces with regular spaces.
	 *
	 * @since 2.8.2
	 * @since 3.1.0 Now catches all non-breaking characters.
	 *
	 * @param string $new_value String with potentially unwanted nbsp values.
	 * @return string A spacey string.
	 */
	public function s_nbsp( $new_value ) {
		return str_replace( [ '&nbsp;', '&#160;', '&#xA0;', "\xc2\xa0" ], ' ', $new_value );
	}

	/**
	 * Replaces backslash with entity backslash.
	 *
	 * @since 2.8.2
	 *
	 * @param string $new_value String with potentially unwanted \ values.
	 * @return string A string with safe HTML encoded backslashes.
	 */
	public function s_bsol( $new_value ) {
		return str_replace( '\\', '&#92;', stripslashes( $new_value ) );
	}

	/**
	 * Replaces backslash with entity backslash.
	 *
	 * @since 4.0.0
	 *
	 * @param string $new_value String with potentially wanted \ values.
	 * @return string A string with safe HTML encoded backslashes.
	 */
	public function s_bsol_raw( $new_value ) {
		return str_replace( '\\', '&#92;', $new_value );
	}

	/**
	 * Sanitizes canonical scheme settings.
	 *
	 * @since 2.9.0
	 *
	 * @param string $new_value String with potentially unwanted values.
	 * @return string A correct canonical scheme setting value.
	 */
	public function s_canonical_scheme( $new_value ) {

		$accepted_values = [
			'automatic',
			'https',
			'http',
		];

		if ( \in_array( $new_value, $accepted_values, true ) )
			return (string) $new_value;

		return 'automatic';
	}

	/**
	 * Sanitizes sitemap's min/max post value.
	 *
	 * @since 3.1.0
	 *
	 * @param int $new_value Integer with potentially unwanted values.
	 * @return int A limited integer 1<=R<=50000.
	 */
	public function s_min_max_sitemap( $new_value ) {

		$new_value = $this->s_absint( $new_value );

		if ( ! $new_value ) {
			// We assume something's wrong. Return default value.
			$new_value = $this->get_default_option( 'sitemap_query_limit' );
		} elseif ( $new_value < 1 ) {
			$new_value = 1;
		} elseif ( $new_value > 50000 ) {
			$new_value = 50000;
		}

		return $new_value;
	}

	/**
	 * Sanitizes image preview directive value.
	 *
	 * @since 4.0.2
	 *
	 * @param string $new_value String with potentially unwanted values.
	 * @return string The robots image snippet preview directive value.
	 */
	public function s_image_preview( $new_value ) {

		if ( ! \in_array( $new_value, [ 'none', 'standard', 'large' ], true ) )
			$new_value = 'standard';

		return $new_value;
	}

	/**
	 * Sanitizes video and snippet preview length directive values.
	 *
	 * @since 4.0.2
	 *
	 * @param int $new_value Integer with potentially unwanted values.
	 * @return int The robots video and snippet preview directive value.
	 */
	public function s_snippet_length( $new_value ) {

		$new_value = (int) $new_value;

		if ( $new_value < 0 ) {
			$new_value = -1;
		} elseif ( $new_value > 600 ) {
			$new_value = 600;
		}

		return $new_value;
	}

	/**
	 * Sanitizeses ID. Mainly removing spaces and coding characters.
	 *
	 * Unlike sanitize_key(), it doesn't alter the case nor applies filters.
	 * It also maintains the '@' character and square brackets.
	 *
	 * @see WordPress Core sanitize_key()
	 * @since 4.0.0
	 *
	 * @param string $id The unsanitized ID.
	 * @return string The sanitized ID.
	 */
	public function s_field_id( $id ) {
		return preg_replace( '/[^a-zA-Z0-9\[\]_\-@]/', '', $id );
	}

	/**
	 * Strips all URLs that are placed on new lines. These are prone to be embeds.
	 *
	 * This might leave stray line feeds. Use `tsf()->s_singleline()` to fix that.
	 *
	 * @since 3.1.0
	 * @see \WP_Embed::autoembed()
	 *
	 * @param string $content The content to look for embed.
	 * @return string $content Without single-lined URLs.
	 */
	public function strip_newline_urls( $content ) {
		return preg_replace( '/^(?!\r|\n)\s*?(https?:\/\/[^\s<>"]+)(\s*)$/mi', '', $content );
	}

	/**
	 * Strips all URLs that are placed in paragraphs on their own. These are prone to be embeds.
	 *
	 * This might leave stray line feeds. Use `tsf()->s_singleline()` to fix that.
	 *
	 * @since 3.1.0
	 * @see \WP_Embed::autoembed()
	 *
	 * @param string $content The content to look for embed.
	 * @return string $content Without the paragraphs containing simple URLs.
	 */
	public function strip_paragraph_urls( $content ) {
		return preg_replace( '/(<p(?: [^>]*)?>\s*)(https?:\/\/[^\s<>"]+)(\s*<\/p>)/i', '', $content );
	}

	/**
	 * Strips tags with HTML Context-Sensitivity and ouputs its breakdown.
	 *
	 * It essentially strips all tags, and replaces block-type tags' endings with spaces.
	 * When done, it performs a sanity-cleanup via `strip_tags()`.
	 *
	 * Tip: You might want to use method `s_dupe_space()` to clear up the duplicated spaces afterward.
	 *
	 * @since 3.2.4
	 * @since 4.0.0 Now allows emptying the indexes `space` and `clear`.
	 * @since 4.0.5 1. Added the `strip` argument index to the second parameter for clearing leftover tags.
	 *              2. Now also clears `iframe` tags by default.
	 *              3. Now no longer (for example) accidentally takes `link` tags when only `li` tags are set for stripping.
	 *              4. Now performs a separate query for void elements; to prevent regex recursion.
	 * @since 4.1.0 Now detects nested elements and preserves that content correctly--as if we'd pass through scrupulously beyond infinity.
	 * @since 4.1.1 Can now replace void elements with spaces when so inclined via the arguments (space vs clear).
	 * @link https://www.w3schools.com/html/html_blocks.asp
	 * @link https://html.spec.whatwg.org/multipage/syntax.html#void-elements
	 *
	 * @param string $input The input text that needs its tags stripped.
	 * @param array  $args  The input arguments: {
	 *                         'space'   : @param array|null HTML elements that should have a space added around it.
	 *                                                       If not set or null, skip check.
	 *                                                       If empty array, skips stripping; otherwise, use input.
	 *                         'clear'   : @param array|null HTML elements that should be emptied and replaced with a space.
	 *                                                       If not set or null, skip check.
	 *                                                       If empty array, skips stripping; otherwise, use input.
	 *                         'strip'   : @param bool       If set, strip_tags() is performed before returning the output.
	 *                                                       Recommended always true, since Regex doesn't understand XML.
	 *                      }
	 *                      NOTE: WARNING The array values are forwarded to a regex without sanitization/quoting.
	 *                      NOTE: Unlisted, script, and style tags will be stripped via PHP's `strip_tags()`. (togglable via `$args['strip']`)
	 *                            Also note that their contents are maintained as-is, without added spaces.
	 *                            It is why you should always list `style` and `script` in the `clear` array.
	 * @return string The output string without tags.
	 */
	public function strip_tags_cs( $input, $args = [] ) {

		$default_args = [
			'space' =>
				[ 'address', 'article', 'aside', 'blockquote', 'br', 'dd', 'div', 'dl', 'dt', 'fieldset', 'figcaption', 'figure', 'footer', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', 'li', 'main', 'nav', 'ol', 'p', 'pre', 'section', 'table', 'tfoot', 'ul' ],
			'clear' =>
				[ 'bdo', 'button', 'canvas', 'code', 'hr', 'iframe', 'input', 'label', 'link', 'noscript', 'meta', 'option', 'samp', 'script', 'select', 'style', 'svg', 'textarea', 'var', 'video' ],
			'strip' => true,
		];

		$void = [ 'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr' ];

		if ( ! $args ) {
			$args = $default_args;
		} else {
			foreach ( [ 'space', 'clear' ] as $type ) {
				if ( isset( $args[ $type ] ) )
					$args[ $type ] = $args[ $type ] ? (array) $args[ $type ] : [];
			}
			$args['strip'] = $args['strip'] ?? $default_args['strip'];
		}

		// Clear first, so there's less to process; then add spaces.
		foreach ( [ 'clear', 'space' ] as $type ) {
			if ( empty( $args[ $type ] ) ) continue;

			// void = element without content.
			$void_query = array_intersect( $args[ $type ], $void );
			// fill = <normal | template | raw text | escapable text | foreign> element.
			$fill_query = array_diff( $args[ $type ], $void );

			if ( $void_query ) {
				$_regex   = sprintf( '<(%s)\b[^>]*?>', implode( '|', $void_query ) );
				$_replace = 'space' === $type ? ' ' : '';
				$input    = preg_replace( "/$_regex/si", $_replace, $input );
			}
			if ( $fill_query ) {
				$_regex   = sprintf( '<(%s)\b[^>]*>([^<]*)(<\/\1>)?|(<\/?(?1)>)', implode( '|', $fill_query ) );
				$_replace = 'space' === $type ? ' $2 ' : ' ';
				$input    = preg_replace( "/$_regex/si", $_replace, $input );
			}
		}

		// phpcs:ignore, WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- $args defines stripping of 'script' and 'style'.
		return $args['strip'] ? strip_tags( $input ) : $input;
	}

	/**
	 * Cleans known parameters from image details.
	 *
	 * @since 4.0.0
	 * @since 4.0.2 Now finds smaller images when they're over 4K.
	 * @since 4.0.5 Now faults images with filename extensions APNG, BMP, ICO, TIFF, or SVG.
	 * @since 4.1.4 Fixed theoretical issue where a different image could be set when width
	 *              and height are supplied and either over 4K, but no ID is given.
	 * @NOTE If the input details are in an associative array, they'll be converted to sequential.
	 *
	 * @param array $details The image details, either associative (see $defaults) or sequential.
	 * @return array The image details array, sequential: int => {
	 *    string url:    The image URL,
	 *    int    id:     The image ID,
	 *    int    width:  The image width in pixels,
	 *    int    height: The image height in pixels,
	 *    string alt:    The image alt tag,
	 * }
	 */
	public function s_image_details( $details ) {

		if ( array_values( $details ) === $details )
			return $this->s_image_details_deep( $details );

		$defaults = [
			'url'    => '',
			'id'     => 0,
			'width'  => 0,
			'height' => 0,
			'alt'    => '',
		];

		[ $url, $id, $width, $height, $alt ] = array_values( array_merge( $defaults, $details ) );

		if ( ! $url ) return $defaults;

		$url = $this->s_url_relative_to_current_scheme( $url );

		if ( ! $url ) return $defaults;

		/**
		 * Skip APNG, BMP, ICO, TIFF, and SVG.
		 *
		 * @link <https://developer.twitter.com/en/docs/tweets/optimize-with-cards/overview/markup>
		 * @link <https://developer.mozilla.org/en-US/docs/Web/Media/Formats/Image_types>
		 * jp(e)g, png, webp, and gif are supported. Assume all non-matches to fall in those categories,
		 * since we don't perform a live MIME-test.
		 *
		 * Tested with Facebook; they ignore them too. There's no documentation available.
		 * TODO Should we even test for this here, or at the image generators' type?
		 * It seems, however, that all services we want to communicate with ignore these types, anyway.
		 */
		if ( \in_array(
			strtolower( strtok( pathinfo( $url, PATHINFO_EXTENSION ), '?' ) ),
			[ 'apng', 'bmp', 'ico', 'cur', 'svg', 'tif', 'tiff' ],
			true
		) ) return $defaults;

		$width  = \absint( $width );
		$height = \absint( $height );

		if ( ! $width || ! $height )
			$width = $height = 0;

		if ( $id && ( $width > 4096 || $height > 4096 ) ) {
			$new_image = $this->get_largest_acceptable_image_src( $id, 4096 );
			$url       = $new_image ? $this->s_url_relative_to_current_scheme( $new_image[0] ) : '';

			if ( ! $url ) return $defaults;

			// No sanitization needed. PHP's getimagesize() returns the correct values.
			$width  = $new_image[1];
			$height = $new_image[2];
		}

		if ( $alt ) {
			$alt = \wp_strip_all_tags( $alt );
			// 420: https://developer.twitter.com/en/docs/tweets/optimize-with-cards/overview/summary.html
			// Don't "ai"-trim if under, it's unlikely to always be a sentence.
			$alt = \strlen( $alt ) > 420 ? $this->trim_excerpt( $alt, 0, 420 ) : $alt;
		}

		return compact( 'url', 'id', 'width', 'height', 'alt' );
	}

	/**
	 * Iterates over and cleans known parameters from image details. Also strips out duplicates.
	 *
	 * @since 4.0.0
	 *
	 * @param array $details_array The image details, preferably sequential.
	 * @return array The image details array, sequential: int => {
	 *    string url:    The image URL,
	 *    int    id:     The image ID,
	 *    int    width:  The image width in pixels,
	 *    int    height: The image height in pixels,
	 *    string alt:    The image alt tag,
	 * }
	 */
	public function s_image_details_deep( $details_array ) {

		$cleaned_details = [];

		// Failsafe. Convert associative details to a multidimensional sequential array.
		if ( isset( $details_array['url'] ) )
			$details_array = [ $details_array ];

		foreach ( $details_array as $details )
			$cleaned_details[] = $this->s_image_details( $details );

		return array_values(
			array_intersect_key(
				$cleaned_details,
				array_unique( array_filter( array_column( $cleaned_details, 'url' ) ) )
			)
		);
	}

	/**
	 * Sets string value and returns boolean if it has any content.
	 *
	 * Best used in an or loop.
	 * e.g.         set_and_strlen( $title, 'one' ) || set_and_strlen( $title, 'two' );
	 * or (slower): set_and_strlen( $title, [ 'one', 'two', ...[value] ] );
	 *
	 * @since 4.1.0
	 * @ignore unused. untested. Creates super-smelly code, but fixes bugs revolving around input '0' or ' '.
	 *         We'd prefer a native PHP "string has length" comparison operator.
	 *         I don't believe any language has this. Then again, many languages don't see '0' as false.
	 *
	 * @param variable        $var   The variable to set. Passed by reference.
	 * @param string|string[] $value The value to set, or array of values.
	 * @return bool True if content has any length.
	 */
	protected function set_and_strlen( &$var, $value = '' ) {

		if ( \is_array( $value ) ) {
			foreach ( $value as $v ) {
				if ( $this->set_and_strlen( $var, $v ) )
					return true;
			}
			return false;
		}

		return \strlen( $var = trim( $value ) );
	}

	/**
	 * Sets string value if current variable has no content. Returns boolean value if it has any length.
	 *
	 * Can be used to loop via or statements -- here, $title will be set to 'two' if $usertitle is empty:
	 * e.g. strlen_or_set( $title, trim( $usertitle ) ) || strlen_or_set( $title, 'two' );
	 *
	 * @since 4.2.3
	 * @ignore unused. untested. Creates super-smelly code, but fixes bugs revolving around input '0' or ' '.
	 *         We'd prefer a native PHP "string has length" comparison operator.
	 *         I don't believe any language has this. Then again, many languages don't see '0' as false.
	 *
	 * @param variable $var   The variable to set. Passed by reference.
	 * @param string   $value The value to set if $var has no string length.
	 * @return bool True if content has any length.
	 */
	protected function strlen_or_set( &$var, $value ) {
		return (bool) ( \strlen( $var ) ?: \strlen( $var = $value ) );
	}
}
