<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\PolyLang
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and \tsf()->_verify_include_secret( $_secret ) or die;

\add_filter( 'the_seo_framework_sitemap_base_path', __NAMESPACE__ . '\\_polylang_fix_sitemap_base_bath' );
/**
 * Sets the correct Polylang base path language when using cookie-based language settings.
 * This resolves an issue where the sitemap would otherwise return a 404 error after a
 * cookie has been set.
 *
 * This function uses the same methods as the main filter.
 * HOWEVER, because of the languid asinine catabolic implementation of the black/block-list in Polylang,
 * this method returns a different value. It's truly astounding how one cannot trust the API.

 * @since 4.1.2
 * @access private
 *
 * @param string $path The home path.
 * @return string Polylang-aware home path.
 */
function _polylang_fix_sitemap_base_bath( $path ) {

	$_options = \get_option( 'polylang' );

	if ( isset( $_options['force_lang'] ) ) {
		switch ( $_options['force_lang'] ) {
			case 0:
				// Polylang determines language sporadically from content: can't be trusted.
				// NOTE: Thanks to '_polylang_blocklist_tsf_urls', this yields a different value albeit the same code.
				// That's Polylang for you: can't trust your own code.
				$path = rtrim(
					parse_url(
						\get_home_url(),
						PHP_URL_PATH
					) ?? '',
					'/'
				);
				break;
			default:
				// Polylang can differentiate languages by (sub)domain/directory name early. No need to interfere.
				break;
		}
	}

	return $path;
}

\add_action( 'the_seo_framework_sitemap_header', __NAMESPACE__ . '\\_polylang_set_sitemap_language' );
/**
 * Sets the correct Polylang query language for the sitemap based on the 'lang' GET parameter.
 *
 * When the user supplies a correct 'lang' query parameter, they can overwrite our testing for force_lang settings.
 * This is a fallback solution because we get endless support requests for Polylang, and we wish that plugin would be
 * rewritten from scratch.
 *
 * @since 4.1.2
 * @access private
 */
function _polylang_set_sitemap_language() {

	if ( ! \function_exists( '\\PLL' ) || ! ( \PLL() instanceof \PLL_Frontend ) ) return;

	// phpcs:ignore, WordPress.Security.NonceVerification.Recommended -- Arbitrary input expected.
	$lang = $_GET['lang'] ?? '';

	// Language codes are user-definable: copy Polylang's filtering.
	// The preg_match's source: \PLL_Admin_Model::validate_lang();
	if ( ! \is_string( $lang ) || ! \strlen( $lang ) || ! preg_match( '#^[a-z_-]+$#', $lang ) ) {
		$_options = \get_option( 'polylang' );
		if ( isset( $_options['force_lang'] ) ) {
			switch ( $_options['force_lang'] ) {
				case 0:
					// Polylang determines language sporadically from content: can't be trusted. Overwrite.
					$lang = \pll_default_language();
					break;
				default:
					// Polylang can differentiate languages by (sub)domain/directory name early. No need to interfere. Cancel.
					return;
			}
		}
	}

	// This will default to the default language when $lang is invalid or unregistered. This is fine.
	$new_lang = \PLL()->model->get_language( $lang );

	if ( $new_lang )
		\PLL()->curlang = $new_lang;
}

\add_filter( 'the_seo_framework_sitemap_hpt_query_args', __NAMESPACE__ . '\\_polylang_sitemap_append_non_translatables' );
\add_filter( 'the_seo_framework_sitemap_nhpt_query_args', __NAMESPACE__ . '\\_polylang_sitemap_append_non_translatables' );
/**
 * Appends nontranslatable post types to the sitemap query arguments.
 * Only appends when the default sitemap language is displayed.
 *
 * TODO Should we fix this? If user unassigns a post type as translatable, previously "translated" posts are still
 *      found "translated" by this query. This query, however, is forwarded to WP_Query, which Polylang can filter.
 *      It wouldn't surprise me if they added another black/white list for that. So, my investigation stops here.
 *
 * @since 4.1.2
 * @since 4.2.0 Now relies on the term_id, instead of mixing term_taxonomy_id and term_id.
 *              This is unlike Polylang, which relies on term_taxonomy_id somewhat consistently; however,
 *              in this case we can use term_id since we're specifying the taxonomy directly.
 *              WordPress 4.4.0 and later also rectifies term_id/term_taxonomy_id stratification, which is
 *              why we couldn't find an issue whilst introducing this filter.
 * @access private
 *
 * @param array $args The query arguments.
 * @return array The augmented query arguments.
 */
function _polylang_sitemap_append_non_translatables( $args ) {

	if ( ! \tsf()->can_i_use( [
		'functions' => [
			'PLL',
			'pll_languages_list',
			'pll_default_language',
		],
	] ) ) return $args;

	if ( ! ( \PLL() instanceof \PLL_Frontend ) ) return $args;

	// Redundantly prefixed \, OBJECT is actually a constant; however, the linter derps out, thinking it's a cast.
	$default_lang = \pll_default_language( \OBJECT );

	if ( ! isset( $default_lang->slug, $default_lang->term_id ) ) return $args;

	if ( ( \PLL()->curlang->slug ?? null ) === $default_lang->slug ) {
		$args['lang']      = ''; // Select all lang, so that Polylang doesn't affect the query below with an AND (we need OR).
		$args['tax_query'] = [
			'relation' => 'OR',
			[
				'taxonomy' => 'language',
				'terms'    => \pll_languages_list( [ 'fields' => 'term_id' ] ),
				'operator' => 'NOT IN',
			],
			[
				'taxonomy' => 'language',
				'terms'    => $default_lang->term_id,
				'operator' => 'IN',
			],
		];
	}

	return $args;
}

