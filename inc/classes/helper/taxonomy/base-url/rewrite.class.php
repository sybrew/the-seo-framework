<?php
/**
 * @package The_SEO_Framework\Classes\Helper\Taxonomy\Base_URL\Rewrite
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
 * Shortens selected taxonomy links and resolves unclaimed archive paths.
 *
 * Native routes retain precedence. Shortened archives are tested in taxonomy
 * name order, without changing their generated links when a conflict exists.
 * Only the requested path is tested; a conflict at an archive's root does not
 * disable its other paths. Original archive URLs remain available.
 *
 * A fallback runs the main query once more before status handling. Plugins
 * which claim routes after this point require their own integration.
 *
 * @since 5.2.2
 * @access private
 */
final class Rewrite {

	/**
	 * Removes the registered base from a completed term URL.
	 *
	 * Runs after WPML caches its native term URLs, so its cached links remain
	 * independent of this setting. The full term path anchors the replacement
	 * and preserves installation and language directories with the same name.
	 *
	 * @hook term_link 20
	 * @since 5.2.2
	 *
	 * @param string   $url  The term URL.
	 * @param \WP_Term $term The linked term.
	 * @return string
	 */
	public static function filter_term_link( $url, $term ) {

		$taxonomy = $term->taxonomy;

		if ( ! Base_URL::should_remove_taxonomy_base( $taxonomy ) )
			return $url;

		global $wp_rewrite;

		$structure = $wp_rewrite->get_extra_permastruct( $taxonomy );

		if ( ! $structure )
			return $url;

		$shortened = Base_URL::strip_taxonomy_base( $structure, $taxonomy );

		if ( $structure === $shortened )
			return $url;

		$path = Meta\URI\Utils::get_term_slug_path( $term );

		if ( '' === $path )
			return $url;

		$token = "%$taxonomy%";

		// Core can register the structure without a leading slash when with_front is false.
		$structure = ltrim(
			\user_trailingslashit(
				str_replace( $token, $path, $structure ),
				'category',
			),
			'/',
		);
		$url_path  = parse_url( $url, \PHP_URL_PATH );

		if ( ! str_ends_with( "/$url_path", "/$structure" ) )
			return $url;

		$shortened = ltrim(
			\user_trailingslashit(
				str_replace( $token, $path, $shortened ),
				'category',
			),
			'/',
		);

		// Replace only the matched path tail, leaving the original URL encoding, query and fragment intact.
		$length = \strlen( $structure );

		return substr_replace(
			$url,
			$shortened,
			strcspn( $url, '?#' ) - $length,
			$length,
		);
	}

	/**
	 * Resolves a term only after the native query leaves a path unclaimed.
	 *
	 * Existing objects, archives and explicit query targets retain precedence.
	 * On an unmatched path, feed, pagination, and ordering arguments qualify the archive.
	 * Core can return homepage posts or the site feed before finding that archive.
	 * Empty singular queries for unpublished objects do not claim public archive paths.
	 * Only the winning fallback runs the main query, preserving request arguments
	 * and contextual query variables.
	 *
	 * @hook pre_handle_404 99
	 * @since 5.2.2
	 * @global \WP         $wp
	 * @global \WP_Query   $wp_query
	 * @global \WP_Rewrite $wp_rewrite
	 *
	 * @param bool      $preempt Whether another filter handled the response status.
	 * @param \WP_Query $query   The main query before Core determines its status.
	 * @return bool
	 */
	public static function resolve_request( $preempt, $query ) {

		if ( false !== $preempt )
			return $preempt;

		global $wp, $wp_query, $wp_rewrite;

		// Earlier pre_handle_404 handlers can replace global $wp_query; Core uses the global after this filter.
		if ( $wp_query instanceof \WP_Query )
			$query = $wp_query;

		// A populated native route needs no fallback.
		if ( ! $wp->request || ( $wp->matched_rule && $query->posts ) )
			return $preempt;

		if ( ! Query\Utils::using_pretty_permalinks() )
			return $preempt;

		$context = null;
		$claimed = (bool) $wp->matched_rule;

		if ( ! $claimed ) {
			$extra_vars = array_diff_key(
				$wp->query_vars,
				[
					'error'   => true,
					'feed'    => true,
					'order'   => true,
					'orderby' => true,
					'paged'   => true,
				],
			);

			if ( $extra_vars ) {
				$context = self::get_route_context();
				$claimed = (bool) array_diff_key( $extra_vars, $context );
			}
		}

		if ( $claimed ) {
			if ( $query->posts )
				return $preempt;

			$object = $query->get_queried_object();

			if (
				   ( $object && ( ! $object instanceof \WP_Post || \is_post_publicly_viewable( $object ) ) )
				|| ( $query->is_feed() && ! $query->is_singular() )
				|| $query->is_home()
				|| $query->is_search()
				|| $query->is_preview()
				|| $query->is_robots()
				|| $query->is_favicon()
			) {
				return $preempt;
			}
		}

		$taxonomies = array_keys( Data\Plugin::get_option( 'remove_taxonomy_bases' ) );

		if ( ! $taxonomies )
			return $preempt;

		sort( $taxonomies, \SORT_STRING );

		$request = $wp->request;

		if (
			   $wp_rewrite->using_index_permalinks()
			&& ! str_starts_with( $request, "$wp_rewrite->index/" )
		) {
			$request = "$wp_rewrite->index/$request";
		}

		$variables = self::match_shortened_taxonomies( $request, $taxonomies );

		if ( ! $variables )
			return $preempt;

		$arguments = [];

		if ( str_contains( $_SERVER['REQUEST_URI'] ?? '', '?' ) ) {
			// Server rewrites can add pagename to $_GET. Use the requested URL to identify explicit arguments.
			$request_uri = \wp_check_invalid_utf8(
				stripslashes( $_SERVER['REQUEST_URI'] ),
			);
			parse_str(
				explode( '?', $request_uri, 2 )[1] ?? '',
				$arguments,
			);
		}

		$context ??= self::get_route_context();

		$variables = array_intersect_key(
			$wp->query_vars,
			$arguments + $context,
		) + $variables;

		$wp->query_vars = $variables;
		$query->query( $variables );

		return $preempt;
	}

