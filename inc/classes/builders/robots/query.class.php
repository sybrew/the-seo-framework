<?php
/**
 * @package The_SEO_Framework\Classes\Builders\Robots\Query
 * @subpackage The_SEO_Framework\Getter\Robots
 */

namespace The_SEO_Framework\Builders\Robots;

/**
 * The SEO Framework plugin
 * Copyright (C) 2021 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * Engine for robots generator via query.
 *
 * @since 4.2.0
 * @access private
 *         Not part of the public API.
 * @final Can't be extended.
 */
final class Query extends Factory {

	// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- You don't love PHP7.
	// phpcs:disable, PSR2.ControlStructures.SwitchDeclaration.TerminatingComment -- You hate goto.
	// phpcs:disable, Generic.WhiteSpace.ScopeIndent.IncorrectExact -- You hate gotoo.
	/**
	 * Generates robots assertions for no[index|archive|follow].
	 *
	 * Yields true when "noindex/noarchive/nofollow", yields false when "index/archive/follow".
	 *
	 * @since 4.2.0
	 * @generator
	 *
	 * @param string $type The robots generator type (noindex, nofollow...).
	 */
	protected static function assert_no( $type ) {

		// Remit FETCH_STATIC_PROP_R opcode calls every time we'd otherwise use static::$tsf hereinafter.
		$tsf = static::$tsf;

		$asserting_noindex = 'noindex' === $type;

		meta_settings: {
			// We assert options here for a jump to meta_settings might be unaware.
			if ( static::$options & \The_SEO_Framework\ROBOTS_IGNORE_SETTINGS )
				goto after_meta_settings;

			$qubit = null;

			if ( $tsf->is_term_meta_capable() ) {
				$qubit = (int) $tsf->get_term_meta_item( $type );
			} elseif ( $tsf->is_singular() ) {
				$qubit = (int) $tsf->get_post_meta_item( "_genesis_$type" );
			} elseif ( \is_post_type_archive() ) {
				$qubit = (int) $tsf->get_post_type_archive_meta_item( $type );
			}

			switch ( isset( $qubit ) ) :
				case false:
					// Page doesn't support metadata.
					break;
				case $qubit < -.33:
					// 'Force' index.
					yield 'meta_qubit_force' => false;
					// Override with index protection.
					goto index_protection;
				case $qubit > .33:
					// Force noindex.
					yield 'meta_qubit_force' => true;
					// We won't override this. Terminate generator. "goto end".
					// No break, generator stops here anyway.
				default:
					// qubit is (closer to) 0. Assert we use _default, albeit false.
					yield 'meta_qubit_default' => false;
			endswitch;
		}
		after_meta_settings:;

		globals: {
			yield 'globals_site' => (bool) $tsf->get_option( "site_$type" );

			if ( $tsf->is_real_front_page() ) {
				yield 'globals_homepage' => (bool) $tsf->get_option( "homepage_$type" );

				if ( ! ( static::$options & \The_SEO_Framework\ROBOTS_IGNORE_PROTECTION ) )
					$asserting_noindex and yield from static::assert_noindex_query_pass( 'paged_home' );
			} else {
				$asserting_noindex and yield from static::assert_noindex_query_pass( '404' );

				if ( ! ( static::$options & \The_SEO_Framework\ROBOTS_IGNORE_PROTECTION ) )
					if ( $asserting_noindex && ( $tsf->is_archive() || $tsf->is_singular_archive() ) )
						yield from static::assert_noindex_query_pass( 'paged' );

				if ( $tsf->is_archive() ) {
					if ( $tsf->is_author() ) {
						yield 'globals_author' => (bool) $tsf->get_option( "author_$type" );
					} elseif ( $tsf->is_date() ) {
						yield 'globals_date' => (bool) $tsf->get_option( "date_$type" );
					}
				} elseif ( $tsf->is_search() ) {
					yield 'globals_search' => (bool) $tsf->get_option( "search_$type" );
				}
			}

			// is_real_front_page() can still be singular or archive. Thus, this conditional block is split up.
			if ( $tsf->is_archive() ) {
				if ( $tsf->is_category() || $tsf->is_tag() || $tsf->is_tax() ) {
					yield 'globals_taxonomy' => $tsf->is_taxonomy_robots_set( $type, $tsf->get_current_taxonomy() );

					// Store values from each post type bound to the taxonomy.
					foreach ( $tsf->get_post_types_from_taxonomy() as $post_type )
						$_is_post_type_robots_set[] = $tsf->is_post_type_robots_set( $type, $post_type );

					// Only enable if _all_ post types have been marked with 'no*'. Return false if no post types are found (corner case).
					yield 'globals_post_type_all' => isset( $_is_post_type_robots_set ) && ! \in_array( false, $_is_post_type_robots_set, true );
				} elseif ( \is_post_type_archive() ) {
					yield 'globals_post_type' => $tsf->is_post_type_robots_set( $type, $tsf->get_current_post_type() );
				}
			} elseif ( $tsf->is_singular() ) {
				yield 'globals_post_type' => $tsf->is_post_type_robots_set( $type, $tsf->get_current_post_type() );
			}
		}

		index_protection: if ( $asserting_noindex ) {
			// We assert options here for a jump to index_protection might be unaware.
			if ( static::$options & \The_SEO_Framework\ROBOTS_IGNORE_PROTECTION )
				goto after_index_protection;

			if ( $tsf->is_singular() ) {
				// A reiteration of the very same code as above... but, homepage may not always be singular.
				// The conditions below MUST overwrite this, too. So, this is the perfect placement.
				if ( $tsf->is_real_front_page() )
					yield from static::assert_noindex_query_pass( 'paged_home' );

				yield from static::assert_noindex_query_pass( 'protected' );

				/**
				 * N.B. WordPress protects this query variable with options 'page_comments'
				 * and 'default_comments_page' via `redirect_canonical()`, so we don't have to.
				 * For reference, it fires `remove_query_arg( 'cpage', $redirect['query'] )`;
				 */
				if ( (int) \get_query_var( 'cpage', 0 ) > 0 )
					yield from static::assert_noindex_query_pass( 'cpage' );
			}
		}
		after_index_protection:;

		exploit_protection: if ( $tsf->is_query_exploited() ) {
			if ( \in_array( $type, [ 'noindex', 'nofollow' ], true ) )
				yield 'query_protection' => true;
		}
		after_exploit_protection:;

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
		// Remit FETCH_STATIC_PROP_R opcode calls every time we'd otherwise use static::$tsf hereinafter.
		$tsf = static::$tsf;

		switch ( $pass ) :
			case 'paged_home':
				yield 'paged_home' => ( $tsf->get_option( 'home_paged_noindex' ) && ( $tsf->page() > 1 || $tsf->paged() > 1 ) );
				break;

			case '404':
				if ( $tsf->is_singular_archive() ) :
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
					yield '404' => $tsf->is_404();
				else :
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
				endif;
				break;

			case 'paged':
				// Advanced Query Protection protects further against pagination attacks. No need to have that here.
				yield 'paged' => $tsf->get_option( 'paged_noindex' ) && $tsf->paged() > 1;
				break;

			case 'protected':
				// We get the "real ID" for WordPress might fault parsing a nefariously forged request.
				yield 'protected' => $tsf->is_protected( $tsf->get_the_real_ID() );
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
				break;
		endswitch;
	}
}
