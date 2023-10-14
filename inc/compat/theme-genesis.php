<?php
/**
 * @package The_SEO_Framework\Compat\Theme\Genesis
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

\add_filter( 'genesis_detect_seo_plugins', __NAMESPACE__ . '\\_disable_genesis_seo', 10, 1 );
\add_filter( 'the_seo_framework_term_meta_defaults', __NAMESPACE__ . '\\_genesis_get_term_meta', 10, 2 );

/**
 * Removes the Genesis SEO meta boxes on the SEO Settings page
 *
 * @hook genesis_detect_seo_plugins 10
 * @since 2.8.0
 * @access private
 *
 * @return array
 */
function _disable_genesis_seo() {
	return [
		'classes'   => [],
		'functions' => [],
		'constants' => [
			'THE_SEO_FRAMEWORK_PRESENT',
		],
	];
}

/**
 * Returns Genesis term meta.
 *
 * @hook the_seo_framework_term_meta_defaults 10
 * @since 2.8.0
 * @since 3.1.0 Now filters empty fields.
 * @TODO remove or shift this, so that we can reduce what's stored in the database via s_term_meta?
 *
 * @param array $data    The current term meta.
 * @param int   $term_id The current term ID.
 * @return array The updated term meta.
 */
function _genesis_get_term_meta( $data = [], $term_id = 0 ) {

	$genesis_data = array_filter( [
		'doctitle'    => \get_term_meta( $term_id, 'doctitle', true ) ?: false,
		'description' => \get_term_meta( $term_id, 'description', true ) ?: false,
		'noindex'     => \get_term_meta( $term_id, 'noindex', true ) ?: false,
		'nofollow'    => \get_term_meta( $term_id, 'nofollow', true ) ?: false,
		'noarchive'   => \get_term_meta( $term_id, 'noarchive', true ) ?: false,
	] );

	return array_merge( $data, $genesis_data );
}
