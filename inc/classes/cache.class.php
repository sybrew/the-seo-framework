<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Cache
 * @subpackage The_SEO_Framework\Cache
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2021 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\Cache
 *
 * Generates, stores and deletes common transients.
 *
 * @since 2.8.0
 */
class Cache extends Site_Options {

	/**
	 * Initializes admin caching actions.
	 *
	 * @since 2.8.0
	 * @action init priority 1
	 * @see $this->init_admin_actions();
	 */
	public function init_admin_caching_actions() {

		$this->init_post_cache_actions();

		// Delete Sitemap transient on permalink structure change.
		\add_action( 'load-options-permalink.php', [ $this, 'delete_sitemap_transient_permalink_updated' ], 20 );

		\add_action( 'activated_plugin', [ $this, 'set_plugin_check_caches' ] );
	}

	/**
	 * Deletes Sitemap and Description transients on post publish/delete.
	 *
	 * @see WP Core wp_transition_post_status()
	 * @since 2.8.2
	 * @action init priority 1
	 * @see $this->init_admin_actions();
	 * @see $this->init_cron_actions();
	 *
	 * @return void Early if already called.
	 */
	public function init_post_cache_actions() {

		if ( _has_run( __METHOD__ ) ) return;

		// Can-be cron actions.
		\add_action( 'publish_post', [ $this, 'delete_post_cache' ] );
		\add_action( 'publish_page', [ $this, 'delete_post_cache' ] );

		// Other actions.
		\add_action( 'deleted_post', [ $this, 'delete_post_cache' ] );
		\add_action( 'deleted_page', [ $this, 'delete_post_cache' ] );
		\add_action( 'post_updated', [ $this, 'delete_post_cache' ] );
		\add_action( 'page_updated', [ $this, 'delete_post_cache' ] );

		// Excluded IDs cache.
		\add_action( 'save_post', [ $this, 'delete_excluded_ids_cache' ] );
		\add_action( 'edit_attachment', [ $this, 'delete_excluded_ids_cache' ] );
	}

	/**
	 * Registers plugin cache checks on plugin activation.
	 *
	 * @since 3.1.0
	 */
	public function set_plugin_check_caches() {
		$this->update_static_cache( 'check_seo_plugin_conflicts', 1 );
	}

	/**
	 * Flushes front-page and global transients that can be affected by options.
	 *
	 * @since 2.8.0
	 * @since 3.1.0 Added excluded post ids flush.
	 * @since 3.1.4 Now flushes object cache.
	 * @since 4.1.4 No longer flushes 'front', 'robots', or 'object' cache.
	 */
	public function delete_main_cache() {
		$this->delete_cache( 'sitemap' );
		$this->delete_cache( 'excluded_post_ids' );
	}

	/**
	 * Deletes transient on post save.
	 *
	 * @since 2.8.0
	 * @since 3.0.0 Process is halted when no valid $post_id is supplied.
	 * @since 4.1.3 Now flushes the sitemap cache (and instigates pinging thereof)
	 *              even when TSF sitemaps are disabled.
	 * @since 4.1.4 No longer deletes object cache for post.
	 *
	 * @param int $post_id The Post ID that has been updated.
	 * @return bool True on success, false on failure.
	 */
	public function delete_post_cache( $post_id ) {

		if ( ! $post_id ) return false;

		$success = false;

		// Don't flush sitemap on revision.
		if ( ! \wp_is_post_revision( $post_id ) )
			$success = $this->delete_cache( 'sitemap' );

		return $success;
	}

	/**
	 * Deletes excluded post IDs cache.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True on success, false on failure.
	 */
	public function delete_excluded_ids_cache() {
		return $this->delete_cache( 'excluded_post_ids' );
	}

	/**
	 * Deletes excluded post IDs transient cache.
	 *
	 * @since 3.0.0
	 * @see $this->delete_excluded_ids_cache()
	 *
	 * @return bool True
	 */
	public function delete_excluded_post_ids_transient() {
		$transient = $this->get_exclusion_transient_name();
		$transient and \delete_transient( $transient );
		return true;
	}

