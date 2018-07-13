<?php
/**
 * @package The_SEO_Framework\Classes
 */
namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2018 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\Site_Options
 *
 * Renders admin pages content for this plugin.
 *
 * @since 2.8.0
 */
class Admin_Pages extends Inpost {

	/**
	 * Page Defaults.
	 *
	 * @since 2.2.2
	 *
	 * @var array Holds Page output defaults.
	 */
	public $page_defaults = [];

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
	 * Constructor. Loads parent constructor, does actions and sets up variables.
	 */
	protected function __construct() {
		parent::__construct();
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

		$this->page_defaults = (array) \apply_filters(
			'the_seo_framework_admin_page_defaults',
			[
				'save_button_text'   => \esc_html__( 'Save Settings', 'autodescription' ),
				'reset_button_text'  => \esc_html__( 'Reset Settings', 'autodescription' ),
				'saved_notice_text'  => \esc_html__( 'Settings are saved.', 'autodescription' ),
				'reset_notice_text'  => \esc_html__( 'Settings are reset.', 'autodescription' ),
				'error_notice_text'  => \esc_html__( 'Error saving settings.', 'autodescription' ),
				'plugin_update_text' => \esc_html__( 'New SEO Settings have been updated.', 'autodescription' ),
			]
		);
	}

