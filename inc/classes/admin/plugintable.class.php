<?php
/**
 * @package The_SEO_Framework\Classes\Admin\PluginTable
 */

namespace The_SEO_Framework\Admin;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\is_headless;

/**
 * The SEO Framework plugin
 * Copyright (C) 2021 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Prepares the Plugin Table view interface.
 *
 * @since 4.1.4
 * @since 5.0.0 Moved from `\The_SEO_Framework\Bridges`
 * @access private
 */
final class PluginTable {

	/**
	 * Adds various links to the plugin row on the plugin's screen.
	 *
	 * @since 3.1.0
	 * @since 4.1.4 Moved to PluginTable.
	 * @access private
	 *
	 * @param array $links The current links.
	 * @return array The plugin links.
	 */
	public static function add_plugin_action_links( $links = [] ) {

		$tsf_links = [];

		if ( ! is_headless( 'settings' ) ) {
			$tsf_links['settings'] = \sprintf(
				'<a href="%s">%s</a>',
				\esc_url( \admin_url( 'admin.php?page=' . \THE_SEO_FRAMEWORK_SITE_OPTIONS_SLUG ) ),
				\esc_html__( 'Settings', 'autodescription' ),
			);
		}

		$tsf_links['tsfem']   = \sprintf(
			'<a href="%s" rel="noreferrer noopener" target=_blank>%s</a>',
			'https://theseoframework.com/extensions/',
			\esc_html_x( 'Extensions', 'Plugin extensions', 'autodescription' )
		);
		$tsf_links['pricing'] = \sprintf(
			'<a href="%s" rel="noreferrer noopener" target=_blank>%s</a>',
			'https://theseoframework.com/pricing/',
			\esc_html_x( 'Pricing', 'Plugin pricing', 'autodescription' )
		);

		return array_merge( $tsf_links, $links );
	}

	/**
	 * Adds more row meta on the plugin screen.
	 *
	 * @since 3.2.4
	 * @since 4.1.4 Moved to PluginTable.
	 * @since 5.0.0 Exchanged API docs for GitHub link. Simplified translations.
	 * @access private
	 *
	 * @param string[] $plugin_meta An array of the plugin's metadata,
	 *                              including the version, author,
	 *                              author URI, and plugin URI.
	 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
	 * @return array $plugin_meta
	 */
	public static function add_plugin_row_meta( $plugin_meta, $plugin_file ) {

		if ( \THE_SEO_FRAMEWORK_PLUGIN_BASENAME !== $plugin_file )
			return $plugin_meta;

		return array_merge(
			$plugin_meta,
			[
				'support' => \sprintf(
					'<a href="%s" rel="noreferrer noopener nofollow" target=_blank>%s</a>',
					'https://tsf.fyi/support',
					\esc_html__( 'Support', 'autodescription' ),
				),
				'docs'    => \sprintf(
					'<a href="%s" rel="noreferrer noopener nofollow" target=_blank>%s</a>',
					'https://tsf.fyi/docs',
					\esc_html__( 'Documentation', 'autodescription' ),
				),
				'Git'     => \sprintf(
					'<a href="%s" rel="noreferrer noopener nofollow" target=_blank>%s</a>',
					'https://tsf.fyi/github',
					'GitHub',
				),
				'EM'      => \sprintf(
					'<a href="%s" rel="noreferrer noopener nofollow" target=_blank>%s</a>',
					'https://tsf.fyi/extension-manager',
					'Extension Manager',
				),
			],
		);
	}
}
