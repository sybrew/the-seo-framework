<?php
/**
 * @package The_SEO_Framework\Classes\Helper\Format\Color
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
 * Holds methods for HTML Color interpretation and conversion.
 *
 * @since 5.0.0
 *
 * @access protected
 *         Use tsf()->format()->color() instead.
 */
class Color {

	/**
	 * Calculates the relative font color according to the background, grayscale.
	 *
	 * @since 2.8.0
	 * @since 2.9.0 Now adds a little more relative softness based on rel_lum.
	 * @since 2.9.2 (Typo): Renamed from 'get_relatitve_fontcolor' to 'get_relative_fontcolor'.
	 * @since 3.0.4 Now uses WCAG's relative luminance formula.
	 * @since 4.2.0 Optimized code, but it now has some rounding changes at the end. This could
	 *              offset the returned values by 1/255th.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 * @link https://www.w3.org/TR/2008/REC-WCAG20-20081211/#visual-audio-contrast-contrast
	 * @link https://www.w3.org/WAI/GL/wiki/Relative_luminance
	 *
	 * @param string $hex The 3 to 6+ character RGB hex. The '#' prefix may be added.
	 *                    RGBA/RRGGBBAA is supported, but the Alpha channels won't be returned.
	 * @return string The hexadecimal RGB relative font color, without '#' prefix.
	 */
	public static function get_relative_fontcolor( $hex = '' ) {

		// TODO: To support RGBA, we must fill to 4 or 8 via sprintf `%0{1,2}x`
		// But doing this will add processing requirements for something we do not need... yet.
		$hex = ltrim( $hex, '#' );

		// Convert hex to usable numerics.
		[ $r, $g, $b ] = array_map(
			'hexdec',
			str_split(
				// rgb[..] == rrggbb[..].
				\strlen( $hex ) >= 6 ? $hex : "$hex[0]$hex[0]$hex[1]$hex[1]$hex[2]$hex[2]",
				2,
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
}
