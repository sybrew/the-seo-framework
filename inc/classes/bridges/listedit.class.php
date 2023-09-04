<?php
/**
 * @package The_SEO_Framework\Classes\Bridges\ListEdit
 * @subpackage The_SEO_Framework\Admin\Edit\List
 */

namespace The_SEO_Framework\Bridges;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Interpreters\HTML,
	\The_SEO_Framework\Data,
	\The_SEO_Framework\Meta\Factory;

use \The_SEO_Framework\Helper\{
	Query,
	Taxonomies,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

		if ( empty( $screen->taxonomy ) ) {
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
			\tsf()->get_view( 'list/bulk-post', get_defined_vars() );
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
			\tsf()->get_view( 'list/quick-term', get_defined_vars() );
		} else {
			\tsf()->get_view( 'list/quick-post', get_defined_vars() );
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

		if (
			   $this->column_name !== $column_name
			|| ! \current_user_can( 'edit_post', $post_id )
		) return;

		$tsf = \tsf();

		$_generator_args = [ 'id' => $post_id ];

		$r_defaults = Factory\Robots::generate_meta(
			$_generator_args,
			[ 'noindex', 'nofollow', 'noarchive' ],
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
				// TODO figure out how to make it work seamlessly with noindex.
				// 'placeholder' => Factory\URI::get_generated_canonical_url( $_generator_args ),
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
		 * @since 4.2.3 Now supports the `placeholder` index for $data.
		 * @param array $data            The current data : {
		 *    string Index => @param array : {
		 *       @param mixed  $value       The current value.
		 *       @param bool   $isSelect    Optional. Whether the field is a select field.
		 *       @param string $default     Optional. Only works when $isSelect is true. The default value to be set in select index 0.
		 *       @param string $placeholder Optional. Only works when $isSelect is false. Sets a placeholder for the input field.
		 *    }
		 * }
		 * @param array $_generator_args The query data. Contains 'id' or 'taxonomy'.
		 */
		$data = \apply_filters_ref_array( 'the_seo_framework_list_table_data', [ $data, $_generator_args ] );

		printf(
			// '<span class=hidden id=%s data-le="%s"></span>',
			'<span class=hidden id=%s %s></span>',
			sprintf( 'tsfLeData[%s]', (int) $post_id ),
			// phpcs:ignore, WordPress.Security.EscapeOutput -- make_data_attributes escapes.
			HTML::make_data_attributes( [ 'le' => $data ] )
		);

		if ( Query::is_static_frontpage( $_generator_args['id'] ) ) {
			// When the homepage title is set, we can safely get the custom field.
			$_has_home_title     = (bool) $tsf->escape_title( $tsf->get_option( 'homepage_title' ) );
			$default_title       = $_has_home_title
								 ? Factory\Title::get_custom_title( $_generator_args )
								 : Factory\Title::get_bare_generated_title( $_generator_args );
			$addition            = Factory\Title::get_addition_for_front_page();
			$seplocation         = Factory\Title::get_addition_location_for_front_page();
			$is_title_ref_locked = $_has_home_title;

			// When the homepage description is set, we can safely get the custom field.
			$_has_home_desc      = (bool) $tsf->escape_title( $tsf->get_option( 'homepage_description' ) );
			$default_description = $_has_home_desc
								 ? Factory\Description::get_custom_description( $_generator_args )
								 : Factory\Description::get_generated_description( $_generator_args );
			$is_desc_ref_locked  = $_has_home_desc;
		} else {
			$default_title       = Factory\Title::get_bare_generated_title( $_generator_args );
			$addition            = Data\Blog::get_public_blog_name();
			$seplocation         = Factory\Title::get_addition_location();
			$is_title_ref_locked = false;

			$default_description = Factory\Description::get_generated_description( $_generator_args );
			$is_desc_ref_locked  = false;
		}

		$post_data  = [
			'isFront' => Query::is_static_frontpage( $_generator_args['id'] ),
		];
		$title_data = [
			'refTitleLocked'    => $is_title_ref_locked,
			'defaultTitle'      => $tsf->s_title( $default_title ),
			'addAdditions'      => Factory\Title\Conditions::use_title_branding( $_generator_args ),
			'additionValue'     => $tsf->s_title( $addition ),
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
	 * @since 4.2.0 Now properly populates use_generated_archive_prefix() with a \WP_Term object.
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

		$tsf = \tsf();

		$_generator_args = [
			'id'       => $term_id,
			'taxonomy' => $this->taxonomy,
		];

		$r_defaults = Factory\Robots::generate_meta(
			$_generator_args,
			[ 'noindex', 'nofollow', 'noarchive' ],
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
				// TODO figure out how to make it work seamlessly with noindex.
				// 'placeholder' => Factory\URI::get_generated_canonical_url( $_generator_args ),
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
		 * @since 4.2.3 Now supports the `placeholder` index for $data.
		 * @param array $data            The current data : {
		 *    string Index => @param array : {
		 *       @param mixed  $value       The current value.
		 *       @param bool   $isSelect    Optional. Whether the field is a select field.
		 *       @param string $default     Optional. Only works when $isSelect is true. The default value to be set in select index 0.
		 *       @param string $placeholder Optional. Only works when $isSelect is false. Sets a placeholder for the input field.
		 *    }
		 * }
		 * @param array $_generator_args The query data. Contains 'id' and 'taxonomy'.
		 */
		$data = \apply_filters_ref_array( 'the_seo_framework_list_table_data', [ $data, $_generator_args ] );

		$container = '';

		$container .= sprintf(
			'<span class=hidden id=%s %s></span>',
			sprintf( 'tsfLeData[%s]', (int) $term_id ),
			// phpcs:ignore, WordPress.Security.EscapeOutput -- make_data_attributes escapes.
			HTML::make_data_attributes( [ 'le' => $data ] )
		);

		$term_prefix = Factory\Title\Conditions::use_generated_archive_prefix( \get_term( $_generator_args['id'], $_generator_args['taxonomy'] ) )
			? sprintf(
				/* translators: %s: Taxonomy singular name. */
				\_x( '%s:', 'taxonomy term archive title prefix', 'default' ),
				Taxonomies::get_taxonomy_label( $_generator_args['taxonomy'] )
			)
			: '';

		$title_data = [
			'refTitleLocked'    => false,
			'defaultTitle'      => $tsf->s_title( Factory\Title::get_bare_generated_title( $_generator_args ) ),
			'addAdditions'      => Factory\Title\Conditions::use_title_branding( $_generator_args ),
			'additionValue'     => $tsf->s_title( Data\Blog::get_public_blog_name() ),
			'additionPlacement' => 'left' === Factory\Title::get_addition_location() ? 'before' : 'after',
			'termPrefix'        => $term_prefix,
		];
		$desc_data  = [
			'refDescriptionLocked' => false,
			'defaultDescription'   => Factory\Description::get_generated_description( $_generator_args ),
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

		return "$string$container";
	}
}
