<?php
/**
 * @package The_SEO_Framework\Classes\Bridges\ListEdit
 * @subpackage The_SEO_Framework\Admin\Edit\List
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
 * @since 4.0.0
 * @access protected
 * @internal
 * @final Can't be extended.
 */
final class ListEdit extends ListTable {

	/**
	 * @since 4.0.0
	 * @var string The column name.
	 */
	private $column_name = 'tsf-quick-edit';

	/**
	 * Constructor, sets column name and calls parent.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		parent::__construct();

		\add_filter( 'hidden_columns', [ $this, '_hide_quick_edit_column' ], 10, 1 );
		\add_action( 'current_screen', [ $this, '_prepare_edit_box' ] );
	}

	/**
	 * Prepares the quick/bulk edit output.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @param \WP_Screen|string $screen \WP_Screen
	 */
	public function _prepare_edit_box( $screen ) {

		$taxonomy = isset( $screen->taxonomy ) ? $screen->taxonomy : '';

		if ( ! $taxonomy ) {
			// WordPress doesn't support this feature yet for taxonomies.
			// Exclude it for when the time may come and faulty fields are displayed.
			// Mind the "2".
			\add_action( 'bulk_edit_custom_box', [ $this, '_display_bulk_edit_fields' ], 10, 2 );
		}
		\add_action( 'quick_edit_custom_box', [ $this, '_display_quick_edit_fields' ], 10, 3 );
	}

	/**
	 * Permanently hides quick/bulk-edit column.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @param array $hidden The existing hidden columns.
	 * @return array $columns the column data
	 */
	public function _hide_quick_edit_column( $hidden ) {
		$hidden[] = $this->column_name;
		return $hidden;
	}

	/**
	 * Adds hidden column to access quick/bulk-edit.
	 * This column is a dummy, but it's required to display quick/bulk edit items.
	 *
	 * @since 4.0.0
	 * @access private
	 * @abstract
	 *
	 * @param array $columns The existing columns
	 * @return array $columns the column data
	 */
	public function _add_column( $columns ) {
		// Don't set a title, otherwise it's displayed in the screen settings.
		$columns[ $this->column_name ] = '';
		return $columns;
	}

	/**
	 * Displays the SEO bulk edit fields.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @param string $column_name Name of the column to edit.
	 * @param string $post_type   The post type slug, or current screen name if this is a taxonomy list table.
	 * @param string $taxonomy    The taxonomy name, if any.
	 */
	public function _display_bulk_edit_fields( $column_name, $post_type, $taxonomy = '' ) {

		if ( $this->column_name !== $column_name ) return;

		// phpcs:ignore, Generic.CodeAnalysis.EmptyStatement -- For the future, when WordPress Core decides.
		if ( $taxonomy ) {
			// Not yet.
		} else {
			\the_seo_framework()->get_view( 'list/bulk-post' );
		}
	}

	/**
	 * Displays the SEO quick edit fields.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @param string $column_name Name of the column to edit.
	 * @param string $post_type   The post type slug, or current screen name if this is a taxonomy list table.
	 * @param string $taxonomy    The taxonomy name, if any.
	 */
	public function _display_quick_edit_fields( $column_name, $post_type, $taxonomy = '' ) {

		if ( $this->column_name !== $column_name ) return;

		if ( $taxonomy ) {
			\the_seo_framework()->get_view( 'list/quick-term' );
		} else {
			\the_seo_framework()->get_view( 'list/quick-post' );
		}
	}

	/**
	 * Outputs the quick edit data for posts and pages.
	 *
	 * @since 4.0.0
	 * @access private
	 * @abstract
	 *
	 * @param string $column_name The name of the column to display.
	 * @param int    $post_id     The current post ID.
	 */
	public function _output_column_contents_for_post( $column_name, $post_id ) {

		if ( $this->column_name !== $column_name )          return;
		if ( ! \current_user_can( 'edit_post', $post_id ) ) return;

		$tsf = \the_seo_framework();

		$query = [ 'id' => $post_id ];

		$r_defaults = $tsf->robots_meta(
			$query,
			\The_SEO_Framework\ROBOTS_IGNORE_SETTINGS | \The_SEO_Framework\ROBOTS_IGNORE_PROTECTION
		);

		$meta = $tsf->get_post_meta( $post_id );

		// NB: The indexes correspond to `autodescription-list[index]` field input names.
		$data = [
			'canonical' => [
				'value' => $meta['_genesis_canonical_uri'],
			],
			'noindex'   => [
				'default' => empty( $r_defaults['noindex'] ) ? 'index' : 'noindex',
				'value'   => $meta['_genesis_noindex'],
			],
			'nofollow'  => [
				'default' => empty( $r_defaults['nofollow'] ) ? 'follow' : 'nofollow',
				'value'   => $meta['_genesis_nofollow'],
			],
			'noarchive' => [
				'default' => empty( $r_defaults['noarchive'] ) ? 'archive' : 'noarchive',
				'value'   => $meta['_genesis_noarchive'],
			],
			'redirect'  => [
				'value' => $meta['redirect'],
			],
		];

		printf(
			'<span class=hidden id=%s data-le="%s"></span>',
			sprintf( 'tsfLeData[%s]', (int) $post_id ),
			// phpcs:ignore, WordPress.Security.EscapeOutput -- esc_attr is too aggressive.
			htmlspecialchars( json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT ), ENT_QUOTES, 'UTF-8' )
		);
	}

	/**
	 * Returns the quick edit data for terms.
	 *
	 * @since 4.0.0
	 * @access private
	 * @abstract
	 * @NOTE Unlike `_output_column_post_data()`, this is a filter callback.
	 *       Because of this, the first parameter is a useless string, which must be extended.
	 *       Discrepancy: https://core.trac.wordpress.org/ticket/33521
	 *
	 * @param string $string      Blank string.
	 * @param string $column_name Name of the column.
	 * @param string $term_id     Term ID.
	 * @return string
	 */
	public function _output_column_contents_for_term( $string, $column_name, $term_id ) {

		if ( $this->column_name !== $column_name )          return $string;
		if ( ! \current_user_can( 'edit_term', $term_id ) ) return $string;

		$tsf = \the_seo_framework();

		$r_defaults = $tsf->robots_meta(
			[
				'id'       => $term_id,
				'taxonomy' => $this->taxonomy,
			],
			\The_SEO_Framework\ROBOTS_IGNORE_SETTINGS | \The_SEO_Framework\ROBOTS_IGNORE_PROTECTION
		);

		$meta = $tsf->get_term_meta( $term_id );

		// NB: The indexes correspond to `autodescription-list[index]` field input names.
		$data = [
			'canonical' => [
				'value' => $meta['canonical'],
			],
			'noindex'   => [
				'default' => empty( $r_defaults['noindex'] ) ? 'index' : 'noindex',
				'value'   => $meta['noindex'],
			],
			'nofollow'  => [
				'default' => empty( $r_defaults['nofollow'] ) ? 'follow' : 'nofollow',
				'value'   => $meta['nofollow'],
			],
			'noarchive' => [
				'default' => empty( $r_defaults['noarchive'] ) ? 'archive' : 'noarchive',
				'value'   => $meta['noarchive'],
			],
			'redirect'  => [
				'value' => $meta['redirect'],
			],
		];

		$container = sprintf(
			'<span class=hidden id=%s data-le="%s"></span>',
			sprintf( 'tsfLeData[%s]', (int) $term_id ),
			htmlspecialchars( json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT ), ENT_QUOTES, 'UTF-8' )
		);

		return $string . $container;
	}
}
