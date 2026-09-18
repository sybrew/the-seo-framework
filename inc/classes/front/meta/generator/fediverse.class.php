<?php
/**
 * @package The_SEO_Framework\Classes\Front\Front\Meta\Generator
 * @subpackage The_SEO_Framework\Meta\Fediverse
 */

namespace The_SEO_Framework\Front\Meta\Generator;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use The_SEO_Framework\Meta;

/**
 * The SEO Framework plugin
 * Copyright (C) 2026 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds Fediverse generators for meta tag output.
 *
 * @since 5.1.5
 * @access private
 */
final class Fediverse {

	/**
	 * @since 5.1.5
	 * @var callable[] GENERATORS A list of auto-loaded meta callbacks.
	 */
	public const GENERATORS = [
		[ __CLASS__, 'generate_fediverse_creator' ],
		[ __CLASS__, 'generate_rel_me' ],
	];

	/**
	 * @since 5.1.5
	 * @generator
	 */
	public static function generate_fediverse_creator() {

		$creator = Meta\Fediverse::get_creator();

		if ( $creator )
			yield 'fediverse:creator' => [
				'attributes' => [
					'name'    => 'fediverse:creator',
					'content' => $creator,
				],
			];
	}

	/**
	 * @since 5.1.5
	 * @generator
	 */
	public static function generate_rel_me() {
		foreach ( Meta\Fediverse::get_profile_urls() as $i => $url )
			yield "rel:me:$i" => [
				'tag'        => 'link',
				'attributes' => [
					'rel'  => 'me',
					'href' => $url,
				],
			];
	}
}
