<?php
/**
 * @package The_SEO_Framework\Classes\Front\Meta\Factory
 * @subpackage The_SEO_Framework\Meta\Facebook
 */

namespace The_SEO_Framework\Meta\Factory;

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
 * Holds getters for meta tag output.
 *
 * @since 4.3.0
 * @access protected
 * @internal
 */
class Facebook {

	/**
	 * Returns the Facebook author's value if API type is 'article'.
	 *
	 * @since 4.3.0
	 *
	 * @return string
	 */
	public static function get_author() {

		if ( 'article' !== Open_Graph::get_type() ) return;

		$tsf = \tsf();

		return $tsf->get_current_post_author_meta_item( 'facebook_page' )
			?: $tsf->get_option( 'facebook_author' );
	}

	/**
	 * Returns the Facebook publisher value if API type is 'article'.
	 *
	 * @since 4.3.0
	 *
	 * @return string
	 */
	public static function get_publisher() {

		if ( 'article' !== Open_Graph::get_type() ) return;

		return \tsf()->get_option( 'facebook_publisher' );
	}

	/**
	 * Returns the Facebook publisher value if API type is 'article'.
	 *
	 * @since 4.3.0
	 *
	 * @return string
	 */
	public static function get_app_id() {
		return \tsf()->get_option( 'facebook_appid' );
	}
}
