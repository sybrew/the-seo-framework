<?php

defined( 'ABSPATH' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

//* Fetch the required instance within this file.
$instance = $this->get_view_instance( 'the_seo_framework_schema_metabox', $instance );

switch ( $instance ) :
	case 'the_seo_framework_schema_metabox_main' :

		?>
		<h4><?php esc_html_e( 'Schema.org Output Settings', 'autodescription' ); ?></h4>
		<?php

		if ( $this->has_json_ld_plugin() ) :
			$this->description( __( 'Another Schema.org plugin has been detected.', 'autodescription' ) );
		else :
			$this->description( __( 'The Schema.org markup is a standard way of annotating structured data for Search Engines. This markup is represented within hidden scripts throughout the website.', 'autodescription' ) );
			$this->description( __( 'When your web pages include structured data markup, Search Engines can use that data to index your content better, present it more prominently in Search Results, and use it in several different applications.', 'autodescription' ) );
			$this->description( __( 'This is also known as the "Knowledge Graph" and "Structured Data", which is under heavy active development by several Search Engines. Therefore, the usage of the outputted markup is not guaranteed.', 'autodescription' ) );

			/**
			 * Parse tabs content.
			 *
			 * @since 2.8.0
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
					'callback' => array( $this, 'schema_metabox_general_tab' ),
					'dashicon' => 'admin-generic',
				),
				'structure' => array(
					'name'     => __( 'Structure', 'autodescription' ),
					'callback' => array( $this, 'schema_metabox_structure_tab' ),
					'dashicon' => 'admin-multisite',
				),
				'presence' => array(
					'name'     => __( 'Presence', 'autodescription' ),
					'callback' => array( $this, 'schema_metabox_presence_tab' ),
					'dashicon' => 'networking',
				),
			);

			/**
			 * Applies filter 'the_seo_framework_schema_settings_tabs' : Array
			 * @since 2.8.0
			 * Used to extend Schema settings tabs
			 */
			$defaults = (array) apply_filters( 'the_seo_framework_schema_settings_tabs', $default_tabs, $args );

			$tabs = wp_parse_args( $args, $defaults );

			$this->nav_tab_wrapper( 'schema', $tabs, '2.8.0' );
		endif;
		break;

	case 'the_seo_framework_schema_metabox_general' :

		?>
		<h4><?php esc_html_e( 'About this website', 'autodescription' ); ?></h4>

		<p>
			<label for="<?php $this->field_id( 'knowledge_type' ); ?>"><?php echo esc_html_x( 'This website represents:', '...Organization or Person.', 'autodescription' ); ?></label>
			<select name="<?php $this->field_name( 'knowledge_type' ); ?>" id="<?php $this->field_id( 'knowledge_type' ); ?>">
				<?php
				$knowledge_type = (array) apply_filters(
					'the_seo_framework_knowledge_types',
					array(
						'organization' => __( 'An Organization', 'autodescription' ),
						'person'       => __( 'A Person', 'autodescription' ),
					)
				);
				foreach ( $knowledge_type as $value => $name ) {
					echo '<option value="' . esc_attr( $value ) . '"' . selected( $this->get_field_value( 'knowledge_type' ), esc_attr( $value ), false ) . '>' . esc_html( $name ) . '</option>' . "\n";
				}
				?>
			</select>
		</p>

		<p>
			<label for="<?php $this->field_id( 'knowledge_name' ); ?>">
				<strong><?php esc_html_e( 'The organization or personal name', 'autodescription' ); ?></strong>
			</label>
		</p>
		<p>
			<input type="text" name="<?php $this->field_name( 'knowledge_name' ); ?>" class="large-text" id="<?php $this->field_id( 'knowledge_name' ); ?>" placeholder="<?php echo esc_attr( $this->get_blogname() ) ?>" value="<?php echo esc_attr( $this->get_field_value( 'knowledge_name' ) ); ?>" />
		</p>
		<?php
		break;

	case 'the_seo_framework_schema_metabox_structure' :

		?><h4><?php esc_html_e( 'Site Structure Options', 'autodescription' ); ?></h4><?php
		$this->description( __( 'The site structure Schema.org output allows Search Engines to gain knowledge on how your website is built.', 'autodescription' ) );
		$this->description( __( "For example, Search Engines display your pages' URLs when listed in the Search Results. These options allow you to enhance those URLs output.", 'autodescription' ) );

		?>
		<hr>

		<h4><?php esc_html_e( 'Breadcrumbs', 'autodescription' ); ?></h4><?php
		$this->description( __( "Breadcrumb trails indicate the page's position in the site hierarchy. Using the following option will show the hierarchy within the Search Results when available.", 'autodescription' ) );

		$info = $this->make_info( __( 'About Breadcrumbs', 'autodescription' ), 'https://developers.google.com/search/docs/data-types/breadcrumbs', false );
		$this->wrap_fields( $this->make_checkbox(
			'ld_json_breadcrumbs',
			esc_html__( 'Enable Breadcrumbs?', 'autodescription' ) . ' ' . $info,
			esc_html__( 'Multiple trails can be outputted. The longest trail is prioritized.', 'autodescription' ),
			false
		), true );

		?>
		<hr>

		<h4><?php esc_html_e( 'Site Name', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( "When using breadcrumbs, the first entry is by default your website's address. Using the following option will convert it to the Site Name.", 'autodescription' ) );

		$info = $this->make_info(
			__( 'Include your Site Name in Search Results', 'autodescription' ),
			'https://developers.google.com/search/docs/data-types/sitename',
			false
		);
		$this->wrap_fields( $this->make_checkbox(
			'ld_json_sitename',
			esc_html__( 'Convert URL to Site Name?', 'autodescription' ) . ' ' . $info,
			sprintf( esc_html__( 'The Site Name is: %s', 'autodescription' ), $this->code_wrap( $this->get_blogname() ) ),
			false
		), true );

		?>
		<hr>
		<?php

		/* translators: https://developers.google.com/search/docs/data-types/sitelinks-searchbox */
		?><h4><?php echo esc_html( _x( 'Sitelinks Searchbox', 'Product name', 'autodescription' ) ); ?></h4><?php
		$this->description( __( 'When Search users search for your brand name, the following option allows them to search through this website directly from the Search Results.', 'autodescription' ) );

		$info = $this->make_info( _x( 'Sitelinks Searchbox', 'Product name', 'autodescription' ), 'https://developers.google.com/search/docs/data-types/sitelinks-searchbox', false );
		$this->wrap_fields( $this->make_checkbox(
			'ld_json_searchbox',
			esc_html_x( 'Enable Sitelinks Searchbox?', 'Product name', 'autodescription' ) . ' ' . $info,
			'',
			false
		), true );
		break;

	case 'the_seo_framework_schema_metabox_presence' :

		?><h4><?php esc_html_e( 'Authorized Presence Options', 'autodescription' ); ?></h4><?php
		$this->description( __( 'The authorized presence Schema.org output helps Search Engine users find ways to interact with this website.', 'autodescription' ) );

		$info = $this->make_info( __( 'About Authorized Presence', 'autodescription' ), 'https://developers.google.com/search/docs/guides/enhance-site#add-your-sites-name-logo-and-social-links', false );
		//* Echo checkbox.
		$this->wrap_fields( $this->make_checkbox(
			'knowledge_output',
			esc_html__( 'Output Authorized Presence?', 'autodescription' ) . ' ' . $info,
			'',
			false
		), true );

		?>
		<hr>

		<h4><?php esc_html_e( 'Website logo', 'autodescription' ); ?></h4>
		<?php
		//* @TODO @priority OMGWTFBBQ 2.8.0 this logo MUST prefer "Site Icon". State that.
		// if ( $this->theme_supports_site_icon() )
		// $this->description( __( 'If your theme supports', 'autodescription' ) );
		$info = $this->make_info( __( 'About Organization Logo', 'autodescription' ), 'https://developers.google.com/search/docs/data-types/logo', false );
		$this->wrap_fields( $this->make_checkbox(
			'knowledge_logo',
			esc_html__( 'Use the Favicon from Customizer as the Organization Logo?', 'autodescription' ) . ' ' . $info,
			esc_html__( 'This option only has an effect when this site represents an Organization. If left disabled, Search Engines will look elsewhere for a logo, if it exists and is assigned as a logo.', 'autodescription' ),
			false
		), true );

		?>
		<hr>

		<h4><?php esc_html_e( 'Social Pages connected to this website', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( "Don't have a page at a site or is the profile only privately accessible? Leave that field empty. Unsure? Fill it in anyway.", 'autodescription' ) );
		$this->description( __( 'Add the link that leads directly to the social page of this website.', 'autodescription' ) );

		$connectedi18n = _x( 'RelatedProfile', 'No spaces. E.g. https://facebook.com/RelatedProfile', 'autodescription' );
		$profile18n = _x( 'Profile', 'Social Profile', 'autodescription' );

		/**
		 * @todo maybe genericons?
		 */
		$socialsites = array(
			'facebook' => array(
				'option'      => 'knowledge_facebook',
				'dashicon'    => 'dashicons-facebook',
				'desc'        => 'Facebook ' . __( 'Page', 'autodescription' ),
				'placeholder' => 'https://www.facebook.com/' . $connectedi18n,
				'examplelink' => 'https://www.facebook.com/me',
			),
			'twitter' => array(
				'option'      => 'knowledge_twitter',
				'dashicon'    => 'dashicons-twitter',
				'desc'        => 'Twitter ' . $profile18n,
				'placeholder' => 'https://twitter.com/' . $connectedi18n,
				'examplelink' => 'https://twitter.com/home', // No example link available.
			),
			'gplus' => array(
				'option'      => 'knowledge_gplus',
				'dashicon'    => 'dashicons-googleplus',
				'desc'        => 'Google+ ' . $profile18n,
				'placeholder' => 'https://plus.google.com/' . $connectedi18n,
				'examplelink' => 'https://plus.google.com/me',
			),
			'instagram' => array(
				'option'      => 'knowledge_instagram',
				'dashicon'    => 'genericon-instagram',
				'desc'        => 'Instagram ' . $profile18n,
				'placeholder' => 'https://instagram.com/' . $connectedi18n,
				'examplelink' => 'https://instagram.com/', // No example link available.
			),
			'youtube' => array(
				'option'      => 'knowledge_youtube',
				'dashicon'    => 'genericon-youtube',
				'desc'        => 'Youtube ' . $profile18n,
				'placeholder' => 'https://www.youtube.com/channel/' . $connectedi18n,
				'examplelink' => 'https://www.youtube.com/user/%2f', // Yes a double slash.
			),
			'linkedin' => array(
				'option'      => 'knowledge_linkedin',
				'dashicon'    => 'genericon-linkedin-alt',
				'desc'        => 'LinkedIn ' . $profile18n,
				'placeholder' => 'https://www.linkedin.com/in/' . $connectedi18n,
				'examplelink' => 'https://www.linkedin.com/profile/view',
			),
			'pinterest' => array(
				'option'      => 'knowledge_pinterest',
				'dashicon'    => 'genericon-pinterest-alt',
				'desc'        => 'Pinterest ' . $profile18n,
				'placeholder' => 'https://www.pinterest.com/' . $connectedi18n . '/',
				'examplelink' => 'https://www.pinterest.com/me/',
			),
			'soundcloud' => array(
				'option'      => 'knowledge_soundcloud',
				'dashicon'    => 'genericon-cloud', // I know, it's not the real one. D:
				'desc'        => 'SoundCloud ' . $profile18n,
				'placeholder' => 'https://soundcloud.com/' . $connectedi18n,
				'examplelink' => 'https://soundcloud.com/you',
			),
			'tumblr' => array(
				'option'      => 'knowledge_tumblr',
				'dashicon'    => 'genericon-tumblr',
				'desc'        => 'Tumblr ' . __( 'Blog', 'autodescription' ),
				'placeholder' => 'https://www.tumblr.com/blog/' . $connectedi18n,
				'examplelink' => 'https://www.tumblr.com/dashboard',  // No example link available.
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
