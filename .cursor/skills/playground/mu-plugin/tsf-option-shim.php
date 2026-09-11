<?php
/**
 * @package The_SEO_Framework\Playground
 */

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2026 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

add_filter(
	'wp_plugin_regression_update_option',
	/**
	 * Routes TSF site settings through Data\Plugin::update_option().
	 *
	 * @since 5.1.5
	 *
	 * @param bool   $handled Whether a consumer already wrote the option.
	 * @param string $name    Option name.
	 * @param mixed  $value   Option value.
	 * @return bool
	 */
	function ( $handled, $name, $value ) {

		if ( $handled )
			return $handled;

		if ( ! defined( 'THE_SEO_FRAMEWORK_PRESENT' ) )
			return $handled;

		$option = defined( 'THE_SEO_FRAMEWORK_SITE_OPTIONS' ) ? THE_SEO_FRAMEWORK_SITE_OPTIONS : 'autodescription-site-settings';

		if ( $option !== $name )
			return $handled;

		if ( ! is_array( $value ) )
			return $handled;

		The_SEO_Framework\Data\Plugin::update_option( $value );

		return true;
	},
	10,
	3,
);

add_filter(
	'wp_plugin_regression_update_meta',
	/**
	 * Routes TSF post and term meta through Data\Plugin\Post and Data\Plugin\Term.
	 *
	 * @since 5.1.5
	 *
	 * @param bool   $handled Whether a consumer already wrote the meta.
	 * @param int    $id      Object ID.
	 * @param string $key     Meta key.
	 * @param mixed  $value   Meta value.
	 * @param string $type    `post` or `term`.
	 * @return bool
	 */
	function ( $handled, $id, $key, $value, $type ) {

		if ( $handled )
			return $handled;

		if ( ! defined( 'THE_SEO_FRAMEWORK_PRESENT' ) )
			return $handled;

		if ( 'term' === $type ) {
			$bag = defined( 'THE_SEO_FRAMEWORK_TERM_OPTIONS' )
				? THE_SEO_FRAMEWORK_TERM_OPTIONS
				: 'autodescription-term-settings';

			if ( $bag === $key ) {
				if ( ! is_array( $value ) )
					return $handled;

				The_SEO_Framework\Data\Plugin\Term::save_meta( $id, $value );

				return true;
			}

			if ( ! array_key_exists(
				$key,
				The_SEO_Framework\Data\Plugin\Term::get_default_meta(),
			) )
				return $handled;

			The_SEO_Framework\Data\Plugin\Term::update_single_meta_item( $key, $value, $id );

			return true;
		}

		if ( ! array_key_exists(
			$key,
			The_SEO_Framework\Data\Plugin\Post::get_default_meta(),
		) )
			return $handled;

		The_SEO_Framework\Data\Plugin\Post::update_single_meta_item( $key, $value, $id );

		return true;
	},
	10,
	5,
);
