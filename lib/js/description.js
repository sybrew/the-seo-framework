/**
 * This file holds The SEO Framework plugin's JS code for TSF description fields.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/autodescription/>
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 */
window.tsfDescription = function () {

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
	const states = {};

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
	function setInputElement( element ) {
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
	function getInputElement( id ) {
		return descriptionInputInstances.get( id );
	}

	/**
	 * Returns state of ID.
	 *
	 * @since 4.1.0
	 * @access public
	 *
	 * @param {string}             id The input element ID.
	 * @param {(string|undefined)} part The part to return. Leave empty to return the whole state.
	 * @return {(Object<string,*>)|*|null}
	 */
	function getStateOf( id, part ) {
		return part ? states[ id ]?.[ part ] : states[ id ];
	}

	/**
	 * Updates state of ID.
	 *
	 * There's no need to escape the input, it may be double-escaped if you do so.
	 *
	 * @since 4.1.0
	 * @since 4.1.2 Added part `useDefaultDescription`.
	 * @since 4.2.0 Now remains intert on a non-change.
	 * @access public
	 *
	 * @param {string} id The input element ID.
	 * @param {string} part  The state index to change.
	 * @param {*}      value The value to set the state to.
	 */
	function updateStateOf( id, part, value ) {

		if ( states[ id ][ part ] === value ) return;

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
	 * @since 4.2.0 Added a 3rd parameter, allowing you to exclude updates for certain elements.
	 * @access public
	 *
	 * @param {string}          part   The state index to change.
	 * @param {*}               value  The value to set the state to.
	 * @param {string|string[]} except The input element IDs to exclude from updates.
	 */
	function updateStateAll( part, value, except ) {

		except = Array.isArray( except ) ? except : [ except ];

		descriptionInputInstances.forEach( element => {
			if ( except.includes( element.id ) ) return;
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
	function _getDescriptionReferences( id ) {
		return [ document.getElementById( `tsf-description-reference_${id}` ) ];
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
	 * @param {Event} event
	 */
	function _setReferenceDescription( event ) {
		const references = _getDescriptionReferences( event.target.id );

		if ( ! references[0] ) return;

		const allowReferenceChange  = getStateOf( event.target.id, 'allowReferenceChange' ),
			  useDefaultDescription = allowReferenceChange ? getStateOf( event.target.id, 'useDefaultDescription' ) : true;

		let text = tsf.coalesceStrlen( allowReferenceChange && event.target.value.trim() )
			?? tsf.coalesceStrlen( useDefaultDescription && getStateOf( event.target.id, 'defaultDescription' ) )
			?? '';

		const referenceValue = tsf.escapeString(
			tsf.decodeEntities(
				tsf.sDoubleSpace(
					tsf.sTabs(
						tsf.sSingleLine(
							text
						).trim()
					)
				)
			) );
		const changeEvent = new Event( 'change' );

		references.forEach( reference => {
			// We require the event below when adjusting some states... Don't uncomment this.
			// if ( reference.innerHTML = referenceValue ) return;

			reference.innerHTML = referenceValue;
			// Fires change event. dispatchEvent is synchronous, so we defer it to another thread.
			setTimeout( () => { reference.dispatchEvent( changeEvent ) } );
		} );
	}

	/**
	 * Updates the title placeholder.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now consistently sets a reliable placeholder.
	 * @access private
	 *
	 * @param {Event} event
	 */
	function _updatePlaceholder( event ) {
		event.target.placeholder = _getDescriptionReferences( event.target.id )[0].textContent;
	}

	/**
	 * Updates used separator and all examples thereof.
	 *
	 * @since 3.0.4 1. Threshold "too long" has been increased from 155 to 300.
	 *              2. Threshold "far too long" has been increased to 330 from 175.
	 * @since 3.1.0 Now uses the new guidelines via a filterable function in PHP.
	 *
	 * @param {Event} event
	 */
	function _updateCounter( event ) {
		const counter   = document.getElementById( `${event.target.id}_chars` ),
			  reference = _getDescriptionReferences( event.target.id )[0];

		if ( ! counter ) return;

		tsfC?.updateCharacterCounter( {
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
	 * @param {Event} event
	 */
	function _updatePixels( event ) {
		const pixels    = document.getElementById( `${event.target.id}_pixels` ),
			  reference = _getDescriptionReferences( event.target.id )[0];

		if ( ! pixels ) return;

		tsfC?.updatePixelCounter( {
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
	 * @param {string} id The input id. When not set, all inputs will be triggered.
	 */
	function triggerInput( id ) {
		if ( id ) {
			getInputElement( id )?.dispatchEvent( new Event( 'input' ) );
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
	 * @param {string} id The input id. When not set, all inputs will be triggered.
	 */
	function triggerCounter( id ) {
		if ( id ) {
			getInputElement( id )?.dispatchEvent( new CustomEvent( 'tsf-update-description-counter' ) );
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
	 *
	 * @param {Event} event
	 */
	function _onUpdateDescriptionsTrigger( event ) {

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
	 * @param {Event} event
	 */
	function _onUpdateCounterTrigger( event ) {
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
	 * @param {string} id The input ID.
	 */
	function enqueueTriggerInput( id ) {
		( id in _enqueueTriggerInputBuffer ) && clearTimeout( _enqueueTriggerInputBuffer[ id ] );
		_enqueueTriggerInputBuffer[ id ] = setTimeout( () => triggerInput( id ), 1000/60 ); // 60fps
	}

	/**
	 * Triggers description update, without affecting tsfAys change listeners.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now allows for a first parameter to be set.
	 * @access public
	 *
	 * @param {Event}
	 * @param {string} id The input id. When not set, all inputs will be triggered.
	 */
	function triggerUnregisteredInput( id ) {
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
	 * @param {string} id The input id. When not set, all inputs will be triggered.
	 */
	function enqueueUnregisteredInputTrigger( id ) {
		( id in _unregisteredTriggerBuffer ) && clearTimeout( _unregisteredTriggerBuffer[ id ] );
		_unregisteredTriggerBuffer[ id ] = setTimeout( () => triggerUnregisteredInput( id ), 1000/60 ); // 60 fps
	}

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
	 * @since 5.1.0 Now always triggers unregistered input to support subpixel
	 *              layout shifting calculations when zooming in or out.
	 *              The title overflow boundaries may also be dynamically hit on
	 *              different screen sizes, and this must be accounted for.
	 * @todo rename this to "onResize"?
	 * @access private
	 * @see ...\wp-admin\js\common.js
	 */
	function _doResize() {
		triggerUnregisteredInput();
	}

	/**
	 * Reinitializes the description input action callbacks.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @param {Element} descriptionInput
	 */
	function _loadDescriptionActions( descriptionInput ) {
		if ( ! descriptionInput instanceof Element ) return;

		descriptionInput.addEventListener( 'input', _onUpdateDescriptionsTrigger );
		descriptionInput.addEventListener( 'tsf-update-description-counter', _onUpdateCounterTrigger );

		enqueueUnregisteredInputTrigger( descriptionInput.id );
	}

	/**
	 * Initializes the description environment.
	 *
	 * @since 4.1.0
	 * @since 4.1.1 No longer passes the event to the enqueueUnregisteredInputTrigger() callback.
	 * @access private
	 */
	function _initAllDescriptionActions() {

		// Triggers input changes on resize after hitting thresholds.
		window.addEventListener( 'tsf-resize', _doResize );

		// When counters are updated, trigger an input; which will reassess them.
		window.addEventListener( 'tsf-counter-updated', () => enqueueUnregisteredInputTrigger() );
	}

	return Object.assign( {
		/**
		 * Initialises all aspects of the scripts.
		 * You shouldn't call this.
		 *
		 * @since 4.0.0
		 * @access protected
		 */
		load: () => {
			document.body.addEventListener( 'tsf-onload', _initAllDescriptionActions );
		},
	}, {
		setInputElement,
		getInputElement,
		getStateOf,
		updateStateOf,
		updateStateAll,
		triggerCounter,
		triggerInput,
		enqueueTriggerInput,
		triggerUnregisteredInput,
		enqueueUnregisteredInputTrigger, // FIXME: this should've been enqueueTriggerUnregisteredInput... deprecate in TSF 5.2
	} );
}();
window.tsfDescription.load();
