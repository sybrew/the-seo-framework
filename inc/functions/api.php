<?php
/**
 * @package The_SEO_Framework\API
 */

namespace {
	defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;
}

/**
 * The SEO Framework plugin
 * Copyright (C) 2018 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

namespace {
	/**
	 * Returns the class from cache.
	 *
	 * This is the recommended way of calling the class, if needed.
	 * Call this after action 'plugins_loaded' priority 5 otherwise you'll cause
	 * unforeseen issues.
	 *
	 * @since 4.2.0
	 * @see `the_seo_framework()` alias.
	 * @api
	 *
	 * @return null|object The plugin class object.
	 */
	function tsf() {
		return The_SEO_Framework\_init_tsf();
	}

	/**
	 * Returns the class from cache.
	 *
	 * This is the recommended way of calling the class, if needed.
	 * Call this after action 'plugins_loaded' priority 5 otherwise you'll cause
	 * unforeseen issues.
	 *
	 * @since 2.2.5
	 * @see `tsf()` alias.
	 * @api
	 *
	 * @return null|object The plugin class object.
	 */
	function the_seo_framework() {
		return The_SEO_Framework\_init_tsf();
	}

	/**
	 * Returns the database version of TSF.
	 *
	 * @since 3.1.0
	 * @since 3.1.2 Now casts to string.
	 * @api
	 *
	 * @return string The database version. '0' if version isn't found.
	 */
	function the_seo_framework_db_version() {
		return (string) get_option( 'the_seo_framework_upgraded_db_version', '0' );
	}

	/**
	 * Returns the facade class name from cache.
	 *
	 * @since 2.7.0
	 * @since 2.8.0 Added `did_action()` check.
	 * @since 4.2.0 Removed memoization.
	 * @api
	 *
	 * @return string|bool The SEO Framework class name. False if The SEO Framework isn't loaded (yet).
	 */
	function the_seo_framework_class() {

		// did_action() returns true for current action match, too.
		if ( ! did_action( 'plugins_loaded' ) )
			return false;

		return get_class( tsf() );
	}
}

namespace The_SEO_Framework {
	/**
	 * Determines if the method or function has already run.
	 *
	 * @since 4.2.3
	 * @api
	 * @todo make $caller optional and use debug_backtrace()?
	 *
	 * @param string $caller The method or function that calls this.
	 * @return bool True if already called, false otherwise.
	 */
	function has_run( $caller ) {
		static $ran = [];
		return $ran[ $caller ] ?? ! ( $ran[ $caller ] = true );
	}

	/**
	 * Stores and returns memoized values for the caller.
	 *
	 * This method is not forward-compatible with PHP: It expects values it doesn't want populated,
	 * instead of filtering what's actually useful for memoization. For example, it expects `file`
	 * and `line` from debug_backtrace() -- those are expected to be dynamic from the caller, and
	 * we set them to `0` to prevent a few opcode calls, rather than telling which array indexes
	 * we want exactly. The chance this failing in a future update is slim, for all useful data of
	 * the callee is given already via debug_backtrace().
	 * We also populate the `args` value "manually" for it's faster than using debug_backtrace()'s
	 * `DEBUG_BACKTRACE_PROVIDE_OBJECT` option.
	 *
	 * We should keep a tap on debug_backtrace changes. Hopefully, they allow us to ignore
	 * more than just args.
	 *
	 * This method does not memoize the object via debug_backtrace. This means that the
	 * objects will have values memoized cross-instantiations.
	 *
	 * Example usage:
	 * ```
	 * function expensive_call( $arg ) {
	 *     print( "expensive $arg!" );
	 *     return $arg * 2;
	 * }
	 * function my_function( $arg ) {
	 *    return memo( null, $arg );
	 *        ?? memo( expensive_call( $arg ), $arg );
	 * }
	 * my_function( 1 ); // prints "expensive 1!", returns 2.
	 * my_function( 1 ); // returns 2.
	 * my_function( 2 ); // prints "expensive 2!", returns 4.
	 *
	 * function test() {
	 *     return memo() ?? memo( expensive_call( 42 ) );
	 * }
	 * test(); // prints "expensive 42", returns 84.
	 * test(); // returns 84.
	 * ```
	 *
	 * @since 4.2.0
	 * @see umemo() -- sacrifices cleanliness for performance.
	 * @see fmemo() -- sacrifices everything for readability.
	 * @api
	 *
	 * @param mixed $value_to_set The value to set.
	 * @param mixed ...$args      Extra arguments, that are used to differentiaty callbacks.
	 *                            Arguments may not contain \Closure()s.
	 * @return mixed : {
	 *    mixed The cached value if set and $value_to_set is null.
	 *       null When no value has been set.
	 *       If $value_to_set is set, the new value.
	 * }
	 */
	function memo( $value_to_set = null, ...$args ) {

		static $memo = [];

		// phpcs:ignore, WordPress.PHP.DiscouragedPHPFunctions -- No objects inserted, nor ever unserialized.
		$hash = serialize(
			[
				'args' => $args,
				'file' => 0,
				'line' => 0,
			]
			// phpcs:ignore, WordPress.PHP.DevelopmentFunctions -- This is the only efficient way.
			+ debug_backtrace( \DEBUG_BACKTRACE_IGNORE_ARGS, 2 )[1]
		);

		if ( isset( $value_to_set ) )
			return $memo[ $hash ] = $value_to_set;

		return $memo[ $hash ] ?? null;
	}

