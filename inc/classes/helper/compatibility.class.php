<?php
/**
 * @package The_SEO_Framework\Classes\Helper\Compatibility
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework\Helper;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\memo;

use \The_SEO_Framework\{
	Admin,
	Data,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds a collection of helper methods for plugin compatibility.
 *
 * @since 4.3.0
 * @access private
 */
class Compatibility {

	/**
	 * Registers plugin cache checks on plugin activation.
	 *
	 * @hook activated_plugin 10
	 * @since 4.3.0
	 */
	public static function try_plugin_conflict_notification() {

		if ( ! static::get_active_conflicting_plugin_types( true )['seo_tools'] ) return;

		Admin\Notice\Persistent::register_notice(
			\__( 'Multiple SEO tools have been detected. You should only use one.', 'autodescription' ),
			'seo-plugin-conflict',
			[ 'type' => 'warning' ],
			[
				'screens'    => [ 'edit', 'edit-tags', 'dashboard', 'plugins', 'toplevel_page_theseoframework-settings' ],
				'capability' => 'activate_plugins',
				'count'      => 3,
				/**
				 * Indefinitely, AIOSEO could be installed by Awesome Motive without admin consent,
				 * only for the admin to find out a few months later.
				 */
				'timeout'    => -1,
			]
		);
	}

	/**
	 * Clears plugin cache checks on plugin deactivation.
	 *
	 * @hook deactivated_plugin 10
	 * @since 4.3.0
	 */
	public static function clear_plugin_conflict_notification() {
		Admin\Notice\Persistent::clear_notice( 'seo-plugin-conflict' );
	}

	/**
	 * Returns a filterable list of conflicting plugins.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 1. Moved from `The_SEO_Framework\Load`.
	 *              2. Renamed from `conflicting_plugins`.
	 *
	 * @return array List of conflicting plugins.
	 */
	public static function get_conflicting_plugins() {

		$conflicting_plugins = [
			'seo_tools'    => [
				'Yoast SEO'           => 'wordpress-seo/wp-seo.php',
				'All in One SEO Pack' => 'all-in-one-seo-pack/all_in_one_seo_pack.php',
				'SEO Ultimate'        => 'seo-ultimate/seo-ultimate.php',
				'SEOPress'            => 'wp-seopress/seopress.php',
				'Rank Math'           => 'seo-by-rank-math/rank-math.php',
				'Smart Crawl'         => 'smartcrawl-seo/wpmu-dev-seo.php',
			],
			'sitemaps'     => [
				'Google XML Sitemaps'             => 'google-sitemap-generator/sitemap.php',
				'XML Sitemap & Google News feeds' => 'xml-sitemap-feed/xml-sitemap.php',
				'Google Sitemap by BestWebSoft'   => 'google-sitemap-plugin/google-sitemap-plugin.php',
			],
			'open_graph'   => [
				'Facebook Open Graph Meta Tags for WordPress' => 'wonderm00ns-simple-facebook-open-graph-tags/wonderm00n-open-graph.php',
				'Open Graph'                            => 'opengraph/opengraph.php', // Redundant.
				'Open Graph Protocol Framework'         => 'open-graph-protocol-framework/open-graph-protocol-framework.php', // Redundant.
				'Shareaholic2'                          => 'shareaholic/sexy-bookmarks.php',
				'WordPress Social Sharing Optimization' => 'wpsso/wpsso.php',
			],
			'twitter_card' => [],
			'schema'       => [],
			'multilingual' => [
				'Polylang'       => 'polylang/polylang.php',
				'WPML'           => 'sitepress-multilingual-cms/sitepress.php',
				'TranslatePress' => 'translatepress-multilingual/index.php',
				'WPGlobus'       => 'wpglobus/wpglobus.php',
			],
		];

		/**
		 * @since 2.6.0
		 * @since 4.3.0 Added indexes 'multilingual' and 'schema'.
		 * @param array $conflicting_plugins The conflicting plugin list.
		 */
		$conflicting_plugins = (array) \apply_filters(
			'the_seo_framework_conflicting_plugins',
			$conflicting_plugins
		);

		if ( \has_filter( 'the_seo_framework_conflicting_plugins_type' ) ) {
			foreach ( $conflicting_plugins as $type => &$plugins ) {
				/**
				 * @since 2.6.1
				 * @since 4.3.0 Deprecated. Use `the_seo_framework_conflicting_plugins` instead.
				 * @param array  $conflicting_plugins Conflicting plugins
				 * @param string $type                The type of plugins to get.
				*/
				$plugins = (array) \apply_filters_deprecated(
					'the_seo_framework_conflicting_plugins_type',
					[
						$plugins,
						$type,
					],
					'4.3.0 of The SEO Framework',
					'the_seo_framework_conflicting_plugins',
				);
			}
		}

		return $conflicting_plugins;
	}

	/**
	 * Determines if other conflicting plugins are active.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $refresh Whether to refresh the cache.
	 * @return array[] A list of types that are potentially conflicting : {
	 *     string type => bool conflicting,
	 * }
	 */
	public static function get_active_conflicting_plugin_types( $refresh = false ) {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( ! $refresh && null !== $memo = memo() ) return $memo;

		$conflicting_types = [
			'seo_tools'    => false,
			'sitemaps'     => false,
			'open_graph'   => false,
			'twitter_card' => false,
			'schema'       => false,
			'multilingual' => false,
		];

		$active_plugins = Data\Blog::get_active_plugins();

		foreach ( static::get_conflicting_plugins() as $type => $plugins )
			if ( array_intersect( $plugins, $active_plugins ) )
				$conflicting_types[ $type ] = true;

		if ( $conflicting_types['seo_tools'] ) {
			$conflicting_types += [
				'sitemaps'     => true,
				'open_graph'   => true,
				'twitter_card' => true,
				'schema'       => true,
			];
		}

		return memo( $conflicting_types );
	}
}
