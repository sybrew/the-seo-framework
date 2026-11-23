<?php
/**
 * @package The_SEO_Framework\Classes\Helper\Taxonomy\Base_URL
 * @subpackage The_SEO_Framework\Permalinks
 */

namespace The_SEO_Framework\Helper\Taxonomy;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use The_SEO_Framework\Helper\{
	Query,
	Taxonomy,
};

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
 * Determines which taxonomy URL bases can be removed and shortens their permalink structures.
 *
 * @since 5.2.2
 * @access protected
 *         Use tsf()->taxonomy()->base_url() instead.
 */
class Base_URL {

	/**
	 * Gets public hierarchical taxonomies with viewable rewrite URLs.
	 *
	 * Taxonomy names determine fallback priority, independently of registration
	 * order, display labels, and the order of the saved settings.
	 *
	 * @since 5.2.2
	 *
	 * @return string[] Eligible taxonomy names, sorted by byte order.
	 */
	public static function get_taxonomies() {

		$taxonomies = [];

		foreach ( \get_taxonomies( [], 'objects' ) as $taxonomy )
			if ( self::supports_base_removal( $taxonomy ) )
				$taxonomies[] = $taxonomy->name;

		sort( $taxonomies, \SORT_STRING );

		return $taxonomies;
	}

	/**
	 * Checks whether a registered taxonomy supports base removal.
	 *
	 * @since 5.2.2
	 *
	 * @param string|\WP_Taxonomy|false $taxonomy The taxonomy name, registered taxonomy, or false if unknown.
	 * @return bool
	 */
	public static function supports_base_removal( $taxonomy ) {

		if ( \is_string( $taxonomy ) )
			$taxonomy = \get_taxonomy( $taxonomy );

		return $taxonomy instanceof \WP_Taxonomy
			&& $taxonomy->public
			&& $taxonomy->hierarchical
			&& $taxonomy->rewrite
			&& \is_taxonomy_viewable( $taxonomy );
	}

	/**
	 * Checks whether base removal applies to the named taxonomy.
	 *
	 * Requires pretty permalinks, an enabled setting, and an eligible taxonomy.
	 *
	 * @since 5.2.2
	 *
	 * @param string $taxonomy The taxonomy name.
	 * @return bool
	 */
	public static function should_remove_taxonomy_base( $taxonomy ) {
		return Taxonomy::is_base_removal_enabled( $taxonomy )
			&& Query\Utils::using_pretty_permalinks()
			&& self::supports_base_removal( $taxonomy );
	}

	/**
	 * Removes the registered base while retaining the permalink front and root.
	 *
	 * The caller determines whether base removal applies.
	 *
	 * @since 5.2.2
	 *
	 * @param string $structure The taxonomy permalink structure.
	 * @param string $taxonomy  The taxonomy name.
	 * @return string
	 */
	public static function strip_taxonomy_base( $structure, $taxonomy ) {

		// The registered base and rewrite tag form the tail, e.g. book-category/%book_category%.
		$base   = trim(
			\get_taxonomy( $taxonomy )->rewrite['slug'] ?? '',
			'/',
		);
		$suffix = "$base/%$taxonomy%";

		if ( ! $base || ! str_ends_with( $structure, $suffix ) )
			return $structure;

		// Cutting that tail keeps the permalink front and root, e.g. /index.php/library/.
		$prefix = substr(
			$structure,
			0,
			-\strlen( $suffix ),
		);

		// Cut at a path boundary so book-category does not match rare-book-category.
		if ( '' !== $prefix && ! str_ends_with( $prefix, '/' ) )
			return $structure;

		// Permalink Settings can store /blog/ in the multisite category base to avoid subsite path conflicts.
		// Keep the main-site prefix when removing the rest of the base.
		if (
			   'category' === $taxonomy
			&& ( 'blog' === $base || str_starts_with( $base, 'blog/' ) )
			&& \is_multisite()
			&& ! \is_subdomain_install()
			&& \is_main_site()
			&& str_starts_with(
				\get_option( 'permalink_structure' ),
				'/blog/',
			)
		) {
			$prefix .= 'blog/';
		}

		return $prefix . "%$taxonomy%";
	}
}
