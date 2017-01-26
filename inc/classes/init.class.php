<?php
/**
 * @package The_SEO_Framework\Classes
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) or die;

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
			\add_action( 'wp_ajax_add-tag', array( $this, 'init_columns_ajax' ), -1 );
		}

		if ( $this->load_options ) {
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

			\add_filter( 'removable_query_args', array( $this, 'add_removable_query_args' ) );
		}
	}

	/**
	 * Initializes front-end actions.
	 * Disregards other SEO plugins, the meta output does look at detection.
	 *
	 * @since 2.5.2
	 */
	protected function init_front_end_actions() {

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
		 * @since 2.7.0 Changed priority from 999 to 9999.
		 *              Now uses another method. Was: 'search_filter'.
		 */
		\add_action( 'pre_get_posts', array( $this, 'adjust_search_filter' ), 9999, 1 );

		/**
		 * Outputs sitemap or stylesheet on request.
		 *
		 * Adding a higher priority will cause a trailing slash to be added.
		 * We need to be in front of the queue to prevent this from happening.
		 */
		\add_action( 'template_redirect', array( $this, 'maybe_output_sitemap' ), 1 );
		\add_action( 'template_redirect', array( $this, 'maybe_output_sitemap_stylesheet' ), 1 );

		if ( $this->is_singular() ) {
			//* Initialize 301 redirects.
			\add_action( 'template_redirect', array( $this, 'custom_field_redirect' ) );
		}

		if ( $this->is_feed() ) {
			//* Initialize feed alteration.
			\add_action( 'template_redirect', array( $this, 'init_feed' ) );
		}

		\add_action( 'wp_head', array( $this, 'html_output' ), 1 );
	}

	/**
	 * Runs front-end filters.
	 *
	 * @since 2.5.2
	 */
	protected function init_front_end_filters() {

		//* Edit the robots.txt file
		\add_filter( 'robots_txt', array( $this, 'robots_txt' ), 10, 2 );

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
		if ( (bool) \apply_filters( 'the_seo_framework_manipulate_title', true ) ) {
			//* Override WordPress Title
			\add_filter( 'wp_title', array( $this, 'title_from_cache' ), 9, 3 );
		}
	}

	/**
	 * Runs header actions.
	 *
	 * @since 2.2.6
	 * @uses The_SEO_Framework_Load::call_function()
	 *
	 * @param string|array $args the arguments that will be passed
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
		 * Applies filters 'the_seo_framework_before_output' : array after functions output
		 * Applies filters 'the_seo_framework_after_output' : array after functions output
		 * @param array $functions {
		 *		'callback' => string|array The function to call.
		 *		'args'     => scalar|array Arguments. When array, each key is a new argument.
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
		 * This function takes the most time anyway.
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
			if ( $this->is_404() || $this->is_search() ) :
				$output	= $this->og_locale()
						. $this->og_type()
						. $this->og_title()
						. $this->og_url()
						. $this->og_sitename()
						. $this->canonical()
						. $this->google_site_output()
						. $this->bing_site_output()
						. $this->yandex_site_output()
						. $this->pint_site_output();
			else :
				$output	= $this->the_description()
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
				$generator = '<meta name="generator" content="' . \esc_attr( $generator ) . '" />' . "\r\n";

			$output = $robots . $before . $before_actions . $output . $after_actions . $after . $generator;

			$this->use_object_cache and $this->object_cache_set( $cache_key, $output, 86400 );
		endif;

		/**
		 * Applies filters 'the_seo_framework_indicator' : Boolean
		 * Whether to show the indicator in HTML.
		 * @since 2.0.0
		 */
		$indicator = (bool) \apply_filters( 'the_seo_framework_indicator', true );

		$indicatorbefore = '';
		$indicatorafter = '';

		if ( $indicator ) :

			/**
			 * Applies filters 'the_seo_framework_indicator_timing' : Boolean
			 * Whether to show the hidden generation time in HTML.
			 * @since 2.4.0
			 */
			$timer = (bool) \apply_filters( 'the_seo_framework_indicator_timing', true );

			/**
			 * Applies filters 'sybre_waaijer_<3' : Boolean
			 * Whether to show the hidden author name in HTML.
			 * @since 2.4.0
			 */
			$sybre = (bool) \apply_filters( 'sybre_waaijer_<3', true );

			$start = \esc_html__( 'Start The Seo Framework', 'autodescription' );
			$end = \esc_html__( 'End The Seo Framework', 'autodescription' );
			$me = $sybre ? ' ' . \esc_html__( 'by Sybre Waaijer', 'autodescription' ) : '';

			$indicatorbefore = '<!-- ' . $start . $me . ' -->' . "\r\n";

			/**
			 * Calculate the plugin load time.
			 * @since 2.4.0
			 */
			if ( $timer ) {
				$indicatorafter = '<!-- ' . $end . $me . ' | ' . number_format( microtime( true ) - $init_start, 5 ) . 's' . ' -->' . "\r\n";
			} else {
				$indicatorafter = '<!-- ' . $end . $me . ' -->' . "\r\n";
			}
		endif;

		$output = "\r\n" . $indicatorbefore . $output . $indicatorafter . "\r\n";

		//* Already escaped.
		echo $output;

		\do_action( 'the_seo_framework_do_after_output' );

	}

	/**
	 * Redirects singular page to an alternate URL.
	 *
	 * @since 2.0.9
	 * @since 2.8.0 Redirect status code is now filterable.
	 *
	 * @return void early on non-singular pages.
	 */
	public function custom_field_redirect() {

		if ( $url = $this->get_custom_field( 'redirect' ) ) {

			$allow_external = $this->allow_external_redirect();

			/**
			 * Applies filters 'the_seo_framework_redirect_status_code' : Absolute integer.
			 * @since 2.8.0
			 */
			$redirect_type = absint( apply_filters( 'the_seo_framework_redirect_status_code', 301 ) );

			if ( $redirect_type > 399 || $redirect_type < 300 )
				$this->_doing_it_wrong( __METHOD__, 'You should use 3xx HTTP Status Codes. Recommended 301 and 302.', '2.8.0' );

			if ( false === $allow_external ) {
				$url = $this->set_url_scheme( $url, 'relative' );
				$url = $this->add_url_host( $url );
				$scheme = $this->is_ssl() ? 'https' : 'http';

				\wp_safe_redirect( \esc_url_raw( $url, array( $scheme ) ), $redirect_type );
				exit;
			}

			\wp_redirect( \esc_url_raw( $url ), $redirect_type );
			exit;
		}
	}

	/**
	 * Edits the robots.txt output.
	 * Requires not to have a robots.txt file in the root directory.
	 *
	 * @since 2.2.9
	 * @uses robots_txt filter located at WP core
	 *
	 * @param string $robots_txt The current robots_txt output.
	 * @param string $public The blog_public option value.
	 * @return string Robots.txt output.
	 */
	public function robots_txt( $robots_txt = '', $public = '' ) {

		/**
		 * Don't do anything if the blog isn't public
		 */
		if ( '0' === $public )
			return $robots_txt;

		if ( $this->use_object_cache ) {
			$cache_key = $this->get_robots_txt_cache_key();
			$output = $this->object_cache_get( $cache_key );
		} else {
			$output = false;
		}

		if ( false === $output ) :
			$output = '';

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
			$path = ( ! empty( $site_url['path'] ) ) ? $site_url['path'] : '';

			$output .= $pre;
			//* Output defaults
			$output .= "User-agent: *\r\n";
			$output .= "Disallow: $path/wp-admin/\r\n";
			$output .= "Allow: $path/wp-admin/admin-ajax.php\r\n";

			/**
			 * Prevents query indexing
			 * @since 2.2.9
			 *
			 * Applies filters the_seo_framework_robots_disallow_queries : Whether to allow queries for robots.
			 * @since 2.5.0
			 */
			if ( \apply_filters( 'the_seo_framework_robots_disallow_queries', false ) ) {
				$home_url = \wp_parse_url( rtrim( $this->the_home_url_from_cache(), ' /\\' ) );
				$home_path = ( ! empty( $home_url['path'] ) ) ? $home_url['path'] : '';
				$output .= "Disallow: $home_path/*?*\r\n";
			}

			$output .= $pro;

			if ( $this->get_option( 'sitemaps_robots' ) && $this->can_do_sitemap_robots() ) {
				//* Add whitespace before sitemap.
				$output .= "\r\n";

				//* Add sitemap full url
				$output .= 'Sitemap: ' . $this->the_home_url_from_cache( true ) . "sitemap.xml\r\n";
			}

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
	 * Excludes posts from search with certain metadata.
	 * For now, it only looks at 'exclude_local_search'. If it exists, the post or
	 * page will be excluded from the local Search Results.
	 *
	 * @since 2.7.0
	 *
	 * @param array $query The possible search query.
	 * @return void Early if no search query is found.
	 */
	public function adjust_search_filter( $query ) {

		// Don't exclude pages in wp-admin.
		if ( $query->is_search && false === $this->is_admin() ) {

			$q = $query->query;
			//* Only interact with an actual Search Query.
			if ( false === isset( $q['s'] ) )
				return;

			$meta_query = $query->get( 'meta_query' );

			//* Convert to array. Unset it if it's empty.
			if ( false === is_array( $meta_query ) )
				$meta_query = $meta_query ? (array) $meta_query : array();

			/**
			 * Exclude posts with exclude_local_search option on.
			 *
			 * Query is faster when the global relation is not set. Defaults to AND.
			 * Query is faster when no value is set. Defaults to 'IS NULL' because
			 *       of 'compare'. Having no effect whatsoever as it's an exclusion.
			 */
			$meta_query[] = array(
				array(
					'key'      => 'exclude_local_search',
					'type'     => 'NUMERIC',
					'compare'  => 'NOT EXISTS',
				),
			);

			$query->set( 'meta_query', $meta_query );
		}
	}
}
