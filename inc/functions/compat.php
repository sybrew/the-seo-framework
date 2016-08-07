<?php

defined( 'ABSPATH' ) or die;

/**
 * Returns whether PCRE/u (PCRE_UTF8 modifier) is available for use.
 *
 * @ignore
 * @since WordPress 4.2.2
 * @access private
 *
 * @staticvar string $utf8_pcre
 *
 * @param bool $set - Used for testing only
 *             null   : default - get PCRE/u capability
 *             false  : Used for testing - return false for future calls to this function
 *             'reset': Used for testing - restore default behavior of this function
 *
 * WordPress core function.
 * Implemented in this plugin for compatibility with older WordPress versions.
 * @since 2.3.5
 */
if ( ! function_exists( '_wp_can_use_pcre_u' ) ) :
	function _wp_can_use_pcre_u( $set = null ) {
		static $utf8_pcre = 'reset';

		if ( null !== $set ) {
			$utf8_pcre = $set;
		}

		if ( 'reset' === $utf8_pcre ) {
			$utf8_pcre = @preg_match( '/^./u', 'a' );
		}

		return $utf8_pcre;
	}
endif;

/**
 * Extended charset support
 *
 * @uses strlen
 * @return mb_strlen
 *
 * @since 1.3.0 The SEO Framework
 * @since 4.2.0 WordPress Core
 *
 */
if ( ! function_exists( 'mb_strlen' ) ) :
	function mb_strlen( $str, $encoding = null ) {
		return _mb_strlen( $str, $encoding );
	}
endif;

/*
* Only understands UTF-8 and 8bit.  All other character sets will be treated as 8bit.
* For $encoding === UTF-8, the $str input is expected to be a valid UTF-8 byte sequence.
* The behavior of this function for invalid inputs is undefined.
*/
if ( ! function_exists( '_mb_strlen' ) ) :
	function _mb_strlen( $str, $encoding = null ) {
		if ( null === $encoding ) {
			$encoding = get_option( 'blog_charset' );
		}

		// The solution below works only for UTF-8,
		// so in case of a different charset just use built-in strlen()
		if ( ! in_array( $encoding, array( 'utf8', 'utf-8', 'UTF8', 'UTF-8' ) ) ) {
			return strlen( $str );
		}

		if ( _wp_can_use_pcre_u() ) {
			// Use the regex unicode support to separate the UTF-8 characters into an array
			preg_match_all( '/./us', $str, $match );
			return count( $match[0] );
		}

		$regex = '/(?:
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

		$count = 1; // Start at 1 instead of 0 since the first thing we do is decrement
		do {
			// We had some string left over from the last round, but we counted it in that last round.
			$count--;

			// Split by UTF-8 character, limit to 1000 characters (last array element will contain the rest of the string)
			$pieces = preg_split( $regex, $str, 1000 );

			// Increment
			$count += count( $pieces );
		} while ( $str = array_pop( $pieces ) ); // If there's anything left over, repeat the loop.

		// Fencepost: preg_split() always returns one extra item in the array
		return --$count;
	}
endif;

/**
 * Extended charset support
 *
 * @see _mb_strpos()
 *
 * @param string		$haystack	The string to search in.
 * @param mixed 		$needle		If needle is not a string, it is converted to an integer and applied as the ordinal value of a character.
 * @param int			$offset		Optional, search will start this number of characters counted from the beginning of the string. The offset cannot be negative.
 * @param string|null	$encoding	Optional. Character encoding to use. Default null.
 *
 * @license GLPv2 or later
 * @return int Position of first occurence found of $haystack of `$needle`.
 */
if ( ! function_exists( 'mb_strpos' ) ) :
	function mb_strpos( $haystack, $needle, $offset = 0, $encoding = null ) {
		return _mb_strpos( $haystack, $needle, $offset, $encoding );
	}
endif;