	/**
	 * Gets the query variables that qualify a shortened term request without claiming its path.
	 *
	 * @since 5.2.2
	 *
	 * @return array<string,true> Query variable names as keys.
	 */
	private static function get_route_context() {
		/**
		 * Filters the query variables that qualify a shortened term request without claiming its path.
		 * Their values carry over to the winning term query.
		 *
		 * @since 5.2.2
		 * @example $context `[ 'edition' => true ]`
		 * @param array<string,true> $context Query variable names as keys.
		 */
		return (array) \apply_filters(
			'the_seo_framework_term_route_context',
			[],
		);
	}

	/**
	 * Matches a shortened archive across enabled taxonomies in byte order.
	 *
	 * Queries one taxonomy at a time so multilingual plugins do not apply a
	 * translated taxonomy's language filter to untranslated taxonomies.
	 *
	 * @since 5.2.2
	 *
	 * @param string   $request    The requested path relative to the WordPress home.
	 * @param string[] $taxonomies Enabled taxonomy names in byte order.
	 * @return array Matched query variables, or an empty array when none matched.
	 */
	private static function match_shortened_taxonomies( $request, $taxonomies ) {

		global $wp_rewrite;

		// Generate $matches[n] references without changing the shared rewrite object.
		$rewrite          = clone $wp_rewrite;
		$rewrite->matches = 'matches';

		$decoded = urldecode( $request );

		// Test each eligible taxonomy in byte order and stop at the first full term path match.
		foreach ( $taxonomies as $name ) {
			$taxonomy = \get_taxonomy( (string) $name );

			if ( ! Base_URL::supports_base_removal( $taxonomy ) ) continue;

			$variables = self::get_query_vars( $request, $decoded, $taxonomy, $rewrite );

			if ( $variables )
				return $variables;
		}

		return [];
	}

