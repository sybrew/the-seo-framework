<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Load
 *
 * This is the main file called.
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
 * Facade final class The_SEO_Framework\Load
 *
 * Extending upon parent classes.
 *
 * @since 2.8.0
 * @since 4.0.0 No longer implements an interface. It's implied.
 * @since 4.1.0 Now extends `Cache` instead of `Feed`.
 */
final class Load extends Cache {

	/**
	 * @since 2.4.3
	 * @var bool Enable object caching.
	 */
	protected $use_object_cache = false;

	/**
	 * @since 2.2.9
	 * @var bool $the_seo_framework_debug Whether TSF-specific debug is enabled.
	 */
	public $the_seo_framework_debug = false;

	/**
	 * @since 2.2.9
	 * @var bool $the_seo_framework_debug Whether TSF-specific transients are used.
	 */
	public $the_seo_framework_use_transients = true;

	/**
	 * @since 2.2.9
	 * @var bool $script_debug Whether WP script debugging is enabled.
	 */
	public $script_debug = false;

	/**
	 * Constructor, setup debug vars and then load parent constructor.
	 *
	 * @since 2.8.0
	 * @since 4.0.0 Now informs developer of invalid class instancing.
	 *
	 * @return null If called twice or more.
	 */
	public function __construct() {

		if ( _has_run( __METHOD__ ) ) {
			// Don't construct twice, warn developer.
			$this->_doing_it_wrong( __METHOD__, 'Do not instance this class. Use function <code>the_seo_framework()</code> instead.', '3.1.0' );
			return null;
		}

		//= Setup debug vars before initializing anything else.
		$this->init_debug_vars();

		if ( $this->the_seo_framework_debug ) {
			$debug_instance = Debug::get_instance();

			\add_action( 'the_seo_framework_do_before_output', [ $debug_instance, '_set_debug_query_output_cache' ] );
			\add_action( 'admin_footer', [ $debug_instance, '_debug_output' ] );
			\add_action( 'wp_footer', [ $debug_instance, '_debug_output' ] );
		}

		//= Register the capabilities early.
		\add_filter( 'option_page_capability_' . THE_SEO_FRAMEWORK_SITE_OPTIONS, [ $this, 'get_settings_capability' ] );

		/**
		 * @since 2.2.2
		 * @param bool $load_options Whether to show or hide option pages.
		 */
		$this->load_options = (bool) \apply_filters( 'the_seo_framework_load_options', true );

		/**
		 * @since 2.4.3
		 * @since 2.8.0 : Uses method $this->use_object_cache() as default.
		 * @param bool $use_object_cache Whether to enable object caching.
		 */
		$this->use_object_cache = (bool) \apply_filters(
			'the_seo_framework_use_object_cache',
			\wp_using_ext_object_cache() && $this->get_option( 'cache_object' )
		);

		//? We always use this, because we need to test whether the sitemap must be outputted.
		$this->pretty_permalinks = '' !== \get_option( 'permalink_structure' );

		//= Load plugin at init 0.
		\add_action( 'init', [ $this, 'init_the_seo_framework' ], 0 );
	}

	/**
	 * Initializes public debug variables for the class to use.
	 *
	 * @since 2.6.0
	 */
	public function init_debug_vars() {

		$this->the_seo_framework_debug = \defined( 'THE_SEO_FRAMEWORK_DEBUG' ) && THE_SEO_FRAMEWORK_DEBUG ?: $this->the_seo_framework_debug;
		if ( $this->the_seo_framework_debug ) {
			\The_SEO_Framework\Debug::_set_instance( $this->the_seo_framework_debug );
		}

		$this->the_seo_framework_use_transients = \defined( 'THE_SEO_FRAMEWORK_DISABLE_TRANSIENTS' ) && THE_SEO_FRAMEWORK_DISABLE_TRANSIENTS ? false : $this->the_seo_framework_use_transients;

		// WP Core definition.
		$this->script_debug = \defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ?: $this->script_debug;
	}

