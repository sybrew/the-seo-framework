<?php
/**
 * @package The_SEO_Framework\Classes\Data\Plugin
 * @subpackage The_SEO_Framework\Data\Plugin
 */

namespace The_SEO_Framework\Data;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function The_SEO_Framework\is_headless;

use The_SEO_Framework\Traits\Property_Refresher;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 - 2026 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * @since 5.0.0
 * @access protected
 *         Use tsf()->data()->plugin() instead.
 *
 * @NOTE: All static:: calls within this class are intentional due to Property_Refresher trait.
 */
class Plugin {
	use Property_Refresher;

	/**
	 * @since 5.0.0
	 * @var ?array Holds 'all' TSF's options/settings.
	 * @uses \THE_SEO_FRAMEWORK_SITE_OPTIONS
	 */
	private static $options_memo;

	/**
	 * @since 5.0.0
	 * @var ?array Holds 'all' TSF's site cache.
	 * @uses \THE_SEO_FRAMEWORK_SITE_CACHE
	 */
	private static $site_cache_memo;

	/**
	 * Determines whether settings writes must wait for a verified database upgrade.
	 *
	 * @since 5.2.2
	 * @internal TSF's settings writers use this check to defer writes until the database version matches.
	 *
	 * @return bool Whether settings writes are blocked.
	 */
	public static function is_upgrade_pending() {
		return \THE_SEO_FRAMEWORK_DB_VERSION !== \the_seo_framework_db_version();
	}

	/**
	 * Sanitizes ordinary settings writes once the database version matches.
	 *
	 * @hook "sanitize_option_ . \THE_SEO_FRAMEWORK_SITE_OPTIONS" 10
	 * @since 5.2.2
	 * @internal Defers pending upgrade writes to the option-specific pre-update guard.
	 *
	 * @param mixed  $value  The proposed settings.
	 * @param string $option The option name.
	 * @return mixed The value for WordPress's pre-update filters.
	 */
	public static function sanitize_option_update( $value, $option ) {
		return self::is_upgrade_pending()
			? $value
			: Filter\Plugin::filter_settings_update( $value, $option );
	}

	/**
	 * Preserves the stored settings while the database upgrade is pending.
	 *
	 * @hook "pre_update_option_ . \THE_SEO_FRAMEWORK_SITE_OPTIONS" PHP_INT_MAX
	 * @since 5.2.2
	 * @internal Relies on the previous value WordPress supplies to its pre-update filter to cancel a rejected write.
	 *
	 * @param mixed $value    The proposed settings.
	 * @param mixed $previous The stored settings supplied by WordPress.
	 * @return mixed The value to save.
	 */
	public static function guard_option_update( $value, $previous ) {
		return self::is_upgrade_pending() ? $previous : $value;
	}

	/**
	 * Flushes all option runtime cache.
	 *
	 * @hook "add_option_ . \THE_SEO_FRAMEWORK_SITE_OPTIONS" 0
	 * @hook "update_option_ . \THE_SEO_FRAMEWORK_SITE_OPTIONS" 0
	 * @hook "delete_option_ . \THE_SEO_FRAMEWORK_SITE_OPTIONS" 0
	 * @since 5.0.0
	 * @since 5.2.2 1. Now refreshes the settings and post type archive caches after successful additions, updates, and deletions of the settings option.
	 *              2. Now runs on admin, front-end, and REST requests.
	 */
	public static function flush_cache() {

		static::refresh_static_properties();
		// PTA is stored in the default plugin options.
		Plugin\PTA::refresh_static_properties();
	}

	/**
	 * Returns selected option. Null on failure.
	 *
	 * @since 2.2.2
	 * @since 2.8.2 No longer decodes entities on request.
	 * @since 3.1.0 Now uses the filterable call when caching is disabled.
	 * @since 4.2.0 Now supports an option index as a $key.
	 * @since 5.0.0 1. Removed $use_cache; the cache is now dynamically updated.
	 *              2. $key is now variadic. Additional variables allow you to dig deeper in the cache.
	 *              3. Moved from `\The_SEO_Framework\Load`.
	 * @uses \THE_SEO_FRAMEWORK_SITE_OPTIONS
	 *
	 * @param string ...$key Option name. Additional parameters will try get sub-values of the array.
	 *                       When empty, it'll return all options. You should use get_options() instead.
	 * @return mixed The TSF option value. Null when not found.
	 */
	public static function get_option( ...$key ) {

		$option = self::$options_memo ?? self::get_options();

		foreach ( $key as $k )
			$option = $option[ $k ] ?? null;

		return $option ?? Plugin\Deprecated::get_deprecated_option( ...$key );
	}

