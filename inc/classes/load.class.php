<?php
/**
 * @package The_SEO_Framework\Classes
 */

namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * @uses interface Debug_Interface
 */
final class Load extends Feed implements Debug_Interface {

	/**
	 * @since 2.2.9
	 * @var bool $the_seo_framework_debug Whether TSF-specific debug is enabled.
	 */
	public $the_seo_framework_debug = false;

	/**
	 * @since 2.2.9
	 * @var bool $the_seo_framework_debug Whether TSF-specific transients are used.
	 */
	public $the_seo_framework_use_transients = true;

	/**
	 * @since 2.2.9
	 * @var bool $script_debug Whether WP script debugging is enabled.
	 */
	public $script_debug = false;

	/**
	 * Constructor, setup debug vars and then load parent constructor.
	 *
	 * @since 2.8.0
	 * @since 3.3.0 Now informs developer of invalid class instancing.
	 *
	 * @return null If called twice or more.
	 */
	public function __construct() {

		if ( _has_run( __METHOD__ ) ) {
			// Don't construct twice, warn developer.
			$this->_doing_it_wrong( __METHOD__, 'Do not instance this class. Use function <code>the_seo_framework()</code> instead.', '3.1.0' );
			return null;
		}

		//= Setup debug vars before initializing anything else.
		$this->init_debug_vars();

		if ( $this->the_seo_framework_debug ) {
			$debug_instance = Debug::get_instance();

			\add_action( 'the_seo_framework_do_before_output', [ $debug_instance, '_set_debug_query_output_cache' ] );
			\add_action( 'admin_footer', [ $debug_instance, '_debug_output' ] );
			\add_action( 'wp_footer', [ $debug_instance, '_debug_output' ] );
		}

		//= Register the capabilities early.
		\add_filter( 'option_page_capability_' . THE_SEO_FRAMEWORK_SITE_OPTIONS, [ $this, 'get_settings_capability' ] );

		/**
		 * @since 2.2.2
		 * @param bool $load_options Whether to show or hide option pages.
		 */
		$this->load_options = (bool) \apply_filters( 'the_seo_framework_load_options', true );

		/**
		 * @since 2.4.3
		 * @since 2.8.0 : Uses method $this->use_object_cache() as default.
		 * @param bool $use_object_cache Whether to enable object caching.
		 */
		$this->use_object_cache = (bool) \apply_filters( 'the_seo_framework_use_object_cache', $this->use_object_cache() );

		//? We always use this, because we need to test whether the sitemap must be outputted.
		$this->pretty_permalinks = '' !== \get_option( 'permalink_structure' );

		//= Load plugin at init 0.
		\add_action( 'init', [ $this, 'init_the_seo_framework' ], 0 );
	}

	/**
	 * Initializes public debug variables for the class to use.
	 *
	 * @since 2.6.0
	 */
	public function init_debug_vars() {

		$this->the_seo_framework_debug = defined( 'THE_SEO_FRAMEWORK_DEBUG' ) && THE_SEO_FRAMEWORK_DEBUG ?: $this->the_seo_framework_debug;
		if ( $this->the_seo_framework_debug ) {
			$instance = \The_SEO_Framework\Debug::_set_instance( $this->the_seo_framework_debug );
		}

		$this->the_seo_framework_use_transients = defined( 'THE_SEO_FRAMEWORK_DISABLE_TRANSIENTS' ) && THE_SEO_FRAMEWORK_DISABLE_TRANSIENTS ? false : $this->the_seo_framework_use_transients;

		//* WP Core definition.
		$this->script_debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ?: $this->script_debug;
	}

	/**
	 * Requires compatibility files which are needed early or on every page.
	 * Mostly requires premium plugins/themes, so we check actual PHP instances,
	 * rather than common paths. As they can require manual FTP upload.
	 *
	 * @since 2.8.0
	 * @since 3.3.0 Renamed to `_load_early_compat_files`, from `load_early_compat_files`
	 * @access private
	 */
	public function _load_early_compat_files() {

		// if ( ! extension_loaded( 'mbstring' ) )
		// $this->_include_compat( 'mbstring', 'php' );

		//* Disable Headway theme SEO.
		\add_filter( 'headway_seo_disabled', '__return_true' );

		if ( $this->is_theme( 'genesis' ) ) {
			//* Genesis Framework
			$this->_include_compat( 'genesis', 'theme' );
		}

		if ( $this->detect_plugin( [ 'constants' => [ 'ICL_LANGUAGE_CODE' ] ] ) ) {
			//* WPML
			$this->_include_compat( 'wpml', 'plugin' );
		}
		if ( $this->detect_plugin( [ 'constants' => [ 'POLYLANG_VERSION' ] ] ) ) {
			//* Polylang
			$this->_include_compat( 'polylang', 'plugin' );
		}

		if ( $this->detect_plugin( [ 'globals' => [ 'ultimatemember' ] ] ) ) {
			//* Ultimate Member
			$this->_include_compat( 'ultimatemember', 'plugin' );
		}
		if ( $this->detect_plugin( [ 'globals' => [ 'bp' ] ] ) ) {
			//* BuddyPress
			$this->_include_compat( 'buddypress', 'plugin' );
		}

		if ( $this->detect_plugin( [ 'functions' => [ 'bbpress' ] ] ) ) {
			//* bbPress
			$this->_include_compat( 'bbpress', 'plugin' );
		} elseif ( $this->detect_plugin( [ 'constants' => [ 'WPFORO_BASENAME' ] ] ) ) {
			//* wpForo
			$this->_include_compat( 'wpforo', 'plugin' );
		}

		if ( $this->detect_plugin( [ 'functions' => [ 'wc' ] ] ) ) {
			//* WooCommerce.
			$this->_include_compat( 'woocommerce', 'plugin' );
		}
	}

