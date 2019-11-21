<?php
/**
 * Plugin Name: The SEO Framework
 * Plugin URI: https://theseoframework.com/
 * Description: An automated, advanced, accessible, unbranded and extremely fast SEO solution for your WordPress website.
 * Version: 4.0.4
 * Author: The SEO Framework Team
 * Author URI: https://theseoframework.com/
 * License: GPLv3
 * Text Domain: autodescription
 * Domain Path: /language
 *
 * @package The_SEO_Framework\Bootstrap
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
 * When we only support WordPress 5.2+, it'll be PHP 5.6.
 * When we only support WordPress 5.4?+, it'll be PHP 7.1.
 */

// phpcs:disable, Squiz.Commenting.InlineComment, Squiz.PHP.CommentedOutCode
//
// Debug: Not to be used on production websites as it dumps and/or disables all kinds of stuff everywhere.
//
// add_action( 'plugins_loaded', function() { if ( is_super_admin() ) {
// if ( is_admin() ) {
// 	define( 'THE_SEO_FRAMEWORK_DEBUG', true );
// 	define( 'THE_SEO_FRAMEWORK_DISABLE_TRANSIENTS', true );
// 	delete_option( 'the_seo_framework_upgraded_db_version' );
// 	( $_GET['reset_tsf_upgrade'] ?? 0 ) and delete_option( 'the_seo_framework_upgraded_db_version' ) and delete_option( 'the_seo_framework_initial_db_version' );
// 	( $_GET['downgrade_tsf'] ?? 0 ) and update_option( 'the_seo_framework_upgraded_db_version', (string) (int) $_GET['downgrade_tsf'] );
// 	( $_GET['downgrade_tsf_initial'] ?? 0 ) and update_option( 'the_seo_framework_initial_db_version', (string) (int) $_GET['downgrade_tsf_initial'] );
// 	( $_GET['reset_tsf_tested'] ?? 0 ) and delete_option( 'the_seo_framework_tested_upgrade_version' );
// 	add_filter( 'the_seo_framework_use_object_cache', '__return_false' );
// }
// }},0);
// phpcs:enable, Squiz.Commenting.InlineComment, Squiz.PHP.CommentedOutCode

/**
 * The plugin version.
 *
 * 3 point: x.x.y; x.x is major; y is minor.
 *
 * @since 2.3.5
 */
define( 'THE_SEO_FRAMEWORK_VERSION', '4.0.4' );

/**
 * The plugin Database version.
 *
 * Used for lightweight version upgrade comparing.
 *
 * @since 2.7.0
 */
define( 'THE_SEO_FRAMEWORK_DB_VERSION', '4000' );

/**
 * The plugin file, absolute unix path.
 *
 * @since 2.2.9
 */
define( 'THE_SEO_FRAMEWORK_PLUGIN_BASE_FILE', __FILE__ );

/**
 * The plugin's bootstrap folder location.
 *
 * @since 3.1.0
 */
define( 'THE_SEO_FRAMEWORK_BOOTSTRAP_PATH', dirname( THE_SEO_FRAMEWORK_PLUGIN_BASE_FILE ) . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR );

/**
 * Checks whether to start plugin or test the server environment first.
 *
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
 * Starts the plugin, loads files outside of the global scope.
 *
 * @since 3.1.0
 * @access private
 */
function the_seo_framework_boot() {

	// Defines environental constants.
	require THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'define.php';

	// Load plugin API functions.
	require THE_SEO_FRAMEWORK_DIR_PATH_FUNCT . 'api.php';

	// Prepare plugin upgrader before the plugin loads.
	the_seo_framework_db_version() !== THE_SEO_FRAMEWORK_DB_VERSION
		and require THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'upgrade.php';

	// Load deprecated functions.
	require THE_SEO_FRAMEWORK_DIR_PATH_FUNCT . 'deprecated.php';

	// Load plugin.
	require THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'load.php';
}
