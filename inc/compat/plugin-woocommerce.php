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
 * @uses \WooCommerce wc()
 */
function _init_wc_compat() {

	$wc = \wc();

	/**
	 * Removes WooCommerce breadcrumbs.
	 * We supply an option to integrate this, and when enabled, the breadcrumbs will conflict.
	 * This effectively hands over full control to the site owner regarding breadcrumbs.
	 */
	if ( isset( $wc->structured_data ) )
		\remove_action( 'woocommerce_breadcrumb', [ $wc->structured_data, 'generate_breadcrumblist_data' ], 10 );
}
