<?php
/**
 * @package The_SEO_Framework\Playground
 */

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2026 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

add_action( 'init', 'wpr_tsf_register_types', 0 );
add_action( 'init', 'wpr_tsf_seed_content', 20 );
add_filter(
	'wp_plugin_regression_frame',
	'wpr_tsf_apply_frame',
	10,
	2,
);

/**
 * Registers baseline CPTs and taxonomies.
 *
 * @since 5.1.5
 */
function wpr_tsf_register_types() {

	register_post_type(
		'wpr_book',
		[
			'label'              => 'WPR Books',
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => false,
			'hierarchical'       => false,
			'has_archive'        => 'wpr-books',
			'rewrite'            => [ 'slug' => 'wpr-book' ],
		],
	);

	register_post_type(
		'wpr_guide',
		[
			'label'              => 'WPR Guides',
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => false,
			'hierarchical'       => true,
			'has_archive'        => 'wpr-guides',
			'rewrite'            => [ 'slug' => 'wpr-guide' ],
		],
	);

	register_taxonomy(
		'wpr_genre',
		[ 'wpr_book', 'wpr_guide' ],
		[
			'label'              => 'WPR Genre',
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => false,
			'hierarchical'       => false,
			'rewrite'            => [ 'slug' => 'wpr-genre' ],
		],
	);

	register_taxonomy(
		'wpr_binding',
		[ 'wpr_book' ],
		[
			'label'              => 'WPR Binding',
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => false,
			'hierarchical'       => false,
			'rewrite'            => [ 'slug' => 'wpr-binding' ],
		],
	);
}

/**
 * Creates baseline posts, pages, and terms once.
 *
 * @since 5.1.5
 */
function wpr_tsf_seed_content() {

	if ( ! function_exists( 'is_blog_installed' ) || ! is_blog_installed() )
		return;

	if ( ! defined( 'THE_SEO_FRAMEWORK_PRESENT' ) ) return;

	if ( get_option( 'wpr_tsf_baseline_ready' ) ) return;

	if ( ! add_option( 'wpr_tsf_baseline_lock', 1 ) ) return;

	update_option( 'posts_per_page', 2 );

	if ( ! get_page_by_path( 'home' ) ) {
		wp_insert_post(
			[
				'post_title'   => 'Home',
				'post_name'    => 'home',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => 'Baseline front page.',
			],
		);
	}

	if ( ! get_page_by_path( 'blog' ) ) {
		wp_insert_post(
			[
				'post_title'   => 'Blog',
				'post_name'    => 'blog',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => 'Baseline posts page.',
			],
		);
	}

	wp_insert_post(
		[
			'post_title'    => 'Dated',
			'post_name'     => 'dated',
			'post_status'   => 'publish',
			'post_type'     => 'post',
			'post_content'  => 'Dated post.',
			'post_date'     => '2020-06-15 12:00:00',
			'post_date_gmt' => '2020-06-15 12:00:00',
		],
	);

	foreach ( [ 'extra-1', 'extra-2', 'extra-3' ] as $slug ) {
		wp_insert_post(
			[
				'post_title'   => $slug,
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_type'    => 'post',
				'post_content' => 'Extra post.',
			],
		);
	}

	$book = wp_insert_post(
		[
			'post_title'   => 'Dune',
			'post_name'    => 'dune',
			'post_status'  => 'publish',
			'post_type'    => 'wpr_book',
			'post_content' => 'A book.',
		],
	);

	$manual = wp_insert_post(
		[
			'post_title'   => 'Manual',
			'post_name'    => 'manual',
			'post_status'  => 'publish',
			'post_type'    => 'wpr_guide',
			'post_content' => 'A guide.',
		],
	);

	wp_insert_post(
		[
			'post_title'   => 'Chapter',
			'post_name'    => 'chapter',
			'post_status'  => 'publish',
			'post_type'    => 'wpr_guide',
			'post_parent'  => $manual,
			'post_content' => 'A chapter.',
		],
	);

	$redirect = wp_insert_post(
		[
			'post_title'   => 'Redirect post',
			'post_name'    => 'wpr-redirect-post',
			'post_status'  => 'publish',
			'post_type'    => 'post',
			'post_content' => 'Redirects.',
		],
	);

	$hello = get_page_by_path( 'hello-world', OBJECT, 'post' );
	$tag   = wp_insert_term( 'WPR Tag', 'post_tag', [ 'slug' => 'wpr-tag' ] );
	$genre = wp_insert_term( 'Shared', 'wpr_genre', [ 'slug' => 'shared' ] );
	$bind  = wp_insert_term( 'Hardcover', 'wpr_binding', [ 'slug' => 'hardcover' ] );
	$rcat  = wp_insert_term(
		'Redirect cat',
		'category',
		[ 'slug' => 'wpr-redirect-cat' ],
	);

	wp_insert_term( 'Empty', 'category', [ 'slug' => 'wpr-empty' ] );

	wp_insert_post(
		[
			'post_title'   => 'Multipage',
			'post_name'    => 'wpr-multipage',
			'post_status'  => 'publish',
			'post_type'    => 'post',
			'post_content' => "Page one.\n\n<!--nextpage-->\n\nPage two.",
		],
	);

	if ( $hello && ! is_wp_error( $tag ) )
		wp_set_object_terms( $hello->ID, [ (int) $tag['term_id'] ], 'post_tag' );

	if ( $book && ! is_wp_error( $genre ) )
		wp_set_object_terms( $book, [ (int) $genre['term_id'] ], 'wpr_genre' );

	if ( $manual && ! is_wp_error( $genre ) )
		wp_set_object_terms( $manual, [ (int) $genre['term_id'] ], 'wpr_genre' );

	if ( $book && ! is_wp_error( $bind ) )
		wp_set_object_terms( $book, [ (int) $bind['term_id'] ], 'wpr_binding' );

	if ( defined( 'THE_SEO_FRAMEWORK_PRESENT' ) ) {
		$to = home_url( '/sample-page/' );

		if ( $redirect )
			The_SEO_Framework\Data\Plugin\Post::update_single_meta_item(
				'redirect',
				$to,
				$redirect,
			);

		if ( ! is_wp_error( $rcat ) )
			The_SEO_Framework\Data\Plugin\Term::update_single_meta_item(
				'redirect',
				$to,
				(int) $rcat['term_id'],
			);
	}

	delete_option( 'rewrite_rules' );
	$GLOBALS['wp_rewrite']->flush_rules( false );
	update_option( 'wpr_tsf_baseline_ready', 1 );
}

/**
 * Switches blog-on-front vs static front + posts page.
 *
 * @since 5.1.5
 *
 * @param bool   $handled Whether a consumer already applied the frame.
 * @param string $name    Frame name.
 * @return bool
 */
function wpr_tsf_apply_frame( $handled, $name ) {

	if ( $handled )
		return $handled;

	if ( ! function_exists( 'is_blog_installed' ) || ! is_blog_installed() )
		return $handled;

	if ( 'static' === $name ) {
		$home = get_page_by_path( 'home' );
		$blog = get_page_by_path( 'blog' );

		if ( ! $home || ! $blog )
			return $handled;

		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home->ID );
		update_option( 'page_for_posts', $blog->ID );

		return true;
	}

	if ( 'blog' === $name ) {
		update_option( 'show_on_front', 'posts' );

		return true;
	}

	return $handled;
}
