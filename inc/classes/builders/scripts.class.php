<?php
/**
 * @package The_SEO_Framework\Classes\Builders
 * @subpackage The_SEO_Framework\Builders
 */
namespace The_SEO_Framework\Builders;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * Sets up class loader as file is loaded.
 * This is done asynchronously, because static calls are handled prior and after.
 * @see EOF. Because of the autoloader and trait calling, we can't do it before the class is read.
 * @link https://bugs.php.net/bug.php?id=75771
 */
$_load_scripts_class = function() {
	new Scripts();
};

/**
 * Registers and outputs inpost GUI scripts. Auto-invokes everything the moment
 * this file is required.
 *
 * This handles admin-only scripts ONLY for now.
 *
 * @since 3.1.0
 * @see the_seo_framework()->Scripts()
 * @access private
 * @final Can't be extended.
 */
final class Scripts {

	/**
	 * @since 3.1.0
	 * @param array $scripts   The registered scripts.
	 * @param array $templates The registered templates.
	 */
	private static $scripts   = [];
	private static $templates = [];

	/**
	 * Internal singleton object holder.
	 * @since 3.1.0
	 * @param The_SEO_Framework\Builders\Scripts $instance The instance.
	 */
	private static $instance;

	/**
	 * @since 3.1.0
	 * @param string $include_secret The inclusion secret generated on tab load.
	 */
	public static $include_secret;

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
	 * @since 3.1.0
	 * Probably loads at action "admin_enqueue_scripts", priority "0".
	 */
	public function __construct() {

		static $count = 0;
		0 === $count++ or \wp_die( 'Don\'t instance <code>' . __CLASS__ . '</code>.' );

		static::$instance = &$this;

		\add_action( 'admin_enqueue_scripts', [ $this, '_prepare_admin_scripts' ], 1 );
		\add_action( 'admin_footer', [ $this, '_output_templates' ] );
	}

	/**
	 * Enqueues registered scripts, styles, and templates.
	 *
	 * @since 3.1.0
	 */
	public static function enqueue() {
		static::$instance->_prepare_admin_scripts();
		static::$instance->_output_templates();
	}

	/**
	 * Registers script to be enqueued.
	 *
	 * @since 3.1.0
	 * @uses static::$scripts
	 * @see $this->enqueue_scripts()
	 *
	 * @NOTE If the script is associative, it'll be registered as-is.
	 *       If the script is sequential (weak check!!!), it'll be iterated over, and then registered.
	 *
	 * @param array $script The script : {
	 *   'id'   => string The script ID,
	 *   'type' => string 'css|js',
	 *   'autoload' => boolean|void If void|null|true, the script will be loaded.
	 *                              If false, it'll only be registered for dependencies.
	 *                              Templates are always outputted,
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
	 *   'inline' => array If 'type' is CSS : {
	 *      'selector' => array : { iterable => 'style' }
	 *    }
	 * }
	 */
	public static function register( array $script ) {
		if ( isset( $script[0] ) ) {
			foreach ( $script as $s ) static::register( $s );
			return;
		}

		static::$scripts[] = $script;
	}

	/**
	 * Prepares scripts for output on post edit screens.
	 *
	 * @since 3.1.0
	 * @access private
	 *
	 * @param string $hook The current admin hook.
	 */
	public function _prepare_admin_scripts( $hook = '' ) {
		$this->enqueue_scripts();
	}

