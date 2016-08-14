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

/**
 * Class AutoDescription_Compat
 *
 * Adds theme/plugin compatibility.
 *
 * @since 2.6.0
 */
class AutoDescription_Compat extends AutoDescription_Debug {

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	private function __wakeup() { }

	/**
	 * Handle unapproachable invoked methods.
	 */
	public function __call( $name, $arguments ) {
		parent::__call( $name, $arguments );
	}

	/**
	 * Constructor, load parent constructor
	 */
	public function __construct() {
		parent::__construct();

		//* Genesis compat.
		add_action( 'init', array( $this, 'genesis_compat' ) );
		add_filter( 'genesis_detect_seo_plugins', array( $this, 'no_more_genesis_seo' ), 10 );

		//* Headway compat.
		add_filter( 'headway_seo_disabled', '__return_true' );

		//* Jetpack compat.
		add_action( 'init', array( $this, 'jetpack_compat' ) );

		//* BuddyPress compat.
		add_action( 'init', array( $this, 'buddypress_compat' ) );

	}

	/**
	 * Adds Genesis SEO compatibility.
	 *
	 * @since 2.6.0
	 * @access private
	 */
	public function genesis_compat() {

		//* Nothing to do on admin.
		if ( $this->is_admin() )
			return;

		//* Reverse the removal of head attributes, this shouldn't affect SEO.
		remove_filter( 'genesis_attr_head', 'genesis_attributes_empty_class' );
		add_filter( 'genesis_attr_head', 'genesis_attributes_head' );

	}

	/**
	 * Removes the Genesis SEO meta boxes on the SEO Settings page
	 *
	 * @since 2.2.4
	 * @access private
	 *
	 * @param array $plugins, overwritten as this filter will fire the
	 * detection, regardless of other SEO plugins.
	 * @return array Plugins to detect.
	 */
	public function no_more_genesis_seo( $plugins ) {

		$plugins = array(
				'classes' => array(
					'The_SEO_Framework_Load',
				),
				'functions' => array(),
				'constants' => array(),
			);

		return $plugins;
	}

	/**
	 * Adds compatibility with various JetPack modules.
	 *
	 * Recently, JetPack made sure this filter doesn't run when The SEO Framework
	 * is active as they've added their own compatibility check towards this plugin.
	 * Let's wait until everyone has updated before removing this.
	 *
	 * @since 2.6.0
	 * @access private
	 */
	public function jetpack_compat() {

		if ( $this->use_og_tags() ) {
			//* Disable Jetpack Publicize's Open Graph.
			add_filter( 'jetpack_enable_open_graph', '__return_false', 99 );
		}
	}

	/**
	 * Removes canonical URL from BuddyPress. Regardless of The SEO Framework settings.
	 *
	 * @since 2.7.0
	 * @access private
	 */
	public function buddypress_compat() {

		//* Nothing to do on admin.
		if ( $this->is_admin() )
			return;

		remove_action( 'wp_head', '_bp_maybe_remove_rel_canonical', 8 );

	}
}
