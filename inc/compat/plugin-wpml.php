<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\WPML
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\{
	Helper\Query,
	Meta\URI,
};

\add_filter( 'the_seo_framework_sitemap_endpoint_list', __NAMESPACE__ . '\\_wpml_register_sitemap_languages', 20 );
\add_action( 'the_seo_framework_cleared_sitemap_transients', __NAMESPACE__ . '\\_wpml_flush_sitemap', 10 );
\add_action( 'the_seo_framework_sitemap_header', __NAMESPACE__ . '\\_wpml_sitemap_filter_display_translatables' );
\add_action( 'the_seo_framework_sitemap_hpt_query_args', __NAMESPACE__ . '\\_wpml_sitemap_filter_non_translatables' );
\add_action( 'the_seo_framework_sitemap_nhpt_query_args', __NAMESPACE__ . '\\_wpml_sitemap_filter_non_translatables' );

/**
 * Registeres more sitemaps for the robots.txt to parse.
 *
 * This has no other intended effect. But default permalinks may react more tsf_sitemap query values,
 * specifically ?tsf_sitemap=_base_wpml_es&lang=es" (assumed, untested).
 *
 * @hook the_seo_framework_sitemap_endpoint_list 20
 * @since 5.0.5
 * @param array[] $list {
 *     A list of sitemap endpoints keyed by ID.
 *
 *     @type string|false $lock_id  Optional. The cache key to use for locking. Defaults to index 'id'.
 *                                  Set to false to disable locking.
 *     @type string|false $cache_id Optional. The cache key to use for storing. Defaults to index 'id'.
 *                                  Set to false to disable caching.
 *     @type string       $endpoint The expected "pretty" endpoint, meant for administrative display.
 *     @type string       $epregex  The endpoint regex, following the home path regex.
 *     @type callable     $callback The callback for the sitemap output.
 *     @type bool         $robots   Whether the endpoint should be mentioned in the robots.txt file.
 * }
 * @return array[]
 */
function _wpml_register_sitemap_languages( $list ) {
	global $sitepress;

	if ( empty( $list['base'] ) )
		return $list;

	if (
		   empty( $sitepress )
		|| ! Helper\Compatibility::can_i_use(
			[
				'methods'   => [
					[ $sitepress, 'get_default_language' ],
					[ $sitepress, 'get_active_languages' ],
					[ $sitepress, 'get_setting' ],
				],
				'constants' => [
					'WPML_LANGUAGE_NEGOTIATION_TYPE_DIRECTORY',
					'WPML_LANGUAGE_NEGOTIATION_TYPE_PARAMETER',
				],
			],
		)
	) return $list;

	// Do most work outside of a loop. We have two loops because of this.
	// We fall back to -1 because null/false match with '0'
	switch ( $sitepress->get_setting( 'language_negotiation_type' ) ) {
		case \WPML_LANGUAGE_NEGOTIATION_TYPE_PARAMETER: // 3
			foreach (
				array_diff(
					array_column( $sitepress->get_active_languages(), 'code' ),
					[ $sitepress->get_default_language() ],
				)
				as $language
			) {
				$list[ "base_wpml_$language" ] = [
					'endpoint' => URI\Utils::append_query_to_url(
						$list['base']['endpoint'],
						"lang=$language",
					),
				] + $list['base'];
			}
			break;
		case \WPML_LANGUAGE_NEGOTIATION_TYPE_DIRECTORY: // 1
			foreach (
				array_diff(
					array_column( $sitepress->get_active_languages(), 'code' ),
					[ $sitepress->get_default_language() ],
				)
				as $language
			) {
				$list[ "base_wpml_$language" ] = [
					'endpoint' => "$language/{$list['base']['endpoint']}",
				] + $list['base'];
			}
	}

	return $list;
}

/**
 * Deletes all sitemap transients, instead of just one.
 *
 * We didn't implement this in our default APIs because we want to trigger WP hooks.
 * Executing database queries directly bypass those. So, we do this afterward.
 *
 * @hook the_seo_framework_cleared_sitemap_transients 10
 * @since 3.1.0
 * @since 5.0.0 Removed clearing once-per-request restriction.
 * @global \wpdb $wpdb
 * @access private
 */
function _wpml_flush_sitemap() {
	global $wpdb;

	$transient_prefix = Sitemap\Cache::get_transient_prefix();

	$wpdb->query( $wpdb->prepare(
		"DELETE FROM $wpdb->options WHERE option_name LIKE %s",
		$wpdb->esc_like( "_transient_$transient_prefix" ) . '%',
	) );

	// We didn't use a wildcard after "_transient_" to reduce scans.
	// A second query is faster on saturated sites.
	$wpdb->query( $wpdb->prepare(
		"DELETE FROM $wpdb->options WHERE option_name LIKE %s",
		$wpdb->esc_like( "_transient_timeout_$transient_prefix" ) . '%',
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
		|| ! Helper\Compatibility::can_i_use(
			[
				'methods' => [
					[ $sitepress, 'get_default_language' ],
					[ $sitepress, 'get_current_language' ],
					[ $sitepress, 'is_translated_post_type' ],
				],
			],
		)
	) return $args;

	if ( $sitepress->get_default_language() === $sitepress->get_current_language() ) return $args;

	// Filter out only 'Not translatable'.
	$args['post_type'] = array_filter( (array) $args['post_type'], [ $sitepress, 'is_translated_post_type' ] );

	return $args;
}
