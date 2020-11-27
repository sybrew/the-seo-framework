<?php
/**
 * @package The_SEO_Framework\Bootstrap\Install
 */

namespace The_SEO_Framework\Bootstrap;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2020 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * @since 4.1.1 No longer memoizes the previous version early. This should help bypass the cache flush.
 * @access private
 * @TODO convert to class, see \TSF_Extension_Manager\Upgrader
 *       It's a generator/iterator, so we must wait to PHP>5.5 support.
 *
 * @since 3.2.4 Applied namspacing to this file. All method names have changed.
 */

// phpcs:disable, TSF.Performance.Opcodes.ShouldHaveNamespaceEscape

\add_action( 'init', __NAMESPACE__ . '\\_do_upgrade', 20 );
\add_action( 'the_seo_framework_upgraded', __NAMESPACE__ . '\\_prepare_upgrade_notice', 99, 2 );
\add_action( 'the_seo_framework_upgraded', __NAMESPACE__ . '\\_prepare_upgrade_suggestion', 100, 2 );
\add_action( 'the_seo_framework_downgraded', __NAMESPACE__ . '\\_prepare_downgrade_notice', 99, 2 );

/**
 * @since 4.1.0 Deprecated. We can no longer rely on this from WP 5.5.
 * @deprecated Use persistent notifications, instead.
 */
\add_action( 'admin_notices', __NAMESPACE__ . '\\_output_upgrade_notices' );

/**
 * Returns the default site options.
 * Memoizes the return value.
 *
 * @since 3.1.0
 *
 * @return array The default site options.
 */
function _upgrade_default_site_options() {
	static $cache;
	return isset( $cache ) ? $cache : $cache = \the_seo_framework()->get_default_site_options();
}

/**
 * Returns the version set before upgrading began.
 * Memoizes the return value.
 *
 * @since 3.0.0
 *
 * @return string The prior-to-upgrade TSF db version.
 */
function _previous_db_version() {
	static $cache;
	return isset( $cache ) ? $cache : $cache = \get_option( 'the_seo_framework_upgraded_db_version', '0' );
}

/**
 * Upgrade The SEO Framework to the latest version.
 *
 * Does an iteration of upgrades in order of upgrade appearance.
 * Each called function will upgrade the version by its iteration.
 *
 * @TODO run this upgrader in a separate thread (e.g. via cron)? And store all notices as persistent?
 * TODO Add a notice that the upgrader is still running (and clear it once the upgrade is completed--preferably before the user can see it!)
 *
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
 * @since 4.1.0 Now checks whether the lock is successfully set before proceeding. Preventing race conditions.
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

	$timeout = 5 * MINUTE_IN_SECONDS;

	$lock = _set_upgrade_lock( $timeout );
	// Lock failed to create--probably because it was already locked (or the database failed us).
	if ( ! $lock ) return;

	// Register this AFTER the lock is set. Otherwise, it may clear the lock in another thread.
	// This releases the lock when the upgrade crashes or when we forget to unlock it...
	// ...if the database connection is still valid; otherwise, we'll have to wait for the $timeout to pass.
	register_shutdown_function( __NAMESPACE__ . '\\_release_upgrade_lock' );

	\wp_raise_memory_limit( 'tsf_upgrade' );
	// This may lower the default timeout--which is good. Prevents overlap.
	set_time_limit( $timeout );

	/**
	 * Clear the cache to prevent an update_option() from saving a stale database version to the cache.
	 * Not all caching plugins recognize 'flush', so delete the options cache too, just to be safe.
	 *
	 * @see WordPress's `.../update-core.php`
	 * @since 3.1.4
	 */
	\wp_cache_flush();
	\wp_cache_delete( 'alloptions', 'options' );

	$previous_version = _previous_db_version();

	if ( ! \get_option( 'the_seo_framework_initial_db_version' ) ) {
		// Sets to previous if previous is known. This is a late addition. New sites default to THE_SEO_FRAMEWORK_DB_VERSION.
		\update_option( 'the_seo_framework_initial_db_version', $previous_version ?: THE_SEO_FRAMEWORK_DB_VERSION, 'no' );
	}

	// Don't run the upgrade cycle if the user downgraded. Downgrade, instead.
	if ( $previous_version > THE_SEO_FRAMEWORK_DB_VERSION ) {
		// Novel idea: Allow webmasters to register custom upgrades. Maybe later. See file PHPDoc's TODO.
		// \do_action( 'the_seo_framework_do_downgrade', $previous_version, THE_SEO_FRAMEWORK_DB_VERSION );

		$current_version = _downgrade( $previous_version );

		/**
		 * @since 4.1.0
		 * @internal
		 * @param string $previous_version The previous version the site downgraded from, if any.
		 * @param string $current_version  The current version of the site.
		 */
		\do_action( 'the_seo_framework_downgraded', (string) $previous_version, (string) $current_version );
	} else {
		// Novel idea: Allow webmasters to register custom upgrades. Maybe later. See file PHPDoc's TODO.
		// \do_action( 'the_seo_framework_do_upgrade', $previous_version, THE_SEO_FRAMEWORK_DB_VERSION );

		$current_version = _upgrade( $previous_version );

		/**
		 * @since 2.7.0
		 * @since 4.1.0 Added first parameter, $previous_version
		 * @internal
		 * @param string $previous_version The previous version the site upgraded from, if any.
		 * @param string $current_version The current version of the site.
		 */
		\do_action( 'the_seo_framework_upgraded', (string) $previous_version, (string) $current_version );
	}
}

