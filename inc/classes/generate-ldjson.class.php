<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Generate_Ldjson
 * @subpackage The_SEO_Framework\Getters\Schema
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2022 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\Generate_Ldjson
 *
 * Generates SEO data based on content
 *
 * @since 2.8.0
 */
class Generate_Ldjson extends Generate_Image {

	/**
	 * Builds up JSON data.
	 *
	 * NOTE: Array indexes with false-esque values will be stripped.
	 *       `[ 'key' => (string) 'anything but 0' ]` will pass.
	 *       `[ 'key' => '0' ]` will not pass.
	 *
	 * @since 2.9.3
	 * @see $this->receive_json_data()
	 * @uses $this->build_json_data_cache()
	 *
	 * @param string   $key  The JSON data key.
	 * @param iterable $data The JSON data.
	 */
	public function build_json_data( $key, $data ) {

		$key  = \sanitize_key( $key );
		$data = array_filter( $data );

		foreach ( $data as $k => $v ) {
			$thing = [ $k => $v ];
			$this->build_json_data_cache( $key, $thing );
		}
	}

	/**
	 * Returns built JSON data.
	 *
	 * May return empty values if data is invalid.
	 *
	 * @since 2.9.3
	 * @since 2.9.4 No longer escapes slashes on PHP 5.4+.
	 * @see $this->build_json_data()
	 * @uses $this->cache_json_data()
	 *
	 * @param string $key    The JSON data key.
	 * @param bool   $encode Whether to JSON encode the output.
	 * @return array|string The JSON data for $key. Array if $encode is false, string otherwise.
	 */
	public function receive_json_data( $key, $encode = true ) {

		$key  = \sanitize_key( $key );
		$data = $this->cache_json_data( true, $key );

		if ( \has_filter( 'the_seo_framework_receive_json_data' ) ) {
			/**
			 * @since 2.9.3
			 * @param array  $data The JSON-LD data.
			 * @param string $key  The data key.
			 */
			$data = (array) \apply_filters_ref_array( 'the_seo_framework_receive_json_data', [ $data, $key ] );
		}

		if ( $encode ) {
			$options  = 0;
			$options |= JSON_UNESCAPED_SLASHES;
			$options |= $this->script_debug ? JSON_PRETTY_PRINT : 0;

			return $data ? (string) json_encode( $data, $options ) : '';
		}

		return $data ?: [];
	}

	/**
	 * Builds up JSON data cache.
	 *
	 * @since 2.9.3
	 * @see $this->build_json_data()
	 *
	 * @param string $key   The JSON data key.
	 * @param array  $entry The JSON data entry.
	 */
	protected function build_json_data_cache( $key, $entry ) {
		$this->cache_json_data( false, $key, $entry );
	}

	/**
	 * Builds up JSON data cache.
	 *
	 * @since 2.9.3
	 * @see $this->build_json_data()
	 * @see $this->receive_json_data()
	 *
	 * @param bool   $get   Whether to get or otherwise set the data.
	 * @param string $key   The JSON data key.
	 * @param array  $entry The JSON data entry.
	 * @return array The JSON data for $key.
	 */
	protected function cache_json_data( $get = true, $key = '', $entry = [] ) {

		static $data = [];

		if ( $get )
			return $data[ $key ];

		$data[ $key ][ key( $entry ) ] = reset( $entry );

		return [];
	}

	/**
	 * Renders the LD+JSON scripts.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 No longer cares for json_ld plugins.
	 * @since 4.0.5 Removed caching.
	 *
	 * @return string The LD+JSON scripts.
	 */
	public function render_ld_json_scripts() {

		if ( $this->is_real_front_page() ) {
			// Homepage Schema.
			$output = '';

			$output .= $this->get_ld_json_website() ?: '';
			$output .= $this->get_ld_json_links() ?: '';
		} else {
			// All other pages' Schema.
			$output = $this->get_ld_json_breadcrumbs() ?: '';
		}

		return $output;
	}

