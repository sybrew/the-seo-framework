/**
 * This file holds The SEO Framework plugin's JS code.
 * Serve JavaScript as an addition, not as a means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link https://wordpress.org/plugins/autodescription/
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2022 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
				// text/html parsing is natively supported when true.
				_canUseDOMParserTest = !! ( new DOMParser() ).parseFromString( '', 'text/html' );
			} catch ( e ) { }

			_canUseDOMParserTest = !! _canUseDOMParserTest;
		}

		return _canUseDOMParserTest;
	}

	let _decodeEntitiesDOMParser = void 0;
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
	 * Modified to allow <, >, and \ entities, and cached the parser.
	 *
	 * @param {String} str The text to decode.
	 * @return {String} The decoded text.
	 */
	const decodeEntities = str => {

		if ( ! str?.length ) return '';

		let map = {
			'<':  '&#x3C;',
			'>':  '&#x3E;',
			"\\": '&#x5C;',
		};
		// Prevent "tags" from being stripped.
		str = str.replace( /[<>\\]/g, m => map[ m ] );

		if ( _canUseDOMParser() ) {
			_decodeEntitiesDOMParser = _decodeEntitiesDOMParser || new DOMParser();
			str = _decodeEntitiesDOMParser.parseFromString( str, 'text/html' ).documentElement.textContent;
		} else {
			_decodeEntitiesDOMParser = _decodeEntitiesDOMParser || document.createElement( 'span' );
			_decodeEntitiesDOMParser.innerHTML = str;
			str = ampHTMLtoText( _decodeEntitiesDOMParser.textContent );
		}

		return str;
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
	 * @function
	 * @param {string} str
	 * @return {string}
	 */
	const escapeString = str => {

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
	 * Removes duplicated spaces from strings.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @function
	 * @param {string} str
	 * @return {string}
	 */
	const sDoubleSpace = str => str.replace( /\s{2,}/g, ' ' );

	/**
	 * Removes line feeds from strings.
	 *
	 * @since 4.2.0
	 * @access public
	 *
	 * @function
	 * @param {string} str
	 * @return {string}
	 */
	const sSingleLine = str => str.replace( /[\x0A\x0B\x0C\x0D]/g, ' ' );

	/**
	 * Removes line feeds from strings.
	 *
	 * @since 4.2.0
	 * @access public
	 *
	 * @function
	 * @param {string} str
	 * @return {string}
	 */
	 const sTabs = str => str.replace( /\x09/g, ' ' );

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
	const getStringLength = str => {
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
	 * First tests the value attribute, then the content.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @function
	 * @param {HTMLSelectElement} element The element to select an item in.
	 * @param {string}            value   The value of the element ot set the index to.
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
	const convertJSONResponse = response => {

		let testJSON = response && response.json || void 0,
			isJSON   = 1 === testJSON;

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
	 * @function
	 * @param {String|Element|jQuery.Element} target
	 */
	const setAjaxLoader = target => {
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
	 * @since 4.1.0 Now ends animations instead of halting. Instantly shows the element again.
	 * @access public
	 *
	 * @function
	 * @param {String|Element|jQuery.Element} target
	 */
	const resetAjaxLoader = target => {
		$( target ).stop( false, true ).empty().prop( 'class', 'tsf-ajax' ).show();
	}

	/**
	 * Outputs deprecation warning to console.
	 *
	 * @since 4.1.0
	 * @access private
	 *
	 * @function
	 * @param {string} target
	 * @param {string} version
	 * @param {string} replacement
	 */
	const deprecatedFunc = ( name, version, replacement ) => {
		version     = version ? ` since The SEO Framework ${version}` : '';
		replacement = replacement ? ` Use ${replacement} instead.` : '';
		console.warn( `[DEPRECATED]: ${name} is deprecated${version}.${replacement}` );
	}

	/**
	 * Sets postbox toggle handlers.
	 * TODO move to Settings.js and Post.js respectively?
	 *
	 * TODO also check for hide-postbox-tog... it prevents the user from saving the page.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 No longer causes an infinite loop (call stack size excession).
	 * @access private
	 *
	 * @function
	 */
	const _initPostboxToggle = () => {

		// Get TSF postboxes. Move this inside of the event for the "dynamic web"?
		let $postboxes = $( '.postbox[id^="autodescription-"], .postbox#tsf-inpost-box' );

		/**
		 * HACK: Reopens a box if it contains invalid input values, and notifies the users thereof.
		 * WordPress should implement this in a non-hacky way, so to give us more freedom.
		 *
		 * Alternatively, we could validate the input and reopen the boxes when the user hits "save".
		 * I do prefer the direct feedback though.
		 *
		 * Note that this event might get deprecated!
		 */
		$( document ).on( 'postbox-toggled', ( event, $postbox ) => {
			if ( ! $postbox || ! $postboxes.is( $postbox ) ) return;

			// WordPress bug--they send an array but should've sent it within one.
			// Let's assume they might fix it by converting it to jQuery.
			$postbox = $( $postbox );

			let $input = $postbox.find( 'input:invalid, select:invalid, textarea:invalid' );
			if ( ! $input.length ) return;

			// Defer from event.
			setTimeout( () => {
				if ( $postbox.is( ':hidden' ) ) {
					let id = $postbox.attr( 'id' );
					// Unhide the postbox. Then, loop back to the other parts.
					$( `#${id}-hide` ).trigger( 'click.postboxes' );
				} else {
					if ( $postbox.hasClass( 'closed' ) ) {
						// Reopen self. Loops back to this function.
						$postbox.find( '.hndle, .handlediv' ).first().trigger( 'click.postboxes' );
					} else {
						// Phase 2, this runs after looping back.
						let firstInput = $input.get( 0 );
						if ( $( firstInput ).is( ':visible' ) ) {
							firstInput.reportValidity();
						}
					}
				}
			} );
		} );
	}

	/**
	 * Prepares notice dismissal listeners.
	 *
	 * @since 4.1.2
	 * @access private
	 *
	 * @function
	 */
	const _initNotices = () => {

		/**
		 * Dismissible notices that use notice wrapper class .tsf-notice.
		 *
		 * @since 2.6.0
		 * @since 2.9.3 Now correctly removes the node from DOM.
		 * @since 4.1.0 1. Now is more in line with how WordPress dismisses notices.
		 *              2. Now also handles dismissible persistent notices.
		 * @since 4.1.2 Moved inside other method.
		 *
		 * @function
		 * @param {Event} event
		 */
		const dismissNotice = event => {

			let $notice = $( event.target ).parents( '.tsf-notice' ).first(),
				key     = event.target.dataset && event.target.dataset.key || void 0,
				nonce   = event.target.dataset && event.target.dataset.nonce || void 0;

			$notice.fadeTo( 100, 0, () => {
				$notice.slideUp( 100, () => {
					$notice.remove();
				} );
			} );

			if ( key && nonce ) {
				// The notice is removed regardless of this being completed.
				// Do not inform the user of its completion--it adds a lot to the annoyance.
				// Instead, rely on keeping the 'count' low!
				wp.ajax.post(
					'tsf_dismiss_notice',
					{
						tsf_dismiss_key:   key,
						tsf_dismiss_nonce: nonce,
					}
				);
			}
		}

		const reset = () => {
			// Enable dismissal of PHP-inserted notices.
			document.querySelectorAll( '.tsf-dismiss' ).forEach( el => el.addEventListener( 'click', dismissNotice ) );
		}
		/**
		 * @access private Use triggerNoticeReset() instead.
		 */
		document.body.addEventListener( 'tsf-reset-notice-listeners', reset );
		reset();
	}

	let _dispatchEvents      = new Set(),
		_loadedDispatchEvent = false;
	/**
	 * Offsets callback to interactive event.
	 *
	 * @since 4.2.1
	 * @access public
	 *
	 * @function
	 * @param {Element} element   The element to dispatch the event upon.
	 * @param {string}  eventName The event name to trigger. Mustn't be custom.
	 */
	const dispatchAtInteractive = ( element, eventName ) => {

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
	 *
	 * @function
	 */
	const _loopDispatchAtInteractive = () => {
		_dispatchEvents.forEach( ( [ element, eventName ] ) => {
			element.dispatchEvent( new Event( eventName ) );
		} );
	}

	let _debounceNoticeReset;
	/**
	 * Invokes notice dismissal listener reset.
	 *
	 * @since 4.1.2
	 * @access public
	 *
	 * @function
	 */
	const triggerNoticeReset = () => {
		clearTimeout( _debounceNoticeReset );
		_debounceNoticeReset = setTimeout(
			() => document.body.dispatchEvent( new CustomEvent( 'tsf-reset-notice-listeners' ) ),
			100 // Magic number. Low enough not to cause ignored clicks, high enough not to cause lag.
		);
	}

	let _debounceResize,
		_debounceResizeTrigger,
	    _throttleResize = false;
	const _throttleResizeDebounceDelay = 100;
	/**
	 * Dispatches tsf-resize event on window.
	 *
	 * It fires immediately, after which it's debounced indefinitely until 100ms passed.
	 * Once debounce is passed, another immediate trigger can happen again.
	 *
	 * @since 4.2.0
	 * @access private
	 *
	 * @function
	 */
	const _triggerResize = () => {

		clearTimeout( _debounceResize );
		_debounceResize = setTimeout( () => { _throttleResize = false }, _throttleResizeDebounceDelay );

		if ( _throttleResize ) {
			clearTimeout( _debounceResizeTrigger );
			_debounceResizeTrigger = setTimeout( _triggerResize, _throttleResizeDebounceDelay );
		} else {
			_throttleResize = true;
			dispatchEvent( new CustomEvent( 'tsf-resize' ) );
		}
	}

	let isInteractive = false;
	/**
	 * Dispatches tsf-interactive event.
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
	 *
	 * @function
	 */
	const _triggerInteractive = () => {
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
	 *
	 * @function
	 */
	const _triggerReady = () => {
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
	 *
	 * @function
	 */
	const _triggerOnLoad = () => {
		document.body.dispatchEvent( new CustomEvent( 'tsf-onload' ) );
	}

	let _isReady = false;
	/**
	 * Runs document-on-ready actions.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @function
	 */
	const _doReady = () => {

		if ( _isReady ) return;

		document.removeEventListener( "DOMContentLoaded", _doReady );
		document.removeEventListener( "load", _doReady );

		// Triggers tsf-onload event.
		_triggerOnLoad();

		// Sets postbox toggles on load.
		_initPostboxToggle();

		// Initializes notices
		_initNotices();

		// Trigger tsf-ready event.
		_triggerReady();

		_isReady = true;

		// Trigger tsf-interactive event. 'load' might be too late 'cause images are loading (slow 3G...)
		document.addEventListener( 'load', _triggerInteractive );

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
			if ( document.readyState === "complete" ||
				( document.readyState !== "loading" && ! document.documentElement.doScroll ) ) {
				// Handle it asynchronously to allow scripts the opportunity to delay ready.
				setTimeout( _doReady() );
			} else {
				document.addEventListener( "DOMContentLoaded", _doReady );
				document.addEventListener( "load", _doReady );
			}

			// Trigger tsf-resize event.
			window.addEventListener( 'resize', _triggerResize );
		}
	}, {
		stripTags,
		decodeEntities,
		escapeString,
		ampHTMLtoText,
		sDoubleSpace,
		sSingleLine,
		sTabs,
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
		l10n
	} );
}( jQuery );
window.tsf.load();
