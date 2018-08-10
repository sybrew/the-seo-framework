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
