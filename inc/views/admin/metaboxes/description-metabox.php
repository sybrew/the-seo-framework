<?php
/**
 * @package The_SEO_Framework\Views\Admin\Metaboxes
 * @subpackage The_SEO_Framework\Admin\Settings
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and the_seo_framework()->_verify_include_secret( $_secret ) or die;

// Fetch the required instance within this file.
$instance = $this->get_view_instance( 'the_seo_framework_description_metabox', $instance );

switch ( $instance ) :
	case 'the_seo_framework_description_metabox_main':
		?>
		<h4><?php esc_html_e( 'Description Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description(
			__( 'The meta description can be used to determine the text used under the title on search engine results pages.', 'autodescription' )
		);

		?>
		<hr>

		<h4><?php esc_html_e( 'Automated Description Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description(
			__( 'A description can be automatically generated for every page.', 'autodescription' )
		);
		$this->description(
			__( 'Open Graph and Twitter Cards require descriptions. Therefore, it is best to leave this option enabled.', 'autodescription' )
		);

		$info = $this->make_info(
			__( 'Learn how this feature works.', 'autodescription' ),
			'https://kb.theseoframework.com/?p=65',
			false
		);

		$this->wrap_fields(
			$this->make_checkbox(
				'auto_description',
				esc_html__( 'Automatically generate descriptions?', 'autodescription' ) . ' ' . $info,
				'',
				false
			),
			true
		);
		break;

	default:
		break;
endswitch;
