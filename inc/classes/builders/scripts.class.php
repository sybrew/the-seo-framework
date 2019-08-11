<?php
/**
 * @package The_SEO_Framework\Classes\Builders\Scripts
 * @subpackage The_SEO_Framework\Scripts
 */

namespace The_SEO_Framework\Builders;

/**
 * The SEO Framework plugin
 * Copyright (C) 2018 - 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * Sets up class loader as file is loaded.
 * This is done asynchronously, because static calls are handled prior and after.
 *
 * @see EOF. Because of the autoloader and (future) trait calling, we can't do it before the class is read.
 * @link https://bugs.php.net/bug.php?id=75771
 */
$_load_scripts_class = function() {
	new Scripts();
};

/**
 * Registers and outputs admin GUI scripts. Auto-invokes everything the moment
 * this file is required.
 * Relies on \WP_Dependencies to prevent duplicate loading, and autoloading.
 *
 * This handles admin-ONLY scripts for now.
 *
 * @since 3.1.0
 * @see \WP_Styles
 * @see \WP_Scripts
 * @see \WP_Dependencies
 * @see \The_SEO_Framework\Bridges\Scripts
 * @access private
 * @final Can't be extended.
 */
final class Scripts {
	use \The_SEO_Framework\Traits\Enclose_Stray_Private;

	/**
	 * Codes to maintain the internal state of the scripts. This state might not reflect
	 * the actual load state. See \WP_Dependencies instead.
	 *
	 * @since 3.1.0
	 * @internal
	 * @var int <bit 01>  REGISTERED
	 * @var int <bit 10>  LOADED     (rather, enqueued)
	 */
	const REGISTERED = 0b01;
	const LOADED     = 0b10;

	/**
	 * @since 3.1.0
	 * @var array $scripts   The registered scripts.
	 */
	private static $scripts = [];

	/**
	 * @since 3.1.0
	 * @var array $templates The registered templates.
	 */
	private static $templates = [];

	/**
	 * @since 3.1.0
	 * @var array $queue     The queued scripts state.
	 */
	private static $queue = [];

	/**
	 * @since 3.1.0
	 * @var \The_SEO_Framework\Builders\Scripts $instance The instance.
	 */
	private static $instance;

	/**
	 * @since 3.1.0
	 * @since 3.2.2 Is now a private variable.
	 * @see static::verify()
	 * @var string|null $include_secret The inclusion secret generated on tab load.
	 */
	private static $include_secret;

	/**
	 * Prepares the class and loads constructor.
	 *
	 * Use this if the actions need to be registered early, but nothing else of
	 * this class is needed yet.
	 *
	 * @since 3.1.0
	 */
	public static function prepare() {}

	/**
	 * The constructor. Can't be instantiated externally from this file.
	 *
	 * This probably autoloads at action "admin_enqueue_scripts", priority "0".
	 *
	 * @since 3.1.0
	 * @access private
	 * @staticvar int $count Enforces singleton.
	 * @internal
	 */
	public function __construct() {

		static $count = 0;
		0 === $count++ or \wp_die( 'Don\'t instance <code>' . __CLASS__ . '</code>.' );

		static::$instance = &$this;

		\add_filter( 'admin_body_class', [ $this, '_add_body_class' ] );
		\add_action( 'in_admin_header', [ $this, '_print_tsfjs_script' ] );

		\add_action( 'admin_enqueue_scripts', [ $this, '_prepare_admin_scripts' ], 1 );
		\add_action( 'admin_footer', [ $this, '_output_templates' ], 999 );
	}

	/**
	 * Adds admin-body classes.
	 *
	 * @since 4.0.0
	 * @access private
	 * @internal
	 *
	 * @param string $classes Space-separated list of CSS classes.
	 * @return string
	 */
	public function _add_body_class( $classes ) {
		// Add spaces at both sides, because who knows what others do.
		return ' tsf-no-js ' . $classes;
	}

	/**
	 * Prints the TSF no-js transform script, using ES2015 (ECMA-262).
	 *
	 * @since 4.0.0
	 * @access private
	 * @internal
	 */
	public function _print_tsfjs_script() {
		echo "<script>(()=>{document.body.classList.replace('tsf-no-js','tsf-js');const a=0;})()</script>";
	}

	/**
	 * Prepares scripts for output on post edit screens.
	 *
	 * @since 3.1.0
	 * @access private
	 * @internal
	 */
	public function _prepare_admin_scripts() {
		$this->forward_known_scripts();
		$this->autoload_known_scripts();
	}

