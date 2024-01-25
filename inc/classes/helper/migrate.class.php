<?php
/**
 * @package The_SEO_Framework\Classes\Helper\Migrate
 * @subpackage The_SEO_Framework\Migrate
 */

namespace The_SEO_Framework\Helper;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\umemo;

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
 * Holds a collection of helper methods for plugin migration.
 *
 * @since 5.0.0
 * @access private
 */
class Migrate {

	/**
	 * Determines whether the text has recognizable transformative syntax.
	 *
	 * It tests Yoast SEO before Rank Math because that one is more popular, thus more
	 * likely to yield a result.
	 *
	 * @todo test all [ 'extension', 'yoast', 'aioseo', 'rankmath', 'seopress' ]
	 * @since 4.2.7
	 * @since 4.2.8 Added SEOPress support.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `has_unprocessed_syntax`.
	 *
	 * @param string $text The text to evaluate
	 * @return bool
	 */
	public static function text_has_unprocessed_syntax( $text ) {

		foreach ( [ 'yoast_seo', 'rank_math', 'seopress' ] as $type )
			if ( static::{"text_has_{$type}_syntax"}( $text ) ) return true;

		return false;
	}

	/**
	 * Determines if the input text has transformative Yoast SEO syntax.
	 *
	 * @link <https://yoast.com/help/list-available-snippet-variables-yoast-seo/> (This list contains false information)
	 * @link <https://theseoframework.com/extensions/transport/#faq/what-data-is-transformed>
	 * @since 4.0.5
	 * @since 4.2.7 1. Added wildcard `ct_`, and `cf_` detection.
	 *              2. Added detection for various other types
	 *              2. Removed wildcard `cs_` detection.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `has_yoast_syntax`.
	 *
	 * @param string $text The text to evaluate.
	 * @return bool
	 */
	public static function text_has_yoast_seo_syntax( $text ) {

		// %%id%% is the shortest valid tag... ish. Let's stop at 6.
		if ( \strlen( $text ) < 6 || ! str_contains( $text, '%%' ) )
			return false;

		$tags = umemo( __METHOD__ . '/tags' );

		if ( empty( $tags ) ) {
			$tags = umemo(
				__METHOD__ . '/tags',
				[
					'simple'       => implode(
						'|',
						[
							// These are Preserved by Transport. Test first, for they are more likely in text.
							'focuskw',
							'page',
							'pagenumber',
							'pagetotal',
							'primary_category',
							'searchphrase',
							'term404',
							'wc_brand',
							'wc_price',
							'wc_shortdesc',
							'wc_sku',

							// These are transformed by Transport
							'archive_title',
							'author_first_name',
							'author_last_name',
							'caption',
							'category',
							'category_description',
							'category_title',
							'currentdate',
							'currentday',
							'currentmonth',
							'currentyear',
							'date',
							'excerpt',
							'excerpt_only',
							'id',
							'modified',
							'name',
							'parent_title',
							'permalink',
							'post_content',
							'post_year',
							'post_month',
							'post_day',
							'pt_plural',
							'pt_single',
							'sep',
							'sitedesc',
							'sitename',
							'tag',
							'tag_description',
							'term_description',
							'term_title',
							'title',
							'user_description',
							'userid',
						],
					),
					'wildcard_end' => implode( '|', [ 'ct_', 'cf_' ] ),
				],
			);
		}

		return preg_match( "/%%(?:{$tags['simple']})%%/", $text )
			|| preg_match( "/%%(?:{$tags['wildcard_end']})[^%]+?%%/", $text );
	}

