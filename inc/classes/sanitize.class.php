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
 * Class AutoDescription_Sanitize
 *
 * Sanitizes strings/arrays within the plugin.
 *
 * @since 2.2.4
 */
class AutoDescription_Sanitize extends AutoDescription_Adminpages {

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
	 * Register each of the settings with a sanitization filter type.
	 *
	 * @since 2.2.2
	 *
	 * @uses autodescription_add_option_filter() Assign filter to array of settings.
	 *
	 * @see AutoDescription_Sanitize::add_filter() Add sanitization filters to options.
	 */
	public function sanitizer_filters() {
		//* If this page doesn't store settings, no need to sanitize them
		if ( ! $this->settings_field )
			return;

		/**
		 * If this page doesn't parse the site options,
		 * There's no need to filter them on each request.
		 *
		 * Reverse call for something we want in our POST.
		 *
		 * @since 2.2.9
		 */
		if ( ! isset( $_POST ) || empty( $_POST ) || ! isset( $_POST[THE_SEO_FRAMEWORK_SITE_OPTIONS] ) || ! is_array( $_POST[THE_SEO_FRAMEWORK_SITE_OPTIONS] ) )
			return;

		$this->autodescription_add_option_filter(
			's_title_separator',
			$this->settings_field,
			array(
				'title_seperator', // NOTE: Typo
			)
		);

		$this->autodescription_add_option_filter(
			's_description_separator',
			$this->settings_field,
			array(
				'description_separator',
			)
		);

		$this->autodescription_add_option_filter(
			's_description',
			$this->settings_field,
			array(
				'homepage_description',
			)
		);

		$this->autodescription_add_option_filter(
			's_knowledge_type',
			$this->settings_field,
			array(
				'knowledge_person',
			)
		);

		$this->autodescription_add_option_filter(
			's_left_right',
			$this->settings_field,
			array(
				'title_location',
				'home_title_location',
			)
		);

		$this->autodescription_add_option_filter(
			's_one_zero',
			$this->settings_field,
			array(
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

				'homepage_noindex',
				'homepage_nofollow',
				'homepage_noarchive',

				'homepage_tagline',

				'shortlink_tag',

				'prev_next_posts',
				'prev_next_archives',

				'og_tags',
				'facebook_tags',
				'twitter_tags',

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
				'ping_yahoo',
			)
		);

		$this->autodescription_add_option_filter(
			's_absint',
			$this->settings_field,
			array(
			//	'home_author', @TODO
			)
		);

		$this->autodescription_add_option_filter(
			's_no_html',
			$this->settings_field,
			array(
				'homepage_title',
				'homepage_title_tagline',

				'facebook_appid',
				'google_verification',
				'bing_verification',

				'knowledge_name',
			)
		);

		$this->autodescription_add_option_filter(
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

		$this->autodescription_add_option_filter(
			's_url_query',
			$this->settings_field,
			array(
				'knowledge_linkedin',
			)
		);

		$this->autodescription_add_option_filter(
			's_twitter_name',
			$this->settings_field,
			array(
				'twitter_site',
				'twitter_creator',
			)
		);

		//* Special action filter.
		$this->autodescription_add_option_filter(
			's_one_zero_flush_rewrite',
			$this->settings_field,
			array(
				'sitemaps_output',
			)
		);

		//* Special action filter.
		$this->autodescription_add_option_filter(
			's_one_zero_flush_sitemap',
			$this->settings_field,
			array(
				'sitemaps_modified',
			)
		);

	}

	/**
	 * Registers option sanitation filter
	 *
	 * @since 2.2.2
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
	 *
	 * @param string $filter Sanitization filter type
	 * @param string $option Option key
	 * @param array|string $suboption Optional. Suboption key
	 * @return boolean Returns true when complete
	 *
	 * @thanks StudioPress (http://www.studiopress.com/) for some code.
	 */
	public function add_filter( $filter, $option, $suboption = null ) {

		if ( is_array( $suboption ) ) {
			foreach ( $suboption as $so ) {
				$this->options[$option][$so] = $filter;
			}
		} else if ( is_null( $suboption ) ) {
			$this->options[$option] = $filter;
		} else {
			$this->options[$option][$suboption] = $filter;
		}

		add_filter( 'sanitize_option_' . $option, array( $this, 'sanitize' ), 10, 2 );

		return true;
	}

	/**
	 * Sanitize a value, via the sanitization filter type associated with an option.
	 *
	 * @since 2.2.2
	 *
	 * @param mixed $new_value New value
	 * @param string $option Name of the option
	 * @return mixed Filtered, or unfiltered value
	 *
	 * @thanks StudioPress (http://www.studiopress.com/) for some code.
	 */
	public function sanitize( $new_value, $option ) {

		if ( !isset( $this->options[$option] ) ) {
			//* We are not filtering this option at all
			return $new_value;
		} else if ( is_string( $this->options[$option] ) ) {
			//* Single option value
			return $this->do_filter( $this->options[$option], $new_value, get_option( $option ) );
		} else if ( is_array( $this->options[$option] ) ) {
			//* Array of suboption values to loop through
			$old_value = get_option( $option );
			foreach ( $this->options[$option] as $suboption => $filter ) {
				$old_value[$suboption] = isset( $old_value[$suboption] ) ? $old_value[$suboption] : '';
				$new_value[$suboption] = isset( $new_value[$suboption] ) ? $new_value[$suboption] : '';
				$new_value[$suboption] = $this->do_filter( $filter, $new_value[$suboption], $old_value[$suboption] );
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
	 * @param string $filter Sanitization filter type
	 * @param string $new_value New value
	 * @param string $old_value Previous value
	 * @return mixed Returns filtered value, or submitted value if value is
	 * unfiltered.
	 *
	 * @thanks StudioPress (http://www.studiopress.com/) for some code.
	 */
	protected function do_filter( $filter, $new_value, $old_value ) {

		$available_filters = $this->get_available_filters();

		if ( ! in_array( $filter, array_keys( $available_filters ) ) )
			return $new_value;

		return call_user_func( $available_filters[$filter], $new_value, $old_value );
	}

	/**
	 * Return array of known sanitization filter types.
	 *
	 * Array can be filtered via 'the_seo_framework_available_sanitizer_filters' to let
	 * child themes and plugins add their own sanitization filters.
	 *
	 * @since 2.2.2
	 *
	 * @return array Array with keys of sanitization types, and values of the
	 * filter function name as a callback
	 *
	 * @thanks StudioPress (http://www.studiopress.com/) for some code.
	 */
	protected function get_available_filters() {

		$default_filters = array(
			's_left_right' 				=> array( $this, 's_left_right' 			),
			's_title_separator' 		=> array( $this, 's_title_separator' 		),
			's_description_separator' 	=> array( $this, 's_description_separator' 	),
			's_description' 			=> array( $this, 's_description' 			),
			's_knowledge_type'			=> array( $this, 's_knowledge_type'			),
			's_one_zero' 				=> array( $this, 's_one_zero' 				),
			's_one_zero_flush_rewrite'	=> array( $this, 's_one_zero_flush_rewrite'	),
			's_one_zero_flush_sitemap'	=> array( $this, 's_one_zero_flush_sitemap'	),
			's_no_html' 				=> array( $this, 's_no_html' 				),
			's_absint' 					=> array( $this, 's_absint' 				),
			's_safe_html' 				=> array( $this, 's_safe_html' 				),
			's_url' 					=> array( $this, 's_url' 					),
			's_url_query' 				=> array( $this, 's_url_query' 				),
			's_twitter_name' 			=> array( $this, 's_twitter_name' 			),
		);

		/**
		 * Filter the available sanitization filter types.
		 *
		 * @since 2.2.2
		 *
		 * @param array $default_filters Array with keys of sanitization types, and values of the filter function name as a callback
		 *
		 * New filter.
		 * @since 2.3.0
		 *
		 * Removed previous filter.
		 * @since 2.3.5
		 */
		return (array) apply_filters( 'the_seo_framework_available_sanitizer_filters', $default_filters );
	}

	/**
	 * Returns the title separator value string.
	 *
	 * @since 2.2.2
	 *
	 * @param mixed $new_value Should be identical to any of the $this->title_separator values
	 * @return string Title separator option
	 */
	protected function s_title_separator( $new_value ) {

		$title_separator = $this->title_separator;

		$key = array_key_exists( $new_value, $title_separator );

		if ( $key )
			return (string) $new_value;

		$previous = $this->get_field_value( 'title_seperator' ); // NOTE: Typo

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
		$description_separator = $this->title_separator;

		$key = array_key_exists( $new_value, $description_separator );

		if ( $key )
			return (string) $new_value;

		$previous = $this->get_field_value( 'description_separator' );

		return (string) $previous;
	}

	/**
	 * Returns a one-line sanitized description
	 *
	 * @since 2.4.4
	 *
	 * @param string $new_value The Description
	 * @return string One line sanitized description.
	 */
	protected function s_description( $new_value ) {

		$description = str_replace( array( "\r\n", "\r", "\n" ), "\n", $new_value );

		$lines = explode( "\n", $description );
		$new_lines = array();

		//* Remove line breaks
		foreach ( $lines as $i => $line ) {
			//* Don't add empty lines or paragraphs
			if ( ! empty( $line ) && '&nbsp;' !== $line )
				$new_lines[] = trim( $line ) . ' ';
		}

		$output = trim( implode( $new_lines ) );

		return (string) strip_tags( $output );
	}

	/**
	 * Returns the knowledge type value string.
	 *
	 * @since 2.2.8
	 *
	 * @param mixed $new_value Should be identical to any of the $person_organization values
	 * @return string title Knowledge type option
	 */
	protected function s_knowledge_type( $new_value ) {

		$person_organization = array( 'person', 'organization' );

		$key = array_key_exists( $new_value, $person_organization );

		if ( $key )
			return (string) $new_value;

		static $home_id = null;

		if ( ! isset( $home_id ) ) {
			// $home_id as false will flush blog front-page.
			$home_id = 'page' == get_option( 'show_on_front' ) ? get_option( 'page_on_front' ) : false;
			$this->delete_ld_json_transient( $home_id );
		}

		$previous = $this->get_field_value( 'knowledge_type' );

		return (string) $previous;
	}

	/**
	 * Returns left or right, for the separator location.
	 *
	 * @since 2.2.2
	 *
	 * @param mixed $new_value Should ideally be a string 'left' or 'right' passed in
	 * @return string left or right
	 */
	protected function s_left_right( $new_value ) {

		if ( (string) $new_value == 'left' || (string) $new_value == 'right' )
			return (string) $new_value;

		$previous = $this->get_field_value( 'title_location' );

		return (string) $previous;
	}

	/**
	 * Returns a 1 or 0, for all truthy / falsy values.
	 *
	 * Uses double casting. First, we cast to bool, then to integer.
	 *
	 * @since 2.2.2
	 *
	 * @param mixed $new_value Should ideally be a 1 or 0 integer passed in
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
	 * @param mixed $new_value Should ideally be a 1 or 0 integer passed in
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

		$this->wpmudev_domainmap_flush_fix( true );

		return (int) (bool) $new_value;
	}

	/**
	 * Returns a 1 or 0, for all truthy / falsy values.
	 *
	 * Uses double casting. First, we cast to bool, then to integer.
	 *
	 * Also flushes the sitemap.
	 *
	 * @since 2.2.9
	 *
	 * @param mixed $new_value Should ideally be a 1 or 0 integer passed in
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
	 * @param string $new_value String, possibly with HTML in it
	 * @return string String without HTML in it.
	 */
	protected function s_no_html( $new_value ) {
		return strip_tags( $new_value );
	}

	/**
	 * Makes URLs safe
	 *
	 * @since 2.2.2
	 *
	 * @param string $new_value String, a URL, possibly unsafe
	 *
	 * @return string String a safe URL without Query Arguments.
	 */
	protected function s_url( $new_value ) {

		static $home_id = null;

		if ( ! isset( $home_id ) ) {
			// $home_id as false will flush blog front-page.
			$home_id = 'page' == get_option( 'show_on_front' ) ? get_option( 'page_on_front' ) : false;
			$this->delete_ld_json_transient( $home_id );
		}

		/**
		 * Remove query strings
		 */
		$pattern 	= 	'/'
					.	'(\?|\&)' 	// 1: ? or &
					. 	'([^=]+)'	// 2: text until =
					.	'\='		// =
					.	'([^&]+)'	// 3: until & if found
					.	'/s'
					;

		$url = preg_replace( $pattern, '', $new_value );

		return esc_url_raw( $url );
	}

	/**
	 * Makes URLs safe
	 *
	 * @since 2.2.8
	 *
	 * @param string $new_value String, a URL, possibly unsafe
	 *
	 * @return string String a safe URL with Query Arguments.
	 */
	protected function s_url_query( $new_value ) {
		static $home_id = null;

		if ( ! isset( $home_id ) ) {
			// $home_id as false will flush blog front-page.
			$home_id = 'page' == get_option( 'show_on_front' ) ? get_option( 'page_on_front' ) : false;
			$this->delete_ld_json_transient( $home_id );
		}

		return esc_url_raw( $new_value );
	}

	/**
	 * Makes Email Addresses safe, via sanitize_email()
	 *
	 * @since 2.2.2
	 *
	 * @param string $new_value String, an email address, possibly unsafe
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
	 * @param string $new_value String with potentially unsafe HTML in it
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
	 * @param string $new_value String with potentially wrong Twitter username
	 * @return string String with 'correct' Twitter username
	 */
	protected function s_twitter_name( $new_value ) {

		if ( empty( $new_value ) )
			return (string) $new_value;

		$profile = trim(strip_tags( $new_value ));

		if ( substr( $profile, 0, 4 ) === 'http' ) {
			$path = str_replace( '/', '', parse_url( $profile, PHP_URL_PATH ) );
			$profile = !empty( $path ) ? '@' . $path : '';

			return (string) $profile;
		}

		if ( substr( $profile, 0, 1 ) !== '@' ) {
			$profile = '@' . $profile;
		}

		return (string) $profile;
	}

	/**
	 * Sanitize the Redirect URL
	 *
	 * @since 2.2.4
	 *
	 * @param string $new_value String with potentially unwanted redirect url
	 *
	 * @return string The Sanitized Redirect URL
	 */
	protected function s_redirect_url( $new_value ) {

		$url = strip_tags( $new_value );

		if ( ! empty( $url ) ) {

			/**
			 * New filter.
			 * @since 2.3.0
			 *
			 * Removed previous filter.
			 * @since 2.3.5
			 */
			$allow_external = (bool) apply_filters( 'the_seo_framework_allow_external_redirect', true );

			/**
			 * Sanitize the redirect URL to only a relative link and removes first slash
			 * Always do this if IS_HMPL
			 *
			 * @requires WP 4.1.0 and up to prevent adding upon itself.
			 */
			if ( ! $allow_external || ( defined( 'IS_HMPL' ) && IS_HMPL ) )
				$url = ltrim( wp_make_link_relative( $url ), '/' );

			//* URL pattern without path
			$pattern 	= 	'/'
						.	'((((http)(s)?)?)\:)?' 	// 1: maybe http: https:
						. 	'(\/\/)?'				// 2: maybe slash slash
						. 	'((www.)?)'				// 3: maybe www.
						.	'(.*\.[a-zA-Z0-9]*)'	// 4: any legal domain with tld
						.	'(?:\/)'				// 5: trailing slash
						.	'/'
						;

			//* If link is relative, make it full again
			if ( preg_match( $pattern, $url ) !== 1 ) {

				//* The url is a relative path
				$path = $url;

				$ismapped = '0';

				//* Do some extra work on domain mapping
				if ( $this->is_domainmapping_active() ) {
					global $wpdb,$blog_id;

					$mapped_key = 'wpmudev_mapped_domain_' . $blog_id;

					//* Check if the domain is mapped
					$mapped_domain = $this->object_cache_get( $mapped_key );
					if ( false === $mapped_domain ) {
						$mapped_domain = $wpdb->get_var( $wpdb->prepare( "SELECT domain FROM {$wpdb->base_prefix}domain_mapping WHERE blog_id = %d", $blog_id ) );
						$this->object_cache_set( $mapped_key, $mapped_domain, 3600 );
					}

					if ( !empty( $mapped_domain ) ) {
						//* Set that the domain is mapped
						$ismapped = '1';

						$scheme_key = 'wpmudev_mapped_scheme_' . $blog_id;

						//* Fetch scheme
						$mappedscheme = $this->object_cache_get( $scheme_key );
						if ( false === $mappedscheme ) {
							$mappedscheme = $wpdb->get_var( $wpdb->prepare( "SELECT scheme FROM {$wpdb->base_prefix}domain_mapping WHERE blog_id = %d", $blog_id ) );
							$this->object_cache_set( $scheme_key, $mappedscheme, 3600 );
						}

						if ( $mappedscheme === '1' ) {
							$scheme_full = 'https://';
							$scheme = 'https';
						} else {
							$scheme_full = 'http://';
							$scheme = 'http';
						}

						// Put it all together
						$url = trailingslashit( $scheme_full . $mapped_domain ) . $path;
					}
				}

				//* Non-mapped URL
				if ( $ismapped !== '1' ) {
					$url = home_url( add_query_arg( array(), $path ) );
					$scheme = is_ssl() ? 'https' : 'http';
				}

				$scheme = !empty( $scheme ) ? $scheme : '';

				$url = esc_url_raw( $url, $scheme );

			}
		}

		/**
		 * New filter.
		 * @since 2.3.0
		 *
		 * Removed previous filter.
		 * @since 2.3.5
		 */
		$noqueries = (bool) apply_filters( 'the_seo_framework_301_noqueries', true );

		/**
		 * Remove queries from the URL
		 *
		 * Returns plain home url if $allow_external is set to false and only a query has been supplied
		 * But that's okay. The url was rogue anyway :)
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
			 * Allow query string parameters. Warning: don't trust anyone :)
			 * XSS safe.
			 */
			$new_value = esc_url_raw( $url );
		}

		//* Save url
		return $new_value;
	}
}
