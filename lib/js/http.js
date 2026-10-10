/**
 * This file holds HTTP helpers for The SEO Framework.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link https://wordpress.org/plugins/autodescription/
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2026 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

'use strict';

/**
 * Holds tsfHTTP values in an object to avoid polluting global namespace.
 *
 * @since 5.2.0
 *
 * @constructor
 */
window.tsfHTTP = function () {

	/**
	 * Parses a URL, assuming https when the scheme is omitted.
	 *
	 * @since 5.2.0
	 * @access public
	 *
	 * @param {string} raw
	 * @return {?URL} The parsed URL.
	 */
	function parseUrl( raw ) {
		try {
			return new URL( raw );
		} catch ( error ) {
			try {
				return new URL( `https://${raw}` );
			} catch ( error ) {
				return null;
			}
		}
	}

	/**
	 * @since 5.2.0
	 * @access public
	 *
	 * @param {URL} url
	 * @return {boolean}
	 */
	function isHttp( url ) {
		return 'http:' === url.protocol || 'https:' === url.protocol;
	}

	return {
		parseUrl,
		isHttp,
	};
}();
