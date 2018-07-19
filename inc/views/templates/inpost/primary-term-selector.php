<?php

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and The_SEO_Framework\Builders\Scripts::verify( $_secret ) or die;

$tsf = the_seo_framework();

?>
<script type="text/html" id="tmpl-tsf-primary-term-selector">
	<input type="hidden" id="autodescription[_primary_term_{{data.taxonomy.name}}]" name="autodescription[_primary_term_{{data.taxonomy.name}}]" value="{{data.taxonomy.primary}}">
	<?php
	wp_nonce_field(
		$tsf->inpost_nonce_field . '_pt',
		$tsf->inpost_nonce_name . '_pt_{{data.taxonomy.name}}'
	);
	?>
</script>

<script type="text/html" id="tmpl-tsf-primary-term-selector-help">
	<span class="tsf-primary-term-selector-help-wrap">
		<?php
		$tsf->make_info(
			sprintf(
				/* translators: %s = term name */
				\esc_html__( 'You can set the primary %s with the buttons below.', 'autodescription' ),
				'{{data.taxonomy.i18n.name}}'
			)
		);
		?>
	</span>
</script>
<?php
