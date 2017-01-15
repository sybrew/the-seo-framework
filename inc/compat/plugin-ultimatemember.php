<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\UltimateMember
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) and $_this = \the_seo_framework_class() and $this instanceof $_this or die;

/**
 * Determines if the user detection functions are available.
 * Checks for: um_user, um_is_core_page, um_get_requested_user
 *
 * @since 2.8.0
 * @staticvar bool $cache
 *
 * @return bool Whether functions can be used.
 */
function _um_user_functions_available() {

	static $cache = null;

	return isset( $cache ) ? $cache : $cache = \the_seo_framework()->can_i_use(
		array( 'functions' => array( 'um_user', 'um_is_core_page', 'um_get_requested_user' ) ),
		false
	);
}

\add_filter( 'the_seo_framework_custom_field_title', __NAMESPACE__ . '\\_um_filter_custom_field_title', 10, 3 );
/**
 * Filters the custom title.
 *
 * @since 2.8.0
 * @access private
 *
 * @param string $title The special title.
 * @param int $id The post or TT ID.
 * @param string $axonomy the TT name.
 * @return string The user title.
 */
function _um_filter_custom_field_title( $title = '', $id = '', $taxonomy = '' ) {

	if ( \The_SEO_Framework\_um_user_functions_available() && \um_is_core_page( 'user' ) && \um_get_requested_user() ) {
		$title = \um_user( 'display_name' );
	}

	return $title;
}
