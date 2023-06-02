<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Admin_Pages
 * @subpackage The_SEO_Framework\Admin\Settings
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
class Admin_Pages extends Generate_Ldjson {

	/**
	 * @since 2.7.0
	 * @var string $seo_settings_page_hook The page hook_suffix added via WP add_menu_page()
	 */
	public $seo_settings_page_hook;

	/**
	 * Adds menu links under "settings" in the wp-admin dashboard
	 *
	 * @since 2.2.2
	 * @since 2.9.2 Added static cache so the method can only run once.
	 *
	 * @return void Early if method is already called.
	 */
	public function add_menu_link() {

		if ( has_run( __METHOD__ ) ) return;

		$issue_count = $this->get_admin_issue_count();

		if ( $issue_count )
			$issue_badge = $this->get_admin_menu_issue_badge( $issue_count );

		/**
		 * @since 4.2.8
		 * @param array $args The menu arguments. All indexes must be maintained.
		 */
		$menu = \apply_filters(
			'the_seo_framework_top_menu_args',
			[
				'page_title' => \esc_html__( 'SEO Settings', 'autodescription' ),
				'menu_title' => \esc_html__( 'SEO', 'autodescription' ) . ( $issue_badge ?? '' ),
				'capability' => $this->get_settings_capability(),
				'menu_slug'  => $this->seo_settings_page_slug,
				'callback'   => [ $this, '_output_settings_wrap' ],
				'icon'       => 'dashicons-search',
				'position'   => '90.9001',
			]
		);

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

		// Enqueue scripts
		\add_action( "admin_print_scripts-{$this->seo_settings_page_hook}", [ $this, '_init_admin_scripts' ], 11 );
		\add_action( "load-{$this->seo_settings_page_hook}", [ $this, '_register_seo_settings_meta_boxes' ] );
	}

	/**
	 * Returns the number of issues registered.
	 * Always returns 0 when the settings are headless.
	 *
	 * @since 4.2.8
	 *
	 * @return int The registered issue count.
	 */
	public function get_admin_issue_count() {

		if ( $this->is_headless['settings'] ) return 0;

		/**
		 * @since 4.2.8
		 * @param int The issue count. Don't overwrite, but increment it!
		 */
		return memo() ?? memo( \absint( \apply_filters( 'the_seo_framework_top_menu_issue_count', 0 ) ) );
	}

	/**
	 * Returns formatted text for the notice count to be displayed in the admin menu as a number.
	 *
	 * @since 4.2.8
	 *
	 * @param int $issue_count The issue count.
	 * @return string The issue count badge.
	 */
	public function get_admin_menu_issue_badge( $issue_count ) {

		$notice_i18n = \number_format_i18n( $issue_count );

		return ' ' . sprintf(
			'<span class="tsf-menu-issue menu-counter count-%d"><span class=tsf-menu-issue-text aria-hidden=true>%s</span><span class=screen-reader-text>%s</span></span>',
			$issue_count,
			$notice_i18n,
			sprintf(
				/* translators: %s: number of issues waiting */
				\_n( '%s issue waiting', '%s issues waiting', $issue_count, 'autodescription' ),
				$notice_i18n
			)
		);
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
			"{$this->seo_settings_page_hook}_settings_page_boxes",
			[ Bridges\SeoSettings::class, '_output_columns' ]
		);

