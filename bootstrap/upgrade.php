<?php
/**
 * @package The_SEO_Framework
 * @subpackage Bootstrapp
 */
namespace The_SEO_Framework\Bootstrap;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * This file holds functions for upgrading the plugin.
 * This file will only be called ONCE if the required version option is lower
 * compared to The SEO Framework version constant.
 *
 * @since 2.7.0
 * @access private
 * @TODO convert to class, see \TSF_Extension_Manager\Upgrader
 *       It's a generator/iterator, so we must wait to PHP>5.5 support.
 *
 * @since 3.2.4 Applied namspacing to this file. All method names have changed.
 */

/**
 * Returns the default site options.
 *
 * @since 3.1.0
 * @staticvar array $cache
 *
 * @return array The default site options.
 */
function _upgrade_default_site_options() {
	static $cache;
	return isset( $cache ) ? $cache : $cache = \the_seo_framework()->get_default_site_options();
}

_previous_db_version(); // sets cache.
/**
 * Returns the version set before upgrading began.
 *
 * @since 3.0.0
 * @staticvar string $cache
 *
 * @return string The prior-to-upgrade TSF db version.
 */
function _previous_db_version() {
	static $cache;
	return isset( $cache ) ? $cache : $cache = \get_option( 'the_seo_framework_upgraded_db_version', '0' );
}

\add_action( 'init', __NAMESPACE__ . '\\_do_upgrade', 20 );
/**
 * Upgrade The SEO Framework to the latest version.
 *
 * Does an iteration of upgrades in order of upgrade appearance.
 * Each called function will upgrade the version by its iteration.
 *
 * @thanks StudioPress for some code.
 * @since 2.7.0
 * @since 2.9.4 No longer tests WP version. This file won't be loaded anyway if rendered incompatible.
 * @since 3.0.0 Fewer option calls are now made when version is higher than former checks.
 * @since 3.1.0 1. Now always updates the database version to the current version, even if it's ahead.
 *              2. No longer checks for WordPress upgrade, this is handled by WordPress in ..\wp-admin\admin.php, before admin_init.
 *              3. Now sets the initial database version to the previous version (if known), or the current version.
 *              4. Now redirects from the SEO settings page, as the options can conflict during upgrade.
 *              5. Now always flushes rewrite rules after an upgrade.
 *              6. Now registers the settings on the first run.
 *              7. Now checks if The SEO Framework is loaded.
 *              8. Now tries to increase memory limit. This probably isn't needed.
 *              9. Now runs on the front-end, too, via `init`, instead of `admin_init`.
 * @since 3.1.4 Now flushes object cache before the upgrade settings are called.
 */
function _do_upgrade() {

	$tsf = \the_seo_framework();

	if ( ! $tsf->loaded ) return;

	if ( $tsf->is_seo_settings_page( false ) ) {
		\wp_redirect( \self_admin_url() ); // phpcs:ignore -- self_admin_url() is safe.
		exit;
	}

	\wp_raise_memory_limit( 'tsf_upgrade' );

	/**
	 * From WordPress' .../update-core.php
	 * @since 3.1.4
	 */
	// Clear the cache to prevent an update_option() from saving a stale database version to the cache
	\wp_cache_flush();
	// (Not all cache back ends listen to 'flush')
	\wp_cache_delete( 'alloptions', 'options' );

	$version = _previous_db_version();

	if ( ! \get_option( 'the_seo_framework_initial_db_version' ) ) {
		//* Sets to previous if previous is known. This is a late addition.
		\update_option( 'the_seo_framework_initial_db_version', $version ?: THE_SEO_FRAMEWORK_DB_VERSION, 'no' );
	}

	if ( $version >= THE_SEO_FRAMEWORK_DB_VERSION ) {
		_upgrade_to_current();
		return;
	}

	if ( ! $version ) {
		_do_upgrade_1();
		$version = '1';
	}
	if ( $version < '2701' ) {
		_do_upgrade_2701();
		$version = '2701';
	}
	if ( $version < '2802' ) {
		_do_upgrade_2802();
		$version = '2802';
	}
	if ( $version < '2900' ) {
		_do_upgrade_2900();
		$version = '2900';
	}
	if ( $version < '3001' ) {
		_do_upgrade_3001();
		$version = '3001';
	}
	if ( $version < '3060' ) {
		_do_upgrade_3060();
		$version = '3060';
	}

	//! From here, the upgrade procedures should be backward compatible.
	//? This means no data may be erased for at least 1 major version, or 1 year, whichever is later.
	if ( $version < '3103' ) {
		_do_upgrade_3103();
		$version = '3103';
	}

	/**
	 * @since 2.7.0
	 */
	\do_action( 'the_seo_framework_upgraded' );
}

