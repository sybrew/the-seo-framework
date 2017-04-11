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
 * Interface The_SEO_Framework\Debug_Interface
 *
 * Sets public debug functions.
 *
 * @since 2.8.0
 */
interface Debug_Interface {

	/**
	 * Mark a filter as deprecated and inform when it has been used.
	 *
	 * @since 2.8.0
	 * @access private
	 * @see @this->_deprecated_function().
	 *
	 * @param string $filter		The function that was called.
	 * @param string $version		The version of WordPress that deprecated the function.
	 * @param string $replacement	Optional. The function that should have been called. Default null.
	 */
	public function _deprecated_filter( $filter, $version, $replacement = null );

	/**
	 * Mark a function as deprecated and inform when it has been used.
	 *
	 * Taken from WordPress core, but added extra parameters and linguistic alterations.
	 *
	 * The current behavior is to trigger a user error if WP_DEBUG is true.
	 *
	 * @since 2.6.0
	 * @access private
	 *
	 * @param string $function		The function that was called.
	 * @param string $version		The version of WordPress that deprecated the function.
	 * @param string $replacement	Optional. The function that should have been called. Default null.
	 */
	public function _deprecated_function( $function, $version, $replacement = null );

	/**
	 * Mark a function as deprecated and inform when it has been used.
	 *
	 * Taken from WordPress core, but added extra parameters and linguistic alterations.
	 *
	 * The current behavior is to trigger a user error if WP_DEBUG is true.
	 *
	 * @since 2.6.0
	 * @access private
	 *
	 * @param string $function	The function that was called.
	 * @param string $message	A message explaining what has been done incorrectly.
	 * @param string $version	The version of WordPress where the message was added.
	 */
	public function _doing_it_wrong( $function, $message, $version );

	/**
	 * Mark a property or method inaccessible when it has been used.

	 * The current behavior is to trigger a user error if WP_DEBUG is true.
	 *
	 * @since 2.7.0
	 * @access private
	 *
	 * @param string $p_or_m	The Property or Method.
	 * @param string $message	A message explaining what has been done incorrectly.
	 */
	public function _inaccessible_p_or_m( $p_or_m, $message = '' );

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
	public function debug_init( $method, $store, $debug_key );
}
