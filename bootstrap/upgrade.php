<?php
/**
 * @package The_SEO_Framework\Bootstrap\Install
 */

namespace The_SEO_Framework\Bootstrap;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

// phpcs:disable, TSF.Performance.Opcodes.ShouldHaveNamespaceEscape -- Too many scoped funcs. Test me once in a while.

use \The_SEO_Framework\{
	Admin,
	Data,
};
use \The_SEO_Framework\Helper\{
	Format\Markdown,
	Query,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2024 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * @since 3.2.4 Applied namspacing to this file. All method names have changed.
 * @since 4.1.1 No longer memoizes the previous version early. This should help bypass the cache flush.
 * @access private
 * @TODO convert to class, see \TSF_Extension_Manager\Upgrader
 *       It's a generator/iterator, so we must wait to PHP>5.5 support.
 */

\add_action( 'init', 'The_SEO_Framework\Bootstrap\_do_upgrade', 20 );
\add_action( 'the_seo_framework_upgraded', 'The_SEO_Framework\Bootstrap\_prepare_upgrade_notice', 99, 2 );
\add_action( 'the_seo_framework_upgraded', 'The_SEO_Framework\Bootstrap\_prepare_upgrade_suggestion', 100, 2 );
\add_action( 'the_seo_framework_downgraded', 'The_SEO_Framework\Bootstrap\_prepare_downgrade_notice', 99, 2 );

/**
 * Returns the version set before upgrading began.
 * Memoizes the return value.
 *
 * @since 3.0.0
 *
 * @return string The prior-to-upgrade TSF db version.
 */
function _previous_db_version() {
	static $memo;
	return $memo ??= \get_option( 'the_seo_framework_upgraded_db_version', '0' );
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
 * @hook init 20
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
 * @since 4.2.1 No longer lowers the PHP execution time limit -- only increases it.
 * @since 5.0.0 No longer checks if TSF can load.
 */
function _do_upgrade() {

	if ( \wp_doing_ajax() ) return;

	if ( Query::is_seo_settings_page( false ) ) {
		// phpcs:ignore, WordPress.Security.SafeRedirect -- self_admin_url() is safe.
		\wp_redirect( \self_admin_url() );
		exit;
	}

	$timeout = 5 * \MINUTE_IN_SECONDS; // Same as WP Core, function update_core().

	$lock = _set_upgrade_lock( $timeout );
	// Lock failed to create--probably because it was already locked (or the database failed us).
	if ( ! $lock ) return;

	// Register this AFTER the lock is set. Otherwise, it may clear the lock in another thread.
	// This releases the lock when the upgrade crashes or when we forget to unlock it...
	// ...if the database connection is still valid; otherwise, we'll have to wait for the $timeout to pass.
	register_shutdown_function( 'The_SEO_Framework\Bootstrap\_release_upgrade_lock' );

	\wp_raise_memory_limit( 'tsf_upgrade' );

	$ini_max_execution_time = (int) ini_get( 'max_execution_time' );
	if ( 0 !== $ini_max_execution_time )
		set_time_limit( max( $ini_max_execution_time, $timeout ) );

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
		// Sets to previous if previous is known. This is a late addition. New sites default to \THE_SEO_FRAMEWORK_DB_VERSION.
		\update_option( 'the_seo_framework_initial_db_version', $previous_version ?: \THE_SEO_FRAMEWORK_DB_VERSION, false );
	}

	// Don't run the upgrade cycle if the user downgraded. Downgrade, instead.
	if ( $previous_version > \THE_SEO_FRAMEWORK_DB_VERSION ) {
		// Novel idea: Allow webmasters to register custom upgrades. Maybe later. See file PHPDoc's TODO.
		// If we do, add it in function _downgrade()'s loop instead.
		// \do_action( 'the_seo_framework_do_downgrade', $previous_version, \THE_SEO_FRAMEWORK_DB_VERSION );

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
		// If we do, add it in function _upgrade()'s loop instead.
		// \do_action( 'the_seo_framework_do_upgrade', $previous_version, \THE_SEO_FRAMEWORK_DB_VERSION );

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
 * NOTE do not skip versions. Sometimes, we add options. (e.g., _do_upgrade_1 & _do_upgrade_5001()).
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

	// phpcs:disable, WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine -- readability.
	// NOTE: From update 3103 henceforth, the upgrade procedures should be backward compatible.
	// This means no data may be erased for at least 1 major version, or 1 year, whichever is later.
	// We must manually delete settings that are no longer used; we merge them otherwise.
	// When a user upgrades beyond this range, they aren't expected to roll back.
	$versions = [
		'1',
		'2701', '2802', '2900',
		'3001', '3103', '3300',
		'4051', '4103', '4110', '4200', '4270',
		'5001', '5050', '5100',
	];
	// phpcs:enable, WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine

	foreach ( $versions as $_version ) {
		if ( $current_version < $_version ) {
			( __NAMESPACE__ . "\\_do_upgrade_{$_version}" )();
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

	// TODO WP 6.6+ change 'no' to 'off'.
	$lock = $wpdb->query(
		$wpdb->prepare(
			"INSERT IGNORE INTO `$wpdb->options` ( `option_name`, `option_value`, `autoload` ) VALUES (%s, %s, 'no') /* LOCK */",
			$lock_option,
			time(),
		),
	);

	if ( ! $lock ) {
		$lock = \get_option( $lock_option );

		// If a lock couldn't be created, and there isn't a lock, bail.
		if ( ! $lock )
			return false;

		// Check to see if the lock is still valid. If it is, bail.
		if ( $lock > ( time() - $release_timeout ) )
			return false;

		// There must exist an expired lock, clear it...
		_release_upgrade_lock();

		// ...and re-gain it.
		return _set_upgrade_lock( $release_timeout );
	}

	// Update the lock, as by this point we've definitely got a lock, just need to fire the actions.
	\update_option( $lock_option, time(), true );

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
 * @param string|int $version The actual database version.
 * @return string The actual database version.
 */
function _set_version( $version = \THE_SEO_FRAMEWORK_DB_VERSION ) {

	\update_option( 'the_seo_framework_upgraded_db_version', (string) $version, true );

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
 * @hook the_seo_framework_downgraded 99
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
		Admin\Notice\Persistent::register_notice(
			Markdown::convert(
				\sprintf(
					/* translators: %1$s = New, lower version number, surrounded in markdown-backticks. %2$s = Old, higher version number, surrounded in markdown-backticks. */
					\esc_html__( 'Your website has been downgraded successfully to use The SEO Framework at database version `%1$s` from `%2$s`.', 'autodescription' ),
					\esc_html( $current_version ),
					\esc_html( $previous_version ),
				),
				[ 'code' ],
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
				'timeout'      => \DAY_IN_SECONDS,
			],
		);
	}
}

/**
 * Prepares a notice when the upgrade is completed.
 *
 * @hook the_seo_framework_upgraded 99
 * @since 4.0.0
 * @since 4.1.0 1. Moved admin notice user capability check here.
 *              2. Now registers persistent notice for the update version.
 * @since 4.1.2 No longer can accidentally show the install notice after stale upgrade.
 * @since 4.2.0 The installation notice is now persistent, shown twice, to users with activate_plugins capability, on the main site.
 * @since 4.2.7 Added data checker directing users to the Transport extension.
 * @since 4.2.8 Now displays the installation notice 3 times.
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
		Admin\Notice\Persistent::register_notice(
			Markdown::convert(
				\sprintf(
					/* translators: %s = Version number, surrounded in markdown-backticks. */
					\esc_html__( 'Thank you for updating The SEO Framework! Your website has been upgraded successfully to use The SEO Framework at database version `%s`.', 'autodescription' ),
					\esc_html( $current_version ),
				),
				[ 'code' ],
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
				'timeout'      => \DAY_IN_SECONDS,
			],
		);
	} elseif ( ! $previous_version && $current_version ) { // User successfully installed.
		$network_mode = (bool) ( \get_site_option( 'active_sitewide_plugins' )[ \THE_SEO_FRAMEWORK_PLUGIN_BASENAME ] ?? false );

		// Only show notices when not in network mode, or on main site otherwise.
		if ( ! $network_mode || \is_main_site() ) {
			Admin\Notice\Persistent::register_notice(
				\sprintf(
					'<p>%s</p><p>%s</p>',
					\esc_html__( 'The SEO Framework automatically optimizes your website for search engines and social media.', 'autodescription' ),
					Markdown::convert(
						\sprintf(
							/* translators: %s = Link, markdown. */
							\esc_html__( 'To take full advantage of all SEO features, please follow our [5-minute setup guide](%s).', 'autodescription' ),
							'https://theseoframework.com/docs/seo-plugin-setup/' // Use https://tsf.fyi/docs/setup ? Needless redirection...
						),
						[ 'a' ],
						[ 'a_internal' => false ],
					),
				),
				'thank-you-installed',
				[
					'type'   => 'info',
					'icon'   => false,
					'escape' => false,
				],
				[
					'screens'      => [],
					'excl_screens' => [ 'post', 'term', 'upload', 'media', 'plugin-editor', 'plugin-install', 'themes' ],
					'capability'   => 'activate_plugins',
					'user'         => 0,
					'count'        => 3,
					'timeout'      => 2 * \MINUTE_IN_SECONDS,
				],
			);
		}

		global $wpdb;

		// Not everything's included. Only data likely to be inserted by the user manually, and which is actually carried over.
		$meta_types = [
			'wordpress-seo'    => [
				'title' => 'Yoast SEO',
				'from'  => $wpdb->postmeta,
				'in'    => [ '_yoast_wpseo_title', '_yoast_wpseo_metadesc', '_yoast_wpseo_opengraph-title', '_yoast_wpseo_opengraph-description', '_yoast_wpseo_twitter-title', '_yoast_wpseo_twitter-description', '_yoast_wpseo_meta-robots-noindex', '_yoast_wpseo_meta-robots-nofollow', '_yoast_wpseo_canonical' ],
			],
			'seo-by-rank-math' => [
				'title' => 'Rank Math',
				'from'  => $wpdb->postmeta,
				'in'    => [ 'rank_math_title', 'rank_math_description', 'rank_math_facebook_title', 'rank_math_facebook_description', 'rank_math_twitter_title', 'rank_math_twitter_description', 'rank_math_canonical_url', 'rank_math_robots' ],
			],
			'wp-seopress'      => [
				'title' => 'SEOPress',
				'from'  => $wpdb->postmeta,
				'in'    => [ '_seopress_titles_title', '_seopress_titles_desc', '_seopress_social_fb_title', '_seopress_social_fb_desc', '_seopress_social_twitter_title', '_seopress_social_twitter_desc', '_seopress_robots_canonical', '_seopress_robots_index' ],
			],
		];

		$esc_sql_in = function ( $var ) {
			if ( ! \is_scalar( $var ) )
				$var = array_filter( (array) $var, 'is_scalar' );
			return \esc_sql( $var );
		};

		$found_titles = [];

		foreach ( $meta_types as $data ) {
			// in WP 6.2 we can use %i and whatnot. <https://core.trac.wordpress.org/ticket/52506>
			// <https://make.wordpress.org/core/2022/10/08/escaping-table-and-field-names-with-wpdbprepare-in-wordpress-6-1/>
			$indexes = implode( "', '", $esc_sql_in( $data['in'] ) );
			$table   = \esc_sql( $data['from'] );

			if ( $wpdb->get_var(
				// phpcs:ignore, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $table/$indexes are escaped.
				"SELECT 1 FROM `$table` WHERE meta_key IN ('$indexes') LIMIT 1"
			) ) {
				$found_titles[] = $data['title'];
			}
		}

		$found_titles and Admin\Notice\Persistent::register_notice(
			\sprintf(
				'<p>%s</p>',
				Markdown::convert(
					\sprintf(
						/* translators: 1: SEO plugin name(s), 2: link to guide, in Markdown! */
						\esc_html__( 'The SEO Framework detected metadata from %1$s. Whenever you are set, read our [migration guide](%2$s).', 'autodescription' ),
						\esc_html(
							\count( $found_titles ) > 1 ? \wp_sprintf_l( '%l', $found_titles ) : current( $found_titles )
						),
						'https://theseoframework.com/docs/seo-data-migration/',
					),
					[ 'a' ],
					[ 'a_internal' => false ],
				),
			),
			'installed-migration-notice',
			[
				'type'   => 'info',
				'icon'   => true,
				'escape' => false,
			],
			[
				'screens'      => [ 'edit', 'edit-tags', 'dashboard', 'toplevel_page_theseoframework-settings' ],
				'excl_screens' => [],
				'capability'   => 'activate_plugins',
				'user'         => 0,
				'count'        => 69,
				'timeout'      => \WEEK_IN_SECONDS,
			],
		);
	}
}

