<?php
/**
 * @package The_SEO_Framework\Suggestion
 * @subpackage The_SEO_Framework\Bootstrap\Install
 */

namespace The_SEO_Framework\Suggestion;

/**
 * The SEO Framework plugin
 * Copyright (C) 2018 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

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
	if ( \defined( 'TSF_DISABLE_SUGGESTIONS' ) && TSF_DISABLE_SUGGESTIONS ) return;
	// 2
	if ( ! \is_main_site() ) return;

	$show_sale = true;
	if ( \function_exists( '\\tsf_extension_manager' ) && method_exists( \tsf_extension_manager(), 'is_connected_user' ) ) {
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

	if ( $previous_version < '4270' && $current_version < '4280' ) {
		$tsf = \tsf();

		$tsf->register_dismissible_persistent_notice(
			$tsf->convert_markdown(
				sprintf(
					'<p>The SEO Framework: [Cyber Sale &ndash; 50%% off](%s). This notification will self-destruct when the sale ends, or when you dismiss it.</p>',
					'https://theseoframework.com/?p=3527'
				),
				[ 'a' ],
				[ 'a_internal' => false ]
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
				'count'        => 4,
				'timeout'      => strtotime( 'December 4th, 2022, 23:00GMT+1' ) - time(),
			]
		);
	}
}