	/**
	 * Matches a shortened archive using one taxonomy's registered rewrite settings.
	 *
	 * Core generates feeds, pagination and endpoints. The resulting patterns are
	 * used only for this request and never stored as rewrite rules. A full term
	 * path match prevents an unrelated parent path from reaching a leaf term.
	 * Core's generic term query argument requires the leaf slug when the taxonomy
	 * has no dedicated query variable.
	 *
	 * @since 5.2.2
	 * @see \WP_Rewrite::rewrite_rules() Uses the same settings for extra permastructs.
	 * @see \get_category_by_path() Adapts Core's category path normalization to hierarchical taxonomies.
	 *
	 * @param string       $request  The requested path relative to the WordPress home.
	 * @param string       $decoded  The URL-decoded request path.
	 * @param \WP_Taxonomy $taxonomy The registered taxonomy.
	 * @param \WP_Rewrite  $rewrite  A cloned rewrite instance configured with `$matches` references.
	 * @return array Matched query variables, or an empty array when none matched.
	 */
	private static function get_query_vars( $request, $decoded, $taxonomy, $rewrite ) {

		$settings  = $rewrite->extra_permastructs[ $taxonomy->name ] ?? [];
		$query_var = $taxonomy->query_var;

		if ( empty( $settings['struct'] ) )
			return [];

		$shortened = Base_URL::strip_taxonomy_base( $settings['struct'], $taxonomy->name );

		if ( $shortened === $settings['struct'] )
			return [];

		$rules = $rewrite->generate_rewrite_rules(
			$shortened,
			$settings['ep_mask'],
			$settings['paged'],
			$settings['feed'],
			$settings['forcomments'],
			$settings['walk_dirs'],
			$settings['endpoints'],
		);

		/**
		 * Filters rewrite rules for the taxonomy's shortened permastruct.
		 *
		 * @since 3.1.0 WordPress Core
		 * @since 5.2.2 Now also filters request-local shortened rules.
		 *
		 * @param string[] $rules Rewrite queries keyed by their regular expression.
		 */
		$rules = \apply_filters(
			"{$taxonomy->name}_rewrite_rules",
			$rules,
		);

		$candidates = [];
		$slugs      = [];

		// Collect every shortened rewrite rule whose pattern matches the requested path.
		foreach ( $rules as $regex => $replacement ) {
			$pattern = self::get_rewrite_pattern( $regex );

			if (
				   ! preg_match( $pattern, $request, $matches )
				&& ( $request === $decoded || ! preg_match( $pattern, $decoded, $matches ) )
			) {
				continue;
			}

			$variables = self::parse_rewrite_query( $replacement, $matches );
			$path      = $variables[ $query_var ?: 'term' ] ?? '';

			if ( ! \is_string( $path ) || '' === $path ) continue;

			// Keep encoded slug characters, but let slashes separate terms and spaces form word breaks.
			$path = str_replace(
				[ '%2F', '%20' ],
				[ '/', ' ' ],
				rawurlencode( urldecode( $path ) ),
			);

			$segments = explode( '/', trim( $path, '/' ) );
			$path     = implode(
				'/',
				array_map( 'sanitize_title', $segments ),
			);

			if ( '' === $path ) continue;

			$leaf_slug           = basename( $path );
			$slugs[ $leaf_slug ] = true;
			$candidates[]        = [
				'path'      => $path,
				'leaf_slug' => $leaf_slug,
				'variables' => $variables,
			];
		}

		if ( ! $candidates )
			return [];

		$terms = \get_terms( [
			'taxonomy'               => $taxonomy->name,
			'slug'                   => array_keys( $slugs ),
			'hide_empty'             => false,
			'update_term_meta_cache' => false,
		] );

		if ( ! $terms || \is_wp_error( $terms ) )
			return [];

		$by_slug = [];

		// Index fetched terms by leaf slug for constant-time candidate checks.
		foreach ( $terms as $term )
			$by_slug[ $term->slug ][] = $term;

		// Evaluate candidates in rule priority order until one matches the full term path.
		foreach ( $candidates as $candidate ) {
			// Verify ancestor slugs so an unrelated parent path cannot reach a leaf term.
			foreach ( $by_slug[ $candidate['leaf_slug'] ] ?? [] as $term ) {
				if ( Meta\URI\Utils::get_term_slug_path( $term ) === $candidate['path'] ) {
					$variables = $candidate['variables'];

					if ( ! $query_var )
						$variables['term'] = $term->slug;

					return $variables;
				}
			}
		}

		return [];
	}

	/**
	 * Anchors a WordPress rewrite pattern and escapes our delimiter, preserving existing escapes.
	 *
	 * @since 5.2.2
	 *
	 * @param string $regex The registered rewrite expression.
	 * @return string The delimited pattern.
	 */
	public static function get_rewrite_pattern( $regex ) {

		$pattern = strtr(
			$regex,
			[
				'\\\\' => '\\\\',
				'\\/'  => '\\/',
				'/'    => '\\/',
			],
		);

		return "/^$pattern/";
	}

	/**
	 * Expands rewrite captures into WordPress query variables.
	 *
	 * @since 5.2.2
	 *
	 * @param string   $query   The registered rewrite query.
	 * @param string[] $matches The matched path fragments.
	 * @return array The query variables.
	 */
	public static function parse_rewrite_query( $query, $matches ) {

		parse_str(
			preg_replace_callback(
				'/\$matches\[([1-9][0-9]*)\]/',
				fn( $reference ) => rawurlencode( $matches[ (int) $reference[1] ] ?? '' ),
				preg_replace( '/^.+\?/', '', $query ),
			),
			$variables,
		);

		return $variables;
	}
}
