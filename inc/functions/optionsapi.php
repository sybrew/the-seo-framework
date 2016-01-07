<?php
/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * This file holds functions for easily extracting or interacting with data
 * from The SEO FrameWork.
 *
 * @since 2.2.5
 *
 * We could bombard it with every public function, but that's very time consuming.
 * I'll add a bunch of functions on 2nd dot (v.X.v) release. e.g. 2.3.0, 2.4.0, etc.
 *
 * This will allow version comparing more easily (as you'll know how many users
 * use v.X version through the WordPress plugin stats.).
 * Therefore reducing work for you.
 */

/**
 * Load the class from cache.
 * This is recommended using this above using 'new The_SEO_Framework_Load();'
 * It also checks if the class is callable in the first place.
 *
 * @since 2.2.5
 */
function the_seo_framework() {
	return the_seo_framework_init();
}

/**
 * The SEO FrameWork version number
 *
 * Useful for version comparing
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
 * The SEO Framework dot version compare.
 *
 * @param string version The two dot version: x.v
 *
 * @since 2.4.0
 *
 * @return bool False plugin inactive or version compare fails.
 */
function the_seo_framework_dot_version( $version = '2.4' ) {

	$current_version = the_seo_framework_version();

	if ( $current_version ) {
		$version_len = strlen( $version );

		//* Only allow 3 or 5 length.
		if ( $version_len != 3 && $version_len != 5 )
			return false;

		//* If 5 length, chop.
		if ( $version_len == 5 ) {
			$version = (string) substr( $version, 0, -2 );
		} else {
			$version = (string) $version;
		}

		$current_version = (string) substr( $current_version, 0, -2 );

		if ( version_compare( $current_version, $version, '=' ) )
			return true;
	}

	return false;
}

/**
 * Check if The SEO FrameWork is active based on global filter.
 *
 * @since 2.2.5
 *
 * @return bool true if SEO framework is active
 */
function the_seo_framework_active() {
	return the_seo_framework_load();
}

/**
 * Compare the WordPress version to the input one.
 *
 * @since 2.2.9
 *
 * @param string $version The 3 point version compare
 * @param string $compare The PHP comparison operator.
 *
 * @return bool true if Version passes comparison.
 */
function tsf_wp_version( $version = '4.3.0', $compare = '>=' ) {
	$theseoframework = the_seo_framework();

	if ( isset( $theseoframework ) )
		return $theseoframework->wp_version( $version, $compare );

	return null;
}

/**
 * Fetch the The SEO Framework Options pagehook.
 *
 * @since 2.2.9
 *
 * @return string The pagehook.
 */
function tsf_options_pagehook() {
	$theseoframework = the_seo_framework();

	if ( isset( $theseoframework ) )
		return $theseoframework->pagehook;

	return null;
}

/**
 * Fetch an option from The SEO Framework.
 *
 * @since 2.2.9
 *
 * @param string  $key       Option name.
 * @param boolean $use_cache Optional. Whether to use the cache value or not. Defaults to true.
 *
 * @return mixed The option value.
 */
function tsf_get_option( $key, $use_cache = true ) {
	$theseoframework = the_seo_framework();

	if ( isset( $theseoframework ) )
		return $theseoframework->get_option( $key, $use_cache );

	return null;
}

/**
 * Fetch title from cache. Only works within Loop.
 *
 * @param string|null $title the previous title
 *
 * @since 2.4.2
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
 * @param bool $social Fetch social description.
 *
 * @since 2.4.2
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
 */
function the_seo_framework_the_url_from_cache() {
	$theseoframework = the_seo_framework();

	if ( isset( $theseoframework ) )
		return $theseoframework->the_url_from_cache();

	return null;
}
