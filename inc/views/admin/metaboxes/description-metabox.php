<?php
/**
 * @package The_SEO_Framework\Views\Admin\Metaboxes
 * @subpackage The_SEO_Framework\Admin\Settings
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

use The_SEO_Framework\Interpreters\HTML,
	The_SEO_Framework\Interpreters\Form;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and the_seo_framework()->_verify_include_secret( $_secret ) or die;

// Fetch the required instance within this file.
$instance = $this->get_view_instance( 'the_seo_framework_description_metabox', $instance );

switch ( $instance ) :
	case 'the_seo_framework_description_metabox_main':
		Form::header_title( __( 'Description Settings', 'autodescription' ) );
		HTML::description(
			__( 'The meta description can be used to determine the text used under the title on search engine results pages.', 'autodescription' )
		);

		?>
		<hr>
		<?php
		Form::header_title( __( 'Automated Description Settings', 'autodescription' ) );
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
			Form::make_checkbox( [
				'id'     => 'auto_description',
				'label'  => esc_html__( 'Automatically generate descriptions?', 'autodescription' ) . ' ' . $info,
				'escape' => false,
			] ),
			true
		);
		break;

	default:
		break;
endswitch;
