<?php

defined( 'ABSPATH' ) or die;

//* Fetch the required instance within this file.
$instance = $this->get_view_instance( 'the_seo_framework_knowledge_metabox', $instance );

switch ( $instance ) :
	case 'the_seo_framework_knowledge_metabox_main' :

		/**
		 * Parse tabs content.
		 *
		 * @since 2.2.8
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
		 * @since 2.2.8
		 * Used to extend Knowledge Graph tabs
		 */
		$defaults = (array) apply_filters( 'the_seo_framework_knowledgegraph_settings_tabs', $default_tabs, $args );

		$tabs = wp_parse_args( $args, $defaults );

		$this->nav_tab_wrapper( 'knowledge', $tabs, '2.2.8' );
		break;

	case 'the_seo_framework_knowledge_metabox_general' :

		?><h4><?php esc_html_e( 'Knowledge Graph Settings', 'autodescription' ); ?></h4><?php
		$this->description( __( "The Knowledge Graph lets Google and other Search Engines know where to find you or your organization and its relevant content.", 'autodescription' ) );
		$this->description( __( "Google is becoming more of an 'Answer Engine' than a 'Search Engine'. Setting up these options could have a positive impact on the SEO value of your website.", 'autodescription' ) );

		//* Echo checkbox.
		$this->wrap_fields(
			$this->make_checkbox(
				'knowledge_output',
				__( 'Output Knowledge tags?', 'autodescription' ),
				'',
				true
			), true
		);

		if ( $this->wp_version( '4.2.999', '>=' ) ) :
		?>
			<hr>

			<h4><?php esc_html_e( 'Website logo', 'autodescription' ); ?></h4>
			<?php
			//* Echo checkbox.
			$this->wrap_fields(
				$this->make_checkbox(
					'knowledge_logo',
					__( 'Use the Favicon from Customizer as the Organization Logo?', 'autodescription' ),
					__( 'This option only has an effect when this site represents an Organization. If left disabled, Search Engines will look elsewhere for a logo, if it exists and is assigned as a logo.', 'autodescription' ),
					true
				), true
			);
		endif;
		break;

	case 'the_seo_framework_knowledge_metabox_about' :

		$blogname = $this->get_blogname();

		?><h4><?php esc_html_e( 'About this website', 'autodescription' ); ?></h4><?php
		$this->description( __( 'Who or what is your website about?', 'autodescription' ) );

		?>
		<hr>

		<p>
			<label for="<?php $this->field_id( 'knowledge_type' ); ?>"><?php echo esc_html_x( 'This website represents:', '...Organization or Person.', 'autodescription' ); ?></label>
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
				<strong><?php esc_html_e( 'The organization or personal name', 'autodescription' ); ?></strong>
			</label>
		</p>
		<p>
			<input type="text" name="<?php $this->field_name( 'knowledge_name' ); ?>" class="large-text" id="<?php $this->field_id( 'knowledge_name' ); ?>" placeholder="<?php echo esc_attr( $blogname ) ?>" value="<?php echo esc_attr( $this->get_field_value( 'knowledge_name' ) ); ?>" />
		</p>
		<?php
		break;

	case 'the_seo_framework_knowledge_metabox_social' :

		?><h4><?php esc_html_e( 'Social Pages connected to this website', 'autodescription' ); ?></h4><?php
		$this->description( __( "Don't have a page at a site or is the profile only privately accessible? Leave that field empty. Unsure? Fill it in anyway.", 'autodescription' ) );
		$this->description( __( 'Add the link that leads directly to the social page of this website.', 'autodescription' ) );

		?><hr><?php

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
				'desc' 			=> 'Google+ ' . $profile18n,
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
					<strong><?php echo esc_html( $value['desc'] ); ?></strong>
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
		break;

	default :
		break;
endswitch;
