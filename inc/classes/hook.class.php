<?php
/**
 * @package The_SEO_Framework\Classes\Hook
 * @subpackage The_SEO_Framework\Hook
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\is_headless;

use \The_SEO_Framework\Helper;
use \The_SEO_Framework\Helper\Query;
use \The_SEO_Framework\Internal\Debug;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Hooks into everything WordPress.
 *
 * @since 4.3.0
 * @access private
 * @internal
 */
final class Hook {

	/**
	 * Setups the plugin.
	 *
	 * @since 4.3.0
	 */
	public static function setup() {

		if ( \THE_SEO_FRAMEWORK_DEBUG )
			Hook::init_debug();

		// Register the required settings capability early.
		\add_filter(
			'option_page_capability_' . \THE_SEO_FRAMEWORK_SITE_OPTIONS,
			static fn() => \THE_SEO_FRAMEWORK_SETTINGS_CAP
		);

		// This is not necessarily a WordPress query. Test it inline.
		if ( \defined( 'XMLRPC_REQUEST' ) && \XMLRPC_REQUEST )
			Helper\Headers::output_robots_noindex_headers();

		\add_action( 'init', [ static::class, 'init_tsf' ], 0 );
	}

	/**
	 * Initializes debugging functionality.
	 *
	 * @since 4.3.0
	 */
	public static function init_debug() {
		\add_action( 'the_seo_framework_do_before_output', [ Debug::class, '_set_debug_query_output_cache' ] );
		\add_action( 'admin_footer', [ Debug::class, '_do_debug_output' ] );
		\add_action( 'wp_footer', [ Debug::class, '_do_debug_output' ] );
	}

	/**
	 * Initializes the plugin actions and filters.
	 *
	 * @since 2.8.0
	 * @since 4.3.0 Moved to `\The_SEO_Framework\Hook`.
	 */
	public static function init_tsf() {
		/**
		 * @since 2.8.0
		 * Runs before the plugin is initialized.
		 */
		\do_action( 'the_seo_framework_init' );

		if ( \wp_doing_cron() )
			static::init_cron_actions();

		static::init_global_filters();

		if ( \is_admin() ) {
			static::init_admin_actions();
		} else {
			static::init_front_end_actions();
			static::init_front_end_filters();
		}

		/**
		 * @since 3.1.0
		 * Runs after the plugin is initialized.
		 * Use this to remove filters and actions.
		 */
		\do_action( 'the_seo_framework_after_init' );
	}

	/**
	 * Initializes cron actions.
	 *
	 * @since 2.8.0
	 * @since 4.1.2 1. Added hook for sitemap prerender.
	 *              2. Added hook for ping retry.
	 * @since 4.2.0 Is now protexted.
	 * @since 4.3.0 1. Is now public.
	 *              2. Moved to `\The_SEO_Framework\Hook`.
	 */
	public static function init_cron_actions() {

		// Init post update/delete caching actions which may occur during cronjobs.
		static::init_post_caching_actions();

		// Ping searchengines.
		if ( Data\Plugin::get_option( 'ping_use_cron' ) ) {
			if (
				   Data\Plugin::get_option( 'sitemaps_output' )
				&& Data\Plugin::get_option( 'ping_use_cron_prerender' )
			) {
				\add_action(
					'tsf_sitemap_cron_hook_before',
					[ new Sitemap\Optimized\Base, 'prerender_sitemap' ] // var_dump() make static
				);
			}

			\add_action( 'tsf_sitemap_cron_hook', [ Sitemap\Ping::class, 'ping_search_engines' ] );
			\add_action( 'tsf_sitemap_cron_hook_retry', [ Sitemap\Ping::class, 'retry_ping_search_engines' ] );
		}
	}

	/**
	 * Initializes the plugin front- and back-end filters.
	 *
	 * @since 2.8.0
	 * @since 4.3.0 1. Is now public.
	 *              2. Moved to `\The_SEO_Framework\Hook`.
	 */
	public static function init_global_filters() {
		// Adjust category link to accommodate primary term.
		\add_filter( 'post_link_category', [ Query\Filter::class, 'filter_post_link_category' ], 10, 3 );

		// Overwrite the robots.txt output.
		\add_filter( 'robots_txt', [ RobotsTXT\Main::class, 'get_robots_txt' ], 10, 2 );
	}

