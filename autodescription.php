<?php
/**
 * Plugin Name: The SEO Framework
 * Plugin URI: https://theseoframework.com/
 * Description: An automated, advanced, accessible, unbranded and extremely fast SEO solution for your WordPress website.
 * Version: 3.2.4
 * Author: Sybre Waaijer
 * Author URI: https://theseoframework.com/
 * License: GPLv3
 * Text Domain: autodescription
 * Domain Path: /language
 */

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * @NOTE This file MUST be written according to WordPress' minimum PHP requirements.
 *       Which is PHP 5.2.
 */

//* Debug. Not to be used on production websites as it dumps and/or disables all kinds of stuff everywhere.
// add_action( 'plugins_loaded', function() { if ( is_super_admin() ) {
// 	if ( is_admin() ) {
// 		define( 'THE_SEO_FRAMEWORK_DEBUG', true );
// 		define( 'THE_SEO_FRAMEWORK_DISABLE_TRANSIENTS', true );
// 		delete_option( 'the_seo_framework_upgraded_db_version' );
// 		delete_option( 'the_seo_framework_tested_upgrade_version' );
// 		add_filter( 'the_seo_framework_use_object_cache', '__return_false' );
// 	}
// }},0);

/**
 * The plugin version.
 *
 * 3 point: x.x.y; x.x is major; y is minor.
 *
 * @since 2.3.5
 */
define( 'THE_SEO_FRAMEWORK_VERSION', '3.2.4' );

/**
 * The plugin Database version.
 *
 * Used for lightweight version upgrade comparing.
 *
 * @since 2.7.0
 */
define( 'THE_SEO_FRAMEWORK_DB_VERSION', '3104' );

/**
 * The plugin file, absolute unix path.
 * @since 2.2.9
 */
define( 'THE_SEO_FRAMEWORK_PLUGIN_BASE_FILE', __FILE__ );

/**
 * The plugin's bootstrap folder location.
 * @since 3.1.0
 */
define( 'THE_SEO_FRAMEWORK_BOOTSTRAP_PATH', dirname( THE_SEO_FRAMEWORK_PLUGIN_BASE_FILE ) . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR );

/**
 * Checks whether to start plugin or test the server environment first.
 * @since 2.8.0
 */
if ( get_option( 'the_seo_framework_tested_upgrade_version' ) < THE_SEO_FRAMEWORK_DB_VERSION ) {
	require THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'envtest.php';

	if ( get_option( 'the_seo_framework_tested_upgrade_version' ) >= THE_SEO_FRAMEWORK_DB_VERSION )
		the_seo_framework_boot();
} else {
	the_seo_framework_boot();
}

/**
 * Starts the plugin.
 *
 * @since 3.1.0
 * @access private
 */
function the_seo_framework_boot() {

	/**
	 * Defines environental constants.
	 * @since 3.1.0
	 */
	require THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'define.php';

	/**
	 * Load plugin API functions.
	 * @since 3.1.0
	 */
	require THE_SEO_FRAMEWORK_DIR_PATH_FUNCT . 'api.php';

	/**
	 * Prepare plugin upgrader before the plugin loads.
	 * @since 3.1.0
	 * @since 3.1.2 Now performs a weak check.
	 */
	if ( the_seo_framework_db_version() != THE_SEO_FRAMEWORK_DB_VERSION ) { // loose comparison OK.
		require THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'upgrade.php';
	}

	/**
	 * Load deprecated functions.
	 * @since 3.1.0
	 */
	require THE_SEO_FRAMEWORK_DIR_PATH_FUNCT . 'deprecated.php';

	/**
	 * Load plugin.
	 * @since 3.1.0
	 */
	require THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'load.php';
}
