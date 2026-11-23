<?php
/**
 * @package The_SEO_Framework\Playground\WooCommerce
 */

defined( 'ABSPATH' ) or die;

/**
 * Keeps the isolated catalog small while exercising archive pagination.
 *
 * @since 5.2.2
 *
 * @return int
 */
function wpr_woocommerce_page_size() {
	return 2;
}
add_filter(
	'loop_shop_per_page',
	'wpr_woocommerce_page_size',
	999,
	0,
);

/**
 * Exposes query identity and real WooCommerce breadcrumbs for HTTP assertions.
 *
 * @since 5.2.2
 */
function wpr_woocommerce_identity() {

	if ( ! function_exists( 'WC' ) ) return;

	header(
		'X-WPR-Matched-Query: ' . $GLOBALS['wp']->matched_query,
	);
	header(
		'X-WPR-Singular: ' . ( is_singular() ? get_queried_object_id() : 0 ),
	);
	header(
		'X-WPR-Category: ' . ( is_category() ? get_queried_object_id() : 0 ),
	);
	header( 'X-WPR-Shop: ' . (int) is_shop() );
	header(
		'X-WPR-Taxonomy: ' . ( is_tax() ? get_queried_object_id() : 0 ),
	);
	header(
		'X-WPR-Archive: ' . ( is_post_type_archive() ? get_query_var( 'post_type' ) : '' ),
	);
	header(
		'X-WPR-Product: ' . ( is_product() ? get_queried_object_id() : 0 ),
	);
	header(
		'X-WPR-Product-Category: ' . ( is_product_category() ? get_queried_object_id() : 0 ),
	);

	if ( is_product_category() ) {
		global $wp_query;

		header( 'X-WPR-Product-Listing: ' . json_encode( [
			'order'   => $wp_query->query['order'] ?? '',
			'orderby' => $wp_query->query['orderby'] ?? '',
			'ids'     => wp_list_pluck( $wp_query->posts, 'ID' ),
		] ) );
	}

	header(
		'X-WPR-Product-Tag: ' . ( is_product_tag() ? get_queried_object_id() : 0 ),
	);
	$breadcrumb = new WC_Breadcrumb();
	header(
		'X-WPR-Breadcrumb: ' . rawurlencode( json_encode( $breadcrumb->generate() ) ),
	);
}
add_action(
	'template_redirect',
	'wpr_woocommerce_identity',
	-100,
	0,
);

/**
 * Records a fixture-owned object before subsequent setup can fail.
 *
 * @since 5.2.2
 *
 * @param string $type  Post or taxonomy name.
 * @param int    $id    Created object ID.
 * @param array  $state Fixture state, updated by reference.
 */
function wpr_woocommerce_record( $type, $id, &$state ) {

	if ( ! $id )
		wp_send_json(
			[ 'error' => "Could not create fixture $type." ],
			500,
		);

	$state['created'][] = [ $type, (int) $id ];
	update_option( 'wpr_woocommerce_state', $state, false );
}

/**
 * Gets or creates a fixture term without changing existing terms.
 *
 * @since 5.2.2
 *
 * @param string $slug     Term slug.
 * @param string $taxonomy Taxonomy name.
 * @param int    $parent   Parent term ID.
 * @param array  $state    Fixture state, updated by reference.
 * @return int
 */
function wpr_woocommerce_term( $slug, $taxonomy, $parent, &$state ) {

	$term = get_term_by( 'slug', $slug, $taxonomy );

	if ( $term ) {
		if ( $parent !== $term->parent )
			wp_send_json(
				[ 'error' => "Fixture term $taxonomy/$slug has parent {$term->parent}, expected $parent." ],
				409,
			);

		return $term->term_id;
	}

	$term = wp_insert_term(
		$slug,
		$taxonomy,
		[
			'slug'   => $slug,
			'parent' => $parent,
		],
	);

	if ( is_wp_error( $term ) )
		wp_send_json(
			[ 'error' => $term->get_error_message() ],
			500,
		);

	wpr_woocommerce_record(
		$taxonomy,
		$term['term_id'],
		$state,
	);

	return (int) $term['term_id'];
}

