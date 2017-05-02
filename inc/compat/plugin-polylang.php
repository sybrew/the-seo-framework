<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\WPML
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) and $_this = \the_seo_framework_class() and $this instanceof $_this or die;

\add_filter( 'the_seo_framework_url_output_args', __NAMESPACE__ . '\\_polylang_filter_url_args', 10, 4 );
/**
 * Filters the canonical generation URL and Scheme arguments.
 *
 * @since 2.9.2
 * @access private
 *
 * @param array $args the URL arguments to supply. : {
 *                'url' => The full URL built from $path,
 *                'scheme' => The preferred scheme
 *              }
 * @param string $path the URL path.
 * @param int $id The current post, page or term ID.
 * @param bool $external Whether the call is made from outside the current ID scope. Like from the Sitemap.
 * @return string The URL path.
 */
function _polylang_filter_url_args( $args = array(), $path = '', $id = 0, $external = false ) {
	global $polylang;

	if ( ! is_object( $polylang ) || ! ( isset( $polylang->filters_links ) && is_object( $polylang->filters_links ) ) )
		return $args;

	if ( \the_seo_framework()->is_sitemap() )
		return \The_SEO_Framework\_polylang_sitemap_filter_url_args( $args, $path, $id, $external );

	if ( \the_seo_framework()->is_admin() )
		return \The_SEO_Framework\_polylang_admin_filter_url_args( $args, $path, $id, $external );

	return \The_SEO_Framework\_polylang_frontend_filter_url_args( $args, $path, $id, $external );
}

/**
 * Filters the canonical generation URL and Scheme arguments for the admin side.
 *
 * @since 2.9.2
 * @access private
 *
 * @param array $args the URL arguments to supply. : {
 *                'url' => The full URL built from $path,
 *                'scheme' => The preferred scheme
 *              }
 * @param string $path the URL path.
 * @param int $id The current post, page or term ID.
 * @param bool $external Whether the call is made from outside the current ID scope. Like from the Sitemap.
 * @return string The URL path.
 */
function _polylang_admin_filter_url_args( $args = array(), $path = '', $id = 0, $external = false ) {
	global $polylang;

	//* We don't want to fire internal filters that lead to this function.
	$home_url = \untrailingslashit( \esc_url_raw( \the_seo_framework()->set_preferred_url_scheme( \the_seo_framework()->get_home_host() ) ) );

	if ( \the_seo_framework()->is_real_front_page() || \the_seo_framework()->is_front_page_by_id( $id ) || \the_seo_framework()->is_404() ) {
		if ( isset( $polylang->links ) && method_exists( $polylang->links, 'get_home_url' ) ) {
			if ( isset( $polylang->curlang ) && is_object( $polylang->curlang ) )
				$args['url'] = $polylang->links->get_home_url( $polylang->curlang, false );
		} else {
			$args['url'] = $home_url;
		}
	} elseif ( $path ) {
		$_post = \get_post( $id );
		$_link = $home_url . $path;

		if ( method_exists( $polylang->filters_links, 'post_type_link' ) ) {
			$args['url'] = $polylang->filters_links->post_type_link( $_link, $_post );
		}
	}

	if ( isset( $args['url'] ) ) {
		$parsed_url = \wp_parse_url( $args['url'] );
		$args['scheme'] = isset( $parsed_url['scheme'] ) ? $parsed_url['scheme'] : '';
	}

	return $args;
}

/**
 * Filters the canonical generation URL and Scheme arguments for the frontend.
 *
 * @since 2.9.2
 * @access private
 *
 * @param array $args the URL arguments to supply. : {
 *                'url' => The full URL built from $path,
 *                'scheme' => The preferred scheme
 *              }
 * @param string $path the URL path.
 * @param int $id The current post, page or term ID.
 * @param bool $external Whether the call is made from outside the current ID scope. Like from the Sitemap.
 * @return string The URL path.
 */
function _polylang_frontend_filter_url_args( $args = array(), $path = '', $id = 0, $external = false ) {
	global $polylang;

	//* We don't want to fire internal filters that lead to this function.
	$home_url = \untrailingslashit( \esc_url_raw( \the_seo_framework()->set_preferred_url_scheme( \the_seo_framework()->get_home_host() ) ) );

	if ( \the_seo_framework()->is_real_front_page() || \the_seo_framework()->is_404() || \the_seo_framework()->is_search() ) {
		if ( isset( $polylang->links ) && method_exists( $polylang->links, 'get_home_url' ) ) {
			$args['url'] = $polylang->links->get_home_url( '' );
		} else {
			$args['url'] = $home_url;
		}
	} elseif ( $path ) {
		if ( method_exists( $polylang->filters_links, 'check_canonical_url' ) )
			$args['url'] = $polylang->filters_links->check_canonical_url( $home_url . $path, false );
	}

	if ( isset( $args['url'] ) ) {
		$parsed_url = \wp_parse_url( $args['url'] );
		$args['scheme'] = isset( $parsed_url['scheme'] ) ? $parsed_url['scheme'] : '';
	}

	return $args;
}

/**
 * Filters the canonical generation URL and Scheme arguments for the sitemap.
 *
 * @since 2.9.2
 * @access private
 *
 * @param array $args the URL arguments to supply. : {
 *                'url' => The full URL built from $path,
 *                'scheme' => The preferred scheme
 *              }
 * @param string $path the URL path.
 * @param int $id The current post, page or term ID.
 * @param bool $external Whether the call is made from outside the current ID scope. Like from the Sitemap.
 * @return string The URL path.
 */
function _polylang_sitemap_filter_url_args( $args = array(), $path = '', $id = 0, $external = false ) {
	global $polylang;

	static $home_url = null;
	//* We don't want to fire internal filters that lead to this function.
	if ( null === $home_url )
		$home_url = \untrailingslashit( \esc_url_raw( \the_seo_framework()->set_preferred_url_scheme( \the_seo_framework()->get_home_host() ) ) );

	$_post = \get_post( $id );
	$_link = $home_url . $path;

	if ( null === $_post ) {
		if ( method_exists( $polylang->filters_links, 'post_type_link' ) )
			$args['url'] = $polylang->links->get_home_url( '' );
	} elseif ( method_exists( $polylang->filters_links, 'post_type_link' ) ) {
		$args['url'] = $polylang->filters_links->post_type_link( $_link, $_post );
	}

	if ( isset( $args['url'] ) ) {
		$parsed_url = \wp_parse_url( $args['url'] );
		$args['scheme'] = isset( $parsed_url['scheme'] ) ? $parsed_url['scheme'] : '';
	}

	return $args;
}
