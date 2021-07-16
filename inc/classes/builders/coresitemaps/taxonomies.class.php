<?php
/**
 * @package The_SEO_Framework\Builders\Core_Sitemaps
 * @subpackage WordPress\Sitemaps
 */

namespace The_SEO_Framework\Builders\CoreSitemaps;

/**
 * The SEO Framework plugin
 * Copyright (C) 2020 - 2021 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

/**
 * Augments the WordPress Core 'taxonomies' sitemap.
 *
 * @since 4.1.2
 *
 * @access private
 */
class Taxonomies extends \WP_Sitemaps_Taxonomies {

	/**
	 * Gets a URL list for a taxonomy sitemap.
	 *
	 * @since 4.1.2
	 * @source \WP_Sitemaps_Taxonomies\get_url_list()
	 * @TEMP https://wordpress.slack.com/archives/CTKTGNJJW/p1604995479019700
	 * @link <https://core.trac.wordpress.org/ticket/51860>
	 *
	 * @param int    $page_num Page of results.
	 * @param string $taxonomy Optional. Taxonomy name. Default empty.
	 * @return array Array of URLs for a sitemap.
	 */
	public function get_url_list( $page_num, $taxonomy = '' ) {

		$supported_types = $this->get_object_subtypes();

		// Bail early if the queried taxonomy is not supported.
		if ( ! isset( $supported_types[ $taxonomy ] ) ) {
			return [];
		}

		/**
		 * Filters the taxonomies URL list before it is generated.
		 *
		 * Passing a non-null value will effectively short-circuit the generation,
		 * returning that value instead.
		 *
		 * @since WP Core 5.5.0
		 *
		 * @param array  $url_list The URL list. Default null.
		 * @param string $taxonomy Taxonomy name.
		 * @param int    $page_num Page of results.
		 */
		$url_list = \apply_filters(
			'wp_sitemaps_taxonomies_pre_url_list',
			null,
			$taxonomy,
			$page_num
		);

		if ( null !== $url_list ) {
			return $url_list;
		}

		$url_list = [];

		$main = Main::get_instance();

		// Offset by how many terms should be included in previous pages.
		$offset = ( $page_num - 1 ) * \wp_sitemaps_get_max_urls( $this->object_type );

		$args           = $this->get_taxonomies_query_args( $taxonomy );
		$args['offset'] = $offset;

		$taxonomy_terms = new \WP_Term_Query( $args );

		if ( ! empty( $taxonomy_terms->terms ) ) {
			foreach ( $taxonomy_terms->terms as $term ) {
				/**
				 * @augmented This if-statement prevents including the term in the sitemap when conditions apply.
				 */
				if ( ! $main->is_term_included_in_sitemap( $term, $taxonomy ) )
					continue;

				$term_link = \get_term_link( $term, $taxonomy );

				if ( \is_wp_error( $term_link ) ) {
					continue;
				}

				$sitemap_entry = [
					'loc' => $term_link,
				];

				/**
				 * Filters the sitemap entry for an individual term.
				 *
				 * @since WP Core 5.5.0
				 *
				 * @param array   $sitemap_entry Sitemap entry for the term.
				 * @param WP_Term $term          Term object.
				 * @param string  $taxonomy      Taxonomy name.
				 */
				$sitemap_entry = \apply_filters( 'wp_sitemaps_taxonomies_entry', $sitemap_entry, $term, $taxonomy );
				$url_list[]    = $sitemap_entry;
			}
		}

		return $url_list;
	}
}
