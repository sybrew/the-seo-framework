<?php
/**
 * @package The_SEO_Framework\Templates\Inpost
 * @subpackage The_SEO_Framework\Admin\Edit\Inpost
 */

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

?>
<script type=text/html id=tmpl-tsf-primary-term-selector>
	<input type=hidden id="autodescription[_primary_term_{{data.taxonomy.name}}]" name="autodescription[_primary_term_{{data.taxonomy.name}}]" value="{{data.taxonomy.primary}}">
	<?php
	wp_nonce_field(
		\The_SEO_Framework\Data\Admin\Post::$nonce_action . '_pt',
		\The_SEO_Framework\Data\Admin\Post::$nonce_name . '_pt_{{data.taxonomy.name}}'
	);
	?>
</script>

<script type=text/html id=tmpl-tsf-primary-term-selector-help>
	<span class=tsf-primary-term-selector-help-wrap>
		<?php
		\The_SEO_Framework\Interpreters\HTML::make_info( // Lacking import OK.
			sprintf(
				/* translators: %s = term name */
				esc_html__( 'The buttons below are for primary %s selection.', 'autodescription' ),
				'{{data.taxonomy.i18n.name}}'
			)
		);
		?>
	</span>
</script>
<?php
