<?php
/**
 * @package The_SEO_Framework\Views\Admin
 * @subpackage The_SEO_Framework\Admin\Settings
 */

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

?>
<div class="wrap tsf-metaboxes">
	<form method="post" action="options.php">
		<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
		<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
		<?php settings_fields( THE_SEO_FRAMEWORK_SITE_OPTIONS ); ?>

		<div class="tsf-top-wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<p class="tsf-top-buttons">
				<?php
				submit_button( __( 'Save Settings', 'autodescription' ), 'primary', 'submit', false, [ 'id' => '' ] );
				submit_button( __( 'Reset Settings', 'autodescription' ), 'secondary tsf-js-confirm-reset', $this->get_field_name( 'tsf-settings-reset' ), false, [ 'id' => '' ] );
				?>
			</p>
		</div>

		<div class="tsf-notice-wrap">
			<?php
			do_action( 'the_seo_framework_setting_notices' );
			?>
		</div>

		<?php
		do_action( "{$this->seo_settings_page_hook}_settings_page_boxes", $this->seo_settings_page_hook );
		?>

		<div class="tsf-bottom-buttons">
			<?php
			submit_button( __( 'Save Settings', 'autodescription' ), 'primary', 'submit', false, [ 'id' => '' ] );
			submit_button( __( 'Reset Settings', 'autodescription' ), 'secondary tsf-js-confirm-reset', $this->get_field_name( 'tsf-settings-reset' ), false, [ 'id' => '' ] );
			?>
		</div>
	</form>
</div>
<?php
//= Add postbox listeners
?>
<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready( function($) {
		// close postboxes that should be closed
		$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
		// postboxes setup
		postboxes.add_postbox_toggles('<?php echo \esc_js( $this->seo_settings_page_hook ); ?>');
	});
	//]]>
</script>
<?php
