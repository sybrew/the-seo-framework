<?php
/**
 * @package The_SEO_Framework\Classes\Meta
 * @subpackage The_SEO_Framework\Meta\Breadcrumb
 */

namespace The_SEO_Framework\Meta;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\{
	memo,
	get_query_type_from_args,
	normalize_generation_args,
};

use \The_SEO_Framework\{
	Data,
	Helper\Query,
	Helper\Taxonomy,
	Meta,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds getters for breadcrumbs output.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->breadcrumbs() instead.
 */
class Breadcrumbs {

	/**
	 * Returns a list of breadcrumbs by URL and name.
	 *
	 * @since 5.0.0
	 * @todo consider wp_force_plain_post_permalink()
	 * @todo add extra parameter for $options; create (class?) constants for them.
	 *       -> Is tsf()->breadcrumbs()::CONSTANT possible?
	 *       -> Then, forward the options to a class variable, and build therefrom. Use as argument for memo().
	 *       -> Requested features (for shortcode): Remove home, remove current page.
	 *       -> Requested features (globally): Remove/show archive prefixes, hide PTA/terms, select home name, select SEO vs custom title (popular).
	 *       -> Add generation args to every crumb; this way we can perform custom lookups for titles after the crumb is generated.
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return array[] {
	 *     The breadcrumb list items in order of appearance.
	 *
	 *     @type string $url  The breadcrumb URL.
	 *     @type string $name The breadcrumb page title.
	 * }
	 */
	public static function get_breadcrumb_list( $args = null ) {

		if ( isset( $args ) ) {
			normalize_generation_args( $args );
			$list = static::get_breadcrumb_list_from_args( $args );
		} else {
			$list = memo() ?? memo( static::get_breadcrumb_list_from_query() );
		}

		/**
		 * @since 5.0.0
		 * @param array[] {
		 *     The breadcrumb list items in order of appearance.
		 *
		 *     @type string $url  The breadcrumb URL.
		 *     @type string $name The breadcrumb page title.
		 * }
		 * @param array|null $args The query arguments. Contains 'id', 'tax', 'pta', and 'uid'.
		 *                         Is null when the query is auto-determined.
		 */
		return (array) \apply_filters(
			'the_seo_framework_breadcrumb_list',
			$list,
			$args,
		);
	}

	/**
	 * Gets a list of breadcrumbs, based on expected or current query.
	 *
	 * @since 5.0.0
	 *
	 * @return array[] {
	 *     The breadcrumb list items in order of appearance.
	 *
	 *     @type string $url  The breadcrumb URL.
	 *     @type string $name The breadcrumb page title.
	 * }
	 */
	private static function get_breadcrumb_list_from_query() {

		if ( Query::is_real_front_page() ) {
			$list = static::get_front_page_breadcrumb_list();
		} elseif ( Query::is_singular() ) {
			$list = static::get_singular_breadcrumb_list();
		} elseif ( Query::is_archive() ) {
			if ( Query::is_editable_term() ) {
				$list = static::get_term_breadcrumb_list();
			} elseif ( \is_post_type_archive() ) {
				$list = static::get_pta_breadcrumb_list();
			} elseif ( Query::is_author() ) {
				$list = static::get_author_breadcrumb_list();
			} elseif ( \is_date() ) {
				$list = static::get_date_breadcrumb_list();
			}
		} elseif ( Query::is_search() ) {
			$list = static::get_search_breadcrumb_list();
		} elseif ( \is_404() ) {
			$list = static::get_404_breadcrumb_list();
		}

		// The ?? operator is redundant here, but the query might be mangled.
		return $list ?? [];
	}

	/**
	 * Gets a list of breadcrumbs, based on input arguments query.
	 *
	 * @since 5.0.0
	 *
	 * @param array $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 * @return array[] {
	 *     The breadcrumb list items in order of appearance.
	 *
	 *     @type string $url  The breadcrumb URL.
	 *     @type string $name The breadcrumb page title.
	 * }
	 */
	private static function get_breadcrumb_list_from_args( $args ) {

		switch ( get_query_type_from_args( $args ) ) {
			case 'single':
				if ( Query::is_static_front_page( $args['id'] ) ) {
					$list = static::get_front_page_breadcrumb_list();
				} else {
					$list = static::get_singular_breadcrumb_list( $args['id'] );
				}
				break;
			case 'term':
				$list = static::get_term_breadcrumb_list( $args['id'], $args['tax'] );
				break;
			case 'homeblog':
				$list = static::get_front_page_breadcrumb_list();
				break;
			case 'pta':
				$list = static::get_pta_breadcrumb_list( $args['pta'] );
				break;
			case 'user':
				$list = static::get_author_breadcrumb_list( $args['uid'] );
		}

		return $list;
	}

	/**
	 * Gets a list of breadcrumbs for the front page.
	 *
	 * @since 5.0.0
	 *
	 * @return array[] {
	 *     The breadcrumb list items in order of appearance.
	 *
	 *     @type string $url  The breadcrumb URL.
	 *     @type string $name The breadcrumb page title.
	 * }
	 */
	private static function get_front_page_breadcrumb_list() {
		return [ static::get_front_breadcrumb() ];
	}

	/**
	 * Gets a list of breadcrumbs for a singular object.
	 *
	 * @since 5.0.0
	 *
	 * @param ?int\WP_Post $id The post ID or post object. Leave null to autodetermine.
	 * @return array[] {
	 *     The breadcrumb list items in order of appearance.
	 *
	 *     @type string $url  The breadcrumb URL.
	 *     @type string $name The breadcrumb page title.
	 * }
	 */
	private static function get_singular_breadcrumb_list( $id = null ) {

		// Blog queries can be tricky. Use get_the_real_id to be certain.
		$post = \get_post( $id ?? Query::get_the_real_id() );

		if ( empty( $post ) )
			return [];

		$crumbs    = [];
		$post_type = \get_post_type( $post );

		// Get Post Type Archive, only if hierarchical.
		if ( \get_post_type_object( $post_type )->has_archive ?? false ) {
			$crumbs[] = [
				'url'  => Meta\URI::get_bare_pta_url( $post_type ),
				'name' => Meta\Title::get_bare_title( [ 'pta' => $post_type ] ),
			];
		}

		// Get Primary Term.
		$taxonomies      = array_keys( array_filter(
			Taxonomy::get_hierarchical( 'objects', $post_type ),
			'is_taxonomy_viewable',
		) );
		$taxonomy        = reset( $taxonomies ); // TODO make this an option; also which output they want to use.
		$primary_term_id = $taxonomy ? Data\Plugin\Post::get_primary_term_id( $post->ID, $taxonomy ) : 0;

		// If there's no ID, then there's no term assigned.
		if ( $primary_term_id ) {
			$ancestors = \get_ancestors(
				$primary_term_id,
				$taxonomy,
				'taxonomy',
			);

			foreach ( array_reverse( $ancestors ) as $ancestor_id ) {
				$crumbs[] = [
					'url'  => Meta\URI::get_bare_term_url( $ancestor_id, $taxonomy ),
					'name' => Meta\Title::get_bare_title( [
						'id'  => $ancestor_id,
						'tax' => $taxonomy,
					] ),
				];
			}

			$crumbs[] = [
				'url'  => Meta\URI::get_bare_term_url( $primary_term_id, $taxonomy ),
				'name' => Meta\Title::get_bare_title( [
					'id'  => $primary_term_id,
					'tax' => $taxonomy,
				] ),
			];
		}

		// get_post_ancestors() has no filter. get_ancestors() isn't used for posts in WP.
		foreach ( array_reverse( $post->ancestors ) as $ancestor_id ) {
			$crumbs[] = [
				'url'  => Meta\URI::get_bare_singular_url( $ancestor_id ),
				'name' => Meta\Title::get_bare_title( [ 'id' => $ancestor_id ] ),
			];
		}

		if ( isset( $id ) ) {
			$crumbs[] = [
				'url'  => Meta\URI::get_bare_singular_url( $post->ID ),
				'name' => Meta\Title::get_bare_title( [ 'id' => $post->ID ] ),
			];
		} else {
			$crumbs[] = [
				'url'  => Meta\URI::get_bare_singular_url(),
				'name' => Meta\Title::get_bare_title(),
			];
		}

		return [
			static::get_front_breadcrumb(),
			...$crumbs,
		];
	}

	/**
	 * Gets a list of breadcrumbs for a term object.
	 *
	 * @since 5.0.0
	 *
	 * @param int|null $term_id  The term ID.
	 * @param string   $taxonomy The taxonomy. Leave empty to autodetermine.
	 * @return array[] {
	 *     The breadcrumb list items in order of appearance.
	 *
	 *     @type string $url  The breadcrumb URL.
	 *     @type string $name The breadcrumb page title.
	 * }
	 */
	private static function get_term_breadcrumb_list( $term_id = null, $taxonomy = '' ) {

		$crumbs = [];

		if ( isset( $term_id ) ) {
			$taxonomy  = $taxonomy ?: \get_term( $term_id )->taxonomy ?? '';
			$ancestors = \get_ancestors( $term_id, $taxonomy, 'taxonomy' );

			foreach ( array_reverse( $ancestors ) as $ancestor_id ) {
				$crumbs[] = [
					'url'  => Meta\URI::get_bare_term_url( $ancestor_id, $taxonomy ),
					'name' => Meta\Title::get_bare_title( [
						'id'  => $ancestor_id,
						'tax' => $taxonomy,
					] ),
				];
			}

			$crumbs[] = [
				'url'  => Meta\URI::get_bare_term_url( $term_id, $taxonomy ),
				'name' => Meta\Title::get_bare_title( [
					'id'  => $term_id,
					'tax' => $taxonomy,
				] ),
			];
		} else {
			$taxonomy  = Query::get_current_taxonomy();
			$ancestors = \get_ancestors( Query::get_the_real_id(), $taxonomy, 'taxonomy' );

			foreach ( array_reverse( $ancestors ) as $ancestor_id ) {
				$crumbs[] = [
					'url'  => Meta\URI::get_bare_term_url( $ancestor_id, $taxonomy ),
					'name' => Meta\Title::get_bare_title( [
						'id'  => $ancestor_id,
						'tax' => $taxonomy,
					] ),
				];
			}

			$crumbs[] = [
				'url'  => Meta\URI::get_bare_term_url(),
				'name' => Meta\Title::get_bare_title(),
			];
		}

		return [
			static::get_front_breadcrumb(),
			...$crumbs,
		];
	}

	/**
	 * Gets a list of breadcrumbs for an post type archive.
	 *
	 * @since 5.0.0
	 *
	 * @param ?string $post_type The post type archive's post type.
	 *                           Leave null to autodetermine query and allow pagination.
	 * @return array[] {
	 *     The breadcrumb list items in order of appearance.
	 *
	 *     @type string $url  The breadcrumb URL.
	 *     @type string $name The breadcrumb page title.
	 * }
	 */
	private static function get_pta_breadcrumb_list( $post_type = null ) {

		$crumbs = [];

		if ( isset( $post_type ) ) {
			$crumbs[] = [
				'url'  => Meta\URI::get_pta_url( $post_type ),
				'name' => Meta\Title::get_bare_title( [ 'pta' => $post_type ] ),
			];
		} else {
			$crumbs[] = [
				'url'  => Meta\URI::get_bare_pta_url(),
				'name' => Meta\Title::get_bare_title(),
			];
		}

		return [
			static::get_front_breadcrumb(),
			...$crumbs,
		];
	}

	/**
	 * Gets a list of breadcrumbs for an author archive.
	 *
	 * @since 5.0.0
	 *
	 * @param ?int $id The author ID. Leave null to autodetermine.
	 * @return array[] {
	 *     The breadcrumb list items in order of appearance.
	 *
	 *     @type string $url  The breadcrumb URL.
	 *     @type string $name The breadcrumb page title.
	 * }
	 */
	private static function get_author_breadcrumb_list( $id = null ) {

		$crumbs = [];

		if ( isset( $id ) ) {
			$crumbs[] = [
				'url'  => Meta\URI::get_author_url( $id ),
				'name' => Meta\Title::get_bare_title( [ 'uid' => $id ] ),
			];
		} else {
			$crumbs[] = [
				'url'  => Meta\URI::get_bare_author_url(),
				'name' => Meta\Title::get_bare_title(),
			];
		}

		return [
			static::get_front_breadcrumb(),
			...$crumbs,
		];
	}

	/**
	 * Gets a list of breadcrumbs for a date archive.
	 *
	 * Unlike other breadcrumb trials, this one doesn't support custom queries.
	 * This is because `Meta\Title::get_bare_title()` accepts no custom date queries.
	 *
	 * @since 5.0.0
	 *
	 * @return array[] {
	 *     The breadcrumb list items in order of appearance.
	 *
	 *     @type string $url  The breadcrumb URL.
	 *     @type string $name The breadcrumb page title.
	 * }
	 */
	private static function get_date_breadcrumb_list() {
		return [
			static::get_front_breadcrumb(),
			[
				'url'  => Meta\URI::get_bare_date_url(
					\get_query_var( 'year' ),
					\get_query_var( 'monthnum' ),
					\get_query_var( 'day' ),
				),
				'name' => Meta\Title::get_bare_title(),
			],
		];
	}

	/**
	 * Gets a list of breadcrumbs for a search query.
	 *
	 * @since 5.0.0
	 *
	 * @return array[] {
	 *     The breadcrumb list items in order of appearance.
	 *
	 *     @type string $url  The breadcrumb URL.
	 *     @type string $name The breadcrumb page title.
	 * }
	 */
	private static function get_search_breadcrumb_list() {
		return [
			static::get_front_breadcrumb(),
			[
				'url'  => Meta\URI::get_search_url(),
				'name' => Meta\Title::get_search_query_title(), // discrepancy
			],
		];
	}

	/**
	 * Gets a list of breadcrumbs for 404 page.
	 *
	 * @since 5.0.0
	 *
	 * @return array[] {
	 *     The breadcrumb list items in order of appearance.
	 *
	 *     @type string $url  The breadcrumb URL.
	 *     @type string $name The breadcrumb page title.
	 * }
	 */
	private static function get_404_breadcrumb_list() {
		return [
			static::get_front_breadcrumb(),
			[
				'url'  => '',
				'name' => Meta\Title::get_404_title(), // discrepancy
			],
		];
	}

	/**
	 * Gets a single breadcrumb for the front page.
	 *
	 * @since 5.0.0
	 *
	 * @return array[] {
	 *     The breadcrumb list items in order of appearance.
	 *
	 *     @type string $url  The breadcrumb URL.
	 *     @type string $name The breadcrumb page title.
	 * }
	 */
	private static function get_front_breadcrumb() {
		return [
			'url'  => Meta\URI::get_bare_front_page_url(),
			'name' => Meta\Title::get_front_page_title(), // discrepancy
		];
	}
}
