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
	 * @since 2.7.0
	 *
	 * @var string Page hook
	 */
	public $seo_settings_page_hook;

	/**
	 * Load the options.
	 *
	 * @since 2.6.0
	 *
	 * @var bool Load options.
	 */
	public $load_options;

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
	 * Constructor. Loads parent constructor, does actions and sets up variables.
	 */
	public function __construct() {
		parent::__construct();

		/**
		* Applies filters the_seo_framework_load_options : Boolean Allows the options page to be removed
		* @since 2.2.2
		*/
		$this->load_options = (bool) apply_filters( 'the_seo_framework_load_options', true );

		add_action( 'init', array( $this, 'init_admin_actions' ), 0 );
	}

	/**
	 * Initializes Admin Menu actions.
	 *
	 * @since 2.7.0
	 */
	public function init_admin_actions() {

		if ( $this->load_options && $this->is_admin() ) {
			// Enqueue i18n defaults.
			add_action( 'admin_init', array( $this, 'enqueue_page_defaults' ), 1 );

			// Add menu links and register $this->seo_settings_page_hook
			add_action( 'admin_menu', array( $this, 'add_menu_link' ) );

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
	 * Applies filter 'the_seo_framework_admin_page_defaults' : Array
	 * This filter adds i18n support for buttons and notices.
	 *
	 * @since 2.3.1
	 */
	public function enqueue_page_defaults() {

		$this->page_defaults = (array) apply_filters(
			'the_seo_framework_admin_page_defaults',
			array(
				'save_button_text'   => esc_html__( 'Save Settings', 'autodescription' ),
				'reset_button_text'  => esc_html__( 'Reset Settings', 'autodescription' ),
				'saved_notice_text'  => esc_html__( 'Settings are saved.', 'autodescription' ),
				'reset_notice_text'  => esc_html__( 'Settings are reset.', 'autodescription' ),
				'error_notice_text'  => esc_html__( 'Error saving settings.', 'autodescription' ),
				'plugin_update_text' => esc_html__( 'New SEO Settings have been updated.', 'autodescription' ),
			)
		);

	}

	/**
	 * Adds menu links under "settings" in the wp-admin dashboard
	 *
	 * @since 2.2.2
	 *
	 * @return void
	 */
	public function add_menu_link() {

		$menu = array(
			'page_title' => esc_html__( 'SEO Settings', 'autodescription' ),
			'menu_title' => esc_html__( 'SEO', 'autodescription' ),
			'capability' => $this->settings_capability(),
			'menu_slug'  => $this->seo_settings_page_slug,
			'callback'   => array( $this, 'admin' ),
			'icon'       => 'dashicons-search',
			'position'   => '90.9001',
		);

		$this->seo_settings_page_hook = add_menu_page(
			$menu['page_title'],
			$menu['menu_title'],
			$menu['capability'],
			$menu['menu_slug'],
			$menu['callback'],
			$menu['icon'],
			$menu['position']
		);

		/**
		 * Simply copy the previous, but rename the submenu entry.
		 * The function add_submenu_page() takes care of the duplications.
		 */
		add_submenu_page(
			$menu['menu_slug'],
			$menu['page_title'],
			$menu['page_title'],
			$menu['capability'],
			$menu['menu_slug'],
			$menu['callback']
		);

		//* Enqueue styles
		add_action( 'admin_print_styles-' . $this->seo_settings_page_hook, array( $this, 'enqueue_admin_css' ), 11 );

		//* Enqueue scripts
		add_action( 'admin_print_scripts-' . $this->seo_settings_page_hook, array( $this, 'enqueue_admin_javascript' ), 11 );

	}

	/**
	 * Initialize the settings page.
	 *
	 * @since 2.2.2
	 */
	public function settings_init() {

		add_action( $this->seo_settings_page_hook . '_settings_page_boxes', array( $this, 'do_metaboxes' ) );
		add_action( 'load-' . $this->seo_settings_page_hook, array( $this, 'metaboxes' ) );

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
				do_action( 'the_seo_framework_before_siteadmin_metaboxes', $this->seo_settings_page_hook );

				do_meta_boxes( $this->seo_settings_page_hook, 'main', null );

				if ( isset( $wp_meta_boxes[ $this->seo_settings_page_hook ]['main_extra'] ) )
					do_meta_boxes( $this->seo_settings_page_hook, 'main_extra', null );

				do_action( 'the_seo_framework_after_siteadmin_metaboxes', $this->seo_settings_page_hook );
				?>
			</div>
			<div class="postbox-container-2">
				<?php
				do_action( 'the_seo_framework_before_siteadmin_metaboxes_side', $this->seo_settings_page_hook );

				/**
				 * @TODO fill this in
				 * @priority low 2.9.0
				 */

				do_action( 'the_seo_framework_after_siteadmin_metaboxes_side', $this->seo_settings_page_hook );
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Register meta boxes on the Site SEO Settings page.
	 *
	 * @since 2.2.2
	 *
	 * @see $this->title_metabox()			Callback for Title Settings box.
	 * @see $this->description_metabox()	Callback for Description Settings box.
	 * @see $this->robots_metabox()			Callback for Robots Settings box.
	 * @see $this->homepage_metabox()		Callback for Home Page Settings box.
	 * @see $this->social_metabox()			Callback for Social Settings box.
	 * @see $this->knowledge_metabox()		Callback for Knowledge Graph Settings box.
	 * @see $this->schema_metabox()			Callback for Schema Settings box.
	 * @see $this->webmaster_metabox()		Callback for Webmaster Settings box.
	 * @see $this->sitemaps_metabox()		Callback for Sitemap Settings box.
	 * @see $this->feed_metabox()			Callback for Feed Settings box.
	 */
	public function metaboxes() {

		/**
		 * Various metabox filters.
		 * Set any to false if you wish the meta box to be removed.
		 *
		 * @since 2.2.4
		 */
		$title       = (bool) apply_filters( 'the_seo_framework_title_metabox', true );
		$description = (bool) apply_filters( 'the_seo_framework_description_metabox', true );
		$robots      = (bool) apply_filters( 'the_seo_framework_robots_metabox', true );
		$home        = (bool) apply_filters( 'the_seo_framework_home_metabox', true );
		$social      = (bool) apply_filters( 'the_seo_framework_social_metabox', true );
		$knowledge   = (bool) apply_filters( 'the_seo_framework_knowledge_metabox', true );
		$schema      = (bool) apply_filters( 'the_seo_framework_schema_metabox', true );
		$webmaster   = (bool) apply_filters( 'the_seo_framework_webmaster_metabox', true );
		$sitemap     = (bool) apply_filters( 'the_seo_framework_sitemap_metabox', true );
		$feed        = (bool) apply_filters( 'the_seo_framework_feed_metabox', true );

		//* Title Meta Box
		if ( $title )
			add_meta_box(
				'autodescription-title-settings',
				esc_html__( 'Title Settings', 'autodescription' ),
				array( $this, 'title_metabox' ),
				$this->seo_settings_page_hook,
				'main',
				array()
			);

		//* Description Meta Box
		if ( $description )
			add_meta_box(
				'autodescription-description-settings',
				esc_html__( 'Description Meta Settings', 'autodescription' ),
				array( $this, 'description_metabox' ),
				$this->seo_settings_page_hook,
				'main',
				array()
			);

		//* Home Page Meta Box
		if ( $home )
			add_meta_box(
				'autodescription-homepage-settings',
				esc_html__( 'Home Page Settings', 'autodescription' ),
				array( $this, 'homepage_metabox' ),
				$this->seo_settings_page_hook,
				'main',
				array()
			);

		//* Social Meta Box
		if ( $social )
			add_meta_box(
				'autodescription-social-settings',
				esc_html__( 'Social Meta Settings', 'autodescription' ),
				array( $this, 'social_metabox' ),
				$this->seo_settings_page_hook,
				'main',
				array()
			);

		//* Knowledge Graph Meta Box
		if ( $knowledge )
			add_meta_box(
				'autodescription-knowledgegraph-settings',
				esc_html__( 'Knowledge Graph Settings', 'autodescription' ),
				array( $this, 'knowledge_metabox' ),
				$this->seo_settings_page_hook,
				'main',
				array()
			);

		//* Title Meta Box
		if ( $schema )
			add_meta_box(
				'autodescription-schema-settings',
				esc_html__( 'Schema Settings', 'autodescription' ),
				array( $this, 'schema_metabox' ),
				$this->seo_settings_page_hook,
				'main',
				array()
			);

		//* Robots Meta Box
		if ( $robots )
			add_meta_box(
				'autodescription-robots-settings',
				esc_html__( 'Robots Meta Settings', 'autodescription' ),
				array( $this, 'robots_metabox' ),
				$this->seo_settings_page_hook,
				'main',
				array()
			);

		//* Webmaster Meta Box
		if ( $webmaster )
			add_meta_box(
				'autodescription-webmaster-settings',
				esc_html__( 'Webmaster Meta Settings', 'autodescription' ),
				array( $this, 'webmaster_metabox' ),
				$this->seo_settings_page_hook,
				'main',
				array()
			);

		//* Sitemaps Meta Box
		if ( $sitemap )
			add_meta_box(
				'autodescription-sitemap-settings',
				esc_html__( 'Sitemap Settings', 'autodescription' ),
				array( $this, 'sitemaps_metabox' ),
				$this->seo_settings_page_hook,
				'main',
				array()
			);

		//* Feed Meta Box
		if ( $feed )
			add_meta_box(
				'autodescription-feed-settings',
				esc_html__( 'Feed Settings', 'autodescription' ),
				array( $this, 'feed_metabox' ),
				$this->seo_settings_page_hook,
				'main',
				array()
			);

	}

	/**
	 * Use this as the settings admin callback to create an admin page with sortable metaboxes.
	 * Create a 'settings_boxes' method to add metaboxes.
	 *
	 * @since 2.2.2
	 */
	public function admin() {

		?>
		<div class="wrap tsf-metaboxes">
		<form method="post" action="options.php">

			<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
			<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
			<?php settings_fields( $this->settings_field ); ?>

			<div class="tsf-top-wrap">
				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
				<p class="tsf-top-buttons">
					<?php
					submit_button( $this->page_defaults['save_button_text'], 'primary', 'submit', false, array( 'id' => '' ) );
					submit_button( $this->page_defaults['reset_button_text'], 'secondary autodescription-js-confirm-reset', $this->get_field_name( 'reset' ), false, array( 'id' => '' ) );
					?>
				</p>
			</div>

			<?php do_action( "{$this->seo_settings_page_hook}_settings_page_boxes", $this->seo_settings_page_hook ); ?>

			<div class="tsf-bottom-buttons">
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
				postboxes.add_postbox_toggles('<?php echo esc_js( $this->seo_settings_page_hook ); ?>');
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

		if ( false === $this->is_seo_settings_page( true ) )
			return;

		if ( isset( $_REQUEST['settings-updated'] ) && 'true' === $_REQUEST['settings-updated'] )
			$this->do_dismissible_notice( $this->page_defaults['saved_notice_text'], 'updated' );
		elseif ( isset( $_REQUEST['reset'] ) && 'true' === $_REQUEST['reset'] )
			$this->do_dismissible_notice( $this->page_defaults['reset_notice_text'], 'warning' );
		elseif ( isset( $_REQUEST['error'] ) && 'true' === $_REQUEST['error'] )
			$this->do_dismissible_notice( $this->page_defaults['error_notice_text'], 'error' );
		elseif ( isset( $_REQUEST['seo-updated'] ) && 'true' === $_REQUEST['seo-updated'] )
			$this->do_dismissible_notice( $this->page_defaults['plugin_update_text'], 'updated' );

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
	 * @uses $this->get_field_name() Construct name attributes for use in form fields.
	 *
	 * @param string $name Field name base
	 */
	public function field_name( $name ) {
		echo esc_attr( $this->get_field_name( $name ) );
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
	 * @uses $this->get_field_id() Constructs id attributes for use in form fields.
	 *
	 * @param string $id Field id base
	 * @param boolean $echo echo or return
	 * @return string Full field id
	 */
	public function field_id( $id, $echo = true ) {

		if ( $echo ) {
			echo esc_attr( $this->get_field_id( $id ) );
		} else {
			return $this->get_field_id( $id );
		}
	}

	/**
	 * Helper function that returns a setting value from this form's settings
	 * field for use in form fields.
	 * Fetches blog option.
	 *
	 * @since 2.2.2
	 *
	 * @param string $key Field key
	 * @return string Field value
	 */
	public function get_field_value( $key ) {
		return $this->get_option( $key, $this->settings_field );
	}

	/**
	 * Echo a setting value from this form's settings field for use in form fields.
	 *
	 * @since 2.2.2
	 * @uses $this->get_field_value() Constructs value attributes for use in form fields.
	 *
	 * @param string $key Field key
	 */
	public function field_value( $key ) {
		echo esc_attr( $this->get_field_value( $key ) );
	}

	/**
	 * Echo or return a chechbox fields wrapper.
	 *
	 * @since 2.6.0
	 *
	 * @param string $input The input to wrap. Should already be escaped.
	 * @param bool $echo Whether to echo or return.
	 * @return Wrapped $input.
	 */
	public function wrap_fields( $input = '', $echo = false ) {

		if ( is_array( $input ) )
			$input = implode( "\r\n", $input );

		if ( $echo ) {
			echo '<div class="tsf-fields">' . "\r\n" . $input . "\r\n" . '</div>';
		} else {
			return '<div class="tsf-fields">' . "\r\n" . $input . "\r\n" . '</div>';
		}
	}

	/**
	 * Return a chechbox wrapper.
	 *
	 * @since 2.6.0
	 * @since 2.7.0 Added escape parameter. Defaults to true.
	 *
	 * @param string $field_id The option ID. Must be within the Autodescription settings.
	 * @param string $label The checkbox description label.
	 * @param string $description Addition description to place beneath the checkbox.
	 * @param bool $escape Whether to escape the label and description.
	 * @return HTML checkbox output.
	 */
	public function make_checkbox( $field_id = '', $label = '', $description = '', $escape = true ) {

		$description = $escape ? esc_html( $description ) : $description;
		$label = $escape ? esc_html( $label ) : $label;

		$description = $description ? '<p class="description tsf-option-spacer">' . $description . '</p>' : '';

		$output = '<span class="tsf-toblock">'
					. '<label for="' . $this->get_field_id( $field_id ) . '">'
						. '<input '
							. 'type="checkbox" '
							. 'name="' . $this->get_field_name( $field_id ) . '" '
							. 'id="' . $this->get_field_id( $field_id ) . '" '
							. $this->get_is_conditional_checked( $field_id ) . ' '
							. 'value="1" '
							. checked( $this->get_field_value( $field_id ), true, false ) .
						' />'
						. $label
					. '</label>'
				. '</span>'
				. $description
				;

		return $output;
	}

	/**
	 * Return a wrapped question mark.
	 *
	 * @since 2.6.0
	 *
	 * @param string $description The descriptive on-hover title.
	 * @param string $link The non-escaped link.
	 * @param bool $echo Whether to echo or return.
	 * @return HTML checkbox output if $echo is false.
	 */
	public function make_info( $description = '', $link = '', $echo = true ) {

		if ( $link ) {
			$output = '<a href="' . esc_url( $link ) . '" target="_blank" title="' . esc_attr( $description ) . '">[?]</a>';
		} else {
			$output = '<span title="' . esc_attr( $description ) . '">[?]</span>';
		}

		if ( $echo ) {
			//* Already escaped.
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Load script and stylesheet assets via metabox_scripts() methods.
	 *
	 * @since 2.2.2
	 */
	public function load_assets() {
		//* Hook scripts method
		add_action( "load-{$this->seo_settings_page_hook}", array( $this, 'metabox_scripts' ) );
	}

	/**
	 * Includes the necessary sortable metabox scripts.
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

		if ( 1 === $default )
			$class = 'tsf-default-selected';

		if ( $echo ) {
			if ( $wrap ) {
				printf( 'class="%s"', esc_attr( $class ) );
			} else {
				echo esc_attr( $class );
			}
		} else {
			if ( $wrap )
				return sprintf( 'class="%s"', $class );

			return $class;
		}
	}

	/**
	 * Returns the HTML class wrap for warning Checkbox options.
	 *
	 * @since 2.3.4
	 *
	 * @param string $key required The option name which returns boolean.
	 * @param string $setting optional The settings field
	 * @param bool $wrap optional output class="" or just the class name.
	 * @param bool $echo optional echo or return the output.
	 * @return string Empty on echo or The class with an optional wrapper.
	 */
	public function is_warning_checked( $key, $setting = '', $wrap = true, $echo = true ) {

		$class = '';

		$warned = $this->get_warned_settings( $key, $setting );

		if ( 1 === $warned )
			$class = 'tsf-warning-selected';

		if ( $echo ) {
			if ( $wrap ) {
				printf( 'class="%s"', esc_attr( $class ) );
			} else {
				echo esc_attr( $class );
			}
		} else {
			if ( $wrap )
				return sprintf( 'class="%s"', $class );

			return $class;
		}
	}

	/**
	 * Helper function that constructs id attributes for use in form fields.
	 *
	 * @since 2.6.0
	 *
	 * @param string $key The option name which returns boolean.
	 */
	public function get_is_conditional_checked( $key ) {
		return $this->is_conditional_checked( $key, $this->settings_field, true, false );
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
	 *
	 * @return string Empty on echo or The class with an optional wrapper.
	 */
	public function is_conditional_checked( $key, $setting = '', $wrap = true, $echo = true ) {

		$class = '';

		$default = $this->is_default_checked( $key, $setting, false, false );
		$warned = $this->is_warning_checked( $key, $setting, false, false );

		if ( '' !== $default && '' !== $warned ) {
			$class = $default . ' ' . $warned;
		} elseif ( '' !== $default ) {
			$class = $default;
		} elseif ( '' !== $warned ) {
			$class = $warned;
		}

		if ( $echo ) {
			if ( $wrap ) {
				printf( 'class="%s"', esc_attr( $class ) );
			} else {
				echo esc_attr( $class );
			}
		} else {
			if ( $wrap )
				return sprintf( 'class="%s"', $class );

			return $class;
		}
	}

	/**
	 * Returns the HTML class wrap for default radio options.
	 *
	 * @since 2.2.5
	 *
	 * @TODO use this
	 * @priority low 2.8.0+
	 *
	 * @param string $key required The option name which returns boolean.
	 * @param string $value required The option value which returns boolean.
	 * @param string $setting optional The settings field
	 * @param bool $wrap optional output class="" or just the class name.
	 * @param bool $echo optional echo or return the output.
	 * @return string|null the default selected class.
	 */
	public function is_default_radio( $key, $value, $setting = '', $wrap = true, $echo = true ) {

		$class = '';

		$default = $this->get_default_settings( $key, $setting );

		if ( $value === $default )
			$class = 'tsf-default-selected';

		if ( $echo ) {
			if ( $wrap ) {
				echo sprintf( 'class="%s"', esc_attr( $class ) );
			} else {
				echo esc_attr( $class );
			}
		} else {
			if ( $wrap )
				return sprintf( 'class="%s"', $class );

			return $class;
		}
	}
}
