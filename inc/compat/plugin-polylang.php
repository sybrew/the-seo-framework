<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\PolyLang
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\{
	Helper\Query,
	Meta\URI,
};

\add_action( 'the_seo_framework_sitemap_header', __NAMESPACE__ . '\\_polylang_set_sitemap_language' );
\add_filter( 'the_seo_framework_sitemap_endpoint_list', __NAMESPACE__ . '\\_polylang_register_sitemap_languages', 20 );
\add_filter( 'the_seo_framework_sitemap_hpt_query_args', __NAMESPACE__ . '\\_polylang_sitemap_append_non_translatables' );
\add_filter( 'the_seo_framework_sitemap_nhpt_query_args', __NAMESPACE__ . '\\_polylang_sitemap_append_non_translatables' );
\add_filter( 'the_seo_framework_title_from_custom_field', __NAMESPACE__ . '\\pll__' );
\add_filter( 'the_seo_framework_title_from_generation', __NAMESPACE__ . '\\pll__' );
\add_filter( 'the_seo_framework_generated_description', __NAMESPACE__ . '\\pll__' );
\add_filter( 'the_seo_framework_custom_field_description', __NAMESPACE__ . '\\pll__' );
\add_filter( 'the_seo_framework_front_init', __NAMESPACE__ . '\\_hijack_polylang_home_url' );
\add_filter( 'pll_home_url_white_list', __NAMESPACE__ . '\\_polylang_allow_tsf_home_url' );
\add_filter( 'pll_home_url_allow_list', __NAMESPACE__ . '\\_polylang_allow_tsf_home_url' );
\add_action( 'the_seo_framework_cleared_sitemap_transients', __NAMESPACE__ . '\\_polylang_flush_sitemap' );
\add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\_defunct_badly_coded_polylang_script', 11 );
\add_filter( 'the_seo_framework_seo_column_keys_order', __NAMESPACE__ . '\\_polylang_seo_column_keys_order' );

/**
 * Registeres more sitemaps for the robots.txt to parse.
 *
 * This has no other intended effect. But default permalinks may react more tsf_sitemap query values,
 * specifically ?tsf_sitemap=_base_polylang_es&lang=es" (assumed, untested).
 *
 * @hook the_seo_framework_sitemap_endpoint_list 20
 * @since 5.0.5
 * @param array[] $list {
 *     A list of sitemap endpoints keyed by ID.
 *
 *     @type string|false $lock_id  Optional. The cache key to use for locking. Defaults to index 'id'.
 *                                  Set to false to disable locking.
 *     @type string|false $cache_id Optional. The cache key to use for storing. Defaults to index 'id'.
 *                                  Set to false to disable caching.
 *     @type string       $endpoint The expected "pretty" endpoint, meant for administrative display.
 *     @type string       $epregex  The endpoint regex, following the home path regex.
 *     @type callable     $callback The callback for the sitemap output.
 *     @type bool         $robots   Whether the endpoint should be mentioned in the robots.txt file.
 * }
 * @return array[]
 */
function _polylang_register_sitemap_languages( $list ) {

	if ( empty( $list['base'] ) )
		return $list;

	if ( ! Helper\Compatibility::can_i_use( [
		'functions' => [
			'pll_languages_list',
			'pll_default_language',
		],
	] ) ) return $list;

	// Do most work outside of a loop. We have two loops because of this.
	// We fall back to -1 because null/false match with '0'
	switch ( \get_option( 'polylang' )['force_lang'] ?? -1 ) {
		case 0: // The language is set from content.
			foreach (
				array_diff(
					\pll_languages_list( [ 'hide_empty' => 1 ] ),
					[ \pll_default_language() ],
				)
				as $language
			) {
				$list[ "_base_polylang_$language" ] = [
					'endpoint' => URI\Utils::append_query_to_url(
						$list['base']['endpoint'],
						"lang=$language",
					),
				] + $list['base'];
			}
			break;
		case 1: // The language is set from the directory name in pretty permalinks.
			foreach (
				array_diff(
					\pll_languages_list( [ 'hide_empty' => 1 ] ),
					[ \pll_default_language() ],
				)
				as $language
			) {
				$list[ "_base_polylang_$language" ] = [
					'endpoint' => "$language/{$list['base']['endpoint']}",
				] + $list['base'];
			}
	}

	return $list;
}

/**
 * Sets the correct Polylang query language for the sitemap based on the 'lang' GET parameter.
 *
 * When the user supplies a correct 'lang' query parameter, they can overwrite our testing for force_lang settings.
 * This is a fallback solution because we get endless support requests for Polylang, and we wish that plugin would be
 * rewritten from scratch.
 *
 * @hook the_seo_framework_sitemap_header 10
 * @since 4.1.2
 * @access private
 */
