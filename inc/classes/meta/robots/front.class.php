<?php
/**
 * @package The_SEO_Framework\Classes\Meta\Robots
 * @subpackage The_SEO_Framework\Meta\Robots
 */

namespace The_SEO_Framework\Meta\Robots;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use const \The_SEO_Framework\{
	ROBOTS_IGNORE_SETTINGS,
	ROBOTS_IGNORE_PROTECTION,
};

use \The_SEO_Framework\Data;
use \The_SEO_Framework\Meta\Robots; // Yes, it is legal to share class and namespaces.
use \The_SEO_Framework\Helper\{
	Query,
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
 * Engine for robots generator via front-end query.
 *
 * @since 4.2.0
 * @since 5.0.0 Moved from `\The_SEO_Framework\Builders\Robots`.
 * @access private
 */
final class Front extends Factory {

	// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- You don't love PHP7.
	// phpcs:disable, PSR2.ControlStructures.SwitchDeclaration.TerminatingComment -- You hate goto.
	// phpcs:disable, Generic.WhiteSpace.ScopeIndent.IncorrectExact -- You hate gotoo.
	/**
	 * Generates robots assertions for no[index|archive|follow].
	 *
	 * Yields true when "noindex/noarchive/nofollow", yields false when "index/archive/follow".
	 *
	 * @since 4.2.0
	 * @since 4.2.5 1. Removed needlessly duplicate homepage test.
	 *              2. Moved archive pagination check to index_protection.
	 * @generator
	 *
	 * @param string $type The robots generator type (noindex, nofollow...).
	 */
	protected static function assert_no( $type ) {

		$asserting_noindex = 'noindex' === $type;

		// We assert options here for a jump to meta_settings might be unaware.
		meta_settings: if ( ! ( static::$options & ROBOTS_IGNORE_SETTINGS ) ) {

			$qubit = null;

			if ( Query::is_editable_term() ) {
				$qubit = (int) Data\Plugin\Term::get_meta_item( $type );
			} elseif ( Query::is_singular() ) {
				$qubit = (int) Data\Plugin\Post::get_meta_item( "_genesis_$type" );
			} elseif ( \is_post_type_archive() ) {
				$qubit = (int) Data\Plugin\PTA::get_meta_item( $type );
			}

			switch ( isset( $qubit ) ) {
				case false:
					// Page doesn't support metadata.
					break;
				case $qubit < -.3333:
					// 'Force' index.
					yield 'meta_qubit_force' => false;
					// Override with index protection.
					goto index_protection;
				case $qubit > .3334:
					// Force noindex.
					yield 'meta_qubit_force' => true;
					// We won't override this. Terminate generator. "goto end".
					// No break, generator stops here anyway.
				default:
					// qubit is (closer to) 0. Assert we use _default, albeit false.
					yield 'meta_qubit_default' => false;
			}
		}

		globals:
			yield 'globals_site' => (bool) Data\Plugin::get_option( "site_$type" );

			if ( Query::is_real_front_page() ) {
				yield 'globals_homepage' => (bool) Data\Plugin::get_option( "homepage_$type" );
			} else {
				$asserting_noindex and yield from static::assert_noindex_query_pass( '404' );

				if ( Query::is_archive() ) {
					if ( Query::is_author() ) {
						yield 'globals_author' => (bool) Data\Plugin::get_option( "author_$type" );
					} elseif ( \is_date() ) {
						yield 'globals_date' => (bool) Data\Plugin::get_option( "date_$type" );
					}
				} elseif ( Query::is_search() ) {
					yield 'globals_search' => (bool) Data\Plugin::get_option( "search_$type" );
				}
			}

			// is_real_front_page() can still be singular or archive. Thus, this conditional block is split up.
			if ( Query::is_archive() ) {
				if ( Query::is_category() || Query::is_tag() || Query::is_tax() ) {
					yield 'globals_taxonomy' => Robots::is_taxonomy_robots_set( $type, Query::get_current_taxonomy() );

					// Store values from each post type bound to the taxonomy.
					foreach ( Taxonomy::get_post_types() as $post_type )
						$_is_post_type_robots_set[] = Robots::is_post_type_robots_set( $type, $post_type );

					// Only enable if _all_ post types have been marked with 'no*'. Return false if no post types are found (corner case).
					yield 'globals_post_type_all' => isset( $_is_post_type_robots_set ) && ! \in_array( false, $_is_post_type_robots_set, true );
				} elseif ( \is_post_type_archive() ) {
					yield 'globals_post_type' => Robots::is_post_type_robots_set( $type, Query::get_current_post_type() );
				}
			} elseif ( Query::is_singular() ) {
				yield 'globals_post_type' => Robots::is_post_type_robots_set( $type, Query::get_current_post_type() );
			}

		// We assert options here for a jump to index_protection might be unaware.
		index_protection: if ( $asserting_noindex && ! ( static::$options & ROBOTS_IGNORE_PROTECTION ) ) {
			if ( Query::is_real_front_page() ) {
				yield from static::assert_noindex_query_pass( 'paged_home' );
			} elseif ( Query::is_archive() || Query::is_singular_archive() ) {
				yield from static::assert_noindex_query_pass( 'paged' );
			}
			if ( Query::is_singular() ) {
				yield from static::assert_noindex_query_pass( 'protected' );

				if ( Query::is_comment_paged() )
					yield from static::assert_noindex_query_pass( 'cpage' );
			}
		}

		exploit_protection: if ( Query\Utils::is_query_exploited() ) {
			if ( \in_array( $type, [ 'noindex', 'nofollow' ], true ) )
				yield 'query_protection' => true;
		}

		end:;
	}
	// phpcs:enable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
	// phpcs:enable, PSR2.ControlStructures.SwitchDeclaration.TerminatingComment
	// phpcs:enable, Generic.WhiteSpace.ScopeIndent.IncorrectExact

	/**
	 * Generates robots assertions for noindex in passes.
	 *
	 * @since 4.2.0
	 * @generator
	 *
	 * @param string $pass The passage to assert.
	 */
	private static function assert_noindex_query_pass( $pass ) {
		switch ( $pass ) {
			case 'paged_home':
				yield 'paged_home' =>
					Data\Plugin::get_option( 'home_paged_noindex' ) && ( Query::page() > 1 || Query::paged() > 1 );
				break;

			case '404':
				if ( Query::is_singular_archive() ) {
					/**
					 * Pagination overflow protection via 404 test.
					 *
					 * When there are no posts, the first page will NOT relay 404;
					 * which is exactly as intended. All other pages will relay 404.
					 *
					 * We do not test the post_count here, because we want to have
					 * the first page indexable via user-intent only. Concordingly, too
					 * because we cannot assert this via the administrative dashboard.
					 */
					yield '404' => \is_404();
				} else {
					/**
					 * Check for 404, or if archive is empty: set noindex for those.
					 *
					 * Don't check this on the homepage. The homepage is sacred in this regard,
					 * because page builders and templates can and will take over.
					 *
					 * Don't use empty(), null is regarded as indexable; it's why we coalesce to true whence null.
					 *
					 * post_count can be 0,    which is false -> thus yield true  -> noindex.
					 * post_count can be null, which is true  -> thus yield false -> index.
					 * post count can be 5,    which is true  => thus yield false -> index.
					 */
					if ( $GLOBALS['wp_query']->post_count ?? true ) {
						yield '404' => false;
					} else {
						/**
						 * We recommend using this filter ONLY for archives that have useful content but no "posts" attached.
						 * For example: a specially custom-developed author page for an author that never published a post.
						 *
						 * This filter won't run when a few other conditions for noindex have been met.
						 *
						 * @since 4.1.4
						 * @link <https://github.com/sybrew/the-seo-framework/issues/194#issuecomment-864298702>
						 * @param bool $noindex Whether to enable no posts protection.
						 */
						yield '404' => (bool) \apply_filters( 'the_seo_framework_enable_noindex_no_posts', true );
					}
				}
				break;

			case 'paged':
				// Advanced Query Protection protects further against pagination attacks. No need to have that here.
				yield 'paged' => Data\Plugin::get_option( 'paged_noindex' ) && Query::paged() > 1;
				break;

			case 'protected':
				// We get the "real ID" for WordPress might fault parsing a nefariously forged request.
				yield 'protected' => Data\Post::is_protected( Query::get_the_real_id() );
				break;

			case 'cpage':
				/**
				 * We do not recommend using this filter as it'll likely get those pages flagged as
				 * duplicated by Google anyway; unless the theme strips or trims the content.
				 *
				 * This filter won't run when other conditions for noindex have been met.
				 *
				 * @since 4.0.5
				 * @param bool $noindex Whether to enable comment pagination protection.
				 */
				yield 'cpage' => \apply_filters( 'the_seo_framework_enable_noindex_comment_pagination', true );
		}
	}
}
