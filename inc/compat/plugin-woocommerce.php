<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\WooCommerce
 */
namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = \the_seo_framework_class() and $this instanceof $_this or die;

// @TODO Move everything WC related over to here.

\add_action( 'woocommerce_init', __NAMESPACE__ . '\\_init_wc_compat' );
/**
 * Initializes WooCommerce compatibility.
 *
 * @since 3.1.0
 * @uses \is_product()
 */
function _init_wc_compat() {
	\add_action( 'the_seo_framework_do_before_output', function() {
		/**
		 * Removes TSF breadcrumbs.
		 */
		if ( function_exists( '\\is_product' ) && \is_product() ) {
			\add_filter( 'the_seo_framework_json_breadcrumb_output', '__return_false' );
		}
	} );
}
