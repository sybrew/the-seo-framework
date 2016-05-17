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

/**
 * Class AutoDescription_Metaboxes
 *
 * Outputs Network and Site SEO settings meta boxes
 *
 * @since 2.2.2
 */
class AutoDescription_Metaboxes extends AutoDescription_Siteoptions {

	/**
	 * Constructor, load parent constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * List of title separators.
	 *
	 * @since 2.6.0
	 *
	 * @return array Title separators.
	 */
	public function get_separator_list() {
		return array(
			'pipe'		=> '|',
			'dash'		=> '-',
			'ndash'		=> '&ndash;',
			'mdash'		=> '&mdash;',
			'bull'		=> '&bull;',
			'middot'	=> '&middot;',
			'lsaquo'	=> '&lsaquo;',
			'rsaquo'	=> '&rsaquo;',
			'frasl'		=> '&frasl;',
			'laquo'		=> '&laquo;',
			'raquo'		=> '&raquo;',
			'le'		=> '&le;',
			'ge'		=> '&ge;',
			'lt'		=> '&lt;',
			'gt'		=> '&gt;',
		);
	}

	/**
	 * Returns array of Twitter Card Types
	 *
	 * @since 2.6.0
	 *
	 * @return array Twitter Card types.
	 */
	public function get_twitter_card_types() {
		return array(
			'summary' 				=> 'summary',
			'summary_large_image'	=> 'summary-large-image',
			'photo' 				=> 'photo',
		);
	}

	/**
	 * Setting nav tab wrappers.
	 * Outputs Tabs and settings content.
	 *
	 * @param string $id The Nav Tab ID
	 * @param array $tabs the tab content {
	 *		$tabs = tab ID key = array(
	 *			$tabs['name'] => tab name
	 *			$tabs['callback'] => string|array callback function
	 *			$tabs['dashicon'] => string Dashicon
	 *			$tabs['args'] => mixed optional callback function args
	 *		)
	 *	}
	 * @param string $version the The SEO Framework version for debugging. May be emptied.
	 * @param bool $use_tabs Whether to output tabs, only works when $tabs only has one count.
	 *
	 * @since 2.3.6
	 *
	 * @refactored
	 * @since 2.6.0
	 */
	public function nav_tab_wrapper( $id, $tabs = array(), $version = '2.3.6', $use_tabs = true ) {

		//* Whether tabs are active.
		$use_tabs = $use_tabs || count( $tabs ) > 1 ? true : false;

		/**
		 * Start navigation.
		 *
		 * Don't output navigation if $use_tabs is false and the amount of tabs is 1 or lower.
		 */
		if ( $use_tabs ) {
			?>
			<div class="seoframework-nav-tab-wrapper hide-if-no-js" id="<?php echo $id; ?>-tabs-wrapper">
			<?php
				$count = 1;
				foreach ( $tabs as $tab => $value ) {

					$dashicon = isset( $value['dashicon'] ) ? $value['dashicon'] : '';
					$name = isset( $value['name'] ) ? $value['name'] : '';

					$checked = 1 === $count ? 'checked' : '';
					$the_id = $id . '-tab-' . $tab;
					$the_name = $id . '-tabs';

					$label_class = $checked ? ' seoframework-active-tab' : ''; // maybe

					?>
					<div class="seoframework-tab">
						<input type="radio" class="seoframework-tabs-radio" id="<?php echo $the_id ?>" name="<?php echo $the_name ?>" <?php echo $checked ?>>
						<label for="<?php echo $the_id; ?>" class="seoframework-nav-tab">
							<?php echo $dashicon ? '<span class="dashicons dashicons-' . esc_attr( $dashicon ) . ' seoframework-dashicons-tabs"></span>' : ''; ?>
							<?php echo $name ? '<span class="seoframework-nav-desktop">' . esc_attr( $name ) . '</span>' : ''; ?>
						</label>
					</div>
					<?php

					$count++;
				}
			?>
			</div>
			<?php
		}

		/**
		 * Start Content.
		 *
		 * The content is relative to the navigation, and uses CSS to become visible.
		 */
		$count = 1;
		foreach ( $tabs as $tab => $value ) {

			$the_id = $id . '-tab-' . $tab . '-content';
			$the_name = $id . '-tabs-content';

			//* Current tab for JS.
			$current = 1 === $count ? ' seoframework-active-tab-content' : '';

			?>
			<div class="seoframework-tabs-content <?php echo $the_name . $current; ?>" id="<?php echo $the_id; ?>" >
			<?php

				//* No-JS tabs.
				if ( $use_tabs ) {
					$dashicon = isset( $value['dashicon'] ) ? $value['dashicon'] : '';
					$name = isset( $value['name'] ) ? $value['name'] : '';

					?>
					<div class="hide-if-js seoframework-content-no-js">
						<div class="seoframework-tab seoframework-tab-no-js">
							<span class="seoframework-nav-tab seoframework-active-tab">
								<?php echo $dashicon ? '<span class="dashicons dashicons-' . esc_attr( $dashicon ) . ' seoframework-dashicons-tabs"></span>' : ''; ?>
								<?php echo $name ? '<span>' . esc_attr( $name ) . '</span>' : ''; ?>
							</span>
						</div>
					</div>
					<?php
				}

				$callback = isset( $value['callback'] ) ? $value['callback'] : '';

				if ( $callback ) {
					$params = isset( $value['args'] ) ? $value['args'] : '';
					$output = $this->call_function( $callback, $version, $params );
					echo $output;
				}

				?>
			</div>
			<?php

			$count++;
		}

	}

	/**
	 * Title meta box on the Site SEO Settings page.
	 *
	 * @since 2.2.2
	 *
	 * @param array $args The metabox arguments.
	 *
	 * @see $this->title_metabox()	Callback for Title Settings box.
	 */
	public function title_metabox( $args = array() ) {

		do_action( 'the_seo_framework_title_metabox_before' );

		$latest_post_id = $this->get_latest_post_id();

		if ( $latest_post_id ) {
			$post = get_post( $latest_post_id, OBJECT );
			$title = esc_attr( $post->post_title );
		} else {
			$title = esc_attr__( 'Example Post Title', 'autodescription' );
		}

		$blogname = $this->get_blogname();
		$sep = $this->get_separator( 'title', true );

		$additions_left = '<span class="title-additions-js">' . $blogname . '<span class="autodescription-sep-js">' . " $sep " . '</span></span>';
		$additions_right = '<span class="title-additions-js"><span class="autodescription-sep-js">' . " $sep " . '</span>' . $blogname . '</span>';

		$example_left = '<em>' . $additions_left . $title . '</em>';
		$example_right = '<em>' . $title . $additions_right . '</em>';

		$showleft = 'left' === $this->get_option( 'title_location' ) ? true : false;
		//* Check left first, as right is default (and thus fallback).
		$example_nojs = $showleft ? $example_left : $example_right;

		?>
		<h4><?php printf( __( 'Automated Title Settings', 'autodescription' ) ); ?></h4>
		<p><span class="description"><?php printf( __( "The page title is prominently shown within the browser tab as well as within the Search Engine results pages.", 'autodescription' ) ); ?></span></p>

		<h4><?php _e( 'Example Automated Title Output', 'autodescription' ); ?></h4>
		<p>
			<span class="title-additions-example-left" style="display:<?php echo $showleft ? 'inline' : 'none'; ?>"><?php echo $this->code_wrap_noesc( $example_left ); ?></span>
			<span class="title-additions-example-right" style="display:<?php echo $showleft ? 'none' : 'inline'; ?>"><?php echo $this->code_wrap_noesc( $example_right ); ?></span>
		</p>

		<hr>
		<?php

		/**
		 * Parse tabs content
		 *
		 * @param array $default_tabs { 'id' = The identifier =>
		 *			array(
		 *				'name' 		=> The name
		 *				'callback' 	=> The callback function, use array for method calling (accepts $this, but isn't used here for optimization purposes)
		 *				'dashicon'	=> Desired dashicon
		 *			)
		 * }
		 *
		 * @since 2.2.2
		 */
		$default_tabs = array(
			'general' => array(
				'name' 		=> __( 'General', 'autodescription' ),
				'callback'	=> array( $this, 'title_metabox_general_tab' ),
				'dashicon'	=> 'admin-generic',
			),
			'additions' => array(
				'name'		=> __( 'Additions', 'autodescription' ),
				'callback'	=> array( $this, 'title_metabox_additions_tab' ),
				'dashicon'	=> 'plus',
				'args'		=> array(
					'examples' => array(
						'left'	=> $example_left,
						'right' => $example_right,
					),
				),
			),
			'prefixes' => array(
				'name'		=> __( 'Prefixes', 'autodescription' ),
				'callback'	=> array( $this, 'title_metabox_prefixes_tab' ),
				'dashicon'	=> 'plus-alt',
				'args'		=> array(
					'additions' => array(
						'left'	=> $additions_left,
						'right' => $additions_right,
					),
					'showleft' => $showleft,
				),
			)
		);

		/**
		 * Applies filters the_seo_framework_title_settings_tabs : array see $default_tabs
		 * @since 2.6.0
		 *
		 * Used to extend Description tabs.
		 */
		$defaults = (array) apply_filters( 'the_seo_framework_title_settings_tabs', $default_tabs, $args );

		$tabs = wp_parse_args( $args, $defaults );

		$this->nav_tab_wrapper( 'title', $tabs, '2.6.0' );

		do_action( 'the_seo_framework_title_metabox_after' );
	}

	/**
	 * Title meta box general tab.
	 *
	 * @since 2.6.0
	 *
	 * @see $this->title_metabox() : Callback for Title Settings box.
	 */
	public function title_metabox_general_tab() {

		$title_separator = $this->get_separator_list();

		$recommended = ' class="recommended" title="' . esc_attr__( 'Recommended', 'autodescription' ) . '"';

		?>
		<fieldset>
			<legend><h4><?php _e( 'Document Title Separator', 'autodescription' ); ?></h4></legend>
			<p id="title-separator" class="theseoframework-fields">
			<?php foreach ( $title_separator as $name => $html ) { ?>
				<input type="radio" name="<?php $this->field_name( 'title_seperator' ); ?>" id="<?php $this->field_id( 'title_seperator_' . $name ); ?>" value="<?php echo $name ?>" <?php checked( $this->get_field_value( 'title_seperator' ), $name ); ?> />
				<label for="<?php $this->field_id( 'title_seperator_' . $name ); ?>" <?php echo ( $name === 'pipe' || $name === 'dash' ) ? $recommended : ''; ?>><?php echo $html ?></label>
			<?php } ?>
			</p>
			<span class="description"><?php _e( 'If the title consists of two parts (original title and optional addition), then the separator will go in-between them.', 'autodescription' ); ?></span>
		</fieldset>
		<?php

	}

	/**
	 * Title meta box general tab.
	 *
	 * @since 2.6.0
	 *
	 * @param array $examples : array {
	 * 		'left'	=> Left Example
	 * 		'right'	=> Right Example
	 * }
	 *
	 * @see $this->title_metabox() : Callback for Title Settings box.
	 */
	public function title_metabox_additions_tab( $examples = array() ) {

		$example_left = $examples['left'];
		$example_right = $examples['right'];

		$language = $this->google_language();

		$home_page_has_option = __( 'The Home Page has a specific option.', 'autodescription' );

		?>
		<fieldset>
			<legend><h4><?php _e( 'Document Title Additions Location', 'autodescription' ); ?></h4></legend>

			<p>
				<span class="description"><?php _e( 'Determines which side the added title text will go on.', 'autodescription' ); ?></span>
			</p>
			<p id="title-location" class="theseoframework-fields">
				<span class="toblock">
					<input type="radio" name="<?php $this->field_name( 'title_location' ); ?>" id="<?php $this->field_id( 'title_location_left' ); ?>" value="left" <?php checked( $this->get_field_value( 'title_location' ), 'left' ); ?> />
					<label for="<?php $this->field_id( 'title_location_left' ); ?>">
						<span><?php _e( 'Left:', 'autodescription' ); ?></span>
						<?php echo $this->code_wrap_noesc( $example_left ) ?>
					</label>
				</span>
				<span class="toblock">
					<input type="radio" name="<?php $this->field_name( 'title_location' ); ?>" id="<?php $this->field_id( 'title_location_right' ); ?>" value="right" <?php checked( $this->get_field_value( 'title_location' ), 'right' ); ?> />
					<label for="<?php $this->field_id( 'title_location_right' ); ?>">
						<span><?php _e( 'Right:', 'autodescription' ); ?></span>
						<?php echo $this->code_wrap_noesc( $example_right ); ?>
					</label>
				</span>
			</p>
			<span class="description"><?php echo $home_page_has_option; ?></span>
		</fieldset>
		<?php

		/**
		 * @todo use checkbox function
		 * @priority low 2.6.x
		 */

		//* Only add this option if the theme is doing it right.
		if ( $this->can_manipulate_title() ) : ?>
			<hr>

			<h4><?php _e( 'Remove Blogname from Title', 'autodescription' ); ?></h4>
			<p id="title-additions-toggle">
				<label for="<?php $this->field_id( 'title_rem_additions' ); ?>">
					<input type="checkbox" name="<?php $this->field_name( 'title_rem_additions' ); ?>" id="<?php $this->field_id( 'title_rem_additions' ); ?>" <?php $this->is_conditional_checked( 'title_rem_additions' ); ?> value="1" <?php checked( $this->get_field_value( 'title_rem_additions' ) ); ?> />
					<?php _e( 'Remove Blogname from title?', 'autodescription' ); ?>
				</label>
				<a href="<?php echo esc_url( 'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#3' ); ?>" target="_blank" title="<?php _e( 'This might decouple your posts and pages from the rest of the website.', 'autodescription' ); ?>">[?]</a>
			</p>
			<span class="description"><?php _e( 'Only use this option if you are aware of its SEO effects.', 'autodescription' ); ?></span>
			<span class="description"><?php echo $home_page_has_option; ?></span>
		<?php endif;

	}

