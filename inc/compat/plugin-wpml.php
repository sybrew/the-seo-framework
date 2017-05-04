<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\WPML
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) and $_this = \the_seo_framework_class() and $this instanceof $_this or die;

/**
 * Warns homepage global title and description about receiving input.
 *
 * @since 1.0.0
 */
\add_filter( 'the_seo_framework_warn_homepage_global_title', '__return_true' );
\add_filter( 'the_seo_framework_warn_homepage_global_description', '__return_true' );

\add_filter( 'the_seo_framework_url_path', __NAMESPACE__ . '\\_wpml_filter_url_path', 10, 3 );
/**
 * Filters the canonical URL path.
 *
 * @since 2.8.0
 * @since 2.9.2 : Now passes $external paramerer
 * @access private
 *
 * @param string $path the URL path.
 * @param int $id The current post, page or term ID.
 * @param bool $external Whether the call is made from outside the current ID scope. Like from the Sitemap.
 * @return string The URL path.
 */
function _wpml_filter_url_path( $path = '', $id = 0, $external = false ) {
	return \The_SEO_Framework\_wmpl_get_relative_url( $path, $id, $external );
}

/**
 * Generate relative WPML url.
 *
 * @since 2.4.3
 * @since 2.9.2 : 1. Added $is_external staticvar.
 *              : 2. Added $current_language staticvar.
 *              : 3. Cached $current_language through WPML determination, improving performance.
 *              : 4. Can now receive $external parameter.
 *
 * @staticvar bool $is_external
 * @staticvar bool $gli_exists
 * @staticvar string $default_lang
 * @staticvar string $current_language
 * @staticvar string $lang_code
 * @global object $sitepress
 * @NOTE: Handles full path, including home directory.
 * @access private
 *
 * @param string $path The current path.
 * @param int $post_id The Post ID.
 * @param bool $external Whether the call is made from outside the current ID scope.
 * @return relative path for WPML urls.
 */
function _wmpl_get_relative_url( $path = '', $post_id = '', $external = false ) {
	global $sitepress;

	if ( ! is_object( $sitepress ) )
		return $path;

	static $is_external = null;
	if ( null === $is_external )
		$is_external = \the_seo_framework()->is_sitemap() || \the_seo_framework()->is_robots();

	//* Reset cache.
	\the_seo_framework()->url_slashit = true;
	\the_seo_framework()->unset_current_subdomain();

	static $gli_exists = null;
	if ( null === $gli_exists )
		$gli_exists = function_exists( 'wpml_get_language_information' );

	if ( false === $gli_exists )
		return $path;

	if ( empty( $post_id ) )
		$post_id = \the_seo_framework()->get_the_real_ID();

	//* Cache default language.
	static $default_lang = null;
	if ( null === $default_lang )
		$default_lang = is_callable( array( $sitepress, 'get_default_language' ) ) ? $sitepress->get_default_language() : '';

	//* Cache current language.
	static $current_language = null;
	if ( null === $current_language )
		$current_language = ! ( $is_external || $external ) && is_callable( array( $sitepress, 'get_current_language' ) ) ? $sitepress->get_current_language() : '';

	if ( empty( $current_language ) ) {
		/**
		 * Applies filters 'wpml_post_language_details' : array|wp_error
		 *
		 * Only works for singular items.
		 *
		 * ... Somehow WPML thought this would be great and understandable.
		 * This should be put inside a callable function.
		 *
		 * @since 2.6.0
		 */
		$lang_info = \apply_filters( 'wpml_post_language_details', null, $post_id );

		if ( \is_wp_error( $lang_info ) ) {
			//* Terms and Taxonomies.
			$lang_info = array();

			//* Cache the code.
			static $lang_code = null;
			if ( null === $lang_code )
				$lang_code = defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : false;

			$lang_info['language_code'] = $lang_code;
		}

		//* If filter isn't used, bail.
		if ( empty( $lang_info['language_code'] ) )
			return $path;

		$current_lang = $lang_info['language_code'];
	} else {
		$current_lang = $current_language;
	}

	//* No need to alter URL if we're on default lang.
	if ( $current_lang === $default_lang )
		return $path;

	//* Cache negotiation type.
	static $negotiation_type = null;
	if ( null === $negotiation_type )
		$negotiation_type = is_callable( array( $sitepress, 'get_current_language' ) ) ? $sitepress->get_setting( 'language_negotiation_type' ) : '';

	switch ( $negotiation_type ) :
		case '1' :
			//* Subdirectory

			$t_path = \trailingslashit( $path );

			if ( 0 === strpos( $t_path, '/' . $current_lang . '/' ) ) {
				//* Link is already good.
				return $path;
			} elseif ( 0 === strpos( $t_path, '/' . $default_lang . '/' ) ) {
				//* Link contains default lang. Strip.
				$t_path = substr( $t_path, strlen( '/' . $default_lang ) );

				if ( 0 === strpos( $t_path, '/' . $current_lang . '/' ) ) {
					//* New link contains current lang correctly.
					return \user_trailingslashit( $t_path );
				} else {
					return $path = \trailingslashit( $current_lang ) . ltrim( \user_trailingslashit( $t_path ), ' \\/' );
				}
			}

			return $path = \trailingslashit( $current_lang ) . ltrim( \user_trailingslashit( $path ), ' \\/' );
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

\add_action( 'current_screen', __NAMESPACE__ . '\\_wpml_do_current_screen_action' );
/**
 * Adds WPML filters only on SEO plugin page.
 *
 * @since 2.8.0
 * @access private
 *
 * @param object $current_screen
 */
function _wpml_do_current_screen_action( $current_screen = '' ) {

	if ( \the_seo_framework()->is_seo_settings_page() ) {
		\add_filter( 'wpml_admin_language_switcher_items', __NAMESPACE__ . '\\_wpml_remove_all_languages' );
	}
}

/**
 * Removes "All languages" option from WPML admin switcher.
 *
 * @since 2.8.0
 * @access private
 *
 * @param array $languages_links
 * @return array
 */
function _wpml_remove_all_languages( $languages_links = array() ) {

	unset( $languages_links['all'] );

	return $languages_links;
}
