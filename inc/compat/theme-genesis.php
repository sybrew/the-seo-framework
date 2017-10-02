<?php
/**
 * @package The_SEO_Framework\Compat\Theme\Genesis
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) and $_this = \the_seo_framework_class() and $this instanceof $_this or die;

//* Disable Genesis SEO.
\add_filter( 'genesis_detect_seo_plugins', __NAMESPACE__ . '\\_disable_genesis_seo', 10, 1 );
/**
 * Removes the Genesis SEO meta boxes on the SEO Settings page
 *
 * @since 2.8.0
 * @access private
 *
 * @param array $plugins, overwritten as this filter will fire the
 * detection, regardless of other SEO plugins.
 * @return array Plugins to detect.
 */
function _disable_genesis_seo( $plugins ) {

	$plugins = array(
		'classes' => array(
			'\The_SEO_Framework\\Load',
		),
		'functions' => array(
			'the_seo_framework',
		),
		'constants' => array(
			'THE_SEO_FRAMEWORK_VERSION',
		),
	);

	return $plugins;
}

\add_filter( 'the_seo_framework_get_term_meta', __NAMESPACE__ . '\\_genesis_get_term_meta', 10, 2 );
/**
 * Returns Genesis term meta.
 *
 * @since 2.8.0
 *
 * @param array $data The current term meta.
 * @param int $term_id The current term ID.
 * @return array The Genesis Term meta.
 */
function _genesis_get_term_meta( $data = array(), $term_id = 0 ) {

	$data['doctitle'] = \get_term_meta( $term_id, 'doctitle', true );
	$data['description'] = \get_term_meta( $term_id, 'description', true );
	$data['noindex'] = \get_term_meta( $term_id, 'noindex', true );
	$data['nofollow'] = \get_term_meta( $term_id, 'nofollow', true );
	$data['noarchive'] = \get_term_meta( $term_id, 'noarchive', true );

	return $data;
}
