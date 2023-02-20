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

switch ( $this->get_view_instance( 'webmaster', $instance ) ) :
	case 'webmaster_main':
		$site_url = $this->get_homepage_permalink();

		$settings = [
			'google'    => [
				'setting'     => 'google_verification',
				'label'       => __( 'Google Search Console Verification Code', 'autodescription' ),
				'info'        => HTML::make_info(
					__( 'Get the Google verification code.', 'autodescription' ),
					'https://www.google.com/webmasters/verification/verification?siteUrl=' . rawurlencode( $site_url ) . '&tid=alternate&vtype=vmeta',
					false
				),
				'placeholder' => '123A456B78901C2D3456E7890F1A234D',
			],
			'bing'      => [
				'setting'     => 'bing_verification',
				'label'       => __( 'Bing Webmaster Verification Code', 'autodescription' ),
				'info'        => HTML::make_info(
					__( 'Get the Bing verification code.', 'autodescription' ),
					'https://www.bing.com/webmaster/home/addsite?addurl=' . rawurlencode( $site_url ),
					false
				),
				'placeholder' => '123A456B78901C2D3456E7890F1A234D',
			],
			'yandex'    => [
				'setting'     => 'yandex_verification',
				'label'       => __( 'Yandex Webmaster Verification Code', 'autodescription' ),
				'info'        => HTML::make_info(
					__( 'Get the Yandex verification code.', 'autodescription' ),
					'https://webmaster.yandex.com/sites/add/?hostName=' . rawurlencode( $site_url ),
					false
				),
				'placeholder' => '12345abc678901d2',
			],
			'baidu'     => [
				'setting'     => 'baidu_verification',
				/* translators: literal translation from '百度搜索资源平台'-Code */
				'label'       => __( 'Baidu Search Resource Platform Code', 'autodescription' ),
				'info'        => HTML::make_info(
					__( 'Get the Baidu verification code.', 'autodescription' ),
					'https://ziyuan.baidu.com/login/index?u=/site/siteadd',
					false
				),
				'placeholder' => 'a12bcDEFGa',
			],
			'pinterest' => [
				'setting'     => 'pint_verification',
				'label'       => __( 'Pinterest Analytics Verification Code', 'autodescription' ),
				'info'        => HTML::make_info(
					__( 'Get the Pinterest verification code.', 'autodescription' ),
					'https://analytics.pinterest.com/',
					false
				),
				'placeholder' => '123456a7b8901de2fa34bcdef5a67b90',
			],
		];

		HTML::header_title( __( 'Webmaster Integration Settings', 'autodescription' ) );
		HTML::description( __( "When adding your website to Google, Bing and other Webmaster Tools, you'll be asked to add a code or file to your website for verification purposes. These options will help you easily integrate those codes.", 'autodescription' ) );
		HTML::description( __( "Verifying your website has no SEO value whatsoever. But you might gain added benefits such as search ranking insights to help you improve your website's content.", 'autodescription' ) );

		?>
		<hr>
		<?php
		foreach ( $settings as $setting ) :
			vprintf(
				'<p><label for=%s><strong>%s</strong> %s</label></p>',
				[
					esc_attr( Input::get_field_id( $setting['setting'] ) ),
					esc_html( $setting['label'] ),
					// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- should be escaped in list.
					$setting['info'],
				]
			);
			vprintf(
				'<p><input type=text name=%s class="large-text ltr" id=%s placeholder="%s" value="%s" /></p>',
				[
					esc_attr( Input::get_field_name( $setting['setting'] ) ),
					esc_attr( Input::get_field_id( $setting['setting'] ) ),
					esc_attr( $setting['placeholder'] ),
					esc_attr( $this->get_option( $setting['setting'] ) ),
				]
			);
		endforeach;
		break;

	default:
		break;
endswitch;
