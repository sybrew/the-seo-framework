<?php
/**
 * @package The_SEO_Framework\Views\Inpost
 */

defined( 'ABSPATH' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

?>
<div class="metabox-holder columns-2">
	<div class="postbox-container-1">
		<?php
		\do_action( 'the_seo_framework_before_siteadmin_metaboxes', $this->seo_settings_page_hook );

		\do_meta_boxes( $this->seo_settings_page_hook, 'main', null );

		if ( isset( $GLOBALS['wp_meta_boxes'][ $this->seo_settings_page_hook ]['main_extra'] ) )
			\do_meta_boxes( $this->seo_settings_page_hook, 'main_extra', null );

		\do_action( 'the_seo_framework_after_siteadmin_metaboxes', $this->seo_settings_page_hook );
		?>
	</div>
	<div class="postbox-container-2">
		<?php
		\do_action( 'the_seo_framework_before_siteadmin_metaboxes_side', $this->seo_settings_page_hook );

		/**
		 * @TODO fill this in
		 * @priority low 2.9.0
		 */

		\do_action( 'the_seo_framework_after_siteadmin_metaboxes_side', $this->seo_settings_page_hook );
		?>
	</div>
</div>
<?php