/**
 * Enqueues and outputs an Extension Manager suggestion.
 *
 * @hook the_seo_framework_upgraded 100
 * @since 3.1.0
 * @since 3.2.2 No longer suggests when the user is new.
 * @since 3.2.4 Moved upgrade suggestion call to applicable file.
 * @since 4.1.0 Now also includes the file on the front-end, so it can register the notice.
 *
 * @param string $previous_version The previous version the site upgraded from, if any.
 * @param string $current_version The current version of the site.
 * @return void Early when already enqueued
 */
function _prepare_upgrade_suggestion( $previous_version, $current_version ) { // phpcs:ignore, VariableAnalysis.CodeAnalysis.VariableAnalysis
	// Don't invoke if the user didn't upgrade.
	if ( ! $previous_version ) return;

	// Can this even run twice? Let's play it safe to prevent crashes.
	if ( \The_SEO_Framework\has_run( __METHOD__ ) ) return;

	require \THE_SEO_FRAMEWORK_DIR_PATH_FUNCT . 'upgrade-suggestion.php';
}

/**
 * Registers upgrade notices.
 *
 * @since 2.9.0
 * @since 4.1.0 Deprecated. We can no longer rely on this from WP 5.5.
 * @since 4.2.0 1. Reinstated, and now forwards notices to the persistent-notice system.
 *              2. No longer returns values. Removed pertaining second parameter.
 *
 * @param string $notice The upgrade notice. Doesn't need to be escaped.
 */
