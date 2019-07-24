<?php
/**
 * @package The_SEO_Framework\Views\Admin
 * @subpackage The_SEO_Framework\Admin\Settings
 */

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

//* Whether tabs are active.
$use_tabs = $use_tabs && count( $tabs ) > 1;

/**
 * Start navigational tabs.
 *
 * Don't output navigation if $use_tabs is false and the amount of tabs is 1 or lower.
 */
if ( $use_tabs ) :
	?>
	<div class="tsf-nav-tab-wrapper hide-if-no-tsf-js" id="<?php echo \esc_attr( $id . '-tabs-wrapper' ); ?>">
		<?php
		$count = 1;
		foreach ( $tabs as $tab => $value ) :
			$dashicon = isset( $value['dashicon'] ) ? $value['dashicon'] : '';
			$name     = isset( $value['name'] ) ? $value['name'] : '';

			printf(
				'<div class=tsf-tab>%s</div>',
				vsprintf(
					'<input type=radio class="tsf-tabs-radio tsf-input-not-saved" id=%1$s name="%2$s" %3$s><label for=%1$s class=tsf-nav-tab>%4$s</label>',
					[
						\esc_attr( 'tsf-' . $id . '-tab-' . $tab ),
						\esc_attr( 'tsf-' . $id . '-tabs' ),
						( 1 === $count ? 'checked' : '' ),
						sprintf(
							'%s%s',
							( $dashicon ? '<span class="dashicons dashicons-' . \esc_attr( $dashicon ) . ' tsf-dashicons-tabs"></span>' : '' ),
							( $name ? '<span class="tsf-nav-desktop">' . \esc_attr( $name ) . '</span>' : '' )
						),
					]
				)
			); // phpcs:ignore -- XSS ok: Validator can't distinguish HTML in ternary.
			$count++;
		endforeach;
		?>
	</div>
	<?php
endif;

/**
 * Start Content.
 *
 * The content is relative to the navigation and outputs navigational tabs too, but uses CSS to become invisible on JS.
 */
$count = 1;
foreach ( $tabs as $tab => $value ) :

	$the_id   = 'tsf-' . $id . '-tab-' . $tab . '-content';
	$the_name = 'tsf-' . $id . '-tabs-content';

	//* Current tab for JS.
	$current = 1 === $count ? ' tsf-active-tab-content' : '';

	?>
	<div class="tsf-tabs-content <?php echo \esc_attr( $the_name . $current ); ?>" id="<?php echo \esc_attr( $the_id ); ?>" >
		<?php
		//* No-JS tabs.
		if ( $use_tabs ) :
			$dashicon = isset( $value['dashicon'] ) ? $value['dashicon'] : '';
			$name     = isset( $value['name'] ) ? $value['name'] : '';

			?>
			<div class="hide-if-tsf-js tsf-content-no-js">
				<div class="tsf-tab tsf-tab-no-js">
					<span class="tsf-nav-tab tsf-active-tab">
						<?php echo $dashicon ? '<span class="dashicons dashicons-' . \esc_attr( $dashicon ) . ' tsf-dashicons-tabs"></span>' : ''; ?>
						<?php echo $name ? '<span>' . \esc_attr( $name ) . '</span>' : ''; ?>
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
