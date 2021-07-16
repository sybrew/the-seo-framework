<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Generate
 * @subpackage The_SEO_Framework\Getters
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2021 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\Generate
 *
 * Generates general SEO data based on content.
 *
 * @since 2.8.0
 */
class Generate extends User_Data {

	/**
	 * Fixes generation arguments, to prevent ID conflicts with taxonomies.
	 *
	 * @since 3.1.0
	 * @since 4.1.0 1: Improved performance by testing for null first.
	 *              2: Improved performance by testing argument keys prior array merge.
	 * @internal
	 *
	 * @param array|int|null $args The arguments, passed by reference.
	 */
	protected function fix_generation_args( &$args ) {

		if ( null === $args ) return;

		if ( \is_array( $args ) ) {
			if ( ! isset( $args['id'], $args['taxonomy'] ) ) {
				$args = array_merge(
					[
						'id'       => 0,
						'taxonomy' => '',
					],
					$args
				);
			}
		} elseif ( is_numeric( $args ) ) {
			$args = [
				'id'       => (int) $args,
				'taxonomy' => '',
			];
		} else {
			$args = null;
		}
	}

	/**
	 * Returns the `noindex`, `nofollow`, `noarchive` robots meta code array.
	 *
	 * @since 4.1.4
	 *
	 * @param array|null $args   The query arguments. Accepts 'id' and 'taxonomy'.
	 * @param null       $get    Reserved.
	 * @param int <bit>  $ignore The ignore level. {
	 *    0 = 0b00: Ignore nothing.
	 *    1 = 0b01: Ignore protection. (\The_SEO_Framework\ROBOTS_IGNORE_PROTECTION)
	 *    2 = 0b10: Ignore post/term setting. (\The_SEO_Framework\ROBOTS_IGNORE_SETTINGS)
	 *    3 = 0b11: Ignore protection and post/term setting.
	 * }
	 * @return array Only values actualized for display: {
	 *    string index : string value
	 * }
	 */
	public function generate_robots_meta( $args = null, $get = null, $ignore = 0b00 ) {

		if ( null === $args ) {
			$_meta = $this->get_robots_meta_by_query( $ignore );

			if ( $this->is_query_exploited() ) {
				$_meta['noindex']  = true;
				$_meta['nofollow'] = true;
			}
		} else {
			$this->fix_generation_args( $args );
			$_meta = $this->get_robots_meta_by_args( $args, $ignore );
		}

		$meta = [
			'noindex'           => '',
			'nofollow'          => '',
			'noarchive'         => '',
			'max_snippet'       => '',
			'max_image_preview' => '',
			'max_video_preview' => '',
		];

		foreach (
			array_intersect_key( $_meta, array_flip( [ 'noindex', 'nofollow', 'noarchive' ] ) )
			as $k => $v
		) $v and $meta[ $k ] = $k;

		foreach (
			array_intersect_key( $_meta, array_flip( [ 'max_snippet', 'max_image_preview', 'max_video_preview' ] ) )
			as $k => $v
		) false !== $v and $meta[ $k ] = str_replace( '_', '-', $k ) . ":$v";

		/**
		 * Filters the front-end robots array, and strips empty indexes thereafter.
		 *
		 * @since 2.6.0
		 * @since 4.0.0 Added two parameters ($args and $ignore).
		 * @since 4.0.2 Now contains the copyright diretive values.
		 * @since 4.0.3 Changed `$meta` key `max_snippet_length` to `max_snippet`
		 *
		 * @param array      $meta The current robots meta. {
		 *     'noindex'           : 'noindex'|''
		 *     'nofollow'          : 'nofollow'|''
		 *     'noarchive'         : 'noarchive'|''
		 *     'max_snippet'       : 'max-snippet:<int>'|''
		 *     'max_image_preview' : 'max-image-preview:<string>'|''
		 *     'max_video_preview' : 'max-video-preview:<string>'|''
		 * }
		 * @param array|null $args The query arguments. Contains 'id' and 'taxonomy'.
		 *                         Is null when query is autodetermined.
		 * @param int <bit>  $ignore The ignore level. {
		 *    0 = 0b00: Ignore nothing.
		 *    1 = 0b01: Ignore protection. (\The_SEO_Framework\ROBOTS_IGNORE_PROTECTION)
		 *    2 = 0b10: Ignore post/term setting. (\The_SEO_Framework\ROBOTS_IGNORE_SETTINGS)
		 *    3 = 0b11: Ignore protection and post/term setting.
		 * }
		 */
		return array_filter(
			(array) \apply_filters_ref_array(
				'the_seo_framework_robots_meta_array',
				[
					$meta,
					$args,
					$ignore,
				]
			)
		);
	}

