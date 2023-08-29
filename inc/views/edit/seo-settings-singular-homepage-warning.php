<?php
/**
 * @package The_SEO_Framework\Views\Edit
 * @subpackage The_SEO_Framework\Admin\Edit\Inpost
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and tsf()->_verify_include_secret( $_secret ) or die;

?>
<div class="tsf-flex-setting tsf-flex" id=tsf-is-homepage-warning>
	<div class="tsf-flex-setting-input tsf-flex">
		<div class="tsf-flex-setting-input-inner-wrap tsf-flex">
			<div class="tsf-flex-setting-input-item tsf-flex">
				<span>
					<?php
					esc_html_e( 'The SEO Settings can overwrite the fields below.', 'autodescription' );
					if ( $this->can_access_settings() ) {
						echo ' &mdash; ';
						printf(
							'<a href="%s" target=_blank>%s</a>',
							esc_url( $this->get_seo_settings_page_url() . '#autodescription-homepage-settings' ),
							esc_html__( 'Edit Homepage Settings instead.', 'autodescription' )
						);
					}
					?>
				</span>
			</div>
		</div>
	</div>
</div>
