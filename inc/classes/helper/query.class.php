<?php
/**
 * @package The_SEO_Framework\Classes\Helper\Query
 * @subpackage The_SEO_Framework\Query
 */

namespace The_SEO_Framework\Helper;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\umemo;

use \The_SEO_Framework\{
	Admin,
	Data,
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
 * Holds a collection of helper methods for the WordPress query.
 * Interprets the WordPress query to eliminate known pitfalls.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->query() instead.
 */
class Query {

	/**
	 * Returns the post type name from query input or real ID.
	 *
	 * @since 4.0.5
	 * @since 4.2.0 Now supports common archives without relying on the first post.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param int|WP_Post|null $post (Optional) Post ID or post object.
	 * @return string|false Post type on success, false on failure.
	 */
	public static function get_post_type_real_id( $post = null ) {

		if ( isset( $post ) )
			return \get_post_type( $post );

		if ( static::is_archive() ) {
			if ( static::is_category() || static::is_tag() || static::is_tax() ) {
				$post_type = Taxonomy::get_post_types();
				$post_type = \is_array( $post_type ) ? reset( $post_type ) : $post_type;
			} elseif ( \is_post_type_archive() ) {
				$post_type = \get_query_var( 'post_type' );
				$post_type = \is_array( $post_type ) ? reset( $post_type ) : $post_type;
			} else {
				// Let WP guess for us. This works reliably (enough) on non-404 queries.
				$post_type = \get_post_type();
			}
		} else {
			$post_type = \get_post_type( static::get_the_real_id() );
		}

		return $post_type;
	}

	/**
	 * Returns the post type name from current screen.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 * @global \WP_Screen $current_screen
	 *
	 * @return string
	 */
	public static function get_admin_post_type() {
		return $GLOBALS['current_screen']->post_type ?? '';
	}

	/**
	 * Get the real page ID, also from CPT, archives, author, blog, etc.
	 * Memoizes the return value.
	 *
	 * Uses `umemo()` instead of `Query\Cache::memo()`, for it is faster.
	 *
	 * @since 2.5.0
	 * @since 3.1.0 No longer checks if we can cache the query when $use_cache is false.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param bool $use_cache Whether to use the cache or not.
	 * @return int|false The ID.
	 */
	public static function get_the_real_id( $use_cache = true ) {

		if ( \is_admin() )
			return static::get_the_real_admin_id();

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( $use_cache && ( null !== $memo = umemo( __METHOD__ ) ) ) return $memo;

		// Try to get ID from plugins or feed when caching is available.
		if ( $use_cache ) {
			/**
			 * @since 2.5.0
			 * @param int $id
			 */
			$id = \apply_filters(
				'the_seo_framework_real_id',
				\is_feed() ? \get_the_id() : 0,
			);
		}

		/**
		 * @since 2.6.2
		 * @param int  $id        Can be either the Post ID, or the Term ID.
		 * @param bool $use_cache Whether this value is stored in runtime caching.
		 */
		$id = (int) \apply_filters(
			'the_seo_framework_current_object_id',
			( $id ?? 0 ) ?: \get_queried_object_id(), // This catches most IDs. Even Post IDs.
			$use_cache,
		);

		// Do not overwrite cache when not requested. Otherwise, we'd have two "initial" states, causing incongruities.
		return $use_cache ? umemo( __METHOD__, $id ) : $id;
	}

	/**
	 * Fetches post or term ID within the admin.
	 *
	 * @since 2.7.0
	 * @since 2.8.0 Removed WP 3.9 compat
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @return int The admin ID.
	 */
	public static function get_the_real_admin_id() {
		/**
		 * @since 2.9.0
		 * @param int $id
		 */
		return (int) \apply_filters(
			'the_seo_framework_current_admin_id',
			// Get in the loop first, fall back to globals or get parameters.
			   \get_the_id()
			?: static::get_admin_post_id()
			?: static::get_admin_term_id()
		);
	}

	/**
	 * Returns the front page ID, if home is a page.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @return int the ID.
	 */
	public static function get_the_front_page_id() {
		return umemo( __METHOD__ )
			?? umemo(
				__METHOD__,
				Query\Utils::has_page_on_front() ? (int) \get_option( 'page_on_front' ) : 0,
			);
	}

	/**
	 * Fetches the Post ID on admin pages.
	 *
	 * @since 5.0.0
	 *
	 * @return int Post ID.
	 */
	public static function get_admin_post_id() {
		return static::is_post_edit()
			// phpcs:ignore, WordPress.Security.NonceVerification -- current_screen validated the 'post' object.
			? \absint( $_GET['post'] ?? $_GET['post_id'] ?? 0 )
			: 0;
	}

	/**
	 * Fetches the Term ID on admin pages.
	 *
	 * @since 2.6.0
	 * @since 2.6.6 Moved from class The_SEO_Framework_Term_Data.
	 * @since 3.1.0 1. Removed WP 4.5 compat. Now uses global $tag_ID.
	 *              2. Removed caching
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 * @global int $tag_ID
	 *
	 * @return int Term ID.
	 */
	public static function get_admin_term_id() {
		return static::is_archive_admin()
			? \absint( $GLOBALS['tag_ID'] ?? 0 )
			: 0;
	}

	/**
	 * Returns the current taxonomy, if any.
	 * Memoizes the return value.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 1. Now works in the admin.
	 *              2. Added memoization.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 * @global \WP_Screen $current_screen
	 *
	 * @return string The queried taxonomy type.
	 */
	public static function get_current_taxonomy() {
		return Query\Cache::memo()
			?? Query\Cache::memo(
				( \is_admin() ? $GLOBALS['current_screen'] : \get_queried_object() )
					->taxonomy ?? '',
			);
	}

	/**
	 * Returns the current post type, if any.
	 * Memoizes the return value.
	 *
	 * @since 4.1.4
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Now falls back to the current post type instead erroneously to a boolean.
	 *              3. Now memoizes the return value.
	 *
	 * @return string The queried post type.
	 */
	public static function get_current_post_type() {
		return Query\Cache::memo()
			?? Query\Cache::memo(
				\is_admin()
					? static::get_admin_post_type()
					: static::get_post_type_real_id()
			);
	}

	/**
	 * Detects attachment page.
	 *
	 * @since 2.6.0
	 * @since 4.0.0 Now reliably works on admin screens.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param mixed $attachment Attachment ID, title, slug, or array of such.
	 * @return bool
	 */
	public static function is_attachment( $attachment = '' ) {

		if ( \is_admin() )
			return static::is_attachment_admin();

		if ( ! $attachment )
			return \is_attachment();

		return Query\Cache::memo( null, $attachment )
			?? Query\Cache::memo( \is_attachment( $attachment ), $attachment );
	}

	/**
	 * Detects attachments within the admin area.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 * @see static::is_attachment()
	 *
	 * @return bool
	 */
	public static function is_attachment_admin() {
		return static::is_singular_admin() && 'attachment' === static::is_singular_admin();
	}

	/**
	 * Determines whether the content type is both singular and archival.
	 * Simply put, it detects a blog page and WooCommerce shop page.
	 *
	 * @since 3.1.0
	 * @since 4.0.5 1. The output is now filterable.
	 *              2. Added caching.
	 *              3. Now has a first parameter `$post`.
	 * @since 4.0.6 Added a short-circuit on current-requests for `is_singular()`.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param int|WP_Post|null $post (Optional) Post ID or post object.
	 * @return bool
	 */
	public static function is_singular_archive( $post = null ) {

		if ( isset( $post ) ) {
			// Keep this an integer, even if 0. Only "null" may tell it's in the loop.
			$id = \is_int( $post )
				? $post
				: ( \get_post( $post )->ID ?? 0 );
		} else {
			$id = null;
		}

		return Query\Cache::memo( null, $id )
			?? Query\Cache::memo(
				/**
				 * @since 4.0.5
				 * @since 4.0.7 The $id can now be null, when no post is given.
				 * @param bool     $is_singular_archive Whether the post ID is a singular archive.
				 * @param int|null $id                  The supplied post ID. Null when in the loop.
				 */
				(bool) \apply_filters(
					'the_seo_framework_is_singular_archive',
					static::is_blog_as_page( $id ),
					$id,
				),
				$id,
			);
	}

	/**
	 * Detects archive pages. Also in admin.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 * @global \WP_Query $wp_query
	 *
	 * @return bool
	 */
	public static function is_archive() {

		if ( \is_admin() )
			return static::is_archive_admin();

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = Query\Cache::memo() ) return $memo;

		if ( \is_archive() && false === static::is_singular() )
			return Query\Cache::memo( true );

		if ( isset( $GLOBALS['wp_query']->query ) && false === static::is_singular() ) {
			global $wp_query;

			if (
				   $wp_query->is_tax
				|| $wp_query->is_category
				|| $wp_query->is_tag
				|| $wp_query->is_post_type_archive
				|| $wp_query->is_author
				|| $wp_query->is_date
			)
				return Query\Cache::memo( true );
		}

		return Query\Cache::memo( false );
	}

	/**
	 * Extends default WordPress is_archive() and determines screen in admin.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 * @global \WP_Screen $current_screen
	 *
	 * @return bool Post Type is archive
	 */
	public static function is_archive_admin() {

		switch ( $GLOBALS['current_screen']->base ?? '' ) {
			case 'edit-tags':
			case 'term':
				return true;
		}

		return false;
	}

	/**
	 * Detects Term edit screen in WP Admin.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 * @global \WP_Screen $current_screen
	 *
	 * @return bool True if on Term Edit screen. False otherwise.
	 */
	public static function is_term_edit() {
		return 'term' === ( $GLOBALS['current_screen']->base ?? '' );
	}

	/**
	 * Detects Post edit screen in WP Admin.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 * @global \WP_Screen $current_screen
	 *
	 * @return bool We're on Post Edit screen.
	 */
	public static function is_post_edit() {
		return 'post' === ( $GLOBALS['current_screen']->base ?? '' );
	}

	/**
	 * Detects Post or Archive Lists in Admin.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 * @global \WP_Screen $current_screen
	 *
	 * @return bool We're on the edit screen.
	 */
	public static function is_wp_lists_edit() {

		switch ( $GLOBALS['current_screen']->base ?? '' ) {
			case 'edit-tags':
			case 'edit':
				return true;
		}

		return false;
	}

	/**
	 * Detects Profile edit screen in WP Admin.
	 *
	 * @since 4.1.4
	 * @since 5.0.0 1. Now also tests network profile edit screens.
	 *              2. Moved from `\The_SEO_Framework\Load`.
	 * @global \WP_Screen $current_screen
	 *
	 * @return bool True if on Profile Edit screen. False otherwise.
	 */
	public static function is_profile_edit() {

		switch ( $GLOBALS['current_screen']->base ?? '' ) {
			case 'profile':
			case 'profile-network':
			case 'user-edit':
			case 'user-edit-network':
				return true;
		}

		return false;
	}

	/**
	 * Detects author archives.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param mixed $author Optional. User ID, nickname, nicename, or array of User IDs, nicknames, and nicenames
	 * @return bool
	 */
	public static function is_author( $author = '' ) {

		if ( ! $author )
			return \is_author();

		return Query\Cache::memo( null, $author )
			?? Query\Cache::memo( \is_author( $author ), $author );
	}

	/**
	 * Detects the blog page.
	 *
	 * @since 2.6.0
	 * @since 4.2.0 Added the first parameter to allow custom query testing.
	 * @since 5.0.0 1. Renamed from `is_home()`.
	 *              2. Moved from `\The_SEO_Framework\Load`.
	 * @since 5.0.3 1. Will no longer validate `0` as a plausible blog page.
	 *              2. Will no longer validate `is_home()` when the blog page is not assigned.
	 *
	 * @param int|WP_Post|null $post Optional. Post ID or post object.
	 *                               Do not supply from WP_Query's main loop-query.
	 * @return bool
	 */
	public static function is_blog( $post = null ) {

		if ( isset( $post ) ) {
			$id = \is_int( $post )
				? ( $post ?: null )
				: ( \get_post( $post )->ID ?? null );

			return ( (int) \get_option( 'page_for_posts' ) ) === $id;
		}

		// If not blog page is assigned, it won't exist. Ignore whatever WP thinks.
		return Query\Utils::has_blog_page() && \is_home();
	}

	/**
	 * Detects the non-front blog page.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 1. Renamed from `is_home_as_page()`.
	 *              2. Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param int|WP_Post|null $post Optional. Post ID or post object.
	 *                               Do not supply from WP_Query's main loop-query.
	 * @return bool
	 */
	public static function is_blog_as_page( $post = null ) {
		// If front is a blog, the blog is never a page.
		return Query\Utils::has_page_on_front() ? static::is_blog( $post ) : false;
	}

	/**
	 * Detects category archives.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param mixed $category Optional. Category ID, name, slug, or array of Category IDs, names, and slugs.
	 * @return bool
	 */
	public static function is_category( $category = '' ) {

		if ( \is_admin() )
			return static::is_category_admin();

		return Query\Cache::memo( null, $category )
			?? Query\Cache::memo( \is_category( $category ), $category );
	}

	/**
	 * Extends default WordPress is_category() and determines screen in admin.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 No longer guesses category by name. It now only matches WordPress's built-in category.
	 * @since 4.0.0 Removed caching.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @return bool Post Type is category
	 */
	public static function is_category_admin() {
		return static::is_archive_admin() && 'category' === static::get_current_taxonomy();
	}

	/**
	 * Determines if the current query handles term metadata.
	 *
	 * @since 5.0.0
	 *
	 * @return bool
	 */
	public static function is_editable_term() {
		return Query\Cache::memo()
			?? Query\Cache::memo(
				Query::is_category() || Query::is_tag() || Query::is_tax()
			);
	}

	/**
	 * Detects front page.
	 *
	 * Adds support for custom "show_on_front" entries.
	 * When the homepage isn't a 'page' (tested via `is_front_page()`) or 'post',
	 * it isn't considered a real front page -- it could be anything custom (Extra by Elegant Themes),
	 * or the `show_on_front` setting is somehow corrupted.
	 *
	 * @since 2.9.0
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @return bool
	 */
	public static function is_real_front_page() {
		return Query\Cache::memo()
			?? Query\Cache::memo(
				\is_front_page()
					?: static::is_blog()
						&& 0 === static::get_the_real_id()
						&& 'post' !== \get_option( 'show_on_front' ) // 'page' is tested via `is_front_page()`
			);
	}

	/**
	 * Checks for front page by input ID without engaging into the query.
	 *
	 * @NOTE This doesn't check for anomalies in the query.
	 * So, don't use this to test user-engaged WordPress queries, ever.
	 * WARNING: This will lead to **FALSE POSITIVES** for Date, CPTA, Search, and other archives.
	 *
	 * @see $this->is_real_front_page(), which solely uses query checking.
	 * @see static::is_static_front_page(), which adds an "is homepage static" check.
	 *
	 * @since 3.2.2
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param int $id The tested ID.
	 * @return bool
	 */
	public static function is_real_front_page_by_id( $id ) {
		return static::get_the_front_page_id() === $id;
	}

	/**
	 * Detects pages.
	 * When $page is supplied, it will check against the current object. So it will not work in the admin screens.
	 *
	 * @since 2.6.0
	 * @since 4.0.0 Now tests for post type, which is more reliable.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 * @api not used internally, polar opposite of is_single().
	 *
	 * @param int|string|array $page Optional. Page ID, title, slug, or array of such. Default empty.
	 * @return bool
	 */
	public static function is_page( $page = '' ) {

		if ( \is_admin() )
			return static::is_page_admin();

		if ( empty( $page ) )
			return \is_page();

		return Query\Cache::memo( null, $page )
			?? Query\Cache::memo(
				\is_int( $page ) || $page instanceof \WP_Post
					? \in_array( \get_post_type( $page ), Post_Type::get_all_hierarchical(), true )
					: \is_page( $page ),
				$page,
			);
	}

	/**
	 * Detects pages within the admin area.
	 *
	 * @since 2.6.0
	 * @since 4.0.0 Now tests for post type, although redundant.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 * @see static::is_page()
	 *
	 * @return bool
	 */
	public static function is_page_admin() {
		return static::is_singular_admin()
			&& \in_array( static::is_singular_admin(), Post_Type::get_all_hierarchical(), true );
	}

	/**
	 * Detects preview, securely.
	 *
	 * @since 2.6.0
	 * @since 4.0.0 This is now deemed a secure method.
	 *              1. Added is_user_logged_in() check.
	 *              2. Added is_singular() check, so get_the_id() won't cross with blog pages.
	 *              3. Added current_user_can() check.
	 *              4. Added wp_verify_nonce() check.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @return bool
	 */
	public static function is_preview() {

		$is_preview = false;

		if (
			   \is_preview()
			&& \is_user_logged_in()
			&& \is_singular()
			&& \current_user_can( 'edit_post', \get_the_id() )
			&& isset( $_GET['preview_id'], $_GET['preview_nonce'] )
			&& \wp_verify_nonce( $_GET['preview_nonce'], 'post_preview_' . (int) $_GET['preview_id'] )
		) {
			$is_preview = true;
		}

		return $is_preview;
	}

	/**
	 * Detects search.
	 *
	 * @since 2.6.0
	 * @since 2.9.4 Now always returns false in admin.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @return bool
	 */
	public static function is_search() {
		return \is_search() && ! \is_admin();
	}

	/**
	 * Detects single post pages.
	 * When $post is supplied, it will check against the current object. So it will not work in the admin screens.
	 *
	 * @since 2.6.0
	 * @since 4.0.0 Now tests for post type, which is more reliable.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param int|string|array $post Optional. Post ID, title, slug, or array of such. Default empty.
	 * @return bool
	 */
	public static function is_single( $post = '' ) {

		if ( \is_admin() )
			return static::is_single_admin();

		return Query\Cache::memo( null, $post )
			?? Query\Cache::memo(
				\is_int( $post ) || $post instanceof \WP_Post
					? \in_array( \get_post_type( $post ), Post_Type::get_all_nonhierarchical(), true )
					: \is_single( $post ),
				$post,
			);
	}

	/**
	 * Detects posts within the admin area.
	 *
	 * @since 2.6.0
	 * @since 4.0.0 Now no longer returns true on categories and tags.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 * @see The_SEO_Framework_Query::is_single()
	 *
	 * @return bool
	 */
	public static function is_single_admin() {
		// Checks for "is_singular_admin()" because the post type is non-hierarchical.
		return static::is_singular_admin()
			&& \in_array( static::is_singular_admin(), Post_Type::get_all_nonhierarchical(), true );
	}

	/**
	 * Determines if the current page is singular is holds singular items within the admin screen.
	 * Replaces and expands default WordPress `is_singular()`.
	 *
	 * @since 2.5.2
	 * @since 3.1.0 Now passes $post_types parameter in admin screens, only when it's an integer.
	 * @since 4.0.0 No longer processes integers as input.
	 * @since 4.2.4 No longer tests type of $post_types.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param string|string[] $post_types Optional. Post type or array of post types. Default empty string.
	 * @return bool Post Type is singular
	 */
	public static function is_singular( $post_types = '' ) {

		// WP_Query functions require loop, do alternative check.
		if ( \is_admin() )
			return static::is_singular_admin();

		if ( $post_types )
			return \is_singular( $post_types );

		return Query\Cache::memo()
			?? Query\Cache::memo( \is_singular() || static::is_singular_archive() );
	}

	/**
	 * Determines if the page is singular within the admin screen.
	 *
	 * @since 2.5.2
	 * @since 3.1.0 Added $post_id parameter. When used, it'll only check for it.
	 * @since 4.0.0 Removed first parameter.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 * @global \WP_Screen $current_screen
	 *
	 * @return bool Post Type is singular
	 */
	public static function is_singular_admin() {

		switch ( $GLOBALS['current_screen']->base ?? '' ) {
			case 'edit':
			case 'post':
				return true;
		}

		return false;
	}

	/**
	 * Detects the static front page.
	 *
	 * @since 5.0.0
	 *
	 * @param int $id the Page ID to check. If empty, the current ID will be fetched.
	 * @return bool True when homepage is static and given/current ID matches.
	 */
	public static function is_static_front_page( $id = 0 ) {

		// Memo this slow part separately; Query\Cache::memo() isn't fast enough.
		$front_id = umemo( __METHOD__ )
			?? umemo(
				__METHOD__,
				Query\Utils::has_assigned_page_on_front()
					? (int) \get_option( 'page_on_front' )
					: false,
			);

		return false !== $front_id && ( $id ?: static::get_the_real_id() ) === $front_id;
	}

	/**
	 * Detects tag archives.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param mixed $tag Optional. Tag ID, name, slug, or array of Tag IDs, names, and slugs.
	 * @return bool
	 */
	public static function is_tag( $tag = '' ) {

		// Admin requires another check.
		if ( \is_admin() )
			return static::is_tag_admin();

		return Query\Cache::memo( null, $tag )
			?? Query\Cache::memo( \is_tag( $tag ), $tag );
	}

	/**
	 * Determines if the page is a tag within the admin screen.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 No longer guesses tag by name. It now only matches WordPress's built-in tag.
	 * @since 4.0.0 Removed caching.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @return bool Post Type is tag.
	 */
	public static function is_tag_admin() {
		return static::is_archive_admin() && 'post_tag' === static::get_current_taxonomy();
	}

	/**
	 * Detects taxonomy archives.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 * @TODO add is_tax_admin() ?
	 *
	 * @param string|array     $taxonomy Optional. Taxonomy slug or slugs.
	 * @param int|string|array $term     Optional. Term ID, name, slug or array of Term IDs, names, and slugs.
	 * @return bool
	 */
	public static function is_tax( $taxonomy = '', $term = '' ) {
		return Query\Cache::memo( null, $taxonomy, $term )
			?? Query\Cache::memo( \is_tax( $taxonomy, $term ), $taxonomy, $term );
	}

	/**
	 * Determines if the $post is a shop page.
	 *
	 * @since 4.0.5
	 * @since 4.1.4 Added memoization.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param int|WP_Post|null $post (Optional) Post ID or post object.
	 * @return bool
	 */
	public static function is_shop( $post = null ) {
		return Query\Cache::memo( null, $post )
			?? Query\Cache::memo(
				/**
				 * @since 4.0.5
				 * @since 4.1.4 Now has its return value memoized.
				 * @param bool $is_shop Whether the post ID is a shop.
				 * @param int  $id      The current or supplied post ID.
				 */
				(bool) \apply_filters( 'the_seo_framework_is_shop', false, $post ),
				$post,
			);
	}

	/**
	 * Determines if the page is a product page.
	 *
	 * @since 4.0.5
	 * @since 4.1.4 Added memoization.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param int|WP_Post|null $post (Optional) Post ID or post object.
	 * @return bool True if on a WooCommerce Product page.
	 */
	public static function is_product( $post = null ) {

		if ( \is_admin() )
			return static::is_product_admin();

		return Query\Cache::memo( null, $post )
			?? Query\Cache::memo(
				/**
				 * @since 4.0.5
				 * @since 4.1.4 Now has its return value memoized.
				 * @param bool $is_product
				 * @param int|WP_Post|null $post (Optional) Post ID or post object.
				 */
				(bool) \apply_filters( 'the_seo_framework_is_product', false, $post ),
				$post,
			);
	}

	/**
	 * Determines if the admin page is for a product page.
	 *
	 * @since 4.0.5
	 * @since 4.1.4 Added memoization.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @return bool
	 */
	public static function is_product_admin() {
		return Query\Cache::memo()
			?? Query\Cache::memo(
				/**
				 * @since 4.0.5
				 * @since 4.1.4 Now has its return value memoized.
				 * @param bool $is_product_admin
				 */
				(bool) \apply_filters( 'the_seo_framework_is_product_admin', false )
			);
	}

	/**
	 * Determines if SSL is used.
	 * Memoizes the return value.
	 *
	 * @since 2.8.0
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @return bool True if SSL, false otherwise.
	 */
	public static function is_ssl() {
		return umemo( __METHOD__ )
			?? umemo( __METHOD__, \is_ssl() );
	}

	/**
	 * Determines whether we're on the SEO settings page.
	 * WARNING: Do not ever use this as a safety check.
	 *
	 * @since 2.6.0
	 * @since 2.7.0 Added secure parameter.
	 * @since 2.9.0 If $secure is false, the cache is no longer used.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 * @see static::is_menu_page() for security notification.
	 *
	 * @param bool $secure Whether to ignore the use of the second (insecure) parameter.
	 * @return bool
	 */
	public static function is_seo_settings_page( $secure = true ) {

		if ( ! \is_admin() )
			return false;

		if ( ! $secure )
			return static::is_menu_page( '', \THE_SEO_FRAMEWORK_SITE_OPTIONS_SLUG );

		return Query\Cache::memo()
			?? Query\Cache::memo( static::is_menu_page( Admin\Menu::get_page_hook_name() ) );
	}

	/**
	 * Checks the screen base file through global $page_hook or $_GET.
	 *
	 * NOTE: Usage of $pageslug might be insecure. Check all variables and don't
	 * perform lasting actions like saving to the database before `admin_init`!
	 *
	 * The second "insecure" parameter is actually secured by WordPress (read on...).
	 * However, we can't verify its integrity, WordPress has to. It's also checked
	 * against too late.
	 * It's secure enough for loading files; nevertheless, it shouldn't be used
	 * when passing sensitive data.
	 *
	 * @since 2.2.2
	 * @since 2.7.0 Added pageslug parameter.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 * @global string $page_hook the current page hook.
	 *
	 * @param string $pagehook The menu pagehook to compare to.
	 *               To be used after `admin_init`.
	 * @param string $pageslug The menu page slug to compare to.
	 *               To be used before `admin_init`.
	 * @return bool true if screen match.
	 */
	public static function is_menu_page( $pagehook = '', $pageslug = '' ) {
		global $page_hook;

		if ( isset( $page_hook ) ) {
			return $page_hook === $pagehook;
		} elseif ( \is_admin() && $pageslug ) {
			// N.B. $_GET['page'] === $plugin_page after admin_init...
			// phpcs:ignore, WordPress.Security.NonceVerification -- This is a public variable, no data is processed.
			return ( $_GET['page'] ?? '' ) === $pageslug;
		}

		return false;
	}

	/**
	 * Returns the current page number.
	 * Fetches global `$page` from `WP_Query` to prevent conflicts.
	 *
	 * @since 2.6.0
	 * @since 3.2.4 1. Added overflow protection.
	 *              2. Now always returns 1 on the admin screens.
	 * @since 4.2.8 Now returns the last page on pagination overflow,
	 *              but only when we're on a paginated static frontpage.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @return int (R>0) $page Always a positive number.
	 */
	public static function page() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition
		if ( null !== $memo = Query\Cache::memo() )
			return $memo;

		if ( static::is_multipage() ) {
			$page = ( (int) \get_query_var( 'page' ) ) ?: 1;
			$max  = static::numpages();

			if ( $page > $max ) {
				// On overflow, WP returns the first page.
				// Exception: When we are on a paginated static frontpage, WP returns the last page...
				if ( static::is_static_front_page() ) {
					$page = $max;
				} else {
					$page = 1;
				}
			}
		} else {
			$page = 1;
		}

		return Query\Cache::memo( $page );
	}

	/**
	 * Returns the current page number.
	 * Fetches global `$paged` from `WP_Query` to prevent conflicts.
	 *
	 * @since 2.6.0
	 * @since 3.2.4 1. Added overflow protection.
	 *              2. Now always returns 1 on the admin screens.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @return int (R>0) $paged Always a positive number.
	 */
	public static function paged() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition
		if ( null !== $memo = Query\Cache::memo() )
			return $memo;

		if ( static::is_multipage() ) {
			$paged = ( (int) \get_query_var( 'paged' ) ) ?: 1;
			$max   = static::numpages();

			if ( $paged > $max ) {
				// On overflow, WP returns the last page.
				$paged = $max;
			}
		} else {
			$paged = 1;
		}

		return Query\Cache::memo( $paged );
	}

	/**
	 * Determines the number of available pages.
	 *
	 * This is largely taken from \WP_Query::setup_postdata(), however, the data
	 * we need is set up in the loop, not in the header; where TSF is active.
	 *
	 * @since 3.1.0
	 * @since 3.2.4 Now only returns "1" in the admin.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 * @global \WP_Query $wp_query
	 *
	 * @return int
	 */
	public static function numpages() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition
		if ( null !== $memo = Query\Cache::memo() )
			return $memo;

		if ( \is_admin() ) {
			// Disable pagination detection in admin: Always on page 1.
			return Query\Cache::memo( 1 );
		}

		global $wp_query;

		if ( static::is_singular() && ! static::is_singular_archive() )
			$post = \get_post( static::get_the_real_id() );

		if ( ( $post ?? null ) instanceof \WP_Post ) {
			$content = Data\Post::get_content( $post );

			if ( str_contains( $content, '<!--nextpage-->' ) ) {
				$content = str_replace( "\n<!--nextpage-->", '<!--nextpage-->', $content );

				// Ignore nextpage at the beginning of the content.
				if ( str_starts_with( $content, '<!--nextpage-->' ) )
					$content = substr( $content, 15 );

				$pages = explode( '<!--nextpage-->', $content );
			} else {
				$pages = [ $content ];
			}

			/**
			 * Filter the "pages" derived from splitting the post content.
			 *
			 * "Pages" are determined by splitting the post content based on the presence
			 * of `<!-- nextpage -->` tags.
			 *
			 * @since 4.4.0 WordPress core
			 *
			 * @param array    $pages Array of "pages" derived from the post content.
			 *                 of `<!-- nextpage -->` tags..
			 * @param \WP_Post $post  Current post object.
			 */
			$pages = \apply_filters( 'content_pagination', $pages, $post );

			$numpages = \count( $pages );
		} elseif ( isset( $wp_query->max_num_pages ) ) {
			$numpages = (int) $wp_query->max_num_pages;
		} else {
			// Empty or faulty query, bail.
			$numpages = 0;
		}

		return Query\Cache::memo( $numpages );
	}

	/**
	 * Determines whether the current loop has multiple pages.
	 *
	 * @since 2.7.0
	 * @since 3.1.0 1. Now also works on archives.
	 *              2. Now is public.
	 * @since 3.2.4 Now always returns false on the admin pages.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @return bool True if multipage.
	 */
	public static function is_multipage() {
		return static::numpages() > 1;
	}

	/**
	 * Detects paginated comment pages thoroughly.
	 *
	 * WordPress 6.0 introduced a last minute function called `build_comment_query_vars_from_block()`.
	 * This function exists to workaround a bug in comment blocks as sub-query by adjusting the main query.
	 *
	 * WordPress 6.7 fixed it, but we keep it in place because other themes and plugins might still mess up the query.
	 * This ensures we're always "right" about the canonical URL.
	 *
	 * @since 5.0.6
	 * @link <https://core.trac.wordpress.org/ticket/60806>
	 *
	 * @return bool
	 */
	public static function is_comment_paged() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition
		if ( null !== $memo = Query\Cache::memo() )
			return $memo;

		/**
		 * N.B. WordPress protects this query variable with options 'page_comments'
		 * and 'default_comments_page' via `redirect_canonical()`, so we don't have to.
		 * For reference, it fires `remove_query_arg( 'cpage', $redirect['query'] )`;
		 */
		$is_cpaged = (int) \get_query_var( 'cpage', 0 ) > 0;

		/**
		 * Let's scrutinize if $cpage might be incorrectly set.
		 *
		 * WP 6.0 bugged this. Any of these blocks can invoke `set_query_var( 'cpage', 1+ )`.
		 * 'core/comment-template',            // parent core/comments
		 * 'core/comments-pagination-next',    // parent core/comments-pagination, parent core/comments
		 * 'core/comments-pagination-numbers', // parent core/comments-pagination, parent core/comments
		 * 'core/comments-pagination-previous' doesn't invoke this; yet to be determined why.
		 *
		 * These functions can too invoke `set_query_var`; but Core doesn't mess this up:
		 * 'comments_template()'
		 * 'wp_list_comments()'  // But only after comments_template()
		 *
		 * If comments haven't yet been parsed, we can safely assume there's no bug active.
		 * Hence, we test for did_action(). We want the main query, not the tainted one.
		 * So, even if this runs in the footer, we should still scrutinize it.
		 */
		if ( $is_cpaged && \did_action( 'parse_comment_query' ) ) {
			// core/comments only works on singular; this bug doesn't invoke otherwise anyway.
			if ( ! static::is_singular() )
				return Query\Cache::memo( false );

			/**
			 * Assume 0 if the unaltered query variable isn't found;
			 * it might be purged, so we won't have pagination.
			 * There is no other fast+reliable method to determine whether
			 * comment pagination is engaged for the current query.
			 * This is a bypass, after all.
			 */
			$is_cpaged = (int) ( $GLOBALS['wp_query']->query['cpage'] ?? 0 ) > 0;
		}

		return Query\Cache::memo( $is_cpaged );
	}

	/**
	 * Determines whether we're on The SEO Framework's sitemap or not.
	 * Memoizes the return value once set.
	 *
	 * @since 2.9.2
	 * @since 4.0.0 Now memoizes instead of populating class properties.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param bool $set Whether to set "doing sitemap".
	 * @return bool
	 */
	public static function is_sitemap( $set = false ) {
		return umemo( __METHOD__, $set ?: null ) ?? false;
	}

	/**
	 * Returns the post author ID.
	 * Memoizes the return value for the current request.
	 *
	 * @since 3.0.0
	 * @since 3.2.2 1. Now no longer returns the latest post author ID on home-as-blog pages.
	 *              2. Now always returns an integer.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param int $post_id The post ID to fetch the author from. Leave 0 to autodetermine.
	 * @return int Post author ID on success, 0 on failure.
	 */
	public static function get_post_author_id( $post_id = 0 ) {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition
		if ( null !== $memo = Query\Cache::memo( null, $post_id ) )
			return $memo;

		if ( $post_id || Query::is_singular() ) {
			$post = \get_post( $post_id ?: Query::get_the_real_id() );

			$author_id = isset( $post->post_author ) && \post_type_supports( $post->post_type, 'author' )
				? $post->post_author
				: 0;
		}

		return Query\Cache::memo( $author_id ?? 0, $post_id );
	}

	/**
	 * Sets up user ID and returns it if user is found.
	 * To be used in AJAX, back-end and front-end.
	 *
	 * @since 2.7.0
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @return int The user ID. 0 if user is not found.
	 */
	public static function get_current_user_id() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition
		if ( null !== $memo = Query\Cache::memo() )
			return $memo;

		$user = \wp_get_current_user();

		return Query\Cache::memo( $user->exists() ? (int) $user->ID : 0 );
	}

	/**
	 * Detects if we're on a Gutenberg page.
	 *
	 * @since 3.1.0
	 * @since 3.2.0 1. Now detects the WP 5.0 block editor.
	 *              2. Method is now public.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `is_gutenberg_page`.
	 *              3. Now reads the current screen value.
	 *
	 * @return bool True if we're viewing the block editor (aka Gutenberg).
	 */
	public static function is_block_editor() {
		return $GLOBALS['current_screen']->is_block_editor ?? false;
	}
}
