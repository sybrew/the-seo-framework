/**
 * This file holds The SEO Framework plugin's JS code for Primary Term Selection.
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
 * Holds tsfPT (tsf primary term) values in an object to avoid polluting global namespace.
 *
 * This is a self-constructed function assigned as an object.
 *
 * @since 3.1.0
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfPT = function( $ ) {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 4.0.0
	 * @access public
	 * @type {(Object<string, *>)|boolean|null} l10n Localized strings
	 */
	const l10n = 'undefined' !== typeof tsfPTL10n && tsfPTL10n;

	/**
	 * Initializes primary term selection.
	 *
	 * @since 3.0.0
	 * @since 3.0.4 1 : Added postbox toggle event listeners for help display correction.
	 *              2 : Added tab visibility checkers.
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initPrimaryTerm = () => {

		if ( ! Object.keys( l10n.taxonomies ).length ) return;

		let taxonomies    = l10n.taxonomies,
			inputTemplate = wp.template( 'tsf-primary-term-selector' ),
			helpTemplate  = wp.template( 'tsf-primary-term-selector-help' );

		let termSelector = document.createElement( 'span' );

		termSelector.classList.add( 'tsf-primary-term-selector', 'tsf-set-primary-term' );

		(() => {
			let radio = document.createElement( 'input' );
			radio.setAttribute( 'type', 'radio' );
			termSelector.appendChild( radio );
		})();

		let input$         = {},
			checked$       = {},
			uniqueChecked$ = {},
			box$           = {},
			primaries      = {};

		const nsAction = ( action, taxonomy ) => action + '.tsfShowPrimary' + taxonomy;

		const addInput = ( taxonomy ) => {
			let $wrap    = $( '#' + taxonomy + 'div' ),
				template = inputTemplate( { 'taxonomy' : taxonomies[ taxonomy ] } );
			$wrap.append( template );
		}
		const addHelp = ( taxonomy ) => {
			let $wrap    = $( '#taxonomy-' + taxonomy ),
				template = helpTemplate( { 'taxonomy' : taxonomies[ taxonomy ] } ),
				$ulChild  = $wrap.children( 'ul:first' );

			if ( $ulChild.length ) {
				$( template ).insertAfter( $ulChild ); // Maintain tab order.
			} else {
				$wrap.prepend( template );
			}
			tsfTT.triggerReset();
			fixHelpPos( taxonomy );
		}
		const fixHelpPos = ( taxonomy ) => {
			let wrap = document.getElementById( 'taxonomy-' + taxonomy ),
				tabs = wrap.querySelectorAll( '.tabs-panel' );

			let postbox = wrap.closest( '.postbox' );
			if ( postbox && postbox.classList.contains( 'closed' ) ) return;

			let tab = [].slice.call( tabs ).filter( ( el ) => {
				return el.offsetWidth > 0 || el.offsetHeight > 0 || el.getClientRects().length > 0;
			} )[0];

			if ( ! tab ) return;

			let offset = tab.scrollHeight > tab.clientHeight
					   ? tab.offsetWidth - tab.clientWidth + 25 - 2 // 2px is padding or something?
					   : 25;

			if ( tsf.l10n.states.isRTL ) {
				wrap.querySelector( '.tsf-primary-term-selector-help-wrap' ).style.left = offset + 'px';
			} else {
				wrap.querySelector( '.tsf-primary-term-selector-help-wrap' ).style.right = offset + 'px';
			}
		}
		const fixHelpPosOnTabToggle = ( event ) => {
			fixHelpPos( event.data.taxonomy );
		}
		const createSelector = ( taxonomy ) => {
			let selector = termSelector.cloneNode( true );
			selector.setAttribute( 'title', taxonomies[ taxonomy ].i18n.makePrimary );
			selector.setAttribute( 'aria-label', taxonomies[ taxonomy ].i18n.makePrimary );
			return selector;
		}
		const setPostValue = ( taxonomy, value ) => {
			let element = document.getElementById( 'autodescription[_primary_term_' + taxonomy + ']' );
			if ( element && element instanceof Element )
				element.value = value || 0;
		}

		const getBox = ( taxonomy, reset ) => {
			if ( ! reset && box$[ taxonomy ] )
				return box$[ taxonomy ];

			box$[ taxonomy ] = $( '#' + taxonomy + 'checklist, #' + taxonomy + 'checklist-pop' );
			return box$[ taxonomy ];
		}
		const getInputWithVal = ( taxonomy, value ) => input$[ taxonomy ].filter( '[value="' + value + '"]' );

		const makePrimary = ( taxonomy, value ) => {
			let $label = getInputWithVal( taxonomy, value ).closest( 'label' );
			if ( $label.length ) {
				$label.addClass( 'tsf-is-primary-term' );
				$label.find( '.tsf-set-primary-term' ).each( function( index, e ) {
					e.setAttribute( 'title', taxonomies[ taxonomy ].i18n.primary );
					e.setAttribute( 'aria-label', taxonomies[ taxonomy ].i18n.primary );
					e.querySelector( 'input' ).checked = true;
				} );
				setPostValue( taxonomy, value );
				primaries[ taxonomy ] = value;
			} else {
				makeFirstPrimary( taxonomy );
			}
		}
		const unsetPrimaries = ( taxonomy ) => {
			let $label = getBox( taxonomy ).find( 'label' );
			$label.removeClass( 'tsf-is-primary-term' );
			$label.find( '.tsf-set-primary-term' ).each( function( index, e ) {
				e.setAttribute( 'title', taxonomies[ taxonomy ].i18n.makePrimary );
				e.setAttribute( 'aria-label', taxonomies[ taxonomy ].i18n.makePrimary );
				e.querySelector( 'input' ).checked = false;
			} );
			setPostValue( taxonomy, 0 );
			primaries[ taxonomy ] = 0;
		}
		const makeFirstPrimary = ( taxonomy ) => {
			let $checked = uniqueChecked$[ taxonomy ].first(),
				value;
			if ( $checked.length ) {
				value = $checked.val() || '';
				makePrimary( taxonomy, value );
				primaries[ taxonomy ] = value;
			} else {
				primaries[ taxonomy ] = 0;
			}
		}

		const setPrimary = ( event ) => {
			let taxonomy = event.data.taxonomy;

			if ( event.target instanceof HTMLInputElement ) {
				// Stop the loop.
				event.stopPropagation();

				$( event.target ).closest( '.tsf-set-primary-term' ).trigger( nsAction( 'click', taxonomy ) );
				// Forward default: Check the button.
				return true;
			}

			unsetPrimaries( taxonomy );
			makePrimary( taxonomy, $( event.target ).closest( 'label' ).find( 'input[type=checkbox]' ).val() );

			//= Stop default, don't deselect the term.
			return false;
		}
		const toggleShowSwitch = ( event ) => {
			let taxonomy = event.data.taxonomy;

			if ( event.target.checked ) {
				addCheckedNode( taxonomy, event.target );
				appendButton( taxonomy, event.target );
			} else {
				removeCheckedNode( taxonomy, event.target );
				removeButton( taxonomy, event.target );
			}

			switch ( uniqueChecked$[ taxonomy ].length ) {
				case 0:
					unsetPrimaries( taxonomy );
					break;

				case 1:
					makeFirstPrimary( taxonomy );
					break;
			}
		}
		const appendButton = ( taxonomy, element ) => {
			let $label, selector;
			getInputWithVal( taxonomy, element.value ).each( ( index, e ) => {
				$label = $( e ).closest( 'label' );
				if ( ! $label.find( '.tsf-primary-term-selector' ).length ) {
					selector = createSelector( taxonomy );
					$label.find( 'input' ).after( selector );
				}
			} );
		}
		const removeButton = ( taxonomy, element ) => {
			let $label, wasPrimary;
			getInputWithVal( taxonomy, element.value ).each( ( index, e ) => {
				$label     = $( e ).closest( 'label' );
				wasPrimary = $label.hasClass( 'tsf-is-primary-term' );

				$label.removeClass( 'tsf-is-primary-term' );
				// This should also remove all attached events.
				$label.find( '.tsf-primary-term-selector' ).remove();

				if ( wasPrimary ) makeFirstPrimary( taxonomy );
			} );
		}
		const addCheckedNode = ( taxonomy, element ) => {
			checked$[ taxonomy ]       = checked$[ taxonomy ].add( '[value="' + element.value + '"]' );
			uniqueChecked$[ taxonomy ] = uniqueChecked$[ taxonomy ].add( element );
		}
		const removeCheckedNode = ( taxonomy, element ) => {
			checked$[ taxonomy ]       = checked$[ taxonomy ].not( '[value="' + element.value + '"]' );
			uniqueChecked$[ taxonomy ] = uniqueChecked$[ taxonomy ].not( '[value="' + element.value + '"]' );
		}
		const togglePostbox = ( event, postbox ) => {
			fixHelpPos( event.data.taxonomy );
		}

		const initVars = ( taxonomy ) => {
			let $box = getBox( taxonomy, 1 );

			input$[ taxonomy ]   = $box.find( 'input[type=checkbox]' );
			checked$[ taxonomy ] = $box.find( 'input[type=checkbox]:checked' );

			let found = {}, val;
			uniqueChecked$[ taxonomy ] = checked$[ taxonomy ];
			uniqueChecked$[ taxonomy ].each( ( index, element ) => {
				val = $( element ).val();
				if ( found[ val ] ) {
					uniqueChecked$[ taxonomy ].splice( index, 1 );
				} else {
					found[ val ] = true;
				}
			} );
		}
		const updateList = ( event, settings, wpList ) => {
			if ( wpList.hasOwnProperty( 'settings' ) && wpList.settings.hasOwnProperty( 'what' ) ) {
				initVars( wpList.settings.what );
				initActions( wpList.settings.what );
				reload( wpList.settings.what );
				fixHelpPos( wpList.settings.what );
			}
		}
		const initActions = ( taxonomy ) => {
			let data      = { 'taxonomy': taxonomy },
				$box     = getBox( taxonomy ),
				$div     = $( '#' + taxonomy + 'div' ),
				$tabs    = $( '#' + taxonomy + '-tabs' ),
				$postbox = $box.closest( '.postbox' );

			let defaultClickAction = nsAction( 'click', taxonomy );

			$box.off( defaultClickAction )
				.on( defaultClickAction, 'input[type="checkbox"]', data, toggleShowSwitch )
				.on( defaultClickAction, '.tsf-primary-term-selector', data, setPrimary );

			$div.off( nsAction( 'wpListAddEnd', taxonomy ) )
				.on( nsAction( 'wpListAddEnd', taxonomy ), '#' + taxonomy + 'checklist', updateList );

			$tabs.off( defaultClickAction )
				.on( defaultClickAction, 'a', data, fixHelpPosOnTabToggle );

			$postbox.off( nsAction( 'click.postboxes', taxonomy ) )
				.on( nsAction( 'click.postboxes', taxonomy ), data, togglePostbox );
		}
		const reload = ( taxonomy ) => {
			getBox( taxonomy ).find( 'input[type="checkbox"]:checked' )
				.each( ( index, element ) => {
					appendButton( taxonomy, element );
				} );

			if ( primaries[ taxonomy ] ) {
				//? One has been set previously via this script, reselect it.
				makePrimary( taxonomy, primaries[ taxonomy ] );
			} else {
				//? Select one according to WordPress's term list sorting.
				makeFirstPrimary( taxonomy );
			}
		}
		const load = ( taxonomy ) => {
			getBox( taxonomy ).find( 'input[type="checkbox"]:checked' )
				.each( ( index, element ) => {
					appendButton( taxonomy, element );
				} );

			if ( taxonomies[ taxonomy ].primary ) {
				//? One has been saved earlier via this script.
				makePrimary( taxonomy, taxonomies[ taxonomy ].primary );
			} else {
				//? Select one according to WordPress's term list sorting.
				makeFirstPrimary( taxonomy );
			}
		}

		// Hook data handles, don't overwrite vars.
		const init = () => {
			for ( let taxonomy in taxonomies ) {
				if ( getBox( taxonomy ).length ) {
					addInput( taxonomy );
					addHelp( taxonomy );
					initVars( taxonomy );
					initActions( taxonomy );
					load( taxonomy );
				}
			}
		}
		init();
	}

	return Object.assign( {
		/**
		 * Initialises all aspects of the scripts.
		 * You shouldn't call this.
		 *
		 * @since 3.1.0
		 * @access protected
		 *
		 * @function
		 * @return {undefined}
		 */
		load: () => {
			document.body.addEventListener( 'tsf-onload', _initPrimaryTerm );
		}
	}, {

	}, {
		l10n
	} );
}( jQuery );
window.tsfPT.load();
