<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Sanitize
 * @subpackage The_SEO_Framework\Admin
 */

namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
	 *
	 * Never run a sensitive function when it's returning false. This means no nonce can be verified.
	 *
	 * @since 2.7.0
	 * @since 3.1.0 Removed settings field existence check.
	 * @since 4.0.0 Added redundant user capability check.
	 * @securitycheck 3.0.0 OK.
	 * @staticvar bool $verified.
	 *
	 * @return bool True if verified and matches. False if can't verify.
	 */
	public function verify_seo_settings_nonce() {

		static $validated = null;

		if ( isset( $validated ) )
			return $validated;

		/**
		 * If this page doesn't parse the site options,
		 * There's no need to filter them on each request.
		 * Nonce is handled elsewhere. This function merely injects filters to the $_POST data.
		 *
		 * @since 2.2.9
		 */
		if ( empty( $_POST[ THE_SEO_FRAMEWORK_SITE_OPTIONS ] )
		|| ! is_array( $_POST[ THE_SEO_FRAMEWORK_SITE_OPTIONS ] ) )
			return $validated = false;

		// This is also handled in /wp-admin/options.php. Nevertheless, one might register outside of scope.
		if ( ! \current_user_can( $this->get_settings_capability() ) )
			return $validated = false;

		// This is also handled in /wp-admin/options.php. Nevertheless, one might register outside of scope.
		// This also checks the nonce: `_wpnonce`.
		\check_admin_referer( THE_SEO_FRAMEWORK_SITE_OPTIONS . '-options' );

		return $validated = true;
	}

	/**
	 * Handles settings field update POST actions.
	 *
	 * @since 2.8.0
	 * @since 3.0.6 Now updates db version, too.
	 * @since 3.1.0 Now always flushes the cache, even before the options are updated.
	 *
	 * @return void Early if nonce failed.
	 */
	public function handle_update_post() {

		//* Verify update headers.
		if ( ! $this->verify_seo_settings_nonce() ) return;

		//* Initialize sanitation filters parsed on each option update.
		$this->init_sanitizer_filters();

		//* Delete main cache now. For when the options don't change.
		$this->delete_main_cache();

		//* Sets that the options are unchanged, preemptively.
		$this->update_static_cache( 'settings_notice', 'unchanged' );
		//* But, if this action fires, we can assure that the settings have been changed.
		\add_action( 'update_option_' . THE_SEO_FRAMEWORK_SITE_OPTIONS, [ $this, '_set_option_updated_notice' ], 0 );

		//* Flush transients after options have changed.
		\add_action( 'update_option_' . THE_SEO_FRAMEWORK_SITE_OPTIONS, [ $this, 'delete_main_cache' ] );
		\add_action( 'update_option_' . THE_SEO_FRAMEWORK_SITE_OPTIONS, [ $this, 'update_db_version' ], 12 );
		//* TEMP: Set backward compatibility.
		// \add_action( 'update_option_' . THE_SEO_FRAMEWORK_SITE_OPTIONS, [ $this, '_set_backward_compatibility' ], 13 );
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
	 * might be different from a future one.
	 *
	 * @since 3.0.6
	 */
	public function update_db_version() {
		\update_option( 'the_seo_framework_upgraded_db_version', THE_SEO_FRAMEWORK_DB_VERSION );
	}

	/**
	 * Maintains backward compatibility for older, migrated options.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 Emptied and is no longer enqueued.
	 * @access private
	 * @staticvar bool $running Prevents on-update loops.
	 */
	public function _set_backward_compatibility() {
		static $running = false;
		if ( $running ) return;
		$running = true;
		end:;
		$running = false;
	}

	/**
	 * Registers each of the settings with a sanitization filter type.
	 *
	 * @since 2.8.0
	 * @since 3.1.0 Added caching, preventing duplicate registrations.
	 * @uses $this->add_option_filter() Assign filter to array of settings.
	 */
	public function init_sanitizer_filters() {

		if ( _has_run( __METHOD__ ) ) return;

		$this->add_option_filter(
			's_title_separator',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'title_separator',
			]
		);

		$this->add_option_filter(
			's_description',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[]
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
			's_title',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'knowledge_name',
			]
		);

		$this->add_option_filter(
			's_title_raw',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
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

				'cache_meta_schema',
				'cache_sitemap',
				'cache_object',

				'display_seo_bar_tables',
				'display_seo_bar_metabox',
				'seo_bar_symbols',

				'title_rem_additions',
				'title_rem_prefixes',
				'title_strip_tags',

				'auto_description',

				'category_noindex',
				'tag_noindex',
				'author_noindex',
				'date_noindex',
				'search_noindex',
				'site_noindex',

				'category_nofollow',
				'tag_nofollow',
				'author_nofollow',
				'date_nofollow',
				'search_nofollow',
				'site_nofollow',

				'category_noarchive',
				'tag_noarchive',
				'author_noarchive',
				'date_noarchive',
				'search_noarchive',
				'site_noarchive',

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

				'og_tags',
				'facebook_tags',
				'twitter_tags',

				'multi_og_image',

				'knowledge_output',

				'post_publish_time',
				'post_modify_time',

				'knowledge_logo',

				'ping_use_cron',
				'ping_google',
				'ping_bing',

				'excerpt_the_feed',
				'source_the_feed',

				'ld_json_searchbox',
				'ld_json_breadcrumbs',

				'sitemaps_output',
				'sitemaps_robots',
				'sitemaps_modified',
				'sitemaps_priority',
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
			's_post_types',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				$this->get_robots_post_type_option_id( 'noindex' ),
				$this->get_robots_post_type_option_id( 'nofollow' ),
				$this->get_robots_post_type_option_id( 'noarchive' ),
			]
		);

		/**
		 * @todo create content="code" stripper
		 * @priority low 2.9.0+
		 */
		$this->add_option_filter(
			's_no_html_space',
			THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'facebook_appid',

				'google_verification',
				'bing_verification',
				'yandex_verification',
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
	 * @since 2.7.0: Uses external caching function.
	 * @since 2.8.0 Renamed.
	 * @since 4.0.0 Now caches its $option registration.
	 * @staticvar array $cache
	 *
	 * @param string       $filter Sanitization filter type
	 * @param string       $option The option key.
	 * @param array|string $suboption Optional. Suboption key(s).
	 * @return boolean Returns true when complete
	 */
	public function add_option_filter( $filter, $option, $suboption = null ) {

		static $cache = [];

		$this->set_option_filter( $filter, $option, $suboption );

		if ( ! isset( $cache[ $option ] ) ) {
			\add_filter( 'sanitize_option_' . $option, [ $this, 'sanitize' ], 10, 2 );
			$cache[ $option ] = true;
		}

		return true;
	}

	/**
	 * Sets sanitation filters cache.
	 *
	 * Associates a sanitization filter to each option (or sub options if they
	 * exist) before adding a reference to run the option through that
	 * sanitizer at the right time.
	 *
	 * @since 2.7.0
	 * @staticvar $options The options filter cache.
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

		if ( is_array( $suboption ) ) {
			foreach ( $suboption as $so ) {
				$options[ $option ][ $so ] = $filter;
			}
		} elseif ( is_null( $suboption ) ) {
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
			//* We are not filtering this option at all
			return $new_value;
		} elseif ( is_string( $filters[ $option ] ) ) {
			//* Single option value
			return $this->do_filter( $filters[ $option ], $new_value, \get_option( $option ) );
		} elseif ( is_array( $filters[ $option ] ) ) {
			//* Array of suboption values to loop through
			$old_value = \get_option( $option, [] );
			foreach ( $filters[ $option ] as $suboption => $filter ) {
				$old_value[ $suboption ] = isset( $old_value[ $suboption ] ) ? $old_value[ $suboption ] : '';
				$new_value[ $suboption ] = isset( $new_value[ $suboption ] ) ? $new_value[ $suboption ] : '';
				$new_value[ $suboption ] = $this->do_filter( $filter, $new_value[ $suboption ], $old_value[ $suboption ] );
			}
			return $new_value;
		}

		//* Should never hit this, but:
		return $new_value;
	}

	/**
	 * Checks sanitization filter exists, and if so, passes the value through it.
	 *
	 * @since 2.2.2
	 *
	 * @thanks StudioPress (http://www.studiopress.com/) for some code.
	 *
	 * @param string $filter Sanitization filter type
	 * @param string $new_value New value
	 * @param string $old_value Previous value
	 * @return mixed Returns filtered value, or submitted value if value is unfiltered.
	 */
	protected function do_filter( $filter, $new_value, $old_value ) {

		$available_filters = $this->get_available_filters();

		if ( ! in_array( $filter, array_keys( $available_filters ), true ) )
			return $new_value;

		return call_user_func( $available_filters[ $filter ], $new_value, $old_value );
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
				's_left_right'          => [ $this, 's_left_right' ],
				's_left_right_home'     => [ $this, 's_left_right_home' ],
				's_title_separator'     => [ $this, 's_title_separator' ],
				's_description'         => [ $this, 's_description' ],
				's_description_raw'     => [ $this, 's_description_raw' ],
				's_title'               => [ $this, 's_title' ],
				's_title_raw'           => [ $this, 's_title_raw' ],
				's_knowledge_type'      => [ $this, 's_knowledge_type' ],
				's_alter_query_type'    => [ $this, 's_alter_query_type' ],
				's_one_zero'            => [ $this, 's_one_zero' ],
				's_disabled_post_types' => [ $this, 's_disabled_post_types' ],
				's_post_types'          => [ $this, 's_post_types' ],
				's_numeric_string'      => [ $this, 's_numeric_string' ],
				's_no_html'             => [ $this, 's_no_html' ],
				's_no_html_space'       => [ $this, 's_no_html_space' ],
				's_absint'              => [ $this, 's_absint' ],
				's_safe_html'           => [ $this, 's_safe_html' ],
				's_url'                 => [ $this, 's_url' ],
				's_url_query'           => [ $this, 's_url_query' ],
				's_facebook_profile'    => [ $this, 's_facebook_profile' ],
				's_twitter_name'        => [ $this, 's_twitter_name' ],
				's_twitter_card'        => [ $this, 's_twitter_card' ],
				's_canonical_scheme'    => [ $this, 's_canonical_scheme' ],
				's_min_max_sitemap'     => [ $this, 's_min_max_sitemap' ],
				's_image_preview'       => [ $this, 's_image_preview' ],
				's_snippet_length'      => [ $this, 's_snippet_length' ],
			]
		);
	}

	/**
	 * Sanitizes term meta.
	 *
	 * @since 4.0.0
	 *
	 * @param array $data The term meta to sanitize.
	 * @return array The sanitized term meta.
	 */
	public function s_term_meta( array $data ) {

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
					//* Bound to social_image_url.
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
	public function s_post_meta( array $data ) {

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
					/**
					 * Remove unwanted query parameters. They're allowed by Google, but very much rather not.
					 * Also, they will only cause bugs.
					 * Query parameters are also only used when no pretty permalinks are used. Which is bad.
					 */
					$value = $this->s_url_query( $value );
					continue 2;

				case '_social_image_id':
					//* Bound to _social_image_url.
					$value = $data['_social_image_url'] ? $this->s_absint( $value ) : 0;
					continue 2;

				case '_genesis_noindex':
				case '_genesis_nofollow':
				case '_genesis_noarchive':
					$value = $this->s_qubit( $value );
					continue 2;

				case 'redirect':
					//* Let's keep this as the output really is.
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
	 * Returns the title separator value string.
	 *
	 * @since 2.2.2
	 *
	 * @param mixed $new_value Should be identical to any of the $this->get_separator_list() values
	 * @return string Title separator option
	 */
	public function s_title_separator( $new_value ) {

		$title_separator = $this->get_separator_list();

		$key = array_key_exists( $new_value, $title_separator );

		if ( $key )
			return (string) $new_value;

		$previous = $this->get_option( 'title_separator' );

		//* Fallback to default if empty.
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
	 * @since 2.6.6 : Removes duplicated spaces.
	 * @since 2.8.0 : Method is now public.
	 * @since 2.8.2 : Added extra sanitation.
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
	 *
	 * @param string $new_value The Description.
	 * @return string One line sanitized description.
	 */
	public function s_description_raw( $new_value ) {

		$new_value = $this->s_singleline( $new_value );
		$new_value = $this->s_nbsp( $new_value );
		$new_value = $this->s_tabs( $new_value );
		$new_value = $this->s_bsol( $new_value );
		$new_value = $this->s_dupe_space( $new_value );

		return $new_value;
	}

	/**
	 * Converts multilines to single lines.
	 *
	 * @since 2.8.2
	 * @since 3.1.0 Simplified method.
	 *
	 * @param string $new_value The input value with possible multiline.
	 * @return string The input string without multiple lines.
	 */
	public function s_singleline( $new_value ) {
		return trim( preg_replace( '/[\p{Zl}\p{Zp}\r\n]+/u', ' ', $new_value ) );
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
	 * @see $this->s_dupe_space() For removing duplicates spaces.
	 *
	 * @param string $new_value The input value with possible tabs.
	 * @return string The input string without tabs.
	 */
	public function s_tabs( $new_value ) {
		return str_replace( "\t", ' ', $new_value );
	}

	/**
	 * Sanitizes input excerpt.
	 *
	 * @since 2.8.0
	 * @since 2.8.2 : 1. Added $allow_shortcodes parameter.
	 *                2. Added $escape parameter.
	 * @see `$this->strip_tags_cs()`
	 *
	 * @param string $excerpt          The excerpt.
	 * @param bool   $allow_shortcodes Whether to maintain shortcodes from excerpt.
	 * @param bool   $escape           Whether to escape the excerpt.
	 * @return string The escaped Excerpt.
	 */
	public function s_excerpt( $excerpt = '', $allow_shortcodes = true, $escape = true ) {

		//* No need to parse an empty excerpt.
		if ( '' === $excerpt ) return '';

		$strip_args = [
			'space' =>
				[ 'article', 'aside', 'blockquote', 'dd', 'div', 'dl', 'dt', 'figcaption', 'figure', 'footer', 'li', 'main', 'ol', 'p', 'section', 'tfoot', 'ul' ],
			'clear' =>
				[ 'address', 'bdo', 'br', 'button', 'canvas', 'code', 'fieldset', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', 'hr', 'input', 'label', 'link', 'meta', 'nav', 'noscript', 'option', 'pre', 'samp', 'script', 'select', 'style', 'svg', 'table', 'textarea', 'var', 'video' ],
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
	 *
	 * @param string $new_value The input Title.
	 * @return string Sanitized, beautified and trimmed title.
	 */
	public function s_title_raw( $new_value ) {

		$new_value = $this->s_singleline( $new_value );
		$new_value = $this->s_nbsp( $new_value );
		$new_value = $this->s_tabs( $new_value );
		$new_value = $this->s_bsol( $new_value );
		$new_value = $this->s_dupe_space( $new_value );

		return (string) $new_value;
	}

	/**
	 * Returns the knowledge type value string.
	 *
	 * @since 2.2.8
	 * @since 2.8.0 Method is now public.
	 *
	 * @param mixed $new_value Should be identical to any of the $person_organization values.
	 * @return string title Knowledge type option
	 */
	public function s_knowledge_type( $new_value ) {

		if ( 'person' === $new_value || 'organization' === $new_value )
			return (string) $new_value;

		$previous = $this->get_option( 'knowledge_type' );

		return (string) $previous;
	}

	/**
	 * Returns left or right, for the separator location.
	 *
	 * @since 2.2.2
	 * @since 2.8.0 Method is now public.
	 *
	 * @param mixed $new_value Should ideally be a string 'left' or 'right' passed in.
	 * @return string left or right
	 */
	public function s_left_right( $new_value ) {

		if ( 'left' === $new_value || 'right' === $new_value )
			return (string) $new_value;

		$previous = $this->get_option( 'title_location' );

		//* Fallback if previous is also empty.
		if ( empty( $previous ) )
			$previous = $this->get_default_option( 'title_location' );

		return (string) $previous;
	}

	/**
	 * Returns left or right, for the home separator location.
	 *
	 * @since 2.5.2
	 * @since 2.8.0 Method is now public.
	 *
	 * @param mixed $new_value Should ideally be a string 'left' or 'right' passed in.
	 * @return string left or right
	 */
	public function s_left_right_home( $new_value ) {

		if ( 'left' === $new_value || 'right' === $new_value )
			return (string) $new_value;

		$previous = $this->get_option( 'home_title_location' );

		//* Fallback if previous is also empty.
		if ( empty( $previous ) )
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

		if ( in_array( $new_value, [ 'in_query', 'post_query' ], true ) )
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

		if ( ! is_array( $new_values ) ) return [];

		foreach ( $this->get_forced_supported_post_types() as $forced ) {
			unset( $new_values[ $forced ] );
		}

		return $this->s_post_types( $new_values );
	}

	/**
	 * Sanitizes generic post type entries.
	 *
	 * @since 3.1.0
	 *
	 * @param mixed $new_values Should ideally be an array with post type name indexes, and 1 or 0 passed in.
	 * @return array
	 */
	public function s_post_types( $new_values ) {

		if ( ! is_array( $new_values ) ) return [];

		foreach ( $new_values as $index => $value ) {
			$new_values[ $index ] = $this->s_one_zero( $value );
		}

		return $new_values;
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
	 * @since 3.0.0: 1. Now removes '@' from the URL path.
	 *               2. Now removes spaces and tabs.
	 * @since 4.0.0: 1. Now returns empty on lone `@` entries.
	 *               2. Now returns empty when using only spaces and tabs.
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
			$profile = '@' . $profile;

		return str_replace( [ ' ', "\t" ], '', $profile );
	}

	/**
	 * Parses Facebook profile URLs. Exchanges URLs for Facebook's.
	 *
	 * @since 2.2.2
	 * @since 2.8.0 Method is now public.
	 * @since 3.0.6 Now allows a sole query argument when profile.php is used.
	 * @since 4.0.0: 1. No longer returns a plain Facebook URL when the entry path is sanitized to become empty.
	 *               2. Now returns empty when using only spaces and tabs.
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

		$link = 'https://www.facebook.com/' . $path;

		if ( strpos( $link, 'profile.php' ) ) {
			//= Gets query parameters.
			$q = strtok( substr( $link, strpos( $link, '?' ) ), '?' );
			parse_str( $q, $r );
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

		//* Fetch Twitter card array.
		$card = $this->get_twitter_card_types();

		$key = array_key_exists( $new_value, $card );

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
	 * @since 4.0.0 : 1. Removed rudimentary relative URL testing.
	 *                2. Removed input transformation filters, and with that, removed redundant multisite spam protection.
	 *                3. Now allows all protocols. Enjoy!
	 *                4. Now no longer lets through double-absolute URLs (e.g. `https://google.com/https://google.com/path/to/file/`)
	 *                   when filter `the_seo_framework_allow_external_redirect` is set to false.
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

		if ( in_array( $new_value, $accepted_values, true ) )
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

		if ( ! in_array( $new_value, [ 'none', 'standard', 'large' ], true ) )
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
	 * @deprecated
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
	 * This might leave stray line feeds. Use `the_seo_framework()->s_singleline()` to fix that.
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
	 * This might leave stray line feeds. Use `the_seo_framework()->s_singleline()` to fix that.
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
	 * @link: https://www.w3schools.com/html/html_blocks.asp
	 *
	 * @param string $input The input text that needs its tags stripped.
	 * @param array  $args  The input arguments: {
	 *                         'space'   : @param array|null HTML elements that should have a space added around it.
	 *                                                       If not set or null, skip check.
	 *                                                       If empty array, skips stripping; otherwise, use input.
	 *                         'clear'   : @param array|null HTML elements that should be emptied and replaced with a space.
	 *                                                       If not set or null, skip check.
	 *                                                       If empty array, skips stripping; otherwise, use input.
	 *                      }
	 *                      NOTE: WARNING The array values are forwarded to a regex without sanitization.
	 *                      NOTE: Unlisted, script, and style tags will be stripped via PHP's `strip_tags()`.
	 *                            Also note that their contents are maintained as-is, without added spaces.
	 *                            It is why you should always list `style` and `script` in the `clear` array.
	 * @return string The output string without tags.
	 */
	public function strip_tags_cs( $input, $args = [] ) {

		$default_args = [
			'space' =>
				[ 'address', 'article', 'aside', 'blockquote', 'dd', 'div', 'dl', 'dt', 'fieldset', 'figcaption', 'figure', 'footer', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', 'li', 'main', 'nav', 'ol', 'p', 'pre', 'section', 'table', 'tfoot', 'ul' ],
			'clear' =>
				[ 'bdo', 'br', 'button', 'canvas', 'code', 'hr', 'input', 'label', 'link', 'noscript', 'meta', 'option', 'samp', 'script', 'select', 'style', 'svg', 'textarea', 'var', 'video' ],
		];

		if ( ! $args ) {
			$args = $default_args;
		} else {
			foreach ( [ 'space', 'clear' ] as $type ) {
				if ( isset( $args[ $type ] ) ) {
					if ( ! $args[ $type ] ) {
						$args[ $type ] = [];
					} else {
						$args[ $type ] = (array) $args[ $type ];
					}
				}
			}
		}

		// Clear first, so there's less to process; then add spaces.
		foreach ( [ 'clear', 'space' ] as $type ) {
			if ( empty( $args[ $type ] ) ) continue;

			$_regex   = sprintf( '<(%s)[^>]*?>((.*?)(<\/\1>))?', implode( '|', $args[ $type ] ) );
			$_replace = 'space' === $type ? ' $2 ' : ' ';

			$input = preg_replace( "/$_regex/si", $_replace, $input );
		}

		// phpcs:ignore, WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- $args defines stripping of 'script' and 'style'.
		return strip_tags( $input );
	}

	/**
	 * Cleans known parameters from image details.
	 *
	 * @since 4.0.0
	 * @since 4.0.2 Now finds smaller images when they're over 4K.
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
	public function s_image_details( array $details ) {

		if ( array_values( $details ) === $details )
			return $this->s_image_details_deep( $details );

		$defaults = [
			'url'    => '',
			'id'     => 0,
			'width'  => 0,
			'height' => 0,
			'alt'    => '',
		];

		list( $url, $id, $width, $height, $alt ) = array_values( array_merge( $defaults, $details ) );

		if ( ! $url ) return $defaults;

		$url = $this->s_url_relative_to_current_scheme( $url );

		if ( ! $url ) return $defaults;

		$width  = (int) $width;
		$height = (int) $height;

		if ( ! $width || ! $height )
			$width = $height = 0;

		if ( $width > 4096 || $height > 4096 ) {
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
			$alt = strlen( $alt ) > 420 ? $this->trim_excerpt( $alt, 0, 420 ) : $alt;
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
	public function s_image_details_deep( array $details_array ) {

		$cleaned_details = [];

		// Failsafe. Convert associative detailts to a multidimensional sequential array.
		if ( isset( $details_array['url'] ) )
			$details_array = [ $details_array ];

		foreach ( $details_array as $details ) {
			$cleaned_details[] = $this->s_image_details( $details );
		}

		return array_values(
			array_intersect_key(
				$cleaned_details,
				array_unique( array_filter( array_column( $cleaned_details, 'url' ) ) )
			)
		);
	}
}
