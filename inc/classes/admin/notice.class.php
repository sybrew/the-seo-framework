<?php
/**
 * @package The_SEO_Framework\Classes\Admin\Notice
 * @subpackage The_SEO_Framework\Admin\Notice
 */

namespace The_SEO_Framework\Admin;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Admin; // Yes, it is legal to share class and namespace.

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
 * Holds simple persistent notices functionality.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->admin()->notice() instead.
 */
class Notice {

	/**
	 * Prints a generated dismissible notice.
	 *
	 * @since 2.7.0
	 * @since 4.1.2 Added the $inline parameter.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Shifted all second and later parameter into `$args`.
	 *              3. Renamed from `do_dismissible_notice`.
	 *
	 * @param string $message The notice message. Expected to be escaped if `$args['escape']` is `false`.
	 *                        When the message contains HTML, it must start with a <p> tag,
	 *                        or it will be added for you--regardless of proper semantics.
	 * @param array  $args    {
	 *     The notice creation arguments.
	 *
	 *     @type string $type   Optional. The notification type. Accepts 'updated', 'warning', 'info', and 'error'.
	 *                          Default 'updated'.
	 *     @type bool   $icon   Optional. Whether to enable icon. Default true.
	 *     @type bool   $escape Optional. Whether to escape the $message. Default true.
	 *     @type bool   $inline Optional. Whether to escape the whole output. Default false.
	 * }
	 */
	public static function output_notice( $message, $args ) {
		// phpcs:ignore, WordPress.Security.EscapeOutput -- use $escape
		echo static::generate_notice( $message, $args );
	}

	/**
	 * Generates dismissible notice.
	 * Also loads scripts and styles if out of The SEO Framework's context.
	 *
	 * @since 2.6.0
	 * @since 3.0.6 The messages are no longer auto-styled to "strong".
	 * @since 4.0.0 Added a tabindex, so keyboard navigation is possible on the "empty" dashicon.
	 * @since 4.0.3 1. Keyboard navigation is now supported on the dismiss icon.
	 *              2. The info notice type is now supported.
	 * @since 4.1.0 Now semantically wraps the content with HTML.
	 * @since 4.1.2 1. No longer invokes the script loader during AJAX-requests.
	 *              2. Now accepts empty messages, so that AJAX-invoked generators can grab a notice wrapper.
	 *              3. Added the inline parameter.
	 *              4. Now enqueues scripts in the footer, so templates won't spam the header.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Shifted all second and later parameter into `$args`.
	 *              3. Renamed from `generate_dismissible_notice`.
	 *
	 * @param string $message The notice message. Expected to be escaped if `$args['escape']` is `false`.
	 *                        When the message contains HTML, it must start with a <p> tag,
	 *                        or it will be added for you--regardless of proper semantics.
	 * @param array  $args    {
	 *     The notice creation arguments.
	 *
	 *     @type string $type   Optional. The notification type. Accepts 'updated', 'warning', 'info', and 'error'.
	 *                          Default 'updated'.
	 *     @type bool   $icon   Optional. Whether to enable icon. Default true.
	 *     @type bool   $escape Optional. Whether to escape the $message. Default true.
	 *     @type bool   $inline Optional. Whether to escape the whole output. Default false.
	 * }
	 * @return string The dismissible error notice.
	 */
	public static function generate_notice( $message, $args = [] ) {

		if ( ! \wp_doing_ajax() ) {
			// Make sure the scripts are loaded.
			Admin\Script\Registry::register_scripts_and_hooks();
			Admin\Script\Registry::footer_enqueue();
		}

		$args += [
			'type'   => 'updated',
			'icon'   => true,
			'escape' => true,
			'inline' => false,
		];

		switch ( $args['type'] ) {
			case 'warning':
			case 'info':
				$args['type'] = "notice-{$args['type']}";
		}

		return vsprintf(
			'<div class="notice %s tsf-notice %s %s">%s%s</div>',
			[
				\esc_attr( $args['type'] ),
				$args['icon'] ? 'tsf-show-icon' : '',
				$args['inline'] ? 'inline' : '',
				\sprintf(
					! $args['escape'] && 0 === stripos( $message, '<p' )
						? '%s'
						: '<p>%s</p>',
					$args['escape'] ? \esc_html( $message ) : $message,
				),
				\sprintf(
					'<a class="hide-if-no-tsf-js tsf-dismiss" href="javascript:;" title="%s"></a>',
					\esc_attr__( 'Dismiss this notice', 'default' )
				),
			],
		);
	}
}
