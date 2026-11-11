<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\TranslatePress
 * @subpackage The_SEO_Framework\Compatibility
 * @access private
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use The_SEO_Framework\Helper\Compatibility;

\add_filter( 'the_seo_framework_sitemap_endpoint_list', __NAMESPACE__ . '\_translatepress_register_sitemap_languages', 20 );
\add_filter( 'the_seo_framework_sitemap_settings_language_endpoints', __NAMESPACE__ . '\_translatepress_sitemap_language_endpoints' );
\add_filter( 'the_seo_framework_sitemap_base_path', __NAMESPACE__ . '\_translatepress_fix_sitemap_base_path' );
\add_action( 'the_seo_framework_cleared_sitemap_transients', __NAMESPACE__ . '\_translatepress_flush_sitemap' );
\add_action( 'init', __NAMESPACE__ . '\_translatepress_prepare_sitemap_request', 2 );

/**
 * Returns the language an unprefixed URL uses.
 *
 * With a directory for the default language, TranslatePress shows visitors the
 * first published language at that URL.
 *
 * @since 5.2.0
 * @param array $settings TranslatePress settings.
 * @return string Language code.
 */
function _translatepress_unprefixed_language( $settings ) {

	if ( ( $settings['add-subdirectory-to-default-language'] ?? null ) !== 'yes' )
		return $settings['default-language'];

	$published = $settings['publish-languages'] ?? null;
	$first     = \is_array( $published ) ? ( $published[0] ?? null ) : null;

	if ( ! \is_string( $first ) || ! \strlen( $first ) )
		return $settings['default-language'];

	return $first;
}

/**
 * Registers `{slug}/sitemap.xml` for each published TranslatePress language.
 *
 * Pretty permalinks are the supported path. A language without a directory is
 * skipped. The language of the unprefixed sitemap is omitted from robots.txt.
 *
 * @hook the_seo_framework_sitemap_endpoint_list 20
 * @since 5.2.0
 * @link https://github.com/sybrew/the-seo-framework/issues/615
 * @example $list `[ 'base' => [ 'endpoint' => 'sitemap.xml', 'robots' => true ] ]`
 * @param array[] $list Sitemap endpoints keyed by ID.
 * @return array[]
 */
function _translatepress_register_sitemap_languages( $list ) {

	if ( empty( $list['base'] ) )
		return $list;

	[ $settings, $converter ] = _translatepress_runtime();

	if ( ! $converter )
		return $list;

	$published = $settings['publish-languages'] ?? null;

	if ( ! \is_array( $published ) )
		return $list;

	$primary = _translatepress_unprefixed_language( $settings );

	foreach ( $published as $language ) {
		if ( ! \is_string( $language ) || ! \strlen( $language ) ) continue;

		// Empty when this language is not served from a directory.
		$slug = $converter->get_url_slug( $language );

		if ( ! \strlen( $slug ) ) continue;

		$endpoint = "{$slug}/{$list['base']['endpoint']}";

		$list[ "_base_translatepress_$language" ] = [
			'endpoint' => $endpoint,
			'regex'    => '/^' . preg_quote( $endpoint, '/' ) . '/i',
			'robots'   => $language !== $primary,
		] + $list['base'];
	}

	return $list;
}

/**
 * Lists advertised TranslatePress sitemap endpoints for SEO Settings.
 *
 * @hook the_seo_framework_sitemap_settings_language_endpoints 10
 * @since 5.2.0
 * @param string[] $endpoints Language names keyed by sitemap endpoint ID.
 * @return string[]
 */
function _translatepress_sitemap_language_endpoints( $endpoints ) {

	[ $settings, $converter, $trp ] = _translatepress_runtime();

	if ( ! $converter )
		return $endpoints;

	$published = $settings['publish-languages'] ?? null;
	$primary   = _translatepress_unprefixed_language( $settings );

	if ( ! \is_array( $published ) || ! \strlen( $primary ) )
		return $endpoints;

	$languages = $trp->get_component( 'languages' );
	$codes     = [];

	foreach ( $published as $language ) {
		if ( ! \is_string( $language ) || ! \strlen( $language ) ) continue;

		if ( $language === $primary ) continue;

		$slug = $converter->get_url_slug( $language );

		if ( ! \strlen( $slug ) ) continue;

		$codes[] = $language;
	}

	if ( ! $codes )
		return $endpoints;

	$names = \is_object( $languages ) && method_exists( $languages, 'get_language_names' )
		? $languages->get_language_names( $codes )
		: [];

	foreach ( $codes as $code )
		$endpoints[ "_base_translatepress_$code" ] = ( $names[ $code ] ?? null ) ?: $code;

	return $endpoints;
}

/**
 * Returns the sitemap base path without TranslatePress's language directory.
 *
 * @hook the_seo_framework_sitemap_base_path 10
 * @since 5.2.0
 * @param string $path The home path.
 * @return string The unfiltered home path.
 */
function _translatepress_fix_sitemap_base_path( $path ) {

	[ , $converter ] = _translatepress_runtime();

	if ( ! $converter )
		return $path;

	$home = $converter->get_abs_home();

	if ( ! \is_string( $home ) || ! \strlen( $home ) )
		return $path;

	return rtrim(
		(string) parse_url( $home, \PHP_URL_PATH ),
		'/',
	);
}

/**
 * Deletes sitemap transients for every published TranslatePress language.
 *
 * @hook the_seo_framework_cleared_sitemap_transients 10
 * @since 5.2.0
 */
