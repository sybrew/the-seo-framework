<?php
/**
 * @package The_SEO_Framework\Traits\Internal\Deprecated
 * @subpackage The_SEO_Framework\Debug\Deprecated
 */

namespace The_SEO_Framework\Traits\Internal;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Trait The_SEO_Framework\Traits\Internal\Static_Deprecator
 * Holds deprecation handler for static classes.
 *
 * @since 5.0.0
 * @access private
 * @see \The_SEO_Framework\Pool
 *
 * @property class[] $subpool The class subpool store. Used in favor of memo() for a chain would become expensive.
 * @property string $colloquial_handle
 * @property array  $deprecated_methods : {
 *     @param string $name The method name => {
 *        @param string   since       The TSF version of deprecation,
 *        @param string   alternative The alternative call,
 *        @param callable fallback    The fallback callback,
 *     }
 * }
 * @property array $deprecated_properties : {
 *     @param string $name The property name => {
 *        @param string since       The TSF version of deprecation,
 *        @param string alternative The alternative call,
 *        @param mixed  fallback    The fallback value,
 *     }
 * }
 */
trait Static_Deprecator {

	/**
	 * @since 5.0.0
	 * @var class[] The class subpool store. Used in favor of memo() for a chain would become expensive.
	 */
	private static $subpool = [];

	/**
	 * Handles unapproachable invoked properties.
	 *
	 * Makes sure deprecated properties are still overwritten.
	 * If the property never existed, default PHP behavior is invoked.
	 *
	 * @since 5.0.0
	 *
	 * @param string $name  The property name.
	 * @param mixed  $value The property value.
	 */
	final public function __set( $name, $value ) {

		$deprecated = $this->deprecated_properties[ $name ] ?? '';

		if ( $deprecated ) {
			$alternative = $deprecated['alternative'] ?? '';
			$since       = $deprecated['since'] ?? '';

			\tsf()->_inaccessible_p_or_m(
				"$$name",
				trim(
					\sprintf(
						'%s;%s',
						$since ? "Since $since of The SEO Framework" : '',
						$alternative ? " Use $alternative instead" : '',
						$this->colloquial_handle,
					),
					'; ',
				),
				$this->colloquial_handle,
			);
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
	 * @since 5.0.0
	 *
	 * @param string $name The property name.
	 * @return mixed
	 */
	final public function __get( $name ) {

		$deprecated = $this->deprecated_properties[ $name ] ?? '';

		if ( $deprecated ) {
			$alternative = $deprecated['alternative'] ?? '';
			$since       = $deprecated['since'] ?? '';

			\tsf()->_inaccessible_p_or_m(
				"$$name",
				trim(
					\sprintf(
						'%s;%s',
						$since ? "Since $since of The SEO Framework" : '',
						$alternative ? " Use $alternative instead" : '',
						$this->colloquial_handle,
					),
					'; ',
				),
				$this->colloquial_handle,
			);

			if ( $deprecated['fallback'] )
				return $deprecated['fallback'];
		} else {
			\tsf()->_inaccessible_p_or_m( "$$name" );
		}
	}

	/**
	 * Handles unapproachable invoked methods.
	 *
	 * @since 5.0.0
	 *
	 * @param string $name      The method name.
	 * @param array  $arguments The method arguments.
	 * @return mixed|void
	 */
	final public function __call( $name, $arguments ) {

		$deprecated = $this->deprecated_methods[ $name ] ?? '';

		if ( $deprecated ) {
			\tsf()->_deprecated_function(
				\esc_html( "{$this->colloquial_handle}->$name()" ), // redundant escape
				\esc_html( $deprecated['since'] ?? '' ),            // redundant escape
				! empty( $deprecated['alternative'] ) ? \esc_html( $deprecated['alternative'] ) : null,
			);

			$fallback = $deprecated['fallback'] ?? null;

			if ( $fallback )
				return \call_user_func_array( $fallback, $arguments );
		} else {
			\tsf()->_inaccessible_p_or_m( "{$this->colloquial_handle}->$name()" );
		}
	}

	/**
	 * Handles unapproachable invoked static methods.
	 *
	 * @since 5.0.5
	 *
	 * @param string $name      The method name.
	 * @param array  $arguments The method arguments.
	 * @return void
	 */
	final public static function __callStatic( $name, $arguments ) { // phpcs:ignore, VariableAnalysis.CodeAnalysis -- __callStatic must take 2 args.
		\tsf()->_inaccessible_p_or_m(
			\esc_html( "$name()" ),
			'Method is of unknown pool. Do not call pool methods statically! A fatal error might follow.',
		);
	}
}