function _polylang_set_sitemap_language() {

	if ( ! \function_exists( 'PLL' ) || ! ( \PLL() instanceof \PLL_Frontend ) ) return;

	// phpcs:ignore, WordPress.Security.NonceVerification.Recommended -- Arbitrary input expected.
	$lang = $_GET['lang'] ?? '';

	// Language codes are user-definable: copy Polylang's filtering.
	// The preg_match's source: \PLL_Admin_Model::validate_lang();
	if ( ! \is_string( $lang ) || ! \strlen( $lang ) || ! preg_match( '#^[a-z_-]+$#', $lang ) ) {

		switch ( \get_option( 'polylang' )['force_lang'] ?? -1 ) {
			case 0:
				// Polylang determines language sporadically from content: can't be trusted. Overwrite.
				$lang = \function_exists( 'pll_default_language' ) ? \pll_default_language() : $lang;
				break;
			default:
				// Polylang can differentiate languages by (sub)domain/directory name early. No need to interfere. Cancel.
				return;
		}
	}

	// This will default to the default language when $lang is invalid or unregistered. This is fine.
	$new_lang = \PLL()->model->get_language( $lang );

	if ( $new_lang ) {
		\PLL()->curlang = $new_lang;
		\did_action( 'pll_language_defined' ) or \do_action( 'pll_language_defined' );
	}
}

/**
 * Appends nontranslatable post types to the sitemap query arguments.
 * Only appends when the default sitemap language is displayed.
 *
 * TODO Should we fix this? If user unassigns a post type as translatable, previously "translated" posts are still
 *      found "translated" by this query. This query, however, is forwarded to WP_Query, which Polylang can filter.
 *      It wouldn't surprise me if they added another black/white list for that. So, my investigation stops here.
 *
 * @hook the_seo_framework_sitemap_hpt_query_args 10
 * @hook the_seo_framework_sitemap_nhpt_query_args 10
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

	if ( ! Helper\Compatibility::can_i_use( [
		'functions' => [
			'PLL',
			'pll_languages_list',
			'pll_default_language',
		],
	] ) ) return $args;

	if ( ! ( \PLL() instanceof \PLL_Frontend ) ) return $args;

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
 * Enables string translation support on titles and descriptions.
 *
 * @hook the_seo_framework_title_from_custom_field 10
 * @hook the_seo_framework_title_from_generation 10
 * @hook the_seo_framework_generated_description 10
 * @hook the_seo_framework_custom_field_description 10
 * @since 3.1.0
 * @access private
 *
 * @param string $string The title or description
 * @return string
 */
function pll__( $string ) {
	if ( \function_exists( 'PLL' ) && \function_exists( 'pll__' ) )
		if ( \PLL() instanceof \PLL_Frontend )
			return \pll__( $string );

	return $string;
}

/**
 * Deletes all sitemap transients, instead of just one.
 *
 * We didn't implement this in our default APIs because we want to trigger WP hooks.
 * Executing database queries directly bypass those. So, we do this afterward.
 *
 * @hook the_seo_framework_cleared_sitemap_transients 10
 * @since 4.0.5
 * @since 5.0.0 Removed clearing once-per-request restriction.
 * @global \wpdb $wpdb
 * @access private
 */
function _polylang_flush_sitemap() {
	global $wpdb;

	$transient_prefix = Sitemap\Cache::get_transient_prefix();

	$wpdb->query( $wpdb->prepare(
		"DELETE FROM $wpdb->options WHERE option_name LIKE %s",
		$wpdb->esc_like( "_transient_$transient_prefix" ) . '%',
	) );

	// We didn't use a wildcard after "_transient_" to reduce scans.
	// A second query is faster on saturated sites.
	$wpdb->query( $wpdb->prepare(
		"DELETE FROM $wpdb->options WHERE option_name LIKE %s",
		$wpdb->esc_like( "_transient_timeout_$transient_prefix" ) . '%',
	) );
}

/**
 * Polylang breaks the admin interface quick-edit and terms-addition functionality.
 * This hack seeks to remove their broken code, letting WordPress take over
 * correctly once more with full forward and backward compatibility, as we proposed.
 *
 * Practically, this applies the proposed fix of <https://github.com/polylang/polylang/issues/928#issuecomment-1040062844>.
 *
 * @hook admin_enqueue_scripts 11
 * @see https://github.com/polylang/polylang/issues/928
 * @since 5.0.0
 */