	/**
	 * Generates LD+JSON Search and Sitename script.
	 *
	 * @since 2.9.3
	 * @since 3.0.0 This whole functions now only listens to the searchbox option.
	 * @since 4.1.2 Now properly slashes the search URL.
	 *
	 * @return string escaped LD+JSON Search and Sitename script.
	 */
	public function get_ld_json_website() {

		if ( ! $this->enable_ld_json_searchbox() )
			return '';

		$data = [
			'@context' => 'https://schema.org',
			'@type'    => 'WebSite',
			'url'      => $this->get_homepage_permalink(),
		];

		// The name part.
		$blogname = $this->get_blogname();
		$kname    = $this->get_option( 'knowledge_name' );

		$alternate_name = $kname && $kname !== $blogname ? $kname : '';

		$data += [
			'name'          => \strlen( $blogname ) ? $this->escape_title( $blogname ) : '',
			'alternateName' => \strlen( $alternate_name ) ? $this->escape_title( $alternate_name ) : '',
		];

		// The searchbox part.
		$pattern     = '%s{%s}';
		$action_name = 'search_term_string';
		$search_link = $this->pretty_permalinks ? \trailingslashit( \get_search_link() ) : \get_search_link();
		/**
		 * @since 2.7.0
		 * @param string $search_url The default WordPress search URL without query parameters.
		 */
		$search_url = (string) \apply_filters( 'the_seo_framework_ld_json_search_url', $search_link );

		if ( ! empty( $GLOBALS['wp_rewrite']->get_search_permastruct() ) ) {
			$pattern    = \user_trailingslashit( '%s{%s}', 'search' );
			$search_url = \trailingslashit( $search_url );
		}

		$data += [
			'potentialAction' => [
				'@type'       => 'SearchAction',
				// not properly sanitized; however, search_term_string is inert.
				'target'      => sprintf( $pattern, \esc_url( $search_url ), $action_name ),
				'query-input' => sprintf( 'required name=%s', $action_name ),
			],
		];

		// Building
		$key = 'website';
		$this->build_json_data( $key, $data );
		$json = $this->receive_json_data( $key );

		if ( $json )
			return '<script type="application/ld+json">' . $json . '</script>' . "\r\n";

		return '';
	}

	/**
	 * Generates LD+JSON Social Profile Links script.
	 *
	 * @since 2.9.3
	 *
	 * @return string LD+JSON Social Profile Links script.
	 */
	public function get_ld_json_links() {

		if ( ! $this->enable_ld_json_knowledge() )
			return '';

		$knowledge_type = $this->get_option( 'knowledge_type' );
		$knowledge_name = $this->get_option( 'knowledge_name' ) ?: $this->get_blogname();

		$data = [
			'@context' => 'https://schema.org',
			'@type'    => ucfirst( \esc_attr( $knowledge_type ) ),
			'url'      => $this->get_homepage_permalink(),
			'name'     => $this->escape_title( $knowledge_name ),
		];

		if ( $this->get_option( 'knowledge_logo' ) && 'organization' === $knowledge_type ) {
			$data += [
				'logo' => \esc_url_raw( $this->get_knowledge_logo() ),
			];
		}

		/**
		 * @since 2.7 or later.
		 * @TODO deprecate
		 * @param array The SEO Framework's option names used for sitelinks.
		 */
		$sameurls_options = (array) \apply_filters(
			'the_seo_framework_json_options',
			[
				'knowledge_facebook',
				'knowledge_twitter',
				'knowledge_gplus',
				'knowledge_instagram',
				'knowledge_youtube',
				'knowledge_linkedin',
				'knowledge_pinterest',
				'knowledge_soundcloud',
				'knowledge_tumblr',
			]
		);

		$sameurls = [];
		foreach ( $sameurls_options as $_o ) {

			$_ov = $this->get_option( $_o ) ?: '';

			if ( $_ov )
				$sameurls[] = \esc_url_raw( $_ov, [ 'https', 'http' ] );
		}

		if ( $sameurls ) {
			$data += [
				'sameAs' => $sameurls,
			];
		}

		$key = 'links';
		$this->build_json_data( $key, $data );
		$json = $this->receive_json_data( $key );

		if ( $json )
			return '<script type="application/ld+json">' . $json . '</script>' . "\r\n";

		return '';
	}

