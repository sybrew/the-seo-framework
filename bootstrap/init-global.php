<?php
/**
 * @package The_SEO_Framework
 * @subpackage The_SEO_Framework\Bootstrap
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use The_SEO_Framework\{
	Data,
	Sitemap,
};
use The_SEO_Framework\Helper\{
	Headers,
	Query,
	Taxonomy\Base_URL\Rewrite,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 - 2026 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// Load the plugin's text domain first.
\load_plugin_textdomain(
	'autodescription',
	false,
	\dirname( \THE_SEO_FRAMEWORK_PLUGIN_BASENAME ) . \DIRECTORY_SEPARATOR . 'language',
);

\add_filter(
	'pre_update_option_' . \THE_SEO_FRAMEWORK_SITE_OPTIONS,
	[ Data\Plugin::class, 'guard_option_update' ],
	\PHP_INT_MAX,
	2,
);

// Core fires these option-specific actions after updating its option cache.
foreach ( [ 'add', 'update', 'delete' ] as $event )
	\add_action(
		"{$event}_option_" . \THE_SEO_FRAMEWORK_SITE_OPTIONS,
		[ Data\Plugin::class, 'flush_cache' ],
		0,
		0,
	);

\add_filter(
	'sanitize_option_' . \THE_SEO_FRAMEWORK_SITE_OPTIONS,
	[ Data\Plugin::class, 'sanitize_option_update' ],
	10,
	2,
);

// Output noindex headers when an XMLRPC request is detected. There are no hooks, test inline.
if ( \defined( 'XMLRPC_REQUEST' ) && \XMLRPC_REQUEST )
	Headers::output_robots_noindex_headers();

// Adjust category link to accommodate primary term.
\add_filter( 'post_link_category', [ Query\Filter::class, 'filter_post_link_category' ], 10, 3 );

// Switched sites can shorten their term links even when the request's site keeps its bases.
if ( Data\Plugin::get_option( 'remove_taxonomy_bases' ) || \is_multisite() )
	\add_filter(
		'term_link',
		[ Rewrite::class, 'filter_term_link' ],
		20,
		2,
	);

// Overwrite the robots.txt output.
\add_filter( 'robots_txt', [ RobotsTXT\Main::class, 'get_robots_txt' ], 10, 2 );

// Register the TSF breadcrumb shortcode.
\add_shortcode( 'tsf_breadcrumb', 'tsf_breadcrumb' );

// wp_insert_post covers publish and update. Attachments return before it. Deletes use deleted_post.
$refresh_post_callback = [ Sitemap\Registry::class, '_refresh_sitemap_on_post_change' ];
\add_action( 'wp_insert_post', $refresh_post_callback );
\add_action( 'deleted_post', $refresh_post_callback );
\add_action( 'wp_insert_post', [ Query\Exclusion::class, 'clear_excluded_post_ids_cache' ] );
\add_action( 'attachment_updated', [ Query\Exclusion::class, 'clear_excluded_post_ids_cache' ] );
