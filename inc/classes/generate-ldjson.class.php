<?php
/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

defined( 'ABSPATH' ) or die;

/**
 * Class AutoDescription_Generate
 *
 * Generates SEO data based on content
 * Returns strings/arrays
 *
 * @since 2.6.0
 */
class AutoDescription_Generate_Ldjson extends AutoDescription_Generate_Image {

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	private function __wakeup() { }

	/**
	 * Handle unapproachable invoked methods.
	 */
	public function __call( $name, $arguments ) {
		parent::__call( $name, $arguments );
	}

	/**
	 * Constructor, load parent constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Render the LD+Json scripts.
	 *
	 * @since 2.6.0
	 *
	 * @return string The LD+Json scripts.
	 */
	public function render_ld_json_scripts() {

		if ( $this->has_json_ld_plugin() )
			return '';

		$this->setup_ld_json_transient( $this->get_the_real_ID() );

		if ( $this->the_seo_framework_debug ) $this->debug_init( __METHOD__, false, $debug_key = microtime( true ), array( 'LD Json transient' => $this->ld_json_transient, 'Output from transient' => false !== $this->get_transient( $this->ld_json_transient ) ) );

		$output = $this->get_transient( $this->ld_json_transient );
		if ( false === $output ) {

			$output = '';

			//* Only display search helper and knowledge graph on front page.
			if ( $this->is_front_page() ) {

				$sitename = $this->ld_json_name();
				$sitelinks = $this->ld_json_search();
				$knowledgegraph = $this->ld_json_knowledge();

				if ( $sitename )
					$output .= $sitename;

				if ( $sitelinks )
					$output .= $sitelinks;

				if ( $knowledgegraph )
					$output .= $knowledgegraph;

			} else {
				$breadcrumbhelper = $this->ld_json_breadcrumbs();

				//* No wrapper, is done within script generator.
				if ( $breadcrumbhelper )
					$output .= $breadcrumbhelper;
			}

			/**
			 * Transient expiration: 1 week.
			 * Keep the script for at most 1 week.
			 */
			$expiration = WEEK_IN_SECONDS;

			$this->set_transient( $this->ld_json_transient, $output, $expiration );
		}

		/**
		 * Debug output.
		 * @since 2.4.2
		 */
		if ( $this->the_seo_framework_debug ) $this->debug_init( __METHOD__, false, $debug_key, array( 'LD Json transient output' => $output ) );

		return $output;
	}

	/**
	 * Returns http://schema.org json encoded context URL.
	 *
	 * @staticvar string $context
	 * @since 2.6.0
	 *
	 * @return string The json encoded context url.
	 */
	public function schema_context() {

		static $context;

		if ( isset( $context ) )
			return $context;

		return $context = json_encode( 'http://schema.org' );
	}

	/**
	 * Returns 'WebSite' json encoded type name.
	 *
	 * @staticvar string $context
	 * @since 2.6.0
	 *
	 * @return string The json encoded type name.
	 */
	public function schema_type() {

		static $type;

		if ( isset( $type ) )
			return $type;

		return $type = json_encode( 'WebSite' );
	}

	/**
	 * Returns json encoded home url.
	 *
	 * @staticvar string $url
	 * @since 2.6.0
	 *
	 * @return string The json encoded home url.
	 */
	public function schema_home_url() {

		static $type;

		if ( isset( $type ) )
			return $type;

		return $type = json_encode( $this->the_home_url_from_cache() );
	}

	/**
	 * Returns json encoded blogname.
	 *
	 * @staticvar string $name
	 * @since 2.6.0
	 *
	 * @return string The json encoded blogname.
	 */
	public function schema_blog_name() {

		static $name;

		if ( isset( $name ) )
			return $name;

		return $name = json_encode( $this->get_blogname() );
	}

