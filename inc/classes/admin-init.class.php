<?php
/**
 * @package The_SEO_Framework\Classes
 */
namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\Admin_Init
 *
 * Initializes the plugin for the wp-admin screens.
 * Enqueues CSS and Javascript.
 *
 * @since 2.8.0
 */
class Admin_Init extends Init {

	/**
	 * Initializes SEO Bar.
	 *
	 * @since 3.3.0
	 */
	public function init_seo_bar() {

		// Initialize table output.
		if ( $this->get_option( 'display_seo_bar_tables' ) ) {
			$seobar = new Bridges\SeoBar;
			$seobar->prepare_seo_bar_tables();
		}
	}

	/**
	 * Add post state on edit.php to the page or post that has been altered.
	 *
	 * @since 2.1.0
	 * @uses $this->add_post_state
	 */
	public function post_state() {

		//* Only load on singular pages.
		if ( $this->is_singular() ) {
			/**
			 * @since 2.1.0
			 * @param bool $allow_states Whether to allow TSF post states output.
			 */
			$allow_states = (bool) \apply_filters( 'the_seo_framework_allow_states', true );

			if ( $allow_states )
				\add_filter( 'display_post_states', [ $this, 'add_post_state' ], 10, 2 );
		}
	}

	/**
	 * Adds post states in post/page edit.php query
	 *
	 * @since 2.1.0
	 * @since 2.9.4 Now listens to `alter_search_query` and `alter_archive_query` options.
	 *
	 * @param array    $states The current post states array
	 * @param \WP_Post $post The Post Object.
	 * @return array Adjusted $states
	 */
	public function add_post_state( $states = [], $post ) {

		$post_id = isset( $post->ID ) ? $post->ID : false;

		if ( $post_id ) {
			$search_exclude  = $this->get_option( 'alter_search_query' ) && $this->get_custom_field( 'exclude_local_search', $post_id );
			$archive_exclude = $this->get_option( 'alter_archive_query' ) && $this->get_custom_field( 'exclude_from_archive', $post_id );

			if ( $search_exclude )
				$states[] = \esc_html__( 'No Search', 'autodescription' );

			if ( $archive_exclude )
				$states[] = \esc_html__( 'No Archive', 'autodescription' );
		}

		return $states;
	}

	/**
	 * Prepares scripts in the admin area.
	 *
	 * @since 3.1.0
	 * @since 3.3.0 Now discerns autoloading between taxonomies and singular types.
	 * @access private
	 *
	 * @param string|null $hook The current page hook.
	 */
	public function _init_admin_scripts( $hook = null ) {

		$autoenqueue = false;

		if ( $this->is_seo_settings_page() ) {
			$autoenqueue = true;
		} elseif ( $hook ) {

			$enqueue_hooks = [];

			if ( ( $this->is_archive_admin() && $this->taxonomy_supports_custom_seo() )
			|| ( $this->is_singular_admin() && $this->post_type_supports_custom_seo() )
			) {
				$enqueue_hooks = [
					'edit.php',
					'post.php',
					'post-new.php',
					'edit-tags.php',
					'term.php',
				];

				if ( ! $this->get_option( 'display_seo_bar_tables' ) ) {
					$enqueue_hooks = array_diff( $enqueue_hooks, [
						'edit.php',
						'edit-tags.php',
					] );
				}
			}

			if ( in_array( $hook, $enqueue_hooks, true ) )
				$autoenqueue = true;
		}

		$autoenqueue and $this->init_admin_scripts();
	}

	/**
	 * Registers admin scripts and styles.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 First parameter is now deprecated.
	 * @since 3.3.0 First parameter is now removed.
	 *
	 * @return void Early if already enqueued.
	 */
	public function init_admin_scripts() {

		if ( _has_run( __METHOD__ ) ) return;

		//! PHP 5.4 compat: put in var.
		$loader = $this->ScriptsLoader();
		$loader::_init();
	}

	/**
	 * Returns the static scripts class object.
	 *
	 * The first letter of the method is capitalized, to indicate it's a class caller.
	 *
	 * @since 3.3.0
	 * @bridge
	 *
	 * @return string The scripts loader class name.
	 */
	public function ScriptsLoader() {
		return Bridges\Scripts::class;
	}

	/**
	 * Returns the static scripts class object.
	 *
	 * The first letter of the method is capitalized, to indicate it's a class caller.
	 *
	 * @since 3.1.0
	 * @builder
	 *
	 * @return string The scripts class name.
	 */
	public function Scripts() {
		return Builders\Scripts::class;
	}

