<?php
/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class AutoDescription_Detect
 *
 * Detects other plugins and themes
 * Returns booleans
 *
 * @since 2.1.6
 */
class AutoDescription_Detect extends AutoDescription_Render {

	/**
	 * Constructor, load parent constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Detect active plugin by constant, class or function existence.
	 *
	 * @since 1.3.0
	 *
	 * @param array $plugins Array of array for constants, classes and / or functions to check for plugin existence.
	 *
	 * @return boolean True if plugin exists or false if plugin constant, class or function not detected.
	 */
	public function detect_plugin( $plugins ) {

		//* Check for classes
		if ( isset( $plugins['classes'] ) ) {
			foreach ( $plugins['classes'] as $name ) {
				if ( class_exists( $name ) )
					return true;
					break;
			}
		}

		//* Check for functions
		if ( isset( $plugins['functions'] ) ) {
			foreach ( $plugins['functions'] as $name ) {
				if ( function_exists( $name ) )
					return true;
					break;
			}
		}

		//* Check for constants
		if ( isset( $plugins['constants'] ) ) {
			foreach ( $plugins['constants'] as $name ) {
				if ( defined( $name ) )
					return true;
					break;
			}
		}

		//* No class, function or constant found to exist
		return false;
	}

	/**
	 * Detect if you can use the given constants, functions and classes.
	 * All must be available to return true.
	 *
	 * @param array $plugins Array of array for constants, classes and / or functions to check for plugin existence.
	 * @param bool $use_cache Bypasses cache if false
	 *
	 * @staticvar array $cache
	 * @uses $this->detect_plugin_multi()
	 *
	 * @since 2.5.2
	 */
	public function can_i_use( array $plugins = array(), $use_cache = true ) {

		if ( ! $use_cache )
			return $this->detect_plugin_multi( $plugins );

		static $cache = array();

		$mapped = array();

		//* Prepare multidimensional array for cache.
		foreach ( $plugins as $key => $func ) {
			if ( ! is_array( $func ) )
				return false;

			//* Sort alphanumeric by value, put values back after sorting.
			$func = array_flip( $func );
			ksort( $func );
			$func = array_flip( $func );

			//* Glue with underscore and space for debugging purposes.
			$mapped[$key] = $key . '_' . implode( ' ', $func );
		}

		ksort( $mapped );

		//* Glue with dash instead of underscore for debugging purposes.
		$plugins_cache = implode( '-', $mapped );

		if ( isset( $cache[$plugins_cache] ) )
			return $cache[$plugins_cache];

		return $cache[$plugins_cache] = $this->detect_plugin_multi( $plugins );
	}

	/**
	 * Detect active plugin by constant, class or function existence.
	 * All parameters must match and return true.
	 *
	 * @since 2.5.2
	 *
	 * @param array $plugins Array of array for constants, classes and / or functions to check for plugin existence.
	 *
	 * @return boolean True if ALL functions classes and constants exists or false if plugin constant, class or function not detected.
	 */
	public function detect_plugin_multi( array $plugins ) {

		//* Check for classes
		if ( isset( $plugins['classes'] ) ) {
			foreach ( $plugins['classes'] as $name ) {
				if ( ! class_exists( $name ) ) {
					return false;
					break;
				}
			}
		}

		//* Check for functions
		if ( isset( $plugins['functions'] ) ) {
			foreach ( $plugins['functions'] as $name ) {
				if ( ! function_exists( $name ) ) {
					return false;
					break;
				}
			}
		}

		//* Check for constants
		if ( isset( $plugins['constants'] ) ) {
			foreach ( $plugins['constants'] as $name ) {
				if ( ! defined( $name ) ) {
					return false;
					break;
				}
			}
		}

		//* All classes, functions and constant have been found to exist
		return true;
	}

