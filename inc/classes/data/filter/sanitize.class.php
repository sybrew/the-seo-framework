<?php
/**
 * @package The_SEO_Framework\Classes\Data\Filter\Sanitize
 * @subpackage The_SEO_Framework\Data\Filter
 */

namespace The_SEO_Framework\Data\Filter;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\{
	Helper,
	Helper\Format\Strings,
	Meta,
};

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
 * Holds a collection of data sanitization methods.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->filter()->sanitize() instead.
 */
class Sanitize {

	/**
	 * Sanitizes input to a boolean integer, i.e. 0, 1,
	 *
	 * Uses double casting. First, we cast to boolean, then to int.
	 *
	 * @since 2.2.2
	 * @since 2.8.0 Method is now public.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `s_one_zero`.
	 *
	 * @param mixed $value The value to cast to a boolean integer.
	 * @return int A boolean as a string (1 or 0)
	 */
	public static function boolean_integer( $value ) {
		return (int) (bool) $value;
	}

	/**
	 * Sanitizes input to a numeric string, like '0', '1', '2'.
	 *
	 * Uses double casting. First, we cast to integer, then to string.
	 * Rounds floats down. Converts non-numeric inputs to '0'.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `s_numeric_string`.
	 *
	 * @param mixed $value The value to cast to a numeric string.
	 * @return string An integer as string.
	 */
	public static function numeric_string( $value ) {
		return (string) (int) $value;
	}

	/**
	 * Sanitizes color hexadecimals to either 3 or 6 length: rgb or rrggbb.
	 * Removes leading hashtags.
	 * Makes the input lowercase.
	 *
	 * @since 2.8.0
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `s_color_hex`.
	 *              3. Now accepts longer strings, and shortens them to the correct length.
	 *
	 * @param string $color String with potentially unwanted hex values.
	 * @return string The sanitized color hex.
	 */
	public static function rgb_hex( $color ) {

		preg_match(
			'/^(?:[a-f\d]{3}){1,2}/i',
			trim( $color, '# ' ),
			$matches,
		);

		return strtolower( $matches[0] ?? '' );
	}

	/**
	 * Sanitizes color hexadecimals to either 3, 4, 6, or 8 length: rgb, rgba, rrggbb, rrggbbaa.
	 * Removes leading hashtags.
	 * Makes the input lowercase.
	 *
	 * @since 5.0.0
	 *
	 * @param string $color String with potentially unwanted hex values.
	 * @return string The sanitized color hex.
	 */
	public static function rgba_hex( $color ) {

		// If rgb, we must only get rgb[a], not rgb[aa].
		// If rrggbb, we must only get rrggbb[aa], not rrggbb[a].
		preg_match(
			'/^(?:[a-f\d]{8}|[a-f\d]{6}|[a-f\d]{3,4})/i',
			trim( $color, '# ' ),
			$matches,
		);

		return strtolower( $matches[0] ?? '' );
	}

	/**
	 * Sanitizes metadata content.
	 * Returns single-line, trimmed text without duplicated spaces, nbsp, or tabs.
	 * Also converts back-solidi to their respective HTML entities for non-destructive handling.
	 * Also adds a capital P, dangit.
	 * Finally, it texturizes the content.
	 *
	 * @since 5.0.0
	 *
	 * @param string $text The text.
	 * @return string One line sanitized text.
	 */
	public static function metadata_content( $text ) {

		if ( ! \is_scalar( $text ) || ! \strlen( $text ) ) return '';

		return \wptexturize(
			\capital_P_dangit(
				static::backward_solidus_to_entity(
					static::lone_hyphen_to_entity(
						static::remove_repeated_spacing(
							trim(
								static::tab_to_space(
									static::newline_to_space(
										static::nbsp_to_space(
											(string) $text,
										),
									),
								),
							),
						),
					),
				),
			),
		);
	}

	/**
	 * Normalizes metadata content for string comparison.
	 * The data returned is considered insecure.
	 *
	 * @since 5.0.0
	 *
	 * @param string $text The input text with possible repeated spacing.
	 * @return string The input string without repeated spaces.
	 */
	public static function normalize_metadata_content_for_strcmp( $text ) {
		// Why not blog_charset? Because blog_charset is there only to onboard non-UTF-8 to UTF-8.
		return html_entity_decode(
			static::metadata_content( $text ),
			\ENT_QUOTES | \ENT_SUBSTITUTE | \ENT_HTML5,
			'UTF-8',
		);
	}

