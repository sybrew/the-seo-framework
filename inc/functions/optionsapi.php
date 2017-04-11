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
 * This file holds functions for easily extracting or interacting with data
 * from The SEO FrameWork.
 *
 * @since 2.2.5
 */

/**
 * Loads the class from cache.
 * This is recommended using this above using 'new The_SEO_Framework_Load();'
 * It also checks if the class is callable in the first place.
 *
 * @since 2.2.5
 *
 * @return object The SEO Framework Facade class.
 */
function the_seo_framework() {
	return \The_SEO_Framework\_init();
}

/**
 * Returns the facade class name from cache.
 *
 * CAUTION: If this is used before plugins_loaded priority 5, then the plugin
 * will fail to load views.
 *
 * @since 2.7.0
 * @since 2.8.0: Added did_action and current_action check.
 *
 * @return string|bool The SEO Framework class name. False if The SEO Framework isn't loaded.
 */
function the_seo_framework_class() {

	static $class = null;

	if ( isset( $class ) )
		return $class;

	if ( ! ( did_action( 'plugins_loaded' ) || current_action( 'plugins_loaded' ) ) )
		return false;

	return $class = get_class( the_seo_framework() );
}

/**
 * Checks if The SEO FrameWork is active based on filter.
 *
 * @since 2.2.5
 *
 * @return bool true if SEO framework is active
 */
function the_seo_framework_active() {
	return \The_SEO_Framework\_can_load();
}

/**
 * Returns The SEO FrameWork version number.
 * Useful for version comparing.
 *
 * @since 2.2.5
 *
 * @return string|null The SEO Framework three point version number. (e.g. '2.2.5')
 */
function the_seo_framework_version() {

	if ( the_seo_framework_active() )
		return THE_SEO_FRAMEWORK_VERSION;

	return null;
}

/**
 * Compares The SEO Framework dot versions.
 *
 * @since 2.4.0
 *
 * @param string version The two dot version: x.v
 * @return bool False plugin inactive or version compare yields negative results.
 */
function the_seo_framework_dot_version( $version = '2.4' ) {

	$current_version = the_seo_framework_version();

	if ( $current_version ) {
		$version_len = strlen( $version );
		$current_version_len = strlen( $current_version );

		//* Only allow 3 length.
		if ( 3 !== $version_len )
			$version = substr( $version, 0, 3 );

		if ( 3 !== $current_version_len )
			$current_version = substr( $current_version, 0, 3 );

		if ( $current_version === $version )
			return true;
	}

	return false;
}

/**
 * Fetch the The SEO Framework Options pagehook.
 *
 * @since 2.7.0
 *
 * @return string|null The pagehook.
 */
function the_seo_framework_options_pagehook() {

	$theseoframework = the_seo_framework();

	if ( isset( $theseoframework ) )
		return $theseoframework->pagehook;

	return null;
}


/**
 * Fetch an option from The SEO Framework.
 *
 * @since 2.7.0
 * @since 2.8.0 Now works as intended, by including the required parameters.
 *
 * @param string  $key Option name.
 * @param boolean $use_cache Optional. Whether to use the cache value or not. Defaults to true.
 * @return mixed The option value.
 */
function the_seo_framework_get_option( $key, $use_cache = true ) {

	$theseoframework = the_seo_framework();

	if ( isset( $theseoframework ) )
		return $theseoframework->get_option( $key, $use_cache );

	return null;
}

/**
 * Fetch title from cache. Only works within Loop.
 *
 * @since 2.4.2
 *
 * @param string|null $title the previous title
 * @return string|null The current page title.
 */
function the_seo_framework_title_from_cache( $title = null ) {

	$theseoframework = the_seo_framework();

	if ( isset( $theseoframework ) )
		$title = $theseoframework->title_from_cache();

	return $title;
}

/**
 * Fetch description from cache. Only works within Loop.
 *
 * @since 2.4.2
 *
 * @param bool $social Fetch social description.
 * @return string|null The current page description.
 */
function the_seo_framework_description_from_cache( $social = false ) {

	$theseoframework = the_seo_framework();

	if ( isset( $theseoframework ) )
		return $theseoframework->description_from_cache( $social );

	return null;
}

/**
 * Fetch url from cache. Only works within Loop.
 *
 * @since 2.4.2
 *
 * @return string|null The current page URL.
 */
function the_seo_framework_the_url_from_cache() {

	$theseoframework = the_seo_framework();

	if ( isset( $theseoframework ) )
		return $theseoframework->the_url_from_cache();

	return null;
}

/**
 * Whether we're on the SEO settings page.
 *
 * @since 2.6.0
 * @since 2.7.0 No longer checks for $_GET requests. Only uses global $pagehook.
 *
 * @return bool
 */
function the_seo_framework_is_settings_page() {

	$theseoframework = the_seo_framework();

	if ( isset( $theseoframework ) )
		return $theseoframework->is_seo_settings_page( true );

	return false;
}

/**
 * Updates The SEO Framework site options.
 *
 * @since 2.7.0
 *
 * @param string|array $new_option {
 *      if string: The string will act as a key for a new empty string option, e.g. : {
 *           'sitemap_index' becomes ['sitemap_index' => '']
 *      }
 *      if array: The option name(s) and value(s), e.g. : {
 *            ['sitemap_index' => 1]
 *      }
 * }
 * @return bool True on success. False on failure.
 */
function the_seo_framework_update_option( $new_option ) {

	$theseoframework = the_seo_framework();

	if ( isset( $theseoframework ) )
		return $theseoframework->update_settings( $new_option );

	return false;
}

/**
 * Returns the parent slug name of The SEO Framework plugin.
 *
 * @since 2.7.0
 *
 * @return bool|string False on failure, the slug on success.
 */
function the_seo_framework_options_page_slug() {

	$theseoframework = the_seo_framework();

	if ( isset( $theseoframework ) )
		return $theseoframework->seo_settings_page_slug;

	return false;
}