	/**
	 * Stores and returns memoized values for the caller.
	 * This is 10 times faster than memo(), but requires from you a $key.
	 *
	 * We're talking milliseconds over thousands of iterations, though.
	 *
	 * Example usage:
	 * ```
	 * function expensive_call( $arg ) {
	 *     print( "expensive $arg!" );
	 *     return $arg * 2;
	 * }
	 * function my_function( $arg ) {
	 *    return umemo( __METHOD__, null, $arg );
	 *        ?? umemo( __METHOD__, expensive_call( $arg ), $arg );
	 * }
	 * my_function( 1 ); // prints "expensive 1!", returns 2.
	 * my_function( 1 ); // returns 2.
	 * my_function( 2 ); // prints "expensive 2!", returns 4.
	 * ```
	 *
	 * @since 4.2.0
	 * @see memo() -- sacrifices performance for cleanliness.
	 * @see fmemo() -- sacrifices everything for readability.
	 * @api
	 *
	 * @param string $key          The key you want to use to memoize. It's best to use the method name.
	 *                             You can share a unique key between various functions.
	 * @param mixed  $value_to_set The value to set.
	 * @param mixed  ...$args      Extra arguments, that are used to differentiate callbacks.
	 *                             Arguments may not contain \Closure()s.
	 * @return mixed : {
	 *    mixed The cached value if set and $value_to_set is null.
	 *       null When no value has been set.
	 *       If $value_to_set is set, the new value.
	 * }
	 */
	function umemo( $key, $value_to_set = null, ...$args ) {

		static $memo = [];

		// phpcs:ignore, WordPress.PHP.DiscouragedPHPFunctions -- No objects are inserted, nor is this ever unserialized.
		$hash = serialize( [ $key, $args ] );

		if ( isset( $value_to_set ) )
			return $memo[ $hash ] = $value_to_set;

		return $memo[ $hash ] ?? null;
	}

	/**
	 * Stores and returns memoized values for the Closure caller. This helps wrap
	 * a whole function inside a single memoization call.
	 *
	 * This method does not memoize the object via debug_backtrace. This means that the
	 * objects will have values memoized cross-instantiations.
	 *
	 * Example usage, PHP7.4+:
	 * ```
	 * function my_function( $arg ) { return fmemo( fn() => print( $arg ) + 5 ); }
	 * my_function( 1 ); // prints '1', returns 6.
	 * my_function( 1 ); // does not print, returns 6.
	 * ```
	 * Arrow functions are neat with this for they automatically register only necessary arguments to fmemo().
	 * This way, callers of my_function() won't bust the cache by sending unregistered superfluous arguments.
	 *
	 * ```
	 * function printer() { print( 69 ); }
	 * function print_once() { fmemo( 'printer' ); }
	 * print_once(); // 69
	 * print_once(); // *cricket noises*
	 * ```
	 *
	 * @since 4.2.0
	 * @see memo() -- sacrifices performance for cleanliness.
	 * @see umemo() -- sacrifices cleanliness for performance.
	 * @ignore We couldn't find a use for this... yet. Probably once we support only PHP7.4+
	 * @api
	 * TODO Can we use callables as $fn? If so, adjust docs and apply internally.
	 *
	 * @param \Closure $fn The Closure or function to memoize.
	 * @return mixed : {
	 *    mixed The cached value if set and $value_to_set is null.
	 *       null When no value has been set.
	 *       If $value_to_set is set, the new value.
	 * }
	 */
	function fmemo( $fn ) {

		static $memo = [];

		// phpcs:ignore, WordPress.PHP.DiscouragedPHPFunctions -- This is never unserialized.
		$hash = serialize(
			[
				'file' => '',
				'line' => 0,
			]
			// phpcs:ignore, WordPress.PHP.DevelopmentFunctions -- This is the only efficient way.
			+ debug_backtrace( 0, 2 )[1]
		);

		// Normally, I try to avoid NOTs for they add (tiny) overhead. Here, I chose readability over performance.
		if ( ! isset( $memo[ $hash ] ) ) {
			// Store the result of the function. If that's null/void, store hash.
			$memo[ $hash ] = \call_user_func( $fn ) ?? $hash;
		}

		return $memo[ $hash ] === $hash ? null : $memo[ $hash ];
	}
}
