<?php
/**
 * @package The_SEO_Framework\Classes\Meta
 * @subpackage The_SEO_Framework\Meta\Theme_Color
 */

namespace The_SEO_Framework\Meta;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\{
	Data,
	Data\Filter\Sanitize,
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
 * Holds getters for meta tag output.
 *
 * @since 5.0.1
 * @access protected
 *         Use tsf()->theme_color() instead.
 */
class Theme_Color {

	/**
	 * @since 5.0.0
	 *
	 * @return string The theme color including prefixed hashtag.
	 */
	public static function get_theme_color() {

		$color = Sanitize::rgb_hex( Data\Plugin::get_option( 'theme_color' ) );

		// '000' is true. '0b0' is also true.
		return $color ? "#$color" : '';
	}
}
