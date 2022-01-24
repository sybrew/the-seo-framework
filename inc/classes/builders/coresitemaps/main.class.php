<?php
/**
 * @package The_SEO_Framework\Builders\Core_Sitemaps
 * @subpackage The_SEO_Framework\Classes\Builders\Sitemap
 */

namespace The_SEO_Framework\Builders\CoreSitemaps;

/**
 * The SEO Framework plugin
 * Copyright (C) 2020 - 2022 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * Augments the WordPress Core sitemap.
 *
 * @since 4.1.2
 *
 * @access private
 */
class Main extends \The_SEO_Framework\Builders\Sitemap\Main {

	/**
	 * @since 4.1.2
	 * @var \The_SEO_Framework\Builders\Sitemap\Main
	 */
	private static $instance;

	/**
	 * Returns this instance.
	 *
	 * @since 4.1.2
	 *
	 * @return \The_SEO_Framework\Builders\Sitemap\Main $instance
	 */
	public static function get_instance() {
		return static::$instance ?: ( static::$instance = new static );
	}

	/**
	 * Generate sitemap.xml content.
	 *
	 * @since 4.1.2
	 * @abstract
	 * @ignore
	 *
	 * @return string The sitemap content.
	 */
	public function build_sitemap() {
		return '';
	}

	/**
	 * Filters Core sitemap provider.
	 *
	 * @since 4.1.2
	 * @access private
	 *
	 * @param \WP_Sitemaps_Provider $provider Instance of a \WP_Sitemaps_Provider.
	 * @param string                $name     Name of the sitemap provider.
	 * @return \WP_Sitemaps_Provider|null The original or augmented instance of a \WP_Sitemaps_Provider.
	 *                                    null if the provider is disabled.
	 */
	public static function _filter_add_provider( $provider, $name ) {

		if ( ! $provider instanceof \WP_Sitemaps_Provider )
			return $provider;

		switch ( $name ) {
			case 'posts':
				$provider = new Posts;
				break;
			case 'taxonomies':
				$provider = new Taxonomies;
				break;
			case 'users':
				// This option is not reversible through means other than filters.
				// static::$tsf isn't set, because static doesn't require instantiation here.
				if ( \tsf()->get_option( 'author_noindex' ) )
					$provider = null;
				break;
			default:
				break;
		}

		return $provider;
	}

	/**
	 * Filters Core sitemap query limit.
	 *
	 * @since 4.1.2
	 * @access private
	 *
	 * @return string The sitemap query limit.
	 */
	public static function _filter_max_urls() {
		return static::get_instance()->get_sitemap_post_limit();
	}
}
