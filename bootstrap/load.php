<?php
/**
 * @package The_SEO_Framework
 * @subpackage Bootstrap
 * @TODO change namespace to The_SEO_Framework\Bootstrap
 *       in a future major release.
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

\add_action( 'plugins_loaded', __NAMESPACE__ . '\\_init_locale', 4 );
/**
 * Plugin locale 'autodescription'
 * Files located in plugin folder `../autodescription/language/`
 * @since 2.8.0
 */
function _init_locale() {
	/**
	 * @since 1.0.0
	 */
	\load_plugin_textdomain(
		'autodescription',
		false,
		THE_SEO_FRAMEWORK_DIR_PATH . 'language'
	);
}

\add_action( 'plugins_loaded', __NAMESPACE__ . '\\_init_tsf', 5 );
/**
 * Load The_SEO_Framework_Load class
 *
 * @action plugins_loaded
 * @priority 5 Use anything above 5, or any action later than plugins_loaded and
 * you can access the class and functions.
 *
 * @since 3.1.0
 * @access private
 * @see function the_seo_framework().
 * @staticvar object $tsf
 * @factory
 *
 * @return object|null The SEO Framework Facade class object. Null on failure.
 */
function _init_tsf() {

	//* Cache the class. Do not run constructors more than once.
	static $tsf = null;

	if ( $tsf )
		return $tsf;

	/**
	 * @package The_SEO_Framework
	 */
	if ( \The_SEO_Framework\_can_load() ) {
		if ( \is_admin() ) {
			//! TODO: admin-only loader.
			$tsf         = new \The_SEO_Framework\Load();
			$tsf->loaded = true;

			/**
			 * Runs after TSF is loaded in the admin.
			 * @since 3.1.0
			 */
			\do_action( 'the_seo_framework_admin_loaded' );
		} else {
			$tsf         = new \The_SEO_Framework\Load();
			$tsf->loaded = true;
		}

		/**
		 * Runs after TSF is loaded.
		 * @since 3.1.0
		 */
		\do_action( 'the_seo_framework_loaded' );
	} else {
		$tsf         = new \The_SEO_Framework\Silencer();
		$tsf->loaded = false;
	}

	// did_action() checks for current action too.
	if ( false === \did_action( 'plugins_loaded' ) )
		$tsf->_doing_it_wrong( 'the_seo_framework() or ' . __FUNCTION__, 'Use <code>the_seo_framework()</code> after action <code>plugins_loaded</code> priority 5.', '3.1' );

	return $tsf;
}

spl_autoload_register( __NAMESPACE__ . '\\_autoload_classes', true, true );
/**
 * Autoloads all class files. To be used when requiring access to all or any of
 * the plugin classes.
 *
 * @since 2.8.0
 * @since 3.1.0 1. No longer maintains cache.
 *              2. Now always returns void.
 * @uses THE_SEO_FRAMEWORK_DIR_PATH_CLASS
 * @access private
 *
 * @NOTE 'The_SEO_Framework\' is a reserved namespace. Using it outside of this
 *       plugin's scope coul result in an error.
 *
 * @param string $class The class name.
 * @return void Early if the class is not within the current namespace.
 */
function _autoload_classes( $class ) {

	if ( 0 !== strpos( $class, 'The_SEO_Framework\\', 0 ) )
		return;

	$strip = 'The_SEO_Framework\\';

	if ( strpos( $class, '_Interface' ) ) {
		$path      = THE_SEO_FRAMEWORK_DIR_PATH_INTERFACE;
		$extension = '.interface.php';
		$class     = str_replace( '_Interface', '', $class );
	} else {
		$path      = THE_SEO_FRAMEWORK_DIR_PATH_CLASS;
		$extension = '.class.php';

		//: substr_count( $class, '\\', 2 ) >= 2 // strrpos... str_split...
		if ( 0 === strpos( $class, 'The_SEO_Framework\\Builders\\' ) ) {
			$path  .= 'builders' . DIRECTORY_SEPARATOR;
			$strip .= 'Builders\\';
		}
	}

	$class = strtolower( str_replace( $strip, '', $class ) );
	$class = str_replace( '_', '-', $class );

	require $path . $class . $extension;
}

\add_action( 'activate_' . THE_SEO_FRAMEWORK_PLUGIN_BASENAME, __NAMESPACE__ . '\\_do_plugin_activation' );
/**
 * Performs plugin activation actions.
 *
 * @since 2.8.0
 * @access private
 */
function _do_plugin_activation() {
	require THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'activation.php';
}

\add_action( 'deactivate_' . THE_SEO_FRAMEWORK_PLUGIN_BASENAME, __NAMESPACE__ . '\\_do_plugin_deactivation' );
/**
 * Performs plugin deactivation actions.
 *
 * @since 2.8.0
 * @access private
 */
function _do_plugin_deactivation() {
	require THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'deactivation.php';
}
