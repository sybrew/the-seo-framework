<?php
/**
 * @package The_SEO_Framework\Views\Notice
 * @subpackage The_SEO_Framework\Admin\Notice
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and the_seo_framework()->_verify_include_secret( $_secret ) or die;

if ( ! $message ) return;

// Make sure the scripts are loaded.
$this->init_admin_scripts();
The_SEO_Framework\Builders\Scripts::footer_enqueue();

if ( in_array( $args['type'], [ 'warning', 'info' ], true ) )
	$args['type'] = "notice-{$args['type']}";

$dismiss_title = __( 'Dismiss this notice', 'default' );

$button_js   = sprintf(
	'<a class="hide-if-no-tsf-js tsf-dismiss" href="javascript:;" title="%s" %s></a>',
	esc_attr( $dismiss_title ),
	$this->make_data_attributes( [
		'key'   => $key,
		// Is this the best nonce key key? Capability validation already happened. See `output_dismissible_persistent_notices()`.
		'nonce' => wp_create_nonce( $this->get_dismiss_notice_nonce_action( $key ) ),
	] )
);
$button_nojs = vsprintf(
	'<form action="%s" method="post" id="tsf-dismiss-notice[%s]" class="hide-if-tsf-js">%s</form>',
	[
		// Register this at removable_query_args? Ignore? No one cares, literally? Does anyone even read this? Hello!? HELLO!?!?
		esc_attr( add_query_arg( [ 'tsf-dismissed-notice' => $key ] ) ),
		esc_attr( $key ),
		implode(
			'',
			[
				wp_nonce_field( $this->get_dismiss_notice_nonce_action( $key ), 'tsf-notice-nonce', true, false ),
				sprintf(
					'<button class="tsf-dismiss" type=submit name=tsf-notice-submit id=tsf-notice-submit[%s] value=%s title="%s">%s</button>',
					esc_attr( $key ),
					esc_attr( $key ),
					esc_attr( $dismiss_title ),
					sprintf(
						'<span class="screen-reader-text">%s</span>',
						esc_html( $dismiss_title )
					)
				),
			]
		),
	]
);

vprintf(
	'<div class="notice %s tsf-notice %s">%s%s</div>',
	[
		esc_attr( $args['type'] ),
		( $args['icon'] ? 'tsf-show-icon' : '' ),
		sprintf(
			// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- conditionals bug.
			( ! $args['escape'] && 0 === strpos( $message, '<p' ) ? '%s' : '<p>%s</p>' ),
			// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- the invoker should be mindful.
			( $args['escape'] ? esc_html( $message ) : $message )
		),
		// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- they are.
		$button_js . $button_nojs,
	]
);