/**
 * Downgrades the plugin's database.
 *
 * @since 4.1.1
 *
 * @param string $previous_version The previous version the site downgraded from, if any.
 * @return string $current_version The current database version.
 */
function _downgrade( $previous_version ) { // phpcs:ignore,VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

	// We aren't (currently) expecting issues where downgrading causes mayem. 4051 did cause some, though. This was added later; just set to current.
	return _set_to_current_version();
}

/**
 * Upgrades the plugin's database.
 *
 * NOTE do not skip versions. Sometimes, we add options. (e.g., _do_upgrade_1 & _do_upgrade_3103()).
 *
 * @since 4.1.0
 * @TODO detect database transaction failures before continuing the upgrade? But, WordPress doesn't do that, either.
 * @see WP Core upgrade_all()
 *
 * @param string $previous_version The previous version the site upgraded from, if any.
 * @return string $current_version The current database version.
 */
function _upgrade( $previous_version ) {

	$current_version = $previous_version;

	//! From update 3103 henceforth, the upgrade procedures should be backward compatible.
	//? This means no data may be erased for at least 1 major version, or 1 year, whichever is later.
	//? We must manually delete settings that are no longer used; we merge them otherwise.
	//? When a user upgrades beyond this scope, they aren't expected to roll back.
	$versions = [ '1', '2701', '2802', '2900', '3001', '3103', '3300', '4051', '4103', '4110', '4120' ];

	foreach ( $versions as $_version ) {
		if ( $current_version < $_version ) {
			// ( __NAMESPACE__ . "\\_do_upgrade_{$_version}" )(); // This is an undocumented method for variable functions. So much for that.
			call_user_func( __NAMESPACE__ . "\\_do_upgrade_{$_version}" ); // PHP 5.6 compat
			$current_version = _set_version( $_version );
		}
	}

	return _set_to_current_version();
}

/**
 * Returns the lock name.
 *
 * @since 4.1.0
 *
 * @return string
 */
function _get_lock_option() {
	return 'tsf_upgrade.lock';
}

/**
 * Creates the upgrade lock.
 *
 * We don't use WordPress's native locking mechanism because it requires too many dependencies.
 *
 * @since 4.1.0
 * @see WP_Upgrader::create_lock()
 *
 * @param int $release_timeout The timeout of the lock.
 * @return bool False if a lock couldn't be created or if the lock is still valid. True otherwise.
 */
function _set_upgrade_lock( $release_timeout ) {
	global $wpdb;

	$lock_option = _get_lock_option();

	$lock_result = $wpdb->query(
		$wpdb->prepare(
			"INSERT IGNORE INTO `$wpdb->options` ( `option_name`, `option_value`, `autoload` ) VALUES (%s, %s, 'no') /* LOCK */",
			$lock_option,
			time()
		)
	);

	if ( ! $lock_result ) {
		$lock_result = \get_option( $lock_option );

		// If a lock couldn't be created, and there isn't a lock, bail.
		if ( ! $lock_result )
			return false;

		// Check to see if the lock is still valid. If it is, bail.
		if ( $lock_result > ( time() - $release_timeout ) )
			return false;

		// There must exist an expired lock, clear it...
		_release_upgrade_lock();

		// ...and re-gain it.
		return _set_upgrade_lock( $release_timeout );
	}

	// Update the lock, as by this point we've definitely got a lock, just need to fire the actions.
	\update_option( $lock_option, time() );

	return true;
}

