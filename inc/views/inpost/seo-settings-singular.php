<?php
/**
 * @package The_SEO_Framework\Views\Inpost
 */

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

//* Fetch the required instance within this file.
$instance = $this->get_view_instance( 'inpost', $instance );

//* Setup default vars.
$post_id = $this->get_the_real_ID();
$type = isset( $type ) ? $type : '';
$language = $this->google_language();

switch ( $instance ) :
	case 'inpost_main':
		$tabs = $this->get_inpost_tabs( $type );
		echo '<div class="tsf-flex tsf-flex-inside-wrap">';
		$this->inpost_flex_nav_tab_wrapper( 'inpost', $tabs, '2.6.0' );
		echo '</div>';
		break;

	case 'inpost_general':
		if ( $this->get_option( 'display_seo_bar_metabox' ) ) :
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

		if ( $this->is_static_frontpage( $post_id ) ) {
			// When the homepage title is set, we can safely get the custom field.
			$title_placeholder = $this->escape_title( $this->get_option( 'homepage_title' ) )
							   ? $this->get_custom_field_title( [ 'id' => $post_id ] )
							   : $this->get_generated_title( [ 'id' => $post_id ] );

			$description_placeholder = $this->escape_description( $this->get_option( 'homepage_description' ) )
									?: $this->get_generated_description( [ 'id' => $post_id ] );
		} else {
			$title_placeholder       = $this->get_generated_title( [ 'id' => $post_id ] );
			$description_placeholder = $this->get_generated_description( [ 'id' => $post_id ] );
		}

		?>
		<div class="tsf-flex-setting tsf-flex">
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for="autodescription_title" class="tsf-flex-setting-label-item tsf-flex">
						<div><strong><?php esc_html_e( 'Meta Title', 'autodescription' ); ?></strong></div>
						<div>
						<?php
						$this->make_info(
							__( 'The meta title can be used to determine the title used on search engine result pages.', 'autodescription' ),
							'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#page-titles'
						);
						?>
						</div>
					</label>
					<?php
					$this->get_option( 'display_character_counter' )
						and $this->output_character_counter_wrap( 'autodescription_title' );
					$this->get_option( 'display_pixel_counter' )
						and $this->output_pixel_counter_wrap( 'autodescription_title', 'title' );
					?>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<div id="tsf-title-wrap">
					<input class="large-text" type="text" name="autodescription[_genesis_title]" id="autodescription_title" placeholder="<?php echo esc_attr( $title_placeholder ); ?>" value="<?php echo esc_attr( $this->get_custom_field( '_genesis_title', $post_id ) ); ?>" autocomplete=off />
					<?php echo $this->output_js_title_elements(); ?>
				</div>

				<div class="tsf-checkbox-wrapper">
					<label for="autodescription_title_no_blogname">
						<?php
						if ( $this->is_static_frontpage( $post_id ) ) :
							// Disable the input, and hide the previously stored value.
							?>
							<input type="checkbox" id="autodescription_title_no_blogname" value="1" <?php checked( $this->get_custom_field( '_tsf_title_no_blogname' ) ); ?> disabled />
							<input type="hidden" name="autodescription[_tsf_title_no_blogname]" value="1" <?php checked( $this->get_custom_field( '_tsf_title_no_blogname' ) ); ?> />
							<?php
						else :
							?>
							<input type="checkbox" name="autodescription[_tsf_title_no_blogname]" id="autodescription_title_no_blogname" value="1" <?php checked( $this->get_custom_field( '_tsf_title_no_blogname' ) ); ?> />
							<?php
						endif;
						esc_html_e( 'Remove the blogname?', 'autodescription' );
						echo ' ';
						$this->make_info( sprintf( __( 'Use this when you want to rearrange the title parts manually.', 'autodescription' ) ) );
						?>
					</label>
				</div>
			</div>
		</div>

		<div class="tsf-flex-setting tsf-flex">
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for="autodescription_description" class="tsf-flex-setting-label-item tsf-flex">
						<div><strong><?php esc_html_e( 'Meta Description', 'autodescription' ); ?></strong></div>
						<div>
						<?php
						$this->make_info(
							__( 'The meta description can be used to determine the text used under the title on search engine results pages.', 'autodescription' ),
							'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#meta-descriptions'
						);
						?>
						</div>
					</label>
					<?php
					$this->get_option( 'display_character_counter' )
						and $this->output_character_counter_wrap( 'autodescription_description' );
					$this->get_option( 'display_pixel_counter' )
						and $this->output_pixel_counter_wrap( 'autodescription_description', 'description' );
					?>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<textarea class="large-text" name="autodescription[_genesis_description]" id="autodescription_description" placeholder="<?php echo esc_attr( $description_placeholder ); ?>" rows="4" cols="4"><?php echo esc_attr( $this->get_custom_field( '_genesis_description', $post_id ) ); ?></textarea>
				<?php echo $this->output_js_description_elements(); ?>
			</div>
		</div>
		<?php
		break;

	case 'inpost_visibility':
		//* Fetch Canonical URL.
		$canonical = $this->get_custom_field( '_genesis_canonical_uri' );
		//* Fetch Canonical URL Placeholder.
		$canonical_placeholder = $this->create_canonical_url( [ 'id' => $post_id ] );

		?>
		<div class="tsf-flex-setting tsf-flex">
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for="autodescription_canonical" class="tsf-flex-setting-label-item tsf-flex">
						<div><strong><?php esc_html_e( 'Canonical URL', 'autodescription' ); ?></strong></div>
						<div>
						<?php
						$this->make_info(
							__( 'This urges search engines to go to the outputted URL.', 'autodescription' ),
							'https://support.google.com/webmasters/answer/139066?hl=' . $language
						);
						?>
						</div>
					</label>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<input class="large-text" type="url" name="autodescription[_genesis_canonical_uri]" id="autodescription_canonical" placeholder="<?php echo esc_url( $canonical_placeholder ); ?>" value="<?php echo esc_url( $this->get_custom_field( '_genesis_canonical_uri' ) ); ?>" autocomplete=off />
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
				<?php
				if ( $this->is_static_frontpage( $post_id ) ) {
					$this->attention( __( 'Warning: No public site should ever disable indexing or following for the homepage.', 'autodescription' ), false );
				}
				?>
				<div class="tsf-checkbox-wrapper">
					<label for="autodescription_noindex">
						<input type="checkbox" name="autodescription[_genesis_noindex]" id="autodescription_noindex" value="1" <?php checked( $this->get_custom_field( '_genesis_noindex' ) ); ?> />
						<?php
						/* translators: 1: Option, 2: Post or Page */
						printf( esc_html__( 'Apply %1$s to this %2$s', 'autodescription' ), $this->code_wrap( 'noindex' ), esc_html( $type ) );
						echo ' ';
						$this->make_info(
							sprintf(
								__( 'This tells search engines not to show this %s in their search results.', 'autodescription' ),
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
						$this->make_info(
							/* translators: %s is Post or Page */
							sprintf( __( 'This tells search engines not to follow links on this %s.', 'autodescription' ), $type ),
							'https://support.google.com/webmasters/answer/96569?hl=' . $language
						);
					?>
					</label>
				</div>
				<div class="tsf-checkbox-wrapper">
					<label for="autodescription_noarchive"><input type="checkbox" name="autodescription[_genesis_noarchive]" id="autodescription_noarchive" value="1" <?php checked( $this->get_custom_field( '_genesis_noarchive' ) ); ?> />
					<?php
						printf(
							/* translators: 1: Option, 2: Post or Page */
							esc_html__( 'Apply %1$s to this %2$s', 'autodescription' ),
							$this->code_wrap( 'noarchive' ),
							esc_html( $type )
						);
						echo ' ';
						$this->make_info(
							sprintf(
								/* translators: %s is Post or Page */
								__( 'This tells search engines not to save a cached copy of this %s.', 'autodescription' ),
								$type
							),
							'https://support.google.com/webmasters/answer/79812?hl=' . $language
						);
					?>
					</label>
				</div>
			</div>
		</div>

		<?php
		$can_do_archive_query = $this->post_type_supports_taxonomies() && $this->get_option( 'alter_archive_query' );
		$can_do_search_query  = (bool) $this->get_option( 'alter_search_query' );
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
						$this->make_info( sprintf( __( 'This excludes this %s from local on-site search results.', 'autodescription' ), $type ) );
						?>
					</label>
				</div>
				<?php endif; ?>
				<?php if ( $can_do_archive_query ) : ?>
				<div class="tsf-checkbox-wrapper">
					<label for="autodescription_exclude_from_archive"><input type="checkbox" name="autodescription[exclude_from_archive]" id="autodescription_exclude_from_archive" value="1" <?php checked( $this->get_custom_field( 'exclude_from_archive' ) ); ?> />
						<?php
						/* translators: %s = Post type name */
						printf( esc_html__( 'Exclude this %s from all archive listings.', 'autodescription' ), esc_html( $type ) );
						echo ' ';
						/* translators: %s = Post type name */
						$this->make_info( sprintf( __( 'This excludes this %s from on-site archive pages.', 'autodescription' ), $type ) );
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
							<strong><?php esc_html_e( '301 Redirect URL', 'autodescription' ); ?></strong>
						</div>
						<div>
							<?php
							$this->make_info(
								__( 'This will force visitors to go to another URL.', 'autodescription' ),
								'https://support.google.com/webmasters/answer/93633?hl=' . $language
							);
							?>
						</div>
					</label>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<input class="large-text" type="url" name="autodescription[redirect]" id="autodescription_redirect" value="<?php echo esc_url( $this->get_custom_field( 'redirect' ) ); ?>" autocomplete=off />
			</div>
		</div>
		<?php
		break;

	case 'inpost_social':
		$desc_from_custom_field = $this->get_description_from_custom_field( [ 'id' => $post_id ] );

		if ( $this->is_static_frontpage( $post_id ) ) {
			// Gets custom fields from SEO settings.
			$home_desc = $this->get_option( 'homepage_description' );

			$home_og_title = $this->get_option( 'homepage_og_title' );
			$home_og_desc  = $this->get_option( 'homepage_og_description' );
			$home_tw_title = $this->get_option( 'homepage_twitter_title' );
			$home_tw_desc  = $this->get_option( 'homepage_twitter_description' );

			// Gets custom fields from page.
			$custom_og_title = $this->get_custom_field( '_open_graph_title', $post_id );
			$custom_og_desc  = $this->get_custom_field( '_open_graph_description', $post_id );

			//! OG input falls back to default input.
			$og_tit_placeholder  = $home_og_title
								?: $custom_og_title
								?: $this->get_generated_open_graph_title( [ 'id' => $post_id ] );
			$og_desc_placeholder = $home_og_desc
								?: $desc_from_custom_field
								?: $this->get_generated_open_graph_description( [ 'id' => $post_id ] );

			//! Twitter input falls back to OG input.
			$tw_tit_placeholder  = $home_tw_title
								?: $og_tit_placeholder;
			$tw_desc_placeholder = $home_tw_desc
								?: $home_og_desc
								?: $custom_og_desc
								?: $home_desc
								?: $desc_from_custom_field
								?: $this->get_generated_twitter_description( [ 'id' => $post_id ] );
		} else {
			// Gets custom fields.
			$custom_og_title = $this->get_custom_field( '_open_graph_title', $post_id );
			$custom_og_desc  = $this->get_custom_field( '_open_graph_description', $post_id );

			//! OG input falls back to default input.
			$og_tit_placeholder  = $this->get_generated_open_graph_title( [ 'id' => $post_id ] );
			$og_desc_placeholder = $desc_from_custom_field ?: $this->get_generated_open_graph_description( [ 'id' => $post_id ] );

			//! Twitter input falls back to OG input.
			$tw_tit_placeholder  = $custom_og_title ?: $og_tit_placeholder;
			$tw_desc_placeholder = $custom_og_desc ?: $desc_from_custom_field ?: $this->get_generated_twitter_description( [ 'id' => $post_id ] );
		}

		$show_og = (bool) $this->get_option( 'og_tags' );
		$show_tw = (bool) $this->get_option( 'twitter_tags' );

		?>
		<div class="tsf-flex-setting tsf-flex" <?php echo $show_og ? '' : 'style=display:none'; ?>>
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for="autodescription_og_title" class="tsf-flex-setting-label-item tsf-flex">
						<div><strong>
							<?php
							esc_html_e( 'Open Graph Title', 'autodescription' );
							?>
						</strong></div>
					</label>
					<?php
					$this->get_option( 'display_character_counter' )
						and $this->output_character_counter_wrap( 'autodescription_og_title' );
					?>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<div id="tsf-og-title-wrap">
					<input class="large-text" type="text" name="autodescription[_open_graph_title]" id="autodescription_og_title" placeholder="<?php echo esc_attr( $og_tit_placeholder ); ?>" value="<?php echo esc_attr( $this->get_custom_field( '_open_graph_title' ) ); ?>" autocomplete=off />
				</div>
			</div>
		</div>

		<div class="tsf-flex-setting tsf-flex" <?php echo $show_og ? '' : 'style=display:none'; ?>>
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for="autodescription_og_description" class="tsf-flex-setting-label-item tsf-flex">
						<div><strong>
							<?php
							esc_html_e( 'Open Graph Description', 'autodescription' );
							?>
						</strong></div>
					</label>
					<?php
					$this->get_option( 'display_character_counter' )
						and $this->output_character_counter_wrap( 'autodescription_og_description' );
					?>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<textarea class="large-text" name="autodescription[_open_graph_description]" id="autodescription_og_description" placeholder="<?php echo esc_attr( $og_desc_placeholder ); ?>" rows="3" cols="4"><?php echo esc_attr( $this->get_custom_field( '_open_graph_description' ) ); ?></textarea>
			</div>
		</div>

		<div class="tsf-flex-setting tsf-flex" <?php echo $show_tw ? '' : 'style=display:none'; ?>>
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for="autodescription_twitter_title" class="tsf-flex-setting-label-item tsf-flex">
						<div><strong>
							<?php
							esc_html_e( 'Twitter Title', 'autodescription' );
							?>
						</strong></div>
					</label>
					<?php
					$this->get_option( 'display_character_counter' )
						and $this->output_character_counter_wrap( 'autodescription_twitter_title' );
					?>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<div id="tsf-twitter-title-wrap">
					<input class="large-text" type="text" name="autodescription[_twitter_title]" id="autodescription_twitter_title" placeholder="<?php echo esc_attr( $tw_tit_placeholder ); ?>" value="<?php echo esc_attr( $this->get_custom_field( '_twitter_title' ) ); ?>" autocomplete=off />
				</div>
			</div>
		</div>

		<div class="tsf-flex-setting tsf-flex" <?php echo $show_tw ? '' : 'style=display:none'; ?>>
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for="autodescription_twitter_description" class="tsf-flex-setting-label-item tsf-flex">
						<div><strong>
							<?php
							esc_html_e( 'Twitter Description', 'autodescription' );
							?>
						</strong></div>
					</label>
					<?php
					$this->get_option( 'display_character_counter' )
						and $this->output_character_counter_wrap( 'autodescription_twitter_description', '' );
					?>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<textarea class="large-text" name="autodescription[_twitter_description]" id="autodescription_twitter_description" placeholder="<?php echo esc_attr( $tw_desc_placeholder ); ?>" rows="3" cols="4"><?php echo esc_attr( $this->get_custom_field( '_twitter_description' ) ); ?></textarea>
			</div>
		</div>
		<?php

		//* Fetch image placeholder.
		$image_placeholder = $this->get_social_image( [
			'post_id'    => $post_id,
			'disallowed' => [ 'postmeta' ],
			'escape'     => false,
		] );

		?>
		<div class="tsf-flex-setting tsf-flex">
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for="autodescription_socialimage-url" class="tsf-flex-setting-label-item tsf-flex">
						<div><strong><?php esc_html_e( 'Social Image URL', 'autodescription' ); ?></strong></div>
						<div>
						<?php
						$this->make_info(
							sprintf(
								/* translators: %s = Post type name */
								__( 'Set preferred %s Social Image URL location.', 'autodescription' ),
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
				<input class="large-text" type="url" name="autodescription[_social_image_url]" id="autodescription_socialimage-url" placeholder="<?php echo esc_url( $image_placeholder ); ?>" value="<?php echo esc_url( $this->get_custom_field( '_social_image_url' ) ); ?>" autocomplete=off />
				<input type="hidden" name="autodescription[_social_image_id]" id="autodescription_socialimage-id" value="<?php echo absint( $this->get_custom_field( '_social_image_id' ) ); ?>" disabled class="tsf-enable-media-if-js" />
				<div class="hide-if-no-js tsf-social-image-buttons">
					<?php
					//= Already escaped.
					echo $this->get_social_image_uploader_form( 'autodescription_socialimage' );
					?>
				</div>
			</div>
		</div>
		<?php
		break;

endswitch;
