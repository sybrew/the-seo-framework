<?php
/**
 * @package The_SEO_Framework\Classes\Sitemap\Optimized\Main
 * @subpackage The_SEO_Framework\Sitemap
 */

namespace The_SEO_Framework\Sitemap\Optimized;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\{
	Data,
	Sitemap,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
	 * Note: Not final, other classes may overwrite this.
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
				$value = "\n" . static::create_xml_entry( $value, $level + 1 ) . $tabs;

			$out .= "$tabs<$key>$value</$key>\n";
		}

		return $out;
	}

	/**
	 * Determines if post is possibly included in the sitemap.
	 *
	 * This is a weak check, as the filter might not be present outside of the sitemap's scope.
	 * The URL also isn't checked, nor the position.
	 *
	 * @since 3.0.4
	 * @since 3.0.6 First filter value now works as intended.
	 * @since 3.1.0 1. Resolved a PHP notice when ID is 0, resulting in returning false-esque unintentionally.
	 *              2. Now accepts 0 in the filter.
	 * @since 4.0.0 1. Now tests qubit options.
	 *              2. FALSE: Now tests for redirect settings. <- it never did! We did document this though...
	 *              3. First parameter can now be a post object.
	 *              4. If the first parameter is 0, it's now indicative of a home-as-blog page.
	 *              5. Moved to \The_SEO_Framework\Builders\Sitemap
	 * @since 4.1.4 TRUE: Now tests for redirect settings.
	 * @since 4.2.0 Now only asserts noindex robots-values, instead of all robots-values, improving performance.
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $post_id The Post ID to check.
	 * @return bool True if included, false otherwise.
	 */
	final public function is_post_included_in_sitemap( $post_id ) {

		\tsf()->_deprecated_function(
			__METHOD__,
			'5.0.0',
			'tsf()->sitemap()->utils()->is_post_included_in_sitemap()',
		);

		return Sitemap\Utils::is_post_included_in_sitemap( $post_id );
	}

	/**
	 * Determines if term is possibly included in the sitemap.
	 *
	 * @since 4.0.0
	 * @since 4.1.4 Now tests for redirect settings.
	 * @since 4.2.0 Now only asserts noindex robots-values, instead of all robots-values, improving performance.
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int    $term_id  The Term ID to check.
	 * @param string $taxonomy The taxonomy.
	 * @return bool True if included, false otherwise.
	 */
	final public function is_term_included_in_sitemap( $term_id, $taxonomy ) {

		\tsf()->_deprecated_function(
			__METHOD__,
			'5.0.0',
			'tsf()->sitemap()->utils()->is_term_included_in_sitemap()',
		);

		return Sitemap\Utils::is_term_included_in_sitemap( $term_id, $taxonomy );
	}

	/**
	 * Returns the sitemap post query limit.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 Moved to \The_SEO_Framework\Builders\Sitemap
	 * @since 5.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param bool $hierarchical Whether the query is for hierarchical post types or not.
	 * @return int The post limit
	 */
	final protected function get_sitemap_post_limit( $hierarchical = false ) {

		\tsf()->_deprecated_function(
			__METHOD__,
			'5.0.0',
			'tsf()->sitemap()->utils()->get_sitemap_post_limit()',
		);

		return Sitemap\Utils::get_sitemap_post_limit( $hierarchical );
	}
}
