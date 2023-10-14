<?php
/**
 * @package The_SEO_Framework\Classes\Helper\Query\Filter
 * @subpackage The_SEO_Framework\Query
 */

namespace The_SEO_Framework\Helper\Query;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Data;
use \The_SEO_Framework\Helper\Query; // Yes, it is legal to share class and namespaces.

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
 * Filters the query.
 *
 * @since 4.3.0
 * @access private
 * @internal
 */
final class Filter {

	/**
	 * Adjusts category post link.
	 *
	 * @since 3.0.0
	 * @since 4.0.3 Now fills in a fallback $post object when null.
	 * @since 4.3.0 Moved to `\The_SEO_Framework\Query\Filter`
	 * @access private
	 *
	 * @param \WP_Term $term  The category to use in the permalink.
	 * @param array    $terms Array of all categories (WP_Term objects) associated with the post. Unused.
	 * @param \WP_Post $post  The post in question.
	 * @return \WP_Term The primary term.
	 */
	public static function filter_post_link_category( $term, $terms = null, $post = null ) {
		return Data\Plugin\Post::get_primary_term(
			$post->ID ?? Query::get_the_real_ID(),
			$term->taxonomy,
		) ?? $term;
	}
}
