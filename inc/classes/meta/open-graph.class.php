<?php
/**
 * @package The_SEO_Framework\Classes\Meta
 * @subpackage The_SEO_Framework\Meta\Open_Graph
 */

namespace The_SEO_Framework\Meta;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\{
	coalesce_strlen,
	memo,
	normalize_generation_args,
};

use \The_SEO_Framework\{
	Data,
	Data\Filter\Sanitize,
	Helper\Query,
};

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
 * Holds getters for meta tag output.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->open_graph() instead.
 */
class Open_Graph {

	/**
	 * Returns an array of the collected robots meta assertions.
	 *
	 * This only works when generate_robots_meta()'s $options value was given:
	 * The_SEO_Framework\ROBOTS_ASSERT (0b100);
	 *
	 * @since 5.0.0
	 *
	 * @return array
	 */
	public static function get_type() {

		switch ( true ) {
			case Query::is_product():
				$type = 'product';
				break;
			case Query::is_single():
				$type = 'article';
				break;
			case Query::is_author():
				$type = 'profile';
				break;
			default:
				$type = 'website';
				break;
		}

		if ( \has_filter( 'the_seo_framework_ogtype_output' ) ) {
			/**
			 * @since 2.3.0
			 * @since 2.7.0 Added output within filter.
			 * @since 5.0.0 Deprecated
			 * @deprecated
			 * @param string $type The OG type.
			 * @param int    $id   The page/term/object ID.
			 */
			$type = (string) \apply_filters_deprecated(
				'the_seo_framework_ogtype_output',
				[
					$type,
					Query::get_the_real_id(),
				],
				'5.0.0 of The SEO Framework',
				'the_seo_framework_meta_render_data',
			);
		}

		return $type;
	}

	/**
	 * Returns the Open Graph meta title.
	 * Falls back to meta title.
	 *
	 * @since 5.0.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return string Open Graph Title.
	 */
	public static function get_title( $args = null ) {
		return coalesce_strlen( static::get_custom_title( $args ) )
			?? static::get_generated_title( $args );
	}

	/**
	 * Returns the Open Graph meta title from custom field.
	 * Falls back to meta title.
	 *
	 * @since 5.0.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return string Open Graph Title.
	 */
	public static function get_custom_title( $args = null ) {
		return isset( $args )
			? static::get_custom_title_from_args( $args )
			: static::get_custom_title_from_query();
	}

	/**
	 * Returns the Twitter meta title from custom field, based on query.
	 * Falls back to meta title.
	 *
	 * @since 5.0.0
	 *
	 * @return string Open Graph Title.
	 */
	public static function get_custom_title_from_query() {

		if ( Query::is_real_front_page() ) {
			if ( Query::is_static_front_page() ) {
				$title = coalesce_strlen( Data\Plugin::get_option( 'homepage_og_title' ) )
					  ?? Data\Plugin\Post::get_meta_item( '_open_graph_title' );
			} else {
				$title = Data\Plugin::get_option( 'homepage_og_title' );
			}
		} elseif ( Query::is_singular() ) {
			$title = Data\Plugin\Post::get_meta_item( '_open_graph_title' );
		} elseif ( Query::is_editable_term() ) {
			$title = Data\Plugin\Term::get_meta_item( 'og_title' );
		} elseif ( \is_post_type_archive() ) {
			$title = Data\Plugin\PTA::get_meta_item( 'og_title' );
		}

		if ( ! isset( $title ) ) return '';

		if ( \strlen( $title ) )
			return Sanitize::metadata_content( $title );

		// At least there was an attempt made to fetch a title when we reach this. Try harder.
		return Title::get_custom_title( null, true );
	}

	/**
	 * Returns the Open Graph meta title from custom field, based on query.
	 * Falls back to meta title.
	 *
	 * @since 5.0.0
	 *
	 * @param array $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 * @return string Open Graph Title.
	 */
	public static function get_custom_title_from_args( $args ) {

		normalize_generation_args( $args );

		if ( $args['tax'] ) {
			$title = Data\Plugin\Term::get_meta_item( 'og_title', $args['id'] );
		} elseif ( $args['pta'] ) {
			$title = Data\Plugin\PTA::get_meta_item( 'og_title', $args['pta'] );
		} elseif ( empty( $args['uid'] ) && Query::is_real_front_page_by_id( $args['id'] ) ) {
			if ( $args['id'] ) {
				$title = coalesce_strlen( Data\Plugin::get_option( 'homepage_og_title' ) )
					  ?? Data\Plugin\Post::get_meta_item( '_open_graph_title', $args['id'] );
			} else {
				$title = Data\Plugin::get_option( 'homepage_og_title' );
			}
		} elseif ( $args['id'] ) {
			$title = Data\Plugin\Post::get_meta_item( '_open_graph_title', $args['id'] );
		}

		if ( ! isset( $title ) ) return '';

		if ( \strlen( $title ) )
			return Sanitize::metadata_content( $title );

		// At least there was an attempt made to fetch a title when we reach this. Try harder.
		return Title::get_custom_title( $args, true );
	}

