<?php
/**
 * Plugin Name: The SEO Framework
 * Plugin URI: https://wordpress.org/plugins/autodescription/
 * Description: The SEO Framework makes sure your SEO is always up-to-date without any configuration needed. It's based upon the Genesis SEO.
 * Version: 2.4.3.1
 * Author: Sybre Waaijer
 * Author URI: https://cyberwire.nl/
 * License: GPLv3
 * Text Domain: autodescription
 * Domain Path: /language
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

//* Debug.
//if ( is_admin() ) {
//	define( 'THE_SEO_FRAMEWORK_DEBUG', true );
//	define( 'THE_SEO_FRAMEWORK_DEBUG_MORE', true );
//	define( 'THE_SEO_FRAMEWORK_DEBUG_HIDDEN', true );
//}

/**
 * CDN Cache buster. 3 point.
 * Not many caching plugins use CDN in dashboard. What a shame.
 *
 * @since 1.0.0
 *
 * New constant.
 * @since 2.3.0
 *
 * Removed previous constant.
 * @since 2.3.5
 */
define( 'THE_SEO_FRAMEWORK_VERSION', '2.4.3' );

/**
 * Plugin options filter
 * We can't change the options name without erasing the settings.
 * We can change the filter, however. So we did.
 *
 * @since 2.2.2
 *
 * New constant and filter.
 * @since 2.3.0
 *
 * Removed previous constant and filter.
 * @since 2.3.5
 */
define( 'THE_SEO_FRAMEWORK_SITE_OPTIONS', (string) apply_filters( 'the_seo_framework_site_options', 'autodescription-site-settings' ) );

/**
 * Plugin options filter
 * We can't change the options name without erasing the settings.
 * We can change the filter, however. So we did.
 *
 * @since 2.2.2
 *
 * New constant and filter.
 * @since 2.3.0
 *
 * Removed previous constant and filter.
 * @since 2.3.5
 */
define( 'THE_SEO_FRAMEWORK_NETWORK_OPTIONS', (string) apply_filters( 'the_seo_framework_network_settings', 'autodescription-network-settings' ) );

/**
 * The plugin map url.
 * Used for calling browser files.
 *
 * @since 1.0.0
 *
 * New constant.
 * @since 2.3.0
 *
 * Removed previous constant.
 * @since 2.3.5
 */
define( 'THE_SEO_FRAMEWORK_DIR_URL', plugin_dir_url( __FILE__ ) );

/**
 * The plugin map absolute path.
 * Used for calling php files.
 *
 * @since 1.0.0
 *
 * New constant.
 * @since 2.3.0
 *
 * Removed previous constant.
 * @since 2.3.5
 */
define( 'THE_SEO_FRAMEWORK_DIR_PATH', plugin_dir_path( __FILE__ ) );

/**
 * The plugin file relative to the plugins dir.
 *
 * @since 2.2.8
 */
define( 'THE_SEO_FRAMEWORK_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The plugin file, absolute unix path.
 * @since 2.2.9
 *
 * New constant.
 * @since 2.3.0
 *
 * Removed previous constant.
 * @since 2.3.5
 */
define( 'THE_SEO_FRAMEWORK_PLUGIN_BASE_FILE', __FILE__ );

/**
 * The plugin class map absolute path.
 *
 * New constant.
 * @since 2.3.0
 *
 * Removed previous constant.
 * @since 2.3.5
 */
define( 'THE_SEO_FRAMEWORK_DIR_PATH_CLASS', THE_SEO_FRAMEWORK_DIR_PATH . '/inc/classes/' );

/**
 * The plugin function map absolute path.
 *
 * New constant.
 * @since 2.3.0
 *
 * Removed previous constant.
 * @since 2.3.5
 */
define( 'THE_SEO_FRAMEWORK_DIR_PATH_FUNCT', THE_SEO_FRAMEWORK_DIR_PATH . '/inc/functions/' );

/**
 * Plugin locale 'autodescription'
 *
 * File located in plugin folder autodescription/language/
 *
 * @since 1.0.0
 *
 * @return void
 */
function the_seo_framework_locale_init() {
	load_plugin_textdomain( 'autodescription', false, basename( dirname( __FILE__ ) ) . '/language/' );
}
add_action( 'plugins_loaded', 'the_seo_framework_locale_init', 10 );

/**
 * Load plugin files
 *
 * @since 1.0.0
 *
 * @uses THE_SEO_FRAMEWORK_DIR_PATH
 */
require_once( THE_SEO_FRAMEWORK_DIR_PATH . '/load.class.php' );
