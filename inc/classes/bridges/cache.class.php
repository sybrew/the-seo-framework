<?php
/**
 * @package The_SEO_Framework\Classes\Bridges\Cache
 */

namespace The_SEO_Framework\Bridges;

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

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * Handles the caching interface.
 *
 * @since 4.2.9
 * @access public
 * @internal
 * @final Can't be extended.
 */
final class Cache {

	/**
	 * @since 4.2.9
	 * @access private
	 *         Immutable! Use constant `THE_SEO_FRAMEWORK_DISABLE_TRANSIENTS` instead.
	 * @see tsf()->init_debug_vars() which can set this to 'false'.
	 * @var bool Whether transients are enabled.
	 */
	public static $use_transients = true;

	/**
	 * Set the value of the transient.
	 *
	 * Prevents setting of transients when they're disabled.
	 *
	 * @since 4.2.9
	 *
	 * @param string $transient  Transient name. Expected to not be SQL-escaped.
	 * @param string $value      Transient value. Expected to not be SQL-escaped.
	 * @param int    $expiration Transient expiration date, optional. Expected to not be SQL-escaped.
	 * @return bool True is value is set, false on failure.
	 */
	public static function set_transient( $transient, $value, $expiration = 0 ) {
		return static::$use_transients && \set_transient( $transient, $value, $expiration );
	}

	/**
	 * Get the value of the transient.
	 *
	 * If the transient does not exists, does not have a value or has expired,
	 * or transients have been disabled through a constant, then the transient
	 * will be false.
	 *
	 * N.B. not all transient settings make use of this function, bypassing the constant check.
	 *
	 * @since 4.2.9
	 *
	 * @param string $transient Transient name. Expected to not be SQL-escaped.
	 * @return mixed|bool Value of the transient. False on failure or non existing transient.
	 */
	public static function get_transient( $transient ) {
		return static::$use_transients ? \get_transient( $transient ) : false;
	}

	/**
	 * Returns a unique cache key suffix per blog and language.
	 *
	 * @since 4.2.9
	 *
	 * @param string $key The cache key.
	 * @return string The cache key with blog ID and locale appended.
	 */
	public static function build_unique_cache_key_suffix( $key ) {

		// Do not memoize: May change at runtime.
		$locale = \get_locale();

		return "{$key}_{$GLOBALS['blog_id']}_{$locale}";
	}

	/**
	 * Clears static excluded IDs cache.
	 *
	 * @since 4.2.9
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function clear_excluded_post_ids_cache() {
		return \tsf()->update_static_cache( 'excluded_ids', false );
	}

	/**
	 * Refreshes sitemaps on post change.
	 *
	 * @since 4.2.9
	 * @access private
	 *
	 * @param int $post_id The Post ID that has been updated.
	 * @return bool True on success, false on failure.
	 */
	public static function _refresh_sitemap_on_post_change( $post_id ) {

		// Don't refresh sitemap on revision.
		if ( ! $post_id || \wp_is_post_revision( $post_id ) ) return false;

		return Sitemap::refresh_sitemaps();
	}

	/**
	 * Checks whether the permalink structure is updated.
	 *
	 * @since 4.2.9
	 * @access private
	 *
	 * @return bool Whether if sitemap transient is deleted.
	 */
	public static function _refresh_sitemap_transient_permalink_updated() {

		if (
			   ( isset( $_POST['permalink_structure'] ) || isset( $_POST['category_base'] ) )
			&& \check_admin_referer( 'update-permalink' )
		) {
				return Sitemap::refresh_sitemaps();
		}

		return false;
	}

	/**
	 * Clears sitemap transients.
	 *
	 * @since 4.2.9
	 */
	public static function clear_sitemap_transients() {

		$sitemap = Sitemap::get_instance();

		foreach ( $sitemap->get_sitemap_endpoint_list() as $id => $data ) {
			$transient = $sitemap->get_transient_key( $id );

			if ( $transient )
				\delete_transient( $transient );
		}

		/**
		 * @since 4.2.9
		 */
		\do_action( 'the_seo_framework_cleared_sitemap_transients' );

		/**
		 * @since 3.1.0
		 * @since 4.2.9 Soft deprecated. Use action 'the_seo_framework_cleared_sitemap_transients' instead.
		 * @todo 4.3.0 deprecate, use do_action_deprecated.
		 *
		 * @param string $type    The flush type. Comes in handy when you use a catch-all function.
		 * @param int    $id      The post, page or TT ID. Defaults to tsf()->get_the_real_ID().
		 * @param array  $args    Additional arguments. They can overwrite $type and $id.
		 * @param array  $success Whether the action cleared. Set to always be true since deprecation.
		 */
		\do_action(
			'the_seo_framework_delete_cache_sitemap',
			[
				'sitemap',
				0,
				[ 'type' => 'sitemap' ],
				[ true ],
			],
			'4.2.9 of The SEO Framework',
			'the_seo_framework_cleared_sitemap_transients'
		);
	}
}
