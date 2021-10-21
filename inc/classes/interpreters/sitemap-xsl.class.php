<?php
/**
 * @package The_SEO_Framework\Classes\Interpreters\Sitemap\XSL
 * @subpackage The_SEO_Framework\Sitemap\XSL
 */

namespace The_SEO_Framework\Interpreters;

/**
 * The SEO Framework plugin
 * Copyright (C) 2021 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\umemo;

/**
 * Interprets the Sitemap Stylesheet of the optimized Sitemap.
 *
 * @since 4.2.0
 *
 * @access private
 * @final Can't be extended.
 */
final class Sitemap_XSL {

	/**
	 * Prepares the sitemap stylesheet: loads all actions.
	 *
	 * @since 4.2.0
	 */
	public static function prepare() {

		$class = static::class;

		// Adds site icon tags to the sitemap stylesheet.
		\add_action( 'the_seo_framework_xsl_head', 'wp_site_icon', 99 );

		\add_action( 'the_seo_framework_xsl_head', "$class::_print_xsl_global_variables", 0 );
		\add_action( 'the_seo_framework_xsl_head', "$class::_print_xsl_title" );
		\add_action( 'the_seo_framework_xsl_head', "$class::_print_xsl_styles" );

		\add_action( 'the_seo_framework_xsl_description', "$class::_print_xsl_description" );

		\add_action( 'the_seo_framework_xsl_content', "$class::_print_xsl_content" );

		\add_action( 'the_seo_framework_xsl_footer', "$class::_print_xsl_footer" );
		\add_action( 'site_icon_meta_tags', "$class::_convert_site_icon_meta_tags", PHP_INT_MAX );
	}

	/**
	 * Prints global XSL variables.
	 *
	 * @since 3.1.0
	 * @since 4.2.0 1. $tableMinWidth no longer adds 'px'.
	 *              2. Moved to class.
	 * @access private
	 * @param \The_SEO_Framework\Load $tsf tsf() object.
	 */
	public static function _print_xsl_global_variables( $tsf ) {
		$tsf->get_view( 'sitemap/xsl/vars' );
	}

	/**
	 * Prints XSL title.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 Now uses a consistent titling scheme.
	 * @since 4.2.0 Moved to class
	 * @access private
	 * @param \The_SEO_Framework\Load $tsf tsf() object.
	 */
	public static function _print_xsl_title( $tsf ) {
		$tsf->get_view( 'sitemap/xsl/title' );
	}

	/**
	 * Prints XSL styles.
	 *
	 * @since 3.1.0
	 * @since 4.2.0 1. Centered sitemap.
	 *              2. Moved to class.
	 * @access private
	 * @param \The_SEO_Framework\Load $tsf tsf() object.
	 */
	public static function _print_xsl_styles( $tsf ) {
		$tsf->get_view( 'sitemap/xsl/styles' );
	}

	/**
	 * Prints XSL description.
	 *
	 * @since 3.1.0
	 * @since 4.2.0 Moved to class;
	 *
	 * @access private
	 * @param \The_SEO_Framework\Load $tsf tsf() object.
	 */
	public static function _print_xsl_description( $tsf ) {
		$tsf->get_view( 'sitemap/xsl/description' );
	}
	/**
	 * Prints XSL content.
	 *
	 * @since 3.1.0
	 * @since 4.2.0 Moved to class.
	 *
	 * @param \The_SEO_Framework\Load $tsf tsf() object.
	 */
	public static function _print_xsl_content( $tsf ) {
		$tsf->get_view( 'sitemap/xsl/table' );
	}

	/**
	 * Prints XSL footer.
	 *
	 * @since 3.1.0
	 * @since 4.2.0 Moved to class
	 * @access private
	 *
	 * @param \The_SEO_Framework\Load $tsf tsf() object.
	 */
	public static function _print_xsl_footer( $tsf ) {
		/**
		 * @since 2.8.0
		 * @param bool $indicator
		 */
		\apply_filters( 'the_seo_framework_indicator_sitemap', true )
			and $tsf->get_view( 'sitemap/xsl/footer' );
	}

	/**
	 * Converts meta tags that aren't XHTML to XHTML, loosely.
	 * Doesn't fix attribute minimization. TODO?..
	 *
	 * @since 3.1.4
	 * @since 4.2.0 Moved to class.
	 *
	 * @param array $tags Site Icon meta elements.
	 * @return array The converted meta tags.
	 */
	public static function _convert_site_icon_meta_tags( $tags ) {

		foreach ( $tags as &$tag ) {
			$tag = \wp_kses(
				\force_balance_tags( $tag ),
				[
					'link' => [
						'charset'  => [],
						'rel'      => [],
						'sizes'    => [],
						'href'     => [],
						'hreflang' => [],
						'media'    => [],
						'rev'      => [],
						'target'   => [],
						'type'     => [],
					],
					'meta' => [
						'content'    => [],
						'property'   => [],
						'http-equiv' => [],
						'name'       => [],
						'scheme'     => [],
					],
				],
				[]
			);
		}

		return $tags;
	}
}
