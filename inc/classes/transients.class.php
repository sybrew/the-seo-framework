<?php
/**
 * The SEO Framework plugin
 * Copyright (C) 2015 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Class AutoDescription_Transients
 *
 * Generates, stores and deletes common transients.
 *
 * @since 2.3.3
 */
class AutoDescription_Transients extends AutoDescription_Sitemaps {

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
	 * Constructor, load parent constructor and set up caches.
	 */
	public function __construct() {
		parent::__construct();

		// Setup Transient names
		add_action( 'plugins_loaded', array( $this, 'setup_transient_names' ), 10 );

		//* Delete Sitemap and Description transients on post publish/delete.
		add_action( 'publish_post', array( $this, 'delete_transients_post' ) );
		add_action( 'delete_post', array( $this, 'delete_transients_post' ) );
		add_action( 'save_post', array( $this, 'delete_transients_post' ) );

		add_action( 'edit_term', array( $this, 'delete_auto_description_transients_term' ), 10, 3 );
		add_action( 'delete_term', array( $this, 'delete_auto_description_transients_term' ), 10, 4 );

		//* Delete Sitemap transient on permalink structure change.
		add_action( 'load-options-permalink.php', array( $this, 'delete_sitemap_transient_permalink_updated' ), 20 );

		add_action( 'update_option_blogdescription', array( $this, 'delete_auto_description_blog_transient' ), 10, 1 );

	}

	/**
	 * Setup vars for transients.
	 *
	 * @since 2.3.3
	 */
	public function setup_transient_names() {
		global $blog_id;

		/**
		 * When the caching mechanism changes. Change this value.
		 *
		 * Use hex. e.g. 0, 1, 2, 9, a, b
		 */
		$revision = '2';

		$this->sitemap_transient = 'the_seo_framework_sitemap_' . (string) $revision . '_' . (string) $blog_id;
	}

	/**
	 * Setup vars for transients which require $page_id.
	 *
	 * @param int|string|bool $page_id the Taxonomy or Post ID. If false it will generate for the blog page.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @since 2.3.3
	 */
	public function setup_auto_description_transient( $page_id, $taxonomy = '' ) {

		$cache_key = $this->generate_cache_key( $page_id, $taxonomy );

		/**
		 * When the caching mechanism changes. Change this value.
		 *
		 * Use hex. e.g. 0, 1, 2, 9, a, b
		 *
		 * @since 2.3.4
		 */
		$revision = '3';

		/**
		 * Two different cache keys for two different settings.
		 *
		 * @since 2.3.4
		 */
		if ( $this->get_option( 'description_blogname' ) ) {
			$this->auto_description_transient = 'the_seo_f' . $revision . '_exc_' . $cache_key;
		} else {
			$this->auto_description_transient = 'the_seo_f' . $revision . '_exc_s_' . $cache_key;
		}

	}

	/**
	 * Setup vars for transients which require $page_id.
	 *
	 * @param int|string|bool $page_id the Taxonomy or Post ID. If false it will generate for the blog page.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @since 2.3.3
	 */
	public function setup_ld_json_transient( $page_id, $taxonomy = '' ) {

		$cache_key = $this->generate_cache_key( $page_id, $taxonomy );

		/**
		 * When the caching mechanism changes. Change this value.
		 *
		 * Use hex. e.g. 0, 1, 2, 9, a, b
		 */
		$revision = '2';

		$this->ld_json_transient = 'the_seo_f' . $revision . '_ldjs_' . $cache_key;
	}

