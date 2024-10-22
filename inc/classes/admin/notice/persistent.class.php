<?php
/**
 * @package The_SEO_Framework\Classes\Admin\Notice\Persistent
 * @subpackage The_SEO_Framework\Admin\Notice
 */

namespace The_SEO_Framework\Admin\Notice;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\{
	Data,
	Helper\Query,
	Helper\Template,
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
 * Holds persistent notices functionality.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->admin()->notice()->persistent() instead.
 */
class Persistent {

	/**
	 * Registers dismissible persistent notice, that'll respawn during page load until dismissed or otherwise expired.
	 *
	 * @since 4.1.0
	 * @since 4.1.3 Now handles timeout values below -1 gracefully, by purging the whole notification gracelessly.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `register_dismissible_persistent_notice`.
	 *
	 * @param string $message    The notice message. Expected to be escaped if $escape is false.
	 *                           When the message contains HTML, it must start with a <p> tag,
	 *                           or it will be added for you--regardless of proper semantics.
	 * @param string $key        The notice key. Must be unique--prevents double-registering of the notice, and allows for
	 *                           deregistering of the notice.
	 * @param array  $args       {
	 *     The notice creation arguments.
	 *
	 *     @type string $type   Optional. The notification type. Default 'updated'.
	 *     @type bool   $icon   Optional. Whether to enable icon. Default true.
	 *     @type bool   $escape Optional. Whether to escape the $message. Default true.
	 * }
	 * @param array  $conditions {
	 *     The notice output conditions.
	 *
	 *     @type string $capability   Required. The user capability required for the notice to display. Defaults to settings capability.
	 *     @type array  $screens      Optional. The screen bases the notice may be displayed on. When left empty, it'll output on any page.
	 *     @type array  $excl_screens Optional. The screen bases the notice may NOT be displayed on. When left empty, only `screens` applies.
	 *     @type int    $user         Optional. The user ID to display the notice for. Capability will not be ignored.
	 *     @type int    $count        Optional. The number of times the persistent notice may appear (for everyone allowed to see it).
	 *                                Set to -1 for unlimited. When -1, the notice must be removed from display manually.
	 *     @type int    $timeout      Optional. The number of seconds the notice should remain valid for display. Set to -1 to disable check.
	 *                                When the timeout is below -1, then the notification will not be outputted.
	 *                                Do not input non-integer values (such as `false`), for those might cause adverse events.
	 * }
	 */
	public static function register_notice( $message, $key, $args = [], $conditions = [] ) {

		// We made this mistake ourselves. Let's test against it.
		// We can't type $key to scalar, for PHP is dumb with that type.
		if ( ! \is_scalar( $key ) || ! \strlen( $key ) ) return;

		// Sanitize the key so that HTML, JS, and PHP can communicate easily via it.
		$key = \sanitize_key( $key );

		$args += [
			'type'   => 'updated',
			'icon'   => true,
			'escape' => true,
		];

		$conditions += [
			'screens'      => [],
			'excl_screens' => [],
			'capability'   => \THE_SEO_FRAMEWORK_SETTINGS_CAP,
			'user'         => 0,
			'count'        => 1,
			'timeout'      => -1,
		];

		// Required key for security.
		if ( ! $conditions['capability'] ) return;

		// Timeout already expired. Let's not register it.
		if ( $conditions['timeout'] < -1 ) return;

		// Add current time to timeout, so we can compare against it later.
		if ( $conditions['timeout'] > -1 )
			$conditions['timeout'] += time();

		$notices         = Data\Plugin::get_site_cache( 'persistent_notices' ) ?? [];
		$notices[ $key ] = compact( 'message', 'args', 'conditions' );

		Data\Plugin::update_site_cache( 'persistent_notices', $notices );
	}

