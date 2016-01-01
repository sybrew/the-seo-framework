<?php
/**
 * The SEO Framework plugin
 * Copyright (C) 2015 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Class AutoDescription_AdminInit
 *
 * Initializes the plugin for the wp-admin screens.
 * Enqueues css and javascript.
 *
 * @since 2.1.6
 */
class AutoDescription_Admin_Init extends AutoDescription_Init {

	/**
	 * Constructor, load parent constructor
	 *
	 * Initalizes wp-admin functions
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'admin_init', array( $this, 'post_state' ) );
		add_action( 'init', array( $this, 'post_type_support' ) );

		/**
		 * @since 2.2.4
		 */
		add_filter( 'genesis_detect_seo_plugins', array( $this, 'no_more_genesis_seo' ), 10 );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ), 10, 1 );

	}

	/**
	 * Add post state on edit.php to the page or post that has been altered
	 *
	 * Called outside autodescription_run
	 *
	 * Applies `hmpl_ad_states` filters.
	 *
	 * @uses $this->add_post_state
	 *
	 * @since 2.1.0
	 */
	public function post_state() {

		/**
		 * New filter.
		 * @since 2.3.0
		 *
		 * Removed previous filter.
		 * @since 2.3.5
		 */
		$allow_states = (bool) apply_filters( 'the_seo_framework_allow_states', true );

		//* Prevent this function from running if this plugin is set to disabled.
		if ( ! $allow_states )
			return;

		add_filter( 'display_post_states', array( $this, 'add_post_state' ) );

	}

	/**
	 * Adds post states in post/page edit.php query
	 *
	 * @param array states 		the current post state
	 * @param string redirected	$this->get_custom_field( 'redirect' );
	 * @param string noindex	$this->get_custom_field( '_genesis_noindex' );
	 *
	 * @since 2.1.0
	 */
	public function add_post_state( $states = array() ) {
		global $post;

		if ( !empty( $post ) ) {
			$post_id = $post->ID;

			$searchexclude = $this->get_custom_field( 'exclude_local_search', $post_id ) ? true : false;

			if ( $searchexclude === true )
				$states[] = __( 'No Search', 'autodescription' );
		}

		return $states;
	}

	/**
	 * Removes the Genesis SEO meta boxes on the SEO Settings page
	 *
	 * @since 2.2.4
	 */
	public function no_more_genesis_seo() {
		$plugins = array(
				// Classes to detect.
				'classes' => array(
					'The_SEO_Framework_Load',
				),

				// Functions to detect.
				'functions' => array(),

				// Constants to detect.
				'constants' => array(),
			);

		return (array) $plugins;
	}

	/**
	 * Adds post type support
	 *
	 * Applies filters the_seo_framework_supported_post_types : The supported post types.
	 * @since 2.3.1
	 *
	 * @since 2.1.6
	 */
	public function post_type_support() {

		/**
		 * Finding the screens.
		 * @debug
		 * @since 2.4.1
		 */
		/*
		add_action( 'admin_footer', function() { global $current_screen; ?><div style="float:right;margin:3em;padding:1em;border:1px solid;background:#fff;color:#000;"><?php foreach( $current_screen as $screen ) echo "<p>$screen</p>"; ?></div><?php } );
		*/

		$args = array();

		/**
		 * Added product post type.
		 *
		 * @since 2.3.1
		 */
		$defaults = array(
			'post', 'page',
			'product',
			'forum', 'topic',
			'jetpack-testimonial', 'jetpack-portfolio'
		 );
		$post_types = (array) apply_filters( 'the_seo_framework_supported_post_types', $defaults, $args );

		$post_types = wp_parse_args( $args, $post_types );

		foreach ( $post_types as $type )
			add_post_type_support( $type, array( 'autodescription-meta' ) );

	}

	/**
	 * Helper function for allowed post/page screens where this plugin is active.
	 *
	 * @param array $screens The allowed screens
	 *
	 * @since 2.1.9
	 *
	 * Applies filters the_seo_framework_supported_screens : The supported administration
	 * screens where css and javascript files are loaded.
	 *
	 * @param array $args the custom supported screens.
	 *
	 * Added WooCommerce edit-product screens.
	 * @since 2.3.1
	 *
	 * Unused.
	 * @since 2.3.5
	 *
	 * @return array $screens
	 */
	protected function supported_screens( $args = array() ) {

		/**
		 * Instead of supporting page ID's, we support the Page base now.
		 *
		 * @since 2.3.3
		 */
		$defaults = array(
			'edit',
			'post',
			'edit-tags',
		);

		$screens = (array) apply_filters( 'the_seo_framework_supported_screens', $defaults, $args );
		$screens = wp_parse_args( $args, $screens );

		return $screens;
	}

	/**
	 * Enqueues scripts in the admin area on the supported screens.
	 *
	 * @since 2.3.3
	 *
	 * @param $hook the current page
	 */
	public function enqueue_admin_scripts( $hook ) {

		/**
		 * Check hook first.
		 * @since 2.3.9
		 */
		if ( isset( $hook ) && !empty( $hook ) && ( $hook == 'edit.php' || $hook == 'post.php' || $hook = 'edit-tags.php' ) ) {
			/**
			 * @uses $this->post_type_supports_custom_seo()
			 * @since 2.3.9
			 */
			if ( $this->post_type_supports_custom_seo() ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_css' ), 11 );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_javascript' ), 11 );
			}
		}

	}

	/**
	 * AutoDescription Javascript helper file
	 *
	 * @since 2.0.2
	 *
	 * @usedby add_inpost_seo_box
	 * @usedby enqueue_javascript
	 *
	 * @param string|array|object $hook the current page
	 * @param array|object $term the current term
	 *
	 * @todo Optimize this
	 */
	public function enqueue_admin_javascript( $hook ) {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script( 'autodescription-js', THE_SEO_FRAMEWORK_DIR_URL . "lib/js/autodescription{$suffix}.js", array( 'jquery' ), THE_SEO_FRAMEWORK_VERSION, true );

		/**
		 * i18n.
		 * @todo only enqueue this on pages that actually need this (edit.php)
		 */
		$blog_name = get_bloginfo( 'name', 'display' );
		$description = get_bloginfo( 'description', 'display' );

		$tagline = $this->get_option( 'homepage_tagline' ) ? true : false;
		$home_tagline = $this->get_option( 'homepage_title_tagline' );

		$separator = $this->get_separator( 'title', true );

		/**
		 * We're gaining UX in exchange for resource usage.
		 *
		 * Any way to cache this?
		 *
		 * @since 2.2.4
		 */
		if ( $hook ) {
			// We're somewhere within default WordPress pages.
			$post_id = $this->get_the_real_ID();

			if ( $this->is_static_frontpage( $post_id ) ) {
				$title = $blog_name;
				if ( $tagline ) {
					$additions = $home_tagline ? $home_tagline : $description;
				} else {
					$additions = '';
				}
			} else if ( $post_id ) {
				//* We're on post.php
				$title = $this->title( '', '', '', array( 'placeholder' => true, 'notagline' => true ) );
				$additions = $blog_name;
			} else {
				//* We're in terms and taxonomies
				// Can't fetch title.
				$title = '';
				$additions = $blog_name;
			}

		} else {
			// We're on our SEO settings pages.
			if ( 'page' === get_option( 'show_on_front' ) ) {
				// Home is a page.
				$inpost_title = $this->get_custom_field( '_genesis_title', get_option( 'page_on_front' ) );
			} else {
				// Home is a blog.
				$inpost_title = '';
			}
			$title = !empty( $inpost_title ) ? $inpost_title : $blog_name;
			$additions = $home_tagline ? $home_tagline : $description;
		}

		$strings = array(
			'saveAlert'		=> __( 'The changes you made will be lost if you navigate away from this page.', 'autodescription' ),
			'confirmReset'	=> __( 'Are you sure you want to reset all SEO settings to their defaults?', 'autodescription' ),
			'siteTitle' 	=> $title,
			'titleAdditions' => $additions,
			'blogDescription' => $description,
			'titleTagline' 	=> $tagline,
			'titleSeparator' => $separator,
		);

		wp_localize_script( 'autodescription-js', 'autodescriptionL10n', $strings );

	}

	/**
	 * CSS for the AutoDescription Bar
	 *
	 * @since 2.1.9
	 *
	 * @param $hook the current page
	 *
	 * @todo get_network_option
	 */
	public function enqueue_admin_css( $hook ) {

		$rtl = '';

		if ( is_rtl() )
			$rtl = '-rtl';

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style( 'autodescription-css', THE_SEO_FRAMEWORK_DIR_URL . "lib/css/autodescription{$rtl}{$suffix}.css", array(), THE_SEO_FRAMEWORK_VERSION, 'all' );

	}

	/**
	 * Mark up content with code tags.
	 *
	 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
	 *
	 * @since 2.0.0
	 *
	 * @param  string $content Content to be wrapped in code tags.
	 *
	 * @return string Content wrapped in code tags.
	 */
	public function code_wrap( $content ) {
		return '<code>' . esc_html( $content ) . '</code>';
	}

	/**
	 * Mark up content with code tags.
	 *
	 * Escapes no HTML.
	 *
	 * @since 2.2.2
	 *
	 * @param  string $content Content to be wrapped in code tags.
	 *
	 * @return string Content wrapped in code tags.
	 */
	public function code_wrap_noesc( $content ) {
		return '<code>' . $content . '</code>';
	}

	/**
	 * Return custom field post meta data.
	 *
	 * Return only the first value of custom field. Return false if field is
	 * blank or not set.
	 *
	 * @since 2.0.0
	 *
	 * @param string $field	Custom field key.
	 * @param int $post_id	The post ID
	 *
	 * @return string|boolean Return value or false on failure.
	 *
	 * @thanks StudioPress (http://www.studiopress.com/) for some code.
	 *
	 * @staticvar array $field_cache
	 * @since 2.2.5
	 */
	public function get_custom_field( $field, $post_id = null ) {

		//* No field has been provided.
		if ( empty( $field ) )
			return false;

		//* Setup cache.
		static $field_cache = array();

		//* Check field cache.
		if ( isset( $field_cache[$field][$post_id] ) )
			//* Field has been cached.
			return $field_cache[$field][$post_id];

		if ( null === $post_id || empty( $post_id ) )
			$post_id = $this->get_the_real_ID();

		if ( null === $post_id || empty( $post_id ) )
			return '';

		$custom_field = get_post_meta( $post_id, $field, true );

		// If custom field is empty, return null.
		if ( ! $custom_field )
			$field_cache[$field][$post_id] = '';

		//* Render custom field, slashes stripped, sanitized if string
		$field_cache[$field][$post_id] = is_array( $custom_field ) ? stripslashes_deep( $custom_field ) : stripslashes( wp_kses_decode_entities( $custom_field ) );

		return $field_cache[$field][$post_id];
	}

	/**
	 * Checks the screen hook.
	 *
	 * @since 2.2.2
	 *
	 * @return bool true if screen match.
	 */
	public function is_menu_page( $pagehook = '' ) {
		global $page_hook;

		if ( isset( $page_hook ) && $page_hook === $pagehook )
			return true;

			//* May be too early for $page_hook
		if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] === $pagehook )
			return true;

		return false;
	}

	/**
	 * Redirect the user to an admin page, and add query args to the URL string
	 * for alerts, etc.
	 *
	 * @since 2.2.2
	 *
	 * @param string $page			Menu slug.
	 * @param array  $query_args 	Optional. Associative array of query string arguments
	 * 								(key => value). Default is an empty array.
	 *
	 * @return null Return early if first argument is false.
	 */
	public function admin_redirect( $page, array $query_args = array() ) {

		if ( ! $page )
			return;

		$url = html_entity_decode( menu_page_url( $page, 0 ) );

		foreach ( (array) $query_args as $key => $value ) {
			if ( empty( $key ) && empty( $value ) ) {
				unset( $query_args[$key] );
			}
		}

		$url = add_query_arg( $query_args, $url );

		wp_redirect( esc_url_raw( $url ) );
		exit;

	}

	/**
	 * Google docs language determinator.
	 *
	 * @since 2.2.2
	 *
	 * @staticvar string $language
	 *
	 * @return string language code
	 */
	protected function google_language() {

		/**
		 * Cache value
		 * @since 2.2.4
		 */
		static $language = null;

		if ( isset( $language ) )
			return $language;

		//* Language shorttag to be used in Google help pages,
		$language = _x( 'en', 'e.g. en for English, nl for Dutch, fi for Finish, de for German', 'autodescription' );

		return $language;
	}

	/**
	 * Fetch Tax labels
	 *
	 * @param string $tax_type the Taxonomy type.
	 *
	 * @since 2.3.1
	 *
	 * @staticvar object $labels
	 *
	 * @return object|null with all the labels as member variables
	 */
	public function get_tax_labels( $tax_type ) {

		static $labels = null;

		if ( isset( $labels ) )
			return $labels;

		$tax_object = get_taxonomy( $tax_type );

		if ( is_object( $tax_object ) )
			return $labels = (object) $tax_object->labels;

		//* Nothing found.
		return null;
	}

	/**
	 * Echo debug values.
	 *
	 * @param mixed $values What to be output.
	 *
	 * @since 2.3.4
	 */
	public function echo_debug_information( $values ) {

		if ( defined( 'THE_SEO_FRAMEWORK_DEBUG' ) && THE_SEO_FRAMEWORK_DEBUG ) {
			echo "\r\n";

			if ( ! defined( 'THE_SEO_FRAMEWORK_DEBUG_HIDDEN' ) || defined( 'THE_SEO_FRAMEWORK_DEBUG_HIDDEN' ) && ! THE_SEO_FRAMEWORK_DEBUG_HIDDEN ) {
				echo "<br>\r\n";
				echo '<span class="code highlight">';
			}

			if ( !isset( $values ) ) {
				echo $this->debug_value_wrapper( "Debug message: Value isn't set." ) . "\r\n";
				return;
			}

			if ( is_object( $values ) ) {
				// Ugh.
				$values = (array) $values;

				if ( is_array( $values ) ) {
					foreach ( $values as $key => $value ) {
						if ( is_object( $value ) ) {
							foreach ( $values as $key => $value ) {
								$values = $value;
								break;
							}
						}
						break;
					}
				}
			}

			if ( is_array( $values ) ) {
				foreach ( $values as $key => $value ) {
					if ( empty( $value ) ) {
						echo $this->debug_key_wrapper( $key ) . ' => ';
						echo $this->debug_value_wrapper( 'Debug message: Empty value given.' );
						echo "\r\n";
					} else if ( is_string( $value ) || is_int( $value ) ) {
						echo $this->debug_key_wrapper( $key ) . ' => ' . $this->debug_value_wrapper( $value );
						echo "\r\n";
					} else if ( is_bool( $value ) ) {
						echo $this->debug_key_wrapper( $key ) . ' => ';
						echo $this->debug_value_wrapper( $value ? 'true' : 'false' );
						echo "\r\n";
					} else if ( is_array( $value ) ) {
						echo $this->debug_key_wrapper( $key ) . ' => ';
						echo "Array[\r\n";

						foreach ( $value as $k => $v ) {
							if ( empty( $v ) ) {
								echo $this->debug_key_wrapper( $k ) . ' => ';
								echo $this->debug_value_wrapper( 'Debug message: Empty value given.' );
								echo "\r\n";
							} else if ( is_string( $v ) || is_int( $v ) ) {
								echo $this->debug_key_wrapper( $k ) . ' => ' . $this->debug_value_wrapper( $v );
								echo "\r\n";
							} else if ( is_bool( $v ) ) {
								echo $this->debug_key_wrapper( $k ) . ' => ';
								echo $this->debug_value_wrapper( $v ? 'true' : 'false' );
								echo "\r\n";
							} else if ( is_array( $v ) ) {
								echo $this->debug_key_wrapper( $k ) . ' => ';
								echo $this->debug_value_wrapper( 'Debug message: Third dimensional array.' );
							} else {
								echo $this->debug_key_wrapper( $k ) . ' => ';
								echo $this->debug_value_wrapper( $v );
								echo "\r\n";
							}
						}
						echo "]";
					} else {
						echo $this->debug_key_wrapper( $key ) . ' => ';
						echo $this->debug_value_wrapper( $value );
						echo "\r\n";
					}
				}
			} else if ( is_string( $values ) || is_int( $value ) ) {
				echo $this->debug_value_wrapper( $values );
			} else if ( is_bool( $values ) ) {
				echo $this->debug_value_wrapper( $values ? 'true' : 'false' );
			} else if ( empty( $values ) ) {
				echo $this->debug_value_wrapper( 'Debug message: Empty value given.' );
			} else {
				echo $this->debug_value_wrapper( $values );
			}

			if ( ! defined( 'THE_SEO_FRAMEWORK_DEBUG_HIDDEN' ) || defined( 'THE_SEO_FRAMEWORK_DEBUG_HIDDEN' ) && ! THE_SEO_FRAMEWORK_DEBUG_HIDDEN ) {
				echo '</span>';
			}
			echo "\r\n";
		}

	}

	/**
	 * Wrap debug key in a colored span.
	 *
	 * @param string $key The debug key.
	 *
	 * @since 2.3.9
	 *
	 * @return string
	 */
	public function debug_key_wrapper( $key ) {
		if ( ! defined( 'THE_SEO_FRAMEWORK_DEBUG_HIDDEN' ) || defined( 'THE_SEO_FRAMEWORK_DEBUG_HIDDEN' ) && ! THE_SEO_FRAMEWORK_DEBUG_HIDDEN ) {
			return '<font color="chucknorris">' . esc_attr( (string) $key ) . '</font>';
		}
		return esc_attr( (string) $key );
	}

	/**
	 * Wrap debug value in a colored span.
	 *
	 * @param string $value The debug value.
	 *
	 * @since 2.3.9
	 *
	 * @return string
	 */
	public function debug_value_wrapper( $value ) {
		if ( ! defined( 'THE_SEO_FRAMEWORK_DEBUG_HIDDEN' ) || defined( 'THE_SEO_FRAMEWORK_DEBUG_HIDDEN' ) && ! THE_SEO_FRAMEWORK_DEBUG_HIDDEN ) {
			return '<span class="wp-ui-notification">' . esc_attr( (string) $value ) . '</span>';
		}
		return esc_attr( (string) $value );
	}

}
