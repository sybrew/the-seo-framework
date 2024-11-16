<?php
/**
 * @package The_SEO_Framework\Classes\Meta\Description\Excerpt
 * @subpackage The_SEO_Framework\Meta\Description
 */

namespace The_SEO_Framework\Meta\Description;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\{
	memo,
	normalize_generation_args,
};

use \The_SEO_Framework\{
	Data,
	Helper\Query,
	Helper\Format,
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
 * Holds Excerpt generation for the Description factory.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->description()->excerpt() instead.
 */
class Excerpt {

	/**
	 * Returns a description excerpt.
	 *
	 * @since 5.1.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return string The post, term, pta, or user excerpt.
	 */
	public static function get_excerpt( $args = null ) {
		/**
		 * @since 5.1.0
		 * @param string     $excerpt The generated excerpt.
		 * @param array|null $args    The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
		 *                            Leave null to autodetermine query.
		 * @return string The post, term, pta, or user excerpt.
		 */
		return \apply_filters(
			'the_seo_framework_get_excerpt',
			isset( $args )
				? static::get_excerpt_from_args( $args )
				: static::get_excerpt_from_query(),
			$args,
		);
	}

	/**
	 * Returns a description excerpt.
	 *
	 * @since 5.0.0
	 * @alias
	 * @todo deprecate 5.2: use get_excerpt() instead.
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return string
	 */
	public static function get_post_excerpt( $args = null ) {
		return static::get_excerpt( $args );
	}

	/**
	 * Returns a description excerpt for the current query.
	 *
	 * @since 5.0.0
	 *
	 * @return string
	 */
	public static function get_excerpt_from_query() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo() ) return $memo;

		if ( Query::is_static_front_page() ) {
			$excerpt = static::get_singular_excerpt();
		} elseif ( Query::is_blog_as_page() ) {
			$excerpt = static::get_blog_page_excerpt();
		} elseif ( Query::is_singular() ) {
			$excerpt = static::get_singular_excerpt();
		} elseif ( Query::is_archive() ) {
			$excerpt = static::get_archive_excerpt();
		}

		return memo( $excerpt ?? '' ?: '' );
	}

	/**
	 * Returns a description excerpt for the current query.
	 *
	 * @since 5.0.0
	 *
	 * @param array $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 * @return string
	 */
	public static function get_excerpt_from_args( $args ) {

		normalize_generation_args( $args );

		if ( $args['tax'] ) {
			$excerpt = static::get_archive_excerpt( \get_term( $args['id'], $args['tax'] ) );
		} elseif ( $args['pta'] ) {
			$excerpt = static::get_archive_excerpt( \get_post_type_object( $args['pta'] ) );
		} elseif ( $args['uid'] ) {
			$excerpt = static::get_archive_excerpt( \get_userdata( $args['uid'] ) );
		} elseif ( Query::is_blog_as_page( $args['id'] ) ) {
			$excerpt = static::get_blog_page_excerpt();
		} elseif ( $args['id'] ) {
			$excerpt = static::get_singular_excerpt( $args['id'] );
		}

		return $excerpt ?? '';
	}

	/**
	 * Returns a description excerpt for the blog page.
	 *
	 * @since 5.0.0
	 *
	 * @return string
	 */
	private static function get_blog_page_excerpt() {
		return \sprintf(
			/* translators: %s = Blog page title. Front-end output. */
			\__( 'Latest posts: %s', 'autodescription' ),
			Data\Blog::get_public_blog_name(),
		);
	}

	/**
	 * Returns a description excerpt for archives.
	 *
	 * @since 5.0.0
	 *
	 * @param null|\WP_Term|\WP_Post_Type $object The term or post type object.
	 * @return string
	 */
	private static function get_archive_excerpt( $object = null ) {

		if ( \is_null( $object ) ) {
			$in_the_loop = true;
			$object      = \get_queried_object();
		} else {
			if ( \is_wp_error( $object ) )
				return '';

			$in_the_loop = false;
		}

		/**
		 * @since 3.1.0
		 * @since 5.1.0 Deprecated.
		 * @deprecated
		 * @see `\tsf()->format()->html()->extract_content()` to strip HTML tags neatly.
		 * @param string                 $excerpt The short circuit excerpt.
		 * @param \WP_Term|\WP_Post_Type $object  The Term object or post type object.
		 */
		$excerpt = (string) \apply_filters_deprecated(
			'the_seo_framework_generated_archive_excerpt',
			[ '', $object ],
			'5.1.0 of The SEO Framework',
			'the_seo_framework_get_excerpt',
		);

		if ( $excerpt ) return $excerpt;

		if ( $in_the_loop ) {
			if ( Query::is_category() || Query::is_tag() || Query::is_tax() ) {
				// WordPress DOES NOT allow HTML in term descriptions, not even if you're a super-administrator.
				// See https://wpscan.com/vulnerability/8bc4cf95-79f7-4d92-b320-a841ab7e6a6f/. We won't parse HTML tags unless WordPress adds native support.
				$excerpt = $object->description ?? '';
			} elseif ( Query::is_author() ) {
				$excerpt = Format\HTML::extract_content( \get_the_author_meta(
					'description',
					(int) \get_query_var( 'author' ),
				) );
			} elseif ( \is_post_type_archive() ) {
				/**
				 * @since 4.0.6
				 * @since 4.2.0 Now provides the post type object description, if assigned.
				 * @since 5.1.0 Deprecated.
				 * @deprecated
				 * @param string $excerpt The archive description excerpt.
				 * @param \WP_Term|\WP_Post_Type $object The post type object.
				 */
				$excerpt = (string) \apply_filters_deprecated(
					'the_seo_framework_pta_description_excerpt',
					[
						$object->description ?? '',
						$object,
					],
					'5.1.0 of The SEO Framework',
					'the_seo_framework_get_excerpt',
				);
			} else {
				/**
				 * @since 4.0.6
				 * @since 4.1.0 Added the $object object parameter.
				 * @since 5.1.0 Deprecated.
				 * @deprecated
				 * @param string $excerpt The fallback archive description excerpt.
				 * @param \WP_Term $object    The Term object.
				 */
				$excerpt = (string) \apply_filters_deprecated(
					'the_seo_framework_fallback_archive_description_excerpt',
					[ '', $object ],
					'5.1.0 of The SEO Framework',
					'the_seo_framework_get_excerpt',
				);
			}
		} else {
			$excerpt = $object->description ?? '';
		}

		return $excerpt;
	}

	/**
	 * Returns a description excerpt for singular post types.
	 *
	 * @since 5.0.0
	 * NOTE: Don't add memo; large memory heaps can occur.
	 *       It only runs twice on the post edit screen (post.php).
	 *       Front-end caller get_excerpt_from_query() uses memo.
	 *
	 * @param ?int $id The singular ID. Leave null to get main query.
	 * @return string
	 */
	private static function get_singular_excerpt( $id = null ) {

		$post = \get_post( $id ?? Query::get_the_real_id() );

		// If the post is protected, don't generate a description.
		if ( ! $post || Data\Post::is_protected( $post ) ) return '';

		$excerpt = Data\Post::get_excerpt( $post );

		if ( empty( $excerpt ) && ! Data\Post::uses_non_html_page_builder( $post->ID ) ) {
			// We should actually get the parsed content here... but that can be heavy on the server.
			// We could cache that parsed content, but that'd be asinine for a plugin. WordPress should've done that.
			$excerpt = Data\Post::get_content( $post );

			if ( $excerpt )
				$excerpt = Format\HTML::strip_paragraph_urls( Format\HTML::strip_newline_urls( $excerpt ) );
		}

		if ( empty( $excerpt ) ) return '';

		return Format\HTML::extract_content( $excerpt );
	}
}
