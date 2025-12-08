<?php
/**
 * @package The_SEO_Framework\Classes\Front\Front\Meta\Generator
 * @subpackage The_SEO_Framework\Meta\Open_Graph
 */

namespace The_SEO_Framework\Front\Meta\Generator;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use The_SEO_Framework\{
	Data,
	Meta,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 - 2025 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * @since 5.0.0
 * @access private
 */
final class Open_Graph {

	/**
	 * @since 5.0.0
	 * @var callable[] GENERATORS A list of autoloaded meta callbacks.
	 */
	public const GENERATORS = [
		[ __CLASS__, 'generate_open_graph_type' ],
		[ __CLASS__, 'generate_open_graph_locale' ],
		[ __CLASS__, 'generate_open_graph_site_name' ],
		[ __CLASS__, 'generate_open_graph_title' ],
		[ __CLASS__, 'generate_open_graph_description' ],
		[ __CLASS__, 'generate_open_graph_url' ],
		[ __CLASS__, 'generate_open_graph_image' ],
		[ __CLASS__, 'generate_article_published_time' ],
		[ __CLASS__, 'generate_article_modified_time' ],
	];

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_open_graph_type() {

		$type = Meta\Open_Graph::get_type();

		if ( $type )
			yield 'og:type' => [
				'attributes' => [
					'property' => 'og:type',
					'content'  => $type,
				],
			];
	}

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_open_graph_locale() {

		$locale = Meta\Open_Graph::get_locale();

		if ( $locale )
			yield 'og:locale' => [
				'attributes' => [
					'property' => 'og:locale',
					'content'  => $locale,
				],
			];
	}

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_open_graph_site_name() {

		$sitename = Meta\Open_Graph::get_site_name();

		// A site called '0' does not make much sense.
		if ( $sitename )
			yield 'og:site_name' => [
				'attributes' => [
					'property' => 'og:site_name',
					'content'  => $sitename,
				],
			];
	}

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_open_graph_title() {

		$title = Meta\Open_Graph::get_title();

		if ( \strlen( $title ) )
			yield 'og:title' => [
				'attributes' => [
					'property' => 'og:title',
					'content'  => $title,
				],
			];
	}

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_open_graph_description() {

		$description = Meta\Open_Graph::get_description();

		if ( \strlen( $description ) )
			yield 'og:description' => [
				'attributes' => [
					'property' => 'og:description',
					'content'  => $description,
				],
			];
	}

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_open_graph_url() {

		$url = Meta\Open_Graph::get_url();

		if ( $url )
			yield 'og:url' => [
				'attributes' => [
					'property' => 'og:url',
					'content'  => $url,
				],
			];
	}

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_open_graph_image() {

		$i = 0;
		foreach ( Meta\Image::get_image_details(
			null,
			! Data\Plugin::get_option( 'multi_og_image' ),
		) as $image ) {
			yield "og:image:$i" => [
				'attributes' => [
					'property' => 'og:image',
					'content'  => $image['url'],
				],
			];

			if ( $image['height'] && $image['width'] ) {
				yield "og:image:width:$i" => [
					'attributes' => [
						'property' => 'og:image:width',
						'content'  => $image['width'],
					],
				];
				yield "og:image:height:$i" => [
					'attributes' => [
						'property' => 'og:image:height',
						'content'  => $image['height'],
					],
				];
			}

			if ( $image['alt'] ) {
				yield "og:image:alt:$i" => [
					'attributes' => [
						'property' => 'og:image:alt',
						'content'  => $image['alt'],
					],
				];
			}

			++$i;
		}
	}

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_article_published_time() {

		$time = Meta\Open_Graph::get_article_published_time();

		if ( $time )
			yield 'article:published_time' => [
				'attributes' => [
					'property' => 'article:published_time',
					'content'  => $time,
				],
			];
	}

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_article_modified_time() {

		$time = Meta\Open_Graph::get_article_modified_time();

		if ( $time )
			yield 'article:modified_time' => [
				'attributes' => [
					'property' => 'article:modified_time',
					'content'  => $time,
				],
			];
	}
}
