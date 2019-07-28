<?php
/**
 * @package The_SEO_Framework\Views\Admin
 * @subpackage The_SEO_Framework\Admin\Settings
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
	<div class="tsf-nav-tab-wrapper hide-if-no-tsf-js" id="<?php echo \esc_attr( $id . '-tabs-wrapper' ); ?>">
		<?php
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
			);
			$count++;
		endforeach;
		?>
	</div>
	<?php
endif;
