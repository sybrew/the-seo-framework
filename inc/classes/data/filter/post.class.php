<?php
/**
 * @package The_SEO_Framework\Classes\Data\Filter\Term
 * @subpackage The_SEO_Framework\Data\Term
 */

namespace The_SEO_Framework\Data\Filter;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

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
 * Holds a collection of post meta sanitization methods.
 *
 * @since 5.0.0
 * @access private
 */
class Post {

	/**
	 * @since 5.0.0
	 *
	 * @param mixed $meta_value Metadata value to sanitize.
	 * @return array[] The sanitized post meta.
	 */
	public static function filter_meta_update( $meta_value ) {

		if ( empty( $meta_value ) )
			return [];

		foreach ( $meta_value as $key => &$value ) {
			switch ( $key ) {
				case '_genesis_title':
				case '_open_graph_title':
				case '_twitter_title':
				case '_genesis_description':
				case '_open_graph_description':
				case '_twitter_description':
					$value = Sanitize::metadata_content( $value );
					continue 2;

				case '_genesis_canonical_uri':
				case '_social_image_url':
					$value = \sanitize_url( $value, [ 'https', 'http' ] );
					continue 2;

				case '_social_image_id':
					// Bound to _social_image_url.
					$value = empty( $meta_value['_social_image_url'] ) ? 0 : \absint( $value );
					continue 2;

				case '_genesis_noindex':
				case '_genesis_nofollow':
				case '_genesis_noarchive':
					$value = Sanitize::qubit( $value );
					continue 2;

				case 'redirect':
					// Allow all protocols also allowed by WP:
					$value = \sanitize_url( $value );
					continue 2;

				case '_tsf_title_no_blogname':
				case 'exclude_local_search':
				case 'exclude_from_archive':
					$value = Sanitize::boolean_integer( $value );
					continue 2;

				default:
					unset( $meta_value[ $key ] );
			}
		}

		return $meta_value;
	}
}
