<?php
/**
 * @package The_SEO_Framework\Classes\Front\Front\Meta\Generator
 * @subpackage The_SEO_Framework\Meta\Author
 */

namespace The_SEO_Framework\Front\Meta\Generator;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use The_SEO_Framework\{
	Meta,
	Data,
	Helper\Query,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2025 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds Author generators for meta tag output.
 *
 * @since 5.1.3
 * @access private
 */
final class Author {

	/**
	 * @since 5.1.3
	 * @var callable[] GENERATORS A list of autoloaded meta callbacks.
	 */
	public const GENERATORS = [
		[ __CLASS__, 'generate_author' ],
	];

	/**
	 * Generates the author meta tag.
	 *
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_author() {

		// Only output on singular posts/pages where an author is available
		if ( ! Query::is_singular() ) return;

		$author_name = Meta\Author::get_author_name();

		if ( \strlen( $author_name ) )
			yield 'author' => [
				'attributes' => [
					'name'    => 'author',
					'content' => $author_name,
				],
			];
	}
}
