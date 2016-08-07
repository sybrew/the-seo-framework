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

defined( 'ABSPATH' ) or die;

/**
 * Class AutoDescription_Sanitize
 *
 * Sanitizes strings/arrays within the plugin.
 *
 * @since 2.2.4
 */
class AutoDescription_Sanitize extends AutoDescription_Adminpages {

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	private function __wakeup() { }

	/**
	 * Handle unapproachable invoked methods.
	 */
	public function __call( $name, $arguments ) {
		parent::__call( $name, $arguments );
	}

	/**
	 * Constructor, load parent constructor
	 */
	public function __construct() {
		parent::__construct();

		/**
		 * Sanitizes Site options
		 * @see siteoptions.class.php
		 */
		add_action( 'admin_init', array( $this, 'sanitizer_filters' ) );
	}

	/**
	 * Checks the SEO Settings page nonce. Returns false if nonce can't be found.
	 * Performs wp_die() when nonce verification fails.
	 *
	 * Never run a sensitive function when it's returning false. This means no nonce can be verified.
	 *
	 * @since 2.7.0
	 * @staticvar bool $verified.
	 *
	 * @return bool True if verified and matches. False if can't verify.
	 */
	public function verify_seo_settings_nonce() {

		static $validated = null;

		if ( isset( $validated ) )
			return $validated;

		//* If this page doesn't store settings, no need to sanitize them
		if ( ! $this->settings_field )
			return $validated = false;

		/**
		 * If this page doesn't parse the site options,
		 * There's no need to filter them on each request.
		 * Nonce is handled elsewhere. This function merely injects filters to the $_POST data.
		 *
		 * @since 2.2.9
		 */
		if ( empty( $_POST ) || ! isset( $_POST[ THE_SEO_FRAMEWORK_SITE_OPTIONS ] ) || ! is_array( $_POST[ THE_SEO_FRAMEWORK_SITE_OPTIONS ] ) )
			return $validated = false;

		check_admin_referer( $this->settings_field . '-options' );

		return $validated = true;
	}

