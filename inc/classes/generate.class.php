<?php
/**
 * @package The_SEO_Framework\Classes
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) or die;

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

/**
 * Class The_SEO_Framework\Generate
 *
 * Generates general SEO data based on content.
 *
 * @since 2.8.0
 */
class Generate extends Term_Data {

	/**
	 * Constructor, load parent constructor
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Output the `index`, `follow`, `noodp`, `noydir`, `noarchive` robots meta code in array
	 *
	 * @since 2.2.2
	 * @since 2.2.4 Added robots SEO settings check.
	 * @since 2.2.8 Added check for empty archives.
	 * @since 2.8.0 Added check for protected/private posts.
	 *
	 * @global object $wp_query
	 *
	 * @return array|null robots
	 */
	public function robots_meta() {

		//* Defaults
		$meta = array(
			'noindex'   => $this->get_option( 'site_noindex' ) ? 'noindex' : '',
			'nofollow'  => $this->get_option( 'site_nofollow' ) ? 'nofollow' : '',
			'noarchive' => $this->get_option( 'site_noarchive' ) ? 'noarchive' : '',
			'noodp'     => $this->get_option( 'noodp' ) ? 'noodp' : '',
			'noydir'    => $this->get_option( 'noydir' ) ? 'noydir' : '',
		);

		/**
		 * Check the Robots SEO settings, set noindex for paged archives.
		 * @since 2.2.4
		 */
		if ( $this->is_archive() && $this->paged() > 1 )
			$meta['noindex'] = $this->get_option( 'paged_noindex' ) ? 'noindex' : $meta['noindex'];

		if ( $this->is_front_page() && ( $this->page() > 1 || $this->paged() > 1 ) )
			$meta['noindex'] = $this->get_option( 'home_paged_noindex' ) ? 'noindex' : $meta['noindex'];

		//* Check home page SEO settings, set noindex, nofollow and noarchive
		if ( $this->is_front_page() ) {
			$meta['noindex']   = empty( $meta['noindex'] ) && $this->is_option_checked( 'homepage_noindex' ) ? 'noindex' : $meta['noindex'];
			$meta['nofollow']  = empty( $meta['nofollow'] ) && $this->is_option_checked( 'homepage_nofollow' ) ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = empty( $meta['noarchive'] ) && $this->is_option_checked( 'homepage_noarchive' ) ? 'noarchive' : $meta['noarchive'];
		} else {
			global $wp_query;

			/**
			 * Check if archive is empty, set noindex for those.
			 * @since 2.2.8
			 *
			 * @todo maybe create option
			 * @priority so low... 3.0.0+
			 */
			if ( isset( $wp_query->post_count ) && 0 === $wp_query->post_count )
				$meta['noindex'] = 'noindex';
		}

		if ( $this->is_category() || $this->is_tag() || $this->is_tax() ) {

			$data = $this->get_term_data();

			$meta['noindex']   = empty( $meta['noindex'] ) && ! empty( $data['noindex'] ) ? 'noindex' : $meta['noindex'];
			$meta['nofollow']  = empty( $meta['nofollow'] ) && ! empty( $data['nofollow'] ) ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = empty( $meta['noarchive'] ) && ! empty( $data['noarchive'] ) ? 'noarchive' : $meta['noarchive'];

			//* If on custom Taxonomy page, but not a category or tag, then should've received specific term SEO settings.
			if ( $this->is_category() ) {
				$meta['noindex']   = empty( $meta['noindex'] ) && $this->is_option_checked( 'category_noindex' ) ? 'noindex' : $meta['noindex'];
				$meta['nofollow']  = empty( $meta['nofollow'] ) && $this->is_option_checked( 'category_nofollow' ) ? 'nofollow' : $meta['nofollow'];
				$meta['noarchive'] = empty( $meta['noarchive'] ) && $this->is_option_checked( 'category_noindex' ) ? 'noarchive' : $meta['noarchive'];
			} elseif ( $this->is_tag() ) {
				$meta['noindex']   = empty( $meta['noindex'] ) && $this->is_option_checked( 'tag_noindex' ) ? 'noindex' : $meta['noindex'];
				$meta['nofollow']  = empty( $meta['nofollow'] ) && $this->is_option_checked( 'tag_nofollow' ) ? 'nofollow' : $meta['nofollow'];
				$meta['noarchive'] = empty( $meta['noarchive'] ) && $this->is_option_checked( 'tag_noindex' ) ? 'noarchive' : $meta['noarchive'];
			}
		}

		if ( $this->is_author() ) {
			$meta['noindex']   = empty( $meta['noindex'] ) && $this->is_option_checked( 'author_noindex' ) ? 'noindex' : $meta['noindex'];
			$meta['nofollow']  = empty( $meta['nofollow'] ) && $this->is_option_checked( 'author_nofollow' ) ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = empty( $meta['noarchive'] ) && $this->is_option_checked( 'author_noarchive' ) ? 'noarchive' : $meta['noarchive'];
		}

		if ( $this->is_date() ) {
			$meta['noindex']   = empty( $meta['noindex'] ) && $this->is_option_checked( 'date_noindex' ) ? 'noindex' : $meta['noindex'];
			$meta['nofollow']  = empty( $meta['nofollow'] ) && $this->is_option_checked( 'date_nofollow' ) ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = empty( $meta['noarchive'] ) && $this->is_option_checked( 'date_noarchive' ) ? 'noarchive' : $meta['noarchive'];
		}

		if ( $this->is_search() ) {
			$meta['noindex']   = empty( $meta['noindex'] ) && $this->is_option_checked( 'search_noindex' ) ? 'noindex' : $meta['noindex'];
			$meta['nofollow']  = empty( $meta['nofollow'] ) && $this->is_option_checked( 'search_nofollow' ) ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = empty( $meta['noarchive'] ) && $this->is_option_checked( 'search_noarchive' ) ? 'noarchive' : $meta['noarchive'];
		}

		if ( $this->is_attachment() ) {
			$meta['noindex']   = empty( $meta['noindex'] ) && $this->is_option_checked( 'attachment_noindex' ) ? 'noindex' : $meta['noindex'];
			$meta['nofollow']  = empty( $meta['nofollow'] ) && $this->is_option_checked( 'attachment_nofollow' ) ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = empty( $meta['noarchive'] ) && $this->is_option_checked( 'attachment_noarchive' ) ? 'noarchive' : $meta['noarchive'];
		}

		if ( $this->is_singular() ) {
			$meta['noindex']   = empty( $meta['noindex'] ) && $this->get_custom_field( '_genesis_noindex' ) ? 'noindex' : $meta['noindex'];
			$meta['nofollow']  = empty( $meta['nofollow'] ) && $this->get_custom_field( '_genesis_nofollow' ) ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = empty( $meta['noarchive'] ) && $this->get_custom_field( '_genesis_noarchive' ) ? 'noarchive' : $meta['noarchive'];

			if ( $this->is_protected( $this->get_the_real_ID() ) ) {
				$meta['noindex'] = 'noindex';
			}
		}

		/**
		 * Applies filters the_seo_framework_robots_meta_array : array
		 * @since 2.6.0
		 */
		$meta = (array) \apply_filters( 'the_seo_framework_robots_meta_array', $meta );

		//* Strip empty array items
		$meta = array_filter( $meta );

		return $meta;
	}

