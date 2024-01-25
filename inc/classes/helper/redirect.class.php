<?php
/**
 * @package The_SEO_Framework\Classes\Helper\Redirect
 * @subpackage The_SEO_Framework\Query
 */

namespace The_SEO_Framework\Helper;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\memo;

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
 * Holds a collection of helper methods for HTTP Redirects.
 *
 * @since 5.0.0
 * @access private
 */
class Redirect {

	/**
	 * Whether to allow external redirect through the 301 redirect option.
	 * Memoizes the return value.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @return bool Whether external redirect is allowed.
	 */
	public static function allow_external_redirect() {
		/**
		 * @since 2.1.0
		 * @param bool $allowed Whether external redirect is allowed.
		 */
		return memo() ?? memo( (bool) \apply_filters( 'the_seo_framework_allow_external_redirect', true ) );
	}
}
