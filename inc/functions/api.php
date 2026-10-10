<?php
/**
 * @package The_SEO_Framework\API
 */

namespace {
	defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;
}

/**
 * The SEO Framework plugin
 * Copyright (C) 2018 - 2025 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
	 * @since 5.0.0 Now always returns TSF's object.
	 * @see `the_seo_framework()` alias.
	 * @see inc\classes\pool.class.php for factory API functions;
	 *      e.g., `tsf()->query()->is_sitemap()`
	 * @api
	 *
	 * @return The_SEO_Framework\Load
	 */
	function tsf() {
		return \The_SEO_Framework\Load::get_instance();
	}

	/**
	 * Returns the class from cache.
	 *
	 * This is the recommended way of calling the class, if needed.
	 * Call this after action 'plugins_loaded' priority 5 otherwise you'll cause
	 * unforeseen issues.
	 *
	 * @since 2.2.5
	 * @since 5.0.0 Now always returns TSF's object.
	 * @see `tsf()` alias.
	 * @api
	 *
	 * @return The_SEO_Framework\Load
	 */
	function the_seo_framework() {
		return \The_SEO_Framework\Load::get_instance();
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
	 * @since 5.0.3 No longer requires action `plugins_loaded` to have occurred.
	 * @api
	 *
	 * @return string|bool The SEO Framework class name. False if The SEO Framework isn't loaded (yet).
	 */
	function the_seo_framework_class() {
		return get_class( tsf() );
	}

	/**
	 * Returns the breadcrumbs for front-end display.
	 *
	 * @since 5.0.0
	 * @since 5.1.4 Added the `title` attribute.
	 * @since 5.2.0 1. Now omits the `<style>` element when `the_seo_framework_breadcrumb_shortcode_css` returns no rules.
	 *              2. An empty `home` attribute now omits the `home` crumb.
	 *              3. Added the `max` attribute, which collapses the trail. Values below 3 are raised to 3.
	 *              4. The `home` label and `aria-current` now follow each crumb's `role` instead of its
	 *                 position. Each crumb must have a `role`.
	 *              5. A crumb without a URL now renders as `<span>` instead of an empty link.
	 * @link <https://www.w3.org/WAI/ARIA/apg/patterns/breadcrumb/examples/breadcrumb/>
	 *
	 * @param array|string $atts {
	 *     The shortcode attributes. WordPress passes an empty string when there are none.
	 *
	 *     @type string   $sep   The separator, added through the CSS `content` property. Default `\203A`.
	 *     @type string   $home  The name of the `home` and `current-home` crumbs.
	 *                           An empty string omits the `home` crumb.
	 *                           Default "Home", as translated by WordPress.
	 *     @type string   $class The class of the `nav` element. Only the first valid class name is kept.
	 *                           Default `tsf-breadcrumb`.
	 *     @type ?string  $title `meta` considers meta titles; any other value uses page titles.
	 *                           Default null, which follows the 'breadcrumb_use_meta_title' option.
	 *     @type ?numeric $max   The most crumbs to show; a longer trail collapses.
	 *                           Values below 3 are raised to 3. Default null, which shows the whole trail.
	 * }
	 * @return string The breadcrumbs.
	 */
	function tsf_breadcrumb( $atts = [] ) {

		$atts = shortcode_atts(
			[
				'sep'   => '\203A',
				'home'  => __( 'Home', 'default' ), // defined in wp_page_menu()
				'class' => 'tsf-breadcrumb',
				'title' => null,
				'max'   => null,
			],
			$atts,
			'tsf_breadcrumb',
		);

		// Extract a valid class; it'll be of an escaped kind.
		preg_match( '/-?[a-z_]+[a-z\d_-]*/i', $atts['class'], $matches );

		$class = $matches[0] ?? 'tsf-breadcrumb';
		$sep   = esc_html( $atts['sep'] );

		$trail  = \The_SEO_Framework\Meta\Breadcrumbs::get_breadcrumb_list(
			null,
			[
				'use_meta_title' => isset( $atts['title'] ) ? 'meta' === $atts['title'] : null,
			],
		);
		$crumbs = [];

		foreach ( $trail as $crumb ) {
			// home="" omits the home crumb. home="0" is the label "0". A bare home attribute is ignored.
			if ( '' === $atts['home'] ) {
				if ( 'home' === $crumb['role'] ) continue;
			} elseif ( 'home' === $crumb['role'] || 'current-home' === $crumb['role'] ) {
				$crumb['name'] = $atts['home'];
			}

			$crumbs[] = $crumb;
		}

		// The collapse keeps the last `max - 1` crumbs, so it waits for the complete trail.
		if ( $atts['max'] ) {
			$max = max( 3, (int) $atts['max'] ); // At least the first, parent, and current crumbs.

			if ( count( $crumbs ) > $max )
				$crumbs = [
					$crumbs[0],
					[
						'role' => 'ellipsis',
						'name' => '',
						'url'  => '',
					],
					...array_slice( $crumbs, -( $max - 1 ) ),
				];
		}

		$html = '';

		foreach ( $crumbs as $crumb ) {
			if ( 'ellipsis' === $crumb['role'] ) {
				$html .= <<<'HTML'
					<li class="breadcrumb-item breadcrumb-ellipsis" aria-hidden="true">&hellip;</li>
					HTML;
				continue;
			}

			$name    = esc_html( $crumb['name'] );
			$current = 'current' === $crumb['role'] || 'current-home' === $crumb['role'];

			if ( $current || '' === $crumb['url'] ) {
				$item = sprintf(
					'<span%s>%s</span>',
					$current ? ' aria-current="page"' : '',
					$name,
				);
			} else {
				$item = sprintf(
					'<a href="%s">%s</a>',
					esc_url( $crumb['url'] ),
					$name,
				);
			}

			$html .= <<<HTML
				<li class="breadcrumb-item">$item</li>
				HTML;
		}

		/**
		 * @since 5.0.0
		 * @since 5.2.0 1. Added `padding-inline-start:0` to `nav.$class ol`.
		 *              2. Now omits the `<style>` element when this filter returns no rules.
		 *              3. `nav.$class ol` is now a wrapping flex row whose crumbs use `white-space:nowrap`.
		 * @param array  $css   The CSS selectors and their attributes.
		 * @param string $class The class name of the breadcrumb wrapper.
		 */
		$css = (array) apply_filters(
			'the_seo_framework_breadcrumb_shortcode_css',
			[
				"nav.$class ol"                            => [
					'display:flex',
					'flex-wrap:wrap',
					'list-style:none',
					'margin-inline-start:0',
					'padding-inline-start:0',
				],
				"nav.$class ol li"                         => [ // We could combine with above; but this is easier for other devs.
					'white-space:nowrap',
				],
				"nav.$class ol li:not(:last-child)::after" => [
					"content:'$sep'",
					'margin-inline-end:1ch',
					'margin-inline-start:1ch',
				],
			],
			$class,
		);

		$styles = '';

		foreach ( $css as $selector => $declaration )
			$styles .= sprintf(
				'%s{%s}',
				$selector,
				implode( ';', $declaration ),
			);

		$style = $styles ? "<style>$styles</style>" : '';
		$nav   = <<<HTML
			<nav aria-label="Breadcrumb" class="$class"><ol>$html</ol></nav>
			HTML;

		/**
		 * @since 5.0.0
		 * @since 5.2.0 1. `$style` is now an empty string when the CSS filter returns no rules.
		 *              2. `$crumbs` may now omit the `home` crumb, rename it, and include an ellipsis item
		 *                 when `max` collapses the trail. It is still the list rendered in the shortcode.
		 *              3. Added `$trail`, the generated list before those changes.
		 * @param string $output The entire breadcrumb navigation element output.
		 * @param array  $crumbs The breadcrumb items rendered in the shortcode. May omit the `home` crumb,
		 *                       use the `home` attribute as its name, and include an item with role `ellipsis`.
		 * @param string $nav    The breadcrumb navigation element.
		 * @param string $style  The CSS style element appended. Empty when no CSS rules remain.
		 * @param array  $trail  The generated breadcrumb list before the shortcode omits or renames
		 *                       the `home` crumb, or collapses the trail.
		 */
		return apply_filters(
			'the_seo_framework_breadcrumb_shortcode_output',
			"$nav$style",
			$crumbs,
			$nav,
			$style,
			$trail,
		);
	}
}

