<?php
/**
* @package The_SEO_Framework
*/
namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_DIR_PATH' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

//! @php7+ convert to IIFE
\The_SEO_Framework\_activation_setup_sitemap();
\The_SEO_Framework\_activation_set_options_autoload();

/**
 * Add and Flush rewrite rules on plugin activation.
 *
 * @since 2.6.6
 * @since 2.7.1: 1. Now no longer reinitializes global $wp_rewrite.
 *               2. Now always listens to the preconditions of the sitemap addition.
 *               3. Now flushes the rules on shutdown.
 * @since 2.8.0: Added namespace and renamed function.
 * @access private
 */
function _activation_setup_sitemap() {

	$the_seo_framework = \the_seo_framework();

	if ( isset( $the_seo_framework ) ) {
		$the_seo_framework->rewrite_rule_sitemap();
		\add_action( 'shutdown', 'flush_rewrite_rules' );
	}
}

/**
 * Turns on autoloading for The SEO Framework main options.
 *
 * @since 2.9.2
 * @access private
 */
function _activation_set_options_autoload() {

	$the_seo_framework = \the_seo_framework();

	if ( isset( $the_seo_framework ) ) {
		$options = $the_seo_framework->get_all_options();
		$setting = THE_SEO_FRAMEWORK_SITE_OPTIONS;

		\remove_all_filters( "pre_update_option_{$setting}" );
		\remove_all_actions( "update_option_{$setting}" );
		\remove_all_filters( "sanitize_option_{$setting}" );

		// Set to false, so we can reset the options.
		$_success = \update_option( $setting, false );
		if ( $_success )
			\update_option( $setting, $options, 'yes' );
	}
}
