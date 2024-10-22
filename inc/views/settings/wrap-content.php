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

// Whether tabs are active.
$show_tabs = \count( $tabs ) > 1;
$tab_index = 1;

/**
 * Start Content.
 *
 * The content is relative to the navigation and outputs navigational tabs too, but uses CSS to become invisible on JS.
 */
foreach ( $tabs as $tab => $args ) {

	$radio_id    = "tsf-{$id}-tab-{$tab}-content";
	$radio_class = "tsf-{$id}-tabs-content";

	// Current tab for JS.
	$current_class = 1 === $tab_index ? ' tsf-nav-tab-content-active' : '';

	?>
	<div class="tsf-nav-tab-content <?= \esc_attr( $radio_class . $current_class ) ?>" id="<?= \esc_attr( $radio_id ) ?>" >
		<?php
		// No-JS tabs.
		if ( $show_tabs ) {
			$dashicon = $args['dashicon'] ?? '';
			$name     = $args['name'] ?? '';

			?>
			<div class="hide-if-tsf-js tsf-nav-tab-content-no-js">
				<div class="tsf-tab tsf-nav-tab-no-js">
					<span class="tsf-nav-tab-label tsf-nav-tab-active">
						<?= $dashicon ? '<span class="dashicons dashicons-' . \esc_attr( $dashicon ) . ' tsf-dashicons-tabs"></span>' : '' ?>
						<?= $name ? '<span>' . \esc_html( $name ) . '</span>' : '' ?>
					</span>
				</div>
			</div>
			<?php
		}

		if ( ! empty( $args['callback'] ) )
			\call_user_func_array( $args['callback'], [ ( $args['args'] ?? [] ) ] );

		/**
		 * @since 4.2.0
		 * @since 5.0.0 Renamed 'params' to 'args'.
		 * @param array $args {
		 *     The tab creation data.
		 *
		 *     @type string $id   The nav-tab ID.
		 *     @type string $tab  The tab name.
		 *     @type array  $args {
		 *         The tab creation arguments.
		 *
		 *         @type string   $name     Tab name.
		 *         @type callable $callback Output function.
		 *         @type string   $dashicon The dashicon to use.
		 *         @type mixed    $args     Optional callback function args. These arguments
		 *                                  will be extracted to variables in scope of the view.
		 *    }
		 * }
		 */
		\do_action(
			'the_seo_framework_tab_content',
			[
				'id'   => $id,
				'tab'  => $tab,
				'args' => $args,
			],
		);
	?>
	</div>
	<?php
	++$tab_index;
}
