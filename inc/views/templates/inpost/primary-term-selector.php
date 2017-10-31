<?php

defined( 'ABSPATH' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

?>
<script type="text/html" id="tmpl-tsf-primary-term-selector">
	<input type="hidden" id="autodescription[_primary_term_{{data.taxonomy.name}}]" name="autodescription[_primary_term_{{data.taxonomy.name}}]" value="{{data.taxonomy.primary}}">
	<?php
	wp_nonce_field(
		$this->inpost_nonce_field . '_pt',
		$this->inpost_nonce_name . '_pt_{{data.taxonomy.name}}'
	);
	?>
</script>

<script type="text/html" id="tmpl-tsf-primary-term-selector-help">
	<span class="tsf-primary-term-selector-help-wrap">
		<?php
		$this->make_info(
			sprintf(
				/* translators: %s = term name */
				\esc_html__( 'You can set the primary %s through the buttons below.', 'autodescription' ),
				'{{data.taxonomy.name}}'
			)
		);
		?>
	</span>
</script>
<?php
