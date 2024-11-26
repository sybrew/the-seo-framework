<?php
/**
 * @package The_SEO_Framework\Classes\Data\Plugin\User
 * @subpackage The_SEO_Framework\Data\Plugin
 */

namespace The_SEO_Framework\Data\Plugin;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\is_headless;

use \The_SEO_Framework\{
	Helper\Query,
	Traits\Property_Refresher,
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
 * Holds a collection of User data interface methods for TSF.
 *
 * @since 5.0.0
 * @since 5.1.0 Added the Property_Refresher trait.
 * @access protected
 *         Use tsf()->data()->plugin()->user() instead.
 */
class User {
	use Property_Refresher;

	/**
	 * @since 5.0.0
	 * @var array[] Stored user meta data.
	 */
	private static $meta_memo = [];

	/**
	 * Returns the current post's author meta item by key.
	 * Won't fall back to the logged in user's data.
	 *
	 * @since 4.1.4
	 * @since 4.2.8 Now returns null when no post author can be established.
	 * @since 5.0.0 1. Removed the second `$use_cache` parameter.
	 *              2. Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param string $item The user meta item to get. Required.
	 * @return ?mixed The author meta item. Null when no author is found.
	 */
	public static function get_current_post_author_meta_item( $item ) {

		$user_id = Query::get_post_author_id();

		return $user_id
			? static::get_meta_item( $item, $user_id )
			: null;
	}

	/**
	 * Returns and caches author meta for the current query.
	 * Memoizes the return value for the current request.
	 *
	 * @since 4.1.4
	 * @since 4.2.7 Removed redundant memoization.
	 * @since 4.2.8 Now returns null when no post author can be established.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 * @ignore Unused internally. Public API.
	 *
	 * @return ?array The current author meta, null when no author is set.
	 */
	public static function get_current_post_author_meta() {

		$user_id = Query::get_post_author_id();

		return $user_id
			? static::get_meta( $user_id )
			: null;
	}

	/**
	 * Returns the user meta item by key.
	 * Will fall back to the CURRENT LOGGED IN user's metadata.
	 *
	 * @since 4.1.4
	 * @since 5.0.0 1. Removed the third `$use_cache` parameter.
	 *              2. Moved from `\The_SEO_Framework\Load`.
	 *              3. Renamed from `get_user_meta_item`.
	 *
	 * @param string $item      The user meta item to get. Required.
	 * @param int    $user_id   The user ID. Optional.
	 * @return mixed The user meta item. Null when not found.
	 */
	public static function get_meta_item( $item, $user_id = 0 ) {

		$user_id = $user_id ?: Query::get_current_user_id();

		return $user_id
			? static::get_meta( $user_id )[ $item ] ?? null
			: null;
	}

	/**
	 * Fetches usermeta set by The SEO Framework.
	 * Memoizes the return value, can be bypassed.
	 *
	 * @since 2.7.0
	 * @since 2.8.0 Always returns array, even if no value is assigned.
	 * @since 4.1.4 1. Now returns default values when custom values are missing.
	 *              2. Now listens to headlessness.
	 *              3. Deprecated the third argument, and moved it to the second.
	 * @since 5.0.0 1. Removed the second `$depr` and third `$use_cache` parameter.
	 *              2. Moved from `\The_SEO_Framework\Load`.
	 *              3. Renamed from `get_user_meta`.
	 * @since 5.1.0 Now returns the default meta if the user ID is empty.
	 *
	 * @param int $user_id The user ID.
	 * @return array The user SEO meta data.
	 */
	public static function get_meta( $user_id = 0 ) {

		$user_id = $user_id ?: Query::get_current_user_id();

		if ( isset( static::$meta_memo[ $user_id ] ) )
			return static::$meta_memo[ $user_id ];

		// Code smell: the empty test is for performance since the memo can be bypassed by input vars.
		empty( static::$meta_memo ) and static::register_automated_refresh( 'meta_memo' );

		// TODO test if user exists via get_userdata()?
		// That is expensive; the user object is not created when fetching meta.
		// But we might as well have a user already when we reach this point.
		if ( empty( $user_id ) )
			return static::$meta_memo[ $user_id ] = static::get_default_meta( $user_id );

		// Keep lucky first when exceeding nice numbers. This way, we won't overload memory in memoization.
		if ( \count( static::$meta_memo ) > 69 )
			static::$meta_memo = \array_slice( static::$meta_memo, 0, 7, true );

		$is_headless = is_headless();

		if ( $is_headless['user'] ) {
			// We filter out everything that's 'not supported' or otherwise 'immutable' in headless-mode.
			$meta = [];

			if ( \in_array( false, $is_headless, true ) ) {
				$_meta = \get_user_meta( $user_id, \THE_SEO_FRAMEWORK_USER_OPTIONS, true ) ?: [];
				// The counter type is still supported for meta and settings.
				// Retrieve those items if either type (meta/settings) isn't headless.
				$non_headless_meta = [
					'counter_type' => [
						'meta',
						'settings',
					],
				];

				// Grab non-headless meta if any meta type isn't headless.
				foreach ( $non_headless_meta as $meta_key => $meta_types ) {
					if ( ! isset( $_meta[ $meta_key ] ) ) continue;

					foreach ( $meta_types as $meta_type ) {
						if ( $is_headless[ $meta_type ] ) continue;

						$meta[ $meta_key ] = $_meta[ $meta_key ];
						// We made this key bypass headless mode. Skip subsequently redundant checks.
						continue 2;
					}
				}
			}
		} else {
			// FIXME: (array) is a patch. We messed up the datastore in 5.1.1, where strings got stored instead of arrays.
			// We'll rectify it in a future database upgrade, so we can remove the patch.
			$meta = (array) ( \get_user_meta( $user_id, \THE_SEO_FRAMEWORK_USER_OPTIONS, true ) ?: [] );
		}

		/**
		 * @since 4.1.4
		 * @param array $meta        The current user meta.
		 *                           If headless, it may still contain administration settings.
		 * @param int   $user_id     The user ID.
		 * @param bool  $is_headless Whether the meta are headless.
		 */
		return static::$meta_memo[ $user_id ] = \apply_filters(
			'the_seo_framework_user_meta',
			array_merge(
				static::get_default_meta( $user_id ),
				$meta,
			),
			$user_id,
			$is_headless['user'],
		);
	}

	/**
	 * Returns an array of default user meta.
	 *
	 * @since 4.1.4
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `get_user_meta_defaults`.
	 *
	 * @param int $user_id The user ID. Defaults to CURRENT USER, NOT CURRENT POST AUTHOR.
	 * @return array The user meta defaults.
	 */
	public static function get_default_meta( $user_id = 0 ) {
		/**
		 * @since 4.1.4
		 * @param array $defaults
		 * @param int   $user_id
		 */
		return (array) \apply_filters(
			'the_seo_framework_user_meta_defaults',
			[
				'counter_type'  => 3,
				'facebook_page' => '',
				'twitter_page'  => '',
			],
			$user_id ?: Query::get_current_user_id(),
		);
	}

	/**
	 * Updates user TSF-meta option.
	 *
	 * @since 4.1.4
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `update_single_user_meta_item`.
	 *
	 * @param int    $user_id The user ID.
	 * @param string $item    The user's SEO meta item to update.
	 * @param mixed  $value   The option value.
	 */
	public static function update_single_meta_item( $user_id, $item, $value ) {

		// Make sure the user exists before we go through another hoop of fetching all data.
		$user    = \get_userdata( $user_id );
		$user_id = $user->ID ?? null;

		if ( empty( $user_id ) ) return;

		$meta          = static::get_meta( $user_id );
		$meta[ $item ] = $value;

		static::save_meta( $user_id, $meta );
	}

	/**
	 * Updates users meta from input.
	 *
	 * @since 4.1.4
	 * @since 4.2.0 No longer returns the update success state.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `save_user_meta`.
	 *
	 * @param int   $user_id The user ID.
	 * @param array $data    The data to save.
	 */
	public static function save_meta( $user_id, $data ) {

		$user    = \get_userdata( $user_id );
		$user_id = $user->ID ?? null;

		if ( empty( $user_id ) ) return;

		/**
		 * @since 4.1.4
		 * @since 5.0.0 No longer sends pre-sanitized data to the filter.
		 * @param array  $data     The data that's going to be saved.
		 * @param int    $user_id  The user ID.
		 */
		$data = (array) \apply_filters(
			'the_seo_framework_save_user_data',
			array_merge(
				static::get_default_meta( $user_id ),
				$data,
			),
			$user->ID,
		);

		unset( static::$meta_memo[ $user_id ] );

		\update_user_meta( $user_id, \THE_SEO_FRAMEWORK_USER_OPTIONS, $data );
	}

	/**
	 * Deletes term meta.
	 * Deletes only the default data keys as set by `get_default_meta()`
	 * or everything when no custom keys are set.
	 *
	 * @since 5.0.0
	 * @ignore Unused internally. Public API.
	 *
	 * @param int $user_id The user ID.
	 */
	public static function delete_meta( $user_id ) {

		// If this results in an empty data string, all data has already been removed by WP core.
		$data = \get_user_meta( $user_id, \THE_SEO_FRAMEWORK_USER_OPTIONS, true );

		if ( \is_array( $data ) ) {
			foreach ( static::get_default_meta( $user_id ) as $key => $value )
				unset( $data[ $key ] );
		}

		// Always unset. We must refill defaults later.
		unset( static::$meta_memo[ $user_id ] );

		// Only delete when no values are left, because someone else might've filtered it.
		if ( empty( $data ) ) {
			\delete_user_meta( $user_id, \THE_SEO_FRAMEWORK_USER_OPTIONS );
		} else {
			\update_user_meta( $user_id, \THE_SEO_FRAMEWORK_USER_OPTIONS, $data );
		}
	}
}
