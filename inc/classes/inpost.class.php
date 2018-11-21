<?php
/**
 * @package The_SEO_Framework\Classes
 */
namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2018 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\Inpost
 *
 * Outputs Taxonomy, Post and Page meta boxes
 *
 * @since 2.8.0
 */
class Inpost extends Profile {

	/**
	 * Defines inpost nonce name.
	 *
	 * @since 2.7.0
	 * @since 3.2.0 Added '_nonce' suffix.
	 *
	 * @var string The nonce name.
	 */
	public $inpost_nonce_name = 'tsf_inpost_seo_settings_nonce';

	/**
	 * Defines inpost nonce field.
	 *
	 * @since 2.7.0
	 *
	 * @var string The nonce field.
	 */
	public $inpost_nonce_field = 'tsf_inpost_nonce';

	/**
	 * Outputs in-post flex navigational wrapper and its content.
	 *
	 * @since 2.9.0
	 * @since 3.0.0: Converted to view.
	 *
	 * @param string $id The Nav Tab ID
	 * @param array $tabs the tab content {
	 *    $tabs = tab ID key = array(
	 *       $tabs['name'] => tab name
	 *       $tabs['callback'] => string|array callback function
	 *       $tabs['dashicon'] => string Dashicon
	 *       $tabs['args'] => mixed optional callback function args
	 *    )
	 * }
	 * @param string $version the The SEO Framework version for debugging. May be emptied.
	 * @param bool $use_tabs Whether to output tabs, only works when $tabs count is greater than 1.
	 */
	public function inpost_flex_nav_tab_wrapper( $id, $tabs = [], $version = '2.3.6', $use_tabs = true ) {
		$this->get_view( 'inpost/wrap-nav', get_defined_vars() );
		$this->get_view( 'inpost/wrap-content', get_defined_vars() );
	}

	/**
	 * Adds the SEO meta box to post edit screens.
	 *
	 * @since 2.0.0
	 * @since 3.1.0 No longer checks for SEO plugin presence.
	 */
	public function add_inpost_seo_box_init() {

		/**
		 * @since 2.0.0
		 * @param bool $show_seobox Whether to show the SEO meta box.
		 */
		$show_seobox = (bool) \apply_filters( 'the_seo_framework_seobox_output', true );

		if ( $show_seobox )
			\add_action( 'add_meta_boxes', [ $this, 'add_inpost_seo_box' ], 10, 1 );
	}

	/**
	 * Adds SEO Meta boxes within Taxonomy screens.
	 *
	 * @since 2.1.8
	 * @since 2.6.0 Can no longer run outside of the term edit scope.
	 * @since 2.6.0 Can no longer run when another SEO plugin is active.
	 * @since 2.8.0 Added show_ui argument for public taxonomy detection.
	 * @since 3.1.0 1. No longer checks for SEO plugin presence.
	 *              2. Now tests for the current taxonomy.
	 *              3. Now only registers the action for the current taxonomy.
	 */
	public function add_taxonomy_seo_box_init() {

		if ( ! $this->is_term_edit() ) // implies "show_ui"
			return;

		if ( ! $this->taxonomy_supports_custom_seo( $this->get_current_taxonomy() ) )
			return;

		/**
		 * High priority, this box is seen right below the post/page edit screen.
		 * @since 2.6.0
		 * @param int $priority The metabox term priority.
		 */
		$priority = (int) \apply_filters( 'the_seo_framework_term_metabox_priority', 0 );

		//* Add taxonomy meta box
		\add_action( $this->get_current_taxonomy() . '_edit_form', [ $this, '_insert_seo_meta_box' ], $priority, 2 );
	}

