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
 * Class The_SEO_Framework\Init
 *
 * Outputs all data in front-end header
 *
 * @since 2.8.0
 */
class Init extends Query {

	/**
	 * Allow object caching through a filter.
	 *
	 * @since 2.4.3
	 *
	 * @var bool Enable object caching.
	 */
	protected $use_object_cache = true;

	/**
	 * Constructor. Initializes actions and loads parent constructor.
	 */
	protected function __construct() {
		parent::__construct();

		/**
		 * Applies filters 'the_seo_framework_load_options' : Boolean Allows the options page to be removed
		 * @since 2.2.2
		 */
		$this->load_options = (bool) \apply_filters( 'the_seo_framework_load_options', true );

		/**
		 * Applies filters 'the_seo_framework_use_object_cache' : bool
		 * @since 2.4.3
		 * @since 2.8.0 : Uses method $this->use_object_cache() as default.
		 */
		$this->use_object_cache = (bool) \apply_filters( 'the_seo_framework_use_object_cache', $this->use_object_cache() );

		//* Determines Whether we're using pretty permalinks.
		$this->pretty_permalinks = '' !== $this->permalink_structure();

		\add_action( 'init', array( $this, 'init_the_seo_framework' ), 0 );

		$this->load_early_compat_files();
	}

	/**
	 * Runs the plugin on the front-end.
	 *
	 * @since 1.0.0
	 * @since 2.8.0 Silently deprecated. Displaying legacy roots.
	 * @deprecated
	 */
	public function autodescription_run() {
		$this->init_the_seo_framework();
	}

	/**
	 * Initializes the plugin actions and filters.
	 *
	 * @since 2.8.0
	 */
	public function init_the_seo_framework() {

		//* Don't initialize cache or cause other issues on preview.
		if ( $this->is_preview() )
			return;

		\do_action( 'the_seo_framework_init' );

		$this->init_global_actions();
		$this->init_global_filters();

		if ( $this->is_admin() ) {
			$this->init_admin_actions();
		} else {
			$this->init_front_end_actions();
			$this->init_front_end_filters();
		}
	}

	/**
	 * Initializes the plugin front- and back-end actions.
	 *
	 * @since 2.8.0
	 */
	public function init_global_actions() {

		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			$this->init_cron_actions();
		}

		//* Add query strings for sitemap rewrite.
		\add_action( 'init', array( $this, 'rewrite_rule_sitemap' ), 1 );

