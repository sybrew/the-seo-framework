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
 * @since 4.0.0
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfPost = function( $ ) {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 4.0.0
	 * @access public
	 * @type {(Object<string, *>)|boolean|null} l10n Localized strings
	 */
	const l10n = 'undefined' !== typeof tsfPostL10n && tsfPostL10n;

	/**
	 * Refines styling for the navigation tabs on the settings pages.
	 *
	 * @since 4.0.0
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
				$( '#' + toggleId ).trigger( 'tsf-flex-tab-toggled' );
			}, 175 );
		}
	}

	/**
	 * Registers on resize/orientationchange listeners and debounces to only run
	 * at set intervals.
	 *
	 * For Flexbox implementation.
	 *
	 * @since 4.0.0
	 * @access private
	 * @TODO rewrite: make this seamless, especially during page-load.
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	const _doFlexResizeListener = () => {

		if ( ! $( '.tsf-flex' ).length ) return;

		let _resizeTimeout = 0,
			_lastWidth    = {},
			_timeOut       = 0;

		// Warning: Only checks for the first item existence.
		let $tabWrapper = $( '.tsf-flex-nav-tab-wrapper' ),
			$innerWrap  = $( '.tsf-flex-nav-tab-inner' ),
			$navNames   = $( '.tsf-flex-nav-name' );

		if ( ! $tabWrapper.length ) return;

		$( window ).on( 'tsf-flex-resize', () => {

			clearTimeout( _resizeTimeout );

			// Onload delays are 0, after than it's 10, 20 and 30 respectively.
			let _delay = 0;

			_resizeTimeout = setTimeout( () => {
				let outerWrapWidth = $tabWrapper.width(),
					innerWrapWidth = $innerWrap.width();

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
					 */
					$navNames.fadeIn( 250 );

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
						$navNames.hide();
						_lastWidth.tabWrapper.shown = 0;
					} else if ( _lastWidth.tabWrapper.outer < outerWrapWidth ) {
						// Grown or first run.
						$navNames.fadeIn( 250 );
						_lastWidth.tabWrapper.shown = 1;
					}
				}, _delay * 2 );

				// Wait for an additional 10 ms for slow browsers.
				setTimeout( () => {
					_lastWidth.tabWrapper.outer = outerWrapWidth;
					_lastWidth.tabWrapper.inner = innerWrapWidth;
				}, _delay * 3 );
			}, _timeOut );

			// Update future timeouts.
			_delay = 10;
			_timeOut = 75;
		} );

		/**
		 * Triggers resize on event.
		 *
		 * @function
		 * @return {(undefined|null)}
		 */
		const triggerResize = () => {
			$( window ).trigger( 'tsf-flex-resize' );
		}
		$( window ).on( 'resize orientationchange', triggerResize );
		$( '#collapse-menu' ).on( 'click', triggerResize );
		$( '.columns-prefs :input[type=radio]' ).on( 'change', triggerResize );
		$( '.meta-box-sortables' ).on( 'sortupdate', triggerResize );

		//* Trigger after setup.
		triggerResize();
	}

	/**
	 * Sets a class to the active element which helps excluding focus rings.
	 *
	 * @since 4.0.0
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
	 * @since 4.0.0
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
	 * Initializes canonical URL meta input listeners.
	 *
	 * @since 4.0.0
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
	 * Initializes title meta input listeners.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initTitleListeners = () => {

		tsfTitle.setInputElement( document.getElementById( 'autodescription_title' ) );

		tsfTitle.updateState( 'allowReferenceChange', ! l10n.params.refTitleLocked );

		const protectedPrefix = tsf.escapeString( l10n.i18n.protectedTitle );
		const privatePrefix   = tsf.escapeString( l10n.i18n.privateTitle );

		/**
		 * Updates title additions, based on singular settings change.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const updateTitleAdditions = ( event ) => {

			let prevUseTagline = tsfTitle.getState( 'useTagline' ),
				useTagline     = ! $( event.target ).is( ':checked' );

			if ( l10n.params.additionsForcedDisabled ) {
				useTagline = false;
			} else if ( l10n.params.additionsForcedEnabled ) {
				useTagline = true;
			}

			if ( prevUseTagline !== useTagline ) {
				tsfTitle.updateState( 'useTagline', useTagline );
			}
		}
		$( '#autodescription_title_no_blogname' ).on( 'change', updateTitleAdditions );
		$( '#autodescription_title_no_blogname' ).trigger( 'change' );

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
		$( document ).on( 'tsf-updated-gutenberg-visibility', ( event, visibility ) => setTitleVisibilityPrefix( visibility ) );

		/**
		 * Sets private/protected visibility state for the classic editor.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const setClassicTitleVisibilityPrefix = ( event ) => {
			let visibility = $( '#visibility' ).find( 'input:radio:checked' ).val();
			if ( 'password' === visibility ) {
				let pass = $( '#visibility' ).find( '#post_password' ).val();
				// A falsy-password (like '0'), will return true in "SOME OF" WP's front-end PHP, false in WP's JS before submitting...
				// It won't invoke WordPress' password protection. TODO FIXME: file WP Core bug report.
				if ( ! pass || ! pass.length ) {
					visibility = 'public';
				}
			}
			setTitleVisibilityPrefix( visibility );
		}
		$( '#visibility .save-post-visibility' ).on( 'click', setClassicTitleVisibilityPrefix );

		if ( l10n.states.isPrivate ) {
			setTitleVisibilityPrefix( 'private' );
		} else if ( l10n.states.isProtected ) {
			setTitleVisibilityPrefix( 'password' );
		}

		/**
		 * Updates default title placeholder.
		 *
		 * @function
		 * @param {string} value
		 * @return {undefined}
		 */
		const updateDefaultTitle = ( val ) => {
			val = typeof val === 'string' && val.trim() || '';

			let defaultTitle = l10n.params.stripTitleTags ? tsf.stripTags( val ) : val

			defaultTitle = defaultTitle || tsfTitle.untitledTitle;

			tsfTitle.updateState( 'defaultTitle', defaultTitle );
		}
		//= The homepage listens to a static preset value. Update all others.
		if ( ! l10n.params.isFront ) {
			$( '#titlewrap #title' ).on( 'input', event => updateDefaultTitle( event.target.value ) );
			$( document ).on( 'tsf-updated-gutenberg-title', ( event, title ) => updateDefaultTitle( title ) );
		}

		tsfTitle.enqueueUnregisteredInputTrigger();
	}

	/**
	 * Initializes description meta input listeners.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initDescriptionListeners = () => {

		tsfDescription.setInputElement( document.getElementById( 'autodescription_description' ) );

		tsfDescription.updateState( 'allowReferenceChange', ! l10n.params.refDescriptionLocked );

		// TODO set private/protected listeners, that will empty the generated description?
		// TODO set post-content (via ajax) listeners?

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
	const _initGeneralListeners = () => {

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
		 * Enqueues meta title and description input trigger synchronously on postbox collapse or open.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @param {Element} elem
		 * @return {undefined}
		 */
		const triggerPostboxSynchronousUnregisteredInput = function( event, elem ) {
			if ( 'tsf-inpost-box' === elem.id ) {
				let inside = elem.querySelector( '.inside' );
				if ( inside.offsetHeight > 0 && inside.offsetWidth > 0 ) {
					enqueueGeneralInputListeners();
				}
			}
		}
		$( document ).on( 'postbox-toggled', triggerPostboxSynchronousUnregisteredInput );

		$( '#tsf-flex-inpost-tab-general' ).on( 'tsf-flex-tab-toggled', enqueueGeneralInputListeners );
		$( window ).on( 'tsf-flex-resize', enqueueGeneralInputListeners );
	}

	/**
	 * Initializes tooltip boundaries.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initTooltipBoundaries = () => {

		if ( ! l10n.states.isGutenbergPage ) return;

		tsfTT.addBoundary( '#editor' );

			// Listen to the Gutenberg state changes.
		$( document ).on( 'tsf-gutenberg-sidebar-opened', () => {
			tsfTT.addBoundary( '.edit-post-sidebar .components-panel' );
		} );
	}

	/**
	 * Updates the SEO Bar and meta description placeholders on successful save.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initUpdateMetaBox = () => {

		if ( ! l10n.states.isGutenbergPage ) return;

		const seobar = document.querySelector( '.tsf-seo-bar' );

		// We only use this because it looks nice. The rest is implied via the counter updates.
		const seobarAjaxLoader = document.querySelector( '#tsf-doing-it-right-wrap .tsf-ajax' );

		const desc   = document.getElementById( 'autodescription_description' ),
			  ogDesc = document.getElementById( 'autodescription_og_description' ),
			  twDesc = document.getElementById( 'autodescription_twitter_description' );

		const imageUrl = document.getElementById( 'autodescription_socialimage-url' );

		const getData = {
			seobar:          !! seobar,
			metadescription: !! desc,
			ogdescription:   !! ogDesc,
			twdescription:   !! twDesc,
			imageurl:        !! imageUrl,
		};

		const onSuccess = response => {

			response = tsf.convertJSONResponse( response );

			switch ( response.type ) {
				case 'success':
					// Wait the same amount of time as the SEO Bar, so to sync the changes.
					const fadeTime = 75;

					setTimeout( () => {
						if ( tsfDescription ) {
							tsfDescription.updateState( 'defaultDescription', tsf.escapeString( response.data.metadescription.trim() ) );
						}
						if ( tsfSocial ) {
							tsfSocial.updateState( 'ogDescPlaceholder', tsf.escapeString( response.data.ogdescription.trim() ) );
							tsfSocial.updateState( 'twDescPlaceholder', tsf.escapeString( response.data.twdescription.trim() ) );
						}

						$( desc ).attr( 'placeholder', response.data.metadescription ).trigger( 'input' );
						$( ogDesc ).attr( 'placeholder', response.data.ogdescription ).trigger( 'input' );
						$( twDesc ).attr( 'placeholder', response.data.twdescription ).trigger( 'input' );

						$( imageUrl ).attr( 'placeholder', response.data.imageurl ).trigger( 'change' );

						tsfAys && tsfAys.reset();
					}, fadeTime );

					$( seobar )
						.fadeOut( fadeTime, () => {
							seobarAjaxLoader && tsf.unsetAjaxLoader( seobarAjaxLoader, true );
						} )
						.html( response.data.seobar )
						.fadeIn( 500, tsfTT.triggerReset );
					break;
				case 'failure':
					seobarAjaxLoader && tsf.unsetAjaxLoader( seobarAjaxLoader, false );
					break;
				default:
					seobarAjaxLoader && tsf.resetAjaxLoader( seobarAjaxLoader );
					break;
			}
		};

		const onFailure = () => {
			seobarAjaxLoader && tsf.unsetAjaxLoader( seobarAjaxLoader, false );
		};

		$( window ).on( 'tsf-gutenberg-onsave', event => {

			//* Reset ajax loader, we only do that for the SEO Bar.
			seobarAjaxLoader && tsf.resetAjaxLoader( seobarAjaxLoader );

			//* Set ajax loader.
			seobarAjaxLoader && tsf.setAjaxLoader( seobarAjaxLoader );

			let settings = {
				method:   'POST',
				url:      ajaxurl,
				datatype: 'json',
				data:     {
					action:  'the_seo_framework_update_post_data',
					nonce:   tsf.l10n.nonces.edit_posts,
					post_id: l10n.states.id,
					get:     getData,
				},
				async:       true,
				timeout:     7000,
				success:     onSuccess,
				error:       onFailure,
			}

			$.ajax( settings );
		} );
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
		_initCanonicalInput();
		_initTitleListeners();
		_initDescriptionListeners();
		_initGeneralListeners();
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
		// Initializes flex tab positions.
		_initTabs();

		// Initializes flex tab resize listeners.
		_doFlexResizeListener();

		// Sets tooltip boundaries
		_initTooltipBoundaries();

		// Set Gutenberg update listeners.
		_initUpdateMetaBox();

		tsfSocial.initTitleInputs( {
			ref:  document.getElementById( 'tsf-title-reference' ),
			meta: document.getElementById( 'autodescription_title' ),
			og:   document.getElementById( 'autodescription_og_title' ),
			tw:   document.getElementById( 'autodescription_twitter_title' ),
		} );

		tsfSocial.initDescriptionInputs( {
			ref:  document.getElementById( 'tsf-description-reference' ),
			meta: document.getElementById( 'autodescription_description' ),
			og:   document.getElementById( 'autodescription_og_description' ),
			tw:   document.getElementById( 'autodescription_twitter_description' ),
		} );
	}

	return Object.assign( {
		/**
		 * Initialises all aspects of the scripts.
		 * You shouldn't call this.
		 *
		 * @since 4.0.0
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
	}, {}, {
		l10n
	} );
}( jQuery );
jQuery( window.tsfPost.load );