	/**
	 * Checks if the (parent) theme name is loaded.
	 *
	 * @NOTE will return true if ANY of the array values matches.
	 *
	 * @param string|array $themes the current theme name
	 * @param bool $use_cache If set to false don't use cache.
	 *
	 * @since 2.1.0
	 *
	 * @staticvar array $themes_cache
	 * @since 2.2.4
	 *
	 * @return bool is theme active.
	 */
	public function is_theme( $themes = null, $use_cache = true ) {

		if ( ! isset( $themes ) )
			return false;

		if ( ! $use_cache ) {
			//* Don't use cache.

			$wp_get_theme = wp_get_theme();

			$theme_parent = strtolower( $wp_get_theme->get('Template') );
			$theme_name = strtolower( $wp_get_theme->get('Name') );

			if ( is_string( $themes ) ) {
				$themes = strtolower( $themes );
				if ( $themes === $theme_parent || $themes === $theme_name )
					return true;
			} else if ( is_array( $themes ) ) {
				foreach ( $themes as $theme ) {
					$theme = strtolower( $theme );
					if ( $theme === $theme_parent || $theme === $theme_name ) {
						return true;
						break;
					}
				}
			}

			return false;
		}

		static $themes_cache = array();

		//* Check theme check cache
		if ( is_string( $themes ) && isset( $themes_cache[$themes] ) ) {
			$themes = strtolower( $themes );
			//* Theme check has been cached
			return $themes_cache[$themes];
		}

		if ( is_array( $themes ) ) {
			foreach ( $themes as $theme ) {
				$theme = strtolower( $theme );
				if ( isset( $themes_cache[$theme] ) && in_array( $themes_cache[$theme], $themes ) && $themes_cache[$theme] ) {
					// Feature is found and true
					return $themes_cache[$theme];
					break;
				}
			}
		}

		$wp_get_theme = wp_get_theme();

		//* Fetch both themes if child theme is present.
		$theme_parent = strtolower( $wp_get_theme->get('Template') );
		$theme_name = strtolower( $wp_get_theme->get('Name') );

		if ( is_string( $themes ) ) {
			$themes = strtolower( $themes );
			if ( $themes === $theme_parent || $themes === $theme_name )
				$themes_cache[$themes] = true;
		} else if ( is_array( $themes ) ) {
			foreach ( $themes as $theme ) {
				$theme = strtolower( $theme );
				if ( $theme === $theme_parent || $theme === $theme_name ) {
					return $themes_cache[$theme] = true;
					break;
				} else {
					$themes_cache[$theme] = false;
				}
			}
			return $themes_cache[$theme];
		}

		//* The theme isn't active
		if ( is_string( $themes ) && ! isset( $themes_cache[$themes] ) )
			$themes_cache[$themes] = false;

		return $themes_cache[$themes];
	}

	/**
	 * SEO plugin detection
	 *
	 * @since 1.3.0
	 *
	 * @staticvar bool $detected
	 * @since 2.2.5
	 *
	 * @return bool SEO plugin detected.
	 *
	 * @thanks StudioPress (http://www.studiopress.com/) for some code.
	 */
	public function detect_seo_plugins() {

		static $detected = null;

		if ( isset( $detected ) )
			return $detected;

		/**
		 * Use this filter to adjust plugin tests.
		 *
		 * New filter.
		 * @since 2.3.0
		 *
		 * Removed previous filter.
		 * @since 2.3.5
		 */
		$plugins_check = (array) apply_filters(
			'the_seo_framework_detect_seo_plugins',
			//* Add to this array to add new plugin checks.
			array(

				// Classes to detect.
				'classes' => array(
					'All_in_One_SEO_Pack',
					'All_in_One_SEO_Pack_p',
					'HeadSpace_Plugin',
					'Platinum_SEO_Pack',
					'wpSEO',
					'SEO_Ultimate',
				),

				// Functions to detect.
				'functions' => array(),

				// Constants to detect.
				'constants' => array( 'WPSEO_VERSION', ),
			)
		);

		return $detected = $this->detect_plugin( $plugins_check );
	}

