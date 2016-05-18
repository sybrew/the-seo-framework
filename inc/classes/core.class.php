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
 * Class AutoDescription_Core
 *
 * Initializes the plugin & Holds plugin core functions.
 *
 * @since 2.6.0
 */
class AutoDescription_Core {

	/**
	 * Constructor, just be there for me when I need you.
	 * Latest Class. Doesn't have parent.
	 */
	public function __construct() {

		add_action( 'current_screen', array( $this, 'post_type_support' ), 0 );

		/**
		 * Add plugin links to the plugin activation page.
		 * @since 2.2.8
		 */
		add_filter( 'plugin_action_links_' . THE_SEO_FRAMEWORK_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ), 10, 2 );

	}

	/**
	 * Proportionate dimensions based on Width and Height.
	 * AKA Aspect Ratio.
	 *
	 * @param int $i The dimension to resize.
	 * @param int $r1 The deminsion that determines the ratio.
	 * @param int $r2 The dimension to proportionate to.
	 *
	 * @since 2.6.0
	 *
	 * @return int The proportional dimension, rounded.
	 */
	public function proportionate_dimensions( $i, $r1, $r2 ) {

		//* Get aspect ratio.
		$ar = $r1 / $r2;

		$i = $i / $ar;
		return round( $i );
	}

	/**
	 * Adds post type support
	 *
	 * Applies filters the_seo_framework_supported_post_types : The supported post types.
	 * @since 2.3.1
	 *
	 * @since 2.1.6
	 */
	public function post_type_support() {

		/**
		 * Added product post type.
		 *
		 * @since 2.3.1
		 */
		$defaults = array(
			'post', 'page',
			'product',
			'forum', 'topic',
			'jetpack-testimonial', 'jetpack-portfolio',
		);

		$post_types = (array) apply_filters( 'the_seo_framework_supported_post_types', $defaults );

		$types = wp_parse_args( $defaults, $post_types );

		foreach ( $types as $type )
			add_post_type_support( $type, array( 'autodescription-meta' ) );

	}

	/**
	 * Adds link from plugins page to SEO Settings page.
	 *
	 * @param array $links The current links.
	 *
	 * @since 2.2.8
	 */
	public function plugin_action_links( $links = array() ) {

		$framework_links = array();

		if ( $this->load_options )
			$framework_links['settings'] = '<a href="' . esc_url( admin_url( 'admin.php?page=' . $this->page_id ) ) . '">' . __( 'SEO Settings', 'autodescription' ) . '</a>';

		$framework_links['home'] = '<a href="'. esc_url( 'https://theseoframework.com/' ) . '" target="_blank">' . _x( 'Plugin Home', 'As in: The Plugin Home Page', 'autodescription' ) . '</a>';

		return array_merge( $framework_links, $links );
	}

	/**
	 * Returns the front page ID, if home is a page.
	 *
	 * @since 2.6.0
	 *
	 * @return int the ID.
	 */
	public function get_the_front_page_ID() {

		static $front_id = null;

		if ( isset( $front_id ) )
			return $front_id;

		return $front_id = $this->has_page_on_front() ? (int) get_option( 'page_on_front' ) : 0;
	}

	/**
	 * Generate dismissible notice.
	 *
	 * @param $message The notice message.
	 * @param $type The notice type : 'updated', 'error', 'warning'
	 *
	 * @since 2.6.0
	 */
	public function generate_dismissible_notice( $message = '', $type = 'updated' ) {

		if ( empty( $message ) )
			return '';

		if ( 'warning' === $type )
			$type = 'notice-warning';

		$notice = '<div class="notice ' . $type . ' seo-notice"><p>';
		$notice .= '<a class="hide-if-no-js autodescription-dismiss" title="' . __( 'Dismiss', 'AutoDescription' ) . '"></a>';
		$notice .= '<strong>' . $message . '</strong>';
		$notice .= '</p></div>';

		return $notice;
	}

	/**
	 * Mark up content with code tags.
	 *
	 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
	 *
	 * @since 2.0.0
	 *
	 * @param string $content Content to be wrapped in code tags.
	 *
	 * @return string Content wrapped in code tags.
	 */
	public function code_wrap( $content ) {
		return '<code>' . esc_html( $content ) . '</code>';
	}

	/**
	 * Mark up content with code tags.
	 *
	 * Escapes no HTML.
	 *
	 * @since 2.2.2
	 *
	 * @param string $content Content to be wrapped in code tags.
	 *
	 * @return string Content wrapped in code tags.
	 */
	public function code_wrap_noesc( $content ) {
		return '<code>' . $content . '</code>';
	}

	/**
	 * Return custom field post meta data.
	 *
	 * Return only the first value of custom field. Return false if field is
	 * blank or not set.
	 *
	 * @since 2.0.0
	 *
	 * @param string $field	Custom field key.
	 * @param int $post_id	The post ID
	 *
	 * @return string|boolean Return value or false on failure.
	 *
	 * @thanks StudioPress (http://www.studiopress.com/) for some code.
	 *
	 * @staticvar array $field_cache
	 * @since 2.2.5
	 */
	public function get_custom_field( $field, $post_id = null ) {

		//* No field has been provided.
		if ( empty( $field ) )
			return false;

		//* Setup cache.
		static $field_cache = array();

		//* Check field cache.
		if ( isset( $field_cache[$field][$post_id] ) )
			//* Field has been cached.
			return $field_cache[$field][$post_id];

		if ( null === $post_id || empty( $post_id ) )
			$post_id = $this->get_the_real_ID();

		if ( null === $post_id || empty( $post_id ) )
			return '';

		$custom_field = get_post_meta( $post_id, $field, true );

		// If custom field is empty, return null.
		if ( empty( $custom_field ) )
			$field_cache[$field][$post_id] = '';

		//* Render custom field, slashes stripped, sanitized if string
		$field_cache[$field][$post_id] = is_array( $custom_field ) ? stripslashes_deep( $custom_field ) : stripslashes( wp_kses_decode_entities( $custom_field ) );

		return $field_cache[$field][$post_id];
	}

	/**
	 * Google docs language determinator.
	 *
	 * @since 2.2.2
	 *
	 * @staticvar string $language
	 *
	 * @return string language code
	 */
	protected function google_language() {

		/**
		 * Cache value
		 * @since 2.2.4
		 */
		static $language = null;

		if ( isset( $language ) )
			return $language;

		//* Language shorttag to be used in Google help pages,
		$language = _x( 'en', 'e.g. en for English, nl for Dutch, fi for Finish, de for German', 'autodescription' );

		return $language;
	}

	/**
	 * Whether to allow external redirect through the 301 redirect option.
	 *
	 * Applies filters the_seo_framework_allow_external_redirect : bool
	 * @staticvar bool $allowed
	 *
	 * @since 2.6.0
	 *
	 * @return bool Whether external redirect is allowed.
	 */
	public function allow_external_redirect() {

		static $allowed = null;

		if ( isset( $allowed ) )
			return $allowed;

		return $allowed = (bool) apply_filters( 'the_seo_framework_allow_external_redirect', true );
	}

	/**
	 * Object cache set wrapper.
	 * Applies filters 'the_seo_framework_use_object_cache' : Disable object
	 * caching for this plugin, when applicable.
	 *
	 * @param string $key The Object cache key.
	 * @param mixed $data The Object cache data.
	 * @param int $expire The Object cache expire time.
	 * @param string $group The Object cache group.
	 *
	 * @since 2.4.3
	 *
	 * @return bool true on set, false when disabled.
	 */
	public function object_cache_set( $key, $data, $expire = 0, $group = 'the_seo_framework' ) {

		if ( $this->use_object_cache )
			return wp_cache_set( $key, $data, $group, $expire );

		return false;
	}

	/**
	 * Object cache get wrapper.
	 * Applies filters 'the_seo_framework_use_object_cache' : Disable object
	 * caching for this plugin, when applicable.
	 *
	 * @param string $key The Object cache key.
	 * @param string $group The Object cache group.
	 * @param bool $force Whether to force an update of the local cache.
	 * @param bool $found Whether the key was found in the cache. Disambiguates a return of false, a storable value.
	 *
	 * @since 2.4.3
	 *
	 * @return mixed wp_cache_get if object caching is allowed. False otherwise.
	 */
	public function object_cache_get( $key, $group = 'the_seo_framework', $force = false, &$found = null ) {

		if ( $this->use_object_cache )
			return wp_cache_get( $key, $group, $force, $found );

		return false;
	}

	/**
	 * Faster way of doing an in_array search compared to default PHP behavior.
	 * @NOTE only to show improvement with large arrays. Might slow down with small arrays.
	 * @NOTE can't do type checks. Always assume the comparing value is a string.
	 *
	 * @uses array_flip()
	 * @uses isset()
	 *
	 * @since 2.5.2
	 *
	 * @param string|array $needle The needle(s) to search for
	 * @param array $array The single dimensional array to search in.
	 *
	 * @return bool true if value is in array.
	 */
	public function in_array( $needle, $array ) {

		$array = array_flip( $array );

		if ( is_string( $needle ) ) {
			if ( isset( $array[$needle] ) )
				return true;
		} else if ( is_array( $needle ) ) {
			foreach ( $needle as $str ) {
				if ( isset( $array[$str] ) )
					return true;
			}
		}

		return false;
	}

	/**
	 * Checks if the string input is exactly '1'.
	 *
	 * @param string $value The value to check.
	 *
	 * @since 2.6.0
	 * @return bool true if value is '1'
	 */
	public function is_checked( $value ) {

		if ( '1' === $value )
			return true;

		return false;
	}

	/**
	 * Checks if the option is used and checked.
	 *
	 * @param string $option The option name.
	 *
	 * @since 2.6.0
	 * @return bool Option is checked.
	 */
	public function is_option_checked( $option ) {

		$option = $this->get_option( $option );

		if ( $this->is_checked( $option ) )
			return true;

		return false;
	}

	/**
	 * Checks if blog is public through WordPress core settings.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 *
	 * @return bool True is blog is public.
	 */
	public function is_blog_public() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		if ( '1' === get_option( 'blog_public' ) )
			return $cache = true;

		return $cache = false;
	}

	/**
	 * Whether the current blog is spam or deleted.
	 * Multisite Only.
	 *
	 * @since 2.6.0
	 *
	 * @global object $current_blog. NULL on single site.
	 *
	 * @return bool Current blog is spam.
	 */
	public function current_blog_is_spam_or_deleted() {
		global $current_blog;

		if ( isset( $current_blog ) && ( '1' === $current_blog->spam || '1' === $current_blog->deleted ) )
			return true;

		return false;
	}

	/**
	 * Whether to lowercase the noun or keep it UCfirst.
	 * Depending if language is German.
	 *
	 * @staticvar array $lowercase Contains nouns.
	 * @since 2.6.0
	 *
	 * @return string The maybe lowercase noun.
	 */
	public function maybe_lowercase_noun( $noun ) {

		static $lowercase = array();

		if ( isset( $lowercase[$noun] ) )
			return $lowercase[$noun];

		return $lowercase[$noun] = $this->check_wp_locale( 'de' ) ? $noun : strtolower( $noun );
	}

	/**
	 * The minimum role required to
	 *
	 * Applies filter 'the_seo_framework_settings_capability' : string
	 * This filter changes the minimum role for viewing and editing the plugin's settings.
	 *
	 * @since 2.6.0
	 * @access private
	 *
	 * @return string The minimum required capability for SEO Settings.
	 */
	public function settings_capability() {
		return (string) apply_filters( 'the_seo_framework_settings_capability', 'manage_options' );
	}

	/**
	 * Returns the SEO Settings page URL.
	 *
	 * @since 2.6.0
	 *
	 * @return string The escaped SEO Settings page URL.
	 */
	public function seo_settings_page_url() {

		if ( $this->load_options ) {
			//* Options are allowed to be loaded.

			$url = html_entity_decode( menu_page_url( $this->page_id, 0 ) );

			return esc_url( $url );
		}

		return '';
	}

	/**
	 * Returns the PHP timezone compatible string.
	 * UTC offsets are unreliable.
	 *
	 * @since 2.6.0
	 *
	 * @param bool $guess : If true, the timezone will be guessed from the
	 * WordPress core gmt_offset option.
	 *
	 * @return string|empty PHP Timezone String.
	 */
	public function get_timezone_string( $guess = false ) {

		$tzstring = get_option( 'timezone_string' );

		if ( false !== strpos( $tzstring, 'Etc/GMT' ) )
			$tzstring = '';

		if ( $guess && empty( $tzstring ) ) {
			$offset = get_option( 'gmt_offset' );
			$tzstring = $this->get_tzstring_from_offset( $offset );
		}

		return $tzstring;
	}

	/**
	 * Fetches the Timezone String from given offset.
	 *
	 * @since 2.6.0
	 *
	 * @param int $offset The GMT offzet.
	 *
	 * @return string PHP Timezone String.
	 */
	protected function get_tzstring_from_offset( $offset = 0 ) {

		$seconds = round( $offset * 60 * 60 );

		//* Try Daylight savings.
		$tzstring = timezone_name_from_abbr( '', $seconds, 1 );
		/**
		 * PHP bug workaround.
		 * @link https://bugs.php.net/bug.php?id=44780
		 */
		if ( false === $tzstring )
			$tzstring = timezone_name_from_abbr( '', $seconds, 0 );

		return $tzstring;
	}

	/**
	 * Sets and resets the timezone.
	 *
	 * @since 2.6.0
	 *
	 * @param string $tzstring Optional. The PHP Timezone string. Best to leave empty to always get a correct one.
	 * @link http://php.net/manual/en/timezones.php
	 * @param bool $reset Whether to reset to default. Ignoring first parameter.
	 *
	 * @return bool True on success. False on failure.
	 */
	public function set_timezone( $tzstring = '', $reset = false ) {

		static $old_tz = null;

		if ( is_null( $old_tz ) ) {
			$old_tz = date_default_timezone_get();
			if ( empty( $old_tz ) )
				$old_tz = 'UTC';
		}

		if ( $reset )
			return date_default_timezone_set( $old_tz );

		if ( empty( $tzstring ) )
			$tzstring = $this->get_timezone_string( true );

		return date_default_timezone_set( $tzstring );
	}

	/**
	 * Resets the timezone to default or UTC.
	 *
	 * @since 2.6.0
	 *
	 * @return bool True on success. False on failure.
	 */
	public function reset_timezone() {
		return $this->set_timezone( '', true );
	}

}