	/**
	 * Returns option array. Does not merge with defaults.
	 *
	 * @since 5.0.0
	 * @since 5.2.2 Now falls back to defaults if the saved settings are not an array.
	 *
	 * @return array Options.
	 */
	public static function get_options() {

		if ( isset( self::$options_memo ) )
			return self::$options_memo;

		static::register_automated_refresh( 'options_memo' );

		$is_headless = is_headless( 'settings' );
		$options     = $is_headless ? Plugin\Setup::get_default_options() : self::get_stored_options();

		/**
		 * @since 2.0.0
		 * @since 4.1.4 1. Now considers headlessness.
		 *              2. Now returns a 3rd parameter: Boolean $headless.
		 *
		 * @param array  $settings The settings
		 * @param string $setting  The settings name.
		 * @param bool   $headless Whether the options are headless.
		 */
		return self::$options_memo = \apply_filters(
			'the_seo_framework_get_options',
			$options,
			\THE_SEO_FRAMEWORK_SITE_OPTIONS,
			$is_headless,
		);
	}

	/**
	 * Reads the settings before TSF applies its settings filter, falling back to defaults for an empty or non-array value.
	 *
	 * @since 5.2.2
	 *
	 * @return array The stored settings or defaults.
	 */
	private static function get_stored_options() {

		$stored = \get_option( \THE_SEO_FRAMEWORK_SITE_OPTIONS );

		return $stored && \is_array( $stored ) ? $stored : Plugin\Setup::get_default_options();
	}

	/**
	 * Updates selected options.
	 *
	 * @since 2.9.0
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 * @since 5.0.2 Now falls back to default for merge: If the option disappears for some reason, we won't crash.
	 * @since 5.1.0 No longer considers headlessness. The headless filters are ought
	 *              to stay in place throughout the request, affecting `get_option()`.
	 * @since 5.2.2 1. Now uses the same WordPress settings sanitization in admin, front-end, and REST requests after TSF initializes.
	 *              2. Now merges changes into defaults when the saved settings value is a non-array.
	 *              3. Now refreshes settings and post type archive meta on WordPress's post-write actions.
	 *              4. Now returns false while a database upgrade is pending.
	 *
	 * @param string|array $option The option key, or an array of key and value pairs.
	 * @param mixed        $value  The option value. Ignored when $option is an array.
	 * @return bool True on successful update, false otherwise.
	 */
	public static function update_option( $option, $value = '' ) {

		if ( self::is_upgrade_pending() )
			return false;

		return \update_option(
			\THE_SEO_FRAMEWORK_SITE_OPTIONS,
			array_merge(
				self::get_stored_options(),
				\is_array( $option ) ? $option : [ $option => $value ],
			),
			true, // autoload
		);
	}

	/**
	 * Retrieves caching options.
	 *
	 * @since 5.0.0
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
	 * @since 5.0.0
	 *
	 * @return array Options.
	 */
	public static function get_site_caches() {

		if ( isset( static::$site_cache_memo ) )
			return static::$site_cache_memo;

		static::register_automated_refresh( 'site_cache_memo' );

		return static::$site_cache_memo =
			   \get_option( \THE_SEO_FRAMEWORK_SITE_CACHE )
			?: Plugin\Setup::get_default_site_caches();
	}

	/**
	 * Updates static caching option.
	 * Can return false if cache is unchanged.
	 *
	 * @since 5.0.0
	 * @since 5.0.2 Now falls back to default for merge: If the option disappears for some reason, we won't crash.
	 *
	 * @param string|array $cache The cache key, or an array (of arrays) of key and value pairs.
	 * @param mixed        $value The cache value. Ignored when $cache is an array.
	 * @return bool True on success, false on failure.
	 */
	public static function update_site_cache( $cache, $value = '' ) {

		// Get the latest known revision from the database.
		$site_cache = array_merge(
			\get_option( \THE_SEO_FRAMEWORK_SITE_CACHE ) ?: Plugin\Setup::get_default_site_caches(),
			\is_array( $cache ) ? $cache : [ $cache => $value ],
		);

		static::$site_cache_memo = null;

		return \update_option(
			\THE_SEO_FRAMEWORK_SITE_CACHE,
			$site_cache,
			true, // autoload
		);
	}

	/**
	 * Deletes static caching option indexes.
	 *
	 * @since 5.0.0
	 * @since 5.0.2 Now falls back to default for unset: If the option disappears for some reason, we won't crash.
	 *
	 * @param string|string[] $cache The cache key, or an array of keys to delete.
	 * @return bool True on success, false on failure.
	 */
	public static function delete_site_cache( $cache ) {

		$site_cache = \get_option( \THE_SEO_FRAMEWORK_SITE_CACHE ) ?: Plugin\Setup::get_default_site_caches();

		foreach ( (array) $cache as $key )
			unset( $site_cache[ $key ] );

		static::$site_cache_memo = null;

		return \update_option(
			\THE_SEO_FRAMEWORK_SITE_CACHE,
			$site_cache,
			true, // autoload
		);
	}
}
