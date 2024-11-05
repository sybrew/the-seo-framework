<?php
/**
 * @package The_SEO_Framework\Classes\Helper\Format\Time
 * @subpackage The_SEO_Framework\Formatting
 */

namespace The_SEO_Framework\Helper\Format;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\umemo;

use \The_SEO_Framework\Data;

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
 * Holds methods for Time interpretation and conversion.
 *
 * @since 5.0.0
 * @NOTE to self: This is also used in XHTML configurations. Keep it strict!
 *
 * @access protected
 *         Use tsf()->format()->time() instead.
 */
class Time {

	/**
	 * Converts the input time to the preferred format.
	 *
	 * @since 5.0.0
	 *
	 * @param int|string $time The timestamp. Expected to be GMT/UTC.
	 * @return string The time in correct format. An empty string on failure.
	 */
	public static function convert_to_preferred_format( $time ) {

		if ( empty( $time ) || '0000-00-00 00:00:00' === $time )
			return '';

		if ( is_numeric( $time ) )
			return gmdate( static::get_preferred_format(), (int) $time );

		// Try to create from date; on success, format it. This way we won't produce errors.
		$value = $time ? date_create_from_format( 'Y-m-d H:i:s', $time ) : '';

		return $value ? date_format( $value, static::get_preferred_format() ) : '';
	}

	/**
	 * Returns timestamp format based on timestamp settings.
	 * Note that this must be XML safe.
	 *
	 * @since 5.0.0
	 * @since 5.0.5 Added seconds to the full timestamp format.
	 * @link https://www.w3.org/TR/NOTE-datetime
	 *
	 * @return string The timestamp format used in PHP date.
	 */
	public static function get_preferred_format() {
		return umemo( __METHOD__ )
			?? umemo(
				__METHOD__,
				static::get_format(
					(bool) Data\Plugin::get_option( 'timestamps_format' )
				),
			);
	}

	/**
	 * Returns timestamp format based on timestamp settings.
	 * Note that this must be XML safe.
	 *
	 * @since 5.0.5
	 * @link https://www.w3.org/TR/NOTE-datetime
	 *
	 * @param bool $get_time False to get only the date, true to get the date + time.
	 * @return string The timestamp format used in PHP date.
	 */
	public static function get_format( $get_time ) {
		/**
		 * @see For valid formats https://www.w3.org/TR/NOTE-datetime.
		 * @since 4.1.4
		 * @param string The full timestamp format. Must be XML safe and in ISO 8601 datetime notation.
		 * @param bool   True if time is requested, false if only date.
		 */
		return (string) \apply_filters(
			'the_seo_framework_timestamp_format',
			$get_time ? 'Y-m-d\TH:i:sP' : 'Y-m-d', // Could use 'c', but that specification is ambiguous
			$get_time,
		);
	}
}
