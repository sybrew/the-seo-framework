<?php
/**
 * @package The_SEO_Framework\Classes\Data\Admin\Plugin
 * @subpackage The_SEO_Framework\Data
 */

namespace The_SEO_Framework\Data\Admin;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\{
	Admin, // Yes, it ios legal to share class and namespace.
	Data,
	Helper\Query,
	Helper\Format\Arrays,
	Sitemap,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds a collection of data saving methods for posts.
 *
 * @since 5.0.0
 * @access private
 */
class Plugin {

	/**
	 * Register the database settings for saving and sanitizing via standard WordPress hooks.
	 *
	 * @hook admin_init 0
	 * @since 2.2.2
	 * @since 2.9.0 Removed reset options check, see check_options_reset().
	 * @since 3.1.0 Removed settings field existence check.
	 * @since 4.0.0 Now checks if the option exists before adding it. Shaves 20μs...
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Is now marked private.
	 *              3. No longer adds options to the database, this is taken care off at `bootstrap\upgrade.php`.
	 */
	public static function register_settings() {
		\register_setting(
			\THE_SEO_FRAMEWORK_SITE_OPTIONS,
			\THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[
				'type' => 'array',
			],
		);
	}

	/**
	 * Prepares updating of TSF settings.
	 *
	 * @hook admin_action_update 10
	 * @since 5.0.0
	 */
	public static function process_settings_update() {

		if (
			   empty( $_POST[ \THE_SEO_FRAMEWORK_SITE_OPTIONS ] )
			|| ! \is_array( $_POST[ \THE_SEO_FRAMEWORK_SITE_OPTIONS ] )
		) return;

		// This is also handled in /wp-admin/options.php. Nevertheless, one might register outside of scope.
		if ( ! \current_user_can( \THE_SEO_FRAMEWORK_SETTINGS_CAP ) )
			return;

		// This is also handled in /wp-admin/options.php. Nevertheless, one might register outside of scope.
		\check_admin_referer( \THE_SEO_FRAMEWORK_SITE_OPTIONS . '-options', '_wpnonce' );

		if ( ! empty( $_POST[ \THE_SEO_FRAMEWORK_SITE_OPTIONS ]['tsf-settings-reset'] ) ) {
			static::process_settings_reset();
		} else {
			static::process_settings_submission();
		}
	}

	/**
	 * Resets options on request.
	 *
	 * @since 5.0.0
	 * @since 5.1.0 1. Now differentiates the options independently of the order.
	 *              2. Now resets options regardless of whether settings are changed from defaults.
	 *
	 * @return void Early if not on SEO settings page.
	 */
	private static function process_settings_reset() {

		if ( Arrays::array_diff_assoc_recursive( Data\Plugin::get_options(), Data\Plugin\Setup::get_default_options() ) ) {
			// Settings are different from default, try resetting.
			$state = Data\Plugin\Setup::reset_options() ? 'reset' : 'error';
		} else {
			// Proceed user intent thoroughly. Reset anyway yet say nothing's changed.
			Data\Plugin\Setup::reset_options();
			$state = 'unchanged';
		}

		Sitemap\Registry::refresh_sitemaps();
		Query\Exclusion::clear_excluded_post_ids_cache();

		Data\Plugin::update_site_cache( 'settings_notice', $state );

		// We're still processing the update; redirect to volitilize all triggers.
		Admin\Utils::redirect( \THE_SEO_FRAMEWORK_SITE_OPTIONS_SLUG );
	}

	/**
	 * Handles settings field update POST actions.
	 *
	 * @since 2.8.0
	 * @since 3.0.6 Now updates db version, too.
	 * @since 3.1.0 Now always flushes the cache, even before the options are updated.
	 * @since 4.1.0 1. Renamed from 'handle_update_post' to 'process_settings_submission'
	 *              2. Is now a protected method.
	 *
	 * @return void Early if nonce failed.
	 */
	private static function process_settings_submission() {

		// Delete main cache directly, for when the options don't change. Don't invoke actions.
		Sitemap\Cache::clear_sitemap_caches();
		Query\Exclusion::clear_excluded_post_ids_cache();

		// Set backward compatibility. This runs after the sanitization.
		/* // phpcs:ignore -- Nothing to set backward compat for, still in place because API is enforced.
		\add_filter(
			'pre_update_option_' . \THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[ static::class, 'set_backward_compatibility' ],
		);
		*/

		// This submission uses WordPress built-in option stores, bypassing Data\Plugin. Hence, we flush:
		\add_action(
			'update_option_' . \THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[ Data\Plugin::class, 'flush_cache' ],
			0
		);

		// Sets that the options are unchanged, preemptively.
		Data\Plugin::update_site_cache( 'settings_notice', 'unchanged' );
		// But, if this action fires, we can assure that the settings have been changed (according to WP).
		// WordPress resorts the settings array; so, right after a save, we do claim that the settings are updated.
		// This is benign.
		\add_action(
			'update_option_' . \THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[ static::class, 'set_option_updated_notice' ],
		);

		\add_action(
			'update_option_' . \THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[ static::class, 'update_db_version' ],
			12,
		);

		\add_action(
			'update_option_' . \THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[ Sitemap\Registry::class, 'refresh_sitemaps' ],
		);
		// Mitigate race condition. If options change affecting the "excluded post", repopulate it.
		\add_action(
			'update_option_' . \THE_SEO_FRAMEWORK_SITE_OPTIONS,
			[ Query\Exclusion::class, 'clear_excluded_post_ids_cache' ],
		);
	}

	/**
	 * Sets the settings notice cache to "updated".
	 *
	 * @hook "update_option_ . THE_SEO_FRAMEWORK_SITE_OPTIONS" 10
	 * @since 4.0.0
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `_set_option_updated_notice`.
	 */
	public static function set_option_updated_notice() {
		Data\Plugin::update_site_cache( 'settings_notice', 'updated' );
	}

	/**
	 * Updates the database version to the defined one.
	 *
	 * This prevents errors when users go back to an earlier version, where options
	 * might be different from a future (or past, since v4.1.0) one.
	 *
	 * @hook "update_option_ . THE_SEO_FRAMEWORK_SITE_OPTIONS" 12
	 * @since 3.0.6
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 */
	public static function update_db_version() {
		\update_option( 'the_seo_framework_upgraded_db_version', \THE_SEO_FRAMEWORK_DB_VERSION, true );
	}

	/**
	 * Maintains backward compatibility for older, migrated options, by injecting them
	 * into the options array before that's processed for updating.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 Emptied and is no longer enqueued.
	 * @since 4.1.0 1. Added taxonomical robots options backward compat.
	 *              2. Added the first two parameters.
	 * @since 4.2.5 Emptied and is no longer enqueued.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `_set_backward_compatibility`.
	 *
	 * @param mixed $options The new, unserialized option values.
	 * @return mixed $options The new, unserialized option values.
	 */
	public static function set_backward_compatibility( $options ) {
		// db_4103:
		// end:;
		return $options;
	}
}
