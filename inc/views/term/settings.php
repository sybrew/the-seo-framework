<?php
/**
 * @package The_SEO_Framework\Views\Term
 * @subpackage The_SEO_Framework\Admin\Term
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) or die;

use \The_SEO_Framework\Admin\Settings\Layout\{
	Form,
	HTML,
	Input,
};
use \The_SEO_Framework\Data\Filter\Sanitize;

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

// See output_setting_fields et al.
[ $term, $taxonomy ] = $view_args;

// Fetch Term ID and taxonomy.
$term_id = $term->term_id;
$meta    = Data\Plugin\Term::get_meta( $term_id );

$generator_args = [
	'id'  => $term_id,
	'tax' => $taxonomy,
];

$show_og = (bool) Data\Plugin::get_option( 'og_tags' );
$show_tw = (bool) Data\Plugin::get_option( 'twitter_tags' );

$tw_suported_cards = Meta\Twitter::get_supported_cards();

$image_placeholder = Meta\Image::get_first_generated_image_url( $generator_args, 'social' );

$default_canonical = Meta\URI::get_generated_url( $generator_args );
$robots_defaults   = Meta\Robots::get_generated_meta(
	$generator_args,
	[ 'noindex', 'nofollow', 'noarchive' ],
	ROBOTS_IGNORE_SETTINGS,
);

// TODO reintroduce the info blocks, and place the labels at the left, instead??
$robots_settings = [
	'noindex'   => [
		'id'        => 'autodescription-meta[noindex]',
		'name'      => 'autodescription-meta[noindex]',
		'force_on'  => 'index',
		'force_off' => 'noindex',
		'label'     => \__( 'Indexing', 'autodescription' ),
		'_default'  => empty( $robots_defaults['noindex'] ) ? 'index' : 'noindex',
		'_value'    => $meta['noindex'],
		'_info'     => [
			\__( 'This tells search engines not to show this term in their search results.', 'autodescription' ),
			'https://developers.google.com/search/docs/advanced/crawling/block-indexing',
		],
	],
	'nofollow'  => [
		'id'        => 'autodescription-meta[nofollow]',
		'name'      => 'autodescription-meta[nofollow]',
		'force_on'  => 'follow',
		'force_off' => 'nofollow',
		'label'     => \__( 'Link following', 'autodescription' ),
		'_default'  => empty( $robots_defaults['nofollow'] ) ? 'follow' : 'nofollow',
		'_value'    => $meta['nofollow'],
		'_info'     => [
			\__( 'This tells search engines not to follow links on this term.', 'autodescription' ),
			'https://developers.google.com/search/docs/advanced/guidelines/qualify-outbound-links',
		],
	],
	'noarchive' => [
		'id'        => 'autodescription-meta[noarchive]',
		'name'      => 'autodescription-meta[noarchive]',
		'force_on'  => 'archive',
		'force_off' => 'noarchive',
		'label'     => \__( 'Archiving', 'autodescription' ),
		'_default'  => empty( $robots_defaults['noarchive'] ) ? 'archive' : 'noarchive',
		'_value'    => $meta['noarchive'],
		'_info'     => [
			\__( 'This tells search engines not to save a cached copy of this term.', 'autodescription' ),
			'https://developers.google.com/search/docs/advanced/robots/robots_meta_tag#directives',
		],
	],
];

/* translators: %s = default option value */
$_default_i18n = \__( 'Default (%s)', 'autodescription' );

?>
<h2><?php \esc_html_e( 'General SEO Settings', 'autodescription' ); ?></h2>

