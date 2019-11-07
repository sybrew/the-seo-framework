/**
 * This file holds The SEO Framework plugin's JS code for the SEO Settings page.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/autodescription/>
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Holds tsfSettings values in an object to avoid polluting global namespace.
 *
 * @since 4.0.0
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfSettings = function( $ ) {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 4.0.0
	 * @access public
	 * @type {(Object<string, *>)|boolean|null} l10n Localized strings
	 */
	const l10n = 'undefined' !== typeof tsfSettingsL10n && tsfSettingsL10n;

	/**
	 * Initializes input helpers for the General Settings.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	const _initGeneralSettings = () => {
		/**
		 * Triggers displaying/hiding of character counters on the settings page.
		 *
		 * @since 4.0.0
		 * @access private
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const togglePixelCounterDisplay = ( event ) => {
			if ( $( event.target ).is( ':checked' ) ) {
				$( '.tsf-pixel-counter-wrap' ).show();
				//= Pixels couldn't be counted when it was hidden.
				tsfC.triggerCounterUpdate();
			} else {
				$( '.tsf-pixel-counter-wrap' ).hide();
			}
		}

		$( '#autodescription-site-settings\\[display_character_counter\\]' ).on( 'click', togglePixelCounterDisplay );
		$( '#autodescription-site-settings\\[display_pixel_counter\\]' ).on( 'click', togglePixelCounterDisplay );
	}

	/**
	 * Enables wpColorPicker on input.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	const _initColorPicker = () => {

		let $selectors = $( '.tsf-color-picker' );

		if ( $selectors.length ) {
			$.each( $selectors, ( index, value ) => {
				let $input = $( value ),
					currentColor = '',
					defaultColor = $input.data( 'tsf-default-color' );

				$input.wpColorPicker( {
					defaultColor: defaultColor,
					width: 238,
					change: ( event, ui ) => {
						currentColor = $input.wpColorPicker( 'color' );

						if ( '' === currentColor )
							currentColor = defaultColor;

						$input.val( currentColor );

						tsfAys.registerChange();
					},
					clear: () => {
						// We can't loop this to the change method, as it's not reliable (due to deferring?).
						// So, we just fill it in for the user.
						if ( defaultColor.length ) {
							$input.val( defaultColor );
							$input.closest( '.wp-picker-container' ).find( '.wp-color-result' ).css( 'backgroundColor', defaultColor );
						}
						tsfAys.registerChange();
					},
					palettes: false,
				} );
			} );
		}
	}

	/**
	 * Initializes Titles' meta input.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initTitleSettings = () => {

		let additionsToggle = document.getElementById( '#autodescription-site-settings[title_rem_additions]' );

		/**
		 * Toggles example on Left/Right selection of global title options.
		 *
		 * @function
		 * @return {undefined}
		 */
		const toggleAdditionsDisplayExample = () => {
			let $exampleAdditions  = $( '.tsf-title-additions-js' );

			if ( $( additionsToggle ).is( ':checked' ) ) {
				$exampleAdditions.css( 'display', 'none' );
			} else {
				$exampleAdditions.css( 'display', 'inline' );
			}
		}
		$( additionsToggle ).on( 'change', toggleAdditionsDisplayExample );
		$( additionsToggle ).trigger( 'change' );

		/**
		 * Toggles title additions location for the Title examples.
		 * There are two elements, rather than one. One is hidden by default.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const toggleAdditionsLocationExample = ( event ) => {
			let $titleExampleLeft  = $( '.tsf-title-additions-example-left' ),
				$titleExampleRight = $( '.tsf-title-additions-example-right' );

			if ( 'right' === $( event.target ).val() ) {
				$titleExampleLeft.css( 'display', 'none' );
				$titleExampleRight.css( 'display', 'inline' );
			} else {
				$titleExampleLeft.css( 'display', 'inline' );
				$titleExampleRight.css( 'display', 'none' );
			}
		}
		$( '#tsf-title-location input' ).on( 'click', toggleAdditionsLocationExample );

		/**
		 * Toggles title prefixes for the Prefix Title example.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const adjustPrefixExample = ( event ) => {
			let $this   = $( event.target ),
				$prefix = $( '.tsf-title-prefix-example' );

			if ( $this.is(':checked') ) {
				$prefix.css( 'display', 'none' );
			} else {
				$prefix.css( 'display', 'inline' );
			}
		}
		$( '#tsf-title-prefixes-toggle :input' ).on( 'click', adjustPrefixExample );

		/**
		 * Updates used separator and all examples thereof.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const updateSeparator = ( event ) => {
			let val       = event.target.value,
				separator = '';

			switch ( val ) {
				case 'pipe':
					separator = '|';
					break;

				default:
					// XSS ok: val is sanitized by PHP: s_title_separator().
					separator = $( '<div/>' ).html( "&" + val + ";" ).text();
					break;
			}
			$( ".tsf-sep-js" ).text( ' ' + separator + ' ' );

			$( window ).trigger( 'tsf-title-sep-updated', [ separator ] );
		}
		$( '#tsf-title-separator :input' ).on( 'click', updateSeparator );
	}

	/**
	 * Initializes Homepage's meta title input.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initHomeTitleSettings = () => {

		const
			titleInput    = document.getElementById( 'autodescription-site-settings[homepage_title]' ),
			taglineInput  = document.getElementById( 'autodescription-site-settings[homepage_title_tagline]' ),
			taglineToggle = document.getElementById( 'autodescription-site-settings[homepage_tagline]' );

		const protectedPrefix = tsf.escapeString( l10n.i18n.protectedTitle );
		const privatePrefix   = tsf.escapeString( l10n.i18n.privateTitle );

		tsfTitle.setInputElement( titleInput );

		/**
		 * Updates the hover additions placement.
		 *
		 * @function
		 * @return {undefined}
		 */
		const setHoverAdditionsPlacement = () => {
			let oldPlacement = tsfTitle.getState( 'additionPlacement' ),
				placement    = 'after';

			if ( tsf.l10n.states.isRTL ) {
				if ( 'right' === $( '#tsf-home-title-location input:checked' ).val() ) {
					placement = 'before';
				}
			} else {
				if ( 'left' === $( '#tsf-home-title-location input:checked' ).val() ) {
					placement = 'before';
				}
			}

			if ( placement !== oldPlacement ) {
				tsfTitle.updateState( 'additionPlacement', placement );
			}
		}
		setHoverAdditionsPlacement();
		$( '#tsf-home-title-location' ).on( 'click', ':input', setHoverAdditionsPlacement );

		/**
		 * Sets private/protected visibility state.
		 *
		 * @function
		 * @param {string} visibility
		 * @return {undefined}
		 */
		const setTitleVisibilityPrefix = ( visibility ) => {

			let oldPrefixValue = tsfTitle.getState( 'prefixValue' ),
				prefixValue    = '';

			switch ( visibility ) {
				case 'password':
					prefixValue = protectedPrefix;
					break;

				case 'private':
					prefixValue = privatePrefix;
					break;

				default:
				case 'public':
					prefixValue = '';
					break;
			}

			if ( prefixValue !== oldPrefixValue )
				tsfTitle.updateState( 'prefixValue', prefixValue );
		}

		if ( l10n.states.isFrontPrivate ) {
			setTitleVisibilityPrefix( 'private' );
		} else if ( l10n.states.isFrontProtected ) {
			setTitleVisibilityPrefix( 'password' );
		}

		/**
		 * Adjusts homepage left/right title example part.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const adjustHomepageExampleOutput = ( event ) => {
			let $example = $( '.tsf-custom-title-js' ),
				val      = tsf.decodeEntities( tsf.sDoubleSpace( event.target.value.trim() ) );

			if ( val.length ) {
				$example.html( tsf.escapeString( val ) );
			} else {
				$example.html( tsf.escapeString( tsf.decodeEntities( tsfTitle.getState( 'defaultTitle' ) ) ) );
			}
		};
		$( titleInput ).on( 'input.tsfInputTitle', adjustHomepageExampleOutput );
		$( titleInput ).trigger( 'input.tsfInputTitle' );

		let updateHomePageTaglineExampleOutputBuffer,
			$exampleTagline = $( '.tsf-custom-tagline-js' );
		/**
		 * Updates homepage title example output.
		 * Has high debounce timer, as it's crucially visible on the input screen anyway.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const updateHomePageTaglineExampleOutput = () => {

			clearTimeout( updateHomePageTaglineExampleOutputBuffer );

			updateHomePageTaglineExampleOutputBuffer = setTimeout( () => {
				let value = tsfTitle.getState( 'additionValue' );

				value = tsf.decodeEntities( tsf.sDoubleSpace( value.trim() ) );

				if ( value.length && tsfTitle.getState( 'useTagline' ) ) {
					$exampleTagline.html( tsf.escapeString( value ) );
					$( '.tsf-custom-blogname-js' ).show();
				} else {
					$( '.tsf-custom-blogname-js' ).hide();
				}
			} );
		}

		/**
		 * Updates the hover additions value.
		 *
		 * @function
		 * @return {undefined}
		 */
		const updateHoverAdditionsValue = () => {
			let oldValue = tsfTitle.getState( 'additionValue' ),
				value    = taglineInput.value.trim();

			if ( ! value.length ) {
				value = taglineInput.placeholder || '';
			}

			value = tsf.escapeString( tsf.decodeEntities( value.trim() ) );

			if ( oldValue !== value ) {
				tsfTitle.updateState( 'additionValue', value );
				updateHomePageTaglineExampleOutput();
			}
		}
		$( taglineInput ).on( 'input.tsfInputTagline', updateHoverAdditionsValue );
		$( taglineInput ).trigger( 'input.tsfInputTagline' );

		/**
		 * Toggle tagline end examples within the Left/Right example for the homepage titles.
		 * Also disables the input field for extra clarity.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const toggleHomePageTaglineExampleDisplay = ( event ) => {
			let useTagline = false;

			if ( event.target.checked ) {
				useTagline            = true;
				taglineInput.readOnly = false;
			} else {
				useTagline            = false;
				taglineInput.readOnly = true;
			}

			// A change action implies a change. Don't test for previous; it changed!
			// (also, it defaults to false; which would cause a bug not calling updateHomePageTaglineExampleOutput on-load)
			tsfTitle.updateState( 'useTagline', useTagline );
			updateHomePageTaglineExampleOutput();
		}
		$( taglineToggle ).on( 'change.tsfToggleTagline', toggleHomePageTaglineExampleDisplay );
		$( taglineToggle ).trigger( 'change.tsfToggleTagline' );

		/**
		 * Updates separator used in the titles.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @param {string}        separator
		 * @return {undefined}
		 */
		const updateSeparator = ( event, separator ) => {
			tsfTitle.updateState( 'separator', separator );
		}
		$( window ).on( 'tsf-title-sep-updated', updateSeparator );

		tsfTitle.enqueueUnregisteredInputTrigger();
	}

	/**
	 * Initializes Homepage's meta description input.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initHomeDescriptionSettings = () => {

		tsfDescription.setInputElement( document.getElementById( 'autodescription-site-settings[homepage_description]' ) );

		tsfDescription.enqueueUnregisteredInputTrigger();
	}

	/**
	 * Initializes uncategorized general tab meta input listeners.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initHomeGeneralListeners = () => {

		/**
		 * Enqueues meta title and description input triggers
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @param {Element} elem
		 * @return {undefined}
		 */
		const enqueueGeneralInputListeners = () => {
			tsfTitle.enqueueUnregisteredInputTrigger();
			tsfDescription.enqueueUnregisteredInputTrigger();
		}

		/**
		 * Enqueues doctitles input trigger synchronously on postbox collapse or open.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @param {Element}       elem
		 * @return {undefined}
		 */
		const triggerPostboxSynchronousUnregisteredInput = function( event, elem ) {
			if ( 'autodescription-homepage-settings' === elem.id ) {
				let inside = elem.querySelector( '.inside' );
				if ( inside.offsetHeight > 0 && inside.offsetWidth > 0 ) {
					enqueueGeneralInputListeners();
				}
			}
		}
		$( document ).on( 'postbox-toggled', triggerPostboxSynchronousUnregisteredInput );

		$( '#tsf-homepage-tab-general' ).on( 'tsf-tab-toggled', tsfTitle.enqueueUnregisteredInputTrigger );
	}

	/**
	 * Initializes Robots' meta input.
	 *
	 * @since 4.0.2
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initRobotsInputs = () => {

		const $input = $( '#autodescription-site-settings\\[set_copyright_directives\\]' );

		const $controls = $( [
			"#autodescription-site-settings\\[max_snippet_length\\]",
			"#autodescription-site-settings\\[max_image_preview\\]",
			"#autodescription-site-settings\\[max_video_preview\\]",
		].join( ', ' ) );

		if ( ! $input.length || ! $controls.length ) return;

		/**
		 * Toggles control directive option states.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const togglePreviewControl = ( event ) => {
			if ( event.target.checked ) {
				$controls.prop( 'disabled', false );
				$( '.tsf-toggle-directives-surrogate' ).remove();
			} else {
				$controls.prop( 'disabled', true );
				$controls.each( ( i, element ) => {
					$( '<input />' )
						.attr( 'type', 'hidden' )
						.attr( 'name', element.name || '' )
						.val( element.value || 0 )
						.addClass( 'tsf-toggle-directives-surrogate' )
						.insertAfter( element );
				} );
			}
		}
		$input.on( 'change.tsfToggleDirectives', togglePreviewControl );
		$input.trigger( 'change.tsfToggleDirectives' );
	}

	/**
	 * Initializes Webmasters' meta input.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initWebmastersInputs = () => {

		const $inputs = $( [
			"#autodescription-site-settings\\[google_verification\\]",
			"#autodescription-site-settings\\[bing_verification\\]",
			"#autodescription-site-settings\\[yandex_verification\\]",
			"#autodescription-site-settings\\[pint_verification\\]",
		].join( ', ' ) );

		if ( ! $inputs.length ) return;

		/**
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const trimScript = ( event ) => {
			let val = event.originalEvent.clipboardData && event.originalEvent.clipboardData.getData('text') || void 0;

			if ( val ) {
				// Extrude tag paste's content value and set that as a value.
				let match = /<meta[^>]+content=(\"|\')?([^\"\'>\s]+)\1?.*?>/i.exec( val );
				if ( match && 2 in match && 'string' === typeof match[2] && match[2].length ) {
					event.stopPropagation();
					event.preventDefault(); // Prevents save listener
					event.target.value = match[2];
					// Tell change:
					tsfAys.registerChange();
				}
			}
		}
		$inputs.on( 'paste', trimScript );
	}

	/**
	 * Initializes settings scripts on TSF-load.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _loadSettings = () => {
		_initGeneralSettings();
		_initTitleSettings();

		_initHomeTitleSettings();
		_initHomeDescriptionSettings();
		_initHomeGeneralListeners();

		_initRobotsInputs();
		_initWebmastersInputs();
		_initColorPicker();
	}

	/**
	 * Initializes settings scripts on TSF-ready.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _readySettings = () => {

		tsfSocial.initTitleInputs( {
			ref:  document.getElementById( 'tsf-title-reference' ),
			meta: document.getElementById( 'autodescription-site-settings[homepage_title]' ),
			og:   document.getElementById( 'autodescription-site-settings[homepage_og_title]' ),
			tw:   document.getElementById( 'autodescription-site-settings[homepage_twitter_title]' ),
		} );

		tsfSocial.initDescriptionInputs( {
			ref:  document.getElementById( 'tsf-description-reference' ),
			meta: document.getElementById( 'autodescription-site-settings[homepage_description]' ),
			og:   document.getElementById( 'autodescription-site-settings[homepage_og_description]' ),
			tw:   document.getElementById( 'autodescription-site-settings[homepage_twitter_description]' ),
		} );
	}

	/**
	 * Ask user to confirm that settings should now be reset.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {(Boolean|null)} True if reset should occur, false if not.
	 */
	const _confirmedReset = () => {
		return confirm( l10n.i18n.confirmReset );
	}

	/**
	 * Sets a class to the active element which helps excluding focus rings.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {(undefined|null)}
	 */
	const _initTabs = () => {

		/**
		 * Sets correct tab content and classes on toggle.
		 *
		 * TODO We might want to debounce the showing of the content.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @param {undefined|true} onload
		 * @return {(undefined|null)}
		 */
		const tabToggle = ( event, onload ) => {

			let $currentToggle = $( event.target );

			if ( ! $currentToggle.is( ':checked' ) ) return;

			onload = typeof onload === 'boolean' ? onload : false;

			let toggleId   = event.target.id,
				toggleName = event.target.name;

			let activeClass       = 'tsf-active-tab-content',
				toggleActiveClass = 'tsf-tab-active',
				$previousContent  = $( '.' + activeClass ),
				$previousToggle   = $currentToggle.closest( '.tsf-nav-tab-wrapper' ).find( '.' + toggleActiveClass );

			//* Perform validity check, this prevents hidden browser validation errors.
			let $invalidInput = $previousContent.find( 'input:invalid, select:invalid, textarea:invalid' );
			if ( $invalidInput.length ) {
				$invalidInput[0].reportValidity();

				$previousToggle.prop( 'checked', true );
				$currentToggle.prop( 'checked', false );
				event.stopPropagation();
				event.preventDefault();
				return false; // stop propagation in jQuery.
			}

			let $newContent = $( '#' + toggleId + '-content' );

			//= Previous active-state logger.
			$previousToggle.removeClass( toggleActiveClass );
			$previousToggle.siblings( 'label' ).removeClass( 'tsf-no-focus-ring' );
			$currentToggle.addClass( toggleActiveClass );

			//* Only parse if old content isn't the new.
			if ( onload ) {
				let $allContent = $( '.' + toggleName + '-content' );
				$allContent.removeClass( activeClass ).hide();
				$newContent.addClass( activeClass ).show();
				$( '#' + toggleId ).trigger( 'tsf-tab-toggled' );
			} else if ( $newContent.length && ! $newContent.is( $previousContent ) ) {
				let $allContent = $( '.' + toggleName + '-content' );

				// Promises dont always complete, making for extraneous display.
				$allContent.fadeOut( 150, function() {
					$( this ).removeClass( activeClass );
				} );
				setTimeout( () => {
					$newContent.addClass( activeClass ).fadeIn( 250 );
				}, 150 );
				setTimeout( () => {
					$( '#' + toggleId ).trigger( 'tsf-tab-toggled' );
				}, 175 );
			}
		}
		$( '.tsf-tabs-radio' ).on( 'change', tabToggle );

		/**
		 * Sets a class to the active element which helps excluding focus rings.
		 *
		 * @see tabToggle Handles this HTML class.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {(undefined|null)}
		 */
		const _addNoFocusClass = ( event ) => {
			event.currentTarget.classList.add( 'tsf-no-focus-ring' );
		}
		$( '.tsf-nav-tab-wrapper' ).on( 'click.tsfNavTab', '.tsf-nav-tab', _addNoFocusClass );

		/**
		 * Sets the correct tab based on selected radio button on window.history navigation.
		 *
		 * @see tabToggle Handles this HTML class.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {(undefined|null)}
		 */
		const _correctTabFocus = () => {
			$( '.tsf-tabs-radio:checked' ).each( ( i, element ) => {
				$( element ).trigger( 'change', [ true ] );
			} );
		}
		$( document.body ).on( 'tsf-ready', _correctTabFocus );
	}

	return Object.assign( {
		/**
		 * Initialises all aspects of the scripts.
		 * You shouldn't call this.
		 *
		 * @since 4.0.0
		 * @since 4.0.3 Now also displaces notice-info.
		 * @access protected
		 *
		 * @function
		 * @return {undefined}
		 */
		load: () => {
			$( 'div.updated, div.error, div.notice, .notice-error, .notice-warning, .notice-info' ).insertAfter( '.tsf-top-wrap' )

			$( document.body ).on( 'tsf-onload', _loadSettings );
			$( document.body ).on( 'tsf-ready', _readySettings );

			// Bind reset confirmation.
			$( '.tsf-js-confirm-reset' ).on( 'click', _confirmedReset );

			// Initializes tabs early.
			_initTabs();
		}
	}, {}, {
		l10n
	} );
}( jQuery );
jQuery( window.tsfSettings.load );
