<?php
/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2 or later as
 * published by the Free Software Foundation.
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
 * Class AutoDescription_Query
 *
 * Caches and organizes the WP Query.
 * Functions are (somewhat) in alphabetical order!
 *
 * @since 2.6.0
 *
 * @license GPLv2+ <http://www.gnu.org/licenses/gpl-2.0.html>
 */
class AutoDescription_Query extends AutoDescription_Compat {

	/**
	 * Constructor. Load parent constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Checks whether $wp_query or $current_screen is set.
	 *
	 * @since 2.6.1
	 * @access private
	 * @staticvar bool $cache : Always true if set.
	 *
	 * @global object $wp_query
	 * @global object|null $current_screen
	 *
	 * @return bool True when wp_query or current_screen has been initialized.
	 */
	public function can_cache_query() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		if ( isset( $GLOBALS['wp_query']->query ) || isset( $GLOBALS['current_screen'] ) )
			return $cache = true;

		return false;
	}

	/**
	 * Get the real page ID, also depending on CPT.
	 *
	 * @param bool $use_cache Whether to use the cache or not.
	 *
	 * @staticvar int $id the ID.
	 *
	 * @since 2.5.0
	 *
	 * @return int|false The ID.
	 */
	public function get_the_real_ID( $use_cache = true ) {

		$is_admin = $this->is_admin();
		$can_cache = $this->can_cache_query();

		//* Never use cache for this in admin. Only causes bugs.
		$use_cache = $is_admin || false === $can_cache ? false : $use_cache;

		if ( $use_cache ) {
			static $id = null;

			if ( isset( $id ) )
				return $id;
		}

		//* Try to get ID from plugins.
		$id = $is_admin || false === $can_cache ? 0 : $this->check_the_real_ID();

		if ( empty( $id ) ) {
			//* The Post ID can be this ID as well.
			$id = get_queried_object_id();

			//* Never get this when this is an archive. It will always return the wrong value.
			if ( empty( $id ) && false === is_archive() && false === is_home() )
				$id = get_the_ID();
		}

		/**
		 * Applies filters 'the_seo_framework_current_object_id' : integer
		 * Can be either the Post ID, or the Term ID.
		 *
		 * @param int $id
		 * @param bool Whether the globals WP_Query or current_screen are set.
		 * @see AutoDescription_Query::can_cache_query()
		 *
		 * @since 2.6.2
		 */
		$id = (int) apply_filters( 'the_seo_framework_current_object_id', $id, $this->can_cache_query() );

		//* Turn ID into 0 if empty.
		return $id = empty( $id ) ? 0 : $id;
	}

	/**
	 * Get the real ID from plugins.
	 *
	 * Only works in front-end as there's no need to check for inconsistent
	 * functions for the current ID in the admin.
	 *
	 * @since 2.5.0
	 *
	 * Applies filters the_seo_framework_real_id : The Real ID for plugins on front-end.
	 *
	 * @staticvar int $cached_id The cached ID.
	 *
	 * @return int|empty the ID.
	 */
	public function check_the_real_ID() {

		static $cached_id = null;

		if ( isset( $cached_id ) && $this->can_cache_query() )
			return $cached_id;

		$id = '';

		if ( $this->is_wc_shop() ) {
			//* WooCommerce Shop
			$id = get_option( 'woocommerce_shop_page_id' );
		} else if ( function_exists( 'get_question_id' ) && did_action( 'template_redirect' ) ) {
			//* AnsPress
			$id = get_question_id();
		}

		$cached_id = (int) apply_filters( 'the_seo_framework_real_id', $id );

		return $cached_id;
	}

	/**
	 * Detects 404.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 *
	 * @return bool
	 */
	public function is_404() {

		static $cache = null;

		if ( isset( $cache ) && $this->can_cache_query() )
			return $cache;

		if ( is_404() )
			return $cache = true;

		return $cache = false;
	}

	/**
	 * Detects admin.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 *
	 * @return bool
	 */
	public function is_admin() {

		static $cache = null;

		if ( isset( $cache ) && $this->can_cache_query() )
			return $cache;

		if ( is_admin() )
			return $cache = true;

		return $cache = false;
	}

	/**
	 * Detects attachment page.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 * @uses $this->is_singular()
	 *
	 * @param int|string|array|object $attachment Attachment ID, title, slug, or array of such.
	 *
	 * @return bool
	 */
	public function is_attachment( $attachment = '' ) {

		static $cache = array();

		if ( isset( $cache[$attachment] ) && $this->can_cache_query() )
			return $cache[$attachment];

		if ( $this->is_singular( $attachment ) && is_attachment( $attachment ) )
			return $cache[$attachment] = true;

		return $cache[$attachment] = false;
	}

	/**
	 * Detects archive pages. Also in admin.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 *
	 * @return bool
	 */
	public function is_archive() {

		if ( $this->is_admin() )
			return $this->is_archive_admin();

		static $cache = null;

		if ( isset( $cache ) && $this->can_cache_query() )
			return $cache;

		global $wp_query;

		if ( is_archive() && false === $this->is_singular() )
			return $cache = true;

		global $wp_query;

		if ( $this->can_cache_query() && false === $this->is_singular() )
			if ( $wp_query->is_post_type_archive || $wp_query->is_date || $wp_query->is_author || $wp_query->is_category || $wp_query->is_tag || $wp_query->is_tax )
				return $cache = true;

		return $cache = false;
	}

	/**
	 * Extends default WordPress is_archive and made available in admin.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 *
	 * @global object $current_screen
	 *
	 * @return bool Post Type is archive
	 */
	public function is_archive_admin() {

		static $cache = null;

		if ( isset( $cache ) && $this->can_cache_query() )
			return $cache;

		global $current_screen;

		if ( isset( $current_screen->base ) && ( 'edit-tags' === $current_screen->base || 'term' === $current_screen->base ) )
			return $cache = true;

		return $cache = false;
	}

	/**
	 * Detects Term edit screen in WP Admin.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 *
	 * @global object $current_screen
	 *
	 * @return bool We're on Term Edit screen.
	 */
	public function is_term_edit() {

		static $cache = null;

		if ( isset( $cache ) && $this->can_cache_query() )
			return $cache;

		global $current_screen;

		if ( $this->wp_version( '4.4.9999', '>=' ) ) {
			if ( isset( $current_screen->base ) && ( 'term' === $current_screen->base ) )
				return $cache = true;
		} else {
			if ( isset( $current_screen->base ) && ( 'edit-tags' === $current_screen->base ) )
				return $cache = true;
		}

		return $cache = false;
	}

	/**
	 * Detects Post edit screen in WP Admin.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 *
	 * @global object $current_screen
	 *
	 * @return bool We're on Post Edit screen.
	 */
	public function is_post_edit() {

		static $cache = null;

		if ( isset( $cache ) && $this->can_cache_query() )
			return $cache;

		global $current_screen;

		if ( isset( $current_screen->base ) && 'post' === $current_screen->base )
			return $cache = true;

		return $cache = false;
	}

	/**
	 * Detects Post or Archive Lists in Admin.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 *
	 * @global object $current_screen
	 *
	 * @return bool We're on the edit screen.
	 */
	public function is_wp_lists_edit() {

		static $cache = null;

		if ( isset( $cache ) && $this->can_cache_query() )
			return $cache;

		global $current_screen;

		//* @NOTE USE WITH CAUTION - WP 4.5 & < 4.5 conflict.
		if ( isset( $current_screen->base ) && ( 'edit' === $current_screen->base || 'edit-tags' === $current_screen->base ) )
			return $cache = true;

		return $cache = false;
	}

	/**
	 * Detects author archives.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 * @uses $this->is_archive()
	 *
	 * @param mixed $author Optional. User ID, nickname, nicename, or array of User IDs, nicknames, and nicenames
	 *
	 * @return bool
	 */
	public function is_author( $author = '' ) {

		static $cache = array();

		if ( isset( $cache[$author] ) && $this->can_cache_query() )
			return $cache[$author];

		if ( $this->is_archive() && is_author( $author ) )
			return $cache[$author] = true;

		return $cache[$author] = false;
	}

	/**
	 * Detect the separated blog page.
	 *
	 * @param int $id the Page ID.
	 *
	 * @since 2.3.4
	 *
	 * @staticvar bool $is_blog_page
	 *
	 * @return bool true if is blog page. Always false if blog page is homepage.
	 */
	public function is_blog_page( $id = '' ) {

		if ( '' === $id )
			$id = $this->get_the_real_ID();

		static $is_blog_page = array();

		if ( isset( $is_blog_page[$id] ) && $this->can_cache_query() )
			return $is_blog_page[$id];

		$pfp = (int) get_option( 'page_for_posts' );

		if ( $id === $pfp ) {
			//* Don't use $this->is_archive (will loop).
			if ( $this->has_page_on_front() && false === $this->is_front_page() && false === is_archive() ) {
				return $is_blog_page[$id] = true;
			}
		}

		return $is_blog_page[$id] = false;
	}

	/**
	 * Detects category archives.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 * @uses $this->is_archive()
	 *
	 * @param mixed $category Optional. Category ID, name, slug, or array of Category IDs, names, and slugs.
	 *
	 * @return bool
	 */
	public function is_category( $category = '' ) {

		if ( $this->is_admin() )
			return $this->is_category_admin();

		static $cache = array();

		if ( isset( $cache[$category] ) && $this->can_cache_query() )
			return $cache[$category];

		if ( $this->is_archive() && is_category( $category ) )
			return $cache[$category] = true;

		return $cache[$category] = false;
	}

	/**
	 * Extends default WordPress is_category and made available in admin.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 *
	 * @global object $current_screen
	 *
	 * @return bool Post Type is category
	 */
	public function is_category_admin() {

		static $cache = null;

		if ( isset( $cache ) && $this->can_cache_query() )
			return $cache;

		global $current_screen;

		if ( $this->is_archive_admin() && isset( $current_screen->taxonomy ) ) {
			$tax = $current_screen->taxonomy;
			$len = strlen( $tax );

			if ( $len >= 8 && false !== strrpos( $tax, 'category', -8 ) )
				return $cache = true;

			if ( $len >= 3 && false !== strrpos( $tax, 'cat', -3 ) )
				return $cache = true;
		}

		return $cache = false;
	}

	/**
	 * Detects date archives.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 * @uses $this->is_archive()
	 *
	 * @return bool
	 */
	public function is_date() {

		static $cache = null;

		if ( isset( $cache ) && $this->can_cache_query() )
			return $cache;

		if ( $this->is_archive() && is_date() )
			return $cache = true;

		return $cache = false;
	}

	/**
	 * Detects day archives.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 * @uses $this->is_date()
	 *
	 * @return bool
	 */
	public function is_day() {

		static $cache = null;

		if ( isset( $cache ) && $this->can_cache_query() )
			return $cache;

		if ( is_day() )
			return $cache = true;

		return $cache = false;
	}

	/**
	 * Detects feed.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 *
	 * @param string|array $feeds Optional feed types to check.
	 *
	 * @return bool
	 */
	public function is_feed( $feeds = '' ) {

		static $cache = array();

		if ( isset( $cache[$feeds] ) && $this->can_cache_query() )
			return $cache[$feeds];

		if ( is_feed( $feeds ) )
			return $cache[$feeds] = true;

		return $cache[$feeds] = false;
	}

	/**
	 * Detects front page.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 *
	 * @param int $id The page or Post ID.
	 *
	 * @return bool
	 */
	public function is_front_page( $id = 0 ) {

		static $cache = array();

		if ( isset( $cache[$id] ) && $this->can_cache_query() )
			return $cache[$id];

		if ( is_front_page() && empty( $id ) )
			return $cache[$id] = true;

		//* Elegant Themes Support.
		if ( empty( $id ) && $this->is_home() ) {
			$sof = get_option( 'show_on_front' );

			if ( 'page' !== $sof && 'posts' !== $sof )
				return $cache[$id] = true;
		}

		if ( $id ) {
			$sof = get_option( 'show_on_front' );

			if ( 'page' === $sof && $id === get_option( 'page_on_front' ) )
				return $cache[$id] = true;

			if ( 'posts' === $sof && $id === get_option( 'page_for_posts' ) )
				return $cache[$id] = true;
		}

		return $cache[$id] = false;
	}

	/**
	 * Detects home page.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 *
	 * @return bool
	 */
	public function is_home() {

		static $cache = null;

		if ( isset( $cache ) && $this->can_cache_query() )
			return $cache;

		if ( is_home() )
			return $cache = true;

		return $cache = false;
	}

	/**
	 * Detects month archives.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 * @uses $this->is_date()
	 *
	 * @return bool
	 */
	public function is_month() {

		static $cache = null;

		if ( isset( $cache ) && $this->can_cache_query() )
			return $cache;

		if ( is_month() )
			return $cache = true;

		return $cache = false;
	}

	/**
	 * Detects pages.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 * @uses $this->is_singular()
	 *
	 * @param int|string|array $page Optional. Page ID, title, slug, or array of such. Default empty.
	 *
	 * @return bool
	 */
	public function is_page( $page = '' ) {

		static $cache = array();

		if ( isset( $cache[$page] ) && $this->can_cache_query() )
			return $cache[$page];

		if ( $this->is_singular( $page ) ) {
			if ( is_page( $page ) )
				return $cache[$page] = true;

			if ( $this->is_admin() )
				return $cache[$page] = $this->is_page_admin( $page );
		}

		return $cache[$page] = false;
	}

	/**
	 * Detects pages within the admin area.
	 *
	 * @since 2.6.0
	 * @see $this->is_page()
	 *
	 * @global object $current_screen;
	 *
	 * @param int|string|array $page Optional. Page ID, title, slug, or array of such. Default empty.
	 *
	 * @return bool
	 */
	public function is_page_admin( $page = '' ) {
		global $current_screen;

		if ( isset( $current_screen->post_type ) && 'page' === $current_screen->post_type )
			return true;

		return false;
	}

	/**
	 * Detects preview.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 *
	 * @return bool
	 */
	public function is_preview() {

		static $cache = null;

		if ( isset( $cache ) && $this->can_cache_query() )
			return $cache;

		if ( is_preview() )
			return $cache = true;

		return $cache = false;
	}

	/**
	 * Detects preview.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 *
	 * @return bool
	 */
	public function is_search() {

		static $cache = null;

		if ( isset( $cache ) && $this->can_cache_query() )
			return $cache;

		if ( is_search() )
			return $cache = true;

		return $cache = false;
	}

	/**
	 * Detects posts.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 * @uses $this->is_singular()
	 *
	 * @param int|string|array $post Optional. Post ID, title, slug, or array of such. Default empty.
	 *
	 * @return bool
	 */
	public function is_single( $post = '' ) {

		static $cache = array();

		if ( isset( $cache[$post] ) && $this->can_cache_query() )
			return $cache[$post];

		if ( $this->is_singular( $post ) ) {
			if ( is_single( $post ) )
				return $cache[$post] = true;

			if ( $this->is_admin() )
				return $cache[$post] = $this->is_single_admin( $post );
		}

		return $cache[$post] = false;
	}

	/**
	 * Detects posts within the admin area.
	 *
	 * @since 2.6.0
	 * @see $this->is_single()
	 *
	 * @global object $current_screen;
	 *
	 * @param int|string|array $post Optional. Page ID, title, slug, or array of such. Default empty.
	 *
	 * @return bool
	 */
	public function is_single_admin( $post = '' ) {
		global $current_screen;

		if ( isset( $current_screen->post_type ) && 'post' === $current_screen->post_type )
			return true;

		return false;
	}

	/**
	 * Replaces and expands default WordPress is_singular.
	 *
	 * @uses $this->is_blog_page()
	 * @uses $this->is_wc_shop()
	 * @uses $this->is_single()
	 * @uses $this->is_page()
	 * @uses $this->is_attachment()
	 *
	 * @param string|array $post_types Optional. Post type or array of post types. Default empty.
	 *
	 * @staticvar bool $cache
	 *
	 * @since 2.5.2
	 *
	 * @return bool Post Type is singular
	 */
	public function is_singular( $post_types = '' ) {

		static $cache = array();

		if ( isset( $cache[$post_types] ) && $this->can_cache_query() )
			return $cache[$post_types];

		//* WP_Query functions require loop, do alternative check.
		if ( $this->is_admin() )
			return $cache[$post_types] = $this->is_singular_admin();

		if ( is_int( $post_types ) ) {
			//* Cache ID. Core is_singlar() doesn't accept integers.
			$id = $post_types;
			$post_types = '';
		}

		//* Default check.
		if ( is_singular( $post_types ) )
			return $cache[$post_types] = true;

		$id = isset( $id ) ? $id : $this->get_the_real_ID();

		//* Check for somewhat singulars. We need this to adjust Meta data filled in Posts.
		if ( $this->is_blog_page( $id ) || $this->is_wc_shop() )
			return $cache[$post_types] = true;

		return $cache[$post_types] = false;
	}

	/**
	 * Extends default WordPress is_singular and made available in admin.
	 *
	 * @staticvar bool $cache
	 *
	 * @since 2.5.2
	 *
	 * @global object $current_screen
	 *
	 * @return bool Post Type is singular
	 */
	public function is_singular_admin() {

		static $cache = null;

		if ( isset( $cache ) && $this->can_cache_query() )
			return $cache;

		global $current_screen;

		if ( isset( $current_screen->base ) && ( 'edit' === $current_screen->base || 'post' === $current_screen->base ) )
			return $cache = true;

		return $cache = false;
	}

	/**
	 * Detect the static front page.
	 *
	 * @param int $id the Page ID.
	 *
	 * @staticvar array $cache
	 * @since 2.3.8
	 *
	 * @return bool true if is blog page. Always false if blog page is homepage.
	 * False early when false as ID is entered.
	 */
	public function is_static_frontpage( $id = '' ) {

		if ( empty( $id ) )
			$id = $this->get_the_real_ID();

		static $cache = array();

		if ( isset( $cache[$id] ) && $this->can_cache_query() )
			return $cache[$id];

		$sof = (string) get_option( 'show_on_front' );

		if ( 'page' === $sof ) {
			$pof = (int) get_option( 'page_on_front' );

			if ( $id === $pof )
				return $cache[$id] = true;
		}

		return $cache[$id] = false;
	}

	/**
	 * Detects tag archives.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 * @uses $this->is_archive()
	 *
	 * @param mixed $tag Optional. Tag ID, name, slug, or array of Tag IDs, names, and slugs.
	 *
	 * @return bool
	 */
	public function is_tag( $tag = '' ) {

		static $cache = array();

		if ( isset( $cache[$tag] ) && $this->can_cache_query() )
			return $cache[$tag];

		//* Admin requires another check.
		if ( $this->is_admin() )
			return $cache[$tag] = $this->is_tag_admin();

		if ( is_tag( $tag ) )
			return $cache[$tag] = true;

		return $cache[$tag] = false;
	}

	/**
	 * Extends default WordPress is_tag and made available in admin.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 *
	 * @global object $current_screen
	 *
	 * @return bool Post Type is category
	 */
	public function is_tag_admin() {

		static $cache = null;

		if ( isset( $cache ) && $this->can_cache_query() )
			return $cache;

		if ( $this->is_archive_admin() ) {
			global $current_screen;

			if ( isset( $current_screen->taxonomy ) && strlen( $current_screen->taxonomy ) >= 3 && false !== strrpos( $current_screen->taxonomy, 'tag', -3 ) )
				return $cache = true;
		}

		return $cache = false;
	}

	/**
	 * Detects taxonomy archives.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 * @uses $this->is_archive()
	 *
	 * @param string|array     $taxonomy Optional. Taxonomy slug or slugs.
	 * @param int|string|array $term     Optional. Term ID, name, slug or array of Term IDs, names, and slugs.
	 *
	 * @return bool
	 */
	public function is_tax( $taxonomy = '', $term = '' ) {

		static $cache = null;

		if ( isset( $cache[$taxonomy][$term] ) && $this->can_cache_query() )
			return $cache[$taxonomy][$term];

		if ( is_tax( $taxonomy, $term ) )
			return $cache[$taxonomy][$term] = true;

		return $cache[$taxonomy][$term] = false;
	}

	/**
	 * Is Ulimate Member user page.
	 * Check for function accessibility: um_user, um_is_core_page, um_get_requested_user
	 *
	 * @staticvar bool $cache
	 * @uses $this->can_i_use()
	 *
	 * @since 2.5.2
	 */
	public function is_ultimate_member_user_page() {

		static $cache = null;

		if ( isset( $cache ) && $this->can_cache_query() )
			return $cache;

		$caniuse = (bool) $this->can_i_use( array( 'functions' => array( 'um_user', 'um_is_core_page', 'um_get_requested_user' ) ), false );

		return $cache = $caniuse;
	}

	/**
	 * Check for WooCommerce shop page.
	 *
	 * @staticvar bool $cache
	 *
	 * @since 2.5.2
	 */
	public function is_wc_shop() {

		static $cache = null;

		if ( isset( $cache ) && $this->can_cache_query() )
			return $cache;

		//* Can't check in admin.
		if ( false === $this->is_admin() && function_exists( 'is_shop' ) && is_shop() )
			return $cache = true;

		return $cache = false;
	}

	/**
	 * Check for WooCommerce product page.
	 *
	 * @staticvar bool $cache
	 *
	 * @since 2.5.2
	 */
	public function is_wc_product() {

		static $cache = null;

		if ( isset( $cache ) && $this->can_cache_query() )
			return $cache;

		//* Can't check in admin.
		if ( false === $this->is_admin() && function_exists( 'is_product' ) && is_product() )
			return $cache = true;

		return $cache = false;
	}

	/**
	 * Detects year archives.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 * @uses $this->is_date()
	 *
	 * @return bool
	 */
	public function is_year() {

		static $cache = null;

		if ( isset( $cache ) && $this->can_cache_query() )
			return $cache;

		if ( is_year() )
			return $cache = true;

		return $cache = false;
	}

	/**
	 * Whether we're on the SEO settings page.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 *
	 * @return bool
	 */
	public function is_seo_settings_page() {

		static $cache = null;

		if ( isset( $cache ) && $this->can_cache_query() )
			return $cache;

		return $cache = $this->is_menu_page( $this->page_id );
	}

	/**
	 * The amount of pages.
	 * Fetches global $page through Query Var.
	 *
	 * @staticvar int $page
	 * @since 2.6.0
	 *
	 * @return int $page
	 */
	public function page() {

		static $page = null;

		if ( isset( $page ) && $this->can_cache_query() )
			return $page;

		$page = get_query_var( 'page' );

		return $page = $page ? (int) $page : 1;
	}

	/**
	 * The number of the current page.
	 * Fetches global $paged through Query Var. Determines
	 *
	 * @staticvar int $paged
	 * @since 2.6.0
	 *
	 * @return int $paged
	 */
	public function paged() {

		static $paged = null;

		if ( isset( $paged ) && $this->can_cache_query() )
			return $paged;

		$paged = get_query_var( 'paged' );

		return $paged = $paged ? (int) $paged : 1;
	}

}
