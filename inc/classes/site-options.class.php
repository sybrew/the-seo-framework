<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Site_Options
 * @subpackage The_SEO_Framework\Data
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\is_headless;

use \The_SEO_Framework\Data;
use \The_SEO_Framework\Helper\{
	Post_Types,
	Query,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\Site_Options
 *
 * Handles Site Options for the plugin.
 *
 * @since 2.8.0
 */
class Site_Options extends Sanitize {

	/**
	 * Hold the SEO Settings Page ID for this plugin.
	 *
	 * @since 2.2.2
	 * @since 2.7.0 Renamed var from page_id and made public.
	 *
	 * @var string The page ID
	 */
	public $seo_settings_page_slug = 'theseoframework-settings';

	/**
	 * Register the database settings for storage.
	 *
	 * @since 2.2.2
	 * @since 2.9.0 Removed reset options check, see check_options_reset().
	 * @since 3.1.0 Removed settings field existence check.
	 * @since 4.0.0 Now checks if the option exists before adding it. Shaves 20μs...
	 * @thanks StudioPress (http://www.studiopress.com/) for some code.
	 *
	 * @return void Early if settings can't be registered.
	 */
	public function register_settings() {

		\register_setting( \THE_SEO_FRAMEWORK_SITE_OPTIONS, \THE_SEO_FRAMEWORK_SITE_OPTIONS );
		\get_option( \THE_SEO_FRAMEWORK_SITE_OPTIONS )
			or \add_option( \THE_SEO_FRAMEWORK_SITE_OPTIONS, Data\Plugin\Setup::get_default_options() );

		// Not a public "setting" -- only add the option to prevent additional db-queries when it's yet to be populated.
		\get_option( \THE_SEO_FRAMEWORK_SITE_CACHE )
			or \add_option( \THE_SEO_FRAMEWORK_SITE_CACHE, [] );

		// Check whether the Options Reset initialization has been added.
		$this->check_options_reset();

		// Handle post-update actions. Must be initialized on admin_init and is initialized on options.php.
		if ( 'options.php' === $GLOBALS['pagenow'] )
			$this->process_settings_submission();
	}

	/**
	 * Checks for options reset, and reset them.
	 *
	 * @since 2.9.0
	 *
	 * @return void Early if not on SEO settings page.
	 */
	protected function check_options_reset() {

		// Check if we're already dealing with the settings. Buggy cache might interfere, otherwise.
		if ( ! Query::is_seo_settings_page( false ) || ! \current_user_can( \THE_SEO_FRAMEWORK_SETTINGS_CAP ) )
			return;

		if ( Data\Plugin::get_option( 'tsf-settings-reset' ) ) {
			if ( \update_option( \THE_SEO_FRAMEWORK_SITE_OPTIONS, $this->get_default_site_options() ) ) {
				Data\Plugin::update_site_cache( 'settings_notice', 'reset' );
			} else {
				Data\Plugin::update_site_cache( 'settings_notice', 'error' );
			}
			$this->admin_redirect( $this->seo_settings_page_slug );
			exit;
		}
	}
}
