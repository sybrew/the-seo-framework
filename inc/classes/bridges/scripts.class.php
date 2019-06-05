<?php
/**
 * @package The_SEO_Framework\Classes\Bridges
 * @subpackage The_SEO_Framework\Bridges
 */
namespace The_SEO_Framework\Bridges;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * Sets up class loader as file is loaded.
 * This is done asynchronously, because static calls are handled prior and after.
 * @see EOF. Because of the autoloader and (future) trait calling, we can't do it before the class is read.
 * @link https://bugs.php.net/bug.php?id=75771
 */
$_load_scripts_class = function() {
	new Scripts();
};

/**
 * Prepares admin GUI scripts. Auto-invokes everything the moment this file is required.
 * Relies on \The_SEO_Framework\Builders\Scripts to register and load scripts.
 *
 * @since 3.3.0
 * @see \The_SEO_Framework\Builders\Scripts
 * @access protected
 *         Use static calls The_SEO_Framework\Bridges\Scripts::funcname()
 * @final Can't be extended.
 */
final class Scripts {

	/**
	 * @since 3.3.0
	 * @var \The_SEO_Framework\Bridges\Scripts $instance The instance.
	 */
	private static $instance;

	/**
	 * Prepares the class and loads constructor.
	 *
	 * Use this if the actions need to be registered early, but nothing else of
	 * this class is needed yet.
	 *
	 * @since 3.3.0
	 */
	public static function prepare() {}

	/**
	 * The constructor. Can't be instantiated externally from this file.
	 *
	 * This probably autoloads at action "admin_enqueue_scripts", priority "0".
	 *
	 * @since 3.3.0
	 * @access private
	 * @staticvar int $count Enforces singleton.
	 * @internal
	 */
	public function __construct() {

		static $count = 0;
		0 === $count++ or \wp_die( 'Don\'t instance <code>' . __CLASS__ . '</code>.' );

		$tsf = \the_seo_framework();

		static::$instance = &$this;
	}

	/**
	 * Initializes scripts based on admin query.
	 *
	 * @since 3.3.0
	 * @access private
	 * @internal This always runs; build your own loader from the public methods, instead.
	 */
	public static function _init() {

		$tsf = \the_seo_framework();

		$_scripts = [
			static::get_tsf_scripts(),
			static::get_tt_scripts(),
		];

		if ( $tsf->is_post_edit() ) {
			static::prepare_media_scripts();

			$_scripts[] = static::get_post_scripts();
			$_scripts[] = static::get_media_scripts();
			$_scripts[] = static::get_primaryterm_scripts();
			$_scripts[] = static::get_counter_scripts();
			$_scripts[] = static::get_ays_scripts();

			if ( $tsf->is_gutenberg_page() ) {
				$_scripts[] = static::get_gutenberg_compat_scripts();
			}
		} elseif ( $tsf->is_term_edit() ) {
			$_scripts[] = static::get_term_scripts();
			$_scripts[] = static::get_counter_scripts();
			$_scripts[] = static::get_ays_scripts();
		} elseif ( $tsf->is_seo_settings_page() ) {
			static::prepare_media_scripts();
			static::prepare_metabox_scripts();

			$_scripts[] = static::get_media_scripts();
			$_scripts[] = static::get_counter_scripts();
			$_scripts[] = static::get_settings_scripts();
			$_scripts[] = static::get_ays_scripts();
		}

		/**
		 * @since 3.1.0
		 * @since 3.3.0 1. Now holds all scripts.
		 *              2. Added $loader parameter.
		 * @param array  $scripts The default CSS and JS loader settings.
		 * @param string $builder The \The_SEO_Framework\Builders\Scripts builder class name.
		 * @param string $loader  The \The_SEO_Framework\Bridges\Scripts loader class name.
		 */
		$_scripts = \apply_filters_ref_array( 'the_seo_framework_scripts', [
			$_scripts,
			\The_SEO_Framework\Builders\Scripts::class,
			static::class, // i.e. `\The_SEO_Framework\Bridges\Scripts::class`
		] );

		\The_SEO_Framework\Builders\Scripts::register( $_scripts );
	}

