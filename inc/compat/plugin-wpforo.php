<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\wpForo
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Meta;

\add_action( 'the_seo_framework_seo_bar', __NAMESPACE__ . '\\_assert_wpforo_page_seo_bar' );
\add_action( 'wpforo_before_init', __NAMESPACE__ . '\\_wpforo_fix_page' );

/**
 * Initializes wpForo page fixes.
 *
 * @hook wpforo_before_init 10
 * @since 2.9.2
 * @since 3.1.2 1. Now disables HTML output when wpForo SEO is enabled.
 *              2. Now disables title override when wpForo Title SEO is enabled.
 * @since 4.2.8 1. Now supports wpForo 2.0+.
 *              2. Now disables TSF's output by default; for if their API disappears again.
 *              3. Now uses action `wpforo_before_init` instead of `the_seo_framework_init`.
 */
function _wpforo_fix_page() {

	if ( \is_admin() || ! \function_exists( 'is_wpforo_page' ) || ! \is_wpforo_page() ) return;

	if ( _wpforo_seo_title_enabled() ) { // phpcs:ignore, TSF.Performance.Opcodes -- is local.
		\add_filter( 'the_seo_framework_title_from_generation', __NAMESPACE__ . '\\_wpforo_filter_pre_title', 10, 2 );
		\add_filter( 'the_seo_framework_use_title_branding', '__return_false' );
	}

	if ( _wpforo_seo_meta_enabled() ) { // phpcs:ignore, TSF.Performance.Opcodes -- is local.
		// Remove TSF's output: Twofold, may they change the order of operation in a future update.
		_wpforo_disable_tsf_html_output(); // phpcs:ignore, TSF.Performance.Opcodes -- is local.

		// This won't run on wpForo at the time of writing (2.1.6), because action the_seo_framework_after_init already happened.
		\add_action( 'the_seo_framework_after_init', __NAMESPACE__ . '\\_wpforo_disable_tsf_html_output', 1 );
	} else {
		// Remove WPForo's SEO meta output.
		\remove_action( 'wp_head', 'wpforo_add_meta_tags', 1 );
		// Fix Canonical URL.
		\add_filter( 'get_canonical_url', __NAMESPACE__ . '\\_wpforo_filter_canonical_url', 10, 2 );
	}
}

/**
 * Disables The SEO Framework's meta tag output on wpForo pages.
 *
 * @hook the_seo_framework_after_init 1
 * @since 3.1.2 Introduced as Lambda.
 * @since 4.0.5 Introduced as function.
 * @access private
 */
function _wpforo_disable_tsf_html_output() {
	\remove_action( 'wp_head', [ Front\Meta\Head::class, 'print_wrap_and_tags' ], 1 );
}

/**
 * Filters the canonical/request URL for wpForo.
 *
 * @hook get_canonical_url 10
 * @since 2.9.2 Introduced as Lambda.
 * @since 4.0.5 Introduced as function.
 * @access private
 *
 * @param string   $canonical_url The post's canonical URL.
 * @param \WP_Post $post          Post object.
 * @return string
 */
function _wpforo_filter_canonical_url( $canonical_url, $post ) { // phpcs:ignore, VariableAnalysis.CodeAnalysis.VariableAnalysis
	return \function_exists( 'wpforo_get_request_uri' ) ? \wpforo_get_request_uri() : $canonical_url;
}

/**
 * Fixes wpForo page Titles.
 *
 * @hook the_seo_framework_title_from_generation 10
 * @since 2.9.2
 * @since 3.1.0 1. No longer emits an error when no wpForo title is presented.
 *              2. Updated to support new title generation.
 * @since 4.0.0 No longer overrules external queries.
 * @access private
 * @todo this may cause issues when the forum is on the homepage... Tell users to set the "additions".
 *
 * @param string     $title The filter title.
 * @param array|null $args  The query arguments. Contains 'id', 'tax', 'pta', and 'uid'.
 *                          Is null when the query is auto-determined.
 * @return string $title The wpForo title.
 */
function _wpforo_filter_pre_title( $title, $args ) {

	if ( ! isset( $args ) ) {
		$sep          = Meta\Title::get_separator();
		$wpforo_title = implode(
			" $sep ",
			array_filter( (array) \wpforo_meta_title( '' ), 'strlen' )
		);
	}

	return ( $wpforo_title ?? '' ) ?: $title;
}

/**
 * Appends noindex default checks to the noindex item of the SEO Bar for pages.
 *
 * @hook the_seo_framework_seo_bar 10
 * @since 4.2.8
 * @access private
 *
 * @param string $interpreter The interpreter class name.
 */
function _assert_wpforo_page_seo_bar( $interpreter ) {

	if ( $interpreter::$query['tax'] ) return;

	$meta_enabled  = _wpforo_seo_meta_enabled();   // phpcs:ignore, TSF.Performance.Opcodes -- is local.
	$title_enabled = _wpforo_seo_title_enabled(); // phpcs:ignore, TSF.Performance.Opcodes -- is local.

	if ( ! $meta_enabled && ! $title_enabled ) return;

	$items = &$interpreter::collect_seo_bar_items();

	// Don't do anything if there's a blocking redirect.
	if ( ! empty( $items['redirect']['meta']['blocking'] ) ) return;

	// Skip if we're not dealing with the wpForo page.
	if ( ! \has_shortcode( Data\Post::get_content( $interpreter::$query['id'] ), 'wpforo' ) ) return;

	foreach ( $items as $id => &$item ) {
		switch ( $id ) {
			case 'redirect':
				// Preserve redirect, for TSF still manages that.
				continue 2;
			case 'title':
				if ( ! $title_enabled ) continue 2;
				break;
			default:
				if ( ! $meta_enabled ) continue 2;
		}

		$item['status'] = $interpreter::STATE_UNDEFINED;
		$item['reason'] = \__( 'Cannot assert.', 'autodescription' );

		// Clear all assessments.
		$item['assess'] = [];

		$item['assess']['base'] = \sprintf(
			// translators: %s = Plugin name.
			\__( 'This is managed by plugin "%s."', 'autodescription' ),
			'wpForo Forum',
		);
	}
}

/**
 * Tests whether wpForo SEO Meta is enabled.
 *
 * @since 4.2.8
 * @access private
 *
 * @return bool
 */
function _wpforo_seo_meta_enabled() {
	return memo() ?? memo(
		// Unreliable in WPForo 2.0.0~2.1.6 (latest version at time of recording). Therefore, assume "true" if null.
		// See https://wordpress.org/support/topic/wpforo_setting-seo-doesnt-work/.
		\function_exists( 'wpforo_setting' ) && ( \wpforo_setting( 'seo', 'seo_meta' ) ?? true )
	);
}

/**
 * Tests whether wpForo SEO Title is enabled.
 *
 * @since 4.2.8
 * @access private
 *
 * @return bool
 */
function _wpforo_seo_title_enabled() {
	return memo() ?? memo(
		// Unreliable in WPForo 2.0.0~2.1.6 (latest version at time of recording). Therefore, assume "true" if null.
		// See https://wordpress.org/support/topic/wpforo_setting-seo-doesnt-work/.
		\function_exists( 'wpforo_setting' ) && ( \wpforo_setting( 'seo', 'seo_title' ) ?? true )
	);
}
