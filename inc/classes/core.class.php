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
 * Class AutoDescription_Core
 *
 * Initializes the plugin & Holds plugin core functions.
 *
 * @since 2.6.0
 */
class AutoDescription_Core {

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	private function __wakeup() { }

	/**
	 * Cloning of this class is forbidden.
	 */
	private function __clone() { }

	/**
	 * __get() deprecation handler.
	 * @since 2.7.0
	 * @return mixed $var The object variable.
	 */
	public function __get( $name ) {

		switch ( $name ) :
			case 'pagehook' :
				$this->_deprecated_function( 'the_seo_framework()->' . esc_html( $name ), '2.7.0', 'the_seo_framework()->seo_settings_page_hook' );
				return $this->seo_settings_page_hook;
				break;

			default:
				break;
		endswitch;

		//* Invoke default error.
		return $this->$name;
	}

	/**
	 * Handle unapproachable invoked methods.
	 * @return void
	 */
	public function __call( $name, $arguments ) {
		$_this = the_seo_framework();
		$_this->_inaccessible_p_or_m( 'the_seo_framework()->' . esc_html( $name ) . '()' );
		return;
	}

	/**
	 * Constructor. Loads actions and filters.
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
	 * Fetches files based on input to reduce memory overhead.
	 * Passes on input vars.
	 *
	 * @param string $view The file name.
	 * @param array $args The arguments to be supplied within the file name.
	 * 		Each array key is converted to a variable with its value attached.
	 * @param string $instance The instance suffix to call back upon.
	 *
	 * @credits Akismet For some code.
	 */
	protected function get_view( $view, array $args = array(), $instance = 'main' ) {

		foreach ( $args as $key => $val )
			$$key = $val;

		$file = THE_SEO_FRAMEWORK_DIR_PATH_VIEWS . $view . '.php';

		include( $file );
	}

