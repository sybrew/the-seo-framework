<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\WPML
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Helper\Query;

\add_action( 'the_seo_framework_cleared_sitemap_transients', __NAMESPACE__ . '\\_wpml_flush_sitemap', 10 );
\add_action( 'the_seo_framework_sitemap_header', __NAMESPACE__ . '\\_wpml_sitemap_filter_display_translatables' );
\add_action( 'the_seo_framework_sitemap_hpt_query_args', __NAMESPACE__ . '\\_wpml_sitemap_filter_non_translatables' );
\add_action( 'the_seo_framework_sitemap_nhpt_query_args', __NAMESPACE__ . '\\_wpml_sitemap_filter_non_translatables' );

/**
 * Deletes all sitemap transients, instead of just one.
 *
 * @hook the_seo_framework_cleared_sitemap_transients 10
 * @since 3.1.0
 * @since 5.0.0 Removed clearing once-per-request restriction.
 * @global \wpdb $wpdb
 * @access private
 */
function _wpml_flush_sitemap() {
	global $wpdb;

	$wpdb->query( $wpdb->prepare(
		"DELETE FROM $wpdb->options WHERE option_name LIKE %s",
		$wpdb->esc_like( '_transient_tsf_sitemap_' ) . '%',
	) );

	// We didn't use a wildcard after "_transient_" to reduce scans.
	// A second query is faster on saturated sites.
	$wpdb->query( $wpdb->prepare(
		"DELETE FROM $wpdb->options WHERE option_name LIKE %s",
		$wpdb->esc_like( '_transient_timeout_tsf_sitemap_' ) . '%',
	) );
}

/**
 * Filters "display translatable" post types from the sitemap query arguments.
 * Only appends actually translated posts to the translated sitemap.
 *
 * @hook the_seo_framework_sitemap_header 10
 * @since 4.1.4
 * @access private
 */
function _wpml_sitemap_filter_display_translatables() {
	// ez.
	\add_filter( 'wpml_should_use_display_as_translated_snippet', '__return_false' );
}

/**
 * Filters nontranslatable post types from the sitemap query arguments.
 * Only appends when the default sitemap language is not displayed.
 *
 * @hook the_seo_framework_sitemap_hpt_query_args 10
 * @hook the_seo_framework_sitemap_nhpt_query_args 10
 * @since 4.1.4
 * @access private
 * @global $sitepress \SitePress
 *
 * @param array $args The query arguments.
 * @return array The augmented query arguments.
 */
function _wpml_sitemap_filter_non_translatables( $args ) {
	global $sitepress;

	if (
		   empty( $sitepress )
		|| ! method_exists( $sitepress, 'get_default_language' )
		|| ! method_exists( $sitepress, 'get_current_language' )
		|| ! method_exists( $sitepress, 'is_translated_post_type' )
	) {
		return $args;
	}

	if ( $sitepress->get_default_language() === $sitepress->get_current_language() ) return $args;

	// Filter out only 'Not translatable'.
	$args['post_type'] = array_filter( (array) $args['post_type'], [ $sitepress, 'is_translated_post_type' ] );

	return $args;
}