	/**
	 * Detects if plugins outputting og:type exists
	 *
	 * @note isn't used in $this->og_image() Because og:image may be output multiple times.
	 *
	 * @uses $this->detect_plugin()
	 *
	 * @since 1.3.0
	 * @return bool OG plugin detected.
	 *
	 * @staticvar bool $has_plugin
	 * @since 2.2.5
	 *
	 * @return bool $has_plugin one of the plugins has been found.
	 */
	public function has_og_plugin() {

		static $has_plugin = null;

		if ( isset( $has_plugin ) )
			return $has_plugin;

		$plugins = array(
			'classes' => array(
				'WPSEO_OpenGraph',
				'All_in_One_SEO_Pack_Opengraph'
			),
			'functions' => array(
				'amt_plugin_actions'
			)
		);

		return $has_plugin = (bool) $this->detect_plugin( $plugins );
	}

	/**
	 * Detects if plugins outputting ld+json exists
	 *
	 * @uses $this->detect_plugin()
	 *
	 * @since 1.3.0
	 *
	 * @return bool LD+Json plugin detected
	 *
	 * @staticvar bool $has_plugin
	 * @since 2.2.5
	 *
	 * @return bool $has_plugin one of the plugins has been found.
	 */
	public function has_json_ld_plugin() {

		static $has_plugin = null;

		if ( isset( $has_plugin ) )
			return $has_plugin;

		$plugins = array( 'classes' => array( 'WPSEO_JSON_LD' ) );

		return $has_plugin = (bool) $this->detect_plugin( $plugins );
	}

	/**
	 * Detecs sitemap plugins
	 *
	 * @uses $this->detect_plugin()
	 *
	 * @since 2.1.0
	 *
	 * @return bool Sitemap plugin detected.
	 *
	 * @staticvar bool $has_plugin
	 * @since 2.2.5
	 *
	 * @return bool $has_plugin one of the plugins has been found.
	 */
	public function has_sitemap_plugin() {

		static $has_plugin = null;

		if ( isset( $has_plugin ) )
			return $has_plugin;

		//* Only sitemap plugins which influence sitemap.xml
		$plugins = array(
				'classes' => array(
					'xml_sitemaps',
					'All_in_One_SEO_Pack_Sitemap',
					'SimpleWpSitemap',
					'Incsub_SimpleSitemaps',
					'BWP_Sitemaps',
					'KocujSitemapPlugin',
					'LTI_Sitemap',
					'ps_auto_sitemap',
					'scalible_sitemaps',
					'Sewn_Xml_Sitemap',
					'csitemap',
				),
				'functions' => array(
					'sm_Setup',
					'wpss_init',
					'gglstmp_sitemapcreate',
					'asxs_sitemap2',
					'build_baidu_sitemap',
					'ect_sitemap_nav',
					'apgmxs_generate_sitemap',
					'sm_Setup',
					'ADSetupSitemapPlugin',
					'ksm_generate_sitemap',
					'studio_xml_sitemap',
					'RegisterPluginLinks_xmlsite',
				),
			);

		return $has_plugin = (bool) $this->detect_plugin( $plugins );
	}

	/**
	 * Detects presence of robots.txt in root folder.
	 *
	 * @staticvar $has_robots
	 *
	 * @since 2.5.2
	 */
	public function has_robots_txt() {

		static $has_robots = null;

		if ( isset( $has_robots ) )
			return $has_robots;

		$path = get_home_path() . 'robots.txt';

		$found = (bool) file_exists( $path );

		return $has_robots = $found;
	}

	/**
	 * Detects presence of sitemap.xml in root folder.
	 *
	 * @staticvar $has_map
	 *
	 * @since 2.5.2
	 */
	public function has_sitemap_xml() {

		static $has_map = null;

		if ( isset( $has_map ) )
			return $has_map;

		$path = get_home_path() . 'sitemap.xml';

		$found = (bool) file_exists( $path );

		return $has_map = $found;
	}

