/**
 * This file holds The SEO Framework plugin's JS code for the Post SEO Settings.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/autodescription/>
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 */
window.tsfPost = function() {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 4.0.0
	 * @access public
	 * @type {(Object<string,*>)|boolean|null} l10n Localized strings
	 */
	const l10n = tsfPostL10n;

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
	 * @since 5.0.7
	 * @access private
	 * @type {string}
	 */
	const _canonicalId = 'autodescription_canonical';

	/**
	 * @since 4.2.0
	 * @access private
	 * @type {string}
	 */
	const _socialGroup = 'autodescription_social_singular';

	/**
	 * Returns the visibility setting from the Classic editor, as WordPress's PHP would interpret it.
	 * We could optimize this (it runs thrice in a row), but that's not for this function to fix.
	 * If WordPress didn't have this bug, we needn't have done these lookups at all.
	 *
	 * @since 5.0.5
	 * @access private
	 *
	 * @return {string} 'public', 'password', or 'private'.
	 */
	function _getClassicVisibility() {

		let visibility = [ ...document.getElementsByName( 'visibility' ) ].filter( e => e.checked )?.[0]?.value;

		// If password type is selected, but no password is set (or a falsy one), then assume public. This is a bug in WP.
		if ( 'password' === visibility ) {
			const val = document.getElementById( 'post_password' )?.value;
			if ( val?.length && '0' === val )
				visibility = 'public';
		}

		return visibility;
	}

	/**
	 * Registers on resize/orientationchange listeners and debounces to only run
	 * at set intervals.
	 *
	 * For Flexbox implementation.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @return {(undefined|null)}
	 */
	function _doFlexResizeListener() {

		if ( ! document.querySelector( '.tsf-flex' ) ) return;

		const wrapper = document.getElementById( 'tsf-flex-inpost-tabs-wrapper' );

		const overflowAnimationFrame = new Map();

		const calculateTextOverflow = target => {

			const innerWrap = target.querySelector( '.tsf-flex-nav-tab-inner' ),
				  navNames  = target.querySelectorAll( '.tsf-flex-nav-name' );

			if ( innerWrap.clientWidth <= target.clientWidth ) {
				if ( +( target.dataset.displayedNames || 1 ) ) return; // Names are displayed by default on-load. Ergo, 1 by default.
				target.dataset.displayedNames = 1;
				navNames.forEach( element => {
					element.style.display = null;
					tsfUI.fadeIn( element );
				} );
			} else {
				if ( ! +( target.dataset.displayedNames || 1 ) ) return;
				target.dataset.displayedNames = 0;
				// Don't animate, we're overflowing--rectify that ASAP.
				navNames.forEach( element => { element.style.display = 'none' } );
			}

			if ( +target.dataset.displayedNames ) {
				if ( innerWrap.clientWidth > target.clientWidth ) {
					// Don't animate, we're overflowing--rectify that ASAP.
					navNames.forEach( element => { element.style.display = 'none' } );
					target.dataset.displayedNames = 0;
				} else {
					// Loop once just to be certain, for the browser may be too slow to notice the offset change.
					// Usually, this only happens once when the navNames are meant to be displayed (target width growing).
					setTimeout(
						() => {
							cancelAnimationFrame( overflowAnimationFrame.get( target.id ) );
							overflowAnimationFrame.set( target.id, requestAnimationFrame( () => calculateTextOverflow( target ) ) );
						},
						1000/144, // 144hz.
					);
				}
			}
		}
		const prepareCalculateTextOverflow = event => {
			const target = event.detail.target || wrapper;
			if ( target )
				overflowAnimationFrame.set( target.id, requestAnimationFrame( () => calculateTextOverflow( target ) ) );
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
						target,
					},
				},
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
		wrapper && resizeObserver.observe( wrapper );

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
	 * @return {(undefined|null)}
	 */
	function _initTabs() {
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
	 */
	function _initVisibilityListeners() {

		const indexSelect    = document.getElementById( 'autodescription_noindex' ),
			  canonicalInput = document.getElementById( 'autodescription_canonical' );

		const urlDataParts = new Map();

		// Prefixed with B because I don't trust using 'protected' (might become reserved).
		const BPROTECTED = 0b01,
			  BNOINDEX   = 0b10;

		let canonicalPhState = 0b00;

		tsfCanonical.setInputElement( canonicalInput );

		const state = JSON.parse( document.getElementById( `tsf-canonical-data_${_canonicalId}` )?.dataset.state || 0 );

		if ( state ) {
			tsfCanonical.updateStateOf( _canonicalId, 'allowReferenceChange', ! state.refCanonicalLocked );
			tsfCanonical.updateStateOf( _canonicalId, 'defaultCanonical', state.defaultCanonical.trim() );
			tsfCanonical.updateStateOf( _canonicalId, 'preferredScheme', state.preferredScheme.trim() );
			tsfCanonical.updateStateOf( _canonicalId, 'urlStructure', state.urlStructure );
		}

		tsfCanonical.enqueueTriggerUnregisteredInput( _canonicalId );

		/**
		 * @since 4.1.2
		 *
		 * @function
		 * @param {string} link
		 */
		const updateCanonicalPlaceholder = tsfUtils.debounce(
			() => {
				tsfCanonical.updateStateOf(
					_canonicalId,
					'showUrlPlaceholder',
					( canonicalPhState & BPROTECTED ) || ( canonicalPhState & BNOINDEX )
						? false
						: true,
				);
				tsfCanonical.updateStateOf(
					_canonicalId,
					'urlDataParts',
					Object.fromEntries( urlDataParts.entries() ),
				);
			},
			1000/60, // 60 fps
		);

		if ( canonicalInput ) {
			/**
			 * @since 4.0.0
			 * @since 5.0.7 Now obtains an accurate canonical URL via AJAX.
			 *
			 * @function
			 */
			const updateCanonical = data => {

				// We'd want to get categories from the server I guess?
				// getCanonicalPromise.done( response => {
				// 	response     = tsf.convertJSONResponse( response );
				// 	canonicalUrl = response.link;
				// } ).always( updateCanonicalPlaceholder );

				let slug = data.slug;
				if ( ! slug )
					slug = encodeURIComponent(
						data.title.substring( 0, 200 ).toLowerCase().replace( /\s+/, '-' )
					);

				const date = new Date( data.date );

				// There are unlimited possibilities for the URL structure,
				// but we'll only support the most common ones, because the entire thing is a mess in Core.
				urlDataParts
					.set( `%year%`, date.getUTCFullYear() )
					.set( `%monthnum%`, date.getUTCMonth() + 1 )
					.set( `%day%`, date.getUTCDate() )
					.set( `%hour%`, date.getUTCHours() )
					.set( `%minute%`, date.getUTCMinutes() )
					// .set( `%second%`, date.getUTCSeconds() ) // This doesn't even work on the front-end.
					.set( `%post_id%`, l10n.states.id )
					.set( `%postname%`, slug ) // This one is for all types.
					.set( `%category%`, '' ) // var_dump() TODO
					.set( `%post_tag%`, '' ) // var_dump() TODO
					.set( `%author%`, data.author );

				// This is valid, but only for the 'page' post type.
				// On all other post types, it creates extraneous URL components. So, I doubt anyone uses it.
				// To fill this, we'd have to make an exception for the page post type.
				// urlDataParts.set( `%pagename%`, slug );

				updateCanonicalPlaceholder();
			}
			document.addEventListener(
				'tsf-updated-block-editor',
				event => {
					// This event is already debounced. No need to debounce it again.
					switch ( event.type ) {
						case 'link':
						case 'title':
						case 'slug': // redundant?
						case 'author':
						case 'date':
							updateCanonical( {
								link:   event.detail.postData.get( 'link' ),
								title:  event.detail.postData.get( 'title' ),
								slug:   event.detail.postData.get( 'slug' ),
								author: event.detail.postData.get( 'author' ),
								date:   event.detail.postData.get( 'date' ),
							} );
					}
				}
			);

			// Classic Editor.
			if ( ! l10n.states.isGutenbergPage ) {
				// 'editable-post-name' and 'sample-permalink' get destroyed on slug change, so we can't rely on them.
				// editSlugBox is only available when the post is not a draft for "publish posts" capability.
				const editSlugBox = document.getElementById( 'edit-slug-box' );

				const postName   = document.getElementById( 'post_name' );
				const authorId   = document.getElementById( 'post_author_override' ) ?? document.getElementById( 'post_author' );
				const dateFields = [
					document.getElementById( 'aa' ),
					document.getElementById( 'mm' ),
					document.getElementById( 'jj' ),
					document.getElementById( 'hh' ),
					document.getElementById( 'mn' ),
				];

				const classicCanonicalUpdate = tsfUtils.debounce(
					() => {
						updateCanonical( {
							link:   document.getElementById( 'sample-permalink' )?.innerText || '', // temp
							title:  document.getElementById( 'title' )?.value ?? '',
							// post_name is the 'slug' meta box, which is the "real_slug."
							slug:   postName?.value ?? document.getElementById( 'editable-post-name-full' )?.innerText,
							author: authorId?.value,
							date:   new Date( ...dateFields.map( e => e?.value ?? '00' ) ).toISOString(),
						} );
					},
					500, // High enough not to invoke too often.
				);

				if ( editSlugBox ) {
					// Observe these for changes. Otherwise, we'd have to rely on a multitude of jQuery callbacks affecting it.
					new MutationObserver(
						mutationList => {
							for ( const mutation of mutationList ) {
								// Look for the reintroduction of the "slug is edited" box.
								if ( mutation.addedNodes.entries().some(
									( [ , node ] ) => 'editable-post-name-full' === node.id
								) ) {
									classicCanonicalUpdate();
									break;
								}
							}
						}
					).observe(
						editSlugBox,
						{ childList: true, subtree: true },
					);
				}

				postName?.addEventListener( 'change', classicCanonicalUpdate );
				authorId?.addEventListener( 'change', classicCanonicalUpdate );

				dateFields.forEach( field => {
					field?.addEventListener( 'change', classicCanonicalUpdate );
				} );

				classicCanonicalUpdate();
			}
		}

		if ( indexSelect ) {
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
					_defaultIndexOption.innerHTML = indexSelect.dataset.defaultI18n.replace(
						'%s',
						tsf.escapeString( tsf.decodeEntities( indexDefaultValue ) )
					);

				updateCanonicalPlaceholder();
			}
			document.addEventListener(
				'tsf-updated-block-editor-visibility',
				event => setRobotsDefaultIndexingState( event.detail.value ),
			);

			// Classic Editor.
			document.querySelector( '#visibility .save-post-visibility' )
				?.addEventListener( 'click', () => setRobotsDefaultIndexingState( _getClassicVisibility() ) );

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
	}

	/**
	 * Initializes title meta input listeners.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	function _initTitleListeners() {

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
		document.addEventListener(
			'tsf-updated-block-editor-visibility',
			event => setTitleVisibilityPrefix( event.detail.value ),
		);

		// Classic Editor.
		document.querySelector( '#visibility .save-post-visibility' )
			?.addEventListener( 'click', () => setTitleVisibilityPrefix( _getClassicVisibility() ) );

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

			tsfTitle.updateStateOf(
				_titleId,
				'defaultTitle',
				( tsfTitle.stripTitleTags ? tsf.stripTags( val ) : val ) || tsfTitle.untitledTitle,
			);
		}
		// The homepage listens to a static preset value. Update all others.
		if ( ! l10n.params.isFront ) {
			document.querySelector( '#titlewrap #title' ) // Extra specific to only target Classic Editor.
				?.addEventListener(
					'input',
					event => { updateDefaultTitle( event.target.value ) },
				);

			document.addEventListener(
				'tsf-updated-block-editor-title',
				event => updateDefaultTitle( event.detail.value )
			);
		}

		tsfTitle.enqueueUnregisteredInputTrigger( _titleId );
	}

	/**
	 * Initializes description meta input listeners.
	 *
	 * @since 4.0.0
	 * @since 4.1.2 Now prefills the 'useDefaultDescription' accordingly.
	 * @access private
	 */
	function _initDescriptionListeners() {

		const descInput = document.getElementById( _descId );
		if ( ! descInput ) return;

		tsfDescription.setInputElement( descInput );

		const state = JSON.parse( document.getElementById( `tsf-description-data_${_descId}` )?.dataset.state || 0 );

		if ( state ) {
			tsfDescription.updateStateOf( _descId, 'allowReferenceChange', ! state.refDescriptionLocked );
			tsfDescription.updateStateOf( _descId, 'defaultDescription', state.defaultDescription.trim() );
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
		document.addEventListener(
			'tsf-updated-block-editor-visibility',
			event => setDescriptionVisibility( event.detail.value )
		);

		// Classic Editor.
		document.querySelector( '#visibility .save-post-visibility' )
			?.addEventListener( 'click', () => setDescriptionVisibility( _getClassicVisibility() ) );

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
	 */
	function _initSocialListeners() {

		tsfSocial.setInputInstance( _socialGroup, _titleId, _descId );

		const groupData = JSON.parse(
			document.getElementById( `tsf-social-data_${_socialGroup}` )?.dataset.settings || 0,
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
	 */
	function _initGeneralListeners() {

		const enqueueGeneralInputListeners = () => {
			tsfTitle.enqueueUnregisteredInputTrigger( _titleId );
			tsfDescription.enqueueUnregisteredInputTrigger( _descId );
		}

		document.getElementById( 'tsf-flex-inpost-tab-general' )
			?.addEventListener( 'tsf-flex-tab-toggled', enqueueGeneralInputListeners );
		window.addEventListener( 'tsf-flex-resize', enqueueGeneralInputListeners );
	}

	/**
	 * Updates the SEO Bar and meta description placeholders on successful save.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	function _initUpdateMetaBox() {

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

			setTimeout(
				() => {
					tsfDescription.updateStateOf( _descId, 'defaultDescription', response.data.metadescription.trim() );

					const socialDefaults = tsfSocial.getStateOf( _socialGroup, 'defaults' );
					socialDefaults.ogDesc = response.data.ogdescription.trim();
					socialDefaults.twDesc = response.data.twdescription.trim();
					tsfSocial.updateStateOf( _socialGroup, 'defaults', socialDefaults );

					if ( imageUrl ) {
						// Is this necessary? It's safer than assuming, though :)
						imageUrl.placeholder = tsf.decodeEntities( response.data.imageurl );
						imageUrl.dispatchEvent( new Event( 'change' ) );
						tsfTT.triggerReset();
					}

					tsfAys.reset();
				},
				fadeTime,
			);

			seobar && tsfUI.fadeOut(
				seobar,
				fadeTime,
				() => {
					seobarAjaxLoader && tsf.unsetAjaxLoader( seobarAjaxLoader, true );
					seobar.innerHTML = response.data.seobar;

					tsfUI.fadeIn(
						seobar,
						fadeTime,
						() => {
							tsfTT.triggerReset();
						}
					)
				},
			);
		};

		const onFailure = () => {
			seobarAjaxLoader && tsf.unsetAjaxLoader( seobarAjaxLoader, false );
		}

		document.addEventListener(
			'tsf-gutenberg-onsave',
			() => {
				// Reset ajax loader, we only do that for the SEO Bar.
				seobarAjaxLoader && tsf.resetAjaxLoader( seobarAjaxLoader );

				// Set ajax loader.
				seobarAjaxLoader && tsf.setAjaxLoader( seobarAjaxLoader );

				wp.ajax.send(
					'tsf_update_post_data',
					{
						data: {
							nonce:   l10n.nonces.edit_post[ l10n.states.id ],
							post_id: l10n.states.id,
							get:     getData,
						},
						timeout: 7000,
					}
				).done( onSuccess ).fail( onFailure );
			}
		);
	}

	/**
	 * Initializes settings scripts on TSF-load.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	function _loadSettings() {
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
	 */
	function _readySettings() {
		// Initializes flex tab resize listeners.
		_doFlexResizeListener();

		// Initializes flex tab listeners and fixes positions.
		_initTabs();

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
		},
	}, {
		l10n,
	} );
}();
window.tsfPost.load();
