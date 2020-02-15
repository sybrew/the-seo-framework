<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\PolyLang
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = \the_seo_framework_class() and $this instanceof $_this or die;

/**
 * Warns homepage global title and description about receiving input.
 *
 * @since 3.1.0
 */
\add_filter( 'the_seo_framework_warn_homepage_global_title', '__return_true' );
\add_filter( 'the_seo_framework_warn_homepage_global_description', '__return_true' );

\add_filter( 'the_seo_framework_title_from_custom_field', __NAMESPACE__ . '\\pll__' );
\add_filter( 'the_seo_framework_title_from_generation', __NAMESPACE__ . '\\pll__' );
\add_filter( 'the_seo_framework_generated_description', __NAMESPACE__ . '\\pll__' );
\add_filter( 'the_seo_framework_custom_field_description', __NAMESPACE__ . '\\pll__' );
/**
 * Enables string translation support on titles and descriptions.
 *
 * @since 3.1.0
 *
 * @param string $string The title or description
 * @return string
 */
function pll__( $string ) {
	if ( function_exists( 'PLL' ) && function_exists( '\\pll__' ) ) {
		if ( \PLL() instanceof \PLL_Frontend ) {
			return \pll__( $string );
		}
	}
	return $string;
}

\add_filter( 'pll_home_url_white_list', __NAMESPACE__ . '\\_whitelist_tsf_urls' );
\add_filter( 'pll_home_url_black_list', __NAMESPACE__ . '\\_blaclist_tsf_sitemap_styles' );
/**
 * Accompany the most broken and asinine idea in WordPress' history.
 * Adds The SEO Framework's files to their whitelist of autoincompatible doom.
 *
 * @since 3.2.4
 *
 * @param array $whitelist The wildcard file parts that are whitelisted.
 * @return array
 */
function _whitelist_tsf_urls( $whitelist ) {
	$whitelist[] = [ 'file' => 'autodescription/inc' ];
	return $whitelist;
}

/**
 * Accompany the most broken and asinine idea in WordPress' history.
 * ...and stop messing with the rewrite system while doing so.
 * Also, you should add support for class methods. Stop living in the PHP 4 era.
 *
 * @since 3.2.4
 *
 * @param array $blacklist The wildcard file parts that are blacklisted.
 * @return array
 */
function _blaclist_tsf_sitemap_styles( $blacklist ) {
	// y u no recurse
	$blacklist[] = [ 'function' => 'get_expected_sitemap_endpoint_url' ];
	$blacklist[] = [ 'function' => 'get_sitemap_base_path_info' ];
	$blacklist[] = [ 'file' => 'autodescription/inc/compat/plugin-polylang.php' ];
	return $blacklist;
}

\add_filter( 'the_seo_framework_sitemap_path_prefix', __NAMESPACE__ . '\\_fix_sitemap_prefix', 9 );
/**
 * Fixes the sitemap prefix, because setting the home URL globally requires only one filter.
 *
 * @since 4.0.0
 * @param string $prefix The path prefix. Ideally appended with a slash.
 *                       Recommended return value: "$prefix$custompath/"
 * @return string New prefix.
 */
function _fix_sitemap_prefix( $prefix = '' ) {

	if ( function_exists( '\\pll_home_url' ) ) {
		$home_url        = \home_url();
		$ruined_home_url = \pll_home_url();

		$path = trim( substr_replace( $ruined_home_url, '', 0, strlen( $home_url ) ), '/' );

		return $path ? "$prefix$path/" : $prefix;
	}

	return $prefix;
}

\add_filter( 'the_seo_framework_rel_canonical_output', __NAMESPACE__ . '\\_fix_home_url', 10, 2 );
\add_filter( 'the_seo_framework_ogurl_output', __NAMESPACE__ . '\\_fix_home_url', 10, 2 );
/**
 * Adds a trailing slash to whatever's deemed as the homepage URL.
 * This fixes user_trailingslashit() issues.
 *
 * @since 3.2.4
 * @param string $url The url to fix.
 * @param int    $id  The page or term ID.
 */
function _fix_home_url( $url, $id ) {
	return \the_seo_framework()->is_front_page_by_ID( $id ) && \get_option( 'permalink_structure' ) ? \trailingslashit( $url ) : $url;
}

\add_action( 'the_seo_framework_delete_cache_sitemap', __NAMESPACE__ . '\\_polylang_flush_sitemap', 10, 4 );
/**
 * Deletes all sitemap transients, instead of just one.
 *
 * @since 4.0.5
 * @global \wpdb $wpdb
 * @access private
 * @staticvar bool $cleared
 *
 * @param string $type    The flush type. Comes in handy when you use a catch-all function.
 * @param int    $id      The post, page or TT ID. Defaults to the_seo_framework()->get_the_real_ID().
 * @param array  $args    Additional arguments. They can overwrite $type and $id.
 * @param bool   $success Whether the action cleared.
 */
function _polylang_flush_sitemap( $type, $id, $args, $success ) {

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