	/**
	 * Fetches view instance for switch.
	 *
	 * @param string $base The instance basename (namespace).
	 * @param string $instance The instance suffix to call back upon.
	 * @return string The file instance case.
	 */
	protected function get_view_instance( $base, $instance = 'main' ) {
		return $base . '_' . str_replace( '-', '_', $instance );
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
	 * Adds post type support for The SEO Framework.
	 *
	 * Applies filters the_seo_framework_supported_post_types : The supported post types.
	 * @since 2.3.1
	 *
	 * @since 2.1.6
	 */
	public function post_type_support() {

		$defaults = array(
			'post',
			'page',
			'product',
			'forum',
			'topic',
			'jetpack-testimonial',
			'jetpack-portfolio',
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

		$tsf_links = array();

		if ( $this->load_options )
			$tsf_links['settings'] = '<a href="' . esc_url( admin_url( 'admin.php?page=' . $this->seo_settings_page_slug ) ) . '">' . esc_html__( 'SEO Settings', 'autodescription' ) . '</a>';

		$tsf_links['home'] = '<a href="' . esc_url( 'https://theseoframework.com/' ) . '" target="_blank">' . esc_html_x( 'Plugin Home', 'As in: The Plugin Home Page', 'autodescription' ) . '</a>';

		return array_merge( $tsf_links, $links );
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
	 * Generates dismissible notice.
	 * Also loads scripts and styles if out of The SEO Framework's context.
	 *
	 * @since 2.6.0
	 *
	 * @param string $message The notice message. Expected to be escaped if $escape is false.
	 * @param string $type The notice type : 'updated', 'error', 'warning'. Expected to be escaped.
	 * @param bool $a11y Whether to add an accessibility icon.
	 * @param bool $escape Whether to escape the whole output.
	 * @return string The dismissible error notice.
	 */
	public function generate_dismissible_notice( $message = '', $type = 'updated', $a11y = true, $escape = true ) {

		if ( empty( $message ) )
			return '';

		if ( $escape )
			$message = esc_html( $message );

		//* Make sure the scripts are loaded.
		$this->init_admin_scripts( true );

		if ( 'warning' === $type )
			$type = 'notice-warning';

		$a11y = $a11y ? 'tsf-show-icon' : '';

		$notice = '<div class="notice ' . esc_attr( $type ) . ' tsf-notice ' . $a11y . '"><p>';
		$notice .= '<a class="hide-if-no-js tsf-dismiss" title="' . esc_attr__( 'Dismiss', 'AutoDescription' ) . '"></a>';
		$notice .= '<strong>' . $message . '</strong>';
		$notice .= '</p></div>';

		return $notice;
	}

	/**
	 * Echos generated dismissible notice.
	 *
	 * @since 2.7.0
	 *
	 * @param $message The notice message. Expected to be escaped if $escape is false.
	 * @param $type The notice type : 'updated', 'error', 'warning'. Expected to be escaped.
	 * @param bool $a11y Whether to add an accessibility icon.
	 * @param bool $escape Whether to escape the whole output.
	 */
	public function do_dismissible_notice( $message = '', $type = 'updated', $a11y = true, $escape = true ) {
		echo $this->generate_dismissible_notice( $message, $type, $a11y, $escape );
	}

	/**
	 * Mark up content with code tags.
	 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
	 *
	 * @since 2.0.0
	 *
	 * @param string $content Content to be wrapped in code tags.
	 * @return string Content wrapped in code tags.
	 */
	public function code_wrap( $content ) {
		return $this->code_wrap_noesc( esc_html( $content ) );
	}

	/**
	 * Mark up content with code tags.
	 * Escapes no HTML.
	 *
	 * @since 2.2.2
	 *
	 * @param string $content Content to be wrapped in code tags.
	 * @return string Content wrapped in code tags.
	 */
	public function code_wrap_noesc( $content ) {
		return '<code>' . $content . '</code>';
	}

	/**
	 * Mark up content in description wrap.
	 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
	 *
	 * @since 2.7.0
	 *
	 * @param string $content Content to be wrapped in the description wrap.
	 * @param bool $block Whether to wrap the content in <p> tags.
	 * @return string Content wrapped int he description wrap.
	 */
	public function description( $content, $block = true ) {
		$this->description_noesc( esc_html( $content ), $block );
	}

	/**
	 * Mark up content in description wrap.
	 *
	 * @since 2.7.0
	 *
	 * @param string $content Content to be wrapped in the description wrap. Expected to be escaped.
	 * @param bool $block Whether to wrap the content in <p> tags.
	 * @return string Content wrapped int he description wrap.
	 */
	public function description_noesc( $content, $block = true ) {
		$output = '<span class="description">' . $content . '</span>';
		echo $block ? '<p>' . $output . '</p>' : $output;
	}

	/**
	 * Return custom field post meta data.
	 *
	 * Return only the first value of custom field. Return false if field is
	 * blank or not set.
	 *
	 * @since 2.0.0
	 * @staticvar array $field_cache
	 *
	 * @param string $field	Custom field key.
	 * @param int $post_id	The post ID
	 * @return string|boolean Return value or false on failure.
	 */
	public function get_custom_field( $field, $post_id = null ) {

		//* If field is falsy, get_post_meta() will return an array.
		if ( ! $field )
			return false;

		static $field_cache = array();

		if ( isset( $field_cache[ $field ][ $post_id ] ) )
			return $field_cache[ $field ][ $post_id ];

		if ( empty( $post_id ) )
			$post_id = $this->get_the_real_ID();

		$custom_field = get_post_meta( $post_id, $field, true );

		//* If custom field is empty, empty cache..
		if ( empty( $custom_field ) )
			$field_cache[ $field ][ $post_id ] = '';

		//* Render custom field, slashes stripped, sanitized if string
		$field_cache[ $field ][ $post_id ] = is_array( $custom_field ) ? stripslashes_deep( $custom_field ) : stripslashes( wp_kses_decode_entities( $custom_field ) );

		return $field_cache[ $field ][ $post_id ];
	}

	/**
	 * Google docs language determinator.
	 *
	 * @since 2.2.2
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

		//* Language shorttag to be used in Google help pages.
		$language = esc_html_x( 'en', 'e.g. en for English, nl for Dutch, fi for Finish, de for German', 'autodescription' );

		return $language;
	}

	/**
	 * Whether to allow external redirect through the 301 redirect option.
	 *
	 * @since 2.6.0
	 * @staticvar bool $allowed
	 *
	 * @return bool Whether external redirect is allowed.
	 */
	public function allow_external_redirect() {

		static $allowed = null;

		if ( isset( $allowed ) )
			return $allowed;

		/**
		 * Applies filters the_seo_framework_allow_external_redirect : bool
		 * @since 2.1.0
		 */
		return $allowed = (bool) apply_filters( 'the_seo_framework_allow_external_redirect', true );
	}

	/**
	 * Object cache set wrapper.
	 *
	 * @since 2.4.3
	 *
	 * @param string $key The Object cache key.
	 * @param mixed $data The Object cache data.
	 * @param int $expire The Object cache expire time.
	 * @param string $group The Object cache group.
	 * @return bool true on set, false when disabled.
	 */
	public function object_cache_set( $key, $data, $expire = 0, $group = 'the_seo_framework' ) {

		if ( $this->use_object_cache )
			return wp_cache_set( $key, $data, $group, $expire );

		return false;
	}

	/**
	 * Object cache get wrapper.
	 *
	 * @since 2.4.3
	 *
	 * @param string $key The Object cache key.
	 * @param string $group The Object cache group.
	 * @param bool $force Whether to force an update of the local cache.
	 * @param bool $found Whether the key was found in the cache. Disambiguates a return of false, a storable value.
	 * @return mixed wp_cache_get if object caching is allowed. False otherwise.
	 */
	public function object_cache_get( $key, $group = 'the_seo_framework', $force = false, &$found = null ) {

		if ( $this->use_object_cache )
			return wp_cache_get( $key, $group, $force, $found );

		return false;
	}

	/**
	 * Checks if the string input is exactly '1'.
	 *
	 * @since 2.6.0
	 *
	 * @param string $value The value to check.
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
	 * @since 2.6.0
	 *
	 * @param string $option The option name.
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
	 * @since 2.6.0
	 * @staticvar bool $cache
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
	 * @since 2.6.0
	 * @staticvar array $lowercase Contains nouns.
	 *
	 * @return string The maybe lowercase noun.
	 */
	public function maybe_lowercase_noun( $noun ) {

		static $lowercase = array();

		if ( isset( $lowercase[ $noun ] ) )
			return $lowercase[ $noun ];

		return $lowercase[ $noun ] = $this->check_wp_locale( 'de' ) ? $noun : strtolower( $noun );
	}

	/**
	 * Returns the minimum role required to adjust settings.
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

			$url = html_entity_decode( menu_page_url( $this->seo_settings_page_slug, 0 ) );

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
	 * @return string PHP Timezone String.
	 */
	protected function get_tzstring_from_offset( $offset = 0 ) {

		$seconds = round( $offset * HOUR_IN_SECONDS );

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

	/**
	 * Counts words encounters from input string.
	 * Case insensitive. Returns first encounter of each word if found multiple times.
	 *
	 * @since 2.7.0
	 *
	 * @param string $string Required. The string to count words in.
	 * @param int $amount Minimum amount of words to encounter in the string. Set to 0 to count all words longer than $bother_length.
	 * @param int $amount_bother Minimum amount of words to encounter in the string that fall under the $bother_length. Set to 0 to count all words shorter than $bother_length.
	 * @param int $bother_length The maximum string length of a word to pass for $amount_bother instead of $amount. Set to 0 to pass all words through $amount_bother
	 * @return array Containing arrays of words with their count.
	 */
	public function get_word_count( $string, $amount = 3, $amount_bother = 5, $bother_length = 3 ) {

		//* Convert string's special characters into PHP readable words.
		$string = htmlentities( $string, ENT_COMPAT, 'UTF-8' );

		//* Count the words. Because we've converted all characters to XHTML codes, the odd ones should be only numerical.
		$words = str_word_count( strtolower( $string ), 2, '&#0123456789;' );

		$words_too_many = array();

		if ( is_array( $words ) ) {

			/**
			 * Applies filters 'the_seo_framework_bother_me_desc_length' : int Min Character length to bother you with.
			 * @since 2.6.0
			 */
			$bother_me_length = (int) apply_filters( 'the_seo_framework_bother_me_desc_length', $bother_length );

			$word_count = array_count_values( $words );

			//* Parse word counting.
			if ( is_array( $word_count ) ) {
				//* We're going to fetch words based on position, and then flip it to become the key.
				$word_keys = array_flip( array_reverse( $words, true ) );

				foreach ( $word_count as $word => $count ) {

					if ( mb_strlen( html_entity_decode( $word ) ) < $bother_me_length ) {
						$run = $count >= $amount_bother;
					} else {
						$run = $count >= $amount;
					}

					if ( $run ) {
						//* The encoded word is longer or equal to the bother lenght.

						$word_len = mb_strlen( $word );

						$position = $word_keys[ $word ];
						$first_encountered_word = mb_substr( $string, $position, $word_len );

						//* Found words that are used too frequently.
						$words_too_many[] = array( $first_encountered_word => $count );
					}
				}
			}
		}

		return $words_too_many;
	}
}
