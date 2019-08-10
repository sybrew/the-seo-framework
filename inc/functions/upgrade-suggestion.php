<?php
/**
 * @package The_SEO_Framework\Suggestion
 * @subpackage The_SEO_Framework\Bootstrap\Install
 */

namespace The_SEO_Framework\Suggestion;

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
 * @since 3.2.4 Applied namspacing to this file. All method names have changed.
 * @access private
 */

_prepare();
/**
 * Prepares a suggestion notification to ALL applicable plugin users on upgrade;
 * For TSFEM, it's shown when:
 *    0. The upgrade happens when an applicable user is on the admin pages. (always true w/ default actions)
 *    1. The constant 'TSF_DISABLE_SUGGESTIONS' is not defined or false.
 *    2. The current dashboard is the main site's.
 *    3. The applicable user can install plugins.
 *    4. TSFEM isn't already installed.
 *    5. PHP and WP requirements of TSFEM are met.
 *
 * This notice is automatically dismissed, and it can be ignored without reappearing.
 *
 * @since 3.0.6
 * @access private
 * @uses the_seo_framework_add_upgrade_notice();
 */
function _prepare() {

	//? 1
	if ( defined( 'TSF_DISABLE_SUGGESTIONS' ) && TSF_DISABLE_SUGGESTIONS ) return;
	//? 2
	if ( ! \is_main_site() ) return;
	//? 3
	if ( ! \current_user_can( 'install_plugins' ) ) return;
	//? 4a
	if ( defined( 'TSF_EXTENSION_MANAGER_VERSION' ) ) return;
	//= PHP<5.5 can't write in empty()
	$plugin = \get_plugins();
	//? 4b
	if ( ! empty( $plugin['the-seo-framework-extension-manager/the-seo-framework-extension-manager.php'] ) ) return;

	/** @source https://github.com/sybrew/The-SEO-Framework-Extension-Manager/blob/34674828a9e79bf72584e23aaa4a82ea1f154229/bootstrap/envtest.php#L51-L62 */
	$envtest = false;
	$_req    = [
		'php' => [
			'5.5' => 50521,
			'5.6' => 50605,
		],
		'wp'  => '37965',
	];

	//? PHP_VERSION_ID is definitely defined, but let's keep it homonymous with the envtest of TSFEM.
	// phpcs:disable, Generic.Formatting.MultipleStatementAlignment, WordPress.WhiteSpace.PrecisionAlignment
	   ! defined( 'PHP_VERSION_ID' ) || PHP_VERSION_ID < $_req['php']['5.5'] and $envtest = 1
	or PHP_VERSION_ID >= 50600 && PHP_VERSION_ID < $_req['php']['5.6'] and $envtest = 2
	or $GLOBALS['wp_db_version'] < $_req['wp'] and $envtest = 3
	or $envtest = true;
	// phpcs:enable, Generic.Formatting.MultipleStatementAlignment, WordPress.WhiteSpace.PrecisionAlignment

	//? 5
	if ( true !== $envtest ) return;

	_load_tsfem_suggestion();
}

/**
 * Loads the TSFEM suggestion.
 *
 * @since 3.2.4
 * @access private
 */
function _load_tsfem_suggestion() {
	\add_action( 'admin_notices', __NAMESPACE__ . '\\_suggest_extension_manager' );
}

/**
 * Outputs "look at TSFEM" notification to applicable plugin users on upgrade.
 *
 * @since 3.0.6
 * @access private
 */
function _suggest_extension_manager() {

	$tsf = \the_seo_framework();

	$tsf->do_dismissible_notice(
		$tsf->convert_markdown(
			sprintf(
				/* translators: The URLs are in markdown! Don't add spaces around the brackets. 1 = first URL, 2 = second URL. */
				\esc_html__( 'We hope you like this latest version of The SEO Framework. Even with all the new features and optimization, it remains the fastest, most lightweight, and highest performing of all the top WordPress SEO plugins. Because we don\'t fill our plugin with annoying ads, like the other guys, several of our additional services can be easily missed, like the [Extension Manager](%1$s) for example. These extra services add incredible functionality, such as Structured Data, Focus Subject Analysis, and even Spam Protection. We are confident these innovative and professional solutions will help you boost your site\'s SEO performance. And the best part is, we bundle many of the extensions for free. [Give them a try](%2$s).', 'autodescription' ),
				'https://theseoframework.com/extension-manager/',
				'https://theseoframework.com/extensions/'
			),
			[ 'a' ], // 'strong' ?
			[ 'a_internal' => false ]
		),
		'updated',
		false,
		false
	);
}
