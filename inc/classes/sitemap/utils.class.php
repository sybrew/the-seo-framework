<?php
/**
 * @package The_SEO_Framework\Classes\Sitemap\Utils
 * @subpackage The_SEO_Framework\Sitemap
 */

namespace The_SEO_Framework\Sitemap;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use const \The_SEO_Framework\ROBOTS_IGNORE_PROTECTION;

use function \The_SEO_Framework\memo;

use \The_SEO_Framework\{
	Data,
	Data\Filter\Sanitize,
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
 * Holds various utility functionality for sitemaps.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->sitemap()->utils() instead.
 */
class Utils {

	/**
	 * Returns the sitemap post query limit.
	 *
	 * @since 5.0.0
	 *
	 * @param string $type Whether the query is for hierarchical post types or not.
	 * @return int The post limit
	 */
	public static function get_sitemap_post_limit( $type = 'nonhierarchical' ) {
		/**
		 * @since 2.2.9
		 * @since 2.8.0 Increased to 1200 from 700.
		 * @since 3.1.0 Now returns an option value; it falls back to the default value if not set.
		 * @since 4.0.0 1. The default is now 3000, from 1200.
		 *              2. Now passes a second parameter.
		 * @param int $total_post_limit
		 * @param bool $hierarchical Whether the query is for hierarchical post types or not.
		 */
		return (int) \apply_filters(
			'the_seo_framework_sitemap_post_limit',
			Data\Plugin::get_option( 'sitemap_query_limit' ),
			'hierarchical' === $type,
		);
	}

	/**
	 * Determines if post is possibly included in the sitemap.
	 *
	 * @since 5.0.0
	 *
	 * @param int $post_id The Post ID to check.
	 * @return bool True if included, false otherwise.
	 */
	public static function is_post_included_in_sitemap( $post_id ) {

		static $excluded;

		if ( ! isset( $excluded ) ) {
			/**
			 * @since 2.5.2
			 * @since 2.8.0 No longer accepts '0' as entry.
			 * @since 3.1.0 '0' is accepted again.
			 * @param int[] $excluded Sequential list of excluded IDs: [ int ...post_id ]
			 */
			$excluded = (array) \apply_filters( 'the_seo_framework_sitemap_exclude_ids', [] );

			// isset() is faster than in_array(). And since we memoize, it's faster to flip.
			$excluded = $excluded ? array_flip( $excluded ) : [];
		}

		$included = ! isset( $excluded[ $post_id ] );

		while ( $included ) {
			$generator_args = [ 'id' => $post_id ];

			// ROBOTS_IGNORE_PROTECTION as we don't need to test 'private' ('post_status'=>'publish'), nor 'password' ('has_password'=>false)
			$included = 'noindex'
				!== (
					Meta\Robots::get_generated_meta(
						$generator_args,
						[ 'noindex' ],
						ROBOTS_IGNORE_PROTECTION,
					)['noindex'] ?? false // We cast type false for Zend tests strict type before identical-string-comparing.
				);

			if ( ! $included ) break;

			// This is less likely than a "noindex," even though it's faster to process, we put it later.
			$included = ! Meta\URI::get_redirect_url( $generator_args );
			break;
		}

		return $included;
	}

	/**
	 * Determines if term is possibly included in the sitemap.
	 *
	 * @since 5.0.0
	 *
	 * @param int    $term_id  The Term ID to check.
	 * @param string $taxonomy The taxonomy.
	 * @return bool True if included, false otherwise.
	 */
	public static function is_term_included_in_sitemap( $term_id, $taxonomy ) {

		static $excluded;

		if ( ! isset( $excluded ) ) {
			/**
			 * @since 4.0.0
			 * @param int[] $excluded Sequential list of excluded IDs: [ int ...term_id ]
			 */
			$excluded = (array) \apply_filters( 'the_seo_framework_sitemap_exclude_term_ids', [] );

			// isset() is faster than in_array(). And since we memoize, it's faster to flip.
			$excluded = $excluded ? array_flip( $excluded ) : [];
		}

		$included = ! isset( $excluded[ $term_id ] );

		// Yes, 90% of this code code isn't DRY. However, terms !== posts. terms == posts, though :).
		// Really: <https://core.trac.wordpress.org/ticket/50568>
		while ( $included ) {
			$generator_args = [
				'id'  => $term_id,
				'tax' => $taxonomy,
			];

			// ROBOTS_IGNORE_PROTECTION is not tested for terms. However, we may use that later.
			$included = 'noindex'
				!== (
					Meta\Robots::get_generated_meta(
						$generator_args,
						[ 'noindex' ],
						ROBOTS_IGNORE_PROTECTION,
					)['noindex'] ?? false // We cast type false for Zend tests strict type before identical-string-comparing.
				);

			if ( ! $included ) break;

			// This is less likely than a "noindex," even though it's faster to process, we put it later.
			$included = ! Meta\URI::get_redirect_url( $generator_args );
			break;
		}

		return $included;
	}

	/**
	 * Returns sitemap color scheme.
	 *
	 * @since 2.8.0
	 * @since 4.0.5 Changed default colors to be more in line with WordPress.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param bool $get_defaults Whether to get the default colors.
	 * @return array The sitemap colors.
	 */
	public static function get_sitemap_colors( $get_defaults = false ) {

		$defaults = [
			'main'   => '#222222',
			'accent' => '#00a0d2',
		];

		if ( $get_defaults )
			return $defaults;

		$main   = Sanitize::rgb_hex( Data\Plugin::get_option( 'sitemap_color_main' ) );
		$accent = Sanitize::rgb_hex( Data\Plugin::get_option( 'sitemap_color_accent' ) );

		return array_merge(
			$defaults,
			array_filter( [
				'main'   => $main ? "#$main" : '',
				'accent' => $accent ? "#$accent" : '',
			] ),
		);
	}

	/**
	 * Tells whether WP 5.5 Core Sitemaps are used.
	 * Memoizes the return value.
	 *
	 * @since 4.1.2
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @return bool
	 */
	public static function use_core_sitemaps() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo() ) return $memo;

		if ( Data\Plugin::get_option( 'sitemaps_output' ) )
			return memo( false );

		$wp_sitemaps_server = \wp_sitemaps_get_server();

		return memo(
			method_exists( $wp_sitemaps_server, 'sitemaps_enabled' ) && $wp_sitemaps_server->sitemaps_enabled()
		);
	}

	/**
	 * Determines whether we can output sitemap or not based on options and blog status.
	 *
	 * @since 5.0.0
	 *
	 * @return bool
	 */
	public static function may_output_optimized_sitemap() {
		return Data\Plugin::get_option( 'sitemaps_output' )
			&& ! Data\Blog::is_spam_or_deleted();
	}

	/**
	 * Detects presence of sitemap.xml in root folder.
	 * Memoizes the return value.
	 *
	 * @since 5.0.0
	 *
	 * @return bool Whether the sitemap.xml file exists.
	 */
	public static function has_root_sitemap_xml() {
		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo() ) return $memo;

		// Ensure get_home_path() is declared.
		if ( ! \function_exists( 'get_home_path' ) )
			require_once \ABSPATH . 'wp-admin/includes/file.php';

		$path = \get_home_path() . 'sitemap.xml';

		// phpcs:ignore, TSF.Performance.Functions.PHP -- we use path, not URL.
		return memo( file_exists( $path ) );
	}
}