	/**
	 * Determines if the input text has transformative Rank Math syntax.
	 *
	 * @link <https://theseoframework.com/extensions/transport/#faq/what-data-is-transformed>
	 *       Wank Math has no documentation on this list, but we sampled their code.
	 * @since 4.2.7
	 * @since 4.2.8 Actualized the variable list.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `has_rankmath_syntax`.
	 *
	 * @param string $text The text to evaluate.
	 * @return bool
	 */
	public static function text_has_rank_math_syntax( $text ) {

		// %id% is the shortest valid tag... ish. Let's stop at 4.
		if ( \strlen( $text ) < 4 || ! str_contains( $text, '%' ) )
			return false;

		$tags = umemo( __METHOD__ . '/tags' );

		if ( empty( $tags ) ) {
			$tags = umemo(
				__METHOD__ . '/tags',
				[
					'simple'       => implode(
						'|',
						[
							// These are Preserved by Transport. Test first, for they are more likely in text.
							'currenttime', // Rank Math has two currenttime, this one is simple.
							'filename',
							'focuskw',
							'group_desc',
							'group_name',
							'keywords',
							'org_name',
							'org_logo',
							'org_url',
							'page',
							'pagenumber',
							'pagetotal',
							'post_thumbnail',
							'primary_category',
							'primary_taxonomy_terms',
							'url',
							'wc_brand',
							'wc_price',
							'wc_shortdesc',
							'wc_sku',
							'currenttime', // Rank Math has two currenttime, this one is simple.

							// These are transformed by Transport
							'category',
							'categories',
							'currentdate',
							'currentday',
							'currentmonth',
							'currentyear',
							'date',
							'excerpt',
							'excerpt_only',
							'id',
							'modified',
							'name',
							'parent_title',
							'post_author',
							'pt_plural',
							'pt_single',
							'seo_title',
							'seo_description',
							'sep',
							'sitedesc',
							'sitename',
							'tag',
							'tags',
							'term',
							'term_description',
							'title',
							'user_description',
							'userid',
						],
					),
					// Check out for ref RankMath\Replace_Variables\Replacer::set_up_replacements();
					'wildcard_end' => implode(
						'|',
						[
							'categories',
							'count',
							'currenttime',
							'customfield',
							'customterm',
							'customterm_desc',
							'date',
							'modified',
							'tags',
						],
					),
				],
			);
		}

		return preg_match( "/%(?:{$tags['simple']})%/", $text )
			|| preg_match( "/%(?:{$tags['wildcard_end']})\([^\)]+?\)%/", $text );
	}

	/**
	 * Determines if the input text has transformative SEOPress syntax.
	 *
	 * @link <https://theseoframework.com/extensions/transport/#faq/what-data-is-transformed>
	 *       SEOPress has no documentation on this list, but we sampled their code.
	 * @since 4.2.8
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `has_seopress_syntax`.
	 *
	 * @param string $text The text to evaluate.
	 * @return bool
	 */
	public static function text_has_seopress_syntax( $text ) {

		// %%sep%% is the shortest valid tag... ish. Let's stop at 7.
		if ( \strlen( $text ) < 7 || ! str_contains( $text, '%%' ) )
			return false;

		$tags = umemo( __METHOD__ . '/tags' );

		if ( empty( $tags ) ) {
			$tags = umemo(
				__METHOD__ . '/tags',
				[
					'simple'       => implode(
						'|',
						[
							// These are Preserved by Transport. Test first, for they are more likely in text.
							'author_website',
							'current_pagination',
							'currenttime',
							'post_thumbnail_url',
							'post_url',
							'target_keyword',
							'wc_single_price',
							'wc_single_price_exc_tax',
							'wc_sku',

							// These are transformed by Transport
							'_category_description',
							'_category_title',
							'archive_title',
							'author_bio',
							'author_first_name',
							'author_last_name',
							'author_nickname',
							'currentday',
							'currentmonth',
							'currentmonth_num',
							'currentmonth_short',
							'currentyear',
							'date',
							'excerpt',
							'post_author',
							'post_category',
							'post_content',
							'post_date',
							'post_excerpt',
							'post_modified_date',
							'post_tag',
							'post_title',
							'sep',
							'sitedesc',
							'sitename',
							'sitetitle',
							'tag_description',
							'tag_title',
							'tagline',
							'term_description',
							'term_title',
							'title',
							'wc_single_cat',
							'wc_single_short_desc',
							'wc_single_tag',
						],
					),
					// Check out for ref somewhere in SEOPress, seopress_get_dyn_variables() is one I guess.
					'wildcard_end' => implode(
						'|',
						[
							'_cf_',
							'_ct_',
							'_ucf_',
						],
					),
				],
			);
		}

		return preg_match( "/%%(?:{$tags['simple']})%%/", $text )
			|| preg_match( "/%%(?:{$tags['wildcard_end']})[^%]+?%%/", $text );
	}
}
