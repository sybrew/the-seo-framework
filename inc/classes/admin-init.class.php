<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Admin_Init
 * @subpackage The_SEO_Framework\Admin
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\is_headless;

use \The_SEO_Framework\Helper\{
	Format\Markdown,
	Post_Types,
	Query,
	Taxonomies,
};
use \The_SEO_Framework\Data;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\Admin_Init
 *
 * Initializes the plugin for the wp-admin screens.
 * Enqueues CSS and Javascript.
 *
 * @since 2.8.0
 */
class Admin_Init extends Pool {

	/**
	 * Redirect the user to an admin page, and add query args to the URL string
	 * for alerts, etc.
	 *
	 * @since 2.2.2
	 * @since 2.9.2 Added user-friendly exception handling.
	 * @since 2.9.3 1. Query arguments work again (regression 2.9.2).
	 *              2. Now only accepts http and https protocols.
	 * @since 4.2.0 Now allows query arguments with value 0|'0'.
	 * @TODO WP 5.2/5.4 will cause this method to never run on wp_die().
	 *       We should further investigate the cause and remove WP's blockade. This is a corner-case, however.
	 *
	 * @param string $page Menu slug. This slug must exist, or the redirect will loop back to the current page.
	 * @param array  $query_args Optional. Associative array of query string arguments
	 *               (key => value). Default is an empty array.
	 * @return null Return early if first argument is false.
	 */
	public function admin_redirect( $page, $query_args = [] ) { // TODO make redirect_to_admin_page

		if ( empty( $page ) ) return;

		// This can be empty... so $target will be empty. TODO test for $success and bail?
		// Might cause security issues... we _must_ exit, always? Show warning?
		$url = html_entity_decode( \menu_page_url( $page, false ) );

		$target = \add_query_arg( array_filter( $query_args, 'strlen' ), $url );
		$target = \sanitize_url( $target, [ 'https', 'http' ] );

		// Predict white screen:
		$headers_sent = headers_sent();

		\wp_safe_redirect( $target, 302 );

		// White screen of death for non-debugging users. Let's make it friendlier.
		if ( $headers_sent && $target ) {
			// Test if WordPress's redirect header is sent. Bail if true.
			if ( \in_array(
				'Location: ' . \wp_sanitize_redirect( $target ),
				headers_list(),
				true
			) ) exit;

			// phpcs:disable, WordPress.Security.EscapeOutput -- convert_markdown escapes. Added esc_url() for sanity.
			printf(
				'<p><strong>%s</strong></p>',
				Markdown::convert(
					sprintf(
						/* translators: %s = Redirect URL markdown */
						\esc_html__( 'There has been an error redirecting. Refresh the page or follow [this link](%s).', 'autodescription' ),
						\esc_url( $target )
					),
					[ 'a' ],
					[ 'a_internal' => true ]
				)
			);
		}

		exit;
	}