	/**
	 * Initializes Admin Menu actions.
	 *
	 * @since 2.7.0
	 * @since 4.3.0 Moved to `\The_SEO_Framework\Hook`.
	 */
	public static function init_admin_actions() {

		/**
		 * @since 2.8.0
		 * Runs before the plugin is initialized in the admin screens.
		 */
		\do_action( 'the_seo_framework_admin_init' );

		if ( \wp_doing_ajax() )
			static::init_ajax_actions();

		// Initialize caching actions.
		static::init_post_caching_actions();

		// Delete Sitemap transient on permalink structure change.
		\add_action(
			'load-options-permalink.php',
			[ Sitemap\Registry::class, '_refresh_sitemap_transient_permalink_updated' ],
			20
		);

		\add_action( 'activated_plugin', [ \tsf(), 'reset_check_plugin_conflicts' ] );

		$headless = is_headless();

		if ( ! $headless['meta'] ) {
			// Initialize term meta filters and actions.
			\add_action( 'edit_term', [ \tsf(), '_update_term_meta' ], 10, 3 );

			// Initialize term meta filters and actions.
			\add_action( 'save_post', [ \tsf(), '_update_post_meta' ], 1 );
			\add_action( 'edit_attachment', [ \tsf(), '_update_attachment_meta' ], 1 );
			\add_action( 'save_post', [ \tsf(), '_save_inpost_primary_term' ], 1 );

			// Enqueue Post meta boxes.
			\add_action( 'add_meta_boxes', [ \tsf(), '_init_post_edit_view' ], 5, 1 );

			// Enqueue Term meta output.
			\add_action( 'current_screen', [ \tsf(), '_init_term_edit_view' ] );

			// Adds post states to list view tables.
			\add_filter( 'display_post_states', [ \tsf(), '_add_post_state' ], 10, 2 );

			// Initialize the SEO Bar for tables.
			\add_action( 'admin_init', [ \tsf(), '_init_seo_bar_tables' ] );

			// Initialize List Edit for tables.
			\add_action( 'admin_init', [ \tsf(), '_init_list_edit' ] );
		}

		if ( ! $headless['settings'] ) {
			// Set up site settings and allow saving resetting them.
			\add_action( 'admin_init', [ \tsf(), 'register_settings' ], 5 );

			// Loads setting notices.
			\add_action( 'the_seo_framework_setting_notices', [ \tsf(), '_do_settings_page_notices' ] );

			// Add menu links and register $this->seo_settings_page_hook
			\add_action( 'admin_menu', [ \tsf(), 'add_menu_link' ] );
		}

		if ( ! $headless['user'] ) {
			// Initialize user meta filters and actions.
			\add_action( 'personal_options_update', [ \tsf(), '_update_user_meta' ], 10, 1 );
			\add_action( 'edit_user_profile_update', [ \tsf(), '_update_user_meta' ], 10, 1 );

			// Enqueue user meta output.
			\add_action( 'current_screen', [ \tsf(), '_init_user_edit_view' ] );
		}

		if ( \in_array( false, $headless, true ) ) {
			// Set up notices.
			\add_action( 'admin_notices', [ \tsf(), '_output_notices' ] );

			// Fallback HTML-only notice dismissal.
			\add_action( 'admin_init', [ \tsf(), '_dismiss_notice' ] );

			// Enqueues admin scripts.
			\add_action( 'admin_enqueue_scripts', [ \tsf(), '_init_admin_scripts' ], 0, 1 );
		}

		// Add plugin links to the plugin activation page.
		\add_filter(
			'plugin_action_links_' . \THE_SEO_FRAMEWORK_PLUGIN_BASENAME,
			[ Admin\PluginTable::class, '_add_plugin_action_links' ],
			10,
			2
		);
		\add_filter(
			'plugin_row_meta',
			[ Admin\PluginTable::class, '_add_plugin_row_meta' ],
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
	 * Initializes AJAX actions.
	 *
	 * @since 4.1.4
	 * @since 4.3.0 1. Is now public.
	 *              2. Moved to `\The_SEO_Framework\Hook`.
	 *              3. Now runs after action `the_seo_framework_admin_init`.
	 */
	public static function init_ajax_actions() {

		// Admin AJAX for notice dismissal.
		\add_action( 'wp_ajax_tsf_dismiss_notice', [ Admin\AJAX::class, '_wp_ajax_dismiss_notice' ] );

		// Admin AJAX for TSF Cropper
		\add_action( 'wp_ajax_tsf_crop_image', [ Admin\AJAX::class, '_wp_ajax_crop_image' ] );

		// Admin AJAX for counter options.
		\add_action( 'wp_ajax_tsf_update_counter', [ Admin\AJAX::class, '_wp_ajax_update_counter_type' ] );

		// Admin AJAX for Gutenberg SEO Bar update.
		\add_action( 'wp_ajax_tsf_update_post_data', [ Admin\AJAX::class, '_wp_ajax_get_post_data' ] );
	}

	/**
	 * Initializes front-end actions.
	 * Disregards other SEO plugins, the meta output does look at detection.
	 *
	 * WARNING: Do not use query functions here.
	 *
	 * @since 2.5.2
	 */
	public static function init_front_end_actions() {

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
		if ( Sitemap\Utils::may_output_optimized_sitemap() ) {
			\add_action( 'parse_request', [ Sitemap\Registry::class, '_init' ], 15 );
			\add_filter( 'wp_sitemaps_enabled', '__return_false' );
		} else {
			// Augment Core sitemaps. Can't hook into `wp_sitemaps_init` as we're augmenting the providers before that.
			// It's not a bridge, don't treat it like one: clean me up?
			\add_filter( 'wp_sitemaps_add_provider', [ Sitemap\WP\Filter::class, '_filter_add_provider' ], 9, 2 );
			\add_filter( 'wp_sitemaps_max_urls', [ Sitemap\WP\Filter::class, '_filter_max_urls' ], 9 );
			// We miss the proper hooks. https://github.com/sybrew/the-seo-framework/issues/610#issuecomment-1300191500
			\add_filter( 'wp_sitemaps_posts_query_args', [ Sitemap\WP\Filter::class, '_trick_filter_doing_sitemap' ], 11 );
		}

		// Initialize 301 redirects.
		\add_action( 'template_redirect', [ \tsf(), '_init_custom_field_redirect' ] );

		// Prepares requisite robots headers to avoid low-quality content penalties.
		\add_action( 'do_robots', [ Helper\Headers::class, 'output_robots_noindex_headers' ] );
		\add_action( 'the_seo_framework_sitemap_header', [ Helper\Headers::class, 'output_robots_noindex_headers' ] );

		// Output meta tags.
		\add_action( 'wp_head', [ Front\Meta\Head::class, 'print_wrap_and_tags' ], 1 );

		if ( Data\Plugin::get_option( 'alter_archive_query' ) )
			static::init_alter_archive_query();

		if ( Data\Plugin::get_option( 'alter_search_query' ) )
			static::init_alter_search_query();

		if ( ! Data\Plugin::get_option( 'index_the_feed' ) )
			\add_action( 'template_redirect', [ Front\Feed::class, 'output_robots_noindex_headers_on_feed' ] );

		// Modify the feed.
		if (
			   Data\Plugin::get_option( 'excerpt_the_feed' )
			|| Data\Plugin::get_option( 'source_the_feed' )
		) {
			// Alter the content feed.
			\add_filter( 'the_content_feed', [ Front\Feed::class, 'modify_the_content_feed' ], 10, 2 );

			// Only add the feed link to the excerpt if we're only building excerpts.
			if ( \get_option( 'rss_use_excerpt' ) )
				\add_filter( 'the_excerpt_rss', [ Front\Feed::class, 'modify_the_content_feed' ], 10, 1 );
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
	public static function init_front_end_filters() {

		/**
		 * @since 2.9.3
		 * @param bool $overwrite_titles Whether to enable title overwriting.
		 */
		if ( \apply_filters( 'the_seo_framework_overwrite_titles', true ) ) {
			// Removes all pre_get_document_title filters.
			\remove_all_filters( 'pre_get_document_title', false );

			// New WordPress 4.4.0 filter. Hurray! It's also much faster :)
			\add_filter( 'pre_get_document_title', [ \tsf(), 'get_document_title' ], 10 );

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
				\add_filter( 'wp_title', [ \tsf(), 'get_wp_title' ], 9 );
				// Override WooThemes Title TODO move this to wc compat file.
				\add_filter( 'woo_title', [ \tsf(), 'get_document_title' ], 99 );
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

		if ( Data\Plugin::get_option( 'og_tags' ) ) { // independent from filter at use_og_tags--let that be deciding later.
			// Disable Jetpack's Open Graph tags. But Sybre, compat files? Yes.
			\add_filter( 'jetpack_enable_open_graph', '__return_false' );
		}

		if ( Data\Plugin::get_option( 'twitter_tags' ) ) { // independent from filter at use_twitter_tags--let that be deciding later.
			// Disable Jetpack's Twitter Card tags. But Sybre, compat files? Maybe.
			\add_filter( 'jetpack_disable_twitter_cards', '__return_true' );
			// Future, maybe. See <https://github.com/Automattic/jetpack/issues/13146#issuecomment-516841698>
			// \add_filter( 'jetpack_enable_twitter_cards', '__return_false' );
		}

		if ( ! Data\Plugin::get_option( 'oembed_scripts' ) ) {
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
		\add_filter( 'oembed_response_data', [ \tsf(), '_alter_oembed_response_data' ], 10, 2 );
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
	public static function init_post_caching_actions() {

		if ( has_run( __METHOD__ ) ) return;

		$refresh_sitemap_callback = [ Sitemap\Registry::class, '_refresh_sitemap_on_post_change' ];

		// Can-be cron actions.
		\add_action( 'publish_post', $refresh_sitemap_callback );
		\add_action( 'publish_page', $refresh_sitemap_callback );

		// Other actions.
		\add_action( 'deleted_post', $refresh_sitemap_callback );
		\add_action( 'deleted_page', $refresh_sitemap_callback );
		\add_action( 'post_updated', $refresh_sitemap_callback );
		\add_action( 'page_updated', $refresh_sitemap_callback );

		$clear_excluded_callback = [ Query\Exclusion::class, 'clear_excluded_post_ids_cache' ];

		// Excluded IDs cache.
		\add_action( 'wp_insert_post', $clear_excluded_callback );
		\add_action( 'attachment_updated', $clear_excluded_callback );
	}

	/**
	 * Initializes search query adjustments.
	 *
	 * @since 2.9.4
	 * @since 4.3.0 Moved to `\The_SEO_Framework\Hook`.
	 */
	public static function init_alter_search_query() {
		switch ( Data\Plugin::get_option( 'alter_search_query_type' ) ) {
			case 'post_query':
				\add_filter( 'the_posts', [ Query\Exclusion::class, '_alter_search_query_post' ], 10, 2 );
				break;

			case 'in_query':
			default:
				\add_action( 'pre_get_posts', [ Query\Exclusion::class, '_alter_search_query_in' ], 9999, 1 );
		}
	}

	/**
	 * Initializes archive query adjustments.
	 *
	 * @since 2.9.4
	 * @since 4.3.0 Moved to `\The_SEO_Framework\Hook`.
	 */
	public static function init_alter_archive_query() {
		switch ( Data\Plugin::get_option( 'alter_archive_query_type' ) ) {
			case 'post_query':
				\add_filter( 'the_posts', [ Query\Exclusion::class, '_alter_archive_query_post' ], 10, 2 );
				break;

			case 'in_query':
			default:
				\add_action( 'pre_get_posts', [ Query\Exclusion::class, '_alter_archive_query_in' ], 9999, 1 );
		}
	}
}
