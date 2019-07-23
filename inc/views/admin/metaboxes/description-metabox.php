<?php
/**
 * @package The_SEO_Framework\Views\Admin\Metaboxes
 * @subpackage The_SEO_Framework\Admin\Settings
 */

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

//* Fetch the required instance within this file.
$instance = $this->get_view_instance( 'the_seo_framework_description_metabox', $instance );

switch ( $instance ) :
	case 'the_seo_framework_description_metabox_main':
		?>
		<h4><?php printf( esc_html__( 'Description Settings', 'autodescription' ) ); ?></h4>
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

		$this->wrap_fields(
			$this->make_checkbox(
				'auto_description',
				__( 'Automatically generate descriptions?', 'autodescription' ),
				'',
				true
			),
			true
		);
		break;

	default:
		break;
endswitch;
