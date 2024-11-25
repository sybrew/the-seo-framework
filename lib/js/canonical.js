/**
 * This file holds The SEO Framework plugin's JS code for TSF canonical URL fields.
 * We dub this Canonical URL Notation Tracker - The Reliable Accurate Predictor.
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
 * Holds tsfCanonical values in an object to avoid polluting global namespace.
 *
 * This is a self-constructed function assigned as an object.
 *
 * @since 5.1.0
 *
 * @constructor
 */
window.tsfCanonical = function () {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 5.1.0
	 * @access public
	 * @type {(Object<string,*>)|boolean|null} l10n Localized strings.
	 */
	const l10n = tsfCanonicalL10n;

	/**
	 * @since 5.1.0
	 * @access public
	 * @type {Boolean}
	 */
	const usingPermalinks = l10n.params.usingPermalinks;
	/**
	 * @since 5.1.0
	 * @access public
	 * @type {String}
	 */
	const rootUrl = tsf.stripTags( l10n.params.rootUrl );
	/**
	 * @since 5.1.0
	 * @access public
	 * @type {{code:String[],replace:String[],queryReplace:String[]}}
	 */
	const rewrite = l10n.params.rewrite;
	/**
	 * @since 5.1.0
	 * @access private
	 * @type {Boolean}
	 */
	const allowCanonicalURLNotationTool = l10n.params.allowCanonicalURLNotationTool;

	/**
	 * @since 5.1.0
	 * @access private
	 * @type {Map<string,Element>} The input element instances.
	 */
	const canonicalInputInstances = new Map();

	/**
	 * @since 5.1.0
	 * @access private
	 * @type {(Object<string,Object<string,*>)} the query state.
	 */
	const states = {};

	/**
	 * Sets input element for all listeners. Must be called prior interacting with this object.
	 * Resets the state for the input ID.
	 *
	 * @since 5.1.0
	 * @access public
	 *
	 * @param {Element} element
	 * @return {Element}
	 */
	function setInputElement( element ) {
		canonicalInputInstances.set( element.id, element );
		states[ element.id ] = {
			allowReferenceChange: true,
			defaultCanonical:     '',
			showUrlPlaceholder:   true,
			preferredScheme:      '',
			urlStructure:         '',
			urlDataParts:         {},
		};
		_loadCanonicalActions( element );
		return getInputElement( element.id );
	}

	/**
	 * Gets input element, if exists.
	 *
	 * @since 5.1.0
	 * @access public
	 *
	 * @param {string} id The element ID.
	 * @return {Element}
	 */
	function getInputElement( id ) {
		return canonicalInputInstances.get( id );
	}

	/**
	 * Returns state of ID.
	 *
	 * @since 5.1.0
	 * @access public
	 *
	 * @param {string}             id The input element ID.
	 * @param {(string|undefined)} part The part to return. Leave empty to return the whole state.
	 * @return {(Object<string,*>)|*|null}
	 */
	function getStateOf( id, part ) {
		return part ? states[ id ]?.[ part ] : states[ id ];
	}

	/**
	 * Updates state of ID.
	 *
	 * There's no need to escape the input, it may be double-escaped if you do so.
	 *
	 * @since 5.1.0
	 * @access public
	 *
	 * @param {string} id The input element ID.
	 * @param {string} part  The state index to change.
	 * @param {*}      value The value to set the state to.
	 */
	function updateStateOf( id, part, value ) {

		if ( states[ id ][ part ] === value ) return;

		states[ id ][ part ] = value;

		switch ( part ) {
			case 'allowReferenceChange':
			case 'showUrlPlaceholder':
			case 'preferredScheme':
			case 'urlStructure':
			case 'urlDataParts':
			default:
				enqueueTriggerInput( id );
				break;
		}
	}

	/**
	 * Updates state of all elements.
	 *
	 * There's no need to escape the input, it may be double-escaped if you do so.
	 *
	 * @since 5.1.0
	 * @access public
	 *
	 * @param {string}          part   The state index to change.
	 * @param {*}               value  The value to set the state to.
	 * @param {string|string[]} except The input element IDs to exclude from updates.
	 */
	function updateStateAll( part, value, except ) {

		except = Array.isArray( except ) ? except : [ except ];

		canonicalInputInstances.forEach( element => {
			if ( except.includes( element.id ) ) return;
			updateStateOf( element.id, part, value );
		} );
	}

	/**
	 * Sanitizes the slug and encodes it to iso-8859-1.
	 *
	 * This function is based on WP Core's `remove_accents()` and `sanitize_title_with_dashes()`,
	 * which are used to sanitize slugs.
	 *
	 * @since 5.1.0
	 * @access public
	 *
	 * @param {String} slug The slug to sanitize.
	 * @returns {String} The sanitized slug.
	 */
	function sanitizeSlug( slug ) {

		// If it's a number, it's fine. If it's neither, it'll crash, which is also fine.
		if ( 'string' !== typeof slug || ! slug.length ) return slug;

		// Define character mappings
		const chars = {
			in:  "\x80\x83\x8a\x8e\x9a\x9e\x9f\xa2\xa5\xb5\xc0\xc1\xc2\xc3\xc4\xc5\xc7\xc8\xc9\xca\xcb\xcc\xcd\xce\xcf\xd1\xd2\xd3\xd4\xd5\xd6\xd8\xd9\xda\xdb\xdc\xdd\xe0\xe1\xe2\xe3\xe4\xe5\xe7\xe8\xe9\xea\xeb\xec\xed\xee\xef\xf1\xf2\xf3\xf4\xf5\xf6\xf8\xf9\xfa\xfb\xfc\xfd\xff",
			out: 'EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy',
		};

		// Translate characters
		slug = slug.replace(
			/./g,
			char => {
				const index = chars.in.indexOf( char );
				return -1 !== index ? chars.out[ index ] : char;
			},
		);

		// Define double character mappings
		const doubleChars = {
			in:  [ "\x8c", "\x9c", "\xc6", "\xd0", "\xde", "\xdf", "\xe6", "\xf0", "\xfe" ],
			out: [ 'OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th' ],
		};

		// Replace double characters
		doubleChars.in.forEach( ( char, index ) => {
			slug = slug.replace(
				new RegExp( char, 'g' ),
				doubleChars.out[ index ],
			);
		} );

		slug = slug.replace( /<\/?[^>]+(>|$)/g, '' )             // Strip HTML tags
			.replace( /%([a-fA-F0-9][a-fA-F0-9])/g, '---$1---' ) // Preserve escaped octets
			.replace( /%|"/g, '' )                               // Remove percent signs not part of an octet, also remove quotes.
			.replace( /---([a-fA-F0-9][a-fA-F0-9])---/g, '%$1' ) // Restore octets
			.replace( /\s+/g, '-' )                              // Convert spaces to hyphens
			.toLowerCase();                                      // Convert to lowercase.

		return encodeURIComponent( slug )
			.replace( /%c2%a0|%e2%80%93|%e2%80%94|&nbsp;|&#160;|&ndash;|&#8211;|&mdash;|&#8212;|\//g, '-' ) // Convert nbsp, ndash, mdash to hyphens.
			.replace( /%c3%97/g, 'x' )        // Convert &times to 'x'
			.replace( /%c2%ad|%c2%a1|%c2%bf|%c2%ab|%c2%bb|%e2%80%b9|%e2%80%ba|%e2%80%98|%e2%80%99|%e2%80%9c|%e2%80%9d|%e2%80%9a|%e2%80%9b|%e2%80%9e|%e2%80%9f|%e2%80%a2|%c2%a9|%c2%ae|%c2%b0|%e2%80%a6|%e2%84%a2|%c2%b4|%cb%8a|%cc%81|%cd%81|%cc%80|%cc%84|%cc%8c|%e2%80%8b|%e2%80%8c|%e2%80%8d|%e2%80%8e|%e2%80%8f|%e2%80%aa|%e2%80%ab|%e2%80%ac|%e2%80%ad|%e2%80%ae|%ef%bb%bf|%ef%bf%bc/g, '' ) // Remove specific characters
			.replace( /%e2%80%80|%e2%80%81|%e2%80%82|%e2%80%83|%e2%80%84|%e2%80%85|%e2%80%86|%e2%80%87|%e2%80%88|%e2%80%89|%e2%80%8a|%e2%80%a8|%e2%80%a9|%e2%80%af/g, '-' )      // Convert non-visible characters to hyphen
			.replace( /&.+?;/g, '' )          // Remove HTML entities
			.replace( /\./g, '-' )            // Replace dots with hyphens
			.replace( /[^%a-z0-9 _-]+/g, '' ) // Remove non-alphanumeric characters
			.replace( /-+/g, '-' )            // Replace multiple hyphens with a single hyphen
			.replace( /^-+|-+$/g, '' );       // Trim hyphens
	}

	/**
	 * Tests whether the rewrite structure includes a rewrite part.
	 * When given an array, any match will return true.
	 *
	 * @since 5.1.0
	 * @access public
	 *
	 * @param {string}          id   The input element ID.
	 * @param {string|string[]} code The rewrite code to test.
	 * @return {boolean}
	 */
	function structIncludes( id, code ) {

		const urlStructure = getStateOf( id, 'urlStructure' );

		if ( Array.isArray( code ) )
			return code.some( c => urlStructure.includes( c ) );

		return urlStructure.includes( code );
	}

	/**
	 * Updates the title placeholder.
	 *
	 * @since 5.1.0
	 * @access private
	 *
	 * @param {Event} event
	 */
	function _updatePlaceholder( event ) {

		let placeholder = '',
			id          = event.target.id;

		if ( getStateOf( id, 'showUrlPlaceholder' ) ) {
			// We had plans for dynamic non-'usingPermalinks', but (aside from tags), it's often static and predetermined on load.
			// Removing the usingPermalinks check will break plain permalink URLs for terms, for we haven't implemented an ID fill.
			if ( allowCanonicalURLNotationTool && usingPermalinks && getStateOf( id, 'allowReferenceChange' ) ) {
				const urlStructure    = getStateOf( id, 'urlStructure' ),
					  urlDataParts    = getStateOf( id, 'urlDataParts' ),
					  preferredScheme = getStateOf( id, 'preferredScheme' ),
					  queryReplace    = usingPermalinks ? [] : rewrite.queryReplace;

				let struct = urlStructure;

				rewrite.code.forEach( ( code, index ) => {
					// Skip unregistered structs.
					if ( ! struct.includes( code ) ) return;

					let replacement = null;

					if ( code in urlDataParts ) {
						// WordPress's rewrite rules account for edge-cases, vestigial code, and other oddities.
						// For example, we'd need to turn '%postname%' from '[^/]+' to '.+' to make it work as expected.
						// There are probably a lot more edge-cases we need to account for.
						// So, we disable this for now and assume the given content is good for URLs. URL.parse() below fixes most issues.
						// replacement = `${urlDataParts[ code ]}`.match(
						// 	new RegExp(
						// 		// Remove the '?' wildcard. The wildcard makes <https://theseoframework.com/non-existing/5-0/> work.
						// 		// But we need to create a fully qualified canonical URL here, not a random one.
						// 		rewrite.replace[ index ]
						// 			.replace( /(\+|\*)\?/g, '$1' ),
						// 	),
						// )?.[0] ?? '';

						replacement = urlDataParts[ code ];
					}

					if ( null !== replacement ) {
						struct = struct.replace(
							code,
							( queryReplace[ index ] ?? '' ) + replacement,
						);
					} else {
						struct = struct.replace( code, '' );
					}
				} );

				// Remove double slashes, and replace spaces with dashes.
				struct = struct
					.replace( /\/{2,}/g, '/' )
					.replace( /\s+/g, '-' );

				const placeholderUrl = URL.parse( struct, rootUrl );
				placeholderUrl.protocol = `${preferredScheme}:`;

				placeholder = placeholderUrl.href;
			} else {
				placeholder = getStateOf( id, 'defaultCanonical' );
			}
		}

		event.target.placeholder = placeholder;
	}

	/**
	 * Triggers canonical URL input.
	 *
	 * @since 5.1.0
	 * @access public
	 *
	 * @param {string} id The input id. When not set, all inputs will be triggered.
	 */
	function triggerInput( id ) {
		if ( id ) {
			getInputElement( id )?.dispatchEvent( new Event( 'input' ) );
		} else {
			// We don't want it to loop infinitely. Check element.id value first.
			canonicalInputInstances.forEach( element => element.id && triggerInput( element.id ) );
		}
	}

	/**
	 * Updates reference and placeholders.
	 *
	 * @since 5.1.0
	 * @access private
	 * @see triggerInput
	 *
	 * @param {Event} event
	 */
	function _onUpdateCanonicalUrlsTrigger( event ) {
		_updatePlaceholder( event );
	}

	let _enqueueTriggerInputBuffer = {};
	/**
	 * Triggers canonical URL input.
	 *
	 * @since 5.1.0
	 * @access public
	 *
	 * @param {string} id The input ID.
	 */
	function enqueueTriggerInput( id ) {
		( id in _enqueueTriggerInputBuffer ) && clearTimeout( _enqueueTriggerInputBuffer[ id ] );
		_enqueueTriggerInputBuffer[ id ] = setTimeout( () => triggerInput( id ), 1000/60 ); // 60fps
	}

	/**
	 * Triggers canonical URL update, without affecting tsfAys change listeners.
	 *
	 * @since 5.1.0
	 * @access public
	 *
	 * @param {Event}
	 * @param {string} id The input id. When not set, all inputs will be triggered.
	 */
	function triggerUnregisteredInput( id ) {
		if ( 'tsfAys' in window ) {
			let wereSettingsChanged = tsfAys.areSettingsChanged();

			triggerInput( id );

			// Only reset if we polluted the change listener, and only if a change wasn't already registered.
			if ( ! wereSettingsChanged && tsfAys.areSettingsChanged() )
				tsfAys.reset();
		} else {
			triggerInput( id );
		}
	}

	let _unregisteredTriggerBuffer = {};
	/**
	 * Enqueues unregistered canonical URL input triggers.
	 *
	 * @since 5.1.0
	 * @access public
	 *
	 * @param {string} id The input id. When not set, all inputs will be triggered.
	 */
	function enqueueTriggerUnregisteredInput( id ) {
		( id in _unregisteredTriggerBuffer ) && clearTimeout( _unregisteredTriggerBuffer[ id ] );
		_unregisteredTriggerBuffer[ id ] = setTimeout( () => triggerUnregisteredInput( id ), 1000/60 ); // 60 fps
	}

	/**
	 * Reinitializes the canonical URL input action callbacks.
	 *
	 * @since 5.1.0
	 * @access private
	 *
	 * @param {Element} canonicalInput
	 */
	function _loadCanonicalActions( canonicalInput ) {

		if ( ! canonicalInput instanceof Element ) return;

		canonicalInput.addEventListener( 'input', _onUpdateCanonicalUrlsTrigger );

		enqueueTriggerUnregisteredInput( canonicalInput.id );
	}

	return Object.assign( {
		setInputElement,
		getInputElement,
		getStateOf,
		updateStateOf,
		updateStateAll,
		structIncludes,
		sanitizeSlug,
		triggerInput,
		enqueueTriggerInput,
		triggerUnregisteredInput,
		enqueueTriggerUnregisteredInput,
	}, {
		l10n,
		usingPermalinks,
		rootUrl,
		rewrite,
	} );
}();
