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
 * Class AutoDescription_Siteoptions
 *
 * Renders admin pages content for AutoDescription.
 *
 * @since 2.2.2
 */
class AutoDescription_Adminpages extends AutoDescription_Inpost {

	/**
	 * Page Defaults.
	 *
	 * @since 2.2.2
	 *
	 * @var array Holds Page output defaults.
	 */
	public $page_defaults = array();

	/**
	 * Name of the page hook when the menu is registered.
	 *
	 * @since 2.2.2
	 *
	 * @var string Page hook
	 */
	public $pagehook;

	/**
	 * Name of the network page hook when the menu is registered.
	 *
	 * @since 2.2.2
	 *
	 * @var string Page hook
	 */
	public $network_pagehook;

	/**
	 * Constructor, load parent constructor
	 *
	 * Cache various variables
	 *
	 * @applies filters the_seo_framework_load_options : Allows the options page to be removed
	 */
	public function __construct() {
		parent::__construct();

		/**
		 * New filter.
		 * @since 2.3.0
		 *
		 * Removed previous filter.
		 * @since 2.3.5
		 */
		$load_options = (bool) apply_filters( 'the_seo_framework_load_options', true );

		if ( $load_options ) {

			add_action( 'admin_init', array( $this, 'enqueue_page_defaults' ), 1 );

			// Add menu links and register $this->pagehook
			add_action( 'admin_menu', array( $this, 'add_menu_link' ) );

			/**
			 * Add specific Multisite options
			 * @TODO
			 */
			// if ( is_multisite() ) add_action( 'network_admin_menu', array( $this, 'add_network_menu_link' ) );

			//* Load the page content
			add_action( 'admin_init', array( $this, 'settings_init' ) );

			// Set up notices
			add_action( 'admin_notices', array( $this, 'notices' ) );

			// Load nessecary assets
			add_action( 'admin_init', array( $this, 'load_assets' ) );

		}
	}

	/**
	 * Enqueue page defaults early.
	 *
	 * Applies filter `the_seo_framework_admin_page_defaults`.
	 * This filter adds i18n support for buttons and notices.
	 *
	 * @since 2.3.1
	 * @return void
	 */
	public function enqueue_page_defaults() {

		/**
		 * New filter.
		 * @since 2.3.0
		 *
		 * Removed previous filter.
		 * @since 2.3.5
		 */
		$this->page_defaults = (array) apply_filters(
			'the_seo_framework_admin_page_defaults',
			array(
				'save_button_text'  => __( 'Save Settings', 'autodescription' ),
				'reset_button_text' => __( 'Reset Settings', 'autodescription' ),
				'saved_notice_text' => __( 'Settings saved.', 'autodescription' ),
				'reset_notice_text' => __( 'Settings reset.', 'autodescription' ),
				'error_notice_text' => __( 'Error saving settings.', 'autodescription' ),
			)
		);

	}

	/**
	 * Adds menu links under "settings" in the wp-admin dashboard
	 *
	 * Applies filter `the_seo_framework_settings_capability`.
	 * This filter changes the minimum role for viewing and editing the plugin's settings.
	 *
	 * @since 2.2.2
	 * @return void
	 */
	public function add_menu_link() {

		$menu = array(
			'pagetitle'		=> __( 'SEO Settings', 'autodescription' ),
			'menutitle'		=> __( 'SEO', 'autodescription' ),

			/**
			 * New filter.
			 * @since 2.3.0
			 *
			 * Removed previous filter.
			 * @since 2.3.5
			 */
			'capability'	=> (string) apply_filters( 'the_seo_framework_settings_capability', 'manage_options' ),

			'menu_slug'		=> 'autodescription-settings',
			'callback'		=> array( $this, 'admin' ),
			'icon'			=> 'dashicons-search',
			'position'		=> '90.9001',
		);

		$this->pagehook = add_menu_page(
			$menu['pagetitle'],
			$menu['menutitle'],
			$menu['capability'],
			$menu['menu_slug'],
			$menu['callback'],
			$menu['icon'],
			$menu['position']
		);

		// Enqueue styles
		// Doesn't pass the $hook argument
		add_action( 'admin_print_styles-' . $this->pagehook, array( $this, 'enqueue_admin_css' ), 11 );

		// Enqueue scripts
		// Doesn't pass the $hook argument
		add_action( 'admin_print_scripts-' . $this->pagehook, array( $this, 'enqueue_admin_javascript' ), 11 );
	}

