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
	 * Minifies JavaScript that doesn't contain comments.
	 * JS with comments will be corrupted using this method.
	 *
	 * @since 5.0.5
	 *
	 * @param string $script The script to minify.
	 * @return string The minified JS.
	 */
	public static function javascript( $script ) {

		static $pairs;

		if ( empty( $pairs ) ) {
			// Get omni-spaced first!
			$s_and_r = [
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
				'search'  => array_keys( $s_and_r ),
				'replace' => array_values( $s_and_r ),
			];
		}

		return trim( str_replace( $pairs['search'], $pairs['replace'], $script ) );
	}

	/**
	 * Minifies CSS that doesn't contain comment-style text in content attributes.
	 * Is compatible with XSLT syntax.
	 *
	 * @since 5.0.5
	 *
	 * @param string $script The script to minify.
	 * @return string The minified CSS.
	 */
	public static function css( $script ) {

		static $pairs;

		if ( empty( $pairs ) ) {
			$s_and_r = [
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
				'search'  => array_keys( $s_and_r ),
				'replace' => array_values( $s_and_r ),
			];
		}

		return trim( str_replace(
			$pairs['search'],
			$pairs['replace'],
			preg_replace(
				'/(\/\*[\w\W]*?\*\/)/',
				'',
				$script,
			),
		) );
	}
}
