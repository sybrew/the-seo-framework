<?php
/**
 * @package The_SEO_Framework\Compat\Theme\Avada
 * @subpackage The_SEO_Framework\Compatibility
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

\add_filter( 'fusion_pagetype_data', __NAMESPACE__ . '\\_remove_avada_pagetype_seo', 10, 2 );
\add_filter( 'awb_metaboxes_sections', __NAMESPACE__ . '\\_remove_avada_seo_metaboxes', 10, 1 );

/**
 * Disables conflicting Avada SEO settings when TSF is active.
 *
 * @since 5.1.3
 */
\add_filter( 'avada_setting_get_status_opengraph', '__return_false' );
\add_filter( 'avada_setting_get_meta_tags_separator', '__return_false' );
\add_filter( 'avada_setting_get_seo_title', '__return_false' );
\add_filter( 'avada_setting_get_meta_description', '__return_false' );
\add_filter( 'avada_setting_get_meta_og_image', '__return_false' );

/**
 * Removes SEO settings from Avada page type data.
 *
 * @hook fusion_pagetype_data 10
 * @since 5.1.3
 * @access private
 *
 * @param array  $pagetype_data The pagetype data.
 * @param string $posttype      The post type.
 * @return array Modified pagetype data with SEO settings removed.
 */
function _remove_avada_pagetype_seo( $pagetype_data, $posttype ) {
	
	unset(  $pagetype_data[ $posttype ]['seo'], $pagetype_data['default']['seo'] );

	return $pagetype_data;
}

/**
 * Removes Avada SEO metaboxes when TSF is active.
 *
 * @hook awb_metaboxes_sections 10
 * @since 5.1.3
 * @access private
 *
 * @param array $sections The metabox sections.
 * @return array Modified sections with SEO options removed.
 */
function _remove_avada_seo_metaboxes( $sections ) {

	unset( $sections['seo'], $sections['advanced']['status_opengraph'] );

	return $sections;
}
