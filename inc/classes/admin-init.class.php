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
 * Class The_SEO_Framework\Admin_Init
 *
 * Initializes the plugin for the wp-admin screens.
 * Enqueues CSS and Javascript.
 *
 * @since 2.8.0
 */
class Admin_Init extends Init {

	/**
	 * Constructor. Loads parent constructor, registers script names and adds actions.
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Enqueues scripts in the admin area on the supported screens.
	 *
	 * @since 2.3.3
	 *
	 * @param string $hook The current page hook.
	 */
	public function enqueue_admin_scripts( $hook ) {

		$enqueue = false;

		if ( $this->is_seo_settings_page() ) {
			$enqueue = true;
		} else {
			$enqueue_hooks = [
				'edit.php',
				'post.php',
				'post-new.php',
				'edit-tags.php',
				'term.php',
			];

			if ( ! $this->is_option_checked( 'display_seo_bar_tables' ) ) {
				$enqueue_hooks = array_diff( $enqueue_hooks, [ 'edit.php', 'edit-tags.php' ] );
			}

			if ( isset( $hook ) && $hook && in_array( $hook, $enqueue_hooks, true ) ) {
				if ( $this->post_type_supports_custom_seo() )
					$enqueue = true;
			}
		}

		$enqueue and $this->init_admin_scripts();
	}

	/**
	 * Returns the static scripts class object.
	 *
	 * The first letter of the method is capitalized, to indicate it's a class caller.
	 *
	 * @since 3.1.0
	 * @builder
	 *
	 * @return string The scripts class name.
	 */
	public function Scripts() {
		// return Builder\Scripts::class; //= PHP 5.5+
		return '\\The_SEO_Framework\\Builders\\Scripts';
	}

	/**
	 * Registers admin scripts and styles.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Rewritten, same functionality.
	 *
	 * @param bool $direct Whether to directly include the files, or let the action handler do it.
	 */
	public function init_admin_scripts( $direct = false ) {

		$scripts = $this->Scripts();
		/**
		 * Applies filter 'the_seo_framework_scripts'.
		 *
		 * @since 3.1.0
		 *
		 * @param array  $scripts The default CSS and JS loader settings.
		 * @param string $scripts The \The_SEO_Framework\Builders\Scripts builder class name.
		 */
		$scripts::register( (array) \apply_filters_ref_array( 'the_seo_framework_scripts', [
			[
				[
					'id'     => 'tsf',
					'type'   => 'css',
					'deps'   => [ 'tsf-tt' ],
					'hasrtl' => true,
					'name'   => 'tsf',
					'base'   => THE_SEO_FRAMEWORK_DIR_URL . 'lib/css/',
					'ver'    => THE_SEO_FRAMEWORK_VERSION,
					'inline' => [
						'.tsf-flex-nav-tab .tsf-flex-nav-tab-radio:checked + .tsf-flex-nav-tab-label' => [
							'box-shadow:0 -2px 0 0 {{$color_accent}} inset',
						],
					],
				],
				[
					'id'   => 'tsf',
					'type' => 'js',
					'deps' => [ 'jquery', 'tsf-tt' ],
					'name' => 'tsf',
					'base' => THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
					'ver'  => THE_SEO_FRAMEWORK_VERSION,
					'l10n' => [
						'name' => 'tsfL10n',
						'data' => $this->get_javascript_l10n(),
					],
				],
				[
					'id'     => 'tsf-tt',
					'type'   => 'css',
					'deps'   => [],
					'hasrtl' => false,
					'name'   => 'tt',
					'base'   => THE_SEO_FRAMEWORK_DIR_URL . 'lib/css/',
					'ver'    => THE_SEO_FRAMEWORK_VERSION,
					'inline' => [
						'.tsf-tooltip-text-wrap' => [
							'background-color:{{$bg_accent}}',
							'color:{{$rel_bg_accent}}',
						],
						'.tsf-tooltip-arrow:after' => [
							'border-top-color:{{$bg_accent}}',
						],
						'.tsf-tooltip-down .tsf-tooltip-arrow:after' => [
							'border-bottom-color:{{$bg_accent}}',
						],
					],
				],
				[
					'id'   => 'tsf-tt',
					'type' => 'js',
					'deps' => [ 'jquery' ],
					'name' => 'tt',
					'base' => THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
					'ver'  => THE_SEO_FRAMEWORK_VERSION,
				],
			],
			$scripts,
		] ) );

		if ( $this->is_post_edit() ) {
			$this->enqueue_media_scripts();
			$this->enqueue_primaryterm_scripts();
		} elseif ( $this->is_seo_settings_page() ) {
			$this->enqueue_media_scripts();
			\wp_enqueue_style( 'wp-color-picker' );
			\wp_enqueue_script( 'wp-color-picker' );
		}

		//= The $scripts class autoinvokes the scripts. $direct helps when it's invoked too late.
		if ( $direct ) {
			$scripts::enqueue();
		}
	}

