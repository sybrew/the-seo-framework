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
 * Start navigational tabs.
 *
 * Don't output navigation if $use_tabs is false and the amount of tabs is 1 or lower.
 */
if ( $use_tabs ) :
	?>
	<div class="tsf-flex tsf-flex-nav-tab-wrapper tsf-flex-hide-if-no-js" id="<?php echo esc_attr( "tsf-flex-{$id}-tabs-wrapper" ); ?>">
		<div class="tsf-flex tsf-flex-nav-tab-inner">
			<?php
			foreach ( $tabs as $tab => $value ) :
				$dashicon   = $value['dashicon'] ?? '';
				$label_name = $value['name'] ?? '';

				$wrapper_id     = esc_attr( "tsf-flex-nav-tab-{$tab}" );
				$wrapper_active = 1 === $count ? 'tsf-flex-nav-tab-active' : '';

				$input_checked = 1 === $count ? 'checked' : '';
				$input_id      = esc_attr( "tsf-flex-{$id}-tab-{$tab}" );
				$input_name    = esc_attr( "tsf-flex-{$id}-tabs" );

				if ( $dashicon )
					$dashicon = sprintf( '<span class="tsf-flex dashicons %s tsf-flex-nav-dashicon"></span>', esc_attr( "dashicons-$dashicon" ) );

				if ( $label_name )
					$label_name = sprintf( '<span class="tsf-flex tsf-flex-nav-name">%s</span>', esc_attr( $label_name ) );

				// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- All output below is escaped.
				echo <<<HTML
				<div class="tsf-flex tsf-flex-nav-tab tsf-flex $wrapper_active" id="$wrapper_id">
					<input type="radio" class="tsf-flex-nav-tab-radio tsf-input-not-saved" id="$input_id" name="$input_name" $input_checked>
					<label for="$input_id" class="tsf-flex tsf-flex-nav-tab-label">
						$dashicon
						$label_name
					</label>
				</div>
HTML;
				// ^ At PHP 7.3+ we can indent this.

				$count++;
			endforeach;
			?>
		</div>
	</div>
	<?php
endif;