function _add_upgrade_notice( $notice = '' ) {
	Admin\Notice\Persistent::register_notice(
		"SEO: $notice",
		'upgrade-' . ( hash( 'md5', $notice ) ?: uniqid( '', true ) ), // if md5 is unregistered, it'll return false
		[
			'type' => 'info',
		],
		[
			'excl_screens' => [ 'post', 'term', 'upload', 'media', 'plugin-editor', 'plugin-install', 'themes' ],
			'capability'   => \THE_SEO_FRAMEWORK_SETTINGS_CAP,
		],
	);
}

/**
 * Sets initial values for The SEO Framework.
 *
 * @since 3.1.0
 */
function _do_upgrade_1() {
	// Here, `Plugin\Setup::get_default_options()` will get called 3 times in a row. Alas.
	\add_option( \THE_SEO_FRAMEWORK_SITE_OPTIONS, Data\Plugin\Setup::get_default_options(), '', true );
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

		foreach ( (array) $term_meta as $term_id => $meta )
			\add_term_meta( $term_id, \THE_SEO_FRAMEWORK_TERM_OPTIONS, $meta, true );

		// Rudimentary test for remaining ~300 users of earlier versions passed, set initial version to 2600.
		\update_option( 'the_seo_framework_initial_db_version', '2600', false );
	}
}

