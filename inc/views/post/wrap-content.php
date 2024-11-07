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

// Whether tabs are active.
$show_tabs = \count( $tabs ) > 1;
$tab_index = 1;

/**
 * Start Content.
 *
 * The content is relative to the navigation, and uses CSS to become visible.
 */
foreach ( $tabs as $tab => $args ) {

	$radio_id    = "tsf-flex-{$id}-tab-{$tab}-content";
	$radio_class = "tsf-flex-{$id}-tabs-content";

	// Current tab for JS.
	$current_class = 1 === $tab_index ? ' tsf-flex-tab-content-active' : '';

	?>
	<div class="tsf-flex tsf-flex-tab-content <?= \esc_attr( $radio_class . $current_class ) ?>" id="<?= \esc_attr( $radio_id ) ?>" >
		<?php
		// No-JS tabs.
		if ( $show_tabs ) {
			$dashicon   = $args['dashicon'] ?? '';
			$label_name = $args['name'] ?? '';

			?>
			<div class="tsf-flex tsf-flex-hide-if-js tsf-flex-tabs-content-no-js">
				<div class="tsf-flex tsf-flex-nav-tab tsf-flex-tab-no-js">
					<span class="tsf-flex tsf-flex-nav-tab">
						<?= $dashicon ? '<span class="tsf-flex dashicons dashicons-' . \esc_attr( $dashicon ) . ' tsf-flex-nav-dashicon"></span>' : '' ?>
						<?= $label_name ? '<span class="tsf-flex tsf-flex-nav-name">' . \esc_html( $label_name ) . '</span>' : '' ?>
					</span>
				</div>
			</div>
			<?php
		}

		if ( ! empty( $args['callback'] ) )
			\call_user_func_array( $args['callback'], ( $args['args'] ?? [] ) );

		/**
		 * @since 4.2.0
		 * @since 5.1.0 Renamed 'params' to 'args'.
		 * @param array $args {
		 *     The tab creation data.
		 *
		 *     @type string $id     The nav-tab ID.
		 *     @type string $tab    The tab name.
		 *     @type array  $params {
		 *         The tab creation arguments.
		 *
		 *         @type string   $name     Tab name.
		 *         @type callable $callback Output function.
		 *         @type string   $dashicon The dashicon to use.
		 *         @type mixed    $args     Optional callback function args. These arguments
		 *                                  will be extracted to variables in scope of the view.
		 *     }
		 * }
		 */
		\do_action(
			'the_seo_framework_flex_tab_content',
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
