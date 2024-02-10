<?php
/**
 * @package The_SEO_Framework\Classes\Admin\Settings\ListEdit
 * @subpackage The_SEO_Framework\Admin\Edit\List
 */

namespace The_SEO_Framework\Admin\Settings;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\{
	Admin,
	Admin\Settings\Layout\HTML,
	Data,
	Data\Filter\Sanitize,
	Helper\Query,
	Helper\Taxonomy,
	Helper\Template,
	Meta,
};

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
 * Prepares the List Edit view interface.
 *
 * @since 4.0.0
 * @since 5.0.0 Moved from `\The_SEO_Framework\Bridges`.
 * @access private
 */
final class ListEdit extends Admin\Lists\Table {

	/**
	 * @since 4.0.0
	 * @var string The column name.
	 */
	private $column_name = 'tsf-quick-edit';

	/**
	 * Setups class and prepares quick edit.
	 *
	 * @hook admin_init 10
	 * @since 5.0.0
	 */
	public static function init_quick_and_bulk_edit() {

		$instance = new self;

		\add_action( 'current_screen', [ $instance, 'prepare_edit_box' ] );
		\add_filter( 'hidden_columns', [ $instance, 'hide_quick_edit_column' ], 10, 1 );
	}

	/**
	 * Prepares the quick/bulk edit output.
	 *
	 * @hook current_screen 10
	 * @since 4.0.0
	 * @since 5.0.0 Renamed from `prepare_edit_box`.
	 *
	 * @param \WP_Screen|string $screen \WP_Screen
	 */
	public function prepare_edit_box( $screen ) {

		if ( empty( $screen->taxonomy ) ) {
			// WordPress doesn't support this feature yet for taxonomies.
			// Exclude it for when the time may come and faulty fields are displayed.
			// Mind the "2".
			\add_action( 'bulk_edit_custom_box', [ $this, 'display_bulk_edit_fields' ], 10, 2 );
		}

		\add_action( 'quick_edit_custom_box', [ $this, 'display_quick_edit_fields' ], 10, 3 );
	}

	/**
	 * Permanently hides quick/bulk-edit column.
	 *
	 * @hook hidden_columns 10
	 * @since 4.0.0
	 * @since 5.0.0 Renamed from `_hide_quick_edit_column`.
	 *
	 * @param array $hidden The existing hidden columns.
	 * @return array $columns the column data
	 */
	public function hide_quick_edit_column( $hidden ) {
		$hidden[] = $this->column_name;
		return $hidden;
	}

	/**
	 * Adds hidden column to access quick/bulk-edit.
	 * This column is a dummy, but it's required to display quick/bulk edit items.
	 *
	 * @hook manage_{$screen_id}_columns 10
	 * @hook manage_edit-{$taxonomy}_columns 1
	 * @since 4.0.0
	 * @since 5.0.0 Renamed from `_add_column`.
	 * @abstract
	 *
	 * @param array $columns The existing columns
	 * @return array $columns the column data
	 */
	public function add_column( $columns ) {
		// Don't set a title, otherwise it's displayed in the screen settings.
		$columns[ $this->column_name ] = '';
		return $columns;
	}

	/**
	 * Displays the SEO bulk edit fields.
	 *
	 * @hook bulk_edit_custom_box 10
	 * @since 4.0.0
	 * @since 5.0.0 Renamed from `_display_bulk_edit_fields`.
	 *
	 * @param string $column_name Name of the column to edit.
	 * @param string $post_type   The post type slug, or current screen name if this is a taxonomy list table.
	 * @param string $taxonomy    The taxonomy name, if any.
	 */
	public function display_bulk_edit_fields( $column_name, $post_type, $taxonomy = '' ) {

		if ( $this->column_name !== $column_name ) return;

		// phpcs:ignore, Generic.CodeAnalysis.EmptyStatement -- For the future, when WordPress Core decides.
		if ( $taxonomy ) {
			// Not yet.
		} else {
			Template::output_view( 'list/bulk-post', $post_type, $taxonomy );
		}
	}

	/**
	 * Displays the SEO quick edit fields.
	 *
	 * @hook quick_edit_custom_box 10
	 * @since 4.0.0
	 * @since 5.0.0 Renamed from `_display_quick_edit_fields`.
	 *
	 * @param string $column_name Name of the column to edit.
	 * @param string $post_type   The post type slug, or current screen name if this is a taxonomy list table.
	 * @param string $taxonomy    The taxonomy name, if any.
	 */
	public function display_quick_edit_fields( $column_name, $post_type, $taxonomy = '' ) {

		if ( $this->column_name !== $column_name ) return;

		if ( $taxonomy ) {
			Template::output_view( 'list/quick-term', $post_type, $taxonomy );
		} else {
			Template::output_view( 'list/quick-post', $post_type, $taxonomy );
		}
	}

