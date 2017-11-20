/**
 * This file holds The SEO Framework plugin's JS code.
 * Serve JavaScript as an addition, not as a means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link https://wordpress.org/plugins/autodescription/
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
	 * Other parameters object.
	 *
	 * @since 2.8.0
	 *
	 * @const
	 * @type {Object<string, ?>} other Localized strings|parameters|states
	 */
	other : tsfL10n.other,

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
	 * Image cropper instance.
	 *
	 * @since 2.8.0
	 *
	 * @type {!Object} cropper
	 */
	cropper : {},

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

		var map = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&#039;'
		};

		return str.replace( /[&<>"']/g, function( m ) {
			return map[m];
		} );
	},

	/**
	 * Gets string length.
	 * We do not trim in JavaScript; that should be self-taught by the user.
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
	 * Updates pixel counter.
	 *
	 * @function
	 * @param {Object} test
	 * @return {undefined}
	 */
	updatePixelCounter: function( test ) {

		let e = test.e,
			text = test.text,
			guideline = test.guideline;

		let wrap = e.parentElement;

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

		bar.classList.remove( fitClass, overflownClass )
		bar.classList.add( newClass );
		bar.dataset.desc = label;
		bar.setAttribute( 'aria-label', label );

		sub.style.width = newWidth;

		tsf._triggerTooltipUpdate( bar );
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
			defaultTitle = tsf.params.objectTitle;

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

			let outerWidth = $input.outerWidth(),
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

			if ( hasPrefixValue ) {
				$prefixElement.css( fontStyleCSS );
				$prefixElement.css( { 'maxWidth' : 'initial' } );
				prefixMaxWidth = $prefixElement.width();
				if ( prefixMaxWidth < 0 )
					prefixMaxWidth = 0;
				prefixOffset += leftOffset;
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
					textWidth = $offsetTest.width();
				})();

				//= Input element width - Padding - input text width - prefix value width.
				additionsMaxWidth = $input.width() - horizontalPadding - textWidth - prefixMaxWidth;
				if ( additionsMaxWidth < 0 ) {
					prefixMaxWidth -=- additionsMaxWidth;
					additionsMaxWidth = 0;
				}

				$additionsElement.css( fontStyleCSS );

				switch ( hoverAdditionsPlacement ) {
					case 'before' :
						let additionsWidth = $additionsElement.width();

						if ( additionsWidth < 0 )
							additionsWidth = 0;

						totalIndent += additionsWidth;
						prefixOffset += additionsWidth;
						additionsOffset += leftOffset;
						break;

					case 'after' :
						additionsOffset += leftOffset + textWidth + prefixMaxWidth;
						break;
				}
			}
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
		const updateCounter = function( event ) {
			let counter = document.getElementById( event.target.id + '_chars' );

			if ( ! counter )
				return;

			let titLen = 0,
				target = event.target,
				counterClass = '',
				counterType = tsf.counterType,
				counterName = '',
				output = '';

			if ( target.value.length < 1 ) {
				titLen = tsf.getStringLength( target.placeholder );
			} else {
				titLen = ( hoverPrefixValue ? tsf.getStringLength( hoverPrefixValue ) : 0 )
				       + tsf.getStringLength( target.value )
				       + ( hoverAdditionsValue ? tsf.getStringLength( hoverAdditionsValue ) : 0 );
			}

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
			let pixels = document.getElementById( event.target.id + '_pixels' );

			if ( ! pixels )
				return;

			let text = '';

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

			let test = {
				'e': pixels,
				'text' : text,
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
		const triggerUnregisteredTitleChange = function() {
			let settingsChangedCache = tsf.settingsChanged;
			triggerInput();
			tsf.settingsChanged = settingsChangedCache;
		}
		jQuery( '#autodescription-homepage-settings > button, #tsf-inpost-box > button' ).on( 'click', triggerUnregisteredTitleChange );
		jQuery( '#homepage-tab-general' ).on( 'tsf-tab-toggled', triggerUnregisteredTitleChange );
		jQuery( '#tsf-flex-inpost-tab-general' ).on( 'tsf-flex-tab-toggled', triggerUnregisteredTitleChange );

		let unregisteredTriggerBuffer = 0;
		/**
		 * Triggers doctitles input.
		 * @function
		 * @return {undefined}
		 */
		const enqueueUnregisteredTriggerInput = function() {
			clearTimeout( unregisteredTriggerBuffer );
			unregisteredTriggerBuffer = setTimeout( function() {
				triggerUnregisteredTitleChange();
			}, 50 );
		}
		jQuery( window ).on( 'tsf-flex-resize', enqueueUnregisteredTriggerInput );

		/**
		 * Triggers additions hover update on tagline placement change.
		 *
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
				defaultTitle = val;
			} else {
				defaultTitle = tsf.params.untitledTitle;
			}
			updatePlaceholder();
			triggerCounter();
		}
		//= The home page listens to a static preset value.
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
							triggerUnregisteredTitleChange();
						}
					} else {
						if ( prevWidth >= 782 && width <= 782 ) {
							triggerUnregisteredTitleChange();
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
				$title.text( tsf.params.objectTitle );
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
		jQuery( '#autodescription-site-settings\\[homepage_title_tagline\\]' ).on( 'input', adjustHomepageTaglineExampleOutput );
		jQuery( '#autodescription-site-settings\\[homepage_title_tagline\\]' ).trigger( 'input' );

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

			if ( descLen < 100 || descLen >= 175 ) {
				counterClass += 'tsf-count-bad';
				counterName = tsf.i18n.bad;
			} else if ( descLen < 137 || descLen > 155 ) {
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
		const updatePixels = function( event ) {
			let element = document.getElementById( event.target.id + '_pixels' );

			if ( ! element )
				return;

			let text = '';

			if ( event.target.value.length < 1 ) {
				text = event.target.placeholder;
			} else {
				text = event.target.value;
			}

			let test = {
				'e': element,
				'text' : text,
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

					/**
					 * @TODO convert to json header and/or test for availability of response.type before parsing?
					 * @see convertJSONResponse() @ https://github.com/sybrew/The-SEO-Framework-Extension-Manager/blob/master/lib/js/tsfem.js
					 * @see send_json() @ https://github.com/sybrew/The-SEO-Framework-Extension-Manager/blob/master/inc/classes/core.class.php
					 */
					response = jQuery.parseJSON( response );

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
	 * Initializes primary term selection.
	 *
	 * @since 3.0.0
	 *
	 * @function
	 * @return {undefined}
	 */
	_initPrimaryTerm: function() {

		if ( ! tsf.hasInput || ! Object.keys( tsf.states.taxonomies ).length )
			return;

		let taxonomies = tsf.states.taxonomies,
			inputTemplate = wp.template( 'tsf-primary-term-selector' ),
			helpTemplate = wp.template( 'tsf-primary-term-selector-help' );

		let termSelector = document.createElement( 'span' );
		termSelector.classList.add( 'tsf-primary-term-selector' );
		termSelector.classList.add( 'tsf-set-primary-term' ); // newline for IE11 compat.

		(function(){
			let radio = document.createElement( 'input' );
			radio.setAttribute( 'type', 'radio' );
			termSelector.appendChild( radio );
		})();

		let input$ = {},
			checked$ = {},
			uniqueChecked$ = {},
			box$ = {},
			primaries = {};

		const addInput = function( taxonomy ) {
			let $wrap = jQuery( '#' + taxonomy + 'div' ),
				template = inputTemplate( { 'taxonomy' : taxonomies[ taxonomy ] } );
			$wrap.append( template );
		}
		const addHelp = function( taxonomy ) {
			let $wrap = jQuery( '#taxonomy-' + taxonomy ),
				template = helpTemplate( { 'taxonomy' : taxonomies[ taxonomy ] } );
			$wrap.append( template );
			fixHelpPos( taxonomy );
		}
		const fixHelpPos = function( taxonomy ) {
			let wrap = document.getElementById( 'taxonomy-' + taxonomy ),
				tabs = wrap.querySelectorAll( '.tabs-panel' );

			let tab = Array.prototype.slice.call( tabs ).filter( function( Element ) {
				return Element.offsetWidth > 0 || Element.offsetHeight > 0 || Element.getClientRects().length > 0;
			} )[0];

			let offset = tab.scrollHeight > tab.clientHeight
			           ? tab.offsetWidth - tab.clientWidth + 25 - 2 // 2px is padding or something?
			           : 25;

			if ( tsf.states.isRTL ) {
				wrap.querySelector( '.tsf-primary-term-selector-help-wrap' ).style.left = offset + 'px';
			} else {
				wrap.querySelector( '.tsf-primary-term-selector-help-wrap' ).style.right = offset + 'px';
			}
		}
		const fixHelpPosOnTabToggle = function( event ) {
			fixHelpPos( event.data.taxonomy );
		}
		const createSelector = function( taxonomy ) {
			let selector = termSelector.cloneNode( true );
			selector.setAttribute( 'title', taxonomies[ taxonomy ].i18n.makePrimary );
			selector.setAttribute( 'aria-label', taxonomies[ taxonomy ].i18n.makePrimary );
			return selector;
		}
		const setPostValue = function( taxonomy, value ) {
			let element = document.getElementById( 'autodescription[_primary_term_' + taxonomy + ']' );
			if ( element && element instanceof Element )
				element.value = value;
		}

		const getBox = function( taxonomy, reset ) {
			if ( ! reset && box$[ taxonomy ] )
				return box$[ taxonomy ];

			box$[ taxonomy ] = jQuery( '#' + taxonomy + 'checklist, #' + taxonomy + 'checklist-pop' );
			return box$[ taxonomy ];
		}
		const getInputWithVal = function( taxonomy, value ) {
			return input$[ taxonomy ].filter( '[value="' + value + '"]' );
		}

		const makePrimary = function( taxonomy, value ) {
			let $label = getInputWithVal( taxonomy, value ).closest( 'label' );
			if ( $label.length ) {
				$label.addClass( 'tsf-is-primary-term' );
				$label.find( '.tsf-set-primary-term' ).each( function( index, e ) {
					e.setAttribute( 'title', taxonomies[ taxonomy ].i18n.primary );
					e.setAttribute( 'aria-label', taxonomies[ taxonomy ].i18n.primary );
					e.querySelector( 'input' ).checked = true;
				} );
				setPostValue( taxonomy, value );
				primaries[ taxonomy ] = value;
			} else {
				makeFirstPrimary( taxonomy );
			}
		}
		const unsetPrimaries = function( taxonomy ) {
			let $label = getBox( taxonomy ).find( 'label' );
			$label.removeClass( 'tsf-is-primary-term' );
			$label.find( '.tsf-set-primary-term' ).each( function( index, e ) {
				e.setAttribute( 'title', taxonomies[ taxonomy ].i18n.makePrimary );
				e.setAttribute( 'aria-label', taxonomies[ taxonomy ].i18n.makePrimary );
				e.querySelector( 'input' ).checked = false;
			} );
			setPostValue( taxonomy, '' );
		}
		const makeFirstPrimary = function( taxonomy ) {
			let $checked = uniqueChecked$[ taxonomy ].first(),
				value;
			if ( $checked.length ) {
				value = $checked.val() || '';
				makePrimary( taxonomy, value );
				primaries[ taxonomy ] = value;
			}
		}

		const setPrimary = function( event ) {
			let taxonomy = event.data.taxonomy,
				value = jQuery( event.target ).closest( 'label' ).find( 'input[type=checkbox]' ).val();

			unsetPrimaries( taxonomy );
			makePrimary( taxonomy, value );

			//= Stop propagation
			return false;
		}
		const toggleShowSwitch = function( event ) {
			let taxonomy = event.data.taxonomy;

			if ( event.target.checked ) {
				addCheckedNode( taxonomy, event.target );
				appendButton( taxonomy, event.target );
			} else {
				removeCheckedNode( taxonomy, event.target );
				removeButton( taxonomy, event.target );
			}

			switch ( uniqueChecked$[ taxonomy ].length ) {
				case 0 :
					setPostValue( taxonomy, '' );
					break;

				case 1 :
					makeFirstPrimary( taxonomy );
					break;
			}
		}
		const appendButton = function( taxonomy, element ) {
			let $label;
			getInputWithVal( taxonomy, element.value ).each( function( index, e ) {
				$label = jQuery( e ).closest( 'label' );
				if ( ! $label.find( '.tsf-primary-term-selector' ).length ) {
					$label.append( createSelector( taxonomy ) );
				}
			} );
		}
		const removeButton = function( taxonomy, element ) {
			let $label, wasPrimary;
			getInputWithVal( taxonomy, element.value ).each( function( index, e ) {
				$label = jQuery( e ).closest( 'label' );
				wasPrimary = $label.hasClass( 'tsf-is-primary-term' );
				$label.removeClass( 'tsf-is-primary-term' );
				$label.find( '.tsf-primary-term-selector' ).remove();
				if ( wasPrimary ) makeFirstPrimary( taxonomy );
			} );
		}
		const addCheckedNode = function( taxonomy, element ) {
			checked$[ taxonomy ] = checked$[ taxonomy ].add( '[value="' + element.value + '"]' );
			uniqueChecked$[ taxonomy ] = uniqueChecked$[ taxonomy ].add( element );
		}
		const removeCheckedNode = function( taxonomy, element ) {
			checked$[ taxonomy ] = checked$[ taxonomy ].not( '[value="' + element.value + '"]' );
			uniqueChecked$[ taxonomy ] = uniqueChecked$[ taxonomy ].not( '[value="' + element.value + '"]' );
		}

		const initVars = function( taxonomy ) {
			let $box = getBox( taxonomy, 1 );

			input$[ taxonomy ] = $box.find( 'input[type=checkbox]' );
			checked$[ taxonomy ] = $box.find( 'input[type=checkbox]:checked' );

			let found = {}, val;
			uniqueChecked$[ taxonomy ] = checked$[ taxonomy ];
			uniqueChecked$[ taxonomy ].each( function( index, element ) {
				val = jQuery( element ).val();
				if ( found[ val ] ) {
					uniqueChecked$[ taxonomy ].splice( index, 1 );
				} else {
					found[ val ] = true;
				}
			} );
		}
		const updateList = function( event, settings, wpList ) {
			if ( wpList.hasOwnProperty( 'settings' ) && wpList.settings.hasOwnProperty( 'what' ) ) {
				initVars( wpList.settings.what );
				initActions( wpList.settings.what );
				load( wpList.settings.what );
				fixHelpPos( wpList.settings.what );
			}
		}
		const initActions = function( taxonomy ) {
			let ns = 'tsfShowPrimary' + taxonomy,
				data = { 'taxonomy': taxonomy },
				$box = getBox( taxonomy ),
				$div = jQuery( '#' + taxonomy + 'div' ),
				$tabs = jQuery( '#' + taxonomy + '-tabs' );

			$box.off( 'click.' + ns );
			$box.on( 'click.' + ns, 'input[type="checkbox"]', data, toggleShowSwitch );
			$box.on( 'click.' + ns, '.tsf-primary-term-selector', data, setPrimary );

			$div.off( 'wpListAddEnd.' + ns );
			$div.on( 'wpListAddEnd.' + ns, '#' + taxonomy + 'checklist', updateList );

			$tabs.off( 'click.' + ns );
			$tabs.on( 'click.' + ns, 'a', data, fixHelpPosOnTabToggle );
		}
		const load = function( taxonomy ) {
			getBox( taxonomy ).find( 'input[type="checkbox"]:checked' )
				.each( function( index, element ) {
					appendButton( taxonomy, element );
				} );

			if ( taxonomies[ taxonomy ].primary ) {
				makePrimary( taxonomy, taxonomies[ taxonomy ].primary );
			} else {
				makeFirstPrimary( taxonomy );
			}
		}

		const init = function() {
			for ( let taxonomy in taxonomies ) {
				if ( getBox( taxonomy ).length ) {
					addInput( taxonomy );
					addHelp( taxonomy );
					initVars( taxonomy );
					initActions( taxonomy );
					load( taxonomy );
				}
			}
		}
		init();
	},

	/**
	 * Initializes status bar hover entries.
	 *
	 * @since 3.0.0
	 *
	 * @function
	 * @return {undefined}
	 */
	_initToolTips: function() {

		let touchBuffer = 0,
			inTouchBuffer = false;

		const setTouchBuffer = function() {
			inTouchBuffer = true;
			clearTimeout( touchBuffer );
			touchBuffer = setTimeout( function() {
				inTouchBuffer = false;
			}, 250 );
		}

		const setEvents = function( target, unset ) {

			unset = unset || false;

			let touchEvents = 'pointerdown.tsfTT touchstart.tsfTT click.tsfTT',
				$target = jQuery( target );

			if ( unset ) {
				$target.off( 'mousemove mouseleave mouseout tsf-tooltip-update' );
				jQuery( document.body ).off( touchEvents );
			} else {
				$target.on( {
					'mousemove'  : mouseMove,
					'mouseleave' : mouseLeave,
					'mouseout'   : mouseLeave,
				} );
				jQuery( document.body ).off( touchEvents ).on( touchEvents, touchRemove );
			}

			$target.on( 'tsf-tooltip-update', updateDesc );
		}
		const unsetEvents = function( target ) {
			setEvents( target, true );
		}
		const updateDesc = function( event ) {
			if ( event.target.classList.contains( 'tsf-tooltip-item' ) ) {
				let tooltipText = event.target.querySelector( '.tsf-tooltip-text' );
				if ( tooltipText instanceof Element )
					tooltipText.innerHTML = event.target.dataset.desc;
			}
		}
		const mouseEnter = function( event ) {
			let $hoverItem = jQuery( event.target ),
				desc = event.target.dataset.desc;

			if ( desc && 0 === $hoverItem.find( 'div' ).length ) {
				//= Remove any titles attached.
				event.target.title = "";

				let $tooltip = jQuery(
						'<div class="tsf-tooltip"><span class="tsf-tooltip-text-wrap"><span class="tsf-tooltip-text">'
							+ desc +
						'</span></span><div class="tsf-tooltip-arrow"></div></div>'
					);
				$hoverItem.append( $tooltip );

				let $boundary = $hoverItem.closest( '.tsf-tooltip-boundary' );
				$boundary = $boundary.length && $boundary || jQuery( document.body );

				//= 9 = arrow (8) + shadow (1)
				let tooltipHeight = $hoverItem.outerHeight() + 9,
					tooltipTop = $tooltip.offset().top - tooltipHeight,
					boundaryTop = $boundary.offset().top - ( $boundary.prop( 'scrolltop' ) || 0 );

				if ( boundaryTop > tooltipTop ) {
					$tooltip.addClass( 'tsf-tooltip-down' );
					$tooltip.css( 'top', tooltipHeight + 'px' );
				} else {
					$tooltip.css( 'bottom', tooltipHeight + 'px' );
				}

				let $hoverItemWrap = $hoverItem.closest( '.tsf-tooltip-wrap' ),
					$textWrap = $tooltip.find( '.tsf-tooltip-text-wrap' ),
					$innerText = $textWrap.find( '.tsf-tooltip-text' ),
					hoverItemWrapWidth = $hoverItemWrap.width(),
					textWrapWidth = $textWrap.outerWidth( true ),
					textWidth = $innerText.outerWidth( true ),
					textLeft = $textWrap.offset().left,
					textRight = textLeft + textWidth,
					boundaryLeft = $boundary.offset().left - ( $boundary.prop( 'scrollLeft' ) || 0 ),
					boundaryRight = boundaryLeft + $boundary.outerWidth();

				//= RTL and LTR are normalized to abide to left.
				let direction = 'left';

				if ( textLeft < boundaryLeft ) {
					//= Overflown over left boundary (likely window)
					//= Add indent relative to boundary. 24px width of arrow / 2 = 12 middle
					let horIndent = boundaryLeft - textLeft + 12,
						basis = parseInt( $textWrap.css( 'flex-basis' ), 10 );

					/**
					 * If the overflow is greater than the tooltip flex basis,
					 * the tooltip was grown. Shrink it back to basis and use that.
					 */
					if ( horIndent < -basis )
						horIndent = -basis;

					$tooltip.css( direction, horIndent + 'px' );
					$tooltip.data( 'overflow', horIndent );
					$tooltip.data( 'overflowDir', direction );
				} else if ( textRight > boundaryRight ) {
					//= Overflown over right boundary (likely window)
					//= Add indent relative to boundary. Add 12px for visual appeal.
					let horIndent = boundaryRight - textRight - hoverItemWrapWidth - 12,
						basis = parseInt( $textWrap.css( 'flex-basis' ), 10 );

					/**
					 * If the overflow is greater than the tooltip flex basis,
					 * the tooltip was grown. Shrink it back to basis and use that.
					 */
					if ( horIndent < -basis )
						horIndent = -basis;

					$tooltip.css( direction, horIndent + 'px' );
					$tooltip.data( 'overflow', horIndent );
					$tooltip.data( 'overflowDir', direction );
				} else if ( hoverItemWrapWidth < 42 ) {
					//= Small tooltip container. Add indent to make it visually appealing.
					let indent = -15;
					$tooltip.css( direction, indent + 'px' );
					$tooltip.data( 'overflow', indent );
					$tooltip.data( 'overflowDir', direction );
				} else if ( hoverItemWrapWidth > textWrapWidth ) {
					//= Wrap is bigger than tooltip. Adjust accordingly.
					let pagex = event.originalEvent && event.originalEvent.pageX || event.pageX, // iOS touch support,
						hoverItemLeft = $hoverItemWrap.offset().left,
						center = pagex - hoverItemLeft,
						left = center - textWrapWidth / 2,
						right = left + textWrapWidth;

					if ( left < 0 ) {
						//= Don't overflow left.
						left = 0;
					} else if ( right > hoverItemWrapWidth ) {
						//= Don't overflow right.
						//* Use textWidth instead of textWrapWidth as it gets squashed in flex.
						left = hoverItemWrapWidth - textWidth;
					}

					$tooltip.css( direction, left + 'px' );
					$tooltip.data( 'adjust', left );
					$tooltip.data( 'adjustDir', direction );
				}
			}
		}
		const mouseMove = function( event ) {
			let $target = jQuery( event.target ),
				$tooltip = $target.find( '.tsf-tooltip' ),
				$arrow = $tooltip.find( '.tsf-tooltip-arrow' ),
				overflow = $tooltip.data( 'overflow' ),
				overflowDir = $tooltip.data( 'overflowDir' );

			overflow = parseInt( overflow, 10 );
			overflow = isNaN( overflow ) ? 0 : - Math.round( overflow );

			if ( overflow ) {
				//= Static arrow based on static overflow.
				$arrow.css( overflowDir, overflow + "px" );
			} else {
				let pagex = event.originalEvent && event.originalEvent.pageX || event.pageX, // iOS touch support
					arrowBoundary = 7,
					arrowWidth = 16,
					$hoverItemWrap = $target.closest( '.tsf-tooltip-wrap' ),
					mousex = pagex - $hoverItemWrap.offset().left - arrowWidth / 2,
					originalMousex = mousex,
					$textWrap = $tooltip.find( '.tsf-tooltip-text-wrap' ),
					textWrapWidth = $textWrap.outerWidth( true ),
					adjust = $tooltip.data( 'adjust' ),
					adjustDir = $tooltip.data( 'adjustDir' ),
					boundaryRight = textWrapWidth - arrowWidth - arrowBoundary;

				//= mousex is skewed, adjust.
				adjust = parseInt( adjust, 10 );
				adjust = isNaN( adjust ) ? 0 : Math.round( adjust );
				if ( adjust ) {
					adjust = 'left' === adjustDir ? -adjust : adjust;
					mousex = mousex + adjust;

					//= Use textWidth for right boundary if adjustment exceeds.
					if ( boundaryRight - adjust > $hoverItemWrap.outerWidth( true ) ) {
						let $innerText = $textWrap.find( '.tsf-tooltip-text' ),
							textWidth = $innerText.outerWidth( true );
						boundaryRight = textWidth - arrowWidth - arrowBoundary;
					}
				}

				if ( mousex <= arrowBoundary ) {
					//* Overflown left.
					$arrow.css( 'left', arrowBoundary + "px" );
				} else if ( mousex >= boundaryRight ) {
					//* Overflown right.
					$arrow.css( 'left', boundaryRight + "px" );
				} else {
					//= Somewhere in the middle.
					$arrow.css( 'left', mousex + "px" );
				}
			}
		}
		const mouseLeave = function( event ) {

			//* @see touchMove
			if ( inTouchBuffer )
				return;

			jQuery( event.target ).find( '.tsf-tooltip' ).remove();
			unsetEvents( event.target );
		}
		/**
		 * ^^^
		 * These two methods conflict eachother in EdgeHTML.
		 * Thusly, touch buffer.
		 * vvv
		 */
		const touchRemove = function( event ) {

			//* @see mouseLeave
			setTouchBuffer();

			let itemSelector = '.tsf-tooltip-item',
				balloonSelector = '.tsf-tooltip';

			let $target = jQuery( event.target ),
				$keepBalloon;

			if ( $target.hasClass( 'tsf-tooltip-item' ) ) {
				$keepBalloon = $target.find( balloonSelector );
			}
			if ( ! $keepBalloon ) {
				let $children = $target.children( itemSelector );
				if ( $children.length ) {
					$keepBalloon = $children.find( balloonSelector );
				}
			}

			if ( $keepBalloon && $keepBalloon.length ) {
				//= Remove all but this.
				jQuery( balloonSelector ).not( $keepBalloon ).remove();
			} else {
				//= Remove all.
				jQuery( balloonSelector ).remove();
			}
		}

		/**
		 * Loads tooltips within wrapper.
		 * @function
		 */
		const loadToolTip = function( event ) {

			if ( inTouchBuffer )
				return;

			let isTouch = false;

			switch ( event.type ) {
				case 'mouseenter' :
					//= Most likely, thus placed first.
					break;

				case 'pointerdown' :
				case 'touchstart' :
					isTouch = true;
					break;

				default :
					break;
			}

			if ( event.target.classList.contains( 'tsf-tooltip-item' ) ) {
				//= Removes previous items and sets buffer.
				isTouch && touchRemove( event );

				mouseEnter( event );
				//= Initiate placement directly for Windows Touch or when overflown.
				mouseMove( event );

				setEvents( event.target );
			} else {
				//= Delegate or bubble, and go back to this method with the correct item.
				let item = event.target.querySelector( '.tsf-tooltip-item:hover' ),
					_event = new jQuery.Event( event.type );

				_event.pageX = event.originalEvent && event.originalEvent.pageX || event.pageX;

				if ( item ) {
					if ( tsfL10n.states.debug ) console.log( 'Tooltip event warning: delegation' );
					jQuery( item ).trigger( _event );
				} else {
					if ( tsfL10n.states.debug ) console.log( 'Tooltip event warning: bubbling' );
					jQuery( event.target ).closest( '.tsf-tooltip-wrap' ).find( '.tsf-tooltip-item:hover' ).trigger( _event );
				}
			}

			//* Stop further propagation.
			event.stopPropagation();
		}

		/**
		 * Initializes SEO Bar tooltips.
		 * @function
		 */
		const initTooltips = function() {
			let $wrap = jQuery( '.tsf-tooltip-wrap' );

			$wrap.off( 'mouseenter pointerdown touchstart' );
			$wrap.on( 'mouseenter pointerdown touchstart', '.tsf-tooltip-item', loadToolTip );
		}
		initTooltips();
		jQuery( window ).on( 'tsf-reset-tooltips', initTooltips );

		(function() {
			let e = jQuery( '#wpcontent' );
			tsf.addTooltipBoundary( e );
		})();
	},

	/**
	 * Adds tooltip boundaries.
	 *
	 * @since 3.0.0
	 *
	 * @function
	 * @param {!jQuery|Element} e
	 * @return {undefined}
	 */
	addTooltipBoundary: function( e ) {
		jQuery( e ).addClass( 'tsf-tooltip-boundary' );
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
	flexTabToggle : function( event ) {

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
	 * @see tsf.tabToggle
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	setTabsOnload: function() {

		if ( ! tsf.hasInput )
			return;

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
	 * Opens the image editor on request.
	 *
	 * @since 2.8.0
	 *
	 * @function
	 * @param {!jQuery.Event} event jQuery event
	 * @return {(undefined|null)}
	 */
	openImageEditor: function( event ) {

		if ( jQuery( event.target ).prop( 'disabled' ) || 'undefined' === typeof wp.media ) {
			//* TODO error handling?
			event.preventDefault();
			event.stopPropagation();
			return;
		}

		let $target = jQuery( event.target ),
			inputID = $target.data( 'inputid' ),
			frame;

		if ( frame ) {
			frame.open();
			return;
		}

		event.preventDefault();
		event.stopPropagation();

		//* Init extend cropper.
		tsf.extendCropper();

		let _states = {
			suggestedWidth: $target.data( 'width' ) || 1200,
			suggestedHeight: $target.data( 'height' ) || 630,
			isFlex: typeof $target.data( 'flex' ) !== 'undefined' ? $target.data( 'flex' ) : 1,
		};

		tsf.cropper.control = {
			'params' : {
				'flex_width' : _states.isFlex ? 4096 : 0,
				'flex_height' : _states.isFlex ? 4096 : 0,
				'width' : _states.suggestedWidth,
				'height' : _states.suggestedHeight,
				'isFlex' : _states.isFlex,
			},
		};

		frame = wp.media( {
			button : {
				'text' : tsf.other[ inputID ]['frame_button'],
				'close' : false,
			},
			states: [
				new wp.media.controller.Library( {
					'title' : tsf.other[ inputID ]['frame_title'],
					'library' : wp.media.query({ 'type' : 'image' }),
					'multiple' : false,
					'date' : false,
					'priority' : 20,
					'suggestedWidth' : _states.suggestedWidth,
					'suggestedHeight' : _states.suggestedHeight
				} ),
				new tsf.cropper( {
					'imgSelectOptions' : tsf.calculateImageSelectOptions,
				} ),
			],
		} );

		const onSelect = (function() {
			frame.setState( 'cropper' );
		} );
		frame.off( 'select', onSelect );
		frame.on( 'select', onSelect );

		const onCropped = function( croppedImage ) {
			let url = croppedImage.url,
				attachmentId = croppedImage.id,
				w = croppedImage.width,
				h = croppedImage.height;

			// Send the attachment id to our hidden input. URL to explicit output.
			jQuery( '#' + inputID + '-url' ).val( url );
			jQuery( '#' + inputID + '-id' ).val( attachmentId );
		};
		frame.off( 'cropped', onCropped );
		frame.on( 'cropped', onCropped );

		const onSkippedCrop = function( selection ) {
			let url = selection.get( 'url' ),
				attachmentId = selection.get( 'id' ),
				w = selection.get( 'width' ),
				h = selection.get( 'height' );

			// Send the attachment id to our hidden input. URL to explicit output.
			jQuery( '#' + inputID + '-url' ).val( url );
			jQuery( '#' + inputID + '-id' ).val( attachmentId );
		};
		frame.off( 'skippedcrop', onSkippedCrop );
		frame.on( 'skippedcrop', onSkippedCrop );

		const onDone = function( imageSelection ) {
			jQuery( '#' + inputID + '-select' ).text( tsf.other[ inputID ]['change'] );
			jQuery( '#' + inputID + '-url' ).prop( 'readonly', true ).css( 'opacity', 0 ).animate(
				{ 'opacity' : 1 },
				{ 'queue' : true, 'duration' : 1000 },
				'swing'
			);

			tsf.appendRemoveButton( $target, inputID, true );
			tsf.registerChange();
		};
		frame.off( 'skippedcrop cropped', onDone );
		frame.on( 'skippedcrop cropped', onDone );

		frame.open();
	},

	/**
	 * Removes the image editor image on request.
	 *
	 * @since 2.8.0
	 *
	 * @function
	 * @param {!jQuery.event.target} target jQuery event.target
	 * @param {string} inputID The input ID.
	 * @return {(undefined|null)}
	 */
	appendRemoveButton: function( target, inputID, animate ) {

		if ( target && inputID ) {
			if ( ! jQuery( '#' + inputID + '-remove' ).length ) {
				target.after(
					'<a href="javascript:void(0)" id="'
						+ inputID + '-remove" class="tsf-remove-social-image button button-small" data-inputid="'
						+ inputID +
					'" title="' + tsf.other[ inputID ]['remove_title'] + '">' + tsf.other[ inputID ]['remove'] + '</a>'
				);
				if ( animate ) {
					jQuery( '#' + inputID + '-remove' ).css( 'opacity', 0 ).animate(
						{ 'opacity' : 1 },
						{ 'queue' : true, 'duration' : 1000 },
						'swing'
					);
				}
			}
		}

		//* Reset cache.
		tsf.resetImageEditorActions();
	},

	/**
	 * Removes the image editor image on request.
	 *
	 * @since 2.8.0
	 *
	 * @function
	 * @param {!jQuery.Event} event jQuery event
	 * @return {(undefined|null)}
	 */
	removeEditorImage: function( event ) {

		let inputID = jQuery( event.target ).data( 'inputid' );

		if ( jQuery( '#' + inputID + '-select' ).prop( 'disabled' ) )
			return;

		jQuery( '#' + inputID + '-select' ).addClass( 'disabled' ).prop( 'disabled', true );

		//* event.target.id === '#' + inputID + '-remove'.
		jQuery( '#' + inputID + '-remove' ).addClass( 'disabled' ).prop( 'disabled', true ).fadeOut( 500, function() {
			jQuery( this ).remove();
			jQuery( '#' + inputID + '-select' ).text( tsf.other[ inputID ]['select'] ).removeClass( 'disabled' ).removeProp( 'disabled' );
		} );

		let $inputUrl = jQuery( '#' + inputID + '-url' );

		$inputUrl.val( '' );
		if ( ! $inputUrl.data( 'readonly' ) ) {
			$inputUrl.removeProp( 'readonly' );
		}
		$inputUrl.css( 'opacity', 0 ).animate(
			{ 'opacity' : 1 },
			{ 'queue' : true, 'duration' : 500 },
			'swing'
		);

		jQuery( '#' + inputID + '-id' ).val( '' );

		tsf.registerChange();
	},

	/**
	 * Builds constructor for media cropper.
	 *
	 * @since 2.8.0
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	extendCropper: function() {

		if ( 'undefined' !== typeof tsf.cropper.control )
			return;

		/**
		 * wp.media.controller.Cropper augmentation.
		 *
		 * A state for cropping an image.
		 *
		 * @class
		 * @augments wp.media.controller.Cropper
		 * @augments wp.media.controller.State
		 * @augments Backbone.Model
		 */
		let TSFCropper,
			Controller = wp.media.controller;

		/**
		 * wp.media.view.Cropper augmentation.
		 *
		 * @class
		 * @augments wp.media.View
		 * @augments wp.Backbone.View
		 * @augments Backbone.View
		 */
		let TSFView,
			View = wp.media.view;

		TSFView = View.Cropper.extend( {
			className: 'crop-content tsf-image',
			ready: function () {
				View.Cropper.prototype.ready.apply( this, arguments );
			},
			onImageLoad: function() {
				let imgOptions = this.controller.get( 'imgSelectOptions' ),
					imgSelect;

				if ( typeof imgOptions === 'function' ) {
					imgOptions = imgOptions( this.options.attachment, this.controller );
				}

				//= Seriously Core team, was this condition too hard to implement?
				if ( 'undefined' === typeof imgOptions.aspectRatio ) {
					imgOptions = _.extend( imgOptions, {
						parent: this.$el,
						onInit: function() {
							this.parent.children().on( 'mousedown touchstart', function( e ) {
								if ( e.shiftKey ) {
									imgSelect.setOptions( {
										aspectRatio: '1:1'
									} );
								} else {
									imgSelect.setOptions( {
										aspectRatio: false
									} );
								}
							} );
						}
					} );
				}
				this.trigger( 'image-loaded' );
				imgSelect = this.controller.imgSelect = this.$image.imgAreaSelect( imgOptions );
			},
		} );

		TSFCropper = Controller.Cropper.extend( {
			createCropContent: function() {
				this.cropperView = new TSFView( {
					controller: this,
					attachment: this.get( 'selection' ).first()
				} );
				this.cropperView.on( 'image-loaded', this.createCropToolbar, this );
				this.frame.content.set( this.cropperView );
			},
			doCrop: function( attachment ) {
				let cropDetails = attachment.get( 'cropDetails' ),
					control = tsf.cropper.control;

				// Use crop measurements when flexible in both directions.
				if ( control.params.flex_width && control.params.flex_height ) {
					// Square
					if ( cropDetails.width === cropDetails.height ) {
						if ( cropDetails.width > control.params.flex_width ) {
							cropDetails.dst_width = cropDetails.dst_height = control.params.flex_width;
						}
					// Landscape/Portrait
					} else {
						// Resize to flex width/height
						if ( cropDetails.width > control.params.flex_width || cropDetails.height > control.params.flex_height ) {
							// Landscape
							if ( cropDetails.width > cropDetails.height ) {
								let _ratio = cropDetails.width / control.params.flex_width;

								cropDetails.dst_width  = control.params.flex_width;
								cropDetails.dst_height = Math.round( cropDetails.height / _ratio );
							// Portrait
							} else {
								let _ratio = cropDetails.height / control.params.flex_height;

								cropDetails.dst_height = control.params.flex_height;
								cropDetails.dst_width  = Math.round( cropDetails.width / _ratio );
							}
						}
					}
				}

				// Nothing happened. Set destination to 0 and let PHP figure it out.
				if ( 'undefined' === typeof cropDetails.dst_width ) {
					cropDetails.dst_width  = 0;
					cropDetails.dst_height = 0;
				}

				return wp.ajax.post( 'tsf-crop-image', {
					'nonce' : tsf.nonces['upload_files'],
					'id' : attachment.get( 'id' ),
					'context' : 'tsf-image',
					'cropDetails' : cropDetails
				} );
			}
		} );

		TSFCropper.prototype.control = {};

		tsf.cropper = TSFCropper;

		return;
	},

	/**
	 * Returns a set of options, computed from the attached image data and
	 * control-specific data, to be fed to the imgAreaSelect plugin in
	 * wp.media.view.Cropper.
	 *
	 * @since 2.8.0
	 *
	 * @function
	 * @param {wp.media.model.Attachment} attachment
	 * @param {wp.media.controller.Cropper} controller
	 * @return {Object} imgSelectOptions
	 */
	calculateImageSelectOptions: function( attachment, controller ) {
		let control = tsf.cropper.control;

		let flexWidth  = !! parseInt( control.params.flex_width, 10 ),
			flexHeight = !! parseInt( control.params.flex_height, 10 ),
			xInit = parseInt( control.params.width, 10 ),
			yInit = parseInt( control.params.height, 10 );

		let realWidth  = attachment.get( 'width' ),
			realHeight = attachment.get( 'height' ),
			ratio = xInit / yInit,
			xImg  = xInit,
			yImg  = yInit,
			x1,
			y1,
			imgSelectOptions;

		let canSkipCrop;
		if ( control.params.isFlex ) {
			canSkipCrop = ! tsf.mustBeCropped( control.params.flex_width, control.params.flex_height, realWidth, realHeight );
		} else {
			//= Not flex. If ratios match, then we can skip.
			canSkipCrop = ratio === realWidth / realHeight;
		}

		controller.set( 'control', control.params );
		controller.set( 'canSkipCrop', canSkipCrop );

		if ( realWidth / realHeight > ratio ) {
			yInit = realHeight;
			xInit = yInit * ratio;
		} else {
			xInit = realWidth;
			yInit = xInit / ratio;
		}

		x1 = ( realWidth - xInit ) / 2;
		y1 = ( realHeight - yInit ) / 2;

		imgSelectOptions = {
			'handles' : true,
			'keys' : true,
			'instance' : true,
			'persistent' : true,
			'imageWidth' : realWidth,
			'imageHeight' : realHeight,
			'minWidth' : xImg > xInit ? xInit : xImg,
			'minHeight' : yImg > yInit ? yInit : yImg,
			'x1' : x1,
			'y1' : y1,
			'x2' : xInit + x1,
			'y2' : yInit + y1
		};

		// @TODO Convert set img min-width/height to output ratio.
		// i.e. 200x2000 will become x = 1500/2000*200 = 150px, which is too small.
		//= Unlikely...

		if ( ! control.params.isFlex ) {
			imgSelectOptions.handles = 'corners';
			imgSelectOptions.aspectRatio = xInit + ':' + yInit;
		} else if ( ! flexHeight && ! flexWidth ) {
			imgSelectOptions.aspectRatio = xInit + ':' + yInit;
		} else {
			if ( flexHeight ) {
				imgSelectOptions.minHeight = 200;
				imgSelectOptions.maxWidth = realWidth;
			}
			if ( flexWidth ) {
				imgSelectOptions.minWidth = 200;
				imgSelectOptions.maxHeight = realHeight;
			}
		}

		return imgSelectOptions;
	},

	/**
	 * Return whether the image must be cropped, based on required dimensions.
	 * Disregards flexWidth/Height.
	 *
	 * @since 2.8.0
	 *
	 * @function
	 * @param {Number} dstW
	 * @param {Number} dstH
	 * @param {Number} imgW
	 * @param {Number} imgH
	 * @return {Boolean}
	 */
	mustBeCropped: function( dstW, dstH, imgW, imgH ) {

		if ( imgW <= dstW && imgH <= dstH )
			return false;

		return true;
	},

	/**
	 * Resets jQuery image editor cache.
	 *
	 * @since 2.8.0
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	resetImageEditorActions: function() {
		jQuery( '.tsf-remove-social-image' ).off( 'click', tsf.removeEditorImage );
		jQuery( '.tsf-remove-social-image' ).on( 'click', tsf.removeEditorImage );
	},

	/**
	 * Sets up jQuery image editor cache.
	 *
	 * @since 2.8.0
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	setupImageEditorActions: function() {
		jQuery( '.tsf-set-social-image' ).off( 'click', tsf.openImageEditor );
		jQuery( '.tsf-remove-social-image' ).off( 'click', tsf.removeEditorImage );
		jQuery( '.tsf-set-social-image' ).on( 'click', tsf.openImageEditor );
		jQuery( '.tsf-remove-social-image' ).on( 'click', tsf.removeEditorImage );
	},

	/**
	 * Checks if input is filled in by image editor.
	 *
	 * @since 2.8.0
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	checkImageEditorInput: function() {

		let $buttons = jQuery( '.tsf-set-social-image' );

		if ( $buttons.length ) {
			let inputID = '',
				$valID = '';

			jQuery.each( $buttons, function( index, value ) {
				inputID = jQuery( value ).data( 'inputid' );
				$valID = jQuery( '#' + inputID + '-id' );

				if ( $valID.length && $valID.val() > 0 ) {
					jQuery( '#' + inputID + '-url' ).prop( 'readonly', true );
					tsf.appendRemoveButton( jQuery( value ), inputID, false );
				}

				if ( jQuery( '#' + inputID + '-url' ).val() ) {
					jQuery( '#' + inputID + '-select' ).text( tsf.other[ inputID ]['change'] );
				}
			} );
		}
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
	 * Triggers tooltip reset.
	 *
	 * @since 3.0.0
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	_triggerTooltipReset: function() {
		jQuery( window ).trigger( 'tsf-reset-tooltips' );
	},

	/**
	 * Triggers active tooltip update.
	 *
	 * @since 3.0.0
	 *
	 * @function
	 * @param {Element} item
	 * @return {(undefined|null)}
	 */
	_triggerTooltipUpdate: function( item ) {
		jQuery( item ).trigger( 'tsf-tooltip-update' );
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
	 * Sets tsf.ready action.
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
	 * Runs document-on-ready actions.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @function
	 */
	_doReady: function() {

		// Add counter listeners.
		tsf._initCounters();

		// Add title prop listeners. Must load before setTabsOnload to work.
		tsf._initTitleInputs();
		tsf._initUnboundTitleSettings();

		// Add description prop listeners. Must load before setTabsOnload to work.
		tsf._initDescInputs();

		// Set primary term listeners.
		tsf._initPrimaryTerm();

		// Sets tabs to correct radio button on load.
		tsf.setTabsOnload();

		// Check if the Title Tagline or Description Additions should be removed when page is loaded.
		tsf.taglineToggleOnload();

		// Initialize the status bar hover balloon.
		tsf._initToolTips();

		// Initialize image uploader button cache.
		tsf.setupImageEditorActions();

		// Determine image editor button input states.
		tsf.checkImageEditorInput();

		// Correct Color Picker input
		tsf.setColorOnload();

		// #== End Before Change listener

		// Initialise form field changing flag.
		tsf.attachUnsavedChangesListener();

		// Deregister changes.
		tsf.onLoadUnregisterChange();

		// Trigger tsf-ready event.
		tsf._triggerReady();

		// #== Start After Change listener

		// Do flex resize functionality.
		tsf._doFlexResizeListener();
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
