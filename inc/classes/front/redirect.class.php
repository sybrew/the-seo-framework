<?php
/**
 * @package The_SEO_Framework\Classes\Front\Redirect
 * @subpackage The_SEO_Framework\Redirect
 */

namespace The_SEO_Framework\Front;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\{
	Helper,
	Helper\Query,
	Meta,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2020 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Prepares redirects.
 *
 * @since 5.0.0
 * @access private
 */
final class Redirect {

	/**
	 * Redirects singular page to an alternate URL.
	 *
	 * @hook template_redirect 10
	 * @since 2.9.0
	 * @since 3.1.0 1. Now no longer redirects on preview.
	 *              2. Now listens to post type settings.
	 * @since 4.0.0 1. No longer tries to redirect on "search".
	 *              2. Added term redirect support.
	 *              3. No longer redirects on Customizer.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `_init_custom_field_redirect`.
	 *
	 * @return void early on non-singular pages.
	 */
	public static function init_meta_setting_redirect() {

		if ( ! Query\Utils::query_supports_seo() ) return;

		$url = Meta\URI::get_redirect_url();

		if ( $url ) {
			/**
			 * @since 4.1.2
			 * @param string $url The URL we're redirecting to.
			 */
			\do_action( 'the_seo_framework_before_redirect', $url );

			static::do_redirect( $url );
		}
	}

	/**
	 * Redirects vistor to input $url.
	 *
	 * @since 2.9.0
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. First parameter is now required.
	 *              3. Removed various sanity tests, since this method is no longer public.
	 *              4. Now exists with a 400 error code when the URL failed.
	 *
	 * @param string $url The redirection URL
	 */
	public static function do_redirect( $url ) {

		// All WP defined protocols are allowed.
		$url = \sanitize_url( $url );

		if ( empty( $url ) ) {
			\status_header( 400 );
			exit;
		}

		/**
		 * @since 2.8.0
		 * @param int <unsigned> $redirect_type
		 */
		$redirect_type = \absint( \apply_filters( 'the_seo_framework_redirect_status_code', 301 ) );

		if ( $redirect_type > 399 || $redirect_type < 300 )
			\tsf()->_doing_it_wrong( __METHOD__, 'You should use 3xx HTTP Status Codes. Recommended 301 and 302.', '2.8.0' );

		if ( ! Helper\Redirect::allow_external_redirect() ) {
			// Only HTTP/HTTPS and home URLs are allowed. Maintain current request's scheme.
			$url = Meta\URI\Utils::set_url_scheme( Meta\URI\Utils::convert_path_to_url(
				Meta\URI\Utils::set_url_scheme( $url, 'relative' )
			) );

			\wp_safe_redirect( $url, $redirect_type );
			exit;
		}

		// phpcs:ignore, WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- intended feature.
		\wp_redirect( $url, $redirect_type );
		exit;
	}
}