	/**
	 * Adds SEO Meta boxes beneath every page/post edit screen.
	 *
	 * @param string $post_type The current Post Type.
	 *
	 * @since 2.0.0
	 */
	public function add_inpost_seo_box( $post_type ) {

		/**
		 * @uses $this->post_type_supports_custom_seo()
		 * @since 2.3.9
		 */
		if ( ! $this->post_type_supports_custom_seo( $post_type ) )
			return;

		$label = $this->get_post_type_label( $post_type );

		/**
		 * @since 2.6.0
		 * @NOTE warning: might cause CSS and JS conflicts.
		 * @TODO solve note.
		 * @param string $id The metabox class/ID.
		 */
		$id = (string) \apply_filters( 'the_seo_framework_metabox_id', 'tsf-inpost-box' );

		/**
		 * @since 2.9.0
		 * @param string $context, default 'normal'. Accepts 'normal', 'side' and 'advanced'.
		 */
		$context = (string) \apply_filters( 'the_seo_framework_metabox_context', 'normal' );

		/**
		 * High priority, this box is seen right below the post/page edit screen.
		 * @since 2.6.0
		 * @param string $default Accepts 'high', 'default', 'low'
		 */
		$priority = (string) \apply_filters( 'the_seo_framework_metabox_priority', 'high' );

		if ( $this->is_front_page_by_id( $this->get_the_real_ID() ) ) {
			if ( $this->can_access_settings() ) {
				$schema = \is_rtl() ? '%2$s - %1$s' : '%1$s - %2$s';
				$title = sprintf(
					$schema,
					\__( 'Homepage SEO Settings', 'autodescription' ),
					$this->make_info(
						\__( 'The SEO Settings take precedence over these settings.', 'autodescription' ),
						$this->seo_settings_page_url(),
						false
					)
				);
			} else {
				$title = \__( 'Homepage SEO Settings', 'autodescription' );
			}
		} else {
			/* translators: %s = Post Type */
			$title = sprintf( \__( '%s SEO Settings', 'autodescription' ), $label );
		}

		/* translators: %s = Post type name */
		\add_meta_box( $id, $title, [ $this, '_insert_seo_meta_box' ], $post_type, $context, $priority, [ $label, 'is_post_page' ] );
	}

	/**
	 * Determines post type and returns the SEO box for either a post or term.
	 *
	 * @since 3.1.0 : Introduced in 2.1.8, but the name changed.
	 * @access private
	 *
	 * @param mixed $object The page/post/taxonomy object.
	 * @param array $args   The page/post arguments or taxonomy slug.
	 */
	public function _insert_seo_meta_box( $object, $args ) {

		if ( isset( $args['args'] ) ) {
			$args_split = $args['args'];

			$page = $args_split[1];

			// Return $args as array on post/page
			if ( 'is_post_page' === $page ) {
				// Note: Passes through object.
				$this->output_seo_meta_box( $object, (array) $args );
			}
		} else {
			$this->output_seo_meta_box( $object, '' );
		}
	}

	/**
	 * Gets the SEO meta box, for either a post or term.
	 *
	 * @since 3.1.0 : Introduced in 2.0.0, but the name changed.
	 *
	 * @param mixed $object The page/post/taxonomy object.
	 * @param array $args   The page/post arguments or taxonomy slug.
	 */
	protected function output_seo_meta_box( $object, $args ) {

		//* Determines if it's inside a meta box or within a taxonomy page.
		$is_term = false;

		// Args are passed.
		if ( isset( $args['args'] ) ) {
			//* The post type callback arg (translated)
			$type = $args['args'][0];
			//* The kind of page we're on.
			$page = $args['args'][1];

			//* Only add nonce on post/page edit screen. Nonce for terms are handled in core.
			if ( 'is_post_page' === $page ) {
				\wp_nonce_field( $this->inpost_nonce_field, $this->inpost_nonce_name );
			} else {
				// This shouldn't happen.
				return;
			}
		} elseif ( isset( $object->taxonomy ) ) {
			//* Singular name.
			$type = $this->get_tax_type_label( $object->taxonomy );
			$is_term = true;
		}

		//* Echo output.
		if ( $is_term ) {
			$this->tt_inpost_box( $type, $object );
		} else {
			$this->singular_inpost_box( $type );
		}
	}