/**
 * Releases the upgrade lock on shutdown.
 *
 * When the upgrader halts, timeouts, or crashes for any reason, this will run.
 *
 * @since 4.0.0
 * @since 4.1.0 Now uses a controllable option instead of a transient.
 */
function _release_upgrade_lock() {
	\delete_option( _get_lock_option() );
}

/**
 * Sets upgraded database version.
 *
 * @since 4.1.1
 *
 * @param string|int $version The actual datbase version.
 * @return string The actual database version.
 */
function _set_version( $version = THE_SEO_FRAMEWORK_DB_VERSION ) {

	\update_option( 'the_seo_framework_upgraded_db_version', (string) $version );

	return (string) $version;
}

/**
 * Upgrades the Database version to the latest version.
 *
 * This happens if all iterations have been executed. This ensure this file will
 * no longer be required.
 * This should run once after every plugin update.
 *
 * @since 2.7.0
 * @since 3.1.4 Now flushes the object cache after the setting's updated.
 * @since 4.1.0 Now also releases the upgrade lock, before flushing the cache.
 *
 * @return string The current database version.
 */
function _set_to_current_version() {

	_set_version();

	_release_upgrade_lock();

	/**
	 * Clear the cache to prevent a get_option() from retrieving a stale database version to the cache.
	 * Not all caching plugins recognize 'flush', so delete the options cache too, just to be safe.
	 *
	 * @see WordPress's `.../update-core.php`
	 * @since 3.1.4
	 */
	\wp_cache_flush();
	\wp_cache_delete( 'alloptions', 'options' );

	// The option update might've failed. Try to obtain the latest version.
	return (string) \get_option( 'the_seo_framework_upgraded_db_version' );
}

/**
 * Prepares a notice when the downgrade is completed.
 *
 * @since 4.1.1
 * @TODO Add browser cache flush notice? Or set a pragma/cache-control header?
 *       Users that remove query strings (thanks to YSlow) are to blame, though.
 *       The authors of the plugin that allowed this to happen are even more to blame.
 * @link <https://wordpress.org/support/topic/4-0-admin-interface-not-loading-correctly/>
 *
 * @param string $previous_version The previous version, if any.
 * @param string $current_version The current version of the site.
 */
function _prepare_downgrade_notice( $previous_version, $current_version ) {

	// phpcs:ignore, WordPress.PHP.StrictComparisons.LooseComparison -- might be mixed types.
	if ( $previous_version && $previous_version != $current_version ) { // User successfully downgraded.
		$tsf = \the_seo_framework();

		$tsf->register_dismissible_persistent_notice(
			$tsf->convert_markdown(
				sprintf(
					/* translators: %1$s = New, lower version number, surrounded in markdown-backticks. %2$s = Old, higher version number, surrounded in markdown-backticks. */
					\esc_html__( 'Your website has been downgraded successfully to use The SEO Framework at database version `%1$s` from `%2$s`.', 'autodescription' ),
					\esc_html( $current_version ),
					\esc_html( $previous_version )
				),
				[ 'code' ]
			),
			"notify-downgraded-$current_version",
			[
				'type'   => 'updated',
				'icon'   => true,
				'escape' => false,
			],
			[
				'screens'      => [],
				'excl_screens' => [ 'post', 'term', 'upload', 'media', 'plugin-editor', 'plugin-install', 'themes' ],
				'capability'   => 'update_plugins',
				'user'         => 0,
				'count'        => 1,
				'timeout'      => DAY_IN_SECONDS,
			]
		);
	}
}

/**
 * Prepares a notice when the upgrade is completed.
 *
 * @since 4.0.0
 * @since 4.1.0 1. Moved admin notice user capability check here.
 *              2. Now registers persistent notice for the update version.
 * @since 4.1.2 No longer can accidentally show the install notice after stale upgrade.
 * @TODO Add browser cache flush notice? Or set a pragma/cache-control header?
 *       Users that remove query strings (thanks to YSlow) are to blame, though.
 *       The authors of the plugin that allowed this to happen are even more to blame.
 * @link <https://wordpress.org/support/topic/4-0-admin-interface-not-loading-correctly/>
 *
 * @param string $previous_version The previous version, if any.
 * @param string $current_version The current version of the site.
 */
