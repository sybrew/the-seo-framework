<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Admin_Init
 * @subpackage The_SEO_Framework\Admin
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
	 * Initializes SEO Bar tables.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	public function _init_seo_bar_tables() {

		if ( $this->get_option( 'display_seo_bar_tables' ) ) {
			new Bridges\SeoBar;
		}
	}

	/**
	 * Initializes List Edit tables.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	public function _init_list_edit() {
		new Bridges\ListEdit;
	}

	/**
	 * Adds post states for the post/page edit.php query.
	 *
	 * @since 4.0.0
	 *
	 * @param array    $states The current post states array
	 * @param \WP_Post $post The Post Object.
	 * @return array Adjusted $states
	 */
	public function _add_post_state( $states = [], $post ) {

		$post_id = isset( $post->ID ) ? $post->ID : false;

		if ( $post_id ) {
			$search_exclude  = $this->get_option( 'alter_search_query' ) && $this->get_post_meta_item( 'exclude_local_search', $post_id );
			$archive_exclude = $this->get_option( 'alter_archive_query' ) && $this->get_post_meta_item( 'exclude_from_archive', $post_id );

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
	 * @since 4.0.0 Now discerns autoloading between taxonomies and singular types.
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

			if ( $this->is_archive_admin() ) {
				$prepare_edit_screen = $this->is_taxonomy_supported();
			} elseif ( $this->is_singular_admin() ) {
				$prepare_edit_screen = $this->is_post_type_supported();
			} else {
				$prepare_edit_screen = false;
			}

			if ( $prepare_edit_screen ) {
				$enqueue_hooks = [
					'edit.php',
					'post.php',
					'post-new.php',
					'edit-tags.php',
					'term.php',
				];

				if ( ! $this->get_option( 'display_seo_bar_tables' ) ) {
					$enqueue_hooks = array_diff(
						$enqueue_hooks,
						[
							'edit.php',
							'edit-tags.php',
						]
					);
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
	 * @since 4.0.0 First parameter is now removed.
	 *
	 * @return void Early if already enqueued.
	 */
	public function init_admin_scripts() {

		if ( _has_run( __METHOD__ ) ) return;

		Bridges\Scripts::_init();
	}

	/**
	 * Returns the title and description input guideline table, for
	 * (Google) search, Open Graph, and Twitter.
	 *
	 * NB: Some scripts have wide characters. These are recognized by Google, and have been adjusted for in the chactacter
	 * guidelines. German is a special Case, where we account for the Capitalization of Nouns.
	 *
	 * NB: Although the Arabic & Farsi scripts are much smaller in width, Google seems to be using the 160 & 70 char limits
	 * strictly... As such, we stricten the guidelines for pixels instead.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 1. Now gives different values for various WordPress locales.
	 *              2. Added $locale input parameter.
	 * @staticvar array $guidelines
	 * @TODO Consider splitting up search into Google, Bing, etc., as we might
	 *       want users to set their preferred search engine. Now, these engines
	 *       are barely any different.
	 * TODO move this to another object?
	 *
	 * @param string|null $locale The locale to test. If empty, it will be auto-determined.
	 * @return array
	 */
	public function get_input_guidelines( $locale = null ) {

		static $guidelines = [];

		$locale = $locale ?: \get_locale();

		// Strip the "_formal" and other suffixes. 5 length: xx_YY
		$locale = substr( $locale, 0, 5 );

		if ( isset( $guidelines[ $locale ] ) )
			return $guidelines[ $locale ];

		// phpcs:disable, WordPress.WhiteSpace.OperatorSpacing.SpacingAfter
		$character_adjustments = [
			'as'    => 148 / 160, // Assamese (অসমীয়া)
			'de_AT' => 158 / 160, // Austrian German (Österreichisch Deutsch)
			'de_CH' => 158 / 160, // Swiss German (Schweiz Deutsch)
			'de_DE' => 158 / 160, // German (Deutsch)
			'gu'    => 148 / 160, // Gujarati (ગુજરાતી)
			'ml_IN' => 100 / 160, // Malayalam (മലയാളം)
			'ja'    =>  70 / 160, // Japanese (日本語)
			'ko_KR' =>  82 / 160, // Korean (한국어)
			'ta_IN' => 120 / 160, // Talim (தமிழ்)
			'zh_TW' =>  70 / 160, // Taiwanese Mandarin (Traditional Chinese) (繁體中文)
			'zh_HK' =>  70 / 160, // Hong Kong (Chinese version) (香港中文版)
			'zh_CN' =>  70 / 160, // Mandarin (Simplified Chinese) (简体中文)
		];
		// phpcs:enable, WordPress.WhiteSpace.OperatorSpacing.SpacingAfter

		$c_adjust = isset( $character_adjustments[ $locale ] ) ? $character_adjustments[ $locale ] : 1;

		$pixel_adjustments = [
			'ar'    => 760 / 910, // Arabic (العربية)
			'ary'   => 760 / 910, // Moroccan Arabic (العربية المغربية)
			'azb'   => 760 / 910, // South Azerbaijani (گؤنئی آذربایجان)
			'fa_IR' => 760 / 910, // Iran Farsi (فارسی)
			'haz'   => 760 / 910, // Hazaragi (هزاره گی)
			'ckb'   => 760 / 910, // Central Kurdish (كوردی)
		];

		$p_adjust = isset( $pixel_adjustments[ $locale ] ) ? $pixel_adjustments[ $locale ] : 1;

		// phpcs:disable, WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
		/**
		 * @since 3.1.0
		 * @param array $guidelines The title and description guidelines.
		 *              Don't alter the format. Only change the numeric values.
		 */
		return $guidelines[ $locale ] = (array) \apply_filters(
			'the_seo_framework_input_guidelines',
			[
				'title' => [
					'search' => [
						'chars'  => [
							'lower'     => (int) ( 25 * $c_adjust ),
							'goodLower' => (int) ( 35 * $c_adjust ),
							'goodUpper' => (int) ( 65 * $c_adjust ),
							'upper'     => (int) ( 75 * $c_adjust ),
						],
						'pixels' => [
							'lower'     => (int) ( 200 * $p_adjust ),
							'goodLower' => (int) ( 280 * $p_adjust ),
							'goodUpper' => (int) ( 520 * $p_adjust ),
							'upper'     => (int) ( 600 * $p_adjust ),
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
							'lower'     => (int) ( 45 * $c_adjust ),
							'goodLower' => (int) ( 80 * $c_adjust ),
							'goodUpper' => (int) ( 160 * $c_adjust ),
							'upper'     => (int) ( 320 * $c_adjust ),
						],
						'pixels' => [
							'lower'     => (int) ( 256 * $p_adjust ),
							'goodLower' => (int) ( 455 * $p_adjust ),
							'goodUpper' => (int) ( 910 * $p_adjust ),
							'upper'     => (int) ( 1820 * $p_adjust ),
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
		// phpcs:enable, WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
	}

	/**
	 * Returns the title and description input guideline explanatory table.
	 *
	 * Already attribute-escaped.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 Now added a short leading-dot version for ARIA labels.
	 *
	 * @return array
	 */
	public function get_input_guidelines_i18n() {
		return [
			'long'     => [
				'empty'       => \esc_attr__( "There's no content.", 'autodescription' ),
				'farTooShort' => \esc_attr__( "It's too short and it should have more information.", 'autodescription' ),
				'tooShort'    => \esc_attr__( "It's short and it could have more information.", 'autodescription' ),
				'tooLong'     => \esc_attr__( "It's long and it might get truncated in search.", 'autodescription' ),
				'farTooLong'  => \esc_attr__( "It's too long and it will get truncated in search.", 'autodescription' ),
				'good'        => \esc_attr__( 'Length is good.', 'autodescription' ),
			],
			'short'    => [
				'empty'       => \esc_attr_x( 'Empty', 'The string is empty', 'autodescription' ),
				'farTooShort' => \esc_attr__( 'Far too short', 'autodescription' ),
				'tooShort'    => \esc_attr__( 'Too short', 'autodescription' ),
				'tooLong'     => \esc_attr__( 'Too long', 'autodescription' ),
				'farTooLong'  => \esc_attr__( 'Far too long', 'autodescription' ),
				'good'        => \esc_attr__( 'Good', 'autodescription' ),
			],
			'shortdot' => [
				'empty'       => \esc_attr_x( 'Empty.', 'The string is empty', 'autodescription' ),
				'farTooShort' => \esc_attr__( 'Far too short.', 'autodescription' ),
				'tooShort'    => \esc_attr__( 'Too short.', 'autodescription' ),
				'tooLong'     => \esc_attr__( 'Too long.', 'autodescription' ),
				'farTooLong'  => \esc_attr__( 'Far too long.', 'autodescription' ),
				'good'        => \esc_attr__( 'Good.', 'autodescription' ),
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
	 * @param string $capability The capability that was required for the nonce check to be created.
	 * @return false|int False if the nonce is invalid, 1 if the nonce is valid
	 *                   and generated between 0-12 hours ago, 2 if the nonce is
	 *                   valid and generated between 12-24 hours ago.
	 */
	public function _check_tsf_ajax_referer( $capability ) {
		return \check_ajax_referer( 'tsf-ajax-' . $capability, 'nonce', true );
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
		$target = \esc_url_raw( $target, [ 'https', 'http' ] );

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

		if ( ! $target ) return;

		$headers_list = headers_list();
		$location     = sprintf( 'Location: %s', \wp_sanitize_redirect( $target ) );

		//* Test if WordPress' redirect header is sent. Bail if true.
		if ( in_array( $location, $headers_list, true ) )
			return;

		// phpcs:disable, WordPress.Security.EscapeOutput -- convert_markdown escapes. Added esc_url() for sanity.
		printf( '<p><strong>%s</strong></p>',
			$this->convert_markdown(
				sprintf(
					/* translators: %s = Redirect URL markdown */
					\esc_html__( 'There has been an error redirecting. Refresh the page or follow [this link](%s).', 'autodescription' ),
					\esc_url( $target )
				),
				[ 'a' ],
				[ 'a_internal' => true ]
			)
		);
		// phpcs:enable, WordPress.Security.EscapeOutput
	}

	/**
	 * Handles counter option update on AJAX request for users that can edit posts.
	 *
	 * @since 3.1.0 : Introduced in 2.6.0, but the name changed.
	 * @securitycheck 3.0.0 OK.
	 * @access private
	 */
	public function _wp_ajax_update_counter_type() {

		// phpcs:disable, WordPress.Security.NonceVerification -- _check_tsf_ajax_referer() does this.
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
		if ( isset( $_POST['val'] ) ) {
			$value = (int) $_POST['val'];
		} else {
			$value = $this->get_user_option( 0, 'counter_type', 3 ) + 1;
		}
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

		// phpcs:enable, WordPress.Security.NonceVerification
	}

	/**
	 * Gets an SEO Bar for AJAX during edit-post.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	public function _wp_ajax_get_post_data() {

		// phpcs:disable, WordPress.Security.NonceVerification -- _check_tsf_ajax_referer() does this.
		$this->_check_tsf_ajax_referer( 'edit_posts' );

		// CLear output buffer.
		$this->clean_response_header();

		$post_id = \absint( $_POST['post_id'] );

		if ( ! $post_id || ! \current_user_can( 'edit_post', $post_id ) ) {
			\wp_send_json( [
				'type' => 'failure',
				'data' => [],
			] );
		}

		$_get_defaults = [
			'seobar'          => false,
			'metadescription' => false,
			'ogdescription'   => false,
			'twdescription'   => false,
			'imageurl'        => false,
		];

		// Only get what's indexed in the defaults and set as "true".
		$get = array_keys(
			array_filter(
				array_intersect_key(
					array_merge(
						$_get_defaults,
						(array) ( isset( $_POST['get'] ) ? $_POST['get'] : [] )
					),
					$_get_defaults
				)
			)
		);

		$_generator_args = [
			'id'       => $post_id,
			'taxonomy' => '',
		];

		$data = [];

		foreach ( $get as $g ) :
			switch ( $g ) {
				case 'seobar':
					$data[ $g ] = $this->get_generated_seo_bar( $_generator_args );
					break;

				case 'metadescription':
				case 'ogdescription':
				case 'twdescription':
					switch ( $g ) {
						case 'metadescription':
							if ( $this->is_static_frontpage( $post_id ) ) {
								// phpcs:disable, WordPress.WhiteSpace.PrecisionAlignment
								$data[ $g ] = $this->get_option( 'homepage_description' )
										   ?: $this->get_generated_description( $_generator_args, false );
								// phpcs:enable, WordPress.WhiteSpace.PrecisionAlignment
							} else {
								$data[ $g ] = $this->get_generated_description( $_generator_args, false );
							}
							break;
						case 'ogdescription':
							// phpcs:ignore, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- Smart loop.
							$_social_ph = isset( $_social_ph ) ? $_social_ph : $this->_get_social_placeholders( $_generator_args );
							$data[ $g ] = $_social_ph['description']['og'];
							break;
						case 'twdescription':
							// phpcs:ignore, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- Smart loop.
							$_social_ph = isset( $_social_ph ) ? $_social_ph : $this->_get_social_placeholders( $_generator_args );
							$data[ $g ] = $_social_ph['description']['twitter'];
							break;
					}

					$data[ $g ] = Bridges\Scripts::decode_entities( $this->s_description( $data[ $g ] ) );
					break;

				case 'imageurl':
					if ( $this->is_static_frontpage( $post_id ) && $this->get_option( 'homepage_social_image_url' ) ) {
						$image_details = current( $this->get_image_details( $_generator_args, true, 'social', true ) );
						$data[ $g ]    = isset( $image_details['url'] ) ? $image_details['url'] : '';
					} else {
						$image_details = current( $this->get_generated_image_details( $_generator_args, true, 'social', true ) );
						$data[ $g ]    = isset( $image_details['url'] ) ? $image_details['url'] : '';
					}
					break;

				default:
					break;
			}
		endforeach;

		\wp_send_json( [
			'type'      => 'success',
			'data'      => $data,
			'processed' => $get,
		] );
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

		// This checks the nonce, re:to all 'WordPress.Security.NonceVerification' below
		// phpcs:disable, WordPress.Security.NonceVerification -- _check_tsf_ajax_referer does this.
		$this->_check_tsf_ajax_referer( 'upload_files' );

		if ( ! \current_user_can( 'upload_files' ) || ! isset( $_POST['id'], $_POST['context'], $_POST['cropDetails'] ) ) {
			\wp_send_json_error();
		}

		$attachment_id = \absint( $_POST['id'] );

		$context = str_replace( '_', '-', \sanitize_key( $_POST['context'] ) );
		$data    = array_map( 'absint', $_POST['cropDetails'] );
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

				// phpcs:ignore, WordPress.PHP.NoSilencedErrors -- Feature may be disabled.
				$size       = @getimagesize( $cropped );
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

		// phpcs:enable, WordPress.Security.NonceVerification
	}
}
