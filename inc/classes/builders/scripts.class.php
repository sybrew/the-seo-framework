<?php
/**
 * @package The_SEO_Framework\Classes\Builders\Scripts
 * @subpackage The_SEO_Framework\Scripts
 */

namespace The_SEO_Framework\Builders;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2018 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

\tsf()->_deprecated_function( 'The_SEO_Framework\Builders\Scripts', '5.0.0', 'The_SEO_Framework\Admin\Script\Registry' );
/**
 * Registers and outputs admin GUI scripts. Auto-invokes everything the moment
 * this file is required.
 * Relies on \WP_Dependencies to prevent duplicate loading, and autoloading.
 *
 * This handles admin-ONLY scripts for now.
 *
 * @since 3.1.0
 * @since 5.0.0 1. Moved to \The_SEO_Framework\Admin\Script\Registry
 *              2. Deprecated.
 * @deprecated
 * @ignore
 */
class Scripts extends \The_SEO_Framework\Admin\Script\Registry {
	/**
	 * Prepares the class and loads constructor.
	 *
	 * Use this if the actions need to be registered early, but nothing else of
	 * this class is needed yet.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @ignore
	 * @deprecated
	 */
	public static function prepare() {
		static::register_scripts_and_hooks();
	}

	/**
	 * Verifies template view inclusion secret.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @ignore
	 * @deprecated
	 *
	 * @example template file header:
	 * `defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and The_SEO_Framework\Builders\Scripts::verify( $_secret ) or die;`
	 *
	 * @param string $secret The passed secret.
	 * @return bool True on success, false on failure.
	 */
	public static function verify( $secret ) { // phpcs:ignore, deprecated.
		// \The_SEO_Framework\Helper\Template::verify_secret( $secret );
		// They've been given $_secret, not $secret. This is not a security issue.
		// For an explanation, see `\The_SEO_Framework\Template::output_view()`.
		return true;
	}
}
