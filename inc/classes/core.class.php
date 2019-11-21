<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Core
 * @see ./index.php for facade details.
 */

namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

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
 * Class The_SEO_Framework\Core
 *
 * Initializes the plugin & Holds plugin core functions.
 *
 * @since 2.8.0
 */
class Core {
	use Traits\Enclose_Core_Final;

	/**
	 * Tells if this plugin is loaded.
	 *
	 * @NOTE: Only `\The_SEO_Framework\_init_tsf()` should adjust this.
	 *
	 * @since 3.1.0
	 * @access protected
	 *         Don't alter this variable!!!
	 * @var boolean $loaded
	 */
	public $loaded = false;

	/**
	 * Calling any top file without __construct() is forbidden.
	 */
	private function __construct() { }

	/**
	 * Handles unapproachable invoked properties.
	 *
	 * Makes sure deprecated properties are still overwritten.
	 * If the property never existed, default PHP behavior is invoked.
	 *
	 * @since 2.8.0
	 * @since 3.2.2 This method no longer allows to overwrite protected or private variables.
	 *
	 * @param string $name  The property name.
	 * @param mixed  $value The property value.
	 */
	final public function __set( $name, $value ) {
		/**
		 * For now, no deprecation is being handled; as no properties have been deprecated. Just removed.
		 */
		$this->_inaccessible_p_or_m( 'the_seo_framework()->' . $name, 'unknown' );

		//* Invoke default behavior: Write variable if it's not protected.
		if ( ! isset( $this->$name ) )
			$this->$name = $value;
	}

	/**
	 * Handles unapproachable invoked properties.
	 *
	 * Makes sure deprecated properties are still accessible.
	 *
	 * @since 2.7.0
	 * @since 3.1.0 Removed known deprecations.
	 * @since 3.2.2 This method no longer invokes PHP errors, nor returns protected values.
	 *
	 * @param string $name The property name.
	 * @return void
	 */
	final public function __get( $name ) {
		$this->_inaccessible_p_or_m( 'the_seo_framework()->' . $name, 'unknown' );
	}

	/**
	 * Handles unapproachable invoked methods.
	 *
	 * @since 2.7.0
	 *
	 * @param string $name      The method name.
	 * @param array  $arguments The method arguments.
	 * @return mixed|void
	 */
	final public function __call( $name, $arguments ) {

		static $depr_class = null;

		if ( is_null( $depr_class ) )
			$depr_class = new Deprecated;

		if ( is_callable( [ $depr_class, $name ] ) ) {
			return call_user_func_array( [ $depr_class, $name ], $arguments );
		}

		\the_seo_framework()->_inaccessible_p_or_m( 'the_seo_framework()->' . $name . '()' );
	}

	/**
	 * Destroys output buffer, if any. To be used with AJAX and XML to clear any PHP errors or dumps.
	 *
	 * @since 2.8.0
	 * @since 2.9.0 Now flushes all levels rather than just the latest one.
	 * @since 4.0.0 Is now public.
	 *
	 * @return bool True on clear. False otherwise.
	 */
	public function clean_response_header() {

		$level = ob_get_level();

		if ( $level ) {
			while ( $level-- ) ob_end_clean();
			return true;
		}

		return false;
	}

	/**
	 * Fetches files based on input to reduce memory overhead.
	 * Passes on input vars.
	 *
	 * @since 2.7.0
	 * @access private
	 * @credits Akismet For some code.
	 *
	 * @param string $view     The file name.
	 * @param array  $__args   The arguments to be supplied within the file name.
	 *                         Each array key is converted to a variable with its value attached.
	 * @param string $instance The instance suffix to call back upon.
	 */
	public function get_view( $view, array $__args = [], $instance = 'main' ) {

		//? A faster extract().
		foreach ( $__args as $__k => $__v ) $$__k = $__v;
		unset( $__k, $__v, $__args );

		include $this->get_view_location( $view );
	}

	/**
	 * Gets view location.
	 *
	 * @since 3.1.0
	 *
	 * @param string $file The file name.
	 * @return string The view location.
	 */
	public function get_view_location( $file ) {
		return THE_SEO_FRAMEWORK_DIR_PATH_VIEWS . $file . '.php';
	}

