<?php
/**
 * @package The_SEO_Framework\Views\Admin\Metaboxes
 * @subpackage The_SEO_Framework\Admin\Settings
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

use The_SEO_Framework\Interpreters\HTML,
	The_SEO_Framework\Interpreters\Settings_Input as Input;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and tsf()->_verify_include_secret( $_secret ) or die;

switch ( $this->get_view_instance( 'description', $instance ) ) :
	case 'description_main':
		HTML::header_title( __( 'Description Settings', 'autodescription' ) );
		HTML::description(
			__( 'The meta description can be used to determine the text used under the title on search engine results pages.', 'autodescription' )
		);

		?>
		<hr>
		<?php
		HTML::header_title( __( 'Automated Description Settings', 'autodescription' ) );
		HTML::description(
			__( 'A description can be automatically generated for every page.', 'autodescription' )
		);
		HTML::description(
			__( 'Open Graph and Twitter Cards require descriptions. Therefore, it is best to leave this option enabled.', 'autodescription' )
		);

		$info = HTML::make_info(
			__( 'Learn how this feature works.', 'autodescription' ),
			'https://kb.theseoframework.com/?p=65',
			false
		);

		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'     => 'auto_description',
				'label'  => esc_html__( 'Automatically generate descriptions?', 'autodescription' ) . " $info",
				'escape' => false,
			] ),
			true
		);
		break;

	default:
		break;
endswitch;
