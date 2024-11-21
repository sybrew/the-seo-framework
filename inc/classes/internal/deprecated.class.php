<?php
/**
 * @package The_SEO_Framework\Classes\Internal\Deprecated
 * @subpackage The_SEO_Framework\Debug\Deprecated
 */

namespace The_SEO_Framework\Internal;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

// Precautionary.
use function \The_SEO_Framework\{
	is_headless,
	normalize_generation_args,
	get_query_type_from_args,
	memo,
	umemo,
};

// Precautionary.
use \The_SEO_Framework\{
	Data,
	Helper,
	Helper\Query,
	Meta,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * @since 5.0.0 Removed all methods deprecated in 4.2.0
 * @ignore
 */
final class Deprecated {

	/**
	 * Set the value of the transient.
	 *
	 * Prevents setting of transients when they're disabled.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $transient  Transient name. Expected to not be SQL-escaped.
	 * @param string $value      Transient value. Expected to not be SQL-escaped.
	 * @param int    $expiration Transient expiration date, optional. Expected to not be SQL-escaped.
	 */
	public function set_transient( $transient, $value, $expiration = 0 ) {
		\tsf()->_deprecated_function( 'tsf()->set_transient()', '5.0.0', 'set_transient()' );
		return \set_transient( $transient, $value, $expiration );
	}

	/**
	 * Get the value of the transient.
	 *
	 * If the transient does not exists, does not have a value or has expired,
	 * or transients have been disabled through a constant, then the transient
	 * will be false.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $transient Transient name. Expected to not be SQL-escaped.
	 * @return mixed|bool Value of the transient. False on failure or non existing transient.
	 */
	public function get_transient( $transient ) {
		\tsf()->_deprecated_function( 'tsf()->get_transient()', '5.0.0', 'get_transient()' );
		return \get_transient( $transient );
	}

	/**
	 * Returns the post type name from query input or real ID.
	 *
	 * @since 4.0.5
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|WP_Post|null $post (Optional) Post ID or post object.
	 * @return string|false Post type on success, false on failure.
	 */
	public function get_post_type_real_id( $post = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_post_type_real_id()', '5.0.0', 'tsf()->query()->get_post_type_real_id()' );

		return $tsf->query()->get_post_type_real_id( $post );
	}

	/**
	 * Returns the post type name from current screen.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string
	 */
	public function get_admin_post_type() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_admin_post_type()', '5.0.0', 'tsf()->query()->get_admin_post_type()' );

		return $tsf->query()->get_admin_post_type();
	}

	/**
	 * Returns a list of post types shared with the taxonomy.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $taxonomy Optional. The taxonomy to check. Defaults to current screen/query taxonomy.
	 * @return array List of post types.
	 */
	public function get_post_types_from_taxonomy( $taxonomy = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_post_types_from_taxonomy()', '5.0.0', 'tsf()->taxonomy()->get_post_types()' );

		return $tsf->taxonomy()->get_post_types( $taxonomy );
	}

	/**
	 * Get the real page ID, also from CPT, archives, author, blog, etc.
	 *
	 * @since 2.5.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param bool $use_cache Whether to use the cache or not.
	 * @return int|false The ID.
	 */
	public function get_the_real_id( $use_cache = true ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_the_real_id()', '5.0.0', 'tsf()->query()->get_the_real_id()' );

		return $tsf->query()->get_the_real_id( $use_cache );
	}

	/**
	 * Fetches post or term ID within the admin.
	 * Alters while in the loop. Therefore, this can't be cached and must be called within the loop.
	 *
	 * @since 2.7.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return int The admin ID.
	 */
	public function get_the_real_admin_id() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_the_real_admin_id()', '5.0.0', 'tsf()->query()->get_the_real_admin_id()' );

		return $tsf->query()->get_the_real_admin_id();
	}

	/**
	 * Returns the front page ID, if home is a page.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return int the ID.
	 */
	public function get_the_front_page_id() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_the_front_page_id()', '5.0.0', 'tsf()->query()->get_the_front_page_id()' );

		return $tsf->query()->get_the_front_page_id();
	}

	/**
	 * Fetches the Term ID on admin pages.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return int Term ID.
	 */
	public function get_admin_term_id() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_admin_term_id()', '5.0.0', 'tsf()->query()->get_admin_term_id()' );

		return $tsf->query()->get_admin_term_id();
	}

	/**
	 * Returns the current taxonomy, if any.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 1. Now works in the admin.
	 *              2. Added caching.
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The queried taxonomy type.
	 */
	public function get_current_taxonomy() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_current_taxonomy()', '5.0.0', 'tsf()->query()->get_current_taxonomy()' );

		return $tsf->query()->get_current_taxonomy();
	}

	/**
	 * Returns the current post type, if any.
	 *
	 * @since 4.1.4
	 * @since 5.0.0 1. Deprecated.
	 *              2. Now falls back to the current post type instead erroneously to a boolean.
	 *              3. Now memoizes the return value.
	 * @deprecated
	 *
	 * @return string The queried post type.
	 */
	public function get_current_post_type() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_current_post_type()', '5.0.0', 'tsf()->query()->get_current_post_type()' );

		return $tsf->query()->get_current_post_type();
	}

	/**
	 * Detects 404.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_404() {
		\tsf()->_deprecated_function( 'tsf()->is_404()', '5.0.0', 'is_404()' );
		return \is_404();
	}

	/**
	 * Detects admin screen.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_admin() {
		\tsf()->_deprecated_function( 'tsf()->is_admin()', '5.0.0', 'is_admin()' );
		return \is_admin();
	}

	/**
	 * Detects attachment page.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param mixed $attachment Attachment ID, title, slug, or array of such.
	 * @return bool
	 */
	public function is_attachment( $attachment = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_attachment()', '5.0.0', 'tsf()->query()->is_attachment()' );

		return $tsf->query()->is_attachment( $attachment );
	}

	/**
	 * Detects attachments within the admin area.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_attachment_admin() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_attachment_admin()', '5.0.0', 'tsf()->query()->is_attachment_admin()' );

		return $tsf->query()->is_attachment_admin();
	}

	/**
	 * Determines whether the content type is both singular and archival.
	 * Simply put, it detects a blog page and WooCommerce shop page.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|WP_Post|null $post (Optional) Post ID or post object.
	 * @return bool
	 */
	public function is_singular_archive( $post = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_singular_archive()', '5.0.0', 'tsf()->query()->is_singular_archive()' );

		return $tsf->query()->is_singular_archive( $post );
	}

	/**
	 * Detects archive pages. Also in admin.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_archive() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_archive()', '5.0.0', 'tsf()->query()->is_archive()' );

		return $tsf->query()->is_archive();
	}

	/**
	 * Extends default WordPress is_archive() and determines screen in admin.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool Post Type is archive
	 */
	public function is_archive_admin() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_archive_admin()', '5.0.0', 'tsf()->query()->is_archive_admin()' );

		return $tsf->query()->is_archive_admin();
	}

	/**
	 * Detects Term edit screen in WP Admin.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool True if on Term Edit screen. False otherwise.
	 */
	public function is_term_edit() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_term_edit()', '5.0.0', 'tsf()->query()->is_term_edit()' );

		return $tsf->query()->is_term_edit();
	}

	/**
	 * Detects Post edit screen in WP Admin.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool We're on Post Edit screen.
	 */
	public function is_post_edit() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_post_edit()', '5.0.0', 'tsf()->query()->is_post_edit()' );

		return $tsf->query()->is_post_edit();
	}

	/**
	 * Detects Post or Archive Lists in Admin.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool We're on the edit screen.
	 */
	public function is_wp_lists_edit() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_wp_lists_edit()', '5.0.0', 'tsf()->query()->is_wp_lists_edit()' );

		return $tsf->query()->is_wp_lists_edit();
	}

	/**
	 * Detects Profile edit screen in WP Admin.
	 *
	 * @since 4.1.4
	 * @since 5.0.0 1. Now also tests network profile edit screens.
	 *              2. Deprecated.
	 * @deprecated
	 *
	 * @return bool True if on Profile Edit screen. False otherwise.
	 */
	public function is_profile_edit() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_profile_edit()', '5.0.0', 'tsf()->query()->is_profile_edit()' );

		return $tsf->query()->is_profile_edit();
	}

	/**
	 * Detects author archives.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param mixed $author Optional. User ID, nickname, nicename, or array of User IDs, nicknames, and nicenames
	 * @return bool
	 */
	public function is_author( $author = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_author()', '5.0.0', 'tsf()->query()->is_author()' );

		return $tsf->query()->is_author( $author );
	}

	/**
	 * Detects the blog page.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|WP_Post|null $post Optional. Post ID or post object.
	 *                               Do not supply from WP_Query's main loop-query.
	 * @return bool
	 */
	public function is_home( $post = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_home()', '5.0.0', 'tsf()->query()->is_blog()' );

		return $tsf->query()->is_blog( $post );
	}

	/**
	 * Detects the non-front blog page.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|WP_Post|null $post Optional. Post ID or post object.
	 *                               Do not supply from WP_Query's main loop-query.
	 * @return bool
	 */
	public function is_home_as_page( $post = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_home_as_page()', '5.0.0', 'tsf()->query()->is_blog_as_page()' );

		return $tsf->query()->is_blog_as_page( $post );
	}

	/**
	 * Detects category archives.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param mixed $category Optional. Category ID, name, slug, or array of Category IDs, names, and slugs.
	 * @return bool
	 */
	public function is_category( $category = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_category()', '5.0.0', 'tsf()->query()->is_category()' );

		return $tsf->query()->is_category( $category );
	}

	/**
	 * Extends default WordPress is_category() and determines screen in admin.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool Post Type is category
	 */
	public function is_category_admin() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_category_admin()', '5.0.0', 'tsf()->query()->is_category_admin()' );

		return $tsf->query()->is_category_admin();
	}

	/**
	 * Detects customizer preview.
	 *
	 * Unlike is_preview(), WordPress has prior security checks for this
	 * in `\WP_Customize_Manager::setup_theme()`.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_customize_preview() {
		\tsf()->_deprecated_function( 'tsf()->is_customize_preview()', '5.0.0', 'is_customize_preview()' );
		return \is_customize_preview();
	}

	/**
	 * Detects date archives.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_date() {
		\tsf()->_deprecated_function( 'tsf()->is_date()', '5.0.0', 'is_date()' );
		return \is_date();
	}

	/**
	 * Detects day archives.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_day() {
		\tsf()->_deprecated_function( 'tsf()->is_day()', '5.0.0', 'is_day()' );
		return \is_day();
	}

	/**
	 * Detects feed.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string|array $feeds Optional feed types to check.
	 * @return bool
	 */
	public function is_feed( $feeds = '' ) {
		\tsf()->_deprecated_function( 'tsf()->is_feed()', '5.0.0', 'is_feed()' );
		return \is_feed( $feeds );
	}

	/**
	 * Detects front page.
	 *
	 * @since 2.9.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_real_front_page() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_real_front_page()', '5.0.0', 'tsf()->query()->is_real_front_page()' );

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
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $id The tested ID.
	 * @return bool
	 */
	public function is_real_front_page_by_id( $id ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_real_front_page_by_id()', '5.0.0', 'tsf()->query()->is_real_front_page_by_id()' );

		return $tsf->query()->is_real_front_page_by_id( $id );
	}

	/**
	 * Detects month archives.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_month() {
		\tsf()->_deprecated_function( 'tsf()->is_month()', '5.0.0', 'is_month()' );
		return \is_month();
	}

	/**
	 * Detects pages.
	 * When $page is supplied, it will check against the current object. So it will not work in the admin screens.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|string|array $page Optional. Page ID, title, slug, or array of such. Default empty.
	 * @return bool
	 */
	public function is_page( $page = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_page()', '5.0.0', 'tsf()->query()->is_page()' );

		return $tsf->query()->is_page( $page );
	}

	/**
	 * Detects pages within the admin area.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_page_admin() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_page_admin()', '5.0.0', 'tsf()->query()->is_page_admin()' );

		return $tsf->query()->is_page_admin();
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
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_preview() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_preview()', '5.0.0', 'tsf()->query()->is_preview()' );

		return $tsf->query()->is_preview();
	}

	/**
	 * Detects search.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_search() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_search()', '5.0.0', 'tsf()->query()->is_search()' );

		return $tsf->query()->is_search();
	}

	/**
	 * Detects single post pages.
	 * When $post is supplied, it will check against the current object. So it will not work in the admin screens.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|string|array $post Optional. Post ID, title, slug, or array of such. Default empty.
	 * @return bool
	 */
	public function is_single( $post = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_single()', '5.0.0', 'tsf()->query()->is_single()' );

		return $tsf->query()->is_single( $post );
	}

	/**
	 * Detects posts within the admin area.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_single_admin() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_single_admin()', '5.0.0', 'tsf()->query()->is_single_admin()' );

		return $tsf->query()->is_single_admin();
	}

	/**
	 * Determines if the current page is singular is holds singular items within the admin screen.
	 * Replaces and expands default WordPress `is_singular()`.
	 *
	 * @since 2.5.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string|string[] $post_types Optional. Post type or array of post types. Default empty string.
	 * @return bool Post Type is singular
	 */
	public function is_singular( $post_types = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_singular()', '5.0.0', 'tsf()->query()->is_singular()' );

		return $tsf->query()->is_singular( $post_types );
	}

	/**
	 * Determines if the page is singular within the admin screen.
	 *
	 * @since 2.5.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool Post Type is singular
	 */
	public function is_singular_admin() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_singular_admin()', '5.0.0', 'tsf()->query()->is_singular_admin()' );

		return $tsf->query()->is_singular_admin();
	}

	/**
	 * Detects the static front page.
	 *
	 * @since 2.3.8
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $id the Page ID to check. If empty, the current ID will be fetched.
	 * @return bool True when homepage is static and given/current ID matches.
	 */
	public function is_static_frontpage( $id = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_static_frontpage()', '5.0.0', 'tsf()->query()->is_static_front_page()' );

		return $tsf->query()->is_static_front_page( $id );
	}

	/**
	 * Detects tag archives.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param mixed $tag Optional. Tag ID, name, slug, or array of Tag IDs, names, and slugs.
	 * @return bool
	 */
	public function is_tag( $tag = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_tag()', '5.0.0', 'tsf()->query()->is_tag()' );

		return $tsf->query()->is_tag( $tag );
	}

	/**
	 * Determines if the page is a tag within the admin screen.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool Post Type is tag.
	 */
	public function is_tag_admin() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_tag_admin()', '5.0.0', 'tsf()->query()->is_tag_admin()' );

		return $tsf->query()->is_tag_admin();
	}

	/**
	 * Detects taxonomy archives.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string|array     $taxonomy Optional. Taxonomy slug or slugs.
	 * @param int|string|array $term     Optional. Term ID, name, slug or array of Term IDs, names, and slugs.
	 * @return bool
	 */
	public function is_tax( $taxonomy = '', $term = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_tax()', '5.0.0', 'tsf()->query()->is_tax()' );

		return $tsf->query()->is_tax( $taxonomy, $term );
	}

	/**
	 * Determines if the $post is a shop page.
	 *
	 * @since 4.0.5
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|WP_Post|null $post (Optional) Post ID or post object.
	 * @return bool
	 */
	public function is_shop( $post = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_shop()', '5.0.0', 'tsf()->query()->is_shop()' );

		return $tsf->query()->is_shop( $post );
	}

	/**
	 * Determines if the page is a product page.
	 *
	 * @since 4.0.5
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|WP_Post|null $post (Optional) Post ID or post object.
	 * @return bool True if on a WooCommerce Product page.
	 */
	public function is_product( $post = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_product()', '5.0.0', 'tsf()->query()->is_product()' );

		return $tsf->query()->is_product( $post );
	}

	/**
	 * Determines if the admin page is for a product page.
	 *
	 * @since 4.0.5
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_product_admin() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_product_admin()', '5.0.0', 'tsf()->query()->is_product_admin()' );

		return $tsf->query()->is_product_admin();
	}

	/**
	 * Detects year archives.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_year() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_year()', '5.0.0', 'tsf()->query()->is_year()' );

		return $tsf->query()->is_year();
	}

	/**
	 * Determines if SSL is used.
	 *
	 * @since 2.8.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool True if SSL, false otherwise.
	 */
	public function is_ssl() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_ssl()', '5.0.0', 'tsf()->query()->is_ssl()' );

		return $tsf->query()->is_ssl();
	}

	/**
	 * Determines whether we're on the SEO settings page.
	 * WARNING: Do not ever use this as a safety check.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param bool $secure Whether to ignore the use of the second (insecure) parameter.
	 * @return bool
	 */
	public function is_seo_settings_page( $secure = true ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_seo_settings_page()', '5.0.0', 'tsf()->query()->is_seo_settings_page()' );

		return $tsf->query()->is_seo_settings_page( $secure );
	}

	/**
	 * Checks the screen base file through global $page_hook or $_GET.
	 *
	 * @since 2.2.2
	 * @since 5.0.0 Deprecated.
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
		$tsf->_deprecated_function( 'tsf()->is_menu_page()', '5.0.0', 'tsf()->query()->is_menu_page()' );

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
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return int (R>0) $page Always a positive number.
	 */
	public function page() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->page()', '5.0.0', 'tsf()->query()->page()' );

		return $tsf->query()->page();
	}

	/**
	 * Returns the current page number.
	 * Fetches global `$paged` from `WP_Query` to prevent conflicts.
	 *
	 * @since 2.6.0
	 * @since 3.2.4 1. Added overflow protection.
	 *              2. Now always returns 1 on the admin screens.
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return int (R>0) $paged Always a positive number.
	 */
	public function paged() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->paged()', '5.0.0', 'tsf()->query()->paged()' );

		return $tsf->query()->paged();
	}

	/**
	 * Determines the number of available pages.
	 *
	 * This is largely taken from \WP_Query::setup_postdata(), however, the data
	 * we need is set up in the loop, not in the header; where TSF is active.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return int
	 */
	public function numpages() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->numpages()', '5.0.0', 'tsf()->query()->numpages()' );

		return $tsf->query()->numpages();
	}

	/**
	 * Determines whether the current loop has multiple pages.
	 *
	 * @since 2.7.0
	 * @since 3.1.0 1. Now also works on archives.
	 *              2. Now is public.
	 * @since 3.2.4 Now always returns false on the admin pages.
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool True if multipage.
	 */
	public function is_multipage() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_multipage()', '5.0.0', 'tsf()->query()->is_multipage()' );

		return $tsf->query()->is_multipage();
	}

	/**
	 * Determines whether we're on The SEO Framework's sitemap or not.
	 * Memoizes the return value once set.
	 *
	 * @since 2.9.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param bool $set Whether to set "doing sitemap".
	 * @return bool
	 */
	public function is_sitemap( $set = false ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_sitemap()', '5.0.0', 'tsf()->query()->is_sitemap()' );

		return $tsf->query()->is_sitemap( $set );
	}

	/**
	 * Determines whether we're on the robots.txt file output.
	 *
	 * @since 2.9.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_robots() {
		\tsf()->_deprecated_function( 'tsf()->is_robots()', '5.0.0', 'is_robots()' );
		return \is_robots();
	}

	/**
	 * Renders the 'tsf:aqp' meta tag. Useful for identifying when query-exploit detection
	 * is triggered.
	 *
	 * @since 4.1.4
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The advanced query protection (aqp) identifier.
	 */
	public function advanced_query_protection() {
		\tsf()->_deprecated_function( 'tsf()->advanced_query_protection()', '5.0.0' );
		return \The_SEO_Framework\Front\Meta\Tags::render( [
			'name'  => 'tsf:aqp',
			'value' => '1',
		] );
	}

	/**
	 * Renders the description meta tag.
	 *
	 * @since 1.3.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The description meta tag.
	 */
	public function the_description() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->the_description()', '5.0.0' );

		/**
		 * @since 2.3.0
		 * @since 5.0.0 Deprecated.
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
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $description ? \The_SEO_Framework\Front\Meta\Tags::render( [
			'name'    => 'description',
			'content' => $description,
		] ) : '';
	}

	/**
	 * Renders Robots meta tags.
	 * Returns early if blog isn't public. WordPress Core will then output the meta tags.
	 *
	 * @since 2.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Robots meta tags.
	 */
	public function robots() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->robots()', '5.0.0' );

		// Don't do anything if the blog isn't set to public.
		if ( false === $tsf->data()->blog()->is_public() ) return '';

		$meta = $tsf->robots()->get_meta();

		return $meta ? \The_SEO_Framework\Front\Meta\Tags::render( [
			'name'    => 'robots',
			'content' => $meta,
		] ) : '';
	}

	/**
	 * Renders Canonical URL meta tag.
	 *
	 * @since 2.0.6
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Canonical URL meta tag.
	 */
	public function canonical() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->canonical()', '5.0.0' );

		$_url = $tsf->get_current_canonical_url();

		/**
		 * @since 2.6.5
		 * @since 5.0.0 Deprecated.
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
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		// If the page should not be indexed, consider removing the canonical URL.
		if ( \in_array( 'noindex', $tsf->robots()->get_generated_meta(), true ) ) {
			// If the URL is filtered, don't empty it.
			// If a custom canonical URL is set, don't empty it.
			if ( $url === $_url && ! $tsf->has_custom_canonical_url() ) {
				$url = '';
			}
		}

		return $url ? \The_SEO_Framework\Front\Meta\Tags::render(
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
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Shortlink meta tag.
	 */
	public function shortlink() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->advanced_query_protection()', '5.0.0' );

		/**
		 * @since 2.6.0
		 * @since 5.0.0 Deprecated.
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
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $url ? \The_SEO_Framework\Front\Meta\Tags::render(
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
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Prev/Next Paged URL meta tags.
	 */
	public function paged_urls() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->paged_urls()', '5.0.0' );

		[ $next, $prev ] = $tsf->uri()->get_paged_urls();
		$id              = $tsf->query()->get_the_real_id();

		/**
		 * @since 2.6.0
		 * @since 5.0.0 Deprecated.
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
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);
		/**
		 * @since 2.6.0
		 * @since 5.0.0 Deprecated.
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
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		$output  = $prev ? \The_SEO_Framework\Front\Meta\Tags::render(
			[
				'rel'  => 'prev',
				'href' => $prev,
			],
			'link'
		) : '';
		$output .= $next ? \The_SEO_Framework\Front\Meta\Tags::render(
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
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Theme Color meta tag.
	 */
	public function theme_color() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->theme_color()', '5.0.0' );

		$theme_color = $tsf->get_option( 'theme_color' );

		return $theme_color ? \The_SEO_Framework\Front\Meta\Tags::render( [
			'name'    => 'theme-color',
			'content' => $theme_color,
		] ) : '';
	}

	/**
	 * Renders Google Site Verification Code meta tag.
	 *
	 * @since 2.2.4
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Google Site Verification code meta tag.
	 */
	public function google_site_output() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->google_site_output()', '5.0.0' );

		/**
		 * @since 2.6.0
		 * @since 5.0.0 Deprecated.
		 * @deprecated
		 * @param string $code The Google verification code.
		 * @param int    $id   The current post or term ID.
		 */
		$code = (string) \apply_filters_deprecated(
			'the_seo_framework_googlesite_output',
			[
				$tsf->get_option( 'google_verification' ),
				$tsf->query()->get_the_real_id(),
			],
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $code ? \The_SEO_Framework\Front\Meta\Tags::render( [
			'name'    => 'google-site-verification',
			'content' => $code,
		] ) : '';
	}

	/**
	 * Renders Bing Site Verification Code meta tag.
	 *
	 * @since 2.2.4
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Bing Site Verification Code meta tag.
	 */
	public function bing_site_output() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->bing_site_output()', '5.0.0' );

		/**
		 * @since 2.6.0
		 * @since 5.0.0 Deprecated.
		 * @deprecated
		 * @param string $code The Bing verification code.
		 * @param int    $id   The current post or term ID.
		 */
		$code = (string) \apply_filters_deprecated(
			'the_seo_framework_bingsite_output',
			[
				$tsf->get_option( 'bing_verification' ),
				$tsf->query()->get_the_real_id(),
			],
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $code ? \The_SEO_Framework\Front\Meta\Tags::render( [
			'name'    => 'msvalidate.01',
			'content' => $code,
		] ) : '';
	}

	/**
	 * Renders Yandex Site Verification code meta tag.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Yandex Site Verification code meta tag.
	 */
	public function yandex_site_output() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->yandex_site_output()', '5.0.0' );

		/**
		 * @since 2.6.0
		 * @since 5.0.0 Deprecated.
		 * @deprecated
		 * @param string $code The Yandex verification code.
		 * @param int    $id   The current post or term ID.
		 */
		$code = (string) \apply_filters_deprecated(
			'the_seo_framework_yandexsite_output',
			[
				$tsf->get_option( 'yandex_verification' ),
				$tsf->query()->get_the_real_id(),
			],
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $code ? \The_SEO_Framework\Front\Meta\Tags::render( [
			'name'    => 'yandex-verification',
			'content' => $code,
		] ) : '';
	}

	/**
	 * Renders Baidu Site Verification code meta tag.
	 *
	 * @since 4.0.5
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Baidu Site Verification code meta tag.
	 */
	public function baidu_site_output() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->baidu_site_output()', '5.0.0' );

		/**
		 * @since 4.0.5
		 * @since 5.0.0 Deprecated.
		 * @deprecated
		 * @param string $code The Baidu verification code.
		 * @param int    $id   The current post or term ID.
		 */
		$code = (string) \apply_filters_deprecated(
			'the_seo_framework_baidusite_output',
			[
				$tsf->get_option( 'baidu_verification' ),
				$tsf->query()->get_the_real_id(),
			],
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $code ? \The_SEO_Framework\Front\Meta\Tags::render( [
			'name'    => 'baidu-site-verification',
			'content' => $code,
		] ) : '';
	}

	/**
	 * Renders Pinterest Site Verification code meta tag.
	 *
	 * @since 2.5.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Pinterest Site Verification code meta tag.
	 */
	public function pint_site_output() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->pint_site_output()', '5.0.0' );

		/**
		 * @since 2.6.0
		 * @since 5.0.0 Deprecated.
		 * @deprecated
		 * @param string $code The Pinterest verification code.
		 * @param int    $id   The current post or term ID.
		 */
		$code = (string) \apply_filters_deprecated(
			'the_seo_framework_pintsite_output',
			[
				$tsf->get_option( 'pint_verification' ),
				$tsf->query()->get_the_real_id(),
			],
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $code ? \The_SEO_Framework\Front\Meta\Tags::render( [
			'name'    => 'p:domain_verify',
			'content' => $code,
		] ) : '';
	}

	/**
	 * Determines whether we can use Open Graph tags on the front-end.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 1. Deprecated.
	 *              2. Removed memoization.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function use_og_tags() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->use_og_tags()', '5.0.0' );

		/**
		 * @since 3.1.4
		 * @since 5.0.0 Deprecated.
		 * @deprecated
		 * @param bool $use_open_graph
		 */
		return \apply_filters_deprecated(
			'the_seo_framework_use_og_tags',
			[
				(bool) $tsf->get_option( 'og_tags' ),
			],
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_generators',
		);
	}

	/**
	 * Renders the Open Graph title meta tag.
	 *
	 * @since 2.0.3
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Open Graph title meta tag.
	 */
	public function og_title() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->og_title()', '5.0.0' );

		if ( ! $tsf->use_og_tags() )
			return '';

		/**
		 * @since 2.3.0
		 * @since 5.0.0 Deprecated.
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
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $title ? \The_SEO_Framework\Front\Meta\Tags::render( [
			'property' => 'og:title',
			'content'  => $title,
		] ) : '';
	}

	/**
	 * Renders og:description meta tag
	 *
	 * @since 1.3.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Open Graph description meta tag.
	 */
	public function og_description() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->og_description()', '5.0.0' );

		if ( ! $tsf->use_og_tags() )
			return '';

		/**
		 * @since 2.3.0
		 * @since 5.0.0 Deprecated.
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
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $description ? \The_SEO_Framework\Front\Meta\Tags::render( [
			'property' => 'og:description',
			'content'  => $description,
		] ) : '';
	}

	/**
	 * Renders the OG locale meta tag.
	 *
	 * @since 1.0.0
	 * @since 5.0.0 Deprecated.
	 * @since 5.1.0 Resolved an issue with a wrong callback; now returns the meta tag again.
	 * @deprecated
	 *
	 * @return string The Open Graph locale meta tag.
	 */
	public function og_locale() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->og_locale()', '5.0.0' );

		if ( ! $tsf->use_og_tags() )
			return '';

		/**
		 * @since 2.3.0
		 * @since 5.0.0 Deprecated.
		 * @deprecated
		 * @param string $locale The generated locale field.
		 * @param int    $id     The page or term ID.
		 */
		$locale = (string) \apply_filters_deprecated(
			'the_seo_framework_oglocale_output',
			[
				$tsf->open_graph()->get_locale(),
				$tsf->query()->get_the_real_id(),
			],
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $locale ? \The_SEO_Framework\Front\Meta\Tags::render( [
			'property' => 'og:locale',
			'content'  => $locale,
		] ) : '';
	}

	/**
	 * Renders the Open Graph type meta tag.
	 *
	 * @since 1.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Open Graph type meta tag.
	 */
	public function og_type() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->og_type()', '5.0.0' );

		if ( ! $tsf->use_og_tags() )
			return '';

		$type = $tsf->open_graph()->get_type();

		return $type ? \The_SEO_Framework\Front\Meta\Tags::render( [
			'property' => 'og:type',
			'content'  => $type,
		] ) : '';
	}

	/**
	 * Renders Open Graph image meta tag.
	 *
	 * @since 1.3.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Open Graph image meta tag.
	 */
	public function og_image() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->og_image()', '5.0.0' );

		if ( ! $tsf->use_og_tags() ) return '';

		$output = '';

		$multi = (bool) $tsf->get_option( 'multi_og_image' );

		foreach ( $tsf->get_image_details( null, ! $multi ) as $image ) {
			$output .= \The_SEO_Framework\Front\Meta\Tags::render( [
				'property' => 'og:image',
				'content'  => $image['url'],
			] );

			if ( $image['height'] && $image['width'] ) {
				$output .= \The_SEO_Framework\Front\Meta\Tags::render( [
					'property' => 'og:image:width',
					'content'  => $image['width'],
				] );
				$output .= \The_SEO_Framework\Front\Meta\Tags::render( [
					'property' => 'og:image:height',
					'content'  => $image['height'],
				] );
			}

			if ( $image['alt'] ) {
				$output .= \The_SEO_Framework\Front\Meta\Tags::render( [
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
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Open Graph sitename meta tag.
	 */
	public function og_sitename() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->og_sitename()', '5.0.0' );

		if ( ! $tsf->use_og_tags() ) return '';

		/**
		 * @since 2.3.0
		 * @since 5.0.0 Deprecated.
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
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $sitename ? \The_SEO_Framework\Front\Meta\Tags::render( [
			'property' => 'og:site_name',
			'content'  => $sitename,
		] ) : '';
	}

	/**
	 * Renders Open Graph URL meta tag.
	 *
	 * @since 1.3.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Open Graph URL meta tag.
	 */
	public function og_url() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->og_url()', '5.0.0' );

		if ( ! $tsf->use_og_tags() ) return '';

		/**
		 * @since 2.9.3
		 * @since 5.0.0 Deprecated.
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
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $url ? \The_SEO_Framework\Front\Meta\Tags::render( [
			'property' => 'og:url',
			'content'  => $url,
		] ) : '';
	}

	/**
	 * Renders the Open Graph Updated Time meta tag.
	 *
	 * @since 4.1.4
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Article Modified Time meta tag, and optionally the Open Graph Updated Time.
	 */
	public function og_updated_time() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->og_updated_time()', '5.0.0' );

		if ( ! $tsf->use_og_tags() ) return '';
		if ( ! $tsf->output_published_time() ) return '';

		$time = $tsf->get_modified_time();

		return $time ? \The_SEO_Framework\Front\Meta\Tags::render( [
			'property' => 'og:updated_time',
			'content'  => $time,
		] ) : '';
	}

	/**
	 * Renders Facebook Author meta tag.
	 *
	 * @since 2.2.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Facebook Author meta tag.
	 */
	public function facebook_author() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->facebook_author()', '5.0.0' );

		if ( ! $tsf->use_facebook_tags() ) return '';
		if ( 'article' !== $tsf->open_graph()->get_type() ) return '';

		/**
		 * @since 2.3.0
		 * @since 5.0.0 Deprecated.
		 * @deprecated
		 * @param string $facebook_page The generated Facebook author page URL.
		 * @param int    $id            The current page or term ID.
		 */
		$facebook_page = (string) \apply_filters_deprecated(
			'the_seo_framework_facebookauthor_output',
			[
				$tsf->data()->plugin()->user()->get_current_post_author_meta_item( 'facebook_page' )
					?: $tsf->get_option( 'facebook_author' ),
				$tsf->query()->get_the_real_id(),
			],
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $facebook_page ? \The_SEO_Framework\Front\Meta\Tags::render( [
			'property' => 'article:author',
			'content'  => $facebook_page,
		] ) : '';
	}

	/**
	 * Renders Facebook Publisher meta tag.
	 *
	 * @since 2.2.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Facebook Publisher meta tag.
	 */
	public function facebook_publisher() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->facebook_publisher()', '5.0.0' );

		if ( ! $tsf->use_facebook_tags() ) return '';
		if ( 'article' !== $tsf->open_graph()->get_type() ) return '';

		/**
		 * @since 2.3.0
		 * @since 5.0.0 Deprecated.
		 * @deprecated
		 * @param string $publisher The Facebook publisher page URL.
		 * @param int    $id        The current page or term ID.
		 */
		$publisher = (string) \apply_filters_deprecated(
			'the_seo_framework_facebookpublisher_output',
			[
				$tsf->get_option( 'facebook_publisher' ),
				$tsf->query()->get_the_real_id(),
			],
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $publisher ? \The_SEO_Framework\Front\Meta\Tags::render( [
			'property' => 'article:publisher',
			'content'  => $publisher,
		] ) : '';
	}

	/**
	 * Determines whether we can use Facebook tags on the front-end.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 1. Deprecated.
	 *              2. Removed memoization.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function use_facebook_tags() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->use_facebook_tags()', '5.0.0' );

		/**
		 * @since 3.1.4
		 * @since 5.0.0 Deprecated.
		 * @deprecated
		 * @param bool $use_facebook
		 */
		return \apply_filters_deprecated(
			'the_seo_framework_use_facebook_tags',
			[
				(bool) $tsf->get_option( 'facebook_tags' ),
			],
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_generators',
		);
	}

	/**
	 * Renders Article Publishing Time meta tag.
	 *
	 * @since 2.2.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Article Publishing Time meta tag.
	 */
	public function article_published_time() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->article_published_time()', '5.0.0' );

		if ( ! $tsf->output_published_time() ) return '';

		/**
		 * @since 2.3.0
		 * @since 5.0.0 Deprecated.
		 * @deprecated
		 * @param string $time The article published time.
		 * @param int    $id   The current page or term ID.
		 */
		$time = (string) \apply_filters_deprecated(
			'the_seo_framework_publishedtime_output',
			[
				$tsf->data()->post()->get_published_time(),
				$tsf->query()->get_the_real_id(),
			],
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $time ? \The_SEO_Framework\Front\Meta\Tags::render( [
			'property' => 'article:published_time',
			'content'  => $time,
		] ) : '';
	}

	/**
	 * Renders Article Modified Time meta tag.
	 *
	 * @since 2.2.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Article Modified Time meta tag
	 */
	public function article_modified_time() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->article_modified_time()', '5.0.0' );

		if ( ! $tsf->output_modified_time() ) return '';

		$time = $tsf->get_modified_time();

		return $time ? \The_SEO_Framework\Front\Meta\Tags::render( [
			'property' => 'article:modified_time',
			'content'  => $time,
		] ) : '';
	}

	/**
	 * Determines if modified time should be used in the current query.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function output_modified_time() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->output_modified_time()', '5.0.0' );

		if ( 'article' !== $tsf->open_graph()->get_type() )
			return false;

		return (bool) $tsf->get_option( 'post_modify_time' );
	}

	/**
	 * Determines if published time should be used in the current query.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function output_published_time() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->output_published_time()', '5.0.0' );

		if ( 'article' !== $tsf->open_graph()->get_type() )
			return false;

		return (bool) $tsf->get_option( 'post_publish_time' );
	}

	/**
	 * Returns the current Twitter card type.
	 *
	 * @since 2.8.2
	 * @since 5.0.0 1. Deprecated.
	 *              2. Removed memoization.
	 * @deprecated
	 *
	 * @return string The cached Twitter card.
	 */
	public function get_current_twitter_card_type() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_current_twitter_card_type()', '5.0.0', 'tsf()->twitter()->get_card_type()' );

		return $tsf->twitter()->get_card_type();
	}

	/**
	 * Renders the Twitter Card type meta tag.
	 *
	 * @since 2.2.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Twitter Card meta tag.
	 */
	public function twitter_card() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->twitter_card()', '5.0.0' );

		if ( ! $tsf->use_twitter_tags() ) return '';

		$card = $tsf->get_current_twitter_card_type();

		return $card ? \The_SEO_Framework\Front\Meta\Tags::render( [
			'name'    => 'twitter:card',
			'content' => $card,
		] ) : '';
	}

	/**
	 * Renders the Twitter Site meta tag.
	 *
	 * @since 2.2.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Twitter Site meta tag.
	 */
	public function twitter_site() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->twitter_site()', '5.0.0' );

		if ( ! $tsf->use_twitter_tags() ) return '';

		/**
		 * @since 2.3.0
		 * @since 5.0.0 Deprecated.
		 * @deprecated
		 * @param string $site The Twitter site owner tag.
		 * @param int    $id   The current page or term ID.
		 */
		$site = (string) \apply_filters_deprecated(
			'the_seo_framework_twittersite_output',
			[
				$tsf->get_option( 'twitter_site' ),
				$tsf->query()->get_the_real_id(),
			],
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $site ? \The_SEO_Framework\Front\Meta\Tags::render( [
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
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Twitter Creator or Twitter Site ID meta tag.
	 */
	public function twitter_creator() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->twitter_creator()', '5.0.0' );

		if ( ! $tsf->use_twitter_tags() ) return '';

		/**
		 * @since 2.3.0
		 * @since 5.0.0 Deprecated.
		 * @deprecated
		 * @param string $creator The Twitter page creator.
		 * @param int    $id      The current page or term ID.
		 */
		$creator = (string) \apply_filters_deprecated(
			'the_seo_framework_twittercreator_output',
			[
				$tsf->data()->plugin()->user()->get_current_post_author_meta_item( 'twitter_page' )
					?: $tsf->get_option( 'twitter_creator' ),
				$tsf->query()->get_the_real_id(),
			],
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $creator ? \The_SEO_Framework\Front\Meta\Tags::render( [
			'name'    => 'twitter:creator',
			'content' => $creator,
		] ) : '';
	}

	/**
	 * Renders Twitter Title meta tag.
	 *
	 * @since 2.2.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Twitter Title meta tag.
	 */
	public function twitter_title() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->twitter_title()', '5.0.0' );

		if ( ! $tsf->use_twitter_tags() ) return '';

		/**
		 * @since 2.3.0
		 * @since 5.0.0 Deprecated.
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
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $title ? \The_SEO_Framework\Front\Meta\Tags::render( [
			'name'    => 'twitter:title',
			'content' => $title,
		] ) : '';
	}

	/**
	 * Renders Twitter Description meta tag.
	 *
	 * @since 2.2.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Twitter Description meta tag.
	 */
	public function twitter_description() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->twitter_adescription()', '5.0.0' );

		if ( ! $tsf->use_twitter_tags() ) return '';

		/**
		 * @since 2.3.0
		 * @since 5.0.0 Deprecated.
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
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);

		return $description ? \The_SEO_Framework\Front\Meta\Tags::render( [
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
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Twitter Image meta tag.
	 */
	public function twitter_image() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->twitter_image()', '5.0.0' );

		if ( ! $tsf->use_twitter_tags() ) return '';

		$output = '';

		foreach ( $tsf->get_image_details( null, ! $tsf->get_option( 'multi_og_image' ) ) as $image ) {
			$output .= \The_SEO_Framework\Front\Meta\Tags::render( [
				'name'    => 'twitter:image',
				'content' => $image['url'],
			] );

			if ( $image['alt'] ) {
				$output .= \The_SEO_Framework\Front\Meta\Tags::render( [
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
	 *
	 * @since 2.6.0
	 * @since 5.0.0 1. Deprecated.
	 *              2. Removed memoization.
	 *              3. Removed test for card type.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function use_twitter_tags() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->use_twitter_tags()', '5.0.0' );

		/**
		 * @since 3.1.4
		 * @since 5.0.0 Deprecated.
		 * @deprecated
		 * @param bool $use_twitter_card
		 */
		return \apply_filters_deprecated(
			'the_seo_framework_use_twitter_tags',
			[
				(bool) $tsf->get_option( 'twitter_tags' ),
			],
			'5.0.0 of The SEO Framework',
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
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array ...$arrays The arrays to merge. The rightmost array's values are dominant.
	 * @return array The merged arrays.
	 */
	public function array_merge_recursive_distinct( ...$arrays ) {

		$tsf = \tsf();
		$tsf->_deprecated_function(
			'tsf()->array_merge_recursive_distinct()',
			'5.0.0',
			'tsf()->format()->arrays()->array_merge_recursive_distinct()',
		);

		return $tsf->format()->arrays()->array_merge_recursive_distinct( ...$arrays );
	}

	/**
	 * Returns an array of the collected robots meta assertions.
	 *
	 * This only works when generate_robots_meta()'s $options value was given:
	 * The_SEO_Framework\ROBOTS_ASSERT (0b100);
	 *
	 * @since 4.2.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return array
	 */
	public function retrieve_robots_meta_assertions() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->retrieve_robots_meta_assertions()', '5.0.0', 'tsf()->robots()->get_collected_meta_assertions()' );

		return $tsf->query()->get_collected_meta_assertions();
	}

	/**
	 * Returns the robots meta array.
	 *
	 * @since 3.2.4
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return array
	 */
	public function get_robots_meta() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_robots_meta()', '5.0.0', 'tsf()->robots()->get_meta()' );

		return explode( ',', $tsf->robots()->get_meta() );
	}

	/**
	 * Returns the `noindex`, `nofollow`, `noarchive` robots meta code array.
	 *
	 * @since 4.1.4
	 * @since 4.2.0 1. Now offloads metadata generation to an actual generator.
	 *              2. Now supports the `$args['pta']` index.
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args    The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
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
		$tsf->_deprecated_function( 'tsf()->generate_robots_meta()', '5.0.0', 'tsf()->robots()->get_generated_meta()' );

		return $tsf->robots()->get_generated_meta( $args, $get, $options );
	}

	/**
	 * Determines if the post type has a robots value set.
	 *
	 * @since 3.1.0
	 * @since 4.0.5 The `$post_type` fallback now uses a real query ID, instead of `$GLOBALS['post']`;
	 *              mitigating issues with singular-archives pages (blog, shop, etc.).
	 * @since 4.1.1 Now tests for not empty, instead of isset. We no longer support PHP 5.4 since v4.0.0.
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $type      Accepts 'noindex', 'nofollow', 'noarchive'.
	 * @param string $post_type The post type, optional. Leave empty to autodetermine type.
	 * @return bool True if noindex, nofollow, or noarchive is set; false otherwise.
	 */
	public function is_post_type_robots_set( $type, $post_type = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_post_type_robots_set()', '5.0.0', 'tsf()->robots()->is_post_type_robots_set()' );

		return $tsf->robots()->is_post_type_robots_set( $type, $post_type );
	}

	/**
	 * Determines if the taxonomy has a robots value set.
	 *
	 * @since 4.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $type     Accepts 'noindex', 'nofollow', 'noarchive'.
	 * @param string $taxonomy The taxonomy, optional. Leave empty to autodetermine type.
	 * @return bool True if noindex, nofollow, or noarchive is set; false otherwise.
	 */
	public function is_taxonomy_robots_set( $type, $taxonomy = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_taxonomy_robots_set()', '5.0.0', 'tsf()->robots()->is_taxonomy_robots_set()' );

		return $tsf->robots()->is_taxonomy_robots_set( $type, $taxonomy ?: null );
	}

	/**
	 * Determines whether the main query supports custom SEO.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 1. Removed detection for JSON(P) and XML type requests, because these cannot be assumed as legitimate.
	 *              2. Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function query_supports_seo() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->query_supports_seo()', '5.0.0', 'tsf()->query()->utils()->query_supports_seo()' );

		return $tsf->query()->utils()->query_supports_seo();
	}

	/**
	 * Determines when paged/page is exploited.
	 *
	 * @since 4.0.5
	 * @since 4.2.7 1. Added detection `not_home_as_page`, specifically for query variable `search`.
	 *              2. Improved detection for `cat` and `author`, where the value may only be numeric above 0.
	 * @since 4.2.8 Now blocks any publicly registered variable requested to the home-as-page.
	 * @since 5.0.0 Deprecated.
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
		$tsf->_deprecated_function( 'tsf()->is_query_exploited()', '5.0.0', 'tsf()->query()->utils()->is_query_exploited()' );

		return $tsf->query()->utils()->is_query_exploited();
	}

	/**
	 * Determines whether a page or blog is on front.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function has_page_on_front() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->has_page_on_front()', '5.0.0', 'tsf()->query()->utils()->has_page_on_front()' );

		return $tsf->query()->utils()->has_page_on_front();
	}

	/**
	 * Detects if the current or inputted post type is supported and not disabled.
	 *
	 * @since 3.1.0
	 * @since 4.0.5 The `$post_type` fallback now uses a real query ID, instead of `$GLOBALS['post']`;
	 *              mitigating issues with singular-archives pages (blog, shop, etc.).
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $post_type Optional. The post type to check.
	 * @return bool
	 */
	public function is_post_type_supported( $post_type = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_post_type_supported()', '5.0.0', 'tsf()->post_type()->is_supported()' );

		return $tsf->post_type()->is_supported( $post_type );
	}

	/**
	 * Detects if the current or inputted post type's archive is supported and not disabled.
	 *
	 * @since 4.2.8
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $post_type Optional. The post type's archive to check.
	 * @return bool
	 */
	public function is_post_type_archive_supported( $post_type = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_post_type_archive_supported()', '5.0.0', 'tsf()->post_type()->is_pta_supported()' );

		return $tsf->post_type()->is_pta_supported( $post_type );
	}

	/**
	 * Checks (current) Post Type for having taxonomical archives.
	 * Memoizes the return value for the input argument.
	 *
	 * @since 2.9.3
	 * @since 4.0.5 The `$post_type` fallback now uses a real query ID, instead of `$GLOBALS['post']`;
	 *              mitigating issues with singular-archives pages (blog, shop, etc.).
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $post_type Optional. The post type to check.
	 * @return bool True when the post type has taxonomies.
	 */
	public function post_type_supports_taxonomies( $post_type = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->post_type_supports_taxonomies()', '5.0.0', 'tsf()->post_type()->supports_taxonomies()' );

		return $tsf->post_type()->supports_taxonomies( $post_type );
	}

	/**
	 * Returns a list of all supported post types with archives.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string[] Supported post types with post type archive support.
	 */
	public function get_supported_post_type_archives() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_supported_post_type_archives()', '5.0.0', 'tsf()->post_type()->get_all_supported_pta()' );

		return $tsf->post_type()->get_all_supported_pta();
	}

	/**
	 * Gets all post types that have PTA and could possibly support SEO.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string[] Public post types with post type archive support.
	 */
	public function get_public_post_type_archives() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_public_post_type_archives()', '5.0.0', 'tsf()->post_type()->get_public_pta()' );

		return $tsf->post_type()->get_public_pta();
	}

	/**
	 * Returns a list of all supported post types.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string[] All supported post types.
	 */
	public function get_supported_post_types() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_supported_post_types()', '5.0.0', 'tsf()->post_type()->get_all_supported()' );

		return $tsf->post_type()->get_all_supported();
	}

	/**
	 * Determines if the post type is disabled from SEO all optimization.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $post_type Optional. The post type to check.
	 * @return bool True if disabled, false otherwise.
	 */
	public function is_post_type_disabled( $post_type = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_post_type_disabled()', '5.0.0', 'tsf()->post_type()->is_disabled()' );

		return $tsf->post_type()->is_disabled( $post_type );
	}

	/**
	 * Determines if the taxonomy supports The SEO Framework.
	 *
	 * Checks if at least one taxonomy objects post type supports The SEO Framework,
	 * and whether the taxonomy is public and rewritable.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $taxonomy Optional. The taxonomy name.
	 * @return bool True if at least one post type in taxonomy isn't disabled.
	 */
	public function is_taxonomy_supported( $taxonomy = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_taxonomy_supported()', '5.0.0', 'tsf()->taxonomy()->is_supported()' );

		return $tsf->taxonomy()->is_supported( $taxonomy );
	}

	/**
	 * Returns a list of all supported taxonomies.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string[] All supported taxonomies.
	 */
	public function get_supported_taxonomies() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_supported_taxonomies()', '5.0.0', 'tsf()->taxonomy()->get_all_supported()' );

		return $tsf->taxonomy()->get_all_supported();
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
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $taxonomy The taxonomy name.
	 * @return bool True if at least one post type in taxonomy is supported.
	 */
	public function is_taxonomy_disabled( $taxonomy = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_taxonomy_disabled()', '5.0.0', 'tsf()->taxonomy()->is_disabled()' );

		return $tsf->taxonomy()->is_disabled( $taxonomy );
	}

	/**
	 * Determines if current query handles term meta.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_term_meta_capable() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_term_meta_capable()', '5.0.0', 'tsf()->query()->is_editable_term()' );

		return $tsf->query()->is_editable_term();
	}

	/**
	 * Returns an array of hierarchical post types.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return array The public hierarchical post types.
	 */
	public function get_hierarchical_post_types() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_hierarchical_post_types()', '5.0.0', 'tsf()->post_type()->get_all_hierarchical()' );

		return $tsf->post_type()->get_all_hierarchical();
	}

	/**
	 * Returns an array of nonhierarchical post types.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return array The public nonhierarchical post types.
	 */
	public function get_nonhierarchical_post_types() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_nonhierarchical_post_types()', '5.0.0', 'tsf()->post_type()->get_all_nonhierarchical()' );

		return $tsf->post_type()->get_all_nonhierarchical();
	}

	/**
	 * Returns hierarchical taxonomies for post type.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $get       Whether to get the names or objects.
	 * @param string $post_type The post type. Will default to current post type.
	 * @return object[]|string[] The post type taxonomy objects or names.
	 */
	public function get_hierarchical_taxonomies_as( $get = 'objects', $post_type = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_hierarchical_taxonomies_as()', '5.0.0', 'tsf()->taxonomy()->get_hierarchical()' );

		return $tsf->taxonomy()->get_hierarchical( $get, $post_type );
	}

	/**
	 * Returns the post type object label. Either plural or singular.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $post_type The post type. Required.
	 * @param bool   $singular  Whether to get the singlural or plural name.
	 * @return string The Post Type name/label, if found.
	 */
	public function get_post_type_label( $post_type, $singular = true ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_post_type_label()', '5.0.0', 'tsf()->post_type()->get_label()' );

		return $tsf->post_type()->get_label( $post_type, $singular );
	}

	/**
	 * Returns the taxonomy type object label. Either plural or singular.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $tax_type The taxonomy type. Required.
	 * @param bool   $singular Whether to get the singlural or plural name.
	 * @return string The Taxonomy Type name/label, if found.
	 */
	public function get_tax_type_label( $tax_type, $singular = true ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_tax_type_label()', '5.0.0', 'tsf()->taxonomy()->get_label()' );

		return $tsf->taxonomy()->get_label( $tax_type, $singular );
	}

	/**
	 * Generates the Open Graph type based on query status.
	 *
	 * @since 2.7.0
	 * @since 5.0.0 1. An image is no longer required to generate the 'article' type.
	 *              2. Deprecated.
	 * @deprecated
	 *
	 * @return string The Open Graph type.
	 */
	public function generate_og_type() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->generate_og_type()', '5.0.0', 'tsf()->open_graph()->get_type()' );

		return $tsf->open_graph()->get_type();
	}

	/**
	 * Returns Open Graph type value.
	 *
	 * @since 2.8.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string
	 */
	public function get_og_type() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_og_type()', '5.0.0', 'tsf()->open_graph()->get_type()' );

		return $tsf->open_graph()->get_type();
	}

	/**
	 * Returns the redirect URL, if any.
	 *
	 * @since 4.1.4
	 * @since 4.2.0 1. Now supports the `$args['pta']` index.
	 *              2. Now redirects post type archives.
	 * @since 5.0.0 1. Now expects an ID before getting a post meta item.
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
		$tsf->_deprecated_function( 'tsf()->get_redirect_url()', '5.0.0', 'tsf()->uri()->get_redirect_url()' );

		return $tsf->uri()->get_redirect_url( $args );
	}

	/**
	 * Fetches public blogname (site title).
	 *
	 * Do not consider this function safe for printing!
	 *
	 * @since 2.5.2
	 * @since 4.2.0 1. Now listens to the new `site_title` option.
	 *              2. Now applies filters.
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string $blogname The sanitized blogname.
	 */
	public function get_blogname() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_blogname()', '5.0.0', 'tsf()->data()->blog()->get_public_blog_name()' );

		return $tsf->data()->blog()->get_public_blog_name();
	}

	/**
	 * Fetches blogname (site title).
	 *
	 * @since 4.2.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string $blogname The sanitized blogname.
	 */
	public function get_filtered_raw_blogname() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_filtered_raw_blogname()', '5.0.0', 'tsf()->data()->blog()->get_filtered_blog_name()' );

		return $tsf->data()->blog()->get_filtered_blog_name();
	}

	/**
	 * Fetch blog description.
	 *
	 * @since 2.5.2
	 * @since 5.0.0 1. No longer memoizes the return value.
	 *              2. Deprecated.
	 * @deprecated
	 *
	 * @return string $blogname The sanitized blog description.
	 */
	public function get_blogdescription() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_blogdescription()', '5.0.0', 'tsf()->data()->blog()->get_filtered_blog_description()' );

		return $tsf->data()->blog()->get_filtered_blog_description();
	}

	/**
	 * Generates the Twitter Card type.
	 *
	 * @since 2.7.0
	 * @since 5.0.0 1. No longer falls back to an empty string on failure.
	 *              2. Deprecated.
	 * @deprecated
	 *
	 * @return string The Twitter Card type
	 */
	public function generate_twitter_card_type() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->generate_twitter_card_type()', '5.0.0', 'tsf()->twitter()->get_card_type()' );

		return $tsf->twitter()->get_card_type();
	}

	/**
	 * Returns array of Twitter Card Types
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return array Twitter Card types.
	 */
	public function get_twitter_card_types() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_twitter_card_types()', '5.0.0', 'tsf()->twitter()->get_supported_cards()' );

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
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return array False when it shouldn't be used. Array of available cards otherwise.
	 */
	public function get_available_twitter_cards() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_available_twitter_cards()', '5.0.0' );

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
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The separator.
	 */
	public function get_separator() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_separator()', '5.0.0', 'tsf()->title()->get_separator()' );

		return $tsf->title()->get_separator();
	}

	/**
	 * Gets Title Separator.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Separator, unescaped.
	 */
	public function get_title_separator() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_title_separator()', '5.0.0', 'tsf()->title()->get_separator()' );

		return $tsf->title()->get_separator();
	}

	/**
	 * List of title separators.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return array Title separators.
	 */
	public function get_separator_list() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_separator_list()', '5.0.0', 'tsf()->title()->utils()->get_separator_list()' );

		return $tsf->title()->utils()->get_separator_list();
	}

	/**
	 * Trims the excerpt by word and determines sentence stops.
	 *
	 * Warning: Returns with entities encoded. The output is not safe for printing.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
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

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->trim_excerpt()', '5.0.0', 'tsf()->format()->strings()->clamp_sentence()' );

		return $tsf->format()->strings()->clamp_sentence( $excerpt, $min_char_length, $max_char_length );
	}

	/**
	 * Fetches or parses the excerpt of the post.
	 *
	 * @since 1.0.0
	 * @since 5.0.0 Deprecated.
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
		$tsf->_deprecated_function( 'tsf()->get_excerpt_by_id()', '5.0.0', 'tsf()->description()->excerpt()->get_excerpt_from_args()' );

		$excerpt = $excerpt ?: $tsf->description()->get_excerpt_from_args( [ 'id' => $id ] );

		// NOTE: the new s_excerpt does NOT escape nor sanitize.
		return $escape ? $tsf->s_excerpt( $excerpt ) : $tsf->s_excerpt_raw( $excerpt );
	}

	/**
	 * Fetches excerpt from post excerpt or fetches the full post content.
	 * Determines if a page builder is used to return an empty string.
	 * Does not sanitize output.
	 *
	 * @since 2.5.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param \WP_Post|int|null $post The Post or Post ID. Leave null to get current post.
	 * @return string The excerpt.
	 */
	public function fetch_excerpt( $post = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->fetch_excerpt()', '5.0.0', 'tsf()->description()->excerpt()->get_excerpt()' );

		return $tsf->description()->excerpt()->get_excerpt(
			$post ? [ 'id' => \get_post( $post )->ID ?? '' ] : null,
		);
	}

	/**
	 * Matches WordPress locales.
	 * If not matched, it will calculate a locale.
	 *
	 * @since 2.5.2
	 * @since 5.0.0 Deleted accidentally.
	 * @since 5.1.0 1. Reintroduced.
	 *              2. Deprecated.
	 *              3. Removed the first parameter. Now always uses the current locale.
	 * @deprecated
	 *
	 * @return string Facebook acceptable OG locale.
	 */
	public function fetch_locale() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->fetch_locale()', '5.1.0', 'tsf()->open_graph()->get_locale()' );

		return $tsf->open_graph()->get_locale();
	}

	/**
	 * Returns the post's modified time.
	 *
	 * @since 4.1.4
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The current post's modified time
	 */
	public function get_modified_time() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_modified_time()', '5.0.0' );

		/**
		 * @since 2.3.0
		 * @since 5.0.0 Deprecated.
		 * @deprecated
		 * @param string $time The article modified time.
		 * @param int    $id   The current page or term ID.
		 */
		return (string) \apply_filters_deprecated(
			'the_seo_framework_modifiedtime_output',
			[
				$tsf->data()->post()->get_modified_time(),
				$tsf->query()->get_the_real_id(),
			],
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_render_data',
		);
	}

	/**
	 * Returns the custom user-inputted description.
	 *
	 * @since 3.0.6
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return string The custom field description.
	 */
	public function get_description_from_custom_field( $args = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_description_from_custom_field()', '5.0.0', 'tsf()->description()->get_custom_description()' );

		return $tsf->description()->get_custom_description( $args );
	}

	/**
	 * Returns the autogenerated meta description.
	 *
	 * @since 3.0.6
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $escape Whether to escape the description.
	 * @param string     $type   Type of description. Accepts 'search', 'opengraph', 'twitter'.
	 * @return string The generated description output.
	 */
	public function get_generated_description( $args = null, $escape = true, $type = 'search' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_generated_description()', '5.0.0', 'tsf()->description()->get_generated()' );

		return $tsf->description()->get_generated( $args, $escape, $type );
	}

	/**
	 * Returns the autogenerated Twitter meta description. Falls back to meta description.
	 *
	 * @since 3.0.4
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return string The generated Twitter description output.
	 */
	public function get_generated_twitter_description( $args = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_generated_twitter_description()', '5.0.0', 'tsf()->twitter()->get_generated_description()' );

		return $tsf->twitter()->get_generated_description( $args );
	}

	/**
	 * Returns the autogenerated Open Graph meta description. Falls back to meta description.
	 *
	 * @since 3.0.4
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return string The generated Open Graph description output.
	 */
	public function get_generated_open_graph_description( $args = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_generated_open_graph_description()', '5.0.0', 'tsf()->open_graph()->get_generated_description()' );

		return $tsf->open_graph()->get_generated_description( $args );
	}

	/**
	 * Returns supported social site locales.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return array Valid social locales
	 */
	public function supported_social_locales() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->supported_social_locales()', '5.0.0', 'tsf()->open_graph()->get_supported_locales()' );

		return $tsf->open_graph()->get_supported_locales();
	}

	/**
	 * Returns the autogenerated Open Graph meta title. Falls back to meta title.
	 * Falls back to meta title.
	 *
	 * @since 3.0.4
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return string The generated Open Graph Title.
	 */
	public function get_generated_open_graph_title( $args = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_generated_open_graph_title()', '5.0.0', 'tsf()->open_graph()->get_generated_title()' );

		// Discrepancy: The interface always hid this bug of using the wrong callback.
		// Let's keep this bug where it fetches the custom field title first.
		return $tsf->title()->get_title( $args, true ); // Discrepancy OK.
	}

	/**
	 * Returns the autogenerated Twitter meta title.
	 * Falls back to meta title.
	 *
	 * @since 3.0.4
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return string The generated Twitter Title.
	 */
	public function get_generated_twitter_title( $args = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_generated_twitter_title()', '5.0.0', 'tsf()->twitter()->get_generated_title()' );

		// Discrepancy: The interface always hid this bug of using the wrong callback.
		// Let's keep this bug where it fetches the custom field title first.
		return $tsf->title()->get_title( $args, true ); // Discrepancy OK.
	}

	/**
	 * Determines whether to add or remove title protection prefixes.
	 *
	 * @since 3.2.4
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return bool True when prefixes are allowed.
	 */
	public function use_title_protection( $args = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->use_title_protection()', '5.0.0', 'tsf()->title()->conditions()->use_protection_status()' );

		return $tsf->title()->conditions()->use_protection_status( $args );
	}

	/**
	 * Determines whether to add or remove title pagination additions.
	 *
	 * @since 3.2.4
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return bool True when additions are allowed.
	 */
	public function use_title_pagination( $args = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->use_title_pagination()', '5.0.0', 'tsf()->title()->conditions()->use_pagination()' );

		return $tsf->title()->conditions()->use_pagination( $args );
	}

	/**
	 * Determines whether to add or remove title branding additions.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null  $args  The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                           Leave null to autodetermine query.
	 * @param bool|string $social Whether the title is meant for social display.
	 *                            Also accepts string 'og' and 'twitter' for future proofing.
	 * @return bool True when additions are allowed.
	 */
	public function use_title_branding( $args = null, $social = false ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->use_title_branding()', '5.0.0', 'tsf()->title()->conditions()->use_branding()' );

		return $tsf->title()->conditions()->use_branding( $args, $social );
	}

	/**
	 * Determines whether to use the autogenerated archive title prefix or not.
	 *
	 * @since 3.1.0
	 * @since 4.0.5 1: Added first parameter `$term`.
	 *              2: Added filter.
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param \WP_Term|\WP_User|\WP_Post_Type|null $term The Term object. Leave null to autodermine query.
	 * @return bool
	 */
	public function use_generated_archive_prefix( $term = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->use_generated_archive_prefix()', '5.0.0', 'tsf()->title()->conditions()->use_generated_archive_prefix()' );

		return $tsf->title()->conditions()->use_generated_archive_prefix( $term );
	}

	/**
	 * Determines whether to add homepage tagline.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function use_home_page_title_tagline() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->use_home_page_title_tagline()', '5.0.0', 'tsf()->title()->conditions()->use_branding()' );

		return $tsf->title()->conditions()->use_branding( [ 'id' => $tsf->query()->get_the_front_page_id() ] );
	}

	/**
	 * Determines whether to add the title tagline for the post.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $id The post ID. Optional.
	 * @return bool
	 */
	public function use_singular_title_branding( $id = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->use_singular_title_branding()', '5.0.0', 'tsf()->title()->conditions()->use_branding()' );

		return $tsf->title()->conditions()->use_branding( [ 'id' => $id ] );
	}

	/**
	 * Determines whether to add the title tagline for the term.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $id The term ID. Optional.
	 * @return bool
	 */
	public function use_taxonomical_title_branding( $id = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->use_taxonomical_title_branding()', '5.0.0', 'tsf()->title()->conditions()->use_branding()' );

		return $tsf->title()->conditions()->use_branding( [
			'id'  => $id ?: $tsf->query()->get_the_real_id(),
			'tax' => $tsf->query()->get_current_taxonomy(),
		] );
	}

	/**
	 * Determines whether to add the title tagline for the pta.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $pta The post type archive. Optional.
	 * @return bool
	 */
	public function use_post_type_archive_title_branding( $pta = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->use_post_type_archive_title_branding()', '5.0.0', 'tsf()->title()->conditions()->use_branding()' );

		return $tsf->title()->conditions()->use_branding( [
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
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param bool $home The home separator location.
	 * @return string The separator location.
	 */
	public function get_title_seplocation( $home = false ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_title_seplocation()', '5.0.0', 'tsf()->title()->get_addition_location()' );

		if ( $home )
			return $tsf->get_home_title_seplocation();

		return $tsf->title()->get_addition_location();
	}

	/**
	 * Gets Title Seplocation for the homepage.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The Seplocation for the homepage.
	 */
	public function get_home_title_seplocation() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_home_title_seplocation()', '5.0.0', 'tsf()->title()->get_addition_location_for_front_page()' );

		return $tsf->title()->get_addition_location_for_front_page();
	}

	/**
	 * Returns the homepage additions (tagline) from option or bloginfo, when set.
	 *
	 * @since 4.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The trimmed tagline.
	 */
	public function get_home_title_additions() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_home_title_additions()', '5.0.0', 'tsf()->title()->get_addition_for_front_page()' );

		return $tsf->title()->get_addition_for_front_page();
	}

	/**
	 * Returns the custom user-inputted title.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $escape Whether to escape the title.
	 * @param bool       $social Whether the title is meant for social display.
	 * @return string The custom field title.
	 */
	public function get_custom_field_title( $args = null, $escape = true, $social = false ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_custom_field_title()', '5.0.0', 'tsf()->title()->get_custom_title()' );

		return $tsf->title()->get_custom_title( $args, $social );
	}

	/**
	 * Returns the autogenerated meta title.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $escape Whether to escape the title.
	 * @param bool       $social Whether the title is meant for social display.
	 * @return string The generated title output.
	 */
	public function get_generated_title( $args = null, $escape = true, $social = false ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_generated_title()', '5.0.0', 'tsf()->title()->get_generated_title()' );

		return $tsf->title()->get_generated_title( $args, $social );
	}

	/**
	 * Returns the raw filtered custom field meta title.
	 *
	 * @since 4.0.0
	 * @since 4.2.0 1. The first parameter can now be voided.
	 *              2. The first parameter is now rectified, so you can leave out indexes.
	 *              3. Now supports the `$args['pta']` index.
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                           Leave null to autodetermine query.
	 * @return string The raw generated title output.
	 */
	public function get_filtered_raw_custom_field_title( $args = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_filtered_raw_custom_field_title()', '5.0.0', 'tsf()->title()->get_bare_custom_title()' );

		return $tsf->title()->get_bare_custom_title( $args );
	}

	/**
	 * Returns the raw filtered autogenerated meta title.
	 *
	 * @since 4.0.0
	 * @since 4.2.0 1. The first parameter can now be voided.
	 *              2. The first parameter is now rectified, so you can leave out indexes.
	 *              3. Now supports the `$args['pta']` index.
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                           Leave null to autodetermine query.
	 * @return string The raw generated title output.
	 */
	public function get_filtered_raw_generated_title( $args = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_filtered_raw_generated_title()', '5.0.0', 'tsf()->title()->get_bare_generated_title()' );

		return $tsf->title()->get_bare_generated_title( $args );
	}

	/**
	 * Returns the custom user-inputted title.
	 *
	 * This doesn't use the taxonomy arguments, because, wonderously, WordPress
	 * finally admits through their code that terms can be queried using only IDs.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return string The custom field title, if it exists.
	 */
	public function get_raw_custom_field_title( $args = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_raw_custom_field_title()', '5.0.0', 'tsf()->title()->get_bare_unfiltered_custom_title()' );

		return $tsf->title()->get_bare_unfiltered_custom_title( $args );
	}

	/**
	 * Generates a title, based on expected or current query, without additions or prefixes.
	 *
	 * @since 3.1.0
	 * @since 4.2.0 1. Added memoization.
	 *              2. Now supports the `$args['pta']` index.
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return string The generated title.
	 */
	public function get_raw_generated_title( $args = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_raw_generated_title()', '5.0.0', 'tsf()->title()->get_bare_generated_title()' );

		return $tsf->title()->get_bare_generated_title( $args );
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
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The generated front page title.
	 */
	public function get_static_front_page_title() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_static_front_page_title()', '5.0.0', 'tsf()->title()->get_front_page_title()' );

		return $tsf->title()->get_front_page_title();
	}

	/**
	 * Returns the archive title. Also works in admin.
	 *
	 * @NOTE Taken from WordPress core. Altered to work for metadata and in admin.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param \WP_Term|\WP_User|\WP_Post_Type|\WP_Error|null $object The Term object or error.
	 *                                                               Leave null to autodetermine query.
	 * @return string The generated archive title, not escaped.
	 */
	public function get_generated_archive_title( $object = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_generated_archive_title()', '5.0.0', 'tsf()->title()->get_archive_title()' );

		return $tsf->title()->get_archive_title( $object );
	}

	/**
	 * Returns the archive title items. Also works in admin.
	 *
	 * @NOTE Taken from WordPress core. Altered to work for metadata.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param \WP_Term|\WP_User|\WP_Post_Type|null $object The Term object.
	 *                                                     Leave null to autodetermine query.
	 * @return String[$title,$prefix,$title_without_prefix] The generated archive title items, not escaped.
	 */
	public function get_raw_generated_archive_title_items( $object = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_raw_generated_archive_title_items()', '5.0.0', 'tsf()->title()->get_archive_title_list()' );

		return $tsf->title()->get_archive_title_list( $object );
	}

	/**
	 * Returns Post Title from ID.
	 *
	 * @NOTE Taken from WordPress core. Altered to work in the Admin area.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|\WP_Post $id The Post ID or post object.
	 * @return string The generated post title.
	 */
	public function get_generated_single_post_title( $id = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_generated_single_post_title()', '5.0.0', 'tsf()->title()->get_post_title()' );

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
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param null|\WP_Term $term The term name, required in the admin area.
	 * @return string The generated single term title.
	 */
	public function get_generated_single_term_title( $term = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_generated_single_term_title()', '5.0.0', 'tsf()->title()->get_term_title()' );

		return $tsf->title()->get_term_title( $term );
	}

	/**
	 * Fetches single term title.
	 *
	 * @NOTE Taken from WordPress core. Altered to work in the Admin area.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $post_type The post type.
	 * @return string The generated post type archive title.
	 */
	public function get_generated_post_type_archive_title( $post_type = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_generated_post_type_archive_title()', '5.0.0', 'tsf()->title()->get_post_type_archive_title()' );

		return $tsf->title()->get_post_type_archive_title( $post_type );
	}

	/**
	 * Returns untitled title.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The untitled title.
	 */
	public function get_static_untitled_title() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_static_untitled_title()', '5.0.0', 'tsf()->title()->get_untitled_title()' );

		return $tsf->title()->get_untitled_title();
	}

	/**
	 * Returns search title.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The generated search title, partially escaped.
	 */
	public function get_generated_search_query_title() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_generated_search_query_title()', '5.0.0', 'tsf()->title()->get_search_query_title()' );

		return $tsf->title()->get_search_query_title();
	}

	/**
	 * Returns 404 title.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The generated 404 title.
	 */
	public function get_static_404_title() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_static_404_title()', '5.0.0', 'tsf()->title()->get_404_title()' );

		return $tsf->title()->get_404_title();
	}

	/**
	 * Merges title branding, when allowed.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string     $title The title. Passed by reference.
	 * @param array|null $args  The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                          Leave null to autodetermine query.
	 */
	public function merge_title_branding( &$title, $args = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->merge_title_branding()', '5.0.0', 'tsf()->title()->add_branding()' );

		$title = $tsf->title()->add_branding( $title, $args );
	}

	/**
	 * Merges pagination with the title, if paginated.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $title The title. Passed by reference.
	 */
	public function merge_title_pagination( &$title ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->merge_title_pagination()', '5.0.0', 'tsf()->title()->add_pagination()' );

		$title = $tsf->title()->add_pagination( $title );
	}

	/**
	 * Merges title protection prefixes.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string     $title The title. Passed by reference.
	 * @param array|null $args  The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                          Leave null to autodetermine query.
	 * @return void
	 */
	public function merge_title_protection( &$title, $args = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->merge_title_protection()', '5.0.0', 'tsf()->title()->add_protection_status()' );

		$title = $tsf->title()->add_protection_status( $title, $args );
	}

	/**
	 * Determines if the given page has a custom canonical URL.
	 *
	 * @since 3.2.4
	 * @since 4.2.0 1. Now also detects canonical URLs for taxonomies.
	 *              2. Now also detects canonical URLs for PTAs.
	 *              3. Now supports the `$args['pta']` index.
	 * @since 5.0.0 Deprecated.
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
		$tsf->_deprecated_function( 'tsf()->has_custom_canonical_url()', '5.0.0', 'tsf()->uri()->get_custom_canonical_url()' );

		return (bool) $tsf->uri()->get_custom_canonical_url( $args );
	}

	/**
	 * Returns the home URL. Created for the WordPress method is slow for it
	 * performs "set_url_scheme" calls slowly. We rely on this method for some
	 * plugins filter `home_url`.
	 * Memoized.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The home URL.
	 */
	public function get_home_url() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_home_url()', '5.0.0', 'tsf->data()->blog()->get_front_page_url()' );

		return $tsf->data()->blog()->get_front_page_url();
	}

	/**
	 * Returns preferred $url scheme.
	 * Which can automatically be detected when not set, based on the site URL setting.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The preferred URl scheme.
	 */
	public function get_preferred_scheme() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_preferred_scheme()', '5.0.0', 'tsf->uri()->utils()->get_preferred_url_scheme()' );

		return $tsf->uri()->utils()->get_preferred_url_scheme();
	}

	/**
	 * Sets URL to preferred URL scheme.
	 * Does not sanitize output.
	 *
	 * @since 2.8.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $url The URL to set scheme for.
	 * @return string The URL with the preferred scheme.
	 */
	public function set_preferred_url_scheme( $url ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->set_preferred_url_scheme()', '5.0.0', 'tsf->uri()->utils()->set_preferred_url_scheme()' );

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
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The detected URl scheme, lowercase.
	 */
	public function detect_site_url_scheme() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->detect_site_url_scheme()', '5.0.0', 'uri()->utils()->detect_site_url_scheme()' );

		return $tsf->uri()->utils()->detect_site_url_scheme();
	}

	/**
	 * Sets URL scheme for input URL.
	 * WordPress core function, without filter.
	 *
	 * @since 2.4.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $url    Absolute url that includes a scheme.
	 * @param string $scheme Optional. Scheme to give $url. Currently 'http', 'https', 'login', 'login_post', 'admin', or 'relative'.
	 * @return string url with chosen scheme.
	 */
	public function set_url_scheme( $url, $scheme = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->set_url_scheme()', '5.0.0', 'uri()->utils()->set_url_scheme()' );

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
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $url Required the current maybe not fully qualified URL.
	 * @return string $url
	 */
	public function make_fully_qualified_url( $url ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->make_fully_qualified_url()', '5.0.0', 'uri()->utils()->make_fully_qualified_url()' );

		return $tsf->uri()->utils()->make_fully_qualified_url( $url );
	}

	/**
	 * Caches and returns the current URL.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The current URL.
	 */
	public function get_current_canonical_url() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_current_canonical_url()', '5.0.0', 'tsf()->uri()->get_canonical_url()' );

		return $tsf->uri()->get_canonical_url();
	}

	/**
	 * Caches and returns the current permalink.
	 * This link excludes any pagination. Great for structured data.
	 *
	 * Does not work for unregistered pages, like search, 404, date, author, and CPTA.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The current permalink.
	 */
	public function get_current_permalink() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_current_permalink()', '5.0.0', 'tsf()->uri()->get_generated_url()' );

		return $tsf->uri()->get_generated_url();
	}

	/**
	 * Caches and returns the homepage URL.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The home URL.
	 */
	public function get_homepage_permalink() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_homepage_permalink()', '5.0.0', 'tsf()->uri()->get_bare_front_page_url()' );

		return $tsf->uri()->get_bare_front_page_url();
	}

	/**
	 * Returns a canonical URL based on parameters.
	 * The URL will never be paginated.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Deprecated.
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
		$tsf->_deprecated_function( 'tsf()->create_canonical_url()', '5.0.0', 'tsf->uri()->get_canonical_url() or tsf->uri()->get_custom_canonical_url()' );

		if ( empty( $args['get_custom_field'] ) )
			return $tsf->uri()->get_canonical_url( $args ?: null );

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
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The home canonical URL.
	 */
	public function get_home_canonical_url() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_home_canonical_url()', '5.0.0', 'tsf->uri()->get_front_page_url()' );

		return $tsf->uri()->get_front_page_url();
	}

	/**
	 * Returns home canonical URL without query considerations.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The home canonical URL without query considerations.
	 */
	public function get_raw_home_canonical_url() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_raw_home_canonical_url()', '5.0.0', 'tsf->uri()->get_bare_front_page_url()' );

		return $tsf->uri()->get_bare_front_page_url();
	}

	/**
	 * Returns singular canonical URL.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|null $post_id The page ID. Leave null to autodetermine.
	 * @return string The custom canonical URL, if any.
	 */
	public function get_singular_canonical_url( $post_id = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_singular_canonical_url()', '5.0.0', 'tsf()->uri()->get_singular_url()' );

		return $tsf->uri()->get_singular_url( $post_id );
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
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|null $term_id  The term ID. Leave null to autodetermine.
	 * @param string   $taxonomy The taxonomy. Leave empty to autodetermine.
	 * @return string The taxonomical canonical URL, if any.
	 */
	public function get_taxonomical_canonical_url( $term_id = null, $taxonomy = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_taxonomical_canonical_url()', '5.0.0', 'tsf()->uri()->get_term_url()' );

		return $tsf->uri()->get_term_url( $term_id, $taxonomy );
	}

	/**
	 * Returns post type archive canonical URL.
	 *
	 * @since 3.0.0
	 * @since 4.0.0 1. Deprecated first parameter as integer. Use strings or null.
	 *              2. Now forwards post type object calling to WordPress's function.
	 * @since 4.2.0 1. Now correctly adds pagination to the URL.
	 *              2. Removed argument type deprecation doing it wrong warning.
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param null|string $post_type The post type archive's post type.
	 *                               Leave null to autodetermine query and allow pagination.
	 * @return string The post type archive canonical URL, if any.
	 */
	public function get_post_type_archive_canonical_url( $post_type = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_post_type_archive_canonical_url()', '5.0.0', 'tsf()->uri()->get_pta_url()' );

		return $tsf->uri()->get_pta_url( $post_type );
	}

	/**
	 * Returns author canonical URL.
	 * Automatically adds pagination if the ID matches the query.
	 *
	 * @since 3.0.0
	 * @since 4.2.0 1. The first parameter is now optional.
	 *              2. When the $id isn't set, the URL won't get tested for pagination issues.
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|null $id The author ID. Leave null to autodetermine.
	 * @return string The author canonical URL, if any.
	 */
	public function get_author_canonical_url( $id = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_author_canonical_url()', '5.0.0', 'tsf()->uri()->get_author_url()' );

		return $tsf->uri()->get_author_url( $id );
	}

	/**
	 * Returns date canonical URL.
	 * Automatically adds pagination if the date input matches the query.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $year  The year.
	 * @param int $month The month.
	 * @param int $day   The day.
	 * @return string The author canonical URL, if any.
	 */
	public function get_date_canonical_url( $year, $month = null, $day = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_date_canonical_url()', '5.0.0', 'tsf()->uri()->get_date_url()' );

		return $tsf->uri()->get_date_url( $year, $month, $day );
	}

	/**
	 * Returns search canonical URL.
	 * Automatically adds pagination if the input matches the query.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 1. The first parameter now defaults to null.
	 *              2. The search term is now matched with the input query if not set,
	 *                 instead of it being empty.
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $search_query The search query. Mustn't be escaped.
	 *                             When left empty, the current query will be used.
	 * @return string The search link.
	 */
	public function get_search_canonical_url( $search_query = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_search_canonical_url()', '5.0.0', 'tsf()->uri()->get_search_url()' );

		return $tsf->uri()->get_search_url( $search_query );
	}

	/**
	 * Adds pagination to input URL.
	 *
	 * @since 4.2.3
	 * @since 5.0.0 Deprecated.
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
		$tsf->_deprecated_function( 'tsf()->add_pagination_to_url()', '5.0.0', 'tsf()->uri()->utils()->add_pagination_to_url()' );

		return $tsf->uri()->utils()->add_pagination_to_url( $url, $page, $use_base );
	}

	/**
	 * Adds pagination to input URL.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Deprecated.
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
		$tsf->_deprecated_function( 'tsf()->add_url_pagination()', '5.0.0', 'tsf()->uri()->utils()->add_pagination_to_url()' );

		return $tsf->uri()->utils()->add_pagination_to_url( $url, $page, $use_base );
	}

	/**
	 * Removes pagination from input URL.
	 * The URL must match this query if no second parameter is provided.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Deprecated.
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
		$tsf->_deprecated_function( 'tsf()->remove_pagination_from_url()', '5.0.0', 'tsf->uri()->utils()->remove_pagination_from_url()' );

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
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $next_prev Whether to get the previous or next page link.
	 *                          Accepts 'prev' and 'next'.
	 * @return string Escaped site Pagination URL
	 */
	public function get_paged_url( $next_prev ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_paged_url()', '5.0.0', 'tsf->uri()->get_paged_url()' );

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
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return array Escaped site Pagination URLs: {
	 *    string 'prev'
	 *    string 'next'
	 * }
	 */
	public function get_paged_urls() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_paged_urls()', '5.0.0', 'tsf->uri()->get_paged_url()' );

		[ $next, $prev ] = $tsf->uri()->get_paged_urls();

		return compact( 'next', 'prev' );
	}

	/**
	 * Fetches home URL host. Like "wordpress.org".
	 * If this fails, you're going to have a bad time.
	 *
	 * @since 2.7.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The home URL host.
	 */
	public function get_home_host() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_home_host()', '5.0.0', 'tsf->uri()->utils()->get_site_host()' );

		return $tsf->uri()->utils()->get_site_host();
	}

	/**
	 * Appends given query to given URL.
	 *
	 * @since 4.1.4
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $url   A fully qualified URL.
	 * @param string $query A fully qualified query taken from parse_url( $url, \PHP_URL_QUERY );
	 * @return string A fully qualified URL with appended $query.
	 */
	public function append_url_query( $url, $query = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->append_url_query()', '5.0.0', 'tsf->uri()->utils()->append_url_query()' );

		return $tsf->uri()->utils()->append_query_to_url( $url, $query );
	}

	/**
	 * Tests if input URL matches current domain.
	 *
	 * @since 2.9.4
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $url The URL to test. Required.
	 * @return bool true on match, false otherwise.
	 */
	public function matches_this_domain( $url ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->matches_this_domain()', '5.0.0', 'tsf->uri()->utils()->url_matches_blog_domain()' );

		return $tsf->uri()->utils()->url_matches_blog_domain( $url );
	}

	/**
	 * Makes a fully qualified URL from any input.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $path Either the URL or path. Will always be transformed to the current domain.
	 * @param string $url  The URL to add the path to. Defaults to the current home URL.
	 * @return string $url
	 */
	public function convert_to_url_if_path( $path, $url = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->convert_to_url_if_path()', '5.0.0', 'tsf->uri()->utils()->convert_path_to_url()' );

		return $tsf->uri()->utils()->convert_path_to_url( $path, $url );
	}

	/**
	 * Returns singular custom field's canonical URL.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|null $id The page ID.
	 * @return string The custom canonical URL, if any.
	 */
	public function get_singular_custom_canonical_url( $id = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_singular_custom_canonical_url()', '5.0.0', 'tsf->uri()->get_custom_canonical_url()' );

		return $tsf->data()->plugin()->post()->get_meta_item( '_genesis_canonical_uri', $id ) ?: '';
	}

	/**
	 * Returns taxonomical custom field's canonical URL.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $term_id The term ID.
	 * @return string The custom canonical URL, if any.
	 */
	public function get_taxonomical_custom_canonical_url( $term_id = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_taxonomical_custom_canonical_url()', '5.0.0', 'tsf->uri()->get_custom_canonical_url()' );

		return $tsf->data()->plugin()->term()->get_meta_item( 'canonical', $term_id ) ?: '';
	}

	/**
	 * Returns post type archive custom field's canonical URL.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $pta The post type.
	 * @return string The custom canonical URL, if any.
	 */
	public function get_post_type_archive_custom_canonical_url( $pta = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_post_type_archive_custom_canonical_url()', '5.0.0', 'tsf->uri()->get_custom_canonical_url()' );

		return $tsf->data()->plugin()->pta()->get_meta_item( 'canonical', $pta ) ?: '';
	}

	/**
	 * Generates shortlink URL.
	 *
	 * @since 2.2.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string|null Escaped site Shortlink URL.
	 */
	public function get_shortlink() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_shortlink()', '5.0.0', 'tsf->uri()->get_shortlink()' );

		return $tsf->uri()->get_shortlink_url();
	}

	/**
	 * Caches current Image URL in static variable.
	 * To be used on the front-end only.
	 *
	 * @since 2.2.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The image URL.
	 */
	public function get_image_from_cache() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_image_from_cache()', '5.0.0', 'tsf()->get_first_valid_image()' );

		foreach ( $tsf->image()->get_image_details( null, true ) as $image ) {
			$url = $image['url'];
			if ( $url ) break;
		}

		return $url ?? '';
	}

	/**
	 * Returns the image details from cache.
	 * Only to be used within the loop, uses default parameters, inlucing the 'social' context.
	 *
	 * @since 4.0.0
	 * @since 4.1.2 Added a $single parameter, which helps reduce processing power required.
	 *              This parameter might get deprecated when we start supporting PHP 7.1+ only.
	 * @since 5.0.0 Deprecated.
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
		$tsf->_deprecated_function( 'tsf()->get_image_details_from_cache()', '5.0.0', 'tsf()->get_image_details()' );

		return $tsf->get_image_details( null, $single );
	}

	/**
	 * Returns single custom field image details.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
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
		$tsf->_deprecated_function( 'tsf()->get_custom_field_image_details()', '5.0.0', 'tsf()->image()->get_custom_image_details()' );

		return $tsf->image()->get_custom_image_details( $args, $single );
	}

	/**
	 * Returns single or multiple generates image details.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array|null $args    The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
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
		$tsf->_deprecated_function( 'tsf()->get_generated_image_details()', '5.0.0', 'tsf()->image()->get_generated_image_details()' );

		return $tsf->image()->get_generated_image_details( $args, $single, $context );
	}

	/**
	 * Adds image dimension and alt parameters to the input details, if any.
	 *
	 * @since 4.0.0
	 * @since 4.2.4 1. Now returns filesizes under index `filesize`.
	 *              2. No longer processes details when no `id` is given in `$details`.
	 * @since 5.0.0 Deprecated.
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
		$tsf->_deprecated_function( 'tsf()->merge_extra_image_details()', '5.0.0', 'tsf()->image()->merge_extra_image_details()' );

		return $tsf->image()->merge_extra_image_details( $details, $size );
	}

	/**
	 * Fetches image dimensions.
	 *
	 * @TODO shift parameters and deprecate using the third one.
	 * @since 4.0.0
	 * @since 4.2.4 1. No longer relies on `$url` to fetch the correct dimensions, improving performance significantly.
	 *              2. Renamed `$url` to `$depr`, without a deprecation notice added.
	 * @since 5.0.0 Deprecated.
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
		$tsf->_deprecated_function( 'tsf()->get_image_dimensions()', '5.0.0', 'tsf()->image()->utils()->get_image_dimensions()' );

		return $tsf->image()->utils()->get_image_dimensions( $src_id, $size );
	}

	/**
	 * Fetches image dimensions.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $src_id The source ID of the image.
	 * @return string The image alt tag
	 */
	public function get_image_alt_tag( $src_id ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_image_alt_tag()', '5.0.0', 'tsf()->image()->utils()->get_image_alt_tag()' );

		return $tsf->image()->utils()->get_image_alt_tag( $src_id );
	}

	/**
	 * Fetches image filesize in bytes. Requires an image (re)generated in WP 6.0 or later.
	 *
	 * @since 4.2.4
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int    $src_id The source ID of the image.
	 * @param string $size   The size of the image used.
	 * @return int The image filesize in bytes. Returns 0 for unprocessed/unprocessable image.
	 */
	public function get_image_filesize( $src_id, $size ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_image_filesize()', '5.0.0', 'tsf()->image()->utils()->get_image_filesize()' );

		return $tsf->image()->utils()->get_image_filesize( $src_id, $size );
	}

	/**
	 * Returns the largest acceptable image size's details.
	 * Skips the original image, which may also be acceptable.
	 *
	 * @since 4.0.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $id           The image ID.
	 * @param int $max_size     The largest acceptable dimension in pixels. Accounts for both width and height.
	 * @param int $max_filesize The largest acceptable filesize in bytes. Default 5MB (5242880).
	 * @return false|array Returns an array (url, width, height, is_intermediate), or false, if no image is available.
	 */
	public function get_largest_acceptable_image_src( $id, $max_size = 4096, $max_filesize = 5242880 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_largest_acceptable_image_src()', '5.0.0', 'tsf()->image()->utils()->get_largest_image_src()' );

		return $tsf->image()->utils()->get_largest_image_src( $id, $max_size, $max_filesize );
	}

	/**
	 * Returns the minimum role required to adjust settings.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The minimum required capability for SEO Settings.
	 */
	public function get_settings_capability() {
		\tsf()->_deprecated_function( 'tsf()->get_settings_capability()', '5.0.0', 'constant THE_SEO_FRAMEWORK_SETTINGS_CAP' );
		return \THE_SEO_FRAMEWORK_SETTINGS_CAP;
	}

	/**
	 * Determines if the current user can do settings.
	 * Not cached as it's imposing security functionality.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function can_access_settings() {
		\tsf()->_deprecated_function( 'tsf()->can_access_settings()', '5.0.0', 'current_user_can( THE_SEO_FRAMEWORK_SETTINGS_CAP )' );
		return \current_user_can( \THE_SEO_FRAMEWORK_SETTINGS_CAP );
	}

	/**
	 * Echos the header meta and scripts.
	 *
	 * @since 1.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 */
	public function html_output() {

		$tsf = \tsf();

		$tsf->_deprecated_function( 'tsf()->html_output()', '5.0.0', 'tsf()->print_seo_meta_tags()' );
		$tsf->print_seo_meta_tags();
	}

	/**
	 * Outputs all meta tags for the current query.
	 *
	 * @since 4.1.4
	 * @since 4.2.0 1. Now invokes two actions before and after output.
	 *              2. No longer rectifies time zones.
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 */
	public function do_meta_output() {
		\tsf()->_deprecated_function( 'tsf()->do_meta_output()', '5.0.0', 'tsf()->print_seo_meta_tags()' );
		\The_SEO_Framework\Front\Meta\Head::print_tags();
	}

	/**
	 * Holds default site options.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return array Default site options.
	 */
	public function get_default_site_options() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_default_site_options()', '5.0.0', 'tsf()->data()->plugin()->setup()->get_default_options()' );

		return $tsf->data()->plugin()->setup()->get_default_options();
	}

	/**
	 * Holds warned site options array.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return array $options.
	 */
	public function get_warned_site_options() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_warned_site_options()', '5.0.0', 'tsf()->data()->plugin()->setup()->get_warned_options()' );

		return $tsf->data()->plugin()->setup()->get_warned_options();
	}

	/**
	 * Return current option array.
	 * Memoizes the return value, can be bypassed and reset with second parameter.
	 *
	 * This method does NOT merge the default post options.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $setting The setting key.
	 * @return array Options.
	 */
	public function get_all_options( $setting = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_all_options()', '5.0.0', 'tsf()->get_options()' );

		if ( ! $setting )
			return $tsf->get_options();

		/**
		 * @since 2.0.0
		 * @since 4.1.4 1. Now considers headlessness.
		 *              2. Now returns a 3rd parameter: boolean $headless.
		 *
		 * @param array  $settings The settings
		 * @param string $setting  The settings field.
		 * @param bool   $headless Whether the options are headless.
		 */
		return \apply_filters(
			'the_seo_framework_get_options',
			\get_option( $setting ),
			$setting,
			false,
		);
	}

	/**
	 * Return Default SEO options from the SEO options array.
	 *
	 * @since 2.2.5
	 * @since 4.2.0 1. Now supports an option index as `$key`.
	 *              2. Removed second parameter (`$use_cache`).
	 *              3. Now always memoizes.
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string|string[] $key Required. The option name, or a map of indexes.
	 * @return mixed The default option. Null if it's not registered.
	 */
	public function get_default_option( $key ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_default_option()', '5.0.0', 'tsf()->data()->plugin()->setup()->get_default_option()' );

		return $tsf->data()->plugin()->setup()->get_default_option( ...(array) $key );
	}

	/**
	 * Return Warned SEO options from the SEO options array.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string|string[] $key Required. The option name, or a map of indexes.
	 * @return bool True if warning is registered. False otherwise.
	 */
	public function get_warned_option( $key ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_warned_option()', '5.0.0', 'tsf()->data()->plugin()->setup()->get_warned_option()' );

		return $tsf->data()->plugin()->setup()->get_warned_option( ...(array) $key );
	}

	/**
	 * Returns the option key for Post Type robots settings.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $type Accepts 'noindex', 'nofollow', 'noarchive'.
	 * @return string
	 */
	public function get_robots_post_type_option_id( $type ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_robots_post_type_option_id()', '5.0.0', 'tsf()->data()->plugin()->helper()->get_robots_option_index()' );

		return $tsf->data()->plugin()->helper()->get_robots_option_index( 'post_type', $type );
	}

	/**
	 * Returns the option key for Taxonomy robots settings.
	 *
	 * @since 4.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $type Accepts 'noindex', 'nofollow', 'noarchive'.
	 * @return string
	 */
	public function get_robots_taxonomy_option_id( $type ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_robots_taxonomy_option_id()', '5.0.0', 'tsf()->data()->plugin()->helper()->get_robots_option_index()' );

		return $tsf->data()->plugin()->helper()->get_robots_option_index( 'taxonomy', $type );
	}

	/**
	 * Allows bulk-updating of the SEO settings.
	 *
	 * @since 2.7.0
	 * @since 5.0.0 Deprecated.
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
		$tsf->_deprecated_function( 'tsf()->update_settings()', '5.0.0', 'tsf()->data()->plugin()->update_option()' );

		if ( ! $settings_field )
			return $tsf->data()->plugin()->update_option( \is_array( $new_option ) ? $new_option : [ $new_option => '' ] );

		return \update_option(
			$settings_field,
			\wp_parse_args( $new_option, \get_option( $settings_field ) ),
			true,
		);
	}

	/**
	 * Retrieves a single caching option.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $key     The option key. Required.
	 * @param string $default The default cache value.
	 * @return mixed Cache value on success, $default if non-existent.
	 */
	public function get_static_cache( $key, $default = false ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_static_cache()', '5.0.0', 'tsf()->data()->plugin()->get_site_cache()' );

		return $tsf->data()->plugin()->get_site_cache( $key ) ?? $default;
	}

	/**
	 * Updates a single caching option.
	 *
	 * Can return false if option is unchanged.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $key   The cache key. Required.
	 * @param string $value The cache value. Expected to be sanitized.
	 * @return bool True on success, false on failure.
	 */
	public function update_static_cache( $key, $value = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->update_static_cache()', '5.0.0', 'tsf()->data()->plugin()->update_site_cache()' );

		return $tsf->data()->plugin()->update_site_cache( $key, $value );
	}

	/**
	 * Returns the term meta item by key.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $item      The item to get.
	 * @param int    $term_id   The Term ID.
	 * @return mixed The term meta item. Null when not found.
	 */
	public function get_term_meta_item( $item, $term_id = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_term_meta_item()', '5.0.0', 'tsf()->data()->plugin()->term()->get_meta_item()' );

		return $tsf->data()->plugin()->term()->get_meta_item( $item, $term_id );
	}

	/**
	 * Returns term meta data from ID.
	 * Memoizes the return value for the current request.
	 *
	 * Returns Genesis 2.3.0+ data if no term meta data is set via compat module.
	 *
	 * @since 2.7.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $term_id The Term ID.
	 * @return array The term meta data.
	 */
	public function get_term_meta( $term_id ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_term_meta()', '5.0.0', 'tsf()->data()->plugin()->term()->get_meta()' );

		return $tsf->data()->plugin()->term()->get_meta( $term_id );
	}

	/**
	 * Returns an array of default term options.
	 *
	 * @since 2.7.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $term_id The term ID.
	 * @return array The Term Metadata default options.
	 */
	public function get_term_meta_defaults( $term_id = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_term_meta_defaults()', '5.0.0', 'tsf()->data()->plugin()->term()->get_default_meta()' );

		return $tsf->data()->plugin()->term()->get_default_meta( $term_id );
	}

	/**
	 * Updates single term meta value.
	 *
	 * Note that this method can be more resource intensive than you intend it to be,
	 * as it reprocesses all term meta.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $item    The item to update.
	 * @param mixed  $value   The value the item should be at.
	 * @param int    $term_id Term ID.
	 */
	public function update_single_term_meta_item( $item, $value, $term_id ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->update_single_term_meta_item()', '5.0.0', 'tsf()->data()->plugin()->term()->update_single_meta_item()' );

		return $tsf->data()->plugin()->term()->update_single_meta_item( $item, $value, $term_id );
	}

	/**
	 * Updates term meta from input.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term Taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 * @param array  $data     The data to save.
	 */
	public function save_term_meta( $term_id, $tt_id, $taxonomy, $data ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->save_term_meta()', '5.0.0', 'tsf()->data()->plugin()->term()->save_meta()' );

		return $tsf->data()->plugin()->term()->save_meta( $term_id, $data );
	}

	/**
	 * Deletes term meta.
	 * Deletes only the default data keys; or everything when only that is present.
	 *
	 * @since 2.7.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $term_id Term ID.
	 */
	public function delete_term_meta( $term_id ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->delete_term_meta()', '5.0.0', 'tsf()->data()->plugin()->term()->delete_meta()' );

		return $tsf->data()->plugin()->term()->delete_meta( $term_id );
	}

	/**
	 * Fetch latest public category ID.
	 *
	 * @since 4.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return int Latest Category ID.
	 */
	public function get_latest_category_id() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_latest_category_id()', '5.0.0', 'tsf()->data()->term()->get_latest_category_id()' );

		return $tsf->data()->term()->get_latest_term_id( 'category' );
	}

	/**
	 * Tests whether term is populated. Also tests the child terms.
	 *
	 * @since 4.2.8
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int    $term_id The term ID.
	 * @param string $taxonomy The term taxonomy.
	 * @return bool True when term or child terms are populated, false otherwise.
	 */
	public function is_term_populated( $term_id, $taxonomy ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_latest_category_id()', '5.0.0', 'tsf()->data()->term()->is_term_populated()' );

		return $tsf->data()->term()->is_term_populated( $term_id, $taxonomy );
	}

	/**
	 * Fetch latest public post/page ID.
	 *
	 * @since 2.4.3
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return int Latest Post ID.
	 */
	public function get_latest_post_id() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_latest_post_id()', '5.0.0', 'tsf()->data()->post()->get_latest_post_id()' );

		return $tsf->data()->post()->get_latest_post_id();
	}

	/**
	 * Returns the primary term for post.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 1. Now always tries to return a term if none is set manually.
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
		$tsf->_deprecated_function( 'tsf()->get_primary_term()', '5.0.0', 'tsf()->data()->plugin()->post()->get_primary_term()' );

		return $tsf->data()->plugin()->post()->get_primary_term( $post_id, $taxonomy );
	}

	/**
	 * Returns the primary term ID for post.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int    $post_id  The post ID.
	 * @param string $taxonomy The taxonomy name.
	 * @return int   The primary term ID. 0 if not found.
	 */
	public function get_primary_term_id( $post_id, $taxonomy ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_primary_term_id()', '5.0.0', 'tsf()->data()->plugin()->post()->get_primary_term_id()' );

		return $tsf->data()->plugin()->post()->get_primary_term_id( $post_id, $taxonomy );
	}

	/**
	 * Updates the primary term ID for post.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|null $post_id  The post ID.
	 * @param string   $taxonomy The taxonomy name.
	 * @param int      $value    The new value. If empty, it will delete the entry.
	 * @return bool True on success, false on failure.
	 */
	public function update_primary_term_id( $post_id = null, $taxonomy = '', $value = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->update_primary_term_id()', '5.0.0', 'tsf()->data()->plugin()->post()->update_primary_term_id()' );

		return $tsf->data()->plugin()->post()->update_primary_term_id( $post_id, $taxonomy, $value );
	}

	/**
	 * Returns the user meta item by key.
	 *
	 * @since 4.1.4
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $item      The item to get. Required.
	 * @param int    $user_id   The user ID. Optional.
	 * @return mixed The user meta item. Null when not found.
	 */
	public function get_user_meta_item( $item, $user_id = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_user_meta_item()', '5.0.0', 'tsf()->data()->plugin()->user()->get_meta_item()' );

		return $tsf->data()->plugin()->user()->get_meta_item( $item, $user_id );
	}

	/**
	 * Returns the author meta item by key.
	 *
	 * @since 4.1.4
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $item      The item to get. Required.
	 * @return mixed The author meta item. Null when not found.
	 */
	public function get_current_post_author_meta_item( $item ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_current_post_author_meta_item()', '5.0.0', 'tsf()->data()->plugin()->user()->get_current_post_author_meta_item()' );

		return $tsf->data()->plugin()->user()->get_current_post_author_meta_item( $item );
	}

	/**
	 * Returns and caches author meta for the current query.
	 * Memoizes the return value for the current request.
	 *
	 * @since 4.1.4
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return ?array The current author meta, null when no author is set.
	 */
	public function get_current_post_author_meta() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_current_post_author_meta()', '5.0.0', 'tsf()->data()->plugin()->user()->get_current_post_author_meta()' );

		return $tsf->data()->plugin()->user()->get_current_post_author_meta();
	}

	/**
	 * Fetches usermeta set by The SEO Framework.
	 * Memoizes the return value, can be bypassed.
	 *
	 * @since 2.7.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $user_id The user ID.
	 * @return array The user SEO meta data.
	 */
	public function get_user_meta( $user_id = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_user_meta()', '5.0.0', 'tsf()->data()->plugin()->user()->get_meta()' );

		return $tsf->data()->plugin()->user()->get_meta( $user_id );
	}

	/**
	 * Returns an array of default user meta.
	 *
	 * @since 4.1.4
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $user_id The user ID. Defaults to CURRENT USER, NOT CURRENT POST AUTHOR.
	 * @return array The user meta defaults.
	 */
	public function get_user_meta_defaults( $user_id = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_user_meta_defaults()', '5.0.0', 'tsf()->data()->plugin()->user()->get_default_meta()' );

		return $tsf->data()->plugin()->user()->get_default_meta( $user_id );
	}

	/**
	 * Updates user TSF-meta option.
	 *
	 * @since 4.1.4
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int    $user_id The user ID.
	 * @param string $option  The user's SEO metadata to update.
	 * @param mixed  $value   The option value.
	 */
	public function update_single_user_meta_item( $user_id, $option, $value ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->update_single_user_meta_item()', '5.0.0', 'tsf()->data()->plugin()->user()->update_single_meta_item()' );

		return $tsf->data()->plugin()->user()->update_single_meta_item( $user_id, $option, $value );
	}

	/**
	 * Updates users meta from input.
	 *
	 * @since 4.1.4
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int   $user_id The user ID.
	 * @param array $data    The data to save.
	 */
	public function save_user_meta( $user_id, $data ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->save_user_meta()', '5.0.0', 'tsf()->data()->plugin()->user()->save_meta()' );

		return $tsf->data()->plugin()->user()->save_meta( $user_id, $data );
	}

	/**
	 * Returns the post author ID.
	 * Memoizes the return value for the current request.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $post_id The post ID to fetch the author from. Leave 0 to autodetermine.
	 * @return int Post author ID on success, 0 on failure.
	 */
	public function get_post_author_id( $post_id = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_post_author_id()', '5.0.0', 'tsf()->query()->get_post_author_id()' );

		return $tsf->query()->get_post_author_id( $post_id );
	}

	/**
	 * Returns the current post author ID.
	 * Memoizes the return value for the current request.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return int Post author ID on success, 0 on failure.
	 */
	public function get_current_post_author_id() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_current_post_author_id()', '5.0.0', 'tsf()->query()->get_post_author_id()' );

		return $tsf->query()->get_post_author_id();
	}

	/**
	 * Sets up user ID and returns it if user is found.
	 * To be used in AJAX, back-end and front-end.
	 *
	 * @since 2.7.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return int The user ID. 0 if user is not found.
	 */
	public function get_user_id() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_user_id()', '5.0.0', 'tsf()->query()->get_current_user_id()' );

		return $tsf->query()->get_current_user_id();
	}

	/**
	 * Fetches Post content.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param \WP_Post|int|null $post The Post or Post ID. Leave null to get current post.
	 * @return string The post content.
	 */
	public function get_post_content( $post = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_post_content()', '5.0.0', 'tsf()->data()->post()->get_content()' );

		return $tsf->data()->post()->get_content( $post );
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
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $post_id The post ID to check.
	 * @return bool
	 */
	public function uses_non_html_page_builder( $post_id ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->uses_non_html_page_builder()', '5.0.0', 'tsf()->data()->post()->uses_non_html_page_builder()' );

		return $tsf->data()->post()->uses_non_html_page_builder( $post_id );
	}

	/**
	 * Determines if the current post is protected or private.
	 * Only works on singular pages.
	 *
	 * @since 2.8.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|null|\WP_Post $post The post ID or WP Post object.
	 * @return bool True if protected or private, false otherwise.
	 */
	public function is_protected( $post = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_protected()', '5.0.0', 'tsf()->data()->post()->is_protected()' );

		return $tsf->data()->post()->is_protected( $post );
	}

	/**
	 * Determines if the current post has a password.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|null|\WP_Post $post The post ID or WP Post object.
	 * @return bool True if protected, false otherwise.
	 */
	public function is_password_protected( $post = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_password_protected()', '5.0.0', 'tsf()->data()->post()->is_password_protected()' );

		return $tsf->data()->post()->is_password_protected( $post );
	}

	/**
	 * Determines if the current post is private.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|null|\WP_Post $post The post ID or WP Post object.
	 * @return bool True if private, false otherwise.
	 */
	public function is_private( $post = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_private()', '5.0.0', 'tsf()->data()->post()->is_private()' );

		return $tsf->data()->post()->is_private( $post );
	}

	/**
	 * Determines if the current post is a draft.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|null|\WP_Post $post The post ID or WP Post object.
	 * @return bool True if draft, false otherwise.
	 */
	public function is_draft( $post = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_draft()', '5.0.0', 'tsf()->data()->post()->is_draft()' );

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
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $item    The item to get.
	 * @param int    $post_id The post ID.
	 * @return mixed The post meta item's value. Null when item isn't registered.
	 */
	public function get_post_meta_item( $item, $post_id = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_post_meta_item()', '5.0.0', 'tsf()->data()->plugin()->post()->get_meta_item()' );

		return $tsf->data()->plugin()->post()->get_meta_item( $item, $post_id );
	}

	/**
	 * Returns all registered custom SEO fields for a post.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $post_id The post ID.
	 * @return array The post meta.
	 */
	public function get_post_meta( $post_id ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_post_meta()', '5.0.0', 'tsf()->data()->plugin()->post()->get_meta()' );

		return $tsf->data()->plugin()->post()->get_meta( $post_id );
	}

	/**
	 * Returns the post meta defaults.
	 *
	 * Unlike other post meta calls, no \WP_Post object is accepted as an input value,
	 * this is done for performance reasons, so we can cache here, instead of relying on
	 * WordPress's cache, where they cast many filters and redundantly sanitize the object.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $post_id The post ID.
	 * @return array The default post meta.
	 */
	public function get_post_meta_defaults( $post_id = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_post_meta_defaults()', '5.0.0', 'tsf()->data()->plugin()->post()->get_default_meta()' );

		return $tsf->data()->plugin()->post()->get_default_meta( $post_id );
	}

	/**
	 * Updates single post meta value.
	 *
	 * Note that this method can be more resource intensive than you intend it to be,
	 * as it reprocesses all post meta.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string           $item  The item to update.
	 * @param mixed            $value The value the item should be at.
	 * @param \WP_Post|integer $post  The post object or post ID.
	 */
	public function update_single_post_meta_item( $item, $value, $post ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->update_single_post_meta_item()', '5.0.0', 'tsf()->data()->plugin()->post()->update_single_post_meta_item()' );

		return $tsf->data()->plugin()->post()->update_single_post_meta_item( $item, $value, $post );
	}

	/**
	 * Save post meta / custom field data for a singular post type.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param \WP_Post|integer $post The post object or post ID.
	 * @param array            $data The post meta fields, will be merged with the defaults.
	 */
	public function save_post_meta( $post, $data ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->save_post_meta()', '5.0.0', 'tsf()->data()->plugin()->post()->save_meta()' );

		return $tsf->data()->plugin()->post()->save_meta( $post, $data );
	}

	/**
	 * Returns all post type archive meta.
	 *
	 * We do not test whether a post type is supported, for it'll conflict with data-fills on the
	 * SEO settings page. This meta should never get called on the front-end if the post type is
	 * disabled, anyway, for we never query post types externally, aside from the SEO settings page.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $post_type The post type.
	 * @return array The post type archive's meta item's values.
	 */
	public function get_post_type_archive_meta( $post_type ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_post_type_archive_meta()', '5.0.0', 'tsf()->data()->plugin()->pta()->get_meta()' );

		return $tsf->data()->plugin()->pta()->get_meta( $post_type );
	}

	/**
	 * Returns a single post type archive item's value.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $item      The item to get.
	 * @param string $post_type The post type.
	 * @return ?mixed The post type archive's meta item value. Null when item isn't registered.
	 */
	public function get_post_type_archive_meta_item( $item, $post_type = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_post_type_archive_meta_item()', '5.0.0', 'tsf()->data()->plugin()->pta()->get_meta_item()' );

		return $tsf->data()->plugin()->pta()->get_meta_item( $item, $post_type );
	}

	/**
	 * Returns an array of all public post type archive option defaults.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return array[] The Post Type Archive Metadata default options
	 *                 of all public Post Type archives.
	 */
	public function get_all_post_type_archive_meta_defaults() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_all_post_type_archive_meta_defaults()', '5.0.0', 'tsf()->data()->plugin()->pta()->get_all_default_meta()' );

		return $tsf->data()->plugin()->pta()->get_all_default_meta();
	}

	/**
	 * Returns an array of default post type archive meta.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $post_type The post type.
	 * @return array The Post Type Archive Metadata default options.
	 */
	public function get_post_type_archive_meta_defaults( $post_type = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_post_type_archive_meta_defaults()', '5.0.0', 'tsf()->data()->plugin()->pta()->get_default_meta()' );

		return $tsf->data()->plugin()->pta()->get_default_meta( $post_type );
	}

	/**
	 * Returns sitemap color scheme.
	 *
	 * @since 2.8.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param bool $get_defaults Whether to get the default colors.
	 * @return array The sitemap colors.
	 */
	public function get_sitemap_colors( $get_defaults = false ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_sitemap_colors()', '5.0.0', 'tsf()->sitemap()->utils()->get_sitemap_colors()' );

		return $tsf->sitemap()->utils()->get_sitemap_colors( $get_defaults );
	}

	/**
	 * Checks if blog is public through WordPress core settings.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool True is blog is public.
	 */
	public function is_blog_public() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_blog_public()', '5.0.0', 'tsf()->data()->blog()->is_public()' );

		return $tsf->data()->blog()->is_public();
	}

	/**
	 * Whether the current blog is spam or deleted.
	 * Multisite only.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool Current blog is spam.
	 */
	public function current_blog_is_spam_or_deleted() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->current_blog_is_spam_or_deleted()', '5.0.0', 'tsf()->data()->blog()->is_spam_or_deleted()' );

		return $tsf->data()->blog()->is_spam_or_deleted();
	}

	/**
	 * Determines if the current installation is on a subdirectory.
	 *
	 * @since 2.9.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_subdirectory_installation() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_subdirectory_installation()', '5.0.0', 'tsf()->data()->blog()->is_subdirectory_installation()' );

		return $tsf->data()->blog()->is_subdirectory_installation();
	}

	/**
	 * Tells whether WP 5.5 Core Sitemaps are used.
	 *
	 * @since 4.1.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function use_core_sitemaps() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->use_core_sitemaps()', '5.0.0', 'tsf()->sitemap()->utils()->use_core_sitemaps()' );

		return $tsf->sitemap()->utils()->use_core_sitemaps();
	}

	/**
	 * Determines whether we can output sitemap or not based on options and blog status.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function can_run_sitemap() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->can_run_sitemap()', '5.0.0', 'tsf()->sitemap()->utils()->may_output_optimized_sitemap()' );

		return $tsf->sitemap()->utils()->may_output_optimized_sitemap();
	}

	/**
	 * Detects presence of robots.txt in root folder.
	 *
	 * @since 2.5.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool Whether the robots.txt file exists.
	 */
	public function has_robots_txt() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->has_robots_txt()', '5.0.0', 'tsf()->robotstxt()->utils()->has_root_robots_txt()' );

		return $tsf->robotstxt()->utils()->has_root_robots_txt();
	}

	/**
	 * Returns the robots.txt location URL.
	 * Only allows root domains.
	 *
	 * @since 2.9.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string URL location of robots.txt. Unescaped.
	 */
	public function get_robots_txt_url() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_robots_txt_url()', '5.0.0', 'tsf()->robotstxt()->utils()->get_robots_txt_url()' );

		return $tsf->robotstxt()->utils()->get_robots_txt_url();
	}

	/**
	 * Detects presence of sitemap.xml in root folder.
	 *
	 * @since 2.5.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool Whether the sitemap.xml file exists.
	 */
	public function has_sitemap_xml() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->has_sitemap_xml()', '5.0.0', 'tsf()->sitemap()->utils()->has_root_sitemap_xml()' );

		return $tsf->sitemap()->utils()->has_root_sitemap_xml();
	}

	/**
	 * Edits the robots.txt output.
	 * Requires the site not to have a robots.txt file in the root directory.
	 *
	 * @since 2.2.9
	 * @since 5.0.0 Deprecated. Even though access was marked private, we still found some used this (including us).
	 * @deprecated
	 *
	 * @return string Robots.txt output.
	 */
	public function robots_txt() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->robots_txt()', '5.0.0', 'tsf()->robotstxt()->get_robots_txt' );

		return $tsf->robotstxt()->get_robots_txt();
	}

	/**
	 * Deletes excluded post IDs cache.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool True on success, false on failure.
	 */
	public function delete_excluded_ids_cache() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->delete_excluded_ids_cache()', '5.0.0', 'tsf()->query()->exclusion()->clear_excluded_post_ids_cache()' );

		return $tsf->query()->exclusion()->clear_excluded_post_ids_cache();
	}

	/**
	 * Builds and returns the excluded post IDs.
	 *
	 * Memoizes the database request.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return array : { 'archive', 'search' }
	 */
	public function get_excluded_ids_from_cache() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_excluded_ids_from_cache()', '5.0.0', 'tsf()->query()->exclusion()->get_excluded_ids_from_cache()' );

		return $tsf->query()->exclusion()->get_excluded_ids_from_cache();
	}

	/**
	 * Destroys output buffer, if any. To be used with AJAX and XML to clear any PHP errors or dumps.
	 *
	 * @since 2.8.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool True on clear. False otherwise.
	 */
	public function clean_response_header() {
		\tsf()->_deprecated_function( 'tsf()->clean_response_header()', '5.0.0' );
		return \The_SEO_Framework\Helper\Headers::clean_response_header();
	}

	/**
	 * Registers admin scripts and styles.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 */
	public function init_admin_scripts() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->init_admin_scripts()', '5.0.0', 'tsf()->load_admin_scripts()' );

		return $tsf->load_admin_scripts();
	}

	/**
	 * Returns the SEO Bar.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array $query : {
	 *   int    $id        : Required. The current post or term ID.
	 *   string $taxonomy  : Optional. If not set, this will interpret it as a post.
	 *   string $post_type : Optional. If not set, this will be automatically filled.
	 *                                 This parameter is ignored for taxonomies.
	 * }
	 * @return string The generated SEO bar, in HTML.
	 */
	public function get_generated_seo_bar( $query ) {
		\tsf()->_deprecated_function( 'tsf()->get_generated_seo_bar()', '5.0.0' );
		return \The_SEO_Framework\Admin\SEOBar\Builder::generate_bar( $query );
	}

	/**
	 * Redirects vistor to input $url.
	 *
	 * @since 2.9.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $url The redirection URL.
	 */
	public function do_redirect( $url = '' ) {
		\tsf()->_deprecated_function( 'tsf()->do_redirect()', '5.0.0', 'wp_safe_redirect()' );
		return \The_SEO_Framework\Front\Redirect::do_redirect( $url );
	}

	/**
	 * Whether to allow external redirect through the 301 redirect option.
	 * Memoizes the return value.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool Whether external redirect is allowed.
	 */
	public function allow_external_redirect() {
		\tsf()->_deprecated_function( 'tsf()->allow_external_redirect()', '5.0.0' );
		return \The_SEO_Framework\Helper\Redirect::allow_external_redirect();
	}

	/**
	 * Returns the document title.
	 *
	 * This method serves as a callback for filter `pre_get_document_title`.
	 * Use tsf()->get_title() instead.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 1. Now escapes the filter output.
	 *              2. Deprecated.
	 * @deprecated
	 *
	 * @param string $title The filterable title.
	 * @return string The document title
	 */
	public function get_document_title( $title = '' ) {
		\tsf()->_deprecated_function( 'tsf()->get_document_title()', '5.0.0' );
		return \The_SEO_Framework\Front\Title::set_document_title( $title );
	}

	/**
	 * Returns the document title.
	 *
	 * This method serves as a callback for filter `wp_title`.
	 * Use tsf()->get_title() instead.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 1. Now escapes the filter output.
	 *              2. Deprecated.
	 * @deprecated
	 *
	 * @param string $title The filterable title.
	 * @return string $title
	 */
	public function get_wp_title( $title = '' ) {
		\tsf()->_deprecated_function( 'tsf()->get_wp_title()', '5.0.0' );
		return \The_SEO_Framework\Front\Title::set_document_title( $title );
	}

	/**
	 * Returns the SEO Settings page URL.
	 *
	 * @since 4.1.4
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The escaped SEO Settings page URL.
	 */
	public function get_seo_settings_page_url() {

		\tsf()->_deprecated_function( 'tsf()->get_seo_settings_page_url()', '5.0.0' );

		return \The_SEO_Framework\is_headless( 'settings' )
			? ''
			: \menu_page_url( \THE_SEO_FRAMEWORK_SITE_OPTIONS_SLUG, false ); // menu_page_url escapes.
	}

	/**
	 * Converts markdown text into HTML.
	 * Does not support list or block elements. Only inline statements.
	 *
	 * @since 2.8.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 * @link https://wordpress.org/plugins/about/readme.txt
	 *
	 * @param string $text    The text that might contain markdown. Expected to be escaped.
	 * @param array  $convert The markdown style types wished to be converted.
	 *                        If left empty, it will convert all.
	 * @param array  $args    The function arguments.
	 * @return string The markdown converted text.
	 */
	public function convert_markdown( $text, $convert = [], $args = [] ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->convert_markdown()', '5.0.0', 'tsf()->format()->markdown()->convert()' );

		return $tsf->format()->markdown()->convert( $text, $convert, $args );
	}

	/**
	 * Converts time from GMT input to given format.
	 *
	 * @since 2.7.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $format The datetime format.
	 * @param string $time The GMT time. Expects the time zone to be omitted.
	 * @return string The converted time. Empty string if no $time is given.
	 */
	public function gmt2date( $format = 'Y-m-d', $time = '' ) {
		\tsf()->_deprecated_function( 'tsf()->gmt2date()', '5.0.0', 'gmdate()' );
		return gmdate( $format, strtotime( "$time GMT" ) );
	}

	/**
	 * Returns timestamp format based on timestamp settings.
	 * Note that this must be XML safe.
	 *
	 * @since 3.0.0
	 * @since 4.1.4 1. Added options-override parameter.
	 *              2. Added return value filter.
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param null|bool $override_get_time Whether to override the $get_time from option value.
	 * @return string The timestamp format used in PHP date.
	 */
	public function get_timestamp_format( $override_get_time = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_timestamp_format()', '5.0.0', 'tsf()->format()->time()->get_preferred_format()' );

		if ( isset( $override_get_time ) && '1' !== $override_get_time ) {
			$get_time = $override_get_time;
			/**
			 * @since 4.1.4
			 * @param string The full timestamp format. Must be XML safe and in ISO 8601 datetime notation.
			 * @param bool   True if time is requested, false if only date.
			 */
			return \apply_filters(
				'the_seo_framework_timestamp_format',
				$get_time ? 'Y-m-d\TH:iP' : 'Y-m-d',
				$get_time,
			);
		}

		return $tsf->format()->time()->get_preferred_format();
	}

	/**
	 * Determines if time is used in the timestamp format.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool True if time is used. False otherwise.
	 */
	public function uses_time_in_timestamp_format() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->uses_time_in_timestamp_format()', '5.0.0', "tsf()->get_option( 'timestamp_format' )" );

		return '1' === $tsf->get_option( 'timestamp_format' );
	}

	/**
	 * Shortens string and adds ellipses when over a threshold in length.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $string The string to test and maybe trim
	 * @param int    $over   The character limit. Must be over 0 to have effect.
	 *                       Bug: If 1 is given, the returned string length will be 3.
	 *                       Bug: If 2 is given, the returned string will only consist of the hellip.
	 * @return string
	 */
	public function hellip_if_over( $string, $over = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->hellip_if_over()', '5.0.0', 'tsf()->format()->strings()->hellip_if_over()' );

		return $tsf->format()->strings()->hellip_if_over( $string, $over );
	}

	/**
	 * Counts words encounters from input string.
	 * Case insensitive. Returns first encounter of each word if found multiple times.
	 *
	 * Will only return words that are above set input thresholds.
	 *
	 * @since 2.7.0
	 * @since 3.1.0 This method now uses PHP 5.4+ encoding, capable of UTF-8 interpreting,
	 *              instead of relying on PHP's incomplete encoding table.
	 *              This does mean that the functionality is crippled when the PHP
	 *              installation isn't unicode compatible; this is unlikely.
	 * @since 4.0.0 1. Now expects PCRE UTF-8 encoding support.
	 *              2. Moved input-parameter alterting filters outside of this function.
	 *              3. Short length now works as intended, instead of comparing as less, it compares as less or equal to.
	 * @since 4.2.0 Now supports detection of connector-dashes, connector-punctuation, and closing quotes,
	 *              and recognizes those as whole words.
	 * @since 5.0.0 1. Now converts input string as UTF-8. This mainly solves issues with attached quotes (d'anglais).
	 *              2. Deprecated.
	 * @deprecated
	 *
	 * @param string $string Required. The string to count words in.
	 * @param int    $dupe_count       Minimum amount of words to encounter in the string.
	 *                                 Set to 0 to count all words longer than $short_length.
	 * @param int    $dupe_short       Minimum amount of words to encounter in the string that fall under the
	 *                                 $short_length. Set to 0 to consider all words with $amount.
	 * @param int    $short_length     The maximum string length of a word to pass for $dupe_short
	 *                                 instead of $count. Set to 0 to ignore $count, and use $dupe_short only.
	 * @return array Containing arrays of words with their count.
	 */
	public function get_word_count( $string, $dupe_count = 3, $dupe_short = 5, $short_length = 3 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_word_count()', '5.0.0', 'tsf()->format()->strings()->get_word_count()' );

		return $tsf->format()->strings()->get_word_count(
			$string,
			[
				'filter_under'       => $dupe_count,
				'filter_short_under' => $dupe_short,
				'short_word_length'  => $short_length,
			],
		);
	}

	/**
	 * Returns the title and description input guideline table, for
	 * (Google) search, Open Graph, and Twitter.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param ?string $locale The locale to test. If empty, it will be auto-determined.
	 * @return array
	 */
	public function get_input_guidelines( $locale = null ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_input_guidelines()', '5.0.0', 'tsf()->guidelines()->get_text_size_guidelines()' );

		return $tsf->guidelines()->get_text_size_guidelines( $locale );
	}

	/**
	 * Returns the title and description input guideline explanatory table.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 Now added a short leading-dot version for ARIA labels.
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return array
	 */
	public function get_input_guidelines_i18n() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_input_guidelines_i18n()', '5.0.0', 'tsf()->guidelines()->get_text_size_guidelines_i18n()' );

		return $tsf->guidelines()->get_input_guidelines_i18n();
	}

	/**
	 * Outputs reference description HTML elements for JavaScript for a specific ID.
	 *
	 * @since 4.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $id The input ID.
	 * @param array  $data The input data.
	 */
	public function output_js_title_data( $id, $data ) {
		\tsf()->_deprecated_function( 'tsf()->output_js_title_data()', '5.0.0' );
		\The_SEO_Framework\Admin\Settings\Layout\Input::output_js_title_data( $id, $data );
	}

	/**
	 * Outputs reference social HTML elements for JavaScript for a specific ID.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string       $group    The social input group ID.
	 * @param array[og,tw] $settings The input settings data.
	 */
	public function output_js_social_data( $group, $settings ) {
		\tsf()->_deprecated_function( 'tsf()->output_js_social_data()', '5.0.0' );
		\The_SEO_Framework\Admin\Settings\Layout\Input::output_js_social_data( $group, $settings );
	}

	/**
	 * Outputs reference description HTML elements for JavaScript for a specific ID.
	 *
	 * @since 4.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $id   The description input ID.
	 * @param array  $data The input data.
	 */
	public function output_js_description_data( $id, $data ) {
		\tsf()->_deprecated_function( 'tsf()->output_js_description_data()', '5.0.0' );
		\The_SEO_Framework\Admin\Settings\Layout\Input::output_js_social_data( $id, $data );
	}

	/**
	 * Adds menu links under "settings" in the wp-admin dashboard
	 *
	 * @since 2.2.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 */
	public function add_menu_link() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->add_menu_link()', '5.0.0', 'tsf()->admin()->menu()->register_top_menu_page()' );

		$tsf->admin()->menu()->register_top_menu_page();
	}

	/**
	 * Returns the number of issues registered.
	 * Always returns 0 when the settings are headless.
	 *
	 * @since 4.2.8
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return int The registered issue count.
	 */
	public function get_admin_issue_count() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_admin_issue_count()', '5.0.0', 'tsf()->admin()->menu()->get_top_menu_issue_count()' );

		return $tsf->admin()->menu()->get_top_menu_issue_count();
	}

	/**
	 * Returns formatted text for the notice count to be displayed in the admin menu as a number.
	 *
	 * @since 4.2.8
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $issue_count The issue count.
	 * @return string The issue count badge.
	 */
	public function get_admin_menu_issue_badge( $issue_count ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_admin_menu_issue_badge()', '5.0.0', 'tsf()->admin()->menu()->get_issue_badge()' );

		return $tsf->admin()->menu()->get_issue_badge( $issue_count );
	}

	/**
	 * Redirect the user to an admin page, and add query args to the URL string
	 * for alerts, etc.
	 *
	 * @since 2.2.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $page Menu slug. This slug must exist, or the redirect will loop back to the current page.
	 * @param array  $query_args Optional. Associative array of query string arguments
	 *               (key => value). Default is an empty array.
	 */
	public function admin_redirect( $page, $query_args = [] ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->admin_redirect()', '5.0.0', 'tsf()->admin()->utils()->redirect()' );

		$tsf->admin()->utils()->redirect( $page, $query_args );
	}

	/**
	 * Registers dismissible persistent notice, that'll respawn during page load until dismissed or otherwise expired.
	 *
	 * @since 4.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $message    The notice message. Expected to be escaped if $escape is false.
	 *                           When the message contains HTML, it must start with a <p> tag,
	 *                           or it will be added for you--regardless of proper semantics.
	 * @param string $key        The notice key. Must be unique--prevents double-registering of the notice, and allows for
	 *                           deregistering of the notice.
	 * @param array  $args       : {
	 *    'type'   => string Optional. The notification type. Default 'updated'.
	 *    'icon'   => bool   Optional. Whether to enable icon. Default true.
	 *    'escape' => bool   Optional. Whether to escape the $message. Default true.
	 * }
	 * @param array  $conditions : {
	 *     'capability'   => string Required. The user capability required for the notice to display. Defaults to settings capability.
	 *     'screens'      => array  Optional. The screen bases the notice may be displayed on. When left empty, it'll output on any page.
	 *     'excl_screens' => array  Optional. The screen bases the notice may NOT be displayed on. When left empty, only `screens` applies.
	 *     'user'         => int    Optional. The user ID to display the notice for. Capability will not be ignored.
	 *     'count'        => int    Optional. The number of times the persistent notice may appear (for everyone allowed to see it).
	 *                              Set to -1 for unlimited. When -1, the notice must be removed from display manually.
	 *     'timeout'      => int    Optional. The number of seconds the notice should remain valid for display. Set to -1 to disable check.
	 *                              When the timeout is below -1, then the notification will not be outputted.
	 *                              Do not input non-integer values (such as `false`), for those might cause adverse events.
	 * }
	 */
	public function register_dismissible_persistent_notice( $message, $key, $args = [], $conditions = [] ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->register_dismissible_persistent_notice()', '5.0.0', 'tsf()->admin()->notice()->persistent()->register_notice()' );

		$tsf->admin()->notice()->persistent()->register_notice( $message, $key, $args, $conditions );
	}

	/**
	 * Lowers the persistent notice display count.
	 * When the threshold is reached, the notice is deleted.
	 *
	 * @since 4.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $key   The notice key.
	 * @param int    $count The number of counts the notice has left. Passed by reference.
	 *                      When -1 (permanent notice), nothing happens.
	 */
	public function count_down_persistent_notice( $key, &$count ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->count_down_persistent_notice()', '5.0.0', 'tsf()->admin()->notice()->persistent()->count_down_notice()' );

		$tsf->admin()->notice()->persistent()->count_down_notice( $key, $count );

		// No longer passes $count by reference. Simulate what had happened:
		if ( $count > 0 )
			--$count;
	}

	/**
	 * Clears a persistent notice by key.
	 *
	 * @since 4.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $key The notice key.
	 * @return bool True on success, false on failure.
	 */
	public function clear_persistent_notice( $key ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->clear_persistent_notice()', '5.0.0', 'tsf()->admin()->notice()->persistent()->clear_notice()' );

		return $tsf->admin()->notice()->persistent()->clear_notice( $key );
	}

	/**
	 * Clears all registered persistent notices. Useful after upgrade.
	 *
	 * @since 4.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool True on success, false on failure.
	 */
	public function clear_all_persistent_notices() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->clear_all_persistent_notices()', '5.0.0', 'tsf()->admin()->notice()->persistent()->clear_all_notices()' );

		return $tsf->admin()->notice()->persistent()->clear_all_notices();
	}

	/**
	 * Generates dismissible notice.
	 * Also loads scripts and styles if out of The SEO Framework's context.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $message The notice message. Expected to be escaped if $escape is false.
	 *                        When the message contains HTML, it must start with a <p> tag,
	 *                        or it will be added for you--regardless of proper semantics.
	 * @param string $type   The notice type : 'updated', 'error', 'warning', 'info'. Expected to be escaped.
	 * @param bool   $icon   Whether to add an accessibility icon.
	 * @param bool   $escape Whether to escape the whole output.
	 * @param bool   $inline Whether WordPress should be allowed to move it.
	 * @return string The dismissible error notice.
	 */
	public function generate_dismissible_notice( $message = '', $type = 'updated', $icon = true, $escape = true, $inline = false ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->generate_dismissible_notice()', '5.0.0', 'tsf()->admin()->notice()->generate_notice()' );

		return $tsf->admin()->notice()->generate_notice(
			$message,
			[
				'type'   => $type,
				'icon'   => $icon,
				'escape' => $escape,
				'inline' => $inline,
			],
		);
	}

	/**
	 * Echos generated dismissible notice.
	 *
	 * @since 2.7.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $message The notice message. Expected to be escaped if $escape is false.
	 * @param string $type    The notice type : 'updated', 'error', 'warning', 'info'. Expected to be escaped.
	 * @param bool   $icon    Whether to add an accessibility icon.
	 * @param bool   $escape  Whether to escape the whole output.
	 * @param bool   $inline Whether WordPress should be allowed to move it.
	 */
	public function do_dismissible_notice( $message = '', $type = 'updated', $icon = true, $escape = true, $inline = false ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->do_dismissible_notice()', '5.0.0', 'tsf()->admin()->notice()->output_notice()' );

		return $tsf->admin()->notice()->output_notice(
			$message,
			[
				'type'   => $type,
				'icon'   => $icon,
				'escape' => $escape,
				'inline' => $inline,
			],
		);
	}

	/**
	 * Register the database settings for storage.
	 *
	 * @since 2.2.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return void Early if settings can't be registered.
	 */
	public function register_settings() {
		\tsf()->_deprecated_function( 'tsf()->register_settings()', '5.0.0' );
		\The_SEO_Framework\Data\Admin\Plugin::register_settings();
	}

	/**
	 * Updates the database version to the defined one.
	 *
	 * This prevents errors when users go back to an earlier version, where options
	 * might be different from a future (or past, since v4.1.0) one.
	 *
	 * @since 3.0.6
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 */
	public function update_db_version() {
		\tsf()->_deprecated_function( 'tsf()->update_db_version()', '5.0.0' );
		\update_option( 'the_seo_framework_upgraded_db_version', THE_SEO_FRAMEWORK_DB_VERSION, true );
	}

	/**
	 * Registers each of the settings with a sanitization filter type.
	 *
	 * @since 2.8.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 */
	public function init_sanitizer_filters() {
		\tsf()->_deprecated_function( 'tsf()->init_sanitizer_filters()', '5.0.0' );
		\The_SEO_Framework\Data\Filter\Plugin::register_sanitizers_jit();
	}

	/**
	 * Add sanitization filters to options.
	 *
	 * Associates a sanitization filter to each option (or sub options if they
	 * exist) before adding a reference to run the option through that
	 * sanitizer at the right time.
	 *
	 * @since 2.2.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return boolean Returns true when complete
	 */
	public function add_option_filter() {
		\tsf()->_deprecated_function( 'tsf()->add_option_filter()', '5.0.0', 'filter the_seo_framework_settings_update_sanitizers' );
		return false;
	}

	/**
	 * Calculates the relative font color according to the background, grayscale.
	 *
	 * @since 2.8.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $hex The 3 to 6+ character RGB hex. The '#' prefix may be added.
	 *                    RGBA/RRGGBBAA is supported, but the Alpha channels won't be returned.
	 * @return string The hexadecimal RGB relative font color, without '#' prefix.
	 */
	public function get_relative_fontcolor( $hex = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_relative_fontcolor()', '5.0.0', 'tsf()->format()->color()->get_relative_fontcolor()' );

		return $tsf->format()->color()->get_relative_fontcolor( $hex );
	}

	/**
	 * Returns list of active plugins.
	 * Memoizes the return value.
	 *
	 * @since 2.6.1
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return array List of active plugins.
	 */
	public function active_plugins() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->active_plugins()', '5.0.0', 'tsf()->data()->blog()->get_active_plugins()' );

		return $tsf->data()->blog()->get_active_plugins();
	}

	/**
	 * Filterable list of conflicting plugins.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return array List of conflicting plugins.
	 */
	public function conflicting_plugins() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->conflicting_plugins()', '5.0.0' );

		return \The_SEO_Framework\Helper\Compatibility::get_conflicting_plugins();
	}

	/**
	 * Fetches type of conflicting plugins.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $type The Key from $this->conflicting_plugins()
	 * @return array
	 */
	public function get_conflicting_plugins( $type = 'seo_tools' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_conflicting_plugins()', '5.0.0' );

		return \The_SEO_Framework\Helper\Compatibility::get_conflicting_plugins()[ $type ] ?? [];
	}

	/**
	 * Determines if other SEO plugins are active.
	 * Memoizes the return value.
	 *
	 * @since 1.3.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool SEO plugin detected.
	 */
	public function detect_seo_plugins() {
		\tsf()->_deprecated_function( 'tsf()->detect_seo_plugins()', '5.0.0' );
		return \The_SEO_Framework\Helper\Compatibility::get_active_conflicting_plugin_types()['seo_tools'];
	}

	/**
	 * Determines if other Open Graph or SEO plugins are active.
	 * Memoizes the return value.
	 *
	 * @since 1.3.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool True if OG or SEO plugin detected.
	 */
	public function detect_og_plugin() {
		\tsf()->_deprecated_function( 'tsf()->detect_og_plugin()', '5.0.0' );
		return \The_SEO_Framework\Helper\Compatibility::get_active_conflicting_plugin_types()['open_graph'];
	}

	/**
	 * Determines if other Twitter Card plugins are active.
	 * Memoizes the return value.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool Twitter Card plugin detected.
	 */
	public function detect_twitter_card_plugin() {
		\tsf()->_deprecated_function( 'tsf()->detect_twitter_card_plugin()', '5.0.0' );
		return \The_SEO_Framework\Helper\Compatibility::get_active_conflicting_plugin_types()['twitter_card'];
	}

	/**
	 * Determines if other Schema.org LD+Json plugins are active.
	 *
	 * @since 1.3.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool Whether another Schema.org plugin is active.
	 */
	public function has_json_ld_plugin() {
		\tsf()->_deprecated_function( 'tsf()->has_json_ld_plugin()', '5.0.0' );
		return \The_SEO_Framework\Helper\Compatibility::get_active_conflicting_plugin_types()['schema'];
	}

	/**
	 * Determines if other Sitemap plugins are active.
	 * Memoizes the return value.
	 *
	 * @since 2.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function detect_sitemap_plugin() {
		\tsf()->_deprecated_function( 'tsf()->detect_sitemap_plugin()', '5.0.0' );
		return \The_SEO_Framework\Helper\Compatibility::get_active_conflicting_plugin_types()['sitemaps'];
	}

	/**
	 * Makes Email Addresses safe, via sanitize_email()
	 *
	 * @since 2.2.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $email A possibly unsafe email.
	 * @return string String a safe email address
	 */
	public function s_email_address( $email ) {
		\tsf()->_deprecated_function( 'tsf()->s_email_address()', '5.0.0', 'sanitize_email()' );
		return \sanitize_email( $email );
	}

	/**
	 * Removes unsafe HTML tags, via wp_kses_post().
	 *
	 * @since 2.2.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $text String with potentially unsafe HTML in it.
	 * @return string String with only safe HTML in it
	 */
	public function s_safe_html( $text ) {
		\tsf()->_deprecated_function( 'tsf()->s_safe_html()', '5.0.0', 'wp_kses_post()' );
		return \wp_kses_post( $text );
	}
	/**
	 * Removes HTML tags from string.
	 *
	 * @since 2.2.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $text String, possibly with HTML in it.
	 * @return string String without HTML in it.
	 */
	public function s_no_html( $text ) {
		\tsf()->_deprecated_function( 'tsf()->s_no_html()', '5.0.0', 'strip_tags()' );
		// phpcs:ignore, WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- This is simple and performant sanity.
		return strip_tags( $text );
	}

	/**
	 * Removes HTML tags and line breaks from string.
	 * Also removes all spaces.
	 *
	 * @since 2.5.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $text String, possibly with HTML and spaces in it.
	 * @return string String without HTML and breaks in it.
	 */
	public function s_no_html_space( $text ) {
		\tsf()->_deprecated_function( 'tsf()->s_no_html_space()', '5.0.0' );
		// phpcs:ignore, WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- This is simple and performant sanity.
		return str_replace( ' ', '', strip_tags( $text ) );
	}

	/**
	 * Makes URLs safe, maintaining queries.
	 *
	 * @since 2.2.8
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $url A possibly unsafe URL.
	 * @return string String a safe URL with Query Arguments.
	 */
	public function s_url_query( $url ) {
		\tsf()->_deprecated_function( 'tsf()->s_url_query()', '5.0.0', 'sanitize_url()' );
		return \sanitize_url( $url );
	}

	/**
	 * Makes URLs safe and removes query args.
	 *
	 * @since 2.2.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $url A possibly unsafe URL.
	 * @return string String a safe URL without Query Arguments.
	 */
	public function s_url( $url ) {
		\tsf()->_deprecated_function( 'tsf()->s_url()', '5.0.0' );
		/**
		 * If queries have been tokenized, take the value before the query args.
		 * Otherwise it's empty, so take the current value.
		 */
		return \sanitize_url( strtok( $url, '?' ) ?: $url );
	}

	/**
	 * Cleans canonical URL.
	 * Looks at permalink settings to determine roughness of escaping.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $url A fully qualified URL.
	 * @return string A fully qualified clean URL.
	 */
	public function clean_canonical_url( $url ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->clean_canonical_url()', '5.0.0', 'esc_url()' );

		if ( $tsf->query()->utils()->using_pretty_permalinks() )
			return \esc_url( $url, [ 'https', 'http' ] );

		// Keep the &'s more readable when using query-parameters.
		return \sanitize_url( $url, [ 'https', 'http' ] );
	}

	/**
	 * Sanitizeses ID. Mainly removing spaces and coding characters.
	 *
	 * Unlike sanitize_key(), it doesn't alter the case nor applies filters.
	 * It also maintains the '@' character and square brackets.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $id The unsanitized ID.
	 * @return string The sanitized ID.
	 */
	public function s_field_id( $id ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->s_field_id()', '5.0.0', 'tsf()->escape()->option_name_attribute()' );

		return $tsf->escape()->option_name_attribute( $id );
	}

	/**
	 * Returns an one-line sanitized description and escapes it.
	 *
	 * @since 2.5.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $description The Description.
	 * @return string One line sanitized description.
	 */
	public function s_description( $description ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->s_description()', '5.0.0' );

		return \esc_html(
			$tsf->sanitize()->metadata_content( $description )
		);
	}

	/**
	 * Escapes and beautifies description.
	 *
	 * @since 2.5.2
	 * @since 5.0.0 1. The first parameter is now required.
	 *              2. Deprecated.
	 * @deprecated
	 *
	 * @param string $description The description to escape and beautify.
	 * @return string Escaped and beautified description.
	 */
	public function escape_description( $description ) {

		\tsf()->_deprecated_function( 'tsf()->escape_description()', '5.0.0', 'esc_html()' );

		return trim(
			\esc_html(
				\convert_chars(
					\wptexturize(
						\capital_P_dangit( $description )
					)
				)
			)
		);
	}

	/**
	 * Returns a sanitized and trimmed title.
	 *
	 * @since 2.5.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $title The input Title.
	 * @return string Sanitized and trimmed title.
	 */
	public function s_title( $title ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->s_title()', '5.0.0' );

		return \esc_html(
			$tsf->sanitize()->metadata_content( $title )
		);
	}

	/**
	 * Escapes and beautifies title.
	 *
	 * @since 2.5.2
	 * @since 5.0.0 1. The first parameter is now required.
	 *              2. Deprecated.
	 * @deprecated
	 *
	 * @param string $title The title to escape and beautify.
	 * @param bool   $trim  Whether to trim the title from whitespaces.
	 * @return string Escaped and beautified title.
	 */
	public function escape_title( $title, $trim = true ) {

		\tsf()->_deprecated_function( 'tsf()->escape_title()', '5.0.0', 'esc_html()' );

		$title = \esc_html(
			\convert_chars(
				\wptexturize(
					\capital_P_dangit( $title )
				)
			)
		);

		return $trim ? trim( $title ) : $title;
	}

	/**
	 * Escapes attributes after converting `&` to `&amp;` to prevent double-escaping
	 * of entities in HTML input value attributes.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $text String with possibly ampersands.
	 * @return string
	 */
	public function esc_attr_preserve_amp( $text ) {

		\tsf()->_deprecated_function( 'tsf()->esc_attr_preserve_amp()', '5.0.0', 'esc_attr()' );

		return \esc_attr( str_replace( '&', '&amp;', $text ) );
	}

	/**
	 * Strips all URLs that are placed on new lines. These are prone to be embeds.
	 *
	 * This might leave stray line feeds. Use `tsf()->s_singleline()` to fix that.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $content The content to look for embed.
	 * @return string $content Content without single-lined URLs.
	 */
	public function strip_newline_urls( $content ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->strip_newline_urls()', '5.0.0', 'tsf()->format()->html()->strip_newline_urls()' );

		return $tsf->format()->html()->strip_newline_urls( $content );
	}

	/**
	 * Strips all URLs that are placed in paragraphs on their own. These are prone to be embeds.
	 *
	 * This might leave stray line feeds. Use `tsf()->s_singleline()` to fix that.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $content The content to look for embed.
	 * @return string $content Content without the paragraphs containing solely URLs.
	 */
	public function strip_paragraph_urls( $content ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->strip_paragraph_urls()', '5.0.0', 'tsf()->format()->html()->strip_paragraph_urls()' );

		return $tsf->format()->html()->strip_paragraph_urls( $content );
	}

	/**
	 * Strips tags with HTML Context-Sensitivity and outputs its breakdown.
	 *
	 * It essentially strips all tags, and replaces block-type tags' endings with spaces.
	 * When done, it performs a sanity-cleanup via `strip_tags()`.
	 *
	 * @since 3.2.4
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $input The input text that needs its tags stripped.
	 * @param array  $args  The input arguments. Tags not included are ignored. {
	 *                         'space'   : @param ?string[] HTML elements that should be processed for spacing. If the space
	 *                                                      element is of void element type, it'll be treated as 'clear'.
	 *                                                      If not set or null, skip check.
	 *                                                      If empty array, skips stripping; otherwise, use input.
	 *                         'clear'   : @param ?string[] HTML elements that should be emptied and replaced with a space.
	 *                                                      If not set or null, skip check.
	 *                                                      If empty array, skips stripping; otherwise, use input.
	 *                         'strip'   : @param bool      If set, strip_tags() is performed before returning the output.
	 *                                                      Recommended always true, since Regex doesn't understand XML.
	 *                         'passes'  : @param int       The maximum number of passes 'space' may conduct. More is slower,
	 *                                                      but more accurate.
	 *                      }
	 *                      NOTE: WARNING The array values are forwarded to a regex without sanitization/quoting.
	 *                      NOTE: Unlisted, script, and style tags will be stripped via PHP's `strip_tags()`. (togglable via `$args['strip']`)
	 *                            Also note that their contents are maintained as-is, without added spaces.
	 *                            It is why you should always list `style` and `script` in the `clear` array, never in 'space'.
	 * @return string The output string without tags. May have many stray and repeated spaces.
	 *                NOT SECURE for display! Don't trust this method. Always use esc_* functionality.
	 */
	public function strip_tags_cs( $input, $args = [] ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->strip_tags_cs()', '5.0.0', 'tsf()->format()->html()->strip_tags_cs()' );

		return $tsf->format()->html()->strip_tags_cs( $input, $args );
	}

	/**
	 * Sanitizes input excerpt.
	 *
	 * @since 2.8.0
	 * @since 5.0.0 1. The first parameter is now required.
	 *              2. Now returns an empty string when something falsesque is returned.
	 *              3. Deprecated.
	 * @deprecated
	 *
	 * @param string $excerpt          The excerpt.
	 * @param bool   $allow_shortcodes Whether to maintain shortcodes from excerpt.
	 * @param bool   $escape           Whether to escape the excerpt.
	 * @return string The escaped Excerpt.
	 */
	public function s_excerpt( $excerpt, $allow_shortcodes = true, $escape = true ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->s_excerpt()', '5.0.0', 'tsf()->format()->html()->extract_content()' );

		$excerpt = $tsf->format()->html()->extract_content(
			$excerpt,
			[ 'allow_shortcodes' => $allow_shortcodes ],
		);

		if ( $escape )
			\esc_html( \convert_chars( \wptexturize( $excerpt ) ) );

		return $excerpt;
	}

	/**
	 * Cleans input excerpt. Does NOT escape excerpt for output.
	 *
	 * @since 2.8.2
	 * @since 5.0.0 1. The first parameter is now required.
	 *              2. Deprecated.
	 * @deprecated
	 *
	 * @param string $excerpt          The excerpt.
	 * @param bool   $allow_shortcodes Whether to maintain shortcodes from excerpt.
	 * @return string The unescaped Excerpt.
	 */
	public function s_excerpt_raw( $excerpt, $allow_shortcodes = true ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->s_excerpt_raw()', '5.0.0', 'tsf()->format()->html()->extract_content()' );

		return $tsf->format()->html()->extract_content(
			$excerpt,
			[ 'allow_shortcodes' => $allow_shortcodes ],
		);
	}

	/**
	 * Returns an single-line, trimmed description without dupliacated spaces, nbsp, or tabs.
	 * Does NOT escape.
	 * Also converts back-solidi to their respective HTML entities for non-destructive handling.
	 *
	 * @since 2.8.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated.
	 *
	 * @param string $description The Description.
	 * @return string One line sanitized description.
	 */
	public function s_description_raw( $description ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->s_description_raw()', '5.0.0', 'tsf()->sanitize()->metadata_content()' );

		return $tsf->sanitize()->metadata_content( $description );
	}

	/**
	 * Converts multilines to single lines.
	 *
	 * @since 2.8.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 * @link https://www.php.net/manual/en/regexp.reference.escape.php
	 *
	 * @param string $text The input value with possible multiline.
	 * @return string The input string without multiple lines.
	 */
	public function s_singleline( $text ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->s_singleline()', '5.0.0', 'tsf()->sanitize()->newline_to_space()' );

		return $tsf->sanitize()->newline_to_space( $text );
	}

	/**
	 * Removes duplicated spaces from the input value.
	 *
	 * @since 2.8.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $text The input value with possible multispaces.
	 * @return string The input string without duplicated spaces.
	 */
	public function s_dupe_space( $text ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->s_dupe_space()', '5.0.0', 'tsf()->sanitize()->remove_repeated_spacing()' );

		return $tsf->sanitize()->remove_repeated_spacing( $text );
	}

	/**
	 * Removes tabs and replaces it with spaces.
	 *
	 * @since 2.8.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $text The input value with possible tabs.
	 * @return string The input string without tabs.
	 */
	public function s_tabs( $text ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->s_tabs()', '5.0.0', 'tsf()->sanitize()->tab_to_space()' );

		return $tsf->sanitize()->tab_to_space( $text );
	}

	/**
	 * Returns a -1, 0, or 1, based on nearest value.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param mixed $value Should ideally be -1, 0, or 1.
	 * @return int -1, 0, or 1.
	 */
	public function s_qubit( $value ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->s_qubit()', '5.0.0', 'tsf()->sanitize()->qubit()' );

		return $tsf->sanitize()->qubit( $value );
	}

	/**
	 * Returns a 1 or 0, for all truthy / falsy values.
	 *
	 * Uses double casting. First, we cast to bool, then to integer.
	 *
	 * @since 2.2.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param mixed $value Should ideally be a 1 or 0 integer passed in.
	 * @return int 1 or 0.
	 */
	public function s_one_zero( $value ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->s_one_zero()', '5.0.0', 'tsf()->sanitize()->boolean_integer()' );

		return $tsf->sanitize()->boolean_integer( $value );
	}

	/**
	 * Returns a numeric string, like '0', '1', '2'.
	 *
	 * Uses double casting. First, we cast to integer, then to string.
	 * Rounds floats down. Converts non-numeric inputs to '0'.
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param mixed $value Should ideally be an integer.
	 * @return string An integer as string.
	 */
	public function s_numeric_string( $value ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->s_numeric_string()', '5.0.0', 'tsf()->sanitize()->numeric_string()' );

		return $tsf->sanitize()->numeric_string( $value );
	}

	/**
	 * Returns a positive integer value.
	 *
	 * @since 2.2.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param mixed $value Should ideally be a positive integer.
	 * @return integer Positive integer.
	 */
	public function s_absint( $value ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->s_absint()', '5.0.0', 'absint()' );

		return \absint( $value );
	}

	/**
	 * Sanitizes color hexadecimals.
	 *
	 * @since 2.8.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $color String with potentially unwanted hex values.
	 * @return string The sanitized color hex.
	 */
	public function s_color_hex( $color ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->s_color_hex()', '5.0.0', 'tsf()->sanitize()->rgb_hex()' );

		return $tsf->sanitize()->rgb_hex( $color );
	}

	/**
	 * Replaces non-transformative hyphens with entity hyphens.
	 * Duplicated simple hyphens are preserved.
	 *
	 * Regex challenge, make the columns without an x light up:
	 * xxx - xx - xxx- - - xxxxxx xxxxxx- xxxxx - -
	 * --- - -- - ---- - - ------ ------- ----- - -
	 *
	 * The answer? `/((-{2,3})(*SKIP)-|-)(?(2)(*FAIL))/`
	 * Sybre-kamisama.
	 *
	 * @since 4.0.5
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $text String with potential hyphens.
	 * @return string A string with safe HTML encoded hyphens.
	 */
	public function s_hyphen( $text ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->s_hyphen()', '5.0.0', 'tsf()->sanitize()->lone_hyphen_to_entity()' );

		return $tsf->sanitize()->lone_hyphen_to_entity( $text );
	}

	/**
	 * Replaces non-break spaces with regular spaces.
	 *
	 * @since 2.8.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $text String with potentially unwanted nbsp values.
	 * @return string A spacey string.
	 */
	public function s_nbsp( $text ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->s_nbsp()', '5.0.0', 'tsf()->sanitize()->nbsp_to_space()' );

		return $tsf->sanitize()->nbsp_to_space( $text );
	}

	/**
	 * Replaces backslash with entity backslash.
	 *
	 * @since 2.8.2
	 * @since 5.0.0 1. No longer removes backslashes since we no longer add them.
	 *                 Even though that changes data handling, this shouldn't be used for data outside of our APIs.
	 *              2. Deprecated.
	 * @deprecated
	 *
	 * @param string $text String with potentially unwanted \ values.
	 * @return string A string with safe HTML encoded backslashes.
	 */
	public function s_bsol( $text ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->s_bsol()', '5.0.0', 'tsf()->sanitize()->backward_solidus_to_entity()' );

		return $tsf->sanitize()->backward_solidus_to_entity( $text );
	}

	/**
	 * Replaces backslash with entity backslash.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $text String with potentially wanted \ values.
	 * @return string A string with safe HTML encoded backslashes.
	 */
	public function s_bsol_raw( $text ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->s_bsol_raw()', '5.0.0', 'tsf()->sanitize()->backward_solidus_to_entity()' );

		return $tsf->sanitize()->backward_solidus_to_entity( $text );
	}

	/**
	 * Returns an single-line, trimmed title without dupliacated spaces, nbsp, or tabs.
	 * Also converts back-solidi to their respective HTML entities for non-destructive handling.
	 *
	 * @since 2.8.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $title The input Title.
	 * @return string Sanitized, beautified and trimmed title.
	 */
	public function s_title_raw( $title ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->s_title_raw()', '5.0.0', 'tsf()->sanitize()->metadata_content()' );

		return $tsf->sanitize()->metadata_content( $title );
	}

	/**
	 * Cleans known parameters from image details.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 1. Now sanitizes the caption.
	 *              2. Deprecated.
	 * @deprecated
	 * @NOTE If the input details are in an associative array, they'll be converted to sequential.
	 *
	 * @param array $details The image details, either associative (see $defaults) or sequential.
	 * @return array|array[] The image details array : {
	 *    string url:      The image URL,
	 *    int    id:       The image ID,
	 *    int    width:    The image width in pixels,
	 *    int    height:   The image height in pixels,
	 *    string alt:      The image alt tag,
	 *    string caption:  The image caption,
	 *    int    filesize: The image filesize in bytes,
	 * }
	 */
	public function s_image_details( $details ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->s_image_details()', '5.0.0', 'tsf()->sanitize()->image_details()' );

		return $tsf->sanitize()->image_details( $details );
	}

	/**
	 * Parses Twitter name and site. Adds @ if it wasn't supplied.
	 * Parses URL to path and adds @ if URL is given.
	 *
	 * @since 2.2.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $username String with potentially wrong Twitter username.
	 * @return string String with 'correct' Twitter username
	 */
	public function s_twitter_name( $username ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->s_twitter_name()', '5.0.0', 'tsf()->sanitize()->twitter_profile_handle()' );

		return $tsf->sanitize()->twitter_profile_handle( $username );
	}

	/**
	 * Parses Facebook profile URLs. Exchanges URLs for Facebook's.
	 *
	 * @since 2.2.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $profile String with potentially wrong Facebook profile URL.
	 * @return string String with 'correct' Facebook profile URL.
	 */
	public function s_facebook_profile( $profile ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->s_facebook_profile()', '5.0.0', 'tsf()->sanitize()->facebook_profile_link()' );

		return $tsf->sanitize()->facebook_profile_link( $profile );
	}

	/**
	 * Iterates over and cleans known parameters from image details. Also strips out duplicates.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array $details_array The image details, preferably sequential.
	 * @return array[] The image details array, sequential: int => {
	 *    string url:      The image URL,
	 *    int    id:       The image ID,
	 *    int    width:    The image width in pixels,
	 *    int    height:   The image height in pixels,
	 *    string alt:      The image alt tag,
	 *    string caption:  The image caption,
	 *    int    filesize: The image filesize in bytes,
	 * }
	 */
	public function s_image_details_deep( $details_array ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->s_image_details_deep()', '5.0.0', 'tsf()->sanitize()->image_details()' );

		$cleaned_details = $tsf->sanitize()->image_details( $details_array );

		return array_values(
			array_intersect_key(
				$cleaned_details,
				array_unique( array_filter( array_column( $cleaned_details, 'url' ) ) )
			)
		);
	}

	/**
	 * Sanitizes the Redirect URL.
	 *
	 * @since 2.2.4
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $url String with potentially unwanted redirect URL.
	 * @return string The Sanitized Redirect URL
	 */
	public function s_redirect_url( $url ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->s_redirect_url()', '5.0.0', 'tsf()->sanitize()->redirect_url()' );

		return $tsf->sanitize()->redirect_url( $url );
	}

	/**
	 * Makes non-relative URLs absolute, corrects the scheme to most preferred when the
	 * domain matches the current site, and makes it safer regardless afterward.
	 *
	 * Could not think of a good name. Enjoy.
	 *
	 * @since 4.0.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $url A possibly unsafe URL.
	 * @return string String a safe URL with Query Arguments.
	 */
	public function s_url_relative_to_current_scheme( $url ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->s_url_relative_to_current_scheme()', '5.0.0', 'tsf()->uri()->utils()->make_absolute_current_scheme_url()' );

		return $tsf->uri()->utils()->make_absolute_current_scheme_url( $url );
	}

	/**
	 * Converts absolute URLs to relative URLs, if they weren't already.
	 * The method should more aptly be named: "maybe_make_url_relative()".
	 *
	 * @since 2.6.5
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $url Full Path URL or relative URL.
	 * @return string Absolute path.
	 */
	public function s_relative_url( $url ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->s_relative_url()', '5.0.0', 'tsf()->uri()->utils()->get_relative_part_from_url()' );

		return $tsf->uri()->utils()->get_relative_part_from_url( $url );
	}

	/**
	 * Sanitizes term meta.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array $data The term meta to sanitize.
	 * @return array The sanitized term meta.
	 */
	public function s_term_meta( $data ) {
		\tsf()->_deprecated_function( 'tsf()->s_term_meta()', '5.0.0' );
		return \The_SEO_Framework\Data\Filter\Term::filter_meta_update( $data );
	}

	/**
	 * Sanitizes post meta.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array $data The post meta to sanitize.
	 * @return array The sanitized post meta.
	 */
	public function s_post_meta( $data ) {
		\tsf()->_deprecated_function( 'tsf()->s_post_meta()', '5.0.0' );
		return \The_SEO_Framework\Data\Filter\Post::filter_meta_update( $data );
	}

	/**
	 * Sanitizes user meta.
	 *
	 * @since 4.1.4
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array $data The user meta to sanitize.
	 * @return array The sanitized user meta.
	 */
	public function s_user_meta( $data ) {
		\tsf()->_deprecated_function( 'tsf()->s_user_meta()', '5.0.0' );
		return \The_SEO_Framework\Data\Filter\User::filter_meta_update( $data );
	}

	/**
	 * Sanitizes post type archive meta.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array $data The post type archive meta to sanitize : {
	 *    string $post_type => array $data
	 * }
	 * @return array The sanitized post type archive meta.
	 */
	public function s_all_post_type_archive_meta( $data ) {
		\tsf()->_deprecated_function( 'tsf()->s_all_post_type_archive_meta()', '5.0.0' );
		return \The_SEO_Framework\Data\Filter\Plugin::pta_meta( $data, [], 'pta' );
	}

	/**
	 * Sanitizes post type archive meta.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array $data The post type archive meta to sanitize.
	 * @return array The sanitized post type archive meta.
	 */
	public function s_post_type_archive_meta( $data ) {
		\tsf()->_deprecated_function( 'tsf()->s_post_type_archive_meta()', '5.0.0' );
		return \The_SEO_Framework\Data\Filter\Plugin::pta_meta( $data, [], 'pta' );
	}

	/**
	 * Sanitizes canonical scheme settings.
	 *
	 * @since 2.9.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $text String with potentially unwanted values.
	 * @return string A correct canonical scheme setting value.
	 */
	public function s_canonical_scheme( $text ) {
		\tsf()->_deprecated_function( 'tsf()->s_canonical_scheme()', '5.0.0' );
		return \The_SEO_Framework\Data\Filter\Plugin::canonical_scheme( $text, '', 'canonical_scheme' );
	}

	/**
	 * Sanitizes sitemap's min/max post value.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 1. Now also sanitizes the default fallback value.
	 *              2. Deprecated.
	 * @deprecated
	 *
	 * @param int $limit Integer with potentially unwanted values.
	 * @return int A limited integer 1<=R<=50000.
	 */
	public function s_min_max_sitemap( $limit ) {
		\tsf()->_deprecated_function( 'tsf()->s_min_max_sitemap()', '5.0.0' );
		return \The_SEO_Framework\Data\Filter\Plugin::sitemap_query_limit( $limit, 0, 'sitemap_query_limit' );
	}

	/**
	 * Parses Twitter Card radio input. Fills in default if incorrect value is supplied.
	 * Falls back to previous value if empty. If previous value is empty if will go to default.
	 *
	 * @since 2.5.2
	 * @since 5.0.0 1. Now falls back to 'summary_large_image' instead of the default option.
	 *              2. Deprecated.
	 * @deprecated
	 *
	 * @param string $card String with potentially wrong option value.
	 * @return string Sanitized twitter card type.
	 */
	public function s_twitter_card( $card ) {
		\tsf()->_deprecated_function( 'tsf()->s_twitter_card()', '5.0.0' );
		return \The_SEO_Framework\Data\Filter\Plugin::twitter_card( $card, 'summary', 'twitter_card' );
	}

	/**
	 * Sanitizes image preview directive value.
	 *
	 * @since 4.0.2
	 * @since 5.0.0 1. Now falls back to 'large' instead of 'standard'.
	 *              2. Deprecated.
	 * @deprecated
	 *
	 * @param string $size The image preview size with possibly unwanted values.
	 * @return string The robots image snippet preview directive value.
	 */
	public function s_image_preview( $size ) {
		\tsf()->_deprecated_function( 'tsf()->s_image_preview()', '5.0.0' );
		return \The_SEO_Framework\Data\Filter\Plugin::copyright_image_size( $size, 'large', 'max_image_preview' );
	}

	/**
	 * Sanitizes video and snippet preview length directive values.
	 *
	 * @since 4.0.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $length The snippet length that's possibly out of range.
	 * @return int A limited integer -1<=R<=600.
	 */
	public function s_snippet_length( $length ) {
		\tsf()->_deprecated_function( 'tsf()->s_snippet_length()', '5.0.0' );
		return \The_SEO_Framework\Data\Filter\Plugin::copyright_content_length( $length, 0, 'max_snippet_length' );
	}

	/**
	 * Returns the title separator value string.
	 *
	 * @since 2.2.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param mixed $sep A valid separator.
	 * @return string Title separator option
	 */
	public function s_title_separator( $sep ) {
		\tsf()->_deprecated_function( 'tsf()->s_title_separator()', '5.0.0' );
		return \The_SEO_Framework\Data\Filter\Plugin::title_separator( $sep, 'pipe', 'title_separator' );
	}

	/**
	 * Returns the knowledge type value string.
	 *
	 * @since 2.2.8
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param mixed $type Should ideally be a string 'person' or 'organization' passed in.
	 * @return string title Knowledge type option
	 */
	public function s_knowledge_type( $type ) {
		\tsf()->_deprecated_function( 'tsf()->s_knowledge_type()', '5.0.0' );
		return \The_SEO_Framework\Data\Filter\Plugin::knowledge_type( $type, 'person', 'knowledge_type' );
	}

	/**
	 * Sanitizes disabled post type entries.
	 * Filters out default post types.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array[string,int] $post_types An array with post type name indexes and 0/1 values.
	 * @return array
	 */
	public function s_disabled_post_types( $post_types ) {
		\tsf()->_deprecated_function( 'tsf()->s_disabled_post_types()', '5.0.0' );
		return \The_SEO_Framework\Data\Filter\Plugin::checkbox_array(
			\The_SEO_Framework\Data\Filter\Plugin::disabled_post_types( $post_types, [], 'disabled_post_types' ),
			[],
			'disabled_post_types',
		);
	}

	/**
	 * Sanitizes generic post type entries.
	 * Ideally, we want to check if the post type exists; however, some might be registered too late.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array[string,int] $post_types An array with post type name indexes and 0/1 values.
	 * @return array
	 */
	public function s_post_types( $post_types ) {
		\tsf()->_deprecated_function( 'tsf()->s_post_types()', '5.0.0' );
		return \The_SEO_Framework\Data\Filter\Plugin::checkbox_array( $post_types );
	}

	/**
	 * Sanitizes disabled taxonomy entries.
	 * Filters out default taxonomies.
	 *
	 * @since 4.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array[string,int] $taxonomies An array with taxonomy name indexes and 0/1 values.
	 * @return array
	 */
	public function s_disabled_taxonomies( $taxonomies ) {
		\tsf()->_deprecated_function( 'tsf()->s_disabled_taxonomies()', '5.0.0' );
		return \The_SEO_Framework\Data\Filter\Plugin::checkbox_array(
			\The_SEO_Framework\Data\Filter\Plugin::disabled_taxonomies( $taxonomies, [], 'disabled_taxonomies' ),
			[],
			'disabled_taxonomies',
		);
	}

	/**
	 * Sanitizes generic taxonomy entries.
	 * Ideally, we want to check if the taxonomy exists; however, some might be registered too late.
	 *
	 * @since 4.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array[string,int] $taxonomies An array with taxonomy name indexes and 0/1 values.
	 * @return array
	 */
	public function s_taxonomies( $taxonomies ) {
		\tsf()->_deprecated_function( 'tsf()->s_taxonomies()', '5.0.0' );
		return \The_SEO_Framework\Data\Filter\Plugin::checkbox_array( $taxonomies );
	}

	/**
	 * Returns left or right, for the separator location.
	 *
	 * This method fetches the default option because it's conditional (LTR/RTL).
	 *
	 * @since 2.2.2
	 * @since 5.0.0 1. No longer falls back to option or default optionm, but a language-based default instead.
	 *              2. Deprecated.
	 * @deprecated
	 *
	 * @param mixed $position Should ideally be a string 'left' or 'right' passed in.
	 * @return string left or right
	 */
	public function s_left_right( $position ) {
		\tsf()->_deprecated_function( 'tsf()->s_left_right()', '5.0.0' );
		return \The_SEO_Framework\Data\Filter\Plugin::title_location( $position, 'left', 'title_location' );
	}

	/**
	 * Returns left or right, for the home separator location.
	 *
	 * This method fetches the default option because it's conditional (LTR/RTL).
	 *
	 * @since 2.5.2
	 * @since 5.0.0 1. No longer falls back to option or default option, but a language-based default instead.
	 *              2. Deprecated.
	 * @deprecated
	 *
	 * @param mixed $position Should ideally be a string 'left' or 'right' passed in.
	 * @return string left or right
	 */
	public function s_left_right_home( $position ) {
		\tsf()->_deprecated_function( 'tsf()->s_left_right_home()', '5.0.0' );
		return \The_SEO_Framework\Data\Filter\Plugin::title_location( $position, 'left', 'home_title_location' );
	}

	/**
	 * Sanitizes alter query type.
	 *
	 * @since 2.9.4
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param mixed $type Should ideally be a string 'in_query' or 'post_query' passed in.
	 * @return string 'in_query' or 'post_query'
	 */
	public function s_alter_query_type( $type ) {
		\tsf()->_deprecated_function( 'tsf()->s_alter_query_type()', '5.0.0' );
		return \The_SEO_Framework\Data\Filter\Plugin::alter_query_type( $type, 'in_query', 'alter_archive_query_type' );
	}

	/**
	 * Sanitizes the html method value.
	 *
	 * @since 4.2.7
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param mixed $method Should ideally be a string 'fast', 'accurate', or 'thorough' passed in.
	 * @return string 'fast', 'accurate', or 'thorough'.
	 */
	public function s_description_html_method( $method ) {
		\tsf()->_deprecated_function( 'tsf()->s_description_html_method()', '5.0.0' );
		return \The_SEO_Framework\Data\Filter\Plugin::auto_description_method( $method, 'fast', 'auto_description_method' );
	}

	/**
	 * Tests if the post type archive of said post type contains public posts.
	 * Memoizes the return value.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $post_type The post type to test.
	 * @return bool True if a post is found in the archive, false otherwise.
	 */
	public function has_posts_in_post_type_archive( $post_type ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->has_posts_in_post_type_archive()', '5.0.0', 'tsf()->data()->post()->has_posts_in_pta()' );

		return $tsf->data()->post()->has_posts_in_pta( $post_type );
	}

	/**
	 * Determines whether the text has recognizable transformative syntax.
	 *
	 * @since 4.2.7
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $text The text to evaluate
	 * @return bool
	 */
	public function has_unprocessed_syntax( $text ) {
		\tsf()->_deprecated_function( 'tsf()->has_unprocessed_syntax()', '5.0.0' );
		return \The_SEO_Framework\Helper\Migrate::text_has_unprocessed_syntax( $text );
	}

	/**
	 * Determines if the input text has transformative Yoast SEO syntax.
	 *
	 * @since 4.0.5
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $text The text to evaluate.
	 * @return bool
	 */
	public function has_yoast_syntax( $text ) {
		\tsf()->_deprecated_function( 'tsf()->has_yoast_syntax()', '5.0.0' );
		return \The_SEO_Framework\Helper\Migrate::text_has_yoast_seo_syntax( $text );
	}

	/**
	 * Determines if the input text has transformative Rank Math syntax.
	 *
	 * @since 4.2.7
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $text The text to evaluate.
	 * @return bool
	 */
	public function has_rankmath_syntax( $text ) {
		\tsf()->_deprecated_function( 'tsf()->has_rankmath_syntax()', '5.0.0' );
		return \The_SEO_Framework\Helper\Migrate::text_has_rank_math_syntax( $text );
	}

	/**
	 * Determines if the input text has transformative SEOPress syntax.
	 *
	 * @since 4.2.8
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $text The text to evaluate.
	 * @return bool
	 */
	public function has_seopress_syntax( $text ) {
		\tsf()->_deprecated_function( 'tsf()->has_seopress_syntax()', '5.0.0' );
		return \The_SEO_Framework\Helper\Migrate::text_has_seopress_syntax( $text );
	}

	/**
	 * Detect active plugin by constant, class or function existence.
	 *
	 * Note: Class check is 3 times as slow as defined check. Function check is 2 times as slow.
	 *
	 * @since 1.3.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array $plugins Array of array for constants, classes and / or functions to check for plugin existence.
	 * @return boolean True if plugin exists or false if plugin constant, class or function not detected.
	 */
	public function detect_plugin( $plugins ) {

		\tsf()->_deprecated_function( 'tsf()->detect_plugin()', '5.0.0' );

		foreach ( $plugins['globals'] ?? [] as $name )
			if ( isset( $GLOBALS[ $name ] ) )
				return true;

		// Check for constants
		foreach ( $plugins['constants'] ?? [] as $name )
			if ( \defined( $name ) )
				return true;

		// Check for functions
		foreach ( $plugins['functions'] ?? [] as $name )
			if ( \function_exists( $name ) )
				return true;

		// Check for classes
		foreach ( $plugins['classes'] ?? [] as $name )
			if ( class_exists( $name, false ) ) // phpcs:ignore, TSF.Performance.Functions.PHP -- we don't autoload.
				return true;

		// No globals, constant, function, or class found to exist
		return false;
	}

	/**
	 * Detect if you can use the given constants, functions and classes.
	 * All inputs must be available for this method to return true.
	 * Memoizes the return value for the input argument--sorts the array deeply to ensure a match.
	 *
	 * @since 2.5.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array[] $plugins Array of array for globals, constants, classes
	 *                         and/or functions to check for plugin existence.
	 */
	public function can_i_use( $plugins = [] ) {
		\tsf()->_deprecated_function( 'tsf()->can_i_use()', '5.0.0' );
		return \The_SEO_Framework\Helper\Compatibility::can_i_use( $plugins );
	}

	/**
	 * Detect active plugin by constant, class or function existence.
	 * All parameters must match and return true.
	 *
	 * @since 2.5.2
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array[] $plugins Array of array for constants, classes
	 *                         and / or functions to check for plugin existence.
	 * @return bool True if ALL functions classes and constants exists
	 *              or false if plugin constant, class or function not detected.
	 */
	public function detect_plugin_multi( $plugins ) {
		\tsf()->_deprecated_function( 'tsf()->detect_plugin_multi()', '5.0.0' );
		return \The_SEO_Framework\Helper\Compatibility::can_i_use( $plugins );
	}

	/**
	 * Checks if the (parent) theme name is loaded.
	 *
	 * @since 2.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string|array $themes The theme names to test.
	 * @return bool is theme active.
	 */
	public function is_theme( $themes = '' ) {
		\tsf()->_deprecated_function( 'tsf()->is_theme()', '5.0.0' );
		return \The_SEO_Framework\Helper\Compatibility::is_theme_active( $themes );
	}

	/**
	 * Detects presence of a page builder that renders content dynamically.
	 *
	 * Detects the following builders:
	 * - Divi Builder by Elegant Themes
	 * - Visual Composer by WPBakery
	 *
	 * @since 4.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function detect_non_html_page_builder() {
		\tsf()->_deprecated_function( 'tsf()->detect_non_html_page_builder()', '5.0.0' );
		return \The_SEO_Framework\Helper\Compatibility::is_non_html_builder_active();
	}

	/**
	 * Detects if we're on a Gutenberg page.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function is_gutenberg_page() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_gutenberg_page()', '5.0.0', 'tsf()->query()->is_block_editor()' );

		return $tsf->query()->is_block_editor();
	}
}
