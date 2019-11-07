<?php
/**
 * @package The_SEO_Framework\Bootstrap\Install
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
 * @since 4.0.0 1. Removed rewrite flushing; unless upgrading from <3300 to 3300
 *              2. Added time limit.
 *              3. No longer runs during AJAX.
 *              4. Added an upgrading lock. Preventing upgrades running simultaneously.
 *                 While this lock is active, the SEO Settings can't be accessed, either.
 */
function _do_upgrade() {

	$tsf = \the_seo_framework();

	if ( ! $tsf->loaded ) return;

	if ( \wp_doing_ajax() ) return;

	if ( $tsf->is_seo_settings_page( false ) ) {
		// phpcs:ignore, WordPress.Security.SafeRedirect -- self_admin_url() is safe.
		\wp_redirect( \self_admin_url() );
		exit;
	}

	// Check if upgrade is locked. Otherwise, lock it.
	if ( \get_transient( 'tsf_upgrade_lock' ) ) return;
	\set_transient( 'tsf_upgrade_lock', true, 300 );

	// Register this AFTER the transient is set. Otherwise, it may clear the transient in another thread.
	register_shutdown_function( __NAMESPACE__ . '\\_release_upgrade_lock' );

	\wp_raise_memory_limit( 'tsf_upgrade' );

	// phpcs:ignore, WordPress.PHP.NoSilencedErrors -- Feature may be disabled.
	@set_time_limit( 300 );

	/**
	 * Clear the cache to prevent an update_option() from saving a stale database version to the cache.
	 * Not all caching plugins recognize 'flush', so delete the options cache too, just to be safe.
	 *
	 * @see WordPress' `.../update-core.php`
	 * @since 3.1.4
	 */
	\wp_cache_flush();
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
	//? We must manually delete settings that are no longer used; we merge them otherwise.
	if ( $version < '3103' ) {
		_do_upgrade_3103();
		$version = '3103';
	}

	if ( $version < '3300' ) {
		_do_upgrade_3300();
		$version = '3300';
	}

	/**
	 * @since 2.7.0
	 */
	\do_action( 'the_seo_framework_upgraded' );
}

/**
 * Releases the upgrade lock on shutdown.
 *
 * When the upgrader halts, timeouts, or crashes for any reason, this will run.
 *
 * @since 4.0.0
 * @TODO add cache flush? @see _upgrade_to_current()
 */
function _release_upgrade_lock() {
	\delete_transient( 'tsf_upgrade_lock' );
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
	 * Clear the cache to prevent a get_option() from retrieving a stale database version to the cache.
	 * Not all caching plugins recognize 'flush', so delete the options cache too, just to be safe.
	 *
	 * @see WordPress' `.../update-core.php`
	 * @since 3.1.4
	 */
	\wp_cache_flush();
	\wp_cache_delete( 'alloptions', 'options' );
}

\add_action( 'the_seo_framework_upgraded', __NAMESPACE__ . '\\_prepare_upgrade_notice', 99 );
/**
 * Prepares a notice when the upgrade is completed.
 *
 * @since 4.0.0
 */
function _prepare_upgrade_notice() {
	\add_action( 'admin_notices', __NAMESPACE__ . '\\_do_upgrade_notice' );
}

/**
 * Outputs "your site has been upgraded" notification to applicable plugin users on upgrade.
 *
 * @since 3.0.6
 */
function _do_upgrade_notice() {

	if ( ! \current_user_can( 'update_plugins' ) ) return;

	$tsf = \the_seo_framework();

	if ( _previous_db_version() ) {
		$tsf->do_dismissible_notice(
			$tsf->convert_markdown(
				sprintf(
					/* translators: %s = Version number, surrounded in markdown-backticks. */
					\esc_html__( 'Thank you for updating The SEO Framework! Your website has been upgraded successfully to use The SEO Framework at database version `%s`.', 'autodescription' ),
					\esc_html( THE_SEO_FRAMEWORK_DB_VERSION )
				),
				[ 'code' ]
			),
			'updated',
			true,
			false
		);
	} else {
		$tsf->do_dismissible_notice(
			\esc_html__( 'Thank you for installing The SEO Framework! Your website is now optimized for SEO, automatically. We hope you enjoy our free plugin. Good luck with your site!', 'autodescription' ),
			'updated',
			false,
			false
		);
		$tsf->do_dismissible_notice(
			$tsf->convert_markdown(
				sprintf(
					/* translators: %s = Link, markdown. */
					\esc_html__( "The SEO Framework only identifies itself rarely during plugin upgrades. We'd like to use this opportunity to highlight our [plugin setup guide](%s).", 'autodescription' ),
					'https://theseoframework.com/docs/seo-plugin-setup/' // Use https://tsf.fyi/docs/setup ? Needless redirection...
				),
				[ 'a' ],
				[ 'a_internal' => false ]
			),
			'info',
			false,
			false
		);
	}
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
	if ( ! \is_admin() ) return;
	if ( ! _previous_db_version() ) return;

	\add_action( 'admin_init', __NAMESPACE__ . '\\_include_upgrade_suggestion', 20 );
}

