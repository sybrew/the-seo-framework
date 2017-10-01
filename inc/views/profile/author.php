<?php
/**
 * @package The_SEO_Framework\Views\Profile
 */

defined( 'ABSPATH' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

?>
<h2><?php esc_html_e( 'Authorial Info', 'autodescription' ); ?></h2>
<table class="form-table">
<?php
foreach ( $fields as $field => $labels ) :
	?>
	<tr class="user-<?php echo $field; ?>-wrap">
		<th><label for="<?php echo $field; ?>">
			<?php echo esc_html( $labels->name ); ?>
		</label></th>
		<td>
			<input type="text" name="<?php echo $field; ?>" id="<?php echo $field; ?>" value="<?php echo esc_attr( $labels->value ) ?>" placeholder="<?php echo esc_attr( $labels->placeholder ) ?>" class="regular-text" />
		</td>
	</tr>
	<?php
endforeach;
?>
</table>
<?php
