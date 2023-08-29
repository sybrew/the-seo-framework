<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Detect
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

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
class Detect extends Render {

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
	 * Determines whether the main query supports custom SEO.
	 *
	 * @since 4.0.0
	 * @since 4.0.2 Now tests for an existing post/term ID when on singular/term pages.
	 * @since 4.0.3 Can now assert empty categories again by checking for taxonomy support.
	 * @since 4.2.4 Added detection for AJAX, Cron, JSON, and REST queries (they're not supported as SEO-able queries).
	 * @since 4.3.0 Removed detection for JSON(P) and XML type requests, because these cannot be assumed as legitimate.
	 *
	 * @return bool
	 */
	public function query_supports_seo() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo() ) return $memo;

		switch ( true ) {
			case \is_feed():
			case \wp_doing_ajax():
			case \wp_doing_cron():
			case \defined( 'REST_REQUEST' ) && \REST_REQUEST:
				$supported = false;
				break;
			case $this->is_singular():
				// This is the most likely scenario, but may collide with is_feed() et al.
				$supported = $this->is_post_type_supported() && $this->get_the_real_id();
				break;
			case \is_post_type_archive():
				$supported = $this->is_post_type_archive_supported();
				break;
			case $this->is_term_meta_capable():
				// When a term has no posts attached, it'll not return a post type, and it returns a 404 late in the loop.
				// This is because get_post_type() tries to assert the first post in the loop here.
				// Thus, we test for is_taxonomy_supported() instead.
				$supported = $this->is_taxonomy_supported() && $this->get_the_real_id();
				break;
			default:
				// Everything else: homepage, 404, search, edge-cases.
				$supported = true;
		}

		/**
		 * Override false negatives on exploit.
		 *
		 * This protects against (accidental) negative-SEO bombarding.
		 * Support broken queries, so we can noindex them.
		 */
		if ( ! $supported && $this->is_query_exploited() )
			$supported = true;

