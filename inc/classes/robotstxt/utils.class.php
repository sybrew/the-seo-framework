<?php
/**
 * @package The_SEO_Framework\Classes\RobotsTXT\Utils
 * @subpackage The_SEO_Framework\RobotsTXT
 */

namespace The_SEO_Framework\RobotsTXT;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\umemo;

use \The_SEO_Framework\{
	Data,
	Helper\Query,
	Meta,
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
 * Holds various utility methods for the robots txt.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->robotstxt()->utils() instead.
 */
class Utils {

	/**
	 * Returns a list of filterable user-agents that can be blocked.
	 *
	 * @since 5.1.0
	 *
	 * @param string $type The type of user-agents to get. Accepts 'ai' and 'seo'.
	 * @return array {
	 *     A list of user-agents with extra info.
	 *
	 *     @type array $user_agent {
	 *         The user-agent's information.
	 *
	 *         @type string $by   The entity behind the user-agent.
	 *         @type string $link The link to the user-agent's documentation.
	 *     }
	 * }
	 */
	public static function get_blocked_user_agents( $type ) {

		switch ( $type ) {
			case 'ai':
				// Excerpt from https://github.com/ai-robots-txt/ai.robots.txt
				$agents = [
					'Amazonbot'          => [
						'by'   => 'Amazon',
						'link' => 'https://developer.amazon.com/amazonbot',
					],
					'Applebot-Extended'  => [
						'by'   => 'Apple',
						'link' => 'https://support.apple.com/en-us/119829',
					],
					'CCBot'              => [
						'by'   => 'Common Crawl',
						'link' => 'https://commoncrawl.org/ccbot',
					],
					'ClaudeBot'          => [
						'by'   => 'Anthropic',
						'link' => 'https://support.anthropic.com/en/articles/8896518-does-anthropic-crawl-data-from-the-web-and-how-can-site-owners-block-the-crawler',
					],
					'GPTBot'             => [
						'by'   => 'OpenAI',
						'link' => 'https://platform.openai.com/docs/bots',
					],
					'Google-Extended'    => [
						'by'   => 'Google',
						'link' => 'https://developers.google.com/search/docs/crawling-indexing/overview-google-crawlers',
					],
					'GoogleOther'        => [
						'by'   => 'Google',
						'link' => 'https://developers.google.com/search/docs/crawling-indexing/overview-google-crawlers',
					],
					'Meta-ExternalAgent' => [ // Why does Meta say lowercase meta-externalagent?
						'by'   => 'Meta',
						'link' => 'https://developers.facebook.com/docs/sharing/webmasters/web-crawlers/',
					],
					'FacebookBot'        => [ // Should not impede social sharing, they use FacebookExternalHit otherwise.
						'by'   => 'Meta',
						'link' => 'https://developers.facebook.com/docs/sharing/bot',
					],
				];
				break;
			case 'seo':
				$agents = [
					'AhrefsBot'        => [
						'by'   => 'Ahrefs',
						'link' => 'https://ahrefs.com/robot',
					],
					'AhrefsSiteAudit ' => [
						'by'   => 'Ahrefs',
						'link' => 'https://ahrefs.com/robot/site-audit',
					],
					'barkrowler'       => [
						'by'   => 'Babbar',
						'link' => 'https://www.babbar.tech/crawler',
					],
					'DataForSeoBot'    => [
						'by'   => 'DataForSEO',
						'link' => 'https://dataforseo.com/dataforseo-bot',
					],
					'dotbot'           => [
						'by'   => 'Moz',
						'link' => 'https://moz.com/help/moz-procedures/crawlers/dotbot',
					],
					'rogerbot'         => [
						'by'   => 'Moz',
						'link' => 'https://moz.com/help/moz-procedures/crawlers/rogerbot',
					],
					'SemrushBot'       => [
						'by'   => 'SEMrush',
						'link' => 'https://www.semrush.com/bot/',
					],
					'SiteAuditBot'     => [
						'by'   => 'SEMrush',
						'link' => 'https://www.semrush.com/bot/',
					],
					'SemrushBot-BA'    => [
						'by'   => 'SEMrush',
						'link' => 'https://www.semrush.com/bot/',
					],
				];
		}

		/**
		 * @since 5.1.0
		 * @param array $agents The user-agent list for $type.
		 * @param arrary $type  The agent type requested by the method caller.
		 */
		return (array) \apply_filters(
			'the_seo_framework_robots_blocked_user_agents',
			$agents ?? [],
			$type,
		);
	}

	/**
	 * Checks if a Robots.txt file exists in the root directory of the WordPress installation.
	 * Memoizes the return value.
	 *
	 * @since 5.0.0
	 *
	 * @return bool Whether the robots.txt file exists.
	 */
	public static function has_root_robots_txt() {
		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = umemo( __METHOD__ ) ) return $memo;

		// Ensure get_home_path() is declared.
		if ( ! \function_exists( 'get_home_path' ) )
			require_once \ABSPATH . 'wp-admin/includes/file.php';

		$path = \get_home_path() . 'robots.txt';

		// phpcs:ignore, TSF.Performance.Functions.PHP -- we use path, not URL.
		return umemo( __METHOD__, file_exists( $path ) );
	}

	/**
	 * Returns the robots.txt location URL.
	 * Only allows root domains.
	 *
	 * @since 2.9.2
	 * @since 4.0.2 Now uses the preferred URL scheme.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 * @since 5.1.0 Now memoizes the return value.
	 *
	 * @return string URL location of robots.txt. Unescaped.
	 */
	public static function get_robots_txt_url() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = umemo( __METHOD__ ) ) return $memo;

		if ( $GLOBALS['wp_rewrite']->using_permalinks() && ! Data\Blog::is_subdirectory_installation() ) {
			$home = \trailingslashit( Meta\URI\Utils::set_preferred_url_scheme( Meta\URI\Utils::get_site_host() ) );
			$path = "{$home}robots.txt";
		} elseif ( static::has_root_robots_txt() ) {
			// TODO: This URL is wrong on subdirectory installations? Use Meta\URI\Utils::get_site_host() instead?
			$home = \trailingslashit( Meta\URI\Utils::set_preferred_url_scheme( \get_option( 'home' ) ) );
			$path = "{$home}robots.txt";
		} else {
			$path = '';
		}

		return umemo( __METHOD__, $path );
	}
}
