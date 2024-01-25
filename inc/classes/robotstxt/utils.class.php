<?php
/**
 * @package The_SEO_Framework\Classes\RobotsTXT\Utils
 * @subpackage The_SEO_Framework\RobotsTXT
 */

namespace The_SEO_Framework\RobotsTXT;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\umemo;

use \The_SEO_Framework\{
	Data,
	Helper\Query,
	Meta,
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
 * Holds various utility methods for the robots txt.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->robotstxt()->utils() instead.
 */
class Utils {

	/**
	 * Detects presence of robots.txt in root folder.
	 * Memoizes the return value.
	 *
	 * @since 5.0.0
	 *
	 * @return bool Whether the robots.txt file exists.
	 */
	public static function has_root_robots_txt() {
		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = umemo( __METHOD__ ) ) return $memo;

		// Ensure get_home_path() is declared.
		if ( ! \function_exists( 'get_home_path' ) )
			require_once \ABSPATH . 'wp-admin/includes/file.php';

		$path = \get_home_path() . 'robots.txt';

		// phpcs:ignore, TSF.Performance.Functions.PHP -- we use path, not URL.
		return umemo( __METHOD__, file_exists( $path ) );
	}

	/**
	 * Returns the robots.txt location URL.
	 * Only allows root domains.
	 *
	 * @since 2.9.2
	 * @since 4.0.2 Now uses the preferred URL scheme.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @return string URL location of robots.txt. Unescaped.
	 */
	public static function get_robots_txt_url() {

		if ( $GLOBALS['wp_rewrite']->using_permalinks() && ! Data\Blog::is_subdirectory_installation() ) {
			$home = \trailingslashit( Meta\URI\Utils::set_preferred_url_scheme( Meta\URI\Utils::get_site_host() ) );
			$path = "{$home}robots.txt";
		} elseif ( static::has_root_robots_txt() ) {
			$home = \trailingslashit( Meta\URI\Utils::set_preferred_url_scheme( \get_option( 'home' ) ) );
			$path = "{$home}robots.txt";
		} else {
			$path = '';
		}

		return $path;
	}
}
