<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Load
 *
 * This is the main file called.
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

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
 * Facade final class The_SEO_Framework\Load
 *
 * Extending upon parent classes.
 *
 * @since 2.8.0
 * @since 4.0.0 No longer implements an interface. It's implied.
 * @since 4.1.0 Now extends `Cache` instead of `Feed`.
 * @since 4.1.4 Removed protected property $use_object_cache.
 */
final class Load extends Sanitize {

	/**
	 * @since 4.3.0
	 * @var \The_SEO_Framework\Load This instance.
	 */
	private static $instance;

	/**
	 * Instance getter.
	 *
	 * @since 4.3.0
	 *
	 * @return null If called twice or more.
	 */
	public static function get_instance() {
		return static::$instance ??= new self;
	}

	/**
	 * Constructor, does nothing useful anymore.
	 *
	 * @since 2.8.0
	 * @since 4.0.0 Now informs developer of invalid class instancing.
	 * @since 4.1.4.Now constructs headlessness.
	 * @since 4.3.0 Is now protected. Use `tsf()` or `the_seo_framework()` instead.
	 */
	protected function __construct() { }

	/**
	 * Marks a function as deprecated and inform when it has been used.
	 * Taken from WordPress core, but added extra parameters and linguistic alterations.
	 * The current behavior is to trigger a user error if WP_DEBUG is true.
	 *
	 * @since 2.6.0
	 * @access private
	 *
	 * @param string $function    The function that was called.
	 * @param string $version     The version of WordPress that deprecated the function.
	 * @param string $replacement Optional. The function that should have been called. Default null.
	 */
	public function _deprecated_function( $function, $version, $replacement = null ) { // phpcs:ignore -- Wrong asserts, copied method name.
		// phpcs:ignore -- Wrong asserts, copied method name.
		Internal\Debug::_deprecated_function( $function, $version, $replacement );
	}

	/**
	 * Marks a function as deprecated and inform when it has been used.
	 * Taken from WordPress core, but added extra parameters and linguistic alterations.
	 * The current behavior is to trigger a user error if WP_DEBUG is true.
	 *
	 * @since 2.6.0
	 * @access private
	 *
	 * @param string $function The function that was called.
	 * @param string $message  A message explaining what has been done incorrectly.
	 * @param string $version  The version of WordPress where the message was added.
	 */
	public function _doing_it_wrong( $function, $message, $version = null ) { // phpcs:ignore -- Wrong asserts, copied method name.
		// phpcs:ignore -- Wrong asserts, copied method name.
		Internal\Debug::_doing_it_wrong( $function, $message, $version );
	}

	/**
	 * Marks a property or method inaccessible when it has been used.
	 * The current behavior is to trigger a user error if WP_DEBUG is true.
	 *
	 * @since 2.7.0
	 * @access private
	 *
	 * @param string $p_or_m  The Property or Method.
	 * @param string $message A message explaining what has been done incorrectly.
	 * @param string $handle  The method handler.
	 */
	public function _inaccessible_p_or_m( $p_or_m, $message = '', $handle = 'tsf()' ) {
		Internal\Debug::_inaccessible_p_or_m( $p_or_m, $message, $handle );
	}
}