	/**
	 * Sanitizes text by removing repeated spaces.
	 *
	 * @since 2.8.2
	 * @since 2.9.4 Now no longer fails when first two characters are spaces.
	 * @since 3.1.0 1. Now also catches non-breaking spaces.
	 *              2. Now uses a regex pattern.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `s_dupe_space`.
	 *              3. Now replaces the spaces with the original spacing type, instead of only \u20.
	 *
	 * @param string $text The input text with possible repeated spacing.
	 * @return string The input string without repeated spaces.
	 */
	public static function remove_repeated_spacing( $text ) {
		return preg_replace_callback(
			'/(\p{Zs}){2,}/u',
			// Unicode support sans mb_*: Calculate the bytes of the match and then remove that length.
			fn( $matches ) => substr( $matches[1], 0, \strlen( $matches[1] ) ),
			$text,
		);
	}

	/**
	 * Replaces non-transformative hyphens with entity hyphens.
	 * Duplicated simple hyphens are preserved.
	 *
	 * Regex challenge, make the columns without an x light up:
	 * xxx - xx - xxx- - - xxxxxx xxxxxx- xxxxx - -
	 * --- - -- - ---- - - ------ ------- ----- - -
	 *
	 * The answer? `/((-{2,3})(*SKIP)-|-)(?(2)(*FAIL))/`
	 * Sybre-kamisama desu.
	 *
	 * @since 4.0.5
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `s_hyphen`.
	 *
	 * @param string $text String with potential hyphens.
	 * @return string A string with safe HTML encoded hyphens.
	 */
	public static function lone_hyphen_to_entity( $text ) {
		// str_replace is faster than putting these alternative sequences in the `-|-` regex below.
		// That'd be this: "/((?'h'-|&\#45;|\xe2\x80\x90){2,3}(*SKIP)(?&h)|(?&h))(?(h)(*FAIL))/u"
		return str_replace(
			[ '&#45;', "\xe2\x80\x90" ], // Should we consider &#000...00045;?
			'&#x2d;',
			preg_replace( '/((-{2,3})(*SKIP)-|-)(?(2)(*FAIL))/', '&#x2d;', $text ),
		);
	}

	/**
	 * Replaces non-break spaces with regular spaces.
	 *
	 * This addresses a quirk in TinyMCE, where paragraph newlines are populated with nbsp.
	 * TODO: Perhaps we should address that quirk directly, instead of removing indiscriminately.
	 *       e.g., like `strip_newline_urls` and `strip_paragraph_urls`.
	 *
	 * @since 2.8.2
	 * @since 3.1.0 Now catches all non-breaking characters.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `s_nbsp`.
	 *
	 * @param string $text String with potentially unwanted nbsp values.
	 * @return string A spacey string.
	 */
	public static function nbsp_to_space( $text ) {
		return str_replace( [ '&nbsp;', '&#160;', '&#xA0;', "\xc2\xa0" ], ' ', $text );
	}

	/**
	 * Replaces backslash with entity backslash.
	 *
	 * @since 2.8.2
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `s_bsol`.
	 *              3. No longer removes backslashes.
	 *
	 * @param string $text String with potentially unwanted \ values.
	 * @return string A string with safe HTML encoded backslashes.
	 */
	public static function backward_solidus_to_entity( $text ) {
		return str_replace( '\\', '&#92;', $text );
	}

	/**
	 * Converts multilines to single lines.
	 *
	 * @since 2.8.2
	 * @since 3.1.0 Simplified method.
	 * @since 4.1.0 1. Made this method about 25~92% faster (more replacements = more faster). 73% slower on empty strings (negligible).
	 *              2. Now also strips form-feed and vertical whitespace characters--might they appear in the wild.
	 *              3. Now also strips horizontal tabs (reverted in 4.1.1).
	 * @since 4.1.1 1. Now uses real bytes, instead of sequences (causing uneven transformations, plausibly emptying content).
	 *              2. No longer transforms horizontal tabs. Use `s_tabs()` instead.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `s_singleline`.
	 * @link https://www.php.net/manual/en/regexp.reference.escape.php
	 *
	 * @param string $text The input value with possible multiline.
	 * @return string The input string without multiple lines.
	 */
	public static function newline_to_space( $text ) {
		// Use x20 because it's a human-visible real space.
		return trim(
			strtr( $text, "\x0A\x0B\x0C\x0D", "\x20\x20\x20\x20" ),
		);
	}

	/**
	 * Removes tabs and replaces it with spaces.
	 *
	 * @since 2.8.2
	 * @since 4.1.1 Now uses real bytes, instead of sequences (causing uneven transformations, plausibly emptying content).
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `s_tabs`.
	 * @link https://www.php.net/manual/en/regexp.reference.escape.php
	 *
	 * @param string $text The input value with possible tabs.
	 * @return string The input string without tabs.
	 */
	public static function tab_to_space( $text ) {
		// Use x20 because it's a human-visible real space.
		return strtr( $text, "\x09", "\x20" );
	}

