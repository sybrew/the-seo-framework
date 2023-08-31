<?php
/**
 * @package The_SEO_Framework\Classes\Internal\Deprecated
 * @subpackage The_SEO_Framework\Debug\Deprecated
 */

namespace The_SEO_Framework\Traits\Internal;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\Internal\Static_Deprecator
 * Holds deprecation handler for static classes.
 *
 * @since 4.3.0
 * @access private
 * @see \The_SEO_Framework\Pool
 *
 * @property string $colloquial_handle
 * @property array  $deprecated_methods : {
 *     @param string $name => {
 *        @param string   since
 *        @param string   alternative
 *        @param callable fallback
 *     }
 * }
 * @property array $deprecated_properties : {
 *     @param string $name => {
 *        @param string since
 *        @param string alternative
 *        @param mixed  fallback
 *     }
 * }
 */
trait Static_Deprecator {

	/**
	 * Handles unapproachable invoked properties.
	 *
	 * Makes sure deprecated properties are still overwritten.
	 * If the property never existed, default PHP behavior is invoked.
	 *
	 * @since 4.3.0
	 *
	 * @param string $name  The property name.
	 * @param mixed  $value The property value.
	 */
	final public function __set( $name, $value ) {

		$deprecation = $this->deprecated_properties[ $name ] ?? '';
		if ( $deprecation ) {
			$alternative = $deprecation['alternative'] ?? '';
			$since       = $deprecation['since'] ?? '';

			\tsf()->_inaccessible_p_or_m(
				"$$name",
				trim(
					sprintf(
						'%s;%s',
						$since ? "Since $since of The SEO Framework" : '',
						$alternative ? " Use $alternative instead" : '',
						$this->colloquial_handle
					),
					'; '
				),
				$this->colloquial_handle
			);

			if ( $deprecation['fallback'] )
				return $deprecation['fallback'];
		} else {
			/**
			 * For now, no deprecation is being handled; as no properties have been deprecated. Just removed.
			 */
			\tsf()->_inaccessible_p_or_m( "$$name", 'unknown' );

			// Invoke default behavior: Write variable if it's not protected.
			if ( property_exists( self, $name ) )
				self::$name = $value;
		}
	}

	/**
	 * Handles unapproachable invoked properties.
	 *
	 * Makes sure deprecated properties are still accessible.
	 *
	 * @since 4.3.0
	 *
	 * @param string $name The property name.
	 * @return mixed
	 */
	final public function __get( $name ) {

		$deprecation = $this->deprecated_properties[ $name ] ?? '';

		if ( $deprecation ) {
			$alternative = $deprecation['alternative'] ?? '';
			$since       = $deprecation['since'] ?? '';

			\tsf()->_inaccessible_p_or_m(
				"$$name",
				trim(
					sprintf(
						'%s;%s',
						$since ? "Since $since of The SEO Framework" : '',
						$alternative ? " Use $alternative instead" : '',
						$this->colloquial_handle
					),
					'; '
				),
				$this->colloquial_handle
			);

			if ( $deprecation['fallback'] )
				return $deprecation['fallback'];
		} else {
			\tsf()->_inaccessible_p_or_m( "$$name" );
		}
	}

	/**
	 * Handles unapproachable invoked methods.
	 *
	 * @since 4.3.0
	 *
	 * @param string $name      The method name.
	 * @param array  $arguments The method arguments.
	 * @return mixed|void
	 */
	final public function __call( $name, $arguments ) {

		$deprecation = $this->deprecated_methods[ $name ] ?? '';

		if ( $deprecation ) {
			\tsf()->_deprecated_function(
				\esc_html( "{$this->colloquial_handle}::$name" ),
				\esc_html( $deprecation['since'] ?? '' ),
				\esc_html( $deprecation['alternative'] ?? '' ),
			);

			$fallback = $deprecation['fallback'] ?? null;

			if ( $fallback )
				return \call_user_func_array( $fallback, $arguments );
		} else {
			\tsf()->_inaccessible_p_or_m( \esc_html( "{$this->colloquial_handle}::$name" ), 'unknown' );
		}
	}
}
