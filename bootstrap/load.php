<?php
/**
 * @package The_SEO_Framework\Bootstrap
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2018 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Loads plugin locale 'autodescription'.
 * Files located in plugin folder `../autodescription/language/`
 *
 * @since 2.8.0
 * @since 4.0.2 Now points to the correct plugin folder for fallback MO-file loading (which was never used).
 */
function _init_locale() {
	/**
	 * @since 1.0.0
	 */
	\load_plugin_textdomain(
		'autodescription',
		false,
		\dirname( \THE_SEO_FRAMEWORK_PLUGIN_BASENAME ) . \DIRECTORY_SEPARATOR . 'language'
	);
}

\add_action( 'plugins_loaded', __NAMESPACE__ . '\\_init_tsf', 5 );
/**
 * Loads and memoizes `\The_SEO_Framework\Load` class.
 *
 * Runs at action `plugins_loaded`, priority `5`. So, use anything above 5, or any
 * action later than plugins_loaded and you can access the class and functions.
 *
 * @since 3.1.0
 * @access private
 * @see function tsf().
 * @see function tsf().
 * @factory
 *
 * @return object|null The SEO Framework Facade class object. Null on failure.
 */
function _init_tsf() {

	// Memoize the class. Do not run constructors more than once.
	static $tsf = null;

	if ( $tsf )
		return $tsf;

	/**
	 * @since 2.3.7
	 * @param bool $load
	 */
	if ( \apply_filters( 'the_seo_framework_load', true ) ) {
		$tsf         = new Load();
		$tsf->loaded = true;

		$tsf->_load_early_compat_files();

		if ( \is_admin() ) {
			//! TODO: admin-only loader?
			/**
			 * @since 3.1.0
			 * Runs after TSF is loaded in the admin.
			 */
			\do_action( 'the_seo_framework_admin_loaded' );
		}

		/**
		 * @since 3.1.0
		 * Runs after TSF is loaded.
		 */
		\do_action( 'the_seo_framework_loaded' );
	} else {
		$tsf         = new Internal\Silencer();
		$tsf->loaded = false;
	}

	// did_action() checks for current action too.
	if ( ! \did_action( 'plugins_loaded' ) )
		$tsf->_doing_it_wrong( 'tsf(), the_seo_framework(), or ' . __FUNCTION__, 'Use <code>tsf()</code> after action <code>plugins_loaded</code> priority 5.', '3.1' );

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
 * @since 4.0.0 1. Streamlined folder lookup by more effectively using the namespace.
 *              2. Added timing functionality
 *              3. No longer loads interfaces automatically.
 * @since 4.2.0 Now supports mixed class case.
 * @uses THE_SEO_FRAMEWORK_DIR_PATH_CLASS
 * @access private
 *
 * @NOTE 'The_SEO_Framework\' is a reserved namespace. Using it outside of this
 *       plugin's scope could result in an error.
 *
 * @param string $class The class name.
 * @return void Early if the class is not within the current namespace.
 */
function _autoload_classes( $class ) {

	$class = strtolower( $class );

	// It's The_SEO_Framework, not the_seo_framework! -- Sybre's a nightmare, honestly! No wonder he hasn't got any friends.
	if ( ! str_starts_with( $class, 'the_seo_framework\\' ) ) return;

	static $_timenow = true;
	// Lock $_timenow to prevent stacking timers during class extending. This is released when the class stack loaded.
	if ( $_timenow ) {
		$_bootstrap_timer = hrtime( true );
		$_timenow         = false;
	} else {
		$_bootstrap_timer = 0;
	}

	$_chunks      = explode( '\\', $class );
	$_chunk_count = \count( $_chunks );

	if ( $_chunk_count > 2 ) {
		// directory position = $_chunk_count - ( 2 = (The_SEO_Framework)\ + (Bridges/Builders/Interpreters)\ )
		$rel_dir = implode( \DIRECTORY_SEPARATOR, array_splice( $_chunks, 1, $_chunk_count - 2 ) ) . \DIRECTORY_SEPARATOR;
	} else {
		$rel_dir = '';
	}

	// The last part of the chunks is the class name--which corresponds to the file.
	$file = str_replace( '_', '-', end( $_chunks ) );

	// The extension is deemed to be ".class.php" always. We may wish to alter this for traits?
	require \THE_SEO_FRAMEWORK_DIR_PATH_CLASS . "{$rel_dir}{$file}.class.php";

	if ( $_bootstrap_timer ) {
		_bootstrap_timer( ( hrtime( true ) - $_bootstrap_timer ) / 1e9 );
		$_timenow = true;
	}
}

\add_action( 'activate_' . \THE_SEO_FRAMEWORK_PLUGIN_BASENAME, __NAMESPACE__ . '\\_do_plugin_activation' );
/**
 * Performs plugin activation actions.
 *
 * @since 2.8.0
 * @access private
 */
function _do_plugin_activation() {
	require \THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'activation.php';
}

\add_action( 'deactivate_' . \THE_SEO_FRAMEWORK_PLUGIN_BASENAME, __NAMESPACE__ . '\\_do_plugin_deactivation' );
/**
 * Performs plugin deactivation actions.
 *
 * @since 2.8.0
 * @access private
 */
function _do_plugin_deactivation() {
	require \THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'deactivation.php';
}

/**
 * Adds and returns-to the memoized bootstrap timer.
 *
 * @since 4.0.0
 * @access private
 *
 * @param int $add The time to add.
 * @return int The accumulated time, roughly.
 */
function _bootstrap_timer( $add = 0 ) {

	static $time = 0;

	$time += $add;
	return $time;
}
