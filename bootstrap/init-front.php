<?php
/**
 * @package The_SEO_Framework
 * @subpackage The_SEO_Framework\Bootstrap
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Helper\{
	Headers,
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

// Remove canonical header tag from WP.
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
	\add_filter( 'wp_sitemaps_add_provider', [ Sitemap\WP\Filter::class, 'filter_add_provider' ], 9, 2 );
	\add_filter( 'wp_sitemaps_max_urls', [ Sitemap\WP\Filter::class, 'filter_max_urls' ], 9 );
	// We miss the proper hooks. https://github.com/sybrew/the-seo-framework/issues/610#issuecomment-1300191500
	\add_filter( 'wp_sitemaps_posts_query_args', [ Sitemap\WP\Filter::class, 'trick_filter_doing_sitemap' ], 11 );
}

// Initialize 301 redirects.
\add_action( 'template_redirect', [ Front\Redirect::class, 'init_meta_setting_redirect' ] );

// Prepares requisite robots headers to avoid low-quality content penalties.
\add_action( 'do_robots', [ Headers::class, 'output_robots_noindex_headers' ] );
\add_action( 'the_seo_framework_sitemap_header', [ Headers::class, 'output_robots_noindex_headers' ] );

// Overwrite title tags.
\add_action( 'template_redirect', [ Front\Title::class, 'overwrite_title_filters' ], 20 );

// Output meta tags.
\add_action( 'wp_head', [ Front\Meta\Head::class, 'print_wrap_and_tags' ], 1 );

if ( Data\Plugin::get_option( 'alter_archive_query' ) ) {
	switch ( Data\Plugin::get_option( 'alter_archive_query_type' ) ) {
		case 'post_query':
			\add_filter( 'the_posts', [ Front\Query::class, 'alter_archive_query_post' ], 10, 2 );
			break;

		case 'in_query':
		default:
			\add_action( 'pre_get_posts', [ Front\Query::class, 'alter_archive_query_in' ], 9999, 1 );
	}
}

if ( Data\Plugin::get_option( 'alter_search_query' ) ) {
	switch ( Data\Plugin::get_option( 'alter_search_query_type' ) ) {
		case 'post_query':
			\add_filter( 'the_posts', [ Front\Query::class, 'alter_search_query_post' ], 10, 2 );
			break;

		case 'in_query':
		default:
			\add_action( 'pre_get_posts', [ Front\Query::class, 'alter_search_query_in' ], 9999, 1 );
	}
}

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
 * @since 4.1.4
 * @param bool $kill_core_robots Whether you lack sympathy for rocks tricked to think.
 */
if ( \apply_filters( 'the_seo_framework_kill_core_robots', true ) ) {
	\remove_filter( 'wp_robots', 'wp_robots_max_image_preview_large' );
	// Reconsider readding this to "supported" queries only?
	\remove_filter( 'wp_robots', 'wp_robots_noindex_search' );
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
\add_filter( 'oembed_response_data', [ Front\OEmbed::class, 'alter_response_data' ], 10, 2 );