/**
 * Removes term metadata for version 2802.
 *
 * @since 2.8.0
 */
function _do_upgrade_2802() {
	// Delete old values from database. Removes backwards compatibility. 2701 is intentional.
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
		$card_type = trim( Data\Plugin::get_option( 'twitter_card' ) );
		if ( 'photo' === $card_type ) {
			Data\Plugin::update_option( 'twitter_card', 'summary_large_image' );
			_add_upgrade_notice(
				\__( 'Twitter Photo Cards have been deprecated. Your site now uses Summary Cards when applicable.', 'autodescription' )
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
		// Only show notice if old option exists. Falls back to default upgrader otherwise.
		$sitemap_timestamps = Data\Plugin::get_option( 'sitemap_timestamps' );
		if ( '' !== $sitemap_timestamps ) {
			Data\Plugin::update_option( 'timestamps_format', (string) (int) $sitemap_timestamps );
			_add_upgrade_notice(
				\__( 'The previous sitemap timestamp settings have been converted into new global timestamp settings.', 'autodescription' )
			);
		} else {
			Data\Plugin::update_option( 'timestamps_format', '1' );
		}

		Data\Plugin::update_option( 'display_pixel_counter', 1 );
		Data\Plugin::update_option( 'display_character_counter', 1 );
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
 * @since 5.0.0 Removed THE_SEO_FRAMEWORK_SITE_CACHE settings registration. (See 5001)
 */
function _do_upgrade_3103() {
	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '3103' ) {
		// Transport title separator (option name typo).
		Data\Plugin::update_option(
			'title_separator',
			Data\Plugin::get_option( 'title_seperator' ) ?: 'hyphen', // Typo intended.
		);

		// Transport attachment_noindex, attachment_nofollow, and attachment_noarchive settings.
		foreach ( [ 'noindex', 'nofollow', 'noarchive' ] as $r ) {
			$_attachment_option = (int) (bool) Data\Plugin::get_option( "attachment_$r" );

			$_value = [];

			// Only populate when set. An empty array is fine.
			if ( $_attachment_option )
				$_value['attachment'] = $_attachment_option;

			Data\Plugin::update_option( Data\Plugin\Helper::get_robots_option_index( 'post_type', $r ), $_value );
		}

		// Adds default auto description option.
		Data\Plugin::update_option( 'auto_description', 1 );

		// Add default sitemap limit option.
		Data\Plugin::update_option( 'sitemap_query_limit', 1000 );

		// Add non-default HTML stripping option. Defaulting to previous behavior.
		Data\Plugin::update_option( 'title_strip_tags', 0 ); // NOTE: Default is 1.
	}
}

/**
 * Flushes rewrite rules for one last time.
 * Converts title separator's dash option to ndash.
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
		// Remove old rewrite rules.
		unset(
			$GLOBALS['wp_rewrite']->extra_rules_top['sitemap\.xml$'],
			$GLOBALS['wp_rewrite']->extra_rules_top['sitemap\.xsl$'],
		); // redundant?
		\add_action( 'shutdown', 'flush_rewrite_rules' );

		// Convert 'dash' title option to 'hyphen', silently. Nothing notably changes for the user.
		if ( 'dash' === Data\Plugin::get_option( 'title_separator' ) )
			Data\Plugin::update_option( 'title_separator', 'hyphen' );

		// Flip the homepage title location to make it in line with all other titles.
		$home_title_location = Data\Plugin::get_option( 'home_title_location' );
		if ( 'left' === $home_title_location ) {
			Data\Plugin::update_option( 'home_title_location', 'right' );
		} else {
			Data\Plugin::update_option( 'home_title_location', 'left' );
		}

		_add_upgrade_notice(
			\__( 'The positions in the "Meta Title Additions Location" setting for the homepage have been reversed, left to right, but the output has not been changed. If you must downgrade for some reason, remember to switch the location back again.', 'autodescription' )
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
		Data\Plugin::update_option( 'advanced_query_protection', 0 );
		Data\Plugin::update_option( 'index_the_feed', 0 );
		Data\Plugin::update_option( 'baidu_verification', '' );
		Data\Plugin::update_option( 'oembed_scripts', 1 );
		Data\Plugin::update_option( 'oembed_remove_author', 0 );
		Data\Plugin::update_option( 'theme_color', '' );
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
		Data\Plugin::update_option( 'disabled_taxonomies', [] );
		Data\Plugin::update_option( 'sitemap_logo_url', '' );
		Data\Plugin::update_option( 'sitemap_logo_id', 0 );
		Data\Plugin::update_option( 'social_title_rem_additions', 0 );

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

			$_category_option = (int) (bool) Data\Plugin::get_option( "category_$r" );
			$_post_tag_option = (int) (bool) Data\Plugin::get_option( "tag_$r" );

			if ( $_category_option )
				$_value['category'] = $_category_option;
			if ( $_post_tag_option )
				$_value['post_tag'] = $_post_tag_option;

			Data\Plugin::update_option( Data\Plugin\Helper::get_robots_option_index( 'taxonomy', $r ), $_value );
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
		Data\Plugin::update_option( 'oembed_use_og_title', 0 );
		Data\Plugin::update_option( 'oembed_use_social_image', 0 ); // Defaults to 1 for new sites!
	}
}

/**
 * Removes the global `the_seo_framework_tested_upgrade_version` option.
 *
 * @since 4.2.0
 */
function _do_upgrade_4200() {
	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '4200' )
		\delete_option( 'the_seo_framework_tested_upgrade_version' );
}

/**
 * Registers the `auto_description_html_method` option, string.
 *
 * @since 4.2.7
 */
function _do_upgrade_4270() {
	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '4270' )
		Data\Plugin::update_option( 'auto_description_html_method', 'fast' );
}

