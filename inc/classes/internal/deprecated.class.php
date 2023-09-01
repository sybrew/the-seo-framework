<?php
/**
 * @package The_SEO_Framework\Classes\Internal\Deprecated
 * @subpackage The_SEO_Framework\Debug\Deprecated
 */

namespace The_SEO_Framework\Internal;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\{
	memo,   // Precautionary.
	umemo,  // Precautionary.
};

/**
 * Class The_SEO_Framework\Internal\Deprecated
 *
 * Contains all deprecated functions.
 *
 * @since 2.8.0
 * @since 3.1.0 Removed all methods deprecated in 3.0.0.
 * @since 4.0.0 Removed all methods deprecated in 3.1.0.
 * @since 4.1.4 Removed all methods deprecated in 4.0.0.
 * @since 4.2.0 1. Changed namespace from \The_SEO_Framework to \The_SEO_Framework\Internal
 *              2. Removed all methods deprecated in 4.1.0.
 * @since 4.3.0 Removed all methods deprecated in 4.2.0
 * @ignore
 */
final class Deprecated {

	/**
	 * Set the value of the transient.
	 *
	 * Prevents setting of transients when they're disabled.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Deprecated
	 * @deprecated
	 *
	 * @param string $transient  Transient name. Expected to not be SQL-escaped.
	 * @param string $value      Transient value. Expected to not be SQL-escaped.
	 * @param int    $expiration Transient expiration date, optional. Expected to not be SQL-escaped.
	 */
	public function set_transient( $transient, $value, $expiration = 0 ) {
		\tsf()->_deprecated_function( 'tsf()->set_transient()', '4.3.0', 'set_transient()' );
		return \set_transient( $transient, $value, $expiration );
	}

	/**
	 * Get the value of the transient.
	 *
	 * If the transient does not exists, does not have a value or has expired,
	 * or transients have been disabled through a constant, then the transient
	 * will be false.
	 *
	 * N.B. not all transient settings make use of this function, bypassing the constant check.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Deprecated
	 * @deprecated
	 *
	 * @param string $transient Transient name. Expected to not be SQL-escaped.
	 * @return mixed|bool Value of the transient. False on failure or non existing transient.
	 */
	public function get_transient( $transient ) {
		\tsf()->_deprecated_function( 'tsf()->get_transient()', '4.3.0', 'get_transient()' );
		return \get_transient( $transient );
	}

	/**
	 * Returns left or right, for the home separator location.
	 *
	 * This method fetches the default option because it's conditional (LTR/RTL).
	 *
	 * @since 2.5.2
	 * @since 2.8.0 Method is now public.
	 * @since 4.3.0 1. No longer falls back to option or default option, but a language-based default instead.
	 *              2. Deprecated.
	 * @deprecated
	 *
	 * @param mixed $position Should ideally be a string 'left' or 'right' passed in.
	 * @return string left or right
	 */
	public function s_left_right_home( $position ) {

		$tsf = \tsf();

		$tsf->_deprecated_function( 'tsf()->s_left_right_home()', '4.3.0', 'tsf()->s_left_right()' );

		return $tsf->s_left_right( $position );
	}

	/**
	 * Returns the post type name from query input or real ID.
	 *
	 * @since 4.0.5
	 * @since 4.2.0 Now supports common archives without relying on the first post.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|WP_Post|null $post (Optional) Post ID or post object.
	 * @return string|false Post type on success, false on failure.
	 */
	public function get_post_type_real_id( $post = null ) {
		\tsf()->_deprecated_function( 'tsf()->get_post_type_real_id()', '4.3.0', 'tsf()->query()->get_post_type_real_id()' );
		return \tsf()->query()->get_post_type_real_id( $post );
	}

	/**
	 * Returns the post type name from current screen.
	 *
	 * @since 3.1.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string
	 */
	public function get_admin_post_type() {
		\tsf()->_deprecated_function( 'tsf()->get_admin_post_type()', '4.3.0', 'tsf()->query()->get_admin_post_type()' );
		return \tsf()->query()->get_admin_post_type();
	}

	/**
	 * Returns a list of post types shared with the taxonomy.
	 *
	 * @since 4.0.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $taxonomy Optional. The taxonomy to check. Defaults to current screen/query taxonomy.
	 * @return array List of post types.
	 */
	public function get_post_types_from_taxonomy( $taxonomy = '' ) {
		\tsf()->_deprecated_function( 'tsf()->get_post_types_from_taxonomy()', '4.3.0', 'tsf()->query()->get_post_types_from_taxonomy()' );
		return \tsf()->query()->get_post_types_from_taxonomy( $taxonomy );
	}

	/**
	 * Get the real page ID, also from CPT, archives, author, blog, etc.
	 * Memoizes the return value.
	 *
	 * @since 2.5.0
	 * @since 3.1.0 No longer checks if we can cache the query when $use_cache is false.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param bool $use_cache Whether to use the cache or not.
	 * @return int|false The ID.
	 */
	public function get_the_real_id( $use_cache = true ) {
		\tsf()->_deprecated_function( 'tsf()->get_the_real_id()', '4.3.0', 'tsf()->query()->get_the_real_id()' );
		return \tsf()->query()->get_the_real_id( $use_cache );
	}

	/**
	 * Fetches post or term ID within the admin.
	 * Alters while in the loop. Therefore, this can't be cached and must be called within the loop.
	 *
	 * @since 2.7.0
	 * @since 2.8.0 Removed WP 3.9 compat
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return int The admin ID.
	 */
	public function get_the_real_admin_id() {
		\tsf()->_deprecated_function( 'tsf()->get_the_real_admin_id()', '4.3.0', 'tsf()->query()->get_the_real_admin_id()' );
		return \tsf()->query()->get_the_real_admin_id();
	}

	/**
	 * Returns the front page ID, if home is a page.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return int the ID.
	 */
	public function get_the_front_page_id() {
		\tsf()->_deprecated_function( 'tsf()->get_the_front_page_id()', '4.3.0', 'tsf()->query()->get_the_front_page_id()' );
		return \tsf()->query()->get_the_front_page_id();
	}

	/**
	 * Fetches the Term ID on admin pages.
	 *
	 * @since 2.6.0
	 * @since 2.6.6 Moved from class The_SEO_Framework_Term_Data.
	 * @since 3.1.0 1. Removed WP 4.5 compat. Now uses global $tag_ID.
	 *              2. Removed caching
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return int Term ID.
	 */
	public function get_admin_term_id() {
		\tsf()->_deprecated_function( 'tsf()->get_admin_term_id()', '4.3.0', 'tsf()->query()->get_admin_term_id()' );
		return \tsf()->query()->get_admin_term_id();
	}

	/**
	 * Returns the current taxonomy, if any.
	 * Memoizes the return value.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 1. Now works in the admin.
	 *              2. Added caching
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The queried taxonomy type.
	 */
	public function get_current_taxonomy() {
		\tsf()->_deprecated_function( 'tsf()->get_current_taxonomy()', '4.3.0', 'tsf()->query()->get_current_taxonomy()' );
		return \tsf()->query()->get_current_taxonomy();
	}

