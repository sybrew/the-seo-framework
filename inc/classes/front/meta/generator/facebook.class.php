<?php
/**
 * @package The_SEO_Framework\Classes\Front\Front\Meta\Generator
 * @subpackage The_SEO_Framework\Meta\Facebook
 */

namespace The_SEO_Framework\Front\Meta\Generator;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Meta;

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
 * Holds Facebook generators for meta tag output.
 *
 * @since 5.0.0
 * @access private
 */
final class Facebook {

	/**
	 * @since 5.0.0
	 * @var callable[] GENERATORS A list of autoloaded meta callbacks.
	 */
	public const GENERATORS = [
		[ __CLASS__, 'generate_article_author' ],
		[ __CLASS__, 'generate_article_publisher' ],
	];

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_article_author() {

		$author = Meta\Facebook::get_author();

		if ( \has_filter( 'the_seo_framework_facebookauthor_output' ) ) {
			/**
			 * @since 2.3.0
			 * @since 2.7.0 Added output within filter.
			 * @since 5.0.0 Deprecated
			 * @deprecated
			 * @param string $facebook_author The generated Facebook author page URL.
			 * @param int    $id              The current page or term ID.
			 */
			$author = (string) \apply_filters_deprecated(
				'the_seo_framework_facebookauthor_output',
				[
					$author,
					\The_SEO_Framework\Helper\Query::get_the_real_id(),
				],
				'5.0.0 of The SEO Framework',
				'the_seo_framework_meta_render_data',
			);
		}

		if ( $author )
			yield 'article:author' => [
				'attributes' => [
					'property' => 'article:author',
					'content'  => $author,
				],
			];
	}

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_article_publisher() {

		$publisher = Meta\Facebook::get_publisher();

		if ( \has_filter( 'the_seo_framework_facebookpublisher_output' ) ) {
			/**
			 * @since 2.3.0
			 * @since 2.7.0 Added output within filter.
			 * @since 5.0.0 Deprecated
			 * @deprecated
			 * @param string $publisher The Facebook publisher page URL.
			 * @param int    $id        The current page or term ID.
			 */
			$publisher = (string) \apply_filters_deprecated(
				'the_seo_framework_facebookpublisher_output',
				[
					$publisher,
					\The_SEO_Framework\Helper\Query::get_the_real_id(),
				],
				'5.0.0 of The SEO Framework',
				'the_seo_framework_meta_render_data',
			);
		}

		if ( $publisher )
			yield 'article:publisher' => [
				'attributes' => [
					'property' => 'article:publisher',
					'content'  => $publisher,
				],
			];
	}
}
