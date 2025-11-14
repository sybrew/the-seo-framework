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
			`#edit-${postId} #${taxonomySlug}checklist, #edit-${postId} #${taxonomySlug}-all`,
		);

		if ( ! checklist ) return;

		// Check if already injected (look for selector near this checklist)
		const existingSelector = checklist.parentElement.querySelector( `.tsf-pt-le-selector-${taxonomySlug}` );

		if ( existingSelector ) return;

		const selectorWrap = document.createElement( 'div' );
		selectorWrap.className = `tsf-pt-le-selector-${taxonomySlug}`;
		selectorWrap.style.marginTop = '8px';

		const label  = document.createElement( 'label' );
		const select = document.createElement( 'select' );

		label.innerText = _geti18n( taxonomySlug, 'selectPrimary' );
		label.style.fontWeight = '600';
		label.style.display = 'block';
		label.style.marginBottom = '4px';

		select.id   = `tsf-pt-le-${taxonomySlug}-${postId}`;
		select.name = `autodescription-quick[primary_term_${taxonomySlug}]`;

		label.setAttribute( 'for', select.id );

		selectorWrap.append( label, select );

		// Insert after the checklist
		checklist.parentNode.insertBefore( selectorWrap, checklist.nextSibling );

		// Populate the select with terms from the checklist
		_populateSelector( taxonomySlug, postId, currentValue );

		// Listen for changes
		select.addEventListener(
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

		for ( const taxonomySlug in supportedTaxonomies ) {
			// Find the checklist directly
			const checklist = bulkEditRow.querySelector( `#${taxonomySlug}checklist, #${taxonomySlug}-all` );

			if ( ! checklist ) continue;

			// Check if already injected
			if ( checklist.parentElement.querySelector( `.tsf-pt-le-selector-${taxonomySlug}` ) ) continue;

			const selectorWrap = document.createElement( 'div' );
			selectorWrap.className = `tsf-pt-le-selector-${taxonomySlug}`;
			selectorWrap.style.marginTop = '8px';

			const label  = document.createElement( 'label' );
			const select = document.createElement( 'select' );

			label.innerText = _geti18n( taxonomySlug, 'selectPrimary' );
			label.style.fontWeight = '600';
			label.style.display = 'block';
			label.style.marginBottom = '4px';

			select.id   = `tsf-pt-le-bulk-${taxonomySlug}`;
			select.name = `autodescription-bulk[primary_term_${taxonomySlug}]`;

			label.setAttribute( 'for', select.id );

			// Add options
			const noChangeOption = document.createElement( 'option' );
			noChangeOption.value = 'nochange';
			noChangeOption.textContent = '— No Change —';
			select.appendChild( noChangeOption );

			const noneOption = document.createElement( 'option' );
			noneOption.value = '0';
			noneOption.textContent = `None (Clear primary ${supportedTaxonomies[ taxonomySlug ].name.toLowerCase()})`;
			select.appendChild( noneOption );

			// Populate with all available terms from the checklist
			const checkboxes = checklist.querySelectorAll( `input[type=checkbox]` );

			checkboxes.forEach( checkbox => {

				const option = document.createElement( 'option' );

				option.value = checkbox.value;
				option.textContent = tsf.decodeEntities( checkbox.parentElement.textContent.trim() );
				select.appendChild( option );
			} );

			selectorWrap.append( label, select );
			checklist.parentNode.insertBefore( selectorWrap, checklist.nextSibling );
		}
	}

	/**
	 * Hijacks WordPress's inline edit to inject primary term selectors.
	 *
	 * @since 5.1.3
	 * @access private
	 */
	function _hijackInlineEdit() {

		const _oldInlineEditPost = window.inlineEditPost?.edit;

		if ( _oldInlineEditPost ) {
			window.inlineEditPost.edit = function ( id ) {

				const ret = _oldInlineEditPost.apply( this, arguments );

				if ( 'object' === typeof id )
					id = window.inlineEditPost?.getId( id );

				if ( id )
					_initQuickEdit( id );

				return ret;
			};
		}

		// For bulk edit, we need to listen to when it opens
		// Unfortunately WordPress doesn't provide a good hook, so we observe
		document.addEventListener(
			'DOMContentLoaded',
			() => {
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
				document.body.addEventListener( 'tsf-onload', _hijackInlineEdit );
			},
		},
		{
			l10n,
		},
	);
}();
window.tsfPTLE.load();
