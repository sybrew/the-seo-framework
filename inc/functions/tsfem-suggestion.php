<?php
/**
 * @package The_SEO_Framework
 * @subpackage The_SEO_Framework\TSFEM\Suggestion
 */

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

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * This file holds functions for installing TSFEM.
 * This file will only be called ONCE on plugin install, or upgrade from pre-v3.0.6.
 *
 * @since 3.0.6
 * @access private
 */

/**
 * Prepares a "look at TSFEM" notification to ALL applicable plugin users on upgrade;
 * when:
 *    0. The upgrade happens when an applicable user is on the admin pages. (always true w/ default actions)
 *    1. The constant 'TSF_DISABLE_SUGGESTIONS' is not defined or false.
 *    2. The current dashboard is the main site's.
 *    3. The applicable user can install plugins.
 *    4. TSFEM isn't already installed.
 *    5. PHP and WP requirements of TSFEM are met.
 * This notice is automatically dismissed, and can be ignored without reappearing.
 *
 * @since 3.0.6
 * @access private
 * @uses the_seo_framework_add_upgrade_notice();
 */
function the_seo_framework_load_extension_manager_suggestion() {

	//? 1
	if ( defined( 'TSF_DISABLE_SUGGESTIONS' ) && TSF_DISABLE_SUGGESTIONS ) return;
	//? 2
	if ( ! is_main_site() ) return;
	//? 3
	if ( ! current_user_can( 'install_plugins' ) ) return;
	//? 4a
	if ( defined( 'TSF_EXTENSION_MANAGER_VERSION' ) ) return;
	//= PHP<5.5 can't write in empty()
	$plugin = get_plugins();
	//? 4b
	if ( ! empty( $plugin['the-seo-framework-extension-manager/the-seo-framework-extension-manager.php'] ) ) return;

	/** @source https://github.com/sybrew/The-SEO-Framework-Extension-Manager/blob/34674828a9e79bf72584e23aaa4a82ea1f154229/bootstrap/envtest.php#L51-L62 */
	$_req = [
		'php' => [
			'5.5' => 50521,
			'5.6' => 50605,
		],
		'wp' => '37965',
	];
	$envtest = false;

	   ! defined( 'PHP_VERSION_ID' ) || PHP_VERSION_ID < $_req['php']['5.5'] and $envtest = 1
	or PHP_VERSION_ID >= 50600 && PHP_VERSION_ID < $_req['php']['5.6'] and $envtest = 2
	or $GLOBALS['wp_db_version'] < $_req['wp'] and $envtest = 3
	or $envtest = true;

	//? 5
	if ( true !== $envtest ) return;

	add_action( 'admin_notices', 'the_seo_framework_suggest_extension_manager' );
}

/**
 * Outputs "look at TSFEM" notification to ALL applicable plugin users on upgrade.
 *
 * @since 3.0.6
 * @access private
 */
function the_seo_framework_suggest_extension_manager() {

	$suggestion = sprintf(
		/* translators: %s: SEO extensions link. */
		esc_html__( 'Looking for more SEO functionality? Check out %s.', 'autodescription' ),
		sprintf(
			'<a href="%s" target=_blank rel="noopener noreferrer">%s</a>',
			'https://theseoframework.com/extensions/',
			esc_html__( "The SEO Framework's extensions", 'autodescription' )
		)
	);

	//= This loads the JS files.
	the_seo_framework()->do_dismissible_notice( $suggestion, 'updated', false, false );
}
