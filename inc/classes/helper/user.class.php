<?php
/**
 * @package The_SEO_Framework\Classes\Helper\User
 * @subpackage The_SEO_Framework\Query
 */

namespace The_SEO_Framework\Helper;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use The_SEO_Framework\Data;

/**
 * The SEO Framework plugin
 * Copyright (C) 2026 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds a collection of helper methods for users.
 *
 * @since 5.2.0
 * @access protected
 *         Use tsf()->user() instead.
 */
class User {

	/**
	 * Determines whether the user can carry author SEO.
	 *
	 * @since 5.2.0
	 *
	 * @param int $user_id Optional. The user ID. Leave 0 to use the profile screen's user.
	 * @return bool
	 */
	public static function is_supported( $user_id = 0 ) {

		$user_id = $user_id ?: Query::get_admin_user_id();

		/**
		 * @since 5.2.0
		 * @param bool $supported Whether the user is supported.
		 * @param int  $user_id   The evaluated user ID.
		 */
		return (bool) \apply_filters(
			'the_seo_framework_supported_user',
			(
				   $user_id
				&& Data\User::user_has_author_info_cap_on_network( $user_id )
			),
			$user_id,
		);
	}

}
