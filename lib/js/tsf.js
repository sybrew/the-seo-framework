/**
 * This file holds The SEO Framework plugin's JS code.
 * Serve JavaScript as an addition, not as a means.
 *
 * @author Sybre Waaijer https://cyberwire.nl/
 * @link https://wordpress.org/plugins/autodescription/
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
	 *
	 * @type {String} nonce The AJAX nonce
	 */
	nonce : tsfL10n.nonce,

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
	 *
	 * @function
	 * @return {!jQuery} The jQuery doctitle ID's
	 */
	docTitles: function() {
		'use strict';

		var $doctitles = jQuery( "#autodescription_title, #autodescription-meta\\[doctitle\\], #autodescription-site-settings\\[homepage_title\\]" );

		return $doctitles;
	},

	/**
	 * Cached description function.
	 *
	 * @since 2.5.0
	 *
	 * @function
	 * @return {!jQuery} The jQuery description ID's
	 */
	docDescriptions: function() {
		'use strict';

		var $descriptions = jQuery( "#autodescription_description, #autodescription-meta\\[description\\], #autodescription-site-settings\\[homepage_description\\]" );

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
		'use strict';

		return confirm( text );
	},

	/**
	 * Description length counter.
	 *
	 * @since 2.2.4
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	updateCharacterCountDescription: function( event ) {
		'use strict';

		var $this = jQuery( event.target ),
			calcLength = $this.val().length,
			$counter = jQuery( '#' + tsf.escapeStr( event.target.id ) + '_chars' ),
			counterClass = '',
			name = '',
			output = '';

		// Emptied input, get Description placeholder.
		if ( 0 === calcLength ) {
			//* Output length from placeholder.
			calcLength = $this.prop( 'placeholder' ).length;
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

		if ( tsf.additionsClass )
			counterClass += ' ' + tsf.additionsClass;

		if ( ! tsf.counterType || 1 == tsf.counterType ) {
			output = calcLength.toString();
		} else if ( 2 == tsf.counterType ) {
			output = name;
		} else if ( 3 == tsf.counterType ) {
			output = calcLength.toString() + ' - ' + name;
		}

		$counter.html( output ).removeClass().addClass( counterClass );
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
		'use strict';

		var $this = jQuery( event.target ),
			additions = tsf.params['titleAdditions'].length,
			description = tsf.params['blogDescription'].length,
			siteTitle = tsf.params['siteTitle'].length,
			titleLength = $this.val().length,
			placeholder = $this.prop( 'placeholder' ).length,
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

		$counter.html( output ).removeClass().addClass( counterClass );
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
		'use strict';

		if ( str )
			return str.replace( /([\[\]\/])/g,'\\$1');

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
		'use strict';

		var $sep = jQuery( ".tsf-sep-js" ),
			val = jQuery( event.target ).val();

		//* Update cache.
		tsf.titleSeparator = val;

		if ( 'pipe' === val ) {
			$sep.text( " | " );
		} else if ( 'dash' === val ) {
			$sep.text( " - " );
		} else {
			$sep.html( " &" + val + "; " );
		}
	},

	/**
	 * Dynamic Description separator replacement in metabox
	 *
	 * @since 2.3.4
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	separatorSwitchDesc: function( event ) {
		'use strict';

		var $sep = jQuery( "#autodescription-descsep-js" ),
			val = jQuery( event.target ).val();

		if ( 'pipe' === val ) {
			$sep.text( " | " );
		} else if ( 'dash' === val ) {
			$sep.text( " - " );
		} else {
			$sep.html( " &" + val + "; " );
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
		'use strict';

		var $wrap = jQuery( '.tsf-seo-bar-inner-wrap' ).find( 'a' );

		$wrap.on( 'mouseenter', tsf.statusBarHoverEnter );
		$wrap.on( 'mousemove', tsf.statusBarHoverMove );
		$wrap.on( 'mouseleave', tsf.statusBarHoverLeave );
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
		'use strict';

		var $this = jQuery( event.target ),
			desc = $this.data( 'desc' );

		if ( desc !== undefined && 0 === $this.find( 'div' ).length ) {
			$this.append( '<div class="tsf-explanation-desc">' + desc + '<div></div></div>' );

			var height = $this.find( 'div.tsf-explanation-desc' ).height() + 28;

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
		'use strict';

		var $this = jQuery( event.target ),
			pagex = event.pageX,
			mousex = pagex - jQuery( '.tsf-seo-bar-inner-wrap' ).offset().left - 11, // 22px width of arrow / 2 = 11 middle
			$balloon = $this.find( '.tsf-explanation-desc' ),
			$arrow = $balloon.find( 'div' );

		if ( mousex < 1 ) {
			$arrow.css( 'left', 0 + "px" );
		} else if ( $balloon.offset() !== undefined ) {
			var width = $balloon.width(),
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
		'use strict';

		jQuery( event.target ).find( 'div.tsf-explanation-desc' ).remove();
	},

	/**
	 * Remove Status bar desc if clicked outside (touch support)
	 *
	 * @since 2.1.9
	 *
	 * @function
	 * @return {undefined}
	 */
	removeDesc: function() {
		'use strict';

		var $target = jQuery( document.body.target ),
			$desc = jQuery( '.tsf-seo-bar-inner-wrap a' );

		if ( ! $target.closest( $desc ).length )
			$desc.find( 'div.tsf-explanation-desc' ).remove();
	},

	/**
	 * Refines Styling for the navigation tabs on the settings pages
	 *
	 * @since 2.2.2
	 *
	 * Rewritten
	 * @since 2.6.0
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	tabToggle: function( event ) {
		'use strict';

		var target = jQuery( event.target ).prop( 'id' ),
			name = jQuery( event.target ).prop( 'name' );

		if ( typeof target !== 'undefined' ) {
			var $content = jQuery( '#' + target + '-content' ),
				$other = jQuery( '.' + name + '-content' );

			if ( typeof $content !== 'undefined' ) {
				$other.removeClass( 'tsf-active-tab-content' );
				$content.addClass( 'tsf-active-tab-content' );
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
		'use strict';

		var $this = jQuery( event.target ),
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
		'use strict';

		var $this = jQuery( event.target ),
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
		'use strict';

		var val = jQuery( event.target ).val(),
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
		'use strict';

		var $this = jQuery( event.target ),
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
		'use strict';

		var $this = jQuery( event.target ),
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
		'use strict';

		if ( ! tsf.hasInput )
			return;

		var $tagTitle = jQuery( '#tsf-title-tagline-toggle :input' ),
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
		'use strict';

		var val = jQuery( event.target ).val(),
			$title = jQuery( '.tsf-custom-title-js' );

		if ( val.length === 0 ) {
			$title.text( tsf.i18n['siteTitle'] );
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
		'use strict';

		var val = jQuery( event.target ).val(),
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
	 * Trigger Change on Left/Right selection of Home Page Title
	 *
	 * @since 2.5.0
	 *
	 * @function
	 * @return {undefined}
	 */
	taglinePropTrigger: function() {
		'use strict';

		jQuery( "#autodescription-site-settings\\[homepage_title_tagline\\]" ).trigger( 'input', tsf.taglineProp );
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
		'use strict';

		var $this = jQuery( event.target ),
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
	 *
	 * @function
	 * @return {undefined}
	 */
	attachUnsavedChangesListener: function() {
		'use strict';

		if ( ! tsf.hasInput )
			return;

		jQuery( '.tsf-metaboxes :input, #tsf-inpost-box .inside :input' ).not( '.tsf-tab :input' ).change( function() {
			tsf.registerChange();
		} );

		jQuery( '.tsf-metaboxes input[type=text], .tsf-metaboxes textarea, #tsf-inpost-box .inside input[type=text], #tsf-inpost-box .inside textarea' ).not(
			'.tsf-nav-tab-wrapper :input' ).on( 'input', function() {
			tsf.registerChange();
		} );

		window.onbeforeunload = function() {
			if ( tsf.settingsChanged ) {
				return tsf.i18n['saveAlert'];
			}
		};

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
		'use strict';

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
		'use strict';

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
		'use strict';

		var hasAdditions = tsf.params['titleAdditions'].length,
			$placeholder = jQuery( '#tsf-title-placeholder' );

		// If check is defined, we're on SEO settings page.
		if ( 0 === hasAdditions ) {
			var $this = jQuery( event.target );

			// Empty the placeholder as we can't execute.
			$this.css( 'text-indent', "initial" );
			return $placeholder.empty();
		}

		var after = false,
			check = jQuery( '#tsf-home-title-location input:checked' ).val(),
			rtl = tsf.states['isRTL'],
			additions = '';

		if ( typeof check !== 'undefined' && check.length !== 0 ) {
			//* We're in SEO Settings page.

			if ( '1' === rtl ) {
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

			var isHome = tsf.states['isHome'],
				titleLocation = tsf.params['titleLocation'],
				tagline = tsf.titleTagline;

			// We're on post/page screen.
			if ( isHome ) {
				// Static Front page, switch check.
				if ( tagline ) {
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

		var $tagbox = jQuery( '#tsf-title-tagline-toggle :input' );

		if ( typeof $tagbox !== "undefined" && $tagbox.length > 0 && ! $tagbox.is( ':checked' ) ) {
			//* We're on SEO Settings Page now, and tagline has been disabled.
			var $this = jQuery( event.target );

			$this.css( 'text-indent', "initial" );
			$placeholder.css( 'display', 'none' );
		} else {
			var $this = jQuery( event.target ),
				inputVal = $this.val(),
				$offsetTest = jQuery( "#tsf-title-offset" ),
				offsetWidth = 0,
				heightPad = ( $this.outerHeight( true ) - $this.height() ) / 2,
				horPad = ( $this.outerWidth() - $this.width() ) / 2,
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
				additions = separator + " " + tsf.params['titleAdditions'];

				// Exchange the placeholder value of the custom Tagline in the HomePage Metabox
				if ( typeof taglineVal !== "undefined" && taglineVal.length > 0 ) {
					additions = separator + " " + taglineVal;
				}

				$this.css( 'text-indent', "initial" );
			} else {
				additions = tsf.params['titleAdditions'] + " " + separator;

				// Exchange the placeholder value of the custom Tagline in the HomePage Metabox
				if ( typeof taglineVal !== "undefined" && taglineVal.length > 0 ) {
					additions = taglineVal + " " + separator;
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

			var maxWidth = $this.width() - horPad - offsetWidth;

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

				// Don't calculate when empty.
				if ( $this.outerWidth() > leftOffset ) {
					if ( after ) {
						$placeholder.css( pos, horPad + leftOffset + $offsetTest.width() + "px" );
					} else {
						var indent = horPad + $placeholder.width();

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
		'use strict';

		var $input = tsf.docTitles();

		$input.focus();

		if ( $input.setSelectionRange ) {
			// Go to end times 2 if setSelectionRange exists.
			var length = $input.val().length * 2;
			$input.setSelectionRange( length, length );
		} else {
			// Replace value with itself.
			$input.val( $input.val() ).focus();
		}
	},

	/**
	 * Adds dynamic placeholder to Title input based on site settings on Load.
	 *
	 * @since 2.5.0
	 *
	 * @function
	 * @return {undefined}
	 */
	dynamicPlaceholderOnLoad: function() {
		'use strict';

		if ( ! tsf.hasInput )
			return;

		var $input = tsf.docTitles();

		if ( typeof $input.val() !== "undefined" ) {
			if ( $input.val().length > 0 ) {
				$input.trigger( 'input', tsf.dynamicPlaceholder );
			} else {
				$input.trigger( 'input', tsf.updateCharacterCountTitle );
			}
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
		'use strict';

		if ( ! tsf.hasInput )
			return;

		var $input = tsf.docDescriptions();

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
		'use strict';

		if ( ! tsf.hasInput )
			return;

		var $input = tsf.docTitles();

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
		'use strict';

		//* Prevent trigger of settings change
		tsf.settingsChanged = false;
	},

	/**
	 * Dismissible notices. Uses class .tsf-notice.
	 *
	 * @since 2.6.0
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	dismissNotice: function( event ) {
		'use strict';

		var $this = jQuery( event.target );

		$this.parents( '.tsf-notice' ).slideUp( 200, function() {
			$this.remove();
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
		'use strict';

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
		'use strict';

		var newclass = 'tsf-success',
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
		'use strict';

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
		'use strict';

		// Count up, reset to 0 if needed. We have 4 options: 0, 1, 2, 3
		tsf.counterType = tsf.counterType + 1;
		if ( tsf.counterType > 3 )
			tsf.counterType = 0;

		//* Update counters locally.
		tsf.additionsClassInit();

		var target = '.tsf-counter .tsf-ajax',
			status = 0;

		//* Reset ajax loader
		tsf.resetAjaxLoader( target );

		//* Set ajax loader.
		tsf.setAjaxLoader( target );

		//* Setup external update.
		var settings = {
			method: 'POST',
			url: ajaxurl,
			datatype: 'json',
			data: {
				'action' : 'the_seo_framework_update_counter',
				'nonce' : tsf.nonce,
				'val' : tsf.counterType,
			},
			async: true,
			success: function( response ) {

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
		'use strict';

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
		'use strict';

		if ( ! tsf.hasInput )
			return;

		var counterType = tsf.counterType,
			cache = tsf.settingsChanged;

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
		tsf.settingsChanged = cache;
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
		'use strict';

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
		'use strict';

		return tsf.i18n[ type ];
	},

	/**
	 * Returns converted HTML title/description separator.
	 *
	 * @since 2.7.0
	 *
	 * @function
	 * @param {String} type
	 * @return {String} sep The converted separator.
	 */
	getSep: function( type ) {
		'use strict';

		if ( 'title' === type ) {
			var sep = tsf.titleSeparator;
		} else {
			var sep = tsf.descriptionSeparator;
		}

		if ( 'pipe' === sep || '|' === sep ) {
			sep = ( "|" );
		} else if ( 'dash' === sep || '-' === sep ) {
			sep = ( "-" );
		} else if ( sep.charCodeAt(0) < 123 ) {
			//* Checked for UTF-8 conversion.
			// Create a memory div to store the html in, convert to text to append in $placeholder
			sep = jQuery.trim( sep );
			if ( '&' === sep.charAt(0) && ';' === sep.slice(-1) )
				sep = sep.substr(1).slice(0, -1);

			sep = jQuery( '<div/>' ).html( "&" + sep + ";" ).text();
		}

		return sep;
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
		'use strict';

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

		frame.on( 'select', function() {
			frame.setState( 'cropper' );
		} );

		frame.on( 'cropped', function( croppedImage ) {

			var url = croppedImage.url,
				attachmentId = croppedImage.id,
				w = croppedImage.width,
				h = croppedImage.height;

			// Send the attachment id to our hidden input. URL to explicit output.
			jQuery( '#' + inputID + '-url' ).val( url );
			jQuery( '#' + inputID + '-id' ).val( attachmentId );
		} );

		frame.on( 'skippedcrop', function( selection ) {

			var url = selection.get( 'url' ),
				attachmentId = selection.get( 'id' ),
				w = selection.get( 'width' ),
				h = selection.get( 'height' );

			// Send the attachment id to our hidden input. URL to explicit output.
			jQuery( '#' + inputID + '-url' ).val( url );
			jQuery( '#' + inputID + '-id' ).val( attachmentId );
		} );

		frame.on( 'skippedcrop cropped', function( imageSelection ) {
			jQuery( '#' + inputID + '-select' ).text( tsf.other[ inputID ]['change'] );
			jQuery( '#' + inputID + '-url' ).prop( 'readonly', true ).css( 'opacity', 0 ).animate(
				{ 'opacity' : 1 },
				{ 'queue' : true, 'duration' : 1000 },
				'swing'
			);

			tsf.appendRemoveButton( $target, inputID, true );
			tsf.registerChange();
		} );

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
		'use strict';

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
		var Controller = wp.media.controller,
			TSFCropper;

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

				return wp.ajax.post( 'crop-image', {
					'nonce' : attachment.get( 'nonces' ).edit,
					'id' : attachment.get( 'id' ),
					'context' : 'tsf-image',
					'cropDetails' : cropDetails
				} );
			}
		} );

		TSFCropper.prototype.control = {};
		TSFCropper.control = {
			'params' : {
				'flex_width' : 1500,
				'flex_height' : 1500,
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
	 * @function
	 * @param {wp.media.model.Attachment} attachment
	 * @param {wp.media.controller.Cropper} controller
	 * @return {Object} imgSelectOptions
	 */
	calculateImageSelectOptions: function( attachment, controller ) {
		'use strict';

		var control = tsf.cropper.control,
			flexWidth  = !! parseInt( control.params.flex_width, 10 ),
			flexHeight = !! parseInt( control.params.flex_height, 10 ),
			realWidth  = attachment.get( 'width' ),
			realHeight = attachment.get( 'height' ),
			xInit = parseInt( control.params.width, 10 ),
			yInit = parseInt( control.params.height, 10 ),
			ratio = xInit / yInit,
			xImg  = xInit,
			yImg  = yInit,
			x1, y1, imgSelectOptions;

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
	 * @function
	 * @param {Number} dstW
	 * @param {Number} dstH
	 * @param {Number} imgW
	 * @param {Number} imgH
	 * @return {Boolean}
	 */
	mustBeCropped: function( dstW, dstH, imgW, imgH ) {
		'use strict';

		if ( imgW <= dstW && imgH <= dstH ) {
			return false;
		}

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

		var $buttons = jQuery( '.tsf-set-social-image' );

		if ( $buttons.length ) {
			var inputID = '',
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

		var $selectors = jQuery( '.tsf-color-picker' );

		if ( $selectors.length ) {
			jQuery.each( $selectors, function( index, value ) {
				var $input = jQuery( value ),
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

		// Mixed string and int (i10n is string, JS is int).
		tsf.counterType = tsf.states['counterType'];

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
	 * @param {!jQuery} jQ jQuery
	 * @return {undefined}
	 */
	ready: function( jQ ) {
		'use strict';

		// Set up object parameters.
		tsf.setupVars();

		// Move the page updates notices below the tsf-top-wrap.
		jQ( 'div.updated, div.error, div.notice-warning' ).insertAfter( 'div.tsf-top-wrap' );

		// Set up additions classes.
		jQ( document.body ).ready( tsf.additionsClassInit );

		// Toggle Dynamic Title Placeholder onLoad, also toggles doing it right colors.
		jQ( document.body ).ready( tsf.dynamicPlaceholderOnLoad );

		// Check if the Title Tagline or Description Additions should be removed when page is loaded.
		jQ( document.body ).ready( tsf.taglineToggleOnload );

		// Initialize the status bar hover balloon.
		jQ( document.body ).ready( tsf.statusBarHover );

		// Initialize status bar removal hover for touch screens.
		jQ( document.body ).on( 'click touchstart MSPointerDown', tsf.removeDesc );

		// Initialize image uploader button cache.
		jQ( document.body ).ready( tsf.setupImageEditorActions );

		// Determine image editor button input states.
		jQ( document.body ).ready( tsf.checkImageEditorInput );

		// Color picker
		jQ( document.body ).ready( tsf.setColorOnload );

		// #== End Before Change listener

		// Initialise form field changing flag.
		jQ( document.body ).ready( tsf.attachUnsavedChangesListener );

		// Deregister changes.
		jQ( document.body ).ready( tsf.onLoadUnregisterChange );

		// #== Start After Change listener

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
		jQ( '.tsf-tab' ).on( 'click', tsf.tabToggle );

		// Toggle Title tagline aditions removal.
		jQ( '#tsf-title-tagline-toggle :input' ).on( 'click', tsf.taglineToggleTitle );
		// Toggle Title additions location.
		jQ( '#tsf-title-location input' ).on( 'click', tsf.titleLocationToggle );
		// Toggle Title prefixes display.
		jQ( '#title-prefixes-toggle :input' ).on( 'click', tsf.titlePrefixToggle );

		// Toggle Description additions removal.
		jQ( '#tsf-description-onblogname-toggle :input' ).on( 'click', tsf.taglineToggleDesc );
		jQ( '#tsf-description-additions-toggle :input' ).on( 'click', tsf.additionsToggleDesc );

		// Change Home Page Title Example prop on input changes.
		jQ( '#autodescription-site-settings\\[homepage_title\\]' ).on( 'input', tsf.titleProp );
		jQ( '#tsf-home-title-location :input, #tsf-title-tagline-toggle :input, #tsf-title-separator input' ).on( 'click', tsf.taglinePropTrigger );
		jQ( '#autodescription-site-settings\\[homepage_title_tagline\\]' ).on( 'input', tsf.taglineProp );

		// Make sure the titleProp is correctly rendered when revealed after being hidden.
		jQ( '#homepage-tab-general' ).on( 'change', tsf.taglinePropTrigger );

		// Change Global Title Example prop on input changes.
		jQ( '#autodescription-site-settings\\[title_rem_additions\\]' ).on( 'click', tsf.titleToggle );

		// Dynamic Placeholder, acts on keydown for a11y, although more cpu intensive. Acts on keyup for perfect output.
		tsf.docTitles().on( 'input', tsf.dynamicPlaceholder );

		// Move click on dynamic additions to focus input behind.
		jQ( '#tsf-title-placeholder' ).on( 'click', tsf.selectTitleInput );

		// Dismiss notices.
		jQ( '.tsf-dismiss' ).on( 'click', tsf.dismissNotice );

		// AJAX counter
		jQ( '.tsf-counter' ).on( 'click', tsf.counterUpdate );

	}
};
jQuery( tsf.ready );
