<?php
/**
 * @package The_SEO_Framework\Classes\Meta
 * @subpackage The_SEO_Framework\Meta\Robots
 */

namespace The_SEO_Framework\Meta;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\{
	umemo,
	normalize_generation_args,
};

use \The_SEO_Framework\{
	Data,
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
 *         Use tsf()->robots() instead.
 */
class Robots {

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
	public static function get_collected_meta_assertions() {
		return Robots\Main::instance()->collect_assertions();
	}

	/**
	 * Returns the robots front-end meta value.
	 * Memoizes the return value.
	 *
	 * @since 5.0.0
	 *
	 * @return string
	 */
	public static function get_meta() {
		return umemo( __METHOD__ ) ?? umemo(
			__METHOD__,
			Data\Blog::is_public()
				? implode( ',', static::get_generated_meta() )
				: '',
		);
	}

	/**
	 * Returns the `noindex`, `nofollow`, `noarchive` robots meta code array.
	 *
	 * @since 3.2.4
	 * @since 5.0.0 1. Renamed from `get_robots_meta()`
	 *              2. Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param array|null $args    The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                            Leave null to autodetermine query.
	 * @param array|null $get     The robots types to retrieve. Accepts an array of
	 *                            'noindex', 'nofollow', 'noarchive', 'max_snippet', 'max_image_preview', 'max_video_preview'.
	 *                            Leave null to retrieve all.
	 * }
	 * @param int <bit>  $options The options level. {
	 *    0 = 0b000: Ignore nothing. Collect no assertions. (Default front-end.)
	 *    1 = 0b001: Ignore protection. (\The_SEO_Framework\ROBOTS_IGNORE_PROTECTION)
	 *    2 = 0b010: Ignore post/term setting. (\The_SEO_Framework\ROBOTS_IGNORE_SETTINGS)
	 *    4 = 0b100: Collect assertions. (\The_SEO_Framework\ROBOTS_ASSERT)
	 * }
	 * @return array {
	 *     The generated robots meta. Only values actualized for display are sent back.
	 *
	 *     @type ?string $noindex           If set, it should be 'noindex'.
	 *     @type ?string $nofollow          If set, it should be 'nofollow'.
	 *     @type ?string $noarchive         If set, it should be 'noarchive'.
	 *     @type ?string $max_snippet       If set, it should be 'max-snippet:<R>=-1>',
	 *                                      where '<R>=-1>' is a number of or above -1.
	 *     @type ?string $max_image_preview If set, it should be 'max-image-preview:<none|standard|large>',
	 *                                      where any of 'none', 'standard', or 'large' is chosen.
	 *     @type ?string $max_video_preview If set, it should be 'max-video-preview:<R>=-1>',
	 *                                      where '<R>=-1>' is a number of or above -1.
	 * }
	 */
	public static function get_generated_meta( $args = null, $get = null, $options = 0b00 ) {

		// Sitemap function: We always normalize arguments here, for `isset( $args ) and` will add a jump.
		normalize_generation_args( $args );

		$meta = Robots\Main::instance()->set( $args, $options )->get( $get );

		foreach ( $meta as $k => $v ) {
			switch ( $k ) {
				case 'noindex':
				case 'nofollow':
				case 'noarchive':
					if ( $v ) {
						// Convert the [ 'noindex' => true ] to [ 'noindex' => 'noindex' ]
						$meta[ $k ] = $k;
					}
					break;
				case 'max_snippet':
				case 'max_image_preview':
				case 'max_video_preview':
					if ( false !== $v ) {
						// Convert the [ 'max_snippet' => x ] to [ 'max-snippet' => 'max-snippet:x' ]
						$meta[ $k ] = str_replace( '_', '-', $k ) . ":$v";
					}
			}
		}

		/**
		 * Filters the front-end robots array, and strips empty indexes thereafter.
		 *
		 * @since 2.6.0
		 * @since 4.0.0 Added two parameters ($args and $ignore).
		 * @since 4.0.2 Now contains the copyright directive values.
		 * @since 4.0.3 Changed `$meta` key `max_snippet_length` to `max_snippet`
		 * @since 4.2.0 Now supports the `$args['pta']` index.
		 *
		 * @param array      $meta {
		 *     The current robots meta.
		 *
		 *     @type ?string $noindex           If set, it should be 'noindex'.
		 *     @type ?string $nofollow          If set, it should be 'nofollow'.
		 *     @type ?string $noarchive         If set, it should be 'noarchive'.
		 *     @type ?string $max_snippet       If set, it should be 'max-snippet:<R>=-1>',
		 *                                      where '<R>=-1>' is a number of or above -1.
		 *     @type ?string $max_image_preview If set, it should be 'max-image-preview:<none|standard|large>',
		 *                                      where any of 'none', 'standard', or 'large' is chosen.
		 *     @type ?string $max_video_preview If set, it should be 'max-video-preview:<R>=-1>',
		 *                                      where '<R>=-1>' is a number of or above -1.
		 * }
		 * @param array|null $args The query arguments. Contains 'id', 'tax', 'pta', and 'uid'.
		 *                         Is null when the query is auto-determined.
		 * @param int <bit>  $options The ignore level. {
		 *    0 = 0b000: Ignore nothing. Collect nothing. (Default front-end.)
		 *    1 = 0b001: Ignore protection. (\The_SEO_Framework\ROBOTS_IGNORE_PROTECTION)
		 *    2 = 0b010: Ignore post/term setting. (\The_SEO_Framework\ROBOTS_IGNORE_SETTINGS)
		 *    4 = 0b100: Collect assertions. (\The_SEO_Framework\ROBOTS_ASSERT)
		 * }
		 */
		return array_filter( (array) \apply_filters(
			'the_seo_framework_robots_meta_array',
			$meta,
			$args,
			$options,
		) );
	}

	/**
	 * Determines if the post type has a robots value set.
	 *
	 * @since 3.1.0
	 * @since 4.0.5 The `$post_type` fallback now uses a real query ID, instead of `$GLOBALS['post']`;
	 *              mitigating issues with singular-archives pages (blog, shop, etc.).
	 * @since 4.1.1 Now tests for not empty, instead of isset. We no longer support PHP 5.4 since v4.0.0.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param string $type      Accepts 'noindex', 'nofollow', 'noarchive'.
	 * @param string $post_type The post type, optional. Leave empty to autodetermine type.
	 * @return bool True if noindex, nofollow, or noarchive is set; false otherwise.
	 */
	public static function is_post_type_robots_set( $type, $post_type = '' ) {
		return (bool) Data\Plugin::get_option(
			Data\Plugin\Helper::get_robots_option_index( 'post_type', $type ),
			$post_type ?: Query::get_current_post_type(),
		);
	}

	/**
	 * Determines if the taxonomy has a robots value set.
	 *
	 * @since 4.1.0
	 * @since 4.1.1 Now tests for not empty, instead of isset. We no longer support PHP 5.4 since v4.0.0.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param string $type     Accepts 'noindex', 'nofollow', 'noarchive'.
	 * @param string $taxonomy The taxonomy, optional. Leave empty to autodetermine type.
	 * @return bool True if noindex, nofollow, or noarchive is set; false otherwise.
	 */
	public static function is_taxonomy_robots_set( $type, $taxonomy = '' ) {
		return (bool) Data\Plugin::get_option(
			Data\Plugin\Helper::get_robots_option_index( 'taxonomy', $type ),
			$taxonomy ?: Query::get_current_taxonomy(),
		);
	}
}
