<?php
/**
 * @package The_SEO_Framework
 * @subpackage The_SEO_Framework\Bootstrap
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2018 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// Always load autoloader -- plugin (de)activation rely on these. We prepend because we safely assume ours is fastest.
spl_autoload_register( 'The_SEO_Framework\_autoload_classes', true, true );

\add_action( 'plugins_loaded', 'The_SEO_Framework\_load_tsf', 5 );
\add_action( 'activate_' . \THE_SEO_FRAMEWORK_PLUGIN_BASENAME, 'The_SEO_Framework\_do_plugin_activation' );
\add_action( 'deactivate_' . \THE_SEO_FRAMEWORK_PLUGIN_BASENAME, 'The_SEO_Framework\_do_plugin_deactivation' );

/**
 * Loads all of TSF.
 *
 * Runs at action `plugins_loaded`, priority `5`. So, use anything above 5, or any
 * action later than plugins_loaded and you can access the class and functions.
 *
 * @hook plugins_loaded 5
 * @since 5.0.0
 * @access private
 */
function _load_tsf() {
	/**
	 * @since 2.3.7
	 * @param bool $load
	 */
	if ( \apply_filters( 'the_seo_framework_load', true ) ) {
		if ( THE_SEO_FRAMEWORK_DEBUG )
			require \THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'load-debug.php';

		require \THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'init-compat.php';

		\add_action( 'init', 'The_SEO_Framework\_init_tsf', 0 );

		if ( \is_admin() ) {
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
	}
}

/**
 * Initializes all of TSF.
 *
 * @hook init 0
 * @since 3.1.0
 * @since 5.0.0 1. Is no longer responsible for the loading.
 *              2. Moved from plugins_loaded to init.
 * @see namespace\_load_tsf().
 * @access private
 */
function _init_tsf() {

	/**
	 * @since 2.8.0
	 * Runs before the plugin is initialized.
	 */
	\do_action( 'the_seo_framework_init' );

	require \THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'init-global.php';

	if ( \is_admin() || \wp_doing_cron() ) {
		/**
		 * @since 2.8.0
		 * Runs before the plugin is initialized in the admin screens.
		 */
		\do_action( 'the_seo_framework_admin_init' );

		require \THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'init-admin.php';

		if ( \wp_doing_ajax() ) {
			require \THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'init-admin-ajax.php';
		} elseif ( \wp_doing_cron() ) {
			require \THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'init-cron.php';
		}

		/**
		 * @since 2.9.4
		 * Runs after the plugin is initialized in the admin screens.
		 * Use this to remove actions.
		 */
		\do_action( 'the_seo_framework_after_admin_init' );
	} else {
		/**
		 * @since 2.8.0
		 * Runs before the plugin is initialized on the front-end.
		 */
		\do_action( 'the_seo_framework_front_init' );

		require \THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'init-front.php';

		/**
		 * @since 2.9.4
		 * Runs before the plugin is initialized on the front-end.
		 * Use this to remove actions.
		 */
		\do_action( 'the_seo_framework_after_front_init' );
	}

	/**
	 * @since 3.1.0
	 * Runs after the plugin is initialized.
	 * Use this to remove filters and actions.
	 */
	\do_action( 'the_seo_framework_after_init' );
}

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
 * @since 5.0.0 Now supports trait loading.
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

	static $_timer;

	$_timer ??= hrtime( true );

	$class = strtr(
		substr( $class, 18 ), // remove the "the_seo_framework\"
		[
			'\\' => \DIRECTORY_SEPARATOR,
			'_'  => '-',
		],
	);

	if ( str_starts_with( $class, 'traits' ) ) {
		$class = substr( $class, 7 ); // Remove "traits/"
		// The extension is deemed to be ".trait.php" always.
		require \THE_SEO_FRAMEWORK_DIR_PATH_TRAIT . "$class.trait.php";
	} else {
		require \THE_SEO_FRAMEWORK_DIR_PATH_CLASS . "$class.class.php";
	}

	if ( isset( $_timer ) ) {
		// When the class extends, the last class in the stack will reach this first.
		// All classes before cannot reach this any more.
		_bootstrap_timer( ( hrtime( true ) - $_timer ) / 1e9 );
		$_timer = null;
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
	require \THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'plugin-activation.php';
}

/**
 * Performs plugin deactivation actions.
 *
 * @hook deactivate_autodescription/autodescription.php 10
 * @since 2.8.0
 * @access private
 */
function _do_plugin_deactivation() {
	require \THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'plugin-deactivation.php';
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
