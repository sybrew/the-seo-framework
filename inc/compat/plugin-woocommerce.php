<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\WooCommerce
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and \the_seo_framework()->_verify_include_secret( $_secret ) or die;

\add_action( 'woocommerce_init', __NAMESPACE__ . '\\_init_wc_compat' );
/**
 * Initializes WooCommerce compatibility.
 *
 * @since 3.1.0
 * @since 4.0.3 Added primary term support to products.
 * @since 4.1.1 Added primary term support to category widgets.
 * @access private
 * @uses \is_product()
 */
function _init_wc_compat() {
	\add_action(
		'the_seo_framework_do_before_output',
		function() {
			/**
			 * Removes TSF breadcrumbs. WooCommerce outputs theirs.
			 */
			if ( \function_exists( '\\is_product' ) && \is_product() ) {
				\add_filter( 'the_seo_framework_json_breadcrumb_output', '__return_false' );
			}
		}
	);

	$tsf = \the_seo_framework();

	// Adjust the product link acknowledging the primary category.
	\add_filter( 'wc_product_post_type_link_product_cat', [ $tsf, '_adjust_post_link_category' ], 10, 3 );

	// Adjust the structured-data breadcrumb primary term. Coincidentally(?), it uses the same filter structure; although, it misses the $post object.
	\add_filter( 'woocommerce_breadcrumb_main_term', [ $tsf, '_adjust_post_link_category' ], 10, 2 );

	// Adjust the widget's tree primary term. Coincidentally(?), it uses the same filter structure; although, it misses the $post object.
	\add_filter( 'woocommerce_product_categories_widget_main_term', [ $tsf, '_adjust_post_link_category' ], 10, 2 );
}

\add_filter( 'the_seo_framework_real_id', __NAMESPACE__ . '\\_set_real_id_wc_shop' );
/**
 * Sets the correct shop ID on the shop page.
 *
 * @since 4.0.5
 * @access private
 *
 * @param int $id The current ID.
 * @return int
 */
function _set_real_id_wc_shop( $id ) {

	if ( \the_seo_framework()->is_wc_shop() ) {
		$id = (int) \get_option( 'woocommerce_shop_page_id' );
	}

	return $id;
}

\add_filter( 'the_seo_framework_is_singular_archive', __NAMESPACE__ . '\\_set_shop_singular_archive', 10, 2 );
/**
 * Sets singular archives for the WC shop page.
 *
 * @since 4.0.5
 * @access private
 *
 * @param bool     $is_singular_archive Whether the post ID is a singular archive.
 * @param int|null $id                  The supplied post ID. Null when in the loop.
 * @return bool
 */
function _set_shop_singular_archive( $is_singular_archive, $id ) {
	return $is_singular_archive || \the_seo_framework()->is_wc_shop( $id );
}

\add_filter( 'the_seo_framework_is_shop', __NAMESPACE__ . '\\_set_wc_is_shop', 10, 2 );
/**
 * Sets the is_shop query.
 *
 * @since 4.0.5
 * @access private
 * @TODO is this redundant for TSF?
 *
 * @param bool             $is_shop Whether this is a shop page.
 * @param int|WP_Post|null $post    Post ID or post object.
 * @return bool
 */
function _set_wc_is_shop( $is_shop, $post ) {
	return $is_shop || \the_seo_framework()->is_wc_shop( $post );
}

\add_filter( 'the_seo_framework_is_product', __NAMESPACE__ . '\\_set_wc_is_product', 10, 2 );
/**
 * Sets the is_product query.
 *
 * @since 4.0.5
 *
 * @param bool             $is_product Whether this is a product page.
 * @param int|WP_Post|null $post       Post ID or post object.
 * @return bool
 */
function _set_wc_is_product( $is_product, $post ) {
	return $is_product || \the_seo_framework()->is_wc_product( $post );
}

\add_filter( 'the_seo_framework_is_product_admin', __NAMESPACE__ . '\\_set_wc_is_product_admin' );
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
function _set_wc_is_product_admin( $is_product_admin ) {
	return $is_product_admin || \the_seo_framework()->is_wc_product_admin();
}

