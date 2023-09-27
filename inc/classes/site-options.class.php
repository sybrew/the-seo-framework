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

	/**
	 * Returns all post type archive meta.
	 *
	 * We do not test whether a post type is supported, for it'll conflict with data-fills on the
	 * SEO settings page. This meta should never get called on the front-end if the post type is
	 * disabled, anyway, for we never query post types externally, aside from the SEO settings page.
	 *
	 * @since 4.2.0
	 *
	 * @param string $post_type The post type.
	 * @param bool   $use_cache Whether to use caching.
	 * @return array The post type archive's meta item's values.
	 */
	public function get_post_type_archive_meta( $post_type, $use_cache = true ) {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( $use_cache && ( $memo = memo( null, $post_type ) ) ) return $memo;

		/**
		 * We can't trust the filter to always contain the expected keys.
		 * However, it may contain more keys than we anticipated. Merge them.
		 */
		$defaults = array_merge(
			$this->get_unfiltered_post_type_archive_meta_defaults(),
			$this->get_post_type_archive_meta_defaults( $post_type )
		);

		// Yes, we abide by "settings". WordPress never gave us Post Type Archive settings-pages.
		$is_headless = is_headless( 'settings' );

		if ( $is_headless ) {
			$meta = [];
		} else {
			// Unlike get_post_meta(), we need not filter here.
			// See: <https://github.com/sybrew/the-seo-framework/issues/185>
			$meta = Data\Plugin::get_option( 'pta', $post_type ) ?: [];
		}

		/**
		 * @since 4.2.0
		 * @note Do not delete/unset/add indexes! It'll cause errors.
		 * @param array $meta      The current post type archive meta.
		 * @param int   $post_type The post type.
		 * @param bool  $headless  Whether the meta are headless.
		 */
		$meta = \apply_filters_ref_array(
			'the_seo_framework_post_type_archive_meta',
			[
				array_merge( $defaults, $meta ),
				$post_type,
				$is_headless,
			]
		);

		// Do not overwrite cache when not requested. Otherwise, we'd have two "initial" states, causing incongruities.
		return $use_cache ? memo( $meta, $post_type ) : $meta;
	}

	/**
	 * Returns a single post type archive item's value.
	 *
	 * @since 4.2.0
	 *
	 * @param string $item      The item to get.
	 * @param string $post_type The post type.
	 * @param bool   $use_cache Whether to use caching.
	 * @return ?mixed The post type archive's meta item value. Null when item isn't registered.
	 */
	public function get_post_type_archive_meta_item( $item, $post_type = '', $use_cache = true ) {
		return $this->get_post_type_archive_meta(
			$post_type ?: Query::get_current_post_type(),
			$use_cache
		)[ $item ] ?? null;
	}

	/**
	 * Returns an array of all public post type archive option defaults.
	 *
	 * @since 4.2.0
	 *
	 * @return array[] The Post Type Archive Metadata default options
	 *                 of all public Post Type archives.
	 */
	public function get_all_post_type_archive_meta_defaults() {

		$defaults = [];

		foreach ( Post_Types::get_public_post_type_archives() as $pta )
			$defaults[ $pta ] = $this->get_post_type_archive_meta_defaults( $pta );

		return $defaults;
	}

	/**
	 * Returns an array of default post type archive meta.
	 *
	 * @since 4.2.0
	 *
	 * @param int $post_type The post type.
	 * @return array The Post Type Archive Metadata default options.
	 */
	public function get_post_type_archive_meta_defaults( $post_type = '' ) {
		/**
		 * @since 4.2.0
		 * @param array $defaults
		 * @param int   $term_id The current term ID.
		 */
		return (array) \apply_filters_ref_array(
			'the_seo_framework_get_post_type_archive_meta_defaults',
			[
				$this->get_unfiltered_post_type_archive_meta_defaults(),
				$post_type ?: Query::get_current_post_type(),
			]
		);
	}

	/**
	 * Returns the unfiltered post type archive meta defaults.
	 *
	 * @since 4.2.0
	 *
	 * @return array The default, unfiltered, post type archive meta.
	 */
	protected function get_unfiltered_post_type_archive_meta_defaults() {
		return [
			'doctitle'           => '',
			'title_no_blog_name' => 0,
			'description'        => '',
			'og_title'           => '',
			'og_description'     => '',
			'tw_title'           => '',
			'tw_description'     => '',
			'social_image_url'   => '',
			'social_image_id'    => 0,
			'canonical'          => '',
			'noindex'            => 0,
			'nofollow'           => 0,
			'noarchive'          => 0,
			'redirect'           => '',
		];
	}
}
