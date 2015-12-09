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
 * Class AutoDescription_Inpost
 *
 * Outputs Taxonomy, Post and Page meta boxes
 *
 * @since 2.2.2
 */
class AutoDescription_Inpost extends AutoDescription_PageOptions {

	/**
	 * Constructor, load parent constructor
	 */
	public function __construct() {
		parent::__construct();

		// Enqueue inpost meta boxes
		add_action( 'add_meta_boxes', array( $this, 'add_inpost_seo_box_init' ), 5 );

		// Enqueue taxonomy meta boxes
		add_action( 'admin_init', array( $this, 'add_taxonomy_seo_box_init' ), 9 );
	}

	/**
	 * Render the SEO meta box
	 *
	 * Called outside autodescription_run
	 *
	 * Applies the_seo_framework_seobox_output filters. Return false to disable the meta boxes
	 *
	 * @since 2.0.0
	 */
	public function add_inpost_seo_box_init() {

		if ( $this->detect_seo_plugins() )
			return '';

		/**
		 * New filter.
		 * @since 2.3.0
		 *
		 * Removed previous filter.
		 * @since 2.3.5
		 */
		$show_seobox = (bool) apply_filters( 'the_seo_framework_seobox_output', true );

		if ( $show_seobox )
			add_action( 'add_meta_boxes', array( $this, 'add_inpost_seo_box' ), 10 );
	}

	/**
	 * Adds SEO Meta boxes within Taxonomy screens
	 *
	 * @since 2.1.8
	 *
	 * @options Genesis : Merge these options with Genesis options. Prevents lost data.
	 */
	public function add_taxonomy_seo_box_init() {
		// Add taxonomy meta boxes
		foreach ( get_taxonomies( array( 'public' => true ) ) as $tax_name )
			add_action( $tax_name . '_edit_form', array( &$this, 'pre_seo_box' ), 10, 2 );

	}

	/**
	 * Adds SEO Meta boxes beneath every page/post edit screen
	 *
	 * High priority, this box is seen right below the post/page edit screen.
	 *
	 * @since 2.0.0
	 *
	 * @options Genesis : Merge these options with Genesis options. Prevents lost data.
	 *
	 * Rewritten to reduce resource usage. It now supports not only set Posts
	 * and Pages, but all kinds of types.
	 * @since 2.3.5
	 */
	public function add_inpost_seo_box() {

		/**
		 * @uses $this->post_type_supports_custom_seo()
		 * @since 2.3.9
		 */
		if ( $this->post_type_supports_custom_seo() ) {
			global $current_screen;

			$screen = $current_screen->post_type;

			$object = get_post_type_object( $screen );

			if ( is_object( $object ) ) {
				$labels = isset( $object->labels ) ? $object->labels : '';

				if ( !empty( $labels ) ) {
					$singular_name = isset( $labels->singular_name ) ? $labels->singular_name : $labels->name;

					//* Title and type are used interchangeably.
					$title = $type = $singular_name;

					$args = array( $type, 'is_post_page' );

					// Metabox HTML class/id
					$id = 'theseoframework-inpost-box';

					// Note: Pass on the object $this
					add_meta_box( $id, sprintf( __( '%s SEO Settings', 'autodescription' ), $title ), array( &$this, 'pre_seo_box' ), $screen, 'normal', 'high', $args );
				}
			}
		}

	}

	/**
	 * Determines which arguments should be used
	 *
	 * @since 2.1.8
	 *
	 * @used by add_inpost_seo_box
	 *
	 * @param $object the page/post/taxonomy object
	 * @param $args the page/post arguments or taxonomy slug
	 */
	public function pre_seo_box( $object, $args ) {

		if ( is_array( $args ) && isset( $args['args'] ) ) {
			$args_split = $args['args'];

			$page = $args_split[1];

			// Return $args as array on post/page
			if ( $page === 'is_post_page') {
				// Note: Passes through object.
				return $this->inpost_seo_box( $object, (array) $args );
			}
		} else {
			// Note: Passes through object.
			// Empty the arguments, if any.
			return $this->inpost_seo_box( $object, $args = '' );
		}
	}