	/**
	 * Returns 'BreadcrumbList' json encoded type name.
	 *
	 * @staticvar string $crumblist
	 * @since 2.6.0
	 *
	 * @return string The json encoded 'BreadcrumbList'.
	 */
	public function schema_breadcrumblist() {

		static $crumblist;

		if ( isset( $crumblist ) )
			return $crumblist;

		return $crumblist = json_encode( 'BreadcrumbList' );
	}

	/**
	 * Returns 'ListItem' json encoded type name.
	 *
	 * @staticvar string $listitem
	 * @since 2.6.0
	 *
	 * @return string The json encoded 'ListItem'.
	 */
	public function schema_listitem() {

		static $listitem;

		if ( isset( $listitem ) )
			return $listitem;

		return $listitem = json_encode( 'ListItem' );
	}

	/**
	 * Returns 'image' json encoded value.
	 *
	 * @staticvar array $images
	 * @since 2.7.0
	 * @todo implement blog page image.
	 * @priority low 2.7.0+ extension.
	 *
	 * @param int|string $id The page, post, product or term ID.
	 * @param bool $singular Whether the ID is singular.
	 */
	public function schema_image( $id = 0, $singular = false ) {

		static $images = array();

		$id = (int) $id;

		if ( isset( $images[ $id ][ $singular ] ) )
			return $images[ $id ][ $singular ];

		$image = '';

		if ( $singular ) {
			if ( $id === $this->get_the_real_ID() ) {
				$image = $this->get_image_from_cache( $id );
			} elseif ( $id ) {
				//* No ID (0) results in the home page being a blog. This will be handled in the future.
				$image = $this->get_image( $id );
			}
		} else {
			//* Placeholder.
			$image = '';
		}

		/**
		 * Applies filters 'the_seo_framework_ld_json_breadcrumb_image' : string
		 * @since 2.7.0
		 * @param string $image The current image.
		 * @param int $id The page, post, product or term ID.
		 * @param bool $singular Whether the ID is singular.
		 */
		$image = apply_filters( 'the_seo_framework_ld_json_breadcrumb_image', $image, $id, $singular );

		return $images[ $id ][ $singular ] = json_encode( esc_url_raw( $image ) );
	}

	/**
	 * Generate LD+Json search helper.
	 *
	 * @since 2.2.8
	 *
	 * @return escaped LD+json search helper string.
	 */
	public function ld_json_search() {

		if ( false === $this->enable_ld_json_searchbox() )
			return '';

		$context = $this->schema_context();
		$webtype = $this->schema_type();
		$url = $this->schema_home_url();
		$name = $this->schema_blog_name();
		$actiontype = json_encode( 'SearchAction' );

		// Remove trailing quote and add it back.
		$target = mb_substr( json_encode( $this->the_home_url_from_cache( true ) . '?s=' ), 0, -1 ) . '{search_term_string}"';

		$queryaction = json_encode( 'required name=search_term_string' );

		$json = sprintf( '{"@context":%s,"@type":%s,"url":%s,"name":%s,"potentialAction":{"@type":%s,"target":%s,"query-input":%s}}', $context, $webtype, $url, $name, $actiontype, $target, $queryaction );

		$output = '';
		if ( $json )
			$output = '<script type="application/ld+json">' . $json . '</script>' . "\r\n";

		return $output;
	}

	/**
	 * Generate LD+Json breadcrumb helper.
	 *
	 * @since 2.4.2
	 *
	 * @return escaped LD+json search helper string.
	 */
	public function ld_json_breadcrumbs() {

		if ( false === $this->enable_ld_json_breadcrumbs() )
			return '';

		//* Used to count ancestors and categories.
		$output = '';

		if ( $this->is_single() || $this->is_wc_product() ) {
			$output = $this->ld_json_breadcrumbs_post();
		} elseif ( false === $this->is_front_page() && $this->is_page() ) {
			$output = $this->ld_json_breadcrumbs_page();
		}

		return $output;
	}

