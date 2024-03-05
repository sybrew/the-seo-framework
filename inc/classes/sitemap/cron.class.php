<?php
/**
 * @package The_SEO_Framework\Classes\Sitemap\Cron
 * @subpackage The_SEO_Framework\Sitemap
 */

namespace The_SEO_Framework\Sitemap;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds sitemap cron functionality.
 *
 * @since 5.0.5
 * @access protected
 *         Use tsf()->sitemap()->cron() instead.
 */
class Cron {

	/**
	 * Prepares a cronjob-based ping within 30 seconds of calling this.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now returns whether the cron engagement was successful.
	 * @since 4.1.2 Now registers before and after cron hooks. They should run subsequentially when successful.
	 * @since 5.0.5 Moved from `The_SEO_Framework\Sitemap\Ping` and renamed from `engage_ping_cron`.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function schedule_single_event() {

		$when = time() + 28;

		// Because WordPress sorts the actions, we can't be sure if they're scrambled. Therefore: skew timing.
		// Note that when WP_CRON_LOCK_TIMEOUT expires, the subsequent actions will run, regardless if previous was successful.
		return \wp_schedule_single_event( ++$when, 'tsf_sitemap_cron_hook_before' )
			&& \wp_schedule_single_event( ++$when, 'tsf_sitemap_cron_hook' )
			&& \wp_schedule_single_event( ++$when, 'tsf_sitemap_cron_hook_after' );
	}
}
