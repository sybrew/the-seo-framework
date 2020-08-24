<?php
/**
 * @package The_SEO_Framework\Views\Admin\Metaboxes
 * @subpackage The_SEO_Framework\Admin\Settings
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

use The_SEO_Framework\Bridges\SeoSettings;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and the_seo_framework()->_verify_include_secret( $_secret ) or die;

// Fetch the required instance within this file.
$instance = $this->get_view_instance( 'the_seo_framework_homepage_metabox', $instance );

$home_id = $this->get_the_front_page_ID();

$_generator_args = [
	'id'       => $home_id,
	'taxonomy' => '',
];

switch ( $instance ) :
	case 'the_seo_framework_homepage_metabox_main':
		$this->description( __( 'These settings will take precedence over the settings set within the homepage edit screen, if any.', 'autodescription' ) );
		?>
		<hr>
		<?php

		$default_tabs = [
			'general'   => [
				'name'     => __( 'General', 'autodescription' ),
				'callback' => SeoSettings::class . '::_homepage_metabox_general_tab',
				'dashicon' => 'admin-generic',
			],
			'additions' => [
				'name'     => __( 'Additions', 'autodescription' ),
				'callback' => SeoSettings::class . '::_homepage_metabox_additions_tab',
				'dashicon' => 'plus',
			],
			'social'    => [
				'name'     => __( 'Social', 'autodescription' ),
				'callback' => SeoSettings::class . '::_homepage_metabox_social_tab',
				'dashicon' => 'share',
			],
			'robots'    => [
				'name'     => __( 'Robots', 'autodescription' ),
				'callback' => SeoSettings::class . '::_homepage_metabox_robots_tab',
				'dashicon' => 'visibility',
			],
		];

		/**
		 * @since 2.6.0
		 * @param array $defaults The default tabs.
		 * @param array $args     The args added on the callback.
		 */
		$defaults = (array) apply_filters( 'the_seo_framework_homepage_settings_tabs', $default_tabs, $args );

		$tabs = wp_parse_args( $args, $defaults );

		SeoSettings::_nav_tab_wrapper( 'homepage', $tabs );
		break;

	case 'the_seo_framework_homepage_metabox_general':
		?>
		<p>
			<label for="<?php $this->field_id( 'homepage_title' ); ?>" class="tsf-toblock">
				<strong><?php esc_html_e( 'Meta Title', 'autodescription' ); ?></strong>
				<?php
					echo ' ';
					$this->make_info(
						__( 'The meta title can be used to determine the title used on search engine result pages.', 'autodescription' ),
						'https://support.google.com/webmasters/answer/35624#page-titles'
					);
				?>
			</label>
		</p>
		<?php
		// Output these unconditionally, with inline CSS attached to allow reacting on settings.
		$this->output_character_counter_wrap( $this->get_field_id( 'homepage_title' ), '', (bool) $this->get_option( 'display_character_counter' ) );
		$this->output_pixel_counter_wrap( $this->get_field_id( 'homepage_title' ), 'title', (bool) $this->get_option( 'display_pixel_counter' ) );
		?>
		<p class=tsf-title-wrap>
			<input type="text" name="<?php $this->field_name( 'homepage_title' ); ?>" class="large-text" id="<?php $this->field_id( 'homepage_title' ); ?>" value="<?php echo $this->esc_attr_preserve_amp( $this->get_option( 'homepage_title' ) ); ?>" autocomplete=off />
			<?php
			$this->output_js_title_elements(); // legacy
			$this->output_js_title_data(
				$this->get_field_id( 'homepage_title' ),
				[
					'state' => [
						'refTitleLocked'    => false,
						'defaultTitle'      =>
							( $home_id ? $this->get_post_meta_item( '_genesis_title', $home_id ) : '' )
							?: $this->get_filtered_raw_generated_title( $_generator_args ),
						'addAdditions'      => $this->use_title_branding( $_generator_args ),
						'useSocialTagline'  => $this->use_title_branding( $_generator_args, true ),
						'additionValue'     => $this->get_home_title_additions(),
						'additionPlacement' => 'left' === $this->get_home_title_seplocation() ? 'before' : 'after',
						'hasLegacy'         => true,
					],
				]
			);
			?>
		</p>
		<?php
		$this->description( __( 'Note: The input value of this field may be used to describe the name of the site elsewhere.', 'autodescription' ) );

		if ( $home_id && $this->get_post_meta_item( '_genesis_title', $home_id ) ) {
			$this->description( __( 'Note: The title placeholder is fetched from the Page SEO Settings on the homepage.', 'autodescription' ) );
		}

		/**
		 * @since 2.8.0
		 * @param bool $warn Whether to warn that there's a plugin active with multiple homepages.
		 */
		if ( $home_id && apply_filters( 'the_seo_framework_warn_homepage_global_title', false ) ) {
			$this->attention_noesc(
				// Markdown escapes.
				$this->convert_markdown(
					sprintf(
						/* translators: %s = Homepage URL markdown */
						esc_html__( 'A plugin has been detected that suggests to maintain this option on the [homepage](%s).', 'autodescription' ),
						esc_url( admin_url( 'post.php?post=' . $home_id . '&action=edit#tsf-inpost-box' ) )
					),
					[ 'a' ],
					[ 'a_internal' => false ] // opens in new tab.
				)
			);
		}
		?>
		<hr>

		<p>
			<label for="<?php $this->field_id( 'homepage_description' ); ?>" class="tsf-toblock">
				<strong><?php esc_html_e( 'Meta Description', 'autodescription' ); ?></strong>
				<?php
					echo ' ';
					$this->make_info(
						__( 'The meta description can be used to determine the text used under the title on search engine results pages.', 'autodescription' ),
						'https://support.google.com/webmasters/answer/35624#meta-descriptions'
					);
				?>
			</label>
		</p>
		<?php
		// Output these unconditionally, with inline CSS attached to allow reacting on settings.
		$this->output_character_counter_wrap( $this->get_field_id( 'homepage_description' ), '', (bool) $this->get_option( 'display_character_counter' ) );
		$this->output_pixel_counter_wrap( $this->get_field_id( 'homepage_description' ), 'description', (bool) $this->get_option( 'display_pixel_counter' ) );
		?>
		<p>
			<textarea name="<?php $this->field_name( 'homepage_description' ); ?>" class="large-text" id="<?php $this->field_id( 'homepage_description' ); ?>" rows="3" cols="70"><?php echo esc_attr( $this->get_option( 'homepage_description' ) ); ?></textarea>
			<?php
			$this->output_js_description_elements(); // legacy
			$this->output_js_description_data(
				$this->get_field_id( 'homepage_description' ),
				[
					'state' => [
						'defaultDescription' =>
							( $home_id ? $this->get_post_meta_item( '_genesis_description', $home_id ) : '' )
							?: $this->get_generated_description( $_generator_args ),
						'hasLegacy'          => true,
					],
				]
			);
			?>
		</p>
		<?php

		if ( $home_id && $this->get_post_meta_item( '_genesis_description', $home_id ) ) {
			$this->description(
				__( 'Note: The description placeholder is fetched from the Page SEO Settings on the homepage.', 'autodescription' )
			);
		}

		/**
		 * @since 2.8.0
		 * @param bool $warn Whether to warn that there's a plugin active with multiple homepages.
		 */
		if ( $home_id && apply_filters( 'the_seo_framework_warn_homepage_global_description', false ) ) {
			$this->attention_noesc(
				// Markdown escapes.
				$this->convert_markdown(
					sprintf(
						/* translators: %s = Homepage URL markdown */
						esc_html__( 'A plugin has been detected that suggests to maintain this option on the [homepage](%s).', 'autodescription' ),
						esc_url( admin_url( 'post.php?post=' . $home_id . '&action=edit#tsf-inpost-box' ) )
					),
					[ 'a' ],
					[ 'a_internal' => false ] // opens in new tab.
				)
			);
		}
		break;

	case 'the_seo_framework_homepage_metabox_additions':
		$tagline_placeholder = $this->s_title_raw( $this->get_blogdescription() );

		// Fetches escaped title parts.
		$_example_title = $this->escape_title(
			$this->get_filtered_raw_custom_field_title( $_generator_args ) ?: $this->get_filtered_raw_generated_title( $_generator_args )
		);
		// FIXME? When no blog description or tagline is set... this will be empty and be ugly on no-JS.
		$_example_blogname  = $this->escape_title( $this->get_home_title_additions() ?: $this->get_static_untitled_title() );
		$_example_separator = esc_html( $this->get_separator( 'title' ) );

		// TODO very readable.
		$example_left  = '<em><span class="tsf-custom-blogname-js"><span class="tsf-custom-tagline-js">' . $_example_blogname . '</span><span class="tsf-sep-js"> ' . $_example_separator . ' </span></span><span class="tsf-custom-title-js">' . $_example_title . '</span></em>';
		$example_right = '<em><span class="tsf-custom-title-js">' . $_example_title . '</span><span class="tsf-custom-blogname-js"><span class="tsf-sep-js"> ' . $_example_separator . ' </span><span class="tsf-custom-tagline-js">' . $_example_blogname . '</span></span></em>';

		?>

		<p>
			<label for="<?php $this->field_id( 'homepage_title_tagline' ); ?>" class="tsf-toblock">
				<strong><?php esc_html_e( 'Meta Title Additions', 'autodescription' ); ?></strong>
			</label>
		</p>
		<p>
			<input type="text" name="<?php $this->field_name( 'homepage_title_tagline' ); ?>" class="large-text" id="<?php $this->field_id( 'homepage_title_tagline' ); ?>" placeholder="<?php echo esc_attr( $tagline_placeholder ); ?>" value="<?php echo $this->esc_attr_preserve_amp( $this->get_option( 'homepage_title_tagline' ) ); ?>" autocomplete=off />
		</p>

		<div id="tsf-title-tagline-toggle">
		<?php
			$this->wrap_fields(
				$this->make_checkbox(
					'homepage_tagline',
					esc_html__( 'Add Meta Title Additions to the homepage title?', 'autodescription' ),
					'',
					false
				),
				true
			);
		?>
		</div>

		<hr>

		<fieldset>
			<legend>
				<h4><?php esc_html_e( 'Meta Title Additions Location', 'autodescription' ); ?></h4>
			</legend>

			<p id="tsf-home-title-location" class="tsf-fields">
				<span class="tsf-toblock">
					<input type="radio" name="<?php $this->field_name( 'home_title_location' ); ?>" id="<?php $this->field_id( 'home_title_location_left' ); ?>" value="left" <?php checked( $this->get_option( 'home_title_location' ), 'left' ); ?> />
					<label for="<?php $this->field_id( 'home_title_location_left' ); ?>">
						<span><?php esc_html_e( 'Left:', 'autodescription' ); ?></span>
						<?php
						// phpcs:ignore, WordPress.Security.EscapeOutput -- $example_left is already escaped.
						echo $this->code_wrap_noesc( $example_left );
						?>
					</label>
				</span>
				<span class="tsf-toblock">
					<input type="radio" name="<?php $this->field_name( 'home_title_location' ); ?>" id="<?php $this->field_id( 'home_title_location_right' ); ?>" value="right" <?php checked( $this->get_option( 'home_title_location' ), 'right' ); ?> />
					<label for="<?php $this->field_id( 'home_title_location_right' ); ?>">
						<span><?php esc_html_e( 'Right:', 'autodescription' ); ?></span>
						<?php
						// phpcs:ignore, WordPress.Security.EscapeOutput -- $example_right is already escaped.
						echo $this->code_wrap_noesc( $example_right );
						?>
					</label>
				</span>
			</p>
		</fieldset>
		<?php
		break;

	case 'the_seo_framework_homepage_metabox_social':
		// Gets custom fields from page.
		$custom_og_title = $home_id ? $this->get_post_meta_item( '_open_graph_title', $home_id ) : '';
		$custom_og_desc  = $home_id ? $this->get_post_meta_item( '_open_graph_description', $home_id ) : '';
		$custom_tw_title = $home_id ? $this->get_post_meta_item( '_twitter_title', $home_id ) : '';
		$custom_tw_desc  = $home_id ? $this->get_post_meta_item( '_twitter_description', $home_id ) : '';

		$social_placeholders = $this->_get_social_placeholders( $_generator_args, 'settings' );

		?>
		<p>
			<label for="<?php $this->field_id( 'homepage_og_title' ); ?>" class="tsf-toblock">
				<strong>
					<?php
					esc_html_e( 'Open Graph Title', 'autodescription' );
					?>
				</strong>
			</label>
		</p>
		<?php
		// Output this unconditionally, with inline CSS attached to allow reacting on settings.
		$this->output_character_counter_wrap( $this->get_field_id( 'homepage_og_title' ), '', (bool) $this->get_option( 'display_character_counter' ) );
		?>
		<p>
			<input type="text" name="<?php $this->field_name( 'homepage_og_title' ); ?>" class="large-text" id="<?php $this->field_id( 'homepage_og_title' ); ?>" placeholder="<?php echo esc_attr( $social_placeholders['title']['og'] ); ?>" value="<?php echo $this->esc_attr_preserve_amp( $this->get_option( 'homepage_og_title' ) ); ?>" autocomplete=off />
		</p>
		<?php
		if ( $this->has_page_on_front() && $custom_og_title ) {
			$this->description(
				__( 'Note: The title placeholder is fetched from the Page SEO Settings on the homepage.', 'autodescription' )
			);
		}
		?>

		<p>
			<label for="<?php $this->field_id( 'homepage_og_description' ); ?>" class="tsf-toblock">
				<strong>
					<?php
					esc_html_e( 'Open Graph Description', 'autodescription' );
					?>
				</strong>
			</label>
		</p>
		<?php
		// Output this unconditionally, with inline CSS attached to allow reacting on settings.
		$this->output_character_counter_wrap( $this->get_field_id( 'homepage_og_description' ), '', (bool) $this->get_option( 'display_character_counter' ) );
		?>
		<p>
			<textarea name="<?php $this->field_name( 'homepage_og_description' ); ?>" class="large-text" id="<?php $this->field_id( 'homepage_og_description' ); ?>" rows="3" cols="70" placeholder="<?php echo esc_attr( $social_placeholders['description']['og'] ); ?>" autocomplete=off><?php echo esc_attr( $this->get_option( 'homepage_og_description' ) ); ?></textarea>
		</p>
		<?php
		if ( $this->has_page_on_front() && $custom_og_desc ) {
			$this->description(
				__( 'Note: The description placeholder is fetched from the Page SEO Settings on the homepage.', 'autodescription' )
			);
		}
		?>
		<hr>

		<p>
			<label for="<?php $this->field_id( 'homepage_twitter_title' ); ?>" class="tsf-toblock">
				<strong>
					<?php
					esc_html_e( 'Twitter Title', 'autodescription' );
					?>
				</strong>
			</label>
		</p>
		<?php
		// Output this unconditionally, with inline CSS attached to allow reacting on settings.
		$this->output_character_counter_wrap( $this->get_field_id( 'homepage_twitter_title' ), '', (bool) $this->get_option( 'display_character_counter' ) );
		?>
		<p>
			<input type="text" name="<?php $this->field_name( 'homepage_twitter_title' ); ?>" class="large-text" id="<?php $this->field_id( 'homepage_twitter_title' ); ?>" placeholder="<?php echo esc_attr( $social_placeholders['title']['twitter'] ); ?>" value="<?php echo $this->esc_attr_preserve_amp( $this->get_option( 'homepage_twitter_title' ) ); ?>" autocomplete=off />
		</p>
		<?php
		if ( $this->has_page_on_front() && ( $custom_og_title || $custom_tw_title ) ) {
			$this->description(
				__( 'Note: The title placeholder is fetched from the Page SEO Settings on the homepage.', 'autodescription' )
			);
		}
		?>

		<p>
			<label for="<?php $this->field_id( 'homepage_twitter_description' ); ?>" class="tsf-toblock">
				<strong>
					<?php
					esc_html_e( 'Twitter Description', 'autodescription' );
					?>
				</strong>
			</label>
		</p>
		<?php
		// Output this unconditionally, with inline CSS attached to allow reacting on settings.
		$this->output_character_counter_wrap( $this->get_field_id( 'homepage_twitter_description' ), '', (bool) $this->get_option( 'display_character_counter' ) );
		?>
		<p>
			<textarea name="<?php $this->field_name( 'homepage_twitter_description' ); ?>" class="large-text" id="<?php $this->field_id( 'homepage_twitter_description' ); ?>" rows="3" cols="70" placeholder="<?php echo esc_attr( $social_placeholders['description']['twitter'] ); ?>" autocomplete=off><?php echo esc_attr( $this->get_option( 'homepage_twitter_description' ) ); ?></textarea>
		</p>
		<?php
		if ( $this->has_page_on_front() && ( $custom_og_desc || $custom_tw_desc ) ) {
			$this->description(
				__( 'Note: The description placeholder is fetched from the Page SEO Settings on the homepage.', 'autodescription' )
			);
		}
		?>
		<hr>

		<h4><?php esc_html_e( 'Social Image Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'A social image can be displayed when your homepage is shared. It is a great way to grab attention.', 'autodescription' ) );

		// Fetch image placeholder.
		$image_details     = current( $this->get_generated_image_details( $_generator_args, true, 'social', true ) );
		$image_placeholder = isset( $image_details['url'] ) ? $image_details['url'] : '';

		?>
		<p>
			<label for="tsf_homepage_socialimage-url">
				<strong><?php esc_html_e( 'Social Image URL', 'autodescription' ); ?></strong>
				<?php
				$this->make_info(
					__( "The social image URL can be used by search engines and social networks alike. It's best to use an image with a 1.91:1 aspect ratio that is at least 1200px wide for universal support.", 'autodescription' ),
					'https://developers.facebook.com/docs/sharing/best-practices#images'
				);
				?>
			</label>
		</p>
		<p>
			<input class="large-text" type="url" name="<?php $this->field_name( 'homepage_social_image_url' ); ?>" id="tsf_homepage_socialimage-url" placeholder="<?php echo esc_url( $image_placeholder ); ?>" value="<?php echo esc_url( $this->get_option( 'homepage_social_image_url' ) ); ?>" />
			<input type="hidden" name="<?php $this->field_name( 'homepage_social_image_id' ); ?>" id="tsf_homepage_socialimage-id" value="<?php echo absint( $this->get_option( 'homepage_social_image_id' ) ); ?>" disabled class="tsf-enable-media-if-js" />
		</p>
		<p class="hide-if-no-tsf-js">
			<?php
			// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped.
			echo $this->get_social_image_uploader_form( 'tsf_homepage_socialimage' );
			?>
		</p>
		<?php
		break;

	case 'the_seo_framework_homepage_metabox_robots':
		$noindex_post   = $home_id ? $this->get_post_meta_item( '_genesis_noindex', $home_id ) : '';
		$nofollow_post  = $home_id ? $this->get_post_meta_item( '_genesis_nofollow', $home_id ) : '';
		$noarchive_post = $home_id ? $this->get_post_meta_item( '_genesis_noarchive', $home_id ) : '';

		$checked_home = '';
		/**
		 * Shows user that the setting is checked on the homepage.
		 * Adds starting - with space to maintain readability.
		 *
		 * @since 2.2.4
		 */
		if ( $noindex_post || $nofollow_post || $noarchive_post ) {
			$checked_home = sprintf(
				'- %s',
				vsprintf(
					'<a href="%s" title="%s" target=_blank class=attention>%s</a>',
					[
						esc_url( admin_url( 'post.php?post=' . $home_id . '&action=edit#tsf-inpost-box' ) ),
						esc_attr_x( 'Edit homepage page settings', 'Bear with me: the homepage can be edited globally, or via its page. Thus "homepage page".', 'autodescription' ),
						esc_html__( 'Overwritten by page settings', 'autodescription' ),
					]
				)
			);
		}

		?>
		<h4><?php esc_html_e( 'Robots Meta Settings', 'autodescription' ); ?></h4>
		<?php

		$i_label = sprintf(
			/* translators: 1: Option label, 2: [?] option info note, 3: Optional warning */
			esc_html_x( '%1$s %2$s %3$s', 'robots setting', 'autodescription' ),
			$this->convert_markdown(
				/* translators: the backticks are Markdown! Preserve them as-is! */
				esc_html__( 'Apply `noindex` to the homepage?', 'autodescription' ),
				[ 'code' ]
			),
			$this->make_info(
				__( 'This tells search engines not to show this page in their search results.', 'autodescription' ),
				'https://support.google.com/webmasters/answer/93710',
				false
			),
			$noindex_post ? $checked_home : ''
		);

		$f_label = sprintf(
			/* translators: 1: Option label, 2: [?] option info note, 3: Optional warning */
			esc_html_x( '%1$s %2$s %3$s', 'robots setting', 'autodescription' ),
			$this->convert_markdown(
				/* translators: the backticks are Markdown! Preserve them as-is! */
				esc_html__( 'Apply `nofollow` to the homepage?', 'autodescription' ),
				[ 'code' ]
			),
			$this->make_info(
				__( 'This tells search engines not to follow links on this page.', 'autodescription' ),
				'https://support.google.com/webmasters/answer/96569',
				false
			),
			$nofollow_post ? $checked_home : ''
		);

		$a_label = sprintf(
			/* translators: 1: Option label, 2: [?] option info note, 3: Optional warning */
			esc_html_x( '%1$s %2$s %3$s', 'robots setting', 'autodescription' ),
			$this->convert_markdown(
				/* translators: the backticks are Markdown! Preserve them as-is! */
				esc_html__( 'Apply `noarchive` to the homepage?', 'autodescription' ),
				[ 'code' ]
			),
			$this->make_info(
				__( 'This tells search engines not to save a cached copy of this page.', 'autodescription' ),
				'https://support.google.com/webmasters/answer/79812',
				false
			),
			$noarchive_post ? $checked_home : ''
		);

		$this->attention_description( __( 'Warning: No public site should ever apply "noindex" or "nofollow" to the homepage.', 'autodescription' ) );

		$this->wrap_fields(
			[
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
			],
			true
		);

		if ( $this->has_page_on_front() ) {
			$this->description_noesc(
				$this->convert_markdown(
					sprintf(
						/* translators: %s = Homepage URL markdown */
						esc_html__( 'Note: These options may be overwritten by the [page settings](%s).', 'autodescription' ),
						esc_url( admin_url( 'post.php?post=' . $home_id . '&action=edit#tsf-inpost-box' ) )
					),
					[ 'a' ],
					[ 'a_internal' => false ]
				)
			);
		}
		?>

		<hr>

		<h4><?php esc_html_e( 'Homepage Pagination Robots Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( "If your homepage is paginated and outputs content that's also found elsewhere on the website, enabling this option may prevent duplicate content.", 'autodescription' ) );

		// Echo checkbox.
		$this->wrap_fields(
			$this->make_checkbox(
				'home_paged_noindex',
				$this->convert_markdown(
					/* translators: the backticks are Markdown! Preserve them as-is! */
					esc_html__( 'Apply `noindex` to every second or later page on the homepage?', 'autodescription' ),
					[ 'code' ]
				),
				'',
				false
			),
			true
		);
		break;

	default:
		break;
endswitch;
