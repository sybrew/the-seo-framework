<?php
/**
 * @package The_SEO_Framework\Compat\PHP\mbstring
 * @subpackage The_SEO_Framework\Compatibility
 *
 * @ignore this file isn't loaded.
 */

// phpcs:disable -- This file isn't loaded.

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * Extended charset support
 *
 * @see _mb_strpos()
 *
 * @param string      $haystack The string to search in.
 * @param mixed       $needle   If needle is not a string, it is converted to an integer and applied as the ordinal value of a character.
 * @param int         $offset   Optional, search will start this number of characters counted from the beginning of the string. The offset cannot be negative.
 * @param string|null $encoding Optional. Character encoding to use. Default null.
 *
 * @license GLPv2 or later
 * @return int Position of first occurrence found of $haystack of `$needle`.
 */
if ( ! function_exists( 'mb_strpos' ) ) :
	function mb_strpos( $haystack, $needle, $offset = 0, $encoding = null ) {
		return _mb_strpos( $haystack, $needle, $offset, $encoding );
	}
endif;

/**
 * Compat function to mimic mb_strpos().
 *
 * Only understands UTF-8 and 8bit. All other character sets will be treated as 8bit.
 * For $encoding === UTF-8, the $str input is expected to be a valid UTF-8 byte sequence.
 * The behavior of this function for invalid inputs is PHP compliant.
 *
 * @since 2.2.0 The SEO Framework
 * @license GLPv2 or later
 *
 * @param string      $haystack The string to search in.
 * @param mixed       $needle   If needle is not a string, it is converted to an integer and applied as the ordinal value of a character.
 * @param int         $offset   Optional, search will start this number of characters counted from the beginning of the string. The offset cannot be negative.
 * @param string|null $encoding Optional. Character encoding to use. Default null.
 *
 * @license GLPv2 or later
 * @return int Position of first occurrence found of $haystack of `$needle`.
 */
if ( ! function_exists( '_mb_strpos' ) ) :
	function _mb_strpos( $haystack, $needle, $offset = 0, $encoding = null ) {

		if ( null === $encoding ) {
			$encoding = get_option( 'blog_charset' );
		}

		// The solution below works only for UTF-8,
		// So in case of a different charset just use built-in strpos()
		if ( ! in_array( $encoding, array( 'utf8', 'utf-8', 'UTF8', 'UTF-8' ), true ) ) {
			return 0 === $offset ? strpos( $haystack, $needle ) : strpos( $haystack, $needle, $offset );
		}

		$haystack_len = mb_strlen( $haystack );

		if ( $offset < (int) 0 || $offset > $haystack_len ) {
			trigger_error( 'mb_strpos(): Offset not contained in string', E_USER_WARNING );
			return false;
		}

		if ( ! is_string( $needle ) ) {
			$needle = (int) $needle;

			if ( ! is_int( $needle ) ) {
				trigger_error( 'mb_strpos(): Array to string conversion', E_USER_WARNING );
				return false;
			}
		}

		if ( empty( $needle ) ) {
			trigger_error( 'mb_strpos(): Empty needle', E_USER_WARNING );
			return false;
		}

		// Slice off the offset
		$haystack_sub = mb_substr( $haystack, $offset );

		if ( _wp_can_use_pcre_u() ) {
			// Use the regex unicode support to separate the UTF-8 characters into an array
			preg_match_all( '/./us', $haystack, $match_h );
			preg_match_all( "/$needle/us", $haystack_sub, $match_n );

			$inter = array_intersect( $match_h[0], $match_n[0] );

			if ( ! isset( $inter ) )
				return false;

			// Prevent bugs, (re)assign var.
			$pos = null;

			// Find first occurrence greater than or equal to offset
			foreach ( $inter as $key => $value ) {
				if ( $key >= $offset ) {
					$pos = $key;
					break;
				}
			}

			// No key has been found.
			if ( ! isset( $pos ) )
				return false;

			return (int) $pos;
		}

		$regex = '/(
			  [\x00-\x7F]                  # single-byte sequences   0xxxxxxx
			| [\xC2-\xDF][\x80-\xBF]       # double-byte sequences   110xxxxx 10xxxxxx
			| \xE0[\xA0-\xBF][\x80-\xBF]   # triple-byte sequences   1110xxxx 10xxxxxx * 2
			| [\xE1-\xEC][\x80-\xBF]{2}
			| \xED[\x80-\x9F][\x80-\xBF]
			| [\xEE-\xEF][\x80-\xBF]{2}
			| \xF0[\x90-\xBF][\x80-\xBF]{2} # four-byte sequences   11110xxx 10xxxxxx * 3
			| [\xF1-\xF3][\x80-\xBF]{3}
			| \xF4[\x80-\x8F][\x80-\xBF]{2}
		)/x';

		/**
		 * Place haystack into array
		 */
		$match_h = array( '' ); // Start with 1 element instead of 0 since the first thing we do is pop
		do {
			// We had some string left over from the last round, but we counted it in that last round.
			array_pop( $match_h );

			// Split by UTF-8 character, limit to 1000 characters (last array element will contain the rest of the string)
			$pieces = preg_split( $regex, $haystack, 1000, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );

			$match_h = array_merge( $match_h, $pieces );
		} while ( count( $pieces ) > 1 && $haystack = array_pop( $pieces ) ); // If there's anything left over, repeat the loop.

		/**
		 * Place haystack offset into array
		 */
		$match_hs = array( '' ); // Start with 1 element instead of 0 since the first thing we do is pop
		do {
			// We had some string left over from the last round, but we counted it in that last round.
			array_pop( $match_hs );

			// Split by UTF-8 character, limit to 1000 characters (last array element will contain the rest of the string)
			$pieces = preg_split( $regex, $haystack_sub, 1000, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );

			$match_hs = array_merge( $match_hs, $pieces );
		} while ( count( $pieces ) > 1 && $haystack_sub = array_pop( $pieces ) ); // If there's anything left over, repeat the loop.

		/**
		 * Put needle into array
		 */
		$match_n = array( '' ); // Start with 1 element instead of 0 since the first thing we do is pop
		do {
			// We had some string left over from the last round, but we counted it in that last round.
			array_pop( $match_n );

			// Split by UTF-8 character, limit to 1000 characters (last array element will contain the rest of the string)
			$pieces = preg_split( $regex, $needle, 1000, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );

			$match_n = array_merge( $match_n, $pieces );
		} while ( count( $pieces ) > 1 && $needle = array_pop( $pieces ) ); // If there's anything left over, repeat the loop.

		/**
		 * Compute match of haystack offset with needle
		 * If passed, return the array key number within the full haystack.
		 */
		if ( false !== in_array( $match_n[0], $match_hs, true ) ) {
			$inter = array_intersect( $match_h, $match_n );

			if ( ! isset( $inter ) )
				return false;

			// Prevent bugs, (re)assign var.
			$pos = null;

			// Find first occurrence greater than or equal to offset
			foreach ( $inter as $key => $value ) {
				if ( $key >= $offset ) {
					$pos = $key;
					break;
				}
			}

			// No key has been found.
			if ( ! isset( $pos ) )
				return false;

			return (int) $pos;
		} else {
			return false;
		}
	}
endif;
