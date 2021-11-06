<?php
/**
 * @package The_SEO_Framework\Views\Admin\Metaboxes
 * @subpackage The_SEO_Framework\Admin\Settings
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

use The_SEO_Framework\Bridges\SeoSettings,
	The_SEO_Framework\Interpreters\HTML,
	The_SEO_Framework\Interpreters\Form,
	The_SEO_Framework\Interpreters\Settings_Input as Input;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and tsf()->_verify_include_secret( $_secret ) or die;

switch ( $this->get_view_instance( 'schema', $instance ) ) :
	case 'schema_main':
		HTML::header_title( __( 'Schema.org Output Settings', 'autodescription' ) );

		if ( $this->has_json_ld_plugin() )
			HTML::attention_description( __( 'Another Schema.org plugin has been detected. These markup settings might conflict.', 'autodescription' ) );

		HTML::description( __( 'The Schema.org markup is a standard way of annotating structured data for search engines. This markup is represented within hidden scripts throughout the website.', 'autodescription' ) );
		HTML::description( __( 'When your web pages include structured data markup, search engines can use that data to index your content better, present it more prominently in search results, and use it in several different applications.', 'autodescription' ) );
		HTML::description( __( 'This is also known as the "Knowledge Graph" and "Structured Data", which is under heavy active development by several search engines. Therefore, the usage of the outputted markup is not guaranteed.', 'autodescription' ) );

		$_settings_class = SeoSettings::class;

		$tabs = [
			'structure' => [
				'name'     => __( 'Structure', 'autodescription' ),
				'callback' => [ $_settings_class, '_schema_metabox_structure_tab' ],
				'dashicon' => 'admin-multisite',
			],
			'presence'  => [
				'name'     => __( 'Presence', 'autodescription' ),
				'callback' => [ $_settings_class, '_schema_metabox_presence_tab' ],
				'dashicon' => 'networking',
			],
		];

		SeoSettings::_nav_tab_wrapper(
			'schema',
			/**
			 * @since 2.8.0
			 * @param array $defaults The default tabs.
			 */
			(array) apply_filters( 'the_seo_framework_schema_settings_tabs', $tabs )
		);
		break;

	case 'schema_structure_tab':
		HTML::header_title( __( 'Site Structure Options', 'autodescription' ) );
		HTML::description( __( 'The site structure Schema.org output allows search engines to gain knowledge on how your website is built.', 'autodescription' ) );
		HTML::description( __( "For example, search engines display your pages' URLs when listed in the search results. These options allow you to enhance those URLs output.", 'autodescription' ) );
		?>
		<hr>
		<?php
		HTML::header_title( __( 'Breadcrumbs', 'autodescription' ) );
		HTML::description( __( "Breadcrumb trails indicate page positions in the site's hierarchy. Using the following option will show the hierarchy within the search results when available.", 'autodescription' ) );

		$info = HTML::make_info(
			__( 'Learn how this data is used.', 'autodescription' ),
			'https://developers.google.com/search/docs/data-types/breadcrumb',
			false
		);
		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'     => 'ld_json_breadcrumbs',
				'label'  => esc_html__( 'Enable Breadcrumbs?', 'autodescription' ) . " $info",
				'escape' => false,
			] ),
			true
		);

		?>
		<hr>
		<h4><?php echo esc_html( _x( 'Sitelinks Searchbox', 'Product name', 'autodescription' ) ); ?></h4>
		<?php
		HTML::description( __( 'When Search users search for your brand name, the following option allows them to search through this website directly from the search results.', 'autodescription' ) );

		$info = HTML::make_info(
			__( 'Learn how this data is used.', 'autodescription' ),
			'https://developers.google.com/search/docs/data-types/sitelinks-searchbox',
			false
		);
		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'     => 'ld_json_searchbox',
				'label'  => esc_html_x( 'Enable Sitelinks Searchbox?', 'Sitelinks Searchbox is a Product name', 'autodescription' ) . " $info",
				'escape' => false,
			] ),
			true
		);
		break;

	case 'schema_presence_tab':
		HTML::header_title( __( 'Authorized Presence Options', 'autodescription' ) );
		HTML::description( __( 'The authorized presence Schema.org output helps search engine users find ways to interact with this website.', 'autodescription' ) );

		$info = HTML::make_info(
			__( 'Learn how this data is used.', 'autodescription' ),
			'https://developers.google.com/search/docs/guides/enhance-site#add-your-sites-name-logo-and-social-links',
			false
		);
		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'     => 'knowledge_output',
				'label'  => esc_html__( 'Output Authorized Presence?', 'autodescription' ) . " $info",
				'escape' => false,
			] ),
			true
		);
		?>
		<hr>

		<?php HTML::header_title( __( 'About this website', 'autodescription' ) ); ?>
		<p>
			<label for="<?php Input::field_id( 'knowledge_type' ); ?>"><?php echo esc_html_x( 'This website represents:', '...Organization or Person.', 'autodescription' ); ?></label>
			<select name="<?php Input::field_name( 'knowledge_type' ); ?>" id="<?php Input::field_id( 'knowledge_type' ); ?>">
				<?php
				$knowledge_type = (array) apply_filters(
					'the_seo_framework_knowledge_types',
					[
						'organization' => __( 'An Organization', 'autodescription' ),
						'person'       => __( 'A Person', 'autodescription' ),
					]
				);
				foreach ( $knowledge_type as $value => $name ) {
					echo '<option value="' . esc_attr( $value ) . '"' . selected( $this->get_option( 'knowledge_type' ), esc_attr( $value ), false ) . '>' . esc_html( $name ) . '</option>' . "\n";
				}
				?>
			</select>
		</p>

		<p>
			<label for="<?php Input::field_id( 'knowledge_name' ); ?>">
				<strong><?php esc_html_e( 'The organization or personal name', 'autodescription' ); ?></strong>
			</label>
		</p>
		<p>
			<input type="text" name="<?php Input::field_name( 'knowledge_name' ); ?>" class="large-text" id="<?php Input::field_id( 'knowledge_name' ); ?>" placeholder="<?php echo esc_attr( $this->get_blogname() ); ?>" value="<?php echo esc_attr( $this->get_option( 'knowledge_name' ) ); ?>" autocomplete=off />
		</p>
		<hr>
		<?php
		HTML::header_title( __( 'Website logo', 'autodescription' ) );
		HTML::description( esc_html__( 'These options are used when this site represents an organization. When no logo is outputted, search engine will look elsewhere.', 'autodescription' ) );
		$info = HTML::make_info(
			__( 'Learn how this data is used.', 'autodescription' ),
			'https://developers.google.com/search/docs/data-types/logo',
			false
		);
		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'     => 'knowledge_logo',
				'label'  => esc_html__( 'Enable logo?', 'autodescription' ) . " $info",
				'escape' => false,
			] ),
		true );

		$logo_placeholder = $this->get_knowledge_logo( false );
		?>
		<p>
			<label for="knowledge_logo-url">
				<strong><?php esc_html_e( 'Logo URL', 'autodescription' ); ?></strong>
			</label>
		</p>
		<p class="hide-if-tsf-js attention"><?php esc_html_e( 'Setting a logo requires JavaScript.', 'autodescription' ); ?></p>
		<p>
			<input class="large-text" type="url" readonly="readonly" data-readonly="1" name="<?php Input::field_name( 'knowledge_logo_url' ); ?>" id="knowledge_logo-url" placeholder="<?php echo esc_url( $logo_placeholder ); ?>" value="<?php echo esc_url( $this->get_option( 'knowledge_logo_url' ) ); ?>" />
			<input type="hidden" name="<?php Input::field_name( 'knowledge_logo_id' ); ?>" id="knowledge_logo-id" value="<?php echo absint( $this->get_option( 'knowledge_logo_id' ) ); ?>" />
		</p>
		<p class="hide-if-no-tsf-js">
			<?php
			// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped.
			echo Form::get_image_uploader_form( [
				'id'   => 'knowledge_logo',
				'data' => [
					'inputType' => 'logo',
					'width'     => 512,
					'height'    => 512,
					'minWidth'  => 112,
					'minHeight' => 112,
					'flex'      => true,
				],
				'i18n' => [
					'button_title' => '',
					'button_text'  => __( 'Select Logo', 'autodescription' ),
				],
			] );
			?>
		</p>
		<?php

		$connectedi18n = _x( 'RelatedProfile', 'No spaces. E.g. https://facebook.com/RelatedProfile', 'autodescription' );
		/**
		 * @todo maybe genericons?
		 */
		$socialsites = [
			'facebook'   => [
				'option'      => 'knowledge_facebook',
				'dashicon'    => 'dashicons-facebook',
				'desc'        => __( 'Facebook Page', 'autodescription' ),
				'placeholder' => "https://www.facebook.com/$connectedi18n",
				'examplelink' => 'https://www.facebook.com/me',
			],
			'twitter'    => [
				'option'      => 'knowledge_twitter',
				'dashicon'    => 'dashicons-twitter',
				'desc'        => __( 'Twitter Profile', 'autodescription' ),
				'placeholder' => "https://twitter.com/$connectedi18n",
				'examplelink' => 'https://twitter.com/home', // No example link available.
			],
			'gplus'      => [
				'option'      => 'knowledge_gplus',
				'dashicon'    => 'dashicons-googleplus',
				'desc'        => _x( 'Google+ Profile&#8224;', 'Google+ is dead. &#8224; is a cross, indicating that.', 'autodescription' ),
				'placeholder' => '',
				'examplelink' => 'https://plus.google.com/me', // Left in, as Google redirects you to their deceased information page.
			],
			'instagram'  => [
				'option'      => 'knowledge_instagram',
				'dashicon'    => 'genericon-instagram',
				'desc'        => __( 'Instagram Profile', 'autodescription' ),
				'placeholder' => "https://instagram.com/$connectedi18n",
				'examplelink' => 'https://instagram.com/', // No example link available.
			],
			'youtube'    => [
				'option'      => 'knowledge_youtube',
				'dashicon'    => 'genericon-youtube',
				'desc'        => __( 'Youtube Profile', 'autodescription' ),
				'placeholder' => "https://www.youtube.com/channel/$connectedi18n",
				'examplelink' => 'https://www.youtube.com/user/%2f', // Yes a double slash.
			],
			'linkedin'   => [
				'option'      => 'knowledge_linkedin',
				'dashicon'    => 'genericon-linkedin-alt',
				'desc'        => __( 'LinkedIn Profile', 'autodescription' ),
				/**
				 * TODO switch to /in/ insteadof /company/ when knowledge-type is personal?
				 * Note that this feature is DEPRECATED. https://developers.google.com/search/docs/data-types/social-profile
				 */
				'placeholder' => "https://www.linkedin.com/company/$connectedi18n/",
				'examplelink' => 'https://www.linkedin.com/profile/view',
			],
			'pinterest'  => [
				'option'      => 'knowledge_pinterest',
				'dashicon'    => 'genericon-pinterest-alt',
				'desc'        => __( 'Pinterest Profile', 'autodescription' ),
				'placeholder' => "https://www.pinterest.com/$connectedi18n/",
				'examplelink' => 'https://www.pinterest.com/me/',
			],
			'soundcloud' => [
				'option'      => 'knowledge_soundcloud',
				'dashicon'    => 'genericon-cloud', // I know, it's not the real one. D:
				'desc'        => __( 'SoundCloud Profile', 'autodescription' ),
				'placeholder' => "https://soundcloud.com/$connectedi18n",
				'examplelink' => 'https://soundcloud.com/you',
			],
			'tumblr'     => [
				'option'      => 'knowledge_tumblr',
				'dashicon'    => 'genericon-tumblr',
				'desc'        => __( 'Tumblr Blog', 'autodescription' ),
				'placeholder' => "https://www.tumblr.com/blog/$connectedi18n",
				'examplelink' => 'https://www.tumblr.com/dashboard',  // No example link available.
			],
		];

		$output_social_presence = false;

		foreach ( $socialsites as $key => $v ) {
			if ( strlen( $this->get_option( $v['option'] ) ) ) {
				$output_social_presence = true;
				break;
			}
		}

		if ( $output_social_presence ) :
			?>
			<hr>
			<?php
			HTML::header_title( __( 'Connected Social Pages', 'autodescription' ) );
			HTML::description( __( "Don't have a page at a site or is the profile only privately accessible? Leave that field empty. Unsure? Fill it in anyway.", 'autodescription' ) );
			HTML::description( __( 'Add links that lead directly to the connected social pages of this website.', 'autodescription' ) );
			HTML::description( __( 'These settings do not affect sharing behavior with the social networks.', 'autodescription' ) );
			HTML::attention_description_noesc(
				$this->convert_markdown(
					sprintf(
						/* translators: %s = Learn more URL. Markdown! */
						esc_html__( 'These settings are marked for removal. When you clear a field, it will be hidden forever. [Learn more](%s).', 'autodescription' ),
						'https://developers.google.com/search/docs/data-types/social-profile'
					),
					[ 'a' ],
					[ 'a_internal' => false ]
				)
			);

			foreach ( $socialsites as $key => $v ) {

				if ( ! strlen( $this->get_option( $v['option'] ) ) ) continue;

				?>
				<p>
					<label for="<?php Input::field_id( $v['option'] ); ?>">
						<strong><?php echo esc_html( $v['desc'] ); ?></strong>
						<?php
						if ( $v['examplelink'] ) {
							HTML::make_info(
								__( 'View your profile.', 'autodescription' ),
								$v['examplelink']
							);
						}
						?>
					</label>
				</p>
				<p>
					<input type="url" name="<?php Input::field_name( $v['option'] ); ?>" class="large-text" id="<?php Input::field_id( $v['option'] ); ?>" placeholder="<?php echo esc_attr( $v['placeholder'] ); ?>" value="<?php echo esc_attr( $this->get_option( $v['option'] ) ); ?>" autocomplete=off />
				</p>
				<?php
			}
		endif; /* end $output_social_presence */
		break;

	default:
		break;
endswitch;
