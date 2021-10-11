<?php
/**
 * @package The_SEO_Framework\Classes\Bridges\Ping
 * @subpackage The_SEO_Framework\Sitemap
 */

namespace The_SEO_Framework\Bridges;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2021 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\memo;

/**
 * Pings search engines.
 *
 * @since 4.0.0
 * @uses \The_SEO_Framework\Bridges\Sitemap.
 * @access protected
 * @final Can't be extended.
 */
final class Ping {

	/**
	 * The constructor, can't be initialized.
	 */
	private function __construct() { }

	/**
	 * Prepares a cronjob-based ping within 30 seconds of calling this.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now returns whether the cron engagement was successful.
	 * @since 4.1.2 Now registers before and after cron hooks. They should run subsequentially when successful.
	 * @see static::engage_pinging_retry_cron()
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function engage_pinging_cron() {

		$when = time() + 28;

		// Because WordPress sorts the actions, we can't be sure if they're scrambled. Therefore: skew timing.
		// Note that when WP_CRON_LOCK_TIMEOUT expires, the subsequent actions will run, regardless if previous was successful.
		return \wp_schedule_single_event( ++$when, 'tsf_sitemap_cron_hook_before' )
			&& \wp_schedule_single_event( ++$when, 'tsf_sitemap_cron_hook' )
			&& \wp_schedule_single_event( ++$when, 'tsf_sitemap_cron_hook_after' );
	}

	/**
	 * Retries a cronjob-based ping, via another hook.
	 *
	 * @since 4.1.2
	 * @uses \WP_CRON_LOCK_TIMEOUT, default 60 (seconds).
	 *
	 * @param array $args Optional. Array containing each separate argument to pass to the hook's callback function.
	 * @return bool True on success, false on failure.
	 */
	public static function engage_pinging_retry_cron( $args = [] ) {

		$when = (int) ( time() + min( \WP_CRON_LOCK_TIMEOUT, 60 ) + 1 );

		return \wp_schedule_single_event( $when, 'tsf_sitemap_cron_hook_retry', [ $args ] );
	}

	/**
	 * Retries pinging the search engines.
	 *
	 * @since 4.1.2
	 * @see static::engage_pinging_retry_cron()
	 * @uses static::ping_search_engines()
	 *
	 * @param array $args Array from ping hook.
	 */
	public static function retry_ping_search_engines( $args = [] ) {

		if ( empty( $args['id'] ) || 'base' !== $args['id'] ) return;

		static::ping_search_engines();
	}

	/**
	 * Pings search engines.
	 *
	 * @since 2.2.9
	 * @since 2.8.0 Only worked when the blog was not public...
	 * @since 3.1.0 Now allows one ping per language.
	 *              @uses $this->add_cache_key_suffix()
	 * @since 3.2.3 1. Now works as intended again.
	 *              2. Removed Easter egg.
	 * @since 4.0.0 Moved to \The_SEO_Framework\Bridges\Ping
	 * @since 4.0.2 Added action.
	 * @since 4.1.1 Added another action.
	 *
	 * @return void Early if blog is not public.
	 */
	public static function ping_search_engines() {

		$tsf = \tsf();

		if ( $tsf->get_option( 'site_noindex' ) || ! $tsf->is_blog_public() ) return;

		// Check for sitemap lock. If TSF's default sitemap isn't used, this should return false.
		if ( \The_SEO_Framework\Bridges\Sitemap::get_instance()->is_sitemap_locked() ) {
			static::engage_pinging_retry_cron( [ 'id' => 'base' ] );
			return;
		}
		$transient = $tsf->generate_cache_key( 0, '', 'ping' );

		// Uses legacy get_transient to bypass TSF's transient filters and prevent ping spam.
		if ( false === \get_transient( $transient ) ) {
			/**
			 * @since 4.1.1
			 * @param string $class The current class name.
			 */
			\do_action( 'the_seo_framework_before_ping_search_engines', static::class );

			if ( $tsf->get_option( 'ping_google' ) )
				static::ping_google();

			if ( $tsf->get_option( 'ping_bing' ) )
				static::ping_bing();

			/**
			 * @since 4.0.2
			 * @param string $class The current class name.
			 */
			\do_action( 'the_seo_framework_ping_search_engines', static::class );

			/**
			 * @since 2.5.1
			 * @param int $expiration The minimum time between two pings.
			 */
			$expiration = (int) \apply_filters( 'the_seo_framework_sitemap_throttle_s', HOUR_IN_SECONDS );

			// Uses legacy set_transient to bypass TSF's transient filters and prevent ping spam.
			\set_transient( $transient, 1, $expiration );
		}
	}

	/**
	 * Pings the main sitemap location to Google.
	 *
	 * @since 2.2.9
	 * @since 3.1.0 Updated ping URL. Old one still worked, too.
	 * @since 4.0.0 Moved to \The_SEO_Framework\Bridges\Ping
	 * @since 4.0.3 Google now redirects to HTTPS. Updated URL scheme to accomodate.
	 * @since 4.1.2 Now fetches WP Sitemaps' index URL when it's enabled.
	 * @link https://developers.google.com/search/docs/advanced/crawling/ask-google-to-recrawl
	 */
	public static function ping_google() {

		$url = static::get_ping_url();

		if ( ! $url ) return;

		\wp_safe_remote_get(
			'https://www.google.com/ping?sitemap=' . rawurlencode( $url ),
			[ 'timeout' => 3 ]
		);
	}

	/**
	 * Pings the main sitemap location to Bing.
	 *
	 * @since 2.2.9
	 * @since 3.2.3 Updated ping URL. Old one still worked, too.
	 * @since 4.0.0 Moved to \The_SEO_Framework\Bridges\Ping
	 * @since 4.0.3 Bing now redirects to HTTPS. Updated URL scheme to accomodate.
	 * @since 4.1.2 Now fetches WP Sitemaps' index URL when it's enabled.
	 * @link https://www.bing.com/webmasters/help/Sitemaps-3b5cf6ed
	 */
	public static function ping_bing() {

		$url = static::get_ping_url();

		if ( ! $url ) return;

		\wp_safe_remote_get(
			'https://www.bing.com/ping?sitemap=' . rawurlencode( $url ),
			[ 'timeout' => 3 ]
		);
	}

	/**
	 * Return the base sitemap's ping URL.
	 * Memoizes the return value.
	 *
	 * @since 4.2.0
	 *
	 * @return string The ping URL. Empty string on failure.
	 */
	public static function get_ping_url() {
		return memo() ?? memo(
			(
				\tsf()->use_core_sitemaps()
					? \get_sitemap_url( 'index' )
					: \The_SEO_Framework\Bridges\Sitemap::get_instance()->get_expected_sitemap_endpoint_url()
			)
			?: ''
		);
	}
}
