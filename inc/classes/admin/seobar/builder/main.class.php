<?php
/**
 * @package The_SEO_Framework\Classes\Admin\SEOBar\Builder\Main
 * @subpackage The_SEO_Framework\SEOBar
 */

namespace The_SEO_Framework\Admin\SEOBar\Builder;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Generates the SEO Bar.
 *
 * Mind the late static binding. We use "self" if the variable is shared between instances.
 * We use "static" if the variable isn't shared between instances.
 *
 * @link <https://www.php.net/manual/en/language.oop5.late-static-bindings.php>
 *
 * Implements test_{$*}, see property $tests and method `run_test()` for what $* may be.
 *
 * @since 4.0.0
 * @since 4.2.0 Renamed from `SeoBar`.
 * @since 5.0.0 Moved from `\The_SEO_Framework\Builders\SEOBar`.
 *
 * @access private
 * @see \The_SEO_Framework\Admin\SEOBar\Builder
 */
abstract class Main {

	/**
	 * @since 4.0.0
	 * @abstract
	 * Shared between instances. But, should be overwritten.
	 * @var array All known tests.
	 */
	public static $tests = [];

	/**
	 * @since 4.0.0
	 * Shared between instances.
	 * @var array $cache A non-volatile caching status. Holds post type settings,
	 *                   among other things, to be used in generation.
	 */
	private static $cache = [];

	/**
	 * @since 4.0.0
	 * Not shared between instances.
	 * @var array $query The current query for the SEO Bar.
	 */
	protected static $query;

	/**
	 * @since 4.0.0
	 * @var array The current query cache.
	 */
	protected $query_cache = [];

	/**
	 * @since 4.0.0
	 * Not shared between instances
	 * @var \The_SEO_Framework\Builders\SEOBar_* $instance The instance.
	 */
	protected static $instance;

	/**
	 * Constructor.
	 *
	 * Sets late static binding.
	 *
	 * @since 4.0.0
	 */
	final protected function __construct() {
		$this->prime_cache();
	}

	/**
	 * Returns this instance.
	 *
	 * @since 4.0.0
	 *
	 * @return static
	 */
	final public static function get_instance() {
		return static::$instance ??= new static;
	}

	/**
	 * Sets non-volatile cache by key value.
	 * This cache will stick around for multiple SEO Bar generations.
	 *
	 * @since 4.0.0
	 *
	 * @param string $key   The cache key.
	 * @param mixed  $value The cache value.
	 * @return mixed The cache value.
	 */
	final protected static function set_cache( $key, $value ) {
		return self::$cache[ $key ] = $value;
	}

	/**
	 * Retrieves non-volatile cache value by key.
	 * This cache will stick around for multiple SEO Bar generations.
	 *
	 * @since 4.0.0
	 *
	 * @param string $key The cache key.
	 * @return mixed|null The cache value. Null on failure.
	 */
	final protected static function get_cache( $key ) {
		return self::$cache[ $key ] ?? null;
	}

	/**
	 * Runs all SEO bar tests.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 Renamed from `_run_all_tests`.
	 * @access private
	 * @generator
	 *
	 * @param array $query {
	 *     The query parameters.
	 *
	 *     @type int    $id        Required. The current post or term ID.
	 *     @type string $taxonomy  Optional. If not set, this will interpret it as a post.
	 *     @type string $post_type Optional. If not set, this will be automatically filled.
	 *                             This parameter is ignored for taxonomies.
	 * }
	 * @yield array {
	 *     @type array {$test} The testing results.
	 * }
	 */
	public function run_all_tests( $query ) {
		yield from $this->run_test( static::$tests, $query );
	}

	/**
	 * Runs one or more SEO bar tests.
	 *
	 * @since 4.0.0
	 * @since 4.1.4 No longer clears the query cache.
	 * @since 5.0.0 Renamed from `_run_test`.
	 * @access private
	 * @generator
	 *
	 * @param string|string[] $tests The test(s) to perform.
	 * @param array           $query {
	 *     The query parameters.
	 *
	 *     @type int    $id        Required. The current post or term ID.
	 *     @type string $taxonomy  Optional. If not set, this will interpret it as a post.
	 *     @type string $post_type Optional. If not set, this will be automatically filled.
	 *                             This parameter is ignored for taxonomies.
	 * }
	 * @yield array {
	 *     @type array {$test} The testing results.
	 * }
	 */
	final public function run_test( $tests, $query ) {

		$tests = array_intersect( static::$tests, (array) $tests );

		static::$query = $query;

		$this->prime_query_cache();

		if ( \in_array( 'redirect', $tests, true ) && $this->has_blocking_redirect() )
			$tests = [ 'redirect' ];

		foreach ( $tests as $test )
			yield $test => $this->{"test_$test"}();
	}

	/**
	 * Clears the query cache. Saving a few bytes of memory, prepping for the next yield.
	 *
	 * @since 4.1.4
	 */
	final public function clear_query_cache() {
		$this->query_cache = [];
	}

	/**
	 * Presents an unalterable form of query cache.
	 *
	 * @since 4.1.4
	 *
	 * @return array The query cache, unknown values.
	 */
	final public function get_query_cache() {
		return $this->query_cache;
	}

	/**
	 * Primes the cache.
	 *
	 * @since 4.0.0
	 */
	abstract protected function prime_cache();

	/**
	 * Primes the current query cache.
	 * It's best to overwrite the cache whenever you generate a new SEO Bar.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Removed first parameter.
	 */
	abstract protected function prime_query_cache();

	/**
	 * Tests for blocking redirection.
	 *
	 * @since 4.0.0
	 *
	 * @return bool True if there's a blocking redirect, false otherwise.
	 */
	abstract protected function has_blocking_redirect();
}