	/**
	 * Determines if WP is above or below a version
	 *
	 * @since 2.2.1
	 *
	 * @param string $version the three part version to compare to WordPress
	 * @param string $compare the comparing operator, default "$version >= Current WP Version"
	 *
	 * @staticvar array $compare_cache
	 * @since 2.3.8
	 *
	 * @return bool wp version is "compare" to
	 */
	public function wp_version( $version = '4.3.0', $compare = '>=' ) {

		static $compare_cache = array();

		if ( isset( $compare_cache[$version][$compare] ) )
			return $compare_cache[$version][$compare];

		global $wp_version;

		// Add a .0 if WP outputs something like 4.3 instead of 4.3.0
		if ( 3 === strlen( $wp_version ) )
			$wp_version = $wp_version . '.0';

		//* Evade 'true-ish' values.
		if ( empty( $compare ) )
			$compare = '>=';

		if ( version_compare( $wp_version, $version, $compare ) )
			return $compare_cache[$version][$compare] = true;

		return $compare_cache[$version][$compare] = false;
	}

	/**
	 * Checks for current theme support.
	 *
	 * Also, if it's cached as true from an array, it will be cached as string as well.
	 * This is desired.
	 *
	 * @NOTE will return true if ANY of the array values matches.
	 *
	 * @since 2.2.5
	 *
	 * @param string|array required $feature The features to check for.
	 * @param bool $use_cache If set to false don't use cache.
	 *
	 * @staticvar array $cache
	 *
	 * @return bool theme support.
	 */
	public function detect_theme_support( $features, $use_cache = true ) {

		if ( ! $use_cache ) {
			//* Don't use cache.

			if ( is_string( $features ) && ( $this->current_theme_supports( $features ) ) )
				return true;

			if ( is_array( $features ) ) {
				foreach ( $features as $feature ) {
					if ( $this->current_theme_supports( $feature ) ) {
						return true;
						break;
					}
				}
			}

			return false;
		}

		//* Setup cache.
		static $cache = array();

		//* Check theme support cache
		if ( is_string( $features ) && isset( $cache[$features] ) )
			//* Feature support check has been cached
			return $cache[$features];

		//* Check theme support array cache
		if ( is_array( $features ) ) {
			foreach ( $features as $feature ) {
				if ( isset( $cache[$feature] ) && in_array( $cache[$feature], $features ) && $cache[$feature] ) {
					// Feature is found and true
					return $cache[$feature];
					break;
				}
			}
		}

		//* Setup cache values
		if ( is_string( $features ) ) {
			if ( $this->current_theme_supports( $features ) ) {
				return $cache[$features] = true;
			} else {
				return $cache[$features] = false;
			}
		} else if ( is_array( $features ) ) {
			foreach ( $features as $feature ) {
				if ( $this->current_theme_supports( $feature ) ) {
					return $cache[$feature] = true;
					break;
				} else {
					$cache[$feature] = false;
				}
			}
			return $cache[$feature];
		}

		// No true value found so far, let's return false.
		if ( ! isset( $cache[$features] ) )
			$cache[$features] = false;

		return $cache[$features];
	}

