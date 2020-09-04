/**
 * This file holds The SEO Framework plugin's JS code for TSF title fields.
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
 * Holds tsfTitle values in an object to avoid polluting global namespace.
 *
 * Only one instance should act on this per window.
 *
 * @since 4.0.0
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfTitle = function( $ ) {

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
	let states = {};

	/**
	 * @since 4.1.0
	 * @access private
	 * @type {(Map<string,string>)} The input element instances.
	 */
	let additionsStack = new Map();
	/**
	 * @since 4.1.0
	 * @access private
	 * @type {(Map<string,string>)} The input element instances.
	 */
	let prefixStack = new Map();

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
	const _getHoverPrefixElement = id => document.getElementById( 'tsf-title-placeholder-prefix_' + id ) || document.createElement( 'span' );
	/**
	 * @since 4.1.0
	 * @access private
	 * @return {Element}
	 */
	const _getHoverAdditionsElement = id => document.getElementById( 'tsf-title-placeholder-additions_' + id ) || document.createElement( 'span' );

	/**
	 * Sets input element for all listeners. Must be called prior interacting with this object.
	 * Resets the state for the input ID.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now creates an instance in a map this object, and returns it.
	 * @access public
	 *
	 * @param {Element} element
	 * @return {undefined}
	 */
	const setInputElement = ( element ) => {
		titleInputInstances.set( element.id, element );
		states[ element.id ] = {
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

		for ( const id of titleInputInstances.keys() ) {
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
	 * @return {(Object<string, *>)|*|null}
	 */
	const getState = part => {
		tsf.deprecatedFunc( 'tsfTitle.getState()', '4.1.0', 'tsfTitle.getStateOf()' );
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
	const getStateOf = ( id, part ) => {
		// Backward compat.
		if ( 'useTagline' === part ) {
			tsf.deprecatedFunc( 'tsfTitle.getStateOf( id, \'useTagline\' )', '4.1.0', 'tsfTitle.getStateOf( id, \'addAdditions\' )' );
			part = 'addAdditions';
		}
		return part ? ( part in states[ id ] ? states[ id ][ part ] : void 0 ) : states[ id ];
	}

	/**
	 * Updates state.
	 *
	 * There's no need to escape the input, it may be double-escaped if you do so.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Deprecated.
	 * @access public
	 * @deprecated 4.1.0
	 * @see updateStateOf()
	 *
	 * @param {string} part  The state index to change.
	 * @param {*}      value The value to set the state to.
	 * @return {undefined}
	 */
	const updateState = ( part, value ) => {
		tsf.deprecatedFunc( 'tsfTitle.updateState()', '4.1.0', 'tsfTitle.updateStateOf()' );
		return updateStateOf( getLegacyElementId(), part, value );
	}

	/**
	 * Updates state of ID.
	 *
	 * There's no need to escape the input, it may be double-escaped if you do so.
	 *
	 * @since 4.1.0
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

		// Backward compat.
		if ( 'useTagline' === part ) {
			tsf.deprecatedFunc( 'tsfTitle.updateStateOf( id, \'useTagline\' )', '4.1.0', 'tsfTitle.updateStateOf( id, \'addAdditions\' )' );
			part = 'addAdditions';
		}

		states[ id ][ part ] = value;

		switch ( part ) {
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

			case 'useSocialTagline':
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
	 * @access public
	 * @TODO add a "but" ({String|Array})
	 *
	 * @param {string} part  The state index to change.
	 * @param {*}      value The value to set the state to.
	 * @return {undefined}
	 */
	const updateStateAll = ( part, value ) => {
		titleInputInstances.forEach( element => {
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
		let references = [ document.getElementById( 'tsf-title-reference_' + id ) ];

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
	const _getTitleNaReferences = id => [ document.getElementById( 'tsf-title-noadditions-reference_' + id ) ];

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
		let references   = _getTitleReferences( event.target.id ),
			referencesNa = _getTitleNaReferences( event.target.id );

		if ( ! references[0] || ! referencesNa[0] ) return;

		let allowReferenceChange = getStateOf( event.target.id, 'allowReferenceChange' ),
			text                 = allowReferenceChange && event.target.value || getStateOf( event.target.id, 'defaultTitle' ) || '',
			textNa               = '';

		text   = text.trim();
		textNa = text;

		if ( text.length && allowReferenceChange ) {
			let prefix    = _getPrefixValue( event.target.id ),
				additions = _getAdditionsValue( event.target.id );

			if ( prefix.length ) {
				switch ( getStateOf( event.target.id, 'prefixPlacement' ) ) {
					case 'before':
						if ( tsf.l10n.states.isRTL ) {
							text = text + prefix;
						} else {
							text = prefix + text;
						}
						break;

					case 'after':
						if ( tsf.l10n.states.isRTL ) {
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

		let referenceValue   = tsf.escapeString( tsf.decodeEntities( tsf.sDoubleSpace( text.trim() ) ) ),
			referenceNaValue = tsf.escapeString( tsf.decodeEntities( tsf.sDoubleSpace( textNa.trim() ) ) );

		references.forEach( reference => {
			// We require the event below when adjusting some states... Don't uncomment this.
			// if ( reference.innerHTML === referenceValue ) return;

			reference.innerHTML = referenceValue;
			// Fires change event. Defered to another thread.
			setTimeout( () => { reference.dispatchEvent( new Event( 'change' ) ) }, 0 );
		} );

		referencesNa.forEach( referenceNa => {
			// We require the event below when adjusting some states... Don't uncomment this.
			// if ( referenceNa.innerHTML === referenceNaValue ) return;

			referenceNa.innerHTML = referenceNaValue;
			// Fires change event. Defered to another thread.
			setTimeout( () => { referenceNa.dispatchEvent( new Event( 'change' ) ) }, 0 );
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
	 * @return {undefined}
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
					value = additionsValue + ' ' + separator + ' ';
					break;

				case 'after':
					value = ' ' + separator + ' ' + additionsValue;
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
	 * @return {undefined}
	 */
	const _updatePrefixValue = id => {
		let value       = '',
			prefixValue = getStateOf( id, 'prefixValue' );

		if ( prefixValue ) {
			switch ( getStateOf( id, 'prefixPlacement' ) ) {
				case 'before':
					if ( tsf.l10n.states.isRTL ) {
						value = ' ' + prefixValue;
					} else {
						value = prefixValue + ' ';
					}
					break;

				case 'after':
					if ( tsf.l10n.states.isRTL ) {
						value = prefixValue + ' ';
					} else {
						value = ' ' + prefixValue;
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
	 * @access private
	 * TODO dejQuery this? We need to use stuff like .cssText then...
	 *
	 * @function
	 * @param {Event} event
	 * @return {undefined}
	 */
	const _updateHoverPlacement = event => {

		let hoverAdditionsElement = _getHoverAdditionsElement( event.target.id ),
			hoverPrefixElement    = _getHoverPrefixElement( event.target.id );

		if ( ! hoverAdditionsElement && ! hoverPrefixElement )
			return;

		const input      = event.target,
			  inputValue = event.target.value;

		let prefix    = _getPrefixValue( event.target.id ),
			additions = _getAdditionsValue( event.target.id );

		let hasPrefixValue    = !! prefix.length,
			hasAdditionsValue = !! additions.length;

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

		const $input = $( input );

		let outerWidth        = $input.outerWidth( true ),
			verticalPadding   = ( $input.outerHeight( true ) - $input.height() ) / 2,
			topMargin         = parseInt( $input.css( 'marginTop' ) ) / 2,
			bottomMargin      = parseInt( $input.css( 'marginBottom' ) ) / 2,
			horizontalPadding = ( outerWidth - $input.innerWidth() ) / 2;

		let offsetPosition = tsf.l10n.states.isRTL ? 'right' : 'left',
			leftOffset     = ( outerWidth - $input.width() ) / 2;

		let fontStyleCSS = {
			display:       $input.css( 'display' ),
			lineHeight:    $input.css( 'lineHeight' ),
			fontFamily:    $input.css( 'fontFamily' ),
			fontWeight:    $input.css( 'fontWeight' ),
			fontSize:      $input.css( 'fontSize' ),
			letterSpacing: $input.css( 'letterSpacing' ),
			marginTop:     topMargin + 'px',
			marginBottom:  bottomMargin + 'px',
			paddingTop:    verticalPadding + 'px',
			paddingBottom: verticalPadding + 'px',
		};

		const $prefixElement    = $( hoverPrefixElement ),
			  $additionsElement = $( hoverAdditionsElement );

		let additionsMaxWidth = 0,
			additionsOffset   = 0,
			prefixOffset      = 0,
			totalIndent       = 0,
			prefixMaxWidth    = 0;

		// TODO make this 18? x-button-Browser incompatible & indentation bugs! We should only calculate this when they show..
		let elipsisWidth = 0;

		if ( hasPrefixValue ) {
			$prefixElement.css( fontStyleCSS );
			$prefixElement.css( { maxWidth: 'initial' } );
			prefixMaxWidth = hoverPrefixElement.getBoundingClientRect().width;
			if ( prefixMaxWidth < elipsisWidth ) // useless code since elipsis is always 0.
				prefixMaxWidth = 0;
		}
		if ( hasAdditionsValue ) {
			let textWidth = 0;

			(() => {
				let offSetElement  = document.getElementById( `tsf-title-offset_${event.target.id}` ),
					$offsetElement = $( offSetElement );
				$offsetElement.text( inputValue );
				$offsetElement.css({
					fontFamily:    fontStyleCSS.fontFamily,
					fontWeight:    fontStyleCSS.fontWeight,
					letterSpacing: fontStyleCSS.letterSpacing,
					fontSize:      fontStyleCSS.fontSize,
				});
				textWidth = offSetElement.getBoundingClientRect().width;
			})();

			//= Input element width - Padding - input text width - prefix value width.
			additionsMaxWidth = input.getBoundingClientRect().width - horizontalPadding - leftOffset - textWidth - prefixMaxWidth;
			if ( additionsMaxWidth < elipsisWidth ) {
				//= Add width to the prefix element, so it may stay its size, and hide the additions.
				prefixMaxWidth += additionsMaxWidth;
				additionsMaxWidth = 0;
			}
			$additionsElement.css( fontStyleCSS );
			$additionsElement.css( { 'maxWidth' : 'initial' } );

			switch ( getStateOf( event.target.id, 'additionPlacement' ) ) {
				case 'before':
					let additionsWidth = hoverAdditionsElement.getBoundingClientRect().width;

					additionsWidth = additionsMaxWidth < additionsWidth ? additionsMaxWidth : additionsWidth;

					if ( additionsWidth < elipsisWidth )
						additionsWidth = 0;

					additionsMaxWidth = additionsWidth;

					totalIndent     += additionsMaxWidth;
					prefixOffset    += additionsMaxWidth;
					additionsOffset += leftOffset;
					break;

				case 'after':
					additionsOffset += leftOffset + textWidth + prefixMaxWidth;
					break;
			}
		}
		prefixOffset  += leftOffset;
		prefixMaxWidth = prefixMaxWidth < 0 ? 0 : prefixMaxWidth;
		totalIndent   += prefixMaxWidth;

		let _css;

		if ( hasPrefixValue ) {
			_css = {};
			_css[ offsetPosition ] = prefixOffset + "px";
			_css['maxWidth'] = prefixMaxWidth  + "px";
			$prefixElement.css( _css );
		}

		if ( hasAdditionsValue ) {
			_css = {};
			_css[ offsetPosition ] = additionsOffset + "px";
			_css['maxWidth'] = additionsMaxWidth + "px";
			$additionsElement.css( _css );
		}

		_css = {};
		_css['text-indent'] = Math.round( totalIndent ) + "px";
		$input.css( _css );
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
		event.target.placeholder = _getTitleReferences( event.target.id )[0].innerText;
	}

	/**
	 * Updates the character counter bound to the input.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @param {Event} event
	 * @return {undefined}
	 */
	const _updateCounter = event => {
		if ( ! ( 'tsfC' in window ) ) return;

		let counter   = document.getElementById( event.target.id + '_chars' ),
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
	 * @return {undefined}
	 */
	const _updatePixels = event => {
		if ( ! ( 'tsfC' in window ) ) return;

		let pixels    = document.getElementById( event.target.id + '_pixels' ),
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
	 * @return {undefined}
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
	 * @return {undefined}
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
	 * @return {undefined}
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
	 * @return {undefined}
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
	 * @return {undefined}
	 */
	const enqueueTriggerInput = id => {
		( id in _enqueueTriggerInputBuffer ) && clearTimeout( _enqueueTriggerInputBuffer[ id ] );
		_enqueueTriggerInputBuffer[ id ] = setTimeout( () => triggerInput( id ), 10 );
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
	 * Enqueues unregistered title input triggers.
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

	/**
	 * Makes user click act naturally by selecting the adjacent Title text
	 * input and move cursor all the way to the end.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now supports multiple instances.
	 * @access private
	 *
	 * @function
	 * @param {Event} event
	 * @return {undefined}
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
				||  'prefix' === type && tsf.l10n.states.isRTL
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
				let length = 'additions' === type && 'after' === getStateOf( input.id, 'additionPlacement' ) ? inputValue.length : 0;
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
	 * Initializes the title environment.
	 *
	 * @since 4.1.0
	 * @since 4.1.1 No longer passes the event to the enqueueUnregisteredInputTrigger() callback.
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initAllTitleActions = () => {

		// Triggers input changes on resize after hitting thresholds.
		// We can't bind to jQuery event listeners via native ES :(
		$( document ).on( 'wp-window-resized', _doResize );

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
	 * @return {undefined}
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
		 * @return {undefined}
		 */
		load: () => {
			document.body.addEventListener( 'tsf-onload', _initAllTitleActions );
		},
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
		l10n,
		untitledTitle,
		privatePrefix,
		protectedPrefix,
		stripTitleTags,
	} );
}( jQuery );
window.tsfTitle.load();
