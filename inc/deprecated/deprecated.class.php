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
 * Deprecation class.
 * Contains all deprecated functions. Is autoloaded.
 *
 * @since 2.3.4
 */
class The_SEO_Framework_Deprecated extends AutoDescription_Feed {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Return option from the options table and cache result.
	 *
	 * @since 2.0.0
	 *
	 * @deprecated
	 * @since 2.3.4
	 */
	public function autodescription_get_option( $key, $setting = null, $use_cache = true ) {
		_deprecated_function( 'AutoDescription_Adminpages::' . __FUNCTION__, $this->the_seo_framework_version( '2.3.4' ), 'AutoDescription_Adminpages::the_seo_framework_get_option' );

		return the_seo_framework_get_option( $key, $setting, $use_cache );
	}

	/**
	 * Enqueues JS in the admin footer
	 *
	 * @since 2.1.9
	 *
	 * @deprecated
	 * @since 2.3.3
	 *
	 * @param $hook the current page
	 */
	public function enqueue_javascript( $hook ) {
		_deprecated_function( 'AutoDescription_Admin_Init::' . __FUNCTION__, $this->the_seo_framework_version( '2.3.3' ), 'AutoDescription_Admin_Init::enqueue_admin_scripts' );

		return $this->enqueue_admin_scripts( $hook );
	}

	/**
	 * Enqueues CSS in the admin header
	 *
	 * @since 2.1.9
	 *
	 * @deprecated
	 * @since 2.3.3
	 *
	 * @param $hook the current page
	 */
	public function enqueue_css( $hook ) {
		_deprecated_function( 'AutoDescription_Admin_Init::' . __FUNCTION__, $this->the_seo_framework_version( '2.3.3' ), 'AutoDescription_Admin_Init::enqueue_admin_scripts' );

		return $this->enqueue_admin_scripts( $hook );
	}

	/**
	 * Setup var for sitemap transient.
	 *
	 * @since 2.2.9
	 *
	 * @deprecated
	 * @since 2.3.3
	 */
	public function fetch_sitemap_transient_name() {
		_deprecated_function( 'AutoDescription_Transients::' . __FUNCTION__, $this->the_seo_framework_version( '2.3.3' ), 'Completely removed. Use AutoDescription_Transients::$sitemap_transient' );

		return $this->sitemap_transient;
	}

	/**
	 * Delete Sitemap transient on post save.
	 *
	 * @since 2.2.9
	 *
	 * @deprecated
	 * @since 2.3.3
	 */
	public function delete_sitemap_transient_post( $post_id ) {
		_deprecated_function( 'AutoDescription_Transients::' . __FUNCTION__, $this->the_seo_framework_version( '2.3.3' ), 'AutoDescription_Transients::delete_sitemap_transient_post' );

		return $this->delete_transients_post( $post_id );
	}

	/**
	 * Helper function for Doing it Wrong
	 *
	 * @since 2.2.4
	 *
	 * @deprecated
	 * @since 2.3.0
	 */
	public function autodescription_version( $version = '' ) {
		//* Wow, a deprecation that deprecates using itself. :D
		_deprecated_function( 'The_SEO_Framework_Load::' . __FUNCTION__, $this->the_seo_framework_version( '2.3.0' ), 'The_SEO_Framework_Load::the_seo_framework_version' );

		return $this->the_seo_framework_version( $version );
	}

	/**
	 * Include the necessary sortable metabox scripts.
	 *
	 * @since 2.2.2
	 *
	 * @deprecated
	 * @since 2.3.5
	 */
	public function scripts() {
		_deprecated_function( 'AutoDescription_Adminpages::' . __FUNCTION__, $this->the_seo_framework_version( '2.3.5' ), 'AutoDescription_Adminpages::metabox_scripts' );

		return $this->metabox_scripts();
	}

	/**
	 * Setup var for sitemap transient on init/admin_init.
	 *
	 * @since 2.3.3
	 * @deprecated
	 * @since 2.3.3
	 * Oops.
	 */
	public function setup_transient_names_init() {
		_deprecated_function( 'AutoDescription_Transients::' . __FUNCTION__, $this->the_seo_framework_version( '2.3.3' ), 'AutoDescription_Transients::setup_transient_names' );

		$this->setup_transient_names();
		return false;
	}

	/**
	 * Helper function for allowed post/page screens where this plugin is active.
	 *
	 * @param array $screens The allowed screens
	 *
	 * @since 2.1.9
	 *
	 * Applies filters the_seo_framework_supported_screens : The supported administration
	 * screens where css and javascript files are loaded.
	 *
	 * @param array $args the custom supported screens.
	 *
	 * Added WooCommerce edit-product screens.
	 * @since 2.3.1
	 *
	 * Unused.
	 * @since 2.3.5
	 *
	 * @deprecated
	 * @since 2.5.2
	 *
	 * @return array $screens
	 */
	protected function supported_screens( $args = array() ) {

		_deprecated_function( 'AutoDescription_Admin_Init::' . __FUNCTION__, $this->the_seo_framework_version( '2.5.2' ) );

		/**
		 * Instead of supporting page ID's, we support the Page base now.
		 *
		 * @since 2.3.3
		 */
		$defaults = array(
			'edit',
			'post',
			'edit-tags',
		);

		$screens = (array) apply_filters( 'the_seo_framework_supported_screens', $defaults, $args );
		$screens = wp_parse_args( $args, $screens );

		return $screens;
	}

	/**
	 * Add doing it wrong html code in the footer.
	 *
	 * @since 2.2.5
	 *
	 * @deprecated
	 * @since 2.5.2.1
	 */
	public function title_doing_it_wrong() {

		_deprecated_function( 'AutoDescription_Detect::' . __FUNCTION__, $this->the_seo_framework_version( '2.5.2.1' ) );

		return;
	}

}
