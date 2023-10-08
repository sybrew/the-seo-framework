<?php
/**
 * @package The_SEO_Framework
 * @subpackage The_SEO_Framework\Bootstrap
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\is_headless;

use \The_SEO_Framework\Helper\Query;

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
