<?php
/**
 * @package The_SEO_Framework\Views\Inpost
 */

defined( 'ABSPATH' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

?>
<div class="wrap tsf-metaboxes">
	<form method="post" action="options.php">
		<?php \wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
		<?php \wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
		<?php \settings_fields( $this->settings_field ); ?>

		<div class="tsf-top-wrap">
			<h1><?php echo \esc_html( \get_admin_page_title() ); ?></h1>
			<p class="tsf-top-buttons">
				<?php
				\submit_button( $this->page_defaults['save_button_text'], 'primary', 'submit', false, array( 'id' => '' ) );
				\submit_button( $this->page_defaults['reset_button_text'], 'secondary tsf-js-confirm-reset', $this->get_field_name( 'tsf-settings-reset' ), false, array( 'id' => '' ) );
				?>
			</p>
		</div>

		<?php \do_action( "{$this->seo_settings_page_hook}_settings_page_boxes", $this->seo_settings_page_hook ); ?>

		<div class="tsf-bottom-buttons">
			<?php
			\submit_button( $this->page_defaults['save_button_text'], 'primary', 'submit', false, array( 'id' => '' ) );
			\submit_button( $this->page_defaults['reset_button_text'], 'secondary tsf-js-confirm-reset', $this->get_field_name( 'tsf-settings-reset' ), false, array( 'id' => '' ) );
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