	/**
	 * Fetches view instance for view-switch statements.
	 *
	 * @since 2.7.0
	 *
	 * @param string $base The instance basename (namespace).
	 * @param string $instance The instance suffix to call back upon.
	 * @return string The file instance case.
	 */
	protected function get_view_instance( $base, $instance = 'main' ) {
		return $base . '_' . str_replace( '-', '_', $instance );
	}

	/**
	 * Proportionate dimensions based on Width and Height.
	 * AKA Aspect Ratio.
	 *
	 * @since 2.6.0
	 *
	 * @param int $i  The dimension to resize.
	 * @param int $r1 The deminsion that determines the ratio.
	 * @param int $r2 The dimension to proportionate to.
	 * @return int The proportional dimension, rounded.
	 */
	public function proportionate_dimensions( $i, $r1, $r2 ) {

		//* Get aspect ratio.
		$ar = $r1 / $r2;
		$i  = $i / $ar;

		return round( $i );
	}

	/**
	 * Adds various links to the plugin row on the plugin's screen.
	 *
	 * @since 3.1.0
	 * @access private
	 *
	 * @param array $links The current links.
	 * @return array The plugin links.
	 */
	public function _add_plugin_action_links( $links = [] ) {

		if ( $this->load_options )
			$tsf_links['settings'] = sprintf(
				'<a href="%s">%s</a>',
				\esc_url( \admin_url( 'admin.php?page=' . $this->seo_settings_page_slug ) ),
				\esc_html__( 'Settings', 'autodescription' )
			);

		$tsf_links['about'] = sprintf(
			'<a href="https://theseoframework.com/about-us/" rel="noreferrer noopener nofollow" target="_blank">%s</a>',
			\esc_html_x( 'About', 'About us', 'autodescription' )
		);
		$tsf_links['tsfem'] = sprintf(
			'<a href="%s" rel="noreferrer noopener" target="_blank">%s</a>',
			'https://theseoframework.com/extensions/',
			\esc_html_x( 'Extensions', 'Plugin extensions', 'autodescription' )
		);

		return array_merge( $tsf_links, $links );
	}

	/**
	 * Adds more row meta on the plugin screen.
	 *
	 * @since 3.2.4
	 * @access private
	 *
	 * @param string[] $plugin_meta An array of the plugin's metadata,
	 *                              including the version, author,
	 *                              author URI, and plugin URI.
	 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
	 * @return array $plugin_meta
	 */
	public function _add_plugin_row_meta( $plugin_meta, $plugin_file ) {

		if ( THE_SEO_FRAMEWORK_PLUGIN_BASENAME !== $plugin_file )
			return $plugin_meta;

		$plugins = \get_plugins();
		$_get_em = empty( $plugins['the-seo-framework-extension-manager/the-seo-framework-extension-manager.php'] );

		return array_merge(
			$plugin_meta,
			[
				'docs' => vsprintf(
					'<a href="%s" rel="noreferrer noopener nofollow" target="_blank">%s</a>',
					[
						'https://tsf.fyi/docs',
						\esc_html__( 'View documentation', 'autodescription' ),
					]
				),
				'API'  => vsprintf(
					'<a href="%s" rel="noreferrer noopener nofollow" target="_blank">%s</a>',
					[
						'https://tsf.fyi/docs/api',
						\esc_html__( 'View API docs', 'autodescription' ),
					]
				),
				'EM'   => vsprintf(
					'<a href="%s" rel="noreferrer noopener nofollow" target="_blank">%s</a>',
					[
						'https://tsf.fyi/extension-manager',
						$_get_em ? \esc_html_x( 'Get the Extension Manager', 'Extension Manager is a product name; do not translate it.', 'autodescription' ) : 'Extension Manager',
					]
				),
			]
		);
	}

	/**
	 * Returns an array of hierarchical post types.
	 *
	 * @since 4.0.0
	 *
	 * @return array The public hierarchical post types with rewrite.
	 */
	public function get_hierarchical_post_types() {
		static $types;
		return $types ?: $types = \get_post_types(
			[
				'hierarchical' => true,
				'public'       => true,
				'rewrite'      => true,
			],
			'names'
		);
	}

