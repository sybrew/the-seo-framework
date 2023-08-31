<?php
/**
 * @package The_SEO_Framework\Classes\Front\Meta\Generator
 * @subpackage The_SEO_Framework\Meta
 */

namespace The_SEO_Framework\Meta\Generator;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

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
 * Holds robots generators for meta tag output.
 *
 * @since 4.3.0
 * @access protected
 * @internal
 * @final Can't be extended.
 */
final class Robots {

	/**
	 * @since 4.3.0
	 * @var callable[] GENERATORS A list of autoloaded meta callbacks.
	 */
	public const GENERATORS = [
		[ __CLASS__, 'generate_robots' ],
	];

	/**
	 * @since 4.3.0
	 * @access protected
	 * @generator
	 */
	public static function generate_robots() {

		$tsf = \tsf();

		// Don't do anything if the blog isn't set to public.
		if ( ! $tsf->is_blog_public() ) return;

		$meta = $tsf->get_robots_meta();

		if ( $meta )
			yield [
				'attributes' => [
					'name'    => 'robots',
					'content' => implode( ',', $meta ),
				],
			];
	}
}
