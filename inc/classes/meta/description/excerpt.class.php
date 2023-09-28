<?php
/**
 * @package The_SEO_Framework\Classes\Meta\Description\Excerpt
 * @subpackage The_SEO_Framework\Meta\Description
 */

namespace The_SEO_Framework\Meta\Description;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\{
	memo,
	Utils\normalize_generation_args,
};

use \The_SEO_Framework\Data,
	\The_SEO_Framework\Helper\Query;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * @since 4.3.0
 * @access protected
 * @internal Use tsf()->description()->excerpt() instead.
 */
final class Excerpt {

	/**
	 * Returns a description excerpt.
	 *
	 * @since 4.3.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                         Leave null to autodetermine query.
	 * @return string
	 */
	public static function get_excerpt( $args = null ) {

		if ( null === $args )
			return static::get_excerpt_from_query();

		normalize_generation_args( $args );

		return static::get_excerpt_from_args( $args );
	}

	/**
	 * Returns a description excerpt for the current query.
	 *
	 * @since 3.1.0
	 * @since 4.2.0 Flipped order of query tests.
	 *
	 * @return string
	 */
	public static function get_excerpt_from_query() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo() ) return $memo;

		if ( Query::is_real_front_page() ) {
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
	 * @since 3.1.0
	 * @since 3.2.2 Fixed front-page as blog logic.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 *
	 * @param array $args The query arguments. Accepts 'id', 'tax', and 'pta'.
	 * @return string
	 */
	public static function get_excerpt_from_args( $args ) {

		if ( $args['tax'] ) {
			$excerpt = static::get_archive_excerpt( \get_term( $args['id'], $args['tax'] ) );
		} elseif ( $args['pta'] ) {
			$excerpt = static::get_archive_excerpt( \get_post_type_object( $args['pta'] ) );
		} else {
			if ( Query::is_blog_as_page( $args['id'] ) ) {
				$excerpt = static::get_blog_page_excerpt();
			} else {
				$excerpt = static::get_singular_excerpt( $args['id'] );
			}
		}

		return $excerpt ?? '' ?: '';
	}

	/**
	 * Returns a description excerpt for the blog page.
	 *
	 * @since 4.3.0
	 *
	 * @return string
	 */
	private static function get_blog_page_excerpt() {
		return sprintf(
			/* translators: %s = Blog page title. Front-end output. */
			\__( 'Latest posts: %s', 'autodescription' ),
			\tsf()->get_filtered_raw_generated_title( [ 'id' => (int) \get_option( 'page_for_posts' ) ] )
		);
	}

	/**
	 * Returns a description excerpt for archives.
	 *
	 * @since 4.3.0
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
		 * @see `\tsf()->s_excerpt_raw()` to strip HTML tags neatly.
		 * @param string                 $excerpt The short circuit excerpt.
		 * @param \WP_Term|\WP_Post_Type $object  The Term object or post type object.
		 * @todo deprecate and move to main fetcher.
		 */
		$excerpt = (string) \apply_filters_ref_array(
			'the_seo_framework_generated_archive_excerpt',
			[
				'',
				$object,
			]
		);

		if ( $excerpt ) return $excerpt;

		if ( $in_the_loop ) {
			if ( Query::is_category() || Query::is_tag() || Query::is_tax() ) {
				// WordPress DOES NOT allow HTML in term descriptions, not even if you're a super-administrator.
				// See https://wpvulndb.com/vulnerabilities/9445. We won't parse HTMl tags unless WordPress adds native support.
				$excerpt = \tsf()->s_description_raw( $object->description ?? '' );
			} elseif ( Query::is_author() ) {
				$excerpt = \tsf()->s_excerpt_raw( \get_the_author_meta( 'description', (int) \get_query_var( 'author' ) ) );
			} elseif ( \is_post_type_archive() ) {
				/**
				 * @since 4.0.6
				 * @since 4.2.0 Now provides the post type object description, if assigned.
				 * @param string $excerpt The archive description excerpt.
				 * @param \WP_Term|\WP_Post_Type $object The post type object.
				 * @todo deprecate and move to main fetcher.
				 */
				$excerpt = (string) \apply_filters_ref_array(
					'the_seo_framework_pta_description_excerpt',
					[
						\tsf()->s_description_raw( $object->description ?? '' ),
						$object,
					]
				);
			} else {
				/**
				 * @since 4.0.6
				 * @since 4.1.0 Added the $object object parameter.
				 * @param string $excerpt The fallback archive description excerpt.
				 * @param \WP_Term $object    The Term object.
				 * @todo deprecate and move to main fetcher.
				 */
				$excerpt = (string) \apply_filters_ref_array(
					'the_seo_framework_fallback_archive_description_excerpt',
					[
						'',
						$object,
					]
				);
			}
		} else {
			$excerpt = \tsf()->s_description_raw( $object->description ?? '' );
		}

		return $excerpt;
	}

	/**
	 * Returns a description excerpt for singular post types.
	 *
	 * @since 4.3.0
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
		if ( Data\Post::is_protected( $post ) ) return '';

		if ( ! empty( $post->post_excerpt ) && \post_type_supports( $post->post_type, 'excerpt' ) ) {
			$excerpt = $post->post_excerpt;
		} elseif ( ! Data\Post::uses_non_html_page_builder( $post->ID ) ) {
			// We should actually get the parsed content here... but that can be heavy on the server.
			// We could cache that parsed content, but that'd be asinine for a plugin. WordPress should've done that.
			$excerpt = Data\Post::get_post_content( $post );

			if ( $excerpt )
				$excerpt = \tsf()->strip_paragraph_urls( \tsf()->strip_newline_urls( $excerpt ) );
		}

		return empty( $excerpt ) ? '' : \tsf()->s_excerpt_raw( $excerpt );
	}
}