	/**
	 * Returns an array of nonhierarchical post types.
	 *
	 * @since 4.0.0
	 *
	 * @return array The public nonhierarchical post types with rewrite.
	 */
	public function get_nonhierarchical_post_types() {
		static $types;
		return $types ?: $types = \get_post_types(
			[
				'hierarchical' => false,
				'public'       => true,
				'rewrite'      => true,
			],
			'names'
		);
	}

	/**
	 * Whether to allow external redirect through the 301 redirect option.
	 *
	 * @since 2.6.0
	 * @staticvar bool $allowed
	 *
	 * @return bool Whether external redirect is allowed.
	 */
	public function allow_external_redirect() {

		static $allowed = null;

		if ( isset( $allowed ) )
			return $allowed;

		/**
		 * @since 2.1.0
		 * @param bool $allowed Whether external redirect is allowed.
		 */
		return $allowed = (bool) \apply_filters( 'the_seo_framework_allow_external_redirect', true );
	}

	/**
	 * Checks if blog is public through WordPress core settings.
	 *
	 * @since 2.6.0
	 * @staticvar bool $cache
	 *
	 * @return bool True is blog is public.
	 */
	public function is_blog_public() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		if ( '1' === \get_option( 'blog_public' ) )
			return $cache = true;

		return $cache = false;
	}

	/**
	 * Whether the current blog is spam or deleted.
	 * Multisite Only.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Now uses get_site()
	 * @since 3.1.1 Now checks for `is_multisite()`, to prevent a crash with Divi's compatibility injection.
	 *
	 * @return bool Current blog is spam.
	 */
	public function current_blog_is_spam_or_deleted() {

		if ( ! function_exists( '\\get_site' ) || ! \is_multisite() )
			return false;

		$site = \get_site();

		if ( $site instanceof \WP_Site && ( '1' === $site->spam || '1' === $site->deleted ) )
			return true;

		return false;
	}

	/**
	 * Returns the minimum role required to adjust settings.
	 *
	 * @since 3.0.0
	 *
	 * @return string The minimum required capability for SEO Settings.
	 */
	public function get_settings_capability() {
		/**
		 * @since 2.6.0
		 * @param string $capability The user capability required to adjust settings.
		 */
		return (string) \apply_filters( 'the_seo_framework_settings_capability', 'manage_options' );
	}

	/**
	 * Determines if the current user can do settings.
	 * Not cached as it's imposing security functionality.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	public function can_access_settings() {
		return \current_user_can( $this->get_settings_capability() );
	}

	/**
	 * Returns the SEO Settings page URL.
	 *
	 * @since 2.6.0
	 *
	 * @return string The escaped SEO Settings page URL.
	 */
	public function seo_settings_page_url() {

		if ( $this->load_options ) {
			//* Options are allowed to be loaded.

			$url = html_entity_decode( \menu_page_url( $this->seo_settings_page_slug, false ) );

			return \esc_url( $url, [ 'https', 'http' ] );
		}

		return '';
	}

	/**
	 * Returns the PHP timezone compatible string.
	 * UTC offsets are unreliable.
	 *
	 * @since 2.6.0
	 *
	 * @param bool $guess If true, the timezone will be guessed from the
	 *                    WordPress core gmt_offset option.
	 * @return string PHP Timezone String. May be empty (thus invalid).
	 */
	public function get_timezone_string( $guess = false ) {

		$tzstring = \get_option( 'timezone_string' );

		if ( false !== strpos( $tzstring, 'Etc/GMT' ) )
			$tzstring = '';

		if ( $guess && empty( $tzstring ) ) {
			$tzstring = $this->get_tzstring_from_offset( \get_option( 'gmt_offset' ) );
		}

		return $tzstring;
	}

	/**
	 * Fetches the Timezone String from given offset.
	 *
	 * @since 2.6.0
	 * @since 4.0.0 Removed PHP <5.6 support.
	 *
	 * @param int $offset The GMT offzet.
	 * @return string PHP Timezone String.
	 */
	protected function get_tzstring_from_offset( $offset = 0 ) {

		$seconds  = round( $offset * HOUR_IN_SECONDS );
		$tzstring = timezone_name_from_abbr( '', $seconds, 1 );

		return $tzstring;
	}

	/**
	 * Sets and resets the timezone.
	 *
	 * NOTE: Always call reset_timezone() ASAP. Don't let changes linger, as they can be destructive.
	 *
	 * This exists because WordPress' current_time() adds discrepancies between UTC and GMT.
	 * This is also far more accurate than WordPress' tiny time table.
	 *
	 * @TODO Note that WordPress 5.3 no longer requires this, and that we should rely on wp_date() instead.
	 *       So, we should remove this dependency ASAP.
	 *
	 * @since 2.6.0
	 * @since 3.0.6 Now uses the old timezone string when a new one can't be generated.
	 * @since 4.0.4 Now also unsets the stored timezone string on reset.
	 * @link http://php.net/manual/en/timezones.php
	 *
	 * @param string $tzstring Optional. The PHP Timezone string. Best to leave empty to always get a correct one.
	 * @param bool   $reset Whether to reset to default. Ignoring first parameter.
	 * @return bool True on success. False on failure.
	 */
	public function set_timezone( $tzstring = '', $reset = false ) {

		static $old_tz = null;

		// phpcs:ignore, WordPress.WP.TimezoneChange
		$old_tz = $old_tz ?: date_default_timezone_get() ?: 'UTC';

		if ( $reset ) {
			$_revert_tz = $old_tz;
			$old_tz     = null;
			// phpcs:ignore, WordPress.WP.TimezoneChange
			return date_default_timezone_set( $_revert_tz );
		}

		if ( empty( $tzstring ) )
			$tzstring = $this->get_timezone_string( true ) ?: $old_tz;

		// phpcs:ignore, WordPress.WP.TimezoneChange
		return date_default_timezone_set( $tzstring );
	}

	/**
	 * Resets the timezone to default or UTC.
	 *
	 * @since 2.6.0
	 *
	 * @return bool True on success. False on failure.
	 */
	public function reset_timezone() {
		return $this->set_timezone( '', true );
	}

	/**
	 * Converts time from GMT input to given format.
	 *
	 * @since 2.7.0
	 * @since 4.0.4 Now uses `gmdate()` instead of `date()`.
	 * @see `$this->set_timezone()`
	 * @see `$this->reset_timezone()`
	 *
	 * @param string $format The datetime format.
	 * @param string $time The GMT time. Expects timezone to be omitted.
	 * @return string The converted time. Empty string if no $time is given.
	 */
	public function gmt2date( $format = 'Y-m-d', $time = '' ) {

		if ( $time )
			return gmdate( $format, strtotime( $time . ' GMT' ) );

		return '';
	}

	/**
	 * Returns timestamp format based on timestamp settings.
	 *
	 * @since 3.0.0
	 *
	 * @return string The timestamp format used in PHP date.
	 */
	public function get_timestamp_format() {
		return $this->uses_time_in_timestamp_format() ? 'Y-m-d\TH:iP' : 'Y-m-d';
	}

	/**
	 * Determines if time is used in the timestamp format.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if time is used. False otherwise.
	 */
	public function uses_time_in_timestamp_format() {
		return '1' === $this->get_option( 'timestamps_format' );
	}

	/**
	 * Shortens string and adds ellipses when over a threshold in length.
	 *
	 * @since 3.1.0
	 *
	 * @param string $string The string to test and maybe trim
	 * @param int    $over   The character limit. Must be over 0 to have effect.
	 *                       If 1 is given, the returned string length will be 3.
	 *                       If 2 is given, the returned string will only consist of the hellip.
	 * @return string
	 */
	public function hellip_if_over( $string, $over = 0 ) {
		if ( $over > 0 && strlen( $string ) > $over ) {
			$string = substr( $string, 0, abs( $over - 2 ) ) . ' &hellip;';
		}

		return $string;
	}

	/**
	 * Counts words encounters from input string.
	 * Case insensitive. Returns first encounter of each word if found multiple times.
	 *
	 * Will only return words that are above set input thresholds.
	 *
	 * @since 2.7.0
	 * @since 3.1.0 This method now uses PHP 5.4+ encoding, capable of UTF-8 interpreting,
	 *              instead of relying on PHP's incomplete encoding table.
	 *              This does mean that the functionality is crippled when the PHP
	 *              installation isn't unicode compatible; this is unlikely.
	 * @since 4.0.0 1. Now expects PCRE UTF-8 encoding support.
	 *              2. Moved filter outside of this function.
	 *              3. Short length now works as intended, instead of comparing as less, it compares as less or equal to.
	 * @staticvar bool   $use_mb Determines whether we can use mb_* functions.
	 *
	 * @param string $string Required. The string to count words in.
	 * @param int    $dupe_count Minimum amount of words to encounter in the string.
	 *                      Set to 0 to count all words longer than $short_length.
	 * @param int    $dupe_short Minimum amount of words to encounter in the string that fall under the
	 *                           $short_length. Set to 0 to consider all words with $amount.
	 * @param int    $short_length The maximum string length of a word to pass for $dupe_short
	 *                             instead of $count. Set to 0 to ignore $count, and use $dupe_short only.
	 * @return array Containing arrays of words with their count.
	 */
	public function get_word_count( $string, $dupe_count = 3, $dupe_short = 5, $short_length = 3 ) {

		$string = html_entity_decode( $string );
		$string = \wp_check_invalid_utf8( $string );

		if ( ! $string ) return [];

		static $use_mb;

		isset( $use_mb ) or $use_mb = extension_loaded( 'mbstring' );

		$word_list = preg_split(
			'/[^\p{L}\p{M}\p{N}\p{Pc}\p{Cc}]+/mu',
			$use_mb ? mb_strtolower( $string ) : strtolower( $string ),
			-1,
			PREG_SPLIT_OFFSET_CAPTURE | PREG_SPLIT_NO_EMPTY
		);

		$words_too_many = [];

		if ( count( $word_list ) ) :
			$words = [];
			foreach ( $word_list as $wli ) {
				//= { $words[ int Offset ] => string Word }
				$words[ $wli[1] ] = $wli[0];
			}

			$word_count = array_count_values( $words );

			// We're going to fetch words based on position, and then flip it to become the key.
			$word_keys = array_flip( array_reverse( $words, true ) );

			foreach ( $word_count as $word => $count ) {
				if ( ( $use_mb ? mb_strlen( $word ) : strlen( $word ) ) <= $short_length ) {
					$run = $count >= $dupe_short;
				} else {
					$run = $count >= $dupe_count;
				}

				if ( $run ) {
					//! Don't use mb_* here. preg_split's offset is in bytes, NOT multibytes.
					$args = [
						'pos' => $word_keys[ $word ],
						'len' => strlen( $word ),
					];

					$first_encountered_word = substr( $string, $args['pos'], $args['len'] );

					//* Found words that are used too frequently.
					$words_too_many[] = [ $first_encountered_word => $count ];
				}
			}
		endif;

		return $words_too_many;
	}

	/**
	 * Calculates the relative font color according to the background.
	 *
	 * @since 2.8.0
	 * @since 2.9.0 Now adds a little more relative softness based on rel_lum.
	 * @since 2.9.2 (Typo): Renamed from 'get_relatitve_fontcolor' to 'get_relative_fontcolor'.
	 * @since 3.0.4 : Now uses WCAG's relative luminance formula
	 * @link https://www.w3.org/TR/2008/REC-WCAG20-20081211/#visual-audio-contrast-contrast
	 * @link https://www.w3.org/WAI/GL/wiki/Relative_luminance
	 *
	 * @param string $hex The 3 to 6 character RGB hex. The '#' prefix may be added.
	 * @return string The hexadecimal RGB relative font color, without '#' prefix.
	 */
	public function get_relative_fontcolor( $hex = '' ) {

		$hex = ltrim( $hex, '#' );

		//* #rgb = #rrggbb
		if ( 3 === strlen( $hex ) )
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];

		$hex = str_split( $hex, 2 );

		//* Convert to numerical values.
		$r = hexdec( $hex[0] );
		$g = hexdec( $hex[1] );
		$b = hexdec( $hex[2] );

		$get_relative_luminance = function( $v ) {
			//= Convert to 0~1 value.
			$v /= 255;

			if ( $v > .03928 ) {
				$lum = ( ( $v + .055 ) / 1.055 ) ** 2.4;
			} else {
				$lum = $v / 12.92;
			}
			return $lum;
		};

		//* Use sRGB for relative luminance.
		$sr = 0.2126 * $get_relative_luminance( $r );
		$sg = 0.7152 * $get_relative_luminance( $g );
		$sb = 0.0722 * $get_relative_luminance( $b );

		$rel_lum = ( $sr + $sg + $sb );

		//= Invert colors if they hit luminance boundaries.
		if ( $rel_lum < 0.5 ) {
			//* Build dark greyscale.
			$gr = 255 - ( $r * 0.2989 / 8 ) * $rel_lum;
			$gg = 255 - ( $g * 0.5870 / 8 ) * $rel_lum;
			$gb = 255 - ( $b * 0.1140 / 8 ) * $rel_lum;
		} else {
			//* Build light greyscale.
			$gr = ( $r * 0.2989 / 8 ) * $rel_lum;
			$gg = ( $g * 0.5870 / 8 ) * $rel_lum;
			$gb = ( $b * 0.1140 / 8 ) * $rel_lum;
		}

		//* Build RGB hex.
		$retr = str_pad( dechex( round( $gr ) ), 2, '0', STR_PAD_LEFT );
		$retg = str_pad( dechex( round( $gg ) ), 2, '0', STR_PAD_LEFT );
		$retb = str_pad( dechex( round( $gb ) ), 2, '0', STR_PAD_LEFT );

		return $retr . $retg . $retb;
	}

	/**
	 * Returns sitemap color scheme.
	 *
	 * @since 2.8.0
	 *
	 * @param bool $get_defaults Whether to get the default colors.
	 * @return array The sitemap colors.
	 */
	public function get_sitemap_colors( $get_defaults = false ) {

		if ( $get_defaults ) {
			$colors = [
				'main'   => '#333',
				'accent' => '#00cd98',
			];
		} else {
			$main   = $this->s_color_hex( $this->get_option( 'sitemap_color_main' ) );
			$accent = $this->s_color_hex( $this->get_option( 'sitemap_color_accent' ) );

			$options = [
				'main'   => $main ? '#' . $main : '',
				'accent' => $accent ? '#' . $accent : '',
			];

			$options = array_filter( $options );

			$colors = array_merge( $this->get_sitemap_colors( true ), $options );
		}

		return $colors;
	}

	/**
	 * Converts markdown text into HMTL.
	 * Does not support list or block elements. Only inline statements.
	 *
	 * Note: This code has been rightfully stolen from the Extension Manager plugin (sorry Sybre!).
	 *
	 * @since 2.8.0
	 * @since 2.9.0 1. Removed word boundary requirement for strong.
	 *              2. Now accepts regex count their numeric values in string.
	 *              3. Fixed header 1~6 calculation.
	 * @since 2.9.3 Added $args parameter.
	 * @since 4.0.3 Added a workaround for connected em/strong elements.
	 * @link https://wordpress.org/plugins/about/readme.txt
	 *
	 * @param string $text    The text that might contain markdown. Expected to be escaped.
	 * @param array  $convert The markdown style types wished to be converted.
	 *                        If left empty, it will convert all.
	 * @param array  $args    The function arguments.
	 * @return string The markdown converted text.
	 */
	public function convert_markdown( $text, $convert = [], $args = [] ) {

		preprocess : {
			$text = str_replace( "\r\n", "\n", $text );
			$text = str_replace( "\t", ' ', $text );
			$text = trim( $text );
		}

		// You need 3 chars to make a markdown: *m*
		if ( strlen( $text ) < 3 )
			return '';

		// Merge defaults with $args.
		$args = array_merge( [ 'a_internal' => false ], $args );

		/**
		 * The conversion list's keys are per reference only.
		 */
		$conversions = [
			'**'     => 'strong',
			'*'      => 'em',
			'`'      => 'code',
			'[]()'   => 'a',
			'======' => 'h6',
			'====='  => 'h5',
			'===='   => 'h4',
			'==='    => 'h3',
			'=='     => 'h2',
			'='      => 'h1',
		];

		$md_types = empty( $convert ) ? $conversions : array_intersect( $conversions, $convert );

		if ( 2 === count( array_intersect( $md_types, [ 'em', 'strong' ] ) ) ) :
			$count = preg_match_all( '/(?:\*{3})([^\*{\3}]+)(?:\*{3})/', $text, $matches, PREG_PATTERN_ORDER );
			for ( $i = 0; $i < $count; $i++ ) {
				$text = str_replace(
					$matches[0][ $i ],
					sprintf( '<strong><em>%s</em></strong>', \esc_html( $matches[1][ $i ] ) ),
					$text
				);
			}
		endif;

		foreach ( $md_types as $type ) :
			switch ( $type ) :
				case 'strong':
					$count = preg_match_all( '/(?:\*{2})([^\*{\2}]+)(?:\*{2})/', $text, $matches, PREG_PATTERN_ORDER );

					for ( $i = 0; $i < $count; $i++ ) {
						$text = str_replace(
							$matches[0][ $i ],
							sprintf( '<strong>%s</strong>', \esc_html( $matches[1][ $i ] ) ),
							$text
						);
					}
					break;

				case 'em':
					$count = preg_match_all( '/(?:\*{1})([^\*{\1}]+)(?:\*{1})/', $text, $matches, PREG_PATTERN_ORDER );

					for ( $i = 0; $i < $count; $i++ ) {
						$text = str_replace(
							$matches[0][ $i ],
							sprintf( '<em>%s</em>', \esc_html( $matches[1][ $i ] ) ),
							$text
						);
					}
					break;

				case 'code':
					$count = preg_match_all( '/(?:`{1})([^`{\1}]+)(?:`{1})/', $text, $matches, PREG_PATTERN_ORDER );

					for ( $i = 0; $i < $count; $i++ ) {
						$text = str_replace(
							$matches[0][ $i ],
							sprintf( '<code>%s</code>', \esc_html( $matches[1][ $i ] ) ),
							$text
						);
					}
					break;

				case 'h6':
				case 'h5':
				case 'h4':
				case 'h3':
				case 'h2':
				case 'h1':
					$amount = filter_var( $type, FILTER_SANITIZE_NUMBER_INT );
					//* Considers word non-boundary. @TODO consider removing this?
					$expression = sprintf( '/(?:\={%1$s})\B([^\={\%1$s}]+)\B(?:\={%1$s})/', $amount );

					$count = preg_match_all( $expression, $text, $matches, PREG_PATTERN_ORDER );

					for ( $i = 0; $i < $count; $i++ ) {
						$text = str_replace(
							$matches[0][ $i ],
							sprintf( '<%1$s>%2$s</%1$s>', \esc_attr( $type ), \esc_html( $matches[1][ $i ] ) ),
							$text
						);
					}
					break;

				case 'a':
					$count = preg_match_all( '/(?:(?:\[{1})([^\]]+)(?:\]{1})(?:\({1})([^\)\(]+)(?:\){1}))/', $text, $matches, PREG_PATTERN_ORDER );

					$_string = $args['a_internal'] ? '<a href="%s">%s</a>' : '<a href="%s" target="_blank" rel="nofollow noreferrer noopener">%s</a>';

					for ( $i = 0; $i < $count; $i++ ) {
						$text = str_replace(
							$matches[0][ $i ],
							sprintf( $_string, \esc_url( $matches[2][ $i ], [ 'https', 'http' ] ), \esc_html( $matches[1][ $i ] ) ),
							$text
						);
					}
					break;

				default:
					break;
			endswitch;
		endforeach;

		return $text;
	}
}
