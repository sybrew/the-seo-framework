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

<script type="text/html" id="tmpl-tsf-disabled-taxonomy-help">
	<span class="tsf-taxonomy-warning">
		<?php
		the_seo_framework()->make_info(
			\esc_html__( "This taxonomy is disabled, so this option won't work.", 'autodescription' )
		);
		?>
	</span>
</script>

<script type="text/html" id="tmpl-tsf-disabled-taxonomy-from-pt-help">
	<span class="tsf-taxonomy-from-pt-warning">
		<?php
		the_seo_framework()->make_info(
			\esc_html__( "This taxonomy's post types are also disabled, so this option won't have any effect.", 'autodescription' )
		);
		?>
	</span>
</script>

<script type="text/html" id="tmpl-tsf-disabled-title-additions-help">
	<span class="tsf-title-additions-warning">
		<?php
		the_seo_framework()->make_info(
			\esc_html__( 'The site title is already removed from meta titles, so this option only affects the homepage.', 'autodescription' )
		);
		?>
	</span>
</script>
<?php
