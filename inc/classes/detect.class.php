<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Detect
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Helper\Query;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\Detect
 *
 * Detects other plugins and themes
 *
 * @since 2.8.0
 */
class Detect extends Admin_Init {

	/**
	 * Returns list of active plugins.
	 * Memoizes the return value.
	 *
	 * @since 2.6.1
	 * @credits Jetpack for some code.
	 *
	 * @return array List of active plugins.
	 */
	public function active_plugins() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo() ) return $memo;

		$active_plugins = (array) \get_option( 'active_plugins', [] );

		if ( \is_multisite() ) {
			// Due to legacy code, active_sitewide_plugins stores them in the keys,
			// whereas active_plugins stores them in the values. array_keys() resolves the disparity.
			$network_plugins = array_keys( \get_site_option( 'active_sitewide_plugins', [] ) );

			if ( $network_plugins )
				$active_plugins = array_merge( $active_plugins, $network_plugins );
		}

		sort( $active_plugins );

		return memo( $active_plugins );
	}

	/**
	 * Filterable list of conflicting plugins.
	 *
	 * @since 2.6.0
	 * @credits Jetpack for most code.
	 *
	 * @return array List of conflicting plugins.
	 */
	public function conflicting_plugins() {

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
			'multilingual' => [
				'Polylang'       => 'polylang/polylang.php',
				'WPML'           => 'sitepress-multilingual-cms/sitepress.php',
				'TranslatePress' => 'translatepress-multilingual/index.php',
				'WPGlobus'       => 'wpglobus/wpglobus.php',
			],
		];

		/**
		 * @since 2.6.0
		 * @since 4.3.0 Added index 'multilingual'
		 * @param array $conflicting_plugins The conflicting plugin list.
		 */
		return (array) \apply_filters_ref_array( 'the_seo_framework_conflicting_plugins', [ $conflicting_plugins ] );
	}

	/**
	 * Fetches type of conflicting plugins.
	 *
	 * @since 2.6.0
	 * @since 4.2.0 Now always runs the filter, even when $type is not registered.
	 *
	 * @param string $type The Key from $this->conflicting_plugins()
	 * @return array
	 */
	public function get_conflicting_plugins( $type = 'seo_tools' ) {
		/**
		 * @since 2.6.1
		 * @param array  $conflicting_plugins Conflicting plugins
		 * @param string $type                The type of plugins to get.
		*/
		return (array) \apply_filters_ref_array(
			'the_seo_framework_conflicting_plugins_type',
			[
				$this->conflicting_plugins()[ $type ] ?? [],
				$type,
			]
		);
	}

	/**
	 * Detect active plugin by constant, class or function existence.
	 *
	 * Note: Class check is 3 times as slow as defined check. Function check is 2 times as slow.
	 *
	 * @since 1.3.0
	 * @since 2.8.0 1. Can now check for globals.
	 *              2. Switched detection order from FAST to SLOW.
	 * @since 4.0.6 Can no longer autoload classes.
	 *
	 * @param array $plugins Array of array for constants, classes and / or functions to check for plugin existence.
	 * @return boolean True if plugin exists or false if plugin constant, class or function not detected.
	 */
	public function detect_plugin( $plugins ) {

		foreach ( $plugins['globals'] ?? [] as $name )
			if ( isset( $GLOBALS[ $name ] ) )
				return true;

		// Check for constants
		foreach ( $plugins['constants'] ?? [] as $name )
			if ( \defined( $name ) )
				return true;

		// Check for functions
		foreach ( $plugins['functions'] ?? [] as $name )
			if ( \function_exists( $name ) )
				return true;

		// Check for classes
		foreach ( $plugins['classes'] ?? [] as $name )
			if ( class_exists( $name, false ) ) // phpcs:ignore, TSF.Performance.Functions.PHP -- we don't autoload.
				return true;

		// No globals, constant, function, or class found to exist
		return false;
	}

	/**
	 * Detect if you can use the given constants, functions and classes.
	 * All inputs must be available for this method to return true.
	 * Memoizes the return value for the input argument--sorts the array deeply to ensure a match.
	 *
	 * @since 2.5.2
	 * @since 4.1.4 Fixed sorting algorithm from fribbling-me to resolving-me. Nothing changed but legibility.
	 * @since 4.2.0 Rewrote sorting algorithm; now, it's actually good.
	 * @uses $this->detect_plugin_multi()
	 *
	 * @param array[] $plugins   Array of array for globals, constants, classes
	 *                           and/or functions to check for plugin existence.
	 * @param bool    $use_cache Bypasses cache if false
	 */
	public function can_i_use( $plugins = [], $use_cache = true ) {

		if ( ! $use_cache )
			return $this->detect_plugin_multi( $plugins );

		ksort( $plugins );

		foreach ( $plugins as &$test )
			sort( $test );

		// phpcs:ignore, WordPress.PHP.DiscouragedPHPFunctions -- No objects are inserted, nor is this ever unserialized.
		$key = serialize( $test );

		return memo( null, $key ) ?? memo( $this->detect_plugin_multi( $plugins ), $key );
	}

	/**
	 * Detect active plugin by constant, class or function existence.
	 * All parameters must match and return true.
	 *
	 * @since 2.5.2
	 * @since 4.0.6 1. Can now check for globals.
	 *              2. Switched detection order from FAST to SLOW.
	 *              3. Can no longer autoload classes.
	 * This method is only used by can_i_use(), and is only effective in the Ultimate Member compat file...
	 * @TODO deprecate?
	 *
	 * @param array[] $plugins Array of array for constants, classes
	 *                         and / or functions to check for plugin existence.
	 * @return bool True if ALL functions classes and constants exists
	 *              or false if plugin constant, class or function not detected.
	 */
	public function detect_plugin_multi( $plugins ) {

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

		// All classes, functions and constant have been found to exist
		return true;
	}

	/**
	 * Checks if the (parent) theme name is loaded.
	 *
	 * @since 2.1.0
	 * @since 4.2.0 No longer "loads" the theme; instead, simply compares input to active theme options.
	 *
	 * @param string|array $themes The theme names to test.
	 * @return bool is theme active.
	 */
	public function is_theme( $themes = '' ) {

		$active_theme = [
			strtolower( \get_option( 'stylesheet' ) ), // Parent
			strtolower( \get_option( 'template' ) ),   // Child
		];

		foreach ( (array) $themes as $theme )
			if ( \in_array( strtolower( $theme ), $active_theme, true ) )
				return true;

		return false;
	}

	/**
	 * Determines if other SEO plugins are active.
	 * Memoizes the return value.
	 *
	 * @since 1.3.0
	 * @since 2.6.0 Uses new style detection.
	 * @since 3.1.0 The filter no longer short-circuits the function when it's false.
	 *
	 * @return bool SEO plugin detected.
	 */
	public function detect_seo_plugins() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo() ) return $memo;

		$conflicting_plugin = array_intersect( $this->get_conflicting_plugins( 'seo_tools' ), $this->active_plugins() );

		return memo(
			/**
			 * @since 2.6.1
			 * @since 3.1.0 Added second and third parameters.
			 * @param bool   $detected    Whether the plugin should be detected.
			 * @param string $plugin_name The plugin name as defined in `$this->conflicting_plugins()`.
			 * @param string $plugin      The plugin that's been detected.
			 */
			$conflicting_plugin && \apply_filters_ref_array(
				'the_seo_framework_seo_plugin_detected',
				[
					true,
					key( $conflicting_plugin ),
					reset( $conflicting_plugin ),
				]
			)
		);
	}

	/**
	 * Determines if other Open Graph or SEO plugins are active.
	 * Memoizes the return value.
	 *
	 * @since 1.3.0
	 * @since 2.8.0 No longer checks for old style filter.
	 * @since 3.1.0 The filter no longer short-circuits the function when it's false.
	 *
	 * @return bool True if OG or SEO plugin detected.
	 */
	public function detect_og_plugin() {

		// Detect SEO plugins beforehand.
		if ( $this->detect_seo_plugins() )
			return true;

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo() ) return $memo;

		$conflicting_plugin = array_intersect( $this->get_conflicting_plugins( 'open_graph' ), $this->active_plugins() );

		return memo(
			/**
			 * @since 2.6.1
			 * @since 3.1.0 Added second and third parameters.
			 * @param bool   $detected    Whether the plugin should be detected.
			 * @param string $plugin_name The plugin name as defined in `$this->conflicting_plugins()`.
			 * @param string $plugin      The plugin that's been detected.
			 */
			$conflicting_plugin && \apply_filters_ref_array(
				'the_seo_framework_og_plugin_detected',
				[
					true,
					key( $conflicting_plugin ),
					reset( $conflicting_plugin ),
				]
			)
		);
	}

	/**
	 * Determines if other Twitter Card plugins are active.
	 * Memoizes the return value.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 The filter no longer short-circuits the function when it's false.
	 *
	 * @return bool Twitter Card plugin detected.
	 */
	public function detect_twitter_card_plugin() {

		// Detect SEO plugins beforehand.
		if ( $this->detect_seo_plugins() )
			return true;

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo() ) return $memo;

		$conflicting_plugin = array_intersect( $this->get_conflicting_plugins( 'twitter_card' ), $this->active_plugins() );

		return memo(
			/**
			 * @since 2.6.1
			 * @param bool   $detected    Whether the plugin should be detected.
			 * @param string $plugin_name The plugin name as defined in `$this->conflicting_plugins()`.
			 * @param string $plugin      The plugin that's been detected.
			 */
			$conflicting_plugin && \apply_filters_ref_array(
				'the_seo_framework_twittercard_plugin_detected',
				[
					true,
					key( $conflicting_plugin ),
					reset( $conflicting_plugin ),
				]
			)
		);
	}

	/**
	 * Determines if other Schema.org LD+Json plugins are active.
	 *
	 * @since 1.3.0
	 * @since 2.6.1 Always return false. Let other plugin authors decide its value.
	 * @TODO Make a list of plugins, so the users are well-informed.
	 *
	 * @return bool Whether another Schema.org plugin is active.
	 */
	public function has_json_ld_plugin() {
		/**
		 * @since 2.6.5
		 * @param bool $detected Whether a conflicting schema plugin is detected.
		 */
		return (bool) \apply_filters( 'the_seo_framework_ldjson_plugin_detected', false );
	}

	/**
	 * Determines if other Sitemap plugins are active.
	 * Memoizes the return value.
	 *
	 * @since 2.1.0
	 * @since 3.1.0 The filter no longer short-circuits the function when it's false.
	 *
	 * @return bool
	 */
	public function detect_sitemap_plugin() {

		// Detect SEO plugins beforehand.
		if ( $this->detect_seo_plugins() )
			return true;

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo() ) return $memo;

		$conflicting_plugin = array_intersect( $this->get_conflicting_plugins( 'sitemaps' ), $this->active_plugins() );

		return memo(
			/**
			 * @since 2.6.1
			 * @param bool   $detected    Whether the plugin should be detected.
			 * @param string $plugin_name The plugin name as defined in `$this->conflicting_plugins()`.
			 * @param string $plugin      The plugin that's been detected.
			 */
			$conflicting_plugin && \apply_filters_ref_array(
				'the_seo_framework_sitemap_plugin_detected',
				[
					true,
					key( $conflicting_plugin ),
					reset( $conflicting_plugin ),
				]
			)
		);
	}

	/**
	 * Determines if other Multilingual plugins are active.
	 * Memoizes the return value.
	 *
	 * @since 4.3.0
	 *
	 * @return bool SEO plugin detected.
	 */
	public function detect_multilingual_plugins() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo() ) return $memo;

		$conflicting_plugin = array_intersect( $this->get_conflicting_plugins( 'multilingual' ), $this->active_plugins() );

		return memo(
			/**
			 * @since 4.3.0
			 * @param bool   $detected    Whether the plugin should be detected.
			 * @param string $plugin_name The plugin name as defined in `$this->conflicting_plugins()`.
			 * @param string $plugin      The plugin that's been detected.
			 */
			$conflicting_plugin && \apply_filters_ref_array(
				'the_seo_framework_multilingual_plugin_detected',
				[
					true,
					key( $conflicting_plugin ),
					reset( $conflicting_plugin ),
				]
			)
		);
	}

	/**
	 * Tells whether WP 5.5 Core Sitemaps are used.
	 * Memoizes the return value.
	 *
	 * @since 4.1.2
	 *
	 * @return bool
	 */
	public function use_core_sitemaps() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo() ) return $memo;

		if ( $this->get_option( 'sitemaps_output' ) )
			return memo( false );

		$wp_sitemaps_server = \wp_sitemaps_get_server();

		return memo(
			method_exists( $wp_sitemaps_server, 'sitemaps_enabled' )
				&& $wp_sitemaps_server->sitemaps_enabled()
		);
	}

	/**
	 * Detects presence of a page builder that renders content dynamically.
	 *
	 * Detects the following builders:
	 * - Divi Builder by Elegant Themes
	 * - Visual Composer by WPBakery
	 *
	 * @since 4.1.0
	 *
	 * @return bool
	 */
	public function detect_non_html_page_builder() {
		return memo() ?? memo(
			/**
			 * @since 4.1.0
			 * @param bool $detected Whether an active page builder that renders content dynamically is detected.
			 * @NOTE not to be confused with `the_seo_framework_detect_non_html_page_builder`, which tests
			 *       the page builder status for each post individually.
			 */
			(bool) \apply_filters(
				'the_seo_framework_shortcode_based_page_builder_active',
				$this->detect_plugin( [
					'constants' => [
						'ET_BUILDER_VERSION',
						'WPB_VC_VERSION',
					],
				] )
			)
		);
	}

	/**
	 * Detects presence of robots.txt in root folder.
	 * Memoizes the return value.
	 *
	 * @since 2.5.2
	 * @since 4.0.0 Now tries to load `wp-admin/includes/file.php` to prevent a fatal error.
	 *
	 * @return bool Whether the robots.txt file exists.
	 */
	public function has_robots_txt() {
		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo() ) return $memo;

		// Ensure get_home_path() is declared.
		if ( ! \function_exists( '\\get_home_path' ) )
			require_once \ABSPATH . 'wp-admin/includes/file.php';

		$path = \get_home_path() . 'robots.txt';

		// phpcs:ignore, TSF.Performance.Functions.PHP -- we use path, not URL.
		return memo( file_exists( $path ) );
	}

	/**
	 * Detects presence of sitemap.xml in root folder.
	 * Memoizes the return value.
	 *
	 * @since 2.5.2
	 * @since 4.0.0 Now tries to load `wp-admin/includes/file.php` to prevent a fatal error.
	 *
	 * @return bool Whether the sitemap.xml file exists.
	 */
	public function has_sitemap_xml() {
		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo() ) return $memo;

		// Ensure get_home_path() is declared.
		if ( ! \function_exists( '\\get_home_path' ) )
			require_once \ABSPATH . 'wp-admin/includes/file.php';

		$path = \get_home_path() . 'sitemap.xml';

		// phpcs:ignore, TSF.Performance.Functions.PHP -- we use path, not URL.
		return memo( file_exists( $path ) );
	}

	/**
	 * Tests if the post type archive of said post type contains public posts.
	 * Memoizes the return value.
	 *
	 * @since 4.2.0
	 * @slow The queried result is not stored in WP Post's cache, which would allow
	 *       direct access to all values of the post (if requested). This is because
	 *       we're using `'fields' => 'ids'` instead of `'fields' => 'all'`.
	 *
	 * @param string $post_type The post type to test.
	 * @return bool True if a post is found in the archive, false otherwise.
	 */
	public function has_posts_in_post_type_archive( $post_type ) {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo( null, $post_type ) ) return $memo;

		$query = new \WP_Query( [
			'posts_per_page' => 1,
			'post_type'      => [ $post_type ],
			'orderby'        => 'date',
			'order'          => 'ASC',
			'post_status'    => 'publish',
			'has_password'   => false,
			'fields'         => 'ids',
			'cache_results'  => false,
			'no_found_rows'  => true,
		] );

		return memo( ! empty( $query->posts ), $post_type );
	}

	/**
	 * Detects if we're on a Gutenberg page.
	 *
	 * @since 3.1.0
	 * @since 3.2.0 1. Now detects the WP 5.0 block editor.
	 *              2. Method is now public.
	 * @TODO use the WP 5.0+ current_screen()->is_block_editor()?
	 *
	 * @return bool
	 */
	public function is_gutenberg_page() {
		if ( \function_exists( '\\use_block_editor_for_post' ) )
			return ! empty( $GLOBALS['post'] ) && \use_block_editor_for_post( $GLOBALS['post'] );

		if ( \function_exists( '\\is_gutenberg_page' ) )
			return \is_gutenberg_page();

		return false;
	}

	/**
	 * Determines whether we can output sitemap or not based on options and blog status.
	 *
	 * @since 2.6.0
	 * @since 2.9.2 No longer checks for plain and ugly permalinks.
	 * @since 4.0.0 Removed caching.
	 *
	 * @return bool
	 */
	public function can_run_sitemap() {
		return $this->get_option( 'sitemaps_output' ) && ! $this->current_blog_is_spam_or_deleted();
	}

	/**
	 * Returns the robots.txt location URL.
	 * Only allows root domains.
	 *
	 * @since 2.9.2
	 * @since 4.0.2 Now uses the preferred URL scheme.
	 * @global \WP_Rewrite $wp_rewrite
	 *
	 * @return string URL location of robots.txt. Unescaped.
	 */
	public function get_robots_txt_url() {

		if ( $GLOBALS['wp_rewrite']->using_permalinks() && ! $this->is_subdirectory_installation() ) {
			$home = \trailingslashit( $this->set_preferred_url_scheme( $this->get_home_host() ) );
			$path = "{$home}robots.txt";
		} elseif ( $this->has_robots_txt() ) {
			$home = \trailingslashit( $this->set_preferred_url_scheme( \get_option( 'home' ) ) );
			$path = "{$home}robots.txt";
		} else {
			$path = '';
		}

		return $path;
	}

	/**
	 * Determines if the current installation is on a subdirectory.
	 * Memoizes the return value.
	 *
	 * @since 2.9.0
	 *
	 * @return bool
	 */
	public function is_subdirectory_installation() {
		return memo() ?? memo(
			(bool) \strlen(
				ltrim(
					parse_url(
						\get_option( 'home' ),
						\PHP_URL_PATH
					) ?? '',
					' \\/'
				)
			)
		);
	}

	/**
	 * Registers plugin cache checks on plugin activation.
	 *
	 * @since 4.3.0
	 */
	public function reset_check_plugin_conflicts() {
		$this->update_static_cache( 'check_seo_plugin_conflicts', 1 );
	}

	/**
	 * Determines whether the text has recognizable transformative syntax.
	 *
	 * It tests Yoast SEO before Rank Math because that one is more popular, thus more
	 * likely to yield a result.
	 *
	 * @todo test all [ 'extension', 'yoast', 'aioseo', 'rankmath', 'seopress' ]
	 * @since 4.2.7
	 * @since 4.2.8 Added SEOPress support.
	 *
	 * @param string $text The text to evaluate
	 * @return bool
	 */
	public function has_unprocessed_syntax( $text ) {

		foreach ( [ 'yoast', 'rankmath', 'seopress' ] as $type )
			if ( $this->{"has_{$type}_syntax"}( $text ) ) return true;

		return false;
	}

	/**
	 * Determines if the input text has transformative Yoast SEO syntax.
	 *
	 * TODO rename to yoast_seo?
	 *
	 * @since 4.0.5
	 * @since 4.2.7 1. Added wildcard `ct_`, and `cf_` detection.
	 *              2. Added detection for various other types
	 *              2. Removed wildcard `cs_` detection.
	 * @see $this->has_unprocessed_syntax(), the caller.
	 * @link <https://yoast.com/help/list-available-snippet-variables-yoast-seo/> (This list contains false information)
	 * @link <https://theseoframework.com/extensions/transport/#faq/what-data-is-transformed>
	 *
	 * @param string $text The text to evaluate.
	 * @return bool
	 */
	public function has_yoast_syntax( $text ) {

		// %%id%% is the shortest valid tag... ish. Let's stop at 6.
		if ( \strlen( $text ) < 6 || ! str_contains( $text, '%%' ) )
			return false;

		$tags = umemo( __METHOD__ . '/tags' );

		if ( ! $tags ) {
			$tags = umemo(
				__METHOD__ . '/tags',
				[
					'simple'       => implode(
						'|',
						[
							// These are Preserved by Transport. Test first, for they are more likely in text.
							'focuskw',
							'page',
							'pagenumber',
							'pagetotal',
							'primary_category',
							'searchphrase',
							'term404',
							'wc_brand',
							'wc_price',
							'wc_shortdesc',
							'wc_sku',

							// These are transformed by Transport
							'archive_title',
							'author_first_name',
							'author_last_name',
							'caption',
							'category',
							'category_description',
							'category_title',
							'currentdate',
							'currentday',
							'currentmonth',
							'currentyear',
							'date',
							'excerpt',
							'excerpt_only',
							'id',
							'modified',
							'name',
							'parent_title',
							'permalink',
							'post_content',
							'post_year',
							'post_month',
							'post_day',
							'pt_plural',
							'pt_single',
							'sep',
							'sitedesc',
							'sitename',
							'tag',
							'tag_description',
							'term_description',
							'term_title',
							'title',
							'user_description',
							'userid',
						]
					),
					'wildcard_end' => implode( '|', [ 'ct_', 'cf_' ] ),
				]
			);
		}

		return preg_match( "/%%(?:{$tags['simple']})%%/", $text )
			|| preg_match( "/%%(?:{$tags['wildcard_end']})[^%]+?%%/", $text );
	}

	/**
	 * Determines if the input text has transformative Rank Math syntax.
	 *
	 * @since 4.2.7
	 * @since 4.2.8 Actualized the variable list.
	 * @link <https://theseoframework.com/extensions/transport/#faq/what-data-is-transformed>
	 *       Rank Math has no documentation on this list, but we sampled their code.
	 * @see $this->has_unprocessed_syntax(), the caller.
	 *
	 * @param string $text The text to evaluate.
	 * @return bool
	 */
	public function has_rankmath_syntax( $text ) {

		// %id% is the shortest valid tag... ish. Let's stop at 4.
		if ( \strlen( $text ) < 4 || ! str_contains( $text, '%' ) )
			return false;

		$tags = umemo( __METHOD__ . '/tags' );

		if ( ! $tags ) {
			$tags = umemo(
				__METHOD__ . '/tags',
				[
					'simple'       => implode(
						'|',
						[
							// These are Preserved by Transport. Test first, for they are more likely in text.
							'currenttime', // Rank Math has two currenttime, this one is simple.
							'filename',
							'focuskw',
							'group_desc',
							'group_name',
							'keywords',
							'org_name',
							'org_logo',
							'org_url',
							'page',
							'pagenumber',
							'pagetotal',
							'post_thumbnail',
							'primary_category',
							'primary_taxonomy_terms',
							'url',
							'wc_brand',
							'wc_price',
							'wc_shortdesc',
							'wc_sku',
							'currenttime', // Rank Math has two currenttime, this one is simple.

							// These are transformed by Transport
							'category',
							'categories',
							'currentdate',
							'currentday',
							'currentmonth',
							'currentyear',
							'date',
							'excerpt',
							'excerpt_only',
							'id',
							'modified',
							'name',
							'parent_title',
							'post_author',
							'pt_plural',
							'pt_single',
							'seo_title',
							'seo_description',
							'sep',
							'sitedesc',
							'sitename',
							'tag',
							'tags',
							'term',
							'term_description',
							'title',
							'user_description',
							'userid',
						]
					),
					// Check out for ref RankMath\Replace_Variables\Replacer::set_up_replacements();
					'wildcard_end' => implode(
						'|',
						[
							'categories',
							'count',
							'currenttime',
							'customfield',
							'customterm',
							'customterm_desc',
							'date',
							'modified',
							'tags',
						]
					),
				]
			);
		}

		return preg_match( "/%(?:{$tags['simple']})%/", $text )
			|| preg_match( "/%(?:{$tags['wildcard_end']})\([^\)]+?\)%/", $text );
	}

	/**
	 * Determines if the input text has transformative SEOPress syntax.
	 *
	 * @since 4.2.8
	 * @link <https://theseoframework.com/extensions/transport/#faq/what-data-is-transformed>
	 *       SEOPress has no documentation on this list, but we sampled their code.
	 * @see $this->has_unprocessed_syntax(), the caller.
	 *
	 * @param string $text The text to evaluate.
	 * @return bool
	 */
	public function has_seopress_syntax( $text ) {

		// %%sep%% is the shortest valid tag... ish. Let's stop at 7.
		if ( \strlen( $text ) < 7 || ! str_contains( $text, '%%' ) )
			return false;

		$tags = umemo( __METHOD__ . '/tags' );

		if ( ! $tags ) {
			$tags = umemo(
				__METHOD__ . '/tags',
				[
					'simple'       => implode(
						'|',
						[
							// These are Preserved by Transport. Test first, for they are more likely in text.
							'author_website',
							'current_pagination',
							'currenttime',
							'post_thumbnail_url',
							'post_url',
							'target_keyword',
							'wc_single_price',
							'wc_single_price_exc_tax',
							'wc_sku',

							// These are transformed by Transport
							'_category_description',
							'_category_title',
							'archive_title',
							'author_bio',
							'author_first_name',
							'author_last_name',
							'author_nickname',
							'currentday',
							'currentmonth',
							'currentmonth_num',
							'currentmonth_short',
							'currentyear',
							'date',
							'excerpt',
							'post_author',
							'post_category',
							'post_content',
							'post_date',
							'post_excerpt',
							'post_modified_date',
							'post_tag',
							'post_title',
							'sep',
							'sitedesc',
							'sitename',
							'sitetitle',
							'tag_description',
							'tag_title',
							'tagline',
							'term_description',
							'term_title',
							'title',
							'wc_single_cat',
							'wc_single_short_desc',
							'wc_single_tag',
						]
					),
					// Check out for ref somewhere in SEOPress, seopress_get_dyn_variables() is one I guess.
					'wildcard_end' => implode(
						'|',
						[
							'_cf_',
							'_ct_',
							'_ucf_',
						]
					),
				]
			);
		}

		return preg_match( "/%%(?:{$tags['simple']})%%/", $text )
			|| preg_match( "/%%(?:{$tags['wildcard_end']})[^%]+?%%/", $text );
	}
}
