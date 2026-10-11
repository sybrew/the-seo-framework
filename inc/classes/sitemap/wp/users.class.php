<?php
/**
 * @package The_SEO_Framework\Classes\Sitemap\WP\Users
 * @subpackage WordPress\Sitemaps
 */

namespace The_SEO_Framework\Sitemap\WP;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use The_SEO_Framework\Sitemap;

/**
 * The SEO Framework plugin
 * Copyright (C) 2026 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Augments the WordPress Core 'users' sitemap.
 *
 * @since 5.2.0
 *
 * @access private
 */
class Users extends \WP_Sitemaps_Users {

	/**
	 * Gets a URL list for a user sitemap.
	 *
	 * @since 5.2.0
	 * @source \WP_Sitemaps_Users\get_url_list()
	 *
	 * @param int    $page_num       Page of results.
	 * @param string $object_subtype Optional. Not applicable for Users but
	 *                               required for compatibility with the parent
	 *                               provider class. Default empty.
	 * @return array[] Array of URL information for a sitemap.
	 */
	public function get_url_list( $page_num, $object_subtype = '' ) {

		/**
		 * Filters the users URL list before it is generated.
		 *
		 * Returning a non-null value will effectively short-circuit the generation,
		 * returning that value instead.
		 *
		 * @since WP Core 5.5.0
		 *
		 * @param array[]|null $url_list The URL list. Default null.
		 * @param int          $page_num Page of results.
		 */
		$url_list = \apply_filters(
			'wp_sitemaps_users_pre_url_list',
			null,
			$page_num,
		);

		if ( null !== $url_list )
			return $url_list;

		$args          = $this->get_users_query_args();
		$args['paged'] = $page_num;

		$query    = new \WP_User_Query( $args );
		$users    = $query->get_results();
		$url_list = [];

		foreach ( $users as $user ) {
			/**
			 * @augmented This if-statement prevents including the user in the sitemap when conditions apply.
			 */
			if ( ! Sitemap\Utils::is_user_included_in_sitemap( $user->ID ) )
				continue;

			/**
			 * Filters the sitemap entry for an individual user.
			 *
			 * @since WP Core 5.5.0
			 *
			 * @param array   $sitemap_entry Sitemap entry for the user.
			 * @param WP_User $user          User object.
			 */
			$url_list[] = \apply_filters(
				'wp_sitemaps_users_entry',
				[ 'loc' => \get_author_posts_url( $user->ID ) ],
				$user,
			);
		}

		return $url_list;
	}
}
