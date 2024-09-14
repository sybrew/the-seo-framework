<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Load
 *
 * This is the main file called.
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\is_headless;

use \The_SEO_Framework\Data;

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

/**
 * Facade final class The_SEO_Framework\Load
 *
 * This class is unused internally, but is part of the public API.
 * It is accessible via function `tsf()`.
 * Do not call this class directly.
 *
 * @since 2.8.0
 * @since 4.0.0 No longer implements an interface. It's implied.
 * @since 4.1.0 Now extends `Cache` instead of `Feed`.
 * @since 4.1.4 Removed protected property $use_object_cache.
 * @since 5.0.0 1. Deprecated $inpost_nonce_field
 *              2. Deprecated $inpost_nonce_name
 *              3. Deprecated $is_headless
 *              4. Deprecated $loaded
 *              5. Deprecated $lpretty_permalinksoaded
 *              6. Deprecated $script_debug
 *              7. Deprecated $seo_settings_page_slug
 *              8. Deprecated $seo_settings_page_hook
 *              9. Deprecated $the_seo_framework_debug
 *              10. Deprecated $the_seo_framework_use_transients
 * @api
 */
final class Load extends Pool {

	/**
	 * @since 5.0.0
	 * @var \The_SEO_Framework\Load This instance.
	 */
	private static $instance;

	/**
	 * Returns the current and only instance -- sets it up if it doesn't exist.
	 *
	 * @since 5.0.0
	 * @access private
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
	 * @since 5.0.0 Is now protected. Use `tsf()` or `the_seo_framework()` instead.
	 */
	protected function __construct() { }

	/**
	 * Handles unapproachable invoked properties.
	 *
	 * Makes sure deprecated properties are still overwritten.
	 * If the property never existed, default PHP behavior is invoked.
	 *
	 * @since 2.8.0
	 * @since 3.2.2 This method no longer allows to overwrite protected or private variables.
	 * @since 5.0.0 Now protects against fatal errors on PHP 8.2 or later.
	 *
	 * @param string $name  The property name.
	 * @param mixed  $value The property value.
	 */
	public function __set( $name, $value ) {

		switch ( $name ) {
			case 'the_seo_framework_debug':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 5.0.0; set constant THE_SEO_FRAMEWORK_DEBUG' );
				return false;
			case 'script_debug':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 5.0.0; set constant SCRIPT_DEBUG' );
				return false;
			case 'seo_settings_page_slug':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 5.0.0; set constant THE_SEO_FRAMEWORK_SITE_OPTIONS_SLUG' );
				return false;
		}

		$this->_inaccessible_p_or_m( "tsf()->$name", 'unknown' );

		// Invoke default behavior: Write variable if it's not protected.
		if ( property_exists( $this, $name ) )
			$this->$name = $value;
	}

	/**
	 * Handles unapproachable invoked properties.
	 *
	 * Makes sure deprecated properties are still accessible.
	 *
	 * @since 2.7.0
	 * @since 3.1.0 Removed known deprecations.
	 * @since 3.2.2 This method no longer invokes PHP errors, nor returns protected values.
	 * @since 5.0.0 Removed 'load_option' deprecation.
	 *
	 * @param string $name The property name.
	 * @return mixed
	 */
	public function __get( $name ) {

		switch ( $name ) {
			case 'inpost_nonce_field':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 5.0.0; you should make your own.' );
				return Data\Admin\Post::$nonce_action;
			case 'inpost_nonce_name':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 5.0.0; you should make your own.' );
				return Data\Admin\Post::$nonce_name;
			case 'is_headless':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 5.0.0; use function \The_SEO_Framework\is_headless()' );
				return is_headless();
			case 'loaded':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 5.0.0; you may drop the loaded check.' );
				return true;
			case 'pretty_permalinks':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 5.0.0; use tsf()->query()->utils()->using_pretty_permalinks()' );
				return $this->query()->utils()->using_pretty_permalinks();
			case 'script_debug':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 5.0.0; use constant SCRIPT_DEBUG' );
				return \SCRIPT_DEBUG;
			case 'seo_settings_page_slug':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 5.0.0; use constant THE_SEO_FRAMEWORK_SITE_OPTIONS_SLUG or tsf()->admin()->menu()->get_top_menu_args()' );
				return \THE_SEO_FRAMEWORK_SITE_OPTIONS_SLUG;
			case 'seo_settings_page_hook':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 5.0.0; use `tsf()->admin()->menu()->get_page_hook_name()` instead.' );
				return Admin\Menu::get_page_hook_name();
			case 'the_seo_framework_debug':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 5.0.0; use constant THE_SEO_FRAMEWORK_DEBUG' );
				return \THE_SEO_FRAMEWORK_DEBUG;
			case 'the_seo_framework_use_transients':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 5.0.0; with no alternative available' );
				return true;
		}

		$this->_inaccessible_p_or_m( "tsf()->$name", 'unknown' );
	}

	/**
	 * Handles unapproachable invoked methods.
	 *
	 * @since 2.7.0
	 *
	 * @param string $name      The method name.
	 * @param array  $arguments The method arguments.
	 * @return mixed|void
	 */
	public function __call( $name, $arguments ) {

		static $depr_class;

		$depr_class ??= new Internal\Deprecated;

		if ( \is_callable( [ $depr_class, $name ] ) )
			return \call_user_func_array( [ $depr_class, $name ], $arguments );

		$this->_inaccessible_p_or_m( "tsf()->$name()" );
	}

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
