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
		HTML::description(
			__( 'The meta description suggests text to be used under the title on search engine results pages.', 'autodescription' )
		);

		?>
		<hr>
		<?php
		HTML::header_title( __( 'Automated Description Settings', 'autodescription' ) );

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
		HTML::description(
			__( "Open Graph and Twitter require descriptions. So, it's best to leave description generation enabled.", 'autodescription' )
		);

		?>
		<hr>
		<?php
		HTML::header_title( __( 'Advanced Generation Settings', 'autodescription' ) );

		HTML::description(
			__( 'The HTML content of your pages can be used to generate descriptions. The generator processes this HTML in passing layers to understand the layout. If the HTML is complex, not all layers may be processed, and you might find spaces missing between sentences. Increasing the maximum number of passes reduces the chance of this happening, but at the cost of performance.', 'autodescription' )
		);

		/**
		 * @since 4.2.7
		 * @param array $html_passes_method The HTML pass option by [ 'option_value' => 'Name' ]
		 */
		$html_passes_methods = (array) apply_filters(
			'the_seo_framework_auto_descripton_html_method_methods',
			[
				'fast'     => __( 'Fast (max. 2 passes)', 'autodescription' ),
				'accurate' => __( 'Accurate (max. 6 passes)', 'autodescription' ),
				'thorough' => __( 'Thorough (max. 12 passes)', 'autodescription' ),
			]
		);

		$html_passes_select_options = '';
		$_current                   = $this->get_option( 'auto_descripton_html_method' );
		foreach ( $html_passes_methods as $value => $name ) {
			$html_passes_select_options .= vsprintf(
				'<option value="%s" %s>%s</option>',
				[
					esc_attr( $value ),
					selected( $_current, esc_attr( $value ), false ),
					esc_html( $name ),
				]
			);
		}

		HTML::wrap_fields(
			vsprintf(
				'<label for="%1$s">%2$s</label>
				<select name="%3$s" id="%1$s">%4$s</select>',
				[
					Input::get_field_id( 'auto_descripton_html_method' ),
					esc_html__( 'HTML parsing method:', 'autodescription' ),
					Input::get_field_name( 'auto_descripton_html_method' ),
					$html_passes_select_options,
				]
			),
			true
		);

		HTML::description_noesc(
			sprintf(
				'<a href="%s" target=_blank rel="noreferrer noopener">%s</a>',
				'https://kb.theseoframework.com/?p=65#html-passes',
				esc_html__( 'Learn how this works.', 'autodescription' )
			)
		);
		break;

	default:
		break;
endswitch;
