<?php
/**
 * @package The_SEO_Framework\Views\Sitemap
 * @subpackage The_SEO_Framework\Sitemap
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) or die;

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// See output_base_sitemap et al.
[ $sitemap_id ] = $view_args;

THE_SEO_FRAMEWORK_DEBUG and $timer_start = hrtime( true );

Sitemap\Registry::output_sitemap_header();

if ( THE_SEO_FRAMEWORK_DEBUG ) {
	echo '<!-- Site estimated peak usage prior to generation: ', number_format( memory_get_peak_usage() / MB_IN_BYTES, 3 ), ' MB -->' . "\n";
	echo '<!-- System estimated peak usage prior to generation: ', number_format( memory_get_peak_usage( true ) / MB_IN_BYTES, 3 ), ' MB -->' . "\n";
}

Sitemap\Registry::output_sitemap_urlset_open_tag();

$sitemap_base = new Sitemap\Optimized\Base; // TODO make static? Why would this need to be instantiated anyway?
// phpcs:ignore, WordPress.Security.EscapeOutput
echo $sitemap_base->generate_sitemap( $sitemap_id );

Sitemap\Registry::output_sitemap_urlset_close_tag();

if ( $sitemap_base->base_is_regenerated ) {
	echo "\n<!-- ", \esc_html__( 'Sitemap is generated for this view', 'autodescription' ), ' -->';
} else {
	echo "\n<!-- ", \esc_html__( 'Sitemap is served from cache', 'autodescription' ), ' -->';
}

// Destruct class.
$sitemap_base = null;

if ( THE_SEO_FRAMEWORK_DEBUG ) {
	echo "\n<!-- Site estimated current usage: ", number_format( memory_get_usage() / MB_IN_BYTES, 3 ), ' MB -->';
	echo "\n<!-- System estimated current usage: ", number_format( memory_get_usage( true ) / MB_IN_BYTES, 3 ), ' MB -->';
	echo "\n<!-- Site estimated peak usage: ", number_format( memory_get_peak_usage() / MB_IN_BYTES, 3 ), ' MB -->';
	echo "\n<!-- System estimated peak usage: ", number_format( memory_get_peak_usage( true ) / MB_IN_BYTES, 3 ), ' MB -->';
	echo "\n<!-- Freed memory prior to generation: ", number_format( Sitemap\Registry::get_freed_memory( true ) / KB_IN_BYTES, 3 ), ' kB -->';
	echo "\n<!-- Sitemap generation time: ", number_format( ( hrtime( true ) - $timer_start ) / 1e9, 6 ), ' seconds -->';
	echo "\n<!-- Sitemap caching enabled: ", ( Data\Plugin::get_option( 'cache_sitemap' ) ? 'yes' : 'no' ), ' -->';
	echo "\n<!-- Sitemap transient key: ", \esc_html( Sitemap\Cache::get_sitemap_cache_key( $sitemap_id ) ), ' -->';
}
