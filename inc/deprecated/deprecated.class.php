<?php
/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Deprecation class.
 * Contains all deprecated functions. Is autoloaded.
 *
 * @since 2.3.4
 */
class The_SEO_Framework_Deprecated extends AutoDescription_Feed {

	/**
	 * Constructor. Load parent constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Return option from the options table and cache result.
	 *
	 * @since 2.0.0
	 *
	 * @deprecated
	 * @since 2.3.4
	 */
	public function autodescription_get_option( $key, $setting = null, $use_cache = true ) {
		$this->_deprecated_function( 'AutoDescription_Adminpages::' . __FUNCTION__, '2.3.4', 'AutoDescription_Adminpages::the_seo_framework_get_option()' );

		return $this->the_seo_framework_get_option( $key, $setting, $use_cache );
	}

	/**
	 * Enqueues JS in the admin footer
	 *
	 * @since 2.1.9
	 *
	 * @deprecated
	 * @since 2.3.3
	 *
	 * @param $hook the current page
	 */
	public function enqueue_javascript( $hook ) {
		$this->_deprecated_function( 'AutoDescription_Admin_Init::' . __FUNCTION__, '2.3.3', 'AutoDescription_Admin_Init::enqueue_admin_scripts()' );

		return $this->enqueue_admin_scripts( $hook );
	}

	/**
	 * Enqueues CSS in the admin header
	 *
	 * @since 2.1.9
	 *
	 * @deprecated
	 * @since 2.3.3
	 *
	 * @param $hook the current page
	 */
	public function enqueue_css( $hook ) {
		$this->_deprecated_function( 'AutoDescription_Admin_Init::' . __FUNCTION__, '2.3.3', 'AutoDescription_Admin_Init::enqueue_admin_scripts()' );

		return $this->enqueue_admin_scripts( $hook );
	}

	/**
	 * Setup var for sitemap transient.
	 *
	 * @since 2.2.9
	 *
	 * @deprecated
	 * @since 2.3.3
	 */
	public function fetch_sitemap_transient_name() {
		$this->_deprecated_function( 'AutoDescription_Transients::' . __FUNCTION__, '2.3.3', 'AutoDescription_Transients::$sitemap_transient' );

		return $this->sitemap_transient;
	}

	/**
	 * Delete Sitemap transient on post save.
	 *
	 * @since 2.2.9
	 *
	 * @deprecated
	 * @since 2.3.3
	 */
	public function delete_sitemap_transient_post( $post_id ) {
		$this->_deprecated_function( 'AutoDescription_Transients::' . __FUNCTION__, '2.3.3', 'AutoDescription_Transients::delete_sitemap_transient_post()' );

		return $this->delete_transients_post( $post_id );
	}

	/**
	 * Helper function for Doing it Wrong
	 *
	 * @since 2.2.4
	 *
	 * @deprecated
	 * @since 2.3.0
	 */
	public function autodescription_version( $version = '' ) {
		$this->_deprecated_function( 'The_SEO_Framework_Load::' . __FUNCTION__, '2.3.0', 'The_SEO_Framework_Load::the_seo_framework_version()' );

		return $this->the_seo_framework_version( $version );
	}

	/**
	 * Include the necessary sortable metabox scripts.
	 *
	 * @since 2.2.2
	 *
	 * @deprecated
	 * @since 2.3.5
	 */
	public function scripts() {
		$this->_deprecated_function( 'AutoDescription_Adminpages::' . __FUNCTION__, '2.3.5', 'AutoDescription_Adminpages::metabox_scripts()' );

		return $this->metabox_scripts();
	}

	/**
	 * Setup var for sitemap transient on init/admin_init.
	 *
	 * @since 2.3.3
	 * @deprecated
	 * @since 2.3.3
	 * Oops.
	 */
	public function setup_transient_names_init() {
		$this->_deprecated_function( 'AutoDescription_Transients::' . __FUNCTION__, '2.3.3', 'AutoDescription_Transients::setup_transient_names()' );

		$this->setup_transient_names();
		return false;
	}

