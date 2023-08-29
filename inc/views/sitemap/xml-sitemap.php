<?php
/**
 * @package The_SEO_Framework\Views\Sitemap
 * @subpackage The_SEO_Framework\Sitemap
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and tsf()->_verify_include_secret( $_secret ) or die;

THE_SEO_FRAMEWORK_DEBUG and $timer_start = hrtime( true );

$sitemap_bridge = The_SEO_Framework\Bridges\Sitemap::get_instance();

$sitemap_bridge->output_sitemap_header();

if ( THE_SEO_FRAMEWORK_DEBUG ) {
	echo '<!-- Site estimated peak usage prior to generation: ', number_format( memory_get_peak_usage() / MB_IN_BYTES, 3 ), ' MB -->' . "\n";
	echo '<!-- System estimated peak usage prior to generation: ', number_format( memory_get_peak_usage( true ) / MB_IN_BYTES, 3 ), ' MB -->' . "\n";
}

$sitemap_bridge->output_sitemap_urlset_open_tag();

$sitemap_base = new The_SEO_Framework\Builders\Sitemap\Base;
// phpcs:ignore, WordPress.Security.EscapeOutput
echo $sitemap_base->generate_sitemap( $sitemap_id );

$sitemap_bridge->output_sitemap_urlset_close_tag();

if ( $sitemap_base->base_is_regenerated ) {
	echo "\n<!-- ", esc_html__( 'Sitemap is generated for this view', 'autodescription' ), ' -->';
} else {
	echo "\n<!-- ", esc_html__( 'Sitemap is served from cache', 'autodescription' ), ' -->';
}

// Destruct class.
$sitemap_base = null;

if ( THE_SEO_FRAMEWORK_DEBUG ) {
	echo "\n<!-- Site estimated current usage: ", number_format( memory_get_usage() / MB_IN_BYTES, 3 ), ' MB -->';
	echo "\n<!-- System estimated current usage: ", number_format( memory_get_usage( true ) / MB_IN_BYTES, 3 ), ' MB -->';
	echo "\n<!-- Site estimated peak usage: ", number_format( memory_get_peak_usage() / MB_IN_BYTES, 3 ), ' MB -->';
	echo "\n<!-- System estimated peak usage: ", number_format( memory_get_peak_usage( true ) / MB_IN_BYTES, 3 ), ' MB -->';
	echo "\n<!-- Freed memory prior to generation: ", number_format( $sitemap_bridge->get_freed_memory( true ) / KB_IN_BYTES, 3 ), ' kB -->';
	echo "\n<!-- Sitemap generation time: ", number_format( ( hrtime( true ) - $timer_start ) / 1e9, 6 ), ' seconds -->';
	echo "\n<!-- Sitemap caching enabled: ", ( $this->get_option( 'cache_sitemap' ) ? 'yes' : 'no' ), ' -->';
	echo "\n<!-- Sitemap transient key: ", esc_html( $sitemap_bridge->get_transient_key() ), ' -->';
}
