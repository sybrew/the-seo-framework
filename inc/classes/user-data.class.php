<?php
/**
 * @package The_SEO_Framework\Classes\Facade\User_Data
 * @subpackage The_SEO_Framework\Data
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Data,
	\The_SEO_Framework\Helper\Query;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\User_Data
 *
 * Holds and maintains User data.
 *
 * @since 3.0.0
 */
class User_Data extends Term_Data {

	/**
	 * Saves user profile fields.
	 *
	 * @since 4.1.4
	 * @since 4.2.0 Now repopulates not-posted user metadata.
	 * @access private
	 *
	 * @param int $user_id The user ID.
	 */
	public function _update_user_meta( $user_id ) {

		if ( empty( $_POST ) ) return;

		\check_admin_referer( "update-user_{$user_id}" );
		if ( ! \current_user_can( 'edit_user', $user_id ) ) return;

		if ( ! Data\User::user_has_author_info_cap_on_network( $user_id ) ) return;

		// We won't reset the data, just overwrite what's given.
		// This is because we only update a portion of the meta.
		$data = array_merge(
			Data\Plugin\User::get_user_meta( $user_id ),
			(array) ( $_POST['tsf-user-meta'] ?? [] )
		);

		Data\Plugin\User::save_user_meta( $user_id, $data );
	}
}