	/**
	 * Callback for in-post SEO meta box.
	 *
	 * @since 2.0.0
	 *
	 * @param array $post		The post object
	 *
	 * @param object $object 	the page/post/taxonomy object
	 * @param array $args 		the page/post arguments or taxonomy slug
	 *
	 * @uses $this->get_custom_field() Get custom field value.
	 *
	 * Note: Passed through object $object by reference
	 */
	public function inpost_seo_box( &$object, $args ) {

		//* Determines if it's inside a meta box or within a taxonomy page.
		$nobox = false;

		// Args are passed.
		if ( is_array( $args ) && isset( $args['args'] ) ) {
			$args_split = $args['args'];

			//* The post type callback arg (translated)
			$type = $args_split[0];
			//* The kind of page we're on.
			$page = $args_split[1];

			// Only add nonce on post/page edit screen
			if ( $page === 'is_post_page' ) {
				wp_nonce_field( 'inpost_seo_save', 'hmpl_ad_inpost_seo_nonce' );
			} else {
				// This shouldn't happen.
				return '';
			}
		} else {
			$term = get_term_by( 'id', $object->term_id, $object->taxonomy, OBJECT );

			if ( !empty( $term ) && is_object( $term ) ) {
				$tax_type = $term->taxonomy;

				/**
				 * Dynamically fetch the term name.
				 *
				 * @since 2.3.1
				 */
				$term_labels = $this->get_tax_labels( $tax_type );

				if ( isset( $term_labels ) ) {
					$type = isset( $term_labels->singular_name ) ? $term_labels->singular_name : $term_labels->name;
				} else {
					// Fallback to Page as it is generic.
					$type = __( 'Page', 'autodescription' );
				}

			} else {
				// Fallback to Page as it is generic.
				$type = __( 'Page', 'autodescription' );
			}

			$nobox = true;
		}

		if ( $nobox ) {
			$this->tt_inpost_box( $type, $object );
		} else {
			$this->page_inpost_box( $type );
		}

	}

