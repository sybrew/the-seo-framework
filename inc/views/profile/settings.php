<?php
/**
 * @package The_SEO_Framework\Views\Profile
 * @subpackage The_SEO_Framework\Admin\User
 */

namespace The_SEO_Framework;

( \defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) ) or die;

use The_SEO_Framework\Admin\Settings\Layout\{
	Form,
	HTML,
	Input,
};
use The_SEO_Framework\Data\Filter\Sanitize;

/**
 * The SEO Framework plugin
 * Copyright (C) 2017 - 2026 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// See output_setting_fields et al.
[ $user ] = $view_args;

$user_id = $user->ID;
$meta    = Data\Plugin\User::get_meta( $user_id );

$generator_args = [ 'uid' => $user_id ];

$show_og = (bool) Data\Plugin::get_option( 'og_tags' );
$show_tw = (bool) Data\Plugin::get_option( 'twitter_tags' );

$tw_supported_cards = Meta\Twitter::get_supported_cards();

$image_placeholder = Meta\Image::get_first_generated_image_url( $generator_args, 'social' );

$default_canonical = Meta\URI::get_generated_url( $generator_args );
$robots_defaults   = Meta\Robots::get_generated_meta(
	$generator_args,
	[ 'noindex', 'nofollow', 'noarchive' ],
	ROBOTS_IGNORE_SETTINGS,
);

$robots_settings = [
	'noindex'   => [
		'id'        => 'tsf-user-meta[noindex]',
		'name'      => 'tsf-user-meta[noindex]',
		'force_on'  => 'index',
		'force_off' => 'noindex',
		'label'     => \__( 'Indexing', 'autodescription' ),
		'_default'  => empty( $robots_defaults['noindex'] ) ? 'index' : 'noindex',
		'_value'    => $meta['noindex'],
		'_info'     => [
			\__( 'This tells search engines not to show this author in their search results.', 'autodescription' ),
			'https://developers.google.com/search/docs/advanced/crawling/block-indexing',
		],
	],
	'nofollow'  => [
		'id'        => 'tsf-user-meta[nofollow]',
		'name'      => 'tsf-user-meta[nofollow]',
		'force_on'  => 'follow',
		'force_off' => 'nofollow',
		'label'     => \__( 'Link following', 'autodescription' ),
		'_default'  => empty( $robots_defaults['nofollow'] ) ? 'follow' : 'nofollow',
		'_value'    => $meta['nofollow'],
		'_info'     => [
			\__( 'This tells search engines not to follow links on this author page.', 'autodescription' ),
			'https://developers.google.com/search/docs/advanced/guidelines/qualify-outbound-links',
		],
	],
	'noarchive' => [
		'id'        => 'tsf-user-meta[noarchive]',
		'name'      => 'tsf-user-meta[noarchive]',
		'force_on'  => 'archive',
		'force_off' => 'noarchive',
		'label'     => \__( 'Archiving', 'autodescription' ),
		'_default'  => empty( $robots_defaults['noarchive'] ) ? 'archive' : 'noarchive',
		'_value'    => $meta['noarchive'],
		'_info'     => [
			\__( 'This tells search engines not to save a cached copy of this author.', 'autodescription' ),
			'https://developers.google.com/search/docs/advanced/robots/robots_meta_tag#directives',
		],
	],
];

$author_fediverse  = $meta['fediverse_page'];
$creator_fediverse = Data\Plugin::get_option( 'fediverse_creator' );
$site_fediverse    = Data\Plugin::get_option( 'fediverse_site' );

$fediverse_handle_placeholder = $creator_fediverse ?: $site_fediverse;

$fediverse_url_fallback = $creator_fediverse
	? Meta\Fediverse::get_profile_url(
		$creator_fediverse,
		Data\Plugin::get_option( 'fediverse_creator_url' ),
	)
	: Meta\Fediverse::get_profile_url(
		$site_fediverse,
		Data\Plugin::get_option( 'fediverse_site_url' ),
	);

$fediverse_url_placeholder = $author_fediverse
	? Meta\Fediverse::get_profile_url( $author_fediverse )
	: $fediverse_url_fallback;

/* translators: %s = default option value */
$_default_i18n = \__( 'Default (%s)', 'autodescription' );