function _prepare_upgrade_notice( $previous_version, $current_version ) {

	// phpcs:ignore, WordPress.PHP.StrictComparisons.LooseComparison -- might be mixed types.
	if ( $previous_version && $previous_version != $current_version ) { // User successfully upgraded.
		$tsf = \the_seo_framework();

		$tsf->register_dismissible_persistent_notice(
			$tsf->convert_markdown(
				sprintf(
					/* translators: %s = Version number, surrounded in markdown-backticks. */
					\esc_html__( 'Thank you for updating The SEO Framework! Your website has been upgraded successfully to use The SEO Framework at database version `%s`.', 'autodescription' ),
					\esc_html( $current_version )
				),
				[ 'code' ]
			),
			"thank-you-updated-$current_version",
			[
				'type'   => 'updated',
				'icon'   => true,
				'escape' => false,
			],
			[
				'screens'      => [],
				'excl_screens' => [ 'post', 'term', 'upload', 'media', 'plugin-editor', 'plugin-install', 'themes' ],
				'capability'   => 'update_plugins',
				'user'         => 0,
				'count'        => 1,
				'timeout'      => DAY_IN_SECONDS,
			]
		);
	} elseif ( ! $previous_version && $current_version ) { // User successfully installed.
		if ( \current_user_can( 'update_plugins' ) ) {
			\add_action( 'admin_notices', __NAMESPACE__ . '\\_do_install_notice' );
		}
	}
}

/**
 * Outputs "your site has been installed" notification to applicable plugin users on upgrade.
 *
 * @since 4.1.0
 */
function _do_install_notice() {

	$tsf = \the_seo_framework();

	// Make this persistent (2x count, 1 minute timeout)?
	$tsf->do_dismissible_notice(
		sprintf(
			'<p>%s</p><p>%s</p>',
			\esc_html__( 'The SEO Framework automatically optimizes your website for search engines and social media.', 'autodescription' ),
			$tsf->convert_markdown(
				sprintf(
					/* translators: %s = Link, markdown. */
					\esc_html__( 'To take full advantage of all SEO features, please follow our [5-minute setup guide](%s).', 'autodescription' ),
					'https://theseoframework.com/docs/seo-plugin-setup/' // Use https://tsf.fyi/docs/setup ? Needless redirection...
				),
				[ 'a' ],
				[ 'a_internal' => false ]
			)
		),
		'info',
		false,
		false
	);
}

/**
 * Enqueues and outputs an Extension Manager suggestion.
 *
 * @since 3.1.0
 * @since 3.2.2 No longer suggests when the user is new.
 * @since 3.2.4 Moved upgrade suggestion call to applicable file.
 * @since 4.1.0 Now also includes the file on the front-end, so it can register the notice.
 *
 * @param string $previous_version The previous version the site upgraded from, if any.
 * @param string $current_version The current version of the site.
 * @return void Early when already enqueued
 */
function _prepare_upgrade_suggestion( $previous_version, $current_version ) {
	// Don't invoke if the user didn't upgrade.
	if ( ! $previous_version ) return;

	// Can this even run twice? Let's play it safe to prevent crashes.
	if ( \The_SEO_Framework\_has_run( __METHOD__ ) ) return;

	require THE_SEO_FRAMEWORK_DIR_PATH_FUNCT . 'upgrade-suggestion.php';
}

/**
 * Memoize and returns upgrade notices to be outputted in admin.
 *
 * @since 2.9.0
 * @since 4.1.0 Deprecated. We can no longer rely on this from WP 5.5.
 * @deprecated Use persistent notifications, instead.
 *
 * @param string $notice The upgrade notice.
 * @param bool   $get    Whether to return the upgrade notices.
 * @return array|void The notices when $get is true.
 */
function _add_upgrade_notice( $notice = '', $get = false ) {

	// Memoize the strings for a later $get
	static $cache = [];

	if ( $get )
		return $cache;

	$cache[] = $notice;
}

