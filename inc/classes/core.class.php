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
 * Class The_SEO_Framework\Core
 *
 * Initializes the plugin & Holds plugin core functions.
 *
 * @since 2.8.0
 */
class Core {

	/**
	 * Unserializing instances of this object is forbidden.
	 */
	final protected function __wakeup() { }

	/**
	 * Cloning of this object is forbidden.
	 */
	final protected function __clone() { }

	/**
	 * Handles unapproachable invoked properties.
	 * Makes sure deprecated properties are still overwritten.
	 * If property never existed, default PHP behavior is invoked.
	 *
	 * @since 2.8.0
	 *
	 * @param string $name The property name.
	 * @param mixed $value The property value.
	 */
	final public function __set( $name, $value ) {
		/**
		 * For now, no deprecation is being handled; as no properties have been deprecated.
		 */
		$this->_deprecated_function( 'the_seo_framework()->' . \esc_html( $name ), 'unknown' );

		//* Invoke default behavior.
		$this->$name = $value;
	}

	/**
	 * Handles unapproachable invoked properties.
	 * Makes sure deprecated properties are still accessible.
	 * If property never existed, default PHP behavior is invoked.
	 *
	 * @since 2.7.0
	 *
	 * @param string $name The property name.
	 * @return mixed $var The property value.
	 */
	final public function __get( $name ) {

		switch ( $name ) :
			case 'pagehook' :
				$this->_deprecated_function( 'the_seo_framework()->pagehook', '2.7.0', 'the_seo_framework()->seo_settings_page_hook' );
				return $this->seo_settings_page_hook;
				break;

			default:
				break;
		endswitch;

		//* Invoke default behavior.
		return $this->$name;
	}

	/**
	 * Handles unapproachable invoked methods.
	 *
	 * @param string $name The method name.
	 * @param array $arguments The method arguments.
	 * @return void
	 */
	final public function __call( $name, $arguments ) {

		static $depr_class = null;

		if ( is_null( $depr_class ) )
			$depr_class = new Deprecated;

		if ( is_callable( array( $depr_class, $name ) ) ) {
			return call_user_func_array( array( $depr_class, $name ), $arguments );
		}

		\the_seo_framework()->_inaccessible_p_or_m( 'the_seo_framework()->' . \esc_html( $name ) . '()' );
		return;
	}

	/**
	 * Constructor. Loads actions and filters.
	 * Latest Class. Doesn't have parent.
	 */
	protected function __construct() {

		\add_action( 'current_screen', array( $this, 'post_type_support' ), 0 );

		if ( $this->the_seo_framework_debug ) {

			$debug_instance = Debug::get_instance();

			\add_action( 'the_seo_framework_do_before_output', array( $debug_instance, 'set_debug_query_output_cache' ) );
			\add_action( 'admin_footer', array( $debug_instance, 'debug_screens' ) );
			\add_action( 'admin_footer', array( $debug_instance, 'debug_output' ) );
			\add_action( 'wp_footer', array( $debug_instance, 'debug_output' ) );
		}
	}

	/**
	 * Destroys output buffer, if any. To be used with AJAX and XML to clear any PHP errors or dumps.
	 *
	 * @since 2.8.0
	 * @since 2.9.0 : Now flushes all levels rather than just the latest one.
	 *
	 * @return bool True on clear. False otherwise.
	 */
	protected function clean_response_header() {

		if ( $level = ob_get_level() ) {
			while ( $level-- ) {
				ob_end_clean();
			}
			return true;
		}

		return false;
	}

	/**
	 * Fetches files based on input to reduce memory overhead.
	 * Passes on input vars.
	 *
	 * @since 2.7.0
	 * @access private
	 * @credits Akismet For some code.
	 *
	 * @param string $view The file name.
	 * @param array $args The arguments to be supplied within the file name.
	 *              Each array key is converted to a variable with its value attached.
	 * @param string $instance The instance suffix to call back upon.
	 */
	public function get_view( $view, array $args = array(), $instance = 'main' ) {

		foreach ( $args as $key => $val )
			$$key = $val;

		$file = THE_SEO_FRAMEWORK_DIR_PATH_VIEWS . $view . '.php';

		include $file;
	}