	/**
	 * Returns knowledge logo URL.
	 * It first tries to get the option and then the Customizer icon.
	 *
	 * @since 3.0.0
	 *
	 * @param bool $get_option Whether to fetch the option.
	 * @return string The logo URL.
	 */
	public function get_knowledge_logo( $get_option = true ) {
		/**
		 * @since 3.0.0
		 * @param string $logo       The current logo URL.
		 * @param bool   $get_option Whether to test the option or just the fallbacks.
		 */
		return (string) \apply_filters_ref_array(
			'the_seo_framework_knowledge_logo',
			[
				( $get_option ? $this->get_option( 'knowledge_logo_url' ) : false )
					?: Builders\Images::get_site_icon_image_details()->current()['url']
					?: '',
				$get_option,
			]
		);
	}

	/**
	 * Generates LD+JSON Breadcrumbs script.
	 *
	 * @since 2.9.3
	 *
	 * @return string LD+JSON Breadcrumbs script.
	 */
	public function get_ld_json_breadcrumbs() {

		if ( ! $this->enable_ld_json_breadcrumbs() )
			return '';

		$output = '';

		if ( $this->is_singular() && ! $this->is_real_front_page() ) {
			// TODO Shouldn't this be is_post_type_hierarchical()?
			if ( $this->is_single() ) {
				$output = $this->get_ld_json_breadcrumbs_post();
			} else {
				$output = $this->get_ld_json_breadcrumbs_page();
			}
		}

		return $output;
	}

	/**
	 * Generates LD+JSON Breadcrumbs script for Pages.
	 *
	 * @since 2.9.3
	 * @since 3.1.0 Now always generates something, regardless of parents.
	 * @since 4.0.0 Removed the image input requirement.
	 *
	 * @return string LD+JSON breadcrumbs script for Pages.
	 */
	public function get_ld_json_breadcrumbs_page() {

		$items   = [];
		$parents = array_reverse( \get_post_ancestors( $this->get_the_real_ID() ) );

		$position = 1; // 0 is the homepage.
		foreach ( $parents as $parent_id ) {
			++$position;

			$_generator_args = [ 'id' => $parent_id ];

			if ( $this->ld_json_breadcrumbs_use_seo_title() ) {
				$parent_name = $this->get_filtered_raw_custom_field_title( $_generator_args )
							?: $this->get_filtered_raw_generated_title( $_generator_args );
			} else {
				$parent_name = $this->get_filtered_raw_generated_title( $_generator_args );
			}

			$crumb = [
				'@type'    => 'ListItem',
				'position' => $position,
				'item'     => [
					'@id'  => $this->get_schema_url_id(
						'breadcrumb',
						'create',
						$_generator_args
					),
					'name' => $this->escape_title( $parent_name ),
				],
			];

			$items[] = $crumb;
		}

		array_unshift( $items, $this->get_ld_json_breadcrumb_home_crumb() );
		array_push( $items, $this->get_ld_json_breadcrumb_current( $position ) );

		return $this->make_breadcrumb_script( $items );
	}

