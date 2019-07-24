<?php
/**
 * @package The_SEO_Framework\Views\Edit
 * @subpackage The_SEO_Framework\Admin\Edit\Inpost
 */

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

//* Whether tabs are active.
$use_tabs = $use_tabs && count( $tabs ) > 1;
$count    = 1;

/**
 * Start navigational tabs.
 *
 * Don't output navigation if $use_tabs is false and the amount of tabs is 1 or lower.
 */
if ( $use_tabs ) :
	?>
	<div class="tsf-flex tsf-flex-nav-tab-wrapper tsf-flex-hide-if-no-js" id="<?php echo \esc_attr( 'tsf-flex-' . $id . '-tabs-wrapper' ); ?>">
		<div class="tsf-flex tsf-flex-nav-tab-inner">
			<?php
			foreach ( $tabs as $tab => $value ) :
				$dashicon = isset( $value['dashicon'] ) ? $value['dashicon'] : '';
				$label_name = isset( $value['name'] ) ? $value['name'] : '';

				$wrapper_id = \esc_attr( 'tsf-flex-nav-tab-' . $tab );
				$wrapper_active = 1 === $count ? ' tsf-flex-nav-tab-active' : '';

				$input_checked = 1 === $count ? 'checked' : '';
				$input_id = \esc_attr( 'tsf-flex-' . $id . '-tab-' . $tab );
				$input_name = \esc_attr( 'tsf-flex-' . $id . '-tabs' );

				//= All output below is escaped.
				?>
				<div class="tsf-flex tsf-flex-nav-tab tsf-flex<?php echo $wrapper_active; ?>" id="<?php echo $wrapper_id; ?>">
					<input type="radio" class="tsf-flex-nav-tab-radio tsf-input-not-saved" id="<?php echo $input_id; ?>" name="<?php echo $input_name; ?>" <?php echo $input_checked; ?>>
					<label for="<?php echo $input_id; ?>" class="tsf-flex tsf-flex-nav-tab-label">
						<?php
						echo $dashicon ? '<span class="tsf-flex dashicons dashicons-' . \esc_attr( $dashicon ) . ' tsf-flex-nav-dashicon"></span>' : '';
						echo $label_name ? '<span class="tsf-flex tsf-flex-nav-name">' . \esc_attr( $label_name ) . '</span>' : '';
						?>
					</label>
				</div>
				<?php

				$count++;
			endforeach;
			?>
		</div>
	</div>
	<?php
endif;
