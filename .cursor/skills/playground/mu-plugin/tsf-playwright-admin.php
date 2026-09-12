<?php
/**
 * @package The_SEO_Framework\Playground
 */

defined( 'ABSPATH' ) or die;

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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

add_action(
	'set_auth_cookie',
	'wpr_tsf_playwright_sync_auth_cookie',
	10,
	5,
);
add_action(
	'set_logged_in_cookie',
	'wpr_tsf_playwright_sync_logged_in_cookie',
);
add_action(
	'init',
	'wpr_tsf_playwright_admin_login',
	0,
);

/**
 * Copies the auth cookie into $_COOKIE so auth_redirect() passes on this request.
 *
 * @since 5.1.5
 *
 * @param string $cookie     Cookie value.
 * @param int    $expire     Cookie expire.
 * @param int    $expiration Session expire.
 * @param int    $user_id    User ID.
 * @param string $scheme     Cookie scheme.
 */
function wpr_tsf_playwright_sync_auth_cookie( $cookie, $expire, $expiration, $user_id, $scheme ) {
	$_COOKIE[ 'secure_auth' === $scheme ? SECURE_AUTH_COOKIE : AUTH_COOKIE ] = $cookie;
}

/**
 * Copies the logged-in cookie into $_COOKIE for this request.
 *
 * @since 5.1.5
 *
 * @param string $cookie Cookie value.
 */
function wpr_tsf_playwright_sync_logged_in_cookie( $cookie ) {
	$_COOKIE[ LOGGED_IN_COOKIE ] = $cookie;
}

/**
 * Returns the current request path.
 *
 * @since 5.1.5
 *
 * @return string
 */
function wpr_tsf_playwright_request_path() {

	$uri = $_SERVER['REQUEST_URI'] ?? '';

	return (string) wp_parse_url( $uri, PHP_URL_PATH );
}

/**
 * Whether this request should receive a Playwright admin session.
 *
 * Front-end HTML stays logged-out. HTTP capture does not send the header.
 *
 * @since 5.1.5
 *
 * @return bool
 */
function wpr_tsf_playwright_wants_admin() {

	if ( defined( 'WP_ADMIN' ) && WP_ADMIN )
		return true;

	$path = wpr_tsf_playwright_request_path();

	if ( str_ends_with( $path, '/wp-login.php' ) )
		return true;

	return str_contains( $path, '/wp-json/' );
}

/**
 * Logs in the Playground admin when Playwright sends the admin header.
 *
 * @since 5.1.5
 */
function wpr_tsf_playwright_admin_login() {

	if ( empty( $_SERVER['HTTP_X_TSF_PLAYGROUND_ADMIN'] ) ) return;

	if ( '1' !== $_SERVER['HTTP_X_TSF_PLAYGROUND_ADMIN'] ) return;

	if ( ! wpr_tsf_playwright_wants_admin() ) return;

	if ( ! is_user_logged_in() ) {
		$user = get_user_by( 'login', 'admin' );

		if ( ! $user ) return;

		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID, true );
	}

	if ( ! str_ends_with( wpr_tsf_playwright_request_path(), '/wp-login.php' ) )
		return;

	wp_safe_redirect( admin_url() );
	exit;
}