	/**
	 * Generates the `noindex`, `nofollow`, `noarchive` robots meta code array from query.
	 *
	 * @since 4.0.0
	 * @since 4.0.2 Added new copyright directive tags.
	 * @since 4.0.3 Changed `max_snippet_length` to `max_snippet`
	 * @since 4.0.5 1. The `$post_type` test now uses a real query ID, instead of `$GLOBALS['post']`;
	 *                 mitigating issues with singular-archives pages (blog, shop, etc.).
	 *              2. Now disregards empty blog pages for automatic `noindex`; although this protection is necessary,
	 *                 it can not be reflected in the SEO Bar.
	 * @since 4.1.0 Now uses the new taxonomy robots settings.
	 * @global \WP_Query $wp_query
	 *
	 * @param int <bit> $ignore The ignore level. {
	 *    0 = 0b00: Ignore nothing.
	 *    1 = 0b01: Ignore protection. (\The_SEO_Framework\ROBOTS_IGNORE_PROTECTION)
	 *    2 = 0b10: Ignore post/term meta settings. (\The_SEO_Framework\ROBOTS_IGNORE_SETTINGS)
	 *    3 = 0b11: Ignore protection and post/term meta setting.
	 * }
	 * @return array|null robots : {
	 *    bool                   'noindex'
	 *    bool                   'nofollow'
	 *    bool                   'noarchive'
	 *    false|int <R>=-1<=600> 'max_snippet'
	 *    false|string           'max_image_preview'
	 *    fasle|int <R>=-1<=600> 'max_video_preview'
	 * }
	 */
	protected function get_robots_meta_by_query( $ignore = 0b00 ) {

		$noindex   = (bool) $this->get_option( 'site_noindex' );
		$nofollow  = (bool) $this->get_option( 'site_nofollow' );
		$noarchive = (bool) $this->get_option( 'site_noarchive' );

		$max_snippet = $max_image_preview = $max_video_preview = false;

		if ( $this->get_option( 'set_copyright_directives' ) ) {
			$max_snippet       = $this->get_option( 'max_snippet_length' );
			$max_image_preview = $this->get_option( 'max_image_preview' );
			$max_video_preview = $this->get_option( 'max_video_preview' );
		}

		// Check homepage SEO settings, set noindex, nofollow and noarchive
		if ( $this->is_real_front_page() ) {
			foreach ( [ 'noindex', 'nofollow', 'noarchive' ] as $r )
				$$r = $$r || $this->get_option( "homepage_$r" );

			if ( ! ( $ignore & ROBOTS_IGNORE_PROTECTION ) ) :
				$noindex = $noindex
					|| ( $this->get_option( 'home_paged_noindex' ) && ( $this->page() > 1 || $this->paged() > 1 ) );
			endif;
		} else {
			global $wp_query;

			if ( $this->is_singular_archive() ) {
				/**
				 * Pagination overflow protection via 404 test.
				 *
				 * When there are no posts, the first page will NOT relay 404;
				 * which is exactly as intended. All other pages will relay 404.
				 *
				 * We do not test the post_count here, because we want to have
				 * the first page indexable via user-intent only.
				 */
				$noindex = $noindex || $this->is_404();
			} else {
				/**
				 * Check for 404, or if archive is empty: set noindex for those.
				 *
				 * Don't check this on the homepage. The homepage is sacred in this regard,
				 * because page builders and templates can and will take over.
				 *
				 * Don't use empty(), null is regarded as indexable.
				 */
				if ( isset( $wp_query->post_count ) && ! $wp_query->post_count ) {
					/**
					 * We recommend using this filter ONLY for archives that have useful content but no "posts" attached.
					 * For example: a specially custom-developed author page for an author that never published a post.
					 *
					 * This filter won't run when a few other conditions for noindex have been met.
					 *
					 * @since 4.1.4
					 * @link <https://github.com/sybrew/the-seo-framework/issues/194#issuecomment-864298702>
					 * @param bool $noindex Whether to enable no posts protection.
					 */
					$noindex = $noindex || \apply_filters( 'the_seo_framework_enable_noindex_no_posts', true );
				}
			}

			if (
				! $noindex
				&& $this->get_option( 'paged_noindex' )
				&& ( $this->is_archive() || $this->is_singular_archive() )
				&& $this->paged() > 1
			) {
				$noindex = true;
			}

			if ( $this->is_archive() ) {
				if ( $this->is_author() ) {
					foreach ( [ 'noindex', 'nofollow', 'noarchive' ] as $r )
						$$r = $$r || $this->get_option( "author_$r" );
				} elseif ( $this->is_date() ) {
					foreach ( [ 'noindex', 'nofollow', 'noarchive' ] as $r )
						$$r = $$r || $this->get_option( "date_$r" );
				}
			} elseif ( $this->is_search() ) {
				foreach ( [ 'noindex', 'nofollow', 'noarchive' ] as $r )
					$$r = $$r || $this->get_option( "search_$r" );
			}
		}

		if ( $this->is_archive() ) {
			$taxonomy = $this->get_current_taxonomy();

			$_post_type_meta = [];
			// Store values from each post type bound to the taxonomy.
			foreach ( $this->get_post_types_from_taxonomy() as $post_type ) {
				foreach ( [ 'noindex', 'nofollow', 'noarchive' ] as $r ) {
					// SECURITY: Put in array to circumvent GLOBALS injection in sequenting loop.
					$_post_type_meta[ $r ][] = $$r || $this->is_post_type_robots_set( $r, $post_type );
				}
			}
			// Only enable if all post types have the value ticked.
			foreach ( $_post_type_meta as $r => $_values )
				$$r = $$r || ! \in_array( false, $_values, true );

			foreach ( [ 'noindex', 'nofollow', 'noarchive' ] as $r )
				$$r = $$r || $this->is_taxonomy_robots_set( $r, $taxonomy );

			if ( ! ( $ignore & ROBOTS_IGNORE_SETTINGS ) ) :
				$term_meta = $this->get_current_term_meta();

				foreach ( [ 'noindex', 'nofollow', 'noarchive' ] as $r ) {
					if ( isset( $term_meta[ $r ] ) ) {
						// Test qubit
						$$r = ( $$r | (int) $term_meta[ $r ] ) > .33;
					}
				}
			endif;
		} elseif ( $this->is_singular() ) {
			$post_type = $this->get_current_post_type();

			foreach ( [ 'noindex', 'nofollow', 'noarchive' ] as $r )
				$$r = $$r || $this->is_post_type_robots_set( $r, $post_type );

			if ( ! ( $ignore & ROBOTS_IGNORE_SETTINGS ) ) :
				$post_meta = [
					'noindex'   => $this->get_post_meta_item( '_genesis_noindex' ),
					'nofollow'  => $this->get_post_meta_item( '_genesis_nofollow' ),
					'noarchive' => $this->get_post_meta_item( '_genesis_noarchive' ),
				];

				foreach ( [ 'noindex', 'nofollow', 'noarchive' ] as $r ) {
					// Test qubit
					$$r = ( $$r | (int) $post_meta[ $r ] ) > .33;
				}
			endif;

			// I hate this reiteration of the very same code as above... but, homepage may not always be singular.
			// The conditions below MUST overwrite this, too. So, this is the perfect placement.
			if ( $this->is_real_front_page() ) {
				if ( ! ( $ignore & ROBOTS_IGNORE_PROTECTION ) ) :
					$noindex = $noindex
						|| ( $this->get_option( 'home_paged_noindex' ) && ( $this->page() > 1 || $this->paged() > 1 ) );
				endif;
			}

			// Overwrite and ignore the user's settings, regardless; unless ignore is set.
			if ( ! ( $ignore & ROBOTS_IGNORE_PROTECTION ) ) :
				$noindex = $noindex || $this->is_protected( $this->get_the_real_ID() );
			endif;

			/**
			 * Noindex on comment pagination.
			 * Overwrites and ignores the user's settings, always.
			 *
			 * N.B. WordPress protects this query variable with options 'page_comments'
			 * and 'default_comments_page' via `redirect_canonical()`, so we don't have to.
			 * For reference, it fires `remove_query_arg( 'cpage', $redirect['query'] )`;
			 */
			if ( (int) \get_query_var( 'cpage', 0 ) > 0 ) {
				/**
				 * We do not recommend using this filter as it'll likely get those pages flagged as
				 * duplicated by Google anyway; unless the theme strips or trims the content.
				 *
				 * This filter won't run when other conditions for noindex have been met.
				 *
				 * @since 4.0.5
				 * @param bool $noindex Whether to enable comment pagination protection.
				 */
				$noindex = $noindex || \apply_filters( 'the_seo_framework_enable_noindex_comment_pagination', true );
			}
		}

		return compact( 'noindex', 'nofollow', 'noarchive', 'max_snippet', 'max_image_preview', 'max_video_preview' );
	}

