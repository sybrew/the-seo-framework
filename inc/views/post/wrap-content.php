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
foreach ( $tabs as $tab => $params ) {

	$radio_id    = "tsf-flex-{$id}-tab-{$tab}-content";
	$radio_class = "tsf-flex-{$id}-tabs-content";

	// Current tab for JS.
	$current_class = 1 === $tab_index ? ' tsf-flex-tab-content-active' : '';

	?>
	<div class="tsf-flex tsf-flex-tab-content <?= \esc_attr( $radio_class . $current_class ) ?>" id="<?= \esc_attr( $radio_id ) ?>" >
		<?php
		// No-JS tabs.
		if ( $show_tabs ) {
			$dashicon   = $params['dashicon'] ?? '';
			$label_name = $params['name'] ?? '';

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

		if ( ! empty( $params['callback'] ) )
			\call_user_func_array( $params['callback'], ( $params['args'] ?? [] ) );

		/**
		 * @since 4.2.0
		 * @param array $args The tab arguments: {
		 *    @param string id
		 *    @param string tab
		 *    @param array  params
		 * }
		 */
		\do_action(
			'the_seo_framework_flex_tab_content',
			[
				'id'     => $id,
				'tab'    => $tab,
				'params' => $params,
			],
		);
	?>
	</div>
	<?php
	++$tab_index;
}
