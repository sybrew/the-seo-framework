<?php
/**
 * @package The_SEO_Framework\Classes\Meta
 * @subpackage The_SEO_Framework\Meta\Breadcrumb
 */

namespace The_SEO_Framework\Meta;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\{
	memo,
	normalize_generation_args,
};

use \The_SEO_Framework\{
	Data,
	Helper\Query,
	Helper\Taxonomies,
	Meta,
};

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
 * Holds getters for breadcrumbs output.
 *
 * @since 4.3.0
 * @access protected
 *         Use tsf()->breadcrumbs() instead.
 */
class Breadcrumbs {

	/**
	 * Returns a list of breadcrumbs by URL and name.
	 *
	 * @since 4.3.0
	 * @todo consider wp_force_plain_post_permalink()
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                           Leave null to autodetermine query.
	 * @return array[] The breadcrumb list : {
	 *    string url:  The breadcrumb URL.
	 *    string name: The breadcrumb page title.
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
		 * @since 4.3.0
		 * @param array[] The breadcrumb list, sequential: int position => {
		 *    string url:  The breadcrumb URL.
		 *    string name: The breadcrumb page title.
		 * }
		 * @param array|null $args The query arguments. Contains 'id', 'tax', and 'pta'.
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
	 * @since 4.3.0
	 *
	 * @return array[] The breadcrumb list : {
	 *    string url:  The breadcrumb URL.
	 *    string name: The breadcrumb page title.
	 * }
	 */
	private static function get_breadcrumb_list_from_query() {

		if ( Query::is_real_front_page() ) {
			return static::get_front_page_breadcrumb_list();
		} elseif ( Query::is_singular() ) {
			return static::get_singular_breadcrumb_list();
		} elseif ( Query::is_archive() ) {
			// var_dump() make this akin to get_generated_url_from_query()?
			return static::get_archive_breadcrumb_list();
		} elseif ( Query::is_search() ) {
			return static::get_search_breadcrumb_list();
		} elseif ( \is_404() ) {
			return static::get_404_breadcrumb_list();
		}

		// Something went terribly wrong if we reach this.
		return [];
	}

	/**
	 * Gets a list of breadcrumbs, based on input arguments query.
	 *
	 * @since 4.3.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                         Leave null to autodetermine query.
	 * @return array[] The breadcrumb list : {
	 *    string url:  The breadcrumb URL.
	 *    string name: The breadcrumb page title.
	 * }
	 */
	private static function get_breadcrumb_list_from_args( $args ) {

		// var_dump() make this akin to get_generated_url_from_args()?
		if ( $args['tax'] ) {
			return static::get_archive_breadcrumb_list( \get_term( $args['id'], $args['tax'] ) );
		} elseif ( $args['pta'] ) {
			return static::get_archive_breadcrumb_list( \get_post_type_object( $args['pta'] ) );
		}

		if ( Query::is_real_front_page_by_id( $args['id'] ) )
			return static::get_front_page_breadcrumb_list();

		return static::get_singular_breadcrumb_list( $args['id'] );
	}

	/**
	 * Gets a list of breadcrumbs for the front page.
	 *
	 * @since 4.3.0
	 *
	 * @return array[] The breadcrumb list : {
	 *    string url:  The breadcrumb URL.
	 *    string name: The breadcrumb page title.
	 * }
	 */
	private static function get_front_page_breadcrumb_list() {
		return [ static::get_front_breadcrumb() ];
	}