	/**
	 * Generates the `noindex`, `nofollow`, `noarchive` robots meta code array from arguments.
	 *
	 * Note that the home-as-blog page can be used for this method.
	 *
	 * @since 4.0.0
	 * @since 4.0.2 Added new copyright directive tags.
	 * @since 4.0.3 Changed `max_snippet_length` to `max_snippet`
	 * @since 4.1.0 Now uses the new taxonomy robots settings.
	 *
	 * @param array|null $args   The query arguments. Accepts 'id' and 'taxonomy'.
	 * @param int <bit>  $ignore The ignore level. {
	 *    0 = 0b00: Ignore nothing.
	 *    1 = 0b01: Ignore protection. (\The_SEO_Framework\ROBOTS_IGNORE_PROTECTION)
	 *    2 = 0b10: Ignore post/term meta setting. (\The_SEO_Framework\ROBOTS_IGNORE_SETTINGS)
	 *    3 = 0b11: Ignore protection and post/term meta setting.
	 * }
	 * @return array|null robots : {
	 *    bool              'noindex'
	 *    bool              'nofollow'
	 *    bool              'noarchive'
	 *    false|int <R>=-1> 'max_snippet'
	 *    false|string      'max_image_preview'
	 *    fasle|int <R>=-1> 'max_video_preview'
	 * }
	 */
	protected function get_robots_meta_by_args( $args, $ignore = 0b00 ) {

		$noindex   = (bool) $this->get_option( 'site_noindex' );
		$nofollow  = (bool) $this->get_option( 'site_nofollow' );
		$noarchive = (bool) $this->get_option( 'site_noarchive' );

		$max_snippet = $max_image_preview = $max_video_preview = false;

		if ( $this->get_option( 'set_copyright_directives' ) ) {
			$max_snippet       = $this->get_option( 'max_snippet_length' );
			$max_image_preview = $this->get_option( 'max_image_preview' );
			$max_video_preview = $this->get_option( 'max_video_preview' );
		}

		// Put outside the loop, id=0 may be used here for home-as-blog.
		if ( ! $args['taxonomy'] && $this->is_real_front_page_by_id( $args['id'] ) ) {
			$noindex   = $noindex || $this->get_option( 'homepage_noindex' );
			$nofollow  = $nofollow || $this->get_option( 'homepage_nofollow' );
			$noarchive = $noarchive || $this->get_option( 'homepage_noarchive' );
		}

		if ( $args['taxonomy'] ) {
			$term = \get_term( $args['id'], $args['taxonomy'] );
			/**
			 * Check if archive is empty: set noindex for those.
			 */
			if ( empty( $term->count ) )
				$noindex = true;

			$_post_type_meta = [];
			// Store values from each post type bound to the taxonomy.
			foreach ( $this->get_post_types_from_taxonomy( $args['taxonomy'] ) as $post_type ) {
				foreach ( [ 'noindex', 'nofollow', 'noarchive' ] as $r ) {
					// SECURITY: Put in array to circumvent GLOBALS injection.
					$_post_type_meta[ $r ][] = $$r || $this->is_post_type_robots_set( $r, $post_type );
				}
			}
			// Only enable if all post types have the value ticked.
			foreach ( $_post_type_meta as $r => $_values ) {
				$$r = $$r || ! \in_array( false, $_values, true );
			}

			foreach ( [ 'noindex', 'nofollow', 'noarchive' ] as $r ) {
				$$r = $$r || $this->is_taxonomy_robots_set( $r, $args['taxonomy'] );
			}

			if ( ! ( $ignore & ROBOTS_IGNORE_SETTINGS ) ) :
				$term_meta = $this->get_term_meta( $args['id'] );

				foreach ( [ 'noindex', 'nofollow', 'noarchive' ] as $r ) {
					if ( isset( $term_meta[ $r ] ) ) {
						// Test qubit
						$$r = ( $$r | (int) $term_meta[ $r ] ) > .33;
					}
				}
			endif;
		} elseif ( $args['id'] ) {
			$post_type = \get_post_type( $args['id'] );
			foreach ( [ 'noindex', 'nofollow', 'noarchive' ] as $r ) {
				$$r = $$r || $this->is_post_type_robots_set( $r, $post_type );
			}

			if ( ! ( $ignore & ROBOTS_IGNORE_SETTINGS ) ) :
				$post_meta = [
					'noindex'   => $this->get_post_meta_item( '_genesis_noindex', $args['id'] ),
					'nofollow'  => $this->get_post_meta_item( '_genesis_nofollow', $args['id'] ),
					'noarchive' => $this->get_post_meta_item( '_genesis_noarchive', $args['id'] ),
				];

				foreach ( [ 'noindex', 'nofollow', 'noarchive' ] as $r ) {
					// Test qubit
					$$r = ( $$r | (int) $post_meta[ $r ] ) > .33;
				}
			endif;

			// Overwrite and ignore the user's settings, regardless; unless ignore is set.
			if ( ! ( $ignore & ROBOTS_IGNORE_PROTECTION ) ) :
				$noindex = $noindex || $this->is_protected( $args['id'] );
			endif;
		}

		return compact( 'noindex', 'nofollow', 'noarchive', 'max_snippet', 'max_image_preview', 'max_video_preview' );
	}

