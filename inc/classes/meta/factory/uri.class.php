<?php
/**
 * @package The_SEO_Framework\Classes\Front\Meta\Factory
 * @subpackage The_SEO_Framework\Meta\URI
 */

namespace The_SEO_Framework\Meta\Factory;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Helper\Query;

use function \The_SEO_Framework\{
	memo,
	Utils\normalize_generation_args,
};

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
class URI {

	/**
	 * Returns the redirect URL, if any.
	 *
	 * @since 4.1.4
	 * @since 4.2.0 1. Now supports the `$args['pta']` index.
	 *              2. Now redirects post type archives.
	 * @since 4.3.0 Now expects an ID before getting a post meta item.
	 *
	 * @param null|array $args The redirect URL arguments, leave null to autodetermine query : {
	 *    int    $id       The Post, Page or Term ID to generate the URL for.
	 *    string $taxonomy The taxonomy.
	 * }
	 * @return string The canonical URL if found, empty string otherwise.
	 */
	public static function get_redirect_url( $args = null ) {

		if ( null === $args ) {
			if ( Query::is_singular() ) {
				$url = \tsf()->get_post_meta_item( 'redirect' );
			} elseif ( Query::is_editable_term() ) {
				$url = \tsf()->get_term_meta_item( 'redirect' );
			} elseif ( \is_post_type_archive() ) {
				$url = \tsf()->get_post_type_archive_meta_item( 'redirect' );
			}
		} else {
			normalize_generation_args( $args );
			if ( $args['taxonomy'] ) {
				$url = \tsf()->get_term_meta_item( 'redirect', $args['id'] );
			} elseif ( $args['pta'] ) {
				$url = \tsf()->get_post_type_archive_meta_item( 'redirect', $args['pta'] );
			} elseif ( $args['id'] ) {
				$url = \tsf()->get_post_meta_item( 'redirect', $args['id'] );
			}
		}

		return $url ?? '' ?: '';
	}
}
