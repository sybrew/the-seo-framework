<?php
/**
 * @package The_SEO_Framework\Classes\Bridges\PluginTable
 */

namespace The_SEO_Framework\Bridges;

/**
 * The SEO Framework plugin
 * Copyright (C) 2021 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * Prepares the List Edit view interface.
 *
 * @since 4.1.4
 * @access protected
 * @internal
 * @final Can't be extended.
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
	public static function _add_plugin_action_links( $links = [] ) {

		$tsf_links = [];

		$tsf = \tsf();

		if ( ! $tsf->is_headless['settings'] ) {
			$tsf_links['settings'] = sprintf(
				'<a href="%s">%s</a>',
				\esc_url( \admin_url( "admin.php?page={$tsf->seo_settings_page_slug}" ) ),
				\esc_html__( 'Settings', 'autodescription' )
			);
		}

		$tsf_links['tsfem']   = sprintf(
			'<a href="%s" rel="noreferrer noopener" target="_blank">%s</a>',
			'https://theseoframework.com/extensions/',
			\esc_html_x( 'Extensions', 'Plugin extensions', 'autodescription' )
		);
		$tsf_links['pricing'] = sprintf(
			'<a href="%s" rel="noreferrer noopener" target="_blank">%s</a>',
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
	 * @access private
	 *
	 * @param string[] $plugin_meta An array of the plugin's metadata,
	 *                              including the version, author,
	 *                              author URI, and plugin URI.
	 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
	 * @return array $plugin_meta
	 */
	public static function _add_plugin_row_meta( $plugin_meta, $plugin_file ) {

		if ( THE_SEO_FRAMEWORK_PLUGIN_BASENAME !== $plugin_file )
			return $plugin_meta;

		$plugins = \get_plugins();
		$_get_em = empty( $plugins['the-seo-framework-extension-manager/the-seo-framework-extension-manager.php'] );

		return array_merge(
			$plugin_meta,
			[
				'support' => vsprintf(
					'<a href="%s" rel="noreferrer noopener nofollow" target="_blank">%s</a>',
					[
						'https://tsf.fyi/support',
						\esc_html__( 'Get support', 'autodescription' ),
					]
				),
				'docs'    => vsprintf(
					'<a href="%s" rel="noreferrer noopener nofollow" target="_blank">%s</a>',
					[
						'https://tsf.fyi/docs',
						\esc_html__( 'View documentation', 'autodescription' ),
					]
				),
				'API'     => vsprintf(
					'<a href="%s" rel="noreferrer noopener nofollow" target="_blank">%s</a>',
					[
						'https://tsf.fyi/docs/api',
						\esc_html__( 'View API docs', 'autodescription' ),
					]
				),
				'EM'      => vsprintf(
					'<a href="%s" rel="noreferrer noopener nofollow" target="_blank">%s</a>',
					[
						'https://tsf.fyi/extension-manager',
						$_get_em
							? \esc_html_x( 'Get Extension Manager', 'Extension Manager is a product name; do not translate it.', 'autodescription' )
							: 'Extension Manager',
					]
				),
			]
		);
	}
}
