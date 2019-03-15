<?php
/**
 * @package The_SEO_Framework\Classes
 */
namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
	 * @internal
	 *
	 * @param array|int|null $args The arguments, passed by reference.
	 */
	protected function fix_generation_args( &$args ) {
		if ( is_array( $args ) ) {
			$args = array_merge( [
				'id'       => 0,
				'taxonomy' => '',
			], $args );
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
	 * @since 2.2.2
	 * @since 2.2.4 Added robots SEO settings check.
	 * @since 2.2.8 Added check for empty archives.
	 * @since 2.8.0 Added check for protected/private posts.
	 * @since 3.0.0 1: Removed noodp.
	 *              2: Improved efficiency by grouping if statements.
	 * @since 3.1.0 1. Simplified statements, often (not always) speeding things up.
	 *              2. Now checks for wc_shop and blog types for pagination.
	 *              3. Removed noydir.
	 * @global \WP_Query $wp_query
	 *
	 * @return array|null robots
	 */
	public function robots_meta() {

		//* Defaults
		$meta = [
			'noindex'   => $this->get_option( 'site_noindex' ) ? 'noindex' : '',
			'nofollow'  => $this->get_option( 'site_nofollow' ) ? 'nofollow' : '',
			'noarchive' => $this->get_option( 'site_noarchive' ) ? 'noarchive' : '',
		];

		//* Check homepage SEO settings, set noindex, nofollow and noarchive
		if ( $this->is_real_front_page() ) {
			$meta['noindex']   = $this->get_option( 'homepage_noindex' ) ? 'noindex' : $meta['noindex'];
			$meta['nofollow']  = $this->get_option( 'homepage_nofollow' ) ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = $this->get_option( 'homepage_noarchive' ) ? 'noarchive' : $meta['noarchive'];

			if ( $this->get_option( 'home_paged_noindex' ) && ( $this->page() > 1 || $this->paged() > 1 ) ) {
				$meta['noindex'] = 'noindex';
			}
		} else {
			global $wp_query;

			/**
			 * Check for 404, or if archive is empty: set noindex for those.
			 * Don't check this on the homepage. The homepage is sacred in this regard,
			 * because page builders and templates likely take over.
			 * @since 2.2.8
			 *
			 * @todo maybe create option
			 * @priority so low... 3.0.0+
			 */
			if ( isset( $wp_query->post_count ) && 0 === $wp_query->post_count )
				$meta['noindex'] = 'noindex';

			$is_archive = $this->is_archive();

			if ( $this->get_option( 'paged_noindex' ) && $this->paged() > 1 ) {
				if ( $is_archive || $this->is_singular_archive() )
					$meta['noindex'] = $this->get_option( 'paged_noindex' ) ? 'noindex' : $meta['noindex'];
			}

			if ( $is_archive ) {
				$term_data = $this->get_current_term_meta();

				if ( $term_data ) {
					$meta['noindex']   = ! empty( $term_data['noindex'] ) ? 'noindex' : $meta['noindex'];
					$meta['nofollow']  = ! empty( $term_data['nofollow'] ) ? 'nofollow' : $meta['nofollow'];
					$meta['noarchive'] = ! empty( $term_data['noarchive'] ) ? 'noarchive' : $meta['noarchive'];
				}

				//* If on custom Taxonomy page, but not a category or tag, then should've received specific term SEO settings.
				if ( $this->is_category() ) {
					$meta['noindex']   = $this->get_option( 'category_noindex' ) ? 'noindex' : $meta['noindex'];
					$meta['nofollow']  = $this->get_option( 'category_nofollow' ) ? 'nofollow' : $meta['nofollow'];
					$meta['noarchive'] = $this->get_option( 'category_noindex' ) ? 'noarchive' : $meta['noarchive'];
				} elseif ( $this->is_tag() ) {
					$meta['noindex']   = $this->get_option( 'tag_noindex' ) ? 'noindex' : $meta['noindex'];
					$meta['nofollow']  = $this->get_option( 'tag_nofollow' ) ? 'nofollow' : $meta['nofollow'];
					$meta['noarchive'] = $this->get_option( 'tag_noindex' ) ? 'noarchive' : $meta['noarchive'];
				} elseif ( $this->is_author() ) {
					$meta['noindex']   = $this->get_option( 'author_noindex' ) ? 'noindex' : $meta['noindex'];
					$meta['nofollow']  = $this->get_option( 'author_nofollow' ) ? 'nofollow' : $meta['nofollow'];
					$meta['noarchive'] = $this->get_option( 'author_noarchive' ) ? 'noarchive' : $meta['noarchive'];
				} elseif ( $this->is_date() ) {
					$meta['noindex']   = $this->get_option( 'date_noindex' ) ? 'noindex' : $meta['noindex'];
					$meta['nofollow']  = $this->get_option( 'date_nofollow' ) ? 'nofollow' : $meta['nofollow'];
					$meta['noarchive'] = $this->get_option( 'date_noarchive' ) ? 'noarchive' : $meta['noarchive'];
				}
			} elseif ( $this->is_search() ) {
				$meta['noindex']   = $this->get_option( 'search_noindex' ) ? 'noindex' : $meta['noindex'];
				$meta['nofollow']  = $this->get_option( 'search_nofollow' ) ? 'nofollow' : $meta['nofollow'];
				$meta['noarchive'] = $this->get_option( 'search_noarchive' ) ? 'noarchive' : $meta['noarchive'];
			}
		}

		if ( $this->is_singular() ) {
			$meta['noindex']   = $this->get_custom_field( '_genesis_noindex' ) ? 'noindex' : $meta['noindex'];
			$meta['nofollow']  = $this->get_custom_field( '_genesis_nofollow' ) ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = $this->get_custom_field( '_genesis_noarchive' ) ? 'noarchive' : $meta['noarchive'];

			if ( $this->is_protected( $this->get_the_real_ID() ) ) {
				$meta['noindex'] = 'noindex';
			}
		}

		$post_type = \get_post_type();
		foreach ( [ 'noindex', 'nofollow', 'noarchive' ] as $r ) {
			$o = $this->get_option( $this->get_robots_post_type_option_id( $r ) );
			if ( ! empty( $o[ $post_type ] ) ) {
				$meta[ $r ] = $r;
			}
		}

		/**
		 * Filters the front-end robots array, and strips empty indexes thereafter.
		 *
		 * @since 2.6.0
		 *
		 * @param array $meta The current term meta.
		 */
		return array_filter( (array) \apply_filters( 'the_seo_framework_robots_meta_array', $meta ) );
	}

	/**
	 * Determines if the post type has a robots value set.
	 *
	 * @since 3.1.0
	 *
	 * @param string $type      Accepts 'noindex', 'nofollow', 'noarchive'.
	 * @param string $post_type The post type, optional. Leave empty to autodetermine type.
	 * @return bool True if disabled, false otherwise.
	 */
	public function is_post_type_robots_set( $type, $post_type = '' ) {
		return isset(
			$this->get_option( $this->get_robots_post_type_option_id( $type ) )[
				$post_type ?: \get_post_type() ?: $this->get_admin_post_type()
			]
		);
	}

	/**
	 * Returns cached and parsed separator option.
	 *
	 * @since 2.3.9
	 * @since 3.1.0 : 1. Removed caching.
	 *                2. Removed escaping parameter.
	 *
	 * @param string $type The separator type. Used to fetch option.
	 * @return string The separator.
	 */
	public function get_separator( $type = 'title' ) {

		$sep_option = $this->get_option( $type . '_separator' );

		if ( 'pipe' === $sep_option ) {
			$sep = '|';
		} elseif ( 'dash' === $sep_option ) {
			$sep = '-';
		} elseif ( '' !== $sep_option ) {
			//* Encapsulate within html entities.
			$sep = '&' . $sep_option . ';';
		} else {
			//* Nothing found.
			$sep = '|';
		}

		return $sep;
	}

	/**
	 * Fetches blogname.
	 *
	 * @since 2.5.2
	 * @staticvar string $blogname
	 *
	 * @return string $blogname The trimmed and sanitized blogname.
	 */
	public function get_blogname() {
		static $blogname = null;
		return isset( $blogname ) ? $blogname : $blogname = trim( \get_bloginfo( 'name', 'display' ) );
	}

	/**
	 * Fetch blog description.
	 *
	 * @since 2.5.2
	 * @since 3.0.0 No longer returns untitled when empty, instead, it just returns an empty string.
	 * @staticvar string $description
	 *
	 * @return string $blogname The trimmed and sanitized blog description.
	 */
	public function get_blogdescription() {

		static $description = null;

		if ( isset( $description ) )
			return $description;

		$description = trim( \get_bloginfo( 'description', 'display' ) );

		return $description = $description ?: '';
	}

	/**
	 * Matches WordPress locales.
	 * If not matched, it will calculate a locale.
	 *
	 * @since 2.5.2
	 *
	 * @param $match the locale to match. Defaults to WordPress locale.
	 * @return string Facebook acceptable OG locale.
	 */
	public function fetch_locale( $match = '' ) {

		if ( empty( $match ) )
			$match = \get_locale();

		$match_len = strlen( $match );
		$valid_locales = (array) $this->fb_locales();
		$default = 'en_US';

		if ( $match_len > 5 ) {
			//* More than full is used. Make it just full.
			$match = substr( $match, 0, 5 );
			$match_len = 5;
		}

		if ( 5 === $match_len ) {
			//* Full locale is used.

			//* Return the match if found.
			if ( in_array( $match, $valid_locales, true ) )
				return $match;

			//* Convert to only language portion.
			$match = substr( $match, 0, 2 );
			$match_len = 2;
		}

		if ( 2 === $match_len ) {
			//* Language key is provided.

			$locale_keys = (array) $this->language_keys();

			//* No need to do for each loop. Just match the keys.
			if ( $key = array_search( $match, $locale_keys, true ) ) {
				//* Fetch the corresponding value from key within the language array.
				return $valid_locales[ $key ];
			}
		}

		return $default;
	}

	/**
	 * Generates the Open Graph type based on query status.
	 *
	 * @since 2.7.0
	 *
	 * @return string The Open Graph type.
	 */
	public function generate_og_type() {

		if ( $this->is_wc_product() ) {
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
	 *
	 * @since 2.8.0
	 * @staticvar string $type
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
	 * @since 3.1.0
	 * @TODO use this
	 * @see get_available_twitter_cards
	 */
	public function get_available_open_graph_types() { }

	/**
	 * Generates the Twitter Card type.
	 *
	 * When there's an image found, it will take the said option.
	 * Otherwise, it will return 'summary' or ''.
	 *
	 * @since 2.7.0
	 * @since 2.8.2 Now considers description output.
	 * @since 2.9.0 Now listens to $this->get_available_twitter_cards().
	 * @since 3.1.0 Now inherits filter `the_seo_framework_twittercard_output`.
	 *
	 * @return string The Twitter Card type.
	 */
	public function generate_twitter_card_type() {

		$available_cards = $this->get_available_twitter_cards();

		//* No valid Twitter cards have been found.
		if ( false === $available_cards )
			return '';

		$option = $this->get_option( 'twitter_card' );
		$option = trim( \esc_attr( $option ) );

		//* Option is equal to found cards. Output option.
		if ( in_array( $option, $available_cards, true ) ) {
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
	 *
	 * @since 2.9.0
	 * @staticvar bool|array $cache
	 *
	 * @return bool|array False when it shouldn't be used. Array of available cards otherwise.
	 */
	public function get_available_twitter_cards() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		if ( ! $this->get_twitter_description() || ! $this->get_twitter_title() ) {
			$retval = [];
		} else {
			$retval = $this->get_image_from_cache() ? [ 'summary_large_image', 'summary' ] : [ 'summary' ];
		}

		/**
		 * Filters the available Twitter cards on the front end.
		 * @since 2.9.0
		 * @param array $retval Use empty array to invalidate Twitter card.
		 */
		$retval = (array) \apply_filters( 'the_seo_framework_available_twitter_cards', $retval );

		return $cache = $retval ?: false;
	}

	/**
	 * List of title separators.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Is now filterable.
	 *
	 * @return array Title separators.
	 */
	public function get_separator_list() {
		/**
		 * @since 3.1.0
		 * @param array $list The separator list in { option_name > display_value } format.
		 *                    The option name should be translatable within `&...;` tags.
		 *                    'pipe' and 'dash' are excluded from this rule.
		 */
		return (array) \apply_filters(
			'the_seo_framework_separator_list',
			[
				'pipe'   => '|',
				'dash'   => '-',
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
}
