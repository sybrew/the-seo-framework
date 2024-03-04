<?php
/**
 * @package The_SEO_Framework\Views\Sitemap\XSL\Vars
 * @subpackage The_SEO_Framework\Sitemap\XSL
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) or die;

use \The_SEO_Framework\{
	Data\Filter\Sanitize,
	Helper\Format,
};

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * The SEO Framework plugin
 * Copyright (C) 2021 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// Styles generic.
printf(
	'<xsl:variable name="tableMinWidth" select="\'%s\'"/>',
	Data\Plugin::get_option( 'sitemaps_modified' ) ? '727' : '557', // magic numbers: sexy primes
);

$colors = Sitemap\Utils::get_sitemap_colors();

// phpcs:disable, WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitize::rgb_hex() also escapes XML.
printf(
	'<xsl:variable name="colorMain" select="\'%s\'"/>',
	'#' . Sanitize::rgb_hex(
		/**
		 * @since 2.8.0
		 * @since 3.1.0 It now filters the mail color, instead of accent.
		 * @param string $colorMain A hexadecimal color.
		 */
		\apply_filters( 'the_seo_framework_sitemap_color_main', $colors['main'] )
	)
);
printf(
	'<xsl:variable name="colorAccent" select="\'%s\'"/>',
	'#' . Sanitize::rgb_hex(
		/**
		 * @since 2.8.0
		 * @since 3.1.0 It now filters the accent color, instead of main.
		 * @param string $colorAccent A hexadecimal color.
		 */
		\apply_filters( 'the_seo_framework_sitemap_color_accent', $colors['accent'] )
	)
);
printf(
	'<xsl:variable name="relativeFontColor" select="\'%s\'"/>',
	'#' . Sanitize::rgb_hex(
		/**
		 * @since 2.8.0
		 * @param string $relativeFontColor A hexadecimal color.
		 */
		\apply_filters(
			'the_seo_framework_sitemap_relative_font_color',
			Format\Color::get_relative_fontcolor( $colors['main'] )
		)
	)
);
// phpcs:enable, WordPress.Security.EscapeOutput.OutputNotEscaped
