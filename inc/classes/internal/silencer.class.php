<?php
/**
 * @package The_SEO_Framework\Classes\Internal\Silencer
 * @subpackage The_SEO_Framework\Classes\Facade
 */

namespace The_SEO_Framework\Internal;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable -- That's the whole premise of this file.

/**
 * The SEO Framework plugin
 * Copyright (C) 2018 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\Internal\Silencer
 *
 * This is an empty class to silence invalid API calls when a class is soft-disabled.
 * This alleviates redundant checks throughout the plugin API.
 *
 * @since 3.1.0
 * @since 4.2.0 Changed namespace from \The_SEO_Framework to \The_SEO_Framework\Internal
 * @since 5.0.5 Repurposed for silencing the pool (\The_SEO_Framework\Pool).
 * @access private
 * @property static $instance
 */
final class Silencer {

	/**
	 * @since 5.0.5
	 * @var static
	 */
	private static $instance;

	/**
	 * @since 3.1.0
	 */
	public function __construct() {}

	/**
	 * @since 5.0.5
	 * @return static
	 */
	public static function instance() {
		return static::$instance ??= new static;
	}

	/**
	 * @since 3.1.0
	 * @param string $name The property name.
	 * @return null
	 */
	public function __get( $name ) {
		return null;
	}

	/**
	 * @since 3.1.0
	 * @param string $name  The property name.
	 * @param mixed  $value The property value to set.
	 * @return mixed
	 */
	public function __set( $name, $value ) {
		return $value;
	}

	/**
	 * @since 3.1.0
	 * @param string $name The property name.
	 * @return false
	 */
	public function __isset( $name ) {
		return false;
	}

	/**
	 * @since 3.1.0
	 * @param string $name      The method name.
	 * @param array  $arguments The method arguments.
	 */
	public function __call( $name, $arguments ) {
		return null;
	}

	/**
	 * @since 5.0.5
	 * @param string $name      The method name.
	 * @param array  $arguments The method arguments.
	 */
	public static function __callStatic( $name, $arguments ) {
		return null;
	}
}