	/**
	 * Generates LD+JSON Breadcrumbs script for Posts.
	 *
	 * @since 2.9.3
	 * @since 3.0.0 1. Now only returns one crumb.
	 *              2. Now listens to primary term ID.
	 *
	 * @return string LD+JSON breadcrumbs script for Posts on success. Empty string on failure.
	 */
	public function get_ld_json_breadcrumbs_post() {

		$output = '';

		$post_id    = $this->get_the_real_ID();
		$post_type  = \get_post_type( $post_id );
		$taxonomies = $this->get_hierarchical_taxonomies_as( 'names', \get_post_type( $post_id ) );

		/**
		 * @since 3.0.0
		 * @param array|string  $taxonomies The assigned hierarchical taxonomies.
		 * @param string        $post_type  The current post type.
		 * @param int           $post_id    The current Post ID.
		 */
		$taxonomies = \apply_filters_ref_array(
			'the_seo_framework_ld_json_breadcrumb_taxonomies',
			[
				$taxonomies,
				$post_type,
				$post_id,
			]
		);

		if ( \is_array( $taxonomies ) ) {
			$taxonomy = reset( $taxonomies );
		} else {
			$taxonomy = $taxonomies;
		}

		// Test categories.
		$r = \is_object_in_term( $post_id, $taxonomy, '' );
		if ( ! $r || \is_wp_error( $r ) )
			return '';

		/**
		 * @since 2.8.0
		 * @param array  $terms    The candidate terms.
		 * @param int    $post_id  The current Post ID.
		 * @param string $taxonomy The current taxonomy.
		 */
		$terms = (array) \apply_filters_ref_array(
			'the_seo_framework_ld_json_breadcrumb_terms',
			[
				\get_the_terms( $post_id, $taxonomy ),
				$post_id,
				$taxonomy,
			]
		);

		if ( empty( $terms ) )
			return '';

		$terms = \wp_list_pluck( $terms, 'parent', 'term_id' );

		$parents      = [];
		$assigned_ids = [];

		// Fetch cats children id's, if any.
		foreach ( $terms as $term_id => $parent_id ) :
			$assigned_ids[ $term_id ] = $parent_id;
			// Check if they have parents (gets them all).
			$ancestors = \get_ancestors( $term_id, $taxonomy );
			if ( $ancestors ) {
				// Save parents to find duplicates.
				$parents[ $term_id ] = $ancestors;
			} else {
				// Save current only with empty parent id..
				$parents[ $term_id ] = [];
			}
		endforeach;
		// Circle of life...
		unset( $terms );

		if ( ! $parents )
			return '';

		// Seed out parents that have multiple assigned children.
		foreach ( $parents as $pa_id => $child_id ) :
			foreach ( $child_id as $ckey => $cid ) :
				if ( isset( $parents[ $cid ] ) ) {
					unset( $parents[ $cid ] );
				}
			endforeach;
		endforeach;

		// Merge tree list.
		$tree_ids = $this->build_ld_json_breadcrumb_trees( $parents );

		if ( ! $tree_ids )
			return '';

		$primary_term    = $this->get_primary_term( $post_id, $taxonomy );
		$primary_term_id = $primary_term ? (int) $primary_term->term_id : 0;

		$filtered = false;
		/**
		 * Only get one crumb.
		 * If a category has multiple trees, it will filter until found.
		 */
		if ( $primary_term_id ) {
			$_trees = $this->filter_ld_json_breadcrumb_trees( $tree_ids, $primary_term_id );
			if ( $_trees ) {
				$tree_ids = $_trees;
				$filtered = true;
			}
		}
		if ( ! $filtered ) {
			// Only get the first tree through numeric ordering.
			ksort( $assigned_ids, SORT_NUMERIC );
			$tree_ids = $this->filter_ld_json_breadcrumb_trees( $tree_ids, key( $assigned_ids ) );
		}

		if ( is_scalar( $tree_ids ) )
			$tree_ids = [ $tree_ids ];

		$items = [];

		foreach ( $tree_ids as $pos => $child_id ) :
			$position = $pos + 2;

			$_generator_args = [
				'id'       => $child_id,
				'taxonomy' => $taxonomy,
			];

			if ( $this->ld_json_breadcrumbs_use_seo_title() ) {
				$cat_name = $this->get_filtered_raw_custom_field_title( $_generator_args )
						 ?: $this->get_generated_single_term_title( \get_term( $child_id, $taxonomy ) )
						 ?: $this->get_static_untitled_title();
			} else {
				$cat_name = $this->get_generated_single_term_title( \get_term( $child_id, $taxonomy ) )
						 ?: $this->get_static_untitled_title();
			}

			// Store in cache.
			$items[] = [
				'@type'    => 'ListItem',
				'position' => $position,
				'item'     => [
					'@id'  => $this->get_schema_url_id(
						'breadcrumb',
						'create',
						$_generator_args
					),
					'name' => $this->escape_title( $cat_name ),
				],
			];
		endforeach;

		if ( $items ) {
			array_unshift( $items, $this->get_ld_json_breadcrumb_home_crumb() );
			array_push( $items, $this->get_ld_json_breadcrumb_current( $position ) );
			$output .= $this->make_breadcrumb_script( $items );
		}

		return $output;
	}

