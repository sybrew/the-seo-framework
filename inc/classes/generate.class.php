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
 * Generates general SEO data based on content.
 *
 * @since 2.1.6
 */
class AutoDescription_Generate extends AutoDescription_TermData {

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
	 * Output the `index`, `follow`, `noodp`, `noydir`, `noarchive` robots meta code in array
	 *
	 * @since 2.2.2
	 *
	 * @uses genesis_get_seo_option()   Get SEO setting value.
	 * @uses genesis_get_custom_field() Get custom field value.
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

			//* If on custom Taxonomy page, but not a category or tag, then should've recieved specific term SEO settings.
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
		}

		/**
		 * Applies filters the_seo_framework_robots_meta_array : array
		 * @since 2.6.0
		 */
		$meta = (array) apply_filters( 'the_seo_framework_robots_meta_array', $meta );

		//* Strip empty array items
		$meta = array_filter( $meta );

		return $meta;
	}

	/**
	 * Returns cached and parsed separator option.
	 *
	 * @param string $type The separator type. Used to fetch option.
	 * @param bool $escape Escape the separator.
	 *
	 * @staticvar array $sepcache The separator cache.
	 * @staticvar array $sep_esc The escaped separator cache.
	 *
	 * @since 2.3.9
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
			return $sep_esc[ $type ][ $escape ] = esc_html( $sepcache[ $type ] );
		} else {
			return $sep_esc[ $type ][ $escape ] = $sepcache[ $type ];
		}
	}

	/**
	 * Fetches blogname.
	 *
	 * @staticvar string $blogname
	 *
	 * @since 2.5.2
	 * @return string $blogname The trimmed and sanitized blogname.
	 */
	public function get_blogname() {

		static $blogname = null;

		if ( isset( $blogname ) )
			return $blogname;

		return $blogname = trim( get_bloginfo( 'name', 'display' ) );
	}

	/**
	 * Fetch blog description.
	 *
	 * @staticvar string $description
	 *
	 * @since 2.5.2
	 * @return string $blogname The trimmed and sanitized blog description.
	 */
	public function get_blogdescription() {

		static $description = null;

		if ( isset( $description ) )
			return $description;

		$description = trim( get_bloginfo( 'description', 'display' ) );

		return $description = $description ? $description : $this->untitled();
	}

	/**
	 * Matches WordPress locales.
	 * If not matched, it will calculate a locale.
	 *
	 * @param $match the locale to match. Defaults to WordPress locale.
	 *
	 * @since 2.5.2
	 *
	 * @return string Facebook acceptable OG locale.
	 */
	public function fetch_locale( $match = '' ) {

		if ( empty( $match ) )
			$match = get_locale();

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
			if ( $key = array_search( $match, $locale_keys ) ) {
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
	 * Generates the Twitter Card type. When there's an image found, it will
	 * take the said option. Otherwise, it will fall back to 'summary'.
	 *
	 * @since 2.7.0
	 *
	 * @return string The Twitter Card type.
	 */
	public function generate_twitter_card_type() {
		return $this->get_image_from_cache() ? $this->get_option( 'twitter_card' ) : 'summary';
	}
}
