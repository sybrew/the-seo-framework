<?php
/**
 * @package The_SEO_Framework\Views\Inpost
 */

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

//* Get the language the Google page should assume.
$language = $this->google_language();

//* Fetch Term ID and taxonomy.
$term_id  = $object->term_id;
// phpcs:ignore -- WordPress.WP.GlobalVariablesOverride.Prohibited: No, we're not in global scope. We're not overriding globals.
$taxonomy = $object->taxonomy;
$meta     = $this->get_term_meta( $object->term_id );

// phpcs:ignore -- WordPress.WP.GlobalVariablesOverride.Prohibited: No, we're not in global scope. We're not overriding globals.
$title       = isset( $meta['doctitle'] ) ? $meta['doctitle'] : '';
$description = isset( $meta['description'] ) ? $meta['description'] : '';
$canonical   = isset( $meta['canonical'] ) ? $meta['canonical'] : '';
$noindex     = isset( $meta['noindex'] ) ? $meta['noindex'] : '';
$nofollow    = isset( $meta['nofollow'] ) ? $meta['nofollow'] : '';
$noarchive   = isset( $meta['noarchive'] ) ? $meta['noarchive'] : '';
$redirect    = isset( $meta['redirect'] ) ? $meta['redirect'] : '';

$social_image_url = isset( $meta['social_image_url'] ) ? $meta['social_image_url'] : '';
$social_image_id  = isset( $meta['social_image_id'] ) ? $meta['social_image_id'] : '';

$og_title       = isset( $meta['og_title'] ) ? $meta['og_title'] : '';
$og_description = isset( $meta['og_description'] ) ? $meta['og_description'] : '';
$tw_title       = isset( $meta['tw_title'] ) ? $meta['tw_title'] : '';
$tw_description = isset( $meta['tw_description'] ) ? $meta['tw_description'] : '';

$_generator_args = [
	'id'       => $term_id,
	'taxonomy' => $taxonomy,
];

$show_og = (bool) $this->get_option( 'og_tags' );
$show_tw = (bool) $this->get_option( 'twitter_tags' );

$title_placeholder       = $this->get_generated_title( $_generator_args );
$description_placeholder = $this->get_generated_description( $_generator_args );

//! OG input falls back to default input.
$og_title_placeholder       = $this->get_generated_open_graph_title( $_generator_args );
$og_description_placeholder = $description ?: $this->get_generated_open_graph_description( $_generator_args );

//! Twitter input falls back to OG input.
$tw_title_placeholder       = $og_title ?: $og_title_placeholder;
$tw_description_placeholder = $og_description ?: $description ?: $this->get_generated_twitter_description( $_generator_args );

$canonical_placeholder = $this->create_canonical_url( $_generator_args ); // implies get_custom_field = false
$robots_defaults       = $this->robots_meta( $_generator_args, The_SEO_Framework\ROBOTS_IGNORE_PROTECTION | The_SEO_Framework\ROBOTS_IGNORE_SETTINGS );

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
			'https://support.google.com/webmasters/answer/93710?hl=' . $language,
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
			'https://support.google.com/webmasters/answer/96569?hl=' . $language,
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
			'https://support.google.com/webmasters/answer/79812?hl=' . $language,
		],
	],
];

?>
<h2><?php esc_html_e( 'General SEO Settings', 'autodescription' ); ?></h2>

