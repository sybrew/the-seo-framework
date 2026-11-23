<?php
/**
 * @package The_SEO_Framework\Classes\Admin\SEOBar\Builder\Main
 * @subpackage The_SEO_Framework\SEOBar
 */

namespace The_SEO_Framework\Admin\SEOBar\Builder;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2026 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Implements test_{$*}. See $tests and BLOCKING_TESTS for the test names.
 *
 * @since 4.0.0
 * @since 4.2.0 Renamed from `SeoBar`.
 * @since 5.0.0 Moved from `\\The_SEO_Framework\\Builders\\SEOBar`.
 *
 * @access private
 * @see \\The_SEO_Framework\\Admin\\SEOBar\\Builder
 *
 * @NOTE: All static:: calls within this class are intentional to allow overrides in subclasses.
 */
abstract class Main {

	/**
	 * The SEO Bar query type handled by this builder.
	 *
	 * @since 5.2.2
	 * @var ?string One of 'single', 'term', 'user', or null when unset.
	 */
	public const TYPE = null;

	/**
	 * @since 4.0.0
	 * @since 5.2.2 Now controls regular assessments only.
	 * @abstract
	 * Shared between instances. But, should be overwritten.
	 * @var string[] The regular test names.
	 */
	public static $tests = [];

	/**
	 * Maps test names to blocking assessments in evaluation order.
	 *
	 * @since 5.2.2
	 * @var array<string,string> Blocking assessment method names indexed by test name.
	 */
	protected const BLOCKING_TESTS = [];

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
	 * @since 5.2.2
	 * @var array<class-string<static>,static> Builder instances indexed by class name.
	 */
	private static $instances = [];

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
	 * @since 5.2.2 Now caches instances per builder class.
	 *
	 * @return static
	 */
	final public static function get_instance() {
		return self::$instances[ static::class ] ??= new static; // static: allow overrides
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
	 * @return ?mixed The cache value. Null on failure.
	 */
	final protected static function get_cache( $key ) {
		return self::$cache[ $key ] ?? null;
	}

	/**
	 * Runs this builder's blocking checks before the regular SEO Bar tests.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 Renamed from `_run_all_tests`.
	 * @since 5.2.0 Now accepts `uid` for the user SEO Bar.
	 * @since 5.2.2 1. Renamed from `run_all_tests`.
	 *              2. Now yields the first matching blocking assessment (marked blocking in meta),
	 *                 or runs the regular tests if none matches.
	 * @access private
	 * @generator
	 * @example $test `[ 'title' => [ 'symbol' => 'T', 'status' => 0b1000 ] ]`
	 *
	 * @param array $query The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 * @yield array {
	 *     @type array {$test} The testing results.
	 * }
	 */
	final public function run_tests( $query ) {

		static::$query = $query; // static: allow overrides

		$this->prime_query_cache();

		// Stop at the first blocking assessment that applies to this query.
		foreach ( static::BLOCKING_TESTS as $test => $callback ) { // static: allow override
			$item = $this->{$callback}();

			if ( null === $item ) continue;

			$item['meta']['blocking'] = true;

			yield $test => $item;

			return;
		}

		foreach ( static::$tests as $test ) // static: allow override
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
}