	/**
	 * Adds menu links under "settings" in the wp-admin dashboard
	 *
	 * Applies `autodescription_settings_capability` filters.
	 * This filter changes the minimum role for viewing and editing the plugin's settings.
	 *
	 * @since 2.2.2
	 * @return void
	 *
	 * @TODO Everything.
	 */
	public function add_network_menu_link() {

		$menu = array(
			'pagetitle'		=> __( 'Network SEO Settings', 'autodescription' ),
			'menutitle'		=> __( 'Network SEO', 'autodescription' ),

			'capability'	=> 'manage_network',

			'menu_slug'		=> 'autodescription-network-settings',
			'callback'		=> array( $this, 'network_admin' ),
			'icon'			=> 'dashicons-search',
			'position'		=> '99.9001',
		);

		$this->network_pagehook = add_menu_page(
			$menu['pagetitle'],
			$menu['menutitle'],
			$menu['capability'],
			$menu['menu_slug'],
			$menu['callback'],
			$menu['icon'],
			$menu['position']
		);

		// Enqueue styles
		add_action( 'admin_print_styles-' . $this->network_pagehook, array( $this, 'enqueue_admin_css' ), 11 );

		// Enqueue scripts
		add_action( 'admin_print_scripts-' . $this->network_pagehook, array( $this, 'enqueue_admin_javascript' ), 11 );
	}

	/**
	 * Initialize the settings page.
	 *
	 * @since 2.2.2
	 */
	public function settings_init() {

		add_action( $this->pagehook . '_settings_page_boxes', array( $this, 'do_metaboxes' ) );
		add_action( 'load-' . $this->pagehook, array( $this, 'metaboxes' ) );

	}