	/**
	 * Checks a theme's support for a given feature
	 *
	 * @since 2.2.5
	 *
	 * @global array $_wp_theme_features
	 *
	 * @param string $feature the feature being checked
	 * @return bool
	 *
	 * Taken from WP Core, but it now returns true on title-tag support.
	 *
	 * @todo rework, it's a mess.
	 */
	public function current_theme_supports( $feature ) {
		global $_wp_theme_features;

		//* SEO Framework Edits. {
		if ( 'custom-header-uploads' == $feature )
			return $this->detect_theme_support( 'custom-header', 'uploads' );
		//* } End SEO Framework Edits.

		if ( ! isset( $_wp_theme_features[$feature] ) )
			return false;

		if ( 'title-tag' == $feature ) {

			//* SEO Framework Edits. {

				//* The SEO Framework unique 'feature'.
				if ( true === $_wp_theme_features[$feature] )
					return true;

				//* We might as well return false now preventing the debug_backtrace();
				return false;

			//* } End SEO Framework Edits.

			// Don't confirm support unless called internally.
			$trace = debug_backtrace();

			if ( ! in_array( $trace[1]['function'], array( '_wp_render_title_tag', 'wp_title' ) ) ) {
				return false;
			}
		}

		// If no args passed then no extra checks need be performed
		if ( func_num_args() <= 1 )
			return true;

		$args = array_slice( func_get_args(), 1 );

		switch ( $feature ) {
			case 'post-thumbnails':
				// post-thumbnails can be registered for only certain content/post types by passing
				// an array of types to add_theme_support(). If no array was passed, then
				// any type is accepted
				if ( true === $_wp_theme_features[$feature] )  // Registered for all types
					return true;
				$content_type = $args[0];
				return in_array( $content_type, $_wp_theme_features[$feature][0] );

			case 'html5':
			case 'post-formats':
				// specific post formats can be registered by passing an array of types to
				// add_theme_support()

				// Specific areas of HTML5 support *must* be passed via an array to add_theme_support()

				$type = $args[0];
				return in_array( $type, $_wp_theme_features[$feature][0] );

			case 'custom-header':
			case 'custom-background' :
				// specific custom header and background capabilities can be registered by passing
				// an array to add_theme_support()
				$header_support = $args[0];
				return ( isset( $_wp_theme_features[$feature][0][$header_support] ) && $_wp_theme_features[$feature][0][$header_support] );
		}

		/**
		 * Filter whether the current theme supports a specific feature.
		 *
		 * The dynamic portion of the hook name, `$feature`, refers to the specific theme
		 * feature. Possible values include 'post-formats', 'post-thumbnails', 'custom-background',
		 * 'custom-header', 'menus', 'automatic-feed-links', 'title-tag' and 'html5'.
		 *
		 * @since WP 3.4.0
		 *
		 * @param bool   true     Whether the current theme supports the given feature. Default true.
		 * @param array  $args    Array of arguments for the feature.
		 * @param string $feature The theme feature.
		 */
		return apply_filters( "current_theme_supports-{$feature}", true, $args, $_wp_theme_features[$feature] );
	}

	/**
	 * Add doing it wrong html code in the footer.
	 *
	 * @since 2.5.2.1
	 * @staticvar bool $no_spam
	 *
	 * @staticvar string $sep_output
	 * @staticvar string $display_output
	 * @staticvar string $seplocation_output
	 *
	 * @param null|string $title The given title
	 * @param null|string $sep The separator
	 * @param null|string $seplocation Wether the blogname is left or right.
	 * @param bool $output Wether to store cache values or echo the output in the footer.
	 *
	 * @return void
	 */
	public function tell_title_doing_it_wrong( $title = null, $sep = null, $seplocation = null, $output = true ) {

		if ( true === $output ) {
			//* Prevent error log spam.
			static $no_spam = null;

			if ( isset( $no_spam ) )
				return;

			$no_spam = true;
		}

		static $title_output = null;
		static $sep_output = null;
		static $seplocation_output = null;

		if ( ! isset( $title_output ) || ! isset( $sep_output ) || ! isset( $seplocation_output ) ) {
			//* Initiate caches.

			if ( '' === $title )
				$title = 'empty';

			if ( '' === $sep )
				$sep = 'empty';

			if ( '' === $seplocation )
				$seplocation = 'empty';

			$title_output = ! isset( $title ) ? 'notset' : esc_attr( $title );
			$sep_output = ! isset( $sep ) ? 'notset' : esc_attr( $sep );
			$seplocation_output = ! isset( $seplocation ) ? 'notset' : esc_attr( $seplocation );
		}

		if ( true === $output )
			echo '<!-- Title diw: "' . $title_output . '" : "' . $sep_output . '" : "' . $seplocation_output . '" -->' . "\r\n";

		return;
	}

