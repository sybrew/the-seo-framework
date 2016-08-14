<?php

defined( 'ABSPATH' ) or die;

//* Fetch the required instance within this file.
$instance = $this->get_view_instance( 'the_seo_framework_homepage_metabox', $instance );

switch ( $instance ) :
	case 'the_seo_framework_homepage_metabox_main' :

		$this->description( __( 'These settings will take precedence over the settings set within the Home Page edit screen, if any.', 'autodescription' ) );

		?><hr><?php

		/**
		 * Parse tabs content.
		 *
		 * @since 2.6.0
		 *
		 * @param array $default_tabs { 'id' = The identifier =>
		 *			array(
		 *				'name' 		=> The name
		 *				'callback' 	=> The callback function, use array for method calling (accepts $this, but isn't used here for optimization purposes)
		 *				'dashicon'	=> Desired dashicon
		 *			)
		 * }
		 */
		$default_tabs = array(
			'general' => array(
				'name' 		=> __( 'General', 'autodescription' ),
				'callback'	=> array( $this, 'homepage_metabox_general_tab' ),
				'dashicon'	=> 'admin-generic',
			),
			'additions' => array(
				'name'		=> __( 'Additions', 'autodescription' ),
				'callback'	=> array( $this, 'homepage_metabox_additions_tab' ),
				'dashicon'	=> 'plus',
			),
			'robots' => array(
				'name'		=> __( 'Robots', 'autodescription' ),
				'callback'	=> array( $this, 'homepage_metabox_robots_tab' ),
				'dashicon'	=> 'visibility',
			),
		);

		/**
		 * Applies filters the_seo_framework_homepage_settings_tabs : array see $default_tabs
		 * @since 2.6.0
		 * Used to extend HomePage tabs.
		 */
		$defaults = (array) apply_filters( 'the_seo_framework_homepage_settings_tabs', $default_tabs, $args );

		$tabs = wp_parse_args( $args, $defaults );

		$this->nav_tab_wrapper( 'homepage', $tabs, '2.6.0' );
		break;

	case 'the_seo_framework_homepage_metabox_general' :

		$language = $this->google_language();

		$page_on_front = $this->has_page_on_front();
		$home_description_frompost = false;

		$description_from_post_message = '';
		$title_from_post_message  = '';

		$title_i18n = esc_html__( 'Title', 'autodescription' );
		$description_i18n = esc_html__( 'Description', 'autodescription' );
		$home_page_i18n = esc_html__( 'Home Page', 'autodescription' );

		$home_id = $this->get_the_front_page_ID();
		$home_title = $this->escape_title( $this->get_option( 'homepage_title' ) );
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
		if ( $this->home_page_add_title_tagline() )
			$home_title_placeholder = $this->process_title_additions( $home_title_args['blogname'], $home_title_args['title'], $home_title_args['seplocation'] );
		else
			$home_title_placeholder = $home_title_args['title'];

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
		 * Create a placeholder.
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
			$page_seo_settings_i18n = __( 'Page SEO Settings', 'autodescription' );
			/* translators: 1: Option, 2: Page SEO Settings, 3: Home Page */
			$description_from_post_message = sprintf( __( 'Note: The %1$s is fetched from the %2$s on the %3$s.', 'autodescription' ), $description_i18n, $page_seo_settings_i18n, $home_page_i18n );
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
			<label for="<?php $this->field_id( 'homepage_title_tagline' ); ?>" class="tsf-toblock">
				<strong><?php printf( esc_html__( 'Custom %s Title Tagline', 'autodescription' ), $home_page_i18n ); ?></strong>
			</label>
		</p>
		<p>
			<input type="text" name="<?php $this->field_name( 'homepage_title_tagline' ); ?>" class="large-text" id="<?php $this->field_id( 'homepage_title_tagline' ); ?>" placeholder="<?php echo $home_tagline_placeholder ?>" value="<?php echo esc_attr( $home_tagline_value ); ?>" />
		</p>

		<hr>

		<p>
			<label for="<?php $this->field_id( 'homepage_title' ); ?>" class="tsf-toblock">
				<strong><?php printf( esc_html__( 'Custom %s Title', 'autodescription' ), $home_page_i18n ); ?></strong>
				<a href="<?php echo esc_url( 'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#3' ); ?>" target="_blank" title="<?php esc_attr_e( 'Recommended Length: 50 to 55 characters', 'autodescription' ) ?>">[?]</a>
				<span class="description tsf-counter">
					<?php printf( esc_html__( 'Characters Used: %s', 'autodescription' ), '<span id="' . $this->field_id( 'homepage_title', false ) . '_chars">'. mb_strlen( $tit_len ) .'</span>' ); ?>
					<span class="hide-if-no-js tsf-ajax"></span>
				</span>
			</label>
		</p>
		<p id="tsf-title-wrap">
			<input type="text" name="<?php $this->field_name( 'homepage_title' ); ?>" class="large-text" id="<?php $this->field_id( 'homepage_title' ); ?>" placeholder="<?php echo esc_attr( $home_title_placeholder ); ?>" value="<?php echo esc_attr( $home_title ); ?>" />
			<span id="tsf-title-offset" class="hide-if-no-js"></span><span id="tsf-title-placeholder" class="hide-if-no-js"></span>
		</p>
		<?php
		if ( $title_from_post_message ) {
			$this->description( $title_from_post_message );
		}
		?>
		<hr>

		<p>
			<label for="<?php $this->field_id( 'homepage_description' ); ?>" class="tsf-toblock">
				<strong><?php printf( esc_html__( 'Custom %s Description', 'autodescription' ), $home_page_i18n ); ?></strong>
				<a href="<?php echo esc_url( 'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#1' ); ?>" target="_blank" title="<?php _e( 'Recommended Length: 145 to 155 characters', 'autodescription' ) ?>">[?]</a>
				<span class="description tsf-counter">
					<?php printf( __( 'Characters Used: %s', 'autodescription' ), '<span id="' . $this->field_id( 'homepage_description', false ) . '_chars">'. mb_strlen( $desc_len ) .'</span>' ); ?>
					<span class="hide-if-no-js tsf-ajax"></span>
				</span>
			</label>
		</p>
		<p>
			<textarea name="<?php $this->field_name( 'homepage_description' ); ?>" class="large-text" id="<?php $this->field_id( 'homepage_description' ); ?>" rows="3" cols="70"  placeholder="<?php echo $description_placeholder ?>"><?php echo esc_textarea( $home_description ); ?></textarea>
		</p>
		<?php
		$this->description( __( 'The meta description can be used to determine the text used under the title on Search Engine results pages.', 'autodescription' ) );

		if ( $description_from_post_message ) {
			echo '<p class="description">' . esc_html( $description_from_post_message ) . '</p>';
		}
		break;

	case 'the_seo_framework_homepage_metabox_additions' :

		//* Fetches escaped title parts.
		$title_args = $this->generate_home_title();
		$title = $title_args['title'];
		$blogname = $title_args['blogname'];
		$sep = $this->get_separator( 'title' );

		$example_left = '<em><span class="custom-title-js">' . $title . '</span><span class="tsf-custom-blogname-js"><span class="autodescription-sep-js"> ' . $sep . ' </span><span class="tsf-custom-tagline-js">' . $blogname . '</span></span></span></em>';
		$example_right = '<em><span class="tsf-custom-blogname-js"><span class="tsf-custom-tagline-js">' . $blogname . '</span><span class="autodescription-sep-js"> ' . $sep . ' </span></span><span class="custom-title-js">' . $title . '</span></em>';

		$home_page_i18n = esc_html__( 'Home Page', 'autodescription' );

		?>
		<fieldset>
			<legend><h4><?php esc_html_e( 'Document Title Additions Location', 'autodescription' ); ?></h4></legend>
			<?php $this->description( __( 'Determines which side the added title text will go on.', 'autodescription' ) ); ?>

			<p id="tsf-home-title-location" class="tsf-fields">
				<span class="tsf-toblock">
					<input type="radio" name="<?php $this->field_name( 'home_title_location' ); ?>" id="<?php $this->field_id( 'home_title_location_left' ); ?>" value="left" <?php checked( $this->get_field_value( 'home_title_location' ), 'left' ); ?> />
					<label for="<?php $this->field_id( 'home_title_location_left' ); ?>">
						<span><?php esc_html_e( 'Left:', 'autodescription' ); ?></span>
						<?php echo ( $example_left ) ? $this->code_wrap_noesc( $example_left ) : ''; ?>
					</label>
				</span>
				<span class="tsf-toblock">
					<input type="radio" name="<?php $this->field_name( 'home_title_location' ); ?>" id="<?php $this->field_id( 'home_title_location_right' ); ?>" value="right" <?php checked( $this->get_field_value( 'home_title_location' ), 'right' ); ?> />
					<label for="<?php $this->field_id( 'home_title_location_right' ); ?>">
						<span><?php esc_html_e( 'Right:', 'autodescription' ); ?></span>
						<?php echo $example_right ? $this->code_wrap_noesc( $example_right ) : ''; ?>
					</label>
				</span>
			</p>
		</fieldset>

		<hr>
		<h4><?php printf( esc_html__( '%s Tagline', 'autodescription' ), $home_page_i18n ); ?></h4>
		<p id="tsf-title-tagline-toggle">
			<label for="<?php $this->field_id( 'homepage_tagline' ); ?>" class="tsf-toblock">
				<input type="checkbox" name="<?php $this->field_name( 'homepage_tagline' ); ?>" id="<?php $this->field_id( 'homepage_tagline' ); ?>" <?php $this->is_conditional_checked( 'homepage_tagline' ); ?> value="1" <?php checked( $this->get_field_value( 'homepage_tagline' ) ); ?> />
				<?php printf( esc_html__( 'Add site description (tagline) to the Title on the %s?', 'autodescription' ), $home_page_i18n ); ?>
			</label>
		</p>
		<?php
		break;

	case 'the_seo_framework_homepage_metabox_robots' :

		$language = $this->google_language();
		$home_page_i18n = esc_html__( 'Home Page', 'autodescription' );

		//* Get home page ID. If blog on front, it's 0.
		$home_id = $this->get_the_front_page_ID();

		$noindex_post = $this->get_custom_field( '_genesis_noindex', $home_id );
		$nofollow_post = $this->get_custom_field( '_genesis_nofollow', $home_id );
		$noarchive_post = $this->get_custom_field( '_genesis_noarchive', $home_id );

		$checked_home = '';
		/**
		 * Shows user that the setting is checked on the home page.
		 * Adds starting - with space to maintain readability.
		 *
		 * @since 2.2.4
		 */
		if ( $noindex_post || $nofollow_post || $noarchive_post ) {
			$checked_home = ' - <a href="' . esc_url( admin_url( 'post.php?post=' . $home_id . '&action=edit#tsf-inpost-box' ) ) . '" target="_blank" class="attention" title="' . esc_attr__( 'View Home Page Settings', 'autodescription' ) . '" >' . esc_html__( 'Checked in Page', 'autodescription' ) . '</a>';
		}

		?><h4><?php esc_html_e( 'Home Page Robots Meta Settings', 'autodescription' ); ?></h4><?php

		$noindex_note = $noindex_post ? $checked_home : '';
		$nofollow_note = $nofollow_post ? $checked_home : '';
		$noarchive_note = $noarchive_post ? $checked_home : '';

		//* Index label.
		/* translators: 1: Option, 2: Location */
		$i_label = sprintf( esc_html__( 'Apply %1$s to the %2$s?', 'autodescription' ), $this->code_wrap( 'noindex' ), $home_page_i18n );
		$i_label .= ' ';
		$i_label .= $this->make_info(
			__( 'Tell Search Engines not to show this page in their search results', 'autodescription' ),
			'https://support.google.com/webmasters/answer/93710?hl=' . $language,
			false
		) . $noindex_note;

		//* Follow label.
		/* translators: 1: Option, 2: Location */
		$f_label = sprintf( esc_html__( 'Apply %1$s to the %2$s?', 'autodescription' ), $this->code_wrap( 'nofollow' ), $home_page_i18n );
		$f_label .= ' ';
		$f_label .= $this->make_info(
			__( 'Tell Search Engines not to follow links on this page', 'autodescription' ),
			'https://support.google.com/webmasters/answer/96569?hl=' . $language,
			false
		) . $nofollow_note;

		//* Archive label.
		/* translators: 1: Option, 2: Location */
		$a_label = sprintf( esc_html__( 'Apply %1$s to the %2$s?', 'autodescription' ), $this->code_wrap( 'noarchive' ), $home_page_i18n );
		$a_label .= ' ';
		$a_label .= $this->make_info(
			__( 'Tell Search Engines not to save a cached copy of this page', 'autodescription' ),
			'https://support.google.com/webmasters/answer/79812?hl=' . $language,
			false
		) . $noarchive_note;

		//* Echo checkboxes.
		$this->wrap_fields(
			array(
				$this->make_checkbox(
					'homepage_noindex',
					$i_label,
					'',
					false
				),
				$this->make_checkbox(
					'homepage_nofollow',
					$f_label,
					'',
					false
				),
				$this->make_checkbox(
					'homepage_noarchive',
					$a_label,
					'',
					false
				),
			),
			true
		);

		// Add notice if any options are checked on the post.
		if ( $noindex_post || $nofollow_post || $noarchive_post ) {
			$this->description( __( 'Note: If any of these options are unchecked, but are checked on the Home Page, they will be outputted regardless.', 'autodescription' ) );
		}
		?>

		<hr>

		<h4><?php esc_html_e( 'Home Page Pagination Robots Settings', 'autodescription' ); ?></h4>
		<?php $this->description( __( "If your Home Page is paginated and outputs content that's also found elsewhere on the website, enabling this option might prevent duplicate content.", 'autodescription' ) ); ?>

		<?php
		//* Echo checkbox.
		$this->wrap_fields(
			$this->make_checkbox(
				'home_paged_noindex',
				/* translators: 1: Option, 2: Location */
				sprintf( esc_html__( 'Apply %1$s to every second or later page on the %2$s?', 'autodescription' ), $this->code_wrap( 'noindex' ), $home_page_i18n ),
				'',
				false
			),
			true
		);
		break;

	default :
		break;
endswitch;