	/**
	 * Includes compatibility files.
	 *
	 * @since 2.8.0
	 * @access private
	 * @staticvar array $included Maintains cache of whether files have been loaded.
	 *
	 * @param string $what The vendor/plugin/theme name for the compatibilty.
	 * @param string $type The compatibility type. Be it 'plugin' or 'theme'.
	 * @return bool True on success, false on failure. Files are expected not to return any values.
	 */
	public function _include_compat( $what, $type = 'plugin' ) {

		static $included = [];

		if ( ! isset( $included[ $what ][ $type ] ) )
			$included[ $what ][ $type ] = (bool) require THE_SEO_FRAMEWORK_DIR_PATH_COMPAT . $type . '-' . $what . '.php';

		return $included[ $what ][ $type ];
	}

	/**
	 * Wrapper for function calling through parameters. The golden nugget.
	 *
	 * @since 2.2.2
	 * @since 3.1.0 Is now protected.
	 * @NOTE _doing_it_wrong notices go towards the callback. Unless this
	 *      function is used wrongfully. Then the notice is about this function.
	 *
	 * @param array|string $callback The method array or function string.
	 * @param string       $version  The version of The SEO Framework the function is used.
	 * @param array|string $args     The arguments passed to the function.
	 * @return mixed $output The function called.
	 */
	protected function call_function( $callback, $version = '', $args = [] ) {

		$output = '';

		//? Convert string/object to array
		if ( is_object( $callback ) ) {
			$function = [ $callback, '' ];
		} else {
			$function = (array) $callback;
		}

		//? Convert string/object to array
		if ( is_object( $args ) ) {
			$args = [ $args, '' ];
		} else {
			$args = (array) $args;
		}

		$class  = reset( $function );
		$method = next( $function );

		// Fetch method/function
		if ( ( is_object( $class ) || is_string( $class ) ) && $class && is_string( $method ) && $method ) {
			if ( get_class( $this ) === get_class( $class ) ) {
				if ( method_exists( $this, $method ) ) {
					if ( empty( $args ) ) {
						// In-Object calling.
						$output = call_user_func( [ $this, $method ] );
					} else {
						// In-Object calling.
						$output = call_user_func_array( [ $this, $method ], $args );
					}
				} else {
					$this->_inaccessible_p_or_m( \esc_html( get_class( $class ) . '->' . $method . '()' ), 'Class or Method not found.', \esc_html( $version ) );
				}
			} else {
				if ( method_exists( $class, $method ) ) {
					if ( empty( $args ) ) {
						$output = call_user_func( [ $class, $method ] );
					} else {
						$output = call_user_func_array( [ $class, $method ], $args );
					}
				} else {
					$this->_inaccessible_p_or_m( \esc_html( get_class( $class ) . '::' . $method . '()' ), 'Class or Method not found.', \esc_html( $version ) );
				}
			}
		} elseif ( is_string( $class ) && $class ) {
			//* Class is function.
			$func = $class;

			if ( empty( $args ) ) {
				$output = call_user_func( $func );
			} else {
				$output = call_user_func_array( $func, $args );
			}
		} else {
			$this->_doing_it_wrong( __METHOD__, 'Function needs to be called as a string.', \esc_html( $version ) );
		}

		return $output;
	}

	/**
	 * Mark a filter as deprecated and inform when it has been used.
	 *
	 * @since 2.8.0
	 * @see $this->_deprecated_function().
	 * @access private
	 *
	 * @param string $filter      The function that was called.
	 * @param string $version     The version of WordPress that deprecated the function.
	 * @param string $replacement Optional. The function that should have been called. Default null.
	 */
	public function _deprecated_filter( $filter, $version, $replacement = null ) {
		Debug::get_instance()->_deprecated_filter( $filter, $version, $replacement );
	}

	/**
	 * Mark a function as deprecated and inform when it has been used.
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
	public function _deprecated_function( $function, $version, $replacement = null ) { // phpcs:ignore -- invalid xss warning
		Debug::get_instance()->_deprecated_function( $function, $version, $replacement ); // phpcs:ignore -- invalid xss warning
	}

	/**
	 * Mark a function as deprecated and inform when it has been used.
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
	public function _doing_it_wrong( $function, $message, $version = null ) { // phpcs:ignore -- invalid xss warning
		Debug::get_instance()->_doing_it_wrong( $function, $message, $version ); // phpcs:ignore -- invalid xss warning
	}

	/**
	 * Mark a property or method inaccessible when it has been used.
	 * The current behavior is to trigger a user error if WP_DEBUG is true.
	 *
	 * @since 2.7.0
	 * @access private
	 *
	 * @param string $p_or_m  The Property or Method.
	 * @param string $message A message explaining what has been done incorrectly.
	 */
	public function _inaccessible_p_or_m( $p_or_m, $message = '' ) {
		Debug::get_instance()->_inaccessible_p_or_m( $p_or_m, $message );
	}
}
