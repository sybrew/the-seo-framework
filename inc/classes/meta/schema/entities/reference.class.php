<?php
/**
 * @package The_SEO_Framework\Meta\Schema\Entities\Reference
 * @subpackage The_SEO_Framework\Meta\Schema
 */

namespace The_SEO_Framework\Meta\Schema\Entities;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Meta;

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
 * Abstract class of Schema.org referential structured data entity builder.
 *
 * @since 5.0.0
 * @access private
 *
 * @property string|string[] $type The Schema @type.
 * @property array[] $references
 */
abstract class Reference {

	/**
	 * @since 5.0.0
	 * @var string|string[] $type The Schema @type.
	 */
	public static $type;

	/**
	 * @var array $references A list of references
	 */
	public static $references = [];

	/**
	 * @since 5.0.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return string The entity ID for $args.
	 */
	public static function get_id( $args = null ) { // phpcs:ignore, VariableAnalysis.CodeAnalysis.VariableAnalysis -- abstract ref.
		return Meta\URI::get_bare_front_page_url() . '#/schema/' . current( (array) static::$type );
	}

	/**
	 * @since 5.0.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return array The instant reference by args.
	 */
	public static function get_instant_ref( $args = null ) {
		return [ '@id' => static::get_id( $args ) ];
	}

	/**
	 * @since 5.0.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return reference   The dynamic reference by args.
	 */
	public static function &get_dynamic_ref( $args = null ) {

		$id = static::get_id( $args );

		Meta\Schema::register_entity_writer(
			$id,
			[ __CLASS__, 'write' ],
		);

		if ( empty( static::$references[ $id ] ) ) {
			static::$references[ $id ] = [
				'entity'   => static::build( $args ),
				'referred' => 1,
			];
		} else {
			++static::$references[ $id ]['referred'];
		}

		// Return by reference -- overwrite in write if requested more than once.
		return static::$references[ $id ]['entity'];
	}

	/**
	 * @since 5.0.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return ?array $entity The Schema.org graph entity. Null on failure.
	 */
	abstract public static function build( $args = null );

	/**
	 * Writes all references, or to itself if there's only one reference.
	 *
	 * @since 5.0.0
	 * @generator
	 */
	public static function write() {

		$refs = &static::$references;

		foreach ( $refs as $id => $data ) {
			if ( $data['referred'] > 1 ) {
				// Write entity to extra graph (unreferred).
				yield $data['entity'];

				// Change all references to symbolic links.
				$data['entity'] = [ '@id' => $id ];
			}
		}

		// Reset after writing.
		$refs = [];
	}
}
