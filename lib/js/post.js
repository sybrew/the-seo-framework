/**
 * This file holds The SEO Framework plugin's JS code for the Post SEO Settings.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/autodescription/>
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2021 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
	 * @since 4.1.0
	 * @access private
	 * @type {string}
	 */
	const _titleId = 'autodescription_title';
	/**
	 * @since 4.1.0
	 * @access private
	 * @type {string}
	 */
	const _descId = 'autodescription_description';

	/**
	 * @since 4.2.0
	 * @access private
	 * @type {string}
	 */
	const _socialGroup = 'autodescription_social_singular';

	/**
	 * Registers on resize/orientationchange listeners and debounces to only run
	 * at set intervals.
	 *
	 * For Flexbox implementation.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	const _doFlexResizeListener = () => {

		if ( ! document.querySelector( '.tsf-flex' ) ) return;

		let overflowAnimationFrame = {};
		const calculateTextOverflow = target => {

			let innerWrap = target.querySelector( '.tsf-flex-nav-tab-inner' ),
				navNames  = target.getElementsByClassName( 'tsf-flex-nav-name' );

			let displayNames = innerWrap.clientWidth <= target.clientWidth;

			if ( displayNames ) {
				if ( +( target.dataset.displayedNames || 1 ) ) return; // Names are displayed by default on-load. Ergo, 1 by default.
				target.dataset.displayedNames = 1;
				$( navNames ).stop( false, true ).fadeIn( 250 );
				// didShow = true;
			} else {
				if ( ! +( target.dataset.displayedNames || 1 ) ) return;
				target.dataset.displayedNames = 0;
				// Don't animate, we're overflowing--rectify that ASAP.
				$( navNames ).hide();
			}

			if ( +target.dataset.displayedNames ) {
				if ( innerWrap.clientWidth > target.clientWidth ) {
					$( navNames ).stop( false, true ).hide();
					target.dataset.displayedNames = 0;
				} else {
					// Loop once just to be certain, for the browser may be too slow to notice the offset change.
					// Usually, this only happens once when the navNames are meant to be displayed (target width growing).
					setTimeout( () => {
						cancelAnimationFrame( overflowAnimationFrame[ target.id ] );
						overflowAnimationFrame[ target.id ] = requestAnimationFrame( () => calculateTextOverflow( target ) );
					}, 7 ); // 144hz
				}
			}
		}
		const prepareCalculateTextOverflow = event => {
			const target = event.detail.target || document.getElementById( 'tsf-flex-inpost-tabs-wrapper' );
			if ( target )
				overflowAnimationFrame[ target.id ] = requestAnimationFrame( () => calculateTextOverflow( target ) );
		}
		window.addEventListener( 'tsf-flex-resize', prepareCalculateTextOverflow );

		/**
		 * Triggers resize on event.
		 *
		 * @function
		 * @param {HTMLElement|undefined} target The target that's being resized. Optional.
		 */
		const triggerResize = target => {
			window.dispatchEvent( new CustomEvent(
				'tsf-flex-resize',
				{
					bubbles:    false,
					cancelable: false,
					detail:     {
						target
					},
				}
			) );
		}
		let resizeAnimationFrame = {};
		const resizeObserver = new ResizeObserver( entries => {
			// There should be only one entry... Nevertheless, let's loop this for we might add more metaboxes.
			for ( const entry of entries ) {
				let target = entry.target;
				cancelAnimationFrame( resizeAnimationFrame[ target.id ] );
				resizeAnimationFrame[ target.id ] = requestAnimationFrame( () => {
					// No support for all major browsers yet. Neither for entry.contentRect.
					// entry.dataset.boxSizeWidth ||= contentBoxSize.inlineSize;

					target.dataset.lastWidth ||= 0;

					if ( +target.clientWidth !== +target.dataset.lastWidth ) {
						target.dataset.lastWidth = target.clientWidth;
						triggerResize( target );
					}
				} );
			}
		} );
		resizeObserver.observe( document.getElementById( 'tsf-flex-inpost-tabs-wrapper' ) );

		// Trigger after setup
		triggerResize();
	}

	/**
	 * Sets the navigation tabs content equal to the buttons.
	 *
	 * @since 4.0.0
	 * @since 4.1.3 Now offloaded to tsfTabs.
	 * @access private
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	const _initTabs = () => {
		tsfTabs.initStack(
			'tsfSettings',
			{
				tabToggledEvent: new CustomEvent( 'tsf-flex-tab-toggled' ),
				HTMLClasses:     {
					wrapper:          'tsf-flex-nav-tab-wrapper',
					tabRadio:         'tsf-flex-nav-tab-radio',
					tabLabel:         'tsf-flex-nav-tab-label',
					activeTab:        'tsf-flex-tab-active', // change to tsf-flex-nav-tab-active?
					// TODO make this tsf-flex-tab-active-content (force -content affix?)
					activeTabContent: 'tsf-flex-tab-content-active',
				},
				fixHistory:      true, // doesn't work since the inputs reset on navigation; enabled for future-proofing.
			}
		);
	}

	/**
	 * Initializes canonical URL meta input listeners.
	 *
	 * @since 4.0.0
	 * @since 4.1.2 Changed name from _initCanonicalInput
	 * @since 4.1.4 Now no longer proceeds on absence of element ID 'autodescription_noindex'.
	 * @access private
	 *
	 * @function
	 */
	const _initVisibilityListeners = () => {

		const indexSelect = document.getElementById( 'autodescription_noindex' );

		if ( ! indexSelect ) return;

		const canonicalInput = document.getElementById( 'autodescription_canonical' );

		// Prefix with B because I don't trust using 'protected' (might become reserved).
		const BPROTECTED = 0b01,
		      BNOINDEX   = 0b10;

		let canonicalPhState = 0b00,
			canonicalUrl     = '';

		let updateCanonicalPlaceholderDebouncer = void 0;
		/**
		 * @since 4.1.2
		 *
		 * @function
		 * @param {string} link
		 */
		const updateCanonicalPlaceholder = () => {
			clearTimeout( updateCanonicalPlaceholderDebouncer );
			updateCanonicalPlaceholderDebouncer = setTimeout( () => {

				if ( ! canonicalInput ) return;

				// Link might not've been updated (yet). Fill it in with PHP-supplied value (if any).
				canonicalUrl ||= canonicalInput.placeholder;

				if ( ( canonicalPhState & BPROTECTED ) || ( canonicalPhState & BNOINDEX ) ) {
					canonicalInput.placeholder = '';
				} else {
					canonicalInput.placeholder = canonicalUrl;
				}
			}, 50 ); // Magic number. Low enough to not signify a delay.
		}

		/**
		 * @since 4.0.0
		 *
		 * @function
		 * @param {string} link
		 */
		const updateCanonical = link => {
			canonicalUrl = link;
			updateCanonicalPlaceholder();
		}
		$( document ).on( 'tsf-updated-gutenberg-link', ( event, link ) => updateCanonical( link ) );

		/**
		 * @since 4.1.2
		 *
		 * @function
		 * @param {string} visibility
		 */
		const setRobotsDefaultIndexingState = visibility => {
			let _defaultIndexOption = indexSelect.querySelector( '[value="0"]' ),
				indexDefaultValue   = '';

			switch ( visibility ) {
				case 'password':
				case 'private':
					indexDefaultValue = 'noindex';
					canonicalPhState |= BPROTECTED;
					break;

				default:
				case 'public':
					indexDefaultValue = indexSelect.dataset.defaultUnprotected;
					canonicalPhState &= ~BPROTECTED;
					break;
			}

			if ( _defaultIndexOption )
				_defaultIndexOption.innerHTML = indexSelect.dataset.defaultI18n.replace( '%s', tsf.decodeEntities( indexDefaultValue ) );

			updateCanonicalPlaceholder();
		}
		$( document ).on( 'tsf-updated-gutenberg-visibility', ( event, visibility ) => setRobotsDefaultIndexingState( visibility ) );

		/**
		 * @since 4.1.2
		 *
		 * @function
		 * @param {Event} event
		 */
		const setClassicRobotsDefaultIndexingState = event => {
			let visibility = $( '#visibility' ).find( 'input:radio:checked' ).val();
			if ( 'password' === visibility ) {
				let pass = $( '#visibility' ).find( '#post_password' ).val();
				// A falsy-password (like '0'), will return true in "SOME OF" WP's front-end PHP, false in WP's JS before submitting...
				// It won't invoke WordPress's password protection. TODO FIXME: file WP Core bug report.
				if ( ! pass || ! pass.length ) {
					visibility = 'public';
				}
			}
			setRobotsDefaultIndexingState( visibility );
		}
		document.querySelector( '#visibility .save-post-visibility' )
			?.addEventListener( 'click', setClassicRobotsDefaultIndexingState );

		if ( l10n.states.isPrivate ) {
			setRobotsDefaultIndexingState( 'private' );
		} else if ( l10n.states.isProtected ) {
			setRobotsDefaultIndexingState( 'password' );
		} else {
			setRobotsDefaultIndexingState( 'public' );
		}

		/**
		 * @since 4.1.2
		 *
		 * @function
		 * @param {Number} value
		 */
		const setRobotsIndexingState = value => {
			let type = '';

			switch ( +value ) {
				case 0: // default, unset since unknown.
					type = indexSelect.dataset.defaultUnprotected;
					break;
				case -1: // index
					type = 'index';
					break;
				case 1: // noindex
					type = 'noindex';
					break;
			}
			if ( 'noindex' === type ) {
				canonicalPhState |= BNOINDEX;
			} else {
				canonicalPhState &= ~BNOINDEX;
			}

			updateCanonicalPlaceholder();
		}
		indexSelect.addEventListener( 'change', event => setRobotsIndexingState( event.target.value ) );
		setRobotsIndexingState( indexSelect.value );
	}

	/**
	 * Initializes title meta input listeners.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 */
	const _initTitleListeners = () => {

		const titleInput = document.getElementById( _titleId );
		if ( ! titleInput ) return;

		tsfTitle.setInputElement( titleInput );

		const state = JSON.parse( document.getElementById( `tsf-title-data_${_titleId}` )?.dataset.state || 0 );

		if ( state ) {
			tsfTitle.updateStateOf( _titleId, 'allowReferenceChange', ! state.refTitleLocked );
			tsfTitle.updateStateOf( _titleId, 'defaultTitle', state.defaultTitle );
			tsfTitle.updateStateOf( _titleId, 'addAdditions', state.addAdditions );
			tsfTitle.updateStateOf( _titleId, 'additionValue', state.additionValue );
			tsfTitle.updateStateOf( _titleId, 'additionPlacement', state.additionPlacement );
			tsfTitle.updateStateOf( _titleId, 'hasLegacy', !! ( state.hasLegacy || false ) );
		}

		/**
		 * Updates title additions, based on singular settings change.
		 *
		 * @function
		 * @param {Event} event
		 */
		const updateTitleAdditions = event => {
			let addAdditions = ! event.target.checked;

			if ( l10n.params.additionsForcedDisabled ) {
				addAdditions = false;
			} else if ( l10n.params.additionsForcedEnabled ) {
				addAdditions = true;
			}

			tsfTitle.updateStateOf( _titleId, 'addAdditions', addAdditions );
		}
		const blogNameTrigger = document.getElementById( 'autodescription_title_no_blogname' );
		if ( blogNameTrigger ) {
			blogNameTrigger.addEventListener( 'change', updateTitleAdditions );
			blogNameTrigger.dispatchEvent( new Event( 'change' ) );
		}

		/**
		 * Sets private/protected visibility state.
		 *
		 * @function
		 * @param {string} visibility
		 */
		const setTitleVisibilityPrefix = visibility => {
			let prefixValue = '';

			switch ( visibility ) {
				case 'password':
					prefixValue = tsfTitle.protectedPrefix;
					break;

				case 'private':
					prefixValue = tsfTitle.privatePrefix;
					break;

				default:
				case 'public':
					prefixValue = '';
					break;
			}

			tsfTitle.updateStateOf( _titleId, 'prefixValue', prefixValue );
		}
		$( document ).on( 'tsf-updated-gutenberg-visibility', ( event, visibility ) => setTitleVisibilityPrefix( visibility ) );

		/**
		 * Sets private/protected visibility state for the classic editor.
		 *
		 * @function
		 * @param {Event} event
		 */
		const setClassicTitleVisibilityPrefix = event => {
			let visibility = $( '#visibility' ).find( 'input:radio:checked' ).val();
			if ( 'password' === visibility ) {
				let pass = $( '#visibility' ).find( '#post_password' ).val();
				// A falsy-password (like '0'), will return true in "SOME OF" WP's front-end PHP, false in WP's JS before submitting...
				// It won't invoke WordPress's password protection. TODO FIXME: file WP Core bug report.
				if ( ! pass || ! pass.length ) {
					visibility = 'public';
				}
			}
			setTitleVisibilityPrefix( visibility );
		}
		document.querySelector( '#visibility .save-post-visibility' )
			?.addEventListener( 'click', setClassicTitleVisibilityPrefix );

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
		 */
		const updateDefaultTitle = val => {
			val = val?.trim() || '';

			let defaultTitle = tsfTitle.stripTitleTags ? tsf.stripTags( val ) : val

			tsfTitle.updateStateOf( _titleId, 'defaultTitle', defaultTitle || tsfTitle.untitledTitle );
		}
		//= The homepage listens to a static preset value. Update all others.
		if ( ! l10n.params.isFront ) {
			const classicTitleInput = document.querySelector( '#titlewrap #title' );
			classicTitleInput && classicTitleInput.addEventListener( 'input', event => updateDefaultTitle( event.target.value ) );

			$( document ).on( 'tsf-updated-gutenberg-title', ( event, title ) => updateDefaultTitle( title ) );
		}

		tsfTitle.enqueueUnregisteredInputTrigger( _titleId );
	}

	/**
	 * Initializes description meta input listeners.
	 *
	 * @since 4.0.0
	 * @since 4.1.2 Now prefills the 'useDefaultDescription' accordingly.
	 * @access private
	 *
	 * @function
	 */
	const _initDescriptionListeners = () => {

		const descInput = document.getElementById( _descId );
		if ( ! descInput ) return;

		tsfDescription.setInputElement( descInput );

		const state = JSON.parse( document.getElementById( `tsf-description-data_${_descId}` )?.dataset.state || 0 );

		if ( state ) {
			tsfDescription.updateStateOf( _descId, 'allowReferenceChange', ! state.refDescriptionLocked );
			tsfDescription.updateStateOf( _descId, 'defaultDescription', state.defaultDescription.trim() );
			tsfDescription.updateStateOf( _descId, 'hasLegacy', !! ( state.hasLegacy || false ) );
		}

		tsfDescription.enqueueUnregisteredInputTrigger( _descId );

		/**
		 * Sets private/protected visibility state.
		 *
		 * @function
		 * @param {string} visibility
		 */
		const setDescriptionVisibility = visibility => {
			let oldUseDefaultDescription = tsfDescription.getStateOf( _descId, 'useDefaultDescription' ),
				useDefaultDescription    = true;

			switch ( visibility ) {
				case 'password':
				case 'private':
					useDefaultDescription = false;
					break;

				default:
				case 'public':
					useDefaultDescription = true;
					break;
			}

			if ( useDefaultDescription !== oldUseDefaultDescription )
				tsfDescription.updateStateOf( _descId, 'useDefaultDescription', useDefaultDescription );
		}
		$( document ).on( 'tsf-updated-gutenberg-visibility', ( event, visibility ) => setDescriptionVisibility( visibility ) );

		/**
		 * Sets private/protected visibility state for the classic editor.
		 *
		 * @function
		 * @param {Event} event
		 */
		const setClassicDescriptionVisibilityPrefix = event => {
			let visibility = $( '#visibility' ).find( 'input:radio:checked' ).val();
			if ( 'password' === visibility ) {
				let pass = $( '#visibility' ).find( '#post_password' ).val();
				// A falsy-password (like '0'), will return true in "SOME OF" WP's front-end PHP, false in WP's JS before submitting...
				// It won't invoke WordPress's password protection. TODO FIXME: file WP Core bug report?
				if ( ! pass || ! pass.length ) {
					visibility = 'public';
				}
			}
			setDescriptionVisibility( visibility );
		}
		document.querySelector( '#visibility .save-post-visibility' )
			?.addEventListener( 'click', setClassicDescriptionVisibilityPrefix );

		if ( l10n.states.isPrivate ) {
			setDescriptionVisibility( 'private' );
		} else if ( l10n.states.isProtected ) {
			setDescriptionVisibility( 'password' );
		}
	}

	/**
	 * Initializes social meta input listeners.
	 *
	 * @since 4.2.0
	 * @access private
	 *
	 * @function
	 */
	const _initSocialListeners = () => {

		tsfSocial.setInputInstance( _socialGroup, _titleId, _descId );

		const groupData = JSON.parse(
			document.getElementById( `tsf-social-data_${_socialGroup}` )?.dataset.settings || 0
		);

		if ( ! groupData ) return;

		tsfSocial.updateStateOf( _socialGroup, 'addAdditions', groupData.og.state.addAdditions ); // tw Also has one. Maybe future.
		tsfSocial.updateStateOf(
			_socialGroup,
			'defaults',
			{
				ogTitle: groupData.og.state.defaultTitle,
				twTitle: groupData.tw.state.defaultTitle,
				ogDesc:  groupData.og.state.defaultDesc,
				twDesc:  groupData.tw.state.defaultDesc,
			}
		);
		tsfSocial.updateStateOf(
			_socialGroup,
			'inputLocks',
			{
				ogTitle: groupData.og.state?.titleLock || false,
				twTitle: groupData.tw.state?.titleLock || false,
				ogDesc:  groupData.og.state?.descLock || false,
				twDesc:  groupData.tw.state?.descLock || false,
			}
		);
	}

	/**
	 * Initializes uncategorized general tab meta input listeners.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Removed postbox-toggled listener, since tsf-flex-resize is all-encapsulating now.
	 * @access private
	 *
	 * @function
	 */
	const _initGeneralListeners = () => {

		const enqueueGeneralInputListeners = () => {
			tsfTitle.enqueueUnregisteredInputTrigger( _titleId );
			tsfDescription.enqueueUnregisteredInputTrigger( _descId );
		}

		// We can't bind to jQuery event listeners via native ES :(
		$( '#tsf-flex-inpost-tab-general' ).on( 'tsf-flex-tab-toggled', enqueueGeneralInputListeners );
		window.addEventListener( 'tsf-flex-resize', enqueueGeneralInputListeners );
	}

	/**
	 * Initializes tooltip boundaries.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 */
	const _initTooltipBoundaries = () => {

		if ( ! l10n.states.isGutenbergPage ) return;

		'tsfTT' in window && tsfTT.addBoundary( '#editor' );

		// Listen to the Gutenberg state changes.
		document.addEventListener( 'tsf-gutenberg-sidebar-opened', () => {
			'tsfTT' in window && tsfTT.addBoundary( '.edit-post-sidebar .components-panel' );
		} );
	}

	/**
	 * Updates the SEO Bar and meta description placeholders on successful save.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 */
	const _initUpdateMetaBox = () => {

		if ( ! l10n.states.isGutenbergPage ) return;

		const seobar = document.querySelector( '.tsf-seo-bar' );

		// We only use this because it looks nice. The rest is implied via the counter updates.
		const seobarAjaxLoader = document.querySelector( '#tsf-doing-it-right-wrap .tsf-ajax' );

		const imageUrl = document.getElementById( 'autodescription_socialimage-url' );

		const _ogDescription = tsfSocial.getInputInstance( _socialGroup )?.inputs?.ogDesc;
		const _twDescription = tsfSocial.getInputInstance( _socialGroup )?.inputs?.twDesc;

		const getData = {
			seobar:          !! seobar,
			metadescription: !! document.getElementById( _descId ),
			ogdescription:   !! _ogDescription,
			twdescription:   !! _twDescription,
			imageurl:        !! imageUrl,
		}

		const onSuccess = response => {

			response = tsf.convertJSONResponse( response );

			// Wait the same amount of time as the SEO Bar, so to sync the changes.
			const fadeTime = 75;

			setTimeout( () => {
				if ( 'tsfDescription' in window ) {
					tsfDescription.updateStateOf( _descId, 'defaultDescription', response.data.metadescription.trim() );
				}
				if ( 'tsfSocial' in window ) {
					const socialDefaults = tsfSocial.getStateOf( _socialGroup, 'defaults' );
					socialDefaults.ogDesc = response.data.ogdescription.trim();
					socialDefaults.twDesc = response.data.twdescription.trim();
					tsfSocial.updateStateOf( _socialGroup, 'defaults', socialDefaults );
				}
				if ( imageUrl ) {
					// Is this necessary? It's safer than assuming, though :)
					imageUrl.placeholder = tsf.decodeEntities( response.data.imageurl );
					imageUrl.dispatchEvent( new Event( 'change' ) );
				}

				'tsfAys' in window && tsfAys.reset();
			}, fadeTime );

			$( seobar )
				.fadeOut(
					fadeTime,
					() => {
						seobarAjaxLoader && tsf.unsetAjaxLoader( seobarAjaxLoader, true );
						seobar.innerHTML = response.data.seobar
					}
				).fadeIn(
					250, // Magic number: Feels fast, but slow enough to grab attention.
					() => {
						'tsfTT' in window && tsfTT.triggerReset();
					}
				);
		};

		const onFailure = () => {
			seobarAjaxLoader && tsf.unsetAjaxLoader( seobarAjaxLoader, false );
		}

		document.addEventListener( 'tsf-gutenberg-onsave', () => {
			// Reset ajax loader, we only do that for the SEO Bar.
			seobarAjaxLoader && tsf.resetAjaxLoader( seobarAjaxLoader );

			// Set ajax loader.
			seobarAjaxLoader && tsf.setAjaxLoader( seobarAjaxLoader );

			wp.ajax.send(
				'tsf_update_post_data',
				{
					data: {
						nonce:   tsf.l10n.nonces.edit_posts,
						post_id: l10n.states.id,
						get:     getData,
					},
					timeout: 7000,
				}
			).done( onSuccess ).fail( onFailure );
		} );
	}

	/**
	 * Initializes settings scripts on TSF-load.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 */
	const _loadSettings = () => {
		_initVisibilityListeners();
		_initTitleListeners();
		_initDescriptionListeners();
		_initSocialListeners();
		_initGeneralListeners();
	}

	/**
	 * Initializes settings scripts on TSF-ready.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now registers the refNa title input.
	 * @access private
	 *
	 * @function
	 */
	const _readySettings = () => {
		// Initializes flex tab resize listeners.
		_doFlexResizeListener();

		// Initializes flex tab listeners and fixes positions.
		_initTabs();

		// Sets tooltip boundaries
		_initTooltipBoundaries();

		// Set Gutenberg update listeners.
		_initUpdateMetaBox();
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
		 */
		load: () => {
			document.body.addEventListener( 'tsf-onload', _loadSettings );
			document.body.addEventListener( 'tsf-ready', _readySettings );
		}
	}, {
		l10n
	} );
}( jQuery );
window.tsfPost.load();
