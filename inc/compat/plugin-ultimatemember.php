<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\UltimateMember
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and \tsf()->_verify_include_secret( $_secret ) or die;

// At 9999 the user query should be registered (um\core\Rewrite::locate_user_profile). So, we use 9999+1 = 100000.
\add_action( 'template_redirect', __NAMESPACE__ . '\\_um_reinstate_title_support', 100000 );
/**
 * Reinstates title support if a UM-controlled profile page is detected.
 *
 * @since 4.2.0
 * @access private
 */
function _um_reinstate_title_support() {

	if ( ! \tsf()->can_i_use( [
		'functions' => [
			'um_is_core_page',
			'um_get_requested_user',
		],
	] ) ) return;

	if ( \um_is_core_page( 'user' ) && \um_get_requested_user() ) {
		// This number has nothing to do with the reasoning hereinbefore -- merely to reflect their API.
		\add_filter( 'wp_title', 'um_dynamic_user_profile_pagetitle', 100000, 2 );
		\add_filter( 'pre_get_document_title', 'um_dynamic_user_profile_pagetitle', 100000, 2 );
	}
}

\add_filter( 'the_seo_framework_query_supports_seo', __NAMESPACE__ . '\\_um_determine_support' );
/**
 * Filters query support on UM pages.
 *
 * @since 4.2.0
 * @access private
 *
 * @param bool $supported Whether the query supports SEO.
 * @return string The filtered title.
 */
function _um_determine_support( $supported = true ) {

	// No need to modify support if it's already not supported.
	if ( ! $supported ) return $supported;

	if ( ! \tsf()->can_i_use( [
		'functions' => [
			'um_queried_user',
			'um_is_core_page',
		],
	] ) ) return $supported;

	/**
	 * We do not test for 'um_get_requested_user()' -- but this is safe.
	 * If `um_queried_user() && um_is_core_page( 'user' ) is true, UM forces um_get_requested_user()
	 * to return something, or otherwise redirects the visitor. This means we
	 * can safely hand over SEO-support to Ultimate Member.
	 */
	return ! ( \um_queried_user() && \um_is_core_page( 'user' ) );
}