	/**
	 * Generate post breadcrumb.
	 *
	 * @since 2.6.0
	 *
	 * @return string $output The breadcrumb script.
	 */
	public function ld_json_breadcrumbs_post() {

		$output = '';

		$post_id = $this->get_the_real_ID();

		$cat_type = 'category';

		//* WooCommerce support.
		if ( $this->is_wc_product() )
			$cat_type = 'product_cat';

		//* Test categories.
		$r = is_object_in_term( $post_id, $cat_type, '' );

		if ( ! $r || is_wp_error( $r ) )
			return '';

		$cats = get_the_terms( $post_id, $cat_type );
		$cats = wp_list_pluck( $cats, 'parent', 'term_id' );
		asort( $cats, SORT_NUMERIC );

		$assigned_ids = array();
		$kittens = array();
		$parents = array();
		$parents_merge = array();

		//* Fetch cats children id's, if any.
		foreach ( $cats as $term_id => $parent_id ) {
			//* Store to filter unused Cat ID's from the post.
			$assigned_ids[] = $term_id;

			// Check if they have kittens.
			$children = get_term_children( $term_id, $cat_type );
			$ancestors = get_ancestors( $term_id, $cat_type );

			//* Save children id's as kittens.
			$kittens[ $term_id ] = $children;
			$parents[ $term_id ] = $ancestors;
		}

		foreach ( $kittens as $kit_id => $child_id ) {
			foreach ( $child_id as $ckey => $cid ) {

				/**
				 * Seed out children that aren't assigned.
				 * (from levels too deep as get_term_children gets them all).
				 */
				if ( $cid && false === in_array( $cid, $assigned_ids, true ) )
					unset( $kittens[ $kit_id ][ $ckey ] );

				/**
				 * Make the tree count down multiple children are assigned.
				 * This fetches the array from the ancestors.
				 *
				 * What we want is that the latest child ID gets its own single tree.
				 * All ancestors should be a representation of the previous assigned trees.
				 *
				 * E.g. We have this structure, all assigned:
				 *	- Cat 1
				 *		- Cat 2
				 *			- Cat 3
				 *
				 * We want a tree for "Cat 1+2+3", "Cat 1+2", and "Cat 3".
				 *
				 * We could add Cat 1 as well, but that's will give two single category lines, which could be misinterperted.
				 * So we only use what we know: The kittens (child tree).
				 */
				if ( isset( $parents[ $cid ] ) && ! empty( $parents[ $kit_id ] ) ) {
					$parents_merge[ $kit_id ] = $parents[ $kit_id ];
					unset( $kittens[ $kit_id ] );
				}
			}
		}

		/**
		 * Build category ID trees for kittens.
		 */
		$trees = $this->build_breadcrumb_trees( $kittens );

		//* Empty parents.
		$parents = array();

		if ( ! empty( $parents_merge ) ) {
			foreach ( $parents_merge as $child_id => $parents_ids ) {

				//* Reset kitten.
				$kitten = array();

				//* Last element should be parent.
				$pid = array_pop( $parents_ids );

				if ( isset( $pid ) ) {
					//* Parents are reversed children. Let's fix that.
					$parents_ids = array_reverse( $parents_ids );

					//* Add previous parent at the end of the rest.
					array_push( $parents_ids, $child_id );

					//* Temporarily array.
					$kitten[ $pid ] = $parents_ids;

					$trees = $this->build_breadcrumb_trees( $kitten, $trees );
				} else {
					//* Parents are reversed children. Let's fix that.
					$parents_ids = array_reverse( $parents_ids );

					$trees = $this->build_breadcrumb_trees( $parents_ids, $trees );
				}
			}
		}

		/**
		 * Sort by number of id's. Provides a cleaner layout, better Search Engine understanding and more consistent cache.
		 */
		if ( count( $trees ) > 1 )
			array_multisort( array_map( 'count', $trees ), SORT_DESC, $trees );

		$context = $this->schema_context();
		$context_type = $this->schema_breadcrumblist();
		$item_type = $this->schema_listitem();

		//* For each of the tree items, create a separated script.
		if ( $trees ) {
			foreach ( $trees as $tree_ids ) {

				if ( is_array( $tree_ids ) ) {
					//* Term has assigned children.

					/**
					 * @staticvar int $item_cache : Used to prevent duplicated item re-generation.
					 */
					static $item_cache = array();

					$items = '';

					//* Put the children in the right order.
					$tree_ids = array_reverse( $tree_ids, false );

					foreach ( $tree_ids as $position => $child_id ) {
						if ( in_array( $child_id, $assigned_ids, true ) ) {
							//* Cat has been assigned, continue.

							//* Fetch item from cache if available.
							if ( isset( $item_cache[ $child_id ] ) ) {
								$pos = $position + 2;
								$item_cache[ $child_id ]['pos'] = $pos;
								$items .= $this->make_breadcrumb( $item_cache[ $child_id ], true );
							} else {
								$pos = $position + 2;

								$cat = get_term_by( 'id', $child_id, $cat_type, OBJECT, 'raw' );
								$data = $this->get_term_data( $cat, $child_id );

								$id = json_encode( $this->the_url( '', array( 'get_custom_field' => false, 'external' => true, 'is_term' => true, 'term' => $cat ) ) );

								$cat_name = empty( $data['doctitle'] ) ? $cat->name : $data['doctitle'];
								$name = json_encode( $cat_name );

								$image = $this->schema_image( $child_id );

								//* Store in cache.
								$item_cache[ $child_id ] = array(
									'type'  => $item_type,
									'pos'   => (string) $pos,
									'id'    => $id,
									'name'  => $name,
									'image' => $image,
								);

								$items .= $this->make_breadcrumb( $item_cache[ $child_id ], true );
							}
						}
					}

					if ( $items ) {

						$items = $this->ld_json_breadcrumb_first( $item_type ) . $items . $this->ld_json_breadcrumb_last( $item_type, $pos, $post_id );

						//* Put it all together.
						$breadcrumbhelper = sprintf( '{"@context":%s,"@type":%s,"itemListElement":[%s]}', $context, $context_type, $items );
						$output .= '<script type="application/ld+json">' . $breadcrumbhelper . '</script>' . "\r\n";
					}
				} else {
					//* No assigned children, single term item.

					$items = '';

					//* The position of the current item is always static here.
					$pos = '2';

					$image = $this->schema_image( $tree_ids );

					//* $tree_ids is a single ID here.
					$cat = get_term_by( 'id', $tree_ids, $cat_type, OBJECT, 'raw' );
					$data = $this->get_term_data( $cat, $tree_ids );

					$id = json_encode( $this->the_url( '', array( 'get_custom_field' => false, 'is_term' => true, 'external' => true, 'term' => $cat ) ) );

					$cat_name = empty( $data['doctitle'] ) ? $cat->name : $data['doctitle'];
					$name = json_encode( $cat_name );

					$items .= sprintf( '{"@type":%s,"position":%s,"item":{"@id":%s,"name":%s,"image":%s}},', $item_type, (string) $pos, $id, $name, $image );

					if ( $items ) {

						$items = $this->ld_json_breadcrumb_first( $item_type ) . $items . $this->ld_json_breadcrumb_last( $item_type, $pos, $post_id );

						//* Put it all together.
						$breadcrumbhelper = sprintf( '{"@context":%s,"@type":%s,"itemListElement":[%s]}', $context, $context_type, $items );
						$output .= '<script type="application/ld+json">' . $breadcrumbhelper . '</script>' . "\r\n";
					}
				}
			}
		}

		return $output;
	}

