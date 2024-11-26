<?php
/**
 * @package The_SEO_Framework\Classes\Data\Post
 * @subpackage The_SEO_Framework\Data
 */

namespace The_SEO_Framework\Data;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\umemo;

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
 * Holds a collection of data helper methods for a user.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->data()->user() instead.
 */
class User {

	/**
	 * Saves user profile fields.
	 *
	 * @since 5.0.0
	 * @todo add memoization?
	 *
	 * @param int|\WP_User $user A user ID or valid \WP_User object.
	 * @return bool True if user has author info cap on any blog.
	 */
	public static function user_has_author_info_cap_on_network( $user ) {

		if ( ! \is_object( $user ) )
			$user = static::get_userdata( $user );

		// User is logged out, how did I get here? (nice song btw)
		if ( ! $user )
			return false;

		if ( \is_multisite() ) {
			// If on multisite, WP prevents editing of other's profiles for non-super admin.
			// Hence, this is fine to test this on either single-or-multisite user-edit.

			// Clone user so not to taint the global object.
			$_user = clone $user;

			// It's funny: get_blogs_of_user() uses the capabilities field to see if the user is of a blog.
			// Then, we switch to the blog to extract those capabilities. This cannot be improved here for security reasons.
			foreach ( \get_blogs_of_user( $_user->ID ) as $user_blog ) {
				// We must use switch_to_blog() for plugins may insert custom roles for the site.
				\switch_to_blog( $user_blog->userblog_id );

				// Neither the stored nor cloned user object switches with switch_to_blog(); let's fix that:
				$_user->for_site( $user_blog->userblog_id );

				$user_has_cap = $_user->has_cap( \THE_SEO_FRAMEWORK_AUTHOR_INFO_CAP );

				\restore_current_blog();
				// There's no need to switch back $_user for it's a clone.

				if ( $user_has_cap ) break;
			}

			// Return early -- we already check "this" blog.
			return $user_has_cap ?? false;
		}

		return $user->has_cap( \THE_SEO_FRAMEWORK_AUTHOR_INFO_CAP );
	}

	/**
	 * Gets user data by key.
	 *
	 * This is an alias of WP Core's `\get_userdata()`, but with proper memoization.
	 *
	 * @since 5.1.0
	 * @since 5.1.1 1. The second parameter is now nullable and null by default.
	 *              2. Can now return the user object as well when the second parameter is null.
	 *
	 * @param int     $user_id The user ID.
	 * @param ?string $key     The data to retrieve. Leave empty to get all data.
	 * @return ?mixed|?\WP_User The requested user data.
	 *                          If `$key` isn't set, it'll return the WP_User object.
	 *                          Null on failure.
	 */
	public static function get_userdata( $user_id, $key = null ) {

		$userdata = umemo( __METHOD__, null, $user_id )
				 ?? umemo( __METHOD__, \get_userdata( $user_id ), $user_id );

		return isset( $key )
			? ( $userdata->$key ?? null )
			: ( $userdata ?: null );
	}
}