\add_action( 'the_seo_framework_upgraded', __NAMESPACE__ . '\\_upgrade_to_current' );
/**
 * Upgrades the Database version to the latest version.
 *
 * This happens if all iterations have been executed. This ensure this file will
 * no longer be required.
 * This should run once after every plugin update.
 *
 * @since 2.7.0
 * @since 3.1.4 Now flushes the object cache after the setting's updated.
 */
function _upgrade_to_current() {

	\update_option( 'the_seo_framework_upgraded_db_version', THE_SEO_FRAMEWORK_DB_VERSION );

	/**
	 * From WordPress' .../update-core.php
	 * @since 3.1.4
	 */
	// Clear the cache to prevent a get_option() from retrieving a stale database version to the cache
	\wp_cache_flush();
	// (Not all cache back ends listen to 'flush')
	\wp_cache_delete( 'alloptions', 'options' );
}

\add_action( 'the_seo_framework_upgraded', __NAMESPACE__ . '\\_upgrade_reinitialize_rewrite', 99 );
/**
 * Reinitializes the rewrite cache.
 *
 * This happens after the plugin's upgraded, because it's not critical, and when
 * this fails, the upgrader won't be locked.
 *
 * @since 3.1.2
 */
function _upgrade_reinitialize_rewrite() {
	\the_seo_framework()->reinitialize_rewrite();
}

\add_action( 'the_seo_framework_upgraded', __NAMESPACE__ . '\\_prepare_upgrade_suggestion', 100 );
/**
 * Enqueues and outputs an Extension Manager suggestion.
 *
 * @since 3.1.0
 * @since 3.2.2 No longer suggests when the user is new.
 * @since 3.2.4 Moved upgrade suggestion call to applicable file.
 * @staticvar bool $run
 *
 * @return void Early when already enqueued
 */
function _prepare_upgrade_suggestion() {
	static $run = false;
	if ( $run ) return;

	if ( \is_admin() ) {
		\add_action( 'admin_init', function() {
			if ( ! _previous_db_version() ) return;
			require THE_SEO_FRAMEWORK_DIR_PATH_FUNCT . 'upgrade-suggestion.php';
		}, 20 );
	}

	$run = true;
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
function _add_upgrade_notice( $notice = '', $get = false ) {

	static $cache = [];

	if ( $get )
		return $cache;

	$cache[] = $notice;
}

\add_action( 'admin_notices', __NAMESPACE__ . '\\_output_upgrade_notices' );
/**
 * Outputs available upgrade notices.
 *
 * @since 2.9.0
 * @since 3.0.0 Added prefix.
 * @uses _add_upgrade_notice()
 */
function _output_upgrade_notices() {

	$notices = _add_upgrade_notice( '', true );

	foreach ( $notices as $notice ) {
		//* @TODO rtl?
		\the_seo_framework()->do_dismissible_notice( 'SEO: ' . $notice, 'updated' );
	}
}

/**
 * Sets initial values for The SEO Framework.
 *
 * @since 3.1.0
 */
function _do_upgrade_1() {
	\the_seo_framework()->register_settings();
	\update_option( 'the_seo_framework_upgraded_db_version', '1' );
}

/**
 * Upgrades term metadata for version 2701.
 *
 * @since 2.7.0
 * @since 3.1.0 No longer tries to set term meta for ID 0.
 *              `add_metadata()` already blocked this, this is defensive.
 */
function _do_upgrade_2701() {

	$term_meta = \get_option( 'autodescription-term-meta' );

	if ( $term_meta ) {
		foreach ( (array) $term_meta as $term_id => $meta ) {
			\add_term_meta( $term_id, THE_SEO_FRAMEWORK_TERM_OPTIONS, $meta, true );
		}

		//= Rudimentary test for remaining ~300 users of the past passed, set initial version to 2600.
		\update_option( 'the_seo_framework_initial_db_version', '2600', 'no' );
	}

	\update_option( 'the_seo_framework_upgraded_db_version', '2701' );
}

/**
 * Removes term metadata for version 2802.
 * Reinitializes rewrite data for for sitemap stylesheet.
 *
 * @since 2.8.0
 */
function _do_upgrade_2802() {

	//* Delete old values from database. Removes backwards compatibility.
	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '2701' )
		\delete_option( 'autodescription-term-meta' );

	\update_option( 'the_seo_framework_upgraded_db_version', '2802' );
}

/**
 * Updates Twitter 'photo' card option to 'summary_large_image'.
 *
 * @since 2.9.0
 * @since 3.1.0 Now only sets new options when defaults exists.
 */
