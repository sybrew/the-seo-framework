<?php
/**
 * @package The_SEO_Framework\Templates\Settings
 * @subpackage The_SEO_Framework\Admin\Settings
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

use \The_SEO_Framework\Interpreters\HTML;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and The_SEO_Framework\Builders\Scripts::verify( $_secret ) or die;

?>
<script type="text/html" id="tmpl-tsf-disabled-post-type-help">
	<span class="tsf-post-type-warning">
		<?php
		HTML::make_info(
			esc_html__( "This post type is excluded, so this option won't work.", 'autodescription' )
		);
		?>
	</span>
</script>

<script type="text/html" id="tmpl-tsf-disabled-taxonomy-help">
	<span class="tsf-taxonomy-warning">
		<?php
		HTML::make_info(
			esc_html__( "This taxonomy is excluded, so this option won't work.", 'autodescription' )
		);
		?>
	</span>
</script>

<script type="text/html" id="tmpl-tsf-disabled-taxonomy-from-pt-help">
	<span class="tsf-taxonomy-from-pt-warning">
		<?php
		HTML::make_info(
			esc_html__( "This taxonomy's post types are also excluded, so this option won't have any effect.", 'autodescription' )
		);
		?>
	</span>
</script>

<script type="text/html" id="tmpl-tsf-disabled-title-additions-help-social">
	<span class="tsf-title-additions-warning-social">
		<?php
		HTML::make_info(
			esc_html__( 'The site title is already removed from meta titles, so this option only affects the homepage.', 'autodescription' )
		);
		?>
	</span>
</script>

<script type="text/html" id="tmpl-tsf-robots-pt-help">
	<span class="tsf-taxonomy-from-pt-robots-warning">
		<?php
		HTML::make_info(
			esc_html__( "This taxonomy inherited the state from the post type, so this option won't have any effect.", 'autodescription' )
		);
		?>
	</span>
</script>

<script type="text/html" id="tmpl-tsf-disabled-title-additions-help">
	<span class="tsf-title-additions-warning">
		<?php
		HTML::make_info(
			esc_html__( "Site titles are removed globally, so this option won't work.", 'autodescription' )
		);
		?>
	</span>
</script>
<?php
