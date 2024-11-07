<?php
/**
 * @package The_SEO_Framework\Classes\Meta
 * @subpackage The_SEO_Framework\Meta\Schema
 */

namespace The_SEO_Framework\Meta;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\{
	get_query_type_from_args,
	normalize_generation_args,
};

use \The_SEO_Framework\{
	Data,
	Helper\Format\Arrays,
	Helper\Query,
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
 * Holds getters for meta tag output.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->schema() instead.
 */
class Schema {

	/**
	 * @var callable[] The writer queue.
	 */
	private static $writer_queue = [];

	/**
	 * Returns the Schema.org graph.
	 *
	 * @since 5.0.0
	 * @see https://developers.google.com/search/docs/appearance/structured-data/search-gallery
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return array The Schema.org graph, if any.
	 */
	public static function get_generated_graph( $args = null ) {

		if ( ! Data\Plugin::get_option( 'ld_json_enabled' ) )
			return [];

		if ( isset( $args ) ) {
			normalize_generation_args( $args );

			// If is protected post, then only set WebSite to prevent spilling data.
			if ( 'single' === get_query_type_from_args( $args ) && Data\Post::is_protected( $args['id'] ) ) {
				// Don't spill WebPage data if protected.
				$primaries = [ 'WebSite' ];
			}
		} else {
			if ( Query::is_singular() && Data\Post::is_protected() ) {
				// Don't spill WebPage data if protected.
				$primaries = [ 'WebSite' ];
			}
		}

		$primaries ??= [ 'WebSite', 'WebPage' ];

		$builders_queue = [];
		// Queue array_merge for improved performance.
		foreach ( $primaries as $class )
			$builders_queue[] = ( "\The_SEO_Framework\Meta\Schema\Entities\\$class" )::BUILDERS;

		/**
		 * @since 5.0.0
		 * @param callable[] $entity_builders A list of Schema.org entity builders.
		 * @param array|null $args            The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
		 *                                    Is null when being autodetermined.
		 */
		$entity_builders = \apply_filters(
			'the_seo_framework_schema_entity_builders',
			array_merge( ...$builders_queue ),
			$args,
		);

		$graph = [];
		// Build the primary objects in the graph.
		foreach ( $entity_builders as $builder )
			$graph[] = \call_user_func( $builder, $args );

		/**
		 * For consistency, data should be filtered deep, such as (WordPress) title
		 * filters for breadcrumb and page titles. Use this only if those aren't available.
		 *
		 * Use this only to adjust write dynamic references.
		 * Use `the_seo_framework_schema_graph_data` for direct alteration instead.
		 *
		 * @since 5.1.0
		 * @param array[]    $graph A sequential list of graph entities.
		 * @param array|null $args  The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
		 *                          Is null when the query is autodetermined.
		 */
		$graph = \apply_filters(
			'the_seo_framework_schema_queued_graph_data',
			$graph,
			$args,
		);

		// Fill the graph's references dynamically. Append extra graphs when given.
		foreach ( static::$writer_queue as $writer )
			foreach ( \call_user_func( $writer ) as $extra_graph )
				$graph[] = $extra_graph;

		// Reset queue.
		static::$writer_queue = [];

		/**
		 * For consistency, data should be filtered deep, such as (WordPress) title
		 * filters for breadcrumb and page titles. Use this only if those aren't available.
		 *
		 * @since 5.0.0
		 * @param array[]    $graph A sequential list of graph entities.
		 * @param array|null $args  The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
		 *                          Is null when the query is autodetermined.
		 */
		$graph = \apply_filters(
			'the_seo_framework_schema_graph_data',
			$graph,
			$args,
		);

		if ( empty( $graph ) ) return [];

		return [
			'@context' => 'https://schema.org',
			'@graph'   => Arrays::scrub( $graph ),
		];
	}

	/**
	 * Registers an entity writer for the current graph.
	 *
	 * @since 5.0.0
	 *
	 * @param string   $id       The identifier to write.
	 * @param callable $callback The callback to call to write graph.
	 */
	public static function register_entity_writer( $id, $callback ) {
		static::$writer_queue[ $id ] = $callback;
	}
}
