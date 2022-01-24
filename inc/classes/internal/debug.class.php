<?php
/**
 * @package The_SEO_Framework\Classes\Internal\Debug
 * @subpackage The_SEO_Framework\Debug
 */

namespace The_SEO_Framework\Internal;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2022 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// phpcs:disable, WordPress.PHP.DevelopmentFunctions -- This whole class is meant for development.

use function \The_SEO_Framework\memo;

/**
 * Singleton class The_SEO_Framework\Internal\Debug
 *
 * Holds plugin debug functions.
 *
 * @since 2.8.0
 * @since 4.0.0 No longer implements an interface. It's implied.
 * @since 4.2.0 Changed namespace from \The_SEO_Framework to \The_SEO_Framework\Internal
 */
final class Debug {

	/**
	 * @since 2.8.0
	 * @var object|null $instance This object instance.
	 */
	private static $instance = null;

	/**
	 * @since 2.8.0
	 * @var bool $the_seo_framework_debug Whether debug is enabled.
	 */
	public $the_seo_framework_debug = false;

	/**
	 * Constructor.
	 */
	protected function __construct() {}

	/**
	 * Sets the class instance.
	 *
	 * @since 3.1.0
	 * @access private
	 *
	 * @param bool|null $debug Whether TSF debugging is enabled.
	 */
	public static function _set_instance( $debug = null ) {

		if ( \is_null( static::$instance ) )
			static::$instance = new static();

		if ( isset( $debug ) )
			static::$instance->the_seo_framework_debug = (bool) $debug;
	}

