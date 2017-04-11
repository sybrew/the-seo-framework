<?php
/**
 * @package The_SEO_Framework\Classes
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 */
final class Debug implements Debug_Interface {

	/**
	 * Enqueue the debug output.
	 *
	 * @since 2.6.0
	 *
	 * @var string The debug output.
	 */
	protected $debug_output = '';

	/**
	 * Whether to accumulate data.
	 *
	 * @since 2.6.5
	 *
	 * @var bool Whether to continue adding to The_SEO_Framework_Debug::debug_output
	 * within The_SEO_Framework_Debug::debug_init().
	 */
	protected $add_debug_output = true;

	/**
	 * The object instance.
	 *
	 * @since 2.8.0
	 *
	 * @var object|null This object instance.
	 */
	private static $instance = null;

	/**
	 * Cached debug/profile properties.
	 *
	 * @since 2.8.0
	 *
	 * @var bool Whether debug is enabled.
	 * @var bool Whether debug is hidden in HTMl.
	 */
	public $the_seo_framework_debug = false;
	public $the_seo_framework_debug_hidden = false;

	/**
	 * Unserializing instances of this object is forbidden.
	 */
	final protected function __wakeup() { }

	/**
	 * Cloning of this object is forbidden.
	 */
	final protected function __clone() { }

	/**
	 * Constructor.
	 */
	final protected function __construct() {}

	/**
	 * Sets the class instance.
	 *
	 * @since 2.8.0
	 * @access private
	 */
	public static function set_instance( $debug = null, $hidden = null ) {

		if ( is_null( static::$instance ) ) {
			static::$instance = new static();
		}

		if ( isset( $debug ) ) {
			static::$instance->the_seo_framework_debug = (bool) $debug;
			static::$instance->the_seo_framework_debug_hidden = (bool) $hidden;
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
			static::set_instance();
		}

		return static::$instance;
	}

