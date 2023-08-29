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

/**
 * Start navigational tabs.
 *
 * Don't output navigation if $use_tabs is false and the amount of tabs is 1 or lower.
 */
if ( $use_tabs ) {
	?>
	<div class="tsf-nav-tab-wrapper hide-if-no-tsf-js" id="<?= esc_attr( $id . '-tabs-wrapper' ) ?>">
		<?php
		$count = 1;

		foreach ( $tabs as $tab => $value ) {
			$dashicon = $value['dashicon'] ?? '';
			$name     = $value['name'] ?? '';

			printf(
				'<div class=tsf-tab>%s</div>',
				vsprintf(
					'<input type=radio class="tsf-nav-tab-radio tsf-input-not-saved" id=%1$s name="%2$s" %3$s><label for=%1$s class=tsf-nav-tab-label>%4$s</label>',
					[
						esc_attr( "tsf-$id-tab-$tab" ),
						esc_attr( "tsf-$id-tabs" ),
						( 1 === $count ? 'checked' : '' ),
						sprintf(
							'%s%s',
							// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- bug in EscapeOutputSniff
							( $dashicon ? '<span class="dashicons dashicons-' . esc_attr( $dashicon ) . ' tsf-dashicons-tabs"></span>' : '' ),
							// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- bug in EscapeOutputSniff
							( $name ? '<span class=tsf-nav-desktop>' . esc_attr( $name ) . '</span>' : '' )
						),
					]
				)
			);
			$count++;
		}
		?>
	</div>
	<?php
}
