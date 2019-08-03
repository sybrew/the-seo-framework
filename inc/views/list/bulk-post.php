<?php
/**
 * @package The_SEO_Framework\Views\List
 * @subpackage The_SEO_Framework\Admin\Edit\List
 *
 * POST index: autodescription-bulk
 */

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

$robots_settings = [
	'noindex'   => [
		'id'        => 'autodescription-bulk[noindex]',
		'name'      => 'autodescription-bulk[noindex]',
		'force_on'  => 'index',
		'force_off' => 'noindex',
		'label'     => __( 'Indexing', 'autodescription' ),
	],
	'nofollow'  => [
		'id'        => 'autodescription-bulk[nofollow]',
		'name'      => 'autodescription-bulk[nofollow]',
		'force_on'  => 'follow',
		'force_off' => 'nofollow',
		'label'     => __( 'Link following', 'autodescription' ),
	],
	'noarchive' => [
		'id'        => 'autodescription-bulk[noarchive]',
		'name'      => 'autodescription-bulk[noarchive]',
		'force_on'  => 'archive',
		'force_off' => 'noarchive',
		'label'     => __( 'Archiving', 'autodescription' ),
	],
];

?>
<div class=tsf-quick-edit-columns>
	<fieldset class=inline-edit-col-left>
		<legend class=inline-edit-legend><?php esc_html_e( 'Visibility SEO Settings', 'autodescription' ); ?></legend>
		<div class=inline-edit-col>
			<div class="inline-edit-group wp-clearfix">
				<?php
				foreach ( $robots_settings as $_setting ) :
					// This is bad accessibility, but it's exactly as bad as WP is, and we don't want to stray away from their standards.
					echo '<label class=clear>';
						printf( '<span class=title>%s</span>', esc_html( $_setting['label'] ) );
						// phpcs:disable, WordPress.Security.EscapeOutput -- make_single_select_form() escapes.
						echo $this->make_single_select_form( [
							'id'      => $_setting['id'],
							'name'    => $_setting['name'],
							'options' => [
								'nochange' => __( '&mdash; No Change &mdash;', 'default' ),
								0          => __( 'Default (unknown)', 'autodescription' ),
								-1         => $_setting['force_on'],
								1          => $_setting['force_off'],
							],
							'default' => 'nochange',
						] );
						// phpcs:enable, WordPress.Security.EscapeOutput
					echo '</label>';
				endforeach;
				?>
			</div>
		</div>
	</fieldset>
</div>
