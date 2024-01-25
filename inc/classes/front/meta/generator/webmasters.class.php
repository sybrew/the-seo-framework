<?php
/**
 * @package The_SEO_Framework\Classes\Front\Front\Meta\Generator
 * @subpackage The_SEO_Framework\Meta\Webmasters
 */

namespace The_SEO_Framework\Front\Meta\Generator;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Data;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

/**
 * Holds webmaster verification generators for meta tag output.
 *
 * @since 5.0.0
 * @access private
 */
final class Webmasters {

	/**
	 * @since 5.0.0
	 * @var callable[] GENERATORS A list of autoloaded meta callbacks.
	 */
	public const GENERATORS = [
		[ __CLASS__, 'generate_google_verification' ],
		[ __CLASS__, 'generate_bing_verification' ],
		[ __CLASS__, 'generate_yandex_verification' ],
		[ __CLASS__, 'generate_baidu_verification' ],
		[ __CLASS__, 'generate_pinterest_verification' ],
	];

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_google_verification() {

		$code = Data\Plugin::get_option( 'google_verification' );

		if ( \has_filter( 'the_seo_framework_googlesite_output' ) ) {
			/**
			 * @since 2.6.0
			 * @since 5.0.0 Deprecated
			 * @deprecated
			 * @param string $code The Google verification code.
			 * @param int    $id   The current post or term ID.
			 */
			$code = (string) \apply_filters_deprecated(
				'the_seo_framework_googlesite_output',
				[
					$code,
					\The_SEO_Framework\Helper\Query::get_the_real_id(),
				],
				'5.0.0 of The SEO Framework',
				'the_seo_framework_meta_render_data',
			);
		}

		if ( $code )
			yield 'google-site-verification' => [
				'attributes' => [
					'name'    => 'google-site-verification',
					'content' => $code,
				],
			];
	}

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_bing_verification() {

		$code = Data\Plugin::get_option( 'bing_verification' );

		if ( \has_filter( 'the_seo_framework_bingsite_output' ) ) {
			/**
			 * @since 2.6.0
			 * @since 5.0.0 Deprecated
			 * @deprecated
			 * @param string $code The Bing verification code.
			 * @param int    $id   The current post or term ID.
			 */
			$code = (string) \apply_filters_deprecated(
				'the_seo_framework_bingsite_output',
				[
					$code,
					\The_SEO_Framework\Helper\Query::get_the_real_id(),
				],
				'5.0.0 of The SEO Framework',
				'the_seo_framework_meta_render_data',
			);
		}

		if ( $code )
			yield 'msvalidate.01' => [
				'attributes' => [
					'name'    => 'msvalidate.01',
					'content' => $code,
				],
			];
	}

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_yandex_verification() {

		$code = Data\Plugin::get_option( 'yandex_verification' );

		if ( \has_filter( 'the_seo_framework_yandexsite_output' ) ) {
			/**
			 * @since 2.6.0
			 * @since 5.0.0 Deprecated
			 * @deprecated
			 * @param string $code The Yandex verification code.
			 * @param int    $id   The current post or term ID.
			 */
			$code = (string) \apply_filters_deprecated(
				'the_seo_framework_yandexsite_output',
				[
					$code,
					\The_SEO_Framework\Helper\Query::get_the_real_id(),
				],
				'5.0.0 of The SEO Framework',
				'the_seo_framework_meta_render_data',
			);
		}

		if ( $code )
			yield 'yandex-verification' => [
				'attributes' => [
					'name'    => 'yandex-verification',
					'content' => $code,
				],
			];
	}

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_baidu_verification() {

		$code = Data\Plugin::get_option( 'baidu_verification' );

		if ( \has_filter( 'the_seo_framework_baidusite_output' ) ) {
			/**
			 * @since 4.0.5
			 * @since 5.0.0 Deprecated
			 * @deprecated
			 * @param string $code The Baidu verification code.
			 * @param int    $id   The current post or term ID.
			 */
			$code = (string) \apply_filters_deprecated(
				'the_seo_framework_baidusite_output',
				[
					$code,
					\The_SEO_Framework\Helper\Query::get_the_real_id(),
				],
				'5.0.0 of The SEO Framework',
				'the_seo_framework_meta_render_data',
			);
		}

		if ( $code )
			yield 'baidu-site-verification' => [
				'attributes' => [
					'name'    => 'baidu-site-verification',
					'content' => $code,
				],
			];
	}

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_pinterest_verification() {

		$code = Data\Plugin::get_option( 'pint_verification' );

		if ( \has_filter( 'the_seo_framework_pintsite_output' ) ) {
			/**
			 * @since 2.6.0
			 * @since 5.0.0 Deprecated
			 * @deprecated
			 * @param string $code The Pinterest verification code.
			 * @param int    $id   The current post or term ID.
			 */
			$code = (string) \apply_filters_deprecated(
				'the_seo_framework_pintsite_output',
				[
					$code,
					\The_SEO_Framework\Helper\Query::get_the_real_id(),
				],
				'5.0.0 of The SEO Framework',
				'the_seo_framework_meta_render_data',
			);
		}

		if ( $code )
			yield 'p:domain_verify' => [
				'attributes' => [
					'name'    => 'p:domain_verify',
					'content' => $code,
				],
			];
	}
}
