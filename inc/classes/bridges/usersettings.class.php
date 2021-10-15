<?php
/**
 * @package The_SEO_Framework\Classes\Bridges\UserSettings
 * @subpackage The_SEO_Framework\Admin\Edit\User
 */

namespace The_SEO_Framework\Bridges;

/**
 * The SEO Framework plugin
 * Copyright (C) 2021 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * Prepares the User Settings view interface.
 *
 * @since 4.1.4
 * @access protected
 * @internal
 * @final Can't be extended.
 */
final class UserSettings {

	/**
	 * Prepares the user setting fields.
	 *
	 * @since 4.1.4
	 * @access private
	 *
	 * @param \WP_User $user WP_User object.
	 */
	public static function _prepare_setting_fields( $user ) {

		if ( ! $user->has_cap( THE_SEO_FRAMEWORK_AUTHOR_INFO_CAP ) ) return;

		static::add_user_author_fields( $user );
	}

	/**
	 * Outputs user profile fields.
	 *
	 * @since 4.1.4
	 *
	 * @param \WP_User $user WP_User object.
	 */
	private static function add_user_author_fields( $user ) { // phpcs:ignore, VariableAnalysis.CodeAnalysis.VariableAnalysis -- get_defined_vars() is used later.
		/**
		 * @since 4.1.4
		 */
		\do_action( 'the_seo_framework_before_author_fields' );
		\tsf()->get_view( 'profile/author', get_defined_vars() );
		/**
		 * @since 4.1.4
		 */
		\do_action( 'the_seo_framework_after_author_fields' );
	}
}
