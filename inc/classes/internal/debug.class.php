<?php
/**
 * @package The_SEO_Framework\Classes\Internal\Debug
 * @subpackage The_SEO_Framework\Debug
 */

namespace The_SEO_Framework\Internal;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\memo;

use \The_SEO_Framework\{
	Data,
	Front,
};
use \The_SEO_Framework\Helper\{
	Post_Type,
	Query,
	Taxonomy,
	Template,
};

// phpcs:disable, WordPress.PHP.DevelopmentFunctions -- This whole class is meant for development.

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Singleton class The_SEO_Framework\Internal\Debug
 *
 * Holds plugin debug functions.
 *
 * @since 2.8.0
 * @since 4.0.0 No longer implements an interface. It's implied.
 * @since 4.2.0 Changed namespace from \The_SEO_Framework to \The_SEO_Framework\Internal
 * @since 5.0.0 Is now private. This was never meant to be public.
 * @access private
 */
final class Debug {

	/**
	 * Mark a function as deprecated and inform when it has been used.
	 *
	 * Taken from WordPress core, but added extra parameters and linguistic alterations.
	 *
	 * The current behavior is to trigger a user error if WP_DEBUG is true.
	 *
	 * @since 2.6.0
	 * @since 2.8.0 Now escapes all input, except for $replacement.
	 * @since 4.1.1 No longer registers a custom error handler.
	 * @access private
	 *
	 * @param string $function     The function that was called.
	 * @param string $version      The version of WordPress that deprecated the function.
	 * @param string $replacement  Optional. The function that should have been called. Default null.
	 *                             Expected to be escaped.
	 */
	public static function _deprecated_function( $function, $version, $replacement = null ) { // phpcs:ignore -- Wrong asserts, copied method name.
		/**
		 * Fires when a deprecated function is called.
		 *
		 * @since WP Core 2.5.0
		 *
		 * @param string $function    The function that was called.
		 * @param string $replacement The function that should have been called.
		 * @param string $version     The version of WordPress that deprecated the function.
		 */
		\do_action( 'deprecated_function_run', $function, $replacement, $version );

		/**
		 * Filter whether to trigger an error for deprecated functions.
		 *
		 * @since WP Core 2.5.0
		 *
		 * @param bool $trigger Whether to trigger the error for deprecated functions. Default true.
		 */
		if ( \WP_DEBUG && \apply_filters( 'deprecated_function_trigger_error', true ) ) {

			if ( isset( $replacement ) ) {
				$message = \sprintf(
					/* translators: 1: Function name, 2: 'Deprecated', 3: Plugin Version notification, 4: Replacement function */
					\esc_html__( '%1$s is %2$s since version %3$s of The SEO Framework! Use %4$s instead.', 'autodescription' ),
					\esc_html( $function ),
					'<strong>' . \esc_html__( 'deprecated', 'autodescription' ) . '</strong>',
					\esc_html( $version ) ?: 'unknown',
					$replacement, // phpcs:ignore, WordPress.Security.EscapeOutput -- See doc comment.
				);
			} else {
				$message = \sprintf(
					/* translators: 1: Function name, 2: 'Deprecated', 3: Plugin Version notification */
					\esc_html__( '%1$s is %2$s since version %3$s of The SEO Framework with no alternative available.', 'autodescription' ),
					\esc_html( $function ),
					'<strong>' . \esc_html__( 'deprecated', 'autodescription' ) . '</strong>',
					\esc_html( $version ) ?: 'unknown',
				);
			}

			trigger_error(
				// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- combobulate_error_message escapes.
				static::combobulate_error_message( static::get_error(), $message, \E_USER_DEPRECATED ),
				\E_USER_DEPRECATED,
			);
		}
	}