	/**
	 * Returns the autogenerated Open Graph meta title.
	 * Falls back to generated meta title.
	 *
	 * @since 5.0.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return string The generated Open Graph Title.
	 */
	public static function get_generated_title( $args = null ) {
		return Title::get_generated_title( $args, true );
	}

	/**
	 * Returns the Open Graph meta description. Falls back to meta description.
	 *
	 * @since 5.0.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return string The real Open Graph description output.
	 */
	public static function get_description( $args = null ) {
		return coalesce_strlen( static::get_custom_description( $args ) )
			?? static::get_generated_description( $args );
	}

	/**
	 * Returns the Open Graph meta description from custom field.
	 * Falls back to meta description.
	 *
	 * @since 5.0.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return string TwOpen Graphitter description.
	 */
	public static function get_custom_description( $args = null ) {
		return isset( $args )
			? static::get_custom_description_from_args( $args )
			: static::get_custom_description_from_query();
	}

	/**
	 * Returns the Open Graph meta description from custom field, based on query.
	 * Falls back to meta description.
	 *
	 * @since 5.0.0
	 *
	 * @return string Open Graph description.
	 */
	public static function get_custom_description_from_query() {

		if ( Query::is_real_front_page() ) {
			if ( Query::is_static_front_page() ) {
				$desc = coalesce_strlen( Data\Plugin::get_option( 'homepage_og_description' ) )
					 ?? Data\Plugin\Post::get_meta_item( '_open_graph_description' );
			} else {
				$desc = Data\Plugin::get_option( 'homepage_og_description' );
			}
		} elseif ( Query::is_singular() ) {
			$desc = Data\Plugin\Post::get_meta_item( '_open_graph_description' );
		} elseif ( Query::is_editable_term() ) {
			$desc = Data\Plugin\Term::get_meta_item( 'og_description' );
		} elseif ( \is_post_type_archive() ) {
			$desc = Data\Plugin\PTA::get_meta_item( 'og_description' );
		}

		if ( ! isset( $desc ) ) return '';

		if ( \strlen( $desc ) )
			return Sanitize::metadata_content( $desc );

		// At least there was an attempt made to fetch a description when we reach this. Try harder.
		return Description::get_custom_description();
	}

	/**
	 * Returns the Open Graph meta description from custom field, based on arguments.
	 * Falls back to meta description.
	 *
	 * @since 5.0.0
	 *
	 * @param array $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 * @return string Open Graph description.
	 */
	public static function get_custom_description_from_args( $args ) {

		normalize_generation_args( $args );

		if ( $args['tax'] ) {
			$desc = Data\Plugin\Term::get_meta_item( 'og_description', $args['id'] );
		} elseif ( $args['pta'] ) {
			$desc = Data\Plugin\PTA::get_meta_item( 'og_description', $args['pta'] );
		} elseif ( empty( $args['uid'] ) && Query::is_real_front_page_by_id( $args['id'] ) ) {
			if ( $args['id'] ) {
				$desc = coalesce_strlen( Data\Plugin::get_option( 'homepage_og_description' ) )
					 ?? Data\Plugin\Post::get_meta_item( '_open_graph_description', $args['id'] );
			} else {
				$desc = Data\Plugin::get_option( 'homepage_og_description' );
			}
		} elseif ( $args['id'] ) {
			$desc = Data\Plugin\Post::get_meta_item( '_open_graph_description', $args['id'] );
		}

		if ( ! isset( $desc ) ) return '';

		if ( \strlen( $desc ) )
			return Sanitize::metadata_content( $desc );

		// At least there was an attempt made to fetch a description when we reach this. Try harder.
		return Description::get_custom_description( $args );
	}

	/**
	 * Returns the autogenerated Open Graph meta description. Falls back to meta description.
	 *
	 * @since 5.0.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return string The generated Open Graph description output.
	 */
	public static function get_generated_description( $args = null ) {
		return Description::get_generated_description( $args, 'opengraph' );
	}

	/**
	 * Returns the locale for Open Graph.
	 *
	 * @since 5.0.0
	 *
	 * @return string
	 */
	public static function get_locale() {

		$locale = \get_locale();

		$locale_len    = \strlen( $locale );
		$valid_locales = static::get_supported_locales(); // [ ll_LL => ll ]

		if ( $locale_len > 5 ) {
			$locale_len = 5;
			// More than standard-full locale type is used. Make it just full.
			$locale = substr( $locale, 0, $locale_len );
		}

		if ( 5 === $locale_len ) {
			// Full locale is used. See if it's valid and return it.
			if ( isset( $valid_locales[ $locale ] ) )
				return $locale;

			// Convert to only language portion.
			$locale_len = 2;
			$locale     = substr( $locale, 0, $locale_len );
		}

		if ( 2 === $locale_len ) {
			// Only two letters of the lang are provided. Find first locale and return it.
			$key = array_search( $locale, $valid_locales, true );

			if ( $key )
				return $key;
		}

		// Return default WordPress locale.
		return 'en_US';
	}

