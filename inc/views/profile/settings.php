<?php
/**
 * @package The_SEO_Framework\Views\Profile
 * @subpackage The_SEO_Framework\Admin\User
 */

namespace The_SEO_Framework;

( \defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) ) or die;

use const The_SEO_Framework\ROBOTS_IGNORE_SETTINGS;

use The_SEO_Framework\Admin\Settings\Layout\HTML;

// phpcs:disable WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * The SEO Framework plugin
 * Copyright (C) 2017 - 2025 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

$author_fediverse  = Data\Plugin\User::get_meta_item( 'fediverse_page', $user->ID );
$creator_fediverse = Data\Plugin::get_option( 'fediverse_creator' );

if ( $author_fediverse ) {
	$fediverse_url_placeholder = Meta\Fediverse::get_profile_url( $author_fediverse );
} elseif ( $creator_fediverse ) {
	$fediverse_url_placeholder = Meta\Fediverse::get_profile_url(
		$creator_fediverse,
		Data\Plugin::get_option( 'fediverse_creator_url' ),
	);
} else {
	$fediverse_url_placeholder = Meta\Fediverse::get_profile_url(
		Data\Plugin::get_option( 'fediverse_site' ),
		Data\Plugin::get_option( 'fediverse_site_url' ),
	);
}

$fields = [
	'tsf-user-meta[facebook_page]'      => [
		'name'    => \__( 'Facebook profile page', 'autodescription' ),
		'type'    => 'url',
		'example' => \_x( 'https://www.facebook.com/YourPersonalProfile', 'Example Facebook Personal URL', 'autodescription' ),
		'value'   => Data\Plugin\User::get_meta_item( 'facebook_page', $user->ID ),
		'class'   => '',
	],
	'tsf-user-meta[fediverse_page]'     => [
		'name'        => \__( 'Fediverse profile', 'autodescription' ),
		'type'        => 'text',
		'placeholder' =>
			   $creator_fediverse
			?: Data\Plugin::get_option( 'fediverse_site' ),
		'example'     => \_x( '@your-username@example.social', 'Fediverse handle', 'autodescription' ),
		'value'       => $author_fediverse,
		'class'       => 'ltr',
	],
	'tsf-user-meta[fediverse_page_url]' => [
		'name'        => \__( 'Fediverse profile URL', 'autodescription' ),
		'type'        => 'url',
		'placeholder' => $fediverse_url_placeholder,
		'example'     => \_x( 'https://example.social/@your-username', 'Fediverse profile URL', 'autodescription' ),
		'value'       => Data\Plugin\User::get_meta_item( 'fediverse_page_url', $user->ID ),
		'class'       => 'ltr',
	],
	'tsf-user-meta[twitter_page]'       => [
		'name'    => \__( 'X profile handle', 'autodescription' ),
		'type'    => 'text',
		'example' => \_x( '@username', 'X @username', 'autodescription' ),
		'value'   => Data\Plugin\User::get_meta_item( 'twitter_page', $user->ID ),
		'class'   => 'ltr',
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
				placeholder="<?= \esc_attr( $labels['placeholder'] ?? '' ) ?>"
				class="regular-text <?= \esc_attr( $labels['class'] ) ?>" />
			<p class=description><?php \esc_html_e( 'This may be shown publicly.', 'autodescription' ); ?></p>
			<p class=description><?php
				printf(
					/* translators: %s = example value */
					\esc_html__( 'Example: %s', 'autodescription' ),
					HTML::code_wrap( $labels['example'] ),
				);
			?></p>
		</td>
	</tr>
	<?php
}
?>
</table>
<?php
