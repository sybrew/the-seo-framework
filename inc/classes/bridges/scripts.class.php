<?php
/**
 * @package The_SEO_Framework\Classes\Bridges\Scripts
 * @subpackage The_SEO_Framework\Scripts
 */

namespace The_SEO_Framework\Bridges;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

\tsf()->_deprecated_function( 'The_SEO_Framework\Bridges\Scripts', '5.0.0', 'The_SEO_Framework\Admin\Script\Loader' );
/**
 * Prepares admin GUI scripts. Auto-invokes everything the moment this file is required.
 *
 * @since 4.0.0
 * @since 5.0.0 1. Moved to \The_SEO_Framework\Admin\Script
 *              2. Deprecated.
 * @deprecated
 * @ignore
 * @access protected
 */
class Scripts extends \The_SEO_Framework\Admin\Script\Loader {
	/**
	 * Prepares the class and loads constructor.
	 *
	 * Use this if the actions need to be registered early, but nothing else of
	 * this class is needed yet.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Deprecated.
	 * @ignore
	 * @deprecated
	 */
	public static function prepare() {}
}
