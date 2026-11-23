<?php
/**
 * @package The_SEO_Framework\Classes\Helper\Taxonomy\Base_URL\Conflicts
 * @subpackage The_SEO_Framework\Permalinks
 */

namespace The_SEO_Framework\Helper\Taxonomy\Base_URL;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use The_SEO_Framework\{
	Data,
	Meta,
};
use The_SEO_Framework\Helper\{
	Query,
	Taxonomy\Base_URL,
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
 * Finds native and shortened routes that take precedence over a term URL.
 *
 * @since 5.2.2
 * @access private
 */
final class Conflicts {

	/**
	 * Finds what takes precedence over each shortened term URL in a batch.
	 *
	 * The caller supplies terms eligible for base removal. Native routes take
	 * precedence. Unresolved paths need a same-slug term lookup only when
	 * an earlier taxonomy can claim them.
	 * Routes handled solely by server configuration or request hooks are
	 * outside this check.
	 *
	 * @since 5.2.2
	 *
	 * @example $conflicts `[ 42 => 69, 420 => null ]`
	 *
	 * @param \WP_Term[] $terms The terms whose shortened URLs to check.
	 * @return array The conflicts, indexed by term ID.
	 *         @type int|string|\WP_Term|null {$term_id} The post ID, archive post type, or competing term, if any.
	 */
	public static function get_term_url_conflicts( $terms ) {

		$conflicts = [];
		$urls      = [];
		$slugs     = [];

		// Resolve each unique term's link once and collect slugs for the post-name lookup.
		foreach ( $terms as $term ) {
			$id = $term->term_id;

			if ( \array_key_exists( $id, $conflicts ) ) continue;

			$conflicts[ $id ] = null;

			$url = \get_term_link( $term );

			if ( \is_wp_error( $url ) || ! $url ) continue;

			$urls[ $id ]          = [ $term, $url ];
			$slugs[ $term->slug ] = true;
		}

		if ( ! $urls )
			return $conflicts;

		global $wpdb;

		$unique_slugs = array_keys( $slugs );
		$placeholders = implode(
			', ',
			array_fill( 0, \count( $unique_slugs ), '%s' ),
		);

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $placeholders are hardcoded %s.
		$post_names = $wpdb->get_col( $wpdb->prepare(
			"SELECT post_name
			FROM $wpdb->posts
			WHERE post_name IN ($placeholders)", // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders
			...$unique_slugs,
		) );
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$post_slugs      = array_fill_keys( $post_names, true );
		$unresolved      = [];
		$remaining_slugs = [];
		$last_taxonomy   = '';

		// Check native routes in precedence order and queue unresolved terms for cross-taxonomy matching.
		foreach ( $urls as $id => [ $term, $url ] ) {
			$path = self::get_comparable_url( $url );

			// A numeric URL can use a post ID without sharing a post slug.
			if (
				   isset( $post_slugs[ $term->slug ] )
				|| ctype_digit( $term->slug )
			) {
				$post_id = self::get_native_post_id( $url, $path );

				if ( $post_id ) {
					$conflicts[ $id ] = $post_id;
					continue;
				}
			}

			$archive_paths ??= self::get_post_type_archive_paths();

			if ( isset( $archive_paths[ $path ] ) ) {
				$conflicts[ $id ] = $archive_paths[ $path ];
				continue;
			}

			// A registered rewrite can resolve to a term with a different slug.
			$native_term = self::get_term_from_rewrite_rules( $url );

			if ( $native_term ) {
				$conflicts[ $id ] = $native_term->term_id === $term->term_id ? null : $native_term;
				continue;
			}

			$unresolved[ $id ]              = [ $term, $path ];
			$remaining_slugs[ $term->slug ] = true;

			// Track the latest taxonomy in routing order while skipping repeated comparisons on the same taxonomy.
			if (
				   $term->taxonomy !== $last_taxonomy
				&& strcmp( $term->taxonomy, $last_taxonomy ) > 0
			) {
				$last_taxonomy = $term->taxonomy;
			}
		}

		if ( ! $unresolved )
			return $conflicts;

		$taxonomies = [];

		// Collect enabled taxonomies that sort before the last unresolved term's taxonomy.
		foreach ( array_keys( Data\Plugin::get_option( 'remove_taxonomy_bases' ) ) as $name ) {
			// Skip this taxonomy if it sorts at or after the last unresolved taxonomy.
			if ( strcmp( $name, $last_taxonomy ) >= 0 ) continue;

			$taxonomy = \get_taxonomy( (string) $name );

			if ( Base_URL::supports_base_removal( $taxonomy ) )
				$taxonomies[] = $taxonomy->name;
		}

		if ( ! $taxonomies )
			return $conflicts;

		sort( $taxonomies, \SORT_STRING );

		$lookup_slugs = array_keys( $remaining_slugs );
		$candidates   = [];

		// Query each preceding taxonomy separately so multilingual plugins do not filter untranslated taxonomies.
		foreach ( $taxonomies as $taxonomy ) {
			$matching_terms = \get_terms( [
				'taxonomy'               => $taxonomy,
				'slug'                   => $lookup_slugs,
				'hide_empty'             => false,
				'update_term_meta_cache' => false,
			] );

			if ( ! $matching_terms || \is_wp_error( $matching_terms ) )
				continue;

			// Group competing terms by slug in taxonomy byte order for conflict matching.
			foreach ( $matching_terms as $candidate )
				$candidates[ $candidate->slug ][] = $candidate;
		}

		if ( ! $candidates )
			return $conflicts;

		// Match each unresolved term against preceding shortened terms that share its slug.
		foreach ( $unresolved as $id => [ $term, $path ] )
			if ( isset( $candidates[ $term->slug ] ) )
				$conflicts[ $id ] = self::get_shortened_term_conflict(
					$term,
					$path,
					$candidates[ $term->slug ],
				);

		return $conflicts;
	}

	/**
	 * Gets the relative URL used to compare competing routes, including its query and fragment.
	 *
	 * @since 5.2.2
	 *
	 * @param string $url The URL to compare.
	 * @return string The relative URL without trailing slashes.
	 */
	private static function get_comparable_url( $url ) {
		return rtrim(
			Meta\URI\Utils::get_relative_part_from_url( $url ),
			'/\\',
		);
	}

	/**
	 * Finds the publicly viewable post or page that owns a shortened term URL.
	 *
	 * @since 5.2.2
	 *
	 * @param string $url  The shortened term URL.
	 * @param string $path The relative URL without a trailing slash.
	 * @return int The post or page ID, or zero if none matches.
	 */
	private static function get_native_post_id( $url, $path ) {

		$post_id = \url_to_postid( $url );

		// The posts page is an archive and may have no singular match.
		if ( ! $post_id ) {
			$page_id = \get_option( 'page_for_posts' );

			if (
				   $page_id
				&& Query\Utils::has_page_on_front()
				&& self::get_comparable_url( \get_permalink( $page_id ) ) === $path
			) {
				$post_id = $page_id;
			}
		}

		return $post_id && \is_post_publicly_viewable( $post_id ) ? (int) $post_id : 0;
	}

	/**
	 * Gets public post type archive paths for the current batch.
	 *
	 * @since 5.2.2
	 *
	 * @return string[] Post type names indexed by archive path.
	 */
	private static function get_post_type_archive_paths() {

		$paths = [];

		// Map each public post type archive's comparable path to its first registered post type.
		foreach ( \get_post_types( [ 'public' => true ] ) as $post_type ) {
			$archive_url = \get_post_type_archive_link( $post_type );

			if ( ! $archive_url ) continue;

			$path = self::get_comparable_url( $archive_url );

			// The first registered archive owns a shared path.
			$paths[ $path ] ??= $post_type;
		}

		return $paths;
	}

	/**
	 * Finds the term targeted by the first registered taxonomy rewrite for a URL.
	 *
	 * Used by the SEO Bar after checking posts and post type archives. Reads
	 * the stored rules without running a post query or regenerating rewrites.
	 *
	 * @since 5.2.2
	 *
	 * @param string $url The generated archive URL to check.
	 * @return ?\WP_Term The native term, if a rewrite targets an existing public term.
	 */
	private static function get_term_from_rewrite_rules( $url ) {

		$rules = self::get_native_term_rules();

		if ( ! $rules ) return;

		/**
		 * Filters the home URL used to interpret stored term rewrite rules.
		 *
		 * The path in this URL is removed before matching. The default is the
		 * stored home URL so prefixes encoded in the rules remain in the request.
		 *
		 * @since 5.2.2
		 *
		 * @param string $home The stored home URL.
		 * @param string $url  The term URL being checked.
		 */
		$home = \apply_filters(
			'the_seo_framework_get_term_home_url',
			\get_option( 'home' ),
			$url,
		);

		$home_path = rtrim(
			(string) parse_url( $home, \PHP_URL_PATH ),
			'/',
		);
		$url_path  = (string) parse_url( $url, \PHP_URL_PATH );

		if (
			   $home_path
			&& ! str_starts_with( $url_path, "$home_path/" )
		) {
			return;
		}

		$request = trim(
			substr( $url_path, \strlen( $home_path ) ),
			'/',
		);
		$decoded = urldecode( $request );

		// Stop at the first stored rewrite rule matching the path, mirroring WordPress routing.
		foreach ( $rules as $pattern => $rewrite_query ) {
			if (
				   ! preg_match( $pattern, $request, $matches )
				&& ( $request === $decoded || ! preg_match( $pattern, $decoded, $matches ) )
			) {
				continue;
			}

			$variables = Rewrite::parse_rewrite_query( $rewrite_query, $matches );

			// A post permalink can use its category as a path component.
			if ( self::is_singular_rewrite( $variables ) ) return;

			// Core normalizes legacy IDs and hierarchical query vars in this step.
			$query     = new \WP_Query;
			$variables = $query->fill_query_vars( $variables );
			$query->parse_tax_query( $variables );

			// Resolve the first public, viewable taxonomy term targeted by the matched rule.
			foreach ( $query->tax_query->queried_terms as $taxonomy => $target ) {
				if ( empty( $target['field'] ) || empty( $target['terms'] ) )
					continue;

				$registration = \get_taxonomy( $taxonomy );

				// Language constraints are sometimes represented as private taxonomies.
				if (
					   empty( $registration->public )
					|| ! \is_taxonomy_viewable( $registration )
				) {
					continue;
				}

				return \get_term_by(
					$target['field'],
					reset( $target['terms'] ),
					$taxonomy,
				) ?: null;
			}

			return;
		}
	}

	/**
	 * Determines whether an expanded rewrite targets a single post, page, or attachment.
	 *
	 * @since 5.2.2
	 * @see \WP::parse_request() Maps viewable post type query variables to a post name.
	 *
	 * @param array $variables The expanded rewrite query variables.
	 * @return bool
	 */
	private static function is_singular_rewrite( $variables ) {

		foreach ( [ 'name', 'pagename', 'attachment', 'subpost' ] as $var )
			if ( '' !== ( $variables[ $var ] ?? '' ) )
				return true;

		foreach ( [ 'p', 'page_id', 'attachment_id', 'subpost_id' ] as $var )
			if ( (int) ( $variables[ $var ] ?? 0 ) )
				return true;

		static $post_type_vars = [];

		$site = \get_current_blog_id();

		if ( ! isset( $post_type_vars[ $site ] ) ) {
			$post_type_vars[ $site ] = [];

			// Cache viewable post type query variables once per site to avoid rescanning per rewrite match.
			foreach ( \get_post_types( [], 'objects' ) as $post_type )
				if ( $post_type->query_var && \is_post_type_viewable( $post_type ) )
					$post_type_vars[ $site ][] = $post_type->query_var;
		}

		foreach ( $post_type_vars[ $site ] as $var )
			if ( ! empty( $variables[ $var ] ) )
				return true;

		return false;
	}

	/**
	 * Gets the stored rewrite rules that can target a term, in WordPress order.
	 *
	 * @since 5.2.2
	 *
	 * @return string[] Rewrite query strings indexed by their regular expressions.
	 */
	private static function get_native_term_rules() {

		static $by_site = [];

		$site = \get_current_blog_id();

		if ( isset( $by_site[ $site ] ) )
			return $by_site[ $site ];

		// Include Core's generic taxonomy variables and legacy category and tag IDs.
		$term_vars = array_fill_keys(
			[ 'taxonomy', 'cat', 'tag_id', 'tag' ],
			true,
		);

		foreach ( \get_taxonomies( [ 'public' => true ], 'objects' ) as $taxonomy )
			if ( $taxonomy->query_var && \is_taxonomy_viewable( $taxonomy ) )
				$term_vars[ $taxonomy->query_var ] = true;

		$rules = [];

		// Retain stored rewrite rules that reference at least one public taxonomy variable.
		foreach ( (array) \get_option( 'rewrite_rules' ) as $regex => $query ) {
			parse_str(
				preg_replace( '/^.+\?/', '', $query ),
				$variables,
			);

			if ( array_intersect_key( $variables, $term_vars ) )
				$rules[ Rewrite::get_rewrite_pattern( $regex ) ] = $query;
		}

		return $by_site[ $site ] = $rules;
	}

	/**
	 * Finds a term whose shortened URL takes precedence over the given term's.
	 *
	 * @since 5.2.2
	 *
	 * @param \WP_Term   $term       The term being checked.
	 * @param string     $path       The relative URL without a trailing slash.
	 * @param \WP_Term[] $candidates Terms with the same slug, sorted by taxonomy name.
	 * @return ?\WP_Term The first matching term from a preceding taxonomy.
	 */
	private static function get_shortened_term_conflict( $term, $path, $candidates ) {

		// Return the first preceding taxonomy's term whose shortened URL matches this path.
		foreach ( $candidates as $candidate ) {
			// Stop if the candidate sorts at or after the term's own taxonomy.
			if ( strcmp( $candidate->taxonomy, $term->taxonomy ) >= 0 ) break;

			$url = \get_term_link( $candidate );

			if (
				   ! \is_wp_error( $url )
				&& self::get_comparable_url( $url ) === $path
			) {
				return $candidate;
			}
		}
	}
}
