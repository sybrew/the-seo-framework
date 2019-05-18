<?php
/**
 * @package The_SEO_Framework\Views\Sitemap
 */

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = \the_seo_framework_class() and $this instanceof $_this or die;

$this->the_seo_framework_debug and $timer_start = microtime( true );

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo $this->get_sitemap_xsl_stylesheet_tag();

/**
 * Output debug prior output.
 * @since 2.8.0
 */
if ( $this->the_seo_framework_debug ) {
	echo '<!-- Site estimated peak usage prior to generation: ' . number_format( memory_get_peak_usage() / 1024 / 1024, 3 ) . ' MB -->' . "\n";
	echo '<!-- System estimated peak usage prior to generation: ' . number_format( memory_get_peak_usage( true ) / 1024 / 1024, 3 ) . ' MB -->' . "\n";
}

$sitemap_content = $this->get_option( 'cache_sitemap' ) ? $this->get_transient( $this->get_sitemap_transient_name() ) : false;

echo $this->get_sitemap_urlset_open_tag();
echo $this->setup_sitemap( $sitemap_content );
echo $this->get_sitemap_urlset_close_tag();

if ( false === $sitemap_content ) {
	echo "\n" . '<!-- ' . \esc_html__( 'Sitemap is generated for this view', 'autodescription' ) . ' -->';
} else {
	echo "\n" . '<!-- ' . \esc_html__( 'Sitemap is served from cache', 'autodescription' ) . ' -->';
}

/**
 * Output debug info.
 * @since 2.3.7
 */
if ( $this->the_seo_framework_debug ) {
	echo "\n" . '<!-- Site estimated peak usage: ' . number_format( memory_get_peak_usage() / 1024 / 1024, 3 ) . ' MB -->';
	echo "\n" . '<!-- System estimated peak usage: ' . number_format( memory_get_peak_usage( true ) / 1024 / 1024, 3 ) . ' MB -->';
	echo "\n" . '<!-- Freed memory prior to generation: ' . number_format( $this->clean_up_globals_for_sitemap( true ) / 1024, 3 ) . ' kB -->';
	echo "\n" . '<!-- Sitemap generation time: ' . number_format( microtime( true ) - $timer_start, 6 ) . ' seconds -->';
}