	/**
	 * Echo out the do_metaboxes() and wrapping markup.
	 *
	 * @since 2.2.2
	 *
	 * @global array $wp_meta_boxes Holds all metaboxes data.
	 */
	public function do_metaboxes() {
		global $wp_meta_boxes;

		?>
		<div class="metabox-holder columns-2">
			<div class="postbox-container-1">
				<?php
				//* @since 2.3.0 action.
				do_action( 'the_seo_framework_before_siteadmin_metaboxes', $this->pagehook );

				do_meta_boxes( $this->pagehook, 'main', null );

				if ( isset( $wp_meta_boxes[$this->pagehook]['main_extra'] ) )
					do_meta_boxes( $this->pagehook, 'main_extra', null );

				//* @since 2.3.0 action.
				do_action( 'the_seo_framework_after_siteadmin_metaboxes', $this->pagehook );
				?>
			</div>
			<div class="postbox-container-2">
				<?php
				//* @since 2.3.0 action.
				do_action( 'the_seo_framework_before_siteadmin_metaboxes_side', $this->pagehook );

				// @TODO fill this in

				//* @since 2.3.0 action.
				do_action( 'the_seo_framework_after_siteadmin_metaboxes_side', $this->pagehook );
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Use this as the settings admin callback to create an admin page with sortable metaboxes.
	 * Create a 'settings_boxes' method to add metaboxes.
	 *
	 * @since 2.2.2
	 */
	public function admin() {
		?>
		<div class="wrap autodescription-metaboxes">
		<form method="post" action="options.php">

			<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
			<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
			<?php settings_fields( $this->settings_field ); ?>

			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<p class="top-buttons">
				<?php
				submit_button( $this->page_defaults['save_button_text'], 'primary', 'submit', false, array( 'id' => '' ) );
				submit_button( $this->page_defaults['reset_button_text'], 'secondary autodescription-js-confirm-reset', $this->get_field_name( 'reset' ), false, array( 'id' => '' ) );
				?>
			</p>

			<?php do_action( "{$this->pagehook}_settings_page_boxes", $this->pagehook ); ?>

			<div class="bottom-buttons">
				<?php
				submit_button( $this->page_defaults['save_button_text'], 'primary', 'submit', false, array( 'id' => '' ) );
				submit_button( $this->page_defaults['reset_button_text'], 'secondary autodescription-js-confirm-reset', $this->get_field_name( 'reset' ), false, array( 'id' => '' ) );
				?>
			</div>
		</form>
		</div>
		<?php // Add postbox listeners ?>
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready( function ($) {
				// close postboxes that should be closed
				$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
				// postboxes setup
				postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
			});
			//]]>
		</script>
		<?php
	}

	/**
	 * Use this as the settings admin callback to create an admin page with sortable metaboxes.
	 * Create a 'settings_boxes' method to add metaboxes.
	 *
	 * @since 2.2.2
	 */
	public function network_admin() {
		?>
		<div class="wrap autodescription-metaboxes">
		<form method="post" action="options.php">

			<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
			<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
			<?php settings_fields( $this->network_settings_field ); ?>

			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<p class="top-buttons">
				<?php
				submit_button( $this->page_defaults['save_button_text'], 'primary', 'submit', false, array( 'id' => '' ) );
				submit_button( $this->page_defaults['reset_button_text'], 'secondary autodescription-js-confirm-reset', $this->get_field_name( 'reset' ), false, array( 'id' => '' ) );
				?>
			</p>

			<?php do_action( "{$this->network_pagehook}_settings_page_boxes", $this->network_pagehook ); ?>

			<div class="bottom-buttons">
				<?php
				submit_button( $this->page_defaults['save_button_text'], 'primary', 'submit', false, array( 'id' => '' ) );
				submit_button( $this->page_defaults['reset_button_text'], 'secondary autodescription-js-confirm-reset', $this->get_field_name( 'reset' ), false, array( 'id' => '' ) );
				?>
			</div>
		</form>
		</div>
		<?php // Add postbox listeners ?>
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready( function ($) {
				// close postboxes that should be closed
				$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
				// postboxes setup
				postboxes.add_postbox_toggles('<?php echo $this->network_pagehook; ?>');
			});
			//]]>
		</script>
		<?php
	}

	/**
	 * Display notices on the save or reset of settings.
	 *
	 * @since 2.2.2
	 *
	 * @return void
	 */
	public function notices() {

		if ( ! $this->is_menu_page( $this->pagehook ) )
			return;

		if ( isset( $_REQUEST['settings-updated'] ) && 'true' === $_REQUEST['settings-updated'] )
			echo '<div id="message" class="updated"><p><strong>' . $this->page_defaults['saved_notice_text'] . '</strong></p></div>';
		else if ( isset( $_REQUEST['reset'] ) && 'true' === $_REQUEST['reset'] )
			echo '<div id="message" class="notice notice-warning"><p><strong>' . $this->page_defaults['reset_notice_text'] . '</strong></p></div>';
		else if ( isset( $_REQUEST['error'] ) && 'true' === $_REQUEST['error'] )
			echo '<div id="message" class="error"><p><strong>' . $this->page_defaults['error_notice_text'] . '</strong></p></div>';

	}

	/**
	 * Register meta boxes on the Site SEO Settings page.
	 *
	 * @since 2.2.2
	 *
	 * @see $this->title_metabox()		Callback for Title Settings box.
	 * @see $this->robots_metabox()		Callback for Robots Settings box.
	 * @see $this->homepage_metabox()	Callback for Home Page Settings box.
	 * @see $this->social_metabox()		Callback for Social Settings box.
	 * @see $this->webmaster_metabox()	Callback for Webmaster Settings box.
	 */
	public function metaboxes() {

		/**
		 * Various metabox filters.
		 * Set any to false if you wish the meta box to be removed.
		 *
		 * @since 2.2.4
		 *
		 * New filters.
		 * @since 2.3.0
		 *
		 * Removed previous filters.
		 * @since 2.3.5
		 */
		$title 			= (bool) apply_filters( 'the_seo_framework_title_metabox', true );
		$description 	= (bool) apply_filters( 'the_seo_framework_description_metabox', true );
		$robots 		= (bool) apply_filters( 'the_seo_framework_robots_metabox', true );
		$home 			= (bool) apply_filters( 'the_seo_framework_home_metabox', true );
		$social 		= (bool) apply_filters( 'the_seo_framework_social_metabox', true );
		$knowledge 		= (bool) apply_filters( 'the_seo_framework_knowledge_metabox', true );
		$webmaster 		= (bool) apply_filters( 'the_seo_framework_webmaster_metabox', true );
		$sitemap 		= (bool) apply_filters( 'the_seo_framework_sitemap_metabox', true );

		//* Title Meta Box
		if ( $title )
			add_meta_box(
				'autodescription-title-settings',
				__( 'Title Settings', 'autodescription' ),
				array( $this, 'title_metabox' ),
				$this->pagehook,
				'main'
			);

		//* Description Meta Box
		if ( $description )
			add_meta_box(
				'autodescription-description-settings',
				__( 'Description Meta Settings', 'autodescription' ),
				array( $this, 'description_metabox' ),
				$this->pagehook,
				'main'
			);

		//* Home Page Meta Box
		if ( $home )
			add_meta_box(
				'autodescription-homepage-settings',
				__( 'Home Page Settings', 'autodescription' ),
				array( $this, 'homepage_metabox' ),
				$this->pagehook,
				'main'
			);

		//* Social Meta Box
		if ( $social )
			add_meta_box(
				'autodescription-social-settings',
				__( 'Social Meta Settings', 'autodescription' ),
				array( $this, 'social_metabox' ),
				$this->pagehook,
				'main'
			);

		//* Knowledge Graph Meta Box
		if ( $knowledge )
			add_meta_box(
				'autodescription-knowledgegraph-settings',
				__( 'Knowledge Graph Settings', 'autodescription' ),
				array( $this, 'knowledge_metabox' ),
				$this->pagehook,
				'main'
			);

		//* Robots Meta Box
		if ( $robots )
			add_meta_box(
				'autodescription-robots-settings',
				__( 'Robots Meta Settings', 'autodescription' ),
				array( $this, 'robots_metabox' ),
				$this->pagehook,
				'main'
			);

		//* Webmaster Meta Box
		if ( $webmaster )
			add_meta_box(
				'autodescription-webmaster-settings',
				__( 'Webmaster Meta Settings', 'autodescription' ),
				array( $this, 'webmaster_metabox' ),
				$this->pagehook,
				'main'
			);

		//* Sitemaps Meta Box
		if ( $sitemap )
			add_meta_box(
				'autodescription-sitemap-settings',
				__( 'Sitemaps Settings', 'autodescription' ),
				array( $this, 'sitemaps_metabox' ),
				$this->pagehook,
				'main'
			);

	}

	/**
	 * Return option from the options table and cache result.
	 *
	 * Applies `the_seo_framework_get_options` filters.
	 * This filter retrieves the (previous) values from Genesis if exists.
	 *
	 * Values pulled from the database are cached on each request, so a second request for the same value won't cause a
	 * second DB interaction.
	 * @staticvar array $settings_cache
	 * @staticvar array $options_cache
	 *
	 * @since 2.0.0
	 *
	 * @param string  $key        Option name.
	 * @param string  $setting    Optional. Settings field name. Eventually defaults to null if not passed as an argument.
	 * @param boolean $use_cache  Optional. Whether to use the cache value or not. Default is true.
	 *
	 * @return mixed The value of this $key in the database.
	 *
	 * @thanks StudioPress (http://www.studiopress.com/) for some code.
	 */
	public function the_seo_framework_get_option( $key, $setting = null, $use_cache = true ) {

		//* If we need to bypass the cache
		if ( ! $use_cache ) {
			$options = get_option( $setting );

			if ( ! is_array( $options ) || ! array_key_exists( $key, $options ) )
				return '';

			return is_array( $options[$key] ) ? stripslashes_deep( $options[$key] ) : stripslashes( wp_kses_decode_entities( $options[$key] ) );
		}

		//* Setup caches
		static $settings_cache = array();
		static $options_cache  = array();

		//* Check options cache
		if ( isset( $options_cache[$setting][$key] ) )
			//* Option has been cached
			return $options_cache[$setting][$key];

		//* Check settings cache
		if ( isset( $settings_cache[$setting] ) ) {
			//* Setting has been cached

			/**
			 * New filter.
			 * @since 2.3.0
			 *
			 * Removed previous filter.
			 * @since 2.3.5
			 */
			$options = apply_filters( 'the_seo_framework_get_options', $settings_cache[$setting], $setting );
		} else {
			//* Set value and cache setting

			/**
			 * New filter.
			 * @since 2.3.0
			 *
			 * Removed previous filter.
			 * @since 2.3.5
			 */
			$options = $settings_cache[$setting] = apply_filters( 'the_seo_framework_get_options', get_option( $setting ), $setting );
		}

		//* Check for non-existent option
		if ( ! is_array( $options ) || ! array_key_exists( $key, (array) $options ) ) {
			//* Cache non-existent option
			$options_cache[$setting][$key] = '';
		} else {
			//* Option has not been previously been cached, so cache now
			$options_cache[$setting][$key] = is_array( $options[$key] ) ? stripslashes_deep( $options[$key] ) : stripslashes( wp_kses_decode_entities( $options[$key] ) );
		}

		return $options_cache[$setting][$key];
	}

	/**
	 * Helper function that constructs name attributes for use in form fields.
	 *
	 * Other page implementation classes may wish to construct and use a
	 * get_field_id() method, if the naming format needs to be different.
	 *
	 * @since 2.2.2
	 *
	 * @param string $name Field name base
	 * @return string Full field name
	 */
	public function get_field_name( $name ) {
		return sprintf( '%s[%s]', $this->settings_field, $name );
	}

	/**
	 * Echo constructed name attributes in form fields.
	 *
	 * @since 2.2.2
	 *
	 * @uses $this->get_field_name() Construct name attributes for use in form fields.
	 *
	 * @param string $name Field name base
	 */
	public function field_name( $name ) {
		echo $this->get_field_name( $name );
	}

	/**
	 * Helper function that constructs id attributes for use in form fields.
	 *
	 * @since 2.2.2
	 *
	 * @param string $id Field id base
	 * @return string Full field id
	 */
	public function get_field_id( $id ) {
		return sprintf( '%s[%s]', $this->settings_field, $id );
	}

	/**
	 * Echo constructed id attributes in form fields.
	 *
	 * @since 2.2.2
	 *
	 * @uses $this->get_field_id() Constructs id attributes for use in form fields.
	 *
	 * @param string $id Field id base
	 * @param boolean $echo echo or return
	 * @return string Full field id
	 */
	public function field_id( $id, $echo = true ) {
		if ( $echo ) {
			echo $this->get_field_id( $id );
		} else {
			return $this->get_field_id( $id );
		}
	}

	/**
	 * Helper function that returns a setting value from this form's settings
	 * field for use in form fields.
	 *
	 * Fetches blog option.
	 *
	 * @since 2.2.2
	 *
	 * @param string $key Field key
	 * @return string Field value
	 */
	public function get_field_value( $key ) {
		$option = $this->get_option( $key, $this->settings_field );

		return $option;
	}

	/**
	 * Helper function that returns a setting value from this form's settings
	 * field for use in form fields.
	 *
	 * Fetches network option.
	 *
	 * @since 2.2.2
	 *
	 * @param string $key Field key
	 * @return string Field value
	 */
	public function get_field_value_network( $key ) {
		$option = $this->get_site_option( $key, $this->settings_field );

		return $option;
	}

	/**
	 * Echo a setting value from this form's settings field for use in form fields.
	 *
	 * @uses $this->get_field_value() Constructs value attributes for use in form fields.
	 *
	 * @since 2.2.2
	 *
	 * @param string $key Field key
	 */
	public function field_value( $key ) {
		echo $this->get_field_value( $key );
	}

	/**
	 * Load script and stylesheet assets via scripts() methods.
	 *
	 * @since 2.2.2
	 */
	public function load_assets() {
		//* Hook scripts method
		add_action( "load-{$this->pagehook}", array( $this, 'metabox_scripts' ) );
	}

	/**
	 * Include the necessary sortable metabox scripts.
	 *
	 * @since 2.2.2
	 */
	public function metabox_scripts() {
		wp_enqueue_script( 'common' );
		wp_enqueue_script( 'wp-lists' );
		wp_enqueue_script( 'postbox' );
	}

	/**
	 * Returns the HTML class wrap for default Checkbox options.
	 *
	 * This function does nothing special. But is merely a simple wrapper.
	 * Just like code_wrap.
	 *
	 * @param string $key required The option name which returns boolean.
	 * @param string $setting optional The settings field
	 * @param bool $wrap optional output class="" or just the class name.
	 * @param bool $echo optional echo or return the output.
	 *
	 * @since 2.2.5
	 */
	public function is_default_checked( $key, $setting = '', $wrap = true, $echo = true ) {

		$class = '';

		$default = $this->get_default_settings( $key, $setting );

		if ( ! is_string( $default ) && $default != -1 && $default )
			$class = 'seoframework-default-selected';

		if ( $echo ) {
			if ( $wrap ) {
				echo sprintf( 'class="%s"', $class );
			} else {
				echo $class;
			}
		} else {
			if ( $wrap )
				return sprintf( 'class="%s"', $class );

			return $class;
		}

		return '';
	}

	/**
	 * Returns the HTML class wrap for warning Checkbox options.
	 *
	 * This function does nothing special. But is merely a simple wrapper.
	 * Just like code_wrap.
	 *
	 * @param string $key required The option name which returns boolean.
	 * @param string $setting optional The settings field
	 * @param bool $wrap optional output class="" or just the class name.
	 * @param bool $echo optional echo or return the output.
	 *
	 * @since 2.3.4
	 */
	public function is_warning_checked( $key, $setting = '', $wrap = true, $echo = true ) {

		$class = '';

		$warned = $this->get_warned_settings( $key, $setting );

		if ( $warned )
			$class = 'seoframework-warning-selected';

		if ( $echo ) {
			if ( $wrap ) {
				echo sprintf( 'class="%s"', $class );
			} else {
				echo $class;
			}
		} else {
			if ( $wrap )
				return sprintf( 'class="%s"', $class );

			return $class;
		}

		return '';
	}

	/**
	 * Returns the HTML class wrap for warning/default Checkbox options.
	 *
	 * This function does nothing special. But is merely a simple wrapper.
	 * Just like code_wrap.
	 *
	 * @param string $key required The option name which returns boolean.
	 * @param string $setting optional The settings field
	 * @param bool $wrap optional output class="" or just the class name.
	 * @param bool $echo optional echo or return the output.
	 *
	 * @since 2.3.4
	 */
	public function is_conditional_checked( $key, $setting = '', $wrap = true, $echo = true ) {

		$class = '';

		$default = $this->is_default_checked( $key, $setting, false, false );
		$warned = $this->is_warning_checked( $key, $setting, false, false );

		if ( ! empty( $default ) && ! empty( $warned ) ) {
			$class = $default . ' ' . $warned;
		} else if ( ! empty( $default ) ) {
			$class = $default;
		} else if ( ! empty( $warned ) ) {
			$class = $warned;
		}

		if ( $echo ) {
			if ( $wrap ) {
				echo sprintf( 'class="%s"', $class );
			} else {
				echo $class;
			}
		} else {
			if ( $wrap ) {
				return sprintf( 'class="%s"', $class );
			} else {
				return $class;
			}
		}
	}

	/**
	 * Returns the HTML class wrap for default radio options.
	 *
	 * @param string $key required The option name which returns boolean.
	 * @param string $value required The option value which returns boolean.
	 * @param string $setting optional The settings field
	 * @param bool $wrap optional output class="" or just the class name.
	 * @param bool $echo optional echo or return the output.
	 *
	 * @since 2.2.5
	 *
	 * @return string|null the default selected class.
	 */
	public function is_default_radio( $key, $value, $setting = '', $wrap = true, $echo = true ) {

		$class = '';

		$default = $this->get_default_settings( $key, $setting );

		if ( $default && $default === $value )
			$class = 'seoframework-default-selected';

		if ( $echo ) {
			if ( $wrap ) {
				echo sprintf( 'class="%s"', $class );
			} else {
				echo $class;
			}
		} else {
			if ( $wrap )
				return sprintf( 'class="%s"', $class );

			return $class;
		}
	}

}
