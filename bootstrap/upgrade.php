<?php
/**
 * @package The_SEO_Framework\Bootstrap\Install
 */

namespace The_SEO_Framework\Bootstrap;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

// phpcs:disable TSF.Performance.Opcodes.ShouldHaveNamespaceEscape -- Too many scoped funcs. Test me once in a while.

use The_SEO_Framework\{
	Admin,
	Data,
	Data\Filter\Sanitize,
};
use The_SEO_Framework\Helper\{
	Format\Markdown,
	Query,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2026 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * This file loads while the saved database version differs from the plugin's
 * database version.
 *
 * @since 2.7.0
 * @since 3.2.4 Applied namespacing to this file. All method names have changed.
 * @since 4.1.1 No longer memoizes the previous version early. This should help bypass the cache flush.
 * @access private
 * @TODO convert to class, see \TSF_Extension_Manager\Upgrader
 *       It's a generator/iterator, so we must wait to PHP>5.5 support.
 */

\add_action(
	'init',
	'The_SEO_Framework\Bootstrap\_do_upgrade',
	20,
);
\add_action(
	'the_seo_framework_upgraded',
	'The_SEO_Framework\Bootstrap\_prepare_upgrade_notice',
	99,
	2,
);
\add_action(
	'the_seo_framework_upgraded',
	'The_SEO_Framework\Bootstrap\_prepare_upgrade_suggestion',
	100,
	2,
);
\add_action(
	'the_seo_framework_downgraded',
	'The_SEO_Framework\Bootstrap\_prepare_downgrade_notice',
	99,
	2,
);

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
 * @since 5.2.2 1. Now verifies the initial database version when backfilling it.
 *              2. Now completes an upgrade or downgrade only after verifying the settings and database version.
 *              3. Now defers the migration if a multisite blog switch is already active on entry.
 */
function _do_upgrade() {

	if ( \wp_doing_ajax() ) return;

	if ( \is_multisite() && \ms_is_switched() ) return;

	// Check $_GET['page'] before admin_menu registers the page hook.
	if ( Query::is_seo_settings_page( 'page_query' ) ) {
		// phpcs:ignore WordPress.Security.SafeRedirect -- self_admin_url() is safe.
		\wp_redirect( \self_admin_url() );
		exit;
	}

	// Use the same timeout as WordPress Core's update_core().
	$timeout = 5 * \MINUTE_IN_SECONDS;
	$lock    = _set_upgrade_lock( $timeout );

	// Another request may hold the lock, or the database write may have failed.
	if ( ! $lock ) return;

	// Register cleanup only after this request acquires the lock.
	// If the database connection is lost, the lock expires after the timeout.
	register_shutdown_function(
		'The_SEO_Framework\Bootstrap\_release_upgrade_lock',
	);

	\wp_raise_memory_limit( 'tsf_upgrade' );

	$ini_max_execution_time = (int) ini_get( 'max_execution_time' );

	// A limit of 0 means execution time is unlimited.
	if ( 0 !== $ini_max_execution_time && \function_exists( 'set_time_limit' ) )
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

	$previous_version = \the_seo_framework_db_version();

	if ( \THE_SEO_FRAMEWORK_DB_VERSION === $previous_version ) return;

	// Backfill the initial version on older sites. New sites start at the current version.
	if ( ! \get_option( 'the_seo_framework_initial_db_version' ) ) {
		$initial_version = $previous_version ?: \THE_SEO_FRAMEWORK_DB_VERSION;

		\update_option(
			'the_seo_framework_initial_db_version',
			$initial_version,
			false, // don't autoload
		);

		if ( \get_option( 'the_seo_framework_initial_db_version' ) !== $initial_version )
			return;
	}

	// Don't run the upgrade cycle if the user downgraded. Downgrade, instead.
	if ( $previous_version > \THE_SEO_FRAMEWORK_DB_VERSION ) {

		if ( ! _downgrade() ) return;

		/**
		 * @since 4.1.0
		 * @since 5.2.2 Now fires only after the settings and database version are verified.
		 *
		 * @param string $previous_version The previous version the site downgraded from, if any.
		 * @param string $current_version  The current version of the site.
		 */
		\do_action(
			'the_seo_framework_downgraded',
			(string) $previous_version,
			\THE_SEO_FRAMEWORK_DB_VERSION,
		);
	} else {

		if ( ! _upgrade( $previous_version ) ) return;

		/**
		 * @since 2.7.0
		 * @since 4.1.0 Added first parameter, $previous_version
		 * @since 5.2.2 Now fires only after the settings and database version are verified.
		 *
		 * @param string $previous_version The previous version the site upgraded from, if any.
		 * @param string $current_version  The current version of the site.
		 */
		\do_action(
			'the_seo_framework_upgraded',
			(string) $previous_version,
			\THE_SEO_FRAMEWORK_DB_VERSION,
		);
	}
}

/**
 * Downgrades the plugin's database.
 *
 * @since 4.1.1
 * @since 5.2.2 1. Removed the unused $previous_version parameter.
 *              2. Now returns whether the settings and installed database version were saved.
 *
 * @return bool Whether the settings and installed database version were saved.
 */
function _downgrade() {
	return _set_to_current_version();
}

/**
 * Upgrades the plugin's database.
 *
 * @since 4.1.0
 * @since 5.2.2 Now returns false if a settings write or database version checkpoint fails.
 * @see WP Core upgrade_all()
 *
 * @param string $previous_version The previous version the site upgraded from, if any.
 * @return bool Whether the settings and database version checkpoints were verified.
 */
function _upgrade( $previous_version ) {

	// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine -- readability.
	// Keep upgrades backward compatible for at least one major version or one year, whichever is later.
	$versions = [
		'2701', '2802', '2900',
		'3001', '3103', '3300',
		'4051', '4103', '4110', '4200', '4270',
		'5001', '5050', '5100', '5130', '5140', '5200', '5220',
	];
	// phpcs:enable WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine

	// Run each pending migration in version order and checkpoint the database version after each step.
	foreach ( $versions as $_version ) {
		if ( $previous_version >= $_version ) continue;

		if ( false === ( __NAMESPACE__ . "\_do_upgrade_$_version" )() )
			return false;

		if ( \THE_SEO_FRAMEWORK_DB_VERSION === $_version ) break;

		if ( ! _set_version( $_version ) )
			return false;
	}

	return _set_to_current_version();
}

/**
 * Reads the original settings row before WordPress option filters translate its fields.
 * The caller must hold the upgrade lock.
 *
 * @since 5.2.2
 *
 * @return mixed The stored value, including an empty array. Returns null for a missing row
 *               or any other empty value, and false if the database read failed.
 */
function _get_upgrade_options() {

	global $wpdb;

	$value = $wpdb->get_var( $wpdb->prepare(
		"SELECT option_value FROM $wpdb->options WHERE option_name = %s",
		\THE_SEO_FRAMEWORK_SITE_OPTIONS,
	) );

	if ( $wpdb->last_error )
		return false;

	if ( \is_serialized( $value ) )
		$value = unserialize( // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- this is the correct way.
			trim( $value ),
			[ 'allowed_classes' => false ],
		);

	// An empty array is a stored value that the writer can verify.
	return \is_array( $value ) ? $value : ( $value ?: null );
}

/**
 * Writes the full settings array directly to the options table and verifies the saved row.
 * Bypassing option hooks prevents multilingual plugins from translating or rewriting settings during migrations.
 * The caller must hold the upgrade lock.
 *
 * @since 5.2.2
 * @global \wpdb $wpdb
 *
 * @param array $settings The prepared settings.
 * @return bool Whether the database holds the prepared value, including an unchanged write.
 */
function _upgrade_option( $settings ) {

	global $wpdb;

	$exists = null !== $wpdb->get_var( $wpdb->prepare(
		"SELECT 1 FROM $wpdb->options WHERE option_name = %s LIMIT 1",
		\THE_SEO_FRAMEWORK_SITE_OPTIONS,
	) );

	if ( $wpdb->last_error )
		return false;

	$serialized = \maybe_serialize( $settings );

	if ( $exists ) {
		$result = $wpdb->update(
			$wpdb->options,
			[
				'option_value' => $serialized,
				'autoload'     => 'on',
			],
			[ 'option_name' => \THE_SEO_FRAMEWORK_SITE_OPTIONS ],
		);
	} else {
		$result = $wpdb->insert(
			$wpdb->options,
			[
				'option_name'  => \THE_SEO_FRAMEWORK_SITE_OPTIONS,
				'option_value' => $serialized,
				'autoload'     => 'on',
			],
		);
	}

	if ( false === $result )
		return false;

	\wp_cache_delete( 'notoptions', 'options' );
	\wp_cache_delete( 'alloptions', 'options' );
	\wp_cache_delete( \THE_SEO_FRAMEWORK_SITE_OPTIONS, 'options' );
	Data\Plugin::flush_cache();

	return _get_upgrade_options() === $settings;
}

/**
 * Reads the original settings or their defaults before applying a migration.
 *
 * @since 5.2.2
 *
 * @return array|false The settings or their defaults, or false when the read failed.
 */
function _get_legacy_settings() {

	$stored = _get_upgrade_options();

	if ( false === $stored )
		return false;

	return \is_array( $stored ) ? $stored : Data\Plugin\Setup::get_default_options();
}

/**
 * Saves selected settings without dropping fields later migrations may still need.
 *
 * @since 5.2.2
 *
 * @param array $changes The settings to update.
 * @return bool Whether the settings were saved.
 */
function _update_upgrade_settings( $changes ) {

	$stored = _get_legacy_settings();

	if ( false === $stored )
		return false;

	return _upgrade_option( array_merge( $stored, $changes ) );
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

	$lock = $wpdb->query(
		$wpdb->prepare(
			"INSERT IGNORE INTO `$wpdb->options` ( `option_name`, `option_value`, `autoload` ) VALUES (%s, %s, 'off') /* LOCK */",
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
	\update_option( $lock_option, time(), true ); // true = autoload

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
 * @since 5.2.2 1. Now expects the database version as a string.
 *              2. Now returns whether the stored database version matches the requested version.
 *
 * @param string $version The database version to save.
 * @return bool Whether the version was saved or already set.
 */
function _set_version( $version = \THE_SEO_FRAMEWORK_DB_VERSION ) {

	\update_option(
		'the_seo_framework_upgraded_db_version',
		$version,
		true, // autoload
	);

	return \the_seo_framework_db_version() === $version;
}

/**
 * Finalizes settings for the installed version before recording its database version.
 *
 * Adds missing defaults and prunes unrecognized top-level keys without altering
 * existing setting values.
 *
 * @since 2.7.0
 * @since 3.1.4 Now flushes the object cache after the setting's updated.
 * @since 4.1.0 Now also releases the upgrade lock, before flushing the cache.
 * @since 5.2.2 1. Now leaves lock release to the upgrader's shutdown callback.
 *              2. Now verifies the finalized settings before saving and verifying the database version.
 *
 * @return bool Whether the settings and installed database version were saved.
 */
function _set_to_current_version() {

	$stored = _get_upgrade_options();

	if ( false === $stored )
		return false;

	$defaults = Data\Plugin\Setup::get_default_options();
	$settings = array_merge(
		$defaults,
		\is_array( $stored ) ? array_intersect_key( $stored, $defaults ) : [],
	);

	if (
		   ( $stored !== $settings && ! _upgrade_option( $settings ) )
		|| ! _set_version()
	) {
		return false;
	}

	/**
	 * Clear the cache to prevent a get_option() from retrieving a stale database version to the cache.
	 * Not all caching plugins recognize 'flush', so delete the options cache too, just to be safe.
	 *
	 * @see WordPress's `.../update-core.php`
	 * @since 3.1.4
	 */
	\wp_cache_flush();
	\wp_cache_delete( 'alloptions', 'options' );

	return true;
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

	// phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual -- might be mixed types.
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

	// phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual -- might be mixed types.
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
							'https://theseoframework.com/docs/seo-plugin-setup/', // Use https://tsf.fyi/docs/setup ? Needless redirection...
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

		$esc_sql_in = fn( $val ) => \esc_sql(
			\is_scalar( $val )
				? $val
				: array_filter( (array) $val, 'is_scalar' ),
		);

		$found_titles = [];

		foreach ( $meta_types as $data ) {
			// in WP 6.2 we can use %i and whatnot. <https://core.trac.wordpress.org/ticket/52506>
			// <https://make.wordpress.org/core/2022/10/08/escaping-table-and-field-names-with-wpdbprepare-in-wordpress-6-1/>
			$indexes = implode( "', '", $esc_sql_in( $data['in'] ) );
			$table   = \esc_sql( $data['from'] );

			if ( $wpdb->get_var(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $table/$indexes are escaped.
				"SELECT 1 FROM `$table` WHERE meta_key IN ('$indexes') LIMIT 1",
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
							\count( $found_titles ) > 1
								? \wp_sprintf_l( '%l', $found_titles )
								: current( $found_titles ),
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
 * Loads the upgrade suggestion notice when a previous version exists.
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
function _prepare_upgrade_suggestion( $previous_version, $current_version ) { // phpcs:ignore Generic.CodeAnalysis

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
		'upgrade-' . (
			   hash( 'md5', $notice )
			?: uniqid( '', true ) // if md5 is unregistered, it'll return false
		),
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
 * @since 5.2.2 Now verifies the settings write.
 *
 * @return false|null False if the settings write failed.
 */
function _do_upgrade_2900() {

	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '2900' ) {
		$stored = _get_legacy_settings();

		if ( false === $stored )
			return false;

		$card_type = trim( $stored['twitter_card'] ?? '' );

		if ( 'photo' === $card_type ) {
			$stored['twitter_card'] = 'summary_large_image';

			if ( ! _upgrade_option( $stored ) )
				return false;

			_add_upgrade_notice(
				\__( 'Twitter Photo Cards have been deprecated. Your site now uses Summary Cards when applicable.', 'autodescription' ),
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
 * @since 5.2.2 Now saves the site settings in one verified write.
 *
 * @return false|null False if the settings write failed.
 */
function _do_upgrade_3001() {

	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '3001' ) {
		$stored = _get_legacy_settings();

		if ( false === $stored )
			return false;

		$sitemap_timestamps = $stored['sitemap_timestamps'] ?? null;

		$options = [
			'timestamps_format'         => '' !== $sitemap_timestamps
				? (string) (int) $sitemap_timestamps
				: '1',
			'display_pixel_counter'     => 1,
			'display_character_counter' => 1,
		];

		if ( ! _upgrade_option( array_merge( $stored, $options ) ) )
			return false;

		if ( '' !== $sitemap_timestamps )
			_add_upgrade_notice(
				\__( 'The previous sitemap timestamp settings have been converted into new global timestamp settings.', 'autodescription' ),
			);
	}
}

/**
 * Adds global cache option.
 * Sets `auto_description` option.
 * Migrates `title_seperator` option to `title_separator`. cspell:ignore seperator
 * Sets `sitemap_query_limit` option.
 * Sets `title_strip_tags` option to known behavior.
 * Migrates `attachment_noindex` option to post type settings.
 * Migrates `attachment_nofollow` option to post type settings.
 * Migrates `attachment_noarchive` option to post type settings.
 *
 * @since 3.1.0
 * @since 4.1.1 No longer tests for default options.
 * @since 5.0.0 Removed THE_SEO_FRAMEWORK_SITE_CACHE settings registration. (See 5001)
 * @since 5.2.2 Now saves the site settings in one verified write.
 *
 * @return bool|null Whether the settings were saved, or null if this migration did not apply.
 */
function _do_upgrade_3103() {

	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '3103' ) {
		$stored = _get_legacy_settings();

		if ( false === $stored )
			return false;

		$options = [
			'title_separator'     => ( $stored['title_seperator'] ?? '' ) ?: 'hyphen', // Typo.
			'auto_description'    => 1,
			'sitemap_query_limit' => 1000,
			'title_strip_tags'    => 0, // Preserve previous behavior. The default for new sites is 1.
		];

		// Transport attachment_noindex, attachment_nofollow, and attachment_noarchive settings.
		foreach ( [ 'noindex', 'nofollow', 'noarchive' ] as $r ) {
			$_attachment_option = (int) ! empty( $stored[ "attachment_$r" ] );

			$_value = [];

			// Only populate when set. An empty array is fine.
			if ( $_attachment_option )
				$_value['attachment'] = $_attachment_option;

			$options[ Data\Plugin\Helper::get_robots_option_index( 'post_type', $r ) ] = $_value;
		}

		return _upgrade_option( array_merge( $stored, $options ) );
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
 * @since 5.2.2 Now saves the site settings in one verified write.
 *
 * @return false|null False if the settings write failed.
 */
function _do_upgrade_3300() {

	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '3300' ) {
		// Remove old rewrite rules.
		unset(
			$GLOBALS['wp_rewrite']->extra_rules_top['sitemap\.xml$'],
			$GLOBALS['wp_rewrite']->extra_rules_top['sitemap\.xsl$'],
		); // redundant?
		\add_action( 'shutdown', 'flush_rewrite_rules' );

		$stored = _get_legacy_settings();

		if ( false === $stored )
			return false;

		$options = [];

		// Convert 'dash' title option to 'hyphen', silently. Nothing notably changes for the user.
		if ( 'dash' === ( $stored['title_separator'] ?? null ) )
			$options['title_separator'] = 'hyphen';

		// Flip the homepage title location to make it in line with all other titles.
		$options['home_title_location'] = 'left' === ( $stored['home_title_location'] ?? null ) ? 'right' : 'left';

		if ( ! _upgrade_option( array_merge( $stored, $options ) ) )
			return false;

		_add_upgrade_notice(
			\__( 'The positions in the "Meta Title Additions Location" setting for the homepage have been reversed, left to right, but the output has not been changed. If you must downgrade for some reason, remember to switch the location back again.', 'autodescription' ),
		);
	}
}

/**
 * Registers the `advanced_query_protection` option. 0 for existing sites. 1 for new sites.
 * Registers the `index_the_feed` option, Boolean.
 * Registers the `baidu_verification` option, string.
 * Registers the `oembed_scripts` option, Boolean.
 * Registers the `oembed_remove_author` option, Boolean.
 * Registers the `theme_color` option, string.
 *
 * @since 4.0.5
 * @since 5.2.2 Now saves the site settings in one verified write.
 *
 * @return bool|null Whether the settings were saved, or null if this migration did not apply.
 */
function _do_upgrade_4051() {
	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '4051' )
		return _update_upgrade_settings( [
			'advanced_query_protection' => 0,
			'index_the_feed'            => 0,
			'baidu_verification'        => '',
			'oembed_scripts'            => 1,
			'oembed_remove_author'      => 0,
			'theme_color'               => '',
		] );
}

/**
 * Registers the `disabled_taxonomies` option, array.
 * Registers the `sitemap_logo_url` option, string.
 * Registers the `sitemap_logo_id` option, int.
 * Registers the `social_title_rem_additions` option, int. 0 for current users, 1 for new.
 * Registers and migrates the robots taxonomy options. Sets defaults (['noindex']['post_format'] = 1).
 *
 * @since 4.1.0
 * @since 5.2.2 Now saves the site settings in one verified write.
 *
 * @return bool|null Whether the settings were saved, or null if this migration did not apply.
 */
function _do_upgrade_4103() {

	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '4103' ) {
		$stored = _get_legacy_settings();

		if ( false === $stored )
			return false;

		$options = [
			'disabled_taxonomies'        => [],
			'sitemap_logo_url'           => '',
			'sitemap_logo_id'            => 0,
			'social_title_rem_additions' => 0,
		];

		// Transport category_noindex/nofollow/noarchive and tag_noindex/nofollow/noarchive settings.
		$robots_defaults = [
			'noindex'   => [ 'post_format' => 1 ],
			'nofollow'  => [],
			'noarchive' => [],
		];

		foreach ( $robots_defaults as $r => $_value ) {
			$_category_option = (int) ! empty( $stored[ "category_$r" ] );
			$_post_tag_option = (int) ! empty( $stored[ "tag_$r" ] );

			if ( $_category_option )
				$_value['category'] = $_category_option;

			if ( $_post_tag_option )
				$_value['post_tag'] = $_post_tag_option;

			$options[ Data\Plugin\Helper::get_robots_option_index( 'taxonomy', $r ) ] = $_value;
		}

		return _upgrade_option( array_merge( $stored, $options ) );
	}
}

// category_$r and tag_$r must be deleted at 4.2 or 5.0 (whichever comes);

/**
 * Registers the `oembed_use_og_title` option, Boolean.
 * Registers the `oembed_use_social_image` option, Boolean. Differs from default option.
 *
 * @since 4.1.1
 * @since 5.2.2 Now saves the site settings in one verified write.
 *
 * @return bool|null Whether the settings were saved, or null if this migration did not apply.
 */
function _do_upgrade_4110() {
	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '4110' )
		return _update_upgrade_settings( [
			'oembed_use_og_title'     => 0,
			'oembed_use_social_image' => 0, // Defaults to 1 for new sites.
		] );
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
 * @since 5.2.2 Now verifies the settings write.
 *
 * @return bool|null Whether the settings were saved, or null if this migration did not apply.
 */
function _do_upgrade_4270() {
	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '4270' )
		return _update_upgrade_settings( [ 'auto_description_html_method' => 'fast' ] );
}

/**
 * Registers option `THE_SEO_FRAMEWORK_SITE_CACHE`.
 * Deletes the static cache for exclusions.
 * Changes `auto_descripton_html_method` to `auto_description_html_method`. (typo) cspell:ignore descripton
 * Changes option `autodescription-updates-cache` to constant value THE_SEO_FRAMEWORK_SITE_CACHE.
 * Enables `ld_json_enabled` only if any structured data function used to be active.
 * Sets `homepage_twitter_card_type` to an empty string (aka default).
 *
 * @since 5.0.0
 * @since 5.2.2 Now saves the site settings in one verified write.
 * @global \wpdb $wpdb
 *
 * @return false|null False if the settings write failed.
 */
function _do_upgrade_5001() {

	// Not a public "setting" -- only add the option to prevent additional db-queries when it's yet to be populated.
	\add_option(
		\THE_SEO_FRAMEWORK_SITE_CACHE,
		Data\Plugin\Setup::get_default_site_caches(),
		'',
		true,
	);

	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '5001' ) {
		$stored = _get_legacy_settings();

		if ( false === $stored )
			return false;

		$ld_json_enabled = ! empty( $stored['ld_json_searchbox'] )
			|| ! empty( $stored['ld_json_breadcrumbs'] )
			|| ! empty( $stored['knowledge_output'] );

		$options = [
			'auto_description_html_method' => ( $stored['auto_descripton_html_method'] ?? '' ) ?: 'fast',
			'seo_bar_low_contrast'         => 0,
			'ld_json_enabled'              => (int) $ld_json_enabled,
			'homepage_twitter_card_type'   => '',
		];

		if ( ! _upgrade_option( array_merge( $stored, $options ) ) )
			return false;

		$site_cache = \get_option( 'autodescription-updates-cache' ) ?: [];

		if ( $site_cache ) {
			// Try to use the options API as much as possible, instead of using $wpdb->update().
			\update_option( \THE_SEO_FRAMEWORK_SITE_CACHE, $site_cache, true );
			// The option holds only generated data that can be regenerated easily.
			// On downgrade, this will be repopulated.
			\delete_option( 'autodescription-updates-cache' );
		}

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
 * Copies `ping_use_cron_prerender` to `sitemap_cron_prerender`.
 * Final settings pruning removes the retired ping options.
 *
 * @since 5.0.5
 * @since 5.2.2 Now verifies the settings write.
 *
 * @return bool|null Whether the settings were saved, or null if this migration did not apply.
 */
function _do_upgrade_5050() {

	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '5050' ) {
		$stored = _get_legacy_settings();

		if ( false === $stored )
			return false;

		$stored['sitemap_cron_prerender'] = ( $stored['ping_use_cron_prerender'] ?? 0 ) ?: 0;

		return _upgrade_option( $stored );
	}
}

/**
 * Registers new options 'robotstxt_block_ai', 'robotstxt_block_seo', 'homepage_canonical', and 'homepage_redirect'.
 *
 * @since 5.1.0
 * @since 5.2.2 Now verifies the settings write.
 *
 * @return bool|null Whether the settings were saved, or null if this migration did not apply.
 */
function _do_upgrade_5100() {
	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '5100' )
		return _update_upgrade_settings( [
			'robotstxt_block_ai'  => 0,
			'robotstxt_block_seo' => 0,
			'homepage_canonical'  => '',
			'homepage_redirect'   => '',
		] );
}

/**
 * Registers new options 'display_list_edit_options', 'display_term_edit_options',
 * and 'display_user_edit_options'.
 *
 * @since 5.1.3
 * @since 5.2.2 Now verifies the settings write.
 *
 * @return bool|null Whether the settings were saved, or null if this migration did not apply.
 */
function _do_upgrade_5130() {
	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '5130' )
		return _update_upgrade_settings( [
			'display_list_edit_options' => 1,
			'display_term_edit_options' => 1,
			'display_user_edit_options' => 1,
		] );
}

/**
 * Registers new option 'breadcrumb_use_meta_title'. Enabled for upgraded sites, disabled for new sites.
 *
 * @since 5.1.4
 * @since 5.2.2 Now verifies the settings write.
 *
 * @return bool|null Whether the settings were saved, or null if this migration did not apply.
 */
function _do_upgrade_5140() {
	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '5140' )
		return _update_upgrade_settings( [ 'breadcrumb_use_meta_title' => 1 ] );
}

/**
 * Registers new options 'facebook_verification', 'fediverse_site',
 * 'fediverse_site_url', 'fediverse_creator', 'fediverse_creator_url',
 * 'breadcrumb_archive', 'breadcrumb_taxonomy', 'knowledge_tiktok',
 * and 'edit_proposed_values'. Copies `counter_type` from user SEO meta
 * into user preferences.
 *
 * @since 5.2.0
 * @since 5.2.0 Now also registers `edit_proposed_values`, disabled for upgraded sites.
 * @since 5.2.2 Now verifies the settings write.
 *
 * @return false|null False if the settings write failed.
 */
function _do_upgrade_5200() {

	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '5200' ) {
		if ( ! _update_upgrade_settings( [
			'facebook_verification' => '',
			'fediverse_site'        => '',
			'fediverse_site_url'    => '',
			'fediverse_creator'     => '',
			'fediverse_creator_url' => '',
			'breadcrumb_archive'    => [],
			'breadcrumb_taxonomy'   => [],
			'knowledge_tiktok'      => '',
			'edit_proposed_values'  => 0,
		] ) ) {
			return false;
		}

		global $wpdb;

		// This runs on every site in a multisite. The number of authors ought to be limited.
		// The counter_type guard below stops processing when it isn't set.
		// Race condition writes are not an issue; add_user_meta's $unique prevents cache thrashing.
		$meta_rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT user_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = %s",
			\THE_SEO_FRAMEWORK_USER_OPTIONS,
		) );

		foreach ( $meta_rows as $row ) {
			$meta = \is_serialized( $row->meta_value )
				? unserialize( // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- this is the correct way.
					trim( $row->meta_value ),
					[ 'allowed_classes' => false ],
				)
				: $row->meta_value;

			if ( ! \is_array( $meta ) || ! isset( $meta['counter_type'] ) )
				continue;

			\add_user_meta(
				(int) $row->user_id,
				\THE_SEO_FRAMEWORK_USER_PREFERENCES,
				[ 'counter_type' => Sanitize::counter_type( $meta['counter_type'] ) ],
				true, // unique
			);
		}
	}
}

/**
 * Disables taxonomy base removal on sites installed before database version 5220.
 *
 * @since 5.2.2
 *
 * @return bool|null Whether the settings were saved, or null if this migration did not apply.
 */
function _do_upgrade_5220() {
	if ( \get_option( 'the_seo_framework_initial_db_version' ) < '5220' )
		return _update_upgrade_settings( [ 'remove_taxonomy_bases' => [] ] );
}