	/**
	 * Returns the current post type, if any.
	 *
	 * @since 4.1.4
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The queried post type.
	 */
	public function get_current_post_type() {
		\tsf()->_deprecated_function( 'tsf()->get_current_post_type()', '4.3.0', 'tsf()->query()->get_current_post_type()' );
		return \tsf()->query()->get_current_post_type();
	}

	/**
	 * Detects 404.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_404() {
		\tsf()->_deprecated_function( 'tsf()->is_404()', '4.3.0', 'is_404()' );
		return \is_404();
	}

	/**
	 * Detects admin screen.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_admin() {
		\tsf()->_deprecated_function( 'tsf()->is_admin()', '4.3.0', 'is_admin()' );
		return \is_admin();
	}

	/**
	 * Detects attachment page.
	 *
	 * @since 2.6.0
	 * @since 4.0.0 Now reliably works on admin screens.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param mixed $attachment Attachment ID, title, slug, or array of such.
	 * @return bool
	 */
	public function is_attachment( $attachment = '' ) {
		\tsf()->_deprecated_function( 'tsf()->is_attachment()', '4.3.0', 'tsf()->query()->is_attachment()' );
		return \tsf()->query()->is_attachment( $attachment );
	}

	/**
	 * Detects attachments within the admin area.
	 *
	 * @since 4.0.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_attachment_admin() {
		\tsf()->_deprecated_function( 'tsf()->is_attachment_admin()', '4.3.0', 'tsf()->query()->is_attachment_admin()' );
		return \tsf()->query()->is_attachment_admin();
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
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|WP_Post|null $post (Optional) Post ID or post object.
	 * @return bool
	 */
	public function is_singular_archive( $post = null ) {
		\tsf()->_deprecated_function( 'tsf()->is_singular_archive()', '4.3.0', 'tsf()->query()->is_singular_archive()' );
		return \tsf()->query()->is_singular_archive( $post );
	}

	/**
	 * Detects archive pages. Also in admin.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_archive() {
		\tsf()->_deprecated_function( 'tsf()->is_archive()', '4.3.0', 'tsf()->query()->is_archive()' );
		return \tsf()->query()->is_archive();
	}

	/**
	 * Extends default WordPress is_archive() and determines screen in admin.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool Post Type is archive
	 */
	public function is_archive_admin() {
		\tsf()->_deprecated_function( 'tsf()->is_archive_admin()', '4.3.0', 'tsf()->query()->is_archive_admin()' );
		return \tsf()->query()->is_archive_admin();
	}

	/**
	 * Detects Term edit screen in WP Admin.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool True if on Term Edit screen. False otherwise.
	 */
	public function is_term_edit() {
		\tsf()->_deprecated_function( 'tsf()->is_term_edit()', '4.3.0', 'tsf()->query()->is_term_edit()' );
		return \tsf()->query()->is_term_edit();
	}

	/**
	 * Detects Post edit screen in WP Admin.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool We're on Post Edit screen.
	 */
	public function is_post_edit() {
		\tsf()->_deprecated_function( 'tsf()->is_post_edit()', '4.3.0', 'tsf()->query()->is_post_edit()' );
		return \tsf()->query()->is_post_edit();
	}

	/**
	 * Detects Post or Archive Lists in Admin.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool We're on the edit screen.
	 */
	public function is_wp_lists_edit() {
		\tsf()->_deprecated_function( 'tsf()->is_wp_lists_edit()', '4.3.0', 'tsf()->query()->is_wp_lists_edit()' );
		return \tsf()->query()->is_wp_lists_edit();
	}

	/**
	 * Detects Profile edit screen in WP Admin.
	 *
	 * @since 4.1.4
	 * @since 4.3.0 1. Now also tests network profile edit screens.
	 *              2. Deprecated.
	 * @deprecated
	 *
	 * @return bool True if on Profile Edit screen. False otherwise.
	 */
	public function is_profile_edit() {
		\tsf()->_deprecated_function( 'tsf()->is_profile_edit()', '4.3.0', 'tsf()->query()->is_profile_edit()' );
		return \tsf()->query()->is_profile_edit();
	}

	/**
	 * Detects author archives.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param mixed $author Optional. User ID, nickname, nicename, or array of User IDs, nicknames, and nicenames
	 * @return bool
	 */
	public function is_author( $author = '' ) {
		\tsf()->_deprecated_function( 'tsf()->is_author()', '4.3.0', 'tsf()->query()->is_author()' );
		return \tsf()->query()->is_author( $author );
	}

	/**
	 * Detects the blog page.
	 *
	 * @since 2.6.0
	 * @since 4.2.0 Added the first parameter to allow custom query testing.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|WP_Post|null $post Optional. Post ID or post object.
	 *                               Do not supply from WP_Query's main loop-query.
	 * @return bool
	 */
	public function is_home( $post = null ) {
		\tsf()->_deprecated_function( 'tsf()->is_home()', '4.3.0', 'tsf()->query()->is_home()' );
		return \tsf()->query()->is_home( $post );
	}

	/**
	 * Detects the non-front blog page.
	 *
	 * @since 4.2.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|WP_Post|null $post Optional. Post ID or post object.
	 *                               Do not supply from WP_Query's main loop-query.
	 * @return bool
	 */
	public function is_home_as_page( $post = null ) {
		\tsf()->_deprecated_function( 'tsf()->is_home_as_page()', '4.3.0', 'tsf()->query()->is_home_as_page()' );
		return \tsf()->query()->is_home_as_page( $post );
	}

	/**
	 * Detects category archives.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param mixed $category Optional. Category ID, name, slug, or array of Category IDs, names, and slugs.
	 * @return bool
	 */
	public function is_category( $category = '' ) {
		\tsf()->_deprecated_function( 'tsf()->is_category()', '4.3.0', 'tsf()->query()->is_category()' );
		return \tsf()->query()->is_category( $category );
	}

	/**
	 * Extends default WordPress is_category() and determines screen in admin.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 No longer guesses category by name. It now only matches WordPress's built-in category.
	 * @since 4.0.0 Removed caching.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool Post Type is category
	 */
	public function is_category_admin() {
		\tsf()->_deprecated_function( 'tsf()->is_category_admin()', '4.3.0', 'tsf()->query()->is_category_admin()' );
		return \tsf()->query()->is_category_admin();
	}

	/**
	 * Detects customizer preview.
	 *
	 * Unlike is_preview(), WordPress has prior security checks for this
	 * in `\WP_Customize_Manager::setup_theme()`.
	 *
	 * @since 4.0.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_customize_preview() {
		\tsf()->_deprecated_function( 'tsf()->is_customize_preview()', '4.3.0', 'is_customize_preview()' );
		return \is_customize_preview();
	}

	/**
	 * Detects date archives.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_date() {
		\tsf()->_deprecated_function( 'tsf()->is_date()', '4.3.0', 'is_date()' );
		return \is_date();
	}

	/**
	 * Detects day archives.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_day() {
		\tsf()->_deprecated_function( 'tsf()->is_day()', '4.3.0', 'is_day()' );
		return \is_day();
	}

	/**
	 * Detects feed.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string|array $feeds Optional feed types to check.
	 * @return bool
	 */
	public function is_feed( $feeds = '' ) {
		\tsf()->_deprecated_function( 'tsf()->is_feed()', '4.3.0', 'is_feed()' );
		return \is_feed( $feeds );
	}

