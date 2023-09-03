<?php
/**
 * @package The_SEO_Framework\Classes\Front\Meta\Generator
 * @subpackage The_SEO_Framework\Meta
 */

namespace The_SEO_Framework\Meta\Generator;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Meta\Factory;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds URI generators for meta tag output.
 *
 * @since 4.3.0
 * @access protected
 * @internal
 * @final Can't be extended.
 */
final class URI {

	/**
	 * @since 4.3.0
	 * @var callable[] GENERATORS A list of autoloaded meta callbacks.
	 */
	public const GENERATORS = [
		[ __CLASS__, 'generate_canonical_url' ],
		[ __CLASS__, 'generate_pagination_urls' ],
		[ __CLASS__, 'generate_shortlink' ],
	];

	/**
	 * @since 4.3.0
	 * @access protected
	 * @generator
	 */
	public static function generate_canonical_url() {

		$tsf = \tsf();

		$_url = $tsf->get_current_canonical_url();

		if ( \has_filter( 'the_seo_framework_rel_canonical_output' ) ) {
			/**
			 * @since 2.6.5
			 * @param string $url The canonical URL. Must be escaped.
			 * @param int    $id  The current page or term ID.
			 */
			$url = (string) \apply_filters_deprecated(
				'the_seo_framework_rel_canonical_output',
				[
					$_url,
					\The_SEO_Framework\Helper\Query::get_the_real_id(), // Lacking import OK.
				],
				'4.3.0 of The SEO Framework',
				'the_seo_framework_meta_render_data',
			);
		} else {
			$url = $_url;
		}

		// var_dump() offload this.
		// If the page should not be indexed, consider removing the canonical URL.
		if ( str_contains( Factory\Robots::get_meta(), 'noindex' ) ) {
			// If the URL is filtered, don't empty it.
			// If a custom canonical URL is set, don't empty it.
			if ( $url === $_url && ! $tsf->has_custom_canonical_url() ) {
				$url = '';
			}
		}
		// to this

		if ( $url )
			yield [
				'tag'        => 'link',
				'attributes' => [
					'rel'  => 'canonical',
					'href' => $url,
				],
			];
	}

	/**
	 * @since 4.3.0
	 * @access protected
	 * @generator
	 */
	public static function generate_pagination_urls() {

		$paged_urls = \tsf()->get_paged_urls();

		if ( \has_filter( 'the_seo_framework_paged_url_output_next' ) ) {
			/**
			 * @since 2.6.0
			 * @since 4.3.0 Deprecated
			 * @deprecated
			 * @param string $next The next-page URL.
			 * @param int    $id   The current post or term ID.
			 */
			$paged_urls['next'] = (string) \apply_filters_deprecated(
				'the_seo_framework_paged_url_output_next',
				[
					$paged_urls['next'],
					\The_SEO_Framework\Helper\Query::get_the_real_id(), // Lacking import OK.
				],
				'4.3.0 of The SEO Framework',
				'the_seo_framework_meta_render_data',
			);
		}
		if ( \has_filter( 'the_seo_framework_paged_url_output_prev' ) ) {
			/**
			 * @since 2.6.0
			 * @since 4.3.0 Deprecated
			 * @deprecated
			 * @param string $next The previous-page URL.
			 * @param int    $id   The current post or term ID.
			 */
			$paged_urls['prev'] = (string) \apply_filters_deprecated(
				'the_seo_framework_paged_url_output_prev',
				[
					$paged_urls['prev'],
					\The_SEO_Framework\Helper\Query::get_the_real_id(), // Lacking import OK.
				],
				'4.3.0 of The SEO Framework',
				'the_seo_framework_meta_render_data',
			);
		}

		if ( $paged_urls['prev'] )
			yield [
				'tag'        => 'link',
				'attributes' => [
					'rel'  => 'prev',
					'href' => $paged_urls['prev'],
				],
			];

		if ( $paged_urls['next'] )
			yield [
				'tag'        => 'link',
				'attributes' => [
					'rel'  => 'next',
					'href' => $paged_urls['next'],
				],
			];
	}

	/**
	 * @since 4.3.0
	 * @access protected
	 * @generator
	 */
	public static function generate_shortlink() {

		$url = \tsf()->get_shortlink();

		if ( \has_filter( 'the_seo_framework_googlesite_output' ) ) {
			/**
			 * @since 2.6.0
			 * @since 4.3.0 Deprecated
			 * @deprecated
			 * @param string $url The generated shortlink URL.
			 * @param int    $id  The current post or term ID.
			 */
			$url = (string) \apply_filters_deprecated(
				'the_seo_framework_shortlink_output',
				[
					$url,
					\The_SEO_Framework\Helper\Query::get_the_real_id(), // Lacking import OK.
				],
				'4.3.0 of The SEO Framework',
				'the_seo_framework_meta_render_data',
			);
		}

		if ( $url )
			yield [
				'tag'        => 'link',
				'attributes' => [
					'rel'  => 'shortlink',
					'href' => $url,
				],
			];
	}
}
