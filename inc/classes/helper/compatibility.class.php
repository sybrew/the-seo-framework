<?php
/**
 * @package The_SEO_Framework\Classes\Helper\Compatibility
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework\Helper;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds a collection of helper methods for plugin compatibility.
 *
 * @since 4.3.0
 * @access private
 */
class Compatibility {

	/**
	 * Registers plugin cache checks on plugin activation.
	 *
	 * @since 4.3.0
	 */
	public static function try_plugin_conflict_notification() {

		if ( \tsf()->detect_seo_plugins() ) {
			\tsf()->register_dismissible_persistent_notice(
				\__( 'Multiple SEO tools have been detected. You should only use one.', 'autodescription' ),
				'seo-plugin-conflict',
				[ 'type' => 'warning' ],
				[
					'screens'    => [ 'edit', 'edit-tags', 'dashboard', 'plugins', 'toplevel_page_theseoframework-settings' ],
					'capability' => 'activate_plugins',
					'count'      => 3,
					'timeout'    => -1, // indefinitely, AIOSEO could be installed by Awesome Motive without admin consent.
				]
			);
		}
	}
}
