<?php
/**
 * @package The_SEO_Framework\Views\List
 * @subpackage The_SEO_Framework\Admin\Edit\List
 *
 * POST index: autodescription-quick
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) or die;

use \The_SEO_Framework\Admin\Settings\Layout\{
	Form,
	Input,
};

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// See display_quick_edit_fields
[ $post_type, $taxonomy ] = $view_args;

$robots_settings = [
	'noindex'   => [
		'id'        => 'autodescription-quick[noindex]',
		'name'      => 'autodescription-quick[noindex]',
		'force_on'  => 'index',
		'force_off' => 'noindex',
		'label'     => \__( 'Indexing', 'autodescription' ),
	],
	'nofollow'  => [
		'id'        => 'autodescription-quick[nofollow]',
		'name'      => 'autodescription-quick[nofollow]',
		'force_on'  => 'follow',
		'force_off' => 'nofollow',
		'label'     => \__( 'Following', 'autodescription' ),
	],
	'noarchive' => [
		'id'        => 'autodescription-quick[noarchive]',
		'name'      => 'autodescription-quick[noarchive]',
		'force_on'  => 'archive',
		'force_off' => 'noarchive',
		'label'     => \__( 'Archiving', 'autodescription' ),
	],
];

?>
<div class=tsf-quick-edit-columns>
	<?php
	/**
	 * @since 4.0.5
	 * @param string $post_type The post type slug, or current screen name if this is a taxonomy list table.
	 * @param string $taxonomy  The current taxonomy type (if any).
	 */
	\do_action( 'the_seo_framework_before_quick_edit', $post_type, $taxonomy );
	?>
	<fieldset class=inline-edit-col-full>
		<legend class=inline-edit-legend><?php \esc_html_e( 'General SEO Settings', 'autodescription' ); ?></legend>
		<div class="inline-edit-col tsf-le-wide-complex-column">
			<label for=autodescription-quick[doctitle]>
				<span class=title><?php \esc_html_e( 'Meta Title', 'autodescription' ); ?></span>
			</label>
			<?php
			Data\Plugin::get_option( 'display_character_counter' )
				and Form::output_character_counter_wrap( 'autodescription-quick[doctitle]' );
			Data\Plugin::get_option( 'display_pixel_counter' )
				and Form::output_pixel_counter_wrap( 'autodescription-quick[doctitle]', 'title' );
			?>
			<div class="tsf-pad-input tsf-title-wrap">
				<input type=text id=autodescription-quick[doctitle] name=autodescription-quick[doctitle]>
				<?php
				Input::output_js_title_data( 'autodescription-quick[doctitle]', [] );
				?>
			</div>
		</div>
		<div class="inline-edit-col tsf-le-wide-complex-column">
			<label for=autodescription-quick[description]>
				<span class=title><?php \esc_html_e( 'Meta Description', 'autodescription' ); ?></span>
			</label>
			<?php
			Data\Plugin::get_option( 'display_character_counter' )
				and Form::output_character_counter_wrap( 'autodescription-quick[description]' );
			Data\Plugin::get_option( 'display_pixel_counter' )
				and Form::output_pixel_counter_wrap( 'autodescription-quick[description]', 'description' );
			?>
			<div class=tsf-pad-input>
				<textarea id=autodescription-quick[description] name=autodescription-quick[description] rows=3 cols=22></textarea>
				<?php
				Input::output_js_description_data( 'autodescription-quick[description]', [] );
				?>
			</div>
		</div>
	</fieldset>
	<fieldset class=inline-edit-col-full>
		<legend class=inline-edit-legend><?php \esc_html_e( 'Visibility SEO Settings', 'autodescription' ); ?></legend>
		<div class=inline-edit-col>
			<label>
				<span class=title><?php \esc_html_e( 'Canonical URL', 'autodescription' ); ?></span>
				<span class=tsf-inline-input>
					<input type=url id=autodescription-quick[canonical] name=autodescription-quick[canonical]>
				</span>
			</label>
			<div class="inline-edit-group wp-clearfix">
				<?php
				/* translators: %s = default option value */
				$_default_i18n = \__( 'Default (%s)', 'autodescription' );

				foreach ( $robots_settings as $_setting ) {
					// This is bad accessibility, but it's exactly as bad as WP is, and we don't want to stray away from their standards.
					echo '<label class=clear>';
						printf( '<span class=title>%s</span>', \esc_html( $_setting['label'] ) );
						// phpcs:disable, WordPress.Security.EscapeOutput -- make_single_select_form() escapes.
						echo Form::make_single_select_form( [
							'id'       => $_setting['id'],
							'name'     => $_setting['name'],
							'options'  => [
								/* translators: %s = default option value */
								0  => $_default_i18n,
								-1 => $_setting['force_on'],
								1  => $_setting['force_off'],
							],
							'selected' => 0,
						] );
						// phpcs:enable, WordPress.Security.EscapeOutput
					echo '</label>';
				}
				?>
			</div>
			<div class="inline-edit-group wp-clearfix">
				<label>
					<span class=title><?php \esc_html_e( '301 Redirect URL', 'autodescription' ); ?></span>
					<span class=tsf-inline-input>
						<input type=url id=autodescription-quick[redirect] name=autodescription-quick[redirect]>
					</span>
				</label>
			</div>
		</div>
	</fieldset>
	<?php
	/**
	 * @since 4.0.5
	 * @param string $post_type The post type slug, or current screen name if this is a taxonomy list table.
	 * @param string $taxonomy  The current taxonomy type (if any).
	 */
	\do_action( 'the_seo_framework_after_quick_edit', $post_type, $taxonomy );
	?>
</div>
