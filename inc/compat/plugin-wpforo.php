<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\wpForo
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and \tsf()->_verify_include_secret( $_secret ) or die;

\add_action( 'the_seo_framework_init', __NAMESPACE__ . '\\_wpforo_fix_page' );
/**
 * Initializes wpForo page fixes.
 *
 * @since 2.9.2
 * @since 3.1.2 1. Now disables HTML output when wpForo SEO is enabled.
 *              2. Now disables title override when wpForo Title SEO is enabled.
 */
function _wpforo_fix_page() {

	$override = [
		'title' => true,
		'meta'  => true,
	];

	if ( \function_exists( '\\wpforo_feature' ) ) {
		if ( \wpforo_feature( 'seo-meta' ) ) {
			// This also disables titles... It's OK, they handle that too.
			$override['meta'] = false;
		}
		if ( \wpforo_feature( 'seo-titles' ) ) {
			$override['title'] = false;
		}
	}
	if ( \function_exists( '\\is_wpforo_page' ) && \is_wpforo_page() ) {

		if ( $override['title'] )
			\add_filter( 'the_seo_framework_title_from_generation', __NAMESPACE__ . '\\_wpforo_filter_pre_title', 10, 2 );

		if ( $override['meta'] ) {
			\add_filter( 'get_canonical_url', __NAMESPACE__ . '\\_wpforo_filter_canonical_url', 10, 2 );

			// Remove TSF's SEO meta output.
			\remove_action( 'wp_head', 'wpforo_add_meta_tags', 1 );
		} else {
			\add_action( 'the_seo_framework_after_init', __NAMESPACE__ . '\\_wpforo_disable_html_output', 1 );
		}
	}
}

/**
 * Disables The SEO Framework's meta tag output on wpForo pages.
 *
 * @since 3.1.2 Introduced as Lambda.
 * @since 4.0.5 Introduced as function.
 * @access private
 */
function _wpforo_disable_html_output() {
	\remove_action( 'wp_head', [ \tsf(), 'html_output' ], 1 );
}

/**
 * Filters the canonical/request URL for wpForo.
 *
 * @since 2.9.2 Introduced as Lambda.
 * @since 4.0.5 Introduced as function.
 * @access private
 *
 * @param string   $canonical_url The post's canonical URL.
 * @param \WP_Post $post          Post object.
 * @return string
 */
function _wpforo_filter_canonical_url( $canonical_url, $post ) { // phpcs:ignore, VariableAnalysis.CodeAnalysis.VariableAnalysis
	return \function_exists( '\\wpforo_get_request_uri' ) ? \wpforo_get_request_uri() : $canonical_url;
}

/**
 * Fixes wpForo page Titles.
 *
 * @since 2.9.2
 * @since 3.1.0 1. No longer emits an error when no wpForo title is presented.
 *              2. Updated to support new title generation.
 * @since 4.0.0 No longer overrules external queries.
 * @access private
 *
 * @param string     $title The filter title.
 * @param array|null $args  The query arguments. Contains 'id' and 'taxonomy'.
 *                          Is null when query is autodetermined.
 * @return string $title The wpForo title.
 */
function _wpforo_filter_pre_title( $title = '', $args = null ) {

	if ( null === $args ) {
		$wpforo_title = \wpforo_meta_title( '' ); // This is either &$title or [ $title, ... ];
		$title        = \is_array( $wpforo_title ) && ! empty( $wpforo_title[0] ) ? $wpforo_title[0] : $title;
	}

	return $title;
}
