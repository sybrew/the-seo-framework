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
\The_SEO_Framework\_deactivation_unset_options_autoload();
\The_SEO_Framework\_deactivation_unset_sitemap();

/**
 * Flush rewrite rules on plugin deactivation.
 *
 * @since 2.6.6
 * @since 2.7.1: 1. Now no longer reinitializes global $wp_rewrite.
 *               2. Now flushes the rules on shutdown.
 * @since 2.8.0: Added namespace and renamed function.
 * @access private
 * @global object $wp_rewrite
 */
function _deactivation_unset_sitemap() {

	unset( $GLOBALS['wp_rewrite']->extra_rules_top['sitemap\.xml$'] );
	unset( $GLOBALS['wp_rewrite']->extra_rules_top['sitemap\.xsl$'] );

	\add_action( 'shutdown', 'flush_rewrite_rules' );
}

/**
 * Turns off autoloading for The SEO Framework main options.
 *
 * @since 2.9.2
 * @access private
 */
function _deactivation_unset_options_autoload() {

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
			\update_option( $setting, $options, 'no' );
	}
}
