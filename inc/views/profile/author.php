<?php
/**
 * @package The_SEO_Framework\Views\Profile
 * @subpackage The_SEO_Framework\Admin\Edit\User
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and tsf()->_verify_include_secret( $_secret ) or die;

$fields = [
	'tsf-user-meta[facebook_page]' => [
		'name'        => __( 'Facebook profile page', 'autodescription' ),
		'type'        => 'url',
		'placeholder' => _x( 'https://www.facebook.com/YourPersonalProfile', 'Example Facebook Personal URL', 'autodescription' ),
		'value'       => $this->get_user_meta_item( 'facebook_page', $user->ID ),
		'class'       => '',
	],
	'tsf-user-meta[twitter_page]'  => [
		'name'        => __( 'Twitter profile name', 'autodescription' ),
		'type'        => 'text',
		'placeholder' => _x( '@your-personal-username', 'Twitter @username', 'autodescription' ),
		'value'       => $this->get_user_meta_item( 'twitter_page', $user->ID ),
		'class'       => 'ltr',
	],
];

?>
<h2><?php esc_html_e( 'Authorial Info', 'autodescription' ); ?></h2>
<table class=form-table>
<?php
foreach ( $fields as $field => $labels ) :
	?>
	<tr class="user-<?= esc_attr( $field ) ?>-wrap">
		<th><label for="<?= esc_attr( $field ) ?>">
			<?= esc_html( $labels['name'] ) ?>
		</label></th>
		<td>
			<input
				type="<?= esc_attr( $labels['type'] ) ?>"
				name="<?= esc_attr( $field ) ?>"
				id="<?= esc_attr( $field ) ?>"
				value="<?= esc_attr( $labels['value'] ) ?>"
				placeholder="<?= esc_attr( $labels['placeholder'] ) ?>"
				class="regular-text <?= esc_attr( $labels['class'] ) ?>" />
			<p class=description><?php esc_html_e( 'This may be shown publicly.', 'autodescription' ); ?></p>
		</td>
	</tr>
	<?php
endforeach;
?>
</table>
<?php