	/**
	 * Returns a -1, 0, or 1, based on nearest value. This is close to a sign function, but not quite.
	 *
	 * Obviously, we're not working with actual quantum bits, but its implementation is based on it:
	 * If more negative, unset.
	 * If nought, change nothing.
	 * If more positive, set.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `s_qubit`.
	 *              3. Now considers .3334 the turnover point, instead of 0.33000...0001.
	 * @since 5.1.0 Now considers .3333 (off by .00003) the turnover point for the negative side,
	 *              instead of -0.3333999...999 (off by .00006).
	 *
	 * @param float|int $value The qubit to test; ideally be -1, 0, or 1.
	 * @return int -1, 0, or 1.
	 */
	public static function qubit( $value ) {
		return $value >= .3334 <=> -.3333 >= $value;
	}

	/**
	 * Sanitizes the Redirect URL.
	 *
	 * @since 2.2.4
	 * @since 2.8.0 Method is now public.
	 * @since 3.0.6 Noqueries is now disabled by default.
	 * @since 4.0.0 1. Removed rudimentary relative URL testing.
	 *              2. Removed input transformation filters, and with that, removed redundant multisite spam protection.
	 *              3. Now allows all protocols. Enjoy!
	 *              4. Now no longer lets through double-absolute URLs (e.g. `https://example.com/https://example.com/path/to/file/`)
	 *                 when filter `the_seo_framework_allow_external_redirect` is set to false.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `s_redirect_url`.
	 *              3. No longer provides stripping URL queries via a filter.
	 *
	 * @param string $url String with potentially unwanted redirect URL.
	 * @return string The Sanitized Redirect URL
	 */
	public static function redirect_url( $url ) {

		$url = trim( $url );

		if ( empty( $url ) ) return '';

		// This is also checked when performing a redirect.
		if ( ! Helper\Redirect::allow_external_redirect() ) {
			$url = Meta\URI\Utils::set_url_scheme( Meta\URI\Utils::convert_path_to_url(
				Meta\URI\Utils::set_url_scheme( $url, 'relative' ),
			) );
		}

		// All WP defined protocols are allowed.
		return \sanitize_url( $url );
	}

	/**
	 * Cleans known parameters from image details.
	 *
	 * @since 4.0.0
	 * @since 4.0.2 Now finds smaller images when they're over 4K.
	 * @since 4.0.5 Now faults images with filename extensions APNG, BMP, ICO, TIFF, or SVG.
	 * @since 4.1.4 Fixed theoretical issue where a different image could be set when width
	 *              and height are supplied and either over 4K, but no ID is given.
	 * @since 4.2.4 Now accepts, processes, and returns filesizes under index `filesize`.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `s_image_details`.
	 *              3. Now sanitizes the caption.
	 *
	 * @param array|array[] $details {
	 *     An array of image details, or an array of an array thereof.
	 *
	 *     @type string $url      Required. The image URL.
	 *     @type int    $id       Optional. The image ID. Used to fetch the largest image possible.
	 *     @type int    $width    Optional. The image width in pixels.
	 *     @type int    $height   Optional. The image height in pixels.
	 *     @type string $alt      Optional. The image alt tag.
	 *     @type string $caption  Optional. The image caption.
	 *     @type int    $filesize Optional. The image filesize in bytes.
	 * }
	 * @return array|array[] $details {
	 *     An array of image details, or an array of an array thereof if the input was multidimensional.
	 *
	 *     @type string $url      The image URL.
	 *     @type int    $id       The image ID.
	 *     @type int    $width    The image width in pixels.
	 *     @type int    $height   The image height in pixels.
	 *     @type string $alt      The image alt tag.
	 *     @type string $caption  The image caption.
	 *     @type int    $filesize The image filesize in bytes.
	 * }
	 */
	public static function image_details( $details ) {

		// This is over 350x faster than a polyfill for `array_is_list()`.
		if ( isset( $details[0] ) && array_values( $details ) === $details ) {
			foreach ( $details as $deets )
				$sanitized_details[] = static::image_details( $deets ); // phpcs:ignore, VariableAnalysis.CodeAnalysis

			return $sanitized_details ?? [];
		}

		$defaults = [
			'url'      => '',
			'id'       => 0,
			'width'    => 0,
			'height'   => 0,
			'alt'      => '',
			'caption'  => '',
			'filesize' => 0,
		];

		if ( empty( $details ) ) return $defaults;

		[ $url, $id, $width, $height, $alt, $caption, $filesize ] = array_values( array_merge( $defaults, $details ) );

		if ( empty( $url ) ) return $defaults;

		$url = \sanitize_url( Meta\URI\Utils::make_absolute_current_scheme_url( $url ), [ 'https', 'http' ] );

		if ( empty( $url ) ) return $defaults;

		/**
		 * Skip APNG, BMP, ICO, TIFF, and SVG.
		 *
		 * @link <https://developer.twitter.com/en/docs/tweets/optimize-with-cards/overview/markup>
		 * @link <https://developer.mozilla.org/en-US/docs/Web/Media/Formats/Image_types>
		 * jp(e)g, png, webp, and gif are supported. Assume all non-matches to fall in those categories,
		 * since we don't perform a live MIME-test.
		 *
		 * Tested with Facebook; they ignore them too. There's no documentation available.
		 * TODO Should we even test for this here, or at the image generators' type?
		 * It seems, however, that _all_ services we want to communicate with ignore these types, anyway.
		 */
		if ( \in_array(
			strtolower( strtok( pathinfo( $url, \PATHINFO_EXTENSION ), '?' ) ),
			[ 'apng', 'bmp', 'ico', 'cur', 'svg', 'tif', 'tiff' ],
			true,
		) ) return $defaults;

		$width  = \absint( $width );
		$height = \absint( $height );

		if ( empty( $width ) || empty( $height ) )
			$width = $height = 0;

		// TODO add filter for 5 * \MB_IN_BYTES; Facebook allows 8MB; Twitter 5MB (Q4 2022).
		if ( $id && ( $width > 4096 || $height > 4096 || $filesize > 5 * \MB_IN_BYTES ) ) {
			$new_image = Meta\Image\Utils::get_largest_image_src( $id, 4096, 5 * \MB_IN_BYTES );
			$url       = $new_image ? \sanitize_url(
				Meta\URI\Utils::make_absolute_current_scheme_url( $new_image[0] ),
				[ 'https', 'http' ],
			) : '';

			if ( empty( $url ) ) return $defaults;

			// No sanitization needed. PHP's getimagesize() returns the correct values.
			$width  = $new_image[1];
			$height = $new_image[2];
		}

		if ( $alt ) {
			$alt = \wp_strip_all_tags( $alt );
			// 420: https://developer.twitter.com/en/docs/tweets/optimize-with-cards/overview/summary.html
			// Don't "ai"-trim if under, it's unlikely to always be a sentence. Trim to 417 to account for plausibly appended "...".
			$alt = \strlen( $alt ) > 420 ? Strings::clamp_sentence( $alt, 0, 417 ) : $alt;
		}
		if ( $caption ) {
			$caption = \wp_strip_all_tags( $caption, true );
		}

		return compact( 'url', 'id', 'width', 'height', 'alt', 'caption', 'filesize' );
	}

