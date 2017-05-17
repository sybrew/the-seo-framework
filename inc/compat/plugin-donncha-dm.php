<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\Donncha-dm
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) and $_this = \the_seo_framework_class() and $this instanceof $_this or die;

\add_filter( 'the_seo_framework_sanitize_redirect_args', __NAMESPACE__ . '\\_donncha_domainmap_sanitize_redirect_url', 10, 2 );
\add_filter( 'the_seo_framework_url_output_args', __NAMESPACE__ . '\\_donncha_domainmap_sanitize_redirect_url', 10, 2 );
/**
 * Filters the sanitation URL.
 *
 * @since 2.8.0
 * @access private
 *
 * @param array : { 'url' => The full URL built from $path, 'scheme' => The preferred scheme }
 * @param string $path the URL path.
 * @param int $id The current post, page or term ID.
 * @param bool $external Whether the call is made from outside the current ID scope. Like from the Sitemap.
 */
function _donncha_domainmap_sanitize_redirect_url( $args = array(), $path = '' ) {

	$args = \The_SEO_Framework\_donncha_domainmap_get_url( $path, true );

	return array(
		'url' => $args[0],
		'scheme' => $args[1],
	);
}

/**
 * Try to get an canonical URL when Donncha Domain Mapping is active.
 *
 * @since 2.4.0
 * @global object $current_blog
 * @access private
 *
 * @param string $path The post relative path.
 * @param bool $get_scheme Output array with scheme.
 * @return string|array The unescaped URL, or the URL and the Scheme.
 */
function _donncha_domainmap_get_url( $path, $get_scheme = false ) {
	global $current_blog;

	$scheme = \the_seo_framework()->is_ssl() ? 'https' : 'http';
	$url = function_exists( 'domain_mapping_siteurl' ) ? \domain_mapping_siteurl( false ) : false;

	$request_uri = '';

	if ( $url && \untrailingslashit( $scheme . '://' . $current_blog->domain . $current_blog->path ) !== $url ) {
		if ( ( defined( 'VHOST' ) && 'yes' !== VHOST ) || ( defined( 'SUBDOMAIN_INSTALL' ) && false === SUBDOMAIN_INSTALL ) )
			$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? str_replace( $current_blog->path, '/', $_SERVER['REQUEST_URI'] ) : '';

		$url = \trailingslashit( $url . $request_uri ) . ltrim( $path, ' \\/' );

		if ( $get_scheme ) {
			return array( $url, $scheme );
		} else {
			return $url;
		}
	}

	if ( $get_scheme ) {
		return array( '', '' );
	} else {
		return '';
	}
}