	/**
	 * Returns the title and description input guideline table, for
	 * (Google) search, Open Graph, and Twitter.
	 *
	 * @since 3.1.0
	 * @staticvar array $guidelines
	 * @TODO Consider splitting up search into Google, Bing, etc., as we might
	 *       want users to set their preferred search engine. Now, these engines
	 *       are barely any different.
	 * TODO move this to another object?
	 *
	 * @return array
	 */
	public function get_input_guidelines() {
		static $guidelines;
		/**
		 * @since 3.1.0
		 * @param array $guidelines The title and description guidelines.
		 *              Don't alter the format. Only change the numeric values.
		 */
		return isset( $guidelines ) ? $guidelines : $guidelines = (array) \apply_filters(
			'the_seo_framework_input_guidelines',
			[
				'title' => [
					'search' => [
						'chars'  => [
							'lower'     => 25,
							'goodLower' => 35,
							'goodUpper' => 65,
							'upper'     => 75,
						],
						'pixels' => [
							'lower'     => 200,
							'goodLower' => 280,
							'goodUpper' => 520,
							'upper'     => 600,
						],
					],
					'opengraph' => [
						'chars'  => [
							'lower'     => 15,
							'goodLower' => 25,
							'goodUpper' => 88,
							'upper'     => 100,
						],
						'pixels' => [],
					],
					'twitter' => [
						'chars'  => [
							'lower'     => 15,
							'goodLower' => 25,
							'goodUpper' => 69,
							'upper'     => 70,
						],
						'pixels' => [],
					],
				],
				'description' => [
					'search' => [
						'chars'  => [
							'lower'     => 45,
							'goodLower' => 80,
							'goodUpper' => 160,
							'upper'     => 320,
						],
						'pixels' => [
							'lower'     => 256,
							'goodLower' => 455,
							'goodUpper' => 910,
							'upper'     => 1820,
						],
					],
					'opengraph' => [
						'chars'  => [
							'lower'     => 45,
							'goodLower' => 80,
							'goodUpper' => 200,
							'upper'     => 300,
						],
						'pixels' => [],
					],
					'twitter' => [
						'chars'  => [
							'lower'     => 45,
							'goodLower' => 80,
							'goodUpper' => 200,
							'upper'     => 200,
						],
						'pixels' => [],
					],
				],
			]
		);
	}

	/**
	 * Returns the title and description input guideline explanatory table.
	 *
	 * Already attribute-escaped.
	 *
	 * @since 3.1.0
	 *
	 * @return array
	 */
	public function get_input_guidelines_i18n() {
		return [
			'long' => [
				'empty'       => \esc_attr__( "There's no content.", 'autodescription' ),
				'farTooShort' => \esc_attr__( "It's too short and it should have more information.", 'autodescription' ),
				'tooShort'    => \esc_attr__( "It's short and it could have more information.", 'autodescription' ),
				'tooLong'     => \esc_attr__( "It's long and it might get truncated in search.", 'autodescription' ),
				'farTooLong'  => \esc_attr__( "It's too long and it will get truncated in search.", 'autodescription' ),
				'good'        => \esc_attr__( 'Length is good.', 'autodescription' ),
			],
			'short' => [
				'empty'       => \esc_attr_x( 'Empty', 'The string is empty', 'autodescription' ),
				'farTooShort' => \esc_attr__( 'Far too short', 'autodescription' ),
				'tooShort'    => \esc_attr__( 'Too short', 'autodescription' ),
				'tooLong'     => \esc_attr__( 'Too long', 'autodescription' ),
				'farTooLong'  => \esc_attr__( 'Far too long', 'autodescription' ),
				'good'        => \esc_attr__( 'Good', 'autodescription' ),
			],
		];
	}

	/**
	 * Checks ajax referred set by set_js_nonces based on capability.
	 *
	 * Performs die() on failure.
	 *
	 * @since 3.1.0 : Introduced in 2.9.0, but the name changed.
	 * @access private
	 *         It uses an internally and manually created prefix.
	 * @uses WP Core check_ajax_referer()
	 * @see @link https://developer.wordpress.org/reference/functions/check_ajax_referer/
	 *
	 * @return false|int False if the nonce is invalid, 1 if the nonce is valid
	 *                   and generated between 0-12 hours ago, 2 if the nonce is
	 *                   valid and generated between 12-24 hours ago.
	 */
	public function _check_tsf_ajax_referer( $capability ) {
		return \check_ajax_referer( 'tsf-ajax-' . $capability, 'nonce', true );
	}

