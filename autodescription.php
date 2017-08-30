<?php
/**
 * Plugin Name: The SEO Framework
 * Plugin URI: https://theseoframework.com/
 * Description: An automated, advanced, accessible, unbranded and extremely fast SEO solution for any WordPress website.
 * Version: 2.9.4
 * Author: Sybre Waaijer
 * Author URI: https://theseoframework.com/
 * License: GPLv3
 * Text Domain: autodescription
 * Domain Path: /language
 */

defined( 'ABSPATH' ) or die;

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

//* Debug. Not to be used on production websites as it dumps and/or disables all kinds of stuff everywhere.
// add_action( 'plugins_loaded', function() { if ( is_super_admin() ) {
// 	if ( is_admin() ) {
// 		define( 'THE_SEO_FRAMEWORK_DEBUG', true );
// 		define( 'THE_SEO_FRAMEWORK_DEBUG_HIDDEN', true );
// 		define( 'THE_SEO_FRAMEWORK_DISABLE_TRANSIENTS', true );
// 		update_option( 'the_seo_framework_upgraded_db_version', '0' );
// 		add_filter( 'the_seo_framework_use_object_cache', '__return_false' );
// 	}
// }},0);

/**
 * The plugin version.
 *
 * Used as a CDN and Browser Cache buster.
 * Chrome and Firefox cache admin scripts.
 *
 * 3 point: x.x.y; x.x is major; y is minor.
 *
 * @since 1.0.0
 */
define( 'THE_SEO_FRAMEWORK_VERSION', '2.9.4' );

/**
 * The plugin Database version.
 *
 * Used for lightweight version upgrade comparing.
 *
 * @since 2.7.0
 */
define( 'THE_SEO_FRAMEWORK_DB_VERSION', '2941' );

/**
 * The plugin options database option_name.
 *
 * Used for storing the SEO options array.
 *
 * @todo document filter.
 *
 * @since 2.2.2
 */
define( 'THE_SEO_FRAMEWORK_SITE_OPTIONS', (string) apply_filters( 'the_seo_framework_site_options', 'autodescription-site-settings' ) );

/**
 * The plugin network options.
 *
 * @todo document filter.
 * Unused. @todo remove
 *
 * @since 2.2.2
 */
define( 'THE_SEO_FRAMEWORK_NETWORK_OPTIONS', (string) apply_filters( 'the_seo_framework_network_settings', 'autodescription-network-settings' ) );

/**
 * Plugin term options filter.
 * @since 2.7.0
 */
define( 'THE_SEO_FRAMEWORK_TERM_OPTIONS', (string) apply_filters( 'the_seo_framework_term_options', 'autodescription-term-settings' ) );

/**
 * Plugin user term options filter.
 * @since 2.7.0
 */
define( 'THE_SEO_FRAMEWORK_USER_OPTIONS', (string) apply_filters( 'the_seo_framework_user_options', 'autodescription-user-settings' ) );

/**
 * Plugin updates cache database name.
 * @since 2.9.3
 */
define( 'THE_SEO_FRAMEWORK_UPDATES_CACHE', (string) apply_filters( 'the_seo_framework_updates_cache', 'autodescription-updates-cache' ) );

/**
 * The plugin map url.
 * Used for calling browser files.
 * @since 2.2.2
 */
define( 'THE_SEO_FRAMEWORK_DIR_URL', plugin_dir_url( __FILE__ ) );

/**
 * The plugin map absolute path.
 * Used for calling php files.
 * @since 2.2.2
 */
define( 'THE_SEO_FRAMEWORK_DIR_PATH', plugin_dir_path( __FILE__ ) );

/**
 * The plugin file relative to the plugins dir.
 * @since 2.2.8
 */
define( 'THE_SEO_FRAMEWORK_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The plugin file, absolute unix path.
 * @since 2.2.9
 */
define( 'THE_SEO_FRAMEWORK_PLUGIN_BASE_FILE', __FILE__ );

/**
 * The plugin views map absolute path.
 * @since 2.7.0
 */
define( 'THE_SEO_FRAMEWORK_DIR_PATH_VIEWS', THE_SEO_FRAMEWORK_DIR_PATH . 'inc' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR );

/**
 * The plugin class map absolute path.
 * @since 2.2.9
 */
define( 'THE_SEO_FRAMEWORK_DIR_PATH_CLASS', THE_SEO_FRAMEWORK_DIR_PATH . 'inc' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR );

/**
 * The plugin interface map absolute path.
 * @since 2.8.0
 */
define( 'THE_SEO_FRAMEWORK_DIR_PATH_INTERFACE', THE_SEO_FRAMEWORK_DIR_PATH . 'inc' . DIRECTORY_SEPARATOR . 'interfaces' . DIRECTORY_SEPARATOR );

/**
 * The plugin function map absolute path.
 * @since 2.2.9
 */
define( 'THE_SEO_FRAMEWORK_DIR_PATH_FUNCT', THE_SEO_FRAMEWORK_DIR_PATH . 'inc' . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR );

/**
 * The plugin function map absolute path.
 * @since 2.8.0
 */
define( 'THE_SEO_FRAMEWORK_DIR_PATH_COMPAT', THE_SEO_FRAMEWORK_DIR_PATH . 'inc' . DIRECTORY_SEPARATOR . 'compat' . DIRECTORY_SEPARATOR );

the_seo_framework_pre_load();
/**
 * Determines whether we can "just" load the plugin, or require verification beforehand.
 *
 * @since 2.8.0
 * @since 2.9.4 The option is now autoloaded.
 * @uses get_site_option(), so it will only test once per WordPress installation; multisite included.
 * @todo This option isn't autoloaded... use is_multisite() condition?
 */
function the_seo_framework_pre_load() {
	if ( get_option( 'the_seo_framework_tested_upgrade_version' ) >= THE_SEO_FRAMEWORK_DB_VERSION ) {
		the_seo_framework_load_base_files();
	} else {
		the_seo_framework_test_server();
	}
}

/**
 * Tests plugin upgrade.
 *
 * @since 2.8.0
 */
function the_seo_framework_test_server() {

	//* Load on init action (manual FTP upload) or after plugin has been upgraded.
	require_once( THE_SEO_FRAMEWORK_DIR_PATH_FUNCT . 'plugin-test-server.php' );

	if ( get_option( 'the_seo_framework_tested_upgrade_version' ) >= THE_SEO_FRAMEWORK_DB_VERSION )
		the_seo_framework_load_base_files();
}

/**
 * Loads plugin base files.
 *
 * @since 2.8.0
 */
function the_seo_framework_load_base_files() {
	/**
	 * Load plugin files.
	 *
	 * @since 1.0.0
	 * @uses THE_SEO_FRAMEWORK_DIR_PATH
	 */
	require_once( THE_SEO_FRAMEWORK_DIR_PATH . 'load.php' );

	/**
	 * Load deprecated functions.
	 *
	 * @since 2.7.0
	 * @since 2.9.2 No longer called to improve performance.
	 * @uses THE_SEO_FRAMEWORK_DIR_PATH_FUNCT
	 */
	// require_once( THE_SEO_FRAMEWORK_DIR_PATH_FUNCT . 'deprecated.php' );

	/**
	 * Load API files.
	 * @since 2.1.6
	 * @uses THE_SEO_FRAMEWORK_DIR_PATH_FUNCT
	 */
	require_once( THE_SEO_FRAMEWORK_DIR_PATH_FUNCT . 'optionsapi.php' );
}