<table class="form-table tsf-term-meta">
	<tbody>
		<?php
		if ( Data\Plugin::get_option( 'display_seo_bar_metabox' ) ) {
			?>
			<tr class=form-field>
				<th scope=row valign=top><?php \esc_html_e( 'Doing it Right', 'autodescription' ); ?></th>
				<td>
					<?php
					// phpcs:ignore, WordPress.Security.EscapeOutput -- generate_bar() escapes.
					echo Admin\SEOBar\Builder::generate_bar( $generator_args );
					?>
				</td>
			</tr>
			<?php
		}
		?>

		<tr class=form-field>
			<th scope=row valign=top>
				<label for="autodescription-meta[doctitle]">
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
					and Form::output_character_counter_wrap( 'autodescription-meta[doctitle]' );
				Data\Plugin::get_option( 'display_pixel_counter' )
					and Form::output_pixel_counter_wrap( 'autodescription-meta[doctitle]', 'title' );
				?>
			</th>
			<td>
				<div class=tsf-title-wrap>
					<input type=text name="autodescription-meta[doctitle]" id="autodescription-meta[doctitle]" value="<?= \esc_html( Sanitize::metadata_content( $meta['doctitle'] ) ) ?>" size=40 autocomplete=off data-form-type=other>
					<?php
					Input::output_js_title_data(
						'autodescription-meta[doctitle]',
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
				<label for="autodescription-meta[title_no_blog_name]" class=tsf-term-checkbox-wrap>
					<input type=checkbox name="autodescription-meta[title_no_blog_name]" id="autodescription-meta[title_no_blog_name]" value=1 <?php \checked( Data\Plugin\Term::get_meta_item( 'title_no_blog_name' ) ); ?>>
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
				<label for="autodescription-meta[description]">
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
					and Form::output_character_counter_wrap( 'autodescription-meta[description]' );
				Data\Plugin::get_option( 'display_pixel_counter' )
					and Form::output_pixel_counter_wrap( 'autodescription-meta[description]', 'description' );
				?>
			</th>
			<td>
				<textarea name="autodescription-meta[description]" id="autodescription-meta[description]" rows=4 cols=50 class=large-text autocomplete=off><?= \esc_html( Sanitize::metadata_content( $meta['description'] ) ) ?></textarea>
				<?php
				Input::output_js_description_data(
					'autodescription-meta[description]',
					[
						'state' => [
							'defaultDescription' => \esc_html(
								Meta\Description::get_generated_description( $generator_args )
							),
						],
					],
				);
				?>
			</td>
		</tr>
	</tbody>
</table>

<h2><?php \esc_html_e( 'Social SEO Settings', 'autodescription' ); ?></h2>
<?php

Input::output_js_social_data(
	'autodescription_social_tt',
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

<table class="form-table tsf-term-meta">
	<tbody>
		<tr class=form-field <?= $show_og ? '' : 'style=display:none' ?>>
			<th scope=row valign=top>
				<label for="autodescription-meta[og_title]">
					<strong><?php \esc_html_e( 'Open Graph Title', 'autodescription' ); ?></strong>
				</label>
				<?php
				Data\Plugin::get_option( 'display_character_counter' )
					and Form::output_character_counter_wrap( 'autodescription-meta[og_title]' );
				?>
			</th>
			<td>
				<div id=tsf-og-title-wrap>
					<input name="autodescription-meta[og_title]" id="autodescription-meta[og_title]" type=text value="<?= \esc_html( Sanitize::metadata_content( $meta['og_title'] ) ) ?>" size=40 autocomplete=off data-form-type=other data-tsf-social-group=autodescription_social_tt data-tsf-social-type=ogTitle>
				</div>
			</td>
		</tr>

		<tr class=form-field <?= $show_og ? '' : 'style=display:none' ?>>
			<th scope=row valign=top>
				<label for="autodescription-meta[og_description]">
					<strong><?php \esc_html_e( 'Open Graph Description', 'autodescription' ); ?></strong>
				</label>
				<?php
				Data\Plugin::get_option( 'display_character_counter' )
					and Form::output_character_counter_wrap( 'autodescription-meta[og_description]' );
				?>
			</th>
			<td>
				<textarea name="autodescription-meta[og_description]" id="autodescription-meta[og_description]" rows=4 cols=50 class=large-text autocomplete=off data-tsf-social-group=autodescription_social_tt data-tsf-social-type=ogDesc><?= \esc_html( Sanitize::metadata_content( $meta['og_description'] ) ) ?></textarea>
			</td>
		</tr>

		<tr class=form-field <?= $show_tw ? '' : 'style=display:none' ?>>
			<th scope=row valign=top>
				<label for="autodescription-meta[tw_title]">
					<strong><?php \esc_html_e( 'Twitter Title', 'autodescription' ); ?></strong>
				</label>
				<?php
				Data\Plugin::get_option( 'display_character_counter' )
					and Form::output_character_counter_wrap( 'autodescription-meta[tw_title]' );
				?>
			</th>
			<td>
				<div id=tsf-tw-title-wrap>
					<input name="autodescription-meta[tw_title]" id="autodescription-meta[tw_title]" type=text value="<?= \esc_html( Sanitize::metadata_content( $meta['tw_title'] ) ) ?>" size=40 autocomplete=off data-form-type=other data-tsf-social-group=autodescription_social_tt data-tsf-social-type=twTitle>
				</div>
			</td>
		</tr>

		<tr class=form-field <?= $show_tw ? '' : 'style=display:none' ?>>
			<th scope=row valign=top>
				<label for="autodescription-meta[tw_description]">
					<strong><?php \esc_html_e( 'Twitter Description', 'autodescription' ); ?></strong>
				</label>
				<?php
				Data\Plugin::get_option( 'display_character_counter' )
					and Form::output_character_counter_wrap( 'autodescription-meta[tw_description]' );
				?>
			</th>
			<td>
				<textarea name="autodescription-meta[tw_description]" id="autodescription-meta[tw_description]" rows=4 cols=50 class=large-text autocomplete=off data-tsf-social-group=autodescription_social_tt data-tsf-social-type=twDesc><?= \esc_html( Sanitize::metadata_content( $meta['tw_description'] ) ) ?></textarea>
			</td>
		</tr>

		<tr class=form-field <?= $show_tw ? '' : 'style=display:none' ?>>
			<th scope=row valign=top>
				<label for="autodescription-meta[tw_card_type]">
					<strong><?php \esc_html_e( 'Twitter Card Type', 'autodescription' ); ?></strong>
					<?php
					echo ' ';
					HTML::make_info(
						\__( 'The Twitter Card type is used to determine whether an image appears on the side or as a large cover. This affects X, but also other social platforms like Discord.', 'autodescription' ),
						'https://developer.twitter.com/en/docs/twitter-for-websites/cards/overview/abouts-cards',
					);
					?>
				</label>
			</th>
			<td>
				<?php
				// phpcs:disable, WordPress.Security.EscapeOutput -- make_single_select_form() escapes.
				echo Form::make_single_select_form( [
					'id'       => 'autodescription-meta[tw_card_type]',
					'class'    => 'tsf-term-select-wrap',
					'name'     => 'autodescription-meta[tw_card_type]',
					'options'  => array_merge(
						[ '' => \sprintf( $_default_i18n, Meta\Twitter::get_generated_card_type( $generator_args ) ) ],
						array_combine( $tw_suported_cards, $tw_suported_cards ),
					),
					'selected' => $meta['tw_card_type'],
				] );
				// phpcs:enable, WordPress.Security.EscapeOutput
				?>
			</td>
		</tr>

		<tr class=form-field>
			<th scope=row valign=top>
				<label for=autodescription_meta_socialimage-url>
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
				<input type=url name="autodescription-meta[social_image_url]" id=autodescription_meta_socialimage-url placeholder="<?= \esc_attr( $image_placeholder ) ?>" value="<?= \esc_attr( $meta['social_image_url'] ) ?>" size=40 autocomplete=off>
				<input type=hidden name="autodescription-meta[social_image_id]" id=autodescription_meta_socialimage-id value="<?= \absint( $meta['social_image_id'] ) ?>" disabled class=tsf-enable-media-if-js>
				<div class="hide-if-no-tsf-js tsf-term-button-wrap">
					<?php
					// phpcs:disable, WordPress.Security.EscapeOutput -- get_image_uploader_form escapes. (phpcs breaks here, so we use disable)
					echo Form::get_image_uploader_form( [ 'id' => 'autodescription_meta_socialimage' ] );
					// phpcs:enable, WordPress.Security.EscapeOutput
					?>
				</div>
			</td>
		</tr>
	</tbody>
</table>

<h2><?php \esc_html_e( 'Visibility SEO Settings', 'autodescription' ); ?></h2>

<table class="form-table tsf-term-meta">
	<tbody>
		<tr class=form-field>
			<th scope=row valign=top>
				<label for="autodescription-meta[canonical]">
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
				<input type=url name="autodescription-meta[canonical]" id="autodescription-meta[canonical]" placeholder="<?= \esc_url( $default_canonical ) ?>" value="<?= \esc_attr( $meta['canonical'] ) ?>" size=40 autocomplete=off>
				<?php
				$tax_object  = \get_taxonomy( $taxonomy );
				$permastruct = Meta\URI\Utils::get_url_permastruct( $generator_args );

				$parent_term_slugs        = [];
				$is_taxonomy_hierarchical = $tax_object->hierarchical && $tax_object->rewrite['hierarchical'];

				if ( $is_taxonomy_hierarchical && str_contains( $permastruct, "%$taxonomy%" ) ) {
					// self is filled by current term name.
					foreach ( Data\Term::get_term_parents( $term_id, $taxonomy ) as $parent_term ) {
						// We write it like this instead of [ id => slug ] to prevent reordering numericals via JSON.parse.
						$parent_term_slugs[] = [
							'id'   => $parent_term->term_id,
							'slug' => $parent_term->slug,
						];
					}
				}

				Input::output_js_canonical_data(
					'autodescription-meta[canonical]',
					[
						'state' => [
							'refCanonicalLocked' => false,
							'defaultCanonical'   => \esc_url( $default_canonical ),
							'preferredScheme'    => Meta\URI\Utils::get_preferred_url_scheme(),
							'urlStructure'       => Meta\URI\Utils::get_url_permastruct( $generator_args ),
							'parentTermSlugs'    => $parent_term_slugs,
							'isHierarchical'     => $is_taxonomy_hierarchical,
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
					\__( 'These directives may urge robots not to display, follow links on, or create a cached copy of this term.', 'autodescription' ),
					'https://developers.google.com/search/docs/advanced/robots/robots_meta_tag#directives',
				);
				?>
			</th>
			<td>
				<?php
				foreach ( $robots_settings as $_s ) {
					// phpcs:disable, WordPress.Security.EscapeOutput -- make_single_select_form() escapes.
					echo Form::make_single_select_form( [
						'id'       => $_s['id'],
						'class'    => 'tsf-term-select-wrap',
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
					// phpcs:enable, WordPress.Security.EscapeOutput
				}
				?>
			</td>
		</tr>

		<tr class=form-field>
			<th scope=row valign=top>
				<label for="autodescription-meta[redirect]">
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
				<input type=url name="autodescription-meta[redirect]" id="autodescription-meta[redirect]" value="<?= \esc_attr( $meta['redirect'] ) ?>" size=40 autocomplete=off>
			</td>
		</tr>
	</tbody>
</table>
<?php
