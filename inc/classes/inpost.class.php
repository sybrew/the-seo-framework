<?php
/**
 * @package The_SEO_Framework\Classes
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
	 * Constructor, load parent constructor and sets up variables.
	 */
	protected function __construct() {
		parent::__construct();

		$this->inpost_nonce_name = 'tsf_inpost_seo_settings';
		$this->inpost_nonce_field = 'tsf_inpost_nonce';

		/**
		 * Applies filters bool|string the_seo_framework_inpost_seo_bar :
		 * Whether to output the SEO bar within the inpost SEO Settings metabox.
		 *
		 * @since 2.5.2
		 *
		 * @param $inpost_seo_bar : {
		 *    string 'above' : Outputs it above the Settings
		 *    string 'below' : Outputs it below the Settings
		 *    bool false     : No output.
		 * }
		 */
		$this->inpost_seo_bar = \apply_filters( 'the_seo_framework_inpost_seo_bar', false );
	}

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
	public function inpost_flex_nav_tab_wrapper( $id, $tabs = array(), $version = '2.3.6', $use_tabs = true ) {
		$this->get_view( 'inpost/wrap', get_defined_vars() );
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
		if ( $this->post_type_supports_custom_seo( $post_type ) ) :

			$post = \get_post_type_object( $post_type );
			$labels = is_object( $post ) && isset( $post->labels ) ? $post->labels : '';

			if ( $labels ) :
				//* Title and type are used interchangeably.
				$label = isset( $labels->singular_name ) ? $labels->singular_name : $labels->name;
				$args = array( $label, 'is_post_page' );

				/**
				 * Applies filters 'the_seo_framework_metabox_id' : string
				 *
				 * Alters The metabox class and ID.
				 *
				 * @since 2.6.0
				 * @NOTE warning: might cause CSS and JS conflicts.
				 * @TODO solve note.
				 * @priority medium 2.7.0
				 *
				 * @param string $id The metabox class/ID.
				 */
				$id = (string) \apply_filters( 'the_seo_framework_metabox_id', 'tsf-inpost-box' );

				/**
				 * Applies filters 'the_seo_framework_metabox_id' : string
				 *
				 * Alters the inpost metabox priority and class ID.
				 *
				 * @since 2.9.0
				 *
				 * @param string $context, default 'normal'. Accepts 'normal', 'side' and 'advanced'.
				 */
				$context = (string) \apply_filters( 'the_seo_framework_metabox_context', 'normal' );

				/**
				 * High priority, this box is seen right below the post/page edit screen.
				 *
				 * Applies filters 'the_seo_framework_metabox_priority' : string
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
				\add_meta_box( $id, $title, array( $this, 'pre_seo_box' ), $post_type, $context, $priority, $args );
			endif;
		endif;
	}

	/**
	 * Determines post type and outputs SEO box.
	 *
	 * @since 2.1.8
	 * @access private
	 *
	 * @param object $object the page/post/taxonomy object
	 * @param array $args the page/post arguments or taxonomy slug.
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
			return $this->inpost_seo_box( $object, '' );
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
	 * @param object $object The page/post/taxonomy object
	 * @param array  $args   The page/post arguments or taxonomy slug
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
	 * @since 2.9.0
	 * @access private
	 *
	 * @param string $type The TT type name.
	 * @param object $object The TT object.
	 */
	public function tt_inpost_box( $type, $object ) {
		\do_action( 'the_seo_framework_pre_tt_inpost_box' );
		$this->get_view( 'inpost/seo-settings-tt', get_defined_vars() );
		\do_action( 'the_seo_framework_pro_tt_inpost_box' );
	}

	/**
	 * Outputs the singular inpost SEO box.
	 *
	 * Callback function for Post and Pages inpost metabox.
	 *
	 * @since 2.9.0
	 * @access private
	 *
	 * @param string $type The post type name.
	 */
	public function singular_inpost_box( $type ) {
		\do_action( 'the_seo_framework_pre_page_inpost_box' );
		$this->get_view( 'inpost/seo-settings-singular', get_defined_vars() );
		\do_action( 'the_seo_framework_pro_page_inpost_box' );
	}

	/**
	 * Outputs the singular inpost SEO box general tab.
	 *
	 * Callback function for Post and Pages inpost metabox.
	 *
	 * @since 2.9.0
	 * @access private
	 *
	 * @param string $type The post type name.
	 */
	public function singular_inpost_box_general_tab( $type ) {
		\do_action( 'the_seo_framework_pre_page_inpost_general_tab' );
		$this->get_view( 'inpost/seo-settings-singular', get_defined_vars(), 'general' );
		\do_action( 'the_seo_framework_pro_page_inpost_general_tab' );
	}

	/**
	 * Outputs the singular inpost SEO box visibility tab.
	 *
	 * Callback function for Post and Pages inpost metabox.
	 *
	 * @since 2.9.0
	 * @access private
	 *
	 * @param string $type The post type name.
	 */
	public function singular_inpost_box_visibility_tab( $type ) {
		\do_action( 'the_seo_framework_pre_page_inpost_visibility_tab' );
		$this->get_view( 'inpost/seo-settings-singular', get_defined_vars(), 'visibility' );
		\do_action( 'the_seo_framework_pro_page_inpost_visibility_tab' );
	}

	/**
	 * Outputs the singular inpost SEO box social tab.
	 *
	 * Callback function for Post and Pages inpost metabox.
	 *
	 * @since 2.9.0
	 * @access private
	 *
	 * @param string $type The post type name.
	 */
	public function singular_inpost_box_social_tab( $type ) {
		\do_action( 'the_seo_framework_pre_page_inpost_social_tab' );
		$this->get_view( 'inpost/seo-settings-singular', get_defined_vars(), 'social' );
		\do_action( 'the_seo_framework_pro_page_inpost_social_tab' );
	}

	/**
	 * Outputs Primary Term nonce tempalte.
	 *
	 * @since 3.0.0
	 * @access private
	 */
	public function _include_primary_term_selector_template() {
		if ( $this->is_post_edit() )
			$this->get_view( 'templates/inpost/primary-term-selector', array() );
	}

	/**
	 * Fills in input variables by call for general tabs.
	 *
	 * Placeholder method that's used prior to upgrade merge 2.9 -> 3.0+.
	 * Do not use. It will take a little too much time to perfect this.
	 *
	 * @since 2.9.0
	 * @access private
	 * @ignore
	 * @todo Remove and refactor caller.
	 *
	 * @param string $tit_len_parsed. Passed by reference.
	 * @param string $doctitle_placeholder. Passed by reference.
	 * @param string $desc_len_parsed. Passed by reference.
	 * @param string $description_placeholder. Passed by reference.
	 */
	public function _get_inpost_general_tab_vars( &$tit_len_parsed, &$doctitle_placeholder, &$desc_len_parsed, &$description_placeholder ) {

		$post_id = $this->get_the_real_ID();
		$is_static_frontpage = $this->is_static_frontpage( $post_id );

		/**
		 * Generate static placeholders
		 */
		if ( $is_static_frontpage ) {
			//* Front page.
			$generated_doctitle_args = array(
				'page_on_front' => true,
				'placeholder' => true,
				'meta' => true,
				'get_custom_field' => false,
			);

			$generated_description_args = array(
				'id' => $post_id,
				'is_home' => true,
				'get_custom_field' => true,
			);
		} elseif ( $this->is_blog_page( $post_id ) ) {
			//* Page for posts.
			$generated_doctitle_args = array(
				'placeholder' => true,
				'meta' => true,
				'get_custom_field' => false,
			);

			$generated_description_args = array(
				'id' => $post_id,
				'page_for_posts' => true,
			);
		} else {
			$generated_doctitle_args = array(
				'placeholder' => true,
				'meta' => true,
				'get_custom_field' => false,
			);

			$generated_description_args = array(
				'id' => $post_id,
			);
		}
		$generated_doctitle = $this->title( '', '', '', $generated_doctitle_args );
		$generated_description = $this->generate_description_from_id( $generated_description_args );

		/**
		 * Start Title vars
		 */
		$title = $this->get_custom_field( '_genesis_title', $post_id );

		/**
		 * Special check for home page.
		 *
		 * @since 2.3.4
		 */
		if ( $is_static_frontpage ) {
			if ( $this->get_option( 'homepage_tagline' ) ) {
				$_blogdescription = $this->get_blogdescription();
				if ( $_blogdescription ) {
					$tit_len_pre = $title ? $title . ' | ' . $_blogdescription : $generated_doctitle;
				} else {
					$tit_len_pre = $title ? $title : $generated_doctitle;
				}
			} else {
				$tit_len_pre = $title ?: $generated_doctitle;
			}
		} else {
			/**
			 * Separator doesn't matter. Since html_entity_decode is used.
			 * Order doesn't matter either. Since it's just used for length calculation.
			 *
			 * @since 2.3.4
			 */
			if ( $this->add_title_additions() ) {
				$tit_len_pre = $title ? $title . ' | ' . $this->get_blogname() : $generated_doctitle;
			} else {
				$tit_len_pre = $title ?: $generated_doctitle;
			}
		}

		/**
		 * Start Description vars
		 */

		//* Fetch description from option.
		$description = $this->get_custom_field( '_genesis_description' );

		/**
		 * Calculate current description length
		 *
		 * Reworked.
		 * @since 2.3.4
		 */
		if ( $is_static_frontpage ) {
			//* The homepage description takes precedence.
			if ( $description ) {
				$desc_len_pre = $this->get_option( 'homepage_description' ) ?: $description;
			} else {
				$desc_len_pre = $this->get_option( 'homepage_description' ) ?: $generated_description;
			}
		} else {
			$desc_len_pre = $description ?: $generated_description;
		}

		/**
		 * Convert to what Google outputs.
		 *
		 * This will convert e.g. &raquo; to a single length character.
		 * @since 2.3.4
		 */
		$tit_len_parsed = html_entity_decode( $tit_len_pre );
		$desc_len_parsed = html_entity_decode( $desc_len_pre );

		/**
		 * Generate static placeholder for when title or description is emptied
		 *
		 * Now within aptly named vars.
		 * @since 2.3.4
		 */
		$doctitle_placeholder = $generated_doctitle;
		$description_placeholder = $generated_description;
	}
}
