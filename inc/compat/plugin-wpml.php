<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\WPML
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) and $_this = \the_seo_framework_class() and $this instanceof $_this or die;

/**
 * Warns homepage global title and description about receiving input.
 *
 * @since 2.8.0
 */
\add_filter( 'the_seo_framework_warn_homepage_global_title', '__return_true' );
\add_filter( 'the_seo_framework_warn_homepage_global_description', '__return_true' );

\add_action( 'current_screen', __NAMESPACE__ . '\\_wpml_do_current_screen_action' );
/**
 * Adds WPML filters only on SEO plugin page.
 *
 * @since 2.8.0
 * @access private
 *
 * @param object $current_screen
 */
function _wpml_do_current_screen_action( $current_screen = '' ) {

	if ( \the_seo_framework()->is_seo_settings_page() ) {
		\add_filter( 'wpml_admin_language_switcher_items', __NAMESPACE__ . '\\_wpml_remove_all_languages' );
	}
}

/**
 * Removes "All languages" option from WPML admin switcher.
 *
 * @since 2.8.0
 * @access private
 *
 * @param array $languages_links
 * @return array
 */
function _wpml_remove_all_languages( $languages_links = array() ) {

	unset( $languages_links['all'] );

	return $languages_links;
}
