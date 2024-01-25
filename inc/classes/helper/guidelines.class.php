<?php
/**
 * @package The_SEO_Framework\Classes\Helper\Guidelines
 * @subpackage The_SEO_Framework\Admin
 */

namespace The_SEO_Framework\Helper;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\memo;

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
 * Holds a collection of helper methods for title and description guidelines.
 *
 * @since 5.0.0
 * @access private
 */
class Guidelines {

	/**
	 * Returns the title and description input guideline table, for
	 * (Google) search, Open Graph, and Twitter.
	 *
	 * Memoizes the output, so the return filter will run only once.
	 *
	 * NB: Some scripts have wide characters. These are recognized by Google, and have been adjusted for in the chactacter
	 * guidelines. German is a special Case, where we account for the Capitalization of Nouns.
	 *
	 * NB: Although the Arabic & Farsi scripts are much smaller in width, Google seems to be using the 160 & 70 char limits
	 * strictly... As such, we stricten the guidelines for pixels instead.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 1. Now gives different values for various WordPress locales.
	 *              2. Added $locale input parameter.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `get_input_guidelines()`.
	 *
	 * @TODO Consider splitting up search into Google, Bing, etc., as we might
	 *       want users to set their preferred search engine. Now, these engines
	 *       are barely any different.
	 *
	 * @param ?string $locale The locale to test. If empty, it will be auto-determined.
	 * @return array
	 */
	public static function get_text_size_guidelines( $locale = null ) {

		// Strip the "_formal" and other suffixes. 5 length max: xx_YY
		$locale = substr( $locale ?? \get_locale(), 0, 5 );

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo( null, $locale ) ) return $memo;

		// phpcs:disable, WordPress.WhiteSpace.OperatorSpacing.SpacingAfter
		$character_adjustments = [
			'as'    => 148 / 160, // Assamese (অসমীয়া)
			'de_AT' => 158 / 160, // Austrian German (Österreichisch Deutsch)
			'de_CH' => 158 / 160, // Swiss German (Schweiz Deutsch)
			'de_DE' => 158 / 160, // German (Deutsch)
			'gu'    => 148 / 160, // Gujarati (ગુજરાતી)
			'ml_IN' => 100 / 160, // Malayalam (മലയാളം)
			'ja'    =>  70 / 160, // Japanese (日本語)
			'ko_KR' =>  82 / 160, // Korean (한국어)
			'ta_IN' => 120 / 160, // Tamil (தமிழ்)
			'zh_TW' =>  70 / 160, // Taiwanese Mandarin (Traditional Chinese) (繁體中文)
			'zh_HK' =>  70 / 160, // Hong Kong (Chinese version) (香港中文版)
			'zh_CN' =>  70 / 160, // Mandarin (Simplified Chinese) (简体中文)
		];
		// phpcs:enable, WordPress.WhiteSpace.OperatorSpacing.SpacingAfter

		// Default to 1 (160/160 = no adjustment).
		$c_adjust = $character_adjustments[ $locale ] ?? 1;

		$pixel_adjustments = [
			'ar'    => 760 / 910, // Arabic (العربية)
			'ary'   => 760 / 910, // Moroccan Arabic (العربية المغربية)
			'azb'   => 760 / 910, // South Azerbaijani (گؤنئی آذربایجان)
			'fa_IR' => 760 / 910, // Iran Farsi (فارسی)
			'haz'   => 760 / 910, // Hazaragi (هزاره گی)
			'ckb'   => 760 / 910, // Central Kurdish (كوردی)
		];

		// Default to 1 (910/910 = no adjustment).
		$p_adjust = $pixel_adjustments[ $locale ] ?? 1;

