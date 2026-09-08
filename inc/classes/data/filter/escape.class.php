<?php
/**
 * @package The_SEO_Framework\Classes\Data\Filter\Escape
 * @subpackage The_SEO_Framework\Data\Filter
 */

namespace The_SEO_Framework\Data\Filter;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 - 2026 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds a collection of data escaping methods.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->escape() instead.
 */
class Escape {

	/**
	 * Escapes a string for use as a CSS <string> (`content`, quoted `url()` arguments).
	 *
	 * `safecss_filter_attr()` is an allowlist for HTML `style` attribute declarations.
	 * It does not encode a CSS string token. Passing a title through it would look
	 * for `property:value` pairs and drop the text.
	 *
	 * `<` is emitted as a CSS hex escape so a `<style>` embed cannot close on
	 * `</style>`. `JSON_HEX_TAG` (`\u003C`) is a JSON escape; CSS would not treat
	 * it as `<`.
	 *
	 * CRLF and leftover CR are collapsed to LF before encoding, so a Windows newline
	 * is one CSS line break (`\A`) instead of CR+LF.
	 *
	 * @since 5.1.5
	 * @see https://www.w3.org/TR/css-values-4/#string-value
	 * @see https://www.w3.org/TR/css-content-3/#propdef-content
	 *
	 * @param mixed $value The value to encode. Expected to be text, not HTML.
	 * @return string The CSS-safe quoted string.
	 */
	public static function css_content( $value ) {

		$text = strtr(
			str_replace(
				[ "\r\n", "\r" ],
				"\n",
				html_entity_decode(
					(string) $value,
					  \ENT_HTML5
					| \ENT_QUOTES
					| \ENT_SUBSTITUTE,
					'UTF-8',
				),
			),
			[
				'\\' => '\\\\',
				'"'  => '\\"',
				"\n" => '\\A ',
				"\f" => '\\C ',
				'<'  => '\\3C ',
			],
		);

		return "\"{$text}\"";
	}

	/**
	 * Escapes option key. Mainly removing spaces and coding characters.
	 *
	 * Unlike sanitize_key(), it doesn't alter the case nor applies filters.
	 * It also maintains the '@' character and square brackets.
	 *
	 * @see WordPress Core sanitize_key()
	 * @since 4.0.0
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `s_field_id`.
	 *
	 * @param string $id The unsanitized ID.
	 * @return string The sanitized ID.
	 */
	public static function option_name_attribute( $id ) {
		return preg_replace( '/[^a-zA-Z0-9\[\]_\-@]/', '', $id );
	}

	/**
	 * Escapes value via JSON encoding for script elements.
	 *
	 * @since 5.0.0
	 *
	 * @param mixed $value   The value to encode.
	 * @param int   $options Extra JSON encoding options
	 * @return string|false The HTML-escaped JSON-encoded text.
	 */
	public static function json_encode_script( $value, $options = 0 ) {
		return json_encode(
			$value,
			  \JSON_UNESCAPED_SLASHES
			| \JSON_HEX_TAG
			| \JSON_UNESCAPED_UNICODE
			| \JSON_INVALID_UTF8_IGNORE
			| $options,
		);
	}

	/**
	 * Escapes value via JSON encoding for HTML output.
	 *
	 * Unused internally.
	 *
	 * @since 5.0.0
	 *
	 * @param mixed $value   The value to encode.
	 * @param int   $options Extra JSON encoding options
	 * @return string|false The HTML-escaped JSON-encoded text.
	 */
	public static function json_encode_html( $value, $options = 0 ) {
		return json_encode(
			$value,
			  \JSON_UNESCAPED_SLASHES
			| \JSON_HEX_TAG
			| \JSON_HEX_APOS
			| \JSON_HEX_QUOT
			| \JSON_HEX_AMP
			| \JSON_UNESCAPED_UNICODE
			| \JSON_INVALID_UTF8_IGNORE
			| $options,
		);
	}

	/**
	 * Escapes value via JSON encoding for attributes.
	 *
	 * @since 5.0.0
	 *
	 * @param mixed $value   The value to encode.
	 * @param int   $options Extra JSON encoding options
	 * @return string|false The HTML-attribute-escaped JSON-encoded text.
	 */
	public static function json_encode_attribute( $value, $options = 0 ) {

		$charset = \get_option( 'blog_charset' ) ?: null;

		switch ( $charset ) {
			case 'utf8':
			case 'utf-8':
			case 'UTF8':
				$charset = 'UTF-8';
		}

		return htmlspecialchars(
			json_encode(
				$value,
				  \JSON_UNESCAPED_SLASHES
				| \JSON_HEX_TAG
				| \JSON_HEX_APOS
				| \JSON_HEX_QUOT
				| \JSON_UNESCAPED_UNICODE
				| \JSON_INVALID_UTF8_IGNORE
				| $options,
			),
			\ENT_QUOTES,
			$charset,
		);
	}

	/**
	 * Escapes URIs for XML.
	 *
	 * @since 5.0.0
	 *
	 * @param mixed $uri The URI to escape.
	 * @return string A value that's safe for XML use.
	 */
	public static function xml_uri( $uri ) {

		$uri = preg_replace(
			'/[^a-z0-9-~+_.?#=!&;,\/:%@$\|*\'()\[\]\\x80-\\xff]/i',
			'',
			str_replace( ' ', '%20', ltrim( $uri ) ),
		);

		$q = parse_url( $uri, PHP_URL_QUERY );

		if ( $q ) {
			parse_str( $q, $r );
			// Don't replace. Tokenize. The query part might be part of the URL (in some alien environment).
			$uri = strtok( $uri, '?' ) . '?' . http_build_query( $r, '', '&amp;', PHP_QUERY_RFC3986 );
		}

		return $uri;
	}
}
