<?php
/**
 * @package The_SEO_Framework\Classes\Meta
 * @subpackage The_SEO_Framework\Meta\Description
 */

namespace The_SEO_Framework\Meta;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function The_SEO_Framework\{
	coalesce_strlen,
	get_query_type_from_args,
	memo,
	normalize_generation_args,
};

use The_SEO_Framework\{
	Data,
	Data\Filter\Sanitize,
	Meta,
};
use The_SEO_Framework\Helper\{
	Guidelines,
	Query,
	Format\Strings,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Holds getters for meta tag output.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->description() instead.
 */
class Description {

	/**
	 * Returns the meta description from custom fields. Falls back to autogenerated description.
	 *
	 * @since 5.0.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return string The real description output.
	 */
	public static function get_description( $args = null ) {
		return coalesce_strlen( static::get_custom_description( $args ) )
			?? static::get_generated_description( $args );
	}

	/**
	 * Returns the custom user-inputted description.
	 *
	 * @since 5.0.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return string The custom field description.
	 */
	public static function get_custom_description( $args = null ) {

		if ( isset( $args ) ) {
			normalize_generation_args( $args );
			$desc = static::get_custom_description_from_args( $args );
		} else {
			$desc = static::get_custom_description_from_query();
		}

		/**
		 * @since 2.9.0
		 * @since 4.2.0 1. No longer gets supplied custom query arguments when in the loop.
		 *              2. Now supports the `$args['pta']` index.
		 * @param string     $desc The custom-field description.
		 * @param array|null $args The query arguments. Contains 'id', 'tax', 'pta', and 'uid'.
		 *                         Is null when the query is auto-determined.
		 */
		return Sanitize::metadata_content( \apply_filters(
			'the_seo_framework_custom_field_description',
			$desc,
			$args,
		) );
	}

	/**
	 * Returns the autogenerated meta description.
	 *
	 * @since 3.0.6
	 * @since 3.1.0 1. The first argument now accepts an array, with "id" and "taxonomy" fields.
	 *              2. No longer caches.
	 *              3. Now listens to option.
	 *              4. Added type argument.
	 * @since 3.1.2 1. Now omits additions when the description will be deemed too short.
	 *              2. Now no longer converts additions into excerpt when no excerpt is found.
	 * @since 3.2.2 Now converts HTML characters prior trimming.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Removed the second `$escape` parameter.
	 *              3. Moved the third parameter to the second.
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @param string     $type Type of description. Accepts 'search', 'opengraph', 'twitter'.
	 * @return string The generated description output.
	 */
	public static function get_generated_description( $args = null, $type = 'search' ) {

		if ( ! static::may_generate( $args ) ) return '';

		switch ( $type ) {
			case 'opengraph':
			case 'twitter':
			case 'search':
				break;
			default:
				$type = 'search';
		}

		isset( $args ) and normalize_generation_args( $args );

		// phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo( null, $args, $type ) ) return $memo;

		/**
		 * @since 2.9.0
		 * @since 3.1.0 No longer passes 3rd and 4th parameter.
		 * @since 4.0.0 1. Deprecated second parameter.
		 *              2. Added third parameter: $args.
		 * @since 4.2.0 Now supports the `$args['pta']` index.
		 * @since 5.0.0 Deprecated.
		 * @deprecated
		 * @param string     $excerpt The excerpt to use.
		 * @param int        $page_id Deprecated.
		 * @param array|null $args The query arguments. Contains 'id', 'tax', 'pta', and 'uid'.
		 *                         Is null when the query is auto-determined.
		 */
		$excerpt = (string) \apply_filters_deprecated(
			'the_seo_framework_fetched_description_excerpt',
			[
				Description\Excerpt::get_excerpt( $args ),
				0,
				$args,
			],
			'5.0.0 of The SEO Framework',
			'the_seo_framework_description_excerpt',
		);

		/**
		 * @since 5.0.0
		 * @param string     $excerpt The excerpt to use.
		 * @param array|null $args    The query arguments. Contains 'id', 'tax', 'pta', and 'uid'.
		 *                            Is null when the query is auto-determined.
		 * @param string     $type    Type of description. Accepts 'search', 'opengraph', 'twitter'.
		 */
		$excerpt = (string) \apply_filters(
			'the_seo_framework_description_excerpt',
			$excerpt,
			$args,
			$type,
		);

		// This page has a generated description that's far too short: https://theseoframework.com/em-changelog/1-0-0-amplified-seo/.
		// A direct directory-'site:' query will accept the description outputted--anything else will ignore it...
		// We should not work around that, because it won't direct in the slightest what to display.
		$desc = Strings::clamp_sentence(
			$excerpt,
			1,
			Guidelines::get_text_size_guidelines()['description'][ $type ]['chars']['goodUpper'],
		);

		/**
		 * @since 2.9.0
		 * @since 3.1.0 No longer passes 3rd and 4th parameter.
		 * @since 4.2.0 Now supports the `$args['pta']` index.
		 * @since 5.0.0 Added third parameter `$type`.
		 * @param string     $desc The generated description.
		 * @param array|null $args The query arguments. Contains 'id', 'tax', 'pta', and 'uid'.
		 *                         Is null when the query is auto-determined.
		 * @param string     $type Type of description. Accepts 'search', 'opengraph', 'twitter'.
		 */
		$desc = (string) \apply_filters(
			'the_seo_framework_generated_description',
			$desc,
			$args,
			$type,
		);

		return memo(
			\strlen( $desc ) ? Sanitize::metadata_content( $desc ) : '',
			$args,
			$type,
		);
	}

	/**
	 * Gets a custom description, based on expected or current query, without escaping.
	 *
	 * @since 5.0.0
	 * @see static::get_custom_description()
	 *
	 * @return string The custom description.
	 */
	public static function get_custom_description_from_query() {

		if ( Query::is_real_front_page() ) {
			if ( Query::is_static_front_page() ) {
				$desc = coalesce_strlen( Data\Plugin::get_option( 'homepage_description' ) )
					 ?? Data\Plugin\Post::get_meta_item( '_genesis_description' );
			} else {
				$desc = Data\Plugin::get_option( 'homepage_description' );
			}
		} elseif ( Query::is_singular() ) {
			$desc = Data\Plugin\Post::get_meta_item( '_genesis_description' );
		} elseif ( Query::is_editable_term() ) {
			$desc = Data\Plugin\Term::get_meta_item( 'description' );
		} elseif ( \is_post_type_archive() ) {
			$desc = Data\Plugin\PTA::get_meta_item( 'description' );
		}

		if ( isset( $desc ) && \strlen( $desc ) )
			return Sanitize::metadata_content( $desc );

		return '';
	}

	/**
	 * Gets a custom description, based on input arguments query, without escaping.
	 *
	 * @since 3.1.0
	 * @since 3.2.2 Now tests for the static frontpage metadata prior getting fallback data.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 5.0.0 1. Now expects an ID before getting a post meta item.
	 *              2. Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param array $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 * @return string The custom description.
	 */
	public static function get_custom_description_from_args( $args ) {

		normalize_generation_args( $args );

		switch ( get_query_type_from_args( $args ) ) {
			case 'single':
				if ( Query::is_static_front_page( $args['id'] ) ) {
					$desc = coalesce_strlen( Data\Plugin::get_option( 'homepage_description' ) )
						 ?? Data\Plugin\Post::get_meta_item( '_genesis_description', $args['id'] );
				} else {
					$desc = Data\Plugin\Post::get_meta_item( '_genesis_description', $args['id'] );
				}
				break;
			case 'term':
				$desc = Data\Plugin\Term::get_meta_item( 'description', $args['id'] );
				break;
			case 'homeblog':
				$desc = Data\Plugin::get_option( 'homepage_description' );
				break;
			case 'pta':
				$desc = Data\Plugin\PTA::get_meta_item( 'description', $args['pta'] );
		}

		if ( isset( $desc ) && \strlen( $desc ) )
			return Sanitize::metadata_content( $desc );

		return '';
	}

	/**
	 * Determines whether automated descriptions are enabled.
	 *
	 * @since 5.0.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return bool
	 */
	public static function may_generate( $args = null ) {

		isset( $args ) and normalize_generation_args( $args );

		/**
		 * @since 2.5.0
		 * @since 3.0.0 Now passes $args as the second parameter.
		 * @since 3.1.0 Now listens to option.
		 * @since 4.2.0 Now supports the `$args['pta']` index.
		 * @param bool       $autodescription Enable or disable the automated descriptions.
		 * @param array|null $args            The query arguments. Contains 'id', 'tax', 'pta', and 'uid'.
		 *                                    Is null when the query is auto-determined.
		 */
		return (bool) \apply_filters(
			'the_seo_framework_enable_auto_description',
			Data\Plugin::get_option( 'auto_description' ),
			$args,
		);
	}
}