	/**
	 * Prepares WordPress Media scripts.
	 *
	 * @since 3.3.0
	 */
	public static function prepare_media_scripts() {

		$tsf = \the_seo_framework();

		$args = [];
		if ( $tsf->is_post_edit() ) {
			$args['post'] = $tsf->get_the_real_admin_ID();
		}
		\wp_enqueue_media( $args );
	}

	/**
	 * Prepares WordPress metabox scripts.
	 *
	 * @since 3.3.0
	 */
	public static function prepare_metabox_scripts() {
		\wp_enqueue_script( 'common' );
		\wp_enqueue_script( 'wp-lists' );
		\wp_enqueue_script( 'postbox' );
	}

	/**
	 * Returns the default TSF scripts.
	 *
	 * @since 3.3.0
	 *
	 * @return array The script params.
	 */
	public static function get_tsf_scripts() {
		return [
			[
				'id'       => 'tsf',
				'type'     => 'css',
				'deps'     => [ 'tsf-tt' ],
				'autoload' => true,
				'hasrtl'   => true,
				'name'     => 'tsf',
				'base'     => THE_SEO_FRAMEWORK_DIR_URL . 'lib/css/',
				'ver'      => THE_SEO_FRAMEWORK_VERSION,
			],
			[
				'id'       => 'tsf',
				'type'     => 'js',
				'deps'     => [ 'jquery', 'tsf-tt' ],
				'autoload' => true,
				'name'     => 'tsf',
				'base'     => THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => THE_SEO_FRAMEWORK_VERSION,
				'l10n'     => [
					'name' => 'tsfL10n',
					'data' => static::get_tsf_l10n_data(),
				],
			],
		];
	}

	/**
	 * Returns TT (tooltip) scripts params.
	 *
	 * @since 3.3.0
	 *
	 * @return array The script params.
	 */
	public static function get_tt_scripts() {
		return [
			[
				'id'       => 'tsf-tt',
				'type'     => 'css',
				'deps'     => [],
				'autoload' => true,
				'hasrtl'   => false,
				'name'     => 'tt',
				'base'     => THE_SEO_FRAMEWORK_DIR_URL . 'lib/css/',
				'ver'      => THE_SEO_FRAMEWORK_VERSION,
				'inline'   => [
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
					'.tsf-tooltip-text' => [
						\is_rtl() ? 'direction:rtl' : '',
					],
				],
			],
			[
				'id'       => 'tsf-tt',
				'type'     => 'js',
				'deps'     => [ 'jquery' ],
				'autoload' => true,
				'name'     => 'tt',
				'base'     => THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => THE_SEO_FRAMEWORK_VERSION,
			],
		];
	}

