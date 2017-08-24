<?php
/**
 * @package The_SEO_Framework\Views\Inpost
 */

defined( 'ABSPATH' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

//* Fetch the required instance within this file.
$instance = $this->get_view_instance( 'inpost', $instance );

//* Setup default vars.
$post_id = $this->get_the_real_ID();
$type = isset( $type ) ? $type : '';
$language = $this->google_language();

switch ( $instance ) :
	case 'inpost_main' :
		/**
		 * Parse inpost tabs content.
		 *
		 * @since 2.9.0
		 * @see $this->call_function()
		 * @see PHP call_user_func_array() For args.
		 *
		 * @param array $default_tabs {
		 *   'id' = The identifier => {
		 *        array(
		 *            'name'     => The name
		 *            'callback' => The callback function, use array for method calling
		 *            'dashicon' => Desired dashicon
		 *            'args'     => Callback parameters
		 *        )
		 *    }
		 * }
		 */
		$default_tabs = array(
			'general' => array(
				'name'     => __( 'General', 'autodescription' ),
				'callback' => array( $this, 'singular_inpost_box_general_tab' ),
				'dashicon' => 'admin-generic',
				'args' => array( $type ),
			),
			'visibility' => array(
				'name'     => __( 'Visibility', 'autodescription' ),
				'callback' => array( $this, 'singular_inpost_box_visibility_tab' ),
				'dashicon' => 'visibility',
				'args' => array( $type ),
			),
			'social' => array(
				'name'     => __( 'Social', 'autodescription' ),
				'callback' => array( $this, 'singular_inpost_box_social_tab' ),
				'dashicon' => 'share',
				'args' => array( $type ),
			),
		);

		/**
		 * Applies filters 'the_seo_framework_inpost_settings_tabs' : array
		 *
		 * Allows for altering the inpost SEO settings metabox tabs.
		 *
		 * @since 2.9.0
		 *
		 * @param array $default_tabs The default tabs.
		 * @param array $type The current post type display name, like "Post", "Page", "Product".
		 */
		$tabs = (array) apply_filters( 'the_seo_framework_inpost_settings_tabs', $default_tabs, $type );

		echo '<div class="tsf-flex tsf-flex-inside-wrap">';
		$this->inpost_flex_nav_tab_wrapper( 'inpost', $tabs, '2.6.0' );
		echo '</div>';
		break;

	case 'inpost_general' :
		//* Temporarily. TODO refactor.
		$tit_len_parsed = $desc_len_parsed = 0;
		$doctitle_placeholder = $description_placeholder = '';
		$this->_get_inpost_general_tab_vars( $tit_len_parsed, $doctitle_placeholder, $desc_len_parsed, $description_placeholder );
		//= End temporarily.

		if ( $this->is_option_checked( 'display_seo_bar_metabox' ) ) :
		?>
			<div class="tsf-flex-setting tsf-flex">
				<div class="tsf-flex-setting-label tsf-flex">
					<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
						<div class="tsf-flex-setting-label-item tsf-flex">
							<div><strong><?php esc_html_e( 'Doing it Right', 'autodescription' ); ?></strong></div>
						</div>
					</div>
				</div>
				<div class="tsf-flex-setting-input tsf-flex">
					<div>
						<?php $this->post_status( $post_id, 'inpost', true ); ?>
					</div>
				</div>
			</div>
		<?php
		endif;

		?>
		<div class="tsf-flex-setting tsf-flex">
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for="autodescription_title" class="tsf-flex-setting-label-item tsf-flex">
						<div><strong>
							<?php
							/* translators: %s = Post type name */
							printf( esc_html__( 'Custom %s Title', 'autodescription' ), esc_html( $type ) );
							?>
						</strong></div>
						<div>
							<?php
							$this->make_info(
								__( 'Recommended Length: 50 to 55 characters', 'autodescription' ),
								'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#3'
							);
							?>
						</div>
					</label>
					<span class="description tsf-counter">
						<?php
						printf(
							/* translators: %s = number */
							esc_html__( 'Characters Used: %s', 'autodescription' ),
							'<span id="autodescription_title_chars">' . (int) mb_strlen( $tit_len_parsed ) . '</span>'
						);
						?>
						<span class="hide-if-no-js tsf-ajax"></span>
					</span>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<div id="tsf-title-wrap">
					<input class="large-text" type="text" name="autodescription[_genesis_title]" id="autodescription_title" placeholder="<?php echo esc_attr( $doctitle_placeholder ); ?>" value="<?php echo esc_attr( $this->get_custom_field( '_genesis_title' ) ); ?>" autocomplete=off />
					<span id="tsf-title-offset" class="hide-if-no-js"></span><span id="tsf-title-placeholder" class="hide-if-no-js"></span>
				</div>
			</div>
		</div>


		<div class="tsf-flex-setting tsf-flex">
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for="autodescription_description" class="tsf-flex-setting-label-item tsf-flex">
						<div><strong>
							<?php
							/* translators: %s = Post type name */
							printf( esc_html__( 'Custom %s Description', 'autodescription' ), esc_html( $type ) );
							?>
						</strong></div>
						<div><?php $this->make_info( __( 'Recommended Length: 145 to 155 characters', 'autodescription' ), 'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#1' ); ?></div>
					</label>
					<span class="description tsf-counter">
						<?php
						printf(
							/* translators: %s = number */
							esc_html__( 'Characters Used: %s', 'autodescription' ),
							'<span id="autodescription_description_chars">' . (int) mb_strlen( $desc_len_parsed ) . '</span>'
						);
						?>
						<span class="hide-if-no-js tsf-ajax"></span>
					</span>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<textarea class="large-text" name="autodescription[_genesis_description]" id="autodescription_description" placeholder="<?php echo esc_attr( $description_placeholder ); ?>" rows="4" cols="4"><?php echo esc_attr( $this->get_custom_field( '_genesis_description' ) ); ?></textarea>
			</div>
		</div>
		<?php
		break;

	case 'inpost_visibility' :
		//* Fetch Canonical URL.
		$canonical = $this->get_custom_field( '_genesis_canonical_uri' );
		//* Fetch Canonical URL Placeholder.
		$canonical_placeholder = $this->the_url_from_cache( '', $post_id, false, false );

		?>
		<div class="tsf-flex-setting tsf-flex">
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for="autodescription_canonical" class="tsf-flex-setting-label-item tsf-flex">
						<div><strong><?php esc_html_e( 'Custom Canonical URL', 'autodescription' ); ?></strong></div>
						<div>
						<?php
						$this->make_info(
							sprintf(
								/* translators: %s = Post type name */
								__( 'Preferred %s URL location', 'autodescription' ),
								$type
							),
							'https://support.google.com/webmasters/answer/139066?hl=' . $language
						);
						?>
						</div>
					</label>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<input class="large-text" type="text" name="autodescription[_genesis_canonical_uri]" id="autodescription_canonical" placeholder="<?php echo esc_url( $canonical_placeholder ); ?>" value="<?php echo esc_url( $this->get_custom_field( '_genesis_canonical_uri' ) ); ?>" />
			</div>
		</div>

		<div class="tsf-flex-setting tsf-flex">
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<div class="tsf-flex-setting-label-item tsf-flex">
						<div><strong><?php esc_html_e( 'Robots Meta Settings', 'autodescription' ); ?></strong></div>
					</div>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<div class="tsf-checkbox-wrapper">
					<label for="autodescription_noindex">
						<input type="checkbox" name="autodescription[_genesis_noindex]" id="autodescription_noindex" value="1" <?php checked( $this->get_custom_field( '_genesis_noindex' ) ); ?> />
						<?php
						/* translators: 1: Option, 2: Post or Page */
						printf( esc_html__( 'Apply %1$s to this %2$s', 'autodescription' ), $this->code_wrap( 'noindex' ), esc_html( $type ) );
						echo ' ';
						$this->make_info(
							sprintf(
								__( 'Tell Search Engines not to show this %s in their search results', 'autodescription' ),
								$type
							),
							'https://support.google.com/webmasters/answer/93710?hl=' . $language
						);
						?>
					</label>
				</div>
				<div class="tsf-checkbox-wrapper">
					<label for="autodescription_nofollow"><input type="checkbox" name="autodescription[_genesis_nofollow]" id="autodescription_nofollow" value="1" <?php checked( $this->get_custom_field( '_genesis_nofollow' ) ); ?> />
					<?php
						/* translators: 1: Option, 2: Post or Page */
						printf( esc_html__( 'Apply %1$s to this %2$s', 'autodescription' ), $this->code_wrap( 'nofollow' ), esc_html( $type ) );
						echo ' ';
						$this->make_info( sprintf( __( 'Tell Search Engines not to follow links on this %s', 'autodescription' ), $type ), 'https://support.google.com/webmasters/answer/96569?hl=' . $language );
					?>
					</label>
				</div>
				<div class="tsf-checkbox-wrapper">
					<label for="autodescription_noarchive"><input type="checkbox" name="autodescription[_genesis_noarchive]" id="autodescription_noarchive" value="1" <?php checked( $this->get_custom_field( '_genesis_noarchive' ) ); ?> />
					<?php
						/* translators: 1: Option, 2: Post or Page */
						printf(
							esc_html__( 'Apply %1$s to this %2$s', 'autodescription' ),
							$this->code_wrap( 'noarchive' ),
							esc_html( $type )
						);
						echo ' ';
						/* translators: %s = Post type name */
						$this->make_info( sprintf( __( 'Tell Search Engines not to save a cached copy of this %s', 'autodescription' ), $type ), 'https://support.google.com/webmasters/answer/79812?hl=' . $language );
					?>
					</label>
				</div>
			</div>
		</div>

		<?php
		$can_do_archive_query = $this->is_option_checked( 'alter_archive_query' ) && $this->post_type_supports_taxonomies();
		$can_do_search_query = $this->is_option_checked( 'alter_search_query' );
		?>

	<?php if ( $can_do_archive_query || $can_do_search_query ) : ?>
		<div class="tsf-flex-setting tsf-flex">
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<div class="tsf-flex-setting-label-item tsf-flex">
						<div><strong><?php esc_html_e( 'Archive Settings', 'autodescription' ); ?></strong></div>
					</div>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<?php if ( $can_do_search_query ) : ?>
				<div class="tsf-checkbox-wrapper">
					<label for="autodescription_exclude_local_search"><input type="checkbox" name="autodescription[exclude_local_search]" id="autodescription_exclude_local_search" value="1" <?php checked( $this->get_custom_field( 'exclude_local_search' ) ); ?> />
						<?php
						/* translators: %s = Post type name */
						printf( esc_html__( 'Exclude this %s from local search', 'autodescription' ), esc_html( $type ) );
						echo ' ';
						/* translators: %s = Post type name */
						$this->make_info( sprintf( __( 'This excludes this %s from local on-site search results', 'autodescription' ), $type ) );
						?>
					</label>
				</div>
				<?php endif; ?>
				<?php if ( $can_do_archive_query ) : ?>
				<div class="tsf-checkbox-wrapper">
					<label for="autodescription_exclude_from_archive"><input type="checkbox" name="autodescription[exclude_from_archive]" id="autodescription_exclude_from_archive" value="1" <?php checked( $this->get_custom_field( 'exclude_from_archive' ) ); ?> />
						<?php
						/* translators: %s = Post type name */
						printf( esc_html__( 'Exclude this %s from all archive listings', 'autodescription' ), esc_html( $type ) );
						echo ' ';
						/* translators: %s = Post type name */
						$this->make_info( sprintf( __( 'This excludes this %s from on-site archive pages', 'autodescription' ), $type ) );
						?>
					</label>
				</div>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>

		<div class="tsf-flex-setting tsf-flex">
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for="autodescription_redirect" class="tsf-flex-setting-label-item tsf-flex">
						<div>
							<strong><?php esc_html_e( 'Custom 301 Redirect URL', 'autodescription' ); ?></strong>
						</div>
						<div>
							<?php
							$this->make_info(
								__( 'This will force visitors to go to another URL', 'autodescription' ),
								'https://support.google.com/webmasters/answer/93633?hl=' . $language
							);
							?>
						</div>
					</label>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<input class="large-text" type="text" name="autodescription[redirect]" id="autodescription_redirect" value="<?php echo esc_url( $this->get_custom_field( 'redirect' ) ); ?>" />
			</div>
		</div>
		<?php
		break;

	case 'inpost_social' :
		//* Fetch image placeholder.
		$image_placeholder = $this->get_social_image( array( 'post_id' => $post_id, 'disallowed' => array( 'postmeta' ), 'escape' => false ) );

		?>
		<div class="tsf-flex-setting tsf-flex">
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for="autodescription_socialimage-url" class="tsf-flex-setting-label-item tsf-flex">
						<div><strong><?php esc_html_e( 'Custom Social Image URL', 'autodescription' ); ?></strong></div>
						<div>
						<?php
						$this->make_info(
							sprintf(
								/* translators: %s = Post type name */
								__( 'Preferred %s Social Image URL location', 'autodescription' ),
								$type
							),
							'https://developers.facebook.com/docs/sharing/best-practices#images'
						);
						?>
						</div>
					</label>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<input class="large-text" type="text" name="autodescription[_social_image_url]" id="autodescription_socialimage-url" placeholder="<?php echo esc_url( $image_placeholder ); ?>" value="<?php echo esc_url( $this->get_custom_field( '_social_image_url' ) ); ?>" />
				<div class="hide-if-no-js tsf-social-image-buttons">
					<?php
					//= Already escaped.
					echo $this->get_social_image_uploader_form( 'autodescription_socialimage' );
					?>
				</div>
				<?php
				/**
				 * Insert form element only if JS is active. If JS is inactive, then this will cause it to be emptied on $_POST
				 * @TODO use disabled and jQuery.removeprop( 'disabled' )?
				 */
				?>
				<script>
					document.getElementById( 'autodescription_socialimage-url' ).insertAdjacentHTML( 'afterend', '<input type="hidden" name="autodescription[_social_image_id]" id="autodescription_socialimage-id" value="<?php echo absint( $this->get_custom_field( '_social_image_id' ) ); ?>" />' );
				</script>
			</div>
		</div>
		<?php
		break;

endswitch;
