<?php
/**
 * @package The_SEO_Framework\Views\Admin\Metaboxes
 * @subpackage The_SEO_Framework\Admin\Settings
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

use The_SEO_Framework\Bridges\SeoSettings,
	The_SEO_Framework\Interpreters\HTML,
	The_SEO_Framework\Interpreters\Form;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and tsf()->_verify_include_secret( $_secret ) or die;

// Fetch the required instance within this file.
switch ( $this->get_view_instance( 'post_type_archive', $instance ) ) :
	case 'post_type_archive_main':
		$_settings_class = SeoSettings::class;

		?>
		<div id=tsf-post-type-archive-selector-wrap class="tsf-fields tsf-hide-if-no-js"></div>
		<?php

		foreach ( $this->get_public_post_type_archives() as $post_type ) {
			$_generator_args = [
				'id'        => '',
				'taxonomy'  => '',
				'post_type' => $post_type,
			];

			$tabs = [
				'general'    => [
					'name'     => __( 'General', 'autodescription' ),
					'callback' => "$_settings_class::_post_type_archive_metabox_general_tab",
					'dashicon' => 'admin-generic',
					'args'     => [
						'post_type'       => $post_type,
						'_generator_args' => $_generator_args,
						'_option_map'     => [ 'pta', $post_type ],
					],
				],
				'social'     => [
					'name'     => __( 'Social', 'autodescription' ),
					'callback' => "$_settings_class::_post_type_archive_metabox_social_tab",
					'dashicon' => 'share',
					'args'     => [
						'post_type'       => $post_type,
						'_generator_args' => $_generator_args,
						'_option_map'     => [ 'pta', $post_type ],
					],
				],
				'visibility' => [
					'name'     => __( 'Visibility', 'autodescription' ),
					'callback' => "$_settings_class::_post_type_archive_metabox_visibility_tab",
					'dashicon' => 'visibility',
					'args'     => [
						'post_type'       => $post_type,
						'_generator_args' => $_generator_args,
						'_option_map'     => [ 'pta', $post_type ],
					],
				],
			];

			printf(
				'<div class=tsf-post-type-archive-wrap %s>',
				// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- This escapes.
				HTML::make_data_attributes( [ 'post_type' => $post_type ] )
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
					<?php
					// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- it is.
					echo Form::get_header_title(
						$this->convert_markdown(
							vsprintf(
								/* translators: 1 = Post Type Archive name, Markdown. 2 = Post Type code, also markdown! 3 = Post Type Archive link, also markdown. Preserve the Markdown as-is! */
								esc_html__( 'Archive of %1$s &ndash; `%2$s` ([View archive](%3$s))', 'autodescription' ),
								[
									esc_html( $this->get_generated_post_type_archive_title( $post_type ) ),
									esc_html( $post_type ),
									esc_url( $this->get_post_type_archive_canonical_url( $post_type ), [ 'https', 'http' ] ),
								]
							),
							[ 'code', 'a' ],
							[ 'a_internal' => false ] // open in new window.
						)
					);
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

			<hr>
			<hr class=tsf-hide-if-js>
			<?php
		}
		break;

	case 'post_type_archive_general_tab':
		?>
		<p>
			<label for="<?php Form::field_id( 'doctitle', $post_type ); ?>" class=tsf-toblock>
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
		Form::output_character_counter_wrap( Form::get_field_id( 'doctitle', $_option_map ), (bool) $this->get_option( 'display_character_counter' ) );
		Form::output_pixel_counter_wrap( Form::get_field_id( 'doctitle', $_option_map ), 'title', (bool) $this->get_option( 'display_pixel_counter' ) );
		?>
		<p class=tsf-title-wrap>
			<input type="text" name="<?php Form::field_name( 'doctitle', $_option_map ); ?>" class="large-text" id="<?php Form::field_id( 'doctitle', $_option_map ); ?>" value="<?php echo $this->esc_attr_preserve_amp( $this->get_post_type_archive_meta_item( 'doctitle', $post_type ) ); ?>" autocomplete=off />
			<?php
			$this->output_js_title_data(
				Form::get_field_id( 'doctitle', $_option_map ),
				[
					'state' => [
						'refTitleLocked'    => false,
						'defaultTitle'      => $this->get_filtered_raw_generated_title( $_generator_args ),
						'addAdditions'      => $this->use_title_branding( $_generator_args ),
						'useSocialTagline'  => $this->use_title_branding( $_generator_args, true ),
						'additionPlacement' => 'left' === $this->get_title_seplocation() ? 'before' : 'after',
						'hasLegacy'         => false,
					],
				]
			);
			?>
		</p>
		<?php
		break;
	case 'post_type_archive_social_tab':
		break;
	case 'post_type_archive_visibility_tab':
		break;
endswitch;
