<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Core
 * @see ./index.php for facade details.
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
 * Class The_SEO_Framework\Core
 *
 * Initializes the plugin & Holds plugin core functions.
 *
 * @since 2.8.0
 */
class Core {

	/**
	 * Tells if this plugin is loaded.
	 *
	 * @NOTE: Only `\The_SEO_Framework\_init_tsf()` should adjust this.
	 *
	 * @since 3.1.0
	 * @access protected
	 *         Don't alter this variable!!!
	 * @var boolean $loaded
	 */
	public $loaded = false;

	/**
	 * Calling any top file without __construct() is forbidden.
	 */
	private function __construct() { }

	/**
	 * Handles unapproachable invoked properties.
	 *
	 * Makes sure deprecated properties are still overwritten.
	 * If the property never existed, default PHP behavior is invoked.
	 *
	 * @since 2.8.0
	 * @since 3.2.2 This method no longer allows to overwrite protected or private variables.
	 *
	 * @param string $name  The property name.
	 * @param mixed  $value The property value.
	 */
	final public function __set( $name, $value ) {

		if ( 'load_options' === $name ) {
			// $this->_inaccessible_p_or_m( 'the_seo_framework()->load_options', 'since 4.2.0; use constant THE_SEO_FRAMEWORK_HEADLESS' );
			$this->is_headless['settings'] = $value;
			return;
		}

		/**
		 * For now, no deprecation is being handled; as no properties have been deprecated. Just removed.
		 */
		$this->_inaccessible_p_or_m( 'the_seo_framework()->' . $name, 'unknown' );

		// Invoke default behavior: Write variable if it's not protected.
		if ( ! isset( $this->$name ) )
			$this->$name = $value;
	}

	/**
	 * Handles unapproachable invoked properties.
	 *
	 * Makes sure deprecated properties are still accessible.
	 *
	 * @since 2.7.0
	 * @since 3.1.0 Removed known deprecations.
	 * @since 3.2.2 This method no longer invokes PHP errors, nor returns protected values.
	 *
	 * @param string $name The property name.
	 * @return mixed
	 */
	final public function __get( $name ) {

		if ( 'load_options' === $name ) {
			// $this->_inaccessible_p_or_m( 'the_seo_framework()->load_options', 'since 4.2.0; use constant THE_SEO_FRAMEWORK_HEADLESS' );
			return ! $this->is_headless['settings'];
		}

		$this->_inaccessible_p_or_m( 'the_seo_framework()->' . $name, 'unknown' );
	}

	/**
	 * Handles unapproachable invoked methods.
	 *
	 * @since 2.7.0
	 *
	 * @param string $name      The method name.
	 * @param array  $arguments The method arguments.
	 * @return mixed|void
	 */
	final public function __call( $name, $arguments ) {

		static $depr_class = null;

		if ( \is_null( $depr_class ) )
			$depr_class = new Deprecated;

		if ( \is_callable( [ $depr_class, $name ] ) )
			return \call_user_func_array( [ $depr_class, $name ], $arguments );

		$this->_inaccessible_p_or_m( 'the_seo_framework()->' . $name . '()' );
	}

	/**
	 * Destroys output buffer, if any. To be used with AJAX and XML to clear any PHP errors or dumps.
	 *
	 * @since 2.8.0
	 * @since 2.9.0 Now flushes all levels rather than just the latest one.
	 * @since 4.0.0 Is now public.
	 *
	 * @return bool True on clear. False otherwise.
	 */
	public function clean_response_header() {

		$level = ob_get_level();

		if ( $level ) {
			while ( $level-- ) ob_end_clean();
			return true;
		}

		return false;
	}

