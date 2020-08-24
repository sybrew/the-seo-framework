<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\EDD
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and \the_seo_framework()->_verify_include_secret( $_secret ) or die;

\add_filter( 'the_seo_framework_is_product', __NAMESPACE__ . '\\_set_edd_is_product', 10, 2 );
/**
 * Sets the is_product query.
 *
 * @since 4.0.5
 * @access private
 *
 * @param bool             $is_product Whether this is a product page.
 * @param int|WP_Post|null $post       Post ID or post object.
 * @return bool
 */
function _set_edd_is_product( $is_product, $post ) {

	if ( ! $is_product ) {
		if ( \function_exists( 'edd_get_download' ) ) {
			$post_id  = $post ? \get_post( $post ) : \the_seo_framework()->get_the_real_ID();
			$download = \edd_get_download( $post_id );

			$is_product = ! empty( $download->ID );
		}
	}

	return $is_product;
}

\add_filter( 'the_seo_framework_is_product_admin', __NAMESPACE__ . '\\_set_edd_is_product_admin' );
/**
 * Sets the is_product_admin query.
 *
 * @since 4.0.5
 * @access private
 * @TODO is this redundant for TSF?
 *
 * @param bool $is_product_admin Whether this is a product admin query.
 * @return bool
 */
function _set_edd_is_product_admin( $is_product_admin ) {

	if ( ! $is_product_admin ) {
		$tsf = \the_seo_framework();
		// Checks for "is_singular_admin()" because the post type is non-hierarchical.
		$is_product_admin = $tsf->is_singular_admin() && 'download' === $tsf->get_admin_post_type();
	}

	return $is_product_admin;
}
