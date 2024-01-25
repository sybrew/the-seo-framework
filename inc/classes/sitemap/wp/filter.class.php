<?php
/**
 * @package The_SEO_Framework\Classes\Sitemap\WP\Filter
 * @subpackage WordPress\Sitemaps
 */

namespace The_SEO_Framework\Sitemap\WP;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\{
	Data,
	Helper\Query,
	Sitemap,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2020 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Augments the WordPress Core sitemap.
 *
 * @since 4.1.2
 * @since 5.0.0 1. Renamed from `Main`.
 *              2. Moved from ``The_SEO_Framework\Builders\CoreSitemaps\Main`.
 *              3. No longer extends `\The_SEO_Framework\Sitemap\Optimized\Main`.
 * @access private
 */
class Filter {

	/**
	 * Sets "doing sitemap" in TSF if preliminary conditions pass.
	 * We do this via a filter, which is unconventional but a bypass.
	 *
	 * @hook wp_sitemaps_posts_query_args 11
	 * @link <https://core.trac.wordpress.org/ticket/56954>
	 * @since 4.2.7
	 * @since 5.0.0 Renamed from `_trick_filter_doing_sitemap`.
	 * @access private
	 * @global \WP_Query $wp_query We test against the main query here.
	 *
	 * @param array $args Array of proposed WP_Query arguments.
	 * @return array $args The WP_Query arguments, unaltered.
	 */
	public static function trick_filter_doing_sitemap( $args ) {
		global $wp_query;

		// If doing Core sitemaps, verify if is actual sitemap, and block if so.
		if ( isset( $wp_query->query_vars['sitemap'] ) ) {
			// Didn't we request a simple API function for this? Anyway, null safe operators would also be nice here.
			// For now, let's assume this API won't change. Test periodically.
			if ( \wp_sitemaps_get_server()->registry->get_provider( $wp_query->query_vars['sitemap'] ) )
				Query::is_sitemap( true );
		}

		return $args;
	}

	/**
	 * Filters Core sitemap provider.
	 *
	 * @hook wp_sitemaps_add_provider 9
	 * @since 4.1.2
	 * @since 5.0.0 Renamed from `_filter_add_provider`.
	 * @access private
	 *
	 * @param \WP_Sitemaps_Provider $provider Instance of a \WP_Sitemaps_Provider.
	 * @param string                $name     Name of the sitemap provider.
	 * @return \WP_Sitemaps_Provider|null The original or augmented instance of a \WP_Sitemaps_Provider.
	 *                                    null if the provider is disabled.
	 */
	public static function filter_add_provider( $provider, $name ) {

		if ( ! $provider instanceof \WP_Sitemaps_Provider )
			return $provider;

		switch ( $name ) {
			case 'posts':
				$provider = new Posts;
				break;
			case 'taxonomies':
				$provider = new Taxonomies;
				break;
			case 'users':
				// This option is not reversible through means other than filters.
				if ( Data\Plugin::get_option( 'author_noindex' ) )
					$provider = null;
		}

		return $provider;
	}

	/**
	 * Filters Core sitemap query limit.
	 *
	 * @hook wp_sitemaps_max_urls 9
	 * @since 4.1.2
	 * @since 5.0.0 Renamed from `_filter_max_urls`.
	 * @access private
	 *
	 * @return string The sitemap query limit.
	 */
	public static function filter_max_urls() {
		return Sitemap\Utils::get_sitemap_post_limit();
	}
}
