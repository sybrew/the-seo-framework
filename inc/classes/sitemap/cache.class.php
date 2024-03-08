<?php
/**
 * @package The_SEO_Framework\Classes\Sitemap\Cache
 * @subpackage The_SEO_Framework\Sitemap
 */

namespace The_SEO_Framework\Sitemap;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Data;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Handles the data and caching interface for sitemaps.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->sitemap()->cache() instead.
 */
class Cache {

	/**
	 * Returns a unique cache key suffix per blog and language.
	 *
	 * @since 5.0.0
	 *
	 * @param string $key The cache key.
	 * @return string The cache key with blog ID and locale appended.
	 */
	public static function build_sitemap_cache_key( $key ) {
		return "{$key}_{$GLOBALS['blog_id']}_" . \get_locale();
	}

	/**
	 * Clears sitemap transients.
	 *
	 * @since 5.0.0
	 */
	public static function clear_sitemap_caches() {

		foreach ( Registry::get_sitemap_endpoint_list() as $id => $data ) {
			$transient = static::get_sitemap_cache_key( $id );

			if ( $transient )
				\delete_transient( $transient );
		}

		/**
		 * @since 5.0.0
		 */
		\do_action( 'the_seo_framework_cleared_sitemap_transients' );

		/**
		 * @since 3.1.0
		 * @since 5.0.0 Deprecated. Use action 'the_seo_framework_cleared_sitemap_transients' instead.
		 *
		 * @param string $type    The flush type. Comes in handy when you use a catch-all function.
		 * @param int    $id      The post, page or TT ID. Defaults to Query::get_the_real_id().
		 * @param array  $args    Additional arguments. They can overwrite $type and $id.
		 * @param array  $success Whether the action cleared. Set to always be true since deprecation.
		 */
		\do_action_deprecated(
			'the_seo_framework_delete_cache_sitemap',
			[
				'sitemap',
				0,
				[ 'type' => 'sitemap' ],
				[ true ],
			],
			'5.0.0 of The SEO Framework',
			'the_seo_framework_cleared_sitemap_transients',
		);
	}

	/**
	 * Tells whether sitemap caching is enabled by user.
	 *
	 * @since 5.0.0
	 *
	 * @return bool
	 */
	public static function is_sitemap_cache_enabled() {
		return (bool) Data\Plugin::get_option( 'cache_sitemap' );
	}

	/**
	 * Returns the transient prefix.
	 * We're using a function instead of a variable or constant, because variables can be overwritten (pre PHP 8.1),
	 * and constants cannot be deprecated via the static deprecator (must use `defined( get_class( ... ), '::constant' )`).
	 *
	 * @since 5.0.5
	 *
	 * @return string The transient prefix of the sitemap.
	 */
	public static function get_transient_prefix() {
		return 'tsf_sitemap_';
	}

	/**
	 * Returns the sitemap's storage transient name.
	 *
	 * @since 5.0.0
	 *
	 * @param string $sitemap_id The sitemap ID.
	 * @return string|false The sitemap transient store key.
	 */
	public static function get_sitemap_cache_key( $sitemap_id ) {

		$ep_list = Registry::get_sitemap_endpoint_list();

		if ( empty( $ep_list[ $sitemap_id ] ) ) return false;

		$cache_key = $ep_list[ $sitemap_id ]['cache_id'] ?? $sitemap_id;

		return static::build_sitemap_cache_key( static::get_transient_prefix() . $cache_key );
	}

	/**
	 * Stores the sitemap in transient cache.
	 *
	 * @since 5.0.0
	 *
	 * @param string $content    The sitemap content
	 * @param string $sitemap_id The sitemap ID.
	 * @param int    $expiration The sitemap's cache timeout.
	 * @return bool True on success, false on failure.
	 */
	public static function cache_sitemap_content( $content, $sitemap_id = '', $expiration = \WEEK_IN_SECONDS ) {

		$transient_key = static::get_sitemap_cache_key( $sitemap_id );

		if ( ! $transient_key ) return false;

		return \set_transient( $transient_key, $content, $expiration );
	}

	/**
	 * Returns the sitemap from transient cache.
	 *
	 * @since 5.0.0
	 *
	 * @param string $sitemap_id The sitemap ID.
	 * @return string|false The sitemap from cache. False is not set.
	 */
	public static function get_cached_sitemap_content( $sitemap_id = '' ) {

		$transient_key = static::get_sitemap_cache_key( $sitemap_id );

		if ( ! $transient_key ) return false;

		return \get_transient( $transient_key );
	}
}
