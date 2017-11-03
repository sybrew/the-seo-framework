<?php
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

defined( 'ABSPATH' ) or die;

/**
 * This file holds functions for upgrading the plugin.
 * This file will only be called ONCE if the required version option is lower
 * compared to The SEO Framework version constant.
 *
 * @since 2.7.0
 * @access private
 */

the_seo_framework_previous_db_version(); // sets cache.
/**
 * Returns the version set before upgrading began.
 *
 * @since 3.0.0
 * @staticvar string $cache
 *
 * @return string The prior-to-upgrade TSF db version.
 */
function the_seo_framework_previous_db_version() {
	static $cache;
	return isset( $cache ) ? $cache : $cache = get_option( 'the_seo_framework_upgraded_db_version', '0' );
}

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
 * @since 2.9.4 No longer tests WP version. This file won't be loaded anyway if rendered incompatible.
 * @since 3.0.0 Fewer option calls are now made when version is higher than former checks.
 *
 * @thanks StudioPress for some code.
 */
function the_seo_framework_do_upgrade() {

	$version = the_seo_framework_previous_db_version();

	if ( $version >= THE_SEO_FRAMEWORK_DB_VERSION )
		return;

	//* If the WordPress Database hasn't been upgraded yet, make the user upgrade first.
	if ( (int) get_option( 'db_version' ) !== (int) $GLOBALS['wp_db_version'] ) {
		wp_safe_redirect( admin_url( 'upgrade.php?_wp_http_referer=' . rawurlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) );
		exit;
	}

	if ( $version < '2701' ) {
		the_seo_framework_do_upgrade_2701();
		$version = '2701';
	}
	if ( $version < '2802' ) {
		the_seo_framework_do_upgrade_2802();
		$version = '2802';
	}
	if ( $version < '2900' ) {
		the_seo_framework_do_upgrade_2900();
		$version = '2900';
	}
	if ( $version < '3001' ) {
		the_seo_framework_do_upgrade_3001();
		$version = '3001';
	}

	do_action( 'the_seo_framework_upgraded' );
}

add_action( 'the_seo_framework_upgraded', 'the_seo_framework_upgrade_to_current' );
/**
 * Upgrades the Database version to the latest version.
 *
 * This happens if all iterations have been executed. This ensure this file will
 * no longer be required.
 * This should run once after every plugin update.
 *
 * @since 2.7.0
 */
function the_seo_framework_upgrade_to_current() {
	update_option( 'the_seo_framework_upgraded_db_version', THE_SEO_FRAMEWORK_DB_VERSION );
}

/**
 * Lists and returns upgrade notices to be outputted in admin.
 *
 * @since 2.9.0
 * @staticvar array $cache The cached notice strings.
 *
 * @param string $notice The upgrade notice.
 * @param bool $get Whether to return the upgrade notices.
 * @return array|void The notices when $get is true.
 */
function the_seo_framework_add_upgrade_notice( $notice = '', $get = false ) {

	static $cache = array();

	if ( $get )
		return $cache;

	$cache[] = $notice;
}

add_action( 'admin_notices', 'the_seo_framework_output_upgrade_notices' );
/**
 * Outputs available upgrade notices.
 *
 * @since 2.9.0
 * @since 3.0.0 Added prefix.
 * @uses the_seo_framework_add_upgrade_notice()
 */
function the_seo_framework_output_upgrade_notices() {

	$notices = the_seo_framework_add_upgrade_notice( '', true );

	foreach ( $notices as $notice ) {
		//* @TODO rtl?
		the_seo_framework()->do_dismissible_notice( 'SEO: ' . $notice, 'updated' );
	}
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

/**
 * Updates Twitter 'photo' card option to 'summary_large_image'.
 * Invalidates object cache if changed.
 *
 * @since 2.9.0
 */
function the_seo_framework_do_upgrade_2900() {

	$tsf = the_seo_framework();

	$card_type = trim( esc_attr( $tsf->get_option( 'twitter_card', false ) ) );

	if ( 'photo' === $card_type ) {
		$tsf->update_option( 'twitter_card', 'summary_large_image' );
		$tsf->delete_object_cache();
		the_seo_framework_add_upgrade_notice(
			esc_html__( 'Twitter Photo Cards have been deprecated. Your site now uses Summary Cards when applicable.', 'autodescription' )
		);
	}

	update_option( 'the_seo_framework_upgraded_db_version', '2900' );
}

/**
 * Converts sitemap timestamp settings to global timestamp settings.
 * Adds new character counter settings.
 * Invalidates object cache.
 *
 * @since 3.0.0
 */
function the_seo_framework_do_upgrade_3001() {

	$tsf = the_seo_framework();
	$previous_version = the_seo_framework_previous_db_version();

	$timestamp_format = $tsf->get_option( 'sitemap_timestamps', false );
	//= Only change if option exists. Falls back to default upgrader instead.
	if ( '' !== $timestamp_format ) {
		$tsf->update_option( 'timestamps_format', (string) (int) $timestamp_format );
		//= Only set notice if an actual upgrade took place. (redundancy check)
		if ( $previous_version > '0' ) {
			the_seo_framework_add_upgrade_notice(
				esc_html__( 'The previous sitemap timestamp settings have been converted into new global timestamp settings.', 'autodescription' )
			);
		}
	}

	if ( $previous_version > '0' ) {
		$tsf->update_option( 'display_character_counter', 1 );
		$tsf->update_option( 'display_pixel_counter', 1 );
	} else {
		$tsf->update_option( 'display_character_counter', 0 );
		$tsf->update_option( 'display_pixel_counter', 1 );
	}

	$tsf->delete_object_cache();

	update_option( 'the_seo_framework_upgraded_db_version', '3001' );
}
