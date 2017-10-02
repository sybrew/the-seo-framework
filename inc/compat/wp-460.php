<?php

defined( 'ABSPATH' ) or die;

if ( ! function_exists( 'wp_get_canonical_url' ) ) :
	/**
	 * Returns the canonical URL for a post.
	 *
	 * When the post is the same as the current requested page the function will handle the
	 * pagination arguments too.
	 *
	 * @since 4.6.0
	 *
	 * @param int|WP_Post $post Optional. Post ID or object. Default is global `$post`.
	 * @return string|false The canonical URL, or false if the post does not exist or has not
	 *                      been published yet.
	 */
	function wp_get_canonical_url( $post = null ) {
		$post = get_post( $post );

		if ( ! $post ) {
			return false;
		}

		if ( 'publish' !== $post->post_status ) {
			return false;
		}

		$canonical_url = get_permalink( $post );

		// If a canonical is being generated for the current page, make sure it has pagination if needed.
		if ( $post->ID === get_queried_object_id() ) {
			$page = get_query_var( 'page', 0 );
			if ( $page >= 2 ) {
				if ( '' == get_option( 'permalink_structure' ) ) {
					$canonical_url = add_query_arg( 'page', $page, $canonical_url );
				} else {
					$canonical_url = trailingslashit( $canonical_url ) . user_trailingslashit( $page, 'single_paged' );
				}
			}

			$cpage = get_query_var( 'cpage', 0 );
			if ( $cpage ) {
				$canonical_url = get_comments_pagenum_link( $cpage );
			}
		}

		/**
		 * Filters the canonical URL for a post.
		 *
		 * @since 4.6.0
		 *
		 * @param string  $canonical_url The post's canonical URL.
		 * @param WP_Post $post          Post object.
		 */
		return apply_filters( 'get_canonical_url', $canonical_url, $post );
	}
endif;
