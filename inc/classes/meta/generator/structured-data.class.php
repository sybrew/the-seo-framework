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
 * Holds structured data generators for meta tag output.
 *
 * @since 4.3.0
 * @access protected
 * @internal
 * @final Can't be extended.
 */
final class Structured_Data {

	/**
	 * @since 4.3.0
	 * @var callable[] GENERATORS A list of autoloaded meta callbacks.
	 */
	public const GENERATORS = [
		[ __CLASS__, 'generate_structured_data' ],
	];

	/**
	 * @since 4.3.0
	 * @access protected
	 * @generator
	 */
	public static function generate_structured_data() {
		return;
		yield; // Enable generator to prevent crashing.

		// TODO defunct. We should probably boot this without deprecation.
		/**
		 * @since 2.6.0
		 * @param string $json The JSON output. Must be escaped.
		 * @param int    $id   The current page or term ID.
		 */
		$json = (string) \apply_filters_ref_array(
			'the_seo_framework_ldjson_scripts',
			[
				\tsf()->render_ld_json_scripts(),
				Query::get_the_real_id(),
			]
		);
	}
}