<table class="form-table">
	<tbody>
		<?php if ( $this->get_option( 'display_seo_bar_metabox' ) ) : ?>
		<tr class="form-field">
			<th scope="row" valign="top"><?php esc_html_e( 'Doing it Right', 'autodescription' ); ?></th>
			<td>
				<?php
				// phpcs:ignore -- get_generated_seo_bar() escapes.
				echo $this->get_generated_seo_bar( [ 'id' => $term_id, 'taxonomy' => $taxonomy ] );
				?>
			</td>
		</tr>
		<?php endif; ?>

		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="autodescription-meta[doctitle]">
					<strong><?php esc_html_e( 'Meta Title', 'autodescription' ); ?></strong>
					<?php
					echo ' ';
					$this->make_info(
						__( 'The meta title can be used to determine the title used on search engine result pages.', 'autodescription' ),
						'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#page-titles'
					);
					?>
				</label>
				<?php
				$this->get_option( 'display_character_counter' )
					and $this->output_character_counter_wrap( 'autodescription-meta[doctitle]' );
				$this->get_option( 'display_pixel_counter' )
					and $this->output_pixel_counter_wrap( 'autodescription-meta[doctitle]', 'title' );
				?>
			</th>
			<td>
				<div id="tsf-title-wrap">
					<input name="autodescription-meta[doctitle]" id="autodescription-meta[doctitle]" type="text" placeholder="<?php echo esc_attr( $title_placeholder ); ?>" value="<?php echo esc_attr( $title ); ?>" size="40" autocomplete=off />
					<?php $this->output_js_title_elements(); ?>
				</div>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="autodescription-meta[description]">
					<strong><?php esc_html_e( 'Meta Description', 'autodescription' ); ?></strong>
					<?php
					echo ' ';
					$this->make_info(
						__( 'The meta description can be used to determine the text used under the title on search engine results pages.', 'autodescription' ),
						'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#meta-descriptions'
					);
					?>
				</label>
				<?php
				$this->get_option( 'display_character_counter' )
					and $this->output_character_counter_wrap( 'autodescription-meta[description]' );
				$this->get_option( 'display_pixel_counter' )
					and $this->output_pixel_counter_wrap( 'autodescription-meta[description]', 'description' );
				?>
			</th>
			<td>
				<textarea name="autodescription-meta[description]" id="autodescription-meta[description]" placeholder="<?php echo esc_attr( $description_placeholder ); ?>" rows="4" cols="50" class="large-text"><?php echo esc_html( $description ); ?></textarea>
				<?php
				// phpcs:ignore -- output_js_description_elements() is escaped.
				echo $this->output_js_description_elements();
				?>
			</td>
		</tr>
	</tbody>
</table>

<h2><?php esc_html_e( 'Social SEO Settings', 'autodescription' ); ?></h2>