	/**
	 * Detects front page.
	 *
	 * @since 2.9.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_real_front_page() {
		\tsf()->_deprecated_function( 'tsf()->is_real_front_page()', '4.3.0', 'tsf()->query()->is_real_front_page()' );
		return \tsf()->query()->is_real_front_page();
	}

	/**
	 * Checks for front page by input ID without engaging into the query.
	 *
	 * @NOTE This doesn't check for anomalies in the query.
	 * So, don't use this to test user-engaged WordPress queries, ever.
	 * WARNING: This will lead to **FALSE POSITIVES** for Date, CPTA, Search, and other archives.
	 *
	 * @see \tsf()->is_real_front_page(), which solely uses query checking.
	 * @see \tsf()->is_static_frontpage(), which adds an "is homepage static" check.
	 *
	 * @since 3.2.2
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $id The tested ID.
	 * @return bool
	 */
	public function is_real_front_page_by_id( $id ) {
		\tsf()->_deprecated_function( 'tsf()->is_real_front_page_by_id()', '4.3.0', 'tsf()->query()->is_real_front_page_by_id()' );
		return \tsf()->query()->is_real_front_page_by_id( $id );
	}

	/**
	 * Detects month archives.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_month() {
		\tsf()->_deprecated_function( 'tsf()->is_month()', '4.3.0', 'is_month()' );
		return \is_month();
	}

	/**
	 * Detects pages.
	 * When $page is supplied, it will check against the current object. So it will not work in the admin screens.
	 *
	 * @since 2.6.0
	 * @since 4.0.0 Now tests for post type, which is more reliable.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|string|array $page Optional. Page ID, title, slug, or array of such. Default empty.
	 * @return bool
	 */
	public function is_page( $page = '' ) {
		\tsf()->_deprecated_function( 'tsf()->is_page()', '4.3.0', 'tsf()->query()->is_page()' );
		return \tsf()->query()->is_page( $page );
	}

	/**
	 * Detects pages within the admin area.
	 *
	 * @since 2.6.0
	 * @since 4.0.0 Now tests for post type, although redundant.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_page_admin() {
		\tsf()->_deprecated_function( 'tsf()->is_page_admin()', '4.3.0', 'tsf()->query()->is_page_admin()' );
		return \tsf()->query()->is_page_admin();
	}

	/**
	 * Detects preview, securely.
	 *
	 * @since 2.6.0
	 * @since 4.0.0 This is now deemed a secure method.
	 *              1. Added is_user_logged_in() check.
	 *              2. Added is_singular() check, so get_the_ID() won't cross with blog pages.
	 *              3. Added current_user_can() check.
	 *              4. Added wp_verify_nonce() check.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_preview() {
		\tsf()->_deprecated_function( 'tsf()->is_preview()', '4.3.0', 'tsf()->query()->is_preview()' );
		return \tsf()->query()->is_preview();
	}

	/**
	 * Detects search.
	 *
	 * @since 2.6.0
	 * @since 2.9.4 Now always returns false in admin.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_search() {
		\tsf()->_deprecated_function( 'tsf()->is_search()', '4.3.0', 'tsf()->query()->is_search()' );
		return \tsf()->query()->is_search();
	}

	/**
	 * Detects single post pages.
	 * When $post is supplied, it will check against the current object. So it will not work in the admin screens.
	 *
	 * @since 2.6.0
	 * @since 4.0.0 Now tests for post type, which is more reliable.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|string|array $post Optional. Post ID, title, slug, or array of such. Default empty.
	 * @return bool
	 */
	public function is_single( $post = '' ) {
		\tsf()->_deprecated_function( 'tsf()->is_single()', '4.3.0', 'tsf()->query()->is_single()' );
		return \tsf()->query()->is_single( $post );
	}

	/**
	 * Detects posts within the admin area.
	 *
	 * @since 2.6.0
	 * @since 4.0.0 Now no longer returns true on categories and tags.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_single_admin() {
		\tsf()->_deprecated_function( 'tsf()->is_single_admin()', '4.3.0', 'tsf()->query()->is_single_admin()' );
		return \tsf()->query()->is_single_admin();
	}

	/**
	 * Determines if the current page is singular is holds singular items within the admin screen.
	 * Replaces and expands default WordPress `is_singular()`.
	 *
	 * @since 2.5.2
	 * @since 3.1.0 Now passes $post_types parameter in admin screens, only when it's an integer.
	 * @since 4.0.0 No longer processes integers as input.
	 * @since 4.2.4 No longer tests type of $post_types.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string|string[] $post_types Optional. Post type or array of post types. Default empty string.
	 * @return bool Post Type is singular
	 */
	public function is_singular( $post_types = '' ) {
		\tsf()->_deprecated_function( 'tsf()->is_singular()', '4.3.0', 'tsf()->query()->is_singular()' );
		return \tsf()->query()->is_singular( $post_types );
	}

	/**
	 * Determines if the page is singular within the admin screen.
	 *
	 * @since 2.5.2
	 * @since 3.1.0 Added $post_id parameter. When used, it'll only check for it.
	 * @since 4.0.0 Removed first parameter.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool Post Type is singular
	 */
	public function is_singular_admin() {
		\tsf()->_deprecated_function( 'tsf()->is_singular_admin()', '4.3.0', 'tsf()->query()->is_singular_admin()' );
		return \tsf()->query()->is_singular_admin();
	}

	/**
	 * Detects the static front page.
	 *
	 * @since 2.3.8
	 * @since 4.1.4 Added memoization.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $id the Page ID to check. If empty, the current ID will be fetched.
	 * @return bool True when homepage is static and given/current ID matches.
	 */
	public function is_static_frontpage( $id = 0 ) {
		\tsf()->_deprecated_function( 'tsf()->is_static_frontpage()', '4.3.0', 'tsf()->query()->is_static_frontpage()' );
		return \tsf()->query()->is_static_frontpage( $id );
	}

	/**
	 * Detects tag archives.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param mixed $tag Optional. Tag ID, name, slug, or array of Tag IDs, names, and slugs.
	 * @return bool
	 */
	public function is_tag( $tag = '' ) {
		\tsf()->_deprecated_function( 'tsf()->is_tag()', '4.3.0', 'tsf()->query()->is_tag()' );
		return \tsf()->query()->is_tag( $tag );
	}

	/**
	 * Determines if the page is a tag within the admin screen.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 No longer guesses tag by name. It now only matches WordPress's built-in tag.
	 * @since 4.0.0 Removed caching.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool Post Type is tag.
	 */
	public function is_tag_admin() {
		\tsf()->_deprecated_function( 'tsf()->is_tag_admin()', '4.3.0', 'tsf()->query()->is_tag_admin()' );
		return \tsf()->query()->is_tag_admin();
	}

	/**
	 * Detects taxonomy archives.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string|array     $taxonomy Optional. Taxonomy slug or slugs.
	 * @param int|string|array $term     Optional. Term ID, name, slug or array of Term IDs, names, and slugs.
	 * @return bool
	 */
	public function is_tax( $taxonomy = '', $term = '' ) {
		\tsf()->_deprecated_function( 'tsf()->is_tax()', '4.3.0', 'tsf()->query()->is_tax()' );
		return \tsf()->query()->is_tax( $taxonomy, $term );
	}

