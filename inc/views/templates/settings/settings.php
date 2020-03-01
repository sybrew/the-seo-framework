<?php
/**
 * @package The_SEO_Framework\Templates\Settings
 * @subpackage The_SEO_Framework\Admin\Settings
 */

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and The_SEO_Framework\Builders\Scripts::verify( $_secret ) or die;

?>
<script type="text/html" id="tmpl-tsf-disabled-post-type-help">
	<span class="tsf-post-type-warning">
		<?php
		the_seo_framework()->make_info(
			\esc_html__( "This post type is disabled, so this option won't work.", 'autodescription' )
		);
		?>
	</span>
</script>
<?php
