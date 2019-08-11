/**
 * This file holds The SEO Framework plugin's JS code for TSF title fields.
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
 * Holds tsfTitle values in an object to avoid polluting global namespace.
 *
 * Only one instance should act on this per window.
 * TODO Allow mulitple instances, instead (convert to transitive object)?
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
	 * @type {(Object<string, *>)|boolean|null} l10n Localized strings
	 */
	const l10n = 'undefined' !== typeof tsfTitleL10n && tsfTitleL10n;

	/**
	 * @since 4.0.0
	 * @type {(void|Element)} The input element.
	 */
	let titleInput = void 0;

	/**
	 * @since 4.0.0
	 * @access private
	 * @type {(Object<string, *>)} the query state.
	 */
	let state = {
		allowReferenceChange: true,
		useTagline:           l10n.states.useTagline,
		separator:            l10n.states.titleSeparator,
		additionPlacement:    l10n.states.additionPlacement,
		prefixPlacement:      l10n.states.prefixPlacement,
		additionValue:        tsf.escapeString( l10n.states.additionValue.trim() ),
		prefixValue:          '',
		defaultTitle:         tsf.escapeString( l10n.states.defaultTitle.trim() ),
	};

	const untitledTitle = tsf.escapeString( l10n.params.untitledTitle );

	/**
	 * @since 4.0.0
	 * @access private
	 * @type {(String)}
	 */
	let additions = '';

	/**
	 * @since 4.0.0
	 * @access private
	 * @type {(String)}
	 */
	let prefix = '';

	/**
	 * @since 4.0.0
	 * @access private
	 * @type {Element}
	 */
	const hoverPrefixElement = document.getElementById( 'tsf-title-placeholder-prefix' ) || $( '</span>' )[0];

	/**
	 * @since 4.0.0
	 * @access private
	 * @type {Element}
	 */
	const hoverAdditionsElement = document.getElementById( 'tsf-title-placeholder' ) || $( '</span>' )[0];

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
		titleInput = element;
	}

	/**
	 * Returns state.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @param {(string|undefined)} part The part to return. Leave empty to return the whole state.
	 * @return {(Object<string, *>)|*|null}
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
			case 'prefixValue':
			case 'prefixPlacement':
				updatePrefixValue();
				enqueueTriggerInput();
				break;

			case 'useTagline':
			case 'separator':
			case 'additionValue':
			case 'additionPlacement':
				updateAdditionsValue();
				enqueueTriggerInput();
				break;

			case 'allowReferenceChange':
			case 'defaultTitle':
			default:
				enqueueTriggerInput();
				break;
		}
	}

	/**
	 * Updates hover additions.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @return {undefined}
	 */
	const updateAdditionsValue = () => {

		let value = '',
			additionsValue = '';

		if ( state.useTagline )
			additionsValue = state.additionValue;

		if ( additionsValue ) {
			switch ( state.additionPlacement ) {
				case 'before':
					value = additionsValue + ' ' + state.separator + ' ';
					break;

				case 'after':
					value = ' ' + state.separator + ' ' + additionsValue;
					break;
			}
		}

		additions = value || '';
		hoverAdditionsElement.innerHTML = additions;
	}

	/**
	 * Updates hover prefix.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @return {undefined}
	 */
	const updatePrefixValue = () => {
		let value       = '',
			prefixValue = state.prefixValue.trim();

		if ( prefixValue ) {
			switch ( state.prefixPlacement ) {
				case 'before':
					value = prefixValue + ' ';
					break;

				case 'after':
					value = ' ' + prefixValue;
					break;
			}
		}

		prefix = value || '';
		hoverPrefixElement.innerHTML = prefix;
	}

	/**
	 * Updates the title hover prefix and additions placement.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	const _updateHoverPlacement = function( event ) {

		if ( ! hoverAdditionsElement && ! hoverPrefixElement )
			return;

		let $input     = $( event.target ),
			inputValue = $input.val();

		let hasAdditionsValue = !! additions.length,
			hasPrefixValue    = !! prefix.length;

		if ( ! hasAdditionsValue && hoverAdditionsElement )
			hoverAdditionsElement.style.display = 'none';

		if ( ! hasPrefixValue && hoverPrefixElement )
			hoverPrefixElement.style.display = 'none';

		if ( ! hasAdditionsValue && ! hasPrefixValue ) {
			//= Both items are emptied through settings.
			$input.css( 'text-indent', 'initial' );
			return;
		}

		if ( ! inputValue.length ) {
			//= Input is emptied.
			$input.css( 'text-indent', "initial" );
			if ( hoverAdditionsElement ) hoverAdditionsElement.style.display = 'none';
			if ( hoverPrefixElement ) hoverPrefixElement.style.display = 'none';
			return;
		}

		let outerWidth        = $input.outerWidth( true ),
			verticalPadding   = ( $input.outerHeight( true ) - $input.height() ) / 2,
			horizontalPadding = ( outerWidth - $input.innerWidth() ) / 2;

		let offsetPosition = tsf.l10n.states.isRTL ? 'right' : 'left',
			leftOffset     = ( $input.outerWidth( true ) - $input.width() ) / 2;

		let fontStyleCSS = {
			display:       $input.css( 'display' ),
			lineHeight:    $input.css( 'lineHeight' ),
			fontFamily:    $input.css( 'fontFamily' ),
			fontWeight:    $input.css( 'fontWeight' ),
			fontSize:      $input.css( 'fontSize' ),
			letterSpacing: $input.css( 'letterSpacing' ),
			paddingTop:    verticalPadding + 'px',
			paddingBottom: verticalPadding + 'px',
		};

		let $prefixElement    = $( hoverPrefixElement ),
			$additionsElement = $( hoverAdditionsElement );

		let additionsMaxWidth = 0,
			additionsOffset   = 0,
			prefixOffset      = 0,
			totalIndent       = 0,
			prefixMaxWidth    = 0;

		let elipsisWidth = 0; // TODO make this 18? x-button-Browser incompatible & indentation bugs!

		if ( hasPrefixValue ) {
			$prefixElement.css( fontStyleCSS );
			$prefixElement.css( { maxWidth: 'initial' } );
			prefixMaxWidth = $prefixElement[0].getBoundingClientRect().width;
			if ( prefixMaxWidth < elipsisWidth )
				prefixMaxWidth = 0;
		}
		if ( hasAdditionsValue ) {
			let textWidth = 0;

			(() => {
				let $offsetTest = $( '#tsf-title-offset' );
				$offsetTest.text( inputValue );
				$offsetTest.css({
					fontFamily:    fontStyleCSS.fontFamily,
					fontWeight:    fontStyleCSS.fontWeight,
					letterSpacing: fontStyleCSS.letterSpacing,
					fontSize:      fontStyleCSS.fontSize,
				});
				textWidth = $offsetTest[0].getBoundingClientRect().width;
			})();

			//= Input element width - Padding - input text width - prefix value width.
			additionsMaxWidth = $input[0].getBoundingClientRect().width - horizontalPadding - leftOffset - textWidth - prefixMaxWidth;
			if ( additionsMaxWidth < elipsisWidth ) {
				//= Add width to the prefix element, so it may stay its size, and hide the additions.
				prefixMaxWidth += additionsMaxWidth;
				additionsMaxWidth = 0;
			}
			$additionsElement.css( fontStyleCSS );
			$additionsElement.css( { 'maxWidth' : 'initial' } );

			switch ( state.additionPlacement ) {
				case 'before':
					let additionsWidth = $additionsElement[0].getBoundingClientRect().width;

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
			_css['maxWidth'] = prefixMaxWidth + "px";
			$prefixElement.css( _css );
		}

		if ( hasAdditionsValue ) {
			_css = {};
			_css[ offsetPosition ] = additionsOffset + "px";
			_css['maxWidth'] = additionsMaxWidth + "px";
			$additionsElement.css( _css );
		}

		_css = {};
		_css['text-indent'] = totalIndent + "px";
		$input.css( _css );
	}

	/**
	 * Updates the title reference.
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
	const _setReferenceTitle = ( event ) => {
		let reference = document.getElementById( 'tsf-title-reference' ),
			text      = state.allowReferenceChange && event.target.value || state.defaultTitle;

		if ( ! reference ) return;

		text = text.trim();

		if ( text.length < 1 || ! state.allowReferenceChange ) {
			text = event.target.placeholder;
		} else {
			if ( prefix.length ) {
				switch ( state.additionPlacement ) {
					case 'before':
						text = prefix + text;
						break;

					case 'after':
						text = text + prefix;
						break;
				}
			}
			if ( additions.length ) {
				switch ( state.additionPlacement ) {
					case 'before':
						text = additions + text;
						break;

					case 'after':
						text = text + additions;
						break;
				}
			}
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

		event.target.placeholder = document.getElementById( 'tsf-title-reference' ).innerText;
	}

	/**
	 * Updates the character counter bound to the input.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	const _updateCounter = ( event ) => {
		let counter   = document.getElementById( event.target.id + '_chars' ),
			reference = document.getElementById( 'tsf-title-reference' );

		if ( ! counter || ! tsfC ) return;

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
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	const _updatePixels = ( event ) => {
		let pixels    = document.getElementById( event.target.id + '_pixels' ),
			reference = document.getElementById( 'tsf-title-reference' );

		if ( ! pixels || ! tsfC ) return;

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
	 * @access public
	 *
	 * @function
	 * @return {undefined}
	 */
	const triggerInput = () => {
		$( titleInput ).trigger( 'input.tsfUpdateTitles' );
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
		$( titleInput ).trigger( 'tsf-update-title-counter' );
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
	const _onUpdateTitlesTrigger = ( event ) => {

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
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	const _onUpdateCounterTrigger = ( event ) => {
		_updateCounter( event );
		_updatePixels( event );
	}

	let _enqueueTriggerInputBuffer = 0;
	/**
	 * Triggers meta title input.
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
	 * Triggers meta title update, without affecting tsfAys change listeners.
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
	 * Enqueues unregistered title input triggers.
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

	/**
	 * Makes user click act naturally by selecting the adjacent Title text
	 * input and move cursor all the way to the end.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	const _focusTitleInput = ( event ) => {

		let $input = $( event.target ).siblings( 'input' ).first();

		if ( $input.length ) {

			let input = $input[0];

			// TODO buffer? It now flickers...
			input.focus();

			switch ( event.detail ) {
				case 3:
					input.setSelectionRange( 0, input.value.length );
					break;

				case 2:
					let start, end;
					if (
						event.target.id === 'tsf-title-placeholder' && state.additionPlacement === 'after'
					||  event.target.id === 'tsf-title-placeholder-prefix' && tsf.l10n.states.isTRL
					) {
						start = input.value.replace( /(\w+|\s+)$/u, '' ).length;
						end   = input.value.length;
					} else {
						start = 0;
						end   = input.value.length - input.value.replace( /^(\s+|\w+)/u, '' ).length;
					}
					input.setSelectionRange( start, end );
					break;

				case 1:
				default:
					// Set length to end if the placeholder is clicked; to 0 otherwise (prefix clicked).
					let length = event.target.id === 'tsf-title-placeholder' && state.additionPlacement === 'after' ? input.value.length : 0;
					input.setSelectionRange( length, length );
					break;
			}
		}
	}

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
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initTitles = () => {

		// Fowards focus from hover-item clicks.
		hoverPrefixElement.addEventListener( 'click', _focusTitleInput );
		hoverAdditionsElement.addEventListener( 'click', _focusTitleInput );

		// Triggers input changes on resize after hitting thresholds.
		$( document ).on( 'wp-window-resized', _doResize );

		// When counters are updated, trigger an input; which will reassess them.
		$( window ).on( 'tsf-counter-updated', enqueueTriggerInput );
	}

	/**
	 * Initializes the title input action callbacks.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _loadTitleActions = () => {

		if ( ! titleInput instanceof Element ) return;

		$( titleInput ).on( 'input.tsfUpdateTitles', _onUpdateTitlesTrigger );
		$( titleInput ).on( 'tsf-update-title-counter', _onUpdateCounterTrigger );

		updateAdditionsValue();
		updatePrefixValue();
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
			$( document.body ).on( 'tsf-onload', _initTitles );

			// the setInputElement() must've been called here.
			$( document.body ).on( 'tsf-ready', _loadTitleActions );
		},
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
		l10n,
		untitledTitle
	} );
}( jQuery );
jQuery( window.tsfTitle.load );