/**
 * Seeds real commerce objects and records every changed option for cleanup.
 *
 * @since 5.2.2
 *
 * @param string $token The runner's ownership token.
 */
function wpr_woocommerce_setup( $token ) {

	if ( get_option( 'wpr_woocommerce_state' ) )
		wp_send_json(
			[ 'error' => 'A WooCommerce fixture already owns this site.' ],
			409,
		);

	$admin = get_user_by( 'login', 'admin' );

	if ( ! $admin )
		wp_send_json(
			[ 'error' => 'Playground admin user is missing.' ],
			400,
		);

	$state = [
		'token'   => $token,
		'created' => [],
		'options' => [],
		'ids'     => [],
	];

	foreach ( [
		'autodescription-site-settings',
		'blog_public',
		'category_base',
		'permalink_structure',
		'posts_per_page',
		'woocommerce_cart_page_id',
		'woocommerce_checkout_page_id',
		'woocommerce_coming_soon',
		'woocommerce_myaccount_page_id',
		'woocommerce_permalinks',
		'woocommerce_shop_page_id',
	] as $option ) {
		$value                       = get_option( $option, null );
		$state['options'][ $option ] = [
			'exists' => null !== $value,
			'value'  => $value,
		];
	}
	update_option( 'wpr_woocommerce_state', $state, false );

	foreach ( [ 'shop', 'cart', 'checkout', 'myaccount' ] as $page ) {
		if ( wc_get_page_id( $page ) > 0 ) continue;

		$id = wp_insert_post( [
			'post_type'    => 'page',
			'post_author'  => $admin->ID,
			'post_status'  => 'publish',
			'post_name'    => 'myaccount' === $page ? 'my-account' : $page,
			'post_title'   => ucfirst( $page ),
			'post_content' => 'shop' === $page ? '' : "[woocommerce_$page]",
		] );
		wpr_woocommerce_record( 'post', $id, $state );
		update_option( "woocommerce_{$page}_page_id", $id );
	}
	update_option( 'woocommerce_coming_soon', 'no' );
	update_option( 'posts_per_page', 2 );
	update_option( 'blog_public', 1 );
	The_SEO_Framework\Data\Plugin::update_option( [
		'cache_sitemap'   => 0,
		'sitemaps_output' => 1,
	] );
	update_option( 'permalink_structure', '/%postname%/' );

	$ids                  = [];
	$ids['product_cat']   = wpr_woocommerce_term(
		'wpr-woo-shirts',
		'product_cat',
		0,
		$state,
	);
	$ids['product_child'] = wpr_woocommerce_term(
		'wpr-woo-tops',
		'product_cat',
		$ids['product_cat'],
		$state,
	);
	$ids['product_tag']   = wpr_woocommerce_term(
		'wpr-woo-hot',
		'product_tag',
		0,
		$state,
	);
	$ids['category']      = wpr_woocommerce_term(
		'wpr-woo-news',
		'category',
		0,
		$state,
	);
	$ids['child']         = wpr_woocommerce_term(
		'wpr-woo-child',
		'category',
		$ids['category'],
		$state,
	);
	$ids['grandchild']    = wpr_woocommerce_term(
		'wpr-woo-grandchild',
		'category',
		$ids['child'],
		$state,
	);

	foreach ( [
		'one'   => '30',
		'two'   => '10',
		'three' => '40',
		'four'  => '20',
	] as $name => $price ) {
		$product = new WC_Product_Simple();
		$product->set_name( "WPR Woo $name" );
		$product->set_slug( "wpr-woo-$name" );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'visible' );
		$product->set_regular_price( $price );
		$product->set_description( "Published regression product $name." );
		$product->set_stock_status( 'instock' );
		$product->set_category_ids( [
			$ids['product_cat'],
			$ids['product_child'],
		] );
		$product->set_tag_ids( [ $ids['product_tag'] ] );
		$ids[ "product_$name" ] = $product->save();
		wpr_woocommerce_record(
			'post',
			$ids[ "product_$name" ],
			$state,
		);
		wp_update_post( [
			'ID'          => $ids[ "product_$name" ],
			'post_author' => $admin->ID,
		] );
		$id = wp_insert_post( [
			'post_title'    => "WPR category post $name",
			'post_author'   => $admin->ID,
			'post_name'     => "wpr-woo-post-$name",
			'post_status'   => 'publish',
			'post_content'  => 'Category regression content.',
			'post_date'     => '2020-06-15 12:00:00',
			'post_date_gmt' => '2020-06-15 12:00:00',
			'post_category' => [ $ids['category'], $ids['child'], $ids['grandchild'] ],
		] );
		$ids[ "post_$name" ] = $id;
		wpr_woocommerce_record( 'post', $id, $state );
	}

	foreach ( [ 'shop', 'cart', 'checkout', 'my-account', 'wpr-books', 'wpr-guides', 'wpr-genre' ] as $slug )
		$ids['collisions'][ $slug ] = wpr_woocommerce_term(
			$slug,
			'category',
			0,
			$state,
		);

	$state['ids'] = $ids;
	update_option( 'wpr_woocommerce_state', $state, false );
	delete_option( 'rewrite_rules' );
}

