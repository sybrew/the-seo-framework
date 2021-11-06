<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Query
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2021 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\Query
 *
 * Caches and organizes the WP Query.
 *
 * @since 2.8.0
 */
class Query extends Core {

	/**
	 * Checks for pretty permalinks.
	 *
	 * @since 2.2.9
	 * @since 3.1.0 Now public.
	 *
	 * @var bool true if pretty
	 */
	public $pretty_permalinks;

	/**
	 * Checks whether $wp_query or $current_screen is set.
	 * Memoizes the return value once we're sure it won't change.
	 *
	 * @since 2.6.1
	 * @since 2.9.0 Added doing it wrong notice.
	 * @since 3.1.0 1. Is now protected.
	 *              2. Now asks for and passes $method.
	 *              3. Now returns false on WP CLI.
	 * @since 3.2.2 No longer spits out errors on production websites.
	 * @global \WP_Query $wp_query
	 * @global \WP_Screen|null $current_screen
	 *
	 * @param string $method The method that invokes this.
	 * @return bool True when wp_query or current_screen has been initialized.
	 */
	protected function can_cache_query( $method ) {

		// Don't use method memo() here for this method is called over 85 times per page.
		static $memo;

		if ( isset( $memo ) ) return $memo;

		if ( \defined( 'WP_CLI' ) && WP_CLI )
			return $memo = false;
		if ( isset( $GLOBALS['wp_query']->query ) || isset( $GLOBALS['current_screen'] ) )
			return $memo = true;

		$this->the_seo_framework_debug
			and $this->do_query_error_notice( $method );

		return false;
	}

	// phpcs:disable -- method unused in production.
	/**
	 * Outputs a doing it wrong notice if an error occurs in the current query.
	 *
	 * @since 3.0.0
	 *
	 * @param string $method The original caller method.
	 */
	protected function do_query_error_notice( $method ) {

		$message = "You've initiated a method that uses queries too early.";

		$trace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 4 );
		if ( ! empty( $trace[3] ) ) {
			$message .= " - In file: {$trace[3]['file']}";
			$message .= " - On line: {$trace[3]['line']}";
		}

		$this->_doing_it_wrong( \esc_html( $method ), \esc_html( $message ), '2.9.0' );

