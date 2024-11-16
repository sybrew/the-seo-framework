<?php
/**
 * @package The_SEO_Framework\Suggestion
 * @subpackage The_SEO_Framework\Bootstrap\Install
 */

namespace The_SEO_Framework\Suggestion;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\{
	Admin,
	Helper\Format\Markdown,
};

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.

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

/**
 * This file holds functions for installing TSFEM.
 * This file will only be called ONCE on plugin install, or upgrade from pre-v3.0.6.
 *
 * @since 3.0.6
 * @since 3.2.4 Applied namspacing to this file. All method names have changed.
 * @access private
 */

// phpcs:ignore, TSF.Performance.Opcodes.ShouldHaveNamespaceEscape
_prepare( $previous_version, $current_version );
/**
 * Prepares a suggestion notification to ALL applicable plugin users on upgrade;
 *
 * The notice is automatically dismissed after X views, and it can be ignored without reappearing.
 *
 * @since 3.0.6
 * @since 4.1.0 1. Now tests TSFEM 2.4.0 requirements.
 *              2. Removed the user capability requirement, and forwarded that to `_suggest_extension_manager()`.
 *              3. Can now run on the front-end without crashing.
 *              4. Added the first two parameters, $previous_version and $current_version.
 *              5. Now tests if the upgrade actually happened, before invoking the suggestion.
 * @since 4.1.2 Can now communicate with Extension Manager for the edge-case sale.
 * @since 4.1.3 Commented out sale notification conditions, as those can't be met anyway.
 * @since 4.2.1 No longer tests WP and PHP requirements for Extension Manager.
 * @since 4.2.7 Removed test for Extension Manager upgrade.
 * @access private
 *
 * @param string $previous_version The previous version the site upgraded from, if any.
 * @param string $current_version  The current version of the site.
 */
function _prepare( $previous_version, $current_version ) {

	// 0
	// phpcs:ignore, WordPress.PHP.StrictComparisons.LooseComparison -- might be mixed types.
	if ( $previous_version == $current_version ) return;
	// 1
	if ( \defined( 'TSF_DISABLE_SUGGESTIONS' ) && \TSF_DISABLE_SUGGESTIONS ) return;
	// 2
	if ( ! \is_main_site() ) return;

	$show_sale = true;
	if ( \function_exists( 'tsf_extension_manager' ) && method_exists( \tsf_extension_manager(), 'is_connected_user' ) ) {
		$show_sale = ! \tsf_extension_manager()->is_connected_user();
	}
	if ( $show_sale ) {
		// phpcs:ignore, TSF.Performance.Opcodes.ShouldHaveNamespaceEscape
		_suggest_temp_sale( $previous_version, $current_version );
	}
}

/**
 * Registers "look at site" notification to applicable plugin users on upgrade.
 *
 * Some will hate me for this. Others will thank me they got notified.
 * In the end, I can't sustain this project without money, and the whiny users still need a good working product.
 * Win-win.
 *
 * @since 4.1.2
 * @access private
 *
 * @param string $previous_version The previous version the site upgraded from, if any.
 * @param string $current_version  The current version of the site.
 */
function _suggest_temp_sale( $previous_version, $current_version ) {

	if ( $previous_version < '5100' && $current_version < '5110' ) {
		Admin\Notice\Persistent::register_notice(
			Markdown::convert(
				\sprintf(
					'<p>For The SEO Framework v5.1, we added over 150 improvements in the past 7 months.</p><p>To celebrate this update (and Black Friday), we are offering a [50%% lifetime discount on our extensions](%s).</p><p>This notification will vanish December 6th or when you dismiss it.</p>',
					'https://theseoframework.com/?p=3527',
				),
				[ 'a' ],
				[ 'a_internal' => false ],
			),
			'suggest-sale',
			[
				'type'   => 'info',
				'icon'   => false,
				'escape' => false,
			],
			[
				'screens'      => [],
				'excl_screens' => [ 'update-core', 'post', 'term', 'upload', 'media', 'plugin-editor', 'plugin-install', 'themes', 'widgets', 'user', 'nav-menus', 'theme-editor', 'profile', 'export', 'site-health', 'export-personal-data', 'erase-personal-data' ],
				'capability'   => 'install_plugins',
				'user'         => 0,
				'count'        => 42,
				'timeout'      => strtotime( 'December 6th, 2024, 23:00GMT+1' ) - time(),
			],
		);
	}
}
