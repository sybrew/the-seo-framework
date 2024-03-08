<?php
/**
 * @package The_SEO_Framework\Classes\Sitemap\Cache
 * @subpackage The_SEO_Framework\Sitemap
 */

namespace The_SEO_Framework\Sitemap;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Helper;

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
 * Handles the locking mechanics for sitemaps.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->sitemap()->lock() instead.
 */
class Lock {

	/**
	 * Returns the sitemap's lock cache ID.
	 *
	 * @since 4.1.2
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Bridges\Sitemap`.
	 *
	 * @param string $sitemap_id The sitemap ID.
	 * @return string|false The sitemap lock key. False when key is invalid.
	 */
	public static function get_lock_key( $sitemap_id ) {

		$ep_list = Registry::get_sitemap_endpoint_list();

		if ( ! isset( $ep_list[ $sitemap_id ] ) ) return false;

		$lock_id = $ep_list[ $sitemap_id ]['lock_id'] ?? $sitemap_id;

		return Cache::build_sitemap_cache_key( Cache::get_transient_prefix() . 'lock' ) . "_{$lock_id}";
	}

	/**
	 * Outputs a '503: Service Unavailable' header and no-cache headers.
	 *
	 * @since 4.1.2
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Bridges\Sitemap`.
	 *
	 * @param string $sitemap_id The sitemap ID.
	 */
	public static function output_locked_header( $sitemap_id ) {

		Helper\Headers::clean_response_header();

		\status_header( 503 );
		\nocache_headers();

		$lock_key = static::get_lock_key( $sitemap_id );
		$timeout  = $lock_key ? \get_transient( $lock_key ) : false;

		if ( $timeout ) {
			printf(
				/* translators: %d = number of seconds */
				\esc_html__( 'Sitemap is locked for %d seconds. Try again later.', 'autodescription' ),
				(int) ( $timeout - time() ),
			);
		} else {
			\esc_html_e( 'Sitemap is locked temporarily. Try again later.', 'autodescription' );
		}

		echo "\n";
		exit;
	}

	/**
	 * Locks a sitemap for the current blog & locale and $sitemap_id, preferably
	 * at least as long as PHP is allowed to run.
	 *
	 * @since 4.1.2
	 * @since 4.2.1 Now considers "unlimited" execution time (0) that'd've prevented locks altogether.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Bridges\Sitemap`.
	 *
	 * @param string $sitemap_id The sitemap ID.
	 * @return bool True on success, false on failure.
	 */
	public static function lock_sitemap( $sitemap_id ) {

		$lock_key = static::get_lock_key( $sitemap_id );

		if ( ! $lock_key ) return false;

		$ini_max_execution_time = (int) ini_get( 'max_execution_time' );

		if ( 0 === $ini_max_execution_time ) { // Unlimited. Let's still put a limit on the lock.
			$timeout = 3 * \MINUTE_IN_SECONDS;
		} else {
			// This is rather at most as PHP will run. However, 3 minutes to generate a sitemap is already ludicrous.
			$timeout = (int) min( $ini_max_execution_time, 3 * \MINUTE_IN_SECONDS );
		}

		return \set_transient( $lock_key, time() + $timeout, $timeout );
	}

	/**
	 * Unlocks a sitemap for the current blog & locale and $sitemap_id.
	 *
	 * @since 4.1.2
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Bridges\Sitemap`.
	 *
	 * @param string $sitemap_id The sitemap ID.
	 * @return bool True on success, false on failure.
	 */
	public static function unlock_sitemap( $sitemap_id ) {

		$lock_key = static::get_lock_key( $sitemap_id );

		return $lock_key ? \delete_transient( $lock_key ) : false;
	}

	/**
	 * Tells whether a sitemap is locked for the current blog & locale and $sitemap_id.
	 *
	 * @since 4.1.2
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Bridges\Sitemap`.
	 *
	 * @param string $sitemap_id The sitemap ID.
	 * @return bool|int False if not locked, the lock UNIX release time otherwise.
	 */
	public static function is_sitemap_locked( $sitemap_id ) {

		$lock_key = static::get_lock_key( $sitemap_id );

		return $lock_key ? \get_transient( $lock_key ) : false;
	}
}
