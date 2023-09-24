<?php
/**
 * @package The_SEO_Framework\Classes\Front\Meta\Factory\Schema\Entities\Webpage
 * @subpackage The_SEO_Framework\Meta\Schema
 */

namespace The_SEO_Framework\Meta\Factory\Schema\Entities;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\Utils\normalize_generation_args;

use \The_SEO_Framework\Data,
	\The_SEO_Framework\Meta\Factory,
	\The_SEO_Framework\Helper\Query;

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
 * Holds WebPage generator for Schema.org structured data.
 *
 * @since 4.3.0
 * @access protected
 */
final class WebPage extends Reference {

	/**
	 * @since 4.3.0
	 * @var callable[] BUILDERS A list of autoloaded builder callbacks.
	 */
	const BUILDERS = [
		[ __CLASS__, 'build' ],
	];

	/**
	 * @since 4.3.0
	 * @var string|string[] $type The Schema @type.
	 */
	public static $type = 'WebPage';

	/**
	 * Returns the schema ID.
	 *
	 * In this case, we use a plain URL -- as that is, most locally, the absolute
	 * canonical identifier of a WebPage.
	 *
	 * @since 4.3.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'taxonomy', and 'pta'.
	 *                         Leave null to autodetermine query.
	 * @return string The ID.
	 */
	public static function get_id( $args = null ) {
		return Factory\URI::get_canonical_url( $args );
	}

	/**
	 * @since 4.3.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'taxonomy', and 'pta'.
	 *                         Leave null to autodetermine query.
	 * @return ?array $entity The Schema.org graph entity. Null on failure.
	 */
	public static function build( $args = null ) {

		$entity = [
			'@type'       => &static::$type,
			'@id'         => static::get_id( $args ),
			'url'         => Factory\URI::get_canonical_url( $args ),
			'name'        => Factory\Title::get_title( $args ),
			'description' => Factory\Description::get_description( $args ),
			'inLanguage'  => Data\Blog::get_language(),
			'isPartOf'    => WebSite::get_instant_ref(),
		];

		$entity['breadcrumb'] = &BreadcrumbList::get_dynamic_ref(); // TODO

		if ( null === $args ) {
			if ( Query::is_singular() ) {
				$entity['potentialAction'] = [
					'@type'  => 'ReadAction',
					'target' => Factory\URI::get_canonical_url(),
				];

				if ( Query::is_single() ) {
					$entity['datePublished'] = Data\Post::get_post_published_time();
					$entity['dateModified']  = Data\Post::get_post_modified_time();
					$entity['author']        = &Author::get_dynamic_ref();
				}
			}

			if ( Query::is_real_front_page() ) {
				$entity['about'] = &Organization::get_dynamic_ref();
			}
		} else {
			normalize_generation_args( $args );

			if ( empty( $args['taxonomy'] ) && empty( $args['pta'] ) ) {
				$entity['potentialAction'] = [
					'@type'  => 'ReadAction',
					'target' => Factory\URI::get_canonical_url( $args ),
				];

				if ( Query::is_static_frontpage( $args['id'] ) )
					$entity['about'] = &Organization::get_dynamic_ref();

				if ( Query::is_single( $args['id'] ) ) {
					$entity['datePublished'] = Data\Post::get_post_published_time( $args['id'] );
					$entity['dateModified']  = Data\Post::get_post_modified_time( $args['id'] );
					$entity['author']        = &Author::get_dynamic_ref( [
						'author_id' => \tsf()->get_post_author_id( $args['id'] ),
					] );
				}
			}
		}

		return $entity;
	}
}