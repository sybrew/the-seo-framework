<?php
/**
 * @package The_SEO_Framework\Classes\Silencer
 * @subpackage The_SEO_Framework\Classes\Facade
 */

namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2018 - 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\Silencer
 *
 * This is an empty class to silence invalid API calls when the plugin is soft-disabled.
 * This alleviates redundant checks throughout the plugin API.
 *
 * @since 3.1.0
 * @ignore
 * @property false $loaded
 */
final class Silencer {

	public $loaded = false;

	public function __construct() {}

	public function __get( $name ) {
		return null;
	}

	public function __set( $name, $value ) {
		return $value;
	}

	public function __isset( $name ) {
		return false;
	}

	public function __call( $name, $arguments ) {
		return null;
	}
}
