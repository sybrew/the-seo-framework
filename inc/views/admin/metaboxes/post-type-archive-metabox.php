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

// Fetch the required instance within this file.
switch ( $this->get_view_instance( 'post_type_archive', $instance ) ) :
	case 'post_type_archive_main':
		$_settings_class = SeoSettings::class;
		$post_types      = $this->get_public_post_type_archives();

		$post_types_data = [];
		foreach ( $post_types as $post_type ) {
			$post_types_data[ $post_type ] = [
				'label' => $this->get_post_type_label( $post_type ),
				'url'   => $this->create_canonical_url( [ 'pta' => $post_type ] ),
			];
		}

		printf(
			'<span class=hidden id=tsf-post-type-archive-data %s></span>',
			// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- This escapes.
			HTML::make_data_attributes( [ 'postTypes' => $post_types_data ] )
		);

		?>
		<div id=tsf-post-type-archive-header-wrap class=tsf-fields style=display:none>
			<div id=tsf-post-type-archive-select-wrap>
				<label for=tsf-post-type-archive-selector><?php esc_html_e( 'Select post type:', 'autodescription' ); ?></label>
				<select id=tsf-post-type-archive-selector></select>
			</div>
		</div>
		<?php
		$i = 0;
		foreach ( $post_types as $post_type ) {
			$_generator_args = [ 'pta' => $post_type ];

			// Create `[ 'doctitle' => [ 'pta', $post_type ] ];`
			$_option_map = array_fill_keys(
				[
					'doctitle',
					'title_no_blog_name',
					'description',
					'og_title',
					'og_description',
					'tw_title',
					'tw_description',
					'social_image_url',
					'social_image_id',
					'canonical',
					'noindex',
					'nofollow',
					'noarchive',
					'redirect',
				],
				[ 'pta', $post_type ]
			);
			// Create: `[ 'doctitle' => [ 'pta', $post_type, 'doctitle' ] ];`
			array_walk(
				$_option_map,
				static function( &$input_id, $key ) {
					$input_id = array_merge( $input_id, [ $key ] );
				}
			);

			$tabs = [
				'general'    => [
					'name'     => __( 'General', 'autodescription' ),
					'callback' => [ $_settings_class, '_post_type_archive_metabox_general_tab' ],
					'dashicon' => 'admin-generic',
					'args'     => compact( 'post_type', '_generator_args', '_option_map' ),
				],
				'social'     => [
					'name'     => __( 'Social', 'autodescription' ),
					'callback' => [ $_settings_class, '_post_type_archive_metabox_social_tab' ],
					'dashicon' => 'share',
					'args'     => compact( 'post_type', '_generator_args', '_option_map' ),
				],
				'visibility' => [
					'name'     => __( 'Visibility', 'autodescription' ),
					'callback' => [ $_settings_class, '_post_type_archive_metabox_visibility_tab' ],
					'dashicon' => 'visibility',
					'args'     => compact( 'post_type', '_generator_args', '_option_map' ),
				],
			];

			printf(
				'<div class=tsf-post-type-archive-wrap %s>',
				// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- This escapes.
				HTML::make_data_attributes( [ 'postType' => $post_type ] )
			);
			?>
				<div class=tsf-post-type-archive-if-excluded style=display:none>
					<?php
					HTML::attention_description(
						__( "This post type is excluded, so these settings won't have any effect.", 'autodescription' )
					)
					?>
				</div>
				<div class=tsf-post-type-archive-if-not-excluded>
					<div class=tsf-post-type-header>
						<?php
						// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- it is.
						echo HTML::get_header_title(
							vsprintf(
								'%s &ndash; <span class=tsf-post-type-archive-details><code>%s</code> %s</span>',
								[
									sprintf(
										/* translators: 1 = Post Type Archive name */
										esc_html__( 'Archive of %s', 'autodescription' ),
										esc_html( $post_types_data[ $post_type ]['label'] )
									),
									esc_html( $post_type ),
									sprintf(
										'<span class=tsf-post-type-archive-link><a href="%s" target=_blank rel=noopener>[%s]</a></span>',
										esc_url( $post_types_data[ $post_type ]['url'] ),
										esc_html__( 'View archive', 'autodescription' )
									),
								]
							)
						);
						?>
					</div>
					<?php
					SeoSettings::_nav_tab_wrapper(
						"post_type_archive_{$post_type}",
						/**
						 * @since 4.2.0
						 * @param array   $tabs      The default tabs.
						 * @param strring $post_type The post type archive's name.
						 */
						(array) apply_filters_ref_array(
							'the_seo_framework_post_type_archive_settings_tabs',
							[
								$tabs,
								$post_type,
							]
						)
					);
					?>
				</div>
			</div>
			<?php
			$i++ or print( '<hr class=hide-if-tsf-js>' );
		}
		break;

	case 'post_type_archive_general_tab':
		?>
		<p>
			<label for="<?php Input::field_id( $_option_map['doctitle'] ); ?>" class=tsf-toblock>
				<strong><?php esc_html_e( 'Meta Title', 'autodescription' ); ?></strong>
				<?php
					echo ' ';
					HTML::make_info(
						__( 'The meta title can be used to determine the title used on search engine result pages.', 'autodescription' ),
						'https://developers.google.com/search/docs/advanced/appearance/good-titles-snippets#page-titles'
					);
				?>
			</label>
		</p>
		<?php
		// Output these unconditionally, with inline CSS attached to allow reacting on settings.
		Form::output_character_counter_wrap( Input::get_field_id( $_option_map['doctitle'] ), (bool) $this->get_option( 'display_character_counter' ) );
		Form::output_pixel_counter_wrap( Input::get_field_id( $_option_map['doctitle'] ), 'title', (bool) $this->get_option( 'display_pixel_counter' ) );
		?>
		<p class=tsf-title-wrap>
			<input type="text" name="<?php Input::field_name( $_option_map['doctitle'] ); ?>" class="large-text" id="<?php Input::field_id( $_option_map['doctitle'] ); ?>" value="<?php echo $this->esc_attr_preserve_amp( $this->get_post_type_archive_meta_item( 'doctitle', $post_type ) ); ?>" autocomplete=off />
			<?php
			[ $prefix_value, $default_title ] = $this->get_generated_archive_title( get_post_type_object( $post_type ), 'admin' );
			$this->output_js_title_data(
				Input::get_field_id( $_option_map['doctitle'] ),
				[
					'state' => [
						'defaultTitle'      => $this->s_title( $default_title ),
						'addAdditions'      => $this->use_title_branding( $_generator_args ),
						'useSocialTagline'  => $this->use_title_branding( $_generator_args, true ),
						'additionValue'     => $this->s_title( $this->get_blogname() ),
						'additionPlacement' => 'left' === $this->get_title_seplocation() ? 'before' : 'after',
						'prefixValue'       => $this->s_title( $prefix_value ),
						'showPrefix'        => $this->use_generated_archive_prefix( get_post_type_object( $post_type ) ),
					],
				]
			);
			?>
		</p>

		<div class=tsf-title-tagline-toggle>
		<?php
			$info = HTML::make_info(
				__( 'Use this when you want to rearrange the title parts manually.', 'autodescription' ),
				'',
				false
			);

			HTML::wrap_fields(
				Input::make_checkbox( [
					'id'     => $_option_map['title_no_blog_name'],
					'label'  => esc_html__( 'Remove the site title?', 'autodescription' ) . " $info",
					'escape' => false,
				] ),
				true
			);
		?>
		</div>

		<hr>

		<p>
			<label for="<?php Input::field_id( $_option_map['description'] ); ?>" class="tsf-toblock">
				<strong><?php esc_html_e( 'Meta Description', 'autodescription' ); ?></strong>
				<?php
					echo ' ';
					HTML::make_info(
						__( 'The meta description can be used to determine the text used under the title on search engine results pages.', 'autodescription' ),
						'https://developers.google.com/search/docs/advanced/appearance/good-titles-snippets#meta-descriptions'
					);
				?>
			</label>
		</p>
		<?php
		// Output these unconditionally, with inline CSS attached to allow reacting on settings.
		Form::output_character_counter_wrap( Input::get_field_id( $_option_map['description'] ), (bool) $this->get_option( 'display_character_counter' ) );
		Form::output_pixel_counter_wrap( Input::get_field_id( $_option_map['description'] ), 'description', (bool) $this->get_option( 'display_pixel_counter' ) );
		?>
		<p>
			<textarea name="<?php Input::field_name( $_option_map['description'] ); ?>" class="large-text" id="<?php Input::field_id( $_option_map['description'] ); ?>" rows="3" cols="70"><?php echo esc_attr( $this->get_option( $_option_map['description'] ) ); ?></textarea>
			<?php
			$this->output_js_description_elements(); // legacy
			$this->output_js_description_data(
				Input::get_field_id( $_option_map['description'] ),
				[
					'state' => [
						'defaultDescription' => $this->get_generated_description( $_generator_args ),
					],
				]
			);
			?>
		</p>
		<?php
		break;
	case 'post_type_archive_social_tab':
		$this->output_js_social_data(
			"pta_social_settings_{$post_type}",
			[
				'og' => [
					'state' => [
						'defaultTitle' => $this->s_title( $this->get_generated_open_graph_title( $_generator_args, false ) ),
						'addAdditions' => $this->use_title_branding( $_generator_args, 'og' ),
						'defaultDesc'  => $this->s_description( $this->get_generated_open_graph_description( $_generator_args, false ) ),
					],
				],
				'tw' => [
					'state' => [
						'defaultTitle' => $this->s_title( $this->get_generated_twitter_title( $_generator_args, false ) ),
						'addAdditions' => $this->use_title_branding( $_generator_args, 'twitter' ),
						'defaultDesc'  => $this->s_description( $this->get_generated_twitter_description( $_generator_args, false ) ),
					],
				],
			]
		);

		?>
		<p>
			<label for="<?php Input::field_id( $_option_map['og_title'] ); ?>" class="tsf-toblock">
				<strong><?php esc_html_e( 'Open Graph Title', 'autodescription' ); ?></strong>
			</label>
		</p>
		<?php
		// Output this unconditionally, with inline CSS attached to allow reacting on settings.
		Form::output_character_counter_wrap( Input::get_field_id( $_option_map['og_title'] ), (bool) $this->get_option( 'display_character_counter' ) );
		?>
		<p>
			<input type="text" name="<?php Input::field_name( $_option_map['og_title'] ); ?>" class="large-text" id="<?php Input::field_id( $_option_map['og_title'] ); ?>" value="<?php echo $this->esc_attr_preserve_amp( $this->get_option( $_option_map['og_title'] ) ); ?>" autocomplete=off data-tsf-social-group=<?php echo esc_attr( "pta_social_settings_{$post_type}" ); ?> data-tsf-social-type=ogTitle />
		</p>

		<p>
			<label for="<?php Input::field_id( $_option_map['og_description'] ); ?>" class="tsf-toblock">
				<strong><?php esc_html_e( 'Open Graph Description', 'autodescription' ); ?></strong>
			</label>
		</p>
		<?php
		// Output this unconditionally, with inline CSS attached to allow reacting on settings.
		Form::output_character_counter_wrap( Input::get_field_id( $_option_map['og_description'] ), (bool) $this->get_option( 'display_character_counter' ) );
		?>
		<p>
			<textarea name="<?php Input::field_name( $_option_map['og_description'] ); ?>" class="large-text" id="<?php Input::field_id( $_option_map['og_description'] ); ?>" rows="3" cols="70" autocomplete=off data-tsf-social-group=<?php echo esc_attr( "pta_social_settings_{$post_type}" ); ?> data-tsf-social-type=ogDesc><?php echo esc_attr( $this->get_option( $_option_map['og_description'] ) ); ?></textarea>
		</p>

		<hr>

		<p>
			<label for="<?php Input::field_id( $_option_map['tw_title'] ); ?>" class="tsf-toblock">
				<strong><?php esc_html_e( 'Twitter Title', 'autodescription' ); ?></strong>
			</label>
		</p>
		<?php
		// Output this unconditionally, with inline CSS attached to allow reacting on settings.
		Form::output_character_counter_wrap( Input::get_field_id( $_option_map['tw_title'] ), (bool) $this->get_option( 'display_character_counter' ) );
		?>
		<p>
			<input type="text" name="<?php Input::field_name( $_option_map['tw_title'] ); ?>" class="large-text" id="<?php Input::field_id( $_option_map['tw_title'] ); ?>" value="<?php echo $this->esc_attr_preserve_amp( $this->get_option( $_option_map['tw_title'] ) ); ?>" autocomplete=off data-tsf-social-group=<?php echo esc_attr( "pta_social_settings_{$post_type}" ); ?> data-tsf-social-type=twTitle />
		</p>

		<p>
			<label for="<?php Input::field_id( $_option_map['tw_description'] ); ?>" class="tsf-toblock">
				<strong><?php esc_html_e( 'Twitter Description', 'autodescription' ); ?></strong>
			</label>
		</p>
		<?php
		// Output this unconditionally, with inline CSS attached to allow reacting on settings.
		Form::output_character_counter_wrap( Input::get_field_id( $_option_map['tw_description'] ), (bool) $this->get_option( 'display_character_counter' ) );
		?>
		<p>
			<textarea name="<?php Input::field_name( $_option_map['tw_description'] ); ?>" class="large-text" id="<?php Input::field_id( $_option_map['tw_description'] ); ?>" rows="3" cols="70" autocomplete=off data-tsf-social-group=<?php echo esc_attr( "pta_social_settings_{$post_type}" ); ?> data-tsf-social-type=twDesc><?php echo esc_attr( $this->get_option( $_option_map['tw_description'] ) ); ?></textarea>
		</p>

		<hr>

		<p>
			<label for="<?php echo esc_attr( "tsf_pta_socialimage_{$post_type}" ); ?>-url">
				<strong><?php esc_html_e( 'Social Image URL', 'autodescription' ); ?></strong>
				<?php
				HTML::make_info(
					__( "The social image URL can be used by search engines and social networks alike. It's best to use an image with a 1.91:1 aspect ratio that is at least 1200px wide for universal support.", 'autodescription' ),
					'https://developers.facebook.com/docs/sharing/best-practices#images'
				);
				?>
			</label>
		</p>
		<p>
			<input class="large-text" type="url" name="<?php Input::field_name( $_option_map['social_image_url'] ); ?>" id="<?php echo esc_attr( "tsf_pta_socialimage_{$post_type}" ); ?>-url" placeholder="<?php echo esc_url( current( $this->get_generated_image_details( $_generator_args, true, 'social', true ) )['url'] ?? '' ); ?>" value="<?php echo esc_url( $this->get_option( $_option_map['social_image_url'] ) ); ?>" />
			<input type="hidden" name="<?php Input::field_name( $_option_map['social_image_id'] ); ?>" id="<?php echo esc_attr( "tsf_pta_socialimage_{$post_type}" ); ?>-id" value="<?php echo absint( $this->get_option( $_option_map['social_image_id'] ) ); ?>" disabled class="tsf-enable-media-if-js" />
		</p>
		<p class="hide-if-no-tsf-js">
			<?php
			// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped.
			echo Form::get_image_uploader_form( [ 'id' => "tsf_pta_socialimage_{$post_type}" ] );
			?>
		</p>
		<?php
		break;
	case 'post_type_archive_visibility_tab':
		?>
		<p>
			<label for="<?php Input::field_id( $_option_map['canonical'] ); ?>" class="tsf-toblock">
				<strong><?php esc_html_e( 'Canonical URL', 'autodescription' ); ?></strong>
				<?php
					echo ' ';
					HTML::make_info(
						__( 'This urges search engines to go to the outputted URL.', 'autodescription' ),
						'https://developers.google.com/search/docs/advanced/crawling/consolidate-duplicate-urls'
					);
				?>
			</label>
		</p>
		<p>
			<input type="url" name="<?php Input::field_name( $_option_map['canonical'] ); ?>" class="large-text" id="<?php Input::field_id( $_option_map['canonical'] ); ?>" placeholder="<?php echo esc_url( $this->create_canonical_url( $_generator_args ) ); ?>" value="<?php echo esc_url( $this->get_option( $_option_map['canonical'] ) ); ?>" autocomplete=off />
		</p>

		<hr>
		<?php
		$robots_settings = [
			'noindex'   => [
				'force_on'    => 'index',
				'force_off'   => 'noindex',
				'label'       => __( 'Indexing', 'autodescription' ),
				'_defaultOn'  => 'index',
				'_defaultOff' => 'noindex',
				'_value'      => $this->get_option( $_option_map['noindex'] ),
				'_info'       => [
					__( 'This tells search engines not to show this term in their search results.', 'autodescription' ),
					'https://developers.google.com/search/docs/advanced/crawling/block-indexing',
				],
			],
			'nofollow'  => [
				'force_on'    => 'follow',
				'force_off'   => 'nofollow',
				'label'       => __( 'Link following', 'autodescription' ),
				'_defaultOn'  => 'follow',
				'_defaultOff' => 'nofollow',
				'_value'      => $this->get_option( $_option_map['nofollow'] ),
				'_info'       => [
					__( 'This tells search engines not to follow links on this term.', 'autodescription' ),
					'https://developers.google.com/search/docs/advanced/guidelines/qualify-outbound-links',
				],
			],
			'noarchive' => [
				'force_on'    => 'archive',
				'force_off'   => 'noarchive',
				'label'       => __( 'Archiving', 'autodescription' ),
				'_defaultOn'  => 'archive',
				'_defaultOff' => 'noarchive',
				'_value'      => $this->get_option( $_option_map['noarchive'] ),
				'_info'       => [
					__( 'This tells search engines not to save a cached copy of this term.', 'autodescription' ),
					'https://developers.google.com/search/docs/advanced/robots/robots_meta_tag#directives',
				],
			],
		];

		foreach ( $robots_settings as $_r_type => $_rs ) :
			// phpcs:enable, WordPress.Security.EscapeOutput
			HTML::wrap_fields(
				vsprintf(
					'<p><label for="%1$s"><strong>%2$s</strong> %3$s</label></p>',
					[
						Input::get_field_id( $_option_map[ $_r_type ] ),
						esc_html( $_rs['label'] ),
						HTML::make_info(
							$_rs['_info'][0],
							$_rs['_info'][1] ?? '',
							false
						),
					]
				),
				true
			);
			// phpcs:disable, WordPress.Security.EscapeOutput -- make_single_select_form() escapes.
			echo Form::make_single_select_form( [
				'id'      => Input::get_field_id( $_option_map[ $_r_type ] ),
				'class'   => 'tsf-select-block',
				'name'    => Input::get_field_name( $_option_map[ $_r_type ] ),
				'label'   => '',
				'options' => [
					0  => __( 'Default (unknown)', 'autodescription' ),
					-1 => $_rs['force_on'],
					1  => $_rs['force_off'],
				],
				'default' => $_rs['_value'],
				'data'    => [
					/* translators: %s = default option value */
					'defaultI18n' => __( 'Default (%s)', 'autodescription' ),
					'defaultOn'   => $_rs['_defaultOn'],
					'defaultOff'  => $_rs['_defaultOff'],
				],
			] );
		endforeach;
		?>
		<hr>

		<p>
			<label for="<?php Input::field_id( $_option_map['redirect'] ); ?>" class="tsf-toblock">
				<strong><?php esc_html_e( '301 Redirect URL', 'autodescription' ); ?></strong>
				<?php
					echo ' ';
					HTML::make_info(
						__( 'This will force visitors to go to another URL.', 'autodescription' ),
						'https://developers.google.com/search/docs/advanced/crawling/301-redirects'
					);
				?>
			</label>
		</p>
		<p>
			<input type="url" name="<?php Input::field_name( $_option_map['redirect'] ); ?>" class="large-text" id="<?php Input::field_id( $_option_map['redirect'] ); ?>" value="<?php echo esc_url( $this->get_option( $_option_map['redirect'] ) ); ?>" autocomplete=off />
		</p>
		<?php
		break;
endswitch;