	/**
	 * Helper function for allowed post/page screens where this plugin is active.
	 *
	 * @param array $screens The allowed screens
	 *
	 * @since 2.1.9
	 *
	 * Applies filters the_seo_framework_supported_screens : The supported administration
	 * screens where css and javascript files are loaded.
	 *
	 * @param array $args the custom supported screens.
	 *
	 * @deprecated
	 * @since 2.5.2
	 *
	 * @return array $screens
	 */
	protected function supported_screens( $args = array() ) {
		$this->_deprecated_function( 'AutoDescription_Admin_Init::' . __FUNCTION__, '2.5.2' );

		/**
		 * Instead of supporting page ID's, we support the Page base now.
		 *
		 * @since 2.3.3
		 */
		$defaults = array(
			'edit',
			'post',
			'edit-tags',
		);

		$screens = (array) apply_filters( 'the_seo_framework_supported_screens', $defaults, $args );
		$screens = wp_parse_args( $args, $screens );

		return $screens;
	}

	/**
	 * Add doing it wrong html code in the footer.
	 *
	 * @since 2.2.5
	 *
	 * @deprecated
	 * @since 2.5.2.1
	 */
	public function title_doing_it_wrong() {
		$this->_deprecated_function( 'AutoDescription_Detect::' . __FUNCTION__, '2.5.2.1', 'AutoDescription_Detect::tell_title_doing_it_wrong()' );

		return;
	}

	/**
	 * Checks a theme's support for a given feature
	 *
	 * @since 2.2.5
	 *
	 * @global array $_wp_theme_features
	 *
	 * @param string $feature the feature being checked
	 * @return bool
	 *
	 * Taken from WP Core, but it now returns true on title-tag support.
	 *
	 * @deprecated
	 * @since 2.6.0
	 */
	public function current_theme_supports( $feature ) {
		$this->_deprecated_function( 'AutoDescription_Detect::' . __FUNCTION__, '2.6.0', 'current_theme_supports()' );

		return current_theme_supports();
	}

	/**
	 * Echo debug values.
	 *
	 * @param mixed $values What to be output.
	 *
	 * @since 2.3.4
	 *
	 * @deprecated
	 * @since 2.6.0
	 */
	public function echo_debug_information( $values ) {
		$this->_deprecated_function( 'AutoDescription_Debug::' . __FUNCTION__, '2.6.0', 'AutoDescription_Debug::get_debug_information()' );

		echo $this->get_debug_information( $values );

	}

	/**
	 * Get the archive Title.
	 *
	 * WordPress core function @since 4.1.0
	 *
	 * @since 2.3.6
	 *
	 * @deprecated
	 * @since 2.6.0
	 */
	public function get_the_archive_title() {
		$this->_deprecated_function( 'AutoDescription_Generate_Description::' . __FUNCTION__, '2.6.0', 'AutoDescription_Generate_Title::get_the_real_archive_title()' );

		return $this->get_the_real_archive_title();
	}

	/**
	 * Adds the SEO Bar.
	 *
	 * @param string $column the current column    : If it's a taxonomy, this is empty
	 * @param int $post_id the post id             : If it's a taxonomy, this is the column name
	 * @param string $tax_id this is empty         : If it's a taxonomy, this is the taxonomy id
	 *
	 * @param string $status the status in html
	 *
	 * @staticvar string $type_cache
	 * @staticvar string $column_cache
	 *
	 * @since 2.1.9
	 *
	 * @deprecated
	 * @since 2.6.0
	 */
	public function seo_column( $column, $post_id, $tax_id = '' ) {
		$this->_deprecated_function( 'AutoDescription_DoingItRight::' . __FUNCTION__, '2.6.0', 'AutoDescription_DoingItRight::seo_bar()' );

		return $this->seo_bar( $column, $post_id, $tax_id );
	}

	/**
	 * Ping Yahoo
	 *
	 * @since 2.2.9
	 * @deprecated
	 * @since 2.6.0
	 */
	public function ping_yahoo() {
		$this->_deprecated_function( 'AutoDescription_Sitemaps::' . __FUNCTION__, '2.6.0', 'AutoDescription_Sitemaps::ping_bing()' );

		$this->ping_bing();
	}