	/**
	 * Generate page breadcrumb.
	 *
	 * @since 2.6.0
	 *
	 * @return string $output The breadcrumb script.
	 */
	public function ld_json_breadcrumbs_page() {

		$output = '';

		$page_id = $this->get_the_real_ID();

		//* Get ancestors.
		$parents = get_post_ancestors( $page_id );

		if ( $parents ) {

			$context = $this->schema_context();
			$context_type = $this->schema_breadcrumblist();
			$item_type = $this->schema_listitem();

			$items = '';

			$parents = array_reverse( $parents );

			foreach ( $parents as $position => $parent_id ) {
				$pos = $position + 2;

				$id = json_encode( $this->the_url( '', array( 'get_custom_field' => false, 'external' => true, 'id' => $parent_id ) ) );

				$custom_field_name = $this->get_custom_field( '_genesis_title', $parent_id );
				$parent_name = $custom_field_name ? $custom_field_name : $this->title( '', '', '', array( 'term_id' => $parent_id, 'meta' => true, 'get_custom_field' => false, 'placeholder' => true, 'notagline' => true, 'description_title' => true ) );

				$name = json_encode( $parent_name );
				$image = $this->schema_image( $parent_id );

				$breadcrumb = array(
					'type'  => $item_type,
					'pos'   => (string) $pos,
					'id'    => $id,
					'name'  => $name,
					'image' => $image,
				);

				$items .= $this->make_breadcrumb( $breadcrumb, true );
			}

			if ( $items ) {

				$items = $this->ld_json_breadcrumb_first( $item_type ) . $items . $this->ld_json_breadcrumb_last( $item_type, $pos, $page_id );

				//* Put it all together.
				$breadcrumbhelper = sprintf( '{"@context":%s,"@type":%s,"itemListElement":[%s]}', $context, $context_type, $items );
				$output = '<script type="application/ld+json">' . $breadcrumbhelper . '</script>' . "\r\n";
			}
		}

		return $output;
	}

