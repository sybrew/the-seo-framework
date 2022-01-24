<?php
/**
 * @package The_SEO_Framework\Classes\Bridges\ListTable
 */

namespace The_SEO_Framework\Bridges;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2022 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Prepares the list table action sequence.
 *
 * @TODO optimize: Every class extending this will invoke running the exact same
 * sequence, over and over again. This class was created because I noticed the
 * classes extending this all did the same. This isn't optimal, but it's a drop-in
 * solution to thin the codebase. Moreover, we load this+X classes, while those X
 * classes may not even do anything at all when this sequence fails to invoke the
 * abstractable methods. We're talking about only 0.01ms (fail) to 0.05ms (success)
 * of extra load time, every single admin request, however, because we memoize deep checks.
 * Totally negligible.
 *
 * @since 4.0.0
 * @access private
 * @abstract
 */
abstract class ListTable {

	/**
	 * @since 4.0.0
	 * @var string $post_type The current post type.
	 */
	protected $post_type = '';

	/**
	 * @since 4.0.0
	 * @var string $taxonomy The current taxonomy.
	 */
	protected $taxonomy = '';

	/**
	 * @since 4.0.0
	 * @var bool $doing_ajax Whether we're satisfying an AJAX request.
	 */
	protected $doing_ajax = false;

	/**
	 * Constructor, loads actions.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	public function __construct() {

		// Initialize columns.
		\add_action( 'current_screen', [ $this, '_prepare_columns' ] );

		// Ajax handlers for columns.
		\add_action( 'wp_ajax_add-tag', [ $this, '_prepare_columns_wp_ajax_add_tag' ], -1 );
		\add_action( 'wp_ajax_inline-save', [ $this, '_prepare_columns_wp_ajax_inline_save' ], -1 );
		\add_action( 'wp_ajax_inline-save-tax', [ $this, '_prepare_columns_wp_ajax_inline_save_tax' ], -1 );
	}

	/**
	 * Initializes columns for current screen.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @param \WP_Screen|string $screen \WP_Screen
	 */
	public function _prepare_columns( $screen ) {
		$this->init_columns( $screen );
	}

	/**
	 * Initializes columns for adding a tag or category.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	public function _prepare_columns_wp_ajax_add_tag() {

		if ( ! \check_ajax_referer( 'add-tag', '_wpnonce_add-tag', false )
		|| empty( $_POST['taxonomy'] ) )
			return;

		$taxonomy   = stripslashes( $_POST['taxonomy'] );
		$tax_object = $taxonomy ? \get_taxonomy( $taxonomy ) : false;

		if ( $tax_object && \current_user_can( $tax_object->cap->edit_terms ) )
			$this->init_columns_ajax();
	}

	/**
	 * Initializes columns for adding a tag or category.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	public function _prepare_columns_wp_ajax_inline_save() {

		if ( ! \check_ajax_referer( 'inlineeditnonce', '_inline_edit', false )
		|| empty( $_POST['post_ID'] )
		|| empty( $_POST['post_type'] ) )
			return;

		$post_type = stripslashes( $_POST['post_type'] );
		$pto       = $post_type ? \get_post_type_object( $post_type ) : false;

		// TODO shouldn't we just use `edit_post`? See _output_column_contents_for_post && get_post_type_capabilities
		if ( $pto && \current_user_can( "edit_{$pto->capability_type}", (int) $_POST['post_ID'] ) )
			$this->init_columns_ajax();
	}

	/**
	 * Initializes columns for adding a tag or category.
	 *
	 * @since 4.0.0
	 * @securitycheck 3.0.0 OK.
	 * @access private
	 */
	public function _prepare_columns_wp_ajax_inline_save_tax() {

		if ( ! \check_ajax_referer( 'taxinlineeditnonce', '_inline_edit', false )
		|| empty( $_POST['tax_ID'] ) )
			return;

		if ( \current_user_can( 'edit_term', (int) $_POST['tax_ID'] ) )
			$this->init_columns_ajax();
	}

	/**
	 * Initializes columns.
	 *
	 * @since 4.0.0
	 *
	 * @param \WP_Screen $screen The current screen.
	 */
	private function init_columns( $screen ) {

		if ( ! \tsf()->is_wp_lists_edit()
		|| empty( $screen->id ) )
			return;

		$post_type = $screen->post_type ?? '';
		$taxonomy  = $screen->taxonomy ?? '';

		if ( $taxonomy ) {
			if ( ! \tsf()->is_taxonomy_supported( $taxonomy ) )
				return;
		} else {
			if ( ! \tsf()->is_post_type_supported( $post_type ) )
				return;
		}

		$this->post_type = $post_type;
		$this->taxonomy  = $taxonomy;

		if ( $taxonomy )
			\add_filter( "manage_{$taxonomy}_custom_column", [ $this, '_output_column_contents_for_term' ], 1, 3 );

		\add_filter( "manage_{$screen->id}_columns", [ $this, '_add_column' ], 10, 1 );
		/**
		 * Always load pages and posts.
		 * Many CPT plugins rely on these.
		 */
		\add_action( 'manage_posts_custom_column', [ $this, '_output_column_contents_for_post' ], 1, 2 );
		\add_action( 'manage_pages_custom_column', [ $this, '_output_column_contents_for_post' ], 1, 2 );
	}

