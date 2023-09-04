<?php
/**
 * @package The_SEO_Framework\Classes\Front\Meta\Factory\Title
 * @subpackage The_SEO_Framework\Meta\Title
 */

namespace The_SEO_Framework\Meta\Factory\Title;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\Utils\normalize_generation_args;
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
 * Holds utility for the Title factory.
 *
 * @since 4.3.0
 * @access protected
 * @internal Use tsf()->title()->utils() instead.
 */
class Utils {

	/**
	 * Removes default title filters, for consistent output and sanitization.
	 * Memoizes the filters removed, so it can add them back on reset.
	 *
	 * Performance test: 0.007ms per remove+reset on PHP 8.0, single core VPN.
	 *
	 * @since 3.1.0
	 * @since 4.1.0 Added a second parameter, $args, to help soften the burden of this method.
	 * @since 4.3.0 Now handles filters with a priority of 0. Only a theoretical bug, so not in changelog.
	 * @internal Only to be used within Factory\Title::get_bare_unfiltered_generated_title()
	 *
	 * @param bool       $reset Whether to reset the removed filters.
	 * @param array|null $args  The query arguments. Accepts 'id', 'taxonomy', and 'pta'.
	 *                          Leave null to autodetermine query.
	 */
	public static function remove_default_title_filters( $reset = false, $args = null ) {

		static $filtered = [];

		if ( $reset ) {
			foreach ( $filtered as [ $filter, $function, $priority ] )
				\add_filter( $filter, $function, $priority );

			// Reset filters.
			$filtered = [];
		} else {

			if ( null === $args ) {
				$filters = [ 'single_post_title', 'single_cat_title', 'single_tag_title' ];
			} else {
				isset( $args ) and normalize_generation_args( $args );

				if ( 'category' === $args['taxonomy'] ) {
					$filters = [ 'single_cat_title' ];
				} elseif ( 'post_tag' === $args['taxonomy'] ) {
					$filters = [ 'single_tag_title' ];
				} else {
					$filters = [ 'single_post_title' ];
				}
			}

			/**
			 * Texturization happens when outputting and saving the title; however,
			 * we want the raw title, so we won't find unexplainable issues later.
			 */
			$functions = [ 'wptexturize' ];

			if ( ! \tsf()->get_option( 'title_strip_tags' ) )
				$functions[] = 'strip_tags';

			foreach ( $filters as $filter ) {
				foreach ( $functions as $function ) {
					// Only grab 10 of these. Yes, one might transform still on the 11th.
					$it = 10;
					$i  = 0;
					// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition
					while ( false !== ( $priority = \has_filter( $filter, $function ) ) ) {
						$filtered[] = [ $filter, $function, $priority ];
						\remove_filter( $filter, $function, $priority );
						// Some noob might've destroyed \WP_Hook. Safeguard.
						if ( ++$i > $it ) break 1;
					}
				}
			}
		}
	}

	/**
	 * Resets default title filters, for consistent output and sanitation.
	 *
	 * @since 3.1.0
	 * @since 4.3.0 Moved to The_SEO_Framework\Meta\Factory\Title\Conditions
	 * @internal Only to be used within Factory\Title::get_bare_unfiltered_generated_title()
	 * @uses $this->remove_default_title_filters()
	 */
	public static function reset_default_title_filters() {
		static::remove_default_title_filters( true );
	}
}
