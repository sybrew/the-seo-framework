<?php
/**
 * @package The_SEO_Framework\Views\Admin\Metaboxes
 * @subpackage The_SEO_Framework\Admin\Settings
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) or die;

use \The_SEO_Framework\Admin\Settings\Layout\{
	HTML,
	Input,
};

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * The SEO Framework plugin
 * Copyright (C) 2016 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

// See _description_metabox et al.
[ $instance ] = $view_args;

switch ( $instance ) : // Quite useless, but prepared for expansion.
	case 'main':
		$site_url = Meta\URI::get_bare_front_page_url();

		$settings = [
			'google'    => [
				'setting'     => 'google_verification',
				'label'       => \__( 'Google Search Console Verification Code', 'autodescription' ),
				'info'        => HTML::make_info(
					\__( 'Get the Google verification code.', 'autodescription' ),
					'https://search.google.com/search-console/ownership?resource_id=' . rawurlencode( $site_url ),
					false,
				),
				'placeholder' => 'ab1cDe2Fg3HI4Jklm5nOpqRSt67UVW78XYzAbcdEfgH',
			],
			'bing'      => [
				'setting'     => 'bing_verification',
				'label'       => \__( 'Bing Webmaster Verification Code', 'autodescription' ),
				'info'        => HTML::make_info(
					\__( 'Get the Bing verification code.', 'autodescription' ),
					'https://www.bing.com/webmaster/home/addsite?addurl=' . rawurlencode( $site_url ),
					false,
				),
				'placeholder' => '123A456B78901C2D3456E7890F1A234D',
			],
			'yandex'    => [
				'setting'     => 'yandex_verification',
				'label'       => \__( 'Yandex Webmaster Verification Code', 'autodescription' ),
				'info'        => HTML::make_info(
					\__( 'Get the Yandex verification code.', 'autodescription' ),
					'https://webmaster.yandex.com/sites/add/?hostName=' . rawurlencode( $site_url ),
					false,
				),
				'placeholder' => '12345abc678901d2',
			],
			'baidu'     => [
				'setting'     => 'baidu_verification',
				/* translators: literal translation from '百度搜索资源平台'-Code */
				'label'       => \__( 'Baidu Search Resource Platform Code', 'autodescription' ),
				'info'        => HTML::make_info(
					\__( 'Get the Baidu verification code.', 'autodescription' ),
					'https://ziyuan.baidu.com/login/index?u=/site/siteadd',
					false,
				),
				'placeholder' => 'a12bcDEFGa',
			],
			'pinterest' => [
				'setting'     => 'pint_verification',
				'label'       => \__( 'Pinterest Analytics Verification Code', 'autodescription' ),
				'info'        => HTML::make_info(
					\__( 'Get the Pinterest verification code.', 'autodescription' ),
					'https://analytics.pinterest.com/',
					false,
				),
				'placeholder' => '123456a7b8901de2fa34bcdef5a67b90',
			],
		];

		HTML::header_title( \__( 'Webmaster Integration Settings', 'autodescription' ) );
		HTML::description( \__( "When adding your website to Google, Bing and other Webmaster Tools, you'll be asked to add a code or file to your website for verification purposes. These options will help you easily integrate those codes.", 'autodescription' ) );
		HTML::description( \__( "Verifying your website has no SEO value whatsoever. But you might gain added benefits such as search ranking insights to help you improve your website's content.", 'autodescription' ) );

		?>
		<hr>
		<?php
		foreach ( $settings as $setting ) {
			printf(
				'<p><label for=%s><strong>%s</strong> %s</label></p>',
				\esc_attr( Input::get_field_id( $setting['setting'] ) ),
				\esc_html( $setting['label'] ),
				$setting['info'], // phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- should be escaped in list.
			);
			printf(
				'<p><input type=text name=%s class="large-text ltr" id=%s placeholder="%s" value="%s"></p>',
				\esc_attr( Input::get_field_name( $setting['setting'] ) ),
				\esc_attr( Input::get_field_id( $setting['setting'] ) ),
				\esc_attr( $setting['placeholder'] ),
				\esc_attr( Data\Plugin::get_option( $setting['setting'] ) ),
			);
		}
endswitch;
