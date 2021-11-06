<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Admin_Init
 * @subpackage The_SEO_Framework\Admin
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2021 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
	 * Initializes SEO Bar tables.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	public function _init_seo_bar_tables() {
		if ( $this->get_option( 'display_seo_bar_tables' ) )
			new Bridges\SEOBar;
	}

	/**
	 * Initializes List Edit tables.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	public function _init_list_edit() {
		new Bridges\ListEdit;
	}

	/**
	 * Adds post states for the post/page edit.php query.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @param array    $states The current post states array
	 * @param \WP_Post $post   The Post Object.
	 * @return array Adjusted $states
	 */
	public function _add_post_state( $states = [], $post = null ) {

		$post_id = $post->ID ?? false;

		if ( $post_id ) {
			$search_exclude  = $this->get_option( 'alter_search_query' ) && $this->get_post_meta_item( 'exclude_local_search', $post_id );
			$archive_exclude = $this->get_option( 'alter_archive_query' ) && $this->get_post_meta_item( 'exclude_from_archive', $post_id );

			if ( $search_exclude )
				$states[] = \esc_html__( 'No Search', 'autodescription' );

			if ( $archive_exclude )
				$states[] = \esc_html__( 'No Archive', 'autodescription' );
		}

		return $states;
	}

	/**
	 * Prepares scripts in the admin area.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 Now discerns autoloading between taxonomies and singular types.
	 * @since 4.1.0 Now invokes autoloading when persistent scripts are enqueued (regardless of validity).
	 * @since 4.1.2 Now autoenqueues on edit.php and edit-tags.php regardless of SEO Bar output (for quick/bulk-edit support).
	 * @since 4.1.4 Now considers headlessness.
	 * @access private
	 *
	 * @param string|null $hook The current page hook.
	 */
	public function _init_admin_scripts( $hook = null ) {

		$autoenqueue = false;

		if ( $this->is_seo_settings_page() ) {
			$autoenqueue = true;
		} elseif ( $hook ) {

			$enqueue_hooks = [];

			$prepare_edit_screen = false;

			if ( ! $this->is_headless['meta'] ) {
				if ( $this->is_archive_admin() ) {
					$prepare_edit_screen = $this->is_taxonomy_supported();
				} elseif ( $this->is_singular_admin() ) {
					$prepare_edit_screen = $this->is_post_type_supported( $this->get_admin_post_type() );
				}
			}

			if ( $prepare_edit_screen ) {
				$enqueue_hooks = [
					'edit.php',
					'post.php',
					'post-new.php',
					'edit-tags.php',
					'term.php',
				];
			}

			if ( \in_array( $hook, $enqueue_hooks, true ) )
				$autoenqueue = true;

			if ( $this->get_static_cache( 'persistent_notices', [] ) )
				$autoenqueue = true;
		}

		$autoenqueue and $this->init_admin_scripts();
	}

	/**
	 * Registers admin scripts and styles.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 First parameter is now deprecated.
	 * @since 4.0.0 First parameter is now removed.
	 *
	 * @return void Early if already enqueued.
	 */
	public function init_admin_scripts() {

		if ( _has_run( __METHOD__ ) ) return;

		Bridges\Scripts::_init();
	}

	/**
	 * Returns the title and description input guideline table, for
	 * (Google) search, Open Graph, and Twitter.
	 *
	 * Memoizes the output, so the return filter will run only once.
	 *
	 * NB: Some scripts have wide characters. These are recognized by Google, and have been adjusted for in the chactacter
	 * guidelines. German is a special Case, where we account for the Capitalization of Nouns.
	 *
	 * NB: Although the Arabic & Farsi scripts are much smaller in width, Google seems to be using the 160 & 70 char limits
	 * strictly... As such, we stricten the guidelines for pixels instead.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 1. Now gives different values for various WordPress locales.
	 *              2. Added $locale input parameter.
	 * @TODO Consider splitting up search into Google, Bing, etc., as we might
	 *       want users to set their preferred search engine. Now, these engines
	 *       are barely any different.
	 * TODO move this to another object?
	 *
	 * @param string|null $locale The locale to test. If empty, it will be auto-determined.
	 * @return array
	 */
	public function get_input_guidelines( $locale = null ) {

		$locale = $locale ?: \get_locale();

		// Strip the "_formal" and other suffixes. 5 length: xx_YY
		$locale = substr( $locale, 0, 5 );

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo( null, $locale ) ) return $memo;

		// phpcs:disable, WordPress.WhiteSpace.OperatorSpacing.SpacingAfter
		$character_adjustments = [
			'as'    => 148 / 160, // Assamese (অসমীয়া)
			'de_AT' => 158 / 160, // Austrian German (Österreichisch Deutsch)
			'de_CH' => 158 / 160, // Swiss German (Schweiz Deutsch)
			'de_DE' => 158 / 160, // German (Deutsch)
			'gu'    => 148 / 160, // Gujarati (ગુજરાતી)
			'ml_IN' => 100 / 160, // Malayalam (മലയാളം)
			'ja'    =>  70 / 160, // Japanese (日本語)
			'ko_KR' =>  82 / 160, // Korean (한국어)
			'ta_IN' => 120 / 160, // Tamil (தமிழ்)
			'zh_TW' =>  70 / 160, // Taiwanese Mandarin (Traditional Chinese) (繁體中文)
			'zh_HK' =>  70 / 160, // Hong Kong (Chinese version) (香港中文版)
			'zh_CN' =>  70 / 160, // Mandarin (Simplified Chinese) (简体中文)
		];
		// phpcs:enable, WordPress.WhiteSpace.OperatorSpacing.SpacingAfter

		$c_adjust = $character_adjustments[ $locale ] ?? 1;

		$pixel_adjustments = [
			'ar'    => 760 / 910, // Arabic (العربية)
			'ary'   => 760 / 910, // Moroccan Arabic (العربية المغربية)
			'azb'   => 760 / 910, // South Azerbaijani (گؤنئی آذربایجان)
			'fa_IR' => 760 / 910, // Iran Farsi (فارسی)
			'haz'   => 760 / 910, // Hazaragi (هزاره گی)
			'ckb'   => 760 / 910, // Central Kurdish (كوردی)
		];

		$p_adjust = $pixel_adjustments[ $locale ] ?? 1;

		// phpcs:disable, WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
		/**
		 * @since 3.1.0
		 * @param array $guidelines The title and description guidelines.
		 *              Don't alter the format. Only change the numeric values.
		 */
		return memo(
			(array) \apply_filters(
				'the_seo_framework_input_guidelines',
				[
					'title' => [
						'search' => [
							'chars'  => [
								'lower'     => (int) ( 25 * $c_adjust ),
								'goodLower' => (int) ( 35 * $c_adjust ),
								'goodUpper' => (int) ( 65 * $c_adjust ),
								'upper'     => (int) ( 75 * $c_adjust ),
							],
							'pixels' => [
								'lower'     => (int) ( 200 * $p_adjust ),
								'goodLower' => (int) ( 280 * $p_adjust ),
								'goodUpper' => (int) ( 520 * $p_adjust ),
								'upper'     => (int) ( 600 * $p_adjust ),
							],
						],
						'opengraph' => [
							'chars'  => [
								'lower'     => 15,
								'goodLower' => 25,
								'goodUpper' => 88,
								'upper'     => 100,
							],
							'pixels' => [],
						],
						'twitter' => [
							'chars'  => [
								'lower'     => 15,
								'goodLower' => 25,
								'goodUpper' => 69,
								'upper'     => 70,
							],
							'pixels' => [],
						],
					],
					'description' => [
						'search' => [
							'chars'  => [
								'lower'     => (int) ( 45 * $c_adjust ),
								'goodLower' => (int) ( 80 * $c_adjust ),
								'goodUpper' => (int) ( 160 * $c_adjust ),
								'upper'     => (int) ( 320 * $c_adjust ),
							],
							'pixels' => [
								'lower'     => (int) ( 256 * $p_adjust ),
								'goodLower' => (int) ( 455 * $p_adjust ),
								'goodUpper' => (int) ( 910 * $p_adjust ),
								'upper'     => (int) ( 1820 * $p_adjust ),
							],
						],
						'opengraph' => [
							'chars'  => [
								'lower'     => 45,
								'goodLower' => 80,
								'goodUpper' => 200,
								'upper'     => 300,
							],
							'pixels' => [],
						],
						'twitter' => [
							'chars'  => [
								'lower'     => 45,
								'goodLower' => 80,
								'goodUpper' => 200,
								'upper'     => 200,
							],
							'pixels' => [],
						],
					],
				]
			),
			$locale
		);
		// phpcs:enable, WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
	}

	/**
	 * Returns the title and description input guideline explanatory table.
	 *
	 * Already attribute-escaped.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 Now added a short leading-dot version for ARIA labels.
	 * @TODO move this to another object? -> i18n/guidelines
	 *
	 * @return array
	 */
	public function get_input_guidelines_i18n() {
		return [
			'long'     => [
				'empty'       => \esc_attr__( "There's no content.", 'autodescription' ),
				'farTooShort' => \esc_attr__( "It's too short and it should have more information.", 'autodescription' ),
				'tooShort'    => \esc_attr__( "It's short and it could have more information.", 'autodescription' ),
				'tooLong'     => \esc_attr__( "It's long and it might get truncated in search.", 'autodescription' ),
				'farTooLong'  => \esc_attr__( "It's too long and it will get truncated in search.", 'autodescription' ),
				'good'        => \esc_attr__( 'Length is good.', 'autodescription' ),
			],
			'short'    => [
				'empty'       => \esc_attr_x( 'Empty', 'The string is empty', 'autodescription' ),
				'farTooShort' => \esc_attr__( 'Far too short', 'autodescription' ),
				'tooShort'    => \esc_attr__( 'Too short', 'autodescription' ),
				'tooLong'     => \esc_attr__( 'Too long', 'autodescription' ),
				'farTooLong'  => \esc_attr__( 'Far too long', 'autodescription' ),
				'good'        => \esc_attr__( 'Good', 'autodescription' ),
			],
			'shortdot' => [
				'empty'       => \esc_attr_x( 'Empty.', 'The string is empty', 'autodescription' ),
				'farTooShort' => \esc_attr__( 'Far too short.', 'autodescription' ),
				'tooShort'    => \esc_attr__( 'Too short.', 'autodescription' ),
				'tooLong'     => \esc_attr__( 'Too long.', 'autodescription' ),
				'farTooLong'  => \esc_attr__( 'Far too long.', 'autodescription' ),
				'good'        => \esc_attr__( 'Good.', 'autodescription' ),
			],
		];
	}

	/**
	 * Checks ajax referred set by set_js_nonces based on capability.
	 *
	 * Performs die() on failure.
	 *
	 * @since 3.1.0 Introduced in 2.9.0, but the name changed.
	 * @access private
	 *         It uses an internally and manually created prefix.
	 * @uses WP Core check_ajax_referer()
	 * @see @link https://developer.wordpress.org/reference/functions/check_ajax_referer/
	 *
	 * @param string $capability The capability that was required for the nonce check to be created.
	 * @return false|int False if the nonce is invalid, 1 if the nonce is valid
	 *                   and generated between 0-12 hours ago, 2 if the nonce is
	 *                   valid and generated between 12-24 hours ago.
	 */
	public function _check_tsf_ajax_referer( $capability ) {
		return \check_ajax_referer( "tsf-ajax-$capability", 'nonce', true );
	}

	/**
	 * Redirect the user to an admin page, and add query args to the URL string
	 * for alerts, etc.
	 *
	 * @since 2.2.2
	 * @since 2.9.2 Added user-friendly exception handling.
	 * @since 2.9.3 1. Query arguments work again (regression 2.9.2).
	 *              2. Now only accepts http and https protocols.
	 * @since 4.2.0 Now allows query arguments with value 0|'0'.
	 *
	 * @param string $page Menu slug. This slug must exist, or the redirect will loop back to the current page.
	 * @param array  $query_args Optional. Associative array of query string arguments
	 *               (key => value). Default is an empty array.
	 * @return null Return early if first argument is false.
	 */
	public function admin_redirect( $page, $query_args = [] ) {

		if ( empty( $page ) ) return;

		// This can be empty... so $target will be empty. TODO test for $success and bail?
		// Might cause security issues... we _must_ exit, always? Show warning?
		$url = html_entity_decode( \menu_page_url( $page, false ) );

		$target = \add_query_arg( array_filter( $query_args, 'strlen' ), $url );
		$target = \esc_url_raw( $target, [ 'https', 'http' ] );

		// Predict white screen:
		$headers_sent = headers_sent();

		/**
		 * Dev debug:
		 * 1. Change 302 to 500 if you wish to test headers.
		 * 2. Also force handle_admin_redirect_error() to run.
		 */
		\wp_safe_redirect( $target, 302 );

		// White screen of death for non-debugging users. Let's make it friendlier.
		if ( $headers_sent )
			$this->handle_admin_redirect_error( $target );

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

		if ( ! $target ) return;

		$headers_list = headers_list();
		$location     = sprintf( 'Location: %s', \wp_sanitize_redirect( $target ) );

		// Test if WordPress's redirect header is sent. Bail if true.
		if ( \in_array( $location, $headers_list, true ) )
			return;

		// phpcs:disable, WordPress.Security.EscapeOutput -- convert_markdown escapes. Added esc_url() for sanity.
		printf( '<p><strong>%s</strong></p>',
			$this->convert_markdown(
				sprintf(
					/* translators: %s = Redirect URL markdown */
					\esc_html__( 'There has been an error redirecting. Refresh the page or follow [this link](%s).', 'autodescription' ),
					\esc_url( $target )
				),
				[ 'a' ],
				[ 'a_internal' => true ]
			)
		);
		// phpcs:enable, WordPress.Security.EscapeOutput
	}

	/**
	 * Registers dismissible persistent notice, that'll respawn during page load until dismissed or otherwise expired.
	 *
	 * @since 4.1.0
	 * @since 4.1.3 Now handles timeout values below -1 gracefully, by purging the whole notification gracelessly.
	 * @uses $this->generate_dismissible_persistent_notice()
	 *
	 * @param string $message    The notice message. Expected to be escaped if $escape is false.
	 *                           When the message contains HTML, it must start with a <p> tag,
	 *                           or it will be added for you--regardless of proper semantics.
	 * @param string $key        The notice key. Must be unique--prevents double-registering of the notice, and allows for
	 *                           deregistering of the notice.
	 * @param array  $args       : {
	 *    'type'   => string Optional. The notification type. Default 'updated'.
	 *    'icon'   => bool   Optional. Whether to enable icon. Default true.
	 *    'escape' => bool   Optional. Whether to escape the $message. Default true.
	 * }
	 * @param array  $conditions : {
	 *     'capability'   => string Required. The user capability required for the notice to display. Defaults to settings capability.
	 *     'screens'      => array  Optional. The screen bases the notice may be displayed on. When left empty, it'll output on any page.
	 *     'excl_screens' => array  Optional. The screen bases the notice may NOT be displayed on. When left empty, only `screens` applies.
	 *     'user'         => int    Optional. The user ID to display the notice for. Capability will not be ignored.
	 *     'count'        => int    Optional. The number of times the persistent notice may appear (for everyone allowed to see it).
	 *                              Set to -1 for unlimited. When -1, the notice must be removed from display manually.
	 *     'timeout'      => int    Optional. The number of seconds the notice should remain valid for display. Set to -1 to disable check.
	 *                              When the timeout is below -1, then the notification will not be outputted.
	 *                              Do not input non-integer values (such as `false`), for those might cause adverse events.
	 * }
	 */
	public function register_dismissible_persistent_notice( $message, $key, $args = [], $conditions = [] ) {

		// We made this mistake ourselves. Let's test against it.
		// We can't type $key to scalar, for PHP is dumb with that type.
		if ( ! is_scalar( $key ) || ! \strlen( $key ) ) return;

		// Sanitize the key so that HTML, JS, and PHP can communicate easily via it.
		$key = \sanitize_key( $key );

		$args = array_merge(
			[
				'type'   => 'updated',
				'icon'   => true,
				'escape' => true,
			],
			$args
		);

		$conditions = array_merge(
			[
				'screens'      => [],
				'excl_screens' => [],
				'capability'   => $this->get_settings_capability(),
				'user'         => 0,
				'count'        => 1,
				'timeout'      => -1,
			],
			$conditions
		);

		// Required key for security.
		if ( ! $conditions['capability'] ) return;

		// Timeout already expired. Let's not register it.
		if ( $conditions['timeout'] < -1 ) return;

		// Add current time to timeout, so we can compare against it later.
		if ( $conditions['timeout'] > -1 )
			$conditions['timeout'] += time();

		$notices         = $this->get_static_cache( 'persistent_notices', [] );
		$notices[ $key ] = compact( 'message', 'args', 'conditions' );

		$this->update_static_cache( 'persistent_notices', $notices );
	}

	/**
	 * Lowers the persistent notice display count.
	 * When the threshold is reached, the notice is deleted.
	 *
	 * @since 4.1.0
	 *
	 * @param string $key   The notice key.
	 * @param int    $count The number of counts the notice has left. Passed by reference.
	 *                      When -1 (permanent notice), nothing happens.
	 */
	public function count_down_persistent_notice( $key, &$count ) {

		$_count_before = $count;

		if ( $count > 0 )
			--$count;

		if ( ! $count ) {
			$this->clear_persistent_notice( $key );
		} elseif ( $_count_before !== $count ) {
			$notices = $this->get_static_cache( 'persistent_notices' );
			if ( isset( $notices[ $key ]['conditions']['count'] ) ) {
				$notices[ $key ]['conditions']['count'] = $count;
				$this->update_static_cache( 'persistent_notices', $notices );
			} else {
				// Notice didn't conform. Remove it.
				$this->clear_persistent_notice( $key );
			}
		}
	}

	/**
	 * Clears a persistent notice by key.
	 *
	 * @since 4.1.0
	 *
	 * @param string $key The notice key.
	 * @return bool True on success, false on failure.
	 */
	public function clear_persistent_notice( $key ) {

		$notices = $this->get_static_cache( 'persistent_notices', [] );
		unset( $notices[ $key ] );

		return $this->update_static_cache( 'persistent_notices', $notices );
	}

	/**
	 * Clears all registered persistent notices. Useful after upgrade.
	 *
	 * @since 4.1.0
	 *
	 * @return bool True on success, false on failure.
	 */
	public function clear_all_persistent_notices() {
		return $this->update_static_cache( 'persistent_notices', [] );
	}

	/**
	 * Returns the snaitized notice action key.
	 *
	 * @since 4.1.0
	 * @since 4.1.4 1. Now 'public', marked private.
	 *              2. Now uses underscores instead of dashes.
	 * @access private
	 *
	 * @param string $key The notice key.
	 * @return string The sanitized nonce action.
	 */
	public function _get_dismiss_notice_nonce_action( $key ) {
		return \sanitize_key( "tsf_notice_nonce_$key" );
	}

	/**
	 * Clears persistent notice on user request (clicked Dismiss icon) via the no-JS form.
	 *
	 * @since 4.1.0
	 * Security check OK.
	 */
	public function _dismiss_notice() {

		// phpcs:ignore, WordPress.Security.NonceVerification.Missing -- We require the POST data to find locally stored nonces.
		$key = $_POST['tsf-notice-submit'] ?? '';

		if ( ! $key ) return;

		$notices = $this->get_static_cache( 'persistent_notices', [] );
		// Notice was deleted already elsewhere, or key was faulty. Either way, ignore--should be self-resolving.
		if ( empty( $notices[ $key ]['conditions']['capability'] ) ) return;

		if ( ! \current_user_can( $notices[ $key ]['conditions']['capability'] )
		// phpcs:ignore, WordPress.Security.NonceVerification.Missing -- We require the POST data to find locally stored nonces.
		|| ! \wp_verify_nonce( $_POST['tsf_notice_nonce'] ?? '', $this->_get_dismiss_notice_nonce_action( $key ) ) ) {
			\wp_die( -1, 403 );
		}

		$this->clear_persistent_notice( $key );
	}
}