function _do_upgrade_2900() {

	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '2900' ) {
		$defaults = _upgrade_default_site_options();

		if ( isset( $defaults['twitter_card'] ) ) {
			$tsf = \the_seo_framework();

			$card_type = trim( \esc_attr( $tsf->get_option( 'twitter_card', false ) ) );
			if ( 'photo' === $card_type ) {
				$tsf->update_option( 'twitter_card', 'summary_large_image' );
				_add_upgrade_notice(
					\esc_html__( 'Twitter Photo Cards have been deprecated. Your site now uses Summary Cards when applicable.', 'autodescription' )
				);
			}
		}
	}

	\update_option( 'the_seo_framework_upgraded_db_version', '2900' );
}

/**
 * Converts sitemap timestamp settings to global timestamp settings.
 * Adds new character counter settings.
 *
 * @since 3.0.0
 * @since 3.0.6 'display_character_counter' option now correctly defaults to 1.
 * @since 3.1.0 Now only sets new options when defaults exist, and when it's an upgraded site.
 */
function _do_upgrade_3001() {

	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '3001' ) {
		$tsf = \the_seo_framework();

		$defaults = _upgrade_default_site_options();

		if ( isset( $defaults['timestamps_format'] ) ) {
			//= Only change if old option exists. Falls back to default upgrader otherwise.
			$sitemap_timestamps = $tsf->get_option( 'sitemap_timestamps', false );
			$tsf->update_option( 'timestamps_format', (string) (int) $sitemap_timestamps ?: $defaults['timestamps_format'] );
			if ( '' !== $sitemap_timestamps ) {
				_add_upgrade_notice(
					\esc_html__( 'The previous sitemap timestamp settings have been converted into new global timestamp settings.', 'autodescription' )
				);
			}
		}

		if ( isset( $defaults['display_character_counter'] ) )
			$tsf->update_option( 'display_character_counter', $defaults['display_character_counter'] );

		if ( isset( $defaults['display_pixel_counter'] ) )
			$tsf->update_option( 'display_pixel_counter', $defaults['display_pixel_counter'] );
	}

	\update_option( 'the_seo_framework_upgraded_db_version', '3001' );
}

/**
 * Loads suggestion for TSFEM.
 * Also deletes sitemap cache.
 *
 * @since 3.0.6
 */
function _do_upgrade_3060() {

	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '3060' )
		\the_seo_framework()->delete_cache( 'sitemap' );

	\update_option( 'the_seo_framework_upgraded_db_version', '3060' );
}

/**
 * Adds global cache option.
 * Sets `auto_description` option.
 * Migrates `title_seperator` option to `title_separator`.
 * Sets `sitemap_query_limit` option.
 * Sets `title_strip_tags` option to known behavior.
 * Migrates `attachment_noindex` option to post type settings.
 * Migrates `attachment_nofollow` option to post type settings.
 * Migrates `attachment_noarchive` option to post type settings.
 *
 * Loads suggestion for TSFEM.
 *
 * @since 3.1.0
 */
function _do_upgrade_3103() {

	// Prevent database lookups when checking for cache.
	\add_option( THE_SEO_FRAMEWORK_SITE_CACHE, [] );

	// If it's an older installation, upgrade these options.
	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '3103' ) {
		$tsf = \the_seo_framework();

		$defaults = _upgrade_default_site_options();

		// Transport title separator.
		if ( isset( $defaults['title_separator'] ) )
			$tsf->update_option( 'title_separator', $tsf->get_option( 'title_seperator' ) ?: $defaults['title_separator'] );

		// Transport attachment_noindex, attachment_nofollow, and attachment_noarchive settings.
		foreach ( [ 'noindex', 'nofollow', 'noarchive' ] as $r ) {
			$_option = $tsf->get_robots_post_type_option_id( $r );
			if ( isset( $defaults[ $_option ] ) ) {
				$_value = (array) ( $tsf->get_option( $_option ) ?: $defaults[ $_option ] );
				$_value['attachment'] = (int) (bool) $tsf->get_option( "attachment_$r" );
				$tsf->update_option( $_option, $_value );
			}
		}

		// Adds default auto description option.
		if ( isset( $defaults['auto_description'] ) )
			$tsf->update_option( 'auto_description', $defaults['auto_description'] );

		// Add default sitemap limit option.
		if ( isset( $defaults['sitemap_query_limit'] ) )
			$tsf->update_option( 'sitemap_query_limit', $defaults['sitemap_query_limit'] );

		// Add non-default HTML stripping option. Defaulting to previous behavior.
		if ( isset( $defaults['title_strip_tags'] ) )
			$tsf->update_option( 'title_strip_tags', 0 );

		// Adds non-default priority option.
		if ( isset( $defaults['sitemaps_priority'] ) )
			$tsf->update_option( 'sitemaps_priority', 1 );
	}

	\update_option( 'the_seo_framework_upgraded_db_version', '3103' );
}
