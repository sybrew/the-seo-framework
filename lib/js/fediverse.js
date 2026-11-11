/**
 * This file holds Fediverse handle and profile URL helpers for The SEO Framework.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link https://wordpress.org/plugins/autodescription/
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
 * Holds tsfFediverse values in an object to avoid polluting global namespace.
 *
 * @since 5.2.0
 *
 * @constructor
 */
window.tsfFediverse = function () {

	/**
	 * Normalizes a WebFinger-like handle to `@user@domain`.
	 *
	 * @since 5.2.0
	 * @access private
	 *
	 * @param {string} handle A handle such as `user@domain` or `@user@domain`.
	 * @return {string} A sanitized `@user@domain` handle, or an empty string.
	 */
	function normalizeFediverseHandle( handle ) {

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
	 * @since 5.2.0
	 * @access private
	 *
	 * @param {string} path A URL path such as `/@user` or `/users/user`.
	 * @return {string} The path username, a `user@other.host` remote path, or empty.
	 */
	function fediversePathUser( path ) {

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
	 * @since 5.2.0
	 * @access public
	 *
	 * @param {string} value A handle, profile URL, or pasted tag.
	 * @return {string} A sanitized `@user@domain` handle, or an empty string.
	 */
	function sanitizeHandle( value ) {

		value = String( value || '' ).trim();

		if ( ! value )
			return '';

		const meta = /<meta\b[^>]+?\bcontent=(["'])?([^"'>\s]+)\1?/i.exec( value ),
			  href = /href\s*=\s*(["'])([^"']+)\1/i.exec( value );

		value = meta?.[2] || href?.[2] || value;

		if ( value.toLowerCase().startsWith( 'acct:' ) )
			value = value.slice( 5 );

		if ( value.includes( '/' ) ) {
			const url = tsfHTTP.parseUrl( value );

			if ( ! url || ! tsfHTTP.isHttp( url ) )
				return '';

			const host = url.hostname,
				  user = fediversePathUser( url.pathname );

			if ( ! host.length || ! user )
				return '';

			if ( user.includes( '@' ) )
				return normalizeFediverseHandle( user );

			return normalizeFediverseHandle( `${user}@${host}` );
		}

		return normalizeFediverseHandle( value );
	}

	/**
	 * Converts a pasted value to an HTTPS Fediverse profile URL.
	 *
	 * @since 5.2.0
	 * @access public
	 *
	 * @param {string} value A profile URL or pasted tag.
	 * @return {string} A sanitized HTTPS profile URL, or an empty string.
	 */
	function sanitizeProfileUrl( value ) {

		value = String( value || '' ).trim();

		if ( ! value )
			return '';

		const href = /href\s*=\s*(["'])([^"']+)\1/i.exec( value );
		value = href?.[2] || value;

		// A handle is not a profile URL.
		if ( ! value.includes( '/' ) )
			return '';

		const url = tsfHTTP.parseUrl( value );

		if ( ! url || ! tsfHTTP.isHttp( url ) )
			return '';

		url.protocol = 'https:';

		const host = url.hostname.toLowerCase(),
			  user = fediversePathUser( url.pathname );

		if (
			   ! host.includes( '.' )
			|| ! /^[a-z0-9.-]+$/.test( host )
			|| ! user
		) {
			return '';
		}

		if ( user.includes( '@' ) ) {
			if ( ! normalizeFediverseHandle( user ) )
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
	 * Returns the profile URL built from a `@user@domain` handle.
	 *
	 * @since 5.2.0
	 * @access public
	 *
	 * @param {string} handle A handle, or a value that sanitizes to one.
	 * @return {string} The profile URL, or an empty string.
	 */
	function urlFromHandle( handle ) {

		const match = /^@([^@]+)@([^@]+)$/.exec( sanitizeHandle( handle ) );

		return match ? `https://${match[2]}/@${match[1]}` : '';
	}

	/**
	 * Converts a pasted Fediverse profile URL or tag to `@user@domain`.
	 *
	 * Also writes a discovered profile URL onto the paired URL field.
	 *
	 * @since 5.2.0
	 * @access private
	 *
	 * @param {Event}             event
	 * @param {?HTMLInputElement} urlInput The paired profile URL input.
	 */
	function onFediverseHandlePaste( event, urlInput ) {

		const val = event.clipboardData?.getData( 'text' ) || '';

		if ( ! val ) return;

		const handle = sanitizeHandle( val );

		if ( ! handle ) return;

		event.stopPropagation();
		event.preventDefault();
		event.target.value = handle;

		'tsfAys' in window && tsfAys.registerChange();

		event.target.dispatchEvent( new Event( 'input' ) );

		const url = sanitizeProfileUrl( val );

		if ( url && urlInput ) {
			urlInput.value = url;
			urlInput.dispatchEvent( new Event( 'input' ) );
		}
	}

	/**
	 * Writes a pasted Fediverse profile URL, and fills an empty paired handle.
	 *
	 * @since 5.2.0
	 * @access private
	 *
	 * @param {Event}             event
	 * @param {?HTMLInputElement} handleInput The paired handle input.
	 */
	function onFediverseUrlPaste( event, handleInput ) {

		const val = event.clipboardData?.getData( 'text' ) || '';

		if ( ! val ) return;

		const url = sanitizeProfileUrl( val );

		if ( ! url ) return;

		event.stopPropagation();
		event.preventDefault();
		event.target.value = url;

		'tsfAys' in window && tsfAys.registerChange();

		event.target.dispatchEvent( new Event( 'input' ) );

		if ( ! handleInput || handleInput.value.trim() ) return;

		const handle = sanitizeHandle( val );

		if ( ! handle ) return;

		handleInput.value = handle;
		handleInput.dispatchEvent( new Event( 'input' ) );
	}

	/**
	 * Binds handle and profile URL paste listeners as a pair.
	 *
	 * @since 5.2.0
	 * @access public
	 *
	 * @param {?HTMLInputElement} handleInput
	 * @param {?HTMLInputElement} urlInput
	 */
	function bindPair( handleInput, urlInput ) {

		if ( ! handleInput ) return;

		handleInput.addEventListener(
			'paste',
			event => {
				onFediverseHandlePaste( event, urlInput );
			},
		);

		urlInput?.addEventListener(
			'paste',
			event => {
				onFediverseUrlPaste( event, handleInput );
			},
		);
	}

	return {
		sanitizeHandle,
		sanitizeProfileUrl,
		urlFromHandle,
		bindPair,
	};
}();
