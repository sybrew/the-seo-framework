<?php
/**
 * @package The_SEO_Framework\Builders\Core_Sitemaps
 * @subpackage WordPress\Sitemaps
 */

namespace The_SEO_Framework\Builders\CoreSitemaps;

/**
 * The SEO Framework plugin
 * Copyright (C) 2020 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
	 * @since 4.2.0 Renamed `$taxonomy` to `$object_subtype` to match parent class
	 *              for PHP 8 named parameter support. (Backport WP 5.9)
	 * @since 4.2.5 Added 'all' fields to the query, allowing caching of terms (Backport WP 6.0).
	 * @source \WP_Sitemaps_Taxonomies\get_url_list()
	 * @TEMP https://wordpress.slack.com/archives/CTKTGNJJW/p1604995479019700
	 * @link <https://core.trac.wordpress.org/ticket/51860>
	 * @link <https://core.trac.wordpress.org/changeset/51787>
	 *
	 * @param int    $page_num       Page of results.
	 * @param string $object_subtype Optional. Taxonomy name. Default empty.
	 * @return array Array of URLs for a sitemap.
	 */
	public function get_url_list( $page_num, $object_subtype = '' ) {
		// Restores the more descriptive, specific name for use within this method.
		$taxonomy        = $object_subtype;
		$supported_types = $this->get_object_subtypes();

		// Bail early if the queried taxonomy is not supported.
		if ( ! isset( $supported_types[ $taxonomy ] ) )
			return [];

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

		if ( null !== $url_list )
			return $url_list;

		$url_list = [];

		// Offset by how many terms should be included in previous pages.
		$offset = ( $page_num - 1 ) * \wp_sitemaps_get_max_urls( $this->object_type );

		$args           = $this->get_taxonomies_query_args( $taxonomy );
		$args['fields'] = 'all'; // On WP<6.0 this is 'ids'; overwrite it. This line is a mirror of WPv6.0, too.
		$args['offset'] = $offset;

		$taxonomy_terms = new \WP_Term_Query( $args );

		$main = Main::get_instance();

		foreach ( $taxonomy_terms->terms ?? [] as $term ) :
			/**
			 * @augmented This if-statement prevents including the term in the sitemap when conditions apply.
			 */
			if ( ! $main->is_term_included_in_sitemap( $term->term_id, $taxonomy ) )
				continue;

			$term_link = \get_term_link( $term, $taxonomy );

			if ( \is_wp_error( $term_link ) )
				continue;

			$sitemap_entry = [
				'loc' => $term_link,
			];

			/**
			 * Filters the sitemap entry for an individual term.
			 *
			 * @since WP Core 5.5.0
			 * @since WP Core 6.0.0 Added `$term` argument containing the term object.
			 *
			 * @param array   $sitemap_entry Sitemap entry for the term.
			 * @param int     $term_id       Term ID.
			 * @param string  $taxonomy      Taxonomy name.
			 * @param WP_Term $term          Term object.
			 */
			$sitemap_entry = \apply_filters( 'wp_sitemaps_taxonomies_entry', $sitemap_entry, $term->term_id, $taxonomy, $term );
			$url_list[]    = $sitemap_entry;
		endforeach;

		return $url_list;
	}
}
