<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\Elementor
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

\add_filter( 'the_seo_framework_public_post_types', __NAMESPACE__ . '\\_elementor_fix_dumb_post_types' );

/**
 * Does the job Elementor was sought to do by everyone back in 2016, by chiseling
 * off their non-public post types purported as public.
 *
 * This solely affects The SEO Framework.
 *
 * @hook the_seo_framework_public_post_types 10
 * @since 4.2.0
 *
 * @param string[] $post_types The list of should-be public post types.
 * @return string[] The list of actual public post types.
 */
function _elementor_fix_dumb_post_types( $post_types ) {
	return array_diff( $post_types, [ 'e-landing-page', 'elementor_library' ] );
}
