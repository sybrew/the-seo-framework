/**
 * This file holds The SEO Framework plugin's JS code.
 * Serve JavaScript as an addition, not as a means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link https://wordpress.org/plugins/autodescription/
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * @since 4.0.0 Thinned code, spread over more files.
 *
 * @constructor
 */
window.tsf = function () {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 4.0.0
	 * @access public
	 * @type {(Object<string,*>)|boolean|null} l10n Localized strings
	 */
	const l10n = tsfL10n;

	/**
	 * Mimics PHP's strip_tags in a rudimentarily form, without allowed tags.
	 *
	 * PHP's version checks every single character to comply with the allowed tags,
	 * whereas we simply use regex. This acts as a carbon-copy, regardless.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @param {String} str The text to strip tags from.
	 * @return {String} The stripped tags.
	 */
	function stripTags( str ) {
		return str.length && str.replace( /(<([^>]+)?>?)/ig, '' ) || '';
	}

	let _decodeEntitiesDOMParser = void 0,
		_decodeEntitiesMap       = {
			'<':  '&#x3C;',
			'>':  '&#x3E;',
			"\\": '&#x5C;',
		};
	/**
	 * Decodes string entities securely.
	 *
	 * Uses a fallback when the browser doesn't support DOMParser.
	 * This fallback sends out exactly the same output.
	 *
	 * The rendering of this function is considered secure against XSS attacks.
	 * However, you must consider the output as insecure HTML, and may only append via innerText.
	 *
	 * @since 4.0.0
	 * @access public
	 * @see tsf.escapeString;
	 *
	 * @credit <https://stackoverflow.com/questions/1912501/unescape-html-entities-in-javascript/34064434#34064434>
	 * Modified to allow <, >, and \ entities, and cached the parser.
	 *
	 * @param {String} str The text to decode.
	 * @return {String} The decoded text.
	 */
	function decodeEntities( str ) {

		if ( ! str?.length ) return '';

		_decodeEntitiesDOMParser ||= new DOMParser();

		return _decodeEntitiesDOMParser.parseFromString(
			// Prevent "tags" from being stripped. When not string, return ''.
			str.replace?.( /[<>\\]/g, m => _decodeEntitiesMap[ m ] ) || '',
			'text/html'
		).documentElement.textContent;
	}

	/**
	 * Escapes input string.
	 *
	 * @since 3.0.1
	 * @since 3.1.2 Now escapes backslashes correctly.
	 * @since 4.0.0 1. Now escapes all backslashes, instead of only double.
	 *              2. Now escapes forward slashes:
	 *                 Although unlikely, some HTML parsers may omit the closing " of an attribute,
	 *                 which may cause the slash to close the HTML tag.
	 * @access public
	 *
	 * @param {string} str
	 * @return {string}
	 */
	function escapeString( str ) {

		if ( ! str?.length ) return '';

		let map = {
			'&':  '&#x26;',
			'<':  '&#x3C;',
			'>':  '&#x3E;',
			'"':  '&#x22;',
			"'":  '&#x27;',
			"\\": '&#x5C;',
			"/":  '&#x2F;',
		};

		// When not string, return ''
		return str.replace?.( /[&<>"'\\\/]/g, m => map[ m ] ) || '';
	}

	/**
	 * Converts ampersands HTML entity (`&amp;`) to text (`&`).
	 *
	 * @since 4.0.0
	 * @since 5.1.0 Deprecated, unused internally. Should've also been named ampEntitytoHTML.
	 * @deprecated
	 * @access public
	 *
	 * @param {string} str
	 * @return {string}
	 */
	function ampHTMLtoText( str ) {
		deprecatedFunc( 'tsf.ampHTMLtoText', '5.1.0' );
		return str.replace( /&amp;|&#x0{0,3}26;|&#38;/gi, '&' ); // why 0,3 and not * ?
	}

	/**
	 * Removes duplicated spaces from strings.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @param {string} str
	 * @return {string}
	 */
	function sDoubleSpace( str ) {
		return str.replace( /\s{2,}/g, ' ' );
	}

	/**
	 * Removes line feeds from strings.
	 *
	 * @since 4.2.0
	 * @access public
	 *
	 * @param {string} str
	 * @return {string}
	 */
	function sSingleLine( str ) {
		return str.replace( /[\x0A\x0B\x0C\x0D]/g, ' ' );
	}

	/**
	 * Removes line feeds from strings.
	 *
	 * @since 4.2.0
	 * @access public
	 *
	 * @param {string} str
	 * @return {string}
	 */
	function sTabs( str ) {
		return str.replace( /\x09/g, ' ' );
	}

	/**
	 * A helper function allows coalescing based on string length.
	 * If the string is of length 0, it'll return null. Otherwise, it'll return the string.
	 *
	 * E.g., coalesceStrlen( '0' ) ?? '1'; will return '0'.
	 * But, coalesceStrlen( '' ) ?? '1'; will return '1'.
	 *
	 * @since 5.0.5
	 * @access public
	 *
	 * @param {string} str The string to coalesce.
	 * @return {?string} The input string if it's at least 1 byte, null otherwise.
	 */
	function coalesceStrlen( str ) {
		return str?.length ? str : null;
	}

	/**
	 * Gets string length.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @param {string} str
	 * @return {number}
	 */
	function getStringLength( str ) {
		let e,
			length = 0;

		if ( str.length ) {
			e = document.createElement( 'span' );
			e.innerHTML = escapeString( str ).trim();
			// Trimming can lead to empty child nodes. Test for undefined.
			length = e.childNodes?.[0].nodeValue.length || 0;
		}

		return +length;
	}

	/**
	 * Sets select option by value.
	 * First tests the value attribute, then the label and content.
	 *
	 * @since 4.0.0
	 * @since 5.1.0 Now also tries to select by label, which is tried together with the content.
	 * @access public
	 *
	 * @param {HTMLSelectElement} element The element to select an item in.
	 * @param {string}            value   The value of the element of set the index to.
	 */
	function selectByValue( element, value ) {

		if ( ! element instanceof HTMLSelectElement ) return;

		// Try by value first.
		for ( const option of element.options )
			if ( value == option.value ) { // Weak check
				element.selectedIndex = option.index;
				return;
			}

		// Try by label and content together.
		for ( const option of element.options )
			if ( value == option.label || value == option.innerHTML ) { // Weak check
				element.selectedIndex = option.index;
				return;
			}
	}

	/**
	 * Tries to convert JSON response to values if not already set.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @param {(object|string|undefined)} response
	 * @return {(object|undefined)}
	 */
	function convertJSONResponse( response ) {

		let isJSON = 1 === response?.json;

		if ( ! isJSON ) {
			let _response = response;

			try {
				response = JSON.parse( response );
				isJSON   = true;
			} catch ( e ) {
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
	 * Visualizes AJAX loading time through target class change.
	 *
	 * @since 2.7.0
	 * @access public
	 *
	 * @param {String|Element|jQuery.Element} target
	 */
	function setAjaxLoader( target ) {

		if ( 'string' === typeof target )
			return document.querySelectorAll( target ).forEach( setAjaxLoader );

		// Backward compat: extract jQuery Element
		if ( target?.[0] )
			target = target[0];

		target?.classList.add( 'tsf-loading' );
	}

	/**
	 * Adjusts class loaders on AJAX response.
	 *
	 * @since 2.7.0
	 * @access public
	 *
	 * @param {String|Element|jQuery.Element} target
	 * @param {Boolean} success
	 */
	function unsetAjaxLoader( target, success ) {

		if ( 'string' === typeof target )
			return document.querySelectorAll( target ).forEach( el => unsetAjaxLoader( el, success ) );

		let newclass = 'tsf-success',
			fadeTime = 2500;

		if ( ! success ) {
			newclass = 'tsf-error';
			fadeTime = 5000;
		}

		// Backward compat: extract jQuery Element
		if ( target?.[0] )
			target = target[0];

		if ( target ) {
			target.classList.remove( 'tsf-loading', 'tsf-error', 'tsf-success', 'tsf-unknown' );
			target.classList.add( newclass );
			tsfUI.fadeOut( target, fadeTime );
		}
	}

	/**
	 * Cleans and resets Ajax wrapper class and contents to default.
	 * Also stops any animation and resets fadeout to beginning.
	 *
	 * @since 2.7.0
	 * @since 4.1.0 Now ends animations instead of halting. Instantly shows the element again.
	 * @access public
	 *
	 * @param {String|Element|jQuery.Element} target
	 */
	function resetAjaxLoader( target ) {

		if ( 'string' === typeof target )
			return document.querySelectorAll( target ).forEach( resetAjaxLoader );

		// Backward compat: extract jQuery Element
		if ( target?.[0] )
			target = target[0];

		target.replaceChildren();
		target.style.animation = null;
		target.style.opacity = '1';
		target.classList.remove( 'tsf-loading', 'tsf-error', 'tsf-success', 'tsf-unknown' );
	}

	/**
	 * Outputs deprecation warning to console.
	 *
	 * @since 4.1.0
	 * @access protected
	 *
	 * @param {string} name
	 * @param {string} version
	 * @param {string} replacement
	 */
	function deprecatedFunc( name, version, replacement ) {
		// This will be removed during minification, because we have "removeConsole" enabled via babel-tsf.
		console.warn(
			`[DEPRECATED]: ${name} is deprecated${version ? ` since The SEO Framework ${version}` : ''}.${replacement ? ` Use ${replacement} instead.` : ''}`
		);
	}

	let _dispatchEvents      = new Set(),
		_loadedDispatchEvent = false;
	/**
	 * Offsets callback to interactive event.
	 *
	 * @since 4.2.1
	 * @access public
	 *
	 * @param {Element} element   The element to dispatch the event upon.
	 * @param {string}  eventName The event name to trigger. Mustn't be custom.
	 */
	function dispatchAtInteractive( element, eventName ) {

		_dispatchEvents.add( [ element, eventName ] );

		if ( ! _loadedDispatchEvent ) {
			document.body.addEventListener( 'tsf-interactive', _loopDispatchAtInteractive );
			_loadedDispatchEvent = true;
		}
	}

	/**
	 * Runs callbacks at interactive event.
	 *
	 * @since 4.2.0
	 * @access private
	 */
	function _loopDispatchAtInteractive() {
		_dispatchEvents.forEach( ( [ element, eventName ] ) => {
			element.dispatchEvent( new Event( eventName ) );
		} );
	}

	/**
	 * Invokes notice dismissal listener reset.
	 *
	 * @since 4.1.2
	 * @access public
	 * @todo deprecate 5.2: Move it to tsfUI instead. Also split the trigger and the dispatch into two functions,
	 *       so that we need not create a constant function.
	 *
	 * @function
	 */
	const triggerNoticeReset = tsfUtils.debounce(
		() => { document.body.dispatchEvent( new CustomEvent( 'tsf-reset-notice-listeners' ) ) },
		100, // Magic number. Low enough not to cause ignored clicks, high enough not to cause lag.
	);

	let _throttleResize = false;
	const _debounceResize        = tsfUtils.debounce( () => { _throttleResize = false }, 50 ),
		  _debounceResizeTrigger = tsfUtils.debounce( _triggerResize, 50 );
	/**
	 * Dispatches tsf-resize event on window during resize events.
	 *
	 * It fires immediately, after which it's debounced indefinitely until 100ms passed.
	 * Once debounce is passed, another immediate trigger can happen again.
	 *
	 * Because it must fire immediately first, we require two debouncers.
	 *
	 * @since 4.2.0
	 * @access private
	 */
	function _triggerResize() {

		_debounceResize();

		if ( _throttleResize ) {
			_debounceResizeTrigger();
		} else {
			_throttleResize = true;
			dispatchEvent( new CustomEvent( 'tsf-resize' ) );
		}
	}

	let isInteractive = false;
	/**
	 * Dispatches tsf-interactive event once.
	 *
	 * This fires as soon as all TSF script are done loading. A few more may load here that rely on user interaction.
	 * Use case: User is expected to interact confidently with the page. (This obviously isn't true, since WP is slow, but one day...)
	 *
	 * Feel free to asynchronously do things at this point.
	 *
	 * Example: jQuery( document.body ).on( 'tsf-interactive', myFunc );
	 * Or:      document.body.addEventListener( 'tsf-interactive', myFunc );
	 *
	 * @since 4.1.1
	 * @access private
	 */
	function _triggerInteractive() {
		if ( ! isInteractive ) {
			isInteractive = true;
			document.body.dispatchEvent( new CustomEvent( 'tsf-interactive' ) );
		}
	}

	/**
	 * Dispatches tsf-ready event.
	 *
	 * This fires as soon as all TSF scripts have registered their interactions.
	 * Use case: User may still see elements painting.
	 *
	 * You should not work asynchronously here.
	 * ...yet we do by triggering "enqueueTriggerInput" events. We need to fix that.
	 *
	 * Example: jQuery( document.body ).on( 'tsf-ready', myFunc );
	 * Or:      document.body.addEventListener( 'tsf-ready', myFunc );
	 *
	 * @since 2.9.0
	 * @access private
	 */
	function _triggerReady() {
		document.body.dispatchEvent( new CustomEvent( 'tsf-ready' ) );
	}

	/**
	 * Dispatches 'tsf-onload' event.
	 *
	 * This fires as soon as all TSF scripts are loaded.
	 * Use case: User still sees a white screen, window has yet to be painted.
	 *
	 * You should not work asynchronously here.
	 *
	 * Example: jQuery( document.body ).on( 'tsf-onload, myFunc );
	 * Or:      document.body.addEventListener( 'tsf-onload', myFunc );
	 *
	 * @since 3.1.0
	 * @access private
	 */
	function _triggerOnLoad() {
		document.body.dispatchEvent( new CustomEvent( 'tsf-onload' ) );
	}

	let _isReady = false;
	/**
	 * Runs document-on-ready actions.
	 *
	 * @since 3.0.0
	 * @access private
	 */
	function _doReady() {

		if ( _isReady ) return;

		document.removeEventListener( 'DOMContentLoaded', _doReady );
		document.removeEventListener( 'load', _doReady );

		// Triggers tsf-onload event.
		_triggerOnLoad();

		// Trigger tsf-ready event.
		_triggerReady();

		_isReady = true;

		// Trigger tsf-interactive event. 'load' might be too late 'cause images are loading (slow 3G)...
		document.addEventListener( 'load', _triggerInteractive );
		// ...so we also trigger it with a timeout. Whichever comes first.
		setTimeout( _triggerInteractive, 100 ); // Magic number. Low enough to be negligible. High enough to let other scripts finish.
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
		 */
		load: () => {

			/**
			 * From: https://developer.akamai.com/blog/2017/12/04/beware-performancetimingdominteractive
			 * "Overall, I found that pages with external (not in the HTML) CSS, JavaScript, or fonts
			 * could lead to inaccurate estimations of domInteractive"
			 */
			// document.addEventListener( 'readystatechange', () => {
			// 	// Interactive means that the document was fully read. However, we cannot reliably determine if all dependencies have been loaded?
			// 	[ 'interactive', 'complete' ].includes( document.readyState ) && _doReady();
			// } );

			/**
			 * The code below isn't affected by the above mentioned issues; albeit not as smoothly executed as we'd like...
			 * such as any page on theseoframework.com; which benefit from considering load order & inline scripts, making for seamless rendering.
			 *
			 * WordPress admin always forces us to load JS assets last--at least, when we do things by their book. We should
			 * honor this, at the expense of extra layout shifts and delayed rendering of critical markup.
			 *
			 * @source jQuery 3.5.1
			 */
			if ( document.readyState === 'complete' ||
				( document.readyState !== 'loading' && ! document.documentElement.doScroll ) ) {
				// Handle it asynchronously to allow scripts the opportunity to delay ready.
				setTimeout( _doReady() );
			} else {
				document.addEventListener( 'DOMContentLoaded', _doReady );
				document.addEventListener( 'load', _doReady );
			}

			// Trigger tsf-resize event.
			window.addEventListener( 'resize', _triggerResize );
		},
	}, {
		stripTags,
		decodeEntities,
		escapeString,
		ampHTMLtoText,
		sDoubleSpace,
		sSingleLine,
		sTabs,
		coalesceStrlen,
		getStringLength,
		selectByValue,
		convertJSONResponse,
		setAjaxLoader,
		unsetAjaxLoader,
		resetAjaxLoader,
		deprecatedFunc,
		triggerNoticeReset,
		dispatchAtInteractive,
	}, {
		l10n,
	} );
}();
window.tsf.load();