	/**
	 * Lowers the persistent notice display count.
	 * When the threshold is reached, the notice is deleted.
	 *
	 * @since 4.1.0
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. The second paremeter is no longer passed by reference.
	 *
	 * @param string $key   The notice key.
	 * @param int    $count The number of counts the notice has left.
	 *                      When -1 (permanent notice), nothing happens.
	 */
	public static function count_down_notice( $key, $count ) {

		// Permanent notice.
		if ( $count < 0 ) return;

		--$count;

		if ( ! $count ) {
			static::clear_notice( $key );
		} else {

			$notices = Data\Plugin::get_site_cache( 'persistent_notices' );

			if ( isset( $notices[ $key ]['conditions']['count'] ) ) {
				$notices[ $key ]['conditions']['count'] = $count;
				Data\Plugin::update_site_cache( 'persistent_notices', $notices );
			} else {
				// Notice didn't conform. Remove it.
				static::clear_notice( $key );
			}
		}
	}

	/**
	 * Clears a persistent notice by key.
	 *
	 * @since 4.1.0
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param string $key The notice key.
	 * @return bool True on success, false on failure.
	 */
	public static function clear_notice( $key ) {

		$notices = Data\Plugin::get_site_cache( 'persistent_notices' ) ?? [];

		unset( $notices[ $key ] );

		return Data\Plugin::update_site_cache( 'persistent_notices', $notices );
	}

	/**
	 * Clears all registered persistent notices. Useful after upgrade.
	 *
	 * @since 4.1.0
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function clear_all_notices() {
		return Data\Plugin::update_site_cache( 'persistent_notices', [] );
	}

	/**
	 * Returns the snaitized notice action key.
	 *
	 * @since 4.1.0
	 * @since 4.1.4 1. Now 'public', marked private.
	 *              2. Now uses underscores instead of dashes.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `_get_dismiss_notice_nonce_action`.
	 * @access private
	 *
	 * @param string $key The notice key.
	 * @return string The sanitized nonce action.
	 */
	public static function _get_dismiss_nonce_action( $key ) {
		return \sanitize_key( "tsf-notice-nonce-$key" );
	}

	/**
	 * Outputs registered dismissible persistent notice.
	 *
	 * @since 4.1.0
	 * @since 4.1.2 Now only ignores timeout values of -1 to test against.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `output_dismissible_persistent_notices`.
	 * @access private
	 */
	public static function _output_notices() {

		$notices    = Data\Plugin::get_site_cache( 'persistent_notices' ) ?? [];
		$screenbase = \get_current_screen()->base ?? '';

		// Ideally, we don't want to output more than one on no-js. Alas, we can't anticipate the importance and order of the notices.
		foreach ( $notices as $key => $notice ) {
			$cond = $notice['conditions'];

			if (
				   ! \current_user_can( $cond['capability'] )
				|| ( $cond['user'] && Query::get_current_user_id() !== $cond['user'] )
				|| ( $cond['screens'] && ! \in_array( $screenbase, $cond['screens'], true ) )
				|| ( $cond['excl_screens'] && \in_array( $screenbase, $cond['excl_screens'], true ) )
			) continue;

			if ( -1 !== $cond['timeout'] && $cond['timeout'] < time() ) {
				static::clear_notice( $key );
				continue;
			}

			Template::output_view( 'notice/persistent', $notice['message'], $key, $notice['args'] );

			static::count_down_notice( $key, $cond['count'] );
		}
	}

	/**
	 * Clears persistent notice on user request (clicked Dismiss icon) via the no-JS form.
	 *
	 * @since 4.1.0
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 * @access private
	 */
	public static function _dismiss_notice() {

		// phpcs:ignore, WordPress.Security.NonceVerification.Missing -- We require the POST data to find locally stored nonces.
		$key = \sanitize_key( $_POST['tsf-notice-submit'] ?? '' );

		if ( ! $key ) return;

		$notices = Data\Plugin::get_site_cache( 'persistent_notices' ) ?? [];

		// Notice was deleted already elsewhere, or key was faulty. Either way, ignore--should be self-resolving.
		if ( empty( $notices[ $key ]['conditions']['capability'] ) ) return;

		if (
			   empty( $_POST['tsf_notice_nonce'] )
			|| ! \current_user_can( $notices[ $key ]['conditions']['capability'] )
			|| ! \wp_verify_nonce( $_POST['tsf_notice_nonce'], static::_get_dismiss_nonce_action( $key ) )
		) {
			\wp_die( -1, 403 );
		}

		static::clear_notice( $key );
	}
}
