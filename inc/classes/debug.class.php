<?php
/**
 * @package The_SEO_Framework\Classes\Debug
 * @subpackage The_SEO_Framework\Debug
 */

namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

// phpcs:disable, WordPress.PHP.DevelopmentFunctions -- This whole class is meant for development.

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
 * Singleton class The_SEO_Framework\Debug
 *
 * Holds plugin debug functions.
 *
 * @since 2.8.0
 * @since 4.0.0 No longer implements an interface. It's implied.
 */
final class Debug {
	use Traits\Enclose_Stray_Private;

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

		if ( is_null( static::$instance ) ) {
			static::$instance = new static();
		}

		if ( isset( $debug ) ) {
			static::$instance->the_seo_framework_debug = (bool) $debug;
		}
	}

	/**
	 * Gets the class instance. It's set when it's null.
	 *
	 * @since 2.8.0
	 *
	 * @return object The current instance.
	 */
	public static function get_instance() {

		if ( is_null( static::$instance ) ) {
			static::_set_instance();
		}

		return static::$instance;
	}

	/**
	 * Mark a filter as deprecated and inform when it has been used.
	 *
	 * @since 2.8.0
	 * @see @this->_deprecated_function().
	 *
	 * @param string $filter      The function that was called.
	 * @param string $version     The version of WordPress that deprecated the function.
	 * @param string $replacement Optional. The function that should have been called. Default null.
	 */
	public function _deprecated_filter( $filter, $version, $replacement = null ) {
		$this->_deprecated_function( 'Filter ' . $filter, $version, $replacement ); // phpcs:ignore -- Wrong asserts, copied method name.
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

			set_error_handler( [ $this, 'error_handler_deprecated' ] );

			if ( isset( $replacement ) ) {
				trigger_error(
					sprintf(
						/* translators: 1: Function name, 2: 'Deprecated', 3: Plugin Version notification, 4: Replacement function */
						\esc_html__( '%1$s is %2$s since version %3$s of The SEO Framework! Use %4$s instead.', 'autodescription' ),
						\esc_html( $function ),
						'<strong>' . \esc_html__( 'deprecated', 'autodescription' ) . '</strong>',
						\esc_html( $version ),
						$replacement // phpcs:ignore, WordPress.Security.EscapeOutput -- See doc comment.
					)
				);
			} else {
				trigger_error(
					sprintf(
						/* translators: 1: Function name, 2: 'Deprecated', 3: Plugin Version notification */
						\esc_html__( '%1$s is %2$s since version %3$s of The SEO Framework with no alternative available.', 'autodescription' ),
						\esc_html( $function ),
						'<strong>' . \esc_html__( 'deprecated', 'autodescription' ) . '</strong>',
						\esc_html( $version )
					)
				);
			}

			restore_error_handler();
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

			set_error_handler( [ $this, 'error_handler_doing_it_wrong' ] );

			/* translators: %s = Version number */
			$version = empty( $version ) ? '' : sprintf( \__( '(This message was added in version %s of The SEO Framework.)', 'autodescription' ), $version );
			trigger_error(
				sprintf(
					/* translators: 1: Function name, 2: 'Incorrectly', 3: Error message 4: Plugin Version notification */
					\esc_html__( '%1$s was called %2$s. %3$s %4$s', 'autodescription' ),
					\esc_html( $function ),
					'<strong>' . \esc_html__( 'incorrectly', 'autodescription' ) . '</strong>',
					$message, // phpcs:ignore, WordPress.Security.EscapeOutput -- See doc comment.
					\esc_html( $version )
				)
			);

			restore_error_handler();
		}
	}

	/**
	 * Mark a property or method inaccessible when it has been used.
	 * The current behavior is to trigger a user error if WP_DEBUG is true.
	 *
	 * @since 2.7.0
	 * @since 2.8.0 1. Now escapes all parameters.
	 *              2. Removed check for gettext.
	 * @access private
	 * @todo Escape translation string.
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

			set_error_handler( [ $this, 'error_handler_inaccessible_call' ] );

			trigger_error(
				sprintf(
					/* translators: 1: Method or Property name, 2: Message */
					\esc_html__( '%1$s is not accessible. %2$s', 'autodescription' ),
					'<code>' . \esc_html( $p_or_m ) . '</code>',
					\esc_html( $message )
				),
				E_USER_ERROR
			);

			restore_error_handler();
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
	 * @see PHP debug_backtrace()
	 *
	 * @return array The erroneous caller data
	 */
	protected function get_error() {

		// phpcs:ignore, WordPress.PHP.NoSilencedErrors -- Feature may be disabled.
		$backtrace = @debug_backtrace();
		/**
		 * 0 = This function.
		 * 1 = Error handler.
		 * 2 = Error forwarder.
		 * 3 = Debug handler.
		 */
		if ( isset( $backtrace[7]['object'] ) && is_a( $backtrace[7]['object'], \the_seo_framework_class(), false ) ) {
			/**
			 * 4 = TSF Factory magic method.
			 * 5 = Error invoking thing.
			 * 6 = TSF Factory. (the_seo_framework(), or a variable that stored this)
			 * 7 = Erroneous caller.
			 */
			$error = $backtrace[7];
		} else {
			/**
			 * 4 = Error invoking thing.
			 * 5 = Erroneous caller.
			 */
			$error = $backtrace[5];
		}

		return $error;
	}

	/**
	 * The SEO Framework error handler.
	 *
	 * Only handles user notices: E_USER_NOTICE
	 *
	 * @since 2.6.0
	 * @since 3.2.2 Fixed unaccounted-for backtrace depth logic since this class decoupling in 3.1
	 *
	 * @param int    $code    The error handler code.
	 * @param string $message The error message. Expected to be escaped.
	 */
	protected function error_handler_deprecated( $code, $message ) {

		//* Only do so if E_USER_NOTICE is passed.
		if ( E_USER_NOTICE & $code && isset( $message ) ) {
			$this->error_handler( $this->get_error(), $message );
		}
	}

	/**
	 * The SEO Framework error handler.
	 *
	 * Only handles user notices.
	 *
	 * @since 2.6.0
	 *
	 * @param int    $code    The error handler code.
	 * @param string $message The error message. Expected to be escaped.
	 */
	protected function error_handler_doing_it_wrong( $code, $message ) {

		//* Only do so if E_USER_NOTICE is pased.
		if ( E_USER_NOTICE === $code && isset( $message ) ) {
			$this->error_handler( $this->get_error(), $message );
		}
	}

	/**
	 * The SEO Framework error handler.
	 *
	 * Only handles user errors.
	 *
	 * @since 2.6.0
	 * @since 3.2.2 Fixed unaccounted-for backtrace depth logic since this class decoupling in 3.1
	 *
	 * @param int    $code    The error handler code.
	 * @param string $message The error message. Expected to be escaped.
	 */
	protected function error_handler_inaccessible_call( $code, $message ) {

		//* Only do so if E_USER_ERROR is pased.
		if ( E_USER_ERROR === $code && isset( $message ) ) {
			$this->error_handler( $this->get_error(), $message, $code );
		}
	}

	/**
	 * Echos error.
	 *
	 * @since 2.6.0
	 * @since 2.8.0 added $code parameter
	 *
	 * @param array  $error   The Error location and file data extruded from debug_backtrace().
	 * @param string $message The error message. Expected to be escaped.
	 * @param int    $code    The error handler code.
	 */
	protected function error_handler( $error, $message, $code = E_USER_NOTICE ) {

		$file = isset( $error['file'] ) ? $error['file'] : '';
		$line = isset( $error['line'] ) ? $error['line'] : '';

		if ( isset( $message ) ) {
			switch ( $code ) :
				case E_USER_ERROR:
					$type = 'Error';
					break;

				case E_USER_WARNING:
					$type = 'Warning';
					break;

				case E_USER_NOTICE:
				default:
					$type = 'Notice';
					break;
			endswitch;

			// phpcs:ignore, WordPress.Security.EscapeOutput -- output is escaped.
			echo sprintf( '<span><strong>%s:</strong> ', $type ) . $message;
			echo $file ? ' In ' . \esc_html( $file ) : '';
			echo $line ? ' on line ' . \esc_html( $line ) : '';
			echo '.</span><br>' . PHP_EOL;
		}
	}

	/**
	 * Echos debug output.
	 *
	 * @since 2.6.0
	 * @since 2.8.0 is now static.
	 * @access private
	 */
	public function _debug_output() {
		\the_seo_framework()->get_view( 'debug/output' );
	}

	/**
	 * Wrap debug key in a colored span.
	 *
	 * @since 2.3.9
	 * @since 3.1.0 1. Removed second parameter.
	 *              2. Now is protected.
	 *
	 * @param string $key The debug key.
	 * @return string
	 */
	protected function debug_key_wrapper( $key ) {
		return '<font color="chucknorris">' . \esc_attr( $key ) . '</font>';
	}

	/**
	 * Wrap debug value in a colored span.
	 *
	 * @since 2.3.9
	 * @since 3.1.0 1. Removed second parameter.
	 *              2. Now is protected.
	 *
	 * @param string $value The debug value.
	 * @return string
	 */
	protected function debug_value_wrapper( $value ) {

		if ( ! is_scalar( $value ) )
			return '<em>Debug message: not scalar</em>';

		return '<span class="wp-ui-notification">' . \esc_attr( trim( $value ) ) . '</span>';
	}

	/**
	 * Times code until it's called again.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Now is protected.
	 *
	 * @param bool $reset Whether to reset the timer.
	 * @return float The time it took for code execution.
	 */
	protected function timer( $reset = false ) {

		static $previous = null;

		if ( isset( $previous ) && false === $reset ) {
			$output   = microtime( true ) - $previous;
			$previous = null;
		} else {
			$output = $previous = microtime( true );
		}

		return $output;
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
	 *
	 * @return string Wrapped SEO meta tags output.
	 */
	protected function get_debug_header_output() {

		$tsf = \the_seo_framework();

		if ( \is_admin() && ! $tsf->is_term_edit() && ! $tsf->is_post_edit() && ! $tsf->is_seo_settings_page( true ) )
			return;

		if ( $tsf->is_seo_settings_page( true ) )
			\add_filter( 'the_seo_framework_current_object_id', [ $tsf, 'get_the_front_page_ID' ] );

		//* Start timer.
		$this->timer( true );

		$output = $tsf->robots()
				. $tsf->the_description()
				. $tsf->og_image()
				. $tsf->og_locale()
				. $tsf->og_type()
				. $tsf->og_title()
				. $tsf->og_description()
				. $tsf->og_url()
				. $tsf->og_sitename()
				. $tsf->facebook_publisher()
				. $tsf->facebook_author()
				. $tsf->facebook_app_id()
				. $tsf->article_published_time()
				. $tsf->article_modified_time()
				. $tsf->twitter_card()
				. $tsf->twitter_site()
				. $tsf->twitter_creator()
				. $tsf->twitter_title()
				. $tsf->twitter_description()
				. $tsf->twitter_image()
				. $tsf->shortlink()
				. $tsf->canonical()
				. $tsf->paged_urls()
				. $tsf->ld_json()
				. $tsf->google_site_output()
				. $tsf->bing_site_output()
				. $tsf->yandex_site_output()
				. $tsf->pint_site_output();

		$timer = '<div style="display:inline-block;width:100%;padding:20px;border-bottom:1px solid #ccc;">Generated in: ' . number_format( $this->timer(), 5 ) . ' seconds</div>';

		$title = \is_admin() ? 'Expected SEO Output' : 'Determined SEO Output';
		$title = '<div style="display:inline-block;width:100%;padding:20px;margin:0 auto;border-bottom:1px solid #ccc;"><h2 style="color:#ddd;font-size:22px;padding:0;margin:0">' . $title . '</h2></div>';

		//* Escape it, replace EOL with breaks, and style everything between quotes (which are ending with space).
		$output = str_replace( PHP_EOL, '<br>' . PHP_EOL, \esc_html( str_replace( str_repeat( ' ', 4 ), str_repeat( '&nbsp;', 4 ), $output ) ) );
		$output = preg_replace( '/(&quot;.*?&quot;)(\s|&nbps;)/', '<font color="arnoldschwarzenegger">$1</font> ', $output );

		$output = '<div style="display:inline-block;width:100%;padding:20px;font-family:Consolas,Monaco,monospace;font-size:14px;">' . $output . '</div>';
		$output = '<div style="display:block;width:100%;background:#23282D;color:#ddd;border-bottom:1px solid #ccc">' . $title . $timer . $output . '</div>';

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
	 * @since 3.1.0 : Introducted in 2.8.0, but the name changed.
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

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		return $cache = $this->get_debug_query_output( 'yup' );
	}

	/**
	 * Wraps query status booleans in human-readable code.
	 *
	 * @since 2.6.6
	 * @since 4.0.0 Cleaned up global callers; only use TSF methods.
	 *
	 * @param string $cache_version 'yup' or 'nope'
	 * @return string Wrapped Query State debug output.
	 */
	protected function get_debug_query_output( $cache_version = 'nope' ) {

		//* Start timer.
		$this->timer( true );

		$tsf = \the_seo_framework();

		//* Only get true/false values.
		$page_id              = $tsf->get_the_real_ID();
		$is_404               = $tsf->is_404();
		$is_admin             = $tsf->is_admin();
		$is_attachment        = $tsf->is_attachment();
		$is_archive           = $tsf->is_archive();
		$is_term_edit         = $tsf->is_term_edit();
		$is_post_edit         = $tsf->is_post_edit();
		$is_wp_lists_edit     = $tsf->is_wp_lists_edit();
		$is_author            = $tsf->is_author();
		$is_blog_page         = $tsf->is_blog_page();
		$is_category          = $tsf->is_category();
		$is_date              = $tsf->is_date();
		$is_year              = $tsf->is_year();
		$is_month             = $tsf->is_month();
		$is_day               = $tsf->is_day();
		$is_feed              = $tsf->is_feed();
		$is_real_front_page   = $tsf->is_real_front_page();
		$is_front_page_by_id  = $tsf->is_front_page_by_id( $tsf->get_the_real_ID() );
		$is_home              = $tsf->is_home();
		$is_page              = $tsf->is_page();
		$page                 = $tsf->page();
		$paged                = $tsf->paged();
		$is_preview           = $tsf->is_preview();
		$is_customize_preview = $tsf->is_customize_preview();
		$is_search            = $tsf->is_search();
		$is_single            = $tsf->is_single();
		$is_singular          = $tsf->is_singular();
		$is_static_frontpage  = $tsf->is_static_frontpage();
		$is_tag               = $tsf->is_tag();
		$is_tax               = $tsf->is_tax();
		$is_wc_shop           = $tsf->is_wc_shop();
		$is_wc_product        = $tsf->is_wc_product();
		$is_seo_settings_page = $tsf->is_seo_settings_page( true );
		$numpages             = $tsf->numpages();
		$is_multipage         = $tsf->is_multipage();
		$is_singular_archive  = $tsf->is_singular_archive();
		$is_term_meta_capable = $tsf->is_term_meta_capable();
		$is_post_type_archive = \is_post_type_archive();

		//* Don't debug the class object.
		unset( $tsf );

		//* Get all above vars, split them in two (true and false) and sort them by key names.
		$vars        = get_defined_vars();
		$current     = array_filter( $vars );
		$not_current = array_diff_key( $vars, $current );

		ksort( $current );
		ksort( $not_current );

		$timer = $this->timer();

		$output = '';
		foreach ( $current as $name => $value ) {
			$type = '(' . gettype( $value ) . ')';

			if ( is_bool( $value ) ) {
				$value = $value ? 'true' : 'false';
			} else {
				$value = \esc_attr( var_export( $value, true ) );
			}

			$value   = '<font color="harrisonford">' . $type . ' ' . $value . '</font>';
			$out     = \esc_html( $name ) . ' => ' . $value;
			$output .= '<span style="background:#dadada">' . $out . '</span>' . PHP_EOL;
		}

		foreach ( $not_current as $name => $value ) {
			$type = '(' . gettype( $value ) . ')';

			if ( is_bool( $value ) ) {
				$value = $value ? 'true' : 'false';
			} else {
				$value = \esc_attr( var_export( $value, true ) );
			}

			$value = '<font color="harrisonford">' . $type . ' ' . $value . '</font>';
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
				number_format( $timer, 5 )
			),
			sprintf(
				'<div style="display:inline-block;width:100%%;padding:20px;font-family:Consolas,Monaco,monospace;font-size:14px;">%s</div>',
				$output
			)
		);

		return $output;
	}
}
