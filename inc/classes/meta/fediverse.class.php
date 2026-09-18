<?php
/**
 * @package The_SEO_Framework\Classes\Meta
 * @subpackage The_SEO_Framework\Meta\Fediverse
 */

namespace The_SEO_Framework\Meta;

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
 * Holds getters for Fediverse output.
 *
 * @since 5.1.5
 * @access protected
 *         Use tsf()->fediverse() instead.
 */
class Fediverse {

	/**
	 * Returns the Fediverse creator handle for the current query.
	 *
	 * Prefers the post author's profile, then the site author fallback, then the site profile.
	 *
	 * @since 5.1.5
	 *
	 * @return string The stored `@user@domain` handle. Empty string if none is set.
	 */
	public static function get_creator() {
		return Data\Plugin\User::get_current_post_author_meta_item( 'fediverse_page' )
			?: Data\Plugin::get_option( 'fediverse_creator' )
			?: Data\Plugin::get_option( 'fediverse_site' );
	}

	/**
	 * Returns the Fediverse creator profile URL for the current query.
	 *
	 * Uses the same source as get_creator(). Empty when that source has no stored URL;
	 * get_profile_url() can still build one from the handle.
	 *
	 * @since 5.1.5
	 *
	 * @return string The stored profile URL. Empty string if none is set.
	 */
	public static function get_creator_url() {

		if ( Data\Plugin\User::get_current_post_author_meta_item( 'fediverse_page' ) )
			return Data\Plugin\User::get_current_post_author_meta_item( 'fediverse_page_url' );

		if ( Data\Plugin::get_option( 'fediverse_creator' ) )
			return Data\Plugin::get_option( 'fediverse_creator_url' );

		return Data\Plugin::get_option( 'fediverse_site_url' );
	}

	/**
	 * Returns the site Fediverse profile handle.
	 *
	 * @since 5.1.5
	 *
	 * @return string The stored `@user@domain` handle. Empty string if none is set.
	 */
	public static function get_site() {
		return Data\Plugin::get_option( 'fediverse_site' );
	}

	/**
	 * Returns the site Fediverse profile URL.
	 *
	 * @since 5.1.5
	 *
	 * @return string The stored profile URL. Empty string if none is set.
	 */
	public static function get_site_url() {
		return Data\Plugin::get_option( 'fediverse_site_url' );
	}

	/**
	 * Returns a Fediverse profile URL from a stored URL or handle.
	 *
	 * @since 5.1.5
	 *
	 * @param string $handle A `@user@domain` handle.
	 * @param string $url    Optional. A stored profile URL. Used when set, so
	 *                       `rel=me` can match a host that differs from the handle.
	 * @return string The profile URL. Empty string on failure.
	 */
	public static function get_profile_url( $handle, $url = '' ) {

		if ( $url )
			return $url;

		if ( ! preg_match( '/^@([^@]+)@([^@]+)$/', $handle, $matches ) )
			return '';

		return \sanitize_url(
			"https://{$matches[2]}/@{$matches[1]}",
			[ 'https' ],
		);
	}

	/**
	 * Returns unique Fediverse profile URLs for `rel=me` output.
	 *
	 * Includes the site profile when set, and the current creator when it differs.
	 * Prefers a stored profile URL; otherwise builds one from the handle.
	 *
	 * @since 5.1.5
	 *
	 * @return string[] Profile URLs.
	 */
	public static function get_profile_urls() {

		foreach ( [
			[ self::get_site(), self::get_site_url() ],
			[ self::get_creator(), self::get_creator_url() ],
		] as [ $handle, $url ] ) {
			$url = self::get_profile_url( $handle, $url );

			if ( $url )
				$urls[] = $url;
		}

		return array_unique( $urls ?? [] );
	}
}
