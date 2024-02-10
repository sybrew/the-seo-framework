<?php
/**
 * @package The_SEO_Framework\Views\Post
 * @subpackage The_SEO_Framework\Admin\Post
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) or die;

use const \The_SEO_Framework\{
	ROBOTS_IGNORE_SETTINGS,
	ROBOTS_IGNORE_PROTECTION,
};

use function \The_SEO_Framework\coalesce_strlen;

use \The_SEO_Framework\{
	Data\Filter\Sanitize,
	Helper\Post_Type,
	Helper\Query,
};
use \The_SEO_Framework\Admin\Settings\Layout\{
	Form,
	HTML,
	Input,
};

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * The SEO Framework plugin
 * Copyright (C) 2017 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// See meta_box et al.
[ $instance ] = $view_args;

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

// Setup default vars.
$post_id = Query::get_the_real_id(); // We also have access to object $post at the main call...
$meta    = Data\Plugin\Post::get_meta( $post_id );

$generator_args = [ 'id' => $post_id ];

$is_static_front_page = Query::is_static_front_page( $post_id );

switch ( $instance ) :
	case 'main':
		$default_tabs = [
			'general'    => [
				'name'     => \__( 'General', 'autodescription' ),
				'callback' => [ Admin\Settings\Post::class, 'general_tab' ],
				'dashicon' => 'admin-generic',
			],
			'social'     => [
				'name'     => \__( 'Social', 'autodescription' ),
				'callback' => [ Admin\Settings\Post::class, 'social_tab' ],
				'dashicon' => 'share',
			],
			'visibility' => [
				'name'     => \__( 'Visibility', 'autodescription' ),
				'callback' => [ Admin\Settings\Post::class, 'visibility_tab' ],
				'dashicon' => 'visibility',
			],
		];

		/**
		 * Allows for altering the inpost SEO settings meta box tabs.
		 *
		 * @since 2.9.0
		 * @since 4.0.0 Removed the second parameter (post type label)
		 *
		 * @param array $default_tabs The default tabs.
		 * @param null  $depr         The post type label. Deprecated.
		 */
		$tabs = (array) \apply_filters( 'the_seo_framework_inpost_settings_tabs', $default_tabs, null );

		echo '<div class="tsf-flex tsf-flex-inside-wrap">';
		Admin\Settings\Post::flex_nav_tab_wrapper( 'inpost', $tabs );
		echo '</div>';
		break;

	case 'general':
		if ( Data\Plugin::get_option( 'display_seo_bar_metabox' ) ) {
			?>
			<div class="tsf-flex-setting tsf-flex" id=tsf-doing-it-right-wrap>
				<div class="tsf-flex-setting-label tsf-flex">
					<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
						<div class="tsf-flex-setting-label-item tsf-flex">
							<div><strong><?php \esc_html_e( 'Doing it Right', 'autodescription' ); ?></strong></div>
							<div><span class=tsf-ajax></span></div>
						</div>
					</div>
				</div>
				<div class="tsf-flex-setting-input tsf-flex">
					<?php
					// phpcs:ignore, WordPress.Security.EscapeOutput -- generate_bar() escapes.
					echo Admin\SEOBar\Builder::generate_bar( $generator_args );
					?>
				</div>
			</div>
			<?php
		}

		if ( $is_static_front_page ) {
			$_has_home_title = (bool) \strlen( Data\Plugin::get_option( 'homepage_title' ) );
			$_has_home_desc  = (bool) \strlen( Data\Plugin::get_option( 'homepage_description' ) );

			// When the homepage title is set, we can safely get the custom field.
			$default_title     = $_has_home_title
				? Meta\Title::get_custom_title( $generator_args )
				: Meta\Title::get_bare_generated_title( $generator_args );
			$title_ref_locked  = $_has_home_title;
			$title_additions   = Meta\Title::get_addition_for_front_page();
			$title_seplocation = Meta\Title::get_addition_location_for_front_page();

			// When the homepage description is set, we can safely get the custom field.
			$default_description    = $_has_home_desc
				? Meta\Description::get_custom_description( $generator_args )
				: Meta\Description::get_generated_description( $generator_args );
			$description_ref_locked = $_has_home_desc;
		} else {
			$default_title     = Meta\Title::get_bare_generated_title( $generator_args );
			$title_ref_locked  = false;
			$title_additions   = Meta\Title::get_addition();
			$title_seplocation = Meta\Title::get_addition_location();

			$default_description    = Meta\Description::get_generated_description( $generator_args );
			$description_ref_locked = false;
		}

		?>
		<div class="tsf-flex-setting tsf-flex">
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for=autodescription_title class="tsf-flex-setting-label-item tsf-flex">
						<div><strong><?php \esc_html_e( 'Meta Title', 'autodescription' ); ?></strong></div>
						<div>
							<?php
							HTML::make_info(
								\__( 'The meta title can be used to determine the title used on search engine result pages.', 'autodescription' ),
								'https://developers.google.com/search/docs/advanced/appearance/title-link',
							);
							?>
						</div>
					</label>
					<?php
					Data\Plugin::get_option( 'display_character_counter' )
						and Form::output_character_counter_wrap( 'autodescription_title' );
					Data\Plugin::get_option( 'display_pixel_counter' )
						and Form::output_pixel_counter_wrap( 'autodescription_title', 'title' );
					?>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<div class=tsf-title-wrap>
					<input class=large-text type=text name="autodescription[_genesis_title]" id=autodescription_title value="<?= \esc_html( Sanitize::metadata_content( $meta['_genesis_title'] ) ) ?>" autocomplete=off data-form-type=other />
					<?php
					Input::output_js_title_data(
						'autodescription_title',
						[
							'state' => [
								'refTitleLocked'    => $title_ref_locked,
								'defaultTitle'      => \esc_html( $default_title ),
								'addAdditions'      => Meta\Title\Conditions::use_branding( $generator_args ),
								'useSocialTagline'  => Meta\Title\Conditions::use_branding( $generator_args, true ),
								'additionValue'     => \esc_html( $title_additions ),
								'additionPlacement' => 'left' === $title_seplocation ? 'before' : 'after',
							],
						],
					);
					?>
				</div>

				<div class=tsf-checkbox-wrapper>
					<label for=autodescription_title_no_blogname>
						<?php
						$title_no_blogname_value = $meta['_tsf_title_no_blogname'];
						if ( $is_static_front_page ) {
							// Disable the input, and hide the previously stored value.
							?>
							<input type=checkbox id=autodescription_title_no_blogname value=1 <?php \checked( $title_no_blogname_value ); ?> disabled />
							<input type=hidden name="autodescription[_tsf_title_no_blogname]" value="<?= (int) $title_no_blogname_value ?>" />
							<?php
							\esc_html_e( 'Remove the site title?', 'autodescription' );
							echo ' ';
							HTML::make_info( \__( 'For the homepage, this option must be managed on the SEO Settings page.', 'autodescription' ) );
						} else {
							?>
							<input type=checkbox name="autodescription[_tsf_title_no_blogname]" id=autodescription_title_no_blogname value=1 <?php \checked( $title_no_blogname_value ); ?> />
							<?php
							\esc_html_e( 'Remove the site title?', 'autodescription' );
							echo ' ';
							HTML::make_info( \__( 'Use this when you want to rearrange the title parts manually.', 'autodescription' ) );
						}
						?>
					</label>
				</div>
			</div>
		</div>

		<div class="tsf-flex-setting tsf-flex">
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for=autodescription_description class="tsf-flex-setting-label-item tsf-flex">
						<div><strong><?php \esc_html_e( 'Meta Description', 'autodescription' ); ?></strong></div>
						<div>
							<?php
							HTML::make_info(
								\__( 'The meta description can be used to determine the text used under the title on search engine results pages.', 'autodescription' ),
								'https://developers.google.com/search/docs/advanced/appearance/snippet',
							);
							?>
						</div>
					</label>
					<?php
					Data\Plugin::get_option( 'display_character_counter' )
						and Form::output_character_counter_wrap( 'autodescription_description' );
					Data\Plugin::get_option( 'display_pixel_counter' )
						and Form::output_pixel_counter_wrap( 'autodescription_description', 'description' );
					?>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<textarea class=large-text name="autodescription[_genesis_description]" id=autodescription_description rows=4 cols=4 autocomplete=off><?= \esc_html( Sanitize::metadata_content( $meta['_genesis_description'] ) ) ?></textarea>
				<?php
				Input::output_js_description_data(
					'autodescription_description',
					[
						'state' => [
							'defaultDescription'   => \esc_html( Sanitize::metadata_content( $default_description ) ),
							'refDescriptionLocked' => $description_ref_locked,
						],
					],
				);
				?>
			</div>
		</div>
		<?php
		break;

	case 'social':
		// Yes, this is hacky, but we don't want to lose the user's old input.
		$show_og = (bool) Data\Plugin::get_option( 'og_tags' );
		$show_tw = (bool) Data\Plugin::get_option( 'twitter_tags' );

		if ( $is_static_front_page ) {
			$_social_title       = [
				'og' => coalesce_strlen( Data\Plugin::get_option( 'homepage_og_title' ) )
						?? coalesce_strlen( Data\Plugin::get_option( 'homepage_title' ) )
						?? Meta\Open_Graph::get_generated_title( $generator_args ),
				'tw' => coalesce_strlen( Data\Plugin::get_option( 'homepage_twitter_title' ) )
						?? coalesce_strlen( Data\Plugin::get_option( 'homepage_og_title' ) )
						?? coalesce_strlen( Data\Plugin::get_option( 'homepage_title' ) )
						?? Meta\Twitter::get_generated_title( $generator_args ),
			];
			$_social_description = [
				'og' => coalesce_strlen( Data\Plugin::get_option( 'homepage_og_description' ) )
						?? coalesce_strlen( Data\Plugin::get_option( 'homepage_description' ) )
						?? Meta\Open_Graph::get_generated_description( $generator_args ),
				'tw' => coalesce_strlen( Data\Plugin::get_option( 'homepage_twitter_description' ) )
						?? coalesce_strlen( Data\Plugin::get_option( 'homepage_og_description' ) )
						?? coalesce_strlen( Data\Plugin::get_option( 'homepage_description' ) )
						?? Meta\Twitter::get_generated_description( $generator_args ),
			];

			$_twitter_card = Data\Plugin::get_option( 'homepage_twitter_card_type' )
						  ?: Meta\Twitter::get_generated_card_type( $generator_args );
		} else {
			$_social_title       = [
				'og' => Meta\Open_Graph::get_generated_title( $generator_args ),
				'tw' => Meta\Twitter::get_generated_title( $generator_args ),
			];
			$_social_description = [
				'og' => Meta\Open_Graph::get_generated_description( $generator_args ),
				'tw' => Meta\Twitter::get_generated_description( $generator_args ),
			];

			$_twitter_card = Meta\Twitter::get_generated_card_type( $generator_args );
		}

		Input::output_js_social_data(
			'autodescription_social_singular',
			[
				'og' => [
					'state' => [
						'defaultTitle' => \esc_html( Sanitize::metadata_content( $_social_title['og'] ) ),
						'addAdditions' => Meta\Title\Conditions::use_branding( $generator_args, 'og' ),
						'defaultDesc'  => \esc_html( Sanitize::metadata_content( $_social_description['og'] ) ),
						'titleLock'    => $is_static_front_page && \strlen( Data\Plugin::get_option( 'homepage_og_title' ) ),
						'descLock'     => $is_static_front_page && \strlen( Data\Plugin::get_option( 'homepage_og_description' ) ),
					],
				],
				'tw' => [
					'state' => [
						'defaultTitle' => \esc_html( Sanitize::metadata_content( $_social_title['tw'] ) ),
						'addAdditions' => Meta\Title\Conditions::use_branding( $generator_args, 'twitter' ),
						'defaultDesc'  => \esc_html( Sanitize::metadata_content( $_social_description['tw'] ) ),
						'titleLock'    => $is_static_front_page && \strlen( Data\Plugin::get_option( 'homepage_twitter_title' ) ),
						'descLock'     => $is_static_front_page && \strlen( Data\Plugin::get_option( 'homepage_twitter_description' ) ),
					],
				],
			],
		);

		?>
		<div class="tsf-flex-setting tsf-flex" <?= $show_og ? '' : 'style=display:none' ?>>
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for=autodescription_og_title class="tsf-flex-setting-label-item tsf-flex">
						<div><strong><?php \esc_html_e( 'Open Graph Title', 'autodescription' ); ?></strong></div>
					</label>
					<?php
					Data\Plugin::get_option( 'display_character_counter' )
						and Form::output_character_counter_wrap( 'autodescription_og_title' );
					?>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<div id=tsf-og-title-wrap>
					<input class=large-text type=text name="autodescription[_open_graph_title]" id=autodescription_og_title value="<?= \esc_html( Sanitize::metadata_content( $meta['_open_graph_title'] ) ) ?>" autocomplete=off data-form-type=other data-tsf-social-group=autodescription_social_singular data-tsf-social-type=ogTitle />
				</div>
			</div>
		</div>

		<div class="tsf-flex-setting tsf-flex" <?= $show_og ? '' : 'style=display:none' ?>>
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for=autodescription_og_description class="tsf-flex-setting-label-item tsf-flex">
						<div><strong><?php \esc_html_e( 'Open Graph Description', 'autodescription' ); ?></strong></div>
					</label>
					<?php
					Data\Plugin::get_option( 'display_character_counter' )
						and Form::output_character_counter_wrap( 'autodescription_og_description' );
					?>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<textarea class=large-text name="autodescription[_open_graph_description]" id=autodescription_og_description rows=3 cols=4 autocomplete=off data-tsf-social-group=autodescription_social_singular data-tsf-social-type=ogDesc><?= \esc_html( Sanitize::metadata_content( $meta['_open_graph_description'] ) ) ?></textarea>
			</div>
		</div>

		<div class="tsf-flex-setting tsf-flex" <?= $show_tw ? '' : 'style=display:none' ?>>
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for=autodescription_twitter_title class="tsf-flex-setting-label-item tsf-flex">
						<div><strong><?php \esc_html_e( 'Twitter Title', 'autodescription' ); ?></strong></div>
					</label>
					<?php
					Data\Plugin::get_option( 'display_character_counter' )
						and Form::output_character_counter_wrap( 'autodescription_twitter_title' );
					?>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<div id=tsf-twitter-title-wrap>
					<input class=large-text type=text name="autodescription[_twitter_title]" id=autodescription_twitter_title value="<?= \esc_html( Sanitize::metadata_content( $meta['_twitter_title'] ) ) ?>" autocomplete=off data-form-type=other data-tsf-social-group=autodescription_social_singular data-tsf-social-type=twTitle />
				</div>
			</div>
		</div>

		<div class="tsf-flex-setting tsf-flex" <?= $show_tw ? '' : 'style=display:none' ?>>
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for=autodescription_twitter_description class="tsf-flex-setting-label-item tsf-flex">
						<div><strong><?php \esc_html_e( 'Twitter Description', 'autodescription' ); ?></strong></div>
					</label>
					<?php
					Data\Plugin::get_option( 'display_character_counter' )
						and Form::output_character_counter_wrap( 'autodescription_twitter_description' );
					?>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<textarea class=large-text name="autodescription[_twitter_description]" id=autodescription_twitter_description rows=3 cols=4 autocomplete=off data-tsf-social-group=autodescription_social_singular data-tsf-social-type=twDesc><?php // phpcs:ignore, Squiz.PHP.EmbeddedPhp -- textarea element's content is input. Do not add spaces/tabs/lines: the php tag should stick to >.
					// Textareas don't require sanitization in HTML5... other than removing the closing </textarea> tag...?
					echo \esc_html( Sanitize::metadata_content( $meta['_twitter_description'] ) );
				// phpcs:ignore, Squiz.PHP.EmbeddedPhp
				?></textarea>
			</div>
		</div>

		<div class="tsf-flex-setting tsf-flex" <?= $show_tw ? '' : 'style=display:none' ?>>
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for=autodescription_twitter_card_type class="tsf-flex-setting-label-item tsf-flex">
						<div><strong><?php \esc_html_e( 'Twitter Card Type', 'autodescription' ); ?></strong></div>
						<div>
							<?php
							HTML::make_info(
								\__( 'The Twitter Card type is used to determine whether an image appears on the side or as a large cover. This affects X, but also other social platforms like Discord.', 'autodescription' ),
								'https://developer.twitter.com/en/docs/twitter-for-websites/cards/overview/abouts-cards',
							);
							?>
						</div>
					</label>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<?php
				/* translators: %s = default option value */
				$_default_i18n     = \__( 'Default (%s)', 'autodescription' );
				$tw_suported_cards = Meta\Twitter::get_supported_cards();

				// phpcs:disable, WordPress.Security.EscapeOutput -- make_single_select_form() escapes.
				echo Form::make_single_select_form( [
					'id'       => 'autodescription_twitter_card_type',
					'class'    => 'tsf-select-block',
					'name'     => 'autodescription[_tsf_twitter_card_type]',
					'label'    => '',
					'options'  => array_merge(
						[ '' => sprintf( $_default_i18n, $_twitter_card ) ],
						array_combine( $tw_suported_cards, $tw_suported_cards ),
					),
					'selected' => $meta['_tsf_twitter_card_type'],
				] );
				// phpcs:enable, WordPress.Security.EscapeOutput
				?>
			</div>
		</div>
		<?php

		// Fetch image placeholder.
		if ( $is_static_front_page && Data\Plugin::get_option( 'homepage_social_image_url' ) ) {
			$image_placeholder = Data\Plugin::get_option( 'homepage_social_image_url' )
								?: Meta\Image::get_first_generated_image_url( $generator_args, 'social' );
		} else {
			$image_placeholder = Meta\Image::get_first_generated_image_url( $generator_args, 'social' );
		}

		?>
		<div class="tsf-flex-setting tsf-flex">
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for=autodescription_socialimage-url class="tsf-flex-setting-label-item tsf-flex">
						<div><strong><?php \esc_html_e( 'Social Image URL', 'autodescription' ); ?></strong></div>
						<div>
							<?php
							HTML::make_info(
								\__( "The social image URL can be used by search engines and social networks alike. It's best to use an image with a 1.91:1 aspect ratio that is at least 1200px wide for universal support.", 'autodescription' ),
								'https://developers.facebook.com/docs/sharing/best-practices#images',
							);
							?>
						</div>
					</label>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<input class=large-text type=url name="autodescription[_social_image_url]" id=autodescription_socialimage-url placeholder="<?= \esc_url( $image_placeholder ) ?>" value="<?= \esc_url( $meta['_social_image_url'] ) ?>" autocomplete=off />
				<input type=hidden name="autodescription[_social_image_id]" id=autodescription_socialimage-id value="<?= \absint( $meta['_social_image_id'] ) ?>" disabled class=tsf-enable-media-if-js />
				<div class="hide-if-no-tsf-js tsf-social-image-buttons">
					<?php
					// phpcs:disable, WordPress.Security.EscapeOutput -- get_image_uploader_form escapes. (phpcs breaks here, so we use disable)
					echo Form::get_image_uploader_form( [ 'id' => 'autodescription_socialimage' ] );
					// phpcs:enable, WordPress.Security.EscapeOutput
					?>
				</div>
			</div>
		</div>
		<?php
		break;

	case 'visibility':
		$canonical_placeholder = Meta\URI::get_generated_url( $generator_args );

		// Get robots defaults.
		$r_defaults = Meta\Robots::get_generated_meta(
			$generator_args,
			[ 'noindex', 'nofollow', 'noarchive' ],
			ROBOTS_IGNORE_SETTINGS | ROBOTS_IGNORE_PROTECTION,
		);
		$r_settings = [
			'noindex'   => [
				'id'        => 'autodescription_noindex',
				'option'    => '_genesis_noindex',
				'force_on'  => 'index',
				'force_off' => 'noindex',
				'label'     => \__( 'Indexing', 'autodescription' ),
				'_default'  => empty( $r_defaults['noindex'] ) ? 'index' : 'noindex',
			],
			'nofollow'  => [
				'id'        => 'autodescription_nofollow',
				'option'    => '_genesis_nofollow',
				'force_on'  => 'follow',
				'force_off' => 'nofollow',
				'label'     => \__( 'Link following', 'autodescription' ),
				'_default'  => empty( $r_defaults['nofollow'] ) ? 'follow' : 'nofollow',
			],
			'noarchive' => [
				'id'        => 'autodescription_noarchive',
				'option'    => '_genesis_noarchive',
				'force_on'  => 'archive',
				'force_off' => 'noarchive',
				'label'     => \__( 'Archiving', 'autodescription' ),
				'_default'  => empty( $r_defaults['noarchive'] ) ? 'archive' : 'noarchive',
			],
		];

		?>
		<div class="tsf-flex-setting tsf-flex">
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for=autodescription_canonical class="tsf-flex-setting-label-item tsf-flex">
						<div><strong><?php \esc_html_e( 'Canonical URL', 'autodescription' ); ?></strong></div>
						<div>
						<?php
							HTML::make_info(
								\__( 'This urges search engines to go to the outputted URL.', 'autodescription' ),
								'https://developers.google.com/search/docs/advanced/crawling/consolidate-duplicate-urls',
							);
						?>
						</div>
					</label>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<input class=large-text type=url name="autodescription[_genesis_canonical_uri]" id=autodescription_canonical placeholder="<?= \esc_url( $canonical_placeholder ) ?>" value="<?= \esc_url( $meta['_genesis_canonical_uri'] ) ?>" autocomplete=off />
			</div>
		</div>

		<div class="tsf-flex-setting tsf-flex">
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<div class="tsf-flex-setting-label-item tsf-flex">
						<div><strong><?php \esc_html_e( 'Robots Meta Settings', 'autodescription' ); ?></strong></div>
						<div>
						<?php
							HTML::make_info(
								\__( 'These directives may urge robots not to display, follow links on, or create a cached copy of this page.', 'autodescription' ),
								'https://developers.google.com/search/docs/advanced/robots/robots_meta_tag#directives',
							);
						?>
						</div>
					</div>
					<?php
					if ( $is_static_front_page ) {
						printf(
							'<div class=tsf-flex-setting-label-sub-item><span class="description attention">%s</span></div>',
							\esc_html__( 'Warning: No public site should ever apply "noindex" or "nofollow" to the homepage.', 'autodescription' )
						);
						printf(
							'<div class=tsf-flex-setting-label-sub-item><span class=description>%s</span></div>',
							\esc_html__( 'Note: A non-default selection here will overwrite the global homepage SEO settings.', 'autodescription' )
						);
					}
					?>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<?php
				foreach ( $r_settings as $_s ) {
					?>
					<div class="tsf-flex-setting tsf-flex">
						<div class="tsf-flex-setting-label tsf-flex">
							<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
								<label for="<?= \esc_attr( $_s['id'] ) ?>" class="tsf-flex-setting-label-item tsf-flex">
									<div><strong><?= \esc_html( $_s['label'] ) ?></strong></div>
								</label>
							</div>
						</div>
						<div class="tsf-flex-setting-input tsf-flex">
						<?php
							/* translators: %s = default option value */
							$_default_i18n = \__( 'Default (%s)', 'autodescription' );

							// phpcs:disable, WordPress.Security.EscapeOutput -- make_single_select_form() escapes.
							echo Form::make_single_select_form( [
								'id'       => $_s['id'],
								'class'    => 'tsf-select-block',
								'name'     => sprintf( 'autodescription[%s]', $_s['option'] ),
								'label'    => '',
								'options'  => [
									0  => sprintf( $_default_i18n, $_s['_default'] ),
									-1 => $_s['force_on'],
									1  => $_s['force_off'],
								],
								'selected' => Data\Plugin\Post::get_meta_item( $_s['option'] ),
								'data'     => [
									'defaultUnprotected' => $_s['_default'],
									'defaultI18n'        => $_default_i18n,
								],
							] );
							// phpcs:enable, WordPress.Security.EscapeOutput
						?>
						</div>
					</div>
					<?php
				}
				?>
			</div>
		</div>

		<?php
		$can_do_archive_query = Post_Type::supports_taxonomies() && Data\Plugin::get_option( 'alter_archive_query' );
		$can_do_search_query  = (bool) Data\Plugin::get_option( 'alter_search_query' );
		?>

		<?php if ( $can_do_archive_query || $can_do_search_query ) : ?>
		<div class="tsf-flex-setting tsf-flex">
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<div class="tsf-flex-setting-label-item tsf-flex">
						<div><strong><?php \esc_html_e( 'Archive Settings', 'autodescription' ); ?></strong></div>
					</div>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<?php if ( $can_do_search_query ) : ?>
				<div class=tsf-checkbox-wrapper>
					<label for=autodescription_exclude_local_search><input type=checkbox name="autodescription[exclude_local_search]" id=autodescription_exclude_local_search value=1 <?php \checked( $meta['exclude_local_search'] ); ?> />
						<?php
						\esc_html_e( 'Exclude this page from all search queries on this site.', 'autodescription' );
						?>
					</label>
				</div>
				<?php endif; ?>
				<?php if ( $can_do_archive_query ) : ?>
				<div class=tsf-checkbox-wrapper>
					<label for=autodescription_exclude_from_archive><input type=checkbox name="autodescription[exclude_from_archive]" id=autodescription_exclude_from_archive value=1 <?php \checked( $meta['exclude_from_archive'] ); ?> />
						<?php
						\esc_html_e( 'Exclude this page from all archive queries on this site.', 'autodescription' );
						?>
					</label>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<?php endif; ?>

		<div class="tsf-flex-setting tsf-flex">
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for=autodescription_redirect class="tsf-flex-setting-label-item tsf-flex">
						<div>
							<strong><?php \esc_html_e( '301 Redirect URL', 'autodescription' ); ?></strong>
						</div>
						<div>
							<?php
							HTML::make_info(
								\__( 'This will force visitors to go to another URL.', 'autodescription' ),
								'https://developers.google.com/search/docs/advanced/crawling/301-redirects',
							);
							?>
						</div>
					</label>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<input class=large-text type=url name="autodescription[redirect]" id=autodescription_redirect value="<?= \esc_url( $meta['redirect'] ) ?>" autocomplete=off />
			</div>
		</div>
		<?php
endswitch;
