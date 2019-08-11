/**
 * This file holds The SEO Framework plugin's JS code for the Post SEO Settings.
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
	 * Initializes Canonical URL meta input listeners.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initCanonicalInput = () => {

		// TODO, listen to slug.

		// let canonicalInput = $( '#autodescription_canonical' );

		// if ( ! canonicalInput ) return;

		// const updateCanonical = ( link ) => {
		// 	canonicalInput.attr( 'placeholder', link );
		// }

		// $( document ).on( 'tsf-updated-gutenberg-link', ( event, link ) => updateCanonical( link ) );
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

		const metaInput       = document.getElementById( 'autodescription-meta[doctitle]' );
		const blogNameTrigger = document.getElementById( 'autodescription-meta[title_no_blog_name]' );

		tsfTitle.setInputElement( metaInput );

		const termPrefix = tsf.escapeString( l10n.params.termPrefix );

		/**
		 * Updates title additions, based on singular settings change.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const updateTitleAdditions = ( event ) => {

			let prevUseTagline = tsfTitle.getState( 'useTagline' ),
				useTagline     = ! $( event.target ).is( ':checked' );

			if ( l10n.params.additionsForcedDisabled ) {
				useTagline = false;
			}

			if ( prevUseTagline !== useTagline ) {
				tsfTitle.updateState( 'useTagline', useTagline );
			}
		}
		$( blogNameTrigger ).on( 'change', updateTitleAdditions );
		$( blogNameTrigger ).trigger( 'change' );

		//!? Disable as we don't add prefixes when using a custom title:
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
		// $( metaInput ).on( 'input', setTermPrefixValue );

		/**
		 * Updates default title placeholder.
		 *
		 * @function
		 * @param {string} value
		 * @return {undefined}
		 */
		const updateDefaultTitle = ( val ) => {
			val = typeof val === 'string' && val.trim() || '';

			let title = l10n.params.stripTitleTags ? tsf.stripTags( val ) : val;

			title = title || tsfTitle.untitledTitle;

			let defaultTitle;

			if ( tsf.l10n.states.isRTL ) {
				defaultTitle = title + ' ' + termPrefix;
			} else {
				defaultTitle = termPrefix + ' ' + title;
			}

			tsfTitle.updateState( 'defaultTitle', defaultTitle );
		}
		$( '#edittag #name' ).on( 'input', event => updateDefaultTitle( event.target.value ) );

		tsfTitle.enqueueUnregisteredInputTrigger();
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

		tsfDescription.setInputElement( document.getElementById( 'autodescription-meta[description]' ) );

		// TODO set term-description input listeners?

		tsfDescription.enqueueUnregisteredInputTrigger();
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
		_initCanonicalInput();
		_initTitleListeners();
		_initDescriptionListeners();
	}

	/**
	 * Initializes settings scripts on TSF-ready.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _readySettings = () => {

		tsfSocial.initTitleInputs( {
			ref:  document.getElementById( 'tsf-title-reference' ),
			meta: document.getElementById( 'autodescription-meta[doctitle]' ),
			og:   document.getElementById( 'autodescription-meta[og_title]' ),
			tw:   document.getElementById( 'autodescription-meta[tw_title]' ),
		} );

		tsfSocial.initDescriptionInputs( {
			ref:  document.getElementById( 'tsf-description-reference' ),
			meta: document.getElementById( 'autodescription-meta[description]' ),
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
			$( document.body ).on( 'tsf-onload', _loadSettings );
			$( document.body ).on( 'tsf-ready', _readySettings );
		}
	}, {

	}, {
		l10n
	} );
}( jQuery );
jQuery( window.tsfTerm.load );
