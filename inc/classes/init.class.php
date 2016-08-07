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

defined( 'ABSPATH' ) or die;

/**
 * Class AutoDescription_Init
 *
 * Outputs all data in front-end header
 *
 * @since 2.1.6
 */
class AutoDescription_Init extends AutoDescription_Query {

	/**
	 * Allow object caching through a filter.
	 *
	 * @since 2.4.3
	 *
	 * @var bool Enable object caching.
	 */
	protected $use_object_cache = true;

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	private function __wakeup() { }

	/**
	 * Handle unapproachable invoked methods.
	 */
	public function __call( $name, $arguments ) {
		parent::__call( $name, $arguments );
	}

	/**
	 * Constructor. Initializes actions and loads parent constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'init', array( $this, 'autodescription_run' ), 1 );
		add_action( 'template_redirect', array( $this, 'custom_field_redirect' ) );

		/**
		 * Applies filters 'the_seo_framework_use_object_cache' : bool
		 * @since 2.4.3
		 */
		$this->use_object_cache = (bool) apply_filters( 'the_seo_framework_use_object_cache', true );

	}

	/**
	 * Runs the plugin on the front-end.
	 *
	 * @since 1.0.0
	 */
	public function autodescription_run() {

		/**
		 * Don't run in admin.
		 * Don't do anything on preview either.
		 * @since 2.2.4
		 */
		if ( $this->is_admin() || $this->is_preview() )
			return;

		$this->init_actions();
		$this->init_filters();

	}

	/**
	 * Initializes front-end actions.
	 *
	 * @since 2.5.2
	 */
	protected function init_actions() {

		//* Remove canonical header tag from WP
		remove_action( 'wp_head', 'rel_canonical' );
		//* Remove shortlink.
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		//* Remove adjecent rel tags.
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );
		//* Earlier removal of the generator tag. Doesn't require filter.
		remove_action( 'wp_head', 'wp_generator' );

		if ( $this->is_theme( 'genesis' ) ) {
			add_action( 'genesis_meta', array( $this, 'html_output' ), 5 );
		} else {
			add_action( 'wp_head', array( $this, 'html_output' ), 1 );
		}
	}

	/**
	 * Runs front-end filters.
	 *
	 * @since 2.5.2
	 */
	protected function init_filters() {

		//* Removes all pre_get_document_title filters.
		remove_all_filters( 'pre_get_document_title', false );

		//* New WordPress 4.4.0 filter. Hurray! It's also much faster :)
		add_filter( 'pre_get_document_title', array( $this, 'title_from_cache' ), 10 );
		//* Override AnsPress Theme Title
		add_filter( 'ap_title', array( $this, 'title_from_cache' ), 99, 1 );
		//* Override bbPress title
		add_filter( 'bbp_title', array( $this, 'title_from_cache' ), 99, 3 );
		//* Override Woo Themes Title
		add_filter( 'woo_title', array( $this, 'title_from_cache' ), 99 );

		/**
		 * Applies filters 'the_seo_framework_manipulate_title' : boolean
		 * Disables the title tag manipulation on old themes.
		 * @since 2.4.1
		 */
		if ( (bool) apply_filters( 'the_seo_framework_manipulate_title', true ) ) {
			//* Override WordPress Title
			add_filter( 'wp_title', array( $this, 'title_from_cache' ), 9, 3 );
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
		 * Applies filters 'the_seo_framework_before_output' : array after functions output
		 * Applies filters 'the_seo_framework_after_output' : array after functions output
		 */
		$filter_tag = $before ? 'the_seo_framework_before_output' : 'the_seo_framework_after_output';
		$filter = (array) apply_filters( $filter_tag, $functions );

		$functions = wp_parse_args( $args, $filter );

		if ( $functions && is_array( $functions ) ) {
			foreach ( $functions as $function ) {
				$arguments = isset( $function['args'] ) ? $function['args'] : '';

				if ( isset( $function['callback'] ) )
					$output .= $this->call_function( $function['callback'], '2.2.6', $arguments );

			}
		}

		return $output;
	}

	/**
	 * Echos the header meta and scripts.
	 *
	 * @since 1.0.0
	 */
	public function html_output() {

		do_action( 'the_seo_framework_do_before_output' );

		/**
		 * Start the timer here. I know it doesn't calculate the initiation of
		 * the plugin, but it will make the code smelly if I were to do so.
		 * A static array cache counter function would make it possible, but meh.
		 * This function takes the most time anyway.
		 */
		$init_start = microtime( true );
		$memory_start = $this->the_seo_framework_debug ? $this->profile( false, false, 'memory', 'html_output' ) : 0;

		/**
		 * Cache key buster
		 * Hexadecimal revision, e.g. 0, 1, 2, e, f,
		 *
		 * @busted to '2' @version 2.5.2.1
		 */
		$revision = '2';
		$the_id = $this->get_the_real_ID();
		$key = $this->generate_cache_key( $the_id ) . $revision;

		/**
		 * Give each paged pages/archives a different cache key.
		 * @since 2.2.6
		 */
		$page = (string) $this->page();
		$paged = (string) $this->paged();

		$cache_key = 'seo_framework_output_' . $key . '_' . $paged . '_' . $page;

		$output = $this->object_cache_get( $cache_key );
		if ( false === $output ) {

			$robots = $this->robots();

			/**
			 * Applies filters 'the_seo_framework_pre' : string
			 * Adds content before the output.
			 * @since 2.6.0
			 */
			$before = (string) apply_filters( 'the_seo_framework_pre', '' );

			$before_actions = $this->header_actions( '', true );

			//* Limit processing on 404 or search
			if ( $this->is_404() || $this->is_search() ) {
				$output	= $this->og_locale()
						. $this->og_type()
						. $this->og_title()
						. $this->og_url()
						. $this->og_sitename()
						. $this->canonical()
						. $this->google_site_output()
						. $this->bing_site_output()
						. $this->yandex_site_output()
						. $this->pint_site_output()
						;
			} else {
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
						. $this->pint_site_output()
						;
			}

			$after_actions = $this->header_actions( '', false );

			/**
			 * Applies filters 'the_seo_framework_pro' : string
			 * Adds content before the output.
			 * @since 2.6.0
			 */
			$after = (string) apply_filters( 'the_seo_framework_pro', '' );

			/**
			 * Applies filters 'the_seo_framework_generator_tag' : String generator tag content.
			 * @since 2.0.1
			 * @see https://wordpress.org/plugins/generator-the-seo-framework/ For an alternative.
			 */
			$generator = (string) apply_filters( 'the_seo_framework_generator_tag', '' );

			if ( '' !== $generator )
				$generator = '<meta name="generator" content="' . esc_attr( $generator ) . '" />' . "\r\n";

			$output = $robots . $before . $before_actions . $output . $after_actions . $after . $generator;

			$this->object_cache_set( $cache_key, $output, 86400 );
		}

		/**
		 * Applies filters 'the_seo_framework_indicator' : Boolean
		 * Whether to show the indicator in HTML.
		 * @since 2.0.0
		 */
		$indicator = (bool) apply_filters( 'the_seo_framework_indicator', true );

		$indicatorbefore = '';
		$indicatorafter = '';

		if ( $indicator ) {

			/**
			 * Applies filters 'the_seo_framework_indicator_timing' : Boolean
			 * Whether to show the hidden generation time in HTML.
			 * @since 2.4.0
			 */
			$timer = (bool) apply_filters( 'the_seo_framework_indicator_timing', true );

			/**
			 * Applies filters 'sybre_waaijer_<3' : Boolean
			 * Whether to show the hidden author name in HTML.
			 * @since 2.4.0
			 */
			$sybre = (bool) apply_filters( 'sybre_waaijer_<3', true );

			$start = esc_html__( 'Start The Seo Framework', 'autodescription' );
			$end = esc_html__( 'End The Seo Framework', 'autodescription' );
			$me = $sybre ? ' ' . esc_html__( 'by Sybre Waaijer', 'autodescription' ) : '';

			$indicatorbefore = '<!-- ' . $start . $me . ' -->' . "\r\n";

			/**
			 * Calculate the plugin load time.
			 * @since 2.4.0
			 */
			if ( $timer ) {
				$memory = $this->the_seo_framework_debug ? ' | ' . number_format( $this->profile( false, true, 'memory', 'html_output' ) / 1024, 2 ) . ' kiB' : '';
				$indicatorafter = '<!-- ' . $end . $me . ' | ' . number_format( microtime( true ) - $init_start, 5 ) . 's' . $memory . ' -->' . "\r\n";
			} else {
				$indicatorafter = '<!-- ' . $end . $me . ' -->' . "\r\n";
			}
		}

		$output = "\r\n" . $indicatorbefore . $output . $indicatorafter . "\r\n";

		do_action( 'the_seo_framework_do_after_output' );

		echo $output;

	}

	/**
	 * Redirects singular page to an alternate URL.
	 *
	 * @since 2.0.9
	 *
	 * @return void early on non-singular pages.
	 */
	public function custom_field_redirect() {

		//* Prevent redirect from options on uneditable pages.
		if ( false === $this->is_singular() || $this->is_admin() )
			return;

		$url = $this->get_custom_field( 'redirect' );

		if ( $url ) {

			$allow_external = $this->allow_external_redirect();
			$scheme = null;

			if ( false === $allow_external ) {
				$url = $this->set_url_scheme( $url, 'relative' );
				$url = $this->add_url_host( $url );
				$scheme = is_ssl() ? 'https' : 'http';

				wp_safe_redirect( esc_url_raw( $url, array( $scheme ) ), 301 );
				exit;
			}

			//* @TODO set scheme filter, adjustable through (multisite) extensions?
			wp_redirect( esc_url_raw( $url ), 301 );
			exit;
		}
	}
}
