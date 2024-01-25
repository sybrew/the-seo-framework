<?php
/**
 * @package The_SEO_Framework/Bootstrap\Install
 */

namespace The_SEO_Framework\Bootstrap;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\is_headless;

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

turn_off_autoloading: if ( ! is_headless( 'settings' ) ) {
	// WP 6.4+, turns off auto loading for The SEO Framework's main options.
	if ( \function_exists( 'wp_set_options_autoload' ) ) {
		$options = [];

		if ( false !== \get_option( \THE_SEO_FRAMEWORK_SITE_OPTIONS ) )
			$options[] = \THE_SEO_FRAMEWORK_SITE_OPTIONS;

		if ( false !== \get_option( \THE_SEO_FRAMEWORK_SITE_CACHE ) )
			$options[] = \THE_SEO_FRAMEWORK_SITE_CACHE;

		\wp_set_options_autoload( $options, 'no' );
	}
}
