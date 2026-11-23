<?php
/**
 * @package The_SEO_Framework\Classes\Helper\Query\Cache
 * @subpackage The_SEO_Framework\Query
 */

namespace The_SEO_Framework\Helper\Query;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 - 2026 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
	 * @var ?bool $can_cache_query Whether query results may be cached.
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
	 * Reserved for Helper\Query methods. The caller's function name identifies the cache entry.
	 *
	 * The first parameter might not get retrieved in a later call, for this method
	 * also tests whether the query is setup correctly at the time of the call.
	 *
	 * @since 5.0.0
	 * @since 5.2.1 Now requires a caller frame.
	 * @since 5.2.2 1. Now waits for the `wp` action before caching front-end queries.
	 *              2. Now skips serialization when no extra arguments are passed.
	 *
	 * @param mixed $value_to_set The value to set.
	 * @param mixed ...$args      Extra arguments, that are used to differentiate queries.
	 * @return mixed $value_to_set when provided.
	 *               Otherwise, the previously sent $value_to_set.
	 *               When that's not set either, null.
	 */
	public static function memo( $value_to_set = null, ...$args ) {

		if (
			   ! self::$can_cache_query
			&& ! self::can_cache_query() // If not set, (re)determine.
		) {
			return $value_to_set;
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions -- This is the only efficient way.
		$caller = debug_backtrace(
			\DEBUG_BACKTRACE_IGNORE_ARGS,
			2, // The current call and its caller.
		)[1]['function'];

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- No objects are inserted, nor is this ever unserialized.
		$hash = $args ? serialize( $args ) : 0;

		if ( isset( $value_to_set ) )
			return self::$memo[ $caller ][ $hash ] = $value_to_set;

		return self::$memo[ $caller ][ $hash ] ?? null;
	}

	/**
	 * Checks whether the front-end route or admin query is ready to cache.
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
	 * @since 5.2.2 1. Now waits for the `wp` action before caching front-end queries.
	 *              2. Now omits the debug notice for early query reads.
	 * @global \WP_Query   $wp_query
	 * @global ?\WP_Screen $current_screen
	 *
	 * @return bool Whether query results may be cached.
	 */
	public static function can_cache_query() {

		if ( isset( self::$can_cache_query ) )
			return self::$can_cache_query;

		if ( \defined( 'WP_CLI' ) && \WP_CLI )
			return self::$can_cache_query = false;

		if (
			   \did_action( 'wp' )
			|| (
				   \is_admin()
				&& (
					   isset( $GLOBALS['wp_query']->query )
					|| isset( $GLOBALS['current_screen'] )
				)
			)
		) {
			return self::$can_cache_query = true;
		}

		// The front-end route can still change in pre_handle_404.
		return false;
	}
}