	/**
	 * Adds removable query args to WordPress query arg handler.
	 *
	 * @since 2.8.0
	 *
	 * @param array $removable_query_args The removable query arguments.
	 * @return array The adjusted removable query args.
	 */
	public function add_removable_query_args( $removable_query_args = [] ) {

		if ( is_array( $removable_query_args ) ) {
			$removable_query_args[] = 'tsf-settings-reset';
			$removable_query_args[] = 'tsf-settings-updated';
		}

		return $removable_query_args;
	}

	/**
	 * Redirect the user to an admin page, and add query args to the URL string
	 * for alerts, etc.
	 *
	 * @since 2.2.2
	 * @since 2.9.2 : Added user-friendly exception handling.
	 * @since 2.9.3 : 1. Query arguments work again (regression 2.9.2).
	 *                2. Now only accepts http and https protocols.
	 *
	 * @param string $page Menu slug. This slug must exist, or the redirect will loop back to the current page.
	 * @param array  $query_args Optional. Associative array of query string arguments
	 *               (key => value). Default is an empty array.
	 * @return null Return early if first argument is false.
	 */
	public function admin_redirect( $page, array $query_args = [] ) {

		if ( empty( $page ) )
			return;

		$url = html_entity_decode( \menu_page_url( $page, false ) ); // This can be empty... TODO test?

		foreach ( $query_args as $key => $value ) {
			if ( empty( $key ) || empty( $value ) )
				unset( $query_args[ $key ] );
		}

		$target = \add_query_arg( $query_args, $url );
		$target = \esc_url_raw( $target, [ 'http', 'https' ] );

		//* Predict white screen:
		$headers_sent = headers_sent();

		/**
		 * Dev debug:
		 * 1. Change 302 to 500 if you wish to test headers.
		 * 2. Also force handle_admin_redirect_error() to run.
		 */
		\wp_safe_redirect( $target, 302 );

		//* White screen of death for non-debugging users. Let's make it friendlier.
		if ( $headers_sent ) {
			$this->handle_admin_redirect_error( $target );
		}

		exit;
	}

	/**
	 * Provides an accessible error for when redirecting fails.
	 *
	 * @since 2.9.2
	 * @see https://developer.wordpress.org/reference/functions/wp_redirect/
	 *
	 * @param string $target The redirect target location. Should be escaped.
	 * @return void
	 */
	protected function handle_admin_redirect_error( $target = '' ) {

		if ( empty( $target ) )
			return;

		$headers_list = headers_list();
		$location     = sprintf( 'Location: %s', \wp_sanitize_redirect( $target ) );

		//* Test if WordPress' redirect header is sent. Bail if true.
		if ( in_array( $location, $headers_list, true ) )
			return;

		printf( '<p><strong>%s</strong></p>',
			$this->convert_markdown(
				sprintf(
					/* translators: %s = Redirect URL markdown */
					\esc_html__( 'There has been an error redirecting. Refresh the page or follow [this link](%s).', 'autodescription' ),
					$target
				),
				[ 'a' ],
				[ 'a_internal' => true ]
			)
		);
	}

	/**
	 * Handles counter option update on AJAX request for users that can edit posts.
	 *
	 * @since 3.1.0 : Introduced in 2.6.0, but the name changed.
	 * @securitycheck 3.0.0 OK.
	 * @access private
	 */
	public function _wp_ajax_update_counter_type() {

		if ( $this->is_admin() && \wp_doing_ajax() ) :
			$this->_check_tsf_ajax_referer( 'edit_posts' );

			//* Remove output buffer.
			$this->clean_response_header();

			//* If current user isn't allowed to edit posts, don't do anything and kill PHP.
			if ( ! \current_user_can( 'edit_posts' ) ) {
				//* Encode and echo results. Requires JSON decode within JS.
				\wp_send_json( [
					'type'  => 'failure',
					'value' => '',
				] );
			}

			/**
			 * Count up, reset to 0 if needed. We have 4 options: 0, 1, 2, 3
			 * $_POST['val'] already contains updated number.
			 */
			$value = isset( $_POST['val'] ) ? intval( $_POST['val'] ) : $this->get_user_option( 0, 'counter_type', 3 ) + 1; // input var ok
			$value = \absint( $value );

			if ( $value > 3 )
				$value = 0;

			//* Update the option and get results of action.
			$type = $this->update_user_option( 0, 'counter_type', $value ) ? 'success' : 'error';

			$results = [
				'type'  => $type,
				'value' => $value,
			];

			//* Encode and echo results. Requires JSON decode within JS.
			\wp_send_json( $results );
		endif;
	}