	/**
	 * Fetches view instance for switch.
	 *
	 * @since 2.7.0
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
	 * @since 2.6.0
	 *
	 * @param int $i The dimension to resize.
	 * @param int $r1 The deminsion that determines the ratio.
	 * @param int $r2 The dimension to proportionate to.
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

		/**
		 * Applies filters the_seo_framework_supported_post_types : Array The supported post types.
		 * @since 2.3.1
		 */
		$post_types = (array) \apply_filters( 'the_seo_framework_supported_post_types', $defaults );

		$types = \wp_parse_args( $defaults, $post_types );

		foreach ( $types as $type ) {
			\add_post_type_support( $type, array( 'autodescription-meta' ) );
		}
	}

	/**
	 * Adds link from plugins page to SEO Settings page.
	 *
	 * @since 2.2.8
	 * @since 2.9.2 : Added TSFEM link.
	 * @since 3.0.0 : 1. Shortened names.
	 *                2. Added noreferrer to the external links.
	 *
	 * @param array $links The current links.
	 * @return array The plugin links.
	 */
	public function plugin_action_links( $links = array() ) {

		$tsf_links = array();

		if ( $this->load_options )
			$tsf_links['settings'] = '<a href="' . \esc_url( \admin_url( 'admin.php?page=' . $this->seo_settings_page_slug ) ) . '">' . \esc_html__( 'Settings', 'autodescription' ) . '</a>';

		$tsf_links['home'] = '<a href="' . \esc_url( 'https://theseoframework.com/' ) . '" rel="noreferrer noopener" target="_blank">' . \esc_html_x( 'Home', 'As in: The Plugin Home Page', 'autodescription' ) . '</a>';

		/**
		 * These are weak checks.
		 * But it has minimum to no UX/performance impact on failure.
		 */
		if ( ! defined( 'TSF_EXTENSION_MANAGER_VERSION' ) ) {
			$tsfem = \get_plugins( '/the-seo-framework-extension-manager' );
			if ( empty( $tsfem ) )
				$tsf_links['tsfem'] = '<a href="' . \esc_url( \__( 'https://wordpress.org/plugins/the-seo-framework-extension-manager/', 'autodescription' ) ) . '" rel="noreferrer noopener" target="_blank">' . \esc_html_x( 'Extensions', 'Plugin extensions', 'autodescription' ) . '</a>';
		}

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

		return $front_id = $this->has_page_on_front() ? (int) \get_option( 'page_on_front' ) : 0;
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
			$message = \esc_html( $message );

		//* Make sure the scripts are loaded.
		$this->init_admin_scripts( true );

		if ( 'warning' === $type )
			$type = 'notice-warning';

		$a11y = $a11y ? 'tsf-show-icon' : '';

		$notice = '<div class="notice ' . \esc_attr( $type ) . ' tsf-notice ' . $a11y . '"><p>';
		$notice .= '<a class="hide-if-no-js tsf-dismiss" title="' . \esc_attr__( 'Dismiss', 'autodescription' ) . '"></a>';
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
		echo $this->generate_dismissible_notice( $message, $type, (bool) $a11y, (bool) $escape );
	}

	/**
	 * Generates dismissible notice that stick until the user dismisses it.
	 * Also loads scripts and styles if out of The SEO Framework's context.
	 *
	 * @since 2.9.3
	 * @see $this->do_dismissible_sticky_notice()
	 * @uses THE_SEO_FRAMEWORK_UPDATES_CACHE
	 * @todo make this do something.
	 * NOTE: This method is a placeholder.
	 *
	 * @param string $message The notice message. Expected to be escaped if $escape is false.
	 * @param string $key     The notice key. Must be unique and tied to the stored updates cache option.
	 * @param array $args : {
	 *    'type'   => string Optional. The notification type. Default 'updated'.
	 *    'a11y'   => bool   Optional. Whether to enable accessibility. Default true.
	 *    'escape' => bool   Optional. Whether to escape the $message. Default true.
	 *    'color'  => string Optional. If filled in, it will output the selected color. Default ''.
	 *    'icon'   => string Optional. If filled in, it will output the selected icon. Default ''.
	 * }
	 * @return string The dismissible error notice.
	 */
	public function generate_dismissible_sticky_notice( $message, $key, $args = array() ) {
		return '';
	}

	/**
	 * Echos generated dismissible sticky notice.
	 *
	 * @since 2.9.3
	 * @uses $this->generate_dismissible_sticky_notice()
	 *
	 * @param string $message The notice message. Expected to be escaped if $escape is false.
	 * @param string $key     The notice key. Must be unique and tied to the stored updates cache option.
	 * @param array $args : {
	 *    'type'   => string Optional. The notification type. Default 'updated'.
	 *    'a11y'   => bool   Optional. Whether to enable accessibility. Default true.
	 *    'escape' => bool   Optional. Whether to escape the $message. Default true.
	 *    'color'  => string Optional. If filled in, it will output the selected color. Default ''.
	 *    'icon'   => string Optional. If filled in, it will output the selected icon. Default ''.
	 * }
	 * @return string The dismissible error notice.
	 */
	public function do_dismissible_sticky_notice( $message, $key, $args = array() ) {
		echo $this->generate_dismissible_sticky_notice( $message, $key, $args );
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
		return $this->code_wrap_noesc( \esc_html( $content ) );
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
		$this->description_noesc( \esc_html( $content ), $block );
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
		$language = \esc_html_x( 'en', 'e.g. en for English, nl for Dutch, fi for Finish, de for German', 'autodescription' );

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
		return $allowed = (bool) \apply_filters( 'the_seo_framework_allow_external_redirect', true );
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
		return $this->is_checked( $this->get_option( $option ) );
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

		if ( '1' === \get_option( 'blog_public' ) )
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
	 * @since 3.0.0
	 *
	 * @return string The minimum required capability for SEO Settings.
	 */
	public function get_settings_capability() {
		/**
		 * Applies filters 'the_seo_framework_settings_capability'
		 *
		 * @since 2.6.0
		 * @string $capability The user capability required to adjust settings.
		 */
		return (string) \apply_filters( 'the_seo_framework_settings_capability', 'manage_options' );
	}

	/**
	 * Determines if the current user can do settings.
	 * Not cached as it's imposing security functionality.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	public function can_access_settings() {
		return \current_user_can( $this->get_settings_capability() );
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

			$url = html_entity_decode( \menu_page_url( $this->seo_settings_page_slug, false ) );

			return \esc_url( $url, array( 'http', 'https' ) );
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
	 * @return string PHP Timezone String.
	 */
	public function get_timezone_string( $guess = false ) {

		$tzstring = \get_option( 'timezone_string' );

		if ( false !== strpos( $tzstring, 'Etc/GMT' ) )
			$tzstring = '';

		if ( $guess && empty( $tzstring ) ) {
			$offset = \get_option( 'gmt_offset' );
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
		 * PHP bug workaround. Disable the DST check.
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
	 * Converts time from GMT input to given format.
	 *
	 * @since 2.7.0
	 *
	 * @param string $format The datetime format.
	 * @param string $time The GMT time. Expects timezone to be omitted.
	 * @return string The converted time. Empty string if no $time is given.
	 */
	public function gmt2date( $format = 'Y-m-d', $time = '' ) {

		if ( $time )
			return date( $format, strtotime( $time . ' GMT' ) );

		return '';
	}

	/**
	 * Returns timestamp format based on timestamp settings.
	 *
	 * @since 3.0.0
	 *
	 * @return string The timestamp format used in PHP date.
	 */
	public function get_timestamp_format() {
		return '1' === $this->get_option( 'timestamps_format' ) ? 'Y-m-d\TH:iP' : 'Y-m-d';
	}

	/**
	 * Determines if time is used in the timestamp format.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if time is used. False otherwise.
	 */
	public function uses_time_in_timestamp_format() {
		return '1' === $this->get_option( 'timestamps_format' );
	}

	/**
	 * Counts words encounters from input string.
	 * Case insensitive. Returns first encounter of each word if found multiple times.
	 *
	 * @since 2.7.0
	 *
	 * @param string $string Required. The string to count words in.
	 * @param int $amount Minimum amount of words to encounter in the string.
	 *            Set to 0 to count all words longer than $bother_length.
	 * @param int $amount_bother Minimum amount of words to encounter in the string
	 *            that fall under the $bother_length. Set to 0 to count all words
	 *            shorter than $bother_length.
	 * @param int $bother_length The maximum string length of a word to pass for
	 *            $amount_bother instead of $amount. Set to 0 to pass all words
	 *            through $amount_bother
	 * @return array Containing arrays of words with their count.
	 */
	public function get_word_count( $string, $amount = 3, $amount_bother = 5, $bother_length = 3 ) {

		//* Convert string's special characters into PHP readable words.
		$string = htmlentities( $string, ENT_COMPAT, 'UTF-8' );

		//* Count the words. Because we've converted all characters to XHTML codes, the odd ones should be only numerical.
		$words = str_word_count( strtolower( $string ), 2, '&#0123456789;' );

		$words_too_many = array();

		if ( is_array( $words ) ) :
			/**
			 * Applies filters 'the_seo_framework_bother_me_desc_length' : int Min Character length to bother you with.
			 * @since 2.6.0
			 */
			$bother_me_length = (int) \apply_filters( 'the_seo_framework_bother_me_desc_length', $bother_length );

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
						//* The encoded word is longer or equal to the bother length.

						$word_len = mb_strlen( $word );

						$position = $word_keys[ $word ];
						$first_encountered_word = mb_substr( $string, $position, $word_len );

						//* Found words that are used too frequently.
						$words_too_many[] = array( $first_encountered_word => $count );
					}
				}
			}
		endif;

		return $words_too_many;
	}

	/**
	 * Calculates the relative font color according to the background.
	 *
	 * @since 2.8.0
	 * @since 2.9.0 Now adds a little more relative softness based on rel_lum.
	 * @since 2.9.2 (Typo): Renamed from 'get_relatitve_fontcolor' to 'get_relative_fontcolor'.
	 *
	 * @param string $hex The 3 to 6 character RGB hex. '#' prefix is supported.
	 * @return string The hexadecimal RGB relative font color, without '#' prefix.
	 */
	public function get_relative_fontcolor( $hex = '' ) {

		$hex = ltrim( $hex, '#' );

		//* #rgb = #rrggbb
		if ( 3 === strlen( $hex ) )
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];

		$hex = str_split( $hex, 2 );

		//* Convert to numerical values.
		$r = hexdec( $hex[0] );
		$g = hexdec( $hex[1] );
		$b = hexdec( $hex[2] );

		//* Convert to sRGB for relative luminance.
		$sr = 0.2125 * $r;
		$sg = 0.7154 * $g;
		$sb = 0.0721 * $b;
		$rel_lum = 1 - ( $sr + $sg + $sb ) / 255;

		//* Convert to relative intvals between 1 and 0 for L from HSL
		// $rr = $r / 255;
		// $rg = $g / 255;
		// $rb = $b / 255;
		// $luminance = ( min( $rr, $rg, $rb ) + max( $rr, $rg, $rb ) ) / 2;

		//* Get perceptive luminance (greyscale) according to W3C.
		$gr = 0.2989 * $r;
		$gg = 0.5870 * $g;
		$gb = 0.1140 * $b;
		$per_lum = 1 - ( $gr + $gg + $gb ) / 255;

		//* Invert colors if they hit luminance boundaries.
		if ( $rel_lum < 0.5 ) {
			//* Build dark. Add softness.
			$gr = $gr * $per_lum / 8 / 0.2989 + 8 * 0.2989 / $rel_lum;
			$gg = $gg * $per_lum / 8 / 0.5870 + 8 * 0.5870 / $rel_lum;
			$gb = $gb * $per_lum / 8 / 0.1140 + 8 * 0.1140 / $rel_lum;
		} else {
			//* Build light. Add (subtract) softness.
			$gr = 255 - $gr * $per_lum / 8 * 0.2989 - 8 * 0.2989 / $rel_lum;
			$gg = 255 - $gg * $per_lum / 8 * 0.5870 - 8 * 0.5870 / $rel_lum;
			$gb = 255 - $gb * $per_lum / 8 * 0.1140 - 8 * 0.1140 / $rel_lum;
		}

		//* Complete hexvals.
		$retr = str_pad( dechex( $gr ), 2, '0', STR_PAD_LEFT );
		$retg = str_pad( dechex( $gg ), 2, '0', STR_PAD_LEFT );
		$retb = str_pad( dechex( $gb ), 2, '0', STR_PAD_LEFT );

		return $retr . $retg . $retb;
	}

	/**
	 * Converts markdown text into HMTL.
	 * Does not support list or block elements. Only inline statements.
	 *
	 * Note: This code has been rightfully stolen from the Extension Manager plugin (sorry Sybre!).
	 *
	 * @since 2.8.0
	 * @since 2.9.0 : 1. Removed word boundary requirement for strong.
	 *                2. Now accepts regex count their numeric values in string.
	 *                3. Fixed header 1~6 calculation.
	 * @since 2.9.3 : 1. Added $args parameter.
	 *                2. TODO It now uses substr_replace instead of str_replace to prevent duplicated replacements.
	 * @link https://wordpress.org/plugins/about/readme.txt
	 *
	 * @param string $text The text that might contain markdown. Expected to be escaped.
	 * @param array $convert The markdown style types wished to be converted.
	 *              If left empty, it will convert all.
	 * @param array $args The function arguments.
	 * @return string The markdown converted text.
	 */
	public function convert_markdown( $text, $convert = array(), $args = array() ) {

		preprocess : {
			$text = str_replace( "\r\n", "\n", $text );
			$text = str_replace( "\t", ' ', $text );
			$text = trim( $text );
		}

		if ( '' === $text )
			return '';

		$defaults = array(
			'a_internal' => false,
		);
		$args = array_merge( $defaults, $args );

		/**
		 * The conversion list's keys are per reference only.
		 */
		$conversions = array(
			'**'   => 'strong',
			'*'    => 'em',
			'`'    => 'code',
			'[]()' => 'a',
			'======'  => 'h6',
			'====='  => 'h5',
			'===='  => 'h4',
			'==='  => 'h3',
			'=='   => 'h2',
			'='    => 'h1',
		);

		$md_types = empty( $convert ) ? $conversions : array_intersect( $conversions, $convert );

		foreach ( $md_types as $type ) :
			switch ( $type ) :
				case 'strong' :
					$count = preg_match_all( '/(?:\*{2})([^\*{\2}]+)(?:\*{2})/', $text, $matches, PREG_PATTERN_ORDER );

					for ( $i = 0; $i < $count; $i++ ) {
						$text = str_replace(
							$matches[0][ $i ],
							sprintf( '<strong>%s</strong>', \esc_html( $matches[1][ $i ] ) ),
							$text
						);
					}
					break;

				case 'em' :
					$count = preg_match_all( '/(?:\*{1})([^\*{\1}]+)(?:\*{1})/', $text, $matches, PREG_PATTERN_ORDER );

					for ( $i = 0; $i < $count; $i++ ) {
						$text = str_replace(
							$matches[0][ $i ],
							sprintf( '<em>%s</em>', \esc_html( $matches[1][ $i ] ) ),
							$text
						);
					}
					break;

				case 'code' :
					$count = preg_match_all( '/(?:`{1})([^`{\1}]+)(?:`{1})/', $text, $matches, PREG_PATTERN_ORDER );

					for ( $i = 0; $i < $count; $i++ ) {
						$text = str_replace(
							$matches[0][ $i ],
							sprintf( '<code>%s</code>', \esc_html( $matches[1][ $i ] ) ),
							$text
						);
					}
					break;

				case 'h6' :
				case 'h5' :
				case 'h4' :
				case 'h3' :
				case 'h2' :
				case 'h1' :
					$amount = filter_var( $type, FILTER_SANITIZE_NUMBER_INT );
					//* Considers word non-boundary. @TODO consider removing this?
					$expression = sprintf( '/(?:\={%1$s})\B([^\={\%1$s}]+)\B(?:\={%1$s})/', $amount );
					$count = preg_match_all( $expression, $text, $matches, PREG_PATTERN_ORDER );

					for ( $i = 0; $i < $count; $i++ ) {
						$text = str_replace(
							$matches[0][ $i ],
							sprintf( '<%1$s>%2$s</%1$s>', \esc_attr( $type ), \esc_html( $matches[1][ $i ] ) ),
							$text
						);
					}
					break;

				case 'a' :
					$count = preg_match_all( '/(?:(?:\[{1})([^\]{1}]+)(?:\]{1})(?:\({1})([^\)\(]+)(?:\){1}))/', $text, $matches, PREG_PATTERN_ORDER );

					$_string = $args['a_internal'] ? '<a href="%s">%s</a>' : '<a href="%s" target="_blank" rel="nofollow noreferrer noopener">%s</a>';

					for ( $i = 0; $i < $count; $i++ ) {
						$text = str_replace(
							$matches[0][ $i ],
							sprintf( $_string, \esc_url( $matches[2][ $i ], array( 'http', 'https' ) ), \esc_html( $matches[1][ $i ] ) ),
							$text
						);
					}
					break;

				default :
					break;
			endswitch;
		endforeach;

		return $text;
	}
}
