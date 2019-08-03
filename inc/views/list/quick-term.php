<?php
/**
 * @package The_SEO_Framework\Views\List
 * @subpackage The_SEO_Framework\Admin\Edit\List
 *
 * POST index: autodescription-quick
 */

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

$robots_settings = [
	'noindex'   => [
		'id'        => 'autodescription-quick[noindex]',
		'name'      => 'autodescription-quick[noindex]',
		'force_on'  => 'index',
		'force_off' => 'noindex',
		'label'     => __( 'Indexing', 'autodescription' ),
	],
	'nofollow'  => [
		'id'        => 'autodescription-quick[nofollow]',
		'name'      => 'autodescription-quick[nofollow]',
		'force_on'  => 'follow',
		'force_off' => 'nofollow',
		'label'     => __( 'Following', 'autodescription' ),
	],
	'noarchive' => [
		'id'        => 'autodescription-quick[noarchive]',
		'name'      => 'autodescription-quick[noarchive]',
		'force_on'  => 'archive',
		'force_off' => 'noarchive',
		'label'     => __( 'Archiving', 'autodescription' ),
	],
];

?>
<div class=tsf-quick-edit-columns>
	<fieldset class=inline-edit-col-full>
		<legend class=inline-edit-legend><?php esc_html_e( 'Visibility SEO Settings', 'autodescription' ); ?></legend>
		<div class=inline-edit-col>
			<label>
				<span class=title><?php esc_html_e( 'Canonical URL', 'autodescription' ); ?></span>
				<span class=tsf-inline-input>
					<input type=url id=autodescription-quick[canonical] name=autodescription-quick[canonical] value />
				</span>
			</label>
			<div class="inline-edit-group wp-clearfix">
				<?php
				foreach ( $robots_settings as $_setting ) :
					// This is bad accessibility, but it's exactly as bad as WP is, and we don't want to stray away from their standards.
					echo '<label class=clear>';
						printf( '<span class="title">%s</span>', esc_html( $_setting['label'] ) );
						// phpcs:disable, WordPress.Security.EscapeOutput -- make_single_select_form() escapes.
						echo $this->make_single_select_form( [
							'id'      => $_setting['id'],
							'name'    => $_setting['name'],
							'options' => [
								/* translators: %s = default option value */
								0  => __( 'Default (%s)', 'autodescription' ),
								-1 => $_setting['force_on'],
								1  => $_setting['force_off'],
							],
							'default' => 0,
						] );
						// phpcs:enable, WordPress.Security.EscapeOutput
					echo '</label>';
				endforeach;
				?>
			</div>
			<div class="inline-edit-group wp-clearfix">
				<label>
					<span class=title><?php esc_html_e( '301 Redirect URL', 'autodescription' ); ?></span>
					<span class=tsf-inline-input>
						<input type=url id=autodescription-quick[redirect] name=autodescription-quick[redirect] value />
					</span>
				</label>
			</div>
		</div>
	</fieldset>
</div>
