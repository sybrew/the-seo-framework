<?php
/**
* @package The_SEO_Framework
*/
namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_DIR_PATH' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

\add_action( 'plugins_loaded', __NAMESPACE__ . '\\_init', 5 );
/**
 * Load The_SEO_Framework_Load class
 *
 * @action plugins_loaded
 * @priority 5 Use anything above 5, or any action later than plugins_loaded and
 * you can access the class and functions.
 *
 * @since 2.2.5
 * @since 2.8.0: Added namespace and renamed function.
 * @access private
 * @staticvar object $tsf
 *
 * @return object|null The SEO Framework Facade class object. Null on failure.
 */
function _init() {

	//* Cache the class. Do not run constructors more than once.
	static $tsf = null;

	if ( null === $tsf && \The_SEO_Framework\_can_load() ) {
		//* Register autoloader.
		spl_autoload_register( __NAMESPACE__ . '\\_autoload_classes' );

		$tsf = new \The_SEO_Framework\Load();
	}

	return $tsf;
}

\add_action( 'plugins_loaded', __NAMESPACE__ . '\\_init_locale', 10 );
/**
 * Plugin locale 'autodescription'
 * File located in plugin folder autodescription/language/
 * @since 1.0.0
 */
function _init_locale() {
	\load_plugin_textdomain( 'autodescription', false, basename( dirname( __FILE__ ) ) . '/language/' );
}

\add_action( 'admin_init', __NAMESPACE__ . '\\_init_upgrade', 5 );
/**
 * Determines whether the plugin needs an option upgrade.
 *
 * @since 2.7.0
 * @since 2.8.0: Added namespace and renamed function.
 * @access private
 * @action admin_init
 * @priority 5
 *
 * @return void Early if no upgrade can or must take place.
 */
function _init_upgrade() {

	if ( false === \The_SEO_Framework\_can_load() )
		return;

	if ( \get_option( 'the_seo_framework_upgraded_db_version' ) >= THE_SEO_FRAMEWORK_DB_VERSION )
		return;

	require THE_SEO_FRAMEWORK_DIR_PATH_FUNCT . 'upgrade.php';
}

/**
 * Determines whether this plugin should load.
 *
 * @since 2.3.7
 * @since 2.8.0: Added namespace and renamed function.
 * @access private
 * @staticvar bool $loaded
 *
 * @action plugins_loaded
 * @return bool Whether to allow loading of plugin.
 */
function _can_load() {

	static $loaded = null;

	if ( isset( $loaded ) )
		return $loaded;

	/**
	 * Applies filters 'the_seo_framework_load' : bool
	 * @since 2.3.7
	 */
	return $loaded = (bool) \apply_filters( 'the_seo_framework_load', true );
}

/**
 * Autoloads all class files. To be used when requiring access to all or any of
 * the plugin classes.
 *
 * @since 2.8.0
 * @uses THE_SEO_FRAMEWORK_DIR_PATH_CLASS
 * @access private
 * @staticvar array $loaded Whether $class has been loaded.
 *
 * @NOTE 'The_SEO_Framework' is a reserved namespace. Using it outside of this plugin's scope will result in an error.
 *
 * @param string $class The class name.
 * @return bool False if file couldn't be included, otherwise true.
 */
function _autoload_classes( $class ) {

	if ( 0 !== strpos( $class, 'The_SEO_Framework\\', 0 ) )
		return;

	static $loaded = array();

	if ( isset( $loaded[ $class ] ) )
		return $loaded[ $class ];

	if ( false !== strpos( $class, '_Interface' ) ) {
		$path = THE_SEO_FRAMEWORK_DIR_PATH_INTERFACE;
		$extension = '.interface.php';
	} else {
		$path = THE_SEO_FRAMEWORK_DIR_PATH_CLASS;
		$extension = '.class.php';
	}

	$_class = strtolower( str_replace( 'The_SEO_Framework\\', '', $class ) );
	$_class = str_replace( '_interface', '', $_class );
	$_class = str_replace( '_', '-', $_class );

	return $loaded[ $class ] = (bool) require $path . $_class . $extension;
}

\add_action( 'activate_' . THE_SEO_FRAMEWORK_PLUGIN_BASENAME, __NAMESPACE__ . '\\_do_plugin_activation' );
/**
 * Performs plugin activation actions.
 *
 * @since 2.6.6
 * @since 2.8.0: Added namespace and renamed function. Also performs PHP tests now.
 * @access private
 */
function _do_plugin_activation() {
	require THE_SEO_FRAMEWORK_DIR_PATH_FUNCT . 'plugin-activation.php';
}

\add_action( 'deactivate_' . THE_SEO_FRAMEWORK_PLUGIN_BASENAME, __NAMESPACE__ . '\\_do_plugin_deactivation' );
/**
 * Performs plugin deactivation actions.
 *
 * @since 2.6.6
 * @since 2.8.0: Added namespace and renamed function.
 * @access private
 */
function _do_plugin_deactivation() {
	require THE_SEO_FRAMEWORK_DIR_PATH_FUNCT . 'plugin-deactivation.php';
}
