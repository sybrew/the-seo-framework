<?php
/**
 * @package The_SEO_Framework\Classes\Helper\Query\Cache
 * @subpackage The_SEO_Framework\Query
 */

namespace The_SEO_Framework\Helper\Query;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Helper\Query; // Yes, it is legal to share class and namespaces.

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
 * Memoizes queries for Query.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->query()->cache() instead.
 */
class Cache {

	/**
	 * @since 5.0.0
	 * @var ?true $can_cache_query
	 */
	private static $can_cache_query;

	/**
	 * @since 5.0.0
	 * @var array[] $memo The memo cache by value
	 */
	private static $memo = [];

	/**
	 * Memoizes queries.
	 * Should not be used on methods that aren't final.
	 *
	 * The first parameter might not get retrieved in a later call, for this method
	 * also tests whether the query is setup correctly at the time of the call.
	 *
	 * @since 5.0.0
	 *
	 * @param mixed $value_to_set The value to set.
	 * @param mixed ...$args      Extra arguments, that are used to differentiaty queries.
	 * @return mixed $value_to_set when provided.
	 *               Otherwise, the previously sent $value_to_set.
	 *               When that's not set either, null.
	 */
	public static function memo( $value_to_set = null, ...$args ) {

		if (
			   ! static::$can_cache_query
			&& ! static::can_cache_query() // If not set, (re)determine.
		) {
			return $value_to_set;
		}

		// phpcs:ignore, WordPress.PHP.DevelopmentFunctions -- This is the only efficient way.
		$caller = debug_backtrace( \DEBUG_BACKTRACE_IGNORE_ARGS, 2 )[1]['function'] ?? '';

		// We need not seralize the caller; waste of processing if we'd merge with $args.
		// phpcs:ignore, WordPress.PHP.DiscouragedPHPFunctions -- No objects are inserted, nor is this ever unserialized.
		$hash = "$caller/" . serialize( $args );

		if ( isset( $value_to_set ) )
			return static::$memo[ $hash ] = $value_to_set;

		return static::$memo[ $hash ] ?? null;
	}

	/**
	 * Checks whether $wp_query or $current_screen is set.
	 * Memoizes the return value once we're sure it won't change.
	 *
	 * @since 2.6.1
	 * @since 2.9.0 Added doing it wrong notice.
	 * @since 3.1.0 1. Is now protected.
	 *              2. Now asks for and passes $method.
	 *              3. Now returns false on WP CLI.
	 * @since 3.2.2 No longer spits out errors on production websites.
	 * @since 5.0.0 1. No longer accepts the $method parameter.
	 *              2. Moved from `\The_SEO_Framework\Load`.
	 * @global \WP_Query $wp_query
	 * @global \WP_Screen|null $current_screen
	 *
	 * @return bool True when wp_query or current_screen has been initialized.
	 */
	public static function can_cache_query() {

		if ( isset( static::$can_cache_query ) )
			return static::$can_cache_query;

		if ( \defined( 'WP_CLI' ) && \WP_CLI )
			return static::$can_cache_query = false;

		if ( isset( $GLOBALS['wp_query']->query ) || isset( $GLOBALS['current_screen'] ) )
			return static::$can_cache_query = true;

		if ( \THE_SEO_FRAMEWORK_DEBUG )
			static::do_query_error_notice();

		// Don't set yet.
		return false;
	}

	// phpcs:disable -- method unused in production.
	/**
	 * Outputs a doing it wrong notice if an error occurs in the current query.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 1. No longer accepts the method parameter.
	 *              2. Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param string $method The original caller method.
	 */
	private static function do_query_error_notice() {

		$backtrace = debug_backtrace( \DEBUG_BACKTRACE_PROVIDE_OBJECT, 8 );

		if ( ! $backtrace ) return [];

		/**
		 * 0 = caller of this func
		 * 1 = Query Cacher
		 * 2 = Query Callee
		 * 3 = Query Caller (internal issue)
		 */
		$error = $backtrace[3];

		/**
		 * Search from the bottom up, for we want the first caller, not the last.
		 *
		 * This is unlike `The_SEO_Framework\Internal/Debug::get_error()` that searches
		 * from top to bottom, because there we expect the user to directly call a
		 * broken function in TSF; we clean up internal deprecations before public release.
		 **/
		foreach ( array_reverse( \array_slice( $backtrace, 3 ) ) as $trace ) {
			if (
				   isset( $trace['object'] )
				&& is_a( $trace['object'], \the_seo_framework_class(), false )
			) {
				$error = $trace;
				break;
			}
		}

		$message = "You've initiated a method that uses queries too early.";

		if ( ! empty( $error ) ) {
			$message .= " - In file: {$error['file']}";
			$message .= " - On line: {$error['line']}";
		}
		\tsf()->_doing_it_wrong( \esc_html( $error['function'] ?? '' ), \esc_html( $message ), '2.9.0' );

		// Backtrace debugging, just once per request.
		$depth = 10;
		static $_more = true;
		if ( $_more ) {
			error_log( var_export( debug_backtrace( \DEBUG_BACKTRACE_PROVIDE_OBJECT, $depth ), true ) );
			$_more = false;
		}
	}
	// phpcs:enable -- Method unused in production.
}
