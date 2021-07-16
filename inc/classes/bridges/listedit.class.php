<?php
/**
 * @package The_SEO_Framework\Classes\Bridges\ListEdit
 * @subpackage The_SEO_Framework\Admin\Edit\List
 */

namespace The_SEO_Framework\Bridges;

use \The_SEO_Framework\Interpreters\HTML;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2021 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
			\the_seo_framework()->get_view( 'list/bulk-post', get_defined_vars() );
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
			\the_seo_framework()->get_view( 'list/quick-term', get_defined_vars() );
		} else {
			\the_seo_framework()->get_view( 'list/quick-post', get_defined_vars() );
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

		$query = [
			'id'       => $post_id,
			'taxonomy' => '',
		];

		$r_defaults = $tsf->generate_robots_meta(
			$query,
			null,
			\The_SEO_Framework\ROBOTS_IGNORE_SETTINGS
		);

		$meta = $tsf->get_post_meta( $post_id );

		// NB: The indexes correspond to `autodescription-list[index]` field input names.
		$data = [
			'doctitle'    => [
				'value' => $meta['_genesis_title'],
			],
			'description' => [
				'value' => $meta['_genesis_description'],
			],
			'canonical'   => [
				'value' => $meta['_genesis_canonical_uri'],
			],
			'noindex'     => [
				'value'    => $meta['_genesis_noindex'],
				'isSelect' => true,
				'default'  => empty( $r_defaults['noindex'] ) ? 'index' : 'noindex',
			],
			'nofollow'    => [
				'value'    => $meta['_genesis_nofollow'],
				'isSelect' => true,
				'default'  => empty( $r_defaults['nofollow'] ) ? 'follow' : 'nofollow',
			],
			'noarchive'   => [
				'value'    => $meta['_genesis_noarchive'],
				'isSelect' => true,
				'default'  => empty( $r_defaults['noarchive'] ) ? 'archive' : 'noarchive',
			],
			'redirect'    => [
				'value' => $meta['redirect'],
			],
		];

		/**
		 * Tip: Prefix the indexes with your (plugin) name to prevent collisions.
		 * The index corresponds to field with the ID `autodescription-quick[%s]`, where %s is the index.
		 *
		 * @since 4.0.5
		 * @since 4.1.0 Now has `doctitle` and `description` indexes in its first parameter.
		 * @param array $data  The current data : {
		 *    string Index => @param array : {
		 *       @param mixed  $value    The current value.
		 *       @param bool   $isSelect Optional. Whether the field is a select field.
		 *       @param string $default  Optional. Only works when $isSelect is true. The default value to be set in select index 0.
		 *    }
		 * }
		 * @param array $query The query data. Contains 'id' and 'taxonomy'.
		 */
		$data = \apply_filters_ref_array( 'the_seo_framework_list_table_data', [ $data, $query ] );

		printf(
			// '<span class=hidden id=%s data-le="%s"></span>',
			'<span class=hidden id=%s %s></span>',
			sprintf( 'tsfLeData[%s]', (int) $post_id ),
			// phpcs:ignore, WordPress.Security.EscapeOutput -- make_data_attributes escapes.
			HTML::make_data_attributes( [ 'le' => $data ] )
		);

		if ( $tsf->is_static_frontpage( $query['id'] ) ) {
			// phpcs:disable, WordPress.WhiteSpace.PrecisionAlignment
			// When the homepage title is set, we can safely get the custom field.
			$_has_home_title     = (bool) $tsf->escape_title( $tsf->get_option( 'homepage_title' ) );
			$default_title       = $_has_home_title
								 ? $tsf->get_custom_field_title( $query )
								 : $tsf->get_filtered_raw_generated_title( $query );
			$addition            = $tsf->get_home_title_additions();
			$seplocation         = $tsf->get_home_title_seplocation();
			$is_title_ref_locked = $_has_home_title;

			// When the homepage description is set, we can safely get the custom field.
			$_has_home_desc      = (bool) $tsf->escape_title( $tsf->get_option( 'homepage_description' ) );
			$default_description = $_has_home_desc
								 ? $tsf->get_description_from_custom_field( $query )
								 : $tsf->get_generated_description( $query );
			$is_desc_ref_locked  = $_has_home_desc;
			// phpcs:enable, WordPress.WhiteSpace.PrecisionAlignment
		} else {
			$default_title       = $tsf->get_filtered_raw_generated_title( $query );
			$addition            = $tsf->get_blogname();
			$seplocation         = $tsf->get_title_seplocation();
			$is_title_ref_locked = false;

			$default_description = $tsf->get_generated_description( $query );
			$is_desc_ref_locked  = false;
		}

		$post_data  = [
			'isFront' => $tsf->is_static_frontpage( $query['id'] ),
		];
		$title_data = [
			'refTitleLocked'    => $is_title_ref_locked,
			'defaultTitle'      => $default_title,
			'addAdditions'      => $tsf->use_title_branding( $query ),
			'additionValue'     => $tsf->s_title_raw( $addition ),
			'additionPlacement' => 'left' === $seplocation ? 'before' : 'after',
		];
		$desc_data  = [
			'refDescriptionLocked' => $is_desc_ref_locked,
			'defaultDescription'   => $default_description,
		];

		printf(
			// '<span class=hidden id=%s data-le-post-data="%s"></span>',
			'<span class=hidden id=%s %s></span>',
			sprintf( 'tsfLePostData[%s]', (int) $post_id ),
			// phpcs:ignore, WordPress.Security.EscapeOutput -- make_data_attributes escapes.
			HTML::make_data_attributes( [ 'lePostData' => $post_data ] )
		);
		printf(
			// '<span class=hidden id=%s data-le-title="%s"></span>',
			'<span class=hidden id=%s %s></span>',
			sprintf( 'tsfLeTitleData[%s]', (int) $post_id ),
			// phpcs:ignore, WordPress.Security.EscapeOutput -- make_data_attributes escapes.
			HTML::make_data_attributes( [ 'leTitle' => $title_data ] )
		);
		printf(
			// '<span class=hidden id=%s data-le-description="%s"></span>',
			'<span class=hidden id=%s %s></span>',
			sprintf( 'tsfLeDescriptionData[%s]', (int) $post_id ),
			// phpcs:ignore, WordPress.Security.EscapeOutput -- make_data_attributes escapes.
			HTML::make_data_attributes( [ 'leDescription' => $desc_data ] )
		);

		if ( $this->doing_ajax )
			echo $this->get_ajax_dispatch_updated_event(); // phpcs:ignore, WordPress.Security.EscapeOutput
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

		$query = [
			'id'       => $term_id,
			'taxonomy' => $this->taxonomy,
		];

		$r_defaults = $tsf->generate_robots_meta(
			$query,
			null,
			\The_SEO_Framework\ROBOTS_IGNORE_SETTINGS
		);

		$meta = $tsf->get_term_meta( $term_id );

		// NB: The indexes correspond to `autodescription-list[index]` field input names.
		$data = [
			'doctitle'    => [
				'value' => $meta['doctitle'],
			],
			'description' => [
				'value' => $meta['description'],
			],
			'canonical'   => [
				'value' => $meta['canonical'],
			],
			'noindex'     => [
				'value'    => $meta['noindex'],
				'isSelect' => true,
				'default'  => empty( $r_defaults['noindex'] ) ? 'index' : 'noindex',
			],
			'nofollow'    => [
				'value'    => $meta['nofollow'],
				'isSelect' => true,
				'default'  => empty( $r_defaults['nofollow'] ) ? 'follow' : 'nofollow',
			],
			'noarchive'   => [
				'value'    => $meta['noarchive'],
				'isSelect' => true,
				'default'  => empty( $r_defaults['noarchive'] ) ? 'archive' : 'noarchive',
			],
			'redirect'    => [
				'value' => $meta['redirect'],
			],
		];

		/**
		 * Tip: Prefix the indexes with your (plugin) name to prevent collisions.
		 * The index corresponds to field with the ID `autodescription-quick[%s]`, where %s is the index.
		 *
		 * @since 4.0.5
		 * @since 4.1.0 Now has `doctitle` and `description` indexes in its first parameter.
		 * @param array $data  The current data : {
		 *    string Index => @param array : {
		 *       @param mixed  $value    The current value.
		 *       @param bool   $isSelect Optional. Whether the field is a select field.
		 *       @param string $default  Optional. Only works when $isSelect is true. The default value to be set in select index 0.
		 *    }
		 * }
		 * @param array $query The query data. Contains 'id' and 'taxonomy'.
		 */
		$data = \apply_filters_ref_array( 'the_seo_framework_list_table_data', [ $data, $query ] );

		$container = '';

		$container .= sprintf(
			'<span class=hidden id=%s %s></span>',
			sprintf( 'tsfLeData[%s]', (int) $term_id ),
			// phpcs:ignore, WordPress.Security.EscapeOutput -- make_data_attributes escapes.
			HTML::make_data_attributes( [ 'le' => $data ] )
		);

		$term_prefix = $tsf->use_generated_archive_prefix( \get_taxonomy( $query['taxonomy'] ) )
			? $tsf->prepend_tax_label_prefix( '', $query['taxonomy'] )
			: '';

		$title_data = [
			'refTitleLocked'    => false,
			'defaultTitle'      => $tsf->get_filtered_raw_generated_title( $query ),
			'addAdditions'      => $tsf->use_title_branding( $query ),
			'additionValue'     => $tsf->s_title_raw( $tsf->get_blogname() ),
			'additionPlacement' => 'left' === $tsf->get_title_seplocation() ? 'before' : 'after',
			'termPrefix'        => $term_prefix,
		];
		$desc_data  = [
			'refDescriptionLocked' => false,
			'defaultDescription'   => $tsf->get_generated_description( $query ),
		];

		$container .= sprintf(
			'<span class=hidden id=%s %s></span>',
			sprintf( 'tsfLeTitleData[%s]', (int) $term_id ),
			// phpcs:ignore, WordPress.Security.EscapeOutput -- make_data_attributes escapes.
			HTML::make_data_attributes( [ 'leTitle' => $title_data ] )
		);
		$container .= sprintf(
			'<span class=hidden id=%s %s></span>',
			sprintf( 'tsfLeDescriptionData[%s]', (int) $term_id ),
			// phpcs:ignore, WordPress.Security.EscapeOutput -- make_data_attributes escapes.
			HTML::make_data_attributes( [ 'leDescription' => $desc_data ] )
		);

		if ( $this->doing_ajax )
			$container .= $this->get_ajax_dispatch_updated_event();

		return $string . $container;
	}
}
