<?php
/**
 * @package The_SEO_Framework\Classes\Meta\Title
 * @subpackage The_SEO_Framework\Meta\Title
 */

namespace The_SEO_Framework\Meta\Title;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\{
	memo,
	normalize_generation_args,
};

use \The_SEO_Framework\{
	Data,
	Helper\Query,
	Meta,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds conditions for the Title factory.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->title()->conditions() instead.
 */
class Conditions {

	/**
	 * Determines whether to add or remove title protection prefixes.
	 *
	 * @since 5.0.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return bool True when prefixes are allowed.
	 */
	public static function use_protection_status( $args = null ) {

		if ( isset( $args ) ) {
			normalize_generation_args( $args );

			if ( empty( $args['id'] ) || $args['tax'] || $args['pta'] || $args['uid'] )
				return false;

			$id = $args['id'];
		} else {
			if ( ! Query::is_singular() ) return false;

			$id = Query::get_the_real_id();
		}

		$post = \get_post( $id );

		return ! empty( $post->post_password )
			|| 'private' === ( $post->post_status ?? null );
	}

	/**
	 * Determines whether to add or remove title pagination additions.
	 *
	 * @since 3.2.4
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `use_title_pagination`.
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return bool True when additions are allowed.
	 */
	public static function use_pagination( $args = null ) {

		// Only add pagination if the query is autodetermined, and on a real page.
		if ( isset( $args ) || \is_404() || \is_admin() )
			return false;

		return Query::is_multipage();
	}

	/**
	 * Determines whether to add or remove title branding additions.
	 *
	 * @since 3.1.0
	 * @since 3.1.2 1. Added filter.
	 *              2. Added strict taxonomical check.
	 * @since 3.2.2 Now differentiates from query and parameter input.
	 * @since 4.1.0 Added the second $social parameter.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `use_title_branding`.
	 *
	 * @param array|null  $args  The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                           Leave null to autodetermine query.
	 * @param bool|string $social Whether the title is meant for social display.
	 *                            Also accepts string 'og' and 'twitter' for future proofing.
	 * @return bool True when additions are allowed.
	 */
	public static function use_branding( $args = null, $social = false ) {

		// If social, test its option first.
		$use = $social ? ! Data\Plugin::get_option( 'social_title_rem_additions' ) : true;

		// Reevaluate from general title settings, overriding social.
		if ( $use ) {
			if ( isset( $args ) ) {
				normalize_generation_args( $args );

				if ( $args['tax'] ) {
					$use = static::use_term_branding( $args['id'] );
				} elseif ( $args['pta'] ) {
					$use = static::use_pta_branding( $args['pta'] );
				} elseif ( empty( $args['uid'] ) && Query::is_real_front_page_by_id( $args['id'] ) ) {
					$use = static::use_front_page_tagline();
				} else {
					$use = static::use_post_branding( $args['id'] );
				}
			} else {
				if ( Query::is_real_front_page() ) {
					$use = static::use_front_page_tagline();
				} elseif ( Query::is_singular() ) {
					$use = static::use_post_branding();
				} elseif ( Query::is_editable_term() ) {
					$use = static::use_term_branding();
				} elseif ( \is_post_type_archive() ) {
					$use = static::use_pta_branding();
				} else {
					$use = ! Data\Plugin::get_option( 'title_rem_additions' );
				}
			}
		}

		/**
		 * @since 3.1.2
		 * @since 4.1.0 Added the third $social parameter.
		 * @param bool       $use    Whether to use branding.
		 * @param array|null $args   The query arguments. Contains 'id', 'tax', 'pta', and 'uid'.
		 *                           Is null when the query is auto-determined.
		 * @param bool       $social Whether the title is meant for social display.
		 */
		return \apply_filters(
			'the_seo_framework_use_title_branding',
			$use,
			$args,
			(bool) $social,
		);
	}

	/**
	 * Determines whether to add homepage tagline.
	 *
	 * @since 5.0.0
	 *
	 * @return bool
	 */
	private static function use_front_page_tagline() {
		return Data\Plugin::get_option( 'homepage_tagline' )
			&& Meta\Title::get_addition_for_front_page();
	}

	/**
	 * Determines whether to add the title tagline for the post.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `use_singular_title_branding`.
	 *
	 * @param int $id The post ID. Optional.
	 * @return bool
	 */
	private static function use_post_branding( $id = 0 ) {
		return ! Data\Plugin\Post::get_meta_item( '_tsf_title_no_blogname', $id )
			&& ! Data\Plugin::get_option( 'title_rem_additions' );
	}

	/**
	 * Determines whether to add the title tagline for the term.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `use_taxonomical_title_branding`.
	 *
	 * @param int $id The term ID. Optional.
	 * @return bool
	 */
	private static function use_term_branding( $id = 0 ) {
		return ! Data\Plugin\Term::get_meta_item( 'title_no_blog_name', $id )
			&& ! Data\Plugin::get_option( 'title_rem_additions' );
	}

	/**
	 * Determines whether to add the title tagline for the post type archive.
	 *
	 * @since 5.0.0
	 *
	 * @param string $pta The post type archive. Optional.
	 * @return bool
	 */
	private static function use_pta_branding( $pta = '' ) {
		return ! Data\Plugin\PTA::get_meta_item( 'title_no_blog_name', $pta )
			&& ! Data\Plugin::get_option( 'title_rem_additions' );
	}

	/**
	 * Determines whether to use the autogenerated archive title prefix or not.
	 *
	 * @since 3.1.0
	 * @since 4.0.5 1: Added first parameter `$term`.
	 *              2: Added filter.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param \WP_Term|\WP_User|\WP_Post_Type|null $term The Term object. Leave null to autodermine query.
	 * @return bool
	 */
	public static function use_generated_archive_prefix( $term = null ) {
		/**
		 * @since 4.0.5
		 * @param bool                            $use  Whether to use the prefix.
		 * @param \WP_Term|\WP_User|\WP_Post_Type $term The current term object.
		 */
		return \apply_filters(
			'the_seo_framework_use_archive_prefix',
			! Data\Plugin::get_option( 'title_rem_prefixes' ),
			$term ?? \get_queried_object(),
		);
	}
}