	/**
	 * Register each of the settings with a sanitization filter type.
	 *
	 * @since 2.2.2
	 * @uses autodescription_add_option_filter() Assign filter to array of settings.
	 * @see AutoDescription_Sanitize::add_filter() Add sanitization filters to options.
	 */
	public function sanitizer_filters() {

		//* Verify update nonce.
		if ( false === $this->verify_seo_settings_nonce() )
			return;

		//* Update hidden options.
		$this->update_hidden_options_to_default();

		$this->add_filter(
			's_title_separator',
			$this->settings_field,
			array(
				'title_seperator', // NOTE: Typo
			)
		);

		$this->add_filter(
			's_description_separator',
			$this->settings_field,
			array(
				'description_separator',
			)
		);

		$this->add_filter(
			's_description',
			$this->settings_field,
			array(
				'homepage_description',
				'description_custom',
			)
		);

		$this->add_filter(
			's_title',
			$this->settings_field,
			array(
				'homepage_title',
				'homepage_title_tagline',

				'knowledge_name',
			)
		);

		$this->add_filter(
			's_knowledge_type',
			$this->settings_field,
			array(
				'knowledge_type',
			)
		);

		$this->add_filter(
			's_left_right',
			$this->settings_field,
			array(
				'title_location',
			)
		);

		$this->add_filter(
			's_left_right_home',
			$this->settings_field,
			array(
				'home_title_location',
			)
		);

		$this->add_filter(
			's_one_zero',
			$this->settings_field,
			array(
				'title_rem_additions',
				'title_rem_prefixes',

				'description_additions',
				'description_blogname',

				'noodp',
				'noydir',

				'category_noindex',
				'tag_noindex',
				'author_noindex',
				'date_noindex',
				'search_noindex',
				'attachment_noindex',
				'site_noindex',

				'category_nofollow',
				'tag_nofollow',
				'author_nofollow',
				'date_nofollow',
				'search_nofollow',
				'attachment_nofollow',
				'site_nofollow',

				'category_noarchive',
				'tag_noarchive',
				'author_noarchive',
				'date_noarchive',
				'search_noarchive',
				'attachment_noarchive',
				'site_noarchive',

				'paged_noindex',
				'home_paged_noindex',

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
				'googleplus_tags',

				'knowledge_output',

				'post_publish_time',
				'post_modify_time',

				'page_publish_time',
				'page_modify_time',

				'home_modify_time',
				'home_publish_time',

				'noodp',
				'noydir',

				'knowledge_logo',

				'sitemaps_robots',
				'ping_google',
				'ping_bing',
				'ping_yandex',

				'excerpt_the_feed',
				'source_the_feed',

				'ld_json_searchbox',
				'ld_json_sitename',
				'ld_json_breadcrumbs',
			)
		);

		$this->add_filter(
			's_absint',
			$this->settings_field,
			array(
			//	'home_author', @TODO
			)
		);

		$this->add_filter(
			's_no_html',
			$this->settings_field,
			array(
			)
		);

		/**
		 * @todo create content="code" stripper
		 * @priority low 2.9.0+
		 */
		$this->add_filter(
			's_no_html_space',
			$this->settings_field,
			array(
				'facebook_appid',

				'google_verification',
				'bing_verification',
				'yandex_verification',
				'pint_verification',
			)
		);

		$this->add_filter(
			's_url',
			$this->settings_field,
			array(
				'facebook_publisher',
				'facebook_author',

				'knowledge_facebook',
				'knowledge_twitter',
				'knowledge_gplus',
				'knowledge_instagram',
				'knowledge_youtube',
			//	'knowledge_myspace',
				'knowledge_pinterest',
				'knowledge_soundcloud',
				'knowledge_tumblr',
			)
		);

		$this->add_filter(
			's_url_query',
			$this->settings_field,
			array(
				'knowledge_linkedin',
			)
		);

		$this->add_filter(
			's_twitter_name',
			$this->settings_field,
			array(
				'twitter_site',
				'twitter_creator',
			)
		);

		$this->add_filter(
			's_twitter_card',
			$this->settings_field,
			array(
				'twitter_card',
			)
		);

		//* Special action filter.
		$this->add_filter(
			's_one_zero_flush_rewrite',
			$this->settings_field,
			array(
				'sitemaps_output',
			)
		);

		//* Special action filter.
		$this->add_filter(
			's_one_zero_flush_sitemap',
			$this->settings_field,
			array(
				'sitemaps_modified',
				'sitemap_timestamps',
			)
		);

	}

