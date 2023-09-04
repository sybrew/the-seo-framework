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
\add_action( 'plugins_loaded', __NAMESPACE__ . '\\_init_tsf', 5 );
\add_action( 'activate_' . \THE_SEO_FRAMEWORK_PLUGIN_BASENAME, __NAMESPACE__ . '\\_do_plugin_activation' );
\add_action( 'deactivate_' . \THE_SEO_FRAMEWORK_PLUGIN_BASENAME, __NAMESPACE__ . '\\_do_plugin_deactivation' );

/**
 * Loads plugin locale 'autodescription'.
 * Files located in plugin folder `../autodescription/language/`
 *
 * @hook plugins_loaded 4
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

/**
 * Loads and memoizes `\The_SEO_Framework\Load` class.
 *
 * Runs at action `plugins_loaded`, priority `5`. So, use anything above 5, or any
 * action later than plugins_loaded and you can access the class and functions.
 *
 * @hook plugins_loaded 5
 * @since 3.1.0
 * @since 4.3.0 1. Now returns an uncached silencer when called too early.
 *              2. No longer memoizes the class. Use `\tsf()` or `\the_seo_framework()` instead.
 * @access private
 * @see function tsf().
 * @see function the_seo_framework().
 * @factory
 *
 * @return The_SEO_Framework\Load|The_SEO_Framework\Internal\Silencer
 */
function _init_tsf() {

	// Memoize the class. Do not run constructors more than once.
	static $tsf;

	if ( isset( $tsf ) )
		return $tsf;

	/**
	 * @since 2.3.7
	 * @param bool $load
	 */
	if (
		   \apply_filters( 'the_seo_framework_load', true )
		&& \did_action( 'plugins_loaded' )
	) {
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
		$_tsf         = new Internal\Silencer();
		$_tsf->loaded = false;

		// did_action() checks for current action too.
		if ( ! \did_action( 'plugins_loaded' ) ) {
			\_doing_it_wrong( 'tsf(), the_seo_framework(), or ' . __FUNCTION__, 'Use <code>tsf()</code> after action <code>plugins_loaded</code> priority 5.', '3.1 or The SEO Framework' );

			return $_tsf;
		}

		// Cache the silencer.
		$tsf = $_tsf;
	}

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
 * @since 4.3.0 Now supports trait loading.
 * @uses THE_SEO_FRAMEWORK_DIR_PATH_TRAIT
 * @uses THE_SEO_FRAMEWORK_DIR_PATH_CLASS
 * @access private
 *
 * @NOTE 'The_SEO_Framework\' is a reserved namespace. Using it outside of this
 *       plugin's scope could result in an error.
 *
 * @param string $class The class or trait name.
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

	$_class_parts   = explode( '\\', str_replace( '_', '-', $class ) );
	$_rel_dir_parts = \array_slice( $_class_parts, 1, -1 );

	// The last part of the chunks is the class name--which corresponds to the file.
	$file = end( $_class_parts );

	if ( $_rel_dir_parts ) {
		if ( 'traits' === $_rel_dir_parts[0] ) {
			// Remove 'traits', otherwise we get /traits/traits/...
			unset( $_rel_dir_parts[0] );

			$rel_dir = implode( \DIRECTORY_SEPARATOR, $_rel_dir_parts ) . \DIRECTORY_SEPARATOR;

			// The extension is deemed to be ".trait.php" always.
			require \THE_SEO_FRAMEWORK_DIR_PATH_TRAIT . "{$rel_dir}{$file}.trait.php";
		} else {
			$rel_dir = implode( \DIRECTORY_SEPARATOR, $_rel_dir_parts ) . \DIRECTORY_SEPARATOR;

			// The extension is deemed to be ".class.php" always.
			require \THE_SEO_FRAMEWORK_DIR_PATH_CLASS . "{$rel_dir}{$file}.class.php";
		}
	} else {
		// Simplified version for facade class loading to improve performance.
		// The extension is deemed to be ".class.php" always.
		require \THE_SEO_FRAMEWORK_DIR_PATH_CLASS . "{$file}.class.php";
	}

	if ( $_bootstrap_timer ) {
		_bootstrap_timer( ( hrtime( true ) - $_bootstrap_timer ) / 1e9 );
		$_timenow = true;
	}
}

/**
 * Performs plugin activation actions.
 *
 * @hook activate_autodescription/autodescription.php 10
 * @since 2.8.0
 * @access private
 */
function _do_plugin_activation() {
	require \THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'activation.php';
}

/**
 * Performs plugin deactivation actions.
 *
 * @hook deactivate_autodescription/autodescription.php 10
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
