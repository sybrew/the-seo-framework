/**
 * This file holds The SEO Framework plugin's JS code for TSF title fields.
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
 * Holds tsfTitle values in an object to avoid polluting global namespace.
 *
 * Only one instance should act on this per window.
 *
 * @since 4.0.0
 *
 * @constructor
 */
window.tsfTitle = function () {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 4.0.0
	 * @access public
	 * @type {(Object<string,*>)|boolean|null} l10n Localized strings.
	 */
	const l10n = tsfTitleL10n;

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
	function _getAdditionsValue( id ) {
		return additionsStack.get( id ) || '';
	}
	/**
	 * @since 4.1.0
	 * @internal Use getStateOf() instead.
	 * @access private
	 *
	 * @param {String} id
	 * @param {String} value
	 * @return {String} The prefix value.
	 */
	function _getPrefixValue( id ) {
		return prefixStack.get( id ) || '';
	}
	/**
	 * @since 4.1.0
	 * @internal Use updateStateOf() instead.
	 * @access private
	 *
	 * @param {String} id
	 * @param {String} value
	 * @return {String} The new value.
	 */
	function _setAdditionsValue( id, value ) {
		return additionsStack.set( id, value ) && _getAdditionsValue( id );
	}
	/**
	 * @since 4.1.0
	 * @internal Use updateStateOf() instead.
	 * @access private
	 *
	 * @param {String} id
	 * @param {String} value
	 * @return {String} The new value.
	 */
	function _setPrefixValue( id, value ) {
		return prefixStack.set( id, value ) && _getPrefixValue( id );
	}

	/**
	 * @since 4.1.0
	 * @access private
	 * @return {Element}
	 */
	function _getHoverPrefixElement( id ) {
		return document.getElementById( `tsf-title-placeholder-prefix_${id}` ) || document.createElement( 'span' );
	}
	/**
	 * @since 4.1.0
	 * @access private
	 * @return {Element}
	 */
	function _getHoverAdditionsElement( id ) {
		return document.getElementById( `tsf-title-placeholder-additions_${id}` ) || document.createElement( 'span' );
	}

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
	function setInputElement( element ) {
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
	function getInputElement( id ) {
		return titleInputInstances.get( id );
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
	function updateStateAll( part, value, except ) {

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
	function _getTitleReferences( id ) {
		return [ document.getElementById( `tsf-title-reference_${id}` ) ];
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
	function _getTitleNaReferences( id ) {
		return [ document.getElementById( `tsf-title-noadditions-reference_${id}` ) ];
	}

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
	 * @param {Event} event
	 * @return {HTMLElement[]}
	 */
	function _setReferenceTitle( event ) {
		const references   = _getTitleReferences( event.target.id ),
			  referencesNa = _getTitleNaReferences( event.target.id );

		if ( ! references[0] || ! referencesNa[0] ) return;

		const allowReferenceChange = getStateOf( event.target.id, 'allowReferenceChange' );

		let text = tsf.coalesceStrlen( allowReferenceChange && event.target.value.trim() )
			?? tsf.coalesceStrlen( getStateOf( event.target.id, 'defaultTitle' ) )
			?? '';
		let textNa = text;

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
			// Fires change event. Deferred to another thread.
			setTimeout( () => { reference.dispatchEvent( changeEvent ) }, 0 );
		} );

		referencesNa.forEach( referenceNa => {
			// We require the event below when adjusting some states... Don't uncomment this.
			// if ( referenceNa.innerHTML === referenceNaValue ) return;

			referenceNa.innerHTML = referenceNaValue;
			// Fires change event. Deferred to another thread.
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
	function _updateAdditionsValue( id ) {
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
	function _updatePrefixValue( id ) {
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
	 * @param {Event} event
	 */
	function _updateHoverPlacement( event ) {

		const hoverAdditionsElement = _getHoverAdditionsElement( event.target.id ),
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
			// Both items are emptied through settings.
			input.style.textIndent = 'initial';
			return;
		}

		if ( ! inputValue.length ) {
			// Input is emptied.
			input.style.textIndent = 'initial';
			if ( hoverPrefixElement )
				hoverPrefixElement.style.display = 'none';
			if ( hoverAdditionsElement )
				hoverAdditionsElement.style.display = 'none';
			return;
		}

		const inputStyles = getComputedStyle( input ),
			  inputRect   = input.getBoundingClientRect();

		// Quick and dirty. getComputedStyle() always gives us pixels to work with.
		const paddingRight  = parseFloat( inputStyles.paddingRight ),
			  paddingLeft   = parseFloat( inputStyles.paddingLeft ),
			  borderRight   = parseFloat( inputStyles.borderRightWidth ),
			  borderLeft    = parseFloat( inputStyles.borderLeftWidth ),
			  marginRight   = parseFloat( inputStyles.marginRight ),
			  marginLeft    = parseFloat( inputStyles.marginLeft );

		const offsetPosition = window.isRtl ? 'right' : 'left',
			  corPaddingProp = window.isRtl ? 'paddingLeft' : 'paddingRight',
			  leftOffset     = paddingLeft + borderLeft + marginLeft,
			  rightOffset    = paddingRight + borderRight + marginRight;

		const fontStyleCSS = new Map();

		fontStyleCSS.set( 'border', '0 solid transparent' );

		[
			'display',
			'lineHeight',
			'fontFamily',
			'fontWeight',
			'fontSize',
			'letterSpacing',
			'marginTop',
			'marginBottom',
			'paddingTop',
			'paddingBottom',
			'borderTopWidth',
			'borderBottomWidth',
			'verticalAlign',
			'boxSizing',
			'textTransform',
		].forEach(
			type => {
				fontStyleCSS.set( type, inputStyles?.[ type ] || '' );
			}
		);

		const offsetElement = document.getElementById( `tsf-title-offset_${event.target.id}` );
		offsetElement.textContent = inputValue;
		Object.assign(
			offsetElement.style,
			{
				fontFamily:    fontStyleCSS.get( 'fontFamily' ) || '',
				fontWeight:    fontStyleCSS.get( 'fontWeight' ) || '',
				fontSize:      fontStyleCSS.get( 'fontSize' ) || '',
				letterSpacing: fontStyleCSS.get( 'letterSpacing' ) || '',
				textTransform: fontStyleCSS.get( 'textTransform' ) || '',
			},
		);
		const textWidth = offsetElement.getBoundingClientRect().width;

		const oneCh              = parseFloat( fontStyleCSS.get( 'fontSize' ) ) || 0,
			  overflowCorrection = oneCh * .33;

		let additionsMaxWidth = 0,
			additionsOffset   = 0,
			additionsCorPad   = 0,
			prefixMaxWidth    = 0,
			prefixOffset      = 0,
			prefixCorPad      = 0,
			totalIndent       = 0;

		let prefixWidth    = 0,
			additionsWidth = 0;

		// Additions collapse before the prefix. Hence, we do rudimentary prefix calculation first.
		// We'll calculate the prefix collapsing later, but only when the additions are less than 0 wide.
		if ( hasPrefixValue ) {
			// Reset to recalculate intended width.
			Object.assign(
				hoverPrefixElement.style,
				Object.fromEntries( fontStyleCSS.entries() ),
				{ maxWidth: 'initial' },
			);
			prefixWidth = hoverPrefixElement.getBoundingClientRect().width - ( hoverPrefixElement.dataset.tsfCorPad || 0 );

			prefixMaxWidth = prefixWidth;
			prefixOffset  += leftOffset; // rightOffset for RTL? -> difficult to determine?
		}

		if ( hasAdditionsValue ) {
			// Reset to recalculate intended width.
			Object.assign(
				hoverAdditionsElement.style,
				Object.fromEntries( fontStyleCSS.entries() ),
				{ maxWidth: 'initial' },
			);
			additionsWidth = hoverAdditionsElement.getBoundingClientRect().width - ( hoverAdditionsElement.dataset.tsfCorPad || 0 );

			switch ( getStateOf( event.target.id, 'additionPlacement' ) ) {
				case 'before':
					additionsMaxWidth = inputRect.width - rightOffset - paddingLeft - borderLeft - textWidth - prefixMaxWidth;
					// At least 0, and don't grow beyond the actual width.
					additionsMaxWidth = Math.max( 0, Math.min( additionsMaxWidth, additionsWidth ) );
					// If the maxWidth is lower than the initial width (minus corrective padding), apply padding to push back the text a bit.
					additionsCorPad = additionsMaxWidth < additionsWidth ? overflowCorrection : 0;

					totalIndent     += additionsMaxWidth;
					prefixOffset    += additionsMaxWidth;
					additionsOffset += leftOffset;
					break;

				case 'after':
					additionsMaxWidth = inputRect.width - leftOffset - paddingRight - borderRight - textWidth - prefixMaxWidth;
					// At least 0, and don't grow beyond the actual width.
					additionsMaxWidth = Math.max( 0, Math.min( additionsMaxWidth, additionsWidth ) );
					additionsOffset  += leftOffset + textWidth + prefixMaxWidth;
					break;
			}
		}

		if ( hasPrefixValue ) {
			if ( ! additionsMaxWidth || ! hasAdditionsValue ) {
				// Collapse Prefix.
				prefixMaxWidth = inputRect.width - leftOffset - paddingRight - borderRight - textWidth;
				// At least 0, and don't grow beyond the actual width.
				prefixMaxWidth = Math.max( 0, Math.min( prefixMaxWidth, prefixWidth ) );
				// If the maxWidth is lower than the initial width (minus corrective padding), apply padding to push back the text a bit.
				prefixCorPad = additionsMaxWidth < additionsWidth ? overflowCorrection : 0;
			}

			totalIndent += prefixMaxWidth;

			Object.assign(
				hoverPrefixElement.style,
				{
					[offsetPosition]: `${prefixOffset}px`,
					maxWidth:         `${prefixMaxWidth}px`,
					[corPaddingProp]: `${prefixCorPad}px`,
					visibility:        prefixMaxWidth < oneCh ? 'hidden' : 'visible',
				},
			);
			hoverPrefixElement.dataset.tsfCorPad = prefixCorPad;
		}

		if ( hasAdditionsValue ) {
			Object.assign(
				hoverAdditionsElement.style,
				{
					[offsetPosition]: `${additionsOffset}px`,
					maxWidth:         `${additionsMaxWidth}px`,
					[corPaddingProp]: `${additionsCorPad}px`,
					visibility:        additionsMaxWidth < oneCh ? 'hidden' : 'visible',
				},
			);
			hoverAdditionsElement.dataset.tsfCorPad = additionsCorPad;
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
	 * @param {Event} event
	 */
	function _updatePlaceholder( event ) {
		event.target.placeholder = _getTitleReferences( event.target.id )[0].textContent;
	}

	/**
	 * Updates the character counter bound to the input.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @param {Event} event
	 */
	function _updateCounter( event ) {

		const counter   = document.getElementById( `${event.target.id}_chars` ),
			  reference = _getTitleReferences( event.target.id )[0];

		if ( ! counter ) return;

		tsfC?.updateCharacterCounter( {
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
	 * @param {Event} event
	 */
	function _updatePixels( event ) {

		const pixels    = document.getElementById( `${event.target.id}_pixels` ),
			  reference = _getTitleReferences( event.target.id )[0];

		if ( ! pixels ) return;

		tsfC?.updatePixelCounter( {
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
	 * @param {string} id The input id. When not set, all inputs will be triggered.
	 */
	function triggerInput( id ) {

		if ( id ) {
			getInputElement( id )?.dispatchEvent( new Event( 'input' ) );
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
	 * @param {string} id The input id. When not set, all inputs will be triggered.
	 */
	function triggerCounter( id ) {
		if ( id ) {
			getInputElement( id )?.dispatchEvent( new CustomEvent( 'tsf-update-title-counter' ) );
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
	 *
	 * @param {Event} event
	 */
	function _onUpdateTitlesTrigger( event ) {

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
	 * @param {Event} event
	 */
	function _onUpdateCounterTrigger( event ) {
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
	 * @param {string} id The input ID.
	 */
	function enqueueTriggerInput( id ) {
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
	 * Enqueues unregistered title input triggers.
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
	 * Makes user click act naturally by selecting the adjacent Title text
	 * input and move cursor all the way to the end.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now supports multiple instances.
	 * @TODO can we not just make the floaty mcfloattitle transparent to clicks?
	 * @access private
	 *
	 * @param {Event} event
	 */
	function _focusTitleInput( event ) {

		const input = document.getElementById( event.target.dataset.for );

		if ( ! input ) return;

		const type       = event.target.classList.contains( 'tsf-title-placeholder-additions' ) ? 'additions' : 'prefix',
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
					|| 'prefix' === type && window.isRtl
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
	function _preventFocus( event ) {
		return event.preventDefault();
	}

	/**
	 * Triggers input event for titles on window resize.
	 *
	 * @since 4.0.0
	 * @since 5.1.0 Now always triggers unregistered input to support subpixel
	 *              layout shifting calculations when zooming in or out.
	 *              The title overflow boundaries may also be dynamically hit on
	 *              different screen sizes, and this must be accounted for.
	 * @access private
	 * @todo rename this to "onResize"?
	 * @see ...\wp-admin\js\common.js
	 */
	function _doResize() {
		triggerUnregisteredInput();
	}

	/**
	 * Initializes the title environment.
	 *
	 * @since 4.1.0
	 * @since 4.1.1 No longer passes the event to the enqueueUnregisteredInputTrigger() callback.
	 * @access private
	 */
	function _initAllTitleActions() {

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
	 * @param {Element} titleInput
	 */
	function _loadTitleActions( titleInput ) {

		if ( ! titleInput instanceof Element ) return;

		titleInput.addEventListener( 'input', _onUpdateTitlesTrigger );
		titleInput.addEventListener( 'tsf-update-title-counter', _onUpdateCounterTrigger );

		const hoverPrefix    = _getHoverPrefixElement( titleInput.id ),
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
		enqueueUnregisteredInputTrigger, // FIXME: this should've been enqueueTriggerUnregisteredInput... deprecate in TSF 5.2
	}, {
		l10n,
		untitledTitle,
		privatePrefix,
		protectedPrefix,
		stripTitleTags,
	} );
}();
window.tsfTitle.load();
