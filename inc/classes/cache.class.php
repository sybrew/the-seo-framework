<?php
/**
 * @package The_SEO_Framework\Classes
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
class Cache extends Sitemaps {

	/**
	 * The sitemap transient name.
	 *
	 * @since 2.2.9
	 *
	 * @var string The Sitemap Transient Name.
	 */
	protected $sitemap_transient;

	/**
	 * The Automatic Description transient name.
	 *
	 * @since 2.3.3
	 *
	 * @var string The Automatic Description Transient Name.
	 */
	protected $auto_description_transient;

	/**
	 * The LD+Json script transient name.
	 *
	 * @since 2.3.3
	 *
	 * @var string The LD+Json Script Transient Name.
	 */
	protected $ld_json_transient;

	/**
	 * The Theme is doing the Title right transient name
	 *
	 * @since 2.5.2
	 *
	 * @var string
	 */
	protected $theme_doing_it_right_transient;

	/**
	 * The excluded Post IDs transient name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	protected $excluded_post_ids_transient;

	/**
	 * Constructor, load parent constructor and set up caches.
	 */
	protected function __construct() {
		parent::__construct();

		//* Setup Transient names
		add_action( 'plugins_loaded', array( $this, 'setup_transient_names' ), 10 );
	}

	/**
	 * Determines whether object cache is being used.
	 *
	 * @since 2.8.0
	 * @see $this->use_object_cache
	 *
	 * @return bool
	 */
	protected function use_object_cache() {
		return \wp_using_ext_object_cache() && $this->is_option_checked( 'cache_object' );
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

		//* Deletes term description transient.
		\add_action( 'edit_term', array( $this, 'delete_auto_description_transients_term' ), 10, 3 );
		\add_action( 'delete_term', array( $this, 'delete_auto_description_transients_term' ), 10, 4 );

		//* Deletes author transient.
		\add_action( 'profile_update', array( $this, 'delete_author_cache' ) );

		//* Delete Sitemap transient on permalink structure change.
		\add_action( 'load-options-permalink.php', array( $this, 'delete_sitemap_transient_permalink_updated' ), 20 );

		//* Deletes front page description transient on Tagline change.
		\add_action( 'update_option_blogdescription', array( $this, 'delete_auto_description_frontpage_transient' ), 10, 1 );

		//* Delete doing it wrong transient after theme switch or plugin upgrade.
		\add_action( 'after_switch_theme', array( $this, 'delete_theme_dir_transient' ), 10, 0 );
		\add_action( 'upgrader_process_complete', array( $this, 'delete_theme_dir_transient' ), 10, 2 );
	}

	/**
	 * Deletes Sitemap and Description transients on post publish/delete.
	 *
	 * @see WP Core wp_transition_post_status()
	 * @since 2.8.2
	 * @staticvar bool $run
	 * @action init priority 1
	 * @see $this->init_admin_actions();
	 * @see $this->init_cron_actions();
	 *
	 * @return void Early if already called.
	 */
	public function init_post_cache_actions() {

		static $run = false;

		if ( $run )
			return;

		//* Can-be cron actions.
		\add_action( 'publish_post', array( $this, 'delete_post_cache' ) );
		\add_action( 'publish_page', array( $this, 'delete_post_cache' ) );

		//* Other actions.
		\add_action( 'deleted_post', array( $this, 'delete_post_cache' ) );
		\add_action( 'deleted_page', array( $this, 'delete_post_cache' ) );
		\add_action( 'post_updated', array( $this, 'delete_post_cache' ) );
		\add_action( 'page_updated', array( $this, 'delete_post_cache' ) );

		//* Excluded IDs cache.
		\add_action( 'save_post', array( $this, 'delete_excluded_ids_cache' ) );

		$run = true;
	}

	/**
	 * Flushes front-page and global transients that can be affected by options.
	 *
	 * @since 2.8.0
	 * @since 2.9.0 : Added object cache flush.
	 * @TODO make 2.9 note work.
	 */
	public function delete_main_cache() {
		$this->delete_cache( 'front' );
		$this->delete_cache( 'sitemap' );
		$this->delete_cache( 'robots' );
		// $this->delete_cache( 'objectflush' );
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

		if ( ! $post_id )
			return false;

		$success = array();

		$success[] = $this->delete_cache( 'post', $post_id );

		if ( $this->is_option_checked( 'sitemaps_output' ) ) {
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
		\delete_transient( $this->excluded_post_ids_transient );
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
	 * @TODO make this work.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function delete_object_cache() {
		return false;
		// return $this->delete_cache( 'objectflush' );
	}

	/**
	 * Handles all kinds of cache for removal.
	 * Main cache deletion function handler.
	 *
	 * @since 2.8.0
	 * @since 2.9.3 $type = 'front' now also returns true.
	 *
	 * @param string $type The type
	 * @param int $id The post, page or TT ID. Defaults to $this->get_the_real_ID().
	 * @param array $args Additional arguments. They can overwrite $type and $id.
	 * @return bool true on success, false on failure.
	 */
	public function delete_cache( $type, $id = 0, array $args = array() ) {

		$this->parse_delete_cache_keys( $type, $id, $args );

		switch ( $type ) :
			case 'front' :
				$front_id = $this->get_the_front_page_ID();

				$this->object_cache_delete( $this->get_meta_output_cache_key_by_type( $front_id, '', 'frontpage' ) );
				$this->delete_auto_description_transient( $front_id, '', 'frontpage' );
				$this->delete_ld_json_transient( $front_id, '', 'frontpage' );
				return true;
				break;

			case 'post' :
				if ( ! $post_type = \get_post_type( $id ) )
					return false;

				switch ( $post_type ) {
					case 'page' :
					case 'post' :
					case 'attachment' :
						break;

					default :
						//* Generic key for CPT.
						$post_type = 'singular';
						break;
				}

				$this->object_cache_delete( $this->get_meta_output_cache_key_by_type( $id, '', $post_type ) );
				$this->delete_auto_description_transient( $id, '', $post_type );
				$this->delete_ld_json_transient( $id, '', $post_type );
				return true;
				break;

			//* Careful, this can only run on archive pages. For now.
			case 'term' :
				$this->object_cache_delete( $this->get_meta_output_cache_key_by_type( $id, $args['term'], 'term' ) );
				$this->delete_auto_description_transient( $id, $args['term'], 'term' );
				$this->delete_ld_json_transient( $id, $args['term'], 'term' );
				return true;
				break;

			case 'author' :
				$this->object_cache_delete( $this->get_meta_output_cache_key_by_type( $id, 'author', 'author' ) );
				$this->delete_auto_description_transient( $id, 'author', 'author' );
				$this->delete_ld_json_transient( $id, 'author', 'author' );
				return true;
				break;

			case 'sitemap' :
				return $this->delete_sitemap_transient();
				break;

			case 'robots' :
				return $this->object_cache_delete( $this->get_robots_txt_cache_key() );
				break;

			case 'excluded_post_ids' :
				return $this->delete_excluded_post_ids_transient();
				break;

			case 'detection' :
				return $this->delete_theme_dir_transient();
				break;

			//* Flush all transients.
			case 'transientflush' :
				/**
				 * Applies filters 'the_seo_framework_allow_transient_flush' : boolean
				 * @since 2.8.0
				 * WARNING: Experimental and untested. Use at own risk.
				 */
				if ( \apply_filters( 'the_seo_framework_allow_transient_flush', false ) ) {
					if ( ! \wp_using_ext_object_cache() ) {
						//* Delete WordPress set transients.
						if ( $GLOBALS['wpdb']->delete( $wpdb->options, array( 'option_name' => '%_transient_timeout_tsf_%' ) ) )
							if ( $GLOBALS['wpdb']->delete( $wpdb->options, array( 'option_name' => '%_transient_tsf_%' ) ) )
								return true;
					}
				}
				break;

			/**
			 * Flush whole object cache group.
			 * Set here for external functions to use. It works because of magic methods.
			 *
			 * @NOTE Other caching plugins can override these groups. Therefore this
			 * does NOT work.
			 * @TODO make this work.
			 */
			case 'objectflush' :
				//* @NOTE false can't pass.
				if ( false && $this->use_object_cache ) {
					if ( isset( $GLOBALS['wp_object_cache']->cache['the_seo_framework'] ) ) {
						$_cache = $GLOBALS['wp_object_cache']->cache;
						unset( $_cache['the_seo_framework'] );
						$GLOBALS['wp_object_cache']->cache = $_cache;
						return true;
					}
				}
				break;

			default :
				break;
		endswitch;

		return false;
	}

	/**
	 * Parses input keys for method delete_cache.
	 *
	 * @since 2.8.0
	 *
	 * @param string $type The cache type. Passed by reference.
	 * @param int $id The post, page or TT ID. Defaults to $this->get_the_real_ID(). Passed by reference.
	 * @param array $args Additional arguments. They can overwrite $type and $id. Passed by reference.
	 */
	protected function parse_delete_cache_keys( &$type, &$id, &$args ) {

		$id = $id ?: $this->get_the_real_ID();

		$defaults = array(
			'type' => $type,
			'id' => $id,
			'term' => '',
		);

		/**
		 * Applies filters 'the_seo_framework_delete_cache_args' : array
		 * Careful: might infinitely loop method delete_cache() if not done strictly.
		 *
		 * @since 2.8.0
		 *
		 * @param array $args
		 * @param string $type
		 * @param int $id
		 */
		$args = (array) \apply_filters( 'the_seo_framework_delete_cache_args', $args, $type, $id );
		$args = \wp_parse_args( $args, $defaults );

		$type = $args['type'];
		$id = $args['id'];
	}

	/**
	 * Set the value of the transient.
	 *
	 * Prevents setting of transients when they're disabled.
	 * @see $this->the_seo_framework_use_transients
	 *
	 * @since 2.6.0
	 *
	 * @param string $transient Transient name. Expected to not be SQL-escaped.
	 * @param string $value Transient value. Expected to not be SQL-escaped.
	 * @param int $expiration Optional Transient expiration date, optional. Expected to not be SQL-escaped.
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
	 * @see $this->the_seo_framework_use_transients
	 *
	 * @since 2.6.0
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
	 * @param string $key The Object cache key.
	 * @param mixed $data The Object cache data.
	 * @param int $expire The Object cache expire time.
	 * @param string $group The Object cache group.
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
	 * @param string $key The Object cache key.
	 * @param string $group The Object cache group.
	 * @param bool $force Whether to force an update of the local cache.
	 * @param bool $found Whether the key was found in the cache. Disambiguates a return of false, a storable value. Passed by reference.
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
	 * @param string $key The Object cache key.
	 * @param string $group The Object cache group.
	 * @return mixed wp_cache_delete if object caching is allowed. False otherwise.
	 */
	public function object_cache_delete( $key, $group = 'the_seo_framework' ) {

		if ( $this->use_object_cache )
			return \wp_cache_delete( $key, $group );

		return false;
	}

	/**
	 * Setup vars for general site transients.
	 *
	 * @since 2.3.3
	 * @since 2.8.0:
	 *    1. Added locale suffix.
	 *    2. Added check for option 'cache_sitemap'.
	 * @since 3.0.0 Now also sets up $excluded_post_ids_transient
	 * @global int $blog_id
	 */
	public function setup_transient_names() {
		global $blog_id;

		/**
		 * When the caching mechanism changes. Change this value.
		 * Use hex. e.g. 0, 1, 2, 9, a, b
		 */
		$sitemap_revision = '4';
		$theme_dir_revision = '1';
		$exclude_revision = '0';

		$this->sitemap_transient = $this->is_option_checked( 'cache_sitemap' ) ? $this->add_cache_key_suffix( 'tsf_sitemap_' . $sitemap_revision ) : '';
		$this->theme_doing_it_right_transient = 'tsf_tdir_' . $theme_dir_revision . '_' . $blog_id;
		$this->excluded_post_ids_transient = 'tsf_exclude_' . $exclude_revision . '_' . $blog_id;
	}

	/**
	 * Sets up property for autodescription transient.
	 *
	 * @since 2.3.3
	 * @since 2.8.0: Now listens to option 'cache_meta_description'.
	 * @see $this->get_auto_description_transient().
	 *
	 * @param int|string|bool $page_id the Taxonomy or Post ID. If false it will generate for the blog page.
	 * @param string $taxonomy The taxonomy name.
	 * @param string $type The Post Type
	 * @return void Early if caching is disabled.
	 */
	public function setup_auto_description_transient( $page_id, $taxonomy = '', $type = null ) {

		if ( false === $this->is_option_checked( 'cache_meta_description' ) )
			return;

		$this->auto_description_transient = $this->get_auto_description_transient( $page_id, $taxonomy, $type );
	}

	/**
	 * Returns autodescription transients key by page ID.
	 *
	 * @since 2.9.1
	 *
	 * @param int|string|bool $page_id the Taxonomy or Post ID.
	 * @param string $taxonomy The taxonomy name.
	 * @param string $type The Post Type.
	 * @return string The auto description transient key.
	 */
	public function get_auto_description_transient( $page_id = 0, $taxonomy = '', $type = null ) {

		$cache_key = $this->generate_cache_key( $page_id, $taxonomy, $type );

		$revision = '3';
		$additions = $this->add_description_additions( $page_id, $taxonomy );

		if ( $additions ) {
			$option = $this->get_option( 'description_blogname' ) ? '1' : '0';
			return 'tsf_desc_' . $option . '_' . $revision . '_' . $cache_key;
		} else {
			return 'tsf_desc_noa_' . $revision . '_' . $cache_key;
		}
	}

	/**
	 * Sets up property for ld_json transient.
	 *
	 * @since 2.3.3
	 * @since 2.8.0: Now listens to option 'cache_meta_schema'.
	 *
	 * @param int|string|bool $page_id the Taxonomy or Post ID. If false it will generate for the blog page.
	 * @param string $taxonomy The taxonomy name.
	 * @param string|null $type The post type.
	 * @return void Early if caching is disabled.
	 */
	public function setup_ld_json_transient( $page_id, $taxonomy = '', $type = null ) {

		if ( false === $this->is_option_checked( 'cache_meta_schema' ) )
			return;

		$this->ld_json_transient = $this->get_ld_json_transient( $page_id, $taxonomy, $type );
	}

	/**
	 * Returns ld_json transients for page ID.
	 *
	 * @since 2.9.1
	 *
	 * @param int|string|bool $page_id the Taxonomy or Post ID. If false it will generate for the blog page.
	 * @param string $taxonomy The taxonomy name.
	 * @param string|null $type The post type.
	 * @return string The ld_json cache key.
	 */
	public function get_ld_json_transient( $page_id, $taxonomy = '', $type = null ) {

		$cache_key = $this->generate_cache_key( $page_id, $taxonomy, $type );

		$revision = '6';

		/**
		 * Change key based on options.
		 */
		$options = $this->enable_ld_json_breadcrumbs() ? '1' : '0';
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
	 * @see $this->generate_cache_key_by_query() to get cache key from the query.
	 * @see $this->generate_cache_key_by_type() to get cache key outside of the query.
	 *
	 * @param int|string|bool $page_id the Taxonomy or Post ID.
	 * @param string $taxonomy The taxonomy name.
	 * @param string $type The Post Type.
	 * @return string The generated cache key by query or type.
	 */
	public function generate_cache_key( $page_id, $taxonomy = '', $type = null ) {

		if ( isset( $type ) )
			return $this->generate_cache_key_by_type( $page_id, $taxonomy, $type );

		return $this->generate_cache_key_by_query( $page_id, $taxonomy, $type );
	}

	/**
	 * Generate transient key based on query vars.
	 *
	 * Warning: This can generate errors when used too early if no type has been set.
	 *
	 * @since 2.9.1
	 * @staticvar array $cached_id : contains cache strings.
	 * @see $this->generate_cache_key_by_type() to get cache key outside of the query.
	 *
	 * @param int|string|bool $page_id the Taxonomy or Post ID.
	 * @param string $taxonomy The Taxonomy name.
	 * @param string $type The Post Type
	 * @return string The generated cache key by query.
	 */
	public function generate_cache_key_by_query( $page_id, $taxonomy = '', $type = null ) {

		$page_id = $page_id ?: $this->get_the_real_ID();

		static $cached_id = array();

		if ( isset( $cached_id[ $page_id ][ $taxonomy ] ) )
			return $cached_id[ $page_id ][ $taxonomy ];

		//* Placeholder ID.
		$the_id = '';
		$t = $taxonomy;

		if ( $this->is_404() ) {
			$the_id = '_404_';
		} elseif ( $this->is_archive() ) {
			if ( $this->is_category() || $this->is_tag() || $this->is_tax() ) {

				if ( empty( $t ) ) {
					$o = \get_queried_object();

					if ( isset( $o->taxonomy ) )
						$t = $o->taxonomy;
				}

				$the_id = $this->generate_taxonomical_cache_key( $page_id, $t );

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

				if ( empty( $t ) ) {
					$post_type = \get_query_var( 'post_type' );

					if ( is_array( $post_type ) )
						reset( $post_type );

					if ( $post_type )
						$post_type_obj = \get_post_type_object( $post_type );

					if ( isset( $post_type_obj->labels->name ) )
						$t = $post_type_obj->labels->name;
				}

				//* Still empty? Try this.
				if ( empty( $t ) )
					$t = \get_query_var( 'taxonomy' );

				$the_id = $this->generate_taxonomical_cache_key( $page_id, $t );

				$the_id = 'archives_' . $the_id;
			}
		} elseif ( ( $this->is_real_front_page() || $this->is_front_page_by_id( $page_id ) ) || ( $this->is_admin() && $this->is_seo_settings_page( true ) ) ) {
			//* Front/HomePage.
			$the_id = $this->generate_front_page_cache_key();
		} elseif ( $this->is_blog_page( $page_id ) ) {
			$the_id = 'blog_' . $page_id;
		} elseif ( $this->is_singular() ) {

			$post_type = \get_post_type( $page_id );

			switch ( $post_type ) :
				case 'page' :
					$the_id = 'page_' . $page_id;
					break;

				case 'post' :
					$the_id = 'post_' . $page_id;
					break;

				case 'attachment' :
					$the_id = 'attach_' . $page_id;
					break;

				default :
					$the_id = 'singular_' . $page_id;
					break;
			endswitch;
		} elseif ( $this->is_search() ) {
			$query = '';

			//* TODO figure out why this check is here... admin compat maybe?
			if ( function_exists( 'get_search_query' ) ) {
				$search_query = \get_search_query( $_escaped = true );

				if ( $search_query )
					$query = str_replace( ' ', '', $search_query );

				//* Limit to 10 chars.
				if ( mb_strlen( $query ) > 10 )
					$query = mb_substr( $query, 0, 10 );

				$query = \esc_sql( $query );
			}

			//* Temporarily disable caches to prevent database spam.
			$this->the_seo_framework_use_transients = false;
			$this->use_object_cache = false;

			$the_id = $page_id . '_s_' . $query;
		}

		/**
		 * Blog page isn't set or something else is happening. Causes all kinds of problems :(
		 * Noob. :D
		 */
		if ( empty( $the_id ) )
			$the_id = 'noob_' . $page_id . '_' . $t;

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
	 * @param int|string|bool $page_id the Taxonomy or Post ID.
	 * @param string $taxonomy The term taxonomy.
	 * @param string $type The Post Type.
	 * @return string|bool String the generated cache key. Bool false on failure.
	 */
	public function generate_cache_key_by_type( $page_id, $taxonomy = '', $type = '' ) {

		switch ( $type ) :
			case 'author' :
				return $this->add_cache_key_suffix( 'author_' . $page_id );
				break;
			case 'frontpage' :
				return $this->add_cache_key_suffix( $this->generate_front_page_cache_key() );
				break;
			case 'page' :
				return $this->add_cache_key_suffix( 'page_' . $page_id );
				break;
			case 'post' :
				return $this->add_cache_key_suffix( 'post_' . $page_id );
				break;
			case 'attachment' :
				return $this->add_cache_key_suffix( 'attach_' . $page_id );
				break;
			case 'singular' :
				return $this->add_cache_key_suffix( 'singular_' . $page_id );
				break;
			case 'term' :
				return $this->add_cache_key_suffix( $this->generate_taxonomical_cache_key( $page_id, $taxonomy ) );
				break;
			default :
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
	 * @since 2.8.0 1: $locale is now static.
	 *              2: $key may now be empty.
	 * @staticvar string $locale
	 * @global string $blog_id
	 *
	 * @return string the cache key.
	 */
	protected function add_cache_key_suffix( $key = '' ) {

		static $locale = null;

		if ( is_null( $locale ) )
			$locale = strtolower( \get_locale() );

		return $key . '_' . $GLOBALS['blog_id'] . '_' . $locale;
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

		if ( empty( $type ) ) {
			if ( $this->has_page_on_front() ) {
				$type = 'page';
			} else {
				$type = 'blog';
			}
		} else {
			$type = \esc_sql( $type );
		}

		return $the_id = 'h' . $type . '_' . $this->get_the_front_page_ID();
	}

	/**
	 * Generates Cache key for taxonomical archives.
	 *
	 * @since 2.6.0
	 *
	 * @param int $page_id The taxonomy or page ID.
	 * @param string $taxonomy The taxonomy name.
	 *
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

		if ( empty( $the_id ) ) {
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

		return $cache_key = 'robots_txt_output_' . $revision . $GLOBALS['blog_id'];
	}

	/**
	 * Returns the TSF meta output Object cache key.
	 *
	 * @since 2.8.0
	 * @uses THE_SEO_FRAMEWORK_DB_VERSION as cache key buster.
	 * @see $this->get_meta_output_cache_key_by_type();
	 * @todo deprecate.
	 *
	 * @param int $id The ID. Defaults to $this->get_the_real_ID();
	 * @return string The TSF meta output cache key.
	 */
	public function get_meta_output_cache_key( $id = 0 ) {
		/**
		 * Cache key buster.
		 * Busts cache on each new db version.
		 */
		$key = $this->generate_cache_key( $id ) . '_' . THE_SEO_FRAMEWORK_DB_VERSION;

		/**
		 * Give each paged pages/archives a different cache key.
		 * @since 2.2.6
		 */
		$page = (string) $this->page();
		$paged = (string) $this->paged();

		return $cache_key = 'seo_framework_output_' . $key . '_' . $paged . '_' . $page;
	}

	/**
	 * Returns the TSF meta output Object cache key.
	 *
	 * @since 2.9.1
	 * @uses THE_SEO_FRAMEWORK_DB_VERSION as cache key buster.
	 *
	 * @param int $id The ID. Defaults to $this->get_the_real_ID();
	 * @param string $type The post type.
	 * @return string The TSF meta output cache key.
	 */
	public function get_meta_output_cache_key_by_query() {
		/**
		 * Cache key buster.
		 * Busts cache on each new db version.
		 */
		$key = $this->generate_cache_key_by_query() . '_' . THE_SEO_FRAMEWORK_DB_VERSION;

		$page = (string) $this->page();
		$paged = (string) $this->paged();

		return $cache_key = 'seo_framework_output_' . $key . '_' . $paged . '_' . $page;
	}

	/**
	 * Returns the TSF meta output Object cache key.
	 *
	 * @since 2.9.1
	 * @uses THE_SEO_FRAMEWORK_DB_VERSION as cache key buster.
	 * @uses $this->generate_cache_key_by_type()
	 * @see $this->get_meta_output_cache_key_by_query()
	 *
	 * @param int $id The ID. Defaults to $this->get_the_real_ID();
	 * @param string $taxonomy The term taxonomy
	 * @param string $type The post type.
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

		return $cache_key = 'seo_framework_output_' . $key . '_' . $_paged . '_' . $_page;
	}

	/**
	 * Delete transient on term save/deletion.
	 *
	 * @since 2.3.3
	 *
	 * @param int $term_id The Term ID
	 * @param int $tt_id The Term Taxonomy ID.
	 * @param string $taxonomy The Taxonomy type.
	 * @param mixed $deleted_term Copy of the already-deleted term. Unused.
	 */
	public function delete_auto_description_transients_term( $term_id, $tt_id, $taxonomy, $deleted_term = '' ) {

		$term_id = $term_id ?: $tt_id;

		$this->delete_cache( 'term', $term_id, array( 'term' => $taxonomy ) );
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

		$this->is_option_checked( 'cache_sitemap' ) and \delete_transient( $this->sitemap_transient );
		$this->ping_searchengines();

		return $run = true;
	}

	/**
	 * Delete transient for the automatic description for blog on save request.
	 * Returns old option, since that's passed for sanitation within WP Core.
	 *
	 * @since 2.3.3
	 * @since 2.8.0 Now listens to option 'cache_meta_description' before deleting transient.
	 * @since 2.9.1 Now no longer sets object property $this->auto_description_transient.
	 *
	 * @param string $old_option The previous blog description option.
	 * @return string Previous option.
	 */
	public function delete_auto_description_frontpage_transient( $old_option ) {

		$this->delete_auto_description_transient( $this->get_the_front_page_ID(), '', 'frontpage' );

		return $old_option;
	}

	/**
	 * Deletes transient for the automatic description on requests.
	 *
	 * @since 2.3.3
	 * @since 2.8.0 : Now listens to option 'cache_meta_description' before deleting transient.
	 * @since 2.9.1 Now no longer sets object property $this->auto_description_transient.
	 *
	 * @param mixed $page_id The page ID or identifier.
	 * @param string $taxonomy The tt name.
	 * @param string $type The Post Type
	 * @return bool true
	 */
	public function delete_auto_description_transient( $page_id, $taxonomy = '', $type = null ) {

		if ( $this->is_option_checked( 'cache_meta_description' ) ) {
			$transient = $this->get_auto_description_transient( $page_id, $taxonomy, $type );
			\delete_transient( $transient );
		}

		return true;
	}

	/**
	 * Deletes transient for the LD+Json scripts on requests.
	 *
	 * @since 2.4.2
	 * @since 2.8.0 Now listens to option 'cache_meta_schema' before deleting transient.
	 * @since 2.9.1 Now no longer sets object property $this->ld_json_transient.
	 * @since 2.9.4 Removed cache.
	 *
	 * @param mixed $page_id The page ID or identifier.
	 * @param string $taxonomy The tt name.
	 * @param string|null $type The post type.
	 * @return bool true
	 */
	public function delete_ld_json_transient( $page_id, $taxonomy = '', $type = null ) {

		if ( $this->is_option_checked( 'cache_meta_schema' ) ) {
			$transient = $this->get_ld_json_transient( $page_id, $taxonomy, $type );
			\delete_transient( $transient );
		}

		return true;
	}

	/**
	 * Delete transient for the Theme doing it Right bool on special requests.
	 *
	 * @since 2.5.2
	 * @since 2.7.0 : ???
	 *
	 * @NOTE: Ignores transient debug constant and options.
	 *
	 * @param string|object $value The theme directory stylesheet location, or either WP_Theme/WP_Upgrader instance.
	 * @param array|object|null $options If set, the update options array or the Old theme WP_Theme instance.
	 * @return bool True on success, false on failure.
	 */
	public function delete_theme_dir_transient( $value = null, $options = null ) {

		if ( isset( $options['type'] ) && 'theme' !== $options['type'] )
			return false;

		\delete_transient( $this->theme_doing_it_right_transient );

		return true;
	}

	/**
	 * Sets transient for Theme doing it Right.
	 *
	 * @since 2.5.2
	 * @since 2.7.0 : Will always set "doing it wrong" transient, even if it was "doing it right" earlier.
	 *                WordPress get_all_options will prevent multiple DB writes.
	 *                Returning false on set_transient() as it was already set to '0'.
	 *
	 * @NOTE: Ignores transient debug constant and options.
	 *
	 * @param bool $doing_it_right
	 */
	public function set_theme_dir_transient( $dir = null ) {

		if ( is_bool( $dir ) && ( false === $dir || false === \get_transient( $this->theme_doing_it_right_transient ) ) ) {

			//* Convert $dir to string 1 or 0 as transients can be false on failure.
			$dir = $dir ? '1' : '0';

			\set_transient( $this->theme_doing_it_right_transient, $dir, 0 );
		}
	}

	/**
	 * Builds and returns the excluded post IDs transient.
	 *
	 * @since 3.0.0
	 * @staticvar array $cache
	 *
	 * @return array : { 'archive', 'search' }
	 */
	public function get_excluded_ids_from_cache() {

		static $cache = null;

		if ( null === $cache )
			$cache = $this->get_transient( $this->excluded_post_ids_transient );

		if ( false === $cache ) {
			global $wpdb;
			$cache = array();

			//= Two separated equals queries are faster than a single IN with 'meta_key'.
			$cache['archive'] = $wpdb->get_results(
				$wpdb->prepare( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = '%s'", 'exclude_from_archive' )
			);
			$cache['search'] = $wpdb->get_results(
				$wpdb->prepare( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = '%s'", 'exclude_local_search' )
			);

			foreach ( array( 'archive', 'search' ) as $key ) {
				array_walk( $cache[ $key ], function( &$v ) {
					$v = $v->meta_value ? (int) $v->post_id : false;
				} );
				$cache[ $key ] = array_filter( $cache[ $key ] );
			}

			$this->set_transient( $this->excluded_post_ids_transient, $cache );
		}

		return $cache;
	}
}
