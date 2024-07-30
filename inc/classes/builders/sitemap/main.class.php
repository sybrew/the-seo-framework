<?php
/**
 * @package The_SEO_Framework\Classes\Builders\Sitemap
 * @subpackage The_SEO_Framework\Sitemap
 */

namespace The_SEO_Framework\Builders\Sitemap;

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

\tsf()->_deprecated_function( 'The_SEO_Framework\Builders\Sitemap\Main', '5.0.0', 'The_SEO_Framework\Sitemap\Optimized\Main' );
/**
 * Generates the sitemap.
 *
 * @since 4.0.0
 * @since 4.2.0 Renamed from `The_SEO_Framework\Builders\Sitemap`
 * @since 5.0.0 Deprecated.
 * @deprecated
 * @access protected
 */
abstract class Main extends \The_SEO_Framework\Sitemap\Optimized\Main {

	/**
	 * @deprecated
	 * @var null|\The_SEO_Framework\Load
	 */
	protected static $tsf;

	/**
	 * Constructor.
	 *
	 * @since 4.0.0
	 * @deprecated
	 */
	final public function __construct() {
		// Fill deprecated property.
		static::$tsf = \tsf();
	}
}
