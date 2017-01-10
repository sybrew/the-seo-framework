<?php
/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

defined( 'ABSPATH' ) or die;

/**
 * This file holds functions for upgrading the plugin.
 * This file will only be called ONCE if the required version option is lower
 * compared to The SEO Framework version constant.
 *
 * @since 2.7.0
 * @access private
 */

add_action( 'admin_init', 'the_seo_framework_do_upgrade', 20 );
/**
 * Upgrade The SEO Framework to the latest version.
 *
 * Does an iteration of upgrades in order of upgrade appearance.
 * Each called function will upgrade the version by its iteration.
 *
 * Only works on WordPress 4.4 and later to ensure and force maximum compatibility.
 *
 * @since 2.7.0
 *
 * @thanks StudioPress for some code.
 */
function the_seo_framework_do_upgrade() {

	if ( get_option( 'the_seo_framework_upgraded_db_version' ) >= THE_SEO_FRAMEWORK_DB_VERSION )
		return;

	if ( ! the_seo_framework()->wp_version( '4.4', '>=' ) )
		return;

	//* If the WordPress Database hasn't been upgraded yet, make the user upgrade first.
	if ( (int) get_option( 'db_version' ) !== (int) $GLOBALS['wp_db_version'] ) {
		wp_safe_redirect( admin_url( 'upgrade.php?_wp_http_referer=' . rawurlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) );
		exit;
	}

	if ( get_option( 'the_seo_framework_upgraded_db_version' ) < '2701' )
		the_seo_framework_do_upgrade_2701();

	if ( get_option( 'the_seo_framework_upgraded_db_version' ) < '2802' )
		the_seo_framework_do_upgrade_2802();

	do_action( 'the_seo_framework_upgraded' );
}

add_action( 'the_seo_framework_upgraded', 'the_seo_framework_upgrade_to_current' );
/**
 * Upgrades the Database version to the latest version if all iterations have been
 * executed. This ensure this file will no longer be required.
 * This should run once after every plugin update.
 *
 * @since 2.7.0
 */
function the_seo_framework_upgrade_to_current() {
	update_option( 'the_seo_framework_upgraded_db_version', THE_SEO_FRAMEWORK_DB_VERSION );
}

/**
 * Upgrades term metadata for version 2701.
 *
 * @since 2.7.0
 */
function the_seo_framework_do_upgrade_2701() {

	$term_meta = get_option( 'autodescription-term-meta' );

	foreach ( (array) $term_meta as $term_id => $meta ) {
		add_term_meta( $term_id, THE_SEO_FRAMEWORK_TERM_OPTIONS, $meta, true );
	}

	update_option( 'the_seo_framework_upgraded_db_version', '2701' );
}

/**
 * Removes term metadata for version 2802.
 * Reinitializes rewrite data for for sitemap stylesheet.
 *
 * @since 2.8.0
 */
function the_seo_framework_do_upgrade_2802() {

	the_seo_framework()->reinitialize_rewrite();

	//* Delete old values from database. Removes backwards compatibility.
	delete_option( 'autodescription-term-meta' );

	update_option( 'the_seo_framework_upgraded_db_version', '2802' );
}
