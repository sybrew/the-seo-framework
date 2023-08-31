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
 * Holds Open Graph generators for meta tag output.
 *
 * @since 4.3.0
 * @access protected
 * @internal
 * @final Can't be extended.
 */
final class Open_Graph {

	/**
	 * @since 4.3.0
	 * @var callable[] GENERATORS A list of autoloaded meta callbacks.
	 */
	public const GENERATORS = [
		[ __CLASS__, 'generate_open_graph' ],
	];

	/**
	 * @since 4.3.0
	 * @access protected
	 * @generator
	 */
	public static function generate_open_graph() {
		/**
		 * @since 3.1.4
		 * @since 4.3.0 Deprecated
		 * @deprecated
		 * @param bool $use_open_graph
		 */
		$use_open_graph = \apply_filters_deprecated(
			'the_seo_framework_use_og_tags',
			[
				(bool) \tsf()->get_option( 'og_tags' ),
			],
			'4.3.0 of The SEO Framework',
			'the_seo_framework_meta_generators',
		);

		if ( $use_open_graph ) {
			yield from static::generate_open_graph_type();
			yield from static::generate_open_graph_locale();
			yield from static::generate_open_graph_site_name();
			yield from static::generate_open_graph_title();
			yield from static::generate_open_graph_description();
			yield from static::generate_open_graph_url();
			yield from static::generate_open_graph_image();
		}
	}

	/**
	 * @since 4.3.0
	 * @access protected
	 * @generator
	 */
	public static function generate_open_graph_type() {

		$type = \tsf()->get_og_type();

		if ( $type )
			yield [
				'attributes' => [
					'property' => 'og:type',
					'content'  => $type,
				],
			];
	}

	/**
	 * @since 4.3.0
	 * @access protected
	 * @generator
	 */
	public static function generate_open_graph_locale() {

		$locale = \tsf()->fetch_locale();

		if ( \has_filter( 'the_seo_framework_ogdescription_output' ) ) {
			/**
			 * @since 2.3.0
			 * @since 2.7.0 Added output within filter.
			 * @since 4.3.0 Deprecated
			 * @deprecated
			 * @param string $locale The generated locale field.
			 * @param int    $id     The page or term ID.
			 */
			$locale = (string) \apply_filters_deprecated(
				'the_seo_framework_oglocale_output',
				[
					$locale,
					\The_SEO_Framework\Helper\Query::get_the_real_id(),
				],
				'4.3.0 of The SEO Framework',
				'the_seo_framework_meta_render_data',
			);
		}

		if ( $locale )
			yield [
				'attributes' => [
					'property' => 'og:locale',
					'content'  => $locale,
				],
			];
	}

	/**
	 * @since 4.3.0
	 * @access protected
	 * @generator
	 */
	public static function generate_open_graph_site_name() {

		$sitename = \tsf()->get_blogname();

		if ( \has_filter( 'the_seo_framework_ogsitename_output' ) ) {
			/**
			 * @since 2.3.0
			 * @since 2.7.0 Added output within filter.
			 * @since 4.3.0 Deprecated
			 * @deprecated
			 * @param string $locale The generated Open Graph site name.
			 * @param int    $id     The page or term ID.
			 */
			$sitename = (string) \apply_filters_deprecated(
				'the_seo_framework_ogsitename_output',
				[
					$sitename,
					\The_SEO_Framework\Helper\Query::get_the_real_id(),
				],
				'4.3.0 of The SEO Framework',
				'the_seo_framework_meta_render_data',
			);
		}

		if ( $sitename )
			yield [
				'attributes' => [
					'property' => 'og:site_name',
					'content'  => $sitename,
				],
			];
	}

	/**
	 * @since 4.3.0
	 * @access protected
	 * @generator
	 */
	public static function generate_open_graph_title() {

		$title = \tsf()->get_open_graph_title();

		if ( \has_filter( 'the_seo_framework_ogtitle_output' ) ) {
			/**
			 * @since 2.3.0
			 * @since 2.7.0 Added output within filter.
			 * @since 4.3.0 Deprecated
			 * @deprecated
			 * @param string $title The generated Open Graph title.
			 * @param int    $id    The page or term ID.
			 */
			$title = (string) \apply_filters_deprecated(
				'the_seo_framework_ogtitle_output',
				[
					$title,
					\The_SEO_Framework\Helper\Query::get_the_real_id(),
				],
				'4.3.0 of The SEO Framework',
				'the_seo_framework_meta_render_data',
			);
		}

		if ( $title )
			yield [
				'attributes' => [
					'property' => 'og:title',
					'content'  => $title,
				],
			];
	}

