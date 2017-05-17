<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\WPMUDev-dm
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) and $_this = \the_seo_framework_class() and $this instanceof $_this or die;

\add_filter( 'the_seo_framework_sanitize_redirect_args', __NAMESPACE__ . '\\_wpmudev_sanitize_redirect_url', 10, 2 );
\add_filter( 'the_seo_framework_url_output_args', __NAMESPACE__ . '\\_wpmudev_sanitize_redirect_url', 10, 2 );
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
function _wpmudev_sanitize_redirect_url( $args = array(), $path = '' ) {

	$args = \The_SEO_Framework\_wpmudev_domainmap_get_url( $path, true );

	return array(
		'url' => $args[0],
		'scheme' => $args[1],
	);
}

/**
 * Creates a full canonical URL when WPMUdev Domain Mapping is active from path.
 *
 * @since 2.3.0
 * @since 2.4.0 Added $get_scheme parameter.
 * @since 2.8.0 Moved to compat file.
 * @since 2.9.2 Now also returns an array if $get_scheme is true when no output is found.
 * @global object $wpdb
 * @global int $blog_id
 * @access private
 *
 * @param string $path The post relative path.
 * @param bool $get_scheme Output array with scheme.
 * @return string|array The unescaped URL, or the URL and the Scheme.
 */
function _wpmudev_domainmap_get_url( $path, $get_scheme = false ) {
	global $wpdb, $blog_id;

	/**
	 * Cache revisions. Hexadecimal.
	 * @since 2.6.0
	 */
	$revision = '1';

	$cache_key = 'wpmudev_mapped_domain_' . $revision . '_' . $blog_id;

	//* Check if the domain is mapped. Store in object cache.
	$mapped_domain = \the_seo_framework()->object_cache_get( $cache_key );
	if ( false === $mapped_domain ) :
		$mapped_domains = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, domain, is_primary, scheme FROM {$wpdb->base_prefix}domain_mapping WHERE blog_id = %d", $blog_id
			), OBJECT
		);

		$primary_key = 0;
		$domain_ids = array();

		foreach ( $mapped_domains as $key => $domain ) :
			if ( isset( $domain->is_primary ) && '1' === $domain->is_primary ) {
				$primary_key = $key;

				//* We've found the primary key, break loop.
				break;
			} else {
				//* Save IDs.
				if ( isset( $domain->id ) && $domain->id )
					$domain_ids[ $key ] = $domain->id;
			}
		endforeach;

		if ( 0 === $primary_key && ! empty( $domain_ids ) ) {
			//* No primary ID has been found. Get the one with the lowest ID, which has been added first.
			$primary_key = array_keys( $domain_ids, min( $domain_ids ), true );
			$primary_key = reset( $primary_key );
		}

		//* Set 0, as we check for false to begin with.
		$mapped_domain = isset( $mapped_domains[ $primary_key ] ) ? $mapped_domains[ $primary_key ] : 0;

		\the_seo_framework()->object_cache_set( $cache_key, $mapped_domain, 3600 );
	endif;

	if ( $mapped_domain ) :

		$domain = isset( $mapped_domain->domain ) ? $mapped_domain->domain : '0';
		$scheme = isset( $mapped_domain->scheme ) ? $mapped_domain->scheme : '';

		//* Fallback to is_ssl if no scheme has been found.
		if ( '' === $scheme )
			$scheme = \the_seo_framework()->is_ssl() ? '1' : '0';

		if ( '1' === $scheme ) {
			//* HTTPS
			$scheme_full = 'https://';
			$scheme = 'https';
		} elseif ( '2' === $scheme ) {
			//* HTTP/HTTPS, use preferred scheme and build URL expected (https).
			// This will be converted back anyway later if preferred is http.
			$scheme_full = 'https://';
			$scheme = '';
		} else {
			//* HTTP
			$scheme_full = 'http://';
			$scheme = 'http';
		}

		//* Put it all together.
		$url = \trailingslashit( $scheme_full . $domain ) . ltrim( $path, ' \\/' );

		if ( $get_scheme ) {
			return array( $url, $scheme );
		} else {
			return $url;
		}
	endif;

	if ( $get_scheme ) {
		return array( '', '' );
	} else {
		return '';
	}
}