	/**
	 * Mark a function as deprecated and inform when it has been used.
	 *
	 * Taken from WordPress core, but added extra parameters and linguistic alterations.
	 *
	 * The current behavior is to trigger a user error if WP_DEBUG is true.
	 *
	 * @since 2.6.0
	 * @since 2.8.0 Now escapes all input, except for $message.
	 * @since 4.1.1 No longer registers a custom error handler.
	 * @access private
	 *
	 * @param string $function The function that was called.
	 * @param string $message  A message explaining what has been done incorrectly. Must be escaped.
	 * @param string $version  The version of WordPress where the message was added.
	 */
	public static function _doing_it_wrong( $function, $message, $version = null ) { // phpcs:ignore -- Wrong asserts, copied method name.
		/**
		 * Fires when the given function is being used incorrectly.
		 *
		 * @since WP Core 3.1.0
		 *
		 * @param string $function The function that was called.
		 * @param string $message  A message explaining what has been done incorrectly.
		 * @param string $version  The version of WordPress where the message was added.
		 */
		\do_action( 'doing_it_wrong_run', $function, $message, $version );

		/**
		 * @since WP Core 3.1.0
		 * @param bool $trigger Whether to trigger the error for _doing_it_wrong() calls. Default true.
		 */
		if ( \WP_DEBUG && \apply_filters( 'doing_it_wrong_trigger_error', true ) ) {

			$ver_message = $version
				/* translators: 1: plugin version */
				? \sprintf( \__( '(This message was added in version %s of The SEO Framework.)', 'autodescription' ), $version )
				: '';

			$message = \sprintf(
				/* translators: 1: Function name, 2: 'Incorrectly', 3: Error message 4: Plugin Version notification */
				\esc_html__( '%1$s was called %2$s. %3$s %4$s', 'autodescription' ),
				\esc_html( $function ),
				'<strong>' . \esc_html__( 'incorrectly', 'autodescription' ) . '</strong>',
				$message, // phpcs:ignore, WordPress.Security.EscapeOutput -- See doc comment.
				\esc_html( $ver_message ),
			);

			trigger_error(
				// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- combobulate_error_message escapes.
				static::combobulate_error_message( static::get_error(), $message, \E_USER_NOTICE ),
				\E_USER_NOTICE,
			);
		}
	}

	/**
	 * Mark a property or method inaccessible when it has been used.
	 * The current behavior is to trigger a user error if WP_DEBUG is true.
	 *
	 * @since 2.7.0
	 * @since 2.8.0 1. Now escapes all parameters.
	 *              2. Removed check for gettext.
	 * @since 4.1.1 No longer registers a custom error handler.
	 * @since 5.0.0 Added third parameter $handle.
	 * @access private
	 *
	 * @param string $p_or_m  The Property or Method.
	 * @param string $message A message explaining what has been done incorrectly.
	 * @param string $handle  The method handler.
	 */
	public static function _inaccessible_p_or_m( $p_or_m, $message = '', $handle = 'tsf()' ) {

		/**
		 * Fires when the inaccessible property or method is being used.
		 *
		 * @since 2.7.0
		 *
		 * @param string $p_or_m  The Property or Method.
		 * @param string $message A message explaining what has been done incorrectly.
		 */
		\do_action( 'the_seo_framework_inaccessible_p_or_m_run', $p_or_m, $message );

		/**
		 * Filter whether to trigger an error for _doing_it_wrong() calls.
		 *
		 * @since WP Core 3.1.0
		 *
		 * @param bool $trigger Whether to trigger the error for _doing_it_wrong() calls. Default true.
		 */
		if ( \WP_DEBUG && \apply_filters( 'the_seo_framework_inaccessible_p_or_m_trigger_error', true ) ) {
			$message = \sprintf(
				/* translators: 1: Method or Property name, 2: "inaccessible", 3: Class name. 4: Message */
				\esc_html__( '%1$s is %2$s in %3$s. %4$s', 'autodescription' ),
				'<code>' . \esc_html( $p_or_m ) . '</code>',
				'<strong>' . \esc_html__( 'inaccessible', 'autodescription' ) . '</strong>',
				\sprintf( '<b>%s</b>', \esc_html( $handle ) ),
				\esc_html( $message ),
			);

			trigger_error(
				// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- combobulate_error_message escapes.
				static::combobulate_error_message( static::get_error(), $message, \E_USER_WARNING ),
				\E_USER_WARNING,
			);
		}
	}

	/**
	 * Retrieves the erroneous caller data.
	 *
	 * Assesses the depth of the caller based on "consistent" tracing.
	 * This is inaccurate when an error is invoked internally; but, you can trust that
	 * the plugin doesn't trigger this behavior.
	 *
	 * @since 3.2.2
	 * @since 4.1.1 Reworked to work with any error handler.
	 * @since 5.0.0 Now actualyl used my brain and added an automated object searcher instead of guessing.
	 * @see PHP debug_backtrace()
	 *
	 * @return array The erroneous caller data
	 */
	private static function get_error() {

		$backtrace = debug_backtrace( \DEBUG_BACKTRACE_PROVIDE_OBJECT, 6 );

		if ( ! $backtrace ) return [];

		/**
		 * Always one step before TSF:
		 * 0 = caller of this func
		 * 1 = tsf debugger
		 * 2 = debugger container
		 * 3 = container caller
		 */
		$error = $backtrace[3];

		// Search deeper if it exists. Skip the first 3.
		foreach ( \array_slice( $backtrace, 3 ) as $trace ) {
			if (
				   isset( $trace['object'] )
				&& is_a( $trace['object'], \the_seo_framework_class(), false )
			) {
				$error = $trace;
				break;
			}
		}

		return $error;
	}

