<?php
/**
 * @package The_SEO_Framework\Classes\Meta\Schema\Entities\Webpage
 * @subpackage The_SEO_Framework\Meta\Schema
 */

namespace The_SEO_Framework\Meta\Schema\Entities;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\{
	get_query_type_from_args,
	normalize_generation_args,
};

use \The_SEO_Framework\{
	Data,
	Meta,
	Helper\Query,
};

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
 * Holds WebPage generator for Schema.org structured data.
 *
 * @since 5.0.0
 * @access protected
 */
final class WebPage extends Reference {

	/**
	 * @since 5.0.0
	 * @var callable[] BUILDERS A list of autoloaded builder callbacks.
	 */
	const BUILDERS = [
		[ __CLASS__, 'build' ],
	];

	/**
	 * @since 5.0.0
	 * @var string|string[] $type The Schema @type.
	 */
	public static $type = 'WebPage';

	/**
	 * Returns the schema ID.
	 *
	 * In this case, we use a plain URL -- as that is, most locally, the absolute
	 * canonical identifier of a WebPage.
	 *
	 * @since 5.0.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return string The ID.
	 */
	public static function get_id( $args = null ) {
		return Meta\URI::get_canonical_url( $args );
	}

	/**
	 * @since 5.0.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return ?array $entity The Schema.org graph entity. Null on failure.
	 */
	public static function build( $args = null ) {

		// We write to this type, reset it.
		static::$type = 'WebPage';

		$entity = [
			'@type'       => &static::$type,
			'@id'         => static::get_id( $args ),
			'url'         => Meta\URI::get_canonical_url( $args ),
			'name'        => Meta\Title::get_title( $args ),
			'description' => Meta\Description::get_description( $args ),
			'inLanguage'  => Data\Blog::get_language(),
			'isPartOf'    => WebSite::get_instant_ref(),
		];

		if ( Data\Plugin::get_option( 'ld_json_breadcrumbs' ) )
			$entity['breadcrumb'] = &BreadcrumbList::get_dynamic_ref( $args );

		if ( isset( $args ) ) {
			normalize_generation_args( $args );

			switch ( get_query_type_from_args( $args ) ) {
				case 'single':
					$entity['potentialAction'] = [
						'@type'  => 'ReadAction',
						'target' => Meta\URI::get_canonical_url( $args ),
					];

					if ( Data\Plugin::get_option( 'knowledge_output' ) && Query::is_static_front_page( $args['id'] ) )
						$entity['about'] = &Organization::get_dynamic_ref(); // Organization doesn't use args.

					if ( Query::is_single( $args['id'] ) ) {
						$entity['datePublished'] = Data\Post::get_published_time( $args['id'] );
						$entity['dateModified']  = Data\Post::get_modified_time( $args['id'] );
						$entity['author']        = &Author::get_dynamic_ref( [
							'uid' => Query::get_post_author_id( $args['id'] ),
						] );
					}

					if ( Query::is_singular_archive( $args['id'] ) )
						static::$type = 'CollectionPage';
					break;
				case 'term':
					static::$type = 'CollectionPage';
			}
		} else {
			if ( Query::is_singular() ) {
				$entity['potentialAction'] = [
					'@type'  => 'ReadAction',
					'target' => Meta\URI::get_canonical_url(),
				];

				if ( Query::is_single() ) {
					$entity['datePublished'] = Data\Post::get_published_time();
					$entity['dateModified']  = Data\Post::get_modified_time();
					$entity['author']        = &Author::get_dynamic_ref();
				}
			}

			if ( Data\Plugin::get_option( 'knowledge_output' ) && Query::is_real_front_page() )
				$entity['about'] = &Organization::get_dynamic_ref();

			if ( Query::is_archive() || Query::is_singular_archive() ) {
				static::$type = 'CollectionPage';
			} elseif ( Query::is_search() ) {
				static::$type = [ 'CollectionPage', 'SearchResultsPage' ];
			}
		}

		return $entity;
	}
}
