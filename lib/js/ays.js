/**
 * This file holds The SEO Framework plugin's JS code for Are You Sure notifications.
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
 * Holds tsfAys values in an object to avoid polluting global namespace.
 *
 * @since 4.0.0
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfAys = function( $ ) {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 4.0.0
	 * @access public
	 * @type {(Object<string, *>)|boolean|null} l10n Localized strings
	 */
	const l10n = 'undefined' !== typeof tsfAysL10n && tsfAysL10n;

	/**
	 * Handles the settings state.
	 *
	 * @since 4.0.0
	 * @access private
	 * @type {boolean}
	 */
	let _settingsChanged = false;

	/**
	 * Determines the initialization state.
	 *
	 * @since 4.0.0
	 * @access private
	 * @type {boolean}
	 */
	let _loadedListeners = false;

	/**
	 * Returns changed state.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @function
	 * @return {boolean}
	 */
	const getChangedState = () => _settingsChanged;

	/**
	 * Transforms element-arrays to a simple element query.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @param {(Element|string|Array<number,string>)} elements
	 * @return {string}
	 */
	const _querify = elements => elements instanceof Element ? elements
								: ( Array.isArray( elements ) ? elements.join( ', ' ) : elements );

	/**
	 * Registers changed state.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @function
	 * @return {undefined}
	 */
	const registerChange = () => {
		_settingsChanged = true;
	}

	/**
	 * Deregisters changed state.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @function
	 * @return {undefined}
	 */
	const deregisterChange = () => {
		_settingsChanged = false;
	}

	/**
	 * Resets all listeners, only after initialization.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @function
	 * @return {undefined}
	 */
	const reset = () => {
		if ( _loadedListeners ) {
			deregisterChange();
			reloadDefaultListeners();
		}
	}

	/**
	 * Unloads the change listener, a saving action is expected.
	 * Also tries to reregister it after 1 second, may other scripts interrupt.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @function
	 * @param {!jQuery.event} event The jQuery event
	 * @return {undefined}
	 */
	const _triggerUnload = () => {

		let _previousState = getChangedState();

		deregisterChange();
		setTimeout( () => {
			reset();
			_previousState && registerChange();
		}, 1000 );
	}

	/**
	 * Triggers default change listener.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @function
	 * @param {!jQuery.event} event The jQuery event
	 * @return {undefined}
	 */
	const _triggerChange = event => {
		registerChange();
		event && _exemptFutureChanges( event );
	}

	/**
	 * Exempts listeners to reduce CPU usage.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @param {!jQuery.event} event The jQuery event
	 * @return {undefined}
	 */
	const _exemptFutureChanges = event => {
		$( event.data._input )
			.not( event.data._except )
			.off( event.type, _triggerChange );
	}

	/**
	 * Registers change listener.
	 * Should be used on input elements that may be saved.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @function
	 * @param {(Element|string|Array<number,string>)} elements  The elements to register.
	 * @param {string}                                eventType The event type to listen to.
	 * @return {undefined}
	 */
	const registerChangeListener = ( elements, eventType ) => {
		let except = '.tsf-input-not-saved';

		elements = _querify( elements );

		$( elements ).not( except )
			.off( eventType + '.tsfChangeListener' )
			.on( eventType + '.tsfChangeListener', { _input: elements, _except: except }, _triggerChange );
	}

	/**
	 * Registers reset listener.
	 * Should be used on input elements that save the settings, but doesn't reload the document.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @function
	 * @param {(Element|string|Array<number,string>)} elements  The elements to register.
	 * @param {string}                                eventType The event type to listen to.
	 * @return {undefined}
	 */
	const registerResetListener = ( elements, eventType ) => {
		$( _querify( elements ) )
			.off( eventType + '.tsfChangeListener' )
			.on( eventType + '.tsfChangeListener', reset );
	}

	/**
	 * Registers unload listener.
	 * Should be used on input elements that save the settings, and reloads the document.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @function
	 * @param {(Element|string|Array<number,string>)} elements  The elements to register.
	 * @param {string}                                eventType The event type to listen to.
	 * @return {undefined}
	 */
	const registerUnloadListener = ( elements, eventType ) => {
		$( _querify( elements ) )
			.off( eventType + '.tsfChangeListener' )
			.on( eventType + '.tsfChangeListener', _triggerUnload );
	}

	/**
	 * Registers default listeners.
	 * Also triggers an event, so other developers may consistently add their listeners.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @function
	 * @return {undefined}
	 */
	const reloadDefaultListeners = () => {

		//= Mouse input
		registerChangeListener(
			[
				'.tsf-metaboxes :input',
				'#tsf-inpost-box .inside :input',
				'.tsf-term-meta :input',
			],
			'change'
		);

		//= Text input
		registerChangeListener(
			[
				'.tsf-metaboxes input[type=text]',
				'.tsf-metaboxes textarea',
				'#tsf-inpost-box .inside input[type=text]',
				'#tsf-inpost-box .inside textarea',
				'.tsf-term-meta input[type=text]',
				'.tsf-term-meta textarea',
			],
			'input'
		);

		//= Non-redirect, Gutenberg save action.
		registerResetListener(
			document,
			'tsf-gutenberg-onsave'
		);

		//= Redirect-save actions.
		registerUnloadListener(
			[
				'.tsf-metaboxes input[type="submit"]',
				'#publishing-action input[type="submit"]',
				'#save-action input[type="submit"]',
				'a.submitdelete',
				'.edit-tag-actions input[type="submit"]',
				'.edit-tag-actions .delete',
			],
			'click'
		);

		$( document ).trigger( 'tsf-registered-ays-listeners' );

		_loadedListeners = true;
	}

	/**
	 * Initializes AYS scripts on TSF-ready.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _readyAys = () => {
		// Initialise form field changing flag.
		reloadDefaultListeners();

		getChangedState() && console.log( 'tsfAys: Doing it wrong! Settings were changed prior ready-state. Debug me.' );
		// Undo accidental load-sequence state changes.
		deregisterChange();

		//= Alert onbeforeunload
		$( window )
			.on( 'beforeunload.tsfChangeListener', () => {
				if ( getChangedState() ) {
					return l10n.i18n['saveAlert'];
				}
			} );
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
			$( document.body ).on( 'tsf-ready', _readyAys );
		}
	}, {
		reset,
		getChangedState,
		registerChange,
		deregisterChange,
		registerChangeListener,
		registerResetListener,
		registerUnloadListener,
		reloadDefaultListeners
	}, {
		l10n
	} );
}( jQuery );
jQuery( window.tsfAys.load );
