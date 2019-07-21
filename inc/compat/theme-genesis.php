<?php
/**
 * @package The_SEO_Framework\Compat\Theme\Genesis
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = \the_seo_framework_class() and $this instanceof $_this or die;

//* Disable Genesis SEO.
\add_filter( 'genesis_detect_seo_plugins', __NAMESPACE__ . '\\_disable_genesis_seo', 10, 1 );
/**
 * Removes the Genesis SEO meta boxes on the SEO Settings page
 *
 * @since 2.8.0
 * @access private
 *
 * @param array $plugins The plugins to detect. Overwritten as this filter will fire the
 *                       detection, regardless of other SEO plugins.
 * @return array
 */
function _disable_genesis_seo( $plugins ) {

	$plugins = [
		'classes' => [
			'\\The_SEO_Framework\\Load',
		],
		'functions' => [
			'the_seo_framework',
		],
		'constants' => [
			'THE_SEO_FRAMEWORK_VERSION',
		],
	];

	return $plugins;
}

\add_filter( 'the_seo_framework_term_meta_defaults', __NAMESPACE__ . '\\_genesis_get_term_meta', 10, 2 );
/**
 * Returns Genesis term meta.
 *
 * @since 2.8.0
 * @since 3.1.0 Now filters empty fields.
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
