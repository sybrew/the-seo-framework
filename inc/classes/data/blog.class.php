<?php
/**
 * @package The_SEO_Framework\Classes\Data\Blog
 * @subpackage The_SEO_Framework\Data
 */

namespace The_SEO_Framework\Data;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\{
	memo,
	umemo,
};

use \The_SEO_Framework\Data;

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
 * Holds a collection of data helper methods for the blog.
 *
 * @since 4.3.0
 * @access protected
 * @internal Use tsf()->data()->blog() instead.
 */
class Blog {

	/**
	 * Fetches public blogname (site title).
	 * Memoizes the return value.
	 *
	 * Do not consider this function safe for printing!
	 *
	 * @since 4.3.0
	 *
	 * @return string $blogname The sanitized blogname.
	 */
	public static function get_public_blog_name() {
		return umemo( __METHOD__ )
			?? umemo(
				__METHOD__,
				Data\Plugin::get_option( 'site_title' ) ?: static::get_filtered_blog_name()
			);
	}

	/**
	 * Fetches blogname (site title).
	 * We use get_bloginfo( ..., 'display' ) because it applies filters.
	 *
	 * @since 4.3.0
	 *
	 * @return string $blogname The sanitized blogname.
	 */
	public static function get_filtered_blog_name() {
		/**
		 * @since 4.2.0
		 * @param string The blog name.
		 */
		return (string) \apply_filters(
			'the_seo_framework_blog_name',
			trim( \get_bloginfo( 'name', 'display' ) )
		);
	}

	/**
	 * Fetch blog description.
	 * We use get_bloginfo( ..., 'display' ) because it applies filters.
	 *
	 * @since 4.3.0
	 *
	 * @return string $blogname The sanitized blog description.
	 */
	public static function get_filtered_blog_description() {
		return trim( \get_bloginfo( 'description', 'display' ) );
	}

	/**
	 * Returns the home URL. Created for the WordPress method is slow for it
	 * performs "set_url_scheme" calls slowly. We rely on this method for some
	 * plugins filter `home_url`.
	 * Memoized.
	 *
	 * @since 4.2.0
	 * @since 4.3.0 Moved to \The_SEO_Framework\Data\Blog
	 *
	 * @return string The home URL.
	 */
	public static function get_front_page_url() {
		return umemo( __METHOD__ ) ?? umemo( __METHOD__, \get_home_url() );
	}

	/**
	 * Returns the determined site language in IETF BCP 47 format.
	 * WordPress's get_bloginfo( 'language' ) is slow; so, we memoize it here.
	 * We do NOT use get_bloginfo( ..., 'display' ) since nothing in Core does.
	 *
	 * @since 4.3.0
	 * @see https://www.w3.org/International/articles/language-tags/
	 *
	 * @return string $blogname The sanitized blogname.
	 */
	public static function get_language() {
		return umemo( __METHOD__ ) ?? umemo( __METHOD__, \get_bloginfo( 'language' ) );
	}

	/**
	 * Checks if blog is public through WordPress core settings.
	 * Memoizes the return value.
	 *
	 * @since 2.6.0
	 * @since 4.0.5 Can now test for non-sanitized 'blog_public' option states.
	 * @since 4.3.0 Moved to \The_SEO_Framework\Data\Blog
	 *
	 * @return bool True is blog is public.
	 */
	public static function is_blog_public() {
		return memo() ?? memo( (bool) \get_option( 'blog_public' ) );
	}

	/**
	 * Whether the current blog is spam or deleted.
	 * Multisite Only.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Now uses get_site()
	 * @since 3.1.1 Now checks for `is_multisite()`, to prevent a crash with Divi's compatibility injection.
	 * @since 4.3.0 Moved to \The_SEO_Framework\Data\Blog
	 *
	 * @return bool Current blog is spam.
	 */
	public static function is_current_blog_spam_or_deleted() {

		if ( ! \function_exists( '\\get_site' ) || ! \is_multisite() )
			return false;

		$site = \get_site();

		if ( $site instanceof \WP_Site && ( '1' === $site->spam || '1' === $site->deleted ) )
			return true;

		return false;
	}

	/**
	 * Determines if the current installation is on a subdirectory.
	 * Memoizes the return value.
	 *
	 * @since 2.9.0
	 * @since 4.3.0 Moved to \The_SEO_Framework\Data\Blog
	 *
	 * @return bool
	 */
	public static function is_subdirectory_installation() {
		return memo() ?? memo(
			(bool) \strlen(
				ltrim(
					parse_url(
						\get_option( 'home' ),
						\PHP_URL_PATH
					) ?? '',
					' \\/'
				)
			)
		);
	}
}
