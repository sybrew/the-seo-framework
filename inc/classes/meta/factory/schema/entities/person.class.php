<?php
/**
 * @package The_SEO_Framework\Classes\Front\Meta\Factory\Schema\Entities\Webpage
 * @subpackage The_SEO_Framework\Meta\Schema
 */

namespace The_SEO_Framework\Meta\Factory\Schema\Entities;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

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
 * Holds Person generator for Schema.org structured data.
 * Not to be confused with "Author". This one represents the entire website.
 *
 * @since 4.3.0
 * @access protected
 */
final class Person extends Reference {

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
	 * @return ?array $entity The Schema.org graph entity.
	 */
	public static function build( $args = null ) { // phpcs:ignore -- VariableAnalysis, abstract.

		$entity = [
			'@type' => static::$type,
			'@id'   => static::get_id(),
			'name'  => \tsf()->get_option( 'knowledge_name' ) ?: Data\Blog::get_public_blog_name(),
			'url'   => Factory\URI::get_bare_front_page_canonical_url(),
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
			$option = \tsf()->get_option( $option );

			if ( $option )
				$entity['sameAs'][] = \sanitize_url( $option, [ 'https', 'http' ] );
		}

		return $entity;
	}
}
