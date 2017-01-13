<?php
/**
 * @package The_SEO_Framework\Compat\Theme\Genesis
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

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
				'The_SEO_Framework\\Load',
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

\add_action( 'init', __NAMESPACE__ . '\\_genesis_add_head_attributes' );
/**
 * Adds Genesis SEO compatibility.
 *
 * @since 2.6.0
 * @since 2.8.0 Moved to compat file and renamed.
 * @access private
 */
function _genesis_add_head_attributes() {
	//* Reverse the removal of head attributes, this shouldn't affect SEO.
	\remove_filter( 'genesis_attr_head', 'genesis_attributes_empty_class' );
	\add_filter( 'genesis_attr_head', 'genesis_attributes_head' );
}
