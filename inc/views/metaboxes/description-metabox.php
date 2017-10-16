<?php

defined( 'ABSPATH' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

//* Fetch the required instance within this file.
$instance = $this->get_view_instance( 'the_seo_framework_description_metabox', $instance );

switch ( $instance ) :
	case 'the_seo_framework_description_metabox_main' :

		$blogname = $this->escape_description( $this->get_blogname() );
		$sep = $this->get_separator( 'description' );

		/**
		 * Generate example.
		 */
		$page_title = $this->escape_description( __( 'Example Title', 'autodescription' ) );
		$on = $this->escape_description( _x( 'on', 'Placement. e.g. Post Title "on" Blog Name', 'autodescription' ) );
		$excerpt = $this->escape_description( __( 'This is an example description...', 'autodescription' ) );

		//* Put it together.
		$example	= '<span id="tsf-description-additions-js">'
						. $page_title
						. '<span id="tsf-on-blogname-js">' . " $on " . $blogname . '</span>'
						. '<span id="autodescription-descsep-js">' . " $sep " . '</span>'
					. '</span>'
					. $excerpt
					;

		$nojs_additions = '';
		//* Add or remove additions based on option.
		if ( $this->add_description_additions() ) {
			$description_blogname_additions = $this->get_option( 'description_blogname' );

			$nojs_additions = $description_blogname_additions ? $page_title . " $on " . $blogname : $page_title;
			$nojs_additions = $nojs_additions . " $sep ";
		}

		$example_nojs = $nojs_additions . $excerpt;

		?><h4><?php printf( esc_html__( 'Automated Description Settings', 'autodescription' ) ); ?></h4><?php
		$this->description( __( 'The meta description can be used to determine the text used under the title on Search Engine results pages.', 'autodescription' ) );

		?>
		<h4><?php esc_html_e( 'Example Automated Description Output', 'autodescription' ); ?></h4>
		<p class="hide-if-no-js"><?php echo $this->code_wrap_noesc( $example ); ?></p>
		<p class="hide-if-js"><?php echo $this->code_wrap( $example_nojs ); ?></p>

		<hr>
		<?php

		/**
		 * Parse tabs content.
		 *
		 * @since 2.6.0
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
				'name'     => esc_html__( 'General', 'autodescription' ),
				'callback' => array( $this, 'description_metabox_general_tab' ),
				'dashicon' => 'admin-generic',
			),
			'additions' => array(
				'name'     => esc_html__( 'Additions', 'autodescription' ),
				'callback' => array( $this, 'description_metabox_additions_tab' ),
				'dashicon' => 'plus',
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
		break;

	case 'the_seo_framework_description_metabox_general' :

		//* Let's use the same separators as for the title.
		$description_separator = $this->get_separator_list();
		$sep_option = $this->get_option( 'description_separator' );
		$sep_option = $sep_option ? $sep_option : 'pipe';

		?>
		<fieldset>
			<legend>
				<h4><?php esc_html_e( 'Description Excerpt Separator', 'autodescription' ); ?></h4>
				<?php $this->description( __( 'If the Automated Description consists of two parts (title and excerpt), then the separator will go in-between them.', 'autodescription' ) ); ?>
			</legend>
			<p id="tsf-description-separator" class="tsf-fields">
			<?php foreach ( $description_separator as $name => $html ) { ?>
				<input type="radio" name="<?php $this->field_name( 'description_separator' ); ?>" id="<?php $this->field_id( 'description_separator' . $name ); ?>" value="<?php echo esc_attr( $name ); ?>" <?php checked( $sep_option, $name ); ?> />
				<label for="<?php $this->field_id( 'description_separator' . $name ); ?>">
					<?php echo $html; ?>
				</label>
			<?php } ?>
			</p>
		</fieldset>
		<?php
		break;

	case 'the_seo_framework_description_metabox_additions' :

		$language = $this->google_language();
		$google_explanation = esc_url( 'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#1' );

		?>
		<h4><?php esc_html_e( 'Description Additions Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'To create a more organic description, a small introduction can be added before the description.', 'autodescription' ) );
		$this->description( __( 'The introduction consists of the title and optionally the blogname.', 'autodescription' ) );
		?>

		<hr>

		<h4><?php esc_html_e( 'Add descriptive Additions to Description', 'autodescription' ); ?></h4>
		<p id="tsf-description-additions-toggle">
			<label for="<?php $this->field_id( 'description_additions' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'description_additions' ); ?>" id="<?php $this->field_id( 'description_additions' ); ?>" <?php $this->is_conditional_checked( 'description_additions' ); ?> value="1" <?php checked( $this->get_field_value( 'description_additions' ) ); ?> />
				<?php
				esc_html_e( 'Add Additions to automated description?', 'autodescription' );
				echo ' ';
				$this->make_info(
					__( 'This creates better automated meta descriptions.', 'autodescription' ),
					$google_explanation
				);
				?>
			</label>
		</p>

		<h4><?php esc_html_e( 'Add Blogname to Additions', 'autodescription' ); ?></h4>
		<p id="tsf-description-onblogname-toggle">
			<label for="<?php $this->field_id( 'description_blogname' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'description_blogname' ); ?>" id="<?php $this->field_id( 'description_blogname' ); ?>" <?php $this->is_conditional_checked( 'description_blogname' ); ?> value="1" <?php checked( $this->get_field_value( 'description_blogname' ) ); ?> />
				<?php esc_html_e( 'Add the blog name to the automated description?', 'autodescription' ); ?>
			</label>
		</p>
		<?php
		break;

	default :
		break;
endswitch;
