<?php
/**
 * @package The_SEO_Framework\Classes\Data\Post
 * @subpackage The_SEO_Framework\Data
 */

namespace The_SEO_Framework\Data;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\memo;

use \The_SEO_Framework\Helper\Query;

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
 * Holds a collection of data helper methods for a post.
 *
 * @since 4.3.0
 * @access protected
 * @internal
 * @internal Use tsf()->data()->post() instead.
 */
class Post {

	/**
	 * Fetches Post content.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 No longer applies WordPress's default filters.
	 * @since 4.2.8 Now tests for post type support of 'editor' before parsing the content.
	 * @since 4.3.0 Moved to \The_SEO_Framework\Data\Post.
	 *
	 * @param \WP_Post|int $post The Post or Post ID.
	 * @return string The post content.
	 */
	public static function get_post_content( $post = null ) {

		$post = \get_post( $post ?: Query::get_the_real_id() );

		// '0' is not deemed content. Return empty string for it's a slippery slope.
		return ! empty( $post->post_content ) && \post_type_supports( $post->post_type, 'editor' )
			? $post->post_content
			: '';
	}

	/**
	 * Determines whether the post has a page builder that renders content dynamically attached to it.
	 * Doesn't use plugin detection features as some builders might be incorporated within themes.
	 *
	 * Detects the following builders:
	 * - Divi Builder by Elegant Themes
	 * - Visual Composer by WPBakery
	 *
	 * @since 4.1.0
	 * @since 4.3.0 1. First parameter may now be empty to automatically fetch the post ID.
	 *              2. Moved to \The_SEO_Framework\Data\Post.
	 *
	 * @param int $post_id The post ID to check.
	 * @return bool
	 */
	public static function uses_non_html_page_builder( $post_id = 0 ) {

		$post_id = $post_id ?: Query::get_the_real_id();
		$meta    = \get_post_meta( $post_id );

		/**
		 * @since 4.1.0
		 * @param boolean|null $detected Whether a builder should be detected.
		 * @param int          $post_id The current Post ID.
		 * @param array        $meta The current post meta.
		 */
		$detected = \apply_filters( 'the_seo_framework_detect_non_html_page_builder', null, $post_id, $meta );

		if ( \is_bool( $detected ) )
			return $detected;

		// If there's no meta, or no builder active, it doesn't use a builder.
		if ( empty( $meta ) || ! \tsf()->detect_non_html_page_builder() )
			return false;

		// Divi Builder by Elegant Themes
		// || Visual Composer by WPBakery
		return ( 'on' === ( $meta['_et_pb_use_builder'][0] ?? '' ) && \defined( 'ET_BUILDER_VERSION' ) )
			|| ( 'true' === ( $meta['_wpb_vc_js_status'][0] ?? '' ) && \defined( 'WPB_VC_VERSION' ) );
	}

	/**
	 * Determines if the current post is protected or private.
	 * Only works on singular pages.
	 *
	 * @since 2.8.0
	 * @since 3.0.0 1. No longer checks for current query.
	 *              2. Input parameter now default to null.
	 *                 This currently doesn't affect how it works.
	 * @since 4.2.0 Added caching. Can be reversed if https://core.trac.wordpress.org/ticket/50567 is fixed.
	 * @since 4.3.0 Moved to \The_SEO_Framework\Data\Post.
	 *
	 * @param int|null|\WP_Post $post The post ID or WP Post object.
	 * @return bool True if protected or private, false otherwise.
	 */
	public static function is_protected( $post = null ) {

		// This is here so we don't have to create another instance hereinafter.
		$post = \get_post( $post );

		return static::is_password_protected( $post ) || static::is_private( $post );
	}

