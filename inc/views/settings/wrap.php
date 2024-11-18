<?php
/**
 * @package The_SEO_Framework\Views\Admin
 * @subpackage The_SEO_Framework\Admin\Settings
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) or die;

use \The_SEO_Framework\Admin\Settings\Layout\Input;

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

if (
	   \function_exists( 'tsf_extension_manager' )
	&& \in_array(
		\tsf_extension_manager()->seo_extensions_page_slug ?? null,
		array_column( $GLOBALS['submenu'][ \THE_SEO_FRAMEWORK_SITE_OPTIONS_SLUG ] ?? [], 2 ),
		true,
	)
) {
	$_extensions_button = \sprintf(
		'<a href="%s" class=button>%s</a>',
		// menu_page_url() escapes
		\menu_page_url( \tsf_extension_manager()->seo_extensions_page_slug, false ),
		\esc_html_x( 'Extensions', 'Plugin extensions', 'autodescription' ),
	);
} else {
	$_extensions_button = Admin\Utils::display_extension_suggestions() ? \sprintf(
		'<a href="%s" class=button rel="noreferrer noopener" target=_blank>%s</a>',
		'https://theseoframework.com/?p=3599',
		\esc_html_x( 'Extensions', 'Plugin extensions', 'autodescription' )
	) : '';
}

$_save_button = \get_submit_button(
	\__( 'Save Settings', 'autodescription' ),
	[ 'primary' ],
	'submit',
	false,
	[ 'id' => '' ] // we output this twice, don't set ID.
);

$_ays_reset    = \esc_js( \__( 'Are you sure you want to reset all SEO settings to their defaults?', 'autodescription' ) );
$_reset_button = \get_submit_button(
	\__( 'Reset Settings', 'autodescription' ),
	[ 'secondary' ],
	Input::get_field_name( 'tsf-settings-reset' ),
	false,
	[
		'id'      => '', // we output this twice, don't set ID.
		'onclick' => "return confirm(`{$_ays_reset}`)", // this passes through \esc_attr() unscathed.
	],
);

$hook_name = Admin\Menu::get_page_hook_name();

?>
<div class="wrap tsf-metaboxes">
	<form id=tsf-settings method=post action=options.php autocomplete=off data-form-type=other>
		<?php \wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
		<?php \wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
		<?php \settings_fields( \THE_SEO_FRAMEWORK_SITE_OPTIONS ); ?>

		<div class=tsf-top-wrap>
			<h1><?= \esc_html( \get_admin_page_title() ) ?></h1>
			<div class="tsf-top-buttons tsf-end">
				<?php
				// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- submit_button() escapes (mostly...)
				echo $_save_button, $_reset_button, $_extensions_button;
				?>
			</div>
		</div>

		<hr class=wp-header-end>

		<?php
		\do_action( 'the_seo_framework_setting_notices' );
		?>

		<?php
		\do_action( "{$hook_name}_settings_page_boxes", $hook_name );
		?>

		<div class=tsf-bottom-wrap>
			<div class="tsf-bottom-buttons tsf-start">
				<?php
				// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- submit_button() escapes (mostly...)
				echo $_extensions_button;
				?>
			</div>
			<div class="tsf-bottom-buttons tsf-end">
				<?php
				// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- submit_button() escapes (mostly...)
				echo $_save_button;
				?>
			</div>
		</div>
	</form>
</div>
<script>
	addEventListener( 'load', () => {
		postboxes.add_postbox_toggles( '<?= \esc_js( $hook_name ) ?>' );
	} );
</script>
<?php