	/**
	 * Handles cropping of images on AJAX request.
	 *
	 * Copied from WordPress Core wp_ajax_crop_image.
	 * Adjusted: 1. It accepts capability 'upload_files', instead of 'customize'.
	 *               - This was set to 'edit_post' in WP 4.7? trac ticket got lost, probably for (invalid) security reasons.
	 *                 In any case, that's still incorrect, and I gave up on communicating this;
	 *                 We're not editing the image, we're creating a new one!
	 *           2. It now only accepts TSF own AJAX nonces.
	 *           3. It now only accepts context 'tsf-image'
	 *           4. It no longer accepts a default context.
	 *
	 * @since 3.1.0 : Introduced in 2.9.0, but the name changed.
	 * @securitycheck 3.0.0 OK.
	 * @access private
	 */
	public function _wp_ajax_crop_image() {

		$this->_check_tsf_ajax_referer( 'upload_files' );
		if (
		   ! \current_user_can( 'upload_files' ) // precision alignment ok.
		|| ! isset( $_POST['id'], $_POST['context'], $_POST['cropDetails'] ) // input var ok.
		) {
			\wp_send_json_error();
		}

		$attachment_id = \absint( $_POST['id'] ); // input var ok.

		$context = str_replace( '_', '-', \sanitize_key( $_POST['context'] ) ); // input var ok.
		$data    = array_map( 'absint', $_POST['cropDetails'] ); // input var ok.
		$cropped = \wp_crop_image( $attachment_id, $data['x1'], $data['y1'], $data['width'], $data['height'], $data['dst_width'], $data['dst_height'] );

		if ( ! $cropped || \is_wp_error( $cropped ) )
			\wp_send_json_error( [ 'message' => \esc_js( \__( 'Image could not be processed.', 'default' ) ) ] );

		switch ( $context ) :
			case 'tsf-image':
				/**
				 * Fires before a cropped image is saved.
				 *
				 * Allows to add filters to modify the way a cropped image is saved.
				 *
				 * @since 4.3.0 WordPress Core
				 *
				 * @param string $context       The Customizer control requesting the cropped image.
				 * @param int    $attachment_id The attachment ID of the original image.
				 * @param string $cropped       Path to the cropped image file.
				 */
				\do_action( 'wp_ajax_crop_image_pre_save', $context, $attachment_id, $cropped );

				/** This filter is documented in wp-admin/custom-header.php */
				$cropped = \apply_filters( 'wp_create_file_in_uploads', $cropped, $attachment_id ); // For replication.

				$parent_url = \wp_get_attachment_url( $attachment_id );
				$url        = str_replace( basename( $parent_url ), basename( $cropped ), $parent_url );

				$size       = @getimagesize( $cropped ); // phpcs:ignore -- Feature might not be enabled.
				$image_type = ( $size ) ? $size['mime'] : 'image/jpeg';

				$object = [
					'post_title'     => basename( $cropped ),
					'post_content'   => $url,
					'post_mime_type' => $image_type,
					'guid'           => $url,
					'context'        => $context,
				];

				$attachment_id = \wp_insert_attachment( $object, $cropped );
				$metadata      = \wp_generate_attachment_metadata( $attachment_id, $cropped );

				/**
				 * Filters the cropped image attachment metadata.
				 *
				 * @since 4.3.0 WordPress Core
				 * @see wp_generate_attachment_metadata()
				 *
				 * @param array $metadata Attachment metadata.
				 */
				$metadata = \apply_filters( 'wp_ajax_cropped_attachment_metadata', $metadata );
				\wp_update_attachment_metadata( $attachment_id, $metadata );

				/**
				 * Filters the attachment ID for a cropped image.
				 *
				 * @since 4.3.0 WordPress Core
				 *
				 * @param int    $attachment_id The attachment ID of the cropped image.
				 * @param string $context       The Customizer control requesting the cropped image.
				 */
				$attachment_id = \apply_filters( 'wp_ajax_cropped_attachment_id', $attachment_id, $context );
				break;

			default:
				\wp_send_json_error( [ 'message' => \esc_js( \__( 'Image could not be processed.', 'default' ) ) ] );
				break;
		endswitch;

		\wp_send_json_success( \wp_prepare_attachment_for_js( $attachment_id ) );
	}
}
