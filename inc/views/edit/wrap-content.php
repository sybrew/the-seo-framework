<?php
/**
 * @package The_SEO_Framework\Views\Edit
 * @subpackage The_SEO_Framework\Admin\Edit\Inpost
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
 * The content is relative to the navigation, and uses CSS to become visible.
 */
foreach ( $tabs as $tab => $params ) :

	$radio_id    = "tsf-flex-{$id}-tab-{$tab}-content";
	$radio_class = "tsf-flex-{$id}-tabs-content";

	// Current tab for JS.
	$current_class = 1 === $count ? ' tsf-flex-tab-content-active' : '';

	?>
	<div class="tsf-flex tsf-flex-tab-content <?php echo esc_attr( $radio_class . $current_class ); ?>" id="<?php echo esc_attr( $radio_id ); ?>" >
		<?php
		// No-JS tabs.
		if ( $use_tabs ) :
			$dashicon   = $params['dashicon'] ?? '';
			$label_name = $params['name'] ?? '';

			?>
			<div class="tsf-flex tsf-flex-hide-if-js tsf-flex-tabs-content-no-js">
				<div class="tsf-flex tsf-flex-nav-tab tsf-flex-tab-no-js">
					<span class="tsf-flex tsf-flex-nav-tab">
						<?php echo $dashicon ? '<span class="tsf-flex dashicons dashicons-' . esc_attr( $dashicon ) . ' tsf-flex-nav-dashicon"></span>' : ''; ?>
						<?php echo $label_name ? '<span class="tsf-flex tsf-flex-nav-name">' . esc_html( $label_name ) . '</span>' : ''; ?>
					</span>
				</div>
			</div>
			<?php
		endif;

		if ( ! empty( $params['callback'] ) )
			call_user_func_array( $params['callback'], ( $params['args'] ?? [] ) );

		/**
		 * @since 4.2.0
		 * @param array $args The tab arguments: {
		 *    @param string id
		 *    @param string tab
		 *    @param array  params
		 * }
		 */
		do_action(
			'the_seo_framework_flex_tab_content',
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
