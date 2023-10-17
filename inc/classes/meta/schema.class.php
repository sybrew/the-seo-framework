<?php
/**
 * @package The_SEO_Framework\Classes\Meta
 * @subpackage The_SEO_Framework\Meta\Schema
 */

namespace The_SEO_Framework\Meta;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\{
	memo,
	umemo,
	normalize_generation_args,
	Utils\scrub_array,
};

use \The_SEO_Framework\Data;
use \The_SEO_Framework\Helper\{
	Format\Arrays,
	Query,
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
 * Holds getters for meta tag output.
 *
 * @since 4.3.0
 * @access protected
 *         Use tsf()->schema() instead.
 */
class Schema {

	/**
	 * @var callable[] The writer queue.
	 */
	private static $writer_queue = [];

	/**
	 * Returns the JSON encoded Schema.org graph.
	 *
	 * @since 4.3.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                         Leave null to autodetermine query.
	 * @return string The JSON encoded Schema.org graph, if any. Empty string on failure.
	 */
	public static function get_generated_graph_in_json( $args = null ) {

		$graph = static::get_generated_graph( $args );

		return $graph
			? (string) \tsf()->escape_json_encode(
				$graph,
				( \SCRIPT_DEBUG ? \JSON_PRETTY_PRINT : 0 )
			)
			: '';
	}

	// NOTE: Specific stuff should be filtered deep in the generators -- such as the Breadcrumb generator's generator.

	/**
	 * Returns the Schema.org graph.
	 *
	 * @since 4.3.0
	 * @see https://developers.google.com/search/docs/appearance/structured-data/search-gallery
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                         Leave null to autodetermine query.
	 * @return array The Schema.org graph, if any.
	 */
	public static function get_generated_graph( $args = null ) {

		if ( isset( $args ) ) {
			normalize_generation_args( $args );

			if ( ! $args['tax'] && ! $args['pta'] && Data\Post::is_protected( $args['id'] ) ) {
				// Don't spill WebPage data if protected.
				$primaries = [ 'WebSite' ];
			}
		} else {
			if ( Data\Post::is_protected() ) {
				// Don't spill WebPage data if protected.
				$primaries = [ 'WebSite' ];
			}
		}

		$primaries ??= [ 'WebSite', 'WebPage' ];

		$builders_queue = [];
		// Queue array_merge for improved performance.
		foreach ( $primaries as $class )
			$builders_queue[] = ( "\The_SEO_Framework\Meta\Schema\Entities\\$class" )::BUILDERS; // Lacking import OK.

		/**
		 * @since 4.3.0
		 * @param callable[] $entity_builders A list of Schema.org entity builders.
		 * @param array|null $args            The query arguments. Accepts 'id', 'tax', and 'pta'.
		 *                                    Is null when being autodetermined.
		 */
		$entity_builders = \apply_filters_ref_array(
			'the_seo_framework_schema_entity_builders',
			[
				array_merge( ...$builders_queue ),
				$args,
			]
		);

		$graph = [];
		// Build the primary objects in the graph.
		foreach ( $entity_builders as $builder )
			$graph[] = \call_user_func( $builder, $args );

		// Fill the graph's references dynamically. Append extra graphs when given.
		foreach ( static::$writer_queue as $writer )
			foreach ( \call_user_func( $writer ) as $extra_graph )
				$graph[] = $extra_graph;

		// Reset queue.
		static::$writer_queue = [];

		/**
		 * @since 4.3.0
		 * @param array[]    $graph A sequential list of graph entities.
		 * @param array|null $args  The query arguments. Accepts 'id', 'tax', and 'pta'.
		 *                          Is null when the query is autodetermined.
		 */
		$graph = \apply_filters_ref_array(
			'the_seo_framework_schema_graph_data',
			[
				$graph,
				$args,
			]
		);

		if ( empty( $graph ) ) return [];

		return [
			'@context' => 'https://schema.org',
			'@graph'   => Arrays::scrub( $graph ),
		];
	}

	/**
	 * Returns the Schema.org graph.
	 *
	 * @since 4.3.0
	 *
	 * @param string   $id       The identifier to write.
	 * @param callable $callback The callback to call to write graph.
	 */
	public static function register_entity_writer( $id, $callback ) {
		static::$writer_queue[ $id ] = $callback;
	}
}
