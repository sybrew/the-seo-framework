<?php
/**
 * @package The_SEO_Framework\Views\Sitemap
 * @subpackage The_SEO_Framework\Sitemap
 */

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = \the_seo_framework_class() and $this instanceof $_this or die;

$this->the_seo_framework_debug and $timer_start = microtime( true );

$sitemap_bridge = \The_SEO_Framework\Bridges\Sitemap::get_instance();
$sitemap_bridge->output_sitemap_header();

if ( $this->the_seo_framework_debug ) {
	echo '<!-- Site estimated peak usage prior to generation: ' . number_format( memory_get_peak_usage() / 1024 / 1024, 3 ) . ' MB -->' . "\n";
	echo '<!-- System estimated peak usage prior to generation: ' . number_format( memory_get_peak_usage( true ) / 1024 / 1024, 3 ) . ' MB -->' . "\n";
}

$sitemap_bridge->output_sitemap_urlset_open_tag();

$sitemap_generated = false;
$sitemap_content   = $this->get_option( 'cache_sitemap' ) ? $this->get_transient( $this->get_sitemap_transient_name() ) : false;

if ( false === $sitemap_content ) {
	$sitemap_generated = true;

	//* Transient doesn't exist yet.
	$sitemap_base = new \The_SEO_Framework\Builders\Sitemap_Base;
	$sitemap_base->prepare_generation();

	$sitemap_content = $sitemap_base->build_sitemap();

	$sitemap_base->shutdown_generation();
	$sitemap_base = null; // destroy class.

	/**
	 * Transient expiration: 1 week.
	 * Keep the sitemap for at most 1 week.
	 */
	$expiration = WEEK_IN_SECONDS;

	if ( $this->get_option( 'cache_sitemap' ) )
		$this->set_transient( $this->get_sitemap_transient_name(), $sitemap_content, $expiration );
}
// phpcs:ignore, WordPress.Security.EscapeOutput
echo $sitemap_content;

$sitemap_bridge->output_sitemap_urlset_close_tag();

if ( $sitemap_generated ) {
	echo "\n" . '<!-- ' . \esc_html__( 'Sitemap is generated for this view', 'autodescription' ) . ' -->';
} else {
	echo "\n" . '<!-- ' . \esc_html__( 'Sitemap is served from cache', 'autodescription' ) . ' -->';
}

if ( $this->the_seo_framework_debug ) {
	echo "\n" . '<!-- Site estimated peak usage: ' . number_format( memory_get_peak_usage() / 1024 / 1024, 3 ) . ' MB -->';
	echo "\n" . '<!-- System estimated peak usage: ' . number_format( memory_get_peak_usage( true ) / 1024 / 1024, 3 ) . ' MB -->';
	echo "\n" . '<!-- Freed memory prior to generation: ' . number_format( $this->clean_up_globals_for_sitemap( true ) / 1024, 3 ) . ' kB -->';
	echo "\n" . '<!-- Sitemap generation time: ' . number_format( microtime( true ) - $timer_start, 6 ) . ' seconds -->';
}