	/**
	 * Handles all kinds of cache for removal.
	 * Main cache deletion function handler.
	 *
	 * @since 2.8.0
	 * @since 2.9.3 $type = 'front' now also returns true.
	 * @since 3.1.0 1. Added action.
	 *              2. Removed support for $type 'detection'.
	 * @since 4.0.5 Removed all JSON-LD transient clear calls.
	 * @since 4.1.4 The following $type's are no longer supported: 'front', 'post', 'term', 'author', 'robots', 'object'.
	 *
	 * @param string $type The type
	 * @param int    $id The post, page or TT ID. Defaults to $this->get_the_real_ID().
	 * @param array  $args Additional arguments. They can overwrite $type and $id.
	 * @return bool true on success, false on failure.
	 */
	public function delete_cache( $type, $id = 0, array $args = [] ) {

		$this->parse_delete_cache_keys( $type, $id, $args );

		$success = false;

		switch ( $type ) :
			case 'sitemap':
				$success = $this->delete_sitemap_transient();
				break;

			case 'excluded_post_ids':
				$success = $this->delete_excluded_post_ids_transient();
				break;

			default:
				break;
		endswitch;

		/**
		 * @since 3.1.0
		 *
		 * @param string $type    The flush type. Comes in handy when you use a catch-all function.
		 * @param int    $id      The post, page or TT ID. Defaults to the_seo_framework()->get_the_real_ID().
		 * @param array  $args    Additional arguments. They can overwrite $type and $id.
		 * @param bool   $success Whether the action cleared.
		 */
		\do_action( "the_seo_framework_delete_cache_{$type}", $type, $id, $args, $success );

		return $success;
	}

	/**
	 * Parses input keys for method delete_cache.
	 *
	 * @since 2.8.0
	 *
	 * @param string $type The cache type. Passed by reference.
	 * @param int    $id The post, page or TT ID. Defaults to $this->get_the_real_ID(). Passed by reference.
	 * @param array  $args Additional arguments. They can overwrite $type and $id. Passed by reference.
	 */
	protected function parse_delete_cache_keys( &$type, &$id, &$args ) {

		//= Don't use cache on fetching ID.
		$id = $id ?: $this->get_the_real_ID( false );

		$defaults = [
			'type' => $type,
			'id'   => $id,
			'term' => '',
		];

		/**
		 * @since 2.8.0
		 * @NOTE Careful: Altering this might infinitely loop method delete_cache() if not done strictly.
		 *       Don't blindly overwrite 'type'.
		 * @param array  $args All caching arguments
		 * @param string $type The cache type.
		 * @param int    $id   The post or term ID.
		 */
		$args = (array) \apply_filters( 'the_seo_framework_delete_cache_args', $args, $type, $id );
		$args = \wp_parse_args( $args, $defaults );

		$type = $args['type'];
		$id   = $args['id'];
	}