	/**
	 * Includes compatibility files, only once per request.
	 *
	 * @since 2.8.0
	 * @access private
	 *
	 * @param string $what The vendor/plugin/theme name for the compatibilty.
	 * @param string $type The compatibility type. Be it 'plugin' or 'theme'.
	 * @return bool True on success, false on failure. Files are expected not to return any values.
	 */
	public function _include_compat( $what, $type = 'plugin' ) {

		static $included = [];

		if ( ! isset( $included[ $what ][ $type ] ) ) {
			// phpcs:ignore, VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable -- forwarded to include...
			$_secret = $this->create_view_secret( uniqid( '', true ) );

			$included[ $what ][ $type ] = (bool) require THE_SEO_FRAMEWORK_DIR_PATH_COMPAT . $type . '-' . $what . '.php';
		}

		return $included[ $what ][ $type ];
	}

	/**
	 * Fetches files based on input to reduce memory overhead.
	 * Passes on input vars.
	 *
	 * @since 2.7.0
	 * @access private
	 * @credits Akismet For some code.
	 *
	 * @param string $view     The file name.
	 * @param array  $__args   The arguments to be supplied within the file name.
	 *                         Each array key is converted to a variable with its value attached.
	 * @param string $instance The instance suffix to call back upon.
	 */
	public function get_view( $view, array $__args = [], $instance = 'main' ) {

		//? A faster extract().
		foreach ( $__args as $__k => $__v ) $$__k = $__v;
		unset( $__k, $__v, $__args );

		// phpcs:ignore, VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable -- forwarded to include...
		$_secret = $this->create_view_secret( uniqid( '', true ) );

		include $this->get_view_location( $view );
	}

	/**
	 * Stores and returns view secret.
	 *
	 * This is not cryptographically secure, but it's enough to fend others off including our files where they shouldn't.
	 * Our view-files have a certain expectation of inputs to meet. If they don't meet that, we could expose our users to security issues.
	 * We could not measure any meaningful performance impact by using this (0.02% of 54x get_view() runtime).
	 *
	 * @since 4.1.1
	 *
	 * @param string|null $value The secret.
	 * @return string|null The stored secret.
	 */
	protected function create_view_secret( $value = null ) {
		static $secret;
		return $secret = isset( $value ) ? $value : $secret;
	}

	/**
	 * Verifies view secret.
	 *
	 * @since 4.1.1
	 * @access private
	 *
	 * @param string $value The secret.
	 * @return bool
	 */
	public function _verify_include_secret( $value ) {
		return isset( $value ) && $this->create_view_secret() === $value;
	}

	/**
	 * Gets view location.
	 *
	 * @since 3.1.0
	 * @access private
	 * @TODO add path traversal mitigation via realpath()?
	 *    -> $file must always be dev-supplied, never user-.
	 *
	 * @param string $file The file name.
	 * @return string The view location.
	 */
	public function get_view_location( $file ) {
		return THE_SEO_FRAMEWORK_DIR_PATH_VIEWS . $file . '.php';
	}

	/**
	 * Fetches view instance for view-switch statements.
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
	 * Returns an array of hierarchical post types.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now gets hierarchical post types that don't support rewrite, as well.
	 *
	 * @return array The public hierarchical post types.
	 */
	public function get_hierarchical_post_types() {
		static $types;
		return $types ?: $types = \get_post_types(
			[
				'hierarchical' => true,
				'public'       => true,
			],
			'names'
		);
	}

	/**
	 * Returns an array of nonhierarchical post types.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now gets non-hierarchical post types that don't support rewrite, as well.
	 *
	 * @return array The public nonhierarchical post types.
	 */
	public function get_nonhierarchical_post_types() {
		static $types;
		return $types ?: $types = \get_post_types(
			[
				'hierarchical' => false,
				'public'       => true,
			],
			'names'
		);
	}

	/**
	 * Whether to allow external redirect through the 301 redirect option.
	 * Memoizes the return value.
	 *
	 * @since 2.6.0
	 *
	 * @return bool Whether external redirect is allowed.
	 */
	public function allow_external_redirect() {
		static $cache = null;
		/**
		 * @since 2.1.0
		 * @param bool $allowed Whether external redirect is allowed.
		 */
		return isset( $cache ) ? $cache : $cache = (bool) \apply_filters( 'the_seo_framework_allow_external_redirect', true );
	}

