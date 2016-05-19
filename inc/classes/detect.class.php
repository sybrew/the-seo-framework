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
	 * Returns list of active plugins.
	 *
	 * @since 2.6.1
	 * @staticvar array $active_plugins
	 *
	 * @credits JetPack for most code.
	 *
	 * @return array List of active plugins.
	 */
	public function active_plugins() {

		static $active_plugins = null;

		if ( isset( $active_plugins ) )
			return $active_plugins;

		$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			// Due to legacy code, active_sitewide_plugins stores them in the keys,
			// whereas active_plugins stores them in the values.
			$network_plugins = array_keys( get_site_option( 'active_sitewide_plugins', array() ) );
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
	 * Applies filters 'the_seo_framework_conflicting_plugins' : array
	 * @since 2.6.0
	 *
	 * @credits JetPack for most code.
	 *
	 * @return array List of conflicting plugins.
	 */
	public function conflicting_plugins() {

		$conflicting_plugins = array(
			'seo_tools' => array(
				'Yoast SEO'                            => 'wordpress-seo/wp-seo.php',
				'Yoast SEO Premium'                    => 'wordpress-seo-premium/wp-seo-premium.php',
				'All in One SEO Pack'                  => 'all-in-one-seo-pack/all_in_one_seo_pack.php',
				'SEO Ultimate'                         => 'seo-ultimate/seo-ultimate.php',
				'Gregs High Performance SEO'           => 'gregs-high-performance-seo/ghpseo.php',
			),
			'sitemaps' => array(
				'Google XML Sitemaps'                  => 'google-sitemap-generator/sitemap.php',
				'Better WordPress Google XML Sitemaps' => 'bwp-google-xml-sitemaps/bwp-simple-gxs.php',
				'Google XML Sitemaps for qTranslate'   => 'google-xml-sitemaps-v3-for-qtranslate/sitemap.php',
				'XML Sitemap & Google News feeds'      => 'xml-sitemap-feed/xml-sitemap.php',
				'Google Sitemap by BestWebSoft'        => 'google-sitemap-plugin/google-sitemap-plugin.php',
				'WordPress SEO by Yoast'               => 'wordpress-seo/wp-seo.php',
				'WordPress SEO Premium by Yoast'       => 'wordpress-seo-premium/wp-seo-premium.php',
				'All in One SEO Pack'                  => 'all-in-one-seo-pack/all_in_one_seo_pack.php',
				'Sitemap'                              => 'sitemap/sitemap.php',
				'Simple Wp Sitemap'                    => 'simple-wp-sitemap/simple-wp-sitemap.php',
				'Simple Sitemap'                       => 'simple-sitemap/simple-sitemap.php',
				'XML Sitemaps'                         => 'xml-sitemaps/xml-sitemaps.php',
				'MSM Sitemaps'                         => 'msm-sitemap/msm-sitemap.php',
			),
			'open_graph' => array(
				'2 Click Social Media Buttons'         => '2-click-socialmedia-buttons/2-click-socialmedia-buttons.php',
				'Add Link to Facebook'                 => 'add-link-to-facebook/add-link-to-facebook.php',
				'Add Meta Tags'                        => 'add-meta-tags/add-meta-tags.php',
				'Easy Facebook Share Thumbnail'        => 'easy-facebook-share-thumbnails/esft.php',
				'Facebook'                             => 'facebook/facebook.php',
				'Facebook AWD All in one'              => 'facebook-awd/AWD_facebook.php',
				'Facebook Featured Image & OG Meta Tags' => 'facebook-featured-image-and-open-graph-meta-tags/fb-featured-image.php',
				'Facebook Meta Tags'                   => 'facebook-meta-tags/facebook-metatags.php',
				'Facebook Open Graph Meta Tags for WordPress' => 'wonderm00ns-simple-facebook-open-graph-tags/wonderm00n-open-graph.php',
				'Facebook Revised Open Graph Meta Tag' => 'facebook-revised-open-graph-meta-tag/index.php',
				'Facebook Thumb Fixer'                 => 'facebook-thumb-fixer/_facebook-thumb-fixer.php',
				'Fedmichs Facebook Open Graph Meta'    => 'facebook-and-digg-thumbnail-generator/facebook-and-digg-thumbnail-generator.php',
				'Header and Footer'                    => 'header-footer/plugin.php',
				'Network Publisher'                    => 'network-publisher/networkpub.php',
				'NextGEN Facebook OG'                  => 'nextgen-facebook/nextgen-facebook.php',
				'NextScripts SNAP'                     => 'social-networks-auto-poster-facebook-twitter-g/NextScripts_SNAP.php',
				'Open Graph'                           => 'opengraph/opengraph.php',
				'Open Graph Protocol Framework'        => 'open-graph-protocol-framework/open-graph-protocol-framework.php',
				'SEO Facebook Comments'                => 'seo-facebook-comments/seofacebook.php',
				'Shareaholic'                          => 'sexybookmarks/sexy-bookmarks.php',
				'Shareaholic2'                         => 'shareaholic/sexy-bookmarks.php',
				'SharePress'                           => 'sharepress/sharepress.php',
				'Simple Facebook Connect'              => 'simple-facebook-connect/sfc.php',
				'Social Discussions'                   => 'social-discussions/social-discussions.php',
				'Social Sharing Toolkit'               => 'social-sharing-toolkit/social_sharing_toolkit.php',
				'Socialize'                            => 'socialize/socialize.php',
				'Tweet, Like, Google +1 and Share'     => 'only-tweet-like-share-and-google-1/tweet-like-plusone.php',
				'Wordbooker'                           => 'wordbooker/wordbooker.php',
				'WordPress Social Sharing Optimization' => 'wpsso/wpsso.php',
				'WP Caregiver'                         => 'wp-caregiver/wp-caregiver.php',
				'WP Facebook Like Send & Open Graph Meta' => 'wp-facebook-like-send-open-graph-meta/wp-facebook-like-send-open-graph-meta.php',
				'WP Facebook Open Graph protocol'      => 'wp-facebook-open-graph-protocol/wp-facebook-ogp.php',
				'WP-OGP'                               => 'wp-ogp/wp-ogp.php',
				'Zolton.org Social Plugin'             => 'zoltonorg-social-plugin/zosp.php',
				'WP Facebook Like Button'              => 'wp-fb-share-like-button/wp_fb_share-like_widget.php'
			),
			'twitter_card' => array(
				'Twitter'                              => 'twitter/twitter.php',
				'Eewee Twitter Card'                   => 'eewee-twitter-card/index.php',
				'IG:Twitter Cards'                     => 'ig-twitter-cards/ig-twitter-cards.php',
				'Twitter Cards'                        => 'twitter-cards/twitter-cards.php',
				'Twitter Cards Meta'                   => 'twitter-cards-meta/twitter-cards-meta.php',
				'WP Twitter Cards'                     => 'wp-twitter-cards/twitter_cards.php',
			),
		);

		return (array) apply_filters( 'the_seo_framework_conflicting_plugins', $conflicting_plugins );
	}

	/**
	 * Fetches type of conflicting plugins.
	 *
	 * @param string $type The Key from $this->conflicting_plugins()
	 *
	 * @since 2.6.0
	 *
	 * @return array
	 */
	public function get_conflicting_plugins( $type = 'seo_tools' ) {

		$conflicting_plugins = $this->conflicting_plugins();

		if ( isset( $conflicting_plugins[ $type ] ) )
			return (array) apply_filters( 'the_seo_framework_conflicting_plugins_type', $conflicting_plugins[ $type ], $type );

		return array();
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
	 * @param bool $depr If set to false don't use cache.
	 *
	 * @since 2.1.0
	 *
	 * @return bool is theme active.
	 */
	public function is_theme( $themes = null, $depr = null ) {

		if ( ! isset( $themes ) )
			return false;

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

	/**
	 * SEO plugin detection
	 *
	 * @since 1.3.0
	 *
	 * @staticvar bool $detected
	 * @since 2.2.5
	 *
	 * Applies filters 'the_seo_framework_seo_plugin_detected' : bool
	 * @since 2.6.1
	 *
	 * @return bool SEO plugin detected.
	 */
	public function detect_seo_plugins() {

		static $detected = null;

		if ( isset( $detected ) )
			return $detected;

		//* Old style filter.
		$detected = $this->detect_seo_plugins_old();
		if ( isset( $detected ) )
			return $detected;

		$active_plugins = $this->active_plugins();

		if ( ! empty( $active_plugins ) ) {
			$conflicting_plugins = $this->get_conflicting_plugins( 'seo_tools' );

			foreach ( $conflicting_plugins as $plugin ) {
				if ( in_array( $plugin, $active_plugins ) ) {
					$detected = apply_filters( 'the_seo_framework_seo_plugin_detected', true );
					break;
				}
			}
		}

		return $detected = $detected ? true : false;
	}

	/**
	 * Open Graph plugin detection
	 *
	 * @since 1.3.0
	 *
	 * @staticvar bool $detected
	 * @since 2.2.5
	 *
	 * Applies filters 'the_seo_framework_og_plugin_detected' : bool
	 * @since 2.6.1
	 *
	 * @return bool OG plugin detected.
	 */
	public function detect_og_plugin() {

		static $detected = null;

		if ( isset( $detected ) )
			return $detected;

		//* Detect SEO plugins beforehand.
		$detected = $this->detect_seo_plugins();
		if ( $detected )
			return $detected;

		//* Old style filter.
		$detected = $this->has_og_plugin();
		if ( isset( $detected ) )
			return $detected;

		$active_plugins = $this->active_plugins();

		if ( ! empty( $active_plugins ) ) {
			$conflicting_plugins = $this->get_conflicting_plugins( 'open_graph' );

			foreach ( $conflicting_plugins as $plugin ) {
				if ( in_array( $plugin, $active_plugins ) ) {
					$detected = apply_filters( 'the_seo_framework_og_plugin_detected', true );
					break;
				}
			}
		}

		return $detected = $detected ? true : false;
	}

	/**
	 * Open Graph plugin detection
	 *
	 * @since 2.6.0
	 *
	 * @staticvar bool $detected
	 * @since 2.6.0
	 *
	 * @return bool Twitter Card plugin detected.
	 */
	public function detect_twitter_card_plugin() {

		static $detected = null;

		if ( isset( $detected ) )
			return $detected;

		//* Detect SEO plugins beforehand.
		$detected = $this->detect_seo_plugins();
		if ( $detected )
			return $detected;

		$active_plugins = $this->active_plugins();

		if ( ! empty( $active_plugins ) ) {
			$conflicting_plugins = $this->get_conflicting_plugins( 'twitter_card' );

			foreach ( $conflicting_plugins as $plugin ) {
				if ( in_array( $plugin, $active_plugins ) ) {
					$detected = apply_filters( 'the_seo_framework_og_plugin_detected', true );
					break;
				}
			}
		}

		return $detected = $detected ? true : false;
	}

	/**
	 * Detects if plugins outputting ld+json exists
	 *
	 * @since 1.3.0
	 *
	 * Always return false.
	 * @since 2.6.1
	 *
	 * @return bool false
	 */
	public function has_json_ld_plugin() {
		return false;
	}

	/**
	 * Detecs sitemap plugins
	 *
	 * @since 2.1.0
	 * @staticvar bool $detected
	 *
	 * @return bool
	 */
	public function detect_sitemap_plugin() {

		static $detected = null;

		if ( isset( $detected ) )
			return $detected;

		$active_plugins = $this->active_plugins();

		if ( ! empty( $active_plugins ) ) {
			$conflicting_plugins = $this->get_conflicting_plugins( 'sitemaps' );

			foreach ( $conflicting_plugins as $plugin ) {
				if ( in_array( $plugin, $active_plugins ) ) {
					$detected = apply_filters( 'the_seo_framework_sitemap_plugin_detected', true );
					break;
				}
			}
		}

		return $detected = $detected ? true : false;
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

}
