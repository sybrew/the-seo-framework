<?php
/**
 * @package The_SEO_Framework\Bootstrap\Install
 */

namespace The_SEO_Framework\Bootstrap;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\is_headless;

use \The_SEO_Framework\Helper\Compatibility;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

Compatibility::try_plugin_conflict_notification();

turn_on_autoloading: if ( ! is_headless( 'settings' ) ) {
	// WP 6.4+, turns on auto loading for The SEO Framework's main options.
	if ( \function_exists( 'wp_set_options_autoload' ) ) {
		$options = [];

		if ( false !== \get_option( \THE_SEO_FRAMEWORK_SITE_OPTIONS ) )
			$options[] = \THE_SEO_FRAMEWORK_SITE_OPTIONS;

		if ( false !== \get_option( \THE_SEO_FRAMEWORK_SITE_CACHE ) )
			$options[] = \THE_SEO_FRAMEWORK_SITE_CACHE;

		\wp_set_options_autoload( $options, 'yes' );
	} elseif ( false !== \get_option( \THE_SEO_FRAMEWORK_SITE_OPTIONS ) ) {
		// Turns on auto loading for The SEO Framework's main options.
		$options = \The_SEO_Framework\Data\Plugin::get_options();
		$setting = \THE_SEO_FRAMEWORK_SITE_OPTIONS;

		\remove_all_filters( "pre_update_option_{$setting}" );
		\remove_all_actions( "update_option_{$setting}" );
		\remove_all_filters( "sanitize_option_{$setting}" );

		$temp_options = $options;

		if ( \is_array( $temp_options ) )
			$temp_options['update_buster'] = time();

		$_success = \update_option( $setting, $temp_options, true );
		if ( $_success )
			\update_option( $setting, $options, true );
	}
}
