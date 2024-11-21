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

		$meta        = Data\Plugin\Post::get_meta( $post_id );
		$is_homepage = Query::is_static_front_page( $generator_args['id'] );

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
				'default'  => empty( $r_defaults['noindex'] ) ? 'index' : 'noindex', // aka defaultUnprotected
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
				'value'       => $meta['redirect'],
				'placeholder' => $is_homepage ? \esc_url( Data\Plugin::get_option( 'homepage_redirect' ) ) : '',
			],
		];

		/**
		 * Tip: Prefix the indexes with your (plugin) name to prevent collisions.
		 * The index corresponds to field with the ID `autodescription-quick[%s]`, where %s is the index.
		 *
		 * Do not modify the structure or remove existing indexes!
		 *
		 * @since 4.0.5
		 * @since 4.1.0 Now has `doctitle` and `description` indexes in its first parameter.
		 * @since 4.2.3 Now supports the `placeholder` index for $data.
		 * @param array $data           {
		 *     The current data keyed by input field name.
		 *
		 *     @type mixed  $value       The current value.
		 *     @type bool   $isSelect    Optional. Whether the field is a select field.
		 *     @type string $default     Optional. Only works when $isSelect is true. The default value to be set in select index 0.
		 *     @type string $placeholder Optional. Only works when $isSelect is false. Sets a placeholder for the input field.
		 * }
		 * @param array $generator_args The query data. Contains 'id' or 'taxonomy'.
		 */
		$data = \apply_filters( 'the_seo_framework_list_table_data', $data, $generator_args );

		printf(
			// '<span class=hidden id=%s data-le="%s"></span>',
			'<span class=hidden id=%s %s></span>',
			\sprintf( 'tsfLeData[%s]', (int) $post_id ),
			// phpcs:ignore, WordPress.Security.EscapeOutput -- make_data_attributes escapes.
			HTML::make_data_attributes( [ 'le' => $data ] )
		);

		if ( $is_homepage ) {
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

			$_has_home_canonical     = (bool) Data\Plugin::get_option( 'homepage_canonical' );
			$default_canonical       = $_has_home_canonical
				? Meta\URI::get_custom_canonical_url( $generator_args )
				: Meta\URI::get_generated_url( $generator_args );
			$is_canonical_ref_locked = $_has_home_canonical;

			$permastruct               = Meta\URI\Utils::get_url_permastruct( $generator_args );
			$is_post_type_hierarchical = false; // Homepage cannot have a parent page.
		} else {
			static $memo = [];

			$memo['addition']    ??= Meta\Title::get_addition();
			$memo['seplocation'] ??= Meta\Title::get_addition_location();

			$default_title       = Meta\Title::get_bare_generated_title( $generator_args );
			$addition            = $memo['addition'];
			$seplocation         = $memo['seplocation'];
			$is_title_ref_locked = false;

			$default_description = Meta\Description::get_generated_description( $generator_args );
			$is_desc_ref_locked  = false;

			$is_canonical_ref_locked = false;
			$default_canonical       = Meta\URI::get_generated_url( $generator_args );

			$memo['post_type']                 ??= Query::get_admin_post_type();
			$memo['permastruct']               ??= Meta\URI\Utils::get_url_permastruct( $generator_args );
			$memo['is_post_type_hierarchical'] ??= \is_post_type_hierarchical( $memo['post_type'] );

			$post_type   = $memo['post_type'];
			$permastruct = $memo['permastruct'];

			$parent_post_slugs         = [];
			$is_post_type_hierarchical = $memo['is_post_type_hierarchical'];

			// Homepage doesn't care about its parent page.
			if ( $is_post_type_hierarchical && str_contains( $permastruct, '%postname%' ) ) {
				// self is filled by current post name.
				foreach ( Data\Post::get_post_parents( $post_id ) as $parent_post ) {
					// We write it like this instead of [ id => slug ] to prevent reordering numericals via JSON.parse.
					$parent_post_slugs[] = [
						'id'   => $parent_post->ID,
						'slug' => $parent_post->post_name,
					];
				}
			}

			// Only hierarchical taxonomies can be used in the URL.
			$memo['taxonomies']     ??= $post_type ? Taxonomy::get_hierarchical( 'names', $post_type ) : [];
			$parent_term_slugs_by_tax = [];

			// Yes, on its surface, this is a very expensive procedure.
			// However, WordPress needs to walk all the terms already to create the post links.
			// Hence, it ought to net to zero impact.
			foreach ( $memo['taxonomies'] as $taxonomy ) {
				if ( str_contains( $permastruct, "%$taxonomy%" ) ) {
					// Broken in Core. Skip writing cache. We may reach this line 200 times, but nobody should be using %post_tag% anyway.
					if ( 'post_tag' === $taxonomy ) continue;

					$parent_term_slugs_by_tax[ $taxonomy ] = [];
					// There's no need to test for hierarchy, because we want the full structure anyway (third parameter).
					foreach (
						Data\Term::get_term_parents(
							Data\Plugin\Post::get_primary_term_id( $post_id, $taxonomy ),
							$taxonomy,
							true,
						)
						as $parent_term
					) {
						// We write it like this instead of [ id => slug ] to prevent reordering numericals via JSON.parse.
						$parent_term_slugs_by_tax[ $taxonomy ][] = [
							'id'   => $parent_term->term_id,
							'slug' => $parent_term->slug,
						];
					}
				}
			}

			// Homepage cannot have an author.
			if ( str_contains( $permastruct, '%author%' ) ) {
				$author_id = Query::get_post_author_id( $post_id );

				if ( $author_id ) {
					$author_slugs = [
						[
							'id'   => $author_id,
							'slug' => Data\User::get_userdata( $author_id, 'user_nicename' ),
						],
					];
				}
			}
		}

		printf(
			// '<span class=hidden id=%s data-le-post-data="%s"></span>',
			'<span class=hidden id=%s %s></span>',
			\sprintf( 'tsfLePostData[%s]', (int) $post_id ),
			// phpcs:ignore, WordPress.Security.EscapeOutput -- make_data_attributes escapes.
			HTML::make_data_attributes( [
				'lePostData' => [
					'isFront' => Query::is_static_front_page( $generator_args['id'] ),
				],
			] ),
		);
		printf(
			// '<span class=hidden id=%s data-le-title="%s"></span>',
			'<span class=hidden id=%s %s></span>',
			\sprintf( 'tsfLeTitleData[%s]', (int) $post_id ),
			// phpcs:ignore, WordPress.Security.EscapeOutput -- make_data_attributes escapes.
			HTML::make_data_attributes( [
				'leTitle' => [
					'refTitleLocked'    => $is_title_ref_locked,
					'defaultTitle'      => \esc_html( $default_title ),
					'addAdditions'      => Meta\Title\Conditions::use_branding( $generator_args ),
					'additionValue'     => \esc_html( $addition ),
					'additionPlacement' => 'left' === $seplocation ? 'before' : 'after',
				],
			] ),
		);
		printf(
			// '<span class=hidden id=%s data-le-description="%s"></span>',
			'<span class=hidden id=%s %s></span>',
			\sprintf( 'tsfLeDescriptionData[%s]', (int) $post_id ),
			// phpcs:ignore, WordPress.Security.EscapeOutput -- make_data_attributes escapes.
			HTML::make_data_attributes( [
				'leDescription' => [
					'refDescriptionLocked' => $is_desc_ref_locked,
					'defaultDescription'   => $default_description,
				],
			] ),
		);
		printf(
			// '<span class=hidden id=%s data-le-canonical="%s"></span>',
			'<span class=hidden id=%s %s></span>',
			\sprintf( 'tsfLeCanonicalData[%s]', (int) $post_id ),
			// phpcs:ignore, WordPress.Security.EscapeOutput -- make_data_attributes escapes.
			HTML::make_data_attributes( [
				'leCanonical' => [
					'refCanonicalLocked' => $is_canonical_ref_locked,
					'defaultCanonical'   => \esc_url( $default_canonical ),
					'preferredScheme'    => Meta\URI\Utils::get_preferred_url_scheme(),
					'urlStructure'       => $permastruct,
					'parentPostSlugs'    => $parent_post_slugs ?? [],
					'parentTermSlugs'    => $parent_term_slugs_by_tax ?? [],
					'authorSlugs'        => $author_slugs ?? [],
					'isHierarchical'     => $is_post_type_hierarchical,
					// phpcs:ignore, WordPress.DateTime.RestrictedFunctions -- date() is used for URL generation. See `get_permalink()`.
					'publishDate'        => date( 'c', strtotime( \get_post( $post_id )->post_date ?? 'now' ) ),
				],
			] ),
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

		$taxonomy = $this->taxonomy;

		$generator_args = [
			'id'  => $term_id,
			'tax' => $taxonomy,
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
		 * @param array $data           {
		 *     The current data keyed by input field name.
		 *
		 *     @type mixed  $value       The current value.
		 *     @type bool   $isSelect    Optional. Whether the field is a select field.
		 *     @type string $default     Optional. Only works when $isSelect is true. The default value to be set in select index 0.
		 *     @type string $placeholder Optional. Only works when $isSelect is false. Sets a placeholder for the input field.
		 * }
		 * @param array $generator_args The query data. Contains 'id' and 'tax'.
		 */
		$data = \apply_filters( 'the_seo_framework_list_table_data', $data, $generator_args );

		static $memo = [];

		$memo['addition']    ??= Meta\Title::get_addition();
		$memo['seplocation'] ??= Meta\Title::get_addition_location();

		$addition    = $memo['addition'];
		$seplocation = $memo['seplocation'];

		$memo['tax_object']               ??= \get_taxonomy( $taxonomy );
		$memo['permastruct']              ??= Meta\URI\Utils::get_url_permastruct( $generator_args );
		$memo['is_taxonomy_hierarchical'] ??= $memo['tax_object']->hierarchical && $memo['tax_object']->rewrite['hierarchical'];

		$permastruct = $memo['permastruct'];

		$parent_term_slugs        = [];
		$is_taxonomy_hierarchical = $memo['is_taxonomy_hierarchical'];

		if ( $is_taxonomy_hierarchical && str_contains( $permastruct, "%$taxonomy%" ) ) {
			// self is filled by current term name.
			foreach ( Data\Term::get_term_parents( $term_id, $taxonomy ) as $parent_term ) {
				// We write it like this instead of [ id => slug ] to prevent reordering numericals via JSON.parse.
				$parent_term_slugs[] = [
					'id'   => $parent_term->term_id,
					'slug' => $parent_term->slug,
				];
			}
		}

		$container = '';

		$container .= \sprintf(
			'<span class=hidden id=%s %s></span>',
			\sprintf( 'tsfLeData[%s]', (int) $term_id ),
			// phpcs:ignore, WordPress.Security.EscapeOutput -- make_data_attributes escapes.
			HTML::make_data_attributes( [ 'le' => $data ] )
		);

		$term_prefix = Meta\Title\Conditions::use_generated_archive_prefix( \get_term( $generator_args['id'], $generator_args['tax'] ) )
			? \sprintf(
				/* translators: %s: Taxonomy singular name. */
				\_x( '%s:', 'taxonomy term archive title prefix', 'default' ),
				Taxonomy::get_label( $generator_args['tax'] ),
			)
			: '';

		$container .= \sprintf(
			'<span class=hidden id=%s %s></span>',
			\sprintf( 'tsfLeTitleData[%s]', (int) $term_id ),
			// phpcs:ignore, WordPress.Security.EscapeOutput -- make_data_attributes escapes.
			HTML::make_data_attributes( [
				'leTitle' => [
					'refTitleLocked'    => false,
					'defaultTitle'      => \esc_html( Meta\Title::get_bare_generated_title( $generator_args ) ),
					'addAdditions'      => Meta\Title\Conditions::use_branding( $generator_args ),
					'additionValue'     => \esc_html( $addition ),
					'additionPlacement' => 'left' === $seplocation ? 'before' : 'after',
					'termPrefix'        => \esc_html( $term_prefix ),
				],
			] ),
		);
		$container .= \sprintf(
			'<span class=hidden id=%s %s></span>',
			\sprintf( 'tsfLeDescriptionData[%s]', (int) $term_id ),
			// phpcs:ignore, WordPress.Security.EscapeOutput -- make_data_attributes escapes.
			HTML::make_data_attributes( [
				'leDescription' => [
					'refDescriptionLocked' => false,
					'defaultDescription'   => Meta\Description::get_generated_description( $generator_args ),
				],
			] ),
		);
		$container .= \sprintf(
			'<span class=hidden id=%s %s></span>',
			\sprintf( 'tsfLeCanonicalData[%s]', (int) $term_id ),
			// phpcs:ignore, WordPress.Security.EscapeOutput -- make_data_attributes escapes.
			HTML::make_data_attributes( [
				'leCanonical' => [
					'refCanonicalLocked' => false,
					'defaultCanonical'   => \esc_url( Meta\URI::get_generated_url( $generator_args ) ),
					'preferredScheme'    => Meta\URI\Utils::get_preferred_url_scheme(),
					'urlStructure'       => $permastruct,
					'parentTermSlugs'    => $parent_term_slugs,
					'isHierarchical'     => $is_taxonomy_hierarchical,
				],
			] ),
		);

		if ( $this->doing_ajax )
			$container .= $this->get_ajax_dispatch_updated_event();

		return "$string$container";
	}
}