\add_filter( 'the_seo_framework_image_generation_params', __NAMESPACE__ . '\\_adjust_wc_image_generation_params', 10, 2 );
/**
 * Adjusts image generation parameters.
 *
 * @since 4.0.5 (introduced @ 4.0.0, renamed to prevent conflict)
 * @access private
 *
 * @param array      $params : [
 *    string  size:     The image size to use.
 *    boolean multi:    Whether to allow multiple images to be returned.
 *    array   cbs:      The callbacks to parse. Ideally be generators, so we can halt remotely.
 *    array   fallback: The callbacks to parse. Ideally be generators, so we can halt remotely.
 * ];
 * @param array|null $args The query arguments. Contains 'id' and 'taxonomy'.
 *                         Is null when query is autodetermined.
 * @return array $params
 */
function _adjust_wc_image_generation_params( $params, $args ) {

	$is_product          = false;
	$is_product_category = false;

	if ( null === $args ) {
		$is_product          = \the_seo_framework()->is_wc_product();
		$is_product_category = \function_exists( '\\is_product_category' ) && \is_product_category();
	} else {
		if ( $args['taxonomy'] ) {
			if ( \function_exists( '\\is_product_category' ) ) {
				$term                = \get_term( $args['id'], $args['taxonomy'] );
				$is_product_category = $term && \is_product_category( $term );
			}
		} else {
			$is_product = \the_seo_framework()->is_wc_product( $args['id'] );
		}
	}

	if ( $is_product ) {
		$params['cbs']['wc_gallery'] = __NAMESPACE__ . '\\_get_product_gallery_image_details';
	}

	if ( $is_product_category ) {
		$params['cbs']['wc_thumbnail'] = __NAMESPACE__ . '\\_get_product_category_thumbnail_image_details';
	}

	return $params;
}

/**
 * Generates image URLs and IDs from the WooCommerce product gallary entries.
 *
 * @since 4.0.0
 * @access private
 * @generator
 *
 * @param array|null $args The query arguments. Accepts 'id' and 'taxonomy'.
 *                         Leave null to autodetermine query.
 * @param string     $size The size of the image to get.
 * @yield array : {
 *    string url: The image URL location,
 *    int    id:  The image ID,
 * }
 */
function _get_product_gallery_image_details( $args = null, $size = 'full' ) {

	$post_id = isset( $args['id'] ) ? $args['id'] : \the_seo_framework()->get_the_real_ID();

	$attachment_ids = [];

	if ( $post_id && \metadata_exists( 'post', $post_id, '_product_image_gallery' ) ) {
		$product_image_gallery = \get_post_meta( $post_id, '_product_image_gallery', true );

		$attachment_ids = array_map( '\\absint', array_filter( explode( ',', $product_image_gallery ) ) );
	}

	if ( $attachment_ids ) {
		foreach ( $attachment_ids as $id ) {
			yield [
				'url' => \wp_get_attachment_image_url( $id, $size ),
				'id'  => $id,
			];
		}
	} else {
		yield [
			'url' => '',
			'id'  => 0,
		];
	}
}

/**
 * Generates image URL and ID from the WooCommerce product category thumbnail entries.
 *
 * @since 4.0.0
 * @access private
 * @generator
 *
 * @param array|null $args The query arguments. Accepts 'id' and 'taxonomy'.
 *                         Leave null to autodetermine query.
 * @param string     $size The size of the image to get.
 * @yield array : {
 *    string url: The image URL location,
 *    int    id:  The image ID,
 * }
 */
function _get_product_category_thumbnail_image_details( $args = null, $size = 'full' ) {

	$term_id = isset( $args['id'] ) ? $args['id'] : \the_seo_framework()->get_the_real_ID();

	$thumbnail_id = \get_term_meta( $term_id, 'thumbnail_id', true ) ?: 0;

	if ( $thumbnail_id ) {
		yield [
			'url' => \wp_get_attachment_image_url( $thumbnail_id, $size ) ?: '',
			'id'  => $thumbnail_id,
		];
	} else {
		yield [
			'url' => '',
			'id'  => 0,
		];
	}
}