	/**
	 * Enqueues Media Upload and Cropping scripts.
	 *
	 * @since 3.1.0
	 */
	public function enqueue_media_scripts() {

		$args = [];
		if ( $this->is_post_edit() ) {
			$args['post'] = $this->get_the_real_admin_ID();
		}
		\wp_enqueue_media( $args );

		$this->Scripts()::register( [
			'id'   => 'tsf-media',
			'type' => 'js',
			'deps' => [ 'jquery', 'tsf' ],
			'name' => 'media',
			'base' => THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
			'ver'  => THE_SEO_FRAMEWORK_VERSION,
			'l10n' => [
				'name' => 'tsfMediaL10n',
				'data' => [
					'labels' => [
						'social' => [
							'imgSelect'      => \esc_attr__( 'Select Image', 'autodescription' ),
							'imgSelectTitle' => \esc_attr_x( 'Select social image', 'Button hover', 'autodescription' ),
							'imgChange'      => \esc_attr__( 'Change Image', 'autodescription' ),
							'imgRemove'      => \esc_attr__( 'Remove Image', 'autodescription' ),
							'imgRemoveTitle' => \esc_attr__( 'Remove selected social image', 'autodescription' ),
							'imgFrameTitle'  => \esc_attr_x( 'Select Social Image', 'Frame title', 'autodescription' ),
							'imgFrameButton' => \esc_attr__( 'Use this image', 'autodescription' ),
						],
						'logo'   => [
							'imgSelect'      => \esc_attr__( 'Select Logo', 'autodescription' ),
							'imgSelectTitle' => '',
							'imgChange'      => \esc_attr__( 'Change Logo', 'autodescription' ),
							'imgRemove'      => \esc_attr__( 'Remove Logo', 'autodescription' ),
							'imgRemoveTitle' => \esc_attr__( 'Unset selected logo', 'autodescription' ),
							'imgFrameTitle'  => \esc_attr_x( 'Select Logo', 'Frame title', 'autodescription' ),
							'imgFrameButton' => \esc_attr__( 'Use this image', 'autodescription' ),
						],
					],
				],
			],
		] );
	}

	/**
	 * Enqueues Primary Term Selection scripts.
	 *
	 * @since 3.1.0
	 */
	public function enqueue_primaryterm_scripts() {

		$id = $this->get_the_real_admin_ID();

		$post_type   = \get_post_type( $id );
		$_taxonomies = $post_type ? $this->get_hierarchical_taxonomies_as( 'objects', $post_type ) : [];

		$taxonomies = [];

		foreach ( $_taxonomies as $_t ) {
			$_i18n_name = strtolower( $_t->labels->singular_name );
			$taxonomies[ $_t->name ] = [
				'name'    => $_t->name,
				'i18n'    => [
					/* translators: %s = term name */
					'makePrimary' => sprintf( \esc_html__( 'Make primary %s', 'autodescription' ), $_i18n_name ),
					/* translators: %s = term name */
					'primary' => sprintf( \esc_html__( 'Primary %s', 'autodescription' ), $_i18n_name ),
				],
				'primary' => $this->get_primary_term_id( $id, $_t->name ) ?: 0,
			];
		}

		$this->Scripts()::register( [
			[
				'id'   => 'tsf-pt',
				'type' => 'js',
				'deps' => [ 'jquery', 'tsf', 'tsf-tt' ],
				'name' => 'pt',
				'base' => THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'  => THE_SEO_FRAMEWORK_VERSION,
				'l10n' => [
					'name' => 'tsfPTL10n',
					'data' => [
						'taxonomies' => $taxonomies,
					],
				],
				'tmpl' => [
					'file' => $this->get_view_location( 'templates/inpost/primary-term-selector' ),
				],
			],
			[
				'id'     => 'tsf-pt',
				'type'   => 'css',
				'deps'   => [ 'tsf-tt' ],
				'hasrtl' => true,
				'name'   => 'pt',
				'base'   => THE_SEO_FRAMEWORK_DIR_URL . 'lib/css/',
				'ver'    => THE_SEO_FRAMEWORK_VERSION,
			],
		] );
	}

