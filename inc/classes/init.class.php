<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Init
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
	 * A true legacy. Ran the plugin on the front-end.
	 *
	 * @since 1.0.0
	 * @since 2.8.0 Silently deprecated. Displaying legacy roots.
	 * @deprecated
	 * @ignore
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

		/**
		 * @since 2.8.0
		 * Runs before the plugin is initialized.
		 */
		\do_action( 'the_seo_framework_init' );

		$this->init_global_actions();
		$this->init_global_filters();

		if ( \is_admin() ) {
			$this->init_admin_actions();
		} else {
			$this->init_front_end_actions();
			$this->init_front_end_filters();
		}

		/**
		 * @since 3.1.0
		 * Runs after the plugin is initialized.
		 * Use this to remove filters and actions.
		 */
		\do_action( 'the_seo_framework_after_init' );
	}

	/**
	 * Initializes the plugin front- and back-end actions.
	 *
	 * @since 2.8.0
	 */
	public function init_global_actions() {

		if ( \wp_doing_cron() )
			$this->init_cron_actions();

		if ( \wp_doing_ajax() )
			$this->init_ajax_actions();
	}

	/**
	 * Initializes the plugin front- and back-end filters.
	 *
	 * @since 2.8.0
	 */
	public function init_global_filters() {
		// Adjust category link to accommodate primary term.
		\add_filter( 'post_link_category', [ $this, '_adjust_post_link_category' ], 10, 3 );
	}

	/**
	 * Initializes cron actions.
	 *
	 * @since 2.8.0
	 * @since 4.1.2 1. Added hook for sitemap prerender.
	 *              2. Added hook for ping retry.
	 * @since 4.2.0 Is now protexted
	 * @access protected
	 */
	protected function init_cron_actions() {

		// Init post update/delete caching actions which may occur during cronjobs.
		$this->init_post_caching_actions();

		// Ping searchengines.
		if ( $this->get_option( 'ping_use_cron' ) ) {
			if ( $this->get_option( 'sitemaps_output' ) && $this->get_option( 'ping_use_cron_prerender' ) )
				\add_action( 'tsf_sitemap_cron_hook_before', [ new Builders\Sitemap\Base, 'prerender_sitemap' ] );

			\add_action( 'tsf_sitemap_cron_hook', [ Bridges\Ping::class, 'ping_search_engines' ] );
			\add_action( 'tsf_sitemap_cron_hook_retry', [ Bridges\Ping::class, 'retry_ping_search_engines' ] );
		}
	}

	/**
	 * Initializes AJAX actions.
	 *
	 * @since 4.1.4
	 */
	protected function init_ajax_actions() {

		// Admin AJAX for notice dismissal.
		\add_action( 'wp_ajax_tsf_dismiss_notice', [ Bridges\AJAX::class, '_wp_ajax_dismiss_notice' ] );

		// Admin AJAX for TSF Cropper
		\add_action( 'wp_ajax_tsf_crop_image', [ Bridges\AJAX::class, '_wp_ajax_crop_image' ] );

		// Admin AJAX for counter options.
		\add_action( 'wp_ajax_tsf_update_counter', [ Bridges\AJAX::class, '_wp_ajax_update_counter_type' ] );

		// Admin AJAX for Gutenberg SEO Bar update.
		\add_action( 'wp_ajax_tsf_update_post_data', [ Bridges\AJAX::class, '_wp_ajax_get_post_data' ] );
	}

	/**
	 * Initializes Admin Menu actions.
	 *
	 * @since 2.7.0
	 */
	public function init_admin_actions() {

		/**
		 * @since 2.8.0
		 * Runs before the plugin is initialized in the admin screens.
		 */
		\do_action( 'the_seo_framework_admin_init' );

		// Initialize caching actions.
		$this->init_post_caching_actions();

		// Delete Sitemap transient on permalink structure change.
		\add_action(
			'load-options-permalink.php',
			[ Bridges\Sitemap::class, '_refresh_sitemap_transient_permalink_updated' ],
			20
		);

		\add_action( 'activated_plugin', [ $this, 'reset_check_plugin_conflicts' ] );

		if ( ! $this->is_headless['meta'] ) {
			// Initialize term meta filters and actions.
			\add_action( 'edit_term', [ $this, '_update_term_meta' ], 10, 3 );

			// Initialize term meta filters and actions.
			\add_action( 'save_post', [ $this, '_update_post_meta' ], 1, 2 );
			\add_action( 'edit_attachment', [ $this, '_update_attachment_meta' ], 1 );
			\add_action( 'save_post', [ $this, '_save_inpost_primary_term' ], 1, 2 );

			// Enqueue Post meta boxes.
			\add_action( 'add_meta_boxes', [ $this, '_init_post_edit_view' ], 5, 1 );

			// Enqueue Term meta output.
			\add_action( 'current_screen', [ $this, '_init_term_edit_view' ] );

			// Adds post states to list view tables.
			\add_filter( 'display_post_states', [ $this, '_add_post_state' ], 10, 2 );

			// Initialize the SEO Bar for tables.
			\add_action( 'admin_init', [ $this, '_init_seo_bar_tables' ] );

			// Initialize List Edit for tables.
			\add_action( 'admin_init', [ $this, '_init_list_edit' ] );
		}

		if ( ! $this->is_headless['settings'] ) {
			// Set up site settings and allow saving resetting them.
			\add_action( 'admin_init', [ $this, 'register_settings' ], 5 );

			// Loads setting notices.
			\add_action( 'the_seo_framework_setting_notices', [ $this, '_do_settings_page_notices' ] );

			// Add menu links and register $this->seo_settings_page_hook
			\add_action( 'admin_menu', [ $this, 'add_menu_link' ] );
		}

		if ( ! $this->is_headless['user'] ) {
			// Initialize user meta filters and actions.
			\add_action( 'personal_options_update', [ $this, '_update_user_meta' ], 10, 1 );
			\add_action( 'edit_user_profile_update', [ $this, '_update_user_meta' ], 10, 1 );

			// Enqueue user meta output.
			\add_action( 'current_screen', [ $this, '_init_user_edit_view' ] );
		}

		if ( \in_array( false, $this->is_headless, true ) ) {
			// Set up notices.
			\add_action( 'admin_notices', [ $this, '_output_notices' ] );

			// Fallback HTML-only notice dismissal.
			\add_action( 'admin_init', [ $this, '_dismiss_notice' ] );

			// Enqueues admin scripts.
			\add_action( 'admin_enqueue_scripts', [ $this, '_init_admin_scripts' ], 0, 1 );
		}

		// Add plugin links to the plugin activation page.
		\add_filter(
			'plugin_action_links_' . \THE_SEO_FRAMEWORK_PLUGIN_BASENAME,
			[ '\The_SEO_Framework\Bridges\PluginTable', '_add_plugin_action_links' ],
			10,
			2
		);
		\add_filter(
			'plugin_row_meta',
			[ '\The_SEO_Framework\Bridges\PluginTable', '_add_plugin_row_meta' ],
			10,
			2
		);

		/**
		 * @since 2.9.4
		 * Runs after the plugin is initialized in the admin screens.
		 * Use this to remove actions.
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
		 * Runs before the plugin is initialized on the front-end.
		 */
		\do_action( 'the_seo_framework_front_init' );

		// Remove canonical header tag from WP
		\remove_action( 'wp_head', 'rel_canonical' );

		// Remove shortlink.
		\remove_action( 'wp_head', 'wp_shortlink_wp_head' );

		// Remove adjacent rel tags.
		\remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );

		// Earlier removal of the generator tag. Doesn't require filter.
		\remove_action( 'wp_head', 'wp_generator' );

		// Prepares sitemap or stylesheet output.
		if ( $this->can_run_sitemap() ) {
			\add_action( 'parse_request', [ $this, '_init_sitemap' ], 15 );
			\add_filter( 'wp_sitemaps_enabled', '__return_false' );
		} else {
			// Augment Core sitemaps. Can't hook into `wp_sitemaps_init` as we're augmenting the providers before that.
			// It's not a bridge, don't treat it like one: clean me up?
			\add_filter( 'wp_sitemaps_add_provider', [ Builders\CoreSitemaps\Main::class, '_filter_add_provider' ], 9, 2 );
			\add_filter( 'wp_sitemaps_max_urls', [ Builders\CoreSitemaps\Main::class, '_filter_max_urls' ], 9 );
			// We miss the proper hooks. https://github.com/sybrew/the-seo-framework/issues/610#issuecomment-1300191500
			\add_filter( 'wp_sitemaps_posts_query_args', [ Builders\CoreSitemaps\Main::class, '_trick_filter_doing_sitemap' ], 11 );
		}

		// Initialize 301 redirects.
		\add_action( 'template_redirect', [ $this, '_init_custom_field_redirect' ] );

		// Prepares requisite robots headers to avoid low-quality content penalties.
		$this->prepare_robots_headers();

		\add_action( 'the_seo_framework_before_meta_output', [ $this, '_do_deprecated_output_hooks_before' ], 5 );
		\add_action( 'the_seo_framework_after_meta_output', [ $this, '_do_deprecated_output_hooks_after' ], 15 );

		// Output meta tags.
		\add_action( 'wp_head', [ $this, 'html_output' ], 1 );

		if ( $this->get_option( 'alter_archive_query' ) )
			$this->init_alter_archive_query();

		if ( $this->get_option( 'alter_search_query' ) )
			$this->init_alter_search_query();

		// Modify the feed.
		if ( $this->get_option( 'excerpt_the_feed' ) || $this->get_option( 'source_the_feed' ) ) {
			// We could use actions 'do_feed_{$feed}', but I don't trust its variability.
			// We could use action 'rss_tag_pre', but I don't trust its availability.
			\add_action( 'template_redirect', [ $this, '_init_feed' ], 1 );
		}

		/**
		 * @since 2.9.4
		 * Runs before the plugin is initialized on the front-end.
		 * Use this to remove actions.
		 */
		\do_action( 'the_seo_framework_after_front_init' );
	}

	/**
	 * Runs front-end filters.
	 *
	 * @since 2.5.2
	 */
	protected function init_front_end_filters() {

		// Overwrite the robots.txt file
		\add_filter( 'robots_txt', [ $this, 'robots_txt' ], 10, 2 );

		/**
		 * @since 2.9.3
		 * @param bool $overwrite_titles Whether to enable title overwriting.
		 */
		if ( \apply_filters( 'the_seo_framework_overwrite_titles', true ) ) {
			// Removes all pre_get_document_title filters.
			\remove_all_filters( 'pre_get_document_title', false );

			// New WordPress 4.4.0 filter. Hurray! It's also much faster :)
			\add_filter( 'pre_get_document_title', [ $this, 'get_document_title' ], 10 );

			/**
			 * @since 2.4.1
			 * @param bool $overwrite_titles Whether to enable legacy title overwriting.
			 *
			 * TODO remove this block? -- it's been 7 years...
			 * <https://make.wordpress.org/core/2015/10/20/document-title-in-4-4/>
			 */
			if ( \apply_filters( 'the_seo_framework_manipulate_title', true ) ) {
				\remove_all_filters( 'wp_title', false );
				// Override WordPress Title
				\add_filter( 'wp_title', [ $this, 'get_wp_title' ], 9 );
				// Override WooThemes Title TODO move this to wc compat file.
				\add_filter( 'woo_title', [ $this, 'get_document_title' ], 99 );
			}
		}

		/**
		 * @since 4.1.4
		 * @param bool $kill_core_robots Whether you lack sympathy for rocks tricked to think.
		 */
		if ( \apply_filters( 'the_seo_framework_kill_core_robots', true ) ) {
			\remove_filter( 'wp_robots', 'wp_robots_max_image_preview_large' );
			// Reconsider readding this to "supported" queries only?
			\remove_filter( 'wp_robots', 'wp_robots_noindex_search' );
		}

		if ( $this->get_option( 'og_tags' ) ) { // independent from filter at use_og_tags--let that be deciding later.
			// Disable Jetpack's Open Graph tags. But Sybre, compat files? Yes.
			\add_filter( 'jetpack_enable_open_graph', '__return_false' );
		}

		if ( $this->get_option( 'twitter_tags' ) ) { // independent from filter at use_twitter_tags--let that be deciding later.
			// Disable Jetpack's Twitter Card tags. But Sybre, compat files? Maybe.
			\add_filter( 'jetpack_disable_twitter_cards', '__return_true' );
			// Future, maybe. See <https://github.com/Automattic/jetpack/issues/13146#issuecomment-516841698>
			// \add_filter( 'jetpack_enable_twitter_cards', '__return_false' );
		}

		if ( ! $this->get_option( 'oembed_scripts' ) ) {
			/**
			 * Only hide the scripts, don't permeably purge them. This should be enough.
			 *
			 * This will still allow embedding within WordPress Multisite via WP-REST's proxy, since WP won't look for a script.
			 * We'd need to empty 'oembed_response_data' in that case... However, thanks to a bug in WP, this 'works' anyway.
			 * The bug: WP_oEmbed_Controller::get_proxy_item_permissions_check() always returns \WP_Error.
			 */
			\remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		}
		/**
		 * WordPress also filters this at priority '10', but it's registered before this runs.
		 * Careful, WordPress can switch blogs when this filter runs. So, run this always,
		 * and assess options (uncached!) therein.
		 */
		\add_filter( 'oembed_response_data', [ $this, '_alter_oembed_response_data' ], 10, 2 );
	}

	/**
	 * Outputs deprecated output hooks.
	 *
	 * @since 4.2.0
	 * @access private
	 * @TODO delete me. v5.0.0+
	 */
	public function _do_deprecated_output_hooks_before() {
		// phpcs:disable, WordPress.Security.EscapeOutput -- Everything we produce is escaped.
		/**
		 * @since 2.6.0
		 * @since 4.2.0 Deprecated.
		 * @param string $before The content before the SEO output.
		 */
		echo \apply_filters_deprecated(
			'the_seo_framework_pre',
			[ '' ],
			'4.2.0 of The SEO Framework',
			'Action the_seo_framework_before_meta_output'
		);

		/**
		 * @since 2.2.6
		 * @since 4.2.0 Deprecated
		 * @param array $functions {
		 *    'callback' => string|array The function to call.
		 *    'args'     => scalar|array Arguments. When array, each key is a new argument.
		 * }
		 */
		$functions = (array) \apply_filters_deprecated(
			'the_seo_framework_before_output',
			[ [] ],
			'4.2.0 of The SEO Framework',
			'Action the_seo_framework_before_meta_output'
		);

		foreach ( $functions as $function ) {
			if ( ! empty( $function['callback'] ) )
				echo \call_user_func_array( $function['callback'], [ ( $function['args'] ?? null ) ] );
		}
		// phpcs:enable, WordPress.Security.EscapeOutput
	}

	/**
	 * Outputs deprecated output hooks.
	 *
	 * @since 4.2.0
	 * @access private
	 * @TODO delete me. v5.0.0+
	 */
	public function _do_deprecated_output_hooks_after() {
		// phpcs:disable, WordPress.Security.EscapeOutput -- Everything we produce is escaped.
		/**
		 * @since 2.2.6
		 * @since 4.2.0 Deprecated
		 * @param array $functions {
		 *    'callback' => string|array The function to call.
		 *    'args'     => scalar|array Arguments. When array, each key is a new argument.
		 * }
		 */
		$functions = (array) \apply_filters_deprecated(
			'the_seo_framework_after_output',
			[ [] ],
			'4.2.0 of The SEO Framework',
			'Action the_seo_framework_after_meta_output'
		);

		foreach ( $functions as $function ) {
			if ( ! empty( $function['callback'] ) )
				echo \call_user_func_array( $function['callback'], [ ( $function['args'] ?? null ) ] );
		}

		/**
		 * @since 2.6.0
		 * @since 4.2.0 Deprecated.
		 * @param string $after The content after the SEO output.
		 */
		echo \apply_filters_deprecated(
			'the_seo_framework_pro',
			[ '' ],
			'4.2.0 of The SEO Framework',
			'Action the_seo_framework_after_meta_output'
		);
		// phpcs:enable, WordPress.Security.EscapeOutput
	}

	/**
	 * Echos the header meta and scripts.
	 *
	 * @since 1.0.0
	 * @since 2.8.0 Cache is busted on each new release.
	 * @since 3.0.0 Now converts timezone if needed.
	 * @since 3.1.0 1. Now no longer outputs anything on preview.
	 *              2. Now no longer outputs anything on blocked post types.
	 * @since 4.0.0 Now no longer outputs anything on Customizer.
	 * @since 4.0.4 1. Now sets timezone to UTC to fix WP 5.3 bug <https://core.trac.wordpress.org/ticket/48623>
	 *              2. Now always sets timezone regardless of settings, because, again, bug.
	 * @since 4.2.0 No longer sets timezone.
	 * @since 4.2.7 No longer marked as private.
	 */
	public function html_output() {

		if ( $this->is_preview() || \is_customize_preview() || ! $this->query_supports_seo() ) return;

		/**
		 * @since 2.6.0
		 */
		\do_action( 'the_seo_framework_do_before_output' );

		/**
		 * The bootstrap timer keeps adding when metadata is strapping.
		 * This causes both timers to increase simultaneously.
		 * We catch the bootstrap here, and let the meta-timer take over.
		 */
		$bootstrap_timer = _bootstrap_timer();
		/**
		 * Start the meta timer here. This also catches file inclusions,
		 * which is also caught by the _bootstrap_timer().
		 */
		$init_start = hrtime( true );

		// phpcs:disable, WordPress.Security.EscapeOutput -- Output is escaped.
		echo "\n", $this->get_plugin_indicator( 'before' );

		$this->do_meta_output();

		echo $this->get_plugin_indicator(
			'after',
			( hrtime( true ) - $init_start ) / 1e9,
			$bootstrap_timer
		), "\n";
		// phpcs:enable, WordPress.Security.EscapeOutput

		/**
		 * @since 2.6.0
		 */
		\do_action( 'the_seo_framework_do_after_output' );
	}

	/**
	 * Outputs all meta tags for the current query.
	 *
	 * @since 4.1.4
	 * @since 4.2.0 1. Now invokes two actions before and after output.
	 *              2. No longer rectifies timezones.
	 */
	public function do_meta_output() {

		/**
		 * @since 4.2.0
		 */
		\do_action( 'the_seo_framework_before_meta_output' );

		// Limit processing and redundant tags on 404 and search.
		switch ( true ) {
			case $this->is_search():
				$get = [
					'robots',
					'og_locale',
					'og_type',
					'og_title',
					'og_url',
					'og_sitename',
					'theme_color',
					'shortlink',
					'canonical',
					'paged_urls',
					'google_site_output',
					'bing_site_output',
					'yandex_site_output',
					'baidu_site_output',
					'pint_site_output',
				];
				break;
			case \is_404():
				$get = [
					'robots',
					'theme_color',
					'google_site_output',
					'bing_site_output',
					'yandex_site_output',
					'baidu_site_output',
					'pint_site_output',
				];
				break;
			case $this->is_query_exploited():
				// search and 404 cannot be exploited.
				$get = [
					'robots',
					'advanced_query_protection',
				];
				break;
			default:
				$get = [
					'robots',
					'the_description',
					'og_image',
					'og_locale',
					'og_type',
					'og_title',
					'og_description',
					'og_url',
					'og_sitename',
					'og_updated_time',
					'facebook_publisher',
					'facebook_author',
					'facebook_app_id',
					'article_published_time',
					'article_modified_time',
					'twitter_card',
					'twitter_site',
					'twitter_creator',
					'twitter_title',
					'twitter_description',
					'twitter_image',
					'theme_color',
					'shortlink',
					'canonical',
					'paged_urls',
					'ld_json',
					'google_site_output',
					'bing_site_output',
					'yandex_site_output',
					'baidu_site_output',
					'pint_site_output',
				];
		}

		// TODO add filter to $get? It won't last a few major updates though...
		// But that's why I created this method like so... anyway... tough luck.
		// phpcs:ignore, WordPress.Security.EscapeOutput -- Everything we produce is escaped.
		foreach ( $get as $method ) echo $this->{$method}();

		/**
		 * @since 4.2.0
		 */
		\do_action( 'the_seo_framework_after_meta_output' );
	}

	/**
	 * Redirects singular page to an alternate URL.
	 *
	 * @since 2.9.0
	 * @since 3.1.0 1. Now no longer redirects on preview.
	 *              2. Now listens to post type settings.
	 * @since 4.0.0 1. No longer tries to redirect on "search".
	 *              2. Added term redirect support.
	 *              3. No longer redirects on Customizer.
	 * @access private
	 *
	 * @return void early on non-singular pages.
	 */
	public function _init_custom_field_redirect() {

		if ( $this->is_preview() || \is_customize_preview() || ! $this->query_supports_seo() ) return;

		$url = $this->get_redirect_url();

		if ( $url ) {
			/**
			 * @since 4.1.2
			 * @param string $url The URL we're redirecting to.
			 */
			\do_action( 'the_seo_framework_before_redirect', $url );

			$this->do_redirect( $url );
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

		// All WP defined protocols are allowed.
		$url = \esc_url_raw( $url );

		if ( empty( $url ) ) {
			$this->_doing_it_wrong( __METHOD__, 'You need to supply an input URL.', '2.9.0' );
			return;
		}

		/**
		 * @since 2.8.0
		 * @param int <unsigned> $redirect_type
		 */
		$redirect_type = \absint( \apply_filters( 'the_seo_framework_redirect_status_code', 301 ) );

		if ( $redirect_type > 399 || $redirect_type < 300 )
			$this->_doing_it_wrong( __METHOD__, 'You should use 3xx HTTP Status Codes. Recommended 301 and 302.', '2.8.0' );

		if ( ! $this->allow_external_redirect() ) {
			// Only HTTP/HTTPS and home URLs are allowed.
			$path = $this->set_url_scheme( $url, 'relative' );
			$url  = \trailingslashit( $this->get_home_host() ) . ltrim( $path, ' /' );

			// Maintain current request's scheme.
			$scheme = $this->is_ssl() ? 'https' : 'http';

			\wp_safe_redirect( $this->set_url_scheme( $url, $scheme ), $redirect_type );
			exit;
		}

		// phpcs:ignore, WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- intended feature. Disable via $this->allow_external_redirect().
		\wp_redirect( $url, $redirect_type );
		exit;
	}

	/**
	 * Initializes various callbacks on post-status changing actions to flush caches.
	 *
	 * @see WP Core wp_transition_post_status()
	 * @since 4.3.0
	 * @see $this->init_admin_actions();
	 * @see $this->init_cron_actions();
	 *
	 * @return void Early if already called.
	 */
	protected function init_post_caching_actions() {

		if ( has_run( __METHOD__ ) ) return;

		$refresh_sitemap_callback = [ Bridges\Cache::class, '_refresh_sitemap_on_post_change' ];

		// Can-be cron actions.
		\add_action( 'publish_post', $refresh_sitemap_callback );
		\add_action( 'publish_page', $refresh_sitemap_callback );

		// Other actions.
		\add_action( 'deleted_post', $refresh_sitemap_callback );
		\add_action( 'deleted_page', $refresh_sitemap_callback );
		\add_action( 'post_updated', $refresh_sitemap_callback );
		\add_action( 'page_updated', $refresh_sitemap_callback );

		$clear_excluded_callback = [ Bridges\Cache::class, 'clear_excluded_post_ids_cache' ];

		// Excluded IDs cache.
		\add_action( 'wp_insert_post', $clear_excluded_callback );
		\add_action( 'attachment_updated', $clear_excluded_callback );
	}

	/**
	 * Prepares sitemap output.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	public function _init_sitemap() {
		Bridges\Sitemap::get_instance()->_init();
	}

	/**
	 * Prepares feed modifications.
	 *
	 * @since 4.1.0
	 * @access private
	 */
	public function _init_feed() {
		\is_feed() and new Bridges\Feed;
	}

	/**
	 * Edits the robots.txt output.
	 * Requires the site not to have a robots.txt file in the root directory.
	 *
	 * This methods completely hijacks default output, intentionally.
	 *
	 * The robots.txt file should be left as default, so to improve SEO.
	 * The Robots Exclusion Protocol encourages you not to use this file for
	 * non-administrative endpoints. Use the robots meta tags (and headers) instead.
	 *
	 * @since 2.2.9
	 * @since 2.9.3 Casts $public to string for check.
	 * @since 4.0.5 1. The output is now filterable.
	 *              2. Improved invalid location test.
	 *              3. No longer shortcircuits on non-public sites.
	 *              4. Now marked as private. Will be renamed to `_robots_txt()` in the future.
	 * @since 4.1.0 Now adds the WordPress Core sitemap URL.
	 * @since 4.1.2 Now only adds the WP Core sitemap URL when the provider tells us it's enabled.
	 * @since 4.1.4 Removed object caching support.
	 * @uses robots_txt filter located at WP core
	 * @access private
	 * @TODO extrapolate the contents without a warning to get_robots_txt(). Forward filter to it.
	 *       See Monitor extension.
	 * @TODO rework into a workable standard...
	 *
	 * @param string $robots_txt The current robots_txt output. Not used.
	 * @param string $public The blog_public option value.
	 * @return string Robots.txt output.
	 */
	public function robots_txt( $robots_txt = '', $public = '' ) {

		$site_path = \esc_attr( parse_url( \site_url(), \PHP_URL_PATH ) ) ?: '';

		/**
		 * @since 2.5.0
		 * @param string $pre The output before this plugin's output.
		 *                    Don't forget to add line breaks ( "\n" )!
		 */
		$output = (string) \apply_filters( 'the_seo_framework_robots_txt_pre', '' );

		// Output defaults
		$output .= "User-agent: *\n";
		$output .= "Disallow: $site_path/wp-admin/\n";
		$output .= "Allow: $site_path/wp-admin/admin-ajax.php\n";

		/**
		 * @since 2.5.0
		 * @param bool $disallow Whether to disallow robots queries.
		 */
		if ( \apply_filters( 'the_seo_framework_robots_disallow_queries', false ) )
			$output .= "Disallow: /*?*\n";

		/**
		 * @since 2.5.0
		 * @param string $pro The output after this plugin's output.
		 *                    Don't forget to add line breaks ( "\n" )!
		 */
		$output .= (string) \apply_filters( 'the_seo_framework_robots_txt_pro', '' );

		// Add extra whitespace and sitemap full URL
		if ( $this->get_option( 'sitemaps_robots' ) ) {
			if ( $this->get_option( 'sitemaps_output' ) ) {
				$sitemaps = Bridges\Sitemap::get_instance();

				foreach ( $sitemaps->get_sitemap_endpoint_list() as $id => $data )
					if ( ! empty( $data['robots'] ) )
						$output .= sprintf( "\nSitemap: %s", \esc_url( $sitemaps->get_expected_sitemap_endpoint_url( $id ) ) );

				$output .= "\n";
			} elseif ( ! $this->detect_sitemap_plugin() ) { // detect_sitemap_plugin() temp backward compat.
				if ( $this->use_core_sitemaps() ) {
					$wp_sitemaps_server = \wp_sitemaps_get_server();
					if ( method_exists( $wp_sitemaps_server, 'add_robots' ) ) {
						// This method augments the output--it doesn't overwrite it.
						$output = \wp_sitemaps_get_server()->add_robots( $output, $public );
					}
				}
			}
		}

		$raw_uri = rawurldecode(
			\wp_check_invalid_utf8(
				stripslashes( $_SERVER['REQUEST_URI'] )
			)
		) ?: '/robots.txt';

		// Simple test for invalid directory depth. Even //robots.txt is an invalid location.
		if ( strrpos( $raw_uri, '/' ) > 0 ) {
			$error  = sprintf(
				"%s\n%s\n\n",
				'# This is an invalid robots.txt location.',
				'# Please visit: ' . \esc_url( \trailingslashit( $this->set_preferred_url_scheme( $this->get_home_host() ) ) . 'robots.txt' )
			);
			$output = "$error$output";
		}

		/**
		 * The robots.txt output.
		 *
		 * @since 4.0.5
		 * @param string $output The (cached) robots.txt output.
		 */
		return (string) \apply_filters( 'the_seo_framework_robots_txt', $output );
	}

	/**
	 * Prepares the X-Robots-Tag headers for various endpoints.
	 *
	 * @since 4.0.2
	 */
	protected function prepare_robots_headers() {

		\add_action( 'template_redirect', [ $this, '_init_robots_headers' ] );
		\add_action( 'the_seo_framework_sitemap_header', [ $this, '_output_robots_noindex_headers' ] );

		// This is not necessarily a WordPress query. Test it inline.
		if ( \defined( 'XMLRPC_REQUEST' ) && \XMLRPC_REQUEST )
			$this->_output_robots_noindex_headers();
	}

	/**
	 * Sets the X-Robots-Tag headers on various endpoints.
	 *
	 * @since 4.0.0
	 * @since 4.0.5 Added filter.
	 * @access private
	 */
	public function _init_robots_headers() {

		/**
		 * @since 4.0.5
		 * @param bool $noindex Whether a noindex header must be set.
		 */
		if ( \apply_filters(
			'the_seo_framework_set_noindex_header',
			\is_robots() || ( ! $this->get_option( 'index_the_feed' ) && \is_feed() )
		) ) $this->_output_robots_noindex_headers();
	}

	/**
	 * Sets the X-Robots tag headers to 'noindex'.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	public function _output_robots_noindex_headers() {
		headers_sent() or header( 'X-Robots-Tag: noindex', true );
	}

	/**
	 * Initializes search query adjustments.
	 *
	 * @since 2.9.4
	 */
	public function init_alter_search_query() {
		switch ( $this->get_option( 'alter_search_query_type' ) ) {
			case 'post_query':
				\add_filter( 'the_posts', [ $this, '_alter_search_query_post' ], 10, 2 );
				break;

			case 'in_query':
			default:
				\add_action( 'pre_get_posts', [ $this, '_alter_search_query_in' ], 9999, 1 );
		}
	}

	/**
	 * Initializes archive query adjustments.
	 *
	 * @since 2.9.4
	 */
	public function init_alter_archive_query() {
		switch ( $this->get_option( 'alter_archive_query_type' ) ) {
			case 'post_query':
				\add_filter( 'the_posts', [ $this, '_alter_archive_query_post' ], 10, 2 );
				break;

			case 'in_query':
			default:
				\add_action( 'pre_get_posts', [ $this, '_alter_archive_query_in' ], 9999, 1 );
		}
	}

	/**
	 * Alters search query.
	 *
	 * @since 2.9.4
	 * @since 3.0.0 Exchanged meta query for post__not_in query.
	 * @see Twenty Fourteen theme @source \Featured_Content::pre_get_posts()
	 * @access private
	 *
	 * @param \WP_Query $wp_query The WP_Query instance.
	 * @return void Early if no search query is found.
	 */
	public function _alter_search_query_in( $wp_query ) {

		// Don't exclude pages in wp-admin.
		if ( $wp_query->is_search ) {
			// Only interact with an actual Search Query.
			if ( ! isset( $wp_query->query['s'] ) )
				return;

			if ( $this->is_query_adjustment_blocked( $wp_query ) )
				return;

			$excluded = $this->get_excluded_ids_from_cache()['search'];

			if ( ! $excluded )
				return;

			$post__not_in = $wp_query->get( 'post__not_in' );

			if ( ! empty( $post__not_in ) ) {
				$excluded = array_unique(
					array_merge( (array) $post__not_in, $excluded )
				);
			}

			$wp_query->set( 'post__not_in', $excluded );
		}
	}

	/**
	 * Alters archive query.
	 *
	 * @since 2.9.4
	 * @since 3.0.0 Exchanged meta query for post__not_in query.
	 * @see Twenty Fourteen theme @source \Featured_Content::pre_get_posts()
	 * @access private
	 *
	 * @param \WP_Query $wp_query The WP_Query instance.
	 * @return void Early if query alteration is useless or blocked.
	 */
	public function _alter_archive_query_in( $wp_query ) {

		if ( $wp_query->is_archive || $wp_query->is_home ) {
			if ( $this->is_query_adjustment_blocked( $wp_query ) )
				return;

			$excluded = $this->get_excluded_ids_from_cache()['archive'];

			if ( ! $excluded )
				return;

			$post__not_in = $wp_query->get( 'post__not_in' );

			if ( ! empty( $post__not_in ) ) {
				$excluded = array_unique(
					array_merge( (array) $post__not_in, $excluded )
				);
			}

			$wp_query->set( 'post__not_in', $excluded );
		}
	}

	/**
	 * Alters search results after database query.
	 *
	 * @since 2.9.4
	 * @access private
	 *
	 * @param array     $posts    The array of retrieved posts.
	 * @param \WP_Query $wp_query The WP_Query instance.
	 * @return array $posts
	 */
	public function _alter_search_query_post( $posts, $wp_query ) {

		if ( $wp_query->is_search ) {
			if ( $this->is_query_adjustment_blocked( $wp_query ) )
				return $posts;

			foreach ( $posts as $n => $post ) {
				if ( $this->get_post_meta_item( 'exclude_local_search', $post->ID ) )
					unset( $posts[ $n ] );
			}
			// Reset numeric index.
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
	 * @param array     $posts    The array of retrieved posts.
	 * @param \WP_Query $wp_query The WP_Query instance.
	 * @return array $posts
	 */
	public function _alter_archive_query_post( $posts, $wp_query ) {

		if ( $wp_query->is_archive || $wp_query->is_home ) {
			if ( $this->is_query_adjustment_blocked( $wp_query ) )
				return $posts;

			foreach ( $posts as $n => $post ) {
				if ( $this->get_post_meta_item( 'exclude_from_archive', $post->ID ) )
					unset( $posts[ $n ] );
			}
			// Reset numeric index.
			$posts = array_values( $posts );
		}

		return $posts;
	}

	/**
	 * Determines whether the archive query adjustment is blocked.
	 *
	 * We do NOT treat this feature with security: If a post still slips through
	 * a query, then so be it. The post may be accessed anyway, otherwise,
	 * if not redirected. This last part is of concern, however, because one
	 * might think the contents of a post is hidden thanks to the redirect, for it
	 * to be exposable via other means. Nevertheless, we never (and won't ever)
	 * redirect REST queries, which may access post content regardless of user settings.
	 *
	 * Perhaps, we should add a disclaimer: Even IF you redirect the post, noindex it,
	 * exclude it from search and archive queries, the post content may still be readable
	 * to the public.
	 *
	 * @since 2.9.4
	 * @since 3.1.0 Now checks for the post type.
	 * @since 4.1.4 1. Renamed from `is_archive_query_adjustment_blocked()`
	 *              2. Added taxonomy-supported lookups.
	 *              3. Added WP Rest checks for the Block Editor.
	 * @since 4.2.0 Improved supported taxonomy loop.
	 * @since 4.2.6 Added check for `did_action( 'wp_loaded' )` early, before queries are tested and cached.
	 * @since 4.2.7 No longer affects the sitemap query.
	 *
	 * @param \WP_Query $wp_query WP_Query object.
	 * @return bool
	 */
	protected function is_query_adjustment_blocked( $wp_query ) {

		static $has_filter = null;

		if ( null === $has_filter )
			$has_filter = \has_filter( 'the_seo_framework_do_adjust_archive_query' );

		if ( $has_filter ) {
			/**
			 * This filter affects both 'search-"archives"' and terms/taxonomies.
			 *
			 * @since 2.9.4
			 * @param bool      $do       True is unblocked (do adjustment), false is blocked (don't do adjustment).
			 * @param \WP_Query $wp_query The current query.
			 */
			if ( ! \apply_filters_ref_array( 'the_seo_framework_do_adjust_archive_query', [ true, $wp_query ] ) )
				return true;
		}

		if ( ! \did_action( 'wp_loaded' ) )
			return true;

		if ( \defined( 'REST_REQUEST' ) && \REST_REQUEST ) {
			$referer = \wp_get_referer();
			if ( str_contains( $referer, 'post.php' ) || str_contains( $referer, 'post-new.php' ) ) {
				/**
				 * WordPress should've authenthicated the user at
				 * WP_REST_Server::check_authentication() -> rest_cookie_check_errors() -> wp_nonce et al.
				 * before executing the query. For REST_REQUEST can not be true otherwise. Ergo,
				 * \current_user_can() should work. If it returns true, we can trust it's a safe request.
				 * If it returns false, the user may still be logged in, but the request isn't sent via
				 * WordPress's API with the proper nonces supplied. This is as perfect as it can be.
				 */
				if ( \current_user_can( 'edit_posts' ) )
					return true;
			}
		}

		// If doing sitemap, don't adjust query via query settings.
		if ( $this->is_sitemap() )
			return true;

		// This should primarily affect 'terms'. Test if TSF is blocked from supporting said terms.
		if ( ! empty( $wp_query->tax_query->queries ) ) {
			$supported = true;

			foreach ( $wp_query->tax_query->queries as $_query ) {
				if ( isset( $_query['taxonomy'] ) ) {
					$supported = $this->is_taxonomy_supported( $_query['taxonomy'] );
					// If just one tax is supported for this query, greenlight it: all must be blocking.
					if ( $supported ) break;
				}
			}

			if ( ! $supported )
				return true;
		}

		return false;
	}

	/**
	 * Alters the oEmbed response data.
	 *
	 * @WARNING: WordPress can switch blogs as this filter runs. So, check all options again, without cache!
	 *           This should only happen at `/oembed/1.0/proxy`.
	 * @since 4.0.5
	 * @since 4.1.1 Now also alters titles and images.
	 * @access private
	 *
	 * @param array    $data   The response data.
	 * @param \WP_Post $post   The post object.
	 * @return array Possibly altered $data.
	 */
	public function _alter_oembed_response_data( $data, $post ) {

		// Don't use cache. See @WARNING in doc comment.
		if ( $this->get_option( 'oembed_use_og_title', false ) )
			$data['title'] = $this->get_open_graph_title( [ 'id' => $post->ID ] ) ?: $data['title'];

		// Don't use cache. See @WARNING in doc comment.
		if ( $this->get_option( 'oembed_use_social_image', false ) ) {
			$image_details = current( $this->get_image_details(
				[ 'id' => $post->ID ],
				true,
				'oembed',
				true
			) );

			if ( $image_details && $image_details['url'] && $image_details['width'] && $image_details['height'] ) {
				// Override WordPress provided data.
				$data['thumbnail_url']    = $image_details['url'];
				$data['thumbnail_width']  = $image_details['width'];
				$data['thumbnail_height'] = $image_details['height'];
			}
		}

		// Don't use cache. See @WARNING in doc comment.
		if ( $this->get_option( 'oembed_remove_author', false ) )
			unset( $data['author_url'], $data['author_name'] );

		return $data;
	}
}