	/**
	 * Somehow puts together a neat error message from unknown sources.
	 *
	 * @since 4.1.1
	 *
	 * @param array  $error   The debug_backtrace() error. May be an empty array.
	 * @param string $message The error message. May contain HTML. Expected to be escaped.
	 * @param int    $code    The error handler code.
	 */
	private static function combobulate_error_message( $error, $message, $code ) {

		switch ( $code ) {
			case \E_USER_ERROR:
				$type = 'Error';
				break;

			case \E_USER_DEPRECATED:
				$type = 'Deprecated';
				break;

			case \E_USER_WARNING:
				$type = 'Warning';
				break;

			case \E_USER_NOTICE:
			default:
				$type = 'Notice';
		}

		$file = \esc_html( $error['file'] ?? '' );
		$line = \esc_html( $error['line'] ?? '' );

		$_message  = "'<span><strong>$type:</strong> $message";
		$_message .= $file ? " In $file" : '';
		$_message .= $line ? " on line $line" : '';
		$_message .= "</span><br>\n";

		return $_message;
	}

	/**
	 * Echos debug output.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 is now static.
	 * @access private
	 */
	public static function _do_debug_output() {
		Template::output_view( 'debug/output' );
	}

	/**
	 * Outputs the debug header.
	 *
	 * @since 2.8.0
	 * @access private
	 */
	public static function _output_debug_header() {

		if ( \is_admin() && ! Query::is_term_edit() && ! Query::is_post_edit() && ! Query::is_seo_settings_page( true ) )
			return;

		if ( Query::is_seo_settings_page( true ) )
			\add_filter( 'the_seo_framework_current_object_id', static fn() => Query::get_the_front_page_id() );

		// phpcs:ignore, WordPress.Security.EscapeOutput -- callee escapes.
		Template::output_view( 'debug/header' );
	}

	/**
	 * Outputs debug query.
	 *
	 * @since 2.8.0
	 * @access private
	 */
	public static function _output_debug_query() {
		// phpcs:ignore, WordPress.Security.EscapeOutput -- This escapes.
		echo static::get_debug_query_output();
	}

	/**
	 * Outputs debug query from cache.
	 *
	 * @since 2.8.0
	 * @access private
	 */
	public static function _output_debug_query_from_cache() {
		// phpcs:ignore, WordPress.Security.EscapeOutput -- This escapes.
		echo static::get_debug_query_output_from_cache();
	}

	/**
	 * Sets debug query cache.
	 *
	 * @since 3.1.0 Introduced in 2.8.0, but the name changed.
	 * @access private
	 */
	public static function _set_debug_query_output_cache() {
		static::get_debug_query_output_from_cache();
	}

	/**
	 * Wraps query status booleans in human-readable code.
	 *
	 * @since 2.6.6
	 * @global bool $multipage
	 * @global int $numpages
	 *
	 * @return string Wrapped Query State debug output.
	 */
	private static function get_debug_query_output_from_cache() {
		return memo() ?? memo( static::get_debug_query_output( 'yup' ) );
	}