	/**
	 * Generate Javascript Localization.
	 *
	 * @TODO rewrite, it's slow and a mess.
	 *
	 * @since 2.6.0
	 * @staticvar array $strings : The l10n strings.
	 * @since 2.7.0 Added AJAX nonce: 'autodescription-ajax-nonce'
	 * @since 2.8.0 1 : Added input detection: 'hasInput'
	 *              2 : Reworked output.
	 *              3 : Removed unused caching.
	 *              4 : Added dynamic output control.
	 * @since 2.9.0 Added boolean $returnValue['states']['isSettingsPage']
	 * @since 3.0.4 `descPixelGuideline` has been increased from "920 and 820" to "1820 and 1720" respectively.
	 *
	 * @return array $strings The l10n strings.
	 */
	protected function get_javascript_l10n() {

		$id = $this->get_the_real_ID();
		$blog_name = $this->get_blogname();
		$description = $this->get_blogdescription();
		$default_title = '';
		$additions = '';

		$use_additions = (bool) $this->get_option( 'homepage_tagline' );
		$home_tagline = $this->get_option( 'homepage_title_tagline' );
		$title_location = $this->get_option( 'title_location' );
		$title_add_additions = $this->add_title_additions();
		$counter_type = (int) $this->get_user_option( 0, 'counter_type', 3 );

		$title_separator = $this->get_separator( 'title' );
		$description_separator = $this->get_separator( 'description' );

		$ishome = false;
		$is_settings_page = $this->is_seo_settings_page();
		$is_post_edit = $this->is_post_edit();
		$is_term_edit = $this->is_term_edit();
		$has_input = $is_settings_page || $is_post_edit || $is_term_edit;

		$post_type = $is_post_edit ? \get_post_type( $id ) : false;

		if ( $is_settings_page ) {
			// We're on our SEO settings pages.
			if ( $this->has_page_on_front() ) {
				// Home is a page.
				$id = \get_option( 'page_on_front' );
				$inpost_title = $this->get_custom_field( '_genesis_title', $id );
			} else {
				// Home is a blog.
				$inpost_title = '';
			}
			$default_title = $inpost_title ?: $blog_name;
			$additions = $home_tagline ?: $description;
		} else {
			// We're somewhere within default WordPress pages.
			if ( $this->is_static_frontpage( $id ) ) {
				$default_title = $this->get_option( 'homepage_title' ) ?: $blog_name;
				$title_location = $this->get_option( 'home_title_location' );
				$ishome = true;

				if ( $use_additions ) {
					$additions = $home_tagline ?: $description;
				} else {
					$additions = '';
				}
			} elseif ( $is_post_edit ) {
				//* We're on post.php
				$generated_doctitle_args = array(
					'term_id' => $id,
					'notagline' => true,
					'get_custom_field' => false,
				);

				$default_title = $this->title( '', '', '', $generated_doctitle_args );

				if ( $title_add_additions ) {
					$additions = $blog_name;
					$use_additions = true;
				} else {
					$additions = '';
					$use_additions = false;
				}
			} elseif ( $is_term_edit ) {
				//* Category or Tag.
				if ( isset( $GLOBALS['current_screen']->taxonomy ) && $id ) {
					$default_title = $this->single_term_title( '', false, $this->fetch_the_term( $id ) );
					$additions = $title_add_additions ? $blog_name : '';
				}
			} else {
				//* We're in a special place.
				// Can't fetch title.
				$default_title = '';
				$additions = $title_add_additions ? $blog_name : '';
			}
		}

		$this->set_js_nonces( [
			/**
			 * Use $this->get_settings_capability() ?... might conflict with other nonces.
			 * @augments tsfMedia 'upload_files'
			 */
			// 'manage_options' => \current_user_can( 'manage_options' ) ? \wp_create_nonce( 'tsf-ajax-manage_options' ) : false,
			'upload_files' => \current_user_can( 'upload_files' ) ? \wp_create_nonce( 'tsf-ajax-upload_files' ) : false,
			'edit_posts'   => \current_user_can( 'edit_posts' ) ? \wp_create_nonce( 'tsf-ajax-edit_posts' ) : false,
		] );

		$term_name = '';
		$use_term_prefix = false;
		if ( $is_term_edit ) {
			$_term = $this->fetch_the_term( $id );
			$term_name = $this->get_the_term_name( $_term, true, false );
			$use_term_prefix = $this->use_archive_prefix( $_term );
		}

		$l10n = array(
			'nonces' => $this->get_js_nonces(),
			'states' => array(
				'isRTL' => (bool) \is_rtl(),
				'isHome' => $ishome,
				'hasInput' => $has_input,
				'counterType' => \absint( $counter_type ),
				'useTagline' => $use_additions,
				'useTermPrefix' => $use_term_prefix,
				'isSettingsPage' => $is_settings_page,
				'isPostEdit' => $is_post_edit,
				'isTermEdit' => $is_term_edit,
				'postType' => $post_type,
				'isPrivate' => $has_input && $id && $this->is_private( $id ),
				'isPasswordProtected' => $has_input && $id && $this->is_password_protected( $id ),
				'debug' => $this->script_debug,
			),
			'i18n' => array(
				'saveAlert' => \__( 'The changes you made will be lost if you navigate away from this page.', 'autodescription' ),
				'confirmReset' => \__( 'Are you sure you want to reset all SEO settings to their defaults?', 'autodescription' ),
				'good' => \__( 'Good', 'autodescription' ),
				'okay' => \__( 'Okay', 'autodescription' ),
				'bad' => \__( 'Bad', 'autodescription' ),
				'unknown' => \__( 'Unknown', 'autodescription' ),
				'privateTitle' => $has_input && $id ? \__( 'Private:', 'autodescription' ) : '',
				'protectedTitle' => $has_input && $id ? \__( 'Protected:', 'autodescription' ) : '',
				/* translators: Pixel counter. 1: width, 2: guideline */
				'pixelsUsed' => $has_input ? \__( '%1$d out of %2$d pixels are used.', 'autodescription' ) : '',
			),
			'params' => array(
				'objectTitle' => $default_title,
				'defaultTitle' => $default_title,
				'titleAdditions' => $additions,
				'blogDescription' => $description,
				'termName' => $term_name,
				'untitledTitle' => $this->untitled(),
				'titleSeparator' => $title_separator,
				'descriptionSeparator' => $description_separator,
				'titleLocation' => $title_location,
				'titlePixelGuideline' => 600,
				'descPixelGuideline' => $is_post_edit ? ( $this->is_page() ? 1820 : 1720 ) : 1820,
			),
		);

		$decode = array( 'i18n', 'params' );
		$flags = ENT_COMPAT;
		foreach ( $decode as $key ) {
			foreach ( $l10n[ $key ] as $k => $v ) {
				$l10n[ $key ][ $k ] = \esc_js( \html_entity_decode( $v, $flags, 'UTF-8' ) );
			}
		}

		/**
		 * Applies filters 'the_seo_framework_js_l10n'
		 *
		 * @since 3.0.0
		 * @param array $l10n
		 */
		return (array) \apply_filters( 'the_seo_framework_js_l10n', $l10n );
	}