	/**
	 * Requires compatibility files which are needed early or on every page.
	 * Mostly requires premium plugins/themes, so we check actual PHP instances,
	 * rather than common paths. As they can require manual FTP upload.
	 *
	 * @since 2.8.0
	 * @since 4.0.0 Renamed to `_load_early_compat_files`, from `load_early_compat_files`
	 * @access private
	 */
	public function _load_early_compat_files() {

		// phpcs:disable, Squiz.PHP.CommentedOutCode
		// if ( ! extension_loaded( 'mbstring' ) )
		// $this->_include_compat( 'mbstring', 'php' );
		// phpcs:enable, Squiz.PHP.CommentedOutCode

		// Disable Headway theme SEO.
		\add_filter( 'headway_seo_disabled', '__return_true' );

		if ( $this->is_theme( 'genesis' ) ) {
			// Genesis Framework
			$this->_include_compat( 'genesis', 'theme' );
		}

		if ( $this->detect_plugin( [ 'constants' => [ 'ICL_LANGUAGE_CODE' ] ] ) ) {
			// WPML
			$this->_include_compat( 'wpml', 'plugin' );
		}
		if ( $this->detect_plugin( [ 'constants' => [ 'POLYLANG_VERSION' ] ] ) ) {
			// Polylang
			$this->_include_compat( 'polylang', 'plugin' );
		}

		if ( $this->detect_plugin( [ 'globals' => [ 'ultimatemember' ] ] ) ) {
			// Ultimate Member
			$this->_include_compat( 'ultimatemember', 'plugin' );
		}
		if ( $this->detect_plugin( [ 'globals' => [ 'bp' ] ] ) ) {
			// BuddyPress
			$this->_include_compat( 'buddypress', 'plugin' );
		}

		if ( $this->detect_plugin( [ 'functions' => [ 'bbpress' ] ] ) ) {
			// bbPress
			$this->_include_compat( 'bbpress', 'plugin' );
		} elseif ( $this->detect_plugin( [ 'constants' => [ 'WPFORO_BASENAME' ] ] ) ) {
			// wpForo
			$this->_include_compat( 'wpforo', 'plugin' );
		}

		if ( $this->detect_plugin( [ 'functions' => [ 'wc' ] ] ) ) {
			// WooCommerce.
			$this->_include_compat( 'woocommerce', 'plugin' );
		} elseif ( $this->detect_plugin( [ 'constants' => [ 'EDD_VERSION' ] ] ) ) {
			// Easy Digital Downloads.
			$this->_include_compat( 'edd', 'plugin' );
		}
	}

	/**
	 * Mark a filter as deprecated and inform when it has been used.
	 *
	 * @since 2.8.0
	 * @see $this->_deprecated_function().
	 * @access private
	 *
	 * @param string $filter      The function that was called.
	 * @param string $version     The version of WordPress that deprecated the function.
	 * @param string $replacement Optional. The function that should have been called. Default null.
	 */
	public function _deprecated_filter( $filter, $version, $replacement = null ) {
		Debug::get_instance()->_deprecated_filter( $filter, $version, $replacement );
	}

	/**
	 * Mark a function as deprecated and inform when it has been used.
	 * Taken from WordPress core, but added extra parameters and linguistic alterations.
	 * The current behavior is to trigger a user error if WP_DEBUG is true.
	 *
	 * @since 2.6.0
	 * @access private
	 *
	 * @param string $function    The function that was called.
	 * @param string $version     The version of WordPress that deprecated the function.
	 * @param string $replacement Optional. The function that should have been called. Default null.
	 */
	public function _deprecated_function( $function, $version, $replacement = null ) { // phpcs:ignore -- Wrong asserts, copied method name.
		Debug::get_instance()->_deprecated_function( $function, $version, $replacement ); // phpcs:ignore -- Wrong asserts, copied method name.
	}

	/**
	 * Mark a function as deprecated and inform when it has been used.
	 * Taken from WordPress core, but added extra parameters and linguistic alterations.
	 * The current behavior is to trigger a user error if WP_DEBUG is true.
	 *
	 * @since 2.6.0
	 * @access private
	 *
	 * @param string $function The function that was called.
	 * @param string $message  A message explaining what has been done incorrectly.
	 * @param string $version  The version of WordPress where the message was added.
	 */
	public function _doing_it_wrong( $function, $message, $version = null ) { // phpcs:ignore -- Wrong asserts, copied method name.
		Debug::get_instance()->_doing_it_wrong( $function, $message, $version ); // phpcs:ignore -- Wrong asserts, copied method name.
	}

	/**
	 * Mark a property or method inaccessible when it has been used.
	 * The current behavior is to trigger a user error if WP_DEBUG is true.
	 *
	 * @since 2.7.0
	 * @access private
	 *
	 * @param string $p_or_m  The Property or Method.
	 * @param string $message A message explaining what has been done incorrectly.
	 */
	public function _inaccessible_p_or_m( $p_or_m, $message = '' ) {
		Debug::get_instance()->_inaccessible_p_or_m( $p_or_m, $message );
	}
}