?>
<div class="tsf-user-settings">
<h2><?php \esc_html_e( 'General SEO Settings', 'autodescription' ); ?></h2>
<p class=description><?php \esc_html_e( 'This may be shown publicly.', 'autodescription' ); ?></p>

<table class="form-table tsf-user-meta">
	<tbody>
		<?php
		if ( Data\Plugin::get_option( 'display_seo_bar_metabox' ) ) {
			?>
			<tr class=form-field>
				<th scope=row valign=top><?php \esc_html_e( 'Doing it Right', 'autodescription' ); ?></th>
				<td>
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- generate_bar escapes.
					echo Admin\SEOBar\Builder::generate_bar( $generator_args );
					?>
				</td>
			</tr>
			<?php
		}
		?>

		<tr class=form-field>
			<th scope=row valign=top>
				<label for="tsf-user-meta[doctitle]">
					<strong><?php \esc_html_e( 'Meta Title', 'autodescription' ); ?></strong>
					<?php
					echo ' ';
					HTML::make_info(
						\__( 'The meta title can be used to determine the title used on search engine result pages.', 'autodescription' ),
						'https://developers.google.com/search/docs/advanced/appearance/title-link',
					);
					?>
				</label>
				<?php
				Data\Plugin::get_option( 'display_character_counter' )
					and Form::output_character_counter_wrap( 'tsf-user-meta[doctitle]' );
				Data\Plugin::get_option( 'display_pixel_counter' )
					and Form::output_pixel_counter_wrap( 'tsf-user-meta[doctitle]', 'title' );
				?>
			</th>
			<td>
				<div class=tsf-title-wrap>
					<input type=text name="tsf-user-meta[doctitle]" id="tsf-user-meta[doctitle]" value="<?= \esc_html( Sanitize::metadata_content( $meta['doctitle'] ) ) ?>" size=40 autocomplete=off data-form-type=other data-tsf-proposed="">
					<?php
					Input::output_js_title_data(
						'tsf-user-meta[doctitle]',
						[
							'state' => [
								'refTitleLocked'    => false,
								'defaultTitle'      => \esc_html( Meta\Title::get_bare_generated_title( $generator_args ) ),
								'addAdditions'      => Meta\Title\Conditions::use_branding( $generator_args ),
								'useSocialTagline'  => Meta\Title\Conditions::use_branding( $generator_args, true ),
								'additionValue'     => \esc_html( Meta\Title::get_addition() ),
								'additionPlacement' => 'left' === Meta\Title::get_addition_location() ? 'before' : 'after',
							],
						],
					);
					?>
				</div>
				<label for="tsf-user-meta[title_no_blog_name]" class=tsf-user-checkbox-wrap>
					<input type=checkbox name="tsf-user-meta[title_no_blog_name]" id="tsf-user-meta[title_no_blog_name]" value=1 <?php \checked( $meta['title_no_blog_name'] ); ?>>
					<?php
					\esc_html_e( 'Remove the site title?', 'autodescription' );
					echo ' ';
					HTML::make_info( \__( 'Use this when you want to rearrange the title parts manually.', 'autodescription' ) );
					?>
				</label>
			</td>
		</tr>

		<tr class=form-field>
			<th scope=row valign=top>
				<label for="tsf-user-meta[description]">
					<strong><?php \esc_html_e( 'Meta Description', 'autodescription' ); ?></strong>
					<?php
					echo ' ';
					HTML::make_info(
						\__( 'The meta description can be used to determine the text used under the title on search engine results pages.', 'autodescription' ),
						'https://developers.google.com/search/docs/advanced/appearance/snippet',
					);
					?>
				</label>
				<?php
				Data\Plugin::get_option( 'display_character_counter' )
					and Form::output_character_counter_wrap( 'tsf-user-meta[description]' );
				Data\Plugin::get_option( 'display_pixel_counter' )
					and Form::output_pixel_counter_wrap( 'tsf-user-meta[description]', 'description' );
				?>
			</th>
			<td>
				<textarea name="tsf-user-meta[description]" id="tsf-user-meta[description]" rows=4 cols=50 class=large-text autocomplete=off data-tsf-proposed=""><?= \esc_html( Sanitize::metadata_content( $meta['description'] ) ) ?></textarea>
				<?php
				Input::output_js_description_data(
					'tsf-user-meta[description]',
					[
						'state' => [
							'defaultDescription' => \esc_html(
								Meta\Description::get_generated_description( $generator_args ),
							),
						],
					],
				);
				?>
			</td>
		</tr>
	</tbody>
