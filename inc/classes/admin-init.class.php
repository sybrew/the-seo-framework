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
 * Class AutoDescription_Admin_Init
 *
 * Initializes the plugin for the wp-admin screens.
 * Enqueues CSS and Javascript.
 *
 * @since 2.1.6
 */
class AutoDescription_Admin_Init extends AutoDescription_Init {

	/**
	 * The page base file.
	 *
	 * @since 2.5.2.2
	 *
	 * @var string Holds Admin page base file.
	 */
	protected $page_base_file;

	/**
	 * JavaScript name identifier to be used with enqueuing.
	 *
	 * @since 2.5.2.2
	 *
	 * @var string JavaScript name identifier.
	 */
	public $js_name = 'autodescription';

	/**
	 * CSS script name identifier to be used with enqueuing.
	 *
	 * @since 2.6.0
	 *
	 * @var string CSS name identifier.
	 */
	public $css_name = 'autodescription';

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
	 * Constructor. Loads parent constructor, registers script names and adds actions.
	 */
	public function __construct() {
		parent::__construct();

		//* Enqueues admin scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ), 0, 1 );

		//* Admin AJAX for counter options.
		add_action( 'wp_ajax_the_seo_framework_update_counter', array( $this, 'wp_ajax_update_counter_type' ) );

	}

	/**
	 * Enqueues scripts in the admin area on the supported screens.
	 *
	 * @since 2.3.3
	 *
	 * @param string $hook The current page hook.
	 */
	public function enqueue_admin_scripts( $hook ) {

		$enqueue_hooks = array(
			'edit.php',
			'post.php',
			'post-new.php',
			'edit-tags.php',
			'term.php',
		);

		/**
		 * Check hook first.
		 * @since 2.3.9
		 */
		if ( isset( $hook ) && $hook && in_array( $hook, $enqueue_hooks, true ) ) {
			/**
			 * @uses $this->post_type_supports_custom_seo()
			 * @since 2.3.9
			 */
			if ( $this->post_type_supports_custom_seo() )
				$this->init_admin_scripts();
		}
	}

