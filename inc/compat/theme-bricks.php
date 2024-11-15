<?php
/**
 * @package The_SEO_Framework\Compat\Theme\Bricks
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

\add_filter( 'the_seo_framework_public_post_types', __NAMESPACE__ . '\\_bricks_fix_public_post_types' );
\add_filter( 'the_seo_framework_public_taxonomies', __NAMESPACE__ . '\\_bricks_fix_public_taxonomies' );

/**
 * Removes support for Bricks' non-public post types conditionally.
 *
 * This solely affects The SEO Framework.
 *
 * @hook the_seo_framework_public_post_types 10
 * @since 5.1.0
 *
 * @param string[] $post_types The list of should-be public post types.
 * @return string[] The list of actual public post types.
 */
function _bricks_fix_public_post_types( $post_types ) {

	// phpcs:ignore, TSF.Performance.Functions.PHP -- this method is memoized via filter, autoload is false.
	if ( \defined( 'BRICKS_DB_TEMPLATE_SLUG' ) && class_exists( \Bricks\Database::class, false ) )
		if ( ! \Bricks\Database::get_setting( 'publicTemplates' ) )
			$post_types = array_diff( $post_types, [ BRICKS_DB_TEMPLATE_SLUG ] );

	return $post_types;
}

/**
 * Removes support for Bricks' template taxonomies.
 * They aren't used for display, only organizing templates.
 *
 * This solely affects The SEO Framework.
 *
 * @hook the_seo_framework_public_taxonomies 10
 * @since 5.1.0
 *
 * @param string[] $taxonomies The list of should-be public taxonomies.
 * @return string[] The list of actual public taxonomies.
 */
function _bricks_fix_public_taxonomies( $taxonomies ) {

	$unset = [];

	if ( \defined( 'BRICKS_DB_TEMPLATE_TAX_TAG' ) )
		$unset[] = BRICKS_DB_TEMPLATE_TAX_TAG;

	if ( \defined( 'BRICKS_DB_TEMPLATE_TAX_BUNDLE' ) )
		$unset[] = BRICKS_DB_TEMPLATE_TAX_BUNDLE;

	return array_diff( $taxonomies, $unset );
}
