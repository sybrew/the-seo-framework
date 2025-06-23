<?php
/**
 * @package The_SEO_Framework\Classes\Front\Front\Meta\Generator
 * @subpackage The_SEO_Framework\Meta\Author
 */

namespace The_SEO_Framework\Front\Meta\Generator;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use The_SEO_Framework\{
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
 * @since 5.0.0
 * @access private
 */
final class Author {

	/**
	 * @since 5.0.0
	 * @var callable[] GENERATORS A list of autoloaded meta callbacks.
	 */
	public const GENERATORS = [
		[ __CLASS__, 'generate_author_meta' ],
	];

	/**
	 * Generates the author meta tag.
	 *
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_author_meta() {

		// Only output on singular posts/pages where an author is available
		if ( ! Query::is_singular() ) return;

		$author_name = static::get_author_name();

		if ( $author_name )
			yield 'author' => [
				'attributes' => [
					'name'    => 'author',
					'content' => $author_name,
				],
			];
	}

	/**
	 * Gets the author name for the current post.
	 *
	 * @since 5.0.0
	 * @return string The author's display name, empty if not available.
	 */
	private static function get_author_name() {

		$author_id = Query::get_post_author_id();

		if ( ! $author_id ) return '';

		$user_data = \get_userdata( $author_id );

		if ( ! $user_data ) return '';

		return $user_data->display_name ?? '';
	}
}