	/**
	 * Callback function for Taxonomy and Terms inpost box.
	 *
	 * @since 2.9.0
	 * @since 3.1.0 Now is protected.
	 *
	 * @param string   $type The TT type name.
	 * @param \WP_Term $object The TT object.
	 */
	protected function tt_inpost_box( $type, $object ) {
		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pre_tt_inpost_box' );
		$this->get_view( 'inpost/seo-settings-tt', get_defined_vars() );
		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pro_tt_inpost_box' );
	}

	/**
	 * Returns the inpost tabs.
	 *
	 * @since 3.1.0
	 *
	 * @param $type The post type label.
	 * @return array
	 */
	protected function get_inpost_tabs( $label ) {

		$default_tabs = [
			'general' => [
				'name'     => \__( 'General', 'autodescription' ),
				'callback' => [ $this, 'singular_inpost_box_general_tab' ],
				'dashicon' => 'admin-generic',
				'args'     => [ $label ],
			],
			'social' => [
				'name'     => \__( 'Social', 'autodescription' ),
				'callback' => [ $this, 'singular_inpost_box_social_tab' ],
				'dashicon' => 'share',
				'args'     => [ $label ],
			],
			'visibility' => [
				'name'     => \__( 'Visibility', 'autodescription' ),
				'callback' => [ $this, 'singular_inpost_box_visibility_tab' ],
				'dashicon' => 'visibility',
				'args'     => [ $label ],
			],
		];

		/**
		 * Allows for altering the inpost SEO settings metabox tabs.
		 *
		 * @since 2.9.0
		 *
		 * @param array  $default_tabs The default tabs.
		 * @param string $label The current post type display name, like "Post", "Page", "Product".
		 */
		$tabs = (array) \apply_filters( 'the_seo_framework_inpost_settings_tabs', $default_tabs, $label );

		return $tabs;
	}

	/**
	 * Outputs the singular inpost SEO box.
	 *
	 * Callback function for Post and Pages inpost metabox.
	 *
	 * @since 2.9.0
	 * @since 3.1.0 Now is protected.
	 *
	 * @param string $type    The post type name.
	 */
	protected function singular_inpost_box( $type ) {
		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pre_page_inpost_box' );
		$this->is_gutenberg_page()
			and $this->get_view( 'inpost/seo-settings-singular-gutenberg-data', get_defined_vars() );
		$this->get_view( 'inpost/seo-settings-singular', get_defined_vars() );
		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pro_page_inpost_box' );
	}

	/**
	 * Outputs the singular inpost SEO box general tab.
	 *
	 * Callback function for Post and Pages inpost metabox.
	 *
	 * @since 2.9.0
	 * @since 3.1.0 Now is protected.
	 *
	 * @param string $type The post type name.
	 */
	protected function singular_inpost_box_general_tab( $type ) {
		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pre_page_inpost_general_tab' );
		$this->get_view( 'inpost/seo-settings-singular', get_defined_vars(), 'general' );
		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pro_page_inpost_general_tab' );
	}

	/**
	 * Outputs the singular inpost SEO box visibility tab.
	 *
	 * Callback function for Post and Pages inpost metabox.
	 *
	 * @since 2.9.0
	 * @since 3.1.0 Now is protected.
	 *
	 * @param string $type The post type name.
	 */
	protected function singular_inpost_box_visibility_tab( $type ) {
		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pre_page_inpost_visibility_tab' );
		$this->get_view( 'inpost/seo-settings-singular', get_defined_vars(), 'visibility' );
		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pro_page_inpost_visibility_tab' );
	}

	/**
	 * Outputs the singular inpost SEO box social tab.
	 *
	 * Callback function for Post and Pages inpost metabox.
	 *
	 * @since 2.9.0
	 * @since 3.1.0 Now is protected.
	 *
	 * @param string $type The post type name.
	 */
	protected function singular_inpost_box_social_tab( $type ) {
		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pre_page_inpost_social_tab' );
		$this->get_view( 'inpost/seo-settings-singular', get_defined_vars(), 'social' );
		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pro_page_inpost_social_tab' );
	}
}