	/**
	 * Detect WPMUdev Domain Mapping plugin.
	 *
	 * @since 2.3.0
	 * @staticvar bool $active
	 *
	 * @return bool false if Domain Mapping isn't active
	 */
	public function is_domainmapping_active() {

		static $active = null;

		if ( isset( $active ) )
			return $active;

		/**
		 * Now uses $this->detect_plugin()
		 *
		 * @since 2.3.1
		 */
		if ( $this->detect_plugin( array( 'classes' => array( 'domain_map' ) ) ) ) {
			return $active = true;
		} else {
			return $active = false;
		}
	}

	/**
	 * Detect Donncha Domain Mapping plugin.
	 *
	 * @since 2.4.0
	 * @staticvar bool $active
	 *
	 * @return bool false if Domain Mapping isn't active
	 */
	public function is_donncha_domainmapping_active() {

		static $active = null;

		if ( isset( $active ) )
			return $active;

		/**
		 * Now uses $this->detect_plugin()
		 *
		 * @since 2.3.1
		 */
		if ( $this->detect_plugin( array( 'functions' => array( 'redirect_to_mapped_domain' ) ) ) ) {
			return $active = true;
		} else {
			return $active = false;
		}
	}

	/**
	 * Detect if the current screen type is a page or taxonomy.
	 *
	 * @param string $type the Screen type
	 * @staticvar array $is_page
	 *
	 * @since 2.3.1
	 */
	public function is_post_type_page( $type ) {

		static $is_page = array();

		if ( isset( $is_page[$type] ) )
			return $is_page[$type];

		$post_page = (array) get_post_types( array( 'public' => true ) );

		foreach ( $post_page as $screen ) {
			if ( $type == $screen ) {
				return $is_page[$type] = true;
				break;
			}
		}

		return $is_page[$type] = false;
	}

	/**
	 * Get the real page ID, also depending on CPT.
	 *
	 * @param bool $use_cache Wether to use the cache or not.
	 *
	 * @staticvar int $id the ID.
	 *
	 * @since 2.5.0
	 */
	public function get_the_real_ID( $use_cache = true ) {

		$is_admin = is_admin();

		//* Never use cache in admin. Only causes bugs.
		$use_cache = $is_admin ? false : $use_cache;

		if ( $use_cache ) {
			static $id = null;

			if ( isset( $id ) )
				return $id;
		}

		if ( ! $is_admin )
			$id = $this->check_the_real_ID();

		if ( ! isset( $id ) || empty( $id ) ) {
			$id = get_queried_object_id();

			if ( empty( $id ) )
				$id = get_the_ID();
		}

		return $id;
	}

	/**
	 * Get the real ID from plugins.
	 *
	 * Only works in front-end as there's no need to check for inconsistent
	 * functions for the current ID in the admin.
	 *
	 * @since 2.5.0
	 *
	 * Applies filters the_seo_framework_real_id : The Real ID for plugins on front-end.
	 *
	 * @staticvar int $cached_id The cached ID.
	 *
	 * @return int|empty the ID.
	 */
	public function check_the_real_ID() {

		static $cached_id = null;

		if ( isset( $cached_id ) )
			return $cached_id;

		$id = '';

		if ( $this->is_wc_shop() ) {
			//* WooCommerce Shop
			$id = get_option( 'woocommerce_shop_page_id' );
		} else if ( function_exists( 'is_anspress' ) && is_anspress() ) {
			//* Get AnsPress Question ID.
			if ( function_exists( 'get_question_id' ) )
				$id = get_question_id();
		}

		$cached_id = (int) apply_filters( 'the_seo_framework_real_id', $id );

		return $cached_id;
	}