</table>

<h2><?php \esc_html_e( 'Social Settings', 'autodescription' ); ?></h2>
<p class=description><?php \esc_html_e( 'This may be shown publicly.', 'autodescription' ); ?></p>
<?php

Input::output_js_social_data(
	'tsf_user_social',
	[
		'og' => [
			'state' => [
				'defaultTitle' => \esc_html( Meta\Open_Graph::get_generated_title( $generator_args ) ),
				'addAdditions' => Meta\Title\Conditions::use_branding( $generator_args, 'og' ),
				'defaultDesc'  => \esc_html( Meta\Open_Graph::get_generated_description( $generator_args ) ),
			],
		],
		'tw' => [
			'state' => [
				'defaultTitle' => \esc_html( Meta\Twitter::get_generated_title( $generator_args ) ),
				'addAdditions' => Meta\Title\Conditions::use_branding( $generator_args, 'twitter' ),
				'defaultDesc'  => \esc_html( Meta\Twitter::get_generated_description( $generator_args ) ),
			],
		],
	],
);
?>

<table class="form-table tsf-user-meta">
	<tbody>
		<tr class=form-field <?= $show_og ? '' : 'style=display:none' ?>>
			<th scope=row valign=top>
				<label for="tsf-user-meta[og_title]">
					<strong><?php \esc_html_e( 'Open Graph Title', 'autodescription' ); ?></strong>
				</label>
				<?php
				Data\Plugin::get_option( 'display_character_counter' )
					and Form::output_character_counter_wrap( 'tsf-user-meta[og_title]' );
				?>
			</th>
			<td>
				<div id=tsf-og-title-wrap>
					<input name="tsf-user-meta[og_title]" id="tsf-user-meta[og_title]" type=text value="<?= \esc_html( Sanitize::metadata_content( $meta['og_title'] ) ) ?>" size=40 autocomplete=off data-form-type=other data-tsf-social-group=tsf_user_social data-tsf-social-type=ogTitle data-tsf-proposed="">
				</div>
			</td>
		</tr>

		<tr class=form-field <?= $show_og ? '' : 'style=display:none' ?>>
			<th scope=row valign=top>
				<label for="tsf-user-meta[og_description]">
					<strong><?php \esc_html_e( 'Open Graph Description', 'autodescription' ); ?></strong>
				</label>
				<?php
				Data\Plugin::get_option( 'display_character_counter' )
					and Form::output_character_counter_wrap( 'tsf-user-meta[og_description]' );
				?>
			</th>
			<td>
				<textarea name="tsf-user-meta[og_description]" id="tsf-user-meta[og_description]" rows=4 cols=50 class=large-text autocomplete=off data-tsf-social-group=tsf_user_social data-tsf-social-type=ogDesc data-tsf-proposed=""><?= \esc_html( Sanitize::metadata_content( $meta['og_description'] ) ) ?></textarea>
			</td>
		</tr>

		<tr class=form-field <?= $show_tw ? '' : 'style=display:none' ?>>
			<th scope=row valign=top>
				<label for="tsf-user-meta[tw_title]">
					<strong><?php \esc_html_e( 'Twitter Card Title', 'autodescription' ); ?></strong>
				</label>
				<?php
				Data\Plugin::get_option( 'display_character_counter' )
					and Form::output_character_counter_wrap( 'tsf-user-meta[tw_title]' );
				?>
			</th>
			<td>
				<div id=tsf-tw-title-wrap>
					<input name="tsf-user-meta[tw_title]" id="tsf-user-meta[tw_title]" type=text value="<?= \esc_html( Sanitize::metadata_content( $meta['tw_title'] ) ) ?>" size=40 autocomplete=off data-form-type=other data-tsf-social-group=tsf_user_social data-tsf-social-type=twTitle data-tsf-proposed="">
				</div>
			</td>
		</tr>

		<tr class=form-field <?= $show_tw ? '' : 'style=display:none' ?>>
			<th scope=row valign=top>
				<label for="tsf-user-meta[tw_description]">
					<strong><?php \esc_html_e( 'Twitter Card Description', 'autodescription' ); ?></strong>
				</label>
				<?php
				Data\Plugin::get_option( 'display_character_counter' )
					and Form::output_character_counter_wrap( 'tsf-user-meta[tw_description]' );
				?>
			</th>
			<td>
				<textarea name="tsf-user-meta[tw_description]" id="tsf-user-meta[tw_description]" rows=4 cols=50 class=large-text autocomplete=off data-tsf-social-group=tsf_user_social data-tsf-social-type=twDesc data-tsf-proposed=""><?= \esc_html( Sanitize::metadata_content( $meta['tw_description'] ) ) ?></textarea>
			</td>
		</tr>

		<tr class=form-field <?= $show_tw ? '' : 'style=display:none' ?>>
			<th scope=row valign=top>
				<label for="tsf-user-meta[tw_card_type]">
					<strong><?php \esc_html_e( 'Twitter Card Type', 'autodescription' ); ?></strong>
					<?php
					echo ' ';
					HTML::make_info(
						\__( 'The Twitter Card type controls the link preview layout. On X, the summary card shows a small thumbnail beside truncated title and description; the large-image card overlays the title on the image with no description. On Discord, the image appears small at the side or large below; both card types show the description.', 'autodescription' ),
						'https://kb.theseoframework.com/?p=451#card-types',
					);
					?>
				</label>
			</th>
			<td>
				<div class=tsf-user-select-wrap>
					<?php
					// phpcs:disable WordPress.Security.EscapeOutput -- make_single_select_form() escapes.
					echo Form::make_single_select_form( [
						'id'       => 'tsf-user-meta[tw_card_type]',
						'name'     => 'tsf-user-meta[tw_card_type]',
						'options'  => array_merge(
							[ '' => \sprintf( $_default_i18n, Meta\Twitter::get_generated_card_type( $generator_args ) ) ],
							array_combine( $tw_supported_cards, $tw_supported_cards ),
						),
						'selected' => $meta['tw_card_type'],
					] );
					// phpcs:enable WordPress.Security.EscapeOutput
					?>
				</div>
			</td>
		</tr>

		<tr class=form-field>
			<th scope=row valign=top>
				<label for=tsf_user_meta_socialimage-url>
					<strong><?php \esc_html_e( 'Social Image URL', 'autodescription' ); ?></strong>
					<?php
					echo ' ';
					HTML::make_info(
						\__( "The social image URL can be used by search engines and social networks alike. It's best to use an image with a 1.91:1 aspect ratio that is at least 1200px wide for universal support.", 'autodescription' ),
						'https://developers.facebook.com/docs/sharing/best-practices#images',
					);
					?>
				</label>
			</th>
			<td>
				<input type=url name="tsf-user-meta[social_image_url]" id=tsf_user_meta_socialimage-url placeholder="<?= \esc_attr( $image_placeholder ) ?>" value="<?= \esc_attr( $meta['social_image_url'] ) ?>" size=40 autocomplete=off data-tsf-proposed="<?= \esc_attr( $image_placeholder ) ?>">
				<input type=hidden name="tsf-user-meta[social_image_id]" id=tsf_user_meta_socialimage-id value="<?= \absint( $meta['social_image_id'] ) ?>" disabled class=tsf-enable-media-if-js>
				<div class="hide-if-no-tsf-js tsf-user-button-wrap">
					<?php
					// phpcs:disable WordPress.Security.EscapeOutput -- get_image_uploader_form escapes. (phpcs breaks here, so we use disable)
					echo Form::get_image_uploader_form( [ 'id' => 'tsf_user_meta_socialimage' ] );
					// phpcs:enable WordPress.Security.EscapeOutput
					?>
				</div>
			</td>
		</tr>
	</tbody>
