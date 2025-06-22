<?php
/**
 * @package The_SEO_Framework\Compat\Theme\Avada
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// Disable Avada SEO functionality using various common filter patterns
\add_filter( 'avada_seo_enabled', '__return_false' );
\add_filter( 'fusion_seo_enabled', '__return_false' );
\add_filter( 'avada_disable_seo', '__return_true' );
\add_filter( 'fusion_disable_seo', '__return_true' );

// Hook into Avada's SEO detection system if available
\add_filter( 'avada_detect_seo_plugins', __NAMESPACE__ . '\\_disable_avada_seo' );
\add_filter( 'fusion_detect_seo_plugins', __NAMESPACE__ . '\\_disable_avada_seo' );

// Additional compatibility filters for comprehensive coverage
\add_filter( 'fusion_app_preview_data', __NAMESPACE__ . '\\_announce_tsf_presence', 10, 1 );

// Filter Avada's specific SEO settings to disable them
\add_filter( 'avada_setting_get_status_opengraph', '__return_false' );
\add_filter( 'avada_setting_get_meta_tags_separator', '__return_false' );
\add_filter( 'avada_setting_get_seo_title', '__return_false' );
\add_filter( 'avada_setting_get_meta_description', '__return_false' );
\add_filter( 'avada_setting_get_meta_og_image', '__return_false' );

// Filter Avada's metabox sections to remove SEO options
\add_filter( 'awb_metaboxes_sections', __NAMESPACE__ . '\\_remove_avada_seo_metaboxes', 10, 1 );

/**
 * Disables Avada SEO functionality by announcing TSF presence.
 *
 * @hook avada_detect_seo_plugins 10
 * @hook fusion_detect_seo_plugins 10
 * @since 5.1.3
 * @access private
 *
 * @return bool Whether SEO plugin is detected.
 */
function _disable_avada_seo() {
	return true;
}

/**
 * Announces TSF presence to Avada theme systems.
 *
 * @hook fusion_app_preview_data 10
 * @since 5.1.3
 * @access private
 *
 * @param array $data The preview data.
 * @return array Modified preview data with TSF presence announced.
 */
function _announce_tsf_presence( $data ) {
	if ( \is_array( $data ) ) {
		$data['seo_plugin_active'] = true;
		$data['the_seo_framework']  = true;
	}

	return $data;
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
	if ( \is_array( $sections ) ) {
		// Remove advanced SEO options
		if ( isset( $sections['advanced']['status_opengraph'] ) ) {
			unset( $sections['advanced']['status_opengraph'] );
		}
		
		// Remove default SEO section entirely
		if ( isset( $sections['seo'] ) ) {
			unset( $sections['seo'] );
		}
	}

	return $sections;
}