	/**
	 * Determines if the $post is a shop page.
	 *
	 * @since 4.0.5
	 * @since 4.1.4 Added memoization.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|WP_Post|null $post (Optional) Post ID or post object.
	 * @return bool
	 */
	public function is_shop( $post = null ) {
		\tsf()->_deprecated_function( 'tsf()->is_shop()', '4.3.0', 'tsf()->query()->is_shop()' );
		return \tsf()->query()->is_shop( $post );
	}

	/**
	 * Determines if the page is a product page.
	 *
	 * @since 4.0.5
	 * @since 4.1.4 Added memoization.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|WP_Post|null $post (Optional) Post ID or post object.
	 * @return bool True if on a WooCommerce Product page.
	 */
	public function is_product( $post = null ) {
		\tsf()->_deprecated_function( 'tsf()->is_product()', '4.3.0', 'tsf()->query()->is_product()' );
		return \tsf()->query()->is_product( $post );
	}

	/**
	 * Determines if the admin page is for a product page.
	 *
	 * @since 4.0.5
	 * @since 4.1.4 Added memoization.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_product_admin() {
		\tsf()->_deprecated_function( 'tsf()->is_product_admin()', '4.3.0', 'tsf()->query()->is_product_admin()' );
		return \tsf()->query()->is_product_admin();
	}

	/**
	 * Detects year archives.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_year() {
		\tsf()->_deprecated_function( 'tsf()->is_year()', '4.3.0', 'tsf()->query()->is_year()' );
		return \tsf()->query()->is_year();
	}

	/**
	 * Determines if SSL is used.
	 * Memoizes the return value.
	 *
	 * @since 2.8.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool True if SSL, false otherwise.
	 */
	public function is_ssl() {
		\tsf()->_deprecated_function( 'tsf()->is_ssl()', '4.3.0', 'tsf()->query()->is_ssl()' );
		return \tsf()->query()->is_ssl();
	}