	/**
	 * Determines if the current post has a password.
	 *
	 * @since 3.0.0
	 * @since 4.3.0 Moved to \The_SEO_Framework\Data\Post.
	 *
	 * @param int|null|\WP_Post $post The post ID or WP Post object.
	 * @return bool True if protected, false otherwise.
	 */
	public static function is_password_protected( $post = null ) {
		// Don't get the post directly if it can be evaded, it's still quite slow.
		return '' !== ( $post->post_password ?? \get_post( $post )->post_password ?? '' );
	}

	/**
	 * Determines if the current post is private.
	 *
	 * @since 3.0.0
	 * @since 4.3.0 Moved to \The_SEO_Framework\Data\Post.
	 *
	 * @param int|null|\WP_Post $post The post ID or WP Post object.
	 * @return bool True if private, false otherwise.
	 */
	public static function is_private( $post = null ) {
		// Don't get the post directly if it can be evaded, it's still quite slow.
		return 'private' === ( $post->post_status ?? \get_post( $post )->post_status ?? '' );
	}

	/**
	 * Determines if the current post is a draft.
	 *
	 * @since 3.1.0
	 * @since 4.3.0 Moved to \The_SEO_Framework\Data\Post.
	 *
	 * @param int|null|\WP_Post $post The post ID or WP Post object.
	 * @return bool True if draft, false otherwise.
	 */
	public static function is_draft( $post = null ) {

		// Don't get the post directly if it can be evaded, it's still quite slow.
		switch ( $post->post_status ?? \get_post( $post )->post_status ?? '' ) {
			case 'draft':
			case 'auto-draft':
			case 'pending':
				return true;
		}

		return false;
	}

	/**
	 * Fetch latest public post/page ID.
	 * Memoizes the return value.
	 *
	 * @since 2.4.3
	 * @since 2.9.3 1. Removed object caching.
	 *              2. It now uses WP_Query, instead of wpdb.
	 * @since 4.3.0 Moved to \The_SEO_Framework\Data\Post.
	 * @slow The queried result is not stored in WP Post's cache, which would allow
	 *       direct access to all values of the post (if requested). This is because
	 *       we're using `'fields' => 'ids'` instead of `'fields' => 'all'`.
	 *
	 * @return int Latest Post ID.
	 */
	public static function get_latest_post_id() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo() ) return $memo;

		$query = new \WP_Query( [
			'posts_per_page'   => 1,
			'post_type'        => [ 'post', 'page' ],
			'orderby'          => 'date',
			'order'            => 'DESC',
			'post_status'      => [ 'publish', 'future', 'pending' ],
			'fields'           => 'ids',
			'cache_results'    => false,
			'suppress_filters' => true,
			'no_found_rows'    => true,
		] );

		return memo( reset( $query->posts ) );
	}

	/**
	 * Returns the post modified time (GMT), formatted according to site settings.
	 *
	 * @since 4.3.0
	 *
	 * @param ?int $id The post ID. Leave null to autodetermine.
	 * @return string The post published time according to settings.
	 */
	public static function get_post_published_time( $id = null ) {

		$post_date_gmt = \get_post( $id ?? Query::get_the_real_id() )
			->post_date_gmt ?? '0000-00-00 00:00:00';

		if ( '0000-00-00 00:00:00' === $post_date_gmt )
			return '';

		return \tsf()->gmt2date( \tsf()->get_timestamp_format(), $post_date_gmt );
	}

	/**
	 * Returns the post modified time (GMT), formatted according to site settings.
	 *
	 * @since 4.3.0
	 *
	 * @param ?int $id The post ID. Leave null to autodetermine.
	 * @return string The post modified time according to settings.
	 */
	public static function get_post_modified_time( $id = null ) {

		$post_modified_gmt = \get_post( $id ?? Query::get_the_real_id() )
			->post_modified_gmt ?? '0000-00-00 00:00:00';

		if ( '0000-00-00 00:00:00' === $post_modified_gmt )
			return '';

		return \tsf()->gmt2date( \tsf()->get_timestamp_format(), $post_modified_gmt );
	}
}