	/**
	 * Return home page item for LD Json Breadcrumbs.
	 *
	 * @staticvar string $first_item.
	 *
	 * @since 2.4.2
	 *
	 * @param string|null $item_type the breadcrumb item type.
	 *
	 * @return string Home Breadcrumb item
	 */
	public function ld_json_breadcrumb_first( $item_type = null ) {

		static $first_item = null;

		if ( isset( $first_item ) )
			return $first_item;

		if ( is_null( $item_type ) )
			$item_type = json_encode( 'ListItem' );

		$id = json_encode( $this->the_home_url_from_cache() );

		$home_title = $this->get_option( 'homepage_title' );

		if ( $home_title ) {
			$custom_name = $home_title;
		} elseif ( $this->has_page_on_front() ) {
			$home_id = (int) get_option( 'page_on_front' );

			$custom_name = $this->get_custom_field( '_genesis_title', $home_id );
			$custom_name = $custom_name ? $custom_name : $this->get_blogname();
		} else {
			$custom_name = $this->get_blogname();
		}

		$custom_name = json_encode( $custom_name );
		$image = $this->schema_image( $this->get_the_front_page_ID(), true );

		$breadcrumb = array(
			'type'  => $item_type,
			'pos'   => '1',
			'id'    => $id,
			'name'  => $custom_name,
			'image' => $image,
		);

		return $first_item = $this->make_breadcrumb( $breadcrumb, true );
	}

	/**
	 * Return current page item for LD Json Breadcrumbs.
	 *
	 * @staticvar string $last_item.
	 *
	 * @since 2.4.2
	 *
	 * @param string $item_type the breadcrumb item type.
	 * @param int $pos Last known position.
	 * @param int $post_id The current Post ID
	 *
	 * @staticvar string $type The breadcrumb item type.
	 * @staticvar string $id The current post/page/archive url.
	 * @staticvar string $name The current post/page/archive title.
	 *
	 * @return string Last Breadcrumb item
	 */
	public function ld_json_breadcrumb_last( $item_type = null, $pos = null, $post_id = null ) {

		/**
		 * 2 (becomes 3) holds mostly true for single term items.
		 * This shouldn't run anyway. Pos should always be provided.
		 */
		if ( is_null( $pos ) )
			$pos = 2;

		//* Add current page.
		$pos = $pos + 1;

		if ( is_null( $item_type ) ) {
			static $type = null;

			if ( ! isset( $type ) )
				$type = json_encode( 'ListItem' );

			$item_type = $type;
		}

		if ( empty( $post_id ) )
			$post_id = $this->get_the_real_ID();

		static $id = null;
		static $name = null;

		if ( ! isset( $id ) )
			$id = json_encode( $this->the_url_from_cache() );

		if ( ! isset( $name ) ) {
			$custom_field = $this->get_custom_field( '_genesis_title', $post_id );
			$name = $custom_field ? $custom_field : $this->title( '', '', '', array( 'term_id' => $post_id, 'placeholder' => true, 'meta' => true, 'notagline' => true, 'description_title' => true ) );
			$name = json_encode( $name );
		}

		$image = $this->schema_image( $post_id, true );

		$breadcrumb = array(
			'type'  => $item_type,
			'pos'   => (string) $pos,
			'id'    => $id,
			'name'  => $name,
			'image' => $image,
		);

		return $this->make_breadcrumb( $breadcrumb, false );
	}

