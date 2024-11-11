<?php
/**
 * @package The_SEO_Framework\Classes\Helper\Format\Minify
 * @subpackage The_SEO_Framework\Formatting
 */

namespace The_SEO_Framework\Helper\Format;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds methods for rudimentary minification.
 * The minification isn't context sensitive and aimed at performance.
 * The minification also assumes our coding standards. Neat in, neat out.
 *
 * @since 5.0.5
 *
 * @access protected
 *         Use tsf()->format()-minify() instead.
 */
class Minify {

	/**
	 * Minifies JavaScript that doesn't contain comments or text.
	 * JS with comments or text will be corrupted using this method.
	 *
	 * @since 5.0.5
	 *
	 * @param string $script The script to minify.
	 * @return string The minified JS.
	 */
	public static function javascript( $script ) {

		static $pairs;

		if ( empty( $pairs ) ) {
			$sr = [
				"\r"   => '',
				"\n"   => '',
				"\t"   => '',
				'    ' => ' ',
				'   '  => ' ',
				'  '   => ' ',
				' ? '  => '?',
				' ! '  => '!',
				' :'   => ':',
				': '   => ':',
				' = '  => '=',
				' || ' => '||',
				' && ' => '&&',
				' ?? ' => '??',
				' =+ ' => '=+',
				' )'   => ')',
				') '   => ')',
				' ('   => '(',
				'( '   => '(',
				' {'   => '{',
				'{ '   => '{',
				' }'   => '}',
				'} '   => '}',
				', '   => ',',
				'; '   => ';',
			];

			$pairs = [
				'search'  => array_keys( $sr ),
				'replace' => array_values( $sr ),
			];
		}

		return trim( str_replace( $pairs['search'], $pairs['replace'], $script ) );
	}

	/**
	 * Minifies CSS that doesn't contain comment-style text in content attributes.
	 * This method is compatible with XSLT syntax.
	 *
	 * @since 5.0.5
	 *
	 * @param string $sheet The sheet to minify.
	 * @return string The minified CSS.
	 */
	public static function css( $sheet ) {

		static $pairs;

		if ( empty( $pairs ) ) {
			$sr = [
				"\r"   => '',
				"\n"   => '',
				"\t"   => '',
				'    ' => ' ',
				'   '  => ' ',
				'  '   => ' ',
				' :'   => ':',
				': '   => ':',
				' + '  => '+',
				' )'   => ')',
				') '   => ')',
				' ('   => '(',
				'( '   => '(',
				' {'   => '{',
				'{ '   => '{',
				' }'   => '}',
				'} '   => '}',
				', '   => ',',
				'; '   => ';',
			];

			$pairs = [
				'search'  => array_keys( $sr ),
				'replace' => array_values( $sr ),
			];
		}

		return trim( str_replace(
			$pairs['search'],
			$pairs['replace'],
			preg_replace(
				'/(\/\*[\w\W]*?\*\/)/',
				'',
				$sheet,
			),
		) );
	}
}
