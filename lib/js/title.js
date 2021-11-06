/**
 * This file holds The SEO Framework plugin's JS code for TSF title fields.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/autodescription/>
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2021 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds tsfTitle values in an object to avoid polluting global namespace.
 *
 * Only one instance should act on this per window.
 *
 * @since 4.0.0
 *
 * @constructor
 */
window.tsfTitle = function() {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 4.0.0
	 * @access public
	 * @type {(Object<string, *>)|boolean|null} l10n Localized strings.
	 */
	const l10n = 'undefined' !== typeof tsfTitleL10n && tsfTitleL10n;

	/**
	 * @since 4.0.0
	 * @access public
	 * @type {String}
	 */
	const untitledTitle = tsf.escapeString( l10n.params.untitledTitle );
	/**
	 * @since 4.1.0
	 * @access public
	 * @type {String}
	 */
	const protectedPrefix = tsf.escapeString( l10n.i18n.protectedTitle );
	/**
	 * @since 4.1.0
	 * @access public
	 * @type {String}
	 */
	const privatePrefix = tsf.escapeString( l10n.i18n.privateTitle );
	/**
	 * @since 4.1.0
	 * @access public
	 * @type {Boolean}
	 */
	const stripTitleTags = !! l10n.params.stripTitleTags;

	/**
	 * @since 4.1.0
	 * @type {(Map<string,Element>)} The input element instances.
	 */
	const titleInputInstances = new Map();

	/**
	 * @since 4.1.0
	 * @access private
	 * @type {(Object<string,Object<string,*>)} the query state.
	 */
	const states = {};

	/**
	 * @since 4.1.0
	 * @access private
	 * @type {(Map<string,string>)} The input element instances.
	 */
	const additionsStack = new Map();
	/**
	 * @since 4.1.0
	 * @access private
	 * @type {(Map<string,string>)} The input element instances.
	 */
	const prefixStack = new Map();

	/**
	 * @since 4.1.0
	 * @internal Use getStateOf() instead.
	 * @access private
	 *
	 * @param {String} id
	 * @param {String} value
	 * @return {String} The additions value.
	 */
	const _getAdditionsValue = id => additionsStack.get( id ) || '';
	/**
	 * @since 4.1.0
	 * @internal Use getStateOf() instead.
	 * @access private
	 *
	 * @param {String} id
	 * @param {String} value
	 * @return {String} The prefix value.
	 */
	const _getPrefixValue = id => prefixStack.get( id ) || '';
	/**
	 * @since 4.1.0
	 * @internal Use updateStateOf() instead.
	 * @access private
	 *
	 * @param {String} id
	 * @param {String} value
	 * @return {String} The new value.
	 */
	const _setAdditionsValue = ( id, value ) => additionsStack.set( id, value ) && _getAdditionsValue( id );
	/**
	 * @since 4.1.0
	 * @internal Use updateStateOf() instead.
	 * @access private
	 *
	 * @param {String} id
	 * @param {String} value
	 * @return {String} The new value.
	 */
	const _setPrefixValue = ( id, value ) => prefixStack.set( id, value ) && _getPrefixValue( id );

	/**
	 * @since 4.1.0
	 * @access private
	 * @return {Element}
	 */
	const _getHoverPrefixElement = id => document.getElementById( `tsf-title-placeholder-prefix_${id}` ) || document.createElement( 'span' );
	/**
	 * @since 4.1.0
	 * @access private
	 * @return {Element}
	 */
	const _getHoverAdditionsElement = id => document.getElementById( `tsf-title-placeholder-additions_${id}` ) || document.createElement( 'span' );

	/**
	 * Sets input element for all listeners. Must be called prior interacting with this object.
	 * Resets the state for the input ID.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now creates an instance in a map this object, and returns it.
	 * @access public
	 *
	 * @param {Element} element
	 */
	const setInputElement = element => {
		titleInputInstances.set( element.id, element );
		states[ element.id ] = {
			showPrefix:           true,
			allowReferenceChange: true,
			defaultTitle:         '',
			separator:            l10n.states.titleSeparator,
			prefixPlacement:      l10n.states.prefixPlacement,
		}
		_loadTitleActions( element );
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
	const getInputElement = id => titleInputInstances.get( id );

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
	const getStateOf = ( id, part ) => part ? states[ id ]?.[ part ] : states[ id ];

	/**
	 * Updates state of ID.
	 *
	 * There's no need to escape the input, it may be double-escaped if you do so.
	 *
	 * @since 4.1.0
	 * @since 4.2.0 Now remains intert on a non-change.
	 * @access public
	 *
	 * @param {string} id The input element ID.
	 * @param {string} part  The state index to change.
	 * @param {*}      value The value to set the state to.
	 */
	const updateStateOf = ( id, part, value ) => {

		if ( states[ id ][ part ] === value ) return;

		states[ id ][ part ] = value;

		switch ( part ) {
			case 'showPrefix':
			case 'prefixValue':
			case 'prefixPlacement':
				_updatePrefixValue( id );
				enqueueTriggerInput( id );
				break;

			case 'addAdditions':
			case 'separator':
			case 'additionValue':
			case 'additionPlacement':
				_updateAdditionsValue( id );
				enqueueTriggerInput( id );
				break;

			case 'allowReferenceChange':
			case 'defaultTitle':
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
	const updateStateAll = ( part, value, except ) => {

		except = Array.isArray( except ) ? except : [ except ];

		titleInputInstances.forEach( element => {
			if ( except.includes( element.id ) ) return;
			updateStateOf( element.id, part, value );
		} );
	}

	/**
	 * Returns title references of ID.
	 *
	 * @since 4.1.0
	 * @access public
	 *
	 * @param {string} id The input element ID.
	 * @return {HTMLElement[]}
	 */
	const _getTitleReferences = id => {
		let references = [ document.getElementById( `tsf-title-reference_${id}` ) ];

		if ( getStateOf( id, 'hasLegacy' ) ) {
			let legacy = document.getElementById( 'tsf-title-reference' );
			legacy && references.unshift( legacy );
		}

		return references;
	}

	/**
	 * Returns title references with no-additions (Na) of ID.
	 *
	 * @since 4.1.0
	 * @access public
	 *
	 * @param {string} id The input element ID.
	 * @return {HTMLElement[]}
	 */
	const _getTitleNaReferences = id => [ document.getElementById( `tsf-title-noadditions-reference_${id}` ) ];

	/**
	 * Updates the title reference.
	 *
	 * Used by the character counters, pixel counters, and social meta inputs.
	 *
	 * @since 4.0.0
	 * @since 4.0.6 Now changes behavior depending on RTL-status.
	 * @since 4.1.0 1. Now also sets references without the additions.
	 *              2. Now supports multiple instances.
	 * @access private
	 *
	 * @function
	 * @param {Event} event
	 * @return {HTMLElement[]}
	 */
	const _setReferenceTitle = event => {
		const references   = _getTitleReferences( event.target.id ),
			  referencesNa = _getTitleNaReferences( event.target.id );

		if ( ! references[0] || ! referencesNa[0] ) return;

		const allowReferenceChange = getStateOf( event.target.id, 'allowReferenceChange' );

		let text   = allowReferenceChange && event.target.value.trim() || getStateOf( event.target.id, 'defaultTitle' ) || '',
			textNa = text;

		if ( text.length && allowReferenceChange ) {
			let prefix    = _getPrefixValue( event.target.id ),
				additions = _getAdditionsValue( event.target.id );

			if ( prefix.length && getStateOf( event.target.id, 'showPrefix' ) ) {
				switch ( getStateOf( event.target.id, 'prefixPlacement' ) ) {
					case 'before':
						if ( window.isRtl ) {
							text = text + prefix;
						} else {
							text = prefix + text;
						}
						break;

					case 'after':
						if ( window.isRtl ) {
							text = prefix + text;
						} else {
							text = text + prefix;
						}
						break;
				}
				textNa = text;
			}
			if ( additions.length ) {
				switch ( getStateOf( event.target.id, 'additionPlacement' ) ) {
					case 'before':
						text = additions + text;
						break;

					case 'after':
						text = text + additions;
						break;
				}
			}
		}

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
		const referenceNaValue = tsf.escapeString(
			tsf.decodeEntities(
				tsf.sDoubleSpace(
					tsf.sTabs(
						tsf.sSingleLine(
							textNa
						).trim()
					)
				)
			) );

		const changeEvent = new Event( 'change' );

		references.forEach( reference => {
			// We require the event below when adjusting some states... Don't uncomment this.
			// if ( reference.innerHTML === referenceValue ) return;

			reference.innerHTML = referenceValue;
			// Fires change event. Defered to another thread.
			setTimeout( () => { reference.dispatchEvent( changeEvent ) }, 0 );
		} );

		referencesNa.forEach( referenceNa => {
			// We require the event below when adjusting some states... Don't uncomment this.
			// if ( referenceNa.innerHTML === referenceNaValue ) return;

			referenceNa.innerHTML = referenceNaValue;
			// Fires change event. Defered to another thread.
			setTimeout( () => { referenceNa.dispatchEvent( changeEvent ) }, 0 );
		} );
	}

	/**
	 * Updates hover additions.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now supports multiple instances.
	 * @access private
	 *
	 * @param {string} id The input ID.
	 */
	const _updateAdditionsValue = id => {
		let value          = '',
			additionsValue = '',
			separator      = '';

		if ( getStateOf( id, 'addAdditions' ) ) {
			additionsValue = tsf.escapeString( tsf.decodeEntities( getStateOf( id, 'additionValue' ) ) );
			separator      = getStateOf( id, 'separator' );
		}

		if ( additionsValue ) {
			switch ( getStateOf( id, 'additionPlacement' ) ) {
				case 'before':
					value = `${additionsValue} ${separator} `;
					break;

				case 'after':
					value = ` ${separator} ${additionsValue}`;
					break;
			}
		}

		_getHoverAdditionsElement( id ).innerHTML = _setAdditionsValue( id, value || '' );
	}

	/**
	 * Updates hover prefix.
	 *
	 * @since 4.0.0
	 * @since 4.0.6 Now changes behavior depending on RTL-status.
	 * @since 4.1.0 Now supports multiple instances.
	 * @access private
	 *
	 * @param {string} id The input ID.
	 */
	const _updatePrefixValue = id => {
		let value       = '',
			showPrefix  = getStateOf( id, 'showPrefix' ),
			prefixValue = getStateOf( id, 'prefixValue' );

		if ( showPrefix && prefixValue ) {
			switch ( getStateOf( id, 'prefixPlacement' ) ) {
				case 'before':
					if ( window.isRtl ) {
						value = ` ${prefixValue}`;
					} else {
						value = `${prefixValue} `;
					}
					break;

				case 'after':
					if ( window.isRtl ) {
						value = `${prefixValue} `;
					} else {
						value = ` ${prefixValue}`;
					}
					break;
			}
		}

		_getHoverPrefixElement( id ).innerHTML = _setPrefixValue( id, value || '' );
	}

	/**
	 * Updates the title hover prefix and additions placement.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now supports multiple instances.
	 * @since 4.2.0 1. No longer relies on jQuery.
	 *              2. Now supports dynamic border sizes, this means that you can
	 *                 make a skewed-proportioned input element, and the hovers
	 *                 will align properly with the input text.
	 * @access private
	 *
	 * @function
	 * @param {Event} event
	 */
	const _updateHoverPlacement = event => {

		let hoverAdditionsElement = _getHoverAdditionsElement( event.target.id ),
			hoverPrefixElement    = _getHoverPrefixElement( event.target.id );

		if ( ! hoverAdditionsElement && ! hoverPrefixElement )
			return;

		const input      = event.target,
			  inputValue = event.target.value;

		const hasPrefixValue    = _getPrefixValue( event.target.id ).length && getStateOf( event.target.id, 'showPrefix' ),
			  hasAdditionsValue = !! _getAdditionsValue( event.target.id ).length;

		if ( ! hasPrefixValue && hoverPrefixElement )
			hoverPrefixElement.style.display = 'none';
		if ( ! hasAdditionsValue && hoverAdditionsElement )
			hoverAdditionsElement.style.display = 'none';

		if ( ! hasPrefixValue && ! hasAdditionsValue ) {
			//= Both items are emptied through settings.
			input.style.textIndent = 'initial';
			return;
		}

		if ( ! inputValue.length ) {
			//= Input is emptied.
			input.style.textIndent = 'initial';
			if ( hoverPrefixElement ) hoverPrefixElement.style.display = 'none';
			if ( hoverAdditionsElement ) hoverAdditionsElement.style.display = 'none';
			return;
		}

		const inputStyles = getComputedStyle( input ),
			  inputRect   = input.getBoundingClientRect();

		// Quick and dirty.
		const paddingTop    = parseInt( inputStyles.paddingTop ),
			  paddingRight  = parseInt( inputStyles.paddingRight ),
			  paddingBottom = parseInt( inputStyles.paddingBottom ),
			  paddingLeft   = parseInt( inputStyles.paddingLeft ),
			  borderTop     = parseInt( inputStyles.borderTopWidth ),
			  borderRight   = parseInt( inputStyles.borderRightWidth ),
			  borderBottom  = parseInt( inputStyles.borderBottomWidth ),
			  borderLeft    = parseInt( inputStyles.borderTopWidth ),
			  marginTop     = parseInt( inputStyles.marginTop ),
			  marginRight   = parseInt( inputStyles.marginRight ),
			  marginBottom  = parseInt( inputStyles.marginBottom ),
			  marginLeft    = parseInt( inputStyles.marginLeft );

		const offsetPosition = window.isRtl ? 'right' : 'left',
			  leftOffset     = paddingLeft + borderLeft + marginLeft,
			  rightOffset    = paddingRight + borderRight + marginRight;

		let fontStyleCSS = {
			display:           inputStyles.display,
			lineHeight:        inputStyles.lineHeight,
			fontFamily:        inputStyles.fontFamily,
			fontWeight:        inputStyles.fontWeight,
			fontSize:          inputStyles.fontSize,
			letterSpacing:     inputStyles.letterSpacing,
			marginTop:         `${marginTop}px`,
			marginBottom:      `${marginBottom}px`,
			paddingTop:        `${paddingTop}px`,
			paddingBottom:     `${paddingBottom}px`,
			border:            `0 solid transparent`,
			borderTopWidth:    `${borderTop}px`,
			borderBottomWidth: `${borderBottom}px`,
		};

		let additionsMaxWidth = 0,
			additionsOffset   = 0,
			prefixOffset      = 0,
			totalIndent       = 0,
			prefixMaxWidth    = 0;

		if ( hasPrefixValue ) {
			Object.assign(
				hoverPrefixElement.style,
				fontStyleCSS,
				{ maxWidth: 'initial' },
			);
			prefixMaxWidth = hoverPrefixElement.getBoundingClientRect().width;
			prefixOffset  += leftOffset; // rightOffset for RTL? -> difficult to determine?
		}

		if ( hasAdditionsValue ) {

			Object.assign(
				hoverAdditionsElement.style,
				fontStyleCSS,
				{ maxWidth: 'initial' },
			);

			const offsetElement = document.getElementById( `tsf-title-offset_${event.target.id}` );
			offsetElement.textContent = inputValue;

			Object.assign(
				offsetElement.style,
				{
					fontFamily:    fontStyleCSS.fontFamily,
					fontWeight:    fontStyleCSS.fontWeight,
					fontSize:      fontStyleCSS.fontSize,
					letterSpacing: fontStyleCSS.letterSpacing,
				},
			);
			const textWidth = offsetElement.getBoundingClientRect().width;

			const additionsWidth = hoverAdditionsElement.getBoundingClientRect().width;

			switch ( getStateOf( event.target.id, 'additionPlacement' ) ) {
				case 'before':
					additionsMaxWidth = inputRect.width - rightOffset - paddingLeft - borderLeft - textWidth - prefixMaxWidth;
					if ( additionsMaxWidth < 0 ) {
						// Add negative width to the prefix element, so it may stay its size, and hide the additions first.
						prefixMaxWidth   += additionsMaxWidth;
						additionsMaxWidth = 0;
					}
					additionsMaxWidth = additionsMaxWidth < additionsWidth ? additionsMaxWidth : additionsWidth;

					if ( additionsMaxWidth < 0 )
						additionsMaxWidth = 0;

					totalIndent     += additionsMaxWidth;
					prefixOffset    += additionsMaxWidth;
					// "We" write to the right, so we take the leftoffset. TODO RTL?
					additionsOffset += leftOffset;
					break;

				case 'after':
					additionsMaxWidth = inputRect.width - leftOffset - paddingRight - borderRight - textWidth - prefixMaxWidth;
					if ( additionsMaxWidth < 0 ) {
						// Add negative width to the prefix element, so it may stay its size, and hide the additions first.
						prefixMaxWidth   += additionsMaxWidth;
						additionsMaxWidth = 0;
					}
					additionsMaxWidth = additionsMaxWidth < additionsWidth ? additionsMaxWidth : additionsWidth;

					if ( additionsMaxWidth < 0 )
						additionsMaxWidth = 0;

					// "We" write to the right, so we take the leftoffset. TODO RTL?
					additionsOffset += leftOffset + textWidth + prefixMaxWidth;
					break;
			}
		}
		prefixMaxWidth = prefixMaxWidth < 0 ? 0 : prefixMaxWidth;

		if ( hasPrefixValue ) {
			Object.assign(
				hoverPrefixElement.style,
				{
					[offsetPosition]: `${prefixOffset}px`,
					maxWidth:         `${prefixMaxWidth}px`,
				}
			);
			// Only set if there's actually a prefix.
			totalIndent += prefixMaxWidth;
		}

		if ( hasAdditionsValue ) {
			Object.assign(
				hoverAdditionsElement.style,
				{
					[offsetPosition]: `${additionsOffset}px`,
					maxWidth:         `${additionsMaxWidth}px`,
				}
			);
		}

		input.style.textIndent = `${totalIndent}px`;
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
	 */
	const _updatePlaceholder = event => {
		event.target.placeholder = _getTitleReferences( event.target.id )[0].textContent;
	}

	/**
	 * Updates the character counter bound to the input.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @param {Event} event
	 */
	const _updateCounter = event => {
		if ( ! ( 'tsfC' in window ) ) return;

		let counter   = document.getElementById( `${event.target.id}_chars` ),
			reference = _getTitleReferences( event.target.id )[0];

		if ( ! counter ) return;

		tsfC.updateCharacterCounter( {
			e:     counter,
			text:  reference.innerHTML,
			field: 'title',
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
	 */
	const _updatePixels = event => {
		if ( ! ( 'tsfC' in window ) ) return;

		let pixels    = document.getElementById( `${event.target.id}_pixels` ),
			reference = _getTitleReferences( event.target.id )[0];

		if ( ! pixels ) return;

		tsfC.updatePixelCounter( {
			e:     pixels,
			text:  reference.innerHTML,
			field: 'title',
			type:  'search',
		} );
	}

	/**
	 * Triggers meta title input.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now allows for a first parameter to be set.
	 * @access public
	 *
	 * @function
	 * @param {string} id The input id. When not set, all inputs will be triggered.
	 */
	const triggerInput = id => {

		if ( id ) {
			let el = getInputElement( id );
			el && el.dispatchEvent( new Event( 'input' ) );
		} else {
			// We don't want it to loop infinitely. Check element.id value first.
			titleInputInstances.forEach( element => element.id && triggerInput( element.id ) );
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
	 */
	const triggerCounter = id => {
		if ( id ) {
			let el = getInputElement( id );
			el && el.dispatchEvent( new CustomEvent( 'tsf-update-title-counter' ) );
		} else {
			// We don't want it to loop infinitely. Check element.id value first.
			titleInputInstances.forEach( element => element.id && triggerCounter( element.id ) );
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
	 */
	const _onUpdateTitlesTrigger = event => {

		_updateHoverPlacement( event );
		_setReferenceTitle( event );
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
	 */
	const _onUpdateCounterTrigger = event => {
		_updateCounter( event );
		_updatePixels( event );
	}

	let _enqueueTriggerInputBuffer = {};
	/**
	 * Triggers meta title input.
	 *
	 * @since 4.0.0
	 * @since 4.1.1 Added first parameter, id.
	 * @access public
	 *
	 * @function
	 * @param {string} id The input ID.
	 */
	const enqueueTriggerInput = id => {
		( id in _enqueueTriggerInputBuffer ) && clearTimeout( _enqueueTriggerInputBuffer[ id ] );
		_enqueueTriggerInputBuffer[ id ] = setTimeout( () => triggerInput( id ), 1000/60 ); // 60 fps
	}

	/**
	 * Triggers meta title update, without affecting tsfAys change listeners.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now allows for a first parameter to be set.
	 * @access public
	 *
	 * @function
	 * @param {string} id The input id. When not set, all inputs will be triggered.
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
	 * Enqueues unregistered title input triggers.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now allows for a first parameter to be set.
	 * @access public
	 *
	 * @function
	 * @param {string} id The input id. When not set, all inputs will be triggered.
	 */
	const enqueueUnregisteredInputTrigger = id => {
		( id in _unregisteredTriggerBuffer ) && clearTimeout( _unregisteredTriggerBuffer[ id ] );
		_unregisteredTriggerBuffer[ id ] = setTimeout( () => triggerUnregisteredInput( id ), 1000/60 ); // 60 fps
	}

	/**
	 * Makes user click act naturally by selecting the adjacent Title text
	 * input and move cursor all the way to the end.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now supports multiple instances.
	 * @TODO can we not just make the floaty mcfloattitle transparent to clicks?
	 * @access private
	 *
	 * @function
	 * @param {Event} event
	 */
	const _focusTitleInput = event => {

		let input = document.getElementById( event.target.dataset.for );
		if ( ! input ) return;

		let type       = event.target.classList.contains( 'tsf-title-placeholder-additions' ) ? 'additions' : 'prefix',
			inputValue = input.value;

		// Make sure the input is focussed, if it wasn't already.
		input.focus();

		switch ( event.detail ) {
			case 3:
				input.setSelectionRange( 0, inputValue.length );
				break;

			case 2:
				let start, end;
				if (
					'additions' === type && 'after' === getStateOf( input.id, 'additionPlacement' )
				||  'prefix' === type && window.isRtl
				) {
					start = inputValue.replace( /(\w+|\s+)$/u, '' ).length;
					end   = inputValue.length;
				} else {
					start = 0;
					end   = inputValue.length - inputValue.replace( /^(\s+|\w+)/u, '' ).length;
				}
				input.setSelectionRange( start, end );
				break;

			case 1:
			default:
				// Set length to end if the placeholder is clicked; to 0 otherwise (prefix clicked).
				let length = 'additions' === type && 'after' === getStateOf( input.id, 'additionPlacement' )
					? inputValue.length
					: 0;
				input.setSelectionRange( length, length );
				break;
		}
	}

	/**
	 * Prevents focus on event.
	 *
	 * @since 4.1.0
	 *
	 * @param {Event} event
	 * @return {void}
	 */
	const _preventFocus = event => event.preventDefault();

	let prevWidth = window.innerWidth;
	/**
	 * Triggers input event for titles in set intervals on window resize.
	 *
	 * This only happens if boundaries are surpassed to reduce CPU usage.
	 * This boundary is 782 pixels, because that forces input fields to change.
	 * in WordPress.
	 *
	 * This happens to all title inputs; as WordPress switches
	 * from Desktop to Mobile view at 782 pixels.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see ...\wp-admin\js\common.js
	 *
	 * @function
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
	 * Initializes the title environment.
	 *
	 * @since 4.1.0
	 * @since 4.1.1 No longer passes the event to the enqueueUnregisteredInputTrigger() callback.
	 * @access private
	 *
	 * @function
	 */
	const _initAllTitleActions = () => {

		// Triggers input changes on resize after hitting thresholds.
		window.addEventListener( 'tsf-resize', _doResize );

		// When counters are updated, trigger an input; which will reassess them.
		window.addEventListener( 'tsf-counter-updated', () => enqueueUnregisteredInputTrigger() );
	}
	/**
	 * Initializes the title input action callbacks.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @param {Element} titleInput
	 */
	const _loadTitleActions = titleInput => {

		if ( ! titleInput instanceof Element ) return;

		titleInput.addEventListener( 'input', _onUpdateTitlesTrigger );
		titleInput.addEventListener( 'tsf-update-title-counter', _onUpdateCounterTrigger );

		let hoverPrefix    = _getHoverPrefixElement( titleInput.id ),
			hoverAdditions = _getHoverAdditionsElement( titleInput.id );

		hoverPrefix.addEventListener( 'click', _focusTitleInput );
		hoverAdditions.addEventListener( 'click', _focusTitleInput );

		// Don't allow focus of the floating elements.
		hoverPrefix.addEventListener( 'mousedown', _preventFocus );
		hoverAdditions.addEventListener( 'mousedown', _preventFocus );

		_updateAdditionsValue( titleInput.id );
		_updatePrefixValue( titleInput.id );
		enqueueUnregisteredInputTrigger( titleInput.id );
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
			document.body.addEventListener( 'tsf-onload', _initAllTitleActions );
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
		enqueueUnregisteredInputTrigger, // this should've been enqueueTriggerUnregisteredInput...
	}, {
		l10n,
		untitledTitle,
		privatePrefix,
		protectedPrefix,
		stripTitleTags,
	} );
}();
window.tsfTitle.load();