	/**
	 * Builds a breadcrumb.
	 *
	 * @since 2.6.0
	 * @param array $item : {
	 *		'type',
	 *		'pos',
	 *		'id',
	 *		'name'
	 * }
	 * @param bool $comma Whether to add a trailing comma.
	 *
	 * @return string The LD+Json breadcrumb.
	 */
	public function make_breadcrumb( $item, $comma = true ) {
		$comma = $comma ? ',' : '';
		return sprintf( '{"@type":%s,"position":%s,"item":{"@id":%s,"name":%s,"image":%s}}%s', $item['type'], $item['pos'], $item['id'], $item['name'], $item['image'], $comma );
	}

	/**
	 * Build breadcrumb trees.
	 *
	 * @since 2.6.0
	 *
	 * @param array The breadcrumb trees, with the key as parent.
	 * @param array $previous_tree A previous set tree to compare to, if set.
	 *
	 * @return trees in order.
	 */
	protected function build_breadcrumb_trees( $kittens, array $previous_tree = array() ) {

		$trees = $previous_tree;

		foreach ( $kittens as $parent => $kitten ) {
			if ( empty( $kitten ) ) {
				//* Final cat.
				$trees[] = $parent;
			} else {
				if ( 1 === count( $kitten ) ) {
					//* Single tree.
					$trees[] = array( reset( $kitten ), $parent );
				} else {
					//* Nested categories.
					$add = array();

					foreach ( $kitten as $kit => $kat ) {
						//* Only add if non-existent in $trees.
						if ( ! in_array( $kat, $trees, true ) )
							$add[] = $kat;
					}

					//* Put children in right order.
					$add = array_reverse( $add );

					$trees[] = array_merge( $add, array( $parent ) );
				}
			}
		}

		return $trees;
	}

	/**
	 * Return LD+Json Knowledge Graph helper.
	 *
	 * @since 2.2.8
	 *
	 * @return string LD+json Knowledge Graph helper.
	 */
	public function ld_json_knowledge() {

		if ( false === $this->enable_ld_json_knowledge() )
			return '';

		$knowledge_type = $this->get_option( 'knowledge_type' );

		/**
		 * Forgot to add this.
		 * @since 2.4.3
		 */
		$knowledge_name = $this->get_option( 'knowledge_name' );
		$knowledge_name = $knowledge_name ? $knowledge_name : $this->get_blogname();

		$context = $this->schema_context();
		$type = json_encode( ucfirst( $knowledge_type ) );
		$name = json_encode( $knowledge_name );
		$url = json_encode( esc_url( home_url( '/' ) ) );

		$logo = '';

		if ( $this->get_option( 'knowledge_logo' ) && 'organization' === $knowledge_type ) {
			$icon = $this->site_icon();

			if ( $icon ) {
				$logourl = esc_url_raw( $icon );

				//* Add trailing comma
				$logo = '"logo":' . json_encode( $logourl ) . ',';
			}
		}

		/**
		 * Applies filters 'the_seo_framework_json_options' : array The option names
		 * @since ???
		 * @todo Document.
		 */
		$options = (array) apply_filters( 'the_seo_framework_json_options', array(
			'knowledge_facebook',
			'knowledge_twitter',
			'knowledge_gplus',
			'knowledge_instagram',
			'knowledge_youtube',
			'knowledge_linkedin',
			'knowledge_pinterest',
			'knowledge_soundcloud',
			'knowledge_tumblr',
		) );

		$sameurls = '';
		$comma = ',';

		//* Put the urls together from the options.
		if ( is_array( $options ) ) {
			foreach ( $options as $option ) {
				$the_option = $this->get_option( $option );

				if ( '' !== $the_option )
					$sameurls .= json_encode( $the_option ) . $comma;
			}
		}

		//* Remove trailing comma
		$sameurls = rtrim( $sameurls, $comma );
		$json = '';

		if ( $sameurls )
			$json = sprintf( '{"@context":%s,"@type":%s,"name":%s,"url":%s,%s"sameAs":[%s]}', $context, $type, $name, $url, $logo, $sameurls );

		$output = '';
		if ( $json )
			$output = '<script type="application/ld+json">' . $json . '</script>' . "\r\n";

		return $output;
	}

