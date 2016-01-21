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
 * Class AutoDescription_Networkoptions
 *
 * Hold Options for the plugin
 *
 * @since 2.2.2
 *
 * @todo everything.
 */
class AutoDescription_Networkoptions extends AutoDescription_Siteoptions {

    /**
	 * Network Settings array, providing defaults.
	 *
	 * @since 2.2.2
	 *
	 * @var array Holds Site SEO options.
	 */
	protected $default_network_options = array();

	/**
	 * Network Settings field.
	 *
	 * @since 2.2.2.
	 *
	 * @var string Settings field.
	 *
	 * This value is subject to change based on page/class.
	 */
	protected $network_settings_field;

	/**
	 * Hold the Page ID for this class
	 *
	 * @since 2.2.9
	 *
	 * @var string Page ID
	 */
	protected $network_page_id;

	/**
	 * Constructor, load parent constructor
	 */
	public function __construct() {
		parent::__construct();

		/**
		 * Default site settings. Seperated from Author, page or network settings.
		 *
		 * These settings can be overwritten per page or post depending on type and setting.
		 *
		 * @since 2.2.2
		 */
		$this->default_network_options = (array) apply_filters(
		'the_seo_framework_default_network_options',
			array(
			)
		);

		$this->network_settings_field = THE_SEO_FRAMEWORK_NETWORK_OPTIONS;

		//* Set up site settings
	//	add_action( 'admin_init', array( $this, 'register_network_settings' ) );

		// Fetch the page_id
		$this->network_page_id = 'autodescription-network-settings';
	}

	/**
	 * Register the database settings for storage.
	 *
	 * @since 2.2.2
	 */
	public function register_network_settings() {
		//* If this page doesn't store settings, no need to register them
		if ( ! $this->network_settings_field )
			return;

		register_setting( $this->network_settings_field, $this->network_settings_field );
		add_site_option( $this->network_settings_field, $this->default_network_options );

		if ( ! $this->is_menu_page( $this->network_page_id ) )
			return;

		if ( get_site_option( 'reset', $this->network_settings_field ) ) {
			if ( update_site_option( $this->network_settings_field, $this->default_network_options ) )
				$this->admin_redirect( $this->network_page_id, array( 'reset' => 'true' ) );
			else
				$this->admin_redirect( $this->network_page_id, array( 'error' => 'true' ) );
			exit;
		}

	}

    /**
	 * Register meta boxes on the Site SEO Settings page.
	 *
	 * @since 2.2.2
	 *
	 * @see $this->title_metabox()      Callback for Title Settings box.
	 * @todo
	 */
	public function network_metaboxes() {
		add_meta_box( 'autodescription-coming-soon', 'Coming soon!', '__return_empty_string', $this->pagehook, 'main' );
	}

}
