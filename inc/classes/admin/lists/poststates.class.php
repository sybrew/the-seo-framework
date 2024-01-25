<?php
/**
 * @package The_SEO_Framework\Classes\Admin\Lists\PostStates
 */

namespace The_SEO_Framework\Admin\Lists;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Data;

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
 * Prepares the SEO Settings page interface.
 *
 * @since 5.0.0
 * @access private
 */
final class PostStates {

	/**
	 * Adds post states for the post/page edit.php query.
	 *
	 * @hook display_post_states 10
	 * @since 4.0.0
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `_add_post_state`.
	 *
	 * @param string[] $post_states An array of post display states.
	 * @param \WP_Post $post        The Post Object.
	 * @return string[] An array of adjusted post display states.
	 */
	public static function add_post_state( $post_states, $post ) {

		if (
			   Data\Plugin::get_option( 'alter_search_query' )
			&& Data\Plugin\Post::get_meta_item( 'exclude_local_search', $post->ID )
		) {
			$post_states[] = \esc_html__( 'No Search', 'autodescription' );
		}

		if (
			   Data\Plugin::get_option( 'alter_archive_query' )
			&& Data\Plugin\Post::get_meta_item( 'exclude_from_archive', $post->ID )
		) {
			$post_states[] = \esc_html__( 'No Archive', 'autodescription' );
		}

		return $post_states;
	}
}
