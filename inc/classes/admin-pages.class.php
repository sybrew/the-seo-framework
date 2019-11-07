<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Admin_Pages
 * @subpackage The_SEO_Framework\Admin\Settings
 */

namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
class Admin_Pages extends Profile {

	/**
	 * @since 2.7.0
	 * @var string $seo_settings_page_hook The page hook_suffix added via WP add_menu_page()
	 */
	public $seo_settings_page_hook;

	/**
	 * @since 2.6.0
	 * @var bool $load_options Determines whether to load the options.
	 */
	public $load_options;

	/**
	 * Adds menu links under "settings" in the wp-admin dashboard
	 *
	 * @since 2.2.2
	 * @since 2.9.2 Added static cache so the method can only run once.
	 *
	 * @return void Early if method is already called.
	 */
	public function add_menu_link() {

		if ( _has_run( __METHOD__ ) ) return;

		$menu = [
			'page_title' => \esc_html__( 'SEO Settings', 'autodescription' ),
			'menu_title' => \esc_html__( 'SEO', 'autodescription' ),
			'capability' => $this->get_settings_capability(),
			'menu_slug'  => $this->seo_settings_page_slug,
			'callback'   => [ $this, '_output_settings_wrap' ],
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
		\add_action( 'load-' . $this->seo_settings_page_hook, [ $this, '_register_seo_settings_meta_boxes' ] );
	}

	/**
	 * Registers the meta boxes early, so WordPress recognizes them for user-settings.
	 *
	 * @since 4.0.0
	 * @see $this->_output_settings_wrap()
	 * @access private
	 */
	public function _register_seo_settings_meta_boxes() {
		Bridges\SeoSettings::_register_seo_settings_meta_boxes();
	}

	/**
	 * Outputs the SEO Settings page wrap.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	public function _output_settings_wrap() {

		\add_action(
			$this->seo_settings_page_hook . '_settings_page_boxes',
			Bridges\SeoSettings::class . '::_output_columns'
		);

		Bridges\SeoSettings::_output_wrap();
	}

	/**
	 * Prepares post edit view, like outputting the fields.
	 *
	 * @since 4.0.0
	 *
	 * @param string   $post_type The current post type.
	 * @param \WP_Post $post      The Post object.
	 */
	public function _init_post_edit_view( $post_type, $post ) {

		if ( ! $this->is_post_edit() ) return;
		if ( ! $this->is_post_type_supported( $post_type ) ) return;

		/**
		 * @since 2.0.0
		 * @param bool $show_seobox Whether to show the SEO meta box.
		 */
		$show_seobox = (bool) \apply_filters( 'the_seo_framework_seobox_output', true );

		if ( $show_seobox )
			\add_action(
				'add_meta_boxes',
				Bridges\PostSettings::class . '::_prepare_meta_box'
			);
	}

	/**
	 * Prepares term edit view, like outputting the fields.
	 *
	 * @since 4.0.0
	 */
	public function _init_term_edit_view() {

		if ( ! $this->is_term_edit() ) return;

		$taxonomy = $this->get_current_taxonomy();

		if ( ! $this->is_taxonomy_supported( $taxonomy ) ) return;

		/**
		 * @since 2.6.0
		 * @param int $priority The metabox term priority.
		 *                      Defaults to a high priority, this box is seen soon below the default edit inputs.
		 */
		$priority = (int) \apply_filters( 'the_seo_framework_term_metabox_priority', 0 );

		\add_action(
			$taxonomy . '_edit_form',
			Bridges\TermSettings::class . '::_prepare_setting_fields',
			$priority,
			2
		);
	}

	/**
	 * Outputs notices on SEO setting changes.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	public static function _do_settings_page_notices() {

		$tsf = \the_seo_framework();

		$notice = $tsf->get_static_cache( 'settings_notice' );

		if ( ! $notice ) return;

		$message = '';
		$type    = '';

		switch ( $notice ) {
			case 'updated':
				$message = \__( 'SEO settings are saved, and the caches have been flushed.', 'autodescription' );
				$type    = 'updated';
				break;

			case 'unchanged':
				$message = \__( 'No SEO settings were changed, but the caches have been flushed.', 'autodescription' );
				$type    = 'info';
				break;

			case 'reset':
				$message = \__( 'SEO settings are reset, and the caches have been flushed.', 'autodescription' );
				$type    = 'warning';
				break;

			case 'error':
				$message = \__( 'An unknown error occurred saving SEO settings.', 'autodescription' );
				$type    = 'error';
				break;
		}

		$tsf->update_static_cache( 'settings_notice', '' );

		$message and $tsf->do_dismissible_notice( $message, $type ?: 'updated' );
	}

	/**
	 * Initializes and outputs various notices.
	 *
	 * @since 2.2.2
	 * @since 3.1.0 1. Added seo plugin check.
	 *              2. Now marked private.
	 * @access private
	 */
	public function notices() {

		if ( $this->get_static_cache( 'check_seo_plugin_conflicts' ) && \current_user_can( 'activate_plugins' ) ) {
			$this->detect_seo_plugins()
				and $this->do_dismissible_notice(
					\__( 'Multiple SEO tools have been detected. You should only use one.', 'autodescription' ),
					'warning'
				);
			$this->update_static_cache( 'check_seo_plugin_conflicts', 0 );
		}
	}

	/**
	 * Setting nav tab wrappers.
	 * Outputs Tabs and settings content.
	 *
	 * @since 2.3.6
	 * @since 2.6.0 Refactored.
	 * @since 3.1.0 Now prefixes the IDs.
	 * @since 4.0.0 Deprecated third parameter, silently.
	 *
	 * @param string $id      The nav-tab ID
	 * @param array  $tabs    The tab content {
	 *    string tab ID => array : {
	 *       string   name     : Tab name.
	 *       callable callback : Output function.
	 *       string   dashicon : The dashicon to use.
	 *       mixed    args     : Optional callback function args.
	 *    }
	 * }
	 * @param null   $depr     Deprecated.
	 * @param bool   $use_tabs Whether to output tabs, only works when $tabs count is greater than 1.
	 */
	public function nav_tab_wrapper( $id, $tabs = [], $depr = null, $use_tabs = true ) {
		Bridges\SeoSettings::_nav_tab_wrapper( $id, $tabs, $use_tabs );
	}

	/**
	 * Outputs in-post flex navigational wrapper and its content.
	 *
	 * @since 2.9.0
	 * @since 3.0.0: Converted to view.
	 * @since 4.0.0: Deprecated third parameter, silently.
	 *
	 * @param string $id       The nav-tab ID
	 * @param array  $tabs     The tab content {
	 *    string tab ID => array : {
	 *       string   name     : Tab name.
	 *       callable callback : Output function.
	 *       string   dashicon : The dashicon to use.
	 *       mixed    args     : Optional callback function args.
	 *    }
	 * }
	 * @param null   $_depr    Deprecated.
	 * @param bool   $use_tabs Whether to output tabs, only works when $tabs count is greater than 1.
	 */
	public static function inpost_flex_nav_tab_wrapper( $id, $tabs = [], $_depr = null, $use_tabs = true ) {
		Bridges\PostSettings::_flex_nav_tab_wrapper( $id, $tabs, $use_tabs );
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
		return sprintf( '%s[%s]', THE_SEO_FRAMEWORK_SITE_OPTIONS, $name );
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
		return sprintf( '%s[%s]', THE_SEO_FRAMEWORK_SITE_OPTIONS, $id );
	}

	/**
	 * Echo constructed id attributes in form fields.
	 *
	 * @since 2.2.2
	 * @uses $this->get_field_id() Constructs id attributes for use in form fields.
	 *
	 * @param string  $id Field id base.
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
	 * Generates dismissible notice.
	 * Also loads scripts and styles if out of The SEO Framework's context.
	 *
	 * @since 2.6.0
	 * @since 3.0.6 The messages are no longer auto-styled to "strong".
	 * @since 4.0.0 Added a tabindex, so keyboard navigation is possible on the "empty" dashicon.
	 * @since 4.0.3 1. Keyboard navigation is now supported on the dismiss icon.
	 *              2. The info notice type is now supported.
	 *
	 * @param string $message The notice message. Expected to be escaped if $escape is false.
	 * @param string $type The notice type : 'updated', 'error', 'warning'. Expected to be escaped.
	 * @param bool   $a11y Whether to add an accessibility icon.
	 * @param bool   $escape Whether to escape the whole output.
	 * @return string The dismissible error notice.
	 */
	public function generate_dismissible_notice( $message = '', $type = 'updated', $a11y = true, $escape = true ) {

		if ( empty( $message ) ) return '';

		//* Make sure the scripts are loaded.
		$this->init_admin_scripts();

		\The_SEO_Framework\Builders\Scripts::enqueue();

		if ( 'warning' === $type )
			$type = 'notice-warning';

		if ( 'info' === $type )
			$type = 'notice-info';

		$a11y = $a11y ? 'tsf-show-icon' : '';

		return vsprintf(
			'<div class="notice %s tsf-notice %s"><p>%s</p>%s</div>',
			[
				\esc_attr( $type ),
				( $a11y ? 'tsf-show-icon' : '' ),
				( $escape ? \esc_html( $message ) : $message ),
				sprintf(
					'<a class="hide-if-no-tsf-js tsf-dismiss" href="javascript:;" title="%s"></a>',
					\esc_attr__( 'Dismiss this notice', 'autodescription' )
				),
			]
		);
	}

	/**
	 * Echos generated dismissible notice.
	 *
	 * @since 2.7.0
	 *
	 * @param string $message The notice message. Expected to be escaped if $escape is false.
	 * @param string $type    The notice type : 'updated', 'error', 'warning'. Expected to be escaped.
	 * @param bool   $a11y    Whether to add an accessibility icon.
	 * @param bool   $escape  Whether to escape the whole output.
	 */
	public function do_dismissible_notice( $message = '', $type = 'updated', $a11y = true, $escape = true ) {
		// phpcs:ignore, WordPress.Security.EscapeOutput -- use $escape
		echo $this->generate_dismissible_notice( $message, $type, (bool) $a11y, (bool) $escape );
	}

	/**
	 * Generates dismissible notice that stick until the user dismisses it.
	 * Also loads scripts and styles if out of The SEO Framework's context.
	 *
	 * @since 2.9.3
	 * @see $this->do_dismissible_sticky_notice()
	 * @uses THE_SEO_FRAMEWORK_UPDATES_CACHE
	 * @todo make this do something.
	 * @ignore
	 * NOTE: This method is a placeholder.
	 *
	 * @param string $message The notice message. Expected to be escaped if $escape is false.
	 * @param string $key     The notice key. Must be unique and tied to the stored updates cache option.
	 * @param array  $args : {
	 *    'type'   => string Optional. The notification type. Default 'updated'.
	 *    'a11y'   => bool   Optional. Whether to enable accessibility. Default true.
	 *    'escape' => bool   Optional. Whether to escape the $message. Default true.
	 *    'color'  => string Optional. If filled in, it will output the selected color. Default ''.
	 *    'icon'   => string Optional. If filled in, it will output the selected icon. Default ''.
	 * }
	 * @return string The dismissible error notice.
	 */
	public function generate_dismissible_sticky_notice( $message, $key, $args = [] ) { // phpcs:ignore -- unused.
		return '';
	}

	/**
	 * Echos generated dismissible sticky notice.
	 *
	 * @since 2.9.3
	 * @uses $this->generate_dismissible_sticky_notice()
	 * @ignore
	 *
	 * @param string $message The notice message. Expected to be escaped if $escape is false.
	 * @param string $key     The notice key. Must be unique and tied to the stored updates cache option.
	 * @param array  $args : {
	 *    'type'   => string Optional. The notification type. Default 'updated'.
	 *    'a11y'   => bool   Optional. Whether to enable accessibility. Default true.
	 *    'escape' => bool   Optional. Whether to escape the $message. Default true.
	 *    'color'  => string Optional. If filled in, it will output the selected color. Default ''.
	 *    'icon'   => string Optional. If filled in, it will output the selected icon. Default ''.
	 * }
	 */
	public function do_dismissible_sticky_notice( $message, $key, $args = [] ) {
		// phpcs:ignore, WordPress.Security.EscapeOutput -- use $args['escape']
		echo $this->generate_dismissible_sticky_notice( $message, $key, $args );
	}

	/**
	 * Mark up content with code tags.
	 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
	 *
	 * @since 2.0.0
	 *
	 * @param string $content Content to be wrapped in code tags.
	 * @return string Content wrapped in code tags.
	 */
	public function code_wrap( $content ) {
		return $this->code_wrap_noesc( \esc_html( $content ) );
	}

	/**
	 * Mark up content with code tags.
	 * Escapes no HTML.
	 *
	 * @since 2.2.2
	 *
	 * @param string $content Content to be wrapped in code tags.
	 * @return string Content wrapped in code tags.
	 */
	public function code_wrap_noesc( $content ) {
		return '<code>' . $content . '</code>';
	}

	/**
	 * Mark up content in description wrap.
	 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
	 *
	 * @since 2.7.0
	 *
	 * @param string $content Content to be wrapped in the description wrap.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public function description( $content, $block = true ) {
		$this->description_noesc( \esc_html( $content ), $block );
	}

	/**
	 * Mark up content in description wrap.
	 *
	 * @since 2.7.0
	 *
	 * @param string $content Content to be wrapped in the description wrap. Expected to be escaped.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public function description_noesc( $content, $block = true ) {
		$output = '<span class="description">' . $content . '</span>';
		// phpcs:ignore, WordPress.Security.EscapeOutput -- Method clearly states it's not escaped.
		echo $block ? '<p>' . $output . '</p>' : $output;
	}

	/**
	 * Mark up content in attention wrap.
	 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
	 *
	 * @since 3.1.0
	 *
	 * @param string $content Content to be wrapped in the attention wrap.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public function attention( $content, $block = true ) {
		$this->attention_noesc( \esc_html( $content ), $block );
	}

	/**
	 * Mark up content in attention wrap.
	 *
	 * @since 3.1.0
	 *
	 * @param string $content Content to be wrapped in the attention wrap. Expected to be escaped.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public function attention_noesc( $content, $block = true ) {
		$output = '<span class="attention">' . $content . '</span>';
		// phpcs:ignore, WordPress.Security.EscapeOutput -- Method clearly states it's not escaped.
		echo $block ? '<p>' . $output . '</p>' : $output;
	}

	/**
	 * Mark up content in a description+attention wrap.
	 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
	 *
	 * @since 3.1.0
	 *
	 * @param string $content Content to be wrapped in the wrap. Expected to be escaped.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public function attention_description( $content, $block = true ) {
		$this->attention_description_noesc( \esc_html( $content ), $block );
	}

	/**
	 * Mark up content in a description+attention wrap.
	 *
	 * @since 3.1.0
	 *
	 * @param string $content Content to be wrapped in the wrap. Expected to be escaped.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public function attention_description_noesc( $content, $block = true ) {
		$output = '<span class="description attention">' . $content . '</span>';
		// phpcs:ignore, WordPress.Security.EscapeOutput -- Method clearly states it's not escaped.
		echo $block ? '<p>' . $output . '</p>' : $output;
	}

	/**
	 * Echo or return a chechbox fields wrapper.
	 *
	 * This method does NOT escape.
	 *
	 * @since 2.6.0
	 *
	 * @param string $input The input to wrap. Should already be escaped.
	 * @param bool   $echo  Whether to escape echo or just return.
	 * @return string|void Wrapped $input.
	 */
	public function wrap_fields( $input = '', $echo = false ) {

		if ( is_array( $input ) )
			$input = implode( PHP_EOL, $input );

		if ( $echo ) {
			// phpcs:ignore, WordPress.Security.EscapeOutput -- Escape your $input prior!
			echo '<div class="tsf-fields">' . $input . '</div>';
		} else {
			return '<div class="tsf-fields">' . $input . '</div>';
		}
	}

	/**
	 * Makes either simple or JSON-encoded data-* attributes for HTML elements.
	 *
	 * @since 4.0.0
	 * @internal
	 *
	 * @param array $data : {
	 *    string $k => mixed $v
	 * }
	 * @return string The HTML data attributes, with added space to the start.
	 */
	public function make_data_attributes( array $data ) {

		$ret = [];

		foreach ( $data as $k => $v ) {
			if ( ! is_scalar( $v ) ) {
				$ret[] = sprintf(
					'data-%s="%s"',
					strtolower( preg_replace(
						'/([A-Z])/',
						'-$1',
						preg_replace( '/[^a-z0-9_\-]/i', '', $k )
					) ), // dash case.
					htmlspecialchars( json_encode( $v, JSON_UNESCAPED_SLASHES ), ENT_COMPAT, 'UTF-8' )
				);
			} else {
				$ret[] = sprintf(
					'data-%s="%s"',
					strtolower( preg_replace(
						'/([A-Z])/',
						'-$1',
						preg_replace( '/[^a-z0-9_\-]/i', '', $k )
					) ), // dash case.
					\esc_attr( $v )
				);
			}
		}

		return ' ' . implode( ' ', $ret );
	}

	/**
	 * Returns a chechbox wrapper.
	 *
	 * @since 2.6.0
	 * @since 2.7.0 Added escape parameter. Defaults to true.
	 * @since 3.0.3 Added $disabled parameter. Defaults to false.
	 * @see $this->make_checkbox_array()
	 *
	 * @param string $field_id    The option ID. Must be within the Autodescription settings.
	 * @param string $label       The checkbox description label.
	 * @param string $description Addition description to place beneath the checkbox.
	 * @param bool   $escape      Whether to escape the label and description.
	 * @param bool   $disabled    Whether to disable the input.
	 * @return string HTML checkbox output.
	 */
	public function make_checkbox( $field_id = '', $label = '', $description = '', $escape = true, $disabled = false ) {
		return $this->make_checkbox_array( [
			'id'          => $field_id,
			'index'       => '',
			'label'       => $label,
			'description' => $description,
			'escape'      => $escape,
			'disabled'    => $disabled,
		] );
	}

	/**
	 * Returns a chechbox wrapper.
	 *
	 * @since 3.1.0
	 *
	 * @param array $args : {
	 *    string $id          The option name, used as field ID.
	 *    string $index       The option index, used when the option is an array.
	 *    string $label       The checkbox label description, placed inline of the checkbox.
	 *    string $description The checkbox additional description, placed underneat.
	 *    bool   $escape      Whether to enable escaping of the $label and $description.
	 *    bool   $disabled    Whether to disable the checkbox field.
	 *    bool   $default     Whether to display-as-default. This is autodetermined when no $index is set.
	 *    bool   $warned      Whether to warn the checkbox field value.
	 * }
	 * @return string HTML checkbox output.
	 */
	public function make_checkbox_array( array $args = [] ) {

		$args = array_merge(
			[
				'id'          => '',
				'index'       => '',
				'label'       => '',
				'description' => '',
				'escape'      => true,
				'disabled'    => false,
				'default'     => false,
				'warned'      => false,
			],
			$args
		);

		if ( $args['escape'] ) {
			$args['description'] = \esc_html( $args['description'] );
			$args['label']       = \esc_html( $args['label'] );
		}

		$index = $this->s_field_id( $args['index'] ?: '' );

		$field_id = $field_name = \esc_attr( sprintf(
			'%s%s',
			$this->get_field_id( $args['id'] ),
			$index ? sprintf( '[%s]', $index ) : ''
		) );

		$value = $this->get_option( $args['id'] );
		if ( $index ) {
			$value = isset( $value[ $index ] ) ? $value[ $index ] : '';
		}

		$cb_class = '';
		if ( $args['disabled'] ) {
			$cb_class = 'tsf-disabled';
		} elseif ( ! $args['index'] ) {
			// Can't fetch conditionals in index.
			$cb_class = $this->get_is_conditional_checked( $args['id'], false );
		} else {
			if ( $args['default'] ) {
				$cb_class = 'tsf-default-selected';
			} elseif ( $args['warned'] ) {
				$cb_class = 'tsf-warning-selected';
			}
		}

		$output = sprintf(
			'<span class="tsf-toblock">%s</span>',
			vsprintf(
				'<label for="%s" %s>%s</label>',
				[
					$field_id,
					( $args['disabled'] ? 'class="tsf-disabled"' : '' ),
					vsprintf(
						'<input type=checkbox class="%s" name="%s" id="%s" value="1" %s %s /> %s',
						[
							$cb_class,
							$field_name,
							$field_id,
							\checked( $value, true, false ),
							( $args['disabled'] ? 'disabled' : '' ),
							$args['label'],
						]
					),
				]
			)
		);

		$output .= $args['description'] ? sprintf( '<p class="description tsf-option-spacer">%s</p>', $args['description'] ) : '';

		return $output;
	}

	/**
	 * Returns a HTML select form elements for qubit options: -1, 0, or 1.
	 * Does not support "multiple" field selections.
	 *
	 * @since 4.0.0
	 *
	 * @param array $args : {
	 *    string     $id       The select field ID.
	 *    string     $class    The div wrapper class.
	 *    string     $name     The option name.
	 *    int|string $default  The current option value.
	 *    array      $options  The select option values : { value => name }
	 *    string     $label    The option label.
	 *    string     $required Whether the field must be required.
	 *    array      $data     The select field data. Sub-items are expected to be escaped if they're not an array.
	 *    array      $info     Extra info field data.
	 * }
	 * @return string The option field.
	 */
	public function make_single_select_form( array $args ) {

		$defaults = [
			'id'       => '',
			'class'    => '',
			'name'     => '',
			'default'  => '',
			'options'  => [],
			'label'    => '',
			'required' => false,
			'data'     => [],
			'info'     => [],
		];

		$args = array_merge( $defaults, $args );

		// The walk below destroys the option array. As such, we assigned a new value.
		$html_options = $args['options'];

		array_walk(
			$html_options,
			/**
			 * @param string $name    The option name. Passed by reference, returned as the HTML option item.
			 * @param mixed  $value
			 * @param mixed  $default
			 */
			function( &$name, $value, $default ) {
				$name = sprintf(
					'<option value="%s"%s>%s</option>',
					\esc_attr( $value ),
					(string) $value === (string) $default ? ' selected' : '',
					\esc_html( $name )
				);
			},
			$args['default']
		);

		return vsprintf(
			sprintf( '<div class="%s">%s</div>',
				\esc_attr( $args['class'] ),
				( \is_rtl() ? '%2$s%1$s%3$s' : '%1$s%2$s%3$s' )
			),
			[
				$args['label'] ? sprintf(
					'<label for=%s>%s</label> ', // NOTE: extra space!
					$this->s_field_id( $args['id'] ),
					\esc_html( $args['label'] )
				) : '',
				$args['info'] ? ' ' . $this->make_info(
					$args['info'][0],
					isset( $args['info'][1] ) ? $args['info'][1] : '',
					false
				) : '',
				vsprintf(
					'<select id=%s name=%s %s %s>%s</select>',
					[
						$this->s_field_id( $args['id'] ),
						\esc_attr( $args['name'] ),
						$args['required'] ? 'required' : '',
						$args['data'] ? $this->make_data_attributes( $args['data'] ) : '',
						implode( $html_options ),
					]
				),
			]
		);
	}

	/**
	 * Return a wrapped question mark.
	 *
	 * @since 2.6.0
	 * @since 3.0.0 Links are now no longer followed, referred or bound to opener.
	 * @since 4.0.0 Now adds a tabindex to the span tag, so you can focus it using keyboard navigation.
	 *
	 * @param string $description The descriptive on-hover title.
	 * @param string $link        The non-escaped link.
	 * @param bool   $echo        Whether to echo or return.
	 * @return string HTML checkbox output if $echo is false.
	 */
	public function make_info( $description = '', $link = '', $echo = true ) {

		if ( $link ) {
			$output = sprintf(
				'<a href="%1$s" class="tsf-tooltip-item tsf-help" target="_blank" rel="nofollow noreferrer noopener" title="%2$s" data-desc="%2$s">[?]</a>',
				\esc_url( $link, [ 'https', 'http' ] ),
				\esc_attr( $description )
			);
		} else {
			$output = sprintf(
				'<span class="tsf-tooltip-item tsf-help" title="%1$s" data-desc="%1$s" tabindex=0>[?]</span>',
				\esc_attr( $description )
			);
		}

		$output = sprintf( '<span class="tsf-tooltip-wrap">%s</span>', $output );

		if ( $echo ) {
			// phpcs:ignore, WordPress.Security.EscapeOutput
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Returns the HTML class wrap for default Checkbox options.
	 *
	 * This function does nothing special. But is merely a simple wrapper.
	 * Just like code_wrap.
	 *
	 * @since 2.2.5
	 * @since 3.1.0 Deprecated second parameter.
	 *
	 * @param string $key  The option name which returns boolean.
	 * @param string $depr Deprecated
	 * @param bool   $wrap Whether to wrap the class name in `class="%s"`
	 * @param bool   $echo Whether to echo or return the output.
	 * @return string Empty on echo or the class name with an optional wrapper.
	 */
	public function is_default_checked( $key, $depr = '', $wrap = true, $echo = true ) {

		$class = '';

		$default = $this->get_default_settings( $key );

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
	 * @since 3.1.0 Deprecated second parameter.
	 *
	 * @param string $key  The option name which returns boolean.
	 * @param string $deprecated Deprecated.
	 * @param bool   $wrap Whether to wrap the class name in `class="%s"`
	 * @param bool   $echo Whether to echo or return the output.
	 * @return string Empty on echo or the class name with an optional wrapper.
	 */
	public function is_warning_checked( $key, $deprecated = '', $wrap = true, $echo = true ) {

		$class = '';

		$warned = $this->get_warned_settings( $key, $deprecated );

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
	 * @since 3.1.0 Added the $wrap parameter
	 *
	 * @param string $key  The option name which returns boolean.
	 * @param bool   $wrap Whether to wrap the class name in `class="%s"`
	 */
	public function get_is_conditional_checked( $key, $wrap = true ) {
		return $this->is_conditional_checked( $key, '', $wrap, false );
	}

	/**
	 * Returns the HTML class wrap for warning/default Checkbox options.
	 *
	 * @since 2.3.4
	 * @since 3.1.0 Deprecated second parameter.
	 *
	 * @param string $key        The option name which returns boolean.
	 * @param string $deprecated Deprecated. Used to be the settings field.
	 * @param bool   $wrap       Whether to wrap the class name in `class="%s"`
	 * @param bool   $echo       Whether to echo or return the output.
	 * @return string Empty on echo or the class name with an optional wrapper.
	 */
	public function is_conditional_checked( $key, $deprecated = '', $wrap = true, $echo = true ) {

		$class = '';

		$default = $this->is_default_checked( $key, $deprecated, false, false );
		$warned  = $this->is_warning_checked( $key, $deprecated, false, false );

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
	 * Returns the SEO Bar.
	 *
	 * @since 4.0.0
	 * @uses \The_SEO_Framework\Interpreters\SeoBar::generate_bar();
	 *
	 * @param array $query : {
	 *   int    $id        : Required. The current post or term ID.
	 *   string $taxonomy  : Optional. If not set, this will interpret it as a post.
	 *   string $post_type : Optional. If not set, this will be automatically filled.
	 *                                 This parameter is ignored for taxonomies.
	 * }
	 * @return string The generated SEO bar, in HTML.
	 */
	public function get_generated_seo_bar( array $query ) {
		return Interpreters\SeoBar::generate_bar( $query );
	}

	/**
	 * Returns social image uploader form button.
	 * Also registers additional i18n strings for JS.
	 *
	 * @since 2.8.0
	 * @since 3.1.0 No longer prepares media l10n data.
	 * @since 4.0.0 Now adds a media preview dispenser.
	 *
	 * @param string $input_id Required. The HTML input id to pass URL into.
	 * @return string The image uploader button.
	 */
	public function get_social_image_uploader_form( $input_id ) {

		if ( ! $input_id )
			return '';

		$s_input_id = \esc_attr( $input_id );

		$content = vsprintf(
			'<button type=button data-href="%s" class="tsf-set-image-button button button-primary button-small" title="%s" id="%s-select"
				%s>%s</button>',
			[
				\esc_url( \get_upload_iframe_src( 'image', $this->get_the_real_ID() ) ),
				\esc_attr_x( 'Select social image', 'Button hover', 'autodescription' ),
				$s_input_id,
				$this->make_data_attributes( [
					'inputId'   => $s_input_id,
					'inputType' => 'social',
					'width'     => 1200,
					'height'    => 630,
					'minWidth'  => 200,
					'minHeight' => 200,
					'flex'      => true,
				] ),
				\esc_html__( 'Select Image', 'autodescription' ),
			]
		);

		$content .= vsprintf(
			'<span class="tsf-tooltip-wrap"><span id="%1$s-preview" class="tsf-image-preview tsf-tooltip-item dashicons dashicons-format-image" data-for="%1$s" tabindex=0></span></span>',
			$s_input_id
		);

		return $content;
	}

	/**
	 * Returns logo uploader form buttons.
	 * Also registers additional i18n strings for JS.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 No longer prepares media l10n data.
	 * @since 4.0.0 Now adds a media preview dispenser.
	 *
	 * @param string $input_id Required. The HTML input id to pass URL into.
	 * @return string The image uploader button.
	 */
	public function get_logo_uploader_form( $input_id ) {

		if ( ! $input_id )
			return '';

		$s_input_id = \esc_attr( $input_id );

		$content = vsprintf(
			'<button type=button data-href="%s" class="tsf-set-image-button button button-primary button-small" title="%s" id="%s-select"
				%s>%s</button>',
			[
				\esc_url( \get_upload_iframe_src( 'image', $this->get_the_real_ID() ) ),
				'', // Redundant
				$s_input_id,
				$this->make_data_attributes( [
					'inputId'   => $s_input_id,
					'inputType' => 'logo',
					'width'     => 512,
					'height'    => 512,
					'minWidth'  => 112,
					'minHeight' => 112,
					'flex'      => true,
				] ),
				\esc_html__( 'Select Logo', 'autodescription' ),
			]
		);

		$content .= vsprintf(
			'<span class="tsf-tooltip-wrap"><span id="%1$s-preview" class="tsf-image-preview tsf-tooltip-item dashicons dashicons-format-image" data-for="%1$s" tabindex=0></span></span>',
			$s_input_id
		);

		return $content;
	}

	/**
	 * Outputs floating and reference title HTML elements for JavaScript.
	 *
	 * @since 3.0.4
	 */
	public function output_js_title_elements() {
		echo '<span id=tsf-title-reference style=display:none></span>';
		echo '<span id=tsf-title-offset class=hide-if-no-tsf-js></span>';
		echo '<span id=tsf-title-placeholder class=hide-if-no-tsf-js></span>';
		echo '<span id=tsf-title-placeholder-prefix class=hide-if-no-tsf-js></span>';
	}

	/**
	 * Outputs reference description HTML elements for JavaScript.
	 *
	 * @since 3.0.4
	 */
	public function output_js_description_elements() {
		echo '<span id="tsf-description-reference" style="display:none"></span>';
	}

	/**
	 * Outputs character counter wrap for both JavaScript and no-Javascript.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 : 1. Added an "what if you click" onhover-title.
	 *                2. Removed second parameter's usage. For passing the expected string.
	 *                3. The whole output is now hidden from no-js.
	 *
	 * @param string $for     The input ID it's for.
	 * @param string $depr    The initial value for no-JS. Deprecated.
	 * @param bool   $display Whether to display the counter. (options page gimmick)
	 */
	public function output_character_counter_wrap( $for, $depr = '', $display = true ) {
		vprintf(
			'<div class="tsf-counter-wrap hide-if-no-tsf-js" %s><span class="description tsf-counter" title="%s">%s</span><span class="tsf-ajax"></span></div>',
			[
				( $display ? '' : 'style="display:none;"' ),
				\esc_attr__( 'Click to change the counter type', 'autodescription' ),
				sprintf(
					/* translators: %s = number */
					\esc_html__( 'Characters Used: %s', 'autodescription' ),
					sprintf(
						'<span id="%s_chars">%s</span>',
						\esc_attr( $for ),
						0
					)
				),
			]
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
			'<div class="tsf-pixel-counter-wrap hide-if-no-tsf-js" %s>%s%s</div>',
			[
				( $display ? '' : 'style="display:none;"' ),
				sprintf(
					'<div id="%s_pixels" class="tsf-tooltip-wrap">%s</div>',
					\esc_attr( $for ),
					'<span class="tsf-pixel-counter-bar tsf-tooltip-item" aria-label="" data-desc="" tabindex=0><span class="tsf-pixel-counter-fluid"></span></span>'
				),
				sprintf(
					'<div class="tsf-pixel-shadow-wrap"><span class="tsf-pixel-counter-shadow tsf-%s-pixel-counter-shadow"></span></div>',
					\esc_attr( $type )
				),
			]
		);
	}

	/**
	 * Calculates the social title and description placeholder values.
	 * This is intricated, voluminous, and convoluted; but, there's no other way :(
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @param array  $args An array of 'id' and 'taxonomy' values.
	 * @param string $for  The screen it's for. Accepts 'edit' and 'settings'.
	 * @return array An array social of titles and descriptions.
	 */
	public function _get_social_placeholders( array $args, $for = 'edit' ) {

		$desc_from_custom_field = $this->get_description_from_custom_field( $args );

		if ( 'settings' === $for ) {
			$pm_edit_og_title = $args['id'] ? $this->get_post_meta_item( '_open_graph_title', $args['id'] ) : '';
			$pm_edit_og_desc  = $args['id'] ? $this->get_post_meta_item( '_open_graph_description', $args['id'] ) : '';
			$pm_edit_tw_title = $args['id'] ? $this->get_post_meta_item( '_twitter_title', $args['id'] ) : '';
			$pm_edit_tw_desc  = $args['id'] ? $this->get_post_meta_item( '_twitter_description', $args['id'] ) : '';

			// Gets custom fields from SEO settings.
			$home_og_title = $this->get_option( 'homepage_og_title' );
			$home_og_desc  = $this->get_option( 'homepage_og_description' );

			//! OG title generator falls back to meta input. The description does not.
			$og_tit_placeholder  = $pm_edit_og_title
								?: $this->get_generated_open_graph_title( $args );
			$og_desc_placeholder = $pm_edit_og_desc
								?: $desc_from_custom_field
								?: $this->get_generated_open_graph_description( $args );

			//! TW title generator falls back to meta input. The description does not.
			$tw_tit_placeholder  = $pm_edit_tw_title
								?: $home_og_title
								?: $pm_edit_og_title
								?: $this->get_generated_twitter_title( $args );
			$tw_desc_placeholder = $pm_edit_tw_desc
								?: $home_og_desc
								?: $pm_edit_og_desc
								?: $desc_from_custom_field
								?: $this->get_generated_twitter_description( $args );
		} elseif ( 'edit' === $for ) {
			if ( ! $args['taxonomy'] ) {
				if ( $this->is_static_frontpage( $args['id'] ) ) {
					// Gets custom fields from SEO settings.
					$home_desc = $this->get_option( 'homepage_description' );

					$home_og_title = $this->get_option( 'homepage_og_title' );
					$home_og_desc  = $this->get_option( 'homepage_og_description' );
					$home_tw_title = $this->get_option( 'homepage_twitter_title' );
					$home_tw_desc  = $this->get_option( 'homepage_twitter_description' );

					// Gets custom fields from page.
					$custom_og_title = $this->get_post_meta_item( '_open_graph_title', $args['id'] );
					$custom_og_desc  = $this->get_post_meta_item( '_open_graph_description', $args['id'] );

					//! OG title generator falls back to meta input. The description does not.
					$og_tit_placeholder  = $home_og_title
										?: $this->get_generated_open_graph_title( $args );
					$og_desc_placeholder = $home_og_desc
										?: $home_desc
										?: $desc_from_custom_field
										?: $this->get_generated_open_graph_description( $args );

					//! TW title generator falls back to meta input. The description does not.
					$tw_tit_placeholder  = $home_tw_title
										?: $home_og_title
										?: $custom_og_title
										?: $this->get_generated_twitter_title( $args );
					$tw_desc_placeholder = $home_tw_desc
										?: $home_og_desc
										?: $custom_og_desc
										?: $home_desc
										?: $desc_from_custom_field
										?: $this->get_generated_twitter_description( $args );
				} else {
					// Gets custom fields.
					$custom_og_title = $this->get_post_meta_item( '_open_graph_title', $args['id'] );
					$custom_og_desc  = $this->get_post_meta_item( '_open_graph_description', $args['id'] );

					//! OG title generator falls back to meta input. The description does not.
					$og_tit_placeholder  = $this->get_generated_open_graph_title( $args );
					$og_desc_placeholder = $desc_from_custom_field
										?: $this->get_generated_open_graph_description( $args );

					//! TW title generator falls back to meta input. The description does not.
					$tw_tit_placeholder  = $custom_og_title
										?: $this->get_generated_twitter_title( $args );
					$tw_desc_placeholder = $custom_og_desc
										?: $desc_from_custom_field
										?: $this->get_generated_twitter_description( $args );
				}
			} else {
				$meta = $this->get_term_meta( $args['id'] );

				//! OG title generator falls back to meta input. The description does not.
				$og_tit_placeholder  = $this->get_generated_open_graph_title( $args );
				$og_desc_placeholder = $desc_from_custom_field
									?: $this->get_generated_open_graph_description( $args );

				//! TW title generator falls back to meta input. The description does not.
				$tw_tit_placeholder  = $meta['og_title']
									?: $og_tit_placeholder;
				$tw_desc_placeholder = $meta['og_description']
									?: $desc_from_custom_field
									?: $this->get_generated_twitter_description( $args );
			}
		} else {
			$og_tit_placeholder  = '';
			$tw_tit_placeholder  = '';
			$og_desc_placeholder = '';
			$tw_desc_placeholder = '';
		}

		return [
			'title'       => [
				'og'      => $og_tit_placeholder,
				'twitter' => $tw_tit_placeholder,
			],
			'description' => [
				'og'      => $og_desc_placeholder,
				'twitter' => $tw_desc_placeholder,
			],
		];
	}
}
