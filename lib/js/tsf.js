/**
 * This file holds The SEO Framework plugin's JS code.
 * Serve JavaScript as an addition, not as a means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link https://wordpress.org/plugins/autodescription/
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2018 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

// ==ClosureCompiler==
// @compilation_level ADVANCED_OPTIMIZATIONS
// @output_file_name tsf.min.js
// @externs_url https://raw.githubusercontent.com/google/closure-compiler/master/contrib/externs/jquery-1.9.js
// @externs_url https://raw.githubusercontent.com/sybrew/the-seo-framework/master/lib/js/externs/tsf.externs.js
// ==/ClosureCompiler==
// http://closure-compiler.appspot.com/home

'use strict';

/**
 * Holds The SEO Framework values in an object to avoid polluting global namespace.
 *
 * @since 2.2.4
 * @todo spread methods accross files/classes through protoype?
 *
 * @constructor
 */
window.tsf = {

	/**
	 * AJAX Nonces object.
	 *
	 * @since 2.9.0
	 *
	 * @type {Object<string, string>} nonces The AJAX nonces
	 */
	nonces : tsfL10n.nonces,

	/**
	 * i18n object.
	 *
	 * @since 2.8.0
	 *
	 * @const
	 * @type {Object<string, string>} i18n Localized strings
	 */
	i18n : tsfL10n.i18n,

	/**
	 * Page states object.
	 *
	 * @since 2.8.0
	 *
	 * @const
	 * @type {Object<string, *>} states Localized states
	 */
	states : tsfL10n.states,

	/**
	 * Option parameters object.
	 *
	 * @since 2.8.0
	 *
	 * @const
	 * @type {Object<string, *>} params Localized parameters
	 */
	params : tsfL10n.params,

	/**
	 * Determines if the settings have been changed since visit.
	 *
	 * @since 2.2.0
	 *
	 * @typedef {(Boolean|null|undefined)} settingsChanged
	 */
	settingsChanged: false,

	/**
	 * Mixed string and int (i10n is string, JS is int).
	 *
	 * @since 2.6.0
	 *
	 * @type {(String|number)} countertype The counterType
	 */
	counterType : 0,

	/**
	 * Determines if the current page has input boxes for The SEO Framework.
	 *
	 * @since 2.7.0
	 *
	 * @typedef {(Boolean|null|undefined)} hasInput
	 */
	hasInput : false,

	/**
	 * The current character counter additions class.
	 *
	 * @since 2.6.0
	 *
	 * @type {(string|null)} additionsClass
	 */
	additionsClass : '',

	/**
	 * Helper function for confirming a user action.
	 *
	 * @since 2.2.4
	 *
	 * @function
	 * @param {String} text The text to display.
	 * @return {(Boolean|null)} True on OK, false on cancel.
	 */
	confirm: function( text ) {
		return confirm( text );
	},

	/**
	 * Escapes input string.
	 *
	 * @since 3.0.1
	 *
	 * @source <https://stackoverflow.com/a/4835406>
	 * @function
	 * @param {string} str
	 * @return {string}
	 */
	escapeString: function( str ) {

		if ( ! str.length )
			return '';

		let map = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&#039;'
		};

		return str.replace( /[&<>"']/g, m => map[ m ] );
	},

	/**
	 * Undoes what tsf.escapeString has done.
	 *
	 * @since 3.0.4
	 *
	 * @function
	 * @param {string} str The escaped str via tsf.escapeString
	 * @return {string}
	 */
	unescapeString: function( str ) {

		if ( ! str.length )
			return '';

		let map = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&#039;'
		};

		//= IE11 replacement for Object.values. <https://stackoverflow.com/a/42830295>
		let values = Object.keys( map ).map( e => map[ e ] );

		let regex = new RegExp(
			values.map(
				v => v.replace( /[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, '\\$&' )
			).join( '|' ),
			'g'
		);

		return str.replace( regex, m => {
			return Object.keys( map ).find( k => {
				return map[ k ] === m;
			} );
		} );
	},

	/**
	 * Gets string length.
	 * We do not trim whitespace in JavaScript; that should be self-taught by the user.
	 *
	 * @since 3.0.0
	 *
	 * @function
	 * @param {string} str
	 * @return {number}
	 */
	getStringLength: function( str ) {
		let e,
			length = 0;

		if ( str.length ) {
			e = document.createElement( 'span' );
			e.innerHTML = tsf.escapeString( str );
			length = e.childNodes[0].nodeValue.length;
		}
		return +length;
	},

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
	convertJSONResponse: function( response ) {

		let testJSON = response && response.json || void 0,
			isJSON = 1 === testJSON;

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
	},

	/**
	 * Updates pixel counter.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @function
	 * @param {Object} test
	 * @return {undefined}
	 */
	updatePixelCounter: function( test ) {

		let el = test.e,
			text = test.text,
			guideline = test.guideline;

		let wrap = el.parentElement;

		if ( ! wrap )
			return;

		let bar = wrap.querySelector( '.tsf-pixel-counter-bar' ),
			shadow = wrap.querySelector( '.tsf-pixel-counter-shadow' );

		if ( ! bar || ! shadow )
			return;

		shadow.innerHTML = tsf.escapeString( text );

		let testWidth = shadow.offsetWidth,
			newClass = '',
			newWidth = '';

		let fitClass = 'tsf-pixel-counter-fit',
			overflownClass = 'tsf-pixel-counter-overflown';

		if ( testWidth > guideline ) {
			//= Can never be 0. Good. Add 2/3rds of difference to it; implying emphasis.
			newWidth = ( guideline / ( testWidth + ( ( testWidth - guideline ) * 2 / 3 ) ) * 100 ) + '%';
			newClass = overflownClass;
		} else {
			//= Can never be over 100. Good.
			newWidth = ( testWidth / guideline * 100 ) + '%';
			newClass = fitClass;
		}

		let sub = bar.querySelector( '.tsf-pixel-counter-fluid' ),
			label;

		label = tsf.i18n.pixelsUsed.replace( /%1\$d/g, testWidth );
		label = label.replace( /%2\$d/g, guideline );

		bar.classList.remove( fitClass, overflownClass );
		bar.classList.add( newClass );
		bar.dataset.desc = label;
		bar.setAttribute( 'aria-label', label );

		sub.style.width = newWidth;

		tsfTT.triggerUpdate( bar );
	},

	/**
	 * Initializes all aspects for title editing.
	 * Assumes only one title editor is present in the DOM.
	 *
	 * @since 3.0.0
	 *
	 * @function
	 * @return {undefined}
	 */
	_initTitleInputs: function() {

		if ( ! tsf.hasInput )
			return;

		let $doctitles = jQuery( "#autodescription_title, #autodescription-meta\\[doctitle\\], #autodescription-site-settings\\[homepage_title\\]" );

		if ( ! $doctitles.length )
			return;

		//= y u no fix dis, Microsoft. Crappy vars don't deserve CamelCase.
		let ie11killswitch = false,
			ie11 = !! navigator.userAgent.match(/Trident\/7\./);

		let hoverPrefixPlacement,
			hoverAdditionsPlacement,
			hoverPrefixElement,
			hoverPrefixValue = '',
			hoverAdditionsElement,
			hoverAdditionsValue = '',
			separator = tsf.params.titleSeparator,
			defaultTitle = tsf.params.defaultTitle;

		let useTagline = tsf.states.useTagline,
			isRTL = tsf.states.isRTL,
			isPrivate = tsf.states.isPrivate,
			isPasswordProtected = tsf.states.isPasswordProtected;

		//= Sets hoverPrefixPlacement.
		hoverPrefixPlacement = isRTL ? 'after' : 'before';
		//= Sets hoverAdditionsPlacement.
		const setHoverAdditionsPlacement = function() {
			let placement = 'before';

			if ( tsf.states.isSettingsPage ) {
				if ( isRTL ) {
					if ( 'right' === jQuery( '#tsf-home-title-location input:checked' ).val() ) {
						placement = 'after';
					}
				} else {
					if ( 'left' === jQuery( '#tsf-home-title-location input:checked' ).val() ) {
						placement = 'after';
					}
				}
			} else {
				if ( tsf.states.isHome ) {
					//= Static front page.
					if ( isRTL ) {
						if ( 'right' === tsf.params.titleLocation )
							placement = 'after';
					} else if ( 'left' === tsf.params.titleLocation ) {
						placement = 'after';
					}
				} else {
					if ( isRTL ) {
						if ( 'left' === tsf.params.titleLocation )
							placement = 'after';
					} else if ( 'right' === tsf.params.titleLocation ) {
						placement = 'after';
					}
				}
			}

			hoverAdditionsPlacement = placement;
		}
		setHoverAdditionsPlacement();
		//= Sets hoverAdditionsValue.
		const setHoverAdditionsValue = function() {
			let newValue = '';

			if ( tsf.states.isSettingsPage ) {
				if ( useTagline ) {
					//= Tagline is enabled.
					let e = document.getElementById( 'autodescription-site-settings[homepage_title_tagline]' ),
						customTagline = e ? e.value : '';

					if ( customTagline.length ) {
						newValue = customTagline;
					} else {
						newValue = tsf.params.blogDescription;
					}
				}
			} else if ( tsf.states.isHome ) {
				//= Static front page.
				if ( useTagline )
					newValue = tsf.params.titleAdditions;
			} else {
				//= Global additions are enabled.
				if ( useTagline )
					newValue = tsf.params.titleAdditions;
			}

			if ( newValue.length ) {
				newValue = tsf.escapeString( newValue );
				switch ( hoverAdditionsPlacement ) {
					case 'before' :
						newValue = newValue + ' ' + separator + ' ';
						break;

					case 'after' :
						newValue = ' ' + separator + ' ' + newValue;
						break;
				}
			}
			hoverAdditionsValue = newValue.length ? newValue : '';

			hoverAdditionsElement = document.getElementById( 'tsf-title-placeholder' );
			if ( hoverAdditionsValue.length && hoverAdditionsElement ) {
				hoverAdditionsElement.innerHTML = hoverAdditionsValue;
			}
		}
		setHoverAdditionsValue();
		//= Sets hoverPrefixValue.
		const setHoverPrefixValue = function() {
			let newValue = '';

			if ( isPrivate ) {
				newValue = tsf.i18n.privateTitle;
			} else if ( isPasswordProtected ) {
				newValue = tsf.i18n.protectedTitle;
			}

			if ( newValue.length ) {
				newValue = tsf.escapeString( newValue );
				switch ( hoverPrefixPlacement ) {
					case 'before' :
						newValue = newValue + ' ';
						break;

					case 'after' :
						newValue = ' ' + newValue;
						break;
				}
			}
			hoverPrefixValue = newValue.length ? newValue : '';

			if ( hoverPrefixValue.length && hoverPrefixElement ) {
				hoverPrefixElement.innerHTML = hoverPrefixValue;
			}

			hoverPrefixElement = document.getElementById( 'tsf-title-placeholder-prefix' );
			if ( hoverPrefixValue.length && hoverPrefixElement ) {
				hoverPrefixElement.innerHTML = hoverPrefixValue;
			}
		}
		setHoverPrefixValue();
		const updateHoverPlacement = function( event ) {

			if ( ! hoverAdditionsElement && ! hoverPrefixElement )
				return;

			let $input = jQuery( event.target ),
				inputValue = $input.val();

			let hasAdditionsValue = !! hoverAdditionsValue.length,
				hasPrefixValue = !! hoverPrefixValue.length;

			if ( ! hasAdditionsValue && hoverAdditionsElement )
				hoverAdditionsElement.style.display = 'none';
			if ( ! hasPrefixValue && hoverPrefixElement )
				hoverPrefixElement.style.display = 'none';

			if ( ! hasAdditionsValue && ! hasPrefixValue ) {
				//= Both items are emptied through settings.
				$input.css( 'text-indent', 'initial' );
				return;
			}

			if ( ! inputValue.length ) {
				//= Input is emptied.
				$input.css( 'text-indent', "initial" );
				if ( hoverAdditionsElement ) hoverAdditionsElement.style.display = 'none';
				if ( hoverPrefixElement ) hoverPrefixElement.style.display = 'none';
				return;
			}

			let outerWidth = $input.outerWidth( true ),
				verticalPadding = ( $input.outerHeight( true ) - $input.height() ) / 2,
				horizontalPadding = ( outerWidth - $input.innerWidth() ) / 2;

			let offsetPosition = isRTL ? 'right' : 'left',
				leftOffset = ( $input.outerWidth( true ) - $input.width() ) / 2;

			let fontStyleCSS = {
				'display': $input.css( "display" ),
				'lineHeight': $input.css( "lineHeight" ),
				'fontFamily': $input.css( "fontFamily" ),
				'fontWeight': $input.css( "fontWeight" ),
				'fontSize': $input.css( "fontSize" ),
				'letterSpacing': $input.css( "letterSpacing" ),
				'paddingTop': verticalPadding + "px",
				'paddingBottom': verticalPadding + "px",
			};

			let $prefixElement = jQuery( hoverPrefixElement ),
				$additionsElement = jQuery( hoverAdditionsElement );

			let additionsMaxWidth = 0,
				additionsOffset = 0,
				prefixOffset = 0,
				totalIndent = 0,
				prefixMaxWidth = 0;

			let elipsisWidth = 0; // TODO make this real? x-Browser incompatible!

			if ( hasPrefixValue ) {
				$prefixElement.css( fontStyleCSS );
				$prefixElement.css( { 'maxWidth' : 'initial' } );
				prefixMaxWidth = $prefixElement[0].getBoundingClientRect().width;
				if ( prefixMaxWidth < elipsisWidth )
					prefixMaxWidth = 0;
			}
			if ( hasAdditionsValue ) {
				let textWidth = 0;

				(function() {
					let $offsetTest = jQuery( "#tsf-title-offset" );
					$offsetTest.text( inputValue );
					$offsetTest.css({
						'fontFamily' : fontStyleCSS.fontFamily,
						'fontWeight' : fontStyleCSS.fontWeight,
						'letterSpacing' : fontStyleCSS.letterSpacing,
						'fontSize' : fontStyleCSS.fontSize,
					});
					textWidth = $offsetTest[0].getBoundingClientRect().width;
				})();

				//= Input element width - Padding - input text width - prefix value width.
				additionsMaxWidth = $input[0].getBoundingClientRect().width - horizontalPadding - leftOffset - textWidth - prefixMaxWidth;
				if ( additionsMaxWidth < elipsisWidth ) {
					//= Add width to the prefix element, so it may stay its size, and hide the additions.
					prefixMaxWidth += additionsMaxWidth;
					additionsMaxWidth = 0;
				}
				$additionsElement.css( fontStyleCSS );
				$additionsElement.css( { 'maxWidth' : 'initial' } );

				switch ( hoverAdditionsPlacement ) {
					case 'before' :
						let additionsWidth = $additionsElement[0].getBoundingClientRect().width;

						additionsWidth = additionsMaxWidth < additionsWidth ? additionsMaxWidth : additionsWidth;
						if ( additionsWidth < elipsisWidth )
							additionsWidth = 0;
						additionsMaxWidth = additionsWidth;

						totalIndent += additionsMaxWidth;
						prefixOffset += additionsMaxWidth;
						additionsOffset += leftOffset;
						break;

					case 'after' :
						additionsOffset += leftOffset + textWidth + prefixMaxWidth;
						break;
				}
			}
			prefixOffset += leftOffset;
			prefixMaxWidth = prefixMaxWidth < 0 ? 0 : prefixMaxWidth;
			totalIndent += prefixMaxWidth;

			let _css;

			if ( hasPrefixValue ) {
				_css = {};
				_css[ offsetPosition ] = prefixOffset + "px";
				_css['maxWidth'] = prefixMaxWidth + "px";
				$prefixElement.css( _css );
			}
			if ( hasAdditionsValue ) {
				_css = {};
				_css[ offsetPosition ] = additionsOffset + "px";
				_css['maxWidth'] = additionsMaxWidth + "px";
				$additionsElement.css( _css );
			}

			_css = {};
			_css['text-indent'] = totalIndent + "px";
			$input.css( _css );
		}
		const updatePlaceholder = function() {

			let $input = $doctitles,
				_placeholder = '';

			let _hasAdditionsValue = !! hoverAdditionsValue.length,
				_hasPrefixValue = !! hoverPrefixValue.length;

			let _hoverAdditionsValue = hoverAdditionsValue,
				_hoverPrefixValue = hoverPrefixValue;

			if ( tsf.states.isTermEdit ) {
				if ( tsf.params.termName ) {
					_hoverPrefixValue = isRTL ? ' :' + tsf.params.termName : tsf.params.termName + ': ';
					_hasPrefixValue = tsf.states.useTermPrefix;
				}
			}

			_placeholder = defaultTitle;

			if ( _hasPrefixValue ) {
				switch ( hoverPrefixPlacement ) {
					case 'before' :
						_placeholder = _hoverPrefixValue + _placeholder;
						break;

					case 'after' :
						_placeholder = _placeholder + _hoverPrefixValue;
						break;
				}
			}
			if ( _hasAdditionsValue ) {
				switch ( hoverAdditionsPlacement ) {
					case 'before' :
						_placeholder = _hoverAdditionsValue + _placeholder;
						break;

					case 'after' :
						_placeholder = _placeholder + _hoverAdditionsValue;
						break;
				}
			}

			//= Microsoft be like: "Let's spare 0.000000000073% of our money."
			if ( ie11 ) ie11killswitch = true;

			//= Converts special characters without running scripts.
			let phText = document.createElement( 'span' );
			phText.innerHTML = _placeholder;

			$input.prop( 'placeholder', phText.textContent );

			//= Promise.
			ie11 && setTimeout( function() {
				ie11killswitch = false;
			}, 0 );
		}
		const setReferenceTitle = function( event ) {
			let reference = document.getElementById( 'tsf-title-reference' ),
				text = '';

			if ( ! reference ) return;

			if ( event.target.value.length < 1 ) {
				text = event.target.placeholder;
			} else {
				//= We must determine the position as trailing whitespace is rendered 0.
				text = event.target.value;

				if ( hoverPrefixValue.length ) {
					switch ( hoverPrefixPlacement ) {
						case 'before' :
							text = hoverPrefixValue + text;
							break;

						case 'after' :
							text = text + hoverPrefixValue;
							break;
					}
				}
				if ( hoverAdditionsValue.length ) {
					switch ( hoverAdditionsPlacement ) {
						case 'before' :
							text = hoverAdditionsValue + text;
							break;

						case 'after' :
							text = text + hoverAdditionsValue;
							break;
					}
				}
			}

			reference.innerHTML = tsf.escapeString( text );
			// Fires change event. Defered.
			setTimeout( () => { jQuery( reference ).change() }, 0 );
		}
		const updateCounter = function( event ) {
			let counter = document.getElementById( event.target.id + '_chars' ),
				reference = document.getElementById( 'tsf-title-reference' );

			if ( ! counter || ! reference ) return;

			let titLen = tsf.getStringLength( tsf.unescapeString( reference.innerHTML ) ),
				target = event.target,
				counterClass = '',
				counterType = tsf.counterType,
				counterName = '',
				output = '';

			if ( titLen < 25 || titLen >= 75 ) {
				counterClass += 'tsf-count-bad';
				counterName = tsf.i18n.bad;
			} else if ( titLen < 42 || titLen > 55 ) {
				counterClass += 'tsf-count-okay';
				counterName = tsf.i18n.okay;
			} else {
				counterClass += 'tsf-count-good';
				counterName = tsf.i18n.good;
			}

			if ( ! counterType || 1 == counterType ) {
				output = titLen.toString();
			} else if ( 2 == counterType ) {
				output = counterName;
			} else if ( 3 == counterType ) {
				output = titLen.toString() + ' - ' + counterName;
			}

			counter.innerHTML = output;

			if ( tsf.additionsClass )
				counterClass += ' ' + tsf.additionsClass;

			if ( counter.className !== counterClass )
				counter.className = counterClass;
		}
		const updatePixels = function( event ) {
			let pixels = document.getElementById( event.target.id + '_pixels' ),
				reference = document.getElementById( 'tsf-title-reference' );

			if ( ! pixels || ! reference )
				return;

			let test = {
				'e': pixels,
				'text' : tsf.unescapeString( reference.innerHTML ),
				'guideline' : tsf.params.titlePixelGuideline,
			};

			tsf.updatePixelCounter( test );
		}

		/**
		 * Updates placements, placeholders and counters.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const updateTitlesTrigger = function( event ) {
			if ( ie11killswitch ) return false;
			updateHoverPlacement( event );
			updatePlaceholder();
			setReferenceTitle( event );
			updateCounter( event );
			updatePixels( event );
		}
		$doctitles.on( 'input.tsfUpdateTitles', updateTitlesTrigger );

		/**
		 * Updates character counters.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const updateCounterTrigger = function( event ) {
			setReferenceTitle( event );
			updateCounter( event );
			updatePixels( event );
		}
		$doctitles.on( 'tsf-update-title-counter', updateCounterTrigger );

		/**
		 * Triggers counter updates.
		 *
		 * @function
		 * @return {undefined}
		 */
		const triggerCounter = function() {
			$doctitles.trigger( 'tsf-update-title-counter' );
		}

		/**
		 * Triggers doctitles input.
		 *
		 * @function
		 * @return {undefined}
		 */
		const triggerInput = function() {
			$doctitles.trigger( 'input.tsfUpdateTitles' );
		}
		triggerInput();

		/**
		 * Triggers additions hover update on tagline change.
		 *
		 * @function
		 * @return {undefined}
		 */
		const updateTagline = function() {
			setHoverAdditionsValue();
			triggerInput();
		}
		jQuery( '#autodescription-site-settings\\[homepage_title_tagline\\]' ).on( 'input', updateTagline );
		jQuery( '#autodescription-site-settings\\[homepage_tagline\\]' ).on( 'change', updateTagline );

		let triggerBuffer = 0;
		/**
		 * Triggers doctitles input.
		 * @function
		 * @return {undefined}
		 */
		const enqueueTriggerInput = function() {
			clearTimeout( triggerBuffer );
			triggerBuffer = setTimeout( function() {
				triggerInput();
			}, 50 );
		}
		jQuery( window ).on( 'tsf-counter-updated', enqueueTriggerInput );

		/**
		 * Toggles tagline left/right example additions visibility for the homepage title.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const changeHomePageAdditionsVisibility = function( event ) {
			let prevUseTagline = useTagline;

			if ( jQuery( event.target ).is( ':checked' ) ) {
				jQuery( '.tsf-custom-blogname-js' ).css( 'display', 'inline' );
				useTagline = true;
			} else {
				jQuery( '.tsf-custom-blogname-js' ).css( 'display', 'none' );
				useTagline = false;
			}

			if ( prevUseTagline ^ useTagline ) {
				setHoverAdditionsValue();
				enqueueTriggerInput();
			}
		}
		jQuery( '#tsf-title-tagline-toggle :input' ).on( 'click', changeHomePageAdditionsVisibility );

		/**
		 * Updates private/protected title prefix upon Post visibility switch.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const updateVisibility = function( event ) {
			let value = jQuery( '#visibility' ).find( 'input:radio:checked' ).val();

			isPrivate = false;
			isPasswordProtected = false;

			switch ( value ) {
				case 'password' :
					let p = jQuery( '#visibility' ).find( '#post_password' ).val();
					isPasswordProtected = p ? !! p.length : false;
					break;

				case 'private' :
					isPrivate = true;
					break;

				default :
				case 'public' :
					break;
			}

			//* @TODO move all of the above to a global state handler?
			setHoverPrefixValue();
			enqueueTriggerInput();
		}
		jQuery( '#visibility .save-post-visibility' ).on( 'click', updateVisibility );

		/**
		 * Updates used separator and all examples thereof.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const updateSeparator = function( event ) {
			let val = jQuery( event.target ).val(),
				newSep = '';

			switch ( val ) {
				case 'pipe' :
					newSep = '|';
					break;

				case 'dash' :
					newSep = '-';
					break;

				default :
					newSep = jQuery( '<div/>' ).html( "&" + val + ";" ).text();
					break;
			}
			separator = newSep;
			jQuery( ".tsf-sep-js" ).text( ' ' + separator + ' ' );

			setHoverAdditionsValue();
			enqueueTriggerInput();
		}
		jQuery( '#tsf-title-separator :input' ).on( 'click', updateSeparator );

		/**
		 * Triggers title update, without affecting change listeners.
		 *
		 * @function
		 * @param {!jQuery.Event}
		 * @return {undefined}
		 */
		const triggerUnregisteredInput = function() {
			let settingsChangedCache = tsf.settingsChanged;
			triggerInput( true );
			tsf.settingsChanged = settingsChangedCache;
		}
		jQuery( '#homepage-tab-general' ).on( 'tsf-tab-toggled', triggerUnregisteredInput );
		jQuery( '#tsf-flex-inpost-tab-general' ).on( 'tsf-flex-tab-toggled', triggerUnregisteredInput );

		let unregisteredTriggerBuffer = 0;
		/**
		 * Enqueues doctitles input trigger.
		 * @function
		 * @return {undefined}
		 */
		const enqueueUnregisteredInputTrigger = function() {
			clearTimeout( unregisteredTriggerBuffer );
			unregisteredTriggerBuffer = setTimeout( triggerUnregisteredInput, 50 );
		}
		//= Defer to prevent early trigger.
		jQuery( window ).one( 'tsf-ready', function() {
			jQuery( window ).on( 'tsf-flex-resize', enqueueUnregisteredInputTrigger );
		} );

		let postboxIds = [ 'autodescription-homepage-settings', 'tsf-inpost-box' ];
		/**
		 * Enqueues doctitles input trigger synchronously.
		 * @function
		 * @param {!jQuery.Event} event
		 * @param {Element} elem
		 * @return {undefined}
		 */
		const triggerPostboxSynchronousUnregisteredInput = function( event, elem ) {
			if ( postboxIds.indexOf( elem.id ) >= 0 ) {
				let inside = elem.querySelector( '.inside' );
				if ( inside.offsetHeight > 0 && inside.offsetWidth > 0 ) {
					triggerUnregisteredInput();
				}
			}
		}
		jQuery( document ).on( 'postbox-toggled', triggerPostboxSynchronousUnregisteredInput );

		/**
		 * Triggers additions hover update on tagline placement change.
		 * @function
		 * @return {undefined}
		 */
		const setTaglinePlacement = function() {
			setHoverAdditionsPlacement();
			setHoverAdditionsValue();
			enqueueTriggerInput();
		}
		jQuery( '#tsf-home-title-location :input' ).on( 'click', setTaglinePlacement );

		/**
		 * Updates default title placeholder.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const updateDefaultTitle = function( event ) {
			let val = event.target.value;
			val = val.trim();

			if ( val.length ) {
				defaultTitle = tsf.escapeString( val );
			} else {
				defaultTitle = tsf.params.untitledTitle;
			}
			updatePlaceholder();
			triggerCounter();
		}
		//= The home page listens to a static preset value. Update all others.
		if ( ! tsf.states.isHome ) {
			jQuery( '#edittag #name, #titlewrap #title' ).on( 'input', updateDefaultTitle );
		}

		/**
		 * Triggers input event for titles in set intervals on window resize.
		 *
		 * This only happens if boundaries are surpassed to reduce CPU usage.
		 * This boundary is 782 pixels, because that forces input fields to change.
		 * in WordPress.
		 *
		 * @function
		 * @return {undefined}
		 */
		const prepareUnregisteredInputOnResize = function() {
			let resizeTimeout = 0,
				prevWidth = window.innerWidth;

			window.addEventListener( 'resize', function() {
				clearTimeout( resizeTimeout );
				resizeTimeout = setTimeout( function() {
					let width = window.innerWidth;
					if ( prevWidth < width ) {
						if ( prevWidth <= 782 && width >= 782 ) {
							triggerUnregisteredInput();
						}
					} else {
						if ( prevWidth >= 782 && width <= 782 ) {
							triggerUnregisteredInput();
						}
					}
					prevWidth = width;
				}, 50 );
			} );
		}
		prepareUnregisteredInputOnResize();
	},

	/**
	 * Initializes unbound title settings, which don't affect prefix/additions
	 * on-page.
	 *
	 * @since 3.0.0
	 *
	 * @function
	 * @return {undefined}
	 */
	_initUnboundTitleSettings: function() {

		if ( ! tsf.hasInput )
			return;

		let $doctitles = jQuery( "#autodescription_title, #autodescription-meta\\[doctitle\\], #autodescription-site-settings\\[homepage_title\\]" );

		/**
		 * Makes user click act naturally by selecting the adjacent Title text
		 * input and move cursor all the way to the end.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const focusTitleInput = function( event ) {
			let input = jQuery( event.target ).siblings( $doctitles )[0];

			if ( 'function' === typeof input.setSelectionRange ) {
				input.focus();
				let length = input.value.length * 2;
				input.setSelectionRange( length, length );
			} else {
				//= Older browser compat.
				let val = input.value;
				input.value = '';
				input.focus();
				input.value = val;
			}
		}
		jQuery( '#tsf-title-placeholder, #tsf-title-placeholder-prefix' ).on( 'click', focusTitleInput );

		/**
		 * Triggers Change on Left/Right selection of global title options.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const toggleExampleDisplay = function( event ) {
			if ( jQuery( event.target ).is( ':checked' ) ) {
				jQuery( '.tsf-title-additions-js' ).css( 'display', 'none' );
			} else {
				jQuery( '.tsf-title-additions-js' ).css( 'display', 'inline' );
			}
		}
		jQuery( '#autodescription-site-settings\\[title_rem_additions\\]' ).on( 'click', toggleExampleDisplay );

		/**
		 * Toggles title additions location for the Title examples.
		 * There are two elements, rather than one. One is hidden by default.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const toggleExampleLocation = function( event ) {
			let $titleExampleLeft = jQuery( '.tsf-title-additions-example-left' ),
				$titleExampleRight = jQuery( '.tsf-title-additions-example-right' );

			if ( 'right' === jQuery( event.target ).val() ) {
				$titleExampleLeft.css( 'display', 'none' );
				$titleExampleRight.css( 'display', 'inline' );
			} else {
				$titleExampleLeft.css( 'display', 'inline' );
				$titleExampleRight.css( 'display', 'none' );
			}
		}
		jQuery( '#tsf-title-location input' ).on( 'click', toggleExampleLocation );

		/**
		 * Adjusts homepage left/right title example part.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const adjustHomepageExampleOutput = function( event ) {
			let val = event.target.value || '',
				$title = jQuery( '.tsf-custom-title-js' );

			if ( 0 === val.length ) {
				$title.text( tsf.params.defaultTitle );
			} else {
				$title.text( val );
			}
		};
		jQuery( '#autodescription-site-settings\\[homepage_title\\]' ).on( 'input', adjustHomepageExampleOutput );

		/**
		 * Adjusts homepage left/right title example part.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const adjustHomepageTaglineExampleOutput = function( event ) {
			let val = event.target.value || '',
				$tagline = jQuery( '.tsf-custom-tagline-js' );

			if ( 0 === val.length ) {
				$tagline.text( tsf.params.blogDescription );

				if ( 0 === tsf.params.blogDescription.length ) {
					jQuery( '#tsf-home-title-location .tsf-sep-js' ).hide();
				} else {
					jQuery( '#tsf-home-title-location .tsf-sep-js' ).show();
				}
			} else {
				$tagline.text( val );
				jQuery( '#tsf-home-title-location .tsf-sep-js' ).show();
			}
		};
		jQuery( '#autodescription-site-settings\\[homepage_title_tagline\\]' ).on( 'input.tsfInputTagline', adjustHomepageTaglineExampleOutput );
		jQuery( '#autodescription-site-settings\\[homepage_title_tagline\\]' ).trigger( 'input.tsfInputTagline' );

		/**
		 * Toggles title prefixes for the Prefix Title example.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const adjustPrefixExample = function( event ) {
			let $this = jQuery( event.target ),
				$prefix = jQuery( '.tsf-title-prefix-example' );

			if ( $this.is(':checked') ) {
				$prefix.css( 'display', 'none' );
			} else {
				$prefix.css( 'display', 'inline' );
			}
		}
		jQuery( '#title-prefixes-toggle :input' ).on( 'click', adjustPrefixExample );
	},

	/**
	 * Initializes all aspects for description editing.
	 * Assumes only one description editor is present in the DOM.
	 *
	 * @since 3.0.0
	 *
	 * @function
	 * @return {undefined}
	 */
	_initDescInputs: function() {

		if ( ! tsf.hasInput )
			return;

		let $descriptions = jQuery( "#autodescription_description, #autodescription-meta\\[description\\], #autodescription-site-settings\\[homepage_description\\]" );

		if ( ! $descriptions.length )
			return;

		let separator = tsf.params.descriptionSeparator;

		/**
		 * Updates used separator and all examples thereof.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const updateSeparator = function( event ) {
			let val = jQuery( event.target ).val(),
				newSep = '';

			switch ( val ) {
				case 'pipe' :
					newSep = '|';
					break;

				case 'dash' :
					newSep = '-';
					break;

				default :
					newSep = jQuery( '<div/>' ).html( "&" + val + ";" ).text();
					break;
			}
			separator = newSep;
			jQuery( "#autodescription-descsep-js" ).text( ' ' + separator + ' ' );

			enqueueTriggerInput();
		}
		jQuery( '#tsf-description-separator input' ).on( 'click', updateSeparator );

		/**
		 * Updates used separator and all examples thereof.
		 *
		 * @since 3.0.4 : 1. Threshold "too long" has been increased from 155 to 300.
		 *                2. Threshold "far too long" has been increased to 330 from 175.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const updateCounter = function( event ) {

			let counter = document.getElementById( event.target.id + '_chars' );

			if ( ! counter )
				return;

			let descLen = 0,
				target = event.target,
				counterClass = '',
				counterType = tsf.counterType,
				counterName = '',
				output = '';

			if ( target.value.length < 1 ) {
				descLen = tsf.getStringLength( target.placeholder );
			} else {
				descLen = tsf.getStringLength( target.value );
			}

			if ( descLen < 100 || descLen >= 330 ) {
				counterClass += 'tsf-count-bad';
				counterName = tsf.i18n.bad;
			} else if ( descLen < 137 || descLen > 300 ) {
				counterClass += 'tsf-count-okay';
				counterName = tsf.i18n.okay;
			} else {
				counterClass += 'tsf-count-good';
				counterName = tsf.i18n.good;
			}

			if ( ! counterType || 1 == counterType ) {
				output = descLen.toString();
			} else if ( 2 == counterType ) {
				output = counterName;
			} else if ( 3 == counterType ) {
				output = descLen.toString() + ' - ' + counterName;
			}

			counter.innerHTML = output;

			if ( tsf.additionsClass )
				counterClass += ' ' + tsf.additionsClass;

			if ( counter.className !== counterClass )
				counter.className = counterClass;
		}
		const setReferenceDescription = function( event ) {
			let reference = document.getElementById( 'tsf-description-reference' ),
				text = '';

			if ( ! reference ) return;

			if ( event.target.value.length < 1 ) {
				text = event.target.placeholder;
			} else {
				text = event.target.value;
			}

			reference.innerHTML = tsf.escapeString( text );
			// Fires change event. Defered.
			setTimeout( () => { jQuery( reference ).change() }, 0 );
		}
		const updatePixels = function( event ) {
			let pixels = document.getElementById( event.target.id + '_pixels' ),
				reference = document.getElementById( 'tsf-description-reference' );

			if ( ! pixels || ! reference )
				return;

			let test = {
				'e': pixels,
				'text' : tsf.unescapeString( reference.innerHTML ),
				'guideline' : tsf.params.descPixelGuideline
			}

			tsf.updatePixelCounter( test );
		}

		/**
		 * Updates placements, placeholders and counters.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const updateDescriptionsTrigger = function( event ) {
			setReferenceDescription( event );
			updateCounter( event );
			updatePixels( event );
		}
		$descriptions.on( 'input.tsfUpdateDescriptions', updateDescriptionsTrigger );

		/**
		 * Triggers descriptions input.
		 *
		 * @function
		 * @return {undefined}
		 */
		const triggerInput = function() {
			$descriptions.trigger( 'input.tsfUpdateDescriptions' );
		}
		triggerInput();

		let triggerBuffer = 0;
		/**
		 * Triggers descriptions input.
		 * @function
		 * @return {undefined}
		 */
		const enqueueTriggerInput = function() {
			clearTimeout( triggerBuffer );
			triggerBuffer = setTimeout( function() {
				triggerInput();
			}, 50 );
		}
		jQuery( window ).on( 'tsf-counter-updated', enqueueTriggerInput );

		/**
		 * Triggers description input, without affecting change listeners.
		 *
		 * @function
		 * @param {!jQuery.Event}
		 * @return {undefined}
		 */
		const triggerUnregisteredInput = function() {
			let settingsChangedCache = tsf.settingsChanged;
			triggerInput();
			tsf.settingsChanged = settingsChangedCache;
		}

		let postboxIds = [ 'autodescription-homepage-settings', 'tsf-inpost-box' ];
		/**
		 * Enqueues description input trigger synchronously.
		 * @function
		 * @param {!jQuery.Event} event
		 * @param {Element} elem
		 * @return {undefined}
		 */
		const triggerPostboxSynchronousUnregisteredInput = function( event, elem ) {
			if ( postboxIds.indexOf( elem.id ) >= 0 ) {
				let inside = elem.querySelector( '.inside' );
				if ( inside.offsetHeight > 0 && inside.offsetWidth > 0 ) {
					enqueueTriggerInput();
				}
			}
		}
		jQuery( document ).on( 'postbox-toggled', triggerPostboxSynchronousUnregisteredInput );
	},

	/**
	 * Initializes social titles.
	 *
	 * @since 3.0.4
	 *
	 * @function
	 * @return {undefined}
	 */
	_initSocialTitleInputs: function() {

		if ( ! tsf.hasInput )
			return;

		let $ogTitle = jQuery( "#autodescription_og_title" ),
			$twTitle = jQuery( "#autodescription_twitter_title" ),
			$reference = jQuery( "#tsf-title-reference" );

		if ( ! $ogTitle.length || ! $twTitle.length || ! $reference.length )
			return;

		//= y u no fix dis, Microsoft. Crappy vars don't deserve CamelCase.
		let ie11killswitch = false,
			ie11 = !! navigator.userAgent.match(/Trident\/7\./);

		let ogTitleValue = tsf.escapeString( $ogTitle.val() ),
			twTitleValue = tsf.escapeString( $twTitle.val() ),
			referenceValue = $reference.text(); // already escaped.

		const getActiveValue = ( what ) => {
			let val = '';
			switch ( what ) {
				case 'twitter' :
					val = twTitleValue;
					// get next if not set.
				case 'og' :
					val = val.length ? val : ogTitleValue;
					// get next if not set.
				case 'reference' :
					val = val.length ? val : referenceValue;
			}
			return val;
		};
		const setPlaceholders = () => {
			if ( ie11 ) ie11killswitch = true;

			$ogTitle.attr( 'placeholder', getActiveValue( 'reference' ) );
			$twTitle.attr( 'placeholder', getActiveValue( 'og' ) );

			ie11 && setTimeout( function() {
				ie11killswitch = false;
			}, 0 );
		};
		const updateCounter = ( target, value, threshold ) => {
			tsf.updateSocialCounter( target, value, threshold );
		};
		const updateAllCounters = () => {
			$ogTitle.each( ( i, el ) => updateCounter( el, getActiveValue( 'og' ), 88 ) );
			$twTitle.each( ( i, el ) => updateCounter( el, getActiveValue( 'twitter' ), 70 ) );
		};
		$reference.on( 'change', () => {
			referenceValue = $reference.text();
			setPlaceholders();
			updateAllCounters();
		} );

		const updateOgTitle = ( event ) => {
			if ( ie11killswitch ) return;
			ogTitleValue = event.target.value.length ? tsf.escapeString( event.target.value ) : '';
			setPlaceholders();
			updateAllCounters();
		};
		const updateTwTitle = ( event ) => {
			if ( ie11killswitch ) return;
			twTitleValue = event.target.value.length ? tsf.escapeString( event.target.value ) : '';
			setPlaceholders();
			updateAllCounters();
		};
		$ogTitle.on( 'input.tsfUpdateOgTitle', updateOgTitle );
		$twTitle.on( 'input.tsfUpdateOgTitle', updateTwTitle );
	},

	/**
	 * Initializes social descriptions.
	 *
	 * @since 3.0.0
	 *
	 * @function
	 * @return {undefined}
	 */
	_initSocialDescInputs: function() {

		if ( ! tsf.hasInput )
			return;

		let $ogDesc = jQuery( "#autodescription_og_description" ),
			$twDesc = jQuery( "#autodescription_twitter_description" ),
			$reference = jQuery( "#tsf-description-reference" );

		if ( ! $ogDesc.length || ! $twDesc.length || ! $reference.length )
			return;

		//= y u no fix dis, Microsoft. Crappy vars don't deserve CamelCase.
		let ie11killswitch = false,
			ie11 = !! navigator.userAgent.match(/Trident\/7\./);

		let ogDescValue = tsf.escapeString( $ogDesc.val() ),
			twDescValue = tsf.escapeString( $twDesc.val() ),
			referenceValue = $reference.text(); // already escaped.

		const getActiveValue = ( what ) => {
			let val = '';
			switch ( what ) {
				case 'twitter' :
					val = twDescValue;
					// get next if not set.
				case 'og' :
					val = val.length ? val : ogDescValue;
					// get next if not set.
				case 'reference' :
					val = val.length ? val : referenceValue;
			}
			return val;
		};
		const setPlaceholders = () => {
			if ( ie11 ) ie11killswitch = true;

			$ogDesc.attr( 'placeholder', getActiveValue( 'reference' ) );
			$twDesc.attr( 'placeholder', getActiveValue( 'og' ) );

			ie11 && setTimeout( function() {
				ie11killswitch = false;
			}, 0 );
		};
		const updateCounter = ( target, value, threshold ) => {
			tsf.updateSocialCounter( target, value, threshold );
		};
		const updateAllCounters = () => {
			$ogDesc.each( ( i, el ) => updateCounter( el, getActiveValue( 'og' ), 300 ) );
			$twDesc.each( ( i, el ) => updateCounter( el, getActiveValue( 'twitter' ), 200 ) );
		};
		$reference.on( 'change', () => {
			referenceValue = $reference.text();
			setPlaceholders();
			updateAllCounters();
		} );

		const updateOgDesc = ( event ) => {
			if ( ie11killswitch ) return;
			ogDescValue = event.target.value.length ? tsf.escapeString( event.target.value ) : '';
			setPlaceholders();
			updateAllCounters();
		};
		const updateTwDesc = ( event ) => {
			if ( ie11killswitch ) return;
			twDescValue = event.target.value.length ? tsf.escapeString( event.target.value ) : '';
			setPlaceholders();
			updateAllCounters();
		};
		$ogDesc.on( 'input.tsfUpdateOgDesc', updateOgDesc );
		$twDesc.on( 'input.tsfUpdateOgDesc', updateTwDesc );
	},

	/**
	 * Updates normalized counters for social input.
	 *
	 * @since 3.0.4
	 *
	 * @function
	 * @param {!Element} target The HMTL eleent.
	 * @param {string} value Must be escaped.
	 * @param {integer} threshold The "bad" threshold.
	 */
	updateSocialCounter: function( target, value, threshold ) {

		let counter = document.getElementById( target.id + '_chars' );

		if ( ! counter )
			return;

		let strLen = tsf.getStringLength( tsf.unescapeString( value ) ),
			counterClass = '',
			counterType = tsf.counterType,
			counterName = '',
			output = '';

		if ( strLen > threshold ) {
			counterClass += 'tsf-count-bad';
			counterName = tsf.i18n.bad;
		} else {
			counterClass += 'tsf-count-good';
			counterName = tsf.i18n.good;
		}

		if ( ! counterType || 1 == counterType ) {
			output = strLen.toString();
		} else if ( 2 == counterType ) {
			output = counterName;
		} else if ( 3 == counterType ) {
			output = strLen.toString() + ' - ' + counterName;
		}

		counter.innerHTML = output;

		if ( tsf.additionsClass )
			counterClass += ' ' + tsf.additionsClass;

		if ( counter.className !== counterClass )
			counter.className = counterClass;
	},

	/**
	 * Initializes counters.
	 *
	 * @since 3.0.0
	 *
	 * @function
	 * @return {undefined}
	 */
	_initCounters: function() {

		if ( ! tsf.hasInput )
			return;

		/**
		 * Updates the counter type.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const counterUpdate = function( event ) {

			// Count up, reset to 0 if needed. We have 4 options: 0, 1, 2, 3
			tsf.counterType = tsf.counterType + 1;
			if ( tsf.counterType > 3 )
				tsf.counterType = 0;

			//* Update counters locally.
			updateCounterClasses();

			let target = '.tsf-counter-wrap .tsf-ajax',
				status = 0;

			//* Reset ajax loader
			tsf.resetAjaxLoader( target );

			//* Set ajax loader.
			tsf.setAjaxLoader( target );

			//* Setup external update.
			let settings = {
				method: 'POST',
				url: ajaxurl,
				datatype: 'json',
				data: {
					'action' : 'the_seo_framework_update_counter',
					'nonce' : tsf.nonces['edit_posts'],
					'val' : tsf.counterType,
				},
				async: true,
				success: function( response ) {

					response = tsf.convertJSONResponse( response );

					//* I could do value check, but that will simply lag behind. Unless an annoying execution delay is added.
					if ( 'success' === response.type )
						status = 1;

					switch ( status ) {
						case 0:
							tsf.unsetAjaxLoader( target, false );
							break;
						case 1:
							tsf.unsetAjaxLoader( target, true );
							break;
						default:
							tsf.resetAjaxLoader( target );
							break;
					}
				},
			}

			jQuery.ajax( settings );
		}
		jQuery( '.tsf-counter' ).on( 'click', counterUpdate );

		/**
		 * Sets up additionsClass variable.
		 * Also sets up browser caches correctly.
		 *
		 * @function
		 * @return {undefined}
		 */
		const updateCounterClasses = function() {

			let counterType = tsf.counterType;

			if ( 1 == counterType ) {
				tsf.additionsClass = 'tsf-counter-one';
				tsf.counterType = 1;
			} else if ( 2 == counterType ) {
				tsf.additionsClass = 'tsf-counter-two';
				tsf.counterType = 2;
			} else if ( 3 == counterType ) {
				tsf.additionsClass = 'tsf-counter-three';
				tsf.counterType = 3;
			} else {
				tsf.additionsClass = 'tsf-counter-zero';
				tsf.counterType = 0;
			}

			tsf._triggerCounterUpdate();
		}
		updateCounterClasses();

		/**
		 * Triggers displaying/hiding of character counters.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const toggleCharacterCounterDisplay = function( event ) {
			if ( jQuery( event.target ).is( ':checked' ) ) {
				jQuery( '.tsf-counter-wrap' ).show();
			} else {
				jQuery( '.tsf-counter-wrap' ).hide();
			}
		}
		jQuery( '#autodescription-site-settings\\[display_character_counter\\]' ).on( 'click', toggleCharacterCounterDisplay );

		/**
		 * Triggers displaying/hiding of character counters.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const togglePixelCounterDisplay = function( event ) {
			if ( jQuery( event.target ).is( ':checked' ) ) {
				jQuery( '.tsf-pixel-counter-wrap' ).show();
				//= Pixels couldn't be counted when it was hidden.
				tsf._triggerCounterUpdate();
			} else {
				jQuery( '.tsf-pixel-counter-wrap' ).hide();
			}
		}
		jQuery( '#autodescription-site-settings\\[display_pixel_counter\\]' ).on( 'click', togglePixelCounterDisplay );
	},

	/**
	 * Sets correct tab content and classes on toggle.
	 *
	 * @since 2.2.2
	 * @since 2.6.0 Improved.
	 * @since 2.9.0 Now always expects radio button input.
	 * @see tsf.setTabsOnload
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {(undefined|null)}
	 */
	tabToggle: function( event ) {

		let $this = jQuery( event.target );

		if ( ! $this.is( ':checked' ) )
			return;

		let target = $this.prop( 'id' ),
			name = $this.prop( 'name' );

		if ( typeof name !== 'undefined' ) {
			let activeClass = 'tsf-active-tab-content',
				$newContent = jQuery( '#' + target + '-content' ),
				$previousContent = jQuery( '.' + activeClass );

			//* Only parse if old content isn't the new.
			if ( ! $newContent.is( $previousContent ) && typeof $newContent !== 'undefined' ) {
				let $allContent = jQuery( '.' + name + '-content' );

				$allContent.fadeOut( 150, function() {
					jQuery( this ).removeClass( activeClass );
				} );
				setTimeout( function() {
					$newContent.addClass( activeClass ).fadeIn( 250 );
				}, 150 );
				setTimeout( function() {
					jQuery( '#' + target ).trigger( 'tsf-tab-toggled' );
				}, 175 );
			}
		}
	},

	/**
	 * Refines Styling for the navigation tabs on the settings pages
	 *
	 * @since 2.9.0
	 * @todo merge with tabTobble or a collective method?
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {(undefined|null)}
	 */
	flexTabToggle: function( event ) {

		let $this = jQuery( event.target );

		if ( ! $this.is( ':checked' ) )
			return;

		let target = $this.prop( 'id' ),
			name = $this.prop( 'name' );

		if ( typeof name !== 'undefined' ) {
			let activeClass = 'tsf-flex-tab-content-active',
				$newContent = jQuery( '#' + target + '-content' ),
				$previousContent = jQuery( '.' + activeClass );

			//* Only parse if old content isn't the new.
			if ( ! $newContent.is( $previousContent ) && typeof $newContent !== 'undefined' ) {
				let $allContent = jQuery( '.' + name + '-content' );

				$allContent.fadeOut( 150, function() {
					jQuery( this ).removeClass( activeClass );
				} );
				setTimeout( function() {
					$newContent.addClass( activeClass ).fadeIn( 250 );
				}, 150 );
				setTimeout( function() {
					jQuery( '#' + target ).trigger( 'tsf-flex-tab-toggled' );
				}, 175 );
			}
		}
	},

	/**
	 * Sets the navigation tabs content equal to the buttons.
	 *
	 * @since 2.9.0
	 * @since 3.0.4 Added inpost flex nav trigger.
	 * @see tsf.tabToggle
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	setTabsOnload: function() {

		if ( ! tsf.hasInput )
			return;

		if ( tsf.states['isPostEdit'] ) {
			//= Triggers inpost change event for tabs
			jQuery( '.tsf-flex-nav-tab-radio' ).trigger( 'change' );
		}

		if ( tsf.states['isSettingsPage'] ) {
			let $buttons = jQuery( '.tsf-nav-tab-wrapper .tsf-tab:nth-of-type(n+2) input:checked' );

			// Select all second or later tabs that have attribute checked.
			if ( $buttons.length ) {
				$buttons.each( function( i ) {
					let $this = jQuery( this ),
						target = $this.prop( 'id' ),
						name = $this.prop( 'name' );

					if ( typeof name !== 'undefined' ) {
						let activeClass = 'tsf-active-tab-content',
							$newContent = jQuery( '#' + target + '-content' );

						//* Only parse if old content isn't the new.
						if ( typeof $newContent !== 'undefined' ) {
							let $allContent = jQuery( '.' + name + '-content' );

							$allContent.removeClass( activeClass );
							$newContent.addClass( activeClass );
							setTimeout( function() {
								jQuery( '#' + target ).trigger( 'tsf-tab-toggled' );
							}, 20 );
						}
					}
				} );
			}
		} else {
			// WordPress resets radio buttons on inpost settings. Leave this open for "when".
		}
	},

	/**
	 * Toggle tagline within the Description Example.
	 *
	 * @since 2.3.4
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	taglineToggleDesc: function( event ) {

		let $this = jQuery( event.target ),
			$tagDesc = jQuery( '#tsf-on-blogname-js' );

		if ( $this.is(':checked') ) {
			$tagDesc.css( 'display', 'inline' );
		} else {
			$tagDesc.css( 'display', 'none' );
		}
	},

	/**
	 * Toggle additions within Description example for the Example Description
	 *
	 * @since 2.6.0
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	additionsToggleDesc: function( event ) {

		let $this = jQuery( event.target ),
			$tagDesc = jQuery( '#tsf-description-additions-js' );

		if ( $this.is(':checked') ) {
			$tagDesc.css( 'display', 'inline' );
		} else {
			$tagDesc.css( 'display', 'none' );
		}
	},

	/**
	 * Toggle tagline end examples within the Left/Right example for the
	 * HomePage Title or Description.
	 *
	 * @since 2.2.7
	 *
	 * @function
	 * @return {undefined}
	 */
	taglineToggleOnload: function() {

		if ( ! tsf.hasInput )
			return;

		let $tagTitle = jQuery( '#tsf-title-tagline-toggle :input' ),
			$title = jQuery( '.tsf-custom-blogname-js' ),
			$tagDescAdditions = jQuery( '#tsf-description-additions-toggle :input' ),
			$descAdditions = jQuery( '#tsf-description-additions-js' ),
			$tagDescBlogname = jQuery( '#tsf-description-onblogname-toggle :input' ),
			$descBlogname = jQuery( '#tsf-on-blogname-js' ),
			$tagTitleAdditions = jQuery( '#tsf-title-additions-toggle :input' ),
			$titleAdditions = jQuery( '.tsf-title-additions-js' );

		if ( $tagTitle.is( ':checked' ) ) {
			$title.css( 'display', 'inline' );
		} else {
			$title.css( 'display', 'none' );
		}

		if ( $tagDescAdditions.is( ':checked' ) ) {
			$descAdditions.css( 'display', 'inline' );
		} else {
			$descAdditions.css( 'display', 'none' );
		}

		if ( $tagDescBlogname.is( ':checked' ) ) {
			$descBlogname.css( 'display', 'inline' );
		} else {
			$descBlogname.css( 'display', 'none' );
		}

		// Reverse option.
		if ( $tagTitleAdditions.is( ':checked' ) ) {
			$titleAdditions.css( 'display', 'none' );
		} else {
			$titleAdditions.css( 'display', 'inline' );
		}
	},

	/**
	 * Have all form fields in The SEO Framework metaboxes set a dirty flag when changed.
	 *
	 * @since 2.0.0
	 * @since 2.9.3 No longer heavily invokes change listeners after change has been set.
	 *
	 * @function
	 * @return {undefined}
	 */
	attachUnsavedChangesListener: function() {

		if ( ! tsf.hasInput )
			return;

		//= Self calling and cancelling function.
		let setUnsetChange = (function( event ) {
			tsf.settingsChanged || tsf.registerChange();
			jQuery( input ).not( except ).off( event.type, setUnsetChange );
		});

		//= Mouse input
		let input = '.tsf-metaboxes :input, #tsf-inpost-box .inside :input',
			except = '.tsf-tab :input, .tsf-flex-nav-tab :input';
		jQuery( input ).not( except ).on( 'change', setUnsetChange );

		//= Text input
		input = '.tsf-metaboxes input[type=text], .tsf-metaboxes textarea, #tsf-inpost-box .inside input[type=text], #tsf-inpost-box .inside textarea';
		except = '.tsf-nav-tab-wrapper input, .tsf-flex-nav-tab-wrapper input';
		jQuery( input ).not( except ).on( 'input', setUnsetChange );

		//= Alert caller (doesn't work well when leave alerts have been disabled)
		window.onbeforeunload = function() {
			if ( tsf.settingsChanged ) {
				return tsf.i18n['saveAlert'];
			}
		};

		//= Remove alert on saving object or delete calls.
		jQuery( '.tsf-metaboxes input[type="submit"], #publishing-action input[type="submit"], #save-action input[type="submit"], a.submitdelete' ).click( function() {
			window.onbeforeunload = null;
		} );
	},

	/**
	 * Set a flag, to indicate form fields have changed.
	 *
	 * @since 2.2.4
	 *
	 * @function
	 * @return {undefined}
	 */
	registerChange: function() {
		tsf.settingsChanged = true;
	},

	/**
	 * Ask user to confirm that settings should now be reset.
	 *
	 * @since 2.2.4
	 *
	 * @function
	 * @return {(Boolean|null)} True if reset should occur, false if not.
	 */
	confirmedReset: function() {
		return confirm( tsf.i18n['confirmReset'] );
	},

	/**
	 * OnLoad changes can affect settings changes. This function reverts those.
	 *
	 * @since 2.5.0
	 *
	 * @function
	 * @return {undefined}
	 */
	onLoadUnregisterChange: function() {
		//* Prevent trigger of settings change
		tsf.settingsChanged = false;
	},

	/**
	 * Dismissible notices. Uses class .tsf-notice.
	 *
	 * @since 2.6.0
	 * @since 2.9.3 Now correctly removes the node from DOM.
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	dismissNotice: function( event ) {
		jQuery( event.target ).parents( '.tsf-notice' ).slideUp( 200, function() {
			this.remove();
		} );
	},

	/**
	 * Visualizes AJAX loading time through target class change.
	 *
	 * @since 2.7.0
	 *
	 * @function
	 * @param {String} target
	 * @return {undefined}
	 */
	setAjaxLoader: function( target ) {
		jQuery( target ).toggleClass( 'tsf-loading' );
	},

	/**
	 * Adjusts class loaders on Ajax response.
	 *
	 * @since 2.7.0
	 *
	 * @function
	 * @param {String} target
	 * @param {Boolean} success
	 * @return {undefined}
	 */
	unsetAjaxLoader: function( target, success ) {

		let newclass = 'tsf-success',
			fadeTime = 2500;

		if ( ! success ) {
			newclass = 'tsf-error';
			fadeTime = 5000;
		}

		jQuery( target ).removeClass( 'tsf-loading' ).addClass( newclass ).fadeOut( fadeTime );
	},

	/**
	 * Cleans and resets Ajax wrapper class and contents to default.
	 * Also stops any animation and resets fadeout to beginning.
	 *
	 * @since 2.7.0
	 *
	 * @function
	 * @param {String} target
	 * @return {undefined}
	 */
	resetAjaxLoader: function( target ) {
		jQuery( target ).stop().empty().prop( 'class', 'tsf-ajax' ).css( 'opacity', '1' ).removeProp( 'style' );
	},

	/**
	 * Enables wpColorPicker on input.
	 *
	 * @since 2.8.0
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	setColorOnload: function() {

		let $selectors = jQuery( '.tsf-color-picker' );

		if ( $selectors.length ) {
			jQuery.each( $selectors, function( index, value ) {
				let $input = jQuery( value ),
					currentColor = '',
					defaultColor = $input.data( 'tsf-default-color' );

				$input.wpColorPicker( {
					'defaultColor' : defaultColor,
					'width' : 238,
					'change' : function( event, ui ) {
						currentColor = $input.wpColorPicker( 'color' );

						if ( '' === currentColor )
							currentColor = defaultColor;

						$input.val( currentColor );

						tsf.registerChange();
					},
					'clear' : function() {
						//* Privately marked WP class... open ticket?
						$input.parent().siblings( '.wp-color-result' ).css( 'backgroundColor', defaultColor );

						tsf.registerChange();
					},
					'palettes' : false,
				} );
			} );
		}
	},

	/**
	 * Registers on resize/orientationchange listeners and debounces to only run
	 * at intervals.
	 *
	 * For Flexbox implementation.
	 *
	 * @since 2.9.0
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	_doFlexResizeListener: function() {

		if ( ! jQuery( '.tsf-flex' ).length )
			return;

		//* Set event listeners.
		tsf._setResizeListeners();

		let resizeTimeout = 0,
			$lastWidth = {},
			timeOut = 0;

		// Warning: Only checks for the first item existence.
		let $tabWrapper = jQuery( '.tsf-flex-nav-tab-wrapper' ),
			$window = jQuery( window );

		$window.on( 'tsf-flex-resize', function() {

			clearTimeout( resizeTimeout );

			// Onload delays are 0, after than it's 10, 20 and 30 respectively.
			let _delay = 0;

			resizeTimeout = setTimeout( function() {
				if ( $tabWrapper.length ) {
					// Flex Tab Wrapper.
					let $innerWrap = jQuery( '.tsf-flex-nav-tab-inner' ),
						outerWrapWidth = $tabWrapper.width(),
						innerWrapWidth = $innerWrap.width(),
						$navName = jQuery( '.tsf-flex-nav-name' );

					if ( ! $lastWidth.tabWrapper ) {
						$lastWidth.tabWrapper = {};
						$lastWidth.tabWrapper.outer = 0;
						$lastWidth.tabWrapper.inner = 0;
						$lastWidth.tabWrapper.shown = 1;
					}

					// First run, revealed, or testing for new width. Either way, fadeIn.
					if ( ! $lastWidth.tabWrapper.shown && $lastWidth.tabWrapper.outer < outerWrapWidth ) {
						/**
						 * If ANYONE can find a way that doesn't make it flicker
						 * without using clones with stripped IDs/names, let me know.
						 * https://github.com/sybrew/the-seo-framework/issues/new
						 * https://github.com/sybrew/the-seo-framework/compare
						 */
						$navName.fadeIn( 250 );

						// Wait for 10 ms for slow browsers.
						setTimeout( function() {
							// Recalulate inner width (outer didn't change):
							innerWrapWidth = $innerWrap.width();
						}, _delay );
					}

					// Wait for an additional 10 ms for slow browsers.
					setTimeout( function() {
						if ( innerWrapWidth > outerWrapWidth ) {
							// Overflow (can be first run).
							$navName.hide();
							$lastWidth.tabWrapper.shown = 0;
						} else if ( $lastWidth.tabWrapper.outer < outerWrapWidth ) {
							// Grown or first run.
							$navName.fadeIn( 250 );
							$lastWidth.tabWrapper.shown = 1;
						}
					}, _delay * 2 );

					// Wait for an additional 10 ms for slow browsers.
					setTimeout( function() {
						$lastWidth.tabWrapper.outer = outerWrapWidth;
						$lastWidth.tabWrapper.inner = innerWrapWidth;
					}, _delay * 3 );
				}
			}, timeOut );

			// Update future timeouts.
			_delay = 10;
			timeOut = 75;
		} );

		//* Trigger after setup.
		$window.trigger( 'tsf-flex-resize' );
	},

	/**
	 * Sets flex resize listeners.
	 *
	 * @since 2.9.0
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	_setResizeListeners: function() {
		jQuery( window ).on( 'resize orientationchange', tsf._triggerResize );
		jQuery( '#collapse-menu' ).click( tsf._triggerResize );
		jQuery( '.columns-prefs :input[type=radio]' ).change( tsf._triggerResize );
		jQuery( '.meta-box-sortables' ).on( 'sortupdate', tsf._triggerResize );
	},

	/**
	 * Triggers resize on event.
	 *
	 * @since 2.9.0
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	_triggerResize: function() {
		jQuery( window ).trigger( 'tsf-flex-resize' );
	},

	/**
	 * Triggers counter update event.
	 *
	 * @since 3.0.0
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	_triggerCounterUpdate: function() {
		jQuery( window ).trigger( 'tsf-counter-updated' );
	},

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
	_triggerReady: function() {
		jQuery( document.body ).trigger( 'tsf-ready' );
	},

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
	_triggerOnLoad: function() {
		jQuery( document.body ).trigger( 'tsf-onload' );
	},

	/**
	 * Runs document-on-ready actions.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @function
	 */
	_doReady: function() {

		// Triggers tsf-onload event.
		tsf._triggerOnLoad();

		// Add counter listeners.
		tsf._initCounters();

		// Add title prop listeners. Must load before setTabsOnload to work.
		tsf._initTitleInputs();
		tsf._initUnboundTitleSettings();
		tsf._initSocialTitleInputs();

		// Add description prop listeners. Must load before setTabsOnload to work.
		tsf._initDescInputs();
		tsf._initSocialDescInputs();

		// Sets tabs to correct radio button on load.
		tsf.setTabsOnload();

		// Check if the Title Tagline or Description Additions should be removed when page is loaded.
		tsf.taglineToggleOnload();

		// Correct Color Picker input
		tsf.setColorOnload();

		// #== End Before Change listener

		// Initialise form field changing flag.
		tsf.attachUnsavedChangesListener();

		// Deregister changes.
		tsf.onLoadUnregisterChange();

		// Do flex resize functionality.
		tsf._doFlexResizeListener();

		// Trigger tsf-ready event.
		tsf._triggerReady();

		// #== Start After Change listener
	},

	/**
	 * Sets up object parameters.
	 *
	 * @since 2.8.0
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	setupVars: function() {

		// The counter type. Mixed string and int (i10n is string, JS is int).
		tsf.counterType = parseInt( tsf.states['counterType'] );

		// Determines if the current page has input boxes for The SEO Framework.
		tsf.hasInput = tsf.states['hasInput'];
	},

	/**
	 * Initialises all aspects of the scripts.
	 *
	 * Generally ordered with stuff that inserts new elements into the DOM first,
	 * then stuff that triggers an event on existing DOM elements when ready,
	 * followed by stuff that triggers an event only on user interaction. This
	 * keeps any screen jumping from occuring later on.
	 *
	 * @since 2.2.4
	 * @since 2.7.0 jQuery object is now passed.
	 *
	 * @function
	 * @param {!jQuery} $ jQuery
	 * @return {undefined}
	 */
	ready: function( $ ) {

		// Set up object parameters.
		tsf.setupVars();

		// Move the page updates notices below the tsf-top-wrap.
		$( 'div.updated, div.error, div.notice-warning' ).insertAfter( 'div.tsf-top-wrap' );

		$( document.body ).ready( tsf._doReady );

		// Bind reset confirmation.
		$( '.tsf-js-confirm-reset' ).on( 'click', tsf.confirmedReset );

		// Toggle Tabs in the SEO settings page.
		$( '.tsf-tabs-radio' ).on( 'change', tsf.tabToggle );

		// Toggle Tabs for the inpost Flex settings.
		$( '.tsf-flex-nav-tab-radio' ).on( 'change', tsf.flexTabToggle );

		// Toggle Description additions removal.
		$( '#tsf-description-onblogname-toggle :input' ).on( 'click', tsf.taglineToggleDesc );
		$( '#tsf-description-additions-toggle :input' ).on( 'click', tsf.additionsToggleDesc );

		// Dismiss notices.
		$( '.tsf-dismiss' ).on( 'click', tsf.dismissNotice );
	}
};
jQuery( tsf.ready );
