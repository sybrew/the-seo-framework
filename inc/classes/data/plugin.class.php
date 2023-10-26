<?php
/**
 * @package The_SEO_Framework\Classes\Data\Plugin
 * @subpackage The_SEO_Framework\Data\Plugin
 */

namespace The_SEO_Framework\Data;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\is_headless;

use \The_SEO_Framework\Traits\Property_Refresher;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds a collection of data interface methods for TSF.
 *
 * @since 4.3.0
 * @access protected
 *         Use tsf()->data()->plugin() instead.
 */
class Plugin {
	use Property_Refresher;

	/**
	 * @since 4.3.0
	 * @var array Holds 'all' TSF's options/settings. Updates in real time.
	 * @uses \THE_SEO_FRAMEWORK_SITE_OPTIONS
	 */
	private static $options_memo;

	/**
	 * @since 4.3.0
	 * @var array Holds 'all' TSF's site cache. Updates in real time.
	 * @uses \THE_SEO_FRAMEWORK_SITE_CACHE
	 */
	private static $site_cache_memo;

	/**
	 * Returns selected option. Null on failure.
	 *
	 * @since 2.2.2
	 * @since 2.8.2 No longer decodes entities on request.
	 * @since 3.1.0 Now uses the filterable call when caching is disabled.
	 * @since 4.2.0 Now supports an option index as a $key.
	 * @since 4.3.0 1. Removed $use_cache; the cache is now dynamically updated.
	 *              2. $key is now variadic. Additional variables allow you to dig deeper in the cache.
	 *              3. Moved to `The_SEO_Framework\Data`.
	 * @uses \THE_SEO_FRAMEWORK_SITE_OPTIONS
	 *
	 * @param string ...$key Option name. Additional parameters will try get subvalues of the array.
	 *                       When empty, it'll return all options. You should use get_options() instead.
	 * @return mixed The TSF option value. Null when not found.
	 */
	public static function get_option( ...$key ) {

		$option = static::$options_memo ?? static::get_options();

		foreach ( $key as $k )
			$option = $option[ $k ] ?? null;

		return $option ?? Plugin\Deprecated::get_deprecated_option( ...$key );
	}

	/**
	 * Returns option array. Does not merge with defaults.
	 *
	 * @since 4.3.0
	 *
	 * @return array Options.
	 */
	public static function get_options() {

		if ( isset( static::$options_memo ) )
			return static::$options_memo;

		$is_headless = is_headless( 'settings' );

		static::register_automated_refresh( 'options' );

		/**
		 * @since 2.0.0
		 * @since 4.1.4 1. Now considers headlessness.
		 *              2. Now returns a 3rd parameter: boolean $headless.
		 *
		 * @param array  $settings The settings
		 * @param string $setting  The settings name.
		 * @param bool   $headless Whether the options are headless.
		 */
		return static::$options_memo = \apply_filters_ref_array(
			'the_seo_framework_get_options',
			[
				$is_headless
					? Plugin\Setup::get_default_options()
					: \get_option( \THE_SEO_FRAMEWORK_SITE_OPTIONS ),
				\THE_SEO_FRAMEWORK_SITE_OPTIONS,
				$is_headless,
			]
		);
	}

	/**
	 * Updates options. Also updates the option cache if the settings aren't headless.
	 *
	 * @since 2.9.0
	 * @since 4.3.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param string|array $option The option key, or an array of key and value pairs.
	 * @param mixed        $value  The option value. Ignored when $option is an array.
	 * @return bool True on succesful update, false otherwise.
	 */
	public static function update_option( $option, $value = '' ) {

		// Get the latest known revision from the database.
		$options = array_merge(
			\get_option( \THE_SEO_FRAMEWORK_SITE_OPTIONS ),
			\is_array( $option ) ? $option : [ $option => $value ]
		);

		// The current request is still headless -- so do not update the state.
		// The next request may have filtered this value, or the update was blocked.
		if ( ! is_headless( 'settings' ) )
			static::$options_memo = null;

		Plugin\PTA::flush_cache();

		return \update_option( \THE_SEO_FRAMEWORK_SITE_OPTIONS, $options );
	}

	/**
	 * Retrieves caching options.
	 *
	 * @since 4.3.0
	 *
	 * @param string $key The cache key.
	 * @return mixed Cache value on success, $default if non-existent.
	 */
	public static function get_site_cache( $key ) {
		return (
			static::$site_cache_memo ?? static::get_site_caches()
		)[ $key ] ?? null;
	}

	/**
	 * Returns option array. Does not merge with defaults.
	 *
	 * @since 4.3.0
	 *
	 * @return array Options.
	 */
	public static function get_site_caches() {

		if ( isset( static::$site_cache_memo ) )
			return static::$site_cache_memo;

		static::register_automated_refresh( 'site_cache' );

		return static::$site_cache_memo = \get_option( \THE_SEO_FRAMEWORK_SITE_CACHE, [] ) ?: [];
	}

	/**
	 * Updates static caching option.
	 * Can return false if cache is unchanged.
	 *
	 * @since 4.3.0
	 *
	 * @param string|array $cache The cache key, or an array (of arrays) of key and value pairs.
	 * @param mixed        $value The cache value. Ignored when $cache is an array.
	 * @return bool True on success, false on failure.
	 */
	public static function update_site_cache( $cache, $value = '' ) {

		// Get the latest known revision from the database.
		$site_cache = array_merge(
			\get_option( \THE_SEO_FRAMEWORK_SITE_CACHE ),
			\is_array( $cache ) ? $cache : [ $cache => $value ]
		);

		static::$site_cache_memo = $site_cache;

		return \update_option( \THE_SEO_FRAMEWORK_SITE_CACHE, $site_cache );
	}

	/**
	 * Deletes static caching option indexes.
	 *
	 * @since 4.3.0
	 *
	 * @param string|string[] $cache The cache key, or an array of keys to delete.
	 * @return bool True on success, false on failure.
	 */
	public static function delete_site_cache( $cache ) {

		$site_cache = \get_option( \THE_SEO_FRAMEWORK_SITE_CACHE );

		foreach ( (array) $cache as $key )
			unset( $site_cache[ $key ] );

		static::$site_cache_memo = $site_cache;

		return \update_option( \THE_SEO_FRAMEWORK_SITE_CACHE, $site_cache );
	}
}
