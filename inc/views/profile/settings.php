<?php
/**
 * @package The_SEO_Framework\Views\Profile
 * @subpackage The_SEO_Framework\Admin\User
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) or die;

use const \The_SEO_Framework\ROBOTS_IGNORE_SETTINGS;

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * The SEO Framework plugin
 * Copyright (C) 2017 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

// See output_setting_fields et al.
[ $user ] = $view_args;

$fields = [
	'tsf-user-meta[facebook_page]' => [
		'name'        => \__( 'Facebook profile page', 'autodescription' ),
		'type'        => 'url',
		'placeholder' => \_x( 'https://www.facebook.com/YourPersonalProfile', 'Example Facebook Personal URL', 'autodescription' ),
		'value'       => Data\Plugin\User::get_meta_item( 'facebook_page', $user->ID ),
		'class'       => '',
	],
	'tsf-user-meta[twitter_page]'  => [
		'name'        => \__( 'X profile handle', 'autodescription' ),
		'type'        => 'text',
		'placeholder' => \_x( '@your-personal-username', 'X @username', 'autodescription' ),
		'value'       => Data\Plugin\User::get_meta_item( 'twitter_page', $user->ID ),
		'class'       => 'ltr',
	],
];

?>
<h2><?php \esc_html_e( 'Authorial Info', 'autodescription' ); ?></h2>
<table class=form-table>
<?php
foreach ( $fields as $field => $labels ) {
	?>
	<tr class="user-<?= \esc_attr( $field ) ?>-wrap">
		<th><label for="<?= \esc_attr( $field ) ?>">
			<?= \esc_html( $labels['name'] ) ?>
		</label></th>
		<td>
			<input
				type="<?= \esc_attr( $labels['type'] ) ?>"
				name="<?= \esc_attr( $field ) ?>"
				id="<?= \esc_attr( $field ) ?>"
				value="<?= \esc_attr( $labels['value'] ) ?>"
				placeholder="<?= \esc_attr( $labels['placeholder'] ) ?>"
				class="regular-text <?= \esc_attr( $labels['class'] ) ?>" />
			<p class=description><?php \esc_html_e( 'This may be shown publicly.', 'autodescription' ); ?></p>
		</td>
	</tr>
	<?php
}
?>
</table>
<?php
