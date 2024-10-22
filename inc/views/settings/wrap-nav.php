<?php
/**
 * @package The_SEO_Framework\Views\Admin
 * @subpackage The_SEO_Framework\Admin\Settings
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) or die;

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// See nav_tab_wrapper
[ $id, $tabs ] = $view_args;

/**
 * Start navigational tabs.
 * Don't output navigation if the number of tabs is 1 or lower.
 */
if ( \count( $tabs ) > 1 ) {
	?>
	<div class="tsf-nav-tab-wrapper hide-if-no-tsf-js" id="<?= \esc_attr( "$id-tabs-wrapper" ) ?>">
		<?php
		$tab_index = 1;

		foreach ( $tabs as $tab => $args ) {
			$dashicon = $args['dashicon'] ?? '';
			$name     = $args['name'] ?? '';

			printf(
				'<div class=tsf-tab>%s</div>',
				vsprintf(
					'<input type=radio class="tsf-nav-tab-radio tsf-input-not-saved" id=%1$s name="%2$s" %3$s><label for=%1$s class=tsf-nav-tab-label>%4$s</label>',
					[
						\esc_attr( "tsf-$id-tab-$tab" ),
						\esc_attr( "tsf-$id-tabs" ),
						1 === $tab_index ? 'checked' : '', // phpcs:ignore, WordPress.Security.EscapeOutput -- plaintext.
						\sprintf(
							'%s%s',
							// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- bug in EscapeOutputSniff
							$dashicon ? '<span class="dashicons dashicons-' . \esc_attr( $dashicon ) . ' tsf-dashicons-tabs"></span>' : '',
							// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- bug in EscapeOutputSniff
							$name ? '<span class=tsf-nav-desktop>' . \esc_attr( $name ) . '</span>' : '',
						),
					],
				),
			);
			++$tab_index;
		}
		?>
	</div>
	<?php
}
