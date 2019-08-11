/**
 * This file holds The SEO Framework plugin's JS code.
 * Serve JavaScript as an addition, not as a means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link https://wordpress.org/plugins/autodescription/
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * The more you know 🌈⭐ We use this babel configuration:
 *
 * "browserslist": "> 1.25%, not dead",
 * "devDependencies": {
 *   "@babel/core": "^7.3.4",
 *   "@babel/preset-env": "^7.3.4",
 *   "babel-minify": "^0.5.0",
 *   "browserslist": "^4.0.0"
 * }
 */

/**
 * Holds The SEO Framework values in an object to avoid polluting global namespace.
 *
 * @since 2.2.4
 * @since 4.0.0 Thinned code over more files.
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsf = function( $ ) {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 4.0.0
	 * @access public
	 * @type {(Object<string, *>)|boolean|null} l10n Localized strings
	 */
	const l10n = 'undefined' !== typeof tsfL10n && tsfL10n;

	/**
	 * Mimics PHP's strip_tags in a rudimentarily form, without allowed tags.
	 *
	 * PHP's version checks every single character to comply with the allowed tags,
	 * whereas we simply use regex. This acts as a carbon-copy, regardless.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @function
	 * @param {String} str The text to strip tags from.
	 * @return {String} The stripped tags.
	 */
	const stripTags = str => str.length && str.replace( /(<([^>]+)?>?)/ig, '' ) || '';

	let _canUseDOMParserTest = void 0;
	/**
	 * Tests if DOMParser is supported.
	 *
	 * @since 4.0.0
	 *
	 * @return {Boolean}
	 */
	const _canUseDOMParser = () => {
		if ( void 0 === _canUseDOMParserTest ) {
			try {
				if ( ( new DOMParser() ).parseFromString( '', 'text/html' ) ) {
					// text/html parsing is natively supported
					_canUseDOMParserTest = true;
				}
			} catch ( e ) { }

			_canUseDOMParserTest = !! _canUseDOMParserTest;
		}

		return _canUseDOMParserTest;
	}

	/**
	 * Decodes string entities securely.
	 *
	 * Uses a fallback when the browser doesn't support DOMParser.
	 * This fallback sends out exactly the same output.
	 *
	 * The output of this function is considered secure against XSS attacks.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @credit <https://stackoverflow.com/questions/1912501/unescape-html-entities-in-javascript/34064434#34064434>
	 * Modified to allow <, >, and \ entities.
	 *
	 * @param {String} str The text to decode.
	 * @return {String} The decoded text.
	 */
	const decodeEntities = ( str ) => {

		if ( ! str.length ) return '';

		let map = {
			'<':  '&#x3C;',
			'>':  '&#x3E;',
			"\\": '&#x5C;',
		};
		// Prevent "tags" from being stripped.
		str = str.replace( /[<>\\]/g, m => map[ m ] );

		if ( _canUseDOMParser() ) {
			str = ( new DOMParser() ).parseFromString( str, 'text/html' ).documentElement.textContent;
		} else {
			let el = document.createElement( 'span' );
			el.innerHTML = str;
			str = ampHTMLtoText( el.textContent );
		}

		return str;
	}

	/**
	 * Escapes input string.
	 *
	 * @since 3.0.1
	 * @since 3.1.2 Now escapes backslashes correctly.
	 * @since 4.0.0: 1. Now escapes all backslashes, instead of only double.
	 *               2. Now escapes forward slashes:
	 *                  Although unlikely, some HTML parsers may omit the closing " of an attribute,
	 *                  which may cause the slash to close the HTML tag.
	 * @access public
	 *
	 * @function
	 * @param {string} str
	 * @return {string}
	 */
	const escapeString = ( str ) => {

		if ( ! str.length ) return '';

		let map = {
			'&':  '&#x26;',
			'<':  '&#x3C;',
			'>':  '&#x3E;',
			'"':  '&#x22;',
			"'":  '&#x27;',
			"\\": '&#x5C;',
			"/":  '&#x2F;',
		};

		return str.replace( /[&<>"'\\\/]/g, m => map[ m ] );
	}

	/**
	 * Converts ampersands HTML entity (`&amp;`) to text (`&`).
	 *
	 * @since 4.0.0
	 * @access public

	 * @function
	 * @param {string} str
	 * @return {string}
	 */
	const ampHTMLtoText = str => str.replace( /&amp;|&#x0{0,3}26;|&#38;/gi, '&' );

	/**
	 * Removes duplicated spaces in strings.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @function
	 * @param {string} str
	 * @return {string}
	 */
	const sDoubleSpace = str => str.replace( /\s\s+/g, ' ' );

	/**
	 * Gets string length.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @function
	 * @param {string} str
	 * @return {number}
	 */
	const getStringLength = ( str ) => {
		let e,
			length = 0;

		if ( str.length ) {
			e = document.createElement( 'span' );
			e.innerHTML = escapeString( str ).trim();
			// Trimming can lead to empty child nodes. Test for undefined.
			if ( 'undefined' !== typeof e.childNodes[0] )
				length = e.childNodes[0].nodeValue.length;
		}
		return +length;
	}

	/**
	 * Sets select option by value.
	 * First tests the value attribute, then the content.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @function
	 * @param {HTMLSelectElement} element The element to select an item in.
	 * @param {string}            value   The value of the element ot set the index to.
	 * @return {undefined}
	 */
	const selectByValue = ( element, value ) => {

		if ( ! element instanceof HTMLSelectElement ) return;

		let _newIndex = void 0;

		// Try by value.
		for ( let i = 0; i < element.options.length; ++i ) {
			if ( value == element.options[ i ].value ) {
				_newIndex = i;
				break;
			}
		}

		if ( void 0 === _newIndex ) {
			// Try by content.
			for ( let i = 0; i < element.options.length; ++i ) {
				if ( value == element.options[ i ].innerHTML ) {
					_newIndex = i;
					break;
				}
			}
		}

		if ( void 0 !== _newIndex ) {
			element.selectedIndex = _newIndex;
		}
	}

	/**
	 * Tries to convert JSON response to values if not already set.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @function
	 * @param {(object|string|undefined)} response
	 * @return {(object|undefined)}
	 */
	const convertJSONResponse = ( response ) => {

		let testJSON = response && response.json || void 0,
			isJSON   = 1 === testJSON;

		if ( ! isJSON ) {
			let _response = response;

			try {
				response = JSON.parse( response );
				isJSON = true;
			} catch ( error ) {
				isJSON = false;
			}

			if ( ! isJSON ) {
				// Reset response.
				response = _response;
			}
		}

		return response;
	}

	/**
	 * Dismissible notices. Uses class .tsf-notice.
	 *
	 * @since 2.6.0
	 * @since 2.9.3 Now correctly removes the node from DOM.
	 * @access private
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	const _dismissNotice = ( event ) => {
		$( event.target ).parents( '.tsf-notice' ).slideUp( 200, function() {
			this.remove();
		} );
	}

	/**
	 * Visualizes AJAX loading time through target class change.
	 *
	 * @since 2.7.0
	 * @access public
	 *
	 * @function
	 * @param {String|Element|jQuery.Element} target
	 * @return {undefined}
	 */
	const setAjaxLoader = ( target ) => {
		$( target ).toggleClass( 'tsf-loading' );
	}

	/**
	 * Adjusts class loaders on Ajax response.
	 *
	 * @since 2.7.0
	 * @access public
	 *
	 * @function
	 * @param {String|Element|jQuery.Element} target
	 * @param {Boolean} success
	 * @return {undefined}
	 */
	const unsetAjaxLoader = ( target, success ) => {

		let newclass = 'tsf-success',
			fadeTime = 2500;

		if ( ! success ) {
			newclass = 'tsf-error';
			fadeTime = 5000;
		}

		$( target ).removeClass( 'tsf-loading' ).addClass( newclass ).fadeOut( fadeTime );
	}

	/**
	 * Cleans and resets Ajax wrapper class and contents to default.
	 * Also stops any animation and resets fadeout to beginning.
	 *
	 * @since 2.7.0
	 * @access public
	 *
	 * @function
	 * @param {String|Element|jQuery.Element} target
	 * @return {undefined}
	 */
	const resetAjaxLoader = ( target ) => {
		$( target ).stop().empty().prop( 'class', 'tsf-ajax' ).css( 'opacity', '1' ).removeProp( 'style' );
	}

	/**
	 * Sets postbox toggle handlers.
	 * TODO move to Settings.js and Post.js respectively?
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initPostboxToggle = () => {

		let $handles;

		$handles = $( '.postbox[id^="autodescription-"], .postbox#tsf-inpost-box' ).find( '.hndle, .handlediv' );

		if ( ! $handles || ! $handles.length ) return;

		let $input;

		const validate = () => {
			$input[0].reportValidity();
		}

		/**
		 * HACK: Reopens a box if it contains invalid input values, and notifies the users thereof.
		 * WordPress should implement this in a non-hacky way, so to give us more freedom.
		 *
		 * There are no needs for timeouts because this should always run later
		 * than "postboxes.handle_click", as that script is loaded earlier.
		 */
		const handleClick = ( event ) => {
			let $postbox = $( event.target ).closest( '.postbox' );
			if ( $postbox[0].classList.contains( 'closed' ) ) {
				$input = $postbox.find( 'input:invalid, select:invalid, textarea:invalid' );
				if ( $input.length ) {
					$( document ).one( 'postbox-toggled', validate );
					$( event.target ).trigger( 'click' );
				}
			}
		}
		$handles.on( 'click.tsfPostboxes', handleClick );
	}

	/**
	 * Sets tsf-ready action.
	 *
	 * Example: jQuery( document.body ).on( 'tsf-ready', myFunc );
	 *
	 * @since 2.9.0
	 * @access private
	 *
	 * @function
	 */
	const _triggerReady = () => {
		$( document.body ).trigger( 'tsf-ready' );
	}

	/**
	 * Sets 'tsf-onload' action.
	 *
	 * Example: jQuery( document.body ).on( 'tsf-onload, myFunc );
	 *
	 * @since 3.1.0
	 * @access private
	 *
	 * @function
	 */
	const _triggerOnLoad = () => {
		$( document.body ).trigger( 'tsf-onload' );
	}

	/**
	 * Runs document-on-ready actions.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @function
	 */
	const _doReady = () => {

		// Triggers tsf-onload event.
		_triggerOnLoad();

		// Sets postbox toggles on load.
		_initPostboxToggle();

		// Trigger tsf-ready event.
		_triggerReady();
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
			// Dismiss notices.
			$( '.tsf-dismiss' ).on( 'click', _dismissNotice );

			$( document.body ).ready( _doReady );
		}
	}, {
		stripTags,
		decodeEntities,
		escapeString,
		ampHTMLtoText,
		sDoubleSpace,
		getStringLength,
		selectByValue,
		convertJSONResponse,
		setAjaxLoader,
		unsetAjaxLoader,
		resetAjaxLoader,
	}, {
		l10n
	} );
}( jQuery );
jQuery( window.tsf.load );
