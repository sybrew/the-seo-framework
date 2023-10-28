<?php
/**
 * @package The_SEO_Framework
 * @subpackage The_SEO_Framework\Bootstrap
 */

namespace The_SEO_Framework;

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

// Ping searchengines.
if ( Data\Plugin::get_option( 'ping_use_cron' ) ) {
	if (
		   Data\Plugin::get_option( 'sitemaps_output' )
		&& Data\Plugin::get_option( 'ping_use_cron_prerender' )
	) {
		\add_action(
			'tsf_sitemap_cron_hook_before',
			[ Sitemap\Optimized\Base::class, 'prerender_sitemap' ],
		);
	}

	\add_action( 'tsf_sitemap_cron_hook', [ Sitemap\Ping::class, 'ping_search_engines' ] );
	\add_action( 'tsf_sitemap_cron_hook_retry', [ Sitemap\Ping::class, 'retry_ping_search_engines' ] );
}
