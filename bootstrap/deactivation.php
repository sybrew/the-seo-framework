<?php
/**
 * @package The_SEO_Framework/Bootstrap\Install
 */

namespace The_SEO_Framework\Bootstrap;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

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

$tsf = \tsf();

if ( ! $tsf->loaded ) return;

turn_off_autoloading: {
	$options = $tsf->get_all_options();
	$setting = \THE_SEO_FRAMEWORK_SITE_OPTIONS;

	\remove_all_filters( "pre_update_option_{$setting}" );
	\remove_all_actions( "update_option_{$setting}" );
	\remove_all_filters( "sanitize_option_{$setting}" );

	$temp_options = $options;
	// Write a small difference, so the change will be forwarded to the database.
	if ( \is_array( $temp_options ) )
		$temp_options['update_buster'] = (int) time();

	$_success = \update_option( $setting, $temp_options, 'no' );
	if ( $_success )
		\update_option( $setting, $options, 'no' );
}