	/**
	 * Builds breadcrumb trees.
	 *
	 * @since 2.9.3
	 *
	 * @param array $cats          The breadcrumb trees, with the key as parent.
	 * @param array $previous_tree A previous set tree to compare to, if set.
	 * @return array Trees in order.
	 */
	protected function build_ld_json_breadcrumb_trees( $cats, $previous_tree = [] ) {

		$trees = $previous_tree;

		foreach ( $cats as $parent => $kitten ) {
			if ( empty( $kitten ) ) {
				// Final cat.
				$trees[] = $parent;
			} else {
				if ( 1 === \count( $kitten ) ) {
					// Single tree.
					$trees[] = [ reset( $kitten ), $parent ];
				} else {
					// Nested categories.
					$add = [];

					foreach ( $kitten as $kit_id => $child_id ) {
						// Only add if non-existent in $trees.
						if ( ! \in_array( $child_id, $trees, true ) )
							$add[] = $child_id;
					}

					// Put children in right order.
					$add = array_reverse( $add );

					$trees[] = array_merge( $add, [ $parent ] );
				}
			}
		}

		return $trees;
	}

	/**
	 * Filters breadcrumb tree with IDs until $find is found.
	 *
	 * Magic.
	 *
	 * @since 3.0.0
	 *
	 * @param array|int $ids The (multidimensional) breadcrumb items with numeric values.
	 *                       Or just a single numeric value, if it's the last item being scrutinized.
	 * @param int       $find The key to find.
	 * @return array    $trees. Empty if $find is nowhere to be found.
	 */
	protected function filter_ld_json_breadcrumb_trees( $ids, $find ) {

		$found = [];

		if ( \in_array( $find, (array) $ids, true ) ) {
			$found = [ $find ];
		} elseif ( \is_array( $ids ) ) {
			foreach ( $ids as $id ) {
				if ( $this->filter_ld_json_breadcrumb_trees( $id, $find ) ) {
					$found = array_splice(
						$id,
						0,
						array_search( $find, $id, true ) + 1
					);
					break;
				}
			}
		}

		return $found;
	}

