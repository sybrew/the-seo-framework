/**
 * This file holds The SEO Framework plugin's JS code for Primary Term Selection in List Edit.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/autodescription/>
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2025 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds tsfPTLE (tsf primary term list edit) values in an object to avoid polluting global namespace.
 *
 * @since 5.1.3
 *
 * @constructor
 */
window.tsfPTLE = function () {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 5.1.3
	 * @access public
	 * @type {(Object<string,*>)|Boolean|null} l10n Localized strings
	 */
	const l10n = tsfPTL10n;

	/**
	 * @since 5.1.3
	 * @access private
	 * @type {{makePrimary: string,primary: string,name: string}|{}}
	 */
	const supportedTaxonomies = l10n.taxonomies;

	/**
	 * @since 5.1.3
	 * @access private
	 * @param {String} taxonomySlug
	 * @param {String} what The i18n to get.
	 * @return {String}
	 */
	function _geti18n( taxonomySlug, what ) {
		return supportedTaxonomies[ taxonomySlug ]?.i18n[ what ] || '';
	}

	/**
	 * @since 5.1.3
	 * @access private
	 *
	 * @param {String} templateId
	 * @param {Object<string,*>} data
	 * @return {String}
	 */
	function _renderTemplate( templateId, data ) {

		if ( ! window.wp || 'function' !== typeof window.wp.template )
			return '';

		const template = window.wp.template( templateId );

		if ( 'function' !== typeof template )
			return '';

		return template( data );
	}

	/**
	 * @since 5.1.3
	 * @access private
	 * @param {Number} id           The term ID.
	 * @param {String} taxonomySlug The taxonomy slug.
	 */
	function dispatchUpdateEvent( id, taxonomySlug ) {

		document.dispatchEvent(
			new CustomEvent(
				'tsf-updated-primary-term',
				{
					detail: {
						id,
						taxonomy: taxonomySlug,
					},
				},
			),
		);
	}

	/**
	 * @since 5.1.3
	 * @access private
	 * @param {String}        taxonomySlug The taxonomy slug.
	 * @param {String|Number} editId       The edit row ID (post ID for quick-edit, 'bulk' for bulk-edit).
	 * @returns {{
	 *     getWrap:                () => ?Element,
	 *     getInputs:              () => Array<HTMLInputElement>,
	 *     getInputsChecked:       () => Array<HTMLInputElement>,
	 *     getInputsCheckedValues: () => Integer[],
	 *     subscribe:              (callback: (selectedValues: Integer[]) => void) => undefined,
	 * }}
	 */
	function _termCheckboxes( taxonomySlug, editId ) {

		const getWrap = () => document.querySelector(
			editId === 'bulk'
				? `#bulk-edit .${taxonomySlug}-checklist`
				: `#edit-${editId} .${taxonomySlug}-checklist`,
		);

		const getInputs = () => [ ...getWrap().querySelectorAll( 'input[type=checkbox]' ) ]
			.sort( ( a, b ) => a.value - b.value );

		// WordPress only implements indeterminate states for the built-in 'category' taxonomy (post_category[]),
		// not for custom taxonomies (tax_input[name][]). We filter indeterminate checkboxes because they represent
		// an ambiguous state where only some posts have the term, making them unsuitable for primary term selection.
		const getInputsChecked = () => getInputs().filter( el => el.checked && ! el.indeterminate );

		const getInputsCheckedValues = () => getInputsChecked().map( el => +el.value );

		const subscribe = callback => {

			const tick = () => callback();

			const registerListeners = () => {
				getInputs().forEach(
					el => {
						el.addEventListener( 'change', tick );
					},
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
			tick();
		}

		return {
			getWrap,
			getInputs,
			getInputsChecked,
			getInputsCheckedValues,
			subscribe,
		};
	}


	/**
	 * @since 5.1.3
	 * @access private
	 *
	 * @param {String}        taxonomySlug The taxonomy slug.
	 * @param {String|Number} postId       The post ID for quick edit.
	 * @returns {{
	 *     get:                   () => Integer,
	 *     set:                   ( id: Integer, fallback?: Integer ) => Integer,
	 *     revalidate:            ( selectedTerms: Integer[] ) => Integer,
	 *     registerPostField:     ( defaultValue?: Integer ) => void,
	 *     isPostFieldRegistered: () => Boolean,
	 * }}
	 */
	function _primaryTermSelectorQuick( taxonomySlug, postId ) {

		const fieldName          = `autodescription-quick[primary_term_${taxonomySlug}]`;
		const _primaryTermField  = () => document.getElementById( fieldName );
		const getFieldContainer  = () => document.getElementById( `edit-${postId}` );

		const get = () => +( _primaryTermField()?.value ?? 0 );
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

		const registerPostField = defaultValue => {
			const wrap = getFieldContainer();
			if ( ! wrap ) return;

			let field = _primaryTermField();

			if ( ! field ) {
				wrap.insertAdjacentHTML(
					'beforeend',
					`<input type="hidden" id="${fieldName}" name="${fieldName}" value="0">`,
				);
				field = _primaryTermField();
			}

			if ( field && undefined !== defaultValue ) {
				const normalizedValue = +defaultValue;
				field.value = isNaN( normalizedValue ) ? 0 : normalizedValue;
			}
		}
		const isPostFieldRegistered = () => !! _primaryTermField();

		return {
			get,
			set,
			revalidate,
			registerPostField,
			isPostFieldRegistered,
		};
	}

	/**
	 * @since 5.1.3
	 * @access private
	 *
	 * @param {String} taxonomySlug The taxonomy slug.
	 * @returns {{
	 *     get:                   () => Integer,
	 *     set:                   ( id: Integer, fallback?: Integer ) => Integer,
	 *     revalidate:            ( selectedTerms: Integer[] ) => Integer,
	 *     registerPostField:     ( defaultValue?: Integer ) => void,
	 *     isPostFieldRegistered: () => Boolean,
	 * }}
	 */
	function _primaryTermSelectorBulk( taxonomySlug ) {

		const fieldName          = `autodescription-bulk[primary_term_${taxonomySlug}]`;
		const _primaryTermField  = () => document.getElementById( fieldName );
		const getFieldContainer  = () => document.getElementById( 'bulk-edit' );

		const get = () => +( _primaryTermField()?.value ?? 0 );
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

		const registerPostField = defaultValue => {
			const wrap = getFieldContainer();
			if ( ! wrap ) return;

			let field = _primaryTermField();

			if ( ! field ) {
				wrap.insertAdjacentHTML(
					'beforeend',
					`<input type="hidden" id="${fieldName}" name="${fieldName}" value="0">`,
				);
				field = _primaryTermField();
			}

			if ( field && undefined !== defaultValue ) {
				const normalizedValue = +defaultValue;
				field.value = isNaN( normalizedValue ) ? 0 : normalizedValue;
			}
		}
		const isPostFieldRegistered = () => !! _primaryTermField();

		return {
			get,
			set,
			revalidate,
			registerPostField,
			isPostFieldRegistered,
		};
	}

	/**
	 * Prepares primary term selectors for quick edit.
	 *
	 * @since 5.1.3
	 * @access private
	 *
	 * @param {String} postId                 The post ID.
	 * @param {Object<string,*>} primaryTerms Term selection data map keyed by primary_term_* indexes.
	 */
	function _prepareQuickEditTerms( postId, primaryTerms ) {

		if ( ! Object.keys( supportedTaxonomies ).length ) return;

		const getStoredTermValue = taxonomySlug => +(
			primaryTerms?.[ `primary_term_${taxonomySlug}` ]?.value ?? 0
		) || 0;

		const initQuickEditSelector = taxonomySlug => {

			const primaryTerm      = _primaryTermSelectorQuick( taxonomySlug, postId );
			const termCheckboxes   = _termCheckboxes( taxonomySlug, postId );

			const selectorWrapId   = `tsf-pt-le-${taxonomySlug}-${postId}`,
				  selectId         = `${selectorWrapId}-select`;

			let selectorWrapShown = false;

			const termCheckboxWrap = termCheckboxes.getWrap();
			const storedTermValue  = getStoredTermValue( taxonomySlug );

			const repopulateSelect = () => {
				const optionElement = document.createElement( 'option' ),
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

				tsf.selectByValue( selectElement, `${primaryTerm.get()}` );
			}
			const showSelectorWrap = () => {
				let selectElement = document.getElementById( selectId );

				if ( ! selectElement ) {
					const markup = _renderTemplate(
						'tsf-primary-term-selector-quick',
						{
							wrapId: selectorWrapId,
							selectId,
							selectName: selectId,
							i18n: {
								selectPrimary: _geti18n( taxonomySlug, 'selectPrimary' ),
							},
						},
					);

					if ( markup.length )
						termCheckboxWrap.insertAdjacentHTML( 'afterend', markup );

					selectElement = document.getElementById( selectId );

					selectElement?.addEventListener(
						'change',
						event => {
							primaryTerm.set( event.target.value );
						},
					);
				}

				if ( selectElement )
					selectorWrapShown = true;

				return selectElement;
			}

			termCheckboxes.subscribe( () => {
				if ( termCheckboxes.getInputsChecked().length > 1 ) {
					if ( ! selectorWrapShown ) {
						selectorWrapShown = true;
						showSelectorWrap();
					}
					repopulateSelect();
					primaryTerm.revalidate( termCheckboxes.getInputsCheckedValues() );
				} else {
					if ( selectorWrapShown ) {
						document.getElementById( selectorWrapId )?.remove();
						selectorWrapShown = false;
					}

					primaryTerm.set(
						0,
						   termCheckboxes.getInputsChecked()[0]?.value
						|| termCheckboxes.getInputs()[0]?.value
						|| storedTermValue
						|| 0
					);
				}
			} );
		};

		for ( const taxonomySlug in supportedTaxonomies ) {
			if ( _termCheckboxes( taxonomySlug, postId ).getWrap() ) {
				const primaryTerm = _primaryTermSelectorQuick( taxonomySlug, postId );
				primaryTerm.registerPostField( getStoredTermValue( taxonomySlug ) );

				if ( primaryTerm.isPostFieldRegistered() )
					initQuickEditSelector( taxonomySlug );
			}
		}
	}

	/**
	 * Prepares primary term selectors for bulk edit.
	 *
	 * @since 5.1.3
	 * @access private
	 */
	function _prepareBulkEditTerms() {

		const initBulkSelector = taxonomySlug => {
			const termCheckboxes = _termCheckboxes( taxonomySlug, 'bulk' );
			const primaryTerm    = _primaryTermSelectorBulk( taxonomySlug );
			const checklist      = termCheckboxes.getWrap();
			const selectorWrapId = `tsf-pt-le-${taxonomySlug}-bulk`;
			const selectId       = `${selectorWrapId}-select`;

			let selectorWrapShown = false;

			const showSelectorWrap = () => {
				let selectElement = document.getElementById( selectId );

				if ( ! selectElement ) {
					const markup = _renderTemplate(
						'tsf-primary-term-selector-bulk',
						{
							wrapId: selectorWrapId,
							selectId,
							selectName: selectId,
							i18n: {
								selectPrimary: _geti18n( taxonomySlug, 'selectPrimary' ),
							},
						},
					);

					if ( markup.length )
						checklist.insertAdjacentHTML( 'afterend', markup );

					selectElement = document.getElementById( selectId );

					selectElement?.addEventListener(
						'change',
						event => {
							if ( 'nochange' === event.target.value )
								return;

							primaryTerm.set( event.target.value );
						},
					);
				}

				if ( selectElement )
					selectorWrapShown = true;

				return selectElement;
			};
			const repopulateSelect = () => {
				const optionElement = document.createElement( 'option' ),
					  selectElement = document.getElementById( selectId );

				const previousValue = selectElement.value;

				selectElement.innerHTML = '';

				const defaultOption = optionElement.cloneNode();
				defaultOption.value = 'nochange';
				defaultOption.label = '\u2014 No Change \u2014';
				selectElement.appendChild( defaultOption );

				let restorePreviousValue = false;

				selectElement.append(
					...termCheckboxes.getInputsChecked().map(
						el => {
							const option = optionElement.cloneNode();
							option.value = el.value;
							option.label = tsf.decodeEntities( el.parentElement.textContent.trim() );

							if ( el.value === previousValue )
								restorePreviousValue = true;

							return option;
						},
					),
				);

				const nextValue = restorePreviousValue ? previousValue : 'nochange';
				tsf.selectByValue( selectElement, nextValue );
			}
			termCheckboxes.subscribe( () => {
				if ( termCheckboxes.getInputsChecked().length > 1 ) {
					if ( ! selectorWrapShown ) {
						selectorWrapShown = true;
						showSelectorWrap();
					}
					repopulateSelect();
					return;
				}

				if ( selectorWrapShown ) {
					document.getElementById( selectorWrapId )?.remove();
					selectorWrapShown = false;
				}
			} );
		};

		for ( const taxonomySlug in supportedTaxonomies ) {
			if ( _termCheckboxes( taxonomySlug, 'bulk' ).getWrap() ) {
				const primaryTerm = _primaryTermSelectorBulk( taxonomySlug );
				primaryTerm.registerPostField();

				if ( primaryTerm.isPostFieldRegistered() )
					initBulkSelector( taxonomySlug );
			}
		}
	}

	return Object.assign(
		{
			_prepareQuickEditTerms,
			_prepareBulkEditTerms,
		},
		{
			l10n,
		},
	);
}();