	/**
	 * Generate transient key based on query vars.
	 *
	 * @param int|string|bool $page_id the Taxonomy or Post ID.
	 * @param string $taxonomy The Taxonomy name.
	 *
	 * @staticvar array $cached_id
	 *
	 * @global $blog_id;
	 *
	 * @since 2.3.3
	 *
	 * @return string The generated page id key.
	 */
	public function generate_cache_key( $page_id, $taxonomy = '' ) {

		static $cached_id = array();

		if ( isset( $cached_id[$page_id][$taxonomy] ) )
			return $cached_id[$page_id][$taxonomy];

		global $blog_id;

		$the_id = '';

		/**
		 * Generate home page cache key for the Home Page metabox.
		 * @since 2.4.3.1
		 */
		if ( $this->is_menu_page( $this->pagehook ) ) {
			//* We're on the SEO Settings page now.

			if ( 'posts' == get_option( 'show_on_front' ) ) {
				/**
				 * Detected home page.
				 * @since 2.3.4
				 */
				$the_id = 'hpage_' . (string) get_option( 'page_on_front' );
			} else {
				/**
				 * Detected home page.
				 * @since 2.3.4
				 */
				$the_id = 'hpage_' . (string) get_option( 'page_on_front' );
			}

		} else {
			//* All other pages, admin and front-end.

			if ( ! is_search() ) {
				if ( ( false === $page_id || is_front_page() ) && ( 'posts' == get_option( 'show_on_front' ) ) ) {
					if ( is_404() ) {
						$the_id = '_404_';
					} else {
						/**
						 * Generate for home is blog page.
						 * New transient name because of the conflicting bugfix on blog.
						 * @since 2.3.4
						 */
						$the_id = 'hblog_' . (string) get_option( 'page_on_front' );
					}
				} else if ( ( false === $page_id || is_front_page() || $page_id == get_option( 'page_on_front' ) ) && ( empty( $taxonomy ) && 'page' == get_option( 'show_on_front' ) ) ) {
					if ( is_404() ) {
						$the_id = '_404_';
					} else {
						/**
						 * Detected home page.
						 * @since 2.3.4
						 */
						$the_id = 'hpage_' . (string) get_option( 'page_on_front' );
					}
				} else if ( ! is_front_page() && empty( $taxonomy ) && ( ( $page_id == get_option( 'page_for_posts' ) && get_option( 'page_for_posts' ) != 0 ) || ( $page_id === false && did_action( 'admin_init' ) ) ) ) {
					/**
					 * Generate key for blog page that's not the home page.
					 * Bugfix
					 * @since 2.3.4
					 */
					$the_id = 'blog_' . $page_id;
				} else if ( ! is_singular() && empty( $taxonomy ) && ! did_action( 'admin_init' ) ) {
					//* Unsigned CPT and e.g. WooCommerce shop, AnsPress question, etc.

					if ( function_exists( 'is_shop' ) && is_shop() ) {
						//* WooCommerce destroys the is_page query var, let's fetch it back.
						$the_id = get_option( 'woocommerce_shop_page_id' );
					} else {
						global $wp_query;

						/**
						 * Generate for everything else.
						 * Doesn't work on admin_init action.
						 */

						$query = isset( $wp_query->query ) ? (array) $wp_query->query : null;

						/**
						 * Automatically generate transient based on query.
						 *
						 * Adjusted to comply with the 45 char limit.
						 * @since 2.3.4
						 */
						if ( isset( $query ) ) {
							$the_id = '';

							$p_id = $this->get_the_real_ID();

							// Trim key to 2 chars.
							foreach ( $query as $key => $value )
								$the_id .= substr( $key, 0, 2 ) . '_' . mb_substr( $value, 0, 2 ) . '_' . $p_id . '_';

							//* Remove final underscore
							$the_id = rtrim( $the_id, '_' );
						}
					}
				} else if ( ! is_singular() && ! empty( $taxonomy ) ) {
					//* Taxonomy

					$the_id = '';

					//* Save taxonomy name and split into words with 3 length.
					$taxonomy_name = explode( '_', $taxonomy );
					foreach ( $taxonomy_name as $name )
						$the_id .= substr( $name, 0, 3 ) . '_';

					$p_id = $page_id ? $page_id : $this->get_the_real_ID();

					//* Put it all together.
					$the_id = rtrim( $the_id, '_' ) . '_' . $p_id;
				} else if ( ! empty( $page_id ) ) {
					$the_id = $page_id;
				}
			} else {
				//* Search query.
				$query = '';

				if ( function_exists( 'get_search_query' ) ) {
					$search_query = get_search_query();

					if ( ! empty( $search_query ) )
						$query = str_replace( ' ', '', $search_query );

					//* Limit to 10 chars.
					if ( mb_strlen( $query ) > 10 )
						$query = mb_substr( $query, 0, 10 );
				}

				$the_id = $page_id . '_s_' . $query;
			}
		}

		/**
		 * Static Front page isn't set. Causes all kinds of problems :(
		 * Noob. :D
		 */
		if ( empty( $the_id ) ) {
			$the_id = 'home_noob';
		}

		/**
		 * This should be at most 25 chars. Unless the $blog_id is higher than 99,999,999.
		 * Then some cache keys will conflict on every 10th blog ID from eachother which post something on the same day..
		 * On the day archive. With the same description setting (short).
		 */
		return $cached_id[$page_id][$taxonomy] = (string) $the_id . '_' . (string) $blog_id;
	}

