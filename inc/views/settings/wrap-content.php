<?php
/**
 * @package The_SEO_Framework\Views\Admin
 * @subpackage The_SEO_Framework\Admin\Settings
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and tsf()->_verify_include_secret( $_secret ) or die;

// Whether tabs are active.
$use_tabs = $use_tabs && count( $tabs ) > 1;
$count    = 1;

/**
 * Start Content.
 *
 * The content is relative to the navigation and outputs navigational tabs too, but uses CSS to become invisible on JS.
 */
foreach ( $tabs as $tab => $params ) :

	$radio_id    = "tsf-{$id}-tab-{$tab}-content";
	$radio_class = "tsf-{$id}-tabs-content";

	// Current tab for JS.
	$current_class = 1 === $count ? ' tsf-nav-tab-content-active' : '';

	?>
	<div class="tsf-nav-tab-content <?= esc_attr( $radio_class . $current_class ) ?>" id="<?= esc_attr( $radio_id ) ?>" >
		<?php
		// No-JS tabs.
		if ( $use_tabs ) :
			$dashicon = $params['dashicon'] ?? '';
			$name     = $params['name'] ?? '';

			?>
			<div class="hide-if-tsf-js tsf-nav-tab-content-no-js">
				<div class="tsf-tab tsf-nav-tab-no-js">
					<span class="tsf-nav-tab-label tsf-nav-tab-active">
						<?= $dashicon ? '<span class="dashicons dashicons-' . esc_attr( $dashicon ) . ' tsf-dashicons-tabs"></span>' : '' ?>
						<?= $name ? '<span>' . esc_html( $name ) . '</span>' : '' ?>
					</span>
				</div>
			</div>
			<?php
		endif;

		if ( ! empty( $params['callback'] ) )
			call_user_func_array( $params['callback'], [ ( $params['args'] ?? [] ) ] );

		/**
		 * @since 4.2.0
		 * @param array $args The tab arguments: {
		 *    @param string id
		 *    @param string tab
		 *    @param array  params
		 * }
		 */
		do_action(
			'the_seo_framework_tab_content',
			[
				'id'     => $id,
				'tab'    => $tab,
				'params' => $params,
			]
		);
	?>
	</div>
	<?php

	$count++;
endforeach;
