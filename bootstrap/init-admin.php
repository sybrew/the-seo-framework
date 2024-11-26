<?php
/**
 * @package The_SEO_Framework
 * @subpackage The_SEO_Framework\Bootstrap
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\is_headless;

use \The_SEO_Framework\Helper\{
	Compatibility,
	Query,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// Delete Sitemap transient on permalink structure change.
\add_action(
	'load-options-permalink.php',
	[ Sitemap\Registry::class, '_refresh_sitemap_transient_permalink_updated' ],
	20,
);

\add_action( 'activated_plugin', [ Compatibility::class, 'try_plugin_conflict_notification' ] );
\add_action( 'deactivated_plugin', [ Compatibility::class, 'clear_plugin_conflict_notification' ] );

$headless = is_headless();

if ( ! $headless['meta'] ) {
	// Initialize term meta filters and actions.
	\add_action( 'edit_term', [ Data\Admin\Term::class, 'update_meta' ], 10, 3 );
	\add_action(
		'sanitize_term_meta_' . \THE_SEO_FRAMEWORK_TERM_OPTIONS,
		[ Data\Filter\Term::class, 'filter_meta_update' ],
	);

	// Initialize post meta filters and actions. Saving handles the sanitization.
	\add_action( 'save_post', [ Data\Admin\Post::class, 'update_meta' ], 1 );
	\add_action( 'edit_attachment', [ Data\Admin\Post::class, 'update_meta' ], 1 );
	\add_action( 'save_post', [ Data\Admin\Post::class, 'update_primary_term' ], 1 );

	// Enqueue Post meta boxes.
	\add_action( 'add_meta_boxes', [ Admin\Settings\Post::class, 'prepare_meta_box' ] );

	// Enqueue Term meta output. Terms don't have proper catch-all hooks, so this loads on every page:
	\add_action( 'current_screen', [ Admin\Settings\Term::class, 'prepare_setting_fields' ] );

	// Adds post states to list view tables.
	\add_filter( 'display_post_states', [ Admin\Lists\PostStates::class, 'add_post_state' ], 10, 2 );

	// Initialize quick and bulk edit for posts and terms.
	\add_action( 'admin_init', [ Admin\Settings\ListEdit::class, 'init_quick_and_bulk_edit' ] );

	if ( Data\Plugin::get_option( 'display_seo_bar_tables' ) ) {
		// Initialize the SEO Bar for tables.
		\add_action( 'admin_init', [ Admin\SEOBar\ListTable::class, 'init_seo_bar' ] );
	}
}

/**
 * Register the required settings capability regardless of headlessness.
 * For it may be higher than manage_options, and shielded via headlessness.
 */
\add_filter(
	'option_page_capability_' . \THE_SEO_FRAMEWORK_SITE_OPTIONS,
	fn() => \THE_SEO_FRAMEWORK_SETTINGS_CAP,
);
/**
 * Register the required settings sanitization regardless of headlessness.
 * Other plugins may still update our options.
 * Also, register_settings only passes 1 parameter, while we need all 3.
 */
\add_filter(
	'sanitize_option_' . \THE_SEO_FRAMEWORK_SITE_OPTIONS,
	[ Data\Filter\Plugin::class, 'filter_settings_update' ],
	10,
	3,
);

if ( ! $headless['settings'] ) {
	// Register settings, saving, and the menu.
	\add_action( 'admin_init', [ Data\Admin\Plugin::class, 'register_settings' ], 0 );
	\add_action( 'admin_action_update', [ Data\Admin\Plugin::class, 'process_settings_update' ] );
	\add_action( 'admin_menu', [ Admin\Menu::class, 'register_top_menu_page' ] );
}

if ( ! $headless['user'] ) {
	// Initialize user meta filters and actions.
	\add_action( 'show_user_profile', [ Admin\Settings\User::class, 'prepare_setting_fields' ], 0, 1 );
	\add_action( 'edit_user_profile', [ Admin\Settings\User::class, 'prepare_setting_fields' ], 0, 1 );

	\add_action( 'personal_options_update', [ Data\Admin\User::class, 'update_meta' ], 10, 1 );
	\add_action( 'edit_user_profile_update', [ Data\Admin\User::class, 'update_meta' ], 10, 1 );
}

if ( \in_array( false, $headless, true ) ) { // Still got head...
	// Set up notices.
	\add_action( 'admin_notices', [ Admin\Notice\Persistent::class, '_output_notices' ] );

	// Fallback HTML-only notice dismissal. See init-admin-ajax.php for the AJAX callback.
	\add_action( 'admin_init', [ Admin\Notice\Persistent::class, '_dismiss_notice' ] );

	// Enqueues admin scripts.
	\add_action( 'admin_enqueue_scripts', [ Admin\Script\Registry::class, '_init' ], 0, 1 );

	// Setup user sanitization. If at least something isn't headless, user metadata can be used.
	\add_action(
		'sanitize_user_meta_' . \THE_SEO_FRAMEWORK_USER_OPTIONS,
		[ Data\Filter\User::class, 'filter_meta_update' ],
	);
}

// Add plugin links to the plugin activation page.
\add_filter(
	'plugin_action_links_' . \THE_SEO_FRAMEWORK_PLUGIN_BASENAME,
	[ Admin\PluginTable::class, 'add_plugin_action_links' ]
);
\add_filter(
	'plugin_row_meta',
	[ Admin\PluginTable::class, 'add_plugin_row_meta' ],
	10,
	2,
);