	/**
	 * Registers option sanitation filter
	 *
	 * @since 2.2.2
	 * @since 2.7.0 : No longer used internally.
	 *
	 * @param string $filter The filter to call (see AutoDescription_Siteoptions::$available_filters for options)
	 * @param string $option The WordPress option name
	 * @param string|array $suboption Optional. The suboption or suboptions you want to filter
	 *
	 * @return true
	 */
	public function autodescription_add_option_filter( $filter, $option, $suboption = null ) {
		return $this->add_filter( $filter, $option, $suboption );
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
	 *
	 * @param string $filter Sanitization filter type
	 * @param string $option Option key
	 * @param array|string $suboption Optional. Suboption key
	 * @return boolean Returns true when complete
	 */
	public function add_filter( $filter, $option, $suboption = null ) {

		$this->set_option_filter( $filter, $option, $suboption );

		add_filter( 'sanitize_option_' . $option, array( $this, 'sanitize' ), 10, 2 );

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
	 * @param string $filter Sanitization filter type
	 * @param string $option Option key
	 * @param array|string $suboption Optional. Suboption key
	 * @param bool $get Whether to retrieve cache.
	 * @return boolean Returns true when complete
	 */
	protected function set_option_filter( $filter, $option, $suboption = null, $get = false ) {

		static $options = array();

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
	 * @param mixed $new_value New value
	 * @param string $option Name of the option
	 * @return mixed Filtered, or unfiltered value
	 */
	public function sanitize( $new_value, $option ) {

		$filters = $this->get_option_filters();

		if ( ! isset( $filters[ $option ] ) ) {
			//* We are not filtering this option at all
			return $new_value;
		} elseif ( is_string( $filters[ $option ] ) ) {
			//* Single option value
			return $this->do_filter( $filters[ $option ], $new_value, get_option( $option ) );
		} elseif ( is_array( $filters[ $option ] ) ) {
			//* Array of suboption values to loop through
			$old_value = get_option( $option );
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
	 * @return mixed Returns filtered value, or submitted value if value is
	 * unfiltered.
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

		$default_filters = array(
			's_left_right' 				=> array( $this, 's_left_right' 			),
			's_left_right_home' 		=> array( $this, 's_left_right_home' 		),
			's_title_separator' 		=> array( $this, 's_title_separator' 		),
			's_description_separator' 	=> array( $this, 's_description_separator' 	),
			's_description' 			=> array( $this, 's_description' 			),
			's_title' 					=> array( $this, 's_title' 					),
			's_knowledge_type'			=> array( $this, 's_knowledge_type'			),
			's_one_zero' 				=> array( $this, 's_one_zero' 				),
			's_one_zero_flush_rewrite'	=> array( $this, 's_one_zero_flush_rewrite'	),
			's_one_zero_flush_sitemap'	=> array( $this, 's_one_zero_flush_sitemap'	),
			's_no_html' 				=> array( $this, 's_no_html' 				),
			's_no_html_space' 			=> array( $this, 's_no_html_space' 			),
			's_absint' 					=> array( $this, 's_absint' 				),
			's_safe_html' 				=> array( $this, 's_safe_html' 				),
			's_url' 					=> array( $this, 's_url' 					),
			's_url_query' 				=> array( $this, 's_url_query' 				),
			's_twitter_name' 			=> array( $this, 's_twitter_name' 			),
			's_twitter_card' 			=> array( $this, 's_twitter_card' 			),
		);

		/**
		 * Filter the available sanitization filter types.
		 *
		 * @since 2.2.2
		 *
		 * Applies filters the_seo_framework_available_sanitizer_filters : array
		 * 		@param array $default_filters Array with keys of sanitization types,
		 *		and values of the filter function name as a callback
		 */
		return (array) apply_filters( 'the_seo_framework_available_sanitizer_filters', $default_filters );
	}

	/**
	 * Returns the title separator value string.
	 *
	 * @since 2.2.2
	 *
	 * @param mixed $new_value Should be identical to any of the $this->get_separator_list() values
	 * @return string Title separator option
	 */
	protected function s_title_separator( $new_value ) {

		$title_separator = $this->get_separator_list();

		$key = array_key_exists( $new_value, $title_separator );

		if ( $key )
			return (string) $new_value;

		$previous = $this->get_field_value( 'title_seperator' ); // NOTE: Typo

		//* Fallback to default if empty.
		if ( empty( $previous ) )
			$previous = $this->get_default_option( 'title_seperator' );

		return (string) $previous;
	}

	/**
	 * Returns the description separator value string.
	 *
	 * @since 2.2.2
	 *
	 * @param mixed $new_value Should be identical to any of the $this->description_separator values
	 * @return string Description separator option
	 */
	protected function s_description_separator( $new_value ) {

		//* Use the same as title_separator
		$description_separator = $this->get_separator_list();

		$key = array_key_exists( $new_value, $description_separator );

		if ( $key )
			return (string) $new_value;

		$previous = $this->get_field_value( 'description_separator' );

		//* Fallback to default if empty.
		if ( empty( $previous ) )
			$previous = $this->get_default_option( 'description_separator' );

		return (string) $previous;
	}

	/**
	 * Returns a one-line sanitized description
	 *
	 * @since 2.5.0
	 * @since 2.6.6 Removes duplicated spaces.
	 *
	 * @param string $new_value The Description.
	 * @return string One line sanitized description.
	 */
	protected function s_description( $new_value ) {

		$description = str_replace( array( "\r\n", "\r", "\n" ), "\n", $new_value );

		$lines = explode( "\n", $description );
		$new_lines = array();

		//* Remove line breaks
		foreach ( $lines as $i => $line ) {
			//* Don't add empty lines or paragraphs
			if ( $line && '&nbsp;' !== $line )
				$new_lines[] = trim( $line ) . ' ';
		}

		$description = trim( implode( $new_lines ) );

		$i = 0;
		//* Run twice at most, to catch uneven multiple spaces.
		do {
			$description = str_replace( '  ', ' ', $description );
			$i++;
		} while ( strpos( $description, '  ' ) && $i <= 2 );

		return (string) strip_tags( $description );
	}

	/**
	 * Returns a sanitized and trimmed title.
	 *
	 * @since 2.5.2
	 *
	 * @param string $new_value The Title.
	 * @return string Sanitized and trimmed title.
	 */
	protected function s_title( $new_value ) {

		$title = esc_html( $new_value );
		$title = trim( $title );

		return (string) strip_tags( $title );
	}

	/**
	 * Returns the knowledge type value string.
	 *
	 * @since 2.2.8
	 *
	 * @param mixed $new_value Should be identical to any of the $person_organization values.
	 * @return string title Knowledge type option
	 */
	protected function s_knowledge_type( $new_value ) {

		if ( 'person' === $new_value || 'organization' === $new_value )
			return (string) $new_value;

		$previous = $this->get_field_value( 'knowledge_type' );

		return (string) $previous;
	}

	/**
	 * Returns left or right, for the separator location.
	 *
	 * @since 2.2.2
	 *
	 * @param mixed $new_value Should ideally be a string 'left' or 'right' passed in.
	 * @return string left or right
	 */
	protected function s_left_right( $new_value ) {

		if ( 'left' === $new_value || 'right' === $new_value )
			return (string) $new_value;

		$previous = $this->get_field_value( 'title_location' );

		//* Fallback if previous is also empty.
		if ( empty( $previous ) )
			$previous = $this->get_default_option( 'title_location' );

		return (string) $previous;
	}

	/**
	 * Returns left or right, for the home separator location.
	 *
	 * @since 2.5.2
	 *
	 * @param mixed $new_value Should ideally be a string 'left' or 'right' passed in.
	 * @return string left or right
	 */
	protected function s_left_right_home( $new_value ) {

		if ( 'left' === $new_value || 'right' === $new_value )
			return (string) $new_value;

		$previous = $this->get_field_value( 'home_title_location' );

		//* Fallback if previous is also empty.
		if ( empty( $previous ) )
			$previous = $this->get_default_option( 'home_title_location' );

		return (string) $previous;
	}

	/**
	 * Returns a 1 or 0, for all truthy / falsy values.
	 *
	 * Uses double casting. First, we cast to bool, then to integer.
	 *
	 * @since 2.2.2
	 *
	 * @param mixed $new_value Should ideally be a 1 or 0 integer passed in.
	 * @return integer 1 or 0.
	 */
	protected function s_one_zero( $new_value ) {
		return (int) (bool) $new_value;
	}

	/**
	 * Returns a 1 or 0, for all truthy / falsy values.
	 *
	 * Uses double casting. First, we cast to bool, then to integer.
	 *
	 * Also flushes rewrite rules.
	 *
	 * @since 2.2.9
	 *
	 * @param mixed $new_value Should ideally be a 1 or 0 integer passed in.
	 * @return integer 1 or 0.
	 */
	protected function s_one_zero_flush_rewrite( $new_value ) {

		/**
		 * Don't call functions anymore. Although it was after admin_init.
		 * It was too early for some plugins.
		 *
		 * @since 2.3.0
		 */
		if ( (int) (bool) $new_value ) {
			$this->enqueue_rewrite_activate( true );
		} else {
			$this->enqueue_rewrite_deactivate( true );
		}

		return (int) (bool) $new_value;
	}

	/**
	 * Returns a 1 or 0, for all truthy / falsy values.
	 * Uses double casting. First, we cast to bool, then to integer.
	 * Also flushes the sitemap.
	 *
	 * @since 2.2.9
	 * @staticvar bool $flushed
	 *
	 * @param mixed $new_value Should ideally be a 1 or 0 integer passed in.
	 * @return integer 1 or 0.
	 */
	protected function s_one_zero_flush_sitemap( $new_value ) {

		static $flushed = null;

		if ( ! isset( $flushed ) )
			$this->delete_sitemap_transient();

		$flushed = true;

		return (int) (bool) $new_value;
	}

	/**
	 * Returns a positive integer value.
	 *
	 * @since 2.2.2
	 *
	 * @param mixed $new_value Should ideally be a positive integer.
	 * @return integer Positive integer.
	 */
	protected function s_absint( $new_value ) {
		return absint( $new_value );
	}

	/**
	 * Removes HTML tags from string.
	 *
	 * @since 2.2.2
	 *
	 * @param string $new_value String, possibly with HTML in it.
	 * @return string String without HTML in it.
	 */
	protected function s_no_html( $new_value ) {
		return strip_tags( $new_value );
	}

	/**
	 * Removes HTML tags and line breaks from string.
	 *
	 * @since 2.5.2
	 *
	 * @param string $new_value String, possibly with HTML and spaces in it.
	 * @return string String without HTML and breaks in it.
	 */
	protected function s_no_html_space( $new_value ) {
		return str_replace( ' ', '', strip_tags( $new_value ) );
	}

	/**
	 * Makes URLs safe
	 *
	 * @since 2.2.2
	 *
	 * @param string $new_value String, a URL, possibly unsafe.
	 * @return string String a safe URL without Query Arguments.
	 */
	protected function s_url( $new_value ) {

		$this->delete_front_ld_json_transient();

		/**
		 * If queries have been tokenized, take the value before the query args.
		 * Otherwise it's empty, so take the current value.
		 */
		$no_query_url = strtok( $new_value, '?' );
		$url = $no_query_url ? $no_query_url : $new_value;

		return esc_url_raw( $url );
	}

	/**
	 * Makes URLs safe and removes query args.
	 *
	 * @since 2.2.8
	 *
	 * @param string $new_value String, a URL, possibly unsafe.
	 * @return string String a safe URL with Query Arguments.
	 */
	protected function s_url_query( $new_value ) {

		$this->delete_front_ld_json_transient();

		return esc_url_raw( $new_value );
	}

	/**
	 * Makes Email Addresses safe, via sanitize_email()
	 *
	 * @since 2.2.2
	 *
	 * @param string $new_value String, an email address, possibly unsafe.
	 * @return string String a safe email address
	 */
	protected function s_email_address( $new_value ) {
		return sanitize_email( $new_value );
	}

	/**
	 * Removes unsafe HTML tags, via wp_kses_post().
	 *
	 * @since 2.2.2
	 *
	 * @param string $new_value String with potentially unsafe HTML in it.
	 * @return string String with only safe HTML in it
	 */
	protected function s_safe_html( $new_value ) {
		return wp_kses_post( $new_value );
	}

	/**
	 * Parses Twitter name and site. Adds @ if it wasn't supplied.
	 * Parses URL to path and adds @ if URL is given.
	 *
	 * @since 2.2.2
	 *
	 * @param string $new_value String with potentially wrong Twitter username.
	 * @return string String with 'correct' Twitter username
	 */
	protected function s_twitter_name( $new_value ) {

		if ( empty( $new_value ) )
			return (string) $new_value;

		$profile = trim( strip_tags( $new_value ) );

		if ( 'http' === substr( $profile, 0, 4 ) ) {
			$parsed_url = wp_parse_url( $profile );
			$path = isset( $parsed_url['path'] ) ? str_replace( '/', '', $parsed_url['path'] ) : '';
			$profile = $path ? '@' . $path : '';

			return (string) $profile;
		}

		if ( '@' !== substr( $profile, 0, 1 ) ) {
			$profile = '@' . $profile;
		}

		return (string) $profile;
	}

	/**
	 * Parses Twitter Card radio input. Fills in default if incorrect value is supplied.
	 * Falls back to previous value if empty. If previous value is empty if will go to default.
	 *
	 * @since 2.5.2
	 *
	 * @param string $new_value String with potentially wrong option value.
	 * @return string Sanitized twitter card type.
	 */
	protected function s_twitter_card( $new_value ) {

		//* Fetch Twitter card array.
		$card = $this->get_twitter_card_types();

		$key = array_key_exists( $new_value, $card );

		if ( $key )
			return (string) $new_value;

		$previous = $this->get_field_value( 'twitter_card' );

		if ( empty( $previous ) )
			$previous = $this->get_default_option( 'twitter_card' );

		return (string) $previous;
	}

	/**
	 * Converts full URL paths to absolute paths.
	 *
	 * Removes the http or https protocols and the domain. Keeps the path '/' at the
	 * beginning, so it isn't a true relative link, but from the web root base.
	 *
	 * @since 2.6.5
	 *
	 * @param string $url Full Path URL or relative URL.
	 * @return string Abolute path.
	 */
	protected function s_relative_url( $url ) {
		return ltrim( preg_replace( '|^(https?:)?//[^/]+(/.*)|i', '$2', $url ), ' \//' );
	}

	/**
	 * Sanitize the Redirect URL
	 *
	 * @since 2.2.4
	 *
	 * @param string $new_value String with potentially unwanted redirect URL.
	 * @return string The Sanitized Redirect URL
	 */
	protected function s_redirect_url( $new_value ) {

		$url = strip_tags( $new_value );

		if ( $url ) {

			$allow_external = $this->allow_external_redirect();

			/**
			 * Sanitize the redirect URL to only a relative link and removes first slash
			 * @requires WP 4.1.0 and up to prevent adding upon itself.
			 */
			if ( ! $allow_external )
				$url = $this->s_relative_url( $url );

			//* Find a path.
			if ( _wp_can_use_pcre_u() ) {
				//* URL pattern excluding path.
				$pattern 	= '/'
							. '((((http)(s)?)?)\:)?' 	// 1: maybe http: https:
							. '(\/\/)?'				// 2: maybe slash slash
							. '((www.)?)'				// 3: maybe www.
							. '(.*\.[a-zA-Z0-9]*)'	// 4: any legal domain with tld
							. '(?:\/)?'				// 5: trailing slash
							. '/'
							;

				$is_path = ! preg_match( $pattern, $url );
			} else {
				$parsed_url = wp_parse_url( $url );
				$is_path = ! isset( $parsed_url['host'] ) && isset( $parsed_url['path'] );
			}

			//* If link is relative, make it full again
			if ( $is_path ) {

				//* The url is a relative path
				$path = $url;

				//* Try WPMUdev Domain Mapping.
				$wpmu_url = $this->the_url_wpmudev_domainmap( $path, true );
				if ( $wpmu_url && is_array( $wpmu_url ) ) {
					$url = $wpmu_url[0];
					$scheme = $wpmu_url[1];
				}

				//* Try Donncha Domain Mapping.
				if ( ! isset( $scheme ) ) {
					$dm_url = $this->the_url_donncha_domainmap( $path, true );
					if ( $dm_url && is_array( $dm_url ) ) {
						$url = $dm_url[0];
						$scheme = $dm_url[1];
					}
				}

				//* Everything else.
				if ( ! isset( $scheme ) ) {
					$url = $this->the_home_url_from_cache( true ) . ltrim( $path, ' /' );
					$scheme = is_ssl() ? 'https' : 'http';
				}

				//* When nothing is found, fall back on WP defaults (is_ssl).
				$scheme = isset( $scheme ) ? $scheme : '';

				$url = $this->set_url_scheme( $url, $scheme );
			}
		}

		/**
		 * Applies filters the_seo_framework_301_noqueries : bool remove query args from 301
		 * @since 2.5.0
		 */
		$noqueries = (bool) apply_filters( 'the_seo_framework_301_noqueries', true );

		/**
		 * Remove queries from the URL
		 *
		 * Returns plain Home URL if $allow_external is set to false and only a query has been supplied
		 * But that's okay. The URL was rogue anyway :)
		 */
		if ( $noqueries ) {
			/**
			 * Remove query args
			 *
			 * @see AutoDescription_Sanitize::s_url
			 * @since 2.2.4
			 */
			$new_value = $this->s_url( $url );
		} else {
			/**
			 * Allow query string parameters. XSS safe.
			 */
			$new_value = esc_url_raw( $url );
		}

		//* Save url
		return $new_value;
	}
}