		//* Enqueue sitemap rewrite flush
		\add_action( 'shutdown', array( $this, 'maybe_flush_rewrite' ), 999 );
	}

	/**
	 * Initializes the plugin front- and back-end filters.
	 *
	 * @since 2.8.0
	 */
	public function init_global_filters() {

		//* Add query strings for sitemap rewrite.
		\add_filter( 'query_vars', array( $this, 'enqueue_sitemap_query_vars' ), 1, 1 );
	}

	/**
	 * Initializes cron actions.
	 *
	 * @since 2.8.0
	 */
	public function init_cron_actions() {

		//* Flush post cache.
		$this->init_post_cache_actions();

	}

	/**
	 * Initializes Admin Menu actions.
	 *
	 * @since 2.7.0
	 */
	public function init_admin_actions() {

		/**
		 * @since 2.8.0
		 */
		\do_action( 'the_seo_framework_admin_init' );

		//* Initialize caching actions.
		$this->init_admin_caching_actions();

		//* Save post data.
		\add_action( 'save_post', array( $this, 'inpost_seo_save' ), 1, 2 );

		//* Enqueues admin scripts.
		\add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ), 0, 1 );

		//* Add plugin links to the plugin activation page.
		\add_filter( 'plugin_action_links_' . THE_SEO_FRAMEWORK_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ), 10, 2 );

		//* Initialize post states.
		\add_action( 'current_screen', array( $this, 'post_state' ) );

		if ( $this->is_option_checked( 'display_seo_bar_tables' ) ) {
			//* Initialize columns.
			\add_action( 'current_screen', array( $this, 'init_columns' ) );

			//* Ajax handlers for columns.
			\add_action( 'wp_ajax_add-tag', array( $this, '_init_columns_wp_ajax_add_tag' ), -1 );
			\add_action( 'wp_ajax_inline-save', array( $this, '_init_columns_wp_ajax_inline_save' ), -1 );
			\add_action( 'wp_ajax_inline-save-tax', array( $this, '_init_columns_wp_ajax_inline_save_tax' ), -1 );
		}

		if ( $this->load_options ) :
			// Enqueue i18n defaults.
			\add_action( 'admin_init', array( $this, 'enqueue_page_defaults' ), 1 );

			//* Set up site settings and save/reset them
			\add_action( 'admin_init', array( $this, 'register_settings' ), 5 );

			//* Load the SEO admin page content and handlers.
			\add_action( 'admin_init', array( $this, 'settings_init' ), 10 );

			//* Update site options at plugin update.
			\add_action( 'admin_init', array( $this, 'site_updated_plugin_option' ), 30 );

			//* Enqueue Inpost meta boxes.
			\add_action( 'add_meta_boxes', array( $this, 'add_inpost_seo_box_init' ), 5 );

			//* Enqueue Taxonomy meta output.
			\add_action( 'current_screen', array( $this, 'add_taxonomy_seo_box_init' ), 10 );

			// Add menu links and register $this->seo_settings_page_hook
			\add_action( 'admin_menu', array( $this, 'add_menu_link' ) );

			// Set up notices
			\add_action( 'admin_notices', array( $this, 'notices' ) );

			// Load nessecary assets
			\add_action( 'admin_init', array( $this, 'load_assets' ) );

			//* Admin AJAX for counter options.
			\add_action( 'wp_ajax_the_seo_framework_update_counter', array( $this, 'wp_ajax_update_counter_type' ) );

			//* Admin AJAX for TSF Cropper
			\add_action( 'wp_ajax_tsf-crop-image', array( $this, 'wp_ajax_crop_image' ) );

			// Add extra removable query arguments to the list.
			\add_filter( 'removable_query_args', array( $this, 'add_removable_query_args' ) );
		endif;

		/**
		 * @since 2.9.4
		 */
		\do_action( 'the_seo_framework_after_admin_init' );
	}

	/**
	 * Initializes front-end actions.
	 * Disregards other SEO plugins, the meta output does look at detection.
	 *
	 * WARNING: Do not use query functions here.
	 *
	 * @since 2.5.2
	 */
	protected function init_front_end_actions() {

		/**
		 * @since 2.8.0
		 */
		\do_action( 'the_seo_framework_front_init' );

		//* Remove canonical header tag from WP
		\remove_action( 'wp_head', 'rel_canonical' );

		//* Remove shortlink.
		\remove_action( 'wp_head', 'wp_shortlink_wp_head' );

		//* Remove adjecent rel tags.
		\remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );

		//* Earlier removal of the generator tag. Doesn't require filter.
		\remove_action( 'wp_head', 'wp_generator' );

		/**
		 * Outputs sitemap or stylesheet on request.
		 *
		 * Adding a higher priority will cause a trailing slash to be added.
		 * We need to be in front of the queue to prevent this from happening.
		 */
		\add_action( 'template_redirect', array( $this, 'maybe_output_sitemap' ), 1 );
		\add_action( 'template_redirect', array( $this, 'maybe_output_sitemap_stylesheet' ), 1 );

		//* Initialize 301 redirects.
		\add_action( 'template_redirect', array( $this, '_init_custom_field_redirect' ) );

		//* Initialize feed alteration.
		\add_action( 'template_redirect', array( $this, '_init_feed_output' ) );

		//* Output meta tags.
		\add_action( 'wp_head', array( $this, 'html_output' ), 1 );

		if ( $this->is_option_checked( 'alter_archive_query' ) )
			$this->init_alter_archive_query();

		if ( $this->is_option_checked( 'alter_search_query' ) )
			$this->init_alter_search_query();

		/**
		 * @since 2.9.4
		 */
		\do_action( 'the_seo_framework_after_front_init' );
	}

	/**
	 * Runs front-end filters.
	 *
	 * @since 2.5.2
	 */
	protected function init_front_end_filters() {

		//* Edit the robots.txt file
		\add_filter( 'robots_txt', array( $this, 'robots_txt' ), 10, 2 );

		/**
		 * Applies filters 'the_seo_framework_overwrite_titles'
		 *
		 * @since 2.9.3
		 * @param bool $overwrite_titles
		 */
		$overwrite_titles = \apply_filters( 'the_seo_framework_overwrite_titles', true );

		if ( $overwrite_titles ) {
			//* Removes all pre_get_document_title filters.
			\remove_all_filters( 'pre_get_document_title', false );

			//* New WordPress 4.4.0 filter. Hurray! It's also much faster :)
			\add_filter( 'pre_get_document_title', array( $this, 'title_from_cache' ), 10 );
			//* Override AnsPress Theme Title
			\add_filter( 'ap_title', array( $this, 'title_from_cache' ), 99, 1 );
			//* Override Woo Themes Title
			\add_filter( 'woo_title', array( $this, 'title_from_cache' ), 99 );

			/**
			 * Applies filters 'the_seo_framework_manipulate_title' : boolean
			 * Disables the title tag manipulation on old themes.
			 * @since 2.4.1
			 */
			if ( \apply_filters( 'the_seo_framework_manipulate_title', true ) ) {
				//* Override WordPress Title
				\add_filter( 'wp_title', array( $this, 'title_from_cache' ), 9, 3 );
			}
		}
	}

	/**
	 * Runs header actions.
	 *
	 * @since 2.2.6
	 * @uses The_SEO_Framework_Load::call_function()
	 *
	 * @param string|array $args the arguments that will be passed onto the callback.
	 * @param bool $before if the header actions should be before or after the SEO Frameworks output
	 * @return string|empty The filter output.
	 */
	public function header_actions( $args = '', $before = true ) {

		$output = '';

		//* Placeholder callback and args.
		$functions = array();

		/**
		 * @since 2.2.6
		 *
		 * Applies filters 'the_seo_framework_before_output' : array before functions output
		 * Applies filters 'the_seo_framework_after_output' : array after functions output
		 * @param array $functions {
		 *    'callback' => string|array The function to call.
		 *    'args'     => scalar|array Arguments. When array, each key is a new argument.
		 * }
		 */
		$filter_tag = $before ? 'the_seo_framework_before_output' : 'the_seo_framework_after_output';
		$filter = (array) \apply_filters( $filter_tag, $functions );

		$functions = \wp_parse_args( $args, $filter );

		if ( $functions && is_array( $functions ) ) :
			foreach ( $functions as $function ) :
				$arguments = isset( $function['args'] ) ? $function['args'] : '';

				if ( isset( $function['callback'] ) )
					$output .= $this->call_function( $function['callback'], '2.2.6', $arguments );
			endforeach;
		endif;

		return $output;
	}

	/**
	 * Echos the header meta and scripts.
	 *
	 * @since 1.0.0
	 * @since 2.8.0 Cache is busted on each new release.
	 */
	public function html_output() {

		\do_action( 'the_seo_framework_do_before_output' );

		/**
		 * Start the timer here. I know it doesn't calculate the initiation of
		 * the plugin, but it will make the code smelly if I were to do so.
		 * A static array cache counter function would make it possible, but meh.
		 * This function presumably takes the most time anyway.
		 */
		$init_start = microtime( true );

		if ( $this->use_object_cache ) {
			$cache_key = $this->get_meta_output_cache_key();
			$output = $this->object_cache_get( $cache_key );
		} else {
			$cache_key = '';
			$output = false;
		}

		if ( false === $output ) :

			$robots = $this->robots();

			/**
			 * Applies filters 'the_seo_framework_pre' : string
			 * Adds content before the output and caches it through Object caching.
			 * @since 2.6.0
			 */
			$before = (string) \apply_filters( 'the_seo_framework_pre', '' );

			$before_actions = $this->header_actions( '', true );

			//* Limit processing on 404 or search
			if ( $this->is_search() ) :
				$output = $this->og_locale()
						. $this->og_type()
						. $this->og_title()
						. $this->og_url()
						. $this->og_sitename()
						. $this->canonical()
						. $this->google_site_output()
						. $this->bing_site_output()
						. $this->yandex_site_output()
						. $this->pint_site_output();
			elseif ( $this->is_404() ) :
				$output = $this->google_site_output()
						. $this->bing_site_output()
						. $this->yandex_site_output()
						. $this->pint_site_output();
			else :
				$output = $this->the_description()
						. $this->og_image()
						. $this->og_locale()
						. $this->og_type()
						. $this->og_title()
						. $this->og_description()
						. $this->og_url()
						. $this->og_sitename()
						. $this->facebook_publisher()
						. $this->facebook_author()
						. $this->facebook_app_id()
						. $this->article_published_time()
						. $this->article_modified_time()
						. $this->twitter_card()
						. $this->twitter_site()
						. $this->twitter_creator()
						. $this->twitter_title()
						. $this->twitter_description()
						. $this->twitter_image()
						. $this->shortlink()
						. $this->canonical()
						. $this->paged_urls()
						. $this->ld_json()
						. $this->google_site_output()
						. $this->bing_site_output()
						. $this->yandex_site_output()
						. $this->pint_site_output();
			endif;

			$after_actions = $this->header_actions( '', false );

			/**
			 * Applies filters 'the_seo_framework_pro' : string
			 * Adds content after the output and caches it through Object caching.
			 * @since 2.6.0
			 */
			$after = (string) \apply_filters( 'the_seo_framework_pro', '' );

			/**
			 * Applies filters 'the_seo_framework_generator_tag' : String generator tag content.
			 * @since 2.0.1
			 * @see https://wordpress.org/plugins/generator-the-seo-framework/ For an alternative.
			 */
			$generator = (string) \apply_filters( 'the_seo_framework_generator_tag', '' );

			if ( $generator )
				$generator = '<meta name="generator" content="' . \esc_attr( $generator ) . '" />' . PHP_EOL;

			$output = $robots . $before . $before_actions . $output . $after_actions . $after . $generator;

			$this->use_object_cache and $this->object_cache_set( $cache_key, $output, DAY_IN_SECONDS );
		endif;

		$output = $this->get_plugin_indicator( 'before' )
				. $output
				. $this->get_plugin_indicator( 'after', $init_start );

		//* Already escaped.
		echo PHP_EOL . $output . PHP_EOL;

		\do_action( 'the_seo_framework_do_after_output' );

	}

	/**
	 * Redirects singular page to an alternate URL.
	 *
	 * @since 2.9.0
	 * @access private
	 *
	 * @return void early on non-singular pages.
	 */
	public function _init_custom_field_redirect() {

		if ( $this->is_singular() ) {
			$url = $this->get_custom_field( 'redirect' );
			$url && $this->do_redirect( $url );
		}
	}

	/**
	 * Redirects vistor to input $url.
	 *
	 * @since 2.9.0
	 *
	 * @param string $url The redirection URL
	 * @return void Early if no URL is supplied.
	 */
	public function do_redirect( $url = '' ) {

		if ( 'template_redirect' !== \current_action() ) {
			$this->_doing_it_wrong( __METHOD__, 'Only use this method on action "template_redirect".', '2.9.0' );
			return;
		}

		//= All WP defined protocols are allowed.
		$url = \esc_url_raw( $url );

		if ( empty( $url ) ) {
			$this->_doing_it_wrong( __METHOD__, 'You need to supply an input URL.', '2.9.0' );
			return;
		}

		$allow_external = $this->allow_external_redirect();

		/**
		 * Applies filters 'the_seo_framework_redirect_status_code' : Absolute integer.
		 *
		 * @since 2.8.0
		 *
		 * @param unsigned int $redirect_type
		 */
		$redirect_type = \absint( \apply_filters( 'the_seo_framework_redirect_status_code', 301 ) );

		if ( $redirect_type > 399 || $redirect_type < 300 )
			$this->_doing_it_wrong( __METHOD__, 'You should use 3xx HTTP Status Codes. Recommended 301 and 302.', '2.8.0' );

		if ( false === $allow_external ) {
			//= Only HTTP/HTTPS and internal URLs are allowed.
			$url = $this->set_url_scheme( $url, 'relative' );
			$url = $this->add_url_host( $url );
			$scheme = $this->is_ssl() ? 'https' : 'http';

			\wp_safe_redirect( $this->set_url_scheme( $url, $scheme ), $redirect_type );
			exit;
		}

		\wp_redirect( $url, $redirect_type );
		exit;
	}

	/**
	 * Edits the robots.txt output.
	 * Requires not to have a robots.txt file in the root directory.
	 *
	 * This methods completely hijacks default output, intentionally.
	 * The robots.txt file should be left as default, so to improve SEO.
	 * The Robots Exclusion Protocol encourages you not to use this file for
	 * non-administrative endpoints.
	 *
	 * @since 2.2.9
	 * @since 2.9.3 Casts $public to string for check.
	 * @uses robots_txt filter located at WP core
	 *
	 * @param string $robots_txt The current robots_txt output.
	 * @param string $public The blog_public option value.
	 * @return string Robots.txt output.
	 */
	public function robots_txt( $robots_txt = '', $public = '' ) {

		/**
		 * Don't do anything if the blog isn't public.
		 */
		if ( '0' === (string) $public )
			return $robots_txt;

		if ( $this->use_object_cache ) {
			$cache_key = $this->get_robots_txt_cache_key();
			$output = $this->object_cache_get( $cache_key );
		} else {
			$output = false;
		}

		if ( false === $output ) :
			$output = '';

			$parsed_home_url = \wp_parse_url( rtrim( $this->the_home_url_from_cache(), ' /\\' ) );
			$home_path = ! empty( $parsed_home_url['path'] ) ? \esc_attr( $parsed_home_url['path'] ) : '';

			if ( $this->is_subdirectory_installation() || $home_path ) {
				$output .= '# This is an invalid robots.txt location.' . "\r\n";
				$output .= '# Please visit: ' . \esc_url( trailingslashit( $this->set_preferred_url_scheme( $this->get_home_host() ) ) . 'robots.txt' ) . "\r\n";
				$output .= "\r\n";
			}

			/**
			 * Apply filters the_seo_framework_robots_txt_pre & the_seo_framework_robots_txt_pro : string
			 * Adds custom cacheable lines.
			 * Don't forget to add line breaks ( "\r\n" || PHP_EOL )
			 *
			 * @since 2.5.0
			 */
			$pre = (string) \apply_filters( 'the_seo_framework_robots_txt_pre', '' );
			$pro = (string) \apply_filters( 'the_seo_framework_robots_txt_pro', '' );

			$site_url = \wp_parse_url( \site_url() );
			$site_path = ( ! empty( $site_url['path'] ) ) ? \esc_attr( $site_url['path'] ) : '';

			$output .= $pre;
			//* Output defaults
			$output .= "User-agent: *\r\n";
			$output .= "Disallow: $site_path/wp-admin/\r\n";
			$output .= "Allow: $site_path/wp-admin/admin-ajax.php\r\n";

			/**
			 * Applies filters the_seo_framework_robots_disallow_queries : boolean
			 * Determines whether to allow queries for robots.
			 * @since 2.5.0
			 */
			if ( \apply_filters( 'the_seo_framework_robots_disallow_queries', false ) ) {
				$output .= "Disallow: $home_path/*?*\r\n";
			}

			$output .= $pro;

			//* Add extra whitespace and sitemap full URL
			if ( $this->can_do_sitemap_robots( true ) )
				$output .= "\r\nSitemap: " . \esc_url( $this->get_sitemap_xml_url() ) . "\r\n";

			$this->use_object_cache and $this->object_cache_set( $cache_key, $output, 86400 );
		endif;

		/**
		 * Completely override robots with output.
		 * @since 2.5.0
		 */
		$robots_txt = $output;

		return $robots_txt;
	}

	/**
	 * Initializes search query adjustments.
	 *
	 * @since 2.9.4
	 */
	public function init_alter_search_query() {

		$type = $this->get_option( 'alter_search_query_type' );

		switch ( $type ) :
			case 'post_query' :
				\add_filter( 'the_posts', array( $this, '_alter_search_query_post' ), 10, 2 );
				break;

			default :
			case 'in_query' :
				\add_action( 'pre_get_posts', array( $this, '_alter_search_query_in' ), 9999, 1 );
				break;
		endswitch;
	}

	/**
	 * Initializes archive query adjustments.
	 *
	 * @since 2.9.4
	 */
	public function init_alter_archive_query() {

		$type = $this->get_option( 'alter_archive_query_type' );

		switch ( $type ) :
			case 'post_query' :
				\add_filter( 'the_posts', array( $this, '_alter_archive_query_post' ), 10, 2 );
				break;

			default :
			case 'in_query' :
				\add_action( 'pre_get_posts', array( $this, '_alter_archive_query_in' ), 9999, 1 );
				break;
		endswitch;
	}

	/**
	 * Alters search query.
	 *
	 * @since 2.9.4
	 *
	 * @param WP_Query $wp_query The WP_Query instance.
	 * @return void Early if no search query is found.
	 */
	public function _alter_search_query_in( $wp_query ) {

		// Don't exclude pages in wp-admin.
		if ( $wp_query->is_search ) {
			//* Only interact with an actual Search Query.
			if ( ! isset( $wp_query->query['s'] ) )
				return;

			if ( $this->is_archive_query_adjustment_blocked( $wp_query ) )
				return;

			$meta_query = $wp_query->get( 'meta_query' );

			//* Convert to array. Unset it if it's empty.
			if ( ! is_array( $meta_query ) )
				$meta_query = $meta_query ? (array) $meta_query : array();

			/**
			 * Exclude posts with exclude_local_search option on.
			 *
			 * Query is faster when the global relation is not set. Defaults to AND.
			 * Query is faster when no value is set. Defaults to 'IS NULL' because
			 *       of 'compare'. Having no effect whatsoever as it's an exclusion.
			 */
			$meta_query[] = array(
				'key'      => 'exclude_local_search',
				'type'     => 'NUMERIC',
				'compare'  => 'NOT EXISTS',
			);

			$wp_query->set( 'meta_query', $meta_query );
		}
	}

	/**
	 * Alters archive query.
	 *
	 * @since 2.9.4
	 * @access private
	 *
	 * @param WP_Query $wp_query The WP_Query instance.
	 * @return void Early if query alteration is useless or blocked.
	 */
	public function _alter_archive_query_in( $wp_query ) {

		if ( $wp_query->is_archive || $wp_query->is_home ) {
			if ( $this->is_archive_query_adjustment_blocked( $wp_query ) )
				return;

			$meta_query = $wp_query->get( 'meta_query' );

			//* Convert to array. Unset it if it's empty.
			if ( ! is_array( $meta_query ) )
				$meta_query = $meta_query ? (array) $meta_query : array();

			/**
			 * Exclude posts with exclude_from_archive option on.
			 *
			 * Query is faster when the global relation is not set. Defaults to AND.
			 * Query is faster when no value is set. Defaults to 'IS NULL' because
			 *       of 'compare'. Having no effect whatsoever as it's an exclusion.
			 */
			$meta_query[] = array(
				'key'      => 'exclude_from_archive',
				'type'     => 'NUMERIC',
				'compare'  => 'NOT EXISTS',
			);

			$wp_query->set( 'meta_query', $meta_query );
		}

		/* @TODO exchange above with this 3.0+
		if ( ! empty( $wp_query->is_archive ) || ! empty( $wp_query->is_home ) ) {

			$excluded = $this->get_exclude_from_archive_ids_cache();

			if ( ! $excluded )
				return;

			$post__not_in = $wp_query->get( 'post__not_in' );

			if ( ! empty( $post__not_in ) ) {
				$excluded = array_merge( (array) $post__not_in, $excluded );
				$excluded = array_unique( $excluded );
			}

			$wp_query->set( 'post__not_in', $excluded );
		}
		*/
	}

	/**
	 * Alters search results after database query.
	 *
	 * @since 2.9.4
	 * @access private
	 *
	 * @param array    $posts The array of retrieved posts.
	 * @param WP_Query $wp_query The WP_Query instance.
	 * @return array $posts
	 */
	public function _alter_search_query_post( $posts, $wp_query ) {

		if ( $wp_query->is_search ) {
			if ( $this->is_archive_query_adjustment_blocked( $wp_query ) )
				return $posts;

			foreach ( $posts as $n => $post ) {
				if ( $this->get_custom_field( 'exclude_local_search', $post->ID ) ) {
					unset( $posts[ $n ] );
				}
			}
			//= Reset numeric index.
			$posts = array_values( $posts );
		}

		return $posts;
	}

	/**
	 * Alters archive results after database query.
	 *
	 * @since 2.9.4
	 * @access private
	 *
	 * @param array    $posts The array of retrieved posts.
	 * @param WP_Query $wp_query The WP_Query instance.
	 * @return array $posts
	 */
	public function _alter_archive_query_post( $posts, $wp_query ) {

		if ( $wp_query->is_archive || $wp_query->is_home ) {
			if ( $this->is_archive_query_adjustment_blocked( $wp_query ) )
				return $posts;

			foreach ( $posts as $n => $post ) {
				if ( $this->get_custom_field( 'exclude_from_archive', $post->ID ) ) {
					unset( $posts[ $n ] );
				}
			}
			//= Reset numeric index.
			$posts = array_values( $posts );
		}

		return $posts;
	}

	/**
	 * Determines whether the archive query adjustment is blocked.
	 *
	 * @since 2.9.4
	 *
	 * @param WP_Query $wp_query WP_Query object. Passed by reference.
	 * @return bool
	 */
	protected function is_archive_query_adjustment_blocked( &$wp_query ) {

		static $has_filter = null;

		if ( null === $has_filter ) {
			$has_filter = \has_filter( 'the_seo_framework_do_adjust_archive_query' );
		}
		if ( $has_filter ) {
			/**
			 * Applies filters 'the_seo_framework_do_adjust_archive_query' : boolean
			 *
			 * @since 2.9.4
			 *
			 * @param bool   $do Whether to execute adjustment.
			 * @param object $wp_query The current query. Passed by reference.
			 */
			if ( ! \apply_filters_ref_array( 'the_seo_framework_do_adjust_archive_query', array( true, &$wp_query ) ) )
				return true;
		}

		return false;
	}
}
