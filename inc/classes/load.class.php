<?php
/**
 * @package The_SEO_Framework\Classes
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
	 * Cached debug/profile properties. Initialized on plugins_loaded priority 5.
	 *
	 * @since 2.2.9
	 *
	 * @var bool Whether debug is enabled.
	 * @var bool Whether debug is hidden in HTMl.
	 * @var bool Whether transients are enabled.
	 * @var bool Whether script debugging is enabled.
	 */
	public $the_seo_framework_debug = false,
	       $the_seo_framework_debug_hidden = false,
	       $the_seo_framework_use_transients = true,
	       $script_debug = false;

	/**
	 * Constructor, setup debug vars and then load parent constructor.
	 *
	 * @staticvar int $count Prevents duplicated constructor loading.
	 * @return null If called twice or more.
	 */
	public function __construct() {

		static $count = 0;

		if ( $count > 0 ) {
			return null;
		}

		$count++;

		//* Setup debug vars before initializing parents.
		$this->init_debug_vars();

		parent::__construct();
	}

	/**
	 * Initializes public debug variables for the class to use.
	 *
	 * @since 2.6.0
	 */
	public function init_debug_vars() {

		$this->the_seo_framework_debug = defined( 'THE_SEO_FRAMEWORK_DEBUG' ) && THE_SEO_FRAMEWORK_DEBUG ?: $this->the_seo_framework_debug;
		if ( $this->the_seo_framework_debug ) {
			//* No need to set these to true if no debugging is enabled.
			$this->the_seo_framework_debug_hidden = defined( 'THE_SEO_FRAMEWORK_DEBUG_HIDDEN' ) && THE_SEO_FRAMEWORK_DEBUG_HIDDEN ?: $this->the_seo_framework_debug_hidden;

			$instance = \The_SEO_Framework\Debug::set_instance( $this->the_seo_framework_debug, $this->the_seo_framework_debug_hidden );
		}

		$this->the_seo_framework_use_transients = defined( 'THE_SEO_FRAMEWORK_DISABLE_TRANSIENTS' ) && THE_SEO_FRAMEWORK_DISABLE_TRANSIENTS ? false : $this->the_seo_framework_use_transients;

		//* WP Core definition.
		$this->script_debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ?: $this->script_debug;
	}

	/**
	 * Wrapper for function calling through parameters. The golden nugget.
	 *
	 * @since 2.2.2
	 * @access private
	 * @NOTE _doing_it_wrong notices go towards the callback. Unless this
	 *      function is used wrongfully. Then the notice is about this function.
	 *
	 * @param array|string $callback the method array or function string.
	 * @param string $version the version of The SEO Framework the function is used.
	 * @param array|string $args The arguments passed to the function.
	 * @return mixed $output The function called.
	 */
	public function call_function( $callback, $version = '', $args = array() ) {

		$output = '';

		/**
		 * Convert string/object to array
		 */
		if ( is_object( $callback ) ) {
			$function = array( $callback, '' );
		} else {
			$function = (array) $callback;
		}

		/**
		 * Convert string/object to array
		 */
		if ( is_object( $args ) ) {
			$args = array( $args, '' );
		} else {
			$args = (array) $args;
		}

		$class = reset( $function );
		$method = next( $function );

		/**
		 * Fetch method/function
		 */
		if ( ( is_object( $class ) || is_string( $class ) ) && $class && is_string( $method ) && $method ) {
			if ( get_class( $this ) === get_class( $class ) ) {
				if ( method_exists( $this, $method ) ) {
					if ( empty( $args ) ) {
						// In-Object calling.
						$output = call_user_func( array( $this, $method ) );
					} else {
						// In-Object calling.
						$output = call_user_func_array( array( $this, $method ), $args );
					}
				} else {
					$this->_inaccessible_p_or_m( \esc_html( get_class( $class ) . '->' . $method . '()' ), 'Class or Method not found.', \esc_html( $version ) );
				}
			} else {
				if ( method_exists( $class, $method ) ) {
					if ( empty( $args ) ) {
						$output = call_user_func( array( $class, $method ) );
					} else {
						$output = call_user_func_array( array( $class, $method ), $args );
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
	public function _deprecated_function( $function, $version, $replacement = null ) {
		Debug::get_instance()->_deprecated_function( $function, $version, $replacement );
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
	public function _doing_it_wrong( $function, $message, $version = null ) {
		Debug::get_instance()->_doing_it_wrong( $function, $message, $version );
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
	/**
	 * Debug init. Simplified way of debugging a function, only works in admin.
	 *
	 * @since 2.6.0
	 * @access private
	 *
	 * @param string $method The function name.
	 * @param bool $store Whether to store the output in cache for next run to pick up on.
	 * @param double $debug_key Use $debug_key as variable, it's reserved.
	 * @param mixed function args.
	 * @return void early if debugging is disabled or when storing cache values.
	 */
	public function debug_init( $method, $store, $debug_key = null ) {
		if ( func_num_args() >= 4 ) {
			Debug::get_instance()->debug_init( $method, $store, $debug_key, array_slice( func_get_args(), 3 ) );
		} else {
			Debug::get_instance()->debug_init( $method, $store, $debug_key );
		}
	}
}
