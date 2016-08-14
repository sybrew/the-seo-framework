/**
 * This file holds The SEO Framework plugin's JS code.
 * Serve JavaScript as an addition, not as a means.
 *
 * @author Sybre Waaijer https://cyberwire.nl/
 * @pluginURI https://wordpress.org/plugins/autodescription/
 *
 * @credits StudioPress (http://www.studiopress.com/) for some code.
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
// @output_file_name autodescription.min.js
// @externs_url https://raw.githubusercontent.com/google/closure-compiler/master/contrib/externs/jquery-1.9.js
// @js_externs /** @constructor */ function autodescription() {}; /** @function */ autodescription.statusBarHover; /** @type {Array|string} */ var autodescriptionL10n;
// ==/ClosureCompiler==
// http://closure-compiler.appspot.com/home

/* global autodescription, confirm, autodescriptionL10n  */

/**
 * Advanced Optimizations caused me to move away from dot annotations, as they
 * get wrongfully minified.
 */

/**
 * Holds AutoDescription values in an object to avoid polluting global namespace.
 *
 * @since 2.2.4
 *
 * @constructor
 */
window[ 'autodescription' ] = {

	settingsChanged: false,

	titleTagline : autodescriptionL10n['titleTagline'],

	/**
	 * @since 2.7.0
	 * @param {String} Ajax nonce
	 */
	nonce : autodescriptionL10n['nonce'],

	/**
	 * Mixed string and int (i10n is string, JS is int).
	 * @param {String|int} autodescription.counterType
	 */
	counterType : autodescriptionL10n['counterType'],

	additionsClass : '',

	/**
	 * Cached doctitle function.
	 *
	 * @since 2.3.3
	 *
	 * @function
	 *
	 * @returns {Object} The jQuery doctitle ID's
	 */
	docTitles: function() {
		'use strict';

		var $doctitles = jQuery( '#autodescription_title, #autodescription-meta\\[doctitle\\], #autodescription-site-settings\\[homepage_title\\]' );

		return $doctitles;
	},

	/**
	 * Cached description function.
	 *
	 * @since 2.5.0
	 *
	 * @function
	 *
	 * @returns {Object} The jQuery description ID's
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
	 *
	 * @param {String} text The text to display.
	 * @return {Boolean|null}
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
	 * @param {jQuery.event} event
	 */
	updateCharacterCountDescription: function( event ) {
		'use strict';

		var $this = jQuery( event.target ),
			$length = $this.val().length,
			$phLength = $this.attr( 'placeholder' ).length,
			$counter = jQuery( '#' + autodescription.escapeStr( event.target.id ) + '_chars' ),
			$additionsClass = autodescription.additionsClass,
			$counterType = autodescription.counterType,
			$counterClass = '',
			$name = '',
			$output = '';

		// Emptied input, get Description placeholder.
		if ( 0 === $length ) {
			//* Output length from placeholder.
			$length = $phLength;
		}

		if ( $length < 100 || $length >= 175 ) {
			$counterClass = 'tsf-count-bad';
			$name = autodescription.getCounterName( 'bad' );
		} else if ( $length < 137 || ( $length > 155 && $length < 175 ) ) {
			$counterClass = 'tsf-count-okay';
			$name = autodescription.getCounterName( 'okay' );
		} else {
			$counterClass = 'tsf-count-good';
			$name = autodescription.getCounterName( 'good' );
		}

		if ( $additionsClass )
			$counterClass += ' ' + $additionsClass;

		if ( ! $counterType || 1 == $counterType ) {
			$output = $length.toString();
		} else if ( 2 == $counterType ) {
			$output = $name;
		} else if ( 3 == $counterType ) {
			$output = $length.toString() + ' - ' + $name;
		}

		$counter.html( $output ).removeClass().addClass( $counterClass );
	},

	/**
	 * Title length counter, with special characters
	 *
	 * @since 2.2.4
	 *
	 * @function
	 * @param {jQuery.event} event
	 */
	updateCharacterCountTitle: function( event ) {
		'use strict';

		var $this = jQuery( event.target ),
			$additions = autodescriptionL10n['titleAdditions'].length,
			$description = autodescriptionL10n['blogDescription'].length,
			$siteTitle = autodescriptionL10n['siteTitle'].length,
			$titleLength = $this.val().length,
			$placeholder = $this.attr( 'placeholder' ).length,
			$tagline = jQuery( '#autodescription-site-settings\\[homepage_title_tagline\\]' ).val(),
			$seplen = 3,
			$counter = jQuery( '#' + autodescription.escapeStr( event.target.id ) + '_chars' ),
			$length = 0,
			$additionsClass = autodescription.additionsClass,
			$counterType = autodescription.counterType,
			$counterClass = '',
			$name = '',
			$output = '';

		// Additions or tagline removed, remove additions and separator.
		if ( ! autodescription.titleTagline ) {
			$additions = 0;
			$seplen = 0;
		}

		// Emptied input, get Site title.
		if ( 0 === $titleLength ) {
			if ( 0 !== $siteTitle ) {
				$titleLength = $siteTitle;
			} else {
				//* Output length from placeholder.
				$length = $placeholder;
			}
		}

		// Length should be something now.
		if ( 0 !== $titleLength ) {

			if ( 0 !== $additions && typeof $tagline !== 'undefined' ) {
				var $tagLength = $tagline.length;

				// Replace $additions with $tagline is $tagline isn't empty.
				if ( 0 !== $tagLength ) {
					$additions = $tagLength;
				} else {
					$additions = $description;
				}
			}

			// Put it all together
			if ( 0 === $additions ) {
				$length = $titleLength;
			} else {
				$length = $titleLength + $seplen + $additions;
			}
		}

		if ( $length < 25 || $length >= 75 ) {
			$counterClass = 'tsf-count-bad';
			$name = autodescription.getCounterName( 'bad' );
		} else if ( $length < 42 || ( $length > 55 && $length < 75 ) ) {
			$counterClass = 'tsf-count-okay';
			$name = autodescription.getCounterName( 'okay' );
		} else {
			$counterClass = 'tsf-count-good';
			$name = autodescription.getCounterName( 'good' );
		}

		if ( $additionsClass )
			$counterClass += ' ' + $additionsClass;

		if ( ! $counterType || 1 == $counterType ) {
			$output = $length.toString();
		} else if ( 2 == $counterType ) {
			$output = $name;
		} else if ( 3 == $counterType ) {
			$output = $length.toString() + ' - ' + $name;
		}

		$counter.html( $output ).removeClass().addClass( $counterClass );
	},

	/**
	 * Escapes HTML strings.
	 *
	 * @since 2.2.4
	 *
	 * @function
	 *
	 * @param {String} str
	 * @return {String} HTML to jQuery converted string
	 */
	escapeStr: function( str ) {
		'use strict';

		if ( str )
			return str.replace(/([\[\]\/])/g,'\\$1');

		return str;
	},

	/**
	 * Dynamic Title separator replacement in metabox
	 *
	 * @since 2.2.2
	 *
	 * @function
	 * @param {jQuery.event} event
	 */
	separatorSwitch: function( event ) {
		'use strict';

		var $sep = jQuery( ".autodescription-sep-js" ),
			$val = jQuery( event.target ).val();

		if ( 'pipe' === $val ) {
			$sep.text( " | " );
		} else if ( 'dash' === $val ) {
			$sep.text( " - " );
		} else {
			$sep.html( " &" + $val + "; " );
		}
	},

	/**
	 * Dynamic Description separator replacement in metabox
	 *
	 * @since 2.3.4
	 *
	 * @function
	 * @param {jQuery.event} event
	 */
	separatorSwitchDesc: function( event ) {
		'use strict';

		var $sep = jQuery( "#autodescription-descsep-js" ),
			$val = jQuery( event.target ).val();

		if ( 'pipe' === $val ) {
			$sep.text( " | " );
		} else if ( 'dash' === $val ) {
			$sep.text( " - " );
		} else {
			$sep.html( " &" + $val + "; " );
		}
	},

	/**
	 * Status bar description init on hover actions.
	 *
	 * @since 2.1.9
	 *
	 * @function
	 */
	statusBarHover: function() {
		'use strict';

		var $wrap = jQuery( '.tsf-seo-bar-inner-wrap' ).find( 'a' );

		$wrap.on( "mouseenter", autodescription.statusBarHoverEnter );
		$wrap.on( "mousemove", autodescription.statusBarHoverMove );
		$wrap.on( "mouseleave", autodescription.statusBarHoverLeave );

	},

	/**
	 * Status bar description output on hover enter.
	 *
	 * @since 2.6.0
	 *
	 * @function
	 */
	statusBarHoverEnter: function() {
		'use strict';

		var $this = jQuery( this ),
			$thisDesc = $this.attr( 'data-desc' );

		if ( $thisDesc !== undefined && 0 === $this.find( 'div' ).length ) {
			$this.append( '<div class="tsf-explanation-desc">' + $thisDesc + '<div></div></div>' );

			var $thisHeight = $this.find( 'div.tsf-explanation-desc' ).height() + 28;

			$this.find( 'div.tsf-explanation-desc' ).css( 'top', ( $this.position().top - $thisHeight ) + 'px' );
		}
	},

	/**
	 * Status bar description output on hover move.
	 *
	 * @since 2.6.0
	 *
	 * @function
	 * @param {jQuery.event} event
	 */
	statusBarHoverMove: function( event ) {
		'use strict';

		var $this = jQuery( event.target ),
			$pagex = event.pageX,
			$mousex = $pagex - jQuery( '.tsf-seo-bar-inner-wrap' ).offset().left - 11, // 22px width of arrow / 2 = 11 middle
			$balloon = $this.find( '.tsf-explanation-desc' ),
			$arrow = $balloon.find( 'div' );

		if ( $mousex < 1 ) {
			$arrow.css( 'left', 0 + "px" );
		} else if ( $balloon.offset() !== undefined ) {
			var $width = $balloon.width(),
				$maxOffset = $balloon.offset().left + $width + 11;

			if ( $pagex > $maxOffset ) {
				$arrow.css( 'left', $width + "px" );
			} else {
				$arrow.css( 'left', $mousex + "px" );
			}
		}
	},


	/**
	 * Status bar description removal on hover leave.
	 *
	 * @since 2.6.0
	 *
	 * @function
	 */
	statusBarHoverLeave: function() {
		'use strict';

		jQuery( this ).find( 'div.tsf-explanation-desc' ).remove();
	},

	/**
	 * Remove Status bar desc if clicked outside (touch support)
	 *
	 * @since 2.1.9
	 *
	 * @function
	 * @param {jQuery.event} event
	 */
	removeDesc: function( event ) {
		'use strict';

		var $this = jQuery( event.target ),
			$desc = jQuery('.tsf-seo-bar-inner-wrap a');

		if ( ! $this.closest( $desc ).length )
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
	 * @param {jQuery.event} event
	 */
	tabToggle: function( event ) {
		'use strict';

		var $target = jQuery( event.target ).attr( 'id' ),
			$name = jQuery( event.target ).attr( 'name' );

		if ( typeof $target !== 'undefined' ) {
			var $content = jQuery( '#' + $target + '-content' ),
				$other = jQuery( '.' + $name + '-content' );

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
	 * @param {jQuery.event} event
	 */
	taglineToggle: function( event ) {
		'use strict';

		var $this = jQuery( event.target ),
			$tag = jQuery( '.tsf-custom-blogname-js' );

		if ( $this.is( ':checked' ) ) {
			$tag.css( 'display', 'inline' );
			autodescription.titleTagline = true;
		} else {
			$tag.css( 'display', 'none' );
			autodescription.titleTagline = false;
		}

		autodescription.docTitles().trigger( 'keyup', autodescription.updateCharacterCountTitle );
	},

	/**
	 * Toggle tagline within the Description Example.
	 *
	 * @since 2.3.4
	 *
	 * @function
	 * @param {jQuery.event} event
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
	 * @param {jQuery.event} event
	 */
	titleLocationToggle: function( event ) {
		'use strict';

		var $this = jQuery( event.target ).val(),
			$titleExampleLeft = jQuery( '.tsf-title-additions-example-left' ),
			$titleExampleRight = jQuery( '.tsf-title-additions-example-right' );

		if ( 'right' === $this ) {
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
	 * @param {jQuery.event} event
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
	 * @param {jQuery.event} event
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
	 * @param {jQuery.event} event
	 */
	taglineToggleOnload: function( event ) {
		'use strict';

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
	 * @param {jQuery.event} event
	 */
	titleProp: function( event ) {
		'use strict';

		var $val = jQuery( event.target ).val(),
			$title = jQuery( '.custom-title-js' );

		if ( $val.length === 0 ) {
			$title.text( autodescriptionL10n['siteTitle'] );
		} else {
			$title.text( $val );
		}

	},

	/**
	 * Change Title based on input of the Custom Title
	 *
	 * @since 2.3.8
	 *
	 * @function
	 * @param {jQuery.event} event
	 */
	taglineProp: function( event ) {
		'use strict';

		var $val = jQuery( event.target ).val(),
			$floatTag = jQuery( '.tsf-custom-tagline-js' ),
			$target = jQuery( '#autodescription-site-settings\\[homepage_title\\]' ),
			$leftRight = jQuery( '#tsf-home-title-location input:checked' ).val(),
			$toggle = jQuery( '#autodescription-site-settings\\[homepage_tagline\\]' ),
			$title = autodescriptionL10n['siteTitle'],
			$placeholder = $title,
			$description = autodescriptionL10n['blogDescription'],
			$sep = jQuery( '#tsf-title-separator input:checked' ).val(),
			$sepOutput = autodescriptionL10n['titleSeparator'];

		if ( $toggle.is( ':checked' ) ) {

			if ( $val.length !== 0 ) {
				$description = $val;
			}

			if ( $sep.length !== 0 ) {
				if ( 'pipe' === $sep ) {
					$sepOutput = ( "|" );
				} else if ( 'dash' === $sep ) {
					$sepOutput = ( "-" );
				} else {
					// Create a memory div to store the html in, convert to text to append in $placeholder
					$sepOutput = jQuery( '<div/>' ).html( "&" + $sep + ";" ).text();
				}
			}

			if ( $leftRight.length !== 0 && 'left' === $leftRight ) {
				$placeholder = $title + ' ' + $sepOutput + ' ' + $description;
			} else {
				$placeholder = $description + ' ' + $sepOutput + ' ' + $title;
			}

		}

		$floatTag.text( $description );
		$target.attr( "placeholder", $placeholder );

		// Notify tagline has changed.
		autodescription.docTitles().trigger( 'input', autodescription.updateCharacterCountTitle );
	},

	/**
	 * Trigger Change on Left/Right selection of Home Page Title
	 *
	 * @since 2.5.0
	 *
	 * @function
	 */
	taglinePropTrigger: function() {
		'use strict';

		jQuery( "#autodescription-site-settings\\[homepage_title_tagline\\]" ).trigger( 'input', autodescription.taglineProp );
	},

	/**
	 * Trigger Change on Left/Right selection of Global Title
	 *
	 * @since 2.5.2
	 *
	 * @function
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
	 */
	attachUnsavedChangesListener: function() {
		'use strict';

		jQuery( '.tsf-metaboxes :input, #tsf-inpost-box .inside :input' ).not( '.tsf-tab :input' ).change( function() {
			autodescription.registerChange();
		});

		jQuery( '.tsf-metaboxes input[type=text], .tsf-metaboxes textarea, #tsf-inpost-box .inside input[type=text], #tsf-inpost-box .inside textarea' ).not( '.tsf-nav-tab-wrapper :input' ).on( 'input', function() {
			autodescription.registerChange();
		});

		window.onbeforeunload = function() {
			if ( autodescription.settingsChanged ) {
				return autodescriptionL10n['saveAlert'];
			}
		};

		jQuery( '.tsf-metaboxes input[type="submit"], #publishing-action input[type="submit"], #save-action input[type="submit"], a.submitdelete' ).click( function() {
			window.onbeforeunload = null;
		});
	},

	/**
	 * Set a flag, to indicate form fields have changed.
	 *
	 * @since 2.2.4
	 *
	 * @function
	 */
	registerChange: function() {
		'use strict';

		autodescription.settingsChanged = true;
	},

	/**
	 * Ask user to confirm that settings should now be reset.
	 *
	 * @since 2.2.4
	 *
	 * @function
	 *
	 * @return {Boolean|null} True if reset should occur, false if not.
	 */
	confirmedReset: function() {
		'use strict';

		return confirm( autodescriptionL10n['confirmReset'] );
	},

	/**
	 * Adds dynamic placeholder to Title input based on site settings.
	 *
	 * @since 2.5.0
	 *
	 * @function
	 * @param {jQuery.event} event
	 *
	 * @return {String} the placeholder additions.
	 */
	dynamicPlaceholder: function( event ) {
		'use strict';

		var $hasAdditions = autodescriptionL10n['titleAdditions'].length,
			$placeholder = jQuery( '#tsf-title-placeholder' );

		// If check is defined, we're on SEO settings page.
		if ( 0 === $hasAdditions ) {
			var $this = jQuery( event.target );

			// Empty the placeholder as we can't execute.
			$this.css( 'text-indent', "initial" );
			return $placeholder.empty();
		}

		var $after = false,
			$check = jQuery( '#tsf-home-title-location input:checked' ).val(),
			$rtl = autodescriptionL10n['isRTL'],
			$additions = '';

		if ( typeof $check !== 'undefined' && $check.length !== 0 ) {
			//* We're in SEO Settings page.

			if ( '1' === $rtl ) {
				if ( 'right' === $check ) {
					$after = true;
				}
			} else {
				if ( 'left' === $check ) {
					$after = true;
				}
			}
		} else {
			//* We're in post/page edit screen.

			var $isHome = autodescriptionL10n['isHome'],
				$titleLocation = autodescriptionL10n['titleLocation'],
				$tagline = autodescription.titleTagline;

			// We're on post/page screen.
			if ( '1' === $isHome ) {
				// Static Front page, switch check.
				if ( '1' === $tagline ) {
					if ( '1' === $rtl ) {
						if ( 'right' === $titleLocation ) {
							$after = true;
						}
					} else if ( 'left' === $titleLocation ) {
						$after = true;
					}
				}
			} else {
				if ( '1' === $rtl ) {
					if ( 'left' === $titleLocation ) {
						$after = true;
					}
				} else if ( 'right' === $titleLocation ) {
					$after = true;
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
				$inputVal = $this.val(),
				$offsetTest = jQuery( "#tsf-title-offset" ),
				$offsetWidth = 0,
				$heightPad = ( $this.outerHeight( true ) - $this.height() ) / 2,
				$horPad = ( $this.outerWidth() - $this.width() ) / 2,
				$leftOffset = ( $this.outerWidth( true ) - $this.width() ) / 2,
				$taglineVal = jQuery( "#autodescription-site-settings\\[homepage_title_tagline\\]" ).val(),
				$pos = 'left',
				$separator = autodescriptionL10n['titleSeparator'];

			if ( '1' === $rtl ) {
				$pos = 'right';
			}

			if ( typeof $taglineVal !== "undefined" && $taglineVal.length === 0) {
				$taglineVal = autodescriptionL10n['blogDescription'];
			}

			if ( $after ) {
				$additions = $separator + " " + autodescriptionL10n['titleAdditions'];

				// Exchange the placeholder value of the custom Tagline in the HomePage Metabox
				if ( typeof $taglineVal !== "undefined" && $taglineVal.length > 0 ) {
					$additions = $separator + " " + $taglineVal;
				}

				$this.css( 'text-indent', "initial" );
			} else {
				$additions = autodescriptionL10n['titleAdditions'] + " " + $separator;

				// Exchange the placeholder value of the custom Tagline in the HomePage Metabox
				if ( typeof $taglineVal !== "undefined" && $taglineVal.length > 0 ) {
					$additions = $taglineVal + " " + $separator;
				}
			}

			// Width offset container, copy variables and remain hidden.
			$offsetTest.text( $inputVal );
			$offsetTest.css({
				fontFamily: $this.css( "fontFamily" ),
				fontWeight: $this.css( "fontWeight" ),
				letterSpacing: $this.css( "letterSpacing" ),
				fontSize: $this.css( "fontSize" ),
			});
			$offsetWidth = $offsetTest.width();

			var $maxWidth = $this.width() - $horPad - $offsetWidth;

			if ( $maxWidth < 0 )
				$maxWidth = 0;

			// Moving Placeholder output
			$placeholder.css({
				display: $this.css( "display" ),
				lineHeight: $this.css( "lineHeight" ),
				paddingTop: $heightPad + "px",
				paddingBottom: $heightPad + "px",
				fontFamily: $this.css( "fontFamily" ),
				fontWeight: $this.css( "fontWeight" ),
				fontSize: $this.css( "fontSize" ),
				letterSpacing: $this.css( "letterSpacing" ),
				maxWidth: $maxWidth + "px",
			});

			//* Empty or fill placeholder and offsets.
			if ( typeof $inputVal === "undefined" || $inputVal.length < 1 ) {

				if ( ! $after )
					$this.css( 'text-indent', "initial" );

				$placeholder.empty();
			} else {
				$placeholder.text( $additions );

				// Don't calculate when empty.
				if ( $this.outerWidth() > $leftOffset ) {
					if ( $after ) {
						$placeholder.css( $pos, $horPad + $leftOffset + $offsetTest.width() + "px" );
					} else {
						var $indent = $horPad + $placeholder.width();

						if ( $indent < 0 )
							$indent = 0;

						$placeholder.css( $pos, $leftOffset + "px" );
						$this.css( 'text-indent', $indent + "px" );
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
	 */
	selectTitleInput: function() {
		'use strict';

		var $input = autodescription.docTitles();

		$input.focus();

		if ( $input.setSelectionRange ) {
			// Go to end times 2 if setSelectionRange exists.
			var $length = $input.val().length * 2;
			$input.setSelectionRange( $length, $length );
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
	 */
	dynamicPlaceholderOnLoad: function() {
		'use strict';

		var $input = autodescription.docTitles();

		if ( typeof $input.val() !== "undefined" ) {
			if ( $input.val().length > 0 ) {
				$input.trigger( 'input', autodescription.dynamicPlaceholder );
			} else {
				$input.trigger( 'input', autodescription.updateCharacterCountTitle );
			}
		}
	},

	/**
	 * Triggers keyup on description input so the counter can colorize.
	 *
	 * @since 2.5.0
	 *
	 * @function
	 */
	triggerDescriptionOnLoad: function() {
		'use strict';

		var $input = autodescription.docDescriptions();

		$input.trigger( 'input', autodescription.updateCharacterCountDescription );
	},


	/**
	 * Triggers keyup on title input so the counter can colorize.
	 *
	 * @since 2.6.0
	 *
	 * @function
	 */
	triggerTitleOnLoad: function() {
		'use strict';

		var $input = autodescription.docTitles();

		$input.trigger( 'input', autodescription.updateCharacterCountTitle );
	},

	/**
	 * OnLoad changes can affect settings changes. This function reverts those.
	 *
	 * @since 2.5.0
	 *
	 * @function
	 */
	onLoadUnregisterChange: function() {
		'use strict';

		//* Prevent trigger of settings change
		autodescription.settingsChanged = false;
	},

	/**
	 * Dismissible notices. Uses class .tsf-notice.
	 *
	 * @since 2.6.0
	 *
	 * @function
	 * @param {jQuery.event} event
	 */
	dismissNotice: function( event ) {
		'use strict';

		var $this = jQuery( event.target );

		$this.parents( '.tsf-notice' ).slideUp( 200, function() {
			$this.remove();
		});

	},

	/**
	 * Visualizes AJAX loading time through target class change.
	 *
	 * @since 2.7.0
	 *
	 * @function
	 * @param {String} target
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
	 */
	unsetAjaxLoader: function( target, success ) {
		'use strict';

		var $newclass = 'tsf-success',
			$fade = 2500;

		if ( ! success ) {
			$newclass = 'tsf-error';
			$fade = 5000;
		}

		jQuery( target ).removeClass( 'tsf-loading' ).addClass( $newclass ).fadeOut( $fade );
	},

	/**
	 * Cleans and resets Ajax wrapper class and contents to default.
	 * Also stops any animation and resets fadeout to beginning.
	 *
	 * @since 2.7.0
	 *
	 * @function
	 * @param {String} target
	 */
	resetAjaxLoader: function( target ) {
		'use strict';

		jQuery( target ).stop().empty().attr( 'class', 'tsf-ajax' ).css( 'opacity', '1' ).removeAttr( 'style' );
	},

	/**
	 * Updates the counter type.
	 *
	 * @since 2.6.0
	 *
	 * @function
	 * @param {jQuery.event} event
	 */
	counterUpdate: function( event ) {
		'use strict';

		// Count up, reset to 0 if needed. We have 4 options: 0, 1, 2, 3
		autodescription.counterType = autodescription.counterType + 1;
		if ( autodescription.counterType > 3 )
			autodescription.counterType = 0;

		//* Update counters locally.
		autodescription.additionsClassInit();

		var $target = '.tsf-counter .tsf-ajax',
			$status = 0;

		//* Reset ajax loader
		autodescription.resetAjaxLoader( $target );

		//* Set ajax loader.
		autodescription.setAjaxLoader( $target );

		//* Setup external update.
		var settings = {
			method: 'POST',
			url: ajaxurl,
			datatype: 'json',
			data: {
				'action' : 'the_seo_framework_update_counter',
				'nonce' : autodescription.nonce,
				'val' : autodescription.counterType,
			},
			async: true,
			success: function( response ) {

				response = jQuery.parseJSON( response );

				//* I could do value check, but that will simply lag behind. Unless an annoying execution delay is added.
				if ( 'success' === response.type )
					$status = 1;

				autodescription.counterUpdatedResponse( $target, $status );
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
	 * @param {Integer} success
	 */
	counterUpdatedResponse: function( target, success ) {
		'use strict';

		switch ( success ) {
			case 0:
				autodescription.unsetAjaxLoader( target, false );
				break;
			case 1:
				autodescription.unsetAjaxLoader( target, true );
				break;
			default:
				autodescription.resetAjaxLoader( target );
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
	 */
	additionsClassInit: function() {
		'use strict';

		/**
		 * Mixed string and int (i10n is string, JS is int).
		 * @param {String|int} $counterType
		 * @param {Boolean} $settingsChanged
		 */
		var $counterType = autodescription.counterType,
			$settingsChanged = autodescription.settingsChanged;

		if ( 1 == $counterType ) {
			autodescription.additionsClass = 'tsf-counter-one';
			autodescription.counterType = 1;
		} else if ( 2 == $counterType ) {
			autodescription.additionsClass = 'tsf-counter-two';
			autodescription.counterType = 2;
		} else if ( 3 == $counterType ) {
			autodescription.additionsClass = 'tsf-counter-three';
			autodescription.counterType = 3;
		} else {
			autodescription.additionsClass = 'tsf-counter-zero';
			autodescription.counterType = 0;
		}

		autodescription.updateCounters();

		// Reset settingschanges to previous value.
		autodescription.settingsChanged = $settingsChanged;
	},

	/**
	 * Update counters.
	 *
	 * @since 2.6.0
	 *
	 * @function
	 */
	updateCounters: function() {
		'use strict';

		autodescription.triggerTitleOnLoad();
		autodescription.triggerDescriptionOnLoad();
	},

	/**
	 * Returns counter name.
	 *
	 * @since 2.6.0
	 * @function
	 *
	 * @param {String|null} type
	 * @return {String} Human Readable Counter name.
	 */
	getCounterName: function( type ) {
		'use strict';

		var name = autodescriptionL10n[type];

		return name;
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
	 *
	 * @function
	 */
	ready: function() {
		'use strict';

		// Move the page updates notices below the tsf-top-wrap.
		jQuery( 'div.updated, div.error, div.notice-warning' ).insertAfter( 'div.tsf-top-wrap' );

		// Set up additions classes.
		jQuery( document.body ).ready( autodescription.additionsClassInit );

		// Toggle Dynamic Title Placeholder onLoad, also toggles doing it right colors.
		jQuery( document.body ).ready( autodescription.dynamicPlaceholderOnLoad );

		// Check if the Title Tagline or Description Additions should be removed when page is loaded.
		jQuery( document.body ).ready( autodescription.taglineToggleOnload );

		// Initialize the status bar hover balloon.
		jQuery( document.body ).ready( autodescription.statusBarHover );

		// Initialize status bar removal hover for touch screens.
		jQuery( document.body ).on( 'click touchstart MSPointerDown', autodescription.removeDesc );

		// #== Before Change listener

		// Initialise form field changing flag.
		autodescription.attachUnsavedChangesListener();

		// Deregister changes.
		jQuery( document.body ).ready( autodescription.onLoadUnregisterChange );

		// #== After Change listener

		// Bind character counters.
		autodescription.docDescriptions().on( 'input', autodescription.updateCharacterCountDescription );
		autodescription.docTitles().on( 'input', autodescription.updateCharacterCountTitle );

		// Allow the title separator to be changed dynamically.
		jQuery( '#tsf-title-separator input' ).on( 'click', autodescription.separatorSwitch );
		// Allow description separator to be changed dynamically.
		jQuery( '#tsf-description-separator input' ).on( 'click', autodescription.separatorSwitchDesc );

		// Bind reset confirmation.
		jQuery( '.autodescription-js-confirm-reset' ).on( 'click.autodescription.autodescription_confirm_reset', autodescription.confirmedReset );

		// Toggle Tabs in the SEO settings page.
		jQuery( '.tsf-tab' ).on( 'click', autodescription.tabToggle );

		// Toggle Title tagline aditions removal.
		jQuery( '#tsf-title-tagline-toggle :input' ).on( 'click', autodescription.taglineToggle );
		// Toggle Title additions location.
		jQuery( '#tsf-title-location input' ).on( 'click', autodescription.titleLocationToggle );
		// Toggle Title prefixes display.
		jQuery( '#title-prefixes-toggle :input' ).on( 'click', autodescription.titlePrefixToggle );

		// Toggle Description additions removal.
		jQuery( '#tsf-description-onblogname-toggle :input' ).on( 'click', autodescription.taglineToggleDesc );
		jQuery( '#tsf-description-additions-toggle :input' ).on( 'click', autodescription.additionsToggleDesc );

		// Change Home Page Title Example prop on input changes.
		jQuery( '#autodescription-site-settings\\[homepage_title\\]' ).on( 'input', autodescription.titleProp );
		jQuery( '#tsf-home-title-location :input, #tsf-title-tagline-toggle :input, #tsf-title-separator input' ).on( 'click', autodescription.taglinePropTrigger );
		jQuery( '#autodescription-site-settings\\[homepage_title_tagline\\]' ).on( 'input', autodescription.taglineProp );

		// Make sure the titleProp is correctly rendered when revealed after being hidden.
		jQuery( '#homepage-tab-general' ).on( 'change', autodescription.taglinePropTrigger );

		// Change Global Title Example prop on input changes.
		jQuery( '#autodescription-site-settings\\[title_rem_additions\\]' ).on( 'click', autodescription.titleToggle );

		// Dynamic Placeholder, acts on keydown for a11y, although more cpu intensive. Acts on keyup for perfect output.
		autodescription.docTitles().on( 'input', autodescription.dynamicPlaceholder );

		// Move click on dynamic additions to focus input behind.
		jQuery( '#tsf-title-placeholder' ).on( 'click', autodescription.selectTitleInput );

		// Dismiss notices.
		jQuery( '.tsf-dismiss' ).on( 'click', autodescription.dismissNotice );

		// AJAX counter
		jQuery( '.tsf-counter' ).on( 'click', autodescription.counterUpdate );

	}

};
jQuery( autodescription.ready );