/**
 * Registers option `THE_SEO_FRAMEWORK_SITE_CACHE`.
 * Deletes the static cache for exclusions.
 * Changes `auto_descripton_html_method` to `auto_description_html_method`. (typo)
 * Changes option `autodescription-updates-cache` to constant value THE_SEO_FRAMEWORK_SITE_CACHE.
 * Enables `ld_json_enabled` only if any structured data function used to be active.
 * Sets `homepage_twitter_card_type` to an empty string (aka default).
 *
 * @since 5.0.0
 * @global \wpdb $wpdb
 */
function _do_upgrade_5001() {

	// Not a public "setting" -- only add the option to prevent additional db-queries when it's yet to be populated.
	\add_option( \THE_SEO_FRAMEWORK_SITE_CACHE, Data\Plugin\Setup::get_default_site_caches(), '', true );

	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '5001' ) {
		Data\Plugin::update_option(
			'auto_description_html_method',
			Data\Plugin::get_option( 'auto_descripton_html_method' ) ?: 'fast', // Typo intended.
		);

		$site_cache = \get_option( 'autodescription-updates-cache' ) ?: [];
		if ( $site_cache ) {
			// Try to use the options API as much as possible, instead of using $wpdb->update().
			\update_option( \THE_SEO_FRAMEWORK_SITE_CACHE, $site_cache, true );
			// The option holds only generated data that can be regenerated easily.
			// On downgrade, this will be repopulated.
			\delete_option( 'autodescription-updates-cache' );
		}

		Data\Plugin::update_option( 'seo_bar_low_contrast', 0 );

		if (
			   Data\Plugin::get_option( 'ld_json_searchbox' )
			|| Data\Plugin::get_option( 'ld_json_breadcrumbs' )
			|| Data\Plugin::get_option( 'knowledge_output' )
		) {
			Data\Plugin::update_option( 'ld_json_enabled', 1 );
		} else {
			Data\Plugin::update_option( 'ld_json_enabled', 0 );
		}

		Data\Plugin::update_option( 'homepage_twitter_card_type', '' );

		global $wpdb;

		// Cleanup leftover from TSF 3.0.0 ~ 3.1.0. Sans trailing _, since it doesn't support multilingual.
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM $wpdb->options WHERE option_name LIKE %s",
			$wpdb->esc_like( "_transient_tsf_exclude_0_{$GLOBALS['blog_id']}" ) . '%',
		) );
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM $wpdb->options WHERE option_name LIKE %s",
			$wpdb->esc_like( "_transient_timeout_tsf_exclude_0_{$GLOBALS['blog_id']}" ) . '%',
		) );

		// Cleanup from 3.1.0 ~ 4.2.8. This data will be rebuilt automatically.
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM $wpdb->options WHERE option_name LIKE %s",
			$wpdb->esc_like( "_transient_tsf_exclude_1_{$GLOBALS['blog_id']}_" ) . '%',
		) );
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM $wpdb->options WHERE option_name LIKE %s",
			$wpdb->esc_like( "_transient_timeout_tsf_exclude_1_{$GLOBALS['blog_id']}_" ) . '%',
		) );
	}
}

/**
 * Changes `ping_use_cron_prerender` to `sitemap_cron_prerender`.
 * Subsequently, it removes indexes `ping_google` and `ping_bing`. Downgrading will keep these disabled, which is fine.
 *
 * @since 5.0.5
 */
function _do_upgrade_5050() {

	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '5050' ) {
		Data\Plugin::update_option(
			'sitemap_cron_prerender',
			Data\Plugin::get_option( 'ping_use_cron_prerender' ) ?: 0,
		);
	}
}

/**
 * Registers new options 'robotstxt_block_ai', 'robotstxt_block_seo', 'homepage_canonical', and 'homepage_redirect'.
 *
 * @since 5.1.0
 */
function _do_upgrade_5100() {

	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '5100' ) {
		Data\Plugin::update_option( 'robotstxt_block_ai', 0 );
		Data\Plugin::update_option( 'robotstxt_block_seo', 0 );
		Data\Plugin::update_option( 'homepage_canonical', '' );
		Data\Plugin::update_option( 'homepage_redirect', '' );
	}
}
