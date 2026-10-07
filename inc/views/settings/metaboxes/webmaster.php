<?php
/**
 * @package The_SEO_Framework\Views\Admin\Metaboxes
 * @subpackage The_SEO_Framework\Admin\Settings
 */

namespace The_SEO_Framework;

( \defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) ) or die;

use The_SEO_Framework\Admin\Settings\Layout\{
	HTML,
	Input,
};

// phpcs:disable WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * The SEO Framework plugin
 * Copyright (C) 2016 - 2025 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
				'description' => \__( 'Get the Google verification code.', 'autodescription' ),
				'link'        => 'https://search.google.com/search-console/ownership?resource_id=' . rawurlencode( $site_url ),
				'placeholder' => 'ab1cDe2Fg3HI4Jklm5nOpqRSt67UVW78XYzAbcdEfgH',
			],
			'bing'      => [
				'setting'     => 'bing_verification',
				'label'       => \__( 'Bing Webmaster Verification Code', 'autodescription' ),
				'description' => \__( 'Get the Bing verification code.', 'autodescription' ),
				'link'        => 'https://www.bing.com/webmaster/home/addsite?addurl=' . rawurlencode( $site_url ),
				'placeholder' => '123A456B78901C2D3456E7890F1A234D',
			],
			'yandex'    => [
				'setting'     => 'yandex_verification',
				'label'       => \__( 'Yandex Webmaster Verification Code', 'autodescription' ),
				'description' => \__( 'Get the Yandex verification code.', 'autodescription' ),
				'link'        => 'https://webmaster.yandex.com/sites/add/?hostName=' . rawurlencode( $site_url ),
				'placeholder' => '12345abc678901d2',
			],
			'baidu'     => [
				'setting'     => 'baidu_verification',
				/* translators: literal translation from '百度搜索资源平台'-Code */
				'label'       => \__( 'Baidu Search Resource Platform Code', 'autodescription' ),
				'description' => \__( 'Get the Baidu verification code.', 'autodescription' ),
				'link'        => 'https://ziyuan.baidu.com/login/index?u=/site/siteadd',
				'placeholder' => 'a12bcDEFGa',
			],
			'pinterest' => [
				'setting'     => 'pint_verification',
				'label'       => \__( 'Pinterest Analytics Verification Code', 'autodescription' ),
				'description' => \__( 'Get the Pinterest verification code.', 'autodescription' ),
				'link'        => 'https://analytics.pinterest.com/',
				'placeholder' => '123456a7b8901de2fa34bcdef5a67b90',
			],
			'facebook'  => [
				'setting'     => 'facebook_verification',
				'label'       => \__( 'Facebook Domain Verification Code', 'autodescription' ),
				'description' => \__( 'Get the Facebook verification code.', 'autodescription' ),
				'link'        => 'https://business.facebook.com/settings/owned-domains',
				'placeholder' => 'abc1d234efghij56k7l8mn9op1qrst',
			],
		];

		HTML::header_title( \__( 'Webmaster Integration Settings', 'autodescription' ) );
		HTML::description( \__( "When adding your website to Google, Bing and other Webmaster Tools, you'll be asked to add a code or file to your website for verification purposes. These options will help you easily integrate those codes.", 'autodescription' ) );
		HTML::description( \__( "Verifying your website has no SEO value whatsoever. But you might gain added benefits such as search ranking insights to help you improve your website's content.", 'autodescription' ) );

		?>
		<hr>
		<?php
		foreach ( $settings as $setting ) {
			?>
			<p>
				<label for="<?php Input::field_id( $setting['setting'] ); ?>">
					<strong><?= \esc_html( $setting['label'] ) ?></strong>
					<?php
					HTML::make_info(
						$setting['description'],
						$setting['link'],
					);
					?>
				</label>
			</p>
			<p>
				<input type=text name="<?php Input::field_name( $setting['setting'] ); ?>" class="large-text ltr" id="<?php Input::field_id( $setting['setting'] ); ?>" placeholder="<?= \esc_attr( $setting['placeholder'] ) ?>" value="<?= \esc_attr( Data\Plugin::get_option( $setting['setting'] ) ) ?>">
			</p>
			<?php
		}
endswitch;
