<?php
/**
 * @package The_SEO_Framework
 * @subpackage The_SEO_Framework\TSFEM\Suggestion
 */

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
	$plugin = get_plugins( '/the-seo-framework-extension-manager' );
	//? 4b
	if ( ! empty( $plugin ) ) return;

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

	the_seo_framework_enqueue_installer_scripts();

	add_action( 'admin_notices', 'the_seo_framework_suggest_extension_manager' );
}

/**
 * Outputs "look at TSFEM" notification to ALL applicable plugin users on upgrade.
 *
 * @since 3.0.6
 * @access private
 */
function the_seo_framework_suggest_extension_manager() {

	$plugin_slug = 'the-seo-framework-extension-manager';
	$em_text = __( 'Extension Manager', 'autodescription' );

	/**
	 * @source https://github.com/WordPress/WordPress/blob/4.9-branch/wp-admin/import.php#L162-L178
	 * @uses Spaghetti.
	 * @see WP Core class Plugin_Installer_Skin
	 */
	$url = add_query_arg( [
		'tab'       => 'plugin-information',
		'plugin'    => $plugin_slug,
		'from'      => 'plugins',
		'TB_iframe' => 'true',
		'width'     => 600,
		'height'    => 550,
	], network_admin_url( 'plugin-install.php' ) );
	$tsfem_details_link = sprintf(
		'<a href="%1$s" id=tsf-tsfem-tb class="thickbox open-plugin-details-modal" aria-label="%2$s">%3$s</a>',
		esc_url( $url ),
		/* translators: %s: Plugin name */
		esc_attr( sprintf( __( 'More information about %s', 'autodescription' ), $em_text ) ),
		esc_html( $em_text )
	);
	$suggestion = sprintf(
		/* translators: 1. "A feature, e.g. Focus keywords", 2: Extension Manager. */
		esc_html__( 'Looking for %1$s? Try out the %2$s for free.', 'autodescription' ),
		sprintf(
			'<strong>%s</strong>',
			esc_html__( 'Focus keywords', 'autodescription' )
		),
		$tsfem_details_link
	);

	/**
	 * @source https://github.com/WordPress/WordPress/blob/4.9-branch/wp-admin/import.php#L125-L138
	 * @uses Bolognese sauce.
	 * @see The closest bowl of spaghetti. Or WordPress\Administration\wp.updates/updates.js
	 * This joke was brought to you by the incomplete API of WP Shiny Updates, where
	 * WP's import.php has been directly injected into, rather than "calling" it via its API.
	 * Therefore, leaving the incompleteness undiscovered internally.
	 * @TODO Open core track ticket.
	 */
	$url = wp_nonce_url( add_query_arg( [
		'action' => 'install-plugin',
		'plugin' => $plugin_slug,
		'from'   => 'plugins',
	], self_admin_url( 'update.php' ) ), 'install-plugin_' . $plugin_slug );
	$action = sprintf(
		'<a href="%1$s" id=tsf-tsfem-install class="install-now button button-small" data-slug="%2$s" data-name="%3$s" aria-label="%4$s">%5$s</a>',
		esc_url( $url ),
		esc_attr( $plugin_slug ),
		esc_attr( $em_text ),
		/* translators: %s: Extension Manager */
		esc_attr( sprintf( __( 'Install the %s', 'autodescription' ), $em_text ) ),
		esc_html__( 'Install Now', 'autodescription' )
	);

	$text = is_rtl() ? $action . ' ' . $suggestion : $suggestion . ' ' . $action;

	//= This loads the JS files.
	the_seo_framework()->do_dismissible_notice( $text, 'updated', false, false );
}

/**
 * Loads scripts for TSFEM "Shiny Updates" implementation for WP 4.6 and later.
 *
 * @since 3.0.6
 * @since 3.1.0 No longer checks WP version, the requirements of this plugin is equal.
 * @access private
 */
function the_seo_framework_enqueue_installer_scripts() {

	$deps = [
		'plugin-install',
		'updates',
	];
	$scriptname = 'tsfinstaller';
	$suffix = the_seo_framework()->script_debug ? '' : '.min';

	$strings = [
		'slug'       => 'the-seo-framework-extension-manager',
		'canEnhance' => true || the_seo_framework()->wp_version( '4.6' ),
	];

	wp_register_script( $scriptname, THE_SEO_FRAMEWORK_DIR_URL . "lib/js/installer/{$scriptname}{$suffix}.js", $deps, THE_SEO_FRAMEWORK_VERSION, true );
	wp_localize_script( $scriptname, "{$scriptname}L10n", $strings );

	add_action( 'admin_print_styles', 'the_seo_framework_print_installer_styles' );
	add_action( 'admin_footer', 'wp_print_request_filesystem_credentials_modal' );
	add_action( 'admin_footer', 'wp_print_admin_notice_templates' );

	wp_enqueue_style( 'plugin-install' );
	wp_enqueue_script( $scriptname );
	add_thickbox();
}

/**
 * Outputs "button-small" "Shiny Updates" compatibility style.
 *
 * @since 3.0.6
 * @staticvar bool $printed Prevents duplicate writing.
 * @access private
 */
function the_seo_framework_print_installer_styles() {
	static $printed = false;
	if ( $printed ) return;
	echo '<style type="text/css">#tsf-tsfem-install.updating-message:before{font-size:16px;vertical-align:top}</style>';
	$printed = true;
}