	/**
	 * Generates homepage LD+JSON breadcrumb.
	 * Memoizes the return value.
	 *
	 * @since 2.9.3
	 * @since 3.2.2 1. The title now works for the homepage as blog.
	 *              2. The image has been disabled for the homepage as blog.
	 *                 i. I couldn't fix it without evading the API, which is bad.
	 * @since 4.0.0 Removed the image input requirement.
	 *
	 * @return array The HomePage crumb entry.
	 */
	public function get_ld_json_breadcrumb_home_crumb() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo() ) return $memo;

		$_generator_args = [ 'id' => $this->get_the_front_page_ID() ];

		if ( $this->ld_json_breadcrumbs_use_seo_title() ) {
			$title = $this->get_filtered_raw_custom_field_title( $_generator_args )
				 ?: $this->get_blogname();
		} else {
			$title = $this->get_filtered_raw_generated_title( $_generator_args )
				 ?: $this->get_blogname();
		}

		return memo( [
			'@type'    => 'ListItem',
			'position' => 1,
			'item'     => [
				'@id'  => $this->get_schema_url_id( 'breadcrumb', 'homepage' ),
				'name' => $this->escape_title( $title ),
			],
		] );
	}

	/**
	 * Generates current Page/Post LD+JSON breadcrumb.
	 * Memoizes the return value.
	 *
	 * @since 2.9.3
	 * @since 3.0.0 Removed @id output to allow for more same-page schema items.
	 * @since 4.0.0 Removed the image input requirement.
	 *
	 * @param int $position The previous crumb position.
	 * @return array The Current Page/Post crumb entry.
	 */
	public function get_ld_json_breadcrumb_current( $position ) {

		static $crumb = null;

		$position++;

		if ( isset( $crumb ) ) {
			$crumb['position'] = $position;
			return $crumb;
		}

		$post_id         = $this->get_the_real_ID();
		$_generator_args = [ 'id' => $post_id ];

		if ( $this->ld_json_breadcrumbs_use_seo_title() ) {
			$name = $this->get_filtered_raw_custom_field_title( $_generator_args )
				 ?: $this->get_generated_single_post_title( $post_id )
				 ?: $this->get_static_untitled_title();
		} else {
			$name = $this->get_generated_single_post_title( $post_id )
				 ?: $this->get_static_untitled_title();
		}

		$crumb = [
			'@type'    => 'ListItem',
			'position' => $position,
			'item'     => [
				'@id'  => $this->get_schema_url_id( 'breadcrumb', 'currentpage' ),
				'name' => $this->escape_title( $name ),
			],
		];

		return $crumb;
	}

	/**
	 * Creates LD+JSON Breadcrumb script from items.
	 * Memoizes the iterations of this method, to create a key for the builder.
	 *
	 * @since 2.9.0
	 * @since 2.9.3 Rewritten to conform to the new generator.
	 *
	 * @param array $items The LD+JSON breadcrumb items.
	 * @return string The LD+JSON Breadcrumb script.
	 */
	protected function make_breadcrumb_script( $items ) {

		if ( ! $items )
			return '';

		static $it = 0;

		$key = "breadcrumbs_{$it}";

		$data = [
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $items,
		];

		$this->build_json_data( $key, $data );
		$json = $this->receive_json_data( $key );

		$it++;

		if ( $json )
			return '<script type="application/ld+json">' . $json . '</script>' . "\r\n";

		return '';
	}

	/**
	 * Returns Schema.org '@id' part from URL.
	 *
	 * @since 3.0.0
	 *
	 * @param string $type The type of script. Must be escaped. Unused.
	 * @param string $from Where to generate from.
	 * @param array  $args The URL generation args.
	 * @return string The JSON URL '@id'
	 */
	public function get_schema_url_id( $type, $from, $args = [] ) {

		switch ( $from ) {
			case 'currentpage':
				$url = $this->get_current_permalink();
				break;

			case 'homepage':
				$url = $this->get_homepage_permalink();
				break;

			case 'create':
				$url = $this->get_canonical_url( $args );
				break;

			default:
				$url = '';
				break;
		}

		return $url;
	}

	/**
	 * Determines whether to use the SEO title or only the fallback page title.
	 * Memoizes the return value.
	 *
	 * @since 2.9.0
	 *
	 * @return bool
	 */
	public function ld_json_breadcrumbs_use_seo_title() {
		/**
		 * @since 2.9.0
		 * @param bool $use_seo_title Whether to use the SEO title.
		 */
		return memo() ?? memo( (bool) \apply_filters( 'the_seo_framework_use_breadcrumb_seo_title', true ) );
	}

	/**
	 * Determines if breadcrumbs scripts are enabled.
	 *
	 * @since 2.6.0
	 * @since 4.2.0 No longer memoizes the return value.
	 *
	 * @return bool
	 */
	public function enable_ld_json_breadcrumbs() {
		/**
		 * @since 2.4.2
		 * @param bool $enable Whether to force disable Schema.org breadcrumbs.
		 */
		return (bool) \apply_filters( 'the_seo_framework_json_breadcrumb_output', $this->get_option( 'ld_json_breadcrumbs' ) );
	}

	/**
	 * Determines if searchbox script is enabled.
	 *
	 * @since 2.6.0
	 * @since 4.2.0 No longer memoizes the return value.
	 *
	 * @return bool
	 */
	public function enable_ld_json_searchbox() {
		/**
		 * @since 2.3.9
		 * @param bool $enable Whether to force disable Schema.org searchbox.
		 */
		return (bool) \apply_filters( 'the_seo_framework_json_search_output', $this->get_option( 'ld_json_searchbox' ) );
	}

	/**
	 * Determines if Knowledge Graph Script is enabled.
	 *
	 * @since 2.6.5
	 * @since 4.2.0 No longer memoizes the return value.
	 *
	 * @return bool
	 */
	public function enable_ld_json_knowledge() {
		/**
		 * @since 2.3.9
		 * @param bool $enable Whether to force disable Schema.org knowledge.
		 */
		return (bool) \apply_filters( 'the_seo_framework_json_knowledge_output', $this->get_option( 'knowledge_output' ) );
	}
}
