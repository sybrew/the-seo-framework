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
				if ( class_exists( $name ) ) {
					return true;
					break;
				}
			}
		}

		//* Check for functions
		if ( isset( $plugins['functions'] ) ) {
			foreach ( $plugins['functions'] as $name ) {
				if ( function_exists( $name ) ) {
					return true;
					break;
				}
			}
		}

		//* Check for constants
		if ( isset( $plugins['constants'] ) ) {
			foreach ( $plugins['constants'] as $name ) {
				if ( defined( $name ) ) {
					return true;
					break;
				}
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
		 */
		$plugins_check = (array) apply_filters(
			'the_seo_framework_detect_seo_plugins',
			//* Add to this array to add new plugin checks.
			array(
				// Classes to detect.
				'classes' => array(
					'gregsHighPerformanceSEO'
				),

				// Functions to detect.
				'functions' => array(),

				// Constants to detect.
				'constants' => array(
					'AIOSEOPPRO',		// All in one SEO + Pro
					'AMT_PLUGIN_FILE',	// Add Meta Tags
					'WPSEO_FILE',		// Yoast SEO
					'SQ_VERSION',		// SEO by Squirrly
					'SU_PLUGIN_NAME',	// SEO Ultimate
				),
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
			),
			'constants' => array(
			),
		);

		return $has_plugin = $this->detect_plugin( $plugins );
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

		return $has_plugin = $this->detect_plugin( $plugins );
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
	 * @todo Try to use constants if possible.
	 * @priority low 2.8.x
	 * @todo List plugin names
	 * @priority low
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
					'All_in_One_SEO_Pack_Sitemap',	// All in One SEO Sitemap
					'SimpleWpSitemap',
					'BWP_Sitemaps',
					'KocujSitemapPlugin',
					'LTI_Sitemap',
					'ps_auto_sitemap',
					'scalible_sitemaps',
					'Sewn_Xml_Sitemap',
					'csitemap',
				),
				'functions' => array(
					'jetpack_sitemap_initialize', // Jetpack
					'sm_Setup',
					'wpss_init',
					'gglstmp_sitemapcreate',
					'asxs_sitemap2',
					'build_baidu_sitemap',
					'ect_sitemap_nav',
					'apgmxs_generate_sitemap',
					'ADSetupSitemapPlugin',
					'ksm_generate_sitemap',
					'studio_xml_sitemap',
					'RegisterPluginLinks_xmlsite',
				),
				'constants' => array(
					'SIMPLE_SITEMAPS_USE_CACHE'	// WPMUdev Simple Sitemaps
				),
			);

		return $has_plugin = $this->detect_plugin( $plugins );
	}

	/**
	 * Whether able to add a line within robots based by plugin detection, or sitemap output option.
	 *
	 * @since 2.6.0
	 *
	 * @return bool True when no conflicting plugins are detected or when The SEO Framework's Sitemaps are output.
	 */
	public function can_do_sitemap_robots() {

		$plugins = array(
			'functions' => array(
				'jetpack_sitemap_initialize' // Jetpack
			),
		);

		if ( $this->detect_plugin( $plugins ) )
			return false;

		if ( $this->is_option_checked( 'sitemaps_output' ) )
			return true;

		return false;
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

		return $has_robots = file_exists( $path );
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

		return $has_map = file_exists( $path );
	}

	/**
	 * Determines if WP is above or below a version
	 *
	 * @since 2.2.1
	 *
	 * @param string $version the three part version to compare to WordPress
	 * @param string $compare the comparing operator, default "$version >= Current WP Version"
	 *
	 * @staticvar array $cache
	 * @since 2.3.8
	 *
	 * @return bool wp version is "compare" to
	 */
	public function wp_version( $version = '4.3.0', $compare = '>=' ) {

		static $cache = array();

		if ( empty( $compare ) )
			$compare = '>=';

		if ( isset( $cache[$version][$compare] ) )
			return $cache[$version][$compare];

		global $wp_version;

		// Add a .0 if WP outputs something like 4.3 instead of 4.3.0
		if ( 3 === strlen( $wp_version ) )
			$wp_version = $wp_version . '.0';

		if ( version_compare( $wp_version, $version, $compare ) )
			return $cache[$version][$compare] = true;

		return $cache[$version][$compare] = false;
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

			if ( is_string( $features ) && ( current_theme_supports( $features ) ) )
				return true;

			if ( is_array( $features ) ) {
				foreach ( $features as $feature ) {
					if ( current_theme_supports( $feature ) ) {
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
			if ( current_theme_supports( $features ) ) {
				return $cache[$features] = true;
			} else {
				return $cache[$features] = false;
			}
		} else if ( is_array( $features ) ) {
			foreach ( $features as $feature ) {
				if ( current_theme_supports( $feature ) ) {
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
	 * Checks a theme's support for title-tag.
	 *
	 * @since 2.6.0
	 * @staticvar bool $supports
	 *
	 * @global array $_wp_theme_features
	 *
	 * @return bool
	 */
	public function current_theme_supports_title_tag() {

		static $supports = null;

		if ( isset( $supports ) )
			return $supports;

		global $_wp_theme_features;

		if ( isset( $_wp_theme_features['title-tag'] ) && true === $_wp_theme_features['title-tag'] )
			return $supports = true;

		return $supports = false;
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
	 * @param null|string $seplocation Whether the blogname is left or right.
	 * @param bool $output Whether to store cache values or echo the output in the footer.
	 *
	 * @return void
	 */
	public function tell_title_doing_it_wrong( $title = null, $sep = null, $seplocation = null, $output = true ) {

		if ( $output ) {
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
			//* Initiate caches, set up variables.

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

		//* Echo the HTML comment.
		if ( $output )
			echo '<!-- Title diw: "' . $title_output . '" : "' . $sep_output . '" : "' . $seplocation_output . '" -->' . "\r\n";

		return;
	}

	/**
	 * Detect WPMUdev Domain Mapping plugin.
	 *
	 * @since 2.3.0
	 * @staticvar bool $active
	 *
	 * @return bool
	 */
	public function is_domainmapping_active() {

		static $active = null;

		if ( isset( $active ) )
			return $active;

		return $active = $this->detect_plugin( array( 'classes' => array( 'domain_map' ) ) );
	}

	/**
	 * Detect Donncha Domain Mapping plugin.
	 *
	 * @since 2.4.0
	 * @staticvar bool $active
	 *
	 * @return bool
	 */
	public function is_donncha_domainmapping_active() {

		static $active = null;

		if ( isset( $active ) )
			return $active;

		return $active = $this->detect_plugin( array( 'functions' => array( 'redirect_to_mapped_domain' ) ) );
	}

	/**
	 * Detect WPML plugin.
	 *
	 * @since 2.6.0
	 * @staticvar bool $active
	 *
	 * @return bool
	 */
	public function is_wpml_active() {

		static $active = null;

		if ( isset( $active ) )
			return $active;

		return $active = $this->detect_plugin( array( 'constants' => array( 'ICL_LANGUAGE_CODE' ) ) );
	}

	/**
	 * Detect qTranslate X plugin.
	 *
	 * @since 2.6.0
	 * @staticvar bool $active
	 *
	 * @return bool
	 */
	public function is_qtranslate_active() {

		static $active = null;

		if ( isset( $active ) )
			return $active;

		return $active = $this->detect_plugin( array( 'constants' => array( 'QTX_VERSION' ) ) );
	}

	/**
	 * Detect if the current screen type is a page or taxonomy.
	 *
	 * @param string $type the Screen type
	 * @staticvar array $is_page
	 *
	 * @since 2.3.1
	 *
	 * @return bool true if post type is a page or post
	 */
	public function is_post_type_page( $type ) {

		static $is_page = array();

		if ( isset( $is_page[$type] ) )
			return $is_page[$type];

		$post_page = (array) get_post_types( array( 'public' => true ) );

		foreach ( $post_page as $screen ) {
			if ( $type === $screen ) {
				return $is_page[$type] = true;
				break;
			}
		}

		return $is_page[$type] = false;
	}

	/**
	 * Detect WordPress language.
	 * Considers en_UK, en_US, en, etc.
	 *
	 * @param string $locale Required, the locale.
	 * @param bool $use_cache Set to false to bypass the cache.
	 *
	 * @staticvar array $locale
	 * @staticvar string $get_locale
	 *
	 * @since 2.6.0
	 *
	 * @return bool Whether the locale is in the WordPress locale.
	 */
	public function check_wp_locale( $locale = '', $use_cache = true ) {

		if ( empty( $locale ) )
			return false;

		if ( true !== $use_cache )
			return (bool) strpos( get_locale(), $locale );

		static $cache = array();

		if ( isset( $cache[$locale] ) )
			return $cache[$locale];

		static $get_locale = null;

		if ( ! isset( $get_locale ) )
			$get_locale = get_locale();

		return $cache[$locale] = false !== strpos( $get_locale, $locale ) ? true : false;
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
			$post_type = $this->get_current_post_type();
		}

		if ( empty( $post_type ) )
			return false;

		static $supported = array();

		if ( isset( $supported[$post_type] ) )
			return $supported[$post_type];

		/**
		 * We now support all posts that allow a title, content editor and excerpt.
		 * To ease the flow, we have our basic list to check first.
		 *
		 * @since 2.3.5
		 */
		if ( post_type_supports( $post_type, 'autodescription-meta' ) || $this->post_type_supports_inpost( $post_type ) )
			return $supported[$post_type] = true;

		return $supported[$post_type] = false;
	}

	/**
	 * Returns Post Type from current screen.
	 *
	 * @param bool $public Whether to only get Public Post types.
	 *
	 * @since 2.6.0
	 *
	 * @return bool|string The Post Type
	 */
	public function get_current_post_type( $public = true ) {

		static $post_type = null;

		//* Detect post type if empty or not set.
		if ( is_null( $post_type ) || empty( $post_type ) ) {
			global $current_screen;

			if ( isset( $current_screen->post_type ) ) {
				static $post_page = array();

				$args = $public ? array( 'public' => true ) : array();

				if ( ! isset( $post_page[$public] ) )
					$post_page[$public] = (array) get_post_types( $args );

				//* Smart var. This elemenates the need for a foreach loop, reducing resource usage.
				$post_type = isset( $post_page[$public][ $current_screen->post_type ] ) ? $current_screen->post_type : '';
			}
		}

		//* No post type has been found.
		if ( empty( $post_type ) )
			return false;

		return $post_type;
	}

	/**
	 * Determines whether the theme is outputting the title correctly based on transient.
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

	/**
	 * Detect theme title fix extension plugin.
	 *
	 * @since 2.6.0
	 *
	 * @return bool True theme will do it right.
	 */
	public function theme_title_fix_active() {

		static $fixed = null;

		if ( isset( $fixed ) )
			return $fixed;

		if ( defined( 'THE_SEO_FRAMEWORK_TITLE_FIX' ) && THE_SEO_FRAMEWORK_TITLE_FIX )
			return $fixed = true;

		return $fixed = false;
	}

	/**
	 * Checks whether we can use special manipulation filters.
	 *
	 * @since 2.6.0
	 *
	 * @return bool True if we can manipulate title.
	 */
	public function can_manipulate_title() {

		if ( $this->theme_title_doing_it_right() || $this->theme_title_fix_active() )
			return true;

		return false;
	}

	/**
	 * Whether a page or blog is on front.
	 *
	 * @staticvar bool $pof
	 * @since 2.6.0
	 *
	 * @return bool
	 */
	public function has_page_on_front() {

		static $pof = null;

		if ( isset( $pof ) )
			return $pof;

		return $pof = 'page' === get_option( 'show_on_front' ) ? true : false;
	}

	/**
	 * The amount of pages.
	 * Fetches global $page through Query Var.
	 *
	 * @staticvar int $page
	 * @since 2.5.2.4
	 *
	 * @return int $page
	 */
	public function page() {

		static $page = null;

		if ( isset( $page ) )
			return $page;

		$page = get_query_var( 'page' );

		return $page = $page ? (int) $page : 1;
	}

	/**
	 * The number of the current page.
	 * Fetches global $paged through Query Var. Determines
	 *
	 * @staticvar int $paged
	 * @since 2.5.2.4
	 *
	 * @return int $paged
	 */
	public function paged() {

		static $paged = null;

		if ( isset( $paged ) )
			return $paged;

		$paged = get_query_var( 'paged' );

		return $paged = $paged ? (int) $paged : 1;
	}

}
