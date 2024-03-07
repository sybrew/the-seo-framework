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
	 * This methods completely hijacks default output, intentionally.
	 *
	 * The robots.txt file should be left as default, so to improve SEO.
	 * The Robots Exclusion Protocol encourages you not to use robots.txt for
	 * non-administrative endpoints. Use the robots meta tags (and headers) instead.
	 *
	 * @since 5.0.0
	 * @uses robots_txt filter located at WP core
	 * @TODO rework into a workable standard...
	 *
	 * @return string Robots.txt output.
	 */
	public static function get_robots_txt() {

		/**
		 * @since 2.5.0
		 * @param string $pre The output before this plugin's output.
		 *                    Don't forget to add line breaks ( "\n" )!
		 */
		$output = (string) \apply_filters( 'the_seo_framework_robots_txt_pre', '' );

		$site_path = parse_url( \site_url(), \PHP_URL_PATH ) ?: '';

		// Output defaults
		$output .= "User-agent: *\n";
		$output .= "Disallow: $site_path/wp-admin/\n";
		$output .= "Allow: $site_path/wp-admin/admin-ajax.php\n";

		/**
		 * @since 2.5.0
		 * @param bool $disallow Whether to disallow robots queries.
		 */
		if ( \apply_filters( 'the_seo_framework_robots_disallow_queries', false ) )
			$output .= "Disallow: /*?*\n";

		/**
		 * @since 2.5.0
		 * @param string $pro The output after this plugin's output.
		 *                    Don't forget to add line breaks ( "\n" )!
		 */
		$output .= (string) \apply_filters( 'the_seo_framework_robots_txt_pro', '' );

		// Add extra whitespace and sitemap full URL
		if ( Data\Plugin::get_option( 'sitemaps_robots' ) ) {
			if ( Data\Plugin::get_option( 'sitemaps_output' ) ) {
				foreach ( Sitemap\Registry::get_sitemap_endpoint_list() as $id => $data )
					if ( ! empty( $data['robots'] ) )
						$output .= sprintf( "\nSitemap: %s", \esc_url( Sitemap\Registry::get_expected_sitemap_endpoint_url( $id ) ) );

				$output .= "\n";
			} elseif ( ! Compatibility::get_active_conflicting_plugin_types()['sitemaps'] && Sitemap\Utils::use_core_sitemaps() ) {
				$wp_sitemaps_server = \wp_sitemaps_get_server();

				if ( method_exists( $wp_sitemaps_server, 'add_robots' ) ) {
					// This method augments the output--it doesn't overwrite it.
					$output = \wp_sitemaps_get_server()->add_robots( $output, Data\Blog::is_public() );
				}
			}
		}

		$raw_uri = rawurldecode( stripslashes( $_SERVER['REQUEST_URI'] ) )
				?: '/robots.txt';

		// Simple test for invalid directory depth. Even //robots.txt is an invalid location.
		if ( strrpos( $raw_uri, '/' ) > 0 ) {
			$error  = sprintf(
				"%s\n%s\n\n",
				'# This is an invalid robots.txt location.',
				'# Please visit: '
				. \esc_url(
					\trailingslashit( Meta\URI\Utils::set_preferred_url_scheme(
						Meta\URI\Utils::get_site_host()
					) )
					. 'robots.txt',
				)
			);
			$output = "$error$output";
		}

		/**
		 * The robots.txt output.
		 *
		 * @since 4.0.5
		 * @param string $output The robots.txt output.
		 */
		return (string) \apply_filters( 'the_seo_framework_robots_txt', $output );
	}
}
