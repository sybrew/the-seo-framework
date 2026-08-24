<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\WPML
 * @subpackage The_SEO_Framework\Compatibility
 * @access private
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use The_SEO_Framework\{
	Helper\Query,
	Meta\URI,
};

\add_filter( 'the_seo_framework_sitemap_endpoint_list', __NAMESPACE__ . '\_wpml_register_sitemap_languages', 20 );
\add_filter( 'the_seo_framework_sitemap_settings_language_endpoints', __NAMESPACE__ . '\_wpml_sitemap_language_endpoints' );
\add_filter( 'the_seo_framework_sitemap_base_path', __NAMESPACE__ . '\_wpml_fix_sitemap_base_path' );
\add_action( 'the_seo_framework_cleared_sitemap_transients', __NAMESPACE__ . '\_wpml_flush_sitemap', 10 );
\add_action( 'the_seo_framework_sitemap_header', __NAMESPACE__ . '\_wpml_sitemap_filter_display_translatables' );
\add_action( 'the_seo_framework_sitemap_hpt_query_args', __NAMESPACE__ . '\_wpml_sitemap_filter_non_translatables' );
\add_action( 'the_seo_framework_sitemap_nhpt_query_args', __NAMESPACE__ . '\_wpml_sitemap_filter_non_translatables' );

/**
 * Registers per-language sitemaps for robots.txt and request matching.
 *
 * Plain permalinks match extra IDs (`?tsf-sitemap=_base_wpml_nl`). WPML still
 * takes language from the request URL, so those sitemaps stay in the default
 * language. Pretty permalinks are the supported path.
 *
 * @hook the_seo_framework_sitemap_endpoint_list 20
 * @since 5.0.5
 * @since 5.1.5 1. Now prefixes endpoint IDs with an underscore.
 *              2. Now sets a language-specific endpoint regex so directory sitemaps match
 *                 when the sitemap base path is the unfiltered home.
 *              3. Now registers a non-advertised directory alias for the default language
 *                 when "Use directory for default language" is enabled.
 * @param array[] $list {
 *     A list of sitemap endpoints keyed by ID.
 *
 *     @type string|false $lock_id  Optional. The cache key to use for locking. Defaults to index 'id'.
 *                                  Set to false to disable locking.
 *     @type string|false $cache_id Optional. The cache key to use for storing. Defaults to index 'id'.
 *                                  Set to false to disable caching.
 *     @type string       $endpoint The expected "pretty" endpoint, meant for administrative display.
 *     @type string       $regex    The endpoint regex, following the home path regex.
 *     @type callable     $callback The callback for the sitemap output.
 *     @type bool         $robots   Whether the endpoint should be mentioned in the robots.txt file.
 * }
 * @return array[]
 */
function _wpml_register_sitemap_languages( $list ) {

	global $sitepress;

	if ( empty( $list['base'] ) )
		return $list;

	if (
		   empty( $sitepress )
		|| ! Helper\Compatibility::can_i_use(
			[
				'methods'   => [
					[ $sitepress, 'get_default_language' ],
					[ $sitepress, 'get_active_languages' ],
					[ $sitepress, 'get_setting' ],
				],
				'constants' => [
					'WPML_LANGUAGE_NEGOTIATION_TYPE_DIRECTORY',
					'WPML_LANGUAGE_NEGOTIATION_TYPE_PARAMETER',
				],
			],
		)
	) return $list;

	$language_codes = array_column( $sitepress->get_active_languages(), 'code' );

	// Do most work outside of a loop. We have two loops because of this.
	// We fall back to -1 because null/false match with '0'
	switch ( $sitepress->get_setting( 'language_negotiation_type' ) ) {
		case \WPML_LANGUAGE_NEGOTIATION_TYPE_PARAMETER: // 3
			foreach (
				array_diff(
					$language_codes,
					[ $sitepress->get_default_language() ],
				)
				as $language
			) {
				$list[ "_base_wpml_$language" ] = [
					'endpoint' => URI\Utils::append_query_to_url(
						$list['base']['endpoint'],
						"lang=$language",
					),
				] + $list['base'];
			}
			break;
		case \WPML_LANGUAGE_NEGOTIATION_TYPE_DIRECTORY: // 1
			$default         = $sitepress->get_default_language();
			$dir_for_default = ! empty( $sitepress->get_setting( 'urls' )['directory_for_default_language'] );

			foreach ( $language_codes as $language ) {
				// Skip when the default language has no directory (checkbox off).
				if ( $language === $default && ! $dir_for_default )
					continue;

				$endpoint = "$language/{$list['base']['endpoint']}";

				$list[ "_base_wpml_$language" ] = [
					'endpoint' => $endpoint,
					'regex'    => '/^' . preg_quote( $endpoint, '/' ) . '/i',
					'robots'   => $language !== $default,
				] + $list['base'];
			}
	}

	return $list;
}