	/**
	 * Determines if the post type has a robots value set.
	 *
	 * @since 3.1.0
	 * @since 4.0.5 The `$post_type` fallback now uses a real query ID, instead of `$GLOBALS['post']`;
	 *              mitigating issues with singular-archives pages (blog, shop, etc.).
	 * @since 4.1.1 Now tests for not empty, instead of isset. We no longer support PHP 5.4 since v4.0.0.
	 *
	 * @param string $type      Accepts 'noindex', 'nofollow', 'noarchive'.
	 * @param string $post_type The post type, optional. Leave empty to autodetermine type.
	 * @return bool True if noindex, nofollow, or noarchive is set; false otherwise.
	 */
	public function is_post_type_robots_set( $type, $post_type = '' ) {
		return ! empty(
			$this->get_option(
				$this->get_robots_post_type_option_id( $type )
			)[ $post_type ?: $this->get_current_post_type() ]
		);
	}

	/**
	 * Determines if the taxonomy has a robots value set.
	 *
	 * @since 4.1.0
	 * @since 4.1.1 Now tests for not empty, instead of isset. We no longer support PHP 5.4 since v4.0.0.
	 *
	 * @param string $type     Accepts 'noindex', 'nofollow', 'noarchive'.
	 * @param string $taxonomy The taxonomy, optional. Leave empty to autodetermine type.
	 * @return bool True if noindex, nofollow, or noarchive is set; false otherwise.
	 */
	public function is_taxonomy_robots_set( $type, $taxonomy = '' ) {
		return ! empty(
			$this->get_option(
				$this->get_robots_taxonomy_option_id( $type )
			)[ $taxonomy ?: $this->get_current_taxonomy() ]
		);
	}

