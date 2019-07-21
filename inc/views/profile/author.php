<?php
/**
 * @package The_SEO_Framework\Views\Profile
 * @subpackage The_SEO_Framework\Admin\Profile
 */

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

?>
<h2><?php esc_html_e( 'Authorial Info', 'autodescription' ); ?></h2>
<table class="form-table">
<?php
foreach ( $fields as $field => $labels ) :
	?>
	<tr class="user-<?php echo esc_attr( $field ); ?>-wrap">
		<th><label for="<?php echo esc_attr( $field ); ?>">
			<?php echo esc_html( $labels->name ); ?>
		</label></th>
		<td>
			<input
				type="<?php echo esc_attr( $labels->type ); ?>"
				name="<?php echo esc_attr( $field ); ?>"
				id="<?php echo esc_attr( $field ); ?>"
				value="<?php echo esc_attr( $labels->value ); ?>"
				placeholder="<?php echo esc_attr( $labels->placeholder ); ?>"
				class="regular-text <?php echo esc_attr( $labels->class ); ?>" />
			<p class="description"><?php esc_html_e( 'This may be shown publicly.', 'autodescription' ); ?></p>
		</td>
	</tr>
	<?php
endforeach;
?>
</table>
<?php
