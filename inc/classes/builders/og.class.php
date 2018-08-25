<?php
/**
 * @package The_SEO_Framework\Classes\Builders
 * @subpackage The_SEO_Framework\Builders
 */
namespace The_SEO_Framework\Builders;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2018 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

\The_SEO_Framework\_load_trait( 'generator/meta' );

/**
 * Builds Open Graph metadata.
 *
 * @since 3.1.0
 * @see the_seo_framework()->OG()
 * @access private
 *         Use `the_seo_framework()->OG()` instead.
 * @final Can't be extended.
 */
final class OG {
	use \The_SEO_Framework\Meta_Generator;

	public function __construct() {
		$this->init();
	}

	public function build() {

		if ( ! $this->tsf->get_option( 'og_tags' ) ) {
			$this->set_failure( 'DISABLED_VIA_OPTIONS' );
			return false;
		}

		$this->can_fail = true;

		if ( ! $this->parse_simple( [
			'title'       => 'get_title',
			'description' => 'get_description',
			'url'         => 'get_url',
		] ) )
			return false;

		if ( ! $this->parse_complex( [
			'image' => 'get_image',
		] ) )
			return false;

		$this->can_fail = false;

		$this->parse_simple( [
			'locale'   => 'get_locale',
			'type'     => 'get_type',
			'sitename' => 'get_sitename',
		] );

		return true;
	}

	public function get_title() {
		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @param string $title The generated Open Graph title.
		 * @param int    $id    The page or term ID.
		 */
		$title = (string) \apply_filters_ref_array(
			'the_seo_framework_ogtitle_output',
			[
				$this->tsf->get_open_graph_title(),
				$this->id,
			]
		);

		if ( $title ) {
			return [
				'@tag'     => 'meta',
				'property' => 'og:title',
				'content'  => $title,
			];
		} else {
			$this->set_failure( 'NO_TITLE' );
			return [];
		}
	}

	public function get_description() {
		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @param string $description The generated Open Graph description.
		 * @param int    $id          The page or term ID.
		 */
		$description = (string) \apply_filters_ref_array(
			'the_seo_framework_ogdescription_output',
			[
				$this->tsf->get_open_graph_description(),
				$this->id,
			]
		);

		if ( $description ) {
			return [
				'@tag'     => 'meta',
				'property' => 'og:description',
				'content'  => $description,
			];
		} else {
			$this->set_failure( 'NO_DESCRIPTION' );
			return [];
		}
	}

	public function get_url() {
		/**
		 * @since 2.9.3
		 * @param string $url The canonical/Open Graph URL. Must be escaped.
		 * @param int    $id  The current page or term ID.
		 */
		$url = (string) \apply_filters_ref_array(
			'the_seo_framework_ogurl_output',
			[
				$this->tsf->get_current_canonical_url(),
				$this->id,
			]
		);

		if ( $url ) {
			return [
				'@tag'     => 'meta',
				'property' => 'og:url',
				'content'  => $url,
			];
		} else {
			$this->set_failure( 'NO_URL' );
			return [];
		}
	}

	public function get_image() {
		/**
		 * @NOTE: Use of this might cause incorrect meta since other functions
		 * depend on the image from cache.
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @since 3.1.0 Now accepts an array of images
		 * @param string|array $image The social image URL.
		 * @param int          $id    The page or term ID.
		 */
		$images = (array) \apply_filters_ref_array(
			'the_seo_framework_ogimage_output',
			[
				$this->tsf->get_image_from_cache(),
				$this->id,
			]
		);

		if ( $images ) {
			$ret = [];
			$i = 0;
			foreach ( $images as $image ) {
				$ret[ $i ]['image'] = [
					'@tag'     => 'meta',
					'property' => 'og:image',
					'content'  => $image,
				];

				if ( ! empty( $this->tsf->image_dimensions[ $this->id ]['width'] )
				&& ! empty( $this->tsf->image_dimensions[ $this->id ]['height'] ) ) {
					$ret[ $i ]['width'] = [
						'@tag'     => 'meta',
						'property' => 'og:image:width',
						'content'  => $this->tsf->image_dimensions[ $this->id ]['width'],
					];
					$ret[ $i ]['height'] = [
						'@tag'     => 'meta',
						'property' => 'og:image:height',
						'content'  => $this->tsf->image_dimensions[ $this->id ]['height'],
					];
				}
				$i++;
			}
			return $ret;
		} else {
			$this->set_failure( 'NO_IMAGE' );
			return [];
		}
	}

	public function get_locale() {
		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @param string $locale The generated locale field.
		 * @param int    $id     The page or term ID.
		 */
		$locale = (string) \apply_filters_ref_array(
			'the_seo_framework_oglocale_output',
			[
				$this->tsf->fetch_locale(),
				$this->id,
			]
		);

		if ( $locale ) {
			return [
				'@tag'     => 'meta',
				'property' => 'og:locale',
				'content'  => $locale,
			];
		}

		return [];
	}

	public function get_type() {

		$type = $this->tsf->get_og_type();

		if ( $type ) {
			return [
				'@tag'     => 'meta',
				'property' => 'og:type',
				'content'  => $type,
			];
		}

		return [];
	}

	public function get_sitename() {
		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @param string $sitename The generated Open Graph site name.
		 * @param int    $id       The page or term ID.
		 */
		$sitename = (string) \apply_filters_ref_array(
			'the_seo_framework_ogsitename_output',
			[
				$this->tsf->get_blogname(),
				$this->id,
			]
		);

		if ( $sitename ) {
			return [
				'@tag'     => 'meta',
				'property' => 'og:site_name',
				'content'  => $sitename,
			];
		}

		return [];
	}
}
