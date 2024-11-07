<?php
/**
 * @package The_SEO_Framework\Classes\Helper\Format\Strings
 * @subpackage The_SEO_Framework\Formatting
 */

namespace The_SEO_Framework\Helper\Format;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds methods for String interpretation and conversion.
 * String is a reserved keyword, so we use Strings.
 *
 * @since 5.0.0
 *
 * @access protected
 *         Use tsf()->format()->strings() instead.
 */
class Strings {

	/**
	 * Shortens string and adds ellipses when over a threshold in length.
	 *
	 * @since 3.1.0
	 * @since 4.2.0 No longer prepends a space before the hellip.
	 * @since 5.0.0 1. Now uses mb_* to determine the string length.
	 *              2. Moved from \The_SEO_Framework\Load.
	 *
	 * @param string $string The string to test and maybe trim
	 * @param int    $over   The character limit. Must be over 0 to have effect.
	 *                       Bug: If 1 is given, the returned string length will be 3.
	 *                       Bug: If 2 is given, the returned string will only consist of the hellip.
	 * @return string
	 */
	public static function hellip_if_over( $string, $over = 0 ) {

		if ( $over > 0 && \mb_strlen( $string ) > $over )
			return mb_substr( $string, 0, abs( $over - 2 ) ) . '&hellip;';

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
	 * @since 5.0.0 1. Now converts input string as UTF-8. This mainly solves issues with attached quotes (d'anglais).
	 *              2. Moved from \The_SEO_Framework\Load.
	 *
	 * @param string $string Required. The string to count words in.
	 * @param array  $args   {
	 *     Optional. The word counting arguments.
	 *
	 *     @type int $filter_under       Consider word duplication under this number not a duplicate, default 3.
	 *     @type int $filter_short_under Consider short word duplication under this number not a duplicate, default 5.
	 *     @type int $short_word_length  Consider words under this character length "short", default 3.
	 * }
	 * @return array Containing arrays of words with their count.
	 */
	public static function get_word_count( $string, $args = [] ) {

		// Why not blog_charset? Because blog_charset is there only to onboard non-UTF-8 to UTF-8.
		$string = \wp_check_invalid_utf8( html_entity_decode( $string, \ENT_QUOTES, 'UTF-8' ) );

		if ( empty( $string ) )
			return [];

		$args += [
			'filter_under'       => 3,
			'filter_short_under' => 5,
			'short_word_length'  => 3,
		];

		// Don't use polyfills; we're going for speed over accuracy. Hosts must do their job correctly.
		$use_mb = \extension_loaded( 'mbstring' );

		$word_list = preg_split(
			'/[^\p{Cc}\p{L}\p{N}\p{Pc}\p{Pd}\p{Pf}\'"]+/mu',
			$use_mb ? mb_strtolower( $string ) : strtolower( $string ),
			-1,
			\PREG_SPLIT_OFFSET_CAPTURE | \PREG_SPLIT_NO_EMPTY,
		);

		if ( empty( $word_list ) )
			return [];

		// 0 = word, 1 = offset. So we get [ offset => word ].
		// We want the offset because we relay how the word is first spelled.
		$words        = array_column( $word_list, 0, 1 );
		$word_offsets = array_flip( array_reverse( $words, true ) );

		$min_count      = min( $args['filter_under'], $args['filter_short_under'] );
		$words_too_many = [];

		foreach ( array_count_values( $words ) as $word => $count ) {
			// Skip strlen if the word is counted fewer than any requirements.
			if ( $count < $min_count ) continue;

			if ( ( $use_mb ? mb_strlen( $word ) : \strlen( $word ) ) <= $args['short_word_length'] ) {
				if ( $count < $args['filter_short_under'] )
					continue;
			} else {
				if ( $count < $args['filter_under'] )
					continue;
			}

			// !! Don't use mb_* here. preg_split's offset is in bytes, NOT multibytes.
			$first_encountered_word = substr( $string, $word_offsets[ $word ], \strlen( $word ) );

			$words_too_many[] = [ $first_encountered_word => $count ];
		}

		return $words_too_many;
	}

	/**
	 * Trims an sentence by word and determines sentence stops.
	 *
	 * Warning: Returns with entities encoded. The output is not safe for printing.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 1. Now uses smarter trimming.
	 *              2. Deprecated 2nd parameter.
	 *              3. Now has unicode support for sentence closing.
	 *              4. Now strips last three words when preceded by a sentence closing separator.
	 *              5. Now always leads with (inviting) dots, even if the sentence is shorter than $max_char_length.
	 * @since 4.0.0 1. Now stops parsing earlier on failure.
	 *              2. Now performs faster queries.
	 *              3. Now maintains last sentence with closing punctuations.
	 * @since 4.0.5 1. Now decodes the sentence input, improving accuracy, and so that HTML entities at
	 *                 the end won't be transformed into gibberish.
	 * @since 4.1.0 1. Now texturizes the sentence input, improving accuracy with included closing & final punctuation support.
	 *              2. Now performs even faster queries, in most situations. (0.2ms/0.02ms total (worst/best) @ PHP 7.3/PCRE 11).
	 *                 Mind you, this method probably boots PCRE and wptexturize; so, it'll be slower than what we noted--it's
	 *                 overhead that otherwise WP, the theme, or other plugin would cause anyway. So, deduct that.
	 *              3. Now recognizes connector and final punctuations for preliminary sentence bounding.
	 *              4. Leading punctuation now excludes symbols, special annotations, opening brackets and quotes,
	 *                 and marks used in some latin languages like ¡¿.
	 *              5. Is now able to always strip leading punctuation.
	 *              6. It will now strip leading colon characters.
	 *              7. It will now stop counting trailing words towards new sentences when a connector, dash, mark, or ¡¿ is found.
	 *              8. Now returns encoded entities once more. So that the return value can be treated the same as anything else
	 *                 revolving around descriptions--preventing double transcoding like `&amp;amp; > &amp; > &` instead of `&amp;`.
	 * @since 4.1.5 1. The second parameter now accepts values again. From "current description length" to minimum accepted char length.
	 *              2. Can now return an empty string when the input string doesn't satisfy the minimum character length.
	 *              3. The third parameter now defaults to 4096, so no longer unexpected results are created.
	 *              4. Resolved some backtracking issues.
	 *              5. Resolved an issue where a character followed by punctuation would cause the match to fail.
	 * @since 4.2.0 Now enforces at least a character length of 1. This prevents needless processing.
	 * @since 4.2.7 Now considers floating numerics as one word.
	 * @since 5.0.0 1. Moved from \The_SEO_Framework\Load.
	 *              2. Renamed from `trim_excerpt()`.
	 *              3. Anchored the first regex to the start prevent catastrophic backtracking when no spacing is found.
	 *              4. Forced a useful match in the first regex to prevent catastrophic backtracking in the second regex.
	 * @since 5.0.3 In the first regex, the last word of a sentence shorter than maximum length without leading punctuation is now considered.
	 * @see https://secure.php.net/manual/en/regexp.reference.unicode.php
	 *
	 * We use `[^\P{Po}\'\":]` because WordPress texturizes ' and " to fall under `\P{Po}`.
	 * This is perfect. Please have the courtesy to credit us when taking it. :)
	 *
	 * @param string $sentence        The untrimmed sentence. Expected not to contain any HTML operators.
	 * @param int    $min_char_length The minimum character length. Set to 0 to ignore the requirement.
	 *                                This is read as a SUGGESTION. Multibyte characters will create inaccuracies.
	 * @param int    $max_char_length At what point to shave off the sentence.
	 * @return string The trimmed sentence with encoded entities. Needs escaping prior printing.
	 */
	public static function clamp_sentence( $sentence, $min_char_length = 1, $max_char_length = 4096 ) {

		// At least 1.
		$min_char_length = max( 1, $min_char_length );

		// We should _actually_ use mb_strlen, but that's wasteful on resources for something benign.
		// We'll rectify that later, somewhat, where characters are transformed.
		// We could also use preg_match_all( '/./u' ); or count( preg_split( '/./u', $sentence, $min_char_length ) );
		// But, again, that'll eat CPU cycles.
		if ( \strlen( $sentence ) < $min_char_length )
			return '';

		// Decode to get a more accurate character length in Unicode.
		$sentence = trim( html_entity_decode( $sentence, \ENT_QUOTES, 'UTF-8' ) );

		// Find all words until $max_char_length, and trim when the last word boundary or punctuation is found.
		// Tries to match "\x20" when the sentence contains no spaces, subsequently failing because trim() already removed that.
		// Uses $ to consider cut-off endings under $max_char_length
		preg_match(
			\sprintf(
				'/^.{0,%d}(?:[^\P{Po}\'\":]|[\p{Pc}\p{Pd}\p{Pf}\p{Z}]|\x20|$)/su',
				$max_char_length,
			),
			$sentence,
			$matches,
		);

		$sentence = trim( $matches[0] ?? '' );

		if ( \strlen( $sentence ) < $min_char_length )
			return '';

		// Texturize to recognize the sentence structure. Decode thereafter since we get HTML returned.
		$sentence = html_entity_decode(
			\wptexturize( htmlentities(
				$sentence,
				\ENT_QUOTES,
				'UTF-8',
			) ),
			\ENT_QUOTES,
			'UTF-8',
		);

		/**
		 * Play with it here:
		 * https://regex101.com/r/u0DIgx/5/ (old)
		 * https://regex101.com/r/G92lUt/5 (old)
		 * https://regex101.com/r/dAqhWC/1 (current)
		 *
		 * TODO Group 4's match is repeated. However, referring to it as (4) will cause it to congeal into 3.
		 * TODO `([\p{Z}\w])` will try to match any word boundary even if there aren't any. This must be detected above.
		 *      e.g., a sentence consisting ONLY of `''''` will cause catastrophic backtracking.
		 * Note: Group 4 misses `?\p{Z}*` between `.+` and `[\p{Pc}`, but I couldn't find a use-case for it.
		 *
		 * Note to self: Do not anchor to start of sentence.
		 * Critically optimized (worst case: 217 logic steps), so the $matches don't make much sense. Bear with me:
		 *
		 * @param array $matches : {
		 *    0 : Full sentence.
		 *    1 : Sentence after leading punctuation (if any), but including opening punctuation, marks, and ¡¿, before first punctuation (if any).
		 *    2 : First one character following [1], always some form of punctuation. Won't be set if [3] is set.
		 *    3 : Following [1] until last punctuation that isn't some sort of connecting punctuation that's leading a word-boundary.
		 *    4 : First three words leading [3]. Connecting punctuations that splits words are included as non-countable.
		 *    5 : All extraneous characters leading [3] and/or [4]. If this isn't set, forgo including 4--it won't be meaningful.
		 * }
		 */
		preg_match(
			'/(?:\A[\p{P}\p{Z}]*?)?([\P{Po}\p{M}\xBF\xA1:\'\p{Z}]+[\p{Z}\w])(?:([^\P{Po}\p{M}\xBF\xA1:]\Z(*ACCEPT))|((?(?=.+(?:\w+[\p{Pc}\p{Pd}\p{Pf}\p{Z}]*){1,3}|[\p{Po}]\Z)(?:[^\p{Pe}\p{Pf}]*+.*[\p{Pe}\p{Pf}]+\Z(*ACCEPT)|.*[^\P{Po}\p{M}\xBF\xA1:][^\P{Nd}\p{Z}]*)|.*\Z(*ACCEPT)))(?>(.+?\p{Z}*(?:\w+[\p{Pc}\p{Pd}\p{Pf}\p{Z}]*){1,3})|[^\p{Pc}\p{Pd}\p{M}\xBF\xA1:])?)(.+)?/su',
			$sentence,
			$matches,
		);

		// Unmatched isn't set. Since we count from last to first match, we don't need to test strlen().
		if ( isset( $matches[5] ) ) {
			$sentence = "$matches[1]$matches[3]$matches[4]$matches[5]";
			// Skip 4. It's useless content without 5.
		} elseif ( isset( $matches[3] ) ) {
			$sentence = "$matches[1]$matches[3]";
		} elseif ( isset( $matches[2] ) ) {
			// TODO Can we skip the next cleanup if we reach this?
			$sentence = "$matches[1]$matches[2]";
		} elseif ( isset( $matches[1] ) ) {
			$sentence = $matches[1];
		} else {
			// The sentence consists of control characters -- ditch it.
			return '';
		}

		if ( \strlen( $sentence ) < $min_char_length )
			return '';

		/**
		 * @param array $matches: {
		 *    1 : Full match until leading punctuation.
		 *    2 : Spaces before (if any) and including closing leading punctuation (if any).
		 *    3 : Non-closing leading punctuation and spaces (if any).
		 * }
		 */
		preg_match(
			'/(.+[^\p{Pc}\p{Pd}\p{M}\xBF\xA1:;,\p{Z}\p{Po}])+?(\p{Z}*?[^\p{Pc}\p{Pd}\p{M}\xBF\xA1:;,\p{Z}]+)?([\p{Pc}\p{Pd}\p{M}\xBF\xA1:;,\p{Z}]+)?/su',
			$sentence,
			$matches,
		);

		if ( isset( $matches[2] ) && \strlen( $matches[2] ) ) {
			$sentence = "$matches[1]$matches[2]";
		} elseif ( isset( $matches[1] ) && \strlen( $matches[1] ) ) {
			// Ignore useless [3], there's no [2], [1] is open-ended; so, add hellip.
			$sentence = "$matches[1]..."; // This should be texturized later to &hellip;.
		} else {
			// If there's no matches[1], only some form of non-closing-leading punctuation was left in $sentence. Empty it.
			$sentence = '';
		}

		if ( \strlen( $sentence ) < $min_char_length )
			return '';

		return trim( htmlentities( $sentence, \ENT_QUOTES, 'UTF-8' ) );
	}
}
