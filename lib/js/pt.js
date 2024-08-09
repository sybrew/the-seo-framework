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
window.tsfPT = function() {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 4.0.0
	 * @access public
	 * @type {(Object<string, *>)|boolean|null} l10n Localized strings
	 */
	const l10n = 'undefined' !== typeof tsfPTL10n && tsfPTL10n;

	/**
	 * @since 5.0.7
	 * @access private
	 * @type {{makePrimary: string,primary: string,name: string}|{}}
	 */
	const supportedTaxonomies = l10n?.taxonomies || {};

	/**
	 * @since 5.0.7
	 * @access private
	 * @param {String} taxonomySlug
	 * @param {String} what The i18n to get.
	 * @return {String}
	 */
	function _geti18n( taxonomySlug, what ) {
		return supportedTaxonomies[ taxonomySlug ]?.i18n[ what ] || '';
	}

	const _registeredFields = new Map();
	/**
	 * @since 5.0.7
	 * @access private
	 * @param {String} taxonomySlug
	 * @returns {{
 	 *   get:                   () => integer,
	 *   set:                   ( id: string|integer ) => integer,
	 *   revalidate:            ( selectedTerms: integer[] ) => integer,
	 *   registerPostField:     () => void,
	 *   isPostFieldRegistered: () => boolean,
	 * }}
	 */
	function _primaryTerm( taxonomySlug ) {

		const _primaryTermField = () => document.getElementById( `autodescription[_primary_term_${taxonomySlug}]` );

		const get = () => +( _primaryTermField().value );
		const set = id => _primaryTermField().value = +id;

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

	const _registeredBoxes = new Map();
	/**
	 * @since 5.0.7
	 * @access private
	 * @param {String} taxonomySlug
	 * @returns {{
	 *   get:                          () => Array<Element>,
	 *   getInputs:                    () => Array<HTMLInputElement>,
	 *   getInputsChecked:             () => Array<HTMLInputElement>,
	 *   getInputsUniqueChecked:       () => Array<HTMLInputElement>,
	 *   getInputsUniqueCheckedValues: () => Integer[],
	 *   getInputWithValue:            (value: string|integer) => Array<HTMLInputElement>,
	 *   clearCache:                   () => void
	 * }}
	 */
	function _termCheckboxes( taxonomySlug ) {
		const get = () => {
			const cacheKey = `get.${taxonomySlug}`;
			return _registeredBoxes.get( cacheKey )
				?? _registeredBoxes.set(
					cacheKey,
					[ ...document.querySelectorAll( `#${taxonomySlug}checklist, #${taxonomySlug}checklist-pop` ) ]
				).get( cacheKey );
		}
		const getInputs = () => {
			const cacheKey = `getInputs.${taxonomySlug}`;
			return _registeredBoxes.get( cacheKey )
				?? _registeredBoxes.set(
					cacheKey,
					get().flatMap( el => [ ...el.querySelectorAll( 'input[type=checkbox]' ) ] )
				).get( cacheKey );
		}
		const getInputsChecked = () => {
			return get().flatMap( el => [ ...el.querySelectorAll( 'input[type=checkbox]:checked' ) ] );
		}
		const getInputsUniqueChecked = () => {
			return [ ...new Map( getInputsChecked().reverse().map( el => [ +el.value, el ] ) ).values() ];
		}
		const getInputsUniqueCheckedValues = () => {
			return [ ...new Set( getInputsUniqueChecked().map( el => +el.value ) ) ];
		}
		const getInputWithValue = value => {
			return getInputs().filter( el => el.value == value ); // Not strict
		}

		return {
			get,
			getInputs,
			getInputsChecked,
			getInputsUniqueChecked,
			getInputsUniqueCheckedValues,
			getInputWithValue,
			clearCache: () => _registeredBoxes.clear(),
		};
	}

	/**
	 * Initializes primary term selection.
	 *
	 * @since 3.0.0
	 * @since 3.0.4 1 : Added postbox toggle event listeners for help display correction.
	 *              2 : Added tab visibility checkers.
	 * @since 5.0.7 Rewritten.
	 *
	 * @function
	 */
	function _initPrimaryTerm() {

		if ( ! Object.keys( supportedTaxonomies ).length ) return;

		const helpWrapIndentObserver = new ResizeObserver(
			entries => {
				for ( let entry of entries ) {
					// If the tab isn't visible, there's nothing to calculate.
					if ( ! entry.target.checkVisibility() ) continue;

					const { clientWidth, offsetWidth } = entry.target;

					entry.target.parentElement.querySelector( '.tsf-primary-term-selector-help-wrap' )
						.style.insetInlineEnd = `${
							offsetWidth - clientWidth + parseFloat( getComputedStyle( entry.target ).paddingInlineEnd )
						}px`;
				}
			}
		);
		const displayHelpWrap = ( taxonomySlug, hide ) => {
			document.getElementById( `tsf-primary-term-selector-help-${taxonomySlug}` )
				?.classList.toggle( 'hidden', 'show' !== hide );
		}

		const primeHelpWrap = taxonomySlug => {

			const alignHelpWrap = () => {
				// Observe the taxonomy wrapper that contains the help wrap. Then, in the observer, we align the help wrap.
				document.getElementById( `taxonomy-${taxonomySlug}` )?.querySelectorAll( '.tabs-panel' ).forEach(
					el => { helpWrapIndentObserver.observe( el ) }
				);
			}

			const addHelpWrap = () => {
				const adder = document.getElementById( `${taxonomySlug}-adder` );

				if ( adder ) {
					const addNewTaxButton = adder.querySelector( '.taxonomy-add-new' );
					// 10px is default of .taxonomy-add-new.
					// We shouldn't ignore the padding, but using that on text is bad practice.
					let marginTop = addNewTaxButton ? getComputedStyle( addNewTaxButton ).marginTop : '10px';

					adder.style.position = 'relative';
					adder.insertAdjacentHTML(
						'beforeend',
						wp.template( 'tsf-primary-term-selector-help-above' )( {
							taxonomySlug,
							taxonomy: supportedTaxonomies[ taxonomySlug ],
						} ),
					);

					adder.querySelector( '.tsf-primary-term-selector-help-wrap' ).style.top = marginTop;
				} else {
					const wrap = document.getElementById( `taxonomy-${taxonomySlug}` );

					if ( ! wrap ) return false;

					wrap.style.position = 'relative';
					wrap.insertAdjacentHTML(
						'beforeend',
						wp.template( 'tsf-primary-term-selector-help' )( {
							taxonomySlug,
							taxonomy: supportedTaxonomies[ taxonomySlug ],
						} ),
					);

					helpWrap = wrap.querySelector( '.tsf-primary-term-selector-help-wrap' );
				}

				tsfTT.triggerReset();

				alignHelpWrap();

				return true;
			}

			addHelpWrap();
		}
		const primeRadioButtons = taxonomySlug => {

			const primaryTerm    = _primaryTerm( taxonomySlug );
			const termCheckboxes = _termCheckboxes( taxonomySlug );

			const selectorWrapClass = 'tsf-primary-term-selector';

			const selectorWrap = document.createElement( 'span' );

			selectorWrap.classList.add( selectorWrapClass );
			selectorWrap.innerHTML =
				`<input type=radio name="tsf-primary-term-${taxonomySlug}" title="${_geti18n(taxonomySlug, 'makePrimary')}" aria-label="${_geti18n(taxonomySlug, 'makePrimary')}">`;

			const appendRadioButtonsByValue = value => {
				const wrap  = selectorWrap.cloneNode( true ),
					  input = wrap.querySelector( 'input' );

				wrap.dataset.tsfRadioValue
					= input.value
					= value;

				termCheckboxes.getInputWithValue( value ).forEach(
					el => {
						el.closest( 'label' ).insertAdjacentElement( 'afterend', wrap );
						input.addEventListener( 'change', onRadioChange );
					},
				);
			}
			const removeRadioButtonsByValue = value => {
				[ ...document.getElementsByClassName( selectorWrapClass ) ].forEach(
					el => {
						if ( value == el.dataset.tsfRadioValue ) // Not strict
							el.remove();
					},
				);
			}
			const checkRadioButtonsByValue = value => {
				[ ...document.getElementsByClassName( selectorWrapClass ) ].forEach(
					el => {
						if ( value == el.dataset.tsfRadioValue ) // Not strict
							el.firstElementChild.checked = true;
					},
				);
			}

			// They're linked by DOM, no?
			// const uncheckRadioButtonsByValue = value => {
			// 	[ ...document.getElementsByClassName( selectorWrapClass ) ].forEach(
			// 		el => {
			// 			if ( value != el.dataset.tsfRadioValue ) // Not strict
			// 				el.firstElementChild.checked = false;
			// 		},
			// 	);
			// }
			// var_dump() remove me?

			const onRadioChange = event => {
				if ( event.target.checked ) {
					primaryTerm.set( event.target.value );
					// var_dump() remove me?
					// checkRadioButtonsByValue( event.target.value );
				} else {
					// uncheckRadioButtonsByValue( event.target.value );
				}
			}
			const onCheckboxChange = event => {
				const totalChecked = termCheckboxes.getInputsUniqueChecked().length;

				let revalidate = false;

				if ( event.target.checked ) {
					switch ( totalChecked ) {
						case 1:
							// Just one checked, remove remainder.
							termCheckboxes.getInputsUniqueCheckedValues().forEach(
								value => { removeRadioButtonsByValue( value ) }
							);
							// This is the first checked, let's "revalidate," thus auto-selecting it.
							revalidate = true;
							displayHelpWrap( taxonomySlug, 'hide' );
							break;
						case 2:
							termCheckboxes.getInputsUniqueCheckedValues().forEach(
								value => { appendRadioButtonsByValue( value ) }
							);
							displayHelpWrap( taxonomySlug, 'show' );
							break;
						default:
							// There ought to be at least 2 checkboxes. Just add one.
							appendRadioButtonsByValue( event.target.value );
					}
				} else {
					removeRadioButtonsByValue( event.target.value );
					revalidate = true;

					if ( 1 === totalChecked ) {
						// There's now one left, remove remainders.
						termCheckboxes.getInputsUniqueCheckedValues().forEach(
							value => { removeRadioButtonsByValue( value ) }
						);
						displayHelpWrap( taxonomySlug, 'hide' );
					}
				}

				if ( revalidate ) {
					const primaryTermValue = primaryTerm.get();
					primaryTerm.revalidate( termCheckboxes.getInputsUniqueCheckedValues() );

					const newPrimaryTermValue = primaryTerm.get();

					if ( newPrimaryTermValue !== primaryTermValue )
						checkRadioButtonsByValue( newPrimaryTermValue );
				}
			}

			const setup = () => {
				// Register input change events.
				termCheckboxes.getInputs().forEach(
					el => { el.addEventListener( 'change', onCheckboxChange ) },
				);
				if ( termCheckboxes.getInputsUniqueCheckedValues().length > 1 ) {
					// Add radio to checkboxes.
					termCheckboxes.getInputsUniqueCheckedValues().forEach(
						value => {
							appendRadioButtonsByValue( value );
						},
					);
					displayHelpWrap( taxonomySlug, 'show' );
				} else {
					displayHelpWrap( taxonomySlug, 'hide' );
				}
				// Preselect the primary checkbox.
				checkRadioButtonsByValue( primaryTerm.get() );
			}

			document.getElementById( `${taxonomySlug}div` )
				?.addEventListener(
					'wpListAddEnd',
					event => {
						if ( event.detail?.[0]?.what === taxonomySlug ) {
							[ ...document.getElementsByClassName( selectorWrapClass ) ].forEach(
								el => { el.remove() },
							);
							termCheckboxes.clearCache();
							setup();
						}
					}
				);

			setup();
		}

		for ( let taxonomySlug in supportedTaxonomies ) {
			if ( _termCheckboxes( taxonomySlug ).get() ) {

				const primaryTerm = _primaryTerm( taxonomySlug );
				primaryTerm.registerPostField();

				if ( primaryTerm.isPostFieldRegistered() ) {
					primeHelpWrap( taxonomySlug );
					primeRadioButtons( taxonomySlug );
					primaryTerm.revalidate( _termCheckboxes( taxonomySlug ).getInputsUniqueCheckedValues() );
				}
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
