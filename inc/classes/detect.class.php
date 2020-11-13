<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Detect
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2020 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
	 * @credits Jetpack for most code.
	 *
	 * @return array List of active plugins.
	 */
	public function active_plugins() {

		static $active_plugins = null;

		if ( isset( $active_plugins ) )
			return $active_plugins;

		$active_plugins = (array) \get_option( 'active_plugins', [] );

		if ( \is_multisite() ) {
			// Due to legacy code, active_sitewide_plugins stores them in the keys,
			// whereas active_plugins stores them in the values.
			$network_plugins = array_keys( \get_site_option( 'active_sitewide_plugins', [] ) );
			if ( $network_plugins ) {
				$active_plugins = array_merge( $active_plugins, $network_plugins );
			}
		}

		sort( $active_plugins );

		return $active_plugins = array_unique( $active_plugins );
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
				'Yoast SEO'                  => 'wordpress-seo/wp-seo.php',
				'Yoast SEO Premium'          => 'wordpress-seo-premium/wp-seo-premium.php',
				'All in One SEO Pack'        => 'all-in-one-seo-pack/all_in_one_seo_pack.php',
				'SEO Ultimate'               => 'seo-ultimate/seo-ultimate.php',
				'Gregs High Performance SEO' => 'gregs-high-performance-seo/ghpseo.php',
				'SEOPress'                   => 'wp-seopress/seopress.php',
				'Rank Math'                  => 'seo-by-rank-math/rank-math.php',
				'Smart Crawl'                => 'smartcrawl-seo/wpmu-dev-seo.php',
			],
			'sitemaps'     => [
				'Google XML Sitemaps'                  => 'google-sitemap-generator/sitemap.php',
				'Better WordPress Google XML Sitemaps' => 'bwp-google-xml-sitemaps/bwp-simple-gxs.php', // Remove?
				'Google XML Sitemaps for qTranslate'   => 'google-xml-sitemaps-v3-for-qtranslate/sitemap.php', // Remove?
				'XML Sitemap & Google News feeds'      => 'xml-sitemap-feed/xml-sitemap.php',
				'Google Sitemap by BestWebSoft'        => 'google-sitemap-plugin/google-sitemap-plugin.php',
				'Simple Wp Sitemap'                    => 'simple-wp-sitemap/simple-wp-sitemap.php',
				'XML Sitemaps'                         => 'xml-sitemaps/xml-sitemaps.php',
			],
			'open_graph'   => [
				'Facebook Open Graph Meta Tags for WordPress' => 'wonderm00ns-simple-facebook-open-graph-tags/wonderm00n-open-graph.php',
				'Facebook Thumb Fixer'                  => 'facebook-thumb-fixer/_facebook-thumb-fixer.php',
				'NextGEN Facebook OG'                   => 'nextgen-facebook/nextgen-facebook.php',
				'Open Graph'                            => 'opengraph/opengraph.php',
				'Open Graph Protocol Framework'         => 'open-graph-protocol-framework/open-graph-protocol-framework.php',
				'Shareaholic2'                          => 'shareaholic/sexy-bookmarks.php',
				'Social Sharing Toolkit'                => 'social-sharing-toolkit/social_sharing_toolkit.php',
				'WordPress Social Sharing Optimization' => 'wpsso/wpsso.php',
				'WP Facebook Open Graph protocol'       => 'wp-facebook-open-graph-protocol/wp-facebook-ogp.php',
			],
			'twitter_card' => [],
		];

		/**
		 * @since 2.6.0
		 * @param array $conflicting_plugins The conflicting plugin list.
		 */
		return (array) \apply_filters( 'the_seo_framework_conflicting_plugins', $conflicting_plugins );
	}

	/**
	 * Fetches type of conflicting plugins.
	 *
	 * @since 2.6.0
	 *
	 * @param string $type The Key from $this->conflicting_plugins()
	 * @return array
	 */
	public function get_conflicting_plugins( $type = 'seo_tools' ) {

		$conflicting_plugins = $this->conflicting_plugins();

		if ( isset( $conflicting_plugins[ $type ] ) )
			return (array) \apply_filters( 'the_seo_framework_conflicting_plugins_type', $conflicting_plugins[ $type ], $type );

		return [];
	}

	/**
	 * Detect active plugin by constant, class or function existence.
	 *
	 * Note: Class check is 3 times as slow as defined check. Function check is 2 times as slow.
	 *
	 * @since 1.3.0
	 * @since 2.8.0 : 1. Can now check for globals.
	 *                2. Switched detection order from FAST to SLOW.
	 * @since 4.0.6 Can no longer autoload classes.
	 *
	 * @param array $plugins Array of array for constants, classes and / or functions to check for plugin existence.
	 * @return boolean True if plugin exists or false if plugin constant, class or function not detected.
	 */
	public function detect_plugin( $plugins ) {

		if ( isset( $plugins['globals'] ) ) {
			foreach ( $plugins['globals'] as $name ) {
				if ( isset( $GLOBALS[ $name ] ) ) {
					return true;
				}
			}
		}

		// Check for constants
		if ( isset( $plugins['constants'] ) ) {
			foreach ( $plugins['constants'] as $name ) {
				if ( \defined( $name ) ) {
					return true;
				}
			}
		}

		// Check for functions
		if ( isset( $plugins['functions'] ) ) {
			foreach ( $plugins['functions'] as $name ) {
				if ( \function_exists( $name ) ) {
					return true;
				}
			}
		}

		// Check for classes
		if ( isset( $plugins['classes'] ) ) {
			foreach ( $plugins['classes'] as $name ) {
				// phpcs:ignore, TSF.Performance.Functions.PHP -- we don't autoload.
				if ( class_exists( $name, false ) ) {
					return true;
				}
			}
		}

		// No globals, constant, function, or class found to exist
		return false;
	}

	/**
	 * Detect if you can use the given constants, functions and classes.
	 * All inputs must be available for this method to return true.
	 * Memoizes the return value for the input argument--sorts the array deeply to ensure a match.
	 *
	 * @since 2.5.2
	 * @uses $this->detect_plugin_multi()
	 *
	 * @param array $plugins   Array of array for globals, constants, classes
	 *                         and/or functions to check for plugin existence.
	 * @param bool  $use_cache Bypasses cache if false
	 */
	public function can_i_use( array $plugins = [], $use_cache = true ) {

		if ( ! $use_cache )
			return $this->detect_plugin_multi( $plugins );

		static $cache = [];

		$mapped = [];

		// Prepare multidimensional array for cache.
		foreach ( $plugins as $key => $func ) {
			if ( ! \is_array( $func ) )
				return false; // doing it wrong...

			// Sort alphanumeric by value, put values back after sorting.
			// TODO Use asort or usort instead???
			$func = array_flip( $func );
			ksort( $func );
			$func = array_flip( $func );

			// Glue with underscore and space for debugging purposes.
			$mapped[ $key ] = $key . '_' . implode( ' ', $func );
		}

		ksort( $mapped );
		// phpcs:ignore, WordPress.PHP.DiscouragedPHPFunctions -- No objects are inserted, nor is this ever unserialized.
		$key = serialize( $mapped );

		if ( isset( $cache[ $key ] ) )
			return $cache[ $key ];

		return $cache[ $key ] = $this->detect_plugin_multi( $plugins );
	}

	/**
	 * Detect active plugin by constant, class or function existence.
	 * All parameters must match and return true.
	 *
	 * @since 2.5.2
	 * @since 4.0.6 : 1. Can now check for globals.
	 *                2. Switched detection order from FAST to SLOW.
	 *                3. Can no longer autoload classes.
	 * This method is only used by can_i_use(), and is only effective in the Ultimate Member compat file...
	 * @TODO deprecate?
	 *
	 * @param array $plugins Array of array for constants, classes and / or functions to check for plugin existence.
	 * @return bool True if ALL functions classes and constants exists or false if plugin constant, class or function not detected.
	 */
	public function detect_plugin_multi( array $plugins ) {

		// Check for globals
		if ( isset( $plugins['globals'] ) ) {
			foreach ( $plugins['globals'] as $name ) {
				if ( ! isset( $GLOBALS[ $name ] ) ) {
					return false;
				}
			}
		}

		// Check for constants
		if ( isset( $plugins['constants'] ) ) {
			foreach ( $plugins['constants'] as $name ) {
				if ( ! \defined( $name ) ) {
					return false;
				}
			}
		}

		// Check for functions
		if ( isset( $plugins['functions'] ) ) {
			foreach ( $plugins['functions'] as $name ) {
				if ( ! \function_exists( $name ) ) {
					return false;
				}
			}
		}

		// Check for classes
		if ( isset( $plugins['classes'] ) ) {
			foreach ( $plugins['classes'] as $name ) {
				// phpcs:ignore, TSF.Performance.Functions.PHP -- we don't autoload.
				if ( ! class_exists( $name, false ) ) {
					return false;
				}
			}
		}

		// All classes, functions and constant have been found to exist
		return true;
	}

	/**
	 * Checks if the (parent) theme name is loaded.
	 *
	 * @since 2.1.0
	 *
	 * @param string|array $themes the current theme name.
	 * @return bool is theme active.
	 */
	public function is_theme( $themes = '' ) {

		if ( empty( $themes ) )
			return false;

		$wp_get_theme = \wp_get_theme();

		$theme_parent = strtolower( $wp_get_theme->get( 'Template' ) );
		$theme_name   = strtolower( $wp_get_theme->get( 'Name' ) );

		if ( \is_string( $themes ) ) {
			$themes = strtolower( $themes );
			if ( $themes === $theme_parent || $themes === $theme_name )
				return true;
		} elseif ( \is_array( $themes ) ) {
			foreach ( $themes as $theme ) {
				$theme = strtolower( $theme );
				if ( $theme === $theme_parent || $theme === $theme_name ) {
					return true;
				}
			}
		}

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

		static $detected = null;

		if ( isset( $detected ) )
			return $detected;

		$active_plugins = $this->active_plugins();

		if ( ! empty( $active_plugins ) ) {
			$conflicting_plugins = $this->get_conflicting_plugins( 'seo_tools' );

			foreach ( $conflicting_plugins as $plugin_name => $plugin ) {
				if ( \in_array( $plugin, $active_plugins, true ) ) {
					/**
					 * @since 2.6.1
					 * @since 3.1.0 Added second and third parameters.
					 * @param bool   $detected    Whether the plugin should be detected.
					 * @param string $plugin_name The plugin name as defined in `$this->conflicting_plugins()`.
					 * @param string $plugin      The plugin that's been detected.
					 */
					$detected = \apply_filters_ref_array(
						'the_seo_framework_seo_plugin_detected',
						[
							true,
							$plugin_name,
							$plugin,
						]
					);
					if ( $detected ) break;
				}
			}
		}

		return $detected = (bool) $detected;
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

		static $detected = null;

		if ( isset( $detected ) )
			return $detected;

		// Detect SEO plugins beforehand.
		if ( $this->detect_seo_plugins() )
			return $detected = true;

		$active_plugins = $this->active_plugins();

		if ( ! empty( $active_plugins ) ) {
			$conflicting_plugins = $this->get_conflicting_plugins( 'open_graph' );

			foreach ( $conflicting_plugins as $plugin_name => $plugin ) {
				if ( \in_array( $plugin, $active_plugins, true ) ) {
					/**
					 * @since 2.6.1
					 * @since 3.1.0 Added second and third parameters.
					 * @param bool   $detected    Whether the plugin should be detected.
					 * @param string $plugin_name The plugin name as defined in `$this->conflicting_plugins()`.
					 * @param string $plugin      The plugin that's been detected.
					 */
					$detected = \apply_filters_ref_array(
						'the_seo_framework_og_plugin_detected',
						[
							true,
							$plugin_name,
							$plugin,
						]
					);
					if ( $detected ) break;
				}
			}
		}

		return $detected = (bool) $detected;
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

		static $detected = null;

		if ( isset( $detected ) )
			return $detected;

		// Detect SEO plugins beforehand.
		if ( $this->detect_seo_plugins() )
			return $detected = true;

		$active_plugins = $this->active_plugins();

		if ( ! empty( $active_plugins ) ) {
			$conflicting_plugins = $this->get_conflicting_plugins( 'twitter_card' );

			foreach ( $conflicting_plugins as $plugin_name => $plugin ) {
				if ( \in_array( $plugin, $active_plugins, true ) ) {
					/**
					 * @since 2.6.1
					 * @param bool   $detected    Whether the plugin should be detected.
					 * @param string $plugin_name The plugin name as defined in `$this->conflicting_plugins()`.
					 * @param string $plugin      The plugin that's been detected.
					 */
					$detected = \apply_filters_ref_array(
						'the_seo_framework_twittercard_plugin_detected',
						[
							true,
							$plugin_name,
							$plugin,
						]
					);
					if ( $detected ) break;
				}
			}
		}

		return $detected = (bool) $detected;
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

		static $detected = null;

		if ( isset( $detected ) )
			return $detected;

		// Detect SEO plugins beforehand.
		if ( $this->detect_seo_plugins() )
			return $detected = true;

		$active_plugins = $this->active_plugins();

		if ( ! empty( $active_plugins ) ) {
			$conflicting_plugins = $this->get_conflicting_plugins( 'sitemaps' );

			foreach ( $conflicting_plugins as $plugin_name => $plugin ) {
				if ( \in_array( $plugin, $active_plugins, true ) ) {
					/**
					 * @since 2.6.1
					 * @param bool   $detected    Whether the plugin should be detected.
					 * @param string $plugin_name The plugin name as defined in `$this->conflicting_plugins()`.
					 * @param string $plugin      The plugin that's been detected.
					 */
					$detected = \apply_filters(
						'the_seo_framework_sitemap_plugin_detected',
						[
							true,
							$plugin_name,
							$plugin,
						]
					);
					if ( $detected ) break;
				}
			}
		}

		return $detected = (bool) $detected;
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
		static $use;

		if ( isset( $use ) ) return $use;

		if ( $this->get_option( 'sitemaps_output' ) )
			return $use = false;

		if ( \function_exists( '\\wp_sitemaps_get_server' ) ) {
			$wp_sitemaps_server = \wp_sitemaps_get_server();

			return $use =
				method_exists( $wp_sitemaps_server, 'sitemaps_enabled' )
				&& $wp_sitemaps_server->sitemaps_enabled();
		}

		return $use = false;
	}

	/**
	 * Detects presence of a page builder.
	 * Memoizes the return value.
	 *
	 * Detects the following builders:
	 * - Elementor by Elementor LTD
	 * - Divi Builder by Elegant Themes
	 * - Visual Composer by WPBakery
	 * - Page Builder by SiteOrigin
	 * - Beaver Builder by Fastline Media
	 *
	 * @since 4.0.0
	 * @since 4.0.6 The output is now filterable.
	 * @TODO deprecate?
	 * @ignore unused.
	 *
	 * @return bool
	 */
	public function detect_page_builder() {

		static $detected = null;

		if ( isset( $detected ) ) return $detected;

		/**
		 * @since 4.0.6
		 * @param bool $detected Whether an active page builder is detected.
		 * @NOTE not to be confused with `the_seo_framework_detect_page_builder`, which tests
		 *       the page builder status for each post individually.
		 */
		return $detected = (bool) \apply_filters(
			'the_seo_framework_page_builder_active',
			$this->detect_plugin( [
				'constants' => [
					'ELEMENTOR_VERSION',
					'ET_BUILDER_VERSION',
					'WPB_VC_VERSION',
					'SITEORIGIN_PANELS_VERSION',
					'FL_BUILDER_VERSION',
				],
			] )
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

		static $detected = null;

		if ( isset( $detected ) ) return $detected;

		/**
		 * @since 4.1.0
		 * @param bool $detected Whether an active page builder that renders content dynamically is detected.
		 * @NOTE not to be confused with `the_seo_framework_detect_non_html_page_builder`, which tests
		 *       the page builder status for each post individually.
		 */
		return $detected = (bool) \apply_filters(
			'the_seo_framework_shortcode_based_page_builder_active',
			$this->detect_plugin( [
				'constants' => [
					'ET_BUILDER_VERSION',
					'WPB_VC_VERSION',
				],
			] )
		);
	}

	/**
	 * Determines whether to add a line within robots based by plugin detection, or sitemap output option.
	 *
	 * @since 2.6.0
	 * @since 2.8.0 Added check_option parameter.
	 * @since 2.9.0 Now also checks for subdirectory installations.
	 * @since 2.9.2 Now also checks for permalinks.
	 * @since 2.9.3 Now also checks for sitemap_robots option.
	 * @since 3.1.0 Removed Jetpack's sitemap check -- it's no longer valid.
	 * @since 4.0.0 : 1. Now uses has_robots_txt()
	 *              : 2. Now uses the get_robots_txt_url() to determine validity.
	 * FIXME This method also checks for file existence (and location...), but is only used when the file definitely doesn't exist.
	 *
	 * @param bool $check_option Whether to check for sitemap option.
	 * @return bool True when no conflicting plugins are detected or when The SEO Framework's Sitemaps are output.
	 */
	public function can_do_sitemap_robots( $check_option = true ) {

		if ( $check_option ) {
			if ( ! $this->get_option( 'sitemaps_output' )
			|| ! $this->get_option( 'sitemaps_robots' ) )
				return false;
		}

		return ! $this->has_robots_txt() && \strlen( $this->get_robots_txt_url() );
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

		static $has_robots = null;

		if ( isset( $has_robots ) )
			return $has_robots;

		// Ensure get_home_path() is declared.
		if ( ! \function_exists( '\\get_home_path' ) )
			require_once ABSPATH . 'wp-admin/includes/file.php';

		$path = \get_home_path() . 'robots.txt';

		// phpcs:ignore, TSF.Performance.Functions.PHP -- we use path, not URL.
		return $has_robots = file_exists( $path );
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

		static $has_map = null;

		if ( isset( $has_map ) )
			return $has_map;

		// Ensure get_home_path() is declared.
		if ( ! \function_exists( '\\get_home_path' ) )
			require_once ABSPATH . 'wp-admin/includes/file.php';

		$path = \get_home_path() . 'sitemap.xml';

		// phpcs:ignore, TSF.Performance.Functions.PHP -- we use path, not URL.
		return $has_map = file_exists( $path );
	}

	/**
	 * Determines if WP is above or below a version
	 *
	 * @since 2.2.1
	 * @since 2.3.8 Added caching
	 * @since 2.8.0 No longer overwrites global $wp_version
	 * @since 3.1.0 1. No longer caches.
	 *              2. Removed redundant parameter checks.
	 *              3. Now supports x.yy.zz WordPress versions.
	 *
	 * @param string $version the three part version to compare to WordPress
	 * @param string $compare the comparing operator, default "$version >= Current WP Version"
	 * @return bool True if the WordPress version comparison passes.
	 */
	public function wp_version( $version = '4.3.0', $compare = '>=' ) {

		$wp_version = $GLOBALS['wp_version'];

		/**
		 * Add a .0 if WP outputs something like 4.3 instead of 4.3.0
		 * Does consider 4.xx, which will become 4.xx.0
		 */
		if ( 1 === substr_count( $wp_version, '.' ) )
			$wp_version = $wp_version . '.0';

		return (bool) version_compare( $wp_version, $version, $compare );
	}

	/**
	 * Checks for current theme support.
	 *
	 * Maintains detection cache, array and strings are mixed through foreach loops.
	 *
	 * @since 2.2.5
	 * @since 3.1.0 Removed caching
	 * @TODO deprecate me.
	 *
	 * @param string|array required $features The features to check for.
	 * @return bool theme support.
	 */
	public function detect_theme_support( $features ) {

		foreach ( (array) $features as $feature ) {
			if ( \current_theme_supports( $feature ) ) {
				return true;
			}
			continue;
		}

		return false;
	}

	/**
	 * Determines whether the main query supports custom SEO.
	 *
	 * @since 4.0.0
	 * @since 4.0.2 Now tests for an existing post/term ID when on singular/term pages.
	 * @since 4.0.3 Can now assert empty categories again by checking for taxonomy support.
	 *
	 * @return bool
	 */
	public function query_supports_seo() {

		static $cache;

		if ( isset( $cache ) ) return $cache;

		switch ( true ) :
			case $this->is_feed():
				$supported = false;
				break;

			case $this->is_singular():
				$supported = $this->is_post_type_supported() && $this->get_the_real_ID();
				break;

			case \is_post_type_archive():
				$supported = $this->is_post_type_supported();
				break;

			case $this->is_term_meta_capable():
				// When a term has no posts attached, it'll not return a post type, and it returns a 404 late in the loop.
				// This is because get_post_type() tries to assert the first post in the loop here.
				// Thus, we test for is_taxonomy_supported() instead.
				$supported = $this->is_taxonomy_supported() && $this->get_the_real_ID();
				break;

			// Including 404.
			default:
				$supported = true;
				break;
		endswitch;

		/**
		 * Override false negatives on exploit.
		 *
		 * This protects against (accidental) negative-SEO bombarding.
		 * Support broken queries, so we can noindex them.
		 */
		if ( ! $supported && $this->is_query_exploited() ) {
			$supported = true;
		}

		/**
		 * @since 4.0.0
		 * @param bool $supported Whether the query supports SEO.
		 */
		return $cache = (bool) \apply_filters( 'the_seo_framework_query_supports_seo', $supported );
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
	 * which is completely false. The canonical URL will be malformed. Even moreso, Google can ignore the canonical URL,
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
	 * @global \WP_Query $wp_query
	 *
	 * @return bool Whether the query is (accidentally) exploited.
	 *              Defaults to false when `advanced_query_protection` option is disabled.
	 *              False when there's a query-ID found.
	 *              False when no custom query is set (for the homepage).
	 *              Otherwise, it performs query tests.
	 */
	public function is_query_exploited() {

		static $exploited;

		if ( isset( $exploited ) ) return $exploited;

		if ( ! $this->get_option( 'advanced_query_protection' ) )
			return $exploited = false;

		// When the page ID is not 0, a real page will always be returned.
		if ( $this->get_the_real_ID() )
			return $exploited = false;

		global $wp_query;

		// When no special query data is registered, ignore this. Don't set cache.
		if ( ! isset( $wp_query->query ) )
			return false;

		/**
		 * @since 4.0.5
		 * @param array $exploitables The exploitable endpoints by type.
		 */
		$exploitables = \apply_filters(
			'the_seo_framework_exploitable_query_endpoints',
			[
				'numeric'       => [
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
				'numeric_array' => [
					'cat',
					'author',
				],
				'requires_s'    => [
					'sentence',
				],
			]
		);

		$query     = $wp_query->query;
		$exploited = false;

		foreach ( $exploitables as $type => $qvs ) :
			foreach ( $qvs as $qv ) :
				// Don't guess "empty", because falsey or empty-array is also empty.
				if ( ! isset( $query[ $qv ] ) ) continue;

				switch ( $type ) :
					case 'numeric':
						if ( '0' === $query[ $qv ] || ! is_numeric( $query[ $qv ] ) ) {
							$exploited = true;
							break 3;
						}
						break;

					case 'numeric_array':
						// We can't protect non-pretty permalinks.
						if ( ! $this->pretty_permalinks ) break;

						// If WordPress didn't canonical_redirect() the user yet, it's exploited.
						// WordPress mitigates this via a 404 query when a numeric value is found.
						if ( ! preg_match( '/[0-9]/', $query[ $qv ] ) ) {
							$exploited = true;
							break 3;
						}
						break;

					case 'requires_s':
						if ( ! isset( $query['s'] ) ) {
							$exploited = true;
							break 3;
						}
						break;

					default:
						break;
				endswitch;
			endforeach;
		endforeach;

		return $exploited;
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

		$post_type = $post_type ?: $this->get_post_type_real_ID() ?: $this->get_admin_post_type();

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
	 * Determines if the taxonomy supports The SEO Framework.
	 *
	 * Checks if at least one taxonomy objects post type supports The SEO Framework,
	 * and wether the taxonomy is public and rewritable.
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

		static $cache = [];

		if ( isset( $cache[ $post_type ] ) )
			return $cache[ $post_type ];

		$post_type = $post_type ?: $this->get_post_type_real_ID() ?: $this->get_admin_post_type();
		if ( ! $post_type ) return false;

		if ( \get_object_taxonomies( $post_type, 'names' ) )
			return $cache[ $post_type ] = true;

		return $cache[ $post_type ] = false;
	}

	/**
	 * Returns a list of all supported post types.
	 *
	 * @since 3.1.0
	 * @stativar array $cache
	 *
	 * @return array The supported post types.
	 */
	public function get_supported_post_types() {

		static $cache = [];
		// Can't be recursively empty. Right?
		if ( $cache ) return $cache;

		return $cache = array_values(
			array_filter( $this->get_public_post_types(), [ $this, 'is_post_type_supported' ] )
		);
	}

	/**
	 * Gets all post types that could possibly support SEO.
	 * Memoizes the return value.
	 *
	 * @since 4.1.0
	 *
	 * @return array All public post types.
	 */
	protected function get_public_post_types() {

		static $cache = null;

		return isset( $cache ) ? $cache : $cache = array_filter(
			array_unique(
				array_merge(
					$this->get_forced_supported_post_types(),
					//? array_values() because get_post_types() gives a sequential array.
					array_values( (array) \get_post_types( [
						'public' => true,
					] ) )
				)
			),
			'\\is_post_type_viewable'
		);
	}

	/**
	 * Returns a list of builtin public post types.
	 * Memoizes the return value.
	 *
	 * @since 3.1.0
	 *
	 * @return array Forced supported post types.
	 */
	protected function get_forced_supported_post_types() {

		static $cache = null;
		/**
		 * @since 3.1.0
		 * @param array $forced Forced supported post types
		 */
		return isset( $cache ) ? $cache : $cache = (array) \apply_filters(
			'the_seo_framework_forced_supported_post_types',
			array_values( \get_post_types( [
				'public'   => true,
				'_builtin' => true,
			] ) )
		);
	}

	/**
	 * Gets all taxonomies that could possibly support SEO.
	 * Memoizes the return value.
	 *
	 * @since 4.1.0
	 *
	 * @return array The taxonomies that are public.
	 */
	protected function get_public_taxonomies() {

		static $cache = null;

		return isset( $cache ) ? $cache : $cache = array_filter(
			array_unique(
				array_merge(
					$this->get_forced_supported_taxonomies(),
					//? array_values() because get_taxonomies() gives a sequential array.
					array_values( (array) \get_taxonomies( [
						'public'   => true,
						'_builtin' => false,
					] ) )
				)
			),
			'\\is_taxonomy_viewable'
		);
	}

	/**
	 * Returns a list of builtin public taxonomies.
	 * Memoizes the return value.
	 *
	 * @since 4.1.0
	 *
	 * @return array Forced supported taxonomies
	 */
	protected function get_forced_supported_taxonomies() {

		static $cache = null;
		/**
		 * @since 4.1.0
		 * @param array $forced Forced supported post types
		 */
		return isset( $cache ) ? $cache : $cache = (array) \apply_filters(
			'the_seo_framework_forced_supported_taxonomies',
			array_values( \get_taxonomies( [
				'public'   => true,
				'_builtin' => true,
			] ) )
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

		$post_type = $post_type ?: $this->get_post_type_real_ID() ?: $this->get_admin_post_type();

		/**
		 * @since 3.1.2
		 * @param bool   $disabled
		 * @param string $post_type
		 */
		return \apply_filters( 'the_seo_framework_post_type_disabled',
			isset(
				$this->get_option( 'disabled_post_types' )[ $post_type ]
			),
			$post_type
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

		if ( isset( $this->get_option( 'disabled_taxonomies' )[ $taxonomy ] ) ) {
			$disabled = true;
		} else {
			foreach ( $this->get_post_types_from_taxonomy( $taxonomy ) as $type ) {
				// Set here, because the taxonomy might not have post types at all.
				$disabled = true;
				if ( $this->is_post_type_supported( $type ) ) {
					$disabled = false;
					break;
				}
			}
		}

		/**
		 * @since 4.1.0
		 * @param bool   $disabled
		 * @param string $taxonomy
		 */
		return \apply_filters( 'the_seo_framework_taxonomy_disabled', $disabled, $taxonomy );
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
	 * @since 3.2.0 : 1. Now detects the WP 5.0 block editor.
	 *                2. Method is now public.
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
		global $wp_rewrite;

		if ( $wp_rewrite->using_permalinks() && ! $this->is_subdirectory_installation() ) {
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

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		$parsed_url = parse_url( \get_option( 'home' ) );

		return $cache = ! empty( $parsed_url['path'] ) && ltrim( $parsed_url['path'], ' \\/' );
	}

	/**
	 * Determines if the input text has transformative Yoast SEO syntax.
	 *
	 * @since 4.0.5
	 * @link <https://yoast.com/help/list-available-snippet-variables-yoast-seo/>
	 *
	 * @param string $text The text to evaluate.
	 * @return bool
	 */
	public function has_yoast_syntax( $text ) {

		if ( false === strpos( $text, '%%' ) ) return false;

		$tags_simple = [ 'date', 'title', 'parent_title', 'archive_title', 'sitename', 'sitedesc', 'excerpt', 'excerpt_only', 'tag', 'category', 'primary_category', 'category_description', 'tag_description', 'term_description', 'term_title', 'searchphrase', 'sep', 'pt_single', 'pt_plural', 'modified', 'id', 'name', 'user_description', 'page', 'pagetotal', 'pagenumber', 'caption', 'focuskw', 'term404', 'ct_product_cat', 'ct_product_tag', 'wc_shortdesc', 'wc_sku', 'wc_brand', 'wc_price' ];

		$_regex = sprintf( '%%%s%%', implode( '|', $tags_simple ) );

		if ( preg_match( "/$_regex/i", $text ) ) return true;

		$tags_wildcard_end = [ 'cs_', 'ct_desc_', 'ct_pa_' ];

		$_regex = sprintf( '%%(%s)[^\s]*?%%', implode( '|', $tags_wildcard_end ) );

		if ( preg_match( "/$_regex/", $text ) ) return true;

		return false;
	}
}
