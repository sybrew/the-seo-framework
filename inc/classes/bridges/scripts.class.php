<?php
/**
 * @package The_SEO_Framework\Classes\Bridges\Scripts
 * @subpackage The_SEO_Framework\Scripts
 */

namespace The_SEO_Framework\Bridges;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * Prepares admin GUI scripts. Auto-invokes everything the moment this file is required.
 * Relies on \The_SEO_Framework\Builders\Scripts to register and load scripts.
 *
 * What's a state, and what's a param?
 * - states may and are expected to be changed, like a page title.
 * - params shouldn't change, like the page ID.
 *
 * @since 4.0.0
 * @see \The_SEO_Framework\Builders\Scripts
 * @access protected
 * @final Can't be extended.
 */
final class Scripts {

	/**
	 * Prepares the class and loads constructor.
	 *
	 * Use this if the actions need to be registered early, but nothing else of
	 * this class is needed yet.
	 *
	 * @since 4.0.0
	 * @ignore
	 * @deprecated
	 */
	public static function prepare() {}

	/**
	 * Initializes scripts based on admin query.
	 *
	 * @since 4.0.0
	 * @access private
	 * @internal This always runs; build your own loader from the public methods, instead.
	 */
	public static function _init() {

		$tsf = \tsf();

		$scripts = [
			static::get_tsf_scripts(),
			static::get_tt_scripts(),
		];

		if ( $tsf->is_post_edit() ) {
			static::prepare_media_scripts();

			$scripts[] = static::get_post_edit_scripts();
			$scripts[] = static::get_tabs_scripts();
			$scripts[] = static::get_media_scripts();
			$scripts[] = static::get_title_scripts();
			$scripts[] = static::get_description_scripts();
			$scripts[] = static::get_social_scripts();
			$scripts[] = static::get_primaryterm_scripts();
			$scripts[] = static::get_ays_scripts();

			if ( $tsf->get_option( 'display_pixel_counter' ) || $tsf->get_option( 'display_character_counter' ) )
				$scripts[] = static::get_counter_scripts();

			if ( $tsf->is_gutenberg_page() )
				$scripts[] = static::get_gutenberg_compat_scripts();
		} elseif ( $tsf->is_term_edit() ) {
			static::prepare_media_scripts();

			$scripts[] = static::get_term_edit_scripts();
			$scripts[] = static::get_media_scripts();
			$scripts[] = static::get_title_scripts();
			$scripts[] = static::get_description_scripts();
			$scripts[] = static::get_social_scripts();
			$scripts[] = static::get_ays_scripts();

			if ( $tsf->get_option( 'display_pixel_counter' ) || $tsf->get_option( 'display_character_counter' ) )
				$scripts[] = static::get_counter_scripts();
		} elseif ( $tsf->is_wp_lists_edit() ) {
			$scripts[] = static::get_list_edit_scripts();
			$scripts[] = static::get_title_scripts();
			$scripts[] = static::get_description_scripts();

			if ( $tsf->get_option( 'display_pixel_counter' ) || $tsf->get_option( 'display_character_counter' ) )
				$scripts[] = static::get_counter_scripts();
		} elseif ( $tsf->is_seo_settings_page() ) {
			static::prepare_media_scripts();
			static::prepare_metabox_scripts();

			$scripts[] = static::get_seo_settings_scripts();
			$scripts[] = static::get_tabs_scripts();
			$scripts[] = static::get_media_scripts();
			$scripts[] = static::get_title_scripts();
			$scripts[] = static::get_description_scripts();
			$scripts[] = static::get_social_scripts();
			$scripts[] = static::get_ays_scripts();

			// Always load unconditionally, options may enable the counters dynamically.
			$scripts[] = static::get_counter_scripts();
		}

		/**
		 * @since 3.1.0
		 * @since 4.0.0 1. Now holds all scripts.
		 *              2. Added $loader parameter.
		 * @since 4.2.7 Consolidated all input scripts into a list.
		 * @param array  $scripts The default CSS and JS loader settings.
		 * @param string $builder The \The_SEO_Framework\Builders\Scripts builder class name.
		 * @param string $loader  The \The_SEO_Framework\Bridges\Scripts loader class name.
		 */
		$scripts = \apply_filters_ref_array(
			'the_seo_framework_scripts',
			[
				// Flattening is 3% of this method's total time, we can improve by simplifying the getters above like do_meta_output().
				$tsf->array_flatten_list( $scripts ),
				\The_SEO_Framework\Builders\Scripts::class,
				static::class, // i.e. `\The_SEO_Framework\Bridges\Scripts::class`
			]
		);

		\The_SEO_Framework\Builders\Scripts::register( $scripts );
	}

