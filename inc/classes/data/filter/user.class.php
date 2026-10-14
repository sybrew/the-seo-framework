<?php
/**
 * @package The_SEO_Framework\Classes\Data\Filter\User
 * @subpackage The_SEO_Framework\Data\User
 */

namespace The_SEO_Framework\Data\Filter;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 - 2026 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds a collection of user meta sanitization methods.
 *
 * @since 5.0.0
 * @access private
 */
final class User {

	/**
	 * @hook "sanitize_user_meta_ . THE_SEO_FRAMEWORK_USER_OPTIONS" 10
	 * @since 5.0.0
	 * @since 5.2.0 1. No longer accepts `counter_type`.
	 *              2. Added author archive SEO fields.
	 *
	 * @param mixed[] $meta_value An unsanitized value.
	 * @return array[] The sanitized user meta.
	 */
	public static function filter_meta_update( $meta_value ) {

		if ( empty( $meta_value ) || ! \is_array( $meta_value ) )
			return [];

		// If registered metadata yields empty -- do not unset key! It'll override "defaults" that way.
		foreach ( $meta_value as $key => &$value ) {
			switch ( $key ) {
				case 'doctitle':
				case 'og_title':
				case 'tw_title':
				case 'description':
				case 'og_description':
				case 'tw_description':
					$value = Sanitize::metadata_content( $value );
					break;

				case 'title_no_blog_name':
					$value = Sanitize::boolean_integer( $value );
					break;

				case 'tw_card_type':
					$value = Sanitize::twitter_card( $value );
					break;

				case 'social_image_url':
					$value = \sanitize_url( $value, [ 'https', 'http' ] );
					break;

				case 'social_image_id':
					// Bound to social_image_url.
					$value = empty( $meta_value['social_image_url'] ) ? 0 : \absint( $value );
					break;

				case 'facebook_page':
					$value = Sanitize::facebook_profile_link( $value );
					break;

				case 'fediverse_page':
					$value = Sanitize::fediverse_profile_handle( $value );
					break;

				case 'fediverse_page_url':
					$value = Sanitize::fediverse_profile_url( $value );
					break;

				case 'twitter_page':
					$value = Sanitize::twitter_profile_handle( $value );
					break;

				case 'canonical':
					$value = Sanitize::fully_qualified_url( $value );
					break;

				case 'noindex':
				case 'nofollow':
				case 'noarchive':
					$value = Sanitize::qubit( $value );
					break;

				case 'redirect':
					$value = Sanitize::redirect_url( $value );
					break;

				default:
					unset( $meta_value[ $key ] );
			}
		}

		// Store an empty array on failure. Data\Plugin\User::get_meta() repopulates it on demand.
		return $meta_value ?: [];
	}

	/**
	 * @hook "sanitize_user_meta_" . THE_SEO_FRAMEWORK_USER_PREFERENCES 10
	 * @since 5.2.0
	 *
	 * @param mixed $meta_value An unsanitized value.
	 * @return array The sanitized user preferences.
	 */
	public static function filter_preference_update( $meta_value ) {

		if ( ! \is_array( $meta_value ) )
			return [];

		foreach ( $meta_value as $key => &$value ) {
			switch ( $key ) {
				case 'counter_type':
					$value = Sanitize::counter_type( $value );
					break;

				default:
					unset( $meta_value[ $key ] );
			}
		}

		return $meta_value ?: [];
	}
}
