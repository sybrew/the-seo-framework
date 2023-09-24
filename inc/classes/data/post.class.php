<?php
/**
 * @package The_SEO_Framework\Classes\Data\Post
 * @subpackage The_SEO_Framework\Data
 */

namespace The_SEO_Framework\Data;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\memo;

use \The_SEO_Framework\Helper\Query;

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
 * Holds a collection of data helper methods for a post.
 *
 * @since 4.3.0
 * @access protected
 * @internal
 */
class Post {

	/**
	 * @since 4.3.0
	 *
	 * @param ?int $id The post ID. Leave null to autodetermine.
	 * @return string The post published time according to settings.
	 */
	public static function get_post_published_time( $id = null ) {

		$post_date_gmt = \get_post( $id ?? Query::get_the_real_id() )
			->post_date_gmt ?? '0000-00-00 00:00:00';

		if ( '0000-00-00 00:00:00' === $post_date_gmt )
			return '';

		return \tsf()->gmt2date( \tsf()->get_timestamp_format(), $post_date_gmt );
	}

	/**
	 * @since 4.3.0
	 *
	 * @param ?int $id The post ID. Leave null to autodetermine.
	 * @return string The post modified time according to settings.
	 */
	public static function get_post_modified_time( $id = null ) {

		$post_modified_gmt = \get_post( $id ?? Query::get_the_real_id() )
			->post_modified_gmt ?? '0000-00-00 00:00:00';

		if ( '0000-00-00 00:00:00' === $post_modified_gmt )
			return '';

		return \tsf()->gmt2date( \tsf()->get_timestamp_format(), $post_modified_gmt );
	}
}
