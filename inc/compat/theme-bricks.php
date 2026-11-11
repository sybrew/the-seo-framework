<?php
/**
 * @package The_SEO_Framework\Compat\Theme\Bricks
 * @subpackage The_SEO_Framework\Compatibility
 * @access private
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use The_SEO_Framework\Data\{
	Filter\Sanitize,
	Post,
};
use The_SEO_Framework\Helper\{
	Format\HTML,
	Query,
};

\add_filter( 'the_seo_framework_public_post_types', __NAMESPACE__ . '\_bricks_fix_public_post_types' );
\add_filter( 'the_seo_framework_public_taxonomies', __NAMESPACE__ . '\_bricks_fix_public_taxonomies' );
\add_filter(
	'the_seo_framework_get_excerpt',
	__NAMESPACE__ . '\_bricks_filter_excerpt',
	10,
	2,
);

/**
 * Removes support for Bricks' non-public post types conditionally.
 *
 * This solely affects The SEO Framework.
 *
 * @hook the_seo_framework_public_post_types 10
 * @since 5.1.0
 *
 * @param string[] $post_types The list of should-be public post types.
 * @return string[] The list of actual public post types.
 */
function _bricks_fix_public_post_types( $post_types ) {

	if (
		   ! \defined( 'BRICKS_DB_TEMPLATE_SLUG' )
		|| ! class_exists( \Bricks\Database::class, false ) // phpcs:ignore TSF.Performance.Functions.PHP -- autoload is false
		|| \Bricks\Database::get_setting( 'publicTemplates' )
	) {
		return $post_types;
	}

	return array_diff(
		$post_types,
		[ \BRICKS_DB_TEMPLATE_SLUG ],
	);
}

/**
 * Removes support for Bricks' template taxonomies.
 * They aren't used for display, only organizing templates.
 *
 * This solely affects The SEO Framework.
 *
 * @hook the_seo_framework_public_taxonomies 10
 * @since 5.1.0
 *
 * @param string[] $taxonomies The list of should-be public taxonomies.
 * @return string[] The list of actual public taxonomies.
 */
function _bricks_fix_public_taxonomies( $taxonomies ) {

	$unset = [];

	if ( \defined( 'BRICKS_DB_TEMPLATE_TAX_TAG' ) )
		$unset[] = \BRICKS_DB_TEMPLATE_TAX_TAG;

	if ( \defined( 'BRICKS_DB_TEMPLATE_TAX_BUNDLE' ) )
		$unset[] = \BRICKS_DB_TEMPLATE_TAX_BUNDLE;

	return array_diff( $taxonomies, $unset );
}

/**
 * Supplies a description excerpt from Bricks page content.
 *
 * Bricks stores the page as an element tree in post meta, not in post_content.
 * Header and footer templates are separate and are not used.
 * The page is not rendered. Hidden elements and the elements inside them are
 * removed, as are elements shown only for a user login, role, or ID. Text and
 * Section elements that remain supply their text. Dynamic tags such as
 * `{post_title}` and `{echo:...}` are deleted and not executed.
 *
 * @hook the_seo_framework_get_excerpt 10
 * @since 5.2.0
 *
 * @param string $excerpt The obtained excerpt.
 * @param ?array $args    The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
 *                        Leave null to autodetermine query.
 * @return string The excerpt, or the Bricks page text when the excerpt is empty.
 */
function _bricks_filter_excerpt( $excerpt, $args ) {

	if (
		   \strlen( $excerpt )
		|| ! \defined( 'BRICKS_DB_EDITOR_MODE' )
		|| ! \defined( 'BRICKS_DB_PAGE_CONTENT' )
	) {
		return $excerpt;
	}

	if ( ! isset( $args ) ) {
		if ( ! Query::is_singular() )
			return $excerpt;

		$post_id = (int) Query::get_the_real_id();
	} else {
		normalize_generation_args( $args );

		if ( 'single' !== get_query_type_from_args( $args ) )
			return $excerpt;

		$post_id = (int) $args['id'];
	}

	if (
		   ! $post_id
		|| Post::is_protected( $post_id )
		|| 'bricks' !== \get_post_meta( $post_id, \BRICKS_DB_EDITOR_MODE, true )
	) {
		return $excerpt;
	}

	$elements = \get_post_meta( $post_id, \BRICKS_DB_PAGE_CONTENT, true );

	if ( ! \is_array( $elements ) )
		return '';

	$by_id  = [];
	$hidden = [];

	foreach ( $elements as $element ) {
		if ( ! \is_array( $element ) ) continue;

		$id = (string) ( $element['id'] ?? '' ); // Bricks cast their ids to string too -- probably latent bug somewhere.

		if ( ! \strlen( $id ) ) continue;

		$by_id[ $id ] = $element;
		$settings     = $element['settings'] ?? [];

		if ( ! empty( $settings['_hideElementFrontend'] ) ) {
			$hidden[ $id ] = true;
			continue;
		}

		// Sets are OR. One public set keeps the element.
		$conditions = $settings['_conditions'] ?? null;
		$user_gated = \is_array( $conditions ) && $conditions;

		if ( $user_gated ) foreach ( $conditions as $set ) {
			if ( ! \is_array( $set ) ) continue;

			$set_gated = false;

			foreach ( $set as $condition ) {
				if ( ! \is_array( $condition ) ) continue;

				switch ( $condition['key'] ?? '' ) {
					case 'user_id':
					case 'user_logged_in':
					case 'user_registered':
					case 'user_role':
					case 'woo_product_purchased_by_user':
						$set_gated = true;
						break;

					case 'dynamic_data':
						$tag = (string) ( $condition['dynamic_data'] ?? $condition['value'] ?? '' );

						if (
							   str_contains( $tag, 'wp_user_' )
							|| str_contains( $tag, 'echo:' )
						) {
							$set_gated = true;
						}
				}
			}

			if ( ! $set_gated ) {
				$user_gated = false;
				break;
			}
		}

		if ( $user_gated )
			$hidden[ $id ] = true;
	}

	// A hidden element hides its children as well.
	$stack = array_keys( $hidden );

	while ( $stack ) {
		$current = array_pop( $stack );

		foreach ( $by_id[ $current ]['children'] ?? [] as $child_id ) {
			$child_id = (string) $child_id;

			if ( ! \strlen( $child_id ) || isset( $hidden[ $child_id ] ) )
				continue;

			$hidden[ $child_id ] = true;
			$stack[]             = $child_id;
		}
	}

	$parts = [];

	foreach ( $elements as $element ) {
		if ( ! \is_array( $element ) ) continue;

		$id   = (string) ( $element['id'] ?? '' );
		$name = (string) ( $element['name'] ?? '' );

		if ( \strlen( $id ) && isset( $hidden[ $id ] ) ) continue;

		if ( 'section' !== $name && 'text' !== $name ) continue;

		$settings = $element['settings'] ?? [];

		foreach ( [ 'text', 'content' ] as $key ) {
			$html = $settings[ $key ] ?? null;

			if ( ! \is_string( $html ) || ! \strlen( $html ) ) continue;

			$parts[] = $html;
		}
	}

	$text = implode( "\n", $parts );

	if ( ! \strlen( $text ) )
		return $excerpt;

	$text = HTML::extract_content( $text );

	if ( ! \strlen( $text ) )
		return $excerpt;

	if ( ! str_contains( $text, '{' ) )
		return $text;

	return trim( Sanitize::remove_repeated_spacing(
		preg_replace( '/\{[a-zA-Z0-9_]+[^}]*\}/', ' ', $text ) ?? $text,
	) );
}