	/**
	 * Determines whether we're on the SEO settings page.
	 * WARNING: Do not ever use this as a safety check.
	 *
	 * @since 2.6.0
	 * @since 2.7.0 Added secure parameter.
	 * @since 2.9.0 If $secure is false, the cache is no longer used.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 * @see \tsf()->is_menu_page() for security notification.
	 *
	 * @param bool $secure Whether to ignore the use of the second (insecure) parameter.
	 * @return bool
	 */
	public function is_seo_settings_page( $secure = true ) {
		\tsf()->_deprecated_function( 'tsf()->is_seo_settings_page()', '4.3.0', 'tsf()->query()->is_seo_settings_page()' );
		return \tsf()->query()->is_seo_settings_page( $secure );
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
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 * @global string $page_hook the current page hook.
	 *
	 * @param string $pagehook The menu pagehook to compare to.
	 *               To be used after `admin_init`.
	 * @param string $pageslug The menu page slug to compare to.
	 *               To be used before `admin_init`.
	 * @return bool true if screen match.
	 */
	public function is_menu_page( $pagehook = '', $pageslug = '' ) {
		\tsf()->_deprecated_function( 'tsf()->is_menu_page()', '4.3.0', 'tsf()->query()->is_menu_page()' );
		return \tsf()->query()->is_menu_page( $pagehook, $pageslug );
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
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return int (R>0) $page Always a positive number.
	 */
	public function page() {
		\tsf()->_deprecated_function( 'tsf()->page()', '4.3.0', 'tsf()->query()->page()' );
		return \tsf()->query()->page();
	}

	/**
	 * Returns the current page number.
	 * Fetches global `$paged` from `WP_Query` to prevent conflicts.
	 *
	 * @since 2.6.0
	 * @since 3.2.4 1. Added overflow protection.
	 *              2. Now always returns 1 on the admin screens.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return int (R>0) $paged Always a positive number.
	 */
	public function paged() {
		\tsf()->_deprecated_function( 'tsf()->paged()', '4.3.0', 'tsf()->query()->paged()' );
		return \tsf()->query()->paged();
	}

	/**
	 * Determines the number of available pages.
	 *
	 * This is largely taken from \WP_Query::setup_postdata(), however, the data
	 * we need is set up in the loop, not in the header; where TSF is active.
	 *
	 * @since 3.1.0
	 * @since 3.2.4 Now only returns "1" in the admin.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return int
	 */
	public function numpages() {
		\tsf()->_deprecated_function( 'tsf()->numpages()', '4.3.0', 'tsf()->query()->numpages()' );
		return \tsf()->query()->numpages();
	}

	/**
	 * Determines whether the current loop has multiple pages.
	 *
	 * @since 2.7.0
	 * @since 3.1.0 1. Now also works on archives.
	 *              2. Now is public.
	 * @since 3.2.4 Now always returns false on the admin pages.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool True if multipage.
	 */
	public function is_multipage() {
		\tsf()->_deprecated_function( 'tsf()->is_multipage()', '4.3.0', 'tsf()->query()->is_multipage()' );
		return \tsf()->query()->is_multipage();
	}

	/**
	 * Determines whether we're on The SEO Framework's sitemap or not.
	 * Memoizes the return value once set.
	 *
	 * @since 2.9.2
	 * @since 4.0.0 Now memoizes instead of populating class properties.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param bool $set Whether to set "doing sitemap".
	 * @return bool
	 */
	public function is_sitemap( $set = false ) {
		\tsf()->_deprecated_function( 'tsf()->is_sitemap()', '4.3.0', 'tsf()->query()->is_sitemap()' );
		return \tsf()->query()->is_sitemap( $set );
	}

	/**
	 * Determines whether we're on the robots.txt file output.
	 *
	 * @since 2.9.2
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_robots() {
		\tsf()->_deprecated_function( 'tsf()->is_robots()', '4.3.0', 'is_robots()' );
		return \is_robots();
	}

	/**
	 * Renders the 'tsf:aqp' meta tag. Useful for identifying when query-exploit detection
	 * is triggered.
	 *
	 * @since 4.1.4
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The advanced query protection (aqp) identifier.
	 */
	public function advanced_query_protection() {

		\tsf()->_deprecated_function( 'tsf()->advanced_query_protection()', '4.3.0' );

		return \The_SEO_Framework\Interpreters\Meta::render( [
			'name'  => 'tsf:aqp',
			'value' => '1',
		] );
	}

	/**
	 * Renders the description meta tag.
	 *
	 * @since 1.3.0
	 * @since 3.0.6 No longer uses \tsf()->description_from_cache()
	 * @since 3.1.0 No longer checks for SEO plugin presence.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 * @uses \tsf()->get_description()
	 *
	 * @return string The description meta tag.
	 */
	public function the_description() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->the_description()', '4.3.0' );

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param string $description The generated description.
		 * @param int    $id          The page or term ID.
		 */
		$description = (string) \apply_filters_deprecated(
			'the_seo_framework_description_output',
			[
				$tsf->get_description(),
				Query::get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $description ? \The_SEO_Framework\Interpreters\Meta::render( [
			'name'    => 'description',
			'content' => $description,
		] ) : '';
	}

	/**
	 * Renders Robots meta tags.
	 * Returns early if blog isn't public. WordPress Core will then output the meta tags.
	 *
	 * @since 2.0.0
	 * @since 4.0.2 Thanks to special tags, output escaping has been added precautionarily.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Robots meta tags.
	 */
	public function robots() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->robots()', '4.3.0' );

		// Don't do anything if the blog isn't set to public.
		if ( false === $tsf->is_blog_public() ) return '';

		$meta = $tsf->get_robots_meta();

		return $meta ? \The_SEO_Framework\Interpreters\Meta::render( [
			'name'    => 'robots',
			'content' => implode( ',', $meta ),
		] ) : '';
	}

	/**
	 * Renders Canonical URL meta tag.
	 *
	 * @since 2.0.6
	 * @since 3.0.0 Deleted filter `the_seo_framework_output_canonical`.
	 * @since 3.2.4 Now no longer returns a value when the post is not indexed with a non-custom URL.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 * @uses \tsf()->get_current_canonical_url()
	 *
	 * @return string The Canonical URL meta tag.
	 */
	public function canonical() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->canonical()', '4.3.0' );

		$_url = $tsf->get_current_canonical_url();

		/**
		 * @since 2.6.5
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param string $url The canonical URL. Must be escaped.
		 * @param int    $id  The current page or term ID.
		 */
		$url = (string) \apply_filters_deprecated(
			'the_seo_framework_rel_canonical_output',
			[
				$_url,
				Query::get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		// If the page should not be indexed, consider removing the canonical URL.
		if ( \in_array( 'noindex', $tsf->get_robots_meta(), true ) ) {
			// If the URL is filtered, don't empty it.
			// If a custom canonical URL is set, don't empty it.
			if ( $url === $_url && ! $tsf->has_custom_canonical_url() ) {
				$url = '';
			}
		}

		return $url ? \The_SEO_Framework\Interpreters\Meta::render(
			[
				'rel'  => 'canonical',
				'href' => $url,
			],
			'link'
		) : '';
	}

	/**
	 * Renders Shortlink meta tag
	 *
	 * @since 2.2.2
	 * @since 2.9.3 Now work when homepage is a blog.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 * @uses \tsf()->get_shortlink()
	 *
	 * @return string The Shortlink meta tag.
	 */
	public function shortlink() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->advanced_query_protection()', '4.3.0' );

		/**
		 * @since 2.6.0
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param string $url The generated shortlink URL.
		 * @param int    $id  The current post or term ID.
		 */
		$url = (string) \apply_filters_deprecated(
			'the_seo_framework_shortlink_output',
			[
				$tsf->get_shortlink(),
				Query::get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $url ? \The_SEO_Framework\Interpreters\Meta::render(
			[
				'rel'  => 'shortlink',
				'href' => $url,
			],
			'link'
		) : '';
	}

	/**
	 * Renders Prev/Next Paged URL meta tags.
	 *
	 * @since 2.2.2
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 * @uses \tsf()->get_paged_urls()
	 *
	 * @return string The Prev/Next Paged URL meta tags.
	 */
	public function paged_urls() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->paged_urls()', '4.3.0' );

		$paged_urls = $tsf->get_paged_urls();
		$id         = Query::get_the_real_id();

		/**
		 * @since 2.6.0
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param string $next The next-page URL.
		 * @param int    $id   The current post or term ID.
		 */
		$next = (string) \apply_filters_deprecated(
			'the_seo_framework_paged_url_output_next',
			[
				$paged_urls['next'],
				$id,
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);
		/**
		 * @since 2.6.0
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param string $next The previous-page URL.
		 * @param int    $id   The current post or term ID.
		 */
		$prev = (string) \apply_filters_deprecated(
			'the_seo_framework_paged_url_output_prev',
			[
				$paged_urls['prev'],
				$id,
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		$output  = $prev ? \The_SEO_Framework\Interpreters\Meta::render(
			[
				'rel'  => 'prev',
				'href' => $prev,
			],
			'link'
		) : '';
		$output .= $next ? \The_SEO_Framework\Interpreters\Meta::render(
			[
				'rel'  => 'next',
				'href' => $next,
			],
			'link'
		) : '';

		return $output;
	}

	/**
	 * Renders Theme Color meta tag.
	 *
	 * @since 4.0.5
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Theme Color meta tag.
	 */
	public function theme_color() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->theme_color()', '4.3.0' );

		$theme_color = $tsf->get_option( 'theme_color' );

		return $theme_color ? \The_SEO_Framework\Interpreters\Meta::render( [
			'name'    => 'theme-color',
			'content' => $theme_color,
		] ) : '';
	}

	/**
	 * Renders Google Site Verification Code meta tag.
	 *
	 * @since 2.2.4
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Google Site Verification code meta tag.
	 */
	public function google_site_output() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->google_site_output()', '4.3.0' );

		/**
		 * @since 2.6.0
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param string $code The Google verification code.
		 * @param int    $id   The current post or term ID.
		 */
		$code = (string) \apply_filters_deprecated(
			'the_seo_framework_googlesite_output',
			[
				$tsf->get_option( 'google_verification' ),
				\The_SEO_Framework\Helper\Queryget_the_real_id(),
			]
		);

		return $code ? \The_SEO_Framework\Interpreters\Meta::render( [
			'name'    => 'google-site-verification',
			'content' => $code,
		] ) : '';
	}

	/**
	 * Renders Bing Site Verification Code meta tag.
	 *
	 * @since 2.2.4
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Bing Site Verification Code meta tag.
	 */
	public function bing_site_output() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->bing_site_output()', '4.3.0' );

		/**
		 * @since 2.6.0
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param string $code The Bing verification code.
		 * @param int    $id   The current post or term ID.
		 */
		$code = (string) \apply_filters_deprecated(
			'the_seo_framework_bingsite_output',
			[
				$tsf->get_option( 'bing_verification' ),
				\The_SEO_Framework\Helper\Queryget_the_real_id(),
			]
		);

		return $code ? \The_SEO_Framework\Interpreters\Meta::render( [
			'name'    => 'msvalidate.01',
			'content' => $code,
		] ) : '';
	}

	/**
	 * Renders Yandex Site Verification code meta tag.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Yandex Site Verification code meta tag.
	 */
	public function yandex_site_output() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->yandex_site_output()', '4.3.0' );

		/**
		 * @since 2.6.0
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param string $code The Yandex verification code.
		 * @param int    $id   The current post or term ID.
		 */
		$code = (string) \apply_filters_deprecated(
			'the_seo_framework_yandexsite_output',
			[
				$tsf->get_option( 'yandex_verification' ),
				\The_SEO_Framework\Helper\Queryget_the_real_id(),
			]
		);

		return $code ? \The_SEO_Framework\Interpreters\Meta::render( [
			'name'    => 'yandex-verification',
			'content' => $code,
		] ) : '';
	}

	/**
	 * Renders Baidu Site Verification code meta tag.
	 *
	 * @since 4.0.5
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Baidu Site Verification code meta tag.
	 */
	public function baidu_site_output() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->baidu_site_output()', '4.3.0' );

		/**
		 * @since 4.0.5
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param string $code The Baidu verification code.
		 * @param int    $id   The current post or term ID.
		 */
		$code = (string) \apply_filters_deprecated(
			'the_seo_framework_baidusite_output',
			[
				$tsf->get_option( 'baidu_verification' ),
				\The_SEO_Framework\Helper\Queryget_the_real_id(),
			]
		);

		return $code ? \The_SEO_Framework\Interpreters\Meta::render( [
			'name'    => 'baidu-site-verification',
			'content' => $code,
		] ) : '';
	}

	/**
	 * Renders Pinterest Site Verification code meta tag.
	 *
	 * @since 2.5.2
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Pinterest Site Verification code meta tag.
	 */
	public function pint_site_output() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->pint_site_output()', '4.3.0' );

		/**
		 * @since 2.6.0
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param string $code The Pinterest verification code.
		 * @param int    $id   The current post or term ID.
		 */
		$code = (string) \apply_filters_deprecated(
			'the_seo_framework_pintsite_output',
			[
				$tsf->get_option( 'pint_verification' ),
				\The_SEO_Framework\Helper\Queryget_the_real_id(),
			]
		);

		return $code ? \The_SEO_Framework\Interpreters\Meta::render( [
			'name'    => 'p:domain_verify',
			'content' => $code,
		] ) : '';
	}

