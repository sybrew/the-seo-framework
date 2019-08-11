/**
 * This file holds The SEO Framework plugin's JS code for TSF description fields.
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
	 * @access private
	 * @type {(Object<string, *>)|boolean|null} l10n Localized strings
	 */
	const l10n = 'undefined' !== typeof tsfDescriptionL10n && tsfDescriptionL10n;

	/**
	 * @since 4.0.0
	 * @type {(void|Element)} The input element.
	 */
	let descriptionInput = void 0;

	/**
	 * @since 4.0.0
	 * @access private
	 * @type {(Object<string, *>)} the query state.
	 */
	let state = {
		allowReferenceChange: true,
		defaultDescription:   tsf.escapeString( l10n.states.defaultDescription.trim() ),
	};

	/**
	 * Sets input element for all listeners.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @param {Element} element
	 * @return {undefined}
	 */
	const setInputElement = ( element ) => {
		descriptionInput = element;
	}

	/**
	 * Returns state.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @param {(string|undefined)} part The part to return. Leave empty to return the whole state.
	 * @return {(Object<string, *>)|*|undefined}
	 */
	const getState = part => part ? ( part in state ? state[ part ] : void 0 ) : state;

	/**
	 * Updates state.
	 *
	 * There's no need to escape the input, it may be double-escaped if you do so.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @param {string} type  The state index to change.
	 * @param {*}      value The value to set the state to.
	 * @return {undefined}
	 */
	const updateState = ( type, value ) => {

		state[ type ] = value;

		switch ( type ) {
			case 'allowReferenceChange':
			case 'defaultDescription':
			default:
				enqueueTriggerInput();
				break;
		}
	}

	/**
	 * Updates the description reference.
	 *
	 * Used by the character counters, pixel counters, and social meta inputs.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	const _setReferenceDescription = ( event ) => {
		let reference = document.getElementById( 'tsf-description-reference' ),
			text      = state.allowReferenceChange && event.target.value || state.defaultDescription;

		if ( ! reference ) return;

		text = text.trim();

		if ( text.length < 1 || ! state.allowReferenceChange ) {
			text = event.target.placeholder;
		}

		reference.innerHTML = tsf.escapeString( tsf.decodeEntities( tsf.sDoubleSpace( text.trim() ) ) );

		// Fires change event. Defered.
		setTimeout( () => { $( reference ).change() }, 0 );
	}

	/**
	 * Updates the title placeholder.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	const _updatePlaceholder = ( event ) => {

		if ( ! state.allowReferenceChange
		|| event.target.value // No need to update it if there's no value set.
		) return;

		event.target.placeholder = document.getElementById( 'tsf-description-reference' ).innerText;
	}

	/**
	 * Updates used separator and all examples thereof.
	 *
	 * @since 3.0.4 : 1. Threshold "too long" has been increased from 155 to 300.
	 *                2. Threshold "far too long" has been increased to 330 from 175.
	 * @since 3.1.0 Now uses the new guidelines via a filterable function in PHP.
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	const _updateCounter = ( event ) => {
		let counter   = document.getElementById( event.target.id + '_chars' ),
			reference = document.getElementById( 'tsf-description-reference' );

		if ( ! counter || ! tsfC ) return;

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
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	const _updatePixels = ( event ) => {
		let pixels    = document.getElementById( event.target.id + '_pixels' ),
			reference = document.getElementById( 'tsf-description-reference' );

		if ( ! pixels || ! tsfC ) return;

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
	 * @access public
	 *
	 * @function
	 * @return {undefined}
	 */
	const triggerInput = () => {
		$( descriptionInput ).trigger( 'input.tsfUpdateDescriptions' );
	}

	/**
	 * Triggers counter updates.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @function
	 * @return {undefined}
	 */
	const triggerCounter = () => {
		$( descriptionInput ).trigger( 'tsf-update-description-counter' );
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
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	const _onUpdateDescriptionsTrigger = ( event ) => {

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
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	const _onUpdateCounterTrigger = ( event ) => {
		_updateCounter( event );
		_updatePixels( event );
	}

	let _enqueueTriggerInputBuffer = 0;
	/**
	 * Triggers meta description input.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @function
	 * @return {undefined}
	 */
	const enqueueTriggerInput = () => {
		clearTimeout( _enqueueTriggerInputBuffer );
		_enqueueTriggerInputBuffer = setTimeout( triggerInput, 10 );
	}

	/**
	 * Triggers description update, without affecting tsfAys change listeners.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @function
	 * @param {!jQuery.Event}
	 * @return {undefined}
	 */
	const triggerUnregisteredInput = () => {
		if ( ! tsfAys ) {
			triggerInput();
		} else {
			let settingsChangedCache = tsfAys.getChangedState;
			triggerInput();
			if ( ! settingsChangedCache ) tsfAys.reset();
		}
	}

	let unregisteredTriggerBuffer = 0;
	/**
	 * Enqueues unregistered description input triggers.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @function
	 * @return {undefined}
	 */
	const enqueueUnregisteredInputTrigger = () => {
		clearTimeout( unregisteredTriggerBuffer );
		unregisteredTriggerBuffer = setTimeout( triggerUnregisteredInput, 10 );
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
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initDescriptions = () => {

		// Triggers input changes on resize after hitting thresholds.
		$( document ).on( 'wp-window-resized', _doResize );

		// When counters are updated, trigger an input; which will reassess them.
		$( window ).on( 'tsf-counter-updated', enqueueTriggerInput );
	}

	/**
	 * Initializes the description input action callbacks.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _loadDescriptionActions = () => {

		if ( ! descriptionInput instanceof Element ) return;

		$( descriptionInput ).on( 'input.tsfUpdateDescriptions', _onUpdateDescriptionsTrigger );
		$( descriptionInput ).on( 'tsf-update-description-counter', _onUpdateCounterTrigger );

		enqueueUnregisteredInputTrigger();
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
			// the setInputElement() must be called here.
			$( document.body ).on( 'tsf-onload', _initDescriptions );

			// the setInputElement() must've been called here.
			$( document.body ).on( 'tsf-ready', _loadDescriptionActions );
		}
	}, {
		setInputElement,
		getState,
		updateState,
		triggerCounter,
		triggerInput,
		enqueueTriggerInput,
		triggerUnregisteredInput,
		enqueueUnregisteredInputTrigger,
	}, {
		l10n
	} );
}( jQuery );
jQuery( window.tsfDescription.load );