	/**
	 * Registers admin scripts and styles.
	 *
	 * @since 2.6.0
	 */
	public function init_admin_scripts( $direct = false ) {

		if ( $direct ) {
			$this->enqueue_admin_css( $this->page_base_file );
			$this->enqueue_admin_javascript( $this->page_base_file );
		} else {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_css' ), 1 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_javascript' ), 1 );
		}

	}

	/**
	 * Enqueues scripts.
	 *
	 * @since 2.0.2
	 *
	 * @param string $hook The current page hook.
	 */
	public function enqueue_admin_javascript( $hook ) {

		/**
		 * Put hook and js name in class vars.
		 * @since 2.5.2.2
		 */
		$this->page_base_file = $this->page_base_file ? $this->page_base_file : $hook;

		//* Register the script.
		$this->register_admin_javascript();

		wp_enqueue_script( $this->js_name );

		/**
		 * Localize JavaScript.
		 * @since 2.5.2.2
		 */
		add_action( 'admin_footer', array( $this, 'localize_admin_javascript' ) );

	}

	/**
	 * Registers admin CSS.
	 *
	 * @since 2.6.0
	 * @staticvar bool $registered : Prevents Re-registering of the style.
	 * @access private
	 */
	public function register_admin_javascript() {

		static $registered = null;

		if ( isset( $registered ) )
			return;

		$suffix = $this->script_debug ? '' : '.min';

		wp_register_script( $this->js_name, THE_SEO_FRAMEWORK_DIR_URL . "lib/js/autodescription{$suffix}.js", array( 'jquery' ), THE_SEO_FRAMEWORK_VERSION, true );

		$registered = true;

	}

	/**
	 * Localizes admin javascript.
	 *
	 * @since 2.5.2.2
	 */
	public function localize_admin_javascript() {

		static $localized = null;

		if ( isset( $localized ) )
			return;

		$strings = $this->get_javascript_l10n();

		wp_localize_script( $this->js_name, 'autodescriptionL10n', $strings );

		$localized = true;

	}

	/**
	 * Generate Javascript Localization.
	 *
	 * @since 2.6.0
	 * @staticvar array $strings : The l10n strings.
	 * @since 2.7.0 Added AJAX nonce: 'autodescription-ajax-nonce'
	 *
	 * @return array $strings The l10n strings.
	 */
	protected function get_javascript_l10n() {

		static $strings = null;

		if ( isset( $strings ) )
			return $strings;

		$blog_name = $this->get_blogname();
		$description = $this->get_blogdescription();
		$title = '';
		$additions = '';

		$tagline = (bool) $this->get_option( 'homepage_tagline' );
		$home_tagline = $this->get_option( 'homepage_title_tagline' );
		$title_location = $this->get_option( 'title_location' );
		$title_add_additions = $this->add_title_additions();
		$counter_type = (int) $this->get_user_option( 0, 'counter_type', 3 );

		//* Enunciate the lenghts of Titles and Descriptions.
		$good = __( 'Good', 'autodescription' );
		$okay = __( 'Okay', 'autodescription' );
		$bad = __( 'Bad', 'autodescription' );
		$unknown = __( 'Unknown', 'autodescription' );

		$separator = $this->get_separator( 'title' );

		$isrtl = (bool) is_rtl();
		$ishome = false;

		if ( isset( $this->page_base_file ) && $this->page_base_file ) {
			// We're somewhere within default WordPress pages.
			$post_id = $this->get_the_real_ID();

			if ( $this->is_static_frontpage( $post_id ) ) {
				$title = $blog_name;
				$title_location = $this->get_option( 'home_title_location' );
				$ishome = true;

				if ( $tagline ) {
					$additions = $home_tagline ? $home_tagline : $description;
				} else {
					$additions = '';
				}
			} elseif ( $post_id ) {
				//* We're on post.php
				$generated_doctitle_args = array(
					'term_id' => $post_id,
					'notagline' => true,
					'get_custom_field' => false,
				);

				$title = $this->title( '', '', '', $generated_doctitle_args );

				if ( $title_add_additions ) {
					$additions = $blog_name;
					$tagline = true;
				} else {
					$additions = '';
					$tagline = false;
				}
			} elseif ( $this->is_archive() ) {
				//* Category or Tag.
				global $current_screen;

				if ( isset( $current_screen->taxonomy ) ) {

					$term_id = $this->get_admin_term_id();

					if ( $term_id ) {
						$generated_doctitle_args = array(
							'term_id' => $term_id,
							'taxonomy' => $current_screen->taxonomy,
							'notagline' => true,
							'get_custom_field' => false,
						);

						$title = $this->title( '', '', '', $generated_doctitle_args );
						$additions = $title_add_additions ? $blog_name : '';
					}
				}
			} else {
				//* We're in a special place.
				// Can't fetch title.
				$title = '';
				$additions = $title_add_additions ? $blog_name : '';
			}
		} else {
			// We're on our SEO settings pages.
			if ( $this->has_page_on_front() ) {
				// Home is a page.
				$inpost_title = $this->get_custom_field( '_genesis_title', get_option( 'page_on_front' ) );
			} else {
				// Home is a blog.
				$inpost_title = '';
			}
			$title = $inpost_title ? $inpost_title : $blog_name;
			$additions = $home_tagline ? $home_tagline : $description;
		}

		$nonce = wp_create_nonce( 'autodescription-ajax-nonce' );

		return $strings = array(
			'saveAlert' => esc_html__( 'The changes you made will be lost if you navigate away from this page.', 'autodescription' ),
			'confirmReset' => esc_html__( 'Are you sure you want to reset all SEO settings to their defaults?', 'autodescription' ),
			'siteTitle' => esc_html( $title ),
			'titleAdditions' => esc_html( $additions ),
			'blogDescription' => esc_html( $description ),
			'titleTagline' => $tagline,
			'titleSeparator' => esc_html( $separator ),
			'titleLocation' => esc_html( $title_location ),
			'isRTL' => $isrtl,
			'isHome' => $ishome,
			'counterType' => absint( $counter_type ),
			'good' => esc_html( $good ),
			'okay' => esc_html( $okay ),
			'bad' => esc_html( $bad ),
			'unknown' => esc_html( $unknown ),
			'nonce' => $nonce,
		);
	}

	/**
	 * CSS for the AutoDescription Bar
	 *
	 * @since 2.1.9
	 *
	 * @param $hook the current page
	 *
	 * @todo get_network_option
	 * @priority low 3.0.0
	 */
	public function enqueue_admin_css( $hook ) {

		/**
		 * Put hook and js name in class vars.
		 * @since 2.5.2.2
		 */
		$this->page_base_file = $this->page_base_file ? $this->page_base_file : $hook;

		//* Register the script.
		$this->register_admin_css();

		wp_enqueue_style( $this->css_name );

	}

	/**
	 * Registers Admin CSS.
	 *
	 * @since 2.6.0
	 * @staticvar bool $registered : Prevents Re-registering of the style.
	 * @access private
	 */
	protected function register_admin_css() {

		static $registered = null;

		if ( isset( $registered ) )
			return;

		$rtl = is_rtl() ? '-rtl' : '';
		$suffix = $this->script_debug ? '' : '.min';
		$registered = true;

		wp_register_style( $this->css_name, THE_SEO_FRAMEWORK_DIR_URL . "lib/css/autodescription{$rtl}{$suffix}.css", array(), THE_SEO_FRAMEWORK_VERSION, 'all' );

	}

	/**
	 * Redirect the user to an admin page, and add query args to the URL string
	 * for alerts, etc.
	 *
	 * @since 2.2.2
	 *
	 * @param string $page			Menu slug.
	 * @param array  $query_args 	Optional. Associative array of query string arguments
	 * 								(key => value). Default is an empty array.
	 * @return null Return early if first argument is false.
	 */
	public function admin_redirect( $page, array $query_args = array() ) {

		if ( empty( $page ) )
			return;

		$url = html_entity_decode( menu_page_url( $page, 0 ) );

		foreach ( $query_args as $key => $value ) {
			if ( empty( $key ) || empty( $value ) )
				unset( $query_args[ $key ] );
		}

		$url = add_query_arg( $query_args, $url );

		wp_safe_redirect( esc_url_raw( $url ), 302 );
		exit;
	}

	/**
	 * Handles counter option update on AJAX request.
	 *
	 * @since 2.6.0
	 * @access private
	 */
	public function wp_ajax_update_counter_type() {

		if ( $this->is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			//* If current user isn't allowed to edit posts, don't do anything and kill PHP.
			if ( ! current_user_can( 'publish_posts' ) )
				exit;

			check_ajax_referer( 'autodescription-ajax-nonce', 'nonce' );

			/**
			 * Count up, reset to 0 if needed. We have 4 options: 0, 1, 2, 3
			 * $_POST['val'] already contains updated number.
			 */
			$value = isset( $_POST['val'] ) ? intval( $_POST['val'] ) : $this->get_user_option( 0, 'counter_type', 3 ) + 1;
			$value = absint( $value );

			if ( $value > 3 )
				$value = 0;

			//* Update the option and get results of action.
			$type = $this->update_user_option( 0, 'counter_type', $value ) ? 'success' : 'error';

			$results = array(
				'type' => $type,
				'value' => $value,
			);

			//* Encode and echo results. Requires JSON decode within JS.
			echo json_encode( $results );

			//* Kill PHP.
			exit;
		}

	}
}
