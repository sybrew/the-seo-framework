<?php
/**
 * The SEO Framework plugin
 * Copyright (C) 2015 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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

		if ( !isset( $themes ) )
			return false;

		if ( ! $use_cache ) {
			//* Don't use cache.

			$wp_get_theme = wp_get_theme();

			$theme_parent = strtolower( $wp_get_theme->get('Template') );
			$theme_name = strtolower( $wp_get_theme->get('Name') );

			if ( is_string( $themes ) ) {
				$themes = strtolower( $themes );
				if ( $theme_parent == $themes || $theme_name == $themes )
					return true;
			} else if ( is_array( $themes ) ) {
				foreach ( $themes as $theme ) {
					$theme = strtolower( $theme );
					if ( $theme_parent == $theme || $theme_name == $theme ) {
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

		$theme_parent = strtolower( $wp_get_theme->get('Template') );
		$theme_name = strtolower( $wp_get_theme->get('Name') );

		if ( is_string( $themes ) ) {
			$themes = strtolower( $themes );
			if ( $theme_parent == $themes || $theme_name == $themes )
				$themes_cache[$themes] = true;
		} else if ( is_array( $themes ) ) {
			foreach ( $themes as $theme ) {
				$theme = strtolower( $theme );
				if ( $theme_parent == $theme || $theme_name == $theme ) {
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
	 */
	public function has_og_plugin() {

		static $has_plugin = null;

		if ( isset( $has_plugin ) )
			return $has_plugin;

		$plugins = array( 'classes' => array( 'WPSEO_OpenGraph', 'All_in_One_SEO_Pack_Opengraph' ), 'functions' => array( 'amt_plugin_actions' ) );

		return $has_plugin = $this->detect_plugin( $plugins ) ? true : false;
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
	 */
	public function has_json_ld_plugin() {

		static $has_plugin = null;

		if ( isset( $has_plugin ) )
			return $has_plugin;

		$plugins = array('classes' => array( 'WPSEO_JSON_LD' ) );

		return $has_plugin = $this->detect_plugin( $plugins ) ? true : false;
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
	 */
	public function has_sitemap_plugin() {

		static $has_plugin = null;

		if ( isset( $has_plugin ) )
			return $has_plugin;

		$plugins = array(
				'classes' => array( 'GoogleSitemapGeneratorLoader', 'xml_sitemaps', 'All_in_One_SEO_Pack_Sitemap', 'SimpleWpSitemap', 'Incsub_SimpleSitemaps' ),
				'functions' => array( 'wpss_init', 'gglstmp_sitemapcreate' ),
			);

		return $has_plugin = $this->detect_plugin( $plugins ) ? true : false;
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
		if ( strlen( $wp_version ) === 3 )
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
	 */
	public function current_theme_supports( $feature ) {
		global $_wp_theme_features;

		//* SEO Framework Edits. {
		if ( 'custom-header-uploads' == $feature )
			return $this->detect_theme_support( 'custom-header', 'uploads' );
		//* } End SEO Framework Edits.

		if ( !isset( $_wp_theme_features[$feature] ) )
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
	 * Add doing it wrong notice in the footer/error-log
	 *
	 * @since 2.2.5
	 * @staticvar bool $no_spam
	 *
	 * @return void
	 */
	public function title_doing_it_wrong() {

		//* Prevent error log spam.
		static $no_spam = null;

		if ( isset( $no_spam ) )
			return;

		/**
		 * Don't put out a deprecation notice on WordPress 4.4.x since WordPress does it.
		 *
		 * @since 2.3.4
		 */
		if ( $this->wp_version( '4.4.0' ) ) {
			$no_spam = true;
			return;
		}

		$version = $this->the_seo_framework_version( '2.2.5' );

		$example = esc_html( "<title><?php wp_title(''); ?></title>" );
		$example2 = esc_html( esc_url( "https://codex.wordpress.org/Title_Tag" ) );

		$message = "wp_title() should be called with only a single empty parameter in your header. Like exactly so: <br>\r\n$example\r\n<br>";
		$message2 = "Alternatively, you could add the title-tag theme support. This is recommended and required since WordPress 4.4. Read how-to here: \r\n$example2\r\n<br>";

		_doing_it_wrong( 'wp_title', $message . $message2, $version );

		$no_spam = true;

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
	 * Detect the current page. Admin or Front-end. With fallback to $_GET values.
	 * Because the page/post detection is so complex this function has been created.
	 * However, it does not work as intended within the admin pages and can therefore
	 * only be used within the loop.
	 *
	 * WARNING: This function is currently inactive and can be removed or changed in the future without prior notice.
	 * Using this function in the current state can result into crashing your website after an update.
	 *
	 * Use within the loop or provide a page id.
	 *
	 * @param string|int $page_id The Page ID or tt_id (taxonomy and terms).
	 * @param string $post_type The post type (taxonomy/term) to check for.
	 * @param bool $admin If the check should be solely on admin or not.
	 * @param bool $use_cache Set to false to bypass the cache.
	 *
	 * @staticvar array $current_page
	 *
	 * @since 2.3.4
	 *
	 * @return string|null the current page. Null early if no page_id is given and wp_query isn't set.
	 */
	public function current_page( $page_id = 0, $post_type = null, $admin = false, $use_cache = true ) {
		// @TODO
		// Do not use!
		return;

		// Should probably use this somewhere:
		// return get_post_type( $page_id ); // Doesn't work on archives.

		//* Cache the current page.
		static $current_page = array();

		//* Return cache early if page_id is given.
		if ( $use_cache && $page_id && isset( $current_page[$page_id][$post_type][$admin] ) )
			return $current_page[$page_id][$post_type][$admin];

		global $wp_query;

		//* Test $page_id and $wp_query. Try to fetch object ID if available.
		if ( !$page_id && isset( $wp_query ) ) {
			//* Fetch queried object ID from $wp_query, if available. Fetch page_id if not.
			$page_id = $wp_query->get_queried_object_id() ? $wp_query->get_queried_object_id() : $wp_query->page_id;

			//* Fetch Page ID from (latest) post if no page ID is found. This should actually never run.
			if ( !$page_id ) {
				$post = get_post();
				$page_id = ( !empty( $post ) ) ? $post->ID : '';
			}

			//* Still No page ID found? Return NULL. This happens when $wp_query is empty.
			if ( !$page_id )
				return NULL;

		} else if ( !$page_id && ! isset( $wp_query ) ) {
			//* Return NULL if no $wp_query is set and no $page_id is found.
			return NULL;
		}

		//* Try to return cache again if no page_id was given.
		if ( $use_cache && isset( $current_page[$page_id][$post_type][$admin] ) )
			return $current_page[$page_id][$post_type][$admin];

		//* Setup cached values.
		if ( $use_cache )
			return $current_page[$page_id][$post_type][$admin] = $this->current_page_pre( $page_id, (string) $post_type, $admin );

		//* Default behaviour is no cache is used.
		return $this->current_page_pre( $page_id, (string) $post_type, $admin );
	}

	/**
	 * Get the real page ID, also depending on CPT.
	 *
	 * @param bool $use_cache Wether to use the cache or not.
	 *
	 * @staticvar int $id the ID.
	 *
	 * @since 2.4.4
	 */
	public function get_the_real_ID( $use_cache = true ) {

		//* Never use cache in admin. Only causes bugs.
		$use_cache = is_admin() ? false : $use_cache;

		if ( $use_cache ) {
			static $id = null;

			if ( isset( $id ) )
				return $id;
		}

		if ( function_exists( 'is_shop' ) && is_shop() ) {
			//* WooCommerce Shop
			$id = get_option( 'woocommerce_shop_page_id' );
		} else if ( function_exists( 'is_anspress' ) && is_anspress() ) {
			//* Get AnsPress Question ID.
			if ( function_exists( 'get_question_id' ) )
				$id = get_question_id();
		}

		if ( ! isset( $id ) || empty( $id ) ) {
			$id = get_queried_object_id();

			if ( empty( $id ) )
				$id = get_the_ID();
		}

		return $id;
	}

	/**
	 * Detect the blog page.
	 *
	 * @param int $id the Page ID.
	 *
	 * @since 2.3.4
	 *
	 * @staticvar array $is_blog_page
	 * @since 2.3.8
	 *
	 * @return bool true if is blog page. Always false if blog page is homepage.
	 */
	public function is_blog_page( $id = '' ) {

		if ( empty( $id ) )
			$o_id = $this->get_the_real_ID();

		static $is_blog_page = array();

		if ( isset( $is_blog_page[$id] ) )
			return $is_blog_page[$id];

		$pfp = get_option( 'page_for_posts' );

		if ( $pfp != 0 ) {

			$sof = get_option( 'show_on_front' );

			if ( 'page' == $sof && ! is_front_page() && ! is_archive() ) {
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

		if ( empty( $id ) )
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

		if ( ! $use_cache ) {
			return (bool) strpos( get_locale(), $str );
		}

		static $locale = array();

		if ( isset( $locale[$str] ) )
			return $locale[$str];

		static $get_locale = null;

		if ( !isset( $get_locale ) )
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

		if ( empty( $post_type ) ) {

			static $post_type = null;

			//* Detect post type if empty or not set.
			if ( ! isset( $post_type ) || empty( $post_type ) ) {
				global $current_screen;

				static $post_page = null;

				if ( !isset( $post_page ) )
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

}