function _translatepress_flush_sitemap() {

	[ $settings ] = _translatepress_runtime();

	$published = \is_array( $settings )
		? ( $settings['publish-languages'] ?? null )
		: null;

	if ( ! \is_array( $published ) ) return;

	Sitemap\Cache::delete_sitemap_transients_for_locales( $published );
}

/**
 * Sets the sitemap language before the homepage URL is memoized.
 *
 * Runs at priority 2, after TranslatePress and before `parse_request`.
 * An unprefixed sitemap uses the language visitors get without a directory.
 * One directory uses that language.
 *
 * @hook init 2
 * @since 5.2.0
 */
function _translatepress_prepare_sitemap_request() {

	$uri = $_SERVER['REQUEST_URI'] ?? null;

	if ( ! \is_string( $uri ) || false === stripos( $uri, 'sitemap' ) ) return;

	[ $settings, $converter ] = _translatepress_runtime();

	if ( ! $converter ) return;

	$default   = $settings['default-language'] ?? null;
	$published = $settings['publish-languages'] ?? null;

	if (
		   ! \is_string( $default )
		|| ! \strlen( $default )
		|| ! \is_array( $published )
	) {
		return;
	}

	$home = trim(
		(string) parse_url(
			(string) $converter->get_abs_home(),
			\PHP_URL_PATH,
		),
		'/',
	);
	$path = trim(
		(string) parse_url( $uri, \PHP_URL_PATH ),
		'/',
	);

	if ( \strlen( $home ) && str_starts_with( $path, "$home/" ) )
		$path = substr( $path, \strlen( $home ) + 1 );

	if ( str_starts_with( $path, 'index.php/' ) )
		$path = substr( $path, \strlen( 'index.php/' ) );

	if ( ! preg_match(
		'/^(?:([^\/]+)\/)?(sitemap(?:_index)?\.xml)$/i',
		$path,
		$matches,
	) ) {
		return;
	}

	[ , $directory, $file ] = $matches;

	if ( ! \strlen( $directory ) ) {
		$language = _translatepress_unprefixed_language( $settings );
	} else {
		if ( 'sitemap.xml' !== strtolower( $file ) ) return;

		$language = '';

		foreach ( $published as $code ) {
			if ( ! \is_string( $code ) || ! \strlen( $code ) ) continue;

			$slug = $converter->get_url_slug( $code );

			if ( \strlen( $slug ) && 0 === strcasecmp( $slug, $directory ) ) {
				$language = $code;
				break;
			}
		}

		if ( ! \strlen( $language ) ) return;
	}

	$GLOBALS['TRP_LANGUAGE'] = $language;

	$slug = $converter->get_url_slug( $language );

	if ( ! \strlen( $slug ) ) return;

	\add_filter( 'home_url', __NAMESPACE__ . '\_translatepress_prefix_home_url', 2, 2 );
}

/**
 * Prefixes the homepage, which TranslatePress skips during a sitemap request.
 *
 * @hook home_url 2
 * @since 5.2.0
 * @param string $url  The home URL.
 * @param string $path Path relative to the home URL.
 * @return string
 */
function _translatepress_prefix_home_url( $url, $path = '' ) {

	if ( ! \is_string( $url ) || ! \strlen( $url ) )
		return $url;

	if ( \is_string( $path ) && \strlen( trim( $path, '/' ) ) )
		return $url;

	[ , $converter ] = _translatepress_runtime();

	$language = $GLOBALS['TRP_LANGUAGE'] ?? null;

	if (
		   ! $converter
		|| ! \is_string( $language )
		|| ! \strlen( $language )
	) {
		return $url;
	}

	$slug = $converter->get_url_slug( $language );

	if ( ! \strlen( $slug ) )
		return $url;

	$home = \untrailingslashit( (string) $converter->get_abs_home() );

	if ( ! \strlen( $home ) || ! str_starts_with( $url, $home ) )
		return $url;

	$segment = strtok(
		ltrim(
			substr( $url, \strlen( $home ) ),
			'/',
		),
		'/?#',
	);

	if ( \is_string( $segment ) && 0 === strcasecmp( $segment, $slug ) )
		return $url;

	$slash = str_ends_with( $url, '/' );
	$url   = \trailingslashit( $home ) . $slug;

	if ( $slash )
		$url = \trailingslashit( $url );

	return $url;
}

/**
 * Returns TranslatePress settings, its URL converter, and the plugin instance.
 *
 * A missing plugin is not cached. Either every entry is set, or every entry is null.
 *
 * @since 5.2.0
 * @return array Index 0 settings, 1 converter, 2 instance.
 */
function _translatepress_runtime() {

	static $runtime = null;

	if ( $runtime )
		return $runtime;

	$unavailable = [ null, null, null ];

	// phpcs:ignore TSF.Performance.Functions.PHP -- we don't autoload.
	if ( ! class_exists( 'TRP_Translate_Press', false ) )
		return $unavailable;

	$trp = \TRP_Translate_Press::get_trp_instance();

	if ( ! \is_object( $trp ) || ! method_exists( $trp, 'get_component' ) )
		return $unavailable;

	$settings  = $trp->get_component( 'settings' );
	$converter = $trp->get_component( 'url_converter' );

	if (
		   ! \is_object( $settings )
		|| ! \is_object( $converter )
		|| ! Compatibility::can_i_use(
			[
				'methods' => [
					[ $settings, 'get_settings' ],
					[ $converter, 'get_url_slug' ],
					[ $converter, 'get_abs_home' ],
				],
			],
		)
	) {
		return $unavailable;
	}

	$stored = $settings->get_settings();

	if ( ! \is_array( $stored ) )
		return $unavailable;

	return $runtime = [ $stored, $converter, $trp ];
}