	/**
	 * Initializes columns for AJAX.
	 *
	 * @since 4.0.0
	 * @see callers for CSRF protection.
	 *    `_prepare_columns_wp_ajax_add_tag()`
	 *    `_prepare_columns_wp_ajax_inline_save()`
	 *    `_prepare_columns_wp_ajax_inline_save_tax()`
	 */
	private function init_columns_ajax() {
		// phpcs:disable, WordPress.Security.NonceVerification -- _prepare_columns_wp_ajax_* verifies this.

		$taxonomy  = isset( $_POST['taxonomy'] ) ? stripslashes( $_POST['taxonomy'] ) : '';
		$post_type = isset( $_POST['post_type'] ) ? stripslashes( $_POST['post_type'] ) : '';

		//? /wp-admin/js/inline-edit-tax.js doesn't send post_type, instead, it sends tax_type, which is the same.
		$post_type = $post_type
				?: ( isset( $_POST['tax_type'] ) ? stripslashes( $_POST['tax_type'] ) : '' );

		if ( $taxonomy ) {
			if ( ! \tsf()->is_taxonomy_supported( $taxonomy ) )
				return;
		} else {
			if ( ! \tsf()->is_post_type_supported( $post_type ) )
				return;
		}

		$this->doing_ajax = true;
		$this->post_type  = $post_type;
		$this->taxonomy   = $taxonomy;

		$screen_id = isset( $_POST['screen'] ) ? stripslashes( $_POST['screen'] ) : '';

		// Not elseif; either request.
		if ( $taxonomy )
			\add_filter( "manage_{$taxonomy}_custom_column", [ $this, '_output_column_contents_for_term' ], 1, 3 );

		if ( $screen_id ) {
			// Everything but inline-save-tax action.
			\add_filter( "manage_{$screen_id}_columns", [ $this, '_add_column' ], 10, 1 );

			/**
			 * Always load pages and posts.
			 * Many CPT plugins rely on these.
			 */
			\add_action( 'manage_posts_custom_column', [ $this, '_output_column_contents_for_post' ], 1, 2 );
			\add_action( 'manage_pages_custom_column', [ $this, '_output_column_contents_for_post' ], 1, 2 );
		} elseif ( $taxonomy ) {
			/**
			 * Action "inline-save-tax" does not POST 'screen'.
			 *
			 * @see WP Core wp_ajax_inline_save_tax():
			 *    `_get_list_table( 'WP_Terms_List_Table', array( 'screen' => "edit-$taxonomy" ) );`
			 */
			\add_filter( "manage_edit-{$taxonomy}_columns", [ $this, '_add_column' ], 1, 1 );
		}
		// phpcs:enable, WordPress.Security.NonceVerification
	}

	/**
	 * Returns a JS script that triggers list updates.
	 * This is a necessity as WordPress doesn't trigger actions on update.
	 *
	 * TODO bind to WordPress's function instead? Didn't we already do that?!
	 * See: `tsfLe._hijackListeners()`; Although, that doesn't cover "adding" new items.
	 *
	 * @since 4.0.5
	 * @NOTE: Do not bind to `tsfLeDispatchUpdate`, it's a private action.
	 *        Bind to `tsfLeUpdated` instead, which is debounced and should only run once.
	 *
	 * @return string The triggering script.
	 */
	protected function get_ajax_dispatch_updated_event() {
		return "<script>'use strict';(()=>document.dispatchEvent(new Event('tsfLeDispatchUpdate')))();</script>";
	}

	/**
	 * Add column on edit(-tags).php
	 *
	 * @since 4.0.0
	 * @access private
	 * @abstract
	 *
	 * @param array $columns The existing columns.
	 * @return array $columns The adjusted columns.
	 */
	abstract public function _add_column( $columns );

	/**
	 * Outputs the contents for a column on post overview screens.
	 *
	 * @since 4.0.0
	 * @access private
	 * @abstract
	 *
	 * @param string $column_name The name of the column to display.
	 * @param int    $post_id     The current post ID.
	 */
	abstract public function _output_column_contents_for_post( $column_name, $post_id );

	/**
	 * Returns the contents for a column on tax screens.
	 *
	 * @since 4.0.0
	 * @access private
	 * @abstract
	 * @NOTE Unlike _output_column_contents_for_post(), this is a filter callback.
	 *       Because of this, the first parameter is a useless string, which must be extended.
	 *       Discrepancy: https://core.trac.wordpress.org/ticket/33521
	 *       With this, the proper function name should be "_get..." or "_add...", but not "_output.."
	 *
	 * @param string $string      Blank string.
	 * @param string $column_name Name of the column.
	 * @param string $term_id     Term ID.
	 * @return string The column contents.
	 */
	abstract public function _output_column_contents_for_term( $string, $column_name, $term_id );
}
