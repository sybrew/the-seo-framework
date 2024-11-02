/**
 * This file holds The SEO Framework plugin's JS code for TSF canonical URL fields.
 * We dub this Canonical URL Notation Tool - The Reliable Accurate Predictor.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/autodescription/>
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
 * Holds tsfCanonical values in an object to avoid polluting global namespace.
 *
 * This is a self-constructed function assigned as an object.
 *
 * @since 5.0.7
 *
 * @constructor
 */
window.tsfCanonical = function () {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 5.0.7
	 * @access public
	 * @type {(Object<string,*>)|boolean|null} l10n Localized strings.
	 */
	const l10n = tsfCanonicalL10n;

	/**
	 * @since 5.0.7
	 * @access public
	 * @type {Boolean}
	 */
	const usingPermalinks = l10n.params.usingPermalinks;
	/**
	 * @since 5.0.7
	 * @access public
	 * @type {String}
	 */
	const rootUrl = tsf.stripTags( l10n.params.rootUrl );
	/**
	 * @since 5.0.7
	 * @access public
	 * @type {{code:String[],replace:String[],queryReplace:String[]}}
	 */
	const rewrite = l10n.params.rewrite;

	/**
	 * @since 5.0.7
	 * @type {Map<string,Element>} The input element instances.
	 */
	const canonicalInputInstances = new Map();

	/**
	 * @since 5.0.7
	 * @access private
	 * @type {(Object<string,Object<string,*>)} the query state.
	 */
	const states = {};

	/**
	 * Sets input element for all listeners. Must be called prior interacting with this object.
	 * Resets the state for the input ID.
	 *
	 * @since 5.0.7
	 * @access public
	 *
	 * @param {Element} element
	 * @return {Element}
	 */
	function setInputElement( element ) {
		canonicalInputInstances.set( element.id, element );
		states[ element.id ] = {
			allowReferenceChange: true,
			defaultCanonical:     '',
			showUrlPlaceholder:   true,
			preferredScheme:      '',
			urlStructure:         '',
			urlDataParts:         {},
		};
		_loadCanonicalActions( element );
		return getInputElement( element.id );
	}

	/**
	 * Gets input element, if exists.
	 *
	 * @since 5.0.7
	 * @access public
	 *
	 * @param {string} id The element ID.
	 * @return {Element}
	 */
	function getInputElement( id ) {
		return canonicalInputInstances.get( id );
	}

	/**
	 * Returns state of ID.
	 *
	 * @since 5.0.7
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
	 * @since 5.0.7
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
			case 'showUrlPlaceholder':
			case 'preferredScheme':
			case 'urlStructure':
			case 'urlDataParts':
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
	 * @since 5.0.7
	 * @access public
	 *
	 * @param {string}          part   The state index to change.
	 * @param {*}               value  The value to set the state to.
	 * @param {string|string[]} except The input element IDs to exclude from updates.
	 */
	function updateStateAll( part, value, except ) {

		except = Array.isArray( except ) ? except : [ except ];

		canonicalInputInstances.forEach( element => {
			if ( except.includes( element.id ) ) return;
			updateStateOf( element.id, part, value );
		} );
	}

	/**
	 * Tests whether the rewrite structure includes a rewrite part.
	 * When given an array, any match will return true.
	 *
	 * @since 5.0.7
	 * @access public
	 *
	 * @param {string}          id   The input element ID.
	 * @param {string|string[]} code The rewrite code to test.
	 * @return {boolean}
	 */
	function structIncludes( id, code ) {

		const urlStructure = getStateOf( id, 'urlStructure' );

		if ( Array.isArray( code ) )
			return code.some( c => urlStructure.includes( c ) );

		return urlStructure.includes( code );
	}

	/**
	 * Updates the title placeholder.
	 *
	 * @since 5.0.7
	 * @access private
	 *
	 * @param {Event} event
	 */
	function _updatePlaceholder( event ) {

		let placeholder = '';

		if ( getStateOf( event.target.id, 'showUrlPlaceholder' ) ) {
			if ( getStateOf( event.target.id, 'allowReferenceChange' ) ) {
				const urlStructure    = getStateOf( event.target.id, 'urlStructure' ),
					  urlDataParts    = getStateOf( event.target.id, 'urlDataParts' ),
					  preferredScheme = getStateOf( event.target.id, 'preferredScheme' ),
					  queryReplace    = usingPermalinks ? [] : rewrite.queryReplace;

				let struct = urlStructure;

				rewrite.code.forEach( ( code, index ) => {
					// Skip unregistered structs.
					if ( ! struct.includes( code ) ) return;

					let replacement = null;

					if ( code in urlDataParts ) {
						replacement = `${urlDataParts[ code ]}`.match(
							new RegExp(
								// The ? is a wildcard on the front-end, so <https://theseoframework.com/non-existing/5-0/> works.
								// But we need to create a full URL, so we remove the wildcard '?'.
								rewrite.replace[ index ].replace( /(\+|\*)\?/g, '$1' ),
							),
						)?.[0] ?? '';
					}

					if ( null !== replacement ) {
						struct = struct.replace(
							code,
							( queryReplace[ index ] ?? '' ) + replacement,
						);
					} else {
						struct = struct.replace( code, '' );
					}
				} );

				// Remove double slashes and spaces.
				struct = struct.replace( /\/{2,}/g, '/' );
				struct = struct.replace( /\s+/g, '-' );

				const placeholderUrl = URL.parse( struct, rootUrl );
				placeholderUrl.protocol = `${preferredScheme}:`;

				placeholder = placeholderUrl.href;
			} else {
				placeholder = getStateOf( event.target.id, 'defaultCanonical' );
			}
		}

		event.target.placeholder = placeholder;
	}

	/**
	 * Triggers canonical URL input.
	 *
	 * @since 5.0.7
	 * @access public
	 *
	 * @param {string} id The input id. When not set, all inputs will be triggered.
	 */
	function triggerInput( id ) {
		if ( id ) {
			getInputElement( id )?.dispatchEvent( new Event( 'input' ) );
		} else {
			// We don't want it to loop infinitely. Check element.id value first.
			canonicalInputInstances.forEach( element => element.id && triggerInput( element.id ) );
		}
	}

	/**
	 * Updates reference and placeholders.
	 *
	 * @since 5.0.7
	 * @access private
	 * @see triggerInput
	 *
	 * @param {Event} event
	 */
	function _onUpdateCanonicalUrlsTrigger( event ) {
		_updatePlaceholder( event );
	}

	let _enqueueTriggerInputBuffer = {};
	/**
	 * Triggers canonical URL input.
	 *
	 * @since 5.0.7
	 * @access public
	 *
	 * @param {string} id The input ID.
	 */
	function enqueueTriggerInput( id ) {
		( id in _enqueueTriggerInputBuffer ) && clearTimeout( _enqueueTriggerInputBuffer[ id ] );
		_enqueueTriggerInputBuffer[ id ] = setTimeout( () => triggerInput( id ), 1000/60 ); // 60fps
	}

	/**
	 * Triggers canonical URL update, without affecting tsfAys change listeners.
	 *
	 * @since 5.0.7
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
	 * Enqueues unregistered canonical URL input triggers.
	 *
	 * @since 5.0.7
	 * @access public
	 *
	 * @param {string} id The input id. When not set, all inputs will be triggered.
	 */
	function enqueueTriggerUnregisteredInput( id ) {
		( id in _unregisteredTriggerBuffer ) && clearTimeout( _unregisteredTriggerBuffer[ id ] );
		_unregisteredTriggerBuffer[ id ] = setTimeout( () => triggerUnregisteredInput( id ), 1000/60 ); // 60 fps
	}

	/**
	 * Reinitializes the canonical URL input action callbacks.
	 *
	 * @since 5.0.7
	 * @access private
	 *
	 * @param {Element} canonicalInput
	 */
	function _loadCanonicalActions( canonicalInput ) {

		if ( ! canonicalInput instanceof Element ) return;

		canonicalInput.addEventListener( 'input', _onUpdateCanonicalUrlsTrigger );

		enqueueTriggerUnregisteredInput( canonicalInput.id );
	}

	/**
	 * Initializes the description environment.
	 *
	 * @since 5.0.7
	 * @access private
	 */
	function _initAllCanonicalActions() {
		// When counters are updated, trigger an input; which will reassess them.
		window.addEventListener( 'tsf-counter-updated', () => enqueueTriggerUnregisteredInput() );
	}

	return Object.assign( {
		/**
		 * Initialises all aspects of the scripts.
		 * You shouldn't call this.
		 *
		 * @since 5.0.7
		 * @access protected
		 */
		load: () => {
			document.body.addEventListener( 'tsf-onload', _initAllCanonicalActions );
		},
	}, {
		setInputElement,
		getInputElement,
		getStateOf,
		updateStateOf,
		updateStateAll,
		structIncludes,
		triggerInput,
		enqueueTriggerInput,
		triggerUnregisteredInput,
		enqueueTriggerUnregisteredInput,
	} );
}();
window.tsfDescription.load();