	/**
	 * Returns the script status of $id for $type.
	 *
	 * @since 3.1.0
	 * @see static::REGISTERED
	 * @see static::LOADED
	 *
	 * @param string $id   The script ID.
	 * @param string $type The script type, albeit 'js' or 'css'.
	 * @return int <bit>
	 */
	public static function get_status_of( $id, $type ) {
		return isset( static::$queue[ $type ][ $id ] ) ? static::$queue[ $type ][ $id ] : 0b0;
	}

	/**
	 * Enqueues all known registered scripts, styles, and templates.
	 *
	 * @since 3.1.0
	 */
	public static function enqueue() {
		static::$instance->_prepare_admin_scripts();
		static::$instance->_output_templates();
	}

	/**
	 * Registers script to be enqueued. Can register multiple scripts at once.
	 *
	 * A better name would've been "collect"...
	 *
	 * @since 3.1.0
	 * @uses static::$scripts
	 * @see $this->forward_known_scripts()
	 * @see $this->autoload_known_scripts()
	 *
	 * @NOTE If the script is associative, it'll be registered as-is.
	 *       If the script is sequential, it'll be iterated over, and then registered.
	 *
	 * @param array $script The script : {
	 *   'id'   => string The script ID,
	 *   'type' => string 'css|js',
	 *   'autoload' => boolean If true, the script will be loaded directly.
	 *                         If false, it'll only be registered for dependencies.
	 *   'name' => string The unique script name, which is also the file name,
	 *   'deps' => array  Dependencies,
	 *   'ver'  => string Script version,
	 *   'l10n' => array If type is 'js' : {
	 *      'name' => string The JavaScript variable,
	 *      'data' => mixed  The l10n properties,
	 *   }
	 *   'tmpl' => array If type is 'js', either multidimensional or single : {
	 *      'file' => string $file. The full file location,
	 *      'args' => array $args. Optional,
	 *    }
	 *   'inline' => array If type is 'css' : {
	 *      'selector' => array : { iterable => 'style' }
	 *    }
	 * }
	 */
	public static function register( array $script ) {
		if ( array_values( $script ) === $script ) {
			foreach ( $script as $s ) static::register( $s );
			return;
		}

		static::$scripts[] = $script;
	}

	/**
	 * Registers and enqueues known scripts.
	 *
	 * @since 3.2.2
	 *
	 * @param string $id   The script ID.
	 * @param string $type The script type.
	 */
	public static function forward_known_script( $id, $type ) {
		if ( ! ( static::get_status_of( $id, $type ) & static::REGISTERED ) ) {
			foreach ( static::$scripts as $s ) {
				if ( $s['id'] === $id ) {
					if ( $s['type'] === $type ) {
						static::forward_script( $s );
					}
				}
			}
		}
	}

	/**
	 * Registers and enqueues known scripts.
	 *
	 * @since 3.2.2
	 * @uses static::forward_known_script();
	 *
	 * @param string $id   The script ID.
	 * @param string $type The script type.
	 */
	public static function enqueue_known_script( $id, $type ) {

		static::forward_known_script( $id, $type );

		if ( static::get_status_of( $id, $type ) & static::REGISTERED ) {
			if ( ! ( static::get_status_of( $id, $type ) & static::LOADED ) ) {
				static::load_script( $id, $type );
			}
		}
	}

	/**
	 * Verifies template view inclusion secret.
	 *
	 * @since 3.1.0
	 * @see static::output_view()
	 * @uses static::$include_secret
	 *
	 * @example template file header:
	 * `defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and The_SEO_Framework\Builders\Scripts::verify( $_secret ) or die;`
	 *
	 * @param string $secret The passed secret.
	 * @return bool True on success, false on failure.
	 */
	public static function verify( $secret ) {
		return $secret && static::$include_secret === $secret;
	}

	/**
	 * Forwards known scripts to WordPress' script handler. Also prepares l10n and templates.
	 *
	 * @since 3.2.2
	 * @uses static::$scripts
	 * @uses static::egister_script()
	 */
	private function forward_known_scripts() {
		//= Register them first to accomodate for dependencies.
		foreach ( static::$scripts as $s ) {
			if ( static::get_status_of( $s['id'], $s['type'] ) & static::REGISTERED ) continue;
			static::forward_script( $s );
		}
	}

	/**
	 * Enqueues known scripts, and invokes the l10n and templates.
	 *
	 * @since 3.2.2
	 * @uses static::$scripts
	 * @uses static::load_script()
	 */
	private function autoload_known_scripts() {
		foreach ( static::$scripts as $s ) {
			if ( $s['autoload'] ) {
				if ( static::get_status_of( $s['id'], $s['type'] ) & static::LOADED ) continue;
				static::load_script( $s['id'], $s['type'] );
			}
		}
	}

