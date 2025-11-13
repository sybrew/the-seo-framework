<?php
/**
 * @package The_SEO_Framework\Compat\Theme\Avada
 * @subpackage The_SEO_Framework\Compatibility
 * @access private
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2025 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

\add_filter( 'avada_setting_get_status_opengraph', __NAMESPACE__ . '\_avada_disable_switch_option' );
\add_filter( 'avada_setting_get_disable_rich_snippet_title', __NAMESPACE__ . '\_avada_disable_switch_option' );
\add_filter( 'avada_setting_get_disable_rich_snippet_author', __NAMESPACE__ . '\_avada_disable_switch_option' );
\add_filter( 'avada_setting_get_disable_rich_snippet_date', __NAMESPACE__ . '\_avada_disable_switch_option' );
\add_filter( 'avada_options_sections', __NAMESPACE__ . '\_avada_remove_settings_sections', 10, 1 );
\add_filter( 'fusion_pagetype_data', __NAMESPACE__ . '\_avada_unset_meta_box_seo_tab', 10, 2 );

/**
 * Disables conflicting Avada SEO settings.
 *
 * In Avada, disable '1' means enable, disable '0' means disable. Fun!
 *
 * @hook avada_setting_get_status_opengraph 10
 * @hook avada_setting_get_disable_rich_snippet_title 10
 * @hook avada_setting_get_disable_rich_snippet_author 10
 * @hook avada_setting_get_disable_rich_snippet_date 10
 * @since 5.1.3
 *
 * @return string '0' to disable the option.
 */
function _avada_disable_switch_option() {
	return '0';
}

/**
 * Removes Avada's SEO-related settings from the Advanced section.
 *
 * @hook avada_options_sections 10
 * @since 5.1.3
 *
 * @param array $sections The theme option sections.
 * @return array Modified sections with SEO settings removed.
 */
function _avada_remove_settings_sections( $sections ) {

	// Leaving out the isset won't cause an issue if the section is gone...
	if ( isset( $sections['advanced']['fields']['theme_features_section']['fields'] ) ) {
		// ...but we'd write "null" to this reference otherwise.
		$advanced_features = &$sections['advanced']['fields']['theme_features_section']['fields'];

		unset(
			$advanced_features['status_opengraph'],
			$advanced_features['meta_tags_separator'],
			$advanced_features['disable_rich_snippet_title'],
			$advanced_features['disable_rich_snippet_author'],
			$advanced_features['disable_rich_snippet_date'],
		);
	}

	return $sections;
}

/**
 * Removes the SEO settings from Avada's post edit meta box registration array.
 *
 * @hook fusion_pagetype_data 10
 * @since 5.1.3
 *
 * @param array  $pagetype_data The pagetype data.
 * @param string $posttype      The current post type.
 * @return array Modified pagetype data with SEO settings removed.
 */
function _avada_unset_meta_box_seo_tab( $pagetype_data, $posttype ) {

	if ( isset( $pagetype_data[ $posttype ?? 'default' ] ) )
		$pagetype_data[ $posttype ] = array_diff(
			$pagetype_data[ $posttype ?? 'default' ],
			[ 'seo' ],
		);

	return $pagetype_data;
}
