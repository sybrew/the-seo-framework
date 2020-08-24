<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Profile
 * @subpackage The_SEO_Framework\Admin\Profile
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2020 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\Profile
 *
 * Outputs Profile fields and saves metadata.
 * TODO move this to admin-pages, invoke like `_init_post_edit_view()`?
 *
 * @since 3.0.0
 */
class Profile extends Generate_Ldjson {

	/**
	 * Outputs profile fields and prepares saving thereof.
	 *
	 * @since 3.0.0
	 */
	protected function init_profile_fields() {

		//= No need to load anything if the current user can't even author posts.
		// This is ultimately useless checking this on EVERY admin page.
		// Debug me... 294 microseconds overhead. The cap check is cached, it seems. Takes as much time adding the actions.
		if ( ! \current_user_can( THE_SEO_FRAMEWORK_AUTHOR_INFO_CAP ) ) return;

		\add_action( 'show_user_profile', [ $this, '_add_user_author_fields' ], 0, 1 );
		\add_action( 'edit_user_profile', [ $this, '_add_user_author_fields' ], 0, 1 );

		\add_action( 'personal_options_update', [ $this, '_update_user_settings' ], 10, 1 );
		\add_action( 'edit_user_profile_update', [ $this, '_update_user_settings' ], 10, 1 );
	}

	/**
	 * Returns the current profile field settings.
	 *
	 * @since 4.0.0
	 *
	 * @return \stdClass The profile settings.
	 */
	protected function get_profile_field_settings() {
		return (object) [
			'keys'         => [
				'facebook_page' => 'tsf_facebook_page',
				'twitter_page'  => 'tsf_twitter_page',
			],
			'sanitization' => [
				'facebook_page' => 's_facebook_profile',
				'twitter_page'  => 's_twitter_name',
			],
		];
	}

	/**
	 * Outputs user profile fields.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @param \WP_User $user WP_User object.
	 */
	public function _add_user_author_fields( \WP_User $user ) {

		if ( ! $user->has_cap( THE_SEO_FRAMEWORK_AUTHOR_INFO_CAP ) ) return;

		// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable -- includes...
		$_field_settings = $this->get_profile_field_settings();

		$fields = [
			$_field_settings->keys['facebook_page'] => (object) [
				'name'        => \__( 'Facebook profile page', 'autodescription' ),
				'type'        => 'url',
				'placeholder' => \_x( 'https://www.facebook.com/YourPersonalProfile', 'Example Facebook Personal URL', 'autodescription' ),
				'value'       => $this->get_user_option( $user->ID, 'facebook_page' ),
				'class'       => '',
			],
			$_field_settings->keys['twitter_page']  => (object) [
				'name'        => \__( 'Twitter profile name', 'autodescription' ),
				'type'        => 'text',
				'placeholder' => \_x( '@your-personal-username', 'Twitter @username', 'autodescription' ),
				'value'       => $this->get_user_option( $user->ID, 'twitter_page' ),
				'class'       => 'ltr',
			],
		];
		// phpcs:enable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

		$this->get_view( 'profile/author', get_defined_vars() );
	}

	/**
	 * Saves user profile fields.
	 *
	 * @since 3.0.0
	 * @securitycheck 3.0.0 OK. NOTE: Nonces and refer(r)ers have been checked prior
	 *                          to the actions bound to this method. We check against them, redundantly.
	 * @access private
	 *
	 * @param int $user_id The user ID.
	 */
	public function _update_user_settings( $user_id ) {

		if ( empty( $_POST ) ) return;

		\check_admin_referer( 'update-user_' . $user_id );
		if ( ! \current_user_can( 'edit_user', $user_id ) ) return;

		$user = new \WP_User( $user_id );

		if ( ! $user->has_cap( THE_SEO_FRAMEWORK_AUTHOR_INFO_CAP ) ) return;

		$success  = [];
		$defaults = $this->get_default_user_data();

		$_field_settings = $this->get_profile_field_settings();

		foreach ( $_field_settings->keys as $option => $post_key ) {
			if ( isset( $_POST[ $post_key ] ) ) {
				$value = $this->{$_field_settings->sanitization[ $option ]}( $_POST[ $post_key ] )
					   ?: $defaults[ $option ]; // phpcs:ignore, WordPress.WhiteSpace

				$success[] = (bool) $this->update_user_option( $user_id, $option, $value );
			}
		}

		//? Do something with $success?
	}
}
