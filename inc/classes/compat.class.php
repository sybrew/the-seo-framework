<?php
/**
 * @package The_SEO_Framework\Classes
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\Compat
 *
 * Adds theme/plugin compatibility.
 *
 * @since 2.8.0
 */
class Compat extends Core {

	/**
	 * Constructor, load parent constructor
	 */
	protected function __construct() {
		parent::__construct();

		//* Disable Headway SEO.
		\add_filter( 'headway_seo_disabled', '__return_true' );

		//* Jetpack compat.
		\add_action( 'init', array( $this, 'jetpack_compat' ) );

		//* BuddyPress front-end compat.
		\remove_action( 'wp_head', '_bp_maybe_remove_rel_canonical', 8 );
	}

	/**
	 * Requires compatibility files which are needed early or on every page.
	 * Mostly requires premium plugins/themes, so we check actual PHP instances,
	 * rather than common paths. As they can require manual FTP upload.
	 *
	 * @since 2.8.0
	 * @TODO Add transients that will bypass all these checks.
	 *       Careful, recheck on each activation -- and even FTP deletion.
	 */
	protected function load_early_compat_files() {

		if ( ! extension_loaded( 'mbstring' ) ) {
			$this->_include_compat( 'mbstring', 'php' );
		}

		if ( $this->is_theme( 'genesis' ) ) {
			//* Genesis Framework
			$this->_include_compat( 'genesis', 'theme' );
		}

		if ( defined( 'DOMAIN_MAPPING' ) ) {
			if ( $this->detect_plugin( array( 'functions' => array( 'redirect_to_mapped_domain' ) ) ) ) {
				//* Donncha domain mapping.
				$this->_include_compat( 'donncha-dm', 'plugin' );
			} elseif ( $this->detect_plugin( array( 'functions' => array( 'domainmap_launch' ) ) ) ) {
				//* WPMUdev domain mapping.
				$this->_include_compat( 'wpmudev-dm', 'plugin' );
			}
		}

		if ( $this->detect_plugin( array( 'globals' => array( 'polylang' ) ) ) ) {
			//* PolyLang... it includes compat for WPML, but let's see how this works for now.
			$this->_include_compat( 'polylang', 'plugin' );
		} elseif ( $this->detect_plugin( array( 'constants' => array( 'ICL_LANGUAGE_CODE' ) ) ) ) {
			//* WPML
			$this->_include_compat( 'wpml', 'plugin' );
		} elseif ( $this->detect_plugin( array( 'constants' => array( 'QTX_VERSION' ) ) ) ) {
			//* qTranslate X
			$this->_include_compat( 'qtranslatex', 'plugin' );
		}

		if ( $this->detect_plugin( array( 'globals' => array( 'ultimatemember' ) ) ) ) {
			//* Ultimate Member
			$this->_include_compat( 'ultimatemember', 'plugin' );
		}

		if ( $this->detect_plugin( array( 'functions' => array( 'bbpress' ) ) ) ) {
			//* bbPress
			$this->_include_compat( 'bbpress', 'plugin' );
		} elseif ( $this->detect_plugin( array( 'constants' => array( 'WPFORO_BASENAME' ) ) ) ) {
			//* wpForo
			$this->_include_compat( 'wpforo', 'plugin' );
		}
	}

	/**
	 * Includes compatibility files.
	 *
	 * @since 2.8.0
	 * @access private
	 * @staticvar array $included Maintains cache of whether files have been loaded.
	 *
	 * @param string $what The vendor/plugin/theme name for the compatibilty.
	 * @param string $type The compatibility type. Be it 'plugin' or 'theme'.
	 * @return bool True on success, false on failure. Files are expected not to return any values.
	 */
	public function _include_compat( $what, $type = 'plugin' ) {

		static $included = array();

		isset( $included[ $what ][ $type ] )
		or $included[ $what ][ $type ] = (bool) require_once( THE_SEO_FRAMEWORK_DIR_PATH_COMPAT . $type . '-' . $what . '.php' );

		return $included[ $what ][ $type ];
	}

	/**
	 * Adds compatibility with various JetPack modules.
	 *
	 * Recently, JetPack (4.0) made sure this filter doesn't run when The SEO Framework
	 * is active as they've added their own compatibility check towards this plugin.
	 * Let's wait until everyone has updated before removing this.
	 *
	 * @since 2.6.0
	 * @access private
	 */
	public function jetpack_compat() {

		if ( $this->use_og_tags() ) {
			//* Disable Jetpack Publicize's Open Graph.
			\add_filter( 'jetpack_enable_open_graph', '__return_false', 99 );
		}
	}
}
