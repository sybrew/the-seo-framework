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
 * Holds Person generator for Schema.org structured data.
 * Not to be confused with "Author". This one represents the entire website.
 *
 * @since 5.0.0
 * @access protected
 */
final class Person extends Reference {

	/**
	 * @since 5.0.0
	 * @var string|string[] $type The Schema @type.
	 */
	public static $type = 'Person';

	/**
	 * @since 5.0.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return ?array $entity The Schema.org graph entity.
	 */
	public static function build( $args = null ) { // phpcs:ignore -- VariableAnalysis, abstract.

		$entity = [
			'@type' => static::$type,
			'@id'   => static::get_id(),
			'name'  => Sanitize::metadata_content( Data\Plugin::get_option( 'knowledge_name' ) ?: Data\Blog::get_public_blog_name() ),
			'url'   => Meta\URI::get_bare_front_page_url(),
		];

		foreach ( [
			'knowledge_facebook',
			'knowledge_twitter',
			'knowledge_instagram',
			'knowledge_youtube',
			'knowledge_linkedin',
			'knowledge_pinterest',
			'knowledge_soundcloud',
			'knowledge_tumblr',
		] as $option ) {
			$option = Data\Plugin::get_option( $option );

			if ( $option )
				$entity['sameAs'][] = \sanitize_url( $option, [ 'https', 'http' ] );
		}

		return $entity;
	}
}
