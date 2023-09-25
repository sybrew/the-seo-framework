<?php
/**
 * @package The_SEO_Framework\Classes\Front\Meta\Factory\Title
 * @subpackage The_SEO_Framework\Meta\Title
 */

namespace The_SEO_Framework\Meta\Factory\Title;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\{
	memo,
	Utils\normalize_generation_args,
};

use \The_SEO_Framework\Helper\Query,
	\The_SEO_Framework\Meta\Factory;

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
 * Holds conditions for the Title factory.
 *
 * @since 4.3.0
 * @access protected
 * @internal Use tsf()->title()->conditions() instead.
 */
final class Conditions {

	/**
	 * Determines whether to add or remove title protection prefixes.
	 *
	 * @since 4.3.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                         Leave null to autodetermine query.
	 * @return bool True when prefixes are allowed.
	 */
	public static function use_title_protection_status( $args = null ) {

		if ( null === $args ) {
			if ( ! Query::is_singular() ) return false;

			$id = Query::get_the_real_id();
		} else {
			normalize_generation_args( $args );

			if ( $args['tax'] || $args['pta'] || empty( $args['id'] ) )
				return false;

			$id = $args['id'];
		}

		$post = \get_post( $id );

		return ! empty( $post->post_password )
			|| 'private' === ( $post->post_status ?? null );
	}

	/**
	 * Determines whether to add or remove title pagination additions.
	 *
	 * @since 3.2.4
	 * @since 4.3.0 Moved to \The_SEO_Framework\Meta\Factory\Title\Conditions
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                         Leave null to autodetermine query.
	 * @return bool True when additions are allowed.
	 */
	public static function use_title_pagination( $args = null ) {

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
	 * @since 4.3.0 Moved to \The_SEO_Framework\Meta\Factory\Title\Conditions
	 *
	 * @param array|null  $args  The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                           Leave null to autodetermine query.
	 * @param bool|string $social Whether the title is meant for social display.
	 *                            Also accepts string 'og' and 'twitter' for future proofing.
	 * @return bool True when additions are allowed.
	 */
	public static function use_title_branding( $args = null, $social = false ) {

		// If social, test its option first.
		$use = $social ? ! \tsf()->get_option( 'social_title_rem_additions' ) : true;

		// Reevaluate from general title settings, overriding social.
		if ( $use ) {
			if ( null === $args ) {
				if ( Query::is_real_front_page() ) {
					$use = static::use_home_page_title_tagline();
				} elseif ( Query::is_singular() ) {
					$use = static::use_singular_title_branding();
				} elseif ( Query::is_editable_term() ) {
					$use = static::use_taxonomical_title_branding();
				} elseif ( \is_post_type_archive() ) {
					$use = static::use_post_type_archive_title_branding();
				} else {
					$use = ! \tsf()->get_option( 'title_rem_additions' );
				}
			} else {
				isset( $args ) and normalize_generation_args( $args );

				if ( $args['tax'] ) {
					$use = static::use_taxonomical_title_branding( $args['id'] );
				} elseif ( $args['pta'] ) {
					$use = static::use_post_type_archive_title_branding( $args['pta'] );
				} elseif ( Query::is_real_front_page_by_id( $args['id'] ) ) {
					$use = static::use_home_page_title_tagline();
				} else {
					$use = static::use_singular_title_branding( $args['id'] );
				}
			}
		}

		/**
		 * @since 3.1.2
		 * @since 4.1.0 Added the third $social parameter.
		 * @param string     $use    Whether to use branding.
		 * @param array|null $args   The query arguments. Contains 'id', 'tax', and 'pta'.
		 *                           Is null when the query is auto-determined.
		 * @param bool       $social Whether the title is meant for social display.
		 */
		return \apply_filters_ref_array(
			'the_seo_framework_use_title_branding',
			[
				$use,
				$args,
				(bool) $social,
			]
		);
	}

	/**
	 * Determines whether to add homepage tagline.
	 *
	 * @since 2.6.0
	 * @since 3.0.4 Now checks for `Factory\Title::get_addition_for_front_page()`.
	 * @since 4.3.0 Moved to \The_SEO_Framework\Meta\Factory\Title\Conditions
	 *
	 * @return bool
	 */
	private static function use_home_page_title_tagline() {
		return \tsf()->get_option( 'homepage_tagline' )
			&& Factory\Title::get_addition_for_front_page();
	}

	/**
	 * Determines whether to add the title tagline for the post.
	 *
	 * @since 3.1.0
	 * @since 4.3.0 Moved to \The_SEO_Framework\Meta\Factory\Title\Conditions
	 *
	 * @param int $id The post ID. Optional.
	 * @return bool
	 */
	private static function use_singular_title_branding( $id = 0 ) {
		return ! \tsf()->get_post_meta_item( '_tsf_title_no_blogname', $id )
			&& ! \tsf()->get_option( 'title_rem_additions' );
	}

	/**
	 * Determines whether to add the title tagline for the term.
	 *
	 * @since 4.0.0
	 * @since 4.3.0 Moved to \The_SEO_Framework\Meta\Factory\Title\Conditions
	 *
	 * @param int $id The term ID. Optional.
	 * @return bool
	 */
	private static function use_taxonomical_title_branding( $id = 0 ) {
		return ! \tsf()->get_term_meta_item( 'title_no_blog_name', $id )
			&& ! \tsf()->get_option( 'title_rem_additions' );
	}

	/**
	 * Determines whether to add the title tagline for the pta.
	 *
	 * @since 4.2.0
	 * @since 4.3.0 Moved to \The_SEO_Framework\Meta\Factory\Title\Conditions
	 *
	 * @param string $pta The post type archive. Optional.
	 * @return bool
	 */
	private static function use_post_type_archive_title_branding( $pta = '' ) {
		return ! \tsf()->get_post_type_archive_meta_item( 'title_no_blog_name', $pta )
			&& ! \tsf()->get_option( 'title_rem_additions' );
	}

	/**
	 * Determines whether to use the autogenerated archive title prefix or not.
	 *
	 * @since 3.1.0
	 * @since 4.0.5 1: Added first parameter `$term`.
	 *              2: Added filter.
	 * @since 4.3.0 Moved to \The_SEO_Framework\Meta\Factory\Title\Conditions
	 *
	 * @param \WP_Term|\WP_User|\WP_Post_Type|null $term The Term object. Leave null to autodermine query.
	 * @return bool
	 */
	public static function use_generated_archive_prefix( $term = null ) {
		/**
		 * @since 4.0.5
		 * @param string                          $use  Whether to use branding.
		 * @param \WP_Term|\WP_User|\WP_Post_Type $term The current term.
		 */
		return \apply_filters_ref_array(
			'the_seo_framework_use_archive_prefix',
			[
				! \tsf()->get_option( 'title_rem_prefixes' ),
				$term ?? \get_queried_object(),
			]
		);
	}
}
