<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Cache
 * @subpackage The_SEO_Framework\Cache
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
 * Class The_SEO_Framework\Cache
 *
 * Generates, stores and deletes common transients.
 *
 * @since 2.8.0
 */
class Cache extends Site_Options {

	/**
	 * Determines whether object cache is being used.
	 *
	 * @since 2.8.0
	 * @see $this->use_object_cache
	 *
	 * @return bool
	 */
	protected function use_object_cache() {
		return \wp_using_ext_object_cache() && $this->get_option( 'cache_object' );
	}

	/**
	 * Initializes admin caching actions.
	 *
	 * @since 2.8.0
	 * @action init priority 1
	 * @see $this->init_admin_actions();
	 */
	public function init_admin_caching_actions() {

		$this->init_post_cache_actions();

		//* Deletes author transient.
		\add_action( 'profile_update', [ $this, 'delete_author_cache' ] );

		//* Delete Sitemap transient on permalink structure change.
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

		//* Can-be cron actions.
		\add_action( 'publish_post', [ $this, 'delete_post_cache' ] );
		\add_action( 'publish_page', [ $this, 'delete_post_cache' ] );

		//* Other actions.
		\add_action( 'deleted_post', [ $this, 'delete_post_cache' ] );
		\add_action( 'deleted_page', [ $this, 'delete_post_cache' ] );
		\add_action( 'post_updated', [ $this, 'delete_post_cache' ] );
		\add_action( 'page_updated', [ $this, 'delete_post_cache' ] );

		//* Excluded IDs cache.
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
	 */
	public function delete_main_cache() {
		$this->delete_cache( 'front' );
		$this->delete_cache( 'sitemap' );
		$this->delete_cache( 'robots' );
		$this->delete_cache( 'excluded_post_ids' );
		$this->delete_cache( 'object' );
	}

	/**
	 * Deletes transient on post save.
	 *
	 * @since 2.8.0
	 * @since 3.0.0 Process is halted when no valid $post_id is supplied.
	 *
	 * @param int $post_id The Post ID that has been updated.
	 * @return bool True on success, false on failure.
	 */
	public function delete_post_cache( $post_id ) {

		if ( ! $post_id ) return false;

		$success[] = $this->delete_cache( 'post', $post_id );

		if ( $this->get_option( 'sitemaps_output' ) ) {
			//* Don't flush sitemap on revision.
			if ( ! \wp_is_post_revision( $post_id ) )
				$success[] = $this->delete_cache( 'sitemap' );
		}

		return ! in_array( false, $success, true );
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
	 * Deletes cache on profile save.
	 *
	 * @since 2.8.0
	 *
	 * @param int $user_id The User ID that has been updated.
	 * @return bool True on success, false on failure.
	 */
	public function delete_author_cache( $user_id ) {
		return $this->delete_cache( 'author', $user_id );
	}

	/**
	 * Deletes object cache.
	 *
	 * @since 2.9.0
	 * @since 4.0.0 Now does something.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function delete_object_cache() {
		return $this->delete_cache( 'object' );
	}

	/**
	 * Handles all kinds of cache for removal.
	 * Main cache deletion function handler.
	 *
	 * @since 2.8.0
	 * @since 2.9.3 $type = 'front' now also returns true.
	 * @since 3.1.0 Added action.
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
			case 'front':
				$front_id = $this->get_the_front_page_ID();

				$this->object_cache_delete( $this->get_meta_output_cache_key_by_type( $front_id, '', 'frontpage' ) );
				$this->delete_ld_json_transient( $front_id, '', 'frontpage' );
				$success = true;
				break;

			case 'post':
				$post_type = \get_post_type( $id );

				if ( $post_type ) {
					switch ( $post_type ) {
						case 'page':
						case 'post':
						case 'attachment':
							break;

						default:
							//* Generic key for CPT.
							$post_type = 'singular';
							break;
					}

					$this->object_cache_delete( $this->get_meta_output_cache_key_by_type( $id, '', $post_type ) );
					$this->delete_ld_json_transient( $id, '', $post_type );
					$success = true;
				}
				break;

			//* Careful, this can only run on archive pages. For now.
			case 'term':
				$this->object_cache_delete( $this->get_meta_output_cache_key_by_type( $id, $args['term'], 'term' ) );
				$this->delete_ld_json_transient( $id, $args['term'], 'term' );
				$success = true;
				break;

			case 'author':
				$this->object_cache_delete( $this->get_meta_output_cache_key_by_type( $id, 'author', 'author' ) );
				$this->delete_ld_json_transient( $id, 'author', 'author' );
				$success = true;
				break;

			case 'sitemap':
				$success = $this->delete_sitemap_transient();
				break;

			case 'robots':
				$success = $this->object_cache_delete( $this->get_robots_txt_cache_key() );
				break;

			case 'excluded_post_ids':
				$success = $this->delete_excluded_post_ids_transient();
				break;

			case 'object':
				$success = \wp_cache_flush();
				break;

			case 'detection':
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
	 * Object cache set wrapper.
	 *
	 * @since 2.4.3
	 *
	 * @param string $key    The Object cache key.
	 * @param mixed  $data   The Object cache data.
	 * @param int    $expire The Object cache expire time.
	 * @param string $group  The Object cache group.
	 * @return bool true on set, false when disabled.
	 */
	public function object_cache_set( $key, $data, $expire = 0, $group = 'the_seo_framework' ) {

		if ( $this->use_object_cache )
			return \wp_cache_set( $key, $data, $group, $expire );

		return false;
	}

	/**
	 * Object cache get wrapper.
	 *
	 * @since 2.4.3
	 *
	 * @param string $key   The Object cache key.
	 * @param string $group The Object cache group.
	 * @param bool   $force Whether to force an update of the local cache.
	 * @param bool   $found Whether the key was found in the cache.
	 *                      Disambiguates a return of false, a storable value. Passed by reference.
	 * @return mixed wp_cache_get if object caching is allowed. False otherwise.
	 */
	public function object_cache_get( $key, $group = 'the_seo_framework', $force = false, &$found = null ) {

		if ( $this->use_object_cache )
			return \wp_cache_get( $key, $group, $force, $found );

		return false;
	}

	/**
	 * Object cache delete wrapper.
	 *
	 * @since 2.8.0
	 *
	 * @param string $key   The Object cache key.
	 * @param string $group The Object cache group.
	 * @return mixed wp_cache_delete if object caching is allowed. False otherwise.
	 */
	public function object_cache_delete( $key, $group = 'the_seo_framework' ) {

		if ( $this->use_object_cache )
			return \wp_cache_delete( $key, $group );

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
	 * Returns ld_json transients for page ID.
	 *
	 * @since 3.1.0
	 * @since 3.1.1 : The first parameter is now optional.
	 *
	 * @param int|string|bool $id       The Taxonomy or Post ID. If false it will generate for the blog page.
	 * @param string          $taxonomy The taxonomy name.
	 * @param string|null     $type     The post type.
	 * @return string The ld_json cache key.
	 */
	public function get_ld_json_transient_name( $id = 0, $taxonomy = '', $type = null ) {

		if ( ! $this->get_option( 'cache_meta_schema' ) )
			return '';

		$cache_key = $this->generate_cache_key( $id, $taxonomy, $type );

		$revision = '7';

		/**
		 * Change key based on options.
		 */
		$options  = $this->enable_ld_json_breadcrumbs() ? '1' : '0';
		$options .= $this->enable_ld_json_searchbox() ? '1' : '0';

		return 'tsf_' . $revision . '_' . $options . '_ldjs_' . $cache_key;
	}

	/**
	 * Generate transient key based on query vars or input variables.
	 *
	 * Warning: This can generate errors when used too early if no type has been set.
	 *
	 * @since 2.3.3
	 * @since 2.6.0 Refactored.
	 * @since 2.9.1 : 1. Added early singular type detection.
	 *                2. Moved generation into $this->generate_cache_key_by_query().
	 * @since 3.1.1 : The first parameter is now optional.
	 * @see $this->generate_cache_key_by_query() to get cache key from the query.
	 * @see $this->generate_cache_key_by_type() to get cache key outside of the query.
	 *
	 * @param int|string|bool $id       The Taxonomy or Post ID.
	 * @param string          $taxonomy The taxonomy name.
	 * @param string          $type     The Post Type.
	 * @return string The generated cache key by query or type.
	 */
	public function generate_cache_key( $id = 0, $taxonomy = '', $type = null ) {

		if ( isset( $type ) )
			return $this->generate_cache_key_by_type( $id, $taxonomy, $type );

		return $this->generate_cache_key_by_query( $id, $taxonomy, $type );
	}

	/**
	 * Generate transient key based on query vars.
	 *
	 * Warning: This can generate errors when used too early if no type has been set.
	 *
	 * @since 2.9.1
	 * @since 3.1.1 : The first parameter is now optional.
	 * @staticvar array $cached_id : contains cache strings.
	 * @see $this->generate_cache_key_by_type() to get cache key outside of the query.
	 *
	 * @param int|string|bool $page_id  The Taxonomy or Post ID.
	 * @param string          $taxonomy The Taxonomy name.
	 * @param string          $type     The Post Type.
	 * @return string The generated cache key by query.
	 */
	public function generate_cache_key_by_query( $page_id = 0, $taxonomy = '', $type = null ) {

		$page_id = $page_id ?: $this->get_the_real_ID();

		static $cached_id = [];

		if ( isset( $cached_id[ $page_id ][ $taxonomy ] ) )
			return $cached_id[ $page_id ][ $taxonomy ];

		//* Placeholder ID.
		$the_id = '';
		$_t     = $taxonomy;

		if ( $this->is_404() ) {
			$the_id = '_404_';
		} elseif ( $this->is_archive() ) {
			if ( $this->is_category() || $this->is_tag() || $this->is_tax() ) {

				if ( empty( $_t ) ) {
					$o = \get_queried_object();

					if ( isset( $o->taxonomy ) )
						$_t = $o->taxonomy;
				}

				$the_id = $this->generate_taxonomical_cache_key( $page_id, $_t );

				if ( $this->is_tax() )
					$the_id = 'archives_' . $the_id;

			} elseif ( $this->is_author() ) {
				$the_id = 'author_' . $page_id;
			} elseif ( $this->is_date() ) {
				$post = \get_post();

				if ( $post && isset( $post->post_date ) ) {
					$date = $post->post_date;

					if ( $this->is_year() ) {
						$the_id .= 'year_' . \mysql2date( 'y', $date, false );
					} elseif ( $this->is_month() ) {
						$the_id .= 'month_' . \mysql2date( 'm_y', $date, false );
					} elseif ( $this->is_day() ) {
						//* Day. The correct notation.
						$the_id .= 'day_' . \mysql2date( 'd_m_y', $date, false );
					}
				} else {
					//* Get seconds since UNIX Epoch. This is a failsafe.

					/**
					 * @staticvar string $unix : Used to maintain a static timestamp for this query.
					 */
					static $unix = null;

					if ( ! isset( $unix ) )
						$unix = time();

					//* Temporarily disable caches to prevent database spam.
					$this->the_seo_framework_use_transients = false;
					$this->use_object_cache = false;

					$the_id = 'unix_' . $unix;
				}
			} else {
				//* Other taxonomical archives.

				if ( empty( $_t ) ) {
					$post_type = \get_query_var( 'post_type' );

					if ( is_array( $post_type ) )
						reset( $post_type );

					if ( $post_type )
						$post_type_obj = \get_post_type_object( $post_type );

					if ( isset( $post_type_obj->labels->name ) )
						$_t = $post_type_obj->labels->name;
				}

				//* Still empty? Try this.
				if ( empty( $_t ) )
					$_t = \get_query_var( 'taxonomy' );

				$the_id = $this->generate_taxonomical_cache_key( $page_id, $_t );

				$the_id = 'archives_' . $the_id;
			}
		} elseif ( ( $this->is_real_front_page() || $this->is_front_page_by_id( $page_id ) ) || ( \is_admin() && $this->is_seo_settings_page( true ) ) ) {
			//* Front/HomePage.
			$the_id = $this->generate_front_page_cache_key();
		} elseif ( $this->is_blog_page( $page_id ) ) {
			$the_id = 'blog_' . $page_id;
		} elseif ( $this->is_singular() ) {

			$post_type = \get_post_type( $page_id );

			switch ( $post_type ) :
				case 'page':
					$the_id = 'page_' . $page_id;
					break;

				case 'post':
					$the_id = 'post_' . $page_id;
					break;

				case 'attachment':
					$the_id = 'attach_' . $page_id;
					break;

				default:
					$the_id = 'singular_' . $page_id;
					break;
			endswitch;
		} elseif ( $this->is_search() ) {
			//* Remove spaces. Limit to 10 chars.
			// TODO use metahpone?
			$query = \esc_sql( substr( str_replace( ' ', '', \get_search_query( true ) ), 0, 10 ) );

			//* Temporarily disable caches to prevent database spam.
			$this->the_seo_framework_use_transients = false;
			$this->use_object_cache                 = false;

			$the_id = $page_id . '_s_' . $query;
		}

		/**
		 * Blog page isn't set or something else is happening. Causes all kinds of problems :(
		 * Noob. :D
		 */
		if ( empty( $the_id ) )
			$the_id = 'noob_' . $page_id . '_' . $_t;

		/**
		 * This should be at most 25 chars. Unless the $blog_id is higher than 99,999,999.
		 * Then some cache keys will conflict on every 10th blog ID from eachother which post something on the same day..
		 * On the day archive. With the same description setting (short).
		 */
		return $cached_id[ $page_id ][ $taxonomy ] = $this->add_cache_key_suffix( $the_id );
	}

	/**
	 * Generate transient key based on input.
	 *
	 * Use this method if you wish to evade the query usage.
	 *
	 * @since 2.9.1
	 * @since 2.9.2 Now returns false when an incorrect $type is supplied.
	 * @staticvar array $cached_id : contains cache strings.
	 * @see $this->generate_cache_key().
	 * @see $this->generate_cache_key_by_query() to get cache key from the query.
	 *
	 * @param int|string|bool $page_id  The Taxonomy or Post ID.
	 * @param string          $taxonomy The term taxonomy.
	 * @param string          $type     The Post Type.
	 * @return string|bool String the generated cache key. Bool false on failure.
	 */
	public function generate_cache_key_by_type( $page_id, $taxonomy = '', $type = '' ) {

		switch ( $type ) :
			case 'author':
				return $this->add_cache_key_suffix( 'author_' . $page_id );
				break;
			case 'frontpage':
				return $this->add_cache_key_suffix( $this->generate_front_page_cache_key() );
				break;
			case 'page':
				return $this->add_cache_key_suffix( 'page_' . $page_id );
				break;
			case 'post':
				return $this->add_cache_key_suffix( 'post_' . $page_id );
				break;
			case 'attachment':
				return $this->add_cache_key_suffix( 'attach_' . $page_id );
				break;
			case 'singular':
				return $this->add_cache_key_suffix( 'singular_' . $page_id );
				break;
			case 'term':
				return $this->add_cache_key_suffix( $this->generate_taxonomical_cache_key( $page_id, $taxonomy ) );
				break;
			case 'ping':
				return $this->add_cache_key_suffix( 'tsf_throttle_ping' );
			default:
				$this->_doing_it_wrong( __METHOD__, 'Third parameter must be a known type.', '2.6.5' );
				return $this->add_cache_key_suffix( \esc_sql( $type . '_' . $page_id . '_' . $taxonomy ) );
				break;
		endswitch;

		return false;
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
	 * Returns the front page partial transient key.
	 *
	 * @since ??? (2.8+)
	 *
	 * @param string $type Either blog or page.
	 * @return string the front page transient key.
	 */
	public function generate_front_page_cache_key( $type = '' ) {

		if ( ! $type ) {
			if ( $this->has_page_on_front() ) {
				$type = 'page';
			} else {
				$type = 'blog';
			}
		}

		return \esc_sql( 'h' . $type . '_' . $this->get_the_front_page_ID() );
	}

	/**
	 * Generates Cache key for taxonomical archives.
	 *
	 * @since 2.6.0
	 *
	 * @param int    $page_id  The taxonomy or page ID.
	 * @param string $taxonomy The taxonomy name.
	 * @return string The Taxonomical Archive cache key.
	 */
	protected function generate_taxonomical_cache_key( $page_id = '', $taxonomy = '' ) {

		$the_id = '';

		if ( false !== strpos( $taxonomy, '_' ) ) {
			$taxonomy_name = explode( '_', $taxonomy );
			if ( is_array( $taxonomy_name ) ) {
				foreach ( $taxonomy_name as $name ) {
					if ( mb_strlen( $name ) >= 3 ) {
						$the_id .= mb_substr( $name, 0, 3 ) . '_';
					} else {
						$the_id = $name . '_';
					}
				}
			}
		}

		if ( ! $the_id ) {
			if ( mb_strlen( $taxonomy ) >= 5 ) {
				$the_id = mb_substr( $taxonomy, 0, 5 );
			} else {
				$the_id = \esc_sql( $taxonomy );
			}
		}

		$the_id = strtolower( $the_id );

		//* Put it all together.
		return rtrim( $the_id, '_' ) . '_' . $page_id;
	}

	/**
	 * Returns the robots.txt object cache key.
	 *
	 * @since 2.8.0
	 *
	 * @return string The robots_txt cache key.
	 */
	public function get_robots_txt_cache_key() {

		$revision = '1';

		return 'robots_txt_output_' . $revision . $GLOBALS['blog_id'];
	}

	/**
	 * Returns the TSF meta output Object cache key.
	 *
	 * @since 2.9.1
	 * @uses THE_SEO_FRAMEWORK_DB_VERSION as cache key buster.
	 *
	 * @return string The TSF meta output cache key.
	 */
	public function get_meta_output_cache_key_by_query() {
		/**
		 * Cache key buster.
		 * Busts cache on each new db version.
		 */
		$key = $this->generate_cache_key_by_query() . '_' . THE_SEO_FRAMEWORK_DB_VERSION;

		$page  = (string) $this->page();
		$paged = (string) $this->paged();

		return 'seo_framework_output_' . $key . '_' . $paged . '_' . $page;
	}

	/**
	 * Returns the TSF meta output Object cache key.
	 *
	 * @since 2.9.1
	 * @uses THE_SEO_FRAMEWORK_DB_VERSION as cache key buster.
	 * @uses $this->generate_cache_key_by_type()
	 * @see $this->get_meta_output_cache_key_by_query()
	 *
	 * @param int    $id       The ID. Defaults to current ID.
	 * @param string $taxonomy The term taxonomy
	 * @param string $type     The post type.
	 * @return string The TSF meta output cache key.
	 */
	public function get_meta_output_cache_key_by_type( $id = 0, $taxonomy = '', $type = '' ) {
		/**
		 * Cache key buster.
		 * Busts cache on each new db version.
		 */
		$key = $this->generate_cache_key_by_type( $id, $taxonomy, $type ) . '_' . THE_SEO_FRAMEWORK_DB_VERSION;

		//= Refers to the first page, always.
		$_page = $_paged = '1';

		return 'seo_framework_output_' . $key . '_' . $_paged . '_' . $_page;
	}

	/**
	 * Checks whether the permalink structure is updated.
	 *
	 * @since 2.3.0
	 * @since 2.7.0 : Added admin referer check.
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
	 * @since 2.2.9
	 * @since 2.8.0 : Mow listens to option 'cache_sitemap' before deleting transient.
	 * @since 2.8.2 : Added cache to prevent duplicated flushes.
	 * @staticvar bool $run
	 *
	 * @return bool True on success, false on failure.
	 */
	public function delete_sitemap_transient() {

		static $run = false;

		if ( $run )
			return false;

		$transient = $this->get_sitemap_transient_name();
		$transient and \delete_transient( $transient );

		if ( $this->get_option( 'ping_use_cron' ) ) {
			\The_SEO_Framework\Bridges\Ping::engage_pinging_cron();
		} else {
			\The_SEO_Framework\Bridges\Ping::ping_search_engines();
		}

		return $run = true;
	}

	/**
	 * Deletes transient for the LD+Json scripts on requests.
	 *
	 * @since 2.4.2
	 * @since 2.8.0 Now listens to option 'cache_meta_schema' before deleting transient.
	 * @since 2.9.1 Now no longer sets object property $this->ld_json_transient.
	 * @since 2.9.4 Removed cache.
	 *
	 * @param mixed       $page_id  The page ID or identifier.
	 * @param string      $taxonomy The tt name.
	 * @param string|null $type     The post type.
	 * @return bool true
	 */
	public function delete_ld_json_transient( $page_id, $taxonomy = '', $type = null ) {

		if ( $this->get_option( 'cache_meta_schema' ) ) {
			$transient = $this->get_ld_json_transient_name( $page_id, $taxonomy, $type );
			$transient and \delete_transient( $transient );
		}

		return true;
	}

	/**
	 * Builds and returns the excluded post IDs transient.
	 * The transients are autoloaded, as no expiration is set.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 Now no longer crashes on database errors.
	 * @staticvar array $cache
	 *
	 * @return array : { 'archive', 'search' }
	 */
	public function get_excluded_ids_from_cache() {

		static $cache = null;

		if ( null === $cache )
			$cache = $this->get_transient( $this->get_exclusion_transient_name() );

		if ( false === $cache ) {
			global $wpdb;
			$cache = [];

			//= Two separated equals queries are faster than a single IN with 'meta_key'.
			$cache['archive'] = $wpdb->get_results(
				"SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = 'exclude_from_archive'"
			); // No cache OK, Set in autoloaded transient. DB call ok.

			$cache['search'] = $wpdb->get_results(
				"SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = 'exclude_local_search'"
			); // No cache OK, Set in autoloaded transient. DB call ok.

			foreach ( [ 'archive', 'search' ] as $key ) {
				array_walk(
					$cache[ $key ],
					function( &$v ) {
						if ( isset( $v->meta_value, $v->post_id ) && $v->meta_value ) {
							$v = (int) $v->post_id;
						} else {
							$v = false;
						}
					}
				);
				$cache[ $key ] = array_filter( $cache[ $key ] );
			}

			$this->set_transient( $this->get_exclusion_transient_name(), $cache, 0 );
		}

		return $cache;
	}
}
