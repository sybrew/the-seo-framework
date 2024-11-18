<?php
/**
 * @package The_SEO_Framework\Classes\Admin\Script\Loader
 * @subpackage The_SEO_Framework\Scripts
 */

namespace The_SEO_Framework\Admin\Script;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\{
	Data,
	Meta,
};
use \The_SEO_Framework\Helper\{
	Compatibility,
	Guidelines,
	Format\Arrays,
	Query,
	Taxonomy,
	Template,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Prepares admin GUI scripts. Auto-invokes everything the moment this file is required.
 * Relies on \The_SEO_Framework\Admin\Script\Registry to register and load scripts.
 *
 * What's a state, and what's a param?
 * - states may and are expected to be changed, like a page title.
 * - params shouldn't change, like the page ID.
 *
 * @since 5.0.0
 * @see \The_SEO_Framework\Admin\Script\Registry
 * @access private
 */
class Loader {

	/**
	 * Initializes scripts based on admin query.
	 *
	 * @since 5.0.0
	 * @access private
	 */
	public static function init() {

		$scripts = [
			static::get_common_scripts(),
		];

		if ( Query::is_post_edit() ) {
			static::prepare_media_scripts();

			$scripts[] = static::get_post_edit_scripts();
			$scripts[] = static::get_tabs_scripts();
			$scripts[] = static::get_media_scripts();
			$scripts[] = static::get_title_scripts();
			$scripts[] = static::get_description_scripts();
			$scripts[] = static::get_social_scripts();
			$scripts[] = static::get_canonical_scripts();
			$scripts[] = static::get_primaryterm_scripts();
			$scripts[] = static::get_ays_scripts();

			if ( Data\Plugin::get_option( 'display_pixel_counter' ) || Data\Plugin::get_option( 'display_character_counter' ) )
				$scripts[] = static::get_counter_scripts();

			if ( Query::is_block_editor() )
				$scripts[] = static::get_gutenberg_compat_scripts();
		} elseif ( Query::is_term_edit() ) {
			static::prepare_media_scripts();

			$scripts[] = static::get_term_edit_scripts();
			$scripts[] = static::get_media_scripts();
			$scripts[] = static::get_title_scripts();
			$scripts[] = static::get_description_scripts();
			$scripts[] = static::get_social_scripts();
			$scripts[] = static::get_canonical_scripts();
			$scripts[] = static::get_ays_scripts();

			if ( Data\Plugin::get_option( 'display_pixel_counter' ) || Data\Plugin::get_option( 'display_character_counter' ) )
				$scripts[] = static::get_counter_scripts();
		} elseif ( Query::is_wp_lists_edit() ) {
			$scripts[] = static::get_list_edit_scripts();
			$scripts[] = static::get_title_scripts();
			$scripts[] = static::get_description_scripts();
			$scripts[] = static::get_canonical_scripts();

			if ( Data\Plugin::get_option( 'display_pixel_counter' ) || Data\Plugin::get_option( 'display_character_counter' ) )
				$scripts[] = static::get_counter_scripts();
		} elseif ( Query::is_seo_settings_page() ) {
			static::prepare_media_scripts();
			static::prepare_metabox_scripts();

			$scripts[] = static::get_seo_settings_scripts();
			$scripts[] = static::get_tabs_scripts();
			$scripts[] = static::get_media_scripts();
			$scripts[] = static::get_title_scripts();
			$scripts[] = static::get_description_scripts();
			$scripts[] = static::get_social_scripts();
			$scripts[] = static::get_canonical_scripts();
			$scripts[] = static::get_ays_scripts();

			// Always load unconditionally, options may enable the counters dynamically.
			$scripts[] = static::get_counter_scripts();
		}

		/**
		 * @since 3.1.0
		 * @since 4.0.0 1. Now holds all scripts.
		 *              2. Added $loader parameter.
		 * @since 4.2.7 Consolidated all input scripts into a list.
		 * @param array  $scripts  The default CSS and JS loader settings.
		 * @param string $registry The \The_SEO_Framework\Admin\Script\Registry registry class name.
		 * @param string $loader   The \The_SEO_Framework\Admin\Script\Loader loader class name.
		 */
		$scripts = \apply_filters(
			'the_seo_framework_scripts',
			// Flattening is 3% of this method's total time, we can improve by simplifying the getters above like do_meta_output().
			Arrays::flatten_list( $scripts ),
			Registry::class,
			Loader::class,
		);

		Registry::register( $scripts );
	}

	/**
	 * Prepares WordPress Media scripts.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Resolved PHP notices by not setting the 'post' indexed on new posts.
	 */
	public static function prepare_media_scripts() {

		$args = [];

		if ( Query::is_post_edit() )
			$args['post'] = Query::get_the_real_admin_id();

		\wp_enqueue_media( $args );
	}

	/**
	 * Prepares WordPress meta box scripts.
	 *
	 * @since 4.0.0
	 */
	public static function prepare_metabox_scripts() {
		\wp_enqueue_script( 'common' );
		\wp_enqueue_script( 'wp-lists' );
		\wp_enqueue_script( 'postbox' );
	}

	/**
	 * Returns the common TSF scripts.
	 *
	 * @since 5.1.0
	 *
	 * @return array The script params.
	 */
	public static function get_common_scripts() {
		return [
			// Load TSF-utils first. TODO split the TSF object so that they will no longer become reliant upon eachother.
			[
				'id'       => 'tsf-utils',
				'type'     => 'js',
				'deps'     => [],
				'autoload' => true,
				'name'     => 'utils',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
			],
			[
				'id'       => 'tsf',
				'type'     => 'css',
				'deps'     => [ 'dashicons' ],
				'autoload' => true,
				'name'     => 'tsf',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/css/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
			],
			[
				'id'       => 'tsf',
				'type'     => 'js',
				'deps'     => [ 'wp-util', 'tsf-utils' ],
				'autoload' => true,
				'name'     => 'tsf',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
				'l10n'     => [
					'name' => 'tsfL10n',
					'data' => [
						'nonces' => [
							/**
							 * Use THE_SEO_FRAMEWORK_SETTINGS_CAP ?... might conflict with other nonces.
							 * -> Just add it to the end, if it matches the existing ones, that's fine (just double work).
							 * If we do this, also add it to "states" or something.
							 */
							'manage_options' => Utils::create_ajax_capability_nonce( 'manage_options' ), // unused
							'upload_files'   => Utils::create_ajax_capability_nonce( 'upload_files' ), // unused
							'edit_posts'     => Utils::create_ajax_capability_nonce( 'edit_posts' ),
						],
						'states' => [
							'debug' => \SCRIPT_DEBUG,
						],
					],
				],
			],
			[
				'id'       => 'tsf-tt',
				'type'     => 'css',
				'deps'     => [],
				'autoload' => true,
				'name'     => 'tt',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/css/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
				'inline'   => [
					'.tsf-tooltip-text-wrap'   => [
						'background-color:{{$bg_accent}}',
						'color:{{$rel_bg_accent}}',
					],
					'.tsf-tooltip-text-wrap *' => [
						'color:{{$rel_bg_accent}}',
					],
					'.tsf-tooltip-arrow:after' => [
						'border-top-color:{{$bg_accent}}',
					],
					'.tsf-tooltip-down .tsf-tooltip-arrow:after' => [
						'border-bottom-color:{{$bg_accent}}',
					],
					'.tsf-tooltip-text'        => [
						\is_rtl() ? 'direction:rtl' : '',
					],
				],
			],
			[
				'id'       => 'tsf-tt',
				'type'     => 'js',
				'deps'     => [ 'tsf' ],
				'autoload' => true,
				'name'     => 'tt',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
			],
			[
				'id'       => 'tsf-ui',
				'type'     => 'css',
				'deps'     => [ 'tsf', 'dashicons' ],
				'autoload' => true,
				'name'     => 'ui',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/css/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
			],
			[
				'id'       => 'tsf-ui',
				'type'     => 'js',
				'deps'     => [ 'tsf', 'tsf-utils', 'jquery' ],
				'autoload' => true,
				'name'     => 'ui',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
			],
		];
	}

	/**
	 * Returns AYS (Are you sure?) scripts params.
	 *
	 * @since 4.0.0
	 *
	 * @return array The script params.
	 */
	public static function get_ays_scripts() {
		return [
			[
				'id'       => 'tsf-ays',
				'type'     => 'js',
				'deps'     => [ 'tsf', 'tsf-utils' ],
				'autoload' => true,
				'name'     => 'ays',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
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
	 * Returns LE (List Edit) scripts params.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now depends on title and description scripts.
	 * @since 4.2.0 No longer registers l10n (data).
	 *
	 * @return array The script params.
	 */
	public static function get_list_edit_scripts() {
		return [
			[
				'id'       => 'tsf-le',
				'type'     => 'css',
				'deps'     => [ 'tsf' ],
				'autoload' => true,
				'name'     => 'le',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/css/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
			],
			[
				'id'       => 'tsf-le',
				'type'     => 'js',
				'deps'     => [ 'tsf-title', 'tsf-description', 'tsf-canonical', 'tsf-postslugs', 'tsf-termslugs', 'tsf-authorslugs', 'tsf', 'tsf-tt', 'tsf-utils' ],
				'autoload' => true,
				'name'     => 'le',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
			],
		];
	}

	/**
	 * Returns the SEO Settings page script params.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Updated l10n.data.
	 *
	 * @return array The script params.
	 */
	public static function get_seo_settings_scripts() {

		$front_id = Query::get_the_front_page_id();

		return [
			[
				'id'       => 'tsf-settings',
				'type'     => 'css',
				'deps'     => [ 'tsf', 'tsf-tt', 'wp-color-picker', 'dashicons' ],
				'autoload' => true,
				'name'     => 'settings',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/css/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
			],
			[
				'id'       => 'tsf-settings',
				'type'     => 'js',
				'deps'     => [ 'jquery', 'tsf-ays', 'tsf-title', 'tsf-description', 'tsf-social', 'tsf-canonical', 'tsf', 'tsf-tabs', 'tsf-tt', 'wp-color-picker', 'wp-util' ],
				'autoload' => true,
				'name'     => 'settings',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
				'l10n'     => [
					'name' => 'tsfSettingsL10n',
					'data' => [
						'states' => [
							'isFrontPrivate'   => $front_id && Data\Post::is_private( $front_id ),
							'isFrontProtected' => $front_id && Data\Post::is_password_protected( $front_id ),
						],
					],
				],
				'tmpl'     => [
					'file' => Template::get_view_location( 'templates/settings/warnings' ),
				],
			],
		];
	}

	/**
	 * Returns Post edit scripts params.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Updated l10n.data.
	 *
	 * @return array The script params.
	 */
	public static function get_post_edit_scripts() {

		$id = Query::get_the_real_id();

		$is_static_front_page = Query::is_static_front_page( $id );
		$is_block_editor      = Query::is_block_editor();

		if ( $is_static_front_page ) {
			$additions_forced_disabled = ! Data\Plugin::get_option( 'homepage_tagline' );
			$additions_forced_enabled  = ! $additions_forced_disabled;
		} else {
			$additions_forced_disabled = (bool) Data\Plugin::get_option( 'title_rem_additions' );
			$additions_forced_enabled  = false;
		}

		return [
			[
				'id'       => 'tsf-post',
				'type'     => 'css',
				'deps'     => [ 'tsf-tt', 'tsf', 'tsf-ui' ],
				'autoload' => true,
				'name'     => 'post',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/css/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
				'inline'   => [
					'.tsf-flex-nav-tab .tsf-flex-nav-tab-radio:checked + .tsf-flex-nav-tab-label' => [
						'box-shadow:0 -2px 0 0 {{$color_accent}} inset, 0 0 0 0 {{$color_accent}} inset',
					],
					'.tsf-flex-nav-tab .tsf-flex-nav-tab-radio:focus + .tsf-flex-nav-tab-label:not(.tsf-no-focus-ring)' => [
						'box-shadow:0 -2px 0 0 {{$color_accent}} inset, 0 0 0 1px {{$color_accent}} inset',
					],
				],
			],
			[
				'id'       => 'tsf-post',
				'type'     => 'js',
				'deps'     => [ 'tsf-ays', 'tsf-title', 'tsf-description', 'tsf-social', 'tsf-canonical', 'tsf-postslugs', 'tsf-termslugs', 'tsf-authorslugs', 'tsf-tabs', 'tsf-tt', 'tsf-utils', 'tsf-ui', 'tsf' ],
				'autoload' => true,
				'name'     => 'post',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
				'l10n'     => [
					'name' => 'tsfPostL10n',
					'data' => [
						'states' => [
							'isPrivate'       => Data\Post::is_private( $id ),
							'isProtected'     => Data\Post::is_password_protected( $id ),
							'isGutenbergPage' => $is_block_editor, // TODO: Deprecate
							'id'              => $id, // TODO: Deprecate
						],
						'params' => [
							'id'                      => $id,
							'isBlockEditor'           => $is_block_editor,
							'isFront'                 => $is_static_front_page,
							'additionsForcedDisabled' => $additions_forced_disabled,
							'additionsForcedEnabled'  => $additions_forced_enabled,
						],
						'nonces' => [
							'edit_post' => [
								$id => Utils::create_ajax_capability_nonce( 'edit_post', $id ),
							],
						],
					],
				],
			],
		];
	}

	/**
	 * Returns Term scripts params.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Updated l10n.data.
	 * @since 4.2.0 Now properly populates use_generated_archive_prefix() with a \WP_Term object.
	 *
	 * @return array The script params.
	 */
	public static function get_term_edit_scripts() {

		$id       = Query::get_the_real_id();
		$taxonomy = Query::get_current_taxonomy();

		$additions_forced_disabled = (bool) Data\Plugin::get_option( 'title_rem_additions' );

		if ( Meta\Title\Conditions::use_generated_archive_prefix( \get_term( $id, $taxonomy ) ) ) {
			$term_prefix = \sprintf(
				/* translators: %s: Taxonomy singular name. */
				\_x( '%s:', 'taxonomy term archive title prefix', 'default' ),
				Taxonomy::get_label( $taxonomy ),
			);
		} else {
			$term_prefix = '';
		}

		return [
			[
				'id'       => 'tsf-term',
				'type'     => 'css',
				'deps'     => [ 'tsf-tt', 'tsf' ],
				'autoload' => true,
				'name'     => 'term',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/css/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
			],
			[
				'id'       => 'tsf-term',
				'type'     => 'js',
				'deps'     => [ 'tsf-ays', 'tsf-title', 'tsf-description', 'tsf-social', 'tsf-canonical', 'tsf-termslugs', 'tsf-tt', 'tsf' ],
				'autoload' => true,
				'name'     => 'term',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
				'l10n'     => [
					'name' => 'tsfTermL10n',
					'data' => [
						'params' => [
							'additionsForcedDisabled' => $additions_forced_disabled,
							'id'                      => $id,
							'taxonomy'                => $taxonomy,
							'termPrefix'              => Utils::decode_entities( $term_prefix ),
						],
						'nonces' => [
							'edit_term' => [
								$id => Utils::create_ajax_capability_nonce( 'edit_term', $id ),
							],
						],
					],
				],
			],
		];
	}

	/**
	 * Returns Gutenberg compatibility scripts params.
	 *
	 * @since 4.0.0
	 * @since 4.2.0 No longer registers l10n (data).
	 *
	 * @return array The script params.
	 */
	public static function get_gutenberg_compat_scripts() {
		return [
			[
				'id'       => 'tsf-gbc',
				'type'     => 'js',
				'deps'     => [ 'jquery', 'tsf', 'tsf-utils', 'wp-editor', 'wp-data', 'react' ],
				'autoload' => true,
				'name'     => 'gbc',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
			],
		];
	}

	/**
	 * Returns Tabs scripts params.
	 *
	 * @since 4.1.3
	 * @since 4.2.0 No longer registers l10n (data).
	 *
	 * @return array The script params.
	 */
	public static function get_tabs_scripts() {
		return [
			'id'       => 'tsf-tabs',
			'type'     => 'js',
			'deps'     => [ 'tsf-utils', 'tsf-ui' ],
			'autoload' => true,
			'name'     => 'tabs',
			'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
			'ver'      => \THE_SEO_FRAMEWORK_VERSION,
		];
	}

	/**
	 * Returns Media scripts params.
	 *
	 * @since 4.0.0
	 * @since 4.1.2 Removed redundant button titles.
	 * @since 5.1.0 Added tsf-media CSS. Added `tsfMediaL10n.warning`.
	 *
	 * @return array The script params.
	 */
	public static function get_media_scripts() {
		return [
			[
				'id'       => 'tsf-media',
				'type'     => 'css',
				'deps'     => [],
				'autoload' => true,
				'name'     => 'media',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/css/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
			],
			[
				'id'       => 'tsf-media',
				'type'     => 'js',
				'deps'     => [ 'media', 'tsf', 'tsf-utils', 'tsf-tt' ],
				'autoload' => true,
				'name'     => 'media',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
				'l10n'     => [
					'name' => 'tsfMediaL10n',
					'data' => [
						'labels'  => [
							'social' => [
								'imgSelect'      => \esc_attr__( 'Select Image', 'autodescription' ),
								'imgSelectTitle' => '',
								'imgChange'      => \esc_attr__( 'Change Image', 'autodescription' ),
								'imgRemove'      => \esc_attr__( 'Remove Image', 'autodescription' ),
								'imgRemoveTitle' => '',
								'imgFrameTitle'  => \esc_attr_x( 'Select Social Image', 'Frame title', 'autodescription' ),
								'imgFrameButton' => \esc_attr__( 'Use this image', 'autodescription' ),
							],
							'logo'   => [
								'imgSelect'      => \esc_attr__( 'Select Logo', 'autodescription' ),
								'imgSelectTitle' => '',
								'imgChange'      => \esc_attr__( 'Change Logo', 'autodescription' ),
								'imgRemove'      => \esc_attr__( 'Remove Logo', 'autodescription' ),
								'imgRemoveTitle' => '',
								'imgFrameTitle'  => \esc_attr_x( 'Select Logo', 'Frame title', 'autodescription' ),
								'imgFrameButton' => \esc_attr__( 'Use this image', 'autodescription' ),
							],
						],
						'warning' => [
							'warnedTypes'    => [
								// This is only a short list of increasingly common types.
								'webp' => 'image/webp',
								'heic' => 'image/heic',
							],
							'forbiddenTypes' => [
								// See The_SEO_Framework\Data\Filter\Sanitize::image_details().
								'apng' => 'image/apng',
								'bmp'  => 'image/bmp',
								'ico'  => 'image/x-icon',
								'cur'  => 'image/x-icon',
								'svg'  => 'image/svg+xml',
								'tif'  => 'image/tiff',
								'tiff' => 'image/tiff',
							],
							'i18n'           => [
								'notLoaded'    => \esc_attr__( 'The image file could not be loaded.', 'autodescription' ),
								/* translators: %s is the file extension. */
								'extWarned'    => \esc_attr__( 'The file extension "%s" is not supported on all platforms, preventing your image from being displayed.', 'autodescription' ),
								/* translators: %s is the file extension. */
								'extForbidden' => \esc_attr__( 'The file extension "%s" is not supported. Choose a different file.', 'autodescription' ),
							],
						],
						'nonce'   => Utils::create_ajax_capability_nonce( 'upload_files' ),
					],
				],
			],
		];
	}

	/**
	 * Returns Title scripts params.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Updated l10n.data.
	 *
	 * @return array The script params.
	 */
	public static function get_title_scripts() {
		return [
			'id'       => 'tsf-title',
			'type'     => 'js',
			'deps'     => [ 'tsf' ],
			'autoload' => true,
			'name'     => 'title',
			'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
			'ver'      => \THE_SEO_FRAMEWORK_VERSION,
			'l10n'     => [
				'name' => 'tsfTitleL10n',
				'data' => [
					'states' => [
						'titleSeparator'  => Utils::decode_entities( Meta\Title::get_separator() ),
						'prefixPlacement' => \is_rtl() ? 'after' : 'before',
					],
					'params' => [
						'untitledTitle'  => Utils::decode_entities( Meta\Title::get_untitled_title() ),
						'stripTitleTags' => (bool) Data\Plugin::get_option( 'title_strip_tags' ),
					],
					'i18n'   => [
						// phpcs:ignore, WordPress.WP.I18n -- WordPress doesn't have a comment, either.
						'privateTitle'   => Utils::decode_entities( trim( str_replace( '%s', '', \__( 'Private: %s', 'default' ) ) ) ),
						// phpcs:ignore, WordPress.WP.I18n -- WordPress doesn't have a comment, either.
						'protectedTitle' => Utils::decode_entities( trim( str_replace( '%s', '', \__( 'Protected: %s', 'default' ) ) ) ),
					],
				],
			],
		];
	}

	/**
	 * Returns Description scripts params.
	 *
	 * @since 4.0.0
	 * @since 4.2.0 No longer registers l10n (data).
	 *
	 * @return array The script params.
	 */
	public static function get_description_scripts() {
		return [
			'id'       => 'tsf-description',
			'type'     => 'js',
			'deps'     => [ 'tsf' ],
			'autoload' => true,
			'name'     => 'description',
			'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
			'ver'      => \THE_SEO_FRAMEWORK_VERSION,
		];
	}

	/**
	 * Returns Social scripts params.
	 *
	 * @since 4.0.0
	 * @since 4.2.0 No longer registers l10n (data).
	 *
	 * @return array The script params.
	 */
	public static function get_social_scripts() {
		return [
			'id'       => 'tsf-social',
			'type'     => 'js',
			'deps'     => [ 'tsf', 'tsf-utils' ],
			'autoload' => true,
			'name'     => 'social',
			'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
			'ver'      => \THE_SEO_FRAMEWORK_VERSION,
		];
	}

	/**
	 * Returns Canonical scripts params.
	 *
	 * @since 5.1.0
	 *
	 * @return array The script params.
	 */
	public static function get_canonical_scripts() {
		global $wp_rewrite;

		return [
			[
				'id'       => 'tsf-canonical',
				'type'     => 'js',
				'deps'     => [ 'tsf', 'tsf-utils' ],
				'autoload' => true,
				'name'     => 'canonical',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
				'l10n'     => [
					'name' => 'tsfCanonicalL10n',
					'data' => [
						'params' => [
							'usingPermalinks' => $wp_rewrite->using_permalinks(),
							'rootUrl'         => \home_url( '/' ),
							'rewrite'         => [
								'code'         => $wp_rewrite->rewritecode,
								'replace'      => $wp_rewrite->rewritereplace,
								'queryReplace' => $wp_rewrite->queryreplace,
							],
							// TEMP: We still have to figure out how to get the right parameters. home_url() is probably key in this.
							'allowCanonicalURLNotationTool' => ! Compatibility::get_active_conflicting_plugin_types()['multilingual'],
						],
					],
				],
			],
			[
				'id'       => 'tsf-postslugs',
				'type'     => 'js',
				'deps'     => [],
				'autoload' => false, // Not all screens require this.
				'name'     => 'postslugs',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
			],
			[
				'id'       => 'tsf-termslugs',
				'type'     => 'js',
				'deps'     => [],
				'autoload' => false, // Not all screens require this.
				'name'     => 'termslugs',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
			],
			[
				'id'       => 'tsf-authorslugs',
				'type'     => 'js',
				'deps'     => [],
				'autoload' => false, // Not all screens require this.
				'name'     => 'authorslugs',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
			],
		];
	}

	/**
	 * Returns Primary Term Selection scripts params.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now filters out unsupported taxonomies.
	 * @since 5.1.0 Changed the dependencies for pt, because we now use a select field.
	 *
	 * @return array The script params.
	 */
	public static function get_primaryterm_scripts() {

		$post_id = Query::get_the_real_admin_id();

		$post_type   = Query::get_admin_post_type();
		$_taxonomies = $post_type ? Taxonomy::get_hierarchical( 'names', $post_type ) : [];
		$taxonomies  = [];

		foreach ( $_taxonomies as $tax ) {
			if ( ! Taxonomy::is_supported( $tax ) ) continue;

			$singular_name   = Taxonomy::get_label( $tax );
			$primary_term_id = Data\Plugin\Post::get_primary_term_id( $post_id, $tax );

			$taxonomies[ $tax ] = [
				'name'    => $tax,
				'primary' => $primary_term_id, // if 0, it'll use hints from the interface.
				'i18n'    => [
					/* translators: %s = term name */
					'selectPrimary' => \sprintf( \esc_html__( 'Select primary %s', 'autodescription' ), $singular_name ),
				],
			];
		}

		if ( Query::is_block_editor() ) {
			$vars = [
				'id'   => 'tsf-pt-gb',
				'name' => 'pt-gb',
			];
			$deps = [ 'tsf', 'tsf-ays', 'wp-hooks', 'wp-element', 'wp-components', 'wp-data', 'wp-util' ];
		} else {
			$vars = [
				'id'   => 'tsf-pt',
				'name' => 'pt',
			];
			$deps = [ 'tsf', 'tsf-ays', 'wp-util' ];
		}

		return [
			[
				'id'       => 'tsf-pt',
				'type'     => 'css',
				'deps'     => [ 'tsf-tt' ],
				'autoload' => true,
				'name'     => 'pt',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/css/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
			],
			[
				'id'       => $vars['id'],
				'type'     => 'js',
				'deps'     => $deps,
				'autoload' => true,
				'name'     => $vars['name'],
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
				'l10n'     => [
					'name' => 'tsfPTL10n',
					'data' => [
						'taxonomies' => $taxonomies,
					],
				],
				'tmpl'     => [
					'file' => Template::get_view_location( 'templates/inpost/primary-term-selector' ),
				],
			],
		];
	}

	/**
	 * Returns the Pixel and Character counter script params.
	 *
	 * @since 4.0.0
	 *
	 * @return array The script params.
	 */
	public static function get_counter_scripts() {
		return [
			[
				'id'       => 'tsf-c',
				'type'     => 'css',
				'deps'     => [ 'tsf-tt' ],
				'autoload' => true,
				'name'     => 'tsfc',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/css/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
			],
			[
				'id'       => 'tsf-c',
				'type'     => 'js',
				'deps'     => [ 'tsf-tt', 'tsf' ],
				'autoload' => true,
				'name'     => 'c',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
				'l10n'     => [
					'name' => 'tsfCL10n',
					'data' => [
						'guidelines'  => Guidelines::get_text_size_guidelines(),
						'counterType' => \absint( Data\Plugin\User::get_meta_item( 'counter_type' ) ),
						'i18n'        => [
							'guidelines' => Guidelines::get_text_size_guidelines_i18n(),
							/* translators: Pixel counter. 1: number (value), 2: number (guideline) */
							'pixelsUsed' => \esc_attr__( '%1$d out of %2$d pixels are used.', 'autodescription' ),
						],
					],
				],
			],
		];
	}
}
