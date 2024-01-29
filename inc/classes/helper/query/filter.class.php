<?php
/**
 * @package The_SEO_Framework\Classes\Helper\Query\Filter
 * @subpackage The_SEO_Framework\Query
 */

namespace The_SEO_Framework\Helper\Query;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\{
	Data,
	Helper\Query, // Yes, it is legal to share class and namespaces.
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
 * Filters the query.
 *
 * @since 5.0.0
 * @access private
 */
final class Filter {

	/**
	 * Adjusts category post link and replace it with the primary term.
	 *
	 * @hook post_link_category 10
	 * @hook wc_product_post_type_link_product_cat 10
	 * @hook woocommerce_breadcrumb_main_term 10
	 * @hook woocommerce_product_categories_widget_main_term 10
	 * @since 5.0.0
	 *
	 * @param \WP_Term $term  The category to use in the permalink.
	 * @param array    $terms Array of all categories (WP_Term objects) associated with the post. Unused.
	 * @param \WP_Post $post  The post in question.
	 * @return \WP_Term The primary term.
	 */
	public static function filter_post_link_category( $term, $terms = null, $post = null ) {
		return Data\Plugin\Post::get_primary_term(
			$post->ID ?? Query::get_the_real_id(),
			$term->taxonomy,
		) ?? $term;
	}
}