	/**
	 * Enqueues scripts in WordPress' script handler. Also prepares l10n and templates.
	 *
	 * @since 3.2.2
	 * @see $this->forward_known_scripts();
	 * @see static::enqueue_known_script();
	 * @augments static::$queue
	 * @uses $this->generate_file_url()
	 * @uses $this->create_inline_css()
	 * @uses $this->register_template()
	 *
	 * @param array $s The script.
	 */
	private static function forward_script( array $s ) {

		$instance   = static::$instance;
		$registered = false;

		switch ( $s['type'] ) {
			case 'css':
				\wp_register_style( $s['id'], $instance->generate_file_url( $s, 'css' ), $s['deps'], $s['ver'], 'all' );
				isset( $s['inline'] )
					and \wp_add_inline_style( $s['id'], $instance->create_inline_css( $s['inline'] ) );
				$registered = true;
				break;
			case 'js':
				\wp_register_script( $s['id'], $instance->generate_file_url( $s, 'js' ), $s['deps'], $s['ver'], true );
				isset( $s['l10n'] )
					and \wp_localize_script( $s['id'], $s['l10n']['name'], $s['l10n']['data'] );
				isset( $s['tmpl'] )
					and $instance->register_template( $s['id'], $s['tmpl'] );
				isset( $s['inline'] )
					and \wp_add_inline_script( $s['id'], $instance->create_inline_js( $s['inline'] ) );
				$registered = true;
				break;
		}
		if ( $registered ) {
			isset( static::$queue[ $s['type'] ][ $s['id'] ] )
				and static::$queue[ $s['type'] ][ $s['id'] ] |= static::REGISTERED
				 or static::$queue[ $s['type'] ][ $s['id'] ]  = static::REGISTERED; // phpcs:ignore, WordPress.WhiteSpace
		}
	}

	/**
	 * Loads known registered script.
	 *
	 * @since 3.2.2
	 *
	 * @param string $id   The script ID.
	 * @param string $type The script type.
	 */
	private static function load_script( $id, $type ) {

		if ( ! ( static::get_status_of( $id, $type ) & static::REGISTERED ) ) return;

		$loaded = false;

		switch ( $type ) {
			case 'css':
				\wp_enqueue_style( $id );
				$loaded = true;
				break;
			case 'js':
				\wp_enqueue_script( $id );
				$loaded = true;
				break;
		}
		if ( $loaded ) {
			isset( static::$queue[ $type ][ $id ] )
				and static::$queue[ $type ][ $id ] |= static::LOADED
				 or static::$queue[ $type ][ $id ]  = static::LOADED; // phpcs:ignore, WordPress.WhiteSpace
		}
	}

	/**
	 * Generates file URL.
	 *
	 * @since 3.1.0
	 * @staticvar string $min
	 * @staticvar string $rtl
	 *
	 * @param array $script The script arguments.
	 * @param array $type Either 'js' or 'css'.
	 * @return string The file URL.
	 */
	private function generate_file_url( array $script, $type = 'js' ) {

		static $min, $rtl;

		if ( ! isset( $min, $rtl ) ) {
			$min = \the_seo_framework()->script_debug ? '' : '.min';
			$rtl = \is_rtl() ? '.rtl' : '';
		}

		$_rtl = ! empty( $script['hasrtl'] ) ? $rtl : '';
		return "{$script['base']}{$script['name']}{$_rtl}{$min}.$type";
	}

	/**
	 * Registers inline CSS.
	 * Implements admin color support.
	 *
	 * Use any of these values to get the corresponding admin color:
	 * - {{$bg}}
	 * - {{$bg_accent}}
	 * - {{$color}}
	 * - {{$color_accent}}
	 *
	 * @since 3.1.0
	 * @uses $this->convert_color_css()
	 *
	 * @param array $styles The styles to add.
	 * @return string
	 */
	private function create_inline_css( array $styles ) {

		$out = '';
		foreach ( $styles as $selector => $css ) {
			$out .= $selector . '{' . implode( ';', $this->convert_color_css( $css ) ) . '}';
		}

		return $out;
	}


	/**
	 * Concatenates inline JS.
	 *
	 * @since 4.0.0
	 *
	 * @param array $scripts The scripts to add.
	 * @return string
	 */
	private function create_inline_js( array $scripts ) {

		$out = '';
		foreach ( $scripts as $script ) {
			$out .= ";$script";
		}

		return $out;
	}