</table>

<h3><?php \esc_html_e( 'Authorial Info', 'autodescription' ); ?></h3>
<p class=description><?php \esc_html_e( 'This may be shown publicly.', 'autodescription' ); ?></p>
<?php
$social_format_hint = \sprintf(
	'<p class="tsf-social-format-hint hidden"><span class=description><span class=tsf-social-format-label></span>%s</span></p>',
	\sprintf(
		'<button type=button class="button button-small tsf-social-format-apply hidden">%s</button>',
		/* translators: Action button. Writes a suggested profile URL into the field. */
		\esc_html__( 'Use this', 'autodescription' ),
	),
);
?>
<table class="form-table tsf-user-meta">
	<tbody>
		<tr class=form-field>
			<th scope=row valign=top>
				<label for="tsf-user-meta[facebook_page]">
					<strong><?php \esc_html_e( 'Facebook profile page', 'autodescription' ); ?></strong>
				</label>
			</th>
			<td>
				<?php
				$facebook_format  = 'https://www.facebook.com/{username}';
				$facebook_pattern = '^(' . str_replace(
					'\{username\}',
					'([^/?#]+)',
					preg_quote( $facebook_format, '/' ),
				) . ')/?$';
				?>
				<div class=tsf-social-format-field>
					<input type=url name="tsf-user-meta[facebook_page]" id="tsf-user-meta[facebook_page]" value="<?= \esc_attr( $meta['facebook_page'] ) ?>" size=40 autocomplete=off data-social-formats="<?= \esc_attr( \wp_json_encode( [ $facebook_format ] ) ) ?>" data-social-pattern="<?= \esc_attr( $facebook_pattern ) ?>">
					<?= $social_format_hint ?>
				</div>
			</td>
		</tr>

		<tr class=form-field>
			<th scope=row valign=top>
				<label for="tsf-user-meta[fediverse_page]">
					<strong><?php \esc_html_e( 'Fediverse profile', 'autodescription' ); ?></strong>
				</label>
			</th>
			<td>
				<div class=tsf-social-format-field>
					<input type=text name="tsf-user-meta[fediverse_page]" id="tsf-user-meta[fediverse_page]" placeholder="<?= \esc_attr( $fediverse_handle_placeholder ) ?>" value="<?= \esc_attr( $author_fediverse ) ?>" size=40 autocomplete=off class=ltr data-tsf-proposed="<?= \esc_attr( $fediverse_handle_placeholder ) ?>" data-social-kind=fediverse-handle data-social-example="<?= \esc_attr( \_x( '@your-username@example.social', 'Fediverse handle', 'autodescription' ) ) ?>">
					<?= $social_format_hint ?>
				</div>
			</td>
		</tr>

		<tr class=form-field>
			<th scope=row valign=top>
				<label for="tsf-user-meta[fediverse_page_url]">
					<strong><?php \esc_html_e( 'Fediverse profile URL', 'autodescription' ); ?></strong>
				</label>
			</th>
			<td>
				<div class=tsf-social-format-field>
					<input type=url name="tsf-user-meta[fediverse_page_url]" id="tsf-user-meta[fediverse_page_url]" placeholder="<?= \esc_attr( $fediverse_url_placeholder ) ?>" value="<?= \esc_attr( $meta['fediverse_page_url'] ) ?>" size=40 autocomplete=off class=ltr data-tsf-proposed="<?= \esc_attr( $fediverse_url_placeholder ) ?>" data-tsf-fediverse-fallback="<?= \esc_attr( $fediverse_url_fallback ) ?>" data-social-kind=fediverse-url data-social-example="<?= \esc_attr( \_x( 'https://example.social/@your-username', 'Fediverse profile URL', 'autodescription' ) ) ?>">
					<?= $social_format_hint ?>
				</div>
			</td>
		</tr>

		<tr class=form-field>
			<th scope=row valign=top>
				<label for="tsf-user-meta[twitter_page]">
					<strong><?php \esc_html_e( 'X profile handle', 'autodescription' ); ?></strong>
				</label>
			</th>
			<td>
				<div class=tsf-social-format-field>
					<input type=text name="tsf-user-meta[twitter_page]" id="tsf-user-meta[twitter_page]" value="<?= \esc_attr( $meta['twitter_page'] ) ?>" size=40 autocomplete=off class=ltr data-social-kind=x data-social-example="<?= \esc_attr( \_x( '@username', 'X @username', 'autodescription' ) ) ?>">
					<?= $social_format_hint ?>
				</div>
			</td>
		</tr>
	</tbody>