	/**
	 * Gets a list of breadcrumbs for a singular object.
	 *
	 * @since 4.3.0
	 *
	 * @param int|\WP_Post $id The post ID or post object.
	 * @return array[] The breadcrumb list : {
	 *    string url:  The breadcrumb URL.
	 *    string name: The breadcrumb page title.
	 * }
	 */
	private static function get_singular_breadcrumb_list( $id = 0 ) {

		// Blog queries can be tricky. Use get_the_real_id to be certain.
		$post = \get_post( $id ?: Query::get_the_real_id() );

		if ( empty( $post ) )
			return [];

		$crumbs    = [];
		$post_type = \get_post_type( $post );

		if ( \is_post_type_hierarchical( $post_type ) ) { // page.
			// get_post_ancestors() has no filter. get_ancestors() isn't used for posts in WP.
			foreach ( array_reverse( $post->ancestors ) as $ancestor_id ) {
				$crumbs[] = [
					'url'  => Meta\URI::get_bare_singular_url( $ancestor_id ),
					'name' => Meta\Title::get_bare_title( [ 'id' => $ancestor_id ] ),
				];
			}
		} else { // single.
			$taxonomies = Taxonomies::get_hierarchical_taxonomies_as( 'names', $post_type );
			$taxonomy   = reset( $taxonomies ); // TODO make this an option; also which output they want to use.

			if ( $taxonomy ) {
				$primary_term_id = Data\Plugin\Post::get_primary_term_id( $post->ID, $taxonomy );
				$ancestors       = \get_ancestors(
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
			} elseif ( \get_post_type_object( $post_type )->has_archive ?? false ) {
				$crumbs[] = [
					'url'  => Meta\URI::get_bare_post_type_archive_url( $post_type ),
					'name' => Meta\Title::get_bare_title( [ 'pta' => $post_type ] ),
				];
			}
		}

		$crumbs[] = [
			'url'  => Meta\URI::get_bare_singular_url( $post->ID ),
			'name' => Meta\Title::get_bare_title( [ 'id' => $post->ID ] ),
		];

		return [
			static::get_front_breadcrumb(),
			...$crumbs,
		];
	}

	/**
	 * Gets a list of breadcrumbs for a singular object.
	 *
	 * @since 4.3.0
	 *
	 * @param \WP_Term|\WP_User|\WP_Post_Type|null $object The Term object. Leave null to autodermine query.
	 * @return array[] The breadcrumb list : {
	 *    string url:  The breadcrumb URL.
	 *    string name: The breadcrumb page title.
	 * }
	 */
	private static function get_archive_breadcrumb_list( $object = null ) {

		$crumbs = [];

		if ( null === $object ) {
			if ( Query::is_editable_term() ) {
				$taxonomy = Query::get_current_taxonomy();

				$ancestors = \get_ancestors(
					Query::get_the_real_id(),
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
					'url'  => Meta\URI::get_bare_term_url(),
					'name' => Meta\Title::get_bare_title(),
				];
			} elseif ( \is_post_type_archive() ) {
				$crumbs[] = [
					'url'  => Meta\URI::get_bare_post_type_archive_url(),
					'name' => Meta\Title::get_bare_title(),
				];
			} elseif ( Query::is_author() ) {
				$crumbs[] = [
					'url'  => Meta\URI::get_bare_author_url(),
					'name' => Meta\Title::get_bare_title(),
				];
			} elseif ( \is_date() ) {
				$year  = \get_query_var( 'year' );
				$month = \get_query_var( 'monthnum' );
				$day   = \get_query_var( 'day' );

				$crumbs[] = [
					'url'  => Meta\URI::get_bare_date_url( $year, $month, $day ),
					'name' => Meta\Title::get_bare_title(),
				];
			}
		} elseif ( $object instanceof \WP_Term ) {
			$term_id  = $object->term_id;
			$taxonomy = $object->taxonomy;

			$ancestors = \get_ancestors(
				$object->term_id,
				$object->taxonomy,
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
				'url'  => Meta\URI::get_bare_term_url( $term_id, $taxonomy ),
				'name' => Meta\Title::get_bare_title( [
					'id'  => $term_id,
					'tax' => $taxonomy,
				] ),
			];
		} elseif ( $object instanceof \WP_Post_Type ) {
			$crumbs[] = [
				'url'  => Meta\URI::get_post_type_archive_url( $object->name ),
				'name' => Meta\Title::get_bare_title( [
					'pta' => $object->name,
				] ),
			];
		} elseif ( $object instanceof \WP_User ) {
			// TODO add, next to 'id', 'tax', and 'pta' support, also 'uid'
			$crumbs[] = [
				'url'  => Meta\URI::get_author_url( $object->id ),
				'name' => Meta\Title::get_archive_title_from_object( $object ),
			];
		}

		return [
			static::get_front_breadcrumb(),
			...$crumbs,
		];
	}

	/**
	 * Gets a list of breadcrumbs for a search query.
	 *
	 * @since 4.3.0
	 *
	 * @return array[] The breadcrumb list : {
	 *    string url:  The breadcrumb URL.
	 *    string name: The breadcrumb page title.
	 * }
	 */
	private static function get_search_breadcrumb_list() {
		return [
			static::get_front_breadcrumb(),
			[
				'url'  => Meta\URI::get_search_url(),
				'name' => Meta\Title::get_search_query_title(),
			],
		];
	}

	/**
	 * Gets a list of breadcrumbs for 404 page.
	 *
	 * @since 4.3.0
	 *
	 * @return array[] The breadcrumb list : {
	 *    string url:  The breadcrumb URL. In this case, it's empty.
	 *    string name: The breadcrumb page title.
	 * }
	 */
	private static function get_404_breadcrumb_list() {
		return [
			static::get_front_breadcrumb(),
			[
				'url'  => '',
				'name' => Meta\Title::get_404_title(),
			],
		];
	}

	/**
	 * Gets a single breadcrumb for the front page.
	 *
	 * @since 4.3.0
	 *
	 * @return array The frontpage breadcrumb : {
	 *    string url:  The breadcrumb URL.
	 *    string name: The breadcrumb page title.
	 * }
	 */
	private static function get_front_breadcrumb() {
		return [
			'url'  => Meta\URI::get_bare_front_page_url(),
			'name' => Meta\Title::get_front_page_title(),
		];
	}
}