/**
 * Outputs available upgrade notices.
 *
 * @since 2.9.0
 * @since 3.0.0 Added prefix.
 * @since 4.1.0 Deprecated. We can no longer rely on this from WP 5.5.
 * @deprecated Use persistent notifications, instead.
 * @uses _add_upgrade_notice()
 */
function _output_upgrade_notices() {
	foreach ( _add_upgrade_notice( '', true ) as $notice ) {
		// @TODO rtl?
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
}

/**
 * Removes term metadata for version 2802.
 *
 * @since 2.8.0
 */
function _do_upgrade_2802() {
	// Delete old values from database. Removes backwards compatibility.
	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '2701' )
		\delete_option( 'autodescription-term-meta' );
}

/**
 * Updates Twitter 'photo' card option to 'summary_large_image'.
 *
 * @since 2.9.0
 * @since 3.1.0 Now only sets new options when defaults exists.
 * @since 4.1.1 No longer tests for default options.
 */
function _do_upgrade_2900() {

	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '2900' ) {
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

/**
 * Converts sitemap timestamp settings to global timestamp settings.
 * Adds new character counter settings.
 *
 * @since 3.0.0
 * @since 3.0.6 'display_character_counter' option now correctly defaults to 1.
 * @since 3.1.0 Now only sets new options when defaults exist, and when it's an upgraded site.
 * @since 4.1.1 No longer tests for default options.
 */
function _do_upgrade_3001() {

	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '3001' ) {
		$tsf = \the_seo_framework();

		//= Only show notice if old option exists. Falls back to default upgrader otherwise.
		$sitemap_timestamps = $tsf->get_option( 'sitemap_timestamps', false );
		if ( '' !== $sitemap_timestamps ) {
			$tsf->update_option( 'timestamps_format', (string) (int) $sitemap_timestamps );
			_add_upgrade_notice(
				\esc_html__( 'The previous sitemap timestamp settings have been converted into new global timestamp settings.', 'autodescription' )
			);
		} else {
			$tsf->update_option( 'timestamps_format', '1' );
		}

		$tsf->update_option( 'display_pixel_counter', 1 );
		$tsf->update_option( 'display_character_counter', 1 );
	}
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
 * @since 4.1.1 No longer tests for default options.
 */
function _do_upgrade_3103() {

	// Prevent database lookups when checking for cache.
	\add_option( THE_SEO_FRAMEWORK_SITE_CACHE, [] );

	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '3103' ) {
		$tsf = \the_seo_framework();

		// Transport title separator (option name typo).
		$tsf->update_option( 'title_separator', $tsf->get_option( 'title_seperator', false ) ?: 'hyphen' );

		// Transport attachment_noindex, attachment_nofollow, and attachment_noarchive settings.
		foreach ( [ 'noindex', 'nofollow', 'noarchive' ] as $r ) {
			$_attachment_option = (int) (bool) $tsf->get_option( "attachment_$r", false );

			$_value = [];

			// Only populate when set. An empty array is fine.
			if ( $_attachment_option )
				$_value['attachment'] = $_attachment_option;

			$tsf->update_option( $tsf->get_robots_post_type_option_id( $r ), $_value );
		}

		// Adds default auto description option.
		$tsf->update_option( 'auto_description', 1 );

		// Add default sitemap limit option.
		$tsf->update_option( 'sitemap_query_limit', 3000 );

		// Add non-default HTML stripping option. Defaulting to previous behavior.
		$tsf->update_option( 'title_strip_tags', 0 ); // NOTE: Default is 1.

		// Adds non-default priority option.
		$tsf->update_option( 'sitemaps_priority', 1 ); // NOTE: Default is 0.
	}
}

/**
 * Flushes rewrite rules for one last time.
 * Converts title separator's dash option to ndash.
 * Enables pinging via cron.
 * Flips the home_title_location option from left to right, and vice versa.
 *
 * Annotated as 3300, because 4.0 was supposed to be the 3.3 update before we
 * refactored the whole API (1000+ changes).
 *
 * @since 4.0.0
 * @since 4.0.5 The upgrader now updates "dash" to "hyphen".
 */