	/**
	 * Create sitemap.xml content transient.
	 *
	 * @param string|bool $content required The sitemap transient content.
	 *
	 * @since 2.2.9
	 * @deprecated
	 * @since 2.6.0
	 */
	public function setup_sitemap_transient( $sitemap_content ) {
		$this->_deprecated_function( 'AutoDescription_Sitemaps::' . __FUNCTION__, '2.6.0', 'AutoDescription_Sitemaps::setup_sitemap()' );

		return $this->setup_sitemap( $sitemap_content );
	}

	/**
	 * Detect WordPress language.
	 * Considers en_UK, en_US, etc.
	 *
	 * @param string $str Required, the locale.
	 * @param bool $use_cache Set to false to bypass the cache.
	 *
	 * @staticvar array $locale
	 * @staticvar string $get_locale
	 *
	 * @since 2.3.8
	 * @deprecated
	 * @since 2.6.0
	 *
	 * @return bool
	 */
	public function is_locale( $str, $use_cache = true ) {
		$this->_deprecated_function( 'AutoDescription_Detect::' . __FUNCTION__, '2.6.0', 'AutoDescription_Detect::check_wp_locale()' );

		return $this->check_wp_locale( $str, $use_cache );
	}

	/**
	 * Build the title based on input, without tagline.
	 * Note: Not escaped.
	 *
	 * @param string $title The Title to return
	 * @param array $args : accepted args : {
	 * 		@param int term_id The Taxonomy Term ID
	 * 		@param bool placeholder Generate placeholder, ignoring options.
	 * 		@param bool page_on_front Page on front condition for example generation
	 * }
	 *
	 * @since 2.4.0
	 * @deprecated
	 * @since 2.6.0
	 *
	 * @return string Title without tagline.
	 */
	public function get_placeholder_title( $title = '', $args = array() ) {
		$this->_deprecated_function( 'AutoDescription_Generate_Title::' . __FUNCTION__, '2.6.0', 'AutoDescription_Generate_Title::title()` with the argument $args[\'notagline\']' );

		$args['notagline'] = true;
		return $this->title( $title, '', '', $args );
	}

	/**
	 * Initializes default settings very early at the after_setup_theme hook.
	 * Admin only.
	 *
	 * @since 2.5.0
	 * @deprecated
	 * @since 2.6.0
	 *
	 * @return void
	 */
	public function initialize_defaults_admin() {
		$this->_deprecated_function( 'AutoDescription_Siteoptions::' . __FUNCTION__, '2.6.0' );
		return;
	}

	/**
	 * Initializes default settings very early at the after_setup_theme hook
	 * Therefore supports is_rtl().
	 *
	 * @since 2.5.0
	 * @deprecated
	 * @since 2.6.0
	 *
	 * @return void
	 */
	public function initialize_defaults() {
		$this->_deprecated_function( 'AutoDescription_Siteoptions::' . __FUNCTION__, '2.6.0' );
		return;
	}

	/**
	 * Old style method for detecting SEO plugins.
	 *
	 * @since 2.6.1
	 * @access private
	 *
	 * @return bool
	 *
	 * @thanks StudioPress (http://www.studiopress.com/) for some code.
	 */
	public function detect_seo_plugins_old() {

		/**
		 * Applies filters 'the_seo_framework_detect_seo_plugins' : array
		 * @deprecated
		 * @since 2.6.1
		 *
		 * Use this filter to adjust plugin tests.
		 */
		$plugins_check = apply_filters(
			'the_seo_framework_detect_seo_plugins',
			//* Add to this array to add new plugin checks.
			null
		);

		if ( isset( $plugins_check ) ) {
			$this->_deprecated_function( 'the_seo_framework_detect_seo_plugins', 'the_seo_framework_conflicting_plugins', '2.6.1' );
			return $this->detect_plugin( $plugins_check );
		}

		return null;
	}


	/**
	 * Detects if plugins outputting og:type exists
	 *
	 * @note isn't used in $this->og_image() Because og:image may be output multiple times.
	 *
	 * @uses $this->detect_plugin()
	 *
	 * @since 1.3.0
	 * @return bool OG plugin detected.
	 *
	 * @staticvar bool $has_plugin
	 * @since 2.2.5
	 *
	 * @return bool $has_plugin one of the plugins has been found.
	 */
	public function has_og_plugin() {

		/**
		 * Applies filters 'the_seo_framework_detect_og_plugins' : array
		 * @since 2.6.1
		 * @deprecated
		 * @since 2.6.1 (same patch)
		 *
		 * Use this filter to adjust plugin tests.
		 */
		$plugins_check = apply_filters(
			'the_seo_framework_detect_og_plugins',
			//* Add to this array to add new plugin checks.
			null
		);

		if ( isset( $plugins_check ) ) {
			$this->_deprecated_function( 'the_seo_framework_detect_og_plugins', 'the_seo_framework_conflicting_plugins', '2.6.1' );
			return $this->detect_plugin( $plugins_check );
		}

		return null;
	}