function _defunct_badly_coded_polylang_script() {

	// Find last ajaxSuccess handler.
	// Since this code runs directly after Polylang, it should grab theirs.
	$remove_ajax_success = <<<'JS'
	jQuery( () => {
		const handler = jQuery._data( document, 'events' )?.ajaxSuccess?.pop().handler;
		handler && jQuery( document ).off( 'ajaxSuccess', handler );
	} );
	JS;

	// Remove PLL term handler on ajaxSuccess. It is redundant, achieves nothing,
	// creates redundant secondary requests, and breaks all plugins but Yoast SEO.
	\wp_add_inline_script( 'pll_term', $remove_ajax_success );

	// Remove PLL post handler on ajaxSuccess. It is redundant, achieves nothing,
	// creates redundant secondary requests, and breaks all plugins but Yoast SEO.
	\wp_add_inline_script( 'pll_post', $remove_ajax_success );
}

/**
 * Polylang breaks the home URL by not always augmenting the home URL.
 * This hack lets Polylang's home_url filter think it's ready to start augmenting URLs, as we proposed,
 * instead of engaging far too late.
 *
 * Practically, this applies the proposed fix of <https://github.com/polylang/polylang/issues/1422#issuecomment-1970620222>.
 *
 * Polylang also tests for a debug backtrace. They skip the first two callbacks because they are redundant.
 * We add another callback in this trace: but it is at position 0. So, the second trace is now tested
 * in Polylang's method (it being `apply_filters()`). This is of no functional impact but on the performance,
 * since that function is not in the allow/block lists.
 *
 * @hook the_seo_framework_front_init 10
 * @see https://github.com/polylang/polylang/issues/1422
 * @see https://github.com/sybrew/the-seo-framework/issues/665
 * @since 5.0.5
 */
function _hijack_polylang_home_url() {

	if ( ! \function_exists( 'PLL' ) || ! ( \PLL() instanceof \PLL_Frontend ) ) return;

	$default_cb = [ \PLL()->filters_links ?? null, 'home_url' ];
	// If not false, this will imply method `home_url()` exists and is public.
	$priority = $default_cb[0] ? \has_filter( 'home_url', $default_cb ) : false;

	if ( false === $priority ) return;

	\remove_filter( 'home_url', $default_cb, $priority );

	\add_filter(
		'home_url',
		function ( ...$args ) use ( $default_cb ) {
			global $wp_actions;

			// Polylang runs as intended at template_redirect or later. Don't trick when pll_language_defined didn't run.
			if ( isset( $wp_actions['template_redirect'] ) || ! isset( $wp_actions['pll_language_defined'] ) )
				return \call_user_func_array( $default_cb, $args );

			// Trick Polylang.
			// phpcs:ignore, WordPress.WP.GlobalVariablesOverride.Prohibited -- it's called a hijack for a reason.
			$wp_actions['template_redirect'] = 1;

			$url = \call_user_func_array( $default_cb, $args );

			// Undo trick.
			unset( $wp_actions['template_redirect'] );

			return $url;
		},
		$priority,
		4, // forward all the args.
	);
}

/**
 * Polylang breaks the home URL by not always augmenting the home URL.
 * This filter adds TSF as correctly interpreting the home URL, so it can be
 * agnostic about the home URL request.
 *
 * @hook pll_home_url_white_list 10 - I didn't pick this name.
 * @hook pll_home_url_allow_list 10 - some day this will probably be instated.
 * @since 5.0.5
 * @param string[][] $allow_list An array of arrays each of them having a 'file' key
 *                               and/or a 'function' key to decide which functions in
 *                               which files using home_url() calls must be filtered.
 * @return string[]
 */
function _polylang_allow_tsf_home_url( $allow_list ) {

	$allow_list[] = [ 'file' => \THE_SEO_FRAMEWORK_DIR_PATH ];

	return $allow_list;
}

/**
 * Polylang and TSF race to prepend their column keys on terms to 'posts'.
 *
 * This filter forces TSF to be put before the language selection of Polylang
 * by prioritizing their column keys to what TSF will prepend itself to.
 *
 * @since 5.1.0
 *
 * @param string[] $order_keys The column keys order.
 * @return string[] The column keys order.
 */
function _polylang_seo_column_keys_order( $order_keys ) {

	if ( ! \function_exists( 'PLL' ) || ! ( \PLL() instanceof \PLL_Admin ) )
		return $order_keys;

	$language_keys = array_map(
		fn( $language ) => "language_{$language->slug}",
		\PLL()->model->get_languages_list(),
	);

	array_unshift( $order_keys, ...$language_keys );

	return $order_keys;
}
