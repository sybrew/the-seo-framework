<?php
/**
 * @package The_SEO_Framework\Classes\Helper\Query\Exclusion
 * @subpackage The_SEO_Framework\Query
 */

namespace The_SEO_Framework\Helper\Query;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\is_headless;

use \The_SEO_Framework\Data;
use \The_SEO_Framework\Helper\{
	Post_Type,
	Taxonomy,
};

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
 * Excludes stuff from the query.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->query()->exclusion() instead.
 */
class Exclusion {

	/**
	 * Clears static excluded IDs cache.
	 *
	 * @hook wp_insert_post 10
	 * @hook attachment_updated 10
	 * @hook "update_option_ . THE_SEO_FRAMEWORK_SITE_OPTIONS" 10
	 * @since 5.0.0
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function clear_excluded_post_ids_cache() {
		return Data\Plugin::update_site_cache( 'excluded_ids', [] );
	}

	/**
	 * Builds and returns the excluded post IDs.
	 *
	 * Memoizes the database request.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 Now no longer crashes on database errors.
	 * @since 4.1.4 1. Now tests against post type exclusions.
	 *              2. Now considers headlessness. This method runs only on the front-end.
	 * @since 5.0.0 1. Now uses the static cache methods instead of non-expiring-transients.
	 *              2. Moved from `\The_SEO_Framework\Load`.
	 *
	 * @return array {
	 *     The excluded post IDs.
	 *
	 *     @type int[] $archive The excluded post IDs for the archive.
	 *     @type int[] $search  The excluded post IDs for the search.
	 * }
	 */
	public static function get_excluded_ids_from_cache() {

		if ( is_headless( 'meta' ) )
			return [
				'archive' => '',
				'search'  => '',
			];

		$cache = Data\Plugin::get_site_cache( 'excluded_ids' );

		if ( isset( $cache['archive'], $cache['search'] ) ) return $cache;

		global $wpdb;

		$supported_post_types = Post_Type::get_all_supported();
		$public_post_types    = Post_Type::get_all_public();

		$join  = '';
		$where = '';
		if ( $supported_post_types !== $public_post_types ) {
			// Post types can be registered arbitrarily through other plugins, even manually by non-super-admins. Prepare!
			$post_type__in = "'" . implode( "','", array_map( 'esc_sql', $supported_post_types ) ) . "'";

			// This is as fast as I could make it. Yes, it uses IN, but only on a (tiny) subset of data.
			$join  = "LEFT JOIN {$wpdb->posts} ON {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID";
			$where = "AND {$wpdb->posts}.post_type IN ($post_type__in)";
		}

		// Two separated equals queries are faster than a single IN with 'meta_key'.
		// phpcs:disable, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- We prepared our whole lives.
		$cache = [
			'archive' => $wpdb->get_results(
				"SELECT post_id, meta_value FROM $wpdb->postmeta $join WHERE meta_key = 'exclude_from_archive' $where",
			),
			'search'  => $wpdb->get_results(
				"SELECT post_id, meta_value FROM $wpdb->postmeta $join WHERE meta_key = 'exclude_local_search' $where",
			),
		];
		// phpcs:enable, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		foreach ( [ 'archive', 'search' ] as $type ) {
			array_walk(
				$cache[ $type ],
				function ( &$v ) {
					if ( isset( $v->meta_value, $v->post_id ) && $v->meta_value ) {
						$v = (int) $v->post_id;
					} else {
						$v = false;
					}
				}
			);
			$cache[ $type ] = array_filter( $cache[ $type ] );
		}

		Data\Plugin::update_site_cache( 'excluded_ids', $cache );

		return $cache;
	}
}
