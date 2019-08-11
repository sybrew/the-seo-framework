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
 * @since 4.0.0
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfLe = function( $ ) {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 4.0.0
	 * @access public
	 * @type {(Object<string, *>)|boolean|null} l10n Localized strings
	 */
	const l10n = 'undefined' !== typeof tsfLeL10n && tsfLeL10n;

	let updateTimeout;
	/**
	 * Runs after a list edit item has been updated.
	 *
	 * @since 4.0.0
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
	 * Sets inline post values for quick-edit.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @param {string|HTMLElement} id
	 */
	const _setInlinePostValues = id => {

		if ( typeof( id ) === 'object' )
			id = window.inlineEditPost.getId( id );

		if ( ! id ) return;

		let dataElement = document.getElementById( 'tsfLeData[' + id + ']' ),
			data        = void 0;

		try {
			data = JSON.parse( dataElement.dataset.le ) || void 0;
		} catch( e ) {}

		if ( ! data ) return;

		let element;

		for ( let index in data ) {

			element = document.getElementById( 'autodescription-quick[%s]'.replace( '%s', index ) );
			if ( ! element ) continue;

			switch ( index ) {
				case 'noindex':
				case 'nofollow':
				case 'noarchive':
					tsf.selectByValue( element, data[ index ].value );

					// Do `sprintf( 'Default (%s)', x.default )`.
					let _default = element.querySelector( '[value="0"]' );
					if ( _default )
						_default.innerHTML = _default.innerHTML.replace( '%s', tsf.decodeEntities( data[ index ].default ) );
					break;

				case 'canonical':
				case 'redirect':
					element.value = tsf.decodeEntities( data[ index ].value );
					break;

				default:
					break;
			}
		}
	}

	/**
	 * Sets inline term values for quick-edit.
	 *
	 * Yes, 99% of this code is a copy of _setInlinePostValues()
	 * Left unchanged for the future may create discrepancy.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @param {string|HTMLElement} id
	 */
	const _setInlineTermValues = id => {

		if ( typeof( id ) === 'object' )
			id = window.inlineEditTax.getId( id );

		if ( ! id ) return;

		let dataElement = document.getElementById( 'tsfLeData[' + id + ']' ),
			data        = void 0;

		try {
			data = JSON.parse( dataElement.dataset.le ) || void 0;
		} catch( e ) {}

		if ( ! data ) return;

		let element;

		for ( let index in data ) {

			element = document.getElementById( 'autodescription-quick[%s]'.replace( '%s', index ) );
			if ( ! element ) continue;

			switch ( index ) {
				case 'noindex':
				case 'nofollow':
				case 'noarchive':
					tsf.selectByValue( element, data[ index ].value );

					// Do `sprintf( 'Default (%s)', x.default )`.
					let _default = element.querySelector( '[value="0"]' );
					if ( _default )
						_default.innerHTML = _default.innerHTML.replace( '%s', tsf.decodeEntities( data[ index ].default ) );
					break;

				case 'canonical':
				case 'redirect':
					element.value = tsf.decodeEntities( data[ index ].value );
					break;

				default:
					break;
			}
		}
	}

	/**
	 * Initializes AYS scripts on ready.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _setListeners = () => {
		document.addEventListener( 'tsfLeUpdated', _updated );
	}

	/**
	 * Hijacks the quick and bulk-edit listeners.
	 *
	 * NOTE: The bulk-editor doesn't need adjusting, yet.
	 *       Moreover, the bulk-edit doesn't have a "save" callback, because it's
	 *       not using AJAX to save data.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 */
	const _hijackListeners = () => {

		let _oldInlineEditCallback;

		if ( window.inlineEditPost ) {
			_oldInlineEditCallback = 'edit' in window.inlineEditPost && window.inlineEditPost.edit;

			if ( _oldInlineEditCallback ) {
				window.inlineEditPost.edit = function( id ) {
					let ret = _oldInlineEditCallback.apply( this, arguments );
					_setInlinePostValues( id );
					return ret;
				}
			}
		}

		if ( window.inlineEditTax ) {
			_oldInlineEditCallback = 'edit' in window.inlineEditTax && window.inlineEditTax.edit;

			if ( _oldInlineEditCallback ) {
				window.inlineEditTax.edit = function( id ) {
					let ret = _oldInlineEditCallback.apply( this, arguments );
					_setInlineTermValues( id );
					return ret;
				}
			}
		}
	}

	return Object.assign( {
		/**
		 * Initialises all aspects of the scripts.
		 * You shouldn't call this.
		 *
		 * @since 4.0.0
		 * @access protected
		 *
		 * @function
		 * @return {undefined}
		 */
		load: () => {
			$( document.body ).ready( _setListeners );
			$( document.body ).ready( _hijackListeners );
		}
	}, {}, {
		l10n
	} );
}( jQuery );
jQuery( window.tsfLe.load );
