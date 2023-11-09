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
 * Copyright (C) 2020 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
	 * @see $this->get_title()
	 *
	 * @param string $title The filterable title.
	 * @return string The document title
	 */
	public static function set_document_title( $title = '' ) {

		if ( ! Query\Utils::query_supports_seo() )
			return $title;

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
