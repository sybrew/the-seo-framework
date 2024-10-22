<?php
/**
 * @package The_SEO_Framework\Views\Post
 * @subpackage The_SEO_Framework\Admin\Post
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) or die;

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

// See flex_nav_tab_wrapper
[ $id, $tabs ] = $view_args;

/**
 * Start navigational tabs.
 * Don't output navigation if the number of tabs is 1 or lower.
 */
if ( \count( $tabs ) > 1 ) {
	?>
	<div class="tsf-flex tsf-flex-nav-tab-wrapper tsf-flex-hide-if-no-js" id="<?= \esc_attr( "tsf-flex-{$id}-tabs-wrapper" ) ?>">
		<div class="tsf-flex tsf-flex-nav-tab-inner">
			<?php
			$tab_index = 1;

			foreach ( $tabs as $tab => $value ) {
				$dashicon   = $value['dashicon'] ?? '';
				$label_name = $value['name'] ?? '';

				$wrapper_id     = \esc_attr( "tsf-flex-nav-tab-{$tab}" );
				$wrapper_active = 1 === $tab_index ? 'tsf-flex-nav-tab-active' : '';

				$input_checked = 1 === $tab_index ? 'checked' : '';
				$input_id      = \esc_attr( "tsf-flex-{$id}-tab-{$tab}" );
				$input_name    = \esc_attr( "tsf-flex-{$id}-tabs" );

				if ( $dashicon )
					$dashicon = \sprintf( '<span class="tsf-flex dashicons %s tsf-flex-nav-dashicon"></span>', \esc_attr( "dashicons-$dashicon" ) );

				if ( $label_name )
					$label_name = \sprintf( '<span class="tsf-flex tsf-flex-nav-name">%s</span>', \esc_attr( $label_name ) );

				// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- All output below is escaped.
				echo <<<HTML
				<div class="tsf-flex tsf-flex-nav-tab tsf-flex $wrapper_active" id="$wrapper_id">
					<input type=radio class="tsf-flex-nav-tab-radio tsf-input-not-saved" id="$input_id" name="$input_name" $input_checked>
					<label for="$input_id" class="tsf-flex tsf-flex-nav-tab-label">
						$dashicon
						$label_name
					</label>
				</div>
				HTML;

				++$tab_index;
			}
			?>
		</div>
	</div>
	<?php
}
