<?php
/**
 * @package The_SEO_Framework\Classes\Front\Front\Meta\Generator
 * @subpackage The_SEO_Framework\Meta\Robots
 */

namespace The_SEO_Framework\Front\Meta\Generator;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Meta;

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
 * Holds robots generators for meta tag output.
 *
 * @since 5.0.0
 * @access private
 */
final class Robots {

	/**
	 * @since 5.0.0
	 * @var callable[] GENERATORS A list of autoloaded meta callbacks.
	 */
	public const GENERATORS = [
		[ __CLASS__, 'generate_robots' ],
	];

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_robots() {

		$meta = Meta\Robots::get_meta();

		if ( \has_filter( 'the_seo_framework_robots_meta' ) ) {
			/**
			 * @since 2.6.0
			 * @since 5.0.0 1. Deprecated.
			 *              2. No longer used internally.
			 * @deprecated
			 * @param array $meta The robots meta.
			 * @param int   $id   The current post or term ID.
			 */
			$meta = implode(
				',',
				(array) \apply_filters_deprecated(
					'the_seo_framework_robots_meta',
					[
						explode( ',', $meta ),
						\The_SEO_Framework\Helper\Query::get_the_real_id(),
					],
					'5.0.0 of The SEO Framework',
					'the_seo_framework_robots_meta_array',
				)
			);
		}

		if ( $meta )
			yield 'robots' => [
				'attributes' => [
					'name'    => 'robots',
					'content' => $meta,
				],
			];
	}
}