	/**
	 * Determines whether we can use Open Graph tags on the front-end.
	 * Memoizes the return value.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Removed cache.
	 * @since 3.1.4 1. Added filter.
	 *              2. Reintroduced cache because of filter.
	 * @since 4.3.0 1. Deprecated.
	 *              2. Removed memoization.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function use_og_tags() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->pint_site_output()', '4.3.0' );

		/**
		 * @since 3.1.4
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param bool $use_open_graph
		 */
		return \apply_filters_deprecated(
			'the_seo_framework_use_og_tags',
			[
				(bool) $tsf->get_option( 'og_tags' ),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_generators',
		);
	}

	/**
	 * Renders the Open Graph title meta tag.
	 *
	 * @since 2.0.3
	 * @since 3.0.4 No longer uses \tsf()->title_from_cache()
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 * @uses \tsf()->get_open_graph_title()
	 *
	 * @return string The Open Graph title meta tag.
	 */
	public function og_title() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->og_title()', '4.3.0' );

		if ( ! $tsf->use_og_tags() )
			return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param string $title The generated Open Graph title.
		 * @param int    $id    The page or term ID.
		 */
		$title = (string) \apply_filters_deprecated(
			'the_seo_framework_ogtitle_output',
			[
				$tsf->get_open_graph_title(),
				\The_SEO_Framework\Helper\Query::get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $title ? \The_SEO_Framework\Interpreters\Meta::render( [
			'property' => 'og:title',
			'content'  => $title,
		] ) : '';
	}

	/**
	 * Renders og:description meta tag
	 *
	 * @since 1.3.0
	 * @since 3.0.4 No longer uses \tsf()->description_from_cache()
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 * @uses \tsf()->get_open_graph_description()
	 *
	 * @return string The Open Graph description meta tag.
	 */
	public function og_description() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->og_description()', '4.3.0' );

		if ( ! $tsf->use_og_tags() )
			return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param string $description The generated Open Graph description.
		 * @param int    $id          The page or term ID.
		 */
		$description = (string) \apply_filters_deprecated(
			'the_seo_framework_ogdescription_output',
			[
				$tsf->get_open_graph_description(),
				Query::get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $description ? \The_SEO_Framework\Interpreters\Meta::render( [
			'property' => 'og:description',
			'content'  => $description,
		] ) : '';
	}

	/**
	 * Renders the OG locale meta tag.
	 *
	 * @since 1.0.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Open Graph locale meta tag.
	 */
	public function og_locale() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->og_locale()', '4.3.0' );

		if ( ! $tsf->use_og_tags() )
			return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param string $locale The generated locale field.
		 * @param int    $id     The page or term ID.
		 */
		$locale = (string) \apply_filters_deprecated(
			'the_seo_framework_oglocale_output',
			[
				$tsf->fetch_locale(),
				Query::get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $locale ? \The_SEO_Framework\Interpreters\Meta::render( [
			'property' => 'og:locale',
			'content'  => $locale,
		] ) : '';
	}

	/**
	 * Renders the Open Graph type meta tag.
	 *
	 * @since 1.1.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Open Graph type meta tag.
	 */
	public function og_type() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->og_type()', '4.3.0' );

		if ( ! $tsf->use_og_tags() )
			return '';

		$type = $tsf->get_og_type();

		return $type ? \The_SEO_Framework\Interpreters\Meta::render( [
			'property' => 'og:type',
			'content'  => $type,
		] ) : '';
	}

	/**
	 * Renders Open Graph image meta tag.
	 *
	 * @since 1.3.0
	 * @since 2.6.0 Added WooCommerce gallery images.
	 * @since 2.7.0 Added image dimensions if found.
	 * @since 4.1.2 Now forwards the `multi_og_image` option to the generator to
	 *              reduce processing power.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Open Graph image meta tag.
	 */
	public function og_image() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->og_type()', '4.3.0' );

		if ( ! $tsf->use_og_tags() ) return '';

		$output = '';

		$multi = (bool) $tsf->get_option( 'multi_og_image' );

		foreach ( $tsf->get_image_details_from_cache( ! $multi ) as $image ) {
			$output .= \The_SEO_Framework\Interpreters\Meta::render( [
				'property' => 'og:image',
				'content'  => $image['url'],
			] );

			if ( $image['height'] && $image['width'] ) {
				$output .= \The_SEO_Framework\Interpreters\Meta::render( [
					'property' => 'og:image:width',
					'content'  => $image['width'],
				] );
				$output .= \The_SEO_Framework\Interpreters\Meta::render( [
					'property' => 'og:image:height',
					'content'  => $image['height'],
				] );
			}

			if ( $image['alt'] ) {
				$output .= \The_SEO_Framework\Interpreters\Meta::render( [
					'property' => 'og:image:alt',
					'content'  => $image['alt'],
				] );
			}

			// Redundant?
			if ( ! $multi ) break;
		}

		return $output;
	}

	/**
	 * Renders Open Graph sitename meta tag.
	 *
	 * @since 1.3.0
	 * @since 3.1.0 Now uses \tsf()->get_blogname(), which trims the output.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Open Graph sitename meta tag.
	 */
	public function og_sitename() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->og_sitename()', '4.3.0' );

		if ( ! $tsf->use_og_tags() ) return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param string $sitename The generated Open Graph site name.
		 * @param int    $id       The page or term ID.
		 */
		$sitename = (string) \apply_filters_deprecated(
			'the_seo_framework_ogsitename_output',
			[
				$tsf->get_blogname(),
				Query::get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $sitename ? \The_SEO_Framework\Interpreters\Meta::render( [
			'property' => 'og:site_name',
			'content'  => $sitename,
		] ) : '';
	}

	/**
	 * Renders Open Graph URL meta tag.
	 *
	 * @since 1.3.0
	 * @since 2.9.3 Added filter
	 * @since 4.1.4 Now uses `render_element()`, which applies `esc_attr()` on the URL.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 * @uses \tsf()->get_current_canonical_url()
	 *
	 * @return string The Open Graph URL meta tag.
	 */
	public function og_url() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->og_url()', '4.3.0' );

		if ( ! $tsf->use_og_tags() ) return '';

		/**
		 * @since 2.9.3
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param string $url The canonical/Open Graph URL. Must be escaped.
		 * @param int    $id  The current page or term ID.
		 */
		$url = (string) \apply_filters_deprecated(
			'the_seo_framework_ogurl_output',
			[
				$tsf->get_current_canonical_url(),
				Query::get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $url ? \The_SEO_Framework\Interpreters\Meta::render( [
			'property' => 'og:url',
			'content'  => $url,
		] ) : '';
	}

	/**
	 * Renders the Open Graph Updated Time meta tag.
	 *
	 * @since 4.1.4
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Article Modified Time meta tag, and optionally the Open Graph Updated Time.
	 */
	public function og_updated_time() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->og_updated_time()', '4.3.0' );

		if ( ! $tsf->use_og_tags() ) return '';
		if ( ! $tsf->output_published_time() ) return '';

		$time = $tsf->get_modified_time();

		return $time ? \The_SEO_Framework\Interpreters\Meta::render( [
			'property' => 'og:updated_time',
			'content'  => $time,
		] ) : '';
	}

	/**
	 * Renders Facebook Author meta tag.
	 *
	 * @since 2.2.2
	 * @since 2.8.0 Returns empty on og:type 'website' or 'product'
	 * @since 3.0.0 Fetches Author meta data.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Facebook Author meta tag.
	 */
	public function facebook_author() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->facebook_author()', '4.3.0' );

		if ( ! $tsf->use_facebook_tags() ) return '';
		if ( 'article' !== $tsf->get_og_type() ) return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param string $facebook_page The generated Facebook author page URL.
		 * @param int    $id            The current page or term ID.
		 */
		$facebook_page = (string) \apply_filters_deprecated(
			'the_seo_framework_facebookauthor_output',
			[
				$tsf->get_current_post_author_meta_item( 'facebook_page' ) ?: $tsf->get_option( 'facebook_author' ),
				Query::get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $facebook_page ? \The_SEO_Framework\Interpreters\Meta::render( [
			'property' => 'article:author',
			'content'  => $facebook_page,
		] ) : '';
	}

	/**
	 * Renders Facebook Publisher meta tag.
	 *
	 * @since 2.2.2
	 * @since 3.0.0 No longer outputs tag when "og:type" isn't 'article'.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Facebook Publisher meta tag.
	 */
	public function facebook_publisher() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->facebook_publisher()', '4.3.0' );

		if ( ! $tsf->use_facebook_tags() ) return '';
		if ( 'article' !== $tsf->get_og_type() ) return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param string $publisher The Facebook publisher page URL.
		 * @param int    $id        The current page or term ID.
		 */
		$publisher = (string) \apply_filters_deprecated(
			'the_seo_framework_facebookpublisher_output',
			[
				$tsf->get_option( 'facebook_publisher' ),
				Query::get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $publisher ? \The_SEO_Framework\Interpreters\Meta::render( [
			'property' => 'article:publisher',
			'content'  => $publisher,
		] ) : '';
	}

	/**
	 * Renders Facebook App ID meta tag.
	 *
	 * @since 2.2.2
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Facebook App ID meta tag.
	 */
	public function facebook_app_id() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->facebook_app_id()', '4.3.0' );

		if ( ! $tsf->use_facebook_tags() ) return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param string $app_id The Facebook app ID.
		 * @param int    $id     The current page or term ID.
		 */
		$app_id = (string) \apply_filters_deprecated(
			'the_seo_framework_facebookappid_output',
			[
				$tsf->get_option( 'facebook_appid' ),
				Query::get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data', // var_dump() delete me?
		);

		return $app_id ? \The_SEO_Framework\Interpreters\Meta::render( [
			'property' => 'fb:app_id',
			'content'  => $app_id,
		] ) : '';
	}

	/**
	 * Determines whether we can use Facebook tags on the front-end.
	 * Memoizes the return value.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Removed cache.
	 * @since 3.1.4 1. Added filter.
	 *              2. Reintroduced cache because of filter.
	 * @since 4.3.0 1. Deprecated.
	 *              2. Removed memoization.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function use_facebook_tags() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->use_facebook_tags()', '4.3.0' );

		/**
		 * @since 3.1.4
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param bool $use_facebook
		 */
		return \apply_filters_deprecated(
			'the_seo_framework_use_facebook_tags',
			[
				(bool) \tsf()->get_option( 'facebook_tags' ),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_generators',
		);
	}

	/**
	 * Renders Article Publishing Time meta tag.
	 *
	 * @since 2.2.2
	 * @since 2.8.0 Returns empty on product pages.
	 * @since 3.0.0 1. Now checks for 0000 timestamps.
	 *              2. Now uses timestamp formats.
	 *              3. Now uses GMT time.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Article Publishing Time meta tag.
	 */
	public function article_published_time() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->article_published_time()', '4.3.0' );

		if ( ! $tsf->output_published_time() ) return '';

		$id            = Query::get_the_real_id();
		$post_date_gmt = \get_post( $id )->post_date_gmt ?? '0000-00-00 00:00:00';

		if ( '0000-00-00 00:00:00' === $post_date_gmt )
			return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param string $time The article published time.
		 * @param int    $id   The current page or term ID.
		 */
		$time = (string) \apply_filters_deprecated(
			'the_seo_framework_publishedtime_output',
			[
				$tsf->gmt2date( $tsf->get_timestamp_format(), $post_date_gmt ),
				$id,
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $time ? \The_SEO_Framework\Interpreters\Meta::render( [
			'property' => 'article:published_time',
			'content'  => $time,
		] ) : '';
	}

	/**
	 * Renders Article Modified Time meta tag.
	 *
	 * @since 2.2.2
	 * @since 2.7.0 Listens to Query::get_the_real_id() instead of WordPress Core ID determination.
	 * @since 2.8.0 Returns empty on product pages.
	 * @since 3.0.0 1. Now checks for 0000 timestamps.
	 *              2. Now uses timestamp formats.
	 * @since 4.1.4 No longer renders the Open Graph Updated Time meta tag.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 * @see og_updated_time()
	 *
	 * @return string The Article Modified Time meta tag
	 */
	public function article_modified_time() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->article_modified_time()', '4.3.0' );

		if ( ! $tsf->output_modified_time() ) return '';

		$time = $tsf->get_modified_time();

		return $time ? \The_SEO_Framework\Interpreters\Meta::render( [
			'property' => 'article:modified_time',
			'content'  => $time,
		] ) : '';
	}

	/**
	 * Determines if modified time should be used in the current query.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 Removed caching.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function output_modified_time() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->output_modified_time()', '4.3.0' );

		if ( 'article' !== $tsf->get_og_type() )
			return false;

		return (bool) $tsf->get_option( 'post_modify_time' );
	}

	/**
	 * Determines if published time should be used in the current query.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 Removed caching.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function output_published_time() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->output_published_time()', '4.3.0' );

		if ( 'article' !== $tsf->get_og_type() )
			return false;

		return (bool) $tsf->get_option( 'post_publish_time' );
	}

	/**
	 * Returns the current Twitter card type.
	 * Memoizes the return value.
	 *
	 * @since 2.8.2
	 * @since 3.1.0 Filter has been moved to generate_twitter_card_type()
	 * @since 4.3.0 1. Deprecated.
	 *              2. Removed memoization.
	 * @deprecated
	 *
	 * @return string The cached Twitter card.
	 */
	public function get_current_twitter_card_type() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_current_twitter_card_type()', '4.3.0' );

		return $tsf->generate_twitter_card_type();
	}

	/**
	 * Renders the Twitter Card type meta tag.
	 *
	 * @since 2.2.2
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Twitter Card meta tag.
	 */
	public function twitter_card() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->twitter_card()', '4.3.0' );

		if ( ! $tsf->use_twitter_tags() ) return '';

		$card = $tsf->get_current_twitter_card_type();

		return $card ? \The_SEO_Framework\Interpreters\Meta::render( [
			'name'    => 'twitter:card',
			'content' => $card,
		] ) : '';
	}

	/**
	 * Renders the Twitter Site meta tag.
	 *
	 * @since 2.2.2
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Twitter Site meta tag.
	 */
	public function twitter_site() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->twitter_site()', '4.3.0' );

		if ( ! $tsf->use_twitter_tags() ) return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param string $site The Twitter site owner tag.
		 * @param int    $id   The current page or term ID.
		 */
		$site = (string) \apply_filters_deprecated(
			'the_seo_framework_twittersite_output',
			[
				$tsf->get_option( 'twitter_site' ),
				Query::get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $site ? \The_SEO_Framework\Interpreters\Meta::render( [
			'name'    => 'twitter:site',
			'content' => $site,
		] ) : '';
	}

	/**
	 * Renders The Twitter Creator meta tag.
	 *
	 * @since 2.2.2
	 * @since 2.9.3 No longer has a fallback to twitter:site:id
	 *              @link https://dev.twitter.com/cards/getting-started
	 * @since 3.0.0 Now uses author meta data.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Twitter Creator or Twitter Site ID meta tag.
	 */
	public function twitter_creator() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->twitter_creator()', '4.3.0' );

		if ( ! $tsf->use_twitter_tags() ) return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param string $creator The Twitter page creator.
		 * @param int    $id      The current page or term ID.
		 */
		$creator = (string) \apply_filters_deprecated(
			'the_seo_framework_twittercreator_output',
			[
				$tsf->get_current_post_author_meta_item( 'twitter_page' ) ?: $tsf->get_option( 'twitter_creator' ),
				Query::get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $creator ? \The_SEO_Framework\Interpreters\Meta::render( [
			'name'    => 'twitter:creator',
			'content' => $creator,
		] ) : '';
	}

	/**
	 * Renders Twitter Title meta tag.
	 *
	 * @since 2.2.2
	 * @since 3.0.4 No longer uses \tsf()->title_from_cache()
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 * @uses \tsf()->get_twitter_title()
	 *
	 * @return string The Twitter Title meta tag.
	 */
	public function twitter_title() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->twitter_title()', '4.3.0' );

		if ( ! $tsf->use_twitter_tags() ) return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param string $title The generated Twitter title.
		 * @param int    $id    The current page or term ID.
		 */
		$title = (string) \apply_filters_deprecated(
			'the_seo_framework_twittertitle_output',
			[
				$tsf->get_twitter_title(),
				Query::get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $title ? \The_SEO_Framework\Interpreters\Meta::render( [
			'name'    => 'twitter:title',
			'content' => $title,
		] ) : '';
	}

	/**
	 * Renders Twitter Description meta tag.
	 *
	 * @since 2.2.2
	 * @since 3.0.4 No longer uses \tsf()->description_from_cache()
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 * @uses \tsf()->get_twitter_description()
	 *
	 * @return string The Twitter Description meta tag.
	 */
	public function twitter_description() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->twitter_adescription()', '4.3.0' );

		if ( ! $tsf->use_twitter_tags() ) return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param string $description The generated Twitter description.
		 * @param int    $id          The current page or term ID.
		 */
		$description = (string) \apply_filters_deprecated(
			'the_seo_framework_twitterdescription_output',
			[
				$tsf->get_twitter_description(),
				Query::get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $description ? \The_SEO_Framework\Interpreters\Meta::render( [
			'name'    => 'twitter:description',
			'content' => $description,
		] ) : '';
	}

	/**
	 * Renders Twitter Image meta tag.
	 *
	 * @since 2.2.2
	 * @since 4.1.2 Now forwards the `multi_og_image` option to the generator. Although
	 *              it'll always use just one image, we read this option so we'll only
	 *              use a single cache instance internally with the generator.
	 * @since 4.2.8 Removed support for the long deprecated `twitter:image:height` and `twitter:image:width`.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Twitter Image meta tag.
	 */
	public function twitter_image() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->twitter_image()', '4.3.0' );

		if ( ! $tsf->use_twitter_tags() ) return '';

		$output = '';

		foreach ( $tsf->get_image_details_from_cache( ! $tsf->get_option( 'multi_og_image' ) ) as $image ) {
			$output .= \The_SEO_Framework\Interpreters\Meta::render( [
				'name'    => 'twitter:image',
				'content' => $image['url'],
			] );

			if ( $image['alt'] ) {
				$output .= \The_SEO_Framework\Interpreters\Meta::render( [
					'name'    => 'twitter:image:alt',
					'content' => $image['alt'],
				] );
			}

			// Only grab a single image. Twitter grabs the final (less favorable) image otherwise.
			break;
		}

		return $output;
	}

	/**
	 * Determines whether we can use Twitter tags on the front-end.
	 * Memoizes the return value.
	 *
	 * @since 2.6.0
	 * @since 2.8.2 Now also considers Twitter card type output.
	 * @since 3.1.4 Added filter.
	 * @since 4.3.0 1. Deprecated.
	 *              2. Removed memoization.
	 *              3. Removed test for card type.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function use_twitter_tags() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->use_twitter_tags()', '4.3.0' );

		/**
		 * @since 3.1.4
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param bool $use_twitter_card
		 */
		return \apply_filters_deprecated(
			'the_seo_framework_use_twitter_tags',
			[
				(bool) $tsf->get_option( 'twitter_tags' ),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_generators',
		);
	}

	/**
	 * Renders LD+JSON Schema.org scripts.
	 *
	 * @uses \tsf()->render_ld_json_scripts()
	 *
	 * @since 1.2.0
	 * @since 3.1.0 No longer returns early on search, 404 or preview.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The LD+json Schema.org scripts.
	 */
	public function ld_json() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->ld_json()', '4.3.0' );

		/**
		 * @since 2.6.0
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param string $json The JSON output. Must be escaped.
		 * @param int    $id   The current page or term ID.
		 */
		return (string) \apply_filters_deprecated(
			'the_seo_framework_ldjson_scripts',
			[
				\tsf()->render_ld_json_scripts(),
				Query::get_the_real_id(),
			],
			'4.3.0 of The SEO Framework'
		);
	}
}
