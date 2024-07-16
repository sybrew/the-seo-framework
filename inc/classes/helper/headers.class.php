<?php
/**
 * @package The_SEO_Framework\Classes\Helper\Headers
 * @subpackage The_SEO_Framework\Query
 */

namespace The_SEO_Framework\Helper;

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
 * Holds a collection of helper methods for HTTP Headers.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->headers() instead.
 */
class Headers {

	/**
	 * Destroys output buffer, if any. To be used with AJAX and XML to clear any PHP errors or dumps.
	 *
	 * @since 2.8.0
	 * @since 2.9.0 Now flushes all levels rather than just the latest one.
	 * @since 4.0.0 Is now public.
	 *
	 * @return bool True on clear. False otherwise.
	 */
	public static function clean_response_header() {

		$level = ob_get_level();

		if ( $level ) {
			while ( $level-- ) ob_end_clean();
			return true;
		}

		return false;
	}

	/**
	 * Sets the X-Robots tag headers to 'noindex'.
	 *
	 * @hook do_robots 10
	 * @hook the_seo_framework_sitemap_header 10
	 * @since 5.0.0
	 */
	public static function output_robots_noindex_headers() {
		/**
		 * @since 4.0.5
		 * @param bool $noindex Whether a noindex header must be set.
		 */
		if ( \apply_filters( 'the_seo_framework_set_noindex_header', true ) )
			headers_sent() or header( 'X-Robots-Tag: noindex', true );
	}
}
