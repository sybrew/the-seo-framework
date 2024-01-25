<?php
/**
 * @package The_SEO_Framework\Traits\Property_Refresher
 * @subpackage The_SEO_Framework\Data
 */

namespace The_SEO_Framework\Traits;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\has_run;

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

/**
 * Trait The_SEO_Framework\Traits\Property_Refresher
 *
 * Creates functionality to flush properties.
 * Handy when dealing with multisite, where class properties need to be flushed
 * when blogs are switched.
 *
 * @since 5.0.0
 * @access public
 */
trait Property_Refresher {

	/**
	 * @since 5.0.0
	 * @var array A list of properties marked for refresh.
	 */
	protected static $marked_for_refresh = [];

	/**
	 * Registers automated refreshes.
	 *
	 * @since 5.0.0
	 * @param string $property The property that's marked for refresh.
	 */
	protected static function register_automated_refresh( $property ) {

		static::$marked_for_refresh[ $property ] = 0b1;

		if ( has_run( __CLASS__ . __METHOD__ ) ) return;

		\add_action( 'switch_blog', [ __CLASS__, '_do_switch_blog_flush' ], 10, 2 );
	}

	/**
	 * Refreshes all static properties.
	 *
	 * @hook switch_blog 10
	 * @since 5.0.0
	 *
	 * @param int $new_site_id New site ID.
	 * @param int $old_site_id Old site ID.
	 */
	public static function _do_switch_blog_flush( $new_site_id, $old_site_id ) {

		if ( $new_site_id === $old_site_id ) return;

		static::refresh_static_properties();
	}

	/**
	 * Refreshes all static properties.
	 *
	 * @since 5.0.0
	 */
	public static function refresh_static_properties() {
		foreach ( get_class_vars( __CLASS__ ) as $key => $initial )
			if ( isset( static::$marked_for_refresh[ $key ], static::${$key} ) )
				static::${$key} = $initial;
	}
}
