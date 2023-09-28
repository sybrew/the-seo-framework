<?php
/**
 * @package The_SEO_Framework\Classes\Meta
 * @subpackage The_SEO_Framework\Meta\Twitter
 */

namespace The_SEO_Framework\Meta;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Data,
	\The_SEO_Framework\Helper\Query;

use function \The_SEO_Framework\{
	memo,
	Utils\normalize_generation_args,
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
 * Holds getters for meta tag output.
 *
 * @since 4.3.0
 * @access protected
 * @internal Use tsf()->twitter() instead.
 */
class Twitter {

	/**
	 * Tests whether we can/should fall back to Open Graph.
	 *
	 * @since 4.3.0
	 *
	 * @return string The Twitter Card type. When no social title is found, an empty string will be returned.
	 */
	public static function fallback_to_open_graph() {
		static $fallback;
		return $fallback ??= Data\Plugin::get_option( 'og_tags' );
	}

	/**
	 * Generates the Twitter Card type.
	 *
	 * @since 4.3.0
	 *
	 * @return string The Twitter Card type. When no social title is found, an empty string will be returned.
	 */
	public static function get_card_type() {

		$preferred_card = Data\Plugin::get_option( 'twitter_card' );

		if ( 'auto' === $preferred_card ) {
			$card = 'summary'; // TODO!
		} else {
			$card = \in_array( $preferred_card, static::get_supported_cards(), true )
				? $preferred_card
				: 'summary';
		}

		if ( \has_filter( 'the_seo_framework_twittercard_output' ) ) {
			/**
			 * @since 2.3.0
			 * @since 2.7.0 Added output within filter.
			 * @since 4.3.0 Deprecated.
			 * @deprecated
			 * @param string $card The generated Twitter card type.
			 * @param int    $id   The current page or term ID.
			 */
			$card = (string) \apply_filters_deprecated(
				'the_seo_framework_twittercard_output',
				[
					$card,
					Query::get_the_real_id(),
				],
				'4.3.0',
				'the_seo_framework_twitter_card',
			);
		}

		/**
		 * @since 4.3.0
		 * @param string $card The generated Twitter card type.
		 * @param int    $id   The current page or term ID.
		 */
		return (string) \apply_filters(
			'the_seo_framework_twitter_card',
			$card
		);
	}

	/**
	 * Returns array of supported Twitter Card types.
	 *
	 * @since 4.3.0
	 *
	 * @return array Twitter Card types.
	 */
	public static function get_supported_cards() {
		return [
			'summary',
			'summary_large_image',
		];
	}

	/**
	 * Returns the Twitter site handle.
	 *
	 * @since 4.3.0
	 *
	 * @return string
	 */
	public static function get_site() {
		return Data\Plugin::get_option( 'twitter_site' );
	}

	/**
	 * Returns the Twitter post creator.
	 *
	 * @since 4.3.0
	 *
	 * @return string
	 */
	public static function get_creator() {
		return Data\Plugin\User::get_current_post_author_meta_item( 'twitter_page' )
			?: Data\Plugin::get_option( 'twitter_creator' );
	}

	/**
	 * Returns the Twitter meta title.
	 * Falls back to Open Graph title.
	 *
	 * @since 4.3.0
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $escape Whether to escape the title.
	 * @return string Twitter Title.
	 */
	public static function get_title( $args = null, $escape = true ) {

		$title = static::get_custom_title( $args, false )
			  ?: static::get_generated_title( $args, false );

		return $escape ? \tsf()->escape_title( $title ) : $title;
	}

	/**
	 * Returns the Twitter meta title from custom field.
	 * Falls back to Open Graph title.
	 *
	 * @since 4.3.0
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', and 'pta'.
	 * @param bool       $escape Whether to escape the title.
	 * @return string Twitter Title.
	 */
	public static function get_custom_title( $args, $escape ) {

		if ( null === $args ) {
			$title = static::get_custom_title_from_query();
		} else {
			$title = static::get_custom_title_from_args( $args );
		}

		return $escape ? \tsf()->escape_title( $title ) : $title;
	}

	/**
	 * Returns the Twitter meta title from custom field, based on query.
	 * Falls back to Open Graph title.
	 *
	 * @since 4.3.0
	 *
	 * @return string Custom twitter Title.
	 */
	public static function get_custom_title_from_query() {

		if ( Query::is_real_front_page() ) {
			if ( Query::is_static_frontpage() ) {
				$title = Data\Plugin::get_option( 'homepage_twitter_title' )
					  ?: Data\Plugin\Post::get_post_meta_item( '_twitter_title' );
			} else {
				$title = Data\Plugin::get_option( 'homepage_twitter_title' );
			}
		} elseif ( Query::is_singular() ) {
			$title = Data\Plugin\Post::get_post_meta_item( '_twitter_title' );
		} elseif ( Query::is_editable_term() ) {
			$title = Data\Plugin\Term::get_term_meta_item( 'tw_title' );
		} elseif ( \is_post_type_archive() ) {
			$title = \tsf()->get_post_type_archive_meta_item( 'tw_title' );
		}

		if ( isset( $title ) ) {
			// At least there was an attempt made to fetch one when we reach this.
			return $title
				?: (
					static::fallback_to_open_graph()
						? Open_Graph::get_custom_title_from_query()
						: Title::get_custom_title( null, false, true )  // var_dump() move the filter to _from_query?
				);
		}

		return '';
	}

	/**
	 * Returns the Twitter meta title from custom field, based on arguments.
	 * Falls back to Open Graph title.
	 *
	 * @since 4.3.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', and 'pta'.
	 * @return string Twitter Title.
	 */
	public static function get_custom_title_from_args( $args ) {

		normalize_generation_args( $args );

		if ( $args['tax'] ) {
			$title = Data\Plugin\Term::get_term_meta_item( 'tw_title', $args['id'] );
		} elseif ( $args['pta'] ) {
			$title = \tsf()->get_post_type_archive_meta_item( 'tw_title', $args['pta'] );
		} elseif ( Query::is_real_front_page_by_id( $args['id'] ) ) {
			if ( $args['id'] ) {
				$title = Data\Plugin::get_option( 'homepage_twitter_title' )
					  ?: Data\Plugin\Post::get_post_meta_item( '_twitter_title', $args['id'] );
			} else {
				$title = Data\Plugin::get_option( 'homepage_twitter_title' );
			}
		} elseif ( $args['id'] ) {
			$title = Data\Plugin\Post::get_post_meta_item( '_twitter_title', $args['id'] );
		}

		if ( isset( $title ) ) {
			// At least there was an attempt made to fetch one when we reach this.
			return $title
				?: (
					static::fallback_to_open_graph()
						? Open_Graph::get_custom_title_from_args( $args )
						: Title::get_custom_title( $args, false, true ) // var_dump() move the filter to _from_args?
				);
		}

		return '';
	}

	/**
	 * Returns the autogenerated Twitter meta title.
	 * Falls back to meta title.
	 *
	 * @since 4.3.0
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $escape Whether to escape the title.
	 * @return string The generated Twitter Title.
	 */
	public static function get_generated_title( $args = null, $escape = true ) {
		return Title::get_generated_title( $args, $escape, true );
	}

	/**
	 * Returns the Twitter meta description.
	 * Falls back to Open Graph description.
	 *
	 * @since 4.3.0
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $escape Whether to escape the description.
	 * @return string The real Twitter description output.
	 */
	public static function get_description( $args = null, $escape = true ) {

		$desc = static::get_custom_description( $args, false )
			 ?: static::get_generated_description( $args, false );

		return $escape ? \tsf()->escape_description( $desc ) : $desc;
	}

	/**
	 * Returns the Twitter meta description from custom field.
	 * Falls back to Open Graph description.
	 *
	 * @since 4.3.0
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $escape Whether to escape the title.
	 * @return string Twitter description.
	 */
	public static function get_custom_description( $args, $escape ) {

		if ( null === $args ) {
			$desc = static::get_custom_description_from_query();
		} else {
			$desc = static::get_custom_description_from_args( $args );
		}

		return $escape ? \tsf()->escape_description( $desc ) : $desc;
	}

	/**
	 * Returns the Twitter meta description from custom field, based on query.
	 * Falls back to Open Graph description.
	 *
	 * @since 4.3.0
	 *
	 * @return string Twitter description.
	 */
	public static function get_custom_description_from_query() {

		if ( Query::is_real_front_page() ) {
			if ( Query::is_static_frontpage() ) {
				$desc = Data\Plugin::get_option( 'homepage_twitter_description' )
					 ?: Data\Plugin\Post::get_post_meta_item( '_twitter_description' );
			} else {
				$desc = Data\Plugin::get_option( 'homepage_twitter_description' );
			}
		} elseif ( Query::is_singular() ) {
			$desc = Data\Plugin\Post::get_post_meta_item( '_twitter_description' );
		} elseif ( Query::is_editable_term() ) {
			$desc = Data\Plugin\Term::get_term_meta_item( 'tw_description' );
		} elseif ( \is_post_type_archive() ) {
			$desc = \tsf()->get_post_type_archive_meta_item( 'tw_description' );
		}

		if ( isset( $desc ) ) {
			// At least there was an attempt made to fetch one when we reach this.
			return $desc
				?: (
					static::fallback_to_open_graph()
						? Open_Graph::get_custom_description_from_query()
						: Description::get_custom_description( null, false ) // var_dump() move the filter to _from_query?
				);
		}

		return '';
	}

	/**
	 * Returns the Twitter meta description from custom field, based on arguments.
	 * Falls back to Open Graph description.
	 *
	 * @since 4.3.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', and 'pta'.
	 * @return string Twitter description.
	 */
	public static function get_custom_description_from_args( $args ) {

		normalize_generation_args( $args );

		if ( $args['tax'] ) {
			$desc = Data\Plugin\Term::get_term_meta_item( 'tw_description', $args['id'] );
		} elseif ( $args['pta'] ) {
			$desc = \tsf()->get_post_type_archive_meta_item( 'tw_description', $args['pta'] );
		} elseif ( Query::is_real_front_page_by_id( $args['id'] ) ) {
			if ( $args['id'] ) {
				$desc = Data\Plugin::get_option( 'homepage_twitter_description' )
					 ?: Data\Plugin\Post::get_post_meta_item( '_twitter_description', $args['id'] );
			} else {
				$desc = Data\Plugin::get_option( 'homepage_twitter_description' );
			}
		} elseif ( $args['id'] ) {
			$desc = Data\Plugin\Post::get_post_meta_item( '_twitter_description', $args['id'] );
		}

		if ( isset( $desc ) ) {
			// At least there was an attempt made to fetch one when we reach this.
			return $desc
				?: (
					static::fallback_to_open_graph()
						? Open_Graph::get_custom_description_from_args( $args )
						: Description::get_custom_description( $args, false )  // var_dump() move the filter to _from_tags?
				);
		}

		return '';
	}

	/**
	 * Returns the autogenerated Twitter meta description. Falls back to Open Graph and then meta description.
	 *
	 * @since 4.3.0
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $escape Whether to escape the description.
	 * @return string The generated Twitter description output.
	 */
	public static function get_generated_description( $args = null, $escape = true ) {
		return Description::get_generated_description( $args, $escape, 'twitter' );
	}
}