</table>

<h2><?php \esc_html_e( 'Visibility Settings', 'autodescription' ); ?></h2>
<p class=description><?php \esc_html_e( 'This may be shown publicly.', 'autodescription' ); ?></p>

<table class="form-table tsf-user-meta">
	<tbody>
		<tr class=form-field>
			<th scope=row valign=top>
				<label for="tsf-user-meta[canonical]">
					<strong><?php \esc_html_e( 'Canonical URL', 'autodescription' ); ?></strong>
					<?php
					echo ' ';
					HTML::make_info(
						\__( 'This urges search engines to go to the outputted URL.', 'autodescription' ),
						'https://developers.google.com/search/docs/advanced/crawling/consolidate-duplicate-urls',
					);
					?>
				</label>
			</th>
			<td>
				<input type=url name="tsf-user-meta[canonical]" id="tsf-user-meta[canonical]" placeholder="<?= \esc_url( $default_canonical ) ?>" value="<?= \esc_attr( $meta['canonical'] ) ?>" size=40 autocomplete=off data-tsf-proposed="<?= \esc_url( $default_canonical ) ?>">
				<?php
				Input::output_js_canonical_data(
					'tsf-user-meta[canonical]',
					[
						'state' => [
							'refCanonicalLocked' => false,
							'defaultCanonical'   => \esc_url( $default_canonical ),
							'preferredScheme'    => Meta\URI\Utils::get_preferred_url_scheme(),
							'urlStructure'       => Meta\URI\Utils::get_url_permastruct( $generator_args ),
						],
					],
				);
				?>
			</td>
		</tr>

		<tr class=form-field>
			<th scope=row valign=top>
				<?php
				\esc_html_e( 'Robots Meta Settings', 'autodescription' );
				echo ' ';
				HTML::make_info(
					\__( 'These directives may urge robots not to display, follow links on, or create a cached copy of this author.', 'autodescription' ),
					'https://developers.google.com/search/docs/advanced/robots/robots_meta_tag#directives',
				);
				?>
			</th>
			<td>
				<?php
				foreach ( $robots_settings as $_s ) {
					?>
					<div class=tsf-user-select-wrap>
						<?php
						// phpcs:disable WordPress.Security.EscapeOutput -- make_single_select_form() escapes.
						echo Form::make_single_select_form( [
							'id'       => $_s['id'],
							'name'     => $_s['name'],
							'label'    => $_s['label'],
							'options'  => [
								0  => \sprintf( $_default_i18n, $_s['_default'] ),
								-1 => $_s['force_on'],
								1  => $_s['force_off'],
							],
							'selected' => $_s['_value'],
							'info'     => $_s['_info'],
							'data'     => [
								'defaultUnprotected' => $_s['_default'],
								'defaultI18n'        => $_default_i18n,
							],
						] );
						// phpcs:enable WordPress.Security.EscapeOutput
						?>
					</div>
					<?php
				}
				?>
			</td>
		</tr>

		<tr class=form-field>
			<th scope=row valign=top>
				<label for="tsf-user-meta[redirect]">
					<strong><?php \esc_html_e( '301 Redirect URL', 'autodescription' ); ?></strong>
					<?php
					echo ' ';
					HTML::make_info(
						\__( 'This will force visitors to go to another URL.', 'autodescription' ),
						'https://developers.google.com/search/docs/crawling-indexing/301-redirects',
					);
					?>
				</label>
			</th>
			<td>
				<input type=url name="tsf-user-meta[redirect]" id="tsf-user-meta[redirect]" value="<?= \esc_attr( $meta['redirect'] ) ?>" size=40 autocomplete=off>
			</td>
		</tr>
	</tbody>
</table>
</div>
<?php
