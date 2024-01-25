<?php
/**
 * @package The_SEO_Framework\Classes\Bridges\Sitemap
 * @subpackage The_SEO_Framework\Sitemap
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

\tsf()->_deprecated_function( 'The_SEO_Framework\Bridges\Sitemap', '5.0.0', 'The_SEO_Framework\Sitemap\Registry' );
/**
 * Prepares sitemap output.
 *
 * @since 4.0.0
 * @since 5.0.0 1. Moved to \The_SEO_Framework\Sitemap\Registry
 *              2. Deprecated.
 * @deprecated
 * @ignore
 * @access protected
 */
class Sitemap extends \The_SEO_Framework\Sitemap\Registry {

	/**
	 * @since 4.0.0
	 * @var \The_SEO_Framework\Sitemap\Registry
	 */
	private static $instance;

	/**
	 * Returns this instance.
	 *
	 * @since 4.0.0
	 *
	 * @return \The_SEO_Framework\Sitemap\Registry $instance
	 */
	public static function get_instance() {
		return static::$instance ??= new static;
	}

	/**
	 * Prepares the class and loads constructor.
	 *
	 * Use this if the actions need to be registered early, but nothing else of
	 * this class is needed yet.
	 *
	 * @since 4.0.0
	 */
	public static function prepare() {
		static::get_instance();
	}

	/**
	 * Deprecation handler for Extension Manager.
	 *
	 * @since 5.0.0
	 * @access private
	 *
	 * @param string $name      The method name.
	 * @param array  $arguments The method arguments.
	 * @return mixed|void
	 */
	public function __call( $name, $arguments ) { // phpcs:ignore -- spl interface

		switch ( $name ) {
			case 'sitemap_cache_enabled':
				return \The_SEO_Framework\Sitemap\Cache::is_sitemap_cache_enabled();
		}

		return null;
	}
}
