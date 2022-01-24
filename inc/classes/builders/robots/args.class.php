<?php
/**
 * @package The_SEO_Framework\Classes\Builders\Robots\Args
 * @subpackage The_SEO_Framework\Getter\Robots
 */

namespace The_SEO_Framework\Builders\Robots;

/**
 * The SEO Framework plugin
 * Copyright (C) 2021 - 2022 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Engine for robots generator by arguments.
 *
 * @since 4.2.0
 * @access private
 *         Not part of the public API.
 * @final Can't be extended.
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

		// Remit FETCH_STATIC_PROP_R opcode calls every time we'd otherwise use static::$tsf/static::$args hereinafter.
		$tsf  = static::$tsf;
		$args = static::$args;

		$asserting_noindex = 'noindex' === $type;

		meta_settings: {
			// We assert options here for a jump to meta_settings might be unaware.
			if ( static::$options & \The_SEO_Framework\ROBOTS_IGNORE_SETTINGS )
				goto after_meta_settings;

			$qubit = null;

			if ( $args['taxonomy'] ) {
				$qubit = (int) $tsf->get_term_meta_item( $type, $args['id'] );
			} elseif ( $args['id'] ) {
				$qubit = (int) $tsf->get_post_meta_item( "_genesis_$type", $args['id'] );
			} elseif ( $args['pta'] ) {
				$qubit = (int) $tsf->get_post_type_archive_meta_item( $type, $args['pta'] );
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

			if ( $args['taxonomy'] ) {
				$asserting_noindex and yield from static::assert_noindex_query_pass( '404' );

				yield 'globals_taxonomy' => $tsf->is_taxonomy_robots_set( $type, $args['taxonomy'] );

				// Store values from each post type bound to the taxonomy.
				foreach ( $tsf->get_post_types_from_taxonomy( $args['taxonomy'] ) as $post_type )
					$_is_post_type_robots_set[] = $tsf->is_post_type_robots_set( $type, $post_type );

				// Only enable if _all_ post types have been marked with 'no*'. Return false if no post types are found (corner case).
				yield 'globals_post_type_all' => isset( $_is_post_type_robots_set ) && ! \in_array( false, $_is_post_type_robots_set, true );
			} elseif ( $args['pta'] ) {
				yield 'globals_post_type' => $tsf->is_post_type_robots_set( $type, $args['pta'] );
			} else {
				// $args['id'] can be empty, pointing to a plausible homepage query.
				if ( $tsf->is_real_front_page_by_id( $args['id'] ) )
					yield 'globals_homepage' => (bool) $tsf->get_option( "homepage_$type" );

				if ( $args['id'] )
					yield 'globals_post_type' => $tsf->is_post_type_robots_set( $type, \get_post_type( $args['id'] ) );
			}
		}

		index_protection: if ( $asserting_noindex ) {
			// We assert options here for a jump to index_protection might be unaware.
			if ( static::$options & \The_SEO_Framework\ROBOTS_IGNORE_PROTECTION )
				goto after_index_protection;

			if ( ! $args['taxonomy'] )
				yield from static::assert_noindex_query_pass( 'protected' );
		}
		after_index_protection:;

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

		// Remit FETCH_STATIC_PROP_R opcode calls every time we'd otherwise use static::$tsf/static::$args hereinafter.
		// $tsf  = static::$tsf;
		$args = static::$args;

		switch ( $pass ) :
			case '404':
				yield '404' => empty( \get_term( $args['id'], $args['taxonomy'] )->count );
				break;

			case 'protected':
				// We get the "real ID" for WordPress might fault parsing a nefariously forged request.
				yield 'protected' => static::$tsf->is_protected( $args['id'] );
				break;
		endswitch;
	}
}