	/**
	 * Outputs the quick edit data for posts and pages.
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

		if (
			   $this->column_name !== $column_name
			|| ! \current_user_can( 'edit_post', $post_id )
		) return;

		$generator_args = [ 'id' => $post_id ];

		$r_defaults = Meta\Robots::get_generated_meta(
			$generator_args,
			[ 'noindex', 'nofollow', 'noarchive' ],
			\The_SEO_Framework\ROBOTS_IGNORE_SETTINGS,
		);

		$meta = Data\Plugin\Post::get_meta( $post_id );

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
				// 'placeholder' => Meta\URI::get_generated_url( $generator_args ),
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
		 * @param array $generator_args The query data. Contains 'id' or 'taxonomy'.
		 */
		$data = \apply_filters( 'the_seo_framework_list_table_data', $data, $generator_args );

		printf(
			// '<span class=hidden id=%s data-le="%s"></span>',
			'<span class=hidden id=%s %s></span>',
			sprintf( 'tsfLeData[%s]', (int) $post_id ),
			// phpcs:ignore, WordPress.Security.EscapeOutput -- make_data_attributes escapes.
			HTML::make_data_attributes( [ 'le' => $data ] )
		);

		if ( Query::is_static_front_page( $generator_args['id'] ) ) {
			// When the homepage title is set, we can safely get the custom field.
			$_has_home_title     = (bool) \strlen( Data\Plugin::get_option( 'homepage_title' ) );
			$default_title       = $_has_home_title
				? Meta\Title::get_custom_title( $generator_args )
				: Meta\Title::get_bare_generated_title( $generator_args );
			$addition            = Meta\Title::get_addition_for_front_page();
			$seplocation         = Meta\Title::get_addition_location_for_front_page();
			$is_title_ref_locked = $_has_home_title;

			// When the homepage description is set, we can safely get the custom field.
			$_has_home_desc      = (bool) \strlen( Data\Plugin::get_option( 'homepage_description' ) );
			$default_description = $_has_home_desc
				? Meta\Description::get_custom_description( $generator_args )
				: Meta\Description::get_generated_description( $generator_args );
			$is_desc_ref_locked  = $_has_home_desc;
		} else {
			$default_title       = Meta\Title::get_bare_generated_title( $generator_args );
			$addition            = Meta\Title::get_addition();
			$seplocation         = Meta\Title::get_addition_location();
			$is_title_ref_locked = false;

			$default_description = Meta\Description::get_generated_description( $generator_args );
			$is_desc_ref_locked  = false;
		}

		$post_data  = [
			'isFront' => Query::is_static_front_page( $generator_args['id'] ),
		];
		$title_data = [
			'refTitleLocked'    => $is_title_ref_locked,
			'defaultTitle'      => \esc_html( $default_title ),
			'addAdditions'      => Meta\Title\Conditions::use_branding( $generator_args ),
			'additionValue'     => \esc_html( $addition ),
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
	 * @hook manage_{$taxonomy}_custom_column 1
	 * @since 4.0.0
	 * @since 4.2.0 Now properly populates use_generated_archive_prefix() with a \WP_Term object.
	 * @since 5.0.0 Renamed from `_output_column_contents_for_term`.
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
	public function output_column_contents_for_term( $string, $column_name, $term_id ) {

		if ( $this->column_name !== $column_name )          return $string;
		if ( ! \current_user_can( 'edit_term', $term_id ) ) return $string;

		$generator_args = [
			'id'  => $term_id,
			'tax' => $this->taxonomy,
		];

		$r_defaults = Meta\Robots::get_generated_meta(
			$generator_args,
			[ 'noindex', 'nofollow', 'noarchive' ],
			\The_SEO_Framework\ROBOTS_IGNORE_SETTINGS,
		);

		$meta = Data\Plugin\Term::get_meta( $term_id );

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
				// 'placeholder' => Meta\URI::get_generated_url( $generator_args ),
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
		 * @param array $generator_args The query data. Contains 'id' and 'tax'.
		 */
		$data = \apply_filters( 'the_seo_framework_list_table_data', $data, $generator_args );

		$container = '';

		$container .= sprintf(
			'<span class=hidden id=%s %s></span>',
			sprintf( 'tsfLeData[%s]', (int) $term_id ),
			// phpcs:ignore, WordPress.Security.EscapeOutput -- make_data_attributes escapes.
			HTML::make_data_attributes( [ 'le' => $data ] )
		);

		$term_prefix = Meta\Title\Conditions::use_generated_archive_prefix( \get_term( $generator_args['id'], $generator_args['tax'] ) )
			? sprintf(
				/* translators: %s: Taxonomy singular name. */
				\_x( '%s:', 'taxonomy term archive title prefix', 'default' ),
				Taxonomy::get_label( $generator_args['tax'] ),
			)
			: '';

		$title_data = [
			'refTitleLocked'    => false,
			'defaultTitle'      => \esc_html( Meta\Title::get_bare_generated_title( $generator_args ) ),
			'addAdditions'      => Meta\Title\Conditions::use_branding( $generator_args ),
			'additionValue'     => \esc_html( Meta\Title::get_addition() ),
			'additionPlacement' => 'left' === Meta\Title::get_addition_location() ? 'before' : 'after',
			'termPrefix'        => $term_prefix,
		];
		$desc_data  = [
			'refDescriptionLocked' => false,
			'defaultDescription'   => Meta\Description::get_generated_description( $generator_args ),
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
