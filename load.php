<?php
/**
* @package The_SEO_Framework
*/
namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_DIR_PATH' ) or die;

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

\add_action( 'plugins_loaded', __NAMESPACE__ . '\\_init', 5 );
/**
 * Load The_SEO_Framework_Load class
 *
 * @action plugins_loaded
 * @priority 5 Use anything above 5, or any action later than plugins_loaded and
 * you can access the class and functions.
 *
 * @since 2.2.5
 * @since 2.8.0: Added namespace and renamed function.
 * @access private
 * @staticvar object $tsf
 *
 * @return object|null The SEO Framework Facade class object. Null on failure.
 */
function _init() {

	//* Cache the class. Do not run constructors more than once.
	static $tsf = null;

	if ( null === $tsf && \The_SEO_Framework\_can_load() ) {
		//* Register autoloader.
		spl_autoload_register( __NAMESPACE__ . '\\_autoload_classes' );

		$tsf = new \The_SEO_Framework\Load();
	}

	return $tsf;
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\_init_locale', 10 );
/**
 * Plugin locale 'autodescription'
 * File located in plugin folder autodescription/language/
 * @since 1.0.0
 */
function _init_locale() {
	load_plugin_textdomain( 'autodescription', false, basename( dirname( __FILE__ ) ) . '/language/' );
}

add_action( 'admin_init', __NAMESPACE__ . '\\_init_upgrade', 5 );
/**
 * Determines whether the plugin needs an option upgrade.
 *
 * @since 2.7.0
 * @since 2.8.0: Added namespace and renamed function.
 * @access private
 * @action admin_init
 * @priority 5
 *
 * @return void Early if no upgrade can or must take place.
 */
function _init_upgrade() {

	if ( false === \The_SEO_Framework\_can_load() )
		return;

	if ( \get_option( 'the_seo_framework_upgraded_db_version' ) >= THE_SEO_FRAMEWORK_DB_VERSION )
		return;

	require_once( THE_SEO_FRAMEWORK_DIR_PATH_FUNCT . 'upgrade.php' );
}

/**
 * Determines whether this plugin should load.
 *
 * @since 2.3.7
 * @since 2.8.0: Added namespace and renamed function.
 * @access private
 * @staticvar bool $loaded
 *
 * @action plugins_loaded
 * @return bool Whether to allow loading of plugin.
 */
function _can_load() {

	static $loaded = null;

	if ( isset( $loaded ) )
		return $loaded;

	/**
	 * Applies filters 'the_seo_framework_load' : bool
	 * @since 2.3.7
	 */
	return $loaded = (bool) \apply_filters( 'the_seo_framework_load', true );
}

/**
 * Autoloads all class files. To be used when requiring access to all or any of
 * the plugin classes.
 *
 * @since 2.8.0
 * @uses THE_SEO_FRAMEWORK_DIR_PATH_CLASS
 * @access private
 * @staticvar array $loaded Whether $class has been loaded.
 *
 * @NOTE 'The_SEO_Framework' is a reserved namespace. Using it outside of this plugin's scope will result in an error.
 *
 * @param string $class The class name.
 * @return bool False if file couldn't be included, otherwise true.
 */
function _autoload_classes( $class ) {

	if ( 0 !== strpos( $class, 'The_SEO_Framework\\', 0 ) )
		return;

	static $loaded = array();

	if ( isset( $loaded[ $class ] ) )
		return $loaded[ $class ];

	if ( false !== strpos( $class, '_Interface' ) ) {
		$path = THE_SEO_FRAMEWORK_DIR_PATH_INTERFACE;
		$extension = '.interface.php';
	} else {
		$path = THE_SEO_FRAMEWORK_DIR_PATH_CLASS;
		$extension = '.class.php';
	}

	$_class = strtolower( str_replace( 'The_SEO_Framework\\', '', $class ) );
	$_class = str_replace( '_interface', '', $_class );
	$_class = str_replace( '_', '-', $_class );

	return $loaded[ $class ] = (bool) require_once( $path . $_class . $extension );
}

\add_action( 'activate_' . THE_SEO_FRAMEWORK_PLUGIN_BASENAME, __NAMESPACE__ . '\\_activation' );
/**
 * Performs plugin activation actions.
 *
 * @since 2.6.6
 * @since 2.8.0: Added namespace and renamed function. Also performs PHP tests now.
 * @access private
 */
function _activation() {

	\The_SEO_Framework\_activation_test_php();
	\The_SEO_Framework\_activation_setup_sitemap();
}

\add_action( 'deactivate_' . THE_SEO_FRAMEWORK_PLUGIN_BASENAME, __NAMESPACE__ . '\\_deactivation' );
/**
 * Performs plugin deactivation actions.
 *
 * @since 2.6.6
 * @since 2.8.0: Added namespace and renamed function.
 * @access private
 */
function _deactivation() {

	\The_SEO_Framework\_deactivation_unset_sitemap();
}

/**
 * Add and Flush rewrite rules on plugin activation.
 *
 * @since 2.6.6
 * @since 2.7.1: 1. Now no longer reinitializes global $wp_rewrite.
 *               2. Now always listens to the preconditions of the sitemap addition.
 *               3. Now flushes the rules on shutdown.
 * @since 2.8.0: Added namespace and renamed function.
 * @access private
 */
function _activation_setup_sitemap() {

	$the_seo_framework = \the_seo_framework();

	if ( isset( $the_seo_framework ) ) {
		$the_seo_framework->rewrite_rule_sitemap();
		\add_action( 'shutdown', 'flush_rewrite_rules' );
	}
}

/**
 * Checks whether the server can run this plugin on activation.
 * If not, it will deactivate this plugin.
 *
 * This function will create a parse error on PHP < 5.3 (use of goto wrappers).
 * Which makes a knowledge database entry easier to make as it won't change anytime soon.
 *
 * @since 2.8.0
 * @access private
 * @link http://php.net/eol.php
 * @link https://codex.wordpress.org/WordPress_Versions
 */
function _activation_test_php() {

	evaluate : {
		   PHP_VERSION_ID < 50300 and $test = 1
		or $GLOBALS['wp_db_version'] < 35700 and $test = 2
		or $test = true;
	}

	//* All good.
	if ( true === $test )
		return;

	deactivate : {
		//* Not good. Deactivate plugin.
		\deactivate_plugins( THE_SEO_FRAMEWORK_PLUGIN_BASENAME );
	}

	switch ( $test ) :
		case 1 :
			//* PHP requirements not met, always count up to encourage best standards.
			$requirement = 'PHP 5.3.0 or later';
			$issue = 'PHP version';
			$version = phpversion();
			$subtitle = 'Server Requirements';
			break;

		case 2 :
			//* WordPress requirements not met.
			$requirement = 'WordPress 4.4 or later';
			$issue = 'WordPress version';
			$version = $GLOBALS['wp_version'];
			$subtitle = 'WordPress Requirements';
			break;

		default :
			\wp_die();
	endswitch;

	//* network_admin_url() falls back to admin_url() on single. But networks can enable single too.
	$pluginspage = $network_wide ? \network_admin_url( 'plugins.php' ) : \admin_url( $network . 'plugins.php' );

	//* Let's have some fun with teapots.
	$response = floor( time() / DAY_IN_SECONDS ) === floor( strtotime( 'first day of April ' . date( 'Y' ) ) / DAY_IN_SECONDS ) ? 418 : 500;

	\wp_die(
		sprintf(
			'<p><strong>The SEO Framework</strong> requires <em>%s</em>. Sorry about that!<br>Your %s is: <code>%s</code></p>
			<p>Do you want to <strong><a onclick="window.history.back()" href="%s">go back</a></strong>?</p>',
			\esc_html( $requirement ), \esc_html( $issue ), \esc_html( $version ), \esc_url( $pluginspage )
		),
		sprintf( 'The SEO Framework &laquo; %s', \esc_attr( $subtitle ) ),
		array( 'response' => intval( $response ) )
	);
}

/**
 * Flush rewrite rules on plugin deactivation.
 *
 * @since 2.6.6
 * @since 2.7.1: 1. Now no longer reinitializes global $wp_rewrite.
 *               2. Now flushes the rules on shutdown.
 * @since 2.8.0: Added namespace and renamed function.
 * @access private
 * @global object $wp_rewrite
 */
function _deactivation_unset_sitemap() {

	unset( $GLOBALS['wp_rewrite']->extra_rules_top['sitemap\.xml$'] );

	\add_action( 'shutdown', 'flush_rewrite_rules' );
}
