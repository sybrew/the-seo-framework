<?php
/**
 * @package The_SEO_Framework\API
 * @subpackage The_SEO_Framework\Debug\Deprecated
 */

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

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * This file contains most functions that have been deprecated.
 * We don't want to rush removing these, as missing functions cause fatal errors.
 *
 * @ignore
 *
 * @since 2.1.6
 * @since 2.3.5 Emptied. (~2.5 months later)
 * @since 2.6.2 Emptied. (~6 months later)
 * @since 2.9.2 Emptied. (~8 months later)
 * @TODO 5.0.0 empty this.
 */

/**
 * Checks if The SEO FrameWork is active based on filter.
 *
 * @since 2.2.5
 * @since 3.1.0 Deprecated
 * @deprecated
 *
 * @return bool true if SEO framework is active
 */
function the_seo_framework_active() {
	the_seo_framework()->_deprecated_function( __FUNCTION__, '3.1.0', 'the_seo_framework()->loaded' );
	return the_seo_framework()->loaded;
}

/**
 * Returns The SEO FrameWork version number.
 * Useful for version comparing.
 *
 * @since 2.2.5
 * @since 3.1.0 Deprecated
 * @deprecated
 *
 * @return string|null The SEO Framework three point version number. (e.g. '2.2.5')
 */
function the_seo_framework_version() {

	the_seo_framework()->_deprecated_function( __FUNCTION__, '3.1.0', 'THE_SEO_FRAMEWORK_VERSION' );

	if ( the_seo_framework()->loaded )
		return THE_SEO_FRAMEWORK_VERSION;

	return null;
}

/**
 * Compares The SEO Framework dot versions.
 *
 * @since 2.4.0
 * @since 3.1.0 Deprecated
 * @deprecated
 *
 * @param string $version The two dot version: x.v
 * @return bool False plugin inactive or version compare yields negative results.
 */
function the_seo_framework_dot_version( $version = '2.4' ) {
	the_seo_framework()->_deprecated_function( __FUNCTION__, '3.1.0' );

	$current_version = the_seo_framework_version();

	if ( $current_version ) {
		$version_len         = strlen( $version );
		$current_version_len = strlen( $current_version );

		// Only allow 3 length.
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
 * @since 3.1.0 Deprecated
 * @deprecated
 *
 * @return string|null The pagehook.
 */
function the_seo_framework_options_pagehook() {
	the_seo_framework()->_deprecated_function( __FUNCTION__, '3.1.0', 'the_seo_framework()->pagehook' );
	return the_seo_framework()->pagehook;
}

/**
 * Fetch an option from The SEO Framework.
 *
 * @since 2.7.0
 * @since 2.8.0 Now works as intended, by including the required parameters.
 * @since 3.1.0 Deprecated
 * @deprecated
 *
 * @param string  $key Option name.
 * @param boolean $use_cache Optional. Whether to use the cache value or not. Defaults to true.
 * @return mixed The option value.
 */
function the_seo_framework_get_option( $key, $use_cache = true ) {
	the_seo_framework()->_deprecated_function( __FUNCTION__, '3.1.0', 'the_seo_framework()->get_option( \'option_key\' )' );
	return the_seo_framework()->get_option( $key, $use_cache );
}

/**
 * Fetch title from cache. Only works within Loop.
 *
 * @since 2.4.2
 * @since 3.1.0 Deprecated
 * @deprecated
 *
 * @param string|null $title the previous title
 * @return string|null The current page title.
 */
function the_seo_framework_title_from_cache( $title = null ) { // phpcs:ignore,VariableAnalysis -- deprecated
	the_seo_framework()->_deprecated_function( __FUNCTION__, '3.1.0', 'the_seo_framework()->get_title(...)' );
	return the_seo_framework()->get_title();
}

/**
 * Fetch description from cache. Only works within Loop.
 *
 * @since 2.4.2
 * @deprecated
 * @since 3.0.6 Silently deprecated.
 * @since 3.1.0 Deprecated
 * @deprecated
 *
 * @param bool $deprecated Deprecated.
 * @return string|null The current page description.
 */
function the_seo_framework_description_from_cache( $deprecated = false ) { // phpcs:ignore,VariableAnalysis -- deprecated
	the_seo_framework()->_deprecated_function( __FUNCTION__, '3.1.0', 'the_seo_framework()->get_description()' );
	return the_seo_framework()->get_description();
}

/**
 * Fetch url from cache. Only works within Loop.
 *
 * @since 2.4.2
 * @since 3.1.0 Deprecated
 * @deprecated
 *
 * @return string|null The current page URL.
 */
function the_seo_framework_the_url_from_cache() {
	the_seo_framework()->_deprecated_function( __FUNCTION__, '3.1.0', 'the_seo_framework()->get_current_canonical_url()' );
	return the_seo_framework()->get_current_canonical_url();
}

/**
 * Whether we're on the SEO settings page.
 *
 * @since 2.6.0
 * @since 2.7.0 No longer checks for $_GET requests. Only uses global $pagehook.
 * @since 3.1.0 Deprecated
 * @deprecated
 *
 * @return bool
 */
function the_seo_framework_is_settings_page() {
	the_seo_framework()->_deprecated_function( __FUNCTION__, '3.1.0', 'the_seo_framework()->is_seo_settings_page()' );
	return the_seo_framework()->is_seo_settings_page( true );
}

/**
 * Updates The SEO Framework site options.
 *
 * @since 2.7.0
 * @since 3.1.0 Deprecated
 * @deprecated
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
	the_seo_framework()->_deprecated_function( __FUNCTION__, '3.1.0', 'the_seo_framework()->update_settings()' );
	return the_seo_framework()->update_settings( $new_option );
}

/**
 * Returns the parent slug name of The SEO Framework plugin.
 *
 * @since 2.7.0
 * @since 3.1.0 Deprecated
 * @deprecated
 *
 * @return bool|string False on failure, the slug on success.
 */
function the_seo_framework_options_page_slug() {
	the_seo_framework()->_deprecated_function( __FUNCTION__, '3.1.0', 'the_seo_framework()->seo_settings_page_slug' );
	return the_seo_framework()->seo_settings_page_slug;
}
