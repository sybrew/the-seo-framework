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

defined( 'ABSPATH' ) or die;

/**
 * This file contains most functions that have been deprecated.
 *
 * @since 2.1.6
 * @since 2.3.5 Emptied. (~2.5 months later )
 * @since 2.6.2 Emptied. (~6 months later)
 */

/**
 * Fetch an option from The SEO Framework.
 *
 * @since 2.2.9
 *
 * @deprecated
 * @since 2.7.0
 *
 * @param string  $key       Option name.
 * @param boolean $use_cache Optional. Whether to use the cache value or not. Defaults to true.
 * @return mixed The option value.
 */
function tsf_get_option( $key, $use_cache = true ) {

	$theseoframework = the_seo_framework();

	if ( isset( $theseoframework ) )
		$theseoframework->_deprecated_function( __FUNCTION__, '2.7.0', 'the_seo_framework_get_option()' );

	return the_seo_framework_get_option( $key, $use_cache );
}

/**
 * Fetch the The SEO Framework Options pagehook.
 *
 * @since 2.2.9
 *
 * @deprecated
 * @since 2.7.0
 *
 * @return string|null The pagehook.
 */
function tsf_options_pagehook() {

	$theseoframework = the_seo_framework();

	if ( isset( $theseoframework ) )
		$theseoframework->_deprecated_function( __FUNCTION__, '2.7.0', 'the_seo_framework_options_pagehook()' );

	return the_seo_framework_options_pagehook();
}


/**
 * Compare the WordPress version to the input one.
 *
 * @since 2.2.9
 *
 * @deprecated
 * @since 2.7.0
 *
 * @param string $version The 3 point version compare
 * @param string $compare The PHP comparison operator.
 * @return bool true if Version passes comparison.
 */
function tsf_wp_version( $version = '4.3.0', $compare = '>=' ) {

	$theseoframework = the_seo_framework();

	if ( isset( $theseoframework ) ) {
		$theseoframework->_deprecated_function( __FUNCTION__, '2.7.0', 'AutoDescription_Detect::wp_version()' );
		return $theseoframework->wp_version( $version, $compare );
	}

	return null;
}
