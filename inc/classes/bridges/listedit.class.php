<?php
/**
 * @package The_SEO_Framework\Classes\Bridges\ListEdit
 * @subpackage The_SEO_Framework\Admin\Edit
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
 * Prepares the List Edit view interface.
 *
 * @since 3.3.0
 * @access protected
 * @internal
 * @final Can't be extended.
 */
final class ListEdit {
	use \The_SEO_Framework\Traits\Enclose_Stray_Private;

	/**
	 * @since 3.3.0
	 * @var string The quick edit column name.
	 */
	private static $quick_edit_column_name = 'tsf-quick-edit';

	/**
	 * Adds hidden column to access quick/bulk-edit.
	 *
	 * @since 3.3.0
	 * @access private
	 *
	 * @param array $columns The existing columns
	 * @return array $columns the column data
	 */
	public static function _set_quick_edit_column( $columns ) {
		// Don't set a title, otherwise it's displayed in the screen settings.
		$columns[ static::$quick_edit_column_name ] = '';
		return $columns;
	}

	/**
	 * Permanently hides quick/bulk-edit column.
	 *
	 * @since 3.3.0
	 * @access private
	 *
	 * @param array $hidden The existing hidden columns.
	 * @return array $columns the column data
	 */
	public static function _hide_quick_edit_column( $hidden ) {
		$hidden[] = static::$quick_edit_column_name;
		return $hidden;
	}

	/**
	 * Displays the SEO bulk edit fields.
	 *
	 * @since 3.3.0
	 * @access private
	 *
	 * @param string $column_name Name of the column to edit.
	 * @param string $post_type   The post type slug, or current screen name if this is a taxonomy list table.
	 * @param string $taxonomy    The taxonomy name, if any.
	 */
	public static function _display_bulk_edit_fields( $column_name, $post_type, $taxonomy = '' ) {

		if ( static::$quick_edit_column_name !== $column_name ) return;

		// phpcs:ignore, Generic.CodeAnalysis.EmptyStatement -- Future.
		if ( $taxonomy ) {
			// This can never run. Future stuff.
		} else {
			// echo 'test bulk';
		}
	}

	/**
	 * Displays the SEO quick edit fields.
	 *
	 * @since 3.3.0
	 * @access private
	 *
	 * @param string $column_name Name of the column to edit.
	 * @param string $post_type   The post type slug, or current screen name if this is a taxonomy list table.
	 * @param string $taxonomy    The taxonomy name, if any.
	 */
	public static function _display_quick_edit_fields( $column_name, $post_type, $taxonomy = '' ) {

		if ( static::$quick_edit_column_name !== $column_name ) return;

		if ( $taxonomy ) {
			// echo 'test term';
		} else {
			// echo 'test post';
		}
	}
}
