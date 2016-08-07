<?php
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

defined( 'ABSPATH' ) or die;

/**
 * Class AutoDescription_Inpost
 *
 * Outputs Taxonomy, Post and Page meta boxes
 *
 * @since 2.2.2
 */
class AutoDescription_Inpost extends AutoDescription_DoingItRight {

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
	 * Unserializing instances of this class is forbidden.
	 */
	private function __wakeup() { }

	/**
	 * Handle unapproachable invoked methods.
	 */
	public function __call( $name, $arguments ) {
		parent::__call( $name, $arguments );
	}

	/**
	 * Constructor, load parent constructor
	 */
	public function __construct() {
		parent::__construct();

		$this->inpost_nonce_name = 'the_seo_framework_inpost_seo_settings';
		$this->inpost_nonce_field = 'the_seo_framework_inpost';

		//* Enqueue Inpost meta boxes.
		add_action( 'add_meta_boxes', array( $this, 'add_inpost_seo_box_init' ), 5 );

		//* Enqueue Taxonomy meta output.
		add_action( 'current_screen', array( $this, 'add_taxonomy_seo_box_init' ), 10 );

		/**
		 * Applies filters bool|string the_seo_framework_inpost_seo_bar :
		 * Whether to output the SEO bar within the inpost SEO Settings metabox.
		 * @param 	: string 'above' Outputs it above the Settings
		 * 			: string 'below' Outputs it below the Settings
		 * 			: bool false No output.
		 * @since 2.5.2
		 */
		$this->inpost_seo_bar = apply_filters( 'the_seo_framework_inpost_seo_bar', false );

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
		$show_seobox = (bool) apply_filters( 'the_seo_framework_seobox_output', true );

		if ( $show_seobox )
			add_action( 'add_meta_boxes', array( $this, 'add_inpost_seo_box' ), 10, 1 );

	}

	/**
	 * Adds SEO Meta boxes within Taxonomy screens.
	 *
	 * @since 2.1.8
	 * @since 2.6.0 Can no longer run outside of the term edit scope.
	 * @since 2.6.0 Can no longer run when another SEO plugin is active.
	 */
	public function add_taxonomy_seo_box_init() {

		if ( $this->detect_seo_plugins() || ! $this->is_term_edit() )
			return;

		/**
		 * High priority, this box is seen right below the post/page edit screen.
		 * Applies filters 'the_seo_framework_term_metabox_priority' : int
		 *
		 * @since 2.6.0
		 */
		$priority = (int) apply_filters( 'the_seo_framework_term_metabox_priority', 0 );

		//* Add taxonomy meta boxes
		foreach ( get_taxonomies( array( 'public' => true ) ) as $tax_name )
			add_action( $tax_name . '_edit_form', array( $this, 'pre_seo_box' ), $priority, 2 );

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

			$post = get_post_type_object( $post_type );

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
					$id = (string) apply_filters( 'the_seo_framework_metabox_id', 'tsf-inpost-box' );
					$context = 'normal';

					/**
					 * High priority, this box is seen right below the post/page edit screen.
					 * Applies filters 'the_seo_framework_metabox_priority' : string
					 * Accepts 'high', 'default', 'low'
					 * @since 2.6.0
					 */
					$priority = (string) apply_filters( 'the_seo_framework_metabox_priority', 'high' );

					add_meta_box( $id, sprintf( __( '%s SEO Settings', 'autodescription' ), $title ), array( $this, 'pre_seo_box' ), $post_type, $context, $priority, $args );
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
	 * @param $args the page/post arguments or taxonomy slug.
	 *
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
			//* Note: Passes object.
			// Empty the arguments, if any.
			return $this->inpost_seo_box( $object, $args = '' );
		}