/**
 * Loads plugin suggestion file
 *
 * @since 4.0.0
 */
function _include_upgrade_suggestion() {

	if ( \The_SEO_Framework\_has_run( __METHOD__ ) ) return;

	require THE_SEO_FRAMEWORK_DIR_PATH_FUNCT . 'upgrade-suggestion.php';
}

/**
 * Lists and returns upgrade notices to be outputted in admin.
 *
 * @since 2.9.0
 * @staticvar array $cache The cached notice strings.
 *
 * @param string $notice The upgrade notice.
 * @param bool   $get    Whether to return the upgrade notices.
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
		\the_seo_framework()->do_dismissible_notice( 'SEO: ' . $notice, 'info' );
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

		//= Rudimentary test for remaining ~300 users of earlier versions passed, set initial version to 2600.
		\update_option( 'the_seo_framework_initial_db_version', '2600', 'no' );
	}

	\update_option( 'the_seo_framework_upgraded_db_version', '2701' );
}

/**
 * Removes term metadata for version 2802.
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
			$tsf->update_option( 'title_separator', $tsf->get_option( 'title_seperator', false ) ?: $defaults['title_separator'] );

		// Transport attachment_noindex, attachment_nofollow, and attachment_noarchive settings.
		foreach ( [ 'noindex', 'nofollow', 'noarchive' ] as $r ) {
			$_option = $tsf->get_robots_post_type_option_id( $r );
			if ( isset( $defaults[ $_option ] ) ) {
				$_value               = (array) ( $tsf->get_option( $_option, false ) ?: $defaults[ $_option ] );
				$_value['attachment'] = (int) (bool) $tsf->get_option( "attachment_$r", false );
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

/**
 * Flushes rewrite rules for one last time.
 * Converts title separator's dash option to ndash.
 * Enables pinging via cron.
 * Flips the home_title_location option from left to right, and vice versa.
 *
 * Annotated as 3300, because 4.0 was supposed to be the 3.3 update before we
 * refactored the whole API.
 *
 * @since 4.0.0
 */
function _do_upgrade_3300() {

	$tsf = \the_seo_framework();

	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '3300' ) {
		// Remove old rewrite rules.
		unset(
			$GLOBALS['wp_rewrite']->extra_rules_top['sitemap\.xml$'],
			$GLOBALS['wp_rewrite']->extra_rules_top['sitemap\.xsl$']
		); // redundant?
		\add_action( 'shutdown', 'flush_rewrite_rules' );

		$defaults = _upgrade_default_site_options();

		// Convert 'dash' title option to 'ndash', silently. Nothing really changes for the user.
		if ( 'dash' === $tsf->get_option( 'title_separator', false ) )
			$tsf->update_option( 'title_separator', 'ndash' );

		// Add default cron pinging option.
		if ( isset( $defaults['ping_use_cron'] ) ) {
			$tsf->update_option( 'ping_use_cron', $defaults['ping_use_cron'] );

			if ( $defaults['ping_use_cron'] ) {
				if ( $tsf->get_option( 'ping_google', false ) || $tsf->get_option( 'ping_bing', false ) ) {
					_add_upgrade_notice(
						\esc_html__( 'A cronjob is now used to ping search engines, and it alerts them to changes in your sitemap.', 'autodescription' )
					);
				}
			}
		}

		// Flip the homepage title location to make it in line with all other titles.
		$home_title_location = $tsf->get_option( 'home_title_location', false );
		if ( 'left' === $home_title_location ) {
			$tsf->update_option( 'home_title_location', 'right' );
		} else {
			$tsf->update_option( 'home_title_location', 'left' );
		}

		_add_upgrade_notice(
			\esc_html__( 'The positions in the "Meta Title Additions Location" setting for the homepage have been reversed, left to right, but the output has not been changed. If you must downgrade for some reason, remember to switch the location back again.', 'autodescription' )
		);
	}

	\update_option( 'the_seo_framework_upgraded_db_version', '3300' );
}
