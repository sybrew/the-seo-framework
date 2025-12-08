<?php
/**
 * @package The_SEO_Framework\Classes\Front\Front\Meta\Generator
 * @subpackage The_SEO_Framework\Meta\Facebook
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

		if ( $publisher )
			yield 'article:publisher' => [
				'attributes' => [
					'property' => 'article:publisher',
					'content'  => $publisher,
				],
			];
	}
}
