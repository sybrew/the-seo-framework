<?php
/**
 * @package The_SEO_Framework\Classes\Front\Oembed
 * @subpackage The_SEO_Framework\Oembed
 */

namespace The_SEO_Framework\Front;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\{
	Data,
	Meta,
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
 * Prepares oEmbed adjustments.
 *
 * They called it OEmbed first:
 * https://blog.leahculver.com/2008/05/announcing-oembed-an-open-standard-for-embedded-content.html
 *
 * @since 5.0.0
 * @access private
 */
final class OEmbed {

	/**
	 * Alters the oEmbed response data.
	 *
	 * @hook oembed_response_data 10
	 * @since 4.0.5
	 * @since 4.1.1 Now also alters titles and images.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `_alter_oembed_response_data`.
	 *
	 * @param array    $data   The response data.
	 * @param \WP_Post $post   The post object.
	 * @return array Possibly altered $data.
	 */
	public static function alter_response_data( $data, $post ) {

		if ( Data\Plugin::get_option( 'oembed_use_og_title' ) ) {
			$data['title'] = (
				Data\Plugin::get_option( 'og_tags' )
					? Meta\Open_Graph::get_title( [ 'id' => $post->ID ] )
					: Meta\Title::get_title( [ 'id' => $post->ID ] )
			) ?: $data['title'];
		}

		if ( Data\Plugin::get_option( 'oembed_use_social_image' ) ) {
			$image_details = current( Meta\Image::get_image_details(
				[ 'id' => $post->ID ],
				true,
				'oembed'
			) );

			if ( $image_details && $image_details['url'] && $image_details['width'] && $image_details['height'] ) {
				// Override WordPress provided data.
				$data['thumbnail_url']    = $image_details['url'];
				$data['thumbnail_width']  = $image_details['width'];
				$data['thumbnail_height'] = $image_details['height'];
			}
		}

		if ( Data\Plugin::get_option( 'oembed_remove_author' ) )
			unset( $data['author_url'], $data['author_name'] );

		return $data;
	}
}
