<?php
/**
 * @package The_SEO_Framework\Views\Inpost
 */

defined( 'ABSPATH' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

//* Fetch the required instance within this file.
$instance = $this->get_view_instance( 'the_seo_framework_settings_type', $instance );

//* Get the language the Google page should assume.
$language = $this->google_language();

switch ( $instance ) :
	case 'the_seo_framework_settings_type_singular' :
		$post_id = $this->get_the_real_ID();
		$is_static_frontpage = $this->is_static_frontpage( $post_id );

		$title = $this->get_custom_field( '_genesis_title', $post_id );

		/**
		 * Generate static placeholder
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
		 * Special check for home page.
		 *
		 * @since 2.3.4
		 */
		if ( $is_static_frontpage ) {
			if ( $this->get_option( 'homepage_tagline' ) ) {
				$tit_len_pre = $title ? $title . ' | ' . $this->get_blogdescription() : $generated_doctitle;
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

		//* Fetch Canonical URL.
		$canonical = $this->get_custom_field( '_genesis_canonical_uri' );
		//* Fetch Canonical URL Placeholder.
		$canonical_placeholder = $this->the_url_from_cache( '', $post_id, false, false );

		//* Fetch image placeholder.
		$image_placeholder = $this->get_image( $post_id, array( 'disallowed' => array( 'postmeta' ) ), false );

		?>
		<?php if ( 'above' === $this->inpost_seo_bar || $this->is_option_checked( 'display_seo_bar_metabox' ) ) : ?>
		<p>
			<strong><?php esc_html_e( 'Doing it Right', 'autodescription' ); ?></strong>
			<div>
				<?php $this->post_status( $post_id, 'inpost', true ); ?>
			</div>
		</p>
		<?php endif; ?>

		<p>
			<label for="autodescription_title"><strong><?php printf( esc_html__( 'Custom %s Title', 'autodescription' ), esc_html( $type ) ); ?></strong>
				<a href="<?php echo esc_url( 'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#3' ); ?>" target="_blank" title="<?php esc_attr_e( 'Recommended Length: 50 to 55 characters', 'autodescription' ); ?>">[?]</a>
				<span class="description tsf-counter">
					<?php printf( esc_html__( 'Characters Used: %s', 'autodescription' ), '<span id="autodescription_title_chars">' . (int) mb_strlen( $tit_len_parsed ) . '</span>' ); ?>
					<span class="hide-if-no-js tsf-ajax"></span>
				</span>
			</label>
		</p>
		<p>
			<div id="tsf-title-wrap">
				<input class="large-text" type="text" name="autodescription[_genesis_title]" id="autodescription_title" placeholder="<?php echo esc_attr( $doctitle_placeholder ); ?>" value="<?php echo esc_attr( $this->get_custom_field( '_genesis_title' ) ); ?>" />
				<span id="tsf-title-offset" class="hide-if-no-js"></span><span id="tsf-title-placeholder" class="hide-if-no-js"></span>
			</div>
		</p>

		<p>
			<label for="autodescription_description">
				<strong><?php printf( esc_html__( 'Custom %s Description', 'autodescription' ), esc_html( $type ) ); ?></strong>
				<a href="<?php echo esc_url( 'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#1' ); ?>" target="_blank" title="<?php esc_attr_e( 'Recommended Length: 145 to 155 characters', 'autodescription' ); ?>">[?]</a>
				<span class="description tsf-counter">
					<?php printf( esc_html__( 'Characters Used: %s', 'autodescription' ), '<span id="autodescription_description_chars">' . (int) mb_strlen( $desc_len_parsed ) . '</span>' ); ?>
					<span class="hide-if-no-js tsf-ajax"></span>
				</span>
			</label>
		</p>
		<p>
			<textarea class="large-text" name="autodescription[_genesis_description]" id="autodescription_description" placeholder="<?php echo esc_attr( $description_placeholder ); ?>" rows="4" cols="4"><?php echo esc_attr( $this->get_custom_field( '_genesis_description' ) ); ?></textarea>
		</p>

		<p>
			<label for="autodescription_socialimage">
				<strong><?php esc_html_e( 'Custom Social Image URL', 'autodescription' ); ?></strong>
				<a href="<?php echo esc_url( 'https://developers.facebook.com/docs/sharing/best-practices#images' ); ?>" target="_blank" title="<?php printf( esc_attr__( 'Preferred %s Social Image URL location', 'autodescription' ), esc_attr( $type ) ); ?>">[?]</a>
			</label>
		</p>
		<p class="hide-if-no-js">
			<?php
			//* Already escaped.
			echo $this->get_social_image_uploader_form( 'autodescription_socialimage' );
			?>
		</p>
		<p>
			<input class="large-text" type="text" name="autodescription[_social_image_url]" id="autodescription_socialimage-url" placeholder="<?php echo esc_url( $image_placeholder ); ?>" value="<?php echo esc_url( $this->get_custom_field( '_social_image_url' ) ); ?>" />
			<?php
			/**
			 * Insert form element only if JS is active. If JS is inactive, then this will cause it to be emptied on $_POST
			 * @TODO use disabled and jQuery.removeprop( 'disabled' )?
			 */
			?>
			<script>
				document.getElementById( 'autodescription_socialimage-url' ).insertAdjacentHTML( 'afterend', '<input type="hidden" name="autodescription[_social_image_id]" id="autodescription_socialimage-id" value="<?php echo absint( $this->get_custom_field( '_social_image_id' ) ); ?>" />' );
			</script>
		</p>

		<p>
			<label for="autodescription_canonical">
				<strong><?php esc_html_e( 'Custom Canonical URL', 'autodescription' ); ?></strong>
				<a href="<?php echo esc_url( 'https://support.google.com/webmasters/answer/139066?hl=' . $language ); ?>" target="_blank" title="<?php printf( esc_attr__( 'Preferred %s URL location', 'autodescription' ), esc_attr( $type ) ); ?>">[?]</a>
			</label>
		</p>
		<p>
			<input class="large-text" type="text" name="autodescription[_genesis_canonical_uri]" id="autodescription_canonical" placeholder="<?php echo esc_url( $canonical_placeholder ); ?>" value="<?php echo esc_url( $this->get_custom_field( '_genesis_canonical_uri' ) ); ?>" />
		</p>

		<p><strong><?php esc_html_e( 'Robots Meta Settings', 'autodescription' ); ?></strong></p>
		<p>
			<label for="autodescription_noindex"><input type="checkbox" name="autodescription[_genesis_noindex]" id="autodescription_noindex" value="1" <?php checked( $this->get_custom_field( '_genesis_noindex' ) ); ?> />
				<?php
					/* translators: 1: Option, 2: Post or Page */
					printf( esc_html__( 'Apply %1$s to this %2$s', 'autodescription' ), $this->code_wrap( 'noindex' ), esc_html( $type ) );
				?>
				<a href="<?php echo esc_url( 'https://support.google.com/webmasters/answer/93710?hl=' . $language ); ?>" target="_blank" title="<?php printf( esc_attr__( 'Tell Search Engines not to show this %s in their search results', 'autodescription' ), esc_attr( $type ) ); ?>">[?]</a>
			</label>

			<br>

			<label for="autodescription_nofollow"><input type="checkbox" name="autodescription[_genesis_nofollow]" id="autodescription_nofollow" value="1" <?php checked( $this->get_custom_field( '_genesis_nofollow' ) ); ?> />
				<?php
					/* translators: 1: Option, 2: Post or Page */
					printf( esc_html__( 'Apply %1$s to this %2$s', 'autodescription' ), $this->code_wrap( 'nofollow' ), esc_html( $type ) );
				?>
				<a href="<?php echo esc_url( 'https://support.google.com/webmasters/answer/96569?hl=' . $language ); ?>" target="_blank" title="<?php printf( esc_attr__( 'Tell Search Engines not to follow links on this %s', 'autodescription' ), esc_attr( $type ) ); ?>">[?]</a>
			</label>

			<br>

			<label for="autodescription_noarchive"><input type="checkbox" name="autodescription[_genesis_noarchive]" id="autodescription_noarchive" value="1" <?php checked( $this->get_custom_field( '_genesis_noarchive' ) ); ?> />
				<?php
					/* translators: 1: Option, 2: Post or Page */
					printf( esc_html__( 'Apply %1$s to this %2$s', 'autodescription' ), $this->code_wrap( 'noarchive' ), esc_html( $type ) );
				?>
				<a href="<?php echo esc_url( 'https://support.google.com/webmasters/answer/79812?hl=' . $language ); ?>" target="_blank" title="<?php printf( esc_attr__( 'Tell Search Engines not to save a cached copy of this %s', 'autodescription' ), esc_attr( $type ) ); ?>">[?]</a>
			</label>
		</p>

		<p><strong><?php esc_html_e( 'Local Search Settings', 'autodescription' ); ?></strong></p>
		<p>
			<label for="autodescription_exclude_local_search"><input type="checkbox" name="autodescription[exclude_local_search]" id="autodescription_exclude_local_search" value="1" <?php checked( $this->get_custom_field( 'exclude_local_search' ) ); ?> />
				<?php printf( esc_html__( 'Exclude this %s from local search', 'autodescription' ), esc_html( $type ) ); ?>
				<span title="<?php printf( esc_attr__( 'This excludes this %s from local on-site search results', 'autodescription' ), esc_attr( $type ) ); ?>">[?]</span>
			</label>
		</p>

		<p>
			<label for="autodescription_redirect">
				<strong><?php esc_html_e( 'Custom 301 Redirect URL', 'autodescription' ); ?></strong>
				<a href="<?php echo esc_url( 'https://support.google.com/webmasters/answer/93633?hl=' . $language ); ?>" target="_blank" title="<?php esc_attr_e( 'This will force visitors to go to another URL', 'autodescription' ); ?>">[?]</a>
			</label>
		</p>
		<p>
			<input class="large-text" type="text" name="autodescription[redirect]" id="genesis_redirect" value="<?php echo esc_url( $this->get_custom_field( 'redirect' ) ); ?>" />
		</p>

		<?php if ( 'below' === $this->inpost_seo_bar ) : ?>
		<p>
			<strong><?php esc_html_e( 'Doing it Right', 'autodescription' ); ?></strong>
			<div>
				<?php $this->post_status( $post_id, 'inpost', true ); ?>
			</div>
		</p>
		<?php endif;
		break;

	case 'the_seo_framework_settings_type_term' :

		//* Fetch Term ID and taxonomy.
		$term_id = $object->term_id;
		$taxonomy = $object->taxonomy;

		$data = $this->get_term_data( $object, $term_id );

		$title = isset( $data['doctitle'] ) ? $data['doctitle'] : '';
		$description = isset( $data['description'] ) ? $data['description'] : '';
		$noindex = isset( $data['noindex'] ) ? $data['noindex'] : '';
		$nofollow = isset( $data['nofollow'] ) ? $data['nofollow'] : '';
		$noarchive = isset( $data['noarchive'] ) ? $data['noarchive'] : '';

		$generated_doctitle_args = array(
			'term_id' => $term_id,
			'taxonomy' => $taxonomy,
			'placeholder' => true,
			'get_custom_field' => false,
		);

		$generated_description_args = array(
			'id' => $term_id,
			'taxonomy' => $taxonomy,
			'get_custom_field' => false,
		);

		//* Generate title and description.
		$generated_doctitle = $this->title( '', '', '', $generated_doctitle_args );
		$generated_description = $this->generate_description( '', $generated_description_args );

		$blog_name = $this->get_blogname();
		$add_additions = $this->add_title_additions();

		/**
		 * Separator doesn't matter. Since html_entity_decode is used.
		 * Order doesn't matter either. Since it's just used for length calculation.
		 *
		 * @since 2.3.4
		 */
		$doc_pre_rem = $add_additions ? $title . ' | ' . $blog_name : $title;
		$title_len = $title ? $doc_pre_rem : $generated_doctitle;
		$description_len = $description ?: $generated_description;

		/**
		 * Convert to what Google outputs.
		 *
		 * This will convert e.g. &raquo; to a single length character.
		 * @since 2.3.4
		 */
		$tit_len_parsed = html_entity_decode( $title_len );
		$desc_len_parsed = html_entity_decode( $description_len );

		/**
		 * Generate static placeholder for when title or description is emptied
		 *
		 * @since 2.2.4
		 */
		$title_placeholder = $generated_doctitle;
		$description_placeholder = $generated_description;

		?>
		<h3><?php printf( esc_html__( '%s SEO Settings', 'autodescription' ), esc_html( $type ) ); ?></h3>

		<table class="form-table">
			<tbody>
				<?php if ( 'above' === $this->inpost_seo_bar || $this->is_option_checked( 'display_seo_bar_metabox' ) ) : ?>
				<tr>
					<th scope="row" valign="top"><?php esc_html_e( 'Doing it Right', 'autodescription' ); ?></th>
					<td>
						<?php $this->post_status( $term_id, $taxonomy, true ); ?>
					</td>
				</tr>
				<?php endif; ?>

				<tr class="form-field">
					<th scope="row" valign="top">
						<label for="autodescription-meta[doctitle]">
							<strong><?php printf( esc_html__( '%s Title', 'autodescription' ), esc_html( $type ) ); ?></strong>
							<a href="<?php echo esc_url( 'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#3' ); ?>" target="_blank" title="<?php esc_attr_e( 'Recommended Length: 50 to 55 characters', 'autodescription' ); ?>">[?]</a>
						</label>
					</th>
					<td>
						<div id="tsf-title-wrap">
							<input name="autodescription-meta[doctitle]" id="autodescription-meta[doctitle]" type="text" placeholder="<?php echo esc_attr( $title_placeholder ) ?>" value="<?php echo esc_attr( $title ); ?>" size="40" />
							<span id="tsf-title-offset" class="hide-if-no-js"></span><span id="tsf-title-placeholder" class="hide-if-no-js"></span>
						</div>
						<p class="description tsf-counter">
							<?php printf( esc_html__( 'Characters Used: %s', 'autodescription' ), '<span id="autodescription-meta[doctitle]_chars">' . esc_html( mb_strlen( $tit_len_parsed ) ) . '</span>' ); ?>
							<span class="hide-if-no-js tsf-ajax"></span>
						</p>
					</td>
				</tr>

				<tr class="form-field">
					<th scope="row" valign="top">
						<label for="autodescription-meta[description]">
							<strong><?php printf( esc_html__( '%s Meta Description', 'autodescription' ), esc_html( $type ) ); ?></strong>
							<a href="<?php echo esc_url( 'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#1' ); ?>" target="_blank" title="<?php esc_attr_e( 'Recommended Length: 145 to 155 characters', 'autodescription' ); ?>">[?]</a>
						</label>
					</th>
					<td>
						<textarea name="autodescription-meta[description]" id="autodescription-meta[description]" placeholder="<?php echo esc_attr( $description_placeholder ); ?>" rows="5" cols="50" class="large-text"><?php echo esc_html( $description ); ?></textarea>
						<p class="description tsf-counter">
							<?php printf( esc_html__( 'Characters Used: %s', 'autodescription' ), '<span id="autodescription-meta[description]_chars">' . esc_html( mb_strlen( $desc_len_parsed ) ) . '</span>' ); ?>
							<span class="hide-if-no-js tsf-ajax"></span>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row" valign="top"><?php esc_html_e( 'Robots Meta Settings', 'autodescription' ); ?></th>
					<td>
						<label for="autodescription-meta[noindex]"><input name="autodescription-meta[noindex]" id="autodescription-meta[noindex]" type="checkbox" value="1" <?php checked( $noindex ); ?> />
							<?php printf( esc_html__( 'Apply %s to this term?', 'autodescription' ), $this->code_wrap( 'noindex' ) ); ?>
							<a href="<?php echo esc_url( 'https://support.google.com/webmasters/answer/93710?hl=' . $language ); ?>" target="_blank" title="<?php printf( esc_attr__( 'Tell Search Engines not to show this page in their search results', 'autodescription' ) ); ?>">[?]</a>
						</label>

						<br>

						<label for="autodescription-meta[nofollow]"><input name="autodescription-meta[nofollow]" id="autodescription-meta[nofollow]" type="checkbox" value="1" <?php checked( $nofollow ); ?> />
							<?php printf( esc_html__( 'Apply %s to this term?', 'autodescription' ), $this->code_wrap( 'nofollow' ) ); ?>
							<a href="<?php echo esc_url( 'https://support.google.com/webmasters/answer/96569?hl=' . $language ); ?>" target="_blank" title="<?php printf( esc_attr__( 'Tell Search Engines not to follow links on this page', 'autodescription' ) ); ?>">[?]</a>
						</label>

						<br>

						<label for="autodescription-meta[noarchive]"><input name="autodescription-meta[noarchive]" id="autodescription-meta[noarchive]" type="checkbox" value="1" <?php checked( $noarchive ); ?> />
							<?php printf( esc_html__( 'Apply %s to this term?', 'autodescription' ), $this->code_wrap( 'noarchive' ) ); ?>
							<a href="<?php echo esc_url( 'https://support.google.com/webmasters/answer/79812?hl=' . $language ); ?>" target="_blank" title="<?php printf( esc_attr__( 'Tell Search Engines not to save a cached copy of this page', 'autodescription' ) ); ?>">[?]</a>
						</label>

						<?php // Saved flag, if set then it won't fetch for Genesis meta anymore ?>
						<label class="hidden" for="autodescription-meta[saved_flag]">
							<input name="autodescription-meta[saved_flag]" id="autodescription-meta[saved_flag]" type="checkbox" value="1" checked='checked' />
						</label>
					</td>
				</tr>

				<?php if ( 'below' === $this->inpost_seo_bar ) : ?>
				<tr>
					<th scope="row" valign="top"><?php esc_html_e( 'Doing it Right', 'autodescription' ); ?></th>
					<td>
						<?php $this->post_status( $term_id, $taxonomy, true ); ?>
					</td>
				</tr>
				<?php endif; ?>
			</tbody>
		</table>
		<?php
		break;

	default :
		break;
endswitch;
