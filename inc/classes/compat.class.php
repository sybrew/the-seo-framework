<?php
/**
 * @package The_SEO_Framework\Classes
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) or die;

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

		//* Disable Genesis SEO.
		add_filter( 'genesis_detect_seo_plugins', array( $this, 'disable_genesis_seo' ), 10, 1 );

		//* Disable Headway SEO.
		add_filter( 'headway_seo_disabled', '__return_true' );
	}

	/**
	 * Adds Genesis SEO compatibility.
	 *
	 * @since 2.6.0
	 * @access private
	 */
	public function genesis_compat() {
		//* Reverse the removal of head attributes, this shouldn't affect SEO.
		remove_filter( 'genesis_attr_head', 'genesis_attributes_empty_class' );
		add_filter( 'genesis_attr_head', 'genesis_attributes_head' );
	}

	/**
	 * Removes the Genesis SEO meta boxes on the SEO Settings page
	 *
	 * @since 2.8.0
	 * @access private
	 *
	 * @param array $plugins, overwritten as this filter will fire the
	 * detection, regardless of other SEO plugins.
	 * @return array Plugins to detect.
	 */
	public function disable_genesis_seo( $plugins ) {

		$plugins = array(
				'classes' => array(
					'The_SEO_Framework\\Load',
				),
				'functions' => array(
					'the_seo_framework',
				),
				'constants' => array(
					'THE_SEO_FRAMEWORK_VERSION',
				),
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
		remove_action( 'wp_head', '_bp_maybe_remove_rel_canonical', 8 );
	}
}
