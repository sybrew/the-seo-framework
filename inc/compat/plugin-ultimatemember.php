<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\UltimateMember
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and \tsf()->_verify_include_secret( $_secret ) or die;

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
