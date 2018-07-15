<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\UltimateMember
 */
namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = \the_seo_framework_class() and $this instanceof $_this or die;

/**
 * Removes extraneous (therefore erroneous) Open Graph and meta functionality of Ultimate Member.
 * We're replacing it, using their API.
 */
\remove_action( 'wp_head', 'um_profile_dynamic_meta_desc', 9999999 );

\add_filter( 'the_seo_framework_title_from_generation', __NAMESPACE__ . '\\_um_filter_generated_title', 10, 1 );
/**
 * Filters the custom title for UM.
 *
 * @since 3.1.0
 * @access private
 *
 * @param string $title The current title.
 * @return string The filtered title.
 */
function _um_filter_generated_title( $title = '' ) {

	if ( \the_seo_framework()->can_i_use( [
		'functions' => [
			'um_is_core_page',
			'um_get_requested_user',
			'um_fetch_user',
			'um_reset_user',
			'um_user',
			'um_get_display_name',
		],
	] ) ) {
		if ( \um_is_core_page( 'user' ) && \um_get_requested_user() ) {
			\um_fetch_user( \um_get_requested_user() );
			$user_id = um_user( 'ID' );
			\um_reset_user();

			$title = \um_get_display_name( $user_id );
		}
	}

	return $title;
}

\add_filter( 'the_seo_framework_ogurl_output', __NAMESPACE__ . '\\_um_filter_generated_url', 10, 1 );
\add_filter( 'the_seo_framework_rel_canonical_output', __NAMESPACE__ . '\\_um_filter_generated_url', 10, 1 );
/**
 * Filters the canonical URL for UM.
 *
 * @since 3.1.0
 * @access private
 *
 * @param string $url The current URL.
 * @return string The filtered URL.
 */
function _um_filter_generated_url( $url = '' ) {

	if ( \the_seo_framework()->can_i_use( [
		'functions' => [
			'um_is_core_page',
			'um_get_requested_user',
			'um_fetch_user',
			'um_reset_user',
			'um_user_profile_url',
		],
	] ) ) {
		if ( \um_is_core_page( 'user' ) && \um_get_requested_user() ) {
			\um_fetch_user( \um_get_requested_user() );
			$url = um_user_profile_url();
			\um_reset_user();
		}
	}

	return $url;
}

\add_filter( 'the_seo_framework_generated_description', __NAMESPACE__ . '\\_um_filter_generated_description', 10, 1 );
/**
 * Filters the generated description for UM.
 *
 * @since 3.1.0
 * @access private
 *
 * @param string $url The current description.
 * @return string The filtered description.
 */
function _um_filter_generated_description( $description = '' ) {

	if ( \the_seo_framework()->can_i_use( [
		'functions' => [
			'um_is_core_page',
			'um_get_requested_user',
			'um_fetch_user',
			'um_reset_user',
			'um_convert_tags',
			'UM',
		],
	] ) ) {
		if ( \um_is_core_page( 'user' ) && \um_get_requested_user() ) {
			\um_fetch_user( \um_get_requested_user() );

			//!! PHP 7 won't fail on the exception. On other versions, an is_callable() loop is too expensive.
			//? However, their deprecated "um_get_option()" short API function tells us to use this.
			try {
				$_description = \um_convert_tags( \UM()->options()->get( 'profile_desc' ) );
			} catch ( \Exception $e ) {
				$_description = '';
			}
			$description = $_description ?: $description;
			\um_reset_user();
		}
	}

	return $description;
}