	/**
	 * Set the value of the transient.
	 *
	 * Prevents setting of transients when they're disabled.
	 *
	 * @since 2.6.0
	 * @uses $this->the_seo_framework_use_transients
	 *
	 * @param string $transient  Transient name. Expected to not be SQL-escaped.
	 * @param string $value      Transient value. Expected to not be SQL-escaped.
	 * @param int    $expiration Transient expiration date, optional. Expected to not be SQL-escaped.
	 */
	public function set_transient( $transient, $value, $expiration = 0 ) {

		if ( $this->the_seo_framework_use_transients )
			\set_transient( $transient, $value, $expiration );
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
	 * @since 2.6.0
	 * @uses $this->the_seo_framework_use_transients
	 *
	 * @param string $transient Transient name. Expected to not be SQL-escaped.
	 * @return mixed|bool Value of the transient. False on failure or non existing transient.
	 */
	public function get_transient( $transient ) {

		if ( $this->the_seo_framework_use_transients )
			return \get_transient( $transient );

		return false;
	}

	/**
	 * Returns the post ID exclusion transient name.
	 *
	 * @since 3.1.0
	 * @NOTE When changing the revision, we MUST delete the old revision key manually.
	 *       Otherwise, the transient will stay indefinitely.
	 *
	 * @return string The current post exclusion transient name. Can be empty.
	 */
	public function get_exclusion_transient_name() {
		$exclude_revision = '1'; // WARNING: SEE NOTE
		return $this->add_cache_key_suffix( 'tsf_exclude_' . $exclude_revision );
	}

	/**
	 * Returns the sitemap transient name.
	 *
	 * @since 3.1.0
	 *
	 * @return string The current sitemap transient name. Can be empty.
	 */
	public function get_sitemap_transient_name() {
		$sitemap_revision = '5';
		return $this->get_option( 'cache_sitemap' ) ? $this->add_cache_key_suffix( 'tsf_sitemap_' . $sitemap_revision ) : '';
	}

	/**
	 * Generate transient key based on query vars or input variables.
	 *
	 * Warning: This can generate errors when used too early if no type has been set.
	 *
	 * @since 2.3.3
	 * @since 2.6.0 Refactored.
	 * @since 2.9.1 : 1. Added early singular type detection.
	 *                2. Moved generation into another method (v4.1.4: removed method).
	 * @since 3.1.1 The first parameter is now optional.
	 * @since 4.1.4 No longer generates a cache key when no `$type` is supplied.
	 * @TODO since we only support by type, it'd be best to rework this into something simple.
	 *
	 * @param int|string|bool $id       The Taxonomy or Post ID.
	 * @param string          $taxonomy The taxonomy name.
	 * @param string          $type     The Post Type.
	 * @return string The generated cache key by query or type.
	 */
	public function generate_cache_key( $id = 0, $taxonomy = '', $type = null ) {

		if ( isset( $type ) )
			return $this->generate_cache_key_by_type( $id, $taxonomy, $type );

		return '';
	}

	/**
	 * Generate transient key based on input.
	 *
	 * Use this method if you wish to evade the query usage.
	 *
	 * @since 2.9.1
	 * @since 2.9.2 Now returns false when an incorrect $type is supplied.
	 * @since 4.1.2 Now accepts $type 'sitemap_lock'.
	 * @since 4.1.4 Removed support for 'author', 'frontpage', 'page', 'post', 'attachment', 'singular', and 'term'.
	 * @see $this->generate_cache_key().
	 * @TODO since we only support a few, it'd be best to rework this into something simple.
	 *
	 * @param int|string|bool $page_id  The Taxonomy or Post ID.
	 * @param string          $taxonomy The term taxonomy.
	 * @param string          $type     The Post Type.
	 * @return string|bool String the generated cache key. Bool false on failure.
	 */
	public function generate_cache_key_by_type( $page_id, $taxonomy = '', $type = '' ) {
		switch ( $type ) :
			case 'ping':
				return $this->add_cache_key_suffix( 'tsf_throttle_ping' );
			case 'sitemap_lock':
				return $this->add_cache_key_suffix( 'tsf_sitemap_lock' );
			default:
				$this->_doing_it_wrong( __METHOD__, 'Third parameter must be a known type.', '2.6.5' );
				return $this->add_cache_key_suffix( \esc_sql( $type . '_' . $page_id . '_' . $taxonomy ) );
		endswitch;
	}

	/**
	 * Adds cache key suffix based on blog id and locale.
	 *
	 * @since 2.7.0
	 * @since 2.8.0 1. $locale is now static.
	 *              2. $key may now be empty.
	 * @since 4.0.0 Removed caching, so to support translation plugin loops.
	 * @global string $blog_id
	 *
	 * @param string $key The cache key.
	 * @return string
	 */
	protected function add_cache_key_suffix( $key = '' ) {
		return $key . '_' . $GLOBALS['blog_id'] . '_' . strtolower( \get_locale() );
	}

	/**
	 * Checks whether the permalink structure is updated.
	 *
	 * @since 2.3.0
	 * @since 2.7.0 Added admin referer check.
	 * @securitycheck 3.0.0 OK.
	 *
	 * @return bool Whether if sitemap transient is deleted.
	 */
	public function delete_sitemap_transient_permalink_updated() {

		if ( isset( $_POST['permalink_structure'] ) || isset( $_POST['category_base'] ) ) {
			if ( \check_admin_referer( 'update-permalink' ) )
				return $this->delete_cache( 'sitemap' );
		}

		return false;
	}

	/**
	 * Delete transient for sitemap on requests.
	 * Also ping search engines.
	 *
	 * Can only run once per request.
	 *
	 * @since 2.2.9
	 * @since 2.8.0 Now listens to option 'cache_sitemap' before deleting transient.
	 * @since 2.8.2 Added cache to prevent duplicated flushes.
	 * @since 4.1.1 Now fires an action.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function delete_sitemap_transient() {

		if ( _has_run( __METHOD__ ) ) return false;

		$transient = $this->get_sitemap_transient_name();
		$transient and \delete_transient( $transient );

		$ping_use_cron           = $this->get_option( 'ping_use_cron' );
		$ping_use_cron_prerender = $this->get_option( 'ping_use_cron_prerender' );

		/**
		 * @since 4.1.1
		 * @since 4.1.2 Added index `ping_use_cron_prerender` to the first parameter.
		 * @param array $params Any useful environment parameters.
		 */
		\do_action(
			'the_seo_framework_sitemap_transient_cleared',
			[
				'ping_use_cron'           => $ping_use_cron,
				'ping_use_cron_prerender' => $ping_use_cron_prerender,
			]
		);

		if ( $ping_use_cron ) {
			\The_SEO_Framework\Bridges\Ping::engage_pinging_cron();
		} else {
			\The_SEO_Framework\Bridges\Ping::ping_search_engines();
		}

		return true;
	}