	/**
	 * Returns cached and parsed separator option.
	 *
	 * @since 2.3.9
	 * @staticvar array $sepcache The separator cache.
	 * @staticvar array $sep_esc The escaped separator cache.
	 *
	 * @param string $type The separator type. Used to fetch option.
	 * @param bool $escape Escape the separator.
	 * @return string The separator.
	 */
	public function get_separator( $type = 'title', $escape = true ) {

		static $sep_esc = array();

		if ( isset( $sep_esc[ $type ][ $escape ] ) )
			return $sep_esc[ $type ][ $escape ];

		static $sepcache = array();

		if ( ! isset( $sepcache[ $type ] ) ) {
			if ( 'title' === $type ) {
				$sep_option = $this->get_option( 'title_seperator' ); // Note: typo.
			} else {
				$sep_option = $this->get_option( $type . '_separator' );
			}

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

			$sepcache[ $type ] = $sep;
		}

		if ( $escape ) {
			return $sep_esc[ $type ][ $escape ] = \esc_html( $sepcache[ $type ] );
		} else {
			return $sep_esc[ $type ][ $escape ] = $sepcache[ $type ];
		}
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

		if ( isset( $blogname ) )
			return $blogname;

		return $blogname = trim( \get_bloginfo( 'name', 'display' ) );
	}

	/**
	 * Fetch blog description.
	 *
	 * @since 2.5.2
	 * @staticvar string $description
	 *
	 * @return string $blogname The trimmed and sanitized blog description.
	 */
	public function get_blogdescription() {

		static $description = null;

		if ( isset( $description ) )
			return $description;

		$description = trim( \get_bloginfo( 'description', 'display' ) );

		return $description = $description ?: $this->untitled();
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
		} elseif ( $this->is_blog_page() || ( $this->is_front_page() && ! $this->has_page_on_front() ) ) {
			$type = 'blog';
		} else {
			$type = 'website';
		}

		return $type;
	}

	/**
	 * Returns OG Type
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
		 * Applies filters 'the_seo_framework_ogtype_output' : string
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 */
		return $type = (string) \apply_filters( 'the_seo_framework_ogtype_output', $this->generate_og_type(), $this->get_the_real_ID() );
	}

	/**
	 * Generates the Twitter Card type. When there's an image found, it will
	 * take the said option. Otherwise, it will fall back to 'summary'.
	 *
	 * @since 2.7.0
	 * @since 2.8.2 : Now considers description output.
	 *
	 * @return string The Twitter Card type.
	 */
	public function generate_twitter_card_type() {

		if ( $this->get_image_from_cache() ) {

			$option = $this->get_option( 'twitter_card' );

			//* Photo will always work with an image.
			if ( 'photo' === $option )
				return 'photo';

			//* Only output 'summary' or 'summary_large_image' if there's a description.
			if ( $this->description_from_cache( true ) )
				return trim( \esc_attr( $option ) );

			//* Output photo otherwise.
			return 'photo';
		}

		if ( $this->description_from_cache( true ) )
			return 'summary';

		return '';
	}

	/**
	 * List of title separators.
	 *
	 * @since 2.6.0
	 *
	 * @todo add filter.
	 * @todo check if filter can propagate within all functions.
	 *
	 * @return array Title separators.
	 */
	public function get_separator_list() {
		return array(
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
		return array(
			'summary'             => 'summary',
			'summary_large_image' => 'summary-large-image'
		);
	}
}
