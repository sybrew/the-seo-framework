<?php
/**
 * @package The_SEO_Framework\Classes\Data\Blog
 * @subpackage The_SEO_Framework\Data
 */

namespace The_SEO_Framework\Data;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\memo;

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
 * @internal
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
		return memo()
			?? memo( \tsf()->get_option( 'site_title' ) ?: static::get_filtered_blog_name() );
	}

	/**
	 * Fetches blogname (site title).
	 * We use get_bloginfo( ..., 'display' ), even though it escapes needlessly, because it applies filters.
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
	 * We use get_bloginfo( ..., 'display' ), even though it escapes needlessly, because it applies filters.
	 *
	 * @since 4.3.0
	 *
	 * @return string $blogname The sanitized blog description.
	 */
	public static function get_filtered_blog_description() {
		return trim( \get_bloginfo( 'description', 'display' ) );
	}
}
