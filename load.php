<?php
/**
 * @package The_SEO_Framework
 */
use The_SEO_Framework\Load as Load;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

defined( 'ABSPATH' ) or die;

add_action( 'admin_init', 'the_seo_framework_upgrade', 5 );
/**
 * Determines whether the plugin needs an option upgrade.
 *
 * @since 2.7.0
 * @action admin_init
 * @priority
 */
function the_seo_framework_upgrade() {

	if ( false === the_seo_framework_active() )
		return;

	if ( get_option( 'the_seo_framework_upgraded_db_version' ) >= THE_SEO_FRAMEWORK_DB_VERSION )
		return;

	require_once( THE_SEO_FRAMEWORK_DIR_PATH_FUNCT . 'upgrade.php' );
}

add_action( 'plugins_loaded', 'the_seo_framework_init', 5 );
/**
 * Load The_SEO_Framework_Load class
 *
 * @action plugins_loaded
 * @priority 5 Use anything above 5, or any action later than plugins_loaded and
 * you can access the class and functions.
 *
 * @since 2.2.5
 * @staticvar object $tsf
 *
 * @return object|null The SEO Framework Facade class object. Null on failure.
 */
function the_seo_framework_init() {

	//* Cache the class. Do not run everything more than once.
	static $tsf = null;

	if ( the_seo_framework_active() ) {
		if ( null === $tsf ) {
			//* Register autoloader.
			spl_autoload_register( '_autoload_the_seo_framework_classes' );

			/**
			 * @package The_SEO_Framework
			 */
			$tsf = new Load();
		}
	}

	return $tsf;
}

/**
 * Determines whether this plugin should load.
 *
 * @since 2.3.7
 * @staticvar bool $loaded
 * Applies filters 'the_seo_framework_load' : bool
 *
 * @action plugins_loaded
 * @return bool Whether to allow loading of plugin.
 */
function the_seo_framework_load() {

	static $loaded = null;

	if ( isset( $loaded ) )
		return $loaded;

	return $loaded = (bool) apply_filters( 'the_seo_framework_load', true );
}

/**
 * Load compat and API files.
 * @since 2.1.6
 * @uses THE_SEO_FRAMEWORK_DIR_PATH_FUNCT
 */
require_once( THE_SEO_FRAMEWORK_DIR_PATH_FUNCT . 'compat.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_FUNCT . 'optionsapi.php' );

/**
 * Autoloads all class files. To be used when requiring access to all or any of
 * the plugin classes.
 *
 * @since 1.0.0
 * @uses THE_SEO_FRAMEWORK_DIR_PATH_CLASS
 * @access private
 * @staticvar array $loaded Whether $class has been loaded.
 *
 * @NOTE 'The_SEO_Framework' is a reserved namespace. Using it outside of this plugin's scope will result in an error.
 *
 * @param string $class The class name.
 * @return bool False if file hasn't yet been included, otherwise true.
 */
function _autoload_the_seo_framework_classes( $class ) {

	if ( 0 !== strpos( $class, 'The_SEO_Framework\\', 0 ) )
		return;

	static $loaded = array();

	if ( isset( $loaded[ $class ] ) )
		return true;

	if ( false !== strpos( $class, '_Abstract' ) ) {
		$path = THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'abstract' . DIRECTORY_SEPARATOR;
	} else {
		$path = THE_SEO_FRAMEWORK_DIR_PATH_CLASS;
	}

	$_class = strtolower( str_replace( 'The_SEO_Framework\\', '', $class ) );
	$_class = str_replace( '_abstract', '.abstract', $_class );
	$_class = str_replace( '_', '-', $_class );

	return $loaded[ $class ] = require_once( $path . $_class . '.class.php' );
}
