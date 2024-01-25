<?php
/**
 * @package The_SEO_Framework\Classes\Front\Redirect
 * @subpackage The_SEO_Framework\Redirect
 */

namespace The_SEO_Framework\Front;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\{
	Helper\Query,
	Meta,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2020 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Prepares redirects.
 *
 * @since 5.0.0
 * @access private
 */
final class Title {

	/**
	 * Engages title writing in head.
	 *
	 * @hook template_redirect 20
	 * @since 5.0.0
	 */
	public static function overwrite_title_filters() {

		if (
			   ! Query\Utils::query_supports_seo()
			/**
			 * @since 2.9.3
			 * @param bool $overwrite_titles Whether to enable title overwriting.
			 */
			|| ! \apply_filters( 'the_seo_framework_overwrite_titles', true )
		) return;

		// Removes all pre_get_document_title filters.
		\remove_all_filters( 'pre_get_document_title', false );

		\add_filter( 'pre_get_document_title', [ static::class, 'set_document_title' ], 10 );

		/**
		 * @since 2.4.1
		 * @since 5.0.0 Deprecated.
		 * @deprecated
		 * @param bool $overwrite_titles Whether to enable legacy title overwriting.
		 * TODO remove this code? -- it's been 8 years...
		 * <https://make.wordpress.org/core/2015/10/20/document-title-in-4-4/>
		 */
		if ( \apply_filters_deprecated(
			'the_seo_framework_manipulate_title',
			[ true ],
			'5.0.0 of The SEO Framework',
			'the_seo_framework_overwrite_titles',
		) ) {
			\remove_all_filters( 'wp_title', false );

			\add_filter( 'wp_title', [ static::class, 'set_document_title' ], 9 );
		}
	}

	/**
	 * Returns the document title.
	 *
	 * This method serves as a callback for filter `pre_get_document_title`.
	 * Use tsf()->get_title() instead.
	 *
	 * @hook pre_get_document_title 10
	 * @hook wp_title 9
	 * @since 3.1.0
	 * @since 5.0.0 1. Now escapes the filter output.
	 *              2. Moved from `\The_SEO_Framework\Load`.
	 *              3. Renamed from `get_document_title`.
	 *
	 * @return string The document title
	 */
	public static function set_document_title() {
		/**
		 * @since 3.1.0
		 * @param string $title The generated title.
		 * @param int    $id    The page or term ID.
		 */
		return \esc_html( \apply_filters(
			'the_seo_framework_pre_get_document_title',
			Meta\Title::get_title(),
			Query::get_the_real_id(),
		) );
	}
}
