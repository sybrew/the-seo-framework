<?php
/**
 * @package The_SEO_Framework\Classes\Facade\User_Data
 * @subpackage The_SEO_Framework\Data
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2022 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
	 * Initializes user meta data handlers.
	 *
	 * @since 4.1.4 Now protected.
	 */
	protected function init_user_meta() {
		\add_action( 'personal_options_update', [ $this, '_update_user_meta' ], 10, 1 );
		\add_action( 'edit_user_profile_update', [ $this, '_update_user_meta' ], 10, 1 );
	}

	/**
	 * Returns the user meta item by key.
	 *
	 * @since 4.1.4
	 *
	 * @param string $item      The item to get. Required.
	 * @param int    $user_id   The user ID. Optional.
	 * @param bool   $use_cache Whether to use caching.
	 * @return mixed The user meta item. Null when not found.
	 */
	public function get_user_meta_item( $item, $user_id = 0, $use_cache = true ) {
		return $this->get_user_meta( $user_id ?: $this->get_user_id(), $use_cache )[ $item ] ?? null;
	}

	/**
	 * Returns the author meta item by key.
	 *
	 * @since 4.1.4
	 *
	 * @param string $item      The item to get. Required.
	 * @param bool   $use_cache Whether to use caching.
	 * @return mixed The author meta item. Null when not found.
	 */
	public function get_current_post_author_meta_item( $item, $use_cache = true ) {
		return $this->get_user_meta_item( $item, $this->get_current_post_author_id(), $use_cache );
	}

	/**
	 * Returns and caches author meta for the current query.
	 * Memoizes the return value for the current request.
	 *
	 * @since 4.1.4
	 * @TODO Throw this away? We do not use it... never have.
	 *
	 * @return array The current author meta.
	 */
	public function get_current_post_author_meta() {
		return memo() ?? memo( $this->get_user_meta( $this->get_current_post_author_id() ) );
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
	 * @todo Send deprecation warning for 3rd parameter
	 *
	 * @param int  $user_id   The user ID.
	 * @param bool $use_cache Whether to store and use options from cache, or bypass it.
	 * @param bool $depr      Deprecated.
	 * @return array The user SEO meta data.
	 */
	public function get_user_meta( $user_id = 0, $use_cache = true, $depr = true ) {

		if ( false === $depr ) $use_cache = false;

		$user_id = $user_id ?: $this->get_user_id();

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( $use_cache && null !== $memo = memo( null, $user_id ) )
			return $memo;

		/**
		 * We can't trust the filter to always contain the expected keys.
		 * However, it may contain more keys than we anticipated. Merge them.
		 */
		$defaults = array_merge(
			$this->get_unfiltered_user_meta_defaults(),
			$this->get_user_meta_defaults( $user_id )
		);

		if ( $this->is_headless['user'] ) {
			$meta = [];

			if ( \in_array( false, $this->is_headless, true ) ) {
				// Some data is still used for the interface elsewhere. Let's retrieve that at least.
				// We filter out the rest because that's 'not supported' or otherwise 'immutable' in headless-mode.
				$_meta = \get_user_meta( $user_id, THE_SEO_FRAMEWORK_USER_OPTIONS, true ) ?: [];

				foreach ( $this->get_headless_user_meta_support() as $meta_key => $supports ) {
					if ( ! isset( $_meta[ $meta_key ] ) ) continue;
					foreach ( $supports as $support_type ) {
						if ( $this->is_headless[ $support_type ] ) continue;
						$meta[ $meta_key ] = $_meta[ $meta_key ];
						continue 2;
					}
				}
			}
		} else {
			$meta = \get_user_meta( $user_id, THE_SEO_FRAMEWORK_USER_OPTIONS, true ) ?: [];
		}

		/**
		 * @since 4.1.4
		 * @note Do not delete/unset/add indexes! It'll cause errors.
		 * @param array $meta    The current user meta.
		 * @param int   $user_id The user ID.
		 * @param bool  $headless Whether the meta are headless.
		 */
		$meta = \apply_filters_ref_array(
			'the_seo_framework_user_meta',
			[
				array_merge( $defaults, $meta ),
				$user_id,
				$this->is_headless['user'],
			]
		);

		// Do not overwrite cache when not requested. Otherwise, we'd have two "initial" states, causing incongruities.
		return $use_cache ? memo( $meta, $user_id ) : $meta;
	}

	/**
	 * Returns an array of default user meta.
	 *
	 * @since 4.1.4
	 *
	 * @param int $user_id The user ID. Defaults to CURRENT USER, NOT CURRENT POST AUTHOR.
	 * @return array The user meta defaults.
	 */
	public function get_user_meta_defaults( $user_id = 0 ) {
		/**
		 * @since 4.1.4
		 * @param array $defaults
		 * @param int   $user_id
		 */
		return (array) \apply_filters_ref_array(
			'the_seo_framework_user_meta_defaults',
			[
				$this->get_unfiltered_user_meta_defaults(),
				$user_id ?: $this->get_user_id(),
			]
		);
	}

	/**
	 * Returns the unfiltered user meta defaults.
	 *
	 * @since 4.1.4
	 *
	 * @return array The default, unfiltered, post meta.
	 */
	protected function get_unfiltered_user_meta_defaults() {
		return [
			'counter_type'  => 3,
			'facebook_page' => '',
			'twitter_page'  => '',
		];
	}

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

		$user = \get_userdata( $user_id );

		if ( ! $user->has_cap( THE_SEO_FRAMEWORK_AUTHOR_INFO_CAP ) ) return;

		$data = \wp_parse_args(
			(array) $_POST['tsf-user-meta'],
			$this->get_user_meta( $user_id )
		);

		$this->save_user_meta( $user_id, $data );
	}

	/**
	 * Updates user TSF-meta option.
	 *
	 * @since 4.1.4
	 *
	 * @param int    $user_id The user ID.
	 * @param string $option  The user's SEO metadata to update.
	 * @param mixed  $value   The option value.
	 */
	public function update_single_user_meta_item( $user_id, $option, $value ) {

		$user = \get_userdata( $user_id );

		// We could test for !$user, but this is more to the point.
		if ( empty( $user->ID ) ) return;

		$meta            = $this->get_user_meta( $user_id, false );
		$meta[ $option ] = $value;

		$this->save_user_meta( $user_id, $meta );
	}

	/**
	 * Updates users meta from input.
	 *
	 * @since 4.1.4
	 * @since 4.2.0 No longer returns the update success state.
	 *
	 * @param int   $user_id The user ID.
	 * @param array $data    The data to save.
	 */
	public function save_user_meta( $user_id, $data ) {

		$user = \get_userdata( $user_id );

		// We could test for !$user, but this is more to the point.
		if ( empty( $user->ID ) ) return;

		/**
		 * @since 4.1.4
		 * @param array  $data     The data that's going to be saved.
		 * @param int    $user_id  The user ID.
		 */
		$data = (array) \apply_filters_ref_array(
			'the_seo_framework_save_user_data',
			[
				$this->s_user_meta( (array) \wp_parse_args(
					$data,
					$this->get_user_meta_defaults( $user->ID )
				) ),
				$user->ID,
			]
		);

		\update_user_meta( $user->ID, THE_SEO_FRAMEWORK_USER_OPTIONS, $data );
	}

	/**
	 * Returns the current post author ID.
	 * Memoizes the return value for the current request.
	 *
	 * @since 3.0.0
	 * @since 3.2.2 1. Now no longer returns the latest post author ID on home-as-blog pages.
	 *              2. Now always returns an integer.
	 *
	 * @return int Post author ID on success, 0 on failure.
	 */
	public function get_current_post_author_id() {
		return memo() ?? memo(
			$this->is_singular()
				? (int) ( \get_post( $this->get_the_real_ID() )->post_author ?? 0 )
				: 0
		);
	}

	/**
	 * Sets up user ID and returns it if user is found.
	 * To be used in AJAX, back-end and front-end.
	 *
	 * @since 2.7.0
	 *
	 * @return int The user ID. 0 if user is not found.
	 */
	public function get_user_id() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo() ) return $memo;

		$user = \wp_get_current_user();

		return memo( $user->exists() ? (int) $user->ID : 0 );
	}

	/**
	 * Returns meta keys for user settings that may still be required when headless.
	 * Used only when not all of TSF is headless.
	 *
	 * @since 4.1.4
	 *
	 * @return array The headless meta keys. : {
	 *    string $meta_key => string[] $supports
	 * }
	 */
	protected function get_headless_user_meta_support() {
		return [
			'counter_type' => [
				'meta',
				'settings',
			],
		];
	}
}