/**
 * Gets generated URLs without guessing a permalink preset's path.
 *
 * @since 5.2.2
 *
 * @param array $state Fixture state.
 * @return array
 */
function wpr_woocommerce_info( $state ) {

	$ids    = $state['ids'];
	$links  = [];
	$owners = [ 'shop' => [ 'shop' => '1' ] ];

	foreach ( [ 'shop', 'cart', 'checkout', 'myaccount' ] as $page ) {
		$links[ $page ] = get_permalink( wc_get_page_id( $page ) );

		if ( 'shop' !== $page )
			$owners[ $page ] = [ 'singular' => (string) wc_get_page_id( $page ) ];
	}

	$links['shop'] = get_post_type_archive_link( 'product' );

	foreach ( [ 'one', 'two', 'three', 'four' ] as $name ) {
		$links[ "product_$name" ]  = get_permalink( $ids[ "product_$name" ] );
		$owners[ "product_$name" ] = [ 'product' => (string) $ids[ "product_$name" ] ];
	}

	foreach ( [
		'product_cat'   => 'product_cat',
		'product_child' => 'product_cat',
		'product_tag'   => 'product_tag',
	] as $key => $taxonomy ) {
		$links[ $key ]  = get_term_link( $ids[ $key ], $taxonomy );
		$field          = 'product_tag' === $key ? 'product_tag' : 'product_category';
		$owners[ $key ] = [ $field => (string) $ids[ $key ] ];
	}

	foreach ( [
		'dune'           => 'wpr_book',
		'manual'         => 'wpr_guide',
		'manual/chapter' => 'wpr_guide',
	] as $slug => $type ) {
		$post = get_page_by_path( $slug, OBJECT, $type );

		if ( ! $post )
			wp_send_json(
				[ 'error' => "Required Playground seed missing: $type/$slug." ],
				500,
			);

		$links[ "$type/$slug" ]  = get_permalink( $post );
		$owners[ "$type/$slug" ] = [ 'singular' => (string) $post->ID ];
	}
	$links['cpt_books']   = get_post_type_archive_link( 'wpr_book' );
	$links['cpt_guides']  = get_post_type_archive_link( 'wpr_guide' );
	$owners['cpt_books']  = [ 'archive' => 'wpr_book' ];
	$owners['cpt_guides'] = [ 'archive' => 'wpr_guide' ];

	foreach ( [
		'wpr_genre'   => 'shared',
		'wpr_binding' => 'hardcover',
	] as $taxonomy => $slug ) {
		$term = get_term_by( 'slug', $slug, $taxonomy );

		if ( ! $term )
			wp_send_json(
				[ 'error' => "Required Playground seed missing: $taxonomy/$slug." ],
				500,
			);

		$links[ $taxonomy ]  = get_term_link( $term );
		$owners[ $taxonomy ] = [ 'taxonomy' => (string) $term->term_id ];
	}
	$links['post']  = get_permalink( $ids['post_one'] );
	$owners['post'] = [ 'singular' => (string) $ids['post_one'] ];
	$categories     = [
		'category'   => get_term_link( $ids['category'], 'category' ),
		'child'      => get_term_link( $ids['child'], 'category' ),
		'grandchild' => get_term_link( $ids['grandchild'], 'category' ),
	];

	foreach ( $ids['collisions'] as $slug => $id )
		$categories[ $slug ] = get_term_link( $id, 'category' );

	return [
		'wc_version'            => WC()->version,
		'wp_version'            => get_bloginfo( 'version' ),
		'php_version'           => PHP_VERSION,
		'tsf_version'           => THE_SEO_FRAMEWORK_VERSION,
		'sitemap'               => The_SEO_Framework\Sitemap\Registry::get_expected_sitemap_endpoint_url(),
		'shop_canonical'        => get_permalink( wc_get_page_id( 'shop' ) ),
		'taxonomy_base_feature' => array_key_exists(
			'remove_taxonomy_bases',
			The_SEO_Framework\Data\Plugin\Setup::get_default_options(),
		),
		'ids'                   => $ids,
		'links'                 => $links,
		'owners'                => $owners,
		'categories'            => $categories,
		'product_category_base' => trim( get_taxonomy( 'product_cat' )->rewrite['slug'], '/' ),
	];
}

