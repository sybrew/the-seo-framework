<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Core
 * @see ./index.php for facade details.
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\is_headless;

use \The_SEO_Framework\Data;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * @since 4.3.0 1. Deprecated $the_seo_framework_debug
 *              2. Deprecated $script_debug
 *              3. Deprecated $seo_settings_page_slug
 *              4. Deprecated $loaded
 */
class Core {

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
	 * @since 4.3.0 Now protects against fatal errors on PHP 8.2 or later.
	 *
	 * @param string $name  The property name.
	 * @param mixed  $value The property value.
	 */
	final public function __set( $name, $value ) {

		switch ( $name ) {
			case 'the_seo_framework_debug':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 4.3.0; set constant THE_SEO_FRAMEWORK_DEBUG' );
				return false;
			case 'script_debug':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 4.3.0; set constant SCRIPT_DEBUG' );
				return false;
			case 'seo_settings_page_slug':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 4.3.0; set constant THE_SEO_FRAMEWORK_SITE_OPTIONS_SLUG' );
				return false;
		}

		/**
		 * For now, no deprecation is being handled; as no properties have been deprecated. Just removed.
		 */
		$this->_inaccessible_p_or_m( "tsf()->$name", 'unknown' );

		// Invoke default behavior: Write variable if it's not protected.
		if ( property_exists( $this, $name ) )
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
	 * @since 4.3.0 Removed 'load_option' deprecation.
	 *
	 * @param string $name The property name.
	 * @return mixed
	 */
	final public function __get( $name ) {

		switch ( $name ) {
			case 'is_headless':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 4.3.0; use function \The_SEO_Framework\is_headless()' );
				return is_headless();
			case 'pretty_permalinks':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 4.3.0; use tsf()->query()->utils()->using_pretty_permalinks()' );
				return $this->query()->utils()->using_pretty_permalinks();
			case 'script_debug':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 4.3.0; use constant SCRIPT_DEBUG' );
				return \SCRIPT_DEBUG;
			case 'the_seo_framework_debug':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 4.3.0; use constant THE_SEO_FRAMEWORK_DEBUG' );
				return \THE_SEO_FRAMEWORK_DEBUG;
			case 'the_seo_framework_use_transients':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 4.3.0; with no alternative available' );
				return true;
			case 'seo_settings_page_slug':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 4.3.0; use constant THE_SEO_FRAMEWORK_SITE_OPTIONS_SLUG' );
				return \THE_SEO_FRAMEWORK_SITE_OPTIONS_SLUG;
			case 'loaded':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 4.3.0; you may drop the loaded check.' );
				return true;
			case 'inpost_nonce_name':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 4.3.0; you should make your own.' );
				return Data\Admin\Post::$nonce_name;
			case 'inpost_nonce_field':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 4.3.0; you should make your own.' );
				return Data\Admin\Post::$nonce_action;
		}

		$this->_inaccessible_p_or_m( "tsf()->$name", 'unknown' );
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

		$depr_class ??= new Internal\Deprecated;

		if ( \is_callable( [ $depr_class, $name ] ) )
			return \call_user_func_array( [ $depr_class, $name ], $arguments );

		$this->_inaccessible_p_or_m( "tsf()->$name()" );
	}

	/**
	 * Converts time from GMT input to given format.
	 *
	 * @since 2.7.0
	 * @since 4.0.4 Now uses `gmdate()` instead of `date()`.
	 *
	 * @param string $format The datetime format.
	 * @param string $time The GMT time. Expects timezone to be omitted.
	 * @return string The converted time. Empty string if no $time is given.
	 */
	public function gmt2date( $format = 'Y-m-d', $time = '' ) {
		return $time ? gmdate( $format, strtotime( "$time GMT" ) ) : '';
	}

	/**
	 * Returns timestamp format based on timestamp settings.
	 * Note that this must be XML safe.
	 *
	 * @since 3.0.0
	 * @since 4.1.4 1. Added options-override parameter.
	 *              2. Added return value filter.
	 * @link https://www.w3.org/TR/NOTE-datetime
	 *
	 * @param null|bool $override_get_time Whether to override the $get_time from option value.
	 * @return string The timestamp format used in PHP date.
	 */
	public function get_timestamp_format( $override_get_time = null ) {

		$get_time = $override_get_time ?? $this->uses_time_in_timestamp_format();

		/**
		 * @see For valid formats https://www.w3.org/TR/NOTE-datetime.
		 * @since 4.1.4
		 * @param string The full timestamp format. Must be XML safe and in ISO 8601 datetime notation.
		 * @param bool   True if time is requested, false if only date.
		 */
		return \apply_filters(
			'the_seo_framework_timestamp_format',
			$get_time ? 'Y-m-d\TH:iP' : 'Y-m-d',
			$get_time,
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
		return '1' === Data\Plugin::get_option( 'timestamps_format' );
	}

	/**
	 * Shortens string and adds ellipses when over a threshold in length.
	 *
	 * @since 3.1.0
	 * @since 4.2.0 No longer prepends a space before the hellip.
	 *
	 * @param string $string The string to test and maybe trim
	 * @param int    $over   The character limit. Must be over 0 to have effect.
	 *                       Bug: If 1 is given, the returned string length will be 3.
	 *                       Bug: If 2 is given, the returned string will only consist of the hellip.
	 * @return string
	 */
	public function hellip_if_over( $string, $over = 0 ) {

		if ( $over > 0 && \strlen( $string ) > $over )
			$string = substr( $string, 0, abs( $over - 2 ) ) . '&hellip;';

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
	 * @since 4.2.0 Now supports detection of connector-dashes, connector-punctuation, and closing quotes,
	 *              and recognizes those as whole words.
	 * @since 4.3.0 Now converts input string as UTF-8. This mainly solves issues with attached quotes (d'anglais).
	 *
	 * @param string $string Required. The string to count words in.
	 * @param int    $dupe_count       Minimum amount of words to encounter in the string.
	 *                                 Set to 0 to count all words longer than $short_length.
	 * @param int    $dupe_short       Minimum amount of words to encounter in the string that fall under the
	 *                                 $short_length. Set to 0 to consider all words with $amount.
	 * @param int    $short_length     The maximum string length of a word to pass for $dupe_short
	 *                                 instead of $count. Set to 0 to ignore $count, and use $dupe_short only.
	 * @return array Containing arrays of words with their count.
	 */
	public function get_word_count( $string, $dupe_count = 3, $dupe_short = 5, $short_length = 3 ) {

		// Why not blog_charset? Because blog_charset is there only to onboard non-UTF-8 to UTF-8.
		$string = \wp_check_invalid_utf8( html_entity_decode( $string, \ENT_QUOTES, 'UTF-8' ) );

		if ( empty( $string ) )
			return [];

		// Not if-function-exists; we're going for speed over accuracy. Hosts must do their job correctly.
		$use_mb = memo( null, 'use_mb' ) ?? memo( \extension_loaded( 'mbstring' ), 'use_mb' );

		$word_list = preg_split(
			'/[^\p{Cc}\p{L}\p{N}\p{Pc}\p{Pd}\p{Pf}\'"]+/mu',
			$use_mb ? mb_strtolower( $string ) : strtolower( $string ),
			-1,
			\PREG_SPLIT_OFFSET_CAPTURE | \PREG_SPLIT_NO_EMPTY
		);

		if ( ! \count( $word_list ) )
			return [];

		$words = [];

		foreach ( $word_list as [ $_word, $_position ] )
			$words[ $_position ] = $_word;

		// We're going to fetch words based on position, and then flip it to become the key.
		$word_keys = array_flip( array_reverse( $words, true ) );

		foreach ( array_count_values( $words ) as $word => $count ) {
			if ( ( $use_mb ? mb_strlen( $word ) : \strlen( $word ) ) <= $short_length ) {
				$assert = $count >= $dupe_short;
			} else {
				$assert = $count >= $dupe_count;
			}

			if ( $assert ) {
				//! Don't use mb_* here. preg_split's offset is in bytes, NOT multibytes.
				$args = [
					'pos' => $word_keys[ $word ],
					'len' => \strlen( $word ),
				];

				$first_encountered_word = substr( $string, $args['pos'], $args['len'] );

				// phpcs:ignore, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- You need more PHP7.
				$words_too_many[] = [ $first_encountered_word => $count ];
			}
		}

		// phpcs:ignore, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- You don't love PHP7.
		return $words_too_many ?? [];
	}

	/**
	 * Calculates the relative font color according to the background, grayscale.
	 *
	 * @since 2.8.0
	 * @since 2.9.0 Now adds a little more relative softness based on rel_lum.
	 * @since 2.9.2 (Typo): Renamed from 'get_relatitve_fontcolor' to 'get_relative_fontcolor'.
	 * @since 3.0.4 Now uses WCAG's relative luminance formula.
	 * @since 4.2.0 Optimized code, but it now has some rounding changes at the end. This could
	 *              offset the returned values by 1/255th.
	 * @link https://www.w3.org/TR/2008/REC-WCAG20-20081211/#visual-audio-contrast-contrast
	 * @link https://www.w3.org/WAI/GL/wiki/Relative_luminance
	 *
	 * @param string $hex The 3 to 6+ character RGB hex. The '#' prefix may be added.
	 *                    RGBA/RRGGBBAA is supported, but the Alpha channels won't be returned.
	 * @return string The hexadecimal RGB relative font color, without '#' prefix.
	 */
	public function get_relative_fontcolor( $hex = '' ) {

		// TODO: To support RGBA, we must fill to 4 or 8 via sprintf `%0{1,2}x`
		// But doing this will add processing requirements for something we do not need... yet.
		$hex = ltrim( $hex, '#' );

		// Convert hex to usable numerics.
		[ $r, $g, $b ] = array_map(
			'hexdec',
			str_split(
				// rgb[..] == rrggbb[..].
				\strlen( $hex ) >= 6 ? $hex : "$hex[0]$hex[0]$hex[1]$hex[1]$hex[2]$hex[2]",
				2
			)
		);

		$get_relative_luminance = static function ( $v ) {
			// Convert hex to 0~1 float.
			$v /= 0xFF;

			if ( $v > .03928 ) {
				$lum = ( ( $v + .055 ) / 1.055 ) ** 2.4;
			} else {
				$lum = $v / 12.92;
			}
			return $lum;
		};

		// Calc relative Luminance using sRGB.
		$rl = .2126 * $get_relative_luminance( $r )
			+ .7152 * $get_relative_luminance( $g )
			+ .0722 * $get_relative_luminance( $b );

		// Build light greyscale using relative contrast.
		// Rounding is required for bitwise operation (PHP8.1+).
		// printf will round anyway when floats are detected. Diff in #opcodes should be minimal.
		$gr = round( $r * .2989 / 8 * $rl );
		$gg = round( $g * .5870 / 8 * $rl );
		$gb = round( $b * .1140 / 8 * $rl );

		// Invert grayscela if they pass the relative luminance midpoint.
		if ( $rl < .5 ) {
			$gr ^= 0xFF;
			$gg ^= 0xFF;
			$gb ^= 0xFF;
		}

		return vsprintf( '%02x%02x%02x', [ $gr, $gg, $gb ] );
	}

	/**
	 * Whether to display Extension Manager suggestions to the user based on several conditions.
	 *
	 * @since 4.2.4
	 * @uses TSF_DISABLE_SUGGESTIONS Set that to true if you don't like us.
	 *
	 * @return bool
	 */
	public function _display_extension_suggestions() {
		return \current_user_can( 'install_plugins' ) && ! ( \defined( 'TSF_DISABLE_SUGGESTIONS' ) && \TSF_DISABLE_SUGGESTIONS );
	}
}
