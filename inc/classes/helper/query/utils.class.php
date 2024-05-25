<?php
/**
 * @package The_SEO_Framework\Classes\Helper\Query\Utils
 * @subpackage The_SEO_Framework\Query
 */

namespace The_SEO_Framework\Helper\Query;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\memo;

use \The_SEO_Framework\Data;
use \The_SEO_Framework\Helper\{
	Post_Type,
	Query, // Yes, it is legal to share class and namespaces.
	Taxonomy,
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
 * Holds a collection of helper methods for queries.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->query()->utils() instead.
 */
class Utils {

	/**
	 * Determines whether pretty permalinks are enabled.
	 *
	 * @since 5.0.0
	 * @todo consider wp_force_plain_post_permalink()
	 *
	 * @return bool
	 */
	public static function using_pretty_permalinks() {
		return memo() ?? memo( '' !== \get_option( 'permalink_structure' ) );
	}

	/**
	 * Determines whether the main query supports custom SEO.
	 *
	 * @since 4.0.0
	 * @since 4.0.2 Now tests for an existing post/term ID when on singular/term pages.
	 * @since 4.0.3 Can now assert empty categories again by checking for taxonomy support.
	 * @since 4.2.4 Added detection for AJAX, Cron, JSON, and REST queries
	 *              (they're not supported as SEO-able queries).
	 * @since 5.0.0 1. Removed detection for JSON(P) and XML type requests,
	 *                 because these cannot be assumed as legitimate.
	 *              2. Added `\is_customize_preview()` as unsupported.
	 *              3. Moved from `\The_SEO_Framework\Load`.
	 *              4. Also removed detection of `wp_doing_ajax()` and `wp_doing_cron()`,
	 *                 this is now being handled by `_init_tsf()`.
	 * @since 5.0.3 Now considers the query supported when the homepage is assigned a broken ID.
	 *
	 * @return bool
	 */
	public static function query_supports_seo() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo() ) return $memo;

		switch ( true ) {
			case \is_feed():
			case \is_customize_preview():
			case \defined( 'REST_REQUEST' ) && \REST_REQUEST: // TODO WP 6.5+ wp_is_serving_rest_request()
				$supported = false;
				break;
			case Query::is_singular():
				// This is the most likely scenario, but may collide with is_feed() et al.
				$supported = Post_Type::is_supported() && ( Query::get_the_real_id() || Query::is_real_front_page() );
				break;
			case \is_post_type_archive():
				$supported = Post_Type::is_pta_supported();
				break;
			case Query::is_category() || Query::is_tag() || Query::is_tax():
				// When a term has no posts attached, it'll not return a post type, and it returns a 404 late in the loop.
				// This is because get_post_type() tries to assert the first post in the loop here.
				// Thus, we test for is_taxonomy_supported() instead.
				$supported = Taxonomy::is_supported() && Query::get_the_real_id();
				break;
			default:
				// Everything else: homepage, 404, search, edge-cases.
				$supported = true;
		}

		/**
		 * Override false negatives on exploit.
		 *
		 * This protects against (accidental) negative-SEO bombarding.
		 * Support broken queries, so we can noindex them.
		 */
		if ( ! $supported && static::is_query_exploited() )
			$supported = true;

		/**
		 * @since 4.0.0
		 * @param bool $supported Whether the query supports SEO.
		 */
		return memo( (bool) \apply_filters( 'the_seo_framework_query_supports_seo', $supported ) );
	}

	/**
	 * Determines when paged/page is exploited.
	 * Memoizes the return value.
	 *
	 * Google is acting "smart" nowadays, and follows everything that remotely resembles a link. Therefore, unintentional
	 * queries can occur in WordPress. WordPress deals with this well, alas, the query parser (WP_Query::parse_query)
	 * doesn't rectify the mixed signals it receives. Instead, it only sanitizes it, resulting in a combobulated mess.
	 * Ultimately, this leads to non-existing blog archives, among other failures.
	 *
	 * Example 1: `/?p=nonnumeric` will cause an issue. We will see a non-existing blog page. `is_home` is true, but
	 * `page_id` leads to 0 while the database expects the blog page to be another page. So, `is_posts_page` is
	 * incorrectly false. This is mitigated via the canonical URL, but that MUST output, thus overriding otherwise chosen
	 * and expected behavior.
	 *
	 * Example 2: `/page/2/?p=nonnumeric` will cause a bigger issue. What happens is that `is_home` will again be true,
	 * but so will `is_paged`. `paged` will be set to `2` (as per example URL). The page ID will again be set to `0`,
	 * which is completely false. The canonical URL will be malformed. Even more so, Google can ignore the canonical URL,
	 * so we MUST output noindex.
	 *
	 * Example 3: `/page/2/?X=nonnumeric` will also cause the same issues as in example 2. Where X can be:
	 * `page_id`, `attachment_id`, `year`, `monthnum`, `day`, `w`, `m`, and of course `p`.
	 *
	 * Example 4: `/?hour=nonnumeric`, the same issue as Example 1. The canonical URL is malformed, noindex is set, and
	 * link relationships will be active. A complete mess. `minute` and `second` are also affected the same way.
	 *
	 * Example 5: `/page/2/?p=0`, this is the trickiest. It's indicative of a paginated blog, but also the homepage. When
	 * the homepage is not a blog, then this query is malformed. Otherwise, however, it's a good query.
	 *
	 * @since 4.0.5
	 * @since 4.2.7 1. Added detection `not_home_as_page`, specifically for query variable `search`.
	 *              2. Improved detection for `cat` and `author`, where the value may only be numeric above 0.
	 * @since 4.2.8 Now blocks any publicly registered variable requested to the home-as-page.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 * @since 5.0.5 Now detects `should_be_404`, specifically for query variable `sitemap` and `sitemap-subtype`.
	 * @global \WP_Query $wp_query
	 *
	 * @return bool Whether the query is (accidentally) exploited.
	 *              Defaults to false when `advanced_query_protection` option is disabled.
	 *              False when there's a query-ID found.
	 *              False when no custom query is set (for the homepage).
	 *              Otherwise, it performs query tests.
	 */
	public static function is_query_exploited() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo() ) return $memo;

		if ( ! Data\Plugin::get_option( 'advanced_query_protection' ) )
			return memo( false );

		// When the page ID is not 0, a real page will always be returned.
		if ( Query::get_the_real_id() )
			return memo( false );

		global $wp_query;

		// When no special query data is registered, ignore this. Don't set cache.
		if ( ! isset( $wp_query->query ) )
			return false;

		/**
		 * @since 4.0.5
		 * @since 4.2.7 Added index `not_home_as_page` with value `search`.
		 * @since 5.0.5 Added index `not_front_page` with values `sitemap` and `sitemap-subtype`.
		 * @param array $exploitables The exploitable endpoints by type.
		 */
		$exploitables = \apply_filters(
			'the_seo_framework_exploitable_query_endpoints',
			[
				'numeric'          => [
					'page_id',
					'attachment_id',
					'year',
					'monthnum',
					'day',
					'w',
					'm',
					'p',
					'paged', // 'page' is mitigated by WordPress.
					'hour',
					'minute',
					'second',
					'subpost_id',
				],
				'numeric_array'    => [
					'cat',
					'author',
				],
				'requires_s'       => [
					'sentence',
				],
				// When the blog (home) is a page then these requests to any registered query variable will cause issues,
				// but only when the page ID returns 0. (We already tested for `if ( Query::get_the_real_id() )` above).
				// This global's property is only populated with requested parameters that match registered `public_query_vars`.
				// We only need one to pass this test. We could use array_key_first()... but that may be nulled (out of our control).
				'not_home_as_page' => array_keys( $GLOBALS['wp']->query_vars ?? [] ),
				// Another WordPress bug type mitigation: https://core.trac.wordpress.org/ticket/51117.
				'should_be_404'    => [
					'sitemap',
					'sitemap-subtype',
				],
			],
		);

		$query = $wp_query->query;

		foreach ( $exploitables as $type => $qvs ) {
			foreach ( $qvs as $qv ) {
				// Only test isset, because falsey or empty-array is what we need to test against.
				if ( ! isset( $query[ $qv ] ) ) continue;

				switch ( $type ) {
					case 'numeric':
						if ( '0' === $query[ $qv ] || ! is_numeric( $query[ $qv ] ) )
							return memo( true );
						break;

					case 'numeric_array':
						// We can't protect non-pretty permalinks.
						if ( ! static::using_pretty_permalinks() ) break;

						// If WordPress didn't canonical_redirect() the user yet, it's exploited.
						// WordPress mitigates this via a 404 query when a numeric value is found without a leading 0.
						if ( ! preg_match( '/^[1-9]\d*$/', $query[ $qv ] ) )
							return memo( true );
						break;

					case 'requires_s':
						if ( ! isset( $query['s'] ) )
							return memo( true );
						break;

					case 'not_home_as_page':
						// isset($query[$qv]) is already executed. Just test if homepage ID still works.
						// !Query::get_the_real_id() is already executed. Just test if home is a page.
						if ( Query::is_blog_as_page() ) {
							return memo( true );
						} else {
							// No need to test other 'not_home_as_page' types. Go to next type (if any).
							continue 3; // 1: switch, 2: loop $qvs, 3: loop $exploitables.
						}
						break; // unreachable?

					case 'should_be_404':
						// isset($query[$qv]) is already executed. Just test if we're also on a 404 page.
						if ( \is_404() ) {
							return memo( true );
						} else {
							// No need to test other 'not_front_page' types. Go to next type (if any).
							continue 3; // 1: switch, 2: loop $qvs, 3: loop $exploitables.
						}
				}
			}
		}

		return memo( false );
	}

	/**
	 * Determines whether a page or blog is on front.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Removed caching.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @return bool
	 */
	public static function has_page_on_front() {
		return 'page' === \get_option( 'show_on_front' );
	}

	/**
	 * Determines whether a page on front is actually assigned.
	 *
	 * @since 5.0.5
	 *
	 * @return bool
	 */
	public static function has_assigned_page_on_front() {
		return static::has_page_on_front() && \get_option( 'page_on_front' );
	}

	/**
	 * Determines whether the blog page exists.
	 * This is not always a "blog as page" -- for that, use `tsf()->query()->is_blog_as_page()`.
	 *
	 * @since 5.0.4
	 *
	 * @return bool
	 */
	public static function has_blog_page() {
		return ! static::has_page_on_front() || \get_option( 'page_for_posts' );
	}
}
