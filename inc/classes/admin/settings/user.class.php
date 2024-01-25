<?php
/**
 * @package The_SEO_Framework\Classes\Admin\Settings\User
 * @subpackage The_SEO_Framework\Admin\Edit\User
 */

namespace The_SEO_Framework\Admin\Settings;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\{
	Data,
	Helper\Template,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2021 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Prepares the User Settings view interface.
 *
 * @since 4.1.4
 * @since 5.0.0 1. Renamed from `UserSettings` to `User`.
 *              2. Moved to `\The_SEO_Framework\Admin\Settings`.
 * @access private
 */
final class User {

	/**
	 * Prepares the user setting fields.
	 *
	 * @hook show_user_profile 0
	 * @hook edit_user_profile 0
	 * @since 4.1.4
	 * @since 5.0.0 1. Now asserts if user has capability on any multisite network's blog.
	 *              2. Renamed from `_prepare_setting_fields`.
	 *
	 * @param \WP_User $user WP_User object.
	 */
	public static function prepare_setting_fields( $user ) {

		if ( ! Data\User::user_has_author_info_cap_on_network( $user ) )
			return;

		static::output_setting_fields( $user );
	}

	/**
	 * Outputs user profile fields.
	 *
	 * @since 5.0.0
	 *
	 * @param \WP_User $user WP_User object.
	 */
	private static function output_setting_fields( $user ) {
		/**
		 * @since 4.1.4
		 */
		\do_action( 'the_seo_framework_before_author_fields' );
		Template::output_view( 'profile/settings', $user );
		/**
		 * @since 4.1.4
		 */
		\do_action( 'the_seo_framework_after_author_fields' );
	}
}
