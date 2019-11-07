<?php
/**
 * @package The_SEO_Framework\Classes\Bridges\Ping
 * @subpackage The_SEO_Framework\Sitemap
 */

namespace The_SEO_Framework\Bridges;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Pings search engines.
 *
 * @since 4.0.0
 * @uses \The_SEO_Framework\Bridges\Sitemap.
 * @access protected
 * @final Can't be extended.
 */
final class Ping {
	use \The_SEO_Framework\Traits\Enclose_Stray_Private;

	/**
	 * The constructor, can't be initialized.
	 */
	private function __construct() { }

	/**
	 * Prepares a CRON-based ping within 30 seconds of calling this.
	 *
	 * @since 4.0.0
	 */
	public static function engage_pinging_cron() {
		\wp_schedule_single_event( time() + 30, 'tsf_sitemap_cron_hook' );
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
	 *
	 * @return void Early if blog is not public.
	 */
	public static function ping_search_engines() {

		$tsf = \the_seo_framework();

		if ( $tsf->get_option( 'site_noindex' ) || ! $tsf->is_blog_public() ) return;

		$transient = $tsf->generate_cache_key( 0, '', 'ping' );

		//* NOTE: Use legacy get_transient to bypass TSF's transient filters and prevent ping spam.
		if ( false === \get_transient( $transient ) ) {
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

			//* @NOTE: Using legacy set_transient to bypass TSF's transient filters and prevent ping spam.
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
	 * @link https://support.google.com/webmasters/answer/6065812?hl=en
	 */
	public static function ping_google() {
		$pingurl = 'https://www.google.com/ping?sitemap=' . rawurlencode(
			\The_SEO_Framework\Bridges\Sitemap::get_instance()->get_expected_sitemap_endpoint_url()
		);
		\wp_safe_remote_get( $pingurl, [ 'timeout' => 3 ] );
	}

	/**
	 * Pings the main sitemap location to Bing.
	 *
	 * @since 2.2.9
	 * @since 3.2.3 Updated ping URL. Old one still worked, too.
	 * @since 4.0.0 Moved to \The_SEO_Framework\Bridges\Ping
	 * @since 4.0.3 Bing now redirects to HTTPS. Updated URL scheme to accomodate.
	 * @link https://www.bing.com/webmaster/help/how-to-submit-sitemaps-82a15bd4
	 */
	public static function ping_bing() {
		$pingurl = 'https://www.bing.com/ping?sitemap=' . rawurlencode(
			\The_SEO_Framework\Bridges\Sitemap::get_instance()->get_expected_sitemap_endpoint_url()
		);
		\wp_safe_remote_get( $pingurl, [ 'timeout' => 3 ] );
	}
}