	/**
	 * Returns the locale for Open Graph.
	 *
	 * @since 5.0.0
	 *
	 * @return string
	 */
	public static function get_site_name() {
		return Data\Blog::get_public_blog_name();
	}

	/**
	 * Returns the locale for Open Graph.
	 *
	 * @since 5.0.0
	 *
	 * @return string
	 */
	public static function get_url() {
		return URI::get_canonical_url();
	}

	/**
	 * Returns the article published time for Open Graph.
	 *
	 * @since 5.0.0
	 *
	 * @return string
	 */
	public static function get_article_published_time() {

		if ( ! Data\Plugin::get_option( 'post_publish_time' ) || ! Query::is_single() )
			return '';

		return Data\Post::get_published_time();
	}

	/**
	 * Returns the locale for Open Graph.
	 *
	 * @since 5.0.0
	 *
	 * @return string
	 */
	public static function get_article_modified_time() {

		if ( ! Data\Plugin::get_option( 'post_modify_time' ) || ! Query::is_single() )
			return '';

		return Data\Post::get_modified_time();
	}

	/**
	 * Returns supported social site locales.
	 *
	 * @since 5.0.0
	 * @see https://www.facebook.com/translations/FacebookLocales.xml (deprecated)
	 * @see https://wordpress.org/support/topic/oglocale-problem/#post-11456346
	 * mirror: http://web.archive.org/web/20190601043836/https://wordpress.org/support/topic/oglocale-problem/
	 *
	 * @return array Valid social locales
	 */
	public static function get_supported_locales() {
		return [
			'af_ZA' => 'af',  // Afrikaans
			'ak_GH' => 'ak',  // Akan
			'am_ET' => 'am',  // Amharic
			'ar_AR' => 'ar',  // Arabic
			'as_IN' => 'as',  // Assamese
			'ay_BO' => 'ay',  // Aymara
			'az_AZ' => 'az',  // Azerbaijani
			'be_BY' => 'be',  // Belarusian
			'bg_BG' => 'bg',  // Bulgarian
			'bn_IN' => 'bn',  // Bengali
			'br_FR' => 'br',  // Breton
			'bs_BA' => 'bs',  // Bosnian
			'ca_ES' => 'ca',  // Catalan
			'cb_IQ' => 'cb',  // Sorani Kurdish
			'ck_US' => 'ck',  // Cherokee
			'co_FR' => 'co',  // Corsican
			'cs_CZ' => 'cs',  // Czech
			'cx_PH' => 'cx',  // Cebuano
			'cy_GB' => 'cy',  // Welsh
			'da_DK' => 'da',  // Danish
			'de_DE' => 'de',  // German
			'el_GR' => 'el',  // Greek
			'en_GB' => 'en',  // English (UK)
			'en_IN' => 'en',  // English (India)
			'en_PI' => 'en',  // English (Pirate)
			'en_UD' => 'en',  // English (Upside Down)
			'en_US' => 'en',  // English (US)
			'eo_EO' => 'eo',  // Esperanto
			'es_CL' => 'es',  // Spanish (Chile)
			'es_CO' => 'es',  // Spanish (Colombia)
			'es_ES' => 'es',  // Spanish (Spain)
			'es_LA' => 'es',  // Spanish
			'es_MX' => 'es',  // Spanish (Mexico)
			'es_VE' => 'es',  // Spanish (Venezuela)
			'et_EE' => 'et',  // Estonian
			'eu_ES' => 'eu',  // Basque
			'fa_IR' => 'fa',  // Persian
			'fb_LT' => 'fb',  // Leet Speak
			'ff_NG' => 'ff',  // Fulah
			'fi_FI' => 'fi',  // Finnish
			'fo_FO' => 'fo',  // Faroese
			'fr_CA' => 'fr',  // French (Canada)
			'fr_FR' => 'fr',  // French (France)
			'fy_NL' => 'fy',  // Frisian
			'ga_IE' => 'ga',  // Irish
			'gl_ES' => 'gl',  // Galician
			'gn_PY' => 'gn',  // Guarani
			'gu_IN' => 'gu',  // Gujarati
			'gx_GR' => 'gx',  // Classical Greek
			'ha_NG' => 'ha',  // Hausa
			'he_IL' => 'he',  // Hebrew
			'hi_IN' => 'hi',  // Hindi
			'hr_HR' => 'hr',  // Croatian
			'hu_HU' => 'hu',  // Hungarian
			'hy_AM' => 'hy',  // Armenian
			'id_ID' => 'id',  // Indonesian
			'ig_NG' => 'ig',  // Igbo
			'is_IS' => 'is',  // Icelandic
			'it_IT' => 'it',  // Italian
			'ja_JP' => 'ja',  // Japanese
			'ja_KS' => 'ja',  // Japanese (Kansai)
			'jv_ID' => 'jv',  // Javanese
			'ka_GE' => 'ka',  // Georgian
			'kk_KZ' => 'kk',  // Kazakh
			'km_KH' => 'km',  // Khmer
			'kn_IN' => 'kn',  // Kannada
			'ko_KR' => 'ko',  // Korean
			'ku_TR' => 'ku',  // Kurdish (Kurmanji)
			'ky_KG' => 'ky',  // Kyrgyz
			'la_VA' => 'la',  // Latin
			'lg_UG' => 'lg',  // Ganda
			'li_NL' => 'li',  // Limburgish
			'ln_CD' => 'ln',  // Lingala
			'lo_LA' => 'lo',  // Lao
			'lt_LT' => 'lt',  // Lithuanian
			'lv_LV' => 'lv',  // Latvian
			'mg_MG' => 'mg',  // Malagasy
			'mi_NZ' => 'mi',  // Māori
			'mk_MK' => 'mk',  // Macedonian
			'ml_IN' => 'ml',  // Malayalam
			'mn_MN' => 'mn',  // Mongolian
			'mr_IN' => 'mr',  // Marathi
			'ms_MY' => 'ms',  // Malay
			'mt_MT' => 'mt',  // Maltese
			'my_MM' => 'my',  // Burmese
			'nb_NO' => 'nb',  // Norwegian (bokmal)
			'nd_ZW' => 'nd',  // Ndebele
			'ne_NP' => 'ne',  // Nepali
			'nl_BE' => 'nl',  // Dutch (België)
			'nl_NL' => 'nl',  // Dutch
			'nn_NO' => 'nn',  // Norwegian (nynorsk)
			'ny_MW' => 'ny',  // Chewa
			'or_IN' => 'or',  // Oriya
			'pa_IN' => 'pa',  // Punjabi
			'pl_PL' => 'pl',  // Polish
			'ps_AF' => 'ps',  // Pashto
			'pt_BR' => 'pt',  // Portuguese (Brazil)
			'pt_PT' => 'pt',  // Portuguese (Portugal)
			'qu_PE' => 'qu',  // Quechua
			'rm_CH' => 'rm',  // Romansh
			'ro_RO' => 'ro',  // Romanian
			'ru_RU' => 'ru',  // Russian
			'rw_RW' => 'rw',  // Kinyarwanda
			'sa_IN' => 'sa',  // Sanskrit
			'sc_IT' => 'sc',  // Sardinian
			'se_NO' => 'se',  // Northern Sámi
			'si_LK' => 'si',  // Sinhala
			'sk_SK' => 'sk',  // Slovak
			'sl_SI' => 'sl',  // Slovenian
			'sn_ZW' => 'sn',  // Shona
			'so_SO' => 'so',  // Somali
			'sq_AL' => 'sq',  // Albanian
			'sr_RS' => 'sr',  // Serbian
			'sv_SE' => 'sv',  // Swedish
			'sy_SY' => 'sy',  // Swahili
			'sw_KE' => 'sw',  // Syriac
			'sz_PL' => 'sz',  // Silesian
			'ta_IN' => 'ta',  // Tamil
			'te_IN' => 'te',  // Telugu
			'tg_TJ' => 'tg',  // Tajik
			'th_TH' => 'th',  // Thai
			'tk_TM' => 'tk',  // Turkmen
			'tl_PH' => 'tl',  // Filipino
			'tl_ST' => 'tl',  // Klingon
			'tr_TR' => 'tr',  // Turkish
			'tt_RU' => 'tt',  // Tatar
			'tz_MA' => 'tz',  // Tamazight
			'uk_UA' => 'uk',  // Ukrainian
			'ur_PK' => 'ur',  // Urdu
			'uz_UZ' => 'uz',  // Uzbek
			'vi_VN' => 'vi',  // Vietnamese
			'wo_SN' => 'wo',  // Wolof
			'xh_ZA' => 'xh',  // Xhosa
			'yi_DE' => 'yi',  // Yiddish
			'yo_NG' => 'yo',  // Yoruba
			'zh_CN' => 'zh',  // Simplified Chinese (China)
			'zh_HK' => 'zh',  // Traditional Chinese (Hong Kong)
			'zh_TW' => 'zh',  // Traditional Chinese (Taiwan)
			'zu_ZA' => 'zu',  // Zulu
			'zz_TR' => 'zz',  // Zazaki
		];
	}
}
