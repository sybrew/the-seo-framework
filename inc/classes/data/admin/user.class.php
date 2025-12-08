<?php
/**
 * @package The_SEO_Framework\Classes\Data\Admin\User
 * @subpackage The_SEO_Framework\Data
 */

namespace The_SEO_Framework\Data\Admin;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use The_SEO_Framework\Data;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 - 2025 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds a collection of data saving methods for users.
 *
 * @since 5.0.0
 * @access private
 */
final class User {

	/**
	 * @since 5.1.3
	 * @var array[] {
	 *     The nonce data per save context.
	 *     WordPress's native nonce doesn't suffice because these options are togglable.
	 *
	 *     @type string $name   The nonce field name.
	 *     @type string $action The nonce action.
	 * }
	 */
	public const SAVE_NONCES = [
		'user-edit' => [
			'name'   => 'tsf_user_nonce_name',
			'action' => 'tsf_user_nonce_action',
		],
	];

	/**
	 * Saves user profile fields.
	 *
	 * @hook personal_options_update 10
	 * @hook edit_user_profile_update 10
	 * @since 4.1.4
	 * @since 4.2.0 Now repopulates not-posted user metadata.
	 * @since 5.0.0 1. Now tests if our POST data is set at all before acting.
	 *              2. Moved from `\The_SEO_Framework\Load`.
	 *              3. Renamed from `_update_user_meta`.
	 * @access private
	 *
	 * @param int $user_id The user ID.
	 */
	public static function update_meta( $user_id ) {

		if ( empty( $_POST['tsf-user-meta'] ) ) return;

		if ( ! \current_user_can( 'edit_user', $user_id ) ) return;

		if (
			   ! isset( $_POST[ self::SAVE_NONCES['user-edit']['name'] ] )
			|| ! \wp_verify_nonce( $_POST[ self::SAVE_NONCES['user-edit']['name'] ], self::SAVE_NONCES['user-edit']['action'] )
		) return;

		if ( ! Data\User::user_has_author_info_cap_on_network( $user_id ) ) return;

		// We won't reset the data, just overwrite what's given.
		// This is because we only update a portion of the meta.
		$data = array_merge(
			Data\Plugin\User::get_meta( $user_id ),
			(array) ( $_POST['tsf-user-meta'] ?? [] ),
		);

		Data\Plugin\User::save_meta( $user_id, $data );
	}
}