/**
 * Compat function to mimic mb_strpos().
 *
 * Only understands UTF-8 and 8bit.  All other character sets will be treated as 8bit.
 * For $encoding === UTF-8, the $str input is expected to be a valid UTF-8 byte sequence.
 * The behavior of this function for invalid inputs is PHP compliant.
 *
 * @since 2.2.0 The SEO Framework
 * @license GLPv2 or later
 *
 * @param string		$haystack	The string to search in.
 * @param mixed 		$needle		If needle is not a string, it is converted to an integer and applied as the ordinal value of a character.
 * @param int			$offset		Optional, search will start this number of characters counted from the beginning of the string. The offset cannot be negative.
 * @param string|null	$encoding	Optional. Character encoding to use. Default null.
 *
 * @license GLPv2 or later
 * @return int Position of first occurence found of $haystack of `$needle`.
 */
if ( ! function_exists( '_mb_strpos' ) ) :
	function _mb_strpos( $haystack, $needle, $offset = 0, $encoding = null ) {

		if ( null === $encoding ) {
			$encoding = get_option( 'blog_charset' );
		}

		// The solution below works only for UTF-8,
		// So in case of a different charset just use built-in strpos()
		if ( ! in_array( $encoding, array( 'utf8', 'utf-8', 'UTF8', 'UTF-8' ) ) ) {
			return $offset === 0 ? strpos( $haystack, $needle ) : strpos( $haystack, $needle, $offset );
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
			preg_match_all( "/./us", $haystack, $match_h );
			preg_match_all( "/$needle/us", $haystack_sub, $match_n );

			$inter = array_intersect( $match_h[0], $match_n[0] );

			if ( ! isset( $inter ) )
				return false;

			//* Prevent bugs, (re)assign var.
			$pos = null;

			// Find first occurence greater than or equal to offset
			foreach ( $inter as $key => $value ) {
				if ( $key >= $offset ) {
					$pos = $key;
					break;
				}
			}

			//* No key has been found.
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
		if ( false !== in_array( $match_n[0], $match_hs ) ) {
			$inter = array_intersect( $match_h, $match_n );

			if ( ! isset( $inter ) )
				return false;

			//* Prevent bugs, (re)assign var.
			$pos = null;

			// Find first occurence greater than or equal to offset
			foreach ( $inter as $key => $value ) {
				if ( $key >= $offset ) {
					$pos = $key;
					break;
				}
			}

			//* No key has been found.
			if ( ! isset( $pos ) )
				return false;

			return (int) $pos;
		} else {
			return false;
		}
	}
endif;

/**
 * A wrapper for PHP's parse_url() function that handles edgecases in < PHP 5.4.7
 *
 * PHP 5.4.7 expanded parse_url()'s ability to handle non-absolute url's, including
 * schemeless and relative url's with :// in the path, this works around those
 * limitations providing a standard output on PHP 5.2~5.4+.
 *
 * Error suppression is used as prior to PHP 5.3.3, an E_WARNING would be generated
 * when URL parsing failed.
 *
 * @since 2.7.0 The SEO Framework
 * @since 4.4.0 WordPress Core
 *
 * @param string $url The URL to parse.
 * @return bool|array False on failure; Array of URL components on success;
 *                    See parse_url()'s return values.
 */
if ( ! function_exists( 'wp_parse_url' ) ) :
	function wp_parse_url( $url ) {
		$parts = @parse_url( $url );
		if ( ! $parts ) {
			// < PHP 5.4.7 compat, trouble with relative paths including a scheme break in the path
			if ( '/' === $url[0] && false !== strpos( $url, '://' ) ) {
				// Since we know it's a relative path, prefix with a scheme/host placeholder and try again
				if ( ! $parts = @parse_url( 'placeholder://placeholder' . $url ) ) {
					return $parts;
				}
				// Remove the placeholder values
				unset( $parts['scheme'], $parts['host'] );
			} else {
				return $parts;
			}
		}

		// < PHP 5.4.7 compat, doesn't detect schemeless URL's host field
		if ( '//' === substr( $url, 0, 2 ) && ! isset( $parts['host'] ) ) {
			$path_parts = explode( '/', substr( $parts['path'], 2 ), 2 );
			$parts['host'] = $path_parts[0];
			if ( isset( $path_parts[1] ) ) {
				$parts['path'] = '/' . $path_parts[1];
			} else {
				unset( $parts['path'] );
			}
		}

		return $parts;
	}
endif;
