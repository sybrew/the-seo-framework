/**
 * This file holds The SEO Framework plugin's JS code for WordPress List Edit adjustments.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/autodescription/>
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Holds tsfLe values in an object to avoid polluting global namespace.
 *
 * @since 3.3.0
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfLe = function( $ ) {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 3.3.0
	 * @access public
	 * @type {(Object<string, *>)|boolean|null} l10n Localized strings
	 */
	const l10n = 'undefined' !== typeof tsfLeL10n && tsfLeL10n;

	let updateTimeout;
	/**
	 * Runs after a list edit item has been updated.
	 *
	 * @since 3.3.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _updated = () => {

		clearTimeout( updateTimeout );

		updateTimeout = setTimeout( () => {
			tsfTT.triggerReset();
		}, 50 ); // 20fps
	}

	/**
	 * Initializes AYS scripts on ready.
	 *
	 * @since 3.3.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _setListeners = () => {
		document.addEventListener( 'tsfLeUpdated', _updated );
	}

	return Object.assign( {
		/**
		 * Initialises all aspects of the scripts.
		 * You shouldn't call this.
		 *
		 * @since 3.3.0
		 * @access protected
		 *
		 * @function
		 * @return {undefined}
		 */
		load: () => {
			$( document.body ).ready( _setListeners );
		}
	}, {}, {
		l10n
	} );
}( jQuery );
jQuery( window.tsfLe.load );