		/**
		 * @since 4.0.0
		 * @param bool $supported Whether the query supports SEO.
		 */
		return memo( (bool) \apply_filters( 'the_seo_framework_query_supports_seo', $supported ) );
	}

	/**
	 * Determines when paged/page is exploited.
	 * Memoizes the return value.
	 *
	 * Google is acting "smart" nowadays, and follows everything that remotely resembles a link. Therefore, unintentional
	 * queries can occur in WordPress. WordPress deals with this well, alas, the query parser (WP_Query::parse_query)
	 * doesn't rectify the mixed signals it receives. Instead, it only sanitizes it, resulting in a combobulated mess.
	 * Ultimately, this leads to non-existing blog archives, among other failures.
	 *
	 * Example 1: `/?p=nonnumeric` will cause an issue. We will see a non-existing blog page. `is_home` is true, but
	 * `page_id` leads to 0 while the database expects the blog page to be another page. So, `is_posts_page` is
	 * incorrectly false. This is mitigated via the canonical URL, but that MUST output, thus overriding otherwise chosen
	 * and expected behavior.
	 *
	 * Example 2: `/page/2/?p=nonnumeric` will cause a bigger issue. What happens is that `is_home` will again be true,
	 * but so will `is_paged`. `paged` will be set to `2` (as per example URL). The page ID will again be set to `0`,
	 * which is completely false. The canonical URL will be malformed. Even more so, Google can ignore the canonical URL,
	 * so we MUST output noindex.
	 *
	 * Example 3: `/page/2/?X=nonnumeric` will also cause the same issues as in example 2. Where X can be:
	 * `page_id`, `attachment_id`, `year`, `monthnum`, `day`, `w`, `m`, and of course `p`.
	 *
	 * Example 4: `/?hour=nonnumeric`, the same issue as Example 1. The canonical URL is malformed, noindex is set, and
	 * link relationships will be active. A complete mess. `minute` and `second` are also affected the same way.
	 *
	 * Example 5: `/page/2/?p=0`, this is the trickiest. It's indicative of a paginated blog, but also the homepage. When
	 * the homepage is not a blog, then this query is malformed. Otherwise, however, it's a good query.
	 *
	 * @since 4.0.5
	 * @since 4.2.7 1. Added detection `not_home_as_page`, specifically for query variable `search`.
	 *              2. Improved detection for `cat` and `author`, where the value may only be numeric above 0.
	 * @since 4.2.8 Now blocks any publicly registered variable requested to the home-as-page.
	 * @global \WP_Query $wp_query
	 *
	 * @return bool Whether the query is (accidentally) exploited.
	 *              Defaults to false when `advanced_query_protection` option is disabled.
	 *              False when there's a query-ID found.
	 *              False when no custom query is set (for the homepage).
	 *              Otherwise, it performs query tests.
	 */
	public function is_query_exploited() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo() ) return $memo;

		if ( ! $this->get_option( 'advanced_query_protection' ) )
			return memo( false );

		// When the page ID is not 0, a real page will always be returned.
		if ( $this->get_the_real_id() )
			return memo( false );

		global $wp_query;

		// When no special query data is registered, ignore this. Don't set cache.
		if ( ! isset( $wp_query->query ) )
			return false;

		/**
		 * @since 4.0.5
		 * @param array $exploitables The exploitable endpoints by type.
		 * @since 4.2.7 Added index `not_home_as_page` with value `search`.
		 */
		$exploitables = \apply_filters(
			'the_seo_framework_exploitable_query_endpoints',
			[
				'numeric'          => [
					'page_id',
					'attachment_id',
					'year',
					'monthnum',
					'day',
					'w',
					'm',
					'p',
					'paged', // 'page' is mitigated by WordPress.
					'hour',
					'minute',
					'second',
					'subpost_id',
				],
				'numeric_array'    => [
					'cat',
					'author',
				],
				'requires_s'       => [
					'sentence',
				],
				// When the blog (home) is a page then these requests to any registered query variable will cause issues,
				// but only when the page ID returns 0. (We already tested for `if ( $this->get_the_real_id() )` above).
				// This global's property is only populated with requested parameters that match registered `public_query_vars`.
				// We only need one to pass this test. We could use array_key_first()... but that may be nulled (out of our control).
				'not_home_as_page' => array_keys( $GLOBALS['wp']->query_vars ?? [] ),
			]
		);

		$query = $wp_query->query;

		foreach ( $exploitables as $type => $qvs ) {
			foreach ( $qvs as $qv ) {
				// Only test isset, because falsey or empty-array is what we need to test against.
				if ( ! isset( $query[ $qv ] ) ) continue;

				switch ( $type ) {
					case 'numeric':
						if ( '0' === $query[ $qv ] || ! is_numeric( $query[ $qv ] ) )
							return memo( true );
						break;

					case 'numeric_array':
						// We can't protect non-pretty permalinks.
						if ( ! $this->pretty_permalinks ) break;

						// If WordPress didn't canonical_redirect() the user yet, it's exploited.
						// WordPress mitigates this via a 404 query when a numeric value is found.
						if ( ! preg_match( '/^[1-9][0-9]*$/', $query[ $qv ] ) )
							return memo( true );
						break;

					case 'requires_s':
						if ( ! isset( $query['s'] ) )
							return memo( true );
						break;

					case 'not_home_as_page':
						// isset($query[$qv]) is already executed. Just test if homepage ID still works.
						// !$this->get_the_real_id() is already executed. Just test if home is a page.
						if ( $this->is_home_as_page() )
							return memo( true );
				}
			}
		}

		return memo( false );
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
	 * Detects if the current or inputted post type is supported and not disabled.
	 *
	 * @since 3.1.0
	 * @since 4.0.5 The `$post_type` fallback now uses a real query ID, instead of `$GLOBALS['post']`;
	 *              mitigating issues with singular-archives pages (blog, shop, etc.).
	 *
	 * @param string $post_type Optional. The post type to check.
	 * @return bool
	 */
	public function is_post_type_supported( $post_type = '' ) {

		$post_type = $post_type ?: $this->get_current_post_type();

		/**
		 * @since 2.6.2
		 * @since 3.1.0 The first parameter is always a boolean now.
		 * @param bool   $supported           Whether the post type is supported.
		 * @param string $post_type_evaluated The evaluated post type.
		 */
		return (bool) \apply_filters_ref_array(
			'the_seo_framework_supported_post_type',
			[
				$post_type
					&& ! $this->is_post_type_disabled( $post_type )
					&& \in_array( $post_type, $this->get_public_post_types(), true ),
				$post_type,
			]
		);
	}

	/**
	 * Detects if the current or inputted post type's archive is supported and not disabled.
	 *
	 * @since 4.2.8
	 * @uses `tsf()->is_post_type_supported()`
	 *
	 * @param string $post_type Optional. The post type's archive to check.
	 * @return bool
	 */
	public function is_post_type_archive_supported( $post_type = '' ) {

		$post_type = $post_type ?: $this->get_current_post_type();

		/**
		 * @since 4.2.8
		 * @param bool   $supported           Whether the post type archive is supported.
		 * @param string $post_type_evaluated The evaluated post type.
		 */
		return (bool) \apply_filters_ref_array(
			'the_seo_framework_supported_post_type_archive',
			[
				$post_type
					&& $this->is_post_type_supported( $post_type )
					&& \in_array( $post_type, $this->get_public_post_type_archives(), true ),
				$post_type,
			]
		);
	}

	/**
	 * Determines if the taxonomy supports The SEO Framework.
	 *
	 * Checks if at least one taxonomy objects post type supports The SEO Framework,
	 * and whether the taxonomy is public and rewritable.
	 *
	 * @since 4.0.0
	 *
	 * @param string $taxonomy Optional. The taxonomy name.
	 * @return bool True if at least one post type in taxonomy isn't disabled.
	 */
	public function is_taxonomy_supported( $taxonomy = '' ) {

		$taxonomy = $taxonomy ?: $this->get_current_taxonomy();

		/**
		 * @since 3.1.0
		 * @since 4.0.0 Now returns only returns false when all post types in the taxonomy aren't supported.
		 * @param bool   $post_type Whether the post type is supported
		 * @param string $post_type_evaluated The evaluated post type.
		 */
		return (bool) \apply_filters_ref_array(
			'the_seo_framework_supported_taxonomy',
			[
				$taxonomy
					&& ! $this->is_taxonomy_disabled( $taxonomy )
					&& \in_array( $taxonomy, $this->get_public_taxonomies(), true ),
				$taxonomy,
			]
		);
	}

	/**
	 * Checks (current) Post Type for having taxonomical archives.
	 * Memoizes the return value for the input argument.
	 *
	 * @since 2.9.3
	 * @since 4.0.5 The `$post_type` fallback now uses a real query ID, instead of `$GLOBALS['post']`;
	 *              mitigating issues with singular-archives pages (blog, shop, etc.).
	 * @global \WP_Screen $current_screen
	 *
	 * @param string $post_type Optional. The post type to check.
	 * @return bool True when the post type has taxonomies.
	 */
	public function post_type_supports_taxonomies( $post_type = '' ) {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo( null, $post_type ) ) return $memo;

		$post_type = $post_type ?: $this->get_current_post_type();

		// Return false if no post type if found -- do not memo that, for query call might be too early.
		return $post_type && memo( (bool) \get_object_taxonomies( $post_type, 'names' ), $post_type );
	}

	/**
	 * Returns a list of all supported post types with archives.
	 * Memoizes the return value.
	 *
	 * @since 4.2.0
	 * @since 4.2.8 Now filters via `tsf()->is_post_type_archive_supported()`.
	 *
	 * @return string[] Supported post types with post type archive support.
	 */
	public function get_supported_post_type_archives() {
		return memo() ?? memo(
			array_values(
				array_filter(
					$this->get_public_post_type_archives(),
					[ $this, 'is_post_type_archive_supported' ]
				)
			)
		);
	}

	/**
	 * Gets all post types that have PTA and could possibly support SEO.
	 * Memoizes the return value.
	 *
	 * @since 4.2.0
	 * @since 4.2.8 Added filter `the_seo_framework_public_post_type_archives`.
	 *
	 * @return string[] Public post types with post type archive support.
	 */
	public function get_public_post_type_archives() {
		return umemo( __METHOD__ )
			?? umemo(
				__METHOD__,
				/**
				 * Do not consider using this filter. Properly register your post type, noob.
				 *
				 * @since 4.2.8
				 * @param string[] $post_types The public post types.
				 */
				\apply_filters(
					'the_seo_framework_public_post_type_archives',
					array_values(
						array_filter(
							$this->get_public_post_types(),
							static fn( $post_type ) => \get_post_type_object( $post_type )->has_archive ?? false
						)
					)
				)
			);
	}

	/**
	 * Returns a list of all supported post types.
	 *
	 * @since 3.1.0
	 *
	 * @return string[] All supported post types.
	 */
	public function get_supported_post_types() {
		return memo() ?? memo(
			array_values(
				array_filter(
					$this->get_public_post_types(),
					[ $this, 'is_post_type_supported' ]
				)
			)
		);
	}

	/**
	 * Gets all post types that could possibly support SEO.
	 * Memoizes the return value.
	 *
	 * @since 4.1.0
	 * @since 4.1.4 Now resets the index keys of the return value.
	 *
	 * @return string[] All public post types.
	 */
	protected function get_public_post_types() {
		return umemo( __METHOD__ )
			?? umemo(
				__METHOD__,
				/**
				 * Do not consider using this filter. Properly register your post type, noob.
				 *
				 * @since 4.2.0
				 * @param string[] $post_types The public post types.
				 */
				\apply_filters(
					'the_seo_framework_public_post_types',
					array_values(
						array_filter(
							array_unique(
								array_merge(
									$this->get_forced_supported_post_types(),
									// array_keys() because get_post_types() gives a sequential array.
									array_keys( (array) \get_post_types( [ 'public' => true ] ) )
								)
							),
							'is_post_type_viewable'
						)
					)
				)
			);
	}

	/**
	 * Returns a list of builtin public post types.
	 *
	 * @since 3.1.0
	 * @since 4.2.0 Removed memoization.
	 *
	 * @return string[] Forced supported post types.
	 */
	protected function get_forced_supported_post_types() {
		/**
		* @since 3.1.0
		* @param string[] $forced Forced supported post types
		*/
		return (array) \apply_filters_ref_array(
			'the_seo_framework_forced_supported_post_types',
			[
				array_values( \get_post_types( [
					'public'   => true,
					'_builtin' => true,
				] ) ),
			]
		);
	}

	/**
	 * Returns a list of all supported taxonomies.
	 *
	 * @since 4.2.0
	 *
	 * @return string[] All supported taxonomies.
	 */
	public function get_supported_taxonomies() {
		return memo() ?? memo(
			array_values(
				array_filter( $this->get_public_taxonomies(), [ $this, 'is_taxonomy_supported' ] )
			)
		);
	}

	/**
	 * Gets all taxonomies that could possibly support SEO.
	 * Memoizes the return value.
	 *
	 * @since 4.1.0
	 *
	 * @return string[] The taxonomies that are public.
	 */
	protected function get_public_taxonomies() {
		return umemo( __METHOD__ )
			?? umemo(
				__METHOD__,
				/**
				 * Do not consider using this filter. Properly register your taxonomy, noob.
				 *
				 * @since 4.2.0
				 * @param string[] $post_types The public post types.
				 */
				\apply_filters(
					'the_seo_framework_public_taxonomies',
					array_filter(
						array_unique(
							array_merge(
								$this->get_forced_supported_taxonomies(),
								// array_values() because get_taxonomies() gives a sequential array.
								array_values( \get_taxonomies( [
									'public'   => true,
									'_builtin' => false,
								] ) )
							)
						),
						'is_taxonomy_viewable'
					)
				)
			);
	}

	/**
	 * Returns a list of builtin public taxonomies.
	 *
	 * @since 4.1.0
	 * @since 4.2.0 Removed memoization.
	 *
	 * @return string[] Forced supported taxonomies
	 */
	protected function get_forced_supported_taxonomies() {
		/**
		 * @since 4.1.0
		 * @param string[] $forced Forced supported post types
		 */
		return (array) \apply_filters_ref_array(
			'the_seo_framework_forced_supported_taxonomies',
			[
				array_values( \get_taxonomies( [
					'public'   => true,
					'_builtin' => true,
				] ) ),
			]
		);
	}

	/**
	 * Determines if the post type is disabled from SEO all optimization.
	 *
	 * @since 3.1.0
	 * @since 3.1.2 Now is fiterable.
	 * @since 4.0.5 The `$post_type` fallback now uses a real query ID, instead of `$GLOBALS['post']`;
	 *              mitigating issues with singular-archives pages (blog, shop, etc.).
	 *
	 * @param string $post_type Optional. The post type to check.
	 * @return bool True if disabled, false otherwise.
	 */
	public function is_post_type_disabled( $post_type = '' ) {

		$post_type = $post_type ?: $this->get_current_post_type();

		/**
		 * @since 3.1.2
		 * @param bool   $disabled
		 * @param string $post_type
		 */
		return \apply_filters_ref_array(
			'the_seo_framework_post_type_disabled',
			[
				isset( $this->get_option( 'disabled_post_types' )[ $post_type ] ),
				$post_type,
			]
		);
	}

	/**
	 * Checks if the taxonomy isn't disabled, and that at least one taxonomy
	 * objects post type supports The SEO Framework.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 1. Now returns true if at least one post type for the taxonomy is supported.
	 *              2. Now uses `is_post_type_supported()` instead of `is_post_type_disabled()`.
	 * @since 4.1.0 1. Now also checks for the option `disabled_taxonomies`.
	 *              2. Now applies filters `the_seo_framework_taxonomy_disabled`.
	 *
	 * @param string $taxonomy The taxonomy name.
	 * @return bool True if at least one post type in taxonomy is supported.
	 */
	public function is_taxonomy_disabled( $taxonomy = '' ) {

		$disabled = false;

		// First, test pertaining option directly.
		if ( $taxonomy && isset( $this->get_option( 'disabled_taxonomies' )[ $taxonomy ] ) ) {
			$disabled = true;
		} else {
			// Then, test some() post types.
			// Populate $disabled within loop, for the taxonomy might not have post types at all.
			foreach ( $this->get_post_types_from_taxonomy( $taxonomy ) as $type ) {
				if ( $this->is_post_type_supported( $type ) ) {
					$disabled = false;
					break;
				}
				$disabled = true;
			}
		}

		/**
		 * @since 4.1.0
		 * @param bool   $disabled
		 * @param string $taxonomy
		 */
		return \apply_filters_ref_array(
			'the_seo_framework_taxonomy_disabled',
			[
				$disabled,
				$taxonomy,
			]
		);
	}

	/**
	 * Determines whether a page or blog is on front.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Removed caching.
	 *
	 * @return bool
	 */
	public function has_page_on_front() {
		return 'page' === \get_option( 'show_on_front' );
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