	/**
	 * Enqueues scripts, l10n and templates.
	 *
	 * @since 3.1.0
	 * @uses static::$scripts
	 * @uses $this->generate_file_url()
	 * @uses $this->register_template()
	 */
	private function enqueue_scripts() {

		//= Register them first to accomodate for dependencies.
		foreach ( static::$scripts as $s ) {
			switch ( $s['type'] ) {
				case 'css' :
					\wp_register_style( $s['id'], $this->generate_file_url( $s, 'css' ), $s['deps'], $s['ver'], 'all' );
					isset( $s['inline'] )
						and \wp_add_inline_style( $s['id'], $this->get_inline_css( $s['inline'] ) );
					break;
				case 'js' :
					\wp_register_script( $s['id'], $this->generate_file_url( $s, 'js' ), $s['deps'], $s['ver'], true );
					isset( $s['l10n'] )
						and \wp_localize_script( $s['id'], $s['l10n']['name'], $s['l10n']['data'] );
					isset( $s['tmpl'] )
						and $this->register_template( $s['tmpl'] );
					break;
			}
		}

		foreach ( static::$scripts as $s ) {
			if ( ! isset( $s['autoload'] ) || $s['autoload'] ) {
				switch ( $s['type'] ) {
					case 'css' :
						\wp_enqueue_style( $s['id'] );
						break;
					case 'js' :
						\wp_enqueue_script( $s['id'] );
						break;
				}
			}
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
	final private function generate_file_url( array $script, $type = 'js' ) {

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
	 *
	 * @param array $colors The color CSS.
	 * @return array $css
	 */
	private function get_inline_css( array $styles ) {

		$out = '';
		foreach ( $styles as $selector => $css ) {
			$out .= $selector . '{' . implode( ';', $this->convert_color_css( $css ) ) . '}';
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
	 * @param array $css
	 * @return array $css
	 */
	private function convert_color_css( array $css ) {

		static $c_ck, $c_cv;

		if ( ! isset( $c_ck, $c_cv ) ) {
			$_scheme = \get_user_option( 'admin_color' ) ?: 'fresh';
			$_colors = $GLOBALS['_wp_admin_css_colors'];

			if (
			   ! isset( $_colors[ $_scheme ]->colors )
			|| ! is_array( $_colors[ $_scheme ]->colors )
			|| count( $_colors[ $_scheme ]->colors ) < 4
			) {
				//= Default 'fresh' table.
				$_table = [
					'{{$bg}}'           => '#222',
					'{{$bg_accent}}'    => '#333',
					'{{$color}}'        => '#0073aa',
					'{{$color_accent}}' => '#00a0d2',
				];
				$c_ck = array_keys( $_table );
				$c_cv = array_values( $_table );
			} else {
				$_colors = $_colors[ $_scheme ]->colors;

				$_bg           = $_colors[0];
				$_bg_accent    = $_colors[1];
				$_color        = $_colors[2];
				$_color_accent = $_colors[3];

				$_table = [
					'{{$bg}}'           => $_bg,
					'{{$bg_accent}}'    => $_bg_accent,
					'{{$color}}'        => $_color,
					'{{$color_accent}}' => $_color_accent,
				];
				$c_ck = array_keys( $_table );
				$c_cv = array_values( $_table );
			}
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
	 * @param array $templates, single or multi-dimensional : {
	 *   'file' => string $file. The full file location,
	 *   'args' => array $args. Optional,
	 * }
	 */
	private function register_template( array $templates ) {
		//= Wrap template if it's only one on the base.
		if ( isset( $templates['file'] ) )
			$templates = [ $templates ];

		foreach ( $templates as $t )
			static::$templates[] = [
				$t['file'],
				isset( $t['args'] ) ? $t['args'] : [],
			];
	}

	/**
	 * Outputs template views.
	 *
	 * The loop will only run when templates are registered.
	 * @see $this->enqueue_scripts()
	 *
	 * @since 3.1.0
	 */
	public function _output_templates() {
		foreach ( static::$templates as $template )
			$this->output_view( $template[0], $template[1] );
	}

	/**
	 * Outputs tab view, whilst trying to prevent 3rd party interference on views.
	 *
	 * There's a secret key generated on each tab load. This key can be accessed
	 * in the view through `$_secret`, and be sent back to this class.
	 * @see static::verify( $secret )
	 *
	 * @since 3.1.0
	 * @uses static::$include_secret
	 *
	 * @param string $file The file location.
	 * @param array  $args The registered view arguments.
	 */
	private function output_view( $file, array $args ) {

		foreach ( $args as $_key => $_val )
			$$_key = $_val;
		unset( $_key, $_val, $args );

		//= Prevent private includes hijacking.
		static::$include_secret = $_secret = mt_rand() . uniqid();
		include $file;
		static::$include_secret = null;
	}

	/**
	 * Verifies view inclusion secret.
	 *
	 * @since 3.1.0
	 * @see static::output_view()
	 * @uses static::$include_secret
	 *
	 * @param string $secret The passed secret.
	 * @return bool True on success, false on failure.
	 */
	public static function verify( $secret ) {
		return static::$include_secret === $secret;
	}
}

$_load_scripts_class();