		// Backtrace debugging.
		$depth = 10;
		static $_more = true;
		if ( $_more ) {
			error_log( var_export( debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, $depth ), true ) );
			$_more = false;
		}
	}
	// phpcs:enable -- Method unused in production.

	/**
	 * Returns the post type name from query input or real ID.
	 *
	 * @since 4.0.5
	 * @since 4.2.0 Now supports common archives without relying on the first post.
	 *
	 * @param int|WP_Post|null $post (Optional) Post ID or post object.
	 * @return string|false Post type on success, false on failure.
	 */
	public function get_post_type_real_ID( $post = null ) {

		if ( isset( $post ) )
			return \get_post_type( $post );

		if ( $this->is_archive() ) {
			if ( $this->is_category() || $this->is_tag() || $this->is_tax() ) {
				$post_type = $this->get_post_types_from_taxonomy();
				$post_type = \is_array( $post_type ) ? reset( $post_type ) : $post_type;
			} elseif ( \is_post_type_archive() ) {
				$post_type = \get_query_var( 'post_type' );
				$post_type = \is_array( $post_type ) ? reset( $post_type ) : $post_type;
			} else {
				// Let WP guess for us. This works reliable (enough) on non-404 queries.
				$post_type = \get_post_type();
			}
		} else {
			$post_type = \get_post_type( $this->get_the_real_ID() );
		}

		return $post_type;
	}

	/**
	 * Returns the post type name from current screen.
	 *
	 * @since 3.1.0
	 * @global \WP_Screen $current_screen
	 *
	 * @return string
	 */
	public function get_admin_post_type() {
		return $GLOBALS['current_screen']->post_type ?? '';
	}

	/**
	 * Returns a list of post types shared with the taxonomy.
	 *
	 * @since 4.0.0
	 *
	 * @param string $taxonomy Optional. The taxonomy to check. Defaults to current screen/query taxonomy.
	 * @return array List of post types.
	 */
	public function get_post_types_from_taxonomy( $taxonomy = '' ) {

		$taxonomy = $taxonomy ?: $this->get_current_taxonomy();
		$tax      = $taxonomy ? \get_taxonomy( $taxonomy ) : null;

		return $tax->object_type ?? [];
	}

	/**
	 * Get the real page ID, also from CPT, archives, author, blog, etc.
	 * Memoizes the return value.
	 *
	 * @since 2.5.0
	 * @since 3.1.0 No longer checks if we can cache the query when $use_cache is false.
	 *
	 * @param bool $use_cache Whether to use the cache or not.
	 * @return int|false The ID.
	 */
	public function get_the_real_ID( $use_cache = true ) { // phpcs:ignore -- ID is capitalized because WordPress does that too: get_the_ID().

		if ( \is_admin() )
			return $this->get_the_real_admin_ID();

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( $use_cache && null !== $memo = umemo( __METHOD__ ) ) return $memo;

		$use_cache = $use_cache && $this->can_cache_query( __METHOD__ );

		// Try to get ID from plugins or feed when caching is available.
		if ( $use_cache ) {
			/**
			 * @since 2.5.0
			 * @param int $id
			 */
			$id = \apply_filters(
				'the_seo_framework_real_id',
				$this->is_feed() ? \get_the_ID() : 0
			);
		}

		/**
		 * @since 2.6.2
		 * @param int  $id        Can be either the Post ID, or the Term ID.
		 * @param bool $use_cache Whether this value is stored in runtime caching.
		 */
		$id = (int) \apply_filters_ref_array(
			'the_seo_framework_current_object_id',
			[
				// This catches most IDs. Even Post IDs.
				( $id ?? 0 ) ?: \get_queried_object_id(),
				$use_cache,
			]
		);

		// Do not overwrite cache when not requested. Otherwise, we'd have two "initial" states, causing incongruities.
		return $use_cache ? umemo( __METHOD__, $id ) : $id;
	}

	/**
	 * Fetches post or term ID within the admin.
	 * Alters while in the loop. Therefore, this can't be cached and must be called within the loop.
	 *
	 * @since 2.7.0
	 * @since 2.8.0 Removed WP 3.9 compat
	 *
	 * @return int The admin ID.
	 */
	public function get_the_real_admin_ID() { // phpcs:ignore -- ID is capitalized because WordPress does that too: get_the_ID().

		$id = \get_the_ID();

		// Current term ID (outside loop).
		if ( ! $id && $this->is_archive_admin() )
			$id = $this->get_admin_term_id();

		return (int) \apply_filters( 'the_seo_framework_current_admin_id', $id );
	}

	/**
	 * Returns the front page ID, if home is a page.
	 *
	 * @since 2.6.0
	 *
	 * @return int the ID.
	 */
	public function get_the_front_page_ID() { // phpcs:ignore -- ID is capitalized because WordPress does that too: get_the_ID().
		return umemo( __METHOD__ )
			?? umemo(
				__METHOD__,
				$this->has_page_on_front() ? (int) \get_option( 'page_on_front' ) : 0
			);
	}

	/**
	 * Fetches the Term ID on admin pages.
	 *
	 * @since 2.6.0
	 * @since 2.6.6 Moved from class The_SEO_Framework_Term_Data.
	 * @since 3.1.0 1. Removed WP 4.5 compat. Now uses global $tag_ID.
	 *              2. Removed caching
	 * @global int $tag_ID
	 *
	 * TODO consider making the function name id -> ID.
	 *
	 * @return int Term ID.
	 */
	public function get_admin_term_id() {

		if ( false === $this->is_archive_admin() )
			return 0;

		return \absint(
			! empty( $GLOBALS['tag_ID'] ) ? $GLOBALS['tag_ID'] : 0
		);
	}

	/**
	 * Returns the current taxonomy, if any.
	 * Memoizes the return value.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 1. Now works in the admin.
	 *              2. Added caching
	 * @global \WP_Screen $current_screen
	 *
	 * @return string The queried taxonomy type.
	 */
	public function get_current_taxonomy() {
		return $this->memo_query()
			?? $this->memo_query(
				( \is_admin() ? $GLOBALS['current_screen'] : \get_queried_object() )
					->taxonomy ?? ''
			);
	}

	/**
	 * Returns the current post type, if any.
	 *
	 * @since 4.1.4
	 *
	 * @return string The queried post type.
	 */
	public function get_current_post_type() {
		return $this->get_post_type_real_ID() ?: $this->get_admin_post_type();
	}

	/**
	 * Detects 404.
	 *
	 * @since 2.6.0
	 *
	 * @return bool
	 */
	public function is_404() {
		return \is_404();
	}

	/**
	 * Detects admin screen.
	 *
	 * @since 2.6.0
	 *
	 * @return bool
	 */
	public function is_admin() {
		return \is_admin();
	}

	/**
	 * Detects attachment page.
	 *
	 * @since 2.6.0
	 * @since 4.0.0 Now reliably works on admin screens.
	 *
	 * @param mixed $attachment Attachment ID, title, slug, or array of such.
	 * @return bool
	 */
	public function is_attachment( $attachment = '' ) {

		if ( \is_admin() )
			return $this->is_attachment_admin();

		if ( ! $attachment )
			return \is_attachment();

		return $this->memo_query( null, $attachment )
			?? $this->memo_query( \is_attachment( $attachment ), $attachment );
	}

	/**
	 * Detects attachments within the admin area.
	 *
	 * @since 4.0.0
	 * @see $this->is_attachment()
	 * @global \WP_Screen $current_screen;
	 *
	 * @return bool
	 */
	public function is_attachment_admin() {
		return $this->is_singular_admin() && 'attachment' === $this->get_admin_post_type();
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
	 *
	 * @param int|WP_Post|null $post (Optional) Post ID or post object.
	 * @return bool
	 */
	public function is_singular_archive( $post = null ) {

		if ( isset( $post ) ) {
			$id = \is_int( $post )
				? $post
				: ( \get_post( $post )->ID ?? 0 );
		} else {
			$id = null;
		}

		return $this->memo_query( null, $id )
			?? $this->memo_query(
				/**
				 * @since 4.0.5
				 * @since 4.0.7 The $id can now be null, when no post is given.
				 * @param bool     $is_singular_archive Whether the post ID is a singular archive.
				 * @param int|null $id                  The supplied post ID. Null when in the loop.
				 */
				\apply_filters_ref_array(
					'the_seo_framework_is_singular_archive',
					[
						$this->is_home_as_page( $id ),
						$id,
					]
				),
				$id
			);
	}

	/**
	 * Detects archive pages. Also in admin.
	 *
	 * @since 2.6.0
	 * @global \WP_Query $wp_query
	 *
	 * @return bool
	 */
	public function is_archive() {

		if ( \is_admin() )
			return $this->is_archive_admin();

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = $this->memo_query() ) return $memo;

		$can_cache = $this->can_cache_query( __METHOD__ );

		if ( \is_archive() && false === $this->is_singular() )
			return $can_cache ? $this->memo_query( true ) : true;

		// The $can_cache check is used here because it asserted $wp_query is valid on the front-end.
		if ( $can_cache && false === $this->is_singular() ) {
			global $wp_query;

			if ( $wp_query->is_category || $wp_query->is_tag || $wp_query->is_tax || $wp_query->is_post_type_archive || $wp_query->is_date || $wp_query->is_author )
				return $this->memo_query( true );
		}

		return $can_cache ? $this->memo_query( false ) : false;
	}

	/**
	 * Extends default WordPress is_archive() and determines screen in admin.
	 *
	 * @since 2.6.0
	 * @global \WP_Screen $current_screen
	 *
	 * @return bool Post Type is archive
	 */
	public function is_archive_admin() {
		return \in_array( $GLOBALS['current_screen']->base ?? '', [ 'edit-tags', 'term' ], true );
	}

	/**
	 * Detects Term edit screen in WP Admin.
	 *
	 * @since 2.6.0
	 * @global \WP_Screen $current_screen
	 *
	 * @return bool True if on Term Edit screen. False otherwise.
	 */
	public function is_term_edit() {
		return 'term' === ( $GLOBALS['current_screen']->base ?? '' );
	}

	/**
	 * Detects Post edit screen in WP Admin.
	 *
	 * @since 2.6.0
	 * @global \WP_Screen $current_screen
	 *
	 * @return bool We're on Post Edit screen.
	 */
	public function is_post_edit() {
		return 'post' === ( $GLOBALS['current_screen']->base ?? '' );
	}

	/**
	 * Detects Post or Archive Lists in Admin.
	 *
	 * @since 2.6.0
	 * @global \WP_Screen $current_screen
	 *
	 * @return bool We're on the edit screen.
	 */
	public function is_wp_lists_edit() {
		return \in_array( $GLOBALS['current_screen']->base ?? '', [ 'edit-tags', 'edit' ], true );
	}

	/**
	 * Detects Profile edit screen in WP Admin.
	 *
	 * @since 4.1.4
	 * @global \WP_Screen $current_screen
	 *
	 * @return bool True if on Profile Edit screen. False otherwise.
	 */
	public function is_profile_edit() {
		return \in_array( $GLOBALS['current_screen']->base ?? '', [ 'profile', 'user-edit' ], true );
	}

	/**
	 * Detects author archives.
	 *
	 * @since 2.6.0
	 * @uses $this->is_archive()
	 *
	 * @param mixed $author Optional. User ID, nickname, nicename, or array of User IDs, nicknames, and nicenames
	 * @return bool
	 */
	public function is_author( $author = '' ) {

		if ( ! $author )
			return \is_author();

		return $this->memo_query( null, $author )
			?? $this->memo_query( \is_author( $author ), $author );
	}

	/**
	 * Detects the blog page.
	 *
	 * @since 2.6.0
	 * @since 4.2.0 Added the first parameter to allow custom query testing.
	 *
	 * @param int|WP_Post|null $post Optional. Post ID or post object.
	 *                               Do not supply from WP_Query's main loop-query.
	 * @return bool
	 */
	public function is_home( $post = null ) {

		if ( isset( $post ) ) {
			$id = \is_int( $post )
				? $post
				: ( \get_post( $post )->ID ?? 0 );

			$is_pfp = (int) \get_option( 'page_for_posts' ) === $id;
		} else {
			$is_pfp = \is_home();
		}

		return $is_pfp;
	}

	/**
	 * Detects the non-front blog page.
	 *
	 * @since 4.2.0
	 *
	 * @param int|WP_Post|null $post Optional. Post ID or post object.
	 *                               Do not supply from WP_Query's main loop-query.
	 * @return bool
	 */
	public function is_home_as_page( $post = null ) {
		// If front is a blog, the blog is never a page.
		return $this->has_page_on_front() ? $this->is_home( $post ) : false;
	}

	/**
	 * Detects category archives.
	 *
	 * @since 2.6.0
	 * @uses $this->is_archive()
	 *
	 * @param mixed $category Optional. Category ID, name, slug, or array of Category IDs, names, and slugs.
	 * @return bool
	 */
	public function is_category( $category = '' ) {

		if ( \is_admin() )
			return $this->is_category_admin();

		return $this->memo_query( null, $category )
			?? $this->memo_query( \is_category( $category ), $category );
	}

	/**
	 * Extends default WordPress is_category() and determines screen in admin.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 No longer guesses category by name. It now only matches WordPress's built-in category.
	 * @since 4.0.0 Removed caching.
	 *
	 * @return bool Post Type is category
	 */
	public function is_category_admin() {
		return $this->is_archive_admin() && 'category' === $this->get_current_taxonomy();
	}

	/**
	 * Detects customizer preview.
	 *
	 * Unlike is_preview(), WordPress has prior security checks for this
	 * in `\WP_Customize_Manager::setup_theme()`.
	 *
	 * @since 4.0.0
	 *
	 * @return bool
	 */
	public function is_customize_preview() {
		return \is_customize_preview();
	}

	/**
	 * Detects date archives.
	 *
	 * @since 2.6.0
	 *
	 * @return bool
	 */
	public function is_date() {
		return \is_date();
	}

	/**
	 * Detects day archives.
	 *
	 * @since 2.6.0
	 * @uses $this->is_date()
	 *
	 * @return bool
	 */
	public function is_day() {
		return \is_day();
	}

	/**
	 * Detects feed.
	 *
	 * @since 2.6.0
	 *
	 * @param string|array $feeds Optional feed types to check.
	 * @return bool
	 */
	public function is_feed( $feeds = '' ) {
		return \is_feed( $feeds );
	}

	/**
	 * Detects front page.
	 *
	 * @since 2.9.0
	 *
	 * @return bool
	 */
	public function is_real_front_page() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition
		if ( null !== $cache = $this->memo_query() )
			return $cache;

		$is_front_page = \is_front_page();

		if ( ! $is_front_page ) {
			// Elegant Themes's Extra Support: Assert home, but only when it's not registered as such.
			$is_front_page = $this->is_home() && 0 === $this->get_the_real_ID()
				&& ! \in_array( \get_option( 'show_on_front' ), [ 'page', 'post' ], true );
		}

		return $this->memo_query( $is_front_page );
	}

	/**
	 * Checks for front page by input ID without engaging into the query.
	 *
	 * @NOTE This doesn't check for anomalies in the query.
	 * So, don't use this to test user-engaged WordPress queries, ever.
	 * WARNING: This will lead to **FALSE POSITIVES** for Date, CPTA, Search, and other archives.
	 *
	 * @see $this->is_front_page_by_id(), which supports query checking.
	 * @see $this->is_real_front_page(), which solely uses query checking.
	 *
	 * @since 3.2.2
	 *
	 * @param int $id The tested ID.
	 * @return bool
	 */
	public function is_real_front_page_by_id( $id ) {
		return $id === $this->get_the_front_page_ID();
	}

	/**
	 * Detects month archives.
	 *
	 * @since 2.6.0
	 *
	 * @return bool
	 */
	public function is_month() {
		return \is_month();
	}

	/**
	 * Detects pages.
	 * When $page is supplied, it will check against the current object. So it will not work in the admin screens.
	 *
	 * @since 2.6.0
	 * @since 4.0.0 Now tests for post type, which is more reliable.
	 * @api not used internally, polar opposite of is_single().
	 * @uses $this->is_singular()
	 *
	 * @param int|string|array $page Optional. Page ID, title, slug, or array of such. Default empty.
	 * @return bool
	 */
	public function is_page( $page = '' ) {

		if ( \is_admin() )
			return $this->is_page_admin();

		if ( empty( $page ) )
			return \is_page();

		return $this->memo_query( null, $page )
			?? $this->memo_query(
				\is_int( $page ) || $page instanceof \WP_Post
					? \in_array( \get_post_type( $page ), $this->get_hierarchical_post_types(), true )
					: \is_page( $page ),
				$page
			);
	}

	/**
	 * Detects pages within the admin area.
	 *
	 * @since 2.6.0
	 * @since 4.0.0 Now tests for post type, although redundant.
	 * @see $this->is_page()
	 *
	 * @return bool
	 */
	public function is_page_admin() {
		return $this->is_singular_admin() && \in_array( $this->get_admin_post_type(), $this->get_hierarchical_post_types(), true );
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
	 *
	 * @return bool
	 */
	public function is_preview() {

		$is_preview = false;

		if ( \is_preview()
			&& \is_user_logged_in()
			&& \is_singular()
			&& \current_user_can( 'edit_post', \get_the_ID() )
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
	 *
	 * @return bool
	 */
	public function is_search() {
		return \is_search() && ! \is_admin();
	}

	/**
	 * Detects single post pages.
	 * When $post is supplied, it will check against the current object. So it will not work in the admin screens.
	 *
	 * @since 2.6.0
	 * @since 4.0.0 Now tests for post type, which is more reliable.
	 * @uses The_SEO_Framework_Query::is_single_admin()
	 *
	 * @param int|string|array $post Optional. Post ID, title, slug, or array of such. Default empty.
	 * @return bool
	 */
	public function is_single( $post = '' ) {

		if ( \is_admin() )
			return $this->is_single_admin();

		return $this->memo_query( null, $post )
			?? $this->memo_query(
				\is_int( $post ) || $post instanceof \WP_Post
					? \in_array( \get_post_type( $post ), $this->get_nonhierarchical_post_types(), true )
					: \is_single( $post ),
				$post
			);
	}

	/**
	 * Detects posts within the admin area.
	 *
	 * @since 2.6.0
	 * @since 4.0.0 Now no longer returns true on categories and tags.
	 * @see The_SEO_Framework_Query::is_single()
	 *
	 * @return bool
	 */
	public function is_single_admin() {
		// Checks for "is_singular_admin()" because the post type is non-hierarchical.
		return $this->is_singular_admin() && \in_array( $this->get_admin_post_type(), $this->get_nonhierarchical_post_types(), true );
	}

	/**
	 * Determines if the current page is singular is holds singular items within the admin screen.
	 * Replaces and expands default WordPress `is_singular()`.
	 *
	 * @since 2.5.2
	 * @since 3.1.0 Now passes $post_types parameter in admin screens, only when it's an integer.
	 * @since 4.0.0 No longer processes integers as input.
	 * @uses $this->is_singular_admin()
	 *
	 * @param string|array $post_types Optional. Post type or array of post types. Default empty string.
	 * @return bool Post Type is singular
	 */
	public function is_singular( $post_types = '' ) {

		if ( \is_int( $post_types ) ) {
			// Integers are no longer accepted.
			$post_types = '';
		}

		// WP_Query functions require loop, do alternative check.
		if ( \is_admin() )
			return $this->is_singular_admin();

		if ( $post_types )
			return \is_singular( $post_types );

		return $this->memo_query()
			?? $this->memo_query( \is_singular() || $this->is_singular_archive() );
	}

	/**
	 * Determines if the page is singular within the admin screen.
	 *
	 * @since 2.5.2
	 * @since 3.1.0 Added $post_id parameter. When used, it'll only check for it.
	 * @since 4.0.0 Removed first parameter.
	 * @global \WP_Screen $current_screen
	 *
	 * @return bool Post Type is singular
	 */
	public function is_singular_admin() {
		return \in_array( $GLOBALS['current_screen']->base ?? '', [ 'edit', 'post' ], true );
	}

	/**
	 * Detects the static front page.
	 *
	 * @since 2.3.8
	 * @since 4.1.4 Added memoization.
	 *
	 * @param int $id the Page ID to check. If empty, the current ID will be fetched.
	 * @return bool True when homepage is static and given/current ID matches.
	 */
	public function is_static_frontpage( $id = 0 ) {

		$front_id = umemo( __METHOD__ )
			?? umemo(
				__METHOD__,
				'page' === \get_option( 'show_on_front' ) ? (int) \get_option( 'page_on_front' ) : false
			);

		return false !== $front_id && ( $id ?: $this->get_the_real_ID() ) === $front_id;
	}

	/**
	 * Detects tag archives.
	 *
	 * @since 2.6.0
	 * @uses $this->is_archive()
	 *
	 * @param mixed $tag Optional. Tag ID, name, slug, or array of Tag IDs, names, and slugs.
	 * @return bool
	 */
	public function is_tag( $tag = '' ) {

		// Admin requires another check.
		if ( \is_admin() )
			return $this->is_tag_admin();

		return $this->memo_query( null, $tag )
			?? $this->memo_query( \is_tag( $tag ), $tag );
	}

	/**
	 * Determines if the page is a tag within the admin screen.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 No longer guesses tag by name. It now only matches WordPress's built-in tag.
	 * @since 4.0.0 Removed caching.
	 *
	 * @return bool Post Type is tag.
	 */
	public function is_tag_admin() {
		return $this->is_archive_admin() && 'post_tag' === $this->get_current_taxonomy();
	}

	/**
	 * Detects taxonomy archives.
	 *
	 * @since 2.6.0
	 * @TODO add is_tax_admin() ?
	 *
	 * @param string|array     $taxonomy Optional. Taxonomy slug or slugs.
	 * @param int|string|array $term     Optional. Term ID, name, slug or array of Term IDs, names, and slugs.
	 * @return bool
	 */
	public function is_tax( $taxonomy = '', $term = '' ) {
		return $this->memo_query( null, $taxonomy, $term )
			?? $this->memo_query( \is_tax( $taxonomy, $term ), $taxonomy, $term );
	}

	/**
	 * Determines if the $post is a shop page.
	 *
	 * @since 4.0.5
	 * @since 4.1.4 Added memoization.
	 *
	 * @param int|WP_Post|null $post (Optional) Post ID or post object.
	 * @return bool
	 */
	public function is_shop( $post = null ) {
		return $this->memo_query( null, $post )
			?? $this->memo_query(
				/**
				 * @since 4.0.5
				 * @since 4.1.4 Now has its return value memoized.
				 * @param bool $is_shop Whether the post ID is a shop.
				 * @param int  $id      The current or supplied post ID.
				 */
				\apply_filters_ref_array( 'the_seo_framework_is_shop', [ false, $post ] ),
				$post
			);
	}

	/**
	 * Determines if the page is a product page.
	 *
	 * @since 4.0.5
	 * @since 4.1.4 Added memoization.
	 *
	 * @param int|WP_Post|null $post (Optional) Post ID or post object.
	 * @return bool True if on a WooCommerce Product page.
	 */
	public function is_product( $post = null ) {

		if ( \is_admin() )
			return $this->is_product_admin();

		return $this->memo_query( null, $post )
			?? $this->memo_query(
				/**
				 * @since 4.0.5
				 * @since 4.1.4 Now has its return value memoized.
				 * @param bool $is_product
				 * @param int|WP_Post|null $post (Optional) Post ID or post object.
				 */
				(bool) \apply_filters_ref_array( 'the_seo_framework_is_product', [ false, $post ] ),
				$post
			);
	}

	/**
	 * Determines if the admin page is for a product page.
	 *
	 * @since 4.0.5
	 * @since 4.1.4 Added memoization.
	 *
	 * @return bool
	 */
	public function is_product_admin() {
		return $this->memo_query()
			?? $this->memo_query(
				/**
				 * @since 4.0.5
				 * @since 4.1.4 Now has its return value memoized.
				 * @param bool $is_product_admin
				 */
				(bool) \apply_filters( 'the_seo_framework_is_product_admin', false )
			);
	}

	/**
	 * Detects year archives.
	 *
	 * @since 2.6.0
	 *
	 * @return bool
	 */
	public function is_year() {
		return \is_year();
	}

	/**
	 * Determines if SSL is used.
	 * Memoizes the return value.
	 *
	 * @since 2.8.0
	 *
	 * @return bool True if SSL, false otherwise.
	 */
	public function is_ssl() {
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
	 * @see $this->is_menu_page() for security notification.
	 *
	 * @param bool $secure Whether to ignore the use of the second (insecure) parameter.
	 * @return bool
	 */
	public function is_seo_settings_page( $secure = true ) {

		if ( ! \is_admin() )
			return false;

		if ( ! $secure )
			return $this->is_menu_page( $this->seo_settings_page_hook, $this->seo_settings_page_slug );

		return $this->memo_query()
			?? $this->memo_query( $this->is_menu_page( $this->seo_settings_page_hook ) );
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
	 * @global string $page_hook the current page hook.
	 *
	 * @param string $pagehook The menu pagehook to compare to.
	 *               To be used after `admin_init`.
	 * @param string $pageslug The menu page slug to compare to.
	 *               To be used before `admin_init`.
	 * @return bool true if screen match.
	 */
	public function is_menu_page( $pagehook = '', $pageslug = '' ) {
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
	 * @TODO Add better protection? This can get filled by users when is_paged() is true.
	 *       WordPress has no protection/test for this, either.
	 *
	 * @return int (R>0) $page Always a positive number.
	 */
	public function page() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition
		if ( null !== $memo = $this->memo_query() )
			return $memo;

		if ( $this->is_multipage() ) {
			$page = (int) \get_query_var( 'page' );

			if ( $page > $this->numpages() ) {
				// On overflow, WP returns the first page.
				$page = 1;
			}
		} else {
			$page = 1;
		}

		return $this->memo_query( $page ?: 1 );
	}

	/**
	 * Returns the current page number.
	 * Fetches global `$paged` from `WP_Query` to prevent conflicts.
	 *
	 * @since 2.6.0
	 * @since 3.2.4 1. Added overflow protection.
	 *              2. Now always returns 1 on the admin screens.
	 *
	 * @return int (R>0) $paged Always a positive number.
	 */
	public function paged() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition
		if ( null !== $memo = $this->memo_query() )
			return $memo;

		if ( $this->is_multipage() ) {
			$paged = (int) \get_query_var( 'paged' );
			$max   = $this->numpages();

			if ( $paged > $max ) {
				// On overflow, WP returns the last page.
				$paged = $max;
			}
		} else {
			$paged = 1;
		}

		return $this->memo_query( $paged ?: 1 );
	}

	/**
	 * Determines the number of available pages.
	 *
	 * This is largely taken from \WP_Query::setup_postdata(), however, the data
	 * we need is set up in the loop, not in the header; where TSF is active.
	 *
	 * @since 3.1.0
	 * @since 3.2.4 Now only returns "1" in the admin.
	 * @global \WP_Query $wp_query
	 *
	 * @return int
	 */
	public function numpages() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition
		if ( null !== $memo = $this->memo_query() )
			return $memo;

		if ( \is_admin() ) {
			// Disable pagination detection in admin: Always on page 1.
			return $this->memo_query( 1 );
		}

		global $wp_query;

		if ( $this->is_singular() && ! $this->is_singular_archive() )
			$post = \get_post( $this->get_the_real_ID() );

		if ( isset( $post ) && $post instanceof \WP_Post ) {
			$content = $post->post_content;
			if ( false !== strpos( $content, '<!--nextpage-->' ) ) {
				$content = str_replace( "\n<!--nextpage-->", '<!--nextpage-->', $content );

				// Ignore nextpage at the beginning of the content.
				if ( 0 === strpos( $content, '<!--nextpage-->' ) )
					$content = substr( $content, 15 );

				$_pages = explode( '<!--nextpage-->', $content );
			} else {
				$_pages = [ $content ];
			}

			/**
			 * Filter the "pages" derived from splitting the post content.
			 *
			 * "Pages" are determined by splitting the post content based on the presence
			 * of `<!-- nextpage -->` tags.
			 *
			 * @since 4.4.0 WordPress core
			 *
			 * @param array $_pages Array of "pages" derived from the post content.
			 *              of `<!-- nextpage -->` tags..
			 * @param WP_Post $post  Current post object.
			 */
			$_pages = \apply_filters( 'content_pagination', $_pages, $post );

			$numpages = \count( $_pages );
		} elseif ( isset( $wp_query->max_num_pages ) ) {
			$numpages = (int) $wp_query->max_num_pages;
		} else {
			// Empty or faulty query, bail.
			$numpages = 0;
		}

		return $this->memo_query( $numpages );
	}

	/**
	 * Determines whether the current loop has multiple pages.
	 *
	 * @since 2.7.0
	 * @since 3.1.0 1. Now also works on archives.
	 *              2. Now is public.
	 * @since 3.2.4 Now always returns false on the admin pages.
	 *
	 * @return bool True if multipage.
	 */
	public function is_multipage() {
		return $this->numpages() > 1;
	}

	/**
	 * Determines whether we're on The SEO Framework's sitemap or not.
	 * Memoizes the return value once set.
	 *
	 * @since 2.9.2
	 * @since 4.0.0 Now memoizes instead of populating class properties.
	 *
	 * @param bool $set Whether to set "doing sitemap".
	 * @return bool
	 */
	public function is_sitemap( $set = false ) {
		return memo( $set ?: null ) ?? false;
	}

	/**
	 * Determines whether we're on the robots.txt file output.
	 *
	 * @since 2.9.2
	 *
	 * @return bool
	 */
	public function is_robots() {
		return \is_robots();
	}

	/**
	 * Memoizes queries.
	 * Should not be used on methods that aren't final.
	 *
	 * The first parameter might not get retrieved in a later call, for this method
	 * also tests whether the query is setup correctly at the time of the call.
	 *
	 * @since 4.2.0
	 *
	 * @param mixed $value_to_set The value to set.
	 * @param mixed ...$args      Extra arguments, that are used to differentiaty queries.
	 * @return mixed $value_to_set when provided.
	 *               Otherwise, the previously sent $value_to_set.
	 *               When that's not set either, null.
	 */
	protected function memo_query( $value_to_set = null, ...$args ) {

		static $can_cache_query = null;

		// phpcs:ignore, WordPress.PHP.DevelopmentFunctions -- This is the only efficient way.
		$caller = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 2 )[1]['function'] ?? '';

		if ( null === $can_cache_query ) {
			if ( $this->can_cache_query( $caller ) ) {
				$can_cache_query = true;
			} else {
				return $value_to_set;
			}
		}

		return umemo( __METHOD__, $value_to_set, $caller, ...$args );
	}
}
