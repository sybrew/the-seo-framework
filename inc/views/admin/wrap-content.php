<?php
/**
 * @package The_SEO_Framework\Views\Admin
 * @subpackage The_SEO_Framework\Admin\Settings
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
 * The content is relative to the navigation and outputs navigational tabs too, but uses CSS to become invisible on JS.
 */
foreach ( $tabs as $tab => $value ) :

	$the_id   = 'tsf-' . $id . '-tab-' . $tab . '-content';
	$the_name = 'tsf-' . $id . '-tabs-content';

	// Current tab for JS.
	$current = 1 === $count ? ' tsf-active-tab-content' : '';

	?>
	<div class="tsf-tabs-content <?php echo esc_attr( $the_name . $current ); ?>" id="<?php echo esc_attr( $the_id ); ?>" >
		<?php
		// No-JS tabs.
		if ( $use_tabs ) :
			$dashicon = isset( $value['dashicon'] ) ? $value['dashicon'] : '';
			$name     = isset( $value['name'] ) ? $value['name'] : '';

			?>
			<div class="hide-if-tsf-js tsf-content-no-js">
				<div class="tsf-tab tsf-tab-no-js">
					<span class="tsf-nav-tab tsf-active-tab">
						<?php echo $dashicon ? '<span class="dashicons dashicons-' . esc_attr( $dashicon ) . ' tsf-dashicons-tabs"></span>' : ''; ?>
						<?php echo $name ? '<span>' . esc_attr( $name ) . '</span>' : ''; ?>
					</span>
				</div>
			</div>
			<?php
		endif;

		$callback = isset( $value['callback'] ) ? $value['callback'] : '';

		if ( $callback ) {
			$params = isset( $value['args'] ) ? [ $value['args'] ] : [];
			call_user_func_array( $callback, $params );
		}
		?>
	</div>
	<?php

	$count++;
endforeach;
