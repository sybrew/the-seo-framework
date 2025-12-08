<?php
/**
 * @package The_SEO_Framework\Classes\Sitemap\Optimized\Main
 * @subpackage The_SEO_Framework\Sitemap
 */

namespace The_SEO_Framework\Sitemap\Optimized;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use The_SEO_Framework\{
	Data,
	Sitemap,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2025 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Generates the sitemap.
 *
 * @since 4.0.0
 * @since 4.2.0 Renamed from `The_SEO_Framework\Builders\Sitemap`
 * @since 5.0.0 1. No longer holds the `$tsf` property.
 *              2. Moved from `\The_SEO_Framework\Builders\Sitemap\Main`.
 * @access public
 */
abstract class Main {

	/**
	 * @since 5.0.0
	 * @var int The total number of URLs registered in the current sitemap.
	 */
	public $url_count = 0;

	/**
	 * Prepares sitemap generation by raising the memory limit and fixing the time zone.
	 *
	 * @since 4.0.0
	 * @since 4.0.4 Now sets time zone to UTC to fix WP 5.3 bug <https://core.trac.wordpress.org/ticket/48623>
	 * @since 4.2.0 No longer sets time zone.
	 */
	final public function prepare_generation() {
		\wp_raise_memory_limit( 'sitemap' );
	}

	/**
	 * Shuts down the sitemap generator.
	 *
	 * @since 4.0.0
	 * @since 4.2.0 No longer resets time zone.
	 * @ignore
	 */
	final public function shutdown_generation() { }

	/**
	 * Generates and returns the sitemap content.
	 * We recommend you overwriting this method to include caching.
	 *
	 * @since 4.1.2
	 * @abstract
	 *
	 * @return string The sitemap content.
	 */
	public function generate_sitemap() {

		$this->prepare_generation();

		$sitemap = $this->build_sitemap();

		$this->shutdown_generation();

		return $sitemap;
	}

	/**
	 * Returns the sitemap content.
	 *
	 * @since 4.0.0
	 *
	 * @return string The sitemap content.
	 */
	abstract public function build_sitemap();

	/**
	 * Creates XML entry from array input.
	 * Input is expected to be escaped and XML-safe.
	 *
	 * @NOTE: Not final, other classes may overwrite this.
	 * The self:: call in this method is intentional since it is used recursively.
	 *
	 * @since 4.1.1
	 * @since 5.0.0 Is now static.
	 *
	 * @param iterable $data  The data to create an XML item from. Expected to be escaped and XML-safe!
	 * @param int      $level The iteration level. Default 1 (one level in from urlset).
	 *                        Affects non-mandatory tab indentation for readability.
	 * @return string The XML data.
	 */
	protected static function create_xml_entry( $data, $level = 1 ) {

		$out = '';

		foreach ( $data as $key => $value ) {
			$tabs = str_repeat( "\t", $level );

			if ( \is_array( $value ) )
				$value = "\n" . self::create_xml_entry( $value, $level + 1 ) . $tabs;

			$out .= "$tabs<$key>$value</$key>\n";
		}

		return $out;
	}
}
