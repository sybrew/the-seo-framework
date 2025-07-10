<?php
/**
 * @package The_SEO_Framework\Views\List
 * @subpackage The_SEO_Framework\Admin\Edit\List
 *
 * POST index: autodescription-bulk
 */

namespace The_SEO_Framework;

( \defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) ) or die;

use The_SEO_Framework\Admin\Settings\Layout\Form;

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

// See display_bulk_edit_fields
[ $post_type, $taxonomy ] = $view_args;

$robots_settings = [
	'noindex'   => [
		'id'        => 'autodescription-bulk[noindex]',
		'name'      => 'autodescription-bulk[noindex]',
		'force_on'  => 'index',
		'force_off' => 'noindex',
		'label'     => \__( 'Indexing', 'autodescription' ),
	],
	'nofollow'  => [
		'id'        => 'autodescription-bulk[nofollow]',
		'name'      => 'autodescription-bulk[nofollow]',
		'force_on'  => 'follow',
		'force_off' => 'nofollow',
		'label'     => \__( 'Link following', 'autodescription' ),
	],
	'noarchive' => [
		'id'        => 'autodescription-bulk[noarchive]',
		'name'      => 'autodescription-bulk[noarchive]',
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
	 * @param string $post_type The current post type.
	 * @param string $taxonomy  The current taxonomy type (if any).
	 */
	\do_action( 'the_seo_framework_before_bulk_edit', $post_type, $taxonomy );
	?>
	<fieldset class=inline-edit-col-left>
		<legend class=inline-edit-legend><?php \esc_html_e( 'Visibility SEO Settings', 'autodescription' ); ?></legend>
		<div class=inline-edit-col>
			<div class="inline-edit-group wp-clearfix">
				<?php
				$_no_change_i18n      = \__( '&mdash; No Change &mdash;', 'default' );
				$_default_unkown_i18n = \__( 'Default (unknown)', 'autodescription' );

				foreach ( $robots_settings as $_setting ) {
					// This is bad accessibility, but it's exactly as bad as WP is, and we don't want to stray away from their standards.
					echo '<label class=clear>';
						printf( '<span class=title>%s</span>', \esc_html( $_setting['label'] ) );
						// phpcs:disable, WordPress.Security.EscapeOutput -- make_single_select_form() escapes.
						echo Form::make_single_select_form( [
							'id'       => $_setting['id'],
							'name'     => $_setting['name'],
							'options'  => [
								'nochange' => $_no_change_i18n,
								0          => $_default_unkown_i18n,
								-1         => $_setting['force_on'],
								1          => $_setting['force_off'],
							],
							'selected' => 'nochange',
						] );
						// phpcs:enable, WordPress.Security.EscapeOutput
					echo '</label>';
				}
				?>
			</div>
		</div>
	</fieldset>
	<?php
	// Add primary term selection for hierarchical taxonomies
	$hierarchical_taxonomies = $post_type ? \The_SEO_Framework\Helper\Taxonomy::get_hierarchical( 'names', $post_type ) : [];
	
	if ( $hierarchical_taxonomies ) :
	?>
	<fieldset class=inline-edit-col-left>
		<legend class=inline-edit-legend><?php \esc_html_e( 'Primary Term Settings', 'autodescription' ); ?></legend>
		<div class=inline-edit-col>
			<div class="inline-edit-group wp-clearfix">
				<?php
				foreach ( $hierarchical_taxonomies as $taxonomy_name ) {
					$taxonomy = \get_taxonomy( $taxonomy_name );
					if ( ! $taxonomy ) continue;
					
					echo '<label class=clear>';
						printf( '<span class=title>%s</span>', \esc_html( $taxonomy->labels->singular_name ) );
						printf(
							'<select id=autodescription-bulk[primary_term_%s] name=autodescription-bulk[primary_term_%s]>',
							\esc_attr( $taxonomy_name ),
							\esc_attr( $taxonomy_name )
						);
						printf(
							'<option value="nochange">%s</option>',
							\esc_html( \__( '&mdash; No Change &mdash;', 'default' ) )
						);
						printf(
							'<option value="0">%s</option>',
							\esc_html( sprintf( \__( 'None (Clear primary %s)', 'autodescription' ), strtolower( $taxonomy->labels->singular_name ) ) )
						);
						
						// Note: Additional options will be populated via JavaScript
						// based on WordPress existing term data to avoid expensive queries
						
						echo '</select>';
					echo '</label>';
				}
				?>
			</div>
		</div>
	</fieldset>
	<?php
	endif;
	
	/**
	 * @since 4.0.5
	 * @param string $post_type The current post type.
	 * @param string $taxonomy  The current taxonomy type (if any).
	 */
	\do_action( 'the_seo_framework_after_bulk_edit', $post_type, $taxonomy );
	?>
</div>
