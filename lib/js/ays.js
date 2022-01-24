/**
 * This file holds The SEO Framework plugin's JS code for Are You Sure notifications.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/autodescription/>
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2022 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 */
window.tsfAys = function() {

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
	 * Holds the registered change listeners map.
	 *
	 * @since 4.1.1
	 * @access private
	 * @type {Map<Element,string>} string: event Type attached.
	 */
	let _registeredChangeListeners = new Map();

	/**
	 * Returns changed state.
	 *
	 * @since 4.0.0
	 * @todo deprecate
	 * @access public
	 *
	 * @function
	 * @return {boolean}
	 */
	const getChangedState = () => areSettingsChanged();

	/**
	 * Returns changed state.
	 *
	 * @since 4.1.0
	 * @access public
	 *
	 * @function
	 * @return {boolean}
	 */
	const areSettingsChanged = () => _settingsChanged;

	/**
	 * Transforms elements and queries to an array from nodelists.
	 *
	 * @since 4.1.1
	 * @access private
	 *
	 * @function
	 * @param {Element|Document|string|string[]} elements
	 * @return {(Element|Document)[]}
	 */
	const _getNodeArray = elements => ( elements instanceof Element || elements instanceof Document )
		? [ elements ]
		: [ ...document.querySelectorAll( Array.isArray( elements ) ? elements.join( ', ' ) : elements ) ];

	/**
	 * Registers changed state.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @function
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
	 */
	const deregisterChange = () => {
		_settingsChanged = false;
	}

	let _debounceReset = void 0;
	/**
	 * Resets all listeners and deregisters changes.
	 *
	 * Deregistration only happens after first initialization.
	 *
	 * @since 4.0.0
	 * @since 4.1.1 debounced reloading of the listeners.
	 * @access public
	 *
	 * @function
	 */
	const reset = () => {
		deregisterChange();
		if ( _loadedListeners ) {
			clearTimeout( _debounceReset );
			// High timeout. Resets should only happen during failures or changing document states; the latter of which is slow.
			_debounceReset = setTimeout( reloadDefaultListeners, 1e3 );
		}
	}

	/**
	 * Unloads the change listener, a saving action is expected that causes the user to unload the page.
	 * Also tries to reregister it after 1 second, may other scripts interrupt the unloading.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 */
	const _triggerUnload = () => {

		let wereSettingsChanged = areSettingsChanged();

		deregisterChange();

		setTimeout( () => {
			reset();
			wereSettingsChanged && registerChange();
		}, 1000 );
	}

	/**
	 * Triggers registered change listener. Also exempt future change listeners,
	 * for once we registered a change, none is required thereafter.
	 *
	 * @since 4.0.0
	 * @since 4.1.1 Now only passes trusted events.
	 * @access private
	 *
	 * @function
	 * @param {event} event
	 */
	const _triggerChange = event => {
		if ( ! event.isTrusted ) return;
		registerChange();
		_exemptFutureChanges();
	}

	/**
	 * Exempts listeners to reduce CPU usage.
	 *
	 * @since 4.0.0
	 * @since 4.1.1 Now defuncts all registered change listers without relying on event details.
	 * @access private
	 *
	 * @function
	 */
	const _exemptFutureChanges = () => {
		_registeredChangeListeners.forEach( ( eventType, element ) => {
			element.removeEventListener( eventType, _triggerChange );
		} );
		_registeredChangeListeners.clear();
	}

	/**
	 * Registers change listener.
	 * Should be used on input elements that may be saved.
	 *
	 * @since 4.0.0
	 * @since 4.1.1 1. No longer uses jQuery for the events.
	 *              2. Now filters the exceptions in place.
	 * @access public
	 *
	 * @function
	 * @param {(Element|string|string[])} elements  The elements to register.
	 * @param {string}                    eventType The event type to listen to.
	 */
	const registerChangeListener = ( elements, eventType ) => {
		_getNodeArray( elements )
			.filter( el => ! el.classList.contains( 'tsf-input-not-saved' ) && 'hidden' !== el.type )
			.forEach( el => {
				_registeredChangeListeners.set( el, eventType );
				el.addEventListener( eventType, _triggerChange )
			} );
	}

	/**
	 * Registers force reset listener.
	 * Should be used on input elements that save the document, but doesn't reload the document.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @function
	 * @param {(Element|string|string[])} elements  The elements to register.
	 * @param {string}                    eventType The event type to listen to.
	 */
	const registerResetListener = ( elements, eventType ) => {
		_getNodeArray( elements ).forEach( el => {
			el.addEventListener( eventType, reset );
		} );
	}

	/**
	 * Registers unload listener.
	 * Should be used on input elements that save the document, and also reloads the document.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @function
	 * @param {(Element|string|string[])} elements  The elements to register.
	 * @param {string}                    eventType The event type to listen to.
	 */
	const registerUnloadListener = ( elements, eventType ) => {
		_getNodeArray( elements ).forEach( el => {
			el.addEventListener( eventType, _triggerUnload )
		} );
	}

	/**
	 * Registers default listeners.
	 * Also triggers an event, so other developers may consistently add their listeners.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @function
	 */
	const reloadDefaultListeners = () => {

		_loadedListeners = false;

		//= Mouse input
		registerChangeListener(
			[
				'.tsf-metaboxes input[type=radio][name]',
				'.tsf-metaboxes input[type=checkbox][name]',
				'.tsf-metaboxes select[name]',
				'#tsf-inpost-box .inside input[type=radio][name]',
				'#tsf-inpost-box .inside input[type=checkbox][name]',
				'#tsf-inpost-box .inside select[name]',
				'.tsf-term-meta input[type=radio][name]',
				'.tsf-term-meta input[type=checkbox][name]',
				'.tsf-term-meta select[name]',
			],
			'change'
		);

		//= Text input
		registerChangeListener(
			[
				'.tsf-metaboxes input:not([type=radio]):not([type=checkbox])[name]',
				'.tsf-metaboxes textarea[name]',
				'#tsf-inpost-box .inside input:not([type=radio]):not([type=checkbox])[name]',
				'#tsf-inpost-box .inside textarea[name]',
				'.tsf-term-meta input:not([type=radio]):not([type=checkbox])[name]',
				'.tsf-term-meta textarea[name]',
			],
			'input'
		);

		//= Non-redirect, Gutenberg save action.
		registerResetListener(
			document,
			'tsf-gutenberg-onsave-completed'
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

		document.dispatchEvent( new CustomEvent( 'tsf-registered-ays-listeners' ) );

		_loadedListeners = true;
	}

	/**
	 * Alerts the user that there are unsaved changes before unloading the tab/window.
	 *
	 * @since 4.1.0
	 * @access private
	 * @link https://developer.mozilla.org/en-US/docs/Web/API/WindowEventHandlers/onbeforeunload
	 *
	 * @function
	 * @param {event} event
	 */
	const _alertUserBeforeunload = event => {
		if ( areSettingsChanged() ) {
			// Every other browser:
			event.preventDefault();
			// Chromium requirement:
			event.returnValue = l10n.i18n['saveAlert'];
		}
	}

	/**
	 * Initializes AYS scripts on tsf-interactive.
	 *
	 * @since 4.0.0
	 * @since 4.1.1 Now binds to the tsf-interactive event, instead of tsf-ready.
	 * @access private
	 *
	 * @function
	 */
	const _readyAys = () => {
		// Initialise form field changing flag.
		reloadDefaultListeners();

		areSettingsChanged() && console.warn( 'tsfAys: Doing it wrong! Settings were changed prior ready-state. Debug me.' );
		// Undo accidental load-sequence state changes.
		deregisterChange();

		//= Alert onbeforeunload
		window.addEventListener( 'beforeunload', _alertUserBeforeunload );
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
		 */
		load: () => {
			document.body.addEventListener( 'tsf-interactive', _readyAys );
		}
	}, {
		reset,
		getChangedState,
		areSettingsChanged,
		registerChange,
		deregisterChange,
		registerChangeListener,
		registerResetListener,
		registerUnloadListener,
		reloadDefaultListeners
	}, {
		l10n
	} );
}();
window.tsfAys.load();