	/**
	 * Sanitizes the Facebook profile link. Makes an actual Facebook link if it isn't already.
	 *
	 * @since 2.2.2
	 * @since 2.8.0 Method is now public.
	 * @since 3.0.6 Now allows a sole query argument when profile.php is used.
	 * @since 4.0.0 1. No longer returns a plain Facebook URL when the entry path is sanitized to become empty.
	 *              2. Now returns empty when using only spaces and tabs.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `s_facebook_profile`.
	 *
	 * @param string $link The unsanitized Facebook profile URL.
	 * @return string The sanitized Facebook profile URL.
	 */
	public static function facebook_profile_link( $link ) {

		$path = trim( Meta\URI\Utils::get_relative_part_from_url( $link ), ' /' );

		// /0 is a valid profile link.
		if ( ! \strlen( $path ) ) return '';

		$link = "https://www.facebook.com/{$path}";

		if ( str_contains( $link, 'profile.php' ) ) {
			// Extract query parameters.
			parse_str( parse_url( $link, \PHP_URL_QUERY ), $r );

			if ( empty( $r['id'] ) ) return '';

			$link = 'https://www.facebook.com/profile.php?id=' . \absint( $r['id'] );
		}

		return \sanitize_url( $link );
	}

	/**
	 * Sanitizes a Twitter/X profile handle.
	 *
	 * @since 2.2.2
	 * @since 2.8.0 Method is now public.
	 * @since 3.0.0 1. Now removes '@' from the URL path.
	 *              2. Now removes spaces and tabs.
	 * @since 4.0.0 1. Now returns empty on lone `@` entries.
	 *              2. Now returns empty when using only spaces and tabs.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `s_twitter_name`.
	 *
	 * @param string $handle An unsanitized profile handle.
	 * @return string A sanitized profile handle with '@' prefixed to it.
	 */
	public static function twitter_profile_handle( $handle ) {

		$handle = preg_replace(
			'/[^a-z\d_]/i',
			'',
			trim( Meta\URI\Utils::get_relative_part_from_url( $handle ), ' /@' )
		);
		$length = \strlen( $handle );

		// 4~15 since 2013 -- support shorter (/0 exists) and the theoretical 18 limit:
		if ( $length < 1 || $length > 18 )
			return '';

		return "@$handle";
	}
}