	/**
	 * Returns default scripts' localization.
	 *
	 * @since 3.3.0
	 *
	 * @return string The default scripts localization.
	 */
	private static function get_tsf_l10n_data() {

		$tsf = \the_seo_framework();

		query: {
			$is_settings_page = $tsf->is_seo_settings_page();
			$is_post_edit     = $tsf->is_post_edit();
			$is_term_edit     = $tsf->is_term_edit();

			$_page_on_front = $tsf->has_page_on_front();

			$id       = $is_settings_page ? $tsf->get_the_front_page_ID() : $tsf->get_the_real_ID();
			$taxonomy = $tsf->get_current_taxonomy();

			$is_home = $is_settings_page || ( ! $taxonomy && $tsf->is_static_frontpage( $id ) );

			$_query = compact( 'id', 'taxonomy' );
		}

		meta: {
			$has_input = $is_settings_page || $is_post_edit || $is_term_edit;
		}

		title: {
			$use_title_additions = $tsf->use_title_branding( $_query );
			$_title_branding     = $tsf->get_title_branding_from_args( $_query );
			$title_additions     = $_title_branding['addition'];
			$title_location      = $_title_branding['seplocation'];
			$title_separator     = \esc_html( $tsf->get_title_separator( $is_home ) );
			// Smart code... make this readable? Also, the custom field can't be filtered...
			$default_title       =
				   ( $is_settings_page && $_page_on_front ? $tsf->get_custom_field( '_genesis_title', $_query['id'] ) : '' )
				?: ( ! $is_settings_page && $is_home ? $tsf->get_option( 'homepage_title' ) : '' )
				?: ( $tsf->get_filtered_raw_generated_title( $_query ) );
		}

		description: {}

		other: {
			$_decode_flags = ENT_QUOTES | ENT_COMPAT;
		}

		social: {
			$social_settings_locks = [];

			if ( $_page_on_front ) {
				if ( $is_settings_page ) {
					// PH = placeholder
					$social_settings_locks = [
						'ogTitlePHLock'       => (bool) $tsf->get_custom_field( '_open_graph_title', $id ),
						'ogDescriptionPHLock' => (bool) $tsf->get_custom_field( '_open_graph_description', $id ),
						'twTitlePHLock'       => (bool) $tsf->get_custom_field( '_twitter_title', $id ),
						'twDescriptionPHLock' => (bool) $tsf->get_custom_field( '_twitter_description', $id ),
					];
				} elseif ( $is_home ) {
					$social_settings_locks = [
						'refTitleLock'       => (bool) $tsf->get_option( 'homepage_title' ),
						'refDescriptionLock' => (bool) $tsf->get_option( 'homepage_description' ),
						'ogTitleLock'        => (bool) $tsf->get_option( 'homepage_og_title' ),
						'ogDescriptionLock'  => (bool) $tsf->get_option( 'homepage_og_description' ),
						'twTitleLock'        => (bool) $tsf->get_option( 'homepage_twitter_title' ),
						'twDescriptionLock'  => (bool) $tsf->get_option( 'homepage_twitter_description' ),
					];
				}
			}

			$social_settings_placeholders = [];

			if ( $is_post_edit || $is_settings_page ) {
				if ( $is_settings_page ) {
					if ( $_page_on_front ) {
						$social_settings_placeholders = [
							'ogDesc' => $tsf->get_custom_field( '_genesis_description', $id ) ?: $tsf->get_generated_open_graph_description( [ 'id' => $id ] ),
							'twDesc' => $tsf->get_custom_field( '_genesis_description', $id ) ?: $tsf->get_generated_twitter_description( [ 'id' => $id ] ),
						];
					} else {
						$social_settings_placeholders = [
							'ogDesc' => $tsf->get_generated_open_graph_description( [ 'id' => $id ] ),
							'twDesc' => $tsf->get_generated_twitter_description( [ 'id' => $id ] ),
						];
					}
				} elseif ( $is_home ) {
					$social_settings_placeholders = [
						'ogDesc' => $tsf->get_option( 'homepage_description' ) ?: $tsf->get_generated_open_graph_description( [ 'id' => $id ] ),
						'twDesc' => $tsf->get_option( 'homepage_description' ) ?: $tsf->get_generated_twitter_description( [ 'id' => $id ] ),
					];
				} else {
					$social_settings_placeholders = [
						'ogDesc' => $tsf->get_generated_open_graph_description( [ 'id' => $id ] ),
						'twDesc' => $tsf->get_generated_twitter_description( [ 'id' => $id ] ),
					];
				}

				foreach ( $social_settings_placeholders as &$v ) {
					$v = html_entity_decode( $v, $_decode_flags, 'UTF-8' );
				}
			}
		}

		$l10n = [
			'nonces' => [
				/**
				 * Use $tsf->get_settings_capability() ?... might conflict with other nonces.
				 * @augments tsfMedia 'upload_files'
				 */
				'manage_options' => \current_user_can( 'manage_options' ) ? \wp_create_nonce( 'tsf-ajax-manage_options' ) : false,
				'upload_files'   => \current_user_can( 'upload_files' ) ? \wp_create_nonce( 'tsf-ajax-upload_files' ) : false,
				'edit_posts'     => \current_user_can( 'edit_posts' ) ? \wp_create_nonce( 'tsf-ajax-edit_posts' ) : false,
			],
			'states' => [
				'isRTL'               => (bool) \is_rtl(),
				'isHome'              => $is_home,
				'hasInput'            => $has_input,
				'useTagline'          => $use_title_additions,
				'taglineLocked'       => (bool) $tsf->get_option( 'title_rem_additions' ),
				'isSettingsPage'      => $is_settings_page,
				'isPostEdit'          => $is_post_edit,
				'isPrivate'           => $has_input && $is_post_edit && $id && $tsf->is_private( $id ),
				'isPasswordProtected' => $has_input && $is_post_edit && $id && $tsf->is_password_protected( $id ),
				'debug'               => $tsf->script_debug,
				'homeLocks'           => $social_settings_locks,
				'stripTitleTags'      => (bool) $tsf->get_option( 'title_strip_tags' ),
				'isGutenbergPage'     => $tsf->is_gutenberg_page(),
			],
			'i18n'   => [
				// phpcs:ignore -- WordPress doesn't have a comment, either.
				'privateTitle'    => $has_input && $id ? trim( str_replace( '%s', '', \__( 'Private: %s', 'default' ) ) ) : '',
				// phpcs:ignore -- WordPress doesn't have a comment, either.
				'protectedTitle'  => $has_input && $id ? trim( str_replace( '%s', '', \__( 'Protected: %s', 'default' ) ) ) : '',
			],
			'params' => [
				'defaultTitle'       => $tsf->s_title_raw( $default_title ),
				'titleAdditions'     => $tsf->s_title_raw( $title_additions ),
				'blogDescription'    => $tsf->s_title_raw( $tsf->get_blogdescription() ),
				'untitledTitle'      => $tsf->s_title_raw( $tsf->get_static_untitled_title() ),
				'titleSeparator'     => $title_separator,
				'titleLocation'      => $title_location,
				'socialPlaceholders' => $social_settings_placeholders,
			],
		];

		foreach ( [ 'i18n', 'params' ] as $key ) {
			foreach ( $l10n[ $key ] as &$v ) {
				if ( is_scalar( $v ) )
					$v = html_entity_decode( $v, $_decode_flags, 'UTF-8' );
			}
		}

		/**
		 * @since 3.0.0
		 * @param array $l10n The JS l10n values.
		 */
		return (array) \apply_filters( 'the_seo_framework_js_l10n', $l10n );
	}