	/**
	 * Returns cached and parsed separator option.
	 *
	 * @since 2.3.9
	 * @since 3.1.0 1. Removed caching.
	 *              2. Removed escaping parameter.
	 * @since 4.0.0 No longer converts the `dash` separator option.
	 * @since 4.0.5 1. Now utilizes the predefined separator list, instead of guessing the output.
	 *              2. The default fallback value is now a hyphen.
	 *
	 * @param string $type The separator type. Used to fetch option.
	 * @return string The separator.
	 */
	public function get_separator( $type = 'title' ) {

		$sep_option = $this->get_option( $type . '_separator' );
		$sep_list   = $this->get_separator_list();

		return isset( $sep_list[ $sep_option ] ) ? $sep_list[ $sep_option ] : '&#x2d;';
	}

	/**
	 * Fetches blogname (site title).
	 * Memoizes the return value.
	 *
	 * @since 2.5.2
	 * TODO add filter?
	 *
	 * @return string $blogname The escaped and sanitized blogname.
	 */
	public function get_blogname() {
		static $blogname;
		return isset( $blogname ) ? $blogname : $blogname = trim( \get_bloginfo( 'name', 'display' ) );
	}

	/**
	 * Fetch blog description.
	 * Memoizes the return value.
	 *
	 * @since 2.5.2
	 * @since 3.0.0 No longer returns untitled when empty, instead, it just returns an empty string.
	 *
	 * @return string $blogname The escaped and sanitized blog description.
	 */
	public function get_blogdescription() {
		static $description;
		return isset( $description ) ? $description : $description = trim( \get_bloginfo( 'description', 'display' ) );
	}

