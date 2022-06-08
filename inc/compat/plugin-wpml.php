<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\WPML
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and \tsf()->_verify_include_secret( $_secret ) or die;

/**
 * Warns homepage global title and description about receiving input.
 *
 * @since 2.8.0
 */
\add_filter( 'the_seo_framework_warn_homepage_global_title', '__return_true' );
\add_filter( 'the_seo_framework_warn_homepage_global_description', '__return_true' );
\add_filter( 'the_seo_framework_tell_multilingual_sitemap', '__return_true' );

\add_action( 'current_screen', __NAMESPACE__ . '\\_wpml_do_current_screen_action' );
/**
 * Adds WPML filters based on current screen.
 *
 * @since 2.8.0
 * @access private
 */
function _wpml_do_current_screen_action() {

	if ( \tsf()->is_seo_settings_page() ) {
		\add_filter( 'wpml_admin_language_switcher_items', __NAMESPACE__ . '\\_wpml_remove_all_languages' );
	}
}

/**
 * Removes "All languages" option from WPML admin switcher.
 *
 * FIXME: Why did we do this again? Does it even affect the settings? Does it fix the home query? Remove me?
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
 * Can only clear once per request.
 *
 * @since 3.1.0
 * @global \wpdb $wpdb
 * @access private
 *
 * @param string $type    The flush type. Comes in handy when you use a catch-all function.
 * @param int    $id      The post, page or TT ID. Defaults to tsf()->get_the_real_ID().
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

		// We didn't use a wildcard after "_transient_" to reduce scans.
		// A second query is faster on saturated sites.
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $wpdb->options WHERE option_name LIKE %s",
				$wpdb->esc_like( '_transient_timeout_tsf_sitemap_' ) . '%'
			)
		); // No cache OK. DB call ok.

		$cleared = true;
	}
}

\add_action( 'the_seo_framework_sitemap_header', __NAMESPACE__ . '\\_wpml_sitemap_filter_display_translatables' );
/**
 * Filters "display translatable" post types from the sitemap query arguments.
 * Only appends actually translated posts to the translated sitemap.
 *
 * @since 4.1.4
 * @access private
 */
function _wpml_sitemap_filter_display_translatables() {
	// ez.
	\add_filter( 'wpml_should_use_display_as_translated_snippet', '__return_false' );
}

\add_action( 'the_seo_framework_sitemap_hpt_query_args', __NAMESPACE__ . '\\_wpml_sitemap_filter_non_translatables' );
\add_action( 'the_seo_framework_sitemap_nhpt_query_args', __NAMESPACE__ . '\\_wpml_sitemap_filter_non_translatables' );
/**
 * Filters nontranslatable post types from the sitemap query arguments.
 * Only appends when the default sitemap language is not displayed.
 *
 * @since 4.1.4
 * @access private
 * @global $sitepress \SitePress
 *
 * @param array $args The query arguments.
 * @return array The augmented query arguments.
 */
function _wpml_sitemap_filter_non_translatables( $args ) {
	global $sitepress;

	if ( empty( $sitepress )
	|| ! method_exists( $sitepress, 'get_default_language' )
	|| ! method_exists( $sitepress, 'get_current_language' )
	|| ! method_exists( $sitepress, 'is_translated_post_type' ) )
		return $args;

	if ( $sitepress->get_default_language() === $sitepress->get_current_language() ) return $args;

	// Filter out only 'Not translatable'.
	$args['post_type'] = array_filter( (array) $args['post_type'], [ $sitepress, 'is_translated_post_type' ] );

	return $args;
}