/**
 * Creates categories whose shortened paths exercise native singular and taxonomy rewrites.
 *
 * @since 5.2.2
 *
 * @param array $state Fixture state.
 * @return array Category IDs and their currently generated links, keyed by case.
 */
function wpr_woocommerce_conflict_terms( $state ) {

	$ids     = $state['ids'];
	$product = get_permalink( $ids['product_one'] );
	$tag     = get_term_link( $ids['product_tag'], 'product_tag' );
	$terms   = [ 'child' => $ids['child'] ];
	$urls    = [
		'post'           => get_permalink( $ids['post_one'] ),
		'product'        => $product,
		'absent_product' => dirname( untrailingslashit( $product ) ) . '/wpr-woo-vacant/',
		'feed'           => get_term_feed_link( $ids['product_tag'], 'product_tag' ),
		'paged'          => trailingslashit( $tag ) . 'page/2/',
	];

	foreach ( $urls as $case => $url ) {
		$parent = 0;
		$path   = trim( wp_parse_url( $url, PHP_URL_PATH ), '/' );

		foreach ( explode( '/', $path ) as $slug )
			$parent = wpr_woocommerce_term( $slug, 'category', $parent, $state );

		$terms[ $case ] = $parent;
	}
	// A published post makes each category eligible for a canonical URL.
	wp_set_post_categories(
		$ids['post_four'],
		array_values( $terms ),
		true,
	);

	foreach ( $terms as $case => $id )
		$terms[ $case ] = [
			'id'  => $id,
			'url' => get_term_link( $id, 'category' ),
		];

	return $terms;
}

/**
 * Normalizes associative array key order before comparing restored options.
 *
 * @since 5.2.2
 *
 * @param mixed $value The option value to normalize.
 * @return mixed The normalized value.
 */
function wpr_woocommerce_normalize_option( $value ) {

	if ( ! is_array( $value ) || ! $value )
		return $value;

	// Sort associative arrays so sanitizer key reordering does not fail restoration checks.
	if ( array_keys( $value ) !== range( 0, count( $value ) - 1 ) )
		ksort( $value );

	foreach ( $value as $key => $item )
		$value[ $key ] = wpr_woocommerce_normalize_option( $item );

	return $value;
}

/**
 * Restores changed settings and deletes only objects created by this fixture.
 *
 * @since 5.2.2
 *
 * @param array $state Fixture state.
 */
