<?php
/**
 * @package The_SEO_Framework\Classes\Meta\Schema\Entities\Webpage
 * @subpackage The_SEO_Framework\Meta\Schema
 */

namespace The_SEO_Framework\Meta\Schema\Entities;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\{
	Data,
	Data\Filter\Sanitize,
	Meta,
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
 * Holds WebSite generator for Schema.org structured data.
 *
 * @since 5.0.0
 * @access protected
 */
final class WebSite extends Reference {

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
	public static $type = 'WebSite';

	/**
	 * @since 5.0.0
	 * @since 5.0.5 Added back alternateName.
	 * @see https://developers.google.com/search/docs/appearance/structured-data/sitelinks-searchbox
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return ?array $entity The Schema.org graph entity. Null on failure.
	 */
	public static function build( $args = null ) { // phpcs:ignore -- VariableAnalysis, abstract.

		// Sanitize them both, even if empty, so we can normalize them to compare later.
		$name    = Sanitize::metadata_content( Data\Blog::get_public_blog_name() );
		$altname = Sanitize::metadata_content( Data\Plugin::get_option( 'knowledge_name' ) );

		$entity = [
			'@type'         => static::$type,
			'@id'           => static::get_id(),
			'url'           => Meta\URI::get_bare_front_page_url(),
			'name'          => $name,
			'alternateName' => $name === $altname ? '' : $altname,
			'description'   => Sanitize::metadata_content( Data\Blog::get_filtered_blog_description() ),
			'inLanguage'    => Data\Blog::get_language(),
		];

		if ( Data\Plugin::get_option( 'ld_json_searchbox' ) ) {
			// No reference because this isn't shared.
			$entity['potentialAction'] = [
				'@type'       => 'SearchAction',
				'target'      => [
					'@type'       => 'EntryPoint',
					'urlTemplate' => str_replace(
						'tsf_search_term_string',
						'{search_term_string}',
						Meta\URI::get_bare_search_url( 'tsf_search_term_string' )
					),
				],
				'query-input' => 'required name=search_term_string',
			];
		}

		if ( Data\Plugin::get_option( 'knowledge_output' ) ) {
			if ( 'organization' === Data\Plugin::get_option( 'knowledge_type' ) ) {
				$entity['publisher'] = &Organization::get_dynamic_ref();
			} else {
				$entity['publisher'] = &Person::get_dynamic_ref();
			}
		}

		return $entity;
	}
}
