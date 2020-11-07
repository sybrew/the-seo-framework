<?php
/**
 * @package The_SEO_Framework\Views\Sitemap
 * @subpackage The_SEO_Framework\Sitemap
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and the_seo_framework()->_verify_include_secret( $_secret ) or die;

$this->the_seo_framework_debug and $timer_start = microtime( true );

$sitemap_bridge = The_SEO_Framework\Bridges\Sitemap::get_instance();

$sitemap_bridge->output_sitemap_header();

if ( $this->the_seo_framework_debug ) {
	echo '<!-- Site estimated peak usage prior to generation: ' . number_format( memory_get_peak_usage() / 1024 / 1024, 3 ) . ' MB -->' . "\n";
	echo '<!-- System estimated peak usage prior to generation: ' . number_format( memory_get_peak_usage( true ) / 1024 / 1024, 3 ) . ' MB -->' . "\n";
}

$sitemap_bridge->output_sitemap_urlset_open_tag();

$sitemap_base = new The_SEO_Framework\Builders\Sitemap_Base;
// phpcs:ignore, WordPress.Security.EscapeOutput
echo $sitemap_base->generate_sitemap( $sitemap_id );

$sitemap_bridge->output_sitemap_urlset_close_tag();

if ( $sitemap_base->base_is_regenerated ) {
	echo "\n" . '<!-- ' . esc_html__( 'Sitemap is generated for this view', 'autodescription' ) . ' -->';
} else {
	echo "\n" . '<!-- ' . esc_html__( 'Sitemap is served from cache', 'autodescription' ) . ' -->';
}

// Destroy class.
$sitemap_base = null;

if ( $this->the_seo_framework_debug ) {
	echo "\n" . '<!-- Site estimated current usage: ' . number_format( memory_get_usage() / 1024 / 1024, 3 ) . ' MB -->';
	echo "\n" . '<!-- System estimated current usage: ' . number_format( memory_get_usage( true ) / 1024 / 1024, 3 ) . ' MB -->';
	echo "\n" . '<!-- Site estimated peak usage: ' . number_format( memory_get_peak_usage() / 1024 / 1024, 3 ) . ' MB -->';
	echo "\n" . '<!-- System estimated peak usage: ' . number_format( memory_get_peak_usage( true ) / 1024 / 1024, 3 ) . ' MB -->';
	echo "\n" . '<!-- Freed memory prior to generation: ' . number_format( $sitemap_bridge->get_freed_memory( true ) / 1024, 3 ) . ' kB -->';
	echo "\n" . '<!-- Sitemap generation time: ' . number_format( microtime( true ) - $timer_start, 6 ) . ' seconds -->';
	echo "\n" . '<!-- Sitemap caching enabled: ' . ( $this->get_option( 'cache_sitemap' ) ? 'yes' : 'no' ) . ' -->';
	echo "\n" . '<!-- Sitemap transient key: ' . esc_html( $this->get_sitemap_transient_name() ) . ' -->';
}
