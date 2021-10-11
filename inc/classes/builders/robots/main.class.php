<?php
/**
 * @package The_SEO_Framework\Classes\Builders\Robots\Main
 * @subpackage The_SEO_Framework\Getter\Robots
 */

namespace The_SEO_Framework\Builders\Robots;

/**
 * The SEO Framework plugin
 * Copyright (C) 2021 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\umemo;

/**
 * Generates robots meta.
 *
 * @since 4.2.0
 * @access protected
 *         Instantiation of class is not part of the public API.
 * @final Can't be extended.
 */
final class Main {

	/**
	 * @since 4.2.0
	 * @var array|null Null to autodetermine query, otherwise the query arguments. : {
	 *    int    $id       The Post, Page or Term ID to generate robots for.
	 *    string $taxonomy The taxonomy.
	 * }
	 */
	private $args;

	/**
	 * @since 4.2.0
	 * @var int Modifies return values/assertions. See const ROBOTS_* at /bootstrap/define.php
	 */
	private $options;

	/**
	 * @since 4.2.0
	 * @param array List of registered getters.
	 */
	private const GETTERS = [
		'noindex',
		'nofollow',
		'noarchive',
		'max_snippet',
		'max_image_preview',
		'max_video_preview',
	];

	/**
	 * The constructor. Or rather, the lack thereof.
	 *
	 * @since 4.2.0
	 * @access private
	 */
	private function __construct() { }

	/**
	 * Creates and returns the instance.
	 *
	 * @since 4.2.0
	 *
	 * @param bool $new Whether to create a new instance.
	 * @return Main
	 */
	public static function instance( $new = false ) {
		return umemo(
			__METHOD__,
			$new ? new static : null
		) ?? static::instance( true );
	}

	/**
	 * Sets class parameters.
	 *
	 * @since 4.2.0
	 * @access private
	 *
	 * @param null|array $args    The robots meta arguments, leave null to autodetermine query : {
	 *    int    $id       The Post, Page or Term ID to generate the URL for.
	 *    string $taxonomy The taxonomy.
	 * }
	 * @param null|array $options Modifies return values/assertions. See const ROBOTS_* at /bootstrap/define.php
	 * @return Main $this
	 */
	public function set( ?array $args = null, int $options = 0 ) {
		$this->args    = $args;
		$this->options = $options;
		return $this;
	}

	/**
	 * Gets the robots values.
	 *
	 * @since 4.2.0
	 * @access public
	 *
	 * @param null|array $get The robots types to retrieve. See class constant GETTERS for valid values.
	 * @return array The robots-values results. Assert values may be true-esque.
	 */
	public function get( ?array $get = null ) {

		// If this leads to 0 getters, so be it: The dev might've used a deprecated value, which is fine. Continue method.
		$get = ( $get ?? false )
			? array_intersect( static::GETTERS, $get )
			: static::GETTERS;

		// Remit FETCH_OBJ_R opcode calls every time we'd otherwise use $this->options hereinafter.
		$options = $this->options;

		$options & \The_SEO_Framework\ROBOTS_ASSERT
			and $this->reset_assertions();

		$factory   = $this->get_factory();
		$halt      = $factory::HALT;
		$generator = $factory->set(
			$this->args,
			$options
		)->generator();

		$results = [];

		foreach ( $get as $g ) {
			$generator->send( $g );

			do {
				// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition.Found -- Shhh. It's OK. I'm a professional.
				if ( ( $r = $generator->current() ) === $halt ) continue; // goto while() -- motivating generator.

				$results[ $g ] = $r;

				$options & \The_SEO_Framework\ROBOTS_ASSERT
					and $this->store_assertion( $g, $generator->key(), $r );
				// We could send anything, really. But this is the only method that loops and yields at the same time.
			} while ( null !== $generator->send( true ) );
		}

		return $results;
	}

	/**
	 * Returns the robots factory. Factory changes depending on input arguments.
	 *
	 * @since 4.2.0
	 * @factory
	 *
	 * @return The_SEO_Framework\Builders\Robots\<Args|Query>
	 */
	private function get_factory() {
		return umemo( __METHOD__, null, isset( $this->args ) )
			?? umemo(
				__METHOD__,
				isset( $this->args ) ? new Args : new Query,
				isset( $this->args )
			);
	}

	/**
	 * Captures and returns the robots-assertions.
	 *
	 * @since 4.2.0
	 * @access public
	 *
	 * @collector
	 * @access protected
	 *         Do not call this method by reference. Only use it to read the return value.
	 * @return array The collected assertions. Returned by reference.
	 */
	public function &collect_assertions() {
		static $collection = [];
		return $collection;
	}

	/**
	 * Stores the robots-assertions.
	 *
	 * @since 4.2.0
	 * @see $this->collect_assertions()
	 *
	 * @param string $get       The robots type getter name (noindex, nofollow...).
	 * @param string $assertion The assertion name (is_404, no_posts);
	 * @param string $result    The assertion's result.
	 */
	private function store_assertion( $get, $assertion, $result ) {
		$this->collect_assertions()[ $get ][ $assertion ] = $result;
	}

	/**
	 * Resets the robots-assertions.
	 *
	 * @since 4.2.0
	 * @see $this->collect_assertions()
	 */
	private function reset_assertions() {
		// phpcs:ignore, VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable -- No function by reference support?
		$collection = &$this->collect_assertions();
		$collection = [];
	}
}