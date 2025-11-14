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
 * @param {String} taxonomySlug
 * @param {String} editId The edit row ID (post ID for quick-edit, 'bulk' for bulk-edit)
 * @returns {{
 *     getWrap:                () => ?Element,
 *     getInputs:              () => Array<HTMLInputElement>,
 *     getInputsChecked:       () => Array<HTMLInputElement>,
 *     getInputsCheckedValues: () => Integer[],
 *     subscribe:              (callback: CallableFunction) => undefined,
 * }}
 */
function _termCheckboxes( taxonomySlug, editId ) {

const getWrap = () => document.querySelector(
`#edit-${editId} #${taxonomySlug}checklist, ` +
`#edit-${editId} #${taxonomySlug}-all`,
);

const getInputs = () => {
const wrap = getWrap();
return wrap
? [ ...wrap.querySelectorAll( 'input[type=checkbox]' ) ].sort( ( a, b ) => a.value - b.value )
: [];
};

const getInputsChecked = () => getInputs().filter( el => el.checked );

const getInputsCheckedValues = () => getInputsChecked().map( el => +el.value );

const subscribe = callback => {

const tick = () => callback( getInputsCheckedValues() );

const registerListeners = () => {
getInputs().forEach(
el => { el.addEventListener( 'change', tick ) },
);
}

const wrap = getWrap();

if ( wrap ) {
new MutationObserver( () => {
registerListeners(); // A new listener might've been added. Reregister.
tick();
} ).observe(
wrap,
{ childList: true },
);

registerListeners();

// Immediately invoke.
tick();
}
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

const termCheckboxes = _termCheckboxes( taxonomySlug, postId );

// Find the fieldset containing the checklist
const checklist = termCheckboxes.getWrap();

if ( ! checklist ) continue;

const fieldset = checklist.closest( 'fieldset' );

if ( ! fieldset ) continue;

// Check if already injected
if ( fieldset.querySelector( '.tsf-primary-term-selector-wrap' ) ) continue;

const selectId   = `tsf-pt-le-${taxonomySlug}-${postId}`,
  selectName = `autodescription-quick[primary_term_${taxonomySlug}]`;

// Use wp.template to generate the selector HTML and inject inside fieldset
fieldset.insertAdjacentHTML(
'beforeend',
wp.template( 'tsf-primary-term-selector-le' )( {
selectId,
selectName,
i18n: {
selectPrimary: _geti18n( taxonomySlug, 'selectPrimary' ),
},
} ),
);

const selectElement = document.getElementById( selectId );

if ( ! selectElement ) continue;

// Repopulate select with checked terms
const repopulateSelect = () => {

selectElement.innerHTML = '';

const checkedInputs = termCheckboxes.getInputsChecked();

checkedInputs.forEach( checkbox => {

const option = document.createElement( 'option' );

option.value = checkbox.value;
option.textContent = tsf.decodeEntities( checkbox.parentElement.textContent.trim() );

if ( +checkbox.value === +currentValue )
option.selected = true;

selectElement.appendChild( option );
} );

// Validate selection - if current value is not in the list, select first
if (
   currentValue
&& ! selectElement.querySelector( `option[value="${currentValue}"]` )
&& selectElement.options.length
)
selectElement.options[0].selected = true;
};

// Subscribe to checkbox changes
termCheckboxes.subscribe( repopulateSelect );

// Listen for changes to dispatch events
selectElement.addEventListener(
'change',
event => {
dispatchUpdateEvent( +event.target.value, taxonomySlug );
},
);
}
}

/**
 * Initializes primary term selection for bulk edit.
 *
 * @since 5.1.3
 * @access private
 */
function _initBulkEdit() {

for ( const taxonomySlug in supportedTaxonomies ) {
const termCheckboxes = _termCheckboxes( taxonomySlug, 'bulk' );

// Find the fieldset containing the checklist
const checklist = termCheckboxes.getWrap();

if ( ! checklist ) continue;

const fieldset = checklist.closest( 'fieldset' );

if ( ! fieldset ) continue;

// Check if already injected
if ( fieldset.querySelector( '.tsf-primary-term-selector-wrap' ) ) continue;

const selectId   = `tsf-pt-le-bulk-${taxonomySlug}`,
  selectName = `autodescription-bulk[primary_term_${taxonomySlug}]`;

// Use wp.template to generate the selector HTML and inject inside fieldset
fieldset.insertAdjacentHTML(
'beforeend',
wp.template( 'tsf-primary-term-selector-le-bulk' )( {
selectId,
selectName,
i18n: {
selectPrimary: _geti18n( taxonomySlug, 'selectPrimary' ),
},
} ),
);

// Populate with all available terms from the checklist
const selectElement = document.getElementById( selectId );

if ( selectElement ) {
const checkboxes = checklist.querySelectorAll( `input[type=checkbox]` );

checkboxes.forEach( checkbox => {

const option = document.createElement( 'option' );

option.value = checkbox.value;
option.textContent = tsf.decodeEntities( checkbox.parentElement.textContent.trim() );
selectElement.appendChild( option );
} );
}
}
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
// Initialization happens via le.js _hijackListeners
},
/**
 * Exposed for le.js to call during quick edit.
 *
 * @since 5.1.3
 * @access protected
 */
_initQuickEdit,
/**
 * Exposed for le.js to call during bulk edit.
 *
 * @since 5.1.3
 * @access protected
 */
_initBulkEdit,
},
{
l10n,
},
);
}();
window.tsfPTLE.load();