		return '';
	}

	/**
	 * Callback for in-post SEO meta box.
	 *
	 * @since 2.0.0
	 * @access private
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

			// Only add nonce on post/page edit screen
			if ( 'is_post_page' === $page ) {
				wp_nonce_field( $this->inpost_nonce_field, $this->inpost_nonce_name );
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
				$type = __( 'Page', 'autodescription' );
			}

			$is_term = true;
		}

		//* Echo output.
		if ( $is_term ) {
			$this->tt_inpost_box( $type, $object );
		} else {
			$this->page_inpost_box( $type );
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

		do_action( 'the_seo_framework_pre_tt_inpost_box' );

		//* Get the language the Google page should assume.
		$language = $this->google_language();

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
		$description_len = $description	? $description : $generated_description;

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
		<h3><?php printf( esc_html__( '%s SEO Settings', 'autodescription' ), $type ); ?></h3>

		<table class="form-table">
			<tbody>

				<?php if ( 'above' === $this->inpost_seo_bar ) : ?>
				<tr>
					<th scope="row" valign="top"><?php esc_html_e( 'Doing it Right', 'autodescription' ); ?></th>
					<td>
						<?php echo $this->post_status( $term_id, $taxonomy, true ); ?>
					</td>
				</tr>
				<?php endif; ?>

				<tr class="form-field">
					<th scope="row" valign="top">
						<label for="autodescription-meta[doctitle]">
							<strong><?php printf( esc_html__( '%s Title', 'autodescription' ), $type ); ?></strong>
							<a href="https://support.google.com/webmasters/answer/35624?hl=<?php echo $language; ?>#3" target="_blank" title="<?php esc_html_e( 'Recommended Length: 50 to 55 characters', 'autodescription' ) ?>">[?]</a>
						</label>
					</th>
					<td>
						<div id="tsf-title-wrap">
							<input name="autodescription-meta[doctitle]" id="autodescription-meta[doctitle]" type="text" placeholder="<?php echo $title_placeholder ?>" value="<?php echo esc_attr( $title ); ?>" size="40" />
							<span id="tsf-title-offset" class="hide-if-no-js"></span><span id="tsf-title-placeholder" class="hide-if-no-js"></span>
						</div>
						<p class="description tsf-counter">
							<?php printf( esc_html__( 'Characters Used: %s', 'autodescription' ), '<span id="autodescription-meta[doctitle]_chars">'. mb_strlen( $tit_len_parsed ) .'</span>' ); ?>
							<span class="hide-if-no-js tsf-ajax"></span>
						</p>
					</td>
				</tr>

				<tr class="form-field">
					<th scope="row" valign="top">
						<label for="autodescription-meta[description]">
							<strong><?php printf( esc_html__( '%s Meta Description', 'autodescription' ), $type ); ?></strong>
							<a href="https://support.google.com/webmasters/answer/35624?hl=<?php echo $language; ?>#1" target="_blank" title="<?php esc_html_e( 'Recommended Length: 145 to 155 characters', 'autodescription' ) ?>">[?]</a>
						</label>
					</th>
					<td>
						<textarea name="autodescription-meta[description]" id="autodescription-meta[description]" placeholder="<?php echo $description_placeholder ?>" rows="5" cols="50" class="large-text"><?php echo esc_html( $description ); ?></textarea>
						<p class="description tsf-counter">
							<?php printf( esc_html__( 'Characters Used: %s', 'autodescription' ), '<span id="autodescription-meta[description]_chars">'. mb_strlen( $desc_len_parsed ) .'</span>' ); ?>
							<span class="hide-if-no-js tsf-ajax"></span>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row" valign="top"><?php esc_html_e( 'Robots Meta Settings', 'autodescription' ); ?></th>
					<td>
						<label for="autodescription-meta[noindex]"><input name="autodescription-meta[noindex]" id="autodescription-meta[noindex]" type="checkbox" value="1" <?php checked( $noindex ); ?> />
							<?php printf( esc_html__( 'Apply %s to this term?', 'autodescription' ), $this->code_wrap( 'noindex' ) ); ?>
							<a href="https://support.google.com/webmasters/answer/93710?hl=<?php echo $language; ?>" target="_blank" title="<?php printf( esc_html__( 'Tell Search Engines not to show this page in their search results', 'autodescription' ) ) ?>">[?]</a>
						</label>

						<br>

						<label for="autodescription-meta[nofollow]"><input name="autodescription-meta[nofollow]" id="autodescription-meta[nofollow]" type="checkbox" value="1" <?php checked( $nofollow ); ?> />
							<?php printf( esc_html__( 'Apply %s to this term?', 'autodescription' ), $this->code_wrap( 'nofollow' ) ); ?>
							<a href="https://support.google.com/webmasters/answer/96569?hl=<?php echo $language; ?>" target="_blank" title="<?php printf( esc_html__( 'Tell Search Engines not to follow links on this page', 'autodescription' ) ) ?>">[?]</a>
						</label>

						<br>

						<label for="autodescription-meta[noarchive]"><input name="autodescription-meta[noarchive]" id="autodescription-meta[noarchive]" type="checkbox" value="1" <?php checked( $noarchive ); ?> />
							<?php printf( esc_html__( 'Apply %s to this term?', 'autodescription' ), $this->code_wrap( 'noarchive' ) ); ?>
							<a href="https://support.google.com/webmasters/answer/79812?hl=<?php echo $language; ?>" target="_blank" title="<?php printf( esc_html__( 'Tell Search Engines not to save a cached copy of this page', 'autodescription' ) ) ?>">[?]</a>
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
						<?php echo $this->post_status( $term_id, $taxonomy, true ); ?>
					</td>
				</tr>
				<?php endif; ?>

			</tbody>
		</table>
		<?php

		do_action( 'the_seo_framework_pro_tt_inpost_box' );

	}

	/**
	 * Callback function for Post and Pages inpost metabox.
	 *
	 * @since 2.3.5
	 * @access private
	 *
	 * @param string $type The post type name.
	 */
	public function page_inpost_box( $type ) {

		do_action( 'the_seo_framework_pre_page_inpost_box' );

		//* Get the language the Google page should assume.
		$language = $this->google_language();

		$post_id = $this->get_the_real_ID();
		$is_static_frontpage = $this->is_static_frontpage( $post_id );

		$title = $this->get_custom_field( '_genesis_title', $post_id );

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
		if ( $is_static_frontpage ) {
			//* Front page.
			$generated_doctitle_args = array(
				'page_on_front' => true,
				'placeholder' => true,
				'meta'	=> true,
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
				'meta'	=> true,
				'get_custom_field' => false,
			);

			$generated_description_args = array(
				'id' => $post_id,
				'page_for_posts' => true,
			);
		} else {
			$generated_doctitle_args = array(
				'placeholder' => true,
				'meta'	=> true,
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
			if ( $this->get_option( 'homepage_tagline' ) )
				$tit_len_pre = $title ? $title . " | " . $this->get_blogdescription() : $generated_doctitle;
			else
				$tit_len_pre = $title ? $title : $generated_doctitle;
		} else {
			/**
			 * Separator doesn't matter. Since html_entity_decode is used.
			 * Order doesn't matter either. Since it's just used for length calculation.
			 *
			 * @since 2.3.4
			 */
			if ( $this->add_title_additions() )
				$tit_len_pre = $title ? $title . " | " . $this->get_blogname() : $generated_doctitle;
			else
				$tit_len_pre = $title ? $title : $generated_doctitle;
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
			$homepage_description = $this->get_option( 'homepage_description' );

			if ( $description )
				$desc_len_pre = $homepage_description ? $homepage_description : $description;
			else
				$desc_len_pre = $homepage_description ? $homepage_description : $generated_description;
		} else {
			$desc_len_pre = $description ? $description : $generated_description;
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
		<?php if ( 'above' === $this->inpost_seo_bar ) : ?>
		<p>
			<strong><?php esc_html_e( 'Doing it Right', 'autodescription' ); ?></strong>
			<div><?php echo $this->post_status( $post_id, 'inpost', true ); ?></div>
		</p>
		<?php endif; ?>

		<p>
			<label for="autodescription_title"><strong><?php printf( esc_html__( 'Custom %s Title', 'autodescription' ), $type ); ?></strong>
				<a href="https://support.google.com/webmasters/answer/35624?hl=<?php echo $language; ?>#3" target="_blank" title="<?php esc_html_e( 'Recommended Length: 50 to 55 characters', 'autodescription' ); ?>">[?]</a>
				<span class="description tsf-counter">
					<?php printf( esc_html__( 'Characters Used: %s', 'autodescription' ), '<span id="autodescription_title_chars">'. mb_strlen( $tit_len_parsed ) .'</span>' ); ?>
					<span class="hide-if-no-js tsf-ajax"></span>
				</span>
			</label>
		</p>
		<p>
			<div id="tsf-title-wrap">
				<input class="large-text" type="text" name="autodescription[_genesis_title]" id="autodescription_title" placeholder="<?php echo $doctitle_placeholder ?>" value="<?php echo esc_attr( $this->get_custom_field( '_genesis_title' ) ); ?>" />
				<span id="tsf-title-offset" class="hide-if-no-js"></span><span id="tsf-title-placeholder" class="hide-if-no-js"></span>
			</div>
		</p>

		<p>
			<label for="autodescription_description">
				<strong><?php printf( esc_html__( 'Custom %s Description', 'autodescription' ), $type ); ?></strong>
				<a href="https://support.google.com/webmasters/answer/35624?hl=<?php echo $language; ?>#1" target="_blank" title="<?php esc_html_e( 'Recommended Length: 145 to 155 characters', 'autodescription' ); ?>">[?]</a>
				<span class="description tsf-counter">
					<?php printf( esc_html__( 'Characters Used: %s', 'autodescription' ), '<span id="autodescription_description_chars">'. mb_strlen( $desc_len_parsed ) .'</span>' ); ?>
					<span class="hide-if-no-js tsf-ajax"></span>
				</span>
			</label>
		</p>
		<p>
			<textarea class="large-text" name="autodescription[_genesis_description]" id="autodescription_description" placeholder="<?php echo $description_placeholder ?>" rows="4" cols="4"><?php echo esc_textarea( $this->get_custom_field( '_genesis_description' ) ); ?></textarea>
		</p>

		<p>
			<label for="autodescription_canonical">
				<strong><?php esc_html_e( 'Custom Canonical URL', 'autodescription' ); ?></strong>
				<a href="https://support.google.com/webmasters/answer/139066?hl=<?php echo $language; ?>" target="_blank" title="<?php printf( esc_html__( 'Preferred %s URL location', 'autodescription' ), $type ); ?>">[?]</a>
			</label>
		</p>
		<p>
			<input class="large-text" type="text" name="autodescription[_genesis_canonical_uri]" id="autodescription_canonical" placeholder="<?php echo $canonical_placeholder ?>" value="<?php echo esc_url( $this->get_custom_field( '_genesis_canonical_uri' ) ); ?>" />
		</p>

		<p><strong><?php esc_html_e( 'Robots Meta Settings', 'autodescription' ); ?></strong></p>
		<p>
			<label for="autodescription_noindex"><input type="checkbox" name="autodescription[_genesis_noindex]" id="autodescription_noindex" value="1" <?php checked( $this->get_custom_field( '_genesis_noindex' ) ); ?> />
				<?php
					/* translators: 1: Option, 2: Post or Page */
					printf( esc_html__( 'Apply %1$s to this %2$s', 'autodescription' ), $this->code_wrap( 'noindex' ), $type );
				?>
				<a href="https://support.google.com/webmasters/answer/93710?hl=<?php echo $language; ?>" target="_blank" title="<?php printf( esc_html__( 'Tell Search Engines not to show this %s in their search results', 'autodescription' ), $type ); ?>">[?]</a>
			</label>

			<br>

			<label for="autodescription_nofollow"><input type="checkbox" name="autodescription[_genesis_nofollow]" id="autodescription_nofollow" value="1" <?php checked( $this->get_custom_field( '_genesis_nofollow' ) ); ?> />
				<?php
					/* translators: 1: Option, 2: Post or Page */
					printf( esc_html__( 'Apply %1$s to this %2$s', 'autodescription' ), $this->code_wrap( 'nofollow' ), $type );
				?>
				<a href="https://support.google.com/webmasters/answer/96569?hl=<?php echo $language; ?>" target="_blank" title="<?php printf( esc_html__( 'Tell Search Engines not to follow links on this %s', 'autodescription' ), $type ); ?>">[?]</a>
			</label>

			<br>

			<label for="autodescription_noarchive"><input type="checkbox" name="autodescription[_genesis_noarchive]" id="autodescription_noarchive" value="1" <?php checked( $this->get_custom_field( '_genesis_noarchive' ) ); ?> />
				<?php
					/* translators: 1: Option, 2: Post or Page */
					printf( esc_html__( 'Apply %1$s to this %2$s', 'autodescription' ), $this->code_wrap( 'noarchive' ), $type );
				?>
				<a href="https://support.google.com/webmasters/answer/79812?hl=<?php echo $language; ?>" target="_blank" title="<?php printf( esc_html__( 'Tell Search Engines not to save a cached copy of this %s', 'autodescription' ), $type ); ?>">[?]</a>
			</label>
		</p>

		<p><strong><?php esc_html_e( 'Local Search Settings', 'autodescription' ); ?></strong></p>
		<p>
			<label for="autodescription_exclude_local_search"><input type="checkbox" name="autodescription[exclude_local_search]" id="autodescription_exclude_local_search" value="1" <?php checked( $this->get_custom_field( 'exclude_local_search' ) ); ?> />
				<?php printf( esc_html__( 'Exclude this %s from local search', 'autodescription' ), $type ); ?>
				<span title="<?php printf( esc_html__( 'This excludes this %s from local on-site search results', 'autodescription' ), $type ); ?>">[?]</span>
			</label>
		</p>

		<p>
			<label for="autodescription_redirect">
				<strong><?php esc_html_e( 'Custom 301 Redirect URL', 'autodescription' ); ?></strong>
				<a href="https://support.google.com/webmasters/answer/93633?hl=<?php echo $language; ?>" target="_blank" title="<?php esc_html_e( 'This will force visitors to go to another URL', 'autodescription' ); ?>">[?]</a>
			</label>
		</p>
		<p>
			<input class="large-text" type="text" name="autodescription[redirect]" id="genesis_redirect" value="<?php echo esc_url( $this->get_custom_field( 'redirect' ) ); ?>" />
		</p>

		<?php if ( 'below' === $this->inpost_seo_bar ) : ?>
		<p>
			<strong><?php esc_html_e( 'Doing it Right', 'autodescription' ); ?></strong>
			<div><?php echo $this->post_status( $post_id, 'inpost', true ); ?></div>
		</p>
		<?php endif;

		do_action( 'the_seo_framework_pro_page_inpost_box' );

	}
}