/**
 * Lists advertised WPML sitemap endpoints for SEO Settings.
 *
 * Only the settings view applies this filter, so language names are not loaded
 * for robots.txt or sitemap matching.
 *
 * @hook the_seo_framework_sitemap_settings_language_endpoints 10
 * @since 5.1.5
 * @global \SitePress $sitepress
 *
 * @param string[] $endpoints Administrative language names keyed by sitemap endpoint ID.
 * @return string[]
 */
function _wpml_sitemap_language_endpoints( $endpoints ) {

	global $sitepress;

	if (
		   empty( $sitepress )
		|| ! Helper\Compatibility::can_i_use(
			[
				'methods'   => [
					[ $sitepress, 'get_default_language' ],
					[ $sitepress, 'get_active_languages' ],
					[ $sitepress, 'get_setting' ],
				],
				'constants' => [
					'WPML_LANGUAGE_NEGOTIATION_TYPE_DIRECTORY',
					'WPML_LANGUAGE_NEGOTIATION_TYPE_PARAMETER',
				],
			],
		)
	) return $endpoints;

	switch ( $sitepress->get_setting( 'language_negotiation_type' ) ) {
		case \WPML_LANGUAGE_NEGOTIATION_TYPE_PARAMETER: // 3
		case \WPML_LANGUAGE_NEGOTIATION_TYPE_DIRECTORY: // 1
			$active_languages = $sitepress->get_active_languages();

			foreach (
				array_diff(
					array_keys( $active_languages ),
					[ $sitepress->get_default_language() ],
				)
				as $code
			) {
				$endpoints[ "_base_wpml_$code" ] = ( $active_languages[ $code ]['display_name'] ?? '' ) ?: $code;
			}
	}

	return $endpoints;
}

/**
 * Returns the sitemap base path without WPML's language directory.
 *
 * WPML filters `home_url` and, from theme files, `pre_option_home`. Language
 * sitemaps are registered as endpoints off the site root, so that path is wrong
 * for matching and for robots.txt when the default language also uses a directory.
 *
 * `get_option( 'home' )` from this file is unfiltered; WPML's `pre_option_home`
 * only rewrites theme-template backtraces. `home_url` would still be converted.
 *
 * @hook the_seo_framework_sitemap_base_path 10
 * @since 5.1.5
 *
 * @param string $path The home path.
 * @return string The unfiltered home path.
 */
function _wpml_fix_sitemap_base_path( $path ) {

	$home = \get_option( 'home' );

	if ( empty( $home ) )
		return $path;

	return rtrim( parse_url( $home, \PHP_URL_PATH ) ?: '', '/' );
}

/**
 * Deletes all sitemap transients, instead of just one.
 *
 * We didn't implement this in our default APIs because we want to trigger WP hooks.
 * Executing database queries directly bypass those. So, we do this afterward.
 *
 * @hook the_seo_framework_cleared_sitemap_transients 10
 * @since 3.1.0
 * @since 5.0.0 Removed clearing once-per-request restriction.
 * @global \wpdb $wpdb
 */
function _wpml_flush_sitemap() {

	global $wpdb;

	$transient_prefix = Sitemap\Cache::get_transient_prefix();

	$wpdb->query( $wpdb->prepare(
		"DELETE FROM $wpdb->options WHERE option_name LIKE %s",
		$wpdb->esc_like( "_transient_$transient_prefix" ) . '%',
	) );

	// We didn't use a wildcard after "_transient_" to reduce scans.
	// A second query is faster on saturated sites.
	$wpdb->query( $wpdb->prepare(
		"DELETE FROM $wpdb->options WHERE option_name LIKE %s",
		$wpdb->esc_like( "_transient_timeout_$transient_prefix" ) . '%',
	) );
}

/**
 * Filters "display translatable" post types from the sitemap query arguments.
 * Only appends actually translated posts to the translated sitemap.
 *
 * @hook the_seo_framework_sitemap_header 10
 * @since 4.1.4
 */
function _wpml_sitemap_filter_display_translatables() {
	// ez.
	\add_filter( 'wpml_should_use_display_as_translated_snippet', '__return_false' );
}

/**
 * Filters nontranslatable post types from the sitemap query arguments.
 * Only appends when the default sitemap language is not displayed.
 *
 * @hook the_seo_framework_sitemap_hpt_query_args 10
 * @hook the_seo_framework_sitemap_nhpt_query_args 10
 * @since 4.1.4
 * @global \SitePress $sitepress
 *
 * @param array $args The query arguments.
 * @return array The augmented query arguments.
 */
function _wpml_sitemap_filter_non_translatables( $args ) {

	global $sitepress;

	if (
		   empty( $sitepress )
		|| ! Helper\Compatibility::can_i_use(
			[
				'methods' => [
					[ $sitepress, 'get_default_language' ],
					[ $sitepress, 'get_current_language' ],
					[ $sitepress, 'is_translated_post_type' ],
				],
			],
		)
	) return $args;

	if ( $sitepress->get_default_language() === $sitepress->get_current_language() ) return $args;

	// Filter out only 'Not translatable'.
	$args['post_type'] = array_filter( (array) $args['post_type'], [ $sitepress, 'is_translated_post_type' ] );

	return $args;
}