	/**
	 * Detect the blog page.
	 *
	 * @param int $id the Page ID.
	 *
	 * @since 2.3.4
	 *
	 * @staticvar bool $is_blog_page
	 * @staticvar bool $pof
	 *
	 * @return bool true if is blog page. Always false if blog page is homepage.
	 */
	public function is_blog_page( $id = '' ) {

		if ( '' === $id )
			$o_id = $this->get_the_real_ID();

		static $is_blog_page = array();

		if ( isset( $is_blog_page[$id] ) )
			return $is_blog_page[$id];

		$pfp = get_option( 'page_for_posts' );

		if ( $pfp != 0 ) {

			static $pof = null;

			if ( ! isset( $pof ) )
				$pof = 'page' === get_option( 'show_on_front' ) ? true : false;

			if ( $pof && ! is_front_page() && ! is_archive() ) {
				if ( isset( $o_id ) ) {
					if ( $o_id == $pfp )
						return $is_blog_page[$id] = true;
				} else {
					if ( $id == $pfp )
						return $is_blog_page[$id] = true;

					$o_id = $this->get_the_real_ID();

					if ( $o_id == $pfp )
						return $is_blog_page[$id] = true;
				}
			}
		}

		return $is_blog_page[$id] = false;
	}

	/**
	 * Detect the static front page.
	 *
	 * @param int $id the Page ID.
	 *
	 * @since 2.3.9
	 *
	 * @staticvar array $is_frontpage
	 * @since 2.3.8
	 *
	 * @return bool true if is blog page. Always false if blog page is homepage.
	 * False early when false as ID is entered.
	 */
	public function is_static_frontpage( $id = '' ) {

		//* Oops, passed a false ID. No need to process.
		if ( false === $id )
			return false;

		if ( '' === $id )
			$o_id = $this->get_the_real_ID();

		static $is_frontpage = array();

		if ( isset( $is_frontpage[$id] ) )
			return $is_frontpage[$id];

		$sof = (string) get_option( 'show_on_front' );

		if ( $sof === 'page' ) {
			$pof = (int) get_option( 'page_on_front' );

			if ( isset( $o_id ) ) {
				if ( $o_id === $pof )
					return $is_frontpage[$id] = true;
			} else {

				if ( $id == $pof )
					return $is_frontpage[$id] = true;

				$o_id = $this->get_the_real_ID();

				if ( $o_id == $pof )
					return $is_frontpage[$id] = true;
			}
		}

		return $is_frontpage[$id] = false;
	}

	/**
	 * Detect WordPress language.
	 * Considers en_UK, en_US, etc.
	 *
	 * @param string $str Required, the locale.
	 * @param bool $use_cache Set to false to bypass the cache.
	 *
	 * @staticvar array $locale
	 * @staticvar string $get_locale
	 *
	 * @since 2.3.8
	 */
	public function is_locale( $str, $use_cache = true ) {

		if ( true !== $use_cache )
			return (bool) strpos( get_locale(), $str );

		static $locale = array();

		if ( isset( $locale[$str] ) )
			return $locale[$str];

		static $get_locale = null;

		if ( ! isset( $get_locale ) )
			$get_locale = get_locale();

		return $locale[$str] = strpos( $get_locale, $str ) !== false ? true : false;
	}

