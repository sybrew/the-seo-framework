<?php
/**
 * @package The_SEO_Framework\Classes\Meta
 * @subpackage The_SEO_Framework\Meta\Breadcrumb
 */

namespace The_SEO_Framework\Meta;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function The_SEO_Framework\{
	get_query_type_from_args,
	memo,
	normalize_generation_args,
};

use The_SEO_Framework\{
	Data,
	Meta,
};
use The_SEO_Framework\Helper\{
	Query,
	Taxonomy,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 - 2025 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
	 * @since 5.1.4
	 * @var array The options for breadcrumb generation.
	 */
	private static $options = [];

	/**
	 * Returns a list of breadcrumbs by URL and name.
	 *
	 * @since 5.0.0
	 * @since 5.1.4 Added the `$options` parameter.
	 * @since 5.2.0 1. Singular trails now follow the `breadcrumb_taxonomy` and `breadcrumb_archive` options.
	 *              2. Added the role index to the return value.
	 *              3. Singular trails now omit post ancestors that aren't publicly viewable.
	 *              4. Generated archive crumb names now drop their archive title prefix.
	 *              5. Front-end term archives now include ancestor terms again.
	 *
	 * @param array|null $args    The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                            Leave null to autodetermine query.
	 * @param array      $options Optional. {
	 *     The options for breadcrumb generation
	 *
	 *     @type ?bool $use_meta_title Whether to consider meta titles before using page titles.
	 *                                 Default determined by 'breadcrumb_use_meta_title' option.
	 * }
	 * @return array[] {
	 *     The breadcrumb list items in order of appearance.
	 *
	 *     @type string $url  The breadcrumb URL.
	 *     @type string $name The breadcrumb page title.
	 *     @type string $role The crumb role: `home`, `pta`, `archive-N`, `page-N`, `current`,
	 *                        or `current-home` on the front page.
	 * }
	 */
	public static function get_breadcrumb_list( $args = null, $options = [] ) {

		// The default options must be ordered alphabetically to ensure consistent cache keys.
		self::$options = array_merge(
			[
				'use_meta_title' => (bool) Data\Plugin::get_option( 'breadcrumb_use_meta_title' ),
			],
			array_filter(
				$options,
				fn( $v ) => null !== $v,
			),
		);

		// Sort options alphabetically to ensure consistent cache keys regardless of input order.
		ksort( self::$options );

		if ( isset( $args ) ) {
			normalize_generation_args( $args );
			$list = self::get_breadcrumb_list_from_args( $args );
		} else {
			$list = memo( null, self::$options )
				?? memo(
					self::get_breadcrumb_list_from_query(),
					self::$options,
				);
		}

		/**
		 * @since 5.0.0
		 * @since 5.1.4 Added the `$options` parameter.
		 * @since 5.2.0 Added the role index to each item, which `tsf_breadcrumb()` now requires.
		 * @param array[] {
		 *     The breadcrumb list items in order of appearance.
		 *
		 *     @type string $url  The breadcrumb URL.
		 *     @type string $name The breadcrumb page title.
		 *     @type string $role The crumb role: `home`, `pta`, `archive-N`, `page-N`, `current`,
		 *                        or `current-home` on the front page.
		 * }
		 * @param array|null $args    The query arguments. Contains 'id', 'tax', 'pta', and 'uid'.
		 *                            Is null when the query is auto-determined.
		 * @param array      $options The options used for breadcrumb generation.
		 */
		return (array) \apply_filters(
			'the_seo_framework_breadcrumb_list',
			$list,
			$args,
			self::$options,
		);
	}

	/**
	 * Returns the breadcrumb title based on the current setting.
	 *
	 * @since 5.1.4
	 * @since 5.2.0 Now drops the archive title prefix from generated archive names.
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return string The breadcrumb title.
	 */
	private static function get_breadcrumb_title( $args = null ) {

		if ( self::$options['use_meta_title'] ) {
			$title = Meta\Title::get_bare_custom_title( $args );

			if ( \strlen( $title ) )
				return $title;
		}

		$title  = Meta\Title::get_bare_generated_title( $args );
		$object = null;

		if ( isset( $args ) ) {
			normalize_generation_args( $args );

			switch ( get_query_type_from_args( $args ) ) {
				case 'term':
					$object = \get_term( $args['id'], $args['tax'] );
					break;
				case 'pta':
					$object = \get_post_type_object( $args['pta'] );
					break;
				case 'user':
					$object = Data\User::get_userdata( $args['uid'] );
			}

			// Without an object, get_archive_title_list() would read the current query instead.
			$is_archive = $object && ! \is_wp_error( $object );
		} else {
			$is_archive = Query::is_archive();
		}

		if ( ! $is_archive || ! Meta\Title\Conditions::use_generated_archive_prefix( $object ) )
			return $title;

		$prefix = Data\Filter\Sanitize::metadata_content( Meta\Title::get_archive_title_list( $object )[1] );

		if ( str_starts_with( $title, $prefix ) )
			$title = trim( substr( $title, \strlen( $prefix ) ) );

		return $title;
	}

	/**
	 * Gets a list of breadcrumbs, based on expected or current query.
	 *
	 * @since 5.0.0
	 * @since 5.2.0 Added the role index to the return value.
	 *
	 * @return array[] {
	 *     The breadcrumb list items in order of appearance.
	 *
	 *     @type string $url  The breadcrumb URL.
	 *     @type string $name The breadcrumb page title.
	 *     @type string $role The crumb role: `home`, `pta`, `archive-N`, `page-N`, `current`,
	 *                        or `current-home` on the front page.
	 * }
	 */
	private static function get_breadcrumb_list_from_query() {

		if ( Query::is_real_front_page() ) {
			$list = self::get_front_page_breadcrumb_list();
		} elseif ( Query::is_singular() ) {
			$list = self::get_singular_breadcrumb_list();
		} elseif ( Query::is_archive() ) {
			if ( Query::is_editable_term() ) {
				$list = self::get_term_breadcrumb_list();
			} elseif ( \is_post_type_archive() ) {
				$list = self::get_pta_breadcrumb_list();
			} elseif ( Query::is_author() ) {
				$list = self::get_author_breadcrumb_list();
			} elseif ( \is_date() ) {
				$list = self::get_date_breadcrumb_list();
			}
		} elseif ( Query::is_search() ) {
			$list = self::get_search_breadcrumb_list();
		} elseif ( \is_404() ) {
			$list = self::get_404_breadcrumb_list();
		}

		// The ?? operator is redundant here, but the query might be mangled.
		return $list ?? [];
	}

	/**
	 * Gets a list of breadcrumbs, based on input arguments query.
	 *
	 * @since 5.0.0
	 * @since 5.2.0 Added the role index to the return value.
	 *
	 * @param array $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 * @return array[] {
	 *     The breadcrumb list items in order of appearance.
	 *
	 *     @type string $url  The breadcrumb URL.
	 *     @type string $name The breadcrumb page title.
	 *     @type string $role The crumb role: `home`, `pta`, `archive-N`, `page-N`, `current`,
	 *                        or `current-home` on the front page.
	 * }
	 */
	private static function get_breadcrumb_list_from_args( $args ) {

		switch ( get_query_type_from_args( $args ) ) {
			case 'single':
				if ( Query::is_static_front_page( $args['id'] ) ) {
					$list = self::get_front_page_breadcrumb_list();
				} else {
					$list = self::get_singular_breadcrumb_list( $args['id'] );
				}
				break;
			case 'term':
				$list = self::get_term_breadcrumb_list( $args['id'], $args['tax'] );
				break;
			case 'homeblog':
				$list = self::get_front_page_breadcrumb_list();
				break;
			case 'pta':
				$list = self::get_pta_breadcrumb_list( $args['pta'] );
				break;
			case 'user':
				$list = self::get_author_breadcrumb_list( $args['uid'] );
		}

		return $list;
	}

	/**
	 * Gets a list of breadcrumbs for the front page.
	 *
	 * @since 5.0.0
	 * @since 5.2.0 Added the role index to the return value.
	 *
	 * @return array[] {
	 *     The breadcrumb list items in order of appearance.
	 *
	 *     @type string $url  The breadcrumb URL.
	 *     @type string $name The breadcrumb page title.
	 *     @type string $role The crumb role: `home`, `pta`, `archive-N`, `page-N`, `current`,
	 *                        or `current-home` on the front page.
	 * }
	 */
	private static function get_front_page_breadcrumb_list() {

		$crumb         = self::get_front_breadcrumb();
		$crumb['role'] = 'current-home';

		return [ $crumb ];
	}

	/**
	 * Gets a list of breadcrumbs for a singular object.
	 *
	 * @since 5.0.0
	 * @since 5.2.0 1. The archive crumb and taxonomy now follow the breadcrumb hierarchy options.
	 *              2. Added the role index to the return value.
	 *              3. Now omits post ancestors that aren't publicly viewable.
	 *
	 * @param ?int\WP_Post $id The post ID or post object. Leave null to autodetermine.
	 * @return array[] {
	 *     The breadcrumb list items in order of appearance.
	 *
	 *     @type string $url  The breadcrumb URL.
	 *     @type string $name The breadcrumb page title.
	 *     @type string $role The crumb role: `home`, `pta`, `archive-N`, `page-N`, `current`,
	 *                        or `current-home` on the front page.
	 * }
	 */
	private static function get_singular_breadcrumb_list( $id = null ) {

		// Blog queries can be tricky. Use get_the_real_id to be certain.
		$post = \get_post( $id ?? Query::get_the_real_id() );

		if ( empty( $post ) )
			return [];

		$crumbs    = [];
		$post_type = \get_post_type( $post );

		// A missing setting includes the archive when the post type has one.
		if (
			   ( \get_post_type_object( $post_type )->has_archive ?? false )
			&& ( Data\Plugin::get_option( 'breadcrumb_archive', $post_type ) ?? true )
		) {
			$crumbs[] = [
				'url'  => Meta\URI::get_bare_pta_url( $post_type ),
				'name' => self::get_breadcrumb_title( [ 'pta' => $post_type ] ),
				'role' => 'pta',
			];
		}

		// Get Primary Term. An empty stored value uses the first public hierarchical taxonomy.
		$pt_taxonomies = array_values( array_intersect(
			Taxonomy::get_hierarchical( 'names', $post_type ),
			Taxonomy::get_all_public(),
		) );
		// `-1` removes the term trail.
		$choice = (string) Data\Plugin::get_option( 'breadcrumb_taxonomy', $post_type );

		if ( '-1' === $choice ) {
			$taxonomy = '';
		} elseif ( \in_array( $choice, $pt_taxonomies, true ) ) {
			$taxonomy = $choice;
		} else {
			$taxonomy = $pt_taxonomies[0] ?? '';
		}

		$primary_term_id = $taxonomy ? Data\Plugin\Post::get_primary_term_id( $post->ID, $taxonomy ) : 0;

		// If there's no ID, then there's no term assigned.
		if ( $primary_term_id ) {
			$i = 0;

			foreach ( Data\Term::get_term_parents(
				$primary_term_id,
				$taxonomy,
				true, // Include self
			) as $parent ) {
				$crumbs[] = [
					'url'  => Meta\URI::get_bare_term_url( $parent->term_id, $parent->taxonomy ),
					'name' => self::get_breadcrumb_title( [
						'id'  => $parent->term_id,
						'tax' => $parent->taxonomy,
					] ),
					'role' => "archive-$i",
				];
				++$i;
			}
		}

		// Exclude self, we add it below (current post is cached if $id is null).
		$i = 0;

		foreach ( Data\Post::get_post_parents( $post->ID ) as $parent ) {
			// Draft, pending, scheduled, and private ancestors only have a plain link, which 404s for visitors.
			if ( ! \is_post_status_viewable( \get_post_status( $parent ) ) )
				continue;

			$crumbs[] = [
				'url'  => Meta\URI::get_bare_singular_url( $parent->ID ),
				'name' => self::get_breadcrumb_title( [ 'id' => $parent->ID ] ),
				'role' => "page-$i",
			];
			++$i;
		}

		if ( isset( $id ) ) {
			$crumbs[] = [
				'url'  => Meta\URI::get_bare_singular_url( $post->ID ),
				'name' => self::get_breadcrumb_title( [ 'id' => $post->ID ] ),
				'role' => 'current',
			];
		} else {
			$crumbs[] = [
				'url'  => Meta\URI::get_bare_singular_url(),
				'name' => self::get_breadcrumb_title(),
				'role' => 'current',
			];
		}

		return [
			self::get_front_breadcrumb(),
			...$crumbs,
		];
	}

	/**
	 * Gets a list of breadcrumbs for a term object.
	 *
	 * @since 5.0.0
	 * @since 5.2.0 1. Added the role index to the return value.
	 *              2. Front-end term archives now include ancestor terms again.
	 *
	 * @param int|null $term_id  The term ID. Leave null to autodetermine.
	 * @param string   $taxonomy The taxonomy. Leave empty to autodetermine.
	 * @return array[] {
	 *     The breadcrumb list items in order of appearance.
	 *
	 *     @type string $url  The breadcrumb URL.
	 *     @type string $name The breadcrumb page title.
	 *     @type string $role The crumb role: `home`, `pta`, `archive-N`, `page-N`, `current`,
	 *                        or `current-home` on the front page.
	 * }
	 */
	private static function get_term_breadcrumb_list( $term_id = null, $taxonomy = '' ) {

		$crumbs = [];

		if ( isset( $term_id ) ) {
			$taxonomy = $taxonomy ?: ( \get_term( $term_id )->taxonomy ?? '' );
		} else {
			// Always override taxonomy when term_id is null.
			$taxonomy = Query::get_current_taxonomy();
		}

		$i = 0;

		foreach (
			Data\Term::get_term_parents(
				$term_id ?? Query::get_the_real_id(),
				$taxonomy,
				false, // Exclude self. The current term is added below.
			)
			as $parent
		) {
			$crumbs[] = [
				'url'  => Meta\URI::get_bare_term_url( $parent->term_id, $parent->taxonomy ),
				'name' => self::get_breadcrumb_title( [
					'id'  => $parent->term_id,
					'tax' => $parent->taxonomy,
				] ),
				'role' => "archive-$i",
			];
			++$i;
		}

		if ( isset( $term_id ) ) {
			$crumbs[] = [
				'url'  => Meta\URI::get_bare_term_url( $term_id, $taxonomy ),
				'name' => self::get_breadcrumb_title( [
					'id'  => $term_id,
					'tax' => $taxonomy,
				] ),
				'role' => 'current',
			];
		} else {
			$crumbs[] = [
				'url'  => Meta\URI::get_bare_term_url(),
				'name' => self::get_breadcrumb_title(),
				'role' => 'current',
			];
		}

		return [
			self::get_front_breadcrumb(),
			...$crumbs,
		];
	}

	/**
	 * Gets a list of breadcrumbs for an post type archive.
	 *
	 * @since 5.0.0
	 * @since 5.2.0 Added the role index to the return value.
	 *
	 * @param ?string $post_type The post type archive's post type.
	 *                           Leave null to autodetermine query and allow pagination.
	 * @return array[] {
	 *     The breadcrumb list items in order of appearance.
	 *
	 *     @type string $url  The breadcrumb URL.
	 *     @type string $name The breadcrumb page title.
	 *     @type string $role The crumb role: `home`, `pta`, `archive-N`, `page-N`, `current`,
	 *                        or `current-home` on the front page.
	 * }
	 */
	private static function get_pta_breadcrumb_list( $post_type = null ) {

		$crumbs = [];

		if ( isset( $post_type ) ) {
			$crumbs[] = [
				'url'  => Meta\URI::get_pta_url( $post_type ),
				'name' => self::get_breadcrumb_title( [ 'pta' => $post_type ] ),
				'role' => 'current',
			];
		} else {
			$crumbs[] = [
				'url'  => Meta\URI::get_bare_pta_url(),
				'name' => self::get_breadcrumb_title(),
				'role' => 'current',
			];
		}

		return [
			self::get_front_breadcrumb(),
			...$crumbs,
		];
	}

	/**
	 * Gets a list of breadcrumbs for an author archive.
	 *
	 * @since 5.0.0
	 * @since 5.2.0 Added the role index to the return value.
	 *
	 * @param ?int $id The author ID. Leave null to autodetermine.
	 * @return array[] {
	 *     The breadcrumb list items in order of appearance.
	 *
	 *     @type string $url  The breadcrumb URL.
	 *     @type string $name The breadcrumb page title.
	 *     @type string $role The crumb role: `home`, `pta`, `archive-N`, `page-N`, `current`,
	 *                        or `current-home` on the front page.
	 * }
	 */
	private static function get_author_breadcrumb_list( $id = null ) {

		$crumbs = [];

		if ( isset( $id ) ) {
			$crumbs[] = [
				'url'  => Meta\URI::get_author_url( $id ),
				'name' => self::get_breadcrumb_title( [ 'uid' => $id ] ),
				'role' => 'current',
			];
		} else {
			$crumbs[] = [
				'url'  => Meta\URI::get_bare_author_url(),
				'name' => self::get_breadcrumb_title(), // NOTE: has no meta title (yet), but we'll add that in 5.2.0
				'role' => 'current',
			];
		}

		return [
			self::get_front_breadcrumb(),
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
	 * @since 5.2.0 Added the role index to the return value.
	 *
	 * @return array[] {
	 *     The breadcrumb list items in order of appearance.
	 *
	 *     @type string $url  The breadcrumb URL.
	 *     @type string $name The breadcrumb page title.
	 *     @type string $role The crumb role: `home`, `pta`, `archive-N`, `page-N`, `current`,
	 *                        or `current-home` on the front page.
	 * }
	 */
	private static function get_date_breadcrumb_list() {
		return [
			self::get_front_breadcrumb(),
			[
				'url'  => Meta\URI::get_bare_date_url(
					\get_query_var( 'year' ),
					\get_query_var( 'monthnum' ),
					\get_query_var( 'day' ),
				),
				'name' => self::get_breadcrumb_title(), // discrepancy, has no meta title
				'role' => 'current',
			],
		];
	}

	/**
	 * Gets a list of breadcrumbs for a search query.
	 *
	 * @since 5.0.0
	 * @since 5.2.0 Added the role index to the return value.
	 *
	 * @return array[] {
	 *     The breadcrumb list items in order of appearance.
	 *
	 *     @type string $url  The breadcrumb URL.
	 *     @type string $name The breadcrumb page title.
	 *     @type string $role The crumb role: `home`, `pta`, `archive-N`, `page-N`, `current`,
	 *                        or `current-home` on the front page.
	 * }
	 */
	private static function get_search_breadcrumb_list() {
		return [
			self::get_front_breadcrumb(),
			[
				'url'  => Meta\URI::get_search_url(),
				'name' => Meta\Title::get_search_query_title(), // discrepancy, has no meta title
				'role' => 'current',
			],
		];
	}

	/**
	 * Gets a list of breadcrumbs for 404 page.
	 *
	 * @since 5.0.0
	 * @since 5.2.0 Added the role index to the return value.
	 *
	 * @return array[] {
	 *     The breadcrumb list items in order of appearance.
	 *
	 *     @type string $url  The breadcrumb URL.
	 *     @type string $name The breadcrumb page title.
	 *     @type string $role The crumb role: `home`, `pta`, `archive-N`, `page-N`, `current`,
	 *                        or `current-home` on the front page.
	 * }
	 */
	private static function get_404_breadcrumb_list() {
		return [
			self::get_front_breadcrumb(),
			[
				'url'  => '',
				'name' => Meta\Title::get_404_title(), // discrepancy, has no meta title
				'role' => 'current',
			],
		];
	}

	/**
	 * Gets a single breadcrumb for the front page.
	 *
	 * @since 5.0.0
	 * @since 5.2.0 Added the role index to the return value.
	 *
	 * @return array[] {
	 *     The breadcrumb list items in order of appearance.
	 *
	 *     @type string $url  The breadcrumb URL.
	 *     @type string $name The breadcrumb page title.
	 *     @type string $role The crumb role: `home`, `pta`, `archive-N`, `page-N`, `current`,
	 *                        or `current-home` on the front page.
	 * }
	 */
	private static function get_front_breadcrumb() {
		return [
			'url'  => Meta\URI::get_bare_front_page_url(),
			'name' => Meta\Title::get_front_page_title(), // discrepancy, has no meta title
			'role' => 'home',
		];
	}
}