	/**
	 * Adds menu links under "settings" in the wp-admin dashboard
	 *
	 * @since 2.2.2
	 * @since 2.9.2 Added static cache so the method can only run once.
	 * @staticvar bool $run True if already run.
	 *
	 * @return void Early if method is already called.
	 */
	public function add_menu_link() {

		static $run = false;

		if ( $run )
			return;

		$menu = [
			'page_title' => \esc_html__( 'SEO Settings', 'autodescription' ),
			'menu_title' => \esc_html__( 'SEO', 'autodescription' ),
			'capability' => $this->get_settings_capability(),
			'menu_slug'  => $this->seo_settings_page_slug,
			'callback'   => [ $this, '_output_seo_settings_wrap' ],
			'icon'       => 'dashicons-search',
			'position'   => '90.9001',
		];

		$this->seo_settings_page_hook = \add_menu_page(
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
		\add_submenu_page(
			$menu['menu_slug'],
			$menu['page_title'],
			$menu['page_title'],
			$menu['capability'],
			$menu['menu_slug'],
			$menu['callback']
		);

		//* Enqueue scripts
		\add_action( 'admin_print_scripts-' . $this->seo_settings_page_hook, [ $this, '_init_admin_scripts' ], 11 );

		$run = true;
	}

	/**
	 * Initialize the settings page.
	 *
	 * @since 2.2.2
	 * @since 2.8.0 Handled settings POST initialization.
	 */
	public function settings_init() {

		//* Handle post-update actions. Must be initialized on admin_init and is initalized on options.php.
		if ( 'options.php' === $GLOBALS['pagenow'] )
			$this->handle_update_post();

		//* Output metaboxes.
		\add_action( $this->seo_settings_page_hook . '_settings_page_boxes', [ $this, '_output_seo_settings_columns' ] );
		\add_action( 'load-' . $this->seo_settings_page_hook, [ $this, '_register_seo_settings_metaboxes' ] );
	}

	/**
	 * Outputs SEO Settings page wrap.
	 *
	 * @since 3.0.0
	 * @access private
	 */
	public function _output_seo_settings_wrap() {
		/**
		 * @since 3.0.0
		 */
		\do_action( 'the_seo_framework_pre_seo_settings' );
		$this->get_view( 'admin/seo-settings-wrap', get_defined_vars() );
		/**
		 * @since 3.0.0
		 */
		\do_action( 'the_seo_framework_pro_seo_settings' );
	}

	/**
	 * Outputs SEO Settings columns.
	 *
	 * @since 3.0.0
	 * @access private
	 */
	public function _output_seo_settings_columns() {
		$this->get_view( 'admin/seo-settings-columns', get_defined_vars() );
	}

	/**
	 * Registers meta boxes on the Site SEO Settings page.
	 *
	 * @since 3.0.0
	 * @access private
	 * @see $this->general_metabox()     Callback for General Settings box.
	 * @see $this->title_metabox()       Callback for Title Settings box.
	 * @see $this->description_metabox() Callback for Description Settings box.
	 * @see $this->robots_metabox()      Callback for Robots Settings box.
	 * @see $this->homepage_metabox()    Callback for Home Page Settings box.
	 * @see $this->social_metabox()      Callback for Social Settings box.
	 * @see $this->schema_metabox()      Callback for Schema Settings box.
	 * @see $this->webmaster_metabox()   Callback for Webmaster Settings box.
	 * @see $this->sitemaps_metabox()    Callback for Sitemap Settings box.
	 * @see $this->feed_metabox()        Callback for Feed Settings box.
	 */
	public function _register_seo_settings_metaboxes() {

		/**
		 * Various metabox filters.
		 * Set any to false if you wish the meta box to be removed.
		 *
		 * @since 2.2.4
		 * @since 2.8.0: Added `the_seo_framework_general_metabox` filter.
		 */
		$general     = (bool) \apply_filters( 'the_seo_framework_general_metabox', true );
		$title       = (bool) \apply_filters( 'the_seo_framework_title_metabox', true );
		$description = (bool) \apply_filters( 'the_seo_framework_description_metabox', true );
		$robots      = (bool) \apply_filters( 'the_seo_framework_robots_metabox', true );
		$home        = (bool) \apply_filters( 'the_seo_framework_home_metabox', true );
		$social      = (bool) \apply_filters( 'the_seo_framework_social_metabox', true );
		$schema      = (bool) \apply_filters( 'the_seo_framework_schema_metabox', true );
		$webmaster   = (bool) \apply_filters( 'the_seo_framework_webmaster_metabox', true );
		$sitemap     = (bool) \apply_filters( 'the_seo_framework_sitemap_metabox', true );
		$feed        = (bool) \apply_filters( 'the_seo_framework_feed_metabox', true );

		//* Title Meta Box
		if ( $general )
			\add_meta_box(
				'autodescription-general-settings',
				\esc_html__( 'General Settings', 'autodescription' ),
				[ $this, 'general_metabox' ],
				$this->seo_settings_page_hook,
				'main',
				[]
			);

		//* Title Meta Box
		if ( $title )
			\add_meta_box(
				'autodescription-title-settings',
				\esc_html__( 'Title Settings', 'autodescription' ),
				[ $this, 'title_metabox' ],
				$this->seo_settings_page_hook,
				'main',
				[]
			);

		//* Description Meta Box
		if ( $description )
			\add_meta_box(
				'autodescription-description-settings',
				\esc_html__( 'Description Meta Settings', 'autodescription' ),
				[ $this, 'description_metabox' ],
				$this->seo_settings_page_hook,
				'main',
				[]
			);

		//* Home Page Meta Box
		if ( $home )
			\add_meta_box(
				'autodescription-homepage-settings',
				\esc_html__( 'Home Page Settings', 'autodescription' ),
				[ $this, 'homepage_metabox' ],
				$this->seo_settings_page_hook,
				'main',
				[]
			);

		//* Social Meta Box
		if ( $social )
			\add_meta_box(
				'autodescription-social-settings',
				\esc_html__( 'Social Meta Settings', 'autodescription' ),
				[ $this, 'social_metabox' ],
				$this->seo_settings_page_hook,
				'main',
				[]
			);

		//* Title Meta Box
		if ( $schema )
			\add_meta_box(
				'autodescription-schema-settings',
				\esc_html__( 'Schema Settings', 'autodescription' ),
				[ $this, 'schema_metabox' ],
				$this->seo_settings_page_hook,
				'main',
				[]
			);

		//* Robots Meta Box
		if ( $robots )
			\add_meta_box(
				'autodescription-robots-settings',
				\esc_html__( 'Robots Meta Settings', 'autodescription' ),
				[ $this, 'robots_metabox' ],
				$this->seo_settings_page_hook,
				'main',
				[]
			);

		//* Webmaster Meta Box
		if ( $webmaster )
			\add_meta_box(
				'autodescription-webmaster-settings',
				\esc_html__( 'Webmaster Meta Settings', 'autodescription' ),
				[ $this, 'webmaster_metabox' ],
				$this->seo_settings_page_hook,
				'main',
				[]
			);

		//* Sitemaps Meta Box
		if ( $sitemap )
			\add_meta_box(
				'autodescription-sitemap-settings',
				\esc_html__( 'Sitemap Settings', 'autodescription' ),
				[ $this, 'sitemaps_metabox' ],
				$this->seo_settings_page_hook,
				'main',
				[]
			);

		//* Feed Meta Box
		if ( $feed )
			\add_meta_box(
				'autodescription-feed-settings',
				\esc_html__( 'Feed Settings', 'autodescription' ),
				[ $this, 'feed_metabox' ],
				$this->seo_settings_page_hook,
				'main',
				[]
			);
	}

	/**
	 * Display notices on the save or reset of settings.
	 *
	 * @since 2.2.2
	 * @securitycheck 3.0.0 OK. NOTE: Users can however MANUALLY trigger these on the SEO settings page.
	 * @todo convert the "get" into secure "error_notice" option. See TSF Extension Manager.
	 * @todo convert $this->page_defaults to inline texts. It's now uselessly rendering.
	 *
	 * @return void
	 */
	public function notices() {

		if ( false === $this->is_seo_settings_page( true ) )
			return;

		$get = empty( $_GET ) ? null : $_GET;

		if ( null === $get )
			return;

		if ( isset( $get['settings-updated'] ) && 'true' === $get['settings-updated'] ) :
			$this->do_dismissible_notice( $this->page_defaults['saved_notice_text'], 'updated' );
		elseif ( isset( $get['tsf-settings-reset'] ) && 'true' === $get['tsf-settings-reset'] ) :
			$this->do_dismissible_notice( $this->page_defaults['reset_notice_text'], 'warning' );
		elseif ( isset( $get['error'] ) && 'true' === $get['error'] ) :
			$this->do_dismissible_notice( $this->page_defaults['error_notice_text'], 'error' );
		elseif ( isset( $get['tsf-settings-updated'] ) && 'true' === $get['tsf-settings-updated'] ) :
			$this->do_dismissible_notice( $this->page_defaults['plugin_update_text'], 'updated' );
		endif;
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
		echo \esc_attr( $this->get_field_name( $name ) );
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
	 * @param string $id Field id base.
	 * @param boolean $echo Whether to escape echo or just return.
	 * @return string Full field id
	 */
	public function field_id( $id, $echo = true ) {

		if ( $echo ) {
			echo \esc_attr( $this->get_field_id( $id ) );
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
		echo \esc_attr( $this->get_field_value( $key ) );
	}

	/**
	 * Echo or return a chechbox fields wrapper.
	 *
	 * @since 2.6.0
	 *
	 * @param string $input The input to wrap. Should already be escaped.
	 * @param boolean $echo Whether to escape echo or just return.
	 * @return string|void Wrapped $input.
	 */
	public function wrap_fields( $input = '', $echo = false ) {

		if ( is_array( $input ) )
			$input = implode( PHP_EOL, $input );

		if ( $echo ) {
			echo '<div class="tsf-fields">' . $input . '</div>'; // xss user warning.
		} else {
			return '<div class="tsf-fields">' . $input . '</div>';
		}
	}

	/**
	 * Returns a chechbox wrapper.
	 *
	 * @since 2.6.0
	 * @since 2.7.0 Added escape parameter. Defaults to true.
	 * @since 3.0.3 Added $disabled parameter. Defaults to false.
	 *
	 * @param string $field_id    The option ID. Must be within the Autodescription settings.
	 * @param string $label       The checkbox description label.
	 * @param string $description Addition description to place beneath the checkbox.
	 * @param bool   $escape      Whether to escape the label and description.
	 * @param bool   $disabled    Whether to disable the input.
	 * @return string HTML checkbox output.
	 */
	public function make_checkbox( $field_id = '', $label = '', $description = '', $escape = true, $disabled = false ) {

		if ( $escape ) {
			$description = \esc_html( $description );
			$label = \esc_html( $label );
		}

		$description = $description ? '<p class="description tsf-option-spacer">' . $description . '</p>' : '';

		$output = '<span class="tsf-toblock">'
					. '<label for="'
						. $this->get_field_id( $field_id ) . '" '
						. ( $disabled ? 'class=tsf-disabled ' : '' )
					. '>'
						. '<input '
							. 'type="checkbox" '
							. ( $disabled ? 'class=tsf-disabled disabled ' : '' )
							. 'name="' . $this->get_field_name( $field_id ) . '" '
							. 'id="' . $this->get_field_id( $field_id ) . '" '
							. ( $disabled ? '' : $this->get_is_conditional_checked( $field_id ) . ' ' )
							. 'value="1" '
							. \checked( $this->get_field_value( $field_id ), true, false ) .
						' />'
						. $label
					. '</label>'
				. '</span>'
				. $description;

		return $output;
	}

	/**
	 * Return a wrapped question mark.
	 *
	 * @since 2.6.0
	 * @since 3.0.0 Links are now no longer followed, referred or bound to opener.
	 *
	 * @param string $description The descriptive on-hover title.
	 * @param string $link The non-escaped link.
	 * @param bool $echo Whether to echo or return.
	 * @return string HTML checkbox output if $echo is false.
	 */
	public function make_info( $description = '', $link = '', $echo = true ) {

		if ( $link ) {
			$output = sprintf(
				'<a href="%1$s" class="tsf-tooltip-item" target="_blank" rel="nofollow noreferrer noopener" title="%2$s" data-desc="%2$s">[?]</a>',
				\esc_url( $link, [ 'http', 'https' ] ),
				\esc_attr( $description )
			);
		} else {
			$output = sprintf(
				'<span class="tsf-tooltip-item" title="%1$s" data-desc="%1$s">[?]</span>',
				\esc_attr( $description )
			);
		}

		$output = sprintf( '<span class="tsf-tooltip-wrap">%s</span>', $output );

		if ( $echo ) {
			echo $output; // xss ok
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
		\add_action( "load-{$this->seo_settings_page_hook}", [ $this, 'metabox_scripts' ] );
	}

	/**
	 * Includes the necessary sortable metabox scripts.
	 *
	 * @since 2.2.2
	 */
	public function metabox_scripts() {
		\wp_enqueue_script( 'common' );
		\wp_enqueue_script( 'wp-lists' );
		\wp_enqueue_script( 'postbox' );
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
				printf( 'class="%s"', \esc_attr( $class ) );
			} else {
				echo \esc_attr( $class );
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
				printf( 'class="%s"', \esc_attr( $class ) );
			} else {
				echo \esc_attr( $class );
			}
		} else {
			if ( $wrap )
				return sprintf( 'class="%s"', $class );

			return $class;
		}
	}

	/**
	 * Returns the HTML class wrap for warning/default Checkbox options.
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
				printf( 'class="%s"', \esc_attr( $class ) );
			} else {
				echo \esc_attr( $class );
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
				printf( 'class="%s"', \esc_attr( $class ) );
			} else {
				echo \esc_attr( $class );
			}
		} else {
			if ( $wrap )
				return sprintf( 'class="%s"', $class );

			return $class;
		}
	}

	/**
	 * Returns social image uploader form button.
	 * Also registers additional i18n strings for JS.
	 *
	 * @since 2.8.0
	 * @since 3.1.0 No longer prepares media l10n data.
	 *
	 * @param string $input_id Required. The HTML input id to pass URL into.
	 * @return string The image uploader button.
	 */
	public function get_social_image_uploader_form( $input_id ) {

		if ( ! $input_id )
			return '';

		$s_input_id = \esc_attr( $input_id );

		$content = vsprintf(
			'<button type=button data-href="%1$s" class="tsf-set-image-button button button-primary button-small" title="%2$s" id="%3$s-select"
				data-input-id="%3$s" data-input-type="social" data-width="%4$s" data-height="%5$s" data-flex="%6$d">%7$s</button>',
			[
				\esc_url( \get_upload_iframe_src( 'image', $this->get_the_real_ID() ) ),
				\esc_attr_x( 'Select social image', 'Button hover', 'autodescription' ),
				$s_input_id,
				'1200',
				'630',
				true,
				\esc_html__( 'Select Image', 'autodescription' ),
			]
		);

		return $content;
	}

	/**
	 * Returns logo uploader form buttons.
	 * Also registers additional i18n strings for JS.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 No longer prepares media l10n data.
	 *
	 * @param string $input_id Required. The HTML input id to pass URL into.
	 * @return string The image uploader button.
	 */
	public function get_logo_uploader_form( $input_id ) {

		if ( ! $input_id )
			return '';

		$s_input_id = \esc_attr( $input_id );

		$content = vsprintf(
			'<button type=button data-href="%1$s" class="tsf-set-image-button button button-primary button-small" title="%2$s" id="%3$s-select"
				data-input-id="%3$s" data-input-type="logo" data-width="%4$s" data-height="%5$s" data-flex="%6$d">%7$s</button>',
			[
				\esc_url( \get_upload_iframe_src( 'image', $this->get_the_real_ID() ) ),
				'',
				$s_input_id,
				'512',
				'512',
				false,
				\esc_html__( 'Select Logo', 'autodescription' ),
			]
		);

		return $content;
	}

	/**
	 * Outputs floating and reference title HTML elements for JavaScript.
	 *
	 * @since 3.0.4
	 */
	public function output_js_title_elements() {
		?>
		<span id="tsf-title-reference" style="display:none"></span>
		<span id="tsf-title-offset" class="hide-if-no-js"></span>
		<span id="tsf-title-placeholder" class="hide-if-no-js"></span>
		<span id="tsf-title-placeholder-prefix" class="hide-if-no-js"></span>
		<?php
	}

	/**
	 * Outputs reference description HTML elements for JavaScript.
	 *
	 * @since 3.0.4
	 */
	public function output_js_description_elements() {
		?>
		<span id="tsf-description-reference" style="display:none"></span>
		<?php
	}

	/**
	 * Outputs character counter wrap for both JavaScript and no-Javascript.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 Added an "what if you click" onhover-title.
	 *
	 * @param string $for     The input ID it's for.
	 * @param string $initial The initial value for no-JS.
	 * @param bool   $display Whether to display the counter. (options page gimmick)
	 */
	public function output_character_counter_wrap( $for, $initial = '', $display = true ) {
		printf(
			'<div class="tsf-counter-wrap" %s><span class="description tsf-counter" title="%s">%s</span><span class="hide-if-no-js tsf-ajax"></span></div>',
			( $display ? '' : 'style="display:none;"' ),
			\esc_attr( 'Click to change counter type', 'autodescription' ),
			sprintf(
				/* translators: %s = number */
				\esc_html__( 'Characters Used: %s', 'autodescription' ),
				sprintf(
					'<span id="%s_chars">%s</span>',
					\esc_attr( $for ),
					(int) mb_strlen( $initial )
				)
			)
		);
	}

	/**
	 * Outputs pixel counter wrap for javascript.
	 *
	 * @since 3.0.0
	 *
	 * @param string $for  The input ID it's for.
	 * @param string $type Whether it's a 'title' or 'description' counter.
	 * @param bool   $display Whether to display the counter. (options page gimmick)
	 */
	public function output_pixel_counter_wrap( $for, $type, $display = true ) {
		vprintf(
			'<div class="tsf-pixel-counter-wrap hide-if-no-js" %s>%s%s</div>',
			[
				( $display ? '' : 'style="display:none;"' ),
				sprintf(
					'<div id="%s_pixels" class="tsf-tooltip-wrap">%s</div>',
					\esc_attr( $for ),
					'<span class="tsf-pixel-counter-bar tsf-tooltip-item" aria-label="" data-desc=""><span class="tsf-pixel-counter-fluid"></span></span>'
				),
				sprintf(
					'<div class="tsf-pixel-shadow-wrap"><span class="tsf-pixel-counter-shadow tsf-%s-pixel-counter-shadow"></span></div>',
					\esc_attr( $type )
				),
			]
		);
	}
}
