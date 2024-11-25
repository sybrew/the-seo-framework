<?php
/**
 * @package The_SEO_Framework\Classes\RobotsTXT\Main
 * @subpackage The_SEO_Framework\RobotsTXT
 */

namespace The_SEO_Framework\RobotsTXT;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\{
	Data,
	Helper\Compatibility,
	Helper\Query,
	Meta,
	RobotsTXT, // Yes, it is legal to import the same namespace.
	Sitemap,
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
 * Holds various methods for the robots txt output.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->robotstxt() instead.
 */
class Main {

	/**
	 * Edits the robots.txt output.
	 * Requires the site not to have a robots.txt file in the root directory.
	 *
	 * This methods completely hijacks default output. This is intentional (read next paragraph).
	 * Use a higher filter priority to override TSF's output.
	 *
	 * The robots.txt file should not be used to block endpoints that are supposed to be hidden.
	 * This is because the robots.txt file is public; adding endpoints there would expose them.
	 * Blocking pages via robots.txt is not effective, either; if a direct link to a page is found,
	 * it can still be crawled and indexed. Use the robots meta tags (and headers) instead.
	 *
	 * @hook robots_txt 10
	 * @since 5.0.0
	 * @since 5.1.0 1. Refactored to output the directives via a priority system.
	 *              2. Now supports blocking AI language model trainers and SEO analysis tools.
	 * @link <https://developers.google.com/search/docs/crawling-indexing/robots/robots_txt>
	 *
	 * @return string Robots.txt output.
	 */
	public static function get_robots_txt() {

		$output = '';

		// Simple test for invalid directory depth. Even //robots.txt is an invalid location.
		// To be fair, though, up to 5 redirects from /robots.txt are allowed. However, nobody has notified us of this usage.
		// TODO Should we add a test for `/?robots=1.*`? Eh...
		if ( strrpos( rawurldecode( stripslashes( $_SERVER['REQUEST_URI'] ) ), '/' ) > 0 ) {
			$correct_location = \esc_url(
				\trailingslashit( Meta\URI\Utils::set_preferred_url_scheme(
					Meta\URI\Utils::get_site_host()
				) ) . 'robots.txt',
			);

			$output .= "# This is an invalid robots.txt location.\n# Please visit: $correct_location\n\n";
		}

		$site_path = parse_url( \site_url(), \PHP_URL_PATH ) ?: '';

		/**
		 * @since 2.5.0
		 * @since 5.1.0 Deprecated.
		 * @deprecated
		 * @param bool $disallow Whether to disallow robots queries.
		 */
		$disallow_queries = \apply_filters_deprecated(
			'the_seo_framework_robots_disallow_queries',
			[ false ],
			'5.1.0 of The SEO Framework',
			'the_seo_framework_robots_txt_sections'
		) ? '/*?*'
		  : '';

		$sitemaps = [];

		// Add extra whitespace and sitemap full URL
		if ( Data\Plugin::get_option( 'sitemaps_robots' ) ) {
			if ( Data\Plugin::get_option( 'sitemaps_output' ) ) {
				foreach ( Sitemap\Registry::get_sitemap_endpoint_list() as $id => $data )
					if ( ! empty( $data['robots'] ) )
						$sitemaps[] = \esc_url( Sitemap\Registry::get_expected_sitemap_endpoint_url( $id ) );

			} elseif ( ! Compatibility::get_active_conflicting_plugin_types()['sitemaps'] && Sitemap\Utils::use_core_sitemaps() ) {
				$wp_sitemaps_server = \wp_sitemaps_get_server();

				if ( method_exists( $wp_sitemaps_server, 'add_robots' ) ) {
					// Already escaped.
					$sitemaps[] = trim( "\n", \wp_sitemaps_get_server()->add_robots( '', Data\Blog::is_public() ) );
				}
			}
		}

		/**
		 * @since 5.1.0
		 * @param array  $robots_sections {
		 *     The robots directives, associative by key.
		 *     All input is expected to be escaped.
		 *
		 *     @type array {$key} {
		 *         The default or custom directives.
		 *
		 *         @type string   $raw        The raw output to prepend.
		 *         @type string[] $user-agent The user agent to apply the directives for.
		 *         @type string[] $disallow   The disallow directives.
		 *         @type string[] $allow      The allow directives.
		 *         @type int      $priority   The priority of the output, a lower priority means earlier output.
		 *                                    Defaults to 10.
		 *     }
		 * }
		 * @param string $site_path The determined site path. Use this path to prefix URLs.
		 */
		$robots_sections = (array) \apply_filters(
			'the_seo_framework_robots_txt_sections',
			[
				'deprecated_before' => [
					/**
					 * @since 2.5.0
					 * @since 5.1.0 Deprecated.
					 * @deprecated
					 * @param string $pre The output before this plugin's output.
					 *                    Don't forget to add line breaks ( "\n" )!
					 */
					'raw'      => (string) \apply_filters_deprecated(
						'the_seo_framework_robots_txt_pre',
						[ '' ],
						'5.1.0 of The SEO Framework',
						'the_seo_framework_robots_txt_sections',
					),
					'priority' => 0,
				],
				'default'           => [
					'user-agent' => [ '*' ],
					'disallow'   => [ "$site_path/wp-admin/", $disallow_queries ],
					'allow'      => [ "$site_path/wp-admin/admin-ajax.php" ],
				],
				'block_ai'          => Data\Plugin::get_option( 'robotstxt_block_ai' ) ? [
					'user-agent' => array_keys( RobotsTXT\Utils::get_blocked_user_agents( 'ai' ) ),
					'disallow'   => [ '/' ],
				] : [],
				'block_seo'         => Data\Plugin::get_option( 'robotstxt_block_seo' ) ? [
					'user-agent' => array_keys( RobotsTXT\Utils::get_blocked_user_agents( 'seo' ) ),
					'disallow'   => [ '/' ],
				] : [],
				'deprecated_after'  => [
					/**
					 * @since 2.5.0
					 * @since 5.1.0 Deprecated.
					 * @deprecated
					 * @param string $pro The output after this plugin's output.
					 *                    Don't forget to add line breaks ( "\n" )!
					 */
					'raw'      => (string) \apply_filters_deprecated(
						'the_seo_framework_robots_txt_pro',
						[ '' ],
						'5.1.0 of The SEO Framework',
						'the_seo_framework_robots_txt_sections',
					),
					'priority' => 500,
				],
				'sitemaps'          => [
					'sitemaps' => $sitemaps,
					'priority' => 1000,
				],
			],
			$site_path,
		);

		// We need to use uasort to maintain index association, but we don't read the indexes.
		usort( $robots_sections, fn( $a, $b ) => ( $a['priority'] ?? 10 ) <=> ( $b['priority'] ?? 10 ) );

		$pieces     = [];
		$directives = [
			'user-agent' => 'User-agent',
			'disallow'   => 'Disallow',
			'allow'      => 'Allow',
			'sitemaps'   => 'Sitemap',
		];
		foreach ( $robots_sections as $section ) {
			$piece = '';

			if ( isset( $section['raw'] ) )
				$piece .= $section['raw'];

			if ( ! empty( $section['user-agent'] ) || ! empty( $section['sitemaps'] ) )
				foreach ( $directives as $key => $directive ) // implies order and allowed keys.
					foreach ( $section[ $key ] ?? [] as $value )
						$piece .= \strlen( $value ) ? "$directive: $value\n" : '';

			if ( \strlen( $piece ) )
				$pieces[] = $piece;
		}

		$output .= implode( "\n", $pieces );

		/**
		 * The robots.txt output.
		 *
		 * @since 4.0.5
		 * @param string $output The robots.txt output.
		 */
		return (string) \apply_filters( 'the_seo_framework_robots_txt', $output );
	}
}
