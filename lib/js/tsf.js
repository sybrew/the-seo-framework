/**
 * This file holds The SEO Framework plugin's JS code.
 * Serve JavaScript as an addition, not as a means.
 *
 * @author Sybre Waaijer https://cyberwire.nl/
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
// @externs_url https://raw.githubusercontent.com/sybrew/the-seo-framework/master/lib/js/tsf.externs.js
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
	 * AJAX Nonce string.
	 *
	 * @since 2.7.0
	 * @todo deprecate
	 *
	 * @type {String} nonce The AJAX nonce
	 */
	nonce : tsfL10n.nonce,

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
	 * The current title tagline.
	 *
	 * @since 2.5.0
	 *
	 * @type {(Boolean|null)} titleTagline
	 */
	titleTagline : false,

	/**
	 * The current title separator.
	 *
	 * @since 2.7.0
	 *
	 * @type {(String|null)} titleSeparator
	 */
	titleSeparator : '',

	/**
	 * The current description separator.
	 *
	 * @since 2.7.0
	 *
	 * @type {(String|null)} descriptionSeparator
	 */
	descriptionSeparator : '',

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
	 * Cached doctitle function.
	 *
	 * @since 2.3.3
	 * @todo Convert to class var instead of function call. It's much faster.
	 *       i.e. setDocTitles() == docTitles : {}
	 *
	 * @function
	 * @return {!jQuery} The jQuery doctitle ID's
	 */
	docTitles: function() {
		/** Concept:
		if ( this.docTitles.cache )
			return this.docTitles.cache;

		let $doctitles = this.docTitles.cache = jQuery( "#autodescription_title, #autodescription-meta\\[doctitle\\], #autodescription-site-settings\\[homepage_title\\]" );
		*/

		let $doctitles = jQuery( "#autodescription_title, #autodescription-meta\\[doctitle\\], #autodescription-site-settings\\[homepage_title\\]" );

		return $doctitles;
	},

	/**
	 * Cached description function.
	 *
	 * @since 2.5.0
	 * @todo Convert to class var instead of function call. It's much faster.
	 *
	 * @function
	 * @return {!jQuery} The jQuery description ID's
	 */
	docDescriptions: function() {
		/** Concept:
		if ( this.docDescriptions.cache )
			return this.docDescriptions.cache;

		let $descriptions = this.docDescriptions.cache = jQuery( "#autodescription_description, #autodescription-meta\\[description\\], #autodescription-site-settings\\[homepage_description\\]" );
		*/

		let $descriptions = jQuery( "#autodescription_description, #autodescription-meta\\[description\\], #autodescription-site-settings\\[homepage_description\\]" );

		return $descriptions;
	},

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
	 * Description length counter.
	 *
	 * @since 2.2.4
	 * @since 2.9.3 Refactored to plain JS for discovering performance bugs.
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	updateCharacterCountDescription: function( event ) {

		let calcLength = event.target.value && event.target.value.length || 0,
			item = document.getElementById( event.target.id + '_chars' ),
			counterType = tsf.counterType,
			additionsClass = tsf.additionsClass,
			counterClass = '',
			name = '',
			output = '';

		// Emptied input, get Description placeholder.
		if ( 0 === calcLength ) {
			//* Output length from placeholder.
			calcLength = event.target.placeholder && event.target.placeholder.length;
		}

		if ( calcLength < 100 || calcLength >= 175 ) {
			counterClass = 'tsf-count-bad';
			name = tsf.getCounterName( 'bad' );
		} else if ( calcLength < 137 || ( calcLength > 155 && calcLength < 175 ) ) {
			counterClass = 'tsf-count-okay';
			name = tsf.getCounterName( 'okay' );
		} else {
			counterClass = 'tsf-count-good';
			name = tsf.getCounterName( 'good' );
		}

		//* Not strict by design.
		if ( counterType < 2 ) {
			output = calcLength;
		} else if ( 2 == counterType ) {
			output = name;
		} else if ( 3 == counterType ) {
			output = calcLength + ' - ' + name;
		}

		item.innerHTML = output;

		if ( additionsClass )
			counterClass += ' ' + additionsClass;

		if ( item.className !== counterClass )
			item.className = counterClass;
	},

	/**
	 * Title length counter, with special characters
	 *
	 * @since 2.2.4
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	updateCharacterCountTitle: function( event ) {

		var $this = jQuery( event.target ),
			additions = tsf.params['titleAdditions'].length,
			description = tsf.params['blogDescription'].length,
			siteTitle = tsf.params['siteTitle'].length,
			titleLength = event.target.value && event.target.value.length || 0,
			placeholder = jQuery( event.target ).prop( 'placeholder' ).length,
			tagline = jQuery( '#autodescription-site-settings\\[homepage_title_tagline\\]' ).val(),
			seplen = 3,
			$counter = jQuery( '#' + tsf.escapeStr( event.target.id ) + '_chars' ),
			calcLength = 0,
			additionsClass = tsf.additionsClass,
			counterType = tsf.counterType,
			counterClass = '',
			name = '',
			output = '';

		// Additions or tagline removed, remove additions and separator.
		if ( ! tsf.titleTagline ) {
			additions = 0;
			seplen = 0;
		}

		// Emptied input, get Site title.
		if ( 0 === titleLength ) {
			if ( 0 !== siteTitle ) {
				titleLength = siteTitle;
			} else {
				//* Output length from placeholder.
				calcLength = placeholder;
			}
		}

		// Length should be something now.
		if ( 0 !== titleLength ) {

			if ( 0 !== additions && typeof tagline !== 'undefined' ) {
				var $tagLength = tagline.length;

				// Replace additions with tagline is tagline isn't empty.
				if ( 0 !== $tagLength ) {
					additions = $tagLength;
				} else {
					additions = description;
				}
			}

			// Put it all together
			if ( 0 === additions ) {
				calcLength = titleLength;
			} else {
				calcLength = titleLength + seplen + additions;
			}
		}

		if ( calcLength < 25 || calcLength >= 75 ) {
			counterClass = 'tsf-count-bad';
			name = tsf.getCounterName( 'bad' );
		} else if ( calcLength < 42 || ( calcLength > 55 && calcLength < 75 ) ) {
			counterClass = 'tsf-count-okay';
			name = tsf.getCounterName( 'okay' );
		} else {
			counterClass = 'tsf-count-good';
			name = tsf.getCounterName( 'good' );
		}

		if ( additionsClass )
			counterClass += ' ' + additionsClass;

		if ( ! counterType || 1 == counterType ) {
			output = calcLength.toString();
		} else if ( 2 == counterType ) {
			output = name;
		} else if ( 3 == counterType ) {
			output = calcLength.toString() + ' - ' + name;
		}

		$counter.text( output ).removeClass().addClass( counterClass );
	},

	/**
	 * Escapes HTML strings.
	 *
	 * @since 2.2.4
	 *
	 * @function
	 *
	 * @param {String} str
	 * @return {(String|null)} HTML to jQuery converted string
	 */
	escapeStr: function( str ) {

		if ( str )
			return str.replace( /([\[\]\/])/g, '\\$1' );

		return str;
	},

	/**
	 * Dynamic Title separator replacement in metabox
	 *
	 * @since 2.2.2
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	separatorSwitchTitle: function( event ) {

		let $sep = jQuery( ".tsf-sep-js" ),
			val = jQuery( event.target ).val();

		//* Update cache.
		tsf.titleSeparator = val;

		if ( 'pipe' === val ) {
			$sep.text( " | " );
		} else if ( 'dash' === val ) {
			$sep.text( " - " );
		} else {
			$sep.html( " &" + val + "; " ).text();
		}
	},

	/**
	 * Dynamic Description separator replacement in metabox
	 *
	 * @since 2.3.4
	 * @since 2.9.3 Removed sanitation on hardcoded input.
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	separatorSwitchDesc: function( event ) {

		let $sep = jQuery( "#autodescription-descsep-js" ),
			val = jQuery( event.target ).val();

		if ( 'pipe' === val ) {
			$sep.text( " | " );
		} else if ( 'dash' === val ) {
			$sep.text( " - " );
		} else {
			$sep.html( " &" + val + "; " ).text();
		}
	},

	/**
	 * Status bar description init on hover actions.
	 *
	 * @since 2.1.9
	 *
	 * @function
	 * @return {undefined}
	 */
	statusBarHover: function() {

		let $wrap = jQuery( '.tsf-seo-bar-inner-wrap' ).find( 'a' );

		$wrap.off( 'mouseenter mousemove mouseleave mouseout' );

		$wrap.on( {
			'mouseenter' : tsf.statusBarHoverEnter,
			'mousemove'  : tsf.statusBarHoverMove,
			'mouseleave' : tsf.statusBarHoverLeave,
			'mouseout'   : tsf.statusBarHoverLeave,
		} );
	},

	/**
	 * Status bar description output on hover enter.
	 *
	 * @since 2.6.0
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	statusBarHoverEnter: function( event ) {

		let $this = jQuery( event.target ),
			desc = $this.data( 'desc' );

		if ( desc !== undefined && 0 === $this.find( 'div' ).length ) {
			$this.append( '<div class="tsf-explanation-desc">' + desc + '<div></div></div>' );

			let height = $this.find( 'div.tsf-explanation-desc' ).height() + 28;

			$this.find( 'div.tsf-explanation-desc' ).css( 'top', ( $this.position().top - height ) + 'px' );
		}
	},

	/**
	 * Status bar description output on hover move.
	 *
	 * @since 2.6.0
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	statusBarHoverMove: function( event ) {

		var $this = jQuery( event.target ),
			pagex = event.pageX,
			mousex = pagex - $this.closest( '.tsf-seo-bar-inner-wrap' ).offset().left - 11, // 22px width of arrow / 2 = 11 middle
			$balloon = $this.find( '.tsf-explanation-desc' ),
			$arrow = $balloon.find( 'div' );

		if ( mousex < 1 ) {
			$arrow.css( 'left', 0 + "px" );
		} else if ( $balloon.offset() !== undefined ) {
			let width = $balloon.width(),
				maxOffset = $balloon.offset().left + width + 11;

			if ( pagex > maxOffset ) {
				$arrow.css( 'left', width + "px" );
			} else {
				$arrow.css( 'left', mousex + "px" );
			}
		}
	},

	/**
	 * Status bar description removal on hover leave.
	 *
	 * @since 2.6.0
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	statusBarHoverLeave: function( event ) {
		jQuery( event.target ).find( 'div.tsf-explanation-desc' ).remove();
	},

	/**
	 * Remove Status bar desc if clicked outside (touch support).
	 *
	 * @since 2.9.3
	 *
	 * @function
	 * @param {jQuery.event} event jQuery event
	 */
	touchRemoveDesc: function( event ) {

		let $target = jQuery( event.target ),
			hasBalloon = $target.closest( '.tsf-seo-bar-inner-wrap a' ).length;

		if ( ! hasBalloon ) {
			$target.closest( '.tsf-seo-bar-inner-wrap a' ).find( 'div.tsf-explanation-desc' ).remove();
		}
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

		if ( tsf.hasInput ) {
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
		}
	},

	/**
	 * Toggle tagline within the Left/Right example for the HomePage Title
	 *
	 * @since 2.2.4
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	taglineToggleTitle: function( event ) {

		let $this = jQuery( event.target ),
			$tag = jQuery( '.tsf-custom-blogname-js' );

		if ( $this.is( ':checked' ) ) {
			$tag.css( 'display', 'inline' );
			tsf.titleTagline = true;
		} else {
			$tag.css( 'display', 'none' );
			tsf.titleTagline = false;
		}

		tsf.docTitles().trigger( 'keyup', tsf.updateCharacterCountTitle );
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
	 * Toggle title additions location for the Title examples.
	 *
	 * @since 2.6.0
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	titleLocationToggle: function( event ) {

		let val = jQuery( event.target ).val(),
			$titleExampleLeft = jQuery( '.tsf-title-additions-example-left' ),
			$titleExampleRight = jQuery( '.tsf-title-additions-example-right' );

		if ( 'right' === val ) {
			$titleExampleLeft.css( 'display', 'none' );
			$titleExampleRight.css( 'display', 'inline' );
		} else {
			$titleExampleLeft.css( 'display', 'inline' );
			$titleExampleRight.css( 'display', 'none' );
		}
	},

	/**
	 * Toggle title prefixes for the Prefix Title example.
	 *
	 * @since 2.6.0
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	titlePrefixToggle: function( event ) {

		let $this = jQuery( event.target ),
			$prefix = jQuery( '.tsf-title-prefix-example' );

		if ( $this.is(':checked') ) {
			$prefix.css( 'display', 'none' );
		} else {
			$prefix.css( 'display', 'inline' );
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
	 * Change Home Page Title based on input of the Custom Title
	 *
	 * @since 2.2.4
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	titleProp: function( event ) {

		let val = event.target.value || '',
			$title = jQuery( '.tsf-custom-title-js' );

		if ( 0 === val.length ) {
			$title.text( tsf.params['siteTitle'] );
		} else {
			$title.text( val );
		}
	},

	/**
	 * Change Title based on input of the Custom Title
	 *
	 * @since 2.3.8
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	taglineProp: function( event ) {

		var val = event.target.value || '',
			$floatTag = jQuery( '.tsf-custom-tagline-js' ),
			$target = jQuery( '#autodescription-site-settings\\[homepage_title\\]' ),
			leftRight = jQuery( '#tsf-home-title-location input:checked' ).val(),
			$toggle = jQuery( '#autodescription-site-settings\\[homepage_tagline\\]' ),
			title = tsf.params['siteTitle'],
			placeholder = title,
			description = tsf.params['blogDescription'],
			sep = tsf.getSep( 'title' );

		if ( $toggle.is( ':checked' ) ) {

			if ( val.length !== 0 ) {
				description = val;
			}

			if ( leftRight.length !== 0 && 'left' === leftRight ) {
				placeholder = title + ' ' + sep + ' ' + description;
			} else {
				placeholder = description + ' ' + sep + ' ' + title;
			}
		}

		$floatTag.text( description );
		$target.prop( 'placeholder', placeholder );

		// Notify tagline has changed.
		tsf.docTitles().trigger( 'input', tsf.updateCharacterCountTitle );
	},

	/**
	 * Trigger input event for page titles.
	 *
	 * @since 2.5.0
	 *
	 * @function
	 * @return {undefined}
	 */
	taglinePropTrigger: function() {

		let settingsChangedCache = tsf.settingsChanged;

		if ( tsf.states['isSettingsPage'] ) {
			jQuery( "#autodescription-site-settings\\[homepage_title_tagline\\]" ).trigger( 'input', tsf.taglineProp );
		} else {
			jQuery( "#autodescription_title" ).trigger( 'input', tsf.taglineProp );
		}

		// Reset settingsChanged to previous value.
		tsf.settingsChanged = settingsChangedCache;
	},

	/**
	 * Triggers input event for titles in set intervals on window resize.
	 *
	 * This only happens if boundaries are surpassed to reduce CPU usage.
	 * This boundary is 782 pixels, because that forces input fields to change.
	 * in WordPress.
	 *
	 * @since 2.9.0
	 *
	 * @function
	 * @return {undefined}
	 */
	taglinePropTriggerResize: function() {

		let resizeTimeout = 0,
			prevWidth = 0;

		jQuery( window ).resize( function() {

			clearTimeout( resizeTimeout );

			resizeTimeout = setTimeout( function() {

				let width = jQuery( window ).width();

				if ( prevWidth < width ) {
					if ( prevWidth <= 782 && width >= 782 ) {
						tsf.taglinePropTrigger();
					}
				} else {
					if ( prevWidth >= 782 && width <= 782 ) {
						tsf.taglinePropTrigger();
					}
				}

				prevWidth = width;
			}, 250 );
		} );
	},

	/**
	 * Trigger Change on Left/Right selection of Global Title
	 *
	 * @since 2.5.2
	 *
	 * @function
	 * @return {undefined}
	 */
	titleToggle: function() {

		let $this = jQuery( event.target ),
			$tagDesc = jQuery( '.tsf-title-additions-js' );

		if ( $this.is( ':checked' ) ) {
			$tagDesc.css( 'display', 'none' );
		} else {
			$tagDesc.css( 'display', 'inline' );
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
	 * Adds dynamic placeholder to Title input based on site settings.
	 *
	 * @since 2.5.0
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {(!jQuery|undefined)}
	 */
	dynamicPlaceholder: function( event ) {

		let hasAdditions = tsf.params['titleAdditions'].length,
			$placeholder = jQuery( '#tsf-title-placeholder' );

		// If hasAdditions is empty, there's nothing to do.
		if ( ! hasAdditions ) {
			// Empty the placeholder as we can't execute.
			jQuery( event.target ).css( 'text-indent', "initial" );
			return $placeholder.empty();
		}

		let after = false,
			check = jQuery( '#tsf-home-title-location input:checked' ).val(),
			rtl = tsf.states['isRTL'],
			additions = '';

		if ( typeof check !== 'undefined' && check.length !== 0 ) {
			//* We're in SEO Settings page.

			if ( rtl ) {
				if ( 'right' === check ) {
					after = true;
				}
			} else {
				if ( 'left' === check ) {
					after = true;
				}
			}
		} else {
			//* We're in post/page edit screen.

			let titleLocation = tsf.params['titleLocation'];

			// We're on post/page screen.
			if ( tsf.states['isHome'] ) {
				// Static Front page, switch check.
				if ( tsf.titleTagline ) {
					if ( rtl ) {
						if ( 'right' === titleLocation ) {
							after = true;
						}
					} else if ( 'left' === titleLocation ) {
						after = true;
					}
				}
			} else {
				if ( rtl ) {
					if ( 'left' === titleLocation ) {
						after = true;
					}
				} else if ( 'right' === titleLocation ) {
					after = true;
				}
			}
		}

		let $tagbox = jQuery( '#tsf-title-tagline-toggle :input' );

		if ( typeof $tagbox !== "undefined" && $tagbox.length > 0 && ! $tagbox.is( ':checked' ) ) {
			//* We're on SEO Settings Page now, and tagline has been disabled.
			let $this = jQuery( event.target );

			$this.css( 'text-indent', "initial" );
			$placeholder.css( 'display', "none" );
		} else {
			var $this = jQuery( event.target ),
				inputVal = $this.val(),
				$offsetTest = jQuery( "#tsf-title-offset" );

			let offsetWidth = 0,
				outerWidth = $this.outerWidth(),
				heightPad = ( $this.outerHeight( true ) - $this.height() ) / 2,
				horPad = ( outerWidth - $this.innerWidth() ) / 2,
				leftOffset = ( $this.outerWidth( true ) - $this.width() ) / 2,
				taglineVal = jQuery( "#autodescription-site-settings\\[homepage_title_tagline\\]" ).val(),
				pos = 'left',
				separator = tsf.getSep( 'title' );

			if ( rtl ) {
				pos = 'right';
			}

			if ( typeof taglineVal !== "undefined" && taglineVal.length === 0) {
				taglineVal = tsf.params['blogDescription'];
			}

			if ( after ) {
				additions = " " + separator + " " + tsf.params['titleAdditions'];

				// Exchange the placeholder value of the custom Tagline in the HomePage Metabox
				if ( typeof taglineVal !== "undefined" && taglineVal.length > 0 ) {
					additions = " " + separator + " " + taglineVal;
				}

				$this.css( 'text-indent', "initial" );
			} else {
				additions = tsf.params['titleAdditions'] + " " + separator + " ";

				// Exchange the placeholder value of the custom Tagline in the HomePage Metabox
				if ( typeof taglineVal !== "undefined" && taglineVal.length > 0 ) {
					additions = taglineVal + " " + separator + " ";
				}
			}

			// Width offset container, copy variables and remain hidden.
			$offsetTest.text( inputVal );
			$offsetTest.css({
				'fontFamily' : $this.css( "fontFamily" ),
				'fontWeight' : $this.css( "fontWeight" ),
				'letterSpacing' : $this.css( "letterSpacing" ),
				'fontSize' : $this.css( "fontSize" ),
			});
			offsetWidth = $offsetTest.width();

			let maxWidth = $this.width() - horPad - offsetWidth;

			if ( maxWidth < 0 )
				maxWidth = 0;

			// Moving Placeholder output
			$placeholder.css({
				'display': $this.css( "display" ),
				'lineHeight': $this.css( "lineHeight" ),
				'paddingTop': heightPad + "px",
				'paddingBottom': heightPad + "px",
				'fontFamily': $this.css( "fontFamily" ),
				'fontWeight': $this.css( "fontWeight" ),
				'fontSize': $this.css( "fontSize" ),
				'letterSpacing': $this.css( "letterSpacing" ),
				'maxWidth': maxWidth + "px",
			});

			//* Empty or fill placeholder and offsets.
			if ( typeof inputVal === "undefined" || inputVal.length < 1 ) {

				if ( ! after )
					$this.css( 'text-indent', "initial" );

				$placeholder.empty();
			} else {
				$placeholder.text( additions );

				// Only calculate when context is present.
				if ( outerWidth > leftOffset ) {
					if ( after ) {
						$placeholder.css( pos, horPad + leftOffset + $offsetTest.width() + "px" );
					} else {
						let indent = horPad + $placeholder.width();

						if ( indent < 0 )
							indent = 0;

						$placeholder.css( pos, leftOffset + "px" );
						$this.css( 'text-indent', indent + "px" );
					}
				}
			}
		}
	},

	/**
	 * Makes user click act natural by selecting the parent Title text input.
	 *
	 * @since 2.5.0
	 *
	 * @function
	 * @return {undefined}
	 */
	selectTitleInput: function() {

		let $input = tsf.docTitles();

		$input.focus();

		if ( $input.setSelectionRange ) {
			// Go to end times 2 if setSelectionRange exists.
			let length = $input.val().length * 2;
			$input.setSelectionRange( length, length );
		} else {
			// Replace value with itself.
			$input.val( $input.val() ).focus();
		}
	},

	/**
	 * Triggers keyup on description input so the counter can colorize.
	 *
	 * @since 2.5.0
	 *
	 * @function
	 * @return {undefined}
	 */
	triggerDescriptionOnLoad: function() {

		if ( ! tsf.hasInput )
			return;

		let $input = tsf.docDescriptions();

		$input.trigger( 'input', tsf.updateCharacterCountDescription );
	},

	/**
	 * Triggers keyup on title input so the counter can colorize.
	 *
	 * @since 2.6.0
	 *
	 * @function
	 * @return {undefined}
	 */
	triggerTitleOnLoad: function() {

		if ( ! tsf.hasInput )
			return;

		let $input = tsf.docTitles();

		$input.trigger( 'input', tsf.updateCharacterCountTitle );
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
			fade = 2500;

		if ( ! success ) {
			newclass = 'tsf-error';
			fade = 5000;
		}

		jQuery( target ).removeClass( 'tsf-loading' ).addClass( newclass ).fadeOut( fade );
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
		tsf.additionsClassInit();

		let target = '.tsf-counter .tsf-ajax',
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
	 * @since 2.6.0
	 *
	 * @function
	 * @return {undefined}
	 */
	additionsClassInit: function() {

		if ( ! tsf.hasInput )
			return;

		let counterType = tsf.counterType,
			settingsChangedCache = tsf.settingsChanged;

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

		tsf.updateCounters();

		// Reset settingsChanged to previous value.
		tsf.settingsChanged = settingsChangedCache;
	},

	/**
	 * Update counters.
	 *
	 * @since 2.6.0
	 *
	 * @function
	 * @return {undefined}
	 */
	updateCounters: function() {
		tsf.triggerTitleOnLoad();
		tsf.triggerDescriptionOnLoad();
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
	 * Returns converted HTML title/description separator.
	 *
	 * @since 2.7.0
	 * @since 2.9.0 Added caching.
	 *
	 * @function
	 * @param {String} type
	 * @return {String} sep The converted separator.
	 */
	getSep: function( type ) {

		let sep = '',
			retVal = '';

		if ( 'title' === type ) {
			sep = tsf.titleSeparator;
		} else {
			sep = tsf.descriptionSeparator;
		}

		/** Concept:
		if ( this.getSep.cachedSep && this.getSep.cachedSep.hasOwnProperty( type ) && sep === this.getSep.cachedSep[ type ]['sep'] ) {
			return this.getSep.cachedSep[ type ]['retVal'];
		} else {
			//* Setup main cache container.
			if ( ! this.getSep.cachedSep )
				this.getSep.cachedSep = {};

			//* Set up secondary cache container.
			if ( ! this.getSep.cachedSep[ type ] )
				this.getSep.cachedSep[ type ] = {};

			//* Setup cache listener.
			this.getSep.cachedSep[ type ]['sep'] = sep;
		}
		*/

		if ( 'pipe' === sep || '|' === sep ) {
			retVal = ( "|" );
		} else if ( 'dash' === sep || '-' === sep ) {
			retVal = ( "-" );
		} else if ( sep.charCodeAt(0) < 123 ) {
			//* Checked for UTF-8 conversion.
			// Create a memory div to store the html in, convert to text to append in $placeholder
			retVal = jQuery.trim( sep );
			if ( '&' === sep.charAt(0) && ';' === sep.slice(-1) )
				sep = sep.substr(1).slice(0, -1);

			retVal = jQuery( '<div/>' ).html( "&" + sep + ";" ).text();
		}

		/** Concept:
		return this.getSep.cachedSep[ type ]['retVal'] = retVal;
		*/

		//* Setup cache listener and return val.
		return retVal;
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

		let selectFunc = (function() {
				frame.setState( 'cropper' );
			} );
		frame.off( 'select', selectFunc );
		frame.on( 'select', selectFunc );

		let croppedFunc = (function( croppedImage ) {
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

		let skippedcropFunc = (function( selection ) {
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

		let doneFunc = (function( imageSelection ) {
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
				var cropDetails = attachment.get( 'cropDetails' ),
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
								var _ratio = cropDetails.width / control.params.flex_width;

								cropDetails.dst_width  = control.params.flex_width;
								cropDetails.dst_height = Math.round( cropDetails.height / _ratio );
							// Portrait
							} else {
								var _ratio = cropDetails.height / control.params.flex_height;

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

		var flexWidth  = !! parseInt( control.params.flex_width, 10 ),
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
	 * Sets up object parameters.
	 *
	 * @since 2.8.0
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	setupVars: function() {

		//The current title separator.
		tsf.titleSeparator = tsf.params['titleSeparator'];

		//The current description separator.
		tsf.descriptionSeparator = tsf.params['descriptionSeparator'];

		// The current title tagline.
		tsf.titleTagline = tsf.states['titleTagline'];

		// The counter type. Mixed string and int (i10n is string, JS is int).
		tsf.counterType = parseInt( tsf.states['counterType'] );

		// Determines if the current page has input boxes for The SEO Framework.
		tsf.hasInput = tsf.states['hasInput'];
	},

	/**
	 * Registers title prop listeners.
	 *
	 * @since 2.9.0
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	_initTitlepropListener: function() {

		if ( tsf.hasInput ) {
			let jQ = jQuery;

			//* Trigger directly on call load.
			//tsf.taglinePropTrigger();

			// Toggle Title tagline aditions removal.
			jQ( '#tsf-title-tagline-toggle :input' ).on( 'click', tsf.taglineToggleTitle );
			// Toggle Title additions location.
			jQ( '#tsf-title-location input' ).on( 'click', tsf.titleLocationToggle );
			// Toggle Title prefixes display.
			jQ( '#title-prefixes-toggle :input' ).on( 'click', tsf.titlePrefixToggle );

			// Change Home Page Title Example prop on input changes.
			jQ( '#autodescription-site-settings\\[homepage_title\\]' ).on( 'input', tsf.titleProp );
			jQ( '#tsf-home-title-location :input, #tsf-title-tagline-toggle :input, #tsf-title-separator input' ).on( 'click', tsf.taglinePropTrigger );
			jQ( '#autodescription-site-settings\\[homepage_title_tagline\\]' ).on( 'input', tsf.taglineProp );

			// Make sure the titleProp is correctly rendered when revealed after being hidden.
			jQ( '#homepage-tab-general' ).on( 'tsf-tab-toggled', tsf.taglinePropTrigger );
			jQ( '#autodescription-homepage-settings > button' ).on( 'click', tsf.taglinePropTrigger );
			jQ( '#tsf-inpost-box > button' ).on( 'click', tsf.taglinePropTrigger );

			// Change Global Title Example prop on input changes.
			jQ( '#autodescription-site-settings\\[title_rem_additions\\]' ).on( 'click', tsf.titleToggle );

			// Dynamic Placeholder, acts on keydown for a11y, although more cpu intensive. Acts on keyup for perfect output.
			tsf.docTitles().on( 'input', tsf.dynamicPlaceholder );

			// Move click on dynamic additions to focus input behind.
			jQ( '#tsf-title-placeholder' ).on( 'click', tsf.selectTitleInput );

			jQ( window ).resize( tsf.taglinePropTriggerResize );
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
	 * @todo setup ready action list and action callers. Now it's ravioli that's
	 *       dependent on eachother.
	 *
	 * @function
	 * @param {!jQuery} jQ jQuery
	 * @return {undefined}
	 */
	ready: function( jQ ) {

		// Set up object parameters.
		tsf.setupVars();

		// Move the page updates notices below the tsf-top-wrap.
		jQ( 'div.updated, div.error, div.notice-warning' ).insertAfter( 'div.tsf-top-wrap' );

		// Add title prop listeners. Must load before setTabsOnload to work.
		jQ( document.body ).ready( tsf._initTitlepropListener );

		// Sets tabs to correct radio button on load.
		jQ( document.body ).ready( tsf.setTabsOnload );

		// Set up additions classes.
		jQ( document.body ).ready( tsf.additionsClassInit );

		// Check if the Title Tagline or Description Additions should be removed when page is loaded.
		jQ( document.body ).ready( tsf.taglineToggleOnload );

		// Initialize the status bar hover balloon.
		jQ( document.body ).ready( tsf.statusBarHover );

		// Initialize image uploader button cache.
		jQ( document.body ).ready( tsf.setupImageEditorActions );

		// Determine image editor button input states.
		jQ( document.body ).ready( tsf.checkImageEditorInput );

		// Correct Color Picker input
		jQ( document.body ).ready( tsf.setColorOnload );

		// #== End Before Change listener

		// Initialise form field changing flag.
		jQ( document.body ).ready( tsf.attachUnsavedChangesListener );

		// Deregister changes.
		jQ( document.body ).ready( tsf.onLoadUnregisterChange );

		// Trigger tsf-ready event.
		jQ( document.body ).ready( tsf._triggerReady );

		// #== Start After Change listener

		// Do flex resize functionality.
		jQ( document.body ).ready( tsf._doFlexResizeListener );

		// Initialize status bar removal hover for touch screens.
		jQ( document.body ).on( 'click touchstart MSPointerDown', tsf.touchRemoveDesc );

		// Bind character counters.
		tsf.docDescriptions().on( 'input', tsf.updateCharacterCountDescription );
		tsf.docTitles().on( 'input', tsf.updateCharacterCountTitle );

		// Allow the title separator to be changed dynamically.
		jQ( '#tsf-title-separator input' ).on( 'click', tsf.separatorSwitchTitle );
		// Allow description separator to be changed dynamically.
		jQ( '#tsf-description-separator input' ).on( 'click', tsf.separatorSwitchDesc );

		// Bind reset confirmation.
		jQ( '.tsf-js-confirm-reset' ).on( 'click', tsf.confirmedReset );

		// Toggle Tabs in the SEO settings page.
		jQ( '.tsf-tabs-radio' ).on( 'change', tsf.tabToggle );

		// Toggle Tabs for the inpost Flex settings.
		jQ( '.tsf-flex-nav-tab-radio' ).on( 'change', tsf.flexTabToggle );

		// Toggle Description additions removal.
		jQ( '#tsf-description-onblogname-toggle :input' ).on( 'click', tsf.taglineToggleDesc );
		jQ( '#tsf-description-additions-toggle :input' ).on( 'click', tsf.additionsToggleDesc );

		// Dismiss notices.
		jQ( '.tsf-dismiss' ).on( 'click', tsf.dismissNotice );

		// AJAX counter
		jQ( '.tsf-counter' ).on( 'click', tsf.counterUpdate );
	}
};
jQuery( tsf.ready );