	/**
	 * Determines if the post type is compatible with The SEO Framework inpost metabox.
	 *
	 * @since 2.3.5
	 *
	 * @return bool True if post type is supported.
	 */
	public function post_type_supports_inpost( $post_type ) {

		if ( isset( $post_type ) ) {
			$supports = (array) apply_filters( 'the_seo_framework_custom_post_type_support',
				array(
					'title',
					'editor',
				//	'custom-fields',
				)
			);

			foreach ( $supports as $support ) {
				if ( ! post_type_supports( $post_type, $support ) ) {
					return false;
					break;
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Check if post type supports The SEO Framework.
	 * Doesn't work on admin_init.
	 *
	 * @since 2.3.9
	 *
	 * @param string $post_type The current post type.
	 *
	 * @staticvar string $post_type
	 * @staticvar bool $supported
	 * @staticvar array $post_page
	 *
	 * @return bool true of post type is supported.
	 */
	public function post_type_supports_custom_seo( $post_type = '' ) {

		if ( '' === $post_type ) {

			static $post_type = null;

			//* Detect post type if empty or not set.
			if ( ! isset( $post_type ) || empty( $post_type ) ) {
				global $current_screen;

				static $post_page = null;

				if ( ! isset( $post_page ) )
					$post_page = (array) get_post_types( array( 'public' => true ) );

				//* Smart var. This elemenates the need for a foreach loop, reducing resource usage.
				$post_type = isset( $post_page[ $current_screen->post_type ] ) ? $current_screen->post_type : '';
			}

			//* No post type has been found.
			if ( empty( $post_type ) )
				return false;
		}

		static $supported = array();

		if ( isset( $supported[$post_type] ) )
			return $supported[$post_type];

		/**
		 * We now support all posts that allow a title, content editor and excerpt.
		 * To ease the flow, we have our basic list to check first.
		 *
		 * @since 2.3.5
		 */
		if ( post_type_supports( $post_type, 'autodescription-meta' ) )
			return $supported[$post_type] = true;

		if ( $this->post_type_supports_inpost( $post_type ) )
			return $supported[$post_type] = true;

		return $supported[$post_type] = false;
	}

	/**
	 * Is Ulimate Member user page.
	 * Check for function accessibility: um_user, um_is_core_page, um_get_requested_user
	 *
	 * @staticvar bool $cache
	 * @uses $this->can_i_use()
	 *
	 * @since 2.5.2
	 */
	public function is_ultimate_member_user_page() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		$caniuse = (bool) $this->can_i_use( array( 'functions' => array( 'um_user', 'um_is_core_page', 'um_get_requested_user' ) ), false );

		return $cache = $caniuse;
	}

	/**
	 * Check for shop page.
	 *
	 * @staticvar bool $cache
	 *
	 * @since 2.5.2
	 */
	public function is_wc_shop() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		//* Can't check in admin.
		if ( ! is_admin() && function_exists( 'is_shop' ) && is_shop() )
			return $cache = true;

		return $cache = false;
	}

	/**
	 * Replaces default WordPress is_singular.
	 *
	 * @uses $this->is_blog_page()
	 * @uses $this->is_wc_shop()
	 * @uses is_single()
	 * @uses is_page()
	 * @uses is_attachment()
	 *
	 * @param int $id the Page ID.
	 *
	 * @staticvar bool $cache
	 *
	 * @since 2.5.2
	 *
	 * @return bool Post Type is singular
	 */
	public function is_singular( $id = 0 ) {

		if ( 0 === $id )
			$id = $this->get_the_real_ID();

		//* WP_Query functions require loop, do alternative check.
		if ( is_admin() )
			return $this->is_singular_admin( $id );

		$cache = array();

		if ( isset( $cache[$id] ) )
			return $cache[$id];

		if ( is_single( $id ) || is_page( $id ) || is_attachment( $id ) || $this->is_blog_page( $id ) || $this->is_wc_shop() )
			return $cache[$id] = true;

		return $cache[$id] = false;
	}

	/**
	 * Extends default WordPress is_singular and made available in admin.
	 *
	 * @staticvar bool $cache
	 *
	 * @since 2.5.2
	 *
	 * @global object $current_screen
	 *
	 * @return bool Post Type is singular
	 */
	public function is_singular_admin() {

		$cache = null;

		if ( isset( $cache ) )
			return $cache;

		global $current_screen;

		if ( isset( $current_screen->base ) && ( 'edit' === $current_screen->base || 'post' === $current_screen->base ) )
			return $cache = true;

		return $cache = false;
	}

	/**
	 * Calculates wether the theme is outputting the title correctly.
	 *
	 * @since 2.5.2
	 *
	 * @staticvar bool $dir
	 *
	 * @return bool True theme is doing it right.
	 */
	public function theme_title_doing_it_right() {

		static $dir = null;

		if ( isset( $dir ) )
			return $dir;

		$transient = get_transient( $this->theme_doing_it_right_transient );

		if ( '0' === $transient )
			return $dir = false;

		/**
		 * Transient has not been set yet (false)
		 * or the theme is doing it right ('1').
		 */
		return $dir = true;
	}

}
