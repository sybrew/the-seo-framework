/**
 * This file holds The SEO Framework plugin's JS code for Primary Term Selection.
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
 * Holds tsfPT (tsf primary term) values in an object to avoid polluting global namespace.
 *
 * This is a self-constructed function assigned as an object.
 * This also is deprecated in favor for `pt-gb.js`.
 *
 * @since 3.1.0
 *
 * @constructor
 */
window.tsfPT = function () {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 4.0.0
	 * @access public
	 * @type {(Object<string,*>)|boolean|null} l10n Localized strings
	 */
	const l10n = tsfPTL10n;

	/**
	 * @since 5.1.0
	 * @access private
	 * @type {{makePrimary: string,primary: string,name: string}|{}}
	 */
	const supportedTaxonomies = l10n?.taxonomies || {};

	/**
	 * @since 5.1.0
	 * @access private
	 * @param {String} taxonomySlug
	 * @param {String} what The i18n to get.
	 * @return {String}
	 */
	function _geti18n( taxonomySlug, what ) {
		return supportedTaxonomies[ taxonomySlug ]?.i18n[ what ] || '';
	}

	/**
	 * @since 5.1.0
	 * @access private
	 * @param {Number} id           The term ID.
	 * @param {String} taxonomySlug The taxonomy slug.
	 */
	function dispatchUpdateEvent( id, taxonomySlug ) {
		document.dispatchEvent(
			new CustomEvent(
				'tsf-updated-primary-term',
				{
					detail: { id, taxonomy: taxonomySlug },
				}
			)
		);
	}

	const _registeredFields = new Map();
	/**
	 * @since 5.1.0
	 * @access private
	 * @param {String} taxonomySlug
	 * @returns {{
 	 *     get:                   () => integer,
	 *     set:                   ( id: string|integer, fallback: string|integer ) => integer,
	 *     revalidate:            ( selectedTerms: integer[] ) => integer,
	 *     registerPostField:     () => void,
	 *     isPostFieldRegistered: () => boolean,
	 * }}
	 */
	function _primaryTerm( taxonomySlug ) {

		const _primaryTermField = () => document.getElementById( `autodescription[_primary_term_${taxonomySlug}]` );

		const get = () => +( _primaryTermField().value );
		const set = ( id, fallback ) => {
			id = +id;
			_primaryTermField().value = id;
			dispatchUpdateEvent( id || +fallback, taxonomySlug );
			return id;
		};

		const revalidate = selectedTerms => {
			const primaryTerm = get();

			if ( selectedTerms.includes( primaryTerm ) )
				return primaryTerm;

			return set( selectedTerms?.[0] || 0 );
		}

		const registerPostField = () => {
			// TODO create a new dataholder for TSF instead?
			const wrap = document.getElementById( `${taxonomySlug}div` );

			if ( ! wrap ) {
				_registeredFields.set( taxonomySlug, false );
			} else {
				wrap.insertAdjacentHTML(
					'beforeend',
					wp.template( 'tsf-primary-term-selector' )( { taxonomy: supportedTaxonomies[ taxonomySlug ] } )
				);

				_registeredFields.set( taxonomySlug, true );
			}
		}
		const isPostFieldRegistered = () => !! _registeredFields.get( taxonomySlug );

		return { get, set, revalidate, registerPostField, isPostFieldRegistered };
	}

	/**
	 * @since 5.1.0
	 * @access private
	 * @param {String} taxonomySlug
	 * @returns {{
	 *     getWrap:                () => ?Element,
	 *     getInputs:              () => Array<HTMLInputElement>,
	 *     getAllInputs:           () => Array<HTMLInputElement>,
	 *     getInputsUnique:        () => Array<HTMLInputElement>,
	 *     getInputsChecked:       () => Array<HTMLInputElement>,
	 *     getInputsCheckedValues: () => Integer[],
	 *     subscribe:              (callback: CallableFunction) => undefined,
	 * }}
	 */
	function _termCheckboxes( taxonomySlug ) {
		const getWrap = () => document.getElementById( `${taxonomySlug}checklist` );

		const getInputs = () => [ ...getWrap().querySelectorAll( 'input[type=checkbox]' ) ]
			.sort( ( a, b ) => a.value - b.value );

		const getAllInputs = () => document.getElementById( `taxonomy-${taxonomySlug}` )
			.querySelectorAll( '.categorychecklist input[type=checkbox]' );

		const getInputsChecked = () => getInputs().filter( el => el.checked );

		const getInputsCheckedValues = () => getInputsChecked().map( el => +el.value );

		const subscribe = callback => {
			const tick = () => callback( getInputsCheckedValues() );

			const registerListeners = () => {
				getAllInputs().forEach(
					el => { el.addEventListener( 'change', tick ) },
				);
			}

			new MutationObserver( () => {
				registerListeners(); // A new listener might've been added. Reregister.
				tick();
			} ).observe(
				getWrap(),
				{ childList: true },
			);

			registerListeners();

			// Immediately invoke.
			tick();
		}

		return {
			getWrap,
			getInputs,
			getAllInputs,
			getInputsChecked,
			getInputsCheckedValues,
			subscribe,
		};
	}

	/**
	 * Initializes primary term selection.
	 *
	 * @since 3.0.0
	 * @since 3.0.4 1 : Added postbox toggle event listeners for help display correction.
	 *              2 : Added tab visibility checkers.
	 * @since 5.1.0 Rewritten.
	 */
	function _initPrimaryTerm() {

		if ( ! Object.keys( supportedTaxonomies ).length ) return;

		const initPrimaryTermSelector = taxonomySlug => {

			const primaryTerm    = _primaryTerm( taxonomySlug );
			const termCheckboxes = _termCheckboxes( taxonomySlug );

			const selectorWrapId = `tsf-primary-term-${taxonomySlug}`,
				  selectId       = `${selectorWrapId}-select`;

			let selectorWrapShown = false;

			// Helper for minifier.
			const createElement = el => document.createElement( el );

			const repopulateSelect = () => {
				const optionElement = createElement( 'option' ),
					  selectElement = document.getElementById( selectId );

				selectElement.innerHTML = '';
				selectElement.append(
					...termCheckboxes.getInputsChecked().map(
						el => {
							const option = optionElement.cloneNode();
							option.value = el.value;
							option.label = tsf.decodeEntities( el.parentElement.textContent.trim() );
							return option;
						},
					),
				);

				tsf.selectByValue( selectElement, primaryTerm.get() );
			}
			// This is needless work for most sites becasuse they don't have more than 1 term selected.
			const showSelectorWrap = () => {
				const selectorWrap  = createElement( 'div' ),
					  labelElement  = createElement( 'label' ),
					  selectElement = createElement( 'select' );

				selectorWrap.id = selectorWrapId;
				selectorWrap.classList.add( 'tsf-primary-term-selector-wrap' );

				labelElement.innerText = _geti18n( taxonomySlug, 'selectPrimary' );

				selectElement.name = selectElement.id = selectId;
				labelElement.setAttribute( 'for', selectId );

				selectorWrap.append( labelElement, selectElement );

				// Insert it after the -adder if exists, otherwise at the end of the wrap.
				document.getElementById( `taxonomy-${taxonomySlug}` )
					.insertBefore(
						selectorWrap,
						document.getElementById( `${taxonomySlug}-adder` )?.nextSibling
					);

				selectElement.addEventListener(
					'change',
					event => {
						primaryTerm.set( event.target.value );
					},
				);
				tsfAys.registerChangeListener( selectElement, 'change' );
			}

			termCheckboxes.subscribe( () => {
				if ( termCheckboxes.getInputsChecked().length > 1 ) {
					if ( ! selectorWrapShown ) {
						selectorWrapShown = true; // set first to mitigate race conditions.
						showSelectorWrap();
					}
					repopulateSelect();
					primaryTerm.revalidate( termCheckboxes.getInputsCheckedValues() );
				} else {
					if ( selectorWrapShown ) {
						document.getElementById( selectorWrapId )?.remove();
						selectorWrapShown = false;
					}

					// Reset stored selection. Append fallback.
					primaryTerm.set(
						0,
						   termCheckboxes.getInputsChecked()[0]?.value
						|| termCheckboxes.getInputs()[0]?.value
					 	|| 0
					);
				}
			} );
		}

		for ( let taxonomySlug in supportedTaxonomies ) {
			if ( _termCheckboxes( taxonomySlug ).getWrap() ) {
				const primaryTerm = _primaryTerm( taxonomySlug );
				primaryTerm.registerPostField();

				if ( primaryTerm.isPostFieldRegistered() )
					initPrimaryTermSelector( taxonomySlug );
			}
		}
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
		 */
		load: () => {
			document.body.addEventListener( 'tsf-onload', _initPrimaryTerm );
		},
	}, {
		l10n,
	} );
}();
window.tsfPT.load();