	/**
	 * Mark a filter as deprecated and inform when it has been used.
	 *
	 * @since 2.8.0
	 * @see @this->_deprecated_function().
	 *
	 * @param string $filter		The function that was called.
	 * @param string $version		The version of WordPress that deprecated the function.
	 * @param string $replacement	Optional. The function that should have been called. Default null.
	 */
	public function _deprecated_filter( $filter, $version, $replacement = null ) {
		$this->_deprecated_function( 'Filter ' . $filter, $version, $replacement );
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
	public function _deprecated_function( $function, $version, $replacement = null ) {
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

			set_error_handler( array( $this, 'error_handler_deprecated' ) );

			if ( isset( $replacement ) ) {
				trigger_error(
					/* translators: 1: Function name, 2: 'Deprecated', 3: Plugin Version notification, 4: Replacement function */
					sprintf( \esc_html__( '%1$s is %2$s since version %3$s of The SEO Framework! Use %4$s instead.', 'autodescription' ),
						\esc_html( $function ),
						'<strong>' . \esc_html__( 'deprecated', 'autodescription' ) . '</strong>',
						\esc_html( $version ),
						$replacement
					)
				);
			} else {
				trigger_error(
					/* translators: 1: Function name, 2: 'Deprecated', 3: Plugin Version notification */
					sprintf( \esc_html__( '%1$s is %2$s since version %3$s of The SEO Framework with no alternative available.', 'autodescription' ),
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
	 * @param string $function	The function that was called.
	 * @param string $message	A message explaining what has been done incorrectly.
	 * @param string $version	The version of WordPress where the message was added.
	 */
	public function _doing_it_wrong( $function, $message, $version = null ) {
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
		* @since 3.1.0
		*
		* @param bool $trigger Whether to trigger the error for _doing_it_wrong() calls. Default true.
		*/
		if ( WP_DEBUG && \apply_filters( 'doing_it_wrong_trigger_error', true ) ) {

			set_error_handler( array( $this, 'error_handler_doing_it_wrong' ) );

			$version = empty( $version ) ? '' : sprintf( \__( '(This message was added in version %s of The SEO Framework.)' ), $version );
			trigger_error(
				/* translators: 1: Function name, 2: 'Incorrectly', 3: Error message 4: Plugin Version notification */
				sprintf( \esc_html__( '%1$s was called %2$s. %3$s %4$s', 'autodescription' ),
					\esc_html( $function ),
					'<strong>' . \esc_html__( 'incorrectly', 'autodescription' ) . '</strong>',
					//* Expected to be escaped.
					$message,
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
		 * @param string $p_or_m	The Property or Method.
		 * @param string $message	A message explaining what has been done incorrectly.
		 */
		\do_action( 'the_seo_framework_inaccessible_p_or_m_run', $p_or_m, $message );

		/**
		 * Filter whether to trigger an error for _doing_it_wrong() calls.
		 *
		 * @since 3.1.0
		 *
		 * @param bool $trigger Whether to trigger the error for _doing_it_wrong() calls. Default true.
		 */
		if ( WP_DEBUG && \apply_filters( 'the_seo_framework_inaccessible_p_or_m_trigger_error', true ) ) {

			set_error_handler( array( $this, 'error_handler_inaccessible_call' ) );

			/* translators: 1: Method or Property name, 2: Message */
			trigger_error( sprintf( \esc_html__( '%1$s is not accessible. %2$s', 'autodescription' ), '<code>' . \esc_html( $p_or_m ) . '</code>', \esc_html( $message ) ), E_USER_ERROR );

			restore_error_handler();
		}
	}

	/**
	 * The SEO Framework error handler.
	 *
	 * Only handles notices.
	 * @see E_USER_NOTICE
	 *
	 * @since 2.6.0
	 *
	 * @param int Error handling code.
	 * @param string The error message.
	 */
	protected function error_handler_deprecated( $code, $message ) {

		//* Only do so if E_USER_NOTICE is pased.
		if ( 1024 === $code && isset( $message ) ) {

			$backtrace = debug_backtrace();
			/**
			 * 0 = This function. 1 = Debug function. 2 = Error trigger. 3 = Deprecated Class, 4 = Deprecated Method, 5 = Magic Method, 6 = Deprecated call.
			 * 0 = This function. 1 = Debug function. 2 = Error trigger. 3 = Deprecated Class, 4 = Deprecated Filter, 5 = Deprecated call.
			 */
			if ( 'Filter ' === substr( $message, 0, 7 ) ) {
				$error = $backtrace[5];
			} else {
				$error = $backtrace[6];
			}

			$this->error_handler( $error, $message );
		}
	}

	/**
	 * The SEO Framework error handler.
	 *
	 * Only handles notices.
	 * @see E_USER_NOTICE
	 *
	 * @since 2.6.0
	 *
	 * @param int Error handling code.
	 * @param string The error message.
	 */
	protected function error_handler_doing_it_wrong( $code, $message ) {

		//* Only do so if E_USER_NOTICE is pased.
		if ( 1024 === $code && isset( $message ) ) {

			$backtrace = debug_backtrace();
			/**
			 * 0 = This function. 1 = Debug function. 2 = magic methods, 3 = Error trigger.
			 */
			$error = $backtrace[3];

			$this->error_handler( $error, $message );
		}
	}

	/**
	 * The SEO Framework error handler.
	 *
	 * Only handles notices.
	 * @see E_USER_ERROR
	 *
	 * @since 2.6.0
	 *
	 * @param int Error handling code.
	 * @param string The error message.
	 */
	protected function error_handler_inaccessible_call( $code, $message ) {

		//* Only do so if E_USER_ERROR is pased.
		if ( 256 === $code && isset( $message ) ) {

			$backtrace = debug_backtrace();

			/**
			 * 0 = This function. 1-3 = Debug functions. 4-5 = magic methods, 6 = user call.
			 */
			$error = $backtrace[6];

			$this->error_handler( $error, $message, $code );
		}
	}

	/**
	 * Echos error.
	 *
	 * @since 2.6.0
	 * @since 2.8.0 added $code parameter
	 *
	 * @param array $error The Error location and file.
	 * @param string $message The error message. Expected to be escaped.
	 * @param int $code The error handler code.
	 */
	protected function error_handler( $error, $message, $code = E_USER_NOTICE ) {

		$file = isset( $error['file'] ) ? $error['file'] : '';
		$line = isset( $error['line'] ) ? $error['line'] : '';

		if ( isset( $message ) ) {
			switch ( $code ) :
				case E_USER_ERROR :
					$type = 'Error';
					break;

				case E_USER_WARNING :
					$type = 'Warning';
					break;

				case E_USER_NOTICE :
				default :
					$type = 'Notice';
					break;
			endswitch;

			//* Already escaped.
			echo sprintf( '<span><strong>%s:</strong> ', $type ) . $message;
			echo $file ? ' In ' . \esc_html( $file ) : '';
			echo $line ? ' on line ' . \esc_html( $line ) : '';
			echo '.</span><br>' . PHP_EOL;
		}
	}

	/**
	 * Adds found screens in the admin footer when debugging is enabled.
	 *
	 * @since 2.5.2
	 * @access private
	 * @global object $current_screen This object is passed through get_defined_vars().
	 */
	public function debug_screens() {
		global $current_screen;

		$this->debug_init( __METHOD__, false, '', get_defined_vars() );
	}

	/**
	 * Echos debug output.
	 *
	 * @since 2.6.0
	 * @since 2.8.0 is now static.
	 * @access private
	 */
	public function debug_output() {
		\the_seo_framework()->get_view( 'debug/output', array( 'debug_output' => $this->debug_output ) );
	}

	/**
	 * Determines if there's debug output.
	 *
	 * @since 2.8.0
	 * @access private
	 *
	 * @return bool True if there's output.
	 */
	public static function has_debug_output() {
		$instance = static::get_instance();
		return (bool) $instance->debug_output;
	}

	/**
	 * Outputs the debug_output property.
	 *
	 * @since 2.8.0
	 * @access private
	 */
	public static function _output_debug() {

		$instance = static::get_instance();
		//* Already escaped.
		echo $instance->debug_output;

	}

	/**
	 * Parses input values and wraps them in human-readable elements.
	 *
	 * @since 2.6.0
	 * @access private
	 *
	 * @param mixed $values Values to be parsed.
	 * @return string $output The parsed value.
	 */
	public function get_debug_information( $values = null ) {

		$output = '';

		if ( $this->the_seo_framework_debug ) {

			$output .= PHP_EOL;
			$output .= $this->the_seo_framework_debug_hidden ? '' : '<span class="code highlight">';

			if ( is_null( $values ) ) {
				$output .= $this->debug_value_wrapper( 'null' ) . PHP_EOL;
				$output .= $this->the_seo_framework_debug_hidden ? '' : '</span>';

				return $output;
			}

			if ( is_object( $values ) ) {
				//* Turn objects into arrays.
				$values = (array) $values;

				foreach ( $values as $key => $value ) {
					if ( is_object( $value ) ) {
						foreach ( (array) $value as $key => $v ) {
							$values = $v;
							break;
						}
					}
					break;
				}
			}

			/**
			 * @TODO Use var_export()?
			 */
			if ( is_array( $values ) ) {
				$output .= $this->the_seo_framework_debug_hidden ? '' : '<div style="margin:0;padding-left:12px">';
				foreach ( $values as $key => $value ) {
					$output .= "\t\t";
					if ( '' === $value ) {
						$output .= $this->debug_key_wrapper( $key ) . ' => ';
						$output .= $this->debug_value_wrapper( "''" );
						$output .= PHP_EOL;
					} elseif ( is_string( $value ) || is_int( $value ) ) {
						$output .= $this->debug_key_wrapper( $key ) . ' => ';
						$output .= $this->debug_value_wrapper( $value );
						$output .= PHP_EOL;
					} elseif ( is_bool( $value ) ) {
						$output .= $this->debug_key_wrapper( $key ) . ' => ';
						$output .= $this->debug_value_wrapper( $value ? 'true' : 'false' );
						$output .= PHP_EOL;
					} elseif ( is_array( $value ) ) {
						$output .= $this->debug_key_wrapper( $key ) . ' => ';
						$output .= 'Array[' . PHP_EOL;
						$output .= $this->the_seo_framework_debug_hidden ? '' : '<p style="margin:0;padding-left:12px">';
						foreach ( $value as $k => $v ) {
							$output .= "\t\t\t";
							if ( '' === $v ) {
								$output .= $this->debug_key_wrapper( $k ) . ' => ';
								$output .= $this->debug_value_wrapper( "''" );
							} elseif ( is_string( $v ) || is_int( $v ) ) {
								$output .= $this->debug_key_wrapper( $k ) . ' => ';
								$output .= $this->debug_value_wrapper( $v );
							} elseif ( is_bool( $v ) ) {
								$output .= $this->debug_key_wrapper( $k ) . ' => ';
								$output .= $this->debug_value_wrapper( $v ? 'true' : 'false' );
							} elseif ( is_array( $v ) ) {
								$output .= $this->debug_key_wrapper( $k ) . ' => ';
								$output .= $this->debug_value_wrapper( 'Debug message: Three+ dimensional array' );
							} else {
								$output .= $this->debug_key_wrapper( $k ) . ' => ';
								$output .= $this->debug_value_wrapper( $v );
							}
							$output .= ',';
							$output .= PHP_EOL;
							$output .= $this->the_seo_framework_debug_hidden ? '' : '<br>';
						}
						$output .= $this->the_seo_framework_debug_hidden ? '' : '</p>';
						$output .= ']';
					} else {
						$output .= $this->debug_key_wrapper( $key ) . ' => ';
						$output .= $this->debug_value_wrapper( $value );
						$output .= PHP_EOL;
					}
					$output .= $this->the_seo_framework_debug_hidden ? '' : '<br>';
				}
				$output .= $this->the_seo_framework_debug_hidden ? '' : '</div>';
			} elseif ( '' === $values ) {
				$output .= "\t\t";
				$output .= $this->debug_value_wrapper( "''" );
			} elseif ( is_string( $values ) || is_int( $values ) ) {
				$output .= "\t\t";
				$output .= $this->debug_value_wrapper( $values );
			} elseif ( is_bool( $values ) ) {
				$output .= "\t\t";
				$output .= $this->debug_value_wrapper( $values ? 'true' : 'false' );
			} else {
				$output .= "\t\t";
				$output .= $this->debug_value_wrapper( $values );
			}

			$output .= $this->the_seo_framework_debug_hidden ? '' : '</span>';
			$output .= PHP_EOL;
		}

		return $output;
	}

	/**
	 * Wrap debug key in a colored span.
	 *
	 * @param string $key The debug key.
	 * @param bool $ignore Ignore the hidden output.
	 *
	 * @since 2.3.9
	 * @access private
	 *
	 * @return string
	 */
	public function debug_key_wrapper( $key, $ignore = false ) {

		if ( $ignore || false === $this->the_seo_framework_debug_hidden )
			return '<font color="chucknorris">' . \esc_attr( $key ) . '</font>';

		return \esc_attr( $key );
	}

	/**
	 * Wrap debug value in a colored span.
	 *
	 * @param string $value The debug value.
	 * @param bool $ignore Ignore the hidden output.
	 *
	 * @since 2.3.9
	 * @access private
	 *
	 * @return string
	 */
	public function debug_value_wrapper( $value, $ignore = false ) {

		if ( ! is_scalar( $value ) )
			return 'Debug message: not scalar';

		if ( "''" === $value && $this->the_seo_framework_debug_hidden )
			return html_entity_decode( $value );

		if ( $ignore || false === $this->the_seo_framework_debug_hidden )
			return '<span class="wp-ui-notification">' . \esc_attr( trim( $value ) ) . '</span>';

		return \esc_attr( $value );
	}

	/**
	 * Debug init. Simplified way of debugging a function, only works in admin.
	 *
	 * @since 2.6.0
	 * @access private
	 *
	 * @param string $method The function name.
	 * @param bool $store Whether to store the output in cache for next run to pick up on.
	 * @param double $debug_key Use $debug_key as variable, it's reserved.
	 * @param mixed function args.
	 * @return void early if debugging is disabled or when storing cache values.
	 */
	public function debug_init( $method, $store, $debug_key ) {

		if ( false === $this->the_seo_framework_debug || false === $this->add_debug_output )
			return;

		$output = '';

		if ( func_num_args() >= 4 ) {

			//* Cache the args for $store.
			static $cached_args = array();
			static $hold_args = array();

			$args = array_slice( func_get_args(), 3 );
			//* Shift array.
			isset( $args[0][0] ) and $args = $args[0][0];

			$key = $method . '_' . $debug_key;

			if ( $store ) {
				$this->profile( false, false, 'time', $key ) . ' seconds';
				$this->profile( false, false, 'memory', $key ) . ' bytes';

				unset( $args['debug_key'] );

				$cached_args[ $method ] = $args;
				$hold_args[ $method ] = $args;
				return;
			} else {

				/**
				 * Generate human-readable debug keys and echo it when it's called.
				 * Matched value is found within the $output.
				 *
				 * @staticvar int $loop
				 */
				static $loop = 0;
				$loop++;
				$debug_key = '[Debug key: ' . $loop . ' - ' . $method . ']';

				if ( \the_seo_framework()->is_admin() && 'admin_footer' !== \current_action() )
					echo $this->the_seo_framework_debug_hidden ? \esc_html( PHP_EOL . $debug_key ) . ' action. ' : '<p>' . \esc_html( $debug_key ) . '</p>';

				$output .= $this->the_seo_framework_debug_hidden ? \esc_html( PHP_EOL . $debug_key ) . ' output. ' : '<h3>' . \esc_html( $debug_key ) . '</h3>';

				if ( isset( $cached_args[ $method ] ) ) {
					$args[] = array(
						'profile' => array(
							'time' => $this->profile( false, true, 'time', $key ) . ' seconds',
							'memory' => $this->profile( false, true, 'memory', $key ) . ' bytes',
						),
					);

					$args = array_merge( $cached_args[ $method ], $args );

					//* Reset args for next run.
					$cached_args[ $method ] = null;
				}
			}

			if ( $args ) {
				$output .= $method . '(';

				if ( isset( $hold_args[ $method ] ) ) {
					if ( is_array( $hold_args[ $method ] ) ) {
						foreach ( $hold_args[ $method ] as $var => $a ) {
								$output .= ' ' . gettype( $a ) . ' $' . $var . ',';
						}
					}
					$output = rtrim( $output, ', ' ) . ' ';
					$hold_args[ $method ] = null;
				}

				$output .= ')';
				$output .= $this->the_seo_framework_debug_hidden ? PHP_EOL : '<br>' . PHP_EOL;

				foreach ( $args as $num => $a ) {
					if ( is_array( $a ) ) {
						foreach ( $a as $k => $v ) {
							$output .= $this->the_seo_framework_debug_hidden ? '' : '<div style="padding-left:12px">';
								$output .= $this->the_seo_framework_debug_hidden ? "\t" . (string) $k . ': {{{' : "\t" . '<font color="fredwilliamson">' . (string) $k . '</font>: {{{';
								$output .= $this->the_seo_framework_debug_hidden ? PHP_EOL : '<br><div style="padding-left:12px">' . PHP_EOL;
									$output .= "\t  " . gettype( $v ) . ': {';
									$output .= $this->the_seo_framework_debug_hidden ? '' : '<div style="padding-left:12px">';
										$output .= "\t\t" . $this->get_debug_information( $v );
									$output .= $this->the_seo_framework_debug_hidden ? '' : '</div>';
									$output .= "\t  " . '}' . PHP_EOL;
								$output .= $this->the_seo_framework_debug_hidden ? '}}}' : '<br>}}}</div>';
							$output .= $this->the_seo_framework_debug_hidden ? '' : '</div>';
						}
					} else {
						$output .= $this->the_seo_framework_debug_hidden ? '' : '<div style="padding-left:12px">';
							$output .= $this->the_seo_framework_debug_hidden ? "\t" . (string) $num . ': {{{' : "\t" . '<font color="peterweller">' . (string) $num . '</font>: {{{';
							$output .= $this->the_seo_framework_debug_hidden ? PHP_EOL : '<br><div style="padding-left:12px">' . PHP_EOL;
								$output .= "\t  " . gettype( $a ) . ': {';
								$output .= $this->the_seo_framework_debug_hidden ? '' : '<div style="padding-left:12px">';
									$output .= "\t\t" . $this->get_debug_information( $a );
								$output .= $this->the_seo_framework_debug_hidden ? '' : '</div>' . PHP_EOL;
								$output .= "\t  " . '}' . PHP_EOL;
							$output .= $this->the_seo_framework_debug_hidden ? '}}}' : '<br>}}}</div>';
						$output .= $this->the_seo_framework_debug_hidden ? '' : '</div>';
					}
				}
			}
		}

		if ( $output ) {
			static $odd = false;
			if ( $odd ) {
				$bg = 'f1f1f1';
				$odd = false;
			} else {
				$bg = 'dadada';
				$odd = true;
			}

			//* Store debug output.
			$this->debug_output .= $this->the_seo_framework_debug_hidden ? '' : '<div style="background:#' . $bg . ';margin-bottom:6px;padding:0px 14px 14px;clear:both;float:left;width:100%;display:inline-block;">';
			$this->debug_output .= $output;
			$this->debug_output .= $this->the_seo_framework_debug_hidden ? '' : '</div>';
		}
	}

	/**
	 * Count the timings and memory usage.
	 * Memory usage fetching is unreliable, i.e. Opcode.
	 *
	 * @since 2.6.0
	 * @access private
	 *
	 * @param bool $echo Whether to echo the total plugin time.
	 * @param bool $from_last Whether to echo the differences from the last timing.
	 * @param string $what Whether to return the time or memory.
	 * @param string $key When used, it will detach the profiling separately.
	 *
	 * @staticvar bool $debug
	 *
	 * @return float The timer in seconds. Or memory in Bytes when $what is 'memory'.
	 */
	public function profile( $echo = false, $from_last = false, $what = 'time', $key = '' ) {

		static $timer_start = array();
		static $memory_start = array();
		static $plugin_time = array();
		static $plugin_memory = array();

		$timer_start[ $key ] = isset( $timer_start[ $key ] ) ? $timer_start[ $key ] : 0;
		$memory_start[ $key ] = isset( $memory_start[ $key ] ) ? $memory_start[ $key ] : 0;
		$plugin_time[ $key ] = isset( $plugin_time[ $key ] ) ? $plugin_time[ $key ] : 0;
		$plugin_memory[ $key ] = isset( $plugin_memory[ $key ] ) ? $plugin_memory[ $key ] : 0;

		//* Get now.
		$time_now = microtime( true );
		$memory_usage_now = memory_get_usage();

		//* Calculate difference.
		$difference_time = $time_now - $timer_start[ $key ];
		$difference_memory = $memory_usage_now - $memory_start[ $key ];

		//* Add difference to total.
		$plugin_time[ $key ] = $plugin_time[ $key ] + $difference_time;
		$plugin_memory[ $key ] = $plugin_memory[ $key ] + $difference_memory;

		//* Reset timer and memory
		$timer_start[ $key ] = $time_now;
		$memory_start[ $key ] = $memory_usage_now;

		if ( $from_last ) {
			if ( false === $echo ) {
				//* Return early if not allowed to echo.
				if ( 'time' === $what )
					return number_format( $difference_time, 5 );

				return $difference_memory;
			}

			//* Convert to string and echo if not returned yet.
			echo \esc_html( PHP_EOL . $difference_time . 's' . PHP_EOL );
			echo \esc_html( ( $difference_memory / 1024 ) . 'kiB' . PHP_EOL );
		} else {
			if ( false === $echo ) {
				//* Return early if not allowed to echo.
				if ( 'time' === $what )
					return number_format( $plugin_time[ $key ], 5 );

				return $plugin_memory[ $key ];
			}

			//* Convert to string and echo if not returned yet.
			echo \esc_html( PHP_EOL . $plugin_time[ $key ] . 's' . PHP_EOL );
			echo \esc_html( ( $plugin_memory[ $key ] / 1024 ) . 'kiB' . PHP_EOL );
		}
	}

	/**
	 * Times code until it's called again.
	 *
	 * @since 2.6.0
	 *
	 * @param bool $set Whether to reset the timer.
	 * @return float PHP Microtime for code execution.
	 */
	protected function timer( $reset = false ) {

		static $previous = null;

		if ( isset( $previous ) && false === $reset ) {
			$output = microtime( true ) - $previous;
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

		$instance = static::get_instance();
		//* Already escaped.
		echo $instance->get_debug_header_output();

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

		if ( $tsf->is_admin() && ! $tsf->is_term_edit() && ! $tsf->is_post_edit() && ! $tsf->is_seo_settings_page( true ) )
			return;

		if ( $tsf->is_seo_settings_page( true ) )
			\add_filter( 'the_seo_framework_current_object_id', array( $tsf, 'get_the_front_page_ID' ) );

		//* Start timer.
		$this->timer( true );

		//* Don't register this output.
		$this->add_debug_output = false;

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

		$timer = '<div style="display:inline-block;width:100%;padding:20px;border-bottom:1px solid #ccc;">Generated in: ' . number_format( $this->timer(), 5 ) . ' seconds</div>' ;

		$title = $tsf->is_admin() ? 'Expected SEO Output' : 'Determined SEO Output';
		$title = '<div style="display:inline-block;width:100%;padding:20px;margin:0 auto;border-bottom:1px solid #ccc;"><h2 style="color:#ddd;font-size:22px;padding:0;margin:0">' . $title . '</h2></div>';

		//* Escape it, replace EOL with breaks, and style everything between quotes (which are ending with space).
		$output = str_replace( PHP_EOL, '<br>' . PHP_EOL, esc_html( $output ) );
		$output = preg_replace( '/(&quot;.*?&quot;)(\s)/', '<font color="arnoldschwarzenegger">$1</font> ', $output );

		$output = '<div style="display:inline-block;width:100%;padding:20px;font-family:Consolas,Monaco,monospace;font-size:14px;">' . $output . '</div>';
		$output = '<div style="display:block;width:100%;background:#23282D;color:#ddd;border-bottom:1px solid #ccc">' . $title . $timer . $output . '</div>';

		$this->add_debug_output = true;

		return $output;
	}

	/**
	 * Outputs debug query.
	 *
	 * @since 2.8.0
	 * @access private
	 */
	public static function _output_debug_query() {

		$instance = static::$instance;
		//* Already escaped.
		echo $instance->get_debug_query_output();

	}

	/**
	 * Outputs debug query from cache.
	 *
	 * @since 2.8.0
	 * @access private
	 */
	public static function _output_debug_query_from_cache() {

		$instance = static::$instance;
		//* Already escaped.
		echo $instance->get_debug_query_output_from_cache();

	}

	/**
	 * Sets debug query cache.
	 *
	 * @since 2.8.0
	 * @access private
	 */
	public function set_debug_query_output_cache() {
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
	 * @global bool $multipage
	 * @global int $numpages
	 *
	 * @param string $cache_version 'Yes/no'
	 * @return string Wrapped Query State debug output.
	 */
	protected function get_debug_query_output( $cache_version = 'nope' ) {

		//* Start timer.
		$this->timer( true );

		//* Don't register duplicated output invoked in this method.
		$this->add_debug_output = false;

		global $multipage, $numpages;

		$tsf = \the_seo_framework();

		//* Only get true/false values.
		$page_id = $tsf->get_the_real_ID();
		$is_404 = $tsf->is_404();
		$is_admin = $tsf->is_admin();
		$is_attachment = $tsf->is_attachment();
		$is_archive = $tsf->is_archive();
		$is_term_edit = $tsf->is_term_edit();
		$is_post_edit = $tsf->is_post_edit();
		$is_wp_lists_edit = $tsf->is_wp_lists_edit();
		$is_wp_lists_edit = $tsf->is_wp_lists_edit();
		$is_author = $tsf->is_author();
		$is_blog_page = $tsf->is_blog_page();
		$is_category = $tsf->is_category();
		$is_date = $tsf->is_date();
		$is_day = $tsf->is_day();
		$is_feed = $tsf->is_feed();
		$is_real_front_page = $tsf->is_real_front_page();
		$is_front_page_by_id = $tsf->is_front_page_by_id( $tsf->get_the_real_ID() );
		$is_home = $tsf->is_home();
		$is_month = $tsf->is_month();
		$is_page = $tsf->is_page();
		$page = $tsf->page();
		$paged = $tsf->paged();
		$is_preview = $tsf->is_preview();
		$is_search = $tsf->is_search();
		$is_single = $tsf->is_single();
		$is_singular = $tsf->is_singular();
		$is_static_frontpage = $tsf->is_static_frontpage();
		$is_tag = $tsf->is_tag();
		$is_tax = $tsf->is_tax();
		$is_wc_shop = $tsf->is_wc_shop();
		$is_wc_product = $tsf->is_wc_product();
		$is_year = $tsf->is_year();
		$is_seo_settings_page = $tsf->is_seo_settings_page( true );

		//* Don't debug the class object.
		unset( $tsf );

		//* Get all above vars, split them in two (true and false) and sort them by key names.
		$vars = get_defined_vars();
		$current = array_filter( $vars );
		$not_current = array_diff_key( $vars, $current );
		ksort( $current );
		ksort( $not_current );

		$timer = '<div style="display:inline-block;width:100%;padding:20px;border-bottom:1px solid #666;">Generated in: ' . number_format( $this->timer(), 5 ) . ' seconds</div>';

		$output = '';
		foreach ( $current as $name => $value ) {
			$type = '(' . gettype( $value ) . ')';

			if ( is_bool( $value ) ) {
				$value = $value ? 'true' : 'false';
			} else {
				$value = \esc_attr( var_export( $value, true ) );
			}

			$value = $this->the_seo_framework_debug_hidden ? $type . ' ' . $value : '<font color="harrisonford">' . $type . ' ' . $value . '</font>';
			$out = \esc_html( $name ) . ' => ' . $value;
			$output .= $this->the_seo_framework_debug_hidden ? $out . PHP_EOL : '<span style="background:#dadada">' . $out . '</span>' . PHP_EOL;
		}

		foreach ( $not_current as $name => $value ) {
			$type = '(' . gettype( $value ) . ')';

			if ( is_bool( $value ) ) {
				$value = $value ? 'true' : 'false';
			} else {
				$value = \esc_attr( var_export( $value, true ) );
			}

			$value = $this->the_seo_framework_debug_hidden ? $type . ' ' . $value : '<font color="harrisonford">' . $type . ' ' . $value . '</font>';
			$out = \esc_html( $name ) . ' => ' . $value;
			$output .= $out . PHP_EOL;
		}

		if ( 'yes' === $cache_version || 'yup' === $cache_version ) {
			$title = 'WordPress Query at Meta Generation';
		} else {
			$title = \the_seo_framework()->is_admin() ? 'Current WordPress Screen + Expected WordPress Query' : 'Current WordPress Query';
		}
		$title = '<div style="display:inline-block;width:100%;padding:20px;margin:0 auto;border-bottom:1px solid #666;"><h2 style="color:#222;font-size:22px;padding:0;margin:0">' . $title . '</h2></div>';

		$output = $this->the_seo_framework_debug_hidden ? $output : str_replace( PHP_EOL, '<br>' . PHP_EOL, $output );

		$output = '<div style="display:inline-block;width:100%;padding:20px;font-family:Consolas,Monaco,monospace;font-size:14px;">' . $output . '</div>';
		$output = '<div style="display:block;width:100%;background:#fafafa;color:#333;border-bottom:1px solid #666">' . $title . $timer . $output . '</div>';

		$this->add_debug_output = true;

		return $output;
	}
}