	/**
	 * Matches WordPress locales.
	 * If not matched, it will calculate a locale.
	 *
	 * @since 2.5.2
	 *
	 * @param string $match the locale to match. Defaults to WordPress locale.
	 * @return string Facebook acceptable OG locale.
	 */
	public function fetch_locale( $match = '' ) {

		if ( ! $match )
			$match = \get_locale();

		$match_len     = \strlen( $match );
		$valid_locales = $this->fb_locales();

		if ( $match_len > 5 ) {
			$match_len = 5;
			// More than standard-full locale ID is used. Make it just full.
			$match = substr( $match, 0, $match_len );
		}

		if ( 5 === $match_len ) {
			// Full locale is used.

			if ( \in_array( $match, $valid_locales, true ) )
				return $match;

			// Convert to only language portion.
			$match_len = 2;
			$match     = substr( $match, 0, $match_len );
		}

		if ( 2 === $match_len ) {
			// Only a language key is provided.

			// Find first matching key.
			$key = array_search( $match, $this->language_keys(), true );

			if ( $key ) {
				return $valid_locales[ $key ];
			}
		}

		// Return default locale.
		return 'en_US';
	}

	/**
	 * Generates the Open Graph type based on query status.
	 *
	 * @since 2.7.0
	 *
	 * @return string The Open Graph type.
	 */
	public function generate_og_type() {

		if ( $this->is_product() ) {
			$type = 'product';
		} elseif ( $this->is_single() && $this->get_image_from_cache() ) {
			$type = 'article';
		} elseif ( $this->is_author() ) {
			$type = 'profile';
		} else {
			$type = 'website';
		}

		return $type;
	}

