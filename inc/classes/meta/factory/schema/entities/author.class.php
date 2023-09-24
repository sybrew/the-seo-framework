<?php
/**
 * @package The_SEO_Framework\Classes\Front\Meta\Factory\Schema\Entities\Webpage
 * @subpackage The_SEO_Framework\Meta\Schema
 */

namespace The_SEO_Framework\Meta\Factory\Schema\Entities;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\Utils\{
	normalize_generation_args,
	clamp_sentence,
};

use \The_SEO_Framework\Data,
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
 * Holds Author generator for Schema.org structured data.
 * Not to be confused with "Person". This one represents a post.
 *
 * @since 4.3.0
 * @access protected
 */
final class Author extends Reference {

	/**
	 * @since 4.3.0
	 * @var string|string[] $type The Schema @type.
	 */
	public static $type = 'Person';

	/**
	 * @since 4.3.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'taxonomy', and 'pta'.
	 *                         Leave null to autodetermine query.
	 *                         Also accepts 'author_id';
	 * @return int The author ID. 0 on failure.
	 */
	private static function get_author_id_from_args( $args ) {

		if ( null === $args ) {
			$author_id = \tsf()->get_post_author_id();
		} else {
			if ( isset( $args['author_id'] ) ) {
				$author_id = $args['author_id'];
			} else {
				normalize_generation_args( $args );

				if ( empty( $args['taxonomy'] ) && empty( $args['pta'] ) ) {
					$author_id = \tsf()->get_post_author_id( $args['id'] );
				}
			}
		}

		return $author_id ?? 0;
	}

	/**
	 * @since 4.3.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'taxonomy', and 'pta'.
	 *                         Leave null to autodetermine query.
	 *                         Also accepts 'author_id';
	 * @return string The entity ID for $args.
	 */
	public static function get_id( $args = null ) {

		$author_id = static::get_author_id_from_args( $args );

		if ( empty( $author_id ) )
			return '';

		return Factory\URI::get_bare_front_page_canonical_url()
			. '#/schema/' . current( (array) static::$type ) . '/' . \wp_hash( "tsf+$author_id" );
	}

	/**
	 * @since 4.3.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'taxonomy', and 'pta'.
	 *                         Leave null to autodetermine query.
	 *                         Also accepts 'author_id';
	 * @return ?array $entity The Schema.org graph entity.
	 */
	public static function build( $args = null ) {

		$author_id = static::get_author_id_from_args( $args );

		if ( empty( $author_id ) ) return null;

		$user_data = \get_userdata( $author_id );
		$user_meta = \tsf()->get_user_meta( $author_id );

		$entity = [
			'@type' => static::$type,
			'@id'   => static::get_id( [ 'author_id' => $author_id ] ),
			'name'  => $user_data->display_name ?? '',
			// Let's not; may invoke bad bots. Let's do this via sameas.
			// 'url'   => Factory\URI::get_bare_author_canonical_url( $author_id ),
		];

		if ( $user_meta['facebook_page'] )
			$entity['sameAs'][] = \sanitize_url( $user_meta['facebook_page'], [ 'https', 'http' ] );
		if ( $user_meta['twitter_page'] )
			$entity['sameAs'][] = \sanitize_url( 'https://twitter.com/' . ltrim( $user_meta['twitter_page'], '@' ) );

		if ( $user_data->description )
			$entity['description'] = clamp_sentence( \wp_strip_all_tags( $user_data->description ), 1, 250 );

		return $entity;
	}
}
