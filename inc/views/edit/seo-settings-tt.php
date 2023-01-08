<?php
/**
 * @package The_SEO_Framework\Views\Edit
 * @subpackage The_SEO_Framework\Admin\Edit\Term
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

use The_SEO_Framework\Bridges\TermSettings,
	The_SEO_Framework\Interpreters\HTML,
	The_SEO_Framework\Interpreters\Form;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and tsf()->_verify_include_secret( $_secret ) or die;

// Fetch Term ID and taxonomy.
$term_id = $term->term_id;
$meta    = $this->get_term_meta( $term_id );

$title       = $meta['doctitle'];
$description = $meta['description'];
$canonical   = $meta['canonical'];
$noindex     = $meta['noindex'];
$nofollow    = $meta['nofollow'];
$noarchive   = $meta['noarchive'];
$redirect    = $meta['redirect'];

$social_image_url = $meta['social_image_url'];
$social_image_id  = $meta['social_image_id'];

$og_title       = $meta['og_title'];
$og_description = $meta['og_description'];
$tw_title       = $meta['tw_title'];
$tw_description = $meta['tw_description'];

$_generator_args = [
	'id'       => $term_id,
	'taxonomy' => $taxonomy,
];

$show_og = (bool) $this->get_option( 'og_tags' );
$show_tw = (bool) $this->get_option( 'twitter_tags' );

//! Social image placeholder.
$image_details     = current( $this->get_generated_image_details( $_generator_args, true, 'social', true ) );
$image_placeholder = $image_details['url'] ?? '';

$canonical_placeholder = $this->get_canonical_url( $_generator_args ); // implies get_custom_field = false
$robots_defaults       = $this->generate_robots_meta(
	$_generator_args,
	[ 'noindex', 'nofollow', 'noarchive' ],
	The_SEO_Framework\ROBOTS_IGNORE_SETTINGS
);

// TODO reintroduce the info blocks, and place the labels at the left, instead??
$robots_settings = [
	'noindex'   => [
		'id'        => 'autodescription-meta[noindex]',
		'name'      => 'autodescription-meta[noindex]',
		'force_on'  => 'index',
		'force_off' => 'noindex',
		'label'     => __( 'Indexing', 'autodescription' ),
		'_default'  => empty( $robots_defaults['noindex'] ) ? 'index' : 'noindex',
		'_value'    => $noindex,
		'_info'     => [
			__( 'This tells search engines not to show this term in their search results.', 'autodescription' ),
			'https://developers.google.com/search/docs/advanced/crawling/block-indexing',
		],
	],
	'nofollow'  => [
		'id'        => 'autodescription-meta[nofollow]',
		'name'      => 'autodescription-meta[nofollow]',
		'force_on'  => 'follow',
		'force_off' => 'nofollow',
		'label'     => __( 'Link following', 'autodescription' ),
		'_default'  => empty( $robots_defaults['nofollow'] ) ? 'follow' : 'nofollow',
		'_value'    => $nofollow,
		'_info'     => [
			__( 'This tells search engines not to follow links on this term.', 'autodescription' ),
			'https://developers.google.com/search/docs/advanced/guidelines/qualify-outbound-links',
		],
	],
	'noarchive' => [
		'id'        => 'autodescription-meta[noarchive]',
		'name'      => 'autodescription-meta[noarchive]',
		'force_on'  => 'archive',
		'force_off' => 'noarchive',
		'label'     => __( 'Archiving', 'autodescription' ),
		'_default'  => empty( $robots_defaults['noarchive'] ) ? 'archive' : 'noarchive',
		'_value'    => $noarchive,
		'_info'     => [
			__( 'This tells search engines not to save a cached copy of this term.', 'autodescription' ),
			'https://developers.google.com/search/docs/advanced/robots/robots_meta_tag#directives',
		],
	],
];

?>
<h2><?php esc_html_e( 'General SEO Settings', 'autodescription' ); ?></h2>

<table class="form-table tsf-term-meta">
	<tbody>
		<?php if ( $this->get_option( 'display_seo_bar_metabox' ) ) : ?>
		<tr class=form-field>
			<th scope=row valign=top><?php esc_html_e( 'Doing it Right', 'autodescription' ); ?></th>
			<td>
				<?php
				// phpcs:ignore, WordPress.Security.EscapeOutput -- get_generated_seo_bar() escapes.
				echo $this->get_generated_seo_bar( $_generator_args );
				?>
			</td>
		</tr>
		<?php endif; ?>

		<tr class=form-field>
			<th scope=row valign=top>
				<label for="autodescription-meta[doctitle]">
					<strong><?php esc_html_e( 'Meta Title', 'autodescription' ); ?></strong>
					<?php
					echo ' ';
					HTML::make_info(
						__( 'The meta title can be used to determine the title used on search engine result pages.', 'autodescription' ),
						'https://developers.google.com/search/docs/advanced/appearance/title-link'
					);
					?>
				</label>
				<?php
				$this->get_option( 'display_character_counter' )
					and Form::output_character_counter_wrap( 'autodescription-meta[doctitle]' );
				$this->get_option( 'display_pixel_counter' )
					and Form::output_pixel_counter_wrap( 'autodescription-meta[doctitle]', 'title' );
				?>
			</th>
			<td>
				<div class=tsf-title-wrap>
					<input type=text name="autodescription-meta[doctitle]" id="autodescription-meta[doctitle]" value="<?= $this->esc_attr_preserve_amp( $title ) ?>" size=40 autocomplete=off />
					<?php
					$this->output_js_title_elements(); // legacy
					$this->output_js_title_data(
						'autodescription-meta[doctitle]',
						[
							'state' => [
								'refTitleLocked'    => false,
								'defaultTitle'      => $this->s_title( $this->get_filtered_raw_generated_title( $_generator_args ) ),
								'addAdditions'      => $this->use_title_branding( $_generator_args ),
								'useSocialTagline'  => $this->use_title_branding( $_generator_args, true ),
								'additionValue'     => $this->s_title( $this->get_blogname() ),
								'additionPlacement' => 'left' === $this->get_title_seplocation() ? 'before' : 'after',
								'hasLegacy'         => true,
							],
						]
					);
					?>
				</div>
				<label for="autodescription-meta[title_no_blog_name]" class=tsf-term-checkbox-wrap>
					<input type=checkbox name="autodescription-meta[title_no_blog_name]" id="autodescription-meta[title_no_blog_name]" value=1 <?php checked( $this->get_term_meta_item( 'title_no_blog_name' ) ); ?> />
					<?php
					esc_html_e( 'Remove the site title?', 'autodescription' );
					echo ' ';
					HTML::make_info( __( 'Use this when you want to rearrange the title parts manually.', 'autodescription' ) );
					?>
				</label>
			</td>
		</tr>

		<tr class=form-field>
			<th scope=row valign=top>
				<label for="autodescription-meta[description]">
					<strong><?php esc_html_e( 'Meta Description', 'autodescription' ); ?></strong>
					<?php
					echo ' ';
					HTML::make_info(
						__( 'The meta description can be used to determine the text used under the title on search engine results pages.', 'autodescription' ),
						'https://developers.google.com/search/docs/advanced/appearance/snippet'
					);
					?>
				</label>
				<?php
				$this->get_option( 'display_character_counter' )
					and Form::output_character_counter_wrap( 'autodescription-meta[description]' );
				$this->get_option( 'display_pixel_counter' )
					and Form::output_pixel_counter_wrap( 'autodescription-meta[description]', 'description' );
				?>
			</th>
			<td>
				<textarea name="autodescription-meta[description]" id="autodescription-meta[description]" rows=4 cols=50 class=large-text autocomplete=off><?= $this->esc_attr_preserve_amp( $description ) ?></textarea>
				<?php
				$this->output_js_description_elements(); // legacy
				$this->output_js_description_data(
					'autodescription-meta[description]',
					[
						'state' => [
							'defaultDescription' => $this->get_generated_description( $_generator_args ),
							'hasLegacy'          => true,
						],
					]
				);
				?>
			</td>
		</tr>
	</tbody>
</table>

<h2><?php esc_html_e( 'Social SEO Settings', 'autodescription' ); ?></h2>
<?php

$this->output_js_social_data(
	'autodescription_social_tt',
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

<table class="form-table tsf-term-meta">
	<tbody>
		<tr class=form-field <?= $show_og ? '' : 'style=display:none' ?>>
			<th scope=row valign=top>
				<label for="autodescription-meta[og_title]">
					<strong><?php esc_html_e( 'Open Graph Title', 'autodescription' ); ?></strong>
				</label>
				<?php
				$this->get_option( 'display_character_counter' )
					and Form::output_character_counter_wrap( 'autodescription-meta[og_title]' );
				?>
			</th>
			<td>
				<div id=tsf-og-title-wrap>
					<input name="autodescription-meta[og_title]" id="autodescription-meta[og_title]" type=text value="<?= $this->esc_attr_preserve_amp( $og_title ) ?>" size=40 autocomplete=off data-tsf-social-group=autodescription_social_tt data-tsf-social-type=ogTitle />
				</div>
			</td>
		</tr>

		<tr class=form-field <?= $show_og ? '' : 'style=display:none' ?>>
			<th scope=row valign=top>
				<label for="autodescription-meta[og_description]">
					<strong><?php esc_html_e( 'Open Graph Description', 'autodescription' ); ?></strong>
				</label>
				<?php
				$this->get_option( 'display_character_counter' )
					and Form::output_character_counter_wrap( 'autodescription-meta[og_description]' );
				?>
			</th>
			<td>
				<textarea name="autodescription-meta[og_description]" id="autodescription-meta[og_description]" rows=4 cols=50 class=large-text autocomplete=off data-tsf-social-group=autodescription_social_tt data-tsf-social-type=ogDesc><?= $this->esc_attr_preserve_amp( $og_description ) ?></textarea>
			</td>
		</tr>

		<tr class=form-field <?= $show_tw ? '' : 'style=display:none' ?>>
			<th scope=row valign=top>
				<label for="autodescription-meta[tw_title]">
					<strong><?php esc_html_e( 'Twitter Title', 'autodescription' ); ?></strong>
				</label>
				<?php
				$this->get_option( 'display_character_counter' )
					and Form::output_character_counter_wrap( 'autodescription-meta[tw_title]' );
				?>
			</th>
			<td>
				<div id=tsf-tw-title-wrap>
					<input name="autodescription-meta[tw_title]" id="autodescription-meta[tw_title]" type=text value="<?= $this->esc_attr_preserve_amp( $tw_title ) ?>" size=40 autocomplete=off data-tsf-social-group=autodescription_social_tt data-tsf-social-type=twTitle />
				</div>
			</td>
		</tr>

		<tr class=form-field <?= $show_tw ? '' : 'style=display:none' ?>>
			<th scope=row valign=top>
				<label for="autodescription-meta[tw_description]">
					<strong><?php esc_html_e( 'Twitter Description', 'autodescription' ); ?></strong>
				</label>
				<?php
				$this->get_option( 'display_character_counter' )
					and Form::output_character_counter_wrap( 'autodescription-meta[tw_description]' );
				?>
			</th>
			<td>
				<textarea name="autodescription-meta[tw_description]" id="autodescription-meta[tw_description]" rows=4 cols=50 class=large-text autocomplete=off data-tsf-social-group=autodescription_social_tt data-tsf-social-type=twDesc><?= $this->esc_attr_preserve_amp( $tw_description ) ?></textarea>
			</td>
		</tr>

		<tr class=form-field>
			<th scope=row valign=top>
				<label for=autodescription_meta_socialimage-url>
					<strong><?php esc_html_e( 'Social Image URL', 'autodescription' ); ?></strong>
					<?php
					echo ' ';
					HTML::make_info(
						__( "The social image URL can be used by search engines and social networks alike. It's best to use an image with a 1.91:1 aspect ratio that is at least 1200px wide for universal support.", 'autodescription' ),
						'https://developers.facebook.com/docs/sharing/best-practices#images'
					);
					?>
				</label>
			</th>
			<td>
				<input type=url name="autodescription-meta[social_image_url]" id=autodescription_meta_socialimage-url placeholder="<?= esc_attr( $image_placeholder ) ?>" value="<?= esc_attr( $social_image_url ) ?>" size=40 autocomplete=off />
				<input type=hidden name="autodescription-meta[social_image_id]" id=autodescription_meta_socialimage-id value="<?= absint( $social_image_id ) ?>" disabled class=tsf-enable-media-if-js />
				<div class="hide-if-no-tsf-js tsf-term-button-wrap">
					<?php
					// phpcs:ignore, WordPress.Security.EscapeOutput -- Already escaped.
					echo Form::get_image_uploader_form( [ 'id' => 'autodescription_meta_socialimage' ] );
					?>
				</div>
			</td>
		</tr>
	</tbody>
</table>

<h2><?php esc_html_e( 'Visibility SEO Settings', 'autodescription' ); ?></h2>

<table class="form-table tsf-term-meta">
	<tbody>
		<tr class=form-field>
			<th scope=row valign=top>
				<label for="autodescription-meta[canonical]">
					<strong><?php esc_html_e( 'Canonical URL', 'autodescription' ); ?></strong>
					<?php
					echo ' ';
					HTML::make_info(
						__( 'This urges search engines to go to the outputted URL.', 'autodescription' ),
						'https://developers.google.com/search/docs/advanced/crawling/consolidate-duplicate-urls'
					);
					?>
				</label>
			</th>
			<td>
				<input type=url name="autodescription-meta[canonical]" id="autodescription-meta[canonical]" placeholder="<?= esc_attr( $canonical_placeholder ) ?>" value="<?= esc_attr( $canonical ) ?>" size=40 autocomplete=off />
			</td>
		</tr>

		<tr class=form-field>
			<th scope=row valign=top>
				<?php
				esc_html_e( 'Robots Meta Settings', 'autodescription' );
				echo ' ';
				HTML::make_info(
					__( 'These directives may urge robots not to display, follow links on, or create a cached copy of this term.', 'autodescription' ),
					'https://developers.google.com/search/docs/advanced/robots/robots_meta_tag#directives'
				);
				?>
				</th>
			<td>
				<?php
				foreach ( $robots_settings as $_s ) :
					// phpcs:disable, WordPress.Security.EscapeOutput -- make_single_select_form() escapes.
					echo Form::make_single_select_form( [
						'id'      => $_s['id'],
						'class'   => 'tsf-term-select-wrap',
						'name'    => $_s['name'],
						'label'   => $_s['label'],
						'options' => [
							/* translators: %s = default option value */
							0  => sprintf( __( 'Default (%s)', 'autodescription' ), $_s['_default'] ),
							-1 => $_s['force_on'],
							1  => $_s['force_off'],
						],
						'default' => $_s['_value'],
						'info'    => $_s['_info'],
						'data'    => [
							'defaultUnprotected' => $_s['_default'],
							/* translators: %s = default option value */
							'defaultI18n'        => __( 'Default (%s)', 'autodescription' ),
						],
					] );
					// phpcs:enable, WordPress.Security.EscapeOutput
				endforeach;
				?>
			</td>
		</tr>

		<tr class=form-field>
			<th scope=row valign=top>
				<label for="autodescription-meta[redirect]">
					<strong><?php esc_html_e( '301 Redirect URL', 'autodescription' ); ?></strong>
					<?php
					echo ' ';
					HTML::make_info(
						__( 'This will force visitors to go to another URL.', 'autodescription' ),
						'https://developers.google.com/search/docs/advanced/crawling/301-redirects'
					);
					?>
				</label>
			</th>
			<td>
				<input type=url name="autodescription-meta[redirect]" id="autodescription-meta[redirect]" value="<?= esc_attr( $redirect ) ?>" size=40 autocomplete=off />
			</td>
		</tr>
	</tbody>
</table>
<?php
