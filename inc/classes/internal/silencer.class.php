<?php
/**
 * @package The_SEO_Framework\Classes\Internal\Silencer
 * @subpackage The_SEO_Framework\Classes\Facade
 */

namespace The_SEO_Framework\Internal;

/**
 * The SEO Framework plugin
 * Copyright (C) 2018 - 2022 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable -- That's the whole premise of this file.

/**
 * Class The_SEO_Framework\Internal\Silencer
 *
 * This is an empty class to silence invalid API calls when the plugin is soft-disabled.
 * This alleviates redundant checks throughout the plugin API.
 *
 * @since 3.1.0
 * @since 4.2.0 Changed namespace from \The_SEO_Framework to \The_SEO_Framework\Internal
 * @ignore
 * @property false $loaded
 */
final class Silencer {

	/**
	 * Tells if this plugin is loaded.
	 *
	 * @NOTE: Only `\The_SEO_Framework\_init_tsf()` should adjust this.
	 *
	 * @since 3.1.0
	 * @access protected
	 *         Don't alter this variable.
	 * @var boolean $loaded
	 */
	public $loaded = false;

	/**
	 * @since 3.1.0
	 */
	public function __construct() {}

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
}
