/**
 * This file holds The SEO Framework plugin's JS code for the Post SEO Settings.
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
 * Holds tsfTerm values in an object to avoid polluting global namespace.
 *
 * @since 4.0.0
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfTerm = function( $ ) {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 4.0.0
	 * @access public
	 * @type {(Object<string, *>)|boolean|null} l10n Localized strings
	 */
	const l10n = 'undefined' !== typeof tsfTermL10n && tsfTermL10n;

	/**
	 * @since 4.1.0
	 * @access private
	 * @type {string}
	 */
	const _titleId = 'autodescription-meta[doctitle]';
	/**
	 * @since 4.1.0
	 * @access private
	 * @type {string}
	 */
	const _descId = 'autodescription-meta[description]';

	/**
	 * Initializes Canonical URL meta input listeners.
	 *
	 * @since 4.0.0
	 * @since 4.1.2 Changed name from _initCanonicalInput
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initVisibilityListeners = () => {
		const indexSelect = document.getElementById( 'autodescription-meta[noindex]' );

		let canonicalUrl    = '',
			showcanonicalPh = true;

		/**
		 * @since 4.1.2
		 *
		 * @function
		 * @param {string} link
		 * @return {undefined}
		 */
		const updateCanonicalPlaceholder = () => {
			let canonicalInput = document.getElementById( 'autodescription-meta[canonical]' );

			if ( ! canonicalInput ) return;

			// Link might not've been updated (yet). Fill it in with PHP-supplied value (if any).
			canonicalUrl = canonicalUrl || canonicalInput.placeholder;

			if ( ! showcanonicalPh ) {
				canonicalInput.placeholder = '';
			} else {
				canonicalInput.placeholder = canonicalUrl;
			}
		}

		/**
		 * @since 4.1.2
		 *
		 * @function
		 * @param {Number} value
		 * @return {undefined}
		 */
		const setRobotsIndexingState = value => {
			let type = '';

			switch ( +value ) {
				case 0: // default, unset since unknown.
					type = indexSelect.dataset.defaultUnprotected;
					break;
				case -1: // index
					type = 'index';
					break;
				case 1: // noindex
					type = 'noindex';
					break;
			}
			if ( 'noindex' === type ) {
				showcanonicalPh = false;
			} else {
				showcanonicalPh = true;
			}

			updateCanonicalPlaceholder();
		}
		indexSelect.addEventListener( 'change', event => setRobotsIndexingState( event.target.value ) );

		setRobotsIndexingState( indexSelect.value );
	}

	/**
	 * Initializes Title meta input listeners.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initTitleListeners = () => {

		const titleInput      = document.getElementById( _titleId );
		const blogNameTrigger = document.getElementById( 'autodescription-meta[title_no_blog_name]' );

		tsfTitle.setInputElement( titleInput );

		let state = JSON.parse(
			document.getElementById( 'tsf-title-data_' + _titleId ).dataset.state
		);

		tsfTitle.updateStateOf( _titleId, 'allowReferenceChange', ! state.refTitleLocked );
		tsfTitle.updateStateOf( _titleId, 'defaultTitle', state.defaultTitle.trim() );
		tsfTitle.updateStateOf( _titleId, 'addAdditions', state.addAdditions );
		tsfTitle.updateStateOf( _titleId, 'useSocialTagline', !! ( state.useSocialTagline || false ) );
		tsfTitle.updateStateOf( _titleId, 'additionValue', state.additionValue.trim() );
		tsfTitle.updateStateOf( _titleId, 'additionPlacement', state.additionPlacement );
		tsfTitle.updateStateOf( _titleId, 'hasLegacy', !! ( state.hasLegacy || false ) );

		// tsfTitle shouldn't be aware of this--since we remove the prefix on-input.
		const termPrefix = tsf.escapeString( l10n.params.termPrefix );

		/**
		 * Updates title additions, based on singular settings change.
		 *
		 * @function
		 * @param {Event} event
		 * @return {undefined}
		 */
		const updateTitleAdditions = event => {
			let prevAddAdditions = tsfTitle.getStateOf( _titleId, 'addAdditions' ),
				addAdditions     = ! event.target.checked;

			if ( l10n.params.additionsForcedDisabled ) {
				addAdditions = false;
			}

			if ( prevAddAdditions !== addAdditions ) {
				tsfTitle.updateStateOf( _titleId, 'addAdditions', addAdditions );
			}
		}
		if ( blogNameTrigger ) {
			blogNameTrigger.addEventListener( 'change', updateTitleAdditions );
			blogNameTrigger.dispatchEvent( new Event( 'change' ) );
		}

		//!? Disabled as we don't add prefixes when using a custom title:
		//
		// /**
		//  * Sets term prefix.
		//  *
		//  * @function
		//  * @param {string} visibility
		//  * @return {undefined}
		//  */
		// const setTermPrefixValue = ( event ) => {
		// 	let oldPrefixValue = tsfTitle.getState( 'prefixValue' ),
		// 		prefixValue    = '';

		// 	if ( ! event.target.value.length ) {
		// 		prefixValue = l10n.params.termPrefix;
		// 	}

		// 	if ( prefixValue !== oldPrefixValue )
		// 		tsfTitle.updateState( 'prefixValue', prefixValue );
		// }
		// $( titleInput ).on( 'input', setTermPrefixValue );

		/**
		 * Updates default title placeholder.
		 *
		 * @function
		 * @param {string} value
		 * @return {undefined}
		 */
		const updateDefaultTitle = ( val ) => {
			val = typeof val === 'string' && val.trim() || '';

			let title = tsfTitle.stripTitleTags ? tsf.stripTags( val ) : val;

			title = title || tsfTitle.untitledTitle;

			let defaultTitle;

			if ( tsf.l10n.states.isRTL ) {
				defaultTitle = title + ' ' + termPrefix;
			} else {
				defaultTitle = termPrefix + ' ' + title;
			}

			tsfTitle.updateStateOf( _titleId, 'defaultTitle', defaultTitle );
		}
		const termNameInput = document.querySelector( '#edittag #name' );
		termNameInput && termNameInput.addEventListener( 'input', event => updateDefaultTitle( event.target.value ) );

		tsfTitle.enqueueUnregisteredInputTrigger( _titleId );
	}

	/**
	 * Initializes description meta input listeners.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initDescriptionListeners = () => {

		let state = JSON.parse(
			document.getElementById( 'tsf-description-data_' + _descId ).dataset.state
		);

		tsfDescription.setInputElement( document.getElementById( _descId ) );

		// tsfDescription.updateState( 'allowReferenceChange', ! state.refDescriptionLocked );
		tsfDescription.updateStateOf( _descId, 'defaultDescription', state.defaultDescription.trim() );
		tsfDescription.updateStateOf( _descId, 'hasLegacy', !! ( state.hasLegacy || false ) );

		// TODO set term-description-content (via ajax) listeners?

		tsfDescription.enqueueUnregisteredInputTrigger( _descId );
	}

	/**
	 * Initializes settings scripts on TSF-load.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _loadSettings = () => {
		_initVisibilityListeners();
		_initTitleListeners();
		_initDescriptionListeners();
	}

	/**
	 * Initializes settings scripts on TSF-ready.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now registers the refNa title input.
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _readySettings = () => {

		tsfSocial.initTitleInputs( {
			ref:   document.getElementById( 'tsf-title-reference_' + _titleId ),
			refNa: document.getElementById( 'tsf-title-noadditions-reference_' + _titleId ),
			meta:  document.getElementById( _titleId ),
			og:    document.getElementById( 'autodescription-meta[og_title]' ),
			tw:    document.getElementById( 'autodescription-meta[tw_title]' ),
		} );

		tsfSocial.initDescriptionInputs( {
			ref:  document.getElementById( 'tsf-description-reference_' + _descId ),
			meta: document.getElementById( _descId ),
			og:   document.getElementById( 'autodescription-meta[og_description]' ),
			tw:   document.getElementById( 'autodescription-meta[tw_description]' ),
		} );
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
			document.body.addEventListener( 'tsf-onload', _loadSettings );
			document.body.addEventListener( 'tsf-ready', _readySettings );
		}
	}, {
		// No public methods.
	}, {
		l10n
	} );
}( jQuery );
window.tsfTerm.load();
