/**
 * This file holds The SEO Framework plugin's JS code for Primary Term Selection in List Edit.
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
	const supportedTaxonomies = l10n?.taxonomies || {};

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
	 * @param {Number} id           The term ID.
	 * @param {String} taxonomySlug The taxonomy slug.
	 */
	function dispatchUpdateEvent( id, taxonomySlug ) {

		document.dispatchEvent(
			new CustomEvent(
				'tsf-updated-primary-term',
				{
					detail: { id, taxonomy: taxonomySlug },
				},
			),
		);
	}

	/**
	 * Injects primary term selector dropdown for a taxonomy in list edit.
	 *
	 * @since 5.1.3
	 * @access private
	 *
	 * @param {String} taxonomySlug The taxonomy slug.
	 * @param {String} postId       The post ID.
	 * @param {Number} currentValue The current primary term ID.
	 */
	function _injectSelector( taxonomySlug, postId, currentValue ) {

		// Find the checklist - try both patterns
		const checklist = document.querySelector(
			`#edit-${postId} #${taxonomySlug}checklist, ` +
			`#edit-${postId} #${taxonomySlug}-all`,
		);

		if ( ! checklist ) return;

		// Check if already injected
		if ( checklist.parentElement.querySelector( '.tsf-primary-term-selector-wrap' ) ) return;

		const createElement = el => document.createElement( el );

		const selectorWrap  = createElement( 'div' ),
			  labelElement  = createElement( 'label' ),
			  selectElement = createElement( 'select' );

		const selectId = `tsf-pt-le-${taxonomySlug}-${postId}`;

		selectorWrap.classList.add( 'tsf-primary-term-selector-wrap' );

		labelElement.innerText = _geti18n( taxonomySlug, 'selectPrimary' );

		selectElement.name = selectElement.id = selectId;
		selectElement.setAttribute( 'name', `autodescription-quick[primary_term_${taxonomySlug}]` );
		labelElement.setAttribute( 'for', selectId );

		selectorWrap.append( labelElement, selectElement );

		// Insert after the checklist
		checklist.parentNode.insertBefore( selectorWrap, checklist.nextSibling );

		// Populate the select with terms from the checklist
		_populateSelector( taxonomySlug, postId, currentValue );

		// Listen for changes
		selectElement.addEventListener(
			'change',
			event => {
				dispatchUpdateEvent( +event.target.value, taxonomySlug );
			},
		);
	}

	/**
	 * Populates the primary term selector with terms from the category checklist.
	 *
	 * @since 5.1.3
	 * @access private
	 *
	 * @param {String} taxonomySlug The taxonomy slug.
	 * @param {String} postId       The post ID.
	 * @param {Number} currentValue The current primary term ID.
	 */
	function _populateSelector( taxonomySlug, postId, currentValue ) {

		const select = document.getElementById( `tsf-pt-le-${taxonomySlug}-${postId}` );

		if ( ! select ) return;

		select.innerHTML = '';

		// Get checked terms from the checklist
		const checkboxes = document.querySelectorAll(
			`#edit-${postId} #${taxonomySlug}checklist input[type=checkbox]:checked, #edit-${postId} #${taxonomySlug}-all input[type=checkbox]:checked`,
		);

		checkboxes.forEach( checkbox => {

			const option = document.createElement( 'option' );

			option.value = checkbox.value;
			option.textContent = tsf.decodeEntities( checkbox.parentElement.textContent.trim() );

			if ( +checkbox.value === +currentValue )
				option.selected = true;

			select.appendChild( option );
		} );

		// If no match and we have a currentValue, select first or fallback
		if ( currentValue && ! select.querySelector( `option[value="${currentValue}"]` ) && select.options.length ) {
			select.options[0].selected = true;
		}
	}

	/**
	 * Initializes primary term selection for quick edit.
	 *
	 * @since 5.1.3
	 * @access private
	 *
	 * @param {String} postId The post ID.
	 */
	function _initQuickEdit( postId ) {

		for ( const taxonomySlug in supportedTaxonomies ) {
			const leData = JSON.parse(
				document.getElementById( `tsfLeData[${postId}]` )?.dataset.le || '{}',
			);

			const currentValue = leData[ `primary_term_${taxonomySlug}` ]?.value || 0;

			_injectSelector( taxonomySlug, postId, currentValue );
		}
	}

	/**
	 * Injects primary term selector for bulk edit.
	 *
	 * @since 5.1.3
	 * @access private
	 */
	function _initBulkEdit() {

		const bulkEditRow = document.getElementById( 'bulk-edit' );

		if ( ! bulkEditRow ) return;

		const createElement = el => document.createElement( el );

		for ( const taxonomySlug in supportedTaxonomies ) {
			// Find the checklist directly
			const checklist = bulkEditRow.querySelector( `#${taxonomySlug}checklist, #${taxonomySlug}-all` );

			if ( ! checklist ) continue;

			// Check if already injected
			if ( checklist.parentElement.querySelector( '.tsf-primary-term-selector-wrap' ) ) continue;

			const selectorWrap  = createElement( 'div' ),
				  labelElement  = createElement( 'label' ),
				  selectElement = createElement( 'select' );

			const selectId = `tsf-pt-le-bulk-${taxonomySlug}`;

			selectorWrap.classList.add( 'tsf-primary-term-selector-wrap' );

			labelElement.innerText = _geti18n( taxonomySlug, 'selectPrimary' );

			selectElement.name = selectElement.id = selectId;
			selectElement.setAttribute( 'name', `autodescription-bulk[primary_term_${taxonomySlug}]` );
			labelElement.setAttribute( 'for', selectId );

			// Add options
			const noChangeOption = createElement( 'option' );
			noChangeOption.value = 'nochange';
			noChangeOption.textContent = '— No Change —';
			selectElement.appendChild( noChangeOption );

			const noneOption = createElement( 'option' );
			noneOption.value = '0';
			noneOption.textContent = `None (Clear primary ${supportedTaxonomies[ taxonomySlug ].name.toLowerCase()})`;
			selectElement.appendChild( noneOption );

			// Populate with all available terms from the checklist
			const checkboxes = checklist.querySelectorAll( `input[type=checkbox]` );

			checkboxes.forEach( checkbox => {

				const option = createElement( 'option' );

				option.value = checkbox.value;
				option.textContent = tsf.decodeEntities( checkbox.parentElement.textContent.trim() );
				selectElement.appendChild( option );
			} );

			selectorWrap.append( labelElement, selectElement );
			checklist.parentNode.insertBefore( selectorWrap, checklist.nextSibling );
		}
	}

	/**
	 * Initializes bulk edit on page load.
	 *
	 * @since 5.1.3
	 * @access private
	 */
	function _initBulkEditOnLoad() {

		// The bulk edit button might not exist yet, so we use delegation
		const table = document.querySelector( '.wp-list-table' );

		if ( ! table ) return;

		table.addEventListener(
			'click',
			event => {
				if ( event.target.matches( 'input[value="Edit"]' ) ) {
					// Wait for bulk edit row to be populated
					setTimeout( _initBulkEdit, 10 );
				}
			},
		);
	}

	return Object.assign(
		{
			/**
			 * Initialises all aspects of the scripts.
			 * You shouldn't call this.
			 *
			 * @since 5.1.3
			 * @access protected
			 *
			 * @function
			 */
			load: () => {
				document.body.addEventListener( 'tsf-onload', _initBulkEditOnLoad );
			},
			/**
			 * Exposed for le.js to call during quick edit.
			 *
			 * @since 5.1.3
			 * @access protected
			 */
			_initQuickEdit,
		},
		{
			l10n,
		},
	);
}();
window.tsfPTLE.load();
