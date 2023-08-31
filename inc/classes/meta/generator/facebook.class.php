<?php
/**
 * @package The_SEO_Framework\Classes\Front\Meta\Generator
 * @subpackage The_SEO_Framework\Meta
 */

namespace The_SEO_Framework\Meta\Generator;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

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
 * Holds Facebook generators for meta tag output.
 *
 * @since 4.3.0
 * @access protected
 * @internal
 * @final Can't be extended.
 */
final class Facebook {

	/**
	 * @since 4.3.0
	 * @var callable[] GENERATORS A list of autoloaded meta callbacks.
	 */
	public const GENERATORS = [
		[ __CLASS__, 'generate_facebook' ],
	];

	/**
	 * @since 4.3.0
	 * @access protected
	 * @generator
	 */
	public static function generate_facebook() {
		/**
		 * @since 3.1.4
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param bool $use_facebook
		 */
		$use_facebook = \apply_filters_deprecated(
			'the_seo_framework_use_facebook_tags',
			[
				(bool) \tsf()->get_option( 'facebook_tags' ),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_generators',
		);

		if ( $use_facebook ) {
			if ( 'article' === \tsf()->get_og_type() ) {
				yield from static::generate_facebook_author();
				yield from static::generate_facebook_publisher();
			}
			yield from static::generate_facebook_app_id();
		}
	}

	/**
	 * @since 4.3.0
	 * @access protected
	 * @generator
	 */
	public static function generate_facebook_author() {

		$tsf = \tsf();

		$author =
			   $tsf->get_current_post_author_meta_item( 'facebook_page' )
			?: $tsf->get_option( 'facebook_author' );

		if ( \has_filter( 'the_seo_framework_facebookauthor_output' ) ) {
			/**
			 * @since 2.3.0
			 * @since 2.7.0 Added output within filter.
			 * @since 4.3.0 Deprecated
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
				'4.3.0 of The SEO Framework',
				'the_seo_framework_meta_render_data',
			);
		}

		if ( $author )
			yield [
				'attributes' => [
					'property' => 'article:author',
					'content'  => $author,
				],
			];
	}

	/**
	 * Renders Facebook Publisher meta tag.
	 *
	 * @since 2.2.2
	 * @since 3.0.0 No longer outputs tag when "og:type" isn't 'article'.
	 *
	 * @return string The Facebook Publisher meta tag.
	 */
	public static function generate_facebook_publisher() {

		$publisher = \tsf()->get_option( 'facebook_publisher' );

		if ( \has_filter( 'the_seo_framework_facebookauthor_output' ) ) {
			/**
			 * @since 2.3.0
			 * @since 2.7.0 Added output within filter.
			 * @since 4.3.0 Deprecated
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
				'4.3.0 of The SEO Framework',
				'the_seo_framework_meta_render_data',
			);
		}

		if ( $publisher )
			yield [
				'attributes' => [
					'property' => 'article:publisher',
					'content'  => $publisher,
				],
			];
	}

	/**
	 * Renders Facebook App ID meta tag.
	 *
	 * @since 2.2.2
	 *
	 * @return string The Facebook App ID meta tag.
	 */
	public static function generate_facebook_app_id() {

		$app_id = \tsf()->get_option( 'facebook_appid' );

		if ( \has_filter( 'the_seo_framework_facebookauthor_output' ) ) {
			/**
			 * @since 2.3.0
			 * @since 2.7.0 Added output within filter.
			 * @since 4.3.0 Deprecated
			 * @deprecated
			 * @param string $app_id The Facebook app ID.
			 * @param int    $id     The current page or term ID.
			 */
			$app_id = (string) \apply_filters_deprecated(
				'the_seo_framework_facebookappid_output',
				[
					$app_id,
					\The_SEO_Framework\Helper\Query::get_the_real_id(),
				],
				'4.3.0 of The SEO Framework',
				'the_seo_framework_meta_render_data',
			);
		}

		if ( $app_id )
			yield [
				'attributes' => [
					'property' => 'fb:app_id',
					'content'  => $app_id,
				],
			];
	}
}
