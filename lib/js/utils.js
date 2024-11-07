/**
 * This file holds Utils' code for various tasks.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link https://wordpress.org/plugins/autodescription/
 */

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

'use strict';

/**
 * Holds tsfUtils values in an object to avoid polluting global namespace.
 *
 * @since 5.1.0
 *
 * @constructor
 */
window.tsfUtils = function () {

	/**
	 * Debounces the input function.
	 *
	 * @since 5.1.0
	 * @access private
	 *
	 * @param {CallableFunction} func
	 * @param {Int} timeout
	 * @return {Function}
	 */
	function debounce( func, timeout = 0 ) {
		let timeoutId;
		return ( ...args ) => {
			clearTimeout( timeoutId );
			return {
				timeoutId: timeoutId = setTimeout( () => func( ...args ), timeout ),
				cancel: () => clearTimeout( timeoutId ),
			};
		};
	}

	/**
	 * Delays script execution. The caller must be asynchronous.
	 *
	 * @since 5.1.0
	 * @access private
	 *
	 * @param {Int} ms The milliseconds to delay script execution.
	 * @return {Promise}
	 */
	function delay( ms ) {
		return new Promise( resolve => setTimeout( resolve, ms ) );
	}

	return {
		debounce,
		delay,
	};
}();