	/**
	 * Gets the class instance. It's set when it's null.
	 *
	 * @since 2.8.0
	 *
	 * @return object The current instance.
	 */
	public static function get_instance() {

		if ( \is_null( static::$instance ) )
			static::_set_instance();

		return static::$instance;
	}

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
	public function _deprecated_function( $function, $version, $replacement = null ) { // phpcs:ignore -- Wrong asserts, copied method name.
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
		if ( WP_DEBUG && \apply_filters( 'deprecated_function_trigger_error', true ) ) {

			if ( isset( $replacement ) ) {
				$message = sprintf(
					/* translators: 1: Function name, 2: 'Deprecated', 3: Plugin Version notification, 4: Replacement function */
					\esc_html__( '%1$s is %2$s since version %3$s of The SEO Framework! Use %4$s instead.', 'autodescription' ),
					\esc_html( $function ),
					'<strong>' . \esc_html__( 'deprecated', 'autodescription' ) . '</strong>',
					\esc_html( $version ),
					$replacement // phpcs:ignore, WordPress.Security.EscapeOutput -- See doc comment.
				);
			} else {
				$message = sprintf(
					/* translators: 1: Function name, 2: 'Deprecated', 3: Plugin Version notification */
					\esc_html__( '%1$s is %2$s since version %3$s of The SEO Framework with no alternative available.', 'autodescription' ),
					\esc_html( $function ),
					'<strong>' . \esc_html__( 'deprecated', 'autodescription' ) . '</strong>',
					\esc_html( $version )
				);
			}

			trigger_error(
				// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- combobulate_error_message escapes.
				$this->combobulate_error_message( $this->get_error( E_USER_DEPRECATED ), $message, E_USER_DEPRECATED ),
				E_USER_DEPRECATED
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
	public function _doing_it_wrong( $function, $message, $version = null ) { // phpcs:ignore -- Wrong asserts, copied method name.
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
		 * Filter whether to trigger an error for _doing_it_wrong() calls.
		 *
		 * @since WP Core 3.1.0
		 *
		 * @param bool $trigger Whether to trigger the error for _doing_it_wrong() calls. Default true.
		 */
		if ( WP_DEBUG && \apply_filters( 'doing_it_wrong_trigger_error', true ) ) {

			/* translators: 1: plugin version */
			$version = $version ? sprintf( \__( '(This message was added in version %s of The SEO Framework.)', 'autodescription' ), $version ) : '';

			$message = sprintf(
				/* translators: 1: Function name, 2: 'Incorrectly', 3: Error message 4: Plugin Version notification */
				\esc_html__( '%1$s was called %2$s. %3$s %4$s', 'autodescription' ),
				\esc_html( $function ),
				'<strong>' . \esc_html__( 'incorrectly', 'autodescription' ) . '</strong>',
				$message, // phpcs:ignore, WordPress.Security.EscapeOutput -- See doc comment.
				\esc_html( $version )
			);

			trigger_error(
				// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- combobulate_error_message escapes.
				$this->combobulate_error_message( $this->get_error( E_USER_NOTICE ), $message, E_USER_NOTICE ),
				E_USER_NOTICE
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
	 * @access private
	 *
	 * @param string $p_or_m The Property or Method.
	 * @param string $message A message explaining what has been done incorrectly.
	 */
	public function _inaccessible_p_or_m( $p_or_m, $message = '' ) {

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
		if ( WP_DEBUG && \apply_filters( 'the_seo_framework_inaccessible_p_or_m_trigger_error', true ) ) {
			$message = sprintf(
				/* translators: 1: Method or Property name, 2: The SEO Framework class. 3: Message */
				\esc_html__( '%1$s is not accessible in %2$s. %3$s', 'autodescription' ),
				'<code>' . \esc_html( $p_or_m ) . '</code>',
				'<code>tsf()</code>',
				\esc_html( $message )
			);

			trigger_error(
				// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- combobulate_error_message escapes.
				$this->combobulate_error_message( $this->get_error( E_USER_WARNING ), $message, E_USER_WARNING ),
				E_USER_WARNING
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
	 * @see PHP debug_backtrace()
	 * @see $this->combobulate_error_message()
	 *
	 * @param int|null $type The error type, that helps us locate the error's origin.
	 * @return array The erroneous caller data
	 */
	protected function get_error( $type = null ) {

		$backtrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 5 );

		if ( ! $backtrace ) return [];

		if ( $type & E_USER_DEPRECATED ) {
			/**
			 * 0 = This function.
			 * 1 = Error handler (This class).
			 * 2 = Error forwarder (TSF class).
			 */
			if ( isset( $backtrace[4]['args'][0][0] ) && is_a( $backtrace[4]['args'][0][0], 'The_SEO_Framework\Internal\Deprecated', false ) ) {
				/**
				 * 3 = Deprecated call.
				 * 4 = TSF deprecation class forwarder.
				 * 5 = User mistake.
				 */
				$error = $backtrace[5];
			} else {
				/**
				 * 3 = Deprecated call & user mistake. (no forwarder)
				 */
				$error = $backtrace[3];
			}
		} else {
			/**
			 * 0 = This function.
			 * 1 = Error handler (This class).
			 * 2 = Error forwarder (TSF class).
			 */
			if ( isset( $backtrace[2]['object'] ) && is_a( $backtrace[2]['object'], \the_seo_framework_class(), false ) ) {
				/**
				 * 3 = Method with fault test & user mistake.
				 */
				$error = $backtrace[3];
			} else {
				/**
				 * 3 = Method with fault test.
				 * 4 = User mistake.
				 */
				$error = $backtrace[4];
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
	protected function combobulate_error_message( $error, $message, $code ) {

		switch ( $code ) :
			case E_USER_ERROR:
				$type = 'Error';
				break;

			case E_USER_DEPRECATED:
				$type = 'Deprecated';
				break;

			case E_USER_WARNING:
				$type = 'Warning';
				break;

			case E_USER_NOTICE:
			default:
				$type = 'Notice';
				break;
		endswitch;

		$file = \esc_html( $error['file'] ?? '' );
		$line = \esc_html( $error['line'] ?? '' );

		$_message  = "'<span><strong>$type:</strong> $message";
		$_message .= $file ? " In $file" : '';
		$_message .= $line ? " on line $line" : '';
		$_message .= '</span><br>' . PHP_EOL;

		return $_message;
	}

	/**
	 * Echos debug output.
	 *
	 * @since 2.6.0
	 * @since 2.8.0 is now static.
	 * @access private
	 */
	public function _debug_output() {
		\tsf()->get_view( 'debug/output' );
	}

	/**
	 * Outputs the debug header.
	 *
	 * @since 2.8.0
	 * @access private
	 */
	public static function _output_debug_header() {
		// phpcs:ignore, WordPress.Security.EscapeOutput -- callee escapes.
		echo static::get_instance()->get_debug_header_output();
	}

	/**
	 * Wraps header output in front-end code.
	 * This won't consider hiding the output.
	 *
	 * @since 2.6.5
	 * @since 4.0.5 Now obtains the real rendered HTML output, instead of estimated.
	 *
	 * @return string Wrapped SEO meta tags output.
	 */
	protected function get_debug_header_output() {

		$tsf = \tsf();

		if ( \is_admin() && ! $tsf->is_term_edit() && ! $tsf->is_post_edit() && ! $tsf->is_seo_settings_page( true ) )
			return;

		if ( $tsf->is_seo_settings_page( true ) )
			\add_filter( 'the_seo_framework_current_object_id', [ $tsf, 'get_the_front_page_ID' ] );

		// Start timer.
		$t = microtime( true );

		// I hate ob_*.
		ob_start();
		$tsf->html_output();
		$output = ob_get_clean();

		$timer = '<div style="font-family:unset;display:inline-block;width:100%;padding:20px;border-bottom:1px solid #ccc;">Generated in: ' . number_format( microtime( true ) - $t, 5 ) . ' seconds</div>';

		$title = \is_admin() ? 'Expected SEO Output' : 'Determined SEO Output';
		$title = '<div style="display:inline-block;width:100%;padding:20px;margin:0 auto;border-bottom:1px solid #ccc;"><h2 style="font-family:unset;color:#ddd;font-size:22px;padding:0;margin:0">' . $title . '</h2></div>';

		// Escape it, replace EOL with breaks, and style everything between quotes (which are ending with space).
		$output = str_replace( PHP_EOL, '<br>' . PHP_EOL, \esc_html( str_replace( str_repeat( ' ', 4 ), str_repeat( '&nbsp;', 4 ), $output ) ) );
		$output = preg_replace( '/(&quot;.*?&quot;)(\s|&nbps;)/', '<font color="arnoldschwarzenegger">$1</font> ', $output );

		$output = '<div style="display:inline-block;width:100%;padding:20px;font-family:Consolas,Monaco,monospace;font-size:14px;">' . $output . '</div>';
		$output = '<div style="font-family:unset;display:block;width:100%;background:#23282D;color:#ddd;border-bottom:1px solid #ccc">' . $title . $timer . $output . '</div>';

		return $output;
	}

	/**
	 * Outputs debug query.
	 *
	 * @since 2.8.0
	 * @access private
	 */
	public static function _output_debug_query() {
		// phpcs:ignore, WordPress.Security.EscapeOutput -- This escapes.
		echo static::$instance->get_debug_query_output();
	}

	/**
	 * Outputs debug query from cache.
	 *
	 * @since 2.8.0
	 * @access private
	 */
	public static function _output_debug_query_from_cache() {
		// phpcs:ignore, WordPress.Security.EscapeOutput -- This escapes.
		echo static::$instance->get_debug_query_output_from_cache();
	}

	/**
	 * Sets debug query cache.
	 *
	 * @since 3.1.0 Introducted in 2.8.0, but the name changed.
	 * @access private
	 */
	public function _set_debug_query_output_cache() {
		$this->get_debug_query_output_from_cache();
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
	protected function get_debug_query_output_from_cache() {
		return memo() ?? memo( $this->get_debug_query_output( 'yup' ) );
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
	protected function get_debug_query_output( $cache_version = 'nope' ) {

		// Start timer.
		$_t = microtime( true );

		$tsf = \tsf();

		// phpcs:disable, WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase -- Not this file's issue.
		// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable -- get_defined_vars() is used later.
		$page_id                = $tsf->get_the_real_ID();
		$is_query_exploited     = $tsf->is_query_exploited();
		$query_supports_seo     = $tsf->query_supports_seo() ? 'yes' : 'no';
		$is_404                 = $tsf->is_404();
		$is_admin               = $tsf->is_admin();
		$is_attachment          = $tsf->is_attachment();
		$is_archive             = $tsf->is_archive();
		$is_term_edit           = $tsf->is_term_edit();
		$is_post_edit           = $tsf->is_post_edit();
		$is_wp_lists_edit       = $tsf->is_wp_lists_edit();
		$is_author              = $tsf->is_author();
		$is_category            = $tsf->is_category();
		$is_date                = $tsf->is_date();
		$is_year                = $tsf->is_year();
		$is_month               = $tsf->is_month();
		$is_day                 = $tsf->is_day();
		$is_feed                = $tsf->is_feed();
		$is_real_front_page     = $tsf->is_real_front_page();
		$is_home                = $tsf->is_home();
		$is_home_as_page        = $tsf->is_home_as_page();
		$is_page                = $tsf->is_page();
		$page                   = $tsf->page();
		$paged                  = $tsf->paged();
		$is_preview             = $tsf->is_preview();
		$is_customize_preview   = $tsf->is_customize_preview();
		$is_search              = $tsf->is_search();
		$is_single              = $tsf->is_single();
		$is_singular            = $tsf->is_singular();
		$is_static_frontpage    = $tsf->is_static_frontpage();
		$is_tag                 = $tsf->is_tag();
		$is_tax                 = $tsf->is_tax();
		$is_shop                = $tsf->is_shop();
		$is_product             = $tsf->is_product();
		$is_seo_settings_page   = $tsf->is_seo_settings_page( true );
		$numpages               = $tsf->numpages();
		$is_multipage           = $tsf->is_multipage();
		$is_singular_archive    = $tsf->is_singular_archive();
		$is_term_meta_capable   = $tsf->is_term_meta_capable();
		$is_post_type_supported = $tsf->is_post_type_supported();
		$is_taxonomy_supported  = $tsf->is_taxonomy_supported();
		$get_post_type          = \get_post_type();
		$get_post_type_real_ID  = $tsf->get_post_type_real_ID();
		$admin_post_type        = $tsf->get_admin_post_type();
		$current_taxonomy       = $tsf->get_current_taxonomy();
		$current_post_type      = $tsf->get_current_post_type();
		$is_taxonomy_disabled   = $tsf->is_taxonomy_disabled();
		$is_post_type_archive   = \is_post_type_archive();
		$is_protected           = $tsf->is_protected( $page_id );
		// phpcs:enable, WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
		// phpcs:enable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

		$timer = microtime( true ) - $_t;

		// Get all above vars, split them in two (true and false) and sort them by key names.
		$vars = get_defined_vars();

		// Don't debug the class object nor timer.
		unset( $vars['tsf'], $vars['timer'], $vars['_t'] );

		$current     = array_filter( $vars );
		$not_current = array_diff_key( $vars, $current );

		ksort( $current );
		ksort( $not_current );

		$output = '';
		foreach ( $current as $name => $value ) {
			$type = '(' . \gettype( $value ) . ')';

			if ( \is_bool( $value ) ) {
				$value = $value ? 'true' : 'false';
			} else {
				$value = \esc_html( var_export( $value, true ) );
			}

			$value   = '<font color="harrisonford">' . "$type $value" . '</font>';
			$out     = \esc_html( $name ) . ' => ' . $value;
			$output .= '<span style="background:#dadada">' . $out . '</span>' . PHP_EOL;
		}

		foreach ( $not_current as $name => $value ) {
			$type = '(' . \gettype( $value ) . ')';

			if ( \is_bool( $value ) ) {
				$value = $value ? 'true' : 'false';
			} else {
				$value = \esc_html( var_export( $value, true ) );
			}

			$value = '<font color="harrisonford">' . "$type $value" . '</font>';
			$out   = \esc_html( $name ) . ' => ' . $value;

			$output .= $out . PHP_EOL;
		}

		if ( 'yup' === $cache_version ) {
			$title = 'WordPress Query at Meta Generation';
		} else {
			$title = \is_admin() ? 'Expected Front-end WordPress Query' : 'Current WordPress Query';
		}

		$output = str_replace( PHP_EOL, '<br>' . PHP_EOL, $output );
		$output = sprintf(
			'<div style="display:block;width:100%%;background:#fafafa;color:#333;border-bottom:1px solid #666">%s%s%s</div>',
			sprintf(
				'<div style="display:inline-block;width:100%%;padding:20px;margin:0 auto;border-bottom:1px solid #666;"><h2 style="color:#222;font-size:22px;padding:0;margin:0">%s</h2></div>',
				$title
			),
			sprintf(
				'<div style="display:inline-block;width:100%%;padding:20px;border-bottom:1px solid #666;">Generated in: %s seconds</div>',
				number_format( number_format( $timer, 5 ), 5 )
			),
			sprintf(
				'<div style="display:inline-block;width:100%%;padding:20px;font-family:Consolas,Monaco,monospace;font-size:14px;">%s</div>',
				$output
			)
		);

		return $output;
	}
}
