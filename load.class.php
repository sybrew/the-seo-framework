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

defined( 'ABSPATH' ) or die;

add_action( 'admin_init', 'the_seo_framework_upgrade', 5 );
/**
 * Determines whether the plugin needs an option upgrade.
 *
 * @action admin_init
 * @priority 5
 *
 * @since 2.7.0
 */
function the_seo_framework_upgrade() {

	if ( false === the_seo_framework_active() )
		return;

	if ( get_option( 'the_seo_framework_upgraded_db_version' ) >= THE_SEO_FRAMEWORK_DB_VERSION )
		return;

	require_once( THE_SEO_FRAMEWORK_DIR_PATH_FUNCT . 'upgrade.php' );
}

add_action( 'plugins_loaded', 'the_seo_framework_init', 5 );
/**
 * Load The_SEO_Framework_Load class
 *
 * @action plugins_loaded
 * @priority 5 Use anything above 5, or any action later than plugins_loaded and
 * you can access the class and functions.
 *
 * @staticvar object $the_seo_framework
 *
 * @since 2.2.5
 */
function the_seo_framework_init() {

	//* Cache the class. Do not run everything more than once.
	static $the_seo_framework = null;

	if ( the_seo_framework_active() )
		if ( ! isset( $the_seo_framework ) )
			$the_seo_framework = new The_SEO_Framework_Load();

	return $the_seo_framework;
}

/**
 * Determines whether this plugin should load.
 *
 * @since 2.3.7
 * @staticvar bool $loaded
 * Applies filters 'the_seo_framework_load' : bool
 *
 * @action plugins_loaded
 * @return bool Whether to allow loading of plugin.
 */
function the_seo_framework_load() {

	static $loaded = null;

	if ( isset( $loaded ) )
		return $loaded;

	return $loaded = (bool) apply_filters( 'the_seo_framework_load', true );
}

/**
 * Load plugin files.
 * @uses THE_SEO_FRAMEWORK_DIR_PATH_FUNCT
 * @uses THE_SEO_FRAMEWORK_DIR_PATH_CLASS
 *
 * @since 2.1.6
 */
