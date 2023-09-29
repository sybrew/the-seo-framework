<?php
/**
 * @package The_SEO_Framework\Classes\Internal\Deprecated
 * @subpackage The_SEO_Framework\Debug\Deprecated
 */

namespace The_SEO_Framework\Internal;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

// Precautionary.
use function \The_SEO_Framework\{
	memo,
	umemo,
};

use function \The_SEO_Framework\Utils\normalize_generation_args;

// Precautionary.
use \The_SEO_Framework\Data,
	\The_SEO_Framework\Helper\Query;

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

/**
 * Class The_SEO_Framework\Internal\Deprecated
 *
 * Contains all deprecated methods of `\tsf()`
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
	 * @since 4.3.0 Deprecated.
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
	 * @since 4.3.0 Deprecated.
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_post_type_real_id()', '4.3.0', 'tsf()->query()->get_post_type_real_id()' );

		return $tsf->query()->get_post_type_real_id( $post );
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_admin_post_type()', '4.3.0', 'tsf()->query()->get_admin_post_type()' );

		return $tsf->query()->get_admin_post_type();
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_post_types_from_taxonomy()', '4.3.0', 'tsf()->taxonomies()->get_post_types_from_taxonomy()' );

		return $tsf->taxonomies()->get_post_types_from_taxonomy( $taxonomy );
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_the_real_id()', '4.3.0', 'tsf()->query()->get_the_real_id()' );

		return $tsf->query()->get_the_real_id( $use_cache );
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_the_real_admin_id()', '4.3.0', 'tsf()->query()->get_the_real_admin_id()' );

		return $tsf->query()->get_the_real_admin_id();
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_the_front_page_id()', '4.3.0', 'tsf()->query()->get_the_front_page_id()' );

		return $tsf->query()->get_the_front_page_id();
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_admin_term_id()', '4.3.0', 'tsf()->query()->get_admin_term_id()' );

		return $tsf->query()->get_admin_term_id();
	}

	/**
	 * Returns the current taxonomy, if any.
	 * Memoizes the return value.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 1. Now works in the admin.
	 *              2. Added caching.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The queried taxonomy type.
	 */
	public function get_current_taxonomy() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_current_taxonomy()', '4.3.0', 'tsf()->query()->get_current_taxonomy()' );

		return $tsf->query()->get_current_taxonomy();
	}

	/**
	 * Returns the current post type, if any.
	 *
	 * @since 4.1.4
	 * @since 4.3.0 1. Deprecated.
	 *              2. Now falls back to the current post type instead erroneously to a boolean.
	 *              3. Now memoizes the return value.
	 * @deprecated
	 *
	 * @return string The queried post type.
	 */
	public function get_current_post_type() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_current_post_type()', '4.3.0', 'tsf()->query()->get_current_post_type()' );

		return $tsf->query()->get_current_post_type();
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_attachment()', '4.3.0', 'tsf()->query()->is_attachment()' );

		return $tsf->query()->is_attachment( $attachment );
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_attachment_admin()', '4.3.0', 'tsf()->query()->is_attachment_admin()' );

		return $tsf->query()->is_attachment_admin();
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_singular_archive()', '4.3.0', 'tsf()->query()->is_singular_archive()' );

		return $tsf->query()->is_singular_archive( $post );
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_archive()', '4.3.0', 'tsf()->query()->is_archive()' );

		return $tsf->query()->is_archive();
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_archive_admin()', '4.3.0', 'tsf()->query()->is_archive_admin()' );

		return $tsf->query()->is_archive_admin();
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_term_edit()', '4.3.0', 'tsf()->query()->is_term_edit()' );

		return $tsf->query()->is_term_edit();
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_post_edit()', '4.3.0', 'tsf()->query()->is_post_edit()' );

		return $tsf->query()->is_post_edit();
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_wp_lists_edit()', '4.3.0', 'tsf()->query()->is_wp_lists_edit()' );

		return $tsf->query()->is_wp_lists_edit();
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_profile_edit()', '4.3.0', 'tsf()->query()->is_profile_edit()' );

		return $tsf->query()->is_profile_edit();
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_author()', '4.3.0', 'tsf()->query()->is_author()' );

		return $tsf->query()->is_author( $author );
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_home()', '4.3.0', 'tsf()->query()->is_blog()' );

		return $tsf->query()->is_blog( $post );
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_home_as_page()', '4.3.0', 'tsf()->query()->is_blog_as_page()' );

		return $tsf->query()->is_blog_as_page( $post );
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_category()', '4.3.0', 'tsf()->query()->is_category()' );

		return $tsf->query()->is_category( $category );
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_category_admin()', '4.3.0', 'tsf()->query()->is_category_admin()' );

		return $tsf->query()->is_category_admin();
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_real_front_page()', '4.3.0', 'tsf()->query()->is_real_front_page()' );

		return $tsf->query()->is_real_front_page();
	}

	/**
	 * Checks for front page by input ID without engaging into the query.
	 *
	 * @NOTE This doesn't check for anomalies in the query.
	 * So, don't use this to test user-engaged WordPress queries, ever.
	 * WARNING: This will lead to **FALSE POSITIVES** for Date, CPTA, Search, and other archives.
	 *
	 * @since 3.2.2
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $id The tested ID.
	 * @return bool
	 */
	public function is_real_front_page_by_id( $id ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_real_front_page_by_id()', '4.3.0', 'tsf()->query()->is_real_front_page_by_id()' );

		return $tsf->query()->is_real_front_page_by_id( $id );
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_page()', '4.3.0', 'tsf()->query()->is_page()' );

		return $tsf->query()->is_page( $page );
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_page_admin()', '4.3.0', 'tsf()->query()->is_page_admin()' );

		return $tsf->query()->is_page_admin();
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_preview()', '4.3.0', 'tsf()->query()->is_preview()' );

		return $tsf->query()->is_preview();
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_search()', '4.3.0', 'tsf()->query()->is_search()' );

		return $tsf->query()->is_search();
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_single()', '4.3.0', 'tsf()->query()->is_single()' );

		return $tsf->query()->is_single( $post );
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_single_admin()', '4.3.0', 'tsf()->query()->is_single_admin()' );

		return $tsf->query()->is_single_admin();
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_singular()', '4.3.0', 'tsf()->query()->is_singular()' );

		return $tsf->query()->is_singular( $post_types );
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_singular_admin()', '4.3.0', 'tsf()->query()->is_singular_admin()' );

		return $tsf->query()->is_singular_admin();
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_static_frontpage()', '4.3.0', 'tsf()->query()->is_static_frontpage()' );

		return $tsf->query()->is_static_frontpage( $id );
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_tag()', '4.3.0', 'tsf()->query()->is_tag()' );

		return $tsf->query()->is_tag( $tag );
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_tag_admin()', '4.3.0', 'tsf()->query()->is_tag_admin()' );

		return $tsf->query()->is_tag_admin();
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_tax()', '4.3.0', 'tsf()->query()->is_tax()' );

		return $tsf->query()->is_tax( $taxonomy, $term );
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_shop()', '4.3.0', 'tsf()->query()->is_shop()' );

		return $tsf->query()->is_shop( $post );
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_product()', '4.3.0', 'tsf()->query()->is_product()' );

		return $tsf->query()->is_product( $post );
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_product_admin()', '4.3.0', 'tsf()->query()->is_product_admin()' );

		return $tsf->query()->is_product_admin();
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_year()', '4.3.0', 'tsf()->query()->is_year()' );

		return $tsf->query()->is_year();
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_ssl()', '4.3.0', 'tsf()->query()->is_ssl()' );

		return $tsf->query()->is_ssl();
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
	 *
	 * @param bool $secure Whether to ignore the use of the second (insecure) parameter.
	 * @return bool
	 */
	public function is_seo_settings_page( $secure = true ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_seo_settings_page()', '4.3.0', 'tsf()->query()->is_seo_settings_page()' );

		return $tsf->query()->is_seo_settings_page( $secure );
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_menu_page()', '4.3.0', 'tsf()->query()->is_menu_page()' );

		return $tsf->query()->is_menu_page( $pagehook, $pageslug );
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->page()', '4.3.0', 'tsf()->query()->page()' );

		return $tsf->query()->page();
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->paged()', '4.3.0', 'tsf()->query()->paged()' );

		return $tsf->query()->paged();
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->numpages()', '4.3.0', 'tsf()->query()->numpages()' );

		return $tsf->query()->numpages();
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_multipage()', '4.3.0', 'tsf()->query()->is_multipage()' );

		return $tsf->query()->is_multipage();
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_sitemap()', '4.3.0', 'tsf()->query()->is_sitemap()' );

		return $tsf->query()->is_sitemap( $set );
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

		return \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
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
	 *
	 * @return string The description meta tag.
	 */
	public function the_description() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->the_description()', '4.3.0' );

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @since 4.3.0 Deprecated.
		 * @deprecated
		 * @param string $description The generated description.
		 * @param int    $id          The page or term ID.
		 */
		$description = (string) \apply_filters_deprecated(
			'the_seo_framework_description_output',
			[
				$tsf->get_description(),
				$tsf->query()->get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $description ? \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
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

		$meta = $tsf->robots()->get_meta();

		return $meta ? \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
			'name'    => 'robots',
			'content' => $meta,
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
	 *
	 * @return string The Canonical URL meta tag.
	 */
	public function canonical() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->canonical()', '4.3.0' );

		$_url = $tsf->get_current_canonical_url();

		/**
		 * @since 2.6.5
		 * @since 4.3.0 Deprecated.
		 * @deprecated
		 * @param string $url The canonical URL. Must be escaped.
		 * @param int    $id  The current page or term ID.
		 */
		$url = (string) \apply_filters_deprecated(
			'the_seo_framework_rel_canonical_output',
			[
				$_url,
				$tsf->query()->get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		// If the page should not be indexed, consider removing the canonical URL.
		if ( \in_array( 'noindex', $tsf->robots()->generate_meta(), true ) ) {
			// If the URL is filtered, don't empty it.
			// If a custom canonical URL is set, don't empty it.
			if ( $url === $_url && ! $tsf->has_custom_canonical_url() ) {
				$url = '';
			}
		}

		return $url ? \The_SEO_Framework\Interpreters\Meta::render( // Lacking import OK.
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
	 *
	 * @return string The Shortlink meta tag.
	 */
	public function shortlink() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->advanced_query_protection()', '4.3.0' );

		/**
		 * @since 2.6.0
		 * @since 4.3.0 Deprecated.
		 * @deprecated
		 * @param string $url The generated shortlink URL.
		 * @param int    $id  The current post or term ID.
		 */
		$url = (string) \apply_filters_deprecated(
			'the_seo_framework_shortlink_output',
			[
				$tsf->get_shortlink(),
				$tsf->query()->get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $url ? \The_SEO_Framework\Interpreters\Meta::render( // Lacking import OK.
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
	 *
	 * @return string The Prev/Next Paged URL meta tags.
	 */
	public function paged_urls() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->paged_urls()', '4.3.0' );

		[ $next, $prev ] = $tsf->uri()->get_paged_urls();
		$id              = $tsf->query()->get_the_real_id();

		/**
		 * @since 2.6.0
		 * @since 4.3.0 Deprecated.
		 * @deprecated
		 * @param string $next The next-page URL.
		 * @param int    $id   The current post or term ID.
		 */
		$next = (string) \apply_filters_deprecated(
			'the_seo_framework_paged_url_output_next',
			[
				$next,
				$id,
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);
		/**
		 * @since 2.6.0
		 * @since 4.3.0 Deprecated.
		 * @deprecated
		 * @param string $next The previous-page URL.
		 * @param int    $id   The current post or term ID.
		 */
		$prev = (string) \apply_filters_deprecated(
			'the_seo_framework_paged_url_output_prev',
			[
				$prev,
				$id,
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		$output  = $prev ? \The_SEO_Framework\Interpreters\Meta::render( // Lacking import OK.
			[
				'rel'  => 'prev',
				'href' => $prev,
			],
			'link'
		) : '';
		$output .= $next ? \The_SEO_Framework\Interpreters\Meta::render( // Lacking import OK.
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

		$theme_color = Data\Plugin::get_option( 'theme_color' );

		return $theme_color ? \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
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
		 * @since 4.3.0 Deprecated.
		 * @deprecated
		 * @param string $code The Google verification code.
		 * @param int    $id   The current post or term ID.
		 */
		$code = (string) \apply_filters_deprecated(
			'the_seo_framework_googlesite_output',
			[
				Data\Plugin::get_option( 'google_verification' ),
				Query::get_the_real_id(),
			]
		);

		return $code ? \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
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
		 * @since 4.3.0 Deprecated.
		 * @deprecated
		 * @param string $code The Bing verification code.
		 * @param int    $id   The current post or term ID.
		 */
		$code = (string) \apply_filters_deprecated(
			'the_seo_framework_bingsite_output',
			[
				Data\Plugin::get_option( 'bing_verification' ),
				Query::get_the_real_id(),
			]
		);

		return $code ? \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
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
		 * @since 4.3.0 Deprecated.
		 * @deprecated
		 * @param string $code The Yandex verification code.
		 * @param int    $id   The current post or term ID.
		 */
		$code = (string) \apply_filters_deprecated(
			'the_seo_framework_yandexsite_output',
			[
				Data\Plugin::get_option( 'yandex_verification' ),
				Query::get_the_real_id(),
			]
		);

		return $code ? \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
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
		 * @since 4.3.0 Deprecated.
		 * @deprecated
		 * @param string $code The Baidu verification code.
		 * @param int    $id   The current post or term ID.
		 */
		$code = (string) \apply_filters_deprecated(
			'the_seo_framework_baidusite_output',
			[
				Data\Plugin::get_option( 'baidu_verification' ),
				Query::get_the_real_id(),
			]
		);

		return $code ? \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
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
		 * @since 4.3.0 Deprecated.
		 * @deprecated
		 * @param string $code The Pinterest verification code.
		 * @param int    $id   The current post or term ID.
		 */
		$code = (string) \apply_filters_deprecated(
			'the_seo_framework_pintsite_output',
			[
				Data\Plugin::get_option( 'pint_verification' ),
				Query::get_the_real_id(),
			]
		);

		return $code ? \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
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
		 * @since 4.3.0 Deprecated.
		 * @deprecated
		 * @param bool $use_open_graph
		 */
		return \apply_filters_deprecated(
			'the_seo_framework_use_og_tags',
			[
				(bool) Data\Plugin::get_option( 'og_tags' ),
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
		 * @since 4.3.0 Deprecated.
		 * @deprecated
		 * @param string $title The generated Open Graph title.
		 * @param int    $id    The page or term ID.
		 */
		$title = (string) \apply_filters_deprecated(
			'the_seo_framework_ogtitle_output',
			[
				$tsf->get_open_graph_title(),
				$tsf->query()->get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $title ? \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
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
		 * @since 4.3.0 Deprecated.
		 * @deprecated
		 * @param string $description The generated Open Graph description.
		 * @param int    $id          The page or term ID.
		 */
		$description = (string) \apply_filters_deprecated(
			'the_seo_framework_ogdescription_output',
			[
				$tsf->get_open_graph_description(),
				$tsf->query()->get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $description ? \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
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
		 * @since 4.3.0 Deprecated.
		 * @deprecated
		 * @param string $locale The generated locale field.
		 * @param int    $id     The page or term ID.
		 */
		$locale = (string) \apply_filters_deprecated(
			'the_seo_framework_oglocale_output',
			[
				$tsf->open_graph()->get_supported_locales(),
				$tsf->query()->get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $locale ? \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
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

		$type = $tsf->open_graph()->get_type();

		return $type ? \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
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

		$multi = (bool) Data\Plugin::get_option( 'multi_og_image' );

		foreach ( $tsf->get_image_details( null, ! $multi ) as $image ) {
			$output .= \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
				'property' => 'og:image',
				'content'  => $image['url'],
			] );

			if ( $image['height'] && $image['width'] ) {
				$output .= \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
					'property' => 'og:image:width',
					'content'  => $image['width'],
				] );
				$output .= \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
					'property' => 'og:image:height',
					'content'  => $image['height'],
				] );
			}

			if ( $image['alt'] ) {
				$output .= \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
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
		 * @since 4.3.0 Deprecated.
		 * @deprecated
		 * @param string $sitename The generated Open Graph site name.
		 * @param int    $id       The page or term ID.
		 */
		$sitename = (string) \apply_filters_deprecated(
			'the_seo_framework_ogsitename_output',
			[
				$tsf->data()->blog()->get_public_blogname(),
				$tsf->query()->get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $sitename ? \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
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
	 *
	 * @return string The Open Graph URL meta tag.
	 */
	public function og_url() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->og_url()', '4.3.0' );

		if ( ! $tsf->use_og_tags() ) return '';

		/**
		 * @since 2.9.3
		 * @since 4.3.0 Deprecated.
		 * @deprecated
		 * @param string $url The canonical/Open Graph URL. Must be escaped.
		 * @param int    $id  The current page or term ID.
		 */
		$url = (string) \apply_filters_deprecated(
			'the_seo_framework_ogurl_output',
			[
				$tsf->get_current_canonical_url(),
				$tsf->query()->get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $url ? \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
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

		return $time ? \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
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
		if ( 'article' !== $tsf->open_graph()->get_type() ) return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @since 4.3.0 Deprecated.
		 * @deprecated
		 * @param string $facebook_page The generated Facebook author page URL.
		 * @param int    $id            The current page or term ID.
		 */
		$facebook_page = (string) \apply_filters_deprecated(
			'the_seo_framework_facebookauthor_output',
			[
				$tsf->data()->plugin()->user()->get_current_post_author_meta_item( 'facebook_page' ) ?: Data\Plugin::get_option( 'facebook_author' ),
				$tsf->query()->get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $facebook_page ? \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
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
		if ( 'article' !== $tsf->open_graph()->get_type() ) return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @since 4.3.0 Deprecated.
		 * @deprecated
		 * @param string $publisher The Facebook publisher page URL.
		 * @param int    $id        The current page or term ID.
		 */
		$publisher = (string) \apply_filters_deprecated(
			'the_seo_framework_facebookpublisher_output',
			[
				Data\Plugin::get_option( 'facebook_publisher' ),
				$tsf->query()->get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $publisher ? \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
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
		 * @since 4.3.0 Deprecated.
		 * @deprecated
		 * @param string $app_id The Facebook app ID.
		 * @param int    $id     The current page or term ID.
		 */
		$app_id = (string) \apply_filters_deprecated(
			'the_seo_framework_facebookappid_output',
			[
				Data\Plugin::get_option( 'facebook_appid' ),
				$tsf->query()->get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data', // var_dump() delete me?
		);

		return $app_id ? \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
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
		 * @since 4.3.0 Deprecated.
		 * @deprecated
		 * @param bool $use_facebook
		 */
		return \apply_filters_deprecated(
			'the_seo_framework_use_facebook_tags',
			[
				(bool) Data\Plugin::get_option( 'facebook_tags' ),
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

		$id            = $tsf->query()->get_the_real_id();
		$post_date_gmt = \get_post( $id )->post_date_gmt ?? '0000-00-00 00:00:00';

		if ( '0000-00-00 00:00:00' === $post_date_gmt )
			return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @since 4.3.0 Deprecated.
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

		return $time ? \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
			'property' => 'article:published_time',
			'content'  => $time,
		] ) : '';
	}

	/**
	 * Renders Article Modified Time meta tag.
	 *
	 * @since 2.2.2
	 * @since 2.7.0 Listens to $tsf->query()->get_the_real_id() instead of WordPress Core ID determination.
	 * @since 2.8.0 Returns empty on product pages.
	 * @since 3.0.0 1. Now checks for 0000 timestamps.
	 *              2. Now uses timestamp formats.
	 * @since 4.1.4 No longer renders the Open Graph Updated Time meta tag.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Article Modified Time meta tag
	 */
	public function article_modified_time() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->article_modified_time()', '4.3.0' );

		if ( ! $tsf->output_modified_time() ) return '';

		$time = $tsf->get_modified_time();

		return $time ? \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
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

		if ( 'article' !== $tsf->open_graph()->get_type() )
			return false;

		return (bool) Data\Plugin::get_option( 'post_modify_time' );
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

		if ( 'article' !== $tsf->open_graph()->get_type() )
			return false;

		return (bool) Data\Plugin::get_option( 'post_publish_time' );
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

		return $tsf->twitter()->get_card_type();
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

		return $card ? \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
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
		 * @since 4.3.0 Deprecated.
		 * @deprecated
		 * @param string $site The Twitter site owner tag.
		 * @param int    $id   The current page or term ID.
		 */
		$site = (string) \apply_filters_deprecated(
			'the_seo_framework_twittersite_output',
			[
				Data\Plugin::get_option( 'twitter_site' ),
				$tsf->query()->get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $site ? \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
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
		 * @since 4.3.0 Deprecated.
		 * @deprecated
		 * @param string $creator The Twitter page creator.
		 * @param int    $id      The current page or term ID.
		 */
		$creator = (string) \apply_filters_deprecated(
			'the_seo_framework_twittercreator_output',
			[
				$tsf->data()->plugin()->user()->get_current_post_author_meta_item( 'twitter_page' ) ?: Data\Plugin::get_option( 'twitter_creator' ),
				$tsf->query()->get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $creator ? \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
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
		 * @since 4.3.0 Deprecated.
		 * @deprecated
		 * @param string $title The generated Twitter title.
		 * @param int    $id    The current page or term ID.
		 */
		$title = (string) \apply_filters_deprecated(
			'the_seo_framework_twittertitle_output',
			[
				$tsf->get_twitter_title(),
				$tsf->query()->get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $title ? \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
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
		 * @since 4.3.0 Deprecated.
		 * @deprecated
		 * @param string $description The generated Twitter description.
		 * @param int    $id          The current page or term ID.
		 */
		$description = (string) \apply_filters_deprecated(
			'the_seo_framework_twitterdescription_output',
			[
				$tsf->get_twitter_description(),
				$tsf->query()->get_the_real_id(),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $description ? \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
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

		foreach ( $tsf->get_image_details( null, ! Data\Plugin::get_option( 'multi_og_image' ) ) as $image ) {
			$output .= \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
				'name'    => 'twitter:image',
				'content' => $image['url'],
			] );

			if ( $image['alt'] ) {
				$output .= \The_SEO_Framework\Interpreters\Meta::render( [ // Lacking import OK.
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
		 * @since 4.3.0 Deprecated.
		 * @deprecated
		 * @param bool $use_twitter_card
		 */
		return \apply_filters_deprecated(
			'the_seo_framework_use_twitter_tags',
			[
				(bool) Data\Plugin::get_option( 'twitter_tags' ),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_generators',
		);
	}

	/**
	 * Merges arrays distinctly, much like `array_merge()`, but then for multidimensionals.
	 * Unlike PHP's `array_merge_recursive()`, this method doesn't convert non-unique keys as sequential.
	 *
	 * @link <https://3v4l.org/9pnW1#v8.1.8> Test it here.
	 *
	 * @since 4.1.4
	 * @since 4.2.7 1. Now supports a single array entry without causing issues.
	 *              2. Reduced number of opcodes by roughly 27% by reworking it.
	 *              3. Now no longer throws warnings with qubed+ arrays.
	 *              4. Now no longer prevents scalar values overwriting arrays.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param array ...$arrays The arrays to merge. The rightmost array's values are dominant.
	 * @return array The merged arrays.
	 */
	public function array_merge_recursive_distinct( ...$arrays ) {
		\tsf()->_deprecated_function(
			'tsf()->array_merge_recursive_distinct()',
			'4.3.0',
			'function \The_SEO_Framework\Utils\array_merge_recursive_distinct()'
		);
		return \The_SEO_Framework\Utils\array_merge_recursive_distinct( ...$arrays );
	}

	/**
	 * Returns an array of the collected robots meta assertions.
	 *
	 * This only works when generate_robots_meta()'s $options value was given:
	 * The_SEO_Framework\ROBOTS_ASSERT (0b100);
	 *
	 * @since 4.2.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return array
	 */
	public function retrieve_robots_meta_assertions() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->retrieve_robots_meta_assertions()', '4.3.0', 'tsf()->robots()->get_collected_meta_assertions()' );

		return $tsf->query()->get_collected_meta_assertions();
	}

	/**
	 * Returns the robots meta array.
	 * Memoizes the return value.
	 *
	 * @since 3.2.4
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return array
	 */
	public function get_robots_meta() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_robots_meta()', '4.3.0', 'tsf()->robots()->get_meta()' );

		return explode( ',', $tsf->robots()->get_meta() );
	}

	/**
	 * Returns the `noindex`, `nofollow`, `noarchive` robots meta code array.
	 *
	 * @since 4.1.4
	 * @since 4.2.0 1. Now offloads metadata generation to an actual generator.
	 *              2. Now supports the `$args['pta']` index.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args    The query arguments. Accepts 'id', 'tax', and 'pta'.
	 * @param null|array $get     The robots types to retrieve. Leave null to get all. Set array to pick: {
	 *    'noindex', 'nofollow', 'noarchive', 'max_snippet', 'max_image_preview', 'max_video_preview'
	 * }
	 * @param int <bit>  $options The options level. {
	 *    0 = 0b000: Ignore nothing. Collect no assertions. (Default front-end.)
	 *    1 = 0b001: Ignore protection. (\The_SEO_Framework\ROBOTS_IGNORE_PROTECTION)
	 *    2 = 0b010: Ignore post/term setting. (\The_SEO_Framework\ROBOTS_IGNORE_SETTINGS)
	 *    4 = 0b100: Collect assertions. (\The_SEO_Framework\ROBOTS_ASSERT)
	 * }
	 * @return array Only values actualized for display: {
	 *    string index : string value
	 * }
	 */
	public function generate_robots_meta( $args = null, $get = null, $options = 0b00 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->generate_robots_meta()', '4.3.0', 'tsf()->robots()->generate_meta()' );

		return $tsf->robots()->generate_meta( $args, $get, $options );
	}

	/**
	 * Determines if the post type has a robots value set.
	 *
	 * @since 3.1.0
	 * @since 4.0.5 The `$post_type` fallback now uses a real query ID, instead of `$GLOBALS['post']`;
	 *              mitigating issues with singular-archives pages (blog, shop, etc.).
	 * @since 4.1.1 Now tests for not empty, instead of isset. We no longer support PHP 5.4 since v4.0.0.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $type      Accepts 'noindex', 'nofollow', 'noarchive'.
	 * @param string $post_type The post type, optional. Leave empty to autodetermine type.
	 * @return bool True if noindex, nofollow, or noarchive is set; false otherwise.
	 */
	public function is_post_type_robots_set( $type, $post_type = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_post_type_robots_set()', '4.3.0', 'tsf()->robots()->is_post_type_robots_set()' );

		return $tsf->robots()->is_post_type_robots_set( $type, $post_type );
	}

	/**
	 * Determines if the taxonomy has a robots value set.
	 *
	 * @since 4.1.0
	 * @since 4.1.1 Now tests for not empty, instead of isset. We no longer support PHP 5.4 since v4.0.0.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $type     Accepts 'noindex', 'nofollow', 'noarchive'.
	 * @param string $taxonomy The taxonomy, optional. Leave empty to autodetermine type.
	 * @return bool True if noindex, nofollow, or noarchive is set; false otherwise.
	 */
	public function is_taxonomy_robots_set( $type, $taxonomy = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_taxonomy_robots_set()', '4.3.0', 'tsf()->robots()->is_taxonomy_robots_set()' );

		return $tsf->robots()->is_taxonomy_robots_set( $type, $taxonomy ?: null );
	}

	/**
	 * Determines whether the main query supports custom SEO.
	 *
	 * @since 4.0.0
	 * @since 4.0.2 Now tests for an existing post/term ID when on singular/term pages.
	 * @since 4.0.3 Can now assert empty categories again by checking for taxonomy support.
	 * @since 4.2.4 Added detection for AJAX, Cron, JSON, and REST queries (they're not supported as SEO-able queries).
	 * @since 4.3.0 1. Removed detection for JSON(P) and XML type requests, because these cannot be assumed as legitimate.
	 *              2. Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function query_supports_seo() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->query_supports_seo()', '4.3.0', 'tsf()->query()->utils()->query_supports_seo()' );

		return $tsf->query()->utils()->query_supports_seo();
	}

	/**
	 * Determines when paged/page is exploited.
	 * Memoizes the return value.
	 *
	 * Google is acting "smart" nowadays, and follows everything that remotely resembles a link. Therefore, unintentional
	 * queries can occur in WordPress. WordPress deals with this well, alas, the query parser (WP_Query::parse_query)
	 * doesn't rectify the mixed signals it receives. Instead, it only sanitizes it, resulting in a combobulated mess.
	 * Ultimately, this leads to non-existing blog archives, among other failures.
	 *
	 * Example 1: `/?p=nonnumeric` will cause an issue. We will see a non-existing blog page. `is_home` is true, but
	 * `page_id` leads to 0 while the database expects the blog page to be another page. So, `is_posts_page` is
	 * incorrectly false. This is mitigated via the canonical URL, but that MUST output, thus overriding otherwise chosen
	 * and expected behavior.
	 *
	 * Example 2: `/page/2/?p=nonnumeric` will cause a bigger issue. What happens is that `is_home` will again be true,
	 * but so will `is_paged`. `paged` will be set to `2` (as per example URL). The page ID will again be set to `0`,
	 * which is completely false. The canonical URL will be malformed. Even more so, Google can ignore the canonical URL,
	 * so we MUST output noindex.
	 *
	 * Example 3: `/page/2/?X=nonnumeric` will also cause the same issues as in example 2. Where X can be:
	 * `page_id`, `attachment_id`, `year`, `monthnum`, `day`, `w`, `m`, and of course `p`.
	 *
	 * Example 4: `/?hour=nonnumeric`, the same issue as Example 1. The canonical URL is malformed, noindex is set, and
	 * link relationships will be active. A complete mess. `minute` and `second` are also affected the same way.
	 *
	 * Example 5: `/page/2/?p=0`, this is the trickiest. It's indicative of a paginated blog, but also the homepage. When
	 * the homepage is not a blog, then this query is malformed. Otherwise, however, it's a good query.
	 *
	 * @since 4.0.5
	 * @since 4.2.7 1. Added detection `not_home_as_page`, specifically for query variable `search`.
	 *              2. Improved detection for `cat` and `author`, where the value may only be numeric above 0.
	 * @since 4.2.8 Now blocks any publicly registered variable requested to the home-as-page.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 * @global \WP_Query $wp_query
	 *
	 * @return bool Whether the query is (accidentally) exploited.
	 *              Defaults to false when `advanced_query_protection` option is disabled.
	 *              False when there's a query-ID found.
	 *              False when no custom query is set (for the homepage).
	 *              Otherwise, it performs query tests.
	 */
	public function is_query_exploited() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_query_exploited()', '4.3.0', 'tsf()->query()->utils()->is_query_exploited()' );

		return $tsf->query()->utils()->is_query_exploited();
	}

	/**
	 * Determines whether a page or blog is on front.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Removed caching.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function has_page_on_front() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->has_page_on_front()', '4.3.0', 'tsf()->query()->utils()->has_page_on_front()' );

		return $tsf->query()->utils()->has_page_on_front();
	}

	/**
	 * Detects if the current or inputted post type is supported and not disabled.
	 *
	 * @since 3.1.0
	 * @since 4.0.5 The `$post_type` fallback now uses a real query ID, instead of `$GLOBALS['post']`;
	 *              mitigating issues with singular-archives pages (blog, shop, etc.).
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $post_type Optional. The post type to check.
	 * @return bool
	 */
	public function is_post_type_supported( $post_type = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_post_type_supported()', '4.3.0', 'tsf()->post_types()->is_post_type_supported()' );

		return $tsf->post_types()->is_post_type_supported( $post_type );
	}

	/**
	 * Detects if the current or inputted post type's archive is supported and not disabled.
	 *
	 * @since 4.2.8
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $post_type Optional. The post type's archive to check.
	 * @return bool
	 */
	public function is_post_type_archive_supported( $post_type = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_post_type_archive_supported()', '4.3.0', 'tsf()->post_types()->is_post_type_archive_supported()' );

		return $tsf->post_types()->is_post_type_archive_supported( $post_type );
	}

	/**
	 * Checks (current) Post Type for having taxonomical archives.
	 * Memoizes the return value for the input argument.
	 *
	 * @since 2.9.3
	 * @since 4.0.5 The `$post_type` fallback now uses a real query ID, instead of `$GLOBALS['post']`;
	 *              mitigating issues with singular-archives pages (blog, shop, etc.).
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $post_type Optional. The post type to check.
	 * @return bool True when the post type has taxonomies.
	 */
	public function post_type_supports_taxonomies( $post_type = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->post_type_supports_taxonomies()', '4.3.0', 'tsf()->post_types()->post_type_supports_taxonomies()' );

		return $tsf->post_types()->post_type_supports_taxonomies( $post_type );
	}

	/**
	 * Returns a list of all supported post types with archives.
	 * Memoizes the return value.
	 *
	 * @since 4.2.0
	 * @since 4.2.8 Now filters via `tsf()->is_post_type_archive_supported()`.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string[] Supported post types with post type archive support.
	 */
	public function get_supported_post_type_archives() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_supported_post_type_archives()', '4.3.0', 'tsf()->post_types()->get_supported_post_type_archives()' );

		return $tsf->post_types()->get_supported_post_type_archives();
	}

	/**
	 * Gets all post types that have PTA and could possibly support SEO.
	 * Memoizes the return value.
	 *
	 * @since 4.2.0
	 * @since 4.2.8 Added filter `the_seo_framework_public_post_type_archives`.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string[] Public post types with post type archive support.
	 */
	public function get_public_post_type_archives() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_public_post_type_archives()', '4.3.0', 'tsf()->post_types()->get_public_post_type_archives()' );

		return $tsf->post_types()->get_public_post_type_archives();
	}

	/**
	 * Returns a list of all supported post types.
	 *
	 * @since 3.1.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string[] All supported post types.
	 */
	public function get_supported_post_types() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_supported_post_types()', '4.3.0', 'tsf()->post_types()->get_supported_post_types()' );

		return $tsf->post_types()->get_supported_post_types();
	}

	/**
	 * Determines if the post type is disabled from SEO all optimization.
	 *
	 * @since 3.1.0
	 * @since 3.1.2 Now is fiterable.
	 * @since 4.0.5 The `$post_type` fallback now uses a real query ID, instead of `$GLOBALS['post']`;
	 *              mitigating issues with singular-archives pages (blog, shop, etc.).
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $post_type Optional. The post type to check.
	 * @return bool True if disabled, false otherwise.
	 */
	public function is_post_type_disabled( $post_type = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_post_type_disabled()', '4.3.0', 'tsf()->post_types()->is_post_type_disabled()' );

		return $tsf->post_types()->is_post_type_disabled( $post_type );
	}

	/**
	 * Determines if the taxonomy supports The SEO Framework.
	 *
	 * Checks if at least one taxonomy objects post type supports The SEO Framework,
	 * and whether the taxonomy is public and rewritable.
	 *
	 * @since 4.0.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $taxonomy Optional. The taxonomy name.
	 * @return bool True if at least one post type in taxonomy isn't disabled.
	 */
	public function is_taxonomy_supported( $taxonomy = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_taxonomy_supported()', '4.3.0', 'tsf()->taxonomies()->is_taxonomy_supported()' );

		return $tsf->taxonomies()->is_taxonomy_supported( $taxonomy );
	}

	/**
	 * Returns a list of all supported taxonomies.
	 *
	 * @since 4.2.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string[] All supported taxonomies.
	 */
	public function get_supported_taxonomies() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_supported_taxonomies()', '4.3.0', 'tsf()->taxonomies()->get_supported_taxonomies()' );

		return $tsf->taxonomies()->get_supported_taxonomies();
	}

	/**
	 * Checks if the taxonomy isn't disabled, and that at least one taxonomy
	 * objects post type supports The SEO Framework.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 1. Now returns true if at least one post type for the taxonomy is supported.
	 *              2. Now uses `is_post_type_supported()` instead of `is_post_type_disabled()`.
	 * @since 4.1.0 1. Now also checks for the option `disabled_taxonomies`.
	 *              2. Now applies filters `the_seo_framework_taxonomy_disabled`.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $taxonomy The taxonomy name.
	 * @return bool True if at least one post type in taxonomy is supported.
	 */
	public function is_taxonomy_disabled( $taxonomy = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_taxonomy_disabled()', '4.3.0', 'tsf()->taxonomies()->is_taxonomy_disabled()' );

		return $tsf->taxonomies()->is_taxonomy_disabled( $taxonomy );
	}

	/**
	 * Determines if current query handles term meta.
	 *
	 * @since 3.0.0
	 * @since 4.0.0 No longer lists post type archives as term-meta capable. It's not a taxonomy.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_term_meta_capable() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_term_meta_capable()', '4.3.0', 'tsf()->query()->is_editable_term()' );

		return $tsf->query()->is_editable_term();
	}

	/**
	 * Returns an array of hierarchical post types.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now gets hierarchical post types that don't support rewrite, as well.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return array The public hierarchical post types.
	 */
	public function get_hierarchical_post_types() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_hierarchical_post_types()', '4.3.0', 'tsf()->post_types()->get_hierarchical_post_types()' );

		return $tsf->post_types()->get_hierarchical_post_types();
	}

	/**
	 * Returns an array of nonhierarchical post types.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now gets non-hierarchical post types that don't support rewrite, as well.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return array The public nonhierarchical post types.
	 */
	public function get_nonhierarchical_post_types() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_nonhierarchical_post_types()', '4.3.0', 'tsf()->post_types()->get_nonhierarchical_post_types()' );

		return $tsf->post_types()->get_nonhierarchical_post_types();
	}

	/**
	 * Returns hierarchical taxonomies for post type.
	 *
	 * @since 3.0.0
	 * @since 4.0.5 The `$post_type` fallback now uses a real query ID, instead of `$GLOBALS['post']`.
	 * @since 4.1.0 Now filters taxonomies more graciously--expecting broken taxonomies returned in the filter.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $get       Whether to get the names or objects.
	 * @param string $post_type The post type. Will default to current post type.
	 * @return object[]|string[] The post type taxonomy objects or names.
	 */
	public function get_hierarchical_taxonomies_as( $get = 'objects', $post_type = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_hierarchical_taxonomies_as()', '4.3.0', 'tsf()->taxonomies()->get_hierarchical_taxonomies_as()' );

		return $tsf->taxonomies()->get_hierarchical_taxonomies_as( $get, $post_type );
	}

	/**
	 * Returns the post type object label. Either plural or singular.
	 *
	 * @since 3.1.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $post_type The post type. Required.
	 * @param bool   $singular  Whether to get the singlural or plural name.
	 * @return string The Post Type name/label, if found.
	 */
	public function get_post_type_label( $post_type, $singular = true ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_post_type_label()', '4.3.0', 'tsf()->post_types()->get_post_type_label()' );

		return $tsf->post_types()->get_post_type_label( $post_type, $singular );
	}

	/**
	 * Returns the taxonomy type object label. Either plural or singular.
	 *
	 * @since 3.1.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $tax_type The taxonomy type. Required.
	 * @param bool   $singular Whether to get the singlural or plural name.
	 * @return string The Taxonomy Type name/label, if found.
	 */
	public function get_tax_type_label( $tax_type, $singular = true ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_tax_type_label()', '4.3.0', 'tsf()->post_types()->get_taxonomy_label()' );

		return $tsf->taxonomies()->get_taxonomy_label( $tax_type, $singular );
	}

	/**
	 * Generates the Open Graph type based on query status.
	 *
	 * @since 2.7.0
	 * @since 4.3.0 1. An image is no longer required to generate the 'article' type.
	 *              2. Deprecated.
	 * @deprecated
	 *
	 * @return string The Open Graph type.
	 */
	public function generate_og_type() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->generate_og_type()', '4.3.0', 'tsf()->open_graph()->get_type()' );

		return $tsf->open_graph()->get_type();
	}

	/**
	 * Returns Open Graph type value.
	 * Memoizes the return value.
	 *
	 * @since 2.8.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string
	 */
	public function get_og_type() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_og_type()', '4.3.0', 'tsf()->open_graph()->get_type()' );

		return $tsf->open_graph()->get_type();
	}

	/**
	 * Returns the redirect URL, if any.
	 *
	 * @since 4.1.4
	 * @since 4.2.0 1. Now supports the `$args['pta']` index.
	 *              2. Now redirects post type archives.
	 * @since 4.3.0 1. Now expects an ID before getting a post meta item.
	 *              2. Deprecated.
	 * @deprecated
	 *
	 * @param null|array $args The redirect URL arguments, leave null to autodetermine query : {
	 *    int    $id       The Post, Page or Term ID to generate the URL for.
	 *    string $taxonomy The taxonomy.
	 * }
	 * @return string The canonical URL if found, empty string otherwise.
	 */
	public function get_redirect_url( $args = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_redirect_url()', '4.3.0', 'tsf()->uri()->get_redirect_url()' );

		return $tsf->uri()->get_redirect_url( $args );
	}

	/**
	 * Fetches public blogname (site title).
	 * Memoizes the return value.
	 *
	 * Do not consider this function safe for printing!
	 *
	 * @since 2.5.2
	 * @since 4.2.0 1. Now listens to the new `site_title` option.
	 *              2. Now applies filters.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string $blogname The sanitized blogname.
	 */
	public function get_blogname() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_blogname()', '4.3.0', 'tsf()->data()->blog()->get_public_blog_name()' );

		return $tsf->data()->blog()->get_public_blog_name();
	}

	/**
	 * Fetches blogname (site title).
	 *
	 * Do not consider this function safe for printing!
	 *
	 * We use get_bloginfo( ..., 'display' ), even though it escapes needlessly, because it applies filters.
	 *
	 * @since 4.2.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string $blogname The sanitized blogname.
	 */
	public function get_filtered_raw_blogname() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_filtered_raw_blogname()', '4.3.0', 'tsf()->data()->blog()->get_filtered_blog_name()' );

		return $tsf->data()->blog()->get_filtered_blog_name();
	}

	/**
	 * Fetch blog description.
	 * Memoizes the return value.
	 *
	 * Do not consider this function safe for printing!
	 *
	 * We use get_bloginfo( ..., 'display' ), even though it escapes needlessly, because it applies filters.
	 *
	 * @since 2.5.2
	 * @since 3.0.0 No longer returns untitled when empty, instead, it just returns an empty string.
	 * @since 4.3.0 1. No longer memoizes the return value.
	 *              2. Deprecated.
	 * @deprecated
	 *
	 * @return string $blogname The sanitized blog description.
	 */
	public function get_blogdescription() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_blogdescription()', '4.3.0', 'tsf()->data()->blog()->get_filtered_blog_description()' );

		return $tsf->data()->blog()->get_filtered_blog_description();
	}

	/**
	 * Generates the Twitter Card type.
	 *
	 * @since 2.7.0
	 * @since 2.8.2 Now considers description output.
	 * @since 2.9.0 Now listens to $this->get_available_twitter_cards().
	 * @since 3.1.0 Now inherits filter `the_seo_framework_twittercard_output`.
	 * @since 4.1.4 Removed needless preprocessing of the option.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Twitter Card type. When no social title is found, an empty string will be returned.
	 */
	public function generate_twitter_card_type() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->generate_twitter_card_type()', '4.3.0', 'tsf()->twitter()->get_card_type()' );

		return $tsf->twitter()->get_card_type();
	}

	/**
	 * Returns array of Twitter Card Types
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return array Twitter Card types.
	 */
	public function get_twitter_card_types() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_twitter_card_types()', '4.3.0', 'tsf()->twitter()->get_supported_cards()' );

		return $tsf->twitter()->get_supported_cards();
	}

	/**
	 * Determines which Twitter cards can be used.
	 *
	 * @since 2.9.0
	 * @since 4.0.0 1. Now only asserts the social titles as required.
	 *              2. Now always returns an array, instead of a boolean (false) on failure.
	 * @since 4.2.0 1. No longer memoizes the return value.
	 *              2. No longer tests for the Twitter title.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return array False when it shouldn't be used. Array of available cards otherwise.
	 */
	public function get_available_twitter_cards() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_available_twitter_cards()', '4.3.0' );

		return [
			'summary'             => 'summary',
			'summary_large_image' => 'summary-large-image',
		];
	}

	/**
	 * Returns cached and parsed separator option.
	 *
	 * @since 2.3.9
	 * @since 3.1.0 1. Removed caching.
	 *              2. Removed escaping parameter.
	 * @since 4.0.0 No longer converts the `dash` separator option.
	 * @since 4.0.5 1. Now utilizes the predefined separator list, instead of guessing the output.
	 *              2. The default fallback value is now a hyphen.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The separator.
	 */
	public function get_separator() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_separator()', '4.3.0', 'tsf()->title()->get_separator()' );

		return $tsf->title()->get_separator();
	}

	/**
	 * Gets Title Separator.
	 * Memoizes the return value.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Separator, unescaped.
	 */
	public function get_title_separator() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_title_separator()', '4.3.0', 'tsf()->title()->get_separator()' );

		return $tsf->title()->get_separator();
	}

	/**
	 * List of title separators.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Is now filterable.
	 * @since 4.0.0 Removed the dash key.
	 * @since 4.0.5 Added back the hyphen.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return array Title separators.
	 */
	public function get_separator_list() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_separator_list()', '4.3.0', 'tsf()->title()->utils()->get_separator_list()' );

		return $tsf->title()->utils()->get_separator_list();
	}

	/**
	 * Trims the excerpt by word and determines sentence stops.
	 *
	 * Warning: Returns with entities encoded. The output is not safe for printing.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 1. Now uses smarter trimming.
	 *              2. Deprecated 2nd parameter.
	 *              3. Now has unicode support for sentence closing.
	 *              4. Now strips last three words when preceded by a sentence closing separator.
	 *              5. Now always leads with (inviting) dots, even if the excerpt is shorter than $max_char_length.
	 * @since 4.0.0 1. Now stops parsing earlier on failure.
	 *              2. Now performs faster queries.
	 *              3. Now maintains last sentence with closing punctuations.
	 * @since 4.0.5 1. Now decodes the excerpt input, improving accuracy, and so that HTML entities at
	 *                 the end won't be transformed into gibberish.
	 * @since 4.1.0 1. Now texturizes the excerpt input, improving accuracy with included closing & final punctuation support.
	 *              2. Now performs even faster queries, in most situations. (0.2ms/0.02ms total (worst/best) @ PHP 7.3/PCRE 11).
	 *                 Mind you, this method probably boots PCRE and wptexturize; so, it'll be slower than what we noted--it's
	 *                 overhead that otherwise WP, the theme, or other plugin would cause anyway. So, deduct that.
	 *              3. Now recognizes connector and final punctuations for preliminary sentence bounding.
	 *              4. Leading punctuation now excludes symbols, special annotations, opening brackets and quotes,
	 *                 and marks used in some latin languages like ¡¿.
	 *              5. Is now able to always strip leading punctuation.
	 *              6. It will now strip leading colon characters.
	 *              7. It will now stop counting trailing words towards new sentences when a connector, dash, mark, or ¡¿ is found.
	 *              8. Now returns encoded entities once more. So that the return value can be treated the same as anything else
	 *                 revolving around descriptions--preventing double transcoding like `&amp;amp; > &amp; > &` instead of `&amp;`.
	 * @since 4.1.5 1. The second parameter now accepts values again. From "current description length" to minimum accepted char length.
	 *              2. Can now return an empty string when the input string doesn't satisfy the minimum character length.
	 *              3. The third parameter now defaults to 4096, so no longer unexpected results are created.
	 *              4. Resolved some backtracking issues.
	 *              5. Resolved an issue where a character followed by punctuation would cause the match to fail.
	 * @since 4.2.0 Now enforces at least a character length of 1. This prevents needless processing.
	 * @since 4.2.7 Now considers floating numerics as one word.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * We use `[^\P{Po}\'\"]` because WordPress texturizes ' and " to fall under `\P{Po}`.
	 * This is perfect. Please have the courtesy to credit us when taking it. :)
	 *
	 * @param string $excerpt         The untrimmed excerpt. Expected not to contain any HTML operators.
	 * @param int    $min_char_length The minimum character length. Set to 0 to ignore the requirement.
	 *                                This is read as a SUGGESTION. Multibyte characters will create inaccuracies.
	 * @param int    $max_char_length At what point to shave off the excerpt.
	 * @return string The trimmed excerpt with encoded entities. Needs escaping prior printing.
	 */
	public function trim_excerpt( $excerpt, $min_char_length = 1, $max_char_length = 4096 ) {
		\tsf()->_deprecated_function( 'tsf()->trim_excerpt()', '4.3.0', 'function \The_SEO_Framework\Utils\clamp_sentence()' );
		return \The_SEO_Framework\Utils\clamp_sentence( $excerpt, $min_char_length, $max_char_length );
	}

	/**
	 * Fetches or parses the excerpt of the post.
	 *
	 * @since 1.0.0
	 * @since 2.8.2 Added 4th parameter for escaping.
	 * @since 3.1.0 1. No longer returns anything for terms.
	 *              2. Now strips plausible embeds URLs.
	 * @since 4.0.1 The second parameter `$id` now defaults to int 0, instead of an empty string.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $excerpt    The Excerpt.
	 * @param int    $id         The Post ID.
	 * @param null   $deprecated No longer used.
	 * @param bool   $escape     Whether to escape the excerpt.
	 * @return string The trimmed excerpt.
	 */
	public function get_excerpt_by_id( $excerpt = '', $id = 0, $deprecated = null, $escape = true ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_excerpt_by_id()', '4.3.0', 'tsf()->description()->excerpt()->get_excerpt_from_args()' );

		$excerpt = $excerpt ?: $tsf->description()->get_excerpt_from_args( [ 'id' => $id ] );

		return $escape ? $tsf->s_excerpt( $excerpt ) : $tsf->s_excerpt_raw( $excerpt );
	}

	/**
	 * Fetches excerpt from post excerpt or fetches the full post content.
	 * Determines if a page builder is used to return an empty string.
	 * Does not sanitize output.
	 *
	 * @since 2.5.2
	 * @since 2.6.6 Detects Page builders.
	 * @since 3.1.0 1. No longer returns anything for terms.
	 *              2. Now strips plausible embeds URLs.
	 * @since 4.0.1 Now fetches the real ID when no post is supplied.
	 *              Internally, this was never an issue.
	 * @since 4.2.8 1. Now tests for post type support of 'excerpt' before parsing the excerpt.
	 *              2. Now tests for post type support of 'editor' before parsing the content.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param \WP_Post|int|null $post The Post or Post ID. Leave null to get current post.
	 * @return string The excerpt.
	 */
	public function fetch_excerpt( $post = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->fetch_excerpt()', '4.3.0', 'tsf()->description()->excerpt()->get_excerpt()' );

		return $tsf->description()->excerpt()->get_excerpt(
			$post ? [ 'id' => \get_post( $post )->ID ?? '' ] : null
		);
	}

	/**
	 * Returns the post's modified time.
	 * Memoizes the return value.
	 *
	 * @since 4.1.4
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The current post's modified time
	 */
	public function get_modified_time() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_modified_time()', '4.3.0' );

		$id                = Query::get_the_real_id();
		$post_modified_gmt = \get_post( $id )->post_modified_gmt ?? '0000-00-00 00:00:00';

		return '0000-00-00 00:00:00' === $post_modified_gmt
			? ''
			/**
			 * @since 2.3.0
			 * @since 2.7.0 Added output within filter.
			 * @since 4.3.0 Deprecated.
			 * @deprecated
			 * @param string $time The article modified time.
			 * @param int    $id   The current page or term ID.
			 */
			: (string) \apply_filters_deprecated(
				'the_seo_framework_modifiedtime_output',
				[
					$tsf->gmt2date( $tsf->get_timestamp_format(), $post_modified_gmt ),
					$id,
				],
				'4.3.0 of The SEO Framework',
				'the_seo_framework_meta_render_data',
			);
	}

	/**
	 * Returns the meta description from custom fields. Falls back to autogenerated description.
	 *
	 * @since 3.0.6
	 * @since 3.1.0 The first argument now accepts an array, with "id" and "taxonomy" fields.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $escape Whether to escape the description.
	 * @return string The real description output.
	 */
	public function get_description( $args = null, $escape = true ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_description()', '4.3.0', 'tsf()->description()->get_description()' );

		return $tsf->description()->get_description( $args, $escape );
	}

	/**
	 * Returns the custom user-inputted description.
	 *
	 * @since 3.0.6
	 * @since 3.1.0 The first argument now accepts an array, with "id" and "taxonomy" fields.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $escape Whether to escape the description.
	 * @return string The custom field description.
	 */
	public function get_description_from_custom_field( $args = null, $escape = true ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_description_from_custom_field()', '4.3.0', 'tsf()->description()->get_custom_description()' );

		return $tsf->description()->get_custom_description( $args, $escape );
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
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $escape Whether to escape the description.
	 * @param string     $type   Type of description. Accepts 'search', 'opengraph', 'twitter'.
	 * @return string The generated description output.
	 */
	public function get_generated_description( $args = null, $escape = true, $type = 'search' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_generated_description()', '4.3.0', 'tsf()->description()->get_generated()' );

		return $tsf->description()->get_generated( $args, $escape, $type );
	}

	/**
	 * Returns the autogenerated Twitter meta description. Falls back to meta description.
	 *
	 * @since 3.0.4
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $escape Whether to escape the description.
	 * @return string The generated Twitter description output.
	 */
	public function get_generated_twitter_description( $args = null, $escape = true ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_generated_twitter_description()', '4.3.0', 'tsf()->twitter()->get_generated_description()' );

		return $tsf->twitter()->get_generated_description( $args, $escape );
	}

	/**
	 * Returns the autogenerated Open Graph meta description. Falls back to meta description.
	 *
	 * @since 3.0.4
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $escape Whether to escape the description.
	 * @return string The generated Open Graph description output.
	 */
	public function get_generated_open_graph_description( $args = null, $escape = true ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_generated_open_graph_description()', '4.3.0', 'tsf()->open_graph()->get_generated_description()' );

		return $tsf->open_graph()->get_generated_description( $args, $escape );
	}

	/**
	 * Returns supported social site locales.
	 *
	 * @since 4.2.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return array Valid social locales
	 */
	public function supported_social_locales() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->supported_social_locales()', '4.3.0', 'tsf()->open_graph()->get_supported_locales()' );

		return $tsf->open_graph()->get_supported_locales();
	}

	/**
	 * Returns the autogenerated Open Graph meta title. Falls back to meta title.
	 * Falls back to meta title.
	 *
	 * @since 3.0.4
	 * @since 3.1.0 The first parameter now expects an array.
	 * @since 4.1.0 Now appends the "social" argument when getting the title.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $escape Whether to escape the title.
	 * @return string The generated Open Graph Title.
	 */
	public function get_generated_open_graph_title( $args = null, $escape = true ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_generated_open_graph_title()', '4.3.0', 'tsf()->open_graph()->get_generated_title()' );

		// Discrepancy: The interface always hid this bug of using the wrong callback.
		// Let's keep this bug where it fetches the custom field title first.
		return $tsf->title()->get_title( $args, $escape, true ); // Discrepancy OK.
	}

	/**
	 * Returns the autogenerated Twitter meta title.
	 * Falls back to meta title.
	 *
	 * @since 3.0.4
	 * @since 3.1.0 The first parameter now expects an array.
	 * @since 4.1.0 Now appends the "social" argument when getting the title.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $escape Whether to escape the title.
	 * @return string The generated Twitter Title.
	 */
	public function get_generated_twitter_title( $args = null, $escape = true ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_generated_twitter_title()', '4.3.0', 'tsf()->twitter()->get_generated_title()' );

		// Discrepancy: The interface always hid this bug of using the wrong callback.
		// Let's keep this bug where it fetches the custom field title first.
		return $tsf->title()->get_title( $args, $escape, true ); // Discrepancy OK.
	}

	/**
	 * Determines whether to add or remove title protection prefixes.
	 *
	 * @since 3.2.4
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                         Leave null to autodetermine query.
	 * @return bool True when prefixes are allowed.
	 */
	public function use_title_protection( $args = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->use_title_protection()', '4.3.0', 'tsf()->title()->conditions()->use_title_protection_status()' );

		return $tsf->title()->conditions()->use_title_protection_status( $args );
	}

	/**
	 * Determines whether to add or remove title pagination additions.
	 *
	 * @since 3.2.4
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                         Leave null to autodetermine query.
	 * @return bool True when additions are allowed.
	 */
	public function use_title_pagination( $args = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->use_title_pagination()', '4.3.0', 'tsf()->title()->conditions()->use_title_pagination()' );

		return $tsf->title()->conditions()->use_title_pagination( $args );
	}

	/**
	 * Determines whether to add or remove title branding additions.
	 *
	 * @since 3.1.0
	 * @since 3.1.2 1. Added filter.
	 *              2. Added strict taxonomical check.
	 * @since 3.2.2 Now differentiates from query and parameter input.
	 * @since 4.1.0 Added the second $social parameter.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null  $args  The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                           Leave null to autodetermine query.
	 * @param bool|string $social Whether the title is meant for social display.
	 *                            Also accepts string 'og' and 'twitter' for future proofing.
	 * @return bool True when additions are allowed.
	 */
	public function use_title_branding( $args = null, $social = false ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->use_title_branding()', '4.3.0', 'tsf()->title()->conditions()->use_title_branding()' );

		return $tsf->title()->conditions()->use_title_branding( $args, $social );
	}

	/**
	 * Determines whether to use the autogenerated archive title prefix or not.
	 *
	 * @since 3.1.0
	 * @since 4.0.5 1: Added first parameter `$term`.
	 *              2: Added filter.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param \WP_Term|\WP_User|\WP_Post_Type|null $term The Term object. Leave null to autodermine query.
	 * @return bool
	 */
	public function use_generated_archive_prefix( $term = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->use_generated_archive_prefix()', '4.3.0', 'tsf()->title()->conditions()->use_generated_archive_prefix()' );

		return $tsf->title()->conditions()->use_generated_archive_prefix( $term );
	}

	/**
	 * Determines whether to add homepage tagline.
	 *
	 * @since 2.6.0
	 * @since 3.0.4 Now checks for `$this->get_home_title_additions()`.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function use_home_page_title_tagline() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->use_home_page_title_tagline()', '4.3.0', 'tsf()->title()->conditions()->use_title_branding()' );

		return $tsf->title()->conditions()->use_title_branding();
	}

	/**
	 * Determines whether to add the title tagline for the post.
	 *
	 * @since 3.1.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $id The post ID. Optional.
	 * @return bool
	 */
	public function use_singular_title_branding( $id = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->use_singular_title_branding()', '4.3.0', 'tsf()->title()->conditions()->use_title_branding()' );

		return $tsf->title()->conditions()->use_title_branding( [ 'id' => $id ] );
	}

	/**
	 * Determines whether to add the title tagline for the term.
	 *
	 * @since 4.0.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $id The term ID. Optional.
	 * @return bool
	 */
	public function use_taxonomical_title_branding( $id = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->use_taxonomical_title_branding()', '4.3.0', 'tsf()->title()->conditions()->use_title_branding()' );

		return $tsf->title()->conditions()->use_title_branding( [
			'id'  => $id ?: $tsf->query()->get_the_real_id(),
			'tax' => $tsf->query()->get_current_taxonomy(),
		] );
	}

	/**
	 * Determines whether to add the title tagline for the pta.
	 *
	 * @since 4.2.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $pta The post type archive. Optional.
	 * @return bool
	 */
	public function use_post_type_archive_title_branding( $pta = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->use_post_type_archive_title_branding()', '4.3.0', 'tsf()->title()->conditions()->use_title_branding()' );

		return $tsf->title()->conditions()->use_title_branding( [
			'pta' => $pta ?: $tsf->query()->get_current_post_type(),
		] );
	}

	/**
	 * Returns title separator location.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 1. Removed the first $seplocation parameter.
	 *              2. The first parameter is now $home
	 *              3. Removed caching.
	 *              4. Removed filters.
	 * @since 4.0.0 The homepage option's return value is now reversed from expected.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param bool $home The home separator location.
	 * @return string The separator location.
	 */
	public function get_title_seplocation( $home = false ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_title_seplocation()', '4.3.0', 'tsf()->title()->get_addition_location()' );

		if ( $home )
			return $tsf->get_home_title_seplocation();

		return $tsf->title()->get_addition_location();
	}

	/**
	 * Gets Title Seplocation for the homepage.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Removed first parameter.
	 * @since 4.0.0 Left is now right, and right is now left.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Seplocation for the homepage.
	 */
	public function get_home_title_seplocation() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_home_title_seplocation()', '4.3.0', 'tsf()->title()->get_addition_location_for_front_page()' );

		return $tsf->title()->get_addition_location_for_front_page();
	}

	/**
	 * Returns the homepage additions (tagline) from option or bloginfo, when set.
	 * Memoizes the return value.
	 *
	 * @since 4.1.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The trimmed tagline.
	 */
	public function get_home_title_additions() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_home_title_additions()', '4.3.0', 'tsf()->title()->get_addition_for_front_page()' );

		return $tsf->title()->get_addition_for_front_page();
	}

	/**
	 * Returns the custom user-inputted title.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 Moved the filter to a separated method.
	 * @since 4.1.0 Added the third $social parameter.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $escape Whether to escape the title.
	 * @param bool       $social Whether the title is meant for social display.
	 * @return string The custom field title.
	 */
	public function get_custom_field_title( $args = null, $escape = true, $social = false ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_custom_field_title()', '4.3.0', 'tsf()->title()->get_custom_title()' );

		return $tsf->title()->get_custom_title( $args, $escape, $social );
	}

	/**
	 * Returns the autogenerated meta title.
	 *
	 * @since 3.1.0
	 * @since 3.2.4 1. Added check for title protection.
	 *              2. Moved check for title pagination.
	 * @since 4.0.0 Moved the filter to a separated method.
	 * @since 4.1.0 Added the third $social parameter.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $escape Whether to escape the title.
	 * @param bool       $social Whether the title is meant for social display.
	 * @return string The generated title output.
	 */
	public function get_generated_title( $args = null, $escape = true, $social = false ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_generated_title()', '4.3.0', 'tsf()->title()->get_generated_title()' );

		return $tsf->title()->get_generated_title( $args, $escape, $social );
	}

	/**
	 * Returns the raw filtered custom field meta title.
	 *
	 * @since 4.0.0
	 * @since 4.2.0 1. The first parameter can now be voided.
	 *              2. The first parameter is now rectified, so you can leave out indexes.
	 *              3. Now supports the `$args['pta']` index.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                           Leave null to autodetermine query.
	 * @return string The raw generated title output.
	 */
	public function get_filtered_raw_custom_field_title( $args = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_filtered_raw_custom_field_title()', '4.3.0', 'tsf()->title()->get_bare_custom_title()' );

		return $tsf->title()->get_bare_custom_title( $args );
	}

	/**
	 * Returns the raw filtered autogenerated meta title.
	 *
	 * @since 4.0.0
	 * @since 4.2.0 1. The first parameter can now be voided.
	 *              2. The first parameter is now rectified, so you can leave out indexes.
	 *              3. Now supports the `$args['pta']` index.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                           Leave null to autodetermine query.
	 * @return string The raw generated title output.
	 */
	public function get_filtered_raw_generated_title( $args = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_filtered_raw_generated_title()', '4.3.0', 'tsf()->title()->get_bare_generated_title()' );

		return $tsf->title()->get_bare_generated_title( $args );
	}

	/**
	 * Returns the custom user-inputted title.
	 *
	 * This doesn't use the taxonomy arguments, because, wonderously, WordPress
	 * finally admits through their code that terms can be queried using only IDs.
	 *
	 * @since 3.1.0
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 * @internal But, feel free to use it.
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                         Leave null to autodetermine query.
	 * @return string The custom field title, if it exists.
	 */
	public function get_raw_custom_field_title( $args = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_raw_custom_field_title()', '4.3.0', 'tsf()->title()->get_bare_unfiltered_custom_title()' );

		return $tsf->title()->get_bare_unfiltered_custom_title( $args );
	}

	/**
	 * Generates a title, based on expected or current query, without additions or prefixes.
	 *
	 * @since 3.1.0
	 * @since 4.2.0 1. Added memoization.
	 *              2. Now supports the `$args['pta']` index.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                         Leave null to autodetermine query.
	 * @return string The generated title.
	 */
	public function get_raw_generated_title( $args = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_raw_generated_title()', '4.3.0', 'tsf()->title()->get_bare_unfiltered_generated_title()' );

		return $tsf->title()->get_bare_unfiltered_generated_title( $args );
	}

	/**
	 * Generates front page title.
	 *
	 * This is an alias of get_blogname(). The difference is that this is used for
	 * the front-page title output solely, whereas the other one has a mixed usage.
	 *
	 * @since 3.1.0
	 * @since 4.2.0 1. Now listens to the new `site_title` option.
	 *              2. Now applies filters.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The generated front page title.
	 */
	public function get_static_front_page_title() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_static_front_page_title()', '4.3.0', 'tsf()->title()->get_front_page_title()' );

		return $tsf->title()->get_front_page_title();
	}

	/**
	 * Returns the archive title. Also works in admin.
	 *
	 * @NOTE Taken from WordPress core. Altered to work for metadata and in admin.
	 *
	 * @since 3.1.0
	 * @since 4.0.2 Now asserts the correct tag taxonomy condition.
	 * @since 4.0.5 1: Now no longer uses `get_the_author()` to fetch the author's display name,
	 *                 but uses the provided term object instead.
	 *              2: The first parameter now accepts `\WP_User` objects.
	 * @since 4.1.2 Now supports WP 5.5 archive titles.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param \WP_Term|\WP_User|\WP_Post_Type|\WP_Error|null $object The Term object or error.
	 *                                                               Leave null to autodetermine query.
	 * @return string The generated archive title, not escaped.
	 */
	public function get_generated_archive_title( $object = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_generated_archive_title()', '4.3.0', 'tsf()->title()->get_archive_title()' );

		return $tsf->title()->get_archive_title( $object );
	}

	/**
	 * Returns the archive title items. Also works in admin.
	 *
	 * @NOTE Taken from WordPress core. Altered to work for metadata.
	 *
	 * @since 4.2.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param \WP_Term|\WP_User|\WP_Post_Type|null $object The Term object.
	 *                                                     Leave null to autodetermine query.
	 * @return String[$title,$prefix,$title_without_prefix] The generated archive title items, not escaped.
	 */
	public function get_raw_generated_archive_title_items( $object = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_raw_generated_archive_title_items()', '4.3.0', 'tsf()->title()->get_archive_title_list()' );

		return $tsf->title()->get_archive_title_list( $object );
	}

	/**
	 * Returns Post Title from ID.
	 *
	 * @NOTE Taken from WordPress core. Altered to work in the Admin area.
	 *
	 * @since 3.1.0
	 * @since 4.2.8 Now tests for post type support of 'title' before parsing the title.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|\WP_Post $id The Post ID or post object.
	 * @return string The generated post title.
	 */
	public function get_generated_single_post_title( $id = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_generated_single_post_title()', '4.3.0', 'tsf()->title()->get_post_title()' );

		return $tsf->title()->get_post_title( $id );
	}

	/**
	 * Fetches single term title.
	 *
	 * It can autodetermine the term; so, perform your checks prior calling.
	 *
	 * Taken from WordPress core. Altered to work in the Admin area.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 No longer redundantly tests the query, now only uses the term input or queried object.
	 * @since 4.0.2 Now asserts the correct tag taxonomy condition.
	 * @since 4.2.7 Now invokes proper filters when 'category' or 'tag' taxonomies are used.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param null|\WP_Term $term The term name, required in the admin area.
	 * @return string The generated single term title.
	 */
	public function get_generated_single_term_title( $term = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_generated_single_term_title()', '4.3.0', 'tsf()->title()->get_term_title()' );

		return $tsf->title()->get_term_title( $term );
	}

	/**
	 * Fetches single term title.
	 *
	 * @NOTE Taken from WordPress core. Altered to work in the Admin area.
	 *
	 * @since 3.1.0
	 * @since 4.2.0 Now actually works in the admin area, provided you forward $post_type.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $post_type The post type.
	 * @return string The generated post type archive title.
	 */
	public function get_generated_post_type_archive_title( $post_type = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_generated_post_type_archive_title()', '4.3.0', 'tsf()->title()->get_post_type_archive_title()' );

		return $tsf->title()->get_post_type_archive_title( $post_type );
	}

	/**
	 * Returns untitled title.
	 *
	 * @since 3.1.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The untitled title.
	 */
	public function get_static_untitled_title() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_static_untitled_title()', '4.3.0', 'tsf()->title()->get_untitled_title()' );

		return $tsf->title()->get_untitled_title();
	}

	/**
	 * Returns search title.
	 *
	 * @since 3.1.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The generated search title, partially escaped.
	 */
	public function get_generated_search_query_title() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_generated_search_query_title()', '4.3.0', 'tsf()->title()->get_search_query_title()' );

		return $tsf->title()->get_search_query_title();
	}

	/**
	 * Returns 404 title.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 No longer accepts parameters, nor has conditions.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The generated 404 title.
	 */
	public function get_static_404_title() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_static_404_title()', '4.3.0', 'tsf()->title()->get_404_title()' );

		return $tsf->title()->get_404_title();
	}

	/**
	 * Merges title branding, when allowed.
	 *
	 * @since 3.1.0
	 * @since 3.1.2 Added strict taxonomical check.
	 * @since 3.1.3 Fixed conditional logic.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string     $title The title. Passed by reference.
	 * @param array|null $args  The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                          Leave null to autodetermine query.
	 */
	public function merge_title_branding( &$title, $args = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->merge_title_branding()', '4.3.0', 'tsf()->title()->add_branding()' );

		$title = $tsf->title()->add_branding( $title, $args );
	}

	/**
	 * Merges pagination with the title, if paginated.
	 *
	 * @since 3.1.0
	 * @since 3.1.2 Now uses the registered default translation.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $title The title. Passed by reference.
	 */
	public function merge_title_pagination( &$title ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->merge_title_pagination()', '4.3.0', 'tsf()->title()->add_pagination()' );

		$title = $tsf->title()->add_pagination( $title );
	}

	/**
	 * Merges title protection prefixes.
	 *
	 * @since 3.1.0
	 * @since 3.1.2 Added strict taxonomical checks for title protection.
	 * @since 3.1.3 Fixed conditional logic.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 4.2.4 Resolved regression where $run-test was reversed (renamed to $merge).
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string     $title The title. Passed by reference.
	 * @param array|null $args  The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                          Leave null to autodetermine query.
	 * @return void
	 */
	public function merge_title_protection( &$title, $args = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->merge_title_protection()', '4.3.0', 'tsf()->title()->add_protection_status()' );

		$title = $tsf->title()->add_protection_status( $title, $args );
	}

	/**
	 * Determines if the given page has a custom canonical URL.
	 *
	 * @since 3.2.4
	 * @since 4.2.0 1. Now also detects canonical URLs for taxonomies.
	 *              2. Now also detects canonical URLs for PTAs.
	 *              3. Now supports the `$args['pta']` index.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param null|array $args The canonical URL arguments, leave null to autodetermine query : {
	 *    int    $id       The Post, Page or Term ID to generate the URL for.
	 *    string $taxonomy The taxonomy.
	 * }
	 * @return bool
	 */
	public function has_custom_canonical_url( $args = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->has_custom_canonical_url()', '4.3.0', 'tsf()->uri()->get_custom_canonical_url()' );

		return (bool) $tsf->uri()->get_custom_canonical_url( $args );
	}

	/**
	 * Returns the home URL. Created for the WordPress method is slow for it
	 * performs "set_url_scheme" calls slowly. We rely on this method for some
	 * plugins filter `home_url`.
	 * Memoized.
	 *
	 * @since 4.2.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The home URL.
	 */
	public function get_home_url() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_home_url()', '4.3.0', 'tsf->data()->blog()->get_front_page_url()' );

		return $tsf->data()->blog()->get_front_page_url();
	}

	/**
	 * Returns preferred $url scheme.
	 * Which can automatically be detected when not set, based on the site URL setting.
	 * Memoizes the return value.
	 *
	 * @since 3.0.0
	 * @since 4.0.0 Now gets the "automatic" scheme from the WordPress home URL.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The preferred URl scheme.
	 */
	public function get_preferred_scheme() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_preferred_scheme()', '4.3.0', 'tsf->uri()->utils()->get_preferred_url_scheme()' );

		return $tsf->uri()->utils()->get_preferred_url_scheme();
	}

	/**
	 * Sets URL to preferred URL scheme.
	 * Does not sanitize output.
	 *
	 * @since 2.8.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $url The URL to set scheme for.
	 * @return string The URL with the preferred scheme.
	 */
	public function set_preferred_url_scheme( $url ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->set_preferred_url_scheme()', '4.3.0', 'tsf->uri()->utils()->set_preferred_url_scheme()' );

		return $tsf->uri()->utils()->set_preferred_url_scheme( $url );
	}

	/**
	 * Detects site's URL scheme from site options.
	 * Falls back to is_ssl() when the hom misconfigured via wp-config.php
	 *
	 * NOTE: Some (insecure, e.g. SP) implementations for the `WP_HOME` constant, where
	 * the scheme is interpreted from the request, may cause this to be unreliable.
	 * We're going to ignore those edge-cases; they're doing it wrong.
	 *
	 * However, should we output a notification? Or let them suffer until they use Monitor to find the issue for them?
	 * Yea, Monitor's great for that. Gibe moni plos.
	 *
	 * @since 4.0.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The detected URl scheme, lowercase.
	 */
	public function detect_site_url_scheme() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->detect_site_url_scheme()', '4.3.0', 'uri()->utils()->detect_site_url_scheme()' );

		return $tsf->uri()->utils()->detect_site_url_scheme();
	}

	/**
	 * Sets URL scheme for input URL.
	 * WordPress core function, without filter.
	 *
	 * @since 2.4.2
	 * @since 3.0.0 $use_filter now defaults to false.
	 * @since 3.1.0 The third parameter ($use_filter) is now $deprecated.
	 * @since 4.0.0 Removed the deprecated parameter.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $url    Absolute url that includes a scheme.
	 * @param string $scheme Optional. Scheme to give $url. Currently 'http', 'https', 'login', 'login_post', 'admin', or 'relative'.
	 * @return string url with chosen scheme.
	 */
	public function set_url_scheme( $url, $scheme = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->set_url_scheme()', '4.3.0', 'uri()->utils()->set_url_scheme()' );

		return $tsf->uri()->utils()->set_url_scheme( $url, $scheme );
	}

	/**
	 * Makes a fully qualified URL by adding the scheme prefix.
	 * Always adds http prefix, not https.
	 *
	 * NOTE: Expects the URL to have either a scheme, or a relative scheme set.
	 *       Domain-relative URLs will not be parsed correctly.
	 *       '/path/to/folder/` will become `http:///path/to/folder/`
	 *
	 * @since 2.6.5
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $url Required the current maybe not fully qualified URL.
	 * @return string $url
	 */
	public function make_fully_qualified_url( $url ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->make_fully_qualified_url()', '4.3.0', 'uri()->utils()->make_fully_qualified_url()' );

		return $tsf->uri()->utils()->make_fully_qualified_url( $url );
	}

	/**
	 * Caches and returns the current URL.
	 * Memoizes the return value.
	 *
	 * @since 3.0.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The current URL.
	 */
	public function get_current_canonical_url() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_current_canonical_url()', '4.3.0', 'tsf()->uri()->get_canonical_url()' );

		return $tsf->uri()->get_canonical_url();
	}

	/**
	 * Caches and returns the current permalink.
	 * This link excludes any pagination. Great for structured data.
	 *
	 * Does not work for unregistered pages, like search, 404, date, author, and CPTA.
	 * Memoizes the return value.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 Now properly generates taxonomical URLs.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The current permalink.
	 */
	public function get_current_permalink() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_current_permalink()', '4.3.0', 'tsf()->uri()->get_generated_canonical_url()' );

		return $tsf->uri()->get_generated_canonical_url();
	}

	/**
	 * Caches and returns the homepage URL.
	 * Memoizes the return value.
	 *
	 * @since 3.0.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The home URL.
	 */
	public function get_homepage_permalink() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_homepage_permalink()', '4.3.0', 'tsf()->uri()->get_bare_front_page_canonical_url()' );

		return $tsf->uri()->get_bare_front_page_canonical_url();
	}

	/**
	 * Returns a canonical URL based on parameters.
	 * The URL will never be paginated.
	 *
	 * @since 3.0.0
	 * @since 4.0.0 Now preemptively fixes the generation arguments, for easier implementation.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 4.2.3 Marked for deprecation.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param array $args The canonical URL arguments : {
	 *    int    $id               The Post, Page or Term ID to generate the URL for.
	 *    string $taxonomy         The taxonomy.
	 *    string $pta              The pta.
	 *    bool   $get_custom_field Whether to get custom canonical URLs from user settings.
	 * }
	 * @return string The canonical URL, if any.
	 */
	public function create_canonical_url( $args = [] ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->create_canonical_url()', '4.3.0', 'tsf->uri()->get_custom_canonical_url()' );

		if ( empty( $args['get_custom_field'] ) )
			return $tsf->uri()->get_generated_canonical_url( $args ?: null );

		return $tsf->uri()->get_custom_canonical_url( $args ?: null );
	}

	/**
	 * Returns home canonical URL.
	 * Automatically adds pagination if the ID matches the query.
	 *
	 * @since 3.0.0
	 * @since 3.2.4 1. Now adds a slash to the home URL when it's a root URL.
	 *              2. Now skips slashing when queries have been appended to the URL.
	 *              3. Home-as-page pagination is now supported.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The home canonical URL.
	 */
	public function get_home_canonical_url() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_home_canonical_url()', '4.3.0', 'tsf->uri()->get_front_page_canonical_url()' );

		return $tsf->uri()->get_front_page_canonical_url();
	}

	/**
	 * Returns home canonical URL without query considerations.
	 *
	 * @since 4.2.0
	 * @since 4.2.2 Now adds a trailing slash if the URL is a root URL.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The home canonical URL without query considerations.
	 */
	public function get_raw_home_canonical_url() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_raw_home_canonical_url()', '4.3.0', 'tsf->uri()->get_bare_front_page_canonical_url()' );

		return $tsf->uri()->get_bare_front_page_canonical_url();
	}

	/**
	 * Returns singular canonical URL.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 Added WC Shop and WP Blog (as page) pagination integration via Query::paged().
	 * @since 3.2.4 Removed pagination support for singular posts, as the SEO attack is now mitigated via WordPress.
	 * @since 4.0.5 Now passes the `$id` to `is_singular_archive()`
	 * @since 4.2.0 1. Added memoization.
	 *              2. When the $id isn't set, the URL won't get tested for pagination issues.
	 * @since 4.2.3 Rectified pagination removal issue. No longer adds pagination when $post_id is null.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|null $post_id The page ID. Leave null to autodetermine.
	 * @return string The custom canonical URL, if any.
	 */
	public function get_singular_canonical_url( $post_id = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_singular_canonical_url()', '4.3.0', 'tsf()->uri()->get_singular_canonical_url()' );

		return $tsf->uri()->get_singular_canonical_url( $post_id );
	}

	/**
	 * Returns taxonomical canonical URL.
	 * Automatically adds pagination if the ID matches the query.
	 *
	 * @since 3.0.0
	 * @since 4.0.0 1. Renamed from "get_taxonomial_canonical_url" (note the typo)
	 *              2. Now works on the admin-screens.
	 * @since 4.2.0 1. Added memoization.
	 *              2. The parameters are now optional.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|null $term_id  The term ID. Leave null to autodetermine.
	 * @param string   $taxonomy The taxonomy. Leave empty to autodetermine.
	 * @return string The taxonomical canonical URL, if any.
	 */
	public function get_taxonomical_canonical_url( $term_id = null, $taxonomy = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_taxonomical_canonical_url()', '4.3.0', 'tsf()->uri()->get_taxonomical_canonical_url()' );

		return $tsf->uri()->get_taxonomical_canonical_url( $term_id, $taxonomy );
	}

	/**
	 * Returns post type archive canonical URL.
	 *
	 * @since 3.0.0
	 * @since 4.0.0 1. Deprecated first parameter as integer. Use strings or null.
	 *              2. Now forwards post type object calling to WordPress's function.
	 * @since 4.2.0 1. Now correctly adds pagination to the URL.
	 *              2. Removed argument type deprecation doing it wrong warning.
	 *
	 * @param null|string $post_type The post type archive's post type.
	 *                               Leave null to autodetermine query and allow pagination.
	 * @return string The post type archive canonical URL, if any.
	 */
	public function get_post_type_archive_canonical_url( $post_type = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_post_type_archive_canonical_url()', '4.3.0', 'tsf()->uri()->get_post_type_archive_canonical_url()' );

		return $tsf->uri()->get_post_type_archive_canonical_url( $post_type );
	}

	/**
	 * Returns author canonical URL.
	 * Automatically adds pagination if the ID matches the query.
	 *
	 * @since 3.0.0
	 * @since 4.2.0 1. The first parameter is now optional.
	 *              2. When the $id isn't set, the URL won't get tested for pagination issues.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|null $id The author ID. Leave null to autodetermine.
	 * @return string The author canonical URL, if any.
	 */
	public function get_author_canonical_url( $id = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_author_canonical_url()', '4.3.0', 'tsf()->uri()->get_author_canonical_url()' );

		return $tsf->uri()->get_author_canonical_url( $id );
	}

	/**
	 * Returns date canonical URL.
	 * Automatically adds pagination if the date input matches the query.
	 *
	 * @since 3.0.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $year  The year.
	 * @param int $month The month.
	 * @param int $day   The day.
	 * @return string The author canonical URL, if any.
	 */
	public function get_date_canonical_url( $year, $month = null, $day = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_date_canonical_url()', '4.3.0', 'tsf()->uri()->get_date_canonical_url()' );

		return $tsf->uri()->get_date_canonical_url( $year, $month, $day );
	}

	/**
	 * Returns search canonical URL.
	 * Automatically adds pagination if the input matches the query.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 1. The first parameter now defaults to null.
	 *              2. The search term is now matched with the input query if not set,
	 *                 instead of it being empty.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $search_query The search query. Mustn't be escaped.
	 *                             When left empty, the current query will be used.
	 * @return string The search link.
	 */
	public function get_search_canonical_url( $search_query = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_search_canonical_url()', '4.3.0', 'tsf()->uri()->get_search_canonical_url()' );

		return $tsf->uri()->get_search_canonical_url( $search_query );
	}

	/**
	 * Adds pagination to input URL.
	 *
	 * @since 4.2.3
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $url      The fully qualified URL.
	 * @param int    $page     The page number. Should be bigger than 1 to paginate.
	 * @param bool   $use_base Whether to use pagination base.
	 *                         If null, it will autodetermine.
	 *                         Should be true on archives and the homepage (blog and static!).
	 *                         False on singular post types.
	 * @return string The fully qualified URL with pagination.
	 */
	public function add_pagination_to_url( $url, $page = null, $use_base = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->add_pagination_to_url()', '4.3.0', 'tsf()->uri()->utils()->add_pagination_to_url()' );

		return $tsf->uri()->utils()->add_pagination_to_url( $url, $page, $use_base );
	}

	/**
	 * Adds pagination to input URL.
	 *
	 * @since 3.0.0
	 * @since 3.2.4 1. Now considers query arguments when using pretty permalinks.
	 *              2. The second and third parameters are now optional.
	 * @since 4.2.0 Now properly adds pagination to search links.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $url      The fully qualified URL.
	 * @param int    $page     The page number. Should be bigger than 1 to paginate.
	 * @param bool   $use_base Whether to use pagination base.
	 *                         If null, it will autodetermine.
	 *                         Should be true on archives and the homepage (blog and static!).
	 *                         False on singular post types.
	 * @return string The fully qualified URL with pagination.
	 */
	public function add_url_pagination( $url, $page = null, $use_base = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->add_url_pagination()', '4.3.0', 'tsf()->uri()->utils()->add_pagination_to_url()' );

		return $tsf->uri()->utils()->add_pagination_to_url( $url, $page, $use_base );
	}

	/**
	 * Removes pagination from input URL.
	 * The URL must match this query if no second parameter is provided.
	 *
	 * @since 3.0.0
	 * @since 3.2.4 1. Now correctly removes the pagination base on singular post types.
	 *              2. The second parameter now accepts null or a value.
	 *              3. The third parameter is now changed to $use_base, from the archive pagination number.
	 *              4. Now supports pretty permalinks with query parameters.
	 *              5. Is now public.
	 * @since 4.1.2 Now correctly reappends query when pagination isn't removed.
	 * @since 4.2.0 Now properly removes pagination from search links.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string    $url  The fully qualified URL to remove pagination from.
	 * @param int|null  $page The page number to remove. If null, it will get number from query.
	 * @param bool|null $use_base Whether to remove the pagination base.
	 *                            If null, it will autodetermine.
	 *                            Should be true on archives and the homepage (blog and static!).
	 *                            False on singular post types.
	 * @return string $url The fully qualified URL without pagination.
	 */
	public function remove_pagination_from_url( $url, $page = null, $use_base = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->remove_pagination_from_url()', '4.3.0', 'tsf->uri()->utils()->remove_pagination_from_url()' );

		return $tsf->uri()->utils()->remove_pagination_from_url( $url, $page, $use_base );
	}

	/**
	 * Generates Previous and Next links.
	 *
	 * @since 2.2.4
	 * @since 3.1.0 1. Now recognizes WC Shops and WP Blog pages as archival types.
	 *              2. Now sanitizes canonical URL according to permalink settings.
	 *              3. Removed second parameter. It was only a source of bugs.
	 *              4. Removed WordPress Core `get_pagenum_link` filter.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $next_prev Whether to get the previous or next page link.
	 *                          Accepts 'prev' and 'next'.
	 * @return string Escaped site Pagination URL
	 */
	public function get_paged_url( $next_prev ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_paged_url()', '4.3.0', 'tsf->uri()->get_paged_url()' );

		[ $next, $prev ] = $tsf->uri()->get_paged_urls();

		if ( 'next' === $next_prev )
			return $next;

		return $prev;
	}

	/**
	 * Generates Previous and Next links.
	 *
	 * @since 3.1.0
	 * @since 3.2.4 1. Now correctly removes the pagination base from singular URLs.
	 *              2. Now returns no URLs when a custom canonical URL is set.
	 * @since 4.1.0 Removed memoization.
	 * @since 4.1.2 1. Added back memoization.
	 *              2. Reduced needless canonical URL generation when it wouldn't be processed anyway.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return array Escaped site Pagination URLs: {
	 *    string 'prev'
	 *    string 'next'
	 * }
	 */
	public function get_paged_urls() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_paged_urls()', '4.3.0', 'tsf->uri()->get_paged_url()' );

		[ $next, $prev ] = $tsf->uri()->get_paged_urls();

		return compact( 'next', 'prev' );
	}

	/**
	 * Fetches home URL host. Like "wordpress.org".
	 * If this fails, you're going to have a bad time.
	 * Memoizes the return value.
	 *
	 * @since 2.7.0
	 * @since 2.9.2 1. Now considers port too.
	 *              2. Now uses get_home_url(), rather than get_option('home').
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The home URL host.
	 */
	public function get_home_host() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_home_host()', '4.3.0', 'tsf->data()->blog()->get_site_host()' );

		return $tsf->uri()->utils()->get_site_host();
	}

	/**
	 * Appends given query to given URL.
	 *
	 * @since 4.1.4
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $url   A fully qualified URL.
	 * @param string $query A fully qualified query taken from parse_url( $url, \PHP_URL_QUERY );
	 * @return string A fully qualified URL with appended $query.
	 */
	public function append_url_query( $url, $query = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->append_url_query()', '4.3.0', 'tsf->uri()->utils()->append_url_query()' );

		return $tsf->uri()->utils()->append_query_to_url( $url, $query );
	}

	/**
	 * Tests if input URL matches current domain.
	 *
	 * @since 2.9.4
	 * @since 4.1.0 Improved performance by testing an early match.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $url The URL to test. Required.
	 * @return bool true on match, false otherwise.
	 */
	public function matches_this_domain( $url ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->matches_this_domain()', '4.3.0', 'tsf->uri()->utils()->url_matches_blog_domain()' );

		return $tsf->uri()->utils()->url_matches_blog_domain( $url );
	}

	/**
	 * Makes a fully qualified URL from any input.
	 *
	 * @since 4.0.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $path Either the URL or path. Will always be transformed to the current domain.
	 * @param string $url  The URL to add the path to. Defaults to the current home URL.
	 * @return string $url
	 */
	public function convert_to_url_if_path( $path, $url = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->convert_to_url_if_path()', '4.3.0', 'tsf->uri()->utils()->convert_path_to_url()' );

		return $tsf->uri()->utils()->convert_path_to_url( $path, $url );
	}

	/**
	 * Returns singular custom field's canonical URL.
	 *
	 * @since 3.0.0
	 * @since 4.2.0 The first parameter is now optional.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|null $id The page ID.
	 * @return string The custom canonical URL, if any.
	 */
	public function get_singular_custom_canonical_url( $id = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_singular_custom_canonical_url()', '4.3.0', 'tsf->uri()->get_custom_canonical_url()' );

		return $tsf->get_post_meta_item( '_genesis_canonical_uri', $id ) ?: '';
	}

	/**
	 * Returns taxonomical custom field's canonical URL.
	 *
	 * @since 4.0.0
	 * @since 4.2.0 The first parameter is now optional.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $term_id The term ID.
	 * @return string The custom canonical URL, if any.
	 */
	public function get_taxonomical_custom_canonical_url( $term_id = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_taxonomical_custom_canonical_url()', '4.3.0', 'tsf->uri()->get_custom_canonical_url()' );

		return $tsf->data()->plugin()->term()->get_term_meta_item( 'canonical', $term_id ) ?: '';
	}

	/**
	 * Returns post type archive custom field's canonical URL.
	 *
	 * @since 4.2.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $pta The post type.
	 * @return string The custom canonical URL, if any.
	 */
	public function get_post_type_archive_custom_canonical_url( $pta = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_post_type_archive_custom_canonical_url()', '4.3.0', 'tsf->uri()->get_custom_canonical_url()' );

		return $tsf->data()->plugin()->pta()->get_post_type_archive_meta_item( 'canonical', $pta ) ?: '';
	}

	/**
	 * Generates shortlink URL.
	 *
	 * @since 2.2.2
	 * @since 3.1.0 1. No longer accepts $post_id input. Output's based on query only.
	 *              2. Shortened date archive URL length.
	 *              3. Removed query parameter collisions.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string|null Escaped site Shortlink URL.
	 */
	public function get_shortlink() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_shortlink()', '4.3.0', 'tsf->uri()->get_shortlink()' );

		return $tsf->uri()->get_shortlink_url();
	}

	/**
	 * Caches current Image URL in static variable.
	 * To be used on the front-end only.
	 *
	 * @since 2.2.2
	 * @since 2.7.0 $get_id parameter has been added.
	 * @since 4.0.0 Now uses the new image generator.
	 * @since 4.1.2 Now forwards the `multi_og_image` option to the generator. Although
	 *              it'll always use just one image, we read this option so we'll only
	 *              use a single cache instance internally with the generator.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The image URL.
	 */
	public function get_image_from_cache() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_image_from_cache()', '4.3.0', 'tsf()->get_first_valid_image()' );

		foreach ( $tsf->image()->get_image_details( null, true ) as $image ) {
			$url = $image['url'];
			if ( $url ) break;
		}

		return $url ?? '';
	}

	/**
	 * Returns the image details from cache.
	 * Only to be used within the loop, uses default parameters, inlucing the 'social' context.
	 * Memoizes the return value.
	 *
	 * @since 4.0.0
	 * @since 4.1.2 Added a $single parameter, which helps reduce processing power required.
	 *              This parameter might get deprecated when we start supporting PHP 7.1+ only.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param bool $single Whether to return at most a single array item.
	 * @return array[] The image details array, sequential: int => {
	 *    string url:    The image URL,
	 *    int    id:     The image ID,
	 *    int    width:  The image width in pixels,
	 *    int    height: The image height in pixels,
	 *    string alt:    The image alt tag,
	 * }
	 */
	public function get_image_details_from_cache( $single = false ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_image_details_from_cache()', '4.3.0', 'tsf()->get_image_details()' );

		return $tsf->get_image_details( null, $single );
	}

	/**
	 * Returns single custom field image details.
	 *
	 * @since 4.0.0
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $single Whether to fetch one image, or multiple. Unused, reserved.
	 * @return array The image details array, sequential: int => {
	 *    string url:    The image URL,
	 *    int    id:     The image ID,
	 *    int    width:  The image width in pixels,
	 *    int    height: The image height in pixels,
	 *    string alt:    The image alt tag,
	 * }
	 */
	public function get_custom_field_image_details( $args = null, $single = false ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_custom_field_image_details()', '4.3.0', 'tsf()->image()->get_custom_image_details()' );

		return $tsf->image()->get_custom_image_details( $args, $single );
	}

	/**
	 * Returns single or multiple generates image details.
	 *
	 * @since 4.0.0
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args    The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                            Leave null to autodetermine query.
	 * @param bool       $single  Whether to fetch one image, or multiple.
	 * @param string     $context The filter context. Default 'social'.
	 * @return array The image details array, sequential: int => {
	 *    string url:    The image URL,
	 *    int    id:     The image ID,
	 *    int    width:  The image width in pixels,
	 *    int    height: The image height in pixels,
	 *    string alt:    The image alt tag,
	 * }
	 */
	public function get_generated_image_details( $args = null, $single = false, $context = 'social' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_generated_image_details()', '4.3.0', 'tsf()->image()->get_generated_image_details()' );

		return $tsf->image()->get_generated_image_details( $args, $single, $context );
	}

	/**
	 * Adds image dimension and alt parameters to the input details, if any.
	 *
	 * @since 4.0.0
	 * @since 4.2.4 1. Now returns filesizes under index `filesize`.
	 *              2. No longer processes details when no `id` is given in `$details`.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param array  $details The image details array, associative: {
	 *    string url:    The image URL,
	 *    int    id:     The image ID,
	 * }
	 * @param string $size    The size of the image used.
	 * @return array The image details array, associative: {
	 *    string url:    The image URL,
	 *    int    id:     The image ID,
	 *    int    width:  The image width in pixels,
	 *    int    height: The image height in pixels,
	 *    string alt:    The image alt tag,
	 *    int    filesize: The image filesize in bytes,
	 * }
	 */
	public function merge_extra_image_details( $details, $size = 'full' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->merge_extra_image_details()', '4.3.0', 'tsf()->image()->merge_extra_image_details()' );

		return $tsf->image()->merge_extra_image_details( $details, $size );
	}

	/**
	 * Fetches image dimensions.
	 *
	 * @TODO shift parameters and deprecate using the third one.
	 * @since 4.0.0
	 * @since 4.2.4 1. No longer relies on `$url` to fetch the correct dimensions, improving performance significantly.
	 *              2. Renamed `$url` to `$depr`, without a deprecation notice added.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int    $src_id The source ID of the image.
	 * @param string $depr   Deprecated. Used to be the source URL of the image.
	 * @param string $size   The size of the image used.
	 * @return array The image dimensions, associative: {
	 *    int width:  The image width in pixels,
	 *    int height: The image height in pixels,
	 * }
	 */
	public function get_image_dimensions( $src_id, $depr, $size ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_image_dimensions()', '4.3.0', 'tsf()->image()->utils()->get_image_dimensions()' );

		return $tsf->image()->utils()->get_image_dimensions( $src_id, $size );
	}

	/**
	 * Fetches image dimensions.
	 *
	 * @since 4.0.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $src_id The source ID of the image.
	 * @return string The image alt tag
	 */
	public function get_image_alt_tag( $src_id ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_image_alt_tag()', '4.3.0', 'tsf()->image()->utils()->get_image_alt_tag()' );

		return $tsf->image()->utils()->get_image_alt_tag( $src_id );
	}

	/**
	 * Fetches image filesize in bytes. Requires an image (re)generated in WP 6.0 or later.
	 *
	 * @since 4.2.4
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int    $src_id The source ID of the image.
	 * @param string $size   The size of the image used.
	 * @return int The image filesize in bytes. Returns 0 for unprocessed/unprocessable image.
	 */
	public function get_image_filesize( $src_id, $size ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_image_filesize()', '4.3.0', 'tsf()->image()->utils()->get_image_filesize()' );

		return $tsf->image()->utils()->get_image_filesize( $src_id, $size );
	}

	/**
	 * Returns the largest acceptable image size's details.
	 * Skips the original image, which may also be acceptable.
	 *
	 * @since 4.0.2
	 * @since 4.2.4 Added parameter `$max_filesize` that filters images larger than it.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $id           The image ID.
	 * @param int $max_size     The largest acceptable dimension in pixels. Accounts for both width and height.
	 * @param int $max_filesize The largest acceptable filesize in bytes. Default 5MB (5242880).
	 * @return false|array Returns an array (url, width, height, is_intermediate), or false, if no image is available.
	 */
	public function get_largest_acceptable_image_src( $id, $max_size = 4096, $max_filesize = 5242880 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_largest_acceptable_image_src()', '4.3.0', 'tsf()->image()->utils()->get_largest_image_src()' );

		return $tsf->image()->utils()->get_largest_image_src( $id, $max_size, $max_filesize );
	}

	/**
	 * Returns the minimum role required to adjust settings.
	 *
	 * @since 3.0.0
	 * @since 4.1.0 Now uses the constant `THE_SEO_FRAMEWORK_SETTINGS_CAP` as a default return value.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The minimum required capability for SEO Settings.
	 */
	public function get_settings_capability() {
		\tsf()->_deprecated_function( 'tsf()->get_settings_capability()', '4.3.0', 'constant THE_SEO_FRAMEWORK_SETTINGS_CAP' );
		return \THE_SEO_FRAMEWORK_SETTINGS_CAP;
	}

	/**
	 * Determines if the current user can do settings.
	 * Not cached as it's imposing security functionality.
	 *
	 * @since 3.0.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function can_access_settings() {
		\tsf()->_deprecated_function( 'tsf()->can_access_settings()', '4.3.0', 'current_user_can( THE_SEO_FRAMEWORK_SETTINGS_CAP )' );
		return \current_user_can( \THE_SEO_FRAMEWORK_SETTINGS_CAP );
	}

	/**
	 * Echos the header meta and scripts.
	 *
	 * @since 1.0.0
	 * @since 2.8.0 Cache is busted on each new release.
	 * @since 3.0.0 Now converts timezone if needed.
	 * @since 3.1.0 1. Now no longer outputs anything on preview.
	 *              2. Now no longer outputs anything on blocked post types.
	 * @since 4.0.0 Now no longer outputs anything on Customizer.
	 * @since 4.0.4 1. Now sets timezone to UTC to fix WP 5.3 bug <https://core.trac.wordpress.org/ticket/48623>
	 *              2. Now always sets timezone regardless of settings, because, again, bug.
	 * @since 4.2.0 No longer sets timezone.
	 * @since 4.2.7 No longer marked as private.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 */
	public function html_output() {
		\tsf()->_deprecated_function( 'tsf()->html_output()', '4.3.0' );
		\The_SEO_Framework\Front\Meta\Head::print_wrap_and_tags(); // Lacking import OK.
	}

	/**
	 * Outputs all meta tags for the current query.
	 *
	 * @since 4.1.4
	 * @since 4.2.0 1. Now invokes two actions before and after output.
	 *              2. No longer rectifies timezones.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 */
	public function do_meta_output() {
		\tsf()->_deprecated_function( 'tsf()->do_meta_output()', '4.3.0' );
		\The_SEO_Framework\Front\Meta\Head::print_tags(); // Lacking import OK.
	}

	/**
	 * Holds default site options.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Now applies filters 'the_seo_framework_default_site_options'
	 * @since 4.0.0 `home_title_location` is now switched from right to left, or vice-versa.
	 * @since 4.2.4 `max_image_preview` now defaults to `large`, from `standard`, matching WordPress's default.
	 * @since 4.2.7 Added `auto_description_html_method`, defaults to `fast`.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return array Default site options.
	 */
	public function get_default_site_options() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_default_site_options()', '4.3.0', 'tsf()->data()->plugin()->setup()->get_default_options()' );

		return $tsf->data()->plugin()->setup()->get_default_options();
	}

	/**
	 * Holds warned site options array.
	 *
	 * @since 2.6.0
	 * @since 2.9.0 Removed all non-warned settings.
	 * @since 3.1.0 Now applies the "the_seo_framework_warned_site_options" filter.
	 * @since 4.1.0 Added robots' post type setting warnings.
	 * @since 4.1.2 Added `ping_use_cron_prerender`.
	 * @since 4.2.0 Now memoizes its return value.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return array $options.
	 */
	public function get_warned_site_options() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_warned_site_options()', '4.3.0', 'tsf()->data()->plugin()->setup()->get_warned_options()' );

		return $tsf->data()->plugin()->setup()->get_warned_options();
	}

	/**
	 * Return current option array.
	 * Memoizes the return value, can be bypassed and reset with second parameter.
	 *
	 * This method does NOT merge the default post options.
	 *
	 * @since 2.6.0
	 * @since 2.9.2 Added $use_current parameter.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $setting The setting key.
	 * @return array Options.
	 */
	public function get_all_options( $setting = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_all_options()', '4.3.0', 'tsf()->data()->plugin()->get_options()' );

		if ( ! $setting )
			return $tsf->data()->plugin()->get_options();

		/**
		 * @since 2.0.0
		 * @since 4.1.4 1. Now considers headlessness.
		 *              2. Now returns a 3rd parameter: boolean $headless.
		 *
		 * @param array  $settings The settings
		 * @param string $setting  The settings field.
		 * @param bool   $headless Whether the options are headless.
		 */
		return \apply_filters_ref_array(
			'the_seo_framework_get_options',
			[
				\get_option( $setting ),
				$setting,
				false,
			]
		);
	}

	/**
	 * Return Default SEO options from the SEO options array.
	 *
	 * @since 2.2.5
	 * @since 4.2.0 1. Now supports an option index as `$key`.
	 *              2. Removed second parameter (`$use_cache`).
	 *              3. Now always memoizes.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string|string[] $key Required. The option name, or a map of indexes.
	 * @return mixed The default option. Null if it's not registered.
	 */
	public function get_default_option( $key ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_default_option()', '4.3.0', 'tsf()->data()->plugin()->setup()->get_default_option()' );

		return $tsf->data()->plugin()->setup()->get_default_option( ...(array) $key );
	}

	/**
	 * Return Warned SEO options from the SEO options array.
	 *
	 * @since 4.2.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string|string[] $key Required. The option name, or a map of indexes.
	 * @return bool True if warning is registered. False otherwise.
	 */
	public function get_warned_option( $key ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_warned_option()', '4.3.0', 'tsf()->data()->plugin()->setup()->get_warned_option()' );

		return $tsf->data()->plugin()->setup()->get_warned_option( ...(array) $key );
	}

	/**
	 * Returns the option key for Post Type robots settings.
	 *
	 * @since 3.1.0
	 * @since 4.2.0 No longer sanitizes the input parameter.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $type Accepts 'noindex', 'nofollow', 'noarchive'.
	 * @return string
	 */
	public function get_robots_post_type_option_id( $type ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_robots_post_type_option_id()', '4.3.0', 'tsf()->data()->plugin()->helper()->get_robots_option_index()' );

		return $tsf->data()->plugin()->helper()->get_robots_option_index( 'post_type', $type );
	}

	/**
	 * Returns the option key for Taxonomy robots settings.
	 *
	 * @since 4.1.0
	 * @since 4.2.0 No longer sanitizes the input parameter.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $type Accepts 'noindex', 'nofollow', 'noarchive'.
	 * @return string
	 */
	public function get_robots_taxonomy_option_id( $type ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_robots_taxonomy_option_id()', '4.3.0', 'tsf()->data()->plugin()->helper()->get_robots_option_index()' );

		return $tsf->data()->plugin()->helper()->get_robots_option_index( 'taxonomy', $type );
	}

	/**
	 * Updates a single SEO option.
	 *
	 * Can return false if option is unchanged.
	 *
	 * @since 2.9.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $key   The option key.
	 * @param string $value The option value.
	 * @return bool True on success, false on failure.
	 */
	public function update_option( $key = '', $value = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->update_option()', '4.3.0', 'tsf()->data()->plugin()->update_option()' );

		return $tsf->data()->plugin()->update_option( $key, $value );
	}

	/**
	 * Allows bulk-updating of the SEO settings.
	 *
	 * @since 2.7.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string|array $new_option : {
	 *      if string: The string will act as a key for a new empty string option, e.g.,
	 *                 'sitemap_index' becomes ['sitemap_index' => '']
	 *      if array:  The option name(s) and value(s), e.g., ['sitemap_index' => 1]
	 * }
	 * @param string       $settings_field The Settings Field to update. Defaults
	 *                                     to The SEO Framework settings field.
	 * @return bool True on success. False on failure.
	 */
	public function update_settings( $new_option = '', $settings_field = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->update_settings()', '4.3.0', 'tsf()->data()->plugin()->update_option()' );

		if ( ! $settings_field )
			return $tsf->data()->plugin()->update_option( \is_array( $new_option ) ? $new_option : [ $new_option => '' ] );

		return \update_option(
			$settings_field,
			\wp_parse_args( $new_option, \get_option( $settings_field ) )
		);
	}

	/**
	 * Retrieves a single caching option.
	 *
	 * @since 3.1.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $key     The option key. Required.
	 * @param string $default The default cache value.
	 * @return mixed Cache value on success, $default if non-existent.
	 */
	public function get_static_cache( $key, $default = false ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_static_cache()', '4.3.0', 'tsf()->data()->plugin()->get_site_cache()' );

		return $tsf->data()->plugin()->get_site_cache( $key ) ?? $default;
	}

	/**
	 * Updates a single caching option.
	 *
	 * Can return false if option is unchanged.
	 *
	 * @since 3.1.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $key   The cache key. Required.
	 * @param string $value The cache value. Expected to be sanitized.
	 * @return bool True on success, false on failure.
	 */
	public function update_static_cache( $key, $value = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->update_static_cache()', '4.3.0', 'tsf()->data()->plugin()->update_site_cache()' );

		return $tsf->data()->plugin()->update_site_cache( $key, $value );
	}

	/**
	 * Returns the term meta item by key.
	 *
	 * @since 4.0.0
	 * @since 4.2.0 No longer accidentally returns an empty array on failure.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $item      The item to get.
	 * @param int    $term_id   The Term ID.
	 * @return mixed The term meta item. Null when not found.
	 */
	public function get_term_meta_item( $item, $term_id = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_term_meta_item()', '4.3.0', 'tsf()->data()->plugin()->term()->get_term_meta_item()' );

		return $tsf->data()->plugin()->term()->get_term_meta_item( $item, $term_id );
	}

	/**
	 * Returns term meta data from ID.
	 * Memoizes the return value for the current request.
	 *
	 * Returns Genesis 2.3.0+ data if no term meta data is set via compat module.
	 *
	 * @since 2.7.0
	 * @since 2.8.0 Added filter.
	 * @since 3.0.0 Added filter.
	 * @since 3.1.0 Deprecated filter.
	 * @since 4.0.0 1. Removed deprecated filter.
	 *              2. Now fills in defaults.
	 * @since 4.1.4 1. Removed deprecated filter.
	 *              2. Now considers headlessness.
	 * @since 4.2.0 Now returns an empty array when the term's taxonomy isn't supported.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $term_id The Term ID.
	 * @return array The term meta data.
	 */
	public function get_term_meta( $term_id ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_term_meta()', '4.3.0', 'tsf()->data()->plugin()->term()->get_term_meta()' );

		return $tsf->data()->plugin()->term()->get_term_meta( $term_id );
	}

	/**
	 * Returns an array of default term options.
	 *
	 * @since 2.7.0
	 * @since 3.1.0 This is now always used.
	 * @since 4.0.0 1. Added $term_id parameter.
	 *              2. Added 'redirect' value.
	 *              3. Added 'title_no_blog_name' value.
	 *              4. Removed 'saved_flag' value.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $term_id The term ID.
	 * @return array The Term Metadata default options.
	 */
	public function get_term_meta_defaults( $term_id = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_term_meta_defaults()', '4.3.0', 'tsf()->data()->plugin()->term()->get_term_meta_defaults()' );

		return $tsf->data()->plugin()->term()->get_term_meta_defaults( $term_id );
	}

	/**
	 * Updates single term meta value.
	 *
	 * Note that this method can be more resource intensive than you intend it to be,
	 * as it reprocesses all term meta.
	 *
	 * @since 4.0.0
	 * @since 4.0.2 1. Now tests for valid term ID in the term object.
	 *              2. Now continues using the filtered term object.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $item    The item to update.
	 * @param mixed  $value   The value the item should be at.
	 * @param int    $term_id Term ID.
	 */
	public function update_single_term_meta_item( $item, $value, $term_id ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->update_single_term_meta_item()', '4.3.0', 'tsf()->data()->plugin()->term()->update_single_term_meta_item()' );

		return $tsf->data()->plugin()->term()->update_single_term_meta_item( $item, $value, $term_id );
	}

	/**
	 * Updates term meta from input.
	 *
	 * @since 4.0.0
	 * @since 4.0.2 1. Now tests for valid term ID in the term object.
	 *              2. Now continues using the filtered term object.
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term Taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 * @param array  $data     The data to save.
	 */
	public function save_term_meta( $term_id, $tt_id, $taxonomy, $data ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->update_single_term_meta_item()', '4.3.0', 'tsf()->data()->plugin()->post()->update_single_term_meta_item()' );

		return $tsf->data()->plugin()->term()->save_term_meta( $term_id, $data );
	}

	/**
	 * Fetch latest public category ID.
	 * Memoizes the return value.
	 *
	 * @since 4.1.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return int Latest Category ID.
	 */
	public function get_latest_category_id() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_latest_category_id()', '4.3.0', 'tsf()->data()->term()->get_latest_category_id()' );

		return $tsf->data()->term()->get_latest_term_id( 'category' );
	}

	/**
	 * Tests whether term is populated. Also tests the child terms.
	 * Memoizes the return value.
	 *
	 * @since 4.2.8
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int    $term_id The term ID.
	 * @param string $taxonomy The term taxonomy.
	 * @return bool True when term or child terms are populated, false otherwise.
	 */
	public function is_term_populated( $term_id, $taxonomy ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_latest_category_id()', '4.3.0', 'tsf()->data()->term()->is_term_populated()' );

		return $tsf->data()->term()->is_term_populated( $term_id, $taxonomy );
	}

	/**
	 * Fetch latest public post/page ID.
	 * Memoizes the return value.
	 *
	 * @since 2.4.3
	 * @since 2.9.3 1. Removed object caching.
	 *              2. It now uses WP_Query, instead of wpdb.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return int Latest Post ID.
	 */
	public function get_latest_post_id() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_latest_post_id()', '4.3.0', 'tsf()->data()->post()->get_latest_post_id()' );

		return $tsf->data()->post()->get_latest_post_id();
	}

	/**
	 * Returns the primary term for post.
	 *
	 * @since 3.0.0
	 * @since 4.1.5   1. Added memoization.
	 *                2. The first and second parameters are now required.
	 * @since 4.1.5.1 1. No longer causes a PHP warning in the unlikely event a post's taxonomy gets deleted.
	 *                2. This method now converts the post meta to an integer, making the comparison work again.
	 * @since 4.2.7 Now correctly memoizes when no terms for a post can be found.
	 * @since 4.2.8 Now correctly returns when no terms for a post can be found.
	 * @since 4.3.0 1. Now always tries to return a term if none is set manually.
	 *              2. Now returns `null` instead of `false` on failure.
	 *              3. Deprecated.
	 * @deprecated
	 *
	 * @param int    $post_id  The post ID.
	 * @param string $taxonomy The taxonomy name.
	 * @return \WP_Term|null The primary term. Null if cannot be generated.
	 */
	public function get_primary_term( $post_id, $taxonomy ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_primary_term()', '4.3.0', 'tsf()->data()->plugin()->post()->get_primary_term()' );

		return $tsf->data()->plugin()->post()->get_primary_term( $post_id, $taxonomy );
	}

	/**
	 * Returns the primary term ID for post.
	 *
	 * @since 3.0.0
	 * @since 4.1.5 1. Now validates if the stored term ID's term exists (for the post or at all).
	 *              2. The first and second parameters are now required.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int    $post_id  The post ID.
	 * @param string $taxonomy The taxonomy name.
	 * @return int   The primary term ID. 0 if not found.
	 */
	public function get_primary_term_id( $post_id, $taxonomy ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_primary_term_id()', '4.3.0', 'tsf()->data()->plugin()->post()->get_primary_term_id()' );

		return $tsf->data()->plugin()->post()->get_primary_term_id( $post_id, $taxonomy );
	}

	/**
	 * Updates the primary term ID for post.
	 *
	 * @since 3.0.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|null $post_id  The post ID.
	 * @param string   $taxonomy The taxonomy name.
	 * @param int      $value    The new value. If empty, it will delete the entry.
	 * @return bool True on success, false on failure.
	 */
	public function update_primary_term_id( $post_id = null, $taxonomy = '', $value = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->update_primary_term_id()', '4.3.0', 'tsf()->data()->plugin()->post()->update_primary_term_id()' );

		return $tsf->data()->plugin()->post()->update_primary_term_id( $post_id, $taxonomy, $value );
	}

	/**
	 * Returns the user meta item by key.
	 *
	 * @since 4.1.4
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $item      The item to get. Required.
	 * @param int    $user_id   The user ID. Optional.
	 * @return mixed The user meta item. Null when not found.
	 */
	public function get_user_meta_item( $item, $user_id = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_user_meta_item()', '4.3.0', 'tsf()->data()->plugin()->user()->get_user_meta_item()' );

		return $tsf->data()->plugin()->user()->get_user_meta_item( $item, $user_id );
	}

	/**
	 * Returns the author meta item by key.
	 *
	 * @since 4.1.4
	 * @since 4.2.8 Now returns null when no post author can be established.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $item      The item to get. Required.
	 * @return mixed The author meta item. Null when not found.
	 */
	public function get_current_post_author_meta_item( $item ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_current_post_author_meta_item()', '4.3.0', 'tsf()->data()->plugin()->user()->get_current_post_author_meta_item()' );

		return $tsf->data()->plugin()->user()->get_current_post_author_meta_item( $item );
	}

	/**
	 * Returns and caches author meta for the current query.
	 * Memoizes the return value for the current request.
	 *
	 * @since 4.1.4
	 * @since 4.2.7 Removed redundant memoization.
	 * @since 4.2.8 Now returns null when no post author can be established.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return ?array The current author meta, null when no author is set.
	 */
	public function get_current_post_author_meta() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_current_post_author_meta()', '4.3.0', 'tsf()->data()->plugin()->user()->get_current_post_author_meta()' );

		return $tsf->data()->plugin()->user()->get_current_post_author_meta();
	}

	/**
	 * Fetches usermeta set by The SEO Framework.
	 * Memoizes the return value, can be bypassed.
	 *
	 * @since 2.7.0
	 * @since 2.8.0 Always returns array, even if no value is assigned.
	 * @since 4.1.4 1. Now returns default values when custom values are missing.
	 *              2. Now listens to headlessness.
	 *              3. Deprecated the third argument, and moved it to the second.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $user_id The user ID.
	 * @return array The user SEO meta data.
	 */
	public function get_user_meta( $user_id = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_user_meta()', '4.3.0', 'tsf()->data()->plugin()->user()->get_user_meta()' );

		return $tsf->data()->plugin()->user()->get_user_meta( $user_id );
	}

	/**
	 * Returns an array of default user meta.
	 *
	 * @since 4.1.4
	 *
	 * @param int $user_id The user ID. Defaults to CURRENT USER, NOT CURRENT POST AUTHOR.
	 * @return array The user meta defaults.
	 */
	public function get_user_meta_defaults( $user_id = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_user_meta_defaults()', '4.3.0', 'tsf()->data()->plugin()->user()->get_user_meta_defaults()' );

		return $tsf->data()->plugin()->user()->get_user_meta_defaults( $user_id );
	}

	/**
	 * Updates user TSF-meta option.
	 *
	 * @since 4.1.4
	 *
	 * @param int    $user_id The user ID.
	 * @param string $option  The user's SEO metadata to update.
	 * @param mixed  $value   The option value.
	 */
	public function update_single_user_meta_item( $user_id, $option, $value ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->update_single_user_meta_item()', '4.3.0', 'tsf()->data()->plugin()->user()->update_single_user_meta_item()' );

		return $tsf->data()->plugin()->user()->update_single_user_meta_item( $user_id, $option, $value );
	}

	/**
	 * Updates users meta from input.
	 *
	 * @since 4.1.4
	 * @since 4.2.0 No longer returns the update success state.
	 *
	 * @param int   $user_id The user ID.
	 * @param array $data    The data to save.
	 */
	public function save_user_meta( $user_id, $data ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->save_user_meta()', '4.3.0', 'tsf()->data()->plugin()->user()->save_user_meta()' );

		return $tsf->data()->plugin()->user()->save_user_meta( $user_id, $data );
	}

	/**
	 * Returns the post author ID.
	 * Memoizes the return value for the current request.
	 *
	 * @since 3.0.0
	 * @since 3.2.2 1. Now no longer returns the latest post author ID on home-as-blog pages.
	 *              2. Now always returns an integer.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $post_id The post ID to fetch the author from. Leave 0 to autodetermine.
	 * @return int Post author ID on success, 0 on failure.
	 */
	public function get_post_author_id( $post_id = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_post_author_id()', '4.3.0', 'tsf()->query()->get_post_author_id()' );

		return $tsf->query()->get_post_author_id( $post_id );
	}

	/**
	 * Returns the current post author ID.
	 * Memoizes the return value for the current request.
	 *
	 * @since 3.0.0
	 * @since 3.2.2 1. Now no longer returns the latest post author ID on home-as-blog pages.
	 *              2. Now always returns an integer.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return int Post author ID on success, 0 on failure.
	 */
	public function get_current_post_author_id() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_current_post_author_id()', '4.3.0', 'tsf()->query()->get_post_author_id()' );

		return $tsf->query()->get_post_author_id();
	}

	/**
	 * Sets up user ID and returns it if user is found.
	 * To be used in AJAX, back-end and front-end.
	 *
	 * @since 2.7.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return int The user ID. 0 if user is not found.
	 */
	public function get_user_id() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_user_id()', '4.3.0', 'tsf()->query()->get_current_user_id()' );

		return $tsf->query()->get_current_user_id();
	}

	/**
	 * Fetches Post content.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 No longer applies WordPress's default filters.
	 * @since 4.2.8 Now tests for post type support of 'editor' before parsing the content.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param \WP_Post|int|null $post The Post or Post ID. Leave null to get current post.
	 * @return string The post content.
	 */
	public function get_post_content( $post = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_post_content()', '4.3.0', 'tsf()->data()->post()->get_post_content()' );

		return $tsf->data()->post()->get_post_content( $post );
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
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $post_id The post ID to check.
	 * @return bool
	 */
	public function uses_non_html_page_builder( $post_id ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->uses_non_html_page_builder()', '4.3.0', 'tsf()->data()->post()->uses_non_html_page_builder()' );

		return $tsf->data()->post()->uses_non_html_page_builder( $post_id );
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
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|null|\WP_Post $post The post ID or WP Post object.
	 * @return bool True if protected or private, false otherwise.
	 */
	public function is_protected( $post = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_protected()', '4.3.0', 'tsf()->data()->post()->is_protected()' );

		return $tsf->data()->post()->is_protected( $post );
	}

	/**
	 * Determines if the current post has a password.
	 *
	 * @since 3.0.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|null|\WP_Post $post The post ID or WP Post object.
	 * @return bool True if protected, false otherwise.
	 */
	public function is_password_protected( $post = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_password_protected()', '4.3.0', 'tsf()->data()->post()->is_password_protected()' );

		return $tsf->data()->post()->is_password_protected( $post );
	}

	/**
	 * Determines if the current post is private.
	 *
	 * @since 3.0.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|null|\WP_Post $post The post ID or WP Post object.
	 * @return bool True if private, false otherwise.
	 */
	public function is_private( $post = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_private()', '4.3.0', 'tsf()->data()->post()->is_private()' );

		return $tsf->data()->post()->is_private( $post );
	}

	/**
	 * Determines if the current post is a draft.
	 *
	 * @since 3.1.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|null|\WP_Post $post The post ID or WP Post object.
	 * @return bool True if draft, false otherwise.
	 */
	public function is_draft( $post = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_draft()', '4.3.0', 'tsf()->data()->post()->is_draft()' );

		return $tsf->data()->post()->is_draft( $post );
	}

	/**
	 * Returns a post SEO meta item by key.
	 *
	 * Unlike other post meta calls, no \WP_Post object is accepted as an input value,
	 * this is done for performance reasons, so we can cache here, instead of relying on
	 * WordPress's cache, where they cast many filters and redundantly sanitize the object.
	 *
	 * @since 4.0.0
	 * @since 4.0.1 Now obtains the real ID when none is supplied.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $item    The item to get.
	 * @param int    $post_id The post ID.
	 * @return mixed The post meta item's value. Null when item isn't registered.
	 */
	public function get_post_meta_item( $item, $post_id = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_post_meta_item()', '4.3.0', 'tsf()->data()->plugin()->post()->get_post_meta_item()' );

		return $tsf->data()->plugin()->post()->get_post_meta_item( $item, $post_id );
	}

	/**
	 * Returns all registered custom SEO fields for a post.
	 * Memoizes the return value.
	 *
	 * Unlike other post meta calls, no \WP_Post object is accepted as an input value,
	 * this is done for performance reasons, so we can cache here, instead of relying on
	 * WordPress's cache, where they cast many filters and redundantly sanitize the object.
	 *
	 * @since 4.0.0
	 * @since 4.0.2 Now tests for valid post ID in the post object.
	 * @since 4.1.4 1. Now returns an empty array when the post type isn't supported.
	 *              2. Now considers headlessness.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $post_id The post ID.
	 * @return array The post meta.
	 */
	public function get_post_meta( $post_id ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_post_meta()', '4.3.0', 'tsf()->data()->plugin()->post()->get_post_meta()' );

		return $tsf->data()->plugin()->post()->get_post_meta( $post_id );
	}

	/**
	 * Returns the post meta defaults.
	 *
	 * Unlike other post meta calls, no \WP_Post object is accepted as an input value,
	 * this is done for performance reasons, so we can cache here, instead of relying on
	 * WordPress's cache, where they cast many filters and redundantly sanitize the object.
	 *
	 * @since 4.0.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $post_id The post ID.
	 * @return array The default post meta.
	 */
	public function get_post_meta_defaults( $post_id = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_post_meta_defaults()', '4.3.0', 'tsf()->data()->plugin()->post()->get_post_meta_defaults()' );

		return $tsf->data()->plugin()->post()->get_post_meta_defaults( $post_id );
	}

	/**
	 * Updates single post meta value.
	 *
	 * Note that this method can be more resource intensive than you intend it to be,
	 * as it reprocesses all post meta.
	 *
	 * @since 4.0.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string           $item  The item to update.
	 * @param mixed            $value The value the item should be at.
	 * @param \WP_Post|integer $post  The post object or post ID.
	 */
	public function update_single_post_meta_item( $item, $value, $post ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->update_single_post_meta_item()', '4.3.0', 'tsf()->data()->plugin()->post()->update_single_post_meta_item()' );

		return $tsf->data()->plugin()->post()->update_single_post_meta_item( $item, $value, $post );
	}

	/**
	 * Save post meta / custom field data for a singular post type.
	 *
	 * @since 4.0.0
	 * @since 4.1.4 Removed deprecated filter.
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param \WP_Post|integer $post The post object or post ID.
	 * @param array            $data The post meta fields, will be merged with the defaults.
	 */
	public function save_post_meta( $post, $data ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->save_post_meta()', '4.3.0', 'tsf()->data()->plugin()->post()->save_post_meta()' );

		return $tsf->data()->plugin()->post()->save_post_meta( $post, $data );
	}

	/**
	 * Returns all post type archive meta.
	 *
	 * We do not test whether a post type is supported, for it'll conflict with data-fills on the
	 * SEO settings page. This meta should never get called on the front-end if the post type is
	 * disabled, anyway, for we never query post types externally, aside from the SEO settings page.
	 *
	 * @since 4.2.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $post_type The post type.
	 * @return array The post type archive's meta item's values.
	 */
	public function get_post_type_archive_meta( $post_type ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_post_type_archive_meta()', '4.3.0', 'tsf()->data()->plugin()->pta()->get_post_type_archive_meta()' );

		return $tsf->data()->plugin()->pta()->get_post_type_archive_meta( $post_type );
	}

	/**
	 * Returns a single post type archive item's value.
	 *
	 * @since 4.2.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $item      The item to get.
	 * @param string $post_type The post type.
	 * @return ?mixed The post type archive's meta item value. Null when item isn't registered.
	 */
	public function get_post_type_archive_meta_item( $item, $post_type = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_post_type_archive_meta_item()', '4.3.0', 'tsf()->data()->plugin()->pta()->get_post_type_archive_meta_item()' );

		return $tsf->data()->plugin()->pta()->get_post_type_archive_meta_item( $item, $post_type );
	}

	/**
	 * Returns an array of all public post type archive option defaults.
	 *
	 * @since 4.2.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return array[] The Post Type Archive Metadata default options
	 *                 of all public Post Type archives.
	 */
	public function get_all_post_type_archive_meta_defaults() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_all_post_type_archive_meta_defaults()', '4.3.0', 'tsf()->data()->plugin()->pta()->get_all_post_type_archive_meta_defaults()' );

		return $tsf->data()->plugin()->pta()->get_all_post_type_archive_meta_defaults();
	}

	/**
	 * Returns an array of default post type archive meta.
	 *
	 * @since 4.2.0
	 * @since 4.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $post_type The post type.
	 * @return array The Post Type Archive Metadata default options.
	 */
	public function get_post_type_archive_meta_defaults( $post_type = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_post_type_archive_meta_defaults()', '4.3.0', 'tsf()->data()->plugin()->pta()->get_post_type_archive_meta_defaults()' );

		return $tsf->data()->plugin()->pta()->get_post_type_archive_meta_defaults( $post_type );
	}
}