	/**
	 * Returns AYS (Are you sure?) scripts params.
	 *
	 * @since 3.3.0
	 *
	 * @return array The script params.
	 */
	public static function get_ays_scripts() {
		return [
			[
				'id'       => 'tsf-ays',
				'type'     => 'js',
				'deps'     => [ 'jquery' ],
				'autoload' => true,
				'name'     => 'ays',
				'base'     => THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => THE_SEO_FRAMEWORK_VERSION,
				'l10n'     => [
					'name' => 'tsfAysL10n',
					'data' => [
						'i18n' => [
							'saveAlert' => \__( 'The changes you made will be lost if you navigate away from this page.', 'autodescription' ),
						],
					],
				],
			],
		];
	}

	/**
	 * Returns Post edit scripts params.
	 *
	 * @since 3.3.0
	 *
	 * @return array The script params.
	 */
	public static function get_post_scripts() {
		return [
			[
				'id'       => 'tsf-post',
				'type'     => 'js',
				'deps'     => [ 'jquery', 'tsf-ays', 'tsf-tt', 'tsf' ],
				'autoload' => true,
				'name'     => 'post',
				'base'     => THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => THE_SEO_FRAMEWORK_VERSION,
				'l10n'     => [
					'name' => 'tsfPostL10n',
					'data' => [],
				],
			],
			[
				'id'       => 'tsf-post',
				'type'     => 'css',
				'deps'     => [ 'tsf-tt', 'tsf' ],
				'autoload' => true,
				'hasrtl'   => true,
				'name'     => 'post',
				'base'     => THE_SEO_FRAMEWORK_DIR_URL . 'lib/css/',
				'ver'      => THE_SEO_FRAMEWORK_VERSION,
				'inline'   => [
					'.tsf-flex-nav-tab .tsf-flex-nav-tab-radio:checked + .tsf-flex-nav-tab-label' => [
						'box-shadow:0 -2px 0 0 {{$color_accent}} inset, 0 0 0 0 {{$color_accent}} inset',
					],
					'.tsf-flex-nav-tab .tsf-flex-nav-tab-radio:focus + .tsf-flex-nav-tab-label:not(.tsf-no-focus-ring)' => [
						'box-shadow:0 -2px 0 0 {{$color_accent}} inset, 0 0 0 1px {{$color_accent}} inset',
					],
				],
			],
		];
	}

