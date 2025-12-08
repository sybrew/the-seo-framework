<?php
/**
 * @package The_SEO_Framework\Classes\Front\Front\Meta\Generator
 * @subpackage The_SEO_Framework\Meta\URI
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
 * Holds URI generators for meta tag output.
 *
 * @since 5.0.0
 * @access private
 */
final class URI {

	/**
	 * @since 5.0.0
	 * @var callable[] GENERATORS A list of autoloaded meta callbacks.
	 */
	public const GENERATORS = [
		[ __CLASS__, 'generate_canonical_url' ],
		[ __CLASS__, 'generate_pagination_urls' ],
		[ __CLASS__, 'generate_shortlink' ],
	];

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_canonical_url() {

		$url = Meta\URI::get_indexable_canonical_url();

		if ( $url )
			yield 'canonical' => [
				'tag'        => 'link',
				'attributes' => [
					'rel'  => 'canonical',
					'href' => $url,
				],
			];
	}

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_pagination_urls() {

		[ $prev, $next ] = Meta\URI::get_paged_urls();

		if ( $prev )
			yield 'prev' => [
				'tag'        => 'link',
				'attributes' => [
					'rel'  => 'prev',
					'href' => $prev,
				],
			];

		if ( $next )
			yield 'next' => [
				'tag'        => 'link',
				'attributes' => [
					'rel'  => 'next',
					'href' => $next,
				],
			];
	}

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_shortlink() {

		$url = Meta\URI::get_shortlink_url();

		if ( $url )
			yield 'shortlink' => [
				'tag'        => 'link',
				'attributes' => [
					'rel'  => 'shortlink',
					'href' => $url,
				],
			];
	}
}
