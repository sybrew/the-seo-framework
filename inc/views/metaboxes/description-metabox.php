<?php
/**
 * @package The_SEO_Framework\Views\Admin
 * @subpackage The_SEO_Framework\Views\Metaboxes
 */

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

//* Fetch the required instance within this file.
$instance = $this->get_view_instance( 'the_seo_framework_description_metabox', $instance );

switch ( $instance ) :
	case 'the_seo_framework_description_metabox_main' :
		?>
		<h4><?php printf( esc_html__( 'Description Settings', 'autodescription' ) ); ?></h4>
		<?php
		$this->description( __( 'The meta description can be used to determine the text used under the title on search engine results pages.', 'autodescription' ) );

		/**
		 * Parse tabs content.
		 *
		 * @since 2.6.0
		 *
		 * @param array $default_tabs { 'id' = The identifier =>
		 *   array(
		 *      'name' => The name
		 *      'callback' => The callback function, use array for method calling
		 *      'dashicon' => Desired dashicon
		 *   )
		 * }
		 */
		$default_tabs = [
			'general' => [
				'name'     => esc_html__( 'General', 'autodescription' ),
				'callback' => [ $this, 'description_metabox_general_tab' ],
				'dashicon' => 'admin-generic',
			],
			'additions' => [
				'name'     => esc_html__( 'Additions', 'autodescription' ),
				'callback' => [ $this, 'description_metabox_additions_tab' ],
				'dashicon' => 'plus',
			],
		];

		/**
		 * @since 2.6.0
		 * @param array $defaults The default tabs.
		 * @param array $args     The args added on the callback.
		 */
		$defaults = (array) apply_filters( 'the_seo_framework_description_settings_tabs', $default_tabs, $args );

		$tabs = wp_parse_args( $args, $defaults );

		$this->nav_tab_wrapper( 'description', $tabs, '2.6.0' );
		break;

	case 'the_seo_framework_description_metabox_general' :
		?>
		<h4><?php esc_html_e( 'Automated Description Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description(
			__( 'A description can be automatically generated for every page.', 'autodescription' )
		);
		$this->description(
			__( 'Open Graph and Twitter Cards require descriptions. Therefore, it is best to leave this option enabled.', 'autodescription' )
		);

		//* Echo checkboxes.
		$this->wrap_fields(
			$this->make_checkbox(
				'auto_description',
				__( 'Automatically generate description?', 'autodescription' ),
				'',
				true
			),
			true
		);
		break;

	case 'the_seo_framework_description_metabox_additions' :
		$language = $this->google_language();

		$blogname = $this->escape_description( $this->get_blogname() );
		$sep = esc_html( $this->get_separator( 'description' ) );

		//* Generate example.
		$page_title = $this->escape_description( __( 'Example Title', 'autodescription' ) );
		$on = $this->escape_description( _x( 'on', 'Placement. e.g. Post Title "on" Blog Name', 'autodescription' ) );
		$excerpt = $this->escape_description( __( 'This is an example excerpt...', 'autodescription' ) );

		//* Put it together.
		$example = '<span id="tsf-description-additions-js">'
				 . $page_title
					 . '<span id="tsf-on-blogname-js">' . " $on " . $blogname . '</span>'
					 . '<span id="autodescription-descsep-js">' . " $sep " . '</span>'
				 . '</span>'
				 . $excerpt;

		$nojs_additions = '';
		//* Add or remove additions based on option.
		if ( $this->add_description_additions() ) {
			$description_blogname_additions = $this->get_option( 'description_blogname' );

			$nojs_additions = $description_blogname_additions ? $page_title . " $on " . $blogname : $page_title;
			$nojs_additions = $nojs_additions . " $sep ";
		}

		$example_nojs = $nojs_additions . $excerpt;

		?>
		<h4><?php esc_html_e( 'Automated Description Additions Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'To create a more organic description, additions can be added before the description.', 'autodescription' ) );
		$this->description( __( 'The additions consist of the page title, the blog name, and a separator.', 'autodescription' ) );
		?>

		<h4><?php esc_html_e( 'Example Automated Description Output', 'autodescription' ); ?></h4>
		<p class="hide-if-no-js"><?php echo $this->code_wrap_noesc( $example ); ?></p>
		<p class="hide-if-js"><?php echo $this->code_wrap( $example_nojs ); ?></p>

		<hr>

		<h4><?php esc_html_e( 'Enable Additions', 'autodescription' ); ?></h4>
		<div id="tsf-description-additions-toggle">
		<?php
			$info = $this->make_info(
				__( 'This creates better automated meta descriptions.', 'autodescription' ),
				'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#meta-descriptions',
				false
			);
			$this->wrap_fields(
				$this->make_checkbox(
					'description_additions',
					esc_html__( 'Add additions to automated description?', 'autodescription' ) . ' ' . $info,
					'',
					false
				),
				true
			);
		?>
		</div>

		<h4><?php esc_html_e( 'Add Blogname to Additions', 'autodescription' ); ?></h4>
		<div id="tsf-description-onblogname-toggle">
		<?php
			$this->wrap_fields(
				$this->make_checkbox(
					'description_blogname',
					esc_html__( 'Add the blog name to the additions?', 'autodescription' ),
					'',
					false
				),
				true
			);
		?>
		</div>
		<?php

		//* Let's use the same separators as for the title.
		$description_separator = $this->get_separator_list();
		$sep_option = $this->get_option( 'description_separator' );
		$sep_option = $sep_option ? $sep_option : 'pipe';

		// FIXME: what a mess...
		?>
		<fieldset>
			<legend>
				<h4><?php esc_html_e( 'Description Separator', 'autodescription' ); ?></h4>
				<?php $this->description( __( 'When additions are enabled, this separator will go in-between the additions and the excerpt.', 'autodescription' ) ); ?>
			</legend>
			<p id="tsf-description-separator" class="tsf-fields">
			<?php foreach ( $description_separator as $name => $html ) { ?>
				<input type="radio" name="<?php $this->field_name( 'description_separator' ); ?>" id="<?php $this->field_id( 'description_separator' . $name ); ?>" value="<?php echo esc_attr( $name ); ?>" <?php checked( $sep_option, $name ); ?> />
				<label for="<?php $this->field_id( 'description_separator' . $name ); ?>">
					<?php
					echo $html; // xss ok
					?>
				</label>
			<?php } ?>
			</p>
		</fieldset>

		<?php
		break;

	default:
		break;
endswitch;
