<?php
/**
* @package The_SEO_Framework/Bootstrap
*/
namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2018 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * File located in plugin folder autodescription/language/
 * @since 1.0.0
 */
function _init_locale() {
	\load_plugin_textdomain(
		'autodescription',
		false,
		THE_SEO_FRAMEWORK_DIR_PATH . 'language' . DIRECTORY_SEPARATOR
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
 * @staticvar object $tsf
 *
 * @return object|null The SEO Framework Facade class object. Null on failure.
 */
function _init_tsf() {

	//* Cache the class. Do not run constructors more than once.
	static $tsf = null;

	if ( $tsf )
		return $tsf;

	if ( false === \doing_action( 'plugins_loaded' ) )
		\wp_die( 'Use the_seo_framework() after action `plugins_loaded` priority 5.' );

	/**
	 * @package The_SEO_Framework
	 */
	if ( \The_SEO_Framework\_can_load() ) {
		if ( \is_admin() ) {
			//! TODO: admin loader.
			$tsf = new \The_SEO_Framework\Load();

			/**
			 * Runs after TSF is initialized in the admin.
			 * @since 3.1.0
			 */
			\do_action( 'the_seo_framework_admin_initialized' );
		} else {
			$tsf = new \The_SEO_Framework\Load();
		}

		/**
		 * Runs after TSF is initialized.
		 * @since 3.1.0
		 */
		\do_action( 'the_seo_framework_initialized' );
	} else {
		$tsf = new \The_SEO_Framework\Silencer();
	}

	return $tsf;
}

\The_SEO_Framework\_register_autoloader();
/**
 * Registers The SEO Framework's autoloader.
 *
 * @since 3.1.0
 * @access private
 */
function _register_autoloader() {
	spl_autoload_register( __NAMESPACE__ . '\\_autoload_classes', true, true );
}

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

	if ( strpos( $class, '_Interface' ) ) {
		$path = THE_SEO_FRAMEWORK_DIR_PATH_INTERFACE;
		$extension = '.interface.php';
	} else {
		$path = THE_SEO_FRAMEWORK_DIR_PATH_CLASS;
		$extension = '.class.php';
	}

	$class = strtolower( str_replace( __NAMESPACE__ . '\\', '', $class ) );
	$class = str_replace( '_interface', '', $class );
	$class = str_replace( '_', '-', $class );

	require $path . $class . $extension;
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
	require THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'activation.php';
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
	require THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'deactivation.php';
}
