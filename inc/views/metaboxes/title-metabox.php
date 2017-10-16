<?php

defined( 'ABSPATH' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

//* Fetch the required instance within this file.
$instance = $this->get_view_instance( 'the_seo_framework_title_metabox', $instance );

switch ( $instance ) :
	case 'the_seo_framework_title_metabox_main' :

		$latest_post_id = $this->get_latest_post_id();

		if ( $latest_post_id ) {
			$post = get_post( $latest_post_id, OBJECT );
			$title = esc_attr( $post->post_title );
		} else {
			$title = esc_attr__( 'Example Post Title', 'autodescription' );
		}

		$blogname = $this->get_blogname();
		$sep = $this->get_separator( 'title' );

		$additions_left = '<span class="tsf-title-additions-js">' . $blogname . '<span class="tsf-sep-js">' . " $sep " . '</span></span>';
		$additions_right = '<span class="tsf-title-additions-js"><span class="tsf-sep-js">' . " $sep " . '</span>' . $blogname . '</span>';

		$example_left = '<em>' . $additions_left . $title . '</em>';
		$example_right = '<em>' . $title . $additions_right . '</em>';

		//* There's no need for "hide-if-no-js" here.
		//* Check left first, as right is default (and thus fallback).
		$showleft = 'left' === $this->get_option( 'title_location' );

		?><h4><?php esc_html_e( 'Automated Title Settings', 'autodescription' ); ?></h4><?php
		$this->description( __( 'The page title is prominently shown within the browser tab as well as within the Search Engine results pages.', 'autodescription' ) );

		?>
		<h4><?php esc_html_e( 'Example Automated Title Output', 'autodescription' ); ?></h4>
		<p>
			<span class="tsf-title-additions-example-left" style="display:<?php echo $showleft ? 'inline' : 'none'; ?>"><?php echo $this->code_wrap_noesc( $example_left ); ?></span>
			<span class="tsf-title-additions-example-right" style="display:<?php echo $showleft ? 'none' : 'inline'; ?>"><?php echo $this->code_wrap_noesc( $example_right ); ?></span>
		</p>

		<hr>
		<?php

		/**
		 * Parse tabs content.
		 *
		 * @since 2.2.2
		 *
		 * @param array $default_tabs { 'id' = The identifier =>
		 *		array(
		 *			'name'     => The name
		 *			'callback' => The callback function, use array for method calling
		 *			'dashicon' => Desired dashicon
		 *		)
		 * }
		 */
		$default_tabs = array(
			'general' => array(
				'name'     => __( 'General', 'autodescription' ),
				'callback' => array( $this, 'title_metabox_general_tab' ),
				'dashicon' => 'admin-generic',
			),
			'additions' => array(
				'name'     => __( 'Additions', 'autodescription' ),
				'callback' => array( $this, 'title_metabox_additions_tab' ),
				'dashicon' => 'plus',
				'args'     => array(
					'examples' => array(
						'left'  => $example_left,
						'right' => $example_right,
					),
				),
			),
			'prefixes' => array(
				'name'     => __( 'Prefixes', 'autodescription' ),
				'callback' => array( $this, 'title_metabox_prefixes_tab' ),
				'dashicon' => 'plus-alt',
				'args'     => array(
					'additions' => array(
						'left'  => $additions_left,
						'right' => $additions_right,
					),
					'showleft' => $showleft,
				),
			),
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
		break;

	case 'the_seo_framework_title_metabox_general' :
		$title_separator = $this->get_separator_list();
		$recommended = ' class="tsf-recommended" title="' . esc_attr__( 'Recommended', 'autodescription' ) . '"';

		?>
		<fieldset>
			<legend>
				<h4><?php esc_html_e( 'Title Separator', 'autodescription' ); ?></h4>
				<?php $this->description( __( 'If the title consists of two parts (original title and optional addition), then the separator will go in-between them.', 'autodescription' ) ); ?>
			</legend>
			<p id="tsf-title-separator" class="tsf-fields">
			<?php foreach ( $title_separator as $name => $html ) { ?>
				<input type="radio" name="<?php $this->field_name( 'title_seperator' ); ?>" id="<?php $this->field_id( 'title_seperator_' . $name ); ?>" value="<?php echo esc_attr( $name ); ?>" <?php checked( $this->get_field_value( 'title_seperator' ), $name ); ?> />
				<label for="<?php $this->field_id( 'title_seperator_' . $name ); ?>" <?php echo in_array( $name, array( 'dash', 'pipe' ), true ) ? $recommended : ''; ?>><?php echo esc_html( $html ); ?></label>
			<?php } ?>
			</p>
		</fieldset>
		<?php
		break;

	case 'the_seo_framework_title_metabox_additions' :

		$language = $this->google_language();

		$example_left = $examples['left'];
		$example_right = $examples['right'];

		$home_page_has_option = __( 'The Home Page has a specific option.', 'autodescription' );

		?>
		<fieldset>
			<legend>
				<h4><?php esc_html_e( 'Title Additions Location', 'autodescription' ); ?></h4>
				<?php $this->description( __( 'This setting determines which side the added title text will go on.', 'autodescription' ) ); ?>
			</legend>
			<p id="tsf-title-location" class="tsf-fields">
				<span class="tsf-toblock">
					<input type="radio" name="<?php $this->field_name( 'title_location' ); ?>" id="<?php $this->field_id( 'title_location_left' ); ?>" value="left" <?php checked( $this->get_field_value( 'title_location' ), 'left' ); ?> />
					<label for="<?php $this->field_id( 'title_location_left' ); ?>">
						<span><?php esc_html_e( 'Left:', 'autodescription' ); ?></span>
						<?php echo $this->code_wrap_noesc( $example_left ) ?>
					</label>
				</span>
				<span class="tsf-toblock">
					<input type="radio" name="<?php $this->field_name( 'title_location' ); ?>" id="<?php $this->field_id( 'title_location_right' ); ?>" value="right" <?php checked( $this->get_field_value( 'title_location' ), 'right' ); ?> />
					<label for="<?php $this->field_id( 'title_location_right' ); ?>">
						<span><?php esc_html_e( 'Right:', 'autodescription' ); ?></span>
						<?php echo $this->code_wrap_noesc( $example_right ); ?>
					</label>
				</span>
			</p>
			<?php $this->description( $home_page_has_option ); ?>
		</fieldset>
		<?php

		//* Only add this option if the theme is doing it right.
		if ( $this->can_manipulate_title() ) :
			?>
			<hr>

			<h4><?php esc_html_e( 'Remove Blogname from Title', 'autodescription' ); ?></h4>
			<div id="tsf-title-additions-toggle">
				<?php
				$info = $this->make_info(
					__( 'This might decouple your posts and pages from the rest of the website.', 'autodescription' ),
					'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#3',
					false
				);

				$this->wrap_fields(
					$this->make_checkbox(
						'title_rem_additions',
						esc_html__( 'Remove Blogname from title?', 'autodescription' ) . ' ' . $info,
						'',
						false
					),
					true
				);
				?>
			</div>
			<?php
			$this->description( __( 'Only use this option if you are aware of its SEO effects.', 'autodescription' ), false );
			echo ' ';
			$this->description( $home_page_has_option, false );
		endif;
		break;

	case 'the_seo_framework_title_metabox_prefixes' :

		//* Get translated category label, if it exists. Otherwise, fallback to translation.
		$term_labels = $this->get_tax_labels( 'category' );
		$label = isset( $term_labels->singular_name ) ? $term_labels->singular_name : __( 'Category', 'autodescription' );

		/**
		 * @since WordPress Core 4.5.0 get_terms first parameter is converted to the latter.
		 */
		$cats = get_terms( array(), array( 'taxonomy' => 'category', 'fields' => 'ids', 'hide_empty' => false, 'order' => 'ASC', 'number' => 1 ) );
		if ( is_array( $cats ) && ! empty( $cats ) ) {
			//* Category should exist.
			$cat = reset( $cats );
		} else {
			//* Default fallback category.
			$cat = 1;
		}

		//* If cat is found, it will return its name. Otherwise it's an empty string.
		$cat_name = get_cat_name( $cat );
		$cat_name = $cat_name ?: __( 'Example Category', 'autodescription' );

		$display_prefix = $this->is_option_checked( 'title_rem_prefixes' ) ? 'none' : 'inline';
		$title = '<span class="tsf-title-prefix-example" style="display:' . $display_prefix . '">' . esc_html( $label ) . ': </span>' . esc_html( $cat_name );

		$additions_left = $additions['left'];
		$additions_right = $additions['right'];

		$example_left = '<em>' . $additions_left . $title . '</em>';
		$example_right = '<em>' . $title . $additions_right . '</em>';

		$language = $this->google_language();

		/**
		 * @todo use checkbox function
		 * @priority low 2.6.x
		 */

		?><h4><?php esc_html_e( 'Title prefix options', 'autodescription' ); ?></h4><?php
		$this->description( __( 'On archives a descriptive prefix may be added to the title.', 'autodescription' ) );

		?>
		<h4><?php esc_html_e( 'Example Automated Archive Title Output', 'autodescription' ); ?></h4>
		<p>
			<span class="tsf-title-additions-example-left" style="display:<?php echo $showleft ? 'inline' : 'none'; ?>"><?php echo $this->code_wrap_noesc( $example_left ); ?></span>
			<span class="tsf-title-additions-example-right" style="display:<?php echo $showleft ? 'none' : 'inline'; ?>"><?php echo $this->code_wrap_noesc( $example_right ); ?></span>
		</p>

		<hr>

		<h4><?php esc_html_e( 'Remove Archive Title Prefixes', 'autodescription' ); ?></h4>
		<p id="title-prefixes-toggle">
			<label for="<?php $this->field_id( 'title_rem_prefixes' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'title_rem_prefixes' ); ?>" id="<?php $this->field_id( 'title_rem_prefixes' ); ?>" <?php $this->is_conditional_checked( 'title_rem_prefixes' ); ?> value="1" <?php checked( $this->get_field_value( 'title_rem_prefixes' ) ); ?> />
				<?php esc_html_e( 'Remove prefixes from title?', 'autodescription' ); ?>
			</label>
			<?php
			$this->make_info(
				__( "The prefix helps visitors and search engines determine what kind of page they're visiting.", 'autodescription' ),
				'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#3',
				true
			);
			?>
		</p>
		<?php
		break;

	default :
		break;
endswitch;
