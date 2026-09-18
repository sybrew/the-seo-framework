/**
 * This file holds The SEO Framework plugin's JS code for the Author profile fields.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/autodescription/>
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2026 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds tsfAuthor values in an object to avoid polluting global namespace.
 *
 * @since 5.1.5
 *
 * @constructor
 */
window.tsfAuthor = function () {

	/**
	 * @since 5.1.5
	 * @access private
	 * @type {string}
	 */
	const _fediverseHandleId = 'tsf-user-meta[fediverse_page]',
		  _fediverseUrlId    = 'tsf-user-meta[fediverse_page_url]';

	/**
	 * Normalizes a WebFinger-like handle to `@user@domain`.
	 *
	 * Duplicated from settings.js until sanitizer.js exists.
	 *
	 * @since 5.1.5
	 * @access private
	 *
	 * @param {string} handle A handle such as `user@domain` or `@user@domain`.
	 * @return {string} A sanitized `@user@domain` handle, or an empty string.
	 */
	function _normalizeFediverseHandle( handle ) {

		handle = handle.replace( /^[\s\t@]+|[\s\t@]+$/g, '' );

		const match = /^([^@]+)@([^@]+)$/.exec( handle );

		if ( ! match )
			return '';

		let user = match[1].toLowerCase(),
			host = match[2].toLowerCase();

		try {
			user = decodeURIComponent( user );
			host = decodeURIComponent( host );
		} catch ( error ) {}

		if ( host.startsWith( 'www.' ) && host.split( '.' ).length > 2 )
			host = host.slice( 4 );

		if (
			   user.length > 64
			|| ! host.includes( '.' )
			|| ! /^[a-z0-9._-]+$/.test( user )
			|| ! /^[a-z0-9.-]+$/.test( host )
		) {
			return '';
		}

		return tsf.escapeString( `@${user}@${host}` );
	}

	/**
	 * Extracts a username from a Fediverse profile URL path.
	 *
	 * Duplicated from settings.js until sanitizer.js exists.
	 *
	 * @since 5.1.5
	 * @access private
	 *
	 * @param {string} path A URL path such as `/@user` or `/users/user`.
	 * @return {string} The path username, a `user@other.host` remote path, or empty.
	 */
	function _fediversePathUser( path ) {

		const segments = String( path || '' )
			.replace( /^\/+|\/+$/g, '' )
			.split( '/' );
		let segment;

		switch ( segments[0].toLowerCase() ) {
			case 'users':
			case 'user':
			case 'u':
				segment = segments[1] || '';
				break;

			default:
				if ( ! segments[0].startsWith( '@' ) )
					return '';

				segment = segments[0];
		}

		try {
			segment = decodeURIComponent( segment );
		} catch ( error ) {}

		return segment.replace( /^@+/, '' );
	}

	/**
	 * Converts a Fediverse profile URL or handle to `@user@domain`.
	 *
	 * Duplicated from settings.js until sanitizer.js exists.
	 *
	 * @since 5.1.5
	 * @access private
	 *
	 * @param {string} value A handle, profile URL, or pasted tag.
	 * @return {string} A sanitized `@user@domain` handle, or an empty string.
	 */
	function _sanitizeFediverseHandle( value ) {

		value = String( value || '' ).trim();

		if ( ! value )
			return '';

		const meta = /<meta\b[^>]+?\bcontent=(["'])?([^"'>\s]+)\1?/i.exec( value ),
			  href = /href\s*=\s*(["'])([^"']+)\1/i.exec( value );

		value = meta?.[2] || href?.[2] || value;

		if ( value.toLowerCase().startsWith( 'acct:' ) )
			value = value.slice( 5 );

		if ( value.includes( '/' ) ) {
			let url;

			try {
				url = new URL( value );
			} catch ( error ) {
				try {
					url = new URL( `https://${value}` );
				} catch ( error2 ) {
					return '';
				}
			}

			if ( ! [ 'http:', 'https:' ].includes( url.protocol ) )
				return '';

			const host = url.hostname,
				  user = _fediversePathUser( url.pathname );

			if ( ! host.length || ! user )
				return '';

			if ( user.includes( '@' ) )
				return _normalizeFediverseHandle( user );

			return _normalizeFediverseHandle( `${user}@${host}` );
		}

		return _normalizeFediverseHandle( value );
	}

	/**
	 * Converts a pasted value to an HTTPS Fediverse profile URL.
	 *
	 * Duplicated from settings.js until sanitizer.js exists.
	 *
	 * @since 5.1.5
	 * @access private
	 *
	 * @param {string} value A profile URL or pasted tag.
	 * @return {string} A sanitized HTTPS profile URL, or an empty string.
	 */
	function _sanitizeFediverseProfileUrl( value ) {

		value = String( value || '' ).trim();

		if ( ! value )
			return '';

		const href = /href\s*=\s*(["'])([^"']+)\1/i.exec( value );
		value = href?.[2] || value;

		// A handle is not a profile URL.
		if ( ! value.includes( '/' ) )
			return '';

		let url;

		try {
			url = new URL( value );
		} catch ( error ) {
			try {
				url = new URL( `https://${value}` );
			} catch ( error2 ) {
				return '';
			}
		}

		if ( ! [ 'http:', 'https:' ].includes( url.protocol ) )
			return '';

		url.protocol = 'https:';

		const host = url.hostname.toLowerCase(),
			  user = _fediversePathUser( url.pathname );

		if (
			   ! host.includes( '.' )
			|| ! /^[a-z0-9.-]+$/.test( host )
			|| ! user
		) {
			return '';
		}

		if ( user.includes( '@' ) ) {
			if ( ! _normalizeFediverseHandle( user ) )
				return '';
		} else if (
			   user.length > 64
			|| ! /^[a-z0-9._-]+$/.test( user.toLowerCase() )
		) {
			return '';
		}

		return url.href;
	}

	/**
	 * Converts a pasted Fediverse profile URL or tag to `@user@domain`.
	 *
	 * Also writes a discovered profile URL onto the paired URL field.
	 *
	 * @since 5.1.5
	 * @access private
	 *
	 * @param {Event} event
	 * @param {HTMLInputElement|null} urlInput The paired profile URL input.
	 */
	function _onFediverseHandlePaste( event, urlInput ) {

		const val = event.clipboardData?.getData( 'text' ) || '';

		if ( ! val ) return;

		const handle = _sanitizeFediverseHandle( val );

		if ( ! handle ) return;

		event.stopPropagation();
		event.preventDefault();
		event.target.value = handle;

		const url = _sanitizeFediverseProfileUrl( val );

		if ( url && urlInput )
			urlInput.value = url;
	}

	/**
	 * Writes a pasted Fediverse profile URL, and fills an empty paired handle.
	 *
	 * @since 5.1.5
	 * @access private
	 *
	 * @param {Event} event
	 * @param {HTMLInputElement|null} handleInput The paired handle input.
	 */
	function _onFediverseUrlPaste( event, handleInput ) {

		const val = event.clipboardData?.getData( 'text' ) || '';

		if ( ! val ) return;

		const url = _sanitizeFediverseProfileUrl( val );

		if ( ! url ) return;

		event.stopPropagation();
		event.preventDefault();
		event.target.value = url;

		if ( ! handleInput || handleInput.value.trim() ) return;

		const handle = _sanitizeFediverseHandle( val );

		if ( ! handle ) return;

		handleInput.value = handle;
	}

	/**
	 * Initializes Fediverse profile input listeners.
	 *
	 * @since 5.1.5
	 * @access private
	 */
	function _initFediverseListeners() {

		const handleInput = document.getElementById( _fediverseHandleId ),
			  urlInput    = document.getElementById( _fediverseUrlId );

		if ( ! handleInput ) return;

		handleInput.addEventListener( 'paste', event => {
			_onFediverseHandlePaste( event, urlInput );
		} );

		urlInput?.addEventListener( 'paste', event => {
			_onFediverseUrlPaste( event, handleInput );
		} );
	}

	/**
	 * Initializes settings scripts on TSF-load.
	 *
	 * @since 5.1.5
	 * @access private
	 */
	function _loadSettings() {

		// One is not reliant on the other; this way, if one crashes, the rest still works.
		[
			_initFediverseListeners,
		].forEach( fn => {
			try {
				fn();
			} catch ( error ) {
				console.error( `Error in ${fn.name}:`, error );
			}
		} );
	}

	return Object.assign( {
		/**
		 * Initialises all aspects of the scripts.
		 * You shouldn't call this.
		 *
		 * @since 5.1.5
		 * @access protected
		 *
		 * @function
		 */
		load: () => {
			document.body.addEventListener( 'tsf-onload', _loadSettings );
		},
	} );
}();
window.tsfAuthor.load();
