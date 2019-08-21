<?php
/**
 * @package The_SEO_Framework\Classes\Bridges\SeoBar
 * @subpackage The_SEO_Framework\SeoBar
 */

namespace The_SEO_Framework\Bridges;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * Loads the SEO Bar for administrative tables.
 *
 * @since 4.0.0
 * @uses \The_SEO_Framework\Interpreters\SeoBar
 * @see \The_SEO_Framework\Interpreters\SeoBar to generate a bar.
 *
 * @access private
 */
final class SeoBar extends ListTable {

	/**
	 * @since 4.0.0
	 * @var string The column name.
	 */
	private $column_name = 'tsf-seo-bar-wrap';

	/**
	 * Adds SEO column on edit(-tags).php
	 *
	 * Also determines where the column should be placed. Preferred before comments, then data, then tags.
	 * When none found, it will add the column to the end.
	 *
	 * @since 4.0.0
	 * @access private
	 * @abstract
	 *
	 * @param array $columns The existing columns.
	 * @return array $columns The adjusted columns.
	 */
	public function _add_column( $columns ) {

		$seocolumn = [ $this->column_name => 'SEO' ];

		$column_keys = array_keys( $columns );

		//* Column keys to look for, in order of appearance.
		$order_keys = [
			'comments',
			'posts',
			'date',
			'tags',
		];

		/**
		 * @since 2.8.0
		 * @param array $order_keys The keys where the SEO column may be prepended to.
		 *                          The first key found will be used.
		 */
		$order_keys = (array) \apply_filters( 'the_seo_framework_seo_column_keys_order', $order_keys );

		foreach ( $order_keys as $key ) {
			//* Put value in $offset, if not false, break loop.
			$offset = array_search( $key, $column_keys, true );
			if ( false !== $offset )
				break;
		}

		//* It tried but found nothing
		if ( false === $offset ) {
			//* Add SEO bar at the end of columns.
			$columns = array_merge( $columns, $seocolumn );
		} else {
			//* Add seo bar between columns.

			//* Cache columns.
			$columns_before = $columns;

			$columns = array_merge(
				array_splice( $columns, 0, $offset ),
				$seocolumn,
				array_splice( $columns_before, $offset )
			);
		}

		return $columns;
	}

	/**
	 * Outputs the SEO Bar for posts and pages.
	 *
	 * @since 4.0.0
	 * @access private
	 * @abstract
	 *
	 * @param string $column_name The name of the column to display.
	 * @param int    $post_id     The current post ID.
	 */
	public function _output_column_contents_for_post( $column_name, $post_id ) {

		if ( $this->column_name !== $column_name ) return;

		// phpcs:ignore, WordPress.Security.EscapeOutput
		echo \The_SEO_Framework\Interpreters\SeoBar::generate_bar( [
			'id'        => $post_id,
			'post_type' => $this->post_type,
		] );

		if ( $this->doing_ajax )
			echo $this->get_seo_bar_ajax_script(); // phpcs:ignore, WordPress.Security.EscapeOutput
	}

	/**
	 * Returns the SEO Bar for terms.
	 *
	 * @since 4.0.0
	 * @access private
	 * @abstract
	 * @NOTE Unlike _output_seo_bar_for_column(), this is a filter callback.
	 *       Because of this, the first parameter is a useless string, which must be extended.
	 *       Discrepancy: https://core.trac.wordpress.org/ticket/33521
	 *       With this, the proper function name should be "_get..." or "_add...", but not "_output.."
	 *
	 * @param string $string      Blank string.
	 * @param string $column_name Name of the column.
	 * @param string $term_id     Term ID.
	 * @return string
	 */
	public function _output_column_contents_for_term( $string, $column_name, $term_id ) {

		if ( $this->column_name !== $column_name ) return $string;

		if ( $this->doing_ajax )
			$string .= $this->get_seo_bar_ajax_script();

		return \The_SEO_Framework\Interpreters\SeoBar::generate_bar( [
			'id'       => $term_id,
			'taxonomy' => $this->taxonomy,
		] ) . $string;
	}

	/**
	 * Outputs a JS script that triggers SEO Bar updates.
	 * This is a necessity as WordPress doesn't trigger actions on update.
	 *
	 * TODO bind to WordPress' function instead? Didn't we already do that?!
	 * See: `tsfLe._hijackListeners()`; Although, that doesn't cover "adding" new items.
	 *
	 * @since 4.0.0
	 *
	 * @return string The triggering script.
	 */
	private function get_seo_bar_ajax_script() {
		return "<script>'use strict';(()=>document.dispatchEvent(new Event('tsfLeUpdated')))();</script>";
	}
}
