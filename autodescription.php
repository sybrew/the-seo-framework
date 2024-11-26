<?php
/**
 * Plugin Name: The SEO Framework
 * Plugin URI: https://theseoframework.com/
 * Description: An automated, advanced, accessible, unbranded and extremely fast SEO solution for your WordPress website.
 * Version: 5.1.2
 * Author: The SEO Framework Team
 * Author URI: https://theseoframework.com/
 * License: GPLv3
 * Text Domain: autodescription
 * Domain Path: /language
 * Requires at least: 6.0
 * Requires PHP: 7.4.0
 *
 * @package   The_SEO_Framework\Bootstrap
 * @author    Sybre Waaijer
 * @copyright 2015 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
 * @license   GPL-3.0
 * @link      https://theseoframework.com/
 */

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * The plugin version.
 *
 * 3 point: x.x.y; x.x is major; y is minor.
 *
 * @since 2.3.5
 */
define( 'THE_SEO_FRAMEWORK_VERSION', '5.1.2' );

/**
 * The plugin Database version.
 *
 * Used for lightweight version upgrade comparing.
 *
 * @since 2.7.0
 */
define( 'THE_SEO_FRAMEWORK_DB_VERSION', '5100' );

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

// Defines environental constants.
require THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'define.php';

// Load plugin API functions.
require THE_SEO_FRAMEWORK_DIR_PATH_FUNCT . 'api.php';

// Prepare plugin upgrader before the plugin loads. This may also downgrade (3103 or higher).
the_seo_framework_db_version() !== THE_SEO_FRAMEWORK_DB_VERSION
	and require THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'upgrade.php';

// Load deprecated functions.
// require THE_SEO_FRAMEWORK_DIR_PATH_FUNCT . 'deprecated.php';

// Load plugin.
require THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'load.php';

// # Zelda is here to protect your site from hackers.
// #
// #                  OLLLLLLLLL
// #               GGOiiiiiiiilGGG
// #           GGGOllttttttttttlllll
// #         GG1111tt1;;;;;;lltttt..L
// #        GttLLLLttiiiiiii;;tttl..,;
// #      GGtLLLLLLLLLiiiiiiii;tttttt,,
// #     GllLLLLLLLLLLLLiiiiiii;;tttt,,.
// #    ,;OttLLLLLLtttLttiiii;;i;;:ttt::.                  ttttG
// #    ;;Olll;;:::ittL::,;;;;;;ii;ttttt.                i.iii..
// #        ;,iiiii:LL:,,:iiiii;ii;ttttt.              ;::iii0..
// #        ,,...,,;;;,.....,ii;ii;...ll.            :::OO,GG.
// # , L....;,.....,::O::.00Gii;ii;ll.11.           .,,G,,0..
// # , L..LL;LLL00LLLL0::.00Gii,;;l..lll.  ...    ..,00,00.
// # , L..L;;;..11LLLLLLL1iiiii,ll;ttt11t..:::.  .,,0,,0..
// # , L..O;;i;;;LLLLLLLLiiiiii,;;L;;t11t..:ii.;;,0i:0;,
// # , L..Oiii;;;;;LLL1iiiii,::t,,t111::1:::00.,,,00L..
// # , L..Oiiiii::t..........::tttt11tttttt:::.OOiii.
// # , L..Oiit00tttLlll11lttt;;tll1llllltttt..GGGO:::..
// # , L..L..;00tttOii...:tt;;;tll::::::lttttt.OO::::::.
// # , L..O;;L00:::011;..l::;tttll:..   ;::lll...:::0ii.
// # , L..L;;L00:::011;..llllttttt.     :..:..LLL.......
// # , L..L::;GGLLL011;,,iGGGOOGGGG:: ;;;GG.....
// #   , :LLiiiiiiiii.iiGOOOGGtttt.. ;;;::.
// #    ,:LLiiiiiiiii.lltttttttttl..   :;
// #      ::tiiiiiittllllllllllllll.
// #      tt:ttiiit;;::::::::::::::::::
// #        l::iii:..:OOO..    :::::0,,
// #         11;;;,;;:OO.        ,::L;;:
// #           1..Lll;..         .ll1tt.
// #         11t11l11,           .ll111l.. It's Link?! Not Zelda??
// #         ll......             ........ - Sybre drew this by hand.

// phpcs:disable, Squiz.Commenting.InlineComment, Squiz.PHP.CommentedOutCode
//
// Debug: Not to be used on production websites as it dumps and/or disables all kinds of stuff everywhere.
//        This is here as an easily accessible toolset used solely for the development of this plugin.
//
// Headless tip: ?tsf_headless[meta]=0&tsf_headless[settings]=0&tsf_headless[user]=0
//
// add_action( 'plugins_loaded', function () { if ( is_super_admin() ) {
// if ( is_admin() ) {
// 	define( 'THE_SEO_FRAMEWORK_DEBUG', true );
// 	delete_option( 'the_seo_framework_upgraded_db_version' );
// 	( $_GET['reset_tsf_upgrade'] ?? 0 ) and delete_option( 'the_seo_framework_upgraded_db_version' ) and delete_option( 'the_seo_framework_initial_db_version' );
// 	( $_GET['downgrade_tsf'] ?? 0 ) and update_option( 'the_seo_framework_upgraded_db_version', (string) (int) $_GET['downgrade_tsf'], true );
// 	( $_GET['downgrade_tsf_initial'] ?? 0 ) and update_option( 'the_seo_framework_initial_db_version', (string) (int) $_GET['downgrade_tsf_initial'], false );
// 	( $_GET['tsf_headless'] ?? 0 ) and define( 'THE_SEO_FRAMEWORK_HEADLESS', $_GET['tsf_headless'] === 'true' ?: $_GET['tsf_headless'] );
// 	add_action( 'admin_footer', function () { print( '<script>jQuery.migrateMute=true;</script>' ); } );
// }
// }},0);
// phpcs:enable, Squiz.Commenting.InlineComment, Squiz.PHP.CommentedOutCode
