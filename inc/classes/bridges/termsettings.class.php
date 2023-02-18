<?php
/**
 * @package The_SEO_Framework\Classes\Bridges\TermSettings
 * @subpackage The_SEO_Framework\Admin\Edit\Term
 */

namespace The_SEO_Framework\Bridges;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * Prepares the Term Settings view interface.
 *
 * @since 4.0.0
 * @access protected
 * @internal
 * @final Can't be extended.
 */
final class TermSettings {

	/**
	 * Prepares the setting fields.
	 *
	 * @since 4.0.0
	 *
	 * @param \WP_Term $term     Current taxonomy term object.
	 * @param string   $taxonomy Current taxonomy slug.
	 */
	public static function _prepare_setting_fields( $term, $taxonomy ) {
		static::_output_setting_fields( $term, $taxonomy );
	}

	/**
	 * Outputs the term settings fields.
	 *
	 * @since 4.0.0
	 *
	 * @param \WP_Term $term     Current taxonomy term object.
	 * @param string   $taxonomy Current taxonomy slug.
	 */
	public static function _output_setting_fields( $term, $taxonomy ) { // phpcs:ignore,VariableAnalysis
		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pre_tt_inpost_box' );
		\tsf()->get_view( 'edit/seo-settings-tt', get_defined_vars() );
		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pro_tt_inpost_box' );
	}
}