	/**
	 * Callback function for Taxonomy and Terms inpost box.
	 *
	 * @since 2.3.5
	 *
	 * @param string $type The TT type name.
	 * @param object $object The TT object.
	 */
	public function tt_inpost_box( $type, $object ) {

		do_action( 'the_seo_framework_pre_tt_inpost_box' );

		//* Get the language the Google page should assume.
		$language = $this->google_language();

		$ad_doctitle = isset( $object->admeta['doctitle'] ) ? $object->admeta['doctitle'] : '';
		$ad_description = isset( $object->admeta['description'] ) ? $object->admeta['description'] : '';
		$ad_noindex = isset( $object->admeta['noindex'] ) ? $object->admeta['noindex'] : '';
		$ad_nofollow = isset( $object->admeta['nofollow'] ) ? $object->admeta['nofollow'] : '';
		$ad_noarchive = isset( $object->admeta['noarchive'] ) ? $object->admeta['noarchive'] : '';
		$ad_savedflag = isset( $object->admeta['saved_flag'] ) ? $object->admeta['saved_flag'] : false;
		$flag = $ad_savedflag ? true : false;

		//* Genesis data fetch. This will override our options with Genesis options.
		if ( !$flag && isset( $object->meta ) ) {
			if ( empty( $ad_doctitle ) && isset( $object->meta['doctitle'] ) )
				$ad_doctitle = $object->meta['doctitle'];

			if ( empty( $ad_description ) && isset( $object->meta['description'] ) )
				$ad_description = $object->meta['description'];

			if ( empty( $ad_noindex ) && isset( $object->meta['noindex'] ) )
				$ad_noindex = $object->meta['noindex'];

			if ( empty( $ad_nofollow ) && isset( $object->meta['nofollow'] ) )
				$ad_nofollow = $object->meta['nofollow'];

			if ( empty( $ad_noarchive ) && isset( $object->meta['noarchive'] ) )
				$ad_noarchive = $object->meta['doctitle'];
		}

		//* Fetch Term ID and taxonomy.
		$term_id = $object->term_id;
		$taxonomy = $object->taxonomy;

		$generated_doctitle_args = array(
			'term_id' => $term_id,
			'taxonomy' => $taxonomy,
			'placeholder' => true,
			'meta' => true,
			'get_custom_field' => false
		);

		//* Generate title and description.
		$generated_doctitle = $this->title( '', '', '', $generated_doctitle_args );
		$generated_description = $this->generate_description_from_id( $term_id, $taxonomy, false, false );

		/**
		 * Calculate true Title length
		 *
		 * @since 2.2.4
		 */
		$blog_name = get_bloginfo( 'name', 'display' );

		/**
		 * Separator doesn't matter. Since html_entity_decode is used.
		 * Order doesn't matter either. Since it's just used for length calculation.
		 *
		 * @since 2.3.4
		 */
		$ad_doctitle_len	= ! empty ( $ad_doctitle ) 		? $ad_doctitle . " | " . $blog_name : $generated_doctitle;
		$ad_description_len = ! empty ( $ad_description )	? $ad_description : $generated_description;

		/**
		 * Convert to what Google outputs.
		 *
		 * This will convert e.g. &raquo; to a single length character.
		 * @since 2.3.4
		 */
		$tit_len_parsed = html_entity_decode( $ad_doctitle_len );
		$desc_len_parsed = html_entity_decode( $ad_description_len );

		/**
		 * Generate static placeholder for when title or description is emptied
		 *
		 * @since 2.2.4
		 */
		$doctitle_placeholder = $generated_doctitle;
		$description_placeholder = $generated_description;

		?>
		<h3><?php printf( __( '%s SEO Settings', 'autodescription' ), $type ); ?></h3>

		<table class="form-table">
			<tbody>
				<tr class="form-field">
					<th scope="row" valign="top">
						<label for="autodescription-meta[doctitle]">
							<strong><?php printf( __( '%s Title', 'autodescription' ), $type ); ?></strong>
							<a href="https://support.google.com/webmasters/answer/35624?hl=<?php echo $language; ?>#3" target="_blank" title="<?php _e( 'Recommended Length: 50 to 55 characters', 'autodescription' ) ?>">[?]</a>
						</label>
					</th>
					<td>
						<input name="autodescription-meta[doctitle]" id="autodescription-meta[doctitle]" type="text" placeholder="<?php echo $doctitle_placeholder ?>" value="<?php echo esc_attr( $ad_doctitle ); ?>" size="40" />
						<p class="description"><?php printf( __( 'Characters Used: %s', 'autodescription' ), '<span id="autodescription-meta[doctitle]_chars">'. mb_strlen( $tit_len_parsed ) .'</span>' ); ?></p>
					</td>
				</tr>

				<tr class="form-field">
					<th scope="row" valign="top">
						<label for="autodescription-meta[description]">
							<strong><?php printf( __( '%s Meta Description', 'autodescription' ), $type ); ?></strong>
							<a href="https://support.google.com/webmasters/answer/35624?hl=<?php echo $language; ?>#1" target="_blank" title="<?php _e( 'Recommended Length: 145 to 155 characters', 'autodescription' ) ?>">[?]</a>
						</label>
					</th>
					<td>
						<textarea name="autodescription-meta[description]" id="autodescription-meta[description]" placeholder="<?php echo $description_placeholder ?>" rows="5" cols="50" class="large-text"><?php echo esc_html( $ad_description ); ?></textarea>
						<p class="description"><?php printf( __( 'Characters Used: %s', 'autodescription' ), '<span id="autodescription-meta[description]_chars">'. mb_strlen( $desc_len_parsed ) .'</span>' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row" valign="top"><?php _e( 'Robots Meta Settings', 'autodescription' ); ?></th>
					<td>
						<label for="autodescription-meta[noindex]"><input name="autodescription-meta[noindex]" id="autodescription-meta[noindex]" type="checkbox" value="1" <?php checked( $ad_noindex ); ?> />
							<?php printf( __( 'Apply %s to this %s', 'autodescription' ), $this->code_wrap( 'noindex' ), $type ); ?>
							<a href="https://support.google.com/webmasters/answer/93710?hl=<?php echo $language; ?>" target="_blank" title="<?php printf( __( 'Tell Search Engines not to show this page in their search results', 'autodescription' ) ) ?>">[?]</a>
						</label>

						<br />

						<label for="autodescription-meta[nofollow]"><input name="autodescription-meta[nofollow]" id="autodescription-meta[nofollow]" type="checkbox" value="1" <?php checked( $ad_nofollow ); ?> />
							<?php printf( __( 'Apply %s to this %s', 'autodescription' ), $this->code_wrap( 'nofollow' ), $type ); ?>
							<a href="https://support.google.com/webmasters/answer/96569?hl=<?php echo $language; ?>" target="_blank" title="<?php printf( __( 'Tell Search Engines not to follow links on this page', 'autodescription' ) ) ?>">[?]</a>
						</label>

						<br />

						<label for="autodescription-meta[noarchive]"><input name="autodescription-meta[noarchive]" id="autodescription-meta[noarchive]" type="checkbox" value="1" <?php checked( $ad_noarchive ); ?> />
							<?php printf( __( 'Apply %s to this %s', 'autodescription' ), $this->code_wrap( 'noarchive' ), $type ); ?>
							<a href="https://support.google.com/webmasters/answer/79812?hl=<?php echo $language; ?>" target="_blank" title="<?php printf( __( 'Tell Search Engines not to save a cached copy this page', 'autodescription' ) ) ?>">[?]</a>
						</label>

						<?php // Saved flag, if set then it won't fetch for genesis meta anymore ?>
						<label class="hidden" for="autodescription-meta[saved_flag]">
							<input name="autodescription-meta[saved_flag]" id="autodescription-meta[saved_flag]" type="checkbox" value="1" checked='checked' />
						</label>
					</td>
				</tr>
			</tbody>
		</table>
		<?php

		do_action( 'the_seo_framework_pro_tt_inpost_box' );
	}

	/**
	 * Callback function for Post and Pages inpost metabox.
	 *
	 * @since 2.3.5
	 *
	 * @param string $type The post type name.
	 */
	public function page_inpost_box( $type ) {

		do_action( 'the_seo_framework_pre_page_inpost_box' );

		//* Get the language the Google page should assume.
		$language = $this->google_language();

		/**
		 * Now uses get_queried_object_id()
		 * @since 2.2.8
		 */
		$post_id = get_queried_object_id() ? get_queried_object_id() : get_the_ID();
		$title = $this->get_custom_field( '_genesis_title' );

		$page_on_front_option = get_option( 'page_on_front' );

		/**
		 * Generate static placeholder for when title or description is emptied
		 *
		 * @since 2.2.4
		 *
		 * Fetch description from Home Page SEO Settings placeholder if it exists.
		 * @since 2.2.5
		 *
		 * Generate description for Posts Page if selected in customizer.
		 * @since 2.2.8
		 */
		if ( $post_id == $page_on_front_option && 'page' == get_option( 'show_on_front' ) ) {
			//* Front page.
			$generated_doctitle_args = array(
				'page_on_front' => true,
				'placeholder' => true,
				'meta'	=> true,
				'get_custom_field' => false,
			);

			$generated_doctitle = $this->title( '', '', '', $generated_doctitle_args );
			$generated_description = $this->generate_description_from_id( $post_id, '', true, false );
		} else if ( $this->is_blog_page( $post_id ) ) {
			//* Page for posts.
			$generated_doctitle_args = array(
				'placeholder' => true,
				'meta'	=> true,
				'get_custom_field' => false,
			);

			$generated_doctitle = $this->title( '', '', '', $generated_doctitle_args );
			$generated_description = $this->generate_description_from_id( $post_id, '', false, true );
		} else {
			$generated_doctitle_args = array(
				'placeholder' => true,
				'meta'	=> true,
				'get_custom_field' => false,
			);

			$generated_doctitle = $this->title( '', '', '', $generated_doctitle_args );
			$generated_description = $this->generate_description_from_id( $post_id, '', false, false );
		}

		/**
		 * Special check for home page.
		 *
		 * @since 2.3.4
		 */
		if ( $post_id == $page_on_front_option && ! $this->get_option( 'homepage_tagline' ) ) {
			$tit_len_pre = ! empty( $title ) ? $title : $generated_doctitle;
		} else if ( $post_id == get_option( 'page_on_front' ) ) {
			$tit_len_pre = !empty( $title ) ? $title . " | " . get_bloginfo( 'description', 'raw' ) : $generated_doctitle;
		} else {
			/**
			 * Calculate true Title length
			 *
			 * @since 2.2.4
			 */
			$blog_name = get_bloginfo( 'name', 'display' );

			/**
			 * Separator doesn't matter. Since html_entity_decode is used.
			 * Order doesn't matter either. Since it's just used for length calculation.
			 *
			 * @since 2.3.4
			 */
			$tit_len_pre = ! empty( $title ) ? $title . " | " . $blog_name : $generated_doctitle;
		}

		//* Fetch description from option.
		$description = $this->get_custom_field( '_genesis_description' );

		/**
		 * Calculate current description length
		 *
		 * Reworked.
		 * @since 2.3.4
		 */
		if ( $post_id == $page_on_front_option && 'page' == get_option( 'show_on_front' ) ) {
			//* The homepage description takes precedence.
			$homepage_description = $this->get_option( 'homepage_description' );

			if ( !empty( $description ) ) {
				$desc_len_pre = ! empty( $homepage_description ) ? $homepage_description : $description;
			} else {
				$desc_len_pre = ! empty( $homepage_description ) ? $homepage_description : $generated_description;
			}
		} else {
			$desc_len_pre = ! empty( $description ) ? $description : $generated_description;
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

		?>
		<p>
			<label for="autodescription_title"><strong><?php printf( __( 'Custom %s Title', 'autodescription' ), $type ); ?></strong>
				<a href="https://support.google.com/webmasters/answer/35624?hl=<?php echo $language; ?>#3" target="_blank" title="<?php _e( 'Recommended Length: 50 to 55 characters', 'autodescription' ) ?>">[?]</a>
				<span class="description"><?php printf( __( 'Characters Used: %s', 'autodescription' ), '<span id="autodescription_title_chars">'. mb_strlen( $tit_len_parsed ) .'</span>' ); ?></span>
			</label>
		</p>
		<p>
			<input class="large-text" type="text" name="autodescription[_genesis_title]" id="autodescription_title" placeholder="<?php echo $doctitle_placeholder ?>" value="<?php echo esc_attr( $this->get_custom_field( '_genesis_title' ) ); ?>" />
		</p>

		<p>
			<label for="autodescription_description">
				<strong><?php printf( __( 'Custom %s Description', 'autodescription' ), $type ); ?></strong>
				<a href="https://support.google.com/webmasters/answer/35624?hl=<?php echo $language; ?>#1" target="_blank" title="<?php _e( 'Recommended Length: 145 to 155 characters', 'autodescription' ) ?>">[?]</a>
				<span class="description"><?php printf( __( 'Characters Used: %s', 'autodescription' ), '<span id="autodescription_description_chars">'. mb_strlen( $desc_len_parsed ) .'</span>' ); ?></span>
			</label>
		</p>
		<p>
			<textarea class="large-text" name="autodescription[_genesis_description]" id="autodescription_description" placeholder="<?php echo $description_placeholder ?>" rows="4" cols="4"><?php echo esc_textarea( $this->get_custom_field( '_genesis_description' ) ); ?></textarea>
		</p>

		<p>
			<label for="autodescription_canonical">
				<strong><?php _e( 'Custom Canonical URL', 'autodescription' ); ?></strong>
				<a href="https://support.google.com/webmasters/answer/139066?hl=<?php echo $language; ?>" target="_blank" title="&lt;link rel=&quot;canonical&quot; /&gt;">[?]</a>
			</label>
		</p>
		<p>
			<input class="large-text" type="text" name="autodescription[_genesis_canonical_uri]" id="autodescription_canonical" placeholder="<?php echo $canonical_placeholder ?>" value="<?php echo esc_url( $this->get_custom_field( '_genesis_canonical_uri' ) ); ?>" />
		</p>

		<p><strong><?php _e( 'Robots Meta Settings', 'autodescription' ); ?></strong></p>
		<p>
			<label for="autodescription_noindex"><input type="checkbox" name="autodescription[_genesis_noindex]" id="autodescription_noindex" value="1" <?php checked( $this->get_custom_field( '_genesis_noindex' ) ); ?> />
				<?php printf( __( 'Apply %s to this %s', 'autodescription' ), $this->code_wrap( 'noindex' ), $type ); ?>
				<a href="https://support.google.com/webmasters/answer/93710?hl=<?php echo $language; ?>" target="_blank" title="<?php printf( __( 'Tell Search Engines not to show this page in their search results', 'autodescription' ) ) ?>">[?]</a>
			</label>

			<br />

			<label for="autodescription_nofollow"><input type="checkbox" name="autodescription[_genesis_nofollow]" id="autodescription_nofollow" value="1" <?php checked( $this->get_custom_field( '_genesis_nofollow' ) ); ?> />
				<?php printf( __( 'Apply %s to this %s', 'autodescription' ), $this->code_wrap( 'nofollow' ), $type ); ?>
				<a href="https://support.google.com/webmasters/answer/96569?hl=<?php echo $language; ?>" target="_blank" title="<?php printf( __( 'Tell Search Engines not to follow links on this page', 'autodescription' ) ) ?>">[?]</a>
			</label>

			<br />

			<label for="autodescription_noarchive"><input type="checkbox" name="autodescription[_genesis_noarchive]" id="autodescription_noarchive" value="1" <?php checked( $this->get_custom_field( '_genesis_noarchive' ) ); ?> />
				<?php printf( __( 'Apply %s to this %s', 'autodescription' ), $this->code_wrap( 'noarchive' ), $type ); ?>
				<a href="https://support.google.com/webmasters/answer/79812?hl=<?php echo $language; ?>" target="_blank" title="<?php printf( __( 'Tell Search Engines not to save a cached copy this page', 'autodescription' ) ) ?>">[?]</a>
			</label>

			<?php // Saved flag, if set then it won't fetch for genesis meta anymore ?>
			<label class="hidden" for="autodescription_saved_flag">
				<input name="autodescription[saved_flag]" id="autodescription[saved_flag]" type="checkbox" value="1" checked='checked' />
			</label>
		</p>

		<p><strong><?php _e( 'Local Search Settings', 'autodescription' ); ?></strong></p>
		<p>
			<label for="autodescription_exclude_local_search"><input type="checkbox" name="autodescription[exclude_local_search]" id="autodescription_exclude_local_search" value="1" <?php checked( $this->get_custom_field( 'exclude_local_search' ) ); ?> />
				<?php printf( __( 'Exclude this %s from local search', 'autodescription' ), $type ); ?>
				<span title="<?php printf( __( 'This excludes this %s from local on-site search results.', 'autodescription' ), $type ) ?>">[?]</a>
			</label>
		</p>

		<p>
			<label for="autodescription_redirect">
				<strong><?php _e( 'Custom 301 Redirect URL', 'autodescription' ); ?></strong>
				<a href="https://support.google.com/webmasters/answer/93633?hl=<?php echo $language; ?>" target="_blank" title="301 Redirect">[?]</a>
			</label>
		</p>
		<p>
			<input class="large-text" type="text" name="autodescription[redirect]" id="genesis_redirect" value="<?php echo esc_url( $this->get_custom_field( 'redirect' ) ); ?>" />
		</p>
		<?php

		do_action( 'the_seo_framework_pro_page_inpost_box' );
	}

}
