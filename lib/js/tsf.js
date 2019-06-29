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
 * @since 3.3.0 Thinned code over more files.
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsf = function( $ ) {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 3.3.0
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

	let canDecode = void 0;
	/**
	 * Decodes string entities securely.
	 * May return an undecoded string if the browser doesn't support this functionality.
	 *
	 * @since 3.3.0
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

		if ( void 0 === canDecode ) {
			try {
				if ( ( new DOMParser() ).parseFromString( '', 'text/html' ) ) {
					// text/html parsing is natively supported
					canDecode = true;
				}
			} catch ( e ) { }

			canDecode = !! canDecode;
		}

		if ( ! canDecode ) return str;

		let map = {
			'<':    '&lt;',
			'>':    '&gt;',
			"\\":   '&#92;',
		};

		str = str.replace( /[<>]|\\\\|\\/g, m => map[ m ] );

		return ( new DOMParser() ).parseFromString( str, 'text/html' ).documentElement.textContent;
	}

	/**
	 * Escapes input string.
	 *
	 * @since 3.0.1
	 * @since 3.1.2 Now escapes backslashes correctly.
	 * @since 3.3.0 Now allows single backslashes.
	 * @access public
	 *
	 * @source <https://stackoverflow.com/a/4835406>
	 * @function
	 * @param {string} str
	 * @return {string}
	 */
	const escapeString = ( str ) => {

		if ( ! str.length ) return '';

		let map = {
			'&':    '&amp;',
			'<':    '&lt;',
			'>':    '&gt;',
			'"':    '&quot;',
			"'":    '&#039;',
			"\\":   '&#92;',
		};

		return str.replace( /[&<>"']|\\/g, m => map[ m ] );
	}

	let unescapeStringRegex = void 0;
	/**
	 * Undoes what tsf.escapeString has done.
	 *
	 * @since 3.0.4
	 * @since 3.1.0 Added IE11 compat for Object.find
	 * @since 3.1.2 Now unescapes backslashes correctly.
	 * @access public
	 *
	 * @function
	 * @param {string} str The escaped str via tsf.escapeString
	 * @return {string}
	 */
	const unescapeString = ( str ) => {

		if ( ! str.length ) return '';

		let map = {
			'&':  '&amp;',
			'<':  '&lt;',
			'>':  '&gt;',
			'"':  '&quot;',
			"'":  '&#039;',
			"\\": '&#92;',
		};

		unescapeStringRegex = unescapeStringRegex || new RegExp(
			Object.values( map ).map(
				v => v.replace( /[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, '\\$&' )
			).join( '|' ),
			'g'
		);

		return str.replace( unescapeStringRegex,
			m => Object.keys( map ).find(
				k => map[ k ] === m
			)
		);
	}

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
	const sDoubleSpace = ( str ) => {
		return str.replace( /\s\s+/g, ' ' );
	}

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
			e.innerHTML = tsf.escapeString( str ).trim(); // Trimming can lead to empty child nodes.
			if ( 'undefined' !== typeof e.childNodes[0] )
				length = e.childNodes[0].nodeValue.length;
		}
		return +length;
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
	 * @param {String} target
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
	 * @param {String} target
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
	 * @param {String} target
	 * @return {undefined}
	 */
	const resetAjaxLoader = ( target ) => {
		$( target ).stop().empty().prop( 'class', 'tsf-ajax' ).css( 'opacity', '1' ).removeProp( 'style' );
	}

	/**
	 * Sets postbox toggle handlers.
	 * TODO move to Settings.js and Post.js respectively?
	 *
	 * @since 3.3.0
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
		 * @since 3.3.0
		 * @access protected
		 *
		 * @function
		 * @return {undefined}
		 */
		load: () => {
			// If this script can parse, we can assure we're not dealing with Trident.
			// Blindly assume the user is on an updated browser; although, mobile browsers do have discrepancies.
			$( document.body ).addClass( 'tsf-js' );

			// Dismiss notices.
			$( '.tsf-dismiss' ).on( 'click', _dismissNotice );

			$( document.body ).ready( _doReady );
		}
	}, {
		stripTags,
		decodeEntities,
		escapeString,
		unescapeString,
		sDoubleSpace,
		getStringLength,
		convertJSONResponse,
		setAjaxLoader,
		unsetAjaxLoader,
		resetAjaxLoader,
	}, {
		l10n
	} );
}( jQuery );
jQuery( window.tsf.load );
