<?php
/**
 * @package The_SEO_Framework\Utils
 * @subpackage The_SEO_Framework\API
 */

namespace The_SEO_Framework\Utils;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Normalizes generation args to prevent PHP warnings.
 * This is the standard way TSF determines the type of query.
 *
 * @since 4.3.0
 * @see https://github.com/sybrew/the-seo-framework/issues/640#issuecomment-1703260744.
 *      We made an exception about passing by reference for this function.
 *
 * @param array|null $args The query arguments. Accepts 'id', 'taxonomy', and 'pta'.
 *                         Leave null to have queries be autodetermined.
 *                         Passed by reference.
 */
function normalize_generation_args( &$args ) {

	if ( \is_array( $args ) ) {
		$args += [
			'id'       => 0,
			'taxonomy' => '',
			'pta'      => '',
		];
	} else {
		$args = null;
	}
}

/**
 * Flattens multidimensional lists into a single dimensional list.
 * Deeply nested lists are merged as well. Won't dig associative arrays.
 *
 * E.g., this [ [ 'one' => 1 ], [ [ 'two' => 2 ], [ 'three' => [ 3, 4 ] ] ] ]
 * becomes    [ [ 'one' => 1 ], [ 'two', => 2 ], [ 'three' => [ 3, 4 ] ] ];
 *
 * @link <https://3v4l.org/XBSFa>, test it here.
 *
 * @since 4.2.7
 * @since 4.3.0 Moved to \Utils\ArrayProcessor and is now public.
 *
 * @param array $array The array to flatten. If input is not an array, it'll be casted.
 * @return array The flattened array.
 */
function array_flatten_list( $array ) {

	// PHP 8.1+, use `!array_is_list()`?
	// This is 350x faster than a polyfill for `!array_is_list()`.
	if ( [] === $array || array_values( $array ) !== $array ) return $array;

	$ret = [];

	foreach ( $array as $value ) {
		// We can later use `array_is_list()`.
		if ( \is_array( $value ) && [] !== $value && array_values( $value ) === $value ) {
			$ret = array_merge( $ret, array_flatten_list( $value ) );
		} else {
			array_push( $ret, $value );
		}
	}

	return $ret;
}

/**
 * Merges arrays distinctly, much like `array_merge()`, but then for multidimensionals.
 * Unlike PHP's `array_merge_recursive()`, this method doesn't convert non-unique keys as sequential.
 *
 * @link <https://3v4l.org/9pnW1#v8.1.8> Test it here.
 *
 * @since 4.1.4
 * @since 4.2.7 1. Now supports a single array entry without causing issues.
 *              2. Reduced number of opcodes by roughly 27% by reworking it.
 *              3. Now no longer throws warnings with qubed+ arrays.
 *              4. Now no longer prevents scalar values overwriting arrays.
 * @since 4.3.0 Moved to The_SEO_Framework\Utils as a function.
 *
 * @param array ...$arrays The arrays to merge. The rightmost array's values are dominant.
 * @return array The merged arrays.
 */
function array_merge_recursive_distinct( ...$arrays ) {

	$i = \count( $arrays );

	while ( --$i ) {
		$p = $i - 1;

		foreach ( $arrays[ $i ] as $key => $value )
			$arrays[ $p ][ $key ] = isset( $arrays[ $p ][ $key ] ) && \is_array( $value )
				? array_merge_recursive_distinct( $arrays[ $p ][ $key ], $value )
				: $value;
	}

	return $arrays[0];
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
 * @since 4.3.0 Moved to The_SEO_Framework\Utils as a function and renamed to clamp_sentence()
 * @see https://secure.php.net/manual/en/regexp.reference.unicode.php
 *
 * We use `[^\P{Po}\'\"]` because WordPress texturizes ' and " to fall under `\P{Po}`.
 * This is perfect. Please have the courtesy to credit us when taking it. :)
 *
 * @param string $sentence        The untrimmed sentence. Expected not to contain any HTML operators.
 * @param int    $min_char_length The minimum character length. Set to 0 to ignore the requirement.
 *                                This is read as a SUGGESTION. Multibyte characters will create inaccuracies.
 * @param int    $max_char_length At what point to shave off the sentence.
 * @return string The trimmed sentence with encoded entities. Needs escaping prior printing.
 */
function clamp_sentence( $sentence, $min_char_length = 1, $max_char_length = 4096 ) {

	// At least 1.
	$min_char_length = max( 1, $min_char_length );

	// We should _actually_ use mb_strlen, but that's wasteful on resources for something benign.
	// We'll rectify that later, somewhat, where characters are transformed.
	// We could also use preg_match_all( '/./u' ); or count( preg_split( '/./u', $sentence, $min_char_length ) );
	// But, again, that'll eat CPU cycles.
	if ( \strlen( $sentence ) < $min_char_length )
		return '';

	// Decode to get a more accurate character length in Unicode.
	$sentence = html_entity_decode( $sentence, \ENT_QUOTES, 'UTF-8' );

	// Find all words until $max_char_length, and trim when the last word boundary or punctuation is found.
	preg_match(
		sprintf(
			'/.{0,%d}([^\P{Po}\'\":]|[\p{Pc}\p{Pd}\p{Pf}\p{Z}]|\Z){1}/su',
			$max_char_length
		),
		trim( $sentence ),
		$matches
	);

	$sentence = trim( $matches[0] ?? '' );

	if ( \strlen( $sentence ) < $min_char_length )
		return '';

	// Texturize to recognize the sentence structure. Decode thereafter since we get HTML returned.
	$sentence = html_entity_decode(
		\wptexturize( htmlentities(
			$sentence,
			\ENT_QUOTES,
			'UTF-8'
		) ),
		\ENT_QUOTES,
		'UTF-8'
	);

	/**
	 * Play with it here:
	 * https://regex101.com/r/u0DIgx/5/ (old)
	 * https://regex101.com/r/G92lUt/5 (old)
	 * https://regex101.com/r/dAqhWC/1 (current)
	 *
	 * TODO Group 4's match is repeated. However, referring to it as (4) will cause it to congeal into 3.
	 * Note: Group 4 misses `?\p{Z}*` between `.+` and `[\p{Pc}`, but I couldn't find a use-case for it.
	 *
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
		$matches
	);

	// Unmatched isn't set. Since we count from last to first match, we don't need to test strlen().
	if ( isset( $matches[5] ) ) {
		$sentence = "$matches[1]$matches[3]$matches[4]$matches[5]";
		// Skip 4. It's useless content without 5.
	} elseif ( isset( $matches[3] ) ) {
		$sentence = "$matches[1]$matches[3]";
	} elseif ( isset( $matches[2] ) ) {
		$sentence = "$matches[1]$matches[2]";
	} elseif ( isset( $matches[1] ) ) {
		$sentence = $matches[1];
	}
	// Else: We'll get the original sentence. This shouldn't happen, but at least it's already trimmed to $max_char_length.

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
		$matches
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
