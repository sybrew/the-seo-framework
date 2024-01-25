<?php
/**
 * @package The_SEO_Framework\Classes\Helper\View
 */

namespace The_SEO_Framework\Helper;

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
 * Holds API interfaces for screen views/templates.
 *
 * @since 5.0.0
 * @access private
 */
final class Template {

	/**
	 * @since 5.0.0
	 * @var ?string $secret The include secret.
	 */
	private static $secret;

	/**
	 * Outputs a template.
	 *
	 * Adds a `$secret` to the file to prevent including without this class.
	 * This make file inclusions difficult when the plugin is dormant (deactivated).
	 *
	 * @since 5.0.0
	 * @access private
	 *
	 * @param string $file         The relative view file name.
	 * @param array  ...$view_args The arguments to be supplied to the file.
	 */
	public static function output_view( $file, ...$view_args ) { // phpcs:ignore, VariableAnalysis.CodeAnalysis -- includes.

		// phpcs:ignore, VariableAnalysis.CodeAnalysis -- includes.
		$secret = static::$secret = uniqid( '', true );

		// This will crash on PHP 8+ if the view isn't resolved. That's good.
		require static::get_view_location( $file );
	}

	/**
	 * Outputs a template via absolute file location.
	 * This function is considered insecure for dynamically created paths.
	 *
	 * Adds a `$secret` to the file to prevent including without this class.
	 * This make file inclusions difficult when the plugin is dormant (deactivated).
	 *
	 * @since 5.0.0
	 * @access private
	 *
	 * @param string $file         The absolute view file name.
	 * @param array  ...$view_args The arguments to be supplied to the file.
	 */
	public static function output_absolute_view( $file, ...$view_args ) { // phpcs:ignore, VariableAnalysis.CodeAnalysis -- includes.

		// phpcs:ignore, VariableAnalysis.CodeAnalysis -- includes.
		$secret = static::$secret = uniqid( '', true );

		require $file;
	}

	/**
	 * Gets view location. Forces a path on our Views folder.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 * @access private
	 *
	 * @param string $file The file name.
	 * @return ?string The view location. Null on failure.
	 */
	public static function get_view_location( $file ) {

		static $realview;

		$realview ??= realpath( \THE_SEO_FRAMEWORK_DIR_PATH_VIEWS );
		$path       = realpath( "$realview/$file.php" );

		if ( $path && str_starts_with( $path, $realview ) )
			return $path;

		return null;
	}

	/**
	 * Verifies view secret.
	 *
	 * @since 5.0.0
	 * @access private
	 *
	 * @param string $value The value to match against secret.
	 * @return bool
	 */
	public static function verify_secret( $value ) {
		return isset( $value ) && static::$secret === $value;
	}
}
