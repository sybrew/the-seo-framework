<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\WPML
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) and $_this = \the_seo_framework_class() and $this instanceof $_this or die;

\add_filter( 'the_seo_framework_url_path', __NAMESPACE__ . '\\_wpml_filter_url_path', 10, 3 );
/**
 * Filters the canonical URL path.
 *
 * @since 2.8.0
 * @access private
 *
 * @param string $path the URL path.
 * @param int $id The current post, page or term ID.
 * @param bool $external Whether the call is made from outside the current ID scope. Like from the Sitemap.
 * @return string The URL path.
 */
function _wpml_filter_url_path( $path = '', $id = 0, $external = false ) {
	return \The_SEO_Framework\_get_relative_wmpl_url( $path, $id );
}

/**
 * Generate relative WPML url.
 *
 * @since 2.4.3
 * @staticvar bool $gli_exists
 * @staticvar string $default_lang
 * @global object $sitepress
 * @NOTE: Handles full path, including home directory.
 * @access private
 *
 * @param string $path The current path.
 * @param int $post_id The Post ID.
 * @return relative path for WPML urls.
 */
function _get_relative_wmpl_url( $path = '', $post_id = '' ) {
	global $sitepress;

	//* Reset cache.
	\the_seo_framework()->url_slashit = true;
	\the_seo_framework()->unset_current_subdomain();

	if ( ! isset( $sitepress ) )
		return $path;

	static $gli_exists = null;
	if ( is_null( $gli_exists ) )
		$gli_exists = function_exists( 'wpml_get_language_information' );

	if ( false === $gli_exists )
		return $path;

	if ( empty( $post_id ) )
		$post_id = \the_seo_framework()->get_the_real_ID();

	//* Cache default language.
	static $default_lang = null;
	if ( is_null( $default_lang ) )
		$default_lang = $sitepress->get_default_language();

	/**
	 * Applies filters wpml_post_language_details : array|wp_error
	 *
	 * ... Somehow WPML thought this would be great and understandable.
	 * This should be put inside a callable function.
	 * @since 2.6.0
	 */
	$lang_info = \apply_filters( 'wpml_post_language_details', null, $post_id );

	if ( \is_wp_error( $lang_info ) ) {
		//* Terms and Taxonomies.
		$lang_info = array();

		//* Cache the code.
		static $lang_code = null;
		if ( is_null( $lang_code ) && defined( 'ICL_LANGUAGE_CODE' ) )
			$lang_code = ICL_LANGUAGE_CODE;

		$lang_info['language_code'] = $lang_code;
	}

	//* If filter isn't used, bail.
	if ( false === isset( $lang_info['language_code'] ) )
		return $path;

	$current_lang = $lang_info['language_code'];

	//* No need to alter URL if we're on default lang.
	if ( $current_lang === $default_lang )
		return $path;

	//* Cache negotiation type.
	static $negotiation_type = null;
	if ( is_null( $negotiation_type ) )
		$negotiation_type = $sitepress->get_setting( 'language_negotiation_type' );

	switch ( $negotiation_type ) :
		case '1' :
			//* Subdirectory

			$contains_path = strpos( \trailingslashit( $path ), '/' . $current_lang . '/' );
			if ( false !== $contains_path && 0 === $contains_path ) {
				return $path;
			} else {
				return $path = \trailingslashit( $current_lang ) . ltrim( $path, ' \\/' );
			}
			break;

		case '2' :
			//* Custom domain.

			$langsettings = $sitepress->get_setting( 'language_domains' );
			$current_lang_setting = isset( $langsettings[ $current_lang ] ) ? $langsettings[ $current_lang ] : '';

			if ( empty( $current_lang_setting ) )
				return $path;

			$current_lang_setting = \the_seo_framework()->make_fully_qualified_url( $current_lang_setting );
			$parsed = \wp_parse_url( $current_lang_setting );

			\the_seo_framework()->current_host = isset( $parsed['host'] ) ? $parsed['host'] : '';
			$current_path = isset( $parsed['path'] ) ? \trailingslashit( $parsed['path'] ) : '';

			return $current_path . $path;
			break;

		case '3' :
			//* Negotiation type query var.

			//* Don't slash it further.
			\the_seo_framework()->url_slashit = false;

			/**
			 * Path must have trailing slash for pagination permalinks to work.
			 * So we remove the query string and add it back with slash.
			 */
			if ( false !== strpos( $path, '?lang=' . $current_lang ) )
				$path = str_replace( '?lang=' . $current_lang, '', $path );

			return \user_trailingslashit( $path ) . '?lang=' . $current_lang;
			break;

		default :
			break;
	endswitch;

	return $path;
}
