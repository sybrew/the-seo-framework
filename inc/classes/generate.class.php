<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Generate
 * @subpackage The_SEO_Framework\Getters
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\Utils\normalize_generation_args;

use \The_SEO_Framework\Helper\Query;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
		return $this->get_separator_list()[ $this->get_option( "{$type}_separator" ) ] ?? '&#x2d;';
	}

	/**
	 * Fetches public blogname (site title).
	 * Memoizes the return value.
	 *
	 * Do not consider this function safe for printing!
	 *
	 * @since 2.5.2
	 * @since 4.2.0 1. Now listens to the new `site_title` option.
	 *              2. Now applies filters.
	 *
	 * @return string $blogname The sanitized blogname.
	 */
	public function get_blogname() {
		return memo()
			?? memo( $this->get_option( 'site_title' ) ?: $this->get_filtered_raw_blogname() );
	}

	/**
	 * Fetches blogname (site title).
	 *
	 * Do not consider this function safe for printing!
	 *
	 * We use get_bloginfo( ..., 'display' ), even though it escapes needlessly, because it applies filters.
	 *
	 * @since 4.2.0
	 *
	 * @return string $blogname The sanitized blogname.
	 */
	public function get_filtered_raw_blogname() {
		/**
		 * @since 4.2.0
		 * @param string The blog name.
		 */
		return (string) \apply_filters(
			'the_seo_framework_blog_name',
			trim( \get_bloginfo( 'name', 'display' ) )
		);
	}

	/**
	 * Fetch blog description.
	 * Memoizes the return value.
	 *
	 * Do not consider this function safe for printing!
	 *
	 * We use get_bloginfo( ..., 'display' ), even though it escapes needlessly, because it applies filters.
	 *
	 * @since 2.5.2
	 * @since 3.0.0 No longer returns untitled when empty, instead, it just returns an empty string.
	 *
	 * @return string $blogname The sanitized blog description.
	 */
	public function get_blogdescription() {
		return memo() ?? memo( trim( \get_bloginfo( 'description', 'display' ) ) );
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
		$valid_locales = $this->supported_social_locales(); // [ ll_LL => ll ]

		if ( $match_len > 5 ) {
			$match_len = 5;
			// More than standard-full locale type is used. Make it just full.
			$match = substr( $match, 0, $match_len );
		}

		if ( 5 === $match_len ) {
			// Full locale is used. See if it's valid and return it.
			if ( isset( $valid_locales[ $match ] ) )
				return $match;

			// Convert to only language portion.
			$match_len = 2;
			$match     = substr( $match, 0, $match_len );
		}

		if ( 2 === $match_len ) {
			// Only two letters of the lang are provided. Find first match and return it.
			$key = array_search( $match, $valid_locales, true );

			if ( $key )
				return $key;
		}

		// Return default WordPress locale.
		return 'en_US';
	}

	/**
	 * Generates the Open Graph type based on query status.
	 *
	 * @since 2.7.0
	 * @since 4.3.0 An image is no longer required to generate the 'article' type.
	 *
	 * @return string The Open Graph type.
	 */
	public function generate_og_type() {

		switch ( true ) {
			case Query::is_product():
				return 'product';
			case Query::is_single():
				return 'article';
			case Query::is_author():
				return 'profile';
		}

		return 'website';
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
		return memo() ?? memo(
			/**
			 * @since 2.3.0
			 * @since 2.7.0 Added output within filter.
			 * @since 4.3.0 Deprecated
			 * @deprecated
			 * @param string $type The OG type.
			 * @param int    $id   The page/term/object ID.
			 */
			(string) \apply_filters_deprecated(
				'the_seo_framework_ogtype_output',
				[
					$this->generate_og_type(),
					Query::get_the_real_id(),
				],
				'4.3.0 of The SEO Framework',
				'the_seo_framework_meta_render_data',
			)
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

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo() ) return $memo;

		$id                = Query::get_the_real_id();
		$post_modified_gmt = \get_post( $id )->post_modified_gmt ?? '0000-00-00 00:00:00';

		return memo(
			'0000-00-00 00:00:00' === $post_modified_gmt
				? ''
				/**
				 * @since 2.3.0
				 * @since 2.7.0 Added output within filter.
				 * @since 4.3.0 Deprecated
				 * @deprecated
				 * @param string $time The article modified time.
				 * @param int    $id   The current page or term ID.
				 */
				: (string) \apply_filters_deprecated(
					'the_seo_framework_modifiedtime_output',
					[
						$this->gmt2date( $this->get_timestamp_format(), $post_modified_gmt ),
						$id,
					],
					'4.3.0 of The SEO Framework',
					'the_seo_framework_meta_render_data',
				)
		);
	}

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
		$type = \in_array( $option, $available_cards, true ) ? $option : 'summary';

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
				Query::get_the_real_id(),
			]
		);
	}

	/**
	 * Determines which Twitter cards can be used.
	 *
	 * @since 2.9.0
	 * @since 4.0.0 1. Now only asserts the social titles as required.
	 *              2. Now always returns an array, instead of a boolean (false) on failure.
	 * @since 4.2.0 1. No longer memoizes the return value.
	 *              2. No longer tests for the Twitter title.
	 *
	 * @return array False when it shouldn't be used. Array of available cards otherwise.
	 */
	public function get_available_twitter_cards() {
		/**
		 * @since 2.9.0
		 * @param array $cards The available Twitter cards. Use empty array to invalidate Twitter card.
		 */
		return (array) \apply_filters(
			'the_seo_framework_available_twitter_cards',
			[ 'summary_large_image', 'summary' ]
		);
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
	 * @since 4.2.0 1. Now supports the `$args['pta']` index.
	 *              2. Now redirects post type archives.
	 * @since 4.3.0 Now expects an ID before getting a post meta item.
	 *
	 * @param null|array $args The redirect URL arguments, leave null to autodetermine query : {
	 *    int    $id       The Post, Page or Term ID to generate the URL for.
	 *    string $taxonomy The taxonomy.
	 * }
	 * @return string The canonical URL if found, empty string otherwise.
	 */
	public function get_redirect_url( $args = null ) {

		if ( null === $args ) {
			if ( Query::is_singular() ) {
				$url = $this->get_post_meta_item( 'redirect' );
			} elseif ( Query::is_editable_term() ) {
				$url = $this->get_term_meta_item( 'redirect' );
			} elseif ( \is_post_type_archive() ) {
				$url = $this->get_post_type_archive_meta_item( 'redirect' );
			}
		} else {
			normalize_generation_args( $args );
			if ( $args['taxonomy'] ) {
				$url = $this->get_term_meta_item( 'redirect', $args['id'] );
			} elseif ( $args['pta'] ) {
				$url = $this->get_post_type_archive_meta_item( 'redirect', $args['pta'] );
			} elseif ( $args['id'] ) {
				$url = $this->get_post_meta_item( 'redirect', $args['id'] );
			}
		}

		return $url ?? '' ?: '';
	}
}
