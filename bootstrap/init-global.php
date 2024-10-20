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

// Load the plugin's text domain first.
\load_plugin_textdomain(
	'autodescription',
	false,
	\dirname( \THE_SEO_FRAMEWORK_PLUGIN_BASENAME ) . \DIRECTORY_SEPARATOR . 'language',
);

// Output noindex headers when an XMLRPC request is detected. There are no hooks, test inline.
if ( \defined( 'XMLRPC_REQUEST' ) && \XMLRPC_REQUEST )
	Headers::output_robots_noindex_headers();

// Adjust category link to accommodate primary term.
\add_filter( 'post_link_category', [ Query\Filter::class, 'filter_post_link_category' ], 10, 3 );

// Overwrite the robots.txt output.
\add_filter( 'robots_txt', [ RobotsTXT\Main::class, 'get_robots_txt' ], 10, 2 );

// Register the TSF breadcrumb shortcode.
\add_shortcode( 'tsf_breadcrumb', 'tsf_breadcrumb' );
