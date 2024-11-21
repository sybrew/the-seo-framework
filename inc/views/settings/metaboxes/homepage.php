<?php
/**
 * @package The_SEO_Framework\Views\Admin\Metaboxes
 * @subpackage The_SEO_Framework\Admin\Settings
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) or die;

use \The_SEO_Framework\Admin\Settings\Layout\{
	Form,
	HTML,
	Input,
};
use \The_SEO_Framework\Data\Filter\Sanitize;
use \The_SEO_Framework\Helper\{
	Compatibility,
	Format\Markdown,
	Query,
};

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * The SEO Framework plugin
 * Copyright (C) 2016 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

// See _homepage_metabox et al.
[ $instance ] = $view_args;

$home_id        = Query::get_the_front_page_id();
$generator_args = [ 'id' => $home_id ];

switch ( $instance ) :
	case 'main':
		HTML::description( \__( 'These settings will take precedence over the settings set within the homepage edit screen, if any.', 'autodescription' ) );

		if ( Compatibility::get_active_conflicting_plugin_types()['multilingual'] ) {
			$_multilingual_warning = \esc_html__( 'A multilingual plugin has been detected and text entered below may not be translated.', 'autodescription' );
			if ( $home_id ) {
				$_multilingual_warning .= '<br>' . Markdown::convert(
					\sprintf(
						/* translators: %s = Homepage URL markdown */
						\esc_html__( 'Edit the fields on the [homepage](%s) instead.', 'autodescription' ),
						\esc_url( \admin_url( "post.php?post={$home_id}&action=edit#tsf-inpost-box" ) ),
					),
					[ 'a' ],
					[ 'a_internal' => false ] // opens in new tab.
				);
			}

			HTML::attention_noesc( $_multilingual_warning );
		}
		?>
		<hr>
		<?php
		$tabs = [
			'general'    => [
				'name'     => \__( 'General', 'autodescription' ),
				'callback' => [ Admin\Settings\Plugin::class, '_homepage_metabox_general_tab' ],
				'dashicon' => 'admin-generic',
			],
			'additions'  => [
				'name'     => \__( 'Additions', 'autodescription' ),
				'callback' => [ Admin\Settings\Plugin::class, '_homepage_metabox_additions_tab' ],
				'dashicon' => 'plus-alt2',
			],
			'social'     => [
				'name'     => \__( 'Social', 'autodescription' ),
				'callback' => [ Admin\Settings\Plugin::class, '_homepage_metabox_social_tab' ],
				'dashicon' => 'share',
			],
			'visibility' => [
				'name'     => \__( 'Visibility', 'autodescription' ),
				'callback' => [ Admin\Settings\Plugin::class, '_homepage_metabox_visibility_tab' ],
				'dashicon' => 'visibility',
			],
		];

		Admin\Settings\Plugin::nav_tab_wrapper(
			'homepage',
			/**
			 * @since 2.6.0
			 * @param array $tabs The default tabs.
			 */
			(array) \apply_filters( 'the_seo_framework_homepage_settings_tabs', $tabs )
		);
		break;

	case 'general':
		?>
		<p>
			<label for="<?php Input::field_id( 'homepage_title' ); ?>" class=tsf-toblock>
				<strong><?php \esc_html_e( 'Meta Title', 'autodescription' ); ?></strong>
				<?php
					echo ' ';
					HTML::make_info(
						\__( 'The meta title can be used to determine the title used on search engine result pages.', 'autodescription' ),
						'https://developers.google.com/search/docs/advanced/appearance/title-link',
					);
				?>
			</label>
		</p>
		<?php
		// Output these unconditionally, with inline CSS attached to allow reacting on settings.
		Form::output_character_counter_wrap( Input::get_field_id( 'homepage_title' ), (bool) Data\Plugin::get_option( 'display_character_counter' ) );
		Form::output_pixel_counter_wrap( Input::get_field_id( 'homepage_title' ), 'title', (bool) Data\Plugin::get_option( 'display_pixel_counter' ) );
		?>
		<p class=tsf-title-wrap>
			<input type=text name="<?php Input::field_name( 'homepage_title' ); ?>" class=large-text id="<?php Input::field_id( 'homepage_title' ); ?>" value="<?= \esc_html( Sanitize::metadata_content( Data\Plugin::get_option( 'homepage_title' ) ) ) ?>" autocomplete=off>
			<?php
			$post_meta_title = $home_id ? Sanitize::metadata_content( Data\Plugin\Post::get_meta_item( '_genesis_title', $home_id ) ) : '';

			Input::output_js_title_data(
				Input::get_field_id( 'homepage_title' ),
				[
					'state' => [
						'refTitleLocked'      => false, // This field is the mother of all references.
						'defaultTitle'        => \esc_html(
							coalesce_strlen( $post_meta_title ) ?? Meta\Title::get_bare_generated_title( $generator_args )
						),
						'_defaultTitleLocked' => (bool) \strlen( $post_meta_title ), // Underscored index because it's non-standard API.
						'addAdditions'        => Meta\Title\Conditions::use_branding( $generator_args ),
						'useSocialTagline'    => Meta\Title\Conditions::use_branding( $generator_args, true ),
						'additionValue'       => \esc_html( Meta\Title::get_addition_for_front_page() ),
						'additionPlacement'   => 'left' === Meta\Title::get_addition_location_for_front_page() ? 'before' : 'after',
					],
				],
			);
			?>
		</p>
		<?php
		HTML::description( \__( 'Note: It is best to only write the site or brand name here. Use additions to decorate the title instead.', 'autodescription' ) );

		if ( $home_id && \strlen( Data\Plugin\Post::get_meta_item( '_genesis_title', $home_id ) ) )
			HTML::description( \__( 'Note: The title placeholder is fetched from the Page SEO Settings on the homepage.', 'autodescription' ) );

		?>
		<hr>

		<p>
			<label for="<?php Input::field_id( 'homepage_description' ); ?>" class=tsf-toblock>
				<strong><?php \esc_html_e( 'Meta Description', 'autodescription' ); ?></strong>
				<?php
					echo ' ';
					HTML::make_info(
						\__( 'The meta description can be used to determine the text used under the title on search engine results pages.', 'autodescription' ),
						'https://developers.google.com/search/docs/advanced/appearance/snippet',
					);
				?>
			</label>
		</p>
		<?php
		// Output these unconditionally, with inline CSS attached to allow reacting on settings.
		Form::output_character_counter_wrap( Input::get_field_id( 'homepage_description' ), (bool) Data\Plugin::get_option( 'display_character_counter' ) );
		Form::output_pixel_counter_wrap( Input::get_field_id( 'homepage_description' ), 'description', (bool) Data\Plugin::get_option( 'display_pixel_counter' ) );
		?>
		<p>
			<textarea name="<?php Input::field_name( 'homepage_description' ); ?>" class=large-text id="<?php Input::field_id( 'homepage_description' ); ?>" rows=3 cols=70><?= \esc_attr( Data\Plugin::get_option( 'homepage_description' ) ) ?></textarea>
			<?php
			Input::output_js_description_data(
				Input::get_field_id( 'homepage_description' ),
				[
					'state' => [
						'defaultDescription' => \esc_html(
							coalesce_strlen(
								$home_id ? Sanitize::metadata_content( Data\Plugin\Post::get_meta_item( '_genesis_description', $home_id ) ) : ''
							)
							?? Meta\Description::get_generated_description( $generator_args )
						),
					],
				],
			);
			?>
		</p>
		<?php

		if ( $home_id && \strlen( Data\Plugin\Post::get_meta_item( '_genesis_description', $home_id ) ) ) {
			HTML::description(
				\__( 'Note: The description placeholder is fetched from the Page SEO Settings on the homepage.', 'autodescription' )
			);
		}
		break;

	case 'additions':
		// Fetches escaped title parts.
		$_example_title = \esc_html(
			Meta\Title::get_bare_custom_title( $generator_args )
			?: Meta\Title::get_bare_generated_title( $generator_args )
		);
		// On JS: The 'Untitled' title will disappear, this is intentional. On no-JS one will see 'Untitled'.
		// TODO: Deprecate no-JS support? WordPress doesn't function without JS since 5.0 anyway...
		$_example_blogname  = \esc_html(
			Meta\Title::get_addition_for_front_page()
			?: Meta\Title::get_untitled_title()
		);
		$_example_separator = \esc_html( Meta\Title::get_separator() );

		// TODO very readable.
		$example_left  = "<em><span class=tsf-custom-blogname-js><span class=tsf-custom-tagline-js>$_example_blogname</span><span class=tsf-sep-js> $_example_separator </span></span><span class=tsf-custom-title-js>$_example_title</span></em>";
		$example_right = "<em><span class=tsf-custom-title-js>$_example_title</span><span class=tsf-custom-blogname-js><span class=tsf-sep-js> $_example_separator </span><span class=tsf-custom-tagline-js>$_example_blogname</span></span></em>";

		?>
		<p>
			<label for="<?php Input::field_id( 'homepage_title_tagline' ); ?>" class=tsf-toblock>
				<strong><?php \esc_html_e( 'Meta Title Additions', 'autodescription' ); ?></strong>
			</label>
		</p>
		<p>
			<input type=text name="<?php Input::field_name( 'homepage_title_tagline' ); ?>" class=large-text id="<?php Input::field_id( 'homepage_title_tagline' ); ?>" placeholder="<?= \esc_html( Sanitize::metadata_content( Data\Blog::get_filtered_blog_description() ) ) ?>" value="<?= \esc_html( Sanitize::metadata_content( Data\Plugin::get_option( 'homepage_title_tagline' ) ) ) ?>" autocomplete=off>
		</p>

		<div class=tsf-title-tagline-toggle>
		<?php
			HTML::wrap_fields(
				Input::make_checkbox( [
					'id'    => 'homepage_tagline',
					'label' => \__( 'Add Meta Title Additions to the homepage title?', 'autodescription' ),
				] ),
				true,
			);
		?>
		</div>

		<hr>

		<fieldset>
			<legend><?php HTML::header_title( \__( 'Meta Title Additions Location', 'autodescription' ) ); ?></legend>

			<p id=tsf-home-title-location class=tsf-fields>
				<span class=tsf-toblock>
					<input type=radio name="<?php Input::field_name( 'home_title_location' ); ?>" id="<?php Input::field_id( 'home_title_location_left' ); ?>" value=left <?php \checked( Data\Plugin::get_option( 'home_title_location' ), 'left' ); ?>>
					<label for="<?php Input::field_id( 'home_title_location_left' ); ?>">
						<span><?php \esc_html_e( 'Left:', 'autodescription' ); ?></span>
						<?php
						// phpcs:ignore, WordPress.Security.EscapeOutput -- $example_left is already escaped.
						echo HTML::code_wrap_noesc( $example_left );
						?>
					</label>
				</span>
				<span class=tsf-toblock>
					<input type=radio name="<?php Input::field_name( 'home_title_location' ); ?>" id="<?php Input::field_id( 'home_title_location_right' ); ?>" value=right <?php \checked( Data\Plugin::get_option( 'home_title_location' ), 'right' ); ?>>
					<label for="<?php Input::field_id( 'home_title_location_right' ); ?>">
						<span><?php \esc_html_e( 'Right:', 'autodescription' ); ?></span>
						<?php
						// phpcs:ignore, WordPress.Security.EscapeOutput -- $example_right is already escaped.
						echo HTML::code_wrap_noesc( $example_right );
						?>
					</label>
				</span>
			</p>
		</fieldset>
		<?php
		break;

	case 'social':
		/* translators: %s = default option value */
		$_default_i18n     = \__( 'Default (%s)', 'autodescription' );
		$tw_suported_cards = Meta\Twitter::get_supported_cards();

		$custom_title    = '';
		$custom_desc     = '';
		$custom_og_title = '';
		$custom_og_desc  = '';
		$custom_tw_title = '';
		$custom_tw_desc  = '';
		$custom_tw_card  = '';
		$custom_image    = '';

		// Gets custom fields from page.
		if ( $home_id ) {
			$custom_title = Sanitize::metadata_content( Data\Plugin\Post::get_meta_item( '_genesis_title', $home_id ) );
			$custom_desc  = Sanitize::metadata_content( Data\Plugin\Post::get_meta_item( '_genesis_description', $home_id ) );

			$custom_og_title = Sanitize::metadata_content( Data\Plugin\Post::get_meta_item( '_open_graph_title', $home_id ) );
			$custom_og_desc  = Sanitize::metadata_content( Data\Plugin\Post::get_meta_item( '_open_graph_description', $home_id ) );
			$custom_tw_title = Sanitize::metadata_content( Data\Plugin\Post::get_meta_item( '_twitter_title', $home_id ) );
			$custom_tw_desc  = Sanitize::metadata_content( Data\Plugin\Post::get_meta_item( '_twitter_description', $home_id ) );

			$custom_tw_card  = Data\Plugin\Post::get_meta_item( '_tsf_twitter_card_type', $home_id );
			$tw_card_default = \in_array( $custom_tw_card, $tw_suported_cards, true )
				? $custom_tw_card
				: Meta\Twitter::get_generated_card_type( $generator_args );

			$custom_image      = \sanitize_url( Data\Plugin\Post::get_meta_item( '_social_image_url', $home_id ) );
			$image_placeholder = $custom_image ?: Meta\Image::get_first_generated_image_url( $generator_args, 'social' );
		} else {
			$tw_card_default   = Meta\Twitter::get_generated_card_type( $generator_args );
			$image_placeholder = Meta\Image::get_first_generated_image_url( $generator_args, 'social' );
		}

		Input::output_js_social_data(
			'homepage_social_settings',
			[
				'og' => [
					'state' => [
						'defaultTitle' => \esc_html(
							coalesce_strlen( $custom_og_title )
							?? coalesce_strlen( $custom_title )
							?? Meta\Open_Graph::get_generated_title( $generator_args )
						),
						'addAdditions' => Meta\Title\Conditions::use_branding( $generator_args, 'og' ),
						'defaultDesc'  => \esc_html(
							coalesce_strlen( $custom_og_desc )
							?? coalesce_strlen( $custom_desc )
							?? Meta\Open_Graph::get_generated_description( $generator_args )
						),
						'titlePhLock'  => (bool) \strlen( $custom_og_title ),
						'descPhLock'   => (bool) \strlen( $custom_og_desc ),
					],
				],
				'tw' => [
					'state' => [
						'defaultTitle' => \esc_html(
							coalesce_strlen( $custom_tw_title )
							?? coalesce_strlen( $custom_og_title )
							?? coalesce_strlen( $custom_title )
							?? Meta\Twitter::get_generated_title( $generator_args )
						),
						'addAdditions' => Meta\Title\Conditions::use_branding( $generator_args, 'twitter' ),
						'defaultDesc'  => \esc_html(
							coalesce_strlen( $custom_tw_desc )
							?? coalesce_strlen( $custom_og_desc )
							?? coalesce_strlen( $custom_desc )
							?? Meta\Twitter::get_generated_description( $generator_args )
						),
						'titlePhLock'  => (bool) \strlen( $custom_tw_title ),
						'descPhLock'   => (bool) \strlen( $custom_tw_desc ),
					],
				],
			],
		);

		?>
		<p>
			<label for="<?php Input::field_id( 'homepage_og_title' ); ?>" class=tsf-toblock>
				<strong><?php \esc_html_e( 'Open Graph Title', 'autodescription' ); ?></strong>
			</label>
		</p>
		<?php
		// Output this unconditionally, with inline CSS attached to allow reacting on settings.
		Form::output_character_counter_wrap( Input::get_field_id( 'homepage_og_title' ), (bool) Data\Plugin::get_option( 'display_character_counter' ) );
		?>
		<p>
			<input type=text name="<?php Input::field_name( 'homepage_og_title' ); ?>" class=large-text id="<?php Input::field_id( 'homepage_og_title' ); ?>" value="<?= \esc_html( Sanitize::metadata_content( Data\Plugin::get_option( 'homepage_og_title' ) ) ) ?>" autocomplete=off data-tsf-social-group=homepage_social_settings data-tsf-social-type=ogTitle>
		</p>
		<?php
		if ( \strlen( $custom_og_title ) ) {
			HTML::description(
				\__( 'Note: The title placeholder is fetched from the Page SEO Settings on the homepage.', 'autodescription' )
			);
		}
		?>

		<p>
			<label for="<?php Input::field_id( 'homepage_og_description' ); ?>" class=tsf-toblock>
				<strong><?php \esc_html_e( 'Open Graph Description', 'autodescription' ); ?></strong>
			</label>
		</p>
		<?php
		// Output this unconditionally, with inline CSS attached to allow reacting on settings.
		Form::output_character_counter_wrap( Input::get_field_id( 'homepage_og_description' ), (bool) Data\Plugin::get_option( 'display_character_counter' ) );
		?>
		<p>
			<textarea name="<?php Input::field_name( 'homepage_og_description' ); ?>" class=large-text id="<?php Input::field_id( 'homepage_og_description' ); ?>" rows=3 cols=70 autocomplete=off data-tsf-social-group=homepage_social_settings data-tsf-social-type=ogDesc><?= \esc_attr( Data\Plugin::get_option( 'homepage_og_description' ) ) ?></textarea>
		</p>
		<?php
		if ( \strlen( $custom_og_desc ) ) {
			HTML::description(
				\__( 'Note: The description placeholder is fetched from the Page SEO Settings on the homepage.', 'autodescription' )
			);
		}
		?>
		<hr>

		<p>
			<label for="<?php Input::field_id( 'homepage_twitter_title' ); ?>" class=tsf-toblock>
				<strong><?php \esc_html_e( 'Twitter Title', 'autodescription' ); ?></strong>
			</label>
		</p>
		<?php
		// Output this unconditionally, with inline CSS attached to allow reacting on settings.
		Form::output_character_counter_wrap( Input::get_field_id( 'homepage_twitter_title' ), (bool) Data\Plugin::get_option( 'display_character_counter' ) );
		?>
		<p>
			<input type=text name="<?php Input::field_name( 'homepage_twitter_title' ); ?>" class=large-text id="<?php Input::field_id( 'homepage_twitter_title' ); ?>" value="<?= \esc_html( Sanitize::metadata_content( Data\Plugin::get_option( 'homepage_twitter_title' ) ) ) ?>" autocomplete=off data-tsf-social-group=homepage_social_settings data-tsf-social-type=twTitle>
		</p>
		<?php
		if ( \strlen( $custom_og_title ) || \strlen( $custom_tw_title ) ) {
			HTML::description(
				\__( 'Note: The title placeholder is fetched from the Page SEO Settings on the homepage.', 'autodescription' )
			);
		}
		?>

		<p>
			<label for="<?php Input::field_id( 'homepage_twitter_description' ); ?>" class=tsf-toblock>
				<strong><?php \esc_html_e( 'Twitter Description', 'autodescription' ); ?></strong>
			</label>
		</p>
		<?php
		// Output this unconditionally, with inline CSS attached to allow reacting on settings.
		Form::output_character_counter_wrap( Input::get_field_id( 'homepage_twitter_description' ), (bool) Data\Plugin::get_option( 'display_character_counter' ) );
		?>
		<p>
			<textarea name="<?php Input::field_name( 'homepage_twitter_description' ); ?>" class=large-text id="<?php Input::field_id( 'homepage_twitter_description' ); ?>" rows=3 cols=70 autocomplete=off data-tsf-social-group=homepage_social_settings data-tsf-social-type=twDesc><?= \esc_attr( Data\Plugin::get_option( 'homepage_twitter_description' ) ) ?></textarea>
		</p>
		<?php
		if ( \strlen( $custom_og_desc ) || \strlen( $custom_tw_desc ) ) {
			HTML::description(
				\__( 'Note: The description placeholder is fetched from the Page SEO Settings on the homepage.', 'autodescription' )
			);
		}
		?>

		<p>
			<label for="<?php Input::field_id( 'homepage_twitter_card_type' ); ?>" class=tsf-toblock>
				<strong><?php \esc_html_e( 'Twitter Card Type', 'autodescription' ); ?></strong>
				<?php
				HTML::make_info(
					\__( 'The Twitter Card type is used to determine whether an image appears on the side or as a large cover. This affects X, but also other social platforms like Discord.', 'autodescription' ),
					'https://developer.twitter.com/en/docs/twitter-for-websites/cards/overview/abouts-cards',
				);
				?>
			</label>
		</p>
		<p>
			<?php
			// phpcs:disable, WordPress.Security.EscapeOutput -- make_single_select_form() escapes.
			echo Form::make_single_select_form( [
				'id'       => Input::get_field_id( 'homepage_twitter_card_type' ),
				'class'    => 'tsf-select-block',
				'name'     => Input::get_field_name( 'homepage_twitter_card_type' ),
				'label'    => '',
				'options'  => array_merge(
					[ '' => \sprintf( $_default_i18n, $tw_card_default ) ],
					array_combine( $tw_suported_cards, $tw_suported_cards ),
				),
				'selected' => Data\Plugin::get_option( 'homepage_twitter_card_type' ),
				'data'     => [
					'defaultI18n'   => $_default_i18n,
					'defaultValue'  => $tw_card_default,
					'defaultLocked' => (bool) $custom_tw_card,
				],
			] );
			// phpcs:enable, WordPress.Security.EscapeOutput
			?>
		</p>
		<?php
		if ( $custom_tw_card ) {
			HTML::description(
				\__( 'Note: The default Twitter Card Type is fetched from the Page SEO Settings on the homepage.', 'autodescription' )
			);
		}
		?>
		<hr>
		<?php
		HTML::header_title( \__( 'Social Image Settings', 'autodescription' ) );
		HTML::description( \__( 'A social image can be displayed when your homepage is shared. It is a great way to grab attention.', 'autodescription' ) );
		?>
		<p>
			<label for=tsf_homepage_socialimage-url>
				<strong><?php \esc_html_e( 'Social Image URL', 'autodescription' ); ?></strong>
				<?php
				HTML::make_info(
					\__( "The social image URL can be used by search engines and social networks alike. It's best to use an image with a 1.91:1 aspect ratio that is at least 1200px wide for universal support.", 'autodescription' ),
					'https://developers.facebook.com/docs/sharing/best-practices#images',
				);
				?>
			</label>
		</p>
		<p>
			<input class=large-text type=url name="<?php Input::field_name( 'homepage_social_image_url' ); ?>" id=tsf_homepage_socialimage-url placeholder="<?= \esc_url( $image_placeholder ) ?>" value="<?= \esc_url( Data\Plugin::get_option( 'homepage_social_image_url' ) ) ?>">
			<input type=hidden name="<?php Input::field_name( 'homepage_social_image_id' ); ?>" id=tsf_homepage_socialimage-id value="<?= \absint( Data\Plugin::get_option( 'homepage_social_image_id' ) ) ?>" disabled class=tsf-enable-media-if-js>
		</p>
		<p class=hide-if-no-tsf-js>
			<?php
			// phpcs:disable, WordPress.Security.EscapeOutput -- get_image_uploader_form escapes. (phpcs breaks here, so we use disable)
			echo Form::get_image_uploader_form( [ 'id' => 'tsf_homepage_socialimage' ] );
			// phpcs:enable, WordPress.Security.EscapeOutput
			?>
		</p>
		<?php
		if ( $custom_image ) {
			HTML::description(
				\__( 'Note: The image placeholder is fetched from the Page SEO Settings on the homepage.', 'autodescription' )
			);
		}
		break;

	case 'visibility':
		if ( $home_id ) {
			$canonical_post = Data\Plugin\Post::get_meta_item( '_genesis_canonical_uri', $home_id );
			$redirect_post  = Data\Plugin\Post::get_meta_item( 'redirect', $home_id );

			$noindex_post   = Data\Plugin\Post::get_meta_item( '_genesis_noindex', $home_id );
			$nofollow_post  = Data\Plugin\Post::get_meta_item( '_genesis_nofollow', $home_id );
			$noarchive_post = Data\Plugin\Post::get_meta_item( '_genesis_noarchive', $home_id );

			$is_protected = Data\Post::is_protected( $home_id );
			$home_is_page = true;
		} else {
			$canonical_post = '';
			$redirect_post  = '';

			$noindex_post   = 0;
			$nofollow_post  = 0;
			$noarchive_post = 0;

			$is_protected = false;
			$home_is_page = false;
		}

		$default_canonical = $canonical_post ?: Meta\URI::get_generated_url( $generator_args );

		?>
		<p>
			<label for="<?php Input::field_id( 'homepage_canonical' ); ?>" class=tsf-toblock>
				<strong><?php \esc_html_e( 'Canonical URL', 'autodescription' ); ?></strong>
				<?php
					echo ' ';
					HTML::make_info(
						\__( 'This urges search engines to go to the outputted URL.', 'autodescription' ),
						'https://developers.google.com/search/docs/advanced/crawling/consolidate-duplicate-urls',
					);
				?>
				<?php
				Input::output_js_canonical_data(
					Input::get_field_id( 'homepage_canonical' ),
					[
						'state' => [
							'refCanonicalLocked' => false, // This is the motherfield.
							'defaultCanonical'   => \esc_url( $default_canonical ),
							'preferredScheme'    => Meta\URI\Utils::get_preferred_url_scheme(),
							'urlStructure'       => Meta\URI\Utils::get_url_permastruct( $generator_args ),
							'noindexQubit'       => Sanitize::qubit( $noindex_post ),
							'isProtected'        => $is_protected,
							'isPage'             => $home_is_page,
						],
					],
				);
				?>
			</label>
		</p>
		<p>
			<input type=url name="<?php Input::field_name( 'homepage_canonical' ); ?>" class=large-text id="<?php Input::field_id( 'homepage_canonical' ); ?>" placeholder="<?= \esc_url( $default_canonical ) ?>" value="<?= \esc_url( Data\Plugin::get_option( 'homepage_canonical' ) ) ?>" autocomplete=off>
		</p>

		<hr>
		<?php

		$checked_home = '';
		/**
		 * Shows user that the setting is set on the homepage.
		 * Adds starting - with space to maintain readability.
		 */
		if ( $noindex_post || $nofollow_post || $noarchive_post ) {
			$checked_home = \sprintf(
				'- %s',
				\sprintf(
					'<a href="%s" title="%s" target=_blank class=attention>%s</a>',
					\esc_url( \admin_url( "post.php?post=$home_id&action=edit#tsf-inpost-box" ) ),
					\esc_attr_x( 'Edit homepage page settings', 'Bear with me: the homepage can be edited globally, or via its page. Thus "homepage page".', 'autodescription' ),
					\esc_html__( 'Overwritten by page settings', 'autodescription' ),
				)
			);
		}

		HTML::header_title( \__( 'Robots Meta Settings', 'autodescription' ) );

		$i_label = \sprintf(
			/* translators: 1: Option label, 2: [?] option info note, 3: Optional warning */
			\esc_html_x( '%1$s %2$s %3$s', 'robots setting', 'autodescription' ),
			Markdown::convert(
				/* translators: the backticks are Markdown! Preserve them as-is! */
				\esc_html__( 'Apply `noindex` to the homepage?', 'autodescription' ),
				[ 'code' ],
			),
			HTML::make_info(
				\__( 'This tells search engines not to show this page in their search results.', 'autodescription' ),
				'https://developers.google.com/search/docs/advanced/crawling/block-indexing',
				false,
			),
			$noindex_post ? $checked_home : '',
		);

		$f_label = \sprintf(
			/* translators: 1: Option label, 2: [?] option info note, 3: Optional warning */
			\esc_html_x( '%1$s %2$s %3$s', 'robots setting', 'autodescription' ),
			Markdown::convert(
				/* translators: the backticks are Markdown! Preserve them as-is! */
				\esc_html__( 'Apply `nofollow` to the homepage?', 'autodescription' ),
				[ 'code' ],
			),
			HTML::make_info(
				\__( 'This tells search engines not to follow links on this page.', 'autodescription' ),
				'https://developers.google.com/search/docs/advanced/guidelines/qualify-outbound-links',
				false,
			),
			$nofollow_post ? $checked_home : '',
		);

		$a_label = \sprintf(
			/* translators: 1: Option label, 2: [?] option info note, 3: Optional warning */
			\esc_html_x( '%1$s %2$s %3$s', 'robots setting', 'autodescription' ),
			Markdown::convert(
				/* translators: the backticks are Markdown! Preserve them as-is! */
				\esc_html__( 'Apply `noarchive` to the homepage?', 'autodescription' ),
				[ 'code' ],
			),
			HTML::make_info(
				\__( 'This tells search engines not to save a cached copy of this page.', 'autodescription' ),
				'https://developers.google.com/search/docs/advanced/robots/robots_meta_tag#directives',
				false,
			),
			$noarchive_post ? $checked_home : '',
		);

		HTML::attention_description( \__( 'Warning: No public website should ever apply "noindex" or "nofollow" to the homepage.', 'autodescription' ) );

		HTML::wrap_fields(
			[
				Input::make_checkbox( [
					'id'     => 'homepage_noindex',
					'label'  => $i_label,
					'escape' => false,
				] ),
				Input::make_checkbox( [
					'id'     => 'homepage_nofollow',
					'label'  => $f_label,
					'escape' => false,
				] ),
				Input::make_checkbox( [
					'id'     => 'homepage_noarchive',
					'label'  => $a_label,
					'escape' => false,
				] ),
			],
			true,
		);

		if ( $home_id ) {
			HTML::description_noesc(
				Markdown::convert(
					\sprintf(
						/* translators: %s = Homepage URL markdown */
						\esc_html__( 'Note: These options may be overwritten by the [page settings](%s).', 'autodescription' ),
						\esc_url( \admin_url( "post.php?post=$home_id&action=edit#tsf-inpost-box" ) ),
					),
					[ 'a' ],
					[ 'a_internal' => false ],
				),
			);
		}
		?>

		<hr>
		<?php
		HTML::header_title( \__( 'Homepage Pagination Robots Settings', 'autodescription' ) );
		HTML::description( \__( "If your homepage is paginated and outputs content that's also found elsewhere on the website, enabling this option may prevent duplicate content.", 'autodescription' ) );

		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'     => 'home_paged_noindex',
				'label'  => Markdown::convert(
					/* translators: the backticks are Markdown! Preserve them as-is! */
					\esc_html__( 'Apply `noindex` to every second or later page on the homepage?', 'autodescription' ),
					[ 'code' ],
				),
				'escape' => false,
			] ),
			true,
		);
		?>
		<hr>

		<p>
			<label for="<?php Input::field_id( 'homepage_redirect' ); ?>" class=tsf-toblock>
				<strong><?php \esc_html_e( '301 Redirect URL', 'autodescription' ); ?></strong>
				<?php
					echo ' ';
					HTML::make_info(
						\__( 'This will force visitors to go to another URL.', 'autodescription' ),
						'https://developers.google.com/search/docs/crawling-indexing/301-redirects',
					);
				?>
			</label>
		</p>
		<p>
			<input type=url name="<?php Input::field_name( 'homepage_redirect' ); ?>" class=large-text id="<?php Input::field_id( 'homepage_redirect' ); ?>" placeholder="<?= \esc_url( $redirect_post ) ?>" value="<?= \esc_url( Data\Plugin::get_option( 'homepage_redirect' ) ) ?>" autocomplete=off>
		</p>
		<?php
endswitch;