	/**
	 * Generate Site Name LD+Json script.
	 *
	 * @since 2.6.0
	 *
	 * @return string The LD+JSon Site Name script.
	 */
	public function ld_json_name() {

		if ( false === $this->enable_ld_json_sitename() )
			return '';

		$context = $this->schema_context();
		$webtype = $this->schema_type();
		$url = $this->schema_home_url();
		$name = $this->schema_blog_name();

		$json = sprintf( '{"@context":%s,"@type":%s,"name":%s,"url":%s}', $context, $webtype, $name, $url );

		$output = '';
		if ( $json )
			$output = '<script type="application/ld+json">' . $json . '</script>' . "\r\n";

		return $output;
	}

	/**
	 * Determines if breadcrumbs scripts are enabled.
	 *
	 * @since 2.6.0
	 * @staticvar bool $cache
	 *
	 * @return bool
	 */
	public function enable_ld_json_breadcrumbs() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		/**
		 * Applies filters the_seo_framework_json_breadcrumb_output
		 * @since 2.4.2
		 */
		$filter = (bool) apply_filters( 'the_seo_framework_json_breadcrumb_output', true );
		$option = $this->is_option_checked( 'ld_json_breadcrumbs' );

		return $cache = $filter && $option;
	}

	/**
	 * Determines if sitename script is enabled.
	 *
	 * @since 2.6.0
	 * @staticvar bool $cache
	 *
	 * @return bool
	 */
	public function enable_ld_json_sitename() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		/**
		 * Applies filters the_seo_framework_json_sitename_output
		 * @since 2.6.0
		 */
		$filter = (bool) apply_filters( 'the_seo_framework_json_sitename_output', true );
		$option = $this->is_option_checked( 'ld_json_sitename' );

		return $cache = $filter && $option;
	}

	/**
	 * Determines if searchbox script is enabled.
	 *
	 * @since 2.6.0
	 * @staticvar bool $cache
	 *
	 * @return bool
	 */
	public function enable_ld_json_searchbox() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		/**
		 * Applies filters 'the_seo_framework_json_search_output'
		 * @since 2.3.9
		 */
		$filter = (bool) apply_filters( 'the_seo_framework_json_search_output', true );
		$option = $this->is_option_checked( 'ld_json_searchbox' );

		return $cache = $filter && $option;
	}

	/**
	 * Determines if Knowledge Graph Script is enabled.
	 *
	 * @since 2.6.5
	 * @staticvar bool $cache
	 *
	 * @return bool
	 */
	public function enable_ld_json_knowledge() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		/**
		 * Applies filters 'the_seo_framework_json_search_output'
		 * @since 2.6.5
		 */
		$filter = (bool) apply_filters( 'the_seo_framework_json_knowledge_output', true );
		$option = $this->is_option_checked( 'knowledge_output' );

		return $cache = $filter && $option;
	}
}
