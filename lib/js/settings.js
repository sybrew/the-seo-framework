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
 * This is a self-constructed function assigned as an object.
 *
 * @since 3.3.0
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfSettings = function( $ ) {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 3.3.0
	 * @access private
	 * @type {(Object<string, *>)|boolean|null} l10n Localized strings
	 */
	let l10n = 'undefined' !== typeof tsfSettingsL10n && tsfSettingsL10n;

	/**
	 * Enables wpColorPicker on input.
	 *
	 * @since 3.3.0
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

						tsf.registerChange();
					},
					clear: () => {
						// We can't loop this to the change method, as it's not reliable (due to deferring?).
						// So, we just fill it in for the user.
						if ( defaultColor.length ) {
							$input.val( defaultColor );
							$input.closest( '.wp-picker-container' ).find( '.wp-color-result' ).css( 'backgroundColor', defaultColor );
						}
						tsf.registerChange();
					},
					palettes: false,
				} );
			} );
		}
	}

	/**
	 * Toggle tagline end examples within the Left/Right example for the
	 * HomePage Title or Description.
	 *
	 * @since 3.3.0
	 *
	 * @function
	 * @return {undefined}
	 */
	const _setHomepageTagline = () => {

		let $tagTitle          = $( '#tsf-title-tagline-toggle :input' ),
			$title             = $( '.tsf-custom-blogname-js' ),
			$tagTitleAdditions = $( '#tsf-title-additions-toggle :input' ),
			$titleAdditions    = $( '.tsf-title-additions-js' );

		if ( $tagTitle.is( ':checked' ) ) {
			$title.css( 'display', 'inline' );
		} else {
			$title.css( 'display', 'none' );
		}

		// Reverse option.
		if ( $tagTitleAdditions.is( ':checked' ) ) {
			$titleAdditions.css( 'display', 'none' );
		} else {
			$titleAdditions.css( 'display', 'inline' );
		}
	}

	/**
	 * Initializes Titles' meta input.
	 *
	 * @since 3.3.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initTitleInputs = () => {
		/**
		 * Triggers Change on Left/Right selection of global title options.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const toggleExampleDisplay = ( event ) => {
			if ( $( event.target ).is( ':checked' ) ) {
				$( '.tsf-title-additions-js' ).css( 'display', 'none' );
			} else {
				$( '.tsf-title-additions-js' ).css( 'display', 'inline' );
			}
		}
		$( '#autodescription-site-settings\\[title_rem_additions\\]' ).on( 'click', toggleExampleDisplay );

		/**
		 * Toggles title additions location for the Title examples.
		 * There are two elements, rather than one. One is hidden by default.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const toggleExampleLocation = ( event ) => {
			let $titleExampleLeft = $( '.tsf-title-additions-example-left' ),
				$titleExampleRight = $( '.tsf-title-additions-example-right' );

			if ( 'right' === $( event.target ).val() ) {
				$titleExampleLeft.css( 'display', 'none' );
				$titleExampleRight.css( 'display', 'inline' );
			} else {
				$titleExampleLeft.css( 'display', 'inline' );
				$titleExampleRight.css( 'display', 'none' );
			}
		}
		$( '#tsf-title-location input' ).on( 'click', toggleExampleLocation );

		/**
		 * Adjusts homepage left/right title example part.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const adjustHomepageExampleOutput = ( event ) => {
			let val    = event.target.value || '',
				$title = $( '.tsf-custom-title-js' );

			if ( 0 === val.length ) {
				$title.text( tsf.params.defaultTitle );
			} else {
				$title.text( val );
			}
		};
		$( '#autodescription-site-settings\\[homepage_title\\]' ).on( 'input', adjustHomepageExampleOutput );

		/**
		 * Adjusts homepage left/right title example part.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const adjustHomepageTaglineExampleOutput = ( event ) => {
			let val = event.target.value || '',
				$tagline = $( '.tsf-custom-tagline-js' );

			val = tsf.escapeString( tsf.sDoubleSpace( val.trim() ) );

			if ( val.length ) {
				$tagline.html( val );
				$( '#tsf-home-title-location .tsf-sep-js' ).show();
			} else {
				$tagline.text( tsf.params.blogDescription );

				if ( 0 === tsf.params.blogDescription.length ) {
					$( '#tsf-home-title-location .tsf-sep-js' ).hide();
				} else {
					$( '#tsf-home-title-location .tsf-sep-js' ).show();
				}
			}
		};
		$( '#autodescription-site-settings\\[homepage_title_tagline\\]' ).on( 'input.tsfInputTagline', adjustHomepageTaglineExampleOutput );
		$( '#autodescription-site-settings\\[homepage_title_tagline\\]' ).trigger( 'input.tsfInputTagline' );

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
	}

	/**
	 * Initializes Webmasters' meta input.
	 *
	 * @since 3.3.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initWebmastersInputs = () => {

		let $inputs = $( [
			"#autodescription-site-settings\\[google_verification\\]",
			"#autodescription-site-settings\\[bing_verification\\]",
			"#autodescription-site-settings\\[yandex_verification\\]",
			"#autodescription-site-settings\\[pint_verification\\]"
		].join( ', ' ) );

		if ( ! $inputs.length ) return;

		$inputs.on( 'paste', ( event ) => {
			let val = event.originalEvent.clipboardData && event.originalEvent.clipboardData.getData('text') || void 0;

			if ( val ) {
				// Extrude tag paste's content value and set that as a value.
				let match = /<meta[^>]+content=(\"|\')?([^\"\'>\s]+)\1?.*?>/i.exec( val );
				if ( match && 2 in match && 'string' === typeof match[2] && match[2].length ) {
					event.stopPropagation();
					event.preventDefault(); // Prevents save listener
					event.target.value = match[2];
					// Tell change:
					tsf.registerChange();
				}
			}
		} );
	}

	/**
	 * Sets the navigation tabs content equal to the buttons.
	 *
	 * @since 3.3.0
	 * @see _tabToggle
	 * @access private
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	const _initTabs = () => {
		// Triggers change event on all tabs at ready state.
		$( '.tsf-tabs-radio:checked' ).each( ( i, element ) => {
			$( element ).trigger( 'change', [ true ] );
		} );
	}

	/**
	 * Initializes settings scripts on TSF-load.
	 *
	 * @since 3.3.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _loadSettings = () => {

		// Set Color Picker input and helpers.
		_initColorPicker();

		// Check if the Title Tagline or Description Additions should be removed when page is loaded.
		_setHomepageTagline();

		// Initialize Title settings' input field helpers.
		_initTitleInputs();

		// Initialize Webmaster settings' input field helpers.
		_initWebmastersInputs();
	}

	/**
	 * Initializes settings scripts on TSF-ready.
	 *
	 * @since 3.3.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _readySettings = () => {
		_initTabs();
	}

	/**
	 * Ask user to confirm that settings should now be reset.
	 *
	 * @since 3.3.0
	 * @access private
	 *
	 * @function
	 * @return {(Boolean|null)} True if reset should occur, false if not.
	 */
	const _confirmedReset = () => {
		return confirm( l10n.i18n.confirmReset );
	}

	/**
	 * Sets correct tab content and classes on toggle.
	 *
	 * TODO We might want to debounce the showing of the content.
	 *
	 * @since 3.3.0
	 * @see _initTabs
	 * @access private
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @param {undefined|true} onload
	 * @return {(undefined|null)}
	 */
	const _tabToggle = ( event, onload ) => {

		let $currentToggle = jQuery( event.target );

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

	/**
	 * Sets a class to the active element which helps excluding focus rings.
	 *
	 * @since 3.3.0
	 * @see _tabToggle Handles this class.
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {(undefined|null)}
	 */
	const _addNoFocusClass = ( event ) => {
		event.currentTarget.classList.add( 'tsf-no-focus-ring' );
	}

	return Object.assign( {
		/**
		 * Initialises all aspects of the scripts.
		 * You shouldn't call this.
		 *
		 * @since 3.3.0
		 * @access protected
		 *
		 * @function
		 * @return {undefined}
		 */
		load: () => {
			// Move the page updates notices below the tsf-top-wrap.
			$( '.updated, .error, .notice-error, .notice-warning' ).insertAfter( '.tsf-top-wrap' );

			$( document.body ).on( 'tsf-onload', _loadSettings );
			$( document.body ).on( 'tsf-ready', _readySettings );

			// Bind reset confirmation.
			$( '.tsf-js-confirm-reset' ).on( 'click', _confirmedReset );

			// Toggle tabs in the SEO settings page.
			$( '.tsf-tabs-radio' ).on( 'change', _tabToggle );
			$( '.tsf-nav-tab-wrapper' ).on( 'click.tsfNavTab', '.tsf-nav-tab', _addNoFocusClass );
		}
	}, {} );
}( jQuery );
jQuery( window.tsfSettings.load );