	/**
	 * Delete transient on post save.
	 *
	 * @since 2.2.9
	 *
	 * @return bool|null True when sitemap is flushed. False on revision. Null
	 * when sitemaps are deactivated.
	 */
	public function delete_transients_post( $post_id ) {

		$this->delete_auto_description_transient( $post_id );
		$this->delete_ld_json_transient( $post_id );

		if ( (bool) $this->get_option( 'sitemaps_output' ) !== false ) {

			//* Don't flush sitemap on revision.
			if ( wp_is_post_revision( $post_id ) )
				return false;

			$this->delete_sitemap_transient();

			return true;
		}
	}

	/**
	 * Delete transient on term save/deletion.
	 *
	 * @param int $term_id The Term ID
	 * @param int $tt_id The Term Taxonomy ID.
	 * @param string $taxonomy The Taxonomy type.
	 * @param mixed $deleted_term Copy of the already-deleted term. Unused.
	 *
	 * @since 2.3.3
	 */
	public function delete_auto_description_transients_term( $term_id, $tt_id, $taxonomy, $deleted_term = '' ) {

		$term_id = $term_id ? $term_id : $tt_id;

		$this->delete_auto_description_transient( $term_id, $taxonomy );
	}

	/**
	 * Checks wether the permalink structure is updated.
	 *
	 * @since 2.3.0
	 *
	 * @return bool Wether if sitemap transient is deleted.
	 */
	public function delete_sitemap_transient_permalink_updated() {

		if ( isset( $_POST['permalink_structure'] ) || isset( $_POST['category_base'] ) )
			return $this->delete_sitemap_transient();

		return false;
	}

	/**
	 * Delete transient for sitemap on requests.
	 * Also ping search engines.
	 *
	 * @since 2.2.9
	 *
	 * @return bool true
	 */
	public function delete_sitemap_transient() {

		delete_transient( $this->sitemap_transient );

		$this->ping_searchengines();

		return true;
	}

	/**
	 * Delete transient for the automatic description for blog on save request.
	 * Returns old option, since that's passed for sanitation within WP Core.
	 *
	 * @param string $old_option The previous blog description option.
	 *
	 * @since 2.3.3
	 *
	 * @return string Previous option.
	 */
	public function delete_auto_description_blog_transient( $old_option ) {

		$this->setup_auto_description_transient( false );

		delete_transient( $this->auto_description_transient );

		return $old_option;
	}

	/**
	 * Delete transient for the automatic description on requests.
	 *
	 * @param mixed $page_id The page ID or identifier.
	 * @param string $taxonomy The tt name.
	 *
	 * @since 2.3.3
	 *
	 * @return bool true
	 */
	public function delete_auto_description_transient( $page_id, $taxonomy = '' ) {

		$this->setup_auto_description_transient( $page_id, $taxonomy );

		delete_transient( $this->auto_description_transient );

		return true;
	}

	/**
	 * Delete transient for the LD+Json scripts on requests.
	 *
	 * @param mixed $page_id The page ID or identifier.
	 * @param string $taxonomy The tt name.
	 *
	 * @since 2.4.2
	 *
	 * @return bool true
	 */
	public function delete_ld_json_transient( $page_id, $taxonomy = '' ) {

		$flushed = null;

		if ( !isset( $flushed ) ) {
			$this->setup_ld_json_transient( $page_id, $taxonomy );

			delete_transient( $this->ld_json_transient );

			$flushed = 'Oh behave!';

			return true;
		}

		return false;
	}

}