	/**
	 * Wraps query status booleans in human-readable code.
	 *
	 * @since 2.6.6
	 * @since 4.0.0 Cleaned up global callers; only use TSF methods.
	 * @since 4.1.0 Added more debugging variables added since 4.0.0.
	 *
	 * @param string $cache_version 'yup' or 'nope'
	 * @return string Wrapped Query State debug output.
	 */
	private static function get_debug_query_output( $cache_version = 'nope' ) {

		// Start timer.
		$_t = hrtime( true );

		// phpcs:disable, WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase -- Not this file's issue.
		// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable -- get_defined_vars() is used later.
		$page_id                        = Query::get_the_real_id();
		$is_query_exploited             = Query\Utils::is_query_exploited();
		$query_supports_seo             = Query\Utils::query_supports_seo();
		$is_404                         = \is_404();
		$is_admin                       = \is_admin();
		$is_attachment                  = Query::is_attachment();
		$is_archive                     = Query::is_archive();
		$is_term_edit                   = Query::is_term_edit();
		$is_post_edit                   = Query::is_post_edit();
		$is_wp_lists_edit               = Query::is_wp_lists_edit();
		$is_author                      = Query::is_author();
		$is_category                    = Query::is_category();
		$is_date                        = \is_date();
		$is_year                        = \is_year();
		$is_month                       = \is_month();
		$is_day                         = \is_day();
		$is_feed                        = \is_feed();
		$is_robots                      = \is_robots();
		$is_real_front_page             = Query::is_real_front_page();
		$is_blog                        = Query::is_blog();
		$is_blog_as_page                = Query::is_blog_as_page();
		$is_page                        = Query::is_page();
		$page                           = Query::page();
		$paged                          = Query::paged();
		$is_preview                     = Query::is_preview();
		$is_customize_preview           = \is_customize_preview();
		$is_search                      = Query::is_search();
		$is_single                      = Query::is_single();
		$is_singular                    = Query::is_singular();
		$is_static_front_page           = Query::is_static_front_page();
		$is_tag                         = Query::is_tag();
		$is_tax                         = Query::is_tax();
		$is_shop                        = Query::is_shop();
		$is_product                     = Query::is_product();
		$is_seo_settings_page           = Query::is_seo_settings_page( true );
		$numpages                       = Query::numpages();
		$is_multipage                   = Query::is_multipage();
		$is_singular_archive            = Query::is_singular_archive();
		$is_term_meta_capable           = Query::is_editable_term();
		$is_post_type_supported         = Post_Type::is_supported();
		$is_post_type_archive_supported = Post_Type::is_pta_supported();
		$has_page_on_front              = Query\Utils::has_page_on_front();
		$has_assigned_page_on_front     = Query\Utils::has_assigned_page_on_front();
		$has_blog_page                  = Query\Utils::has_blog_page();
		$is_taxonomy_supported          = Taxonomy::is_supported();
		$get_post_type                  = \get_post_type();
		$get_post_type_real_id          = Query::get_post_type_real_id();
		$admin_post_type                = Query::get_admin_post_type();
		$current_taxonomy               = Query::get_current_taxonomy();
		$current_post_type              = Query::get_current_post_type();
		$is_taxonomy_disabled           = Taxonomy::is_disabled();
		$is_post_type_archive           = \is_post_type_archive();
		$is_protected                   = Data\Post::is_protected( $page_id );
		$wp_doing_ajax                  = \wp_doing_ajax();
		$wp_doing_cron                  = \wp_doing_cron();
		$wp_is_rest                     = \defined( 'REST_REQUEST' ) && \REST_REQUEST; // TODO WP 6.5+ wp_is_serving_rest_request()
		// phpcs:enable, WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
		// phpcs:enable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

		$timer = ( hrtime( true ) - $_t ) / 1e9;

		// Get all above vars, split them in two (true and false) and sort them by key names.
		$vars = get_defined_vars();

		// Don't debug the class object nor timer.
		unset( $vars['timer'], $vars['_t'] );

		$current     = array_filter( $vars );
		$not_current = array_diff_key( $vars, $current );

		ksort( $current );
		ksort( $not_current );

		$output = '';
		foreach ( $current as $name => $value ) {
			$type = \esc_html( '(' . \gettype( $value ) . ')' );
			$name = \esc_html( $name );

			if ( \is_bool( $value ) ) {
				$value = $value ? 'true' : 'false';
			} else {
				$value = \esc_html( var_export( $value, true ) );
			}

			$output .= "<span style=background:#dadada>$name => <span style=color:#0a00f0>$type $value</span></span>\n";
		}

		foreach ( $not_current as $name => $value ) {
			$type = \esc_html( '(' . \gettype( $value ) . ')' );
			$name = \esc_html( $name );

			if ( \is_bool( $value ) ) {
				$value = $value ? 'true' : 'false';
			} else {
				$value = \esc_html( var_export( $value, true ) );
			}

			$output .= "$name => <span style=color:#0a00f0>$type $value</span>\n";
		}

		if ( 'yup' === $cache_version ) {
			$title = 'WordPress Query at Meta Generation';
		} else {
			$title = \is_admin() ? 'Current WordPress Admin Query' : 'Current WordPress Query';
		}

		$output = str_replace( [ "\r\n", "\r", "\n" ], "<br>\n", $output );
		$timer  = number_format( number_format( $timer, 5 ), 5 );

		return <<<HTML
			<div style="display:block;width:100%;background:#fafafa;color:#333;border-bottom:1px solid #666">
				<div style="display:inline-block;width:100%;padding:20px;margin:0 auto;border-bottom:1px solid #666">
					<h2 style="color:#222;font-size:22px;padding:0;margin:0">$title</h2>
				</div>
				<div style="display:inline-block;width:100%;padding:20px;border-bottom:1px solid #666">
					Generated in: $timer seconds
				</div>
				<div style="display:inline-block;width:100%;padding:20px;font-family:Consolas,Monaco,monospace;font-size:14px">
					$output
				</div>
			</div>
		HTML;
	}
}
