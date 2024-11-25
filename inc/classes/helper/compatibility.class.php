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
 * Copyright (C) 2023 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * @since 5.0.0
 * @access private
 */
class Compatibility {

	/**
	 * Registers plugin cache checks on plugin activation.
	 *
	 * @hook activated_plugin 10
	 * @since 5.0.0
	 */
	public static function try_plugin_conflict_notification() {

		// We refresh here because the list is loaded before a plugin is (de)activated.
		if ( ! static::get_active_conflicting_plugin_types( true )['seo_tools'] ) return;

		Admin\Notice\Persistent::register_notice(
			\__( 'Multiple SEO plugins have been detected. You should only use one.', 'autodescription' ),
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
			],
		);
	}

	/**
	 * Clears plugin cache checks on plugin deactivation.
	 *
	 * @hook deactivated_plugin 10
	 * @since 5.0.0
	 */
	public static function clear_plugin_conflict_notification() {
		Admin\Notice\Persistent::clear_notice( 'seo-plugin-conflict' );
	}

	/**
	 * Returns a filterable list of conflicting plugins.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 1. Moved from `The_SEO_Framework\Load`.
	 *              2. Renamed from `conflicting_plugins`.
	 *
	 * @return array[] {
	 *     The conflicting plugins types.
	 *
	 *     @type string[] $seo_tools    The conflicting SEO plugins base files, indexed by plugin name.
	 *     @type string[] $sitemaps     The conflicting sitemap plugins base files, indexed by plugin name.
	 *     @type string[] $open_graph   The conflicting Open Graph plugins base files, indexed by plugin name.
	 *     @type string[] $twitter_card The conflicting Twitter Card plugins base files, indexed by plugin name.
	 *     @type string[] $schema       The conflicting Schema plugins base files, indexed by plugin name.
	 *     @type string[] $multilingual The conflicting multilingual plugins base files, indexed by plugin name.
	 * }
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
				'Polylang Pro'   => 'polylang-pro/polylang.php',
				'WPML'           => 'sitepress-multilingual-cms/sitepress.php',
				'TranslatePress' => 'translatepress-multilingual/index.php',
				'WPGlobus'       => 'wpglobus/wpglobus.php',
			],
		];

		/**
		 * @since 2.6.0
		 * @since 5.0.0 Added indexes 'multilingual' and 'schema'.
		 * @param array[] $conflicting_plugins {
		 *     The conflicting plugins types. You should not unset any keys.
		 *
		 *     @type string[] $seo_tools    The conflicting SEO plugins base files, indexed by plugin name.
		 *     @type string[] $sitemaps     The conflicting sitemap plugins base files, indexed by plugin name.
		 *     @type string[] $open_graph   The conflicting Open Graph plugins base files, indexed by plugin name.
		 *     @type string[] $twitter_card The conflicting Twitter Card plugins base files, indexed by plugin name.
		 *     @type string[] $schema       The conflicting Schema plugins base files, indexed by plugin name.
		 *     @type string[] $multilingual The conflicting multilingual plugins base files, indexed by plugin name.
		 * }
		 */
		$conflicting_plugins = (array) \apply_filters(
			'the_seo_framework_conflicting_plugins',
			$conflicting_plugins,
		);

		if ( \has_filter( 'the_seo_framework_conflicting_plugins_type' ) ) {
			foreach ( $conflicting_plugins as $type => &$plugins ) {
				/**
				 * @since 2.6.1
				 * @since 5.0.0 Deprecated. Use `the_seo_framework_conflicting_plugins` instead.
				 * @deprecated
				 * @param array  $conflicting_plugins Conflicting plugins
				 * @param string $type                The type of plugins to get.
				*/
				$plugins = (array) \apply_filters_deprecated(
					'the_seo_framework_conflicting_plugins_type',
					[
						$plugins,
						$type,
					],
					'5.0.0 of The SEO Framework',
					'the_seo_framework_conflicting_plugins',
				);
			}
		}

		return $conflicting_plugins;
	}

	/**
	 * Determines if other conflicting plugins are active.
	 *
	 * @since 5.0.0
	 *
	 * @param bool $refresh Whether to refresh the cache.
	 * @return array {
	 *     The active conflicting plugin types.
	 *
	 *     @type bool $seo_tools    Whether an SEO plugin is active.
	 *     @type bool $sitemaps     Whether a sitemap plugin is active.
	 *     @type bool $open_graph   Whether an Open Graph plugin is active.
	 *     @type bool $twitter_card Whether a Twitter Card plugin is active.
	 *     @type bool $schema       Whether a Schema plugin is active.
	 *     @type bool $multilingual Whether a multilingual plugin is active.
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
			$conflicting_types = array_merge(
				$conflicting_types,
				[
					'sitemaps'     => true,
					'open_graph'   => true,
					'twitter_card' => true,
					'schema'       => true,
				],
			);
		}

		return memo( $conflicting_types );
	}

	/**
	 * Detect if you can use all the given constants, functions and classes.
	 *
	 * @since 2.5.2
	 * @since 4.1.4 Fixed sorting algorithm from fribbling-me to resolving-me. Nothing changed but legibility.
	 * @since 4.2.0 Rewrote sorting algorithm; now, it's actually good.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `can_i_use`.
	 *              3. Removed the second parameter `$use_cache`.
	 *              4. Removed caching. This responsibility now lies by the caller.
	 * @since 5.0.5 Now accepts methods.
	 *
	 * @param array[] $plugins Array of array for globals, constants, classes, methods,
	 *                         and/or functions to check for plugin existence.
	 * @return bool True if everything is accessible.
	 */
	public static function can_i_use( $plugins = [] ) {

		// Check for globals
		foreach ( $plugins['globals'] ?? [] as $name )
			if ( ! isset( $GLOBALS[ $name ] ) )
				return false;

		// Check for constants
		foreach ( $plugins['constants'] ?? [] as $name )
			if ( ! \defined( $name ) )
				return false;

		// Check for functions
		foreach ( $plugins['functions'] ?? [] as $name )
			if ( ! \function_exists( $name ) )
				return false;

		// Check for classes
		foreach ( $plugins['classes'] ?? [] as $name )
			if ( ! class_exists( $name, false ) ) // phpcs:ignore, TSF.Performance.Functions.PHP -- we don't autoload.
				return false;

		// Check for classes
		foreach ( $plugins['methods'] ?? [] as [ $object, $name ] )
			if ( ! method_exists( $object, $name ) )
				return false;

		// All classes, functions and constant have been found to exist
		return true;
	}

	/**
	 * Checks if the (parent) theme name is loaded.
	 *
	 * @since 2.1.0
	 * @since 4.2.0 No longer "loads" the theme; instead, simply compares input to active theme options.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `is_theme`.
	 * @since 5.1.0 Added memoization.
	 *
	 * @param string|string[] $themes The theme names to test.
	 * @return bool Any of the themes are active.
	 */
	public static function is_theme_active( $themes = '' ) {

		$active_theme = memo() ?? memo( array_unique( [
			strtolower( \get_option( 'stylesheet' ) ), // Parent.
			strtolower( \get_option( 'template' ) ),   // Child.
		] ) );

		foreach ( (array) $themes as $theme )
			if ( \in_array( strtolower( $theme ), $active_theme, true ) )
				return true;

		return false;
	}

	/**
	 * Detects presence of a page builder that renders content dynamically.
	 *
	 * Detects the following builders:
	 * - Divi Builder by Elegant Themes
	 * - Visual Composer by WPBakery
	 * - Bricks Builder by Bricks
	 *
	 * @since 4.1.0
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `detect_non_html_page_builder`.
	 * @since 5.1.0 Added 'BRICKS_VERSION' (Bricks) constants.
	 *
	 * @return bool
	 */
	public static function is_non_html_builder_active() {
		return memo() ?? memo(
			/**
			 * @since 4.1.0
			 * @param bool $detected Whether an active page builder that renders content dynamically is detected.
			 * @NOTE not to be confused with `the_seo_framework_detect_non_html_page_builder`, which tests
			 *       the page builder status for each post individually.
			 */
			(bool) \apply_filters(
				'the_seo_framework_shortcode_based_page_builder_active',
				\defined( 'ET_BUILDER_VERSION' )
				|| \defined( 'WPB_VC_VERSION' )
				|| \defined( 'BRICKS_VERSION' ),
			)
		);
	}
}
