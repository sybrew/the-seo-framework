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
	 * @source <https://stackoverflow.com/a/4835406>
	 * @function
	 * @param {string} str
	 * @return {string}
	 */
	escapeSring: function( str ) {

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
			e.innerHTML = tsf.escapeSring( str );
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

		shadow.innerHTML = tsf.escapeSring( text );

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
					newValue = tsf.params.blogDescription;
			} else {
				//= Global additions are enabled.
				if ( useTagline )
					newValue = tsf.params.titleAdditions;
			}

			if ( newValue.length ) {
				newValue = tsf.escapeSring( newValue );
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
				newValue = tsf.escapeSring( newValue );
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
				prefixValueWidth = 0;

			if ( hasPrefixValue ) {
				$prefixElement.css( fontStyleCSS );
				prefixValueWidth = $prefixElement.width();
				totalIndent += prefixValueWidth;
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
				additionsMaxWidth = $input.width() - horizontalPadding - textWidth - prefixValueWidth;
				additionsMaxWidth = additionsMaxWidth < 0 ? 0 : additionsMaxWidth;

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
						additionsOffset += leftOffset + textWidth + prefixValueWidth;
						break;
				}
			}

			if ( hasPrefixValue ) {
				$prefixElement.css( {
					[offsetPosition]: prefixOffset + "px"
				} );
			}
			if ( hasAdditionsValue ) {
				$additionsElement.css( {
					[offsetPosition]: additionsOffset + "px",
					'maxWidth': additionsMaxWidth + "px"
				} );
			}

			$input.css( 'text-indent', totalIndent + "px" );
		}
		const updatePlaceholder = function() {

			let $input = $doctitles,
				_placeholder = '';

			let _hasAdditionsValue = !! hoverAdditionsValue.length,
				_hasPrefixValue = !! hoverPrefixValue.length;

			let _hoverAdditionsValue = hoverAdditionsValue,
				_hoverPrefixValue = hoverPrefixValue;

			//* @TODO this is hackish...?
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

			$input.prop( 'placeholder', _placeholder );
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
				counterName = tsf.getCounterName( 'bad' );
			} else if ( titLen < 42 || titLen > 55 ) {
				counterClass += 'tsf-count-okay';
				counterName = tsf.getCounterName( 'okay' );
			} else {
				counterClass += 'tsf-count-good';
				counterName = tsf.getCounterName( 'good' );
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
				'guideline' : 600
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
		const updateTitlesTrigger = function( event ) {
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

			//* @TODO move all of the above to a global state handler.
			//* @TODO update real placeholder too.
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
		 * Updates default title.
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
		jQuery( '#edittag #name' ).on( 'input', updateDefaultTitle );
		jQuery( '#titlewrap #title' ).on( 'input', updateDefaultTitle );

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
		const triggerUnregisteredInputOnResize = function() {
			let resizeTimeout = 0,
				prevWidth = window.innerWidth;
			jQuery( window ).resize( function() {

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
		triggerUnregisteredInputOnResize();
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
				counterName = tsf.getCounterName( 'bad' );
			} else if ( descLen < 137 || descLen > 155 ) {
				counterClass += 'tsf-count-okay';
				counterName = tsf.getCounterName( 'okay' );
			} else {
				counterClass += 'tsf-count-good';
				counterName = tsf.getCounterName( 'good' );
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
				'guideline' : 920
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
			let $item = jQuery( event.target ),
				desc = event.target.dataset.desc;

			if ( desc && 0 === $item.find( 'div' ).length ) {
				//= Remove any titles attached.
				event.target.title = "";

				let $tooltip = jQuery( '<div class="tsf-tooltip"><span class="tsf-tooltip-text">' + desc + '</span><div class="tsf-tooltip-arrow"></div></div>' );
				$item.append( $tooltip );

				//= 9 = arrow (8) + shadow (1)
				let height = $item.outerHeight() + 9;
				$tooltip.css( 'bottom', height + 'px' );

				let $wrap = $item.closest( '.tsf-tooltip-wrap' );
				//= Add 15 extra indent if the caller is very small to make it look more natural.
				if ( $wrap.width() < 42 ) {
					let indent = 15;

					//* This isn't needed, yet, as we always attach such tooltips at the end of sentences.
					// indentOverflown = tsf.states.isRTL ? wrapOffset < ( textOffset + indent ) : $item.offset().left > ( $tooltip.offset().left - indent );

					$tooltip.css( ( tsf.states.isRTL ? 'right' : 'left' ), - indent + 'px' );
				}
			}
		}
		const mouseMove = function( event ) {
			let $target = jQuery( event.target ),
				pagex = event.originalEvent && event.originalEvent.pageX || event.pageX, // iOS touch support
				mousex = pagex - $target.closest( '.tsf-tooltip-wrap' ).offset().left - 12, // 24px width of arrow / 2 = 12 middle
				$tooltip = $target.find( '.tsf-tooltip' ),
				$arrow = $tooltip.find( '.tsf-tooltip-arrow' ),
				indent = tsf.states.isRTL ? $tooltip.css( 'right' ) : $tooltip.css( 'left' );

			indent = parseInt( indent, 10 );
			//* 2 is shadow.
			indent = isNaN( indent ) ? 0 : - Math.floor( indent / 2 ) - 2;

			if ( mousex <= 8 ) {
				//* Overflown left.
				//= 6 is half of arrow overflow, 2 px shadow margin.
				$arrow.css( 'left', ( 8 + indent ) + "px" );
			} else if ( $tooltip.offset() !== void 0 ) {
				let $text = $tooltip.find( '.tsf-tooltip-text' ),
					width = $text.width() + indent,
					maxOffset = $tooltip.offset().left + width + 12;

				if ( pagex > maxOffset ) {
					$arrow.css( 'left', width + "px" );
				} else {
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

			//* Stop further delegation.
			return false;
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
	 * Updates the counter type.
	 *
	 * @since 2.6.0
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	counterUpdate: function( event ) {

		// Count up, reset to 0 if needed. We have 4 options: 0, 1, 2, 3
		tsf.counterType = tsf.counterType + 1;
		if ( tsf.counterType > 3 )
			tsf.counterType = 0;

		//* Update counters locally.
		tsf.updateCounterClasses();

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

				tsf.counterUpdatedResponse( target, status );
			},
		}

		jQuery.ajax( settings );
	},

	/**
	 * Visualizes the AJAX response to the user.
	 *
	 * @since 2.7.0
	 * @todo move this back up? In the function itself?
	 *
	 * @function
	 * @param {String} target
	 * @param {Number} success
	 * @return {undefined}
	 */
	counterUpdatedResponse: function( target, success ) {
		switch ( success ) {
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

	/**
	 * Sets up additionsClass variable.
	 * Also sets up browser caches correctly.
	 *
	 * @since 3.0.0
	 *
	 * @function
	 * @return {undefined}
	 */
	updateCounterClasses: function() {

		if ( ! tsf.hasInput )
			return;

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
	},

	/**
	 * Returns counter name.
	 *
	 * @since 2.6.0
	 *
	 * @function
	 * @param {String} type
	 * @return {String} name Human readable counter name.
	 */
	getCounterName: function( type ) {
		return tsf.i18n[ type ];
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

		var $target = jQuery( event.target ),
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
					'suggestedWidth' : 1200,
					'suggestedHeight' : 630
				} ),
				new tsf.cropper( {
					'imgSelectOptions' : tsf.calculateImageSelectOptions
				} ),
			],
		} );

		const selectFunc = (function() {
			frame.setState( 'cropper' );
		} );
		frame.off( 'select', selectFunc );
		frame.on( 'select', selectFunc );

		const croppedFunc = (function( croppedImage ) {
			let url = croppedImage.url,
				attachmentId = croppedImage.id,
				w = croppedImage.width,
				h = croppedImage.height;

			// Send the attachment id to our hidden input. URL to explicit output.
			jQuery( '#' + inputID + '-url' ).val( url );
			jQuery( '#' + inputID + '-id' ).val( attachmentId );
		} );
		frame.off( 'cropped', croppedFunc );
		frame.on( 'cropped', croppedFunc );

		const skippedcropFunc = (function( selection ) {
			let url = selection.get( 'url' ),
				attachmentId = selection.get( 'id' ),
				w = selection.get( 'width' ),
				h = selection.get( 'height' );

			// Send the attachment id to our hidden input. URL to explicit output.
			jQuery( '#' + inputID + '-url' ).val( url );
			jQuery( '#' + inputID + '-id' ).val( attachmentId );
		} );
		frame.off( 'skippedcrop', skippedcropFunc );
		frame.on( 'skippedcrop', skippedcropFunc );

		const doneFunc = (function( imageSelection ) {
			jQuery( '#' + inputID + '-select' ).text( tsf.other[ inputID ]['change'] );
			jQuery( '#' + inputID + '-url' ).prop( 'readonly', true ).css( 'opacity', 0 ).animate(
				{ 'opacity' : 1 },
				{ 'queue' : true, 'duration' : 1000 },
				'swing'
			);

			tsf.appendRemoveButton( $target, inputID, true );
			tsf.registerChange();
		} );
		frame.off( 'skippedcrop cropped', doneFunc );
		frame.on( 'skippedcrop cropped', doneFunc );

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

		var $target = jQuery( event.target ),
			inputID = $target.data( 'inputid' );

		if ( jQuery( '#' + inputID + '-select' ).prop( 'disabled' ) )
			return;

		jQuery( '#' + inputID + '-select' ).addClass( 'disabled' ).prop( 'disabled', true );

		//* target.event.id === '#' + inputID + '-remove'.
		jQuery( '#' + inputID + '-remove' ).addClass( 'disabled' ).prop( 'disabled', true ).fadeOut( 500, function() {
			jQuery( this ).remove();
			jQuery( '#' + inputID + '-select' ).text( tsf.other[ inputID ]['select'] ).removeClass( 'disabled' ).removeProp( 'disabled' );
		} );

		jQuery( '#' + inputID + '-url' ).val( '' ).removeProp( 'readonly' ).css( 'opacity', 0 ).animate(
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
		 * tsf.extendCropper => wp.media.controller.TSFCropper
		 *
		 * A state for cropping an image.
		 *
		 * @class
		 * @augments wp.media.controller.Cropper
		 * @augments wp.media.controller.State
		 * @augments Backbone.Model
		 */
		var TSFCropper;
		let Controller = wp.media.controller;

		TSFCropper = Controller.Cropper.extend( {
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
		TSFCropper.control = {
			'params' : {
				'flex_width' : 4096,
				'flex_height' : 4096,
				'width' : 1200,
				'height' : 630,
			},
		};

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

		controller.set( 'control', control.params );
		controller.set( 'canSkipCrop', ! tsf.mustBeCropped( control.params.flex_width, control.params.flex_height, realWidth, realHeight ) );

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

		if ( false === flexHeight && false === flexWidth ) {
			imgSelectOptions.aspectRatio = xInit + ':' + yInit;
		}

		// @TODO Convert set img min-width/height to output ratio.
		// i.e. 200x2000 will become x = 1500/2000*200 = 150px, which is too small.
		//= Unlikely...

		if ( true === flexHeight ) {
			imgSelectOptions.minHeight = 200;
			imgSelectOptions.maxWidth = realWidth;
		}

		if ( true === flexWidth ) {
			imgSelectOptions.minWidth = 200;
			imgSelectOptions.maxHeight = realHeight;
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

		var resizeTimeout = 0,
			$lastWidth = {},
			timeOut = 0;

		// Warning: Only checks for the first item existence.
		var $tabWrapper = jQuery( '.tsf-flex-nav-tab-wrapper' );

		jQuery( window ).on( 'tsf-flex-resize', function() {

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
		jQuery( window ).trigger( 'tsf-flex-resize' );
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

		// Set up counter classes.
		tsf.updateCounterClasses();

		// Add title prop listeners. Must load before setTabsOnload to work.
		tsf._initTitleInputs();
		tsf._initUnboundTitleSettings();

		// Add description prop listeners. Must load before setTabsOnload to work.
		tsf._initDescInputs();

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

		// AJAX counter
		$( '.tsf-counter' ).on( 'click', tsf.counterUpdate );
	}
};
jQuery( tsf.ready );