	/**
	 * Sets up additional JS l10n values for nonces.
	 *
	 * They are put under object 'tsfemL10n.nonces[ $key ] = $val'.
	 *
	 * @since 2.9.0
	 *
	 * @param string|array $key Required. The object key or array of keys and values. Requires escape.
	 * @param mixed $val The object value if $key is string. Requires escape.
	 */
	public function set_js_nonces( $key, $val = null ) {
		$this->get_js_nonces( $key, $val, false );
	}

	/**
	 * Maintains and Returns additional JS l10n.
	 *
	 * They are put under object 'tsfemL10n.nonces[ $key ] = $val'.
	 *
	 * If $key is an array, $val is ignored and $key's values are used instead.
	 *
	 * @since 2.9.0
	 * @staticvar object $nonces The cached nonces object.
	 *
	 * @param string|array $key The object key or array of keys and values. Requires escape.
	 * @param mixed $val The object value if $key is string. Requires escape.
	 * @param bool $get Whether to return the cached nonces.
	 * @return object Early when $get is true
	 */
	public function get_js_nonces( $key = null, $val = null, $get = true ) {

		static $nonces = null;

		if ( null === $nonces )
			$nonces = new \stdClass();

		if ( $get )
			return $nonces;

		if ( is_string( $key ) ) {
			$nonces->$key = $val;
		} elseif ( is_array( $key ) ) {
			foreach ( $key as $k => $v ) {
				$nonces->$k = $v;
			}
		}
	}