	/**
	 * Returns Term scripts params.
	 *
	 * @since 3.3.0
	 *
	 * @return array The script params.
	 */
	public static function get_term_scripts() {
		return [
			// [
			// 	'id'       => 'tsf-term',
			// 	'type'     => 'js',
			// 	'deps'     => [ 'jquery', 'tsf-ays', 'tsf-tt', 'tsf' ],
			// 	'autoload' => true,
			// 	'name'     => 'term',
			// 	'base'     => THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
			// 	'ver'      => THE_SEO_FRAMEWORK_VERSION,
			// 	'l10n'     => [
			// 		'name' => 'tsfTermL10n',
			// 		'data' => [],
			// 	],
			// ],
			// [
			// 	'id'       => 'tsf-term',
			// 	'type'     => 'css',
			// 	'deps'     => [ 'tsf-tt', 'tsf' ],
			// 	'autoload' => true,
			// 	'hasrtl'   => false,
			// 	'name'     => 'term',
			// 	'base'     => THE_SEO_FRAMEWORK_DIR_URL . 'lib/css/',
			// 	'ver'      => THE_SEO_FRAMEWORK_VERSION,
			// ],
		];
	}

	/**
	 * Returns Gutenberg compatibility scripts params.
	 *
	 * @since 3.3.0
	 *
	 * @return array The script params.
	 */
	public static function get_gutenberg_compat_scripts() {
		return [
			[
				'id'       => 'tsf-gbc',
				'type'     => 'js',
				'deps'     => [ 'jquery', 'tsf', 'tsf-post', 'wp-editor', 'wp-data', 'lodash', 'react' ],
				'autoload' => true,
				'name'     => 'tsf-gbc',
				'base'     => THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => THE_SEO_FRAMEWORK_VERSION,
				'l10n'     => [
					'name' => 'tsfGBCL10n',
					'data' => [],
				],
			],
		];
	}