function wpr_woocommerce_cleanup( $state ) {

	$errors = [];

	foreach ( array_reverse( $state['created'] ) as [ $type, $id ] ) {
		if ( 'post' === $type ) {
			wp_delete_post( $id, true );

			if ( get_post( $id ) )
				$errors[] = "post $id remains";
		} else {
			wp_delete_term( $id, $type );

			if ( term_exists( $id, $type ) )
				$errors[] = "$type $id remains";
		}
	}

	foreach ( $state['options'] as $option => $saved ) {
		if ( $saved['exists'] ) {
			update_option( $option, $saved['value'] );
		} else {
			delete_option( $option );
		}

		$value = get_option( $option, null );

		// WordPress normalizes non-serialized option values to strings after saving.
		if (
			   ( null === $value ) !== ! $saved['exists']
			|| (string) maybe_serialize( wpr_woocommerce_normalize_option( $value ) )
				!== (string) maybe_serialize( wpr_woocommerce_normalize_option( $saved['value'] ) )
		) {
			$errors[] = "$option was not restored";
		}
	}
	delete_option( 'rewrite_rules' );

	if ( $errors )
		wp_send_json(
			[ 'error' => implode( '; ', $errors ) ],
			500,
		);

	delete_option( 'wpr_woocommerce_state' );
}

/**
 * Handles authenticated fixture requests after all rewrite owners register.
 *
 * @since 5.2.2
 */
function wpr_woocommerce_control() {

	if ( empty( $_GET['wpr-woocommerce'] ) ) return;

	$key  = __DIR__ . '/wp-plugin-regression.key';
	$sent = $_SERVER['HTTP_X_WPR_KEY'] ?? '';

	if (
		   ! is_file( $key )
		|| ! is_string( $sent )
		|| ! hash_equals(
			trim( file_get_contents( $key ) ),
			$sent,
		)
	) {
		wp_send_json(
			[ 'error' => 'Invalid Playground harness key.' ],
			403,
		);
	}

	if (
		   ! function_exists( 'WC' )
		|| ! class_exists( 'The_SEO_Framework\Data\Plugin' )
	) {
		wp_send_json(
			[ 'error' => 'WooCommerce and TSF must be active.' ],
			400,
		);
	}

	$body = json_decode(
		file_get_contents( 'php://input' ),
		true,
	);

	if ( ! is_array( $body ) )
		wp_send_json(
			[ 'error' => 'JSON object required.' ],
			400,
		);

	$token = $body['token'] ?? '';

	if ( ! is_string( $token ) || ! $token )
		wp_send_json(
			[ 'error' => 'Fixture ownership token required.' ],
			400,
		);

	$action = $body['action'] ?? '';

	if ( 'setup' === $action ) {
		wpr_woocommerce_setup( $token );
		wp_send_json( [ 'setup' => true ] );
	}

	$state = get_option( 'wpr_woocommerce_state' );

	if ( ! $state || ! hash_equals( $state['token'], $token ) )
		wp_send_json(
			[ 'error' => 'Fixture belongs to another run.' ],
			409,
		);

	if ( 'cleanup' === $action ) {
		wpr_woocommerce_cleanup( $state );
		wp_send_json( [ 'cleaned' => true ] );
	}

	if ( 'conflicts' === $action )
		wp_send_json( wpr_woocommerce_conflict_terms( $state ) );

	if ( 'config' === $action ) {
		update_option(
			'permalink_structure',
			$body['permalink'],
		);
		update_option( 'category_base', '' );
		update_option(
			'woocommerce_permalinks',
			$body['woocommerce'],
		);

		if ( array_key_exists(
			'remove_taxonomy_bases',
			The_SEO_Framework\Data\Plugin\Setup::get_default_options(),
		) ) {
			The_SEO_Framework\Data\Plugin::update_option(
				'remove_taxonomy_bases',
				array_fill_keys(
					$body['taxonomies'] ?? [ 'category' ],
					(int) $body['enabled'],
				),
			);
		}
		delete_option( 'rewrite_rules' );
		wp_send_json( [ 'configured' => true ] );
	}

	wp_send_json( wpr_woocommerce_info( $state ) );
}
add_action(
	'wp_loaded',
	'wpr_woocommerce_control',
	999,
	0,
);
