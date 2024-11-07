<?php
/**
 * @package The_SEO_Framework\Classes\Admin\Script\Registry
 * @subpackage The_SEO_Framework\Scripts
 */

namespace The_SEO_Framework\Admin\Script;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\{
	has_run,
	umemo,
	is_headless,
};

use \The_SEO_Framework\Data;
use \The_SEO_Framework\Helper\{
	Format,
	Post_Type,
	Query,
	Taxonomy,
	Template
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2018 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Registers and outputs admin GUI scripts. Auto-invokes everything the moment
 * this file is required.
 * Relies on \WP_Dependencies to prevent duplicate loading, and autoloading.
 *
 * This handles admin-ONLY scripts for now.
 *
 * @since 3.1.0
 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Builders`.
 *              2. Renamed from `Scripts`.
 * @see \WP_Styles
 * @see \WP_Scripts
 * @see \WP_Dependencies
 * @see \The_SEO_Framework\Admin\Script\Loader
 * @access private
 */
class Registry {

	/**
	 * Codes to maintain the internal state of the scripts. This state might not reflect
	 * the actual load state. See \WP_Dependencies instead.
	 *
	 * @since 3.1.0
	 * @access private
	 *         There's a PHP bug preventing us from making this private during the deprecation phase.
	 * @var int <bit 01> REGISTERED
	 * @var int <bit 10> LOADED     (rather, enqueued)
	 */
	public const REGISTERED = 0b01;
	public const LOADED     = 0b10;

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
	 * Registers the script hooks when TSF is deemed to be loaded.
	 *
	 * @hook admin_enqueue_scripts 0
	 * @since 5.0.0
	 *
	 * @access private
	 */
	public static function _init() {

		$register = (
			   Query::is_seo_settings_page()
			// Notices can be outputted if not entirely headless -- this very method only runs when not entirely headless.
			|| Data\Plugin::get_site_cache( 'persistent_notices' )
			|| (
				! is_headless( 'meta' ) && (
					   ( Query::is_archive_admin() && Taxonomy::is_supported() )
					|| ( Query::is_singular_admin() && Post_Type::is_supported() )
				)
			)
		);

		/**
		 * @since 5.0.0
		 * @param bool $register Whether to register scripts and hooks.
		 */
		if ( \apply_filters( 'the_seo_framework_register_scripts', $register ) )
			static::register_scripts_and_hooks();
	}

	/**
	 * Registers all scripts and necessary hooks.
	 *
	 * @since 5.0.0
	 *
	 * @access public
	 */
	public static function register_scripts_and_hooks() {

		if ( has_run( __METHOD__ ) ) return;

		if ( \did_action( 'admin_enqueue_scripts' ) )
			Loader::init();

		if ( \did_action( 'in_admin_header' ) )
			static::footer_enqueue();

		// These fail when called in the body.
		\add_action( 'admin_enqueue_scripts', [ Loader::class, 'init' ], 0 );
		\add_filter( 'admin_body_class', [ static::class, '_add_body_class' ] );
		\add_action( 'in_admin_header', [ static::class, '_print_tsfjs_script' ] );

		\add_action( 'admin_enqueue_scripts', [ static::class, '_prepare_admin_scripts' ], 1 ); // Magic number: we likely run at priority 0. Add 1.
		\add_action( 'admin_footer', [ static::class, '_output_templates' ], 999 ); // Magic number: later is less likely to collide?
	}

	/**
	 * Enqueues all known registered scripts, styles, and templates.
	 *
	 * @since 3.1.0
	 */
	public static function enqueue() {
		static::_prepare_admin_scripts();
		static::_output_templates();
	}

	/**
	 * Enqueues all known registers scripts, styles, and templates,
	 * in the footer, right before WordPress's last script-outputting call.
	 *
	 * @since 4.1.2
	 * @see ABSPATH.wp-admin/admin-footer.php
	 */
	public static function footer_enqueue() {

		if ( has_run( __METHOD__ ) ) return;

		\add_action( 'admin_footer', [ static::class, 'enqueue' ], 998 ); // Magic number: 1 before output_templates.
	}

	/**
	 * Adds admin-body classes.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 1. Is now static.
	 *              2. Now adds a low contrast SEO Bar class.
	 *
	 * @param string $classes Space-separated list of CSS classes.
	 * @return string
	 */
	public static function _add_body_class( $classes ) {

		$lcseobar = Data\Plugin::get_option( 'seo_bar_low_contrast' ) ? 'tsf-seo-bar-low-contrast' : '';

		// Add spaces on both sides, because who knows what others do.
		return " tsf-no-js $lcseobar $classes";
	}

	/**
	 * Prints the TSF no-js transform script, using ES2015 (ECMA-262).
	 *
	 * @since 4.0.0
	 * @since 4.0.5 Put the const assignment on front, so it's prone to fail earlier.
	 * @since 5.0.0 Is now static.
	 */
	public static function _print_tsfjs_script() {
		echo "<script>(()=>{const a=0;document.body.classList.replace('tsf-no-js','tsf-js')})()</script>";
	}

	/**
	 * Prepares scripts for output on post edit screens.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Is now static.
	 */
	public static function _prepare_admin_scripts() {
		static::forward_known_scripts();
		static::autoload_known_scripts();
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
		return static::$queue[ $type ][ $id ] ?? 0b0;
	}

	/**
	 * Registers script to be enqueued. Can register multiple scripts at once.
	 *
	 * A better name would've been "collect"...
	 *
	 * @since 3.1.0
	 * @see $this->forward_known_scripts()
	 * @see $this->autoload_known_scripts()
	 *
	 * @NOTE If the script is associative, it'll be registered as-is.
	 *       If the script is sequential, it'll be iterated over, and then registered.
	 *
	 * @param array|array[] $script {
	 *     The script arguments or sequential array of scripts and their arguments.
	 *
	 *     @type string        $id       The script unique ID.
	 *     @type string        $type     The script type, either 'js' or 'css'.
	 *     @type boolean       $hasrtl   Optional. If true, the script will consider .rtl and .rtl.min versions.
	 *                                   Default false.
	 *     @type boolean       $autoload If true, the script will be loaded directly.
	 *                                   If false, it'll only be registered for dependencies.
	 *     @type string        $name     The script file name.
	 *     @type array         $deps     Any script dependencies by name.
	 *     @type string        $ver      Script version.
	 *     @type array         $l10n     {
	 *         Optional. Use if type is 'js'.
	 *
	 *         @type string $name The JavaScript variable.
	 *         @type mixed  $data The l10n properties.
	 *     }
	 *     @type array|array[] $tmpl     {
	 *         Optional. Use if type is 'js'. One templates or an array of templates.
	 *
	 *         @type string $file The full file location.
	 *         @type array  $args Optional. Any arguments added to the $view_args array.
	 *     }
	 *     @type array         $inline   {
	 *         Optional. Use if type is 'css'.
	 *
	 *         @type array $selector : { iterable => 'style' }
	 *     }
	 * }
	 */
	public static function register( $script ) {
		// This is over 350x faster than a polyfill for `array_is_list()`.
		if ( isset( $script[0] ) && array_values( $script ) === $script ) {
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
				if ( $s['id'] === $id && $s['type'] === $type )
					static::forward_script( $s );
			}
		}
	}

	/**
	 * Registers and enqueues known scripts.
	 *
	 * @since 3.2.2
	 *
	 * @param string $id   The script ID.
	 * @param string $type The script type.
	 */
	public static function enqueue_known_script( $id, $type ) {

		static::forward_known_script( $id, $type );

		$status = static::get_status_of( $id, $type );

		if ( ( $status & static::REGISTERED ) && ! ( $status & static::LOADED ) )
			static::load_script( $id, $type );
	}

	/**
	 * Forwards known scripts to WordPress's script handler. Also prepares l10n and templates.
	 *
	 * @since 3.2.2
	 * @since 5.0.0 Is now static.
	 */
	private static function forward_known_scripts() {
		// Register them first to accommodate for dependencies.
		foreach ( static::$scripts as $s ) {
			if ( static::get_status_of( $s['id'], $s['type'] ) & static::REGISTERED ) continue;
			static::forward_script( $s );
		}
	}

	/**
	 * Enqueues known scripts, and invokes the l10n and templates.
	 *
	 * @since 3.2.2
	 * @since 5.0.0 Is now static.
	 */
	private static function autoload_known_scripts() {
		foreach ( static::$scripts as $s ) {
			if ( $s['autoload'] ) {
				if ( static::get_status_of( $s['id'], $s['type'] ) & static::LOADED ) continue;
				static::load_script( $s['id'], $s['type'] );
			}
		}
	}

	/**
	 * Enqueues scripts in WordPress's script handler. Also prepares l10n and templates.
	 *
	 * @since 3.2.2
	 *
	 * @param array $s The script.
	 */
	private static function forward_script( $s ) {

		$registered = false;

		switch ( $s['type'] ) {
			case 'css':
				\wp_register_style( $s['id'], static::generate_file_url( $s, 'css' ), $s['deps'], $s['ver'], 'all' );
				isset( $s['inline'] )
					and \wp_add_inline_style( $s['id'], static::create_inline_css( $s['inline'] ) );
				$registered = true;
				break;
			case 'js':
				\wp_register_script( $s['id'], static::generate_file_url( $s, 'js' ), $s['deps'], $s['ver'], true );
				isset( $s['l10n'] )
					and \wp_localize_script( $s['id'], $s['l10n']['name'], $s['l10n']['data'] );
				isset( $s['tmpl'] )
					and static::register_template( $s['id'], $s['tmpl'] );
				isset( $s['inline'] )
					and \wp_add_inline_script( $s['id'], static::create_inline_js( $s['inline'] ) );
				$registered = true;
		}

		if ( $registered ) {
			isset( static::$queue[ $s['type'] ][ $s['id'] ] )
				and static::$queue[ $s['type'] ][ $s['id'] ] |= static::REGISTERED
				 or static::$queue[ $s['type'] ][ $s['id'] ]  = static::REGISTERED;
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
		}

		if ( $loaded ) {
			isset( static::$queue[ $type ][ $id ] )
				and static::$queue[ $type ][ $id ] |= static::LOADED
				 or static::$queue[ $type ][ $id ]  = static::LOADED;
		}
	}

	/**
	 * Generates file URL.
	 * Memoizes use of RTL and minification.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Is now static.
	 *
	 * @param array $script The script arguments.
	 * @param array $type Either 'js' or 'css'.
	 * @return string The file URL.
	 */
	private static function generate_file_url( $script, $type = 'js' ) {

		static $min, $rtl;

		if ( ! isset( $min, $rtl ) ) {
			$min = \SCRIPT_DEBUG ? '' : '.min';
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
	 * @since 5.0.0 Is now static.
	 *
	 * @param iterable $styles The styles to add.
	 * @return string
	 */
	private static function create_inline_css( $styles ) {

		$out = '';

		foreach ( $styles as $selector => $declaration ) {
			$out .= \sprintf(
				'%s{%s}',
				$selector,
				implode( ';', static::convert_color_css_declaration( $declaration ) )
			);
		}

		return $out;
	}

	/**
	 * Concatenates inline JS.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Is now static.
	 *
	 * @param iterable $scripts The scripts to add.
	 * @return string
	 */
	private static function create_inline_js( $scripts ) {

		$out = '';

		foreach ( $scripts as $script )
			$out .= ";$script";

		return $out;
	}

	/**
	 * Converts color CSS.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 1. Is now static.
	 *              2. Renamed from `convert_color_css`.
	 * @link <https://make.wordpress.org/core/2021/02/23/standardization-of-wp-admin-colors-in-wordpress-5-7/>
	 *
	 * @param array $css The CSS to convert.
	 * @return array $css
	 */
	private static function convert_color_css_declaration( $css ) {

		$conversions = umemo( __METHOD__ . '/conversions' );

		if ( ! $conversions ) {
			$_scheme = \get_user_option( 'admin_color' ) ?: 'fresh';
			$_colors = $GLOBALS['_wp_admin_css_colors'];

			if (
				   ! \is_array( $_colors[ $_scheme ]->colors ?? null )
				|| \count( $_colors[ $_scheme ]->colors ) < 4 // unexpected scheme, ignore and override.
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

			$_conversion_table = [
				'{{$bg}}'               => $_colors[0],
				'{{$rel_bg}}'           => '#' . Format\Color::get_relative_fontcolor( $_colors[0] ),
				'{{$bg_accent}}'        => $_colors[1],
				'{{$rel_bg_accent}}'    => '#' . Format\Color::get_relative_fontcolor( $_colors[1] ),
				'{{$color}}'            => $_colors[2],
				'{{$rel_color}}'        => '#' . Format\Color::get_relative_fontcolor( $_colors[2] ),
				'{{$color_accent}}'     => $_colors[3],
				'{{$rel_color_accent}}' => '#' . Format\Color::get_relative_fontcolor( $_colors[3] ),
			];

			$conversions = umemo(
				__METHOD__ . '/conversions',
				[
					'search'  => array_keys( $_conversion_table ),
					'replace' => array_values( $_conversion_table ),
				],
			);
		}

		return str_replace( $conversions['search'], $conversions['replace'], $css );
	}

	/**
	 * Registers template for output in the admin footer.
	 *
	 * Set a multidimensional array to register multiple views.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Is now static.
	 *
	 * @param string      $id        The related script handle/ID.
	 * @param array|[?][] $templates {
	 *     Associative-&-singul-, or sequential-&-multi-dimensional array of templates.
	 *
	 *     @type string $file The full file location.
	 *     @type array  $args Optional. Any arguments added to the $view_args array.
	 * }
	 */
	private static function register_template( $id, $templates ) {
		// Wrap template if it's only one on the base.
		if ( isset( $templates['file'] ) )
			$templates = [ $templates ];

		foreach ( $templates as $t ) {
			static::$templates[ $id ][] = [
				$t['file'],
				$t['args'] ?? [],
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
	 * @since 4.1.2 Now clears templates right before outputting them, so to prevent a plausible infinite loop.
	 * @since 5.0.0 Is now static.
	 */
	public static function _output_templates() {
		foreach ( static::$templates as $id => $templates ) {
			if ( \wp_script_is( $id, 'enqueued' ) ) { // This list retains scripts after they're outputted.
				// Unset template before the loop, to prevent an infinite loop.
				unset( static::$templates[ $id ] );

				foreach ( $templates as $t )
					Template::output_absolute_view( $t[0], $t[1] );
			}
		}
	}
}
