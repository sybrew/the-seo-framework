/**
 * This file holds social profile URL format hints for The SEO Framework.
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
 * Holds tsfSocialFormat values in an object to avoid polluting global namespace.
 *
 * @since 5.2.0
 *
 * @constructor
 */
window.tsfSocialFormat = function () {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 5.2.0
	 * @access public
	 * @type {(Object<string,*>)|Boolean|null} l10n Localized strings
	 */
	const l10n = tsfSocialFormatL10n;

	/**
	 * Fills the template whose path marker is in the URL, otherwise the first template.
	 *
	 * @since 5.2.0
	 * @access private
	 *
	 * @param {URL}      url
	 * @param {string[]} templates
	 * @return {string} The filled template, or an empty string.
	 */
	function filledTemplate( url, templates ) {

		const parts = url.pathname
			.split( '/' )
			.filter( Boolean )
			.map( part => {
				try {
					return decodeURIComponent( part );
				} catch ( error ) {
					return part;
				}
			} );

		for ( const template of templates ) {
			const found = template.match( /\/([^/@]+)\/\{username\}/ );
			let marker  = '';

			if ( found ) {
				marker = found[1];
			} else if ( template.includes( '@{username}' ) ) {
				marker = '@';
			}

			if ( ! marker || '@' === marker ) continue;

			const index = parts.findIndex( part => marker.toLowerCase() === part.toLowerCase() );

			if ( -1 === index || ! parts[ index + 1 ] ) continue;

			return template.replace(
				'{username}',
				parts[ index + 1 ].replace( /^@/, '' ),
			);
		}

		const at = parts.find( part => part.startsWith( '@' ) );

		if ( at?.slice( 1 ) ) {
			const template = templates.find( item => item.includes( '@{username}' ) ) || templates[0];

			return template.replace( '{username}', at.slice( 1 ) );
		}

		const last = ( parts[ parts.length - 1 ] || '' ).replace( /^@/, '' );

		if ( ! last )
			return '';

		return templates[0].replace( '{username}', last );
	}

	/**
	 * Reads an X handle from a handle or profile URL.
	 *
	 * @since 5.2.0
	 * @access private
	 *
	 * @param {string} raw
	 * @return {string} `@username`, or an empty string.
	 */
	function xHandle( raw ) {

		const path = raw.trim()
			.replace(
				/^(?:\w+:\/*|\/\/|(?=[^@]*[.\/]))[^\/#?]*/,
				'',
			)
			.replace( /^[\s/@]+|[\s/@]+$/g, '' )
			.replace( /[^a-z\d_]/gi, '' );

		if ( path.length < 1 || path.length > 18 )
			return '';

		return `@${path}`;
	}

	/**
	 * Shows a suggested profile value when the field does not match.
	 *
	 * @since 5.2.0
	 * @access public
	 *
	 * @param {HTMLInputElement} input
	 */
	function bind( input ) {

		const suggestedText = l10n?.i18n?.socialFormatSuggested,
			  exampleText   = l10n?.i18n?.socialFormatExample;

		let templates = [];
		let pattern;

		try {
			templates = JSON.parse( input.dataset.socialFormats || '[]' );

			if ( input.dataset.socialPattern )
				pattern = new RegExp( input.dataset.socialPattern, 'i' );
		} catch ( error ) {
			templates = [];
		}

		const example = input.dataset.socialExample || '',
			  kind    = input.dataset.socialKind || '';

		if ( templates.length ) {
			if ( ! suggestedText || ! pattern ) return;
		} else if ( example ) {
			if ( ! exampleText ) return;
		} else {
			return;
		}

		const hint  = input.closest( '.tsf-social-format-field' )?.querySelector( '.tsf-social-format-hint' ),
			  label = hint?.querySelector( '.tsf-social-format-label' ),
			  apply = hint?.querySelector( '.tsf-social-format-apply' );

		if ( ! hint || ! label || ! apply ) return;

		/**
		 * Writes the hint label and, when a correction exists, the button.
		 *
		 * @since 5.2.0
		 * @access private
		 *
		 * @param {string} hintText
		 * @param {string} suggestion
		 * @param {string} correction
		 */
		function writeHint( hintText, suggestion, correction ) {

			const parts = hintText.split( '%s' ),
				  code  = document.createElement( 'code' );

			code.textContent = suggestion;
			label.replaceChildren();

			if ( parts[0] )
				label.append( parts[0] );

			label.append( code );

			if ( parts[1] )
				label.append( parts[1] );

			hint.classList.remove( 'hidden' );

			if ( ! correction ) return;

			apply.dataset.suggestion = correction;
			apply.classList.remove( 'hidden' );
		}

		/**
		 * Returns a corrected handle or profile URL.
		 *
		 * @since 5.2.0
		 * @access private
		 *
		 * @param {string} raw
		 * @return {string}
		 */
		function correctionFor( raw ) {
			switch ( kind ) {
				case 'x':
					return xHandle( raw );

				case 'fediverse-handle':
					return 'tsfFediverse' in window ? tsfFediverse.sanitizeHandle( raw ) : '';

				case 'fediverse-url':
					return 'tsfFediverse' in window ? tsfFediverse.sanitizeProfileUrl( raw ) : '';

				default:
					return '';
			}
		}

		/**
		 * Rewrites the hint for the current field value.
		 *
		 * @since 5.2.0
		 * @access private
		 */
		function refresh() {

			const raw = input.value.trim();

			hint.classList.add( 'hidden' );
			apply.classList.add( 'hidden' );
			delete apply.dataset.suggestion;

			if ( ! raw ) return;

			if ( ! templates.length ) {
				const fixed = correctionFor( raw );

				if ( fixed === raw ) return;

				if ( fixed ) {
					writeHint( suggestedText || exampleText, fixed, fixed );
				} else {
					writeHint( exampleText, example, '' );
				}

				return;
			}

			const url = tsfHTTP.parseUrl( raw );

			if (
				   url
				&& tsfHTTP.isHttp( url )
				&& ! url.search
				&& ! url.hash
				&& pattern.test( `${url.protocol}//${url.host}${url.pathname}` )
			) {
				return;
			}

			const filled     = url ? filledTemplate( url, templates ) : '',
				  suggestion = filled || templates[0];

			writeHint( suggestedText, suggestion, filled );
		}

		/**
		 * Writes the suggestion into the field.
		 *
		 * @since 5.2.0
		 * @access private
		 */
		function applySuggestion() {

			if ( ! apply.dataset.suggestion ) return;

			input.value = apply.dataset.suggestion;
			'tsfAys' in window && tsfAys.registerChange();
			refresh();
		}
		apply.addEventListener( 'click', applySuggestion );

		input.addEventListener( 'input', refresh );
		refresh();
	}

	return {
		bind,
	};
}();
