<?php
/**
 * @package The_SEO_Framework\Classes\Admin\Utils
 */

namespace The_SEO_Framework\Admin;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Helper\Format\Markdown;

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
 * Holds various Utility methods for TSF admin.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->admin()->utils() instead.
 */
class Utils {

	/**
	 * Redirect the user to an admin page, and add query args to the URL string
	 * for alerts, etc.
	 *
	 * @since 2.2.2
	 * @since 2.9.2 Added user-friendly exception handling.
	 * @since 2.9.3 1. Query arguments work again (regression 2.9.2).
	 *              2. Now only accepts http and https protocols.
	 * @since 4.2.0 Now allows query arguments with value 0|'0'.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `admin_redirect`.
	 *              3. Now bails with an explanatory error when the page doesn't exist.
	 * @TODO WP 5.2/5.4+ will cause this method to never run on wp_die().
	 *       We should further investigate the cause and remove WP's blockade. This is a corner-case, however.
	 *
	 * @param string $page_hook  Menu slug. This slug must exist, or the redirect will loop back to the current page.
	 * @param array  $query_args Optional. Associative array of query string arguments (key => value).
	 *                           Default is an empty array.
	 * @return null Return early if first argument is false.
	 */
	public static function redirect( $page_hook, $query_args = [] ) {

		if ( empty( $page_hook ) ) return;

		// menu_page_url() always uses esc_url() for display, breaking ampersands. Undo that via html_entity_decode()
		$url = html_entity_decode( \menu_page_url( $page_hook, false ) );

		$url or exit( 'Redirect error: Page not found' );

		$target = \sanitize_url(
			\add_query_arg( array_filter( $query_args, 'strlen' ), $url ),
			[ 'https', 'http' ],
		);

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
					\sprintf(
						/* translators: %s = Redirect URL markdown */
						\esc_html__( 'There has been an error redirecting. Refresh the page or follow [this link](%s).', 'autodescription' ),
						\esc_url( $target ),
					),
					[ 'a' ],
					[ 'a_internal' => true ],
				),
			);
		}

		exit;
	}

	/**
	 * Whether to display Extension Manager suggestions to the user based on several conditions.
	 *
	 * @since 4.2.4
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `_display_extension_suggestions`.
	 * @uses TSF_DISABLE_SUGGESTIONS Set that to true if you don't like us.
	 *
	 * @return bool
	 */
	public static function display_extension_suggestions() {
		return \current_user_can( 'install_plugins' )
			&& ! ( \defined( 'TSF_DISABLE_SUGGESTIONS' ) && \TSF_DISABLE_SUGGESTIONS );
	}
}
