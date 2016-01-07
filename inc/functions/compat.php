<?php
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
 * @since 1.3.0
 *
 * Rewritten
 * @since 2.3.5
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
 * @uses substr
 * @return mb_substr
 *
 * @since 1.3.0
 *
 * Rewritten
 * @since 2.3.5
 */
if ( ! function_exists( 'mb_substr' ) ) :
	function mb_substr( $str, $start, $length = null, $encoding = null ) {
		return _mb_substr( $str, $start, $length, $encoding );
	}
endif;

/*
 * Only understands UTF-8 and 8bit.  All other character sets will be treated as 8bit.
 * For $encoding === UTF-8, the $str input is expected to be a valid UTF-8 byte sequence.
 * The behavior of this function for invalid inputs is undefined.
 */
if ( ! function_exists( '_mb_substr' ) ) :
	function _mb_substr( $str, $start, $length = null, $encoding = null ) {
		if ( null === $encoding ) {
			$encoding = get_option( 'blog_charset' );
		}

		// The solution below works only for UTF-8,
		// so in case of a different charset just use built-in substr()
		if ( ! in_array( $encoding, array( 'utf8', 'utf-8', 'UTF8', 'UTF-8' ) ) ) {
			return is_null( $length ) ? substr( $str, $start ) : substr( $str, $start, $length );
		}

		if ( _wp_can_use_pcre_u() ) {
			// Use the regex unicode support to separate the UTF-8 characters into an array
			preg_match_all( '/./us', $str, $match );
			$chars = is_null( $length ) ? array_slice( $match[0], $start ) : array_slice( $match[0], $start, $length );
			return implode( '', $chars );
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

		$chars = array( '' ); // Start with 1 element instead of 0 since the first thing we do is pop
		do {
			// We had some string left over from the last round, but we counted it in that last round.
			array_pop( $chars );

			// Split by UTF-8 character, limit to 1000 characters (last array element will contain the rest of the string)
			$pieces = preg_split( $regex, $str, 1000, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );

			$chars = array_merge( $chars, $pieces );
		} while ( count( $pieces ) > 1 && $str = array_pop( $pieces ) ); // If there's anything left over, repeat the loop.

		return join( '', array_slice( $chars, $start, $length ) );
	}
endif;

/**
 * Extended charset support
 *
 * @uses strpos
 * @return mb_strpos
 *
 * @since 1.3.0
 *
 * Rewritten.
 * @since 2.3.5
 *
 * @license GLPv2 or later
 */
if ( ! function_exists( 'mb_strpos' ) ) :
	function mb_strpos( $haystack, $needle, $offset = 0, $encoding = null ) {
		return _mb_strpos( $haystack, $needle, $offset, $encoding );
	}
endif;

/**
 * Only understands UTF-8 and 8bit.  All other character sets will be treated as 8bit.
 * For $encoding === UTF-8, the $str input is expected to be a valid UTF-8 byte sequence.
 * The behavior of this function for invalid inputs is PHP compliant.
 *
 * @license GLPv2 or later
 */
if ( ! function_exists( '_mb_strpos' ) ) :
	function _mb_strpos( $haystack, $needle, $offset = 0, $encoding = null ) {

		if ( null === $encoding ) {
			$encoding = get_option( 'blog_charset' );
		}

		// The solution below works only for UTF-8,
		// so in case of a different charset just use built-in strpos()
		if ( ! in_array( $encoding, array( 'utf8', 'utf-8', 'UTF8', 'UTF-8' ) ) ) {
			return $offset === 0 ? strpos( $haystack, $needle ) : strpos( $haystack, $needle, $offset );
		}

		$haystack_len = mb_strlen( $haystack );

		if ( $offset < (int) 0 || $offset > $haystack_len ) {
			trigger_error( 'mb_strpos(): Offset not contained in string', E_USER_WARNING );
			return false;
		}

		if ( ! is_string( $needle ) ) {
			$needle = (string) $needle;

			if ( ! is_string( $needle ) ) {
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

			$pos = key( array_intersect( $match_h[0], $match_n[0] ) );

			if ( empty( $pos ) ) {
				return false;
			}

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
		if ( in_array( $match_n[0], $match_hs ) ) {
			return (int) key( array_intersect( $match_h, $match_n ) );
		} else {
			return false;
		}
	}
endif;