	/**
	 * Builds and returns the excluded post IDs transient.
	 * The transients are autoloaded, as no expiration is set.
	 *
	 * Memoizes the database request.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 Now no longer crashes on database errors.
	 * @since 4.1.4 1. Now tests against post type exclusions.
	 *              2. Now considers headlessness. This method runs only on the front-end.
	 *
	 * @return array : { 'archive', 'search' }
	 */
	public function get_excluded_ids_from_cache() {

		if ( $this->is_headless['meta'] ) return [
			'archive' => '',
			'search'  => '',
		];

		static $cache = null;

		if ( null === $cache )
			$cache = $this->get_transient( $this->get_exclusion_transient_name() );

		if ( false === $cache ) {
			global $wpdb;

			$supported_post_types = $this->get_supported_post_types();
			$public_post_types    = $this->get_public_post_types();

			$join  = '';
			$where = '';
			if ( $supported_post_types !== $public_post_types ) {
				// Post types can be registered arbitrarily through other plugins, even manually by non-super-admins. Prepare!
				$post_type__in = "'" . implode( "','", array_map( '\\esc_sql', $supported_post_types ) ) . "'";

				// This is as fast as I could make it. Yes, it uses IN, but only on a (tiny) subset of data.
				$join  = "LEFT JOIN {$wpdb->posts} ON {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID";
				$where = "AND {$wpdb->posts}.post_type IN ($post_type__in)";
			}

			//= Two separated equals queries are faster than a single IN with 'meta_key'.
			// phpcs:disable, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- We prepared our whole lives.
			$cache = [
				'archive' => $wpdb->get_results(
					"SELECT post_id, meta_value FROM $wpdb->postmeta $join WHERE meta_key = 'exclude_from_archive' $where"
				),
				'search'  => $wpdb->get_results(
					"SELECT post_id, meta_value FROM $wpdb->postmeta $join WHERE meta_key = 'exclude_local_search' $where"
				),
			];
			// phpcs:enable, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			foreach ( [ 'archive', 'search' ] as $type ) {
				array_walk(
					$cache[ $type ],
					static function( &$v ) {
						if ( isset( $v->meta_value, $v->post_id ) && $v->meta_value ) {
							$v = (int) $v->post_id;
						} else {
							$v = false;
						}
					}
				);
				$cache[ $type ] = array_filter( $cache[ $type ] );
			}

			$this->set_transient( $this->get_exclusion_transient_name(), $cache, 0 );
		}

		return $cache;
	}
}
