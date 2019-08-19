<?php
/**
 * @package The_SEO_Framework\API
 */

namespace {
	defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;
}

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

namespace {
	/**
	 * Returns the class from cache.
	 *
	 * This is the recommended way of calling the class, if needed.
	 * Call this after action 'init' priority 0 otherwise it will kill the plugin,
	 * or even other plugins.
	 *
	 * @since 2.2.5
	 *
	 * @return null|object The plugin class object.
	 */
	function the_seo_framework() {
		return The_SEO_Framework\_init_tsf();
	}

	/**
	 * Returns the database version of TSF.
	 *
	 * @since 3.1.0
	 * @since 3.1.2 Now casts to string.
	 *
	 * @return string The database version. '0' if version isn't found.
	 */
	function the_seo_framework_db_version() {
		return (string) get_option( 'the_seo_framework_upgraded_db_version', '0' );
	}

	/**
	 * Returns the facade class name from cache.
	 *
	 * CAUTION: If this is used before plugins_loaded priority 5, then the plugin
	 * will fail to load views.
	 *
	 * @since 2.7.0
	 * @since 2.8.0: Added did_action and current_action check.
	 *
	 * @return string|bool The SEO Framework class name. False if The SEO Framework isn't loaded.
	 */
	function the_seo_framework_class() {

		static $class = null;

		if ( isset( $class ) )
			return $class;

		if ( ! ( did_action( 'plugins_loaded' ) || current_action( 'plugins_loaded' ) ) )
			return false;

		return $class = get_class( the_seo_framework() );
	}
}

namespace The_SEO_Framework {
	/**
	 * Determines whether this plugin should load.
	 *
	 * @since 2.8.0
	 * @access private
	 * @staticvar bool $load
	 * @action plugins_loaded
	 *
	 * @return bool Whether to allow loading of plugin.
	 */
	function _can_load() {

		static $load = null;

		if ( isset( $load ) )
			return $load;

		/**
		 * @since 2.3.7
		 * @param bool $load
		 */
		return $load = (bool) \apply_filters( 'the_seo_framework_load', true );
	}

	/**
	 * Requires trait files once.
	 *
	 * @since 3.1.0
	 * @uses THE_SEO_FRAMEWORK_DIR_PATH_TRAIT
	 * @access private
	 * @staticvar array $loaded
	 *
	 * @param string $file Where the trait is for. Must be lowercase.
	 * @return bool True if loaded, false otherwise.
	 */
	function _load_trait( $file ) {

		static $loaded = [];

		if ( isset( $loaded[ $file ] ) )
			return $loaded[ $file ];

		$_file = str_replace( '/', DIRECTORY_SEPARATOR, $file );

		return $loaded[ $file ] = (bool) require THE_SEO_FRAMEWORK_DIR_PATH_TRAIT . $_file . '.trait.php';
	}

	/**
	 * Determines if the method or function has already run.
	 *
	 * @since 3.1.0
	 * @access private
	 * @staticvar array $cache
	 *
	 * @param string $caller The method or function that calls this.
	 * @return bool True if already called, false otherwise.
	 */
	function _has_run( $caller ) {

		static $cache = [];

		return isset( $cache[ $caller ] ) ?: ( ( $cache[ $caller ] = true ) && false );
	}

	/**
	 * Adds and returns-to the bootstrap timer.
	 *
	 * @since 4.0.0
	 * @access private
	 * @staticvar $time The estimated total time for bootstrapping.
	 *
	 * @param int $add The time to add.
	 * @return int The accumulated time, roughly.
	 */
	function _bootstrap_timer( $add = 0 ) {
		static $time  = 0;
		return $time += $add;
	}
}
