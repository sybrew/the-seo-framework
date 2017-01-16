<?php
/**
 * @package The_SEO_Framework\Classes
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
class Inpost extends Doing_It_Right {

	/**
	 * Add inpost SEO Bar through a filter.
	 *
	 * @since 2.5.2
	 *
	 * @var bool|string Whether and where to show the inpost SEO bar.
	 */
	protected $inpost_seo_bar = false;

	/**
	 * Defines inpost nonce name.
	 *
	 * @since 2.7.0
	 *
	 * @var string The nonce name.
	 */
	public $inpost_nonce_name;

	/**
	 * Defines inpost nonce field.
	 *
	 * @since 2.7.0
	 *
	 * @var string The nonce field.
	 */
	public $inpost_nonce_field;

	/**
	 * Constructor, load parent constructor
	 */
	protected function __construct() {
		parent::__construct();

		$this->inpost_nonce_name = 'the_seo_framework_inpost_seo_settings';
		$this->inpost_nonce_field = 'the_seo_framework_inpost';

		/**
		 * Applies filters bool|string the_seo_framework_inpost_seo_bar :
		 * Whether to output the SEO bar within the inpost SEO Settings metabox.
		 * @param 	: string 'above' Outputs it above the Settings
		 * 			: string 'below' Outputs it below the Settings
		 * 			: bool false No output.
		 * @since 2.5.2
		 */
		$this->inpost_seo_bar = \apply_filters( 'the_seo_framework_inpost_seo_bar', false );

	}

	/**
	 * Adds the SEO meta box to post edit screens.
	 *
	 * @since 2.0.0
	 */
	public function add_inpost_seo_box_init() {

		if ( $this->detect_seo_plugins() )
			return;

		/**
		 * Applies filters the_seo_framework_seobox_output : bool
		 * @since 2.0.0
		 */
		$show_seobox = (bool) \apply_filters( 'the_seo_framework_seobox_output', true );

		if ( $show_seobox )
			\add_action( 'add_meta_boxes', array( $this, 'add_inpost_seo_box' ), 10, 1 );

	}

	/**
	 * Adds SEO Meta boxes within Taxonomy screens.
	 *
	 * @since 2.1.8
	 * @since 2.6.0 Can no longer run outside of the term edit scope.
	 * @since 2.6.0 Can no longer run when another SEO plugin is active.
	 * @since 2.8.0 Added show_ui argument for public taxonomy detection.
	 */
	public function add_taxonomy_seo_box_init() {

		if ( $this->detect_seo_plugins() || false === $this->is_term_edit() )
			return;

		/**
		 * High priority, this box is seen right below the post/page edit screen.
		 * Applies filters 'the_seo_framework_term_metabox_priority' : int
		 *
		 * @since 2.6.0
		 */
		$priority = (int) \apply_filters( 'the_seo_framework_term_metabox_priority', 0 );

		//* Add taxonomy meta boxes
		foreach ( \get_taxonomies( array( 'public' => true, 'show_ui' => true ) ) as $tax_name ) {
			\add_action( $tax_name . '_edit_form', array( $this, 'pre_seo_box' ), $priority, 2 );
		}
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
		if ( $this->post_type_supports_custom_seo( $post_type ) ) {

			$post = \get_post_type_object( $post_type );

			if ( is_object( $post ) ) {
				$labels = isset( $post->labels ) ? $post->labels : '';

				if ( $labels ) {
					//* Title and type are used interchangeably.
					$title = isset( $labels->singular_name ) ? $labels->singular_name : $labels->name;
					$args = array( $title, 'is_post_page' );

					/**
					 * Applies filters the_seo_framework_metabox_id : string The metabox priority and class ID.
					 * @since 2.6.0
					 * @NOTE warning: might cause CSS and JS conflicts.
					 * @TODO solve note.
					 * @priority medium 2.7.0
					 */
					$id = (string) \apply_filters( 'the_seo_framework_metabox_id', 'tsf-inpost-box' );
					$context = 'normal';

					/**
					 * High priority, this box is seen right below the post/page edit screen.
					 * Applies filters 'the_seo_framework_metabox_priority' : string
					 * @since 2.6.0
					 * @param string $default Accepts 'high', 'default', 'low'
					 */
					$priority = (string) \apply_filters( 'the_seo_framework_metabox_priority', 'high' );

					\add_meta_box( $id, sprintf( \__( '%s SEO Settings', 'autodescription' ), $title ), array( $this, 'pre_seo_box' ), $post_type, $context, $priority, $args );
				}
			}
		}
	}

	/**
	 * Determines post type and outputs SEO box.
	 *
	 * @since 2.1.8
	 * @access private
	 *
	 * @param $object the page/post/taxonomy object
	 * @param $args the page/post arguments or taxonomy slug.
	 * @return string Inpost SEO box.
	 */
	public function pre_seo_box( $object, $args ) {

		if ( is_array( $args ) && isset( $args['args'] ) ) {
			$args_split = $args['args'];

			$page = $args_split[1];

			// Return $args as array on post/page
			if ( 'is_post_page' === $page ) {
				// Note: Passes through object.
				return $this->inpost_seo_box( $object, (array) $args );
			}
		} else {
			//* Empty the arguments, if any.
			return $this->inpost_seo_box( $object, $args = '' );
		}

		return '';
	}

	/**
	 * Callback for in-post SEO meta box.
	 *
	 * @since 2.0.0
	 * @access private
	 * @uses $this->get_custom_field() Get custom field value.
	 *
	 * @param object $object 	the page/post/taxonomy object
	 * @param array $args 		the page/post arguments or taxonomy slug
	 */
	public function inpost_seo_box( $object, $args ) {

		//* Determines if it's inside a meta box or within a taxonomy page.
		$is_term = false;

		// Args are passed.
		if ( is_array( $args ) && isset( $args['args'] ) ) {
			$args_split = $args['args'];

			//* The post type callback arg (translated)
			$type = $args_split[0];
			//* The kind of page we're on.
			$page = $args_split[1];

			//* Only add nonce on post/page edit screen. Nonce for terms are handled in core.
			if ( 'is_post_page' === $page ) {
				\wp_nonce_field( $this->inpost_nonce_field, $this->inpost_nonce_name );
			} else {
				// This shouldn't happen.
				return;
			}
		} elseif ( is_object( $object ) ) {

			//* Singular name.
			$type = $this->get_the_term_name( $object, true, false );

			//* Plural name.
			if ( empty( $type ) )
				$type = $this->get_the_term_name( $object, false, false );

			if ( empty( $type ) ) {
				// Fallback to Page as it is generic.
				$type = \__( 'Page', 'autodescription' );
			}

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
	 * @since 2.3.5
	 * @access private
	 *
	 * @param string $type The TT type name.
	 * @param object $object The TT object.
	 */
	public function tt_inpost_box( $type, $object ) {
		\do_action( 'the_seo_framework_pre_tt_inpost_box' );
		$this->get_view( 'inpost/seo-settings', get_defined_vars(), 'term' );
		\do_action( 'the_seo_framework_pro_tt_inpost_box' );
	}

	/**
	 * Callback function for Post and Pages inpost metabox.
	 *
	 * @since 2.3.5
	 * @access private
	 *
	 * @param string $type The post type name.
	 */
	public function singular_inpost_box( $type ) {
		\do_action( 'the_seo_framework_pre_page_inpost_box' );
		$this->get_view( 'inpost/seo-settings', get_defined_vars(), 'singular' );
		\do_action( 'the_seo_framework_pro_page_inpost_box' );
	}

	/**
	 * Returns social image uploader form button.
	 * Also registers additional i18n strings for JS.
	 *
	 * @since 2.8.0
	 * @todo optimize? Sanitation and translations are duplicated -> microseconds...
	 *
	 * @param string $input_id Required. The HTML input id to pass URL into.
	 * @return string The image uploader button.
	 */
	public function get_social_image_uploader_form( $input_id ) {

		if ( ! $input_id )
			return '';

		$content = sprintf( '<a href="%1$s" class="tsf-set-social-image button button-primary button-small" title="%2$s" id="%3$s-select" data-inputid="%3$s">%4$s</a>',
			\esc_url( \get_upload_iframe_src( 'image', $this->get_the_real_ID() ) ),
			\esc_attr__( 'Select social image', 'autodescription' ),
			\esc_attr( $input_id ),
			\esc_html__( 'Select Image', 'autodescription' )
		);

		$button_labels = array(
			'select' => \esc_attr__( 'Select Image', 'autodescription' ),
			'select_title' => \esc_attr__( 'Select social image', 'autodescription' ),
			'change' => \esc_attr__( 'Change Image', 'autodescription' ),
			'remove' => \esc_attr__( 'Remove Image', 'autodescription' ),
			'remove_title' => \esc_attr__( 'Remove selected social image', 'autodescription' ),
			'frame_title' => \esc_attr__( 'Select Social Image', 'autodescription' ),
			'frame_button' => \esc_attr__( 'Use this image', 'autodescription' ),
		);

		//* Already escaped. Turn off escaping.
		$this->additional_js_l10n( \esc_attr( $input_id ), $button_labels, false, false );

		return $content;
	}
}
