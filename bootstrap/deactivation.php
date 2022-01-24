<?php
/**
 * @package The_SEO_Framework/Bootstrap\Install
 */

namespace The_SEO_Framework\Bootstrap;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2022 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * @since 3.2.4 Applied namspacing to this file. All method names have changed.
 */

//! @php7+ convert to IIFE
// phpcs:ignore, TSF.Performance.Opcodes.ShouldHaveNamespaceEscape
_deactivation_unset_options_autoload();

/**
 * Turns off autoloading for The SEO Framework main options.
 *
 * @since 2.9.2
 * @since 3.1.0 No longer deletes the whole option array, trying to reactivate auto loading.
 * @access private
 */
function _deactivation_unset_options_autoload() {

	$the_seo_framework = \tsf();

	if ( $the_seo_framework->loaded ) {
		$options = $the_seo_framework->get_all_options();
		$setting = THE_SEO_FRAMEWORK_SITE_OPTIONS;

		\remove_all_filters( "pre_update_option_{$setting}" );
		\remove_all_actions( "update_option_{$setting}" );
		\remove_all_filters( "sanitize_option_{$setting}" );

		$temp_options = $options;
		//? Write a small difference, so the change will be forwarded to the database.
		if ( \is_array( $temp_options ) )
			$temp_options['update_buster'] = (int) time();

		$_success = \update_option( $setting, $temp_options, 'no' );
		if ( $_success )
			\update_option( $setting, $options, 'no' );
	}
}