	/**
	 * Registers dismissible persistent notice, that'll respawn during page load until dismissed or otherwise expired.
	 *
	 * @since 4.1.0
	 * @since 4.1.3 Now handles timeout values below -1 gracefully, by purging the whole notification gracelessly.
	 * @uses $this->generate_dismissible_persistent_notice()
	 *
	 * @param string $message    The notice message. Expected to be escaped if $escape is false.
	 *                           When the message contains HTML, it must start with a <p> tag,
	 *                           or it will be added for you--regardless of proper semantics.
	 * @param string $key        The notice key. Must be unique--prevents double-registering of the notice, and allows for
	 *                           deregistering of the notice.
	 * @param array  $args       : {
	 *    'type'   => string Optional. The notification type. Default 'updated'.
	 *    'icon'   => bool   Optional. Whether to enable icon. Default true.
	 *    'escape' => bool   Optional. Whether to escape the $message. Default true.
	 * }
	 * @param array  $conditions : {
	 *     'capability'   => string Required. The user capability required for the notice to display. Defaults to settings capability.
	 *     'screens'      => array  Optional. The screen bases the notice may be displayed on. When left empty, it'll output on any page.
	 *     'excl_screens' => array  Optional. The screen bases the notice may NOT be displayed on. When left empty, only `screens` applies.
	 *     'user'         => int    Optional. The user ID to display the notice for. Capability will not be ignored.
	 *     'count'        => int    Optional. The number of times the persistent notice may appear (for everyone allowed to see it).
	 *                              Set to -1 for unlimited. When -1, the notice must be removed from display manually.
	 *     'timeout'      => int    Optional. The number of seconds the notice should remain valid for display. Set to -1 to disable check.
	 *                              When the timeout is below -1, then the notification will not be outputted.
	 *                              Do not input non-integer values (such as `false`), for those might cause adverse events.
	 * }
	 */
	public function register_dismissible_persistent_notice( $message, $key, $args = [], $conditions = [] ) {

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
	 *
	 * @param string $key   The notice key.
	 * @param int    $count The number of counts the notice has left. Passed by reference.
	 *                      When -1 (permanent notice), nothing happens.
	 */
	public function count_down_persistent_notice( $key, &$count ) {

		$_count_before = $count;

		if ( $count > 0 )
			--$count;

		if ( ! $count ) {
			$this->clear_persistent_notice( $key );
		} elseif ( $_count_before !== $count ) {
			$notices = Data\Plugin::get_site_cache( 'persistent_notices' );
			if ( isset( $notices[ $key ]['conditions']['count'] ) ) {
				$notices[ $key ]['conditions']['count'] = $count;
				Data\Plugin::update_site_cache( 'persistent_notices', $notices );
			} else {
				// Notice didn't conform. Remove it.
				$this->clear_persistent_notice( $key );
			}
		}
	}

	/**
	 * Clears a persistent notice by key.
	 *
	 * @since 4.1.0
	 *
	 * @param string $key The notice key.
	 * @return bool True on success, false on failure.
	 */
	public function clear_persistent_notice( $key ) {

		$notices = Data\Plugin::get_site_cache( 'persistent_notices' ) ?? [];

		unset( $notices[ $key ] );

		return Data\Plugin::update_site_cache( 'persistent_notices', $notices );
	}

	/**
	 * Clears all registered persistent notices. Useful after upgrade.
	 *
	 * @since 4.1.0
	 *
	 * @return bool True on success, false on failure.
	 */
	public function clear_all_persistent_notices() {
		return Data\Plugin::update_site_cache( 'persistent_notices', [] );
	}

	/**
	 * Returns the snaitized notice action key.
	 *
	 * @since 4.1.0
	 * @since 4.1.4 1. Now 'public', marked private.
	 *              2. Now uses underscores instead of dashes.
	 * @access private
	 *
	 * @param string $key The notice key.
	 * @return string The sanitized nonce action.
	 */
	public function _get_dismiss_notice_nonce_action( $key ) {
		return \sanitize_key( "tsf_notice_nonce_$key" );
	}

	/**
	 * Clears persistent notice on user request (clicked Dismiss icon) via the no-JS form.
	 *
	 * @since 4.1.0
	 * var_dump() equalize with AJAX::dismiss_notice() and combine
	 */
	public function _dismiss_notice() {

		// phpcs:ignore, WordPress.Security.NonceVerification.Missing -- We require the POST data to find locally stored nonces.
		$key = \sanitize_key( $_POST['tsf-notice-submit'] ?? '' );

		if ( ! $key ) return;

		$notices = Data\Plugin::get_site_cache( 'persistent_notices' );

		// Notice was deleted already elsewhere, or key was faulty. Either way, ignore--should be self-resolving.
		if ( empty( $notices[ $key ]['conditions']['capability'] ) ) return;

		if (
			   ! \current_user_can( $notices[ $key ]['conditions']['capability'] )
			|| ! \wp_verify_nonce( $_POST['tsf_notice_nonce'] ?? '', $this->_get_dismiss_notice_nonce_action( $key ) )
		) {
			\wp_die( -1, 403 );
		}

		$this->clear_persistent_notice( $key );
	}
}