	/**
	 * Detecs sitemap plugins
	 *
	 * @since 2.1.0
	 * @staticvar bool $detected
	 *
	 * @deprecated
	 * @since 2.6.1
	 *
	 * @return bool
	 */
	public function has_sitemap_plugin() {
		$this->_deprecated_function( 'AutoDescription_Detect::' . __FUNCTION__, 'AutoDescription_Detect::detect_sitemap_plugin', '2.6.1' );

		return $this->detect_sitemap_plugin();
	}

	/**
	 * Returns Post Type from current screen.
	 *
	 * @param bool $public Whether to only get Public Post types.
	 *
	 * @since 2.6.0
	 *
	 * @deprecated
	 * @since 2.6.1
	 *
	 * @return bool|string The Post Type
	 */
	public function get_current_post_type( $public = true ) {

		$this->_deprecated_function( 'AutoDescription_Detect::' . __FUNCTION__, 'AutoDescription_Detect::get_supported_post_type', '2.6.2' );

		static $post_type = null;

		//* Detect post type if empty or not set.
		if ( is_null( $post_type ) || empty( $post_type ) ) {
			global $current_screen;

			if ( isset( $current_screen->post_type ) ) {

				static $post_page = array();

				$args = $public ? array( 'public' => true ) : array();

				if ( ! isset( $post_page[$public] ) )
					$post_page[$public] = (array) get_post_types( $args );

				//* Smart var. This elemenates the need for a foreach loop, reducing resource usage.
				$post_type = isset( $post_page[$public][ $current_screen->post_type ] ) ? $current_screen->post_type : '';
			}
		}

		//* No post type has been found.
		if ( empty( $post_type ) )
			return false;

		return $post_type;
	}

	/**
	 * Add the WPMUdev Domain Mapping rules again. And flush them on init.
	 * Domain Mapping bugfix.
	 *
	 * @param bool $options_saved : If we're in admin and the sanitiation function runs.
	 * @param bool $flush_now : Whether to flush directly on call if not yet flushed. Only when $options_saved is false.
	 *
	 * Runs a flush and updates the site option to "true".
	 * When the site option is set to true, it not flush again on init.
	 *
	 * If $options_saved is true, it will not check for the init action hook and continue,
	 * So it will flush the next time on init.
	 *
	 * @since 2.3.0
	 * @access private
	 *
	 * @deprecated
	 * @since 2.6.3
	 */
	public function wpmudev_domainmap_flush_fix( $options_saved = false, $flush_now = true ) {

		$this->_deprecated_function( 'AutoDescription_Sitemaps::' . __FUNCTION__, '', '2.6.2' );

		if ( $this->pretty_permalinks && $this->is_domainmapping_active() ) {
			if ( $options_saved || 'init' === current_action() ) {

				if ( class_exists( 'Domainmap_Module_Cdsso' ) && defined( 'Domainmap_Module_Cdsso::SSO_ENDPOINT' ) ) {
					add_rewrite_endpoint( Domainmap_Module_Cdsso::SSO_ENDPOINT, EP_ALL );

					$name = 'tsf_wpmudev_dm_fix';
					$option = (array) get_site_option( $name, array() );
					$key = get_current_blog_id();
					$value = $this->o_plugin_updated;

					if ( $options_saved ) {
						//* Reset the flush on option change.
						if ( isset( $option[$key] ) && $value === $option[$key] ) {
							$option[$key] = false;
							update_site_option( $name, $option );
						}
					} else {
						if ( ! isset( $option[$key] ) || false === $option[$key] ) {
							//* Prevent flushing multiple times.
							$option[$key] = $value;
							update_site_option( $name, $option );

							//* Now flush
							if ( $flush_now )
								$this->flush_rewrite_rules();
							else
								$this->enqueue_rewrite_flush_other( true );
						}
					}
				}
			}
		}

	}