	/**
	 * @since 4.3.0
	 * @access protected
	 * @generator
	 */
	public static function generate_open_graph_description() {

		$description = \tsf()->get_open_graph_description();

		if ( \has_filter( 'the_seo_framework_ogdescription_output' ) ) {
			/**
			 * @since 2.3.0
			 * @since 2.7.0 Added output within filter.
			 * @since 4.3.0 Deprecated
			 * @deprecated
			 * @param string $description The generated Open Graph description.
			 * @param int    $id          The page or term ID.
			 */
			$description = (string) \apply_filters_deprecated(
				'the_seo_framework_ogdescription_output',
				[
					$description,
					\The_SEO_Framework\Helper\Query::get_the_real_id(),
				],
				'4.3.0 of The SEO Framework',
				'the_seo_framework_meta_render_data',
			);
		}

		if ( $description )
			yield [
				'attributes' => [
					'property' => 'og:description',
					'content'  => $description,
				],
			];
	}

	/**
	 * @since 4.3.0
	 * @access protected
	 * @generator
	 */
	public static function generate_open_graph_url() {

		$url = \tsf()->get_current_canonical_url();

		if ( \has_filter( 'the_seo_framework_ogurl_output' ) ) {
			/**
			 * @since 2.9.3
			 * @since 4.3.0 Deprecated
			 * @deprecated
			 * @param string $url The canonical/Open Graph URL. Must be escaped.
			 * @param int    $id  The page or term ID.
			 */
			$url = (string) \apply_filters_deprecated(
				'the_seo_framework_ogurl_output',
				[
					$url,
					\The_SEO_Framework\Helper\Query::get_the_real_id(),
				],
				'4.3.0 of The SEO Framework',
				'the_seo_framework_meta_render_data',
			);
		}

		if ( $url )
			yield [
				'attributes' => [
					'property' => 'og:url',
					'content'  => $url,
				],
			];
	}

	/**
	 * @since 4.3.0
	 * @access protected
	 * @generator
	 */
	public static function generate_open_graph_image() {

		$tsf = \tsf();

		$multi = $tsf->get_option( 'multi_og_image' );

		foreach ( $tsf->get_image_details_from_cache( ! $multi ) as $image ) {
			yield [
				'attributes' => [
					'property' => 'og:image',
					'content'  => $image['url'],
				],
			];

			if ( $image['height'] && $image['width'] ) {
				yield [
					'attributes' => [
						'property' => 'og:image:width',
						'content'  => $image['width'],
					],
				];
				yield [
					'attributes' => [
						'property' => 'og:image:height',
						'content'  => $image['height'],
					],
				];
			}

			if ( $image['alt'] ) {
				yield [
					'attributes' => [
						'property' => 'og:image:alt',
						'content'  => $image['alt'],
					],
				];
			}

			// Redundant?
			if ( ! $multi ) break;
		}
	}

	/**
	 * Renders Article Publishing Time meta tag.
	 *
	 * @since 2.2.2
	 * @since 2.8.0 Returns empty on product pages.
	 * @since 3.0.0 1. Now checks for 0000 timestamps.
	 *              2. Now uses timestamp formats.
	 *              3. Now uses GMT time.
	 *
	 * @return string The Article Publishing Time meta tag.
	 */
	public function article_published_time() {

		if ( ! $this->output_published_time() ) return '';

		$id            = Query::get_the_real_id();
		$post_date_gmt = \get_post( $id )->post_date_gmt ?? '0000-00-00 00:00:00';

		if ( '0000-00-00 00:00:00' === $post_date_gmt )
			return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @param string $time The article published time.
		 * @param int    $id   The current page or term ID.
		 */
		$time = (string) \apply_filters_ref_array(
			'the_seo_framework_publishedtime_output',
			[
				$this->gmt2date( $this->get_timestamp_format(), $post_date_gmt ),
				$id,
			]
		);

		return $time ? Interpreters\Meta::render( [
			'property' => 'article:published_time',
			'content'  => $time,
		] ) : '';
	}

	/**
	 * Renders Article Modified Time meta tag.
	 *
	 * @since 2.2.2
	 * @since 2.7.0 Listens to Query::get_the_real_id() instead of WordPress Core ID determination.
	 * @since 2.8.0 Returns empty on product pages.
	 * @since 3.0.0 1. Now checks for 0000 timestamps.
	 *              2. Now uses timestamp formats.
	 * @since 4.1.4 No longer renders the Open Graph Updated Time meta tag.
	 * @see og_updated_time()
	 *
	 * @return string The Article Modified Time meta tag
	 */
	public function article_modified_time() {

		if ( ! $this->output_modified_time() ) return '';

		$time = $this->get_modified_time();

		return $time ? Interpreters\Meta::render( [
			'property' => 'article:modified_time',
			'content'  => $time,
		] ) : '';
	}
}
