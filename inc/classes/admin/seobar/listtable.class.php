<?php
/**
 * @package The_SEO_Framework\Classes\Admin\SEOBar\ListTable
 * @subpackage The_SEO_Framework\SEOBar
 */

namespace The_SEO_Framework\Admin\SEOBar;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Admin;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Loads the SEO Bar for administrative tables.
 *
 * List is a reserved keyword in PHP, so we use ListTable.
 * PHP should deprecate list() and array().
 *
 * @since 4.0.0
 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Bridges`.
 *              2. Renamed from `SEOBar`.
 * @access private
 */
final class ListTable extends Admin\Lists\Table {

	/**
	 * @since 4.0.0
	 * @var string The column name.
	 */
	private $column_name = 'tsf-seo-bar-wrap';

	/**
	 * Setups class and prepares quick edit.
	 *
	 * @hook admin_init 10
	 * @since 5.0.0
	 */
	public static function init_seo_bar() {
		new self;
	}

	/**
	 * Adds SEO column on edit(-tags).php
	 *
	 * Also determines where the column should be placed. Preferred before comments, then data, then tags.
	 * When none found, it will add the column to the end.
	 *
	 * @hook manage_{$screen_id}_columns 10
	 * @hook manage_edit-{$taxonomy}_columns 1
	 * @since 4.0.0
	 * @since 5.0.0 Renamed from `_add_column`.
	 * @abstract
	 *
	 * @param array $columns The existing columns.
	 * @return array $columns The adjusted columns.
	 */
	public function add_column( $columns ) {

		$seocolumn = [ $this->column_name => 'SEO' ];

		$column_keys = array_keys( $columns );

		// Column keys to look for, in order of appearance.
		$order_keys = [
			'comments',
			'posts',
			'date',
			'tags',
		];

		/**
		 * @since 2.8.0
		 * @param string[] $order_keys The keys where the SEO column may be prepended to.
		 *                             The first key found will be used.
		 */
		$order_keys = (array) \apply_filters( 'the_seo_framework_seo_column_keys_order', $order_keys );

		foreach ( $order_keys as $key ) {
			// Put value in $offset, if not false, break loop.
			$offset = array_search( $key, $column_keys, true );
			if ( false !== $offset )
				break;
		}

		// It tried but found nothing
		if ( false === $offset ) {
			// Add SEO bar to the end of columns.
			$columns = array_merge( $columns, $seocolumn );
		} else {
			// Add seo bar between columns.

			// Cache columns.
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
	 * @hook manage_posts_custom_column 1
	 * @hook manage_pages_custom_column 1
	 * @since 4.0.0
	 * @since 5.0.0 Renamed from `_output_column_contents_for_post`.
	 * @abstract
	 *
	 * @param string $column_name The name of the column to display.
	 * @param int    $post_id     The current post ID.
	 */
	public function output_column_contents_for_post( $column_name, $post_id ) {

		if ( $this->column_name !== $column_name ) return;

		// phpcs:ignore, WordPress.Security.EscapeOutput -- generate_bar escapes.
		echo Builder::generate_bar( [
			'id'        => $post_id,
			'post_type' => $this->post_type,
		] );

		if ( $this->doing_ajax )
			echo $this->get_ajax_dispatch_updated_event(); // phpcs:ignore, WordPress.Security.EscapeOutput
	}

	/**
	 * Returns the SEO Bar for terms.
	 *
	 * @hook manage_{$taxonomy}_custom_column 1
	 * @since 4.0.0
	 * @since 5.0.0 Renamed from `_output_column_contents_for_term`.
	 * @abstract
	 * @NOTE Unlike output_column_contents_for_post(), this is a filter callback.
	 *       Because of this, the first parameter is a useless string, which must be extended.
	 *       Discrepancy: https://core.trac.wordpress.org/ticket/33521
	 *       With this, the proper function name should be "_get..." or "_add...", but not "_output.."
	 *
	 * @param string $string      Blank string.
	 * @param string $column_name Name of the column.
	 * @param string $term_id     Term ID.
	 * @return string
	 */
	public function output_column_contents_for_term( $string, $column_name, $term_id ) {

		if ( $this->column_name !== $column_name ) return $string;

		if ( $this->doing_ajax )
			$string .= $this->get_ajax_dispatch_updated_event();

		return Builder::generate_bar( [
			'id'  => $term_id,
			'tax' => $this->taxonomy,
		] ) . $string;
	}
}