	/**
	 * Decodes entities of a string.
	 *
	 * @since 4.0.0
	 *
	 * @param mixed $value If string, it'll be decoded.
	 * @return mixed
	 */
	public static function decode_entities( $value ) {
		return $value && \is_string( $value ) ? html_entity_decode( $value, \ENT_QUOTES, 'UTF-8' ) : $value;
	}

	/**
	 * Decodes all entities of the input.
	 *
	 * @since 4.0.0
	 * @uses static::decode_entities();
	 *
	 * @param mixed $values The entries to decode.
	 * @return mixed
	 */
	public static function decode_all_entities( $values ) {

		if ( is_scalar( $values ) )
			return static::decode_entities( $values );

		foreach ( $values as &$v )
			$v = static::decode_entities( $v );

		return $values;
	}

	/**
	 * Prepares WordPress Media scripts.
	 *
	 * @since 4.0.0
	 */
	public static function prepare_media_scripts() {

		$tsf  = \tsf();
		$args = [];

		if ( $tsf->is_post_edit() )
			$args['post'] = $tsf->get_the_real_admin_id();

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
	 * Returns the default TSF scripts.
	 *
	 * @since 4.0.0
	 *
	 * @return array The script params.
	 */
	public static function get_tsf_scripts() {
		return [
			[
				'id'       => 'tsf',
				'type'     => 'css',
				'deps'     => [],
				'autoload' => true,
				'hasrtl'   => false,
				'name'     => 'tsf',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/css/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
			],
			[
				'id'       => 'tsf',
				'type'     => 'js',
				'deps'     => [ 'jquery', 'wp-util' ],
				'autoload' => true,
				'name'     => 'tsf',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
				'l10n'     => [
					'name' => 'tsfL10n',
					'data' => [
						'nonces' => [
							/**
							 * Use $tsf->get_settings_capability() ?... might conflict with other nonces.
							 */
							// unused.
							'manage_options' => \current_user_can( 'manage_options' ) ? \wp_create_nonce( 'tsf-ajax-manage_options' ) : false,
							// unused.
							'upload_files'   => \current_user_can( 'upload_files' ) ? \wp_create_nonce( 'tsf-ajax-upload_files' ) : false,
							'edit_posts'     => \current_user_can( 'edit_posts' ) ? \wp_create_nonce( 'tsf-ajax-edit_posts' ) : false,
						],
						'states' => [
							'debug' => \SCRIPT_DEBUG,
						],
					],
				],
			],
		];
	}

	/**
	 * Returns TT (tooltip) scripts params.
	 *
	 * @since 4.0.0
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
				'deps'     => [ 'tsf' ],
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
				'hasrtl'   => false,
				'name'     => 'le',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/css/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
			],
			[
				'id'       => 'tsf-le',
				'type'     => 'js',
				'deps'     => [ 'tsf-title', 'tsf-description', 'tsf' ],
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

		$tsf = \tsf();

		$front_id = $tsf->get_the_front_page_id();

		return [
			[
				'id'       => 'tsf-settings',
				'type'     => 'css',
				'deps'     => [ 'tsf', 'tsf-tt', 'wp-color-picker' ],
				'autoload' => true,
				'hasrtl'   => false,
				'name'     => 'settings',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/css/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
			],
			[
				'id'       => 'tsf-settings',
				'type'     => 'js',
				'deps'     => [ 'jquery', 'tsf-ays', 'tsf-title', 'tsf-description', 'tsf-social', 'tsf', 'tsf-tabs', 'tsf-tt', 'wp-color-picker', 'wp-util' ],
				'autoload' => true,
				'name'     => 'settings',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
				'l10n'     => [
					'name' => 'tsfSettingsL10n',
					'data' => [
						'states' => [
							'isFrontPrivate'   => $front_id && $tsf->is_private( $front_id ),
							'isFrontProtected' => $front_id && $tsf->is_password_protected( $front_id ),
						],
					],
				],
				'tmpl'     => [
					'file' => $tsf->get_view_location( 'templates/settings/settings' ),
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

		$tsf = \tsf();
		$id  = $tsf->get_the_real_id();

		$is_static_frontpage = $tsf->is_static_frontpage( $id );

		if ( $is_static_frontpage ) {
			$additions_forced_disabled = ! $tsf->get_option( 'homepage_tagline' );
			$additions_forced_enabled  = ! $additions_forced_disabled;
		} else {
			$additions_forced_disabled = (bool) $tsf->get_option( 'title_rem_additions' );
			$additions_forced_enabled  = false;
		}

		return [
			[
				'id'       => 'tsf-post',
				'type'     => 'css',
				'deps'     => [ 'tsf-tt', 'tsf' ],
				'autoload' => true,
				'hasrtl'   => false,
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
				'deps'     => [ 'jquery', 'tsf-ays', 'tsf-title', 'tsf-description', 'tsf-social', 'tsf-tabs', 'tsf-tt', 'tsf' ],
				'autoload' => true,
				'name'     => 'post',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
				'l10n'     => [
					'name' => 'tsfPostL10n',
					'data' => [
						'states' => [
							'isPrivate'       => $tsf->is_private( $id ),
							'isProtected'     => $tsf->is_password_protected( $id ),
							'isGutenbergPage' => $tsf->is_gutenberg_page(),
							'id'              => (int) $id,
						],
						'params' => [
							'isFront'                 => $is_static_frontpage,
							'additionsForcedDisabled' => $additions_forced_disabled,
							'additionsForcedEnabled'  => $additions_forced_enabled,
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

		$tsf      = \tsf();
		$taxonomy = $tsf->get_current_taxonomy();

		$additions_forced_disabled = (bool) $tsf->get_option( 'title_rem_additions' );

		$term_prefix = $tsf->use_generated_archive_prefix( \get_term( $tsf->get_the_real_id(), $taxonomy ) )
			/* translators: %s: Taxonomy singular name. */
			? sprintf(
				/* translators: %s: Taxonomy singular name. */
				\_x( '%s:', 'taxonomy term archive title prefix', 'default' ),
				$tsf->get_tax_type_label( $taxonomy )
			)
			: '';

		return [
			[
				'id'       => 'tsf-term',
				'type'     => 'css',
				'deps'     => [ 'tsf-tt', 'tsf' ],
				'autoload' => true,
				'hasrtl'   => false,
				'name'     => 'term',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/css/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
			],
			[
				'id'       => 'tsf-term',
				'type'     => 'js',
				'deps'     => [ 'tsf-ays', 'tsf-title', 'tsf-description', 'tsf-social', 'tsf-tt', 'tsf' ],
				'autoload' => true,
				'name'     => 'term',
				'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
				'ver'      => \THE_SEO_FRAMEWORK_VERSION,
				'l10n'     => [
					'name' => 'tsfTermL10n',
					'data' => [
						'params' => [
							'additionsForcedDisabled' => $additions_forced_disabled,
							'termPrefix'              => static::decode_entities( $term_prefix ),
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
				'deps'     => [ 'jquery', 'tsf', 'wp-editor', 'wp-data', 'lodash', 'react' ],
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
			'deps'     => [], // nada.
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
	 *
	 * @return array The script params.
	 */
	public static function get_media_scripts() {
		return [
			'id'       => 'tsf-media',
			'type'     => 'js',
			'deps'     => [ 'jquery', 'media', 'tsf-tt', 'tsf' ],
			'autoload' => true,
			'name'     => 'media',
			'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
			'ver'      => \THE_SEO_FRAMEWORK_VERSION,
			'l10n'     => [
				'name' => 'tsfMediaL10n',
				'data' => [
					'labels' => [
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
					'nonce'  => \current_user_can( 'upload_files' ) ? \wp_create_nonce( 'tsf-ajax-upload_files' ) : false,
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

		$tsf = \tsf();

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
						'titleSeparator'  => static::decode_entities( $tsf->s_title_raw( $tsf->get_title_separator() ) ),
						'prefixPlacement' => \is_rtl() ? 'after' : 'before',
					],
					'params' => [
						'untitledTitle'  => static::decode_entities( $tsf->s_title_raw( $tsf->get_static_untitled_title() ) ),
						'stripTitleTags' => (bool) $tsf->get_option( 'title_strip_tags' ),
					],
					'i18n'   => [
						// phpcs:ignore, WordPress.WP.I18n -- WordPress doesn't have a comment, either.
						'privateTitle'   => static::decode_entities( trim( str_replace( '%s', '', \__( 'Private: %s', 'default' ) ) ) ),
						// phpcs:ignore, WordPress.WP.I18n -- WordPress doesn't have a comment, either.
						'protectedTitle' => static::decode_entities( trim( str_replace( '%s', '', \__( 'Protected: %s', 'default' ) ) ) ),
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
			'deps'     => [ 'tsf' ],
			'autoload' => true,
			'name'     => 'social',
			'base'     => \THE_SEO_FRAMEWORK_DIR_URL . 'lib/js/',
			'ver'      => \THE_SEO_FRAMEWORK_VERSION,
		];
	}

	/**
	 * Returns Primary Term Selection scripts params.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now filters out unsupported taxonomies.
	 *
	 * @return array The script params.
	 */
	public static function get_primaryterm_scripts() {

		$tsf = \tsf();

		$id = $tsf->get_the_real_admin_id();

		$post_type   = \get_post_type( $id );
		$_taxonomies = $post_type ? $tsf->get_hierarchical_taxonomies_as( 'objects', $post_type ) : [];
		$taxonomies  = [];

		$gutenberg = $tsf->is_gutenberg_page();

		foreach ( $_taxonomies as $_t ) {
			if ( ! $tsf->is_taxonomy_supported( $_t->name ) ) continue;

			$singular_name   = $tsf->get_tax_type_label( $_t->name );
			$primary_term_id = $tsf->get_primary_term_id( $id, $_t->name ) ?: 0;

			if ( ! $primary_term_id ) {
				/**
				 * This is essentially how the filter "post_link_category" gets its
				 * primary term. However, this is without trying to support PHP 5.2.
				 */
				$terms = \get_the_terms( $id, $_t->name );
				if ( $terms && ! \is_wp_error( $terms ) ) {
					$term_ids = array_column( $terms, 'term_id' );
					sort( $term_ids );
					$primary_term_id = reset( $term_ids );
				}
			}

			$taxonomies[ $_t->name ] = [
				'name'    => $_t->name,
				'primary' => $primary_term_id,
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

		if ( $gutenberg ) {
			$vars = [
				'id'   => 'tsf-pt-gb',
				'name' => 'pt-gb',
			];
			$deps = [ 'tsf', 'wp-hooks', 'wp-element', 'wp-components', 'wp-url', 'wp-api-fetch', 'lodash', 'react', 'wp-util' ];
		} else {
			$vars = [
				'id'   => 'tsf-pt',
				'name' => 'pt',
			];
			$deps = [ 'jquery', 'tsf', 'tsf-post', 'tsf-tt', 'wp-util' ];
		}

		return [
			[
				'id'       => 'tsf-pt',
				'type'     => 'css',
				'deps'     => [ 'tsf-tt' ],
				'autoload' => true,
				'hasrtl'   => false,
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
					'file' => $tsf->get_view_location( 'templates/inpost/primary-term-selector' ),
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

		$tsf = \tsf();

		return [
			[
				'id'       => 'tsf-c',
				'type'     => 'css',
				'deps'     => [ 'tsf-tt' ],
				'autoload' => true,
				'hasrtl'   => false,
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
						'guidelines'  => $tsf->get_input_guidelines(),
						'counterType' => \absint( $tsf->get_user_meta_item( 'counter_type' ) ),
						'i18n'        => [
							'guidelines' => $tsf->get_input_guidelines_i18n(),
							/* translators: Pixel counter. 1: number (value), 2: number (guideline) */
							'pixelsUsed' => \esc_attr__( '%1$d out of %2$d pixels are used.', 'autodescription' ),
						],
					],
				],
			],
		];
	}
}
