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
 * Copyright (C) 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Engine for robots generator by arguments.
 *
 * @since 4.2.0
 * @since 5.0.0 Moved from `\The_SEO_Framework\Builders\Robots`.
 * @access private
 */
final class Args extends Factory {

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

		// Remit FETCH_STATIC_PROP_R opcode calls every time we'd otherwise use static::$args hereinafter.
		$args = static::$args;

		$asserting_noindex = 'noindex' === $type;

		// We assert options here for a jump to meta_settings might be unaware.
		meta_settings: if ( ! ( static::$options & ROBOTS_IGNORE_SETTINGS ) ) {
			$qubit = null;

			if ( $args['tax'] ) {
				$qubit = (int) Data\Plugin\Term::get_meta_item( $type, $args['id'] );
			} elseif ( $args['id'] ) {
				$qubit = (int) Data\Plugin\Post::get_meta_item( "_genesis_$type", $args['id'] );
			} elseif ( $args['pta'] ) {
				$qubit = (int) Data\Plugin\PTA::get_meta_item( $type, $args['pta'] );
			}

			switch ( isset( $qubit ) ) {
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
			}
		}

		globals:
			yield 'globals_site' => (bool) Data\Plugin::get_option( "site_$type" );

			if ( $args['tax'] ) {
				$asserting_noindex and yield from static::assert_noindex_query_pass( '404' );

				yield 'globals_taxonomy' => Robots::is_taxonomy_robots_set( $type, $args['tax'] );

				// Store values from each post type bound to the taxonomy.
				foreach ( Taxonomy::get_post_types( $args['tax'] ) as $post_type )
					$_is_post_type_robots_set[] = Robots::is_post_type_robots_set( $type, $post_type );

				// Only enable if _all_ post types have been marked with 'no*'. Return false if no post types are found (corner case).
				yield 'globals_post_type_all' => isset( $_is_post_type_robots_set ) && ! \in_array( false, $_is_post_type_robots_set, true );
			} elseif ( $args['pta'] ) {
				yield 'globals_post_type' => Robots::is_post_type_robots_set( $type, $args['pta'] );
			} elseif ( empty( $args['uid'] ) ) {
				// $args['id'] can be empty, pointing to a plausible homepage query.
				if ( Query::is_real_front_page_by_id( $args['id'] ) )
					yield 'globals_homepage' => (bool) Data\Plugin::get_option( "homepage_$type" );

				if ( $args['id'] )
					yield 'globals_post_type' => Robots::is_post_type_robots_set( $type, \get_post_type( $args['id'] ) );
			}

		index_protection: if ( $asserting_noindex && ! ( static::$options & ROBOTS_IGNORE_PROTECTION ) ) {
			if ( empty( $args['tax'] ) && empty( $args['pta'] ) && empty( $args['uid'] ) )
				yield from static::assert_noindex_query_pass( 'protected' );
		}

		end:;
	}
	// phpcs:enable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
	// phpcs:enable, PSR2.ControlStructures.SwitchDeclaration.TerminatingComment
	// phpcs:disable, Generic.WhiteSpace.ScopeIndent.IncorrectExact

	/**
	 * Generates robots assertions for noindex in passes.
	 *
	 * @since 4.2.0
	 * @generator
	 *
	 * @param string $pass The passage to assert.
	 */
	private static function assert_noindex_query_pass( $pass ) {

		// Remit FETCH_STATIC_PROP_R opcode calls every time we'd otherwise use static::$args hereinafter.
		$args = static::$args;

		switch ( $pass ) {
			case '404':
				yield '404' => ! Data\Term::is_term_populated( $args['id'], $args['tax'] );
				break;

			case 'protected':
				// We get the "real ID" for WordPress might fault parsing a nefariously forged request.
				yield 'protected' => Data\Post::is_protected( $args['id'] );
		}
	}
}
