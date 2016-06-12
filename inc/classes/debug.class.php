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
	 * @var bool Whether to add to AutoDescription_Debug::debug_output.
	 */
	protected $add_debug_output = true;

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
					trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since version %2$s of The SEO Framework! Use %3$s instead.', 'autodescription' ), $function, $version, $replacement ) );
				else
					trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since version %2$s of The SEO Framework with no alternative available.' ), $function, $version ) );
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

				/* translators: 1: Function name, 2: Message, 3: Plugin Version notification */
				trigger_error( sprintf( '%1$s was called <strong>incorrectly</strong>. %2$s %3$s', $function, $message, $version ) );
			}

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
	 * Echo's error.
	 *
	 * @access private
	 * Please don't use this error handler.
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
	 * Echo found screens in the admin footer when debugging is enabled.
	 *
	 * @uses bool $this->the_seo_framework_debug
	 * @global array $current_screen
	 *
	 * @access private
	 * @since 2.5.2
	 */
	public function debug_screens() {
		global $current_screen;

		$this->debug_init( __CLASS__, __FUNCTION__, false, '', get_defined_vars() );

	}

	/**
	 * Echos debug output.
	 *
	 * @access private
	 * @since 2.6.0
	 */
	public function debug_output() {

		if ( $this->debug_output ) {
			if ( $this->the_seo_framework_debug_hidden ) {
				echo "\r\n<!--\r\n:: THE SEO FRAMEWORK DEBUG :: \r\n" . $this->debug_output . "\r\n:: / THE SEO FRAMEWORK DEBUG ::\r\n-->\r\n";
			} else {

				$id = $this->get_the_real_ID();
				$mdash = ' &mdash; ';
				$taxonomy = $this->is_archive() ? $this->fetch_the_term( $id ) : '';
				$tax_type = isset( $taxonomy->taxonomy ) ? $taxonomy->taxonomy : '';
				$post_type = ! $this->is_archive() && $this->is_front_page( $id ) ? 'Front Page' : $this->get_the_term_name( $taxonomy );
				$cache_key = $this->generate_cache_key( $this->get_the_real_ID(), $tax_type );

				if ( $this->is_admin() ) {
					?>
					<div style="clear:both;float:left;position:relative;width:calc( 100% - 200px );min-height:700px;padding:0;margin:20px 20px 40px 180px;overflow:hidden;border:1px solid #ccc;border-radius:3px;">
						<h3 style="font-size:14px;padding:0 12px;margin:0;line-height:39px;border-bottom: 2px solid #aaa;position:absolute;z-index:1;width:100%;right:0;left:0;top:0;background:#fff;border-radius:3px 3px 0 0;height:39px;">
							SEO Debug Information
							<?php
							if ( $this->is_post_edit() || $this->is_term_edit() ) :
								echo ' :: ';
								echo 'Type: ' . $post_type;
								echo $mdash . 'ID: ' . $id;
								echo $mdash . 'Cache key: ' . $cache_key;
							endif;
							?>
						</h3>
						<div style="position:absolute;bottom:0;right:0;left:0;top:41px;margin:0;padding:0;background:#fff;border-radius:3px;overflow-x:hidden;">
							<?php echo $this->debug_init_output(); ?>
							<?php echo $this->debug_output; ?>
						</div>
					</div>
					<?php
				} else {
					?>
					<style type="text/css">.wp-ui-notification{color:#fff;background-color:#d54e21}.code.highlight{font-family:Consolas,Monaco,monospace;font-size:14px;}</style>
					<div style="clear:both;float:left;position:relative;width:calc( 100% - 80px );min-height:700px;padding:0;margin:40px;overflow:hidden;border:1px solid #ccc;border-radius:3px;">
						<h3 style="font-size:14px;padding:0 12px;margin:0;line-height:39px;border-bottom: 2px solid #aaa;position:absolute;z-index:1;width:100%;right:0;left:0;top:0;background:#fff;border-radius:3px 3px 0 0;height:39px;">
							SEO Debug Information
							<?php
							echo ' :: ';
							echo 'Type: ' . $post_type;
							echo $mdash . 'ID: ' . $id;
							echo $mdash . 'Cache key: ' . $cache_key;
							?>
						</h3>
						<div style="position:absolute;bottom:0;right:0;left:0;top:41px;margin:0;padding:0;background:#fff;border-radius:3px;overflow-x:hidden;">
							<?php echo $this->debug_init_output(); ?>
							<?php echo $this->debug_output; ?>
						</div>
					</div>
					<?php
				}
			}
		}

	}

	/**
	 * Return debug values.
	 *
	 * @param mixed $values What to be output.
	 *
	 * @access private
	 * @since 2.6.0
	 */
	public function get_debug_information( $values = null ) {

		$output = '';

		if ( $this->the_seo_framework_debug ) {

			$output .= "\r\n";
			$output .=  $this->the_seo_framework_debug_hidden ? '' : '<span class="code highlight">';

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
					} else if ( is_string( $value ) || is_int( $value ) ) {
						$output .= $this->debug_key_wrapper( $key ) . ' => ';
						$output .= $this->debug_value_wrapper( $value );
						$output .= "\r\n";
					} else if ( is_bool( $value ) ) {
						$output .= $this->debug_key_wrapper( $key ) . ' => ';
						$output .= $this->debug_value_wrapper( $value ? 'true' : 'false' );
						$output .= "\r\n";
					} else if ( is_array( $value ) ) {
						$output .= $this->debug_key_wrapper( $key ) . ' => ';
						$output .= "Array[\r\n";
						$output .= $this->the_seo_framework_debug_hidden ? '' : '<p style="margin:0;padding-left:12px">';
						foreach ( $value as $k => $v ) {
							$output .= "\t\t\t";
							if ( '' === $v ) {
								$output .= $this->debug_key_wrapper( $k ) . ' => ';
								$output .= $this->debug_value_wrapper( "''" );
							} else if ( is_string( $v ) || is_int( $v ) ) {
								$output .= $this->debug_key_wrapper( $k ) . ' => ';
								$output .= $this->debug_value_wrapper( $v );
							} else if ( is_bool( $v ) ) {
								$output .= $this->debug_key_wrapper( $k ) . ' => ';
								$output .= $this->debug_value_wrapper( $v ? 'true' : 'false' );
							} else if ( is_array( $v ) ) {
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
						$output .= "]";
					} else {
						$output .= $this->debug_key_wrapper( $key ) . ' => ';
						$output .= $this->debug_value_wrapper( $value );
						$output .= "\r\n";
					}
					$output .= $this->the_seo_framework_debug_hidden ? '' : '<br>';
				}
				$output .= $this->the_seo_framework_debug_hidden ? '' : '</div>';
			} else if ( '' === $values ) {
				$output .= "\t\t";
				$output .= $this->debug_value_wrapper( "''" );
			} else if ( is_string( $values ) || is_int( $values ) ) {
				$output .= "\t\t";
				$output .= $this->debug_value_wrapper( $values );
			} else if ( is_bool( $values ) ) {
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
			return '<font color="chucknorris">' . esc_attr( (string) $key ) . '</font>';

		return esc_attr( (string) $key );
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
			return '<span class="wp-ui-notification">' . esc_attr( (string) trim( $value ) ) . '</span>';

		return esc_attr( (string) $value );
	}

	/**
	 * Debug init. Simplified way of debugging a function, only works in admin.
	 *
	 * @since 2.6.0
	 *
	 * @access private
	 *
	 * @param string $class The class name.
	 * @param string $method The function name.
	 * @param bool $store Whether to store the output in cache for next run to pick up on.
	 * @param double $debug_key Use $debug_key as variable, it's reserved.
	 * @param mixed function args.
	 * @return void early if debugging is disabled or when storing cache values.
	 */
	protected function debug_init( $class, $method, $store, $debug_key ) {

		if ( false === $this->the_seo_framework_debug || false === $this->add_debug_output )
			return;

		$output = '';

		if ( func_num_args() >= 5 ) {

			//* Cache the args for $store.
			static $cached_args = array();
			static $hold_args = array();

			$args = array_slice( func_get_args(), 4 );
			$key = $class . '_' . $method . '_' . $debug_key;

			if ( $store ) {
				$this->profile( false, false, 'time', $key ) . ' seconds';
				$this->profile( false, false, 'memory', $key ) . ' bytes';

				unset( $args[0]['debug_key'] );

				$cached_args[$class][$method] = $args;
				$hold_args[$class][$method] = $args;
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
					echo $this->the_seo_framework_debug_hidden ? $debug_key . ' action. ' : '<p>' . $debug_key . '</p>';
				}

				$output .= "\r\n";
				$output .= $this->the_seo_framework_debug_hidden ? $debug_key . ' output. ' : '<h3>' . $debug_key . '</h3>';

				if ( isset( $cached_args[$class][$method] ) ) {
					$args[] = array(
						'profile' => array(
							'time' => $this->profile( false, true, 'time', $key ) . ' seconds',
							'memory' => $this->profile( false, true, 'memory', $key ) . ' bytes'
						)
					);

					$args = array_merge( $cached_args[$class][$method], $args );

					//* Reset args for next run.
					$cached_args[$class][$method] = null;
				}
			}

			if ( $args ) {

				if ( $class ) {
					$output .= $class . '::' . $method . '( ';
				} else {
					$output .= $method . '( ';
				}

				if ( isset( $hold_args[$class][$method][0] ) ) {
					if ( is_array( $hold_args[$class][$method][0] ) ) {
						foreach ( $hold_args[$class][$method][0] as $var => $a ) {
								$output .= gettype( $a ) . ' $' . $var . ', ';
						}
					}
					$output = rtrim( $output, ', ' );
					$hold_args[$class][$method] = null;
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
									$output .= "\t  " .  ']' . "\r\n";
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

		$timer_start[$key] = isset( $timer_start[$key] ) ? $timer_start[$key] : 0;
		$memory_start[$key] = isset( $memory_start[$key] ) ? $memory_start[$key] : 0;
		$plugin_time[$key] = isset( $plugin_time[$key] ) ? $plugin_time[$key] : 0;
		$plugin_memory[$key] = isset( $plugin_memory[$key] ) ? $plugin_memory[$key] : 0;

		//* Get now.
		$time_now = microtime( true );
		$memory_usage_now = memory_get_usage();

		//* Calculate difference.
		$difference_time = $time_now - $timer_start[$key];
		$difference_memory = $memory_usage_now - $memory_start[$key];

		//* Add difference to total.
		$plugin_time[$key] = $plugin_time[$key] + $difference_time;
		$plugin_memory[$key] = $plugin_memory[$key] + $difference_memory;

		//* Reset timer and memory
		$timer_start[$key] = $time_now;
		$memory_start[$key] = $memory_usage_now;

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
					return number_format( $plugin_time[$key], 5 );

				return $plugin_memory[$key];
			}

			//* Convert to string and echo if not returned yet.
			echo (string) "\r\n" . $plugin_time[$key] . "s\r\n";
			echo (string) ( $plugin_memory[$key] / 1024 ) . "kiB\r\n";
		}

	}

	/**
	 * Times code until it's called again.
	 *
	 * @since 2.6.0
	 *
	 * @return float PHP Microtime for code execution.
	 */
	protected function timer() {

		static $previous = null;

		if ( isset( $previous ) ) {
			$output = $previous - microtime( true );
			$previous = null;
		} else {
			$output = $previous = microtime( true );
		}

		return $output;
	}

	/**
	 * Wraps header output in front-end code.
	 *
	 * @since 2.6.5
	 *
	 * @return string Wrapped HTML debug output.
	 */
	protected function debug_init_output() {

		if ( $this->is_admin() && ! $this->is_term_edit() && ! $this->is_post_edit() && ! $this->is_seo_settings_page() )
			return;

		if ( $this->is_seo_settings_page() )
			add_filter( 'the_seo_framework_current_object_id', array( $this, 'get_the_front_page_ID' ) );

		$init_start = microtime( true );

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

		$timer = '<div style="display:inline-block;width:100%;padding:20px;border-bottom:1px solid #ccc;">Generated in: ' . number_format( microtime( true ) - $init_start, 5 ) . ' seconds</div>' ;

		$title = $this->is_admin() ? 'Expected SEO Output' : 'Current SEO Output';
		$title = '<div style="display:inline-block;width:100%;padding:20px;margin:0 auto;border-bottom:1px solid #ccc;"><h2 style="color:#ddd;font-size:22px;padding:0;margin:0">' . $title . '</h2></div>';

		//* Escape it, replace EOL with breaks, and style everything between quotes (which are ending with space).
		$output = str_replace( PHP_EOL, '<br>', esc_html( $output ) );
		$output = preg_replace( "/(&quot;.*?&quot;)(\s)/", '<font color="arnoldschwarzenegger">$1</font> ', $output );

		$output = '<div style="display:inline-block;width:100%;padding:20px;font-family:Consolas,Monaco,monospace;font-size:14px;">' . $output . '</div>';
		$output = '<div style="display:block;width:100%;background:#23282D;color:#ddd;border-bottom:1px solid #ccc">' . $title . $timer . $output . '</div>';

		return $output;
	}

}