	/**
	 * Title meta box prefixes tab.
	 *
	 * @since 2.6.0
	 *
<<<<<<< HEAD
	 * @see $this->description_metabox()	Callback for Description Settings box.
	 */
	public function description_metabox() {
=======
	 * @param array $additions : array {
	 * 		'left'	=> Left Example Addtitions
	 * 		'right'	=> Right Example Additions
	 * }
	 * @param bool $showleft The example location.
	 *
	 * @see $this->title_metabox() : Callback for Title Settings box.
	 */
	public function title_metabox_prefixes_tab( $additions = array(), $showleft = false ) {
>>>>>>> ef405fe90ddfcedfe3f7898dcde7198f4eccf621

		$left_additions = $additions['left'];
		$right_additions = $additions['right'];

		//* Get translated category label, if it exists. Otherwise, fallback to translation.
		$term_labels = $this->get_tax_labels( 'category' );
		$label = isset( $term_labels->singular_name ) ? $term_labels->singular_name : __( 'Category', 'autodescription' );

		$cats = get_terms( array( 'taxonomy' => 'category', 'fields' => 'ids', 'hide_empty' => false, 'order' => 'ASC', 'number' => 1 ) );
		if ( is_array( $cats ) && ! empty( $cats ) ) {
			//* Category should exist.
			$cat = reset( $cats );
		} else {
			//* Default fallback category.
			$cat = 1;
		}
		//* If cat is found, it will return its name. Otherwise it's an empty string.
		$cat_name = get_cat_name( $cat );
		$cat_name = $cat_name ? $cat_name : __( 'Example Category', 'autodescription' );

		$display_prefix = $this->is_option_checked( 'title_rem_prefixes' ) ? 'none' : 'inline';
		$title = '<span class="title-prefix-example" style="display:' . $display_prefix . '">' . $label . ': </span>' . $cat_name;

		$example_left = '<em>' . $left_additions . $title . '</em>';
		$example_right = '<em>' . $title . $right_additions . '</em>';

		$example_nojs = $showleft ? $example_left : $example_right;

		$language = $this->google_language();

		/**
		 * @todo use checkbox function
		 * @priority low 2.6.x
		 */

		?>
		<h4><?php _e( 'Title prefix options', 'autodescription' ); ?></h4>
		<p><span class="description"><?php _e( "On archives a descriptive prefix may be added to the title.", 'autodescription' ); ?></span></p>

		<h4><?php _e( 'Example Automated Archive Title Output' ); ?></h4>
		<p>
			<span class="title-additions-example-left" style="display:<?php echo $showleft ? 'inline' : 'none'; ?>"><?php echo $this->code_wrap_noesc( $example_left ); ?></span>
			<span class="title-additions-example-right" style="display:<?php echo $showleft ? 'none' : 'inline'; ?>"><?php echo $this->code_wrap_noesc( $example_right ); ?></span>
		</p>

		<hr>

		<h4><?php _e( 'Remove Archive Title Prefixes', 'autodescription' ); ?></h4>
		<p id="title-prefixes-toggle">
			<label for="<?php $this->field_id( 'title_rem_prefixes' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'title_rem_prefixes' ); ?>" id="<?php $this->field_id( 'title_rem_prefixes' ); ?>" <?php $this->is_conditional_checked( 'title_rem_prefixes' ); ?> value="1" <?php checked( $this->get_field_value( 'title_rem_prefixes' ) ); ?> />
				<?php _e( 'Remove Prefixes from title?', 'autodescription' ); ?>
			</label>
			<?php
			$this->make_info(
				__( "The prefix helps visitors and Search Engines determine what kind of page they're visiting", 'autodescription' ),
				'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#3',
				true
			);
			?>
		</p>
		<?php

	}

	/**
	 * Description meta box on the Site SEO Settings page.
	 *
	 * @since 2.3.4
	 *
	 * @param array $args The metabox arguments.
	 *
	 * @see $this->description_metabox()	Callback for Description Settings box.
	 */
	public function description_metabox( $args = array() ) {

		do_action( 'the_seo_framework_description_metabox_before' );

		$blogname = $this->get_blogname();
		$sep = $this->get_separator( 'description', true );

		/**
		 * Generate example.
		 */
		$page_title = __( 'Example Title', 'autodescription' );
		$on = _x( 'on', 'Placement. e.g. Post Title "on" Blog Name', 'autodescription' );
		$excerpt = __( 'This is an example description...', 'autodescription' );

		$page_title = $this->escape_description( $page_title );
		$on = $this->escape_description( $on );
		$excerpt = $this->escape_description( $excerpt );

		$page_title = $this->escape_description( $page_title );
		$on = $this->escape_description( $on );
		$excerpt = $this->escape_description( $excerpt );

		//* Put it together.
		$example 	= '<span id="description-additions-js">'
						. $page_title
						. '<span id="on-blogname-js">' . " $on " . $blogname . '</span>'
						. '<span id="autodescription-descsep-js">' . " $sep " . '</span>'
					. '</span>'
					. $excerpt
					;

		$nojs_additions = '';
		//* Add or remove additions based on option.
		if ( $this->add_description_additions() ) {
			$description_blogname_additions = $this->get_option( 'description_blogname' );

			$example_nojs_onblog = $description_blogname_additions ? $page_title . " $on " . $blogname : $page_title;
			$nojs_additions = $example_nojs_onblog . " $sep ";
		}

		$example_nojs = $nojs_additions . $excerpt;

		?>
		<h4><?php printf( __( 'Automated Description Settings', 'autodescription' ) ); ?></h4>
		<p><span class="description"><?php printf( __( "The meta description can be used to determine the text used under the title on Search Engine results pages.", 'autodescription' ) ); ?></span></p>

		<h4><?php _e( 'Example Automated Description Output', 'autodescription' ); ?></h4>
		<p class="hide-if-no-js"><?php echo $this->code_wrap_noesc( $example ); ?></p>
		<p class="hide-if-js"><?php echo $this->code_wrap( $example_nojs ); ?></p>

		<hr>
		<?php

		/**
		 * Parse tabs content
		 *
		 * @param array $default_tabs { 'id' = The identifier =>
		 *			array(
		 *				'name' 		=> The name
		 *				'callback' 	=> The callback function, use array for method calling (accepts $this, but isn't used here for optimization purposes)
		 *				'dashicon'	=> Desired dashicon
		 *			)
		 * }
		 *
		 * @since 2.6.0
		 */
		$default_tabs = array(
			'general' => array(
				'name' 		=> __( 'General', 'autodescription' ),
				'callback'	=> array( $this, 'description_metabox_general_tab' ),
				'dashicon'	=> 'admin-generic',
			),
			'additions' => array(
				'name'		=> __( 'Additions', 'autodescription' ),
				'callback'	=> array( $this, 'description_metabox_additions_tab' ),
				'dashicon'	=> 'plus',
			),
		);

		/**
		 * Applies filters the_seo_framework_description_settings_tabs : array see $default_tabs
		 * @since 2.6.0
		 *
		 * Used to extend Description tabs.
		 */
		$defaults = (array) apply_filters( 'the_seo_framework_description_settings_tabs', $default_tabs, $args );

		$tabs = wp_parse_args( $args, $defaults );

		$this->nav_tab_wrapper( 'description', $tabs, '2.6.0' );

		do_action( 'the_seo_framework_description_metabox_after' );

	}

	/**
	 * Description meta box general tab.
	 *
	 * @since 2.6.0
	 *
	 * @see $this->description_metabox()	Callback for Description Settings box.
	 */
	public function description_metabox_general_tab() {

		//* Let's use the same separators as for the title.
		$description_separator = $this->get_separator_list();
		$sep_option = $this->get_option( 'description_separator' );
		$sep_option = $sep_option ? $sep_option : 'pipe';

		$recommended = ' class="recommended" title="' . __( 'Recommended', 'autodescription' ) . '"';

		?>
		<fieldset>
			<legend><h4><?php _e( 'Description Excerpt Separator', 'autodescription' ); ?></h4></legend>
			<p id="description-separator" class="theseoframework-fields">
			<?php foreach ( $description_separator as $name => $html ) { ?>
				<input type="radio" name="<?php $this->field_name( 'description_separator' ); ?>" id="<?php $this->field_id( 'description_separator' . $name ); ?>" value="<?php echo $name ?>" <?php checked( $sep_option, $name ); ?> />
				<label for="<?php $this->field_id( 'description_separator' . $name ); ?>" <?php echo ( 'pipe' === $name || 'dash' === $name ) ? $recommended : ''; ?>><?php echo $html ?></label>
			<?php } ?>
			</p>
			<span class="description"><?php _e( 'If the Automated Description consists of two parts (title and excerpt), then the separator will go in-between them.', 'autodescription' ); ?></span>
		</fieldset>
		<?php

	}

	/**
	 * Description meta box additions tab.
	 *
	 * @since 2.6.0
	 *
	 * @see $this->description_metabox()	Callback for Description Settings box.
	 */
	public function description_metabox_additions_tab() {

		$language = $this->google_language();
		$google_explanation = esc_url( 'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#1' );

		/**
		 * @todo use checkbox functions.
		 * @priority low 2.6.x
		 */
		?>
		<h4><?php printf( __( 'Additions Description Settings', 'autodescription' ) ); ?></h4>
		<p><span class="description"><?php printf( __( "To create a more organic description, a small introduction can be added before the description.", 'autodescription' ) ); ?></span></p>
		<p><span class="description"><?php printf( __( "The introduction consists of the title and optionally the blogname.", 'autodescription' ) ); ?></span></p>

		<hr>

		<h4><?php _e( 'Add descriptive Additions to Description', 'autodescription' ); ?></h4>
		<p id="description-additions-toggle">
			<label for="<?php $this->field_id( 'description_additions' ); ?>" class="toblock">
				<input type="checkbox" name="<?php $this->field_name( 'description_additions' ); ?>" id="<?php $this->field_id( 'description_additions' ); ?>" <?php $this->is_conditional_checked( 'description_additions' ); ?> value="1" <?php checked( $this->get_field_value( 'description_additions' ) ); ?> />
				<?php _e( 'Add Additions to automated description?', 'autodescription' ); ?>
				<a href="<?php echo esc_url( $google_explanation ); ?>" target="_blank" class="description" title="<?php _e( 'This creates good meta descriptions', 'autodescription' ); ?>">[?]</a>
			</label>
		</p>

		<h4><?php _e( 'Add Blogname to Additions', 'autodescription' ); ?></h4>
		<p id="description-onblogname-toggle">
			<label for="<?php $this->field_id( 'description_blogname' ); ?>" class="toblock">
				<input type="checkbox" name="<?php $this->field_name( 'description_blogname' ); ?>" id="<?php $this->field_id( 'description_blogname' ); ?>" <?php $this->is_conditional_checked( 'description_blogname' ); ?> value="1" <?php checked( $this->get_field_value( 'description_blogname' ) ); ?> />
				<?php _e( 'Add Blogname to automated description additions?', 'autodescription' ); ?>
			</label>
		</p>
		<?php

	}

	/**
	 * Robots meta box on the Site SEO Settings page.
	 *
	 * @since 2.2.2
	 */
	public function robots_metabox( $args = array() ) {

		do_action( 'the_seo_framework_robots_metabox_before' );

		//* Robots types
		$types = array(
			'category' => __( 'Category', 'autodescription' ),
			'tag' => __( 'Tag', 'autodescription' ),
			'author' => __( 'Author', 'autodescription' ),
			'date' => __( 'Date', 'autodescription' ),
			'search' => __( 'Search Pages', 'autodescription' ),
			'attachment' => __( 'Attachment Pages', 'autodescription' ),
			'site' => _x( 'the entire site', '...for the entire site', 'autodescription' ),
		);

		//* Robots i18n
		$robots = array(
			'noindex' =>  array(
				'value' => 'noindex',
				'name' 	=> __( 'NoIndex', 'autodescription' ),
				'desc' 	=> __( 'These options prevent indexing of the selected archives and pages. If you enable this, the selected archives or pages will be removed from Search Engine results pages.', 'autodescription' ),
			),
			'nofollow' =>  array(
				'value' => 'nofollow',
				'name'	=> __( 'NoFollow', 'autodescription' ),
				'desc'	=> __( 'These options prevent links from being followed on the selected archives and pages. If you enable this, the selected archives or pages in-page links will gain no SEO value, including your own links.', 'autodescription' ),
			),
			'noarchive' =>  array(
				'value' => 'noarchive',
				'name'	=> __( 'NoArchive', 'autodescription' ),
				'desc'	=> __( 'These options prevent caching of the selected archives and pages. If you enable this, Search Engines will not create a cached copy of the selected archives or pages.', 'autodescription' ),
			),
		);

		/**
		 * Parse tabs content
		 *
		 * @param array $default_tabs { 'id' = The identifier =>
		 *			array(
		 *				'name' 		=> The name
		 *				'callback'	=> function callback
		 *				'dashicon'	=> WordPress Dashicon
		 *				'args'		=> function args
		 *			)
		 * }
		 *
		 * @since 2.2.2
		 */
		$default_tabs = array(
				'general' => array(
					'name' 		=> __( 'General', 'autodescription' ),
					'callback'	=> array( $this, 'robots_metabox_general_tab' ),
					'dashicon'	=> 'admin-generic',
					'args'		=> '',
				),
				'index' => array(
					'name' 		=> __( 'Indexing', 'autodescription' ),
					'callback'	=> array( $this, 'robots_metabox_no_tab' ),
					'dashicon'	=> 'filter',
					'args'		=> array( $types, $robots['noindex'] ),
				),
				'follow' => array(
					'name'		=> __( 'Following', 'autodescription' ),
					'callback'	=> array( $this, 'robots_metabox_no_tab' ),
					'dashicon'	=> 'editor-unlink',
					'args'		=> array( $types, $robots['nofollow'] ),
				),
				'archive' => array(
					'name'		=> __( 'Archiving', 'autodescription' ),
					'callback'	=> array( $this, 'robots_metabox_no_tab' ),
					'dashicon'	=> 'download',
					'args'		=> array( $types, $robots['noarchive'] ),
				),
			);

		/**
		 * Applies filters 'the_seo_framework_robots_settings_tabs' : array see $default_tabs
		 *
		 * Used to extend Social tabs
		 * @since 2.2.4
		 */
		$defaults = (array) apply_filters( 'the_seo_framework_robots_settings_tabs', $default_tabs, $args );

		$tabs = wp_parse_args( $args, $defaults );

		$this->nav_tab_wrapper( 'robots', $tabs, '2.2.4' );

		do_action( 'the_seo_framework_robots_metabox_after' );

	}

	/**
	 * Robots Metabox General Tab output
	 *
	 * @since 2.2.4
	 *
	 * @see $this->robots_metabox() Callback for Robots Settings box.
	 */
	protected function robots_metabox_general_tab() {

		?>
		<h4><?php _e( 'Open Directory Settings', 'autodescription' ); ?></h4>
		<p class="description"><?php printf( __( "Sometimes, Search Engines use resources from certain Directories to find titles and descriptions for your content. You generally don't want them to. Turn these options on to prevent them from doing so.", 'autodescription' ), $this->code_wrap( 'noodp' ), $this->code_wrap( 'noydir' ) ); ?></p>
		<p class="description"><?php _e( "The Open Directory Project and the Yahoo! Directory may contain outdated SEO values. Therefore, it's best to leave these options checked.", 'autodescription' ); ?></p>
		<?php

		$this->wrap_fields(
		 	array(
				$this->make_checkbox(
					'noodp',
					sprintf( __( 'Apply %s to the entire site?', 'autodescription' ), $this->code_wrap( 'noodp' ) ),
					''
				),
				$this->make_checkbox(
					'noydir',
					sprintf( __( 'Apply %s to the entire site?', 'autodescription' ), $this->code_wrap( 'noydir' ) ),
					''
				),
			),
			true
		);

		?>
		<hr>

		<h4><?php _e( 'Paginated Archive Settings', 'autodescription' ); ?></h4>
		<p class="description"><?php printf( __( "Indexing the second or later page of any archive might cause duplication errors. Search Engines look down upon them; therefore, it's recommended to disable indexing of those pages.", 'autodescription' ), $this->code_wrap( 'noodp' ), $this->code_wrap( 'noydir' ) ); ?></p>
		<?php

		$this->wrap_fields(
			$this->make_checkbox(
				'paged_noindex',
				sprintf( __( 'Apply %s to every second or later archive page?', 'autodescription' ), $this->code_wrap( 'noindex' ) ),
				''
			),
		true
		);

	}

	/**
	 * Robots Metabox
	 *		No-: Index/Follow/Archive
	 * Tab output
	 *
	 * @since 2.2.4
	 */
	protected function robots_metabox_no_tab( $types, $robots ) {

		$ro_value = $robots['value'];
		$ro_name = $robots['name'];
		$ro_i18n = $robots['desc'];

		?>
		<h4><?php printf( __( '%s Robots Settings', 'autodescription' ), $ro_name ); ?></h4>
		<p><span class="description"><?php echo $ro_i18n ?></span></p>
		<p class="theseoframework-fields">
			<?php

			$checkboxes = '';

			foreach ( $types as $type => $i18n ) {

				if ( 'site' === $type || 'attachment' === $type || 'search' === $type ) {
					//* Singular.
					/* translators: 1: Option, 2: Post Type */
					$label = sprintf( __( 'Apply %1$s to %2$s?', 'autodescription' ), $this->code_wrap( $ro_name ), $i18n );
				} else {
					//* Archive.
					/* translators: 1: Option, 2: Post Type */
					$label = sprintf( __( 'Apply %1$s to %2$s Archives?', 'autodescription' ), $this->code_wrap( $ro_name ), $i18n );
				}

				$id = $type . '_' . $ro_value;

				//* Add <hr> if it's 'site'
				$checkboxes .= ( 'site' === $type ) ? '<hr class="theseoframework-option-spacer">' : '';

				$checkboxes .= $this->make_checkbox( $id, $label, '' );
			}

			//* Echo checkboxes.
			echo $this->wrap_fields( $checkboxes );
			?>
		</p>
		<?php

	}

	/**
	 * Home Page meta box on the Site SEO Settings page.
	 *
	 * @param array $args The navigation tabs args.
	 *
	 * @since 2.2.2
	 */
	public function homepage_metabox( $args = array() ) {

		do_action( 'the_seo_framework_homepage_metabox_before' );

		?>
		<p><span class="description"><?php printf( __( 'These settings will take precedence over the settings set within the Home Page edit screen, if any.', 'autodescription' ) ); ?></span></p>

		<hr>
		<?php

		/**
		 * Parse tabs content
		 *
		 * @param array $default_tabs { 'id' = The identifier =>
		 *			array(
		 *				'name' 		=> The name
		 *				'callback' 	=> The callback function, use array for method calling (accepts $this, but isn't used here for optimization purposes)
		 *				'dashicon'	=> Desired dashicon
		 *			)
		 * }
		 *
		 * @since 2.6.0
		 */
		$default_tabs = array(
			'general' => array(
				'name' 		=> __( 'General', 'autodescription' ),
				'callback'	=> array( $this, 'homepage_metabox_general' ),
				'dashicon'	=> 'admin-generic',
			),
			'additions' => array(
				'name'		=> __( 'Additions', 'autodescription' ),
				'callback'	=> array( $this, 'homepage_metabox_additions' ),
				'dashicon'	=> 'plus',
			),
			'robots' => array(
				'name'		=> __( 'Robots', 'autodescription' ),
				'callback'	=> array( $this, 'homepage_metabox_robots' ),
				'dashicon'	=> 'visibility',
			),
		);

		/**
		 * Applies filters the_seo_framework_homepage_settings_tabs : array see $default_tabs
		 * @since 2.6.0
		 *
		 * Used to extend HomePage tabs.
		 */
		$defaults = (array) apply_filters( 'the_seo_framework_homepage_settings_tabs', $default_tabs, $args );

		$tabs = wp_parse_args( $args, $defaults );

		$this->nav_tab_wrapper( 'homepage', $tabs, '2.6.0' );

		do_action( 'the_seo_framework_homepage_metabox_after' );

	}

	public function homepage_metabox_general() {

		/**
		 * @param string $language The language for help pages. See $this->google_language();
		 */
		$language = $this->google_language();

		/**
		 * @param bool $page_on_front False if homepage is blog, true if single page/post
		 * @param bool $home_description_frompost True if home inpost title is filled in. False if not.
		 */
		$page_on_front = $this->has_page_on_front();
		$home_description_frompost = false;

		/**
		 * Notify the user that the data is pulled from the post.
		 */
		$description_from_post_message = '';
		$title_from_post_message  = '';

		// Setting up often used Translations
		$title_i18n = __( 'Title', 'autodescription' );
		$description_i18n = __( 'Description', 'autodescription' );
		$home_page_i18n = __( 'Home Page', 'autodescription' );

		//* Get home page ID. If blog on front, it's 0.
		$home_id = $this->get_the_front_page_ID();

		$home_title = $this->escape_title( $this->get_option( 'homepage_title' ) );

		//* Get blog tagline
		$blog_description = $this->get_blogdescription();

		/**
		 * Home Page Tagline settings.
		 * @since 2.3.8
		 *
		 * @param string $home_tagline The tagline option.
		 * @param string $home_tagline_placeholder The option placeholder. Always defaults to description.
		 * @param string|void $home_tagline_value The tagline input value.
		 * @param string $blog_description Override blog description with option if applicable.
		 */
		$home_tagline = $this->get_field_value( 'homepage_title_tagline' );
		$home_tagline_placeholder = $blog_description;
		$home_tagline_value = $home_tagline ? $home_tagline : '';
		$blog_description = $home_tagline_value ? $home_tagline_value : $blog_description;

		/**
		 * Create a placeholder for when there's no custom HomePage title found.
		 * @since 2.2.4
		 */
		$home_title_args = $this->generate_home_title( true, '', '', true, false );
		$home_title_placeholder = $this->process_title_additions( $home_title_args['title'], $home_title_args['blogname'], $home_title_args['seplocation'] );

		/**
		 * If the home title is fetched from the post, notify about that instead.
		 * @since 2.2.4
		 *
		 * Nesting often used translations
		 */
		if ( empty( $home_title ) && $page_on_front && $this->get_custom_field( '_genesis_title', $home_id ) ) {
			/* translators: 1: Option, 2: Page SEO Settings, 3: Home Page */
			$title_from_post_message = sprintf( __( 'Note: The %1$s is fetched from the %2$s on the %3$s.', 'autodescription' ), $title_i18n, __( 'Page SEO Settings', 'autodescription' ), $home_page_i18n );
		}

		/**
		 * Check for options to calculate title length.
		 *
		 * @since 2.3.4
		 */
		if ( $home_title ) {
			$home_title_args = $this->generate_home_title();
			$tit_len_pre = $this->process_title_additions( $home_title_args['title'], $home_title_args['blogname'], $home_title_args['seplocation'] );
		} else {
			$tit_len_pre = $home_title_placeholder;
		}

		//* Fetch the description from the home page.
		$frompost_description = $page_on_front ? $this->get_custom_field( '_genesis_description', $home_id ) : '';

		//* Fetch the HomePage Description option.
		$home_description = $this->get_field_value( 'homepage_description' );

		/**
		 * Create a placeholder if there's no custom HomePage description found.
		 * @since 2.2.4
		 *
		 * Reworked. Always create a placeholder.
		 * @since 2.3.4
		 */
		if ( $frompost_description ) {
			$description_placeholder = $frompost_description;
		} else {
			$description_args = array(
				'id' => $home_id,
				'is_home' => true,
				'get_custom_field' => false
			);

			$description_placeholder = $this->generate_description( '', $description_args );
		}

		/**
		 * Checks if the home is blog, the Home Page Metabox description and
		 * the frompost description.
		 * @since 2.3.4
		 */
		if ( empty( $home_description ) && $page_on_front && $frompost_description )
			$home_description_frompost = true;

		/**
		 *
		 * If the HomePage Description empty, it will check for the InPost
		 * Description set on the Home Page. And it will set the InPost
		 * Description as placeholder.
		 *
		 * Nesting often used translations.
		 *
		 * Notify that the homepage is a blog.
		 * @since 2.2.2
		 */
		if ( $home_description_frompost ) {
			/* translators: 1: Option, 2: Page SEO Settings, 3: Home Page */
			$description_from_post_message = sprintf( __( 'Note: The %1$s is fetched from the %2$s on the %3$s.', 'autodescription' ), $description_i18n, __( 'Page SEO Settings', 'autodescription' ), $home_page_i18n );
		}

		$desc_len_pre = $home_description ? $home_description : $description_placeholder;

		/**
		 * Convert to what Google outputs.
		 *
		 * This will convert e.g. &raquo; to a single length character.
		 * @since 2.3.4
		 */
		$tit_len = html_entity_decode( $this->escape_title( $tit_len_pre ) );
		$desc_len = html_entity_decode( $this->escape_title( $desc_len_pre ) );

		?>
		<p>
			<label for="<?php $this->field_id( 'homepage_title_tagline' ); ?>" class="toblock">
				<strong><?php printf( __( 'Custom %s Title Tagline', 'autodescription' ), $home_page_i18n ); ?></strong>
			</label>
		</p>
		<p>
			<input type="text" name="<?php $this->field_name( 'homepage_title_tagline' ); ?>" class="large-text" id="<?php $this->field_id( 'homepage_title_tagline' ); ?>" placeholder="<?php echo $home_tagline_placeholder ?>" value="<?php echo esc_attr( $home_tagline_value ); ?>" />
		</p>

		<hr>

		<p>
			<label for="<?php $this->field_id( 'homepage_title' ); ?>" class="toblock">
				<strong><?php printf( __( 'Custom %s Title', 'autodescription' ), $home_page_i18n ); ?></strong>
				<a href="<?php echo esc_url( 'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#3' ); ?>" target="_blank" title="<?php _e( 'Recommended Length: 50 to 55 characters', 'autodescription' ) ?>">[?]</a>
				<span class="description theseoframework-counter"><?php printf( __( 'Characters Used: %s', 'autodescription' ), '<span id="' . $this->field_id( 'homepage_title', false ) . '_chars">'. mb_strlen( $tit_len ) .'</span>' ); ?></span>
			</label>
		</p>
		<p id="autodescription-title-wrap">
			<input type="text" name="<?php $this->field_name( 'homepage_title' ); ?>" class="large-text" id="<?php $this->field_id( 'homepage_title' ); ?>" placeholder="<?php echo $home_title_placeholder ?>" value="<?php echo esc_attr( $home_title ); ?>" />
			<span id="autodescription-title-offset" class="hide-if-no-js"></span><span id="autodescription-title-placeholder" class="hide-if-no-js"></span>
		</p>
		<?php
		if ( $title_from_post_message ) {
			echo '<p class="description">' . $title_from_post_message . '</p>';
		}
		?>
		<hr>

		<p>
			<label for="<?php $this->field_id( 'homepage_description' ); ?>" class="toblock">
				<strong><?php printf( __( 'Custom %s Description', 'autodescription' ), $home_page_i18n ); ?></strong>
				<a href="<?php echo esc_url( 'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#1' ); ?>" target="_blank" title="<?php _e( 'Recommended Length: 145 to 155 characters', 'autodescription' ) ?>">[?]</a>
				<span class="description theseoframework-counter"><?php printf( __( 'Characters Used: %s', 'autodescription' ), '<span id="' . $this->field_id( 'homepage_description', false ) . '_chars">'. mb_strlen( $desc_len ) .'</span>' ); ?></span>
			</label>
		</p>
		<p>
			<textarea name="<?php $this->field_name( 'homepage_description' ); ?>" class="large-text" id="<?php $this->field_id( 'homepage_description' ); ?>" rows="3" cols="70"  placeholder="<?php echo $description_placeholder ?>"><?php echo esc_textarea( $home_description ); ?></textarea>
		</p>
		<p class="description">
			<?php _e( 'The meta description can be used to determine the text used under the title on Search Engine results pages.', 'autodescription' ); ?>
		</p>
		<?php
		if ( $description_from_post_message ) {
			echo '<p class="description">' . $description_from_post_message . '</p>';
		}

	}


	/**
	 * HomePage Metabox Additions Tab Output
	 *
	 * @since 2.6.0
	 *
	 * @see $this->homepage_metabox() Callback for HomePage Settings box.
	 */
	public function homepage_metabox_additions() {

		$home_page_i18n = __( 'Home Page', 'autodescription' );

		/**
		 * Generate example for Title Additions Location.
		 */
		$title_args = $this->generate_home_title();

		//* I know, brilliant. @TODO @priority high 2.6.x.
		$title = $title_args['blogname'];
		$blogname = $title_args['title'];

		// Get title separator
		$sep = $this->get_separator( 'title', true );

		/**
		 * Generate Examples for both left and right seplocations.
		 */
		$example_left = '<em><span class="custom-title-js">' . esc_attr( $title ) . '</span><span class="custom-blogname-js"><span class="autodescription-sep-js"> ' . esc_attr( $sep ) . ' </span><span class="custom-tagline-js">' . esc_attr( $blogname ) . '</span></span></span>' . '</em>';
		$example_right = '<em>' . '<span class="custom-blogname-js"><span class="custom-tagline-js">' . esc_attr( $blogname ) . '</span><span class="autodescription-sep-js"> ' . esc_attr( $sep ) . ' </span></span><span class="custom-title-js">' . esc_attr( $title ) . '</span></em>';

		?>
		<fieldset>
			<legend><h4><?php _e( 'Document Title Additions Location', 'autodescription' ); ?></h4></legend>
			<p>
				<span class="description"><?php _e( 'Determines which side the added title text will go on.', 'autodescription' ); ?></span>
			</p>

			<p id="home-title-location" class="theseoframework-fields">
				<span class="toblock">
					<input type="radio" name="<?php $this->field_name( 'home_title_location' ); ?>" id="<?php $this->field_id( 'home_title_location_left' ); ?>" value="left" <?php checked( $this->get_field_value( 'home_title_location' ), 'left' ); ?> />
					<label for="<?php $this->field_id( 'home_title_location_left' ); ?>">
						<span><?php _e( 'Left:', 'autodescription' ); ?></span>
						<?php echo ( $example_left ) ? $this->code_wrap_noesc( $example_left ) : ''; ?>
					</label>
				</span>
				<span class="toblock">
					<input type="radio" name="<?php $this->field_name( 'home_title_location' ); ?>" id="<?php $this->field_id( 'home_title_location_right' ); ?>" value="right" <?php checked( $this->get_field_value( 'home_title_location' ), 'right' ); ?> />
					<label for="<?php $this->field_id( 'home_title_location_right' ); ?>">
						<span><?php _e( 'Right:', 'autodescription' ); ?></span>
						<?php echo ( $example_right ) ? $this->code_wrap_noesc( $example_right ) : ''; ?>
					</label>
				</span>
			</p>
		</fieldset>

		<hr>
		<?php
		/**
		 * @TODO work on this checkbox.
		 * @priority low 2.6.x
		 */
		?>
		<h4><?php printf( __( '%s Tagline', 'autodescription' ), $home_page_i18n ); ?></h4>
		<p id="title-tagline-toggle">
			<label for="<?php $this->field_id( 'homepage_tagline' ); ?>" class="toblock">
				<input type="checkbox" name="<?php $this->field_name( 'homepage_tagline' ); ?>" id="<?php $this->field_id( 'homepage_tagline' ); ?>" <?php $this->is_conditional_checked( 'homepage_tagline' ); ?> value="1" <?php checked( $this->get_field_value( 'homepage_tagline' ) ); ?> />
				<?php printf( __( 'Add site description (tagline) to the Title on the %s?', 'autodescription' ), $home_page_i18n ); ?>
			</label>
		</p>
		<?php
	}

	/**
	 * HomePage Metabox Robots Tab Output
	 *
	 * @since 2.6.0
	 *
	 * @see $this->homepage_metabox() Callback for HomePage Settings box.
	 */
	public function homepage_metabox_robots() {

		$home_page_i18n = __( 'Home Page', 'autodescription' );
		$language = $this->google_language();

		//* Get home page ID. If blog on front, it's 0.
		$home_id = $this->get_the_front_page_ID();

		$noindex_post = $this->get_custom_field( '_genesis_noindex', $home_id );
		$nofollow_post = $this->get_custom_field( '_genesis_nofollow', $home_id );
		$noarchive_post = $this->get_custom_field( '_genesis_noarchive', $home_id );

		/**
		 * Shows user that the setting is checked on the home page.
		 * Adds starting - with space to maintain readability.
		 *
		 * @since 2.2.4
		 */
		if ( $noindex_post || $nofollow_post || $noarchive_post ) {
			$checked_home = ' - <a href="' . esc_url( admin_url( 'post.php?post=' . $home_id . '&action=edit#theseoframework-inpost-box' ) ) . '" target="_blank" class="attention" title="' . __( 'View Home Page Settings', 'autodescription' ) . '" >' . __( 'Checked in Page', 'autodescription' ) . '</a>';
		} else {
			$checked_home = '';
		}

		?>
		<h4><?php _e( 'Home Page Robots Meta Settings', 'autodescription' ); ?></h4>
		<?php

		$noindex_note = $noindex_post ? $checked_home : '';
		$nofollow_note = $nofollow_post ? $checked_home : '';
		$noarchive_note = $noarchive_post ? $checked_home : '';

		/* translators: 1: Option, 2: Location */
		$i_label 	= sprintf( __( 'Apply %1$s to the %2$s?', 'autodescription' ), $this->code_wrap( 'noindex' ), $home_page_i18n );
		$i_label	.= ' ';
		$i_label	.= $this->make_info(
							__( 'Tell Search Engines not to show this page in their search results', 'autodescription' ),
							'https://support.google.com/webmasters/answer/93710?hl=' . $language,
							false
						)
					. $noindex_note;

		/* translators: 1: Option, 2: Location */
		$f_label 	= sprintf( __( 'Apply %1$s to the %2$s?', 'autodescription' ), $this->code_wrap( 'nofollow' ), $home_page_i18n );
		$f_label	.= ' ';
		$f_label	.= $this->make_info(
							__( 'Tell Search Engines not to follow links on this page', 'autodescription' ),
							'https://support.google.com/webmasters/answer/96569?hl=' . $language,
							false
						)
					. $nofollow_note;

		/* translators: 1: Option, 2: Location */
		$a_label 	= sprintf( __( 'Apply %1$s to the %2$s?', 'autodescription' ), $this->code_wrap( 'noarchive' ), $home_page_i18n );
		$a_label	.= ' ';
		$a_label 	.=	$this->make_info(
						__( 'Tell Search Engines not to save a cached copy of this page', 'autodescription' ),
						'https://support.google.com/webmasters/answer/79812?hl=' . $language,
						false
					)
					. $noarchive_note;

		//* Echo checkboxes.
		$this->wrap_fields(
			array(
				$this->make_checkbox(
					'homepage_noindex',
					$i_label,
					''
				),
				$this->make_checkbox(
					'homepage_nofollow',
					$f_label,
					''
				),
				$this->make_checkbox(
					'homepage_noarchive',
					$a_label,
					''
				),
			),
			true
		);

		// Add notice if any options are checked on the post.
		if ( $noindex_post || $nofollow_post || $noarchive_post ) {
			?><p class="description"><?php printf( __( 'Note: If any of these options are unchecked, but are checked on the Home Page, they will be outputted regardless.', 'autodescription' ) ); ?></p><?php
		}
		?>

		<hr>

		<h4><?php _e( 'Home Page Pagination Robots Settings', 'autodescription' ); ?></h4>

		<p class="description"><?php _e( "If your Home Page is paginated and outputs content that's also found elsewhere on the website, enabling this option might prevent duplicate content.", 'autodescription' ); ?></p>

		<?php
		//* Echo checkbox.
		$this->wrap_fields(
			$this->make_checkbox(
				'home_paged_noindex',
				/* translators: 1: Option, 2: Location */
				sprintf( __( 'Apply %1$s to every second or later page on the %2$s?', 'autodescription' ), $this->code_wrap( 'noindex' ), $home_page_i18n ),
				''
			),
			true
		);

	}

	/**
	 * Social meta box on the Site SEO Settings page.
	 *
	 * @since 2.2.2
	 *
	 * @uses $this->social_metabox_general_tab()
	 * @uses $this->social_metabox_facebook_tab()
	 * @uses $this->social_metabox_twitter_tab()
	 *
	 * @applies filter 'social_settings_tabs'
	 *
	 * @param array $args the social tabs arguments
	 */
	public function social_metabox( $args = array() ) {

		do_action( 'the_seo_framework_social_metabox_before' );

		/**
		 * Parse tabs content
		 *
		 * @param array $default_tabs { 'id' = The identifier =>
		 *			array(
		 *				'name' 		=> The name
		 *				'callback' 	=> The callback function, use array for method calling (accepts $this, but isn't used here for optimization purposes)
		 *				'dashicon'	=> Desired dashicon
		 *			)
		 * }
		 *
		 * @since 2.2.2
		 */
		$default_tabs = array(
			'general' => array(
				'name' 		=> __( 'General', 'autodescription' ),
				'callback'	=> array( $this, 'social_metabox_general_tab' ),
				'dashicon'	=> 'admin-generic',
			),
			'facebook' => array(
				'name'		=> 'Facebook',
				'callback'	=> array( $this, 'social_metabox_facebook_tab' ),
				'dashicon'	=> 'facebook-alt',
			),
			'twitter' => array(
				'name'		=> 'Twitter',
				'callback'	=> array( $this, 'social_metabox_twitter_tab' ),
				'dashicon'	=> 'twitter',
			),
			'postdates' => array(
				'name'		=> __( 'Post Dates', 'autodescription' ),
				'callback'	=> array( $this, 'social_metabox_postdates_tab' ),
				'dashicon'	=> 'backup',
			),
			'relationships' => array(
				'name'		=> __( 'Link Relationships', 'autodescription' ),
				'callback'	=> array( $this, 'social_metabox_relationships_tab' ),
				'dashicon'	=> 'leftright',
			),
		);

		/**
		 * Applies filters the_seo_framework_social_settings_tabs : array see $default_tabs
		 *
		 * Used to extend Social tabs
		 */
		$defaults = (array) apply_filters( 'the_seo_framework_social_settings_tabs', $default_tabs, $args );

		$tabs = wp_parse_args( $args, $defaults );

		$this->nav_tab_wrapper( 'social', $tabs, '2.2.2' );

		do_action( 'the_seo_framework_social_metabox_after' );

	}

	/**
	 * Social Metabox General Tab output
	 *
	 * @since 2.2.2
	 *
	 * @see $this->social_metabox() Callback for Social Settings box.
	 */
	protected function social_metabox_general_tab() {

		?>
		<h4><?php _e( 'Site Shortlink Settings', 'autodescription' ); ?></h4>
		<p class="description"><?php printf( __( 'The shortlink tag might have some use for 3rd party service discoverability, but it has little to no SEO value whatsoever.', 'autodescription' ) ); ?></p>
		<?php

		//* Echo checkboxes.
		$this->wrap_fields(
			$this->make_checkbox(
				'shortlink_tag',
				__( 'Output shortlink tag?', 'autodescription' ),
				''
			),
			true
		);

		?>
		<hr>

		<h4><?php _e( 'Social Meta Tags Settings', 'autodescription' ); ?></h4>
		<p class="description"><?php _e( 'Output various meta tags for social site integration, among other 3rd party services.', 'autodescription' ); ?></p>

		<hr>
		<?php

		//* Echo Open Graph Tags checkboxes.
		$this->wrap_fields(
			$this->make_checkbox(
				'og_tags',
				__( 'Output Open Graph meta tags?', 'autodescription' ),
				__( 'Facebook, Twitter, Pinterest and many other social sites make use of these tags.', 'autodescription' )
			),
			true
		);

		if ( $this->has_og_plugin() )
			echo '<p class="description">' . __( 'Note: Another Open Graph plugin has been detected.', 'autodescription' ) . '</p>';

		?><hr><?php

		//* Echo Facebook Tags checkbox.
		$this->wrap_fields(
			$this->make_checkbox(
				'facebook_tags',
				__( 'Output Facebook meta tags?', 'autodescription' ),
				sprintf( __( 'Output various tags targetted at %s.', 'autodescription' ), 'Facebook' )
			),
			true
		);

		?><hr><?php

		//* Echo Twitter Tags checkboxes.
		$this->wrap_fields(
			$this->make_checkbox(
				'twitter_tags',
				__( 'Output Twitter meta tags?', 'autodescription' ),
				sprintf( __( 'Output various tags targetted at %s.', 'autodescription' ), 'Twitter' )
			),
			true
		);

	}

	/**
	 * Social Metabox Facebook Tab Output
	 *
	 * @since 2.2.2
	 *
	 * @see $this->social_metabox() Callback for Social Settings box.
	 */
	protected function social_metabox_facebook_tab() {

		$fb_author = $this->get_field_value( 'facebook_author' );
		$fb_author_placeholder = empty( $fb_publisher ) ? _x( 'http://www.facebook.com/YourPersonalProfile', 'Example Facebook Personal URL', 'autodescription' ) : '';

		$fb_publisher = $this->get_field_value( 'facebook_publisher' );
		$fb_publisher_placeholder = empty( $fb_publisher ) ? _x( 'http://www.facebook.com/YourVerifiedBusinessProfile', 'Example Verified Facebook Business URL', 'autodescription' ) : '';

		$fb_appid = $this->get_field_value( 'facebook_appid' );
		$fb_appid_placeholder = empty( $fb_appid ) ? '123456789012345' : '';

		?>
		<h4><?php _e( 'Default Facebook Integration Settings', 'autodescription' ); ?></h4>
		<p class="description"><?php _e( 'Facebook post sharing works mostly through Open Graph. However, you can also link your Business and Personal Facebook pages, among various other options.', 'autodescription' ); ?></p>
		<p class="description"><?php _e( 'When these options are filled in, Facebook might link your Facebook profile to be followed and liked when your post or page is shared.', 'autodescription' ); ?></p>

		<hr>

		<p>
			<label for="<?php $this->field_id( 'facebook_author' ); ?>">
				<strong><?php _e( 'Article Author Facebook URL', 'autodescription' ); ?></strong>
				<a href="<?php echo esc_url( 'https://facebook.com/me' ); ?>" class="description" target="_blank" title="<?php _e( 'Your Facebook Profile', 'autodescription' ); ?>">[?]</a>
			</label>
		</p>
		<p>
			<input type="text" name="<?php $this->field_name( 'facebook_author' ); ?>" class="large-text" id="<?php $this->field_id( 'facebook_author' ); ?>" placeholder="<?php echo $fb_author_placeholder ?>" value="<?php echo esc_attr( $fb_author ); ?>" />
		</p>

		<p>
			<label for="<?php $this->field_id( 'facebook_publisher' ); ?>">
				<strong><?php _e( 'Article Publisher Facebook URL', 'autodescription' ); ?></strong>
				<a href="<?php echo esc_url( 'https://instantarticles.fb.com/' ); ?>" class="description" target="_blank" title="<?php _e( 'To use this, you need to be a verified business', 'autodescription' ); ?>">[?]</a>
			</label>
		</p>
		<p>
			<input type="text" name="<?php $this->field_name( 'facebook_publisher' ); ?>" class="large-text" id="<?php $this->field_id( 'facebook_publisher' ); ?>" placeholder="<?php echo $fb_publisher_placeholder ?>" value="<?php echo esc_attr( $fb_publisher ); ?>" />
		</p>

		<p>
			<label for="<?php $this->field_id( 'facebook_appid' ); ?>">
				<strong><?php _e( 'Facebook App ID', 'autodescription' ); ?></strong>
				<a href="<?php echo esc_url( 'https://developers.facebook.com/apps' ); ?>" target="_blank" class="description" title="<?php _e( 'Get Facebook App ID', 'autodescription' ); ?>">[?]</a>
			</label>
		</p>
		<p>
			<input type="text" name="<?php $this->field_name( 'facebook_appid' ); ?>" class="large-text" id="<?php $this->field_id( 'facebook_appid' ); ?>" placeholder="<?php echo $fb_appid_placeholder ?>" value="<?php echo esc_attr( $fb_appid ); ?>" />
		</p>
		<?php

	}

	/**
	 * Social Metabox Twitter Tab Output
	 *
	 * @since 2.2.2
	 *
	 * @see $this->social_metabox() Callback for Social Settings box.
	 */
	protected function social_metabox_twitter_tab() {

		$tw_site = $this->get_field_value( 'twitter_site' );
		$tw_site_placeholder = empty( $tw_site ) ? _x( '@your-site-username', 'Twitter @username', 'autodescription' ) : '';

		$tw_creator = $this->get_field_value( 'twitter_creator' );
		$tw_creator_placeholder = empty( $tw_creator ) ? _x( '@your-personal-username', 'Twitter @username', 'autodescription' ) : '';

		$twitter_card = $this->get_twitter_card_types();

		?>
		<h4><?php _e( 'Default Twitter Integration Settings', 'autodescription' ); ?></h4>
		<p class="description"><?php printf( __( 'Twitter post sharing works mostly through Open Graph. However, you can also link your Business and Personal Twitter pages, among various other options.', 'autodescription' ) ); ?></p>

		<hr>

		<fieldset id="twitter-cards">
			<legend><h4><?php _e( 'Twitter Card Type', 'autodescription' ); ?></h4></legend>
			<p class="description"><?php printf( __( 'What kind of Twitter card would you like to use? It will default to %s if no image is found.', 'autodescription' ), $this->code_wrap( 'Summary' ) ); ?></p>

			<p class="theseoframework-fields">
			<?php
				foreach ( $twitter_card as $type => $name ) {
					?>
						<span class="toblock">
							<input type="radio" name="<?php $this->field_name( 'twitter_card' ); ?>" id="<?php $this->field_id( 'twitter_card_' . $type ); ?>" value="<?php echo $type ?>" <?php checked( $this->get_field_value( 'twitter_card' ), $type ); ?> />
							<label for="<?php $this->field_id( 'twitter_card_' . $type ); ?>">
								<span><?php echo $this->code_wrap( ucfirst( $name ) ); ?></span>
								<a class="description" href="<?php echo esc_url('https://dev.twitter.com/cards/types/' . $name ); ?>" target="_blank" title="Twitter Card <?php echo ucfirst( $name ) . ' ' . __( 'Example', 'autodescription' ); ?>"><?php _e( 'Example', 'autodescription' ); ?></a>
							</label>
						</span>
					<?php
				}
			?>
			</p>
		</fieldset>

		<hr>

		<p class="description"><?php printf( __( 'When the following options are filled in, Twitter might link your Twitter Site or Personal Profile when your post or page is shared.', 'autodescription' ) ); ?></p>
		<p>
			<label for="<?php $this->field_id( 'twitter_site' ); ?>" class="toblock">
				<strong><?php _e( "Your Website's Twitter Profile", 'autodescription' ); ?></strong>
				<a href="<?php echo esc_url( 'https://twitter.com/home' ); ?>" target="_blank" class="description" title="<?php _e( 'Find your @username', 'autodescription' ); ?>">[?]</a>
			</label>
		</p>
		<p>
			<input type="text" name="<?php $this->field_name( 'twitter_site' ); ?>" class="large-text" id="<?php $this->field_id( 'twitter_site' ); ?>" placeholder="<?php echo $tw_site_placeholder ?>" value="<?php echo esc_attr( $tw_site ); ?>" />
		</p>

		<p>
			<label for="<?php $this->field_id( 'twitter_creator' ); ?>" class="toblock">
				<strong><?php _e( 'Your Personal Twitter Profile', 'autodescription' ); ?></strong>
				<a href="<?php echo esc_url( 'https://twitter.com/home' ); ?>" target="_blank" class="description" title="<?php _e( 'Find your @username', 'autodescription' ); ?>">[?]</a>
			</label>
		</p>
		<p>
			<input type="text" name="<?php $this->field_name( 'twitter_creator' ); ?>" class="large-text" id="<?php $this->field_id( 'twitter_creator' ); ?>" placeholder="<?php echo $tw_creator_placeholder ?>" value="<?php echo esc_attr( $tw_creator ); ?>" />
		</p>
		<?php

	}

	/**
	 * Social Metabox PostDates Tab Output
	 *
	 * @since 2.2.4
	 *
	 * @see $this->social_metabox() Callback for Social Settings box.
	 */
	public function social_metabox_postdates_tab() {

		$pages_i18n = __( 'Pages', 'autodescription' );
		$posts_i18n = __( 'Posts', 'autodescription' );
		$home_i18n = __( 'Home Page', 'autodescription' );

		?>
		<h4><?php _e( 'Post Date Settings', 'autodescription' ); ?></h4>
		<p class="description"><?php _e( "Some Search Engines output the publishing date and modified date next to the search results. These help Search Engines find new content and could impact the SEO value.", 'autodescription' ); ?></p>
		<p class="description"><?php _e( "It's recommended on posts, but it's not recommended on pages unless you modify or create new pages frequently.", 'autodescription' ); ?></p>

		<?php
			/* translators: 1: Option, 2: Post Type */
			$post_publish_time_label = sprintf( __( 'Add %1$s to %2$s?', 'autodescription' ), $this->code_wrap( 'article:published_time' ), $posts_i18n );
			$post_publish_time_checkbox = $this->make_checkbox( 'post_publish_time', $post_publish_time_label, '' );

			/* translators: 1: Option, 2: Post Type */
			$page_publish_time_label = sprintf( __( 'Add %1$s to %2$s?', 'autodescription' ), $this->code_wrap( 'article:published_time' ), $pages_i18n );
			$page_publish_time_checkbox = $this->make_checkbox( 'page_publish_time', $page_publish_time_label, '' );

			//* Echo checkboxes.
			echo $this->wrap_fields( $post_publish_time_checkbox . $page_publish_time_checkbox );

			/* translators: 1: Option, 2: Post Type */
			$post_modify_time_label = sprintf( __( 'Add %1$s to %2$s?', 'autodescription' ), $this->code_wrap( 'article:modified_time' ), $posts_i18n );
			$post_modify_time_checkbox = $this->make_checkbox( 'post_modify_time', $post_modify_time_label, '' );

			/* translators: 1: Option, 2: Post Type */
			$page_modify_time_label = sprintf( __( 'Add %1$s to %2$s?', 'autodescription' ), $this->code_wrap( 'article:modified_time' ), $pages_i18n );
			$page_modify_time_checkbox = $this->make_checkbox( 'page_modify_time', $page_modify_time_label, '' );

			//* Echo checkboxes.
			echo $this->wrap_fields( $post_modify_time_checkbox . $page_modify_time_checkbox );
		?>

		<hr>

		<h4><?php _e( 'Home Page', 'autodescription' ); ?></h4>
		<p class="description"><?php _e( "Because you only publish the Home Page once, Search Engines might think your website is outdated. This can be prevented by disabling the following options.", 'autodescription' ); ?></p>

		<?php
			/* translators: 1: Option, 2: Post Type */
			$home_publish_time_label = sprintf( __( 'Add %1$s to %2$s?', 'autodescription' ), $this->code_wrap( 'article:published_time' ), $home_i18n );
			$home_publish_time_checkbox = $this->make_checkbox( 'home_publish_time', $home_publish_time_label, '' );

			/* translators: 1: Option, 2: Post Type */
			$home_modify_time_label = sprintf( __( 'Add %1$s to %2$s?', 'autodescription' ), $this->code_wrap( 'article:modified_time' ), $home_i18n );
			$home_modify_time_checkbox = $this->make_checkbox( 'home_modify_time', $home_modify_time_label, '' );

			//* Echo checkboxes.
			echo $this->wrap_fields( $home_publish_time_checkbox . $home_modify_time_checkbox );

	}

	/**
	 * Social Metabox Relationships Tab Output
	 *
	 * @since 2.2.4
	 *
	 * @see $this->social_metabox() Callback for Social Settings box.
	 */
	public function social_metabox_relationships_tab() {

		?>
		<h4><?php _e( 'Link Relationship Settings', 'autodescription' ); ?></h4>
		<p class="description"><?php _e( "Some Search Engines look for relations between the content of your pages. If you have multiple pages for a single Post or Page, or have archives indexed, this option will help Search Engines look for the right page to display in the Search Results.", 'autodescription' ); ?></p>
		<p class="description"><?php _e( "It's recommended to turn this option on for better SEO consistency and to prevent duplicate content errors.", 'autodescription' ); ?></p>

		<hr>
		<?php
			$prev_next_posts_label = sprintf( __( 'Add %s link tags to Posts and Pages?', 'autodescription' ), $this->code_wrap( 'rel' ) );
			$prev_next_posts_checkbox = $this->make_checkbox( 'prev_next_posts', $prev_next_posts_label, '' );

			$prev_next_archives_label = sprintf( __( 'Add %s link tags to Archives?', 'autodescription' ), $this->code_wrap( 'rel' ) );
			$prev_next_archives_checkbox = $this->make_checkbox( 'prev_next_archives', $prev_next_archives_label, '' );

			$prev_next_frontpage_label = sprintf( __( 'Add %s link tags to the Home Page?', 'autodescription' ), $this->code_wrap( 'rel' ) );
			$prev_next_frontpage_checkbox = $this->make_checkbox( 'prev_next_frontpage', $prev_next_frontpage_label, '' );

			//* Echo checkboxes.
			echo $this->wrap_fields( $prev_next_posts_checkbox . $prev_next_archives_checkbox . $prev_next_frontpage_checkbox );

	}

	/**
	 * Webmaster meta box on the Site SEO Settings page.
	 *
	 * @since 2.2.4
	 */
	public function webmaster_metabox() {

		do_action( 'the_seo_framework_webmaster_metabox_before' );

		$site_url = $this->the_home_url_from_cache();
		$language = $this->google_language();

		$bing_site_url = "https://www.bing.com/webmaster/configure/verify/ownership?url=" . urlencode( $site_url );
		$google_site_url = "https://www.google.com/webmasters/verification/verification?hl=" . $language . "&siteUrl=" . $site_url;
		$pint_site_url = "https://analytics.pinterest.com/";
		$yandex_site_url = "https://webmaster.yandex.com/site/verification.xml";

		?>
		<h4><?php _e( 'Webmaster Integration Settings', 'autodescription' ); ?></h4>
		<p class="description"><?php _e( "When adding your website to Google, Bing and other Webmaster Tools, you'll be asked to add a code or file to your website for verification purposes. These options will help you easily integrate those codes.", 'autodescription' ); ?></p>
		<p class="description"><?php _e( "Verifying your website has no SEO value whatsoever. But you might gain added benefits such as search ranking insights to help you improve your website's content.", 'autodescription' ); ?></p>

		<hr>

		<p>
			<label for="<?php $this->field_id( 'google_verification' ); ?>" class="toblock">
				<strong><?php _e( "Google Webmaster Verification Code", 'autodescription' ); ?></strong>
				<a href="<?php echo esc_url( $google_site_url ); ?>" target="_blank" class="description" title="<?php _e( 'Get the Google Verification code', 'autodescription' ); ?>">[?]</a>
			</label>
		</p>
		<p>
			<input type="text" name="<?php $this->field_name( 'google_verification' ); ?>" class="large-text" id="<?php $this->field_id( 'google_verification' ); ?>" placeholder="ABC1d2eFg34H5iJ6klmNOp7qRstUvWXyZaBc8dEfG9" value="<?php echo esc_attr( $this->get_field_value( 'google_verification' ) ); ?>" />
		</p>

		<p>
			<label for="<?php $this->field_id( 'bing_verification' ); ?>" class="toblock">
				<strong><?php _e( "Bing Webmaster Verification Code", 'autodescription' ); ?></strong>
				<a href="<?php echo esc_url( $bing_site_url ); ?>" target="_blank" class="description" title="<?php _e( 'Get the Bing Verification Code', 'autodescription' ); ?>">[?]</a>
			</label>
		</p>
		<p>
			<input type="text" name="<?php $this->field_name( 'bing_verification' ); ?>" class="large-text" id="<?php $this->field_id( 'bing_verification' ); ?>" placeholder="123A456B78901C2D3456E7890F1A234D" value="<?php echo esc_attr( $this->get_field_value( 'bing_verification' ) ); ?>" />
		</p>

		<p>
			<label for="<?php $this->field_id( 'yandex_verification' ); ?>" class="toblock">
				<strong><?php _e( "Yandex Webmaster Verification Code", 'autodescription' ); ?></strong>
				<a href="<?php echo esc_url( $yandex_site_url ); ?>" target="_blank" class="description" title="<?php _e( 'Get the Yandex Verification Code', 'autodescription' ); ?>">[?]</a>
			</label>
		</p>
		<p>
			<input type="text" name="<?php $this->field_name( 'yandex_verification' ); ?>" class="large-text" id="<?php $this->field_id( 'yandex_verification' ); ?>" placeholder="12345abc678901d2" value="<?php echo esc_attr( $this->get_field_value( 'yandex_verification' ) ); ?>" />
		</p>

		<p>
			<label for="<?php $this->field_id( 'pint_verification' ); ?>" class="toblock">
				<strong><?php _e( "Pinterest Analytics Verification Code", 'autodescription' ); ?></strong>
				<a href="<?php echo esc_url( $pint_site_url ); ?>" target="_blank" class="description" title="<?php _e( 'Get the Pinterest Verification Code', 'autodescription' ); ?>">[?]</a>
			</label>
		</p>
		<p>
			<input type="text" name="<?php $this->field_name( 'pint_verification' ); ?>" class="large-text" id="<?php $this->field_id( 'pint_verification' ); ?>" placeholder="123456a7b8901de2fa34bcdef5a67b98" value="<?php echo esc_attr( $this->get_field_value( 'pint_verification' ) ); ?>" />
		</p>
		<?php

		do_action( 'the_seo_framework_webmaster_metabox_after' );

	}

	/**
	 * Knowlegde Graph metabox on the Site SEO Settings page.
	 *
	 * @since 2.2.8
	 *
	 * @see $this->knowledge_metabox() Callback for Social Settings box.
	 */
	public function knowledge_metabox( $args = array() ) {

		do_action( 'the_seo_framework_knowledge_metabox_before' );

		/**
		 * Parse tabs content
		 *
		 * @param array $default_tabs { 'id' = The identifier =>
		 *			array(
		 *				'name' 		=> The name
		 *				'callback' 	=> The callback function, use array for method calling (accepts $this, but isn't used here for optimization purposes)
		 *				'dashicon'	=> Desired dashicon
		 *			)
		 * }
		 *
		 * @since 2.2.8
		 */
		$default_tabs = array(
			'general' => array(
				'name' 		=> __( 'General', 'autodescription' ),
				'callback'	=> array( $this, 'knowledge_metabox_general_tab' ),
				'dashicon'	=> 'admin-generic',
			),
			'website' => array(
				'name'		=> __( 'Website', 'autodescription' ),
				'callback'	=> array( $this, 'knowledge_metabox_about_tab' ),
				'dashicon'	=> 'admin-home',
			),
			'social' => array(
				'name'		=> 'Social Sites',
				'callback'	=> array( $this, 'knowledge_metabox_social_tab' ),
				'dashicon'	=> 'networking',
			),
		);

		/**
		 * Applies filter knowledgegraph_settings_tabs : Array see $default_tabs
		 *
		 * Used to extend Knowledge Graph tabs
		 */
		$defaults = (array) apply_filters( 'the_seo_framework_knowledgegraph_settings_tabs', $default_tabs, $args );

		$tabs = wp_parse_args( $args, $defaults );

		$this->nav_tab_wrapper( 'knowledge', $tabs, '2.2.8' );

		do_action( 'the_seo_framework_knowledge_metabox_after' );

	}

	/**
	 * Knowledge Graph Metabox General Tab Output
	 *
	 * @since 2.2.8
	 *
	 * @see $this->knowledge_metabox() Callback for Knowledge Graph Settings box.
	 */
	public function knowledge_metabox_general_tab() {

		?>
		<h4><?php _e( 'Knowledge Graph Settings', 'autodescription' ); ?></h4>
		<p><span class="description"><?php printf( __( "The Knowledge Graph lets Google and other Search Engines know where to find you or your organization and its relevant content.", 'autodescription' ) ); ?></span></p>
		<p><span class="description"><?php printf( __( "Google is becoming more of an 'Answer Engine' than a 'Search Engine'. Setting up these options could have a positive impact on the SEO value of your website.", 'autodescription' ) ); ?></span></p>

		<?php
			$knowledge_output_label = __( 'Output Knowledge tags?', 'autodescription' );
			$knowledge_output_checkbox = $this->make_checkbox( 'knowledge_output', $knowledge_output_label, '' );

			//* Echo checkbox.
			echo $this->wrap_fields( $knowledge_output_checkbox );

		if ( $this->wp_version( '4.2.999', '>=' ) ) :
		?>
			<hr>

			<h4><?php printf( _x( "Website logo", 'WordPress Customizer', 'autodescription' ) ); ?></h4>
			<?php
				$knowledge_logo_label = __( 'Use the Favicon from Customizer as the Organization Logo?', 'autodescription' );
				$knowledge_logo_description = __( "This option only has an effect when this site represents an Organization. If left disabled, Search Engines will look elsewhere for a logo, if it exists and is assigned as a logo.", 'autodescription' );
				$knowledge_logo_checkbox = $this->make_checkbox( 'knowledge_logo', $knowledge_logo_label, $knowledge_logo_description );

				//* Echo checkbox.
				echo $this->wrap_fields( $knowledge_logo_checkbox );
		endif;

	}

	/**
	 * Knowledge Graph Metabox About Tab Output
	 *
	 * @since 2.2.8
	 *
	 * @see $this->knowledge_metabox() Callback for Knowledge Graph Settings box.
	 */
	public function knowledge_metabox_about_tab() {

		$blogname = $this->get_blogname();

		?>
		<h4><?php _e( 'About this website', 'autodescription' ); ?></h4>
		<p><span class="description"><?php printf( __( 'Who or what is your website about?', 'autodescription' ) ); ?></span></p>

		<hr>

		<p>
			<label for="<?php $this->field_id( 'knowledge_type' ); ?>"><?php _ex( 'This website represents:', '...Organization or Person.', 'autodescription' ); ?></label>
			<select name="<?php $this->field_name( 'knowledge_type' ); ?>" id="<?php $this->field_id( 'knowledge_type' ); ?>">
			<?php
			$knowledge_type = (array) apply_filters(
				'the_seo_framework_knowledge_types',
				array(
					'organization'	=> __( 'An Organization', 'autodescription' ),
					'person' 		=> __( 'A Person', 'autodescription' ),
				)
			);
			foreach ( $knowledge_type as $value => $name )
				echo '<option value="' . esc_attr( $value ) . '"' . selected( $this->get_field_value( 'knowledge_type' ), esc_attr( $value ), false ) . '>' . esc_html( $name ) . '</option>' . "\n";
			?>
			</select>
		</p>

		<hr>

		<p>
			<label for="<?php $this->field_id( 'knowledge_name' ); ?>">
				<strong><?php _e( "The organization or personal name", 'autodescription' ); ?></strong>
			</label>
		</p>
		<p>
			<input type="text" name="<?php $this->field_name( 'knowledge_name' ); ?>" class="large-text" id="<?php $this->field_id( 'knowledge_name' ); ?>" placeholder="<?php echo esc_attr( $blogname ) ?>" value="<?php echo esc_attr( $this->get_field_value( 'knowledge_name' ) ); ?>" />
		</p>
		<?php

	}

	/**
	 * Knowledge Graph Metabox Social Tab Output
	 *
	 * @since 2.2.8
	 *
	 * @see $this->knowledge_metabox() Callback for Knowledge Graph Settings box.
	 */
	public function knowledge_metabox_social_tab() {

		?>
		<h4><?php _e( 'Social Pages connected to this website', 'autodescription' ); ?></h4>
		<p><span class="description"><?php _e( "Don't have a page at a site or is the profile only privately accessible? Leave that field empty. Unsure? Fill it in anyway.", 'autodescription' ); ?></span></p>
		<p><span class="description"><?php _e( "Add the link that leads directly to the social page of this website.", 'autodescription' ); ?></span></p>

		<hr>

		<?php
		$connectedi18n = _x( 'RelatedProfile', 'No spaces. E.g. https://facebook.com/RelatedProfile', 'autodescription' );
		$profile18n = _x( 'Profile', 'Social Profile', 'autodescription' );

		/**
		 * @todo maybe genericons?
		 */

		$socialsites = array(
			'facebook' => array(
				'option'		=> 'knowledge_facebook',
				'dashicon'		=> 'dashicons-facebook',
				'desc' 			=> 'Facebook ' . __( 'Page', 'autodescription' ),
				'placeholder'	=> 'http://www.facebook.com/' . $connectedi18n,
				'examplelink'	=> esc_url( 'https://facebook.com/me' ),
			),
			'twitter' => array(
				'option'		=> 'knowledge_twitter',
				'dashicon'		=> 'dashicons-twitter',
				'desc' 			=> 'Twitter ' . $profile18n,
				'placeholder'	=> 'http://www.twitter.com/' . $connectedi18n,
				'examplelink'	=> esc_url( 'https://twitter.com/home' ), // No example link available.
			),
			'gplus' => array(
				'option'		=> 'knowledge_gplus',
				'dashicon'		=> 'dashicons-googleplus',
				'desc' 			=>  'Google+ ' . $profile18n,
				'placeholder'	=> 'https://plus.google.com/' . $connectedi18n,
				'examplelink'	=> esc_url( 'https://plus.google.com/me' ),
			),
			'instagram' => array(
				'option'		=> 'knowledge_instagram',
				'dashicon'		=> 'genericon-instagram',
				'desc' 			=> 'Instagram ' . $profile18n,
				'placeholder'	=> 'http://instagram.com/' . $connectedi18n,
				'examplelink'	=> esc_url( 'https://instagram.com/' ), // No example link available.
			),
			'youtube' => array(
				'option'		=> 'knowledge_youtube',
				'dashicon'		=> 'genericon-youtube',
				'desc' 			=> 'Youtube ' . $profile18n,
				'placeholder'	=> 'http://www.youtube.com/' . $connectedi18n,
				'examplelink'	=> esc_url( 'https://www.youtube.com/user/%2f' ), // Yes a double slash.
			),
			'linkedin' => array(
				'option'		=> 'knowledge_linkedin',
				'dashicon'		=> 'genericon-linkedin-alt',
				'desc' 			=> 'LinkedIn ' . $profile18n . ' ID',
				'placeholder'	=> 'http://www.linkedin.com/profile/view?id=' . $connectedi18n,
				'examplelink'	=> esc_url( 'https://www.linkedin.com/profile/view' ), // This generates a query arg. We should allow that.
			),
			'pinterest' => array(
				'option'		=> 'knowledge_pinterest',
				'dashicon'		=> 'genericon-pinterest-alt',
				'desc' 			=> 'Pinterest ' . $profile18n,
				'placeholder'	=> 'https://www.pinterest.com/' . $connectedi18n . '/',
				'examplelink'	=> esc_url( 'https://www.pinterest.com/me/' ),
			),
			'soundcloud' => array(
				'option'		=> 'knowledge_soundcloud',
				'dashicon'		=> 'genericon-cloud', // I know, it's not the real one. D:
				'desc' 			=> 'SoundCloud ' . $profile18n,
				'placeholder'	=> 'https://soundcloud.com/' . $connectedi18n,
				'examplelink'	=> esc_url( 'https://soundcloud.com/you' ),
			),
			'tumblr' => array(
				'option'		=> 'knowledge_tumblr',
				'dashicon'		=> 'genericon-tumblr',
				'desc' 			=> 'Tumblr ' . __( 'Blog', 'autodescription' ),
				'placeholder'	=> 'https://tumblr.com/blog/' . $connectedi18n,
				'examplelink'	=> esc_url( 'https://www.tumblr.com/dashboard' ),  // No example link available.
			),
		);

		foreach ( $socialsites as $key => $value ) {
			?>
			<p>
				<label for="<?php $this->field_id( $value['option'] ); ?>">
					<strong><?php echo $value['desc'] ?></strong>
					<?php
					if ( $value['examplelink'] ) {
						?><a href="<?php echo esc_url( $value['examplelink'] ); ?>" target="_blank">[?]</a><?php
					}
					?>
				</label>
			</p>
			<p>
				<input type="text" name="<?php $this->field_name( $value['option'] ); ?>" class="large-text" id="<?php $this->field_id( $value['option'] ); ?>" placeholder="<?php echo esc_attr( $value['placeholder'] ) ?>" value="<?php echo esc_attr( $this->get_field_value( $value['option'] ) ); ?>" />
			</p>
			<?php
		}

	}

	/**
	 * Sitemaps meta box on the Site SEO Settings page.
	 *
	 * @since 2.2.9
	 *
	 * @see $this->sitemaps_metabox() Callback for Sitemaps Settings box.
	 */
	public function sitemaps_metabox( $args = array() ) {

		do_action( 'the_seo_framework_sitemaps_metabox_before' );

		if ( '' === $this->permalink_structure() ) {

			$permalink_settings_url = esc_url( admin_url( 'options-permalink.php' ) );
			$here = '<a href="' . $permalink_settings_url  . '" target="_blank" title="' . __( 'Permalink Settings', 'autodescription' ) . '">' . _x( 'here', 'The sitemap can be found %s.', 'autodescription' ) . '</a>';

			?>
			<h4><?php _e( "You're using the plain permalink structure.", 'autodescription' ); ?></h4>
			<p><span class="description"><?php _e( "This means we can't output the sitemap through the WordPress rewrite rules.", 'autodescription' ); ?></span></p>
			<hr>
			<p><span class="description"><?php printf( _x( "Change your Permalink Settings %s (Recommended: 'postname').", '%s = here', 'autodescription' ), $here ); ?></span></p>
			<?php

		} else {

			/**
			 * Parse tabs content
			 *
			 * @param array $default_tabs { 'id' = The identifier =>
			 *			array(
			 *				'name' 		=> The name
			 *				'callback' 	=> The callback function, use array for method calling (accepts $this, but isn't used here for optimization purposes)
			 *				'dashicon'	=> Desired dashicon
			 *			)
			 * }
			 *
			 * @since 2.2.9
			 */
			$default_tabs = array(
				'general' => array(
					'name' 		=> __( 'General', 'autodescription' ),
					'callback'	=> array( $this, 'sitemaps_metabox_general_tab' ),
					'dashicon'	=> 'admin-generic',
				),
				'robots' => array(
					'name'		=> 'Robots.txt',
					'callback'	=> array( $this, 'sitemaps_metabox_robots_tab' ),
					'dashicon'	=> 'share-alt2',
				),
				'timestamps' => array(
					'name'		=> __( 'Timestamps', 'autodescription' ),
					'callback'	=> array( $this, 'sitemaps_metabox_timestamps_tab' ),
					'dashicon'	=> 'backup',
				),
				'notify' => array(
					'name'		=> _x( 'Ping', 'Ping or notify Search Engine', 'autodescription' ),
					'callback'	=> array( $this, 'sitemaps_metabox_notify_tab' ),
					'dashicon'	=> 'megaphone',
				),
			);

			/**
			 * Applies filters the_seo_framework_sitemaps_settings_tabs : array see $default_tabs
			 *
			 * Used to extend Knowledge Graph tabs
			 */
			$defaults = (array) apply_filters( 'the_seo_framework_sitemaps_settings_tabs', $default_tabs, $args );

			$tabs = wp_parse_args( $args, $defaults );
			$use_tabs = true;

			$sitemap_plugin = $this->has_sitemap_plugin();
			$sitemap_detected = $this->has_sitemap_xml();
			$robots_detected = $this->has_robots_txt();

			/**
			 * Remove the timestamps and notify submenus
			 * @since 2.5.2
			 */
			if ( $sitemap_plugin || $sitemap_detected ) {
				unset( $tabs['timestamps'] );
				unset( $tabs['notify'] );
			}

			/**
			 * Remove the robots submenu
			 * @since 2.5.2
			 */
			if ( $robots_detected ) {
				unset( $tabs['robots'] );
			}

			if ( $robots_detected && ( $sitemap_plugin || $sitemap_detected ) )
				$use_tabs = false;

			$this->nav_tab_wrapper( 'sitemaps', $tabs, '2.2.8', $use_tabs );

		}

		do_action( 'the_seo_framework_sitemaps_metabox_after' );

	}

	/**
	 * Sitemaps Metabox General Tab Output
	 *
	 * @since 2.2.9
	 *
	 * @see $this->sitemaps_metabox() Callback for Sitemaps Settings box.
	 */
	public function sitemaps_metabox_general_tab() {

		$site_url = $this->the_home_url_from_cache( true );

		$sitemap_url = $site_url . 'sitemap.xml';
		$has_sitemap_plugin = $this->has_sitemap_plugin();
		$sitemap_detected = $this->has_sitemap_xml();

		?>
		<h4><?php _e( 'Sitemap Integration Settings', 'autodescription' ); ?></h4>
		<?php

		if ( $has_sitemap_plugin ) {
			?>
			<p class="description"><?php _e( "Another active sitemap plugin has been detected. This means that the sitemap functionality has been replaced.", 'autodescription' ); ?></p>
			<?php
		} else if ( $sitemap_detected ) {
			?>
			<p class="description"><?php _e( "A sitemap has been detected in the root folder of your website. This means that the sitemap functionality has no effect.", 'autodescription' ); ?></p>
			<?php
		} else {
			?>
			<p class="description"><?php _e( "The Sitemap is an XML file that lists pages and posts for your website along with optional metadata about each post or page. This helps Search Engines crawl your website more easily.", 'autodescription' ); ?></p>
			<p class="description"><?php _e( "The optional metadata include the post and page modified time and a page priority indication, which is automated.", 'autodescription' ); ?></p>

			<hr>

			<h4><?php _e( 'Sitemap Output', 'autodescription' ); ?></h4>
			<?php
				$sitemaps_output_label = __( 'Output Sitemap?', 'autodescription' );
				$sitemaps_output_checkbox = $this->make_checkbox( 'sitemaps_output', $sitemaps_output_label, '' );

				//* Echo checkbox.
				echo $this->wrap_fields( $sitemaps_output_checkbox );
		}

		if ( ! ( $has_sitemap_plugin || $sitemap_detected ) && $this->get_option( 'sitemaps_output' ) ) {
			$here = '<a href="' . $sitemap_url  . '" target="_blank" title="' . __( 'View sitemap', 'autodescription' ) . '">' . _x( 'here', 'The sitemap can be found %s.', 'autodescription' ) . '</a>';

			?><p class="description"><?php printf( _x( 'The sitemap can be found %s.', '%s = here', 'autodescription' ), $here ); ?></p><?php
		}

	}

	/**
	 * Sitemaps Metabox Robots Tab Output
	 *
	 * @since 2.2.9
	 *
	 * @see $this->sitemaps_metabox() Callback for Sitemaps Settings box.
	 */
	public function sitemaps_metabox_robots_tab() {

		$site_url = $this->the_home_url_from_cache( true );

		$robots_url = trailingslashit( $site_url ) . 'robots.txt';
		$here =  '<a href="' . $robots_url  . '" target="_blank" title="' . __( 'View robots.txt', 'autodescription' ) . '">' . _x( 'here', 'The sitemap can be found %s.', 'autodescription' ) . '</a>';

		?>
		<h4><?php _e( 'Robots.txt Settings', 'autodescription' ); ?></h4>
		<?php
		if ( $this->can_do_sitemap_robots() ) :
			?>
			<p class="description"><?php _e( 'The robots.txt file is the first thing Search Engines look for. If you add the sitemap location in the robots.txt file, then Search Engines will look for and index the sitemap.', 'autodescription' ); ?></p>
			<p class="description"><?php _e( 'If you do not add the sitemap location to the robots.txt file, you will need to notify Search Engines manually through the Webmaster Console provided by the Search Engines.', 'autodescription' ); ?></p>

			<hr>

			<h4><?php _e( 'Add sitemap location in robots.txt', 'autodescription' ); ?></h4>
			<?php
			//* Echo checkbox.
			$this->wrap_fields(
				$this->make_checkbox(
					'sitemaps_robots',
					__( 'Add sitemap location in robots?', 'autodescription' ),
					''
				), true
			);
		else :
			?>
			<p class="description"><?php _e( 'Another robots.txt sitemap Location addition has been detected.', 'autodescription' ); ?></p>
			<?php
		endif;

		?>
		<p class="description"><?php printf( _x( 'The robots.txt file can be found %s.', '%s = here', 'autodescription' ), $here ); ?></p>
		<?php

	}

	/**
	 * Sitemaps Metabox Timestamps Tab Output
	 *
	 * @since 2.2.9
	 *
	 * @see $this->sitemaps_metabox() Callback for Sitemaps Settings box.
	 */
	public function sitemaps_metabox_timestamps_tab() {

		//* Sets timezone according to WordPress settings.
		$this->set_timezone();

		$timestamp_0 = date( 'Y-m-d' );

		/**
		 * @link https://www.w3.org/TR/NOTE-datetime
		 * We use the second expression of the time zone offset handling.
		 */
		$timestamp_1 = date( 'Y-m-d\TH:iP' );

		//* Reset timezone to default.
		$this->reset_timezone();

		?>
		<h4><?php _e( 'Timestamps Settings', 'autodescription' ); ?></h4>
		<p><span class="description"><?php printf( __( 'The modified time suggests to Search Engines where to look for content changes. It has no impact on the SEO value unless you drastically change pages or posts. It then depends on how well your content is constructed.', 'autodescription' ) ); ?></span></p>
		<p><span class="description"><?php printf( __( "By default, the sitemap only outputs the modified date if you've enabled them within the Social Metabox. This setting overrides those settings for the Sitemap.", 'autodescription' ) ); ?></span></p>

		<hr>

		<h4><?php _e( 'Output Modified Date', 'autodescription' ); ?></h4>
		<?php
			$sitemaps_modified_label = sprintf( __( 'Add %s to the sitemap?', 'autodescription' ), $this->code_wrap( '<lastmod>' ) );
			$sitemaps_modified_checkbox = $this->make_checkbox( 'sitemaps_modified', $sitemaps_modified_label, '' );

			//* Echo checkbox.
			echo $this->wrap_fields( $sitemaps_modified_checkbox );
		?>

		<hr>

		<fieldset>
			<legend><h4><?php _e( 'Timestamp Format Settings', 'autodescription' ); ?></h4></legend>
			<p>
				<span class="description"><?php _e( 'Determines how specific the modification timestamp is.', 'autodescription' ); ?></span>
			</p>

			<p id="sitemaps-timestamp-format" class="theseoframework-fields">
				<span class="toblock">
					<input type="radio" name="<?php $this->field_name( 'sitemap_timestamps' ); ?>" id="<?php $this->field_id( 'sitemap_timestamps_0' ); ?>" value="0" <?php checked( $this->get_field_value( 'sitemap_timestamps' ), '0' ); ?> />
					<label for="<?php $this->field_id( 'sitemap_timestamps_0' ); ?>">
						<span title="<?php _e( 'Complete date', 'autodescription' ); ?>"><?php echo $this->code_wrap( $timestamp_0 ) ?> [?]</span>
					</label>
				</span>
				<span class="toblock">
					<input type="radio" name="<?php $this->field_name( 'sitemap_timestamps' ); ?>" id="<?php $this->field_id( 'sitemap_timestamps_1' ); ?>" value="1" <?php checked( $this->get_field_value( 'sitemap_timestamps' ), '1' ); ?> />
					<label for="<?php $this->field_id( 'sitemap_timestamps_1' ); ?>">
						<span title="<?php _e( 'Complete date plus hours, minutes and timezone', 'autodescription' ); ?>"><?php echo $this->code_wrap( $timestamp_1 ); ?> [?]</span>
					</label>
				</span>
			</p>
		</fieldset>
		<?php

	}

	/**
	 * Sitemaps Metabox Notify Tab Output
	 *
	 * @since 2.2.9
	 *
	 * @see $this->sitemaps_metabox() Callback for Sitemaps Settings box.
	 */
	public function sitemaps_metabox_notify_tab() {

		?>
		<h4><?php _e( 'Ping Settings', 'autodescription' ); ?></h4>
		<p><span class="description"><?php _e( "Notifying Search Engines of a sitemap change is helpful to get your content indexed as soon as possible.", 'autodescription' ); ?></span></p>
		<p><span class="description"><?php _e( "By default this will happen at most once an hour.", 'autodescription' ); ?></span></p>

		<hr>

		<h4><?php _e( 'Notify Search Engines', 'autodescription' ); ?></h4>
		<?php
			$engines = array(
				'ping_google'	=> 'Google',
				'ping_bing' 	=> 'Bing',
				'ping_yandex'	=> 'Yandex'
			);

			$ping_checkbox = '';

			foreach ( $engines as $option => $engine ) {
				$ping_label = sprintf( __( 'Notify %s about sitemap changes?', 'autodescription' ), $engine );
				$ping_checkbox .= $this->make_checkbox( $option, $ping_label, '' );
			}

			//* Echo checkbox.
			$this->wrap_fields( $ping_checkbox, true );

	}

	/**
	 * Feed meta box on the Site SEO Settings page.
	 *
	 * @since 2.5.2
	 */
	public function feed_metabox() {

		do_action( 'the_seo_framework_feed_metabox_before' );

		?>
		<h4><?php _e( 'Content Feed Settings', 'autodescription' ); ?></h4>
		<p class="description"><?php _e( "Sometimes, your content can get stolen by robots through the WordPress feeds. This can cause duplicate content issues. To prevent this from happening, it's recommended to convert the feed's content into an excerpt.", 'autodescription' ); ?></p>
		<p class="description"><?php _e( "Adding a backlink below the feed's content will also let the visitors know where the content came from.", 'autodescription' ); ?></p>

		<hr>

		<h4><?php _e( 'Change Feed Settings', 'autodescription' ); ?></h4>
		<?php
			$excerpt_the_feed_label = __( 'Convert feed content into excerpts?', 'autodescription' );
			$excerpt_the_feed_label .= ' ' . $this->make_info( __( "By default the excerpt will be at most 400 characters long", 'autodescription' ), '', false );

			$source_the_feed_label = __( 'Add backlinks below the feed content?', 'autodescription' );
			$source_the_feed_label .= ' ' . $this->make_info( __( "This link will not be followed by Search Engines", 'autodescription' ), '', false );

			//* Echo checkboxes.
			$this->wrap_fields(
				array(
					$this->make_checkbox( 'excerpt_the_feed', $excerpt_the_feed_label, '' ),
					$this->make_checkbox( 'source_the_feed', $source_the_feed_label, '' ),
				), true
			);

		if ( $this->rss_uses_excerpt() ) {
			$reading_settings_url = esc_url( admin_url( 'options-reading.php' ) );
			$reading_settings = '<a href="' . $reading_settings_url  . '" target="_blank" title="' . __( 'Reading Settings', 'autodescription' ) . '">' . __( 'Reading Settings', 'autodescription' ) . '</a>';

			?><p><span class="description"><?php
				printf( _x( "Note: The feed is already converted into an excerpt through the %s.", '%s = Reading Settings', 'autodescription' ), $reading_settings );
			?></span></p><?php
		}

		$feed_url = esc_url( get_feed_link() );
		$here = '<a href="' . $feed_url  . '" target="_blank" title="' . __( 'View feed', 'autodescription' ) . '">' . _x( 'here', 'The feed can be found %s.', 'autodescription' ) . '</a>';

		?><p class="description"><?php printf( _x( 'The feed can be found %s.', '%s = here', 'autodescription' ), $here ); ?></p><?php

		do_action( 'the_seo_framework_feed_metabox_after' );

	}

	/**
	 * Schema metabox.
	 *
	 * @since 2.6.0
	 */
	public function schema_metabox() {

		do_action( 'the_seo_framework_schema_metabox_before' );

		?>
		<h4><?php _e( 'Schema.org Output Settings', 'autodescription' ); ?></h4>
		<p class="description"><?php _e( "The Schema.org markup is a standard way of annotating structured data for Search Engines. This markup is represented within hidden scripts throughout the website.", 'autodescription' ); ?></p>
		<p class="description"><?php _e( "When your web pages include structured data markup, Search Engines can use that data to index your content better, present it more prominently in Search Results, and use it in several different applications.", 'autodescription' ); ?></p>

		<hr>

		<?php /* translators: https://developers.google.com/structured-data/slsb-overview */ ?>
		<h4><?php _ex( 'Sitelinks Search Box', 'Product name', 'autodescription' ); ?></h4>
		<p class="description"><?php _e( 'When Search users search for your brand name, the following option allows them to search through your website directly from the Search Results.', 'autodescription' ); ?></p>
		<?php
		$info = $this->make_info(
			_x( 'Sitelinks Search Box', 'Product name', 'autodescription' ),
			'https://developers.google.com/structured-data/slsb-overview',
			false
		);
		$this->wrap_fields(
			$this->make_checkbox(
				'ld_json_searchbox',
				_x( 'Enable Sitelinks Search Box?', 'Product name', 'autodescription' ) . ' ' . $info,
				''
			),
			true
		);
		?>

		<hr>

		<h4><?php _e( 'Site Name', 'autodescription' ); ?></h4>
		<p class="description"><?php _e( "When using breadcrumbs, the first entry is by default your website's address. Using the following option will convert it to the Site Name.", 'autodescription' ); ?></p>
		<?php
		$info = $this->make_info(
			__( 'Include your Site Name in Search Results', 'autodescription' ),
			'https://developers.google.com/structured-data/site-name',
			false
		);
		$description = sprintf( __( "The Site Name is: %s", 'autodescription' ), $this->code_wrap( $this->get_blogname() ) );
		$this->wrap_fields(
			$this->make_checkbox(
				'ld_json_sitename',
				__( 'Convert URL to Site Name?', 'autodescription' ) . ' ' . $info,
				$description
			),
			true
		);
		?>

		<hr>

		<h4><?php _e( 'Breadcrumbs', 'autodescription' ); ?></h4>
		<p class="description"><?php _e( "Breadcrumb trails indicate the page's position in the site hierarchy. Using the following option will show the hierarchy within the Search Results when available.", 'autodescription' ); ?></p>
		<?php
		$info = $this->make_info(
			__( 'About Breadcrumbs', 'autodescription' ),
			'https://developers.google.com/structured-data/breadcrumbs',
			false
		);
		$description = __( "Multiple trails can be outputted. The longest trail is prioritized.", 'autodescription' );
		$this->wrap_fields(
			$this->make_checkbox(
				'ld_json_breadcrumbs',
				__( 'Enable Breadcrumbs?', 'autodescription' ) . ' ' . $info,
				$description
			),
			true
		);

		do_action( 'the_seo_framework_schema_metabox_after' );

	}

}