require_once( THE_SEO_FRAMEWORK_DIR_PATH_FUNCT . 'compat.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_FUNCT . 'optionsapi.php' );
//require_once( THE_SEO_FRAMEWORK_DIR_PATH_FUNCT . 'benchmark.php' );

/**
 * @since 2.7.0
 * Unused, set as placeholder.
 * Do: spl_autoload_register( 'the_seo_framework_autoload' );
 */
function the_seo_framework_autoload( $class ) {

	if ( 0 !== strpos( $class, 'AutoDescription_', 0 ) )
		return;

	$_class = strtolower( str_replace( array( 'AutoDescription_', '_' ), array( '', '-' ), $class ) );
	require( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . $_class . '.class.php' );
}

require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'core.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'debug.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'compat.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'query.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'init.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'admin-init.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'render.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'detect.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'postdata.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'termdata.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'generate.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'generate-description.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'generate-title.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'generate-url.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'generate-image.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'generate-ldjson.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'search.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'doingitright.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'inpost.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'adminpages.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'sanitize.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'siteoptions.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'metaboxes.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'sitemaps.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'transients.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'feed.class.php' );

require_once( THE_SEO_FRAMEWORK_DIR_PATH . 'inc/deprecated/deprecated.class.php' );

/**
 * Facade class.
 *
 * Extending upon parent classes.
 *
 * @since 2.1.6
 */
class The_SEO_Framework_Load extends The_SEO_Framework_Deprecated {

	/**
	 * Cached debug/profile constants. Initialized on plugins_loaded priority 5.
	 *
	 * @since 2.2.9
	 *
	 * @var bool The SEO Framework Debug/Profile states.
	 */
	public $the_seo_framework_debug = false;
	public $the_seo_framework_debug_hidden = false;
	public $the_seo_framework_use_transients = true;
	public $script_debug = false;

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	private function __wakeup() { }

	/**
	 * Cloning of this class is forbidden.
	 */
	private function __clone() { }

	/**
	 * Handle unapproachable invoked methods.
	 */
	public function __call( $name, $arguments ) {
		parent::__call( $name, $arguments );
	}

	/**
	 * Constructor, setup debug vars and then load parent constructor.
	 */
	public function __construct() {
		//* Setup debug vars before initializing parents.
		$this->init_debug_vars();

		parent::__construct();
	}

	/**
	 * Initializes public debug variables for the class to use.
	 *
	 * @since 2.6.0
	 */
	public function init_debug_vars() {

		$this->the_seo_framework_debug = defined( 'THE_SEO_FRAMEWORK_DEBUG' ) && THE_SEO_FRAMEWORK_DEBUG ? true : $this->the_seo_framework_debug;
		if ( $this->the_seo_framework_debug ) {
			//* No need to set these to true if no debugging is enabled.
			$this->the_seo_framework_debug_hidden = defined( 'THE_SEO_FRAMEWORK_DEBUG_HIDDEN' ) && THE_SEO_FRAMEWORK_DEBUG_HIDDEN ? true : $this->the_seo_framework_debug_hidden;
		}

		$this->the_seo_framework_use_transients = defined( 'THE_SEO_FRAMEWORK_DISABLE_TRANSIENTS' ) && THE_SEO_FRAMEWORK_DISABLE_TRANSIENTS ? false : $this->the_seo_framework_use_transients;

		//* WP Core definition.
		$this->script_debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? true : $this->script_debug;

	}

	/**
	 * Wrapper for function calling through parameters. The golden nugget.
	 *
	 * @since 2.2.2
	 * @NOTE _doing_it_wrong notices go towards the callback. Unless this
	 * function is used wrongfully. Then the notice is about this function.
	 *
	 * @param array|string $callback the method array or function string.
	 * @param string $version the version of AutoDescription the function is used.
	 * @param array|string $args The arguments passed to the function.
	 * @return mixed $output The function called.
	 */
	public function call_function( $callback, $version = '', $args = array() ) {

		$output = '';

		/**
		 * Convert string/object to array
		 */
		if ( is_object( $callback ) ) {
			$function = array( $callback, '' );
		} else {
			$function = (array) $callback;
		}

		/**
		 * Convert string/object to array
		 */
		if ( is_object( $args ) ) {
			$args = array( $args, '' );
		} else {
			$args = (array) $args;
		}

		$class = reset( $function );
		$method = next( $function );

		/**
		 * Fetch method/function
		 */
		if ( is_object( $class ) && is_string( $method ) ) {
			$class = get_class( $class );

			if ( get_class( $this ) === $class ) {
				if ( method_exists( $this, $method ) ) {
					if ( empty( $args ) ) {
						// In-Object calling.
						$output = call_user_func( array( $this, $method ) );
					} else {
						// In-Object calling.
						$output = call_user_func_array( array( $this, $method ), $args );
					}
				} else {
					$this->_doing_it_wrong( $class . '::' . $method, __( 'Class or Method not found.', 'autodescription' ), $version );
				}
			} else {
				if ( method_exists( $class, $method ) ) {
					if ( empty( $args ) ) {
						$output = call_user_func( array( $class, $method ) );
					} else {
						$output = call_user_func_array( array( $class, $method ), $args );
					}
				} else {
					$this->_doing_it_wrong( $class . '::' . $method, __( 'Class or Method not found.', 'autodescription' ), $version );
				}
			}
		} elseif ( is_string( $class ) && is_string( $method ) ) {
			//* This could be combined with the one above.
			if ( method_exists( $class, $method ) ) {
				if ( empty( $args ) ) {
					$output = call_user_func( array( $class, $method ) );
				} else {
					$output = call_user_func_array( array( $class, $method ), $args );
				}
			} else {
				$this->_doing_it_wrong( $class . '::' . $method, __( 'Class or Method not found.', 'autodescription' ), $version );
			}
		} elseif ( is_string( $class ) ) {
			//* Class is function.
			$func = $class;

			if ( empty( $args ) ) {
				$output = call_user_func( $func );
			} else {
				$output = call_user_func_array( $func, $args );
			}
		} else {
			$this->_doing_it_wrong( __METHOD__, __( 'Function needs to be called as a string.', 'autodescription' ), $version );
		}

		return $output;
	}
}
