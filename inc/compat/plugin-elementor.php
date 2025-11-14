<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\Elementor
 * @subpackage The_SEO_Framework\Compatibility
 * @access private
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use The_SEO_Framework\Helper\Query;

const ELEMENTOR_DUMB_POST_TYPES = [ // TODO remove "ELEMENTOR_" prefix when we namespace this file properly
	'e-landing-page',
	'elementor_library',
	'e-floating-buttons',
];

\add_filter( 'the_seo_framework_public_post_types', __NAMESPACE__ . '\_elementor_fix_dumb_post_types' );
\add_filter( 'the_seo_framework_robots_meta_array', __NAMESPACE__ . '\_elementor_force_noindex' );

/**
 * Does the job Elementor was sought to do by everyone back in 2016, by chiseling
 * off their non-public post types purported as public.
 *
 * This solely affects The SEO Framework.
 *
 * This filter only runs on admin pages and sitemap to hide the post type from
 * TSF's interface and improve sitemap rendering performance.
 *
 * @hook the_seo_framework_public_post_types 10
 * @since 4.2.0
 * @since 5.1.3 Now only runs on admin and sitemap.
 *
 * @param string[] $post_types The list of should-be public post types.
 * @return string[] The list of actual public post types.
 */
function _elementor_fix_dumb_post_types( $post_types ) {

	if ( \is_admin() || Query::is_sitemap() )
		return array_diff( $post_types, ELEMENTOR_DUMB_POST_TYPES );

	return $post_types;
}

/**
 * Forces noindex on Elementor's post types.
 *
 * Elementor incorrectly made these post types publicly queryable.
 * This filter ensures they are not indexed.
 *
 * @hook the_seo_framework_robots_meta_array 10
 * @since 5.1.3
 *
 * @param array $meta {
 *     The parsed robots meta.
 *
 *     @type string $noindex           Ideally be empty or 'noindex'
 *     @type string $nofollow          Ideally be empty or 'nofollow'
 *     @type string $noarchive         Ideally be empty or 'noarchive'
 *     @type string $max_snippet       Ideally be empty or 'max-snippet:<R>=-1>'
 *     @type string $max_image_preview Ideally be empty or 'max-image-preview:<none|standard|large>'
 *     @type string $max_video_preview Ideally be empty or 'max-video-preview:<R>=-1>'
 * }
 * @return array
 */
function _elementor_force_noindex( $meta ) {

	// Already noindex, nothing to do.
	if ( 'noindex' === $meta['noindex'] )
		return $meta;

	if ( \in_array( Query::get_post_type_real_id(), ELEMENTOR_DUMB_POST_TYPES, true ) )
		$meta['noindex'] = 'noindex';

	return $meta;
}