		// phpcs:disable, WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
		/**
		 * @since 3.1.0
		 * @since 4.2.7 Added two more parameters (`$c_adjust` and `$locale`)
		 * @todo rename to match function?
		 * @param array                      $guidelines The title and description guidelines.
		 *                                   Don't alter the format. Only change the numeric values.
		 * @param array[$c_adjust,$p_adjust] The guideline calibration (Character and Pixels respectively).
		 * @param string                     $locale The current locale.
		 */
		return memo(
			(array) \apply_filters(
				'the_seo_framework_input_guidelines',
				[
					'title' => [
						'search' => [
							'chars'  => [
								'lower'     => (int) ( 25 * $c_adjust ),
								'goodLower' => (int) ( 35 * $c_adjust ),
								'goodUpper' => (int) ( 65 * $c_adjust ),
								'upper'     => (int) ( 75 * $c_adjust ),
							],
							'pixels' => [
								'lower'     => (int) ( 200 * $p_adjust ),
								'goodLower' => (int) ( 280 * $p_adjust ),
								'goodUpper' => (int) ( 520 * $p_adjust ),
								'upper'     => (int) ( 600 * $p_adjust ),
							],
						],
						'opengraph' => [
							'chars'  => [
								'lower'     => 15,
								'goodLower' => 25,
								'goodUpper' => 88,
								'upper'     => 100,
							],
							'pixels' => [],
						],
						'twitter' => [
							'chars'  => [
								'lower'     => 15,
								'goodLower' => 25,
								'goodUpper' => 69,
								'upper'     => 70,
							],
							'pixels' => [],
						],
					],
					'description' => [
						'search' => [
							'chars'  => [
								'lower'     => (int) ( 45 * $c_adjust ),
								'goodLower' => (int) ( 80 * $c_adjust ),
								'goodUpper' => (int) ( 160 * $c_adjust ),
								'upper'     => (int) ( 320 * $c_adjust ),
							],
							'pixels' => [
								'lower'     => (int) ( 256 * $p_adjust ),
								'goodLower' => (int) ( 455 * $p_adjust ),
								'goodUpper' => (int) ( 910 * $p_adjust ),
								'upper'     => (int) ( 1820 * $p_adjust ),
							],
						],
						'opengraph' => [
							'chars'  => [
								'lower'     => 45,
								'goodLower' => 80,
								'goodUpper' => 200,
								'upper'     => 300,
							],
							'pixels' => [],
						],
						'twitter' => [
							'chars'  => [
								'lower'     => 45,
								'goodLower' => 80,
								'goodUpper' => 200,
								'upper'     => 200,
							],
							'pixels' => [],
						],
					],
				],
				[ $c_adjust, $p_adjust ],
				$locale,
			),
			$locale,
		);
		// phpcs:enable, WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
	}

	/**
	 * Returns the title and description input guideline explanatory table.
	 *
	 * Already attribute-escaped.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 Now added a short leading-dot version for ARIA labels.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Added memoization.
	 *
	 * @return array
	 */
	public static function get_text_size_guidelines_i18n() {
		return memo() ?? memo( [
			'long'     => [
				'empty'       => \esc_attr__( "There's no content.", 'autodescription' ),
				'farTooShort' => \esc_attr__( "It's too short and it should have more information.", 'autodescription' ),
				'tooShort'    => \esc_attr__( "It's short and it could have more information.", 'autodescription' ),
				'tooLong'     => \esc_attr__( "It's long and it might get truncated in search.", 'autodescription' ),
				'farTooLong'  => \esc_attr__( "It's too long and it will get truncated in search.", 'autodescription' ),
				'good'        => \esc_attr__( 'Length is good.', 'autodescription' ),
			],
			'short'    => [
				'empty'       => \esc_attr_x( 'Empty', 'The text field is empty', 'autodescription' ),
				'farTooShort' => \esc_attr__( 'Far too short', 'autodescription' ),
				'tooShort'    => \esc_attr__( 'Too short', 'autodescription' ),
				'tooLong'     => \esc_attr__( 'Too long', 'autodescription' ),
				'farTooLong'  => \esc_attr__( 'Far too long', 'autodescription' ),
				'good'        => \esc_attr__( 'Good', 'autodescription' ),
			],
			'shortdot' => [
				'empty'       => \esc_attr_x( 'Empty.', 'The text field is empty', 'autodescription' ),
				'farTooShort' => \esc_attr__( 'Far too short.', 'autodescription' ),
				'tooShort'    => \esc_attr__( 'Too short.', 'autodescription' ),
				'tooLong'     => \esc_attr__( 'Too long.', 'autodescription' ),
				'farTooLong'  => \esc_attr__( 'Far too long.', 'autodescription' ),
				'good'        => \esc_attr__( 'Good.', 'autodescription' ),
			],
		] );
	}
}
