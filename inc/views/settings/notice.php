<?php
/**
 * @package The_SEO_Framework\Views\Admin
 * @subpackage The_SEO_Framework\Admin\Settings
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) or die;

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

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

$notice = Data\Plugin::get_site_cache( 'settings_notice' );

if ( ! $notice ) return;

$message = '';
$type    = '';

switch ( $notice ) {
	case 'updated':
		$message = \__( 'SEO settings are saved, and the caches have been flushed.', 'autodescription' );
		$type    = 'updated';
		break;

	case 'unchanged':
		$message = \__( 'No SEO settings were changed, but the caches have been flushed.', 'autodescription' );
		$type    = 'info';
		break;

	case 'reset':
		$message = \__( 'SEO settings are reset, and the caches have been flushed.', 'autodescription' );
		$type    = 'warning';
		break;

	case 'error':
		$message = \__( 'An unknown error occurred saving SEO settings.', 'autodescription' );
		$type    = 'error';
}

Data\Plugin::update_site_cache( 'settings_notice', '' );

$message and Admin\Notice::output_notice( $message, [ 'type' => $type ] );
