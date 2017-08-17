<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\qTranslateX
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) and $_this = \the_seo_framework_class() and $this instanceof $_this or die;

\add_filter( 'the_seo_framework_url_path', __NAMESPACE__ . '\\_qtranslatex_filter_url_path', 10, 3 );
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
function _qtranslatex_filter_url_path( $path = '', $id = 0, $external = false ) {
	return \The_SEO_Framework\_qtranslate_get_relative_url( $path, $id );
}

/**
 * Generates qtranslate URL from path.
 *
 * @since 2.6.0
 * @since 2.8.0 Moved to compat file and renamed.
 * @staticvar int $q_config_mode
 * @global array $q_config
 * @NOTE: Handles full path, including home directory.
 * @access private
 *
 * @param string $path The current path.
 * @param int $post_id The Post ID. Unused until qTranslate provides external URL forgery.
 */
function _qtranslate_get_relative_url( $path = '', $post_id = '' ) {

	//* Reset cache.
	\the_seo_framework()->url_slashit = true;
	\the_seo_framework()->unset_current_subdomain();

	static $q_config_mode = null;

	if ( ! isset( $q_config ) ) {
		global $q_config;
		$q_config_mode = $q_config['url_mode'];
	}

	//* If false, change canonical URL for every page.
	$hide = isset( $q_config['hide_default_language'] ) ? $q_config['hide_default_language'] : true;

	$current_lang = isset( $q_config['language'] ) ? $q_config['language'] : false;
	$default_lang = isset( $q_config['default_language'] ) ? $q_config['default_language'] : false;

	//* Don't to anything on default language when path is hidden.
	if ( $hide && $current_lang === $default_lang )
		return $path;

	switch ( $q_config_mode ) :
		case '1' :
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

		case '2' :
			//* Subdirectory
			if ( 0 === strpos( trailingslashit( $path ), '/' . $current_lang . '/' ) ) {
				return $path;
			} else {
				return $path = \trailingslashit( $current_lang ) . ltrim( $path, ' \\/' );
			}
			break;

		case '3' :
			//* Notify cache of subdomain addition.
			\the_seo_framework()->set_current_subdomain( $current_lang );

			//* No need to alter the path.
			return $path;
			break;

		default :
			break;
	endswitch;

	return $path;
}
