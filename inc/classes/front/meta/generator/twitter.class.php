<?php
/**
 * @package The_SEO_Framework\Classes\Front\Front\Meta\Generator
 * @subpackage The_SEO_Framework\Meta\Twitter
 */

namespace The_SEO_Framework\Front\Meta\Generator;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use The_SEO_Framework\Meta;

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
 * Holds Twitter generators for meta tag output.
 *
 * @since 5.0.0
 * @access private
 */
final class Twitter {

	/**
	 * @since 5.0.0
	 * @var callable[] GENERATORS A list of autoloaded meta callbacks.
	 */
	public const GENERATORS = [
		[ __CLASS__, 'generate_twitter_card' ],
		[ __CLASS__, 'generate_twitter_site' ],
		[ __CLASS__, 'generate_twitter_creator' ],
		[ __CLASS__, 'generate_twitter_title' ],
		[ __CLASS__, 'generate_twitter_description' ],
		[ __CLASS__, 'generate_twitter_image' ],
	];

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_twitter_card() {

		$card = Meta\Twitter::get_card_type();

		if ( $card )
			yield 'twitter:card' => [
				'attributes' => [
					'name'    => 'twitter:card',
					'content' => $card,
				],
			];
	}

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_twitter_site() {

		$site = Meta\Twitter::get_site();

		if ( $site )
			yield 'twitter:site' => [
				'attributes' => [
					'name'    => 'twitter:site',
					'content' => $site,
				],
			];
	}

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_twitter_creator() {

		$creator = Meta\Twitter::get_creator();

		if ( $creator )
			yield 'twitter:creator' => [
				'attributes' => [
					'name'    => 'twitter:creator',
					'content' => $creator,
				],
			];
	}

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_twitter_title() {

		$title = Meta\Twitter::get_title();

		if ( \strlen( $title ) )
			yield 'twitter:title' => [
				'attributes' => [
					'name'    => 'twitter:title',
					'content' => $title,
				],
			];
	}

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_twitter_description() {

		$description = Meta\Twitter::get_description();

		if ( \strlen( $description ) )
			yield 'twitter:description' => [
				'attributes' => [
					'name'    => 'twitter:description',
					'content' => $description,
				],
			];
	}

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_twitter_image() {

		// Only grab a single image. Twitter grabs the final (less favorable) image otherwise.
		$image = current( Meta\Image::get_image_details( null, true ) );

		if ( $image ) {
			yield 'twitter:image' => [
				'attributes' => [
					'name'    => 'twitter:image',
					'content' => $image['url'],
				],
			];

			if ( $image['alt'] ) {
				yield 'twitter:image:alt' => [
					'attributes' => [
						'name'    => 'twitter:image:alt',
						'content' => $image['alt'],
					],
				];
			}
		}
	}
}
