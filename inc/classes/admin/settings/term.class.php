<?php
/**
 * @package The_SEO_Framework\Classes\Admin\Settings\Post
 * @subpackage The_SEO_Framework\Admin\Edit\Term
 */

namespace The_SEO_Framework\Admin\Settings;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Helper\{
	Query,
	Taxonomy,
	Template,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Prepares the Term Settings view interface.
 *
 * @since 4.0.0
 * @since 5.0.0 1. Renamed from `TermSettings` to `Term`.
 *              2. Moved to `\The_SEO_Framework\Admin\Settings`.
 * @access private
 */
final class Term {

	/**
	 * Prepares the setting fields.
	 *
	 * @since 5.0.0
	 * @hook current_screen 10
	 */
	public static function prepare_setting_fields() {

		if ( ! Query::is_term_edit() ) return;

		$taxonomy = Query::get_current_taxonomy();

		if ( ! Taxonomy::is_supported( $taxonomy ) ) return;

		\add_action(
			"{$taxonomy}_edit_form",
			[ static::class, 'output_setting_fields' ],
			/**
			 * @since 2.6.0
			 * @param int $priority The meta box term priority.
			 *                      Defaults to a high priority, this box is seen soon below the default edit inputs.
			 */
			(int) \apply_filters( 'the_seo_framework_term_metabox_priority', 0 ),
			2,
		);
	}

	/**
	 * Outputs the term settings fields.
	 *
	 * @since 4.0.0
	 *
	 * @param \WP_Term $term     Current taxonomy term object.
	 * @param string   $taxonomy Current taxonomy slug.
	 */
	public static function output_setting_fields( $term, $taxonomy ) {
		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pre_tt_inpost_box' );
		Template::output_view( 'term/settings', $term, $taxonomy );
		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pro_tt_inpost_box' );
	}
}
