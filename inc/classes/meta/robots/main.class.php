<?php
/**
 * @package The_SEO_Framework\Classes\Meta\Robots
 * @subpackage The_SEO_Framework\Meta\Robots
 */

namespace The_SEO_Framework\Meta\Robots;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use const The_SEO_Framework\ROBOTS_ASSERT;

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
 * Generates robots meta.
 *
 * @since 4.2.0
 * @since 5.0.0 Moved from `\The_SEO_Framework\Builders\Robots`.
 * @access private
 */
final class Main {

	/**
	 * @since 4.2.0
	 * @var ?array $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                   Leave null to autodetermine query.
	 */
	private $args;

	/**
	 * @since 4.2.0
	 * @var int Modifies return values/assertions. See const ROBOTS_* at /bootstrap/define.php
	 */
	private $options;

	/**
	 * @since 4.2.0
	 * @var Main This instance.
	 */
	private static $instance;

	/**
	 * @since 5.0.0
	 * @var array[] The collected assertions
	 */
	private $assertions = [];

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
	private function __construct() {}

	/**
	 * Creates and returns the instance.
	 *
	 * @since 4.2.0
	 *
	 * @return Main
	 */
	public static function instance() {
		return self::$instance ??= new self;
	}

	/**
	 * Sets class parameters.
	 *
	 * @since 4.2.0
	 * @access private
	 *
	 * @param ?array $args    The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                        Leave null to autodetermine query.
	 * @param int    $options Modifies return values/assertions. See const ROBOTS_* at /bootstrap/define.php
	 * @return Main $this
	 */
	public function set( $args = null, $options = 0 ) {

		$this->args    = $args;
		$this->options = $options;

		return $this;
	}

	/**
	 * Gets the robots values.
	 *
	 * @since 4.2.0
	 * @since 5.2.1 Now reuses yielded values and updates assertions directly.
	 *              With assertions enabled, generating a post's indexing, following, and archiving
	 *              directives takes 4.03% less time in an isolated test. Both test variants reuse
	 *              yielded values and cache the factories.
	 * @access public
	 *
	 * @param ?array $get The robots types to retrieve. See class constant GETTERS for valid values.
	 * @return array The robots-values results. Assert values may be true-esque.
	 */
	public function get( $get = null ) {

		// If this leads to 0 getters, so be it: The dev might've used a deprecated value, which is fine. Continue method.
		$get = ( $get ?? false )
			? array_intersect( self::GETTERS, $get )
			: self::GETTERS;

		// Remit FETCH_OBJ_R opcode calls every time we'd otherwise use $this->options hereinafter.
		$options = $this->options;

		$assert = (bool) ( $options & ROBOTS_ASSERT );

		$assert
			and $this->assertions = [];

		$factory   = $this->get_factory();
		$halt      = $factory::HALT;
		$start     = $factory::START;
		$generator = $factory->set(
			$this->args,
			$options,
		)::generator();

		$results = [];

		foreach ( $get as $g ) {
			// send() already returns the newly yielded value.
			$r = $generator->send( $g );

			do {
				if ( $r !== $halt ) {
					$results[ $g ] = $r;

					$assert
						and $this->assertions[ $g ][ $generator->key() ] = $r;
				}

				$r = $generator->send( true );
			} while ( $start !== $r );
		}

		return $results;
	}

	/**
	 * Returns the robots factory. Factory changes depending on input arguments.
	 *
	 * @since 4.2.0
	 * @since 5.2.1 Caches the two factories without computing memo keys.
	 * @factory
	 *
	 * @return Args|Front
	 */
	private function get_factory() {

		static $args_factory, $front_factory;

		// Each reusable factory receives fresh arguments/options in get().
		return isset( $this->args )
			? ( $args_factory ??= new Args )
			: ( $front_factory ??= new Front );
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
		return $this->assertions;
	}
}