	/**
	 * Generates relative URL for current post_ID.
	 *
	 * @param int|object $post The post object or ID.
	 * @param bool $external Whether to fetch the WP Request or get the permalink by Post Object.
	 * @param int $depr Deprecated The post ID.
	 *
	 * @since 2.3.0
	 *
	 * @deprecated
	 * @since 2.6.5
	 *
	 * @global object $wp
	 *
	 * @return relative Post or Page url.
	 */
	public function get_relative_url( $post = null, $external = false, $depr = null ) {

		$this->_deprecated_function( 'AutoDescription_Generate_Url::' . __FUNCTION__, 'AutoDescription_Generate_Url::build_singular_relative_url()', '2.6.5' );

		if ( isset( $depr ) ) {
			$post_id = $depr;
		} else {
			if ( is_object( $post ) ) {
				if ( isset( $post->ID ) )
					$post_id = $post->ID;
			} else if ( is_scalar( $post ) ) {
				$post_id = (int) $post;
			}
		}

		if ( ! isset( $post_id ) ) {
			if ( ! $external )
				$post_id = $this->get_the_real_ID();
			else
				return '';
		}

		if ( $external || ! $this->is_home() ) {
			$permalink = get_permalink( $post_id );
		} else if ( ! $external ) {
			global $wp;

			if ( isset( $wp->request ) )
				$permalink = $wp->request;
		}

		//* No permalink found.
		if ( ! isset( $permalink ) )
			return '';

		$path = $this->set_url_scheme( $permalink, 'relative' );

		return $path;
	}
	/**
	 * Creates canonical url for the default permalink structure.
	 *
	 * @param object|int $post The post object or ID.
	 * @param bool $paged Whether to add pagination for all types.
	 * @param bool $paged_plural Whether to add pagination for the second or later page.
	 *
	 * @since 2.3.0
	 *
	 * @deprecated
	 * @since 2.6.5
	 *
	 * @return string The URL path.
	 */
	public function the_url_path_default_permalink_structure( $post = null, $paged = false, $paged_plural = true ) {

		$this->_deprecated_function( 'AutoDescription_Generate_Url::' . __FUNCTION__, 'AutoDescription_Generate_Url::build_singular_relative_url()', '2.6.5' );

		//* Don't slash it.
		$this->url_slashit = false;

		if ( false === $this->is_singular() ) {
			//* We're on a taxonomy
			$object = get_queried_object();

			if ( is_object( $object ) ) {
				if ( $this->is_category() ) {
					$path = '?cat=' . $object->term_id;
				} else if ( $this->is_tag() ) {
					$path = '?tag=' . $object->name;
				} else if ( $this->is_date() ) {
					global $wp_query;

					$query = $wp_query->query;

					$year = $query->year;
					$month = $query->monthnum ? '&monthnum=' . $query->monthnum : '';
					$day = $query->day ? '&day=' . $query->day : '';

					$path = '?year=' . $year . $month . $day;
				} else if ( $this->is_author() ) {
					$path = '?author=' . $object->author_name;
				} else if ( $this->is_tax() ) {
					$path = '?taxonomy=' . $object->taxonomy . '&term=' . $object->slug;
				} else if ( isset( $object->query_var ) && $object->query_var ) {
					$path = '?' . $object->query_var . '=' . $object->slug;
				} else {
					$path = '?p=' . $object->ID;
				}

				$paged = $this->maybe_get_paged( $this->paged(), $paged, $paged_plural );
				if ( $paged )
					$path .= '&paged=' . $paged;
			}

		}

		if ( ! isset( $path ) ) {

			if ( isset( $post ) ) {
				if ( is_object( $post ) && isset( $post->ID ) ) {
					$id = $post->ID;
				} else if ( is_scalar( $post ) ) {
					$id = $post;
				}
			}

			if ( ! isset( $id ) )
				$id = $this->get_the_real_ID();

			$path = '?p=' . $id;

			$page = $this->maybe_get_paged( $this->page(), $paged, $paged_plural );
			if ( $page )
				$path .= '&page=' . $page;
		}

		return $path;
	}

}
