<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\Polylang
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
 * @param $string The title or description
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
 * @param array $whitelist
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
 * @param array $blacklist
 * @return array
 */
function _blaclist_tsf_sitemap_styles( $blacklist ) {
	$blacklist[] = [ 'function' => 'get_sitemap_xsl_url' ];
	return $blacklist;
}

\add_filter( 'the_seo_framework_rel_canonical_output', __NAMESPACE__ . '\\_fix_home_url', 10, 2 );
\add_filter( 'the_seo_framework_ogurl_output', __NAMESPACE__ . '\\_fix_home_url', 10, 2 );
/**
 * Adds a trailing slash to whatever's deemed as the homepage URL.
 * This fixes user_trailingslashit() issues.
 *
 * @since 3.2.4
 */
function _fix_home_url( $url, $id ) {
	return \the_seo_framework()->is_front_page_by_ID( $id ) && \get_option( 'permalink_structure' ) ? \trailingslashit( $url ) : $url;
}
