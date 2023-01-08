<?php
/**
 * @package The_SEO_Framework\Views\Admin
 * @subpackage The_SEO_Framework\Admin\Settings
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

use The_SEO_Framework\Interpreters\HTML,
	The_SEO_Framework\Interpreters\Settings_Input as Input;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and tsf()->_verify_include_secret( $_secret ) or die;

if ( function_exists( 'tsf_extension_manager' )
	&& in_array(
		tsf_extension_manager()->seo_extensions_page_slug ?? null,
		array_column( $GLOBALS['submenu'][ $this->seo_settings_page_slug ] ?? [], 2 ),
		true
	)
) {
	$_extensions_button = sprintf(
		'<a href="%s" class=button>%s</a>',
		menu_page_url( tsf_extension_manager()->seo_extensions_page_slug, false ),
		esc_html_x( 'Extensions', 'Plugin extensions', 'autodescription' )
	);
} else {
	$_extensions_button = $this->_display_extension_suggestions() ? sprintf(
		'<a href="%s" class=button rel="noreferrer noopener" target=_blank>%s</a>',
		'https://theseoframework.com/?p=3599',
		esc_html_x( 'Extensions', 'Plugin extensions', 'autodescription' )
	) : '';
}

$_save_button = get_submit_button(
	__( 'Save Settings', 'autodescription' ),
	[ 'primary' ],
	'submit',
	false,
	[ 'id' => '' ] // we ouput this twice, don't set ID.
);

$_ays_reset    = esc_js( __( 'Are you sure you want to reset all SEO settings to their defaults?', 'autodescription' ) );
$_reset_button = get_submit_button(
	__( 'Reset Settings', 'autodescription' ),
	[ 'secondary' ],
	Input::get_field_name( 'tsf-settings-reset' ),
	false,
	[
		'id'      => '', // we ouput this twice, don't set ID.
		'onclick' => "return confirm(`{$_ays_reset}`)", // this passes through esc_attr() unscathed.
	]
);

?>
<div class="wrap tsf-metaboxes">
	<form method=post action=options.php autocomplete=off data-form-type=other>
		<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
		<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
		<?php settings_fields( THE_SEO_FRAMEWORK_SITE_OPTIONS ); ?>

		<div class=tsf-top-wrap>
			<h1><?= esc_html( get_admin_page_title() ) ?></h1>
			<div class="tsf-top-buttons tsf-end">
				<?php
				// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- submit_button() escapes (mostly...)
				echo $_save_button, $_reset_button, $_extensions_button;
				?>
			</div>
		</div>

		<hr class=wp-header-end>

		<div class=tsf-notice-wrap>
			<?php
			do_action( 'the_seo_framework_setting_notices' );
			?>
		</div>

		<?php
		do_action( "{$this->seo_settings_page_hook}_settings_page_boxes", $this->seo_settings_page_hook );
		?>

		<div class=tsf-bottom-wrap>
			<div class="tsf-bottom-buttons tsf-start">
				<?php
				// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- submit_button() escapes (mostly...)
				echo $_extensions_button;
				?>
			</div>
			<div class="tsf-bottom-buttons tsf-end">
				<?php
				// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- submit_button() escapes (mostly...)
				echo $_save_button;
				?>
			</div>
		</div>
	</form>
</div>
<script>
	//<![CDATA[
	jQuery( document ).ready( function( $ ) {
		// close postboxes that should be closed
		$( '.if-js-closed' ).removeClass( 'if-js-closed' ).addClass( 'closed' );
		// postboxes setup
		postboxes.add_postbox_toggles('<?= esc_js( $this->seo_settings_page_hook ) ?>');
	} );
	//]]>
</script>
<?php
