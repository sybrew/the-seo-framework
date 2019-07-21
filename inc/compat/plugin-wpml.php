<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\WPML
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = \the_seo_framework_class() and $this instanceof $_this or die;

/**
 * Warns homepage global title and description about receiving input.
 *
 * @since 2.8.0
 */
\add_filter( 'the_seo_framework_warn_homepage_global_title', '__return_true' );
\add_filter( 'the_seo_framework_warn_homepage_global_description', '__return_true' );

\add_action( 'current_screen', __NAMESPACE__ . '\\_wpml_do_current_screen_action' );
/**
 * Adds WPML filters based on current screen.
 *
 * @since 2.8.0
 * @access private
 *
 * @param \WP_Screen $current_screen The current screen object.
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
 * @param array $languages_links A list of selectable languages.
 * @return array
 */
function _wpml_remove_all_languages( $languages_links = [] ) {

	unset( $languages_links['all'] );

	return $languages_links;
}

\add_action( 'the_seo_framework_delete_cache_sitemap', __NAMESPACE__ . '\\_wpml_flush_sitemap', 10, 4 );
/**
 * Deletes all sitemap transients, instead of just one.
 *
 * @since 3.1.0
 * @global \wpdb $wpdb
 * @access private
 * @staticvar bool $cleared
 *
 * @param string $type    The flush type. Comes in handy when you use a catch-all function.
 * @param int    $id      The post, page or TT ID. Defaults to the_seo_framework()->get_the_real_ID().
 * @param array  $args    Additional arguments. They can overwrite $type and $id.
 * @param bool   $success Whether the action cleared.
 */
function _wpml_flush_sitemap( $type, $id, $args, $success ) {

	static $cleared = false;
	if ( $cleared ) return;

	if ( $success ) {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $wpdb->options WHERE option_name LIKE %s",
				$wpdb->esc_like( '_transient_tsf_sitemap_' ) . '%'
			)
		); // No cache OK. DB call ok.

		//? We didn't use a wildcard after "_transient_" to reduce scans.
		//? A second query is faster on saturated sites.
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $wpdb->options WHERE option_name LIKE %s",
				$wpdb->esc_like( '_transient_timeout_tsf_sitemap_' ) . '%'
			)
		); // No cache OK. DB call ok.

		$cleared = true;
	}
}
