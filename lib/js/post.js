/**
 * This file holds The SEO Framework plugin's JS code for the Post SEO Settings.
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
 * Holds tsfPost values in an object to avoid polluting global namespace.
 *
 * This is a self-constructed function assigned as an object.
 *
 * @since 3.3.0
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfPost = function( $ ) {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 3.3.0
	 * @access private
	 * @type {(Object<string, *>)|boolean|null} l10n Localized strings
	 */
	let l10n = 'undefined' !== typeof tsfPostL10n && tsfPostL10n;

	/**
	 * Refines Styling for the navigation tabs on the settings pages
	 *
	 * @since 3.3.0
	 * @access private
	 * @todo merge with tsfSettings.tabToggle or a collective method?
	 * @TODO add more debouncing.
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @param {undefined|true} onload
	 * @return {(undefined|null)}
	 */
	const _flexTabToggle = ( event, onload ) => {

		let $currentToggle = $( event.target );

		if ( ! $currentToggle.is( ':checked' ) ) return;

		onload = typeof onload === 'boolean' ? onload : false;

		let toggleId   = event.target.id,
			toggleName = event.target.name;

		let activeClass       = 'tsf-flex-tab-content-active',
			toggleActiveClass = 'tsf-flex-tab-active',
			$previousContent  = $( '.' + activeClass ),
			$previousToggle   = $currentToggle.closest( '.tsf-flex-nav-tab-wrapper' ).find( '.' + toggleActiveClass );

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
			$( '#' + toggleId ).trigger( 'tsf-flex-tab-toggled' );
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
				jQuery( '#' + toggleId ).trigger( 'tsf-flex-tab-toggled' );
			}, 175 );
		}
	}
	/**
	 * Registers on resize/orientationchange listeners and debounces to only run
	 * at set intervals.
	 *
	 * For Flexbox implementation.
	 *
	 * @since 3.3.0
	 * @access private
	 * @TODO use more debouncing.
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	const _doFlexResizeListener = () => {

		if ( ! $( '.tsf-flex' ).length ) return;

		//* Set event listeners.
		tsf._setResizeListeners();

		let _resizeTimeout = 0,
			_lastWidth    = {},
			_timeOut       = 0;

		// Warning: Only checks for the first item existence.
		let $tabWrapper = $( '.tsf-flex-nav-tab-wrapper' ),
			$window     = $( window );

		$window.on( 'tsf-flex-resize', () => {

			clearTimeout( _resizeTimeout );

			// Onload delays are 0, after than it's 10, 20 and 30 respectively.
			let _delay = 0;

			_resizeTimeout = setTimeout( () => {
				if ( $tabWrapper.length ) {
					// Flex Tab Wrapper.
					let $innerWrap = $( '.tsf-flex-nav-tab-inner' ),
						outerWrapWidth = $tabWrapper.width(),
						innerWrapWidth = $innerWrap.width(),
						$navName = $( '.tsf-flex-nav-name' );

					if ( ! _lastWidth.tabWrapper ) {
						_lastWidth.tabWrapper = {};
						_lastWidth.tabWrapper.outer = 0;
						_lastWidth.tabWrapper.inner = 0;
						_lastWidth.tabWrapper.shown = 1;
					}

					// First run, revealed, or testing for new width. Either way, fadeIn.
					if ( ! _lastWidth.tabWrapper.shown && _lastWidth.tabWrapper.outer < outerWrapWidth ) {
						/**
						 * If ANYONE can find a way that doesn't make it flicker
						 * without using clones with stripped IDs/names, let me know.
						 * https://github.com/sybrew/the-seo-framework/issues/new
						 * https://github.com/sybrew/the-seo-framework/compare
						 */
						$navName.fadeIn( 250 );

						// Wait for 10 ms for slow browsers.
						setTimeout( () => {
							// Recalulate inner width (outer didn't change):
							innerWrapWidth = $innerWrap.width();
						}, _delay );
					}

					// Wait for an additional 10 ms for slow browsers.
					setTimeout( () => {
						if ( innerWrapWidth > outerWrapWidth ) {
							// Overflow (can be first run).
							$navName.hide();
							_lastWidth.tabWrapper.shown = 0;
						} else if ( _lastWidth.tabWrapper.outer < outerWrapWidth ) {
							// Grown or first run.
							$navName.fadeIn( 250 );
							_lastWidth.tabWrapper.shown = 1;
						}
					}, _delay * 2 );

					// Wait for an additional 10 ms for slow browsers.
					setTimeout( () => {
						_lastWidth.tabWrapper.outer = outerWrapWidth;
						_lastWidth.tabWrapper.inner = innerWrapWidth;
					}, _delay * 3 );
				}
			}, _timeOut );

			// Update future timeouts.
			_delay = 10;
			_timeOut = 75;
		} );

		//* Trigger after setup.
		$window.trigger( 'tsf-flex-resize' );
	}

	/**
	 * Sets a class to the active element which helps excluding focus rings.
	 *
	 * @since 3.3.0
	 * @access private
	 * @see _flexTabToggle Handles this class.
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {(undefined|null)}
	 */
	const _addNoFocusClass = ( event ) => {
		event.currentTarget.classList.add( 'tsf-no-focus-ring' );
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
		//= Triggers inpost change event for tabs. There's only one active tab.
		$( '.tsf-flex-nav-tab-radio:checked' ).trigger( 'change', [ true ] );
	}

	/**
	 * Initializes Canonical URL meta input.
	 *
	 * @since 3.3.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initCanonicalInput = () => {

		let canonicalInput = $( '#autodescription_canonical' );

		if ( ! canonicalInput ) return;

		const updateCanonical = ( link ) => {
			canonicalInput.attr( 'placeholder', link );
		}

		$( document ).on( 'tsf-updated-gutenberg-link', ( event, link ) => updateCanonical( link ) );
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
		// Initializes canonical URL input fields.
		_initCanonicalInput();
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
		// Initializes flex tab positions.
		_initTabs();

		// Initializes flex tab resize listeners.
		_doFlexResizeListener();
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
			$( document.body ).on( 'tsf-onload', _loadSettings );
			$( document.body ).on( 'tsf-ready', _readySettings );

			// Toggle tabs for the inpost Flex settings.
			$( '.tsf-flex-nav-tab-radio' ).on( 'change', _flexTabToggle );
			$( '.tsf-flex-nav-tab-wrapper' ).on( 'click.tsfFlexNav', '.tsf-flex-nav-tab-label', _addNoFocusClass );
		}
	}, {} );
}( jQuery );
jQuery( window.tsfPost.load );
