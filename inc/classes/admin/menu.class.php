<?php
/**
 * @package The_SEO_Framework\Classes\Admin\Menu
 */

namespace The_SEO_Framework\Admin;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\{
	memo,
	has_run,
	is_headless,
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
 * Prepares the TSF menu interfaces.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->admin()->menu() instead.
 */
class Menu {

	/**
	 * Adds menu links under "settings" in the wp-admin dashboard
	 *
	 * @since 2.2.2
	 * @since 2.9.2 Added static cache so the method can only run once.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `add_menu_link`.
	 *
	 * @return void Early if method is already called.
	 */
	public static function register_top_menu_page() {

		if ( has_run( __METHOD__ ) ) return;

		$menu = static::get_top_menu_args();

		\add_menu_page(
			$menu['page_title'],
			$menu['menu_title'],
			$menu['capability'],
			$menu['menu_slug'],
			$menu['callback'],
			$menu['icon'],
			$menu['position'],
		);

		/**
		 * Simply copy the previous, but rename the submenu entry.
		 * The function add_submenu_page() takes care of the duplications.
		 */
		\add_submenu_page(
			$menu['menu_slug'],
			$menu['page_title'],
			$menu['page_title'],
			$menu['capability'],
			$menu['menu_slug'],
			$menu['callback'],
		);

		/**
		 * Register the meta boxes early, otherwise we cannot toggle them via Screen Options.
		 * This is "temporary," in v6.0 we'll remove this feature and show a better interface.
		 */
		if ( \current_user_can( $menu['capability'] ) )
			\add_action(
				'load-' . static::get_page_hook_name(),
				[ Settings\Plugin::class, 'register_seo_settings_meta_boxes' ]
			);
	}

	/**
	 * @since 5.0.0
	 *
	 * @return array The top menu page arguments.
	 */
	public static function get_top_menu_args() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo() ) return $memo;

		$issue_count = static::get_top_menu_issue_count();

		/**
		 * @since 4.2.8
		 * @param array $args The menu arguments. All indexes must be maintained.
		 */
		return memo( \apply_filters(
			'the_seo_framework_top_menu_args',
			[
				'page_title' => \esc_html__( 'SEO Settings', 'autodescription' ),
				'menu_title' => \esc_html__( 'SEO', 'autodescription' )
					. ( $issue_count ? static::get_issue_badge( $issue_count ) : '' ),
				'capability' => \THE_SEO_FRAMEWORK_SETTINGS_CAP,
				'menu_slug'  => \THE_SEO_FRAMEWORK_SITE_OPTIONS_SLUG,
				'callback'   => [ Settings\Plugin::class, 'prepare_settings_wrap' ],
				'icon'       => 'dashicons-search',
				'position'   => '90.9001',
			],
		) );
	}

	/**
	 * @since 5.0.0
	 *
	 * @param string $submenu The submenu to get. If it's empty, it'll get TSF's main page hook.
	 * @return string TSF's menu page hook name or its submenu hook name.
	 */
	public static function get_page_hook_name( $submenu = '' ) {

		static $names = [];

		if ( $submenu ) {
			return $names[ $submenu ] ??= \get_plugin_page_hookname(
				$submenu,
				static::get_top_menu_args()['menu_slug'],
			);
		}

		return $names[''] ??= \get_plugin_page_hookname(
			static::get_top_menu_args()['menu_slug'],
			'',
		);
	}

	/**
	 * Returns the number of issues registered.
	 * Always returns 0 when the settings are headless.
	 *
	 * @since 4.2.8
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `get_admin_issue_count`.
	 *
	 * @return int The registered issue count.
	 */
	public static function get_top_menu_issue_count() {

		if ( is_headless( 'settings' ) ) return 0;

		/**
		 * @since 4.2.8
		 * @param int The issue count. Don't overwrite, but increment it!
		 */
		return memo() ?? memo( \absint( \apply_filters( 'the_seo_framework_top_menu_issue_count', 0 ) ) );
	}

	/**
	 * Returns formatted text for the notice count to be displayed in the admin menu as a number.
	 *
	 * @since 4.2.8
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `get_issue_badge`.
	 *
	 * @param int $issue_count The issue count.
	 * @return string The issue count badge.
	 */
	public static function get_issue_badge( $issue_count ) {

		$notice_i18n = \number_format_i18n( $issue_count );

		return ' ' . \sprintf(
			'<span class="tsf-menu-issue menu-counter count-%d"><span class=tsf-menu-issue-text aria-hidden=true>%s</span><span class=screen-reader-text>%s</span></span>',
			$issue_count,
			$notice_i18n,
			\sprintf(
				/* translators: %s: number of issues waiting */
				\_n( '%s issue waiting', '%s issues waiting', $issue_count, 'autodescription' ),
				$notice_i18n,
			)
		);
	}
}
