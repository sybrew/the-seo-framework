<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\wpForo
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) and $_this = \the_seo_framework_class() and $this instanceof $_this or die;

add_action( 'init', __NAMESPACE__ . '\\_wpforo_fix_page' );
/**
 * Initializes wpForo page fixes.
 *
 * @since 2.9.2
 */
function _wpforo_fix_page() {

	if ( function_exists( '\\is_wpforo_page' ) && \is_wpforo_page() ) {
		\add_filter( 'the_seo_framework_pre_add_title', __NAMESPACE__ . '\\_wpforo_filter_pre_title', 10, 3 );
		\add_filter( 'the_seo_framework_url_path', __NAMESPACE__ . '\\_wpforo_filter_url_path', 10, 3 );
		\add_filter( 'the_seo_framework_description_args', __NAMESPACE__ . '\\_wpforo_filter_description_arguments', 10, 3 );

		//* Remove wpforo SEO meta output.
		\remove_action( 'wp_head', 'wpforo_add_meta_tags', 1 );
	}
}

/**
 * Fixes wpForo page Titles.
 *
 * @since 2.9.2
 * @access private
 *
 * @param string $title The filter title.
 * @param array $args The title arguments.
 * @param bool $escape Whether the output will be sanitized.
 * @return string $title The wpForo title.
 */
function _wpforo_filter_pre_title( $title, $args, $escape ) {
	$wpforo_title = \wpforo_meta_title( '' );
	return $wpforo_title[0] ?: $title;
}

/**
 * Fixes wpForo page URL paths.
 *
 * @since 2.9.2
 * @access private
 *
 * @param string $path The current path.
 * @param int $id The page/post ID.
 * @param bool $external Whether the request is external (i.e. sitemap)
 * @return string The URL path.
 */
function _wpforo_filter_url_path( $path, $id, $external ) {

	if ( $external )
		return $path;

	if ( '' === \the_seo_framework()->permalink_structure() )
		return $path;

	return \the_seo_framework()->set_url_scheme( \wpforo_get_request_uri(), 'relative' );
}

/**
 * Fixes wpForo page descriptions.
 *
 * @since 2.9.2
 * @access private
 *
 * @param array $defaults The default arguments.
 * @param array $args The method caller arguments.
 * @return array The description default arguments.
 */
function _wpforo_filter_description_arguments( $defaults, $args ) {

	//* Disable internal requests only. Magic variable (i.e. do overthink it, as it will loop).
	if ( empty( $args['social'] ) && empty( $args['get_custom_field'] ) )
		$defaults['get_custom_field'] = false;

	return $defaults;
}