	/**
	 * Converts color CSS.
	 *
	 * @since 3.1.0
	 * @staticvar array $c_ck Color keys.
	 * @staticvar array $c_cv Color values.
	 *
	 * @param array $css The CSS to convert.
	 * @return array $css
	 */
	private function convert_color_css( array $css ) {

		static $c_ck, $c_cv;

		if ( ! isset( $c_ck, $c_cv ) ) {
			$_scheme = \get_user_option( 'admin_color' ) ?: 'fresh';
			$_colors = $GLOBALS['_wp_admin_css_colors'];

			$tsf = \the_seo_framework();

			if (
			   ! isset( $_colors[ $_scheme ]->colors ) // phpcs:ignore, WordPress.WhiteSpace
			|| ! is_array( $_colors[ $_scheme ]->colors )
			|| count( $_colors[ $_scheme ]->colors ) < 4
			) {
				$_colors = [
					'#222',
					'#333',
					'#0073aa',
					'#00a0d2',
				];
			} else {
				$_colors = $_colors[ $_scheme ]->colors;
			}

			$_bg           = $_colors[0];
			$_bg_accent    = $_colors[1];
			$_color        = $_colors[2];
			$_color_accent = $_colors[3];

			$_rel_bg           = '#' . $tsf->get_relative_fontcolor( $_colors[0] );
			$_rel_bg_accent    = '#' . $tsf->get_relative_fontcolor( $_colors[1] );
			$_rel_color        = '#' . $tsf->get_relative_fontcolor( $_colors[2] );
			$_rel_color_accent = '#' . $tsf->get_relative_fontcolor( $_colors[3] );

			$_table = [
				'{{$bg}}'               => $_bg,
				'{{$rel_bg}}'           => $_rel_bg,
				'{{$bg_accent}}'        => $_bg_accent,
				'{{$rel_bg_accent}}'    => $_rel_bg_accent,
				'{{$color}}'            => $_color,
				'{{$rel_color}}'        => $_rel_color,
				'{{$color_accent}}'     => $_color_accent,
				'{{$rel_color_accent}}' => $_rel_color_accent,
			];

			$c_ck = array_keys( $_table );
			$c_cv = array_values( $_table );
		}

		return str_replace( $c_ck, $c_cv, $css );
	}

	/**
	 * Registers template for output in the admin footer.
	 *
	 * Set a multidimensional array to register multiple views.
	 *
	 * @since 3.1.0
	 *
	 * @param string $id        The related script handle/ID.
	 * @param array  $templates Associative-&-singul-, or sequential-&-multi-dimensional : {
	 *   'file' => string $file. The full file location,
	 *   'args' => array $args. Optional,
	 * }
	 */
	private function register_template( $id, array $templates ) {
		//= Wrap template if it's only one on the base.
		if ( isset( $templates['file'] ) )
			$templates = [ $templates ];

		foreach ( $templates as $t ) {
			static::$templates[ $id ][] = [
				$t['file'],
				isset( $t['args'] ) ? $t['args'] : [],
			];
		}
	}

	/**
	 * Outputs template views.
	 *
	 * The template will only be outputted when the related script is too.
	 * The loop will only run when templates are registered.
	 *
	 * @since 3.1.0
	 * @since 3.2.2 Now clears outputted templates, so to prevent duplications.
	 * @see $this->forward_known_scripts()
	 * @see $this->register_template()
	 * @see $this->autoload_scripts()
	 * @access private
	 * @internal
	 */
	public function _output_templates() {
		foreach ( static::$templates as $id => $templates ) {
			if ( \wp_script_is( $id, 'enqueued' ) ) { // This list retains scripts after they're outputted.
				foreach ( $templates as $t )
					$this->output_view( $t[0], $t[1] );
				unset( static::$templates[ $id ] );
			}
		}
	}

	/**
	 * Outputs tab view, whilst trying to prevent 3rd party interference on views.
	 *
	 * There's a secret key generated on each tab load. This key can be accessed
	 * in the view through `$_secret`, and be sent back to this class.
	 *
	 * @see static::verify( $secret )
	 *
	 * @since 3.1.0
	 * @since 3.2.4 Enabled entropy to prevent system sleep.
	 * @uses static::$include_secret
	 *
	 * @param string $file The file location.
	 * @param array  $args The registered view arguments.
	 */
	private function output_view( $file, array $args ) {

		foreach ( $args as $_key => $_val )
			$$_key = $_val;
		unset( $_key, $_val, $args );

		//= Prevents private-includes hijacking.
		// phpcs:ignore, VariableAnalysis.CodeAnalysis.VariableAnalysis -- Read the include?
		static::$include_secret = $_secret = mt_rand() . uniqid( '', true );
		include $file;
		static::$include_secret = null;
	}
}

$_load_scripts_class();
