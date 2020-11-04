/**
 * This file holds The SEO Framework plugin's JS code for TSF description fields.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/autodescription/>
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2020 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Holds tsfDescription values in an object to avoid polluting global namespace.
 *
 * This is a self-constructed function assigned as an object.
 *
 * @since 4.0.0
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfDescription = function( $ ) {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Unused.
	 * @ignore
	 * @access private
	 * @type {(Object<string, *>)|boolean|null} l10n Localized strings
	 */
	const l10n = 'undefined' !== typeof tsfDescriptionL10n && tsfDescriptionL10n;

	/**
	 * @since 4.1.0
	 * @type {Map<string,Element>} The input element instances.
	 */
	const descriptionInputInstances = new Map();

	/**
	 * @since 4.1.0
	 * @access private
	 * @type {(Object<string,Object<string,*>)} the query state.
	 */
	let states = {};

	/**
	 * Sets input element for all listeners. Must be called prior interacting with this object.
	 * Resets the state for the input ID.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now creates an instance in a map this object, and returns it.
	 * @access public
	 *
	 * @param {Element} element
	 * @return {Element}
	 */
	const setInputElement = ( element ) => {
		descriptionInputInstances.set( element.id, element );
		states[ element.id ] = {
			allowReferenceChange: true,
			defaultDescription:   '',
			useDefaultDescription: true,
		};
		_loadDescriptionActions( element );
		return getInputElement( element.id );
	}

	/**
	 * Gets input element, if exists.
	 *
	 * @since 4.1.0
	 * @access public
	 *
	 * @param {string} id The element ID.
	 * @return {Element}
	 */
	const getInputElement = id => descriptionInputInstances.get( id );

	let _legacyElementId = void 0;
	/**
	 * Gets legacy input element, if exists.
	 *
	 * @since 4.1.0
	 * @access public
	 *
	 * @param {string} id The element ID.
	 * @return {Element|undefined}
	 */
	const getLegacyElementId = () => {
		if ( _legacyElementId ) return _legacyElementId;

		for ( const id of descriptionInputInstances.keys() ) {
			if ( getStateOf( id, 'hasLegacy' ) )
				return _legacyElementId = id;
		}

		return undefined;
	}

	/**
	 * Returns state.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Deprecated.
	 * @access public
	 * @deprecated 4.1.0
	 * @see getStateOf()
	 *
	 * @param {(string|undefined)} part The part to return. Leave empty to return the whole state.
	 * @return {(Object<string, *>)|*|undefined}
	 */
	const getState = part => {
		tsf.deprecatedFunc( 'tsfDescription.getState()', '4.1.0', 'tsfDescription.getStateOf()' );
		return getStateOf( getLegacyElementId(), part );
	}

	/**
	 * Returns state of ID.
	 *
	 * @since 4.1.0
	 * @access public
	 *
	 * @param {string}             id The input element ID.
	 * @param {(string|undefined)} part The part to return. Leave empty to return the whole state.
	 * @return {(Object<string, *>)|*|null}
	 */
	const getStateOf = ( id, part ) => part ? ( part in states[ id ] ? states[ id ][ part ] : void 0 ) : states[ id ];

	/**
	 * Updates state.
	 *
	 * There's no need to escape the input, it may be double-escaped if you do so.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 deprecated.
	 * @access public
	 * @deprecated 4.1.0
	 * @see updateStateOf()
	 *
	 * @param {string} part  The state index to change.
	 * @param {*}      value The value to set the state to.
	 * @return {undefined}
	 */
	const updateState = ( part, value ) => {
		tsf.deprecatedFunc( 'tsfDescription.updateState()', '4.1.0', 'tsfDescription.updateStateOf()' );
		return updateStateOf( getLegacyElementId(), part, value );
	}

	/**
	 * Updates state of ID.
	 *
	 * There's no need to escape the input, it may be double-escaped if you do so.
	 *
	 * @since 4.1.0
	 * @since 4.1.2 Added part `useDefaultDescription`.
	 * @access public
	 *
	 * @param {string} id The input element ID.
	 * @param {string} part  The state index to change.
	 * @param {*}      value The value to set the state to.
	 * @return {undefined}
	 */
	const updateStateOf = ( id, part, value ) => {

		// Legacy was probably called, but doesn't exist (yet).
		if ( ! ( id in states ) ) return;

		states[ id ][ part ] = value;

		switch ( part ) {
			case 'allowReferenceChange':
			case 'defaultDescription':
			case 'useDefaultDescription':
			default:
				enqueueTriggerInput( id );
				break;
		}
	}

	/**
	 * Updates state of all elements.
	 *
	 * There's no need to escape the input, it may be double-escaped if you do so.
	 *
	 * @since 4.1.0
	 * @access public
	 * @TODO add a "but" ({String|Array})
	 *
	 * @param {string} part  The state index to change.
	 * @param {*}      value The value to set the state to.
	 * @return {undefined}
	 */
	const updateStateAll = ( part, value ) => {
		descriptionInputInstances.forEach( element => {
			updateStateOf( element.id, part, value );
		} );
	}

	/**
	 * Returns description references of ID.
	 *
	 * @since 4.1.0
	 * @access public
	 *
	 * @param {string} id The input element ID.
	 * @return {HTMLElement[]}
	 */
	const _getDescriptionReferences = id => {
		let references = [ document.getElementById( 'tsf-description-reference_' + id ) ];

		if ( getStateOf( id, 'hasLegacy' ) ) {
			let legacy = document.getElementById( 'tsf-description-reference' );
			legacy && references.unshift( legacy );
		}

		return references;
	}

	/**
	 * Updates the description reference.
	 *
	 * Used by the character counters, pixel counters, and social meta inputs.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now supports multiple instances.
	 * @since 4.1.2 Now listens to `useDefaultDescription` when reference isn't locked.
	 * @access private
	 *
	 * @function
	 * @param {Event} event
	 * @return {undefined}
	 */
	const _setReferenceDescription = event => {
		let references = _getDescriptionReferences( event.target.id );

		if ( ! references[0] ) return;

		let allowReferenceChange  = getStateOf( event.target.id, 'allowReferenceChange' ),
			useDefaultDescription = allowReferenceChange ? getStateOf( event.target.id, 'useDefaultDescription' ) : true;

		let text = ( allowReferenceChange && event.target.value )
			|| ( useDefaultDescription && getStateOf( event.target.id, 'defaultDescription' ) )
			|| '';

		let referenceValue = tsf.escapeString( tsf.decodeEntities( tsf.sDoubleSpace( text.trim() ) ) );

		references.forEach( reference => {
			// We require the event below when adjusting some states... Don't uncomment this.
			// if ( reference.innerHTML = referenceValue ) return;

			reference.innerHTML = referenceValue;
			// Fires change event. Defered to another thread.
			setTimeout( () => { reference.dispatchEvent( new Event( 'change' ) ) }, 0 );
		} );
	}

	/**
	 * Updates the title placeholder.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now consistently sets a reliable placeholder.
	 * @access private
	 *
	 * @function
	 * @param {Event} event
	 * @return {undefined}
	 */
	const _updatePlaceholder = event => {
		event.target.placeholder = _getDescriptionReferences( event.target.id )[0].innerText;
	}

	/**
	 * Updates used separator and all examples thereof.
	 *
	 * @since 3.0.4 : 1. Threshold "too long" has been increased from 155 to 300.
	 *                2. Threshold "far too long" has been increased to 330 from 175.
	 * @since 3.1.0 Now uses the new guidelines via a filterable function in PHP.
	 *
	 * @function
	 * @param {Event} event
	 * @return {undefined}
	 */
	const _updateCounter = event => {
		if ( ! ( 'tsfC' in window ) ) return;

		let counter   = document.getElementById( event.target.id + '_chars' ),
			reference = _getDescriptionReferences( event.target.id )[0];

		if ( ! counter ) return;

		tsfC.updateCharacterCounter( {
			e:     counter,
			text:  reference.innerHTML,
			field: 'description',
			type:  'search',
		} );
	}

	/**
	 * Updates the pixel counter bound to the input.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @param {Event} event
	 * @return {undefined}
	 */
	const _updatePixels = event => {
		if ( ! ( 'tsfC' in window ) ) return;

		let pixels    = document.getElementById( event.target.id + '_pixels' ),
			reference = _getDescriptionReferences( event.target.id )[0];

		if ( ! pixels ) return;

		tsfC.updatePixelCounter( {
			e:     pixels,
			text:  reference.innerHTML,
			field: 'description',
			type:  'search',
		} );
	}

	/**
	 * Triggers meta description input.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now allows for a first parameter to be set.
	 * @access public
	 *
	 * @function
	 * @param {string} id The input id. When not set, all inputs will be triggered.
	 * @return {undefined}
	 */
	const triggerInput = id => {
		if ( id ) {
			let el = getInputElement( id );
			el && el.dispatchEvent( new Event( 'input' ) );
		} else {
			// We don't want it to loop infinitely. Check element.id value first.
			descriptionInputInstances.forEach( element => element.id && triggerInput( element.id ) );
		}
	}

	/**
	 * Triggers counter updates.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now allows for a first parameter to be set.
	 * @access public
	 *
	 * @function
	 * @param {string} id The input id. When not set, all inputs will be triggered.
	 * @return {undefined}
	 */
	const triggerCounter = id => {
		if ( id ) {
			let el = getInputElement( id );
			el && el.dispatchEvent( new CustomEvent( 'tsf-update-description-counter' ) );
		} else {
			// We don't want it to loop infinitely. Check element.id value first.
			descriptionInputInstances.forEach( element => element.id && triggerCounter( element.id ) );
		}
	}

	/**
	 * Updates placements, placeholders and counters.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see triggerInput
	 * @uses _onUpdateCounterTrigger
	 *
	 * @function
	 * @param {Event} event
	 * @return {undefined}
	 */
	const _onUpdateDescriptionsTrigger = event => {

		_setReferenceDescription( event );
		_updatePlaceholder( event );

		_onUpdateCounterTrigger( event );
	}

	/**
	 * Updates character counters.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see triggerCounter
	 *
	 * @function
	 * @param {Event} event
	 * @return {undefined}
	 */
	const _onUpdateCounterTrigger = event => {
		_updateCounter( event );
		_updatePixels( event );
	}

	let _enqueueTriggerInputBuffer = {};
	/**
	 * Triggers meta description input.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Added first parameter, id.
	 * @since 4.1.1 Now passes the right parameter to the input event.
	 * @access public
	 *
	 * @function
	 * @param {string} id The input ID.
	 * @return {undefined}
	 */
	const enqueueTriggerInput = id => {
		( id in _enqueueTriggerInputBuffer ) && clearTimeout( _enqueueTriggerInputBuffer[ id ] );
		_enqueueTriggerInputBuffer[ id ] = setTimeout( () => triggerInput( id ), 10 );
	}

	/**
	 * Triggers description update, without affecting tsfAys change listeners.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now allows for a first parameter to be set.
	 * @access public
	 *
	 * @function
	 * @param {Event}
	 * @param {string} id The input id. When not set, all inputs will be triggered.
	 * @return {undefined}
	 */
	const triggerUnregisteredInput = id => {
		if ( 'tsfAys' in window ) {
			let wereSettingsChanged = tsfAys.areSettingsChanged();

			triggerInput( id );

			// Only reset if we polluted the change listener, and only if a change wasn't already registered.
			if ( ! wereSettingsChanged && tsfAys.areSettingsChanged() )
				tsfAys.reset();
		} else {
			triggerInput( id );
		}
	}

	let _unregisteredTriggerBuffer = {};
	/**
	 * Enqueues unregistered description input triggers.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now allows for a first parameter to be set.
	 * @access public
	 *
	 * @function
	 * @param {string} id The input id. When not set, all inputs will be triggered.
	 * @return {undefined}
	 */
	const enqueueUnregisteredInputTrigger = id => {
		( id in _unregisteredTriggerBuffer ) && clearTimeout( _unregisteredTriggerBuffer[ id ] );
		_unregisteredTriggerBuffer[ id ] = setTimeout( () => triggerUnregisteredInput( id ), 10 );
	}

	let prevWidth = window.innerWidth;
	/**
	 * Triggers input event for descriptions in set intervals on window resize.
	 *
	 * This only happens if boundaries are surpassed to reduce CPU usage.
	 * This boundary is 782 pixels, because that forces input fields to change.
	 * in WordPress.
	 *
	 * This happens to all description inputs; as WordPress switches
	 * from Desktop to Mobile view at 782 pixels.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see ...\wp-admin\js\common.js
	 *
	 * @function
	 * @return {undefined}
	 */
	const _doResize = () => {
		let width = window.innerWidth;
		if ( prevWidth < width ) {
			if ( prevWidth <= 782 && width >= 782 ) {
				triggerUnregisteredInput();
			}
		} else {
			if ( prevWidth >= 782 && width <= 782 ) {
				triggerUnregisteredInput();
			}
		}
		prevWidth = width;
	}

	/**
	 * Initializes the description environment.
	 *
	 * @since 4.1.0
	 * @since 4.1.1 No longer passes the event to the enqueueUnregisteredInputTrigger() callback.
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initAllDescriptionActions = () => {

		// Triggers input changes on resize after hitting thresholds.
		// We can't bind to jQuery event listeners via native ES :(
		$( document ).on( 'wp-window-resized', _doResize );

		// When counters are updated, trigger an input; which will reassess them.
		window.addEventListener( 'tsf-counter-updated', () => enqueueUnregisteredInputTrigger() );
	}

	/**
	 * Reinitializes the description input action callbacks.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @param {Element} descriptionInput
	 * @return {undefined}
	 */
	const _loadDescriptionActions = descriptionInput => {
		if ( ! descriptionInput instanceof Element ) return;

		descriptionInput.addEventListener( 'input', _onUpdateDescriptionsTrigger );
		descriptionInput.addEventListener( 'tsf-update-description-counter', _onUpdateCounterTrigger );

		enqueueUnregisteredInputTrigger( descriptionInput.id );
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
			document.body.addEventListener( 'tsf-onload', _initAllDescriptionActions );
		}
	}, {
		setInputElement,
		getInputElement,
		getState,
		getStateOf,
		updateState,
		updateStateOf,
		updateStateAll,
		triggerCounter,
		triggerInput,
		enqueueTriggerInput,
		triggerUnregisteredInput,
		enqueueUnregisteredInputTrigger, // this should've been enqueueTriggerUnregisteredInput...
	}, {
		l10n
	} );
}( jQuery );
window.tsfDescription.load();
