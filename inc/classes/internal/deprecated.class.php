<?php
/**
 * @package The_SEO_Framework\Classes\Internal\Deprecated
 * @subpackage The_SEO_Framework\Debug\Deprecated
 */

namespace The_SEO_Framework\Internal;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

use function \The_SEO_Framework\{
	memo,   // Precautionary.
	umemo,  // Precautionary.
};

/**
 * Class The_SEO_Framework\Internal\Deprecated
 *
 * Contains all deprecated functions.
 *
 * @since 2.8.0
 * @since 3.1.0 Removed all methods deprecated in 3.0.0.
 * @since 4.0.0 Removed all methods deprecated in 3.1.0.
 * @since 4.1.4 Removed all methods deprecated in 4.0.0.
 * @since 4.2.0 1. Changed namespace from \The_SEO_Framework to \The_SEO_Framework\Internal
 *              2. Removed all methods deprecated in 4.1.0.
 * @ignore
 */
final class Deprecated {

	/**
	 * Set the value of the transient.
	 *
	 * Prevents setting of transients when they're disabled.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Deprecated
	 * @deprecated
	 *
	 * @param string $transient  Transient name. Expected to not be SQL-escaped.
	 * @param string $value      Transient value. Expected to not be SQL-escaped.
	 * @param int    $expiration Transient expiration date, optional. Expected to not be SQL-escaped.
	 */
	public function set_transient( $transient, $value, $expiration = 0 ) {

		\tsf()->_deprecated_function( 'tsf()->set_transient()', '4.3.0', 'set_transient()' );

		return \set_transient( $transient, $value, $expiration );
	}

	/**
	 * Get the value of the transient.
	 *
	 * If the transient does not exists, does not have a value or has expired,
	 * or transients have been disabled through a constant, then the transient
	 * will be false.
	 *
	 * N.B. not all transient settings make use of this function, bypassing the constant check.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Deprecated
	 * @deprecated
	 *
	 * @param string $transient Transient name. Expected to not be SQL-escaped.
	 * @return mixed|bool Value of the transient. False on failure or non existing transient.
	 */
	public function get_transient( $transient ) {

		\tsf()->_deprecated_function( 'tsf()->get_transient()', '4.3.0', 'get_transient()' );

		return \get_transient( $transient );
	}

	/**
	 * Returns left or right, for the home separator location.
	 *
	 * This method fetches the default option because it's conditional (LTR/RTL).
	 *
	 * @since 2.5.2
	 * @since 2.8.0 Method is now public.
	 * @since 4.3.0 1. No longer falls back to option or default option, but a language-based default instead.
	 *              2. Deprecated.
	 *
	 * @param mixed $position Should ideally be a string 'left' or 'right' passed in.
	 * @return string left or right
	 */
	public function s_left_right_home( $position ) {

		$tsf = \tsf();

		$tsf->_deprecated_function( 'tsf()->s_left_right_home()', '4.3.0', 'tsf()->s_left_right()' );

		return $tsf->s_left_right( $position );
	}
}
