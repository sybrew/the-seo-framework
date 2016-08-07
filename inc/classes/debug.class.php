<?php
/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

defined( 'ABSPATH' ) or die;

/**
 * Class AutoDescription_Debug
 *
 * Holds plugin debug functions.
 *
 * @since 2.6.0
 */
class AutoDescription_Debug extends AutoDescription_Core {

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
	 * @var bool Whether to continue adding to AutoDescription_Debug::debug_output
	 * within AutoDescription_Debug::debug_init().
	 */
	protected $add_debug_output = true;

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	private function __wakeup() { }

	/**
	 * Handle unapproachable invoked methods.
	 */
	public function __call( $name, $arguments ) {
		parent::__call( $name, $arguments );
	}

	/**
	 * Constructor, load parent constructor and add actions.
	 */
	public function __construct() {
		parent::__construct();

		if ( $this->the_seo_framework_debug ) {
			add_action( 'admin_footer', array( $this, 'debug_screens' ) );
			add_action( 'admin_footer', array( $this, 'debug_output' ) );
			add_action( 'wp_footer', array( $this, 'debug_output' ) );
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
	 * @access private
	 *
	 * @param string $function		The function that was called.
	 * @param string $version		The version of WordPress that deprecated the function.
	 * @param string $replacement	Optional. The function that should have been called. Default null.
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
		do_action( 'deprecated_function_run', $function, $replacement, $version );

		/**
		 * Filter whether to trigger an error for deprecated functions.
		 *
		 * @since WP Core 2.5.0
		 *
		 * @param bool $trigger Whether to trigger the error for deprecated functions. Default true.
		 */
		if ( WP_DEBUG && apply_filters( 'deprecated_function_trigger_error', true ) ) {

			set_error_handler( array( $this, 'error_handler_deprecated' ) );

			if ( function_exists( '__' ) ) {
				if ( isset( $replacement ) )
					/* translators: 1: Function name, 2: Plugin Version notification, 3: Replacement function */
					trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since version %2$s of The SEO Framework! Use %3$s instead.', 'autodescription' ), $function, $version, $replacement ) );
				else
					/* translators: 1: Function name, 2: Plugin Version notification */
					trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since version %2$s of The SEO Framework with no alternative available.', 'autodescription' ), $function, $version ) );
			} else {
				if ( isset( $replacement ) )
					trigger_error( sprintf( '%1$s is <strong>deprecated</strong> since version %2$s of The SEO Framework! Use %3$s instead.', $function, $version, $replacement ) );
				else
					trigger_error( sprintf( '%1$s is <strong>deprecated</strong> since version %2$s of The SEO Framework with no alternative available.', $function, $version ) );
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
	 * @access private
	 *
	 * @param string $function	The function that was called.
	 * @param string $message	A message explaining what has been done incorrectly.
	 * @param string $version	The version of WordPress where the message was added.
	 */
	public function _doing_it_wrong( $function, $message, $version ) {
		/**
		* Fires when the given function is being used incorrectly.
		*
		* @since WP Core 3.1.0
		*
		* @param string $function The function that was called.
		* @param string $message  A message explaining what has been done incorrectly.
		* @param string $version  The version of WordPress where the message was added.
		*/
		do_action( 'doing_it_wrong_run', $function, $message, $version );

		/**
		* Filter whether to trigger an error for _doing_it_wrong() calls.
		*
		* @since 3.1.0
		*
		* @param bool $trigger Whether to trigger the error for _doing_it_wrong() calls. Default true.
		*/
		if ( WP_DEBUG && apply_filters( 'doing_it_wrong_trigger_error', true ) ) {

			set_error_handler( array( $this, 'error_handler_doing_it_wrong' ) );

			if ( function_exists( '__' ) ) {
				$version = is_null( $version ) ? '' : sprintf( __( '(This message was added in version %s of The SEO Framework.)' ), $version );
				/* translators: %s: Codex URL */
				$message .= ' ' . sprintf( __( 'Please see <a href="%s">Debugging in WordPress</a> for more information.', 'autodescription' ),
					__( 'https://codex.wordpress.org/Debugging_in_WordPress', 'autodescription' )
				);
				/* translators: 1: Function name, 2: Message, 3: Plugin Version notification */
				trigger_error( sprintf( __( '%1$s was called <strong>incorrectly</strong>. %2$s %3$s', 'autodescription' ), $function, $message, $version ) );
			} else {
				$version = is_null( $version ) ? '' : sprintf( '(This message was added in version %s of The SEO Framework.)', $version );
				$message .= ' ' . sprintf( 'Please see <a href="%s">Debugging in WordPress</a> for more information.',
					'https://codex.wordpress.org/Debugging_in_WordPress'
				);

				trigger_error( sprintf( '%1$s was called <strong>incorrectly</strong>. %2$s %3$s', $function, $message, $version ) );
			}

			restore_error_handler();
		}
	}

	/**
	 * Mark a property or method inaccessible when it has been used.

	 * The current behavior is to trigger a user error if WP_DEBUG is true.
	 *
	 * @since 2.7.0
	 * @access private
	 *
	 * @param string $p_or_m	The Property or Method.
	 * @param string $message	A message explaining what has been done incorrectly.
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
		do_action( 'the_seo_framework_inaccessible_p_or_m_run', $p_or_m, $message );

		/**
		* Filter whether to trigger an error for _doing_it_wrong() calls.
		*
		* @since 3.1.0
		*
		* @param bool $trigger Whether to trigger the error for _doing_it_wrong() calls. Default true.
		*/
		if ( WP_DEBUG && apply_filters( 'the_seo_framework_inaccessible_p_or_m_trigger_error', true ) ) {

			set_error_handler( array( $this, 'error_handler_inaccessible_call' ) );

			if ( function_exists( '__' ) )
				/* translators: 1: Method or Property name, 2: Message */
				trigger_error( sprintf( __( '%1$s is not <strong>accessible</strong>. %2$s', 'autodescription' ), $p_or_m, $message ) );
			else
				trigger_error( sprintf( '%1$s is not <strong>accessible</strong>. %2$s', $p_or_m, $message ) );

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
		if ( $code >= 1024 && isset( $message ) ) {

			$backtrace = debug_backtrace();
			/**
			 * 0 = This function. 1 = Debug function. 2 = Error trigger. 3 = Deprecated call.
			 */
			$error = $backtrace[3];

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
		if ( $code >= 1024 && isset( $message ) ) {

			$backtrace = debug_backtrace();
			/**
			 * 0 = This function. 1 = Debug function. 2 = Error trigger.
			 */
			$error = $backtrace[2];

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
	protected function error_handler_inaccessible_call( $code, $message ) {

		if ( $code >= 1024 && isset( $message ) ) {

			$backtrace = debug_backtrace();
			/**
			 * 0 = This function. 1 = Debug function. 2 = debug function. 3-29 = 26 classes loop, 30 = user call.
			 */
			$error = $backtrace[30];

			$this->error_handler( $error, $message );
		}

	}

	/**
	 * Echos error.
	 *
	 * @since 2.6.0
	 *
	 * @param array $error The Error location and file.
	 * @param string $message The error message.
	 */
	protected function error_handler( $error, $message ) {

		$file = isset( $error['file'] ) ? $error['file'] : '';
		$line = isset( $error['line'] ) ? $error['line'] : '';

		if ( isset( $message ) ) {
			echo "\r\n" . '<strong>Notice:</strong> ' . $message;
			echo $file ? ' In ' . $file : '';
			echo $line ? ' on line ' . $line : '';
			echo ".<br>\r\n";
		}
	}

	/**
	 * Echos found screens in the admin footer when debugging is enabled.
	 *
	 * @since 2.5.2
	 * @uses bool $this->the_seo_framework_debug
	 * @access private
	 * @global object $current_screen
	 */
	public function debug_screens() {
		global $current_screen;

		$this->debug_init( __METHOD__, false, '', get_defined_vars() );

	}

	/**
	 * Echos debug output.
	 *
	 * @since 2.6.0
	 * @access private
	 */
	public function debug_output() {
		$this->get_view( 'debug/output' );
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

			$output .= "\r\n";
			$output .= $this->the_seo_framework_debug_hidden ? '' : '<span class="code highlight">';

			if ( is_null( $values ) ) {
				$output .= $this->debug_value_wrapper( "Debug message: Value isn't set." ) . "\r\n";
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
						$output .= "\r\n";
					} elseif ( is_string( $value ) || is_int( $value ) ) {
						$output .= $this->debug_key_wrapper( $key ) . ' => ';
						$output .= $this->debug_value_wrapper( $value );
						$output .= "\r\n";
					} elseif ( is_bool( $value ) ) {
						$output .= $this->debug_key_wrapper( $key ) . ' => ';
						$output .= $this->debug_value_wrapper( $value ? 'true' : 'false' );
						$output .= "\r\n";
					} elseif ( is_array( $value ) ) {
						$output .= $this->debug_key_wrapper( $key ) . ' => ';
						$output .= "Array[\r\n";
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
							$output .= "\r\n";
							$output .= $this->the_seo_framework_debug_hidden ? '' : '<br>';
						}
						$output .= $this->the_seo_framework_debug_hidden ? '' : '</p>';
						$output .= ']';
					} else {
						$output .= $this->debug_key_wrapper( $key ) . ' => ';
						$output .= $this->debug_value_wrapper( $value );
						$output .= "\r\n";
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
			$output .= "\r\n";
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
			return '<font color="chucknorris">' . esc_attr( $key ) . '</font>';

		return esc_attr( $key );
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
			return '<span class="wp-ui-notification">' . esc_attr( trim( $value ) ) . '</span>';

		return esc_attr( $value );
	}

	/**
	 * Debug init. Simplified way of debugging a function, only works in admin.
	 *
	 * @since 2.6.0
	 *
	 * @access private
	 *
	 * @param string $method The function name.
	 * @param bool $store Whether to store the output in cache for next run to pick up on.
	 * @param double $debug_key Use $debug_key as variable, it's reserved.
	 * @param mixed function args.
	 * @return void early if debugging is disabled or when storing cache values.
	 */
	protected function debug_init( $method, $store, $debug_key ) {

		if ( false === $this->the_seo_framework_debug || false === $this->add_debug_output )
			return;

		$output = '';

		if ( func_num_args() >= 4 ) {

			//* Cache the args for $store.
			static $cached_args = array();
			static $hold_args = array();

			$args = array_slice( func_get_args(), 3 );
			$key = $method . '_' . $debug_key;

			if ( $store ) {
				$this->profile( false, false, 'time', $key ) . ' seconds';
				$this->profile( false, false, 'memory', $key ) . ' bytes';

				unset( $args[0]['debug_key'] );

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

				if ( $this->is_admin() && 'admin_footer' !== current_action() ) {
					echo "\r\n";
					echo $this->the_seo_framework_debug_hidden ? esc_html( $debug_key ) . ' action. ' : '<p>' . esc_html( $debug_key ) . '</p>';
				}

				$output .= "\r\n";
				$output .= $this->the_seo_framework_debug_hidden ? esc_html( $debug_key ) . ' output. ' : '<h3>' . esc_html( $debug_key ) . '</h3>';

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

				$output .= $method . '( ';

				if ( isset( $hold_args[ $method ][0] ) ) {
					if ( is_array( $hold_args[ $method ][0] ) ) {
						foreach ( $hold_args[ $method ][0] as $var => $a ) {
								$output .= gettype( $a ) . ' $' . $var . ', ';
						}
					}
					$output = rtrim( $output, ', ' );
					$hold_args[ $method ] = null;
				}

				$output .= ' )';
				$output .= $this->the_seo_framework_debug_hidden ? "\r\n" : "<br>\r\n";

				foreach ( $args as $num => $a ) {
					if ( is_array( $a ) ) {
						foreach ( $a as $k => $v ) {
							$output .= $this->the_seo_framework_debug_hidden ? '' : '<div style="padding-left:12px">';
								$output .= "\t" . (string) $k . ': ';
								$output .= $this->the_seo_framework_debug_hidden ? "\r\n" : '<br><div style="padding-left:12px">' . "\r\n";
									$output .= "\t  " . gettype( $v ) . ': [';
									$output .= $this->the_seo_framework_debug_hidden ? '' : '<div style="padding-left:12px">';
										$output .= "\t\t" . $this->get_debug_information( $v );
									$output .= $this->the_seo_framework_debug_hidden ? '' : '</div>';
									$output .= "\t  " . ']' . "\r\n";
								$output .= $this->the_seo_framework_debug_hidden ? '' : '</div>';
							$output .= $this->the_seo_framework_debug_hidden ? '' : '</div>';
						}
					} else {
						$output .= $this->the_seo_framework_debug_hidden ? '' : '<div style="padding-left:12px">';
							$output .= "\t" . (string) $num . ': ';
							$output .= $this->the_seo_framework_debug_hidden ? "\r\n" : "<br>\r\n";
							$output .= "\t  " . gettype( $a ) . ': [';
							$output .= $this->the_seo_framework_debug_hidden ? '' : '<div style="padding-left:12px">';
								$output .= "\t\t" . $this->get_debug_information( $a );
							$output .= $this->the_seo_framework_debug_hidden ? '' : "</div><br>\r\n";
							$output .= "\t  " . ']' . "\r\n";
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
			echo (string) "\r\n" . $difference_time . "s\r\n";
			echo (string) ( $difference_memory / 1024 ) . "kiB\r\n";
		} else {
			if ( false === $echo ) {
				//* Return early if not allowed to echo.
				if ( 'time' === $what )
					return number_format( $plugin_time[ $key ], 5 );

				return $plugin_memory[$key];
			}

			//* Convert to string and echo if not returned yet.
			echo (string) "\r\n" . $plugin_time[ $key ] . "s\r\n";
			echo (string) ( $plugin_memory[ $key ] / 1024 ) . "kiB\r\n";
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
	 * Wraps header output in front-end code.
	 * This won't consider hiding the output.
	 *
	 * @since 2.6.5
	 *
	 * @return string Wrapped SEO meta tags output.
	 */
	protected function debug_header_output() {

		if ( $this->is_admin() && ! $this->is_term_edit() && ! $this->is_post_edit() && ! $this->is_seo_settings_page( true ) )
			return;

		if ( $this->is_seo_settings_page( true ) )
			add_filter( 'the_seo_framework_current_object_id', array( $this, 'get_the_front_page_ID' ) );

		//* Start timer.
		$this->timer( true );

		//* Don't register this output.
		$this->add_debug_output = false;

		$output	= $this->the_description()
				. $this->og_image()
				. $this->og_locale()
				. $this->og_type()
				. $this->og_title()
				. $this->og_description()
				. $this->og_url()
				. $this->og_sitename()
				. $this->facebook_publisher()
				. $this->facebook_author()
				. $this->facebook_app_id()
				. $this->article_published_time()
				. $this->article_modified_time()
				. $this->twitter_card()
				. $this->twitter_site()
				. $this->twitter_creator()
				. $this->twitter_title()
				. $this->twitter_description()
				. $this->twitter_image()
				. $this->shortlink()
				. $this->canonical()
				. $this->paged_urls()
				. $this->ld_json()
				. $this->google_site_output()
				. $this->bing_site_output()
				. $this->yandex_site_output()
				. $this->pint_site_output()
				;

		$timer = '<div style="display:inline-block;width:100%;padding:20px;border-bottom:1px solid #ccc;">Generated in: ' . number_format( $this->timer(), 5 ) . ' seconds</div>' ;

		$title = $this->is_admin() ? 'Expected SEO Output' : 'Current SEO Output';
		$title = '<div style="display:inline-block;width:100%;padding:20px;margin:0 auto;border-bottom:1px solid #ccc;"><h2 style="color:#ddd;font-size:22px;padding:0;margin:0">' . $title . '</h2></div>';

		//* Escape it, replace EOL with breaks, and style everything between quotes (which are ending with space).
		$output = str_replace( PHP_EOL, '<br>' . PHP_EOL, esc_html( $output ) );
		$output = preg_replace( '/(&quot;.*?&quot;)(\s)/', '<font color="arnoldschwarzenegger">$1</font> ', $output );

		$output = '<div style="display:inline-block;width:100%;padding:20px;font-family:Consolas,Monaco,monospace;font-size:14px;">' . $output . '</div>';
		$output = '<div style="display:block;width:100%;background:#23282D;color:#ddd;border-bottom:1px solid #ccc">' . $title . $timer . $output . '</div>';

		return $output;
	}

	/**
	 * Wraps query status booleans in human-readable code.
	 *
	 * @since 2.6.6
	 *
	 * @return string Wrapped Query State debug output.
	 */
	protected function debug_query_output() {

		//* Start timer.
		$this->timer( true );

		//* Don't register this output.
		$this->add_debug_output = false;

		global $multipage, $numpages;

		//* Only get true/false values.
		$is_404 = $this->is_404();
		$is_admin = $this->is_admin();
		$is_attachment = $this->is_attachment();
		$is_archive = $this->is_archive();
		$is_term_edit = $this->is_term_edit();
		$is_post_edit = $this->is_post_edit();
		$is_wp_lists_edit = $this->is_wp_lists_edit();
		$is_wp_lists_edit = $this->is_wp_lists_edit();
		$is_author = $this->is_author();
		$is_blog_page = $this->is_blog_page();
		$is_category = $this->is_category();
		$is_date = $this->is_date();
		$is_day = $this->is_day();
		$is_feed = $this->is_feed();
		$is_front_page = $this->is_front_page();
		$is_home = $this->is_home();
		$is_month = $this->is_month();
		$is_page = $this->is_page();
		$page = $this->page();
		$paged = $this->paged();
		$is_preview = $this->is_preview();
		$is_search = $this->is_search();
		$is_single = $this->is_single();
		$is_singular = $this->is_singular();
		$is_static_frontpage = $this->is_static_frontpage();
		$is_tag = $this->is_tag();
		$is_tax = $this->is_tax();
		$is_ultimate_member_user_page = $this->is_ultimate_member_user_page();
		$is_wc_shop = $this->is_wc_shop();
		$is_wc_product = $this->is_wc_product();
		$is_year = $this->is_year();
		$is_seo_settings_page = $this->is_seo_settings_page( true );

		//* Get all above vars, split them in two (true and false) and sort them by key names.
		$vars = get_defined_vars();
		$current = array_filter( $vars );
		$not_current = array_diff_key( $vars, $current );
		ksort( $current );
		ksort( $not_current );

		$timer = '<div style="display:inline-block;width:100%;padding:20px;border-bottom:1px solid #666;">Generated in: ' . number_format( $this->timer(), 5 ) . ' seconds</div>' ;

		$output = '';
		foreach ( $current as $name => $value ) {
			$type = '(' . gettype( $value ) . ')';

			if ( is_bool( $value ) ) {
				$value = $value ? 'true' : 'false';
			} else {
				$value = esc_attr( var_export( $value, true ) );
			}

			$value = $this->the_seo_framework_debug_hidden ? $type . ' ' . $value : '<font color="harrisonford">' . $type . ' ' . $value . '</font>';
			$out = $name . ' => ' . $value;
			$output .= $this->the_seo_framework_debug_hidden ? $out . PHP_EOL : '<span style="background:#dadada">' . $out . '</span>' . PHP_EOL;
		}

		foreach ( $not_current as $name => $value ) {
			$type = '(' . gettype( $value ) . ')';

			if ( is_bool( $value ) ) {
				$value = $value ? 'true' : 'false';
			} else {
				$value = esc_attr( var_export( $value, true ) );
			}

			$value = $this->the_seo_framework_debug_hidden ? $type . ' ' . $value : '<font color="harrisonford">' . $type . ' ' . $value . '</font>';
			$out = $name . ' => ' . $value;
			$output .= $out . PHP_EOL;
		}

		$title = $this->is_admin() ? 'Current WordPress Screen + Expected WordPress Query' : 'Current WordPress Query';
		$title = '<div style="display:inline-block;width:100%;padding:20px;margin:0 auto;border-bottom:1px solid #666;"><h2 style="color:#222;font-size:22px;padding:0;margin:0">' . $title . '</h2></div>';

		$output = $this->the_seo_framework_debug_hidden ? $output : str_replace( PHP_EOL, '<br>' . PHP_EOL, $output );

		$output = '<div style="display:inline-block;width:100%;padding:20px;font-family:Consolas,Monaco,monospace;font-size:14px;">' . $output . '</div>';
		$output = '<div style="display:block;width:100%;background:#fafafa;color:#333;border-bottom:1px solid #666">' . $title . $timer . $output . '</div>';

		return $output;
	}
}