	/**
	 * Returns Open Graph type value.
	 * Memoizes the return value.
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public function get_og_type() {

		static $type = null;

		if ( isset( $type ) )
			return $type;

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @param string $type The OG type.
		 * @param int    $id   The page/term/object ID.
		 */
		return $type = (string) \apply_filters_ref_array(
			'the_seo_framework_ogtype_output',
			[
				$this->generate_og_type(),
				$this->get_the_real_ID(),
			]
		);
	}

	/**
	 * Returns the post's modified time.
	 * Memoizes the return value.
	 *
	 * @since 4.1.4
	 *
	 * @return string The current post's modified time
	 */
	public function get_modified_time() {

		static $time = null;

		if ( isset( $time ) )
			return $time;

		$id = $this->get_the_real_ID();

		$post              = \get_post( $id );
		$post_modified_gmt = $post->post_modified_gmt;

		if ( '0000-00-00 00:00:00' === $post_modified_gmt )
			return $time = '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @param string $time The article modified time.
		 * @param int    $id   The current page or term ID.
		 */
		return $time = (string) \apply_filters_ref_array(
			'the_seo_framework_modifiedtime_output',
			[
				$this->gmt2date( $this->get_timestamp_format(), $post_modified_gmt ),
				$id,
			]
		);
	}

	/**
	 * @since 3.1.0
	 * @TODO use this
	 * @see get_available_twitter_cards
	 * @ignore
	 */
	public function get_available_open_graph_types() { }

	/**
	 * Generates the Twitter Card type.
	 *
	 * @since 2.7.0
	 * @since 2.8.2 Now considers description output.
	 * @since 2.9.0 Now listens to $this->get_available_twitter_cards().
	 * @since 3.1.0 Now inherits filter `the_seo_framework_twittercard_output`.
	 * @since 4.1.4 Removed needless preprocessing of the option.
	 *
	 * @return string The Twitter Card type. When no social title is found, an empty string will be returned.
	 */
	public function generate_twitter_card_type() {

		$available_cards = $this->get_available_twitter_cards();

		if ( ! $available_cards ) return '';

		$option = $this->get_option( 'twitter_card' );

		// Option is equal to found cards. Output option.
		if ( \in_array( $option, $available_cards, true ) ) {
			if ( 'summary_large_image' === $option ) {
				$type = 'summary_large_image';
			} elseif ( 'summary' === $option ) {
				$type = 'summary';
			}
		} else {
			$type = 'summary';
		}

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @param string $card The generated Twitter card type.
		 * @param int    $id   The current page or term ID.
		 */
		return (string) \apply_filters_ref_array(
			'the_seo_framework_twittercard_output',
			[
				$type,
				$this->get_the_real_ID(),
			]
		);
	}

	/**
	 * Determines which Twitter cards can be used.
	 * Memoizes the return value.
	 *
	 * @since 2.9.0
	 * @since 4.0.0 1. Now only asserts the social titles as required.
	 *              2. Now always returns an array, instead of a boolean (false) on failure.
	 *
	 * @return array False when it shouldn't be used. Array of available cards otherwise.
	 */
	public function get_available_twitter_cards() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		if ( ! $this->get_twitter_title() ) {
			$retval = [];
		} else {
			$retval = [ 'summary_large_image', 'summary' ];
		}

		/**
		 * @since 2.9.0
		 * @param array $retval The available Twitter cards. Use empty array to invalidate Twitter card.
		 */
		$retval = (array) \apply_filters( 'the_seo_framework_available_twitter_cards', $retval );

		return $cache = $retval ?: [];
	}

	/**
	 * List of title separators.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Is now filterable.
	 * @since 4.0.0 Removed the dash key.
	 * @since 4.0.5 Added back the hyphen.
	 *
	 * @return array Title separators.
	 */
	public function get_separator_list() {
		/**
		 * @since 3.1.0
		 * @since 4.0.0 Removed the hyphen (then known as 'dash') key.
		 * @since 4.0.5 Reintroduced hyphen.
		 * @param array $list The separator list in { option_name > display_value } format.
		 *                    The option name should be translatable within `&...;` tags.
		 *                    'pipe' is excluded from this rule.
		 */
		return (array) \apply_filters(
			'the_seo_framework_separator_list',
			[
				'hyphen' => '&#x2d;',
				'pipe'   => '|',
				'ndash'  => '&ndash;',
				'mdash'  => '&mdash;',
				'bull'   => '&bull;',
				'middot' => '&middot;',
				'lsaquo' => '&lsaquo;',
				'rsaquo' => '&rsaquo;',
				'frasl'  => '&frasl;',
				'laquo'  => '&laquo;',
				'raquo'  => '&raquo;',
				'le'     => '&le;',
				'ge'     => '&ge;',
				'lt'     => '&lt;',
				'gt'     => '&gt;',
			]
		);
	}

	/**
	 * Returns array of Twitter Card Types
	 *
	 * @since 2.6.0
	 *
	 * @return array Twitter Card types.
	 */
	public function get_twitter_card_types() {
		return [
			'summary'             => 'summary',
			'summary_large_image' => 'summary-large-image',
		];
	}

	/**
	 * Returns the redirect URL, if any.
	 *
	 * @since 4.1.4
	 *
	 * @param null|array $args The redirect URL arguments, leave null to autodetermine query : {
	 *    int    $id               The Post, Page or Term ID to generate the URL for.
	 *    string $taxonomy         The taxonomy.
	 * }
	 * @return string The canonical URL if found, empty string otherwise.
	 */
	public function get_redirect_url( $args = null ) {

		$url = '';

		if ( null === $args ) {
			if ( $this->is_singular() ) {
				$url = $this->get_post_meta_item( 'redirect' ) ?: '';
			} elseif ( $this->is_term_meta_capable() ) {
				$url = $this->get_term_meta_item( 'redirect' ) ?: '';
			}
		} else {
			$this->fix_generation_args( $args );
			if ( ! $args['taxonomy'] ) {
				$url = $this->get_post_meta_item( 'redirect', $args['id'] ) ?: '';
			} else {
				$url = $this->get_term_meta_item( 'redirect', $args['id'] ) ?: '';
			}
		}

		return $url;
	}
}
