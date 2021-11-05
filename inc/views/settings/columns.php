<?php
/**
 * @package The_SEO_Framework\Views\Admin
 * @subpackage The_SEO_Framework\Admin\Settings
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and tsf()->_verify_include_secret( $_secret ) or die;

?>
<div class="metabox-holder columns-2">
	<div class="postbox-container-1">
		<?php
		do_action( 'the_seo_framework_before_siteadmin_metaboxes', $this->seo_settings_page_hook );

		do_meta_boxes( $this->seo_settings_page_hook, 'main', null );

		if ( isset( $GLOBALS['wp_meta_boxes'][ $this->seo_settings_page_hook ]['main_extra'] ) )
			do_meta_boxes( $this->seo_settings_page_hook, 'main_extra', null );

		do_action( 'the_seo_framework_after_siteadmin_metaboxes', $this->seo_settings_page_hook );
		?>
	</div>
	<div class="postbox-container-2">
		<?php
		do_action( 'the_seo_framework_before_siteadmin_metaboxes_side', $this->seo_settings_page_hook );

		/**
		 * @TODO fill this in...?
		 */

		do_action( 'the_seo_framework_after_siteadmin_metaboxes_side', $this->seo_settings_page_hook );
		?>
	</div>
</div>
<?php