namespace The_SEO_Framework {

	/**
	 * Tells the headless state of the plugin.
	 *
	 * @since 5.0.0
	 * @api
	 *
	 * @param ?string $type The type of headless mode to request.
	 * @return bool|array $is_headless Whether headless TSF is enabled by $type, or otherwise all values: {
	 *   'meta'     => bool True to disable post/term-meta-data storing/fetching.
	 *   'settings' => bool True to disable non-default setting.
	 *   'user'     => bool True to disable SEO user-meta-data storing/fetching.
	 * }
	 */
	function is_headless( $type = null ) {

		static $is_headless;

		if ( ! isset( $is_headless ) ) {
			if ( \defined( 'THE_SEO_FRAMEWORK_HEADLESS' ) ) {
				$is_headless = [
					'meta'     => true,
					'settings' => true,
					'user'     => true,
				];

				\is_array( \THE_SEO_FRAMEWORK_HEADLESS )
					and $is_headless = array_map(
						'wp_validate_boolean',
						array_merge( $is_headless, \THE_SEO_FRAMEWORK_HEADLESS ),
					);
			} else {
				$is_headless = [
					'meta'     => false,
					'settings' => false,
					'user'     => false,
				];
			}
		}

		return isset( $type )
			? $is_headless[ $type ] ?? false
			: $is_headless;
	}

