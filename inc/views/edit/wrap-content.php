<?php
/**
 * @package The_SEO_Framework\Views\Edit
 * @subpackage The_SEO_Framework\Admin\Edit\Inpost
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and the_seo_framework()->_verify_include_secret( $_secret ) or die;

// Whether tabs are active.
$use_tabs = $use_tabs && count( $tabs ) > 1;
$count    = 1;

/**
 * Start Content.
 *
 * The content is relative to the navigation, and uses CSS to become visible.
 */
foreach ( $tabs as $tab => $value ) :

	$radio_id    = esc_attr( 'tsf-flex-' . $id . '-tab-' . $tab . '-content' );
	$radio_class = esc_attr( 'tsf-flex-' . $id . '-tabs-content' );

	// Current tab for JS.
	$current_class = 1 === $count ? ' tsf-flex-tab-content-active' : '';

	?>
	<div class="tsf-flex tsf-flex-tab-content <?php echo esc_attr( $radio_class . $current_class ); ?>" id="<?php echo esc_attr( $radio_id ); ?>" >
		<?php
		// No-JS tabs.
		if ( $use_tabs ) :
			$dashicon   = isset( $value['dashicon'] ) ? $value['dashicon'] : '';
			$label_name = isset( $value['name'] ) ? $value['name'] : '';

			?>
			<div class="tsf-flex tsf-flex-hide-if-js tsf-flex-tabs-content-no-js">
				<div class="tsf-flex tsf-flex-nav-tab tsf-flex-tab-no-js">
					<span class="tsf-flex tsf-flex-nav-tab">
						<?php echo $dashicon ? '<span class="tsf-flex dashicons dashicons-' . esc_attr( $dashicon ) . ' tsf-flex-nav-dashicon"></span>' : ''; ?>
						<?php echo $label_name ? '<span class="tsf-flex tsf-flex-nav-name">' . esc_attr( $label_name ) . '</span>' : ''; ?>
					</span>
				</div>
			</div>
			<?php
		endif;

		$callback = isset( $value['callback'] ) ? $value['callback'] : '';

		if ( $callback ) {
			$params = isset( $value['args'] ) ? $value['args'] : '';
			call_user_func_array( $callback, (array) $params );
		}
		?>
	</div>
	<?php

	$count++;
endforeach;
