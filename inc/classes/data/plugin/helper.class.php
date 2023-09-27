<?php
/**
 * @package The_SEO_Framework\Classes\Data\Plugin\Helper
 * @subpackage The_SEO_Framework\Data\Plugin
 */

namespace The_SEO_Framework\Data\Plugin;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds a collection of data helper methods for TSF.
 *
 * @since 4.3.0
 * @access protected
 * @internal
 */
class Helper {

	/**
	 * Returns the option key for robots settings.
	 *
	 * @since 4.3.0
	 *
	 * @param string $field Accepts 'post_type' and 'taxonomy'
	 * @param string $type  Accepts 'noindex', 'nofollow', 'noarchive'.
	 * @return string
	 */
	public static function get_robots_option_index( $field, $type ) {

		switch ( $field ) {
			case 'post_type':
				return "{$type}_post_types";
			case 'taxonomy':
				return "{$type}_taxonomies";
		}

		return '';
	}
}