	/**
	 * Normalizes generation args to prevent PHP warnings.
	 * This is the standard way TSF determines the type of query.
	 *
	 * 'uid' is reserved. It is already used in Author::build(), however.
	 *
	 * @since 5.0.0
	 * @see https://github.com/sybrew/the-seo-framework/issues/640#issuecomment-1703260744.
	 *      We made an exception about passing by reference for this function.
	 *
	 * @param ?array $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                     Leave null to have queries be autodetermined.
	 *                     Passed by reference.
	 */
	function normalize_generation_args( &$args ) {

		if ( \is_array( $args ) ) {
			$args += [
				'id'       => 0,
				'tax'      => $args['taxonomy'] ?? '',
				'taxonomy' => $args['tax'] ?? '', // Legacy support.
				'pta'      => '',
				'uid'      => 0,
			];
		} else {
			$args = null;
		}
	}

	/**
	 * Determines the type of request from the arguments.
	 *
	 * Hint: Use `tsf()->query()->is_static_front_page()` to determine if 'single' is the frontpage.
	 *
	 * @since 5.0.0
	 *
	 * @param array $args The query arguments. Expects indexes 'id', 'tax', 'pta', and 'uid'.
	 * @return string The query type: 'user', 'pta', 'homeblog', 'term', or 'single'.
	 */
	function get_query_type_from_args( $args ) {

		if ( empty( $args['id'] ) ) {
			if ( $args['uid'] )
				return 'user';

			if ( $args['pta'] )
				return 'pta';

			return 'homeblog'; // "homeblog" isn't single, has no id, and is the frontpage.
		} elseif ( $args['tax'] ) {
			return 'term';
		}

		return 'single'; // page, post, product, frontpage, etc.
	}

	/**
	 * A helper function allows coalescing based on string length.
	 * If the string is of length 0, it'll return null. Otherwise, it'll return the string.
	 *
	 * E.g., coalesce_strlen( '0' ) ?? '1'; will return '0'.
	 * But, coalesce_strlen( '' ) ?? '1'; will return '1'.
	 *
	 * @since 5.0.0
	 *
	 * @param string $string The string to coalesce.
	 * @return ?string The input string if it's at least 1 byte, null otherwise.
	 */
	function coalesce_strlen( $string ) {
		return \strlen( $string ) ? $string : null;
	}

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
	 *     print "expensive $arg!";
	 *     return $arg * 2;
	 * }
	 * function my_function( $arg ) {
	 *     return memo( null, $arg )
	 *         ?? memo( expensive_call( $arg ), $arg );
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
	 * @return mixed The cached value if $value_to_set is null.
	 *               Otherwise, the $value_to_set.
	 */
	function memo( $value_to_set = null, ...$args ) {

		static $memo = [];

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- No objects inserted, nor ever unserialized.
		$hash = serialize(
			[
				'args' => $args,
				'file' => 0,
				'line' => 0,
			]
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions -- This is the only efficient way.
			+ debug_backtrace( \DEBUG_BACKTRACE_IGNORE_ARGS, 2 )[1],
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
	 *     print "expensive $arg!";
	 *     return $arg * 2;
	 * }
	 * function my_function( $arg ) {
	 *     return umemo( __METHOD__, null, $arg )
	 *         ?? umemo( __METHOD__, expensive_call( $arg ), $arg );
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
	 * @return mixed The cached value if $value_to_set is null.
	 *               Otherwise, the $value_to_set.
	 */
	function umemo( $key, $value_to_set = null, ...$args ) {

		static $memo = [];

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- No objects are inserted, nor is this ever unserialized.
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
	 * function my_function( $arg ) { return fmemo( fn() => print $arg + 5 ); }
	 * my_function( 1 ); // prints '1', returns 6.
	 * my_function( 1 ); // does not print, returns 6.
	 * ```
	 * Arrow functions are neat with this for they automatically register only necessary arguments to fmemo().
	 * This way, callers of my_function() won't bust the cache by sending unregistered superfluous arguments.
	 *
	 * ```
	 * function printer() { print 69; }
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
	 * TODO Can we use callables as $func? If so, adjust docs and apply internally.
	 *
	 * @param callable $func The Closure or function to memoize.
	 *                       The Closure can only be cached properly if it's statically stored.
	 * @return mixed The cached value if $value_to_set is null.
	 *               Otherwise, the $value_to_set.
	 */
	function fmemo( $func ) {

		static $memo = [];

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- This is never unserialized.
		$hash = serialize(
			[
				'file' => '',
				'line' => 0,
			]
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions -- This is the only efficient way.
			+ debug_backtrace( 0, 2 )[1],
		);

		// Normally, I try to avoid NOTs for they add (tiny) overhead. Here, I chose readability over performance.
		if ( ! isset( $memo[ $hash ] ) ) {
			// Store the result of the function. If that's null/void, store hash.
			$memo[ $hash ] = \call_user_func( $func ) ?? $hash;
		}

		return $memo[ $hash ] === $hash ? null : $memo[ $hash ];
	}
}