function _do_upgrade_3300() {

	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '3300' ) {
		$tsf = \the_seo_framework();

		// Remove old rewrite rules.
		unset(
			$GLOBALS['wp_rewrite']->extra_rules_top['sitemap\.xml$'],
			$GLOBALS['wp_rewrite']->extra_rules_top['sitemap\.xsl$']
		); // redundant?
		\add_action( 'shutdown', 'flush_rewrite_rules' );

		// Convert 'dash' title option to 'hyphen', silently. Nothing notably changes for the user.
		if ( 'dash' === $tsf->get_option( 'title_separator', false ) )
			$tsf->update_option( 'title_separator', 'hyphen' );

		// Add default cron pinging option.
		$tsf->update_option( 'ping_use_cron', 1 );

		if ( $tsf->get_option( 'ping_google', false ) || $tsf->get_option( 'ping_bing', false ) ) {
			_add_upgrade_notice(
				\esc_html__( 'A cronjob is now used to ping search engines, and it alerts them to changes in your sitemap.', 'autodescription' )
			);
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
}

/**
 * Registers the `advanced_query_protection` option. 0 for existing sites. 1 for new sites.
 * Registers the `index_the_feed` option, boolean.
 * Registers the `baidu_verification` option, string.
 * Registers the `oembed_scripts` option, boolean.
 * Registers the `oembed_remove_author` option, boolean.
 * Registers the `theme_color` option, string.
 *
 * @since 4.0.5
 */
function _do_upgrade_4051() {

	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '4051' ) {
		$tsf = \the_seo_framework();

		$tsf->update_option( 'advanced_query_protection', 0 );
		$tsf->update_option( 'index_the_feed', 0 );
		$tsf->update_option( 'baidu_verification', '' );
		$tsf->update_option( 'oembed_scripts', 1 );
		$tsf->update_option( 'oembed_remove_author', 0 );
		$tsf->update_option( 'theme_color', '' );
	}
}

/**
 * Registers the `disabled_taxonomies` option, array.
 * Registers the `sitemap_logo_url` option, string.
 * Registers the `sitemap_logo_id` option, int.
 * Registers the `social_title_rem_additions` option, int. 0 for current users, 1 for new.
 * Registers and migrates the robots taxonomy options. Sets defaults (['noindex']['post_format'] = 1).
 *
 * @since 4.1.0
 */
function _do_upgrade_4103() {

	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '4103' ) {
		$tsf = \the_seo_framework();

		$tsf->update_option( 'disabled_taxonomies', [] );
		$tsf->update_option( 'sitemap_logo_url', '' );
		$tsf->update_option( 'sitemap_logo_id', 0 );
		$tsf->update_option( 'social_title_rem_additions', 0 );

		// Transport category_noindex/nofollow/noarchive and tag_noindex/nofollow/noarchive settings.
		$_new_pt_option_defaults = [
			'noindex'   => [
				'post_format' => 1,
			],
			'nofollow'  => [],
			'noarchive' => [],
		];
		foreach ( [ 'noindex', 'nofollow', 'noarchive' ] as $r ) {
			$_value = $_new_pt_option_defaults[ $r ];

			$_category_option = (int) (bool) $tsf->get_option( "category_$r", false );
			$_post_tag_option = (int) (bool) $tsf->get_option( "tag_$r", false );

			if ( $_category_option )
				$_value['category'] = $_category_option;
			if ( $_post_tag_option )
				$_value['post_tag'] = $_post_tag_option;

			$tsf->update_option( $tsf->get_robots_taxonomy_option_id( $r ), $_value );
		}
	}
}

// category_$r and tag_$r must be deleted at 4.2 or 5.0 (whichever comes);

/**
 * Registers the `oembed_use_og_title` option, boolean.
 * Registers the `oembed_use_social_image` option, boolean. Differs from default option.
 *
 * @since 4.1.1
 */
function _do_upgrade_4110() {

	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '4110' ) {
		$tsf = \the_seo_framework();

		$tsf->update_option( 'oembed_use_og_title', 0 );
		$tsf->update_option( 'oembed_use_social_image', 0 ); // Defaults to 1 for new sites!
	}
}

/**
 * Registers the `ping_use_cron_prerender` option, boolean.
 *
 * @since 4.1.2
 */
function _do_upgrade_4120() {
	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '4120' ) {
		$tsf = \the_seo_framework();

		$tsf->update_option( 'ping_use_cron_prerender', 0 );
	}
}