	/**
	 * Checks if blog is public through WordPress core settings.
	 * Memoizes the return value.
	 *
	 * @since 2.6.0
	 * @since 4.0.5 Can now test for non-sanitized 'blog_public' option states.
	 *
	 * @return bool True is blog is public.
	 */
	public function is_blog_public() {

		static $cache = null;

		return isset( $cache ) ? $cache : $cache = (bool) \get_option( 'blog_public' );
	}

	/**
	 * Whether the current blog is spam or deleted.
	 * Multisite Only.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Now uses get_site()
	 * @since 3.1.1 Now checks for `is_multisite()`, to prevent a crash with Divi's compatibility injection.
	 *
	 * @return bool Current blog is spam.
	 */
	public function current_blog_is_spam_or_deleted() {

		if ( ! \function_exists( '\\get_site' ) || ! \is_multisite() )
			return false;

		$site = \get_site();

		if ( $site instanceof \WP_Site && ( '1' === $site->spam || '1' === $site->deleted ) )
			return true;

		return false;
	}

	/**
	 * Returns the minimum role required to adjust settings.
	 *
	 * @since 3.0.0
	 * @since 4.1.0 Now uses the constant `THE_SEO_FRAMEWORK_SETTINGS_CAP` as a default return value.
	 * @todo deprecate, use constant instead.
	 *
	 * @return string The minimum required capability for SEO Settings.
	 */
	public function get_settings_capability() {
		/**
		 * @since 2.6.0
		 * @todo deprecate 4.2.0, use constant instead.
		 * @param string $capability The user capability required to adjust settings.
		 */
		return (string) \apply_filters( 'the_seo_framework_settings_capability', THE_SEO_FRAMEWORK_SETTINGS_CAP );
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
	 * @since 4.1.4
	 *
	 * @return string The escaped SEO Settings page URL.
	 */
	public function get_seo_settings_page_url() {

		if ( ! $this->is_headless['settings'] ) {
			$url = html_entity_decode( \menu_page_url( $this->seo_settings_page_slug, false ) );
			return \esc_url( $url, [ 'https', 'http' ] );
		}

		return '';
	}

	/**
	 * Returns the PHP timezone compatible string.
	 * UTC offsets are unreliable.
	 *
	 * @since 2.6.0
	 *
	 * @param bool $guess If true, the timezone will be guessed from the
	 *                    WordPress core gmt_offset option.
	 * @return string PHP Timezone String. May be empty (thus invalid).
	 */
	public function get_timezone_string( $guess = false ) {

		$tzstring = \get_option( 'timezone_string' );

		if ( false !== strpos( $tzstring, 'Etc/GMT' ) )
			$tzstring = '';

		if ( $guess && empty( $tzstring ) ) {
			$tzstring = $this->get_tzstring_from_offset( \get_option( 'gmt_offset' ) );
		}

		return $tzstring;
	}

	/**
	 * Fetches the Timezone String from given offset.
	 *
	 * @since 2.6.0
	 * @since 4.0.0 Removed PHP <5.6 support.
	 *
	 * @param int $offset The GMT offzet.
	 * @return string PHP Timezone String.
	 */
	protected function get_tzstring_from_offset( $offset = 0 ) {
		$seconds = round( $offset * HOUR_IN_SECONDS );
		return timezone_name_from_abbr( '', $seconds, 1 );
	}

	/**
	 * Sets and resets the timezone.
	 *
	 * NOTE: Always call reset_timezone() ASAP. Don't let changes linger, as they can be destructive.
	 *
	 * This exists because WordPress's current_time() adds discrepancies between UTC and GMT.
	 * This is also far more accurate than WordPress's tiny time table.
	 *
	 * @TODO Note that WordPress 5.3 no longer requires this, and that we should rely on wp_date() instead.
	 *       So, we should remove this dependency ASAP.
	 *
	 * @since 2.6.0
	 * @since 3.0.6 Now uses the old timezone string when a new one can't be generated.
	 * @since 4.0.4 Now also unsets the stored timezone string on reset.
	 * @link http://php.net/manual/en/timezones.php
	 *
	 * @param string $tzstring Optional. The PHP Timezone string. Best to leave empty to always get a correct one.
	 * @param bool   $reset Whether to reset to default. Ignoring first parameter.
	 * @return bool True on success. False on failure.
	 */
	public function set_timezone( $tzstring = '', $reset = false ) {

		static $old_tz = null;

		$old_tz = $old_tz ?: date_default_timezone_get() ?: 'UTC';

		if ( $reset ) {
			$_revert_tz = $old_tz;
			$old_tz     = null;
			// phpcs:ignore, WordPress.DateTime.RestrictedFunctions.timezone_change_date_default_timezone_set
			return date_default_timezone_set( $_revert_tz );
		}

		if ( empty( $tzstring ) )
			$tzstring = $this->get_timezone_string( true ) ?: $old_tz;

		// phpcs:ignore, WordPress.DateTime.RestrictedFunctions.timezone_change_date_default_timezone_set
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
	 * @since 4.0.4 Now uses `gmdate()` instead of `date()`.
	 * @see `$this->set_timezone()`
	 * @see `$this->reset_timezone()`
	 *
	 * @param string $format The datetime format.
	 * @param string $time The GMT time. Expects timezone to be omitted.
	 * @return string The converted time. Empty string if no $time is given.
	 */
	public function gmt2date( $format = 'Y-m-d', $time = '' ) {

		if ( $time )
			return gmdate( $format, strtotime( $time . ' GMT' ) );

		return '';
	}

	/**
	 * Returns timestamp format based on timestamp settings.
	 *
	 * @since 3.0.0
	 * @since 4.1.4: 1. Added options-override parameter.
	 *               1. Added return value filter.
	 * @link https://www.w3.org/TR/NOTE-datetime
	 *
	 * @param null|bool $override_get_time Whether to override the $get_time from option value.
	 * @return string The timestamp format used in PHP date.
	 */
	public function get_timestamp_format( $override_get_time = null ) {

		$get_time = isset( $override_get_time )
			? $override_get_time
			: $this->uses_time_in_timestamp_format();

		return \apply_filters_ref_array(
			'the_seo_framework_timestamp_format',
			[
				$get_time ? 'Y-m-d\TH:iP' : 'Y-m-d',
				$get_time,
			]
		);
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
	 * Merges arrays distinctly, much like `array_merge()`, but then for multidimensionals.
	 * Unlike PHP's `array_merge_recursive()`, this method doesn't convert non-unique keys as sequential.
	 *
	 * A do-while is faster than while. Sorry for the legibility.
	 *
	 * @since 4.1.4
	 *
	 * @param array ...$arrays The arrays to merge. The rightmost array's values are dominant.
	 * @return array The merged arrays.
	 */
	public function array_merge_recursive_distinct( array ...$arrays ) {

		$i = \count( $arrays );

		if ( 2 === $i ) foreach ( $arrays[1] as $key => $value ) {
			$arrays[0][ $key ] =
				isset( $arrays[0][ $key ] ) && \is_array( $arrays[0][ $key ] )
				? $this->array_merge_recursive_distinct( $arrays[0][ $key ], $value )
				: $value;
		} else do {
			// phpcs:ignore -- Imagine assigning from right to left, but also left to right. Yes:
			$arrays[ --$i - 1 ] = $this->array_merge_recursive_distinct( $arrays[ $i - 1 ], $arrays[ $i ] );
		} while ( $i > 1 );

		return $arrays[0];
	}

	/**
	 * Shortens string and adds ellipses when over a threshold in length.
	 *
	 * @since 3.1.0
	 *
	 * @param string $string The string to test and maybe trim
	 * @param int    $over   The character limit. Must be over 0 to have effect.
	 *                       Bug: If 1 is given, the returned string length will be 3.
	 *                       Bug: If 2 is given, the returned string will only consist of the hellip.
	 * @return string
	 */
	public function hellip_if_over( $string, $over = 0 ) {

		if ( $over > 0 && \strlen( $string ) > $over ) {
			$string = substr( $string, 0, abs( $over - 2 ) ) . ' &hellip;';
		}

		return $string;
	}

	/**
	 * Counts words encounters from input string.
	 * Case insensitive. Returns first encounter of each word if found multiple times.
	 *
	 * Will only return words that are above set input thresholds.
	 *
	 * @since 2.7.0
	 * @since 3.1.0 This method now uses PHP 5.4+ encoding, capable of UTF-8 interpreting,
	 *              instead of relying on PHP's incomplete encoding table.
	 *              This does mean that the functionality is crippled when the PHP
	 *              installation isn't unicode compatible; this is unlikely.
	 * @since 4.0.0 1. Now expects PCRE UTF-8 encoding support.
	 *              2. Moved input-parameter alterting filters outside of this function.
	 *              3. Short length now works as intended, instead of comparing as less, it compares as less or equal to.
	 *
	 * @param string $string Required. The string to count words in.
	 * @param int    $dupe_count Minimum amount of words to encounter in the string.
	 *                      Set to 0 to count all words longer than $short_length.
	 * @param int    $dupe_short Minimum amount of words to encounter in the string that fall under the
	 *                           $short_length. Set to 0 to consider all words with $amount.
	 * @param int    $short_length The maximum string length of a word to pass for $dupe_short
	 *                             instead of $count. Set to 0 to ignore $count, and use $dupe_short only.
	 * @return array Containing arrays of words with their count.
	 */
	public function get_word_count( $string, $dupe_count = 3, $dupe_short = 5, $short_length = 3 ) {

		$string = html_entity_decode( $string );
		$string = \wp_check_invalid_utf8( $string );

		if ( ! $string ) return [];

		static $use_mb;

		isset( $use_mb ) or ( $use_mb = \extension_loaded( 'mbstring' ) );

		// TODO does this test well for "we're"? We haven't had any reports, though.
		$word_list = preg_split(
			'/[^\p{L}\p{M}\p{N}\p{Pc}\p{Cc}]+/mu',
			$use_mb ? mb_strtolower( $string ) : strtolower( $string ),
			-1,
			PREG_SPLIT_OFFSET_CAPTURE | PREG_SPLIT_NO_EMPTY
		);

		$words_too_many = [];

		if ( \count( $word_list ) ) :
			$words = [];
			foreach ( $word_list as $wli ) {
				//= { $words[ int Offset ] => string Word }
				$words[ $wli[1] ] = $wli[0];
			}

			$word_count = array_count_values( $words );

			// We're going to fetch words based on position, and then flip it to become the key.
			$word_keys = array_flip( array_reverse( $words, true ) );

			foreach ( $word_count as $word => $count ) {
				if ( ( $use_mb ? mb_strlen( $word ) : \strlen( $word ) ) <= $short_length ) {
					$run = $count >= $dupe_short;
				} else {
					$run = $count >= $dupe_count;
				}

				if ( $run ) {
					//! Don't use mb_* here. preg_split's offset is in bytes, NOT multibytes.
					$args = [
						'pos' => $word_keys[ $word ],
						'len' => \strlen( $word ),
					];

					$first_encountered_word = substr( $string, $args['pos'], $args['len'] );

					// Found words that are used too frequently.
					$words_too_many[] = [ $first_encountered_word => $count ];
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
	 * @since 3.0.4 Now uses WCAG's relative luminance formula
	 * @link https://www.w3.org/TR/2008/REC-WCAG20-20081211/#visual-audio-contrast-contrast
	 * @link https://www.w3.org/WAI/GL/wiki/Relative_luminance
	 *
	 * @param string $hex The 3 to 6 character RGB hex. The '#' prefix may be added.
	 * @return string The hexadecimal RGB relative font color, without '#' prefix.
	 */
	public function get_relative_fontcolor( $hex = '' ) {

		$hex = ltrim( $hex, '#' );

		// #rgb = #rrggbb
		if ( 3 === \strlen( $hex ) )
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];

		$hex = str_split( $hex, 2 );

		// Convert to usable numerics.
		$r = hexdec( $hex[0] );
		$g = hexdec( $hex[1] );
		$b = hexdec( $hex[2] );

		$get_relative_luminance = static function( $v ) {
			//= Convert to 0~1 value.
			$v /= 255;

			if ( $v > .03928 ) {
				$lum = ( ( $v + .055 ) / 1.055 ) ** 2.4;
			} else {
				$lum = $v / 12.92;
			}
			return $lum;
		};

		// Use sRGB for relative luminance.
		$sr = 0.2126 * $get_relative_luminance( $r );
		$sg = 0.7152 * $get_relative_luminance( $g );
		$sb = 0.0722 * $get_relative_luminance( $b );

		$rel_lum = ( $sr + $sg + $sb );

		// Build light greyscale.
		$gr = ( $r * 0.2989 / 8 ) * $rel_lum;
		$gg = ( $g * 0.5870 / 8 ) * $rel_lum;
		$gb = ( $b * 0.1140 / 8 ) * $rel_lum;

		//= Invert colors if they hit luminance boundaries.
		if ( $rel_lum < 0.5 ) {
			// Build dark greyscale.
			$gr = 255 - $gr;
			$gg = 255 - $gg;
			$gb = 255 - $gb;
		}

		// Build RGB hex.
		$retr = str_pad( dechex( round( $gr ) ), 2, '0', STR_PAD_LEFT );
		$retg = str_pad( dechex( round( $gg ) ), 2, '0', STR_PAD_LEFT );
		$retb = str_pad( dechex( round( $gb ) ), 2, '0', STR_PAD_LEFT );

		return $retr . $retg . $retb;
	}

	/**
	 * Returns sitemap color scheme.
	 *
	 * @since 2.8.0
	 * @since 4.0.5 Changed default colors to be more in line with WordPress.
	 *
	 * @param bool $get_defaults Whether to get the default colors.
	 * @return array The sitemap colors.
	 */
	public function get_sitemap_colors( $get_defaults = false ) {

		if ( $get_defaults ) {
			$colors = [
				'main'   => '#222222',
				'accent' => '#00a0d2',
			];
		} else {
			$main   = $this->s_color_hex( $this->get_option( 'sitemap_color_main' ) );
			$accent = $this->s_color_hex( $this->get_option( 'sitemap_color_accent' ) );

			$options = [
				'main'   => $main ? '#' . $main : '',
				'accent' => $accent ? '#' . $accent : '',
			];

			$options = array_filter( $options );

			$colors = array_merge( $this->get_sitemap_colors( true ), $options );
		}

		return $colors;
	}

	/**
	 * Converts markdown text into HMTL.
	 * Does not support list or block elements. Only inline statements.
	 *
	 * @since 2.8.0
	 * @since 2.9.0 1. Removed word boundary requirement for strong.
	 *              2. Now lets regex count their numeric values in string.
	 *              3. Fixed header 1~6 calculation.
	 * @since 2.9.3 Added $args parameter.
	 * @since 4.0.3 Added a workaround for connected em/strong elements.
	 * @since 4.1.4 Offloaded to `The_SEO_Framework\Interpreters\Markdown::convert()`
	 * @link https://wordpress.org/plugins/about/readme.txt
	 *
	 * @param string $text    The text that might contain markdown. Expected to be escaped.
	 * @param array  $convert The markdown style types wished to be converted.
	 *                        If left empty, it will convert all.
	 * @param array  $args    The function arguments.
	 * @return string The markdown converted text.
	 */
	public function convert_markdown( $text, $convert = [], $args = [] ) {
		return Interpreters\Markdown::convert( $text, $convert, $args );
	}
}