	/**
	 * Checks ajax referred set by set_js_nonces based on capability.
	 *
	 * Performs die() on failure.
	 *
	 * @since 2.9.0
	 * @access private
	 *         It uses an internally and manually created prefix.
	 * @uses WP Core check_ajax_referer()
	 * @see @link https://developer.wordpress.org/reference/functions/check_ajax_referer/
	 *
	 * @return false|int False if the nonce is invalid, 1 if the nonce is valid
	 *                   and generated between 0-12 hours ago, 2 if the nonce is
	 *                   valid and generated between 12-24 hours ago.
	 */
	public function check_tsf_ajax_referer( $capability ) {
		return \check_ajax_referer( 'tsf-ajax-' . $capability, 'nonce', true );
	}

	/**
	 * Adds removable query args to WordPress query arg handler.
	 *
	 * @since 2.8.0
	 *
	 * @param array $removable_query_args
	 * @return array The adjusted removable query args.
	 */
	public function add_removable_query_args( $removable_query_args = [] ) {

		if ( ! is_array( $removable_query_args ) )
			return $removable_query_args;

		$removable_query_args[] = 'tsf-settings-reset';
		$removable_query_args[] = 'tsf-settings-updated';

		return $removable_query_args;
	}

	/**
	 * Redirect the user to an admin page, and add query args to the URL string
	 * for alerts, etc.
	 *
	 * @since 2.2.2
	 * @since 2.9.2 : Added user-friendly exception handling.
	 * @since 2.9.3 : 1. Query arguments work again (regression 2.9.2).
	 *                2. Now only accepts http and https protocols.
	 *
	 * @param string $page Menu slug.
	 * @param array  $query_args Optional. Associative array of query string arguments
	 *               (key => value). Default is an empty array.
	 * @return null Return early if first argument is false.
	 */
	public function admin_redirect( $page, array $query_args = array() ) {

		if ( empty( $page ) )
			return;

		$url = html_entity_decode( \menu_page_url( $page, false ) );

		foreach ( $query_args as $key => $value ) {
			if ( empty( $key ) || empty( $value ) )
				unset( $query_args[ $key ] );
		}

		$target = \add_query_arg( $query_args, $url );
		$target = \esc_url_raw( $target, array( 'http', 'https' ) );

		//* Predict white screen:
		$headers_sent = headers_sent();

		/**
		 * Dev debug:
		 * 1. Change 302 to 500 if you wish to test headers.
		 * 2. Also force handle_admin_redirect_error() to run.
		 */
		\wp_safe_redirect( $target, 302 );

		//* White screen of death for non-debugging users. Let's make it friendlier.
		if ( $headers_sent ) {
			$this->handle_admin_redirect_error( $target );
		}

		exit;
	}

	/**
	 * Provides an accessible error for when redirecting fails.
	 *
	 * @since 2.9.2
	 * @see https://developer.wordpress.org/reference/functions/wp_redirect/
	 *
	 * @param string $target The redirect target location. Should be escaped.
	 * @return void
	 */
	protected function handle_admin_redirect_error( $target = '' ) {

		if ( empty( $target ) )
			return;

		$headers_list = headers_list();
		$location = sprintf( 'Location: %s', \wp_sanitize_redirect( $target ) );

		//* Test if WordPress' redirect header is sent. Bail if true.
		if ( in_array( $location, $headers_list, true ) )
			return;

		//* Output message:
		printf( '<p><strong>%s</strong></p>',
			//* Markdown escapes.
			$this->convert_markdown(
				sprintf(
					/* translators: %s = Redirect URL markdown */
					\esc_html__( 'There has been an error redirecting. Refresh the page or follow [this link](%s).', 'autodescription' ),
					$target
				),
				[ 'a' ],
				[ 'a_internal' => true ]
			)
		);
	}

	/**
	 * Handles counter option update on AJAX request.
	 *
	 * @since 2.6.0
	 * @since 2.9.0 : 1. Changed capability from 'publish_posts' to 'edit_posts'.
	 *                2. Added json header.
	 * @securitycheck 3.0.0 OK.
	 * @access private
	 */
	public function wp_ajax_update_counter_type() {

		if ( $this->is_admin() && $this->doing_ajax() ) :

			$this->check_tsf_ajax_referer( 'edit_posts' );

			//* Remove output buffer.
			$this->clean_response_header();

			//* If current user isn't allowed to edit posts, don't do anything and kill PHP.
			if ( ! \current_user_can( 'edit_posts' ) ) {
				//* Encode and echo results. Requires JSON decode within JS.
				\wp_send_json( [
					'type' => 'failure',
					'value' => '',
				] );
			}

			/**
			 * Count up, reset to 0 if needed. We have 4 options: 0, 1, 2, 3
			 * $_POST['val'] already contains updated number.
			 */
			$value = isset( $_POST['val'] ) ? intval( $_POST['val'] ) : $this->get_user_option( 0, 'counter_type', 3 ) + 1;
			$value = \absint( $value );

			if ( $value > 3 )
				$value = 0;

			//* Update the option and get results of action.
			$type = $this->update_user_option( 0, 'counter_type', $value ) ? 'success' : 'error';

			$results = [
				'type'  => $type,
				'value' => $value,
			];

			//* Encode and echo results. Requires JSON decode within JS.
			\wp_send_json( $results );
		endif;
	}