		Bridges\SeoSettings::_output_wrap();
	}

	/**
	 * Prepares post edit view, like outputting the fields.
	 *
	 * @since 4.0.0
	 *
	 * @param string   $post_type The current post type.
	 * @param \WP_Post $post      The Post object. Unused.
	 */
	public function _init_post_edit_view( $post_type, $post ) {

		if ( ! $this->is_post_edit() || ! $this->is_post_type_supported( $post_type ) ) return;

		/**
		 * @since 2.0.0
		 * @param bool $show_seobox Whether to show the SEO meta box.
		 */
		$show_seobox = (bool) \apply_filters( 'the_seo_framework_seobox_output', true );

		if ( $show_seobox )
			\add_action(
				'add_meta_boxes',
				[ Bridges\PostSettings::class, '_prepare_meta_box' ]
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
		 * @param int $priority The meta box term priority.
		 *                      Defaults to a high priority, this box is seen soon below the default edit inputs.
		 */
		$priority = (int) \apply_filters( 'the_seo_framework_term_metabox_priority', 0 );

		\add_action(
			"{$taxonomy}_edit_form",
			[ Bridges\TermSettings::class, '_prepare_setting_fields' ],
			$priority,
			2
		);
	}

	/**
	 * Prepares profile/user edit view, like outputting the SEO fields.
	 *
	 * @since 4.1.4
	 * @access private
	 */
	public function _init_user_edit_view() {

		if ( ! $this->is_profile_edit() ) return;

		// WordPress made a mess of this. We can't reliably get a user future-proof. Load class for all users; check there.
		// if ( ! $user->has_cap( \THE_SEO_FRAMEWORK_AUTHOR_INFO_CAP ) ) return;

		\add_action( 'show_user_profile', [ Bridges\UserSettings::class, '_prepare_setting_fields' ], 0, 1 );
		\add_action( 'edit_user_profile', [ Bridges\UserSettings::class, '_prepare_setting_fields' ], 0, 1 );
	}

	/**
	 * Outputs notices on SEO setting changes.
	 *
	 * @since 4.0.0
	 * @since 4.0.5 This is no longer a static function.
	 * @access private
	 */
	public function _do_settings_page_notices() {

		$notice = $this->get_static_cache( 'settings_notice' );

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

		$this->update_static_cache( 'settings_notice', '' );

		$message and $this->do_dismissible_notice( $message, $type ?: 'updated' );
	}

	/**
	 * Initializes and outputs various notices.
	 *
	 * @since 4.1.0
	 * @access private
	 */
	public function _output_notices() {

		if ( $this->get_static_cache( 'check_seo_plugin_conflicts' ) && \current_user_can( 'activate_plugins' ) ) {
			$this->detect_seo_plugins()
				and $this->do_dismissible_notice(
					\__( 'Multiple SEO tools have been detected. You should only use one.', 'autodescription' ),
					'warning'
				);
			$this->update_static_cache( 'check_seo_plugin_conflicts', 0 );
		}

		$this->output_dismissible_persistent_notices();
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
	 * @since 4.1.0 Now semantically wraps the content with HTML.
	 * @since 4.1.2 1. No longer invokes the script loader during AJAX-requests.
	 *              2. Now accepts empty messages, so that AJAX-invoked generators can grab a notice wrapper.
	 *              3. Added the inline parameter.
	 *              4. Now enqueues scripts in the footer, so templates won't spam the header.
	 * @TODO deprecate -- Use the more reliable and secure persistent notices registry instead...
	 *                    Then again, this allows for AJAX-generated notices.
	 * @see register_dismissible_persistent_notice()
	 *
	 * @param string $message The notice message. Expected to be escaped if $escape is false.
	 *                        When the message contains HTML, it must start with a <p> tag,
	 *                        or it will be added for you--regardless of proper semantics.
	 * @param string $type   The notice type : 'updated', 'error', 'warning', 'info'. Expected to be escaped.
	 * @param bool   $icon   Whether to add an accessibility icon.
	 * @param bool   $escape Whether to escape the whole output.
	 * @param bool   $inline Whether WordPress should be allowed to move it.
	 * @return string The dismissible error notice.
	 */
	public function generate_dismissible_notice( $message = '', $type = 'updated', $icon = true, $escape = true, $inline = false ) {

		if ( ! \wp_doing_ajax() ) {
			// Make sure the scripts are loaded.
			$this->init_admin_scripts();
			\The_SEO_Framework\Builders\Scripts::footer_enqueue();
		}

		switch ( $type ) {
			case 'warning':
			case 'info':
				$type = "notice-$type";
				break;
		}

		return vsprintf(
			'<div class="notice %s tsf-notice %s %s">%s%s</div>',
			[
				\esc_attr( $type ),
				( $icon ? 'tsf-show-icon' : '' ),
				( $inline ? 'inline' : '' ),
				sprintf(
					( ! $escape && 0 === strpos( $message, '<p' ) ? '%s' : '<p>%s</p>' ),
					( $escape ? \esc_html( $message ) : $message )
				),
				sprintf(
					'<a class="hide-if-no-tsf-js tsf-dismiss" href="javascript:;" title="%s"></a>',
					\esc_attr__( 'Dismiss this notice', 'default' )
				),
			]
		);
	}

	/**
	 * Echos generated dismissible notice.
	 *
	 * @since 2.7.0
	 * @since 4.1.2 Added the $inline parameter.
	 * @TODO deprecate -- Use the more reliable and secure persistent notices registry instead...
	 *                    Then again, this allows for AJAX-generated notices.
	 * @see register_dismissible_persistent_notice()
	 *
	 * @param string $message The notice message. Expected to be escaped if $escape is false.
	 * @param string $type    The notice type : 'updated', 'error', 'warning', 'info'. Expected to be escaped.
	 * @param bool   $icon    Whether to add an accessibility icon.
	 * @param bool   $escape  Whether to escape the whole output.
	 * @param bool   $inline Whether WordPress should be allowed to move it.
	 */
	public function do_dismissible_notice( $message = '', $type = 'updated', $icon = true, $escape = true, $inline = false ) {
		// phpcs:ignore, WordPress.Security.EscapeOutput -- use $escape
		echo $this->generate_dismissible_notice( $message, $type, $icon, $escape, $inline );
	}

	/**
	 * Echos dismissible persistent notice to screen.
	 *
	 * @since 4.1.0
	 *
	 * @param string $message    The notice message. Expected to be escaped if $escape is false.
	 * @param string $key        The unique notice key used to dismiss notices.
	 * @param array  $args       : {
	 *    'type'   => string Optional. The notification type. Default 'updated'.
	 *    'icon'   => bool   Optional. Whether to enable accessibility. Default true.
	 *    'escape' => bool   Optional. Whether to escape the $message. Default true.
	 * }
	 */
	protected function output_dismissible_persistent_notice( $message, $key, $args ) { // phpcs:ignore,VariableAnalysis.CodeAnalysis
		$this->get_view( 'notice/persistent', get_defined_vars() );
	}

	/**
	 * Outputs registered dismissible persistent notice.
	 *
	 * @since 4.1.0
	 * @since 4.1.2 Now only ignores timeout values of -1 to test against.
	 * @uses $this->output_dismissible_persistent_notice()
	 * @uses $this->count_down_persistent_notice()
	 * @global string $page_hook
	 */
	protected function output_dismissible_persistent_notices() {

		$notices    = $this->get_static_cache( 'persistent_notices', [] );
		$screenbase = \get_current_screen()->base ?? '';

		// Ideally, we don't want to output more than one on no-js. Alas, we can't anticipate the importance and order of the notices.
		foreach ( $notices as $key => $notice ) {
			$cond = $notice['conditions'];

			if (
				   ! \current_user_can( $cond['capability'] )
				|| ( $cond['user'] && $cond['user'] !== $this->get_user_id() )
				|| ( $cond['screens'] && ! \in_array( $screenbase, $cond['screens'], true ) )
				|| ( $cond['excl_screens'] && \in_array( $screenbase, $cond['excl_screens'], true ) )
			) continue;

			if ( -1 !== $cond['timeout'] && $cond['timeout'] < time() ) {
				$this->clear_persistent_notice( $key );
				continue;
			}

			// phpcs:ignore, WordPress.Security.EscapeOutput -- use $notice['args']['escape']
			$this->output_dismissible_persistent_notice( $notice['message'], $key, $notice['args'] );

			$this->count_down_persistent_notice( $key, $cond['count'] );
		}
	}

	/**
	 * Returns the SEO Bar.
	 *
	 * @since 4.0.0
	 * @uses \The_SEO_Framework\Interpreters\SEOBar::generate_bar();
	 *
	 * @param array $query : {
	 *   int    $id        : Required. The current post or term ID.
	 *   string $taxonomy  : Optional. If not set, this will interpret it as a post.
	 *   string $post_type : Optional. If not set, this will be automatically filled.
	 *                                 This parameter is ignored for taxonomies.
	 * }
	 * @return string The generated SEO bar, in HTML.
	 */
	public function get_generated_seo_bar( $query ) {
		return Interpreters\SEOBar::generate_bar( $query );
	}

	/**
	 * Outputs floating and reference title HTML elements for JavaScript.
	 *
	 * Do not use. Legacy item output for backward compatibility.
	 *
	 * @since 3.0.4
	 * @since 4.1.0 Now only outputs the legacy reference and noadditions reference.
	 * @since 4.1.2 Now prevents wp-emoji.js parsing the reference.
	 * @ignore
	 * @todo deprecate
	 */
	public function output_js_title_elements() {
		echo '<span data-ignore-me=legacy id=tsf-title-reference class="tsf-title-reference wp-exclude-emoji hidden" data-do-not-use=legacy></span>';
	}

	/**
	 * Outputs reference description HTML elements for JavaScript for a specific ID.
	 *
	 * @since 4.1.0
	 * @since 4.1.2 Now prevents wp-emoji.js parsing the references and data.
	 *
	 * @param string $id The input ID.
	 * @param array  $data The input data.
	 */
	public function output_js_title_data( $id, $data ) {
		vprintf(
			implode(
				'',
				[
					'<span id="tsf-title-reference_%1$s" class="tsf-title-reference wp-exclude-emoji hidden" data-for="%1$s"></span>',
					'<span id="tsf-title-noadditions-reference_%1$s" class="tsf-title-noadditions-reference wp-exclude-emoji hidden" data-for="%1$s"></span>',
					'<span id="tsf-title-offset_%1$s" class="tsf-title-offset wp-exclude-emoji hide-if-no-tsf-js" data-for="%1$s"></span>',
					'<span id="tsf-title-placeholder-additions_%1$s" class="tsf-title-placeholder-additions wp-exclude-emoji hide-if-no-tsf-js" data-for="%1$s"></span>',
					'<span id="tsf-title-placeholder-prefix_%1$s" class="tsf-title-placeholder-prefix wp-exclude-emoji hide-if-no-tsf-js" data-for="%1$s"></span>',
					'<span id="tsf-title-data_%1$s" class="hidden wp-exclude-emoji" data-for="%1$s" %2$s></span>',
				]
			),
			[
				\esc_attr( $id ),
				// phpcs:ignore, WordPress.Security.EscapeOutput -- make_data_attributes escapes.
				Interpreters\HTML::make_data_attributes( $data ),
			]
		);
	}

	/**
	 * Outputs reference social HTML elements for JavaScript for a specific ID.
	 *
	 * @since 4.2.0
	 *
	 * @param string       $group    The social input group ID.
	 * @param array[og,tw] $settings The input settings data.
	 */
	public function output_js_social_data( $group, $settings ) {
		vprintf(
			'<span id="tsf-social-data_%1$s" class="hidden wp-exclude-emoji" data-group="%1$s" %2$s></span>',
			[
				\esc_attr( $group ),
				// phpcs:ignore, WordPress.Security.EscapeOutput -- make_data_attributes escapes.
				Interpreters\HTML::make_data_attributes( [ 'settings' => $settings ] ),
			]
		);
	}

	/**
	 * Outputs reference description HTML elements for JavaScript.
	 *
	 * Do not use. Legacy item output for backward compatibility.
	 *
	 * @since 3.0.4
	 * @since 4.1.2 Now prevents wp-emoji.js parsing the reference.
	 * @ignore
	 * @todo deprecate
	 */
	public function output_js_description_elements() {
		echo '<span data-ignore-me=legacy id=tsf-description-reference class="tsf-description-reference wp-exclude-emoji hidden" data-do-not-use=legacy></span>';
	}

	/**
	 * Outputs reference description HTML elements for JavaScript for a specific ID.
	 *
	 * @since 4.1.0
	 * @since 4.1.2 Now prevents wp-emoji.js parsing the references and data.
	 *
	 * @param string $id   The description input ID.
	 * @param array  $data The input data.
	 */
	public function output_js_description_data( $id, $data ) {
		vprintf(
			implode(
				'',
				[
					'<span id="tsf-description-reference_%1$s" class="hidden wp-exclude-emoji" data-for="%1$s" ></span>',
					'<span id="tsf-description-data_%1$s" class="hidden wp-exclude-emoji" data-for="%1$s" %2$s ></span>',
				]
			),
			[
				\esc_attr( $id ),
				// phpcs:ignore, WordPress.Security.EscapeOutput -- make_data_attributes escapes.
				Interpreters\HTML::make_data_attributes( $data ),
			]
		);
	}
}