/**
 * Warns homepage global title and description about receiving input.
 *
 * @since 3.1.0
 */
\add_filter( 'the_seo_framework_warn_homepage_global_title', '__return_true' );
\add_filter( 'the_seo_framework_warn_homepage_global_description', '__return_true' );
\add_filter( 'the_seo_framework_tell_multilingual_sitemap', '__return_true' );

\add_filter( 'the_seo_framework_title_from_custom_field', __NAMESPACE__ . '\\pll__' );
\add_filter( 'the_seo_framework_title_from_generation', __NAMESPACE__ . '\\pll__' );
\add_filter( 'the_seo_framework_generated_description', __NAMESPACE__ . '\\pll__' );
\add_filter( 'the_seo_framework_custom_field_description', __NAMESPACE__ . '\\pll__' );
/**
 * Enables string translation support on titles and descriptions.
 *
 * @since 3.1.0
 * @access private
 *
 * @param string $string The title or description
 * @return string
 */
function pll__( $string ) {
	if ( \function_exists( '\\PLL' ) && \function_exists( '\\pll__' ) )
		if ( \PLL() instanceof \PLL_Frontend )
			return \pll__( $string );

	return $string;
}

\add_filter( 'pll_home_url_white_list', __NAMESPACE__ . '\\_polylang_allowlist_tsf_urls' );
/**
 * Accompany the most broken and asinine idea in WordPress's history.
 * Adds The SEO Framework's files to their allowlist of autoincompatible doom.
 *
 * @since 3.2.4
 * @since 4.1.0 Renamed function and parameters to something racially neutral.
 * @access private
 *
 * @param array $allowlist The wildcard file parts that are allowlisted.
 * @return array
 */
function _polylang_allowlist_tsf_urls( $allowlist ) {
	$allowlist[] = [ 'file' => 'autodescription/inc' ];
	return $allowlist;
}

\add_filter( 'pll_home_url_black_list', __NAMESPACE__ . '\\_polylang_blocklist_tsf_urls' );
/**
 * Accompany the most broken and asinine idea in WordPress's history.
 * ...and stop messing with the rewrite system while doing so.
 * Also, you should add support for class methods. Stop living in the PHP 4 era.
 *
 * @since 3.2.4
 * @since 4.1.0 Renamed function and parameters to something racially neutral.
 * @since 4.1.2 Prefixed function name with _polylang.
 * @access private
 *
 * @param array $blocklist The wildcard file parts that are blocklisted.
 * @return array
 */
function _polylang_blocklist_tsf_urls( $blocklist ) {
	$blocklist[] = [ 'file' => 'autodescription/inc/compat/plugin-polylang.php' ];
	return $blocklist;
}

\add_filter( 'the_seo_framework_rel_canonical_output', __NAMESPACE__ . '\\_polylang_fix_home_url', 10, 1 );
\add_filter( 'the_seo_framework_ogurl_output', __NAMESPACE__ . '\\_polylang_fix_home_url', 10, 1 );
/**
 * Adds a trailing slash to whatever's deemed as the homepage URL.
 * This fixes user_trailingslashit() issues.
 *
 * @since 3.2.4
 * @since 4.1.2 Prefixed function name with _polylang.
 * @since 4.2.0 No longer uses the second parameter, and relies on theq query to find the homepage, instead.
 * @access private
 *
 * @param string $url The url to fix.
 * @return string The fixed home URL.
 */
function _polylang_fix_home_url( $url ) {
	return \tsf()->is_real_front_page() && \get_option( 'permalink_structure' ) ? \trailingslashit( $url ) : $url;
}

\add_action( 'the_seo_framework_delete_cache_sitemap', __NAMESPACE__ . '\\_polylang_flush_sitemap', 10, 4 );
/**
 * Deletes all sitemap transients, instead of just one.
 * Can only clear once per request.
 *
 * @since 4.0.5
 * @global \wpdb $wpdb
 * @access private
 *
 * @param string $type    The flush type. Comes in handy when you use a catch-all function.
 * @param int    $id      The post, page or TT ID. Defaults to tsf()->get_the_real_ID().
 * @param array  $args    Additional arguments. They can overwrite $type and $id.
 * @param bool   $success Whether the action cleared.
 */
function _polylang_flush_sitemap( $type, $id, $args, $success ) {

	static $cleared = false;
	if ( $cleared ) return;

	if ( $success ) {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $wpdb->options WHERE option_name LIKE %s",
				$wpdb->esc_like( '_transient_tsf_sitemap_' ) . '%'
			)
		); // No cache OK. DB call ok.

		// We didn't use a wildcard after "_transient_" to reduce scans.
		// A second query is faster on saturated sites.
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $wpdb->options WHERE option_name LIKE %s",
				$wpdb->esc_like( '_transient_timeout_tsf_sitemap_' ) . '%'
			)
		); // No cache OK. DB call ok.

		$cleared = true;
	}
}