	/**
	 * Handles cropping of images on AJAX request.
	 *
	 * Copied from WordPress Core wp_ajax_crop_image.
	 * Adjusted: 1. It accepts capability 'upload_files', instead of 'customize'.
	 *           2. It now only accepts TSF own AJAX nonces.
	 *           3. It now only accepts context 'tsf-image'
	 *           4. It no longer accepts a default context.
	 *
	 * @since 2.9.0
	 * @securitycheck 3.0.0 OK.
	 * @access private
	 */
	public function wp_ajax_crop_image() {

		$this->check_tsf_ajax_referer( 'upload_files' );
		if ( ! \current_user_can( 'upload_files' ) )
			\wp_send_json_error();

		$attachment_id = \absint( $_POST['id'] );

		$context = \sanitize_key( str_replace( '_', '-', $_POST['context'] ) );
		$data    = array_map( 'absint', $_POST['cropDetails'] );
		$cropped = \wp_crop_image( $attachment_id, $data['x1'], $data['y1'], $data['width'], $data['height'], $data['dst_width'], $data['dst_height'] );

		if ( ! $cropped || \is_wp_error( $cropped ) )
			\wp_send_json_error( [ 'message' => \esc_js( \__( 'Image could not be processed.', 'autodescription' ) ) ] );

		switch ( $context ) :
			case 'tsf-image':
				/**
				 * Fires before a cropped image is saved.
				 *
				 * Allows to add filters to modify the way a cropped image is saved.
				 *
				 * @since 4.3.0 WordPress Core
				 *
				 * @param string $context       The Customizer control requesting the cropped image.
				 * @param int    $attachment_id The attachment ID of the original image.
				 * @param string $cropped       Path to the cropped image file.
				 */
				\do_action( 'wp_ajax_crop_image_pre_save', $context, $attachment_id, $cropped );

				/** This filter is documented in wp-admin/custom-header.php */
				$cropped = \apply_filters( 'wp_create_file_in_uploads', $cropped, $attachment_id ); // For replication.

				$parent_url = \wp_get_attachment_url( $attachment_id );
				$url        = str_replace( basename( $parent_url ), basename( $cropped ), $parent_url );

				$size       = @getimagesize( $cropped );
				$image_type = ( $size ) ? $size['mime'] : 'image/jpeg';

				$object = array(
					'post_title'     => basename( $cropped ),
					'post_content'   => $url,
					'post_mime_type' => $image_type,
					'guid'           => $url,
					'context'        => $context,
				);

				$attachment_id = \wp_insert_attachment( $object, $cropped );
				$metadata = \wp_generate_attachment_metadata( $attachment_id, $cropped );

				/**
				 * Filters the cropped image attachment metadata.
				 *
				 * @since 4.3.0 WordPress Core
				 * @see wp_generate_attachment_metadata()
				 *
				 * @param array $metadata Attachment metadata.
				 */
				$metadata = \apply_filters( 'wp_ajax_cropped_attachment_metadata', $metadata );
				\wp_update_attachment_metadata( $attachment_id, $metadata );

				/**
				 * Filters the attachment ID for a cropped image.
				 *
				 * @since 4.3.0 WordPress Core
				 *
				 * @param int    $attachment_id The attachment ID of the cropped image.
				 * @param string $context       The Customizer control requesting the cropped image.
				 */
				$attachment_id = \apply_filters( 'wp_ajax_cropped_attachment_id', $attachment_id, $context );
				break;

			default :
				\wp_send_json_error( [ 'message' => \esc_js( \__( 'Image could not be processed.', 'autodescription' ) ) ] );
				break;
		endswitch;

		\wp_send_json_success( \wp_prepare_attachment_for_js( $attachment_id ) );
	}
}