<table class="form-table">
	<tbody>
		<tr class="form-field" <?php echo $show_og ? '' : 'style=display:none'; ?>>
			<th scope="row" valign="top">
				<label for="autodescription-meta[og_title]">
					<strong><?php esc_html_e( 'Open Graph Title', 'autodescription' ); ?></strong>
				</label>
				<?php
				$this->get_option( 'display_character_counter' )
					and $this->output_character_counter_wrap( 'autodescription-meta[og_title]' );
				?>
			</th>
			<td>
				<div id="tsf-og-title-wrap">
					<input name="autodescription-meta[og_title]" id="autodescription-meta[og_title]" type="text" placeholder="<?php echo esc_attr( $og_title_placeholder ); ?>" value="<?php echo esc_attr( $og_title ); ?>" size="40" autocomplete=off />
				</div>
			</td>
		</tr>

		<tr class="form-field" <?php echo $show_og ? '' : 'style=display:none'; ?>>
			<th scope="row" valign="top">
				<label for="autodescription-meta[og_description]">
					<strong><?php esc_html_e( 'Open Graph Description', 'autodescription' ); ?></strong>
				</label>
				<?php
				$this->get_option( 'display_character_counter' )
					and $this->output_character_counter_wrap( 'autodescription-meta[og_description]' );
				?>
			</th>
			<td>
				<textarea name="autodescription-meta[og_description]" id="autodescription-meta[og_description]" placeholder="<?php echo esc_attr( $og_description_placeholder ); ?>" rows="4" cols="50" class="large-text"><?php echo esc_html( $og_description ); ?></textarea>
			</td>
		</tr>

		<tr class="form-field" <?php echo $show_tw ? '' : 'style=display:none'; ?>>
			<th scope="row" valign="top">
				<label for="autodescription-meta[tw_title]">
					<strong><?php esc_html_e( 'Twitter Title', 'autodescription' ); ?></strong>
				</label>
				<?php
				$this->get_option( 'display_character_counter' )
					and $this->output_character_counter_wrap( 'autodescription-meta[tw_title]' );
				?>
			</th>
			<td>
				<div id="tsf-tw-title-wrap">
					<input name="autodescription-meta[tw_title]" id="autodescription-meta[tw_title]" type="text" placeholder="<?php echo esc_attr( $tw_title_placeholder ); ?>" value="<?php echo esc_attr( $tw_title ); ?>" size="40" autocomplete=off />
				</div>
			</td>
		</tr>

		<tr class="form-field" <?php echo $show_tw ? '' : 'style=display:none'; ?>>
			<th scope="row" valign="top">
				<label for="autodescription-meta[tw_description]">
					<strong><?php esc_html_e( 'Twitter Description', 'autodescription' ); ?></strong>
				</label>
				<?php
				$this->get_option( 'display_character_counter' )
					and $this->output_character_counter_wrap( 'autodescription-meta[tw_description]' );
				?>
			</th>
			<td>
				<textarea name="autodescription-meta[tw_description]" id="autodescription-meta[tw_description]" placeholder="<?php echo esc_attr( $tw_description_placeholder ); ?>" rows="4" cols="50" class="large-text"><?php echo esc_html( $tw_description ); ?></textarea>
			</td>
		</tr>
	</tbody>
</table>

<h2><?php esc_html_e( 'Visibility SEO Settings', 'autodescription' ); ?></h2>

<table class="form-table">
	<tbody>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="autodescription-meta[canonical]">
					<strong><?php esc_html_e( 'Canonical URL', 'autodescription' ); ?></strong>
					<?php
					echo ' ';
					$this->make_info(
						__( 'This urges search engines to go to the outputted URL.', 'autodescription' ),
						'https://support.google.com/webmasters/answer/139066?hl=' . $language
					);
					?>
				</label>
			</th>
			<td>
				<input name="autodescription-meta[canonical]" id="autodescription-meta[canonical]" type=url placeholder="<?php echo esc_attr( $canonical_placeholder ); ?>" value="<?php echo esc_attr( $canonical ); ?>" size="40" autocomplete=off />
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row" valign="top">
				<?php
				esc_html_e( 'Robots Meta Settings', 'autodescription' );
				echo ' ';
				$this->make_info(
					__( 'These directives may urge robots not to display, follow links on, or create a cached copy of this term.', 'autodescription' ),
					'https://developers.google.com/search/reference/robots_meta_tag#valid-indexing--serving-directives'
				);
				?>
				</th>
			<td>
				<?php
				foreach ( $robots_settings as $_s ) :
					// phpcs:disable -- WordPress.Security.EscapeOutput.OutputNotEscaped, make_single_select_form() escapes.
					echo $this->make_single_select_form( [
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
					] );
					// phpcs:enable -- WordPress.Security.EscapeOutput.OutputNotEscaped
				endforeach;
				?>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="autodescription-meta[redirect]">
					<strong><?php esc_html_e( '301 Redirect URL', 'autodescription' ); ?></strong>
					<?php
					echo ' ';
					$this->make_info(
						__( 'This will force visitors to go to another URL.', 'autodescription' ),
						'https://support.google.com/webmasters/answer/93633?hl=' . $language
					);
					?>
				</label>
			</th>
			<td>
				<input name="autodescription-meta[redirect]" id="autodescription-meta[redirect]" type=url value="<?php echo esc_attr( $redirect ); ?>" size="40" autocomplete=off />
			</td>
		</tr>
	</tbody>
</table>
<?php