	/**
	 * Returns Media scripts params.
	 *
	 * @since 3.3.0
	 *
	 * @return array The script params.
	 */
	public static function get_media_scripts() {
		return [
			'id'       => 'tsf-media',
			'type'     => 'js',
			'deps'     => [ 'jquery', 'media', 'tsf' ],
			'autoload' => true,
			'name'     => 'media',
			'base'     => THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
			'ver'      => THE_SEO_FRAMEWORK_VERSION,
			'l10n'     => [
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
		];
	}

	/**
	 * Returns Primary Term Selection scripts params.
	 *
	 * @since 3.3.0
	 *
	 * @return array The script params.
	 */
	public static function get_primaryterm_scripts() {

		$tsf = \the_seo_framework();

		$id = $tsf->get_the_real_admin_ID();

		$post_type   = \get_post_type( $id );
		$_taxonomies = $post_type ? $tsf->get_hierarchical_taxonomies_as( 'objects', $post_type ) : [];
		$taxonomies  = [];

		$gutenberg = $tsf->is_gutenberg_page();

		foreach ( $_taxonomies as $_t ) {
			$singular_name = $tsf->get_tax_type_label( $_t->name );

			$taxonomies[ $_t->name ] = [
				'name'    => $_t->name,
				'primary' => $tsf->get_primary_term_id( $id, $_t->name ) ?: 0,
			] + (
				$gutenberg ? [
					'i18n' => [
						/* translators: %s = term name */
						'selectPrimary' => sprintf( \esc_html__( 'Select Primary %s', 'autodescription' ), $singular_name ),
					],
				] : [
					'i18n' => [
						/* translators: %s = term name */
						'makePrimary' => sprintf( \esc_html__( 'Make primary %s', 'autodescription' ), strtolower( $singular_name ) ),
						/* translators: %s = term name */
						'primary'     => sprintf( \esc_html__( 'Primary %s', 'autodescription' ), strtolower( $singular_name ) ),
						'name'        => strtolower( $singular_name ),
					],
				]
			);
		}

		$inline_css = [];
		if ( \is_rtl() ) {
			$inline_css = [
				'.tsf-primary-term-selector' => [
					'float:left;',
				],
				'.tsf-primary-term-selector-help-wrap' => [
					'left:25px;',
					'right:initial;',
				],
			];
		}

		if ( $gutenberg ) {
			$vars = [
				'id'   => 'tsf-pt-gb',
				'name' => 'pt-gb',
			];
			$deps = [ 'jquery', 'tsf', 'tsf-post', 'wp-hooks', 'wp-element', 'wp-components', 'wp-url', 'wp-api-fetch', 'lodash', 'react' ];
		} else {
			$vars = [
				'id'   => 'tsf-pt',
				'name' => 'pt',
			];
			$deps = [ 'jquery', 'tsf', 'tsf-post', 'tsf-tt' ];
		}

		return [
			[
				'id'       => $vars['id'],
				'type'     => 'js',
				'deps'     => $deps,
				'autoload' => true,
				'name'     => $vars['name'],
				'base'     => THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => THE_SEO_FRAMEWORK_VERSION,
				'l10n'     => [
					'name' => 'tsfPTL10n',
					'data' => [
						'taxonomies' => $taxonomies,
					],
				],
				'tmpl'     => [
					'file' => $tsf->get_view_location( 'templates/inpost/primary-term-selector' ),
				],
			],
			[
				'id'       => 'tsf-pt',
				'type'     => 'css',
				'deps'     => [ 'tsf-tt' ],
				'autoload' => true,
				'hasrtl'   => false,
				'name'     => 'pt',
				'base'     => THE_SEO_FRAMEWORK_DIR_URL . 'lib/css/',
				'ver'      => THE_SEO_FRAMEWORK_VERSION,
				'inline'   => $inline_css,
			],
		];
	}

	/**
	 * Returns the Pixel and Character counter script params.
	 *
	 * @since 3.3.0
	 *
	 * @return array The script params.
	 */
	public static function get_counter_scripts() {

		$tsf = \the_seo_framework();

		return [
			[
				'id'       => 'tsf-c',
				'type'     => 'js',
				'deps'     => [ 'jquery', 'tsf-tt', 'tsf' ],
				'autoload' => true,
				'name'     => 'tsfc',
				'base'     => THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => THE_SEO_FRAMEWORK_VERSION,
				'l10n'     => [
					'name' => 'tsfCL10n',
					'data' => [
						'guidelines'  => $tsf->get_input_guidelines(),
						'counterType' => \absint( $tsf->get_user_option( 0, 'counter_type', 3 ) ),
						'i18n'        => [
							'guidelines' => $tsf->get_input_guidelines_i18n(),
							/* translators: Pixel counter. 1: number (value), 2: number (guideline) */
							'pixelsUsed' => \esc_attr__( '%1$d out of %2$d pixels are used.', 'autodescription' ),
						],
					],
				],
			],
			[
				'id'       => 'tsf-c',
				'type'     => 'css',
				'deps'     => [ 'tsf-tt' ],
				'autoload' => true,
				'hasrtl'   => true,
				'name'     => 'tsfc',
				'base'     => THE_SEO_FRAMEWORK_DIR_URL . 'lib/css/',
				'ver'      => THE_SEO_FRAMEWORK_VERSION,
			],
		];
	}

	/**
	 * Returns the SEO Settings page script params.
	 *
	 * @since 3.3.0
	 *
	 * @return array The script params.
	 */
	public static function get_settings_scripts() {
		return [
			[
				'id'       => 'tsf-settings',
				'type'     => 'js',
				'deps'     => [ 'jquery', 'tsf-ays', 'tsf', 'tsf-tt', 'wp-color-picker' ],
				'autoload' => true,
				'name'     => 'settings',
				'base'     => THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => THE_SEO_FRAMEWORK_VERSION,
				'l10n'     => [
					'name' => 'tsfSettingsL10n',
					'data' => [
						'i18n' => [
							'confirmReset' => \__( 'Are you sure you want to reset all SEO settings to their defaults?', 'autodescription' ),
						],
					],
				],
			],
			[
				'id'       => 'tsf-settings',
				'type'     => 'css',
				'deps'     => [ 'tsf', 'tsf-tt', 'wp-color-picker' ],
				'autoload' => true,
				'hasrtl'   => true,
				'name'     => 'settings',
				'base'     => THE_SEO_FRAMEWORK_DIR_URL . 'lib/css/',
				'ver'      => THE_SEO_FRAMEWORK_VERSION,
			],
		];
	}
}

$_load_scripts_class();
